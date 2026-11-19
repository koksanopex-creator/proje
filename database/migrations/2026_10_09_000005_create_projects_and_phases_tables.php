<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // örn: PRJ-2026-001
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category'); // insaat, ges_enerji, lpg_hatti, makine, arge, sap_bt, preform_kalip, kavramsal
            $table->string('status')->default('draft'); // draft, in_progress, waiting_approval, completed, on_hold, cancelled
            $table->string('priority')->default('medium'); // low, medium, high, critical
            $table->string('location')->nullable(); // Gaziantep Merkez, 2. OSB, Çorlu
            
            $table->unsignedBigInteger('bolum_id')->nullable()->index();
            $table->unsignedBigInteger('manager_id')->nullable()->index(); // Proje Yöneticisi
            $table->unsignedBigInteger('sponsor_id')->nullable()->index(); // Proje Sponsoru

            // Bütçe Özeti
            $table->decimal('planned_budget', 15, 2)->default(0);
            $table->decimal('actual_spent', 15, 2)->default(0);
            $table->string('currency', 5)->default('TRY');

            // Tarih ve İlerleme
            $table->date('start_date')->nullable();
            $table->date('target_end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->unsignedTinyInteger('progress_percentage')->default(0);

            // "Kimde Bekliyor?" Bottleneck & SLA Radarı
            $table->unsignedBigInteger('current_waiting_role_id')->nullable()->index();
            $table->unsignedBigInteger('current_waiting_user_id')->nullable()->index();
            $table->timestamp('waiting_since')->nullable();
            $table->string('waiting_step_name')->nullable();

            $table->boolean('is_template')->default(false); // Şablon proje bayrağı
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('bolum_id')->references('id')->on('bolumler')->onDelete('set null');
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('sponsor_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('project_phases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->string('status')->default('pending'); // pending, in_progress, waiting_ehs, completed
            
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedTinyInteger('progress_percentage')->default(0);

            // İSG Denetçi Kapı Kilidi (Gatekeeper)
            $table->boolean('requires_ehs_approval')->default(false);
            $table->unsignedBigInteger('ehs_approved_by')->nullable()->index();
            $table->timestamp('ehs_approved_at')->nullable();
            $table->string('ehs_permit_document')->nullable(); // Yüklenen İSG izin belgesi
            $table->text('ehs_notes')->nullable();

            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('ehs_approved_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('project_stakeholders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('role_type'); // sponsor, manager, ehs_officer, ehs_auditor, qa_qc, lead_engineer, subcontractor, site_supervisor
            $table->string('raci_role', 1)->default('R'); // R, A, C, I
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['project_id', 'user_id', 'role_type'], 'prj_user_role_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_stakeholders');
        Schema::dropIfExists('project_phases');
        Schema::dropIfExists('projects');
    }
};
