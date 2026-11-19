<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('role_groups')) {
            Schema::create('role_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('slug', 50)->unique();
                $table->string('icon', 50)->nullable()->default('🏷️');
                $table->string('color', 20)->default('#0284c7');
                $table->text('description')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_system')->default(false);
                $table->timestamps();
            });

            // Başlangıç Rol Grupları Tohumlama (Seed)
            DB::table('role_groups')->insert([
                [
                    'name' => 'Yönetim & Sistem',
                    'slug' => 'yonetim',
                    'icon' => '👑',
                    'color' => '#8b5cf6',
                    'description' => 'Superadmin, Direktör, Admin ve üst düzey sistem yöneticileri.',
                    'sort_order' => 1,
                    'is_system' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Proje & Saha',
                    'slug' => 'proje',
                    'icon' => '🏗️',
                    'color' => '#0284c7',
                    'description' => 'Mühendis, Saha Şefi, Proje Yöneticisi ve teknik ekip.',
                    'sort_order' => 2,
                    'is_system' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Denetim & Kalite',
                    'slug' => 'denetim',
                    'icon' => '🛡️',
                    'color' => '#10b981',
                    'description' => 'İSG, Kalite, Taşeron ve denetim sorumluları.',
                    'sort_order' => 3,
                    'is_system' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Diğer Departmanlar',
                    'slug' => 'diger',
                    'icon' => '🏢',
                    'color' => '#64748b',
                    'description' => 'Diğer destek birimleri ve genel kurumsal roller.',
                    'sort_order' => 4,
                    'is_system' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_groups');
    }
};