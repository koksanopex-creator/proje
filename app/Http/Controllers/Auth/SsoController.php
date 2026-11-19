<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserSyncService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SsoController extends Controller
{
    /**
     * Kullanıcıyı Merkezi API SSO Giriş Kapısına Yönlendirir.
     */
    public function redirectToCentral(Request $request)
    {
        $centralUrl = rtrim(config('services.central_sso.url', env('CENTRAL_SSO_URL', 'http://localhost:8001')), '/');
        $appCode = config('services.central_sso.app_code', env('CENTRAL_SSO_APP_CODE', 'koksan-pms'));

        $browserHost = $request->getHost();
        $targetCentralUrl = preg_replace('/(localhost|127\.0\.0\.1)/', $browserHost, $centralUrl, 1);

        $intended = $request->query('intended') ?: session('url.intended');
        $redirectParams = '?app_code=' . urlencode($appCode);
        if ($intended) {
            $redirectParams .= '&intended=' . urlencode($intended);
        }

        return redirect($targetCentralUrl . '/sso-entry' . $redirectParams);
    }

    /**
     * Merkezi API'den token ile dönüşü karşılar ve oturum açar.
     */
    public function login(Request $request)
    {
        $token = $request->query('token');
        $centralUrl = rtrim(config('services.central_sso.url', env('CENTRAL_SSO_URL', 'http://localhost:8001')), '/');
        
        // Windows curl localhost IPv6 gecikmesini önlemek için internal çağrıda 127.0.0.1 kullanıyoruz
        $internalHttpUrl = str_replace('localhost', '127.0.0.1', $centralUrl);
        $appCode = config('services.central_sso.app_code', env('CENTRAL_SSO_APP_CODE', 'koksan-pms'));

        if (!$token) {
            return $this->redirectToCentral($request);
        }

        // Merkezi API'ye token'ı doğrulat
        try {
            $response = Http::withoutVerifying()->timeout(6)->get($internalHttpUrl . '/api/auth/verify-sso-token', [
                'token' => $token,
            ]);
        } catch (\Throwable $e) {
            Log::error('Merkezi SSO Token Doğrulama İstek Hatası: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Merkezi oturum sunucusuna bağlanılamadı: ' . $e->getMessage());
        }

        if ($response->failed()) {
            Log::warning('Merkezi SSO Token Doğrulanamadı: ' . $response->body());
            return redirect()->route('login')->with('error', 'Geçersiz veya süresi dolmuş merkezi oturum bileti.');
        }

        $centralUser = $response->json('user');
        $centralSessionId = $response->json('central_session_id');

        if (!$centralUser || empty($centralUser['email'])) {
            return redirect()->route('login')->with('error', 'Merkezi API üzerinden kullanıcı profili temin edilemedi.');
        }

        session([
            'central_user_id' => $centralUser['id'] ?? null,
            'central_session_id' => $centralSessionId,
            'sso_user_data' => $centralUser,
        ]);

        // Kullanıcıyı Köksan PMS veritabanında ara veya senkronize et
        try {
            $localUser = UserSyncService::syncSingleUser($centralUser);
        } catch (\Throwable $e) {
            Log::error('SSO Kullanıcı Senkronizasyon Hatası: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Kullanıcı hesabı senkronize edilirken bir hata oluştu: ' . $e->getMessage());
        }

        // Pasif / Engelli kontrolü
        if (!$localUser->is_active) {
            return redirect()->route('login')->with('error', 'Hesabınızın Köksan PMS sistemine erişim yetkisi askıya alınmıştır.');
        }

        // Oturumu Aç
        Auth::login($localUser, true);
        $request->session()->regenerate();

        // Hedef URL belirleme
        $targetUrl = $request->query('intended') ?: session()->pull('url.intended', route('dashboard'));

        // Harici Kullanıcı İzolasyonu
        if ($localUser->isExternal()) {
            $firstProject = $localUser->stakeholderProjects()->first();
            if ($firstProject) {
                return redirect()->route('projects.show', $firstProject->id);
            }
            Auth::logout();
            $request->session()->invalidate();
            return redirect()->route('login')->with('error', 'Tanımlı bir aktif projeniz bulunmamaktadır.');
        }

        AuditLogService::log(
            'auth',
            "Merkezi API SSO Girişi Yapıldı: **{$localUser->name}** ({$localUser->email}).",
            'User',
            $localUser->id
        );

        return redirect()->to($targetUrl)->with('success', "Hoş geldiniz, **{$localUser->name}**!");
    }

    /**
     * Merkezi Çıkış (Global SSO Logout)
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            AuditLogService::log(
                'auth',
                "Oturum Kapatıldı: **{$user->name}** ({$user->email}).",
                'User',
                $user->id
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $centralUrl = rtrim(config('services.central_sso.url', env('CENTRAL_SSO_URL', 'http://localhost:8001')), '/');
        $browserHost = $request->getHost();
        $targetCentralUrl = preg_replace('/(localhost|127\.0\.0\.1)/', $browserHost, $centralUrl, 1);

        $returnUrl = route('login');

        // Canlıda veya SSO aktifken doğrudan merkezi global logout'a git
        return redirect($targetCentralUrl . '/global-logout?redirect=' . urlencode($returnUrl));
    }
}
