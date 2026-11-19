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
        Schema::create('user_calendar_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->date('note_date');
            $table->string('note_time', 10)->nullable();
            $table->string('color', 30)->default('#f59e0b');
            $table->boolean('send_bell')->default(true);
            $table->boolean('send_email')->default(false);
            $table->string('email_subject', 255)->nullable();
            $table->text('email_body')->nullable();
            $table->boolean('is_sent')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'note_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_calendar_notes');
    }
};
