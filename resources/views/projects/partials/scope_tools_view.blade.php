@php
    $tools = $project->scope_tools ?? [];
    if (!is_array($tools) && is_string($tools)) {
        $tools = json_decode($tools, true) ?: [];
    }
    $activeCount = $project->getActiveScopeToolsCount();
    $colors = ['#0284c7', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#64748b'];
@endphp

@if($activeCount > 0)
<div class="card mb-4" id="scopeToolsViewCard" style="border: 1px solid #cbd5e1; border-radius: 12px; box-shadow: 0 2px 8px rgba(15,23,42,0.03); overflow: hidden; background: #ffffff; margin-top: 18px;">
    <!-- KART BAŞLIĞI -->
    <div style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); padding: 14px 20px; border-bottom: 1.5px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 34px; height: 34px; border-radius: 8px; background: linear-gradient(135deg, #0284c7, #2563eb); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 17px; box-shadow: 0 2px 5px rgba(2,132,199,0.25);">
                🛠️
            </div>
            <div>
                <h4 style="margin: 0; font-size: 14px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <span>Kapsam, Analiz & Proje Araçları</span>
                    <span class="badge" style="background: #e0f2fe; color: #0284c7; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 6px; border: 1px solid #bae6fd;">
                        {{ $activeCount }} Araç Aktif
                    </span>
                </h4>
                <p style="margin: 2px 0 0; font-size: 11.5px; color: #64748b;">
                    Bu proje için tanımlanmış analitik grafikler, KPI radarı, fizibilite ve kapsam sınırları.
                </p>
            </div>
        </div>

        @can('proje.edit')
            @if(isset($canManage) && $canManage)
                <a href="{{ route('projects.edit', $project->id) }}#scopeToolsMainCard" class="btn btn-xs btn-outline-secondary" style="font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 6px;">
                    ✏️ Araçları Düzenle
                </a>
            @endif
        @endcan
    </div>

    <div style="padding: 18px 20px; display: flex; flex-direction: column; gap: 18px;">

        <!-- 1. BÜTÇE DAĞILIM GRAFİĞİ (DONUT & KATEGORİ KIRILIMI) -->
        @if($project->hasScopeTool('budget_chart'))
            @php
                $bChart = $tools['budget_chart'] ?? [];
                $items = $bChart['items'] ?? [];
                $total = (float)($bChart['total'] ?? 0);
                if ($total <= 0) {
                    foreach ($items as $it) { $total += (float)($it['amount'] ?? 0); }
                }
            @endphp
            @if(count($items) > 0)
                <div style="background: #ffffff; border: 1.5px solid #bae6fd; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 4px rgba(2,132,199,0.04);">
                    <div style="background: #f0f9ff; padding: 10px 16px; border-bottom: 1px solid #bae6fd; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 16px;">📊</span>
                            <strong style="font-size: 13px; color: #0369a1;">Bütçe Dağılım Grafiği (Donut & Kategori Kırılımı)</strong>
                        </div>
                        <span style="font-size: 12px; font-weight: 800; color: #0284c7;">
                            Toplam: {{ number_format($total, 2, ',', '.') }} {{ $project->currency ?? 'TRY' }}
                        </span>
                    </div>
                    <div style="padding: 16px;">
                        
                        <div style="display: grid; grid-template-columns: 160px 1fr; gap: 20px; align-items: center; margin-bottom: 14px;">
                            <!-- SVG Donut Chart -->
                            <div style="display: flex; justify-content: center; align-items: center;">
                                @php
                                    $radius = 48;
                                    $circ = 2 * pi() * $radius; // ~301.59
                                    $offset = 0;
                                @endphp
                                <svg width="150" height="150" viewBox="0 0 140 140" style="transform: rotate(-90deg);">
                                    <circle cx="70" cy="70" r="{{ $radius }}" fill="transparent" stroke="#f1f5f9" stroke-width="18" />
                                    @foreach($items as $idx => $it)
                                        @php
                                            $amt = (float)($it['amount'] ?? 0);
                                            $pct = $total > 0 ? ($amt / $total) * 100 : 0;
                                            $dash = ($pct / 100) * $circ;
                                            $clr = $colors[$idx % count($colors)];
                                        @endphp
                                        <circle cx="70" cy="70" r="{{ $radius }}" fill="transparent" 
                                                stroke="{{ $clr }}" stroke-width="18" 
                                                stroke-dasharray="{{ $dash }} {{ $circ }}" 
                                                stroke-dashoffset="-{{ $offset }}"
                                                style="transition: all 0.3s;">
                                            <title>{{ $it['label'] ?? '' }}: %{{ round($pct, 1) }}</title>
                                        </circle>
                                        @php $offset += $dash; @endphp
                                    @endforeach
                                </svg>
                            </div>

                            <!-- Lejant Grid'i -->
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                                @foreach($items as $idx => $it)
                                    @php
                                        $amt = (float)($it['amount'] ?? 0);
                                        $pct = $total > 0 ? round(($amt / $total) * 100, 1) : 0;
                                        $clr = $colors[$idx % count($colors)];
                                    @endphp
                                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px; display: flex; flex-direction: column; gap: 6px;">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span style="width: 10px; height: 10px; border-radius: 50%; background: {{ $clr }}; display: inline-block;"></span>
                                                <strong style="font-size: 12px; color: #334155;">{{ $it['label'] ?? 'Genel' }}</strong>
                                            </div>
                                            <span style="font-weight: 800; font-size: 12.5px; color: #0f172a;">%{{ $pct }}</span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 11px; color: #64748b;">
                                            <span>Tutar:</span>
                                            <strong style="color: #0f172a;">{{ number_format($amt, 2, ',', '.') }} {{ $project->currency }}</strong>
                                        </div>
                                        <div style="background: #e2e8f0; height: 5px; border-radius: 3px; overflow: hidden;">
                                            <div style="width: {{ $pct }}%; height: 100%; background: {{ $clr }};"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Segmentli Dağılım Barı -->
                        <div style="font-size: 10.5px; font-weight: 600; color: #64748b; margin-bottom: 4px;">Kümülatif Bütçe Dağılım Çizgisi:</div>
                        <div style="height: 14px; border-radius: 6px; background: #e2e8f0; display: flex; overflow: hidden;">
                            @foreach($items as $idx => $it)
                                @php
                                    $amt = (float)($it['amount'] ?? 0);
                                    $pct = $total > 0 ? round(($amt / $total) * 100, 1) : 0;
                                    $clr = $colors[$idx % count($colors)];
                                @endphp
                                <div style="width: {{ $pct }}%; background: {{ $clr }}; height: 100%; transition: width 0.3s;" title="{{ $it['label'] ?? '' }}: %{{ $pct }}"></div>
                            @endforeach
                        </div>

                    </div>
                </div>
            @endif
        @endif

        <!-- 2. SMART HEDEFLER & KPI METRİKLERİ (CANLI GRAFİKLERLE) -->
        @if($project->hasScopeTool('kpis'))
            @php
                $kpis = $tools['kpis'] ?? [];
                $kpiItems = $kpis['items'] ?? [];
                
                // İstatistik hesaplamaları
                $totalKpi = count($kpiItems);
                $totalPct = 0;
                $validCount = 0;
                $achievedCount = 0;
                $inProgressCount = 0;

                foreach($kpiItems as $kpi) {
                    $bNum = is_numeric($kpi['baseline'] ?? null) ? (float)$kpi['baseline'] : null;
                    $tNum = is_numeric($kpi['target'] ?? null) ? (float)$kpi['target'] : null;
                    $cNum = is_numeric($kpi['current'] ?? null) ? (float)$kpi['current'] : null;

                    if ($tNum !== null && $cNum !== null) {
                        if ($bNum !== null && $tNum != $bNum) {
                            $pct = round((($cNum - $bNum) / ($tNum - $bNum)) * 100);
                        } elseif ($tNum > 0) {
                            $pct = round(($cNum / $tNum) * 100);
                        } else {
                            $pct = 0;
                        }
                        $totalPct += $pct;
                        $validCount++;
                        if ($pct >= 80) $achievedCount++;
                        else $inProgressCount++;
                    }
                }
                $avgPct = $validCount > 0 ? round($totalPct / $validCount) : 0;
                $clampedAvg = min(max($avgPct, 0), 100);
                $radarColor = $avgPct >= 80 ? '#10b981' : ($avgPct >= 50 ? '#0284c7' : '#f59e0b');
            @endphp
            @if(count($kpiItems) > 0)
                <div style="background: #ffffff; border: 1.5px solid #bbf7d0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 4px rgba(16,185,129,0.04);">
                    <div style="background: #f0fdf4; padding: 10px 16px; border-bottom: 1px solid #bbf7d0; display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 16px;">🎯</span>
                            <strong style="font-size: 13px; color: #166534;">SMART Hedefler & KPI Başarı Radarı</strong>
                        </div>
                        <span class="badge" style="background: #dcfce7; color: #15803d; font-size: 11px; font-weight: 700;">
                            {{ count($kpiItems) }} Metrik
                        </span>
                    </div>

                    <div style="padding: 16px;">
                        <!-- KPI RADAR BİLGİ ŞERİDİ -->
                        <div style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border: 1.5px solid #a7f3d0; border-radius: 9px; padding: 12px 16px; margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                            <div style="display: flex; align-items: center; gap: 14px;">
                                @php
                                    $circRadar = 2 * pi() * 18; // ~113.1
                                    $dashRadar = ($clampedAvg / 100) * $circRadar;
                                @endphp
                                <div style="width: 44px; height: 44px; position: relative; flex-shrink: 0;">
                                    <svg width="44" height="44" viewBox="0 0 44 44" style="transform: rotate(-90deg);">
                                        <circle cx="22" cy="22" r="18" fill="transparent" stroke="#d1fae5" stroke-width="5"></circle>
                                        <circle cx="22" cy="22" r="18" fill="transparent" stroke="{{ $radarColor }}" stroke-width="5" stroke-dasharray="{{ $dashRadar }} {{ $circRadar }}" stroke-linecap="round"></circle>
                                    </svg>
                                    <span style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 10.5px; font-weight: 850; color: {{ $radarColor }};">
                                        %{{ $avgPct }}
                                    </span>
                                </div>
                                <div>
                                    <strong style="color: #065f46; font-size: 13px; display: block;">Genel KPI Başarı & Hedef Ortalaması</strong>
                                    <span style="font-size: 11.5px; color: #047857;">
                                        {{ $validCount > 0 ? "{$validCount} metrik üzerinden ortalama %{$avgPct} gerçekleşme sağlandı." : 'Metrik gerçekleşmeleri takip ediliyor.' }}
                                    </span>
                                </div>
                            </div>

                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <span class="badge" style="background: #ffffff; color: #166534; border: 1px solid #bbf7d0; font-size: 11px; padding: 4px 9px;">{{ $totalKpi }} KPI</span>
                                <span class="badge" style="background: #10b981; color: #ffffff; font-size: 11px; padding: 4px 9px;">{{ $achievedCount }} Hedefte</span>
                                <span class="badge" style="background: #0284c7; color: #ffffff; font-size: 11px; padding: 4px 9px;">{{ $inProgressCount }} İlerlemede</span>
                            </div>
                        </div>

                        <!-- TABLO & CANLI GRAFİKLER -->
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 12.5px;">
                                <thead style="background: #f8fafc; color: #475569; font-weight: 700; font-size: 11.5px;">
                                    <tr>
                                        <th style="padding: 10px 14px;">Hedef / Metrik Tanımı</th>
                                        <th style="padding: 10px 14px; width: 120px; text-align: center;">Başlangıç</th>
                                        <th style="padding: 10px 14px; width: 120px; text-align: center;">Hedeflenen</th>
                                        <th style="padding: 10px 14px; width: 120px; text-align: center;">Gerçekleşen</th>
                                        <th style="padding: 10px 14px; width: 80px; text-align: center;">Birim</th>
                                        <th style="padding: 10px 14px; width: 185px;">📊 Canlı Başarı Grafiği</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($kpiItems as $kpi)
                                        @php
                                            $bNum = is_numeric($kpi['baseline'] ?? null) ? (float)$kpi['baseline'] : null;
                                            $tNum = is_numeric($kpi['target'] ?? null) ? (float)$kpi['target'] : null;
                                            $cNum = is_numeric($kpi['current'] ?? null) ? (float)$kpi['current'] : null;

                                            $pct = null;
                                            if ($tNum !== null && $cNum !== null) {
                                                if ($bNum !== null && $tNum != $bNum) {
                                                    $pct = round((($cNum - $bNum) / ($tNum - $bNum)) * 100);
                                                } elseif ($tNum > 0) {
                                                    $pct = round(($cNum / $tNum) * 100);
                                                }
                                            }
                                            $clamped = $pct !== null ? min(max($pct, 0), 100) : 0;
                                            $rowColor = ($pct !== null && $pct >= 80) ? '#10b981' : (($pct !== null && $pct >= 50) ? '#0284c7' : '#f59e0b');
                                            $circRow = 2 * pi() * 14; // ~87.96
                                            $dashRow = ($clamped / 100) * $circRow;
                                        @endphp
                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                            <td style="padding: 10px 14px;">
                                                <strong style="color: #0f172a; font-size: 13px;">{{ $kpi['title'] ?? '-' }}</strong>
                                            </td>
                                            <td style="padding: 10px 14px; text-align: center; color: #64748b; font-weight: 600;">
                                                {{ $kpi['baseline'] ?: '-' }}
                                            </td>
                                            <td style="padding: 10px 14px; text-align: center;">
                                                <span style="font-weight: 800; color: #0284c7; background: #e0f2fe; padding: 2px 10px; border-radius: 6px; font-size: 12px;">
                                                    🎯 {{ $kpi['target'] ?: '-' }}
                                                </span>
                                            </td>
                                            <td style="padding: 10px 14px; text-align: center;">
                                                @if(!empty($kpi['current']))
                                                    <span style="font-weight: 800; color: #166534; background: #dcfce7; padding: 2px 10px; border-radius: 6px; font-size: 12px;">
                                                        {{ $kpi['current'] }}
                                                    </span>
                                                @else
                                                    <span style="color: #94a3b8; font-style: italic;">Takipte</span>
                                                @endif
                                            </td>
                                            <td style="padding: 10px 14px; text-align: center; color: #475569; font-weight: 600;">
                                                {{ $kpi['unit'] ?? '%' }}
                                            </td>
                                            <td style="padding: 8px 14px;">
                                                @if($pct !== null)
                                                    <div style="display: flex; align-items: center; gap: 8px;">
                                                        <div style="position: relative; width: 34px; height: 34px; flex-shrink: 0;">
                                                            <svg width="34" height="34" viewBox="0 0 34 34" style="transform: rotate(-90deg);">
                                                                <circle cx="17" cy="17" r="14" fill="transparent" stroke="#f1f5f9" stroke-width="4"></circle>
                                                                <circle cx="17" cy="17" r="14" fill="transparent" stroke="{{ $rowColor }}" stroke-width="4" stroke-dasharray="{{ $dashRow }} {{ $circRow }}" stroke-linecap="round"></circle>
                                                            </svg>
                                                            <span style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 850; color: {{ $rowColor }};">
                                                                %{{ $pct }}
                                                            </span>
                                                        </div>
                                                        <div style="display: flex; flex-direction: column; gap: 2px;">
                                                            <span style="font-size: 10px; font-weight: 800; color: {{ $rowColor }};">
                                                                {{ $pct >= 100 ? '✓ Hedefe Ulaşıldı' : ($pct >= 50 ? 'İlerleme Var' : 'Kritik / Başlangıç') }}
                                                            </span>
                                                            <div style="background: #e2e8f0; height: 5px; width: 90px; border-radius: 3px; overflow: hidden;">
                                                                <div style="width: {{ $clamped }}%; height: 100%; background: {{ $rowColor }};"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 10px;">Takipte</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        <!-- 3. KAPSAM SINIRLARI (DAHİL / HARİÇ) -->
        @if($project->hasScopeTool('scope_boundaries'))
            @php
                $sb = $tools['scope_boundaries'] ?? [];
                $inScope = $sb['in_scope'] ?? [];
                $outScope = $sb['out_scope'] ?? [];
            @endphp
            @if(count($inScope) > 0 || count($outScope) > 0)
                <div style="background: #ffffff; border: 1.5px solid #fed7aa; border-radius: 10px; overflow: hidden;">
                    <div style="background: #fff7ed; padding: 10px 16px; border-bottom: 1px solid #fed7aa; display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 16px;">📌</span>
                            <strong style="font-size: 13px; color: #9a3412;">Kapsam Sınırları Listesi (In-Scope vs. Out-of-Scope)</strong>
                        </div>
                        <span class="badge" style="background: #ffedd5; color: #c2410c; font-size: 10.5px;">
                            Kapsam Kaymasını Önler
                        </span>
                    </div>
                    <div style="padding: 16px;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                            <!-- Sol: Kapsam İçi -->
                            <div style="background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 8px; padding: 14px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 1px solid #bbf7d0; padding-bottom: 6px;">
                                    <strong style="color: #166534; font-size: 12.5px; display: flex; align-items: center; gap: 6px;">
                                        <span>✅</span> Kapsam İçi (Taahhüt Edilenler)
                                    </strong>
                                    <span class="badge" style="background: #dcfce7; color: #166534; font-size: 11px;">
                                        {{ count($inScope) }} Madde
                                    </span>
                                </div>
                                @if(count($inScope) > 0)
                                    <div style="display: flex; flex-direction: column; gap: 6px;">
                                        @foreach($inScope as $inItem)
                                            <div style="background: #ffffff; border: 1px solid #bbf7d0; border-radius: 6px; padding: 6px 10px; font-size: 12px; color: #166534; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                                                <span>✓</span> <span>{{ $inItem }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span style="color: #94a3b8; font-size: 11.5px; font-style: italic;">Belirtilmedi.</span>
                                @endif
                            </div>

                            <!-- Sağ: Kapsam Dışı -->
                            <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 14px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; border-bottom: 1px solid #fecaca; padding-bottom: 6px;">
                                    <strong style="color: #991b1b; font-size: 12.5px; display: flex; align-items: center; gap: 6px;">
                                        <span>🚫</span> Kapsam Dışı (Dahil Edilmeyenler / Faz 2)
                                    </strong>
                                    <span class="badge" style="background: #fee2e2; color: #991b1b; font-size: 11px;">
                                        {{ count($outScope) }} Madde
                                    </span>
                                </div>
                                @if(count($outScope) > 0)
                                    <div style="display: flex; flex-direction: column; gap: 6px;">
                                        @foreach($outScope as $outItem)
                                            <div style="background: #ffffff; border: 1px solid #fecaca; border-radius: 6px; padding: 6px 10px; font-size: 12px; color: #991b1b; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                                                <span>✕</span> <span>{{ $outItem }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span style="color: #94a3b8; font-size: 11.5px; font-style: italic;">Belirtilmedi.</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        <!-- 4. ROI, TASARRUF & GERİ DÖNÜŞ HESAPLAYICI -->
        @if($project->hasScopeTool('roi_calculator'))
            @php
                $roi = $tools['roi_calculator'] ?? [];
                $inv = (float)($roi['investment_cost'] ?? 0);
                $savings = (float)($roi['annual_savings'] ?? 0);
                $horizon = (int)($roi['horizon_years'] ?? 3);
                $pbYears = $roi['payback_years'] ?? ($savings > 0 ? round($inv / $savings, 1) : '-');
                $pbMonths = $roi['payback_months'] ?? ($savings > 0 ? round(($inv / $savings) * 12) : '-');
                $roiPct = $roi['roi_percentage'] ?? ($inv > 0 ? round(($savings / $inv) * 100, 1) : '-');
                $netBenefit = $roi['net_benefit'] ?? (($savings * $horizon) - $inv);
            @endphp
            @if($inv > 0 || $savings > 0)
                <div style="background: #ffffff; border: 1.5px solid #ddd6fe; border-radius: 10px; overflow: hidden;">
                    <div style="background: #f5f3ff; padding: 10px 16px; border-bottom: 1px solid #ddd6fe; display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 16px;">⚖️</span>
                            <strong style="font-size: 13px; color: #6d28d9;">Fizibilite, Tasarruf & ROI (Geri Dönüş) Analizi</strong>
                        </div>
                        <span class="badge" style="background: #ede9fe; color: #6d28d9; font-size: 10.5px;">
                            {{ $horizon }} Yıllık Projeksiyon
                        </span>
                    </div>
                    <div style="padding: 16px;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px;">
                            <!-- Geri Dönüş Süresi -->
                            <div style="background: linear-gradient(135deg, #f8fafc 0%, #f5f3ff 100%); border: 1.5px solid #c4b5fd; border-radius: 10px; padding: 14px 16px;">
                                <span style="font-size: 11px; font-weight: 700; color: #6d28d9; text-transform: uppercase;">⏱️ Geri Dönüş Süresi</span>
                                <div style="font-size: 18px; font-weight: 900; color: #5b21b6; margin: 4px 0 2px 0;">
                                    {{ $pbYears }} Yıl <span style="font-size: 12.5px; font-weight: 700; color: #7c3aed;">({{ $pbMonths }} Ay)</span>
                                </div>
                                <span style="font-size: 10.5px; color: #6b21a8;">Amortisman süresi</span>
                            </div>

                            <!-- Yıllık ROI -->
                            <div style="background: linear-gradient(135deg, #f8fafc 0%, #f0fdf4 100%); border: 1.5px solid #86efac; border-radius: 10px; padding: 14px 16px;">
                                <span style="font-size: 11px; font-weight: 700; color: #166534; text-transform: uppercase;">📈 Yıllık ROI Oranı</span>
                                <div style="font-size: 18px; font-weight: 900; color: #15803d; margin: 4px 0 2px 0;">
                                    %{{ $roiPct }}
                                </div>
                                <span style="font-size: 10.5px; color: #16a34a;">Yıllık net getiri</span>
                            </div>

                            <!-- Yıllık Net Tasarruf -->
                            <div style="background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%); border: 1.5px solid #93c5fd; border-radius: 10px; padding: 14px 16px;">
                                <span style="font-size: 11px; font-weight: 700; color: #1d4ed8; text-transform: uppercase;">💰 Yıllık Net Tasarruf</span>
                                <div style="font-size: 18px; font-weight: 900; color: #1e40af; margin: 4px 0 2px 0;">
                                    {{ number_format($savings, 2, ',', '.') }} {{ $project->currency }}
                                </div>
                                <span style="font-size: 10.5px; color: #2563eb;">Yıllık tasarruf / nakit akışı</span>
                            </div>

                            <!-- Kümülatif Net Katkı -->
                            <div style="background: linear-gradient(135deg, #f8fafc 0%, #fefce8 100%); border: 1.5px solid #fde047; border-radius: 10px; padding: 14px 16px;">
                                <span style="font-size: 11px; font-weight: 700; color: #854d0e; text-transform: uppercase;">💵 {{ $horizon }} Yıllık Net Kazanç</span>
                                <div style="font-size: 18px; font-weight: 900; color: {{ $netBenefit >= 0 ? '#15803d' : '#dc2626' }}; margin: 4px 0 2px 0;">
                                    {{ ($netBenefit >= 0 ? '+' : '') . number_format($netBenefit, 2, ',', '.') }} {{ $project->currency }}
                                </div>
                                <span style="font-size: 10.5px; color: #a16207;">Yatırım sonrası net kazanç</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        <!-- 5. KRİTİK DIŞ BAĞIMLILIKLAR & VARSAYIMLAR -->
        @if($project->hasScopeTool('dependencies'))
            @php
                $dep = $tools['dependencies'] ?? [];
                $depItems = $dep['items'] ?? [];
                $critColors = [
                    'low' => ['bg' => '#f1f5f9', 'color' => '#475569', 'label' => 'Düşük'],
                    'medium' => ['bg' => '#fef3c7', 'color' => '#b45309', 'label' => 'Orta'],
                    'high' => ['bg' => '#ffedd5', 'color' => '#c2410c', 'label' => 'Yüksek'],
                    'critical' => ['bg' => '#fee2e2', 'color' => '#b91c1c', 'label' => 'Kritik / Bloke']
                ];
            @endphp
            @if(count($depItems) > 0)
                <div style="background: #ffffff; border: 1.5px solid #fecdd3; border-radius: 10px; overflow: hidden;">
                    <div style="background: #fff1f2; padding: 10px 16px; border-bottom: 1px solid #fecdd3; display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 16px;">🔗</span>
                            <strong style="font-size: 13px; color: #be123c;">Kritik Dış Bağımlılıklar & Kilometre Taşları</strong>
                        </div>
                        <span class="badge" style="background: #ffe4e6; color: #e11d48; font-size: 10.5px;">
                            {{ count($depItems) }} Dış Bağımlılık
                        </span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 12.5px;">
                            <thead style="background: #f8fafc; color: #475569; font-weight: 700; font-size: 11.5px;">
                                <tr>
                                    <th style="padding: 10px 14px;">Bağımlılık / Varsayım Tanımı</th>
                                    <th style="padding: 10px 14px; width: 220px;">İlgili Dış Taraf / Tedarikçi</th>
                                    <th style="padding: 10px 14px; width: 140px; text-align: center;">Kritiklik Seviyesi</th>
                                    <th style="padding: 10px 14px; width: 140px; text-align: center;">Hedef Tarih</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($depItems as $depItem)
                                    @php
                                        $lvl = $depItem['critical_level'] ?? 'medium';
                                        $cConf = $critColors[$lvl] ?? $critColors['medium'];
                                    @endphp
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 10px 14px;">
                                            <strong style="color: #0f172a; font-size: 13px;">{{ $depItem['title'] ?? '-' }}</strong>
                                        </td>
                                        <td style="padding: 10px 14px; color: #334155; font-weight: 600;">
                                            🏢 {{ $depItem['owner'] ?: 'Belirtilmedi' }}
                                        </td>
                                        <td style="padding: 10px 14px; text-align: center;">
                                            <span class="badge" style="background: {{ $cConf['bg'] }}; color: {{ $cConf['color'] }}; font-size: 11px; padding: 3px 9px;">
                                                {{ $cConf['label'] }}
                                            </span>
                                        </td>
                                        <td style="padding: 10px 14px; text-align: center; color: #64748b; font-weight: 600;">
                                            @if(!empty($depItem['target_date']))
                                                📅 {{ \Carbon\Carbon::parse($depItem['target_date'])->format('d.m.Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endif

        <!-- 6. ÖZEL PARAMETRELER / ALANLAR -->
        @if($project->hasScopeTool('custom_fields'))
            @php
                $cFields = $tools['custom_fields'] ?? [];
                $cfItems = $cFields['items'] ?? [];
            @endphp
            @if(count($cfItems) > 0)
                <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; overflow: hidden;">
                    <div style="background: #f8fafc; padding: 10px 16px; border-bottom: 1px solid #cbd5e1; display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 16px;">🧩</span>
                            <strong style="font-size: 13px; color: #334155;">Özel Parametreler / Tanımlı Alanlar</strong>
                        </div>
                        <span class="badge" style="background: #e2e8f0; color: #475569; font-size: 10.5px;">
                            {{ count($cfItems) }} Parametre
                        </span>
                    </div>
                    <div style="padding: 16px;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 12px;">
                            @foreach($cfItems as $cf)
                                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: #64748b; font-size: 12px; font-weight: 600;">{{ $cf['label'] ?? '' }}</span>
                                    <strong style="color: #0f172a; font-size: 12.5px;">{{ $cf['value'] ?? '-' }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        @endif

    </div>
</div>
@endif
