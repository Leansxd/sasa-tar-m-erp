<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tenants')) {
            Schema::create('tenants', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('owner_name')->nullable();
                $table->string('owner_email')->nullable();
                $table->string('plan')->default('enterprise');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            DB::table('tenants')->insert([
                'id' => 1,
                'name' => 'SASA Tarım ERP (Ana Sistem)',
                'slug' => 'sasa-tarim',
                'owner_name' => 'SASA Admin',
                'owner_email' => 'admin@sasa.com',
                'plan' => 'enterprise',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $tables = [
            'users',
            'firmalar',
            'personeller',
            'uretim_yerleri',
            'urunler',
            'is_tanimlari',
            'birim_tanimlari',
            'paketleme_tanimlari',
            'cavuslar',
            'isciler',
            'yemek_tedarikcileri',
            'gubre_receteleri',
            'ilac_receteleri',
            'su_kaynaklari',
            'filtreler',
            'cari_taraflar',
            'teslimat_sekilleri',
            'gunluk_isci_formlari',
            'musteri_siparisleri',
            'sevkiyat_teslimat',
            'is_planlari',
            'piyasa_fiyatlari',
            'gubre_uygulamalari',
            'sulama_programlari',
            'ilaclama_uygulamalari',
            'su_analizleri',
            'kaynak_suyu_kontrolleri',
            'aritma_suyu_kontrolleri',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedBigInteger('tenant_id')->default(1)->index()->after('id');
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'users',
            'firmalar',
            'personeller',
            'uretim_yerleri',
            'urunler',
            'is_tanimlari',
            'birim_tanimlari',
            'paketleme_tanimlari',
            'cavuslar',
            'isciler',
            'yemek_tedarikcileri',
            'gubre_receteleri',
            'ilac_receteleri',
            'su_kaynaklari',
            'filtreler',
            'cari_taraflar',
            'teslimat_sekilleri',
            'gunluk_isci_formlari',
            'musteri_siparisleri',
            'sevkiyat_teslimat',
            'is_planlari',
            'piyasa_fiyatlari',
            'gubre_uygulamalari',
            'sulama_programlari',
            'ilaclama_uygulamalari',
            'su_analizleri',
            'kaynak_suyu_kontrolleri',
            'aritma_suyu_kontrolleri',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('tenant_id');
                });
            }
        }

        Schema::dropIfExists('tenants');
    }
};
