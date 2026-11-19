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
        Schema::create('doa_threshold_rules', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('rule_type')->default('project_approval'); // project_approval, finance_payment, budget_revision
            $table->string('currency_code', 10)->default('USD');
            $table->decimal('min_amount', 15, 2)->default(0.00);
            $table->decimal('max_amount', 15, 2)->nullable(); // null = sınırsız üst limit
            $table->boolean('requires_approval')->default(true); // false = doğrudan devreye alma (fast-track)
            $table->string('approver_type')->default('role'); // role, user, committee, team
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('committee_id')->nullable()->constrained('committees')->nullOnDelete();
            $table->string('role_group_slug')->nullable(); // takımlar/gruplar için slug referansı
            $table->string('project_category_slug')->nullable(); // kategoriye özel kural filtresi
            $table->foreignId('bolum_id')->nullable()->constrained('bolumler')->nullOnDelete(); // departmana özel kural filtresi
            $table->unsignedInteger('priority_order')->default(10);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doa_threshold_rules');
    }
};
