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
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SetupWizardController extends Controller
{
    public function index(Request $request): Response
    {
        $companies = Company::with(['products', 'productionLocations'])->latest()->get();
        $productionLocations = ProductionLocation::with(['company', 'sections', 'valves'])->latest()->get();
        $units = UnitDefinition::latest()->get();
        $packagings = PackagingDefinition::with(['product', 'unit'])->latest()->get();
        $products = Product::with(['companies', 'subtypes', 'units', 'packagings'])->latest()->get();
        $personnels = Personnel::with(['user', 'parent'])->latest()->get();
        $crewLeaders = CrewLeader::with('workers')->latest()->get();
        $workers = Worker::with('crewLeader')->latest()->get();
        $jobTypes = JobType::with('units')->latest()->get();
        $waterSources = WaterSource::with(['productionLocation.company'])->latest()->get();
        $filters = Filter::with(['productionLocation.company', 'waterSources'])->latest()->get();
        $fertilizationRecipes = FertilizationRecipe::with('tanks.items')->latest()->get();
        $sprayingRecipes = SprayingRecipe::with('items')->latest()->get();
        $tradingParties = TradingParty::latest()->get();
        $deliveryTypes = DeliveryType::latest()->get();

        $stepStats = [
            'companies_count' => $companies->count(),
            'locations_count' => $productionLocations->count(),
            'units_count' => $units->count(),
            'packagings_count' => $packagings->count(),
            'products_count' => $products->count(),
            'personnels_count' => $personnels->count(),
            'crew_leaders_count' => $crewLeaders->count(),
            'workers_count' => $workers->count(),
            'job_types_count' => $jobTypes->count(),
            'water_sources_count' => $waterSources->count(),
            'filters_count' => $filters->count(),
            'recipes_count' => $fertilizationRecipes->count() + $sprayingRecipes->count(),
        ];

        return Inertia::render('Setup/Index', [
            'initialStep' => (int) $request->query('step', 1),
            'companies' => $companies,
            'productionLocations' => $productionLocations,
            'units' => $units,
            'packagings' => $packagings,
            'products' => $products,
            'personnels' => $personnels,
            'crewLeaders' => $crewLeaders,
            'workers' => $workers,
            'jobTypes' => $jobTypes,
            'waterSources' => $waterSources,
            'filters' => $filters,
            'fertilizationRecipes' => $fertilizationRecipes,
            'sprayingRecipes' => $sprayingRecipes,
            'tradingParties' => $tradingParties,
            'deliveryTypes' => $deliveryTypes,
            'stepStats' => $stepStats,
        ]);
    }

    public function getData(): \Illuminate\Http\JsonResponse
    {
        $companies = Company::with(['products', 'productionLocations'])->latest()->get();
        $productionLocations = ProductionLocation::with(['company', 'sections', 'valves'])->latest()->get();
        $units = UnitDefinition::latest()->get();
        $packagings = PackagingDefinition::with(['product', 'unit'])->latest()->get();
        $products = Product::with(['companies', 'subtypes', 'units', 'packagings'])->latest()->get();
        $personnels = Personnel::with(['user', 'parent'])->latest()->get();
        $crewLeaders = CrewLeader::with('workers')->latest()->get();
        $workers = Worker::with('crewLeader')->latest()->get();
        $jobTypes = JobType::with('units')->latest()->get();
        $waterSources = WaterSource::with(['productionLocation.company'])->latest()->get();
        $filters = Filter::with(['productionLocation.company', 'waterSources'])->latest()->get();
        $fertilizationRecipes = FertilizationRecipe::with('tanks.items')->latest()->get();
        $sprayingRecipes = SprayingRecipe::with('items')->latest()->get();

        return response()->json([
            'companies' => $companies,
            'productionLocations' => $productionLocations,
            'units' => $units,
            'packagings' => $packagings,
            'products' => $products,
            'personnels' => $personnels,
            'crewLeaders' => $crewLeaders,
            'workers' => $workers,
            'jobTypes' => $jobTypes,
            'waterSources' => $waterSources,
            'filters' => $filters,
            'fertilizationRecipes' => $fertilizationRecipes,
            'sprayingRecipes' => $sprayingRecipes,
        ]);
    }
}
