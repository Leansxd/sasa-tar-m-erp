<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hal_piyasa_fiyatlari', function (Blueprint $table) {
            $table->comment('Hal Piyasa Fiyatları ve Değişimleri');
            $table->id();
            $table->foreignId('product_id')->constrained('urunler')->cascadeOnDelete();
            $table->date('price_date');
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->string('source_name')->default('Hal Fiyatı');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hal_piyasa_fiyatlari');
    }
};
