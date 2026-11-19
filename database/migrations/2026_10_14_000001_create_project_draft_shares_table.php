<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_draft_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('email');
            $table->string('token', 80)->unique();
            $table->string('passkey', 32);
            $table->foreignId('shared_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('shared_by_role_name')->nullable();
            $table->text('note')->nullable();
            $table->enum('status', ['active', 'revoked', 'expired'])->default('active');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->unsignedInteger('access_count')->default(0);
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index(['email', 'token']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_draft_shares');
    }
};
