<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_super_admin')) {
                $table->boolean('is_super_admin')->default(false)->after('is_admin');
            }
        });

        Schema::table('firmalar', function (Blueprint $table) {
            if (!Schema::hasColumn('firmalar', 'owner_user_id')) {
                $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete()->after('dia_company_code');
            }
            if (!Schema::hasColumn('firmalar', 'subscription_plan')) {
                $table->string('subscription_plan')->default('enterprise')->after('owner_user_id');
            }
            if (!Schema::hasColumn('firmalar', 'max_locations')) {
                $table->integer('max_locations')->default(10)->after('subscription_plan');
            }
            if (!Schema::hasColumn('firmalar', 'max_users')) {
                $table->integer('max_users')->default(20)->after('max_locations');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_super_admin')) {
                $table->dropColumn('is_super_admin');
            }
        });

        Schema::table('firmalar', function (Blueprint $table) {
            if (Schema::hasColumn('firmalar', 'owner_user_id')) {
                $table->dropForeign(['owner_user_id']);
                $table->dropColumn(['owner_user_id', 'subscription_plan', 'max_locations', 'max_users']);
            }
        });
    }
};
