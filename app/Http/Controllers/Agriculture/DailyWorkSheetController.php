<?php

namespace App\Http\Controllers\Agriculture;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Models\DailyWorkSheet;
use App\Models\DailyWorkSheetCrewLeader;
use App\Models\DailyWorkSheetHarvestItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Personnel;
use App\Models\CrewLeader;
use App\Models\MarketPrice;
use App\Models\ProductionLocation;

class DailyWorkSheetController extends Controller
{
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
        $personnel = Personnel::where('user_id', $user->id)->first();
        if (!$user->is_admin && $personnel) {
            $allowedCompanies = $personnel->company_ids ?? [];
            if (!in_array($validated['company_id'], $allowedCompanies)) {
                return redirect()->back()->withErrors(['company_id' => 'Bu şirkete veri girişi yetkiniz bulunmamaktadır.']);
            }
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
                    $workerCount = intval($cl['worker_count'] ?? 1) + intval($cl['extra_worker_count'] ?? 0);
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
                    if ($price <= 0 && !empty($h['product_id'])) {
                        $latestPrice = MarketPrice::where('product_id', $h['product_id'])->latest('price_date')->value('unit_price');
                        if ($latestPrice) {
                            $price = floatval($latestPrice);
                        }
                    }

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

    public function destroyDailyWorkSheet(DailyWorkSheet $sheet): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->is_admin) {
            if ($sheet->status === 'approved') {
                return redirect()->back()->withErrors(['error' => 'Onaylanmış günlük işçi formu silinemez.']);
            }
            $personnel = Personnel::where('user_id', $user->id)->first();
            $allowedCompanies = $personnel ? ($personnel->company_ids ?? []) : [];
            if (!in_array($sheet->company_id, $allowedCompanies) || ($sheet->created_by_id !== $user->id && $sheet->submitted_by_personnel_id !== ($personnel->id ?? null))) {
                return redirect()->back()->withErrors(['error' => 'Bu formu silme yetkiniz bulunmamaktadır.']);
            }
        }

        $sheet->delete();
        return redirect()->back()->with('success', 'Günlük işçi formu silindi.');
    }

}
