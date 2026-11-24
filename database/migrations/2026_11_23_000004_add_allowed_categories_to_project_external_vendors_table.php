<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_external_vendors', function (Blueprint $table) {
            $table->json('allowed_categories')->nullable()->after('service_scope');
        });
    }

    public function down(): void
    {
        Schema::table('project_external_vendors', function (Blueprint $table) {
            $table->dropColumn('allowed_categories');
        });
    }
};
