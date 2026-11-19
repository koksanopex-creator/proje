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
        Schema::create('project_approval_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('doa_rule_id')->nullable()->constrained('doa_threshold_rules')->nullOnDelete();
            $table->json('rule_snapshot')->nullable(); // O anki kural adı, limitler, kur, hedef onaycı verisi
            $table->string('target_approver_type')->default('role'); // role, user, committee, team, auto
            $table->foreignId('target_role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('target_committee_id')->nullable()->constrained('committees')->nullOnDelete();
            $table->string('status')->default('pending'); // pending, approved, rejected, bypassed_auto
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->text('decision_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('committee_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_request_id')->constrained('project_approval_requests')->cascadeOnDelete();
            $table->foreignId('committee_id')->constrained('committees')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('vote'); // approve, reject, conditional_approve, abstain
            $table->decimal('weight', 4, 2)->default(1.00);
            $table->text('notes')->nullable();
            $table->timestamp('voted_at')->useCurrent();
            $table->timestamps();

            $table->unique(['approval_request_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('committee_votes');
        Schema::dropIfExists('project_approval_requests');
    }
};
