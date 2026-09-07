<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CrewLeader;
use App\Models\CustomerOrder;
use App\Models\DailyWorkSheet;
use App\Models\DailyWorkSheetHarvestItem;
use App\Models\DeliveryType;
use App\Models\FertilizationRecipe;
use App\Models\FertilizationRun;
use App\Models\FertilizationTankLog;
use App\Models\IrrigationSchedule;
use App\Models\JobType;
use App\Models\MarketPrice;
use App\Models\PackagingDefinition;
use App\Models\Personnel;
use App\Models\Product;
use App\Models\ProductionLocation;
use App\Models\PurificationControl;
use App\Models\RawWaterControl;
use App\Models\ShipmentDelivery;
use App\Models\SprayingApplication;
use App\Models\SprayingRecipe;
use App\Models\TradingParty;
use App\Models\WaterAnalysisLog;
use App\Models\WaterSource;
use App\Models\Worker;
use App\Models\WorkPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgricultureController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->query('cat', 'tesis');
        $module = $request->query('mod', 'is_planlama');

        $user = Auth::user();
        $personnel = null;
        $companyIds = null;

        if ($user && !$user->is_admin) {
            $personnel = Personnel::where('user_id', $user->id)->first();
            if (!$personnel || !$personnel->is_active) {
                abort(403, 'Hesabınız pasif durumdadır veya personel kaydınız bulunmamaktadır.');
            }
            $userPerms = $personnel->permissions ?? [];
            if (!in_array($category, $userPerms)) {
                $allowedCat = collect($userPerms)->first(fn($p) => in_array($p, ['tesis', 'uretim', 'teknik', 'operasyon', 'raporlar']));
                if (!$allowedCat) {
                    abort(403, 'Bu modüllere erişim yetkiniz bulunmamaktadır.');
                }
                $category = $allowedCat;
            }
            $companyIds = $personnel->company_ids ?? [];
        }

        $companiesQuery = Company::query();
        $locationsQuery = ProductionLocation::with(['sections', 'valves']);
        $dailyWorkSheetsQuery = DailyWorkSheet::with([
            'company',
            'productionLocation',
            'createdBy',
            'updatedBy',
            'submittedBy',
            'approvedByUser',
            'crewLeaders.crewLeader',
            'workerAssignments.jobType',
            'workerAssignments.crewLeader',
            'workerAssignments.worker',
            'workerAssignments.personnel',
            'harvestItems.product',
            'harvestItems.productSubtype',
            'harvestItems.packaging',
        ]);
        $workPlansQuery = WorkPlan::with(['productionLocation', 'jobType', 'assignedPersonnel', 'comments.personnel']);
        $waterSourcesQuery = WaterSource::with('filters');

        if ($companyIds !== null) {
            $companiesQuery->whereIn('id', $companyIds);
            $locationsQuery->whereIn('company_id', $companyIds);
            $dailyWorkSheetsQuery->whereIn('company_id', $companyIds);
            $workPlansQuery->where(function ($q) use ($companyIds) {
                $q->whereNull('production_location_id')
                    ->orWhereHas('productionLocation', fn ($sq) => $sq->whereIn('company_id', $companyIds));
            });
            $waterSourcesQuery->whereHas('productionLocation', fn ($sq) => $sq->whereIn('company_id', $companyIds));
        }

        return inertia('Agriculture/Index', [
            'activeCategory' => $category,
            'activeModule' => $module,
            'companies' => $companiesQuery->get(),
            'locations' => $locationsQuery->get(),
            'jobTypes' => JobType::all(),
            'personnels' => Personnel::all(),
            'workers' => Worker::with('crewLeader')->get(),
            'products' => Product::with(['subtypes', 'packagings'])->get(),
            'crewLeaders' => CrewLeader::with('workers')->get(),
            'tradingParties' => TradingParty::all(),
            'deliveryTypes' => DeliveryType::all(),
            'fertRecipes' => FertilizationRecipe::with('tanks.items')->get(),
            'sprayRecipes' => SprayingRecipe::with('items')->get(),
            'waterSources' => $waterSourcesQuery->get(),
            'packagings' => PackagingDefinition::all(),
            'marketPrices' => MarketPrice::with('product')->latest()->get(),
            'workPlans' => $workPlansQuery->latest()->get(),
            'fertRuns' => FertilizationRun::with(['recipe.tanks.items', 'tankLogs.preparedBy', 'tankLogs.tank'])->latest()->get(),
            'irrigationSchedules' => IrrigationSchedule::with(['recipe', 'valves.valve', 'valves.location'])->latest()->get(),
            'sprayApplications' => SprayingApplication::with(['recipe', 'appliedBy'])->latest()->get(),
            'waterAnalysisLogs' => WaterAnalysisLog::with('waterSource')->latest()->get(),
            'rawWaterControls' => RawWaterControl::with('waterSource')->latest()->get(),
            'purificationControls' => PurificationControl::with('waterSource')->latest()->get(),
            'customerOrders' => CustomerOrder::with(['tradingParty', 'items.product', 'items.packaging', 'shipments'])->latest()->get(),
            'shipments' => ShipmentDelivery::with(['order', 'tradingParty', 'product', 'packaging', 'deliveryType'])->latest()->get(),
            'dailyWorkSheets' => $dailyWorkSheetsQuery->orderBy('work_date', 'desc')->orderBy('id', 'desc')->get(),
        ]);
    }

    public function storeWorkPlan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'title' => 'required|string|max:255',
            'production_location_id' => 'nullable|exists:uretim_yerleri,id',
            'job_type_id' => 'nullable|exists:is_tanimlari,id',
            'assigned_personnel_id' => 'nullable|exists:personeller,id',
            'plan_date' => 'required|date',
            'due_date' => 'nullable|date',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'description' => 'nullable|string',
            'completion_notes' => 'nullable|string',
            'photo' => 'nullable|image|max:10240',
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('work_plans', 'public');
            $validated['photo_path'] = '/storage/' . $path;
        }

        if ($request->filled('id')) {
            $workPlan = WorkPlan::findOrFail($request->id);
            $workPlan->update($validated);
        } else {
            WorkPlan::create($validated);
        }

        return redirect()->back()->with('success', 'İş planı kaydedildi.');
    }

    public function storeWorkPlanComment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'work_plan_id' => 'required|exists:is_planlari,id',
            'comment' => 'required|string',
            'photo' => 'nullable|image|max:10240',
        ]);

        $personnel = Personnel::where('user_id', Auth::id())->first();

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('work_plan_comments', 'public');
            $photoPath = '/storage/' . $path;
        }

        \App\Models\WorkPlanComment::create([
            'work_plan_id' => $validated['work_plan_id'],
            'personnel_id' => $personnel?->id,
            'comment' => $validated['comment'],
            'photo_path' => $photoPath,
        ]);

        return redirect()->back()->with('success', 'Not / Yorum eklendi.');
    }

    public function storeFertilizationRun(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'fertilization_recipe_id' => 'required|exists:gubre_receteleri,id',
            'start_date' => 'required|date',
            'end_condition' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $isActive = $request->has('is_active') ? (bool) $request->is_active : true;

        if ($isActive) {
            FertilizationRun::query()->where('id', '!=', $request->id ?? 0)->update(['is_active' => false, 'end_date' => now()->toDateString()]);
        }

        FertilizationRun::updateOrCreate(
            ['id' => $request->id],
            [
                'fertilization_recipe_id' => $validated['fertilization_recipe_id'],
                'start_date' => $validated['start_date'],
                'end_condition' => $validated['end_condition'],
                'is_active' => $isActive,
                'end_date' => $isActive ? null : now()->toDateString(),
            ]
        );

        return redirect()->back()->with('success', 'Gübreleme reçetesi kaydı güncellendi.');
    }

    public function toggleActiveFertilizationRun(FertilizationRun $run): RedirectResponse
    {
        if (!$run->is_active) {
            FertilizationRun::query()->where('id', '!=', $run->id)->update(['is_active' => false, 'end_date' => now()->toDateString()]);
            $run->update(['is_active' => true, 'end_date' => null]);
            return redirect()->back()->with('success', 'Gübreleme reçetesi aktif edildi.');
        } else {
            $run->update(['is_active' => false, 'end_date' => now()->toDateString()]);
            return redirect()->back()->with('success', 'Gübreleme reçetesi pasife alındı.');
        }
    }

    public function storeFertilizationTankLog(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fertilization_run_id' => 'required|exists:gubre_uygulamalari,id',
            'fertilization_tank_id' => 'required|exists:gubre_tanklari,id',
            'prepared_at' => 'required|date',
            'prepared_by_id' => 'nullable|exists:personeller,id',
            'tank_name' => 'required|string|max:100',
            'notes' => 'nullable|string',
        ]);

        FertilizationTankLog::create($validated);

        return redirect()->back()->with('success', 'Tank hazırlama kaydı oluşturuldu.');
    }

    public function storeIrrigationSchedule(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'schedule_date' => 'required|date',
            'run_number' => 'required|integer|min:1',
            'start_time' => 'required|string',
            'is_fertilized' => 'boolean',
            'fertilization_recipe_id' => 'nullable|exists:gubre_receteleri,id',
            'notes' => 'nullable|string',
        ]);

        $schedule = IrrigationSchedule::updateOrCreate(['id' => $request->id], $validated);

        if ($request->has('valves') && is_array($request->valves)) {
            $schedule->valves()->delete();
            $locId = $request->production_location_id;
            foreach ($request->valves as $v) {
                $valveId = $v['location_valve_id'] ?? $v['valve_id'] ?? null;
                $vLocId = $v['production_location_id'] ?? $locId;
                if (!empty($valveId) && !empty($vLocId) && \App\Models\LocationValve::where('id', $valveId)->exists()) {
                    $schedule->valves()->create([
                        'production_location_id' => $vLocId,
                        'location_valve_id' => $valveId,
                        'duration_minutes' => $v['duration_minutes'] ?? 5,
                        'tank_step_level' => $v['tank_step_level'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Sulama programı kaydedildi.');
    }

    public function storeSprayingApplication(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'application_date' => 'required|date',
            'spraying_recipe_id' => 'required|exists:ilac_receteleri,id',
            'purpose' => 'nullable|string|max:255',
            'applied_by_id' => 'nullable|exists:personeller,id',
            'covered_area_description' => 'nullable|string|max:255',
            'is_tank_finished' => 'boolean',
            'batch_code' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        SprayingApplication::updateOrCreate(['id' => $request->id], $validated);

        return redirect()->back()->with('success', 'İlaçlama uygulaması kaydedildi.');
    }

    public function storeWaterAnalysis(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'water_source_id' => 'required|exists:su_kaynaklari,id',
            'analysis_date' => 'required|date',
            'ph_level' => 'required|numeric|min:0|max:14',
            'ec_level' => 'required|numeric|min:0',
            'chemical_details' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        WaterAnalysisLog::updateOrCreate(['id' => $request->id], $validated);

        return redirect()->back()->with('success', 'Su analizi kaydedildi.');
    }

    public function storeRawWaterControl(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'water_source_id' => 'required|exists:su_kaynaklari,id',
            'control_date' => 'required|date',
            'ec_val' => 'nullable|numeric',
            'ph_val' => 'nullable|numeric',
            'pump_status' => 'required|in:open,closed,faulty',
            'pump_fault_note' => 'nullable|string',
            'active_start_date' => 'nullable|date',
            'passive_start_date' => 'nullable|date',
            'source_switch_reason' => 'nullable|string',
            'is_filter_cleaned' => 'boolean',
            'filter_cleaned_photo' => 'nullable',
            'water_tank_level' => 'required|in:full,half_plus,half_minus,empty',
            'water_tank_photo' => 'nullable',
            'water_tank_note' => 'nullable|string',
            'chlorine_tank_level' => 'required|in:full,half_plus,half_minus,empty',
            'dosing_pump_mode' => 'required|in:auto,manual,faulty',
            'dosing_pump_manual_val' => 'nullable|string',
            'dosing_pump_fault_note' => 'nullable|string',
        ]);

        if ($request->hasFile('filter_cleaned_photo')) {
            $validated['filter_cleaned_photo'] = '/storage/' . $request->file('filter_cleaned_photo')->store('water_controls', 'public');
        } elseif (!$request->filled('filter_cleaned_photo')) {
            unset($validated['filter_cleaned_photo']);
        }

        if ($request->hasFile('water_tank_photo')) {
            $validated['water_tank_photo'] = '/storage/' . $request->file('water_tank_photo')->store('water_controls', 'public');
        } elseif (!$request->filled('water_tank_photo')) {
            unset($validated['water_tank_photo']);
        }

        RawWaterControl::updateOrCreate(['id' => $request->id], $validated);

        return redirect()->back()->with('success', 'Kaynak suyu kontrolü kaydedildi.');
    }

    public function storePurificationControl(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'water_source_id' => 'required|exists:su_kaynaklari,id',
            'control_date' => 'required|date',
            'inlet_pressure_bar' => 'required|numeric|min:0',
            'outlet_pressure_bar' => 'required|numeric|min:0',
            'max_threshold_bar' => 'nullable|numeric|min:0',
        ]);

        $inlet = floatval($validated['inlet_pressure_bar']);
        $outlet = floatval($validated['outlet_pressure_bar']);
        $delta = $inlet - $outlet;
        $maxThresh = floatval($validated['max_threshold_bar'] ?? 1.50);

        $hasWarning = $delta >= $maxThresh;
        $msg = $hasWarning ? "Uyarı: Barometre basınç farkı ($delta bar) üst limiti aştı! Filtre doldu, kontrol edip değiştiriniz." : null;

        PurificationControl::updateOrCreate(
            ['id' => $request->id],
            [
                'water_source_id' => $validated['water_source_id'],
                'control_date' => $validated['control_date'],
                'inlet_pressure_bar' => $inlet,
                'outlet_pressure_bar' => $outlet,
                'delta_pressure_bar' => $delta,
                'max_threshold_bar' => $maxThresh,
                'has_warning' => $hasWarning,
                'warning_message' => $msg,
            ]
        );

        return redirect()->back()->with('success', 'Arıtma suyu kontrolü ve basınç analizi tamamlandı.');
    }

    public function storeCustomerOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'trading_party_id' => 'required|exists:cari_taraflar,id',
            'order_date' => 'required|date',
            'requested_delivery_date' => 'nullable|date',
            'contact_person' => 'nullable|string|max:100',
            'total_amount' => 'numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $order = CustomerOrder::create($validated);

        if ($request->has('items') && is_array($request->items)) {
            foreach ($request->items as $item) {
                if (!empty($item['product_id'])) {
                    $qty = floatval($item['quantity'] ?? 0);
                    $price = floatval($item['unit_price'] ?? 0);
                    $order->items()->create([
                        'product_id' => $item['product_id'],
                        'packaging_definition_id' => $item['packaging_definition_id'] ?? null,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'total_price' => $qty * $price,
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Sipariş başarıyla oluşturuldu.');
    }

    public function updateCustomerOrder(Request $request, CustomerOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'trading_party_id' => 'required|exists:cari_taraflar,id',
            'order_date' => 'required|date',
            'requested_delivery_date' => 'nullable|date',
            'contact_person' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:pending,confirmed,shipped,cancelled',
        ]);

        $order->update($validated);

        if ($request->has('items') && is_array($request->items)) {
            $order->items()->delete();
            $totalAmount = 0;
            foreach ($request->items as $item) {
                if (!empty($item['product_id'])) {
                    $qty = floatval($item['quantity'] ?? 0);
                    $price = floatval($item['unit_price'] ?? 0);
                    $lineTotal = $qty * $price;
                    $totalAmount += $lineTotal;
                    $order->items()->create([
                        'product_id' => $item['product_id'],
                        'packaging_definition_id' => $item['packaging_definition_id'] ?? null,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'total_price' => $lineTotal,
                    ]);
                }
            }
            $order->update(['total_amount' => $totalAmount]);
        }

        return redirect()->back()->with('success', 'Sipariş başarıyla güncellendi.');
    }

    public function updateOrderStatus(Request $request, CustomerOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,shipped,cancelled',
        ]);

        $order->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Sipariş durumu güncellendi.');
    }

    public function storeShipmentDelivery(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_order_id' => 'nullable|exists:musteri_siparisleri,id',
            'trading_party_id' => 'required|exists:cari_taraflar,id',
            'product_id' => 'required|exists:urunler,id',
            'packaging_definition_id' => 'nullable|exists:paketleme_tanimlari,id',
            'shipment_date' => 'required|date',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'numeric|min:0',
            'delivery_type_id' => 'nullable|exists:teslimat_sekilleri,id',
            'vehicle_plate' => 'nullable|string|max:50',
            'driver_name' => 'nullable|string|max:100',
            'driver_phone' => 'nullable|string|max:50',
            'dia_waybill_code' => 'nullable|string|max:100',
            'status' => 'required|in:on_the_way,delivered',
            'notes' => 'nullable|string',
        ]);

        $shipment = ShipmentDelivery::create($validated);

        if (!empty($validated['customer_order_id'])) {
            $order = CustomerOrder::with(['items', 'shipments'])->find($validated['customer_order_id']);
            if ($order) {
                $totalOrderQty = $order->items->sum('quantity');
                $totalShippedQty = $order->shipments->sum('quantity');
                if ($totalOrderQty > 0 && $totalShippedQty >= $totalOrderQty) {
                    $order->update(['status' => 'shipped']);
                } else if ($order->status === 'pending') {
                    $order->update(['status' => 'confirmed']);
                }
            }
        }

        return redirect()->back()->with('success', 'Sevkiyat ve irsaliye kaydı oluşturuldu.');
    }

    public function updateShipmentStatus(Request $request, ShipmentDelivery $shipment): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:on_the_way,delivered',
        ]);

        $shipment->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Sevkiyat durumu güncellendi.');
    }

    public function storeMarketPrice(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|exists:agriculture_market_prices,id',
            'product_id' => 'required|exists:urunler,id',
            'price_date' => 'required|date',
            'unit_price' => 'required|numeric|min:0|max:999999',
            'source_name' => 'required|string|max:255',
        ]);

        MarketPrice::updateOrCreate(
            ['id' => $validated['id'] ?? null],
            [
                'product_id' => $validated['product_id'],
                'price_date' => $validated['price_date'],
                'unit_price' => $validated['unit_price'],
                'source_name' => $validated['source_name'],
            ]
        );

        return redirect()->back()->with('success', 'Piyasa fiyatı kaydedildi.');
    }

    public function storeDailyWorkSheet(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'work_date' => 'required|date',
            'company_id' => 'required|exists:firmalar,id',
            'production_location_id' => 'required|exists:uretim_yerleri,id',
            'has_external_workers' => 'boolean',
            'has_internal_workers' => 'boolean',
            'storage_destination' => 'required|in:cold_storage,direct_sale,warehouse,merchant',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();
        $personnel = \App\Models\Personnel::where('user_id', $user->id)->first();
        if (!$user->is_admin && $personnel) {
            if (!$personnel->can_enter_backdated_data) {
                $today = now()->toDateString();
                if ($validated['work_date'] < $today) {
                    return redirect()->back()->withErrors(['work_date' => 'Geçmişe dönük veri girişi yetkiniz bulunmamaktadır.']);
                }
            }
        }

        $status = $user->is_admin ? 'approved' : 'submitted';
        $submittedBy = $personnel ? $personnel->id : null;

        $sheet = DailyWorkSheet::create([
            'work_date' => $validated['work_date'],
            'company_id' => $validated['company_id'],
            'production_location_id' => $validated['production_location_id'],
            'has_external_workers' => $validated['has_external_workers'] ?? false,
            'has_internal_workers' => $validated['has_internal_workers'] ?? true,
            'storage_destination' => $validated['storage_destination'],
            'created_by_id' => Auth::id(),
            'updated_by_id' => Auth::id(),
            'notes' => $validated['notes'] ?? null,
            'status' => $status,
            'submitted_by_personnel_id' => $submittedBy,
        ]);

        if ($request->has('crew_leaders') && is_array($request->crew_leaders)) {
            foreach ($request->crew_leaders as $cl) {
                if (!empty($cl['crew_leader_id'])) {
                    $leaderObj = CrewLeader::find($cl['crew_leader_id']);
                    $workerCount = intval($cl['worker_count'] ?? 1);
                    $carCount = intval($cl['car_count'] ?? 1);
                    $driverType = $cl['second_driver_fee_type'] ?? 'leader_rate';
                    $overtime = floatval($cl['overtime_hours'] ?? 0);
                    $overtimeEndTime = $cl['overtime_end_time'] ?? null;
                    $extraWage = floatval($cl['extra_wage_per_worker'] ?? 0);
                    $ramadanMeals = isset($cl['ramadan_meal_count']) ? intval($cl['ramadan_meal_count']) : null;
                    $foodOverride = isset($cl['is_food_included_override']) ? !!$cl['is_food_included_override'] : null;

                    $dailyRate = $leaderObj ? floatval($leaderObj->daily_wage) : 500;
                    $multiplier = $leaderObj ? floatval($leaderObj->multiplier) : 1.0;
                    $travelFee = $leaderObj ? floatval($leaderObj->travel_fee_per_car) : 100;
                    $mealFee = ($ramadanMeals !== null) ? ($ramadanMeals * 65) : ($workerCount * 65);

                    $secondDriverFee = 0;
                    if ($carCount > 1) {
                        if ($driverType === 'leader_rate') {
                            $secondDriverFee = $dailyRate * $multiplier;
                        } elseif ($driverType === 'normal_worker') {
                            $secondDriverFee = $dailyRate;
                        }
                    }

                    $workersTotal = $workerCount * ($dailyRate + $extraWage);
                    $leaderBaseFee = $dailyRate * $multiplier;
                    $totalWage = $workersTotal + $leaderBaseFee + $secondDriverFee + ($carCount * $travelFee) + $mealFee + ($overtime * 75);

                    $sheet->crewLeaders()->create([
                        'crew_leader_id' => $cl['crew_leader_id'],
                        'worker_count' => $workerCount,
                        'car_count' => $carCount,
                        'second_driver_fee_type' => $driverType,
                        'overtime_hours' => $overtime,
                        'overtime_end_time' => $overtimeEndTime,
                        'extra_wage_per_worker' => $extraWage,
                        'travel_fee' => $travelFee,
                        'meal_fee' => $mealFee,
                        'ramadan_meal_count' => $ramadanMeals,
                        'is_food_included_override' => $foodOverride,
                        'calculated_wage_total' => $totalWage,
                        'dia_cari_code' => $leaderObj->dia_cari_code ?? null,
                    ]);
                }
            }
        }

        if ($request->has('assignments') && is_array($request->assignments)) {
            foreach ($request->assignments as $a) {
                if (!empty($a['job_type_id'])) {
                    $sheet->workerAssignments()->create([
                        'crew_leader_id' => $a['crew_leader_id'] ?? null,
                        'worker_id' => $a['worker_id'] ?? null,
                        'personnel_id' => $a['personnel_id'] ?? null,
                        'job_type_id' => $a['job_type_id'],
                        'start_time' => $a['start_time'] ?? '08:00',
                        'end_time' => $a['end_time'] ?? '17:00',
                        'break_minutes' => $a['break_minutes'] ?? 60,
                    ]);
                }
            }
        }

        if ($request->has('harvest_items') && is_array($request->harvest_items)) {
            foreach ($request->harvest_items as $h) {
                if (!empty($h['product_id'])) {
                    $qty = floatval($h['quantity'] ?? 0);
                    $price = floatval($h['unit_price'] ?? 0);

                    $sheet->harvestItems()->create([
                        'product_id' => $h['product_id'],
                        'product_subtype_id' => $h['product_subtype_id'] ?? null,
                        'packaging_definition_id' => $h['packaging_definition_id'] ?? null,
                        'package_count' => $h['package_count'] ?? 0,
                        'quantity' => $qty,
                        'unit_symbol' => $h['unit_symbol'] ?? 'kg',
                        'unit_price' => $price,
                        'total_revenue' => $qty * $price,
                        'crop_type' => $h['crop_type'] ?? 'strawberry',
                        'banana_bunch_count' => $h['banana_bunch_count'] ?? null,
                        'farm_scale_kg' => $h['farm_scale_kg'] ?? null,
                        'merchant_scale_1st_kg' => $h['merchant_scale_1st_kg'] ?? null,
                        'merchant_scale_2nd_kg' => $h['merchant_scale_2nd_kg'] ?? null,
                        'is_merchant_weighed' => isset($h['merchant_scale_1st_kg']),
                        'weighed_by_id' => Personnel::where('user_id', Auth::id())->value('id'),
                    ]);
                }
            }
        }

        $loc = ProductionLocation::find($validated['production_location_id']);
        $diaWarehouse = $loc ? $loc->dia_warehouse_code : null;
        $successMsg = 'Günlük işçi ve hasat formu kaydedildi.' . ($diaWarehouse ? " Dia ($diaWarehouse) deposuna üretim fişi oluşturularak stok girişi aktarıldı." : '');

        return redirect()->back()->with('success', $successMsg);
    }

    public function updateBananaWeights(Request $request, DailyWorkSheetHarvestItem $item): RedirectResponse
    {
        $validated = $request->validate([
            'merchant_scale_1st_kg' => 'required|numeric|min:0',
            'merchant_scale_2nd_kg' => 'required|numeric|min:0',
            'unit_price' => 'numeric|min:0',
        ]);

        $firstKg = floatval($validated['merchant_scale_1st_kg']);
        $secondKg = floatval($validated['merchant_scale_2nd_kg']);
        $totalKg = $firstKg + $secondKg;
        $price = floatval($validated['unit_price'] ?? $item->unit_price);

        $item->update([
            'merchant_scale_1st_kg' => $firstKg,
            'merchant_scale_2nd_kg' => $secondKg,
            'quantity' => $totalKg,
            'total_revenue' => $totalKg * $price,
            'is_merchant_weighed' => true,
            'weighed_by_id' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Muz tüccar kantar kilo verileri güncellendi ve stok girişi tamamlandı.');
    }

    public function approveDailyWorkSheet(Request $request, DailyWorkSheet $sheet): RedirectResponse
    {
        $user = Auth::user();
        $personnel = \App\Models\Personnel::where('user_id', $user->id)->first();
        
        $canApprove = $user->is_admin;
        if ($sheet->submitted_by_personnel_id) {
            $sender = \App\Models\Personnel::find($sheet->submitted_by_personnel_id);
            if ($sender && $personnel && $sender->parent_personnel_id == $personnel->id) {
                $canApprove = true;
            }
        }
        
        if (!$canApprove) {
            return redirect()->back()->withErrors(['error' => 'Bu formu onaylamaya veya reddetmeye yetkiniz yoktur. Sadece belirlenen üst amir veya admin işlem yapabilir.']);
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'rejection_note' => 'required_if:action,reject|nullable|string|max:1000',
        ]);

        if ($validated['action'] === 'approve') {
            $sheet->update([
                'status' => 'approved',
                'approved_by_user_id' => Auth::id(),
                'approved_at' => now(),
                'rejection_note' => null,
            ]);
            $msg = 'Form başarıyla onaylandı.';
        } else {
            $sheet->update([
                'status' => 'rejected',
                'approved_by_user_id' => Auth::id(),
                'approved_at' => now(),
                'rejection_note' => $validated['rejection_note'],
            ]);
            $msg = 'Form reddedildi.';
        }

        return redirect()->back()->with('success', $msg);
    }

    public function destroyWorkPlan(WorkPlan $workPlan): RedirectResponse
    {
        $workPlan->delete();
        return redirect()->back()->with('success', 'İş planı silindi.');
    }

    public function destroyFertilizationRun(FertilizationRun $run): RedirectResponse
    {
        $run->delete();
        return redirect()->back()->with('success', 'Gübreleme reçetesi kaydı silindi.');
    }

    public function destroyIrrigationSchedule(IrrigationSchedule $schedule): RedirectResponse
    {
        $schedule->delete();
        return redirect()->back()->with('success', 'Sulama programı kaydı silindi.');
    }

    public function destroySprayingApplication(SprayingApplication $app): RedirectResponse
    {
        $app->delete();
        return redirect()->back()->with('success', 'İlaçlama kaydı silindi.');
    }

    public function destroyWaterAnalysis(WaterAnalysisLog $log): RedirectResponse
    {
        $log->delete();
        return redirect()->back()->with('success', 'Su analiz kaydı silindi.');
    }

    public function destroyRawWaterControl(RawWaterControl $control): RedirectResponse
    {
        $control->delete();
        return redirect()->back()->with('success', 'Kaynak suyu kontrol kaydı silindi.');
    }

    public function destroyPurificationControl(PurificationControl $control): RedirectResponse
    {
        $control->delete();
        return redirect()->back()->with('success', 'Arıtma kontrol kaydı silindi.');
    }

    public function destroyCustomerOrder(CustomerOrder $order): RedirectResponse
    {
        $order->delete();
        return redirect()->back()->with('success', 'Sipariş kaydı silindi.');
    }

    public function destroyShipmentDelivery(ShipmentDelivery $shipment): RedirectResponse
    {
        $shipment->delete();
        return redirect()->back()->with('success', 'Sevkiyat kaydı silindi.');
    }

    public function destroyDailyWorkSheet(DailyWorkSheet $sheet): RedirectResponse
    {
        $sheet->delete();
        return redirect()->back()->with('success', 'Günlük işçi formu silindi.');
    }

    public function destroyMarketPrice(MarketPrice $price): RedirectResponse
    {
        $price->delete();
        return redirect()->back()->with('success', 'Piyasa fiyat kaydı silindi.');
    }
}
