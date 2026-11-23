<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') • {{ \App\Models\SystemSetting::get('site_title', 'Köksan Enterprise PPM') }}@else{{ \App\Models\SystemSetting::get('site_title', 'Köksan Enterprise PPM') }}@endif</title>
    
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Özel Modern Vanilla CSS Tasarım Paketi -->
    <link rel="stylesheet" href="{{ asset('css/koksan_pms.css') }}?v={{ file_exists(public_path('css/koksan_pms.css')) ? filemtime(public_path('css/koksan_pms.css')) : '1.0' }}">
    @stack('styles')
<style>
/* ==========================================================================
   MASAÜSTÜ SIDEBAR DARALTMA / MİNİ MOD (CHATGPT & GEMINI STİLİ)
   ========================================================================== */
.app-sidebar {
    width: 270px;
    min-width: 270px;
    max-width: 270px;
    background: var(--sidebar-bg, #0b1329);
    border-right: 1px solid rgba(255, 255, 255, 0.12) !important;
    transition: width 0.24s cubic-bezier(0.4, 0, 0.2, 1),
                min-width 0.24s cubic-bezier(0.4, 0, 0.2, 1),
                max-width 0.24s cubic-bezier(0.4, 0, 0.2, 1) !important;
    overflow-x: hidden !important;
    overflow-y: auto !important;
    will-change: width, min-width;
    z-index: 100;
}

/* ==========================================================================
   KURUMSAL VEKALET ARAMA DROPDOWN STİLLERİ
   ========================================================================== */
.delegation-user-option {
    transition: background 0.15s ease, border-color 0.15s ease;
}
.delegation-user-option:hover {
    background: #f0f9ff !important;
}
.delegation-user-option:hover .delegation-opt-name {
    color: #0284c7 !important;
}
.delegation-user-option:active {
    background: #e0f2fe !important;
}
.delegation-perm-group {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 12px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.02);
}
.delegation-perm-item {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    padding: 7px 9px;
    border-radius: 6px;
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    cursor: pointer;
    transition: all 0.15s ease;
    margin: 0;
}
.delegation-perm-item:hover {
    background: #f0fdf4;
    border-color: #bbf7d0;
}
.delegation-perm-item input[type="checkbox"]:checked ~ div strong {
    color: #0369a1;
}

.app-main {
    flex: 1 1 0% !important;
    display: flex !important;
    flex-direction: column !important;
    overflow-x: hidden !important;
    overflow-y: hidden !important;
    height: 100vh !important;
    min-width: 0 !important;
    max-width: 100% !important;
    width: auto !important;
    box-sizing: border-box !important;
}

/* ==========================================================================
   2-TIER MODERN ENTERPRISE TOPBAR (GLOBAL UTILITY + CLEAN PAGE HEADER)
   ========================================================================== */
.app-topbar {
    height: auto !important;
    background: #ffffff !important;
    border-bottom: 1px solid var(--border-color, #e2e8f0) !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: stretch !important;
    padding: 0 !important;
    flex-shrink: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
    overflow: visible !important;
    position: relative !important;
    z-index: 50 !important;
}

/* Tier 1: En Üst Global Yardımcı Çubuk (Omnisearch + Hızlı Rol + Bildirim) */
.topbar-utility-row {
    height: 52px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    padding: 0 16px !important;
    border-bottom: 1px solid #f1f5f9 !important;
    background: #ffffff !important;
    gap: 10px !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
    overflow: visible !important;
    position: relative !important;
}

.topbar-utility-main {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    width: 100% !important;
    gap: 10px !important;
    min-width: 0 !important;
}

.topbar-mobile-search-row {
    display: none !important;
}

.utility-left {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    flex: 1 1 auto !important;
    min-width: 0 !important;
}

.utility-right {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    flex-shrink: 0 !important;
    max-width: 100% !important;
}

/* Global Omnisearch Butonu (Akışkan & Dinamik Genişlik) */
.topbar-search-trigger {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 8px !important;
    width: 100% !important;
    max-width: 320px !important;
    min-width: 140px !important;
    flex: 1 1 auto !important;
    height: 36px !important;
    background: #f8fafc !important;
    border: 1.5px solid #cbd5e1 !important;
    border-radius: 9px !important;
    padding: 0 10px !important;
    cursor: pointer !important;
    box-sizing: border-box !important;
    transition: all 0.2s ease !important;
}

.role-switcher-wrap {
    display: flex !important;
    align-items: center !important;
    gap: 5px !important;
    min-width: 0 !important;
    flex-shrink: 1 !important;
}

.switcher-label {
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #64748b !important;
    white-space: nowrap !important;
}

.role-switcher-select {
    max-width: 180px !important;
    min-width: 105px !important;
    height: 34px !important;
    padding: 0 8px !important;
    font-size: 11.5px !important;
    font-weight: 600 !important;
    color: #1e293b !important;
    background: #ffffff !important;
    border: 1.5px solid #cbd5e1 !important;
    border-radius: 8px !important;
    outline: none !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    flex-shrink: 1 !important;
}

.topbar-delegation-wrap {
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    flex-shrink: 0 !important;
    min-width: 0 !important;
}

.topbar-delegation-badge {
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    max-width: 480px !important;
    flex-shrink: 0 !important;
    font-size: 11.5px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 5px !important;
}

.mobile-only-delegation {
    display: none !important;
}

.desktop-only-delegation {
    display: flex !important;
}

.desktop-only-search {
    display: flex !important;
}

.bell-btn * {
    pointer-events: none !important;
}

.notification-bell-wrap {
    position: relative !important;
    z-index: 1000 !important;
}

.notification-dropdown {
    position: absolute !important;
    top: calc(100% + 8px) !important;
    right: 0 !important;
    width: 380px !important;
    max-width: calc(100vw - 20px) !important;
    background: #ffffff !important;
    border-radius: 12px !important;
    box-shadow: 0 12px 32px rgba(15, 23, 42, 0.22), 0 2px 6px rgba(15, 23, 42, 0.08) !important;
    border: 1px solid #cbd5e1 !important;
    z-index: 99999 !important;
}

@media (max-width: 1400px) {
    .switcher-label {
        display: none !important;
    }
    .role-switcher-select {
        max-width: 150px !important;
    }
    .topbar-delegation-badge {
        max-width: 420px !important;
    }
    .topbar-search-trigger {
        max-width: 240px !important;
    }
}

@media (max-width: 1024px) {
    .topbar-delegation-badge {
        max-width: 340px !important;
    }
    .role-switcher-select {
        max-width: 130px !important;
    }
    .topbar-search-trigger {
        max-width: 180px !important;
    }
}

@media (max-width: 768px) {
    .topbar-utility-row {
        height: auto !important;
        padding: 8px 12px 10px !important;
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 8px !important;
        overflow: visible !important;
    }

    .topbar-utility-main {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
        gap: 6px !important;
    }

    .desktop-only-search {
        display: none !important;
    }

    .desktop-only-delegation {
        display: none !important;
    }

    .mobile-only-delegation {
        display: inline-flex !important;
        max-width: 165px !important;
        padding: 4px 8px !important;
        font-size: 11px !important;
        border-radius: 8px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
        cursor: pointer;
        align-items: center;
        gap: 4px;
        flex-shrink: 1;
        font-weight: 600;
    }

    .mobile-only-delegation.is-given {
        background: #fffbeb !important;
        border-color: #fde68a !important;
        color: #92400e !important;
    }

    .topbar-mobile-search-row {
        display: flex !important;
        width: 100% !important;
    }

    .topbar-mobile-search-row .topbar-search-trigger {
        width: 100% !important;
        max-width: 100% !important;
        height: 38px !important;
        min-width: 100% !important;
    }

    .role-switcher-select {
        max-width: 110px !important;
        font-size: 11px !important;
        height: 34px !important;
        padding: 0 4px !important;
    }

    .topbar-delegation-badge {
        max-width: 165px !important;
        padding: 3px 6px !important;
        font-size: 11px !important;
        flex-shrink: 1 !important;
    }

    .btn-delegation-label {
        display: none !important;
    }

    .notification-dropdown {
        right: 0 !important;
        left: auto !important;
        width: min(360px, calc(100vw - 16px)) !important;
        max-width: calc(100vw - 16px) !important;
    }
}
.topbar-search-trigger:hover {
    background: #ffffff !important;
    border-color: #0284c7 !important;
    box-shadow: 0 2px 8px rgba(2, 132, 199, 0.12) !important;
}
.topbar-search-trigger .search-placeholder {
    font-size: 12.5px !important;
    color: #64748b !important;
    font-weight: 500 !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}
.topbar-search-trigger .search-kbd {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 4px !important;
    padding: 1px 6px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #475569 !important;
    font-family: monospace !important;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
}

/* Tier 2: Sayfa Başlığı ve Açıklaması (Kesintisiz Geniş Alan) */
.topbar-title-row {
    padding: 12px 24px 14px 24px !important;
    display: flex !important;
    align-items: flex-start !important;
    justify-content: flex-start !important;
    background: #ffffff !important;
    text-align: left !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

.topbar-title-wrap {
    display: flex !important;
    flex-direction: column !important;
    align-items: flex-start !important;
    justify-content: center !important;
    text-align: left !important;
    min-width: 0 !important;
    width: 100% !important;
}

.page-header-title {
    font-size: 19px !important;
    font-weight: 800 !important;
    color: var(--navy, #0f172a) !important;
    margin: 0 !important;
    line-height: 1.25 !important;
    letter-spacing: -0.015em !important;
    text-align: left !important;
}

.page-subtitle {
    font-size: 12.5px !important;
    color: var(--text-muted, #64748b) !important;
    margin: 4px 0 0 0 !important;
    line-height: 1.35 !important;
    display: block !important;
    font-weight: 450 !important;
    text-align: left !important;
}

/* SIDEBAR BRAND & BUTONLAR */
.sidebar-brand {
    padding: 16px 14px 12px 14px !important;
    transition: padding 0.24s ease;
}
.sidebar-brand-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.sidebar-desktop-collapse-btn {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
    padding: 0;
}
.sidebar-desktop-collapse-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
    border-color: rgba(255, 255, 255, 0.35);
    transform: scale(1.05);
}

.brand-corporate-tag {
    margin-top: 10px;
    padding-top: 8px;
    border-top: 1px dashed rgba(255, 255, 255, 0.12);
    text-align: center;
    transition: opacity 0.2s ease;
}
.brand-corporate-title {
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: 0.07em;
    color: #38bdf8;
    text-transform: uppercase;
    line-height: 1.3;
}
.brand-corporate-sub {
    font-size: 9.5px;
    color: #94a3b8;
    letter-spacing: 0.03em;
    margin-top: 2px;
    font-weight: 500;
}

/* SIDEBAR KULLANICI PROFİL KARTI */
.sidebar-user-card {
    padding: 14px 14px !important;
    margin: 12px 14px !important;
    display: flex !important;
    align-items: flex-start !important;
    gap: 12px !important;
    transition: all 0.24s ease;
}
.user-avatar-circle {
    width: 38px !important;
    height: 38px !important;
    border-radius: 50% !important;
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
    color: #fff !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-weight: 800 !important;
    font-size: 15px !important;
    flex-shrink: 0 !important;
    margin-top: 2px !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25) !important;
}
.user-info-text {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}
.user-name-title {
    font-size: 13.5px;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: -0.01em;
    line-height: 1.25;
    word-break: break-word;
}
.user-dept-title {
    font-size: 11.5px;
    font-weight: 600;
    color: #94a3b8;
    margin-top: 2px;
    line-height: 1.3;
}
.user-role-badge {
    margin-top: 5px;
}

/* ==========================================================================
   SIDEBAR DARALTILDIĞINDA / MİNİ MODDA (COLLAPSED STATE) - YALNIZCA MASAÜSTÜ
   ========================================================================== */
@media (min-width: 769px) {
    .app-layout.sidebar-collapsed .app-sidebar {
        width: 70px !important;
        min-width: 70px !important;
        max-width: 70px !important;
        margin-left: 0 !important;
        opacity: 1 !important;
        visibility: visible !important;
        pointer-events: auto !important;
        border-right: 1px solid rgba(255, 255, 255, 0.12) !important;
    }

    .app-layout.sidebar-collapsed .app-main {
        flex: 1 1 0% !important;
        min-width: 0 !important;
        width: auto !important;
    }

    /* Brand Alanı: Küçük Logo ve Toggle Butonu Dikey Hizalı */
    .app-layout.sidebar-collapsed .sidebar-brand {
        padding: 12px 6px 10px !important;
    }
    .app-layout.sidebar-collapsed .sidebar-brand-header {
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
    }
    .app-layout.sidebar-collapsed .brand-full-logo {
        display: none !important;
    }
    .app-layout.sidebar-collapsed .brand-mini-logo {
        display: flex !important;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        background: #ffffff;
        border-radius: 9px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.25);
        padding: 3px;
        text-decoration: none;
        transition: transform 0.2s ease;
    }
    .app-layout.sidebar-collapsed .brand-mini-logo:hover {
        transform: scale(1.08);
    }
    .app-layout.sidebar-collapsed .sidebar-desktop-collapse-btn {
        width: 34px !important;
        height: 34px !important;
        border-radius: 8px !important;
        margin: 0 !important;
        background: rgba(255, 255, 255, 0.12) !important;
        border-color: rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
    }
    .app-layout.sidebar-collapsed .sidebar-desktop-collapse-btn svg {
        transform: rotate(180deg);
        transition: transform 0.2s ease;
    }
    .app-layout.sidebar-collapsed .brand-corporate-tag {
        display: none !important;
    }

    /* Kullanıcı Profil Kartı: Yalnızca Avatar Dairesi Ortalanır */
    .app-layout.sidebar-collapsed .sidebar-user-card {
        padding: 8px 0 !important;
        margin: 8px auto !important;
        width: 100% !important;
        justify-content: center !important;
        background: transparent !important;
        border: none !important;
    }
    .app-layout.sidebar-collapsed .user-info-text {
        display: none !important;
    }
    .app-layout.sidebar-collapsed .user-avatar-circle {
        margin: 0 auto !important;
        width: 38px !important;
        height: 38px !important;
        cursor: pointer;
    }

    /* Navigasyon: Etiketler Gizlenir, İkonlar Şık Şekilde Ortalanır */
    .app-layout.sidebar-collapsed .sidebar-nav {
        padding: 4px 6px !important;
    }
    .app-layout.sidebar-collapsed .nav-section-title {
        height: 1px !important;
        padding: 0 !important;
        margin: 10px 8px !important;
        background: rgba(255, 255, 255, 0.08) !important;
        font-size: 0 !important;
        overflow: hidden !important;
        border: none !important;
    }
    .app-layout.sidebar-collapsed .nav-item {
        padding: 0 !important;
        margin: 4px auto !important;
        width: 44px !important;
        height: 44px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 10px !important;
        transition: all 0.15s ease;
    }
    .app-layout.sidebar-collapsed .nav-label {
        display: none !important;
    }
    .app-layout.sidebar-collapsed .nav-icon {
        font-size: 20px !important;
        margin: 0 !important;
        line-height: 1 !important;
    }

    /* Footer: Çıkış İkonu Ortalanır */
    .app-layout.sidebar-collapsed .sidebar-footer {
        padding: 12px 6px !important;
        display: flex !important;
        justify-content: center !important;
    }
    .app-layout.sidebar-collapsed .logout-link {
        padding: 0 !important;
        width: 44px !important;
        height: 44px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 10px !important;
        margin: 0 auto !important;
    }
    .app-layout.sidebar-collapsed .logout-text {
        display: none !important;
    }
}

@media (max-width: 768px) {
    .sidebar-desktop-collapse-btn {
        display: none !important;
    }
    .app-main {
        width: 100% !important;
    }

    /* MOBİL ÇEKMECE: HER ZAMAN 280px GENİŞLİKTE VE TÜM YAZILAR GÖRÜNÜR */
    .app-sidebar,
    .app-layout.sidebar-collapsed .app-sidebar {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        bottom: 0 !important;
        height: 100vh !important;
        width: 280px !important;
        min-width: 280px !important;
        max-width: 85vw !important;
        z-index: 1050 !important;
        transform: translateX(-100%) !important;
        transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5) !important;
        display: flex !important;
        flex-direction: column !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        visibility: visible !important;
        opacity: 1 !important;
        background: #0b1329 !important;
    }

    .app-sidebar.show,
    .app-layout.sidebar-collapsed .app-sidebar.show {
        transform: translateX(0) !important;
    }

    .app-sidebar .sidebar-brand-header,
    .app-layout.sidebar-collapsed .sidebar-brand-header {
        flex-direction: row !important;
        align-items: center !important;
        justify-content: space-between !important;
    }

    .app-sidebar .brand-full-logo,
    .app-layout.sidebar-collapsed .brand-full-logo {
        display: flex !important;
    }

    .app-sidebar .brand-mini-logo,
    .app-layout.sidebar-collapsed .brand-mini-logo {
        display: none !important;
    }

    .app-sidebar .brand-corporate-tag,
    .app-layout.sidebar-collapsed .brand-corporate-tag {
        display: block !important;
        opacity: 1 !important;
    }

    .app-sidebar .sidebar-user-card,
    .app-layout.sidebar-collapsed .sidebar-user-card {
        padding: 12px 14px !important;
        margin: 10px 12px !important;
        width: auto !important;
        justify-content: flex-start !important;
        background: rgba(255, 255, 255, 0.05) !important;
        border-radius: 10px !important;
        display: flex !important;
    }

    .app-sidebar .user-info-text,
    .app-layout.sidebar-collapsed .app-sidebar .user-info-text {
        display: flex !important;
    }

    .app-sidebar .sidebar-nav,
    .app-layout.sidebar-collapsed .app-sidebar .sidebar-nav {
        padding: 6px 10px !important;
    }

    .app-sidebar .nav-section-title,
    .app-layout.sidebar-collapsed .app-sidebar .nav-section-title {
        height: auto !important;
        padding: 8px 12px 4px !important;
        margin: 10px 0 2px !important;
        font-size: 10.5px !important;
        overflow: visible !important;
        display: block !important;
        background: transparent !important;
        color: #64748b !important;
    }

    .app-sidebar .nav-item,
    .app-layout.sidebar-collapsed .app-sidebar .nav-item {
        width: 100% !important;
        height: auto !important;
        min-height: 40px !important;
        padding: 9px 12px !important;
        margin: 2px 0 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        gap: 10px !important;
        border-radius: 8px !important;
    }

    .app-sidebar .nav-label,
    .app-layout.sidebar-collapsed .app-sidebar .nav-label {
        display: inline-block !important;
        font-size: 13.5px !important;
        color: #e2e8f0 !important;
    }

    .app-sidebar .sidebar-footer,
    .app-layout.sidebar-collapsed .app-sidebar .sidebar-footer {
        padding: 12px 14px !important;
        display: flex !important;
        justify-content: stretch !important;
    }

    .app-sidebar .logout-link,
    .app-layout.sidebar-collapsed .app-sidebar .logout-link {
        width: 100% !important;
        height: auto !important;
        padding: 10px 14px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        gap: 10px !important;
    }

    .app-sidebar .logout-text,
    .app-layout.sidebar-collapsed .app-sidebar .logout-text {
        display: inline !important;
    }
}
</style>
</head>
<body>
    <div class="app-layout">
        <!-- SOL SIDEBAR (KÖKSAN KURUMSAL MENÜ) -->
        <aside class="app-sidebar" id="appSidebar">
            <button type="button" class="sidebar-close-btn" id="sidebarCloseBtn" onclick="closeMobileSidebar()" aria-label="Menüyü Kapat" title="Kapat">✕</button>
            <div class="sidebar-brand">
                <div class="sidebar-brand-header">
                    @php
                        $siteLogo = \App\Models\SystemSetting::get('site_logo');
                    @endphp
                    @if($siteLogo && file_exists(public_path($siteLogo)))
                        <!-- Genişletilmiş Logo Linki -->
                        <a href="{{ route('dashboard') }}" class="brand-logo-link brand-full-logo" style="display: flex; align-items: center; justify-content: center; background: #ffffff; padding: 6px 12px; border-radius: 10px; text-decoration: none; box-shadow: 0 4px 12px rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.2); transition: transform 0.2s; flex: 1;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                            <img src="{{ asset($siteLogo) }}" alt="Köksan Logo" style="max-height: 36px; width: auto; max-width: 100%; object-fit: contain; display: block;">
                        </a>
                        <!-- Daraltılmış Mini Logo Linki -->
                        <a href="{{ route('dashboard') }}" class="brand-mini-logo" title="Köksan Enterprise PPM" style="display: none;">
                            <img src="{{ asset($siteLogo) }}" alt="Köksan Logo" style="max-height: 28px; width: auto; max-width: 100%; object-fit: contain; display: block;">
                        </a>
                    @else
                        <!-- Genişletilmiş Varsayılan Logo -->
                        <a href="{{ route('dashboard') }}" class="brand-logo-wrap brand-full-logo" style="text-decoration: none; flex: 1;">
                            <div class="brand-icon">🏢</div>
                            <div class="brand-text">
                                <span class="brand-title">KÖKSAN</span>
                                <span class="brand-sub">ENTERPRISE PPM</span>
                            </div>
                        </a>
                        <!-- Daraltılmış Mini Varsayılan Logo -->
                        <a href="{{ route('dashboard') }}" class="brand-mini-logo" title="Köksan Enterprise PPM" style="display: none;">
                            <span style="font-size: 20px;">🏢</span>
                        </a>
                    @endif

                    <!-- MASAÜSTÜ MENÜYÜ GİZLE / DARALT BUTONU (CHATGPT & GEMINI STİLİ) -->
                    <button type="button" class="sidebar-desktop-collapse-btn" id="sidebarCollapseBtn" onclick="toggleSidebarDesktop()" title="Menüyü Daralt (Geniş Ekran)" aria-label="Menüyü Daralt">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="9" y1="3" x2="9" y2="21"></line>
                            <path d="M14 9l-3 3 3 3"></path>
                        </svg>
                    </button>
                </div>

                <!-- KÖKSAN LOGOSU ALTINDA KURUMSAL SİSTEM İSMİ -->
                <div class="brand-corporate-tag">
                    <div class="brand-corporate-title">KURUMSAL PROJE YÖNETİM SİSTEMİ</div>
                    <div class="brand-corporate-sub">Enterprise PPM & Operasyon Platformu</div>
                </div>
            </div>

            <!-- AKTİF KULLANICI PROFİL KARTI -->
            <div class="sidebar-user-card">
                <div class="user-avatar-circle" title="{{ auth()->user()->name }} ({{ auth()->user()->roles->first()?->name ?? 'Kullanıcı' }})">
                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="user-info-text">
                    <!-- 1. İsim Üstte ve Büyük -->
                    <div class="user-name-title" title="{{ auth()->user()->name }}">
                        {{ auth()->user()->name }}
                    </div>
                    <!-- 2. Altında Yönetim / Departman Etiketi -->
                    <div class="user-dept-title">
                        @if(auth()->user()->isSuperAdmin())
                            <span>Yönetim</span>
                        @elseif(auth()->user()->department)
                            <span>{{ auth()->user()->department }}</span>
                        @else
                            <span>{{ auth()->user()->roles->first()?->name ?? 'Kullanıcı' }}</span>
                        @endif
                    </div>
                    <!-- 3. En Altta Developer / Rol Rozeti -->
                    <div class="user-role-badge">
                        @if(auth()->user()->isSuperAdmin())
                            <span class="role-pill pill-superadmin">Developer</span>
                        @elseif(auth()->user()->isYonetim())
                            <span class="role-pill pill-yonetim">Yönetim (Read-Only)</span>
                        @else
                            <span class="role-pill pill-default">{{ auth()->user()->roles->first()?->name ?? 'Kullanıcı' }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- NAVİGASYON MENÜSÜ -->
            <nav class="sidebar-nav">
                <div class="nav-section-title">ANA MODÜLLER</div>
                
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" title="Yönetici Paneli">
                    <span class="nav-icon">📊</span>
                    <span class="nav-label">Yönetici Paneli</span>
                </a>

                <a href="{{ route('projects.index') }}" class="nav-item {{ request()->routeIs('projects.*') ? 'active' : '' }}" title="Proje Portföyü">
                    <span class="nav-icon">📁</span>
                    <span class="nav-label">Proje Portföyü</span>
                </a>

                <a href="{{ route('kanban.index') }}" class="nav-item {{ request()->routeIs('kanban.*') ? 'active' : '' }}" title="Kanban Panosu">
                    <span class="nav-icon">📌</span>
                    <span class="nav-label">Kanban Panosu</span>
                </a>

                <a href="{{ route('gantt.index') }}" class="nav-item {{ request()->routeIs('gantt.*') ? 'active' : '' }}" title="Gantt Çizelgesi">
                    <span class="nav-icon">📈</span>
                    <span class="nav-label">Gantt Çizelgesi</span>
                </a>

                <div class="nav-section-title">KAPASİTE & RİSK & FİNANS</div>

                <a href="{{ route('resources.index') }}" class="nav-item {{ request()->routeIs('resources.*') ? 'active' : '' }}" title="Kaynak Kapasitesi">
                    <span class="nav-icon">👥</span>
                    <span class="nav-label">Kaynak Kapasitesi</span>
                </a>

                <a href="{{ route('risks.index') }}" class="nav-item {{ request()->routeIs('risks.*') ? 'active' : '' }}" title="5×5 Risk Matrisi">
                    <span class="nav-icon">🛡️</span>
                    <span class="nav-label">5×5 Risk Matrisi</span>
                </a>

                <a href="{{ route('quality.index') }}" class="nav-item {{ request()->routeIs('quality.*') ? 'active' : '' }}" title="Kalite & Kabul Yönetimi">
                    <span class="nav-icon">🔬</span>
                    <span class="nav-label">Kalite & Kabul</span>
                </a>

                <a href="{{ route('timesheets.index') }}" class="nav-item {{ request()->routeIs('timesheets.*') ? 'active' : '' }}" title="Zaman Takibi">
                    <span class="nav-icon">⏱️</span>
                    <span class="nav-label">Zaman Takibi</span>
                </a>

                <a href="{{ route('boms.index') }}" class="nav-item {{ request()->routeIs('boms.*') ? 'active' : '' }}" title="BOM & Bütçe Yönetimi">
                    <span class="nav-icon">📦</span>
                    <span class="nav-label">BOM & Bütçe</span>
                </a>

                <a href="{{ route('meetings.index') }}" class="nav-item {{ request()->routeIs('meetings.*') ? 'active' : '' }}" title="Toplantılar">
                    <span class="nav-icon">📅</span>
                    <span class="nav-label">Toplantılar</span>
                </a>

                <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}" title="5 Rapor Tipi">
                    <span class="nav-icon">📑</span>
                    <span class="nav-label">5 Rapor Tipi</span>
                </a>

                <!-- YÖNETİM & SİSTEMİK AYARLAR -->
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isYonetim())
                    <div class="nav-section-title">SİSTEM & GÜVENLİK</div>

                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                        <a href="{{ route('admin.user_roles.index') }}" class="nav-item {{ request()->routeIs('admin.user_roles*') ? 'active' : '' }}" title="Kullanıcı & Çoklu Rol Yetkilendirme">
                            <span class="nav-icon">👥</span>
                            <span class="nav-label">Kullanıcı Rolleri</span>
                        </a>

                        <a href="{{ route('admin.roles.index') }}" class="nav-item {{ request()->routeIs('admin.roles*') ? 'active' : '' }}" title="Rol Tanımları ve Grupları">
                            <span class="nav-icon">🎭</span>
                            <span class="nav-label">Rol Tanımları</span>
                        </a>

                        <a href="{{ route('admin.permission_matrix') }}" class="nav-item {{ request()->routeIs('admin.permission_matrix*') ? 'active' : '' }}" title="Dinamik Yetki Matrisi">
                            <span class="nav-icon">🔐</span>
                            <span class="nav-label">Dinamik Yetki Matrisi</span>
                        </a>

                        <a href="{{ route('admin.notification_matrix') }}" class="nav-item {{ request()->routeIs('admin.notification_matrix*') ? 'active' : '' }}" title="Bildirim Matrisi">
                            <span class="nav-icon">🔔</span>
                            <span class="nav-label">Bildirim Matrisi</span>
                        </a>

                        <a href="{{ route('admin.doa_thresholds.index') }}" class="nav-item {{ request()->routeIs('admin.doa_thresholds*') ? 'active' : '' }}" title="Bütçe Eşik Kademeleri & DOA">
                            <span class="nav-icon">⚖️</span>
                            <span class="nav-label">Bütçe Eşikleri (DOA)</span>
                        </a>

                        <a href="{{ route('admin.committees.index') }}" class="nav-item {{ request()->routeIs('admin.committees*') ? 'active' : '' }}" title="Kurullar ve Komiteler">
                            <span class="nav-icon">🏛️</span>
                            <span class="nav-label">Kurullar & Komiteler</span>
                        </a>
                    @endif

                    <a href="{{ route('admin.system_health') }}" class="nav-item {{ request()->routeIs('admin.system_health*') ? 'active' : '' }}" title="Sistem Sağlığı">
                        <span class="nav-icon">🩺</span>
                        <span class="nav-label">Sistem Sağlığı</span>
                    </a>

                    <a href="{{ route('admin.audit_logs') }}" class="nav-item {{ request()->routeIs('admin.audit_logs*') ? 'active' : '' }}" title="Denetim Logları">
                        <span class="nav-icon">📜</span>
                        <span class="nav-label">Denetim Logları</span>
                    </a>

                    <a href="{{ route('admin.notification_logs.index') }}" class="nav-item {{ request()->routeIs('admin.notification_logs*') ? 'active' : '' }}" title="Bildirim & Mail Logları">
                        <span class="nav-icon">📧</span>
                        <span class="nav-label">Bildirim & Mail Logları</span>
                    </a>

                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.settings') }}" class="nav-item {{ request()->routeIs('admin.settings*') ? 'active' : '' }}" title="Sistem Ayarları">
                            <span class="nav-icon">⚙️</span>
                            <span class="nav-label">Sistem Ayarları</span>
                        </a>
                    @endif
                @endif
            </nav>

            <div class="sidebar-footer">
                <a href="{{ route('logout') }}" class="logout-link" title="Güvenli Çıkış">
                    <span>🚪</span> <span class="logout-text">Güvenli Çıkış</span>
                </a>
            </div>
        </aside>
        <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="closeMobileSidebar()"></div>

        <!-- SAĞ ANA ALAN -->
        <div class="app-main">
            <!-- ÜST TOPBAR (2 KATMANLI: EN ÜSTTE GLOBAL ARAMA & ARAÇLAR, ALTINDA GENİŞ BAŞLIK) -->
            <header class="app-topbar">
                <!-- 1. ÜST GLOBAL YARDIMCI ÇUBUK (EN ÜSTE: OMNISEARCH + HIZLI ROL + BİLDİRİM) -->
                <div class="topbar-utility-row">
                    @auth
                        @php
                            $myActiveReceived = auth()->user()->getActiveReceivedDelegations();
                            $myActiveGiven = auth()->user()->getActiveGivenDelegation();
                        @endphp
                    @endauth

                    <div class="topbar-utility-main">
                        <div class="utility-left">
                            <!-- Mobil Menü Çekmecesi Butonu -->
                            <button type="button" class="mobile-sidebar-toggle" id="mobileSidebarToggle" onclick="toggleMobileSidebar()" aria-label="Menüyü Aç" title="Menü"><span></span><span></span><span></span></button>

                            <!-- GLOBAL OMNISEARCH (CTRL+K) - YALNIZCA MASAÜSTÜNDE SOLDA FERAH GÖRÜNÜR -->
                            <div class="topbar-search-trigger desktop-only-search" onclick="openGlobalSearchModal()" title="Global Arama (Kısayol: Ctrl + K)">
                                <div style="display: flex; align-items: center; gap: 8px; min-width: 0;">
                                    <span style="font-size: 14px; color: #0284c7;">🔍</span>
                                    <span class="search-placeholder">Proje, #etiket veya dosya ara...</span>
                                </div>
                                <kbd class="search-kbd">Ctrl K</kbd>
                            </div>

                            <!-- MOBİLDE HAMBURGER YANINDA CANLI VEKALET ÇİPİ -->
                            @auth
                                @if($myActiveReceived->isNotEmpty())
                                    <div class="mobile-only-delegation" onclick="openDelegationScopeModal({{ $myActiveReceived->first()->id }})" title="{{ $myActiveReceived->first()->delegator->name }} adına vekilsiniz (Kapsamı İncele)">
                                        <span>🛡️</span>
                                        <span><strong>{{ $myActiveReceived->first()->delegator->name }}</strong> ↗</span>
                                    </div>
                                @elseif($myActiveGiven)
                                    <div class="mobile-only-delegation is-given" onclick="openDelegationScopeModal({{ $myActiveGiven->id }})" title="Vekiliniz: {{ $myActiveGiven->delegatee->name }} (Kapsamı İncele)">
                                        <span>⏳</span>
                                        <span><strong>{{ $myActiveGiven->delegatee->name }}</strong> ↗</span>
                                    </div>
                                @endif
                            @endauth
                        </div>

                        <div class="utility-right">
                            <!-- HIZLI ROL TEST SEÇİCİSİ (YALNIZCA GELİŞTİRME / LOCAL ORTAMINDA GÖRÜNÜR) -->
                            @if(app()->environment('local', 'development', 'testing'))
                            <div class="role-switcher-wrap" title="Sistemdeki diğer kullanıcı rollerini anında simüle edin">
                                <span class="switcher-label">🎭 Hızlı Rol Testi:</span>
                                <select class="role-switcher-select" onchange="window.location.href=this.value;">
                                    @php
                                        $switchUsers = \App\Models\User::with('roles')->where('is_active', true)->get();
                                    @endphp
                                    @foreach($switchUsers as $su)
                                        <option value="{{ route('switch_user', $su->id) }}" {{ auth()->id() == $su->id ? 'selected' : '' }}>
                                            {{ $su->name }} ({{ $su->roles->first()?->name ?? 'Kullanıcı' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <!-- KURUMSAL VEKALET BUTONU & AKTİF VEKİLLİK ROZETİ (MASAÜSTÜ) -->
                            @auth
                                <div class="topbar-delegation-wrap desktop-only-delegation">
                                    @if($myActiveReceived->isNotEmpty())
                                        <div class="topbar-delegation-badge" onclick="openDelegationScopeModal({{ $myActiveReceived->first()->id }})" style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 4px 8px; font-size: 11px; color: #065f46; display: flex; align-items: center; gap: 5px; box-shadow: 0 1px 2px rgba(0,0,0,0.03); cursor: pointer;" title="{{ $myActiveReceived->first()->delegator->name }} adına vekilsiniz (Yetki Kapsamını İncele)">
                                            <span>🛡️</span>
                                            <span class="delegation-active-badge-text"><strong>{{ $myActiveReceived->first()->delegator->name }}</strong> adına vekilsiniz ↗</span>
                                        </div>
                                    @endif

                                    @if($myActiveGiven)
                                        <div class="topbar-delegation-badge" onclick="openDelegationScopeModal({{ $myActiveGiven->id }})" style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 4px 8px; font-size: 11px; color: #92400e; display: flex; align-items: center; gap: 5px; cursor: pointer;" title="Vekiliniz: {{ $myActiveGiven->delegatee->name }} (Yetki Kapsamını İncele)">
                                            <span>⏳</span>
                                            <span class="delegation-active-badge-text">Vekiliniz: <strong>{{ $myActiveGiven->delegatee->name }}</strong> ↗</span>
                                        </div>
                                    @endif

                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openGlobalDelegationModal()" style="height: 34px; padding: 0 10px; font-size: 11.5px; font-weight: 700; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; white-space: nowrap; flex-shrink: 0;" title="Kurumsal Vekalet Bırak / İncele">
                                        <span>🛡️</span> <span class="btn-delegation-label">Vekaletlerim</span>
                                    </button>
                                </div>
                            @endauth

                            <!-- BİLDİRİM ZİLİ & AÇILIR PANEL -->
                            <div class="notification-bell-wrap" style="position: relative;">
                                <button class="bell-btn" id="bellBtn" title="Bildirimler" type="button" onclick="toggleNotificationDropdown(event)">
                                    <span style="font-size: 18px;">🔔</span>
                                    @php
                                        $unreadCount = auth()->user()->inAppNotifications()->whereNull('read_at')->count();
                                    @endphp
                                    @if($unreadCount > 0)
                                        <span class="bell-badge" id="bellBadge">{{ $unreadCount }}</span>
                                    @endif
                                </button>

                                <!-- AÇILIR BİLDİRİM PENCERESİ -->
                                <div class="notification-dropdown" id="notificationDropdown" style="display: none;">
                                    <div class="notif-dropdown-header">
                                        <div class="d-flex align-center gap-2">
                                            <span class="font-weight-700 text-dark">🔔 Bildirimler</span>
                                            @if($unreadCount > 0)
                                                <span class="badge badge-primary text-xs" id="dropdownUnreadBadge">{{ $unreadCount }} Yeni</span>
                                            @endif
                                        </div>
                                        <button type="button" class="btn-text-xs text-primary" id="markAllReadBtn" style="background: none; border: none; cursor: pointer; font-size: 11px; font-weight: 600;">
                                            Tümünü Okundu Say
                                        </button>
                                    </div>
                                    <div class="notif-dropdown-body" id="notifList">
                                        @php
                                            $notifications = auth()->user()->inAppNotifications()->latest()->take(8)->get();
                                        @endphp
                                        @forelse($notifications as $notif)
                                            <a href="{{ $notif->action_url ?? '#' }}" class="notif-item {{ $notif->read_at ? 'is-read' : 'is-unread' }}">
                                                <div class="notif-item-icon">📢</div>
                                                <div class="notif-item-info">
                                                    <div class="notif-item-title">{{ $notif->title }}</div>
                                                    <div class="notif-item-text">{!! $notif->message !!}</div>
                                                    <div class="notif-item-time">{{ $notif->created_at->diffForHumans() }}</div>
                                                </div>
                                            </a>
                                        @empty
                                            <div class="notif-empty-state">
                                                <div style="font-size: 28px; margin-bottom: 6px;">🔕</div>
                                                <div class="font-weight-600 text-dark text-sm">Yeni Bildirim Yok</div>
                                                <div class="text-xs text-muted mt-1">Sistem ve projelerinizle ilgili tüm bildirimler burada listelenecektir.</div>
                                            </div>
                                        @endforelse
                                    </div>
                                    <div class="notif-dropdown-footer">
                                        <a href="{{ route('admin.notification_matrix') }}" class="text-xs text-muted font-weight-500">
                                            ⚙️ Bildirim Matrisi Ayarları
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. SATIR: MOBİL OMNISEARCH (SADECE MOBİLDE %100 FERAH GENİŞLİKLE GÖRÜNÜR) -->
                    <div class="topbar-mobile-search-row">
                        <div class="topbar-search-trigger" onclick="openGlobalSearchModal()" title="Global Arama (Kısayol: Ctrl + K)">
                            <div style="display: flex; align-items: center; gap: 8px; min-width: 0;">
                                <span style="font-size: 14px; color: #0284c7;">🔍</span>
                                <span class="search-placeholder">Proje, #etiket veya dosya ara...</span>
                            </div>
                            <kbd class="search-kbd">Ara</kbd>
                        </div>
                    </div>
                </div>

                <!-- 2. SAYFA BAŞLIK & AÇIKLAMA SATIRI (GENİŞ & FERAH BAŞLIK ALANI) -->
                @if(View::hasSection('page_title'))
                    <div class="topbar-title-row">
                        <div class="topbar-title-wrap">
                            <h1 class="page-header-title">@yield('page_title')</h1>
                            @hasSection('page_subtitle')
                                <span class="page-subtitle">@yield('page_subtitle')</span>
                            @endif
                        </div>
                    </div>
                @endif
            </header>

            <!-- BİLDİRİM ALERT ALANI (KÖKSAN MODERN BİLDİRİM ÇUBUĞU) -->
            @if(session('success'))
                <div class="app-alert alert-success" style="margin: 14px 24px 18px; padding: 14px 20px; border-radius: 10px; display: flex; align-items: center; justify-content: space-between; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; box-shadow: 0 4px 12px rgba(16,185,129,0.08);">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 18px; font-weight: 800; color: #059669;">✓</span>
                        <span style="font-size: 13.5px; font-weight: 600;">{!! session('success') !!}</span>
                    </div>
                    <button type="button" onclick="this.closest('.app-alert').remove()" style="border: none; background: none; cursor: pointer; color: #059669; font-size: 16px; font-weight: 700; padding: 4px 8px; border-radius: 4px;" title="Kapat">✕</button>
                </div>
            @endif
            @if(session('error'))
                <div class="app-alert alert-danger" style="margin: 14px 24px 18px; padding: 14px 20px; border-radius: 10px; display: flex; align-items: center; justify-content: space-between; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; box-shadow: 0 4px 12px rgba(220,38,38,0.08);">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 18px; font-weight: 800; color: #dc2626;">⚠️</span>
                        <span style="font-size: 13.5px; font-weight: 600;">{!! session('error') !!}</span>
                    </div>
                    <button type="button" onclick="this.closest('.app-alert').remove()" style="border: none; background: none; cursor: pointer; color: #dc2626; font-size: 16px; font-weight: 700; padding: 4px 8px; border-radius: 4px;" title="Kapat">✕</button>
                </div>
            @endif

            <!-- İÇERİK CONTAINER'I (İÇ SCROLL - DIŞ SCROLL YASAK) -->
            <main class="app-content-body">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- EVRENSEL ÇİFT TIKLAMA KALKANI (DOUBLE-CLICK PREVENTION) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tüm form submitlerinde çift tıklama kilidi
            document.querySelectorAll('form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.hasAttribute('data-no-lock')) {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('is-loading');
                        const originalHtml = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<span class="btn-spinner"></span> İşleniyor...';
                        setTimeout(function() {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalHtml;
                        }, 8000);
                    }
                });
            });

            // Global Bildirim Dropdown Toggle Fonksiyonu (Mobil & Masaüstü Çift Güvenceli)
            window.toggleNotificationDropdown = function(e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                var notifDropdown = document.getElementById('notificationDropdown');
                if (!notifDropdown) return;
                var isHidden = (notifDropdown.style.display === 'none' || notifDropdown.style.display === '') ||
                               (window.getComputedStyle(notifDropdown).display === 'none');
                notifDropdown.style.display = isHidden ? 'block' : 'none';
            };

            const notifDropdown = document.getElementById('notificationDropdown');
            if (notifDropdown) {
                notifDropdown.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }

            document.addEventListener('click', function(e) {
                var notifDropdown = document.getElementById('notificationDropdown');
                var bellBtn = document.getElementById('bellBtn');
                if (!notifDropdown || notifDropdown.style.display === 'none') return;
                if (bellBtn && (e.target === bellBtn || bellBtn.contains(e.target))) return;
                if (notifDropdown.contains(e.target)) return;
                notifDropdown.style.display = 'none';
            });

            // Tümünü Okundu Say AJAX
            const markAllBtn = document.getElementById('markAllReadBtn');
            if (markAllBtn) {
                markAllBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    fetch("{{ route('notifications.mark_all_read') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const badge = document.getElementById('bellBadge');
                            if (badge) badge.remove();
                            const dropBadge = document.getElementById('dropdownUnreadBadge');
                            if (dropBadge) dropBadge.remove();
                            document.querySelectorAll('.notif-item.is-unread').forEach(item => {
                                item.classList.remove('is-unread');
                                item.classList.add('is-read');
                            });
                        }
                    })
                    .catch(console.error);
                });
            }
        });
    
    // Evrensel Mobil Sidebar Drawer Fonksiyonları
    // Masaüstü Sidebar Gizleme / Gösterme (Gemini Stili)
    function toggleSidebarDesktop() {
        if (window.innerWidth <= 768) {
            toggleMobileSidebar();
            return;
        }

        var layout = document.querySelector('.app-layout');
        if (!layout) return;

        var isCollapsed = layout.classList.toggle('sidebar-collapsed');
        var btn = document.getElementById('sidebarCollapseBtn');
        if (btn) {
            btn.setAttribute('title', isCollapsed ? 'Menüyü Genişlet (Geniş Ekran)' : 'Menüyü Daralt (Geniş Ekran)');
            btn.setAttribute('aria-label', isCollapsed ? 'Menüyü Genişlet' : 'Menüyü Daralt');
        }
        try {
            localStorage.setItem('koksan_sidebar_collapsed', isCollapsed ? 'true' : 'false');
        } catch (e) {}
    }

    // Sayfa yüklendiğinde ve ekran boyutu değiştiğinde kenar çubuğu durumu
    document.addEventListener('DOMContentLoaded', function() {
        try {
            var savedState = localStorage.getItem('koksan_sidebar_collapsed');
            var layout = document.querySelector('.app-layout');
            if (layout) {
                if (window.innerWidth <= 768) {
                    layout.classList.remove('sidebar-collapsed');
                } else if (savedState === 'true') {
                    layout.classList.add('sidebar-collapsed');
                }
            }
        } catch (e) {}
    });

    window.addEventListener('resize', function() {
        var layout = document.querySelector('.app-layout');
        if (layout && window.innerWidth <= 768) {
            layout.classList.remove('sidebar-collapsed');
        }
    });

    // Klavye kısayolu (Ctrl + B) ile menüyü açma / kapatma
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && (e.key === 'b' || e.key === 'B')) {
            e.preventDefault();
            if (window.innerWidth > 768) {
                toggleSidebarDesktop();
            } else {
                toggleMobileSidebar();
            }
        }
    });

    function openMobileSidebar() {
        var s = document.getElementById('appSidebar');
        var b = document.getElementById('sidebarBackdrop');
        if (s) s.classList.add('show');
        if (b) b.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileSidebar() {
        var s = document.getElementById('appSidebar');
        var b = document.getElementById('sidebarBackdrop');
        if (s) s.classList.remove('show');
        if (b) b.classList.remove('show');
        document.body.style.overflow = '';
    }

    function toggleMobileSidebar() {
        var s = document.getElementById('appSidebar');
        if (s && s.classList.contains('show')) {
            closeMobileSidebar();
        } else {
            openMobileSidebar();
        }
    }
    </script>
    @include('partials.modals.confirm_delete_modal')
    <script>
    function confirmProjectDelete(btn, projectTitle, projectCode) {
        if (!btn) return;
        const form = btn.closest('form');
        if (!form) return;

        const displayTitle = projectCode ? ('[' + projectCode + '] ' + projectTitle) : projectTitle;

        if (typeof window.showAppConfirmModal === 'function') {
            window.showAppConfirmModal({
                title: 'Projeyi Silmeyi Onayla',
                message: '"' + displayTitle + '" projesini silmek istediğinize emin misiniz?',
                details: '⚠️ DİKKAT: Proje güvenli şekilde arşive alınacaktır (Soft Delete). İlgili görev, faz ve bütçe geçmişi korunacak fakat aktif listelerden kaldırılacaktır. Bu işlem sistem denetim loglarına kaydedilecektir.',
                confirmButtonText: 'Evet, Projeyi Sil',
                onConfirm: function() {
                    form.submit();
                }
            });
        } else {
            if (confirm('"' + displayTitle + '" projesini silmek istediğinize emin misiniz?')) {
                form.submit();
            }
        }
    }
    </script>
    <!-- Global Toast Notification Component (İşlem Sonucu Geçici Bildirim Balonu) -->
    <div id="toastNotification" style="display: none; position: fixed; bottom: 24px; right: 24px; background: #0f172a; color: #ffffff; padding: 12px 20px; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.35); z-index: 99999; align-items: center; gap: 12px; font-size: 13.5px; font-weight: 600; border-left: 4px solid #0284c7; transition: opacity 0.3s ease; opacity: 0; pointer-events: none;">
        <span id="toastIcon" style="font-size: 18px;">🔔</span>
        <span id="toastText"></span>
    </div>
    <script>
    window.showToast = function(msg, icon = '🔔', borderColor = '#0284c7') {
        const toast = document.getElementById('toastNotification');
        const text = document.getElementById('toastText');
        const iconEl = document.getElementById('toastIcon');
        if (toast && text) {
            text.innerText = msg;
            if (iconEl) iconEl.innerText = icon;
            toast.style.borderLeftColor = borderColor;
            toast.style.display = 'flex';
            setTimeout(() => { toast.style.opacity = '1'; }, 10);
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => { toast.style.display = 'none'; }, 300);
            }, 3500);
        }
    };
    </script>
    @stack('scripts')

    <!-- GLOBAL SPOTLIGHT OMNISEARCH MODAL (Ctrl + K) -->
    <div id="globalSearchBackdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 999999; align-items: flex-start; justify-content: center; padding-top: 80px;" onclick="if(event.target === this) closeGlobalSearchModal()">
        <div style="background: #ffffff; width: 100%; max-width: 680px; border-radius: 14px; box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.35); overflow: hidden; border: 1px solid #e2e8f0; display: flex; flex-direction: column; max-height: 82vh;">
            <!-- Arama Inputu -->
            <div style="padding: 16px 20px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 12px; background: #ffffff;">
                <span style="font-size: 18px; color: #64748b;">🔍</span>
                <input type="text" id="globalSearchInput" placeholder="Proje kodu/başlığı, #etiket, doküman veya ekip ara..." style="flex: 1; border: none; outline: none; font-size: 15px; font-weight: 500; color: #0f172a; background: transparent;" autocomplete="off" oninput="handleGlobalSearchInput(this.value)">
                <div id="globalSearchSpinner" style="display: none; width: 18px; height: 18px; border: 2px solid #e2e8f0; border-top-color: #0284c7; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
                <kbd style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 5px; padding: 2px 7px; font-size: 11px; font-weight: 600; color: #64748b; font-family: monospace; cursor: pointer;" onclick="closeGlobalSearchModal()">ESC</kbd>
            </div>

            <!-- Hızlı Popüler Etiketler / İpuçları -->
            <div id="globalSearchPopularTags" style="padding: 10px 20px; background: #f8fafc; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Önerilen Etiketler:</span>
                <span class="badge" onclick="searchTagQuick('Statik')" style="background: #e0f2fe; color: #0369a1; font-size: 11.5px; font-weight: 600; padding: 3px 8px; border-radius: 6px; cursor: pointer;">#Statik</span>
                <span class="badge" onclick="searchTagQuick('İSG')" style="background: #fef3c7; color: #b45309; font-size: 11.5px; font-weight: 600; padding: 3px 8px; border-radius: 6px; cursor: pointer;">#İSG</span>
                <span class="badge" onclick="searchTagQuick('Sözleşme')" style="background: #f3e8ff; color: #7e22ce; font-size: 11.5px; font-weight: 600; padding: 3px 8px; border-radius: 6px; cursor: pointer;">#Sözleşme</span>
                <span class="badge" onclick="searchTagQuick('Fizibilite')" style="background: #dcfce7; color: #15803d; font-size: 11.5px; font-weight: 600; padding: 3px 8px; border-radius: 6px; cursor: pointer;">#Fizibilite</span>
                <span class="badge" onclick="searchTagQuick('Test')" style="background: #fee2e2; color: #b91c1c; font-size: 11.5px; font-weight: 600; padding: 3px 8px; border-radius: 6px; cursor: pointer;">#Test</span>
            </div>

            <!-- Sonuç Listesi -->
            <div id="globalSearchResultsContainer" style="overflow-y: auto; padding: 12px 20px; flex: 1; min-height: 220px;">
                <div id="globalSearchPlaceholder" style="text-align: center; padding: 40px 20px; color: #94a3b8;">
                    <div style="font-size: 32px; margin-bottom: 8px;">⌨️</div>
                    <div style="font-size: 14px; font-weight: 600; color: #475569;">Aramak istediğiniz terimi yazın</div>
                    <div style="font-size: 12px; margin-top: 4px;">Örn: "PRJ-2026", "#Statik", "Şartname" veya "Serkan"</div>
                </div>
                <div id="globalSearchResultsContent" style="display: none;"></div>
            </div>

            <!-- Alt Bilgi / Kısayollar -->
            <div style="padding: 10px 20px; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; font-size: 11px; color: #64748b;">
                <div style="display: flex; gap: 14px;">
                    <span><kbd style="padding: 1px 4px; background: #fff; border: 1px solid #cbd5e1; border-radius: 3px;">↑↓</kbd> Gezin</span>
                    <span><kbd style="padding: 1px 4px; background: #fff; border: 1px solid #cbd5e1; border-radius: 3px;">↵</kbd> Seç</span>
                    <span><kbd style="padding: 1px 4px; background: #fff; border: 1px solid #cbd5e1; border-radius: 3px;">ESC</kbd> Kapat</span>
                </div>
                <div>🔒 Yalnızca yetkili olduğunuz projeler ve ekler gösterilir</div>
            </div>
        </div>
    </div>

    <script>
    let searchDebounceTimer = null;

    function openGlobalSearchModal() {
        const backdrop = document.getElementById('globalSearchBackdrop');
        const input = document.getElementById('globalSearchInput');
        if (backdrop && input) {
            backdrop.style.display = 'flex';
            setTimeout(() => input.focus(), 50);
        }
    }

    function closeGlobalSearchModal() {
        const backdrop = document.getElementById('globalSearchBackdrop');
        if (backdrop) {
            backdrop.style.display = 'none';
        }
    }

    function searchTagQuick(tag) {
        const input = document.getElementById('globalSearchInput');
        if (input) {
            input.value = '#' + tag;
            handleGlobalSearchInput(input.value);
            input.focus();
        }
    }

    // Ctrl+K veya Cmd+K ile açma, ESC ile kapatma
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
            e.preventDefault();
            openGlobalSearchModal();
        } else if (e.key === 'Escape') {
            closeGlobalSearchModal();
        }
    });

    function handleGlobalSearchInput(val) {
        clearTimeout(searchDebounceTimer);
        const query = val.trim();
        const placeholder = document.getElementById('globalSearchPlaceholder');
        const content = document.getElementById('globalSearchResultsContent');
        const spinner = document.getElementById('globalSearchSpinner');

        if (query.length < 2) {
            if (placeholder) placeholder.style.display = 'block';
            if (content) {
                content.style.display = 'none';
                content.innerHTML = '';
            }
            if (spinner) spinner.style.display = 'none';
            return;
        }

        if (spinner) spinner.style.display = 'block';

        searchDebounceTimer = setTimeout(() => {
            fetch(`/global-search?q=${encodeURIComponent(query)}`)
                .then(r => r.json())
                .then(res => {
                    if (spinner) spinner.style.display = 'none';
                    if (res.success && res.data) {
                        renderGlobalSearchResults(res.data);
                    }
                })
                .catch(() => {
                    if (spinner) spinner.style.display = 'none';
                });
        }, 220);
    }

    function renderGlobalSearchResults(data) {
        const placeholder = document.getElementById('globalSearchPlaceholder');
        const content = document.getElementById('globalSearchResultsContent');
        if (!content) return;

        if (placeholder) placeholder.style.display = 'none';
        content.style.display = 'block';

        if (data.total_count === 0) {
            content.innerHTML = `
                <div style="text-align: center; padding: 40px 20px; color: #64748b;">
                    <div style="font-size: 28px; margin-bottom: 6px;">🔍</div>
                    <div style="font-weight: 700; color: #0f172a;">"${data.query}" ile eşleşen sonuç bulunamadı</div>
                    <div style="font-size: 12px; margin-top: 4px;">Farklı bir proje adı, dosya etiketi veya personel adı deneyebilirsiniz.</div>
                </div>
            `;
            return;
        }

        let html = '';

        // 1. PROJELER
        if (data.projects && data.projects.length > 0) {
            html += `
                <div style="margin-bottom: 16px;">
                    <div style="font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                        <span>📁</span> Projeler (${data.projects.length})
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
            `;
            data.projects.forEach(p => {
                html += `
                    <a href="${p.url}" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: inherit; transition: all 0.15s ease;" onmouseover="this.style.borderColor='#0284c7'; this.style.background='#f0f9ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='#f8fafc';">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 11px; font-weight: 800; background: #0284c7; color: #fff; padding: 2px 7px; border-radius: 4px;">${p.code}</span>
                            <div>
                                <div style="font-size: 13.5px; font-weight: 700; color: #0f172a;">${p.title}</div>
                                <div style="font-size: 11.5px; color: #64748b;">${p.department_name} • Ynt: ${p.manager_name}</div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="badge" style="background: #e2e8f0; color: #334155; font-size: 11px; font-weight: 600;">${p.category || 'Genel'}</span>
                            <span style="font-size: 12px; font-weight: 700; color: #0284c7;">%${p.progress}</span>
                        </div>
                    </a>
                `;
            });
            html += `</div></div>`;
        }

        // 2. DOSYALAR & ETİKETLER
        if (data.files && data.files.length > 0) {
            html += `
                <div style="margin-bottom: 16px;">
                    <div style="font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                        <span>🏷️</span> Dosyalar & Etiketler (${data.files.length})
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
            `;
            data.files.forEach(f => {
                let tagsHtml = '';
                if (f.tags && f.tags.length > 0) {
                    tagsHtml = f.tags.map(t => `<span style="background: #e0f2fe; color: #0369a1; font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 4px;">#${t}</span>`).join(' ');
                }
                html += `
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; transition: all 0.15s ease;">
                        <div style="display: flex; align-items: center; gap: 10px; flex: 1; overflow: hidden;">
                            <span style="font-size: 16px;">📄</span>
                            <div style="overflow: hidden;">
                                <a href="${f.project_url}" style="font-size: 13px; font-weight: 700; color: #0f172a; text-decoration: none; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" onmouseover="this.style.color='#0284c7'" onmouseout="this.style.color='#0f172a'">${f.original_name}</a>
                                <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: #64748b; margin-top: 3px;">
                                    <span style="font-weight: 700; color: #475569;">[${f.project_code}]</span>
                                    <span>${f.file_size}</span>
                                    <span>•</span>
                                    <div style="display: flex; gap: 3px;">${tagsHtml}</div>
                                </div>
                            </div>
                        </div>
                        <a href="${f.download_url}" class="btn btn-sm btn-outline" style="font-size: 11px; padding: 3px 8px; border-radius: 5px; text-decoration: none; margin-left: 10px; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">
                            <span>⬇️</span> İndir
                        </a>
                    </div>
                `;
            });
            html += `</div></div>`;
        }

        // 3. EKİP & KULLANICILAR
        if (data.users && data.users.length > 0) {
            html += `
                <div style="margin-bottom: 12px;">
                    <div style="font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                        <span>👤</span> Ekip & Kullanıcılar (${data.users.length})
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
            `;
            data.users.forEach(u => {
                html += `
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 28px; height: 28px; border-radius: 50%; background: #0284c7; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px;">
                                ${u.name.charAt(0)}
                            </div>
                            <div>
                                <div style="font-size: 13px; font-weight: 700; color: #0f172a;">${u.name}</div>
                                <div style="font-size: 11px; color: #64748b;">${u.job_title} • ${u.department}</div>
                            </div>
                        </div>
                        <span style="font-size: 11px; color: #64748b;">${u.email}</span>
                    </div>
                `;
            });
            html += `</div></div>`;
        }

        content.innerHTML = html;
    }

    // ==========================================
    // GLOBAL ÇOKLU TIKLAMA & ÇİFT SUBMIT ENGELLEME
    // ==========================================
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (!form || form.tagName !== 'FORM') return;
        
        if (form.checkValidity && !form.checkValidity()) {
            return;
        }

        if (form.dataset.submitting === 'true') {
            e.preventDefault();
            return false;
        }

        form.dataset.submitting = 'true';

        const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
        if (submitBtn) {
            submitBtn.style.pointerEvents = 'none';
            submitBtn.style.opacity = '0.7';
            setTimeout(() => {
                if (form.dataset.submitting === 'true') {
                    submitBtn.disabled = true;
                }
            }, 100);
        }
    }, true);

    // ==========================================
    // KURUMSAL VEKALET (USER DEPUTYSHIP) MODALI & AJAX MOTORU
    // ==========================================
    function openGlobalDelegationModal() {
        const m = document.getElementById('modalGlobalDelegation');
        if (!m) return;
        m.style.display = 'flex';
        loadDelegationsData();
    }

    function closeGlobalDelegationModal() {
        const m = document.getElementById('modalGlobalDelegation');
        if (m) m.style.display = 'none';
    }

    function switchDelegationTab(tab) {
        document.getElementById('panelNewDelegation').style.display = (tab === 'new') ? 'block' : 'none';
        document.getElementById('panelListDelegations').style.display = (tab === 'list') ? 'block' : 'none';
        document.getElementById('btnTabNewDelegation').className = (tab === 'new') ? 'btn btn-sm btn-primary' : 'btn btn-sm btn-outline-secondary';
        document.getElementById('btnTabListDelegations').className = (tab === 'list') ? 'btn btn-sm btn-primary' : 'btn btn-sm btn-outline-secondary';
    }

    // ==========================================
    // ARAMA YAPILABİLEN PERSONEL DROPDOWN MOTORU
    // ==========================================
    let allDelegationCandidateUsers = [];

    function populateDelegationUsers(users) {
        allDelegationCandidateUsers = users || [];
        const countBadge = document.getElementById('delegationUserCountBadge');
        if (countBadge) countBadge.innerText = `${allDelegationCandidateUsers.length} Personel`;
        renderDelegationUserItems(allDelegationCandidateUsers);
    }

    function renderDelegationUserItems(users) {
        const container = document.getElementById('delegationUsersListItems');
        const noResult = document.getElementById('delegationUsersNoResult');
        if (!container) return;

        if (!users || users.length === 0) {
            container.innerHTML = '';
            if (noResult) noResult.style.display = 'block';
            return;
        }

        if (noResult) noResult.style.display = 'none';

        const currentSelectedId = document.getElementById('delegationHiddenDelegateeId')?.value;

        let html = '';
        users.forEach(u => {
            const deptName = u.bolum ? (u.bolum.ad || u.bolum.name || 'Merkez') : 'Merkez';
            const unvan = u.unvan || 'Personel';
            const initial = (u.name || '?').charAt(0).toUpperCase();
            const isSelected = String(currentSelectedId) === String(u.id);

            html += `
                <div class="delegation-user-option" data-id="${u.id}" onclick="selectDelegationUser(${u.id}, '${escapeJs(u.name)}', '${escapeJs(deptName)}', '${escapeJs(unvan)}')" style="padding: 10px 14px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 10px; border-bottom: 1px solid #f1f5f9; background: ${isSelected ? '#f0f9ff' : '#ffffff'}; transition: background 0.15s;">
                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: ${isSelected ? '#0284c7' : '#e0f2fe'}; color: ${isSelected ? '#ffffff' : '#0284c7'}; font-weight: 750; font-size: 13px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            ${initial}
                        </div>
                        <div style="min-width: 0;">
                            <div class="delegation-opt-name" style="font-size: 13px; font-weight: 700; color: ${isSelected ? '#0284c7' : '#0f172a'}; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                ${escapeHtml(u.name)}
                            </div>
                            <div style="font-size: 11px; color: #64748b; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                🏢 <strong>${escapeHtml(deptName)}</strong> • ${escapeHtml(unvan)}
                            </div>
                        </div>
                    </div>
                    <div class="delegation-user-check" id="delCheck_${u.id}" style="display: ${isSelected ? 'block' : 'none'}; color: #0284c7; font-weight: 800; font-size: 14px;">✓</div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    function openDelegationUserDropdown(e) {
        if (e) e.stopPropagation();
        const menu = document.getElementById('delegationUserDropdownMenu');
        const wrap = document.getElementById('delegationInputWrap');
        if (!menu) return;

        menu.style.display = 'block';
        if (wrap) {
            wrap.style.borderColor = '#0284c7';
            wrap.style.boxShadow = '0 0 0 3px rgba(2, 132, 199, 0.15)';
        }

        const input = document.getElementById('delegationSearchInput');
        if (input && document.activeElement !== input) {
            input.focus();
        }
    }

    function closeDelegationUserDropdown() {
        const menu = document.getElementById('delegationUserDropdownMenu');
        const wrap = document.getElementById('delegationInputWrap');
        if (menu) menu.style.display = 'none';
        if (wrap) {
            wrap.style.borderColor = '#cbd5e1';
            wrap.style.boxShadow = 'none';
        }
    }

    function filterDelegationUsers(query) {
        const menu = document.getElementById('delegationUserDropdownMenu');
        if (menu && menu.style.display === 'none') {
            openDelegationUserDropdown();
        }

        const q = (query || '').toLowerCase().trim();
        if (!q) {
            renderDelegationUserItems(allDelegationCandidateUsers);
            return;
        }

        const filtered = allDelegationCandidateUsers.filter(u => {
            const name = (u.name || '').toLowerCase();
            const unvan = (u.unvan || '').toLowerCase();
            const dept = (u.bolum ? (u.bolum.ad || u.bolum.name || '') : '').toLowerCase();
            return name.includes(q) || unvan.includes(q) || dept.includes(q);
        });

        renderDelegationUserItems(filtered);
    }

    function selectDelegationUser(id, name, dept, unvan) {
        const hiddenInput = document.getElementById('delegationHiddenDelegateeId');
        const searchInput = document.getElementById('delegationSearchInput');
        const clearBtn = document.getElementById('delegationClearBtn');
        const wrap = document.getElementById('delegationInputWrap');

        if (hiddenInput) hiddenInput.value = id;
        if (searchInput) {
            searchInput.value = `${name} (${dept} - ${unvan})`;
            searchInput.style.fontWeight = '700';
            searchInput.style.color = '#0369a1';
        }
        if (clearBtn) clearBtn.style.display = 'block';
        if (wrap) {
            wrap.style.background = '#f0f9ff';
            wrap.style.borderColor = '#0284c7';
        }

        document.querySelectorAll('.delegation-user-check').forEach(el => el.style.display = 'none');
        const myCheck = document.getElementById(`delCheck_${id}`);
        if (myCheck) myCheck.style.display = 'block';

        closeDelegationUserDropdown();
    }

    function clearDelegationUserSelection(e) {
        if (e) e.stopPropagation();
        const hiddenInput = document.getElementById('delegationHiddenDelegateeId');
        const searchInput = document.getElementById('delegationSearchInput');
        const clearBtn = document.getElementById('delegationClearBtn');
        const wrap = document.getElementById('delegationInputWrap');

        if (hiddenInput) hiddenInput.value = '';
        if (searchInput) {
            searchInput.value = '';
            searchInput.style.fontWeight = 'normal';
            searchInput.style.color = '#0f172a';
            searchInput.placeholder = '1500+ personel içinde ara (İsim, Departman, Unvan)...';
            searchInput.focus();
        }
        if (clearBtn) clearBtn.style.display = 'none';
        if (wrap) {
            wrap.style.background = '#ffffff';
            wrap.style.borderColor = '#cbd5e1';
        }

        document.querySelectorAll('.delegation-user-check').forEach(el => el.style.display = 'none');
        renderDelegationUserItems(allDelegationCandidateUsers);
        openDelegationUserDropdown();
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function escapeJs(text) {
        if (!text) return '';
        return text.toString().replace(/\\/g, '\\\\').replace(/'/g, "\\'");
    }

    // Modal dışı tıklamada dropdown'ı kapat
    document.addEventListener('click', function(e) {
        const container = document.getElementById('delegationUserSearchContainer');
        if (container && !container.contains(e.target)) {
            closeDelegationUserDropdown();
        }
    });

    let cachedDelegationPermissions = [];

    function renderDelegationPermissions(groups) {
        cachedDelegationPermissions = groups || [];
        const container = document.getElementById('delegationDynamicPermsList');
        if (!container) return;

        if (!groups || groups.length === 0) {
            container.innerHTML = '<div style="background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 14px; text-align: center; color: #64748b; font-size: 12px;">Devredebileceğiniz tanımlı bir operasyonel yetki bulunamadı.</div>';
            updateDelegationPermBadge();
            return;
        }

        let html = '';
        groups.forEach((group, gIdx) => {
            html += `
                <div class="delegation-perm-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; padding-bottom: 6px; border-bottom: 1px solid #f1f5f9;">
                        <span style="font-weight: 700; font-size: 12px; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                            📁 ${group.group}
                            <span style="font-size: 10px; color: #64748b; font-weight: normal;">(${group.permissions.length} yetki)</span>
                        </span>
                        <button type="button" onclick="toggleDelegationGroup(${gIdx})" style="background: none; border: none; font-size: 11px; font-weight: 600; color: #0284c7; cursor: pointer; padding: 2px 6px; border-radius: 4px;">
                            Tümünü Seç / Kaldır
                        </button>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr; gap: 6px;">
            `;

            group.permissions.forEach(p => {
                html += `
                    <label class="delegation-perm-item">
                        <input type="checkbox" name="scope[]" value="${p.slug}" class="delegation-perm-chk" data-category="${p.category || 'other'}" data-group-idx="${gIdx}" checked onchange="updateDelegationPermBadge()" style="accent-color: #0284c7; width: 16px; height: 16px; margin-top: 2px; cursor: pointer;">
                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                <strong style="font-size: 12px; color: #0f172a;">${p.name}</strong>
                                <code style="font-size: 10px; background: #f1f5f9; color: #475569; padding: 1px 5px; border-radius: 3px; font-family: monospace;">${p.slug}</code>
                            </div>
                            <div style="font-size: 11px; color: #64748b; margin-top: 2px; line-height: 1.35;">
                                ${p.desc}
                            </div>
                        </div>
                    </label>
                `;
            });

            html += `
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        updateDelegationPermBadge();
    }

    function updateDelegationPermBadge() {
        const chks = document.querySelectorAll('.delegation-perm-chk');
        const checked = document.querySelectorAll('.delegation-perm-chk:checked');
        const badge = document.getElementById('delegationSelectedPermCountBadge');
        if (!badge) return;

        if (chks.length === 0) {
            badge.textContent = 'Yetki Yok';
            badge.style.background = '#f1f5f9';
            badge.style.color = '#64748b';
            badge.style.borderColor = '#cbd5e1';
            return;
        }

        if (checked.length === chks.length) {
            badge.textContent = `🌟 Tam Yetki (${checked.length}/${chks.length})`;
            badge.style.background = '#dcfce7';
            badge.style.color = '#15803d';
            badge.style.borderColor = '#86efac';
        } else if (checked.length === 0) {
            badge.textContent = `0 Yetki Seçildi (Geçersiz)`;
            badge.style.background = '#fee2e2';
            badge.style.color = '#b91c1c';
            badge.style.borderColor = '#fca5a5';
        } else {
            badge.textContent = `🎯 ${checked.length}/${chks.length} Yetki (Kısmi Devir)`;
            badge.style.background = '#e0f2fe';
            badge.style.color = '#0369a1';
            badge.style.borderColor = '#7dd3fc';
        }
    }

    function setDelegationPermPreset(preset) {
        const chks = document.querySelectorAll('.delegation-perm-chk');
        chks.forEach(chk => {
            if (preset === 'all') {
                chk.checked = true;
            } else if (preset === 'clear') {
                chk.checked = false;
            } else if (preset === 'approvals') {
                chk.checked = chk.getAttribute('data-category') === 'approvals';
            } else if (preset === 'tasks') {
                chk.checked = chk.getAttribute('data-category') === 'tasks';
            }
        });
        updateDelegationPermBadge();
    }

    function toggleDelegationGroup(groupIdx) {
        const groupChks = document.querySelectorAll(`.delegation-perm-chk[data-group-idx="${groupIdx}"]`);
        const allChecked = Array.from(groupChks).every(c => c.checked);
        groupChks.forEach(c => c.checked = !allChecked);
        updateDelegationPermBadge();
    }

    function loadDelegationsData() {
        const listDiv = document.getElementById('delegationsListArea');
        if (listDiv) listDiv.innerHTML = '<div style="padding: 24px; text-align: center; color: #64748b;">⏳ Vekalet verileri yükleniyor...</div>';

        fetch('/delegations', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;

            // Arama yapılabilen personel havuzunu doldur
            if (data.users) {
                populateDelegationUsers(data.users);
            }

            // Dinamik yetki listesini oluştur
            if (data.my_permissions) {
                renderDelegationPermissions(data.my_permissions);
            }

            // Proje seçim listesini oluştur
            if (data.my_projects) {
                populateDelegationProjects(data.my_projects);
            }

            // Render List (Aktif ve Geçmiş Vekaletleri Ayrı Bölümlerde Göster)
            function renderDelegationCard(d, isGiven) {
                const isActive = d.is_active;
                const scopeLabels = Array.isArray(d.scope) ? d.scope.map(s => data.scopes[s] || s).join(', ') : 'Tüm Yetkiler';
                const startDate = d.starts_at_formatted || new Date(d.starts_at).toLocaleDateString('tr-TR', {day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit'});
                const endDate = d.ends_at_formatted || new Date(d.ends_at).toLocaleDateString('tr-TR', {day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit'});
                
                const otherUser = isGiven ? d.delegatee : d.delegator;
                const otherUserName = otherUser ? otherUser.name : 'Bilinmeyen';
                const otherUserRole = otherUser ? (otherUser.unvan || '') : '';
                
                const reasonCat = d.reason_category ? `<span class="badge" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-size: 10.5px; padding: 1px 6px; border-radius: 4px;">🏷️ ${d.reason_category}</span>` : '';
                const noteText = d.notes ? `<div style="font-size: 11px; color: #475569; font-style: italic; margin-top: 3px;">💬 "${d.notes}"</div>` : '';
                
                let projectScopeBadge = '<span style="background: #fefce8; color: #854d0e; border: 1px solid #fef08a; padding: 1px 7px; border-radius: 4px; font-size: 10.5px; font-weight: 700;">🌟 Tüm Projeler</span>';
                if (d.project_scope_type === 'selected') {
                    const pCount = (d.project_ids || []).length;
                    const pNames = Object.values(d.project_names || {}).join(', ');
                    projectScopeBadge = `<span style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; padding: 1px 7px; border-radius: 4px; font-size: 10.5px; font-weight: 700;" title="${pNames || 'Seçili Projeler'}">📁 ${pCount} Seçili Proje</span>`;
                }

                const actionsCount = d.actions_count || (d.actions ? d.actions.length : 0);
                const accordionId = `acc_del_${isGiven ? 'g' : 'r'}_${d.id}`;
                const iconId = `icon_acc_${isGiven ? 'g' : 'r'}_${d.id}`;

                let actionsTableHtml = '';
                if (!d.actions || d.actions.length === 0) {
                    actionsTableHtml = '<div style="font-size: 11.5px; color: #64748b; text-align: center; padding: 10px;">Bu vekalet kapsamında vekil tarafından henüz bir işlem (görev, onay vb.) kaydı bulunmamaktadır.</div>';
                } else {
                    actionsTableHtml = `
                        <div style="max-height: 180px; overflow-y: auto;">
                            <table style="width: 100%; font-size: 11px; border-collapse: collapse;">
                                <thead>
                                    <tr style="border-bottom: 1px solid #cbd5e1; color: #475569; text-align: left;">
                                        <th style="padding: 4px 6px;">Tarih</th>
                                        <th style="padding: 4px 6px;">Proje</th>
                                        <th style="padding: 4px 6px;">İşlem</th>
                                        <th style="padding: 4px 6px;">Açıklama</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${d.actions.map(a => `
                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                            <td style="padding: 4px 6px; color: #64748b; white-space: nowrap;">${a.created_at}</td>
                                            <td style="padding: 4px 6px; font-weight: 600; color: #0f172a; white-space: nowrap;">${a.project_title}</td>
                                            <td style="padding: 4px 6px;"><span class="badge" style="background: #e2e8f0; color: #334155; font-size: 9.5px;">${a.action}</span></td>
                                            <td style="padding: 4px 6px; color: #334155;">${a.description}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    `;
                }

                return `
                    <div style="background: #ffffff; border: 1.5px solid ${isActive ? '#bae6fd' : '#e2e8f0'}; border-radius: 10px; padding: 12px 14px; margin-bottom: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap;">
                            <div style="flex: 1; min-width: 260px;">
                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <strong style="font-size: 13.5px; color: #0f172a;">${otherUserName}</strong>
                                    ${otherUserRole ? `<span style="font-size: 11px; color: #64748b;">(${otherUserRole})</span>` : ''}
                                    <span class="badge" style="font-size: 10.5px; padding: 2px 7px; border-radius: 4px; ${isActive ? 'background: #dcfce7; color: #15803d;' : 'background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1;'}">
                                        ${isActive ? (isGiven ? '🟢 Aktif Vekalet' : '🟢 Vekilsiniz') : '⚪ Sona Erdi / İptal'}
                                    </span>
                                    <span style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-size: 10.5px; font-weight: 700; padding: 1px 6px; border-radius: 4px;">
                                        ⏳ ${d.duration_text || ''}
                                    </span>
                                    ${reasonCat}
                                </div>
                                <div style="font-size: 11.5px; color: #475569; margin-top: 4px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <span>📅 <strong>${startDate}</strong> — <strong>${endDate}</strong></span>
                                    ${projectScopeBadge}
                                </div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 3px;">
                                    <span>🎯 Yetkiler: ${scopeLabels}</span>
                                </div>
                                ${!isActive && d.cancel_reason ? `
                                    <div style="font-size: 11px; color: #dc2626; margin-top: 3px;">
                                        🛑 <strong>İptal Gerekçesi:</strong> ${d.cancel_reason} ${d.cancelled_at ? `(${d.cancelled_at})` : ''}
                                    </div>
                                ` : ''}
                                ${noteText}
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                                <button type="button" class="btn btn-xs btn-outline-primary" onclick="openDelegationScopeModal(${d.id})" style="font-weight: 700; font-size: 11px; padding: 4px 8px; border-radius: 6px; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; cursor: pointer;">
                                    🎯 Yetki Kapsamı
                                </button>
                                ${isActive && isGiven ? `
                                    <button type="button" class="btn btn-xs btn-outline-danger" onclick="cancelDelegation(${d.id})" style="font-weight: 700; font-size: 11px; padding: 4px 10px; border-radius: 6px; cursor: pointer;">
                                        ✕ İptal Et
                                    </button>
                                ` : ''}
                            </div>
                        </div>

                        <!-- VARSAYILAN KAPALI AKORDEON: YAPILAN İŞLEMLER -->
                        <div style="margin-top: 8px; border-top: 1px dashed #e2e8f0; padding-top: 6px;">
                            <button type="button" onclick="toggleDelegationActionsAccordion('${accordionId}', '${iconId}')" style="background: transparent; border: none; padding: 0; font-size: 11.5px; font-weight: 700; color: #0284c7; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                <span id="${iconId}">▶</span>
                                <span>${isGiven ? 'Vekilin Sizin Adınıza Yaptığı İşlemler' : 'Bu Vekalet Kapsamında Yaptığınız İşlemler'} (${actionsCount} Kayıt)</span>
                                <span style="font-size: 10px; color: #94a3b8; font-weight: normal;">(Varsayılan Kapalı)</span>
                            </button>

                            <div id="${accordionId}" style="display: none; margin-top: 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px;">
                                ${actionsTableHtml}
                            </div>
                        </div>
                    </div>
                `;
            }

            // Render List
            let html = '';
            
            // 1. Verilen Vekaletler (Sizin Bıraktıklarınız)
            const givenActive = data.given.filter(d => d.is_active);
            const givenPast = data.given.filter(d => !d.is_active);

            html += '<div style="margin-bottom: 24px;"><h4 style="font-size: 13.5px; font-weight: 800; color: #0f172a; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;"><span>📤</span> Sizin Bıraktığınız Vekaletler</h4>';
            
            if (givenActive.length === 0 && givenPast.length === 0) {
                html += '<div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 12px; font-size: 12px; color: #64748b; text-align: center;">Henüz tanımlı bir vekaletiniz bulunmuyor.</div>';
            } else {
                if (givenActive.length > 0) {
                    html += '<div style="font-size: 12px; font-weight: 700; color: #166534; margin: 8px 0 6px;">🟢 Aktif Vekaletler (Devam Edenler)</div>';
                    givenActive.forEach(d => { html += renderDelegationCard(d, true); });
                }
                if (givenPast.length > 0) {
                    html += '<div style="font-size: 12px; font-weight: 700; color: #64748b; margin: 12px 0 6px;">📁 Geçmiş Vekaletler (Sona Eren / İptal Edilenler)</div>';
                    givenPast.forEach(d => { html += renderDelegationCard(d, true); });
                }
            }
            html += '</div>';

            // 2. Alınan Vekaletler (Vekil Olduklarınız)
            const receivedActive = data.received.filter(d => d.is_active);
            const receivedPast = data.received.filter(d => !d.is_active);

            html += '<div><h4 style="font-size: 13.5px; font-weight: 800; color: #0f172a; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;"><span>📥</span> Adınıza Tanımlanan Vekaletler (Vekil Olduklarınız)</h4>';
            if (receivedActive.length === 0 && receivedPast.length === 0) {
                html += '<div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 12px; font-size: 12px; color: #64748b; text-align: center;">Adınıza bırakılmış aktif bir vekalet bulunmuyor.</div>';
            } else {
                if (receivedActive.length > 0) {
                    html += '<div style="font-size: 12px; font-weight: 700; color: #166534; margin: 8px 0 6px;">🟢 Aktif Vekillikleriniz</div>';
                    receivedActive.forEach(d => { html += renderDelegationCard(d, false); });
                }
                if (receivedPast.length > 0) {
                    html += '<div style="font-size: 12px; font-weight: 700; color: #64748b; margin: 12px 0 6px;">📁 Geçmiş Vekillikleriniz</div>';
                    receivedPast.forEach(d => { html += renderDelegationCard(d, false); });
                }
            }
            html += '</div>';

            if (listDiv) listDiv.innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            if (listDiv) listDiv.innerHTML = '<div style="padding: 16px; color: #dc2626; text-align: center;">Veriler alınamadı!</div>';
        });
    }

    function toggleDelegationActionsAccordion(accordionId, iconId) {
        const acc = document.getElementById(accordionId);
        const icon = document.getElementById(iconId);
        if (!acc) return;

        if (acc.style.display === 'none' || !acc.style.display) {
            acc.style.display = 'block';
            if (icon) icon.textContent = '▼';
        } else {
            acc.style.display = 'none';
            if (icon) icon.textContent = '▶';
        }
    }

    function populateDelegationProjects(projects) {
        const container = document.getElementById('delegationProjectsCheckboxList');
        if (!container) return;
        if (!projects || projects.length === 0) {
            container.innerHTML = '<div style="font-size: 11px; color: #64748b; text-align: center;">Erişilebilir proje bulunamadı.</div>';
            return;
        }
        container.innerHTML = projects.map(p => `
            <label style="display: flex; align-items: center; gap: 6px; font-size: 11.5px; color: #334155; cursor: pointer; padding: 3px 6px; border-radius: 4px; transition: background 0.15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                <input type="checkbox" name="project_ids[]" value="${p.id}" class="delegation-proj-chk" onchange="updateDelegationProjectCount()" style="accent-color: #0284c7; cursor: pointer;">
                <span style="font-weight: 700; color: #0284c7;">[${p.code || ('PRJ-' + p.id)}]</span>
                <span>${p.title}</span>
            </label>
        `).join('');
        updateDelegationProjectCount();
    }

    function updateDelegationProjectCount() {
        const checked = document.querySelectorAll('.delegation-proj-chk:checked').length;
        const badge = document.getElementById('delegationSelectedProjectCount');
        if (badge) badge.textContent = `${checked} Proje Seçildi`;
    }

    function toggleDelegationProjectSelection(isSelected) {
        const area = document.getElementById('delegationProjectSelectArea');
        if (area) area.style.display = isSelected ? 'block' : 'none';
    }

    // Modal Bildirim ve İptal Fonksiyonları (Alert/Prompt yerine)
    let feedbackReloadOnClose = false;
    function showDelegationFeedback(title, message, isSuccess = true, reloadOnClose = false) {
        feedbackReloadOnClose = reloadOnClose;
        const modal = document.getElementById('modalDelegationFeedback');
        const header = document.getElementById('delegationFeedbackHeader');
        const icon = document.getElementById('delegationFeedbackIcon');
        const titleEl = document.getElementById('delegationFeedbackTitle');
        const msgEl = document.getElementById('delegationFeedbackMessage');
        if (!modal) {
            alert(message);
            if (reloadOnClose) window.location.reload();
            return;
        }
        titleEl.textContent = title;
        msgEl.textContent = message;
        if (isSuccess) {
            header.style.background = '#f0fdf4';
            header.style.borderBottomColor = '#bbf7d0';
            icon.textContent = '✓';
            icon.style.color = '#15803d';
            titleEl.style.color = '#166534';
        } else {
            header.style.background = '#fef2f2';
            header.style.borderBottomColor = '#fecaca';
            icon.textContent = '✕';
            icon.style.color = '#dc2626';
            titleEl.style.color = '#991b1b';
        }
        modal.style.display = 'flex';
    }

    function closeDelegationFeedbackModal() {
        const modal = document.getElementById('modalDelegationFeedback');
        if (modal) modal.style.display = 'none';
        if (feedbackReloadOnClose) {
            window.location.reload();
        }
    }

    function submitDelegationForm(e) {
        e.preventDefault();
        const form = document.getElementById('formCreateDelegation');
        const delegateeId = document.getElementById('delegationHiddenDelegateeId')?.value;
        if (!delegateeId) {
            showDelegationFeedback('Eksik Bilgi', 'Lütfen vekil tayin edilecek personeli arama kutusundan arayarak seçiniz.', false);
            openDelegationUserDropdown();
            return;
        }

        const checkedPerms = document.querySelectorAll('.delegation-perm-chk:checked');
        if (checkedPerms.length === 0) {
            showDelegationFeedback('Eksik Bilgi', 'Lütfen vekilinize devretmek istediğiniz en az bir yetki seçiniz.', false);
            return;
        }

        const projectScopeType = form.querySelector('input[name="project_scope_type"]:checked')?.value || 'all';
        if (projectScopeType === 'selected') {
            const checkedProjects = form.querySelectorAll('.delegation-proj-chk:checked');
            if (checkedProjects.length === 0) {
                showDelegationFeedback('Eksik Bilgi', 'Proje bazlı vekalet seçildiğinde en az bir proje seçmelisiniz.', false);
                return;
            }
        }

        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');

        submitBtn.disabled = true;
        submitBtn.textContent = 'Kaydediliyor...';

        fetch('/delegations', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.textContent = '✓ Vekaleti Tanımla ve Bildir';

            if (data.success) {
                form.reset();
                clearDelegationUserSelection();
                setDelegationPermPreset('all');
                toggleDelegationProjectSelection(false);
                
                const shouldReload = window.location.pathname.startsWith('/projects/') || window.location.pathname.includes('/dashboard');
                showDelegationFeedback('Vekalet Başarıyla Tanımlandı', data.message, true, shouldReload);
                if (!shouldReload) {
                    switchDelegationTab('list');
                    loadDelegationsData();
                }
            } else {
                showDelegationFeedback('Hata Oluştu', data.message || 'Vekalet kaydedilemedi!', false);
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.textContent = '✓ Vekaleti Tanımla ve Bildir';
            console.error(err);
            showDelegationFeedback('İletişim Hatası', 'Sunucu ile iletişim kurulurken bir hata oluştu.', false);
        });
    }

    function cancelDelegation(id) {
        const modal = document.getElementById('modalDelegationCancelConfirm');
        const targetInput = document.getElementById('delegationCancelTargetId');
        const reasonInput = document.getElementById('delegationCancelReasonInput');
        if (!modal) return;
        targetInput.value = id;
        if (reasonInput) reasonInput.value = 'İzin / Görev Tamamlandı';
        modal.style.display = 'flex';
    }

    function closeDelegationCancelModal() {
        const modal = document.getElementById('modalDelegationCancelConfirm');
        if (modal) modal.style.display = 'none';
    }

    function executeCancelDelegation() {
        const targetId = document.getElementById('delegationCancelTargetId')?.value;
        const reasonInput = document.getElementById('delegationCancelReasonInput')?.value;
        const btn = document.getElementById('btnExecuteCancelDelegation');
        if (!targetId) return;

        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Sonlandırılıyor...';
        }

        fetch(`/delegations/${targetId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                reason: (reasonInput || '').trim() || 'Kullanıcı talebiyle erken sonlandırıldı'
            })
        })
        .then(res => res.json())
        .then(data => {
            if (btn) {
                btn.disabled = false;
                btn.textContent = '✕ Evet, Vekaleti Sonlandır';
            }
            closeDelegationCancelModal();
            if (data.success) {
                const shouldReload = window.location.pathname.startsWith('/projects/') || window.location.pathname.includes('/dashboard');
                showDelegationFeedback('Vekalet Sonlandırıldı', data.message, true, shouldReload);
                if (!shouldReload) {
                    loadDelegationsData();
                }
            } else {
                showDelegationFeedback('Hata', data.message || 'İptal edilemedi!', false);
            }
        })
        .catch(err => {
            if (btn) {
                btn.disabled = false;
                btn.textContent = '✕ Evet, Vekaleti Sonlandır';
            }
            closeDelegationCancelModal();
            console.error(err);
            showDelegationFeedback('Hata', 'Sunucu iletişim hatası oluştu!', false);
        });
    }
    </script>

    <!-- GLOBAL KURUMSAL VEKALET MERKEZİ MODALI -->
    <div id="modalGlobalDelegation" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.65); align-items: center; justify-content: center; z-index: 10000; backdrop-filter: blur(4px);">
        <div style="background: #ffffff; width: 740px; max-width: 95vw; max-height: 90vh; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35); overflow: hidden; display: flex; flex-direction: column;">
            <!-- Modal Header -->
            <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 18px 22px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 22px;">🛡️</span>
                    <div>
                        <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: #ffffff;">Kurumsal Vekalet & Temsil Merkezi</h3>
                        <div style="font-size: 11.5px; color: #94a3b8;">Yıllık izin, yurt dışı görevi veya rapor süreçlerinde vekil tayini</div>
                    </div>
                </div>
                <button type="button" onclick="closeGlobalDelegationModal()" style="background: transparent; border: none; color: #ffffff; font-size: 18px; cursor: pointer;">✕</button>
            </div>

            <!-- Modal Nav Tabs -->
            <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 10px 22px; display: flex; gap: 8px;">
                <button type="button" id="btnTabNewDelegation" class="btn btn-sm btn-primary" onclick="switchDelegationTab('new')" style="font-size: 12px; font-weight: 700; padding: 6px 14px; border-radius: 6px;">
                    ➕ Yeni Vekalet Bırak
                </button>
                <button type="button" id="btnTabListDelegations" class="btn btn-sm btn-outline-secondary" onclick="switchDelegationTab('list')" style="font-size: 12px; font-weight: 700; padding: 6px 14px; border-radius: 6px;">
                    📋 Vekaletlerim & Vekil Olduklarım
                </button>
            </div>

            <!-- Modal Body -->
            <div style="padding: 22px; overflow-y: auto; flex: 1;">
                <!-- Panel 1: Yeni Vekalet Formu -->
                <div id="panelNewDelegation" style="display: block;">
                    <form id="formCreateDelegation" onsubmit="submitDelegationForm(event)">
                        <div class="form-group mb-3">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                <label class="form-label mb-0" style="font-weight: 700; font-size: 12.5px; color: #334155;">
                                    Vekil Tayin Edilecek Personel <span class="text-danger">*</span>
                                </label>
                                <span id="delegationUserCountBadge" style="font-size: 11px; font-weight: 600; color: #64748b; background: #f1f5f9; padding: 2px 8px; border-radius: 10px;">0 Personel</span>
                            </div>

                            <div class="searchable-delegation-container" id="delegationUserSearchContainer" style="position: relative;">
                                <!-- Input Wrapper / Search Bar -->
                                <div id="delegationInputWrap" onclick="openDelegationUserDropdown(event)" style="min-height: 42px; display: flex; align-items: center; justify-content: space-between; padding: 5px 12px; cursor: pointer; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; transition: all 0.2s;">
                                    <div id="delegationSelectedBox" style="display: flex; align-items: center; flex: 1; min-width: 0; gap: 8px;">
                                        <span style="color: #64748b; font-size: 14px;">🔍</span>
                                        <input type="text" id="delegationSearchInput" class="sms-search-input" placeholder="1500+ personel içinde ara (İsim, Departman, Unvan)..." oninput="filterDelegationUsers(this.value)" onfocus="openDelegationUserDropdown(event)" autocomplete="off" style="width: 100%; border: none; outline: none; font-size: 13px; background: transparent; color: #0f172a;">
                                    </div>
                                    <div id="delegationClearBtn" style="display: none; cursor: pointer; color: #94a3b8; font-weight: 700; margin-left: 8px; padding: 2px 6px; font-size: 14px; border-radius: 4px; transition: color 0.15s;" onclick="clearDelegationUserSelection(event)" title="Seçimi Temizle">✕</div>
                                </div>

                                <!-- Floating Dropdown Menu -->
                                <div id="delegationUserDropdownMenu" style="display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0; max-height: 250px; overflow-y: auto; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; box-shadow: 0 12px 28px -4px rgba(15,23,42,0.18); z-index: 1050;">
                                    <div id="delegationUsersListItems" style="padding: 4px 0;">
                                        <!-- JavaScript ile doldurulacak -->
                                    </div>
                                    <div id="delegationUsersNoResult" style="display: none; padding: 18px; text-align: center; color: #94a3b8; font-size: 12.5px;">
                                        🔍 Aradığınız kriterde personel bulunamadı.
                                    </div>
                                </div>

                                <!-- Hidden input for form submission -->
                                <input type="hidden" name="delegatee_id" id="delegationHiddenDelegateeId">
                            </div>
                        </div>

                        <div class="row" style="display: flex; gap: 12px; margin-bottom: 14px;">
                            <div style="flex: 1;">
                                <label class="form-label" style="font-weight: 700; font-size: 12px; color: #334155;">Başlangıç Tarihi & Saati <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="starts_at" class="form-control" required value="{{ now()->format('Y-m-d\TH:i') }}" style="height: 38px; font-size: 12.5px; border-radius: 7px;">
                            </div>
                            <div style="flex: 1;">
                                <label class="form-label" style="font-weight: 700; font-size: 12px; color: #334155;">Bitiş Tarihi & Saati <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="ends_at" class="form-control" required value="{{ now()->addDays(7)->format('Y-m-d\TH:i') }}" style="height: 38px; font-size: 12.5px; border-radius: 7px;">
                            </div>
                        </div>

                        <!-- PROJE BAZLI YETKİ KAPSAMI SEÇİCİ -->
                        <div class="form-group mb-3" style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 14px 16px;">
                            <label class="form-label mb-1" style="font-weight: 800; font-size: 13px; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                                <span>📁</span> Proje Yetki Kapsamı <span class="text-danger">*</span>
                            </label>
                            <div style="font-size: 11px; color: #64748b; margin-bottom: 10px;">
                                Vekilin hangi projelerde yetkili olacağını belirleyin.
                            </div>

                            <div style="display: flex; gap: 20px; align-items: center; margin-bottom: 10px; flex-wrap: wrap;">
                                <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 700; color: #1e293b; cursor: pointer;">
                                    <input type="radio" name="project_scope_type" value="all" checked onchange="toggleDelegationProjectSelection(false)" style="accent-color: #0284c7; cursor: pointer;">
                                    <span>🌟 Tüm Projelerim (Genel Vekalet)</span>
                                </label>
                                <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 700; color: #1e293b; cursor: pointer;">
                                    <input type="radio" name="project_scope_type" value="selected" onchange="toggleDelegationProjectSelection(true)" style="accent-color: #0284c7; cursor: pointer;">
                                    <span>🎯 Seçili Projeler (Proje Bazlı Vekalet)</span>
                                </label>
                            </div>

                            <!-- Proje Seçim Havuzu (Sadece "selected" seçildiğinde görünür) -->
                            <div id="delegationProjectSelectArea" style="display: none; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px; max-height: 180px; overflow-y: auto;">
                                <div style="font-size: 11px; color: #64748b; margin-bottom: 6px; display: flex; justify-content: space-between; align-items: center;">
                                    <span>Vekil atamak istediğiniz projeleri işaretleyin:</span>
                                    <span id="delegationSelectedProjectCount" style="font-weight: 700; color: #0284c7;">0 Proje Seçildi</span>
                                </div>
                                <div id="delegationProjectsCheckboxList" style="display: flex; flex-direction: column; gap: 4px;">
                                    <!-- JS ile dinamik doldurulacak -->
                                </div>
                            </div>
                        </div>

                        <!-- DİNAMİK VEKALET YETKİ KAPSAMI SEÇİCİ -->
                        <div class="form-group mb-3" style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 14px 16px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <div>
                                    <label class="form-label mb-0" style="font-weight: 800; font-size: 13px; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                                        <span>🎯</span> Vekalet Yetki Kapsamı & Devredilecek Yetkiler <span class="text-danger">*</span>
                                    </label>
                                    <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                        Bizzat sahip olduğunuz yetkilerden devretmek istediklerinizi seçin (Granüler / Kısmi Yetki)
                                    </div>
                                </div>
                                <span id="delegationSelectedPermCountBadge" style="font-size: 11px; font-weight: 700; color: #0369a1; background: #e0f2fe; padding: 3px 9px; border-radius: 12px; border: 1px solid #bae6fd;">
                                    0 Yetki Seçildi
                                </span>
                            </div>

                            <!-- Hızlı Ön Ayar / Filtre Butonları -->
                            <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0;">
                                <button type="button" onclick="setDelegationPermPreset('all')" class="btn btn-xs" style="background: #0284c7; color: #ffffff; font-weight: 700; font-size: 11px; padding: 4px 10px; border-radius: 6px; border: none; cursor: pointer; transition: all 0.15s;">
                                    🌟 Tüm Yetkilerimi Seç
                                </button>
                                <button type="button" onclick="setDelegationPermPreset('approvals')" class="btn btn-xs" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-weight: 700; font-size: 11px; padding: 4px 10px; border-radius: 6px; cursor: pointer; transition: all 0.15s;">
                                    📑 Yalnızca Onay Yetkileri
                                </button>
                                <button type="button" onclick="setDelegationPermPreset('tasks')" class="btn btn-xs" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-weight: 700; font-size: 11px; padding: 4px 10px; border-radius: 6px; cursor: pointer; transition: all 0.15s;">
                                    📋 Yalnızca Görev & Planlama
                                </button>
                                <button type="button" onclick="setDelegationPermPreset('clear')" class="btn btn-xs" style="background: #ffffff; color: #64748b; border: 1px solid #cbd5e1; font-weight: 700; font-size: 11px; padding: 4px 10px; border-radius: 6px; cursor: pointer; transition: all 0.15s;">
                                    ✕ Temizle
                                </button>
                            </div>

                            <!-- Dinamik Yetki Grupları Listesi -->
                            <div id="delegationDynamicPermsList" style="max-height: 240px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; padding-right: 4px;">
                                <div style="padding: 16px; text-align: center; color: #94a3b8; font-size: 12px;">
                                    ⏳ Sahip olduğunuz yetkiler yükleniyor...
                                </div>
                            </div>
                        </div>

                        <div class="row" style="display: flex; gap: 12px; margin-bottom: 14px;">
                            <div style="flex: 1;">
                                <label class="form-label" style="font-weight: 700; font-size: 12px; color: #334155;">Vekalet Sebebi / Kategori <span class="text-danger">*</span></label>
                                <select name="reason_category" id="delegationReasonCategory" class="form-control" required style="height: 38px; font-size: 12.5px; border-radius: 7px;">
                                    <option value="Yıllık İzin">🏖️ Yıllık İzin</option>
                                    <option value="Yurt Dışı Saha Görevi" selected>✈️ Yurt Dışı Saha Görevi</option>
                                    <option value="Saha / Şantiye Ziyareti">🏗️ Saha / Şantiye Ziyareti</option>
                                    <option value="Sağlık / İstirahat">🏥 Sağlık / İstirahat</option>
                                    <option value="Şirket İçi Eğitim / Seminer">🎓 Şirket İçi Eğitim / Seminer</option>
                                    <option value="Mazeret İzni">📝 Mazeret İzni</option>
                                    <option value="Diğer / Özel Görev">📌 Diğer / Özel Görev</option>
                                </select>
                            </div>
                            <div style="flex: 1.4;">
                                <label class="form-label" style="font-weight: 700; font-size: 12px; color: #334155;">Kişisel Mesaj / Ek Açıklama Notu</label>
                                <input type="text" name="notes" class="form-control" placeholder="Örn: Fabrika kabul testi için İtalya'dayım, acil durumlarda WhatsApp..." style="height: 38px; font-size: 12.5px; border-radius: 7px;">
                            </div>
                        </div>

                        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 10px 14px; font-size: 11.5px; color: #166534; margin-bottom: 16px;">
                            💡 Vekiliniz adınıza işlem yaptığında arayüzde <strong>adınıza işlem yapıldığı açıkça gösterilecek</strong> ve denetim loglarına çift yönlü işlenecektir.
                        </div>

                        <div style="display: flex; justify-content: flex-end; gap: 10px;">
                            <button type="button" class="btn btn-secondary" onclick="closeGlobalDelegationModal()" style="font-size: 13px;">Kapat</button>
                            <button type="submit" class="btn btn-primary fw-bold" style="font-size: 13px; padding: 8px 20px; border-radius: 8px;">
                                ✓ Vekaleti Tanımla ve Bildir
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Panel 2: Liste -->
                <div id="panelListDelegations" style="display: none;">
                    <div id="delegationsListArea">
                        <!-- AJAX ile yüklenecek -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- GLOBAL KURUMSAL VEKALET KAPSAMI & YETKİ DETAYLARI MODALI -->
    <div id="modalDelegationScopeDetail" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.65); align-items: center; justify-content: center; z-index: 10001; backdrop-filter: blur(4px);" onclick="if(event.target === this) closeDelegationScopeModal()">
        <div style="background: #ffffff; width: 780px; max-width: 95vw; max-height: 90vh; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35); overflow: hidden; display: flex; flex-direction: column;">
            <!-- Modal Header -->
            <div style="background: linear-gradient(135deg, #064e3b 0%, #065f46 100%); color: #ffffff; padding: 18px 22px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">🛡️</span>
                    <div>
                        <h3 style="font-size: 16px; font-weight: 800; margin: 0; letter-spacing: -0.01em;">Kurumsal Vekalet Kapsamı & Temsil Yetki Detayları</h3>
                        <div style="font-size: 12px; color: #a7f3d0; margin-top: 2px;">Proje içi vekil yetki sınırları, devredilen izinler ve yasal geçerlilik şartnamesi</div>
                    </div>
                </div>
                <button type="button" onclick="closeDelegationScopeModal()" style="background: rgba(255,255,255,0.15); border: none; color: #ffffff; font-size: 16px; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">✕</button>
            </div>

            <!-- Modal Body (Kaydırılabilir) -->
            <div id="delegationScopeModalBody" style="padding: 22px; overflow-y: auto; flex: 1;">
                <div style="padding: 40px; text-align: center; color: #64748b;">
                    <div style="font-size: 28px; margin-bottom: 8px;">⏳</div>
                    <div>Yetki kapsamı ve vekalet şartnamesi yükleniyor...</div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div style="padding: 12px 22px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                <div style="font-size: 11.5px; color: #64748b; display: flex; align-items: center; gap: 5px;">
                    <span>🔒</span> <span>Denetim Kodu: Rule 16 (Çift Yönlü Vekalet Loglaması Devrededir)</span>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeDelegationScopeModal()" style="padding: 6px 18px; font-weight: 600; font-size: 12.5px; border-radius: 8px;">Kapat</button>
            </div>
        </div>
    </div>

    <!-- GLOBAL VEKALET BİLDİRİM / GERİ BİLDİRİM MODALI (Alert Yerine Modern UI) -->
    <div id="modalDelegationFeedback" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.65); align-items: center; justify-content: center; z-index: 10005; backdrop-filter: blur(4px);" onclick="if(event.target === this) closeDelegationFeedbackModal()">
        <div style="background: #ffffff; width: 440px; max-width: 92vw; border-radius: 14px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35); overflow: hidden; display: flex; flex-direction: column;">
            <div id="delegationFeedbackHeader" style="background: #f0fdf4; border-bottom: 1px solid #bbf7d0; padding: 16px 20px; display: flex; align-items: center; gap: 10px;">
                <span id="delegationFeedbackIcon" style="font-size: 20px; font-weight: 800; color: #15803d; width: 28px; height: 28px; border-radius: 50%; background: #dcfce7; display: flex; align-items: center; justify-content: center;">✓</span>
                <h4 id="delegationFeedbackTitle" style="margin: 0; font-size: 15px; font-weight: 800; color: #166534;">Bilgilendirme</h4>
            </div>
            <div style="padding: 20px;">
                <div id="delegationFeedbackMessage" style="font-size: 13.5px; color: #334155; line-height: 1.5;"></div>
            </div>
            <div style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end;">
                <button type="button" class="btn btn-primary btn-sm" onclick="closeDelegationFeedbackModal()" style="padding: 7px 20px; font-weight: 700; font-size: 13px; border-radius: 8px;">Tamam</button>
            </div>
        </div>
    </div>

    <!-- GLOBAL VEKALET İPTAL / SONLANDIRMA ONAY MODALI (Prompt Yerine Modern UI) -->
    <div id="modalDelegationCancelConfirm" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.65); align-items: center; justify-content: center; z-index: 10004; backdrop-filter: blur(4px);" onclick="if(event.target === this) closeDelegationCancelModal()">
        <div style="background: #ffffff; width: 480px; max-width: 92vw; border-radius: 14px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35); overflow: hidden; display: flex; flex-direction: column;">
            <div style="background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 100%); color: #ffffff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 20px;">🛑</span>
                    <h4 style="margin: 0; font-size: 15px; font-weight: 800; color: #ffffff;">Vekaleti İptal Et / Sonlandır</h4>
                </div>
                <button type="button" onclick="closeDelegationCancelModal()" style="background: transparent; border: none; color: #ffffff; font-size: 16px; cursor: pointer;">✕</button>
            </div>
            <div style="padding: 20px;">
                <input type="hidden" id="delegationCancelTargetId">
                <p style="font-size: 13px; color: #334155; margin-top: 0; margin-bottom: 12px; line-height: 1.45;">
                    Bu vekalet sözleşmesini sonlandırmak istediğinize emin misiniz? Vekilin yetkileri derhal geri alınacak ve durum her iki tarafa da bildirilecektir.
                </p>
                <div class="form-group mb-0">
                    <label class="form-label" style="font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 5px;">
                        İptal / Erken Sonlandırma Gerekçesi <span class="text-danger">*</span>
                    </label>
                    <textarea id="delegationCancelReasonInput" class="form-control" rows="3" placeholder="Örn: Görev / seyahat tamamlandı, göreve geri döndüm..." style="font-size: 12.5px; border-radius: 8px; resize: vertical;"></textarea>
                </div>
            </div>
            <div style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeDelegationCancelModal()" style="font-size: 12.5px; padding: 6px 14px; border-radius: 7px;">Vazgeç</button>
                <button type="button" id="btnExecuteCancelDelegation" class="btn btn-danger btn-sm fw-bold" onclick="executeCancelDelegation()" style="font-size: 12.5px; padding: 6px 16px; border-radius: 7px; background: #dc2626; border-color: #dc2626;">
                    ✕ Evet, Vekaleti Sonlandır
                </button>
            </div>
        </div>
    </div>

    <script>
    window.openDelegationScopeModal = function(id) {
        const modal = document.getElementById('modalDelegationScopeDetail');
        const body = document.getElementById('delegationScopeModalBody');
        if (!modal || !body) return;

        modal.style.display = 'flex';
        body.innerHTML = `
            <div style="padding: 50px; text-align: center; color: #64748b;">
                <div style="font-size: 32px; margin-bottom: 8px;">⏳</div>
                <div style="font-weight: 600;">Yetki kapsamı ve vekalet şartnamesi yükleniyor...</div>
            </div>
        `;

        fetch(`/delegations/${id}`)
            .then(res => res.json())
            .then(data => {
                if (!data.success || !data.delegation) {
                    body.innerHTML = `<div style="padding: 30px; text-align: center; color: #dc2626;">Vekalet bilgisi alınamadı!</div>`;
                    return;
                }

                const d = data.delegation;
                const matrix = d.matrix;
                const badge = d.scope_badge;

                let projectScopeHtml = '<span style="background: #fefce8; color: #854d0e; border: 1px solid #fef08a; padding: 2px 8px; border-radius: 5px; font-size: 11px; font-weight: 700;">🌟 Tüm Projeler (Genel Vekalet)</span>';
                if (d.project_scope_type === 'selected') {
                    const pCount = (d.project_ids || []).length;
                    const pNames = Object.values(d.project_names || {}).join(', ');
                    projectScopeHtml = `<span style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; padding: 2px 8px; border-radius: 5px; font-size: 11px; font-weight: 700;" title="${pNames}">📁 ${pCount} Seçili Proje: ${pNames || 'Belirtilmedi'}</span>`;
                }

                let actionsTableRows = '';
                if (d.actions && d.actions.length > 0) {
                    actionsTableRows = d.actions.map(a => `
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 5px 8px; color: #64748b; white-space: nowrap;">${a.created_at}</td>
                            <td style="padding: 5px 8px; font-weight: 700; color: #0f172a; white-space: nowrap;">${a.project_title}</td>
                            <td style="padding: 5px 8px;"><span class="badge" style="background: #e2e8f0; color: #334155; font-size: 10px;">${a.action}</span></td>
                            <td style="padding: 5px 8px; color: #334155;">${a.description}</td>
                        </tr>
                    `).join('');
                }

                let html = `
                    <!-- 1. Üst Özet Kartı: Taraflar & Sebepler -->
                    <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-bottom: 18px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 14px;">
                            <!-- Sol: Asıl Yetkili -->
                            <div style="flex: 1; min-width: 200px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 12px 14px;">
                                <div style="font-size: 10.5px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Vekalet Veren (Asıl Yetkili)</div>
                                <div style="font-size: 14px; font-weight: 800; color: #0f172a; margin-top: 3px;">${d.delegator_name}</div>
                                <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">${d.delegator_department} • ${d.delegator_role}</div>
                            </div>

                            <!-- Orta: Transfer Oku -->
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 2px;">
                                <span style="font-size: 20px; color: #059669;">➔</span>
                                <span style="font-size: 10px; font-weight: 800; color: #059669; background: #dcfce7; padding: 1px 6px; border-radius: 6px;">Vekalet Devri</span>
                            </div>

                            <!-- Sağ: Vekil -->
                            <div style="flex: 1; min-width: 200px; background: #ffffff; border: 1.5px solid #86efac; border-radius: 10px; padding: 12px 14px;">
                                <div style="font-size: 10.5px; font-weight: 800; color: #059669; text-transform: uppercase; letter-spacing: 0.5px;">Vekil Tayin Edilen (Temsilci)</div>
                                <div style="font-size: 14px; font-weight: 800; color: #065f46; margin-top: 3px;">${d.delegatee_name}</div>
                                <div style="font-size: 11.5px; color: #059669; margin-top: 2px;">${d.delegatee_department || 'Kurumsal Personel'} • ${d.delegatee_role || 'Vekil'}</div>
                            </div>
                        </div>

                        <!-- Alt Satır: Tarih, Süre, Proje Kapsamı & Kişisel Not -->
                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; display: flex; flex-direction: column; gap: 8px; font-size: 12px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <span style="color: #64748b;">📅 Geçerlilik Süresi:</span>
                                    <strong style="color: #0f172a;">${d.starts_at}</strong> - <strong style="color: #0f172a;">${d.ends_at}</strong>
                                    <span style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-size: 11px; font-weight: 700; padding: 1px 7px; border-radius: 4px;">
                                        ⏳ ${d.duration_text || ''}
                                    </span>
                                </div>
                                <span class="badge" style="background: ${d.is_currently_valid ? '#dcfce7' : '#fee2e2'}; color: ${d.is_currently_valid ? '#15803d' : '#991b1b'}; border: 1px solid ${d.is_currently_valid ? '#86efac' : '#fca5a5'}; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 6px;">
                                    ${d.is_currently_valid ? '● Canlı / Devrede' : '⚪ Süresi Doldu / Pasif'}
                                </span>
                            </div>

                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; padding-top: 6px; border-top: 1px solid #f1f5f9;">
                                ${projectScopeHtml}
                                <span style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 2px 8px; border-radius: 5px; font-size: 11px; font-weight: 700;">
                                    🏷️ Vekalet Nedeni: ${d.reason_category}
                                </span>
                                ${d.has_notes ? `
                                    <span style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; padding: 2px 8px; border-radius: 5px; font-size: 11px; font-style: italic;">
                                        💬 Kişisel Not: "${d.notes}"
                                    </span>
                                ` : ''}
                            </div>

                            ${!d.is_active && d.cancel_reason ? `
                                <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 6px 10px; font-size: 11.5px; color: #991b1b;">
                                    🛑 <strong>Erken Sonlandırma Bilgisi:</strong> ${d.cancel_reason} ${d.cancelled_at ? `(${d.cancelled_at})` : ''}
                                </div>
                            ` : ''}
                        </div>
                    </div>

                    <!-- 2. Yetki Kapsamı Dağılımı & Başlık -->
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; gap: 10px; flex-wrap: wrap;">
                        <div>
                            <h4 style="font-size: 13.5px; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 6px;">
                                <span>🎯</span> Devredilen Yetki Listesi & Kapsam Sınırları
                            </h4>
                            <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                Vekil kullanıcının bu vekalet kapsamında yapabileceği ve yapamayacağı işlemler aşağıda belirtilmiştir.
                            </div>
                        </div>
                        <span style="font-size: 11.5px; font-weight: 800; background: ${badge.bg}; color: ${badge.color}; border: 1px solid ${badge.border}; padding: 3px 10px; border-radius: 12px;">
                            ${badge.count_text}
                        </span>
                    </div>

                    <!-- Yetki Grupları Grid -->
                    <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 18px;">
                `;

                if (matrix && matrix.groups && matrix.groups.length > 0) {
                    matrix.groups.forEach(g => {
                        const grantedCount = g.items.filter(i => i.is_granted).length;
                        const totalCount = g.items.length;
                        const isAllInGroup = grantedCount === totalCount;
                        const isNoneInGroup = grantedCount === 0;

                        html += `
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
                                <div style="background: #f8fafc; padding: 8px 14px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                                    <strong style="font-size: 12.5px; color: #1e293b;">${g.group}</strong>
                                    <span style="font-size: 11px; font-weight: 700; ${isNoneInGroup ? 'color: #94a3b8;' : (isAllInGroup ? 'color: #16a34a;' : 'color: #0284c7;')}">
                                        ${grantedCount} / ${totalCount} Yetki Verildi
                                    </span>
                                </div>
                                <div style="display: flex; flex-direction: column;">
                        `;

                        g.items.forEach(item => {
                            const isGranted = item.is_granted;
                            html += `
                                <div style="padding: 8px 14px; display: flex; align-items: center; justify-content: space-between; gap: 12px; background: ${isGranted ? '#f0fdf4' : '#fafafa'}; border-bottom: 1px solid #f1f5f9;">
                                    <div style="display: flex; align-items: flex-start; gap: 9px; flex: 1;">
                                        <span style="font-size: 13px; font-weight: 800; line-height: 1.2; ${isGranted ? 'color: #16a34a;' : 'color: #94a3b8;'}">
                                            ${isGranted ? '✓' : '✕'}
                                        </span>
                                        <div>
                                            <div style="font-size: 12px; font-weight: 700; ${isGranted ? 'color: #0f172a;' : 'color: #64748b; text-decoration: line-through;'}">${item.name}</div>
                                            <div style="font-size: 11px; color: #64748b; margin-top: 1px;">${item.desc}</div>
                                        </div>
                                    </div>
                                    <div>
                                        ${isGranted ? `
                                            <span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-size: 10.5px; font-weight: 700; padding: 2px 7px; border-radius: 5px; white-space: nowrap;">
                                                ✓ Yetki Devredildi
                                            </span>
                                        ` : `
                                            <span class="badge" style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; font-size: 10.5px; font-weight: 600; padding: 2px 7px; border-radius: 5px; white-space: nowrap;">
                                                ✕ Kapsam Dışı (Yetkisiz)
                                            </span>
                                        `}
                                    </div>
                                </div>
                            `;
                        });

                        html += `</div></div>`;
                    });
                } else {
                    html += `<div style="padding: 20px; text-align: center; color: #94a3b8;">Yetki detayları bulunamadı.</div>`;
                }

                html += `
                    </div>

                    <!-- 3. VARSAYILAN KAPALI AKORDEON: BU VEKALET KAPSAMINDA YAPILAN İŞLEMLER (DENETİM İZİ) -->
                    <div style="margin-bottom: 18px; background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
                        <div style="background: #f8fafc; padding: 10px 14px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 15px;">⚡</span>
                                <strong style="font-size: 12.5px; color: #1e293b;">Vekalet Kapsamında Yapılan İşlemler (Denetim İzi)</strong>
                                <span style="font-size: 11px; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 1px 7px; border-radius: 10px;">
                                    ${d.actions_count || 0} Kayıt
                                </span>
                            </div>
                            <button type="button" onclick="toggleScopeModalActionsAccordion()" id="btnToggleScopeActions" style="background: #ffffff; border: 1px solid #cbd5e1; font-size: 11px; font-weight: 700; color: #334155; padding: 3px 9px; border-radius: 6px; cursor: pointer;">
                                <span id="lblScopeActionsToggle">İşlemleri İncele ▼</span>
                            </button>
                        </div>
                        <div id="panelScopeActionsTable" style="display: none; padding: 12px 14px; background: #ffffff;">
                            ${d.actions && d.actions.length > 0 ? `
                                <div style="max-height: 200px; overflow-y: auto;">
                                    <table style="width: 100%; font-size: 11.5px; border-collapse: collapse;">
                                        <thead>
                                            <tr style="border-bottom: 1px solid #cbd5e1; color: #475569; text-align: left; font-size: 10.5px;">
                                                <th style="padding: 4px 6px;">Tarih</th>
                                                <th style="padding: 4px 6px;">Proje</th>
                                                <th style="padding: 4px 6px;">İşlem</th>
                                                <th style="padding: 4px 6px;">Açıklama</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${actionsTableRows}
                                        </tbody>
                                    </table>
                                </div>
                            ` : `
                                <div style="font-size: 11.5px; color: #64748b; text-align: center; padding: 10px;">
                                    Bu vekalet süresince vekil tarafından sistemde henüz bir işlem (onay, görev tamamlama vb.) kaydı bulunmamaktadır.
                                </div>
                            `}
                        </div>
                    </div>

                    <!-- 4. Ekip ve Organizasyon İçin Aydınlatma Notu -->
                    <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 9px; padding: 12px 14px; font-size: 11.5px; color: #92400e; line-height: 1.45;">
                        💡 <strong>Proje Ekibi İçin Bilgilendirme:</strong> Vekil kullanıcı yalnızca yukarıda yeşil ile <strong>"✓ Yetki Devredildi"</strong> olarak belirtilen alanlarda asıl yetkili adına onay ve işlem yapmaya salahiyetlidir. Kapsam dışı bırakılan işlemler vekil tarafından icra edilemez ve sistem tarafından engellenir. Vekil tarafından alınan tüm onaylar ve yapılan işlemler <strong>Rule 16</strong> gereğince denetim kayıtlarına (Audit Trail) vekaleten çift yönlü olarak işlenmektedir.
                    </div>
                `;

                body.innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                body.innerHTML = `<div style="padding: 30px; text-align: center; color: #dc2626;">Veri yüklenirken iletişim hatası oluştu!</div>`;
            });
    };

    window.toggleScopeModalActionsAccordion = function() {
        const panel = document.getElementById('panelScopeActionsTable');
        const lbl = document.getElementById('lblScopeActionsToggle');
        if (!panel) return;
        if (panel.style.display === 'none' || !panel.style.display) {
            panel.style.display = 'block';
            if (lbl) lbl.textContent = 'İşlemleri Gizle ▲';
        } else {
            panel.style.display = 'none';
            if (lbl) lbl.textContent = 'İşlemleri İncele ▼';
        }
    };

    // =========================================================================
    // GLOBAL UNIVERSAL MODAL SYSTEM (CUSTOM-MODAL & BOOTSTRAP EMULATION)
    // =========================================================================
    window.openCustomModal = function(modalId) {
        if (!modalId) return;
        modalId = modalId.replace(/^#/, '');
        const modal = document.getElementById(modalId);
        if (!modal) {
            console.warn('Modal bulunamadı: #' + modalId);
            return;
        }
        modal.classList.add('show');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };

    window.closeCustomModal = function(modalId) {
        if (!modalId) return;
        modalId = modalId.replace(/^#/, '');
        const modal = document.getElementById(modalId);
        if (!modal) return;
        modal.classList.remove('show');
        modal.style.display = 'none';
        document.body.style.overflow = '';
    };

    // Universal Delegated Event Listener for [data-bs-toggle="modal"] and [data-bs-dismiss="modal"]
    document.addEventListener('click', function(e) {
        const trigger = e.target.closest('[data-bs-toggle="modal"], [data-toggle="modal"]');
        if (trigger) {
            e.preventDefault();
            e.stopPropagation();
            const targetSelector = trigger.getAttribute('data-bs-target') || trigger.getAttribute('data-target') || trigger.getAttribute('href');
            if (targetSelector) {
                window.openCustomModal(targetSelector);
            }
            return;
        }

        const dismissBtn = e.target.closest('[data-bs-dismiss="modal"], [data-dismiss="modal"], .custom-modal-close, .btn-close');
        if (dismissBtn) {
            e.preventDefault();
            const modal = dismissBtn.closest('.custom-modal-backdrop, .modal');
            if (modal) {
                window.closeCustomModal(modal.id);
            }
            return;
        }
    });

    // Close on Escape Key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.custom-modal-backdrop.show, .modal.show, [id^="scopeToolsModal_"][style*="display: flex"], [id^="projectTeamModal_"][style*="display: flex"]').forEach(m => {
                window.closeCustomModal(m.id);
            });
        }
    });

    window.closeDelegationScopeModal = function() {
        const modal = document.getElementById('modalDelegationScopeDetail');
        if (modal) modal.style.display = 'none';
    };
    </script>
</body>
</html>