<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gunluk_form_cavuslar', function (Blueprint $table) {
            $table->enum('second_driver_fee_type', ['leader_rate', 'normal_worker', 'no_fee'])->default('leader_rate')->after('car_count');
            $table->string('overtime_end_time')->nullable()->after('overtime_hours');
            $table->integer('ramadan_meal_count')->nullable()->after('meal_fee');
            $table->boolean('is_food_included_override')->nullable()->after('ramadan_meal_count');
        });
    }

    public function down(): void
    {
        Schema::table('gunluk_form_cavuslar', function (Blueprint $table) {
            $table->dropColumn(['second_driver_fee_type', 'overtime_end_time', 'ramadan_meal_count', 'is_food_included_override']);
        });
    }
};
