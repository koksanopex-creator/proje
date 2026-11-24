<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_external_vendors', function (Blueprint $table) {
            $table->dateTime('credentials_sent_at')->nullable()->after('passkey');
            $table->foreignId('credentials_sent_by_user_id')->nullable()->constrained('users')->nullOnDelete()->after('credentials_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('project_external_vendors', function (Blueprint $table) {
            $table->dropForeign(['credentials_sent_by_user_id']);
            $table->dropColumn(['credentials_sent_at', 'credentials_sent_by_user_id']);
        });
    }
};
