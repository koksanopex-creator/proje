@extends('layouts.app')

@section('title', 'Kullanıcı Rolleri & Rozet Renkleri - Köksan Enterprise PPM')

@section('content')
<style>
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

.role-filter-chip {
    padding: 6px 13px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #475569;
    cursor: pointer;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    user-select: none;
    line-height: 1.2;
}
.role-filter-chip:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
    color: #0f172a;
}
.role-filter-chip.active {
    background: #0284c7;
    border-color: #0284c7;
    color: #ffffff;
    box-shadow: 0 2px 4px rgba(2, 132, 199, 0.25);
}
.role-filter-chip.active .chip-count {
    background: rgba(255, 255, 255, 0.25);
    color: #ffffff;
}
.chip-count {
    font-size: 10.5px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 10px;
    background: #f1f5f9;
    color: #64748b;
    transition: all 0.15s;
}

/* Sütun Gizleme / Gösterme Sınıfları */
body.hide-col-slug .col-slug { display: none !important; }
body.hide-col-group .col-group { display: none !important; }
body.hide-col-color .col-color { display: none !important; }
body.hide-col-perms .col-perms { display: none !important; }
body.hide-col-users .col-users { display: none !important; }
body.hide-col-status .col-status { display: none !important; }

/* Modern Sütun Yönetim Menüsü */
.col-visibility-dropdown-wrap {
    position: relative;
}

.btn-modern-col-toggle {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 13px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    font-size: 12px;
    font-weight: 650;
    color: #334155;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.btn-modern-col-toggle:hover {
    background: #f8fafc;
    border-color: #0284c7;
    color: #0284c7;
    box-shadow: 0 3px 10px rgba(2, 132, 199, 0.12);
    transform: translateY(-1px);
}

.btn-modern-col-toggle.active-menu {
    background: #f0f9ff;
    border-color: #0284c7;
    color: #0284c7;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
}

.col-btn-count-badge {
    background: #e2e8f0;
    color: #475569;
    font-size: 10.5px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 10px;
    letter-spacing: 0.3px;
    transition: all 0.2s;
}

.btn-modern-col-toggle.active-menu .col-btn-count-badge,
.btn-modern-col-toggle:hover .col-btn-count-badge {
    background: #bae6fd;
    color: #0369a1;
}

.col-btn-chevron {
    transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.btn-modern-col-toggle.active-menu .col-btn-chevron {
    transform: rotate(180deg);
}

/* Modern Popover */
.role-col-visibility-popover {
    position: absolute;
    right: 0;
    top: calc(100% + 8px);
    z-index: 1000;
    width: 270px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.12), 0 8px 10px -6px rgba(15, 23, 42, 0.08);
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    animation: colMenuSlideDown 0.18s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes colMenuSlideDown {
    from {
        opacity: 0;
        transform: translateY(-6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.role-popover-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 2px 4px 8px 4px;
    border-bottom: 1px solid #f1f5f9;
    margin-bottom: 4px;
}

.role-popover-title {
    font-size: 11px;
    font-weight: 800;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.role-popover-reset-btn {
    font-size: 11px;
    color: #0284c7;
    font-weight: 650;
    background: none;
    border: none;
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 4px;
    transition: background 0.15s;
}

.role-popover-reset-btn:hover {
    background: #f0f9ff;
    text-decoration: underline;
}

.col-toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 7px 10px;
    border-radius: 8px;
    font-size: 12.5px;
    color: #334155;
    cursor: pointer;
    user-select: none;
    transition: all 0.15s;
}

.col-toggle-row:hover {
    background: #f8fafc;
}

.col-toggle-info {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 550;
}

/* Modern iOS Style Mini Toggle Switch */
.col-switch {
    position: relative;
    display: inline-block;
    width: 32px;
    height: 18px;
    flex-shrink: 0;
}

.col-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.col-slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #cbd5e1;
    transition: 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    border-radius: 18px;
}

.col-slider:before {
    position: absolute;
    content: "";
    height: 14px;
    width: 14px;
    left: 2px;
    bottom: 2px;
    background-color: #ffffff;
    transition: 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.col-switch input:checked + .col-slider {
    background-color: #0284c7;
}

.col-switch input:checked + .col-slider:before {
    transform: translateX(14px);
}

.role-popover-footer {
    padding-top: 8px;
    margin-top: 4px;
    border-top: 1px dashed #f1f5f9;
    text-align: center;
    font-size: 10.5px;
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}

/* Kullanıcı & İzin Listeleme Butonları */
.btn-role-modal-trigger {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 7px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    color: #334155;
    transition: all 0.15s ease;
    text-decoration: none;
    line-height: 1.2;
}
.btn-role-modal-trigger:hover {
    background: #e0f2fe;
    border-color: #7dd3fc;
    color: #0369a1;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(2, 132, 199, 0.15);
}
.btn-perm-trigger {
    background: #e0f2fe;
    border-color: #bae6fd;
    color: #0284c7;
}
.btn-perm-trigger:hover {
    background: #bae6fd;
    border-color: #7dd3fc;
    color: #0369a1;
}
.btn-user-trigger {
    background: #f1f5f9;
    border-color: #e2e8f0;
    color: #334155;
}
.btn-user-trigger:hover {
    background: #e2e8f0;
    border-color: #cbd5e1;
    color: #0f172a;
}
.trigger-hint {
    font-size: 10px;
    opacity: 0.7;
    margin-left: 2px;
}
</style>

<div class="matrix-page-header">
    <div class="header-left">
        <h1 class="page-title">
            <span>🎭</span>
            <span>Rol & Etiket Renk Yönetimi</span>
        </h1>
        <p class="page-subtitle">Yetki matrisinde ve sistem kartlarında kullanılacak kullanıcı rolleri, etiket renkleri ve sorumluluk grupları.</p>
    </div>
    <div class="header-right">
        <a href="{{ route('admin.user_roles.index') }}" class="matrix-header-btn-secondary" title="Kullanıcı Rol Yetkilendirme Paneli">
            <span>👥</span>
            <span>Kullanıcı Rolleri</span>
        </a>
        <button type="button" class="matrix-header-btn-secondary" onclick="openManageGroupsModal()" title="Dinamik Rol Gruplarını Yönet">
            <span>🏷️</span>
            <span>Rol Grupları Yönetimi</span>
        </button>
        <a href="{{ route('admin.permission_matrix') }}" class="matrix-header-btn-secondary" title="Yetki Matrisini Görüntüle">
            <span>🛡️</span>
            <span>Yetki Matrisine Git</span>
        </a>
        <button type="button" class="matrix-header-btn-primary" onclick="openRoleModal('create')" title="Yeni Rol Ekle">
            <span>+</span>
            <span>Yeni Rol Ekle</span>
        </button>
    </div>
</div>
<!-- Matrisler & Roller Arası Hızlı Geçiş Sekmeleri -->
<div class="matrix-sister-tabs">
    <a href="{{ route('admin.roles.index') }}" class="matrix-tab-link active">
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

@if(session('success'))
    <div class="alert alert-success mb-4" style="padding: 12px 18px; border-radius: 10px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-size: 13.5px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
        <span>✓</span> <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger mb-4" style="padding: 12px 18px; border-radius: 10px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; font-size: 13.5px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
        <span>⚠️</span> <span>{{ session('error') }}</span>
    </div>
@endif

<!-- İstatistik Sayaçları -->
<div class="stats-grid mb-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
    <div class="card" style="padding: 18px 20px; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div class="text-xs text-muted font-weight-600" style="text-transform: uppercase; letter-spacing: 0.5px; font-size: 11px;">Toplam Rol Sayısı</div>
        <div style="font-size: 28px; font-weight: 800; color: #0f172a; margin-top: 4px;">{{ $stats['total'] }}</div>
        <div class="text-xs text-muted mt-1" style="font-size: 12px; color: #64748b;">Sistem ve özel saha rolleri</div>
    </div>
    <div class="card" style="padding: 18px 20px; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div class="text-xs text-muted font-weight-600" style="text-transform: uppercase; letter-spacing: 0.5px; font-size: 11px;">Korumalı Sistem Rolleri</div>
        <div style="font-size: 28px; font-weight: 800; color: #0284c7; margin-top: 4px;">{{ $stats['system'] }}</div>
        <div class="text-xs text-muted mt-1" style="font-size: 12px; color: #64748b;">Superadmin, Yönetim, Admin vb.</div>
    </div>
    <div class="card" style="padding: 18px 20px; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div class="text-xs text-muted font-weight-600" style="text-transform: uppercase; letter-spacing: 0.5px; font-size: 11px;">Özel & Saha Rolleri</div>
        <div style="font-size: 28px; font-weight: 800; color: #10b981; margin-top: 4px;">{{ $stats['custom'] }}</div>
        <div class="text-xs text-muted mt-1" style="font-size: 12px; color: #64748b;">Şantiye şefi, denetmen, mühendis vb.</div>
    </div>
</div>

<!-- AKILLI FİLTRE PANELİ (ANLIK ARAMA & ÇOKLU SEÇİM) -->
<div class="card mb-4" style="border: 1px solid #e2e8f0; border-radius: 14px; background: #ffffff; box-shadow: 0 2px 6px rgba(0,0,0,0.03); padding: 18px 22px;">
    <!-- Üst Satır: Canlı Arama Kutusu & Sayaç / Temizleme -->
    <div style="display: flex; gap: 14px; align-items: center; justify-content: space-between; flex-wrap: wrap; margin-bottom: 14px;">
        <div style="position: relative; flex: 1; min-width: 280px;">
            <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 15px; color: #94a3b8; pointer-events: none;">🔍</span>
            <input type="text" id="roleSearchInput" placeholder="Rol adı, sistem slug'ı (kod) veya açıklama ara..." 
                   style="width: 100%; height: 42px; padding: 0 38px 0 40px; border-radius: 10px; border: 1.5px solid #cbd5e1; font-size: 13.5px; background: #f8fafc; transition: all 0.2s; outline: none;"
                   oninput="filterRolesTable()" onfocus="this.style.background='#ffffff'; this.style.borderColor='#0284c7';" onblur="this.style.background='#f8fafc'; this.style.borderColor='#cbd5e1';">
            <button type="button" id="clearSearchBtn" onclick="clearRoleSearch()" title="Aramayı Temizle"
                    style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: #e2e8f0; border: none; width: 22px; height: 22px; border-radius: 50%; font-size: 12px; color: #475569; cursor: pointer; display: none; align-items: center; justify-content: center; line-height: 1;">✕</button>
        </div>

        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <div id="filterResultCountBadge" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 8px; background: #eff6ff; border: 1px solid #bfdbfe; font-size: 12.5px; font-weight: 700; color: #1e40af;">
                <span>🎯 Gösterilen:</span>
                <span id="visibleRoleCount">{{ count($roles) }}</span> / <span>{{ count($roles) }} Rol</span>
            </div>
            <button type="button" id="btnResetAllFilters" onclick="resetAllRoleFilters()" class="btn btn-sm btn-secondary" 
                    style="height: 38px; padding: 0 14px; font-size: 12px; font-weight: 600; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; border: 1px solid #cbd5e1; background: #ffffff; color: #475569;">
                <span>↺</span> Filtreleri Sıfırla
            </button>
        </div>
    </div>

    <!-- Alt Satır: Çoklu Seçim Çipleri (Multi-Select Chips - Satır Satır Düzen) -->
    <div style="display: flex; flex-direction: column; gap: 11px; border-top: 1px dashed #e2e8f0; padding-top: 14px;">
        <!-- 1. Rol Grubu Çoklu Seçim -->
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <span style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; min-width: 95px; flex-shrink: 0;">Rol Grubu:</span>
            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                <button type="button" class="role-filter-chip active" data-filter-type="group" data-value="all" onclick="toggleRoleFilter('group', 'all')">
                    <span>Tüm Gruplar</span>
                    <span class="badge" style="background: rgba(0,0,0,0.06); color: inherit; padding: 1px 6px; border-radius: 10px; font-size: 11px;">{{ count($roles) }}</span>
                </button>
                @foreach($roleGroups as $rg)
                    @php $rgCount = collect($roles)->where('role_group', $rg->slug)->count(); @endphp
                    <button type="button" class="role-filter-chip" data-filter-type="group" data-value="{{ $rg->slug }}" onclick="toggleRoleFilter('group', '{{ $rg->slug }}')">
                        <span>{{ $rg->icon }} {{ $rg->name }}</span>
                        <span class="badge" style="background: rgba(0,0,0,0.06); color: inherit; padding: 1px 6px; border-radius: 10px; font-size: 11px;">{{ $rgCount }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- 2. Durum Çoklu Seçim -->
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <span style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; min-width: 95px; flex-shrink: 0;">Durum:</span>
            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                <button type="button" class="role-filter-chip active" data-filter-type="status" data-value="all" onclick="toggleRoleFilter('status', 'all')">
                    Tümü
                </button>
                <button type="button" class="role-filter-chip" data-filter-type="status" data-value="system" onclick="toggleRoleFilter('status', 'system')">
                    🔒 Korumalı Sistem ({{ $stats['system'] }})
                </button>
                <button type="button" class="role-filter-chip" data-filter-type="status" data-value="custom" onclick="toggleRoleFilter('status', 'custom')">
                    ⭐ Özel Roller ({{ $stats['custom'] }})
                </button>
            </div>
        </div>

        <!-- 3. İzin Durumu Çoklu Seçim -->
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <span style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; min-width: 95px; flex-shrink: 0;">İzin Durumu:</span>
            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                <button type="button" class="role-filter-chip active" data-filter-type="perm" data-value="all" onclick="toggleRoleFilter('perm', 'all')">
                    Tümü
                </button>
                <button type="button" class="role-filter-chip" data-filter-type="perm" data-value="with_perms" onclick="toggleRoleFilter('perm', 'with_perms')">
                    🔑 İzinli (> 0)
                </button>
                <button type="button" class="role-filter-chip" data-filter-type="perm" data-value="no_perms" onclick="toggleRoleFilter('perm', 'no_perms')">
                    ⚪ İzinsiz (0 İzin)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Rol Listesi Tablosu -->
<div class="card" style="border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; background: #ffffff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03);">
    <div class="card-header d-flex justify-between align-center" style="padding: 16px 22px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <h3 class="card-title" style="margin: 0; font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
            <span>📋</span> Tanımlı Roller ve Etiket Renkleri
        </h3>
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <!-- Modern Sütun Gizleme / Gösterme Menüsü (Excel Modeli & LocalStorage Kalıcı) -->
            <div class="col-visibility-dropdown-wrap" style="position: relative;">
                <button type="button" class="btn-modern-col-toggle" id="btnToggleRoleColsMenu" onclick="toggleRoleColumnVisibilityMenu(event)" title="Tablo Sütunlarını Yönet">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #0284c7;">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="9" y1="3" x2="9" y2="21"></line>
                        <line x1="15" y1="3" x2="15" y2="21"></line>
                    </svg>
                    <span>Sütunları Yönet</span>
                    <span id="activeColCountBadge" class="col-btn-count-badge">6/6</span>
                    <svg class="col-btn-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: #64748b; margin-left: 2px;">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div id="roleColVisibilityMenu" class="role-col-visibility-popover" style="display: none;">
                    <div class="role-popover-header">
                        <div class="role-popover-title">
                            <span>📊</span>
                            <span>Tablo Sütunları</span>
                        </div>
                        <button type="button" class="role-popover-reset-btn" onclick="resetRoleColsDefault(event)">
                            Tümünü Aç
                        </button>
                    </div>

                    <label class="col-toggle-row">
                        <span class="col-toggle-info">
                            <span>🏷️</span> Sistem Slug'ı
                        </span>
                        <div class="col-switch">
                            <input type="checkbox" id="chk_col_slug" checked onchange="toggleRoleCol('slug', this.checked)">
                            <span class="col-slider"></span>
                        </div>
                    </label>

                    <label class="col-toggle-row">
                        <span class="col-toggle-info">
                            <span>👥</span> Rol Grubu
                        </span>
                        <div class="col-switch">
                            <input type="checkbox" id="chk_col_group" checked onchange="toggleRoleCol('group', this.checked)">
                            <span class="col-slider"></span>
                        </div>
                    </label>

                    <label class="col-toggle-row">
                        <span class="col-toggle-info">
                            <span>🎨</span> Renk Kodu
                        </span>
                        <div class="col-switch">
                            <input type="checkbox" id="chk_col_color" checked onchange="toggleRoleCol('color', this.checked)">
                            <span class="col-slider"></span>
                        </div>
                    </label>

                    <label class="col-toggle-row">
                        <span class="col-toggle-info">
                            <span>🔑</span> İzin Sayısı
                        </span>
                        <div class="col-switch">
                            <input type="checkbox" id="chk_col_perms" checked onchange="toggleRoleCol('perms', this.checked)">
                            <span class="col-slider"></span>
                        </div>
                    </label>

                    <label class="col-toggle-row">
                        <span class="col-toggle-info">
                            <span>👤</span> Kullanıcı Sayısı
                        </span>
                        <div class="col-switch">
                            <input type="checkbox" id="chk_col_users" checked onchange="toggleRoleCol('users', this.checked)">
                            <span class="col-slider"></span>
                        </div>
                    </label>

                    <label class="col-toggle-row">
                        <span class="col-toggle-info">
                            <span>🔒</span> Durum Rozeti
                        </span>
                        <div class="col-switch">
                            <input type="checkbox" id="chk_col_status" checked onchange="toggleRoleCol('status', this.checked)">
                            <span class="col-slider"></span>
                        </div>
                    </label>

                    <div class="role-popover-footer">
                        <span>💾</span>
                        <span>Seçimleriniz otomatik hatırlanır</span>
                    </div>
                </div>
            </div>

            <span class="badge" style="background: #e2e8f0; color: #334155; padding: 5px 12px; border-radius: 12px; font-size: 12px; font-weight: 700;">
                Toplam {{ count($roles) }} Rol
            </span>
        </div>
    </div>

    <div class="table-responsive" style="overflow-x: auto;">
        <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px; margin: 0;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #cbd5e1; color: #475569; font-size: 11.5px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">
                    <th class="col-name" style="padding: 14px 18px; text-align: left; min-width: 220px; white-space: nowrap;">Rol Adı & Önizleme</th>
                    <th class="col-slug" style="padding: 14px 14px; text-align: left; min-width: 140px; white-space: nowrap;">Sistem Slug'ı</th>
                    <th class="col-group" style="padding: 14px 14px; text-align: left; min-width: 170px; white-space: nowrap;">Rol Grubu</th>
                    <th class="col-color" style="padding: 14px 14px; text-align: center; min-width: 110px; white-space: nowrap;">Renk Kodu</th>
                    <th class="col-perms" style="padding: 14px 14px; text-align: center; min-width: 115px; white-space: nowrap;">İzin Sayısı</th>
                    <th class="col-users" style="padding: 14px 14px; text-align: center; min-width: 110px; white-space: nowrap;">Kullanıcı</th>
                    <th class="col-status" style="padding: 14px 14px; text-align: center; min-width: 110px; white-space: nowrap;">Durum</th>
                    <th class="col-actions" style="padding: 14px 18px; text-align: right; min-width: 140px; white-space: nowrap;">Aksiyonlar</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $role)
                    @php
                        $groupConfig = match($role->role_group) {
                            'yonetim' => ['title' => 'Yönetim & Sistem', 'icon' => '🏛️', 'bg' => '#fdf2f8', 'text' => '#9d174d', 'border' => '#fbcfe8'],
                            'proje' => ['title' => 'Proje & Saha', 'icon' => '🏗️', 'bg' => '#eff6ff', 'text' => '#1e40af', 'border' => '#bfdbfe'],
                            'denetim' => ['title' => 'Denetim & Kalite', 'icon' => '🛡️', 'bg' => '#f0fdf4', 'text' => '#166534', 'border' => '#bbf7d0'],
                            default => ['title' => 'Diğer Departman', 'icon' => '📦', 'bg' => '#f8fafc', 'text' => '#475569', 'border' => '#e2e8f0'],
                        };
                    @endphp
                    <tr class="role-table-row"
                        data-name="{{ mb_strtolower($role->name, 'UTF-8') }}"
                        data-slug="{{ mb_strtolower($role->slug, 'UTF-8') }}"
                        data-desc="{{ mb_strtolower($role->description ?? '', 'UTF-8') }}"
                        data-group="{{ $role->role_group ?? 'diger' }}"
                        data-is-system="{{ $role->is_system ? '1' : '0' }}"
                        data-perms-count="{{ (int)$role->permissions_count }}"
                        style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" 
                        onmouseover="this.style.background='#f8fafc'" 
                        onmouseout="this.style.background='#ffffff'">
                        
                        <!-- 1. Rol Adı & Önizleme -->
                        <td class="col-name" style="padding: 14px 18px; vertical-align: middle;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span class="matrix-role-header-badge" 
                                      style="background: {{ $role->badge_bg }}; color: {{ $role->badge_text }}; border: 1px solid {{ $role->badge_border }}; padding: 6px 14px; font-size: 12.5px; font-weight: 700; border-radius: 7px; white-space: nowrap; display: inline-block;">
                                    {{ $role->name }}
                                </span>
                            </div>
                            @if($role->description)
                                <div class="text-xs text-muted" style="margin-top: 5px; font-size: 11.5px; line-height: 1.4; color: #64748b; max-width: 320px;">
                                    {{ $role->description }}
                                </div>
                            @endif
                        </td>

                        <!-- 2. Sistem Slug'ı -->
                        <td class="col-slug" style="padding: 14px 14px; vertical-align: middle; white-space: nowrap;">
                            <code style="font-size: 11.5px; padding: 4px 8px; border-radius: 6px; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; font-family: 'JetBrains Mono', Consolas, monospace; white-space: nowrap;">
                                {{ $role->slug }}
                            </code>
                        </td>

                        <!-- 3. Rol Grubu (Düzenli, Asla Kırılmayan Rozet) -->
                        <td class="col-group" style="padding: 14px 14px; vertical-align: middle; white-space: nowrap;">
                            <span style="display: inline-flex; align-items: center; gap: 5px; padding: 5px 11px; border-radius: 8px; font-weight: 700; font-size: 11.5px; white-space: nowrap; background: {{ $groupConfig['bg'] }}; color: {{ $groupConfig['text'] }}; border: 1px solid {{ $groupConfig['border'] }};">
                                <span style="font-size: 12px; line-height: 1;">{{ $groupConfig['icon'] }}</span>
                                <span>{{ $groupConfig['title'] }}</span>
                            </span>
                        </td>

                        <!-- 4. Renk Kodu -->
                        <td class="col-color" style="padding: 14px 14px; text-align: center; vertical-align: middle; white-space: nowrap;">
                            <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; white-space: nowrap;">
                                <span style="display: inline-block; width: 14px; height: 14px; border-radius: 4px; background: {{ $role->color }}; border: 1px solid rgba(0,0,0,0.15); box-shadow: 0 1px 2px rgba(0,0,0,0.06);"></span>
                                <code style="font-size: 11px; font-weight: 700; color: #475569; font-family: monospace;">{{ $role->color }}</code>
                            </div>
                        </td>

                        <!-- 5. İzin Sayısı (Tıklanabilir Şık Modal Butonu) -->
                        <td class="col-perms" style="padding: 14px 14px; text-align: center; vertical-align: middle; white-space: nowrap;">
                            <button type="button" class="btn-role-modal-trigger btn-perm-trigger" onclick="openRolePermissionsModal({{ $role->id }})" title="Tanımlı izinleri modalda listele">
                                <span>🔑</span> <span>{{ $role->permissions_count }} İzin</span>
                                <span class="trigger-hint">↗</span>
                            </button>
                        </td>

                        <!-- 6. Kullanıcı Sayısı (Tıklanabilir Şık Modal Butonu) -->
                        <td class="col-users" style="padding: 14px 14px; text-align: center; vertical-align: middle; white-space: nowrap;">
                            <button type="button" class="btn-role-modal-trigger btn-user-trigger" onclick="openRoleUsersModal({{ $role->id }})" title="Atanmış kullanıcıları modalda listele">
                                <span>👤</span> <span>{{ $role->users_count }} Kişi</span>
                                <span class="trigger-hint">↗</span>
                            </button>
                        </td>

                        <!-- 7. Durum (Tek Satır, Asla Kırılmayan Rozet) -->
                        <td class="col-status" style="padding: 14px 14px; text-align: center; vertical-align: middle; white-space: nowrap;">
                            @if($role->is_system)
                                <span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 6px; font-size: 11.5px; font-weight: 700; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; white-space: nowrap;">
                                    <span>🔒</span> Sistem
                                </span>
                            @else
                                <span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 6px; font-size: 11.5px; font-weight: 700; background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; white-space: nowrap;">
                                    <span>⭐</span> Özel Rol
                                </span>
                            @endif
                        </td>

                        <!-- 8. Aksiyonlar (Düzgün Hizalı, Butonlar Sıkışmaz) -->
                        <td style="padding: 14px 18px; text-align: right; vertical-align: middle; white-space: nowrap;">
                            <div style="display: inline-flex; align-items: center; justify-content: flex-end; gap: 6px; white-space: nowrap;">
                                <button type="button" class="btn btn-sm" onclick="openRoleModal('edit', {{ json_encode($role) }})" 
                                        style="padding: 5px 11px; font-size: 11.5px; font-weight: 700; border-radius: 6px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; white-space: nowrap; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; transition: all 0.15s;"
                                        onmouseover="this.style.background='#dbeafe';" onmouseout="this.style.background='#eff6ff';">
                                    <span>🎨</span> Düzenle
                                </button>

                                @if(!$role->is_system && $role->users_count == 0)
                                    <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('\'{{ addslashes($role->name) }}\' rolünü silmek istediğinize emin misiniz?');" style="display: inline; margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm" 
                                                style="padding: 5px 11px; font-size: 11.5px; font-weight: 700; border-radius: 6px; background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; display: inline-flex; align-items: center; gap: 4px; cursor: pointer; white-space: nowrap; transition: all 0.15s;"
                                                onmouseover="this.style.background='#fecaca';" onmouseout="this.style.background='#fee2e2';">
                                            <span>🗑️</span> Sil
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach

                <!-- Eşleşmeyen Durum Satırı -->
                <tr id="noRolesFoundRow" style="display: none;">
                    <td colspan="8" style="padding: 40px 20px; text-align: center; color: #64748b;">
                        <div style="font-size: 32px; margin-bottom: 8px;">🔍</div>
                        <div style="font-size: 14.5px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">Aramanızla Eşleşen Rol Bulunamadı</div>
                        <div style="font-size: 12.5px; color: #64748b; margin-bottom: 14px;">Belirlediğiniz arama ve çoklu filtre kriterlerine uyan bir kayıt bulunamadı.</div>
                        <button type="button" class="btn btn-sm btn-primary" onclick="resetAllRoleFilters()" style="padding: 6px 16px; font-size: 12px; font-weight: 600; border-radius: 6px; cursor: pointer;">
                            Filtreleri Temizle
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ROL EKLEME / DÜZENLEME MODALI -->
<div id="roleModalBackdrop" class="custom-modal-backdrop">
    <div class="custom-modal-dialog" style="max-width: 580px; width: 100%;">
        <form id="roleForm" method="POST" action="{{ route('admin.roles.store') }}">
            @csrf
            <div id="methodSpoof"></div>

            <div class="custom-modal-header" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 18px 22px; display: flex; justify-content: space-between; align-items: center;">
                <h3 class="custom-modal-title" id="roleModalTitle" style="margin: 0; font-size: 15.5px; font-weight: 700; color: #ffffff; display: flex; align-items: center; gap: 8px;">
                    <span>🎭</span> Rol Tanımı & Etiket Rengi
                </h3>
                <button type="button" class="custom-modal-close" onclick="closeRoleModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">✕</button>
            </div>

            <div class="custom-modal-body" style="padding: 22px;">
                <!-- GRUP 1: 📋 TEMEL ROL BİLGİLERİ & SİSTEM KİMLİĞİ -->
                <div class="modal-form-group-card" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; margin-bottom: 16px;">
                    <div class="modal-group-header" style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                        <span>📋</span> 1. Rol Kimliği & Sistem Kodu
                    </div>

                    <div class="form-group mb-3">
                        <label class="modal-label" style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Rol Adı <span class="text-danger" style="color: #ef4444;">*</span></label>
                        <input type="text" name="name" id="roleNameInput" class="form-control" placeholder="Örn: Saha / Şantiye Şefi, Satın Alma..." required oninput="updateLivePreview()" style="height: 40px; font-size: 13px;">
                    </div>

                    <div class="form-group mb-0">
                        <label class="modal-label" style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Sistem Kodu (Slug)</label>
                        <input type="text" name="slug" id="roleSlugInput" class="form-control" placeholder="Otomatik üretilir (örrn: saha_sefi)" style="height: 40px; font-size: 13px;">
                        <div class="modal-field-helper" style="font-size: 11px; color: #64748b; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                            <span>ℹ️</span> Boş bırakırsanız rol adından otomatik oluşturulur. Sistem rollerinde değiştirilemez.
                        </div>
                    </div>
                </div>

                <!-- GRUP 2: 🏛️ MATRİS GRUBU & YETKİ KAPSAMI -->
                <div class="modal-form-group-card" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; margin-bottom: 16px;">
                    <div class="modal-group-header" style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                        <span>🏛️</span> 2. Matris Grubu & Yetki Kapsamı
                    </div>

                    <div class="form-group mb-3">
                        <label class="modal-label" style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Rol Grubu (Matris Filtresi) <span class="text-danger" style="color: #ef4444;">*</span></label>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <select name="role_group" id="roleGroupSelect" class="form-control" required style="height: 40px; font-size: 13px; flex: 1;">
                                @foreach($roleGroups as $rg)
                                    <option value="{{ $rg->slug }}" data-icon="{{ $rg->icon }}" data-color="{{ $rg->color }}">{{ $rg->icon }} {{ $rg->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-sm" onclick="openCreateGroupModal()" style="height: 40px; padding: 0 14px; font-size: 12px; font-weight: 700; border-radius: 8px; border: 1.5px solid #bfdbfe; background: #eff6ff; color: #1d4ed8; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; cursor: pointer; transition: all 0.15s;" onmouseover="this.style.background='#dbeafe';" onmouseout="this.style.background='#eff6ff';" title="Yeni Rol Grubu Tanımla">
                                <span>+</span> <span>Yeni Grup</span>
                            </button>
                        </div>
                        <div class="modal-field-helper" style="font-size: 11px; color: #64748b; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                            <span>💡</span> Yetki matrisi sayfasındaki filtreleme sekmelerinde bu rolün hangi kategoride listeleneceğini belirler.
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="modal-label" style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Görev Tanımı & Açıklama <span class="text-muted" style="font-weight: 500; font-size: 11px;">(Opsiyonel)</span></label>
                        <textarea name="description" id="roleDescInput" class="form-control" rows="2" placeholder="Rolün genel sorumluluk alanı veya açıklaması..." style="font-size: 12.5px;"></textarea>
                    </div>
                </div>

                <!-- GRUP 3: 🎨 ETİKET & ROZET RENGİ -->
                <div class="modal-form-group-card" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px;">
                    <div class="modal-group-header" style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                        <span>🎨</span> 3. Etiket & Rozet Rengi
                    </div>

                    <div class="form-group mb-3">
                        <label style="font-size: 11px; font-weight: 700; color: #64748b; margin-bottom: 6px; display: block; text-transform: uppercase;">Hızlı Renk Paleti:</label>
                        <div class="color-swatches-grid" style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <button type="button" class="color-swatch-btn" style="background: #dc2626; width: 26px; height: 26px; border-radius: 6px; border: 2px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.2); cursor: pointer;" data-color="#dc2626" title="Köksan Kırmızısı" onclick="selectColor('#dc2626')"></button>
                            <button type="button" class="color-swatch-btn" style="background: #ea580c; width: 26px; height: 26px; border-radius: 6px; border: 2px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.2); cursor: pointer;" data-color="#ea580c" title="Sıcak Turuncu" onclick="selectColor('#ea580c')"></button>
                            <button type="button" class="color-swatch-btn" style="background: #d97706; width: 26px; height: 26px; border-radius: 6px; border: 2px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.2); cursor: pointer;" data-color="#d97706" title="Kehribar Sarısı" onclick="selectColor('#d97706')"></button>
                            <button type="button" class="color-swatch-btn" style="background: #059669; width: 26px; height: 26px; border-radius: 6px; border: 2px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.2); cursor: pointer;" data-color="#059669" title="Zümrüt Yeşili" onclick="selectColor('#059669')"></button>
                            <button type="button" class="color-swatch-btn" style="background: #0d9488; width: 26px; height: 26px; border-radius: 6px; border: 2px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.2); cursor: pointer;" data-color="#0d9488" title="Camgöbeği" onclick="selectColor('#0d9488')"></button>
                            <button type="button" class="color-swatch-btn" style="background: #0284c7; width: 26px; height: 26px; border-radius: 6px; border: 2px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.2); cursor: pointer;" data-color="#0284c7" title="Fiori Mavisi" onclick="selectColor('#0284c7')"></button>
                            <button type="button" class="color-swatch-btn" style="background: #2563eb; width: 26px; height: 26px; border-radius: 6px; border: 2px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.2); cursor: pointer;" data-color="#2563eb" title="Derin Mavi" onclick="selectColor('#2563eb')"></button>
                            <button type="button" class="color-swatch-btn" style="background: #4f46e5; width: 26px; height: 26px; border-radius: 6px; border: 2px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.2); cursor: pointer;" data-color="#4f46e5" title="İndigo" onclick="selectColor('#4f46e5')"></button>
                            <button type="button" class="color-swatch-btn" style="background: #7c3aed; width: 26px; height: 26px; border-radius: 6px; border: 2px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.2); cursor: pointer;" data-color="#7c3aed" title="Ametist Moru" onclick="selectColor('#7c3aed')"></button>
                            <button type="button" class="color-swatch-btn" style="background: #475569; width: 26px; height: 26px; border-radius: 6px; border: 2px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.2); cursor: pointer;" data-color="#475569" title="Çelik Grisi" onclick="selectColor('#475569')"></button>
                        </div>
                    </div>

                    <!-- Özel HEX Renk Seçici -->
                    <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 12px;">
                        <input type="color" id="roleColorNative" value="#0284c7" oninput="selectColor(this.value)" style="width: 44px; height: 38px; padding: 2px; border-radius: 6px; border: 1px solid #cbd5e1; cursor: pointer;">
                        <input type="text" name="color" id="roleColorHex" class="form-control" value="#0284c7" maxlength="7" pattern="^#[0-9A-Fa-f]{6}$" required oninput="selectColor(this.value)" style="height: 38px; font-weight: 700; font-family: monospace; font-size: 13px;">
                    </div>

                    <!-- Canlı Rozet Önizleme Kutusu -->
                    <div style="background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 12px; text-align: center;">
                        <div style="font-size: 11px; color: #64748b; font-weight: 600; margin-bottom: 6px;">Yetki Matrisi Tablo Başlığı Canlı Önizlemesi:</div>
                        <span id="livePreviewBadge" style="background: rgba(2, 132, 199, 0.12); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.35); padding: 6px 14px; font-size: 12.5px; border-radius: 6px; font-weight: 700; display: inline-block;">
                            Örnek Rol Rozeti
                        </span>
                    </div>
                </div>
            </div>

            <div class="custom-modal-footer" style="padding: 14px 22px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeRoleModal()" style="padding: 7px 16px; font-weight: 600; border-radius: 6px; cursor: pointer;">İptal</button>
                <button type="submit" class="btn btn-primary" id="roleSubmitBtn" style="padding: 7px 20px; font-weight: 700; border-radius: 6px; cursor: pointer;">Kaydet</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 1. ROL GRUPLARI YÖNETİMİ MODALI (LİSTELE, DÜZENLE, SİL) -->
<!-- ========================================================================= -->
<div id="manageGroupsModalBackdrop" class="custom-modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 10050; align-items: center; justify-content: center; padding: 16px;">
    <div class="custom-modal-dialog" style="max-width: 680px; width: 100%; background: #ffffff; border-radius: 14px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden; display: flex; flex-direction: column; max-height: 90vh;">
        <div class="custom-modal-header" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 18px 22px; display: flex; justify-content: space-between; align-items: center;">
            <h3 class="custom-modal-title" style="margin: 0; font-size: 16px; font-weight: 700; color: #ffffff; display: flex; align-items: center; gap: 8px;">
                <span>🏷️</span> Dinamik Rol Grupları Yönetimi
            </h3>
            <button type="button" onclick="closeManageGroupsModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">✕</button>
        </div>
        
        <div class="custom-modal-body" style="padding: 20px; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <p style="margin: 0; font-size: 13px; color: #64748b;">
                    Sistemdeki rollerin gruplandırıldığı matris kategorileri. Yeni grup ekleyebilir veya mevcutları düzenleyebilirsiniz.
                </p>
                <button type="button" class="btn btn-sm btn-primary" onclick="openCreateGroupModal()" style="padding: 6px 14px; font-size: 12.5px; font-weight: 700; border-radius: 8px; display: inline-flex; align-items: center; gap: 5px; flex-shrink: 0; cursor: pointer;">
                    <span>+</span> Yeni Grup Ekle
                </button>
            </div>

            <div class="table-responsive" style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; text-align: left; color: #475569; font-size: 11.5px; font-weight: 700; text-transform: uppercase;">
                            <th style="padding: 10px 14px;">Grup Adı & İkon</th>
                            <th style="padding: 10px 14px;">Sistem Kodu (Slug)</th>
                            <th style="padding: 10px 14px; text-align: center;">Rol Sayısı</th>
                            <th style="padding: 10px 14px; text-align: center;">Durum</th>
                            <th style="padding: 10px 14px; text-align: right;">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody id="roleGroupsTableBody">
                        @foreach($roleGroups as $rg)
                            @php $rgRolesCount = collect($roles)->where('role_group', $rg->slug)->count(); @endphp
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px 14px; font-weight: 600; color: #0f172a;">
                                    <span style="font-size: 16px; margin-right: 6px;">{{ $rg->icon }}</span>
                                    <span>{{ $rg->name }}</span>
                                </td>
                                <td style="padding: 12px 14px;">
                                    <code style="background: #f1f5f9; padding: 2px 7px; border-radius: 5px; font-size: 11.5px; color: #475569;">{{ $rg->slug }}</code>
                                </td>
                                <td style="padding: 12px 14px; text-align: center;">
                                    <span style="background: #eff6ff; color: #1d4ed8; font-weight: 700; padding: 2px 8px; border-radius: 12px; font-size: 11.5px;">{{ $rgRolesCount }} Rol</span>
                                </td>
                                <td style="padding: 12px 14px; text-align: center;">
                                    @if($rg->is_system)
                                        <span style="font-size: 11px; font-weight: 700; color: #64748b; background: #f1f5f9; padding: 2px 7px; border-radius: 6px;">🔒 Sistem</span>
                                    @else
                                        <span style="font-size: 11px; font-weight: 700; color: #166534; background: #dcfce7; padding: 2px 7px; border-radius: 6px;">⭐ Özel</span>
                                    @endif
                                </td>
                                <td style="padding: 12px 14px; text-align: right; white-space: nowrap;">
                                    <button type="button" class="btn btn-xs btn-outline-secondary" onclick='openEditGroupModal(@json($rg))' style="padding: 4px 8px; font-size: 11px; border-radius: 6px; cursor: pointer;">
                                        ✏️ Düzenle
                                    </button>
                                    @if(!$rg->is_system && $rgRolesCount == 0)
                                        <button type="button" class="btn btn-xs btn-outline-danger" onclick="deleteRoleGroup({{ $rg->id }}, '{{ addslashes($rg->name) }}')" style="padding: 4px 8px; font-size: 11px; border-radius: 6px; cursor: pointer; margin-left: 4px; color: #dc2626; border-color: #fca5a5;">
                                            🗑️ Sil
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="custom-modal-footer" style="padding: 14px 22px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end;">
            <button type="button" class="btn btn-secondary" onclick="closeManageGroupsModal()" style="padding: 7px 18px; font-size: 13px; font-weight: 600; border-radius: 8px; cursor: pointer;">Kapat</button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 2. YENİ / DÜZENLE ROL GRUBU MODALI (AJAX DESTEKLİ) -->
<!-- ========================================================================= -->
<div id="groupFormModalBackdrop" class="custom-modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); z-index: 10100; align-items: center; justify-content: center; padding: 16px;">
    <div class="custom-modal-dialog" style="max-width: 480px; width: 100%; background: #ffffff; border-radius: 14px; box-shadow: 0 25px 30px -5px rgba(0, 0, 0, 0.25); overflow: hidden;">
        <form id="roleGroupForm" onsubmit="submitRoleGroupForm(event)">
            @csrf
            <input type="hidden" id="groupFormId" value="">

            <div class="custom-modal-header" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
                <h4 class="custom-modal-title" id="groupFormTitle" style="margin: 0; font-size: 15px; font-weight: 700; color: #ffffff; display: flex; align-items: center; gap: 8px;">
                    <span>🏷️</span> Yeni Rol Grubu Tanımla
                </h4>
                <button type="button" onclick="closeGroupFormModal()" style="background: none; border: none; color: #ffffff; opacity: 0.8; font-size: 18px; cursor: pointer;">✕</button>
            </div>

            <div class="custom-modal-body" style="padding: 20px;">
                <div class="form-group mb-3">
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Grup Adı <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="groupNameInput" class="form-control" placeholder="Örn: Finans & Satın Alma, Lojistik vb." required style="height: 38px; font-size: 13px;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 12px; margin-bottom: 14px;">
                    <div class="form-group mb-0">
                        <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">İkon (Emoji)</label>
                        <input type="text" id="groupIconInput" class="form-control" placeholder="💼" value="🏷️" style="height: 38px; font-size: 15px; text-align: center;">
                    </div>
                    <div class="form-group mb-0">
                        <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Sistem Kodu (Slug)</label>
                        <input type="text" id="groupSlugInput" class="form-control" placeholder="Otomatik (örn: finans)" style="height: 38px; font-size: 13px;">
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label style="font-size: 12px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">Açıklama / Kapsam</label>
                    <textarea id="groupDescInput" class="form-control" rows="2" placeholder="Bu gruba dahil edilecek rollerin sorumluluk alanı..." style="font-size: 12.5px; resize: none;"></textarea>
                </div>
            </div>

            <div class="custom-modal-footer" style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-secondary" onclick="closeGroupFormModal()" style="padding: 7px 14px; font-size: 12.5px; font-weight: 600; border-radius: 8px; cursor: pointer;">İptal</button>
                <button type="submit" id="btnSubmitGroup" class="btn btn-primary" style="padding: 7px 18px; font-size: 12.5px; font-weight: 700; border-radius: 8px; cursor: pointer; background: #0284c7; border-color: #0284c7;">Kaydet</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- ATANMIŞ KULLANICILAR MODALI (DİNAMİK, ŞIK, SABİT PENCERE & KAYDIRILABİLİR LİSTE) -->
<!-- ========================================================================= -->
<div id="roleUsersModalBackdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 10050; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #ffffff; border-radius: 14px; width: 100%; max-width: 560px; max-height: 85vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); border: 1px solid #e2e8f0;">
        <!-- Modal Başlık Çubuğu (Sabit) -->
        <div style="padding: 16px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-shrink: 0;">
            <div style="display: flex; align-items: center; gap: 10px; overflow: hidden;">
                <span id="userModalRoleBadge" class="matrix-role-header-badge" style="font-size: 13px; font-weight: 700; padding: 4px 10px; border-radius: 6px;">Rol</span>
                <div>
                    <h4 style="margin: 0; font-size: 15px; font-weight: 700; color: #0f172a; line-height: 1.2;">Atanmış Kullanıcılar</h4>
                    <p style="margin: 2px 0 0 0; font-size: 11.5px; color: #64748b;" id="userModalRoleSubtitle">Bu role sahip kullanıcılar listeleniyor</p>
                </div>
            </div>
            <button type="button" onclick="closeRoleUsersModal()" style="border: none; background: #e2e8f0; width: 28px; height: 28px; border-radius: 50%; font-size: 16px; color: #475569; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.15s;" onmouseover="this.style.background='#cbd5e1'" onmouseout="this.style.background='#e2e8f0'">&times;</button>
        </div>

        <!-- Arama ve Filtre Kutusu -->
        <div style="padding: 12px 20px; background: #ffffff; border-bottom: 1px solid #f1f5f9; flex-shrink: 0;">
            <div style="position: relative;">
                <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 13px;">🔍</span>
                <input type="text" id="modalUserSearchInput" placeholder="Kullanıcı, unvan veya bölüm ara..." 
                       style="width: 100%; height: 36px; padding: 0 12px 0 34px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 12.5px; outline: none; box-sizing: border-box;"
                       oninput="filterModalUserList(this.value)">
            </div>
        </div>

        <!-- Kaydırılabilir Kullanıcı Listesi (Scrollable Body) -->
        <div id="modalUsersListContainer" style="padding: 14px 20px; overflow-y: auto; max-height: 420px; display: flex; flex-direction: column; gap: 8px; flex: 1;">
            <!-- JS tarafından doldurulur -->
        </div>

        <!-- Modal Alt Çubuğu (Sabit) -->
        <div style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
            <span style="font-size: 12px; font-weight: 600; color: #64748b;" id="userModalFooterCount">Toplam 0 Kullanıcı</span>
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeRoleUsersModal()" style="padding: 6px 14px; font-size: 12px; font-weight: 600; border-radius: 7px; cursor: pointer;">Kapat</button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- TANIMLI İZİNLER MODALI (DİNAMİK, ŞIK, SABİT PENCERE & KAYDIRILABİLİR LİSTE) -->
<!-- ========================================================================= -->
<div id="rolePermissionsModalBackdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 10050; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #ffffff; border-radius: 14px; width: 100%; max-width: 640px; max-height: 85vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); border: 1px solid #e2e8f0;">
        <!-- Modal Başlık Çubuğu (Sabit) -->
        <div style="padding: 16px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-shrink: 0;">
            <div style="display: flex; align-items: center; gap: 10px; overflow: hidden;">
                <span id="permModalRoleBadge" class="matrix-role-header-badge" style="font-size: 13px; font-weight: 700; padding: 4px 10px; border-radius: 6px;">Rol</span>
                <div>
                    <h4 style="margin: 0; font-size: 15px; font-weight: 700; color: #0f172a; line-height: 1.2;">Tanımlı Yetki & İzinler</h4>
                    <p style="margin: 2px 0 0 0; font-size: 11.5px; color: #64748b;" id="permModalRoleSubtitle">Bu role verilmiş sistem izinleri listeleniyor</p>
                </div>
            </div>
            <button type="button" onclick="closeRolePermissionsModal()" style="border: none; background: #e2e8f0; width: 28px; height: 28px; border-radius: 50%; font-size: 16px; color: #475569; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.15s;" onmouseover="this.style.background='#cbd5e1'" onmouseout="this.style.background='#e2e8f0'">&times;</button>
        </div>

        <!-- Arama ve Filtre / Hızlı Aksiyon Kutusu -->
        <div style="padding: 12px 20px; background: #ffffff; border-bottom: 1px solid #f1f5f9; flex-shrink: 0; display: flex; flex-direction: column; gap: 9px;">
            <div style="position: relative;">
                <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 13px;">🔍</span>
                <input type="text" id="modalPermSearchInput" placeholder="İzin adı, açıklama veya kod (slug) ara..." 
                       style="width: 100%; height: 38px; padding: 0 34px 0 36px; border-radius: 8px; border: 1.5px solid #cbd5e1; font-size: 12.5px; outline: none; box-sizing: border-box; transition: all 0.2s;"
                       onfocus="this.style.borderColor='#0284c7';" onblur="this.style.borderColor='#cbd5e1';"
                       oninput="filterModalPermList(this.value)">
                <button type="button" id="modalPermSearchClearBtn" onclick="clearModalPermSearch()" title="Aramayı Temizle"
                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: #e2e8f0; border: none; width: 20px; height: 20px; border-radius: 50%; font-size: 11px; color: #475569; cursor: pointer; display: none; align-items: center; justify-content: center; line-height: 1;">✕</button>
            </div>
            <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11.5px; color: #64748b; padding: 0 2px;">
                <span id="modalPermGroupSummary" style="font-weight: 600;">Modül kategorileri yükleniyor...</span>
                <div style="display: inline-flex; align-items: center; gap: 6px;">
                    <button type="button" onclick="toggleAllPermGroups(true)" class="btn btn-sm" 
                            style="padding: 3px 10px; font-size: 11px; font-weight: 600; border-radius: 6px; background: #f8fafc; color: #334155; border: 1px solid #cbd5e1; cursor: pointer; transition: all 0.15s;"
                            onmouseover="this.style.background='#e2e8f0';" onmouseout="this.style.background='#f8fafc';">
                        📂 Tümünü Aç
                    </button>
                    <button type="button" onclick="toggleAllPermGroups(false)" class="btn btn-sm" 
                            style="padding: 3px 10px; font-size: 11px; font-weight: 600; border-radius: 6px; background: #f8fafc; color: #334155; border: 1px solid #cbd5e1; cursor: pointer; transition: all 0.15s;"
                            onmouseover="this.style.background='#e2e8f0';" onmouseout="this.style.background='#f8fafc';">
                        📁 Tümünü Kapat
                    </button>
                </div>
            </div>
        </div>

        <!-- Kaydırılabilir İzin Listesi (Scrollable Body & Akordiyon) -->
        <div id="modalPermsListContainer" style="padding: 14px 20px; overflow-y: auto; flex: 1 1 auto; min-height: 0; max-height: 60vh; display: flex; flex-direction: column; gap: 10px;">
            <!-- JS tarafından doldurulur -->
        </div>

        <!-- Modal Alt Çubuğu (Sabit) -->
        <div style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
            <a href="{{ route('admin.permission_matrix') }}" class="btn btn-secondary btn-sm" style="padding: 6px 14px; font-size: 12px; font-weight: 600; border-radius: 7px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                <span>🛡️</span> Yetki Matrisinde Düzenle
            </a>
            <button type="button" class="btn btn-primary btn-sm" onclick="closeRolePermissionsModal()" style="padding: 6px 16px; font-size: 12px; font-weight: 700; border-radius: 7px; cursor: pointer; background: #0284c7; border-color: #0284c7;">Tamam</button>
        </div>
    </div>
</div>

<script>
// Rol Grupları Yönetim Modalı Kontrolleri
function openManageGroupsModal() {
    const m = document.getElementById('manageGroupsModalBackdrop');
    if (m) {
        m.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeManageGroupsModal() {
    const m = document.getElementById('manageGroupsModalBackdrop');
    if (m) {
        m.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function openCreateGroupModal() {
    document.getElementById('groupFormId').value = '';
    document.getElementById('groupFormTitle').innerHTML = '<span>🏷️</span> Yeni Rol Grubu Tanımla';
    document.getElementById('groupNameInput').value = '';
    document.getElementById('groupIconInput').value = '🏷️';
    document.getElementById('groupSlugInput').value = '';
    document.getElementById('groupSlugInput').disabled = false;
    document.getElementById('groupDescInput').value = '';
    
    const m = document.getElementById('groupFormModalBackdrop');
    if (m) {
        m.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function openEditGroupModal(rg) {
    document.getElementById('groupFormId').value = rg.id;
    document.getElementById('groupFormTitle').innerHTML = '<span>✏️</span> Rol Grubunu Düzenle: ' + rg.name;
    document.getElementById('groupNameInput').value = rg.name;
    document.getElementById('groupIconInput').value = rg.icon || '🏷️';
    document.getElementById('groupSlugInput').value = rg.slug;
    document.getElementById('groupSlugInput').disabled = true; // Slug değiştirilemez
    document.getElementById('groupDescInput').value = rg.description || '';
    
    const m = document.getElementById('groupFormModalBackdrop');
    if (m) {
        m.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeGroupFormModal() {
    const m = document.getElementById('groupFormModalBackdrop');
    if (m) {
        m.style.display = 'none';
        if (!document.getElementById('manageGroupsModalBackdrop') || document.getElementById('manageGroupsModalBackdrop').style.display === 'none') {
            document.body.style.overflow = 'auto';
        }
    }
}

function submitRoleGroupForm(e) {
    e.preventDefault();
    const id = document.getElementById('groupFormId').value;
    const name = document.getElementById('groupNameInput').value.trim();
    const icon = document.getElementById('groupIconInput').value.trim() || '🏷️';
    const slug = document.getElementById('groupSlugInput').value.trim();
    const description = document.getElementById('groupDescInput').value.trim();
    const token = document.querySelector('input[name="_token"]').value;

    const btn = document.getElementById('btnSubmitGroup');
    btn.disabled = true;
    btn.textContent = 'Kaydediliyor...';

    const url = id ? `{{ url('admin/rol-gruplari') }}/${id}` : '{{ url('admin/rol-gruplari') }}';
    const method = id ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ name, icon, slug, description })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.textContent = 'Kaydet';
        if (data.success) {
            closeGroupFormModal();
            // Sayfayı yenileyerek filtreleri ve listeyi güncelle
            window.location.reload();
        } else {
            alert(data.message || 'Bir hata oluştu.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.textContent = 'Kaydet';
        alert('İşlem sırasında bir hata oluştu: ' + err);
    });
}

function deleteRoleGroup(id, name) {
    if (!confirm(`'${name}' rol grubunu silmek istediğinize emin misiniz?`)) return;
    const token = document.querySelector('input[name="_token"]').value;

    fetch(`{{ url('admin/rol-gruplari') }}/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Silinemedi.');
        }
    })
    .catch(err => {
        alert('Hata: ' + err);
    });
}
</script>
@endsection

@push('scripts')
<script>
// =========================================================================
// AKILLI FİLTRE VE ÇOKLU SEÇİM MOTORU (INSTANT MULTI-SELECT FILTER ENGINE)
// =========================================================================
const roleFilterState = {
    search: '',
    groups: new Set(['all']),
    status: new Set(['all']),
    perms: new Set(['all'])
};

function toggleRoleFilter(type, value) {
    let set;
    if (type === 'group') set = roleFilterState.groups;
    else if (type === 'status') set = roleFilterState.status;
    else if (type === 'perm') set = roleFilterState.perms;
    else return;

    if (value === 'all') {
        set.clear();
        set.add('all');
    } else {
        set.delete('all');
        if (set.has(value)) {
            set.delete(value);
            if (set.size === 0) set.add('all');
        } else {
            set.add(value);
        }
    }

    renderFilterChips();
    applyRoleFilters();
}

function renderFilterChips() {
    ['group', 'status', 'perm'].forEach(type => {
        let set = (type === 'group') ? roleFilterState.groups : (type === 'status' ? roleFilterState.status : roleFilterState.perms);
        document.querySelectorAll(`.role-filter-chip[data-filter-type="${type}"]`).forEach(btn => {
            const val = btn.getAttribute('data-value');
            if (set.has(val)) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
    });
}

function filterRolesTable() {
    const input = document.getElementById('roleSearchInput');
    roleFilterState.search = (input ? input.value : '').trim().toLowerCase();
    
    const clearBtn = document.getElementById('clearSearchBtn');
    if (clearBtn) {
        clearBtn.style.display = roleFilterState.search ? 'inline-flex' : 'none';
    }

    applyRoleFilters();
}

function clearRoleSearch() {
    const input = document.getElementById('roleSearchInput');
    if (input) {
        input.value = '';
        input.focus();
    }
    filterRolesTable();
}

function resetAllRoleFilters() {
    roleFilterState.search = '';
    roleFilterState.groups = new Set(['all']);
    roleFilterState.status = new Set(['all']);
    roleFilterState.perms = new Set(['all']);

    const input = document.getElementById('roleSearchInput');
    if (input) input.value = '';

    const clearBtn = document.getElementById('clearSearchBtn');
    if (clearBtn) clearBtn.style.display = 'none';

    renderFilterChips();
    applyRoleFilters();
}

function applyRoleFilters() {
    const rows = document.querySelectorAll('.role-table-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const name = row.getAttribute('data-name') || '';
        const slug = row.getAttribute('data-slug') || '';
        const desc = row.getAttribute('data-desc') || '';
        const group = row.getAttribute('data-group') || '';
        const isSystem = row.getAttribute('data-is-system') === '1';
        const permsCount = parseInt(row.getAttribute('data-perms-count') || '0', 10);

        // 1. Metin Arama Eşleşmesi
        let matchSearch = true;
        if (roleFilterState.search) {
            const q = roleFilterState.search;
            matchSearch = name.includes(q) || slug.includes(q) || desc.includes(q);
        }

        // 2. Rol Grubu Çoklu Seçim Eşleşmesi
        let matchGroup = true;
        if (!roleFilterState.groups.has('all')) {
            matchGroup = roleFilterState.groups.has(group);
        }

        // 3. Durum Filtresi Eşleşmesi
        let matchStatus = true;
        if (!roleFilterState.status.has('all')) {
            const hasSystem = roleFilterState.status.has('system');
            const hasCustom = roleFilterState.status.has('custom');
            if (hasSystem && !hasCustom) matchStatus = isSystem;
            else if (!hasSystem && hasCustom) matchStatus = !isSystem;
            else if (hasSystem && hasCustom) matchStatus = true;
        }

        // 4. İzin Durumu Eşleşmesi
        let matchPerm = true;
        if (!roleFilterState.perms.has('all')) {
            const hasWith = roleFilterState.perms.has('with_perms');
            const hasNo = roleFilterState.perms.has('no_perms');
            if (hasWith && !hasNo) matchPerm = permsCount > 0;
            else if (!hasWith && hasNo) matchPerm = permsCount === 0;
            else if (hasWith && hasNo) matchPerm = true;
        }

        if (matchSearch && matchGroup && matchStatus && matchPerm) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const visibleCountEl = document.getElementById('visibleRoleCount');
    if (visibleCountEl) visibleCountEl.textContent = visibleCount;

    const noRows = document.getElementById('noRolesFoundRow');
    if (noRows) {
        noRows.style.display = (visibleCount === 0) ? '' : 'none';
    }
}

// =========================================================================
// ROL EKLEME / DÜZENLEME MODAL FONKSİYONLARI
// =========================================================================
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
            btn.style.outline = '3px solid #0284c7';
            btn.style.outlineOffset = '2px';
        } else {
            btn.style.outline = 'none';
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

// Modal dışına tıklandığında kapatma
document.getElementById('roleModalBackdrop').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRoleModal();
    }
});

// =========================================================================
// ROL VERİLERİ (KULLANICILAR & İZİNLER - İSTEMCİ TARAFLI HIZLI MODAL)
// =========================================================================
const rolesData = {
@foreach($roles as $role)
    {{ $role->id }}: {
        id: {{ $role->id }},
        name: @json($role->name),
        slug: @json($role->slug),
        color: @json($role->color),
        badge_bg: @json($role->badge_bg),
        badge_text: @json($role->badge_text),
        badge_border: @json($role->badge_border),
        group_title: @json($role->group_title ?? 'Diğer'),
        users_count: {{ $role->users_count }},
        permissions_count: {{ $role->permissions_count }},
        users: [
            @foreach($role->users as $u)
            {
                id: {{ $u->id }},
                name: @json($u->getMaskedNameFor(auth()->user())),
                email: @json($u->email),
                unvan: @json($u->unvan ?? '-'),
                bolum: @json($u->bolum?->ad ?? '-'),
                is_active: {{ $u->is_active ? 'true' : 'false' }}
            },
            @endforeach
        ],
        permissions: [
            @foreach($role->permissions as $perm)
            {
                id: {{ $perm->id }},
                name: @json($perm->name),
                slug: @json($perm->slug),
                group: @json($perm->group_name ?? 'Genel Sistem'),
                description: @json($perm->description ?? '')
            },
            @endforeach
        ]
    },
@endforeach
};

let currentModalRoleId = null;

// =========================================================================
// SÜTUN GİZLEME / GÖSTERME (EXCEL MODELİ & LOCALSTORAGE KALICILIĞI)
// =========================================================================
function toggleRoleColumnVisibilityMenu(e) {
    if (e) e.stopPropagation();
    const menu = document.getElementById('roleColVisibilityMenu');
    const btn = document.getElementById('btnToggleRoleColsMenu');
    if (!menu) return;
    const isShowing = menu.style.display !== 'none';
    if (isShowing) {
        menu.style.display = 'none';
        btn?.classList.remove('active-menu');
    } else {
        menu.style.display = 'flex';
        btn?.classList.add('active-menu');
    }
}

function updateActiveColBadge() {
    const cols = ['slug', 'group', 'color', 'perms', 'users', 'status'];
    let visibleCount = 0;
    cols.forEach(colKey => {
        const chk = document.getElementById('chk_col_' + colKey);
        if (chk && chk.checked) visibleCount++;
    });
    const badge = document.getElementById('activeColCountBadge');
    if (badge) {
        badge.textContent = `${visibleCount}/${cols.length}`;
        if (visibleCount < cols.length) {
            badge.style.background = '#fef3c7';
            badge.style.color = '#b45309';
        } else {
            badge.style.background = '#e2e8f0';
            badge.style.color = '#475569';
        }
    }
}

function toggleRoleCol(colKey, isVisible) {
    const className = 'hide-col-' + colKey;
    if (isVisible) {
        document.body.classList.remove(className);
        localStorage.setItem('koksan_roles_col_' + colKey, '1');
    } else {
        document.body.classList.add(className);
        localStorage.setItem('koksan_roles_col_' + colKey, '0');
    }
    updateActiveColBadge();
}

function resetRoleColsDefault(e) {
    if (e) e.stopPropagation();
    const cols = ['slug', 'group', 'color', 'perms', 'users', 'status'];
    cols.forEach(colKey => {
        const chk = document.getElementById('chk_col_' + colKey);
        if (chk) chk.checked = true;
        document.body.classList.remove('hide-col-' + colKey);
        localStorage.setItem('koksan_roles_col_' + colKey, '1');
    });
    updateActiveColBadge();
}

function initRoleColumnVisibility() {
    const cols = ['slug', 'group', 'color', 'perms', 'users', 'status'];
    cols.forEach(colKey => {
        const saved = localStorage.getItem('koksan_roles_col_' + colKey);
        const chk = document.getElementById('chk_col_' + colKey);
        if (saved === '0') {
            document.body.classList.add('hide-col-' + colKey);
            if (chk) chk.checked = false;
        } else {
            document.body.classList.remove('hide-col-' + colKey);
            if (chk) chk.checked = true;
        }
    });
    updateActiveColBadge();
}

// Menü dışına tıklandığında kapatma
document.addEventListener('click', function(e) {
    const menu = document.getElementById('roleColVisibilityMenu');
    const btn = document.getElementById('btnToggleRoleColsMenu');
    if (menu && menu.style.display !== 'none') {
        if (!menu.contains(e.target) && (!btn || !btn.contains(e.target))) {
            menu.style.display = 'none';
            btn?.classList.remove('active-menu');
        }
    }
});

// =========================================================================
// ATANMIŞ KULLANICILAR MODALI FONKSİYONLARI
// =========================================================================
function openRoleUsersModal(roleId) {
    const role = rolesData[roleId];
    if (!role) return;

    currentModalRoleId = roleId;

    const badge = document.getElementById('userModalRoleBadge');
    badge.textContent = role.name;
    badge.style.background = role.badge_bg;
    badge.style.color = role.badge_text;
    badge.style.borderColor = role.badge_border;

    document.getElementById('userModalRoleSubtitle').textContent = `${role.name} rolüne atanmış ${role.users.length} kullanıcı`;
    document.getElementById('modalUserSearchInput').value = '';

    renderModalUsersList(role.users);

    const m = document.getElementById('roleUsersModalBackdrop');
    if (m) {
        m.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeRoleUsersModal() {
    const m = document.getElementById('roleUsersModalBackdrop');
    if (m) {
        m.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function filterModalUserList(query) {
    if (!currentModalRoleId) return;
    const role = rolesData[currentModalRoleId];
    if (!role) return;

    const q = (query || '').toLowerCase().trim();
    if (!q) {
        renderModalUsersList(role.users);
        return;
    }

    const filtered = role.users.filter(u => 
        (u.name && u.name.toLowerCase().includes(q)) ||
        (u.email && u.email.toLowerCase().includes(q)) ||
        (u.unvan && u.unvan.toLowerCase().includes(q)) ||
        (u.bolum && u.bolum.toLowerCase().includes(q))
    );

    renderModalUsersList(filtered);
}

function renderModalUsersList(users) {
    const container = document.getElementById('modalUsersListContainer');
    const countEl = document.getElementById('userModalFooterCount');
    if (!container) return;

    if (countEl) countEl.textContent = `Toplam: ${users.length} Kullanıcı`;

    if (users.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 36px 16px; color: #94a3b8; background: #f8fafc; border-radius: 10px; border: 1px dashed #cbd5e1;">
                <span style="font-size: 28px; display: block; margin-bottom: 8px;">👤</span>
                <strong style="color: #475569; font-size: 13px;">Eşleşen Kullanıcı Bulunamadı</strong>
                <p style="margin: 4px 0 0 0; font-size: 11.5px;">Bu role henüz atanmış kullanıcı bulunmuyor veya arama kriterine uymuyor.</p>
            </div>
        `;
        return;
    }

    let html = '';
    users.forEach(u => {
        const initials = u.name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
        html += `
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 14px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 9px; transition: all 0.15s;" onmouseover="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='#ffffff';">
                <div style="display: flex; align-items: center; gap: 12px; overflow: hidden;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 13px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid #bae6fd;">
                        ${initials}
                    </div>
                    <div style="overflow: hidden;">
                        <div style="font-weight: 700; font-size: 13px; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            ${escapeHtmlRoles(u.name)}
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; margin-top: 2px; font-size: 11.5px; color: #64748b; flex-wrap: wrap;">
                            <span style="display: inline-flex; align-items: center; gap: 3px;">🏢 ${escapeHtmlRoles(u.bolum)}</span>
                            <span>•</span>
                            <span style="display: inline-flex; align-items: center; gap: 3px;">💼 ${escapeHtmlRoles(u.unvan)}</span>
                        </div>
                    </div>
                </div>
                <div style="text-align: right; flex-shrink: 0; display: flex; flex-direction: column; align-items: flex-end; gap: 3px;">
                    <span style="font-size: 11px; color: #64748b; display: block; font-family: monospace;">${escapeHtmlRoles(u.email)}</span>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span class="badge" style="font-size: 10.5px; font-weight: 600; padding: 2px 7px; border-radius: 4px; ${u.is_active ? 'background: #dcfce7; color: #15803d;' : 'background: #fee2e2; color: #b91c1c;'}">
                            ${u.is_active ? 'Aktif' : 'Pasif'}
                        </span>
                        <a href="{{ url('admin/kullanici-rolleri') }}?q=${encodeURIComponent(u.email)}" class="btn btn-xs" style="font-size: 10.5px; font-weight: 700; padding: 2px 8px; border-radius: 5px; background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; text-decoration: none; display: inline-flex; align-items: center; gap: 3px;">
                            <span>⚡</span> <span>Yetkileri Düzenle</span>
                        </a>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

// =========================================================================
// TANIMLI İZİNLER MODALI FONKSİYONLARI
// =========================================================================
let permGroupStates = {}; // idx -> boolean (true: collapsed, false: expanded)

function clearModalPermSearch() {
    const input = document.getElementById('modalPermSearchInput');
    const btn = document.getElementById('modalPermSearchClearBtn');
    if (input) input.value = '';
    if (btn) btn.style.display = 'none';
    filterModalPermList('');
}

function togglePermGroup(idx) {
    const body = document.getElementById(`perm-items-${idx}`);
    const chevron = document.getElementById(`perm-chevron-${idx}`);
    if (!body) return;

    const isHidden = body.style.display === 'none';
    if (isHidden) {
        body.style.display = 'flex';
        if (chevron) chevron.style.transform = 'rotate(0deg)';
        permGroupStates[idx] = false;
    } else {
        body.style.display = 'none';
        if (chevron) chevron.style.transform = 'rotate(-90deg)';
        permGroupStates[idx] = true;
    }
}

function toggleAllPermGroups(expand) {
    const bodies = document.querySelectorAll('.perm-group-body');
    const chevrons = document.querySelectorAll('[id^="perm-chevron-"]');

    bodies.forEach((b, i) => {
        b.style.display = expand ? 'flex' : 'none';
        permGroupStates[i] = !expand;
    });

    chevrons.forEach(c => {
        c.style.transform = expand ? 'rotate(0deg)' : 'rotate(-90deg)';
    });
}

function openRolePermissionsModal(roleId) {
    const role = rolesData[roleId];
    if (!role) return;

    currentModalRoleId = roleId;
    // Varsayılan olarak tüm modül akordiyonları KAPALI başlar
    // Her grubu kapalı olarak işaretle (renderModalPermsList'te kullanılır)
    permGroupStates = {};
    const grouped = {};
    role.permissions.forEach(p => {
        const grp = p.group || 'Genel Sistem';
        if (!grouped[grp]) grouped[grp] = true;
    });
    Object.keys(grouped).forEach((_, i) => {
        permGroupStates[i] = true; // true = collapsed
    });

    const badge = document.getElementById('permModalRoleBadge');
    badge.textContent = role.name;
    badge.style.background = role.badge_bg;
    badge.style.color = role.badge_text;
    badge.style.borderColor = role.badge_border;

    document.getElementById('permModalRoleSubtitle').textContent = `${role.name} rolüne atanmış ${role.permissions.length} sistem yetkisi`;
    const searchInput = document.getElementById('modalPermSearchInput');
    if (searchInput) searchInput.value = '';
    const clearBtn = document.getElementById('modalPermSearchClearBtn');
    if (clearBtn) clearBtn.style.display = 'none';

    renderModalPermsList(role.permissions, false);

    const m = document.getElementById('rolePermissionsModalBackdrop');
    if (m) {
        m.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeRolePermissionsModal() {
    const m = document.getElementById('rolePermissionsModalBackdrop');
    if (m) {
        m.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function filterModalPermList(query) {
    if (!currentModalRoleId) return;
    const role = rolesData[currentModalRoleId];
    if (!role) return;

    const q = (query || '').toLowerCase().trim();
    const clearBtn = document.getElementById('modalPermSearchClearBtn');
    if (clearBtn) {
        clearBtn.style.display = q.length > 0 ? 'inline-flex' : 'none';
    }

    if (!q) {
        renderModalPermsList(role.permissions, false);
        return;
    }

    const filtered = role.permissions.filter(p => 
        (p.name && p.name.toLowerCase().includes(q)) ||
        (p.slug && p.slug.toLowerCase().includes(q)) ||
        (p.group && p.group.toLowerCase().includes(q)) ||
        (p.description && p.description.toLowerCase().includes(q))
    );

    renderModalPermsList(filtered, true);
}

function renderModalPermsList(perms, isFiltered = false) {
    const container = document.getElementById('modalPermsListContainer');
    const summary = document.getElementById('modalPermGroupSummary');
    if (!container) return;

    if (perms.length === 0) {
        if (summary) summary.textContent = '0 İzin Bulundu';
        container.innerHTML = `
            <div style="text-align: center; padding: 36px 16px; color: #94a3b8; background: #f8fafc; border-radius: 10px; border: 1px dashed #cbd5e1;">
                <span style="font-size: 28px; display: block; margin-bottom: 8px;">🔍</span>
                <strong style="color: #475569; font-size: 13px;">Eşleşen İzin Bulunamadı</strong>
                <p style="margin: 4px 0 14px 0; font-size: 11.5px;">Arama kriterinize uygun herhangi bir sistem yetkisi bulunamadı.</p>
                <button type="button" onclick="clearModalPermSearch()" class="btn btn-secondary btn-sm" style="padding: 6px 14px; font-size: 12px; font-weight: 600; border-radius: 7px; cursor: pointer;">
                    Filtreyi Temizle
                </button>
            </div>
        `;
        return;
    }

    const grouped = {};
    perms.forEach(p => {
        const grp = p.group || 'Genel Sistem';
        if (!grouped[grp]) grouped[grp] = [];
        grouped[grp].push(p);
    });

    const groupKeys = Object.keys(grouped);
    if (summary) {
        summary.textContent = `${groupKeys.length} Kategori • Toplam ${perms.length} Sistem İzni`;
    }

    let html = '';
    let idx = 0;
    for (let grpName in grouped) {
        const items = grouped[grpName];
        const isCollapsed = isFiltered ? false : (permGroupStates[idx] === true);
        const chevronTransform = isCollapsed ? 'rotate(-90deg)' : 'rotate(0deg)';
        const bodyDisplay = isCollapsed ? 'none' : 'flex';

        html += `
            <div class="perm-group-card" style="border: 1.5px solid #e2e8f0; border-radius: 10px; overflow: hidden; background: #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.02); flex-shrink: 0;">
                <!-- Tıklanabilir Akordiyon Başlığı -->
                <div class="perm-group-header" onclick="togglePermGroup(${idx})" 
                     style="padding: 10px 14px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; cursor: pointer; user-select: none; transition: background 0.15s; flex-shrink: 0;"
                     onmouseover="this.style.background='#f1f5f9';" onmouseout="this.style.background='#f8fafc';"
                     title="Bu kategorideki izinleri aç / kapat">
                    <div style="display: flex; align-items: center; gap: 9px;">
                        <span id="perm-chevron-${idx}" style="display: inline-block; font-size: 10.5px; color: #64748b; transition: transform 0.2s ease; transform: ${chevronTransform}; line-height: 1;">▼</span>
                        <span style="font-size: 13px; font-weight: 750; color: #1e293b;">📁 ${escapeHtmlRoles(grpName)}</span>
                    </div>
                    <span class="badge" style="background: #e2e8f0; color: #334155; font-size: 11.5px; font-weight: 700; padding: 2px 8px; border-radius: 10px; border: 1px solid #cbd5e1;">
                        ${items.length} İzin
                    </span>
                </div>

                <!-- İzin Detayları Listesi (Tüm Liste Eksiksiz Görünür) -->
                <div id="perm-items-${idx}" class="perm-group-body" style="padding: 7px 9px; display: ${bodyDisplay}; flex-direction: column; gap: 5px; background: #ffffff; flex-shrink: 0;">
        `;

        items.forEach(p => {
            html += `
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 7px 10px; border-radius: 7px; background: #f8fafc; border: 1px solid #f1f5f9; transition: all 0.12s ease; flex-shrink: 0;" 
                     onmouseover="this.style.background='#f1f5f9'; this.style.borderColor='#cbd5e1';" 
                     onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#f1f5f9';">
                    <div style="display: flex; align-items: center; gap: 10px; overflow: hidden; flex: 1; padding-right: 10px;">
                        <span style="color: #16a34a; font-weight: 900; font-size: 14px; flex-shrink: 0; line-height: 1;">✓</span>
                        <div style="overflow: hidden;">
                            <span style="font-weight: 650; font-size: 12.5px; color: #0f172a; display: block; line-height: 1.35;">${escapeHtmlRoles(p.name)}</span>
                            ${p.description ? `<p style="margin: 2px 0 0 0; font-size: 11px; color: #64748b; line-height: 1.3;">${escapeHtmlRoles(p.description)}</p>` : ''}
                        </div>
                    </div>
                    <code style="font-size: 11px; background: #ffffff; color: #334155; border: 1px solid #cbd5e1; padding: 2.5px 7px; border-radius: 5px; font-family: 'JetBrains Mono', Consolas, monospace; font-weight: 600; flex-shrink: 0; white-space: nowrap; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                        ${escapeHtmlRoles(p.slug)}
                    </code>
                </div>
            `;
        });

        html += `
                </div>
            </div>
        `;
        idx++;
    }

    container.innerHTML = html;
}

function escapeHtmlRoles(text) {
    if (!text) return '';
    return text.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

document.getElementById('roleUsersModalBackdrop')?.addEventListener('click', function(e) {
    if (e.target === this) closeRoleUsersModal();
});
document.getElementById('rolePermissionsModalBackdrop')?.addEventListener('click', function(e) {
    if (e.target === this) closeRolePermissionsModal();
});

document.addEventListener('DOMContentLoaded', function() {
    initRoleColumnVisibility();
});
</script>
@endpush