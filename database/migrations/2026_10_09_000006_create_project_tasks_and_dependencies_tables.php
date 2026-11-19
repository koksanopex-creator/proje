<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index();
            $table->unsignedBigInteger('phase_id')->nullable()->index();
            $table->unsignedBigInteger('parent_id')->nullable()->index(); // Alt görevler için
            $table->string('title');
            $table->text('description')->nullable();
            
            $table->string('status')->default('todo'); // todo, in_progress, review, done, blocked
            $table->string('priority')->default('medium'); // low, medium, high, critical
            $table->string('tag')->nullable(); // #Elektrik, #Mekanik, #İnşaat, #İSG, #SAP
            
            $table->unsignedBigInteger('assigned_to')->nullable()->index(); // Sorumlu
            $table->unsignedBigInteger('created_by')->nullable()->index();

            // Tarih ve Efor Takibi
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('estimated_hours', 8, 2)->default(0);
            $table->decimal('spent_hours', 8, 2)->default(0);
            $table->unsignedTinyInteger('progress_percentage')->default(0);

            $table->boolean('is_milestone')->default(false); // Kilometre taşı bayrağı
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('phase_id')->references('id')->on('project_phases')->onDelete('set null');
            $table->foreign('parent_id')->references('id')->on('project_tasks')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('project_task_dependencies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id')->index();
            $table->unsignedBigInteger('depends_on_task_id')->index();
            $table->string('dependency_type', 4)->default('FS'); // FS (Finish-to-Start), SS (Start-to-Start), FF (Finish-to-Finish)
            $table->timestamps();

            $table->foreign('task_id')->references('id')->on('project_tasks')->onDelete('cascade');
            $table->foreign('depends_on_task_id')->references('id')->on('project_tasks')->onDelete('cascade');
        });

        Schema::create('project_timesheets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index();
            $table->unsignedBigInteger('task_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->date('work_date');
            $table->decimal('hours', 6, 2);
            $table->text('description')->nullable();
            $table->boolean('is_timer_entry')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('task_id')->references('id')->on('project_tasks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_timesheets');
        Schema::dropIfExists('project_task_dependencies');
        Schema::dropIfExists('project_tasks');
    }
};
