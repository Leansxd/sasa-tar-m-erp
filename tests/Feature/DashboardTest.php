<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CrewLeader;
use App\Models\DailyWorkSheet;
use App\Models\Product;
use App\Models\ProductionLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;
    protected ProductionLocation $location;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['is_admin' => true]);

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
    }

    public function test_dashboard_renders_for_admin(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));
        $response->assertStatus(200);
    }

    public function test_dashboard_calculates_metrics_with_approved_sheets(): void
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
            'name' => 'Çilek',
        ]);

        $sheet = DailyWorkSheet::create([
            'work_date' => now()->toDateString(),
            'company_id' => $this->company->id,
            'production_location_id' => $this->location->id,
            'storage_destination' => 'direct_sale',
            'status' => 'approved',
            'created_by_id' => $this->user->id,
        ]);

        $sheet->crewLeaders()->create([
            'crew_leader_id' => $crewLeader->id,
            'worker_count' => 5,
            'car_count' => 1,
            'travel_fee' => 100,
            'meal_fee' => 250,
            'calculated_wage_total' => 2850,
        ]);

        $sheet->harvestItems()->create([
            'product_id' => $product->id,
            'quantity' => 500,
            'unit_price' => 50,
            'total_revenue' => 25000,
            'crop_type' => 'strawberry',
        ]);

        $response = $this->actingAs($this->user)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('analytics.today')
            ->where('analytics.today.revenue', 25000)
            ->where('analytics.today.harvest_kg', 500)
            ->where('analytics.today.total_cost', 2850)
            ->where('analytics.today.travel_fee', 100)
            ->where('analytics.today.meal_fee', 250)
            ->where('analytics.today.labor_wage', 2500)
            ->where('analytics.today.net_profit', 22150)
            ->where('analytics.today.profit_margin', 88.6)
            ->where('analytics.today.products.0.estimated_cost', 2850)
            ->where('analytics.today.locations.0.cost', 2850)
            ->where('analytics.today.crew_leaders.0.total_cost', 2850)
            ->where('analytics.today.crew_leaders.0.wage_total', 2500)
        );
    }
}
