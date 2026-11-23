@extends('layouts.app')

@section('title', 'Kalite & Kabul Yönetimi')
@section('page_title', 'Kalite Güvence, Test & Kabul Yönetimi')
@section('page_subtitle', 'Endüstriyel test standartları, kabul kapıları (Stage-Gates), tolerans ölçümleri ve uygunsuzluk (NCR/DÖF) takibi')

@section('content')
<style>
/* ==========================================================================
   KALİTE MODÜLÜ ÖZEL STİLLERİ (SIFIR BOOTSTRAP BAĞIMLILIĞI - PURE CSS)
   ========================================================================== */

/* 1. Üst Sekmeler ve Aksiyon Çubuğu */
.quality-nav-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    border-bottom: 2px solid #e2e8f0;
    margin-bottom: 24px;
    padding-bottom: 0;
}

.quality-tabs-group {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: -2px; /* Çizgiyle birleşme efekti */
}

.quality-tab-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    font-size: 13.5px;
    font-weight: 700;
    color: #64748b;
    text-decoration: none;
    border-bottom: 3px solid transparent;
    transition: all 0.15s ease;
    border-radius: 8px 8px 0 0;
}

.quality-tab-item:hover {
    color: #0284c7;
    background: #f8fafc;
}

.quality-tab-item.active {
    color: #0284c7;
    background: #f0f9ff;
    border-bottom: 3px solid #0284c7;
}

.quality-tab-item.danger-tab.active {
    color: #dc2626;
    background: #fef2f2;
    border-bottom: 3px solid #dc2626;
}

.quality-tab-badge {
    padding: 2px 8px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 800;
}

.quality-tab-item.active .quality-tab-badge {
    background: #0284c7;
    color: #ffffff;
}

.quality-tab-item:not(.active) .quality-tab-badge {
    background: #e2e8f0;
    color: #475569;
}

.quality-tab-item.danger-tab.active .quality-tab-badge {
    background: #dc2626;
    color: #ffffff;
}

/* 2. Üçlü KPI Özet Izgarası (Ekrana Tam Oturan, Taşmayan 3 Sütun) */
.quality-kpi-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 24px;
    width: 100%;
    box-sizing: border-box;
}

@media (max-width: 992px) {
    .quality-kpi-grid {
        grid-template-columns: 1fr;
    }
}

.quality-kpi-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    transition: all 0.2s ease;
    min-height: 96px;
    box-sizing: border-box;
}

.quality-kpi-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    transform: translateY(-1px);
}

.kpi-icon-box {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
}

.kpi-meta-box {
    flex: 1;
    min-width: 0;
}

.kpi-label {
    font-size: 11px;
    font-weight: 750;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.kpi-value-row {
    display: flex;
    align-items: baseline;
    gap: 8px;
    margin-bottom: 3px;
}

.kpi-number {
    font-size: 24px;
    font-weight: 900;
    line-height: 1.1;
    font-family: 'JetBrains Mono', monospace;
}

.kpi-subtext {
    font-size: 11.5px;
    color: #94a3b8;
    line-height: 1.3;
}

/* 3. Kompakt Yatay Filtre Alanı */
.quality-filter-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 14px 18px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}

.quality-filter-form {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin: 0;
}

.q-filter-control {
    height: 38px;
    border-radius: 8px;
    border: 1.5px solid #cbd5e1;
    font-size: 12.5px;
    padding: 0 12px;
    background: #ffffff;
    color: #0f172a;
    transition: all 0.15s ease;
    box-sizing: border-box;
}

.q-filter-control:focus {
    border-color: #0284c7;
    outline: none;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
}

/* 4. Tablo ve İçerik Düzeni */
.quality-table-container {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    overflow: hidden;
    margin-bottom: 24px;
}

.quality-table-header {
    padding: 14px 20px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #ffffff;
}

.quality-data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12.5px;
}

.quality-data-table th {
    background: #f8fafc;
    border-bottom: 1.5px solid #e2e8f0;
    color: #475569;
    font-size: 11px;
    text-transform: uppercase;
    font-weight: 800;
    letter-spacing: 0.4px;
    padding: 12px 18px;
    text-align: left;
}

.quality-data-table td {
    padding: 14px 18px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
    color: #1e293b;
}

.quality-data-table tbody tr:hover td {
    background-color: #f8fafc;
}

.quality-data-table tbody tr:last-child td {
    border-bottom: none;
}

/* Durum ve Şiddet Rozetleri */
.badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 9px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 700;
    border: 1px solid transparent;
}

/* 5. Custom Native Modal Overlay Stilleri */
.native-modal-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.65);
    align-items: center;
    justify-content: center;
    z-index: 10000;
    backdrop-filter: blur(4px);
    animation: modalBackdropFade 0.15s ease-out;
}

.native-modal-box {
    background: #ffffff;
    width: 680px;
    max-width: 94vw;
    border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    max-height: 90vh;
    animation: modalScaleIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes modalBackdropFade {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes modalScaleIn {
    from { transform: scale(0.96) translateY(6px); opacity: 0; }
    to { transform: scale(1) translateY(0); opacity: 1; }
}
</style>

<!-- ========================================================================= -->
<!-- 1. ÜST NAVİGASYON (SEKME BUTONLARI VE SAĞ AKSİYON BUTONLARI) -->
<!-- ========================================================================= -->
<div class="quality-nav-bar">
    <!-- Sol: Sekme Bağlantıları (Temiz Çizgili Tasarım) -->
    <div class="quality-tabs-group">
        <a href="?tab=checks{{ $selectedProjectId ? '&project_id=' . $selectedProjectId : '' }}" 
           class="quality-tab-item {{ $activeTab === 'checks' ? 'active' : '' }}">
            <span>🔬</span>
            <span>Kalite Testleri &amp; Kabul Kapıları</span>
            <span class="quality-tab-badge">{{ $totalChecks }}</span>
        </a>
        <a href="?tab=ncrs{{ $selectedProjectId ? '&project_id=' . $selectedProjectId : '' }}" 
           class="quality-tab-item danger-tab {{ $activeTab === 'ncrs' ? 'active' : '' }}">
            <span>🚨</span>
            <span>Uygunsuzluk &amp; Hata Panosu (NCR / DÖF)</span>
            <span class="quality-tab-badge">{{ $openNcrs }} Açık</span>
        </a>
    </div>

    <!-- Sağ: Aksiyon Butonları (Madde 19 - Yönetim Salt Okunur Koruması) -->
    @if(!auth()->user()->isYonetim())
    <div style="display: flex; align-items: center; gap: 8px;">
        <button type="button" onclick="openNativeModal('modalNewQuality')" 
                style="background: #0284c7; color: #ffffff; font-weight: 700; font-size: 12.5px; border-radius: 8px; padding: 8px 16px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25); height: 38px;">
            <span style="font-size: 15px; font-weight: 900;">+</span> Yeni Kalite Kriteri Ekle
        </button>
        <button type="button" onclick="openNativeModal('modalNewNcr')" 
                style="background: #dc2626; color: #ffffff; font-weight: 700; font-size: 12.5px; border-radius: 8px; padding: 8px 16px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(220, 38, 38, 0.25); height: 38px;">
            <span>🚨</span> Yeni Uygunsuzluk (NCR) Aç
        </button>
    </div>
    @endif
</div>

<!-- ========================================================================= -->
<!-- 2. ÜÇ SÜTUNLU YÖNETİCİ KPI KARTLARI (TAM OTURAN, TAŞMAYAN KURUMSAL DÜZEN) -->
<!-- ========================================================================= -->
<div class="quality-kpi-grid">
    
    <!-- Kart 1: Kalite Başarı Oranı -->
    <div class="quality-kpi-card" style="border-left: 4px solid {{ $qualityRate >= 85 ? '#16a34a' : ($qualityRate >= 65 ? '#d97706' : '#dc2626') }};">
        <div class="kpi-icon-box" style="background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7;">
            🎯
        </div>
        <div class="kpi-meta-box">
            <div class="kpi-label">Kalite Başarı Oranı</div>
            <div class="kpi-value-row">
                <span class="kpi-number" style="color: {{ $qualityRate >= 85 ? '#16a34a' : ($qualityRate >= 65 ? '#d97706' : '#dc2626') }};">
                    %{{ $qualityRate }}
                </span>
                <span style="font-size: 11px; font-weight: 700; color: #64748b;">
                    ({{ $passedChecks }} / {{ $totalChecks }} Onaylı)
                </span>
            </div>
            <div class="kpi-subtext">Geçti ve Şartlı Kabul testleri</div>
        </div>
    </div>

    <!-- Kart 2: Test & Kontrol Planı -->
    <div class="quality-kpi-card" style="border-left: 4px solid #0284c7;">
        <div class="kpi-icon-box" style="background: #f0f9ff; color: #0284c7; border: 1px solid #e0f2fe;">
            📋
        </div>
        <div class="kpi-meta-box">
            <div class="kpi-label">Test &amp; Kontrol Planı</div>
            <div class="kpi-value-row">
                <span class="kpi-number" style="color: #0f172a;">{{ $totalChecks }}</span>
                <span style="font-size: 11.5px; font-weight: 700; color: #64748b;">Toplam Kriter</span>
            </div>
            <div style="display: flex; gap: 6px; margin-top: 2px;">
                <span style="font-size: 10.5px; font-weight: 700; background: #fef3c7; color: #b45309; padding: 1px 6px; border-radius: 4px;">
                    ⏳ {{ $pendingChecks }} Bekliyor
                </span>
                <span style="font-size: 10.5px; font-weight: 700; background: #fee2e2; color: #b91c1c; padding: 1px 6px; border-radius: 4px;">
                    ✕ {{ $failedChecks }} Red
                </span>
            </div>
        </div>
    </div>

    <!-- Kart 3: Açık Uygunsuzluklar (NCR) -->
    <div class="quality-kpi-card" style="border-left: 4px solid {{ $openNcrs > 0 ? '#dc2626' : '#16a34a' }};">
        <div class="kpi-icon-box" style="background: {{ $openNcrs > 0 ? '#fef2f2' : '#f0fdf4' }}; color: {{ $openNcrs > 0 ? '#dc2626' : '#16a34a' }}; border: 1px solid {{ $openNcrs > 0 ? '#fee2e2' : '#dcfce7' }};">
            ⚠️
        </div>
        <div class="kpi-meta-box">
            <div class="kpi-label">Açık Uygunsuzluk &amp; DÖF (NCR)</div>
            <div class="kpi-value-row">
                <span class="kpi-number" style="color: {{ $openNcrs > 0 ? '#dc2626' : '#16a34a' }};">{{ $openNcrs }}</span>
                @if($criticalNcrs > 0)
                    <span style="font-size: 10.5px; font-weight: 800; background: #fee2e2; color: #991b1b; padding: 2px 6px; border-radius: 4px; border: 1px solid #fecaca;">
                        🚨 {{ $criticalNcrs }} Kritik
                    </span>
                @endif
            </div>
            <div class="kpi-subtext">Giderilmeyi bekleyen DÖF ({{ $resolvedNcrs }} Kapatıldı)</div>
        </div>
    </div>

</div>

<!-- ========================================================================= -->
<!-- 3. KOMPAKT YATAY FİLTRE VE ARAMA ARAÇ ÇUBUĞU -->
<!-- ========================================================================= -->
<div class="quality-filter-card">
    <form method="GET" action="{{ route('quality.index') }}" class="quality-filter-form">
        <input type="hidden" name="tab" value="{{ $activeTab }}">
        
        <!-- Proje Filtresi -->
        <div style="min-width: 240px; flex: 1;">
            <select name="project_id" class="q-filter-control" style="width: 100%; font-weight: 600;" onchange="this.form.submit()">
                <option value="">📁 Tüm Projeler (Konsolide)</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ $selectedProjectId == $p->id ? 'selected' : '' }}>
                        {{ $p->code }} - {{ $p->title }}
                    </option>
                @endforeach
            </select>
        </div>

        @if($activeTab === 'checks')
            <!-- Kontrol Türü -->
            <div style="min-width: 180px;">
                <select name="check_type" class="q-filter-control" style="width: 100%;" onchange="this.form.submit()">
                    <option value="">🔬 Tüm Kontrol Türleri</option>
                    @foreach(\App\Models\ProjectQuality::checkTypes() as $k => $label)
                        <option value="{{ $k }}" {{ $selectedCheckType === $k ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Durum -->
            <div style="min-width: 150px;">
                <select name="status" class="q-filter-control" style="width: 100%;" onchange="this.form.submit()">
                    <option value="">⚡ Tüm Durumlar</option>
                    <option value="pending" {{ $selectedStatus === 'pending' ? 'selected' : '' }}>⏳ Bekliyor</option>
                    <option value="passed" {{ $selectedStatus === 'passed' ? 'selected' : '' }}>✓ Başarılı</option>
                    <option value="conditional" {{ $selectedStatus === 'conditional' ? 'selected' : '' }}>⚠️ Şartlı Kabul</option>
                    <option value="failed" {{ $selectedStatus === 'failed' ? 'selected' : '' }}>✕ Reddedildi</option>
                    <option value="exempted" {{ $selectedStatus === 'exempted' ? 'selected' : '' }}>⊘ Muaf</option>
                </select>
            </div>
        @else
            <!-- Şiddet Derecesi -->
            <div style="min-width: 180px;">
                <select name="severity" class="q-filter-control" style="width: 100%;" onchange="this.form.submit()">
                    <option value="">🚨 Tüm Şiddetler</option>
                    <option value="critical" {{ $selectedSeverity === 'critical' ? 'selected' : '' }}>🚨 Kritik / Durdurucu</option>
                    <option value="major" {{ $selectedSeverity === 'major' ? 'selected' : '' }}>⚠️ Majör / Önemli</option>
                    <option value="minor" {{ $selectedSeverity === 'minor' ? 'selected' : '' }}>ℹ️ Minör / Kusur</option>
                </select>
            </div>

            <!-- NCR Durumu -->
            <div style="min-width: 150px;">
                <select name="ncr_status" class="q-filter-control" style="width: 100%;" onchange="this.form.submit()">
                    <option value="">⚙️ Tüm Durumlar</option>
                    <option value="open" {{ request('ncr_status') === 'open' ? 'selected' : '' }}>Açık</option>
                    <option value="in_progress" {{ request('ncr_status') === 'in_progress' ? 'selected' : '' }}>Aksiyon Alınıyor</option>
                    <option value="resolved" {{ request('ncr_status') === 'resolved' ? 'selected' : '' }}>Düzeltildi</option>
                    <option value="closed" {{ request('ncr_status') === 'closed' ? 'selected' : '' }}>Kapatıldı</option>
                </select>
            </div>
        @endif

        <!-- Arama Çubuğu -->
        <div style="min-width: 220px; flex: 1; display: flex; gap: 6px;">
            <input type="text" name="search" value="{{ $search }}" placeholder="Test, standart, kriter veya NCR ara..." 
                   class="q-filter-control" style="flex: 1;">
            <button type="submit" style="background: #0f172a; color: #ffffff; font-weight: 700; border-radius: 8px; padding: 0 16px; height: 38px; border: none; cursor: pointer;">
                Ara
            </button>
            @if($search || $selectedProjectId || $selectedCheckType || $selectedStatus || $selectedSeverity || request('ncr_status'))
                <a href="{{ route('quality.index', ['tab' => $activeTab]) }}" style="border-radius: 8px; height: 38px; display: inline-flex; align-items: center; justify-content: center; padding: 0 12px; background: #fee2e2; color: #b91c1c; text-decoration: none; font-weight: 800; border: 1px solid #fecaca;" title="Filtreleri Temizle">
                    ✕
                </a>
            @endif
        </div>
    </form>
</div>

<!-- ========================================================================= -->
<!-- 4. VERİ TABLOSU: KALİTE TESTLERİ VEYA UYGUNSUZLUK (NCR) LİSTESİ -->
<!-- ========================================================================= -->
@if($activeTab === 'checks')
    <div class="quality-table-container">
        <div class="quality-table-header">
            <div style="font-weight: 800; font-size: 14px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <span>🔬</span>
                <span>Kalite Kontrol Planı &amp; Test Listesi</span>
                <span style="background: #f1f5f9; color: #475569; font-weight: 700; border-radius: 6px; padding: 2px 8px; font-size: 11px;">
                    {{ $qualities->total() }} Kayıt
                </span>
            </div>
            <div style="font-size: 12px; color: #64748b;">
                Tolerans testleri, görsel muayeneler ve faz sonu kabul kapıları
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="quality-data-table">
                <thead>
                    <tr>
                        <th style="padding-left: 20px;">Proje &amp; Faz</th>
                        <th>Kalite Kontrolü &amp; Standart</th>
                        <th>Kabul Kriteri &amp; Hedef</th>
                        <th>Ölçülen / Fiili Değer</th>
                        <th style="text-align: center;">Durum</th>
                        <th>Denetçi / Onaylayan</th>
                        <th>Sertifika / Rapor</th>
                        <th style="text-align: right; padding-right: 20px;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($qualities as $q)
                        @php
                            $badge = $q->status_badge;
                        @endphp
                        <tr>
                            <td style="padding-left: 20px;">
                                <div style="font-weight: 800; color: #0284c7; font-size: 12px;">
                                    {{ $q->project->code ?? 'PRJ' }}
                                </div>
                                <div style="font-size: 11.5px; color: #0f172a; max-width: 170px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $q->project->title ?? '' }}">
                                    {{ $q->project->title ?? '-' }}
                                </div>
                                @if($q->phase)
                                    <div style="font-size: 10.5px; color: #94a3b8; margin-top: 2px;">
                                        🚪 Faz: {{ $q->phase->name }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                                    <span style="background: #e0f2fe; color: #0369a1; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px;">
                                        {{ $q->check_type_label }}
                                    </span>
                                    @if($q->standard_code)
                                        <span style="background: #f1f5f9; color: #475569; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; font-family: monospace;">
                                            {{ $q->standard_code }}
                                        </span>
                                    @endif
                                </div>
                                <div style="font-weight: 700; color: #0f172a; font-size: 13px;">
                                    {{ $q->title }}
                                </div>
                                @if($q->notes)
                                    <div style="font-size: 11px; color: #64748b; margin-top: 2px; font-style: italic;">
                                        {{ Str::limit($q->notes, 60) }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div style="font-size: 12px; color: #334155; line-height: 1.35; max-width: 230px;">
                                    {{ $q->acceptance_criteria ?: '-' }}
                                </div>
                            </td>
                            <td>
                                @if($q->measured_value)
                                    <div style="font-family: 'JetBrains Mono', monospace; font-weight: 800; font-size: 12.5px; color: {{ $q->status === 'passed' ? '#15803d' : ($q->status === 'failed' ? '#b91c1c' : '#b45309') }};">
                                        {{ $q->measured_value }}
                                    </div>
                                @else
                                    <span style="color: #94a3b8; font-size: 11px;">Ölçüm Girilmedi</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <span class="badge-pill" style="background: {{ $badge['bg'] }}; color: {{ $badge['color'] }};">
                                    <span>{{ $badge['icon'] }}</span>
                                    <span>{{ $badge['label'] }}</span>
                                </span>
                            </td>
                            <td>
                                @if($q->checker)
                                    <div style="font-weight: 600; color: #0f172a; font-size: 11.5px;">
                                        👤 {{ $q->checker->name }}
                                    </div>
                                    <div style="font-size: 10px; color: #94a3b8;">
                                        {{ $q->checked_at ? $q->checked_at->format('d.m.Y H:i') : '' }}
                                    </div>
                                @else
                                    <span style="color: #94a3b8; font-size: 11px;">Atanmadı</span>
                                @endif
                            </td>
                            <td>
                                @if($q->certificate_file)
                                    <a href="{{ asset($q->certificate_file) }}" target="_blank" 
                                       style="font-size: 11px; padding: 4px 8px; border-radius: 6px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; border: 1px solid #bfdbfe; background: #eff6ff; color: #1d4ed8; text-decoration: none;">
                                        📄 Sertifika ↗
                                    </a>
                                @else
                                    <span style="color: #cbd5e1; font-size: 11px;">-</span>
                                @endif
                            </td>
                            <td style="text-align: right; padding-right: 20px;">
                                @if(!auth()->user()->isYonetim())
                                    <div style="display: flex; align-items: center; justify-content: flex-end; gap: 6px;">
                                        <button type="button" onclick="openEvaluateModal({{ json_encode($q) }})" 
                                                style="background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; font-weight: 700; font-size: 11.5px; border-radius: 6px; padding: 5px 10px; cursor: pointer;" title="Test Sonucunu Değerlendir">
                                            ⚡ Sonuçlandır
                                        </button>
                                        <form method="POST" action="{{ route('quality.destroy', $q->id) }}" onsubmit="return confirm('Bu kalite kontrol kriterini silmek istediğinize emin misiniz?')" style="display: inline; margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="background: none; border: none; color: #ef4444; font-size: 15px; cursor: pointer; padding: 2px 4px;" title="Sil">
                                                ✕
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span style="color: #94a3b8; font-size: 11px; font-weight: 600;">👁️ Salt Okunur</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 50px 20px; color: #94a3b8;">
                                <div style="font-size: 36px; margin-bottom: 8px;">🔬</div>
                                <div style="font-weight: 700; font-size: 14px; color: #475569;">Kayıtlı Kalite Kontrolü Bulunamadı</div>
                                <p style="font-size: 12px; margin-top: 4px; color: #64748b;">Yukarıdaki "Yeni Kalite Kriteri Ekle" butonu ile projenize test planı tanımlayabilirsiniz.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($qualities->hasPages())
            <div style="padding: 12px 20px; border-top: 1px solid #f1f5f9; background: #ffffff;">
                {{ $qualities->withQueryString()->links() }}
            </div>
        @endif
    </div>

@else
    <!-- UYGUNSUZLUK (NCR / DÖF) TABLOSU -->
    <div class="quality-table-container">
        <div class="quality-table-header">
            <div style="font-weight: 800; font-size: 14px; color: #991b1b; display: flex; align-items: center; gap: 8px;">
                <span>🚨</span>
                <span>Kalite Uygunsuzlukları &amp; Düzeltici Faaliyetler (NCR / DÖF)</span>
                <span style="background: #fee2e2; color: #b91c1c; font-weight: 700; border-radius: 6px; padding: 2px 8px; font-size: 11px;">
                    {{ $nonConformances->total() }} Kayıt
                </span>
            </div>
            <div style="font-size: 12px; color: #64748b;">
                Saha kusurları, tolerans aşımları ve kök neden aksiyon planları
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="quality-data-table">
                <thead>
                    <tr>
                        <th style="padding-left: 20px;">NCR Kodu &amp; Proje</th>
                        <th>Kusur / Uygunsuzluk Tanımı</th>
                        <th style="text-align: center;">Şiddet</th>
                        <th>DÖF / Önleyici Aksiyon Planı</th>
                        <th>Sorumlu &amp; Termin</th>
                        <th style="text-align: center;">Durum</th>
                        <th style="text-align: right; padding-right: 20px;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($nonConformances as $ncr)
                        @php
                            $sev = $ncr->severity_badge;
                            $st = $ncr->status_badge;
                            $isOverdue = $ncr->due_date && $ncr->due_date->isPast() && in_array($ncr->status, ['open', 'in_progress']);
                        @endphp
                        <tr>
                            <td style="padding-left: 20px;">
                                <div style="font-weight: 900; font-family: 'JetBrains Mono', monospace; color: #dc2626; font-size: 12.5px;">
                                    {{ $ncr->ncr_code }}
                                </div>
                                <div style="font-weight: 700; color: #0284c7; font-size: 11px;">
                                    {{ $ncr->project->code ?? 'PRJ' }}
                                </div>
                                <div style="font-size: 10.5px; color: #64748b;">
                                    {{ Str::limit($ncr->project->title ?? '', 22) }}
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #0f172a; font-size: 13px;">
                                    {{ $ncr->title }}
                                </div>
                                @if($ncr->description)
                                    <div style="font-size: 11.5px; color: #475569; margin-top: 2px;">
                                        {{ Str::limit($ncr->description, 80) }}
                                    </div>
                                @endif
                                @if($ncr->quality)
                                    <div style="font-size: 10px; color: #0284c7; margin-top: 2px;">
                                        🔗 Bağlı Test: {{ $ncr->quality->title }}
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <span class="badge-pill" style="background: {{ $sev['bg'] }}; color: {{ $sev['color'] }}; font-weight: 800;">
                                    {{ $sev['badge'] }}
                                </span>
                            </td>
                            <td>
                                @if($ncr->corrective_action)
                                    <div style="font-size: 12px; color: #0f172a; line-height: 1.35; max-width: 250px;">
                                        {{ $ncr->corrective_action }}
                                    </div>
                                @else
                                    <span style="color: #94a3b8; font-size: 11px; font-style: italic;">Aksiyon Planı Belirlenmedi</span>
                                @endif
                                @if($ncr->resolution_notes)
                                    <div style="font-size: 11px; color: #16a34a; font-weight: 600; margin-top: 3px;">
                                        ✓ Çözüm Notu: {{ Str::limit($ncr->resolution_notes, 60) }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #0f172a; font-size: 12px;">
                                    👤 {{ $ncr->assignee->name ?? 'Atanmadı' }}
                                </div>
                                @if($ncr->due_date)
                                    <div style="font-size: 11px; font-weight: 700; color: {{ $isOverdue ? '#dc2626' : '#64748b' }}; margin-top: 2px;">
                                        📅 {{ $ncr->due_date->format('d.m.Y') }}
                                        @if($isOverdue)
                                            <span style="background: #fee2e2; color: #b91c1c; font-size: 9.5px; padding: 1px 4px; border-radius: 4px; font-weight: 800;">GECİKTİ</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <span class="badge-pill" style="background: {{ $st['bg'] }}; color: {{ $st['color'] }};">
                                    {{ $st['label'] }}
                                </span>
                            </td>
                            <td style="text-align: right; padding-right: 20px;">
                                @if(!auth()->user()->isYonetim())
                                    <div style="display: flex; align-items: center; justify-content: flex-end; gap: 6px;">
                                        <button type="button" onclick="openUpdateNcrModal({{ json_encode($ncr) }})" 
                                                style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; font-weight: 700; font-size: 11.5px; border-radius: 6px; padding: 5px 10px; cursor: pointer;">
                                            ⚙️ Güncelle / Kapat
                                        </button>
                                        <form method="POST" action="{{ route('quality.ncr.destroy', $ncr->id) }}" onsubmit="return confirm('Bu uygunsuzluk kaydını silmek istediğinize emin misiniz?')" style="display: inline; margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="background: none; border: none; color: #ef4444; font-size: 15px; cursor: pointer; padding: 2px 4px;" title="Sil">
                                                ✕
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span style="color: #94a3b8; font-size: 11px; font-weight: 600;">👁️ Salt Okunur</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 50px 20px; color: #94a3b8;">
                                <div style="font-size: 36px; margin-bottom: 8px;">🎉</div>
                                <div style="font-weight: 700; font-size: 14px; color: #15803d;">Açık Kalite Uygunsuzluğu Bulunmuyor</div>
                                <p style="font-size: 12px; margin-top: 4px; color: #64748b;">Projeniz tüm kabul ve kalite testlerini başarıyla geçmektedir.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($nonConformances->hasPages())
            <div style="padding: 12px 20px; border-top: 1px solid #f1f5f9; background: #ffffff;">
                {{ $nonConformances->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endif

<!-- ========================================================================= -->
<!-- 5. KURUMSAL NATIVE MODALLAR (OVERLAY ARŞİTEKTÜRÜ - BOOTSTRAP BAĞIMSIZ) -->
<!-- ========================================================================= -->

<!-- MODAL 1: YENİ KALİTE KRİTERİ & TEST EKLE -->
<div id="modalNewQuality" class="native-modal-backdrop" style="display: none;" onclick="if(event.target === this) closeNativeModal('modalNewQuality')">
    <div class="native-modal-box">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff; padding: 16px 22px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
            <div style="font-weight: 800; font-size: 15px; display: flex; align-items: center; gap: 8px;">
                <span>🔬</span> Yeni Kalite Kontrolü &amp; Kabul Kriteri Tanımla
            </div>
            <button type="button" onclick="closeNativeModal('modalNewQuality')" style="background: none; border: none; color: #ffffff; font-size: 22px; cursor: pointer; line-height: 1; padding: 0;">&times;</button>
        </div>
        <!-- Body Form -->
        <form method="POST" action="{{ route('quality.store') }}" enctype="multipart/form-data" style="margin: 0; display: flex; flex-direction: column; overflow: hidden;">
            @csrf
            <div style="padding: 22px; overflow-y: auto; max-height: calc(90vh - 130px); font-size: 13px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="font-weight: 700; color: #0f172a; display: block; margin-bottom: 4px;">İlgili Proje *</label>
                        <select name="project_id" class="q-filter-control" required style="width: 100%;">
                            <option value="">-- Proje Seçiniz --</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ $selectedProjectId == $p->id ? 'selected' : '' }}>
                                    {{ $p->code }} - {{ $p->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="font-weight: 700; color: #0f172a; display: block; margin-bottom: 4px;">Kontrol / Kabul Türü *</label>
                        <select name="check_type" class="q-filter-control" required style="width: 100%;">
                            @foreach(\App\Models\ProjectQuality::checkTypes() as $k => $label)
                                <option value="{{ $k }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="font-weight: 700; color: #0f172a; display: block; margin-bottom: 4px;">Kalite Kontrol / Test Başlığı *</label>
                        <input type="text" name="title" class="q-filter-control" placeholder="Örn: Hat 4 Hidrolik Basınç Testi &amp; Sızdırmazlık Doğrulaması" required style="width: 100%;">
                    </div>

                    <div>
                        <label style="font-weight: 700; color: #0f172a; display: block; margin-bottom: 4px;">Standart / Şartname No</label>
                        <input type="text" name="standard_code" class="q-filter-control" placeholder="Örn: ISO 9001 / DIN 18800" style="width: 100%;">
                    </div>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="font-weight: 700; color: #0f172a; display: block; margin-bottom: 4px;">Kabul Kriteri / Hedef Değer &amp; Tolerans *</label>
                    <textarea name="acceptance_criteria" rows="2" class="q-filter-control" placeholder="Örn: 15 Bar basınç altında 30 dakika boyunca sıfır sızıntı; maksimum 0.2 bar basınç düşümü toleransı." style="width: 100%; height: auto; padding: 8px 12px;"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div>
                        <label style="font-weight: 700; color: #0f172a; display: block; margin-bottom: 4px;">Testi Yapacak Uzman / Denetçi</label>
                        <select name="checked_by" class="q-filter-control" style="width: 100%;">
                            <option value="">-- Denetçi Seçiniz --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->title ?: 'Personel' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="font-weight: 700; color: #0f172a; display: block; margin-bottom: 4px;">Sertifika / Şartname Dosyası (Opsiyonel)</label>
                        <input type="file" name="certificate_file" class="q-filter-control" style="width: 100%; padding: 4px;">
                    </div>
                </div>
            </div>
            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 22px; display: flex; justify-content: flex-end; gap: 10px; flex-shrink: 0;">
                <button type="button" onclick="closeNativeModal('modalNewQuality')" style="background: #e2e8f0; color: #475569; font-weight: 600; border-radius: 8px; padding: 8px 16px; border: none; cursor: pointer;">İptal</button>
                <button type="submit" style="background: #0284c7; color: #ffffff; font-weight: 700; border-radius: 8px; padding: 8px 20px; border: none; cursor: pointer;">✓ Kriteri Kaydet</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: TEST SONUCUNU GİR & DEĞERLENDİR -->
<div id="modalEvaluateQuality" class="native-modal-backdrop" style="display: none;" onclick="if(event.target === this) closeNativeModal('modalEvaluateQuality')">
    <div class="native-modal-box" style="width: 560px;">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: #ffffff; padding: 16px 22px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
            <div style="font-weight: 800; font-size: 15px; display: flex; align-items: center; gap: 8px;">
                <span>⚡</span> Test / Kalite Kontrol Değerlendirmesi
            </div>
            <button type="button" onclick="closeNativeModal('modalEvaluateQuality')" style="background: none; border: none; color: #ffffff; font-size: 22px; cursor: pointer; line-height: 1; padding: 0;">&times;</button>
        </div>
        <!-- Body Form -->
        <form method="POST" id="formEvaluateQuality" enctype="multipart/form-data" style="margin: 0; display: flex; flex-direction: column; overflow: hidden;">
            @csrf
            <div style="padding: 22px; overflow-y: auto; max-height: calc(90vh - 130px); font-size: 13px;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 14px; margin-bottom: 14px;">
                    <div style="font-weight: 800; color: #0f172a; font-size: 13.5px;" id="evalTitleText">Test Başlığı</div>
                    <div style="font-size: 12px; color: #64748b; margin-top: 2px;" id="evalCriteriaText">Kriter</div>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="font-weight: 700; color: #0f172a; display: block; margin-bottom: 4px;">Değerlendirme Sonucu *</label>
                    <select name="status" id="evalStatusSelect" class="q-filter-control" required style="width: 100%; font-weight: 700;" onchange="toggleAutoNcrSection(this.value)">
                        <option value="passed">✓ Başarılı / Şartnameyi Sağladı (Passed)</option>
                        <option value="conditional">⚠️ Şartlı Kabul / İstisnalı Onay (Conditional)</option>
                        <option value="failed">✕ Başarısız / Reddedildi (Failed)</option>
                        <option value="exempted">⊘ Muaf Tutuldu (Exempted)</option>
                        <option value="pending">⏳ Bekliyor / Yeniden Test</option>
                    </select>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="font-weight: 700; color: #0f172a; display: block; margin-bottom: 4px;">Ölçülen / Fiili Değer</label>
                    <input type="text" name="measured_value" id="evalMeasuredValue" class="q-filter-control" placeholder="Örn: 15.1 Bar veya 0.8mm Sapma" style="width: 100%; font-family: 'JetBrains Mono', monospace;">
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="font-weight: 700; color: #0f172a; display: block; margin-bottom: 4px;">Test / Muayene Notu</label>
                    <textarea name="notes" id="evalNotes" rows="2" class="q-filter-control" placeholder="Ölçüm kalibrasyonu, ortam sıcaklığı veya gözlem notları..." style="width: 100%; height: auto; padding: 8px 12px;"></textarea>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="font-weight: 700; color: #0f172a; display: block; margin-bottom: 4px;">Test Raporu / Kalibrasyon Belgesi (PDF/Görsel)</label>
                    <input type="file" name="certificate_file" class="q-filter-control" style="width: 100%; padding: 4px;">
                </div>

                <!-- Otomatik NCR Açma Alanı (Başarısız seçildiğinde) -->
                <div id="autoNcrBox" style="display: none; background: #fef2f2; border: 1.5px dashed #fca5a5; border-radius: 10px; padding: 14px; margin-top: 14px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <input type="checkbox" name="auto_create_ncr" value="1" id="autoNcrCheck" checked>
                        <label for="autoNcrCheck" style="font-size: 12px; font-weight: 800; color: #b91c1c; cursor: pointer; margin: 0;">
                            🚨 Başarısız test için otomatik Uygunsuzluk (NCR) aç
                        </label>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #991b1b; display: block; margin-bottom: 2px;">Şiddet Seviyesi:</label>
                            <select name="ncr_severity" class="q-filter-control" style="width: 100%; height: 32px; font-size: 11.5px;">
                                <option value="major">⚠️ Majör / Önemli</option>
                                <option value="critical">🚨 Kritik / Durdurucu</option>
                                <option value="minor">ℹ️ Minör / Kusur</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 11px; font-weight: 700; color: #991b1b; display: block; margin-bottom: 2px;">DÖF Sorumlusu:</label>
                            <select name="ncr_assignee" class="q-filter-control" style="width: 100%; height: 32px; font-size: 11.5px;">
                                <option value="">-- Sorumlu Ata --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 22px; display: flex; justify-content: flex-end; gap: 10px; flex-shrink: 0;">
                <button type="button" onclick="closeNativeModal('modalEvaluateQuality')" style="background: #e2e8f0; color: #475569; font-weight: 600; border-radius: 8px; padding: 8px 16px; border: none; cursor: pointer;">İptal</button>
                <button type="submit" style="background: #16a34a; color: #ffffff; font-weight: 700; border-radius: 8px; padding: 8px 20px; border: none; cursor: pointer;">✓ Sonucu Kaydet</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 3: YENİ UYGUNSUZLUK (NCR / DÖF) AÇ -->
<div id="modalNewNcr" class="native-modal-backdrop" style="display: none;" onclick="if(event.target === this) closeNativeModal('modalNewNcr')">
    <div class="native-modal-box">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: #ffffff; padding: 16px 22px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
            <div style="font-weight: 800; font-size: 15px; display: flex; align-items: center; gap: 8px;">
                <span>🚨</span> Yeni Kalite Uygunsuzluğu / Kusur Raporu (NCR) Aç
            </div>
            <button type="button" onclick="closeNativeModal('modalNewNcr')" style="background: none; border: none; color: #ffffff; font-size: 22px; cursor: pointer; line-height: 1; padding: 0;">&times;</button>
        </div>
        <!-- Body Form -->
        <form method="POST" action="{{ route('quality.ncr.store') }}" enctype="multipart/form-data" style="margin: 0; display: flex; flex-direction: column; overflow: hidden;">
            @csrf
            <div style="padding: 22px; overflow-y: auto; max-height: calc(90vh - 130px); font-size: 13px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="font-weight: 700; color: #0f172a; display: block; margin-bottom: 4px;">İlgili Proje *</label>
                        <select name="project_id" class="q-filter-control" required style="width: 100%;">
                            <option value="">-- Proje Seçiniz --</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ $selectedProjectId == $p->id ? 'selected' : '' }}>
                                    {{ $p->code }} - {{ $p->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="font-weight: 700; color: #0f172a; display: block; margin-bottom: 4px;">Şiddet Derecesi *</label>
                        <select name="severity" class="q-filter-control" required style="width: 100%; font-weight: 700;">
                            <option value="critical">🚨 Kritik (Fazı veya Projeyi Durdurucu Hata)</option>
                            <option value="major" selected>⚠️ Majör (Önemli Kusur / Fonksiyonel Hata)</option>
                            <option value="minor">ℹ️ Minör (Görsel Kusur / Çapak / Kozmetik Hata)</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="font-weight: 700; color: #0f172a; display: block; margin-bottom: 4px;">Uygunsuzluk / Hata Başlığı *</label>
                    <input type="text" name="title" class="q-filter-control" placeholder="Örn: Enjeksiyon Kalıp Sıcak Yolluk Termokupl Hatası" required style="width: 100%;">
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="font-weight: 700; color: #0f172a; display: block; margin-bottom: 4px;">Tespit Edilen Kusur &amp; Kök Neden Açıklaması</label>
                    <textarea name="description" rows="2" class="q-filter-control" placeholder="Sahada veya üretimde ne tespit edildi? Şartnameden ne kadar sapıldı?" style="width: 100%; height: auto; padding: 8px 12px;"></textarea>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="font-weight: 700; color: #0f172a; display: block; margin-bottom: 4px;">DÖF (Düzeltici ve Önleyici Faaliyet Önerisi)</label>
                    <textarea name="corrective_action" rows="2" class="q-filter-control" placeholder="Bu hatayı gidermek ve tekrarlanmasını önlemek için ne yapılacak?" style="width: 100%; height: auto; padding: 8px 12px;"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="font-weight: 700; color: #0f172a; display: block; margin-bottom: 4px;">Düzeltme Sorumlusu (Assignee)</label>
                        <select name="assigned_to" class="q-filter-control" style="width: 100%;">
                            <option value="">-- Sorumlu Personel --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->title ?: 'Ekip' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="font-weight: 700; color: #0f172a; display: block; margin-bottom: 4px;">Düzeltme Termini (Due Date)</label>
                        <input type="date" name="due_date" class="q-filter-control" style="width: 100%;">
                    </div>
                </div>

                <div>
                    <label style="font-weight: 700; color: #0f172a; display: block; margin-bottom: 4px;">Kusur Fotoğrafı / Kanıt Dosyası</label>
                    <input type="file" name="evidence_file" class="q-filter-control" style="width: 100%; padding: 4px;">
                </div>
            </div>
            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 22px; display: flex; justify-content: flex-end; gap: 10px; flex-shrink: 0;">
                <button type="button" onclick="closeNativeModal('modalNewNcr')" style="background: #e2e8f0; color: #475569; font-weight: 600; border-radius: 8px; padding: 8px 16px; border: none; cursor: pointer;">İptal</button>
                <button type="submit" style="background: #dc2626; color: #ffffff; font-weight: 700; border-radius: 8px; padding: 8px 20px; border: none; cursor: pointer;">🚨 NCR Kaydını Başlat</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 4: NCR DURUM GÜNCELLE / KAPAT -->
<div id="modalUpdateNcr" class="native-modal-backdrop" style="display: none;" onclick="if(event.target === this) closeNativeModal('modalUpdateNcr')">
    <div class="native-modal-box" style="width: 520px;">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #ffffff; padding: 16px 22px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
            <div style="font-weight: 800; font-size: 15px; display: flex; align-items: center; gap: 8px;">
                <span>⚙️</span> NCR Durumunu Güncelle &amp; Kapat
            </div>
            <button type="button" onclick="closeNativeModal('modalUpdateNcr')" style="background: none; border: none; color: #ffffff; font-size: 22px; cursor: pointer; line-height: 1; padding: 0;">&times;</button>
        </div>
        <!-- Body Form -->
        <form method="POST" id="formUpdateNcr" style="margin: 0; display: flex; flex-direction: column; overflow: hidden;">
            @csrf
            <div style="padding: 22px; overflow-y: auto; max-height: calc(90vh - 130px); font-size: 13px;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 14px; margin-bottom: 14px;">
                    <div style="font-weight: 900; color: #dc2626; font-family: 'JetBrains Mono', monospace; font-size: 13.5px;" id="ncrModalCode">NCR-2026-0001</div>
                    <div style="font-weight: 700; color: #0f172a; margin-top: 2px;" id="ncrModalTitle">Başlık</div>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="font-weight: 700; color: #0f172a; display: block; margin-bottom: 4px;">Yeni Durum *</label>
                    <select name="status" id="ncrModalStatus" class="q-filter-control" required style="width: 100%; font-weight: 700;">
                        <option value="open">Açık / Tespit Edildi</option>
                        <option value="in_progress">Aksiyon Alınıyor / Düzeltme Devam Ediyor</option>
                        <option value="resolved">Düzeltildi / Doğrulama Bekliyor</option>
                        <option value="closed">✓ Kapatıldı / Kalite Onaylı</option>
                    </select>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="font-weight: 700; color: #0f172a; display: block; margin-bottom: 4px;">Alınan Aksiyon &amp; Çözüm Notu</label>
                    <textarea name="resolution_notes" id="ncrModalNotes" rows="3" class="q-filter-control" placeholder="Düzeltme nasıl yapıldı? Tekrar test sonucu ne oldu?" style="width: 100%; height: auto; padding: 8px 12px;"></textarea>
                </div>
            </div>
            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 22px; display: flex; justify-content: flex-end; gap: 10px; flex-shrink: 0;">
                <button type="button" onclick="closeNativeModal('modalUpdateNcr')" style="background: #e2e8f0; color: #475569; font-weight: 600; border-radius: 8px; padding: 8px 16px; border: none; cursor: pointer;">İptal</button>
                <button type="submit" style="background: #0284c7; color: #ffffff; font-weight: 700; border-radius: 8px; padding: 8px 20px; border: none; cursor: pointer;">✓ Güncelle</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 6. NATIVE JAVASCRIPT MODAL YÖNETİCİLERİ -->
<!-- ========================================================================= -->
<script>
function openNativeModal(modalId) {
    const el = document.getElementById(modalId);
    if (el) {
        el.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeNativeModal(modalId) {
    const el = document.getElementById(modalId);
    if (el) {
        el.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// ESC tuşu ile açık modalları kapatma
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        ['modalNewQuality', 'modalEvaluateQuality', 'modalNewNcr', 'modalUpdateNcr'].forEach(function(id) {
            closeNativeModal(id);
        });
    }
});

function openEvaluateModal(quality) {
    document.getElementById('evalTitleText').textContent = quality.title;
    document.getElementById('evalCriteriaText').textContent = 'Kriter: ' + (quality.acceptance_criteria || '-');
    document.getElementById('evalStatusSelect').value = quality.status || 'passed';
    document.getElementById('evalMeasuredValue').value = quality.measured_value || '';
    document.getElementById('evalNotes').value = quality.notes || '';
    document.getElementById('formEvaluateQuality').action = '{{ url("/kalite") }}/' + quality.id + '/evaluate';
    
    toggleAutoNcrSection(quality.status);
    openNativeModal('modalEvaluateQuality');
}

function toggleAutoNcrSection(status) {
    const box = document.getElementById('autoNcrBox');
    if (box) {
        box.style.display = (status === 'failed') ? 'block' : 'none';
    }
}

function openUpdateNcrModal(ncr) {
    document.getElementById('ncrModalCode').textContent = ncr.ncr_code;
    document.getElementById('ncrModalTitle').textContent = ncr.title;
    document.getElementById('ncrModalStatus').value = ncr.status;
    document.getElementById('ncrModalNotes').value = ncr.resolution_notes || '';
    document.getElementById('formUpdateNcr').action = '{{ url("/kalite/ncr") }}/' + ncr.id;

    openNativeModal('modalUpdateNcr');
}
</script>
@endsection