<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('event_key')->unique(); // örn: 'proje_olusturuldu', 'isg_onayi_bekleniyor'
            $table->string('event_title');
            $table->string('category')->default('Proje'); // Proje, Görev, İSG, Bütçe, Toplantı
            $table->string('bell_title_template');
            $table->text('bell_body_template');
            $table->string('mail_subject_template');
            $table->text('mail_body_template');
            $table->json('available_variables')->nullable(); // ['proje_adi', 'kullanici', 'rol', 'sure', 'termin']
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('notification_matrix', function (Blueprint $table) {
            $table->id();
            $table->string('event_key')->index();
            $table->unsignedBigInteger('role_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->boolean('is_bell_active')->default(true);
            $table->boolean('is_mail_active')->default(true);
            $table->unsignedBigInteger('template_id')->nullable();
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('template_id')->references('id')->on('notification_templates')->onDelete('set null');
        });

        Schema::create('in_app_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('event_key')->nullable();
            $table->string('title');
            $table->text('message');
            $table->string('action_url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('in_app_notifications');
        Schema::dropIfExists('notification_matrix');
        Schema::dropIfExists('notification_templates');
    }
};
