<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        // CANLI ORTAM KORUMASI: Canlı ortamda doğrudan Merkezi API SSO giriş kapısına yönlendir
        if (app()->environment('production')) {
            return app(\App\Http\Controllers\Auth\SsoController::class)->redirectToCentral(request());
        }

        $testUsers = User::with('roles')->where('is_active', true)->where('is_external', false)->get();
        return view('auth.login', compact('testUsers'));
    }

    public function showLoginForm()
    {
        return $this->showLogin();
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Pasif / Erişim yetkisi kaldırılmış kullanıcı kontrolü
            if (!$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors([
                    'email' => 'Hesabınızın erişim yetkisi kaldırılmış veya askıya alınmıştır. Lütfen proje yöneticiniz ile iletişime geçiniz.'
                ])->withInput();
            }

            $request->session()->regenerate();

            // Harici Gözlemci İzolasyonu: Sadece kendi projesine yönlendir
            if ($user->isExternal()) {
                $firstProject = $user->stakeholderProjects()->first();
                if ($firstProject) {
                    return redirect()->route('projects.show', $firstProject->id);
                }
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors(['email' => 'Tanımlı bir aktif projeniz bulunmamaktadır.']);
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email' => 'Girdiğiniz bilgiler kayıtlarımızla eşleşmiyor.'])->withInput();
    }

    // Şifremi Unuttum Ekranı
    public function showForgotPassword()
    {
        return view('auth.forgot_password');
    }

    // Şifre Sıfırlama İsteği Gönderme (Aktif Olmayan Harici Kullanıcıları Engeller)
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower(trim($request->email));
        $user = User::where('email', $email)->first();

        // Kullanıcı yoksa veya inaktif/erişimi engellenmişse şifre sıfırlama talebini reddet
        if (!$user || !$user->is_active) {
            return back()->withErrors([
                'email' => 'Bu e-posta adresine ait aktif bir kullanıcı bulunamadı veya hesabın erişim yetkisi askıya alınmıştır.'
            ])->withInput();
        }

        // Aktif kullanıcı için yeni geçici şifre oluştur
        $newTempPassword = 'Ksn#' . strtoupper(Str::random(4)) . '!' . rand(100, 999);
        $user->password = Hash::make($newTempPassword);
        $user->save();

        return back()->with('status', "Şifreniz başarıyla sıfırlandı. Yeni geçici şifreniz: {$newTempPassword}");
    }

    // Hızlı Test / Rol Değiştirme (Simülasyon)
    public function switchUser(int $id)
    {
        // CANLI ORTAM GÜVENLİK BARİYERİ
        if (!app()->environment('local', 'development', 'testing')) {
            abort(403, 'Hızlı rol simülatörü yalnızca yerel geliştirme (local) ortamında kullanılabilir.');
        }

        $user = User::findOrFail($id);
        Auth::login($user);
        session()->regenerate();

        if ($user->isExternal()) {
            $firstProject = $user->stakeholderProjects()->first();
            if ($firstProject) {
                return redirect()->route('projects.show', $firstProject->id)->with('success', "**{$user->name}** (Harici Gözlemci) olarak oturum açıldı.");
            }
        }

        return redirect()->route('dashboard')->with('success', "**{$user->name}** olarak oturum açıldı.");
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}