<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\Bolum;
use App\Models\User;
use App\Models\Role;
use App\Services\AuditLogService;

Artisan::command('koksan:sync-iaa', function () {
    $this->info("=== IAA SENKRONİZASYONU BAŞLATILIYOR ===");

    // 1. Bölümleri Senkronize Et
    try {
        $iaaDepts = DB::connection('mysql')->select("SELECT * FROM iaa_db.bolumler WHERE deleted_at IS NULL ORDER BY id ASC");
        $this->info("iaa_db içinde " . count($iaaDepts) . " aktif bölüm bulundu.");

        $deptSynced = 0;
        foreach ($iaaDepts as $d) {
            Bolum::updateOrCreate(
                ['id' => $d->id],
                [
                    'ad' => $d->ad,
                    'director_id' => $d->director_id,
                    'is_active' => (bool)$d->is_active,
                ]
            );
            $deptSynced++;
        }
        $this->info("✓ {$deptSynced} Bölüm başarıyla senkronize edildi.");
    } catch (\Exception $e) {
        $this->error("Bölüm senkronizasyon hatası: " . $e->getMessage());
        return 1;
    }

    // 2. Kullanıcıları Senkronize Et
    try {
        $iaaUsers = DB::connection('mysql')->select("SELECT * FROM iaa_db.users WHERE deleted_at IS NULL AND is_personnel = 1 ORDER BY id ASC");
        $this->info("iaa_db içinde " . count($iaaUsers) . " aktif personel bulundu (müşteriler hariç tutuldu).");

        $roles = Role::all()->keyBy('slug');
        $userSynced = 0;

        foreach ($iaaUsers as $u) {
            $user = User::updateOrCreate(
                ['email' => $u->email],
                [
                    'name' => $u->name,
                    'sicil_no' => $u->sicil_no,
                    'bolum_id' => $u->bolum_id,
                    'unvan' => $u->unvan ?? 'Uzman',
                    'phone' => $u->telefon ?? null,
                    'is_active' => (bool)($u->is_personnel ?? true),
                    'password' => $u->password,
                ]
            );

            // Rolleri Ata
            $assignedRoles = [];
            if ($user->id === 1 || str_contains(strtolower($user->email), 'celal.karaman')) {
                $assignedRoles[] = $roles['superadmin']?->id;
            } elseif (str_contains(strtolower($u->unvan ?? ''), 'direktör')) {
                $assignedRoles[] = $roles['direktor']?->id;
                $assignedRoles[] = $roles['sponsor']?->id;
            } elseif (str_contains(strtolower($u->unvan ?? ''), 'lider')) {
                $assignedRoles[] = $roles['bolum_lideri']?->id;
            } elseif (str_contains(strtolower($u->unvan ?? ''), 'kalite')) {
                $assignedRoles[] = $roles['kalite_denetleyici']?->id;
            } elseif (str_contains(strtolower($u->unvan ?? ''), 'müdür') || str_contains(strtolower($u->unvan ?? ''), 'yönetici')) {
                $assignedRoles[] = $roles['proje_yoneticisi']?->id;
            } else {
                $assignedRoles[] = $roles['kullanici']?->id;
            }

            $assignedRoles = array_filter($assignedRoles);
            if (!empty($assignedRoles)) {
                $user->roles()->syncWithoutDetaching($assignedRoles);
            }

            $userSynced++;
            $this->line(" -> [{$user->id}] {$user->name} ({$user->email}) - Bölüm: {$user->bolum?->ad}");
        }

        $this->info("✓ {$userSynced} Kullanıcı başarıyla senkronize edildi ve rolleri atandı.");

        AuditLogService::log(
            'created',
            "Merkezi IAA veritabanından **{$deptSynced}** bölüm ve **{$userSynced}** kullanıcı başarıyla senkronize edildi.",
            'User'
        );

    } catch (\Exception $e) {
        $this->error("Kullanıcı senkronizasyon hatası: " . $e->getMessage());
        return 1;
    }

    $this->info("=== SENKRONİZASYON TAMAMLANDI ===");
    return 0;
})->purpose('iaa_db verilerini koksan_pms sistemine senkronize eder');

use Illuminate\Support\Facades\Schedule;

Schedule::command('delegations:check-expired')->everyFifteenMinutes();