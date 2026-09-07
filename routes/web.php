<?php

use App\Http\Controllers\AgricultureController;
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
        Route::post('/work-plans', [AgricultureController::class, 'storeWorkPlan'])->name('work-plans.store');
        Route::post('/work-plans/comment', [AgricultureController::class, 'storeWorkPlanComment'])->name('work-plans.comment');
        Route::post('/fertilization-runs', [AgricultureController::class, 'storeFertilizationRun'])->name('fertilization-runs.store');
        Route::post('/fertilization-runs/{run}/toggle-active', [AgricultureController::class, 'toggleActiveFertilizationRun'])->name('fertilization-runs.toggle-active');
        Route::post('/fertilization-tank-logs', [AgricultureController::class, 'storeFertilizationTankLog'])->name('fertilization-tank-logs.store');
        Route::post('/irrigation-schedules', [AgricultureController::class, 'storeIrrigationSchedule'])->name('irrigation-schedules.store');
        Route::post('/spraying-applications', [AgricultureController::class, 'storeSprayingApplication'])->name('spraying-applications.store');
        Route::post('/water-analyses', [AgricultureController::class, 'storeWaterAnalysis'])->name('water-analyses.store');
        Route::post('/raw-water-controls', [AgricultureController::class, 'storeRawWaterControl'])->name('raw-water-controls.store');
        Route::post('/purification-controls', [AgricultureController::class, 'storePurificationControl'])->name('purification-controls.store');
        Route::post('/customer-orders', [AgricultureController::class, 'storeCustomerOrder'])->name('customer-orders.store');
        Route::put('/customer-orders/{order}', [AgricultureController::class, 'updateCustomerOrder'])->name('customer-orders.update');
        Route::patch('/customer-orders/{order}/status', [AgricultureController::class, 'updateOrderStatus'])->name('customer-orders.update-status');
        Route::post('/shipment-deliveries', [AgricultureController::class, 'storeShipmentDelivery'])->name('shipment-deliveries.store');
        Route::patch('/shipment-deliveries/{shipment}/status', [AgricultureController::class, 'updateShipmentStatus'])->name('shipment-deliveries.update-status');
        Route::post('/market-prices', [AgricultureController::class, 'storeMarketPrice'])->name('market-prices.store');
        Route::post('/daily-work-sheets', [AgricultureController::class, 'storeDailyWorkSheet'])->name('daily-work-sheets.store');
        Route::patch('/daily-work-sheets/harvest-items/{item}/banana-weights', [AgricultureController::class, 'updateBananaWeights'])->name('daily-work-sheets.update-banana-weights');
        Route::post('/daily-work-sheets/{sheet}/approve', [AgricultureController::class, 'approveDailyWorkSheet'])->name('daily-work-sheets.approve');
        Route::delete('/work-plans/{workPlan}', [AgricultureController::class, 'destroyWorkPlan'])->name('work-plans.destroy');
        Route::delete('/fertilization-runs/{run}', [AgricultureController::class, 'destroyFertilizationRun'])->name('fertilization-runs.destroy');
        Route::delete('/irrigation-schedules/{schedule}', [AgricultureController::class, 'destroyIrrigationSchedule'])->name('irrigation-schedules.destroy');
        Route::delete('/spraying-applications/{app}', [AgricultureController::class, 'destroySprayingApplication'])->name('spraying-applications.destroy');
        Route::delete('/water-analyses/{log}', [AgricultureController::class, 'destroyWaterAnalysis'])->name('water-analyses.destroy');
        Route::delete('/raw-water-controls/{control}', [AgricultureController::class, 'destroyRawWaterControl'])->name('raw-water-controls.destroy');
        Route::delete('/purification-controls/{control}', [AgricultureController::class, 'destroyPurificationControl'])->name('purification-controls.destroy');
        Route::delete('/customer-orders/{order}', [AgricultureController::class, 'destroyCustomerOrder'])->name('customer-orders.destroy');
        Route::delete('/shipment-deliveries/{shipment}', [AgricultureController::class, 'destroyShipmentDelivery'])->name('shipment-deliveries.destroy');
        Route::delete('/daily-work-sheets/{sheet}', [AgricultureController::class, 'destroyDailyWorkSheet'])->name('daily-work-sheets.destroy');
        Route::delete('/market-prices/{price}', [AgricultureController::class, 'destroyMarketPrice'])->name('market-prices.destroy');
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
