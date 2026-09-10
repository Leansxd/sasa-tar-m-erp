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
use Illuminate\Support\Facades\DB;

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

        $allSheetsQuery = DailyWorkSheet::where('status', 'approved');
        if ($companyIds !== null) {
            $allSheetsQuery->whereIn('company_id', $companyIds);
        }

        if ($hasReports) {
            $today = Carbon::today()->toDateString();
            $sevenDaysAgo = Carbon::today()->subDays(6)->toDateString();
            $thirtyDaysAgo = Carbon::today()->subDays(29)->toDateString();

            $analytics = [
                'today' => $this->calculatePeriodMetrics((clone $allSheetsQuery)->where('work_date', '=', $today)),
                'week' => $this->calculatePeriodMetrics((clone $allSheetsQuery)->where('work_date', '>=', $sevenDaysAgo)),
                'month' => $this->calculatePeriodMetrics((clone $allSheetsQuery)->where('work_date', '>=', $thirtyDaysAgo)),
                'all' => $this->calculatePeriodMetrics((clone $allSheetsQuery)),
            ];

            $dailyTrends = $this->calculateDailyTrends((clone $allSheetsQuery));
            
            // Recent items still need models
            $recentSheets = (clone $allSheetsQuery)
                ->with(['company', 'productionLocation', 'crewLeaders.crewLeader', 'harvestItems.product'])
                ->orderBy('work_date', 'desc')
                ->take(5)
                ->get();
                
            $recentOrders = CustomerOrder::with('tradingParty')->latest()->take(5)->get();
        } else {
            $analytics = [
                'today' => $this->getEmptyMetrics(),
                'week' => $this->getEmptyMetrics(),
                'month' => $this->getEmptyMetrics(),
                'all' => $this->getEmptyMetrics(),
            ];
            $dailyTrends = [];
            $recentSheets = [];
            $recentOrders = [];
        }

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

    private function getEmptyMetrics(): array
    {
        return [
            'revenue' => 0, 'harvest_kg' => 0, 'labor_wage' => 0, 'travel_fee' => 0,
            'meal_fee' => 0, 'total_cost' => 0, 'net_profit' => 0, 'profit_margin' => 0,
            'avg_revenue_per_kg' => 0, 'avg_cost_per_kg' => 0, 'avg_profit_per_kg' => 0,
            'products' => [], 'locations' => [], 'crew_leaders' => [],
        ];
    }

    private function calculatePeriodMetrics($query): array
    {
        $sheetIds = $query->pluck('id')->toArray();
        if (empty($sheetIds)) {
            return $this->getEmptyMetrics();
        }

        // 1. Revenue & Harvest Kg (Aggregate)
        // Check if total_revenue is > 0, otherwise fallback to quantity * unit_price
        $revenueData = DailyWorkSheetHarvestItem::whereIn('daily_work_sheet_id', $sheetIds)
            ->selectRaw('
                SUM(quantity) as total_kg,
                SUM(CASE WHEN total_revenue > 0 THEN total_revenue ELSE quantity * unit_price END) as total_rev
            ')->first();

        $revenue = (float) ($revenueData->total_rev ?? 0);
        $harvestKg = (float) ($revenueData->total_kg ?? 0);

        // 2. Costs (Aggregate)
        $costData = DailyWorkSheetCrewLeader::whereIn('daily_work_sheet_id', $sheetIds)
            ->selectRaw('
                SUM(calculated_wage_total) as labor_wage,
                SUM(travel_fee * car_count) as travel_fee,
                SUM(meal_fee) as meal_fee
            ')->first();

        $laborWage = (float) ($costData->labor_wage ?? 0);
        $travelFee = (float) ($costData->travel_fee ?? 0);
        $mealFee = (float) ($costData->meal_fee ?? 0);
        $totalCost = $laborWage + $travelFee + $mealFee;

        // 3. Products Grouping
        $productsRaw = DailyWorkSheetHarvestItem::whereIn('daily_work_sheet_id', $sheetIds)
            ->with('product')
            ->selectRaw('
                product_id,
                SUM(quantity) as total_kg,
                SUM(CASE WHEN total_revenue > 0 THEN total_revenue ELSE quantity * unit_price END) as total_rev
            ')
            ->groupBy('product_id')
            ->get();

        $productMap = [];
        foreach ($productsRaw as $p) {
            $kg = (float) $p->total_kg;
            $rev = (float) $p->total_rev;
            $ratio = $harvestKg > 0 ? ($kg / $harvestKg) : 0;
            $estCost = round($totalCost * $ratio, 2);
            $profit = round($rev - $estCost, 2);

            $productMap[] = [
                'name' => $p->product->name ?? 'Genel Ürün',
                'code' => $p->product->code ?? '-',
                'kg' => round($kg, 2),
                'revenue' => round($rev, 2),
                'estimated_cost' => $estCost,
                'profit' => $profit,
                'margin' => $rev > 0 ? round(($profit / $rev) * 100, 1) : 0.0,
            ];
        }

        // 4. Crew Leaders Grouping
        $crewsRaw = DailyWorkSheetCrewLeader::whereIn('daily_work_sheet_id', $sheetIds)
            ->with('crewLeader')
            ->selectRaw('
                crew_leader_id,
                SUM(worker_count) as worker_count,
                COUNT(id) as shift_count,
                SUM(calculated_wage_total) as wage_total,
                SUM(travel_fee * car_count) as travel_total,
                SUM(meal_fee) as meal_total
            ')
            ->groupBy('crew_leader_id')
            ->get();

        $crewLeaderMap = [];
        foreach ($crewsRaw as $c) {
            $wage = (float) $c->wage_total;
            $travel = (float) $c->travel_total;
            $meal = (float) $c->meal_total;
            $clName = $c->crewLeader ? ($c->crewLeader->first_name . ' ' . $c->crewLeader->last_name) : 'Diğer / Tanımsız';

            $crewLeaderMap[] = [
                'name' => $clName,
                'origin_city' => $c->crewLeader->origin_city ?? '-',
                'worker_count' => (int) $c->worker_count,
                'shift_count' => (int) $c->shift_count,
                'wage_total' => round($wage, 2),
                'travel_total' => round($travel, 2),
                'meal_total' => round($meal, 2),
                'total_cost' => round($wage + $travel + $meal, 2),
            ];
        }

        // Locations grouping requires joining sheets with harvest and costs,
        // For simplicity and speed without complex joins, we can query sheets directly
        // and loop, but since sheet IDs are limited, we can eager load just what's needed
        $locationSheets = DailyWorkSheet::whereIn('id', $sheetIds)
            ->with(['productionLocation.company', 'harvestItems', 'crewLeaders'])
            ->get();
            
        $locMapArr = [];
        foreach ($locationSheets as $sheet) {
            $locName = $sheet->productionLocation->name ?? 'Tesis';
            $compName = $sheet->productionLocation->company->name ?? 'SASA';
            
            if (!isset($locMapArr[$locName])) {
                $locMapArr[$locName] = [
                    'name' => $locName,
                    'company' => $compName,
                    'kg' => 0.0,
                    'revenue' => 0.0,
                    'cost' => 0.0,
                    'profit' => 0.0,
                ];
            }
            
            $sRev = 0; $sKg = 0; $sCost = 0;
            foreach ($sheet->harvestItems as $hi) {
                $sKg += (float) $hi->quantity;
                $r = (float) $hi->total_revenue;
                $sRev += ($r > 0) ? $r : ((float) $hi->quantity * (float) $hi->unit_price);
            }
            foreach ($sheet->crewLeaders as $cl) {
                $sCost += (float) $cl->calculated_wage_total + ((float) $cl->travel_fee * (int) $cl->car_count) + (float) $cl->meal_fee;
            }
            
            $locMapArr[$locName]['kg'] += $sKg;
            $locMapArr[$locName]['revenue'] += $sRev;
            $locMapArr[$locName]['cost'] += $sCost;
            $locMapArr[$locName]['profit'] += ($sRev - $sCost);
        }

        $netProfit = $revenue - $totalCost;
        $profitMargin = $revenue > 0 ? round(($netProfit / $revenue) * 100, 1) : 0.0;
        $avgRevenuePerKg = $harvestKg > 0 ? round($revenue / $harvestKg, 2) : 0.0;
        $avgCostPerKg = $harvestKg > 0 ? round($totalCost / $harvestKg, 2) : 0.0;
        $avgProfitPerKg = round($avgRevenuePerKg - $avgCostPerKg, 2);

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
            'products' => $productMap,
            'locations' => array_values($locMapArr),
            'crew_leaders' => $crewLeaderMap,
        ];
    }

    private function calculateDailyTrends($query): array
    {
        $days = [];
        $startDate = Carbon::today()->subDays(29);
        
        // Optimize: Fetch all relevant data within the 30 days grouped by date
        $sheetIds = (clone $query)->where('work_date', '>=', $startDate->toDateString())->pluck('id')->toArray();
        
        $revByDate = [];
        $kgByDate = [];
        $costByDate = [];
        
        if (!empty($sheetIds)) {
            $sheetTable = (new DailyWorkSheet)->getTable();
            $harvestTable = (new DailyWorkSheetHarvestItem)->getTable();
            $crewTable = (new DailyWorkSheetCrewLeader)->getTable();

            $harvests = DailyWorkSheetHarvestItem::join($sheetTable, "{$harvestTable}.daily_work_sheet_id", '=', "{$sheetTable}.id")
                ->whereIn("{$sheetTable}.id", $sheetIds)
                ->selectRaw("
                    {$sheetTable}.work_date,
                    SUM({$harvestTable}.quantity) as total_kg,
                    SUM(CASE WHEN {$harvestTable}.total_revenue > 0 
                             THEN {$harvestTable}.total_revenue 
                             ELSE {$harvestTable}.quantity * {$harvestTable}.unit_price END) as total_rev
                ")
                ->groupBy("{$sheetTable}.work_date")
                ->get();
                
            foreach ($harvests as $h) {
                $revByDate[$h->work_date] = (float) $h->total_rev;
                $kgByDate[$h->work_date] = (float) $h->total_kg;
            }
            
            $costs = DailyWorkSheetCrewLeader::join($sheetTable, "{$crewTable}.daily_work_sheet_id", '=', "{$sheetTable}.id")
                ->whereIn("{$sheetTable}.id", $sheetIds)
                ->selectRaw("
                    {$sheetTable}.work_date,
                    SUM({$crewTable}.calculated_wage_total + 
                        ({$crewTable}.travel_fee * {$crewTable}.car_count) + 
                        {$crewTable}.meal_fee) as total_cost
                ")
                ->groupBy("{$sheetTable}.work_date")
                ->get();
                
            foreach ($costs as $c) {
                $costByDate[$c->work_date] = (float) $c->total_cost;
            }
        }

        for ($i = 0; $i < 30; $i++) {
            $d = (clone $startDate)->addDays($i);
            $dStr = $d->toDateString();
            $label = $d->translatedFormat('d M');

            $rev = $revByDate[$dStr] ?? 0.0;
            $kg = $kgByDate[$dStr] ?? 0.0;
            $cost = $costByDate[$dStr] ?? 0.0;

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
