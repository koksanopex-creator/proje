<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_budget_categories', function (Blueprint $table) {
            $table->dropUnique('project_budget_categories_name_unique');
            $table->unsignedBigInteger('parent_id')->nullable()->after('id')->index();
            $table->foreign('parent_id')->references('id')->on('project_budget_categories')->onDelete('cascade');
        });

        Schema::table('project_budget_items', function (Blueprint $table) {
            $table->string('sub_category', 150)->nullable()->after('category');
            $table->unsignedBigInteger('category_id')->nullable()->after('sub_category')->index();
            $table->unsignedBigInteger('sub_category_id')->nullable()->after('category_id')->index();

            $table->foreign('category_id')->references('id')->on('project_budget_categories')->nullOnDelete();
            $table->foreign('sub_category_id')->references('id')->on('project_budget_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('project_budget_items', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['sub_category_id']);
            $table->dropColumn(['sub_category', 'category_id', 'sub_category_id']);
        });

        Schema::table('project_budget_categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
            $table->unique('name');
        });
    }
};
