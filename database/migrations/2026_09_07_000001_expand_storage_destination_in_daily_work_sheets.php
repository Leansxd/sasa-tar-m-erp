<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gunluk_isci_formlari', function (Blueprint $table) {
            $table->string('storage_destination', 50)->default('direct_sale')->change();
        });
    }

    public function down(): void
    {
        Schema::table('gunluk_isci_formlari', function (Blueprint $table) {
            $table->enum('storage_destination', ['cold_storage', 'direct_sale'])->default('direct_sale')->change();
        });
    }
};
