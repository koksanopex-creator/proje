@extends('layouts.app')

@section('title', '5x5 Risk Matrisi & Analiz')
@section('page_title', 'Risk Yönetimi & 5x5 Likert Değerlendirme Matrisi')
@section('page_subtitle', 'Endüstriyel, İSG, teknik, tedarik ve finansal risklerin interaktif baloncuklu analizi')

@section('content')
<style>
/* 5x5 Risk Matrisi Hücre ve Baloncuk Stilleri */
.risk-matrix-wrapper {
    display: flex;
    gap: 28px;
    align-items: stretch;
    justify-content: center;
    flex-wrap: wrap;
}
.risk-matrix-table {
    border-collapse: separate;
    border-spacing: 5px;
    width: 100%;
    max-width: 580px;
}
.risk-matrix-cell {
    width: 90px;
    height: 82px;
    border-radius: 8px;
    padding: 6px;
    position: relative;
    vertical-align: top;
    transition: all 0.2s ease;
    border: 1.5px solid transparent;
}
.risk-matrix-cell:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.risk-matrix-cell .cell-score {
    position: absolute;
    bottom: 4px;
    right: 6px;
    font-size: 10px;
    font-weight: 800;
    opacity: 0.45;
    user-select: none;
}
.risk-matrix-cell.cell-low {
    background: #ecfdf5;
    border-color: #a7f3d0;
    color: #065f46;
}
.risk-matrix-cell.cell-medium {
    background: #fefce8;
    border-color: #fde047;
    color: #854d0e;
}
.risk-matrix-cell.cell-high {
    background: #fff7ed;
    border-color: #fdba74;
    color: #9a3412;
}
.risk-matrix-cell.cell-critical {
    background: #fef2f2;
    border-color: #fca5a5;
    color: #991b1b;
}

/* Numaralı Risk Baloncukları (Risk Bubble Chips) */
.risk-matrix-cell {
    width: 90px;
    height: 82px;
    border-radius: 8px;
    padding: 6px;
    position: relative;
    vertical-align: top;
    transition: all 0.2s ease;
    border: 1.5px solid transparent;
    overflow: visible !important;
}
.risk-bubble-container {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    align-items: flex-start;
    overflow: visible !important;
}
.risk-bubble {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 10.5px;
    font-weight: 800;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0,0,0,0.12);
    transition: all 0.15s ease;
    user-select: none;
    color: #ffffff;
    position: relative;
}
.risk-bubble:hover {
    transform: scale(1.22);
    z-index: 30;
    box-shadow: 0 4px 10px rgba(0,0,0,0.25);
}
.risk-bubble.bubble-critical {
    background: #dc2626;
    border: 1.5px solid #b91c1c;
}
.risk-bubble.bubble-high {
    background: #ea580c;
    border: 1.5px solid #c2410c;
}
.risk-bubble.bubble-medium {
    background: #d97706;
    border: 1.5px solid #b45309;
}
.risk-bubble.bubble-low {
    background: #16a34a;
    border: 1.5px solid #15803d;
}

/* Tooltip / Açıklama Kutusu */
.risk-tooltip-content {
    display: none;
    position: absolute;
    bottom: 110%;
    left: 50%;
    transform: translateX(-50%);
    background: #0f172a;
    color: #ffffff;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 500;
    white-space: normal;
    width: 210px;
    z-index: 100;
    box-shadow: 0 6px 18px rgba(0,0,0,0.25);
    pointer-events: none;
    line-height: 1.3;
}
.risk-bubble:hover .risk-tooltip-content {
    display: block;
}

/* Tablo Vurgulama Animasyonu */
@keyframes highlightFlash {
    0% { background: #fef08a; }
    50% { background: #fef9c3; }
    100% { background: transparent; }
}
.row-highlighted {
    animation: highlightFlash 2.5s ease;
}
</style>

    <!-- 1. ÜST KPI ÖZET KARTLARI -->
    @php
        $totalRisks = $risks->count();
        $critRisks = $risks->where('risk_score', '>=', 15)->count();
        $medRisks = $risks->whereBetween('risk_score', [8, 14])->count();
        $lowRisks = $risks->where('risk_score', '<=', 6)->count();
        $totalMitigations = $risks->sum(fn($r) => $r->mitigations->count());
        $completedMitigations = $risks->sum(fn($r) => $r->mitigations->where('is_completed', true)->count());
    @endphp

    <div class="grid-4" style="gap: 16px; margin-bottom: 24px;">
        <div class="card" style="border-radius: 10px; border-left: 4px solid #ef4444; padding: 16px 18px;">
            <div style="font-size: 12px; font-weight: 700; color: #dc2626; text-transform: uppercase;">Kritik & Yüksek Risk</div>
            <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 6px;">
                <span style="font-size: 26px; font-weight: 800; color: #0f172a;">{{ $critRisks }}</span>
                <span class="badge" style="background: #fef2f2; color: #991b1b; font-size: 11px; font-weight: 700;">Skor ≥ 15</span>
            </div>
            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Acil hafifletme eylemi zorunlu</div>
        </div>

        <div class="card" style="border-radius: 10px; border-left: 4px solid #f59e0b; padding: 16px 18px;">
            <div style="font-size: 12px; font-weight: 700; color: #d97706; text-transform: uppercase;">Orta Seviye Risk</div>
            <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 6px;">
                <span style="font-size: 26px; font-weight: 800; color: #0f172a;">{{ $medRisks }}</span>
                <span class="badge" style="background: #fffbeb; color: #92400e; font-size: 11px; font-weight: 700;">Skor 8 - 14</span>
            </div>
            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Yönetim takibi & plan önerilir</div>
        </div>

        <div class="card" style="border-radius: 10px; border-left: 4px solid #10b981; padding: 16px 18px;">
            <div style="font-size: 12px; font-weight: 700; color: #059669; text-transform: uppercase;">Düşük / Kabul Edilebilir</div>
            <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 6px;">
                <span style="font-size: 26px; font-weight: 800; color: #0f172a;">{{ $lowRisks }}</span>
                <span class="badge" style="background: #ecfdf5; color: #065f46; font-size: 11px; font-weight: 700;">Skor 1 - 6</span>
            </div>
            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Periyodik izleme yeterli</div>
        </div>

        <div class="card" style="border-radius: 10px; border-left: 4px solid #0284c7; padding: 16px 18px;">
            <div style="font-size: 12px; font-weight: 700; color: #0284c7; text-transform: uppercase;">Azaltma Eylemleri</div>
            <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 6px;">
                <span style="font-size: 26px; font-weight: 800; color: #0f172a;">{{ $totalMitigations }}</span>
                <span class="badge" style="background: #f0f9ff; color: #0369a1; font-size: 11px; font-weight: 700;">{{ $completedMitigations }} Çözüldü</span>
            </div>
            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Toplam {{ $totalRisks }} kayıtlı risk</div>
        </div>
    </div>

    <!-- 2. CELOXIS 5x5 BALONCUKLU RİSK MATRİSİ -->
    <div class="card mb-4" style="border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff; box-shadow: 0 2px 10px rgba(0,0,0,0.03);">
        <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 18px;">🎯</span>
                <h3 class="card-title" style="margin: 0; font-size: 15px; font-weight: 800; color: #0f172a;">
                    5×5 Olasılık & Etki Dağılım Matrisi (Likert Bubble Chart)
                </h3>
            </div>
            <div style="font-size: 12px; color: #64748b; font-weight: 600;">
                💡 Baloncukların üzerine gelerek detayları görebilir, tıklayarak aşağıdaki tablodan odaklanabilirsiniz.
            </div>
        </div>
        <div class="card-body" style="padding: 24px;">
            <div class="risk-matrix-wrapper">
                
                <!-- Y Ekseni Başlığı + 5x5 Matris Izgarası -->
                <div style="display: flex; align-items: center; gap: 10px;">
                    
                    <!-- Y Ekseni Etiketi -->
                    <div style="writing-mode: vertical-rl; transform: rotate(180deg); font-weight: 800; font-size: 12px; color: #475569; letter-spacing: 1px; text-transform: uppercase; text-align: center;">
                        ▲ OLASILIK (PROBABILITY: 1 - 5)
                    </div>

                    <div>
                        <!-- Üst Etki Başlıkları -->
                        <div style="text-align: center; font-weight: 800; font-size: 12px; color: #475569; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 6px;">
                            ETKİ (IMPACT: 1 - 5) ►
                        </div>

                        <table class="risk-matrix-table">
                            <thead>
                                <tr style="font-size: 11px; font-weight: 800; color: #64748b; text-align: center;">
                                    <th style="width: 24px;"></th>
                                    <th>1 (Çok Düşük)</th>
                                    <th>2 (Düşük)</th>
                                    <th>3 (Orta)</th>
                                    <th>4 (Yüksek)</th>
                                    <th>5 (Kritik)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    // Y Ekseni: Olasılık 5'ten 1'e iner
                                    $probs = [5, 4, 3, 2, 1];
                                    $impacts = [1, 2, 3, 4, 5];
                                @endphp

                                @foreach($probs as $p)
                                    <tr>
                                        <!-- Y Satır Başlığı -->
                                        <td style="font-size: 11px; font-weight: 800; color: #64748b; text-align: right; padding-right: 6px;">
                                            {{ $p }}
                                        </td>

                                        @foreach($impacts as $i)
                                            @php
                                                $score = $p * $i;
                                                if ($score >= 15) {
                                                    $cellClass = 'cell-critical';
                                                    $bubbleClass = 'bubble-critical';
                                                } elseif ($score >= 10) {
                                                    $cellClass = 'cell-high';
                                                    $bubbleClass = 'bubble-high';
                                                } elseif ($score >= 7) {
                                                    $cellClass = 'cell-medium';
                                                    $bubbleClass = 'bubble-medium';
                                                } else {
                                                    $cellClass = 'cell-low';
                                                    $bubbleClass = 'bubble-low';
                                                }

                                                // Bu hücredeki riskleri filtrele
                                                $cellRisks = $risks->where('probability', $p)->where('impact', $i);
                                            @endphp

                                            <td class="risk-matrix-cell {{ $cellClass }}">
                                                <div class="cell-score">{{ $score }}</div>

                                                <div class="risk-bubble-container">
                                                    @foreach($cellRisks as $cr)
                                                        <div class="risk-bubble {{ $bubbleClass }}" 
                                                             onclick="focusRiskRow({{ $cr->id }})"
                                                             onmouseenter="showRiskFloatingTooltip(this, '{{ $cr->id }}', '{{ addslashes($cr->title) }}', '{{ addslashes($cr->project->title) }}', '{{ strtoupper($cr->category) }}', '{{ $cr->probability }}', '{{ $cr->impact }}', '{{ $cr->risk_score }}', '{{ strtoupper($cr->severity) }}', '{{ addslashes($cr->owner?->name ?? 'Belirtilmedi') }}')"
                                                             onmouseleave="hideRiskFloatingTooltip()">
                                                            {{ $cr->id }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Sağ Panel: Matris Açıklama Skalası & İstatistikler -->
                <div style="width: 300px; display: flex; flex-direction: column; justify-content: space-between; gap: 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px;">
                    <div>
                        <h4 style="margin: 0 0 12px; font-size: 13.5px; font-weight: 800; color: #0f172a;">
                            Skor Seviyesi Değerlendirme Skalası:
                        </h4>

                        <div style="display: flex; flex-direction: column; gap: 10px; font-size: 12px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 6px 10px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="width: 14px; height: 14px; border-radius: 50%; background: #dc2626; display: inline-block;"></span>
                                    <strong style="color: #991b1b;">15 - 25 Kritik / Yüksek</strong>
                                </div>
                                <span class="badge" style="background: #dc2626; color: #fff; font-weight: 800;">{{ $critRisks }}</span>
                            </div>

                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 6px 10px; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 6px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="width: 14px; height: 14px; border-radius: 50%; background: #ea580c; display: inline-block;"></span>
                                    <strong style="color: #9a3412;">10 - 14 Yüksek Seviye</strong>
                                </div>
                                <span class="badge" style="background: #ea580c; color: #fff; font-weight: 800;">{{ $risks->whereBetween('risk_score', [10, 14])->count() }}</span>
                            </div>

                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 6px 10px; background: #fefce8; border: 1px solid #fef08a; border-radius: 6px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="width: 14px; height: 14px; border-radius: 50%; background: #d97706; display: inline-block;"></span>
                                    <strong style="color: #854d0e;">7 - 9 Orta Seviye</strong>
                                </div>
                                <span class="badge" style="background: #d97706; color: #fff; font-weight: 800;">{{ $risks->whereBetween('risk_score', [7, 9])->count() }}</span>
                            </div>

                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 6px 10px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="width: 14px; height: 14px; border-radius: 50%; background: #16a34a; display: inline-block;"></span>
                                    <strong style="color: #065f46;">1 - 6 Düşük / Tolere</strong>
                                </div>
                                <span class="badge" style="background: #16a34a; color: #fff; font-weight: 800;">{{ $lowRisks }}</span>
                            </div>
                        </div>
                    </div>

                    <div style="background: #ffffff; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 11.5px; color: #475569;">
                        <strong>Likert Formülü:</strong><br>
                        <code>Risk Skoru = Olasılık (1-5) × Etki (1-5)</code><br>
                        Skor ≥ 15 olan tüm riskler için İSG veya PMO düzeyinde zorunlu azaltma planı işletilir.
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- 3. FİLTRE VE EYLEM ARAÇ ÇUBUĞU -->
    <div class="card mb-3" style="border-radius: 10px; border: 1px solid #e2e8f0; background: #ffffff;">
        <div class="card-body" style="padding: 12px 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                
                <!-- Proje Seçim Formu -->
                <form action="{{ route('risks.index') }}" method="GET" style="display: flex; align-items: center; gap: 8px; margin: 0;">
                    <label style="font-size: 12.5px; font-weight: 700; color: #0f172a; margin: 0;">📁 Proje:</label>
                    <select name="project_id" onchange="this.form.submit()" class="form-control" style="font-size: 12px; height: 34px; border-radius: 6px; width: 280px; max-width: 100%;">
                        <option value="">-- Tüm Projeler (Portföy Geneli) --</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ $selectedProjectId == $p->id ? 'selected' : '' }}>
                                {{ $p->code }} - {{ $p->title }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <!-- Canlı Arama Çubuğu -->
                <div style="display: flex; align-items: center; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 10px;">
                    <span style="font-size: 13px; color: #64748b;">🔍</span>
                    <input type="text" id="riskTableSearch" placeholder="Risk başlığı, kategori veya sorumlu ara..." onkeyup="filterRiskTable()" style="border: none; background: transparent; padding: 6px 8px; font-size: 12px; outline: none; width: 230px;">
                </div>
            </div>

            <!-- Yeni Risk Ekle Butonu -->
            <div>
                <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('newRiskModal').style.display='flex'" style="font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 6px; border-radius: 6px; padding: 7px 14px;">
                    <span>➕</span> Yeni Risk Tanımla
                </button>
            </div>
        </div>
    </div>

    <!-- 4. DETAYLI RİSK VE AZALTMA PLANI TABLOSU -->
    <div class="card" style="border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff; box-shadow: 0 2px 10px rgba(0,0,0,0.03); overflow: hidden;">
        <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 14px 20px;">
            <span class="card-title" style="font-size: 15px; font-weight: 800; color: #0f172a;">
                📋 Kayıtlı Proje Riskleri & Azaltma Eylemleri (Mitigations)
            </span>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="app-table" id="risksMasterTable" style="margin: 0; width: 100%;">
                <thead>
                    <tr style="background: #f8fafc; font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase;">
                        <th style="width: 50px; text-align: center;">No</th>
                        <th style="width: 28%;">Risk Başlığı & Açıklama</th>
                        <th style="width: 18%;">Proje</th>
                        <th style="width: 10%;">Kategori</th>
                        <th style="width: 7%; text-align: center;">Olasılık</th>
                        <th style="width: 7%; text-align: center;">Etki</th>
                        <th style="width: 8%; text-align: center;">Skor</th>
                        <th style="width: 12%;">Sorumlu</th>
                        <th style="width: 10%; text-align: right;">Eylemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($risks as $r)
                        @php
                            if ($r->risk_score >= 15) {
                                $scoreBg = '#fee2e2'; $scoreColor = '#991b1b'; $badgeTxt = 'KRİTİK';
                            } elseif ($r->risk_score >= 10) {
                                $scoreBg = '#ffedd5'; $scoreColor = '#9a3412'; $badgeTxt = 'YÜKSEK';
                            } elseif ($r->risk_score >= 7) {
                                $scoreBg = '#fef3c7'; $scoreColor = '#92400e'; $badgeTxt = 'ORTA';
                            } else {
                                $scoreBg = '#dcfce7'; $scoreColor = '#166534'; $badgeTxt = 'DÜŞÜK';
                            }
                        @endphp
                        <tr class="risk-data-row" id="risk-row-{{ $r->id }}" style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;">
                            <!-- No -->
                            <td style="text-align: center;">
                                <span class="badge" style="background: #0f172a; color: #ffffff; font-size: 11px; font-weight: 800; border-radius: 12px; padding: 3px 7px;">
                                    #{{ $r->id }}
                                </span>
                            </td>

                            <!-- Başlık & Açıklama -->
                            <td>
                                <strong style="color: #0f172a; font-size: 13px;">{{ $r->title }}</strong>
                                @if($r->description)
                                    <div style="font-size: 11.5px; color: #64748b; margin-top: 3px; line-height: 1.3;">
                                        {{ $r->description }}
                                    </div>
                                @endif
                            </td>

                            <!-- Proje -->
                            <td style="font-size: 12px; color: #334155;">
                                <strong>{{ $r->project->code }}</strong><br>
                                <span style="font-size: 11px; color: #64748b;">{{ Str::limit($r->project->title, 26) }}</span>
                            </td>

                            <!-- Kategori -->
                            <td>
                                <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 10.5px; font-weight: 700; text-transform: uppercase;">
                                    {{ $r->category }}
                                </span>
                            </td>

                            <!-- Olasılık -->
                            <td style="text-align: center; font-weight: 800; font-size: 13px; color: #1e293b;">
                                {{ $r->probability }} / 5
                            </td>

                            <!-- Etki -->
                            <td style="text-align: center; font-weight: 800; font-size: 13px; color: #1e293b;">
                                {{ $r->impact }} / 5
                            </td>

                            <!-- Skor Rozeti -->
                            <td style="text-align: center;">
                                <span class="badge" style="background: {{ $scoreBg }}; color: {{ $scoreColor }}; font-size: 12px; font-weight: 800; padding: 4px 8px; border-radius: 6px;">
                                    {{ $r->risk_score }} ({{ $badgeTxt }})
                                </span>
                            </td>

                            <!-- Sorumlu -->
                            <td style="font-size: 12px;">
                                @if($r->owner)
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <div style="width: 22px; height: 22px; border-radius: 50%; background: #0284c7; color: #fff; font-size: 10px; font-weight: 700; display: flex; align-items: center; justify-content: center;">
                                            {{ mb_substr($r->owner->name, 0, 1) }}
                                        </div>
                                        <span>{{ $r->owner->name }}</span>
                                    </div>
                                @else
                                    <span style="color: #94a3b8; font-style: italic;">Atanmadı</span>
                                @endif
                            </td>

                            <!-- Eylemler (Akordeon Açma) -->
                            <td style="text-align: right;">
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="toggleMitigations({{ $r->id }})" style="font-size: 11px; font-weight: 700; border-radius: 5px; padding: 3px 8px;">
                                    🛡️ Eylemler ({{ $r->mitigations->count() }}) ▼
                                </button>
                            </td>
                        </tr>

                        <!-- ALT AKORDEON: AZALTMA EYLEM PLANLARI -->
                        <tr id="mitigation-row-{{ $r->id }}" style="display: none; background: #f8fafc;">
                            <td colspan="9" style="padding: 16px 20px; border-bottom: 2px solid #e2e8f0;">
                                <div style="border-left: 3px solid #0284c7; padding-left: 14px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                        <h5 style="margin: 0; font-size: 13px; font-weight: 800; color: #0f172a;">
                                            🛡️ "{{ $r->title }}" İçin Azaltma / Önleme Eylem Planları
                                        </h5>
                                        <button type="button" class="btn btn-xs btn-outline-primary" onclick="document.getElementById('addMitigationForm-{{ $r->id }}').style.display='block'" style="font-size: 11px; font-weight: 700;">
                                            ➕ Yeni Eylem Ekle
                                        </button>
                                    </div>

                                    <!-- Mevcut Eylem Planları Listesi -->
                                    @if($r->mitigations->count() > 0)
                                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px;">
                                            @foreach($r->mitigations as $m)
                                                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                                                    <div style="flex: 1; min-width: 260px;">
                                                        <div style="font-size: 12px; font-weight: 600; color: #1e293b;">
                                                            {{ $m->action_plan }}
                                                        </div>
                                                        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                                            Strateji: <strong style="color: #0284c7; text-transform: uppercase;">{{ $m->strategy }}</strong> | 
                                                            Hedef Termin: <strong>{{ $m->target_date ? \Carbon\Carbon::parse($m->target_date)->format('d.m.Y') : 'Belirtilmedi' }}</strong> |
                                                            Sorumlu: <strong>{{ $m->assignee?->name ?? 'Ekip' }}</strong>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <span class="badge" style="background: {{ $m->is_completed ? '#ecfdf5; color: #065f46;' : '#fef3c7; color: #92400e;' }}; font-weight: 700;">
                                                            {{ $m->is_completed ? '✓ Tamamlandı' : '⏳ Süreçte' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div style="font-size: 12px; color: #64748b; margin-bottom: 10px;">Henüz azaltma eylemi eklenmedi.</div>
                                    @endif

                                    <!-- Yeni Eylem Planı Ekleme Formu (Gizlenebilir) -->
                                    <div id="addMitigationForm-{{ $r->id }}" style="display: none; background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 12px; margin-top: 8px;">
                                        <form action="{{ route('risks.mitigation', $r->id) }}" method="POST">
                                            @csrf
                                            <div class="grid-3" style="gap: 10px; margin-bottom: 8px;">
                                                <div style="grid-column: span 2;">
                                                    <label class="form-label" style="font-size: 11px; font-weight: 700;">Önleyici / Azaltıcı Eylem Planı Metni *</label>
                                                    <input type="text" name="action_plan" class="form-control" required placeholder="Örn: Yedek parça tedarik kontratı imzalanması..." style="font-size: 12px; height: 32px;">
                                                </div>
                                                <div>
                                                    <label class="form-label" style="font-size: 11px; font-weight: 700;">Strateji *</label>
                                                    <select name="strategy" class="form-control" style="font-size: 12px; height: 32px;">
                                                        <option value="mitigate">Mitigate (Hafiflet / Azalt)</option>
                                                        <option value="avoid">Avoid (Kaçın / Önle)</option>
                                                        <option value="transfer">Transfer (Devret / Sigorta)</option>
                                                        <option value="accept">Accept (Kabul Et / İzle)</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="grid-3" style="gap: 10px; margin-bottom: 10px;">
                                                <div>
                                                    <label class="form-label" style="font-size: 11px; font-weight: 700;">Sorumlu Kişi</label>
                                                    <select name="assigned_to" class="form-control" style="font-size: 12px; height: 32px;">
                                                        <option value="">-- Sorumlu Seç --</option>
                                                        @foreach($users as $u)
                                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label" style="font-size: 11px; font-weight: 700;">Hedef Termin Tarihi</label>
                                                    <input type="date" name="target_date" class="form-control" style="font-size: 12px; height: 32px;">
                                                </div>
                                                <div style="display: flex; align-items: flex-end; gap: 6px;">
                                                    <button type="submit" class="btn btn-sm btn-primary" style="font-size: 11px; font-weight: 700; height: 32px;">
                                                        Kaydet
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('addMitigationForm-{{ $r->id }}').style.display='none'" style="font-size: 11px; height: 32px;">
                                                        İptal
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 30px; color: #64748b;">
                                Seçilen filtrede kayıtlı risk bulunamadı.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 5. YENİ RİSK TANIMLAMA MODALI -->
    <div id="newRiskModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15,23,42,0.6); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div style="background: #ffffff; border-radius: 12px; width: 620px; max-width: 95%; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
                <h4 style="margin: 0; font-size: 15px; font-weight: 800; color: #0f172a;">➕ Yeni Proje Riski Tanımla (5×5 Likert)</h4>
                <button type="button" onclick="document.getElementById('newRiskModal').style.display='none'" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #64748b;">✕</button>
            </div>
            
            <form action="{{ route('risks.store') }}" method="POST" style="padding: 20px;">
                @csrf
                <div style="margin-bottom: 14px;">
                    <label class="form-label" style="font-size: 12px; font-weight: 700;">İlgili Proje *</label>
                    <select name="project_id" class="form-control" required style="font-size: 13px;">
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ $selectedProjectId == $p->id ? 'selected' : '' }}>
                                {{ $p->code }} - {{ $p->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 14px;">
                    <label class="form-label" style="font-size: 12px; font-weight: 700;">Risk Başlığı *</label>
                    <input type="text" name="title" class="form-control" required placeholder="Örn: Yüksek gerilim trafo teslimat gecikmesi..." style="font-size: 13px;">
                </div>

                <div class="grid-2" style="gap: 12px; margin-bottom: 14px;">
                    <div>
                        <label class="form-label" style="font-size: 12px; font-weight: 700;">Kategori *</label>
                        <select name="category" class="form-control" required style="font-size: 13px;">
                            <option value="isg">İSG & Çevre Güvenliği</option>
                            <option value="tedarik">Tedarik & Lojistik</option>
                            <option value="teknik">Teknik & Mühendislik</option>
                            <option value="finansal">Finansal & Maliyet</option>
                            <option value="yasal">Yasal & İzin Süreçleri</option>
                            <option value="operasyonel">Operasyonel / Üretim</option>
                            <option value="insan_kaynaklari">İnsan Kaynakları</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 12px; font-weight: 700;">Risk Sorumlusu</label>
                        <select name="owner_id" class="form-control" style="font-size: 13px;">
                            <option value="">-- Sorumlu Seç --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid-2" style="gap: 12px; margin-bottom: 14px; background: #f8fafc; padding: 14px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <div>
                        <label class="form-label" style="font-size: 12px; font-weight: 800; color: #0284c7;">Olasılık (Likelihood: 1 - 5) *</label>
                        <select name="probability" id="modalProbSelect" class="form-control" required onchange="calcModalScore()" style="font-size: 13px; font-weight: 700;">
                            <option value="1">1 - Çok Düşük (Yılda bir kez / Nadiren)</option>
                            <option value="2">2 - Düşük (Olasılığı az)</option>
                            <option value="3" selected>3 - Orta (Olabilir / Beklenebilir)</option>
                            <option value="4">4 - Yüksek (Büyük olasılıkla)</option>
                            <option value="5">5 - Çok Yüksek (Neredeyse kesin)</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 12px; font-weight: 800; color: #dc2626;">Etki (Impact: 1 - 5) *</label>
                        <select name="impact" id="modalImpactSelect" class="form-control" required onchange="calcModalScore()" style="font-size: 13px; font-weight: 700;">
                            <option value="1">1 - Çok Düşük (İhmal edilebilir)</option>
                            <option value="2">2 - Düşük (Küçük aksama)</option>
                            <option value="3" selected>3 - Orta (Termin / bütçe sapması)</option>
                            <option value="4">4 - Yüksek (Ciddi aksama)</option>
                            <option value="5">5 - Kritik / Felaket (Durdurucu)</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 10px 14px; border-radius: 8px;">
                    <span style="font-size: 12.5px; font-weight: 700; color: #166534;">Tahmini Risk Skoru:</span>
                    <span id="modalCalculatedScore" style="font-size: 16px; font-weight: 800; color: #166534;">9 (ORTA)</span>
                </div>

                <div style="margin-bottom: 14px;">
                    <label class="form-label" style="font-size: 12px; font-weight: 700;">Risk Açıklaması & Kök Neden</label>
                    <textarea name="description" rows="3" class="form-control" placeholder="Riskin olası nedenleri, etkileri ve şantiyedeki durumu..." style="font-size: 12.5px;"></textarea>
                </div>

                <input type="hidden" name="status" value="open">

                <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px;">
                    <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('newRiskModal').style.display='none'">Vazgeç</button>
                    <button type="submit" class="btn btn-primary" style="font-weight: 700;">Riski Kaydet</button>
                </div>
            </form>
        </div>
    </div>

<!-- KUSURSUZ SABİT TOOLTIP KATMANI (SCROLLBAR VE TAŞMA YAPMAZ) -->
<div id="riskGlobalFloatingTooltip" style="display: none; position: fixed; z-index: 999999; background: #0f172a; color: #ffffff; padding: 10px 14px; border-radius: 8px; font-size: 11.5px; width: 230px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); pointer-events: none; line-height: 1.35; border: 1px solid #334155;">
    <div id="tipTitle" style="font-weight: 800; color: #38bdf8; margin-bottom: 3px;"></div>
    <div id="tipProject" style="font-size: 11px; color: #cbd5e1; margin-bottom: 2px;"></div>
    <div id="tipCategory" style="font-size: 11px; color: #94a3b8; margin-bottom: 2px;"></div>
    <div id="tipScore" style="font-size: 11.5px; font-weight: 700; color: #fde047; margin-bottom: 2px;"></div>
    <div id="tipOwner" style="font-size: 11px; color: #cbd5e1; margin-bottom: 4px;"></div>
    <div style="font-size: 9.5px; color: #64748b; border-top: 1px solid #334155; padding-top: 3px;">
        👇 Tıklayarak aşağıdaki tablodan odaklanın
    </div>
</div>

<script>
function showRiskFloatingTooltip(el, id, title, project, cat, prob, imp, score, sev, owner) {
    const tip = document.getElementById('riskGlobalFloatingTooltip');
    if (!tip) return;

    document.getElementById('tipTitle').textContent = '#R' + id + ' - ' + title;
    document.getElementById('tipProject').innerHTML = '📁 <strong>Proje:</strong> ' + project;
    document.getElementById('tipCategory').innerHTML = '🏷️ <strong>Kategori:</strong> ' + cat;
    document.getElementById('tipScore').innerHTML = '⚡ <strong>Skor:</strong> ' + prob + '×' + imp + ' = ' + score + ' (' + sev + ')';
    document.getElementById('tipOwner').innerHTML = '👤 <strong>Sorumlu:</strong> ' + owner;

    tip.style.display = 'block';

    const rect = el.getBoundingClientRect();
    const tipWidth = tip.offsetWidth || 230;
    const tipHeight = tip.offsetHeight || 120;

    let left = rect.left + (rect.width / 2) - (tipWidth / 2);
    let top = rect.top - tipHeight - 10;

    // Ekrandan dışarı taşmayı engelle
    if (left < 10) left = 10;
    if (left + tipWidth > window.innerWidth - 10) left = window.innerWidth - tipWidth - 10;
    if (top < 10) top = rect.bottom + 10; // Yukarı sığmıyorsa alta koy

    tip.style.left = left + 'px';
    tip.style.top = top + 'px';
}

function hideRiskFloatingTooltip() {
    const tip = document.getElementById('riskGlobalFloatingTooltip');
    if (tip) tip.style.display = 'none';
}

// =========================================================================
// 1. RİSK BALONCUĞUNA TIKLANDIĞINDA TABLODA ODAKLANMA
// =========================================================================
function focusRiskRow(riskId) {
    const row = document.getElementById('risk-row-' + riskId);
    if (row) {
        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
        row.classList.remove('row-highlighted');
        void row.offsetWidth; // re-trigger animation
        row.classList.add('row-highlighted');
    }
}

// =========================================================================
// 2. AZALTMA EYLEMLERİ AKORDEONUNU AÇIP KAPATMA
// =========================================================================
function toggleMitigations(riskId) {
    const row = document.getElementById('mitigation-row-' + riskId);
    if (row) {
        row.style.display = (row.style.display === 'none' || row.style.display === '') ? 'table-row' : 'none';
    }
}

// =========================================================================
// 3. TABLO CANLI ARAMA MOTORU
// =========================================================================
function filterRiskTable() {
    const query = document.getElementById('riskTableSearch').value.toLowerCase();
    const rows = document.querySelectorAll('.risk-data-row');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
}

// =========================================================================
// 4. MODALDA DİNAMİK SKOR HESAPLAMA
// =========================================================================
function calcModalScore() {
    const p = parseInt(document.getElementById('modalProbSelect').value) || 1;
    const i = parseInt(document.getElementById('modalImpactSelect').value) || 1;
    const score = p * i;
    const badge = document.getElementById('modalCalculatedScore');

    let label = 'DÜŞÜK';
    let color = '#166534';
    if (score >= 15) {
        label = 'KRİTİK';
        color = '#991b1b';
    } else if (score >= 10) {
        label = 'YÜKSEK';
        color = '#9a3412';
    } else if (score >= 7) {
        label = 'ORTA';
        color = '#854d0e';
    }

    badge.textContent = score + ' (' + label + ')';
    badge.style.color = color;
}
</script>
@endsection