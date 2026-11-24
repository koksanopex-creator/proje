@extends('vendor_portal.layout')

@section('title', 'Güvenlik Doğrulaması - ' . $vendor->company_name)

@section('content')
    @if(!app()->isLocal())
        <script src="https://www.google.com/recaptcha/api.js?hl=tr" async defer></script>
    @endif

    <div style="max-width: 480px; margin: 40px auto;">
        <div class="portal-card" style="box-shadow: 0 20px 40px -15px rgba(0,0,0,0.12); border-radius: 16px;">
            <div
                style="background: linear-gradient(135deg, #091325 0%, #0f172a 100%); padding: 32px 28px; text-align: center; color: #ffffff; border-radius: 16px 16px 0 0;">
                <div
                    style="width: 56px; height: 56px; background: rgba(2, 132, 199, 0.2); border: 2px solid #38bdf8; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 24px;">
                    🔐
                </div>
                <h2 style="font-size: 20px; font-weight: 800; margin: 0 0 6px;">Dış Paydaş Güvenlik Doğrulaması</h2>
                <div style="font-size: 13px; color: #94a3b8;">
                    {{ $vendor->company_name }}
                </div>
            </div>

            <div class="portal-card-body" style="padding: 28px;">
                <p style="font-size: 13.5px; color: #475569; line-height: 1.5; margin-bottom: 20px; text-align: center;">
                    Sayın <strong>{{ $vendor->contact_person }}</strong>,<br>
                    Belge transfer portalına güvenli erişim sağlayabilmek için tarafınıza iletilen <strong>Güvenlik
                        Şifrenizi (Passkey)</strong> giriniz.
                </p>

                @if(app()->isLocal())
                    <div
                        style="background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 12px 14px; margin-bottom: 20px; font-size: 12px; color: #166534; display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 18px;">💡</span>
                        <div>
                            <strong>Geliştirme Modu (Local):</strong> Güvenlik şifresi (<code>{{ $vendor->passkey }}</code>)
                            otomatik dolduruldu, reCAPTCHA devre dışı bırakıldı. Doğrudan butona basarak girebilirsiniz.
                        </div>
                    </div>
                @endif

                <form id="vendorAuthForm" action="{{ route('vendor_portal.verify', ['token' => $vendor->access_token]) }}"
                    method="POST">
                    @csrf
                    <div style="margin-bottom: 20px;">
                        <label
                            style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 8px;">
                            GÜVENLİK ŞİFRESİ (PASSKEY)
                        </label>
                        <input type="text" name="passkey" id="inputPasskey" required autofocus
                            placeholder="Örn: KKS-XXXX-123"
                            style="width: 100%; height: 46px; border: 2px solid #cbd5e1; border-radius: 8px; padding: 0 14px; font-size: 15px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; box-sizing: border-box; text-align: center; color: #0f172a;"
                            value="{{ old('passkey', app()->isLocal() ? $vendor->passkey : '') }}">
                    </div>

                    @if(!app()->isLocal())
                        <!-- Google reCAPTCHA v2 (Sadece canlıda) -->
                        <div style="margin-bottom: 20px; display: flex; flex-direction: column; align-items: center;">
                            <div class="g-recaptcha"
                                data-sitekey="{{ config('services.recaptcha.site_key') ?: env('RECAPTCHA_SITE_KEY', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI') }}">
                            </div>
                            @error('recaptcha')
                                <div
                                    style="color: #ef4444; font-size: 12.5px; text-align: center; margin-top: 8px; font-weight: 600;">
                                    ⚠️ {{ $message }}
                                </div>
                            @enderror
                        </div>
                    @endif

                    <!-- Çift Tıklama Korumalı Giriş Butonu -->
                    <button type="submit" id="btnAuthSubmit" class="btn-portal-primary"
                        style="width: 100%; height: 46px; justify-content: center; font-size: 14px; font-weight: 700; transition: all 0.2s ease;">
                        Portala Güvenli Giriş Yap ➔
                    </button>
                </form>

                <!-- ŞİFREMİ UNUTTUM / TEKRAR GÖNDER BÖLÜMÜ -->
                <div
                    style="margin-top: 24px; padding: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; text-align: center;">
                    <div style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                        🔐 Şifrenizi kaybettiniz veya bulamıyor musunuz?
                    </div>
                    <div style="font-size: 11.5px; color: #64748b; margin-bottom: 12px; line-height: 1.4;">
                        Güvenlik şifrenizi kayıtlı e-posta adresinize
                        (<strong>{{ Str::mask($vendor->email, '*', 3, -4) }}</strong>) anında yeniden iletebilirsiniz.
                    </div>
                    <form id="resendPasskeyForm"
                        action="{{ route('vendor_portal.resend_passkey', ['token' => $vendor->access_token]) }}"
                        method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" id="btnResendPasskey" class="btn-portal-secondary"
                            style="width: 100%; justify-content: center; font-size: 12.5px; padding: 9px 14px; border-color: #cbd5e1; color: #0284c7; font-weight: 700;">
                            <span>✉️</span> Güvenlik Şifremi Kayıtlı E-Postama Tekrar Gönder
                        </button>
                    </form>
                </div>

                <div
                    style="margin-top: 20px; padding-top: 16px; border-top: 1px solid #f1f5f9; text-align: center; font-size: 11.5px; color: #64748b;">
                    Sorun yaşamaya devam ediyorsanız Köksan proje yöneticiniz ile iletişime geçebilirsiniz.
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const authForm = document.getElementById('vendorAuthForm');
            const authBtn = document.getElementById('btnAuthSubmit');
            const inputPasskey = document.getElementById('inputPasskey');

            if (authForm && authBtn) {
                authForm.addEventListener('submit', function (e) {
                    if (authBtn.disabled) {
                        e.preventDefault();
                        return false;
                    }

                    if (!inputPasskey.value.trim()) {
                        e.preventDefault();
                        inputPasskey.focus();
                        return false;
                    }

                    // Çift tıklamayı anında kilitle
                    authBtn.disabled = true;
                    authBtn.innerHTML = '<span>⏳ Giriş Yapılıyor, Lütfen Bekleyiniz...</span>';
                    authBtn.style.opacity = '0.75';
                    authBtn.style.cursor = 'not-allowed';
                });
            }

            const resendForm = document.getElementById('resendPasskeyForm');
            const resendBtn = document.getElementById('btnResendPasskey');
            if (resendForm && resendBtn) {
                resendForm.addEventListener('submit', function (e) {
                    if (resendBtn.disabled) {
                        e.preventDefault();
                        return false;
                    }
                    resendBtn.disabled = true;
                    resendBtn.innerHTML = '<span>⏳ E-Posta İletiliyor, Bekleyiniz...</span>';
                    resendBtn.style.opacity = '0.75';
                    resendBtn.style.cursor = 'not-allowed';
                });
            }
        });
    </script>
@endsection