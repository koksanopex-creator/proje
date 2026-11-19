<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_meetings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index();
            $table->string('title');
            $table->dateTime('meeting_date');
            $table->string('location')->nullable(); // Toplantı Odası 1, Saha Şantiyesi, Online vb.
            $table->text('agenda')->nullable(); // Gündem maddeleri
            $table->text('minutes')->nullable(); // Canlı tutanak ve notlar
            $table->string('status')->default('scheduled'); // scheduled, in_progress, completed, cancelled
            $table->unsignedBigInteger('organizer_id')->index(); // Düzenleyen
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('organizer_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('project_meeting_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meeting_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('external_attendee_name')->nullable(); // Dış katılımcı (Taşeron yetkilisi vb.)
            $table->string('external_attendee_email')->nullable();
            $table->string('status')->default('invited'); // invited, attending, declined, present
            $table->timestamps();

            $table->foreign('meeting_id')->references('id')->on('project_meetings')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('project_meeting_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meeting_id')->index();
            $table->unsignedBigInteger('project_id')->index();
            $table->unsignedBigInteger('task_id')->nullable()->index(); // Proje Görevine dönüşmüşse FK
            $table->text('decision_text'); // Alınan Karar
            $table->unsignedBigInteger('assigned_to')->nullable()->index(); // Sorumlu
            $table->date('due_date')->nullable();
            $table->string('status')->default('open'); // open, converted_to_task, completed
            $table->timestamps();

            $table->foreign('meeting_id')->references('id')->on('project_meetings')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('task_id')->references('id')->on('project_tasks')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_meeting_actions');
        Schema::dropIfExists('project_meeting_participants');
        Schema::dropIfExists('project_meetings');
    }
};
