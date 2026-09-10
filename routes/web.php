<?php

use App\Http\Controllers\AgricultureController;
use App\Http\Controllers\Agriculture\WorkPlanController;
use App\Http\Controllers\Agriculture\FertilizationController;
use App\Http\Controllers\Agriculture\IrrigationController;
use App\Http\Controllers\Agriculture\SprayingController;
use App\Http\Controllers\Agriculture\WaterControlController;
use App\Http\Controllers\Agriculture\CustomerOrderController;
use App\Http\Controllers\Agriculture\ShipmentController;
use App\Http\Controllers\Agriculture\DailyWorkSheetController;
use App\Http\Controllers\Agriculture\MarketPriceController;

use App\Http\Controllers\MasterDefinitionsController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::prefix('agriculture')->name('agriculture.')->group(function () {
        Route::get('/', [AgricultureController::class, 'index'])->name('index');
        
        Route::post('/work-plans', [WorkPlanController::class, 'storeWorkPlan'])->name('work-plans.store');
        Route::post('/work-plans/comment', [WorkPlanController::class, 'storeWorkPlanComment'])->name('work-plans.comment');
        Route::delete('/work-plans/{workPlan}', [WorkPlanController::class, 'destroyWorkPlan'])->name('work-plans.destroy');

        Route::post('/fertilization-runs', [FertilizationController::class, 'storeFertilizationRun'])->name('fertilization-runs.store');
        Route::post('/fertilization-runs/{run}/toggle-active', [FertilizationController::class, 'toggleActiveFertilizationRun'])->name('fertilization-runs.toggle-active');
        Route::post('/fertilization-tank-logs', [FertilizationController::class, 'storeFertilizationTankLog'])->name('fertilization-tank-logs.store');
        Route::delete('/fertilization-runs/{run}', [FertilizationController::class, 'destroyFertilizationRun'])->name('fertilization-runs.destroy');

        Route::post('/irrigation-schedules', [IrrigationController::class, 'storeIrrigationSchedule'])->name('irrigation-schedules.store');
        Route::delete('/irrigation-schedules/{schedule}', [IrrigationController::class, 'destroyIrrigationSchedule'])->name('irrigation-schedules.destroy');

        Route::post('/spraying-applications', [SprayingController::class, 'storeSprayingApplication'])->name('spraying-applications.store');
        Route::delete('/spraying-applications/{app}', [SprayingController::class, 'destroySprayingApplication'])->name('spraying-applications.destroy');

        Route::post('/water-analyses', [WaterControlController::class, 'storeWaterAnalysis'])->name('water-analyses.store');
        Route::delete('/water-analyses/{log}', [WaterControlController::class, 'destroyWaterAnalysis'])->name('water-analyses.destroy');

        Route::post('/raw-water-controls', [WaterControlController::class, 'storeRawWaterControl'])->name('raw-water-controls.store');
        Route::delete('/raw-water-controls/{control}', [WaterControlController::class, 'destroyRawWaterControl'])->name('raw-water-controls.destroy');

        Route::post('/purification-controls', [WaterControlController::class, 'storePurificationControl'])->name('purification-controls.store');
        Route::delete('/purification-controls/{control}', [WaterControlController::class, 'destroyPurificationControl'])->name('purification-controls.destroy');

        Route::post('/customer-orders', [CustomerOrderController::class, 'storeCustomerOrder'])->name('customer-orders.store');
        Route::put('/customer-orders/{order}', [CustomerOrderController::class, 'updateCustomerOrder'])->name('customer-orders.update');
        Route::patch('/customer-orders/{order}/status', [CustomerOrderController::class, 'updateOrderStatus'])->name('customer-orders.update-status');
        Route::delete('/customer-orders/{order}', [CustomerOrderController::class, 'destroyCustomerOrder'])->name('customer-orders.destroy');

        Route::post('/shipment-deliveries', [ShipmentController::class, 'storeShipmentDelivery'])->name('shipment-deliveries.store');
        Route::patch('/shipment-deliveries/{shipment}/status', [ShipmentController::class, 'updateShipmentStatus'])->name('shipment-deliveries.update-status');
        Route::delete('/shipment-deliveries/{shipment}', [ShipmentController::class, 'destroyShipmentDelivery'])->name('shipment-deliveries.destroy');

        Route::post('/daily-work-sheets', [DailyWorkSheetController::class, 'storeDailyWorkSheet'])->name('daily-work-sheets.store');
        Route::patch('/daily-work-sheets/harvest-items/{item}/banana-weights', [DailyWorkSheetController::class, 'updateBananaWeights'])->name('daily-work-sheets.update-banana-weights');
        Route::post('/daily-work-sheets/{sheet}/approve', [DailyWorkSheetController::class, 'approveDailyWorkSheet'])->name('daily-work-sheets.approve');
        Route::delete('/daily-work-sheets/{sheet}', [DailyWorkSheetController::class, 'destroyDailyWorkSheet'])->name('daily-work-sheets.destroy');

        Route::post('/market-prices', [MarketPriceController::class, 'storeMarketPrice'])->name('market-prices.store');
        Route::delete('/market-prices/{price}', [MarketPriceController::class, 'destroyMarketPrice'])->name('market-prices.destroy');
    });

    Route::prefix('definitions')->name('definitions.')->middleware('permission:tanimlamalar')->group(function () {
        Route::get('/', [MasterDefinitionsController::class, 'index'])->name('index');

        Route::post('/companies', [MasterDefinitionsController::class, 'storeCompany'])->name('companies.store');
        Route::post('/companies/sync-dia', [MasterDefinitionsController::class, 'syncDiaCompanies'])->name('companies.sync-dia');
        Route::delete('/companies/{company}', [MasterDefinitionsController::class, 'destroyCompany'])->name('companies.destroy');

        Route::post('/personnels', [MasterDefinitionsController::class, 'storePersonnel'])->name('personnels.store');
        Route::delete('/personnels/{personnel}', [MasterDefinitionsController::class, 'destroyPersonnel'])->name('personnels.destroy');

        Route::post('/trading-parties', [MasterDefinitionsController::class, 'storeTradingParty'])->name('trading-parties.store');
        Route::post('/trading-parties/sync-dia', [MasterDefinitionsController::class, 'syncDiaTradingParties'])->name('trading-parties.sync-dia');
        Route::delete('/trading-parties/{tradingParty}', [MasterDefinitionsController::class, 'destroyTradingParty'])->name('trading-parties.destroy');

        Route::post('/delivery-types', [MasterDefinitionsController::class, 'storeDeliveryType'])->name('delivery-types.store');
        Route::delete('/delivery-types/{deliveryType}', [MasterDefinitionsController::class, 'destroyDeliveryType'])->name('delivery-types.destroy');

        Route::post('/production-locations', [MasterDefinitionsController::class, 'storeProductionLocation'])->name('production-locations.store');
        Route::delete('/production-locations/{productionLocation}', [MasterDefinitionsController::class, 'destroyProductionLocation'])->name('production-locations.destroy');

        Route::post('/products', [MasterDefinitionsController::class, 'storeProduct'])->name('products.store');
        Route::delete('/products/{product}', [MasterDefinitionsController::class, 'destroyProduct'])->name('products.destroy');

        Route::post('/job-types', [MasterDefinitionsController::class, 'storeJobType'])->name('job-types.store');
        Route::delete('/job-types/{jobType}', [MasterDefinitionsController::class, 'destroyJobType'])->name('job-types.destroy');

        Route::post('/units', [MasterDefinitionsController::class, 'storeUnitDefinition'])->name('units.store');
        Route::delete('/units/{unitDefinition}', [MasterDefinitionsController::class, 'destroyUnitDefinition'])->name('units.destroy');

        Route::post('/packagings', [MasterDefinitionsController::class, 'storePackagingDefinition'])->name('packagings.store');
        Route::delete('/packagings/{packagingDefinition}', [MasterDefinitionsController::class, 'destroyPackagingDefinition'])->name('packagings.destroy');

        Route::post('/crew-leaders', [MasterDefinitionsController::class, 'storeCrewLeader'])->name('crew-leaders.store');
        Route::delete('/crew-leaders/{crewLeader}', [MasterDefinitionsController::class, 'destroyCrewLeader'])->name('crew-leaders.destroy');

        Route::post('/workers', [MasterDefinitionsController::class, 'storeWorker'])->name('workers.store');
        Route::delete('/workers/{worker}', [MasterDefinitionsController::class, 'destroyWorker'])->name('workers.destroy');

        Route::post('/catering-suppliers', [MasterDefinitionsController::class, 'storeCateringSupplier'])->name('catering-suppliers.store');
        Route::delete('/catering-suppliers/{cateringSupplier}', [MasterDefinitionsController::class, 'destroyCateringSupplier'])->name('catering-suppliers.destroy');

        Route::post('/fertilization-recipes', [MasterDefinitionsController::class, 'storeFertilizationRecipe'])->name('fertilization-recipes.store');
        Route::post('/fertilization-recipes/{recipe}/toggle-active', [MasterDefinitionsController::class, 'toggleActiveFertilizationRecipe'])->name('fertilization-recipes.toggle-active');
        Route::delete('/fertilization-recipes/{recipe}', [MasterDefinitionsController::class, 'destroyFertilizationRecipe'])->name('fertilization-recipes.destroy');

        Route::post('/spraying-recipes', [MasterDefinitionsController::class, 'storeSprayingRecipe'])->name('spraying-recipes.store');
        Route::delete('/spraying-recipes/{recipe}', [MasterDefinitionsController::class, 'destroySprayingRecipe'])->name('spraying-recipes.destroy');

        Route::post('/water-sources', [MasterDefinitionsController::class, 'storeWaterSource'])->name('water-sources.store');
        Route::delete('/water-sources/{waterSource}', [MasterDefinitionsController::class, 'destroyWaterSource'])->name('water-sources.destroy');

        Route::post('/filters', [MasterDefinitionsController::class, 'storeFilter'])->name('filters.store');
        Route::delete('/filters/{filter}', [MasterDefinitionsController::class, 'destroyFilter'])->name('filters.destroy');
    });
});

require __DIR__.'/auth.php';
