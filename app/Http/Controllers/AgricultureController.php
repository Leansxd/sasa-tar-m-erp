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

        $fertRunsQuery = FertilizationRun::with(['recipe.tanks.items', 'tankLogs.preparedBy', 'tankLogs.tank']);
        $irrigationQuery = IrrigationSchedule::with(['recipe', 'valves.valve', 'valves.location']);
        $sprayQuery = SprayingApplication::with(['recipe', 'appliedBy']);
        $waterAnalysisQuery = WaterAnalysisLog::with('waterSource');
        $rawWaterQuery = RawWaterControl::with('waterSource');
        $purificationQuery = PurificationControl::with('waterSource');

        if ($companyIds !== null) {
            $companiesQuery->whereIn('id', $companyIds);
            $locationsQuery->whereIn('company_id', $companyIds);
            $dailyWorkSheetsQuery->whereIn('company_id', $companyIds);
            $workPlansQuery->where(function ($q) use ($companyIds) {
                $q->whereNull('production_location_id')
                    ->orWhereHas('productionLocation', fn ($sq) => $sq->whereIn('company_id', $companyIds));
            });
            $waterSourcesQuery->whereHas('productionLocation', fn ($sq) => $sq->whereIn('company_id', $companyIds));
            $irrigationQuery->whereHas('valves.location', fn ($sq) => $sq->whereIn('company_id', $companyIds));
            $waterAnalysisQuery->whereHas('waterSource.productionLocation', fn ($sq) => $sq->whereIn('company_id', $companyIds));
            $rawWaterQuery->whereHas('waterSource.productionLocation', fn ($sq) => $sq->whereIn('company_id', $companyIds));
            $purificationQuery->whereHas('waterSource.productionLocation', fn ($sq) => $sq->whereIn('company_id', $companyIds));
        }

        $data = [
            'activeCategory' => $category,
            'activeModule' => $module,
            'companies' => $companiesQuery->get(),
            'locations' => $locationsQuery->get(),
            'jobTypes' => JobType::all(),
            'personnels' => Personnel::where('is_active', true)->get(),
            'workers' => Worker::with('crewLeader')->where('is_active', true)->get(),
            'products' => Product::with(['subtypes', 'packagings'])->get(),
            'crewLeaders' => CrewLeader::with('workers')->get(),
            'tradingParties' => TradingParty::all(),
            'deliveryTypes' => DeliveryType::all(),
            'fertRecipes' => FertilizationRecipe::with('tanks.items')->get(),
            'sprayRecipes' => SprayingRecipe::with('items')->get(),
            'waterSources' => $waterSourcesQuery->get(),
            'packagings' => PackagingDefinition::all(),
            
            'marketPrices' => [],
            'workPlans' => [],
            'fertRuns' => [],
            'irrigationSchedules' => [],
            'sprayApplications' => [],
            'waterAnalysisLogs' => [],
            'rawWaterControls' => [],
            'purificationControls' => [],
            'customerOrders' => [],
            'shipments' => [],
            'dailyWorkSheets' => [],
        ];

        if ($module === 'is_planlama') {
            $data['workPlans'] = $workPlansQuery->latest()->get();
        } elseif ($module === 'gubreleme') {
            $data['fertRuns'] = $fertRunsQuery->latest()->get();
        } elseif ($module === 'sulama') {
            $data['irrigationSchedules'] = $irrigationQuery->latest()->get();
        } elseif ($module === 'ilaclama') {
            $data['sprayApplications'] = $sprayQuery->latest()->get();
        } elseif ($module === 'su_analizleri') {
            $data['waterAnalysisLogs'] = $waterAnalysisQuery->latest()->get();
        } elseif ($module === 'kaynak_suyu_kontrol') {
            $data['rawWaterControls'] = $rawWaterQuery->latest()->get();
        } elseif ($module === 'aritma_suyu_kontrol') {
            $data['purificationControls'] = $purificationQuery->latest()->get();
        } elseif ($module === 'alinan_siparis') {
            $data['customerOrders'] = CustomerOrder::with(['tradingParty', 'items.product', 'items.packaging', 'shipments'])->latest()->get();
        } elseif ($module === 'sevkiyat_teslimat') {
            $data['shipments'] = ShipmentDelivery::with(['order', 'tradingParty', 'product', 'packaging', 'deliveryType'])->latest()->get();
            $data['customerOrders'] = CustomerOrder::with(['tradingParty', 'items.product', 'items.packaging', 'shipments'])->latest()->get();
        } elseif ($module === 'gunluk_isci_formu') {
            $data['dailyWorkSheets'] = $dailyWorkSheetsQuery->orderBy('work_date', 'desc')->orderBy('id', 'desc')->get();
            $data['marketPrices'] = MarketPrice::with('product')->latest()->get();
        } elseif ($category === 'raporlar') {
            $data['dailyWorkSheets'] = $dailyWorkSheetsQuery->orderBy('work_date', 'desc')->orderBy('id', 'desc')->get();
            $data['shipments'] = ShipmentDelivery::with(['order', 'tradingParty', 'product', 'packaging', 'deliveryType'])->latest()->get();
            $data['customerOrders'] = CustomerOrder::with(['tradingParty', 'items.product', 'items.packaging', 'shipments'])->latest()->get();
            $data['marketPrices'] = MarketPrice::with('product')->latest()->get();
        }

        return inertia('Agriculture/Index', $data);
    }

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    
}
