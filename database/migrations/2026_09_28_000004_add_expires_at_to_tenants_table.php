<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenants') && !Schema::hasColumn('tenants', 'expires_at')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->timestamp('expires_at')->nullable()->after('is_active');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tenants') && Schema::hasColumn('tenants', 'expires_at')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropColumn('expires_at');
            });
        }
    }
};
