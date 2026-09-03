<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('is_planlari', function (Blueprint $table) {
            $table->comment('İş Planlama ve Görev Takip Tablosu');
            $table->id();
            $table->foreignId('production_location_id')->nullable()->constrained('uretim_yerleri')->nullOnDelete();
            $table->foreignId('job_type_id')->nullable()->constrained('is_tanimlari')->nullOnDelete();
            $table->foreignId('assigned_personnel_id')->nullable()->constrained('personeller')->nullOnDelete();
            $table->string('title');
            $table->date('plan_date');
            $table->date('due_date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->text('description')->nullable();
            $table->text('completion_notes')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });

        Schema::create('is_plani_notlari', function (Blueprint $table) {
            $table->comment('İş Planı Personel Yorum ve Süreç Notları');
            $table->id();
            $table->foreignId('work_plan_id')->constrained('is_planlari')->cascadeOnDelete();
            $table->foreignId('personnel_id')->nullable()->constrained('personeller')->nullOnDelete();
            $table->text('comment');
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });

        Schema::create('gubre_uygulamalari', function (Blueprint $table) {
            $table->comment('Başlatılan Gübreleme Reçete Uygulamaları');
            $table->id();
            $table->foreignId('fertilization_recipe_id')->constrained('gubre_receteleri')->cascadeOnDelete();
            $table->date('start_date');
            $table->string('end_condition')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('gubre_tank_loglari', function (Blueprint $table) {
            $table->comment('Hazırlanan Gübre Tankı Logları');
            $table->id();
            $table->foreignId('fertilization_run_id')->constrained('gubre_uygulamalari')->cascadeOnDelete();
            $table->foreignId('fertilization_tank_id')->constrained('gubre_tanklari')->cascadeOnDelete();
            $table->timestamp('prepared_at');
            $table->foreignId('prepared_by_id')->nullable()->constrained('personeller')->nullOnDelete();
            $table->string('tank_name');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sulama_programlari', function (Blueprint $table) {
            $table->comment('Günlük Sulama Programları');
            $table->id();
            $table->date('schedule_date');
            $table->integer('run_number')->default(1);
            $table->string('start_time')->default('09:00');
            $table->boolean('is_fertilized')->default(true);
            $table->foreignId('fertilization_recipe_id')->nullable()->constrained('gubre_receteleri')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sulama_program_vanalari', function (Blueprint $table) {
            $table->comment('Sulama Programındaki Vana Süreleri');
            $table->id();
            $table->foreignId('irrigation_schedule_id')->constrained('sulama_programlari')->cascadeOnDelete();
            $table->foreignId('production_location_id')->constrained('uretim_yerleri')->cascadeOnDelete();
            $table->foreignId('location_valve_id')->constrained('uretim_vanalari')->cascadeOnDelete();
            $table->integer('duration_minutes')->default(5);
            $table->string('tank_step_level')->nullable();
            $table->timestamps();
        });

        Schema::create('ilac_uygulamalari', function (Blueprint $table) {
            $table->comment('Yapılan İlaçlama Uygulamaları');
            $table->id();
            $table->date('application_date');
            $table->foreignId('spraying_recipe_id')->constrained('ilac_receteleri')->cascadeOnDelete();
            $table->string('purpose')->nullable();
            $table->foreignId('applied_by_id')->nullable()->constrained('personeller')->nullOnDelete();
            $table->string('covered_area_description')->nullable();
            $table->boolean('is_tank_finished')->default(true);
            $table->string('batch_code')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('su_analiz_loglari', function (Blueprint $table) {
            $table->comment('Su Analiz Logları (pH/EC)');
            $table->id();
            $table->foreignId('water_source_id')->constrained('su_kaynaklari')->cascadeOnDelete();
            $table->date('analysis_date');
            $table->decimal('ph_level', 4, 2)->nullable();
            $table->decimal('ec_level', 4, 2)->nullable();
            $table->json('chemical_details')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('kaynak_suyu_kontrolleri', function (Blueprint $table) {
            $table->comment('Kaynak Suyu Pompa ve Depo Kontrol Kayıtları');
            $table->id();
            $table->foreignId('water_source_id')->constrained('su_kaynaklari')->cascadeOnDelete();
            $table->date('control_date');
            $table->decimal('ec_val', 4, 2)->nullable();
            $table->decimal('ph_val', 4, 2)->nullable();
            $table->enum('pump_status', ['open', 'closed', 'faulty'])->default('open');
            $table->text('pump_fault_note')->nullable();
            $table->date('active_start_date')->nullable();
            $table->date('passive_start_date')->nullable();
            $table->text('source_switch_reason')->nullable();
            $table->boolean('is_filter_cleaned')->default(false);
            $table->string('filter_cleaned_photo')->nullable();
            $table->enum('water_tank_level', ['full', 'half_plus', 'half_minus', 'empty'])->default('full');
            $table->string('water_tank_photo')->nullable();
            $table->text('water_tank_note')->nullable();
            $table->enum('chlorine_tank_level', ['full', 'half_plus', 'half_minus', 'empty'])->default('full');
            $table->enum('dosing_pump_mode', ['auto', 'manual', 'faulty'])->default('auto');
            $table->string('dosing_pump_manual_val')->nullable();
            $table->text('dosing_pump_fault_note')->nullable();
            $table->timestamps();
        });

        Schema::create('aritma_suyu_kontrolleri', function (Blueprint $table) {
            $table->comment('Arıtma Suyu Cihaz ve Basınç Kontrolleri');
            $table->id();
            $table->foreignId('water_source_id')->constrained('su_kaynaklari')->cascadeOnDelete();
            $table->date('control_date');
            $table->decimal('inlet_pressure_bar', 4, 2);
            $table->decimal('outlet_pressure_bar', 4, 2);
            $table->decimal('delta_pressure_bar', 4, 2);
            $table->decimal('max_threshold_bar', 4, 2)->default(1.50);
            $table->boolean('has_warning')->default(false);
            $table->string('warning_message')->nullable();
            $table->timestamps();
        });

        Schema::create('musteri_siparisleri', function (Blueprint $table) {
            $table->comment('Alınan Müşteri Siparişleri');
            $table->id();
            $table->foreignId('trading_party_id')->constrained('cari_taraflar')->cascadeOnDelete();
            $table->date('order_date');
            $table->date('requested_delivery_date')->nullable();
            $table->string('contact_person')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->enum('status', ['pending', 'confirmed', 'shipped', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('musteri_siparis_kalemleri', function (Blueprint $table) {
            $table->comment('Müşteri Sipariş Detay Kalemleri');
            $table->id();
            $table->foreignId('customer_order_id')->constrained('musteri_siparisleri')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('urunler')->cascadeOnDelete();
            $table->foreignId('packaging_definition_id')->nullable()->constrained('paketleme_tanimlari')->nullOnDelete();
            $table->decimal('quantity', 10, 2)->default(0);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('sevkiyatlar', function (Blueprint $table) {
            $table->comment('Sevkiyat ve İrsaliye Kayıtları');
            $table->id();
            $table->foreignId('customer_order_id')->nullable()->constrained('musteri_siparisleri')->nullOnDelete();
            $table->foreignId('trading_party_id')->constrained('cari_taraflar')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('urunler')->cascadeOnDelete();
            $table->foreignId('packaging_definition_id')->nullable()->constrained('paketleme_tanimlari')->nullOnDelete();
            $table->date('shipment_date');
            $table->decimal('quantity', 10, 2)->default(0);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->foreignId('delivery_type_id')->nullable()->constrained('teslimat_sekilleri')->nullOnDelete();
            $table->string('vehicle_plate')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->string('dia_waybill_code')->nullable();
            $table->enum('status', ['on_the_way', 'delivered'])->default('on_the_way');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sevkiyatlar');
        Schema::dropIfExists('musteri_siparis_kalemleri');
        Schema::dropIfExists('musteri_siparisleri');
        Schema::dropIfExists('aritma_suyu_kontrolleri');
        Schema::dropIfExists('kaynak_suyu_kontrolleri');
        Schema::dropIfExists('su_analiz_loglari');
        Schema::dropIfExists('ilac_uygulamalari');
        Schema::dropIfExists('sulama_program_vanalari');
        Schema::dropIfExists('sulama_programlari');
        Schema::dropIfExists('gubre_tank_loglari');
        Schema::dropIfExists('gubre_uygulamalari');
        Schema::dropIfExists('is_plani_notlari');
        Schema::dropIfExists('is_planlari');
    }
};
