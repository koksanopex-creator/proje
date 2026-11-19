@extends('layouts.app')

@section('title', 'Dinamik Yetki & Buton Matrisi')

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

    .matrix-info-guide-card {
        margin-bottom: 18px !important;
    }
    .matrix-info-guide-card .card-body {
        padding: 14px !important;
    }

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
    .matrix-column-visibility {
        width: 100% !important;
    }
    .matrix-column-visibility button {
        width: 100% !important;
        justify-content: center !important;
    }

    .matrix-modern-table {
        width: max-content !important;
        font-size: 12px !important;
    }
    /* Sütun 1: Kompakt Sticky (140px) */
    .matrix-modern-table .sticky-col-perm,
    .matrix-modern-table thead th.sticky-col-perm {
        width: 140px !important;
        min-width: 140px !important;
        max-width: 140px !important;
        padding: 8px 10px !important;
        font-size: 11.5px !important;
        border-right: 2px solid #cbd5e1 !important;
        box-shadow: 3px 0 6px -2px rgba(15, 23, 42, 0.08) !important;
    }
    /* Sütun 2: Mobilde Sticky Olmayacak, Sağa Kaydıkça Roller Görünecek */
    .matrix-modern-table .sticky-col-slug,
    .matrix-modern-table thead th.sticky-col-slug {
        position: static !important;
        left: auto !important;
        width: 130px !important;
        min-width: 130px !important;
        max-width: 130px !important;
        border-right: 1px solid #e2e8f0 !important;
        box-shadow: none !important;
        z-index: 1 !important;
        padding: 6px 8px !important;
    }
    .matrix-modern-table .matrix-role-col {
        min-width: 110px !important;
        width: 110px !important;
        padding: 10px 4px !important;
    }

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

/* Sistem Kodu (Slug) Sütunu Gizlendiğinde */
body.hide-slug-col .matrix-modern-table .sticky-col-slug,
body.hide-slug-col .sticky-col-slug {
    display: none !important;
}
body.hide-slug-col .matrix-modern-table .sticky-col-perm,
body.hide-slug-col .sticky-col-perm {
    border-right: 2px solid #cbd5e1 !important;
    box-shadow: 4px 0 8px -2px rgba(15, 23, 42, 0.08) !important;
}
body.hide-slug-col .matrix-modern-table thead th.sticky-col-perm,
body.hide-slug-col thead th.sticky-col-perm {
    border-right: 2px solid #cbd5e1 !important;
    box-shadow: 4px 0 8px -2px rgba(15, 23, 42, 0.08) !important;
}
</style>
<div class="matrix-page-header">
    <div class="header-left">
        <h1 class="page-title">
            <span>🛡️</span>
            <span>Dinamik Yetki & Buton Matrisi</span>
        </h1>
        <p class="page-subtitle">Sıfır Hardcode mimarisi: Sistemdeki tüm sayfa, buton ve aksiyon yetkilerini roller bazında anlık yönetin.</p>
    </div>
    <div class="header-right">
        <form action="{{ route('admin.permission_matrix.reset_defaults') }}" method="POST" onsubmit="return confirm('Tüm rollere standart önerilen sistem yetkilerini (Bölüm Lideri, Direktör, vb.) yüklemek istediğinize emin misiniz?');" style="display: inline-block;">
            @csrf
            <button type="submit" class="matrix-header-btn-secondary" style="background: #f0fdf4; border-color: #86efac; color: #166534;" title="Bölüm Lideri, Direktör ve tüm rollere standart sistem yetkilerini yükle">
                <span>⚡</span>
                <span>Standart Yetkileri Yükle</span>
            </button>
        </form>
        <a href="{{ route('admin.roles.index') }}" class="matrix-header-btn-secondary" title="Rolleri ve Etiket Renklerini Düzenle">
            <span>🎨</span>
            <span>Rol & Renk Yönetimi</span>
        </a>
        <button type="button" class="matrix-header-btn-primary" onclick="openRoleModal('create')" title="Yeni Rol Ekle">
            <span>+</span>
            <span>Yeni Rol Ekle</span>
        </button>
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
    <a href="{{ route('admin.permission_matrix') }}" class="matrix-tab-link active">
        <span class="tab-emoji">🛡️</span>
        <span>Yetki & Buton Matrisi</span>
    </a>
    <a href="{{ route('admin.notification_matrix') }}" class="matrix-tab-link">
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
                <h4 style="margin: 0 0 4px 0; font-size: 14px; font-weight: 700; color: #0f172a;">Yetki Kılavuzu & Güvenlik Prensipleri</h4>
                <p style="margin: 0; font-size: 12.5px; color: #475569; line-height: 1.5;">
                    Kural 14/15/19 gereğince <strong>Superadmin</strong> tüm izinlere doğal olarak sahiptir. 
                    <strong>Yönetim (ID=19)</strong> rolü ise "Salt Okunur (Read-Only)" denetim rolü olup ekleme, düzenleme ve onaylama aksiyonlarına dahil edilemez (kilitlidir).
                    Rollerin isimlerini veya etiket renklerini değiştirmek için başlık üzerindeki <strong>✏️</strong> simgesine veya üstteki <strong>🎨 Rol & Renk Yönetimi</strong> butonuna tıklayabilirsiniz.
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
        <button type="button" class="btn-col-toggle active" id="btnToggleSlugCol" onclick="toggleSlugColumn()" title="Sistem Kodu (Slug) sütununu gizle veya göster">
            <span class="toggle-icon">👁️</span>
            <span id="slugColToggleLabel">Sistem Kodu (Slug)</span>
        </button>
    </div>
</div>

@foreach($permissions as $groupName => $groupPermissions)
    @php
        $groupIcon = match($groupName) {
            'Projeler' => '📁',
            'İSG & Denetim' => '🦺',
            'Kanban & Görevler' => '📋',
            'Görevler & Zaman' => '⏱️',
            'Bütçe & BOM' => '💰',
            'Toplantılar' => '👥',
            'Raporlar' => '📊',
            'Sistem & Güvenlik' => '🛡️',
            default => '🔑',
        };
    @endphp
    <div class="matrix-group-card permission-group-card" data-group="{{ strtolower($groupName) }}">
        <div class="matrix-group-header">
            <div class="matrix-group-title">
                <span class="group-icon">{{ $groupIcon }}</span>
                <span>{{ strtoupper($groupName) }} İZİNLERİ</span>
            </div>
            <span class="matrix-group-badge">{{ count($groupPermissions) }} İzin Tanımı</span>
        </div>

        <div class="matrix-scroll-wrapper">
            <table class="matrix-modern-table">
                <thead>
                    <tr>
                        <th class="sticky-col-perm">İzin Tanımı</th>
                        <th class="sticky-col-slug">Sistem Kodu (Slug)</th>
                        @foreach($roles as $role)
                            <th class="matrix-role-col role-col-{{ $role->id }}" 
                                data-role-id="{{ $role->id }}" 
                                data-role-group="{{ $role->role_group ?? 'proje' }}">
                                <div class="matrix-role-header-badge" 
                                     style="background: {{ $role->badge_bg }}; color: {{ $role->badge_text }}; border: 1px solid {{ $role->badge_border }};"
                                     title="{{ $role->name }} ({{ $role->slug }})">
                                    <div style="display: flex; align-items: center; justify-content: center; gap: 3px; width: 100%; text-align: center;">
                                        <span style="text-align: center; line-height: 1.2;">{{ $role->name }}</span>
                                        <button type="button" 
                                                class="role-header-edit-btn" 
                                                title="Rolü & Rengi Düzenle"
                                                onclick="openRoleModal('edit', {{ json_encode($role) }})">
                                            ✏️
                                        </button>
                                    </div>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupPermissions as $permission)
                        <tr class="permission-row" data-text="{{ strtolower($permission->name . ' ' . $permission->slug . ' ' . ($permission->description ?? '')) }}">
                            <!-- Sabit Sütun 1: İzin Adı ve Açıklaması -->
                            <td class="sticky-col-perm">
                                <span class="perm-title-text">{{ $permission->name }}</span>
                                @if(!empty($permission->description) && $permission->description !== 'Tanım girilmemiş')
                                    <span class="perm-desc-text">{{ $permission->description }}</span>
                                @endif
                            </td>

                            <!-- Sabit Sütun 2: Sistem Slug'ı -->
                            <td class="sticky-col-slug">
                                <code class="perm-code-pill">{{ $permission->slug }}</code>
                            </td>

                            <!-- Rol Toggle Hücreleri -->
                            @foreach($roles as $role)
                                @php
                                    $hasPermission = $role->permissions->contains('id', $permission->id);
                                    $isSuperAdmin = $role->isSuperAdmin();
                                    $isYonetim = $role->isYonetim();
                                    $isMutation = in_array(explode('.', $permission->slug)[1] ?? '', ['create', 'edit', 'delete', 'approve', 'toggle']);
                                @endphp
                                <td class="matrix-role-col role-col-{{ $role->id }}" 
                                    data-role-id="{{ $role->id }}" 
                                    data-role-group="{{ $role->role_group ?? 'proje' }}">
                                    @if($isSuperAdmin)
                                        <span class="badge-superadmin-access" title="Superadmin tüm yetkilere doğal olarak sahiptir">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                                            <span>Tam Yetki</span>
                                        </span>
                                    @elseif($isYonetim && $isMutation)
                                        <span class="badge-locked-access" title="Yönetim rolü salt okunurdur, mutasyon yapamaz">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                            <span>Kilitli</span>
                                        </span>
                                    @else
                                        <label class="custom-toggle" title="{{ $role->name }} - {{ $permission->name }}">
                                            <input type="checkbox" 
                                                   class="matrix-checkbox"
                                                   data-role-id="{{ $role->id }}"
                                                   data-permission-id="{{ $permission->id }}"
                                                   {{ $hasPermission ? 'checked' : '' }}>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach

<!-- ROL EKLEME / DÜZENLEME MODALI -->
<div id="roleModalBackdrop" class="custom-modal-backdrop">
    <div class="custom-modal-dialog">
        <form id="roleForm" method="POST" action="{{ route('admin.roles.store') }}">
            @csrf
            <div id="methodSpoof"></div>

            <div class="custom-modal-header">
                <h3 class="custom-modal-title" id="roleModalTitle">
                    <span>🎭 Rol Tanımı & Etiket Rengi</span>
                </h3>
                <button type="button" class="custom-modal-close" onclick="closeRoleModal()">✕</button>
            </div>

            <div class="custom-modal-body" style="padding: 22px;">
                <!-- GRUP 1: 📋 TEMEL ROL BİLGİLERİ & SİSTEM KİMLİĞİ -->
                <div class="modal-form-group-card">
                    <div class="modal-group-header">
                        <span class="modal-group-icon">📋</span>
                        <span class="modal-group-title">1. Rol Kimliği & Sistem Kodu</span>
                    </div>

                    <div class="form-group mb-3">
                        <label class="modal-label">Rol Adı <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="roleNameInput" class="form-control" placeholder="Örn: Saha / Şantiye Şefi, Satın Alma..." required oninput="updateLivePreview()">
                    </div>

                    <div class="form-group mb-0">
                        <label class="modal-label">Sistem Kodu (Slug)</label>
                        <input type="text" name="slug" id="roleSlugInput" class="form-control" placeholder="Otomatik üretilir (örn: saha_sefi)">
                        <div class="modal-field-helper">
                            <span class="helper-icon">ℹ️</span>
                            <span>Boş bırakırsanız rol adından otomatik oluşturulur. Sistem rollerinde değiştirilemez.</span>
                        </div>
                    </div>
                </div>

                <!-- GRUP 2: 🏢 MATRİS GRUBU & YETKİ KAPSAMI -->
                <div class="modal-form-group-card">
                    <div class="modal-group-header">
                        <span class="modal-group-icon">🏢</span>
                        <span class="modal-group-title">2. Matris Grubu & Yetki Kapsamı</span>
                    </div>

                    <div class="form-group mb-3">
                        <label class="modal-label">Rol Grubu (Matris Filtresi) <span class="text-danger">*</span></label>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <select name="role_group" id="roleGroupSelect" class="form-control" required style="height: 40px; font-size: 13px; flex: 1;">
                                @foreach($roleGroups as $rg)
                                    <option value="{{ $rg->slug }}" data-icon="{{ $rg->icon }}">{{ $rg->icon }} {{ $rg->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-sm" onclick="openCreateGroupModal()" style="height: 40px; padding: 0 14px; font-size: 12px; font-weight: 700; border-radius: 8px; border: 1.5px solid #bfdbfe; background: #eff6ff; color: #1d4ed8; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; cursor: pointer;" title="Yeni Rol Grubu Tanımla">
                                <span>+</span> <span>Yeni Grup</span>
                            </button>
                        </div>
                        <div class="modal-field-helper">
                            <span class="helper-icon">💡</span>
                            <span>Yetki matrisi sayfasındaki filtreleme sekmelerinde bu rolün hangi kategoride listeleneceğini belirler.</span>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="modal-label">Görev Tanımı & Açıklama <span class="text-muted" style="font-weight: 500; font-size: 11px;">(Opsiyonel)</span></label>
                        <textarea name="description" id="roleDescInput" class="form-control" rows="2" placeholder="Rolün genel sorumluluk alanı veya açıklaması..."></textarea>
                    </div>
                </div>

                <!-- GRUP 3: 🎨 ETİKET & ROZET RENGİ -->
                <div class="modal-form-group-card">
                    <div class="modal-group-header">
                        <span class="modal-group-icon">🎨</span>
                        <span class="modal-group-title">3. Etiket & Rozet Rengi</span>
                    </div>

                    <div class="form-group mb-3">
                        <label class="text-xs font-weight-700 text-muted mb-2 d-block">Hızlı Renk Paleti:</label>
                        <div class="color-swatches-grid">
                            <button type="button" class="color-swatch-btn" style="background: #dc2626;" data-color="#dc2626" title="Köksan Kırmızısı" onclick="selectColor('#dc2626')"></button>
                            <button type="button" class="color-swatch-btn" style="background: #ea580c;" data-color="#ea580c" title="Sıcak Turuncu" onclick="selectColor('#ea580c')"></button>
                            <button type="button" class="color-swatch-btn" style="background: #d97706;" data-color="#d97706" title="Kehribar Sarısı" onclick="selectColor('#d97706')"></button>
                            <button type="button" class="color-swatch-btn" style="background: #059669;" data-color="#059669" title="Zümrüt Yeşili" onclick="selectColor('#059669')"></button>
                            <button type="button" class="color-swatch-btn" style="background: #0d9488;" data-color="#0d9488" title="Camgöbeği" onclick="selectColor('#0d9488')"></button>
                            <button type="button" class="color-swatch-btn" style="background: #0284c7;" data-color="#0284c7" title="Fiori Mavisi" onclick="selectColor('#0284c7')"></button>
                            <button type="button" class="color-swatch-btn" style="background: #2563eb;" data-color="#2563eb" title="Derin Mavi" onclick="selectColor('#2563eb')"></button>
                            <button type="button" class="color-swatch-btn" style="background: #4f46e5;" data-color="#4f46e5" title="İndigo" onclick="selectColor('#4f46e5')"></button>
                            <button type="button" class="color-swatch-btn" style="background: #7c3aed;" data-color="#7c3aed" title="Ametist Moru" onclick="selectColor('#7c3aed')"></button>
                            <button type="button" class="color-swatch-btn" style="background: #475569;" data-color="#475569" title="Çelik Grisi" onclick="selectColor('#475569')"></button>
                        </div>
                    </div>

                    <!-- Özel HEX Renk Seçici -->
                    <div class="color-picker-input-wrap mb-3">
                        <input type="color" id="roleColorNative" value="#0284c7" oninput="selectColor(this.value)">
                        <input type="text" name="color" id="roleColorHex" class="form-control" value="#0284c7" maxlength="7" pattern="^#[0-9A-Fa-f]{6}$" required oninput="selectColor(this.value)">
                    </div>

                    <!-- Canlı Rozet Önizleme Kutusu -->
                    <div class="live-preview-box">
                        <div class="text-xs text-muted mb-2 font-weight-600">Yetki Matrisi Tablo Başlığı Canlı Önizlemesi:</div>
                        <span id="livePreviewBadge" class="live-preview-badge" style="background: rgba(2, 132, 199, 0.12); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.35); padding: 5px 12px; font-size: 12px; border-radius: 6px; font-weight: 700; display: inline-block;">
                            Örnek Rol Rozeti
                        </span>
                    </div>
                </div>
            </div>

            <div class="custom-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeRoleModal()">İptal</button>
                <button type="submit" class="btn btn-primary" id="roleSubmitBtn">Kaydet</button>
            </div>
        </form>
    </div>
</div>

<div id="toastNotification" class="toast-container" style="display: none;">
    <div class="toast-message">
        <span class="toast-icon">✓</span>
        <span id="toastText">Yetki başarıyla güncellendi</span>
    </div>
</div>

<!-- HIZLI ROL GRUBU OLUŞTURMA MODALI -->
<div id="groupFormModalBackdrop" class="custom-modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); z-index: 10100; align-items: center; justify-content: center; padding: 16px;">
    <div class="custom-modal-dialog" style="max-width: 460px; width: 100%; background: #ffffff; border-radius: 14px; box-shadow: 0 25px 30px -5px rgba(0, 0, 0, 0.25); overflow: hidden;">
        <form id="roleGroupQuickForm" onsubmit="submitQuickRoleGroup(event)">
            @csrf
            <div class="custom-modal-header" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
                <h4 style="margin: 0; font-size: 15px; font-weight: 700; color: #ffffff; display: flex; align-items: center; gap: 8px;">
                    <span>🏷️</span> Yeni Rol Grubu Tanımla
                </h4>
                <button type="button" onclick="closeGroupFormModal()" style="background: none; border: none; color: #ffffff; opacity: 0.8; font-size: 18px; cursor: pointer;">✕</button>
            </div>
            <div style="padding: 20px;">
                <div class="form-group mb-3">
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Grup Adı <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="groupNameInput" class="form-control" placeholder="Örn: Finans & Satın Alma" required style="height: 38px; font-size: 13px;">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 12px; margin-bottom: 14px;">
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">İkon (Emoji)</label>
                        <input type="text" id="groupIconInput" class="form-control" value="🏷️" style="height: 38px; font-size: 15px; text-align: center;">
                    </div>
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Sistem Kodu (Slug)</label>
                        <input type="text" id="groupSlugInput" class="form-control" placeholder="Otomatik" style="height: 38px; font-size: 13px;">
                    </div>
                </div>
            </div>
            <div style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-secondary" onclick="closeGroupFormModal()" style="padding: 6px 14px; font-size: 12.5px; border-radius: 8px; cursor: pointer;">İptal</button>
                <button type="submit" id="btnSubmitGroupQuick" class="btn btn-primary" style="padding: 6px 18px; font-size: 12.5px; font-weight: 700; border-radius: 8px; cursor: pointer; background: #0284c7; border-color: #0284c7;">Kaydet & Seç</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateGroupModal() {
    document.getElementById('groupNameInput').value = '';
    document.getElementById('groupIconInput').value = '🏷️';
    document.getElementById('groupSlugInput').value = '';
    const m = document.getElementById('groupFormModalBackdrop');
    if (m) m.style.display = 'flex';
}
function closeGroupFormModal() {
    const m = document.getElementById('groupFormModalBackdrop');
    if (m) m.style.display = 'none';
}
function submitQuickRoleGroup(e) {
    e.preventDefault();
    const name = document.getElementById('groupNameInput').value.trim();
    const icon = document.getElementById('groupIconInput').value.trim() || '🏷️';
    const slug = document.getElementById('groupSlugInput').value.trim();
    const token = document.querySelector('input[name="_token"]').value;

    const btn = document.getElementById('btnSubmitGroupQuick');
    btn.disabled = true;
    btn.textContent = 'Kaydediliyor...';

    fetch('/admin/rol-gruplari', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
        body: JSON.stringify({ name, icon, slug })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.textContent = 'Kaydet & Seç';
        if (data.success) {
            closeGroupFormModal();
            // Dropdown'a ekle ve seç
            const sel = document.getElementById('roleGroupSelect');
            if (sel) {
                const opt = document.createElement('option');
                opt.value = data.group.slug;
                opt.textContent = (data.group.icon ? data.group.icon + ' ' : '') + data.group.name;
                opt.selected = true;
                sel.appendChild(opt);
            }
            alert(`'${data.group.name}' rol grubu başarıyla eklendi ve seçildi.`);
        } else {
            alert(data.message || 'Hata oluştu.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.textContent = 'Kaydet & Seç';
        alert('Hata: ' + err);
    });
}
</script>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('permissionSearch');
    const rows = document.querySelectorAll('.permission-row');
    const groupCards = document.querySelectorAll('.permission-group-card');
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

    // 2. Canlı Arama Filtresi (İzin adı, slug ve tanım)
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase().trim();
            rows.forEach(row => {
                const text = row.getAttribute('data-text') || '';
                if (text.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            groupCards.forEach(card => {
                const visibleRows = card.querySelectorAll('.permission-row:not([style*="display: none"])');
                if (visibleRows.length === 0 && query !== '') {
                    card.style.display = 'none';
                } else {
                    card.style.display = '';
                }
            });
        });
    }

    // 3. AJAX Checkbox Toggle
    const checkboxes = document.querySelectorAll('.matrix-checkbox');
    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const roleId = this.dataset.roleId;
            const permissionId = this.dataset.permissionId;
            const state = this.checked ? 1 : 0;
            const originalState = !this.checked;

            this.disabled = true;

            fetch("{{ route('admin.permission_matrix.toggle') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    role_id: roleId,
                    permission_id: permissionId,
                    state: state
                })
            })
            .then(res => res.json())
            .then(data => {
                this.disabled = false;
                if (data.success) {
                    showToast('Yetki matrisi güncellendi ve denetim loglarına kaydedildi.');
                } else {
                    this.checked = originalState;
                    alert('Yetki güncellenirken bir hata oluştu: ' + (data.message || 'Bilinmeyen hata'));
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
});

// Rol Modal Yönetimi
function openRoleModal(mode, role = null) {
    const backdrop = document.getElementById('roleModalBackdrop');
    const form = document.getElementById('roleForm');
    const methodSpoof = document.getElementById('methodSpoof');
    const modalTitle = document.getElementById('roleModalTitle');
    const nameInput = document.getElementById('roleNameInput');
    const slugInput = document.getElementById('roleSlugInput');
    const groupSelect = document.getElementById('roleGroupSelect');
    const descInput = document.getElementById('roleDescInput');

    if (mode === 'edit' && role) {
        modalTitle.innerHTML = '<span>🎨 Rol & Renk Düzenle: ' + role.name + '</span>';
        form.action = "{{ url('admin/roller') }}/" + role.id;
        methodSpoof.innerHTML = '<input type="hidden" name="_method" value="PUT">';
        nameInput.value = role.name;
        slugInput.value = role.slug;
        if (role.is_system) {
            slugInput.disabled = true;
        } else {
            slugInput.disabled = false;
        }
        groupSelect.value = role.role_group || 'proje';
        descInput.value = role.description || '';
        selectColor(role.color || '#0284c7');
    } else {
        modalTitle.innerHTML = '<span>+ Yeni Rol Tanımla</span>';
        form.action = "{{ route('admin.roles.store') }}";
        methodSpoof.innerHTML = '';
        nameInput.value = '';
        slugInput.value = '';
        slugInput.disabled = false;
        groupSelect.value = 'proje';
        descInput.value = '';
        selectColor('#0284c7');
    }

    backdrop.classList.add('show');
}

function closeRoleModal() {
    document.getElementById('roleModalBackdrop').classList.remove('show');
}

function selectColor(hex) {
    if (!hex || !hex.startsWith('#') || hex.length !== 7) return;
    
    document.getElementById('roleColorNative').value = hex;
    document.getElementById('roleColorHex').value = hex.toUpperCase();

    // Palet butonlarında aktifliği güncelle
    document.querySelectorAll('.color-swatch-btn').forEach(btn => {
        if (btn.getAttribute('data-color').toLowerCase() === hex.toLowerCase()) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    updateLivePreview();
}

function updateLivePreview() {
    const name = document.getElementById('roleNameInput').value.trim() || 'Örnek Rol Rozeti';
    const hex = document.getElementById('roleColorHex').value.trim() || '#0284c7';
    const preview = document.getElementById('livePreviewBadge');

    preview.textContent = name;

    let r = 2, g = 132, b = 199;
    if (hex.length === 7 && hex.startsWith('#')) {
        r = parseInt(hex.substring(1, 3), 16) || 2;
        g = parseInt(hex.substring(3, 5), 16) || 132;
        b = parseInt(hex.substring(5, 7), 16) || 199;
    }

    preview.style.backgroundColor = `rgba(${r}, ${g}, ${b}, 0.12)`;
    preview.style.color = hex;
    preview.style.borderColor = `rgba(${r}, ${g}, ${b}, 0.35)`;
}

document.getElementById('roleModalBackdrop').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRoleModal();
    }
});

// =========================================================================
// SÜTUN GİZLEME / GÖSTERME MOTORU (EXCEL MODELİ & LOCALSTORAGE KALICILIĞI)
// =========================================================================
window.toggleSlugColumn = function() {
    const isCurrentlyHidden = document.body.classList.contains('hide-slug-col');
    window.applySlugColumnVisibility(isCurrentlyHidden);
};

window.applySlugColumnVisibility = function(show) {
    const btn = document.getElementById('btnToggleSlugCol');
    const label = document.getElementById('slugColToggleLabel');
    if (show) {
        document.body.classList.remove('hide-slug-col');
        if (btn) {
            btn.classList.add('active');
            btn.classList.remove('btn-inactive');
        }
        if (label) label.textContent = 'Sistem Kodu (Slug)';
        localStorage.setItem('perm_matrix_show_slug', '1');
    } else {
        document.body.classList.add('hide-slug-col');
        if (btn) {
            btn.classList.remove('active');
            btn.classList.add('btn-inactive');
        }
        if (label) label.textContent = 'Sistem Kodu (Gizli)';
        localStorage.setItem('perm_matrix_show_slug', '0');
    }
};

document.addEventListener('DOMContentLoaded', function() {
    const savedSlugState = localStorage.getItem('perm_matrix_show_slug');
    if (savedSlugState === '0') {
        window.applySlugColumnVisibility(false);
    } else {
        window.applySlugColumnVisibility(true);
    }
});
</script>
@endpush