<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bolumler', function (Blueprint $table) {
            $table->id();
            $table->string('ad');
            $table->string('kod')->nullable();
            $table->unsignedBigInteger('director_id')->nullable()->index();
            $table->unsignedBigInteger('lider_id')->nullable()->index();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bolumler');
    }
};
