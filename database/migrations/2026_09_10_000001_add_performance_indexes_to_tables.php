<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gunluk_isci_formlari', function (Blueprint $table) {
            $table->index('work_date', 'idx_gif_work_date');
            $table->index(['status', 'work_date'], 'idx_gif_status_work_date');
        });

        Schema::table('gunluk_form_hasat_kalemleri', function (Blueprint $table) {
            $table->index(['daily_work_sheet_id', 'product_id'], 'idx_gfhk_sheet_product');
        });

        Schema::table('gunluk_form_cavuslar', function (Blueprint $table) {
            $table->index(['daily_work_sheet_id', 'crew_leader_id'], 'idx_gfc_sheet_crew');
        });

        Schema::table('musteri_siparisleri', function (Blueprint $table) {
            $table->index('status', 'idx_ms_status');
            $table->index('order_date', 'idx_ms_order_date');
        });

        Schema::table('sevkiyatlar', function (Blueprint $table) {
            $table->index('status', 'idx_sev_status');
            $table->index('shipment_date', 'idx_sev_shipment_date');
        });

        Schema::table('hal_piyasa_fiyatlari', function (Blueprint $table) {
            $table->index(['product_id', 'price_date'], 'idx_hpf_product_price_date');
        });
    }

    public function down(): void
    {
        Schema::table('gunluk_isci_formlari', function (Blueprint $table) {
            $table->dropIndex('idx_gif_work_date');
            $table->dropIndex('idx_gif_status_work_date');
        });

        Schema::table('gunluk_form_hasat_kalemleri', function (Blueprint $table) {
            $table->dropIndex('idx_gfhk_sheet_product');
        });

        Schema::table('gunluk_form_cavuslar', function (Blueprint $table) {
            $table->dropIndex('idx_gfc_sheet_crew');
        });

        Schema::table('musteri_siparisleri', function (Blueprint $table) {
            $table->dropIndex('idx_ms_status');
            $table->dropIndex('idx_ms_order_date');
        });

        Schema::table('sevkiyatlar', function (Blueprint $table) {
            $table->dropIndex('idx_sev_status');
            $table->dropIndex('idx_sev_shipment_date');
        });

        Schema::table('hal_piyasa_fiyatlari', function (Blueprint $table) {
            $table->dropIndex('idx_hpf_product_price_date');
        });
    }
};
