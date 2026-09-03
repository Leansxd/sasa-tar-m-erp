<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('firmalar', function (Blueprint $table) {
            $table->comment('Firmalar (SASA Tarım Üretim A.Ş. vb)');
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('tax_number')->nullable();
            $table->string('dia_company_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('personeller', function (Blueprint $table) {
            $table->comment('Personel Listesi, Görev ve Yetkiler');
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_personnel_id')->nullable()->constrained('personeller')->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->string('role_title')->nullable();
            $table->json('permissions')->nullable();
            $table->json('company_ids')->nullable();
            $table->boolean('can_enter_backdated_data')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('cari_taraflar', function (Blueprint $table) {
            $table->comment('Cari Taraflar (Müşteriler, Hal Carileri, Alıcı & Satıcılar)');
            $table->id();
            $table->string('name');
            $table->enum('type', ['buyer', 'seller', 'both'])->default('buyer');
            $table->string('dia_cari_code')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->decimal('default_transport_fee', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('teslimat_sekilleri', function (Blueprint $table) {
            $table->comment('Ürün Teslimat & Nakliye Şekilleri');
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('transport_by', ['customer', 'company'])->default('customer');
            $table->boolean('is_fee_included')->default(true);
            $table->decimal('extra_fee', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('uretim_yerleri', function (Blueprint $table) {
            $table->comment('Üretim Yerleri ve Seralar');
            $table->id();
            $table->foreignId('company_id')->constrained('firmalar')->cascadeOnDelete();
            $table->string('name');
            $table->enum('location_type', ['greenhouse', 'open_field', 'mixed'])->default('greenhouse');
            $table->string('dia_branch_code')->nullable();
            $table->string('dia_warehouse_code')->nullable();
            $table->decimal('total_area_dekar', 8, 2)->default(0);
            $table->integer('approx_plant_count')->nullable();
            $table->timestamps();
        });

        Schema::create('uretim_bolumleri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_location_id')->constrained('uretim_yerleri')->cascadeOnDelete();
            $table->string('name');
            $table->enum('section_type', ['greenhouse', 'open_field'])->default('greenhouse');
            $table->decimal('area_dekar', 8, 2)->default(0);
            $table->integer('tunnel_count')->default(0);
            $table->integer('table_stand_count')->default(0);
            $table->integer('approx_plant_count')->nullable();
            $table->timestamps();
        });

        Schema::create('uretim_vanalari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_location_id')->constrained('uretim_yerleri')->cascadeOnDelete();
            $table->foreignId('production_section_id')->nullable()->constrained('uretim_bolumleri')->nullOnDelete();
            $table->string('valve_number');
            $table->string('name');
            $table->enum('duty', ['irrigation', 'misting', 'fertigation', 'other'])->default('irrigation');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('urunler', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('product_type', ['produced', 'consumed', 'both'])->default('produced');
            $table->string('dia_stock_code')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('firma_urun', function (Blueprint $table) {
            $table->foreignId('company_id')->constrained('firmalar')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('urunler')->cascadeOnDelete();
            $table->primary(['company_id', 'product_id']);
        });

        Schema::create('urun_alt_tipleri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('urunler')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->timestamps();
        });

        Schema::create('is_tanimlari', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('form_type')->default('general');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('birim_tanimlari', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('symbol');
            $table->enum('unit_category', ['quantity', 'area', 'count'])->default('quantity');
            $table->timestamps();
        });

        Schema::create('is_tanimi_birim', function (Blueprint $table) {
            $table->foreignId('job_type_id')->constrained('is_tanimlari')->cascadeOnDelete();
            $table->foreignId('unit_definition_id')->constrained('birim_tanimlari')->cascadeOnDelete();
            $table->primary(['job_type_id', 'unit_definition_id']);
        });

        Schema::create('urun_birim', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('urunler')->cascadeOnDelete();
            $table->foreignId('unit_definition_id')->constrained('birim_tanimlari')->cascadeOnDelete();
            $table->primary(['product_id', 'unit_definition_id']);
        });

        Schema::create('paketleme_tanimlari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('urunler')->nullOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('dia_stock_code')->nullable();
            $table->decimal('capacity_qty', 10, 2)->nullable();
            $table->foreignId('unit_id')->nullable()->constrained('birim_tanimlari')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('cavuslar', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('identity_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('origin_city')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('id_photo_path')->nullable();
            $table->decimal('daily_wage', 10, 2)->default(0);
            $table->decimal('multiplier', 5, 2)->default(1.00);
            $table->boolean('is_leader_fee_included')->default(true);
            $table->integer('min_car_requirement')->default(0);
            $table->decimal('travel_fee_per_car', 10, 2)->default(0);
            $table->boolean('is_food_included')->default(false);
            $table->string('dia_cari_code')->nullable();
            $table->timestamps();
        });

        Schema::create('isciler', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crew_leader_id')->nullable()->constrained('cavuslar')->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('identity_number')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('id_photo_path')->nullable();
            $table->unsignedTinyInteger('performance_rating')->default(3);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('yemek_tedarikcileri', function (Blueprint $table) {
            $table->id();
            $table->string('company_title');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->decimal('meal_unit_price', 10, 2)->default(0);
            $table->boolean('is_vat_included')->default(true);
            $table->string('dia_cari_code')->nullable();
            $table->timestamps();
        });

        Schema::create('gubre_receteleri', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('creation_date');
            $table->string('duration_condition')->nullable();
            $table->string('creator_name')->nullable();
            $table->decimal('water_ph', 4, 2)->nullable();
            $table->decimal('water_ec', 4, 2)->nullable();
            $table->text('water_notes')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('gubre_tanklari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fertilization_recipe_id')->constrained('gubre_receteleri')->cascadeOnDelete();
            $table->string('tank_name');
            $table->decimal('capacity_liters', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('gubre_tank_icerikleri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fertilization_tank_id')->constrained('gubre_tanklari')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('urunler')->nullOnDelete();
            $table->string('product_name');
            $table->string('brand')->nullable();
            $table->decimal('quantity', 10, 2)->default(0);
            $table->string('unit')->default('gr');
            $table->string('usage_purpose')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('ilac_receteleri', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('creation_date');
            $table->string('usage_time')->nullable();
            $table->string('usage_purpose')->nullable();
            $table->decimal('water_volume_liters', 10, 2)->default(0);
            $table->enum('application_method', ['sprayer_machine', 'fertigation_tank', 'backpack_pump', 'other'])->default('sprayer_machine');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ilac_recete_icerikleri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spraying_recipe_id')->constrained('ilac_receteleri')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('urunler')->nullOnDelete();
            $table->string('product_name');
            $table->string('brand')->nullable();
            $table->decimal('quantity', 10, 2)->default(0);
            $table->string('unit')->default('ml');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('su_kaynaklari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_location_id')->constrained('uretim_yerleri')->cascadeOnDelete();
            $table->string('name');
            $table->integer('active_cycle_minutes')->default(60);
            $table->integer('passive_cycle_minutes')->default(120);
            $table->boolean('requires_photo_verification')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('filtreler', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_location_id')->constrained('uretim_yerleri')->cascadeOnDelete();
            $table->string('name');
            $table->integer('cleaning_cycle_days')->default(2);
            $table->boolean('requires_photo_verification')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('filtre_su_kaynagi', function (Blueprint $table) {
            $table->foreignId('filter_id')->constrained('filtreler')->cascadeOnDelete();
            $table->foreignId('water_source_id')->constrained('su_kaynaklari')->cascadeOnDelete();
            $table->primary(['filter_id', 'water_source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filtre_su_kaynagi');
        Schema::dropIfExists('filtreler');
        Schema::dropIfExists('su_kaynaklari');
        Schema::dropIfExists('ilac_recete_icerikleri');
        Schema::dropIfExists('ilac_receteleri');
        Schema::dropIfExists('gubre_tank_icerikleri');
        Schema::dropIfExists('gubre_tanklari');
        Schema::dropIfExists('gubre_receteleri');
        Schema::dropIfExists('yemek_tedarikcileri');
        Schema::dropIfExists('isciler');
        Schema::dropIfExists('cavuslar');
        Schema::dropIfExists('paketleme_tanimlari');
        Schema::dropIfExists('urun_birim');
        Schema::dropIfExists('is_tanimi_birim');
        Schema::dropIfExists('birim_tanimlari');
        Schema::dropIfExists('is_tanimlari');
        Schema::dropIfExists('urun_alt_tipleri');
        Schema::dropIfExists('firma_urun');
        Schema::dropIfExists('urunler');
        Schema::dropIfExists('uretim_vanalari');
        Schema::dropIfExists('uretim_bolumleri');
        Schema::dropIfExists('uretim_yerleri');
        Schema::dropIfExists('teslimat_sekilleri');
        Schema::dropIfExists('cari_taraflar');
        Schema::dropIfExists('personeller');
        Schema::dropIfExists('firmalar');
    }
};
