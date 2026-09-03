<?php

namespace Database\Seeders;

use App\Models\CateringSupplier;
use App\Models\Company;
use App\Models\CrewLeader;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderItem;
use App\Models\DailyWorkSheet;
use App\Models\DeliveryType;
use App\Models\FertilizationRecipe;
use App\Models\FertilizationRun;
use App\Models\Filter;
use App\Models\JobType;
use App\Models\LocationValve;
use App\Models\MarketPrice;
use App\Models\PackagingDefinition;
use App\Models\Personnel;
use App\Models\Product;
use App\Models\ProductSubtype;
use App\Models\ProductionLocation;
use App\Models\ProductionSection;
use App\Models\PurificationControl;
use App\Models\RawWaterControl;
use App\Models\ShipmentDelivery;
use App\Models\SprayingApplication;
use App\Models\SprayingRecipe;
use App\Models\TradingParty;
use App\Models\UnitDefinition;
use App\Models\User;
use App\Models\WaterAnalysisLog;
use App\Models\WaterSource;
use App\Models\Worker;
use App\Models\WorkPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::create([
            'name' => 'Ahmet Yılmaz',
            'email' => 'admin@sasa.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        $selinUser = User::create([
            'name' => 'Dr. Selin Aksoy',
            'email' => 'ziraat.selin@sasa.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $mustafaUser = User::create([
            'name' => 'Mustafa Çelik',
            'email' => 'serasorumlusu.mustafa@sasa.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $kemalUser = User::create([
            'name' => 'Kemal Aydın',
            'email' => 'teknik.kemal@sasa.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $merveUser = User::create([
            'name' => 'Merve Koç',
            'email' => 'lojistik.merve@sasa.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $emreUser = User::create([
            'name' => 'Emre Öztürk',
            'email' => 'muhasebe.emre@sasa.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $company1 = Company::create([
            'name' => 'SASA Tarım İşletmeleri A.Ş.',
            'code' => 'SASA-01',
            'tax_number' => '7480521943',
            'dia_company_code' => 'SASA-01',
            'is_active' => true,
        ]);

        $company2 = Company::create([
            'name' => 'SASA Sera & Tohumculuk Ltd. Şti.',
            'code' => 'SASA-02',
            'tax_number' => '8390145267',
            'dia_company_code' => 'SASA-02',
            'is_active' => true,
        ]);

        $persAdmin = Personnel::create([
            'user_id' => $adminUser->id,
            'first_name' => 'Ahmet',
            'last_name' => 'Yılmaz',
            'phone' => '0532 100 0001',
            'role_title' => 'Genel Müdür / ERP Yöneticisi',
            'permissions' => ['tesis', 'uretim', 'teknik', 'operasyon', 'raporlar', 'tanimlamalar'],
            'company_ids' => [$company1->id, $company2->id],
            'can_enter_backdated_data' => true,
            'is_active' => true,
        ]);

        $persSelin = Personnel::create([
            'user_id' => $selinUser->id,
            'parent_personnel_id' => $persAdmin->id,
            'first_name' => 'Selin',
            'last_name' => 'Aksoy',
            'phone' => '0532 200 0002',
            'role_title' => 'Baş Ziraat Mühendisi',
            'permissions' => ['tesis', 'uretim', 'teknik', 'raporlar'],
            'company_ids' => [$company1->id, $company2->id],
            'can_enter_backdated_data' => true,
            'is_active' => true,
        ]);

        $persMustafa = Personnel::create([
            'user_id' => $mustafaUser->id,
            'parent_personnel_id' => $persSelin->id,
            'first_name' => 'Mustafa',
            'last_name' => 'Çelik',
            'phone' => '0533 300 0003',
            'role_title' => 'Silifke Sera & Saha Şefi',
            'permissions' => ['tesis', 'uretim', 'operasyon'],
            'company_ids' => [$company1->id],
            'can_enter_backdated_data' => false,
            'is_active' => true,
        ]);

        $persKemal = Personnel::create([
            'user_id' => $kemalUser->id,
            'parent_personnel_id' => $persMustafa->id,
            'first_name' => 'Kemal',
            'last_name' => 'Aydın',
            'phone' => '0534 400 0004',
            'role_title' => 'Sulama & Otomasyon Teknikeri',
            'permissions' => ['tesis', 'uretim', 'teknik'],
            'company_ids' => [$company1->id, $company2->id],
            'can_enter_backdated_data' => false,
            'is_active' => true,
        ]);

        $persMerve = Personnel::create([
            'user_id' => $merveUser->id,
            'parent_personnel_id' => $persAdmin->id,
            'first_name' => 'Merve',
            'last_name' => 'Koç',
            'phone' => '0535 500 0005',
            'role_title' => 'Lojistik & Depo Sorumlusu',
            'permissions' => ['operasyon', 'raporlar'],
            'company_ids' => [$company1->id, $company2->id],
            'can_enter_backdated_data' => false,
            'is_active' => true,
        ]);

        $persEmre = Personnel::create([
            'user_id' => $emreUser->id,
            'parent_personnel_id' => $persAdmin->id,
            'first_name' => 'Emre',
            'last_name' => 'Öztürk',
            'phone' => '0536 600 0006',
            'role_title' => 'Finans & Maliyet Uzmanı',
            'permissions' => ['raporlar', 'tanimlamalar'],
            'company_ids' => [$company1->id, $company2->id],
            'can_enter_backdated_data' => true,
            'is_active' => true,
        ]);

        $loc1 = ProductionLocation::create([
            'company_id' => $company1->id,
            'name' => 'Silifke Çilek Üretim Serası',
            'location_type' => 'greenhouse',
            'total_area_dekar' => 55.0,
            'approx_plant_count' => 165000,
            'dia_branch_code' => 'SUBE-SLF',
            'dia_warehouse_code' => 'DEP-SLF-01',
        ]);

        $loc2 = ProductionLocation::create([
            'company_id' => $company1->id,
            'name' => 'Anamur Muz & Egzotik Meyve Tesisi',
            'location_type' => 'mixed',
            'total_area_dekar' => 40.0,
            'approx_plant_count' => 8500,
            'dia_branch_code' => 'SUBE-ANM',
            'dia_warehouse_code' => 'DEP-ANM-01',
        ]);

        $loc3 = ProductionLocation::create([
            'company_id' => $company2->id,
            'name' => 'Gazipaşa Avokado & Ejder Meyvesi Serası',
            'location_type' => 'greenhouse',
            'total_area_dekar' => 35.0,
            'approx_plant_count' => 6000,
            'dia_branch_code' => 'SUBE-GZP',
            'dia_warehouse_code' => 'DEP-GZP-01',
        ]);

        ProductionSection::create([
            'production_location_id' => $loc1->id,
            'name' => 'Tünel Blok A (Tünel 1-6)',
            'section_type' => 'greenhouse',
            'area_dekar' => 28.0,
            'tunnel_count' => 6,
            'table_stand_count' => 380,
            'approx_plant_count' => 84000,
        ]);

        ProductionSection::create([
            'production_location_id' => $loc1->id,
            'name' => 'Tünel Blok B (Tünel 7-12)',
            'section_type' => 'greenhouse',
            'area_dekar' => 27.0,
            'tunnel_count' => 6,
            'table_stand_count' => 370,
            'approx_plant_count' => 81000,
        ]);

        ProductionSection::create([
            'production_location_id' => $loc2->id,
            'name' => 'Muz Parseli 1-2 (Ana Sera)',
            'section_type' => 'greenhouse',
            'area_dekar' => 25.0,
            'tunnel_count' => 4,
            'table_stand_count' => 0,
            'approx_plant_count' => 5500,
        ]);

        LocationValve::create(['production_location_id' => $loc1->id, 'valve_number' => 'V-01', 'name' => 'Tünel A-1 Sulama', 'duty' => 'irrigation']);
        LocationValve::create(['production_location_id' => $loc1->id, 'valve_number' => 'V-02', 'name' => 'Tünel A-2 Gübreleme', 'duty' => 'fertigation']);
        LocationValve::create(['production_location_id' => $loc1->id, 'valve_number' => 'V-03', 'name' => 'Tünel A Sisleme & Nemlendirme', 'duty' => 'misting']);
        LocationValve::create(['production_location_id' => $loc1->id, 'valve_number' => 'V-04', 'name' => 'Tünel B-1 Sulama', 'duty' => 'irrigation']);
        LocationValve::create(['production_location_id' => $loc1->id, 'valve_number' => 'V-05', 'name' => 'Tünel B-2 Gübreleme', 'duty' => 'fertigation']);
        LocationValve::create(['production_location_id' => $loc1->id, 'valve_number' => 'V-06', 'name' => 'Tünel B Sisleme', 'duty' => 'misting']);

        LocationValve::create(['production_location_id' => $loc2->id, 'valve_number' => 'V-01', 'name' => 'Muz Parseli Damlama', 'duty' => 'irrigation']);
        LocationValve::create(['production_location_id' => $loc2->id, 'valve_number' => 'V-02', 'name' => 'Muz Parseli Gübre Enjeksiyon', 'duty' => 'fertigation']);

        $uKg = UnitDefinition::create(['name' => 'Kilogram', 'symbol' => 'kg', 'unit_category' => 'quantity']);
        $uDal = UnitDefinition::create(['name' => 'Dal / Hevenk', 'symbol' => 'dal', 'unit_category' => 'count']);
        $uAdet = UnitDefinition::create(['name' => 'Adet', 'symbol' => 'ad', 'unit_category' => 'count']);
        $uDekar = UnitDefinition::create(['name' => 'Dekar', 'symbol' => 'da', 'unit_category' => 'area']);
        $uKasa = UnitDefinition::create(['name' => 'Kasa', 'symbol' => 'kasa', 'unit_category' => 'count']);

        $prodStrawberry = Product::create([
            'name' => 'Albion Çilek',
            'code' => 'PRD-CLK',
            'dia_stock_code' => 'STK-CLK-001',
            'product_type' => 'produced',
        ]);
        $prodStrawberry->companies()->attach([$company1->id, $company2->id]);

        $stStrawExport = ProductSubtype::create(['product_id' => $prodStrawberry->id, 'name' => '1. Kalite İhracatlık Çilek', 'code' => 'CLK-EXP']);
        $stStrawLocal = ProductSubtype::create(['product_id' => $prodStrawberry->id, 'name' => '2. Kalite İç Piyasa Çilek', 'code' => 'CLK-LOC']);
        $stStrawIndustry = ProductSubtype::create(['product_id' => $prodStrawberry->id, 'name' => 'Sanayi / Reçellik Çilek', 'code' => 'CLK-IND']);

        $prodBanana = Product::create([
            'name' => 'Grand Nain Muz',
            'code' => 'PRD-MUZ',
            'dia_stock_code' => 'STK-MUZ-001',
            'product_type' => 'produced',
        ]);
        $prodBanana->companies()->attach([$company1->id]);

        $stBananaLux = ProductSubtype::create(['product_id' => $prodBanana->id, 'name' => 'Extra Lüks Dal Muz', 'code' => 'MUZ-LUX']);
        $stBananaFirst = ProductSubtype::create(['product_id' => $prodBanana->id, 'name' => '1. Sınıf Standart Muz', 'code' => 'MUZ-STD']);

        $prodAvocado = Product::create([
            'name' => 'Hass Avokado',
            'code' => 'PRD-AVK',
            'dia_stock_code' => 'STK-AVK-001',
            'product_type' => 'produced',
        ]);
        $prodAvocado->companies()->attach([$company2->id]);
        $stAvocado1 = ProductSubtype::create(['product_id' => $prodAvocado->id, 'name' => '1. Kalite İri Boy Avokado', 'code' => 'AVK-01']);

        $prodPitaya = Product::create([
            'name' => 'Pitaya (Ejder Meyvesi)',
            'code' => 'PRD-EJD',
            'dia_stock_code' => 'STK-EJD-001',
            'product_type' => 'produced',
        ]);
        $prodPitaya->companies()->attach([$company2->id]);
        $stPitayaRed = ProductSubtype::create(['product_id' => $prodPitaya->id, 'name' => 'Kırmızı Etli A Kalite', 'code' => 'EJD-RED']);

        $pkgStrawWood5 = PackagingDefinition::create([
            'product_id' => $prodStrawberry->id,
            'name' => '5 Kg Ahşap Çilek Kasası',
            'code' => 'PKG-CLK-5KG',
            'dia_stock_code' => 'STK-KASA-5KG',
            'capacity_qty' => 5.0,
            'unit_id' => $uKg->id,
        ]);

        $pkgStrawCup500 = PackagingDefinition::create([
            'product_id' => $prodStrawberry->id,
            'name' => '500gr Şeffaf Plastik Kap',
            'code' => 'PKG-CLK-500G',
            'dia_stock_code' => 'STK-KAP-500G',
            'capacity_qty' => 0.5,
            'unit_id' => $uKg->id,
        ]);

        $pkgBananaBox18 = PackagingDefinition::create([
            'product_id' => $prodBanana->id,
            'name' => '18 Kg Teleskopik Muz Kolisi',
            'code' => 'PKG-MUZ-18KG',
            'dia_stock_code' => 'STK-KUTU-18KG',
            'capacity_qty' => 18.0,
            'unit_id' => $uKg->id,
        ]);

        $pkgAvocadoBox4 = PackagingDefinition::create([
            'product_id' => $prodAvocado->id,
            'name' => '4 Kg İhracat Karton Kutu',
            'code' => 'PKG-AVK-4KG',
            'dia_stock_code' => 'STK-AVK-4KG',
            'capacity_qty' => 4.0,
            'unit_id' => $uKg->id,
        ]);

        $jobHarvest = JobType::create(['name' => 'Hasat / Ürün Toplama', 'code' => 'JOB-HST', 'form_type' => 'harvest']);
        $jobPruning = JobType::create(['name' => 'Budama ve Yaprak Temizliği', 'code' => 'JOB-BDM', 'form_type' => 'pruning']);
        $jobSpraying = JobType::create(['name' => 'Zirai İlaçlama Uygulaması', 'code' => 'JOB-ILC', 'form_type' => 'spraying']);
        $jobPlanting = JobType::create(['name' => 'Fidan Dikimi & Şaşırtma', 'code' => 'JOB-FDN', 'form_type' => 'planting']);
        $jobDaily = JobType::create(['name' => 'Günlük Saha & Çapa İşi', 'code' => 'JOB-DAY', 'form_type' => 'daily_worker']);

        $jobHarvest->units()->attach([$uKg->id, $uKasa->id, $uDal->id]);
        $jobPruning->units()->attach([$uDekar->id, $uAdet->id]);
        $jobSpraying->units()->attach([$uDekar->id]);

        $dtColdTruck = DeliveryType::create([
            'name' => 'SASA Soğuk Zincir Aracıyla Sevk',
            'code' => 'DEL-SASA-COLD',
            'transport_by' => 'company',
            'is_fee_included' => true,
            'extra_fee' => 0.0,
        ]);

        $dtCustomerRamp = DeliveryType::create([
            'name' => 'Müşteri Kendi Aracıyla Tesiste Teslim',
            'code' => 'DEL-CUSTOMER-RAMP',
            'transport_by' => 'customer',
            'is_fee_included' => true,
            'extra_fee' => 0.0,
        ]);

        $dtContractedLogistics = DeliveryType::create([
            'name' => 'Anlaşmalı Lojistik / Ek Nakliyeli',
            'code' => 'DEL-LOGISTICS-FEE',
            'transport_by' => 'company',
            'is_fee_included' => false,
            'extra_fee' => 750.0,
        ]);

        $tpMigros = TradingParty::create([
            'name' => 'Migros Ticaret A.Ş. (Akdeniz Dağıtım Merkezi)',
            'type' => 'buyer',
            'dia_cari_code' => 'CAR-MIGROS',
            'tax_number' => '6220033112',
            'phone' => '0216 577 1000',
            'email' => 'tedarik@migros.com.tr',
            'address' => 'Antalya Lojistik Merkezi',
            'default_transport_fee' => 0.0,
        ]);

        $tpCarrefour = TradingParty::create([
            'name' => 'CarrefourSA A.Ş. (Taze Gıda Direktörlüğü)',
            'type' => 'buyer',
            'dia_cari_code' => 'CAR-CARREFOUR',
            'tax_number' => '2010045588',
            'phone' => '0216 655 0000',
            'email' => 'meyvesebze@carrefoursa.com',
            'address' => 'Mersin Dağıtım Deposu',
            'default_transport_fee' => 0.0,
        ]);

        $tpBim = TradingParty::create([
            'name' => 'BİM Birleşik Mağazalar A.Ş.',
            'type' => 'buyer',
            'dia_cari_code' => 'CAR-BIM',
            'tax_number' => '1750054411',
            'phone' => '0216 564 0303',
            'email' => 'satin_alma@bim.com.tr',
            'address' => 'Adana Bölge Depo',
            'default_transport_fee' => 0.0,
        ]);

        $tpHalKadıkoy = TradingParty::create([
            'name' => 'İstanbul Kadıköy Toptancı Hal Komisyonculuğu',
            'type' => 'buyer',
            'dia_cari_code' => 'CAR-IST-HAL',
            'tax_number' => '4810098877',
            'phone' => '0216 418 5544',
            'email' => 'siparis@istanbultoptancihal.com',
            'address' => 'Kadıköy Meyve Sebze Hali No: 42',
            'default_transport_fee' => 1200.0,
        ]);

        $tpToros = TradingParty::create([
            'name' => 'Toros Tarım Sanayi ve Ticaret A.Ş.',
            'type' => 'seller',
            'dia_cari_code' => 'CAR-TOROS',
            'tax_number' => '8500112233',
            'phone' => '0212 357 0202',
            'email' => 'satis@toros.com.tr',
            'address' => 'Mersin Fabrika',
            'default_transport_fee' => 0.0,
        ]);

        $tpHektas = TradingParty::create([
            'name' => 'Hektaş Ticaret T.A.Ş.',
            'type' => 'seller',
            'dia_cari_code' => 'CAR-HEKTAS',
            'tax_number' => '4610023344',
            'phone' => '0262 751 1412',
            'email' => 'bilgi@hektas.com.tr',
            'address' => 'Gebze Organize Sanayi',
            'default_transport_fee' => 0.0,
        ]);

        $catBereket = CateringSupplier::create([
            'company_title' => 'Bereket Kurumsal Tabldot & Yemek A.Ş.',
            'contact_person' => 'Süleyman Karaca',
            'phone' => '0324 336 8800',
            'meal_unit_price' => 140.0,
            'is_vat_included' => true,
            'dia_cari_code' => 'CAR-YEMEK-BEREKET',
        ]);

        $catAkdeniz = CateringSupplier::create([
            'company_title' => 'Akdeniz Lezzet Yemek Sanayi',
            'contact_person' => 'Fatma Hanım',
            'phone' => '0324 714 5520',
            'meal_unit_price' => 125.0,
            'is_vat_included' => false,
            'dia_cari_code' => 'CAR-YEMEK-AKDENIZ',
        ]);

        $clHasan = CrewLeader::create([
            'first_name' => 'Hasan',
            'last_name' => 'Baran',
            'identity_number' => '24890123456',
            'phone' => '0532 411 2233',
            'origin_city' => 'Şanlıurfa',
            'daily_wage' => 950.0,
            'multiplier' => 1.5,
            'is_leader_fee_included' => false,
            'min_car_requirement' => 2,
            'travel_fee_per_car' => 1200.0,
            'is_food_included' => true,
            'dia_cari_code' => 'CAR-CAVUS-HASAN',
        ]);

        $clMehmet = CrewLeader::create([
            'first_name' => 'Mehmet',
            'last_name' => 'Demirtaş',
            'identity_number' => '38491029384',
            'phone' => '0533 522 3344',
            'origin_city' => 'Hatay',
            'daily_wage' => 900.0,
            'multiplier' => 1.2,
            'is_leader_fee_included' => true,
            'min_car_requirement' => 2,
            'travel_fee_per_car' => 1000.0,
            'is_food_included' => false,
            'dia_cari_code' => 'CAR-CAVUS-MEHMET',
        ]);

        $clIbrahim = CrewLeader::create([
            'first_name' => 'İbrahim',
            'last_name' => 'Güneş',
            'identity_number' => '19283746501',
            'phone' => '0535 633 4455',
            'origin_city' => 'Mersin',
            'daily_wage' => 850.0,
            'multiplier' => 1.0,
            'is_leader_fee_included' => false,
            'min_car_requirement' => 1,
            'travel_fee_per_car' => 800.0,
            'is_food_included' => false,
            'dia_cari_code' => 'CAR-CAVUS-IBRAHIM',
        ]);

        $clRamazan = CrewLeader::create([
            'first_name' => 'Ramazan',
            'last_name' => 'Yıldırım',
            'identity_number' => '47583920192',
            'phone' => '0536 744 5566',
            'origin_city' => 'Adana',
            'daily_wage' => 920.0,
            'multiplier' => 1.3,
            'is_leader_fee_included' => false,
            'min_car_requirement' => 2,
            'travel_fee_per_car' => 1100.0,
            'is_food_included' => true,
            'dia_cari_code' => 'CAR-CAVUS-RAMAZAN',
        ]);

        $workersHasan = [
            ['first_name' => 'Halil', 'last_name' => 'Baran', 'tc' => '24890123401', 'rating' => 5, 'notes' => 'Hızlı toplayıcı, kasa fire oranı çok düşük.'],
            ['first_name' => 'Mahmut', 'last_name' => 'Baran', 'tc' => '24890123402', 'rating' => 5, 'notes' => 'Kantar tartım ve kasa diziminde dikkatli.'],
            ['first_name' => 'Fatma', 'last_name' => 'Baran', 'tc' => '24890123403', 'rating' => 4, 'notes' => 'Düzenli ve disiplinli çalışıyor.'],
            ['first_name' => 'Emine', 'last_name' => 'Doğan', 'tc' => '24890123404', 'rating' => 5, 'notes' => 'Hasat kalitesi çok iyi.'],
            ['first_name' => 'Zehra', 'last_name' => 'Şahin', 'tc' => '24890123405', 'rating' => 4, 'notes' => 'Temiz çalışıyor.'],
            ['first_name' => 'Yusuf', 'last_name' => 'Demir', 'tc' => '24890123406', 'rating' => 4, 'notes' => 'Taşıma ve yüklemede başarılı.'],
        ];
        foreach ($workersHasan as $w) {
            Worker::create([
                'crew_leader_id' => $clHasan->id,
                'first_name' => $w['first_name'],
                'last_name' => $w['last_name'],
                'identity_number' => $w['tc'],
                'performance_rating' => $w['rating'],
                'notes' => $w['notes'],
                'is_active' => true,
            ]);
        }

        $workersMehmet = [
            ['first_name' => 'Ali', 'last_name' => 'Demirtaş', 'tc' => '38491029301', 'rating' => 4, 'notes' => 'Sera içi budama ve toplama işlerinde deneyimli.'],
            ['first_name' => 'Serkan', 'last_name' => 'Kaya', 'tc' => '38491029302', 'rating' => 5, 'notes' => 'Yüksek hasat hızı.'],
            ['first_name' => 'Hüseyin', 'last_name' => 'Polat', 'tc' => '38491029303', 'rating' => 3, 'notes' => 'Kasa yerleşiminde daha dikkatli olması gerekiyor.'],
            ['first_name' => 'Ayşe', 'last_name' => 'Çetin', 'tc' => '38491029304', 'rating' => 4, 'notes' => 'Muz ambalajlamada titiz.'],
            ['first_name' => 'Meryem', 'last_name' => 'Kılıç', 'tc' => '38491029305', 'rating' => 5, 'notes' => 'Örnek çalışma performansı.'],
            ['first_name' => 'Ömer', 'last_name' => 'Faruk', 'tc' => '38491029306', 'rating' => 4, 'notes' => 'Ağır kasa taşımada yardımcı.'],
        ];
        foreach ($workersMehmet as $w) {
            Worker::create([
                'crew_leader_id' => $clMehmet->id,
                'first_name' => $w['first_name'],
                'last_name' => $w['last_name'],
                'identity_number' => $w['tc'],
                'performance_rating' => $w['rating'],
                'notes' => $w['notes'],
                'is_active' => true,
            ]);
        }

        $workersIbrahim = [
            ['first_name' => 'Veli', 'last_name' => 'Güneş', 'tc' => '19283746501', 'rating' => 4, 'notes' => 'Yerli tecrübeli işçi.'],
            ['first_name' => 'Hasan', 'last_name' => 'Kaya', 'tc' => '19283746502', 'rating' => 4, 'notes' => 'Düzenli devamlılık sağlıyor.'],
            ['first_name' => 'Derya', 'last_name' => 'Aslan', 'tc' => '19283746503', 'rating' => 5, 'notes' => 'Çilek toplama kalitesi mükemmel.'],
            ['first_name' => 'Songül', 'last_name' => 'Aydın', 'tc' => '19283746504', 'rating' => 4, 'notes' => 'Hassas meyve toplamada usta.'],
            ['first_name' => 'İsmail', 'last_name' => 'Kurt', 'tc' => '19283746505', 'rating' => 3, 'notes' => 'Hızlandırılması talep edildi.'],
            ['first_name' => 'Nazan', 'last_name' => 'Çiftçi', 'tc' => '19283746506', 'rating' => 5, 'notes' => 'Yüksek iş disiplini.'],
        ];
        foreach ($workersIbrahim as $w) {
            Worker::create([
                'crew_leader_id' => $clIbrahim->id,
                'first_name' => $w['first_name'],
                'last_name' => $w['last_name'],
                'identity_number' => $w['tc'],
                'performance_rating' => $w['rating'],
                'notes' => $w['notes'],
                'is_active' => true,
            ]);
        }

        $workersRamazan = [
            ['first_name' => 'Murat', 'last_name' => 'Yıldırım', 'tc' => '47583920101', 'rating' => 5, 'notes' => 'Muz dal kesimi ve askılamada uzman.'],
            ['first_name' => 'Kadir', 'last_name' => 'Yıldırım', 'tc' => '47583920102', 'rating' => 4, 'notes' => 'Sera içi taşıma ve istiflemede güvenilir.'],
            ['first_name' => 'Zeynep', 'last_name' => 'Kurt', 'tc' => '47583920103', 'rating' => 5, 'notes' => 'Ambalajlama ve etiketlemede hatasız.'],
            ['first_name' => 'Elif', 'last_name' => 'Arslan', 'tc' => '47583920104', 'rating' => 4, 'notes' => 'Yüksek verimle çalışan toplayıcı.'],
            ['first_name' => 'Burak', 'last_name' => 'Can', 'tc' => '47583920105', 'rating' => 4, 'notes' => 'Kantar ve tartım desteği sağlıyor.'],
            ['first_name' => 'Selim', 'last_name' => 'Genç', 'tc' => '47583920106', 'rating' => 5, 'notes' => 'Örnek çalışma ahlakı.'],
        ];
        foreach ($workersRamazan as $w) {
            Worker::create([
                'crew_leader_id' => $clRamazan->id,
                'first_name' => $w['first_name'],
                'last_name' => $w['last_name'],
                'identity_number' => $w['tc'],
                'performance_rating' => $w['rating'],
                'notes' => $w['notes'],
                'is_active' => true,
            ]);
        }

        $recipeStraw = FertilizationRecipe::create([
            'name' => 'Topraksız Çilek Büyütme ve Meyve Tutum Reçetesi',
            'creator_name' => 'Dr. Selin Aksoy',
            'creation_date' => now()->subDays(60)->toDateString(),
            'duration_condition' => 'Hasat sezonu boyunca her sulamada',
            'water_ph' => 5.8,
            'water_ec' => 1.85,
            'is_active' => true,
        ]);
        $tA = $recipeStraw->tanks()->create(['tank_name' => 'A Tankı (Kalsiyum & Mikro Elementler)', 'capacity_liters' => 1000]);
        $tA->items()->create(['product_name' => 'Kalsiyum Nitrat (YaraLiva Calcinit)', 'brand' => 'Yara', 'quantity' => 40.0, 'unit' => 'kg', 'usage_purpose' => 'Hücre duvarı ve meyve sertliği']);
        $tA->items()->create(['product_name' => 'Demir Şelat (EDDHA Fe %6)', 'brand' => 'Haifa', 'quantity' => 1.5, 'unit' => 'kg', 'usage_purpose' => 'Klorofil ve fotosentez desteği']);

        $tB = $recipeStraw->tanks()->create(['tank_name' => 'B Tankı (Potasyum, Fosfor, Magnezyum)', 'capacity_liters' => 1000]);
        $tB->items()->create(['product_name' => 'Monopotasyum Fosfat (MKP 0-52-34)', 'brand' => 'Haifa', 'quantity' => 25.0, 'unit' => 'kg', 'usage_purpose' => 'Köklenme ve çiçek tutumu']);
        $tB->items()->create(['product_name' => 'Potasyum Nitrat (13-0-46)', 'brand' => 'Doktor Tarsa', 'quantity' => 35.0, 'unit' => 'kg', 'usage_purpose' => 'Meyve iriliği ve briks şeker oranı']);
        $tB->items()->create(['product_name' => 'Magnezyum Sülfat', 'brand' => 'K+S', 'quantity' => 20.0, 'unit' => 'kg', 'usage_purpose' => 'Magnezyum ve kükürt takviyesi']);

        FertilizationRun::create([
            'fertilization_recipe_id' => $recipeStraw->id,
            'start_date' => now()->subDays(60)->toDateString(),
            'end_condition' => 'Sezon sonuna kadar aktif',
            'is_active' => true,
        ]);

        $recipeBanana = FertilizationRecipe::create([
            'name' => 'Muz Hevenk Besleme ve Gövde Kalınlaştırma Reçetesi',
            'creator_name' => 'Dr. Selin Aksoy',
            'creation_date' => now()->subDays(60)->toDateString(),
            'duration_condition' => '15 günde bir periyodik döngü',
            'water_ph' => 6.2,
            'water_ec' => 2.10,
            'is_active' => true,
        ]);
        $tMuzA = $recipeBanana->tanks()->create(['tank_name' => 'Muz A Tankı', 'capacity_liters' => 1500]);
        $tMuzA->items()->create(['product_name' => 'Potasyum Sülfat (0-0-51)', 'brand' => 'Torku', 'quantity' => 60.0, 'unit' => 'kg', 'usage_purpose' => 'Parmak dolgunluğu ve raf ömrü']);
        $tMuzA->items()->create(['product_name' => 'Çinko Sülfat', 'brand' => 'Toros', 'quantity' => 3.0, 'unit' => 'kg', 'usage_purpose' => 'Boğum arası uzama kontrolü']);

        $sprayStraw = SprayingRecipe::create([
            'name' => 'Kırmızı Örümcek ve Külleme Önleyici Koruma Reçetesi',
            'creation_date' => now()->subDays(45)->toDateString(),
            'usage_time' => 'Akşam serinliğinde rüzgarsız saatte',
            'usage_purpose' => 'Zararlı Biyolojik Koruma',
            'water_volume_liters' => 600,
            'application_method' => 'sprayer_machine',
            'is_active' => true,
        ]);
        $sprayStraw->items()->create(['product_name' => 'Abamectin 18g/l (Kırmızı Örümcek)', 'brand' => 'Hektaş', 'quantity' => 250.0, 'unit' => 'ml', 'notes' => 'Yaprak altı ilaçlaması']);
        $sprayStraw->items()->create(['product_name' => 'Potasyum Fosfit (Foliar Külleme)', 'brand' => 'Doktor Tarsa', 'quantity' => 1200.0, 'unit' => 'ml', 'notes' => 'Kök boğazı ve külleme']);

        $wsSilifke1 = WaterSource::create([
            'production_location_id' => $loc1->id,
            'name' => 'Silifke 1 No\'lu Derin Kuyu Sondaj Pompası',
            'active_cycle_minutes' => 35,
            'passive_cycle_minutes' => 180,
            'requires_photo_verification' => true,
        ]);

        $wsSilifke2 = WaterSource::create([
            'production_location_id' => $loc1->id,
            'name' => 'Silifke Drenaj & Geri Kazanım Havuzu',
            'active_cycle_minutes' => 45,
            'passive_cycle_minutes' => 240,
            'requires_photo_verification' => false,
        ]);

        $wsAnamur = WaterSource::create([
            'production_location_id' => $loc2->id,
            'name' => 'Anamur Kuyu Suyu İstasyonu',
            'active_cycle_minutes' => 40,
            'passive_cycle_minutes' => 200,
            'requires_photo_verification' => true,
        ]);

        $fltDisk1 = Filter::create([
            'production_location_id' => $loc1->id,
            'name' => 'Otomatik Ters Yıkamalı Disk Filtre Grubu A-1',
            'cleaning_cycle_days' => 2,
            'requires_photo_verification' => true,
        ]);
        $fltDisk1->waterSources()->attach([$wsSilifke1->id, $wsSilifke2->id]);

        $fltSand = Filter::create([
            'production_location_id' => $loc2->id,
            'name' => 'Anamur Kum-Çakıl Medya Filtre Tankı',
            'cleaning_cycle_days' => 4,
            'requires_photo_verification' => true,
        ]);
        $fltSand->waterSources()->attach([$wsAnamur->id]);

        $strawberryCrewLeaders = [$clHasan, $clIbrahim];
        $bananaCrewLeaders = [$clMehmet, $clRamazan];
        $tradingPartiesList = [$tpMigros, $tpCarrefour, $tpBim, $tpHalKadıkoy];

        $drivers = ['Mehmet Taşçı', 'Kadir Güler', 'Ali Rıza Can', 'Süleyman Demir'];
        $plates = ['33 SAS 101', '07 TAR 88', '33 AKD 450', '33 EFE 92'];
        $waybillCounter = 101;

        for ($day = 30; $day >= 0; $day--) {
            $currentDate = Carbon::now()->subDays($day)->toDateString();

            $basePriceClk = 85.0 + (sin($day / 4) * 8.0) + rand(-2, 2);
            $basePriceMuz = 58.0 + (cos($day / 5) * 5.0) + rand(-1, 2);
            $basePriceAvk = 135.0 + rand(-4, 4);

            MarketPrice::create(['product_id' => $prodStrawberry->id, 'price_date' => $currentDate, 'source_name' => 'Antalya Toptancı Hal Borsası', 'unit_price' => round($basePriceClk, 2)]);
            MarketPrice::create(['product_id' => $prodBanana->id, 'price_date' => $currentDate, 'source_name' => 'Anamur Hal Komisyoncuları', 'unit_price' => round($basePriceMuz, 2)]);
            MarketPrice::create(['product_id' => $prodAvocado->id, 'price_date' => $currentDate, 'source_name' => 'Alanya Avokado Üreticileri Birliği', 'unit_price' => round($basePriceAvk, 2)]);

            $operationsToday = [];
            if ($day % 2 === 0 || $day === 0) {
                $clStraw = $strawberryCrewLeaders[($day / 2) % 2];
                $operationsToday[] = [
                    'location' => $loc1,
                    'product' => $prodStrawberry,
                    'subtype' => $stStrawExport,
                    'packaging' => $pkgStrawWood5,
                    'crop_type' => 'strawberry',
                    'crew_leader' => $clStraw,
                    'worker_count' => 6,
                    'car_count' => 2,
                    'kg' => rand(1600, 2400),
                    'price' => round($basePriceClk, 2),
                ];
            }

            if ($day % 3 === 0 || $day === 1 || $day === 0) {
                $clMuz = $bananaCrewLeaders[($day) % 2];
                $operationsToday[] = [
                    'location' => $loc2,
                    'product' => $prodBanana,
                    'subtype' => $stBananaLux,
                    'packaging' => $pkgBananaBox18,
                    'crop_type' => 'banana',
                    'crew_leader' => $clMuz,
                    'worker_count' => 6,
                    'car_count' => 2,
                    'kg' => rand(2200, 3200),
                    'price' => round($basePriceMuz, 2),
                ];
            }

            foreach ($operationsToday as $opIdx => $op) {
                $cl = $op['crew_leader'];
                $workerCnt = $op['worker_count'];
                $carCnt = $op['car_count'];
                $dailyWageRate = floatval($cl->daily_wage);
                $multiplier = floatval($cl->multiplier);
                $travelFee = floatval($cl->travel_fee_per_car);
                $mealFee = $cl->is_food_included ? 0 : ($workerCnt * 140.0);
                $wageTotal = ($workerCnt * $dailyWageRate) + ($dailyWageRate * $multiplier) + ($carCnt * $travelFee) + $mealFee;

                $kgHarvest = $op['kg'];
                $priceHarvest = $op['price'];
                $revHarvest = $kgHarvest * $priceHarvest;

                $sheet = DailyWorkSheet::create([
                    'work_date' => $currentDate,
                    'company_id' => $company1->id,
                    'production_location_id' => $op['location']->id,
                    'has_external_workers' => true,
                    'has_internal_workers' => true,
                    'created_by_id' => $persMustafa->id,
                    'status' => 'approved',
                    'approved_by_user_id' => $adminUser->id,
                    'approved_at' => Carbon::now()->subDays($day)->setTime(18, 30),
                    'notes' => "$currentDate tarihli hasat ve puantaj kaydı. Ekip şefi: {$cl->first_name} {$cl->last_name}.",
                ]);

                $sheet->crewLeaders()->create([
                    'crew_leader_id' => $cl->id,
                    'worker_count' => $workerCnt,
                    'car_count' => $carCnt,
                    'second_driver_fee_type' => 'leader_rate',
                    'overtime_hours' => 0,
                    'overtime_end_time' => null,
                    'extra_wage_per_worker' => 0,
                    'travel_fee' => $travelFee,
                    'meal_fee' => $mealFee,
                    'calculated_wage_total' => $wageTotal,
                    'dia_cari_code' => $cl->dia_cari_code,
                ]);

                $sheet->harvestItems()->create([
                    'product_id' => $op['product']->id,
                    'product_subtype_id' => $op['subtype']->id,
                    'packaging_definition_id' => $op['packaging']->id,
                    'package_count' => intval($kgHarvest / ($op['packaging']->capacity_qty ?: 5)),
                    'quantity' => $kgHarvest,
                    'unit_symbol' => 'kg',
                    'unit_price' => $priceHarvest,
                    'total_revenue' => $revHarvest,
                    'crop_type' => $op['crop_type'],
                    'banana_bunch_count' => ($op['crop_type'] === 'banana') ? intval($kgHarvest / 25) : null,
                    'farm_scale_kg' => $kgHarvest,
                    'merchant_scale_1st_kg' => $kgHarvest - rand(2, 5),
                    'merchant_scale_2nd_kg' => $kgHarvest - rand(4, 8),
                    'is_merchant_weighed' => true,
                    'weighed_by_id' => $persMerve->id,
                ]);

                $tpSel = $tradingPartiesList[($day + $opIdx) % 4];
                $order = CustomerOrder::create([
                    'trading_party_id' => $tpSel->id,
                    'order_date' => $currentDate,
                    'requested_delivery_date' => Carbon::now()->subDays($day)->addDay()->toDateString(),
                    'contact_person' => ($tpSel->id === $tpMigros->id) ? 'Mehmet Bey (Kategori Müdürü)' : 'Sipariş Yetkilisi',
                    'total_amount' => $revHarvest,
                    'status' => ($day === 0) ? 'confirmed' : 'shipped',
                    'notes' => 'Soğuk zincir sevkiyat talimatı.',
                ]);

                CustomerOrderItem::create([
                    'customer_order_id' => $order->id,
                    'product_id' => $op['product']->id,
                    'packaging_definition_id' => $op['packaging']->id,
                    'quantity' => $kgHarvest,
                    'unit_price' => $priceHarvest,
                    'total_price' => $revHarvest,
                ]);

                ShipmentDelivery::create([
                    'customer_order_id' => $order->id,
                    'trading_party_id' => $tpSel->id,
                    'product_id' => $op['product']->id,
                    'packaging_definition_id' => $op['packaging']->id,
                    'delivery_type_id' => $dtColdTruck->id,
                    'shipment_date' => $currentDate,
                    'vehicle_plate' => $plates[($day + $opIdx) % 4],
                    'driver_name' => $drivers[($day + $opIdx) % 4],
                    'driver_phone' => '0532 ' . sprintf('%03d', rand(100, 999)) . ' ' . sprintf('%04d', rand(1000, 9999)),
                    'dia_waybill_code' => 'IRS-2026-' . sprintf('%04d', $waybillCounter++),
                    'quantity' => $kgHarvest,
                    'unit_price' => $priceHarvest,
                    'status' => 'delivered',
                    'notes' => 'Teslim tutanağı imzalı olarak teslim edildi.',
                ]);
            }

            WaterAnalysisLog::create([
                'water_source_id' => $wsSilifke1->id,
                'analysis_date' => $currentDate,
                'ph_level' => round(6.5 + (rand(-3, 4) / 10.0), 2),
                'ec_level' => round(1.75 + (rand(-2, 3) / 10.0), 2),
                'notes' => 'Otomatik istasyon günlük periyodik ölçümü.',
            ]);

            RawWaterControl::create([
                'water_source_id' => $wsSilifke1->id,
                'control_date' => $currentDate,
                'ph_val' => 6.6,
                'ec_val' => 1.8,
                'pump_status' => 'open',
                'is_filter_cleaned' => true,
            ]);

            PurificationControl::create([
                'water_source_id' => $wsSilifke1->id,
                'control_date' => $currentDate,
                'inlet_pressure_bar' => 3.80,
                'outlet_pressure_bar' => 2.70,
                'delta_pressure_bar' => 1.10,
                'max_threshold_bar' => 1.50,
                'has_warning' => ($day === 8 || $day === 22),
                'warning_message' => ($day === 8 || $day === 22) ? 'Filtre fark basıncı yüksek, otomatik ters yıkama devreye alındı.' : null,
            ]);
        }

        $workPlanTasks = [
            ['title' => 'Silifke Tünel A Damlama Boruları ve Debi Testi', 'loc' => $loc1, 'job' => $jobDaily, 'pers' => $persKemal, 'status' => 'completed', 'daysAgo' => 28],
            ['title' => 'Muz Serası Kuru Yaprak Budaması ve Havalandırma', 'loc' => $loc2, 'job' => $jobPruning, 'pers' => $persMustafa, 'status' => 'completed', 'daysAgo' => 24],
            ['title' => 'Çilek Blok B İlaçlama Öncesi PHI Güvenlik Kontrolü', 'loc' => $loc1, 'job' => $jobSpraying, 'pers' => $persSelin, 'status' => 'completed', 'daysAgo' => 18],
            ['title' => 'Anamur Kuyu Pompası #1 Yıllık Elektrik & Basınç Bakımı', 'loc' => $loc2, 'job' => $jobDaily, 'pers' => $persKemal, 'status' => 'completed', 'daysAgo' => 14],
            ['title' => 'Gazipaşa Avokado Fidanı Taban Gübrelemesi ve Çapa', 'loc' => $loc3, 'job' => $jobPlanting, 'pers' => $persMustafa, 'status' => 'completed', 'daysAgo' => 10],
            ['title' => 'Silifke Tünel Blok B Çilek Tepe Budaması', 'loc' => $loc1, 'job' => $jobPruning, 'pers' => $persMustafa, 'status' => 'completed', 'daysAgo' => 5],
            ['title' => 'Haftalık Zincir Market Sevkiyat Paletleri ve Soğuk Oda Kalibrasyonu', 'loc' => $loc1, 'job' => $jobDaily, 'pers' => $persMerve, 'status' => 'in_progress', 'daysAgo' => 1],
            ['title' => 'Aylık Çavuş Hakediş ve Yemek Faturası Mutabakatı', 'loc' => $loc1, 'job' => $jobDaily, 'pers' => $persEmre, 'status' => 'in_progress', 'daysAgo' => 0],
        ];

        foreach ($workPlanTasks as $wp) {
            WorkPlan::create([
                'production_location_id' => $wp['loc']->id,
                'job_type_id' => $wp['job']->id,
                'assigned_personnel_id' => $wp['pers']->id,
                'title' => $wp['title'],
                'plan_date' => now()->subDays($wp['daysAgo'])->toDateString(),
                'due_date' => now()->subDays(max(0, $wp['daysAgo'] - 2))->toDateString(),
                'status' => $wp['status'],
                'description' => $wp['title'] . ' için saha çalışma talimatı.',
                'completion_notes' => ($wp['status'] === 'completed') ? 'İlgili kontroller ve saha uygulamaları başarıyla tamamlandı.' : 'Saha çalışması devam ediyor.',
            ]);
        }
    }
}
