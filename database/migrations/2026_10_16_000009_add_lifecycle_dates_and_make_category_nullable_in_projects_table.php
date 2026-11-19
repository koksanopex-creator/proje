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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('category')->nullable()->default('genel')->change();
            $table->timestamp('draft_created_at')->nullable()->after('status');
            $table->timestamp('draft_updated_at')->nullable()->after('draft_created_at');
            $table->json('draft_history')->nullable()->after('draft_updated_at');
            $table->timestamp('submitted_for_approval_at')->nullable()->after('draft_history');
            $table->timestamp('approved_at')->nullable()->after('submitted_for_approval_at');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'draft_created_at',
                'draft_updated_at',
                'draft_history',
                'submitted_for_approval_at',
                'approved_at',
                'rejected_at',
            ]);
        });
    }
};
