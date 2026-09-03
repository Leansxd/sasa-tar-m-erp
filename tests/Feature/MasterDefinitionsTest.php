<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDefinitionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_definitions_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('definitions.index'));

        $response->assertStatus(200);
    }

    public function test_company_can_be_created(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('definitions.companies.store'), [
            'name' => 'Test Tarım A.Ş.',
            'code' => 'TEST_01',
            'tax_number' => '1111111111',
            'dia_company_code' => 'DIA_TEST',
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('firmalar', [
            'code' => 'TEST_01',
            'name' => 'Test Tarım A.Ş.',
        ]);
    }

    public function test_personnel_with_user_account_can_be_created(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('definitions.personnels.store'), [
            'first_name' => 'Ahmet',
            'last_name' => 'Personel',
            'phone' => '05331112233',
            'email' => 'ahmet.personel@sasa.com',
            'password' => 'secret123',
            'role_title' => 'Saha Müdürü',
            'can_enter_backdated_data' => true,
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('personeller', [
            'first_name' => 'Ahmet',
            'last_name' => 'Personel',
            'phone' => '05331112233',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'ahmet.personel@sasa.com',
            'name' => 'Ahmet Personel',
        ]);
    }

    public function test_non_admin_user_without_permission_is_blocked_from_definitions(): void
    {
        $restrictedUser = User::factory()->create(['is_admin' => false]);
        \App\Models\Personnel::create([
            'user_id' => $restrictedUser->id,
            'first_name' => 'Kısıtlı',
            'last_name' => 'Kullanıcı',
            'permissions' => ['tesis', 'uretim'],
            'is_active' => true,
        ]);

        $response = $this->actingAs($restrictedUser)->get(route('definitions.index'));
        $response->assertStatus(403);
    }
}
