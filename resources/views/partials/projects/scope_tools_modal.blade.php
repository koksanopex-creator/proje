@php
    $tools = $p->scope_tools ?? [];
    if (!is_array($tools) && is_string($tools)) {
        $tools = json_decode($tools, true) ?: [];
    }
    $colors = ['#0284c7', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#64748b'];
@endphp

<!-- MODAL: KAPSAM & ANALİZ ARAÇLARI ÖZETİ (PROJE ID: {{ $p->id }}) -->
<div id="scopeToolsModal_{{ $p->id }}" class="custom-modal-backdrop" onclick="if(event.target === this) window.closeCustomModal('scopeToolsModal_{{ $p->id }}')" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 100000; backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 20px;">
    <div class="custom-modal-dialog" style="max-width: 820px; width: 100%; max-height: 88vh; display: flex; flex-direction: column; background: #ffffff; border-radius: 14px; box-shadow: 0 25px 50px -12px rgba(15,23,42,0.35); overflow: hidden; border: none;">
            
            <!-- MODAL BAŞLIĞI -->
            <div class="modal-header" style="background: linear-gradient(135deg, #0284c7 0%, #1e40af 100%); color: #ffffff; padding: 16px 22px; border-bottom: none;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center; font-size: 20px; backdrop-filter: blur(4px);">
                        🛠️
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="badge" style="background: rgba(255,255,255,0.25); color: #ffffff; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 6px;">
                                {{ $p->code }}
                            </span>
                            <h5 class="modal-title mb-0" id="scopeToolsModalLabel_{{ $p->id }}" style="font-size: 15px; font-weight: 800; color: #ffffff;">
                                {{ $p->title }}
                            </h5>
                        </div>
                        <p style="margin: 3px 0 0; font-size: 12px; color: rgba(255,255,255,0.85);">
                            Kullanılan Kapsam & Analiz Araçları Özeti ({{ $p->getActiveScopeToolsCount() }} Aktif Araç)
                        </p>
                    </div>
                </div>
                <button type="button" class="custom-modal-close" onclick="window.closeCustomModal('scopeToolsModal_{{ $p->id }}')" style="background: rgba(255,255,255,0.18); border: none; color: #ffffff; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 700; cursor: pointer; transition: all 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.35)'" onmouseout="this.style.background='rgba(255,255,255,0.18)'">✕</button>
            </div>

            <!-- MODAL GÖVDESİ -->
            <div class="custom-modal-body" style="padding: 20px 22px; background: #f8fafc; display: flex; flex-direction: column; gap: 16px; overflow-y: auto; max-height: calc(88vh - 130px);">
                
                <!-- 1. BÜTÇE DAĞILIM GRAFİĞİ -->
                @if($p->hasScopeTool('budget_chart'))
                    @php
                        $bChart = $tools['budget_chart'] ?? [];
                        $items = $bChart['items'] ?? [];
                        $total = (float)($bChart['total'] ?? 0);
                        if ($total <= 0) {
                            foreach ($items as $it) { $total += (float)($it['amount'] ?? 0); }
                        }
                    @endphp
                    @if(count($items) > 0)
                        <div style="background: #ffffff; border: 1.5px solid #bae6fd; border-radius: 10px; padding: 14px; box-shadow: 0 1px 4px rgba(2,132,199,0.05);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid #f0f9ff;">
                                <strong style="color: #0369a1; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                                    <span>📊</span> Bütçe Dağılım Grafiği (Kategori Kırılımı)
                                </strong>
                                <span style="font-size: 12px; font-weight: 800; color: #0284c7;">
                                    Toplam: {{ number_format($total, 2, ',', '.') }} {{ $p->currency ?? 'TRY' }}
                                </span>
                            </div>

                            <div style="display: grid; grid-template-columns: 140px 1fr; gap: 16px; align-items: center;">
                                <!-- SVG Mini Donut Chart -->
                                <div style="display: flex; justify-content: center; align-items: center;">
                                    @php
                                        $radius = 48;
                                        $circ = 2 * pi() * $radius; // ~301.59
                                        $offset = 0;
                                    @endphp
                                    <svg width="130" height="130" viewBox="0 0 140 140" style="transform: rotate(-90deg);">
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
                                                    stroke-dashoffset="-{{ $offset }}" />
                                            @php $offset += $dash; @endphp
                                        @endforeach
                                    </svg>
                                </div>

                                <!-- Kalem Listesi -->
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    @foreach($items as $idx => $it)
                                        @php
                                            $amt = (float)($it['amount'] ?? 0);
                                            $pct = $total > 0 ? round(($amt / $total) * 100, 1) : 0;
                                            $clr = $colors[$idx % count($colors)];
                                        @endphp
                                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 10px; display: flex; justify-content: space-between; align-items: center; font-size: 11.5px;">
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <span style="width: 8px; height: 8px; border-radius: 50%; background: {{ $clr }}; display: inline-block;"></span>
                                                <strong style="color: #334155;">{{ $it['label'] ?? 'Genel' }}</strong>
                                            </div>
                                            <div style="text-align: right;">
                                                <span style="font-weight: 800; color: #0f172a;">%{{ $pct }}</span>
                                                <span style="color: #64748b; font-size: 10.5px;">({{ number_format($amt, 2, ',', '.') }} {{ $p->currency }})</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                <!-- 2. SMART HEDEFLER & KPI METRİKLERİ -->
                @if($p->hasScopeTool('kpis'))
                    @php
                        $kpiData = $tools['kpis'] ?? [];
                        $kpiItems = $kpiData['items'] ?? [];
                    @endphp
                    @if(count($kpiItems) > 0)
                        <div style="background: #ffffff; border: 1.5px solid #bbf7d0; border-radius: 10px; padding: 14px; box-shadow: 0 1px 4px rgba(16,185,129,0.05);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #f0fdf4;">
                                <strong style="color: #166534; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                                    <span>🎯</span> SMART Hedefler & KPI Metrikleri
                                </strong>
                                <span class="badge" style="background: #dcfce7; color: #15803d; font-size: 11px;">
                                    {{ count($kpiItems) }} Metrik
                                </span>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                @foreach($kpiItems as $kpi)
                                    @php
                                        $base = is_numeric($kpi['baseline'] ?? null) ? (float)$kpi['baseline'] : null;
                                        $targ = is_numeric($kpi['target'] ?? null) ? (float)$kpi['target'] : null;
                                        $curr = is_numeric($kpi['current'] ?? null) ? (float)$kpi['current'] : null;
                                        $pct = null;
                                        if ($targ !== null && $curr !== null) {
                                            if ($base !== null && $targ != $base) {
                                                $pct = round((($curr - $base) / ($targ - $base)) * 100);
                                            } elseif ($targ > 0) {
                                                $pct = round(($curr / $targ) * 100);
                                            }
                                        }
                                        $pctClamped = $pct !== null ? min(max($pct, 0), 100) : 0;
                                        $color = ($pct !== null && $pct >= 80) ? '#10b981' : (($pct !== null && $pct >= 50) ? '#0284c7' : '#f59e0b');
                                    @endphp
                                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px; font-size: 11.5px;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                            <strong style="color: #0f172a; font-size: 12px;">{{ $kpi['title'] ?? '-' }}</strong>
                                            <span style="font-size: 11px; font-weight: 700; color: {{ $color }};">
                                                @if($pct !== null)
                                                    %{{ $pct }} Başarıldı
                                                @else
                                                    Takipte
                                                @endif
                                            </span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; align-items: center; color: #64748b; font-size: 11px; margin-bottom: 6px;">
                                            <span>Başlangıç: <strong>{{ $kpi['baseline'] ?: '-' }}</strong></span>
                                            <span>Hedef: <strong style="color: #0284c7;">🎯 {{ $kpi['target'] ?: '-' }} {{ $kpi['unit'] ?? '' }}</strong></span>
                                            <span>Mevcut: <strong style="color: #166534;">{{ $kpi['current'] ?: 'Girilmedi' }}</strong></span>
                                        </div>
                                        <div style="background: #e2e8f0; height: 6px; border-radius: 3px; overflow: hidden;">
                                            <div style="width: {{ $pctClamped }}%; height: 100%; background: {{ $color }}; transition: width 0.3s;"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif

                <!-- 3. KAPSAM SINIRLARI -->
                @if($p->hasScopeTool('scope_boundaries'))
                    @php
                        $sb = $tools['scope_boundaries'] ?? [];
                        $inScope = $sb['in_scope'] ?? [];
                        $outScope = $sb['out_scope'] ?? [];
                    @endphp
                    @if(count($inScope) > 0 || count($outScope) > 0)
                        <div style="background: #ffffff; border: 1.5px solid #fed7aa; border-radius: 10px; padding: 14px; box-shadow: 0 1px 4px rgba(234,88,12,0.05);">
                            <div style="margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #fff7ed;">
                                <strong style="color: #9a3412; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                                    <span>📌</span> Kapsam Sınırları (In-Scope vs. Out-of-Scope)
                                </strong>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                <!-- Kapsam İçi -->
                                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 10px;">
                                    <strong style="color: #166534; font-size: 11.5px; display: block; margin-bottom: 6px;">
                                        ✅ Kapsam İçi ({{ count($inScope) }})
                                    </strong>
                                    <div style="display: flex; flex-direction: column; gap: 4px; font-size: 11px;">
                                        @forelse($inScope as $item)
                                            <div style="color: #166534; font-weight: 600;">✓ {{ $item }}</div>
                                        @empty
                                            <span style="color: #94a3b8; font-style: italic;">Tanımlanmadı</span>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- Kapsam Dışı -->
                                <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 10px;">
                                    <strong style="color: #991b1b; font-size: 11.5px; display: block; margin-bottom: 6px;">
                                        🚫 Kapsam Dışı ({{ count($outScope) }})
                                    </strong>
                                    <div style="display: flex; flex-direction: column; gap: 4px; font-size: 11px;">
                                        @forelse($outScope as $item)
                                            <div style="color: #991b1b; font-weight: 600;">✕ {{ $item }}</div>
                                        @empty
                                            <span style="color: #94a3b8; font-style: italic;">Tanımlanmadı</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                <!-- 4. ROI & GERİ DÖNÜŞ HESAPLAYICI -->
                @if($p->hasScopeTool('roi_calculator'))
                    @php
                        $roi = $tools['roi_calculator'] ?? [];
                        $inv = (float)($roi['investment_cost'] ?? 0);
                        $savings = (float)($roi['annual_savings'] ?? 0);
                        $horizon = (int)($roi['horizon_years'] ?? 3);
                        $pbYears = $roi['payback_years'] ?? ($savings > 0 ? round($inv / $savings, 1) : '-');
                        $roiPct = $roi['roi_percentage'] ?? ($inv > 0 ? round(($savings / $inv) * 100, 1) : '-');
                        $netBenefit = $roi['net_benefit'] ?? (($savings * $horizon) - $inv);
                    @endphp
                    @if($inv > 0 || $savings > 0)
                        <div style="background: #ffffff; border: 1.5px solid #ddd6fe; border-radius: 10px; padding: 14px; box-shadow: 0 1px 4px rgba(109,40,217,0.05);">
                            <div style="margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #f5f3ff;">
                                <strong style="color: #6d28d9; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                                    <span>⚖️</span> ROI & Fizibilite Analizi ({{ $horizon }} Yıllık)
                                </strong>
                            </div>

                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 10px; text-align: center;">
                                <div style="background: #f5f3ff; border: 1px solid #c4b5fd; border-radius: 8px; padding: 8px;">
                                    <span style="font-size: 10.5px; color: #6d28d9; font-weight: 700;">⏱️ Geri Dönüş</span>
                                    <div style="font-size: 15px; font-weight: 850; color: #5b21b6; margin-top: 2px;">{{ $pbYears }} Yıl</div>
                                </div>
                                <div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 8px;">
                                    <span style="font-size: 10.5px; color: #166534; font-weight: 700;">📈 Yıllık ROI</span>
                                    <div style="font-size: 15px; font-weight: 850; color: #15803d; margin-top: 2px;">%{{ $roiPct }}</div>
                                </div>
                                <div style="background: #eff6ff; border: 1px solid #93c5fd; border-radius: 8px; padding: 8px;">
                                    <span style="font-size: 10.5px; color: #1d4ed8; font-weight: 700;">💰 Yıllık Tasarruf</span>
                                    <div style="font-size: 14px; font-weight: 850; color: #1e40af; margin-top: 2px;">{{ number_format($savings, 0, ',', '.') }} {{ $p->currency }}</div>
                                </div>
                                <div style="background: #fefce8; border: 1px solid #fde047; border-radius: 8px; padding: 8px;">
                                    <span style="font-size: 10.5px; color: #854d0e; font-weight: 700;">💵 {{ $horizon }} Yıllık Net</span>
                                    <div style="font-size: 14px; font-weight: 850; color: {{ $netBenefit >= 0 ? '#15803d' : '#dc2626' }}; margin-top: 2px;">
                                        {{ ($netBenefit >= 0 ? '+' : '') . number_format($netBenefit, 0, ',', '.') }} {{ $p->currency }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                <!-- 5. KRİTİK DIŞ BAĞIMLILIKLAR -->
                @if($p->hasScopeTool('dependencies'))
                    @php
                        $dep = $tools['dependencies'] ?? [];
                        $depItems = $dep['items'] ?? [];
                    @endphp
                    @if(count($depItems) > 0)
                        <div style="background: #ffffff; border: 1.5px solid #fecdd3; border-radius: 10px; padding: 14px;">
                            <div style="margin-bottom: 8px; padding-bottom: 6px; border-bottom: 1px solid #fff1f2;">
                                <strong style="color: #be123c; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                                    <span>🔗</span> Kritik Dış Bağımlılıklar ({{ count($depItems) }})
                                </strong>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 6px; font-size: 11.5px;">
                                @foreach($depItems as $it)
                                    <div style="background: #fff1f2; border: 1px solid #fecdd3; border-radius: 6px; padding: 6px 10px; display: flex; justify-content: space-between; align-items: center;">
                                        <span style="font-weight: 600; color: #9f1239;">{{ $it['title'] ?? '-' }}</span>
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <span style="font-size: 11px; color: #64748b;">{{ $it['vendor'] ?? '' }}</span>
                                            @if(!empty($it['date']))
                                                <span class="badge" style="background: #ffe4e6; color: #be123c;">📅 {{ $it['date'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif

                <!-- 6. ÖZEL PARAMETRELER -->
                @if($p->hasScopeTool('custom_fields'))
                    @php
                        $cFields = $tools['custom_fields'] ?? [];
                        $cfItems = $cFields['items'] ?? [];
                    @endphp
                    @if(count($cfItems) > 0)
                        <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 14px;">
                            <div style="margin-bottom: 8px; padding-bottom: 6px; border-bottom: 1px solid #f8fafc;">
                                <strong style="color: #334155; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                                    <span>🧩</span> Özel Proje Parametreleri ({{ count($cfItems) }})
                                </strong>
                            </div>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                @foreach($cfItems as $cf)
                                    <div style="background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; padding: 5px 10px; font-size: 11.5px;">
                                        <span style="color: #64748b; font-weight: 600;">{{ $cf['key'] ?? '' }}:</span>
                                        <strong style="color: #0f172a;">{{ $cf['value'] ?? '' }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif

            </div>

            <!-- MODAL ALTI -->
            <div class="custom-modal-footer" style="background: #ffffff; border-top: 1px solid #e2e8f0; padding: 12px 22px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 11.5px; color: #64748b; font-weight: 600;">
                    Köksan PMS Modüler Proje Kapsamı
                </span>
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.closeCustomModal('scopeToolsModal_{{ $p->id }}')" style="font-size: 12px; font-weight: 700; padding: 5px 14px; border-radius: 7px;">
                        Kapat
                    </button>
                    <a href="{{ route('projects.show', $p->id) }}" class="btn btn-sm btn-primary" style="font-size: 12px; font-weight: 750; padding: 5px 16px; border-radius: 7px; display: inline-flex; align-items: center; gap: 6px; background: #0284c7; border-color: #0284c7;">
                        <span>🔍 Proje Detayına Git</span>
                        <span>→</span>
                    </a>
                </div>
            </div>

            </div>
</div>
