<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_vendor_files', function (Blueprint $table) {
            $table->string('upload_batch_id', 64)->nullable()->after('notes');
            $table->index('upload_batch_id');
        });
    }

    public function down(): void
    {
        Schema::table('project_vendor_files', function (Blueprint $table) {
            $table->dropIndex(['upload_batch_id']);
            $table->dropColumn('upload_batch_id');
        });
    }
};
