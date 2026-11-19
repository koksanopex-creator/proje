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
        Schema::create('committees', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('voting_type')->default('majority'); // unanimous, majority, single_any, quorum_percentage
            $table->unsignedInteger('quorum_percentage')->default(51);
            $table->string('color')->default('#6366f1');
            $table->string('icon')->default('ti ti-users-group');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('committee_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('committee_id')->constrained('committees')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('member_role')->default('uye'); // baskan, uye, raportor, yedek
            $table->decimal('voting_weight', 4, 2)->default(1.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['committee_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('committee_members');
        Schema::dropIfExists('committees');
    }
};
