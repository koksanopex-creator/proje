<?php

namespace App\Services;

use App\Models\User;
use App\Models\Bolum;
use App\Models\Role;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserSyncService
{
    /**
     * Merkezi Sistemden (Veritabanı veya REST API) tüm kullanıcıları senkronize eder.
     *
     * @return array
     */
    public static function syncFromCentral(): array
    {
        $added = 0;
        $updated = 0;
        $roles = Role::all()->keyBy('slug');

        try {
            // 1. ÖNCELİK: Merkezi Veritabanı (merkezi_app veya iaa_db)
            $sourceUsers = self::fetchUsersFromCentralDatabase();

            // Eğer yerel DB'de bulunamadıysa REST API üzerinden çekmeyi dene
            if (empty($sourceUsers)) {
                $sourceUsers = self::fetchUsersFromCentralApi();
            }

            if (empty($sourceUsers)) {
                return [
                    'success' => false,
                    'added' => 0,
                    'updated' => 0,
                    'total' => User::count(),
                    'message' => 'Merkezi API veya merkezi veritabanında senkronize edilecek kullanıcı bulunamadı.',
                ];
            }

            foreach ($sourceUsers as $u) {
                $email = strtolower(trim($u['email'] ?? ''));
                if (empty($email)) {
                    continue;
                }

                $existingUser = User::withTrashed()->where('email', $email)->first();
                $isNew = !$existingUser;

                // Bölüm Eşleştirme / Oluşturma
                $bolumId = null;
                if (!empty($u['department_name'])) {
                    $bolum = Bolum::firstOrCreate(
                        ['ad' => trim($u['department_name'])],
                        ['is_active' => true]
                    );
                    $bolumId = $bolum->id;
                } elseif (!empty($u['bolum_id'])) {
                    $bolumId = $u['bolum_id'];
                }

                $userData = [
                    'central_user_id' => $u['id'] ?? null,
                    'name' => trim($u['name'] ?? ($u['first_name'] . ' ' . $u['last_name'])),
                    'sicil_no' => $u['sicil_no'] ?? $u['registration_no'] ?? null,
                    'unvan' => $u['unvan'] ?? $u['job_title'] ?? 'Uzman',
                    'phone' => $u['phone'] ?? $u['telefon'] ?? null,
                    'bolum_id' => $bolumId,
                    'avatar' => $u['profile_photo'] ?? $u['avatar'] ?? null,
                    'is_active' => isset($u['is_active']) ? (bool)$u['is_active'] : true,
                    'deleted_at' => null, // Soft delete varsa geri getir
                ];

                if ($isNew) {
                    $userData['password'] = !empty($u['password']) ? $u['password'] : Hash::make('password123');
                    $user = User::create(array_merge(['email' => $email], $userData));

                    // Yeni kullanıcı için akıllı varsayılan rol ataması
                    $assignedRoles = [];
                    $lowerEmail = strtolower($email);
                    $lowerUnvan = strtolower($userData['unvan'] ?? '');

                    if ($user->id === 1 || str_contains($lowerEmail, 'celal.karaman')) {
                        $assignedRoles[] = $roles['superadmin']?->id;
                    } elseif (str_contains($lowerUnvan, 'direktör')) {
                        $assignedRoles[] = $roles['direktor']?->id;
                        $assignedRoles[] = $roles['sponsor']?->id;
                    } elseif (str_contains($lowerUnvan, 'lider')) {
                        $assignedRoles[] = $roles['bolum_lideri']?->id;
                    } elseif (str_contains($lowerUnvan, 'kalite')) {
                        $assignedRoles[] = $roles['kalite_denetleyici']?->id;
                    } elseif (str_contains($lowerUnvan, 'müdür') || str_contains($lowerUnvan, 'yönetici')) {
                        $assignedRoles[] = $roles['proje_yoneticisi']?->id;
                    } else {
                        $assignedRoles[] = $roles['kullanici']?->id;
                    }

                    $assignedRoles = array_values(array_filter($assignedRoles));
                    if (!empty($assignedRoles)) {
                        $user->roles()->sync($assignedRoles);
                    }

                    $added++;
                } else {
                    $existingUser->update($userData);
                    // Kullanıcının hiç rolü kalmamışsa varsayılan rolü ata
                    if ($existingUser->roles()->count() === 0 && isset($roles['kullanici'])) {
                        $existingUser->roles()->attach($roles['kullanici']->id);
                    }
                    $updated++;
                }
            }

            $total = User::count();

            AuditLogService::log(
                'sync',
                "Merkezi API Senkronizasyonu: **{$added}** yeni kullanıcı eklendi, **{$updated}** kullanıcı güncellendi (Toplam: {$total}).",
                'User'
            );

            return [
                'success' => true,
                'added' => $added,
                'updated' => $updated,
                'total' => $total,
                'message' => "Merkezi sistemden senkronizasyon tamamlandı: {$added} yeni kullanıcı eklendi, {$updated} kullanıcı güncellendi.",
            ];

        } catch (\Throwable $e) {
            Log::error('UserSyncService Senkronizasyon Hatası: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'added' => $added,
                'updated' => $updated,
                'total' => User::count(),
                'message' => 'Senkronizasyon sırasında hata oluştu: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Merkezi API'den tek bir kullanıcıyı (Webhook veya SSO sırasında) anında eşitler.
     *
     * @param array $payload
     * @return User
     */
    public static function syncSingleUser(array $payload): User
    {
        $email = strtolower(trim($payload['email'] ?? ''));
        if (empty($email)) {
            throw new \InvalidArgumentException('Kullanıcı e-posta adresi boş olamaz.');
        }

        if ((isset($payload['is_customer']) && (int)$payload['is_customer'] === 1) || 
            (isset($payload['is_personnel']) && (int)$payload['is_personnel'] === 0)) {
            throw new \InvalidArgumentException('Müşteri hesapları personel sistemine senkronize edilemez (is_personnel=0).');
        }

        $bolumId = null;
        if (!empty($payload['department_name'])) {
            $bolum = Bolum::firstOrCreate(
                ['ad' => trim($payload['department_name'])],
                ['is_active' => true]
            );
            $bolumId = $bolum->id;
        } elseif (!empty($payload['bolum_id'])) {
            $bolumId = $payload['bolum_id'];
        }

        $userData = [
            'central_user_id' => $payload['id'] ?? $payload['central_user_id'] ?? null,
            'name' => trim($payload['name'] ?? (($payload['first_name'] ?? '') . ' ' . ($payload['last_name'] ?? ''))),
            'sicil_no' => $payload['sicil_no'] ?? $payload['registration_no'] ?? null,
            'unvan' => $payload['unvan'] ?? $payload['job_title'] ?? 'Uzman',
            'phone' => $payload['phone'] ?? $payload['telefon'] ?? null,
            'bolum_id' => $bolumId,
            'avatar' => $payload['profile_photo'] ?? $payload['avatar'] ?? null,
            'is_active' => isset($payload['is_active']) ? (bool)$payload['is_active'] : true,
            'deleted_at' => null,
        ];

        $user = null;
        if (!empty($userData['central_user_id'])) {
            $user = User::withTrashed()->where('central_user_id', $userData['central_user_id'])->first();
        }
        if (!$user) {
            $user = User::withTrashed()->where('email', $email)->first();
        }

        if (!$user) {
            $userData['password'] = !empty($payload['password']) ? $payload['password'] : Hash::make('password123');
            $user = User::create(array_merge(['email' => $email], $userData));

            // Yeni kullanıcı varsayılan rolü
            $defaultRole = Role::where('slug', 'kullanici')->first();
            if ($defaultRole) {
                $user->roles()->attach($defaultRole->id);
            }

            AuditLogService::log(
                'created',
                "Merkezi API Webhook ile Yeni Kullanıcı Eklendi: **{$user->name}** ({$user->email}).",
                'User',
                $user->id
            );
        } else {
            $user->update($userData);
            AuditLogService::log(
                'updated',
                "Merkezi API Webhook ile Kullanıcı Güncellendi: **{$user->name}** ({$user->email}).",
                'User',
                $user->id
            );
        }

        return $user;
    }

    /**
     * Yerel / ağdaki MySQL veritabanından kullanıcıları çeker.
     * Öncelik: merkezi_app.users + departments -> Sonra: iaa_db.users + bolumler
     */
    protected static function fetchUsersFromCentralDatabase(): array
    {
        $users = [];

        // 1. merkezi_auth_db veya merkezi_app kontrolü
        try {
            $centralDb = null;
            $hasDb = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME IN ('merkezi_auth_db', 'merkezi_app') ORDER BY FIELD(SCHEMA_NAME, 'merkezi_auth_db', 'merkezi_app') LIMIT 1");
            if (!empty($hasDb)) {
                $centralDb = $hasDb[0]->SCHEMA_NAME ?? $hasDb[0]->schema_name;
            }

            if ($centralDb) {
                $raw = DB::table("{$centralDb}.users as u")
                    ->leftJoin("{$centralDb}.departments as d", 'u.department_id', '=', 'd.id')
                    ->whereNull('u.deleted_at')
                    ->where('u.is_customer', 0) // Müşterileri çekme, sadece personelleri al
                    ->select([
                        'u.id',
                        'u.first_name',
                        'u.last_name',
                        'u.email',
                        'u.registration_no',
                        'u.job_title',
                        'u.phone',
                        'u.is_active',
                        'u.profile_photo',
                        'u.password',
                        'd.name as department_name',
                    ])
                    ->get();

                foreach ($raw as $item) {
                    $users[] = (array)$item;
                }

                if (!empty($users)) {
                    return $users;
                }
            }
        } catch (\Throwable $e) {
            Log::info("Merkezi auth veritabanından çekilemedi: " . $e->getMessage());
        }

        // 2. iaa_db kontrolü
        try {
            $hasIaaDb = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'iaa_db'");
            if (!empty($hasIaaDb)) {
                $raw = DB::table('iaa_db.users as u')
                    ->leftJoin('iaa_db.bolumler as b', 'u.bolum_id', '=', 'b.id')
                    ->whereNull('u.deleted_at')
                    ->where('u.is_personnel', 1) // Sadece personel olanları al, is_personnel=0 olan müşterileri hariç tut
                    ->select([
                        'u.id',
                        'u.name',
                        'u.email',
                        'u.sicil_no',
                        'u.unvan',
                        'u.telefon as phone',
                        'u.is_personnel as is_active',
                        'u.password',
                        'b.ad as department_name',
                        'u.bolum_id',
                    ])
                    ->get();

                foreach ($raw as $item) {
                    $users[] = (array)$item;
                }
            }
        } catch (\Throwable $e) {
            Log::info("iaa_db veritabanından çekilemedi: " . $e->getMessage());
        }

        return $users;
    }

    /**
     * Merkezi REST API'den kullanıcı listesini çeker.
     */
    protected static function fetchUsersFromCentralApi(): array
    {
        $apiUrl = config('services.merkezi_api.url', 'https://kys.koksan.com/merkezi_yonetim_sistemi');
        $apiKey = config('services.merkezi_api.key', env('MERKEZI_API_KEY', ''));

        try {
            $response = Http::timeout(8)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'X-API-Key' => $apiKey,
                ])
                ->get("{$apiUrl}/api/v1/users");

            if ($response->successful()) {
                $data = $response->json();
                $rawList = $data['data'] ?? $data['users'] ?? $data;
                if (is_array($rawList)) {
                    return array_values(array_filter($rawList, function ($item) {
                        if (isset($item['is_customer']) && (int)$item['is_customer'] === 1) return false;
                        if (isset($item['is_personnel']) && (int)$item['is_personnel'] === 0) return false;
                        return true;
                    }));
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Merkezi REST API bağlantı hatası: " . $e->getMessage());
        }

        return [];
    }
}
