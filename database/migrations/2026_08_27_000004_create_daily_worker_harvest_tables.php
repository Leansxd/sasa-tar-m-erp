<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gunluk_isci_formlari', function (Blueprint $table) {
            $table->comment('Günlük İşçi Formu ve Puantaj Kayıtları');
            $table->id();
            $table->date('work_date');
            $table->foreignId('company_id')->constrained('firmalar')->cascadeOnDelete();
            $table->foreignId('production_location_id')->constrained('uretim_yerleri')->cascadeOnDelete();
            $table->boolean('has_external_workers')->default(false);
            $table->boolean('has_internal_workers')->default(true);
            $table->enum('storage_destination', ['cold_storage', 'direct_sale'])->default('direct_sale');
            $table->enum('status', ['submitted', 'approved', 'rejected'])->default('submitted');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('gunluk_form_cavuslar', function (Blueprint $table) {
            $table->comment('Form İçindeki Çavuş Yevmiye ve Hakediş Detayları');
            $table->id();
            $table->foreignId('daily_work_sheet_id')->constrained('gunluk_isci_formlari')->cascadeOnDelete();
            $table->foreignId('crew_leader_id')->constrained('cavuslar')->cascadeOnDelete();
            $table->integer('worker_count')->default(1);
            $table->integer('car_count')->default(1);
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->decimal('extra_wage_per_worker', 10, 2)->default(0);
            $table->decimal('travel_fee', 10, 2)->default(0);
            $table->decimal('meal_fee', 10, 2)->default(0);
            $table->decimal('calculated_wage_total', 12, 2)->default(0);
            $table->string('dia_cari_code')->nullable();
            $table->timestamps();
        });

        Schema::create('gunluk_form_isci_atamalari', function (Blueprint $table) {
            $table->comment('İşçi Görevlendirme ve Çalışma Saatleri');
            $table->id();
            $table->foreignId('daily_work_sheet_id')->constrained('gunluk_isci_formlari')->cascadeOnDelete();
            $table->foreignId('crew_leader_id')->nullable()->constrained('cavuslar')->nullOnDelete();
            $table->foreignId('worker_id')->nullable()->constrained('isciler')->nullOnDelete();
            $table->foreignId('personnel_id')->nullable()->constrained('personeller')->nullOnDelete();
            $table->foreignId('job_type_id')->constrained('is_tanimlari')->cascadeOnDelete();
            $table->string('start_time')->default('08:00');
            $table->string('end_time')->default('17:00');
            $table->integer('break_minutes')->default(60);
            $table->timestamps();
        });

        Schema::create('gunluk_form_hasat_kalemleri', function (Blueprint $table) {
            $table->comment('Günlük Hasat Miktarları ve Hal Kantarı Tartımları');
            $table->id();
            $table->foreignId('daily_work_sheet_id')->constrained('gunluk_isci_formlari')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('urunler')->cascadeOnDelete();
            $table->foreignId('product_subtype_id')->nullable()->constrained('urun_alt_tipleri')->nullOnDelete();
            $table->foreignId('packaging_definition_id')->nullable()->constrained('paketleme_tanimlari')->nullOnDelete();
            $table->integer('package_count')->default(0);
            $table->decimal('quantity', 10, 2)->default(0);
            $table->string('unit_symbol')->default('kg');
            $table->decimal('gross_weight_kg', 10, 2)->default(0);
            $table->decimal('tare_weight_kg', 10, 2)->default(0);
            $table->decimal('net_weight_kg', 10, 2)->default(0);
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->decimal('total_revenue', 12, 2)->nullable();
            $table->enum('crop_type', ['strawberry', 'banana', 'other'])->default('strawberry');
            $table->integer('banana_bunch_count')->nullable();
            $table->decimal('farm_scale_kg', 10, 2)->nullable();
            $table->decimal('merchant_scale_1st_kg', 10, 2)->nullable();
            $table->decimal('merchant_scale_2nd_kg', 10, 2)->nullable();
            $table->boolean('is_merchant_weighed')->default(false);
            $table->foreignId('weighed_by_id')->nullable()->constrained('personeller')->nullOnDelete();
            $table->foreignId('buyer_party_id')->nullable()->constrained('cari_taraflar')->nullOnDelete();
            $table->string('first_weighing_photo')->nullable();
            $table->string('second_weighing_photo')->nullable();
            $table->string('dia_voucher_code')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gunluk_form_hasat_kalemleri');
        Schema::dropIfExists('gunluk_form_isci_atamalari');
        Schema::dropIfExists('gunluk_form_cavuslar');
        Schema::dropIfExists('gunluk_isci_formlari');
    }
};
