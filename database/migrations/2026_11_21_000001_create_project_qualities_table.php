<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_qualities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('phase_id')->nullable()->constrained('project_phases')->nullOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('project_tasks')->nullOnDelete();
            $table->string('title', 255);
            $table->string('check_type', 50)->default('test'); // test, acceptance_gate, inspection, compliance, fat_sat
            $table->string('standard_code', 100)->nullable(); // ISO 9001, DIN, TSE, Fabrika Şartnamesi
            $table->text('acceptance_criteria')->nullable(); // Kabul kriteri / tolerans / nominal değer
            $table->string('measured_value', 255)->nullable(); // Ölçülen / gerçekleşen değer
            $table->string('status', 30)->default('pending'); // pending, passed, conditional, failed, exempted
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('checked_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('certificate_file', 500)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index(['project_id', 'check_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_qualities');
    }
};
