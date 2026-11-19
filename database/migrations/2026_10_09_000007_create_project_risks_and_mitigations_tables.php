<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_risks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category'); // teknik, isg, finansal_kur, tedarik_lojistik, yasal_ruhsat, cevresel
            
            // 5x5 Likert Olasılık x Etki = Risk Puanı (1-25)
            $table->unsignedTinyInteger('probability')->default(1); // 1-5
            $table->unsignedTinyInteger('impact')->default(1); // 1-5
            $table->unsignedTinyInteger('risk_score')->default(1); // prob * impact
            $table->string('severity', 10)->default('low'); // low (1-6), medium (8-12), high (15-25)
            
            $table->string('status')->default('open'); // open, mitigated, closed, accepted
            $table->unsignedBigInteger('owner_id')->nullable()->index(); // Risk Sahibi
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('project_risk_mitigations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('risk_id')->index();
            $table->text('action_plan');
            $table->string('strategy')->default('reduce'); // avoid, reduce, transfer, accept
            $table->unsignedBigInteger('assigned_to')->nullable()->index();
            $table->date('target_date')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('risk_id')->references('id')->on('project_risks')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_risk_mitigations');
        Schema::dropIfExists('project_risks');
    }
};
