<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('sicil_no')->nullable()->after('email');
            $table->unsignedBigInteger('bolum_id')->nullable()->index()->after('sicil_no');
            $table->string('unvan')->nullable()->after('bolum_id');
            $table->string('phone')->nullable()->after('unvan');
            $table->string('avatar')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('avatar');
            $table->softDeletes()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['sicil_no', 'bolum_id', 'unvan', 'phone', 'avatar', 'is_active', 'deleted_at']);
        });
    }
};
