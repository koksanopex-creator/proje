@extends('layouts.app')

@section('title', 'Kurullar & Komiteler Yönetimi - Köksan Enterprise PPM')

@section('content')
@php
    $canManage = auth()->user()->isDeveloper() || auth()->user()->hasRole('admin') || auth()->user()->hasPermission('manage_committees');
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

/* Ferah & Ayrılmış Toolbar */
.committee-toolbar-container {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 14px 18px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.toolbar-top-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 12px;
}
.search-input-wrap {
    position: relative;
    flex: 1;
    min-width: 260px;
    max-width: 440px;
}
.search-input-wrap input {
    width: 100%;
    height: 38px;
    padding-left: 36px;
    padding-right: 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 13px;
    transition: all 0.15s;
    background: #f8fafc;
    box-sizing: border-box;
}
.search-input-wrap input:focus {
    outline: none;
    border-color: #0284c7;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
}
.search-input-wrap .search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 14px;
    pointer-events: none;
}

.toolbar-bottom-row {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    padding-top: 10px;
    border-top: 1px solid #f1f5f9;
}
.filter-chip {
    padding: 6px 14px;
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
    gap: 6px;
    text-decoration: none;
}
.filter-chip:hover {
    background: #f8fafc;
    border-color: #94a3b8;
    color: #0f172a;
}
.filter-chip.active {
    background: #0284c7;
    border-color: #0284c7;
    color: #ffffff;
    box-shadow: 0 2px 4px rgba(2, 132, 199, 0.25);
}

.view-toggle-btn {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #64748b;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.15s;
    font-size: 14px;
}
.view-toggle-btn.active {
    background: #0284c7;
    border-color: #0284c7;
    color: #ffffff;
}

/* ==========================================================================
   ORANTILI & FERAH KURUL KARTLARI
   ========================================================================== */
.committee-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(460px, 1fr));
    gap: 24px;
}

.modern-committee-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    box-shadow: 0 2px 5px rgba(15, 23, 42, 0.04);
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    position: relative;
}
.modern-committee-card:hover {
    box-shadow: 0 10px 24px -4px rgba(15, 23, 42, 0.08);
    border-color: #cbd5e1;
}

.card-top-accent {
    height: 4px;
    width: 100%;
}

.card-header-row {
    padding: 18px 22px 10px 22px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
}

.comm-icon-box {
    width: 44px;
    height: 44px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
    border: 1px solid #e2e8f0;
}

.comm-title-wrap h3 {
    font-size: 17px;
    font-weight: 750;
    color: #0f172a;
    margin: 0 0 5px 0;
    line-height: 1.3;
}
.comm-slug-pill {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 11px;
    background: #f1f5f9;
    color: #475569;
    padding: 2px 7px;
    border-radius: 4px;
    border: 1px solid #e2e8f0;
}

.comm-description-text {
    font-size: 12.5px;
    color: #64748b;
    margin: 6px 0 16px 0;
    padding: 0 22px;
    line-height: 1.5;
}

.comm-actions-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}
.comm-btn-action {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    height: 30px;
    padding: 0 11px;
    font-size: 12px;
    font-weight: 650;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #334155;
    transition: all 0.15s ease;
    white-space: nowrap;
}
.comm-btn-action:hover {
    background: #f8fafc;
    border-color: #94a3b8;
    color: #0f172a;
}
.comm-btn-action.btn-add {
    background: #eff6ff;
    border-color: #bfdbfe;
    color: #1d4ed8;
}
.comm-btn-action.btn-add:hover {
    background: #dbeafe;
    border-color: #93c5fd;
    color: #1e40af;
}
.comm-btn-action.btn-delete {
    background: #fff;
    border-color: #fecaca;
    color: #dc2626;
}
.comm-btn-action.btn-delete:hover {
    background: #fef2f2;
    border-color: #fca5a5;
    color: #b91c1c;
}

/* Karar Modeli Şeridi */
.comm-meta-strip {
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
    border-bottom: 1px solid #f1f5f9;
    padding: 10px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 14px;
}
.voting-model-badge {
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

/* Kurul Yetki ve Görev Kapsamı Rozetleri */
.comm-authorities-strip {
    padding: 0 22px 14px 22px;
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.auth-pill {
    font-size: 11px;
    font-weight: 650;
    padding: 2.5px 8px;
    border-radius: 5px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

/* Başkan Spotlight */
.comm-chairman-spotlight {
    margin: 0 22px 14px 22px;
    padding: 9px 14px;
    border-radius: 8px;
    background: #fffbeb;
    border: 1px solid #fef3c7;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.comm-chairman-spotlight .name {
    font-size: 13px;
    font-weight: 750;
    color: #78350f;
}

/* Üyeler Bölümü */
.comm-members-section {
    padding: 0 22px 18px 22px;
    flex: 1;
}
.comm-members-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}
.comm-members-title {
    font-size: 12px;
    font-weight: 750;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

.members-roster-list {
    max-height: 220px;
    overflow-y: auto;
}
.member-row-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 7px 12px;
    background: #ffffff;
    border: 1px solid #f1f5f9;
    border-radius: 8px;
    margin-bottom: 5px;
    transition: all 0.15s ease;
}
.member-row-item:hover {
    background: #f8fafc;
    border-color: #e2e8f0;
}
.member-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #334155;
    font-size: 11.5px;
    font-weight: 750;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.member-info .m-name {
    font-size: 12.5px;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.2;
}
.member-info .m-email {
    font-size: 11px;
    color: #64748b;
}
.btn-remove-member {
    width: 24px;
    height: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 5px;
    border: none;
    background: transparent;
    color: #94a3b8;
    cursor: pointer;
    font-size: 13px;
    font-weight: 700;
    transition: all 0.15s ease;
    padding: 0;
}
.btn-remove-member:hover {
    background: #fee2e2;
    color: #dc2626;
}

/* Switch */
.switch-sm {
    position: relative;
    display: inline-block;
    width: 34px;
    height: 20px;
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
    border-radius: 20px;
}
.slider-sm:before {
    position: absolute;
    content: "";
    height: 14px;
    width: 14px;
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
    transform: translateX(14px);
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
.custom-modal-dialog {
    max-width: 540px;
    width: 100%;
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
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
    padding: 18px 24px;
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.custom-modal-title {
    margin: 0;
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 8px;
}
.custom-modal-close {
    background: none;
    border: none;
    font-size: 18px;
    font-weight: 700;
    color: #94a3b8;
    cursor: pointer;
    line-height: 1;
    padding: 4px;
}
.custom-modal-close:hover {
    color: #0f172a;
}
.custom-modal-body {
    padding: 24px;
    overflow-y: auto;
    font-size: 13px;
}
.custom-modal-footer {
    padding: 14px 24px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
}

/* Form Grupları ve Ferah Boşluklar */
.modal-form-group {
    margin-bottom: 18px;
}
.modal-form-label {
    display: block;
    font-size: 12px;
    font-weight: 750;
    color: #334155;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 8px;
}
.modal-form-control {
    width: 100%;
    height: 40px;
    border: 1.5px solid #cbd5e1;
    border-radius: 8px;
    padding: 0 12px;
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
    padding: 10px 12px;
}

/* Yetki Seçim Grubu (Modal İçi) */
.modal-authority-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 14px;
    margin-bottom: 18px;
}
.modal-authority-title {
    font-size: 11.5px;
    font-weight: 750;
    color: #0f172a;
    text-transform: uppercase;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.auth-checkbox-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}
.auth-checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 600;
    color: #334155;
    cursor: pointer;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    padding: 6px 10px;
    border-radius: 6px;
    transition: all 0.15s ease;
}
.auth-checkbox-label:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}
.auth-checkbox-label input[type="checkbox"] {
    accent-color: #0284c7;
    width: 15px;
    height: 15px;
}

/* Bildirim Kutusu */
.modal-notif-box {
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 10px;
    padding: 12px 14px;
    margin-top: 14px;
}
.modal-notif-title {
    font-size: 11.5px;
    font-weight: 750;
    color: #0369a1;
    text-transform: uppercase;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.modal-checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 600;
    color: #334155;
    cursor: pointer;
    margin-bottom: 6px;
}
.modal-checkbox-label input[type="checkbox"] {
    accent-color: #0284c7;
    width: 15px;
    height: 15px;
}

/* Kurula Üye Ekle Kullanıcı Seçici & Arama Stilleri */
.member-user-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 7px 10px;
    border-radius: 8px;
    border: 1.5px solid #f1f5f9;
    background: #ffffff;
    cursor: pointer;
    transition: all 0.15s ease;
    user-select: none;
}
.member-user-item:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}
.member-user-item.is-selected {
    background: #f0fdf4 !important;
    border-color: #10b981 !important;
    box-shadow: 0 0 0 1px #10b981;
}
.member-user-item.is-disabled {
    opacity: 0.65;
    background: #f8fafc;
    cursor: not-allowed;
}
</style>

<div class="container-fluid px-4 py-3">

    <!-- Üst Başlık & Eylemler -->
    <div class="matrix-page-header">
        <div class="header-left">
            <h1 class="page-title">
                <span>🏛️</span> Kurullar, Komiteler & Oylama Meclisleri
            </h1>
            <p class="page-subtitle">
                Yüksek bütçeli yatırımlar, teknik heyetler ve stratejik yönetim kararları için dinamik kurullar tanımlayın, oylama ve nisap modelleri atayın.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.doa_thresholds.index') }}" class="matrix-header-btn-secondary">
                <span>⚖️</span> DOA Bütçe Matrisi
            </a>
            @if($canManage)
                <button type="button" class="matrix-header-btn-primary" onclick="openCustomModal('newCommitteeModal')">
                    <span>+</span> Yeni Kurul Oluştur
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
        <a href="{{ route('admin.doa_thresholds.index') }}" class="matrix-tab-link">
            <span>⚖️</span> <span>Bütçe Eşik Matrisi (DOA)</span>
        </a>
        <a href="{{ route('admin.committees.index') }}" class="matrix-tab-link active">
            <span>🏛️</span> <span>Kurullar & Komiteler</span>
        </a>
        <a href="{{ route('admin.audit_logs') }}" class="matrix-tab-link">
            <span>📋</span> <span>Log Görünürlük Matrisi</span>
        </a>
        <a href="{{ route('admin.system_health') }}" class="matrix-tab-link">
            <span>🩺</span> <span>Sistem Sağlığı</span>
        </a>
        <a href="{{ route('admin.notification_logs.index') }}" class="matrix-tab-link">
            <span>📧</span> <span>Bildirim & Mail Logları</span>
        </a>
    </div>

    <!-- MINIMAL & KİBAR YAN YANA KPI STRIP -->
    <div class="minimal-kpi-strip">
        <div class="minimal-kpi-card">
            <div class="minimal-kpi-icon" style="background: #eef2ff; color: #4f46e5;">🏛️</div>
            <div class="minimal-kpi-info">
                <span class="minimal-kpi-label">Toplam Kurul</span>
                <span class="minimal-kpi-val">{{ $stats['total'] }} <small style="font-size: 11px; font-weight: 600; color: #10b981;">({{ $stats['active'] }} Aktif)</small></span>
            </div>
        </div>

        <div class="minimal-kpi-card">
            <div class="minimal-kpi-icon" style="background: #ecfdf5; color: #059669;">👥</div>
            <div class="minimal-kpi-info">
                <span class="minimal-kpi-label">Atanmış Sandalye</span>
                <span class="minimal-kpi-val">{{ $stats['total_seats'] }} <small style="font-size: 11px; font-weight: 500; color: #64748b;">({{ $stats['unique_users'] }} Kişi)</small></span>
            </div>
        </div>

        <div class="minimal-kpi-card">
            <div class="minimal-kpi-icon" style="background: #fff7ed; color: #ea580c;">⚡</div>
            <div class="minimal-kpi-info">
                <span class="minimal-kpi-label">Toplam Oy Gücü</span>
                <span class="minimal-kpi-val">{{ number_format($stats['voting_power'], 1) }}x <small style="font-size: 11px; font-weight: 500; color: #64748b;">(Nisap)</small></span>
            </div>
        </div>

        <div class="minimal-kpi-card">
            <div class="minimal-kpi-icon" style="background: #f0f9ff; color: #0284c7;">⏳</div>
            <div class="minimal-kpi-info">
                <span class="minimal-kpi-label">Bekleyen Onay</span>
                <span class="minimal-kpi-val">{{ $stats['pending_approvals'] }} <small style="font-size: 11px; font-weight: 600; color: {{ $stats['pending_approvals'] > 0 ? '#ea580c' : '#10b981' }};">{{ $stats['pending_approvals'] > 0 ? 'İncelemede' : 'Güncel' }}</small></span>
            </div>
        </div>
    </div>

    <!-- FERAH & AYRILMIŞ TOOLBAR (HİÇBİR ZAMAN ÜST ÜSTE BİNMEZ) -->
    <div class="committee-toolbar-container">
        <!-- Satır 1: Arama ve Görünüm Seçici -->
        <div class="toolbar-top-row">
            <div class="search-input-wrap">
                <span class="search-icon">🔍</span>
                <input type="text" id="committeeSearchInput" placeholder="Kurul adı, slug veya üye personelleri ara..." onkeyup="filterCommittees()">
            </div>
            <div class="btn-group" role="group">
                <button type="button" class="view-toggle-btn active" id="btnViewGrid" onclick="switchView('grid')" title="Kart Görünümü">
                    🎴
                </button>
                <button type="button" class="view-toggle-btn" id="btnViewTable" onclick="switchView('table')" title="Tablo Görünümü">
                    📋
                </button>
            </div>
        </div>

        <!-- Satır 2: Filtre Çipleri (Ayrı Satır, Asla Sıkışmaz) -->
        <div class="toolbar-bottom-row">
            <span style="font-size: 11.5px; font-weight: 750; color: #64748b; margin-right: 6px;">FİLTRELE:</span>
            <a href="javascript:void(0)" class="filter-chip active" data-filter="all" onclick="setCommitteeFilter('all', this)">
                Tümü ({{ $committees->count() }})
            </a>
            <a href="javascript:void(0)" class="filter-chip" data-filter="active" onclick="setCommitteeFilter('active', this)">
                🟢 Aktif Meclisler
            </a>
            <a href="javascript:void(0)" class="filter-chip" data-filter="majority" onclick="setCommitteeFilter('majority', this)">
                ⚖️ Oy Çokluğu (%50+)
            </a>
            <a href="javascript:void(0)" class="filter-chip" data-filter="unanimous" onclick="setCommitteeFilter('unanimous', this)">
                🔒 Oy Birliği (%100)
            </a>
            <a href="javascript:void(0)" class="filter-chip" data-filter="quorum" onclick="setCommitteeFilter('quorum', this)">
                🎯 Özel Nisap Oranı
            </a>
        </div>
    </div>

    <!-- GÖRÜNÜM 1: DENGELİ & FERAH KART GRİDİ (VARSAYILAN AKTİF) -->
    <div id="committeesGridView" class="committee-grid" style="display: grid;">
        @forelse($committees as $c)
            @php
                $president = $c->members->firstWhere('member_role', 'baskan');
                $totalWeight = $c->activeMembers->sum('voting_weight');
                $iconDisplay = !empty($c->icon) && mb_strlen($c->icon) <= 4 ? $c->icon : '🏛️';
            @endphp
            <div class="committee-item" 
                 data-name="{{ mb_strtolower($c->name) }}" 
                 data-slug="{{ mb_strtolower($c->slug) }}" 
                 data-active="{{ $c->is_active ? 'true' : 'false' }}" 
                 data-voting="{{ $c->voting_type }}"
                 data-members="{{ mb_strtolower($c->members->pluck('user.name')->implode(' ')) }}">
                
                <div class="modern-committee-card">
                    <!-- Üst Renk Çizgisi -->
                    <div class="card-top-accent" style="background: {{ $c->color ?? '#0284c7' }};"></div>

                    <!-- Kart Başlık Satırı -->
                    <div class="card-header-row">
                        <div class="d-flex align-items-start gap-3">
                            <div class="comm-icon-box" style="background: {{ $c->color ? $c->color . '15' : '#0284c715' }}; color: {{ $c->color ?? '#0284c7' }};">
                                {{ $iconDisplay }}
                            </div>
                            <div class="comm-title-wrap">
                                <h3>{{ $c->name }}</h3>
                                <span class="comm-slug-pill">{{ $c->slug }}</span>
                            </div>
                        </div>

                        <!-- Aksiyon Butonları (Yazı şeklinde değil, gerçek butonlar!) -->
                        <div class="comm-actions-bar">
                            @if($canManage)
                                <label class="switch-sm" title="Kurulu Aktif/Pasif Yap">
                                    <input type="checkbox" {{ $c->is_active ? 'checked' : '' }} onchange="toggleCommitteeActive({{ $c->id }}, this)">
                                    <span class="slider-sm"></span>
                                </label>
                                <button type="button" class="comm-btn-action btn-add" onclick="openAddMemberModal({{ $c->id }}, '{{ addslashes($c->name) }}')" title="Üye Ekle">
                                    <span>+ Üye</span>
                                </button>
                                <button type="button" class="comm-btn-action" onclick="openEditCommitteeModal({{ json_encode($c) }})" title="Kurulu Düzenle">
                                    <span>✏️</span>
                                </button>
                                <form action="{{ route('admin.committees.destroy', $c->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu kurulu ve tüm üyeliklerini silmek istediğinize emin misiniz?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="comm-btn-action btn-delete" title="Kurulu Sil">
                                        <span>🗑️</span>
                                    </button>
                                </form>
                            @else
                                <span class="badge {{ $c->is_active ? 'bg-success' : 'bg-secondary' }}" style="font-size: 11px;">
                                    {{ $c->is_active ? 'Aktif' : 'Pasif' }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Açıklama (Dipdibe olmaması için ferah boşluk) -->
                    @if($c->description)
                        <div class="comm-description-text">{{ $c->description }}</div>
                    @else
                        <div style="height: 10px;"></div>
                    @endif

                    <!-- Karar & Nisap Şeridi -->
                    <div class="comm-meta-strip">
                        @if($c->voting_type === 'majority')
                            <span class="voting-model-badge" style="background: #e0e7ff; color: #3730a3;">
                                ⚖️ Oy Çokluğu (%50+ Onay)
                            </span>
                        @elseif($c->voting_type === 'unanimous')
                            <span class="voting-model-badge" style="background: #fee2e2; color: #991b1b;">
                                🔒 Oy Birliği (Tüm Üyelerin Onayı Şart)
                            </span>
                        @elseif($c->voting_type === 'single_any')
                            <span class="voting-model-badge" style="background: #dcfce7; color: #166534;">
                                ⚡ Tek Üye Onayı Yeterli
                            </span>
                        @else
                            <span class="voting-model-badge" style="background: #fef3c7; color: #92400e;">
                                🎯 Özel Nisap: %{{ $c->quorum_percentage }} Onay Eşiği
                            </span>
                        @endif

                        <span class="small text-muted" style="font-size: 11.5px;">
                            Toplam Oy Gücü: <strong style="color: #0f172a;">{{ number_format($totalWeight, 1) }}x</strong>
                        </span>
                    </div>

                    <!-- Yetki ve Görev Kapsamı Rozetleri (Proje İlişkisi) -->
                    <div class="comm-authorities-strip">
                        @if($c->hasAuthority('can_approve'))
                            <span class="auth-pill" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;" title="Projeyi onaylayıp devreye alma veya bir sonraki faza geçirme yetkisi">
                                ✓ Onay Yetkisi
                            </span>
                        @endif
                        @if($c->hasAuthority('can_reject'))
                            <span class="auth-pill" style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;" title="Projeyi reddetme yetkisi">
                                ✕ Ret Yetkisi
                            </span>
                        @endif
                        @if($c->hasAuthority('can_conditional_approve'))
                            <span class="auth-pill" style="background: #fffbeb; color: #92400e; border: 1px solid #fde68a;" title="Ek şartla onaylama veya revizyon isteme yetkisi">
                                ⚠️ Şartlı Onay
                            </span>
                        @endif
                        @if($c->hasAuthority('can_veto'))
                            <span class="auth-pill" style="background: #faf5ff; color: #6b21a8; border: 1px solid #e9d5ff;" title="Tek oyla durdurma (veto) yetkisi">
                                🛡️ Veto Hakkı
                            </span>
                        @endif
                        @if($c->hasAuthority('scope_budget'))
                            <span class="auth-pill" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe;" title="DOA bütçe kademelerine bağlı onay mercii">
                                ⚖️ DOA Bütçe
                            </span>
                        @endif
                        @if($c->hasAuthority('scope_isg'))
                            <span class="auth-pill" style="background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0;" title="İSG ve Çevre kriter denetimi">
                                🦺 İSG Denetimi
                            </span>
                        @endif
                        @if($c->hasAuthority('scope_phases'))
                            <span class="auth-pill" style="background: #f8fafc; color: #475569; border: 1px solid #cbd5e1;" title="Faz ve aşama kapıları geçiş onayı">
                                🚩 Faz Geçişi
                            </span>
                        @endif
                    </div>

                    <!-- Kurul Başkanı Spotlight (Kibar & Tek Satır) -->
                    @if($president)
                        <div class="comm-chairman-spotlight">
                            <div class="d-flex align-items-center gap-2 overflow-hidden">
                                <div class="member-avatar" style="background: #fbbf24; color: #78350f; font-size: 11px;">
                                    ⭐
                                </div>
                                <div class="text-truncate">
                                    <div class="name text-truncate">
                                        {{ $president->user?->name ?? 'Atanmamış' }}
                                        <span class="fw-normal text-muted" style="font-size: 11px;">({{ $president->user?->email }})</span>
                                    </div>
                                </div>
                            </div>
                            <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 11px; font-weight: 700; border: 1px solid #fde68a;">
                                {{ $president->voting_weight }}x Oy Ağırlığı
                            </span>
                        </div>
                    @else
                        <div class="comm-chairman-spotlight" style="background: #f8fafc; border-color: #e2e8f0;">
                            <span class="small text-muted" style="font-size: 11.5px;">👑 Kurul Başkanı atanmamış.</span>
                            @if($canManage)
                                <button type="button" class="btn btn-xs btn-outline-primary" style="font-size: 11px; padding: 2px 8px; border-radius: 4px;" onclick="openAddMemberModal({{ $c->id }}, '{{ addslashes($c->name) }}', 'baskan')">
                                    + Başkan Ata
                                </button>
                            @endif
                        </div>
                    @endif

                    <!-- Üyeler Listesi (Kibar Roster) -->
                    <div class="comm-members-section">
                        <div class="comm-members-header">
                            <span class="comm-members-title">
                                👥 Kurul Üyeleri ({{ $c->members->count() }})
                            </span>
                        </div>

                        <div class="members-roster-list">
                            @forelse($c->members as $m)
                                <div class="member-row-item">
                                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                                        <div class="member-avatar">
                                            {{ mb_substr($m->user?->name ?? 'U', 0, 1) }}
                                        </div>
                                        <div class="member-info text-truncate">
                                            <div class="m-name text-truncate">{{ $m->user?->name ?? 'Kullanıcı' }}</div>
                                            <div class="m-email text-truncate">{{ $m->user?->email }}</div>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                        @if($m->member_role === 'baskan')
                                            <span class="badge" style="background: #fef3c7; color: #b45309; font-size: 10px; border: 1px solid #fde68a;">⭐ Başkan</span>
                                        @elseif($m->member_role === 'raportor')
                                            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 10px; border: 1px solid #bae6fd;">📝 Raportör</span>
                                        @elseif($m->member_role === 'yedek')
                                            <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 10px; border: 1px solid #e2e8f0;">Yedek</span>
                                        @else
                                            <span class="badge" style="background: #f8fafc; color: #475569; font-size: 10px; border: 1px solid #e2e8f0;">Üye</span>
                                        @endif

                                        <span style="font-size: 11px; font-weight: 700; color: #334155;">
                                            {{ $m->voting_weight }}x
                                        </span>

                                        @if($canManage)
                                            <button type="button" class="btn-remove-member" title="Üyeyi Çıkar" onclick="confirmRemoveMember({{ $c->id }}, {{ $m->id }}, '{{ addslashes($m->user?->name ?? 'Üye') }}', '{{ addslashes($c->name) }}')">
                                                ✕
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-3 text-muted small border border-dashed rounded-2 bg-light">
                                    Bu kurula henüz üye atanmamış.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="p-4 bg-white rounded-3 border border-dashed text-center">
                    <div style="font-size: 32px; margin-bottom: 8px;">🏛️</div>
                    <h5 class="fw-bold text-slate-800">Henüz Tanımlı Bir Kurul Bulunmamaktadır</h5>
                    <p class="text-muted small mb-3">Yatırım, İSG veya bütçe onay kurullarınızı oluşturarak hemen başlayabilirsiniz.</p>
                    @if($canManage)
                        <button type="button" class="matrix-header-btn-primary" onclick="openCustomModal('newCommitteeModal')">
                            + İlk Kurulu Oluştur
                        </button>
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    <!-- GÖRÜNÜM 2: ENTERPRISE TABLO LİSTESİ (VARSAYILAN GİZLİ - ASLA ÜST ÜSTE BİNMEZ) -->
    <div id="committeesTableView" class="card border-0 shadow-sm rounded-3 overflow-hidden d-none" style="display: none;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 12.5px;">
                <thead class="table-light text-muted">
                    <tr>
                        <th style="width: 260px;">Kurul & Komite Adı</th>
                        <th>Karar Modeli</th>
                        <th>Yetki Kapsamı</th>
                        <th>Kurul Başkanı</th>
                        <th>Üye Sayısı</th>
                        <th class="text-center" style="width: 100px;">Toplam Oy</th>
                        <th class="text-center" style="width: 80px;">Durum</th>
                        <th class="text-end" style="width: 140px;">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($committees as $c)
                        @php
                            $president = $c->members->firstWhere('member_role', 'baskan');
                            $totalWeight = $c->activeMembers->sum('voting_weight');
                        @endphp
                        <tr class="committee-table-row" 
                            data-name="{{ mb_strtolower($c->name) }}" 
                            data-slug="{{ mb_strtolower($c->slug) }}" 
                            data-active="{{ $c->is_active ? 'true' : 'false' }}" 
                            data-voting="{{ $c->voting_type }}"
                            data-members="{{ mb_strtolower($c->members->pluck('user.name')->implode(' ')) }}">
                            <td>
                                <div class="fw-bold text-slate-800">{{ $c->name }}</div>
                                <code class="small text-muted">{{ $c->slug }}</code>
                            </td>
                            <td>
                                @if($c->voting_type === 'majority')
                                    <span class="badge" style="background: #e0e7ff; color: #3730a3;">⚖️ Oy Çokluğu (%50+)</span>
                                @elseif($c->voting_type === 'unanimous')
                                    <span class="badge" style="background: #fee2e2; color: #991b1b;">🔒 Oy Birliği</span>
                                @elseif($c->voting_type === 'single_any')
                                    <span class="badge" style="background: #dcfce7; color: #166534;">⚡ Tek Üye Onayı</span>
                                @else
                                    <span class="badge" style="background: #fef3c7; color: #92400e;">🎯 Nisap %{{ $c->quorum_percentage }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1 flex-wrap">
                                    @if($c->hasAuthority('can_approve')) <span class="badge bg-success-subtle text-success">✓ Onay</span> @endif
                                    @if($c->hasAuthority('can_reject')) <span class="badge bg-danger-subtle text-danger">✕ Ret</span> @endif
                                    @if($c->hasAuthority('can_conditional_approve')) <span class="badge bg-warning-subtle text-warning">⚠️ Şartlı</span> @endif
                                    @if($c->hasAuthority('scope_budget')) <span class="badge bg-primary-subtle text-primary">⚖️ DOA</span> @endif
                                </div>
                            </td>
                            <td>
                                @if($president)
                                    <div class="fw-semibold text-slate-800">⭐ {{ $president->user?->name }}</div>
                                    <div class="text-muted" style="font-size: 11px;">{{ $president->voting_weight }}x Katsayı</div>
                                @else
                                    <span class="text-muted small">Atanmamış</span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold text-slate-700">{{ $c->members->count() }} Üye</span>
                            </td>
                            <td class="text-center fw-bold text-slate-700">
                                {{ number_format($totalWeight, 1) }}x
                            </td>
                            <td class="text-center">
                                @if($canManage)
                                    <label class="switch-sm" title="Kurulu Aktif/Pasif Yap">
                                        <input type="checkbox" {{ $c->is_active ? 'checked' : '' }} onchange="toggleCommitteeActive({{ $c->id }}, this)">
                                        <span class="slider-sm"></span>
                                    </label>
                                @else
                                    <span class="badge {{ $c->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $c->is_active ? 'Aktif' : 'Pasif' }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($canManage)
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        <button type="button" class="comm-btn-action btn-add" onclick="openAddMemberModal({{ $c->id }}, '{{ addslashes($c->name) }}')">
                                            + Üye
                                        </button>
                                        <button type="button" class="comm-btn-action" onclick="openEditCommitteeModal({{ json_encode($c) }})">
                                            ✏️
                                        </button>
                                        <form action="{{ route('admin.committees.destroy', $c->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu kurulu silmek istediğinize emin misiniz?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="comm-btn-action btn-delete">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-muted small">Görüntüleme</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Tanımlı kurul bulunamadı.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ==========================================================================
     VANILLA CSS MODALLAR (FERAH ALANLAR, BOŞLUKLU VE TEMİZ FORMLAR)
     ========================================================================== -->

<!-- MODAL 1: YENİ KURUL OLUŞTUR -->
<div id="newCommitteeModal" class="custom-modal-backdrop" onclick="handleBackdropClick(event, 'newCommitteeModal')">
    <div class="custom-modal-dialog">
        <form action="{{ route('admin.committees.store') }}" method="POST">
            @csrf
            <div class="custom-modal-header">
                <h3 class="custom-modal-title">
                    <span>🏛️</span> Yeni Kurul / Komite Tanımla
                </h3>
                <button type="button" class="custom-modal-close" onclick="closeCustomModal('newCommitteeModal')">✕</button>
            </div>
            <div class="custom-modal-body">
                <div class="modal-form-group">
                    <label class="modal-form-label">Kurul / Komite Adı <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="modal-form-control" placeholder="Örn: Yatırım Değerlendirme Kurulu" required>
                </div>

                <div class="modal-form-group">
                    <label class="modal-form-label">Oylama / Karar Modeli <span class="text-danger">*</span></label>
                    <select name="voting_type" id="newVotingType" class="modal-form-control" onchange="toggleQuorumPct('new')">
                        <option value="majority">Oy Çokluğu (%50'den fazla onay ağırlığı)</option>
                        <option value="unanimous">Oy Birliği (Tüm üyelerin onayı şart, 1 ret iptal eder)</option>
                        <option value="single_any">Tek Üye Onayı Yeterli (İlk onay ile proje kabul edilir)</option>
                        <option value="quorum_percentage">Özel Nisap Yüzdesi (Örn: %75 kabul şartı)</option>
                    </select>
                </div>

                <div class="modal-form-group" id="newQuorumPctContainer" style="display: none;">
                    <label class="modal-form-label">Karar Nisap Oranı (%)</label>
                    <input type="number" name="quorum_percentage" id="newQuorumPct" class="modal-form-control" value="51" min="1" max="100">
                </div>

                <!-- Kurulun Yetki ve Görev Kapsamı -->
                <div class="modal-authority-box">
                    <div class="modal-authority-title">
                        <span>⚡</span> Kurulun Yetki & Görev Kapsamı
                    </div>
                    <div class="auth-checkbox-grid">
                        <label class="auth-checkbox-label">
                            <input type="checkbox" name="authorities[can_approve]" value="1" checked>
                            <span>✓ Proje Onaylama</span>
                        </label>
                        <label class="auth-checkbox-label">
                            <input type="checkbox" name="authorities[can_reject]" value="1" checked>
                            <span>✕ Proje Reddetme</span>
                        </label>
                        <label class="auth-checkbox-label">
                            <input type="checkbox" name="authorities[can_conditional_approve]" value="1" checked>
                            <span>⚠️ Şartlı Onay Verme</span>
                        </label>
                        <label class="auth-checkbox-label">
                            <input type="checkbox" name="authorities[can_veto]" value="1">
                            <span>🛡️ Tek Oyla Veto Hakkı</span>
                        </label>
                        <label class="auth-checkbox-label">
                            <input type="checkbox" name="authorities[scope_budget]" value="1" checked>
                            <span>⚖️ DOA Bütçe Onayları</span>
                        </label>
                        <label class="auth-checkbox-label">
                            <input type="checkbox" name="authorities[scope_phases]" value="1">
                            <span>🚩 Faz Geçiş İzni</span>
                        </label>
                        <label class="auth-checkbox-label">
                            <input type="checkbox" name="authorities[scope_isg]" value="1">
                            <span>🦺 İSG & Uygunluk</span>
                        </label>
                    </div>
                </div>

                <div class="row g-2 modal-form-group">
                    <div class="col-6">
                        <label class="modal-form-label">Tema Rengi</label>
                        <input type="color" name="color" class="modal-form-control" value="#0284c7" style="height: 40px; padding: 2px;">
                    </div>
                    <div class="col-6">
                        <label class="modal-form-label">İkon (Emoji)</label>
                        <input type="text" name="icon" class="modal-form-control" value="🏛️" placeholder="🏛️">
                    </div>
                </div>

                <div class="modal-form-group mb-0">
                    <label class="modal-form-label">Açıklama / Yetki Kapsamı</label>
                    <textarea name="description" class="modal-form-control" rows="2" placeholder="Kurulun yetki alanı ve sorumlulukları..."></textarea>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeCustomModal('newCommitteeModal')">İptal</button>
                <button type="submit" class="matrix-header-btn-primary" style="height: 36px; padding: 0 16px;">
                    ✓ Kurulu Oluştur
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: KURULU DÜZENLE -->
<div id="editCommitteeModal" class="custom-modal-backdrop" onclick="handleBackdropClick(event, 'editCommitteeModal')">
    <div class="custom-modal-dialog">
        <form id="editCommitteeForm" method="POST">
            @csrf
            @method('PUT')
            <div class="custom-modal-header">
                <h3 class="custom-modal-title">
                    <span>✏️</span> Kurulu Düzenle
                </h3>
                <button type="button" class="custom-modal-close" onclick="closeCustomModal('editCommitteeModal')">✕</button>
            </div>
            <div class="custom-modal-body">
                <div class="modal-form-group">
                    <label class="modal-form-label">Kurul Adı</label>
                    <input type="text" name="name" id="editCommName" class="modal-form-control" required>
                </div>

                <div class="modal-form-group">
                    <label class="modal-form-label">Oylama Modeli</label>
                    <select name="voting_type" id="editVotingType" class="modal-form-control" onchange="toggleQuorumPct('edit')">
                        <option value="majority">Oy Çokluğu</option>
                        <option value="unanimous">Oy Birliği</option>
                        <option value="single_any">Tek Üye Onayı Yeterli</option>
                        <option value="quorum_percentage">Özel Nisap Yüzdesi</option>
                    </select>
                </div>

                <div class="modal-form-group" id="editQuorumPctContainer" style="display: none;">
                    <label class="modal-form-label">Nisap Oranı (%)</label>
                    <input type="number" name="quorum_percentage" id="editQuorumPct" class="modal-form-control" min="1" max="100">
                </div>

                <!-- Kurulun Yetki ve Görev Kapsamı -->
                <div class="modal-authority-box">
                    <div class="modal-authority-title">
                        <span>⚡</span> Kurulun Yetki & Görev Kapsamı
                    </div>
                    <div class="auth-checkbox-grid">
                        <label class="auth-checkbox-label">
                            <input type="checkbox" name="authorities[can_approve]" id="auth_can_approve" value="1">
                            <span>✓ Proje Onaylama</span>
                        </label>
                        <label class="auth-checkbox-label">
                            <input type="checkbox" name="authorities[can_reject]" id="auth_can_reject" value="1">
                            <span>✕ Proje Reddetme</span>
                        </label>
                        <label class="auth-checkbox-label">
                            <input type="checkbox" name="authorities[can_conditional_approve]" id="auth_can_conditional_approve" value="1">
                            <span>⚠️ Şartlı Onay</span>
                        </label>
                        <label class="auth-checkbox-label">
                            <input type="checkbox" name="authorities[can_veto]" id="auth_can_veto" value="1">
                            <span>🛡️ Veto Hakkı</span>
                        </label>
                        <label class="auth-checkbox-label">
                            <input type="checkbox" name="authorities[scope_budget]" id="auth_scope_budget" value="1">
                            <span>⚖️ DOA Bütçe</span>
                        </label>
                        <label class="auth-checkbox-label">
                            <input type="checkbox" name="authorities[scope_phases]" id="auth_scope_phases" value="1">
                            <span>🚩 Faz Geçişi</span>
                        </label>
                        <label class="auth-checkbox-label">
                            <input type="checkbox" name="authorities[scope_isg]" id="auth_scope_isg" value="1">
                            <span>🦺 İSG Denetimi</span>
                        </label>
                    </div>
                </div>

                <div class="row g-2 modal-form-group">
                    <div class="col-6">
                        <label class="modal-form-label">Tema Rengi</label>
                        <input type="color" name="color" id="editCommColor" class="modal-form-control" style="height: 40px; padding: 2px;">
                    </div>
                    <div class="col-6">
                        <label class="modal-form-label">İkon (Emoji)</label>
                        <input type="text" name="icon" id="editCommIcon" class="modal-form-control">
                    </div>
                </div>

                <div class="modal-form-group mb-0">
                    <label class="modal-form-label">Açıklama</label>
                    <textarea name="description" id="editCommDesc" class="modal-form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeCustomModal('editCommitteeModal')">Vazgeç</button>
                <button type="submit" class="matrix-header-btn-primary" style="height: 36px; padding: 0 16px;">
                    ✓ Değişiklikleri Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 3: KURULA ÜYE EKLE -->
<div id="addMemberModal" class="custom-modal-backdrop" onclick="handleBackdropClick(event, 'addMemberModal')">
    <div class="custom-modal-dialog" style="max-width: 560px;">
        <form id="addMemberForm" method="POST" onsubmit="return validateAddMemberForm()">
            @csrf
            <div class="custom-modal-header">
                <h3 class="custom-modal-title">
                    <span>👤</span> Kurula Üye Ata
                </h3>
                <button type="button" class="custom-modal-close" onclick="closeCustomModal('addMemberModal')">✕</button>
            </div>
            <div class="custom-modal-body">
                <div class="small mb-3" id="addMemberSubhead" style="font-weight: 700; color: #0284c7;">
                    Seçili Kurul: -
                </div>

                <!-- Gizli Seçili User ID -->
                <input type="hidden" name="user_id" id="selectedAddMemberUserId" required>

                <!-- Seçili Personel Bilgi Kartı (Bir personel tıklandığında görünür) -->
                <div id="memberSelectedUserBox" style="display: none; margin-bottom: 14px; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 10px; padding: 10px 14px; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div id="selectedUserAvatar" style="width: 34px; height: 34px; border-radius: 50%; background: #059669; color: #ffffff; font-weight: 700; display: flex; align-items: center; justify-content: center; font-size: 13px;">✓</div>
                        <div>
                            <div id="selectedUserName" style="font-weight: 700; color: #065f46; font-size: 13.5px;">-</div>
                            <div id="selectedUserSub" style="font-size: 11.5px; color: #166534;">-</div>
                        </div>
                    </div>
                    <button type="button" onclick="clearSelectedMemberUser()" style="background: #ffffff; border: 1px solid #bbf7d0; color: #dc2626; font-size: 11.5px; font-weight: 700; cursor: pointer; padding: 4px 8px; border-radius: 6px;">✕ Değiştir</button>
                </div>

                <!-- Personel Seçim, Arama & Sayfalama Bölümü -->
                <div class="modal-form-group" id="memberUserSelectGroup" style="margin-bottom: 16px;">
                    <label class="modal-form-label" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <span>Personel / Kullanıcı Seçin <span class="text-danger">*</span></span>
                        <span id="memberUserSearchCountBadge" style="font-size: 11px; font-weight: 600; color: #0284c7; background: #e0f2fe; padding: 2px 8px; border-radius: 6px;">Yükleniyor...</span>
                    </label>

                    <!-- Anlık Arama Kutusu -->
                    <div style="position: relative; margin-bottom: 8px;">
                        <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 13px; color: #94a3b8; pointer-events: none;">🔍</span>
                        <input type="text" 
                               id="memberUserSearchInput" 
                               class="modal-form-control" 
                               placeholder="İsim, e-posta, unvan veya sicil no ile anlık ara..." 
                               style="padding-left: 34px; padding-right: 32px; height: 38px; font-size: 13px;" 
                               oninput="debouncedMemberUserSearch()"
                               autocomplete="off">
                        <button type="button" 
                                id="memberUserSearchClearBtn" 
                                onclick="clearMemberUserSearch()" 
                                style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: #e2e8f0; border: none; width: 20px; height: 20px; border-radius: 50%; font-size: 11px; color: #475569; cursor: pointer; display: none; align-items: center; justify-content: center; line-height: 1;"
                                title="Aramayı Temizle">✕</button>
                    </div>

                    <!-- Kullanıcı Listesi (Scrollable & Paginated) -->
                    <div id="memberUserListContainer" style="max-height: 220px; overflow-y: auto; border: 1.5px solid #e2e8f0; border-radius: 10px; background: #ffffff; padding: 6px; display: flex; flex-direction: column; gap: 4px;">
                        <div style="padding: 24px; text-align: center; color: #94a3b8; font-size: 12.5px;">
                            ⏳ Personel listesi yükleniyor...
                        </div>
                    </div>

                    <!-- Sayfalama (Pagination) Alt Çubuğu -->
                    <div id="memberUserPaginationBar" style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px; padding: 4px 6px; font-size: 11.5px; color: #64748b; background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 8px;">
                        <button type="button" 
                                id="memberUserPrevBtn" 
                                onclick="changeMemberUserPage(-1)" 
                                class="btn btn-xs btn-outline-secondary" 
                                style="padding: 3px 10px; font-size: 11.5px; border-radius: 6px;" 
                                disabled>
                            ◀ Önceki
                        </button>
                        <span id="memberUserPageInfo" style="font-weight: 700; color: #334155;">Sayfa 1 / 1</span>
                        <button type="button" 
                                id="memberUserNextBtn" 
                                onclick="changeMemberUserPage(1)" 
                                class="btn btn-xs btn-outline-secondary" 
                                style="padding: 3px 10px; font-size: 11.5px; border-radius: 6px;" 
                                disabled>
                            Sonraki ▶
                        </button>
                    </div>
                </div>

                <div class="modal-form-group">
                    <label class="modal-form-label">Kuruldaki Rolü</label>
                    <select name="member_role" id="addMemberRoleSelect" class="modal-form-control">
                        <option value="uye">Kurul Üyesi (Normal Oy)</option>
                        <option value="baskan">⭐ Kurul Başkanı (Heyet Lideri)</option>
                        <option value="raportor">📝 Raportör / Katip</option>
                        <option value="yedek">Yedek Üye</option>
                    </select>
                </div>

                <div class="modal-form-group">
                    <label class="modal-form-label">Oy Ağırlığı (Katsayı)</label>
                    <input type="number" step="0.1" name="voting_weight" class="modal-form-control" value="1.0" min="0.1" max="10.0">
                    <div class="small text-muted mt-1" style="font-size: 11px;">Örn: Başkanın oyu 2.0x, normal üyenin oyu 1.0x.</div>
                </div>

                <!-- Bildirim Matrisi Entegrasyon Seçenekleri -->
                <div class="modal-notif-box">
                    <div class="modal-notif-title">
                        <span>🔔</span> Bildirim Matrisi Dağıtımı
                    </div>
                    <label class="modal-checkbox-label">
                        <input type="checkbox" name="send_bell" value="1" checked>
                        <span>Zil (Uygulama İçi) Bildirimi Gönder</span>
                    </label>
                    <label class="modal-checkbox-label">
                        <input type="checkbox" name="send_mail" value="1" checked>
                        <span>E-Posta Bildirimi Gönder</span>
                    </label>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeCustomModal('addMemberModal')">İptal</button>
                <button type="submit" class="matrix-header-btn-primary" style="height: 36px; padding: 0 16px;">
                    ✓ Üyeyi Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 4: ÜYE ÇIKARMA ONAYI VE BİLDİRİMİ -->
<div id="removeMemberConfirmModal" class="custom-modal-backdrop" onclick="handleBackdropClick(event, 'removeMemberConfirmModal')">
    <div class="custom-modal-dialog" style="max-width: 440px;">
        <form id="removeMemberForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="custom-modal-header" style="background: #fef2f2; border-bottom: 1px solid #fee2e2;">
                <h3 class="custom-modal-title" style="color: #991b1b;">
                    <span>⚠️</span> Üyelikten Çıkarma Onayı
                </h3>
                <button type="button" class="custom-modal-close" onclick="closeCustomModal('removeMemberConfirmModal')">✕</button>
            </div>
            <div class="custom-modal-body">
                <p id="removeMemberConfirmText" style="font-size: 13.5px; font-weight: 600; color: #0f172a; margin-bottom: 14px; line-height: 1.4;">
                    Bu üyeyi kuruldan çıkarmak istediğinize emin misiniz?
                </p>

                <div class="modal-notif-box" style="background: #fff7ed; border-color: #fed7aa;">
                    <div class="modal-notif-title" style="color: #c2410c;">
                        <span>🔔</span> Çıkarılma Bildirimi (Bildirim Matrisi)
                    </div>
                    <label class="modal-checkbox-label">
                        <input type="checkbox" name="send_bell" value="1" checked>
                        <span>Kullanıcıya Zil Bildirimi Gönder</span>
                    </label>
                    <label class="modal-checkbox-label">
                        <input type="checkbox" name="send_mail" value="1" checked>
                        <span>Kullanıcıya E-Posta Bildirimi Gönder</span>
                    </label>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeCustomModal('removeMemberConfirmModal')">Vazgeç</button>
                <button type="submit" class="btn btn-danger btn-sm" style="font-weight: 700; height: 36px;">
                    Evet, Üyeyi Çıkar
                </button>
            </div>
        </form>
    </div>
</div>

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

// ==========================================
// ARAMA VE FİLTRELEME MOTORU
// ==========================================
let currentFilter = 'all';

function filterCommittees() {
    const query = document.getElementById('committeeSearchInput').value.toLowerCase().trim();
    
    // Grid Kartlarını Filtrele
    document.querySelectorAll('.committee-item').forEach(el => {
        const name = el.getAttribute('data-name') || '';
        const slug = el.getAttribute('data-slug') || '';
        const members = el.getAttribute('data-members') || '';
        const active = el.getAttribute('data-active');
        const voting = el.getAttribute('data-voting');

        let matchesSearch = !query || name.includes(query) || slug.includes(query) || members.includes(query);
        let matchesFilter = true;

        if (currentFilter === 'active') {
            matchesFilter = (active === 'true');
        } else if (currentFilter === 'majority') {
            matchesFilter = (voting === 'majority');
        } else if (currentFilter === 'unanimous') {
            matchesFilter = (voting === 'unanimous');
        } else if (currentFilter === 'quorum') {
            matchesFilter = (voting === 'quorum_percentage');
        }

        el.style.display = (matchesSearch && matchesFilter) ? '' : 'none';
    });

    // Tablo Satırlarını Filtrele
    document.querySelectorAll('.committee-table-row').forEach(el => {
        const name = el.getAttribute('data-name') || '';
        const slug = el.getAttribute('data-slug') || '';
        const members = el.getAttribute('data-members') || '';
        const active = el.getAttribute('data-active');
        const voting = el.getAttribute('data-voting');

        let matchesSearch = !query || name.includes(query) || slug.includes(query) || members.includes(query);
        let matchesFilter = true;

        if (currentFilter === 'active') {
            matchesFilter = (active === 'true');
        } else if (currentFilter === 'majority') {
            matchesFilter = (voting === 'majority');
        } else if (currentFilter === 'unanimous') {
            matchesFilter = (voting === 'unanimous');
        } else if (currentFilter === 'quorum') {
            matchesFilter = (voting === 'quorum_percentage');
        }

        el.style.display = (matchesSearch && matchesFilter) ? '' : 'none';
    });
}

function setCommitteeFilter(filter, el) {
    currentFilter = filter;
    document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    filterCommittees();
}

// Görünüm Değiştirici (Grid / Table) - KESİN DISPLAY KONTROLÜ
function switchView(view) {
    const gridView = document.getElementById('committeesGridView');
    const tableView = document.getElementById('committeesTableView');
    const btnGrid = document.getElementById('btnViewGrid');
    const btnTable = document.getElementById('btnViewTable');

    if (view === 'table') {
        gridView.style.display = 'none';
        tableView.style.display = 'block';
        tableView.classList.remove('d-none');
        btnGrid.classList.remove('active');
        btnTable.classList.add('active');
        localStorage.setItem('committee_view_mode', 'table');
    } else {
        tableView.style.display = 'none';
        tableView.classList.add('d-none');
        gridView.style.display = 'grid';
        btnTable.classList.remove('active');
        btnGrid.classList.add('active');
        localStorage.setItem('committee_view_mode', 'grid');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const saved = localStorage.getItem('committee_view_mode');
    if (saved === 'table') {
        switchView('table');
    } else {
        switchView('grid');
    }
});

// AJAX AKTİF/PASİF TOGGLE
function toggleCommitteeActive(id, checkbox) {
    fetch(`{{ url('admin/kurullar') }}/${id}/toggle`, {
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
            alert(data.message || 'Durum güncellenemedi.');
        }
    })
    .catch(() => {
        checkbox.checked = !checkbox.checked;
        alert('Sunucuyla iletişim kurulurken bir hata oluştu.');
    });
}

function toggleQuorumPct(prefix) {
    const vt = document.getElementById(prefix + 'VotingType').value;
    const container = document.getElementById(prefix + 'QuorumPctContainer');
    container.style.display = (vt === 'quorum_percentage') ? 'block' : 'none';
}

// =========================================================================
// KURULA ÜYE ATA: ANLIK ARAMA, SAYFALAMA VE KULLANICI SEÇİMİ
// =========================================================================
let currentMemberAssignCommitteeId = null;
let currentMemberPage = 1;
let currentMemberTotalPages = 1;
let memberUserSearchTimeout = null;
let selectedMemberUserData = null;

function openAddMemberModal(committeeId, committeeName, defaultRole = 'uye') {
    currentMemberAssignCommitteeId = committeeId;
    document.getElementById('addMemberForm').action = `{{ url('admin/kurullar') }}/${committeeId}/members`;
    document.getElementById('addMemberSubhead').innerText = `Seçili Kurul: ${committeeName}`;
    if (defaultRole) {
        document.getElementById('addMemberRoleSelect').value = defaultRole;
    }

    // Seçimi ve Arama Kutusunu Sıfırla
    clearSelectedMemberUser();
    const searchInput = document.getElementById('memberUserSearchInput');
    if (searchInput) searchInput.value = '';
    const clearBtn = document.getElementById('memberUserSearchClearBtn');
    if (clearBtn) clearBtn.style.display = 'none';
    currentMemberPage = 1;

    openCustomModal('addMemberModal');

    // İlk sayfayı asenkron yükle (Alfabetik sırada ilk 25 kişi)
    loadCommitteeUsers(1);
}

function debouncedMemberUserSearch() {
    clearTimeout(memberUserSearchTimeout);
    const input = document.getElementById('memberUserSearchInput');
    const clearBtn = document.getElementById('memberUserSearchClearBtn');
    if (input.value.trim().length > 0) {
        clearBtn.style.display = 'flex';
    } else {
        clearBtn.style.display = 'none';
    }
    memberUserSearchTimeout = setTimeout(() => {
        loadCommitteeUsers(1);
    }, 250);
}

function clearMemberUserSearch() {
    const input = document.getElementById('memberUserSearchInput');
    input.value = '';
    document.getElementById('memberUserSearchClearBtn').style.display = 'none';
    input.focus();
    loadCommitteeUsers(1);
}

function changeMemberUserPage(delta) {
    const targetPage = currentMemberPage + delta;
    if (targetPage >= 1 && targetPage <= currentMemberTotalPages) {
        loadCommitteeUsers(targetPage);
    }
}

function loadCommitteeUsers(page = 1) {
    currentMemberPage = page;
    const container = document.getElementById('memberUserListContainer');
    const query = document.getElementById('memberUserSearchInput')?.value.trim() || '';
    const badge = document.getElementById('memberUserSearchCountBadge');
    const prevBtn = document.getElementById('memberUserPrevBtn');
    const nextBtn = document.getElementById('memberUserNextBtn');
    const pageInfo = document.getElementById('memberUserPageInfo');

    container.innerHTML = `
        <div style="padding: 24px; text-align: center; color: #94a3b8; font-size: 12.5px;">
            ⏳ Personel listesi taranıyor...
        </div>
    `;

    const searchUrl = `{{ route('admin.committees.search_users') }}?q=${encodeURIComponent(query)}&page=${page}&committee_id=${currentMemberAssignCommitteeId || ''}`;

    fetch(searchUrl)
        .then(res => res.json())
        .then(data => {
            if (!data.success || !data.users || data.users.length === 0) {
                container.innerHTML = `
                    <div style="padding: 24px; text-align: center; color: #94a3b8; font-size: 12.5px;">
                        ${query ? '🔍 Aradığınız kritere uygun personel bulunamadı.' : 'Kayıtlı aktif personel bulunamadı.'}
                    </div>
                `;
                badge.innerText = '0 Personel';
                pageInfo.innerText = 'Sayfa 0 / 0';
                prevBtn.disabled = true;
                nextBtn.disabled = true;
                return;
            }

            currentMemberTotalPages = data.last_page;
            badge.innerText = `${data.total} Personel`;
            pageInfo.innerText = `Sayfa ${data.current_page} / ${data.last_page}`;
            prevBtn.disabled = data.current_page <= 1;
            nextBtn.disabled = data.current_page >= data.last_page;

            let html = '';
            data.users.forEach(u => {
                const isSelected = selectedMemberUserData && selectedMemberUserData.id === u.id;
                const selectedClass = isSelected ? 'is-selected' : '';
                const alreadyMemberBadge = u.already_member ? 
                    `<span style="background: #f1f5f9; color: #64748b; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; border: 1px solid #e2e8f0;">✓ Kurulda Mevcut</span>` : '';

                html += `
                    <div class="member-user-item ${selectedClass}" 
                         id="userItemRow_${u.id}"
                         onclick='selectMemberUser(${JSON.stringify(u)})'>
                        <div style="display: flex; align-items: center; gap: 9px; min-width: 0;">
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: #e0f2fe; color: #0284c7; font-weight: 800; font-size: 12.5px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                ${escapeHtml(u.initials)}
                            </div>
                            <div style="min-width: 0;">
                                <div style="font-size: 12.5px; font-weight: 700; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    ${escapeHtml(u.name)}
                                </div>
                                <div style="font-size: 11px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    ${escapeHtml(u.email)} ${u.unvan ? '• ' + escapeHtml(u.unvan) : ''} ${u.bolum_name ? '(' + escapeHtml(u.bolum_name) + ')' : ''}
                                </div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                            ${alreadyMemberBadge}
                            <span class="user-select-indicator" style="font-size: 11px; font-weight: 700; color: ${isSelected ? '#059669' : '#0284c7'}; background: ${isSelected ? '#dcfce7' : '#f0f9ff'}; padding: 3px 8px; border-radius: 6px; border: 1px solid ${isSelected ? '#86efac' : '#bae6fd'};">
                                ${isSelected ? '✓ Seçildi' : 'Seç'}
                            </span>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            container.innerHTML = `
                <div style="padding: 20px; text-align: center; color: #dc2626; font-size: 12px;">
                    ⚠️ Personel listesi yüklenirken bir hata oluştu.
                </div>
            `;
        });
}

function selectMemberUser(user) {
    selectedMemberUserData = user;
    document.getElementById('selectedAddMemberUserId').value = user.id;

    // Seçili kutusunu göster
    const box = document.getElementById('memberSelectedUserBox');
    document.getElementById('selectedUserAvatar').innerText = user.initials;
    document.getElementById('selectedUserName').innerText = user.name;
    document.getElementById('selectedUserSub').innerText = `${user.email} ${user.unvan ? '• ' + user.unvan : ''} ${user.bolum_name ? '(' + user.bolum_name + ')' : ''}`;
    box.style.display = 'flex';

    // Listede seçilen öğeyi işaretle
    document.querySelectorAll('.member-user-item').forEach(el => {
        el.classList.remove('is-selected');
        const indicator = el.querySelector('.user-select-indicator');
        if (indicator) {
            indicator.innerText = 'Seç';
            indicator.style.color = '#0284c7';
            indicator.style.background = '#f0f9ff';
            indicator.style.borderColor = '#bae6fd';
        }
    });

    const activeRow = document.getElementById(`userItemRow_${user.id}`);
    if (activeRow) {
        activeRow.classList.add('is-selected');
        const indicator = activeRow.querySelector('.user-select-indicator');
        if (indicator) {
            indicator.innerText = '✓ Seçildi';
            indicator.style.color = '#059669';
            indicator.style.background = '#dcfce7';
            indicator.style.borderColor = '#86efac';
        }
    }
}

function clearSelectedMemberUser() {
    selectedMemberUserData = null;
    const hiddenInput = document.getElementById('selectedAddMemberUserId');
    if (hiddenInput) hiddenInput.value = '';
    const box = document.getElementById('memberSelectedUserBox');
    if (box) box.style.display = 'none';

    document.querySelectorAll('.member-user-item').forEach(el => {
        el.classList.remove('is-selected');
        const indicator = el.querySelector('.user-select-indicator');
        if (indicator) {
            indicator.innerText = 'Seç';
            indicator.style.color = '#0284c7';
            indicator.style.background = '#f0f9ff';
            indicator.style.borderColor = '#bae6fd';
        }
    });
}

function validateAddMemberForm() {
    const userId = document.getElementById('selectedAddMemberUserId').value;
    if (!userId) {
        alert('Lütfen kurula atamak istediğiniz personeli listeden seçin.');
        document.getElementById('memberUserSearchInput')?.focus();
        return false;
    }
    return true;
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function openEditCommitteeModal(committee) {
    document.getElementById('editCommitteeForm').action = `{{ url('admin/kurullar') }}/${committee.id}`;
    document.getElementById('editCommName').value = committee.name || '';
    document.getElementById('editVotingType').value = committee.voting_type || 'majority';
    document.getElementById('editQuorumPct').value = committee.quorum_percentage || 51;
    document.getElementById('editCommColor').value = committee.color || '#0284c7';
    document.getElementById('editCommIcon').value = committee.icon || '🏛️';
    document.getElementById('editCommDesc').value = committee.description || '';

    // Yetki checkbox'larını doldur
    const auth = committee.authorities || {};
    document.getElementById('auth_can_approve').checked = !!auth.can_approve;
    document.getElementById('auth_can_reject').checked = !!auth.can_reject;
    document.getElementById('auth_can_conditional_approve').checked = !!auth.can_conditional_approve;
    document.getElementById('auth_can_veto').checked = !!auth.can_veto;
    document.getElementById('auth_scope_budget').checked = !!auth.scope_budget;
    document.getElementById('auth_scope_phases').checked = !!auth.scope_phases;
    document.getElementById('auth_scope_isg').checked = !!auth.scope_isg;

    toggleQuorumPct('edit');
    openCustomModal('editCommitteeModal');
}

function confirmRemoveMember(committeeId, memberId, memberName, committeeName) {
    const form = document.getElementById('removeMemberForm');
    form.action = `{{ url('admin/kurullar') }}/${committeeId}/members/${memberId}`;
    document.getElementById('removeMemberConfirmText').innerHTML = 
        `<strong>${memberName}</strong> adlı kullanıcıyı <strong>${committeeName}</strong> kurulundan çıkarmak istediğinize emin misiniz?`;
    openCustomModal('removeMemberConfirmModal');
}
</script>
@endsection
