@extends('layouts.app')

@section('title', 'Sistem Sağlığı & Canlı Rota Tanı Merkezi')

@section('content')
<style>
/* ==========================================================================
   SİSTEM SAĞLIĞI & TEST ORTAMI ÖZEL STİLLERİ
   ========================================================================== */

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


/* Sayfa Başlığı ve Buton Ayrımı */
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
    max-width: 840px;
    font-weight: 450;
}
.matrix-page-header .header-right {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
    padding-top: 4px;
    flex-wrap: wrap;
}

/* Header Butonları */
.matrix-header-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    height: 42px;
    padding: 0 18px;
    font-size: 13px;
    font-weight: 650;
    color: #334155;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
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
    gap: 8px;
    height: 42px;
    padding: 0 20px;
    font-size: 13.5px;
    font-weight: 700;
    color: #ffffff;
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
    border: 1px solid #0284c7;
    border-radius: 10px;
    text-decoration: none;
    cursor: pointer;
    box-shadow: 0 3px 8px rgba(2, 132, 199, 0.28);
    transition: all 0.2s ease;
    white-space: nowrap;
}
.matrix-header-btn-primary:hover {
    background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
    box-shadow: 0 5px 12px rgba(2, 132, 199, 0.38);
    transform: translateY(-1px);
}
.matrix-header-btn-primary:active {
    transform: translateY(0);
}
.matrix-header-btn-primary:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Dönen İkon Animasyonu */
@keyframes spinSlow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.btn-spinning .spin-icon {
    display: inline-block;
    animation: spinSlow 0.9s linear infinite;
}

/* Canlı Test Sonuç Bildirim Panosu (Result Banner) */
.health-result-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 24px;
    border-left: 5px solid;
    background: #ffffff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}
.health-result-banner.status-healthy {
    background: #f0fdf4;
    border-color: #10b981;
    color: #065f46;
}
.health-result-banner.status-warning {
    background: #fffbeb;
    border-color: #f59e0b;
    color: #92400e;
}
.health-result-banner.status-critical {
    background: #fef2f2;
    border-color: #ef4444;
    color: #991b1b;
}

/* Tanı KPI Kartları */
.diag-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
}
.diag-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 18px 20px;
    display: flex;
    align-items: flex-start;
    gap: 16px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.03);
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
}
.diag-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    transform: translateY(-2px);
}
.diag-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: #cbd5e1;
}
.diag-card.card-success::before { background: #10b981; }
.diag-card.card-warning::before { background: #f59e0b; }
.diag-card.card-danger::before { background: #ef4444; }
.diag-card.card-info::before { background: #0284c7; }
.diag-card.card-purple::before { background: #8b5cf6; }

.diag-icon-box {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
}
.diag-content {
    flex: 1;
    min-width: 0;
}
.diag-label {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #64748b;
    margin-bottom: 4px;
}
.diag-value {
    font-size: 20px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.2;
    margin-bottom: 4px;
    letter-spacing: -0.02em;
}
.diag-subtext {
    font-size: 11.5px;
    color: #64748b;
    line-height: 1.4;
}

/* Rol Güvenlik Denetim Kartları */
.role-cards-section {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 22px 24px;
    margin-bottom: 28px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.02);
}
.role-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
    margin-top: 18px;
}
.role-diag-card {
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
    transition: all 0.2s ease;
    cursor: pointer;
    position: relative;
}
.role-diag-card:hover {
    background: #ffffff;
    border-color: #0284c7;
    box-shadow: 0 4px 12px rgba(2, 132, 199, 0.1);
    transform: translateY(-2px);
}
.role-diag-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
}
.role-name-box {
    display: flex;
    align-items: center;
    gap: 8px;
}
.role-group-pill {
    font-size: 10.5px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 12px;
    color: #ffffff;
    display: inline-block;
}
.role-coverage-bar {
    width: 100%;
    height: 6px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
    margin: 10px 0 6px 0;
}
.role-coverage-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.4s ease;
}

/* Canlı Rota & Middleware Tablo Kartı */
.routes-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    overflow: hidden;
    margin-bottom: 32px;
}
.routes-toolbar {
    padding: 18px 24px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px;
    background: #fbfcfe;
}
.routes-filter-chips {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.route-chip-btn {
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 650;
    border-radius: 20px;
    border: 1.5px solid #cbd5e1;
    background: #ffffff;
    color: #475569;
    cursor: pointer;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.route-chip-btn:hover {
    border-color: #0284c7;
    color: #0284c7;
    background: #f0f9ff;
}
.route-chip-btn.active {
    background: #0284c7;
    border-color: #0284c7;
    color: #ffffff;
    box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);
}

.routes-search-input {
    height: 38px;
    padding: 0 14px 0 34px;
    border: 1.5px solid #cbd5e1;
    border-radius: 8px;
    font-size: 13px;
    color: #1e293b;
    background: #ffffff;
    width: 260px;
    transition: all 0.2s ease;
}
.routes-search-input:focus {
    outline: none;
    border-color: #0284c7;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
}

/* Rota Tablosu */
.routes-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.routes-table th {
    background: #f8fafc;
    color: #475569;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 0.05em;
    padding: 12px 18px;
    border-bottom: 2px solid #e2e8f0;
    text-align: left;
    white-space: nowrap;
}
.routes-table td {
    padding: 13px 18px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.routes-table tr:hover {
    background: #f8fafc;
}
.routes-table tr.has-error-row {
    background: #fffdfa;
}
.routes-table tr.has-error-row:hover {
    background: #fef2f2;
}

/* Hata Ayıklama & Tanı Detayı Modal */
.custom-modal-backdrop {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(4px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.custom-modal-backdrop.show {
    display: flex;
}
.custom-modal-dialog {
    background: #ffffff;
    border-radius: 16px;
    width: 100%;
    max-width: 780px;
    max-height: 90vh;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    animation: modalSlideIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes modalSlideIn {
    from { opacity: 0; transform: translateY(16px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.custom-modal-header {
    padding: 18px 24px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f8fafc;
}
.custom-modal-body {
    padding: 24px;
    overflow-y: auto;
    max-height: calc(90vh - 140px);
}
.custom-modal-footer {
    padding: 16px 24px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
    background: #f8fafc;
}

/* Kod ve Hata Çıktı Kutusu */
.debug-code-box {
    background: #0f172a;
    color: #e2e8f0;
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
    font-size: 12px;
    line-height: 1.6;
    padding: 14px 18px;
    border-radius: 10px;
    overflow-x: auto;
    border: 1px solid #1e293b;
    white-space: pre-wrap;
    word-break: break-all;
}

/* Toast Bildirimi */
#toastNotification {
    position: fixed;
    bottom: 24px;
    right: 24px;
    padding: 14px 22px;
    border-radius: 10px;
    background: #0f172a;
    color: #ffffff;
    font-size: 13.5px;
    font-weight: 600;
    display: none;
    align-items: center;
    gap: 10px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.25);
    z-index: 10000;
    animation: toastSlideUp 0.25s ease;
}
@keyframes toastSlideUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<!-- MODERN SAYFA BAŞLIĞI VE AYRILMIŞ BUTONLAR -->
<div class="matrix-page-header">
    <div class="header-left">
        <h1 class="page-title">
            <span>🩺</span>
            <span>Sistem Sağlığı & Canlı Tanı Merkezi</span>
        </h1>
        <p class="page-subtitle">
            IAA referanslı sistem sağlığı: Veritabanı latency pingleme, Storage I/O bütünlüğü, Cache motoru, Kuyruk izleme, {{ $health['total_routes'] }} web rotasının bütünlük denetimi ve rol erişim matrisi.
        </p>
    </div>
    <div class="header-right">
        <button type="button" class="matrix-header-btn-secondary" onclick="copyFullHealthReport()" title="Tüm Sistem Tanı Raporunu Panoya Kopyala">
            <span>📋</span>
            <span>Tanı Raporunu Kopyala</span>
        </button>
        <button type="button" class="matrix-header-btn-primary" id="btnRetest" onclick="runLiveRetest()" title="Tüm Sistem Bileşenlerini Canlı Olarak Baştan Test Et">
            <span class="spin-icon">🔄</span>
            <span id="btnRetestText">Yeniden Test Et</span>
        </button>
    </div>
</div>

<!-- MATRİSLER & ROLLER ARASI HIZLI GEÇİŞ SEKMELERİ (8 SEKME SENKRONİZE) -->
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
    <a href="{{ route('admin.audit_logs') }}#tab-visibility" class="matrix-tab-link">
        <span class="tab-emoji">📋</span>
        <span>Log Görünürlük Matrisi</span>
    </a>
    <a href="{{ route('admin.system_health') }}" class="matrix-tab-link active">
        <span class="tab-emoji">🩺</span>
        <span>Sistem Sağlığı</span>
    </a>
</div>

<!-- CANLI TEST SONUÇ PANOSU (ALERT BANNER) -->
<div id="healthResultBanner" class="health-result-banner {{ $health['overall_status'] === 'HEALTHY' ? 'status-healthy' : ($health['overall_status'] === 'WARNING' ? 'status-warning' : 'status-critical') }}">
    <div class="d-flex align-center gap-3">
        <span style="font-size: 26px;" id="bannerStatusEmoji">
            {{ $health['overall_status'] === 'HEALTHY' ? '✅' : ($health['overall_status'] === 'WARNING' ? '⚠️' : '❌') }}
        </span>
        <div>
            <div style="font-size: 14.5px; font-weight: 750;" id="bannerStatusTitle">
                @if($health['overall_status'] === 'HEALTHY')
                    Tüm Sistem Altyapısı Operasyonel ve Sağlıklı (0 Kritik Hata)
                @elseif($health['overall_status'] === 'WARNING')
                    Temel Altyapı Aktif; Ancak {{ $health['broken_routes'] }} Rotada Geliştirici İncelemesi Gerekli
                @else
                    Kritik Hata Tespit Edildi: Altyapı bileşenleri yanıt vermiyor!
                @endif
            </div>
            <div style="font-size: 12.5px; margin-top: 3px; opacity: 0.9;" id="bannerStatusSubtext">
                Veritabanı: <strong>{{ $health['db_latency_ms'] }} ms</strong> | Depolama: <strong>{{ $health['storage_status'] }}</strong> | Cache: <strong>{{ $health['cache_status'] }}</strong> | Aktif Rotalar: <strong>{{ $health['healthy_routes'] }} / {{ $health['total_routes'] }} Geçerli</strong> | <span id="bannerTimestamp">Son Test: {{ $health['server_time'] }}</span>
            </div>
        </div>
    </div>
    <div class="d-flex align-center gap-2">
        <button type="button" class="btn btn-sm btn-light" onclick="document.getElementById('healthResultBanner').style.display='none'" style="border: 1px solid rgba(0,0,0,0.1); font-size: 11px; padding: 4px 10px; border-radius: 6px;">
            ✕ Gizle
        </button>
    </div>
</div>

<!-- DEVOPS & SİSTEM TESTİ KPI KARTLARI -->
<div class="diag-kpi-grid">
    <!-- 1. Veritabanı -->
    <div class="diag-card card-success">
        <div class="diag-icon-box" style="background: #ecfdf5; color: #10b981;">
            <span>🗄️</span>
        </div>
        <div class="diag-content">
            <div class="diag-label">Veritabanı ({{ strtoupper($health['db_driver']) }})</div>
            <div class="diag-value text-success" id="kpiDbStatus">
                {{ $health['db_status'] === 'OK' ? 'Çevrimiçi' : 'Hata!' }}
            </div>
            <div class="diag-subtext">
                Gecikme: <strong id="kpiDbLatency">{{ $health['db_latency_ms'] }} ms</strong> | DB: <code>{{ $health['db_database'] }}</code>
            </div>
        </div>
    </div>

    <!-- 2. Storage / Disk -->
    <div class="diag-card card-info">
        <div class="diag-icon-box" style="background: #f0f9ff; color: #0284c7;">
            <span>💾</span>
        </div>
        <div class="diag-content">
            <div class="diag-label">Depolama & Disk I/O</div>
            <div class="diag-value text-primary" id="kpiStorageStatus">
                {{ $health['storage_status'] === 'OK' ? 'Yazılabilir' : 'Hata!' }}
            </div>
            <div class="diag-subtext">
                Disk: <code>{{ $health['storage_disk'] }}</code> | I/O Ping Doğrulandı
            </div>
        </div>
    </div>

    <!-- 3. Cache & Session -->
    <div class="diag-card card-purple">
        <div class="diag-icon-box" style="background: #f5f3ff; color: #8b5cf6;">
            <span>⚡</span>
        </div>
        <div class="diag-content">
            <div class="diag-label">Önbellek ({{ strtoupper($health['cache_driver']) }})</div>
            <div class="diag-value text-dark" id="kpiCacheStatus">
                {{ $health['cache_status'] === 'OK' ? 'Operasyonel' : 'Uyarı' }}
            </div>
            <div class="diag-subtext">
                Probe: <strong id="kpiCacheLatency">{{ $health['cache_latency_ms'] }} ms</strong> | Session Aktif
            </div>
        </div>
    </div>

    <!-- 4. Kuyruk (Queue Worker) -->
    <div class="diag-card card-info">
        <div class="diag-icon-box" style="background: #eff6ff; color: #3b82f6;">
            <span>📨</span>
        </div>
        <div class="diag-content">
            <div class="diag-label">Kuyruk ({{ strtoupper($health['queue_driver']) }})</div>
            <div class="diag-value text-dark" id="kpiQueueStatus">
                {{ $health['queue_failed_jobs'] > 0 ? 'Uyarı!' : 'Stabil' }}
            </div>
            <div class="diag-subtext">
                Bekleyen: <strong id="kpiQueuePending">{{ $health['queue_pending_jobs'] }}</strong> | Başarısız: <strong id="kpiQueueFailed">{{ $health['queue_failed_jobs'] }}</strong>
            </div>
        </div>
    </div>

    <!-- 5. Sunucu & Bellek -->
    <div class="diag-card card-warning">
        <div class="diag-icon-box" style="background: #fffbeb; color: #f59e0b;">
            <span>🧠</span>
        </div>
        <div class="diag-content">
            <div class="diag-label">Sunucu & Runtime</div>
            <div class="diag-value text-dark">PHP {{ $health['php_version'] }}</div>
            <div class="diag-subtext">
                Bellek: <strong id="kpiMemoryUsage">{{ $health['memory_usage_mb'] }} MB</strong> (Limit: {{ $health['memory_limit'] }})
            </div>
        </div>
    </div>

    <!-- 6. Rota & Bütünlük Skoru -->
    <div class="diag-card {{ $health['broken_routes'] > 0 ? 'card-warning' : 'card-success' }}">
        <div class="diag-icon-box" style="background: #fdf2f8; color: #ec4899;">
            <span>🛡️</span>
        </div>
        <div class="diag-content">
            <div class="diag-label">Rota Bütünlük Skoru</div>
            <div class="diag-value {{ $health['broken_routes'] > 0 ? 'text-warning' : 'text-success' }}" id="kpiRouteScore">
                {{ $health['healthy_routes'] }} / {{ $health['total_routes'] }}
            </div>
            <div class="diag-subtext" id="kpiRouteSubtext">
                @if($health['broken_routes'] > 0)
                    <span style="color: #d97706; font-weight: 700;">⚠️ {{ $health['broken_routes'] }} Rota İnceleme Bekliyor</span>
                @else
                    <span style="color: #059669; font-weight: 700;">✓ Tüm Rotalar Doğrulandı</span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- YENİ EKLENEN OPERASYONEL ALTMOTOR SAĞLIĞI & ENTEGRASYON KARTLARI -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px; margin-bottom: 24px;">
    <!-- 1. Bütçe Yetki Devri (DOA) Motoru -->
    <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: #eef2ff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-size: 20px;">⚖️</div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Bütçe Eşik (DOA) Motoru</div>
                <div style="font-size: 14px; font-weight: 800; color: #0f172a;">
                    {{ $health['doa_engine']['is_active'] ? 'Motor Devrede (Aktif)' : 'Pasif' }} <span style="font-size: 11px; color: {{ $health['doa_engine']['is_active'] ? '#10b981' : '#ef4444' }};">●</span>
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                    <strong>{{ $health['doa_engine']['active_rules'] }} / {{ $health['doa_engine']['total_rules'] }}</strong> Kademe Aktif | Baz: <strong>{{ $health['doa_engine']['base_currency'] }}</strong> ({{ $health['doa_engine']['currencies_count'] }} Kur)
                </div>
            </div>
        </div>
        <a href="{{ route('admin.doa_thresholds.index') }}" style="font-size: 11.5px; font-weight: 700; color: #0284c7; text-decoration: none; padding: 5px 12px; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 6px;">İncele →</a>
    </div>

    <!-- 2. Kurul ve Komite Oylama Mekanizması -->
    <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: #ecfdf5; color: #047857; display: flex; align-items: center; justify-content: center; font-size: 20px;">🏛️</div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Kurul & Komite Oylama</div>
                <div style="font-size: 14px; font-weight: 800; color: #0f172a;">
                    {{ $health['committees_subsystem']['total_committees'] }} Tanımlı Heyet / Meclis
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                    Toplam <strong>{{ $health['committees_subsystem']['total_members'] }}</strong> Aktif Üye | Karar Motoru Hazır
                </div>
            </div>
        </div>
        <a href="{{ route('admin.committees.index') }}" style="font-size: 11.5px; font-weight: 700; color: #047857; text-decoration: none; padding: 5px 12px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px;">Yönet →</a>
    </div>

    <!-- 3. Dinamik Taksonomi & Ekip Rolleri -->
    <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: #fff7ed; color: #c2410c; display: flex; align-items: center; justify-content: center; font-size: 20px;">📁</div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Taksonomi & Rol Havuzu</div>
                <div style="font-size: 14px; font-weight: 800; color: #0f172a;">
                    {{ $health['taxonomy_subsystem']['categories_count'] }} Kategori / {{ $health['taxonomy_subsystem']['team_roles_count'] }} Ekip Rolü
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                    <strong>{{ $health['taxonomy_subsystem']['budget_categories_count'] }}</strong> Harcama Kalemi Kırılımı Senkronize
                </div>
            </div>
        </div>
        <a href="{{ route('admin.categories.index') }}" style="font-size: 11.5px; font-weight: 700; color: #ea580c; text-decoration: none; padding: 5px 12px; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 6px;">Yapılandır →</a>
    </div>

    <!-- 4. Proje İçi Ekip Yetki Matrisi -->
    <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: #f0fdf4; color: #15803d; display: flex; align-items: center; justify-content: center; font-size: 20px;">🛡️</div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Proje İçi Ekip Yetki Matrisi</div>
                <div style="font-size: 14px; font-weight: 800; color: #0f172a;" id="kpiPrjPermStatus">
                    {{ $health['delegation_matrix_subsystem']['granted_permissions'] }} Aktif Mikro Yetki
                    <span style="font-size: 11px; color: {{ $health['delegation_matrix_subsystem']['status'] === 'HEALTHY' ? '#10b981' : '#f59e0b' }};">●</span>
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;" id="kpiPrjPermSubtext">
                    Toplam <strong>{{ $health['delegation_matrix_subsystem']['total_permissions'] }}</strong> Kayıt | Yetim/Askıda İzin: <strong style="color: {{ $health['delegation_matrix_subsystem']['orphaned_permissions'] > 0 ? '#ef4444' : '#10b981' }};">{{ $health['delegation_matrix_subsystem']['orphaned_permissions'] }}</strong>
                </div>
            </div>
        </div>
        <a href="{{ route('admin.permission_matrix') }}" style="font-size: 11.5px; font-weight: 700; color: #15803d; text-decoration: none; padding: 5px 12px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px;">Yetki Matrisi →</a>
    </div>

    <!-- 5. Kurumsal Vekalet & Personel Devir Sistemi -->
    <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 20px;">🤝</div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Kurumsal Vekalet & Devir</div>
                <div style="font-size: 14px; font-weight: 800; color: #0f172a;" id="kpiDelegationStatus">
                    {{ $health['user_delegations_subsystem']['active_delegations'] }} Canlı Vekalet Devrede
                    <span style="font-size: 11px; color: #10b981;">●</span>
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;" id="kpiDelegationSubtext">
                    Toplam: <strong>{{ $health['user_delegations_subsystem']['total_delegations'] }}</strong> | Süresi Dolan: <strong>{{ $health['user_delegations_subsystem']['expired_delegations'] }}</strong> | Devredilen Üye: <strong>{{ $health['user_delegations_subsystem']['handover_records'] }}</strong>
                </div>
            </div>
        </div>
        <a href="{{ route('admin.audit_logs') }}#tab-visibility" style="font-size: 11.5px; font-weight: 700; color: #2563eb; text-decoration: none; padding: 5px 12px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px;">Denetim Logları →</a>
    </div>
</div>

<!-- ROL BAZLI ERİŞİM VE GÜVENLİK DENETİM KARTLARI -->
<div class="role-cards-section">
    <div class="d-flex justify-between align-center flex-wrap gap-2">
        <div>
            <h3 style="font-size: 16.5px; font-weight: 800; color: #0f172a; margin: 0 0 4px 0; display: flex; align-items: center; gap: 8px;">
                <span>👥</span>
                <span>Rol Bazlı Güvenlik & Yetki Kapsam Kartları</span>
            </h3>
            <p style="font-size: 12.5px; color: #64748b; margin: 0;">
                Sistemdeki her rolün erişebildiği rota hacmi, izin genişliği ve güvenlik kapsamı. Karta tıklayarak aşağıdaki rota tablosunu anında bu role göre filtreleyebilirsiniz.
            </p>
        </div>
        <div>
            <span class="badge badge-light text-xs border" style="padding: 6px 12px; font-weight: 600;">
                Toplam {{ count($health['role_audits']) }} Kullanıcı Rolü Denetlendi
            </span>
        </div>
    </div>

    <div class="role-cards-grid">
        @foreach($health['role_audits'] as $roleAudit)
            <div class="role-diag-card" onclick="filterByRoleName('{{ addslashes($roleAudit['name']) }}')" title="{{ $roleAudit['name'] }} rolünün erişebildiği rotaları listele">
                <div class="role-diag-header">
                    <div class="role-name-box">
                        <span style="width: 10px; height: 10px; border-radius: 50%; background: {{ $roleAudit['badge_color'] }}; display: inline-block;"></span>
                        <strong style="font-size: 13.5px; color: #0f172a;">{{ $roleAudit['name'] }}</strong>
                    </div>
                    <span class="role-group-pill" style="background: {{ $roleAudit['group_color'] }};">
                        {{ $roleAudit['group_name'] }}
                    </span>
                </div>

                <div class="d-flex justify-between align-center" style="font-size: 11.5px; color: #64748b; margin-top: 6px;">
                    <span>Erişim Kapsamı:</span>
                    <strong style="color: #0f172a;">{{ $roleAudit['accessible_routes'] }} / {{ $health['total_routes'] }} Rota (%{{ $roleAudit['coverage_pct'] }})</strong>
                </div>

                <div class="role-coverage-bar">
                    <div class="role-coverage-fill" style="width: {{ $roleAudit['coverage_pct'] }}%; background: {{ $roleAudit['is_superadmin'] ? '#10b981' : ($roleAudit['coverage_pct'] > 50 ? '#0284c7' : '#f59e0b') }};"></div>
                </div>

                <div class="d-flex justify-between align-center" style="margin-top: 8px;">
                    <span style="font-size: 11px; color: #94a3b8;">
                        {{ $roleAudit['permissions_count'] }} Tanımlı İzin
                    </span>
                    <span style="font-size: 11.5px; font-weight: 700; color: #0284c7;">
                        Rotaları Göster →
                    </span>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- CANLI ROTA, MIDDLEWARE & HATA AYIKLAMA TABLOSU -->
<div class="routes-card">
    <div class="routes-toolbar">
        <div>
            <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 4px 0; display: flex; align-items: center; gap: 8px;">
                <span>🗺️</span>
                <span>Canlı Rota, Middleware & Bütünlük Matrisi</span>
            </h3>
            <p style="font-size: 12.5px; color: #64748b; margin: 0;">
                Uygulama rotalarının denetleyici bağlantıları, middleware güvenlik zinciri ve anlık rol izinleri.
            </p>
        </div>

        <div class="d-flex align-center gap-3 flex-wrap">
            <!-- Hızlı Durum Filtre Çipleri -->
            <div class="routes-filter-chips">
                <button type="button" class="route-chip-btn active" data-status="all" onclick="setStatusFilter('all', this)">
                    Tümü (<span id="countAll">{{ $health['total_routes'] }}</span>)
                </button>
                <button type="button" class="route-chip-btn" data-status="healthy" onclick="setStatusFilter('healthy', this)">
                    <span>✅ Sağlıklı</span> (<span id="countHealthy">{{ $health['healthy_routes'] }}</span>)
                </button>
                @if($health['broken_routes'] > 0)
                    <button type="button" class="route-chip-btn" data-status="broken" onclick="setStatusFilter('broken', this)" style="border-color: #fca5a5; color: #dc2626;">
                        <span>⚠️ İnceleme Gereken</span> (<span id="countBroken">{{ $health['broken_routes'] }}</span>)
                    </button>
                @endif
                <button type="button" class="route-chip-btn" data-status="public" onclick="setStatusFilter('public', this)">
                    🌐 Genel Erişim
                </button>
                <button type="button" class="route-chip-btn" onclick="setModuleFilter('DOA Eşik Matrisi', this)">
                    <span>⚖️ DOA Eşik</span>
                </button>
                <button type="button" class="route-chip-btn" onclick="setModuleFilter('Kurullar & Komiteler', this)">
                    <span>🏛️ Kurullar</span>
                </button>
                <button type="button" class="route-chip-btn" onclick="setModuleFilter('Proje Yönetimi', this)">
                    <span>📁 Projeler</span>
                </button>
            </div>

            <!-- Metot Seçici -->
            <select id="methodFilter" class="form-control" style="width: 120px; height: 38px; font-size: 12.5px;" onchange="applyAllFilters()">
                <option value="">Metot: Tümü</option>
                <option value="GET">GET</option>
                <option value="POST">POST</option>
                <option value="PUT">PUT</option>
                <option value="DELETE">DELETE</option>
            </select>

            <!-- Arama Kutusu -->
            <div style="position: relative;">
                <span style="position: absolute; left: 12px; top: 10px; font-size: 14px; color: #94a3b8;">🔍</span>
                <input type="text" id="routeSearch" class="routes-search-input" placeholder="URI, isim, controller veya rol ara..." oninput="applyAllFilters()">
            </div>
        </div>
    </div>

    <div class="table-responsive" style="max-height: 680px; overflow-y: auto;">
        <table class="routes-table" id="routesTable">
            <thead style="position: sticky; top: 0; z-index: 2;">
                <tr>
                    <th style="width: 80px;">Metot</th>
                    <th style="width: 25%;">Rota URI</th>
                    <th style="width: 18%;">İsim (Route Name)</th>
                    <th style="width: 20%;">Controller / Action</th>
                    <th style="width: 12%;">Gerekli İzin</th>
                    <th style="width: 15%;">Erişebilen Roller</th>
                    <th style="width: 100px; text-align: right;">Durum & Tanı</th>
                </tr>
            </thead>
            <tbody>
                @foreach($health['routes'] as $r)
                    @php
                        $methodClass = match($r['methods']) {
                            'GET' => 'badge-info',
                            'POST' => 'badge-success',
                            'PUT', 'PATCH' => 'badge-warning',
                            'DELETE' => 'badge-danger',
                            default => 'badge-secondary'
                        };
                        $isPublic = ($r['permission'] === 'Genel Erişim');
                    @endphp
                    <tr class="route-row {{ $r['has_error'] ? 'has-error-row' : '' }}"
                        data-method="{{ $r['methods'] }}"
                        data-status="{{ $r['has_error'] ? 'broken' : 'healthy' }}"
                        data-is-public="{{ $isPublic ? '1' : '0' }}"
                        data-module="{{ $r['module'] ?? '' }}"
                        data-search="{{ strtolower($r['uri'] . ' ' . $r['name'] . ' ' . $r['action'] . ' ' . $r['full_action'] . ' ' . $r['permission'] . ' ' . ($r['module'] ?? '') . ' ' . implode(' ', $r['allowed_roles'])) }}">
                        <td>
                            <span class="badge {{ $methodClass }} text-xs font-weight-700" style="padding: 4px 8px; border-radius: 6px;">
                                {{ $r['methods'] }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-center gap-2 flex-wrap">
                                <code class="text-sm font-weight-600 text-dark" style="background: #f1f5f9; padding: 3px 8px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                    {{ $r['uri'] }}
                                </code>
                                @if(isset($r['module']) && $r['module'])
                                    <span style="font-size: 10.5px; font-weight: 650; color: #475569; background: #e2e8f0; padding: 2px 7px; border-radius: 4px; white-space: nowrap;">
                                        {{ $r['module'] }}
                                    </span>
                                @endif
                                @if($r['has_error'])
                                    <span class="badge badge-danger text-xs" style="padding: 2px 6px; font-size: 10px; border-radius: 4px;">
                                        Eksik Metot
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="text-xs text-muted" style="font-family: monospace;">
                                {{ $r['name'] !== '-' ? $r['name'] : '—' }}
                            </span>
                        </td>
                        <td>
                            <span class="text-xs font-weight-600 {{ $r['has_error'] ? 'text-danger' : 'text-secondary' }}" title="Middleware: {{ $r['middleware'] }} | Hedef: {{ $r['full_action'] }}">
                                {{ $r['action'] }}
                            </span>
                        </td>
                        <td>
                            @if($isPublic)
                                <span class="badge badge-light text-xs font-weight-500" style="border: 1px solid #e2e8f0; color: #64748b;">
                                    🌐 Genel
                                </span>
                            @else
                                <span class="badge badge-light text-xs font-weight-600" style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;">
                                    🔑 {{ $r['permission'] }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @forelse($r['allowed_roles'] as $roleName)
                                    <span class="badge badge-light text-xs font-weight-500 border" style="font-size: 11px; padding: 2px 6px;">
                                        {{ $roleName }}
                                    </span>
                                @empty
                                    <span class="text-xs text-muted">Korumasız / Yetki Yok</span>
                                @endforelse
                            </div>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            @if($r['has_error'])
                                <button type="button" class="btn btn-sm btn-danger" onclick="openDebugModal({{ json_encode($r) }})" style="padding: 4px 10px; font-size: 11.5px; font-weight: 700; border-radius: 6px; display: inline-flex; align-items: center; gap: 5px;">
                                    <span>🐛</span> Hata Ayıkla
                                </button>
                            @else
                                <button type="button" class="btn btn-sm btn-light" onclick="openDebugModal({{ json_encode($r) }})" style="padding: 4px 10px; font-size: 11.5px; font-weight: 600; border-radius: 6px; border: 1px solid #e2e8f0; display: inline-flex; align-items: center; gap: 5px;">
                                    <span>🔍</span> Tanı
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="padding: 12px 24px; border-top: 1px solid #e2e8f0; background: #f8fafc; font-size: 12px; color: #64748b; display: flex; justify-content: space-between; align-items: center;">
        <span id="routesVisibleCount">101 rota listeleniyor</span>
        <span>Kök Dizin: <code>routes/web.php</code></span>
    </div>
</div>

<!-- HATA AYIKLAMA VE TANI DETAYI MODAL (KOPYALANABİLİR HATA RAPORU) -->
<div id="debugModalBackdrop" class="custom-modal-backdrop" onclick="closeDebugModal(event)">
    <div class="custom-modal-dialog" onclick="event.stopPropagation()">
        <div class="custom-modal-header">
            <div class="d-flex align-center gap-2">
                <span id="modalHeaderEmoji" style="font-size: 20px;">🐛</span>
                <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0;" id="modalHeaderTitle">
                    Rota Hata Ayıklama & Tanı Detayı
                </h3>
            </div>
            <button type="button" onclick="closeDebugModal()" style="border: none; background: transparent; font-size: 20px; color: #94a3b8; cursor: pointer; padding: 4px 8px;">
                ✕
            </button>
        </div>

        <div class="custom-modal-body">
            <!-- Durum Uyarı Kutusu -->
            <div id="modalStatusAlert" style="padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid; display: flex; align-items: flex-start; gap: 12px;">
                <span id="modalAlertIcon" style="font-size: 22px;">⚠️</span>
                <div>
                    <strong id="modalAlertTitle" style="font-size: 13.5px; display: block; margin-bottom: 2px;"></strong>
                    <div id="modalAlertDesc" style="font-size: 12.5px; line-height: 1.5;"></div>
                </div>
            </div>

            <!-- Rota & Bileşen Bilgi Kartı -->
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 20px; background: #f8fafc; padding: 14px; border-radius: 10px; border: 1px solid #e2e8f0;">
                <div>
                    <span style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; display: block;">Rota URI & Metot:</span>
                    <code id="modalRouteUri" style="font-size: 12.5px; font-weight: 700; color: #0f172a;"></code>
                </div>
                <div>
                    <span style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; display: block;">Rota İsmi:</span>
                    <span id="modalRouteName" style="font-size: 12.5px; font-weight: 600; color: #334155;"></span>
                </div>
                <div>
                    <span style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; display: block;">Hedef Controller & Metot:</span>
                    <code id="modalRouteTarget" style="font-size: 12px; color: #0284c7;"></code>
                </div>
                <div>
                    <span style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; display: block;">Gerekli İzin:</span>
                    <span id="modalRoutePerm" style="font-size: 12px; font-weight: 600; color: #059669;"></span>
                </div>
                <div style="grid-column: span 2;">
                    <span style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; display: block;">Middleware Güvenlik Zinciri:</span>
                    <span id="modalRouteMiddleware" style="font-size: 12px; color: #475569; word-break: break-all;"></span>
                </div>
            </div>

            <!-- Çözüm & Düzeltme Önerisi -->
            <div id="modalSolutionBox" style="margin-bottom: 20px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 14px 16px;">
                <strong style="color: #1e40af; font-size: 13px; display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                    <span>💡</span> Geliştirici Çözüm Önerisi:
                </strong>
                <p id="modalSolutionText" style="margin: 0; font-size: 12.5px; color: #1e3a8a; line-height: 1.5;"></p>
            </div>

            <!-- Kod ve Exception Stack Trace Snippet -->
            <div>
                <div class="d-flex justify-between align-center mb-2">
                    <span style="font-size: 12px; font-weight: 700; color: #334155;">Simüle Edilen Hata / Stack Trace:</span>
                    <button type="button" class="btn btn-sm btn-light" onclick="copyModalTrace()" style="font-size: 11px; padding: 3px 8px; border-radius: 4px; border: 1px solid #cbd5e1;">
                        📋 Bu Kodu Kopyala
                    </button>
                </div>
                <pre id="modalCodeBox" class="debug-code-box"></pre>
            </div>
        </div>

        <div class="custom-modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeDebugModal()">
                Kapat
            </button>
            <button type="button" class="btn btn-primary" id="btnCopyDebugReport" onclick="copyCurrentDebugReport()">
                <span>📋</span> Hatayı ve Tanı Raporunu Kopyala
            </button>
        </div>
    </div>
</div>

<!-- TOAST BİLDİRİMİ -->
<div id="toastNotification">
    <span id="toastIcon">✅</span>
    <span id="toastMessage">İşlem başarılı!</span>
</div>

@endsection

@push('scripts')
<script>
let currentActiveStatusFilter = 'all';
let currentSelectedRouteData = null;

// ==========================================================================
// 1. CANLI YENİDEN TEST ET (AJAX RE-TEST) FONKSİYONU
// ==========================================================================
function runLiveRetest() {
    const btn = document.getElementById('btnRetest');
    const btnText = document.getElementById('btnRetestText');

    btn.disabled = true;
    btn.classList.add('btn-spinning');
    btnText.innerText = 'Test Ediliyor...';

    fetch("{{ route('admin.system_health.retest') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Sunucu yanıt vermedi: HTTP ' + response.status);
        return response.json();
    })
    .then(res => {
        btn.classList.remove('btn-spinning');
        btnText.innerText = '✓ Tamamlandı!';

        setTimeout(() => {
            btn.disabled = false;
            btnText.innerText = 'Yeniden Test Et';
        }, 1500);

        if (res.success || res.status) {
            updateDashboardMetrics(res.data, res.message, res.timestamp);
            showToast('✅ Canlı sistem testi tamamlandı: ' + res.message);
        } else {
            showToast('❌ Test başarısız: ' + (res.message || 'Bilinmeyen hata'), 'error');
        }
    })
    .catch(err => {
        btn.classList.remove('btn-spinning');
        btn.disabled = false;
        btnText.innerText = 'Yeniden Test Et';
        showToast('❌ Bağlantı hatası: ' + err.message, 'error');
    });
}

// Canlı Metrikleri Ekranda Güncelleme
function updateDashboardMetrics(data, message, timestamp) {
    // Sonuç Panosu (Result Banner)
    const banner = document.getElementById('healthResultBanner');
    banner.style.display = 'flex';
    banner.className = 'health-result-banner ' + (data.overall_status === 'HEALTHY' ? 'status-healthy' : (data.overall_status === 'WARNING' ? 'status-warning' : 'status-critical'));

    document.getElementById('bannerStatusEmoji').innerText = (data.overall_status === 'HEALTHY' ? '✅' : (data.overall_status === 'WARNING' ? '⚠️' : '❌'));
    document.getElementById('bannerStatusTitle').innerText = message;
    document.getElementById('bannerTimestamp').innerText = 'Son Test: ' + timestamp;
    document.getElementById('bannerStatusSubtext').innerHTML =
        `Veritabanı: <strong>${data.db_latency_ms} ms</strong> | Depolama: <strong>${data.storage_status}</strong> | Cache: <strong>${data.cache_status}</strong> | Aktif Rotalar: <strong>${data.healthy_routes} / ${data.total_routes} Geçerli</strong> | <span id="bannerTimestamp">Son Test: ${timestamp}</span>`;

    // KPI Kartları
    if (document.getElementById('kpiDbStatus')) document.getElementById('kpiDbStatus').innerText = (data.db_status === 'OK' ? 'Çevrimiçi' : 'Hata!');
    if (document.getElementById('kpiDbLatency')) document.getElementById('kpiDbLatency').innerText = data.db_latency_ms + ' ms';
    if (document.getElementById('kpiStorageStatus')) document.getElementById('kpiStorageStatus').innerText = (data.storage_status === 'OK' ? 'Yazılabilir' : 'Hata!');
    if (document.getElementById('kpiCacheStatus')) document.getElementById('kpiCacheStatus').innerText = (data.cache_status === 'OK' ? 'Operasyonel' : 'Uyarı');
    if (document.getElementById('kpiCacheLatency')) document.getElementById('kpiCacheLatency').innerText = data.cache_latency_ms + ' ms';
    if (document.getElementById('kpiQueuePending')) document.getElementById('kpiQueuePending').innerText = data.queue_pending_jobs;
    if (document.getElementById('kpiQueueFailed')) document.getElementById('kpiQueueFailed').innerText = data.queue_failed_jobs;
    if (document.getElementById('kpiMemoryUsage')) document.getElementById('kpiMemoryUsage').innerText = data.memory_usage_mb + ' MB';
    if (document.getElementById('kpiRouteScore')) document.getElementById('kpiRouteScore').innerText = `${data.healthy_routes} / ${data.total_routes}`;

    if (data.delegation_matrix_subsystem) {
        const dms = data.delegation_matrix_subsystem;
        if (document.getElementById('kpiPrjPermStatus')) {
            document.getElementById('kpiPrjPermStatus').innerHTML = `${dms.granted_permissions} Aktif Mikro Yetki <span style="font-size: 11px; color: ${dms.status === 'HEALTHY' ? '#10b981' : '#f59e0b'};">●</span>`;
        }
        if (document.getElementById('kpiPrjPermSubtext')) {
            document.getElementById('kpiPrjPermSubtext').innerHTML = `Toplam <strong>${dms.total_permissions}</strong> Kayıt | Yetim/Askıda İzin: <strong style="color: ${dms.orphaned_permissions > 0 ? '#ef4444' : '#10b981'};">${dms.orphaned_permissions}</strong>`;
        }
    }
    if (data.user_delegations_subsystem) {
        const uds = data.user_delegations_subsystem;
        if (document.getElementById('kpiDelegationStatus')) {
            document.getElementById('kpiDelegationStatus').innerHTML = `${uds.active_delegations} Canlı Vekalet Devrede <span style="font-size: 11px; color: #10b981;">●</span>`;
        }
        if (document.getElementById('kpiDelegationSubtext')) {
            document.getElementById('kpiDelegationSubtext').innerHTML = `Toplam: <strong>${uds.total_delegations}</strong> | Süresi Dolan: <strong>${uds.expired_delegations}</strong> | Devredilen Üye: <strong>${uds.handover_records}</strong>`;
        }
    }
}

// ==========================================================================
// 2. TABLO VE ARAMA FİLTRELEME
// ==========================================================================
let currentActiveModuleFilter = 'all';

function setStatusFilter(status, btnElement) {
    currentActiveStatusFilter = status;
    currentActiveModuleFilter = 'all';
    document.querySelectorAll('.route-chip-btn').forEach(btn => btn.classList.remove('active'));
    if (btnElement) btnElement.classList.add('active');
    applyAllFilters();
}

function setModuleFilter(moduleName, btnElement) {
    currentActiveModuleFilter = moduleName;
    currentActiveStatusFilter = 'all';
    document.querySelectorAll('.route-chip-btn').forEach(btn => btn.classList.remove('active'));
    if (btnElement) btnElement.classList.add('active');
    applyAllFilters();
}

function filterByRoleName(roleName) {
    const searchInput = document.getElementById('routeSearch');
    searchInput.value = roleName;
    applyAllFilters();
    showToast(`🔍 "${roleName}" rolüne ait rotalar filtrelendi.`);

    // Tabloya doğru yumuşak kaydır
    document.getElementById('routesTable').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function applyAllFilters() {
    const query = (document.getElementById('routeSearch').value || '').toLowerCase().trim();
    const selectedMethod = (document.getElementById('methodFilter').value || '').toUpperCase();
    const rows = document.querySelectorAll('.route-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const rowSearch = row.getAttribute('data-search') || '';
        const rowMethod = row.getAttribute('data-method') || '';
        const rowStatus = row.getAttribute('data-status') || '';
        const rowModule = row.getAttribute('data-module') || '';
        const isPublic = row.getAttribute('data-is-public') === '1';

        const matchesQuery = query === '' || rowSearch.includes(query);
        const matchesMethod = selectedMethod === '' || rowMethod.includes(selectedMethod);

        let matchesStatus = true;
        if (currentActiveStatusFilter === 'healthy') {
            matchesStatus = (rowStatus === 'healthy');
        } else if (currentActiveStatusFilter === 'broken') {
            matchesStatus = (rowStatus === 'broken');
        } else if (currentActiveStatusFilter === 'public') {
            matchesStatus = isPublic;
        }

        let matchesModule = true;
        if (currentActiveModuleFilter !== 'all') {
            matchesModule = rowModule.includes(currentActiveModuleFilter);
        }

        if (matchesQuery && matchesMethod && matchesStatus && matchesModule) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const counter = document.getElementById('routesVisibleCount');
    if (counter) counter.innerText = `${visibleCount} rota listeleniyor`;
}

// ==========================================================================
// 3. HATA AYIKLAMA & TANI MODAL İŞLEMLERİ
// ==========================================================================
function openDebugModal(routeData) {
    currentSelectedRouteData = routeData;
    const backdrop = document.getElementById('debugModalBackdrop');

    document.getElementById('modalRouteUri').innerText = `${routeData.methods} ${routeData.uri}`;
    document.getElementById('modalRouteName').innerText = routeData.name || '—';
    document.getElementById('modalRouteTarget').innerText = routeData.full_action || routeData.action;
    document.getElementById('modalRoutePerm').innerText = routeData.permission || 'Genel Erişim';
    document.getElementById('modalRouteMiddleware').innerText = routeData.middleware || 'Middleware yok';

    const alertBox = document.getElementById('modalStatusAlert');
    const alertIcon = document.getElementById('modalAlertIcon');
    const alertTitle = document.getElementById('modalAlertTitle');
    const alertDesc = document.getElementById('modalAlertDesc');
    const solutionBox = document.getElementById('modalSolutionBox');
    const solutionText = document.getElementById('modalSolutionText');
    const codeBox = document.getElementById('modalCodeBox');

    if (routeData.has_error) {
        document.getElementById('modalHeaderEmoji').innerText = '🐛';
        document.getElementById('modalHeaderTitle').innerText = 'Rota Hata Ayıklama & Tanı Detayı';

        alertBox.style.background = '#fef2f2';
        alertBox.style.borderColor = '#ef4444';
        alertBox.style.color = '#991b1b';
        alertIcon.innerText = '❌';
        alertTitle.innerText = `Kritik Hata: ${routeData.error_type} (${routeData.error_message})`;
        alertDesc.innerText = 'Bu rota için tanımlanan PHP Controller sınıfı veya metodu mevcut değil. Rota çağrıldığında sunucu HTTP 500 veya BadMethodCallException hatası döndürecektir.';

        solutionBox.style.display = 'block';
        solutionText.innerText = routeData.debug_hint || 'routes/web.php dosyasındaki rota hedefini kontrol edin veya Controller içine eksik metodu ekleyin.';

        codeBox.innerText = routeData.simulated_trace || `// HATA DETAYI:\nRota: ${routeData.methods} ${routeData.uri}\nHata: ${routeData.error_message}\nÖneri: ${routeData.debug_hint}`;
    } else {
        document.getElementById('modalHeaderEmoji').innerText = '🔍';
        document.getElementById('modalHeaderTitle').innerText = 'Rota Sağlık & Yetki Tanı Detayı';

        alertBox.style.background = '#f0fdf4';
        alertBox.style.borderColor = '#10b981';
        alertBox.style.color = '#065f46';
        alertIcon.innerText = '✅';
        alertTitle.innerText = 'Rota Sağlıklı & Operasyonel';
        alertDesc.innerText = 'Controller ve metot bütünlüğü doğrulandı. Güvenlik middleware zinciri ve izin filtreleri aktif durumda.';

        solutionBox.style.display = 'none';

        codeBox.innerText = `// ROTA SAĞLIK BİLGİSİ:\nURI: ${routeData.methods} ${routeData.uri}\nHedef: ${routeData.full_action}\nGerekli İzin: ${routeData.permission}\nMiddleware: ${routeData.middleware}\nErişebilen Roller: ${routeData.allowed_roles.join(', ') || 'Herkes'}`;
    }

    backdrop.classList.add('show');
}

function closeDebugModal(e) {
    if (!e || e.target.id === 'debugModalBackdrop') {
        document.getElementById('debugModalBackdrop').classList.remove('show');
    }
}

// Hatayı Panoya Kopyalama (Tekil Rota Raporu)
function copyCurrentDebugReport() {
    if (!currentSelectedRouteData) return;
    const r = currentSelectedRouteData;

    const report = [
        '### 🐛 KÖKSAN PMS - Rota Tanı & Hata Ayıklama Raporu',
        `- **Tarih:** ${new Date().toLocaleString('tr-TR')}`,
        `- **Metot & URI:** ${r.methods} ${r.uri}`,
        `- **İsim (Route Name):** ${r.name || '—'}`,
        `- **Hedef Sınıf / Eylem:** ${r.full_action || r.action}`,
        `- **Gerekli İzin:** ${r.permission}`,
        `- **Hata Durumu:** ${r.has_error ? 'KRİTİK HATA (' + r.error_type + ')' : 'SAĞLIKLI'}`,
        `- **Hata Mesajı:** ${r.error_message || 'Hata yok, operasyonel.'}`,
        `- **Önerilen Çözüm:** ${r.debug_hint || 'Gereksinim yok.'}`,
        `- **Middleware Zinciri:** ${r.middleware || 'Yok'}`,
        '',
        '```php',
        r.simulated_trace || r.full_action,
        '```'
    ].join('\n');

    navigator.clipboard.writeText(report).then(() => {
        showToast('📋 Hata ve tanı raporu panoya kopyalandı!');
    }).catch(err => {
        showToast('Kopyalama başarısız: ' + err, 'error');
    });
}

function copyModalTrace() {
    const code = document.getElementById('modalCodeBox').innerText;
    navigator.clipboard.writeText(code).then(() => {
        showToast('📋 Kod / Trace panoya kopyalandı!');
    });
}

// Tüm Sistem Tanı Raporunu Panoya Kopyalama
function copyFullHealthReport() {
    const summary = [
        '### 🩺 KÖKSAN PMS - Sistem Sağlığı & Tanı Özeti',
        `- **Tarih:** ${new Date().toLocaleString('tr-TR')}`,
        `- **Veritabanı Durumu:** {{ $health['db_status'] }} (Gecikme: {{ $health['db_latency_ms'] }} ms)`,
        `- **Storage / Disk:** {{ $health['storage_status'] }} (Disk: {{ $health['storage_disk'] }})`,
        `- **Cache / Önbellek:** {{ $health['cache_status'] }} (Latency: {{ $health['cache_latency_ms'] }} ms)`,
        `- **Kuyruk Durumu:** {{ $health['queue_driver'] }} (Bekleyen: {{ $health['queue_pending_jobs'] }}, Hatalı: {{ $health['queue_failed_jobs'] }})`,
        `- **Sunucu Çevresi:** PHP {{ $health['php_version'] }} | Laravel {{ $health['laravel_version'] }}`,
        `- **Bellek Kullanımı:** {{ $health['memory_usage_mb'] }} MB (Limit: {{ $health['memory_limit'] }})`,
        `- **Toplam Rotalar:** {{ $health['total_routes'] }} Adet (Sağlıklı: {{ $health['healthy_routes'] }}, İnceleme Bekleyen: {{ $health['broken_routes'] }})`,
        `- **Proje İçi Yetki Matrisi:** {{ $health['delegation_matrix_subsystem']['status'] }} (Aktif: {{ $health['delegation_matrix_subsystem']['granted_permissions'] }}, Yetim: {{ $health['delegation_matrix_subsystem']['orphaned_permissions'] }})`,
        `- **Kurumsal Vekalet & Devir:** {{ $health['user_delegations_subsystem']['status'] }} (Canlı Vekalet: {{ $health['user_delegations_subsystem']['active_delegations'] }}, Toplam Devir: {{ $health['user_delegations_subsystem']['handover_records'] }})`,
        `- **Genel Sağlık:** {{ $health['overall_status'] }}`
    ].join('\n');

    navigator.clipboard.writeText(summary).then(() => {
        showToast('📋 Tüm sistem tanı raporu panoya kopyalandı!');
    }).catch(err => {
        showToast('Kopyalama başarısız: ' + err, 'error');
    });
}

// Toast Gösterme
function showToast(message, type = 'success') {
    const toast = document.getElementById('toastNotification');
    const toastIcon = document.getElementById('toastIcon');
    const toastMessage = document.getElementById('toastMessage');

    toastIcon.innerText = (type === 'error' ? '❌' : '✅');
    toastMessage.innerText = message;
    toast.style.background = (type === 'error' ? '#991b1b' : '#0f172a');
    toast.style.display = 'flex';

    setTimeout(() => {
        toast.style.display = 'none';
    }, 3500);
}
</script>
@endpush
