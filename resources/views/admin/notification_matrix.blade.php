@extends('layouts.app')

@section('title', 'Dinamik Bildirim & Şablon Matrisi')

@section('content')
<style>

/* ==========================================================================
   MOBİL VE TABLET UYUMLULUK (RESPONSIVE TABLE & TOUCH SCROLL)
   ========================================================================== */
.matrix-scroll-wrapper {
    position: relative !important;
    overflow: visible !important;
    width: 100% !important;
    max-width: 100% !important;
}

@media (max-width: 991.98px) {
    .matrix-scroll-wrapper {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
    }
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
        <table class="matrix-modern-table">
            <thead>
                <tr>
                    <th class="sticky-col-event">Tetikleyici Olay & Kategori</th>
                    <th class="sticky-col-template">Şablon Taslağı & Metin</th>
                    @foreach($roles as $role)
                        <th class="matrix-role-col role-col-{{ $role->id }}" 
                            data-role-id="{{ $role->id }}" 
                            data-role-group="{{ $role->role_group ?? 'proje' }}"
                            style="min-width: 115px; width: 115px;">
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

<!-- ŞABLON DÜZENLEME MODALI -->
<div id="templateModalBackdrop" class="custom-modal-backdrop">
    <div class="custom-modal-dialog" style="max-width: 620px;">
        <form id="templateEditForm" onsubmit="submitTemplateUpdate(event)">
            <input type="hidden" id="editTemplateId">

            <div class="custom-modal-header">
                <h3 class="custom-modal-title">
                    <span>✏️ Bildirim Taslağını Düzenle</span>
                </h3>
                <button type="button" class="custom-modal-close" onclick="closeTemplateModal()">✕</button>
            </div>

            <div class="custom-modal-body">
                <div class="form-group mb-3">
                    <label class="font-weight-600 text-sm mb-1" style="display:block;">Olay Başlığı <span class="text-danger">*</span></label>
                    <input type="text" id="editEventTitle" class="form-control" required>
                </div>

                <!-- Dinamik Değişkenler Butonları -->
                <div class="mb-3 p-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span class="text-xs font-weight-700 text-dark" style="display: flex; align-items: center; gap: 6px;">
                            <span>🏷️</span> Kullanılabilir Değişkenler (Tıklayarak İmlecin Olduğu Yere Ekleyin):
                        </span>
                        <span style="font-size: 11px; color: #64748b;">Hem TR hem EN değişkenler desteklenir</span>
                    </div>

                    <!-- Kategori 1: Kullanıcı & Departman -->
                    <div style="margin-bottom: 8px;">
                        <span style="font-size: 10.5px; font-weight: 700; color: #0369a1; text-transform: uppercase; display: block; margin-bottom: 4px;">
                            👤 Kullanıcı & Departman:
                        </span>
                        <div id="userVarsContainer" style="display: flex; flex-wrap: wrap; gap: 6px;">
                            <!-- JS ile doldurulacak -->
                        </div>
                    </div>

                    <!-- Kategori 2: Proje, Süreç & Tarih -->
                    <div>
                        <span style="font-size: 10.5px; font-weight: 700; color: #15803d; text-transform: uppercase; display: block; margin-bottom: 4px;">
                            📁 Proje, Olay & Süreç:
                        </span>
                        <div id="projectVarsContainer" style="display: flex; flex-wrap: wrap; gap: 6px;">
                            <!-- JS ile doldurulacak -->
                        </div>
                    </div>
                </div>

                <!-- Zil Şablonu -->
                <div class="card p-3 mb-3" style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px;">
                    <div class="font-weight-700 text-sm mb-2" style="color: #0369a1;">🔔 Zil Bildirimi Şablonu</div>
                    
                    <div class="form-group mb-2">
                        <label class="text-xs font-weight-600 text-muted mb-1" style="display:block;">Zil Başlığı</label>
                        <input type="text" id="editBellTitle" class="form-control" required>
                    </div>

                    <div class="form-group mb-0">
                        <label class="text-xs font-weight-600 text-muted mb-1" style="display:block;">Zil Mesaj Gövdesi</label>
                        <textarea id="editBellBody" rows="2" class="form-control" required></textarea>
                    </div>
                </div>

                <!-- E-Posta Şablonu -->
                <div class="card p-3 mb-2" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;">
                    <div class="font-weight-700 text-sm mb-2" style="color: #166534;">📧 E-Posta Bildirimi Şablonu</div>
                    
                    <div class="form-group mb-2">
                        <label class="text-xs font-weight-600 text-muted mb-1" style="display:block;">E-Posta Konu Başlığı</label>
                        <input type="text" id="editMailSubject" class="form-control" required>
                    </div>

                    <div class="form-group mb-0">
                        <label class="text-xs font-weight-600 text-muted mb-1" style="display:block;">E-Posta HTML Gövdesi</label>
                        <textarea id="editMailBody" rows="3" class="form-control" required></textarea>
                    </div>
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeTemplateModal()">İptal</button>
                <button type="submit" class="btn btn-primary" id="saveTemplateBtn">Kaydet ve Güncelle</button>
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

// Hangi inputun aktif olduğunu takip et
document.addEventListener('focusin', function(e) {
    if (e.target && ['editBellTitle', 'editBellBody', 'editMailSubject', 'editMailBody'].includes(e.target.id)) {
        activeFocusedInput = e.target;
    }
});

// Şablon Düzenleme Modalı
window.openTemplateModal = function(tpl) {
    document.getElementById('editTemplateId').value = tpl.id;
    document.getElementById('editEventTitle').value = tpl.event_title;
    document.getElementById('editBellTitle').value = tpl.bell_title_template;
    document.getElementById('editBellBody').value = tpl.bell_body_template;
    document.getElementById('editMailSubject').value = tpl.mail_subject_template;
    document.getElementById('editMailBody').value = tpl.mail_body_template;

    // Değişken etiketlerini kategorilere ayırarak oluştur
    const userContainer = document.getElementById('userVarsContainer');
    const projectContainer = document.getElementById('projectVarsContainer');
    if (userContainer) userContainer.innerHTML = '';
    if (projectContainer) projectContainer.innerHTML = '';

    // Evrensel Kullanıcı & Departman Değişkenleri
    const userVarsList = [
        { key: 'kullanici_adi', label: '{kullanici_adi}', desc: 'Alıcı Adı Soyadı' },
        { key: 'username', label: '{username}', desc: 'Kullanıcı Adı (EN)' },
        { key: 'departman_adi', label: '{departman_adi}', desc: 'Bölüm / Departman' },
        { key: 'department_name', label: '{department_name}', desc: 'Departman (EN)' },
        { key: 'islem_yapan', label: '{islem_yapan}', desc: 'İşlemi Yapan Personel' },
        { key: 'action_by', label: '{action_by}', desc: 'İşlemi Yapan (EN)' },
        { key: 'unvan', label: '{unvan}', desc: 'Kullanıcı Unvanı' },
        { key: 'tarih', label: '{tarih}', desc: 'İşlem Tarih & Saat' },
        { key: 'date', label: '{date}', desc: 'Tarih (EN)' }
    ];

    // Olay ve Proje Değişkenleri
    let rawVars = tpl.available_variables;
    if (typeof rawVars === 'string') {
        try { rawVars = JSON.parse(rawVars); } catch(e) { rawVars = []; }
    }
    rawVars = Array.isArray(rawVars) ? rawVars : [];

    const defaultProjectVars = ['proje_adi', 'proje_kodu', 'faz_adi', 'adim_adi', 'dosya_adi', 'rol', 'sure', 'butce', 'kategori', 'ilerleme'];
    const allProjectKeys = Array.from(new Set([...rawVars, ...defaultProjectVars]));

    // Kullanıcı Değişkenlerini Ekle
    userVarsList.forEach(item => {
        if (userContainer) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'variable-tag-btn';
            btn.style.cssText = 'background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 11px; padding: 3px 8px; border-radius: 6px; cursor: pointer; font-weight: 600; transition: all 0.15s;';
            btn.textContent = item.label;
            btn.title = item.desc;
            btn.onmouseover = () => { btn.style.background = '#dbeafe'; };
            btn.onmouseout = () => { btn.style.background = '#eff6ff'; };
            btn.onclick = () => insertVariableTag(item.label);
            userContainer.appendChild(btn);
        }
    });

    // Proje & Olay Değişkenlerini Ekle
    allProjectKeys.forEach(key => {
        // Kullanıcı değişkenleriyle mükerrer olmasın
        if (!['kullanici_adi', 'username', 'departman_adi', 'department_name', 'islem_yapan', 'action_by', 'unvan', 'tarih', 'date'].includes(key)) {
            if (projectContainer) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'variable-tag-btn';
                btn.style.cssText = 'background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; font-size: 11px; padding: 3px 8px; border-radius: 6px; cursor: pointer; font-weight: 600; transition: all 0.15s;';
                btn.textContent = `{${key}}`;
                btn.title = `Proje / Olay Değişkeni: ${key}`;
                btn.onmouseover = () => { btn.style.background = '#dcfce7'; };
                btn.onmouseout = () => { btn.style.background = '#f0fdf4'; };
                btn.onclick = () => insertVariableTag(`{${key}}`);
                projectContainer.appendChild(btn);
            }
        }
    });

    document.getElementById('templateModalBackdrop').classList.add('show');
}

window.closeTemplateModal = function() {
    document.getElementById('templateModalBackdrop').classList.remove('show');
}

window.insertVariableTag = function(tag) {
    const target = activeFocusedInput || document.getElementById('editBellBody');
    const start = target.selectionStart || target.value.length;
    const end = target.selectionEnd || target.value.length;
    target.value = target.value.substring(0, start) + tag + target.value.substring(end);
    target.focus();
}

window.submitTemplateUpdate = function(e) {
    e.preventDefault();
    const id = document.getElementById('editTemplateId').value;
    const saveBtn = document.getElementById('saveTemplateBtn');
    saveBtn.disabled = true;
    saveBtn.textContent = '⏳ Kaydediliyor...';

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
        saveBtn.textContent = 'Kaydet ve Güncelle';

        if (data.success) {
            // Canlı önizleme kartını güncelle
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
        saveBtn.textContent = 'Kaydet ve Güncelle';
        console.error(err);
        alert('Sunucu iletişim hatası oluştu!');
    });
}

function showToast(msg) {
    const toast = document.getElementById('toastNotification');
    const text = document.getElementById('toastText');
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