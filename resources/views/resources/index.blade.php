@extends('layouts.app')

@section('title', 'Kaynak İş Yükü Isı Haritası')
@section('page_title', 'Kaynak Kapasite & İş Yükü Isı Haritası (Workload Heatmap)')
@section('page_subtitle', 'Personel ve ekipmanların haftalık doluluk matrisi, çakışma radarı ve görev dökümü')

@section('content')
<style>
/* Isı Haritası Hücre Stilleri */
.heatmap-cell {
    padding: 8px 10px;
    border-radius: 6px;
    text-align: center;
    font-size: 11.5px;
    font-weight: 700;
    transition: all 0.15s ease;
    user-select: none;
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-height: 44px;
}
.heatmap-cell:hover {
    transform: scale(1.05);
    box-shadow: 0 3px 8px rgba(0,0,0,0.1);
    z-index: 5;
}
.cell-overloaded {
    background: #fee2e2;
    color: #991b1b;
    border: 1.5px solid #fca5a5;
    font-weight: 800;
}
.cell-optimal {
    background: #dcfce7;
    color: #166534;
    border: 1.5px solid #86efac;
}
.cell-underloaded {
    background: #e0f2fe;
    color: #0369a1;
    border: 1.5px solid #bae6fd;
}
.cell-idle {
    background: #f8fafc;
    color: #94a3b8;
    border: 1px dashed #cbd5e1;
    font-weight: normal;
}

/* Akordeon Genişletme Oku */
.tree-toggle-arrow {
    display: inline-block;
    transition: transform 0.2s ease;
    font-size: 11px;
    color: #64748b;
    cursor: pointer;
    margin-right: 6px;
}
.tree-toggle-arrow.open {
    transform: rotate(90deg);
    color: #0284c7;
}

/* Lejant Renk Kutuları */
.legend-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11.5px;
    font-weight: 600;
    color: #334155;
}
.legend-color-box {
    width: 14px;
    height: 14px;
    border-radius: 4px;
    display: inline-block;
}
</style>

    <!-- 1. ÜST KPI ÖZET KARTLARI -->
    <div class="grid-4" style="gap: 16px; margin-bottom: 24px;">
        <div class="card" style="border-radius: 10px; border-left: 4px solid #ef4444; padding: 16px 18px;">
            <div style="font-size: 12px; font-weight: 700; color: #dc2626; text-transform: uppercase;">Aşırı Yüklü / Çakışma</div>
            <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 6px;">
                <span style="font-size: 26px; font-weight: 800; color: #0f172a;">{{ $summary['overloaded_count'] }}</span>
                <span class="badge" style="background: #fef2f2; color: #991b1b; font-size: 11px; font-weight: 700;">> 45 Saat / Hafta</span>
            </div>
            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Kapasite darboğazı uyarısı</div>
        </div>

        <div class="card" style="border-radius: 10px; border-left: 4px solid #10b981; padding: 16px 18px;">
            <div style="font-size: 12px; font-weight: 700; color: #059669; text-transform: uppercase;">Optimal / Dengeli</div>
            <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 6px;">
                <span style="font-size: 26px; font-weight: 800; color: #0f172a;">{{ $summary['optimal_count'] }}</span>
                <span class="badge" style="background: #ecfdf5; color: #065f46; font-size: 11px; font-weight: 700;">35 - 45 Saat</span>
            </div>
            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Maksimum verimlilik aralığı</div>
        </div>

        <div class="card" style="border-radius: 10px; border-left: 4px solid #0284c7; padding: 16px 18px;">
            <div style="font-size: 12px; font-weight: 700; color: #0284c7; text-transform: uppercase;">Atıl / Müsait Kapasite</div>
            <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 6px;">
                <span style="font-size: 26px; font-weight: 800; color: #0f172a;">{{ $summary['underloaded_count'] }}</span>
                <span class="badge" style="background: #f0f9ff; color: #0369a1; font-size: 11px; font-weight: 700;">< 35 Saat</span>
            </div>
            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Yeni görev atanabilir</div>
        </div>

        <div class="card" style="border-radius: 10px; border-left: 4px solid #6366f1; padding: 16px 18px;">
            <div style="font-size: 12px; font-weight: 700; color: #4f46e5; text-transform: uppercase;">Toplam Planlanan Efor</div>
            <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 6px;">
                <span style="font-size: 26px; font-weight: 800; color: #0f172a;">{{ round($summary['total_company_hours'], 1) }}h</span>
                <span class="badge" style="background: #eef2ff; color: #4338ca; font-size: 11px; font-weight: 700;">{{ $summary['active_assigned_users'] }} Personel</span>
            </div>
            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Önümüzdeki 5 haftalık toplam</div>
        </div>
    </div>

    <!-- 2. KRİTİK ÇAKIŞMA UYARI PANELİ -->
    @if($conflicts->count() > 0)
        <div class="card mb-4" style="border-radius: 10px; border-left: 4px solid #ef4444; background: #fff; box-shadow: 0 2px 8px rgba(239,68,68,0.06);">
            <div class="card-header" style="background: #fef2f2; border-bottom: 1px solid #fee2e2; padding: 12px 18px;">
                <span class="card-title" style="color: #991b1b; font-size: 13.5px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                    <span>⚠️</span> Kritik Kaynak Çakışmaları ve Kapasite Darboğazı Uyarısı ({{ $conflicts->count() }} Personel)
                </span>
            </div>
            <div class="card-body" style="padding: 14px 18px;">
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    @foreach($conflicts as $c)
                        <div style="padding: 10px 14px; background: #ffffff; border: 1px solid #fecaca; border-radius: 8px; font-size: 12.5px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                            <div>
                                <strong style="color: #991b1b;">{{ $c['name'] }}</strong> ({{ $c['unvan'] }}) — 
                                <span style="color: #475569;">Toplam Aktif Görev: <strong>{{ $c['task_count'] }}</strong> | Toplam Efor: <strong>{{ $c['total_hours'] }} Saat</strong></span>
                            </div>
                            <span class="badge" style="background: #dc2626; color: #fff; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 6px;">
                                AŞIRI YÜKLÜ (%{{ $c['capacity_percentage'] }})
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- 3. FİLTRE VE LEJANT ÇUBUĞU -->
    <div class="card mb-3" style="border-radius: 10px; border: 1px solid #e2e8f0; background: #ffffff;">
        <div class="card-body" style="padding: 12px 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
            
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <!-- Canlı Arama Çubuğu -->
                <div style="display: flex; align-items: center; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 10px;">
                    <span style="font-size: 13px; color: #64748b;">🔍</span>
                    <input type="text" id="resourceSearch" placeholder="Personel adı, unvan veya departman ara..." onkeyup="filterResourceHeatmap()" style="border: none; background: transparent; padding: 6px 8px; font-size: 12px; outline: none; width: 260px;">
                </div>

                <!-- Durum Filtre Butonları -->
                <div style="display: inline-flex; gap: 4px; background: #f1f5f9; padding: 3px; border-radius: 6px;">
                    <button type="button" class="btn btn-xs filter-btn active" onclick="filterByStatus('all', this)" style="padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 5px; border: none; background: #0284c7; color: #fff;">Tümü</button>
                    <button type="button" class="btn btn-xs filter-btn" onclick="filterByStatus('overloaded', this)" style="padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 5px; border: none; background: transparent; color: #64748b;">⚠️ Çakışanlar</button>
                    <button type="button" class="btn btn-xs filter-btn" onclick="filterByStatus('optimal', this)" style="padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 5px; border: none; background: transparent; color: #64748b;">✓ Optimal</button>
                    <button type="button" class="btn btn-xs filter-btn" onclick="filterByStatus('underloaded', this)" style="padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 5px; border: none; background: transparent; color: #64748b;">Müsait</button>
                </div>
            </div>

            <!-- Isı Haritası Lejantı -->
            <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                <span class="legend-pill">
                    <span class="legend-color-box" style="background: #fee2e2; border: 1px solid #fca5a5;"></span>
                    <span>Aşırı Yüklü (>45h)</span>
                </span>
                <span class="legend-pill">
                    <span class="legend-color-box" style="background: #dcfce7; border: 1px solid #86efac;"></span>
                    <span>Optimal (35-45h)</span>
                </span>
                <span class="legend-pill">
                    <span class="legend-color-box" style="background: #e0f2fe; border: 1px solid #bae6fd;"></span>
                    <span>Düşük Yük (1-34h)</span>
                </span>
                <span class="legend-pill">
                    <span class="legend-color-box" style="background: #f8fafc; border: 1px dashed #cbd5e1;"></span>
                    <span>Boş (0h)</span>
                </span>
            </div>

        </div>
    </div>

    <!-- 4. CELOXIS KAYNAK İŞ YÜKÜ ISI HARİTASI TABLOSU -->
    <div class="card mb-4" style="border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff; box-shadow: 0 4px 14px rgba(0,0,0,0.03); overflow: hidden;">
        <div class="card-header" style="background: #f8fafc; border-bottom: 2px solid #cbd5e1; padding: 14px 20px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 18px;">👥</span>
                <h3 class="card-title" style="margin: 0; font-size: 15px; font-weight: 800; color: #0f172a;">
                    Haftalık Personel Kapasite Dağılım Matrisi
                </h3>
            </div>
            <div style="font-size: 11.5px; color: #64748b; font-weight: 600;">
                ▶ Satırlara tıklayarak atanan projeleri ve görev detaylarını açabilirsiniz.
            </div>
        </div>

        <div class="card-body" style="padding: 0; overflow-x: auto;">
            <table class="app-table" id="heatmapMasterTable" style="margin: 0; width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f1f5f9; font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; border-bottom: 2px solid #cbd5e1;">
                        <th style="padding: 12px 18px; width: 280px;">Personel / Kaynak</th>
                        <th style="padding: 12px 10px; width: 90px; text-align: center;">Görevler</th>
                        <th style="padding: 12px 12px; width: 170px;">Ort. Doluluk</th>
                        
                        <!-- 5 Hafta Sütun Başlıkları -->
                        @foreach($weeks as $w)
                            <th style="padding: 10px 8px; width: 115px; text-align: center;" title="{{ $w['full_label'] }}">
                                <div style="font-size: 11.5px; font-weight: 800; color: #0f172a;">{{ $w['label'] }}</div>
                                <div style="font-size: 9.5px; color: #64748b; font-weight: 600;">{{ \Carbon\Carbon::parse($w['start_date'])->format('d.m') }} - {{ \Carbon\Carbon::parse($w['end_date'])->format('d.m') }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($userRows as $u)
                        @php
                            $statusType = $u['has_conflict'] ? 'overloaded' : ($u['total_hours'] >= 35 ? 'optimal' : ($u['total_hours'] > 0 ? 'underloaded' : 'idle'));
                        @endphp
                        <!-- PERSONEL ANA SATIRI -->
                        <tr class="resource-row" data-status="{{ $statusType }}" id="user-row-{{ $u['user_id'] }}" style="border-bottom: 1px solid #e2e8f0; transition: background 0.15s; cursor: pointer;" onclick="toggleUserTree({{ $u['user_id'] }})">
                            
                            <!-- Personel Adı & Unvan -->
                            <td style="padding: 12px 18px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span class="tree-toggle-arrow" id="arrow-{{ $u['user_id'] }}">▶</span>
                                    <div style="width: 28px; height: 28px; border-radius: 50%; background: {{ $u['has_conflict'] ? '#ef4444' : '#0284c7' }}; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800;">
                                        {{ mb_substr($u['name'], 0, 1) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: #0f172a; font-size: 12.5px;">{{ $u['name'] }}</div>
                                        <div style="font-size: 11px; color: #64748b;">{{ $u['unvan'] }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Görev Sayısı -->
                            <td style="text-align: center;">
                                <span class="badge" style="background: {{ $u['task_count'] > 0 ? '#eff6ff; color: #1d4ed8;' : '#f1f5f9; color: #94a3b8;' }}; font-weight: 700; font-size: 11px; padding: 3px 8px; border-radius: 5px;">
                                    {{ $u['task_count'] }} Görev
                                </span>
                            </td>

                            <!-- Ortalama Doluluk & Progress Bar -->
                            <td style="padding: 10px 12px;">
                                <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 800; margin-bottom: 4px;">
                                    <span style="color: {{ $u['has_conflict'] ? '#dc2626' : ($u['capacity_percentage'] >= 75 ? '#15803d' : '#0284c7') }};">%{{ $u['capacity_percentage'] }}</span>
                                    <span style="color: #64748b;">{{ round($u['total_hours'], 1) }}h</span>
                                </div>
                                <div style="background: #e2e8f0; height: 6px; border-radius: 3px; overflow: hidden;">
                                    <div style="background: {{ $u['has_conflict'] ? '#ef4444' : ($u['capacity_percentage'] >= 75 ? '#10b981' : '#0284c7') }}; width: {{ min($u['capacity_percentage'], 100) }}%; height: 100%;"></div>
                                </div>
                            </td>

                            <!-- 5 Hafta Isı Hücreleri -->
                            @foreach($weeks as $idx => $w)
                                @php
                                    $cell = $u['weeks_load'][$idx];
                                @endphp
                                <td style="padding: 6px 8px; text-align: center;">
                                    <div class="heatmap-cell {{ $cell['class'] }}">
                                        @if($cell['hours'] > 0)
                                            <span style="font-size: 12px;">{{ round($cell['hours'], 1) }}h</span>
                                            <span style="font-size: 9.5px; opacity: 0.85;">%{{ $cell['percentage'] }}</span>
                                        @else
                                            <span style="font-size: 11px;">-</span>
                                        @endif
                                    </div>
                                </td>
                            @endforeach

                        </tr>

                        <!-- ALT AKORDEON: GÖREV VE PROJE DÖKÜMÜ AĞACI -->
                        <tr class="tree-subrow" id="tree-subrow-{{ $u['user_id'] }}" style="display: none; background: #f8fafc;">
                            <td colspan="{{ 3 + count($weeks) }}" style="padding: 12px 20px 16px 48px; border-bottom: 2px solid #cbd5e1;">
                                <div style="border-left: 3px solid #0284c7; padding-left: 14px;">
                                    <div style="font-size: 12px; font-weight: 800; color: #0f172a; margin-bottom: 8px;">
                                        📋 {{ $u['name'] }} — Aktif Görev & Proje Sorumlulukları ({{ count($u['tasks_breakdown']) }} Görev):
                                    </div>

                                    @if(count($u['tasks_breakdown']) > 0)
                                        <table style="width: 100%; border-collapse: collapse; font-size: 11.5px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
                                            <thead>
                                                <tr style="background: #f1f5f9; font-weight: 700; color: #475569;">
                                                    <th style="padding: 6px 10px; text-align: left;">Proje</th>
                                                    <th style="padding: 6px 10px; text-align: left;">Görev Başlığı</th>
                                                    <th style="padding: 6px 10px; text-align: center; width: 100px;">Planlanan Efor</th>
                                                    <th style="padding: 6px 10px; text-align: center; width: 110px;">Termin</th>
                                                    <th style="padding: 6px 10px; text-align: right; width: 110px;">Durum</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($u['tasks_breakdown'] as $tb)
                                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                                        <td style="padding: 6px 10px; font-weight: 700; color: #0284c7;">
                                                            {{ $tb['project_code'] }} - {{ Str::limit($tb['project_title'], 28) }}
                                                        </td>
                                                        <td style="padding: 6px 10px; color: #0f172a;">
                                                            {{ $tb['title'] }}
                                                        </td>
                                                        <td style="padding: 6px 10px; text-align: center; font-weight: 800; color: #1e293b;">
                                                            {{ $tb['hours'] }} Saat
                                                        </td>
                                                        <td style="padding: 6px 10px; text-align: center; color: #64748b;">
                                                            {{ $tb['due_date'] }}
                                                        </td>
                                                        <td style="padding: 6px 10px; text-align: right;">
                                                            <x-status-badge :status="$tb['status']" />
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div style="font-size: 11.5px; color: #64748b; font-style: italic;">
                                            Bu personele atanmış aktif görev bulunmamaktadır. Kapasite yeni projeler için müsaittir.
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

                <!-- 5. ŞİRKET DİP TOPLAM SATIRI (COMPANY TOTAL HOURS) -->
                <tfoot>
                    <tr style="background: #0f172a; color: #ffffff; font-weight: 800; font-size: 12px; border-top: 2px solid #0f172a;">
                        <td style="padding: 14px 18px; color: #ffffff;">
                            🏢 ŞİRKET GENELİ TOPLAM İŞ YÜKÜ
                        </td>
                        <td style="text-align: center; color: #38bdf8;">
                            {{ $summary['active_assigned_users'] }} Kaynak
                        </td>
                        <td style="padding: 10px 12px; color: #38bdf8;">
                            {{ round($summary['total_company_hours'], 1) }} Saat
                        </td>

                        <!-- Haftalık Şirket Toplam Hücreleri -->
                        @foreach($weeks as $idx => $w)
                            @php
                                $wTot = $weeklyTotals[$idx] ?? 0;
                            @endphp
                            <td style="padding: 8px 10px; text-align: center;">
                                <div style="background: rgba(56,189,248,0.15); border: 1px solid rgba(56,189,248,0.3); border-radius: 6px; padding: 6px 4px; color: #38bdf8;">
                                    <div style="font-size: 12.5px; font-weight: 800;">{{ round($wTot, 1) }}h</div>
                                    <div style="font-size: 9.5px; opacity: 0.8; color: #94a3b8;">Hafta Toplamı</div>
                                </div>
                            </td>
                        @endforeach
                    </tr>
                </tfoot>

            </table>
        </div>
    </div>

<script>
// =========================================================================
// 1. PERSONEL GÖREV AĞACI AÇMA / KAPATMA
// =========================================================================
function toggleUserTree(userId) {
    const subrow = document.getElementById('tree-subrow-' + userId);
    const arrow = document.getElementById('arrow-' + userId);
    if (!subrow) return;

    if (subrow.style.display === 'none' || subrow.style.display === '') {
        subrow.style.display = 'table-row';
        if (arrow) arrow.classList.add('open');
    } else {
        subrow.style.display = 'none';
        if (arrow) arrow.classList.remove('open');
    }
}

// =========================================================================
// 2. CANLI ARAMA FİLTRESİ
// =========================================================================
function filterResourceHeatmap() {
    const q = document.getElementById('resourceSearch').value.toLowerCase();
    const rows = document.querySelectorAll('.resource-row');
    rows.forEach(r => {
        const text = r.textContent.toLowerCase();
        const userId = r.id.replace('user-row-', '');
        const subrow = document.getElementById('tree-subrow-' + userId);

        if (text.includes(q)) {
            r.style.display = '';
        } else {
            r.style.display = 'none';
            if (subrow) subrow.style.display = 'none';
        }
    });
}

// =========================================================================
// 3. DURUM BAZLI FİLTRELEME (TÜMÜ / ÇAKIŞANLAR / OPTİMAL / MÜSAİT)
// =========================================================================
function filterByStatus(status, btn) {
    document.querySelectorAll('.filter-btn').forEach(b => {
        b.style.background = 'transparent';
        b.style.color = '#64748b';
        b.classList.remove('active');
    });
    btn.style.background = '#0284c7';
    btn.style.color = '#ffffff';
    btn.classList.add('active');

    const rows = document.querySelectorAll('.resource-row');
    rows.forEach(r => {
        const rowStatus = r.getAttribute('data-status');
        const userId = r.id.replace('user-row-', '');
        const subrow = document.getElementById('tree-subrow-' + userId);

        if (status === 'all' || rowStatus === status) {
            r.style.display = '';
        } else {
            r.style.display = 'none';
            if (subrow) subrow.style.display = 'none';
        }
    });
}
</script>
@endsection