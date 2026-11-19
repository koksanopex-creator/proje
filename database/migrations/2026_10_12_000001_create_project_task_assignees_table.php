<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('project_task_assignees')) {
            Schema::create('project_task_assignees', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('task_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->timestamps();

                $table->foreign('task_id')->references('id')->on('project_tasks')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->unique(['task_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('project_task_assignees');
    }
};
