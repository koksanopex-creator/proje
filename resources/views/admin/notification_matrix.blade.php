@extends('layouts.app')

@section('title', 'Dinamik Bildirim & Şablon Matrisi')

@section('content')
<style>

/* ==========================================================================
   MOBİL VE TABLET UYUMLULUK (RESPONSIVE TABLE & TOUCH SCROLL)
   ========================================================================== */
.matrix-scroll-wrapper {
    position: relative !important;
    overflow-x: auto !important;
    overflow-y: visible !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
    -webkit-overflow-scrolling: touch !important;
}

.matrix-scroll-wrapper::-webkit-scrollbar {
    height: 8px;
}
.matrix-scroll-wrapper::-webkit-scrollbar-track {
    background: #f1f5f9;
}
.matrix-scroll-wrapper::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 4px;
}
.matrix-scroll-wrapper::-webkit-scrollbar-thumb:hover {
    background-color: #94a3b8;
}

/* Masaüstü Sticky Kolon Stilleri (Kaydırmada Sola Sabit Kalır) */
.matrix-modern-table .sticky-col-event {
    position: sticky !important;
    left: 0 !important;
    z-index: 22 !important;
    background: #ffffff !important;
    width: 220px !important;
    min-width: 220px !important;
    max-width: 220px !important;
    border-right: 1px solid #e2e8f0 !important;
}

.matrix-modern-table thead th.sticky-col-event {
    position: sticky !important;
    top: -24px !important;
    left: 0 !important;
    z-index: 35 !important;
    background: #f8fafc !important;
    width: 220px !important;
    min-width: 220px !important;
    max-width: 220px !important;
    border-right: 1px solid #cbd5e1 !important;
    border-bottom: 2px solid #cbd5e1 !important;
}

.matrix-modern-table .sticky-col-template {
    position: sticky !important;
    left: 220px !important;
    z-index: 21 !important;
    background: #ffffff !important;
    width: 240px !important;
    min-width: 240px !important;
    max-width: 240px !important;
    border-right: 2px solid #cbd5e1 !important;
    box-shadow: 4px 0 8px -2px rgba(15, 23, 42, 0.08) !important;
}

.matrix-modern-table thead th.sticky-col-template {
    position: sticky !important;
    top: -24px !important;
    left: 220px !important;
    z-index: 34 !important;
    background: #f8fafc !important;
    width: 240px !important;
    min-width: 240px !important;
    max-width: 240px !important;
    border-right: 2px solid #cbd5e1 !important;
    border-bottom: 2px solid #cbd5e1 !important;
    box-shadow: 4px 0 8px -2px rgba(15, 23, 42, 0.08) !important;
}

.matrix-modern-table tbody tr:hover .sticky-col-event,
.matrix-modern-table tbody tr:hover .sticky-col-template {
    background-color: #f1f5f9 !important;
}

@media (max-width: 768px) {
    /* Başlık ve Butonlar */
    .matrix-page-header {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 14px !important;
        margin-bottom: 18px !important;
    }
    .matrix-page-header .page-title {
        font-size: 20px !important;
    }
    .matrix-page-header .page-subtitle {
        font-size: 12.5px !important;
    }
    .matrix-page-header .header-right {
        width: 100% !important;
        justify-content: flex-start !important;
    }

    /* Kardeş Sekmeler Çubuğu - Responsive Wrap */
    .matrix-sister-tabs {
        flex-wrap: wrap !important;
        height: auto !important;
        gap: 6px !important;
        margin-bottom: 20px !important;
        overflow: visible !important;
    }
    .matrix-tab-link {
        padding: 0 12px !important;
        font-size: 12px !important;
        height: 38px !important;
    }

    /* Kılavuz Kartı */
    .matrix-info-guide-card {
        margin-bottom: 18px !important;
    }
    .matrix-info-guide-card .card-body {
        padding: 14px !important;
    }

    /* Araç Çubuğu (Toolbar) */
    .matrix-toolbar {
        flex-direction: column !important;
        align-items: stretch !important;
        padding: 12px 14px !important;
        gap: 12px !important;
        margin-bottom: 18px !important;
    }
    .matrix-role-filters {
        overflow-x: auto !important;
        flex-wrap: nowrap !important;
        padding-bottom: 6px !important;
        -webkit-overflow-scrolling: touch !important;
        width: 100% !important;
    }
    .matrix-filter-btn {
        padding: 6px 12px !important;
        font-size: 11.5px !important;
        white-space: nowrap !important;
        flex-shrink: 0 !important;
    }
    .matrix-search-box {
        min-width: 100% !important;
        max-width: 100% !important;
    }
    .matrix-toolbar > div:last-child {
        width: 100% !important;
    }
    .matrix-toolbar > div:last-child > div {
        display: flex !important;
        width: 100% !important;
        gap: 8px !important;
    }
    .matrix-toolbar > div:last-child button {
        flex: 1 !important;
        justify-content: center !important;
    }

    /* Matris Tablosu & Dondurulmuş Kolon Mobil Optimizasyonu */
    .matrix-modern-table {
        width: max-content !important;
        font-size: 12px !important;
    }
    /* Sütun 1: Kompakt Sticky (140px) */
    .matrix-modern-table .sticky-col-event,
    .matrix-modern-table thead th.sticky-col-event {
        width: 140px !important;
        min-width: 140px !important;
        max-width: 140px !important;
        padding: 8px 10px !important;
        font-size: 11.5px !important;
        border-right: 2px solid #cbd5e1 !important;
        box-shadow: 3px 0 6px -2px rgba(15, 23, 42, 0.08) !important;
    }
    /* Sütun 2: Mobilde Sticky Olmayacak, Sağa Kaydıkça Roller Görünecek */
    .matrix-modern-table .sticky-col-template,
    .matrix-modern-table thead th.sticky-col-template {
        position: static !important;
        left: auto !important;
        width: 200px !important;
        min-width: 200px !important;
        max-width: 200px !important;
        border-right: 1px solid #e2e8f0 !important;
        box-shadow: none !important;
        z-index: 1 !important;
        padding: 6px 8px !important;
    }
    /* Rol Sütunları Dokunmatik Boyut */
    .matrix-modern-table .matrix-role-col {
        min-width: 110px !important;
        width: 110px !important;
        padding: 10px 4px !important;
    }

    /* Modallar Mobilde Bottom-Sheet / Full-Screen */
    .custom-modal-backdrop {
        padding: 8px !important;
        align-items: flex-end !important;
    }
    .custom-modal-dialog {
        max-width: 100% !important;
        width: 100% !important;
        max-height: 94vh !important;
        border-radius: 16px 16px 0 0 !important;
    }
    .custom-modal-body {
        max-height: calc(94vh - 120px) !important;
        overflow-y: auto !important;
        padding: 14px !important;
    }
}

/* ==========================================================================
   MODERN BİLDİRİM TASLAĞI DÜZENLEME MODALI VE CANLI ÖNİZLEME STİLLERİ
   ========================================================================== */
#templateModalBackdrop {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(5px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
#templateModalBackdrop.show {
    display: flex !important;
}
#templateModalBackdrop .custom-modal-dialog {
    background: #ffffff;
    border-radius: 18px;
    width: 100%;
    max-width: 1020px;
    max-height: 92vh;
    box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    animation: modalScaleUp 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes modalScaleUp {
    from { opacity: 0; transform: scale(0.96) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
.template-modal-tab-btn {
    background: none;
    border: none;
    border-bottom: 2.5px solid transparent;
    padding: 12px 16px;
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s ease;
}
.template-modal-tab-btn:hover {
    color: #0f172a;
    background: rgba(241, 245, 249, 0.6);
}
.template-modal-tab-btn.active {
    color: #0284c7;
    border-bottom-color: #0284c7;
    font-weight: 700;
    background: rgba(224, 242, 254, 0.3);
}
.var-filter-btn {
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #475569;
    cursor: pointer;
    transition: all 0.15s ease;
}
.var-filter-btn:hover {
    background: #f1f5f9;
}
.var-filter-btn.active {
    background: #0284c7 !important;
    color: #ffffff !important;
    border-color: #0284c7 !important;
}


/* ==========================================================================
   MODERN MATRIX HEADER & SPACIOUS SISTER NAVIGATION
   ========================================================================== */
.matrix-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 24px;
    margin-bottom: 24px;
    padding-bottom: 4px;
    flex-wrap: wrap;
}
.matrix-page-header .header-left {
    flex: 1;
    min-width: 300px;
}
.matrix-page-header .page-title {
    font-size: 26px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.025em;
    margin: 0 0 8px 0;
    display: flex;
    align-items: center;
    gap: 12px;
    line-height: 1.25;
}
.matrix-page-header .page-subtitle {
    font-size: 13.5px;
    color: #64748b;
    margin: 0;
    line-height: 1.55;
    max-width: 820px;
    font-weight: 450;
}
.matrix-page-header .header-right {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
    padding-top: 4px;
    flex-wrap: wrap;
}

/* Matrisler Arası Hızlı Geçiş Sekmeler Çubuğu (Responsive & Wrap) */
.matrix-sister-tabs {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 8px !important;
    border-bottom: 2px solid #e2e8f0 !important;
    margin-bottom: 24px !important;
    padding-bottom: 0 !important;
    align-items: center !important;
    box-sizing: border-box !important;
    width: 100% !important;
    height: auto !important;
    min-height: 44px !important;
    overflow: visible !important;
}

.matrix-tab-link {
    display: inline-flex !important;
    align-items: center !important;
    gap: 7px !important;
    padding: 0 14px !important;
    height: 40px !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    color: #64748b !important;
    text-decoration: none !important;
    border-radius: 8px 8px 0 0 !important;
    border-bottom: 3px solid transparent !important;
    transition: all 0.2s ease !important;
    white-space: nowrap !important;
    box-sizing: border-box !important;
    line-height: 1 !important;
}
.matrix-tab-link:hover {
    color: #0284c7 !important;
    background: #f8fafc !important;
}
.matrix-tab-link.active {
    color: #0284c7 !important;
    font-weight: 750 !important;
    background: #f0f9ff !important;
    border-bottom: 3px solid #0284c7 !important;
    box-shadow: 0 -2px 6px rgba(2, 132, 199, 0.05) !important;
}
.matrix-tab-link .tab-emoji {
    font-size: 14px !important;
    line-height: 1 !important;
}

/* Modern Header Eylem Butonları */
.matrix-header-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    height: 40px;
    padding: 0 16px;
    font-size: 13px;
    font-weight: 650;
    color: #334155;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 9px;
    text-decoration: none;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    transition: all 0.2s ease;
    white-space: nowrap;
}
.matrix-header-btn-secondary:hover {
    background: #f8fafc;
    border-color: #94a3b8;
    color: #0f172a;
}
.matrix-header-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    height: 40px;
    padding: 0 18px;
    font-size: 13px;
    font-weight: 700;
    color: #ffffff;
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
    border: 1px solid #0284c7;
    border-radius: 9px;
    text-decoration: none;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);
    transition: all 0.2s ease;
    white-space: nowrap;
}
.matrix-header-btn-primary:hover {
    background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
    box-shadow: 0 4px 10px rgba(2, 132, 199, 0.35);
    color: #ffffff;
}

/* Modern Header */
.matrix-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 24px;
    margin-bottom: 24px;
    padding-bottom: 4px;
    flex-wrap: wrap;
}
.matrix-page-header .header-left {
    flex: 1;
    min-width: 300px;
}
.matrix-page-header .page-title {
    font-size: 26px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.025em;
    margin: 0 0 8px 0;
    display: flex;
    align-items: center;
    gap: 12px;
    line-height: 1.25;
}
.matrix-page-header .page-subtitle {
    font-size: 13.5px;
    color: #64748b;
    margin: 0;
    line-height: 1.55;
    max-width: 820px;
    font-weight: 450;
}
.matrix-page-header .header-right {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
    padding-top: 4px;
    flex-wrap: wrap;
}

/* Modern Header Eylem Butonları */
.matrix-header-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    height: 40px;
    padding: 0 16px;
    font-size: 13px;
    font-weight: 650;
    color: #334155;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 9px;
    text-decoration: none;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    transition: all 0.2s ease;
}
.matrix-header-btn-secondary:hover {
    background: #f8fafc;
    border-color: #94a3b8;
    color: #0f172a;
}
.matrix-header-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    height: 40px;
    padding: 0 18px;
    font-size: 13px;
    font-weight: 700;
    color: #ffffff;
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
    border: 1px solid #0284c7;
    border-radius: 9px;
    text-decoration: none;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);
    transition: all 0.2s ease;
}
.matrix-header-btn-primary:hover {
    background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
    box-shadow: 0 4px 10px rgba(2, 132, 199, 0.35);
    color: #ffffff;
}

/* Bilgi Kılavuz Kartı */
.matrix-info-guide-card {
    border-radius: 14px !important;
    border: 1px solid #e2e8f0 !important;
    background: #ffffff !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03) !important;
    margin-bottom: 28px !important;
}
.matrix-info-guide-card .card-body {
    padding: 18px 22px !important;
}

/* Sütun Gizleme / Gösterme Toggle Butonu */
.btn-col-toggle {
    padding: 6px 13px;
    font-size: 11.5px;
    font-weight: 700;
    border-radius: 8px;
    border: 1.5px solid #cbd5e1;
    cursor: pointer;
    background: #ffffff;
    color: #475569;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
    user-select: none;
    line-height: 1.2;
    white-space: nowrap;
}
.btn-col-toggle:hover {
    border-color: #0284c7;
    color: #0284c7;
    background: #f0f9ff;
}
.btn-col-toggle.active {
    background: #e0f2fe !important;
    color: #0284c7 !important;
    border-color: #7dd3fc !important;
    box-shadow: 0 1px 3px rgba(2, 132, 199, 0.15);
}
.btn-col-toggle:not(.active) {
    background: #f8fafc !important;
    color: #64748b !important;
    border-color: #cbd5e1 !important;
}

/* Şablon Taslağı & Metin Sütunu Gizlendiğinde */
body.hide-template-col .matrix-modern-table .sticky-col-template,
body.hide-template-col .sticky-col-template {
    display: none !important;
}
body.hide-template-col .matrix-modern-table .sticky-col-event,
body.hide-template-col .sticky-col-event {
    border-right: 2px solid #cbd5e1 !important;
    box-shadow: 4px 0 8px -2px rgba(15, 23, 42, 0.08) !important;
}
body.hide-template-col .matrix-modern-table thead th.sticky-col-event,
body.hide-template-col thead th.sticky-col-event {
    border-right: 2px solid #cbd5e1 !important;
    box-shadow: 4px 0 8px -2px rgba(15, 23, 42, 0.08) !important;
}
</style>
<div class="matrix-page-header">
    <div class="header-left">
        <h1 class="page-title">
            <span>🔔</span>
            <span>Dinamik Bildirim & Şablon Matrisi</span>
        </h1>
        <p class="page-subtitle">Sistemdeki tüm tetikleyici olaylar, zil ve e-posta bildirimleri ile kurumsal metin taslaklarını roller bazında anlık yönetin.</p>
    </div>
    <div class="header-right">
        <span class="info-pill" style="height: 38px; padding: 0 16px; border-radius: 20px; font-size: 12.5px; font-weight: 600; background: #ecfdf5; color: #065f46; border: 1.5px solid #a7f3d0; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
            <span class="dot-green"></span>
            <span>Değişiklikler anında canlıya geçer (AJAX)</span>
        </span>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4" style="padding: 12px 18px; border-radius: 10px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-size: 13.5px; font-weight: 500; margin-bottom: 24px;">
        ✓ {{ session('success') }}
    </div>
@endif

<!-- Matrisler & Roller Arası Hızlı Geçiş Sekmeleri -->
<div class="matrix-sister-tabs">
    <a href="{{ route('admin.roles.index') }}" class="matrix-tab-link">
        <span class="tab-emoji">🎭</span>
        <span>Roller & Gruplar</span>
    </a>
    <a href="{{ route('admin.user_roles.index') }}" class="matrix-tab-link">
        <span class="tab-emoji">👥</span>
        <span>Kullanıcı Yetki & Rolleri</span>
    </a>
    <a href="{{ route('admin.permission_matrix') }}" class="matrix-tab-link">
        <span class="tab-emoji">🛡️</span>
        <span>Yetki & Buton Matrisi</span>
    </a>
    <a href="{{ route('admin.notification_matrix') }}" class="matrix-tab-link active">
        <span class="tab-emoji">🔔</span>
        <span>Bildirim Matrisi</span>
    </a>
    <a href="{{ route('admin.doa_thresholds.index') }}" class="matrix-tab-link">
        <span class="tab-emoji">⚖️</span>
        <span>Bütçe Eşik Matrisi (DOA)</span>
    </a>
    <a href="{{ route('admin.committees.index') }}" class="matrix-tab-link">
        <span class="tab-emoji">🏛️</span>
        <span>Kurullar & Komiteler</span>
    </a>
    <a href="{{ route('admin.audit_logs') }}#tab-visibility" class="matrix-tab-link">
        <span class="tab-emoji">📋</span>
        <span>Log Görünürlük Matrisi</span>
    </a>
    <a href="{{ route('admin.system_health') }}" class="matrix-tab-link">
        <span class="tab-emoji">🩺</span>
        <span>Sistem Sağlığı</span>
    </a>
    <a href="{{ route('admin.notification_logs.index') }}" class="matrix-tab-link">
        <span class="tab-emoji">📧</span>
        <span>Bildirim & Mail Logları</span>
    </a>
</div>
<!-- Bilgi ve Prensipler Kartı -->
<div class="card matrix-info-guide-card mb-4">
    <div class="card-body" style="padding: 16px 20px;">
        <div style="display: flex; align-items: flex-start; gap: 14px;">
            <div style="width: 36px; height: 36px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                ℹ️
            </div>
            <div>
                <h4 style="margin: 0 0 4px 0; font-size: 14px; font-weight: 700; color: #0f172a;">Bildirim Kanalları & Taslak Kılavuzu</h4>
                <p style="margin: 0; font-size: 12.5px; color: #475569; line-height: 1.5;">
                    Her rol için <strong>🔔 Zil</strong> (panel içi anlık bildirim) ve <strong>📧 E-Posta</strong> (resmi bilgilendirme iletisi) kanallarını bağımsız olarak açıp kapatabilirsiniz.
                    Olayların metin şablonlarını, e-posta gövdelerini ve değişkenleri (`{proje_adi}`, `{kullanici_adi}`, `{sorumluluk}` vb.) düzenlemek için <strong>"✏️ Taslağı Düzenle"</strong> butonunu kullanabilirsiniz.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Modern Toolbar: Rol Grupları Filtresi ve Arama -->
<div class="matrix-toolbar">
    <div class="matrix-role-filters">
        <span style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-right: 4px;">Görünüm:</span>
        <button type="button" class="matrix-filter-btn active" data-filter="all">
            <span>Tüm Roller</span>
            <span class="filter-badge">{{ count($roles) }}</span>
        </button>
        @foreach($roleGroups as $rg)
            @php $rgCount = collect($roles)->where('role_group', $rg->slug)->count(); @endphp
            <button type="button" class="matrix-filter-btn" data-filter="{{ $rg->slug }}">
                <span>{{ $rg->icon ?? '' }} {{ $rg->name }}</span>
                <span class="filter-badge">{{ $rgCount }}</span>
            </button>
        @endforeach
    </div>

    <!-- Sütun Gizleme / Gösterme Kontrolü -->
    <div class="matrix-column-visibility" style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
        <span style="font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase;">Sütun:</span>
        <button type="button" class="btn-col-toggle active" id="btnToggleTemplateCol" onclick="toggleNotificationTemplateCol()" title="Şablon Taslağı & Metin sütununu gizle veya göster">
            <span class="toggle-icon">👁️</span>
            <span id="tplColToggleLabel">Şablon Taslağı & Metin</span>
        </button>
    </div>
</div>

<!-- Dondurulmuş Bildirim Matrisi Tablosu -->
<div class="matrix-group-card">
    <div class="matrix-group-header">
        <div class="matrix-group-title">
            <span class="group-icon">📢</span>
            <span>OTOMATİK BİLDİRİM KURALLARI & ROL İLİŞKİLERİ</span>
        </div>
        <span class="matrix-group-badge">{{ count($templates) }} Olay Şablonu</span>
    </div>

    <div class="matrix-scroll-wrapper">
        <table class="matrix-modern-table" style="width: 100%; min-width: 100%;">
            <thead>
                <tr>
                    <th class="sticky-col-event">Tetikleyici Olay & Kategori</th>
                    <th class="sticky-col-template">Şablon Taslağı & Metin</th>
                    @foreach($roles as $role)
                        <th class="matrix-role-col role-col-{{ $role->id }}" 
                            data-role-id="{{ $role->id }}" 
                            data-role-group="{{ $role->role_group ?? 'proje' }}"
                            style="min-width: 115px;">
                            <div class="matrix-role-header-badge" 
                                 style="background: {{ $role->badge_bg }}; color: {{ $role->badge_text }}; border: 1px solid {{ $role->badge_border }};"
                                 title="{{ $role->name }}">
                                <span style="font-weight: 700; text-align: center; line-height: 1.2;">{{ $role->name }}</span>
                            </div>
                            <div style="display: flex; justify-content: center; gap: 14px; margin-top: 6px; font-size: 10.5px; font-weight: 600; color: #64748b;">
                                <span>🔔 Zil</span>
                                <span>📧 Mail</span>
                            </div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($templates as $tpl)
                    <tr class="template-row" data-text="{{ strtolower($tpl->event_title . ' ' . $tpl->event_key . ' ' . $tpl->category . ' ' . $tpl->bell_title_template . ' ' . $tpl->mail_subject_template) }}">
                        <!-- Sabit Sütun 1: Olay Adı ve Kategori -->
                        <td class="sticky-col-event">
                            <div class="perm-title-text">{{ $tpl->event_title }}</div>
                            <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                                <span class="badge" style="background: #e0f2fe; color: #0369a1; padding: 2px 7px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                                    {{ $tpl->category }}
                                </span>
                                <code class="perm-code-pill" style="font-size: 11px;">{{ $tpl->event_key }}</code>
                            </div>
                        </td>

                        <!-- Sabit Sütun 2: Şablon Önizleme ve Düzenleme Butonu -->
                        <td class="sticky-col-template">
                            <div class="notif-preview-card" id="previewBox_{{ $tpl->id }}">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 6px; margin-bottom: 4px;">
                                    <div style="font-weight: 700; color: #0f172a;" id="bellTitlePreview_{{ $tpl->id }}">
                                        🔔 {{ $tpl->bell_title_template }}
                                    </div>
                                    <button type="button" class="btn btn-xs btn-outline-primary" style="padding: 2px 7px; font-size: 11px; border-radius: 6px; flex-shrink: 0;" onclick="openTemplateModal({{ json_encode($tpl) }})">
                                        ✏️ Düzenle
                                    </button>
                                </div>
                                <div style="color: #64748b; font-size: 11px; margin-bottom: 4px;" id="bellBodyPreview_{{ $tpl->id }}">
                                    {{ Str::limit($tpl->bell_body_template, 75) }}
                                </div>
                                <div style="font-size: 10.5px; color: #166534; background: #f0fdf4; padding: 2px 6px; border-radius: 4px; display: inline-block;" id="mailSubjectPreview_{{ $tpl->id }}">
                                    📧 {{ Str::limit($tpl->mail_subject_template, 35) }}
                                </div>
                            </div>
                        </td>

                        <!-- Rol Bazlı Çiftli Toggle Switchler -->
                        @foreach($roles as $role)
                            @php
                                $setting = $matrix->first(function($m) use ($tpl, $role) {
                                    return $m->event_key === $tpl->event_key && $m->role_id === $role->id;
                                });
                                $bellActive = $setting ? (bool)$setting->is_bell_active : false;
                                $mailActive = $setting ? (bool)$setting->is_mail_active : false;
                            @endphp
                            <td class="matrix-role-col role-col-{{ $role->id }}" 
                                data-role-id="{{ $role->id }}" 
                                data-role-group="{{ $role->role_group ?? 'proje' }}"
                                style="text-align: center; vertical-align: middle;">
                                <div class="channel-toggles-wrap">
                                    <!-- Zil Switch (Mavi) -->
                                    <label class="custom-toggle" title="{{ $role->name }} için Zil Bildirimi">
                                        <input type="checkbox"
                                               class="notif-toggle"
                                               data-event-key="{{ $tpl->event_key }}"
                                               data-role-id="{{ $role->id }}"
                                               data-channel="bell"
                                               {{ $bellActive ? 'checked' : '' }}>
                                        <span class="toggle-slider"></span>
                                    </label>

                                    <!-- Mail Switch (Yeşil) -->
                                    <label class="custom-toggle" title="{{ $role->name }} için E-Posta Bildirimi">
                                        <input type="checkbox"
                                               class="notif-toggle toggle-mail"
                                               data-event-key="{{ $tpl->event_key }}"
                                               data-role-id="{{ $role->id }}"
                                               data-channel="mail"
                                               {{ $mailActive ? 'checked' : '' }}>
                                        <span class="toggle-slider slider-mail"></span>
                                    </label>
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- ŞABLON DÜZENLEME MODALI (MODERN & CANLI ÖNİZLEMELİ) -->
<div id="templateModalBackdrop" class="custom-modal-backdrop">
    <div class="custom-modal-dialog" style="max-width: 1040px; width: 95vw;">
        <form id="templateEditForm" onsubmit="submitTemplateUpdate(event)" style="display: flex; flex-direction: column; height: 100%; max-height: 92vh;">
            <input type="hidden" id="editTemplateId">

            <!-- Modal Header -->
            <div class="custom-modal-header" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1);">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(59, 130, 246, 0.2); border: 1px solid rgba(96, 165, 250, 0.35); display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        ✏️
                    </div>
                    <div>
                        <h3 class="custom-modal-title" style="margin: 0; font-size: 16px; font-weight: 700; color: #ffffff; display: flex; align-items: center; gap: 8px;">
                            <span>Bildirim Taslağını Düzenle</span>
                            <span id="editModalEventBadge" style="font-size: 11px; font-family: monospace; font-weight: 600; padding: 3px 8px; border-radius: 6px; background: rgba(255,255,255,0.12); color: #93c5fd; border: 1px solid rgba(147, 197, 253, 0.25);">event_key</span>
                        </h3>
                        <p style="margin: 2px 0 0 0; font-size: 12px; color: #94a3b8;">
                            Zil ve e-posta bildirim şablonlarını düzenleyin, dinamik değişkenleri ekleyin ve canlı simülasyonunu test edin.
                        </p>
                    </div>
                </div>
                <button type="button" class="custom-modal-close" onclick="closeTemplateModal()" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12); color: #cbd5e1; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 16px; cursor: pointer; transition: all 0.15s;">✕</button>
            </div>

            <!-- Tab Gezinme Çubuğu -->
            <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 0 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; gap: 4px;">
                    <button type="button" class="template-modal-tab-btn active" id="tabBtn_editor" onclick="switchTemplateModalTab('editor')">
                        <span style="font-size: 15px;">✏️</span>
                        <span>Taslak Düzenleyici</span>
                    </button>
                    <button type="button" class="template-modal-tab-btn" id="tabBtn_preview" onclick="switchTemplateModalTab('preview')">
                        <span style="font-size: 15px;">👁️</span>
                        <span>Canlı Önizleme</span>
                        <span style="font-size: 10px; background: #e0f2fe; color: #0284c7; padding: 1px 6px; border-radius: 9999px; font-weight: 700; margin-left: 4px;">Zil & Mail</span>
                    </button>
                    <button type="button" class="template-modal-tab-btn" id="tabBtn_variables" onclick="switchTemplateModalTab('variables')">
                        <span style="font-size: 15px;">🏷️</span>
                        <span>Değişkenler & Örnekler Rehberi</span>
                        <span id="tabVarCountBadge" style="font-size: 10px; background: #f1f5f9; color: #475569; padding: 1px 6px; border-radius: 9999px; font-weight: 700; margin-left: 4px;">25+</span>
                    </button>
                </div>
                <div style="font-size: 11.5px; color: #64748b; display: flex; align-items: center; gap: 6px; padding: 8px 0;">
                    <span>💡 İpucu:</span>
                    <span>Değişkenlere tıklayarak imlecin bulunduğu alana ekleyebilirsiniz.</span>
                </div>
            </div>

            <!-- Modal Body (Kaydırılabilir İçerik Alanı) -->
            <div class="custom-modal-body" style="padding: 24px; overflow-y: auto; max-height: calc(92vh - 165px); flex: 1; background: #ffffff;">
                
                <!-- ========================================================= -->
                <!-- SEKME 1: TASLAK DÜZENLEYİCİ -->
                <!-- ========================================================= -->
                <div id="tabContent_editor" class="template-modal-tab-pane">
                    <!-- Olay Başlığı -->
                    <div style="margin-bottom: 20px;">
                        <label style="display: flex; align-items: center; justify-content: space-between; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                            <span>Olay & Şablon Başlığı <span class="text-danger">*</span></span>
                            <span style="font-size: 11px; font-weight: 500; color: #64748b;">Kullanıcıların ve yöneticilerin göreceği resmi olay adı</span>
                        </label>
                        <input type="text" id="editEventTitle" class="form-control" style="font-size: 14px; font-weight: 600; padding: 10px 14px; border-radius: 10px; border: 1.5px solid #cbd5e1;" required placeholder="Örn: Toplantı Katılımcı Daveti">
                    </div>

                    <!-- Hızlı Değişken Ekleme Paneli -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 16px; margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 8px;">
                            <span style="font-size: 12px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                                <span>⚡ Hızlı Değişken Ekle</span>
                                <span style="font-size: 11px; font-weight: 500; color: #64748b;">(Tıklanan değişken odaklanılan metin kutusuna anında eklenir)</span>
                            </span>
                            <button type="button" onclick="switchTemplateModalTab('variables')" style="background: none; border: none; color: #0284c7; font-size: 12px; font-weight: 700; cursor: pointer; text-decoration: underline; display: flex; align-items: center; gap: 4px;">
                                <span>Tüm Değişkenleri & Canlı Örnekleri İncele ↗</span>
                            </button>
                        </div>

                        <!-- 2 Kolon: Kullanıcı Değişkenleri & Proje Değişkenleri -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div style="background: #ffffff; padding: 10px 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <div style="font-size: 11px; font-weight: 700; color: #0369a1; text-transform: uppercase; margin-bottom: 6px; display: flex; align-items: center; gap: 4px;">
                                    <span>👤</span> Kullanıcı & Departman
                                </div>
                                <div id="userVarsContainer" style="display: flex; flex-wrap: wrap; gap: 6px;">
                                    <!-- JS ile doldurulacak -->
                                </div>
                            </div>

                            <div style="background: #ffffff; padding: 10px 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <div style="font-size: 11px; font-weight: 700; color: #15803d; text-transform: uppercase; margin-bottom: 6px; display: flex; align-items: center; gap: 4px;">
                                    <span>📁</span> Proje, Kurul & Olay
                                </div>
                                <div id="projectVarsContainer" style="display: flex; flex-wrap: wrap; gap: 6px;">
                                    <!-- JS ile doldurulacak -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- İki Kolon: Sol Zil Şablonu, Sağ E-Posta Şablonu -->
                    <div style="display: grid; grid-template-columns: 1fr 1.25fr; gap: 18px;">
                        
                        <!-- ZİL ŞABLONU KARTI -->
                        <div style="background: #f0f9ff; border: 1.5px solid #bae6fd; border-radius: 14px; padding: 18px; display: flex; flex-direction: column;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid #e0f2fe;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 18px;">🔔</span>
                                    <div>
                                        <div style="font-size: 13.5px; font-weight: 700; color: #0369a1;">Zil Bildirimi Şablonu</div>
                                        <div style="font-size: 11px; color: #0284c7;">Uygulama içi anlık zil & push mesajı</div>
                                    </div>
                                </div>
                                <span style="font-size: 10.5px; background: #e0f2fe; color: #0369a1; padding: 2px 7px; border-radius: 6px; font-weight: 600;">Kompakt Metin</span>
                            </div>

                            <div style="margin-bottom: 12px;">
                                <label style="display: block; font-size: 12px; font-weight: 600; color: #0369a1; margin-bottom: 4px;">Zil Başlığı</label>
                                <input type="text" id="editBellTitle" class="form-control" style="font-size: 13px; border-radius: 8px; border: 1px solid #bae6fd; background: #ffffff;" required placeholder="Örn: Toplantı Daveti: {toplanti_baslik}">
                            </div>

                            <div style="flex: 1; display: flex; flex-direction: column;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                    <label style="font-size: 12px; font-weight: 600; color: #0369a1; margin: 0;">Zil Mesaj Gövdesi</label>
                                    <span style="font-size: 10.5px; color: #0284c7;">**kalın yazı** desteklenir</span>
                                </div>
                                <textarea id="editBellBody" rows="5" class="form-control" style="font-size: 12.5px; line-height: 1.5; border-radius: 8px; border: 1px solid #bae6fd; background: #ffffff; flex: 1; resize: vertical;" required placeholder="Örn: **{islem_yapan}** sizi **{proje_kodu}** projesine ait toplantıya davet etti."></textarea>
                            </div>
                        </div>

                        <!-- E-POSTA ŞABLONU KARTI -->
                        <div style="background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 14px; padding: 18px; display: flex; flex-direction: column;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid #dcfce7;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 18px;">📧</span>
                                    <div>
                                        <div style="font-size: 13.5px; font-weight: 700; color: #166534;">E-Posta Bildirimi Şablonu</div>
                                        <div style="font-size: 11px; color: #15803d;">Microsoft Graph üzerinden gönderilen kurumsal e-posta</div>
                                    </div>
                                </div>
                                <span style="font-size: 10.5px; background: #dcfce7; color: #166534; padding: 2px 7px; border-radius: 6px; font-weight: 600;">Zengin HTML</span>
                            </div>

                            <div style="margin-bottom: 12px;">
                                <label style="display: block; font-size: 12px; font-weight: 600; color: #166534; margin-bottom: 4px;">E-Posta Konu Başlığı (Subject)</label>
                                <input type="text" id="editMailSubject" class="form-control" style="font-size: 13px; border-radius: 8px; border: 1px solid #bbf7d0; background: #ffffff;" required placeholder="Örn: [Toplantı Daveti] {proje_kodu} - {toplanti_baslik}">
                            </div>

                            <div style="flex: 1; display: flex; flex-direction: column;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                    <label style="font-size: 12px; font-weight: 600; color: #166534; margin: 0;">E-Posta HTML Gövdesi</label>
                                    <span style="font-size: 10.5px; color: #15803d;">HTML & Köksan kurumsal şablonla sarılır</span>
                                </div>
                                <textarea id="editMailBody" rows="5" class="form-control" style="font-family: monospace; font-size: 12px; line-height: 1.5; border-radius: 8px; border: 1px solid #bbf7d0; background: #ffffff; flex: 1; resize: vertical;" required placeholder="<p>Sayın <strong>{kullanici_adi}</strong>,</p>..."></textarea>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ========================================================= -->
                <!-- SEKME 2: CANLI ÖNİZLEME (ZİL & E-POSTA SİMÜLASYONU) -->
                <!-- ========================================================= -->
                <div id="tabContent_preview" class="template-modal-tab-pane" style="display: none;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; background: #f8fafc; padding: 10px 16px; border-radius: 10px; border: 1px solid #e2e8f0; flex-wrap: wrap; gap: 8px;">
                        <div style="font-size: 12.5px; color: #475569;">
                            <span style="font-weight: 700; color: #0f172a;">👁️ Canlı Simülasyon:</span>
                            Taslakta yazdığınız metinler ve HTML etiketleri sistemdeki gerçekçi örnek verilerle doldurularak aşağıda birebir önizlenmektedir.
                        </div>
                        <button type="button" onclick="updateTemplateLivePreview()" class="btn btn-sm btn-outline-primary" style="font-size: 11.5px; padding: 4px 10px; border-radius: 6px; display: flex; align-items: center; gap: 4px;">
                            <span>🔄</span> Önizlemeyi Yenile
                        </button>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1.35fr; gap: 20px; align-items: start;">
                        
                        <!-- SOL: ZİL BİLDİRİMİ KART SİMÜLASYONU -->
                        <div>
                            <div style="font-size: 12px; font-weight: 700; color: #0369a1; text-transform: uppercase; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                                <span>🔔</span> Uygulama İçi Zil Bildirimi Önizlemesi
                            </div>

                            <!-- Gerçekçi Zil Popover / Bildirim Kartı -->
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.1); overflow: hidden;">
                                <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 10px 14px; display: flex; justify-content: space-between; align-items: center;">
                                    <div style="display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: #334155;">
                                        <span>🔔 Bildirimler</span>
                                        <span style="font-size: 10px; background: #2563eb; color: #ffffff; padding: 1px 6px; border-radius: 9999px;">1 Yeni</span>
                                    </div>
                                    <span style="font-size: 11px; color: #64748b;">Tümünü Okundu Say</span>
                                </div>

                                <div style="padding: 14px; border-bottom: 1px solid #f1f5f9; background: #eff6ff; display: flex; gap: 12px; align-items: flex-start;">
                                    <div style="width: 36px; height: 36px; border-radius: 10px; background: #2563eb; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3);">
                                        🔔
                                    </div>
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 4px;">
                                            <div id="previewBellTitle" style="font-size: 13px; font-weight: 700; color: #0f172a; word-break: break-word;">
                                                Toplantı Daveti: 2. OSB Saha Koordinasyon Toplantısı
                                            </div>
                                            <span style="font-size: 10.5px; color: #2563eb; font-weight: 600; white-space: nowrap; margin-left: 8px;">Az önce</span>
                                        </div>
                                        <div id="previewBellBody" style="font-size: 12px; color: #334155; line-height: 1.5; word-break: break-word;">
                                            <!-- JS ile doldurulacak -->
                                        </div>
                                    </div>
                                    <div style="width: 8px; height: 8px; border-radius: 50%; background: #2563eb; margin-top: 6px; flex-shrink: 0;"></div>
                                </div>

                                <div style="padding: 10px 14px; background: #fafafa; text-align: center; font-size: 11px; color: #94a3b8;">
                                    Bu kart, kullanıcının ekranının sağ üst köşesindeki zil simgesine tıklandığında açılan bildirim penceresinin birebir simülasyonudur.
                                </div>
                            </div>
                        </div>

                        <!-- SAĞ: E-POSTA İSTEMCİSİ SİMÜLASYONU -->
                        <div>
                            <div style="font-size: 12px; font-weight: 700; color: #166534; text-transform: uppercase; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                                <span>📧</span> Microsoft Graph E-Posta Görünümü Önizlemesi
                            </div>

                            <!-- E-Posta İstemci Çerçevesi -->
                            <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 14px; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.1); overflow: hidden;">
                                
                                <!-- Mail İstemci Başlık Çubuğu -->
                                <div style="background: #f1f5f9; border-bottom: 1px solid #e2e8f0; padding: 12px 16px;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                        <div style="font-size: 13px; font-weight: 700; color: #0f172a;" id="previewMailSubject">
                                            [Toplantı Daveti] PRJ-2026-0001 - 2. OSB Saha Koordinasyonu
                                        </div>
                                        <span style="font-size: 11px; background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 9999px; font-weight: 600;">Gelen Kutusu</span>
                                    </div>
                                    
                                    <div style="font-size: 11.5px; color: #475569; display: flex; flex-direction: column; gap: 2px;">
                                        <div><span style="font-weight: 600; color: #64748b;">Kimden:</span> <strong>Köksan Enterprise PPM</strong> &lt;kys@koksan.com&gt;</div>
                                        <div><span style="font-weight: 600; color: #64748b;">Kime:</span> <strong>Celal KARAMAN</strong> &lt;celal.karaman@koksan.com&gt;</div>
                                        <div><span style="font-weight: 600; color: #64748b;">Tarih:</span> 19 Kasım 2026, 16:30 (Microsoft Graph API)</div>
                                    </div>
                                </div>

                                <!-- Rendered Kurumsal Köksan E-Posta Gövdesi -->
                                <div style="padding: 16px; background: #e2e8f0; max-height: 400px; overflow-y: auto;">
                                    <div style="max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border: 1px solid #cbd5e1;">
                                        
                                        <!-- Kurumsal Header -->
                                        <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 16px 20px; text-align: center; border-bottom: 3px solid #0284c7;">
                                            <h2 style="margin: 0; color: #ffffff; font-size: 15px; font-weight: 800; letter-spacing: 0.5px;">KÖKSAN PROJE YÖNETİM SİSTEMİ</h2>
                                            <p style="margin: 3px 0 0 0; color: #94a3b8; font-size: 11px;">Enterprise PPM & Operasyon Platformu</p>
                                        </div>

                                        <!-- HTML Gövde İçeriği -->
                                        <div id="previewMailBody" style="padding: 20px; font-size: 13px; line-height: 1.6; color: #1e293b; background: #ffffff;">
                                            <!-- JS ile doldurulacak -->
                                        </div>

                                        <!-- Köksan PPM Buton Alanı (Wrap Layout Simülasyonu) -->
                                        <div style="padding: 14px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center;">
                                            <a href="javascript:void(0)" style="display: inline-block; background: #0284c7; color: #ffffff; text-decoration: none; padding: 9px 20px; border-radius: 8px; font-size: 12px; font-weight: 700; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.3);">
                                                Köksan Proje Yönetim Sistemi ↗
                                            </a>
                                            <div style="margin-top: 8px; font-size: 10.5px; color: #94a3b8;">
                                                Bu e-posta Köksan Enterprise PPM bildirim servisi tarafından otomatik oluşturulmuştur.
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

                <!-- ========================================================= -->
                <!-- SEKME 3: DEĞİŞKENLER & ÖRNEKLER REHBERİ -->
                <!-- ========================================================= -->
                <div id="tabContent_variables" class="template-modal-tab-pane" style="display: none;">
                    
                    <!-- Arama & Filtre Çubuğu -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
                        <div style="position: relative; flex: 1; max-width: 360px;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 13px; color: #94a3b8;">🔍</span>
                            <input type="text" id="variableSearchInput" oninput="filterTemplateVariables(this.value)" placeholder="Değişken kodu, açıklama veya örnek ara..." style="width: 100%; padding: 8px 12px 8px 34px; font-size: 12.5px; border-radius: 8px; border: 1.5px solid #cbd5e1; outline: none;">
                        </div>

                        <!-- Kategori Filtre Butonları -->
                        <div style="display: flex; gap: 6px; flex-wrap: wrap;" id="varCategoryFilters">
                            <button type="button" class="btn btn-xs var-filter-btn active" onclick="filterVarCategory('all', this)" style="font-size: 11px; padding: 4px 10px; border-radius: 6px; font-weight: 600;">Tümü</button>
                            <button type="button" class="btn btn-xs var-filter-btn" onclick="filterVarCategory('Kullanıcı', this)" style="font-size: 11px; padding: 4px 10px; border-radius: 6px; font-weight: 600;">👤 Kullanıcı</button>
                            <button type="button" class="btn btn-xs var-filter-btn" onclick="filterVarCategory('Kurul & Komite', this)" style="font-size: 11px; padding: 4px 10px; border-radius: 6px; font-weight: 600;">🏛️ Kurul & Komite</button>
                            <button type="button" class="btn btn-xs var-filter-btn" onclick="filterVarCategory('Proje & Süreç', this)" style="font-size: 11px; padding: 4px 10px; border-radius: 6px; font-weight: 600;">📁 Proje & Süreç</button>
                            <button type="button" class="btn btn-xs var-filter-btn" onclick="filterVarCategory('Toplantı', this)" style="font-size: 11px; padding: 4px 10px; border-radius: 6px; font-weight: 600;">📅 Toplantı</button>
                            <button type="button" class="btn btn-xs var-filter-btn" onclick="filterVarCategory('Finans', this)" style="font-size: 11px; padding: 4px 10px; border-radius: 6px; font-weight: 600;">💰 Finans</button>
                            <button type="button" class="btn btn-xs var-filter-btn" onclick="filterVarCategory('Tarih & Sistem', this)" style="font-size: 11px; padding: 4px 10px; border-radius: 6px; font-weight: 600;">⏰ Tarih & Sistem</button>
                        </div>
                    </div>

                    <!-- Değişkenler Tablosu -->
                    <div style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                        <table class="table mb-0" style="width: 100%; font-size: 12.5px; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                    <th style="padding: 10px 14px; text-align: left; color: #475569; font-weight: 700; width: 180px;">Değişken Kodu</th>
                                    <th style="padding: 10px 14px; text-align: left; color: #475569; font-weight: 700; width: 140px;">Kategori</th>
                                    <th style="padding: 10px 14px; text-align: left; color: #475569; font-weight: 700;">Açıklama</th>
                                    <th style="padding: 10px 14px; text-align: left; color: #475569; font-weight: 700; width: 220px;">Canlı Örnek Değer</th>
                                    <th style="padding: 10px 14px; text-align: center; color: #475569; font-weight: 700; width: 100px;">İşlem</th>
                                </tr>
                            </thead>
                            <tbody id="variablesTableBody">
                                <!-- JS ile dinamik render edilecek -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="custom-modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 24px; display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 11.5px; color: #64748b; display: flex; align-items: center; gap: 8px;">
                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></span>
                    <span>Değişiklikler anında canlı sistem bildirim şablonuna işlenir.</span>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="btn btn-secondary" onclick="closeTemplateModal()" style="font-size: 13px; padding: 7px 16px; border-radius: 8px;">İptal</button>
                    <button type="submit" class="btn btn-primary" id="saveTemplateBtn" style="font-size: 13px; padding: 7px 22px; border-radius: 8px; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                        <span>💾</span> <span>Taslağı Kaydet ve Güncelle</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- TOAST CONTAINER -->
<div id="toastNotification" class="toast-container" style="display: none;">
    <div class="toast-message">
        <span class="toast-icon">✓</span>
        <span id="toastText">Ayar başarıyla güncellendi</span>
    </div>
</div>


<!-- YENİ BİLDİRİM TASLAĞI OLUŞTURMA MODALI -->
<div id="createTemplateModalBackdrop" class="custom-modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; backdrop-filter: blur(4px); align-items: center; justify-content: center;">
    <div class="custom-modal-dialog" style="background: #ffffff; width: 100%; max-width: 620px; border-radius: 14px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.25); overflow: hidden; margin: 20px;">
        <form action="{{ route('admin.notification_templates.store') }}" method="POST">
            @csrf
            <div class="custom-modal-header" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
                <h3 class="custom-modal-title" style="margin: 0; font-size: 15px; font-weight: 700; color: #ffffff; display: flex; align-items: center; gap: 8px;">
                    <span>➕</span> <span>Yeni Bildirim Taslağı Oluştur</span>
                </h3>
                <button type="button" class="custom-modal-close" onclick="closeCreateTemplateModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">✕</button>
            </div>

            <div class="custom-modal-body" style="padding: 20px; max-height: calc(85vh - 120px); overflow-y: auto;">
                <div class="grid-2 mb-3" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group mb-0">
                        <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">
                            Olay Kodu (event_key) <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="event_key" required placeholder="örn: gorev_tamamlandi" class="form-control" style="height: 38px; font-size: 12.5px;">
                        <small style="font-size: 10.5px; color: #64748b;">Küçük harf ve alt çizgi ile benzersiz kod</small>
                    </div>
                    <div class="form-group mb-0">
                        <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">
                            Kategori <span class="text-danger">*</span>
                        </label>
                        <select name="category" class="form-control" required style="height: 38px; font-size: 12.5px;">
                            <option value="Görev">Görev</option>
                            <option value="Proje">Proje</option>
                            <option value="Ekip">Ekip</option>
                            <option value="İSG">İSG</option>
                            <option value="Bütçe">Bütçe</option>
                            <option value="Toplantı">Toplantı</option>
                        </select>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px; display: block;">
                        Olay Başlığı <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="event_title" required placeholder="örn: Görev Tamamlandı Olarak İşaretlendi" class="form-control" style="height: 38px; font-size: 13px;">
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 14px;">
                    <h5 style="margin: 0 0 8px 0; font-size: 12.5px; font-weight: 700; color: #0284c7;">🔔 Zil (In-App) Bildirim Şablonu</h5>
                    <div class="form-group mb-2">
                        <label style="font-size: 11px; font-weight: 600; color: #475569; display: block; margin-bottom: 3px;">Zil Başlığı</label>
                        <input type="text" name="bell_title_template" required placeholder="Yeni Görev: {gorev_adi}" class="form-control" style="height: 36px; font-size: 12px;">
                    </div>
                    <div class="form-group mb-0">
                        <label style="font-size: 11px; font-weight: 600; color: #475569; display: block; margin-bottom: 3px;">Zil Mesaj Gövdesi</label>
                        <textarea name="bell_body_template" rows="2" required placeholder="{proje_adi} projesinde {gorev_adi} görevi güncellendi." class="form-control" style="font-size: 12px;"></textarea>
                    </div>
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 10px;">
                    <h5 style="margin: 0 0 8px 0; font-size: 12.5px; font-weight: 700; color: #059669;">📧 E-Posta Şablonu</h5>
                    <div class="form-group mb-2">
                        <label style="font-size: 11px; font-weight: 600; color: #475569; display: block; margin-bottom: 3px;">E-Posta Konusu</label>
                        <input type="text" name="mail_subject_template" required placeholder="Görev Bildirimi: {gorev_adi}" class="form-control" style="height: 36px; font-size: 12px;">
                    </div>
                    <div class="form-group mb-0">
                        <label style="font-size: 11px; font-weight: 600; color: #475569; display: block; margin-bottom: 3px;">E-Posta HTML Gövdesi</label>
                        <textarea name="mail_body_template" rows="3" required placeholder="Sayın {kullanici_adi}, {proje_adi} projesindeki göreviniz hakkında bilgilendirme..." class="form-control" style="font-size: 12px;"></textarea>
                    </div>
                </div>
            </div>

            <div class="custom-modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeCreateTemplateModal()" style="font-size: 12px; padding: 6px 14px;">İptal</button>
                <button type="submit" class="btn btn-primary" style="font-size: 12px; padding: 6px 18px; font-weight: 700;">Kaydet ve Matrise Ekle</button>
            </div>
        </form>
    </div>
</div>

<script>
window.openCreateTemplateModal = function() {
    const modal = document.getElementById('createTemplateModalBackdrop');
    if (modal) modal.style.display = 'flex';
}
window.closeCreateTemplateModal = function() {
    const modal = document.getElementById('createTemplateModalBackdrop');
    if (modal) modal.style.display = 'none';
}
</script>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('notificationSearch');
    const rows = document.querySelectorAll('.template-row');
    const filterButtons = document.querySelectorAll('.matrix-filter-btn');
    const roleCells = document.querySelectorAll('.matrix-role-col');

    // 1. Rol Grubu Filtreleme (Tabs)
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const filter = this.getAttribute('data-filter');

            roleCells.forEach(cell => {
                const group = cell.getAttribute('data-role-group');
                if (filter === 'all' || group === filter) {
                    cell.classList.remove('role-col-hidden');
                } else {
                    cell.classList.add('role-col-hidden');
                }
            });
        });
    });

    // 2. Canlı Arama Filtresi
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase().trim();
            rows.forEach(row => {
                const text = row.getAttribute('data-text') || '';
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }

    // 3. AJAX Zil & Mail Toggle
    const toggles = document.querySelectorAll('.notif-toggle');
    toggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const eventKey = this.dataset.eventKey;
            const roleId = this.dataset.roleId;
            const channel = this.dataset.channel;
            const state = this.checked ? 1 : 0;
            const originalState = !this.checked;

            this.disabled = true;

            fetch("{{ route('admin.notification_matrix.toggle') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    event_key: eventKey,
                    role_id: roleId,
                    channel: channel,
                    state: state
                })
            })
            .then(res => res.json())
            .then(data => {
                this.disabled = false;
                if (data.success) {
                    const channelName = channel === 'bell' ? 'Zil 🔔' : 'E-Posta 📧';
                    showToast(`${channelName} bildirimi başarıyla güncellendi.`);
                } else {
                    this.checked = originalState;
                    alert('Hata: ' + (data.message || 'İşlem başarısız'));
                }
            })
            .catch(err => {
                this.disabled = false;
                this.checked = originalState;
                console.error(err);
                alert('Sunucu iletişim hatası oluştu!');
            });
        });
    });
});

let activeFocusedInput = null;

// Hangi inputun aktif olduğunu takip et & anlık önizleme tetikle
document.addEventListener('focusin', function(e) {
    if (e.target && ['editEventTitle', 'editBellTitle', 'editBellBody', 'editMailSubject', 'editMailBody'].includes(e.target.id)) {
        activeFocusedInput = e.target;
    }
});

document.addEventListener('input', function(e) {
    if (e.target && ['editBellTitle', 'editBellBody', 'editMailSubject', 'editMailBody'].includes(e.target.id)) {
        if (typeof updateTemplateLivePreview === 'function') {
            updateTemplateLivePreview();
        }
    }
});

// Canlı Simülasyon İçin Kapsamlı Gerçekçi Örnek Değerler
const sampleVariables = {
    // Kullanıcı & Personel
    kullanici_adi: "Celal KARAMAN",
    user_name: "Celal KARAMAN",
    username: "celal.karaman",
    ad_soyad: "Celal KARAMAN",
    islem_yapan: "Enes BOZKURT",
    action_by: "Enes BOZKURT",
    departman_adi: "Bilgi Teknolojileri",
    department_name: "Bilgi Teknolojileri",
    bolum_adi: "Bilgi Teknolojileri",
    unvan: "Yazılım Geliştirme Yöneticisi",
    rol: "Proje Yöneticisi",
    role: "Proje Yöneticisi",
    eposta: "celal.karaman@koksan.com",
    email: "celal.karaman@koksan.com",

    // Kurul & Komite
    committee_name: "İSG ve Çevre Üst Kurulu",
    kurul_adi: "İSG ve Çevre Üst Kurulu",
    komite_adi: "İSG ve Çevre Üst Kurulu",
    assigned_by: "Enes BOZKURT",
    atanan_kisi: "Celal KARAMAN",
    removed_by: "Enes BOZKURT",
    cikarilma_nedeni: "Dönemsel Kurul Rotasyonu",
    member_role: "Asil Üye",
    uye_rolu: "Asil Üye",
    voting_weight: "1.0",
    oy_agirligi: "1.0",

    // Proje, Süreç & Görev
    proje_adi: "Gaziantep 2. OSB Pet Preform Hat Kurulumu",
    project_name: "Gaziantep 2. OSB Pet Preform Hat Kurulumu",
    proje_kodu: "PRJ-2026-0001",
    project_code: "PRJ-2026-0001",
    faz_adi: "Faz 2: Mekanik Montaj & Altyapı",
    phase_name: "Faz 2: Mekanik Montaj & Altyapı",
    adim_adi: "Ana Hat Ekstrüder Kurulumu",
    step_name: "Ana Hat Ekstrüder Kurulumu",
    gorev_adi: "Statik Basınç ve Sızdırmazlık Testi",
    task_name: "Statik Basınç ve Sızdırmazlık Testi",
    dosya_adi: "teknik_sartname_rev3.pdf",
    kategori: "Yatırım & Altyapı",
    category: "Yatırım & Altyapı",
    durum: "Devam Ediyor",
    status: "Devam Ediyor",
    ilerleme: "%68",
    progress: "%68",

    // Toplantı
    toplanti_baslik: "2. OSB Saha Koordinasyon & İSG Değerlendirmesi",
    meeting_title: "2. OSB Saha Koordinasyon & İSG Değerlendirmesi",
    lokasyon: "2. OSB Fabrika Toplantı Salonu A / Teams",
    location: "2. OSB Fabrika Toplantı Salonu A / Teams",
    gunden_alani: "1. Ekstrüder montaj takvimi 2. İSG yüksekte çalışma izinleri",
    agenda: "1. Ekstrüder montaj takvimi 2. İSG yüksekte çalışma izinleri",
    toplanti_url: "{{ url('toplantilar') }}",

    // Finans & Bütçe
    butce: "1.250.000 USD",
    budget: "1.250.000 USD",
    tutar: "45.000 EUR",
    amount: "45.000 EUR",
    harcanan: "380.000 USD",
    kalan_butce: "870.000 USD",

    // Tarih, Sistem & Diğer
    tarih: "19.11.2026 16:30",
    date: "19.11.2026 16:30",
    tarih_saat: "19.11.2026 16:30",
    datetime: "19.11.2026 16:30",
    sure: "14 Gün",
    duration: "14 Gün",
    ek_not: "Acil aksiyon ve değerlendirme gerektirmektedir.",
    notlar: "Tüm onay adımları başarıyla tamamlanmıştır.",
    url: "{{ url('projects') }}/1"
};

// Değişkenler Rehberi Veritabanı
const variableDirectory = [
    // Kullanıcı & Personel
    { key: 'kullanici_adi', category: 'Kullanıcı', desc: 'Bildirimi alan kullanıcının adı soyadı', example: 'Celal KARAMAN' },
    { key: 'islem_yapan', category: 'Kullanıcı', desc: 'Olayı veya değişikliği gerçekleştiren yetkili personel', example: 'Enes BOZKURT' },
    { key: 'departman_adi', category: 'Kullanıcı', desc: 'Kullanıcının bağlı olduğu departman / bölüm', example: 'Bilgi Teknolojileri' },
    { key: 'unvan', category: 'Kullanıcı', desc: 'Kullanıcının kurumsal unvanı', example: 'Yazılım Geliştirme Yöneticisi' },
    { key: 'rol', category: 'Kullanıcı', desc: 'Kullanıcının sistemdeki veya projedeki rolü', example: 'Proje Yöneticisi' },
    { key: 'eposta', category: 'Kullanıcı', desc: 'Kullanıcının kurumsal e-posta adresi', example: 'celal.karaman@koksan.com' },

    // Kurul & Komite
    { key: 'committee_name', category: 'Kurul & Komite', desc: 'İlgili kurul, komite veya oylama meclisinin adı', example: 'İSG ve Çevre Üst Kurulu' },
    { key: 'kurul_adi', category: 'Kurul & Komite', desc: 'Kurul adı (Türkçe alternatif token)', example: 'İSG ve Çevre Üst Kurulu' },
    { key: 'member_role', category: 'Kurul & Komite', desc: 'Kurul üyesinin rolü (Asil Üye, Raportör, Başkan vb.)', example: 'Asil Üye' },
    { key: 'assigned_by', category: 'Kurul & Komite', desc: 'Üyeyi kurula atayan yetkili', example: 'Enes BOZKURT' },
    { key: 'removed_by', category: 'Kurul & Komite', desc: 'Üyeyi kuruldan çıkaran yetkili', example: 'Enes BOZKURT' },
    { key: 'cikarilma_nedeni', category: 'Kurul & Komite', desc: 'Kuruldan çıkarılma açıklaması veya gerekçesi', example: 'Dönemsel Kurul Rotasyonu' },
    { key: 'voting_weight', category: 'Kurul & Komite', desc: 'Kurul üyesinin oy ağırlığı katsayısı', example: '1.0' },

    // Proje & Süreç
    { key: 'proje_adi', category: 'Proje & Süreç', desc: 'İlgili projenin resmi adı', example: 'Gaziantep 2. OSB Pet Preform Hat Kurulumu' },
    { key: 'proje_kodu', category: 'Proje & Süreç', desc: 'Projenin benzersiz takip kodu', example: 'PRJ-2026-0001' },
    { key: 'faz_adi', category: 'Proje & Süreç', desc: 'Proje aşaması / faz başlığı', example: 'Faz 2: Mekanik Montaj & Altyapı' },
    { key: 'adim_adi', category: 'Proje & Süreç', desc: 'İş paketi veya iş akış adımı', example: 'Ana Hat Ekstrüder Kurulumu' },
    { key: 'gorev_adi', category: 'Proje & Süreç', desc: 'Spesifik görev başlığı', example: 'Statik Basınç ve Sızdırmazlık Testi' },
    { key: 'kategori', category: 'Proje & Süreç', desc: 'Proje veya olay kategorisi', example: 'Yatırım & Altyapı' },
    { key: 'durum', category: 'Proje & Süreç', desc: 'İşin güncel durumu', example: 'Devam Ediyor' },
    { key: 'ilerleme', category: 'Proje & Süreç', desc: 'Proje tamamlanma yüzdesi', example: '%68' },
    { key: 'dosya_adi', category: 'Proje & Süreç', desc: 'Eklenen veya onaylanan dosya adı', example: 'teknik_sartname_rev3.pdf' },

    // Toplantı
    { key: 'toplanti_baslik', category: 'Toplantı', desc: 'Toplantının başlığı ve konusu', example: '2. OSB Saha Koordinasyon & İSG Değerlendirmesi' },
    { key: 'lokasyon', category: 'Toplantı', desc: 'Toplantı yeri, salon veya online bağlantı linki', example: '2. OSB Fabrika Toplantı Salonu A / Teams' },
    { key: 'gunden_alani', category: 'Toplantı', desc: 'Toplantıda görüşülecek gündem maddeleri', example: '1. Ekstrüder montaj takvimi 2. İSG izinleri' },
    { key: 'toplanti_url', category: 'Toplantı', desc: 'Toplantı detay sayfasına doğrudan link', example: 'https://kys.koksan.com/proje/toplantilar' },

    // Finans
    { key: 'butce', category: 'Finans', desc: 'Onaya sunulan veya tahsis edilen bütçe', example: '1.250.000 USD' },
    { key: 'tutar', category: 'Finans', desc: 'İşlem tutarı (Fatura, DOA, Harcama vb.)', example: '45.000 EUR' },
    { key: 'kalan_butce', category: 'Finans', desc: 'Kalan kullanılabilir bütçe miktarı', example: '870.000 USD' },

    // Tarih & Sistem
    { key: 'tarih', category: 'Tarih & Sistem', desc: 'İşlem veya bildirim tarihi ve saati', example: '19.11.2026 16:30' },
    { key: 'sure', category: 'Tarih & Sistem', desc: 'İşlem süresi, termin veya bekleme süresi', example: '14 Gün' },
    { key: 'ek_not', category: 'Tarih & Sistem', desc: 'Yetkilinin veya sistemin eklediği not', example: 'Acil aksiyon gerektirmektedir.' },
    { key: 'url', category: 'Tarih & Sistem', desc: 'İlgili ekrana doğrudan yönlendiren sistem linki', example: 'https://kys.koksan.com/proje/projects/1' }
];

let currentVariableCategoryFilter = 'all';
let currentVariableSearchQuery = '';

// Sekme Değiştirici
window.switchTemplateModalTab = function(tabKey) {
    const tabs = ['editor', 'preview', 'variables'];
    tabs.forEach(t => {
        const btn = document.getElementById(`tabBtn_${t}`);
        const pane = document.getElementById(`tabContent_${t}`);
        if (btn) btn.classList.toggle('active', t === tabKey);
        if (pane) pane.style.display = (t === tabKey) ? 'block' : 'none';
    });

    if (tabKey === 'preview') {
        updateTemplateLivePreview();
    }
};

// Canlı Önizleme Motoru (Zil Kartı ve E-Posta Simülasyonu)
window.updateTemplateLivePreview = function() {
    const bellTitleTpl = document.getElementById('editBellTitle')?.value || '';
    const bellBodyTpl = document.getElementById('editBellBody')?.value || '';
    const mailSubjectTpl = document.getElementById('editMailSubject')?.value || '';
    const mailBodyTpl = document.getElementById('editMailBody')?.value || '';

    function replaceWithSample(text, isHtml = false, isMailBody = false) {
        if (!text) return '';
        let res = text;
        for (const [k, v] of Object.entries(sampleVariables)) {
            const re = new RegExp(`\\{${k}\\}`, 'gi');
            if (isHtml) {
                const color = isMailBody ? '#166534' : '#0369a1';
                res = res.replace(re, `<strong style="color: ${color}; font-weight: 700;">${v}</strong>`);
            } else {
                res = res.replace(re, v);
            }
        }
        // Markdown **text** dönüştürme
        res = res.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        return res;
    }

    // Zil Önizlemesi
    const pBellTitle = document.getElementById('previewBellTitle');
    const pBellBody = document.getElementById('previewBellBody');
    if (pBellTitle) pBellTitle.innerHTML = replaceWithSample(bellTitleTpl, true, false);
    if (pBellBody) pBellBody.innerHTML = replaceWithSample(bellBodyTpl, true, false).replace(/\n/g, '<br>');

    // E-Posta Önizlemesi
    const pMailSubject = document.getElementById('previewMailSubject');
    const pMailBody = document.getElementById('previewMailBody');
    if (pMailSubject) pMailSubject.innerHTML = replaceWithSample(mailSubjectTpl, false, false);
    if (pMailBody) pMailBody.innerHTML = replaceWithSample(mailBodyTpl, true, true);
};

// Değişken Tablosunu Render Et
window.renderVariablesTable = function() {
    const tbody = document.getElementById('variablesTableBody');
    if (!tbody) return;

    const filtered = variableDirectory.filter(item => {
        const matchesCat = (currentVariableCategoryFilter === 'all' || item.category === currentVariableCategoryFilter);
        const q = currentVariableSearchQuery.toLowerCase().trim();
        const matchesQuery = !q || item.key.toLowerCase().includes(q) || item.desc.toLowerCase().includes(q) || item.example.toLowerCase().includes(q);
        return matchesCat && matchesQuery;
    });

    if (filtered.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align: center; padding: 30px; color: #94a3b8; font-size: 13px;">
                    🔍 Arama kriterine uygun değişken bulunamadı.
                </td>
            </tr>
        `;
        return;
    }

    const catBadgeStyles = {
        'Kullanıcı': 'background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;',
        'Kurul & Komite': 'background: #fdf2f8; color: #9d174d; border: 1px solid #fbcfe8;',
        'Proje & Süreç': 'background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0;',
        'Toplantı': 'background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe;',
        'Finans': 'background: #fefce8; color: #854d0e; border: 1px solid #fef08a;',
        'Tarih & Sistem': 'background: #f8fafc; color: #475569; border: 1px solid #e2e8f0;'
    };

    tbody.innerHTML = filtered.map(item => `
        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
            <td style="padding: 10px 14px; font-weight: 700;">
                <code style="background: #f1f5f9; color: #0f172a; padding: 3px 8px; border-radius: 6px; font-size: 12px; border: 1px solid #e2e8f0; cursor: pointer;" onclick="insertVariableTag('{${item.key}}')" title="Tıkla ve imlecin olduğu yere ekle">
                    {${item.key}}
                </code>
            </td>
            <td style="padding: 10px 14px;">
                <span style="font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 9999px; display: inline-block; ${catBadgeStyles[item.category] || 'background: #f1f5f9; color: #475569;'}">
                    ${item.category}
                </span>
            </td>
            <td style="padding: 10px 14px; color: #334155;">${item.desc}</td>
            <td style="padding: 10px 14px;">
                <span style="font-size: 12px; font-weight: 600; color: #0284c7; background: #e0f2fe; padding: 2px 8px; border-radius: 6px; display: inline-block;">
                    ${item.example}
                </span>
            </td>
            <td style="padding: 10px 14px; text-align: center;">
                <button type="button" class="btn btn-xs btn-outline-primary" style="font-size: 11px; padding: 3px 10px; border-radius: 6px; font-weight: 600;" onclick="insertVariableTag('{${item.key}}')">
                    + Ekle
                </button>
            </td>
        </tr>
    `).join('');
};

window.filterTemplateVariables = function(query) {
    currentVariableSearchQuery = query;
    renderVariablesTable();
};

window.filterVarCategory = function(cat, btn) {
    currentVariableCategoryFilter = cat;
    document.querySelectorAll('.var-filter-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    renderVariablesTable();
};

// Şablon Düzenleme Modalı Açma
window.openTemplateModal = function(tpl) {
    document.getElementById('editTemplateId').value = tpl.id;
    document.getElementById('editEventTitle').value = tpl.event_title || '';
    document.getElementById('editBellTitle').value = tpl.bell_title_template || '';
    document.getElementById('editBellBody').value = tpl.bell_body_template || '';
    document.getElementById('editMailSubject').value = tpl.mail_subject_template || '';
    document.getElementById('editMailBody').value = tpl.mail_body_template || '';

    // Modal Header rozetine olay anahtarını yaz
    const eventBadge = document.getElementById('editModalEventBadge');
    if (eventBadge) eventBadge.textContent = tpl.event_key || 'event_key';

    // Hızlı değişken etiketlerini oluştur
    const userContainer = document.getElementById('userVarsContainer');
    const projectContainer = document.getElementById('projectVarsContainer');
    if (userContainer) userContainer.innerHTML = '';
    if (projectContainer) projectContainer.innerHTML = '';

    const quickUserVars = [
        { label: '{kullanici_adi}', desc: 'Alıcı Adı Soyadı' },
        { label: '{islem_yapan}', desc: 'İşlemi Yapan Personel' },
        { label: '{departman_adi}', desc: 'Bölüm / Departman' },
        { label: '{unvan}', desc: 'Kullanıcı Unvanı' },
        { label: '{tarih}', desc: 'İşlem Tarihi' }
    ];

    let rawVars = tpl.available_variables;
    if (typeof rawVars === 'string') {
        try { rawVars = JSON.parse(rawVars); } catch(e) { rawVars = []; }
    }
    rawVars = Array.isArray(rawVars) ? rawVars : [];

    const defaultProjectVars = ['proje_adi', 'proje_kodu', 'faz_adi', 'adim_adi', 'dosya_adi', 'rol', 'sure', 'butce', 'kategori', 'ilerleme', 'toplanti_baslik', 'committee_name'];
    const allProjectKeys = Array.from(new Set([...rawVars, ...defaultProjectVars]));

    quickUserVars.forEach(item => {
        if (userContainer) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'variable-tag-btn';
            btn.style.cssText = 'background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 11px; padding: 3px 8px; border-radius: 6px; cursor: pointer; font-weight: 600; transition: all 0.15s;';
            btn.textContent = item.label;
            btn.title = item.desc;
            btn.onclick = () => insertVariableTag(item.label);
            userContainer.appendChild(btn);
        }
    });

    allProjectKeys.forEach(key => {
        if (!['kullanici_adi', 'username', 'departman_adi', 'department_name', 'islem_yapan', 'action_by', 'unvan', 'tarih', 'date'].includes(key)) {
            if (projectContainer) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'variable-tag-btn';
                btn.style.cssText = 'background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; font-size: 11px; padding: 3px 8px; border-radius: 6px; cursor: pointer; font-weight: 600; transition: all 0.15s;';
                btn.textContent = `{${key}}`;
                btn.title = `Proje / Olay Değişkeni: ${key}`;
                btn.onclick = () => insertVariableTag(`{${key}}`);
                projectContainer.appendChild(btn);
            }
        }
    });

    // Sekmeyi ilk sekmeye getir ve tabloları doldur
    switchTemplateModalTab('editor');
    renderVariablesTable();
    updateTemplateLivePreview();

    document.getElementById('templateModalBackdrop').classList.add('show');
};

window.closeTemplateModal = function() {
    document.getElementById('templateModalBackdrop').classList.remove('show');
};

window.insertVariableTag = function(tag) {
    const target = activeFocusedInput || document.getElementById('editBellBody');
    if (!target) return;

    // İmlecin olduğu yere ekle
    const start = target.selectionStart || target.value.length;
    const end = target.selectionEnd || target.value.length;
    target.value = target.value.substring(0, start) + tag + target.value.substring(end);
    target.focus();
    target.setSelectionRange(start + tag.length, start + tag.length);

    // Canlı önizlemeyi yenile ve toast bildirimi ver
    updateTemplateLivePreview();
    showToast(`"${tag}" taslağa eklendi.`);
};

window.submitTemplateUpdate = function(e) {
    e.preventDefault();
    const id = document.getElementById('editTemplateId').value;
    const saveBtn = document.getElementById('saveTemplateBtn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span>⏳</span> <span>Kaydediliyor...</span>';

    const payload = {
        event_title: document.getElementById('editEventTitle').value,
        bell_title_template: document.getElementById('editBellTitle').value,
        bell_body_template: document.getElementById('editBellBody').value,
        mail_subject_template: document.getElementById('editMailSubject').value,
        mail_body_template: document.getElementById('editMailBody').value,
    };

    fetch(`{{ url('admin/bildirim-sablonlari') }}/${id}`, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            "Accept": "application/json"
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<span>💾</span> <span>Taslağı Kaydet ve Güncelle</span>';

        if (data.success) {
            // Matris tablosundaki kart önizlemelerini anında güncelle
            const bTitle = document.getElementById(`bellTitlePreview_${id}`);
            const bBody = document.getElementById(`bellBodyPreview_${id}`);
            const mSubject = document.getElementById(`mailSubjectPreview_${id}`);

            if (bTitle) bTitle.textContent = '🔔 ' + payload.bell_title_template;
            if (bBody) bBody.textContent = payload.bell_body_template.substring(0, 75);
            if (mSubject) mSubject.textContent = '📧 ' + payload.mail_subject_template.substring(0, 35);

            closeTemplateModal();
            showToast('Bildirim taslağı başarıyla güncellendi.');
        } else {
            alert('Hata: ' + (data.message || 'Güncelleme başarısız'));
        }
    })
    .catch(err => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<span>💾</span> <span>Taslağı Kaydet ve Güncelle</span>';
        console.error(err);
        alert('Sunucu iletişim hatası oluştu!');
    });
};

function showToast(msg) {
    const toast = document.getElementById('toastNotification');
    const text = document.getElementById('toastText');
    if (!toast || !text) return;
    text.textContent = msg;
    toast.style.display = 'block';
    toast.classList.add('show');
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => { toast.style.display = 'none'; }, 300);
    }, 2500);
}

document.getElementById('templateModalBackdrop').addEventListener('click', function(e) {
    if (e.target === this) {
        closeTemplateModal();
    }
});



// =========================================================================
// SÜTUN GİZLEME / GÖSTERME MOTORU (EXCEL MODELİ & LOCALSTORAGE KALICILIĞI)
// =========================================================================
window.toggleNotificationTemplateCol = function() {
    const isCurrentlyHidden = document.body.classList.contains('hide-template-col');
    window.applyTemplateColumnVisibility(isCurrentlyHidden);
};

window.applyTemplateColumnVisibility = function(show) {
    const btn = document.getElementById('btnToggleTemplateCol');
    const label = document.getElementById('tplColToggleLabel');
    if (show) {
        document.body.classList.remove('hide-template-col');
        if (btn) {
            btn.classList.add('active');
            btn.classList.remove('btn-inactive');
        }
        if (label) label.textContent = 'Şablon Taslağı & Metin';
        localStorage.setItem('notif_matrix_show_template', '1');
    } else {
        document.body.classList.add('hide-template-col');
        if (btn) {
            btn.classList.remove('active');
            btn.classList.add('btn-inactive');
        }
        if (label) label.textContent = 'Şablon Taslağı (Gizli)';
        localStorage.setItem('notif_matrix_show_template', '0');
    }
};

document.addEventListener('DOMContentLoaded', function() {
    const savedTplState = localStorage.getItem('notif_matrix_show_template');
    if (savedTplState === '0') {
        window.applyTemplateColumnVisibility(false);
    } else {
        window.applyTemplateColumnVisibility(true);
    }
});
</script>
@endpush