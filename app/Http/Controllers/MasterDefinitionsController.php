<?php

namespace App\Http\Controllers;

use App\Models\CateringSupplier;
use App\Models\Company;
use App\Models\CrewLeader;
use App\Models\DeliveryType;
use App\Models\FertilizationRecipe;
use App\Models\Filter;
use App\Models\JobType;
use App\Models\PackagingDefinition;
use App\Models\Personnel;
use App\Models\Product;
use App\Models\ProductionLocation;
use App\Models\SprayingRecipe;
use App\Models\TradingParty;
use App\Models\UnitDefinition;
use App\Models\WaterSource;
use App\Models\Worker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MasterDefinitionsController extends Controller
{
    public function index(Request $request): Response
    {
        $activeTab = $request->query('tab', 'companies');

        return Inertia::render('Definitions/Index', [
            'activeTab' => $activeTab,
            'companies' => Company::with(['products', 'productionLocations'])->latest()->get(),
            'personnels' => Personnel::with(['user', 'parent', 'subordinates'])->latest()->get(),
            'tradingParties' => TradingParty::latest()->get(),
            'deliveryTypes' => DeliveryType::latest()->get(),
            'productionLocations' => ProductionLocation::with(['company', 'sections', 'valves'])->latest()->get(),
            'products' => Product::with(['companies', 'subtypes', 'units', 'packagings'])->latest()->get(),
            'jobTypes' => JobType::with('units')->latest()->get(),
            'units' => UnitDefinition::latest()->get(),
            'packagings' => PackagingDefinition::with(['product', 'unit'])->latest()->get(),
            'crewLeaders' => CrewLeader::with('workers')->latest()->get(),
            'workers' => Worker::with('crewLeader')->latest()->get(),
            'cateringSuppliers' => CateringSupplier::latest()->get(),
            'fertilizationRecipes' => FertilizationRecipe::with('tanks.items')->latest()->get(),
            'sprayingRecipes' => SprayingRecipe::with('items')->latest()->get(),
            'waterSources' => WaterSource::with(['productionLocation.company'])->latest()->get(),
            'filters' => Filter::with(['productionLocation.company', 'waterSources'])->latest()->get(),
        ]);
    }

    public function storeCompany(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:firmalar,code,' . $request->id,
            'tax_number' => 'nullable|string|max:100',
            'dia_company_code' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        Company::updateOrCreate(['id' => $request->id], $validated);

        return redirect()->back()->with('success', 'Firma kaydedildi.');
    }

    public function destroyCompany(Company $company): RedirectResponse
    {
        $company->delete();
        return redirect()->back()->with('success', 'Firma silindi.');
    }

    public function syncDiaCompanies(): RedirectResponse
    {
        Company::updateOrCreate(['code' => 'SASA-01'], [
            'name' => 'SASA Tarım Üretim A.Ş.',
            'tax_number' => '7890123456',
            'dia_company_code' => 'DIA-SASA-01',
            'is_active' => true,
        ]);

        Company::updateOrCreate(['code' => 'SASA-02'], [
            'name' => 'SASA Sera İletişim & Pazarlama Ltd. Şti.',
            'tax_number' => '7890987654',
            'dia_company_code' => 'DIA-SASA-02',
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Firmalar Dia ERP Web Servisinden başarıyla senkronize edildi.');
    }

    public function storePersonnel(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'password' => 'nullable|string|min:1',
            'role_title' => 'nullable|string|max:100',
            'parent_personnel_id' => 'nullable|exists:personeller,id',
            'company_ids' => 'nullable|array',
            'permissions' => 'nullable|array',
            'can_enter_backdated_data' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $personnel = Personnel::find($request->id);
        $userId = $personnel?->user_id;

        if (!empty($validated['email'])) {
            if ($userId) {
                $user = \App\Models\User::find($userId);
                if ($user) {
                    $user->name = $validated['first_name'] . ' ' . $validated['last_name'];
                    $user->email = $validated['email'];
                    if (!empty($validated['password'])) {
                        $user->password = bcrypt($validated['password']);
                    }
                    $user->save();
                }
            } else {
                $user = \App\Models\User::firstOrCreate(
                    ['email' => $validated['email']],
                    [
                        'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                        'password' => bcrypt($validated['password'] ?? 'password'),
                        'email_verified_at' => now(),
                        'is_admin' => false,
                    ]
                );
                $userId = $user->id;
            }
        }

        $personnelData = collect($validated)->except(['email', 'password'])->toArray();
        $personnelData['user_id'] = $userId;

        Personnel::updateOrCreate(
            ['id' => $request->id],
            $personnelData
        );

        return redirect()->back()->with('success', 'Personel ve kullanıcı erişim tanımı kaydedildi.');
    }

    public function destroyPersonnel(Personnel $personnel): RedirectResponse
    {
        $personnel->delete();
        return redirect()->back()->with('success', 'Personel silindi.');
    }

    public function storeTradingParty(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:buyer,seller,both',
            'dia_cari_code' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'default_transport_fee' => 'numeric|min:0',
        ]);

        TradingParty::updateOrCreate(['id' => $request->id], $validated);

        return redirect()->back()->with('success', 'Cari taraf kaydedildi.');
    }

    public function destroyTradingParty(TradingParty $tradingParty): RedirectResponse
    {
        $tradingParty->delete();
        return redirect()->back()->with('success', 'Cari taraf silindi.');
    }

    public function syncDiaTradingParties(): RedirectResponse
    {
        TradingParty::updateOrCreate(['dia_cari_code' => 'CAR-MUSTERI-01'], [
            'name' => 'Migros Ticaret A.Ş.',
            'type' => 'buyer',
            'tax_number' => '6220033441',
            'phone' => '08502004000',
            'email' => 'siparis@migros.com.tr',
            'address' => 'İstanbul Dağıtım Merkezi',
            'default_transport_fee' => 500,
        ]);

        TradingParty::updateOrCreate(['dia_cari_code' => 'CAR-TEDARIKCI-01'], [
            'name' => 'Toros Gübre Sanayi A.Ş.',
            'type' => 'seller',
            'tax_number' => '8500112233',
            'phone' => '02123550000',
            'email' => 'satis@toros.com.tr',
            'address' => 'Mersin Fabrikası',
            'default_transport_fee' => 0,
        ]);

        return redirect()->back()->with('success', 'Dia carileri (Alıcı ve Satıcılar) başarıyla çekildi ve güncellendi.');
    }

    public function storeDeliveryType(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:teslimat_sekilleri,code,' . $request->id,
            'transport_by' => 'required|in:customer,company',
            'is_fee_included' => 'boolean',
            'extra_fee' => 'nullable|numeric|min:0',
        ]);

        DeliveryType::updateOrCreate(['id' => $request->id], $validated);

        return redirect()->back()->with('success', 'Teslim şekli kaydedildi.');
    }

    public function destroyDeliveryType(DeliveryType $deliveryType): RedirectResponse
    {
        $deliveryType->delete();
        return redirect()->back()->with('success', 'Teslim şekli silindi.');
    }

    public function storeProductionLocation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:firmalar,id',
            'name' => 'required|string|max:255',
            'location_type' => 'required|in:greenhouse,open_field,mixed',
            'dia_branch_code' => 'nullable|string|max:100',
            'dia_warehouse_code' => 'nullable|string|max:100',
            'total_area_dekar' => 'numeric|min:0',
            'approx_plant_count' => 'nullable|integer|min:0',
        ]);

        $location = ProductionLocation::updateOrCreate(['id' => $request->id], $validated);

        if ($request->has('sections') && is_array($request->sections)) {
            $existingSecIds = [];
            foreach ($request->sections as $sec) {
                if (!empty($sec['name'])) {
                    $secData = [
                        'name' => $sec['name'],
                        'section_type' => $sec['section_type'] ?? 'greenhouse',
                        'area_dekar' => $sec['area_dekar'] ?? 0,
                        'tunnel_count' => $sec['tunnel_count'] ?? 0,
                        'table_stand_count' => $sec['table_stand_count'] ?? 0,
                        'approx_plant_count' => $sec['approx_plant_count'] ?? null,
                    ];
                    if (!empty($sec['id']) && $existing = $location->sections()->find($sec['id'])) {
                        $existing->update($secData);
                        $existingSecIds[] = $existing->id;
                    } else {
                        $newSec = $location->sections()->create($secData);
                        $existingSecIds[] = $newSec->id;
                    }
                }
            }
            $location->sections()->whereNotIn('id', $existingSecIds)->delete();
        }

        if ($request->has('valves') && is_array($request->valves)) {
            $existingValveIds = [];
            foreach ($request->valves as $valve) {
                if (!empty($valve['name'])) {
                    $valveData = [
                        'production_section_id' => $valve['production_section_id'] ?? null,
                        'valve_number' => $valve['valve_number'] ?? 'V-01',
                        'name' => $valve['name'],
                        'duty' => $valve['duty'] ?? 'irrigation',
                        'description' => $valve['description'] ?? null,
                    ];
                    if (!empty($valve['id']) && $existing = $location->valves()->find($valve['id'])) {
                        $existing->update($valveData);
                        $existingValveIds[] = $existing->id;
                    } else {
                        $newValve = $location->valves()->create($valveData);
                        $existingValveIds[] = $newValve->id;
                    }
                }
            }
            $location->valves()->whereNotIn('id', $existingValveIds)->delete();
        }

        return redirect()->back()->with('success', 'Üretim yeri detaylarıyla kaydedildi.');
    }

    public function destroyProductionLocation(ProductionLocation $productionLocation): RedirectResponse
    {
        $productionLocation->delete();
        return redirect()->back()->with('success', 'Üretim yeri silindi.');
    }

    public function storeProduct(Request $request): RedirectResponse
    {
        if (empty($request->code) && !empty($request->name)) {
            $slug = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $request->name));
            $codeBase = 'PRD-' . (substr($slug, 0, 4) ?: 'PRD');
            $code = $codeBase;
            $counter = 1;
            while (\App\Models\Product::where('code', $code)->where('id', '!=', $request->id)->exists()) {
                $code = $codeBase . '-' . $counter++;
            }
            $request->merge(['code' => $code]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:urunler,code,' . $request->id,
            'product_type' => 'required|in:produced,consumed,both',
            'dia_stock_code' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'company_ids' => 'nullable|array',
            'unit_ids' => 'nullable|array',
        ]);

        $product = Product::updateOrCreate(['id' => $request->id], [
            'name' => $validated['name'],
            'code' => $validated['code'],
            'product_type' => $validated['product_type'],
            'dia_stock_code' => $validated['dia_stock_code'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        if (isset($validated['company_ids'])) {
            $product->companies()->sync($validated['company_ids']);
        }
        if (isset($validated['unit_ids'])) {
            $product->units()->sync($validated['unit_ids']);
        }

        if ($request->has('subtypes') && is_array($request->subtypes)) {
            $existingSubtypeIds = [];
            foreach ($request->subtypes as $st) {
                if (!empty($st['name'])) {
                    $stData = [
                        'name' => $st['name'],
                        'code' => $st['code'] ?? null,
                    ];
                    if (!empty($st['id']) && $existing = $product->subtypes()->find($st['id'])) {
                        $existing->update($stData);
                        $existingSubtypeIds[] = $existing->id;
                    } else {
                        $newSt = $product->subtypes()->create($stData);
                        $existingSubtypeIds[] = $newSt->id;
                    }
                }
            }
            $product->subtypes()->whereNotIn('id', $existingSubtypeIds)->delete();
        }

        return redirect()->back()->with('success', 'Ürün kaydedildi.');
    }

    public function destroyProduct(Product $product): RedirectResponse
    {
        $product->delete();
        return redirect()->back()->with('success', 'Ürün silindi.');
    }

    public function storeJobType(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:is_tanimlari,code,' . $request->id,
            'form_type' => 'required|string|max:100',
            'description' => 'nullable|string',
            'unit_ids' => 'nullable|array',
        ]);

        $jobType = JobType::updateOrCreate(['id' => $request->id], $validated);

        if (isset($validated['unit_ids'])) {
            $jobType->units()->sync($validated['unit_ids']);
        }

        return redirect()->back()->with('success', 'İş tanımı kaydedildi.');
    }

    public function destroyJobType(JobType $jobType): RedirectResponse
    {
        $jobType->delete();
        return redirect()->back()->with('success', 'İş tanımı silindi.');
    }

    public function storeUnitDefinition(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'symbol' => 'required|string|max:20',
            'unit_category' => 'required|in:quantity,area,count',
        ]);

        UnitDefinition::updateOrCreate(['id' => $request->id], $validated);

        return redirect()->back()->with('success', 'Birim kaydedildi.');
    }

    public function destroyUnitDefinition(UnitDefinition $unitDefinition): RedirectResponse
    {
        $unitDefinition->delete();
        return redirect()->back()->with('success', 'Birim silindi.');
    }

    public function storePackagingDefinition(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:100',
            'product_id' => 'nullable|exists:urunler,id',
            'dia_stock_code' => 'nullable|string|max:100',
            'capacity_qty' => 'nullable|numeric|min:0',
            'unit_id' => 'nullable|exists:birim_tanimlari,id',
        ]);

        PackagingDefinition::updateOrCreate(['id' => $request->id], $validated);

        return redirect()->back()->with('success', 'Paketleme tanımı kaydedildi.');
    }

    public function destroyPackagingDefinition(PackagingDefinition $packagingDefinition): RedirectResponse
    {
        $packagingDefinition->delete();
        return redirect()->back()->with('success', 'Paketleme tanımı silindi.');
    }

    public function storeCrewLeader(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'identity_number' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:50',
            'origin_city' => 'nullable|string|max:100',
            'daily_wage' => 'numeric|min:0',
            'multiplier' => 'numeric|min:0.1',
            'is_leader_fee_included' => 'boolean',
            'min_car_requirement' => 'integer|min:0',
            'travel_fee_per_car' => 'numeric|min:0',
            'is_food_included' => 'boolean',
            'dia_cari_code' => 'nullable|string|max:100',
        ]);

        CrewLeader::updateOrCreate(['id' => $request->id], $validated);

        return redirect()->back()->with('success', 'Çavuş kaydedildi.');
    }

    public function destroyCrewLeader(CrewLeader $crewLeader): RedirectResponse
    {
        $crewLeader->delete();
        return redirect()->back()->with('success', 'Çavuş silindi.');
    }

    public function storeWorker(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'crew_leader_id' => 'nullable|exists:cavuslar,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'identity_number' => 'nullable|string|max:20',
            'performance_rating' => 'integer|min:1|max:5',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Worker::updateOrCreate(['id' => $request->id], $validated);

        return redirect()->back()->with('success', 'İşçi kaydedildi.');
    }

    public function destroyWorker(Worker $worker): RedirectResponse
    {
        $worker->delete();
        return redirect()->back()->with('success', 'İşçi silindi.');
    }

    public function storeCateringSupplier(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_title' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'meal_unit_price' => 'numeric|min:0',
            'is_vat_included' => 'boolean',
            'dia_cari_code' => 'nullable|string|max:100',
        ]);

        CateringSupplier::updateOrCreate(['id' => $request->id], $validated);

        return redirect()->back()->with('success', 'Yemek tedarikçisi kaydedildi.');
    }

    public function destroyCateringSupplier(CateringSupplier $cateringSupplier): RedirectResponse
    {
        $cateringSupplier->delete();
        return redirect()->back()->with('success', 'Yemek tedarikçisi silindi.');
    }

    public function storeFertilizationRecipe(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'creation_date' => 'required|date',
            'duration_condition' => 'nullable|string|max:255',
            'creator_name' => 'nullable|string|max:100',
            'water_ph' => 'nullable|numeric|min:0|max:14',
            'water_ec' => 'nullable|numeric|min:0',
            'water_notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['is_active'])) {
            FertilizationRecipe::query()->update(['is_active' => false]);
        }

        $recipe = FertilizationRecipe::updateOrCreate(['id' => $request->id], $validated);

        if ($request->has('tanks') && is_array($request->tanks)) {
            $recipe->tanks()->delete();
            foreach ($request->tanks as $t) {
                if (!empty($t['tank_name'])) {
                    $tank = $recipe->tanks()->create([
                        'tank_name' => $t['tank_name'],
                        'capacity_liters' => $t['capacity_liters'] ?? 0,
                    ]);

                    if (isset($t['items']) && is_array($t['items'])) {
                        foreach ($t['items'] as $item) {
                            if (!empty($item['product_name'])) {
                                $tank->items()->create([
                                    'product_name' => $item['product_name'],
                                    'brand' => $item['brand'] ?? null,
                                    'quantity' => $item['quantity'] ?? 0,
                                    'unit' => $item['unit'] ?? 'gr',
                                    'usage_purpose' => $item['usage_purpose'] ?? null,
                                    'description' => $item['description'] ?? null,
                                ]);
                            }
                        }
                    }
                }
            }
        }

        return redirect()->back()->with('success', 'Gübreleme reçetesi kaydedildi.');
    }

    public function destroyFertilizationRecipe(FertilizationRecipe $recipe): RedirectResponse
    {
        $recipe->delete();
        return redirect()->back()->with('success', 'Gübreleme reçetesi silindi.');
    }

    public function toggleActiveFertilizationRecipe(FertilizationRecipe $recipe): RedirectResponse
    {
        FertilizationRecipe::query()->update(['is_active' => false]);
        $recipe->update(['is_active' => true]);

        return redirect()->back()->with('success', "'{$recipe->name}' reçetesi aktif edildi. Diğer tüm reçeteler pasife alındı.");
    }

    public function storeSprayingRecipe(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'creation_date' => 'required|date',
            'usage_time' => 'nullable|string|max:255',
            'usage_purpose' => 'nullable|string|max:255',
            'water_volume_liters' => 'numeric|min:0',
            'application_method' => 'required|in:sprayer_machine,fertigation_tank,backpack_pump,other',
            'is_active' => 'boolean',
        ]);

        $recipe = SprayingRecipe::updateOrCreate(['id' => $request->id], $validated);

        if ($request->has('items') && is_array($request->items)) {
            $recipe->items()->delete();
            foreach ($request->items as $item) {
                if (!empty($item['product_name'])) {
                    $recipe->items()->create([
                        'product_name' => $item['product_name'],
                        'brand' => $item['brand'] ?? null,
                        'quantity' => $item['quantity'] ?? 0,
                        'unit' => $item['unit'] ?? 'ml',
                        'notes' => $item['notes'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'İlaçlama reçetesi kaydedildi.');
    }

    public function destroySprayingRecipe(SprayingRecipe $recipe): RedirectResponse
    {
        $recipe->delete();
        return redirect()->back()->with('success', 'İlaçlama reçetesi silindi.');
    }

    public function storeWaterSource(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'production_location_id' => 'required|exists:uretim_yerleri,id',
            'name' => 'required|string|max:255',
            'active_cycle_minutes' => 'integer|min:1',
            'passive_cycle_minutes' => 'integer|min:1',
            'requires_photo_verification' => 'boolean',
            'is_active' => 'boolean',
        ]);

        WaterSource::updateOrCreate(['id' => $request->id], $validated);

        return redirect()->back()->with('success', 'Su kaynağı kaydedildi.');
    }

    public function destroyWaterSource(WaterSource $waterSource): RedirectResponse
    {
        $waterSource->delete();
        return redirect()->back()->with('success', 'Su kaynağı silindi.');
    }

    public function storeFilter(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'production_location_id' => 'required|exists:uretim_yerleri,id',
            'name' => 'required|string|max:255',
            'cleaning_cycle_days' => 'integer|min:1',
            'requires_photo_verification' => 'boolean',
            'is_active' => 'boolean',
            'water_source_ids' => 'nullable|array',
        ]);

        $filter = Filter::updateOrCreate(['id' => $request->id], $validated);

        if (isset($validated['water_source_ids'])) {
            $filter->waterSources()->sync($validated['water_source_ids']);
        }

        return redirect()->back()->with('success', 'Filtre kaydedildi.');
    }

    public function destroyFilter(Filter $filter): RedirectResponse
    {
        $filter->delete();
        return redirect()->back()->with('success', 'Filtre silindi.');
    }
}
