<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_non_conformances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('quality_id')->nullable()->constrained('project_qualities')->cascadeOnDelete();
            $table->string('ncr_code', 50)->unique(); // Örn: NCR-2026-0001
            $table->string('title', 255);
            $table->string('severity', 30)->default('major'); // critical, major, minor
            $table->string('status', 30)->default('open'); // open, in_progress, resolved, closed
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->text('description')->nullable();
            $table->text('corrective_action')->nullable(); // DÖF (Düzeltici ve Önleyici Faaliyet)
            $table->text('resolution_notes')->nullable();
            $table->string('evidence_file', 500)->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('closed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index(['project_id', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_non_conformances');
    }
};
