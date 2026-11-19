<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 20)->index(); // 'mail', 'bell'
            $table->string('event_key')->nullable()->index(); // örn: 'toplanti_daveti', 'kurul_uyeligi_cikarildi', 'test_mail'
            $table->string('event_title')->nullable(); // örn: 'Toplantı Katılımcı Daveti', 'Kurul Üyeliğinden Çıkarılma'
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_email')->nullable()->index();
            $table->string('subject')->nullable();
            $table->longText('body')->nullable();
            $table->string('action_url', 500)->nullable();
            $table->string('status', 20)->default('sent')->index(); // 'sent', 'failed', 'pending'
            $table->text('error_message')->nullable();
            $table->foreignId('sent_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
