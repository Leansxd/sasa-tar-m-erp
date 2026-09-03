<?php

namespace App\Http\Controllers;

use App\Models\CustomerOrder;
use App\Models\DailyWorkSheet;
use App\Models\DailyWorkSheetHarvestItem;
use App\Models\DailyWorkSheetCrewLeader;
use App\Models\FertilizationRun;
use App\Models\MarketPrice;
use App\Models\ProductionLocation;
use App\Models\PurificationControl;
use App\Models\WorkPlan;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $isAdmin = !!$user->is_admin;
        $personnel = \App\Models\Personnel::where('user_id', $user->id)->first();
        $permissions = $isAdmin ? ['tesis', 'uretim', 'teknik', 'operasyon', 'raporlar', 'tanimlamalar'] : ($personnel->permissions ?? []);
        $companyIds = $isAdmin ? null : ($personnel->company_ids ?? []);

        $hasReports = in_array('raporlar', $permissions);

        $activeFertRun = FertilizationRun::with('recipe')->where('is_active', true)->first();

        $locationQuery = ProductionLocation::query();
        if ($companyIds !== null) {
            $locationQuery->whereIn('company_id', $companyIds);
        }
        $totalDekar = $hasReports ? (clone $locationQuery)->sum('total_area_dekar') : 0;
        $locationCount = $hasReports ? (clone $locationQuery)->count() : 0;

        $workPlanQuery = WorkPlan::query();
        if ($companyIds !== null) {
            $workPlanQuery->whereHas('productionLocation', fn ($q) => $q->whereIn('company_id', $companyIds));
        }
        $pendingPlans = (clone $workPlanQuery)->where('status', 'pending')->count();
        $inProgressPlans = (clone $workPlanQuery)->where('status', 'in_progress')->count();
        $completedPlans = (clone $workPlanQuery)->where('status', 'completed')->count();

        $allSheetsQuery = DailyWorkSheet::with([
            'company',
            'productionLocation.company',
            'crewLeaders.crewLeader',
            'harvestItems.product'
        ])->where('status', 'approved');

        if ($companyIds !== null) {
            $allSheetsQuery->whereIn('company_id', $companyIds);
        }

        $allApprovedSheets = $hasReports ? $allSheetsQuery->orderBy('work_date', 'asc')->get() : collect();

        $todayStr = Carbon::today()->toDateString();
        $sevenDaysAgo = Carbon::today()->subDays(6)->toDateString();
        $thirtyDaysAgo = Carbon::today()->subDays(29)->toDateString();

        $analytics = [
            'today' => $this->calculatePeriodMetrics($allApprovedSheets->filter(fn ($s) => $s->work_date->toDateString() === $todayStr)),
            'week' => $this->calculatePeriodMetrics($allApprovedSheets->filter(fn ($s) => $s->work_date->toDateString() >= $sevenDaysAgo)),
            'month' => $this->calculatePeriodMetrics($allApprovedSheets->filter(fn ($s) => $s->work_date->toDateString() >= $thirtyDaysAgo)),
            'all' => $this->calculatePeriodMetrics($allApprovedSheets),
        ];

        $dailyTrends = $this->calculateDailyTrends($allApprovedSheets);

        $recentSheets = $hasReports ? $allApprovedSheets->sortByDesc('work_date')->take(5)->values() : [];
        $recentOrders = $hasReports ? CustomerOrder::with('tradingParty')->latest()->take(5)->get() : [];
        $recentMarketPrices = MarketPrice::with('product')->latest()->take(4)->get();
        $pressureWarnings = PurificationControl::where('has_warning', true)->count();

        $weatherData = [
            'temp_c' => 18.5,
            'humidity' => 64,
            'wind_kmh' => 12,
            'condition' => 'Parçalı Bulutlu',
            'frost_risk' => false,
            'rain_mm' => 0.0,
            'et0_evapotranspiration' => 3.8,
        ];

        $phiWarnings = [
            [
                'location' => 'Sera A - Çilek Tünelleri',
                'pesticide' => 'Ortiva (Azoxystrobin)',
                'applied_date' => now()->subDays(2)->toDateString(),
                'phi_days' => 7,
                'remaining_days' => 5,
                'status' => 'restricted',
                'message' => 'Hasat Edilemez (Gıda Güvenliği İlaç Bekleme Süresi)',
            ],
            [
                'location' => 'Sera B - Muz Bloku',
                'pesticide' => 'Signum (Boscalid)',
                'applied_date' => now()->subDays(6)->toDateString(),
                'phi_days' => 7,
                'remaining_days' => 1,
                'status' => 'warning',
                'message' => 'Son 1 Gün Bekleme Süresi Kaldı',
            ],
        ];

        return Inertia::render('Dashboard', [
            'stats' => [
                'location_count' => $locationCount,
                'total_dekar' => $totalDekar,
                'active_fert_recipe' => $activeFertRun?->recipe?->name ?? 'Tanımlanmadı',
                'pending_plans' => $pendingPlans,
                'in_progress_plans' => $inProgressPlans,
                'completed_plans' => $completedPlans,
                'pressure_warnings' => $pressureWarnings,
            ],
            'analytics' => $analytics,
            'dailyTrends' => $dailyTrends,
            'recentSheets' => $recentSheets,
            'recentOrders' => $recentOrders,
            'recentMarketPrices' => $recentMarketPrices,
            'activeFertRun' => $activeFertRun,
            'weatherData' => $weatherData,
            'phiWarnings' => $phiWarnings,
        ]);
    }

    private function calculatePeriodMetrics($sheets): array
    {
        $revenue = 0.0;
        $harvestKg = 0.0;
        $laborWage = 0.0;
        $travelFee = 0.0;
        $mealFee = 0.0;
        $totalCost = 0.0;

        $productMap = [];
        $locationMap = [];
        $crewLeaderMap = [];

        foreach ($sheets as $sheet) {
            $sheetLabor = 0.0;
            $sheetTravel = 0.0;
            $sheetMeal = 0.0;

            foreach ($sheet->crewLeaders as $clRow) {
                $cWage = (float) ($clRow->calculated_wage_total ?? 0);
                $cTravel = (float) (($clRow->travel_fee ?? 0) * ($clRow->car_count ?? 0));
                $cMeal = (float) ($clRow->meal_fee ?? 0);

                $sheetLabor += $cWage;
                $sheetTravel += $cTravel;
                $sheetMeal += $cMeal;

                $clName = $clRow->crewLeader ? ($clRow->crewLeader->first_name . ' ' . $clRow->crewLeader->last_name) : 'Diğer / Tanımsız';
                if (!isset($crewLeaderMap[$clName])) {
                    $crewLeaderMap[$clName] = [
                        'name' => $clName,
                        'origin_city' => $clRow->crewLeader->origin_city ?? '-',
                        'worker_count' => 0,
                        'shift_count' => 0,
                        'wage_total' => 0.0,
                        'travel_total' => 0.0,
                        'meal_total' => 0.0,
                        'total_cost' => 0.0,
                    ];
                }
                $crewLeaderMap[$clName]['worker_count'] += (int) ($clRow->worker_count ?? 0);
                $crewLeaderMap[$clName]['shift_count'] += 1;
                $crewLeaderMap[$clName]['wage_total'] += $cWage;
                $crewLeaderMap[$clName]['travel_total'] += $cTravel;
                $crewLeaderMap[$clName]['meal_total'] += $cMeal;
                $crewLeaderMap[$clName]['total_cost'] += $cWage + $cTravel + $cMeal;
            }

            $laborWage += $sheetLabor;
            $travelFee += $sheetTravel;
            $mealFee += $sheetMeal;
            $sheetCost = $sheetLabor + $sheetTravel + $sheetMeal;
            $totalCost += $sheetCost;

            $sheetRevenue = 0.0;
            $sheetKg = 0.0;

            foreach ($sheet->harvestItems as $hItem) {
                $kg = (float) ($hItem->quantity ?? 0);
                $rev = (float) ($hItem->total_revenue ?? 0);
                if ($rev <= 0 && $kg > 0) {
                    $rev = $kg * (float) ($hItem->unit_price ?? 0);
                }

                $sheetRevenue += $rev;
                $sheetKg += $kg;
                $revenue += $rev;
                $harvestKg += $kg;

                $pName = $hItem->product->name ?? 'Genel Ürün';
                $pCode = $hItem->product->code ?? '-';
                if (!isset($productMap[$pName])) {
                    $productMap[$pName] = [
                        'name' => $pName,
                        'code' => $pCode,
                        'kg' => 0.0,
                        'revenue' => 0.0,
                        'estimated_cost' => 0.0,
                        'profit' => 0.0,
                    ];
                }
                $productMap[$pName]['kg'] += $kg;
                $productMap[$pName]['revenue'] += $rev;
            }

            $locName = $sheet->productionLocation->name ?? 'Tesis';
            $compName = $sheet->company->name ?? 'SASA';
            if (!isset($locationMap[$locName])) {
                $locationMap[$locName] = [
                    'name' => $locName,
                    'company' => $compName,
                    'kg' => 0.0,
                    'revenue' => 0.0,
                    'cost' => 0.0,
                    'profit' => 0.0,
                ];
            }
            $locationMap[$locName]['kg'] += $sheetKg;
            $locationMap[$locName]['revenue'] += $sheetRevenue;
            $locationMap[$locName]['cost'] += $sheetCost;
            $locationMap[$locName]['profit'] += ($sheetRevenue - $sheetCost);
        }

        $netProfit = $revenue - $totalCost;
        $profitMargin = $revenue > 0 ? round(($netProfit / $revenue) * 100, 1) : 0.0;
        $avgRevenuePerKg = $harvestKg > 0 ? round($revenue / $harvestKg, 2) : 0.0;
        $avgCostPerKg = $harvestKg > 0 ? round($totalCost / $harvestKg, 2) : 0.0;
        $avgProfitPerKg = round($avgRevenuePerKg - $avgCostPerKg, 2);

        foreach ($productMap as $k => $p) {
            $ratio = $harvestKg > 0 ? ($p['kg'] / $harvestKg) : 0;
            $productMap[$k]['estimated_cost'] = round($totalCost * $ratio, 2);
            $productMap[$k]['profit'] = round($p['revenue'] - $productMap[$k]['estimated_cost'], 2);
            $productMap[$k]['margin'] = $p['revenue'] > 0 ? round(($productMap[$k]['profit'] / $p['revenue']) * 100, 1) : 0.0;
        }

        return [
            'revenue' => round($revenue, 2),
            'harvest_kg' => round($harvestKg, 2),
            'labor_wage' => round($laborWage, 2),
            'travel_fee' => round($travelFee, 2),
            'meal_fee' => round($mealFee, 2),
            'total_cost' => round($totalCost, 2),
            'net_profit' => round($netProfit, 2),
            'profit_margin' => $profitMargin,
            'avg_revenue_per_kg' => $avgRevenuePerKg,
            'avg_cost_per_kg' => $avgCostPerKg,
            'avg_profit_per_kg' => $avgProfitPerKg,
            'products' => array_values($productMap),
            'locations' => array_values($locationMap),
            'crew_leaders' => array_values($crewLeaderMap),
        ];
    }

    private function calculateDailyTrends($sheets): array
    {
        $days = [];
        $startDate = Carbon::today()->subDays(29);

        for ($i = 0; $i < 30; $i++) {
            $d = (clone $startDate)->addDays($i);
            $dStr = $d->toDateString();
            $label = $d->translatedFormat('d M');

            $daySheets = $sheets->filter(fn ($s) => $s->work_date->toDateString() === $dStr);
            $rev = 0.0;
            $cost = 0.0;
            $kg = 0.0;

            foreach ($daySheets as $ds) {
                foreach ($ds->harvestItems as $hi) {
                    $itemKg = (float) ($hi->quantity ?? 0);
                    $itemRev = (float) ($hi->total_revenue ?? 0);
                    if ($itemRev <= 0 && $itemKg > 0) {
                        $itemRev = $itemKg * (float) ($hi->unit_price ?? 0);
                    }
                    $rev += $itemRev;
                    $kg += $itemKg;
                }
                foreach ($ds->crewLeaders as $cl) {
                    $cost += (float) ($cl->calculated_wage_total ?? 0)
                        + ((float) ($cl->travel_fee ?? 0) * (int) ($cl->car_count ?? 0))
                        + (float) ($cl->meal_fee ?? 0);
                }
            }

            $days[] = [
                'date' => $dStr,
                'label' => $label,
                'revenue' => round($rev, 2),
                'cost' => round($cost, 2),
                'profit' => round($rev - $cost, 2),
                'harvest_kg' => round($kg, 2),
            ];
        }

        return $days;
    }
}
