<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_task_dependencies', function (Blueprint $table) {
            $table->string('dependency_type', 50)->default('FS')->change();
        });
    }

    public function down(): void
    {
        Schema::table('project_task_dependencies', function (Blueprint $table) {
            $table->string('dependency_type', 4)->default('FS')->change();
        });
    }
};
