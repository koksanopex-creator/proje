<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_categories') && !Schema::hasColumn('project_categories', 'deleted_at')) {
            Schema::table('project_categories', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('project_categories') && Schema::hasColumn('project_categories', 'deleted_at')) {
            Schema::table('project_categories', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
