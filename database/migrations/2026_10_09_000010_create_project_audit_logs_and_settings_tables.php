<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->index(); // İşlemi fiilen yapan gerçek ID
            $table->unsignedBigInteger('acting_as_user_id')->nullable()->index(); // Vekaleten/Başkası adına işlem yapılmışsa (Madde 16)
            $table->string('acting_as_role_name')->nullable(); // örn: "Şenol KANAT (Direktör) adına"
            
            $table->string('entity_type'); // Project, Task, Phase, Bom, Risk, Meeting, Permission, Setting
            $table->unsignedBigInteger('entity_id')->nullable();
            
            $table->string('action'); // created, updated, status_changed, approved, rejected, deleted, file_uploaded
            $table->string('description'); // Yapılan eylemin okunabilir özeti
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('acting_as_user_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('log_visibility_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id')->index();
            $table->string('log_category'); // project, task, financial, ehs, system
            $table->boolean('can_view_logs')->default(true);
            $table->boolean('show_full_details')->default(false); // Detaylı JSON değerleri görebilme
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->unique(['role_id', 'log_category']);
        });

        Schema::create('project_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index();
            $table->unsignedBigInteger('task_id')->nullable()->index();
            $table->string('original_name');
            $table->string('storage_path'); // storage/projeler/{user}/{id}/{tarih}_{random}.ext
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');
            $table->string('category')->default('dokuman'); // cizim_cad, sartname, isg_izin, fatura, tutanak, foto
            $table->string('revision', 10)->default('Rev.0');
            $table->unsignedBigInteger('uploaded_by')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('task_id')->references('id')->on('project_tasks')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, text, boolean, image, json
            $table->string('group')->default('general'); // general, branding, mail, security
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('project_files');
        Schema::dropIfExists('log_visibility_settings');
        Schema::dropIfExists('project_audit_logs');
    }
};
