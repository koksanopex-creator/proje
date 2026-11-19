<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_delegations', function (Blueprint $table) {
            $table->string('project_scope_type', 20)->default('all')->after('scope'); // 'all' (Tüm Projeler) veya 'selected' (Belirli Projeler)
            $table->json('project_ids')->nullable()->after('project_scope_type'); // Seçili projelerin ID listesi
            $table->dateTime('cancelled_at')->nullable()->after('is_active'); // Erken iptal tarihi
            $table->string('cancel_reason', 255)->nullable()->after('cancelled_at'); // İptal gerekçesi
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_delegations', function (Blueprint $table) {
            $table->dropColumn(['project_scope_type', 'project_ids', 'cancelled_at', 'cancel_reason']);
        });
    }
};
