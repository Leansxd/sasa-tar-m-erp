<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gunluk_isci_formlari', function (Blueprint $table) {
            $table->foreignId('submitted_by_personnel_id')
                ->nullable()
                ->constrained('personeller')
                ->nullOnDelete()
                ->after('notes');
            $table->text('rejection_note')->nullable()->after('submitted_by_personnel_id');
        });
    }

    public function down(): void
    {
        Schema::table('gunluk_isci_formlari', function (Blueprint $table) {
            $table->dropConstrainedForeignId('submitted_by_personnel_id');
            $table->dropColumn('rejection_note');
        });
    }
};
