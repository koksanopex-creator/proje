@extends('layouts.app')

@section('title', 'Sistem Ayarları')
@section('page_title', 'Sistem Ayarları')
@section('page_subtitle', 'Kurumsal marka kimliği, küresel portföy parametreleri ve güvenlik politikaları')

@section('content')

    <!-- 1. ÜST BİLGİLENDİRME & SUPERADMIN ROZETİ -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 24px; box-shadow: 0 1px 3px rgba(15,23,42,0.04);">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                ⚙️
            </div>
            <div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: #0f172a;">
                    Sistem Ayarları Yönetim Merkezi
                </h3>
                <p style="margin: 2px 0 0; font-size: 12.5px; color: #64748b;">
                    Bu panelden yapılan güncellemeler sol menü, tarayıcı sekmeleri, e-postalar ve tüm proje modüllerine anında yansır.
                </p>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <span class="role-pill pill-superadmin" style="font-size: 12px; padding: 6px 14px; font-weight: 700; border-radius: 6px;">
                👑 Superadmin Tam Yetki Modu
            </span>
        </div>
    </div>

    <!-- 2. ANA AYARLAR IZGARASI (2 SÜTUNLU MODERN DÜZEN) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 24px; margin-bottom: 24px;">
        
        <!-- KART 1: 🎨 KURUMSAL LOGO & MARKA KİMLİĞİ -->
        <div class="card" style="margin-bottom: 0; border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff; box-shadow: 0 1px 3px rgba(15,23,42,0.04); display: flex; flex-direction: column;">
            <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #f1f5f9; padding: 18px 22px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 class="card-title" style="font-size: 15px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <span>🎨</span> Kurumsal Logo & Markalama
                    </h3>
                    <p style="margin: 4px 0 0; font-size: 12px; color: #64748b;">
                        Sol menü üstünde, sistem sekmelerinde ve giden bildirim e-postalarında gösterilen resmi logo.
                    </p>
                </div>
                <span class="badge" style="background: #eff6ff; color: #1e40af; font-size: 11px; font-weight: 700;">
                    Marka Varlığı
                </span>
            </div>

            <div class="card-body" style="padding: 22px; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- MEVCUT LOGO ÖNİZLEME ALANI -->
                    <div style="margin-bottom: 20px;">
                        <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 8px; display: block;">
                            Mevcut Aktif Kurumsal Logo
                        </label>
                        <div style="background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 10px; padding: 24px; text-align: center; position: relative; min-height: 120px; display: flex; align-items: center; justify-content: center;">
                            @php
                                $siteLogo = \App\Models\SystemSetting::get('site_logo', 'assets/images/koksan_logo.png');
                            @endphp
                            @if(file_exists(public_path($siteLogo)))
                                <img id="logoPreviewImg" src="{{ asset($siteLogo) }}" alt="Köksan Logo" style="max-height: 75px; max-width: 100%; object-fit: contain; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.06));">
                            @else
                                <div id="logoFallback" class="text-muted">
                                    <div style="font-size: 36px; margin-bottom: 4px;">🏭</div>
                                    <div style="font-weight: 800; font-size: 15px; color: #0f172a;">KÖKSAN HOLDİNG</div>
                                    <div style="font-size: 11px; color: #64748b;">Varsayılan Sistem Logosu</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- YENİ DOSYA SEÇİMİ -->
                    <div style="margin-bottom: 20px;">
                        <label class="form-label" style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">
                            Yeni Logo Dosyası Yükle
                        </label>
                        <div style="position: relative;">
                            <input type="file" name="logo" id="logoFileInput" class="form-control" accept="image/png, image/jpeg, image/svg+xml, image/webp" required style="font-size: 12.5px; padding: 8px 12px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px;" onchange="previewSelectedLogo(this)">
                        </div>
                        <div style="display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap;">
                            <span style="font-size: 10.5px; font-weight: 600; background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 4px;">PNG</span>
                            <span style="font-size: 10.5px; font-weight: 600; background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 4px;">SVG</span>
                            <span style="font-size: 10.5px; font-weight: 600; background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 4px;">WebP</span>
                            <span style="font-size: 10.5px; font-weight: 600; background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 4px;">Maks. 2 MB</span>
                        </div>
                        @error('logo')
                            <div style="color: #dc2626; font-size: 12px; margin-top: 6px; font-weight: 600;">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; font-weight: 700; font-size: 13px; padding: 10px 18px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                        <span>📤</span> Kurumsal Logoyu Güncelle & Kaydet
                    </button>
                </form>
            </div>
        </div>

        <!-- KART 2: ⚙️ GENEL PORTFÖY PARAMETRELERİ -->
        <div class="card" style="margin-bottom: 0; border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff; box-shadow: 0 1px 3px rgba(15,23,42,0.04); display: flex; flex-direction: column;">
            <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #f1f5f9; padding: 18px 22px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 class="card-title" style="font-size: 15px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <span>⚙️</span> Genel Sistem & Portföy Parametreleri
                    </h3>
                    <p style="margin: 4px 0 0; font-size: 12px; color: #64748b;">
                        Sistem başlığı, kuruluş resmi ünvanı ve varsayılan para birimi tanımları.
                    </p>
                </div>
                <span class="badge" style="background: #f0fdf4; color: #166534; font-size: 11px; font-weight: 700;">
                    Küresel Konfigürasyon
                </span>
            </div>

            <div class="card-body" style="padding: 22px; flex: 1;">
                <form action="{{ route('admin.settings.update') }}" method="POST">
                    @csrf

                    <!-- SİSTEM / PORTFÖY BAŞLIĞI -->
                    <div class="form-group" style="margin-bottom: 18px;">
                        <label class="form-label" style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">
                            Sistem / Portföy Başlığı *
                        </label>
                        <input type="text" name="site_title" class="form-control" 
                               value="{{ $siteTitle ?? 'Köksan Enterprise PPM' }}" required 
                               style="font-size: 13px; padding: 8px 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-weight: 600; color: #0f172a;">
                        <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">
                            💡 Tarayıcı sekmesinde, bildirim maillerinde ve ana gösterge panelinde görünen resmi portföy başlığıdır.
                        </div>
                    </div>

                    <!-- ŞİRKET / KURULUŞ ÜNVANI -->
                    <div class="form-group" style="margin-bottom: 18px;">
                        <label class="form-label" style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: flex; justify-content: space-between; align-items: center;">
                            <span>Şirket / Kuruluş Resmi Ünvanı</span>
                            <span style="font-size: 10.5px; font-weight: 700; color: #059669; background: #ecfdf5; padding: 2px 6px; border-radius: 4px;">
                                ✓ Merkezi SAP SSO Korumalı
                            </span>
                        </label>
                        <input type="text" class="form-control" 
                               value="{{ $companyName ?? 'Köksan Pet ve Plastik Ambalaj Sanayi ve Ticaret A.Ş.' }}" readonly disabled
                               style="font-size: 13px; padding: 8px 12px; border-radius: 8px; border: 1px solid #e2e8f0; background: #f8fafc; color: #475569; font-weight: 600;">
                        <div style="font-size: 11.5px; color: #94a3b8; margin-top: 4px;">
                            Merkezi kurumsal API ve SSO kimlik doğrulaması ile kilitlenmiştir.
                        </div>
                    </div>

                    <!-- VARSAYILAN PARA BİRİMİ -->
                    <div class="form-group" style="margin-bottom: 22px;">
                        <label class="form-label" style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">
                            Varsayılan Portföy Para Birimi
                        </label>
                        <select name="currency_default" class="form-control" style="font-size: 13px; padding: 8px 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-weight: 600; background: #ffffff;">
                            <option value="TRY" {{ ($currencyDefault ?? 'TRY') === 'TRY' ? 'selected' : '' }}>₺ TRY - Türk Lirası (Varsayılan)</option>
                            <option value="USD" {{ ($currencyDefault ?? 'TRY') === 'USD' ? 'selected' : '' }}>$ USD - Amerikan Doları</option>
                            <option value="EUR" {{ ($currencyDefault ?? 'TRY') === 'EUR' ? 'selected' : '' }}>€ EUR - Euro</option>
                        </select>
                        <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">
                            BOM ve yatırım bütçelerinde kullanılan ana para birimi.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success" style="font-weight: 700; font-size: 13px; padding: 10px 22px; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px; background: #16a34a; border-color: #16a34a;">
                        <span>💾</span> Parametreleri Kaydet & Uygula
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- 3. KÜRESEL GÜVENLİK & İSG POLİTİKALARI (SAFETY GATEKEEPER & POLICIES) -->
    <div class="card" style="margin-bottom: 24px; border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff; box-shadow: 0 1px 3px rgba(15,23,42,0.04);">
        <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #f1f5f9; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                <h3 class="card-title" style="font-size: 15px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <span>🛡️</span> Küresel Proje Güvenliği & Kurumsal Yönetim Politikaları
                </h3>
                <p style="margin: 4px 0 0; font-size: 12px; color: #64748b;">
                    Saha İSG kapı kilitleri, yetki vekaleti ve denetim loglarının sistem genelindeki zorunlu kuralları.
                </p>
            </div>
            <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 11px; font-weight: 700;">
                İSG & Denetim Uyumu
            </span>
        </div>

        <div class="card-body" style="padding: 24px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 18px;">
                
                <!-- POLİTİKA 1: İSG KAPI KİLİDİ -->
                <div style="background: #fffbeb; border: 1px solid #fef3c7; border-radius: 10px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <span style="font-size: 18px;">🔒</span>
                            <h4 style="margin: 0; font-size: 13.5px; font-weight: 800; color: #92400e;">
                                İSG Kapı Kilidi (Safety Gatekeeper)
                            </h4>
                        </div>
                        <p style="font-size: 12px; color: #78350f; line-height: 1.5; margin: 0 0 12px 0;">
                            İnşaat, Enerji ve Hat devreye alma projelerinde <strong>İSG Denetçisi onayı ve İSG İzin Belgesi yüklenmeden</strong> fazların bir sonraki aşamaya geçmesi sistemsel olarak engellenmektedir.
                        </p>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; padding-top: 10px; border-top: 1px solid #fde68a;">
                        <span style="font-size: 11.5px; font-weight: 700; color: #059669; background: #ecfdf5; padding: 3px 8px; border-radius: 4px;">
                            ✓ Sistem Genelinde Aktif
                        </span>
                    </div>
                </div>

                <!-- POLİTİKA 2: SUPERADMIN YÖNETİM MASKELEME -->
                <div style="background: #eff6ff; border: 1px solid #dbeafe; border-radius: 10px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <span style="font-size: 18px;">👤</span>
                            <h4 style="margin: 0; font-size: 13.5px; font-weight: 800; color: #1e40af;">
                                Superadmin Gizlilik Kuralı (Madde 14)
                            </h4>
                        </div>
                        <p style="font-size: 12px; color: #1e3a8a; line-height: 1.5; margin: 0 0 12px 0;">
                            Superadmin tarafından yapılan tüm onay, geri alma ve düzenlemeler diğer tüm rollere <strong>"Yönetim"</strong> veya <strong>"Sistem"</strong> olarak maskelenir. Gerçek ad yalnızca Superadmin ekranlarında görünür.
                        </p>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; padding-top: 10px; border-top: 1px solid #bfdbfe;">
                        <span style="font-size: 11.5px; font-weight: 700; color: #1e40af; background: #dbeafe; padding: 3px 8px; border-radius: 4px;">
                            🛡️ Maskeleme Korumalı
                        </span>
                    </div>
                </div>

                <!-- POLİTİKA 3: DENETİM İZİ & GERİ ALMA (UNDO) -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <span style="font-size: 18px;">📜</span>
                            <h4 style="margin: 0; font-size: 13.5px; font-weight: 800; color: #0f172a;">
                                Kesintisiz Denetim İzi & Geri Alma
                            </h4>
                        </div>
                        <p style="font-size: 12px; color: #475569; line-height: 1.5; margin: 0 0 12px 0;">
                            Tüm faz tamamlama, onay ve saha kararları <strong>ProjectAuditLog</strong> tablosunda saklanır. Superadmin veya yetkili liderler yapılan işlemleri eksiksiz olarak geriye alabilir (Rollback).
                        </p>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; padding-top: 10px; border-top: 1px solid #e2e8f0;">
                        <span style="font-size: 11.5px; font-weight: 700; color: #475569; background: #f1f5f9; padding: 3px 8px; border-radius: 4px;">
                            ⚡ Geri Alma (Undo) Hazır
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- 4. SUNUCU & ALTYAPI BİLGİ KARTLARI -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px;">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 18px;">
            <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Framework</div>
            <div style="font-size: 13.5px; font-weight: 800; color: #0f172a; margin-top: 4px;">Laravel 12 (PHP 8.2)</div>
        </div>
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 18px;">
            <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Veritabanı</div>
            <div style="font-size: 13.5px; font-weight: 800; color: #0f172a; margin-top: 4px;">MySQL 8.0 (koksan_pms)</div>
        </div>
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 18px;">
            <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Çalışma Ortamı</div>
            <div style="font-size: 13.5px; font-weight: 800; color: #15803d; margin-top: 4px;">Localhost (Geliştirme)</div>
        </div>
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 18px;">
            <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Aktif Canlı Port</div>
            <div style="font-size: 13.5px; font-weight: 800; color: #2563eb; margin-top: 4px;">http://127.0.0.1:8088</div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function previewSelectedLogo(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                let img = document.getElementById('logoPreviewImg');
                const fallback = document.getElementById('logoFallback');
                if (!img && fallback) {
                    fallback.style.display = 'none';
                    img = document.createElement('img');
                    img.id = 'logoPreviewImg';
                    img.style.maxHeight = '75px';
                    img.style.maxWidth = '100%';
                    img.style.objectFit = 'contain';
                    fallback.parentNode.appendChild(img);
                }
                if (img) {
                    img.src = e.target.result;
                    img.style.display = 'block';
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush