<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_external_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('company_name');
            $table->string('contact_person');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('service_scope')->nullable(); // Yapılan iş / çalışma konusu
            $table->string('access_token', 64)->unique();
            $table->string('passkey', 32); // KKS-XXXXXX formatında şifre
            $table->enum('status', ['pending_approval', 'approved', 'rejected', 'revoked'])->default('pending_approval');
            
            // Onay / Red / İptal takibi
            $table->foreignId('added_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->foreignId('rejected_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            $table->foreignId('revoked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('revoked_at')->nullable();
            $table->text('revocation_reason')->nullable();

            $table->dateTime('expires_at')->nullable();
            $table->dateTime('last_accessed_at')->nullable();
            $table->unsignedInteger('access_count')->default(0);
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'status']);
            $table->index('access_token');
        });

        Schema::create('project_vendor_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('project_external_vendors')->cascadeOnDelete();
            $table->string('original_name');
            $table->string('storage_path');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('mime_type')->nullable();
            $table->enum('direction', ['inbound_from_vendor', 'outbound_to_vendor'])->default('inbound_from_vendor');
            $table->enum('status', ['pending_review', 'approved', 'rejected'])->default('pending_review');
            
            // Dış firma görünürlüğü (açık / kapalı)
            $table->boolean('is_visible_to_vendor')->default(true);

            // Geçersiz / Dikkate Alma İşaretçisi (Marker / Void Flag)
            $table->boolean('is_void')->default(false);
            $table->text('void_reason')->nullable();
            $table->enum('voided_by_type', ['vendor', 'internal_user'])->nullable();
            $table->unsignedBigInteger('voided_by_id')->nullable();
            $table->dateTime('voided_at')->nullable();

            // Kim yükledi & kim inceledi
            $table->unsignedBigInteger('uploaded_by_vendor_id')->nullable();
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->text('review_note')->nullable();

            $table->string('category')->default('muayene_raporu'); // muayene_raporu, teknik_sartname, cizim vb.
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'vendor_id']);
            $table->index(['vendor_id', 'direction', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_vendor_files');
        Schema::dropIfExists('project_external_vendors');
    }
};
