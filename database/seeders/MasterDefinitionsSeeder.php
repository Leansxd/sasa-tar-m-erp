<?php

namespace Database\Seeders;

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
use Illuminate\Database\Seeder;

class MasterDefinitionsSeeder extends Seeder
{
    public function run(): void
    {
        $company1 = Company::create([
            'name' => 'SASA Tarım A.Ş.',
            'code' => 'SASA01',
            'tax_number' => '1234567890',
            'dia_company_code' => 'DIA_SASA_01',
            'is_active' => true,
        ]);

        $company2 = Company::create([
            'name' => 'SASA Organik Üretim Ltd.',
            'code' => 'SASA02',
            'tax_number' => '9876543210',
            'dia_company_code' => 'DIA_SASA_02',
            'is_active' => true,
        ]);

        $p1 = Personnel::create([
            'first_name' => 'Ahmet',
            'last_name' => 'Yılmaz',
            'phone' => '05320000001',
            'role_title' => 'İşletme Müdürü',
            'permissions' => ['all'],
            'company_ids' => [$company1->id, $company2->id],
            'can_enter_backdated_data' => true,
            'is_active' => true,
        ]);

        Personnel::create([
            'parent_personnel_id' => $p1->id,
            'first_name' => 'Mehmet',
            'last_name' => 'Demir',
            'phone' => '05320000002',
            'role_title' => 'Sera Sorumlusu',
            'permissions' => ['forms.fill', 'forms.view'],
            'company_ids' => [$company1->id],
            'can_enter_backdated_data' => false,
            'is_active' => true,
        ]);

        TradingParty::create([
            'name' => 'Migros Ticaret A.Ş.',
            'type' => 'buyer',
            'dia_cari_code' => 'CARI_MIGROS',
            'tax_number' => '6220033112',
            'phone' => '02165550000',
            'email' => 'siparis@migros.com.tr',
            'address' => 'İstanbul',
            'default_transport_fee' => 1500.00,
        ]);

        TradingParty::create([
            'name' => 'Toros Gübre Sanayi',
            'type' => 'seller',
            'dia_cari_code' => 'CARI_TOROS',
            'tax_number' => '8500112233',
            'phone' => '02124440000',
            'address' => 'Mersin',
            'default_transport_fee' => 0.00,
        ]);

        DeliveryType::create([
            'name' => 'Tesis Teslimi (Müşteri Aracı)',
            'code' => 'DEL_CUSTOMER',
            'transport_by' => 'customer',
            'is_fee_included' => true,
            'extra_fee' => 0.00,
        ]);

        DeliveryType::create([
            'name' => 'Adrese Sevk (Nakliye Dahil)',
            'code' => 'DEL_COMPANY_INC',
            'transport_by' => 'company',
            'is_fee_included' => true,
            'extra_fee' => 0.00,
        ]);

        DeliveryType::create([
            'name' => 'Adrese Sevk (Ek Ücretli Nakliye)',
            'code' => 'DEL_COMPANY_EXTRA',
            'transport_by' => 'company',
            'is_fee_included' => false,
            'extra_fee' => 2500.00,
        ]);

        $loc1 = ProductionLocation::create([
            'company_id' => $company1->id,
            'name' => 'Antalya Serik Serası',
            'location_type' => 'greenhouse',
            'dia_branch_code' => 'SUBE_ANT',
            'dia_warehouse_code' => 'DEPO_SERIK',
            'total_area_dekar' => 50.00,
            'approx_plant_count' => 120000,
        ]);

        $sec1 = $loc1->sections()->create([
            'name' => 'Sera Block A',
            'section_type' => 'greenhouse',
            'area_dekar' => 25.00,
            'tunnel_count' => 14,
            'table_stand_count' => 280,
            'approx_plant_count' => 60000,
        ]);

        $loc1->valves()->create([
            'production_section_id' => $sec1->id,
            'valve_number' => 'V-01',
            'name' => 'Vana 1 Sulama',
            'duty' => 'irrigation',
        ]);

        $loc1->valves()->create([
            'production_section_id' => $sec1->id,
            'valve_number' => 'V-22',
            'name' => 'Vana 22 Sisleme',
            'duty' => 'misting',
        ]);

        $prod1 = Product::create([
            'name' => 'Albion Çilek',
            'code' => 'PRD_STRAWBERRY',
            'product_type' => 'produced',
            'dia_stock_code' => 'STK_CILEK',
            'description' => 'Taze Sofralık Çilek',
        ]);

        $prod1->subtypes()->createMany([
            ['name' => 'Çilek 1. Kalite', 'code' => 'KALITE_1'],
            ['name' => 'Çilek 2. Kalite / Sanayi', 'code' => 'KALITE_2'],
        ]);

        $prod1->companies()->attach([$company1->id, $company2->id]);

        $prod2 = Product::create([
            'name' => 'Muz Fidesi',
            'code' => 'PRD_BANANA_SEEDLING',
            'product_type' => 'consumed',
            'dia_stock_code' => 'STK_MUZ_FIDE',
            'description' => 'Dikimlik Muz Fidesi',
        ]);

        $unit1 = UnitDefinition::create(['name' => 'Kilogram', 'symbol' => 'kg', 'unit_category' => 'quantity']);
        $unit2 = UnitDefinition::create(['name' => 'Dekar', 'symbol' => 'da', 'unit_category' => 'area']);
        $unit3 = UnitDefinition::create(['name' => 'Tünel Sayısı', 'symbol' => 'tünel', 'unit_category' => 'count']);
        $unit4 = UnitDefinition::create(['name' => 'Sehpa Sayısı', 'symbol' => 'sehpa', 'unit_category' => 'count']);

        $job1 = JobType::create([
            'name' => 'Hasat / Ürün Toplama',
            'code' => 'JOB_HARVEST',
            'form_type' => 'harvest_form',
            'description' => 'Günlük ürün toplama ve miktar kaydı',
        ]);
        $job1->units()->attach([$unit1->id]);

        $job2 = JobType::create([
            'name' => 'Fidan Dikimi',
            'code' => 'JOB_PLANTING',
            'form_type' => 'planting_form',
            'description' => 'Sera ve bahçe fidan dikim takibi',
        ]);
        $job2->units()->attach([$unit2->id, $unit3->id, $unit4->id]);

        PackagingDefinition::create([
            'product_id' => $prod1->id,
            'name' => '500g Plastik Kase',
            'code' => 'PKG_KASE_500',
            'dia_stock_code' => 'STK_PKG_500',
            'capacity_qty' => 0.50,
            'unit_id' => $unit1->id,
        ]);

        $crew1 = CrewLeader::create([
            'first_name' => 'Hasan',
            'last_name' => 'Kaya (Ekip Başı)',
            'identity_number' => '11223344556',
            'phone' => '05421112233',
            'origin_city' => 'Şanlıurfa',
            'daily_wage' => 800.00,
            'multiplier' => 1.50,
            'is_leader_fee_included' => false,
            'min_car_requirement' => 2,
            'travel_fee_per_car' => 600.00,
            'is_food_included' => false,
            'dia_cari_code' => 'CARI_CAVUS_HASAN',
        ]);

        Worker::create([
            'crew_leader_id' => $crew1->id,
            'first_name' => 'Ali',
            'last_name' => 'Kaya',
            'identity_number' => '22334455667',
            'performance_rating' => 5,
            'notes' => 'Çok hızlı ve verimli',
            'is_active' => true,
        ]);

        CateringSupplier::create([
            'company_title' => 'Akdeniz Yemekçilik Tic. Ltd.',
            'contact_person' => 'Mustafa Bey',
            'phone' => '02422223344',
            'meal_unit_price' => 120.00,
            'is_vat_included' => true,
            'dia_cari_code' => 'CARI_AKDENIZ_YEMEK',
        ]);

        $fRecipe = FertilizationRecipe::create([
            'name' => 'Çiçeklenme Dönemi Reçetesi',
            'creation_date' => now()->toDateString(),
            'duration_condition' => 'Çiçeklenme sonuna kadar',
            'creator_name' => 'Dr. Ziraat Müh. Selin Can',
            'water_ph' => 6.20,
            'water_ec' => 1.80,
            'water_notes' => 'Kuyu suyu pH düşürme sonrası uygulansın',
            'is_active' => true,
        ]);

        $tankA = $fRecipe->tanks()->create([
            'tank_name' => 'A Tankı',
            'capacity_liters' => 1000.00,
        ]);

        $tankA->items()->create([
            'product_name' => 'Kalsiyum Nitrat',
            'brand' => 'Toros',
            'quantity' => 25.00,
            'unit' => 'kg',
            'usage_purpose' => 'Hücre yapısı güçlendirme',
        ]);

        $sRecipe = SprayingRecipe::create([
            'name' => 'Kırmızı Örümcek Mücadele Reçetesi',
            'creation_date' => now()->toDateString(),
            'usage_time' => 'Sabah erken saatlerde',
            'usage_purpose' => 'Zararlı kontrolü',
            'water_volume_liters' => 400.00,
            'application_method' => 'sprayer_machine',
            'is_active' => true,
        ]);

        $sRecipe->items()->create([
            'product_name' => 'Abamectin 18g/l',
            'brand' => 'Hektaş',
            'quantity' => 250.00,
            'unit' => 'ml',
            'notes' => 'Homojen püskürtme yapılsın',
        ]);

        $wSource = WaterSource::create([
            'production_location_id' => $loc1->id,
            'name' => 'Ana Derin Kuyu Pompası',
            'active_cycle_minutes' => 45,
            'passive_cycle_minutes' => 90,
            'requires_photo_verification' => true,
            'is_active' => true,
        ]);

        $filter = Filter::create([
            'production_location_id' => $loc1->id,
            'name' => 'Otomatik Disk Filtre Grubu',
            'cleaning_cycle_days' => 2,
            'requires_photo_verification' => true,
            'is_active' => true,
        ]);

        $filter->waterSources()->attach($wSource->id);
    }
}
