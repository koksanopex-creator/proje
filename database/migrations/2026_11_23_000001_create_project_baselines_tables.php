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
        Schema::create('project_baselines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('version', 20)->default('v1.0');
            $table->string('name')->default('Orijinal Temel Çizgi (Baseline)');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('total_planned_budget', 15, 2)->default(0);
            $table->decimal('total_planned_hours', 10, 2)->default(0);
            $table->date('planned_start_date')->nullable();
            $table->date('planned_end_date')->nullable();
            $table->longText('snapshot_data')->nullable(); // JSON: Tüm WBS, faz ve görev hiyerarşisi
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('project_baseline_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('baseline_id')->constrained('project_baselines')->cascadeOnDelete();
            $table->foreignId('task_id')->constrained('project_tasks')->cascadeOnDelete();
            $table->foreignId('phase_id')->nullable()->constrained('project_phases')->nullOnDelete();
            $table->string('title')->nullable();
            $table->date('baseline_start_date')->nullable();
            $table->date('baseline_due_date')->nullable();
            $table->decimal('baseline_hours', 8, 2)->default(0);
            $table->boolean('is_milestone')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_baseline_tasks');
        Schema::dropIfExists('project_baselines');
    }
};
