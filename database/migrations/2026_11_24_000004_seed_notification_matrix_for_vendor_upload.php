<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $eventKey = 'dis_paydas_dosya_yukledi';

        // Varsayılan olarak aktif olması gereken roller
        $rolesToActivate = [
            'Proje Yöneticisi',
            'Proje Sponsoru',
            'Kullanıcı / Mühendis',
            'Teknik Lider / Baş Mühendis',
            'Direktör',
            'Bölüm Lideri',
            'Kalite Denetleyicisi',
            'Superadmin (Developer)',
        ];

        foreach ($rolesToActivate as $roleName) {
            $role = Role::where('name', 'like', "%{$roleName}%")->first();
            if ($role) {
                DB::table('notification_matrix')->updateOrInsert(
                    [
                        'event_key' => $eventKey,
                        'role_id' => $role->id,
                    ],
                    [
                        'is_bell_active' => true,
                        'is_mail_active' => true,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('notification_matrix')->where('event_key', 'dis_paydas_dosya_yukledi')->delete();
    }
};
