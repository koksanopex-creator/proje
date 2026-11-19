<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_draft_suggestions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('project_draft_suggestions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            if (!Schema::hasColumn('project_draft_suggestions', 'external_email')) {
                $table->string('external_email', 191)->nullable()->after('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('project_draft_suggestions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('project_draft_suggestions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            if (Schema::hasColumn('project_draft_suggestions', 'external_email')) {
                $table->dropColumn('external_email');
            }
        });
    }
};
