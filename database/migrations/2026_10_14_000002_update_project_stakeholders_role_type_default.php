<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_stakeholders', function (Blueprint $table) {
            $table->string('role_type', 50)->default('stakeholder')->change();
            $table->string('raci_role', 20)->default('R')->change();
        });
    }

    public function down(): void
    {
        Schema::table('project_stakeholders', function (Blueprint $table) {
            $table->string('role_type')->change();
            $table->string('raci_role', 1)->default('R')->change();
        });
    }
};
