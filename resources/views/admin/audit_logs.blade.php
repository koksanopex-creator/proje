@extends('layouts.app')

@section('title', 'Sistem Denetim Logları & Görünürlük Matrisi')

@section('content')
<style>

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

.excel-dropdown-group {
    position: relative;
}
.excel-filter-trigger {
    width: 100%;
    height: 38px;
    border-radius: 6px;
    padding: 0 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 12.5px;
    cursor: pointer;
    text-align: left;
    transition: all 0.2s ease;
    user-select: none;
}
.excel-filter-trigger:hover {
    border-color: #0284c7 !important;
}
.excel-dropdown-menu {
    animation: excelFadeIn 0.15s ease-out;
}
@keyframes excelFadeIn {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}
.excel-item-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 5px 8px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 12px;
    margin-bottom: 2px;
    transition: background 0.15s;
    user-select: none;
}
.excel-item-label:hover {
    background: #f1f5f9;
}
.excel-item-label input[type="checkbox"] {
    accent-color: #0284c7;
    cursor: pointer;
}
.active-filter-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 6px;
    padding: 3px 9px;
    font-size: 11.5px;
    font-weight: 600;
    transition: all 0.15s;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
.active-filter-pill button {
    border: none;
    background: none;
    cursor: pointer;
    padding: 0 2px;
    font-weight: 700;
    font-size: 11px;
    line-height: 1;
}
.active-filter-pill button:hover {
    opacity: 0.7;
}

.excel-dropdown-group {
    position: relative;
}
.excel-dropdown-menu {
    z-index: 9999 !important;
}
.excel-items-list {
    min-height: 50px;
    max-height: 230px;
    overflow-y: auto;
    padding: 4px 0;
}
.excel-empty-notice {
    text-align: center;
    padding: 14px 8px;
    font-size: 11.5px;
    color: #94a3b8;
    font-style: italic;
}

</style>

<div class="matrix-page-header">
    <div class="header-left">
        <h1 class="page-title">
            <span>📜</span>
            <span>Sistem Denetim Logları & Görünürlük Matrisi</span>
        </h1>
        <p class="page-subtitle">Her işlem kayıt altındadır. Çoklu Excel tarzı filtreler, kimlik maskeleme ve rol bazlı log görünürlük matrisi.</p>
    </div>
    <div class="header-right">
        <span class="info-pill" style="height: 38px; padding: 0 16px; border-radius: 20px; font-size: 12.5px; font-weight: 600; background: #ecfdf5; color: #065f46; border: 1.5px solid #a7f3d0; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
            <span class="dot-green"></span>
            <span>Madde 14 & 15: Kimlik Maskeleme & Çoklu Denetim Aktif</span>
        </span>
    </div>
</div>

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
    <a href="javascript:void(0)" onclick="switchLogTab('visibility')" id="sisterTabVisibility" class="matrix-tab-link active">
        <span class="tab-emoji">📋</span>
        <span>Log Görünürlük Matrisi</span>
    </a>
    <a href="{{ route('admin.system_health') }}" class="matrix-tab-link">
        <span class="tab-emoji">🩺</span>
        <span>Sistem Sağlığı</span>
    </a>
</div>

<!-- SEKME BAŞLIKLARI (CANLI LOG AKIŞI & GÖRÜNÜRLÜK MATRİSİ) -->
<div class="tabs-header mb-4" style="display: flex; gap: 10px; margin-bottom: 24px; padding: 6px; background: #f1f5f9; border-radius: 12px; width: fit-content;">
    <button class="tab-btn active" id="btnTabStream" onclick="switchLogTab('stream')" style="padding: 9px 20px; border-radius: 8px; font-weight: 700; font-size: 13px; border: none; cursor: pointer; background: #0284c7; color: #ffffff; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.25);">
        <span>⏱️ Canlı Log Akışı</span>
        <span class="badge" style="background: rgba(255,255,255,0.25); color: #fff; padding: 2px 8px; border-radius: 6px; font-size: 11px;">{{ $logs->total() }}</span>
    </button>
    <button class="tab-btn" id="btnTabVisibility" onclick="switchLogTab('visibility')" style="padding: 9px 20px; border-radius: 8px; font-weight: 700; font-size: 13px; border: none; cursor: pointer; background: transparent; color: #475569; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px;">
        <span>👁️ Log Görünürlük Matrisi</span>
        <span class="badge" style="background: #e2e8f0; color: #334155; padding: 2px 8px; border-radius: 6px; font-size: 11px;">Rol Bazlı İzinler</span>
    </button>
</div>

<!-- SEKME 1: CANLI LOG AKIŞI -->
<div id="tab-stream" class="tab-content-panel active">
    <!-- EXCEL TARZI ÇOKLU FİLTRELER VE ARAMA PANELİ -->
    <div class="card mb-3" style="border-radius: 10px; border: 1px solid #e2e8f0; background: #ffffff; box-shadow: 0 1px 4px rgba(0,0,0,0.03); overflow: visible !important;">
        <form method="GET" action="{{ route('admin.audit_logs') }}" id="logFilterForm" style="padding: 16px 18px; overflow: visible !important;">
            <!-- ÜST FİLTRE ÇUBUĞU (GRID) -->
            <div style="display: grid; grid-template-columns: 1.8fr 1.3fr 1.3fr 1.3fr 1.5fr auto; gap: 12px; align-items: end;">
                <!-- 1. Detay & Metin Arama -->
                <div class="form-group mb-0">
                    <label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 4px; display: block;">
                        🔍 Detay & Metin Arama
                    </label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Açıklama, IP, konu ara..." class="form-control text-xs" style="height: 38px; border-radius: 6px; font-size: 12.5px;">
                </div>

                <!-- 2. Kategori / Modül (Excel Çoklu Seçim) -->
                <div class="excel-dropdown-group">
                    <label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 4px; display: block;">
                        🏷️ Kategori / Modül
                    </label>
                    <button type="button" class="excel-filter-trigger" id="btnTriggerCategories" onclick="toggleExcelDropdown('dropdownCategories', event)" style="background: {{ count($selectedCategories) > 0 ? '#f0f9ff' : '#ffffff' }}; border: 1.5px solid {{ count($selectedCategories) > 0 ? '#0284c7' : '#cbd5e1' }};">
                        <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: {{ count($selectedCategories) > 0 ? '#0284c7' : '#334155' }}; font-weight: {{ count($selectedCategories) > 0 ? '700' : '500' }};">
                            {{ count($selectedCategories) > 0 ? count($selectedCategories) . ' Kategori Seçili' : '-- Tümü --' }}
                        </span>
                        <span style="font-size: 10px; color: #94a3b8; margin-left: 6px;">▼</span>
                    </button>

                    <!-- Popover Panel -->
                    <div id="dropdownCategories" class="excel-dropdown-menu" style="display: none; position: absolute; top: calc(100% + 4px); left: 0; min-width: 280px; max-width: 340px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; box-shadow: 0 12px 28px -4px rgba(0,0,0,0.25); z-index: 9999 !important; padding: 10px;">
                        <input type="text" placeholder="🔍 Kategori ara..." class="excel-search-input" oninput="filterExcelList(this, 'listCategories')" style="width: 100%; height: 32px; padding: 4px 8px; font-size: 11.5px; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 8px;">
                        <div style="display: flex; justify-content: space-between; padding: 0 2px 6px; border-bottom: 1px solid #f1f5f9; font-size: 11px;">
                            <a href="javascript:void(0)" onclick="excelSelectAll('categories', 'listCategories')" style="color: #0284c7; font-weight: 600; text-decoration: none;">✓ Tümünü Seç</a>
                            <a href="javascript:void(0)" onclick="excelDeselectAll('categories', 'listCategories')" style="color: #64748b; font-weight: 600; text-decoration: none;">✕ Temizle</a>
                        </div>
                        <div id="listCategories" class="excel-items-list" style="max-height: 200px; overflow-y: auto; padding: 4px 0;">
                            @forelse($categoryCatalog as $cKey => $cMeta)
                                @php
                                    $cCount = $categoryCounts[$cKey] ?? 0;
                                    $cTitle = is_array($cMeta) ? ($cMeta['title'] ?? $cKey) : $cMeta;
                                    $cIcon = is_array($cMeta) ? ($cMeta['icon'] ?? '🏷️') : '🏷️';
                                    $cBg = is_array($cMeta) ? ($cMeta['bg'] ?? '#f8fafc') : '#f8fafc';
                                    $cText = is_array($cMeta) ? ($cMeta['text'] ?? '#475569') : '#475569';
                                    $cBorder = is_array($cMeta) ? ($cMeta['border'] ?? '#cbd5e1') : '#cbd5e1';
                                @endphp
                                <label class="excel-item-label">
                                    <div style="display: flex; align-items: center; gap: 7px; overflow: hidden;">
                                        <input type="checkbox" name="categories[]" value="{{ $cKey }}" {{ in_array($cKey, $selectedCategories) ? 'checked' : '' }} onchange="updateTriggerText('Categories')">
                                        <span style="font-size: 11px; font-weight: 600; padding: 1.5px 6px; border-radius: 4px; background: {{ $cBg }}; color: {{ $cText }}; border: 1px solid {{ $cBorder }}; white-space: nowrap;">
                                            {{ $cIcon }} {{ $cTitle }}
                                        </span>
                                    </div>
                                    <span style="font-size: 10.5px; color: #94a3b8; margin-left: 6px; font-weight: 600;">{{ $cCount }}</span>
                                </label>
                            @empty
                                <div style="padding: 10px; font-size: 11.5px; color: #94a3b8; text-align: center;">Log kaydı bulunan kategori yok</div>
                            @endforelse
                        </div>
                        <div style="display: flex; justify-content: space-between; gap: 6px; padding-top: 8px; border-top: 1px solid #f1f5f9; margin-top: 4px;">
                            <button type="button" class="btn btn-xs btn-outline-secondary" onclick="closeAllExcelDropdowns()">Kapat</button>
                            <button type="submit" class="btn btn-xs btn-primary" style="padding: 4px 12px; font-weight: 600;">✓ Uygula</button>
                        </div>
                    </div>
                </div>

                <!-- 3. Aksiyon Türü (Excel Çoklu Seçim) -->
                <div class="excel-dropdown-group">
                    <label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 4px; display: block;">
                        ⚡ Aksiyon Türü
                    </label>
                    <button type="button" class="excel-filter-trigger" id="btnTriggerActions" onclick="toggleExcelDropdown('dropdownActions', event)" style="background: {{ count($selectedActions) > 0 ? '#f0f9ff' : '#ffffff' }}; border: 1.5px solid {{ count($selectedActions) > 0 ? '#0284c7' : '#cbd5e1' }};">
                        <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: {{ count($selectedActions) > 0 ? '#0284c7' : '#334155' }}; font-weight: {{ count($selectedActions) > 0 ? '700' : '500' }};">
                            {{ count($selectedActions) > 0 ? count($selectedActions) . ' Aksiyon Seçili' : '-- Tümü --' }}
                        </span>
                        <span style="font-size: 10px; color: #94a3b8; margin-left: 6px;">▼</span>
                    </button>

                    <!-- Popover Panel -->
                    <div id="dropdownActions" class="excel-dropdown-menu" style="display: none; position: absolute; top: calc(100% + 4px); left: 0; min-width: 280px; max-width: 340px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; box-shadow: 0 12px 28px -4px rgba(0,0,0,0.25); z-index: 9999 !important; padding: 10px;">
                        <input type="text" placeholder="🔍 Aksiyon ara..." class="excel-search-input" oninput="filterExcelList(this, 'listActions')" style="width: 100%; height: 32px; padding: 4px 8px; font-size: 11.5px; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 8px;">
                        <div style="display: flex; justify-content: space-between; padding: 0 2px 6px; border-bottom: 1px solid #f1f5f9; font-size: 11px;">
                            <a href="javascript:void(0)" onclick="excelSelectAll('actions', 'listActions')" style="color: #0284c7; font-weight: 600; text-decoration: none;">✓ Tümünü Seç</a>
                            <a href="javascript:void(0)" onclick="excelDeselectAll('actions', 'listActions')" style="color: #64748b; font-weight: 600; text-decoration: none;">✕ Temizle</a>
                        </div>
                        <div id="listActions" class="excel-items-list" style="max-height: 200px; overflow-y: auto; padding: 4px 0;">
                            @forelse($actionCatalog as $aKey => $aMeta)
                                @php $aCount = $actionCounts[$aKey] ?? 0; @endphp
                                <label class="excel-item-label">
                                    <div style="display: flex; align-items: center; gap: 7px; overflow: hidden;">
                                        <input type="checkbox" name="actions[]" value="{{ $aKey }}" {{ in_array($aKey, $selectedActions) ? 'checked' : '' }} onchange="updateTriggerText('Actions')">
                                        <span style="font-size: 11px; font-weight: 700; padding: 1.5px 6px; border-radius: 4px; background: {{ $aMeta['bg'] }}; color: {{ $aMeta['text'] }}; border: 1px solid {{ $aMeta['border'] }}; white-space: nowrap;">
                                            {{ $aMeta['icon'] ?? '⚡' }} {{ $aMeta['title'] }}
                                        </span>
                                    </div>
                                    <span style="font-size: 10.5px; color: #94a3b8; margin-left: 6px; font-weight: 600;">{{ $aCount }}</span>
                                </label>
                            @empty
                                <div style="padding: 10px; font-size: 11.5px; color: #94a3b8; text-align: center;">Log kaydı bulunan aksiyon türü yok</div>
                            @endforelse
                        </div>
                        <div style="display: flex; justify-content: space-between; gap: 6px; padding-top: 8px; border-top: 1px solid #f1f5f9; margin-top: 4px;">
                            <button type="button" class="btn btn-xs btn-outline-secondary" onclick="closeAllExcelDropdowns()">Kapat</button>
                            <button type="submit" class="btn btn-xs btn-primary" style="padding: 4px 12px; font-weight: 600;">✓ Uygula</button>
                        </div>
                    </div>
                </div>

                <!-- 4. İşlemi Yapan (Kullanıcı) (Excel Çoklu Seçim) -->
                <div class="excel-dropdown-group">
                    <label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 4px; display: block;">
                        👤 İşlemi Yapan
                    </label>
                    <button type="button" class="excel-filter-trigger" id="btnTriggerUsers" onclick="toggleExcelDropdown('dropdownUsers', event)" style="background: {{ count($selectedUserIds) > 0 ? '#f0f9ff' : '#ffffff' }}; border: 1.5px solid {{ count($selectedUserIds) > 0 ? '#0284c7' : '#cbd5e1' }};">
                        <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: {{ count($selectedUserIds) > 0 ? '#0284c7' : '#334155' }}; font-weight: {{ count($selectedUserIds) > 0 ? '700' : '500' }};">
                            {{ count($selectedUserIds) > 0 ? count($selectedUserIds) . ' Kullanıcı Seçili' : '-- Tümü --' }}
                        </span>
                        <span style="font-size: 10px; color: #94a3b8; margin-left: 6px;">▼</span>
                    </button>

                    <!-- Popover Panel -->
                    <div id="dropdownUsers" class="excel-dropdown-menu" style="display: none; position: absolute; top: calc(100% + 4px); left: 0; min-width: 280px; max-width: 340px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; box-shadow: 0 12px 28px -4px rgba(0,0,0,0.25); z-index: 9999 !important; padding: 10px;">
                        <input type="text" placeholder="🔍 Personel ara..." class="excel-search-input" oninput="filterExcelList(this, 'listUsers')" style="width: 100%; height: 32px; padding: 4px 8px; font-size: 11.5px; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 8px;">
                        <div style="display: flex; justify-content: space-between; padding: 0 2px 6px; border-bottom: 1px solid #f1f5f9; font-size: 11px;">
                            <a href="javascript:void(0)" onclick="excelSelectAll('user_ids', 'listUsers')" style="color: #0284c7; font-weight: 600; text-decoration: none;">✓ Tümünü Seç</a>
                            <a href="javascript:void(0)" onclick="excelDeselectAll('user_ids', 'listUsers')" style="color: #64748b; font-weight: 600; text-decoration: none;">✕ Temizle</a>
                        </div>
                        <div id="listUsers" class="excel-items-list" style="max-height: 200px; overflow-y: auto; padding: 4px 0;">
                            @forelse($allUsers as $u)
                                @php $uCount = $userCounts[$u->id] ?? 0; @endphp
                                <label class="excel-item-label">
                                    <div style="display: flex; align-items: center; gap: 7px; overflow: hidden;">
                                        <input type="checkbox" name="user_ids[]" value="{{ $u->id }}" {{ in_array($u->id, $selectedUserIds) ? 'checked' : '' }} onchange="updateTriggerText('Users')">
                                        <span style="color: #1e293b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $u->name }}</span>
                                    </div>
                                    <span style="font-size: 10.5px; color: #94a3b8; margin-left: 6px; font-weight: 600;">{{ $uCount }}</span>
                                </label>
                            @empty
                                <div style="padding: 10px; font-size: 11.5px; color: #94a3b8; text-align: center;">Log kaydı bulunan kullanıcı yok</div>
                            @endforelse
                        </div>
                        <div style="display: flex; justify-content: space-between; gap: 6px; padding-top: 8px; border-top: 1px solid #f1f5f9; margin-top: 4px;">
                            <button type="button" class="btn btn-xs btn-outline-secondary" onclick="closeAllExcelDropdowns()">Kapat</button>
                            <button type="submit" class="btn btn-xs btn-primary" style="padding: 4px 12px; font-weight: 600;">✓ Uygula</button>
                        </div>
                    </div>
                </div>

                <!-- 5. İlgili Proje (Excel Çoklu Seçim) -->
                <div class="excel-dropdown-group">
                    <label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 4px; display: block;">
                        📁 İlgili Proje
                    </label>
                    <button type="button" class="excel-filter-trigger" id="btnTriggerProjects" onclick="toggleExcelDropdown('dropdownProjects', event)" style="background: {{ count($selectedProjectIds) > 0 ? '#f0f9ff' : '#ffffff' }}; border: 1.5px solid {{ count($selectedProjectIds) > 0 ? '#0284c7' : '#cbd5e1' }};">
                        <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: {{ count($selectedProjectIds) > 0 ? '#0284c7' : '#334155' }}; font-weight: {{ count($selectedProjectIds) > 0 ? '700' : '500' }};">
                            {{ count($selectedProjectIds) > 0 ? count($selectedProjectIds) . ' Proje Seçili' : '-- Tümü --' }}
                        </span>
                        <span style="font-size: 10px; color: #94a3b8; margin-left: 6px;">▼</span>
                    </button>

                    <!-- Popover Panel -->
                    <div id="dropdownProjects" class="excel-dropdown-menu" style="display: none; position: absolute; top: calc(100% + 4px); right: 0; min-width: 300px; max-width: 360px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; box-shadow: 0 12px 28px -4px rgba(0,0,0,0.25); z-index: 9999 !important; padding: 10px;">
                        <input type="text" placeholder="🔍 Proje kodu veya adı ara..." class="excel-search-input" oninput="filterExcelList(this, 'listProjects')" style="width: 100%; height: 32px; padding: 4px 8px; font-size: 11.5px; border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 8px;">
                        <div style="display: flex; justify-content: space-between; padding: 0 2px 6px; border-bottom: 1px solid #f1f5f9; font-size: 11px;">
                            <a href="javascript:void(0)" onclick="excelSelectAll('project_ids', 'listProjects')" style="color: #0284c7; font-weight: 600; text-decoration: none;">✓ Tümünü Seç</a>
                            <a href="javascript:void(0)" onclick="excelDeselectAll('project_ids', 'listProjects')" style="color: #64748b; font-weight: 600; text-decoration: none;">✕ Temizle</a>
                        </div>
                        <div id="listProjects" class="excel-items-list" style="max-height: 200px; overflow-y: auto; padding: 4px 0;">
                            <!-- Genel Sistem (Projesiz Loglar - Sadece log kaydı varsa veya seçilmişse göster) -->
                            @if($generalSystemCount > 0 || in_array('0', $selectedProjectIds) || in_array('general', $selectedProjectIds))
                                <label class="excel-item-label">
                                    <div style="display: flex; align-items: center; gap: 7px;">
                                        <input type="checkbox" name="project_ids[]" value="0" {{ in_array('0', $selectedProjectIds) || in_array('general', $selectedProjectIds) ? 'checked' : '' }} onchange="updateTriggerText('Projects')">
                                        <span style="color: #64748b; font-style: italic;">🌐 Genel Sistem (Projesiz)</span>
                                    </div>
                                    <span style="font-size: 10.5px; color: #94a3b8; font-weight: 600;">{{ $generalSystemCount }}</span>
                                </label>
                            @endif

                            @forelse($allProjects as $pr)
                                @php $pCount = $projectCounts[$pr->id] ?? 0; @endphp
                                <label class="excel-item-label">
                                    <div style="display: flex; align-items: center; gap: 7px; overflow: hidden;">
                                        <input type="checkbox" name="project_ids[]" value="{{ $pr->id }}" {{ in_array($pr->id, $selectedProjectIds) ? 'checked' : '' }} onchange="updateTriggerText('Projects')">
                                        <span style="color: #1e293b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $pr->title }}">
                                            <strong style="color: #0284c7;">{{ $pr->code }}</strong> - {{ Str::limit($pr->title, 20) }}
                                        </span>
                                    </div>
                                    <span style="font-size: 10.5px; color: #94a3b8; margin-left: 6px; font-weight: 600;">{{ $pCount }}</span>
                                </label>
                            @empty
                                @if(!($generalSystemCount > 0 || in_array('0', $selectedProjectIds) || in_array('general', $selectedProjectIds)))
                                    <div style="padding: 10px; font-size: 11.5px; color: #94a3b8; text-align: center;">Log kaydı bulunan proje yok</div>
                                @endif
                            @endforelse
                        </div>
                        <div style="display: flex; justify-content: space-between; gap: 6px; padding-top: 8px; border-top: 1px solid #f1f5f9; margin-top: 4px;">
                            <button type="button" class="btn btn-xs btn-outline-secondary" onclick="closeAllExcelDropdowns()">Kapat</button>
                            <button type="submit" class="btn btn-xs btn-primary" style="padding: 4px 12px; font-weight: 600;">✓ Uygula</button>
                        </div>
                    </div>
                </div>

                <!-- 6. Butonlar -->
                <div style="display: flex; gap: 6px;">
                    <button type="submit" class="btn btn-sm btn-primary" style="height: 38px; padding: 0 16px; font-weight: 700; font-size: 12.5px; border-radius: 6px; box-shadow: 0 2px 4px rgba(2,132,199,0.2);">
                        🔍 Filtrele
                    </button>
                    @if(!empty($selectedActions) || !empty($selectedUserIds) || !empty($selectedProjectIds) || !empty($selectedCategories) || request()->filled('search'))
                        <a href="{{ route('admin.audit_logs') }}" class="btn btn-sm btn-outline-secondary" style="height: 38px; padding: 0 12px; display: flex; align-items: center; border-radius: 6px; font-size: 12px;" title="Filtreleri Temizle">
                            ✕ Sıfırla
                        </a>
                    @endif
                </div>
            </div>

            <!-- AKTİF FİLTRELER BİLGİ KUTUSU (EXCEL BİLGİ VE HIZLI ÇIKARMA ŞERİDİ) -->
            @php
                $hasActiveFilters = !empty($selectedActions) || !empty($selectedUserIds) || !empty($selectedProjectIds) || !empty($selectedCategories) || request()->filled('search');
                $totalActiveFilterCount = count($selectedActions) + count($selectedUserIds) + count($selectedProjectIds) + count($selectedCategories) + (request()->filled('search') ? 1 : 0);
            @endphp

            <div class="active-filters-box" style="margin-top: 14px; padding: 11px 14px; background: {{ $hasActiveFilters ? '#f0f9ff' : '#f8fafc' }}; border: 1px solid {{ $hasActiveFilters ? '#bae6fd' : '#e2e8f0' }}; border-radius: 8px; transition: all 0.2s;">
                @if($hasActiveFilters)
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                        <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 8px; flex: 1;">
                            <span style="font-size: 12px; font-weight: 700; color: #0369a1; display: inline-flex; align-items: center; gap: 5px;">
                                <span>🎯</span> Aktif Filtreler ({{ $totalActiveFilterCount }} Kriter):
                            </span>

                            <!-- Kategori Rozetleri -->
                            @foreach($selectedCategories as $catVal)
                                @php
                                    $catMeta = $categoryCatalog[$catVal] ?? \App\Models\ProjectAuditLog::getCategoryMeta($catVal);
                                    $catTitle = is_array($catMeta) ? ($catMeta['title'] ?? $catVal) : $catMeta;
                                    $catIcon = is_array($catMeta) ? ($catMeta['icon'] ?? '🏷️') : '🏷️';
                                @endphp
                                <span class="active-filter-pill" style="background: #ffffff; border: 1px solid #7dd3fc; color: #0369a1;">
                                    <span>{{ $catIcon }}</span> {{ $catTitle }}
                                    <button type="button" onclick="removeExcelFilter('categories', '{{ $catVal }}')" title="Bu filtreyi kaldır">✕</button>
                                </span>
                            @endforeach

                            <!-- Aksiyon Rozetleri -->
                            @foreach($selectedActions as $actVal)
                                @php
                                    $actMeta = $actionCatalog[$actVal] ?? \App\Models\ProjectAuditLog::getActionMeta($actVal);
                                    $actTitle = is_array($actMeta) ? ($actMeta['title'] ?? $actVal) : $actVal;
                                    $actIcon = is_array($actMeta) ? ($actMeta['icon'] ?? '⚡') : '⚡';
                                @endphp
                                <span class="active-filter-pill" style="background: #ffffff; border: 1px solid #fecdd3; color: #9f1239;">
                                    <span>{{ $actIcon }}</span> {{ $actTitle }}
                                    <button type="button" onclick="removeExcelFilter('actions', '{{ $actVal }}')" title="Bu filtreyi kaldır">✕</button>
                                </span>
                            @endforeach

                            <!-- Kullanıcı Rozetleri -->
                            @foreach($selectedUserIds as $uId)
                                @php $userObj = $allUsers->firstWhere('id', $uId); @endphp
                                <span class="active-filter-pill" style="background: #ffffff; border: 1px solid #bbf7d0; color: #166534;">
                                    <span>👤</span> {{ $userObj ? $userObj->name : "Kullanıcı #{$uId}" }}
                                    <button type="button" onclick="removeExcelFilter('user_ids', '{{ $uId }}')" title="Bu filtreyi kaldır">✕</button>
                                </span>
                            @endforeach

                            <!-- Proje Rozetleri -->
                            @foreach($selectedProjectIds as $pId)
                                @php
                                    $isGen = ($pId === '0' || $pId === 'general');
                                    $projObj = !$isGen ? $allProjects->firstWhere('id', $pId) : null;
                                @endphp
                                <span class="active-filter-pill" style="background: #ffffff; border: 1px solid #fed7aa; color: #9a3412;">
                                    <span>📁</span> {{ $isGen ? 'Genel Sistem' : ($projObj ? "{$projObj->code} - " . Str::limit($projObj->title, 18) : "Proje #{$pId}") }}
                                    <button type="button" onclick="removeExcelFilter('project_ids', '{{ $pId }}')" title="Bu filtreyi kaldır">✕</button>
                                </span>
                            @endforeach

                            <!-- Arama Rozeti -->
                            @if(request()->filled('search'))
                                <span class="active-filter-pill" style="background: #ffffff; border: 1px solid #cbd5e1; color: #334155;">
                                    <span>🔍</span> "{{ request('search') }}"
                                    <button type="button" onclick="removeSearchFilter()" title="Aramayı temizle">✕</button>
                                </span>
                            @endif
                        </div>

                        <!-- Sağ Taraf: Sonuç Sayısı ve Sıfırla -->
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="badge" style="background: #0284c7; color: #ffffff; padding: 5px 10px; border-radius: 6px; font-weight: 700; font-size: 11.5px;">
                                {{ $logs->total() }} Kayıt Listeleniyor
                            </span>
                            <a href="{{ route('admin.audit_logs') }}" class="btn btn-xs btn-outline-danger" style="padding: 4px 10px; font-weight: 600; border-radius: 6px; font-size: 11.5px; text-decoration: none;">
                                🗑️ Tüm Filtreleri Temizle
                            </a>
                        </div>
                    </div>
                @else
                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: #64748b;">
                        <span style="display: flex; align-items: center; gap: 6px;">
                            <span>ℹ️</span> Şu anda filtre uygulanmıyor (Tüm denetim logları listeleniyor). Excel menülerinden dilediğiniz kriterleri seçerek daraltabilirsiniz.
                        </span>
                        <span class="badge" style="background: #e2e8f0; color: #334155; padding: 4px 9px; border-radius: 6px; font-weight: 700;">
                            Toplam {{ $totalLogsCount ?? $logs->total() }} Kayıt
                        </span>
                    </div>
                @endif
            </div>
        </form>
    </div>

    <!-- KOMPAKT VE MODERN DENETİM LOGLARI TABLOSU -->
    <div class="card mb-4" style="border-radius: 10px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.03);">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="width: 100%; border-collapse: collapse; font-size: 12.5px;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                            <th style="width: 130px; padding: 10px 14px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Zaman</th>
                            <th style="width: 155px; padding: 10px 14px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">İşlemi Yapan</th>
                            <th style="width: 155px; padding: 10px 14px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">İlgili Proje</th>
                            <th style="width: 145px; padding: 10px 14px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; text-align: center;">Kategori & Modül</th>
                            <th style="width: 135px; padding: 10px 14px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; text-align: center;">Aksiyon</th>
                            <th style="padding: 10px 14px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Açıklama & Değişiklik Detayı</th>
                            <th style="width: 105px; padding: 10px 14px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; text-align: right;">IP Adresi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            @php
                                // STANDART SİSTEM AKSİYON & KATEGORİ KATALOĞU (Tek Merkez)
                                $actionMeta = $log->action_meta;
                                $catMeta = $log->category_meta;
                            @endphp
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.1s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 10px 14px; vertical-align: top;">
                                    <div style="font-weight: 700; color: #0f172a; font-size: 11.5px;">{{ $log->created_at->format('d.m.Y H:i') }}</div>
                                    <div style="font-size: 10.5px; color: #64748b; margin-top: 2px;">{{ $log->created_at->diffForHumans() }}</div>
                                </td>
                                <td style="padding: 10px 14px; vertical-align: top;">
                                    @php
                                        $userName = $log->user ? $log->user->getMaskedNameFor(auth()->user()) : 'Sistem';
                                    @endphp
                                    <div style="font-weight: 700; color: #0f172a; font-size: 12.5px;">{{ $userName }}</div>
                                    @if($log->actingAsUser)
                                        <div style="font-size: 10.5px; color: #dc2626; font-weight: 600; margin-top: 2px;">
                                            ⚠️ <strong>{{ $log->actingAsUser->name }}</strong> adına
                                        </div>
                                    @endif
                                </td>
                                <td style="padding: 10px 14px; vertical-align: top;">
                                    @if($log->project)
                                        <a href="{{ route('projects.show', $log->project_id) }}" style="color: #0284c7; font-weight: 700; text-decoration: none; font-size: 12px;">
                                            {{ $log->project->code }}
                                        </a>
                                        <div style="font-size: 11px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;" title="{{ $log->project->title }}">
                                            {{ $log->project->title }}
                                        </div>
                                    @else
                                        <span style="font-size: 11.5px; color: #94a3b8; font-style: italic;">Genel Sistem</span>
                                    @endif
                                </td>
                                <td style="padding: 10px 14px; vertical-align: top; text-align: center;">
                                    <span style="display: inline-block; font-size: 11px; font-weight: 600; padding: 2.5px 7px; border-radius: 5px; background: {{ $catMeta['bg'] }}; color: {{ $catMeta['text'] }}; border: 1px solid {{ $catMeta['border'] }}; white-space: nowrap;">
                                        {{ $catMeta['icon'] }} {{ $catMeta['title'] }}
                                    </span>
                                </td>
                                <td style="padding: 10px 14px; vertical-align: top; text-align: center;">
                                    <span style="display: inline-block; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px; background: {{ $actionMeta['bg'] }}; color: {{ $actionMeta['text'] }}; border: 1px solid {{ $actionMeta['border'] }}; white-space: nowrap;">
                                        {{ $actionMeta['icon'] }} {{ $actionMeta['title'] }}
                                    </span>
                                </td>
                                <td style="padding: 10px 14px; vertical-align: top;">
                                    <div style="color: #1e293b; font-size: 12.5px; line-height: 1.45;">
                                        {!! $log->formatted_description !!}
                                    </div>
                                    @if(!empty($log->diffs))
                                        <div style="margin-top: 8px;">
                                            <button type="button" onclick="toggleLogDiff({{ $log->id }})" style="font-size: 11px; font-weight: 700; color: #0369a1; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 6px; padding: 3px 9px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 1px 2px rgba(2,132,199,0.08); transition: all 0.15s ease;" onmouseover="this.style.background='#e0f2fe'" onmouseout="this.style.background='#f0f9ff'">
                                                <span>🔍</span> <span id="diffBtnText_{{ $log->id }}">Eski / Yeni Veri Karşılaştırması ({{ count($log->diffs) }} Alan Değişti) ▾</span>
                                            </button>
                                            <div id="diffBox_{{ $log->id }}" style="display: none; margin-top: 8px; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 12px; font-size: 11.5px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                                                <table style="width: 100%; border-collapse: collapse; font-size: 11.5px;">
                                                    <thead>
                                                        <tr style="border-bottom: 1.5px solid #e2e8f0; text-align: left; color: #475569;">
                                                            <th style="padding: 5px 8px; width: 140px; font-weight: 700;">Değişen Parametre</th>
                                                            <th style="padding: 5px 8px; color: #991b1b; font-weight: 700; width: 40%;">Eski Değer</th>
                                                            <th style="padding: 5px 8px; color: #166534; font-weight: 700; width: 40%;">Yeni Değer</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($log->diffs as $df)
                                                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                                                <td style="padding: 6px 8px; font-weight: 700; color: #334155;">{{ $df['label'] }}</td>
                                                                <td style="padding: 6px 8px; color: #991b1b; background: #fef2f2; border-radius: 4px; font-weight: 500;">
                                                                    <del>{{ $df['old'] }}</del>
                                                                </td>
                                                                <td style="padding: 6px 8px; color: #15803d; background: #f0fdf4; border-radius: 4px; font-weight: 700;">
                                                                    <span>{{ $df['new'] }}</span>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif
                                    @if($log->waiting_for_role)
                                        <div style="margin-top: 4px; display: inline-flex; align-items: center; gap: 4px; background: #fffbeb; color: #92400e; border: 1px solid #fde68a; padding: 2px 7px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                            ⏳ <strong>{{ $log->created_at->diffInMinutes() }}</strong> dk'dır <strong>{{ $log->waiting_for_role }}</strong> rolünü bekliyor
                                        </div>
                                    @endif
                                </td>
                                <td style="padding: 10px 14px; vertical-align: top; text-align: right;">
                                    <code style="font-size: 11px; color: #64748b; background: #f1f5f9; padding: 2px 5px; border-radius: 4px;">{{ $log->ip_address ?? '127.0.0.1' }}</code>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 36px 14px; color: #94a3b8; font-size: 13px;">
                                    📜 Seçilen çoklu filtrelere uygun denetim logu bulunamadı.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Sayfalama (Pagination) -->
            <div style="padding: 12px 18px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; background: #fafafa;">
                <div style="font-size: 12px; color: #64748b;">
                    Toplam <strong>{{ $logs->total() }}</strong> denetim kaydından <strong>{{ $logs->firstItem() ?? 0 }}-{{ $logs->lastItem() ?? 0 }}</strong> arası gösteriliyor.
                </div>
                <div>
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SEKME 2: LOG GÖRÜNÜRLÜK MATRİSİ (YETKİ MATRİSİ İLE AYNI DİNAMİK ROL RENKLERİ) -->
<div id="tab-visibility" class="tab-content-panel" style="display: none;">
    <div class="card mb-4" style="border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden !important; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
        <div class="card-header" style="background: #ffffff; padding: 16px 20px; border-bottom: 1.5px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <h3 class="card-title" style="margin: 0; font-size: 15px; font-weight: 700; color: #0f172a;">
                    Rol Bazlı Log Görünürlük Matrisi
                </h3>
                <p style="margin: 4px 0 0 0; font-size: 12px; color: #64748b;">
                    Rollerin isim ve etiket renkleri <strong>Yetki Matrisi / Rol & Renk Yönetimi</strong> ile tam eşzamanlıdır.
                </p>
            </div>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-sm btn-outline-primary" style="font-size: 12px; font-weight: 600; padding: 5px 12px; border-radius: 6px;">
                🎨 Rol & Renk Yönetimine Git
            </a>
        </div>
        <div class="card-body p-0" style="overflow: hidden !important;">
            @php
                $matrixCategories = [
                    'proje' => 'Proje Durum & Genel Loglar',
                    'proje_silme' => 'Proje Silme & Arşivleme Logları',
                    'gorev' => 'Görev & Atama Logları',
                    'risk' => 'Risk & Azaltma Eylemleri',
                    'isg' => 'İSG Kapı Kilidi & Güvenlik',
                    'butce' => 'BOM, Maliyet & Finansal Loglar',
                    'doa' => 'Yetki Devri (DOA) & Bütçe Eşikleri',
                    'kurul' => 'Kurul Oylamaları & Karar Logları',
                    'toplanti' => 'Toplantı & Karar Logları',
                    'vekalet' => 'Vekalet, Görev & Rol Devri Logları',
                    'sistem' => 'Sistem & Güvenlik Ayarları',
                ];
            @endphp
            <div class="table-responsive matrix-scroll-wrapper" style="overflow-x: auto !important; overflow-y: visible !important; width: 100% !important; max-width: 100% !important; box-sizing: border-box !important; -webkit-overflow-scrolling: touch !important;">
                <table class="table table-hover matrix-table mb-0" style="width: max-content; min-width: 100%; border-collapse: separate; border-spacing: 0;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #cbd5e1;">
                            <th style="position: sticky; top: -24px; left: 0; z-index: 35; background: #f8fafc; width: 280px; min-width: 280px; padding: 12px 18px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; border-right: 2px solid #cbd5e1; border-bottom: 2px solid #cbd5e1; box-shadow: 4px 0 8px -2px rgba(15, 23, 42, 0.08);">Log Kategorisi</th>
                            @foreach($roles as $role)
                                <th class="text-center" style="position: sticky; top: -24px; z-index: 25; background: #f8fafc; min-width: 130px; padding: 12px 8px; vertical-align: middle; border-bottom: 2px solid #cbd5e1; box-shadow: 0 2px 4px rgba(0,0,0,0.04);">
                                    <div class="matrix-role-header-badge" 
                                         style="background: {{ $role->badge_bg }}; color: {{ $role->badge_text }}; border: 1px solid {{ $role->badge_border }}; border-radius: 6px; padding: 4px 6px; font-size: 10px; font-weight: 700; text-align: center; display: inline-flex; align-items: center; justify-content: center; width: 100%; min-height: 32px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);"
                                         title="{{ $role->name }} ({{ $role->slug }})">
                                        <span style="text-align: center; line-height: 1.2;">{{ $role->name }}</span>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($matrixCategories as $catKey => $catTitle)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="position: sticky; left: 0; z-index: 20; background: #ffffff; width: 280px; min-width: 280px; padding: 14px 18px; border-right: 2px solid #cbd5e1; box-shadow: 4px 0 8px -2px rgba(15, 23, 42, 0.08);">
                                    <div style="font-weight: 700; color: #0f172a; font-size: 13px;">{{ $catTitle }}</div>
                                    <code class="code-pill text-xs" style="font-size: 11px; color: #0284c7; background: #f0f9ff; padding: 2px 6px; border-radius: 4px; margin-top: 3px; display: inline-block;">{{ $catKey }}</code>
                                </td>
                                @foreach($roles as $role)
                                    @php
                                        $setting = $visibilitySettings->first(function($s) use ($role, $catKey) {
                                            return $s->role_id === $role->id && $s->log_category === $catKey;
                                        });
                                        $canView = $setting ? (bool)$setting->can_view_logs : ($role->isSuperAdmin() || $role->isAdmin());
                                    @endphp
                                    <td class="text-center" style="vertical-align: middle; padding: 12px 8px;">
                                        @if($role->isSuperAdmin())
                                            <span class="badge" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-size: 11px; padding: 4px 8px; border-radius: 6px; font-weight: 700;">
                                                Tam Görünür
                                            </span>
                                        @else
                                            <label class="custom-toggle" style="margin: 0; display: inline-block;">
                                                <input type="checkbox"
                                                       class="log-visibility-toggle"
                                                       data-role-id="{{ $role->id }}"
                                                       data-category="{{ $catKey }}"
                                                       {{ $canView ? 'checked' : '' }}
                                                       onchange="handleVisibilityToggle(this)">
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
    </div>
</div>

<script>
function toggleLogDiff(logId) {
    const box = document.getElementById('diffBox_' + logId);
    const txt = document.getElementById('diffBtnText_' + logId);
    if (!box) return;
    if (box.style.display === 'none') {
        box.style.display = 'block';
        if (txt) txt.textContent = txt.textContent.replace('▾', '▴');
    } else {
        box.style.display = 'none';
        if (txt) txt.textContent = txt.textContent.replace('▴', '▾');
    }
}
window.toggleLogDiff = toggleLogDiff;

// =========================================================================
// SEKME YÖNETİMİ & HASH KONTROLÜ (#tab-visibility)
// =========================================================================
function switchLogTab(tabName) {
    const streamPanel = document.getElementById('tab-stream');
    const visPanel = document.getElementById('tab-visibility');
    const btnStream = document.getElementById('btnTabStream');
    const btnVis = document.getElementById('btnTabVisibility');
    const sisterVis = document.getElementById('sisterTabVisibility');

    if (tabName === 'stream') {
        if (streamPanel) streamPanel.style.display = 'block';
        if (visPanel) visPanel.style.display = 'none';
        if (btnStream) {
            btnStream.style.background = '#0284c7';
            btnStream.style.color = '#ffffff';
            btnStream.style.boxShadow = '0 2px 4px rgba(2, 132, 199, 0.25)';
        }
        if (btnVis) {
            btnVis.style.background = 'transparent';
            btnVis.style.color = '#475569';
            btnVis.style.boxShadow = 'none';
        }
        if (sisterVis) sisterVis.classList.remove('active');
        history.replaceState(null, null, ' ');
    } else {
        if (streamPanel) streamPanel.style.display = 'none';
        if (visPanel) visPanel.style.display = 'block';
        if (btnVis) {
            btnVis.style.background = '#0284c7';
            btnVis.style.color = '#ffffff';
            btnVis.style.boxShadow = '0 2px 4px rgba(2, 132, 199, 0.25)';
        }
        if (btnStream) {
            btnStream.style.background = 'transparent';
            btnStream.style.color = '#475569';
            btnStream.style.boxShadow = 'none';
        }
        if (sisterVis) sisterVis.classList.add('active');
        window.location.hash = 'tab-visibility';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash === '#tab-visibility') {
        switchLogTab('visibility');
    }
});

// =========================================================================
// EXCEL TARZI ÇOKLU FİLTRE DROPDOWN MOTORU
// =========================================================================
function toggleExcelDropdown(menuId, event) {
    if (event) event.stopPropagation();
    const targetMenu = document.getElementById(menuId);
    if (!targetMenu) return;
    const isCurrentlyOpen = targetMenu.style.display === 'block';

    closeAllExcelDropdowns();

    if (!isCurrentlyOpen) {
        targetMenu.style.display = 'block';
        const searchInput = targetMenu.querySelector('.excel-search-input');
        if (searchInput) {
            setTimeout(() => searchInput.focus(), 50);
        }
    }
}

function closeAllExcelDropdowns() {
    document.querySelectorAll('.excel-dropdown-menu').forEach(m => {
        m.style.display = 'none';
    });
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.excel-dropdown-group')) {
        closeAllExcelDropdowns();
    }
});

function filterExcelList(inputEl, listId) {
    const filter = (inputEl.value || '').toLowerCase().trim();
    const list = document.getElementById(listId);
    if (!list) return;
    const labels = list.querySelectorAll('.excel-item-label');
    let matchCount = 0;
    labels.forEach(lbl => {
        const text = lbl.textContent.toLowerCase();
        const matches = text.includes(filter);
        lbl.style.display = matches ? 'flex' : 'none';
        if (matches) matchCount++;
    });

    let emptyNotice = list.querySelector('.excel-empty-notice');
    if (matchCount === 0) {
        if (!emptyNotice) {
            emptyNotice = document.createElement('div');
            emptyNotice.className = 'excel-empty-notice';
            emptyNotice.textContent = 'Aramaya uygun kayıt bulunamadı.';
            list.appendChild(emptyNotice);
        } else {
            emptyNotice.style.display = 'block';
        }
    } else if (emptyNotice) {
        emptyNotice.style.display = 'none';
    }
}

function excelSelectAll(fieldName, listId) {
    const list = document.getElementById(listId);
    if (!list) return;
    const visibleLabels = list.querySelectorAll('.excel-item-label');
    visibleLabels.forEach(lbl => {
        if (lbl.style.display !== 'none') {
            const cb = lbl.querySelector('input[type="checkbox"]');
            if (cb) cb.checked = true;
        }
    });
    const groupName = fieldName.charAt(0).toUpperCase() + fieldName.slice(1).replace('_ids', 's');
    updateTriggerText(groupName);
}

function excelDeselectAll(fieldName, listId) {
    const list = document.getElementById(listId);
    if (!list) return;
    const checkboxes = list.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(cb => cb.checked = false);
    const groupName = fieldName.charAt(0).toUpperCase() + fieldName.slice(1).replace('_ids', 's');
    updateTriggerText(groupName);
}

function updateTriggerText(group) {
    let name = '';
    let btnId = '';
    let suffix = '';
    if (group === 'Categories') {
        name = 'categories[]';
        btnId = 'btnTriggerCategories';
        suffix = ' Kategori Seçili';
    } else if (group === 'Actions') {
        name = 'actions[]';
        btnId = 'btnTriggerActions';
        suffix = ' Aksiyon Seçili';
    } else if (group === 'Users') {
        name = 'user_ids[]';
        btnId = 'btnTriggerUsers';
        suffix = ' Kullanıcı Seçili';
    } else if (group === 'Projects') {
        name = 'project_ids[]';
        btnId = 'btnTriggerProjects';
        suffix = ' Proje Seçili';
    }

    const checkedCount = document.querySelectorAll(`input[name="${name}"]:checked`).length;
    const btn = document.getElementById(btnId);
    if (btn) {
        const span = btn.querySelector('span');
        if (checkedCount > 0) {
            btn.style.background = '#f0f9ff';
            btn.style.borderColor = '#0284c7';
            if (span) {
                span.style.color = '#0284c7';
                span.style.fontWeight = '700';
                span.textContent = checkedCount + suffix;
            }
        } else {
            btn.style.background = '#ffffff';
            btn.style.borderColor = '#cbd5e1';
            if (span) {
                span.style.color = '#334155';
                span.style.fontWeight = '500';
                span.textContent = '-- Tümü --';
            }
        }
    }
}

// =========================================================================
// AKTİF FİLTRE ROZETLERİNDEN HIZLI ÇIKARMA
// =========================================================================
function removeExcelFilter(fieldName, value) {
    const cb = document.querySelector(`input[name="${fieldName}[]"][value="${value}"]`);
    if (cb) {
        cb.checked = false;
    }
    document.getElementById('logFilterForm').submit();
}

function removeSearchFilter() {
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.value = '';
    }
    document.getElementById('logFilterForm').submit();
}

// =========================================================================
// ESKİ / YENİ VERİ KARŞILAŞTIRMASI (DIFF) TOGGLE
// =========================================================================
function toggleLogDiff(logId) {
    const box = document.getElementById('diffBox_' + logId);
    const btnText = document.getElementById('diffBtnText_' + logId);
    if (!box) return;

    if (box.style.display === 'none' || box.style.display === '') {
        box.style.display = 'block';
        if (btnText) {
            btnText.innerHTML = btnText.innerHTML.replace('▾', '▴');
        }
    } else {
        box.style.display = 'none';
        if (btnText) {
            btnText.innerHTML = btnText.innerHTML.replace('▴', '▾');
        }
    }
}

// =========================================================================
// LOG GÖRÜNÜRLÜK MATRİSİ TOGGLE
// =========================================================================
function handleVisibilityToggle(el) {
    const roleId = el.getAttribute('data-role-id');
    const category = el.getAttribute('data-category');
    const canView = el.checked ? 1 : 0;

    fetch('{{ route("admin.audit_logs.toggle_visibility") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            role_id: roleId,
            log_category: category,
            can_view: canView
        })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert('Ayar kaydedilemedi!');
            el.checked = !el.checked;
        }
    })
    .catch(err => {
        console.error(err);
        alert('Sunucu iletişim hatası!');
        el.checked = !el.checked;
    });
}
</script>
@endsection
