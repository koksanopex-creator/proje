@extends('layouts.app')

@section('title', 'Kalite & Kabul Yönetimi')
@section('page_title', 'Kalite Güvence, Test & Kabul Yönetimi')
@section('page_subtitle', 'Endüstriyel test standartları, kabul kapıları (Stage-Gates), tolerans ölçümleri ve uygunsuzluk (NCR/DÖF) takibi')

@section('content')
<style>
.quality-card {
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    transition: all 0.2s ease;
}
.quality-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    border-color: #cbd5e1;
}
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 700;
}
.severity-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2.5px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 800;
}
.tab-switch-btn {
    padding: 8px 18px;
    font-size: 13px;
    font-weight: 700;
    border-radius: 8px;
    border: 1.5px solid #e2e8f0;
    background: #ffffff;
    color: #64748b;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    transition: all 0.15s ease;
    text-decoration: none;
}
.tab-switch-btn.active {
    background: #0284c7;
    border-color: #0284c7;
    color: #ffffff;
    box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);
}
.tab-switch-btn:hover:not(.active) {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #0f172a;
}
</style>

<!-- ÜST AKSİYON VE BİLGİ BANDI -->
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div class="d-flex align-items-center gap-2">
        <a href="?tab=checks{{ $selectedProjectId ? '&project_id=' . $selectedProjectId : '' }}" 
           class="tab-switch-btn {{ $activeTab === 'checks' ? 'active' : '' }}">
            <span>🔬</span> Kalite Testleri &amp; Kabul Kapıları
            <span class="badge" style="background: {{ $activeTab === 'checks' ? 'rgba(255,255,255,0.25)' : '#f1f5f9' }}; color: {{ $activeTab === 'checks' ? '#ffffff' : '#475569' }}; border-radius: 9999px; font-size: 11px;">
                {{ $totalChecks }}
            </span>
        </a>
        <a href="?tab=ncrs{{ $selectedProjectId ? '&project_id=' . $selectedProjectId : '' }}" 
           class="tab-switch-btn {{ $activeTab === 'ncrs' ? 'active' : '' }}">
            <span>🚨</span> Uygunsuzluk &amp; Hata Panosu (NCR / DÖF)
            <span class="badge" style="background: {{ $activeTab === 'ncrs' ? 'rgba(255,255,255,0.25)' : '#fee2e2' }}; color: {{ $activeTab === 'ncrs' ? '#ffffff' : '#b91c1c' }}; border-radius: 9999px; font-size: 11px;">
                {{ $openNcrs }} Açık
            </span>
        </a>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap">
        <button type="button" class="btn btn-sm" onclick="openNewQualityModal()" 
                style="background: #0284c7; color: #ffffff; font-weight: 700; border-radius: 8px; padding: 7px 14px; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.2);">
            + Yeni Kalite Kriteri Ekle
        </button>
        <button type="button" class="btn btn-sm" onclick="openNewNcrModal()" 
                style="background: #dc2626; color: #ffffff; font-weight: 700; border-radius: 8px; padding: 7px 14px; box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);">
            🚨 Yeni Uygunsuzluk (NCR) Aç
        </button>
    </div>
</div>

<!-- KURUMSAL KALİTE GÖSTERGE KARTLARI (KPI) -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 16px; margin-bottom: 24px;">
    
    <!-- Kart 1: Kalite Başarı Oranı -->
    <div class="quality-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span style="font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase;">🎯 Kalite Başarı Oranı</span>
            <span style="font-size: 18px;">🏆</span>
        </div>
        <div class="d-flex align-items-baseline gap-2 mb-1">
            <span style="font-size: 26px; font-weight: 900; color: {{ $qualityRate >= 85 ? '#16a34a' : ($qualityRate >= 65 ? '#d97706' : '#dc2626') }}; font-family: 'JetBrains Mono', monospace;">
                %{{ $qualityRate }}
            </span>
            <span style="font-size: 12px; font-weight: 600; color: #64748b;">
                ({{ $passedChecks }} / {{ $totalChecks }} Onaylı)
            </span>
        </div>
        <div class="progress" style="height: 6px; border-radius: 999px; background: #f1f5f9;">
            <div class="progress-bar" style="width: {{ $qualityRate }}%; background: {{ $qualityRate >= 85 ? '#16a34a' : ($qualityRate >= 65 ? '#d97706' : '#dc2626') }}; border-radius: 999px;"></div>
        </div>
        <div style="font-size: 11px; color: #94a3b8; margin-top: 6px;">
            Geçti ve Şartlı Kabul edilen testler
        </div>
    </div>

    <!-- Kart 2: Toplam Kontrol & Bekleyen Testler -->
    <div class="quality-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span style="font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase;">🔬 Test &amp; Kontrol Planı</span>
            <span style="font-size: 18px;">📋</span>
        </div>
        <div style="font-size: 26px; font-weight: 900; color: #0f172a; font-family: 'JetBrains Mono', monospace; margin-bottom: 2px;">
            {{ $totalChecks }}
        </div>
        <div class="d-flex align-items-center gap-2" style="font-size: 11.5px; color: #64748b;">
            <span class="status-pill" style="background: #fef3c7; color: #b45309; padding: 1px 7px;">
                ⏳ {{ $pendingChecks }} Bekliyor
            </span>
            <span class="status-pill" style="background: #fee2e2; color: #b91c1c; padding: 1px 7px;">
                ✕ {{ $failedChecks }} Reddedildi
            </span>
        </div>
    </div>

    <!-- Kart 3: Açık Uygunsuzluklar (NCR) -->
    <div class="quality-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span style="font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase;">🚨 Açık Uygunsuzluk (NCR)</span>
            <span style="font-size: 18px;">⚠️</span>
        </div>
        <div class="d-flex align-items-baseline gap-2 mb-1">
            <span style="font-size: 26px; font-weight: 900; color: {{ $openNcrs > 0 ? '#dc2626' : '#16a34a' }}; font-family: 'JetBrains Mono', monospace;">
                {{ $openNcrs }}
            </span>
            @if($criticalNcrs > 0)
                <span class="severity-pill" style="background: #fee2e2; color: #991b1b;">
                    🚨 {{ $criticalNcrs }} Kritik
                </span>
            @endif
        </div>
        <div style="font-size: 11px; color: #64748b;">
            Giderilmeyi bekleyen DÖF ve kusur sayısı ({{ $resolvedNcrs }} Kapatıldı)
        </div>
    </div>

    <!-- Kart 4: Kalite Standartları ve Sertifikasyon -->
    <div class="quality-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span style="font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase;">🏭 Fabrika &amp; Saha Kabul</span>
            <span style="font-size: 18px;">🛡️</span>
        </div>
        <div style="font-size: 14px; font-weight: 800; color: #0f172a; margin-top: 4px; margin-bottom: 4px;">
            Köksan QMS &amp; ISO Standartları
        </div>
        <div style="font-size: 11px; color: #64748b; line-height: 1.4;">
            ISO 9001, DIN, TSE ve EHS standartlarına tam uyumlu Stage-Gate kalite kontrolü
        </div>
    </div>

</div>

<!-- FİLTRE VE ARAMA KUTUSU -->
<div class="card mb-4" style="border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff;">
    <div class="card-body" style="padding: 14px 20px;">
        <form method="GET" action="{{ route('quality.index') }}" class="row g-2 align-items-center">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            
            <div class="col-md-4">
                <label style="font-size: 11px; font-weight: 700; color: #64748b; display: block; margin-bottom: 3px;">📁 Proje Filtresi:</label>
                <select name="project_id" class="form-control form-control-sm" style="border-radius: 7px; border: 1.5px solid #cbd5e1; font-weight: 600;" onchange="this.form.submit()">
                    <option value="">-- Tüm Projeler (Konsolide) --</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ $selectedProjectId == $p->id ? 'selected' : '' }}>
                            {{ $p->code }} - {{ $p->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if($activeTab === 'checks')
                <div class="col-md-3">
                    <label style="font-size: 11px; font-weight: 700; color: #64748b; display: block; margin-bottom: 3px;">🔬 Kontrol Türü:</label>
                    <select name="check_type" class="form-control form-control-sm" style="border-radius: 7px; border: 1.5px solid #cbd5e1;" onchange="this.form.submit()">
                        <option value="">-- Tüm Kontrol Türleri --</option>
                        @foreach(\App\Models\ProjectQuality::checkTypes() as $k => $label)
                            <option value="{{ $k }}" {{ $selectedCheckType === $k ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label style="font-size: 11px; font-weight: 700; color: #64748b; display: block; margin-bottom: 3px;">Durum:</label>
                    <select name="status" class="form-control form-control-sm" style="border-radius: 7px; border: 1.5px solid #cbd5e1;" onchange="this.form.submit()">
                        <option value="">-- Tümü --</option>
                        <option value="pending" {{ $selectedStatus === 'pending' ? 'selected' : '' }}>⏳ Bekliyor</option>
                        <option value="passed" {{ $selectedStatus === 'passed' ? 'selected' : '' }}>✓ Başarılı</option>
                        <option value="conditional" {{ $selectedStatus === 'conditional' ? 'selected' : '' }}>⚠️ Şartlı Kabul</option>
                        <option value="failed" {{ $selectedStatus === 'failed' ? 'selected' : '' }}>✕ Reddedildi</option>
                        <option value="exempted" {{ $selectedStatus === 'exempted' ? 'selected' : '' }}>⊘ Muaf</option>
                    </select>
                </div>
            @else
                <div class="col-md-3">
                    <label style="font-size: 11px; font-weight: 700; color: #64748b; display: block; margin-bottom: 3px;">🚨 Şiddet Derecesi:</label>
                    <select name="severity" class="form-control form-control-sm" style="border-radius: 7px; border: 1.5px solid #cbd5e1;" onchange="this.form.submit()">
                        <option value="">-- Tüm Şiddetler --</option>
                        <option value="critical" {{ $selectedSeverity === 'critical' ? 'selected' : '' }}>🚨 Kritik / Durdurucu</option>
                        <option value="major" {{ $selectedSeverity === 'major' ? 'selected' : '' }}>⚠️ Majör / Önemli</option>
                        <option value="minor" {{ $selectedSeverity === 'minor' ? 'selected' : '' }}>ℹ️ Minör / Kusur</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label style="font-size: 11px; font-weight: 700; color: #64748b; display: block; margin-bottom: 3px;">NCR Durumu:</label>
                    <select name="ncr_status" class="form-control form-control-sm" style="border-radius: 7px; border: 1.5px solid #cbd5e1;" onchange="this.form.submit()">
                        <option value="">-- Tümü --</option>
                        <option value="open" {{ request('ncr_status') === 'open' ? 'selected' : '' }}>Açık</option>
                        <option value="in_progress" {{ request('ncr_status') === 'in_progress' ? 'selected' : '' }}>Aksiyon Alınıyor</option>
                        <option value="resolved" {{ request('ncr_status') === 'resolved' ? 'selected' : '' }}>Düzeltildi</option>
                        <option value="closed" {{ request('ncr_status') === 'closed' ? 'selected' : '' }}>Kapatıldı</option>
                    </select>
                </div>
            @endif

            <div class="col-md-3">
                <label style="font-size: 11px; font-weight: 700; color: #64748b; display: block; margin-bottom: 3px;">🔍 Arama:</label>
                <div class="input-group input-group-sm">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Test, standart, kriter veya NCR ara..." 
                           class="form-control" style="border-radius: 7px 0 0 7px; border: 1.5px solid #cbd5e1;">
                    <button class="btn btn-outline-secondary" type="submit" style="border-radius: 0 7px 7px 0;">Ara</button>
                    @if($search || $selectedProjectId || $selectedCheckType || $selectedStatus || $selectedSeverity)
                        <a href="{{ route('quality.index', ['tab' => $activeTab]) }}" class="btn btn-outline-danger" title="Filtreyi Sıfırla">✕</a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- SEKME 1: KALİTE TESTLERİ, KABUL KRİTERLERİ & KALİTE KAPILARI -->
<!-- ========================================================================= -->
@if($activeTab === 'checks')
    <div class="card" style="border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div class="card-header bg-white" style="padding: 14px 20px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
            <div style="font-weight: 800; font-size: 14px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <span>🔬</span> Kalite Kontrol Planı &amp; Test Listesi
                <span class="badge" style="background: #f1f5f9; color: #475569; font-weight: 700; border-radius: 6px;">{{ $qualities->total() }} Kayıt</span>
            </div>
            <div style="font-size: 11.5px; color: #64748b;">
                Tolerans testleri, görsel muayeneler ve faz sonu kalite kapıları
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0" style="font-size: 12.5px;">
                <thead style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; color: #475569; font-size: 11px; text-transform: uppercase; font-weight: 800;">
                    <tr>
                        <th style="padding: 12px 16px;">Proje &amp; Faz</th>
                        <th>Kalite Kontrolü &amp; Standart</th>
                        <th>Kabul Kriteri &amp; Hedef</th>
                        <th>Ölçülen / Fiili Değer</th>
                        <th style="text-align: center;">Durum</th>
                        <th>Denetçi / Onaylayan</th>
                        <th>Sertifika / Rapor</th>
                        <th style="text-align: right; padding-right: 16px;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($qualities as $q)
                        @php
                            $badge = $q->status_badge;
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 12px 16px;">
                                <div style="font-weight: 800; color: #0284c7; font-size: 12px;">
                                    {{ $q->project->code ?? 'PRJ' }}
                                </div>
                                <div style="font-size: 11px; color: #0f172a; max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $q->project->title ?? '' }}">
                                    {{ $q->project->title ?? '-' }}
                                </div>
                                @if($q->phase)
                                    <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">
                                        🚪 Faz: {{ $q->phase->name }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1 mb-0.5">
                                    <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px;">
                                        {{ $q->check_type_label }}
                                    </span>
                                    @if($q->standard_code)
                                        <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; font-family: monospace;">
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
                                <div style="font-size: 12px; color: #334155; line-height: 1.35; max-width: 240px;">
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
                                <span class="status-pill" style="background: {{ $badge['bg'] }}; color: {{ $badge['color'] }};">
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
                                       class="btn btn-xs btn-outline-primary" style="font-size: 11px; padding: 2px 8px; border-radius: 6px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                        📄 Sertifika ↗
                                    </a>
                                @else
                                    <span style="color: #cbd5e1; font-size: 11px;">-</span>
                                @endif
                            </td>
                            <td style="text-align: right; padding-right: 16px;">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <button type="button" class="btn btn-sm" onclick="openEvaluateModal({{ json_encode($q) }})" 
                                            style="background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; font-weight: 700; font-size: 11.5px; border-radius: 6px; padding: 3px 8px;" title="Test Sonucunu Gir &amp; Değerlendir">
                                        ⚡ Sonuçlandır
                                    </button>
                                    <form method="POST" action="{{ route('quality.destroy', $q->id) }}" onsubmit="return confirm('Bu kalite kontrol kriterini silmek istediğinize emin misiniz?')" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0 px-1" title="Sil">
                                            ✕
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5" style="color: #94a3b8;">
                                <div style="font-size: 32px; margin-bottom: 8px;">🔬</div>
                                <div style="font-weight: 700; font-size: 14px; color: #475569;">Kayıtlı Kalite Kontrolü Bulunamadı</div>
                                <p style="font-size: 12px; margin-top: 4px;">Yukarıdaki "Yeni Kalite Kriteri Ekle" butonu ile projenize test planı tanımlayabilirsiniz.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($qualities->hasPages())
            <div class="card-footer bg-white py-2 px-3 border-top">
                {{ $qualities->withQueryString()->links() }}
            </div>
        @endif
    </div>

<!-- ========================================================================= -->
<!-- SEKME 2: UYGUNSUZLUK & HATA PANOSU (NCR / PUNCH LIST / DÖF) -->
<!-- ========================================================================= -->
@else
    <div class="card" style="border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div class="card-header bg-white" style="padding: 14px 20px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
            <div style="font-weight: 800; font-size: 14px; color: #991b1b; display: flex; align-items: center; gap: 8px;">
                <span>🚨</span> Kalite Uygunsuzlukları &amp; Düzeltici Faaliyetler (NCR / DÖF)
                <span class="badge" style="background: #fee2e2; color: #b91c1c; font-weight: 700; border-radius: 6px;">{{ $nonConformances->total() }} Kayıt</span>
            </div>
            <div style="font-size: 11.5px; color: #64748b;">
                Saha kusurları, tolerans aşımları ve kök neden aksiyon planları
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0" style="font-size: 12.5px;">
                <thead style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; color: #475569; font-size: 11px; text-transform: uppercase; font-weight: 800;">
                    <tr>
                        <th style="padding: 12px 16px;">NCR Kodu &amp; Proje</th>
                        <th>Kusur / Uygunsuzluk Tanımı</th>
                        <th style="text-align: center;">Şiddet</th>
                        <th>DÖF / Önleyici Aksiyon Planı</th>
                        <th>Sorumlu &amp; Termin</th>
                        <th style="text-align: center;">Durum</th>
                        <th style="text-align: right; padding-right: 16px;">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($nonConformances as $ncr)
                        @php
                            $sev = $ncr->severity_badge;
                            $st = $ncr->status_badge;
                            $isOverdue = $ncr->due_date && $ncr->due_date->isPast() && in_array($ncr->status, ['open', 'in_progress']);
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 12px 16px;">
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
                                <span class="severity-pill" style="background: {{ $sev['bg'] }}; color: {{ $sev['color'] }};">
                                    {{ $sev['badge'] }}
                                </span>
                            </td>
                            <td>
                                @if($ncr->corrective_action)
                                    <div style="font-size: 12px; color: #0f172a; line-height: 1.35; max-width: 260px;">
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
                                            <span class="badge" style="background: #fee2e2; color: #b91c1c; font-size: 9.5px; padding: 1px 4px;">GECİKTİ</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <span class="status-pill" style="background: {{ $st['bg'] }}; color: {{ $st['color'] }};">
                                    {{ $st['label'] }}
                                </span>
                            </td>
                            <td style="text-align: right; padding-right: 16px;">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <button type="button" class="btn btn-sm" onclick="openUpdateNcrModal({{ json_encode($ncr) }})" 
                                            style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; font-weight: 700; font-size: 11px; border-radius: 6px; padding: 3px 8px;">
                                        Durum Güncelle / Kapat
                                    </button>
                                    <form method="POST" action="{{ route('quality.ncr.destroy', $ncr->id) }}" onsubmit="return confirm('Bu uygunsuzluk kaydını silmek istediğinize emin misiniz?')" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0 px-1" title="Sil">
                                            ✕
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5" style="color: #94a3b8;">
                                <div style="font-size: 32px; margin-bottom: 8px;">🎉</div>
                                <div style="font-weight: 700; font-size: 14px; color: #15803d;">Açık Kalite Uygunsuzluğu Bulunmuyor</div>
                                <p style="font-size: 12px; margin-top: 4px;">Projeniz tüm kabul ve kalite testlerini başarıyla geçmektedir.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($nonConformances->hasPages())
            <div class="card-footer bg-white py-2 px-3 border-top">
                {{ $nonConformances->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endif

<!-- ========================================================================= -->
<!-- MODAL 1: YENİ KALİTE KRİTERİ & TEST EKLE -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalNewQuality" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('quality.store') }}" enctype="multipart/form-data" class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            @csrf
            <div class="modal-header" style="background: #0284c7; color: #ffffff; border-radius: 12px 12px 0 0; padding: 14px 20px;">
                <h5 class="modal-title" style="font-size: 15px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                    <span>🔬</span> Yeni Kalite Kontrolü &amp; Kabul Kriteri Tanımla
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body" style="padding: 20px; font-size: 13px;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" style="font-weight: 700; color: #0f172a;">İlgili Proje *</label>
                        <select name="project_id" class="form-control" required style="border-radius: 7px; border: 1.5px solid #cbd5e1;">
                            <option value="">-- Proje Seçiniz --</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ $selectedProjectId == $p->id ? 'selected' : '' }}>
                                    {{ $p->code }} - {{ $p->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" style="font-weight: 700; color: #0f172a;">Kontrol / Kabul Türü *</label>
                        <select name="check_type" class="form-control" required style="border-radius: 7px; border: 1.5px solid #cbd5e1;">
                            @foreach(\App\Models\ProjectQuality::checkTypes() as $k => $label)
                                <option value="{{ $k }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label" style="font-weight: 700; color: #0f172a;">Kalite Kontrol / Test Başlığı *</label>
                        <input type="text" name="title" class="form-control" placeholder="Örn: Hat 4 Hidrolik Basınç Testi &amp; Sızdırmazlık Doğrulaması" required style="border-radius: 7px; border: 1.5px solid #cbd5e1;">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" style="font-weight: 700; color: #0f172a;">Standart / Şartname No</label>
                        <input type="text" name="standard_code" class="form-control" placeholder="Örn: ISO 9001 / DIN 18800" style="border-radius: 7px; border: 1.5px solid #cbd5e1;">
                    </div>

                    <div class="col-12">
                        <label class="form-label" style="font-weight: 700; color: #0f172a;">Kabul Kriteri / Hedef Değer &amp; Tolerans *</label>
                        <textarea name="acceptance_criteria" rows="2" class="form-control" placeholder="Örn: 15 Bar basınç altında 30 dakika boyunca sıfır sızıntı; maksimum 0.2 bar basınç düşümü toleransı." style="border-radius: 7px; border: 1.5px solid #cbd5e1;"></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" style="font-weight: 700; color: #0f172a;">Testi Yapacak Uzman / Denetçi</label>
                        <select name="checked_by" class="form-control" style="border-radius: 7px; border: 1.5px solid #cbd5e1;">
                            <option value="">-- Denetçi Seçiniz --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->title ?: 'Personel' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" style="font-weight: 700; color: #0f172a;">Sertifika / Şartname Dosyası (Opsiyonel)</label>
                        <input type="file" name="certificate_file" class="form-control" style="border-radius: 7px; border: 1.5px solid #cbd5e1;">
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background: #f8fafc; border-radius: 0 0 12px 12px; border-top: 1px solid #e2e8f0;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                <button type="submit" class="btn btn-primary btn-sm" style="font-weight: 700; background: #0284c7;">✓ Kriteri Kaydet</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: TEST SONUCUNU GİR & DEĞERLENDİR -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalEvaluateQuality" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="formEvaluateQuality" enctype="multipart/form-data" class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            @csrf
            <div class="modal-header" style="background: #16a34a; color: #ffffff; border-radius: 12px 12px 0 0; padding: 14px 20px;">
                <h5 class="modal-title" style="font-size: 15px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                    <span>⚡</span> Test / Kalite Kontrol Değerlendirmesi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body" style="padding: 20px; font-size: 13px;">
                <div class="mb-3 p-2.5 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                    <div style="font-weight: 800; color: #0f172a;" id="evalTitleText">Test Başlığı</div>
                    <div style="font-size: 11.5px; color: #64748b;" id="evalCriteriaText">Kriter</div>
                </div>

                <div class="mb-3">
                    <label class="form-label" style="font-weight: 700; color: #0f172a;">Değerlendirme Sonucu *</label>
                    <select name="status" id="evalStatusSelect" class="form-control" required style="border-radius: 7px; border: 1.5px solid #cbd5e1; font-weight: 700;" onchange="toggleAutoNcrSection(this.value)">
                        <option value="passed">✓ Başarılı / Şartnameyi Sağladı (Passed)</option>
                        <option value="conditional">⚠️ Şartlı Kabul / İstisnalı Onay (Conditional)</option>
                        <option value="failed">✕ Başarısız / Reddedildi (Failed)</option>
                        <option value="exempted">⊘ Muaf Tutuldu (Exempted)</option>
                        <option value="pending">⏳ Bekliyor / Yeniden Test</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" style="font-weight: 700; color: #0f172a;">Ölçülen / Fiili Değer</label>
                    <input type="text" name="measured_value" id="evalMeasuredValue" class="form-control" placeholder="Örn: 15.1 Bar (Ölçüm Başarılı) veya 0.8mm Çatlak" style="border-radius: 7px; border: 1.5px solid #cbd5e1; font-family: 'JetBrains Mono', monospace;">
                </div>

                <div class="mb-3">
                    <label class="form-label" style="font-weight: 700; color: #0f172a;">Test / Muayene Notu</label>
                    <textarea name="notes" id="evalNotes" rows="2" class="form-control" placeholder="Ölçüm kalibrasyonu, ortam sıcaklığı veya gözlem notları..." style="border-radius: 7px; border: 1.5px solid #cbd5e1;"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label" style="font-weight: 700; color: #0f172a;">Test Raporu / Kalibrasyon Belgesi (PDF/Görsel)</label>
                    <input type="file" name="certificate_file" class="form-control" style="border-radius: 7px; border: 1.5px solid #cbd5e1;">
                </div>

                <!-- Otomatik NCR Açma Alanı (Başarısız seçildiğinde) -->
                <div id="autoNcrBox" style="display: none; background: #fef2f2; border: 1.5px dashed #fca5a5; border-radius: 8px; padding: 12px; margin-top: 12px;">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="auto_create_ncr" value="1" id="autoNcrCheck" checked>
                        <label class="form-check-label font-bold text-danger" for="autoNcrCheck" style="font-size: 12px; font-weight: 800;">
                            🚨 Başarısız test için otomatik Uygunsuzluk (NCR) aç
                        </label>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label style="font-size: 11px; font-weight: 700; color: #991b1b;">Şiddet Seviyesi:</label>
                            <select name="ncr_severity" class="form-control form-control-sm">
                                <option value="major">⚠️ Majör / Önemli</option>
                                <option value="critical">🚨 Kritik / Durdurucu</option>
                                <option value="minor">ℹ️ Minör / Kusur</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label style="font-size: 11px; font-weight: 700; color: #991b1b;">DÖF Sorumlusu:</label>
                            <select name="ncr_assignee" class="form-control form-control-sm">
                                <option value="">-- Sorumlu Ata --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background: #f8fafc; border-radius: 0 0 12px 12px; border-top: 1px solid #e2e8f0;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                <button type="submit" class="btn btn-success btn-sm" style="font-weight: 700;">✓ Sonucu Kaydet</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: YENİ UYGUNSUZLUK (NCR / DÖF) AÇ -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalNewNcr" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('quality.ncr.store') }}" enctype="multipart/form-data" class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            @csrf
            <div class="modal-header" style="background: #dc2626; color: #ffffff; border-radius: 12px 12px 0 0; padding: 14px 20px;">
                <h5 class="modal-title" style="font-size: 15px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                    <span>🚨</span> Yeni Kalite Uygunsuzluğu / Kusur Raporu (NCR) Aç
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body" style="padding: 20px; font-size: 13px;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" style="font-weight: 700; color: #0f172a;">İlgili Proje *</label>
                        <select name="project_id" class="form-control" required style="border-radius: 7px; border: 1.5px solid #cbd5e1;">
                            <option value="">-- Proje Seçiniz --</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ $selectedProjectId == $p->id ? 'selected' : '' }}>
                                    {{ $p->code }} - {{ $p->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" style="font-weight: 700; color: #0f172a;">Şiddet Derecesi *</label>
                        <select name="severity" class="form-control" required style="border-radius: 7px; border: 1.5px solid #cbd5e1; font-weight: 700;">
                            <option value="critical">🚨 Kritik (Fazı veya Projeyi Durdurucu Hata)</option>
                            <option value="major" selected>⚠️ Majör (Önemli Kusur / Fonksiyonel Hata)</option>
                            <option value="minor">ℹ️ Minör (Görsel Kusur / Çapak / Kozmetik Hata)</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label" style="font-weight: 700; color: #0f172a;">Uygunsuzluk / Hata Başlığı *</label>
                        <input type="text" name="title" class="form-control" placeholder="Örn: Enjeksiyon Kalıp Sıcak Yolluk Termokupl Hatası" required style="border-radius: 7px; border: 1.5px solid #cbd5e1;">
                    </div>

                    <div class="col-12">
                        <label class="form-label" style="font-weight: 700; color: #0f172a;">Tespit Edilen Kusur &amp; Kök Neden Açıklaması</label>
                        <textarea name="description" rows="2" class="form-control" placeholder="Sahada veya üretimde ne tespit edildi? Şartnameden ne kadar sapıldı?" style="border-radius: 7px; border: 1.5px solid #cbd5e1;"></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label" style="font-weight: 700; color: #0f172a;">DÖF (Düzeltici ve Önleyici Faaliyet Önerisi)</label>
                        <textarea name="corrective_action" rows="2" class="form-control" placeholder="Bu hatayı gidermek ve tekrarlanmasını önlemek için ne yapılacak?" style="border-radius: 7px; border: 1.5px solid #cbd5e1;"></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" style="font-weight: 700; color: #0f172a;">Düzeltme Sorumlusu (Assignee)</label>
                        <select name="assigned_to" class="form-control" style="border-radius: 7px; border: 1.5px solid #cbd5e1;">
                            <option value="">-- Sorumlu Personel --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->title ?: 'Ekip' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" style="font-weight: 700; color: #0f172a;">Düzeltme Termini (Due Date)</label>
                        <input type="date" name="due_date" class="form-control" style="border-radius: 7px; border: 1.5px solid #cbd5e1;">
                    </div>

                    <div class="col-12">
                        <label class="form-label" style="font-weight: 700; color: #0f172a;">Kusur Fotoğrafı / Kanıt Dosyası</label>
                        <input type="file" name="evidence_file" class="form-control" style="border-radius: 7px; border: 1.5px solid #cbd5e1;">
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background: #f8fafc; border-radius: 0 0 12px 12px; border-top: 1px solid #e2e8f0;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                <button type="submit" class="btn btn-danger btn-sm" style="font-weight: 700;">🚨 NCR Kaydını Başlat</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 4: NCR DURUM GÜNCELLE / KAPAT -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalUpdateNcr" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="formUpdateNcr" class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            @csrf
            <div class="modal-header" style="background: #334155; color: #ffffff; border-radius: 12px 12px 0 0; padding: 14px 20px;">
                <h5 class="modal-title" style="font-size: 15px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                    <span>⚙️</span> NCR Durumunu Güncelle &amp; Kapat
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body" style="padding: 20px; font-size: 13px;">
                <div class="mb-3 p-2.5 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                    <div style="font-weight: 900; color: #dc2626; font-family: monospace;" id="ncrModalCode">NCR-2026-0001</div>
                    <div style="font-weight: 700; color: #0f172a; margin-top: 2px;" id="ncrModalTitle">Başlık</div>
                </div>

                <div class="mb-3">
                    <label class="form-label" style="font-weight: 700; color: #0f172a;">Yeni Durum *</label>
                    <select name="status" id="ncrModalStatus" class="form-control" required style="border-radius: 7px; border: 1.5px solid #cbd5e1; font-weight: 700;">
                        <option value="open">Açık / Tespit Edildi</option>
                        <option value="in_progress">Aksiyon Alınıyor / Düzeltme Devam Ediyor</option>
                        <option value="resolved">Düzeltildi / Doğrulama Bekliyor</option>
                        <option value="closed">✓ Kapatıldı / Kalite Onaylı</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" style="font-weight: 700; color: #0f172a;">Alınan Aksiyon &amp; Çözüm Notu</label>
                    <textarea name="resolution_notes" id="ncrModalNotes" rows="3" class="form-control" placeholder="Düzeltme nasıl yapıldı? Tekrar test sonucu ne oldu?" style="border-radius: 7px; border: 1.5px solid #cbd5e1;"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="background: #f8fafc; border-radius: 0 0 12px 12px; border-top: 1px solid #e2e8f0;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">İptal</button>
                <button type="submit" class="btn btn-primary btn-sm" style="font-weight: 700;">✓ Güncelle</button>
            </div>
        </form>
    </div>
</div>

<script>
function openNewQualityModal() {
    new bootstrap.Modal(document.getElementById('modalNewQuality')).show();
}

function openNewNcrModal() {
    new bootstrap.Modal(document.getElementById('modalNewNcr')).show();
}

function openEvaluateModal(quality) {
    document.getElementById('evalTitleText').textContent = quality.title;
    document.getElementById('evalCriteriaText').textContent = 'Kriter: ' + (quality.acceptance_criteria || '-');
    document.getElementById('evalStatusSelect').value = quality.status || 'passed';
    document.getElementById('evalMeasuredValue').value = quality.measured_value || '';
    document.getElementById('evalNotes').value = quality.notes || '';
    document.getElementById('formEvaluateQuality').action = '{{ url("/kalite") }}/' + quality.id + '/evaluate';
    
    toggleAutoNcrSection(quality.status);
    new bootstrap.Modal(document.getElementById('modalEvaluateQuality')).show();
}

function toggleAutoNcrSection(status) {
    const box = document.getElementById('autoNcrBox');
    if (status === 'failed') {
        box.style.display = 'block';
    } else {
        box.style.display = 'none';
    }
}

function openUpdateNcrModal(ncr) {
    document.getElementById('ncrModalCode').textContent = ncr.ncr_code;
    document.getElementById('ncrModalTitle').textContent = ncr.title;
    document.getElementById('ncrModalStatus').value = ncr.status;
    document.getElementById('ncrModalNotes').value = ncr.resolution_notes || '';
    document.getElementById('formUpdateNcr').action = '{{ url("/kalite/ncr") }}/' + ncr.id;

    new bootstrap.Modal(document.getElementById('modalUpdateNcr')).show();
}
</script>
@endsection
