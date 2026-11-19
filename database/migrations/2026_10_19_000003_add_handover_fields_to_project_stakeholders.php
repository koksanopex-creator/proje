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
        Schema::table('project_stakeholders', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('notes')->index();
            $table->dateTime('left_at')->nullable()->after('is_active');
            $table->string('left_reason', 255)->nullable()->after('left_at');
            $table->unsignedBigInteger('handover_to_user_id')->nullable()->after('left_reason');

            $table->foreign('handover_to_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_stakeholders', function (Blueprint $table) {
            $table->dropForeign(['handover_to_user_id']);
            $table->dropColumn(['is_active', 'left_at', 'left_reason', 'handover_to_user_id']);
        });
    }
};
