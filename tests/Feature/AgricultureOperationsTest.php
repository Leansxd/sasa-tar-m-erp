<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CrewLeader;
use App\Models\DeliveryType;
use App\Models\FertilizationRecipe;
use App\Models\FertilizationTank;
use App\Models\JobType;
use App\Models\PackagingDefinition;
use App\Models\Personnel;
use App\Models\Product;
use App\Models\ProductionLocation;
use App\Models\SprayingRecipe;
use App\Models\TradingParty;
use App\Models\User;
use App\Models\WaterSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgricultureOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;
    protected ProductionLocation $location;
    protected WaterSource $waterSource;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->company = Company::create([
            'code' => 'COMP01',
            'name' => 'SASA Tarım A.Ş.',
            'tax_number' => '1234567890',
        ]);

        $this->location = ProductionLocation::create([
            'company_id' => $this->company->id,
            'code' => 'LOC01',
            'name' => 'Serik Çilek Serası 1',
        ]);

        $this->waterSource = WaterSource::create([
            'production_location_id' => $this->location->id,
            'name' => 'Derin Kuyu Pompası 1',
            'type' => 'well',
        ]);
    }

    public function test_agriculture_index_page_loads_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->user)->get(route('agriculture.index'));
        $response->assertStatus(200);
    }

    public function test_can_create_work_plan(): void
    {
        $response = $this->actingAs($this->user)->post(route('agriculture.work-plans.store'), [
            'title' => 'Toprak Havalandırma',
            'production_location_id' => $this->location->id,
            'plan_date' => now()->toDateString(),
            'status' => 'pending',
            'description' => 'Tünel 1-3 havalandırılacak',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('is_planlari', [
            'title' => 'Toprak Havalandırma',
        ]);
    }

    public function test_purification_control_triggers_pressure_warning(): void
    {
        $response = $this->actingAs($this->user)->post(route('agriculture.purification-controls.store'), [
            'water_source_id' => $this->waterSource->id,
            'control_date' => now()->toDateString(),
            'inlet_pressure_bar' => 4.0,
            'outlet_pressure_bar' => 1.5,
            'max_threshold_bar' => 1.5,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('aritma_suyu_kontrolleri', [
            'has_warning' => true,
            'delta_pressure_bar' => 2.5,
        ]);
    }

    public function test_fertilization_run_deactivates_previous_recipe(): void
    {
        $recipe1 = FertilizationRecipe::create([
            'company_id' => $this->company->id,
            'creation_date' => now()->toDateString(),
            'name' => 'Vejetatif Reçete'
        ]);
        $recipe2 = FertilizationRecipe::create([
            'company_id' => $this->company->id,
            'creation_date' => now()->toDateString(),
            'name' => 'Çiçeklenme Reçetesi'
        ]);

        $this->actingAs($this->user)->post(route('agriculture.fertilization-runs.store'), [
            'fertilization_recipe_id' => $recipe1->id,
            'start_date' => now()->toDateString(),
            'end_condition' => 'Çiçeklenmeye kadar',
        ]);

        $this->actingAs($this->user)->post(route('agriculture.fertilization-runs.store'), [
            'fertilization_recipe_id' => $recipe2->id,
            'start_date' => now()->toDateString(),
            'end_condition' => 'Hasat sonuna kadar',
        ]);

        $this->assertDatabaseHas('gubre_uygulamalari', [
            'fertilization_recipe_id' => $recipe1->id,
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('gubre_uygulamalari', [
            'fertilization_recipe_id' => $recipe2->id,
            'is_active' => true,
        ]);
    }

    public function test_can_create_daily_work_sheet_and_calculate_crew_wage(): void
    {
        $crewLeader = CrewLeader::create([
            'company_id' => $this->company->id,
            'code' => 'CL-01',
            'first_name' => 'Ahmet',
            'last_name' => 'Çavuş',
            'phone' => '05320000000',
            'daily_rate' => 500,
            'leader_rate' => 600,
            'travel_fee' => 100,
            'meal_fee' => 50,
        ]);

        $product = Product::create([
            'company_id' => $this->company->id,
            'code' => 'P-01',
            'name' => 'Çilek (Rubygem)',
        ]);

        $response = $this->actingAs($this->user)->post(route('agriculture.daily-work-sheets.store'), [
            'work_date' => now()->toDateString(),
            'company_id' => $this->company->id,
            'production_location_id' => $this->location->id,
            'has_external_workers' => true,
            'has_internal_workers' => true,
            'storage_destination' => 'direct_sale',
            'crew_leaders' => [
                [
                    'crew_leader_id' => $crewLeader->id,
                    'worker_count' => 10,
                    'car_count' => 1,
                    'overtime_hours' => 2,
                    'extra_wage_per_worker' => 50,
                ]
            ],
            'harvest_items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1000,
                    'unit_price' => 90,
                    'crop_type' => 'strawberry',
                ]
            ]
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('gunluk_isci_formlari', [
            'company_id' => $this->company->id,
        ]);
        $this->assertDatabaseHas('gunluk_form_cavuslar', [
            'crew_leader_id' => $crewLeader->id,
            'worker_count' => 10,
        ]);
    }
}
