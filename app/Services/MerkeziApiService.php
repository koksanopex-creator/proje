<?php

namespace App\Services;

use App\Models\User;
use App\Models\Bolum;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class MerkeziApiService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.merkezi_api.url', 'https://kys.koksan.com/merkezi_yonetim_sistemi');
    }

    /**
     * Merkezi API üzerinden kullanıcı kimlik doğrulaması yapar (SSO Login).
     */
    public function authenticate(string $email, string $password): ?array
    {
        try {
            $response = Http::timeout(5)->post("{$this->baseUrl}/api/v1/auth/login", [
                'email' => $email,
                'password' => $password,
            ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::warning("Merkezi API bağlantı hatası: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Kullanıcıyı Merkezi API verisiyle yerel DB'ye eşitler veya günceller.
     */
    public function syncUser(array $apiUserData, ?string $plainPassword = null): User
    {
        $user = User::updateOrCreate(
            ['email' => $apiUserData['email']],
            [
                'name' => $apiUserData['name'] ?? ($apiUserData['first_name'] . ' ' . $apiUserData['last_name']),
                'sicil_no' => $apiUserData['sicil_no'] ?? null,
                'unvan' => $apiUserData['unvan'] ?? null,
                'phone' => $apiUserData['phone'] ?? null,
                'is_active' => $apiUserData['is_active'] ?? true,
                'password' => $plainPassword ? Hash::make($plainPassword) : Hash::make('password123'),
            ]
        );

        return $user;
    }
}