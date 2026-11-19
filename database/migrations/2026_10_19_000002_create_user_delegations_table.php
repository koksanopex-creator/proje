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
        Schema::create('user_delegations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delegator_id')->index(); // Asıl yetkili kullanıcı
            $table->unsignedBigInteger('delegatee_id')->index(); // Vekil tayin edilen kullanıcı
            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at')->index();
            $table->json('scope')->nullable(); // ['approvals', 'tasks', 'meetings', 'all']
            $table->string('notes', 255)->nullable(); // Yıllık izin, yurt dışı vb.
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('delegator_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('delegatee_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_delegations');
    }
};
