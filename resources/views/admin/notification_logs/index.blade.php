@extends('layouts.app')

@section('title', 'Bildirim & E-Posta Gönderim Günlüğü')

@section('content')
<style>
/* ==========================================================================
   MODERN MATRIX HEADER & SISTER NAVIGATION
   ========================================================================== */
.matrix-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.matrix-page-header .header-left {
    flex: 1;
    min-width: 280px;
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
    line-height: 1.25;
}
.matrix-page-header .page-subtitle {
    font-size: 13.5px;
    color: #64748b;
    margin: 0;
    line-height: 1.5;
    max-width: 820px;
    font-weight: 450;
}

/* Matrisler Arası Hızlı Geçiş Sekmeler Çubuğu */
.matrix-sister-tabs {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 8px !important;
    border-bottom: 2px solid #e2e8f0 !important;
    margin-bottom: 24px !important;
    padding-bottom: 0 !important;
    align-items: center !important;
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

/* KPI Kartları */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.kpi-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    display: flex;
    align-items: center;
    gap: 16px;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}
.kpi-icon-wrap {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
}
.kpi-info {
    flex: 1;
    min-width: 0;
}
.kpi-label {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
    margin-bottom: 4px;
}
.kpi-value {
    font-size: 24px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.1;
}

/* Canlı Test & Entegrasyon Bilgi Kartı */
.test-panel-card {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    border-radius: 12px;
    padding: 20px 24px;
    color: #f8fafc;
    margin-bottom: 24px;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.25);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 24px;
    flex-wrap: wrap;
}
.test-panel-info {
    flex: 1;
    min-width: 280px;
}
.test-panel-title {
    font-size: 16px;
    font-weight: 700;
    color: #ffffff;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0 0 6px 0;
}
.test-panel-desc {
    font-size: 13px;
    color: #94a3b8;
    margin: 0;
    line-height: 1.5;
}
.test-panel-form {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.test-input {
    background: #0b1120;
    border: 1px solid #334155;
    color: #ffffff;
    padding: 9px 14px;
    border-radius: 8px;
    font-size: 13.5px;
    min-width: 260px;
    outline: none;
    transition: border-color 0.15s ease;
}
.test-input:focus {
    border-color: #38bdf8;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2);
}
.test-btn {
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
    color: #ffffff;
    border: none;
    padding: 9px 18px;
    border-radius: 8px;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: opacity 0.15s ease, transform 0.1s ease;
    white-space: nowrap;
}
.test-btn:hover {
    opacity: 0.95;
    transform: translateY(-1px);
}
.test-btn:active {
    transform: translateY(0);
}

/* Filtre Alanı */
.filter-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}
.filter-form-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1.5fr 1.5fr auto;
    gap: 12px;
    align-items: end;
}
@media (max-width: 1200px) {
    .filter-form-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }
}
.filter-label {
    font-size: 12px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 5px;
    display: block;
}
.filter-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 13px;
    background-color: #f8fafc;
    color: #0f172a;
    outline: none;
    transition: border-color 0.15s ease;
}
.filter-control:focus {
    border-color: #0284c7;
    background-color: #ffffff;
}

/* Tablo Kartı & Konteyner */
.table-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    overflow: hidden;
}
.table-responsive-box {
    width: 100% !important;
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
}
.table-responsive-box::-webkit-scrollbar {
    height: 7px;
}
.table-responsive-box::-webkit-scrollbar-track {
    background: #f1f5f9;
}
.table-responsive-box::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.table-responsive-box::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.log-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
    white-space: nowrap;
}
.log-table th {
    background-color: #f8fafc;
    color: #475569;
    font-weight: 700;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 12px 16px;
    border-bottom: 2px solid #e2e8f0;
}
.log-table td {
    padding: 14px 16px;
    border-bottom: 1px solid #f1f5f9;
    color: #1e293b;
    vertical-align: middle;
}
.log-table tr:hover td {
    background-color: #f8fafc;
}

/* Badge'ler */
.badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 9999px;
    font-size: 11.5px;
    font-weight: 600;
    line-height: 1;
}
.badge-mail {
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
}
.badge-bell {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
}
.badge-status-sent {
    background: #dcfce7;
    color: #15803d;
    border: 1px solid #bbf7d0;
}
.badge-status-failed {
    background: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}

/* Modal Stilleri */
.log-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    box-sizing: border-box;
}
.log-modal-content {
    background: #ffffff;
    width: 100%;
    max-width: 800px;
    max-height: 90vh;
    border-radius: 16px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: modalPopIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes modalPopIn {
    from { opacity: 0; transform: scale(0.96); }
    to { opacity: 1; transform: scale(1); }
}
.log-modal-header {
    background: #0f172a;
    color: #ffffff;
    padding: 18px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.log-modal-body {
    padding: 24px;
    overflow-y: auto;
    flex: 1;
}
.log-modal-footer {
    padding: 14px 24px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
</style>

<!-- ÜST SAYFA BAŞLIĞI -->
<div class="matrix-page-header">
    <div class="header-left">
        <h1 class="page-title">
            <span>📧</span> Bildirim & E-Posta Gönderim Günlüğü
        </h1>
        <p class="page-subtitle">
            Microsoft Graph API (<code>kys@koksan.com</code>) üzerinden gönderilen tüm e-postalar ve sistem içi zil bildirimlerinin anlık denetim günlüğü, hata takibi ve canlı test paneli.
        </p>
    </div>
    <div class="header-right">
        <span class="badge-pill" style="background: #0284c7; color: #ffffff; padding: 6px 12px; font-size: 12px;">
            ⚡ Aktif Sürücü: {{ strtoupper(config('mail.default')) }}
        </span>
    </div>
</div>

<!-- SİSTER SEKMELER (Admin Modülleri Arası Hızlı Geçiş) -->
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
    <a href="{{ route('admin.system_health') }}" class="matrix-tab-link">
        <span class="tab-emoji">🩺</span>
        <span>Sistem Sağlığı</span>
    </a>
    <a href="{{ route('admin.notification_logs.index') }}" class="matrix-tab-link active">
        <span class="tab-emoji">📧</span>
        <span>Bildirim & Mail Logları</span>
    </a>
</div>

<!-- CANLI TEST & MICROSOFT GRAPH SERVİS PANELİ -->
<div class="test-panel-card">
    <div class="test-panel-info">
        <div class="test-panel-title">
            <span>🚀</span> Microsoft Graph Canlı E-Posta Testi
        </div>
        <p class="test-panel-desc">
            Azure AD üzerinden <strong>kys@koksan.com</strong> ile e-posta gönderim mekanizmasını anlık olarak test edebilirsiniz. Giden posta anında loglanacaktır.
        </p>
    </div>
    <form action="{{ route('admin.notification_logs.test_mail') }}" method="POST" class="test-panel-form">
        @csrf
        <input type="email" name="email" value="{{ auth()->user()->email ?? 'celal.karaman@koksan.com' }}" required placeholder="Alıcı e-posta adresi giriniz..." class="test-input">
        <button type="submit" class="test-btn">
            <span>✉️</span> Test E-Postası Gönder
        </button>
    </form>
</div>

<!-- KPI İSTATİSTİK KARTLARI -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-icon-wrap" style="background: #f1f5f9; color: #475569;">📊</div>
        <div class="kpi-info">
            <div class="kpi-label">Toplam Bildirim</div>
            <div class="kpi-value">{{ number_format($totalCount) }}</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon-wrap" style="background: #e0f2fe; color: #0284c7;">✉️</div>
        <div class="kpi-info">
            <div class="kpi-label">E-Posta Gönderimi</div>
            <div class="kpi-value">{{ number_format($mailCount) }}</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon-wrap" style="background: #fef3c7; color: #d97706;">🔔</div>
        <div class="kpi-info">
            <div class="kpi-label">Zil Bildirimi</div>
            <div class="kpi-value">{{ number_format($bellCount) }}</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon-wrap" style="background: #fee2e2; color: #dc2626;">⚠️</div>
        <div class="kpi-info">
            <div class="kpi-label">Hatalı / İletilemeyen</div>
            <div class="kpi-value" style="color: {{ $failedCount > 0 ? '#dc2626' : '#0f172a' }};">
                {{ number_format($failedCount) }}
            </div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon-wrap" style="background: #dcfce7; color: #16a34a;">📅</div>
        <div class="kpi-info">
            <div class="kpi-label">Bugün Gönderilen</div>
            <div class="kpi-value">{{ number_format($todayCount) }}</div>
        </div>
    </div>
</div>

<!-- FİLTRE & ARAMA ÇUBUĞU -->
<div class="filter-card">
    <form action="{{ route('admin.notification_logs.index') }}" method="GET" class="filter-form-grid">
        <div>
            <label class="filter-label">Arama</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Alıcı, e-posta, konu veya anahtar kelime..." class="filter-control">
        </div>
        <div>
            <label class="filter-label">Kanal</label>
            <select name="channel" class="filter-control">
                <option value="all">Tüm Kanallar</option>
                <option value="mail" {{ request('channel') === 'mail' ? 'selected' : '' }}>✉️ E-Posta</option>
                <option value="bell" {{ request('channel') === 'bell' ? 'selected' : '' }}>🔔 Zil Bildirimi</option>
            </select>
        </div>
        <div>
            <label class="filter-label">Durum</label>
            <select name="status" class="filter-control">
                <option value="all">Tüm Durumlar</option>
                <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>İletildi / Başarılı</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Başarısız</option>
            </select>
        </div>
        <div>
            <label class="filter-label">Olay / Şablon</label>
            <select name="event_key" class="filter-control">
                <option value="all">Tüm Olay Şablonları</option>
                @foreach($events as $ev)
                    <option value="{{ $ev->event_key }}" {{ request('event_key') === $ev->event_key ? 'selected' : '' }}>
                        {{ $ev->event_title }} ({{ $ev->event_key }})
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="filter-label">Tarih Aralığı</label>
            <div style="display: flex; gap: 6px;">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="filter-control" style="font-size: 11.5px;">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="filter-control" style="font-size: 11.5px;">
            </div>
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="submit" class="test-btn" style="padding: 8px 14px; background: #0284c7;">
                🔍 Filtrele
            </button>
            <a href="{{ route('admin.notification_logs.index') }}" class="test-btn" style="padding: 8px 12px; background: #64748b; text-decoration: none;">
                ✕ Sıfırla
            </a>
        </div>
    </form>
</div>

<!-- LOG TABLOSU -->
<div class="table-card">
    <div class="table-responsive-box">
        <table class="log-table">
            <thead>
                <tr>
                    <th style="width: 70px;">ID</th>
                    <th style="width: 120px;">Kanal</th>
                    <th>Alıcı Bilgisi</th>
                    <th>Olay / Şablon</th>
                    <th>Konu / Başlık</th>
                    <th style="width: 120px;">Durum</th>
                    <th style="width: 150px;">Tarih / Tetikleyen</th>
                    <th style="width: 130px; text-align: right;">İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td style="font-weight: 700; color: #64748b;">
                            #{{ $log->id }}
                        </td>
                        <td>
                            @if($log->channel === 'mail')
                                <span class="badge-pill badge-mail">
                                    <span>✉️</span> E-Posta
                                </span>
                            @else
                                <span class="badge-pill badge-bell">
                                    <span>🔔</span> Zil Bildirimi
                                </span>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #0f172a;">
                                {{ $log->recipient_name ?? ($log->user?->name ?? '—') }}
                            </div>
                            <div style="font-size: 11.5px; color: #64748b;">
                                {{ $log->recipient_email ?? ($log->user?->email ?? 'Sistem İçi') }}
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #1e293b;">
                                {{ $log->event_title ?? $log->event_key }}
                            </div>
                            <div style="font-size: 11px; color: #94a3b8; font-family: monospace;">
                                {{ $log->event_key }}
                            </div>
                        </td>
                        <td style="max-width: 320px; overflow: hidden; text-overflow: ellipsis;" title="{{ $log->subject }}">
                            <div style="font-weight: 500; color: #334155; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $log->subject ?? '(Başlık Belirtilmemiş)' }}
                            </div>
                        </td>
                        <td>
                            @if($log->status === 'sent')
                                <span class="badge-pill badge-status-sent">
                                    <span>✓</span> İletildi
                                </span>
                            @elseif($log->status === 'failed')
                                <span class="badge-pill badge-status-failed" title="{{ $log->error_message }}">
                                    <span>✕</span> Başarısız
                                </span>
                            @else
                                <span class="badge-pill" style="background: #f1f5f9; color: #475569;">
                                    {{ ucfirst($log->status) }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: 500; color: #334155;">
                                {{ $log->created_at ? $log->created_at->format('d.m.Y H:i') : '—' }}
                            </div>
                            <div style="font-size: 11.5px; color: #64748b;">
                                {{ $log->sentBy?->name ?? 'Sistem' }}
                            </div>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px; align-items: center;">
                                <button type="button" onclick="inspectLog({{ $log->id }})" class="test-btn" style="padding: 5px 10px; font-size: 12px; background: #0284c7;">
                                    👁️ İncele
                                </button>
                                @if($log->channel === 'mail' && $log->recipient_email)
                                    <form action="{{ route('admin.notification_logs.resend', $log->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Bu e-postayı alıcıya tekrar göndermek istiyor musunuz?');">
                                        @csrf
                                        <button type="submit" class="test-btn" style="padding: 5px 8px; font-size: 12px; background: #64748b;" title="Tekrar Gönder">
                                            🔄
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 48px 20px; color: #64748b;">
                            <div style="font-size: 32px; margin-bottom: 8px;">📭</div>
                            <div style="font-weight: 600; font-size: 14px;">Henüz bildirim veya mail log kaydı bulunmuyor.</div>
                            <div style="font-size: 12.5px; color: #94a3b8; margin-top: 4px;">Yukarıdaki "Test E-Postası Gönder" panelinden ilk testi gerçekleştirebilirsiniz.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- SAYFALAMA -->
    @if($logs->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
            {{ $logs->links() }}
        </div>
    @endif
</div>

<!-- DETAY İNCELEME & GÖVDE ÖNİZLEME MODALI -->
<div class="log-modal-backdrop" id="logModalBackdrop">
    <div class="log-modal-content">
        <div class="log-modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span id="modalChannelIcon" style="font-size: 20px;">✉️</span>
                <span style="font-weight: 700; font-size: 16px;" id="modalEventTitle">Bildirim Detayı</span>
                <span class="badge-pill" id="modalStatusBadge"></span>
            </div>
            <button type="button" onclick="closeLogModal()" style="background: none; border: none; color: #94a3b8; font-size: 22px; cursor: pointer; padding: 0 4px; line-height: 1;">
                ✕
            </button>
        </div>
        <div class="log-modal-body">
            <!-- ÜST META BİLGİLER -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 16px; font-size: 13px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <span style="color: #64748b;">Alıcı:</span>
                        <strong id="modalRecipient" style="color: #0f172a; margin-left: 6px;"></strong>
                    </div>
                    <div>
                        <span style="color: #64748b;">Tarih:</span>
                        <span id="modalDate" style="color: #0f172a; margin-left: 6px;"></span>
                    </div>
                    <div>
                        <span style="color: #64748b;">Tetikleyen:</span>
                        <span id="modalSentBy" style="color: #0f172a; margin-left: 6px;"></span>
                    </div>
                    <div>
                        <span style="color: #64748b;">Olay Kodu:</span>
                        <code id="modalEventKey" style="background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-size: 11.5px;"></code>
                    </div>
                </div>
                <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #e2e8f0;">
                    <span style="color: #64748b;">Konu / Başlık:</span>
                    <strong id="modalSubject" style="color: #0284c7; margin-left: 6px;"></strong>
                </div>
            </div>

            <!-- HATA MESAJI (Varsa) -->
            <div id="modalErrorBox" style="display: none; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; color: #991b1b; font-size: 13px;">
                <strong style="display: block; margin-bottom: 4px;">⚠️ Hata Detayı:</strong>
                <span id="modalErrorMessage"></span>
            </div>

            <!-- GÖVDE ÖNİZLEME -->
            <div>
                <div style="font-weight: 700; color: #0f172a; font-size: 13px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
                    İçerik Önizleme
                </div>
                <div id="modalBodyContainer" style="border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; min-height: 200px; max-height: 450px; overflow-y: auto; background: #ffffff;">
                    <!-- Dinamik Gövde Yüklenecek -->
                </div>
            </div>
        </div>
        <div class="log-modal-footer">
            <button type="button" onclick="closeLogModal()" class="test-btn" style="background: #64748b;">
                Kapat
            </button>
        </div>
    </div>
</div>

<script>
function inspectLog(logId) {
    const showUrl = "{{ url('/admin/bildirim-loglari') }}/" + logId;
    
    fetch(showUrl)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert('Log detayı alınamadı.');
                return;
            }
            const log = data.log;

            document.getElementById('modalEventTitle').innerText = log.event_title || log.event_key;
            document.getElementById('modalEventKey').innerText = log.event_key || '—';
            document.getElementById('modalRecipient').innerText = (log.recipient_name || '—') + ' (' + (log.recipient_email || '—') + ')';
            document.getElementById('modalDate').innerText = log.created_at;
            document.getElementById('modalSentBy').innerText = log.sent_by;
            document.getElementById('modalSubject').innerText = log.subject;
            document.getElementById('modalChannelIcon').innerText = log.channel === 'mail' ? '✉️' : '🔔';

            const statusBadge = document.getElementById('modalStatusBadge');
            if (log.status === 'sent') {
                statusBadge.innerText = 'İletildi / Başarılı';
                statusBadge.className = 'badge-pill badge-status-sent';
            } else if (log.status === 'failed') {
                statusBadge.innerText = 'Başarısız';
                statusBadge.className = 'badge-pill badge-status-failed';
            } else {
                statusBadge.innerText = log.status;
                statusBadge.className = 'badge-pill';
            }

            const errorBox = document.getElementById('modalErrorBox');
            if (log.error_message) {
                document.getElementById('modalErrorMessage').innerText = log.error_message;
                errorBox.style.display = 'block';
            } else {
                errorBox.style.display = 'none';
            }

            const bodyContainer = document.getElementById('modalBodyContainer');
            if (log.channel === 'mail') {
                // E-posta tam HTML önizlemesi için güvenli iframe kullanımı
                const iframe = document.createElement('iframe');
                iframe.style.width = '100%';
                iframe.style.minHeight = '360px';
                iframe.style.border = 'none';
                bodyContainer.innerHTML = '';
                bodyContainer.appendChild(iframe);
                
                const doc = iframe.contentWindow.document;
                doc.open();
                doc.write(log.body || '<p style="padding: 16px; color: #64748b;">(Gövde içeriği boş)</p>');
                doc.close();
            } else {
                // Zil bildirimi için metin gösterimi
                bodyContainer.innerHTML = `
                    <div style="padding: 20px; font-size: 14px; line-height: 1.6; color: #1e293b;">
                        <div style="font-weight: 700; font-size: 15px; margin-bottom: 8px; color: #0284c7;">${log.subject || 'Bildirim'}</div>
                        <div style="margin-bottom: 16px;">${log.body || ''}</div>
                        ${log.action_url ? `<a href="${log.action_url}" target="_blank" style="display: inline-block; padding: 6px 12px; background: #0284c7; color: white; text-decoration: none; border-radius: 6px; font-size: 12.5px;">Bağlantıya Git ↗</a>` : ''}
                    </div>
                `;
            }

            document.getElementById('logModalBackdrop').style.display = 'flex';
        })
        .catch(err => {
            console.error(err);
            alert('Log yüklenirken bir sorun oluştu.');
        });
}

function closeLogModal() {
    document.getElementById('logModalBackdrop').style.display = 'none';
    document.getElementById('modalBodyContainer').innerHTML = '';
}

// Modal dışına tıklayınca kapatma
window.onclick = function(event) {
    const modal = document.getElementById('logModalBackdrop');
    if (event.target === modal) {
        closeLogModal();
    }
}
</script>
@endsection
