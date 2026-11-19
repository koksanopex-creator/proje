@extends('layouts.app')

@section('title', 'Bütçe Eşik Kademeleri & Yetki Devri (DOA) - Köksan Enterprise PPM')

@section('content')
@php
    $canManage = auth()->user()->isDeveloper() || auth()->user()->hasRole('admin') || auth()->user()->hasPermission('manage_doa_rules');
@endphp
<style>
/* Kesin Gizleme Sınıfı */
.d-none {
    display: none !important;
}

/* Matrisler Arası Hızlı Geçiş Sekmeler Çubuğu */
.matrix-sister-tabs {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 8px !important;
    border-bottom: 2px solid #e2e8f0 !important;
    margin-bottom: 20px !important;
    padding-bottom: 0 !important;
    align-items: center !important;
    box-sizing: border-box !important;
    width: 100% !important;
    min-height: 44px !important;
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

/* Modern Header */
.matrix-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}
.matrix-page-header .page-title {
    font-size: 24px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.025em;
    margin: 0 0 6px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.matrix-page-header .page-subtitle {
    font-size: 13px;
    color: #64748b;
    margin: 0;
    line-height: 1.5;
    max-width: 820px;
}
.matrix-header-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    height: 38px;
    padding: 0 16px;
    font-size: 13px;
    font-weight: 700;
    color: #ffffff !important;
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
    border: 1px solid #0284c7;
    border-radius: 8px;
    text-decoration: none;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);
    transition: all 0.15s ease;
    white-space: nowrap;
}
.matrix-header-btn-primary:hover {
    background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(2, 132, 199, 0.35);
}
.matrix-header-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    height: 38px;
    padding: 0 14px;
    font-size: 13px;
    font-weight: 650;
    color: #334155 !important;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 8px;
    text-decoration: none;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    transition: all 0.15s ease;
    white-space: nowrap;
}
.matrix-header-btn-secondary:hover {
    background: #f8fafc;
    border-color: #94a3b8;
    color: #0f172a !important;
}

/* Minimal & Kibar KPI Strip */
.minimal-kpi-strip {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 12px;
    margin-bottom: 20px;
}
.minimal-kpi-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
    height: 54px;
    box-sizing: border-box;
}
.minimal-kpi-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.06);
}
.minimal-kpi-icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}
.minimal-kpi-info {
    display: flex;
    flex-direction: column;
    justify-content: center;
    overflow: hidden;
}
.minimal-kpi-label {
    font-size: 11px;
    font-weight: 650;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    line-height: 1.1;
    margin-bottom: 2px;
}
.minimal-kpi-val {
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.1;
    white-space: nowrap;
}

/* Küresel Motor & Simülatör Kartı (Ferah & Şık) */
.doa-control-banner {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.control-banner-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    padding-bottom: 14px;
    border-bottom: 1px solid #f1f5f9;
}
.control-banner-bottom {
    padding-top: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    flex-wrap: wrap;
}

/* Switch */
.switch-sm {
    position: relative;
    display: inline-block;
    width: 38px;
    height: 22px;
    vertical-align: middle;
}
.switch-sm input {
    opacity: 0;
    width: 0;
    height: 0;
}
.slider-sm {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #cbd5e1;
    transition: .25s;
    border-radius: 22px;
}
.slider-sm:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .25s;
    border-radius: 50%;
}
input:checked + .slider-sm {
    background-color: #10b981;
}
input:checked + .slider-sm:before {
    transform: translateX(16px);
}

/* Modern Enterprise Tablo */
.doa-table-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    box-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.05);
    overflow: hidden;
}
.doa-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    margin: 0;
}
.doa-table thead tr {
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
}
.doa-table th {
    padding: 14px 16px;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #475569;
    border: none;
    white-space: nowrap;
    text-align: left;
}
.doa-table tbody tr {
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.15s ease;
}
.doa-table tbody tr:hover {
    background: #f8fafc;
}
.doa-table tbody tr:last-child {
    border-bottom: none;
}
.doa-table td {
    border: none;
    padding: 13px 16px;
    vertical-align: middle;
}

.priority-pill {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
    color: #ffffff;
    font-size: 12px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 4px rgba(2, 132, 199, 0.25);
}

.btn-action-edit {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 700;
    color: #0369a1;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 7px;
    cursor: pointer;
    transition: all 0.15s ease;
    white-space: nowrap;
    text-decoration: none;
    line-height: 1;
}
.btn-action-edit:hover {
    background: #e0f2fe;
    border-color: #7dd3fc;
    color: #0284c7;
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(2, 132, 199, 0.15);
}

.btn-action-delete {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    padding: 0;
    font-size: 13px;
    color: #dc2626;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 7px;
    cursor: pointer;
    transition: all 0.15s ease;
    line-height: 1;
}
.btn-action-delete:hover {
    background: #fee2e2;
    border-color: #fca5a5;
    color: #b91c1c;
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(220, 38, 38, 0.15);
}

/* ==========================================================================
   PURE VANILLA CSS CUSTOM MODALS (GÜVENLİ & FERAH FORMLAR)
   ========================================================================== */
.custom-modal-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.65);
    z-index: 10050;
    backdrop-filter: blur(4px);
    align-items: center;
    justify-content: center;
    padding: 24px;
    box-sizing: border-box;
}
.custom-modal-backdrop.is-active {
    display: flex !important;
}
#currencyModalBackdrop {
    z-index: 10100 !important;
}
.custom-modal-dialog {
    max-width: 680px;
    width: 100%;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 25px 60px -15px rgba(15, 23, 42, 0.28), 0 0 0 1px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    animation: modalScaleIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    max-height: 92vh;
    display: flex;
    flex-direction: column;
}
@keyframes modalScaleIn {
    0% { opacity: 0; transform: scale(0.96) translateY(-6px); }
    100% { opacity: 1; transform: scale(1) translateY(0); }
}
.custom-modal-header {
    padding: 20px 26px;
    background: #ffffff;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.custom-modal-title {
    margin: 0;
    font-size: 16.5px;
    font-weight: 800;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 10px;
}
.custom-modal-close {
    background: #f1f5f9;
    border: none;
    font-size: 16px;
    font-weight: 700;
    color: #64748b;
    cursor: pointer;
    line-height: 1;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
}
.custom-modal-close:hover {
    background: #fee2e2;
    color: #b91c1c;
}
.custom-modal-body {
    padding: 24px 28px;
    overflow-y: auto;
    font-size: 13px;
}
.custom-modal-footer {
    padding: 16px 28px;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
}

/* Form Grupları */
.modal-form-group {
    margin-bottom: 20px;
}
.modal-form-label {
    display: block;
    font-size: 11.5px;
    font-weight: 750;
    color: #334155;
    text-transform: uppercase;
    letter-spacing: 0.35px;
    margin-bottom: 7px;
}
.modal-form-control {
    width: 100%;
    height: 42px;
    border: 1.5px solid #cbd5e1;
    border-radius: 9px;
    padding: 0 13px;
    font-size: 13.5px;
    color: #0f172a;
    background: #ffffff;
    box-sizing: border-box;
    transition: all 0.15s ease;
}
.modal-form-control:focus {
    outline: none;
    border-color: #0284c7;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
}
textarea.modal-form-control {
    height: auto;
    padding: 11px 13px;
    line-height: 1.45;
}

/* Modern Bölüm Kartı */
.doa-modal-section-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 22px;
}
.doa-modal-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
    padding-bottom: 8px;
    border-bottom: 1px dashed #cbd5e1;
}
.doa-modal-section-title {
    font-size: 12px;
    font-weight: 800;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 6px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

/* Modern Hap Buton: Birimleri Yönet */
.btn-manage-currencies-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    font-size: 11px;
    font-weight: 750;
    color: #0369a1;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.15s ease;
    text-decoration: none;
    line-height: 1.2;
}
.btn-manage-currencies-pill:hover {
    background: #dbeafe;
    border-color: #60a5fa;
    color: #1d4ed8;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(2, 132, 199, 0.12);
}

/* Onay Tipi Görsel Seçim Kartları */
.approver-type-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 14px;
}
.approver-type-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 12px 10px;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.15s ease;
    text-align: center;
    color: #334155;
    outline: none;
}
.approver-type-card:hover {
    background: #f8fafc;
    border-color: #94a3b8;
    transform: translateY(-1px);
}
.approver-type-card.active {
    background: #eff6ff;
    border-color: #0284c7;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
    color: #0369a1;
}
.approver-type-card .type-icon {
    font-size: 20px;
    margin-bottom: 5px;
}
.approver-type-card .type-title {
    font-size: 12.5px;
    font-weight: 750;
    display: block;
    line-height: 1.25;
}
.approver-type-card .type-desc {
    font-size: 10px;
    color: #64748b;
    margin-top: 3px;
    line-height: 1.2;
}
.approver-type-card.active .type-desc {
    color: #0284c7;
}

/* Alan İpucu Notları (Field Hints) */
.field-hint {
    font-size: 11px;
    color: #64748b;
    margin-top: 6px;
    line-height: 1.4;
    display: flex;
    align-items: flex-start;
    gap: 6px;
}
.field-hint .hint-icon {
    font-size: 12px;
    flex-shrink: 0;
    margin-top: 1px;
}
</style>

<div class="container-fluid px-4 py-3">

    <!-- Üst Başlık & Eylemler -->
    <div class="matrix-page-header">
        <div class="header-left">
            <h1 class="page-title">
                <span>⚖️</span> Bütçe Eşik Kademeleri & Yetki Devri (DOA Matrix)
            </h1>
            <p class="page-subtitle">
                Proje planlanan bütçesine göre dinamik onay rotaları, kurul oylama atamaları ve hızlı devreye alma kurallarını yönetin.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($canManage)
                <button type="button" class="matrix-header-btn-secondary" onclick="openCurrencyModal()" title="Merkezi Para Birimleri Yönetimi (Döviz Kurları ve Tanımlar)">
                    <span>💱</span> Para Birimleri Yönetimi
                </button>
            @endif
            <a href="{{ route('admin.committees.index') }}" class="matrix-header-btn-secondary">
                <span>🏛️</span> Kurullar & Komiteler
            </a>
            @if($canManage)
                <button type="button" class="matrix-header-btn-primary" onclick="openCustomModal('newThresholdModal')">
                    <span>+</span> Yeni Eşik Kademesi Ekle
                </button>
            @endif
        </div>
    </div>

    <!-- Hızlı Geçiş Sekmeleri (Sister Tabs) -->
    <div class="matrix-sister-tabs">
        <a href="{{ route('admin.roles.index') }}" class="matrix-tab-link">
            <span>🎭</span> <span>Roller & Gruplar</span>
        </a>
        <a href="{{ route('admin.user_roles.index') }}" class="matrix-tab-link">
            <span>👥</span> <span>Kullanıcı Yetki & Rolleri</span>
        </a>
        <a href="{{ route('admin.permission_matrix') }}" class="matrix-tab-link">
            <span>🛡️</span> <span>Yetki Matrisi</span>
        </a>
        <a href="{{ route('admin.notification_matrix') }}" class="matrix-tab-link">
            <span>🔔</span> <span>Bildirim Matrisi</span>
        </a>
        <a href="{{ route('admin.doa_thresholds.index') }}" class="matrix-tab-link active">
            <span>⚖️</span> <span>Bütçe Eşik Matrisi (DOA)</span>
        </a>
        <a href="{{ route('admin.committees.index') }}" class="matrix-tab-link">
            <span>🏛️</span> <span>Kurullar & Komiteler</span>
        </a>
        <a href="{{ route('admin.audit_logs') }}" class="matrix-tab-link">
            <span>📋</span> <span>Log Görünürlük Matrisi</span>
        </a>
        <a href="{{ route('admin.system_health') }}" class="matrix-tab-link">
            <span>🩺</span> <span>Sistem Sağlığı</span>
        </a>
    </div>

    <!-- MINIMAL & KİBAR YAN YANA KPI STRIP -->
    <div class="minimal-kpi-strip">
        <div class="minimal-kpi-card">
            <div class="minimal-kpi-icon" style="background: #eef2ff; color: #4f46e5;">⚖️</div>
            <div class="minimal-kpi-info">
                <span class="minimal-kpi-label">Toplam Kademe</span>
                <span class="minimal-kpi-val">{{ $rules->count() }} <small style="font-size: 11px; font-weight: 600; color: #10b981;">({{ $rules->where('is_active', true)->count() }} Aktif)</small></span>
            </div>
        </div>

        <div class="minimal-kpi-card">
            <div class="minimal-kpi-icon" style="background: #ecfdf5; color: #059669;">🏛️</div>
            <div class="minimal-kpi-info">
                <span class="minimal-kpi-label">Kurul Onaylı Kademe</span>
                <span class="minimal-kpi-val">{{ $rules->where('approver_type', 'committee')->count() }} <small style="font-size: 11px; font-weight: 500; color: #64748b;">(Meclis Sevk)</small></span>
            </div>
        </div>

        <div class="minimal-kpi-card" style="cursor: pointer;" onclick="openCurrencyModal()" title="Para Birimleri Yönetimini Aç ve Düzenle">
            <div class="minimal-kpi-icon" style="background: #fff7ed; color: #ea580c;">💱</div>
            <div class="minimal-kpi-info">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="minimal-kpi-label">Baz Para Birimi</span>
                    <span style="font-size: 10.5px; color: #0284c7; font-weight: 700;">⚙️ Yönet</span>
                </div>
                <span class="minimal-kpi-val">{{ $baseCurrency }} <small style="font-size: 11px; font-weight: 600; color: #ea580c;">({{ $currencies->count() }} Tanımlı)</small></span>
            </div>
        </div>

        <div class="minimal-kpi-card">
            <div class="minimal-kpi-icon" style="background: #f0f9ff; color: #0284c7;">⚙️</div>
            <div class="minimal-kpi-info">
                <span class="minimal-kpi-label">Global Motor</span>
                <span class="minimal-kpi-val">{{ $engineActive ? 'DEVREDE' : 'PASİF' }} <small style="font-size: 11px; font-weight: 600; color: {{ $engineActive ? '#10b981' : '#ef4444' }};">●</small></span>
            </div>
        </div>
    </div>

    <!-- KÜRESEL MOTOR ANAHTARI & CANLI TEST SİMÜLATÖRÜ -->
    <div class="doa-control-banner">
        <div class="control-banner-top">
            <div class="d-flex align-items-center gap-3">
                <div style="font-size: 24px;">{{ $engineActive ? '🟢' : '🔴' }}</div>
                <div>
                    <h5 class="fw-bold mb-0" style="font-size: 15px; color: {{ $engineActive ? '#15803d' : '#9f1239' }};">
                        Bütçe Yetki Devri (DOA) Motoru: <span id="engineStatusLabel">{{ $engineActive ? 'DEVREDE (AKTİF)' : 'DEVRE DIŞI (PASİF)' }}</span>
                    </h5>
                    <p class="small text-muted mb-0" style="font-size: 12px;">
                        @if($engineActive)
                            Projeler bütçelerine göre eşleşen kademeye (Doğrudan Hızlı Onay, Yönetici veya Kurul Meclisine) yönlendirilir.
                        @else
                            Motor kapalı olduğunda projeler standart onay rotasına yönlendirilir.
                        @endif
                    </p>
                </div>
            </div>
            @if($canManage)
                <div class="d-flex align-items-center gap-2">
                    <span class="small fw-bold text-slate-700">Motor Durumu:</span>
                    <label class="switch-sm">
                        <input type="checkbox" id="globalEngineToggle" {{ $engineActive ? 'checked' : '' }} onchange="toggleGlobalEngine(this)">
                        <span class="slider-sm"></span>
                    </label>
                </div>
            @endif
        </div>

        <!-- Canlı Kural Simülatörü (Tek Satır, Kibar & Hızlı) -->
        <div class="control-banner-bottom">
            <div class="d-flex align-items-center gap-2 flex-grow-1 flex-wrap">
                <span style="font-size: 12px; font-weight: 750; color: #475569;">🎯 CANLI TEST:</span>
                <div style="display: inline-flex; align-items: center; border: 1px solid #cbd5e1; border-radius: 6px; overflow: hidden; background: #fff; height: 34px;">
                    <input type="number" id="simAmount" value="75000" style="width: 110px; border: none; padding: 0 10px; font-size: 12.5px; font-weight: 700;">
                    <select id="simCurrency" style="border: none; border-left: 1px solid #e2e8f0; padding: 0 8px; font-size: 12px; background: #f8fafc; font-weight: 600;">
                        @foreach($currencies as $c)
                            <option value="{{ $c->code }}" {{ $c->code === 'USD' ? 'selected' : '' }}>{{ $c->code }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" class="btn btn-sm btn-primary" onclick="runSimulation()" style="height: 34px; padding: 0 14px; font-size: 12px; font-weight: 700; border-radius: 6px;">
                    ⚡ Test Et
                </button>
            </div>
            <div id="simResultBox" class="d-none" style="display: none; align-items: center; gap: 8px; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 6px 14px; border-radius: 8px;">
                <span id="simResultText" style="font-size: 12.5px; font-weight: 700; color: #166534;"></span>
            </div>
        </div>
    </div>

    <!-- DİNAMİK BÜTÇE EŞİK KADEMELERİ TABLOSU -->
    <div class="doa-table-card">
        <div class="table-responsive" style="overflow-x: auto;">
            <table class="doa-table">
                <thead>
                    <tr>
                        <th style="width: 60px; text-align: left;">Sıra</th>
                        <th style="min-width: 210px; text-align: left;">Eşik Kademesi</th>
                        <th style="min-width: 160px; text-align: left;">Bütçe Aralığı & Para Birimi</th>
                        <th style="min-width: 140px; white-space: nowrap; text-align: left;">Onay Merci / Hedef</th>
                        <th style="min-width: 130px; text-align: left;">Kapsam</th>
                        <th style="width: 110px; text-align: left;">Onay Tipi</th>
                        <th style="width: 80px; text-align: left;">Durum</th>
                        <th style="width: 120px; text-align: right;">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rules as $r)
                        <tr>
                            <td style="text-align: left;">
                                <span class="priority-pill">{{ $r->priority_order }}</span>
                            </td>
                            <td>
                                <div style="font-weight: 750; color: #0f172a; font-size: 13.5px; line-height: 1.35;">{{ $r->name }}</div>
                                <div style="margin-top: 4px;">
                                    <code style="font-size: 11px; padding: 2px 7px; border-radius: 5px; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; font-family: 'JetBrains Mono', Consolas, monospace; font-weight: 600;">{{ $r->slug }}</code>
                                </div>
                                @if($r->description)
                                    <div style="font-size: 11.5px; color: #64748b; margin-top: 4px; line-height: 1.35;">{{ $r->description }}</div>
                                @endif
                            </td>
                            <td style="white-space: nowrap;">
                                <div style="display: inline-flex; align-items: center; gap: 7px; padding: 5px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                                    <span style="font-size: 13px; font-weight: 800; color: #0f172a; font-family: 'JetBrains Mono', monospace; letter-spacing: -0.2px;">
                                        {{ $r->amount_range_formatted }}
                                    </span>
                                </div>
                            </td>
                            <td style="white-space: nowrap;">
                                @if(!$r->requires_approval)
                                    <span style="display: inline-flex; align-items: center; gap: 5px; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 11.5px; font-weight: 700; padding: 3px 9px; border-radius: 6px; white-space: nowrap; width: fit-content;">
                                        <span style="font-size: 11px;">⚡</span>
                                        <span>Doğrudan (Onaysız)</span>
                                    </span>
                                @elseif($r->approver_type === 'role')
                                    @if($r->role)
                                        <span style="display: inline-flex; flex-direction: row; align-items: center; gap: 6px; background: {{ $r->role->badge_bg }}; color: {{ $r->role->badge_text }}; border: 1px solid {{ $r->role->badge_border }}; padding: 3px 9px; font-size: 11.5px; font-weight: 700; border-radius: 6px; white-space: nowrap; width: fit-content; line-height: 1.25;">
                                            <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: {{ $r->role->color ?? '#0284c7' }}; flex-shrink: 0;"></span>
                                            <span>{{ $r->role->name }}</span>
                                        </span>
                                    @else
                                        <span style="display: inline-flex; align-items: center; gap: 4px; background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; font-size: 11px; font-weight: 650; padding: 3px 8px; border-radius: 6px; white-space: nowrap; width: fit-content;">
                                            <span>⚠️</span>
                                            <span>Rol Atanmamış</span>
                                        </span>
                                    @endif
                                @elseif($r->approver_type === 'committee')
                                    <div style="display: inline-flex; flex-direction: column; gap: 2px;">
                                        <span style="display: inline-flex; align-items: center; gap: 5px; background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 11.5px; font-weight: 700; padding: 3px 9px; border-radius: 6px; white-space: nowrap; width: fit-content;">
                                            <span style="font-size: 11px;">🏛️</span>
                                            <span>{{ $r->committee?->name ?? 'Kurul Belirtilmemiş' }}</span>
                                        </span>
                                        @if($r->committee?->voting_type_label)
                                            <span style="font-size: 10px; color: #64748b; font-weight: 600; padding-left: 3px; white-space: nowrap;">
                                                🗳️ {{ $r->committee->voting_type_label }}
                                            </span>
                                        @endif
                                    </div>
                                @elseif($r->approver_type === 'user')
                                    <span style="display: inline-flex; align-items: center; gap: 5px; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-size: 11.5px; font-weight: 700; padding: 3px 9px; border-radius: 6px; white-space: nowrap; width: fit-content;">
                                        <span style="font-size: 11px;">👤</span>
                                        <span>{{ $r->user?->name ?? 'Kullanıcı Belirtilmemiş' }}</span>
                                    </span>
                                @else
                                    <span style="display: inline-flex; align-items: center; gap: 5px; background: #f8fafc; color: #334155; border: 1px solid #cbd5e1; font-size: 11.5px; font-weight: 700; padding: 3px 9px; border-radius: 6px; white-space: nowrap; width: fit-content;">
                                        <span>⚡</span>
                                        <span>{{ $r->approver_name }}</span>
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($r->project_category_slug || $r->bolum)
                                    <div style="display: inline-flex; flex-direction: column; gap: 4px;">
                                        @if($r->project_category_slug)
                                            <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; background: #f1f5f9; color: #334155; padding: 3px 8px; border-radius: 6px; border: 1px solid #cbd5e1; width: fit-content; white-space: nowrap;">
                                                <span>📁</span> {{ $r->project_category_slug }}
                                            </span>
                                        @endif
                                        @if($r->bolum)
                                            <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; background: #eff6ff; color: #1e40af; padding: 3px 8px; border-radius: 6px; border: 1px solid #bfdbfe; width: fit-content; white-space: nowrap;">
                                                <span>🏢</span> {{ $r->bolum->name }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span style="display: inline-flex; align-items: center; gap: 5px; font-size: 11.5px; color: #64748b; font-weight: 600; background: #f8fafc; padding: 4px 9px; border-radius: 6px; border: 1px solid #e2e8f0; white-space: nowrap;">
                                        <span>🌐</span> Tüm Projeler (Genel)
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: left; white-space: nowrap;">
                                @if(!$r->requires_approval)
                                    <span style="display: inline-flex; align-items: center; gap: 5px; background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; font-size: 11.5px; font-weight: 750; padding: 4px 10px; border-radius: 6px; white-space: nowrap;">
                                        <span>⚡</span> Hızlı Geçiş
                                    </span>
                                @else
                                    <span style="display: inline-flex; align-items: center; gap: 5px; background: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-size: 11.5px; font-weight: 750; padding: 4px 10px; border-radius: 6px; white-space: nowrap;">
                                        <span>🔒</span> Onay Şart
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: left; white-space: nowrap;">
                                @if($canManage)
                                    <label class="switch-sm" title="Kademe Durumunu Değiştir">
                                        <input type="checkbox" {{ $r->is_active ? 'checked' : '' }} onchange="toggleRuleActive({{ $r->id }}, this)">
                                        <span class="slider-sm"></span>
                                    </label>
                                @else
                                    <span class="badge {{ $r->is_active ? 'bg-success' : 'bg-secondary' }}" style="font-size: 11px; padding: 4px 8px; border-radius: 6px;">
                                        {{ $r->is_active ? 'Aktif' : 'Pasif' }}
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                @if($canManage)
                                    <div style="display: inline-flex; align-items: center; justify-content: flex-end; gap: 6px; flex-wrap: nowrap;">
                                        <button type="button" class="btn-action-edit" title="Eşik Kademesini Düzenle" onclick="openEditThresholdModal({{ json_encode($r) }})">
                                            <span>✏️</span> <span>Düzenle</span>
                                        </button>
                                        <form action="{{ route('admin.doa_thresholds.destroy', $r->id) }}" method="POST" style="margin: 0; display: inline-block;" onsubmit="return confirm('\'{{ $r->name }}\' eşik kademesini silmek istediğinize emin misiniz?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action-delete" title="Kademeyi Sil">
                                                <span>🗑️</span>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span style="font-size: 11px; color: #94a3b8;">Görüntüleme</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted" style="padding: 32px; font-size: 13px;">Tanımlı eşik kademesi bulunmamaktadır.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ==========================================================================
     VANILLA CSS MODALLAR (FERAH VE BOŞLUKLU FORMLAR)
     ========================================================================== -->

<!-- MODAL 1: YENİ EŞİK KADEMESİ EKLE -->
<div id="newThresholdModal" class="custom-modal-backdrop" onclick="handleBackdropClick(event, 'newThresholdModal')">
    <div class="custom-modal-dialog">
        <form action="{{ route('admin.doa_thresholds.store') }}" method="POST" onsubmit="cleanAmountBeforeSubmit(this)">
            @csrf
            <div class="custom-modal-header">
                <div>
                    <h3 class="custom-modal-title">
                        <span style="font-size: 19px; background: #f0f9ff; border: 1px solid #bae6fd; width: 34px; height: 34px; border-radius: 9px; display: inline-flex; align-items: center; justify-content: center;">⚖️</span>
                        <span>Yeni Bütçe Eşik Kademesi Tanımla</span>
                    </h3>
                    <p style="margin: 4px 0 0 44px; font-size: 12px; color: #64748b; line-height: 1.35;">
                        Proje planlanan bütçesine göre otomatik devreye girecek onay rotasını ve kuralları yapılandırın.
                    </p>
                </div>
                <button type="button" class="custom-modal-close" onclick="closeCustomModal('newThresholdModal')" title="Kapat">✕</button>
            </div>
            <div class="custom-modal-body">
                <!-- 1. KADEME ADI -->
                <div class="modal-form-group">
                    <label class="modal-form-label">Kademe Adı <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="modal-form-control" placeholder="Örn: Bölüm Lideri Onay Kademesi (1.000 - 50.000 USD)" required>
                    <div class="field-hint">
                        <span class="hint-icon">💡</span>
                        <span>DOA matris tablosunda ve onay denetim loglarında görüntülenecek açıklayıcı kademe başlığı.</span>
                    </div>
                </div>

                <!-- 2. BÜTÇE ARALIĞI & PARA BİRİMİ BÖLÜMÜ (MODERN KART) -->
                <div class="doa-modal-section-card">
                    <div class="doa-modal-section-header">
                        <div class="doa-modal-section-title">
                            <span>💰</span> Bütçe Aralığı ve Para Birimi
                        </div>
                        @if($canManage)
                            <button type="button" onclick="openCurrencyModal()" class="btn-manage-currencies-pill" title="Merkezi para birimlerini yönet (Yeni Ekle / Kur Güncelle)">
                                <span>⚙️</span> Para Birimlerini Yönet
                            </button>
                        @endif
                    </div>
                    <div style="display: grid; grid-template-columns: 1.25fr 1fr 1fr; gap: 14px;">
                        <div>
                            <label class="modal-form-label">Para Birimi <span class="text-danger">*</span></label>
                            <select name="currency_code" id="newRuleCurrency" class="modal-form-control currency-dynamic-select" required>
                                @foreach($currencies as $c)
                                    <option value="{{ $c->code }}" {{ $c->code === 'USD' ? 'selected' : '' }}>
                                        {{ $c->code }} ({{ $c->symbol }}) - {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="modal-form-label">Minimum Tutar <span class="text-danger">*</span></label>
                            <input type="text" inputmode="numeric" name="min_amount" id="newRuleMin" class="modal-form-control" placeholder="0" required oninput="handleAmountInput(this)" autocomplete="off">
                        </div>
                        <div>
                            <label class="modal-form-label">Maksimum Tutar</label>
                            <input type="text" inputmode="numeric" name="max_amount" id="newRuleMax" class="modal-form-control" placeholder="Boş = Limitsiz" oninput="handleAmountInput(this)" autocomplete="off">
                        </div>
                    </div>
                    <div class="field-hint" style="margin-top: 10px;">
                        <span class="hint-icon">💡</span>
                        <span>Belirtilen minimum ve maksimum tutar aralığındaki projeler bu kademeyle eşleşir. Maksimum boş bırakılırsa üst limitsiz ("... ve Üzeri") kabul edilir. Sayı girdikçe binlik ayracı otomatik eklenir.</span>
                    </div>
                </div>

                <!-- 3. HEDEF ONAY MERCİ / ATAMA TİPİ (DİNAMİK GÖRSEL KARTLAR) -->
                <div class="modal-form-group">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label class="modal-form-label mb-0">Hedef Onay Merci / Atama Tipi <span class="text-danger">*</span></label>
                        <span style="font-size: 10.5px; color: #0284c7; font-weight: 750; background: #e0f2fe; padding: 2px 8px; border-radius: 5px;">⚡ Dinamik Sistem Eşleşmesi</span>
                    </div>

                    <input type="hidden" name="approver_type" id="newApproverType" value="role">
                    
                    <div class="approver-type-grid" id="newApproverTypeGrid">
                        <button type="button" class="approver-type-card active" data-type="role" onclick="setApproverType('new', 'role')">
                            <span class="type-icon">🎭</span>
                            <span class="type-title">Rol / Makam</span>
                            <span class="type-desc">Bölüm Lideri, Direktör, GM vb.</span>
                        </button>
                        <button type="button" class="approver-type-card" data-type="committee" onclick="setApproverType('new', 'committee')">
                            <span class="type-icon">🏛️</span>
                            <span class="type-title">Kurul / Komite</span>
                            <span class="type-desc">Yatırım veya İcra Heyeti Oylaması</span>
                        </button>
                        <button type="button" class="approver-type-card" data-type="user" onclick="setApproverType('new', 'user')">
                            <span class="type-icon">👤</span>
                            <span class="type-title">Spesifik Kişi</span>
                            <span class="type-desc">Doğrudan Belirli Bir Yönetici</span>
                        </button>
                    </div>

                    <div class="field-hint mb-2">
                        <span class="hint-icon">💡</span>
                        <span>Bu tutar aralığındaki bir proje onaya sunulduğunda, kararın kurumsal bir unvana mı (Rol), çoklu oylama heyetine mi (Kurul) yoksa tekil bir kişiye mi yönlendirileceğini belirler.</span>
                    </div>

                    <!-- DİNAMİK HEDEF SEÇİCİLER -->
                    <div id="newRoleField" class="mt-2">
                        <label class="modal-form-label">Onay Verecek Rol / Makam <span class="text-danger">*</span></label>
                        <select name="role_id" id="newRoleId" class="modal-form-control">
                            <option value="">Rol Seçiniz...</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->slug }})</option>
                            @endforeach
                        </select>
                        <div class="field-hint">
                            <span class="hint-icon">ℹ️</span>
                            <span>Roller sistemdeki Dinamik Rol Tanımlarından beslenir. Bu role sahip herhangi bir yetkili projeyi onaylayabilir.</span>
                        </div>
                    </div>

                    <div id="newCommitteeField" class="mt-2" style="display: none;">
                        <label class="modal-form-label">Oylama Yapacak Kurul / Komite <span class="text-danger">*</span></label>
                        <select name="committee_id" id="newCommitteeId" class="modal-form-control">
                            <option value="">Kurul Seçiniz...</option>
                            @foreach($committees as $c)
                                <option value="{{ $c->id }}">🏛️ {{ $c->name }} (Karar Modeli: {{ $c->voting_type_label }})</option>
                            @endforeach
                        </select>
                        <div class="field-hint">
                            <span class="hint-icon">ℹ️</span>
                            <span>Kurullar listesi Kurul & Komite Yönetiminden beslenir. Proje kurulun salt çoğunluk/oy birliği modeline göre oylanır.</span>
                        </div>
                    </div>

                    <div id="newUserField" class="mt-2" style="display: none;">
                        <label class="modal-form-label">Onay Verecek Spesifik Kullanıcı <span class="text-danger">*</span></label>
                        <select name="user_id" id="newUserId" class="modal-form-control">
                            <option value="">Kullanıcı Seçiniz...</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }}) - {{ $u->unvan ?? 'Yetkili' }}</option>
                            @endforeach
                        </select>
                        <div class="field-hint">
                            <span class="hint-icon">ℹ️</span>
                            <span>Yalnızca seçilen bu spesifik kullanıcı onay paneline erişebilir ve karar verebilir.</span>
                        </div>
                    </div>
                </div>

                <!-- 4. ÖNCELİK SIRASI & ONAY GEREKSİNİMİ -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                    <div>
                        <label class="modal-form-label">Öncelik Sırası <span class="text-danger">*</span></label>
                        <input type="number" name="priority_order" class="modal-form-control" value="10" min="1" required>
                        <div class="field-hint">
                            <span class="hint-icon">💡</span>
                            <span><strong>Değerlendirme Sırası:</strong> Eşleşme aranırken kurallar 1'den başlayarak küçükten büyüğe taranır. İlk eşleşen kural devreye girer (1 = En öncelikli).</span>
                        </div>
                    </div>
                    <div>
                        <label class="modal-form-label">Onay Gereksinimi <span class="text-danger">*</span></label>
                        <select name="requires_approval" class="modal-form-control">
                            <option value="1">🔒 Onay Şart (Normal Onay Akışı)</option>
                            <option value="0">⚡ Onaysız Doğrudan Geçiş (Hızlı Devreye Alma)</option>
                        </select>
                        <div class="field-hint">
                            <span class="hint-icon">💡</span>
                            <span><strong>Hızlı Geçiş (Fast-Track):</strong> "Onaysız" seçilirse bu tutardaki projeler yöneticiyi beklemeden anında "Devam Ediyor" durumuna geçer.</span>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="rule_type" value="project_approval">

                <!-- 5. AÇIKLAMA / NOT -->
                <div class="modal-form-group mb-0">
                    <label class="modal-form-label">Açıklama / Kurumsal Politika Notu</label>
                    <textarea name="description" class="modal-form-control" rows="2" placeholder="Örn: 50.000 USD altındaki düşük bütçeli operasyonel projeler Bölüm Lideri onayına düşer..."></textarea>
                    <div class="field-hint">
                        <span class="hint-icon">💡</span>
                        <span>Bu kademenin kurumsal yetki matrisindeki gerekçesini belirten not. Tabloda alt bilgi olarak görüntülenecektir.</span>
                    </div>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeCustomModal('newThresholdModal')" style="padding: 7px 16px; border-radius: 7px;">İptal</button>
                <button type="submit" class="matrix-header-btn-primary" style="height: 38px; padding: 0 18px; border-radius: 7px;">
                    ✓ Kademeyi Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: EŞİK KADEMESİNİ DÜZENLE -->
<div id="editThresholdModal" class="custom-modal-backdrop" onclick="handleBackdropClick(event, 'editThresholdModal')">
    <div class="custom-modal-dialog">
        <form id="editThresholdForm" method="POST" onsubmit="cleanAmountBeforeSubmit(this)">
            @csrf
            @method('PUT')
            <div class="custom-modal-header">
                <div>
                    <h3 class="custom-modal-title">
                        <span style="font-size: 19px; background: #fef3c7; border: 1px solid #fde68a; width: 34px; height: 34px; border-radius: 9px; display: inline-flex; align-items: center; justify-content: center;">✏️</span>
                        <span>Eşik Kademesini Düzenle</span>
                    </h3>
                    <p style="margin: 4px 0 0 44px; font-size: 12px; color: #64748b; line-height: 1.35;">
                        Seçilen bütçe eşik kademesinin tutar aralığını, onay mercisini ve kurallarını güncelleyin.
                    </p>
                </div>
                <button type="button" class="custom-modal-close" onclick="closeCustomModal('editThresholdModal')" title="Kapat">✕</button>
            </div>
            <div class="custom-modal-body">
                <!-- 1. KADEME ADI -->
                <div class="modal-form-group">
                    <label class="modal-form-label">Kademe Adı <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="editRuleName" class="modal-form-control" required>
                    <div class="field-hint">
                        <span class="hint-icon">💡</span>
                        <span>DOA matris tablosunda ve onay denetim loglarında görüntülenecek açıklayıcı kademe başlığı.</span>
                    </div>
                </div>

                <!-- 2. BÜTÇE ARALIĞI & PARA BİRİMİ BÖLÜMÜ (MODERN KART) -->
                <div class="doa-modal-section-card">
                    <div class="doa-modal-section-header">
                        <div class="doa-modal-section-title">
                            <span>💰</span> Bütçe Aralığı ve Para Birimi
                        </div>
                        @if($canManage)
                            <button type="button" onclick="openCurrencyModal()" class="btn-manage-currencies-pill" title="Merkezi para birimlerini yönet (Yeni Ekle / Kur Güncelle)">
                                <span>⚙️</span> Para Birimlerini Yönet
                            </button>
                        @endif
                    </div>
                    <div style="display: grid; grid-template-columns: 1.25fr 1fr 1fr; gap: 14px;">
                        <div>
                            <label class="modal-form-label">Para Birimi <span class="text-danger">*</span></label>
                            <select name="currency_code" id="editRuleCurrency" class="modal-form-control currency-dynamic-select" required>
                                @foreach($currencies as $c)
                                    <option value="{{ $c->code }}">
                                        {{ $c->code }} ({{ $c->symbol }}) - {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="modal-form-label">Minimum Tutar <span class="text-danger">*</span></label>
                            <input type="text" inputmode="numeric" name="min_amount" id="editRuleMin" class="modal-form-control" required oninput="handleAmountInput(this)" autocomplete="off">
                        </div>
                        <div>
                            <label class="modal-form-label">Maksimum Tutar</label>
                            <input type="text" inputmode="numeric" name="max_amount" id="editRuleMax" class="modal-form-control" placeholder="Boş = Limitsiz" oninput="handleAmountInput(this)" autocomplete="off">
                        </div>
                    </div>
                    <div class="field-hint" style="margin-top: 10px;">
                        <span class="hint-icon">💡</span>
                        <span>Belirtilen minimum ve maksimum tutar aralığındaki projeler bu kademeyle eşleşir. Maksimum boş bırakılırsa üst limitsiz kabul edilir.</span>
                    </div>
                </div>

                <!-- 3. HEDEF ONAY MERCİ / ATAMA TİPİ (DİNAMİK GÖRSEL KARTLAR) -->
                <div class="modal-form-group">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <label class="modal-form-label mb-0">Hedef Onay Merci / Atama Tipi <span class="text-danger">*</span></label>
                        <span style="font-size: 10.5px; color: #0284c7; font-weight: 750; background: #e0f2fe; padding: 2px 8px; border-radius: 5px;">⚡ Dinamik Sistem Eşleşmesi</span>
                    </div>

                    <input type="hidden" name="approver_type" id="editApproverType" value="role">

                    <div class="approver-type-grid" id="editApproverTypeGrid">
                        <button type="button" class="approver-type-card active" data-type="role" onclick="setApproverType('edit', 'role')">
                            <span class="type-icon">🎭</span>
                            <span class="type-title">Rol / Makam</span>
                            <span class="type-desc">Bölüm Lideri, Direktör, GM vb.</span>
                        </button>
                        <button type="button" class="approver-type-card" data-type="committee" onclick="setApproverType('edit', 'committee')">
                            <span class="type-icon">🏛️</span>
                            <span class="type-title">Kurul / Komite</span>
                            <span class="type-desc">Yatırım veya İcra Heyeti Oylaması</span>
                        </button>
                        <button type="button" class="approver-type-card" data-type="user" onclick="setApproverType('edit', 'user')">
                            <span class="type-icon">👤</span>
                            <span class="type-title">Spesifik Kişi</span>
                            <span class="type-desc">Doğrudan Belirli Bir Yönetici</span>
                        </button>
                    </div>

                    <div class="field-hint mb-2">
                        <span class="hint-icon">💡</span>
                        <span>Bu tutar aralığındaki bir proje onaya sunulduğunda, kararın kurumsal bir unvana mı (Rol), çoklu oylama heyetine mi (Kurul) yoksa tekil bir kişiye mi yönlendirileceğini belirler.</span>
                    </div>

                    <!-- DİNAMİK HEDEF SEÇİCİLER -->
                    <div id="editRoleField" class="mt-2">
                        <label class="modal-form-label">Onay Verecek Rol / Makam <span class="text-danger">*</span></label>
                        <select name="role_id" id="editRoleId" class="modal-form-control">
                            <option value="">Rol Seçiniz...</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->slug }})</option>
                            @endforeach
                        </select>
                        <div class="field-hint">
                            <span class="hint-icon">ℹ️</span>
                            <span>Roller sistemdeki Dinamik Rol Tanımlarından beslenir. Bu role sahip herhangi bir yetkili projeyi onaylayabilir.</span>
                        </div>
                    </div>

                    <div id="editCommitteeField" class="mt-2" style="display: none;">
                        <label class="modal-form-label">Oylama Yapacak Kurul / Komite <span class="text-danger">*</span></label>
                        <select name="committee_id" id="editCommitteeId" class="modal-form-control">
                            <option value="">Kurul Seçiniz...</option>
                            @foreach($committees as $c)
                                <option value="{{ $c->id }}">🏛️ {{ $c->name }} (Karar Modeli: {{ $c->voting_type_label }})</option>
                            @endforeach
                        </select>
                        <div class="field-hint">
                            <span class="hint-icon">ℹ️</span>
                            <span>Kurullar listesi Kurul & Komite Yönetiminden beslenir. Proje kurulun salt çoğunluk/oy birliği modeline göre oylanır.</span>
                        </div>
                    </div>

                    <div id="editUserField" class="mt-2" style="display: none;">
                        <label class="modal-form-label">Onay Verecek Spesifik Kullanıcı <span class="text-danger">*</span></label>
                        <select name="user_id" id="editUserId" class="modal-form-control">
                            <option value="">Kullanıcı Seçiniz...</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }}) - {{ $u->unvan ?? 'Yetkili' }}</option>
                            @endforeach
                        </select>
                        <div class="field-hint">
                            <span class="hint-icon">ℹ️</span>
                            <span>Yalnızca seçilen bu spesifik kullanıcı onay paneline erişebilir ve karar verebilir.</span>
                        </div>
                    </div>
                </div>

                <!-- 4. ÖNCELİK SIRASI & ONAY GEREKSİNİMİ -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                    <div>
                        <label class="modal-form-label">Öncelik Sırası <span class="text-danger">*</span></label>
                        <input type="number" name="priority_order" id="editRulePriority" class="modal-form-control" required>
                        <div class="field-hint">
                            <span class="hint-icon">💡</span>
                            <span><strong>Değerlendirme Sırası:</strong> Eşleşme aranırken kurallar 1'den başlayarak küçükten büyüğe taranır. İlk eşleşen kural devreye girer (1 = En öncelikli).</span>
                        </div>
                    </div>
                    <div>
                        <label class="modal-form-label">Onay Gereksinimi <span class="text-danger">*</span></label>
                        <select name="requires_approval" id="editRuleRequiresApproval" class="modal-form-control">
                            <option value="1">🔒 Onay Şart (Normal Onay Akışı)</option>
                            <option value="0">⚡ Onaysız Doğrudan Geçiş (Hızlı Devreye Alma)</option>
                        </select>
                        <div class="field-hint">
                            <span class="hint-icon">💡</span>
                            <span><strong>Hızlı Geçiş (Fast-Track):</strong> "Onaysız" seçilirse bu tutardaki projeler yöneticiyi beklemeden anında "Devam Ediyor" durumuna geçer.</span>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="rule_type" value="project_approval">

                <!-- 5. AÇIKLAMA / NOT -->
                <div class="modal-form-group mb-0">
                    <label class="modal-form-label">Açıklama / Kurumsal Politika Notu</label>
                    <textarea name="description" id="editRuleDesc" class="modal-form-control" rows="2"></textarea>
                    <div class="field-hint">
                        <span class="hint-icon">💡</span>
                        <span>Bu kademenin kurumsal yetki matrisindeki gerekçesini belirten not. Tabloda alt bilgi olarak görüntülenecektir.</span>
                    </div>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeCustomModal('editThresholdModal')" style="padding: 7px 16px; border-radius: 7px;">Vazgeç</button>
                <button type="submit" class="matrix-header-btn-primary" style="height: 38px; padding: 0 18px; border-radius: 7px;">
                    ✓ Değişiklikleri Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MERKEZİ PARA BİRİMLERİ YÖNETİMİ MODALI -->
@include('partials.modals.currencies_modal')

<script>
// ==========================================
// PURE VANILLA CSS MODAL MOTORU
// ==========================================
function openCustomModal(id) {
    const el = document.getElementById(id);
    if (el) {
        el.classList.add('is-active');
        document.body.style.overflow = 'hidden';
    }
}

function closeCustomModal(id) {
    const el = document.getElementById(id);
    if (el) {
        el.classList.remove('is-active');
        document.body.style.overflow = '';
    }
}

function handleBackdropClick(e, id) {
    if (e.target && e.target.id === id) {
        closeCustomModal(id);
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.custom-modal-backdrop.is-active').forEach(m => {
            m.classList.remove('is-active');
        });
        document.body.style.overflow = '';
    }
});

function setApproverType(prefix, type) {
    const input = document.getElementById(prefix + 'ApproverType');
    if (input) input.value = type;

    const grid = document.getElementById(prefix + 'ApproverTypeGrid');
    if (grid) {
        grid.querySelectorAll('.approver-type-card').forEach(card => {
            if (card.getAttribute('data-type') === type) {
                card.classList.add('active');
            } else {
                card.classList.remove('active');
            }
        });
    }

    const roleField = document.getElementById(prefix + 'RoleField');
    const commField = document.getElementById(prefix + 'CommitteeField');
    const userField = document.getElementById(prefix + 'UserField');

    if (roleField) roleField.style.display = (type === 'role') ? 'block' : 'none';
    if (commField) commField.style.display = (type === 'committee') ? 'block' : 'none';
    if (userField) userField.style.display = (type === 'user') ? 'block' : 'none';
}

function toggleApproverFields(prefix) {
    const typeInput = document.getElementById(prefix + 'ApproverType');
    if (typeInput) {
        setApproverType(prefix, typeInput.value);
    }
}

// Binlik Ayraçlı Sayı Formatlama Fonksiyonları
function formatNumberWithDots(val) {
    if (val === null || val === undefined || val === '') return '';
    let str = val.toString().trim();
    let parts = str.split('.');
    let intPart = parts[0].replace(/\D/g, '');
    let decPart = parts.length > 1 && parseInt(parts[1], 10) > 0 ? ',' + parts[1] : '';
    if (!intPart) return '';
    return intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.') + decPart;
}

function handleAmountInput(input) {
    let cursorPos = input.selectionStart;
    let oldVal = input.value;
    let oldLen = oldVal.length;

    if (!oldVal) return;

    let parts = oldVal.split(',');
    let intPart = parts[0].replace(/\D/g, '');
    let decPart = parts.length > 1 ? ',' + parts[1].replace(/\D/g, '').substring(0, 2) : '';

    if (!intPart && !decPart) {
        input.value = '';
        return;
    }

    let formattedInt = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    input.value = formattedInt + decPart;

    let newLen = input.value.length;
    let diff = newLen - oldLen;
    let newCursorPos = Math.max(0, cursorPos + diff);
    input.setSelectionRange(newCursorPos, newCursorPos);
}

function cleanAmountBeforeSubmit(form) {
    const minInput = form.querySelector('[name="min_amount"]');
    const maxInput = form.querySelector('[name="max_amount"]');
    if (minInput && minInput.value) {
        minInput.value = minInput.value.replace(/\./g, '').replace(',', '.');
    }
    if (maxInput && maxInput.value) {
        maxInput.value = maxInput.value.replace(/\./g, '').replace(',', '.');
    }
}

function openEditThresholdModal(rule) {
    document.getElementById('editThresholdForm').action = `/admin/doa-esikleri/${rule.id}`;
    document.getElementById('editRuleName').value = rule.name || '';
    document.getElementById('editRuleCurrency').value = rule.currency_code || 'USD';
    
    // Tutar alanlarını noktalı binlik ayracı ile doldur
    document.getElementById('editRuleMin').value = formatNumberWithDots(rule.min_amount !== null ? rule.min_amount : 0);
    document.getElementById('editRuleMax').value = (rule.max_amount !== null && rule.max_amount !== undefined && rule.max_amount !== '') 
        ? formatNumberWithDots(rule.max_amount) 
        : '';

    setApproverType('edit', rule.approver_type || 'role');

    document.getElementById('editRoleId').value = rule.role_id || '';
    document.getElementById('editCommitteeId').value = rule.committee_id || '';
    document.getElementById('editUserId').value = rule.user_id || '';
    document.getElementById('editRulePriority').value = rule.priority_order || 10;
    document.getElementById('editRuleRequiresApproval').value = rule.requires_approval ? '1' : '0';
    document.getElementById('editRuleDesc').value = rule.description || '';

    openCustomModal('editThresholdModal');
}

// AJAX Global Motor Toggle
function toggleGlobalEngine(checkbox) {
    fetch('/admin/doa-esikleri/toggle-global-engine', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('engineStatusLabel').innerText = data.engine_active ? 'DEVREDE (AKTİF)' : 'DEVRE DIŞI (PASİF)';
        } else {
            checkbox.checked = !checkbox.checked;
        }
    })
    .catch(() => {
        checkbox.checked = !checkbox.checked;
        alert('Sunucuyla bağlantı hatası.');
    });
}

// AJAX Kural Aktif/Pasif
function toggleRuleActive(id, checkbox) {
    fetch(`/admin/doa-esikleri/${id}/toggle`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            checkbox.checked = !checkbox.checked;
        }
    })
    .catch(() => {
        checkbox.checked = !checkbox.checked;
    });
}

// Canlı Kural Simülasyonu
function runSimulation() {
    const amount = document.getElementById('simAmount').value;
    const currency = document.getElementById('simCurrency').value;

    fetch('/admin/doa-esikleri/simulate', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            amount: amount,
            currency: currency
        })
    })
    .then(r => r.json())
    .then(data => {
        const box = document.getElementById('simResultBox');
        const text = document.getElementById('simResultText');
        box.style.display = 'inline-flex';
        box.classList.remove('d-none');
        if (data.matched) {
            text.innerHTML = `✓ Eşleşen Kademe: <strong>${data.rule_name}</strong> → Hedef: <strong>${data.approver_name}</strong>`;
        } else {
            text.innerHTML = `ℹ️ Standart Onay Akışı Devreye Girer (Özel Eşik Yok)`;
        }
    })
    .catch(() => {
        alert('Simülasyon hesaplanamadı.');
    });
}

// =========================================================================
// MERKEZİ PARA BİRİMLERİ YÖNETİMİ & DOA ENTEGRASYONU
// =========================================================================
window.CURRENCIES_CACHE = window.CURRENCIES_CACHE || new Map();

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function openCurrencyModal() {
    const modal = document.getElementById('currencyModalBackdrop');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('is-active');
    }
    resetCurrencyForm();
    loadCurrenciesList();
}

function closeCurrencyModal() {
    const modal = document.getElementById('currencyModalBackdrop');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('is-active');
    }
}

function resetCurrencyForm() {
    const editId = document.getElementById('editCurrencyId');
    if (editId) editId.value = '';
    const code = document.getElementById('newCurrencyCode');
    if (code) code.value = '';
    const name = document.getElementById('newCurrencyName');
    if (name) name.value = '';
    const sym = document.getElementById('newCurrencySymbol');
    if (sym) sym.value = '';
    const rate = document.getElementById('newCurrencyRate');
    if (rate) rate.value = '1.0000';
    const isDef = document.getElementById('newCurrencyIsDefault');
    if (isDef) isDef.checked = false;
    const title = document.getElementById('currencyFormHeaderTitle');
    if (title) title.textContent = '➕ Yeni Para Birimi Ekle';
    const btnSave = document.getElementById('btnSaveCurrency');
    if (btnSave) btnSave.textContent = '✓ Para Birimini Kaydet';
    const btnCancel = document.getElementById('btnCancelEditCurrency');
    if (btnCancel) btnCancel.style.display = 'none';
}

function loadCurrenciesList() {
    fetch('{{ route("admin.currencies.index") }}', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;
        window.CURRENCIES_CACHE.clear();
        const tbody = document.getElementById('currencyModalTableBody');
        if (!tbody) return;
        tbody.innerHTML = '';
        data.currencies.forEach(cur => {
            window.CURRENCIES_CACHE.set(cur.code, cur);
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid #f1f5f9';
            const defaultBadge = cur.is_default 
                ? '<span class="badge" style="background: #dcfce7; color: #166534; font-weight: 700; font-size: 10px; margin-left: 6px;">Varsayılan</span>' 
                : '';
            tr.innerHTML = `
                <td style="padding: 10px 12px;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="font-weight: 800; font-size: 13px; color: #0f172a; font-family: monospace;">${escapeHtml(cur.code)}</span>
                        <span style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-weight: 700; color: #475569;">${escapeHtml(cur.symbol)}</span>
                        ${defaultBadge}
                    </div>
                </td>
                <td style="padding: 10px 12px;">
                    <strong style="color: #334155; font-size: 12.5px;">${escapeHtml(cur.name)}</strong>
                    <div style="font-size: 10.5px; color: #94a3b8;">Kur: ${parseFloat(cur.exchange_rate).toFixed(4)}</div>
                </td>
                <td style="padding: 10px 12px; text-align: right; white-space: nowrap;">
                    <div style="display: inline-flex; align-items: center; justify-content: flex-end; gap: 6px;">
                        <button type="button" class="btn btn-xs btn-outline-primary" onclick="editCurrencyItem(${cur.id})" style="padding: 4px 9px; font-weight: 600; border-radius: 5px; cursor: pointer;">✏️ Düzenle</button>
                        ${cur.is_default ? '' : `<button type="button" class="btn btn-xs btn-outline-danger" onclick="deleteCurrencyItem(${cur.id})" style="padding: 4px 9px; font-weight: 600; border-radius: 5px; cursor: pointer; color: #dc2626; border-color: #fca5a5;">🗑️ Sil</button>`}
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
        refreshCurrencySelectOptions(data.currencies);
    })
    .catch(err => console.error('Para birimleri yüklenirken hata:', err));
}

function refreshCurrencySelectOptions(currenciesList) {
    if (!Array.isArray(currenciesList)) return;

    // Hem eşik kademesi modallarını hem de simülatör selectini güncelle
    const selects = [
        document.getElementById('newRuleCurrency'),
        document.getElementById('editRuleCurrency'),
        document.getElementById('simCurrency')
    ];

    selects.forEach(sel => {
        if (!sel) return;
        const currentVal = sel.value;
        sel.innerHTML = '';
        currenciesList.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.code;
            if (sel.id === 'simCurrency') {
                opt.textContent = `${c.code}`;
            } else {
                opt.textContent = `${c.code} (${c.symbol}) - ${c.name}`;
            }
            if (c.code === currentVal || (!currentVal && c.is_default)) {
                opt.selected = true;
            }
            sel.appendChild(opt);
        });
    });
}

function editCurrencyItem(id) {
    let cur = null;
    for (let item of window.CURRENCIES_CACHE.values()) {
        if (Number(item.id) === Number(id)) { cur = item; break; }
    }
    if (!cur) return;

    document.getElementById('editCurrencyId').value = cur.id;
    document.getElementById('newCurrencyCode').value = cur.code;
    document.getElementById('newCurrencyName').value = cur.name;
    document.getElementById('newCurrencySymbol').value = cur.symbol;
    document.getElementById('newCurrencyRate').value = parseFloat(cur.exchange_rate).toFixed(4);
    document.getElementById('newCurrencyIsDefault').checked = Boolean(cur.is_default);
    document.getElementById('currencyFormHeaderTitle').textContent = `✏️ '${cur.code}' Para Birimini Düzenle`;
    document.getElementById('btnSaveCurrency').textContent = '✓ Güncellemeyi Kaydet';
    document.getElementById('btnCancelEditCurrency').style.display = 'inline';
    document.getElementById('newCurrencyCode').focus();
}

function saveCurrencyModalAction() {
    const editId = document.getElementById('editCurrencyId').value;
    const code = document.getElementById('newCurrencyCode').value.trim().toUpperCase();
    const name = document.getElementById('newCurrencyName').value.trim();
    const symbol = document.getElementById('newCurrencySymbol').value.trim();
    const exchange_rate = document.getElementById('newCurrencyRate').value;
    const is_default = document.getElementById('newCurrencyIsDefault').checked ? 1 : 0;

    if (!code || !name || !symbol) {
        alert('Lütfen Kod, Tanım ve Sembol alanlarını eksiksiz doldurunuz.');
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    const isEdit = Boolean(editId);
    const url = isEdit ? `{{ url('admin/currencies') }}/${editId}` : `{{ route('admin.currencies.store') }}`;
    const method = isEdit ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ code, name, symbol, exchange_rate, is_default, _token: csrfToken })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            resetCurrencyForm();
            loadCurrenciesList();
        } else {
            alert(res.message || 'Hata oluştu.');
        }
    })
    .catch(e => alert('İşlem sırasında sunucu hatası oluştu.'));
}

function deleteCurrencyItem(id) {
    let cur = null;
    for (let item of window.CURRENCIES_CACHE.values()) {
        if (Number(item.id) === Number(id)) { cur = item; break; }
    }
    const curCode = cur ? cur.code : 'Seçili para birimi';

    const executeDelete = () => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        fetch('{{ url("admin/currencies") }}/' + id, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ _token: csrfToken, _method: 'DELETE' })
        })
        .then(async (r) => {
            if (!r.ok) {
                const fallback = await fetch('{{ url("admin/currencies") }}/' + id + '/delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ _token: csrfToken })
                });
                return fallback.json();
            }
            return r.json();
        })
        .then(data => {
            if (data.success) {
                resetCurrencyForm();
                loadCurrenciesList();
            } else {
                alert(data.message || 'Silme işlemi gerçekleştirilemedi.');
            }
        })
        .catch(err => alert('Silme işlemi sırasında hata oluştu.'));
    };

    if (typeof window.showAppConfirmModal === 'function') {
        window.showAppConfirmModal({
            title: 'Para Birimini Sil',
            message: `'${curCode}' para birimini silmek istediğinize emin misiniz?`,
            details: 'Bu para birimi DOA eşikleri ve proje seçeneklerinden kaldırılır.',
            confirmButtonText: 'Evet, Para Birimini Sil',
            onConfirm: executeDelete
        });
    } else {
        if (confirm(`'${curCode}' para birimini silmek istediğinize emin misiniz?`)) {
            executeDelete();
        }
    }
}

// Sayfa yüklendiğinde mevcut para birimlerini önbelleğe al
document.addEventListener('DOMContentLoaded', function() {
    try {
        const initialCurrencies = @json($currencies);
        if (Array.isArray(initialCurrencies)) {
            initialCurrencies.forEach(cur => window.CURRENCIES_CACHE.set(cur.code, cur));
        }
    } catch(e) {}
});
</script>
@endsection
