<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifre Sıfırlama - Köksan Enterprise PPM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/koksan_pms.css') }}">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: #0b1329; margin: 0; font-family: 'Inter', sans-serif; }
        .login-box { width: 440px; background: #fff; border-radius: var(--radius-lg); padding: 36px; box-shadow: var(--shadow-lg); }
        .login-header { text-align: center; margin-bottom: 24px; }
        .login-brand { font-size: 22px; font-weight: 800; color: var(--navy); }
        .login-sub { font-size: 13px; color: var(--text-muted); margin-top: 4px; }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="login-header">
            <div style="font-size: 38px; margin-bottom: 8px;">🔐</div>
            <div class="login-brand">Şifre Sıfırlama</div>
            <div class="login-sub">Kayıtlı e-posta adresinizi girerek yeni şifrenizi alabilirsiniz.</div>
        </div>

        @if(session('status'))
            <div class="app-alert alert-success" style="margin-bottom: 16px;">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="app-alert alert-danger" style="margin-bottom: 16px;">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="pastel-blue-helper-box mb-3">
            <span class="helper-box-icon">ℹ️</span>
            <div class="helper-box-content">
                <strong>Erişim Kuralı:</strong> Sadece aktif proje paydaşları ve yetkili gözlemciler şifre sıfırlama işlemi yapabilir. Projeden çıkarılan harici hesapların şifre sıfırlama hakkı engellenir.
            </div>
        </div>

        <form action="{{ route('password.email') }}" method="POST">
            @csrf
            <div class="form-group mb-3">
                <label class="form-label font-weight-600">E-Posta Adresi</label>
                <input type="email" name="email" class="form-control" placeholder="ornek@sirket.com" value="{{ old('email') }}" required autofocus>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 11px; font-weight: 600;">
                Yeni Şifre Gönder / Sıfırla
            </button>

            <div style="text-align: center; margin-top: 18px;">
                <a href="{{ route('login') }}" style="color: #0284c7; font-size: 13px; font-weight: 600; text-decoration: none;">
                    ← Giriş Ekranına Dön
                </a>
            </div>
        </form>
    </div>
</body>
</html>