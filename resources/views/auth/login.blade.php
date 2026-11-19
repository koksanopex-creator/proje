<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - Köksan Enterprise PPM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/koksan_pms.css') }}">
    <style>
        body { display: flex; align-items: center; justify-content: center; background: #0b1329; }
        .login-box { width: 440px; background: #fff; border-radius: var(--radius-lg); padding: 36px; box-shadow: var(--shadow-lg); }
        .login-header { text-align: center; margin-bottom: 28px; }
        .login-brand { font-size: 24px; font-weight: 800; color: var(--navy); }
        .login-sub { font-size: 13px; color: var(--text-muted); margin-top: 4px; }
        .test-users-section { margin-top: 24px; padding-top: 20px; border-top: 1px dashed var(--border-color); }
        .test-user-pill { display: inline-block; padding: 6px 10px; background: #f1f5f9; border-radius: 6px; font-size: 11px; font-weight: 600; color: #334155; text-decoration: none; margin: 3px; border: 1px solid #cbd5e1; transition: 0.15s; }
        .test-user-pill:hover { background: var(--primary); color: #fff; }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="login-header">
            <div style="font-size: 40px; margin-bottom: 8px;">🏭</div>
            <div class="login-brand">KÖKSAN PPM</div>
            <div class="login-sub">Endüstriyel Proje ve Portföy Yönetim Portalı</div>
        </div>

        @if(session('error'))
            <div class="app-alert alert-danger" style="margin: 0 0 16px 0;">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="app-alert alert-success" style="margin: 0 0 16px 0;">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="app-alert alert-danger" style="margin: 0 0 16px 0;">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- MERKEZİ SSO GİRİŞ BUTONU -->
        <div style="margin-bottom: 20px;">
            <a href="{{ route('sso.redirect') }}" class="btn" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; background: #0284c7; color: #fff; font-weight: 700; padding: 12px; border-radius: var(--radius-md); text-decoration: none; box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25); transition: 0.15s;">
                <span>🏢</span> Köksan Merkezi Giriş (SSO)
            </a>
            <div style="display: flex; align-items: center; margin: 18px 0 14px 0; color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                <span style="flex: 1; height: 1px; background: var(--border-color);"></span>
                <span style="padding: 0 10px; font-weight: 600;">veya yerel şifre</span>
                <span style="flex: 1; height: 1px; background: var(--border-color);"></span>
            </div>
        </div>

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">E-Posta Adresi</label>
                <input type="email" name="email" class="form-control" value="celal.karaman@koksan.com" required>
            </div>
            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;"><label class="form-label mb-0">Şifre</label><a href="{{ route('password.request') }}" style="font-size: 11.5px; color: #0284c7; text-decoration: none; font-weight: 600;">Şifremi Unuttum?</a></div>
                <input type="password" name="password" class="form-control" value="password123" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 11px;">
                Giriş Yap
            </button>
        </form>

        @if(app()->environment('local', 'development', 'testing'))
        <!-- HIZLI TEST / ROL SEÇİMİ (DEVELOPER & DEMO - CANLIDA GİZLENİR) -->
        <div class="test-users-section">
            <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">
                ⚡ HIZLI TEST HESAPLARI (TEK TIKLA GİRİŞ):
            </div>
            @foreach($testUsers as $u)
                <a href="{{ route('switch_user', $u->id) }}" class="test-user-pill">
                    {{ $u->name }} ({{ $u->roles->first()?->name ?? 'Kullanıcı' }})
                </a>
            @endforeach
        </div>
        @endif
    </div>
</body>
</html>