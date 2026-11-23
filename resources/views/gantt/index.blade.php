@extends('layouts.app')

@section('title', 'Gantt Zaman Çizelgesi')
@section('page_title', 'Gantt Zaman Çizelgesi & Kritik Yol')
@section('page_subtitle', 'Planlanan vs. Mevcut Durum Analizi, Gecikme Uyarıları ve Takvim Şeması')

@section('content')
<style>
/* Sütun Gizleme / Gösterme Toggle Butonları */
.btn-col-toggle {
    padding: 3px 9px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 5px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #475569;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.15s ease;
    user-select: none;
}
.btn-col-toggle:hover {
    border-color: #0284c7;
    color: #0284c7;
}
.btn-col-toggle.active {
    background: #0284c7;
    color: #ffffff;
    border-color: #0284c7;
    font-weight: 700;
}
.btn-col-toggle:not(.active) {
    background: #f1f5f9;
    color: #94a3b8;
    text-decoration: line-through;
}

/* Sol Panel Tablo Satırları (CSS Grid) */
.gantt-table-row {
    display: grid;
    align-items: center;
    padding: 0 12px;
    border-bottom: 1px solid #e2e8f0;
    transition: background 0.1s ease;
    box-sizing: border-box;
}

/* Mobil Görünüm Sekmesi */
.mobile-gantt-tabs {
    display: none;
    margin-bottom: 12px;
    background: #e2e8f0;
    border-radius: 10px;
    padding: 4px;
    gap: 6px;
}
.mobile-tab-btn {
    flex: 1;
    padding: 9px 12px;
    font-size: 12.5px;
    font-weight: 700;
    border-radius: 8px;
    border: none;
    background: transparent;
    color: #475569;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: all 0.2s ease;
}
.mobile-tab-btn.active {
    background: #0284c7;
    color: #ffffff;
    box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);
}

/* Mobil Cihazlar İçin Duyarlı (Responsive) Kurallar */
@media (max-width: 768px) {
    .mobile-gantt-tabs {
        display: flex !important;
    }

    .gantt-toolbar-card .card-body {
        padding: 12px 14px !important;
    }

    .gantt-project-select-wrap {
        width: 100% !important;
    }
    .gantt-project-select-wrap select {
        width: 100% !important;
    }

    .gantt-controls-wrap {
        width: 100% !important;
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 8px !important;
    }
    .gantt-controls-row {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 6px !important;
        width: 100% !important;
        justify-content: space-between !important;
    }
    .gantt-controls-row > * {
        flex: 1 1 auto !important;
    }

    /* Lejant (Legend) Mobilde Kompakt */
    .gantt-legend-card {
        padding: 8px 12px !important;
    }
    .gantt-legend-items {
        gap: 8px 12px !important;
        font-size: 11px !important;
    }

    /* Gantt Kapsayıcısı Mobilde Tab Moduna Geçer */
    .gantt-container {
        display: block !important;
        width: 100% !important;
        min-height: 480px !important;
    }

    /* 1. Tab: Zaman Çizelgesi Aktifken */
    .gantt-container.mobile-show-timeline .gantt-left-pane {
        display: none !important;
    }
    .gantt-container.mobile-show-timeline .gantt-right-timeline {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        flex: 1 1 100% !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
    }

    /* 2. Tab: Görev Listesi Aktifken */
    .gantt-container.mobile-show-table .gantt-left-pane {
        display: block !important;
        width: 100% !important;
        min-width: 100% !important;
        max-width: 100% !important;
        border-right: none !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
    }
    .gantt-container.mobile-show-table .gantt-right-timeline {
        display: none !important;
    }
}

/* Print / PDF Stilleri */
@media print {
    .sidebar, .navbar, .top-nav, .header-right,
    .page-header, .gantt-column-toggles, .scale-btn, #filterDelayedBtn,
    .btn, button, select, .card-header, .tabs-header, .mobile-gantt-tabs {
        display: none !important;
    }

    body {
        background: #ffffff !important;
        color: #000000 !important;
        font-size: 10pt !important;
    }

    .container, .main-content, .content-wrapper, .card {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        border: none !important;
        box-shadow: none !important;
    }

    .gantt-print-header {
        display: block !important;
        margin-bottom: 14px;
        border-bottom: 2px solid #0f172a;
        padding-bottom: 8px;
    }

    .gantt-container {
        display: flex !important;
        width: 100% !important;
    }

    .gantt-left-pane {
        box-shadow: none !important;
    }

    .gantt-right-timeline {
        overflow: visible !important;
    }

    @page {
        size: landscape;
        margin: 8mm;
    }
}
</style>

    <!-- Yazdırma / PDF Çıktı Başlığı -->
    @if($project)
        <div class="gantt-print-header" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                <div>
                    <h2 style="margin: 0; font-size: 18px; font-weight: 800; color: #0f172a;">{{ $project->code }} - {{ $project->title }}</h2>
                    <p style="margin: 3px 0 0; font-size: 12px; color: #64748b;">Gantt Zaman Çizelgesi & İlerleme Raporu | Proje Lideri: {{ $project->manager?->name ?? 'PMO' }}</p>
                </div>
                <div style="text-align: right; font-size: 11px; color: #475569;">
                    <div>Yazdırma Tarihi: <strong>{{ now()->format('d.m.Y H:i') }}</strong></div>
                    <div>Genel İlerleme: <strong>%{{ (int) round($project->progress_percentage) }}</strong> | Durum: <strong>{{ ucfirst($project->status) }}</strong></div>
                </div>
            </div>
        </div>
    @endif

    @php
        // Zaman Ölçeği Parametresi (day, week, month)
        $scale = request('scale', 'week');
        if (!in_array($scale, ['day', 'week', 'month'])) {
            $scale = 'week';
        }
    @endphp

    <!-- Proje Seçim ve Üst Kontrol Barı -->
    <div class="card mb-3 gantt-toolbar-card" style="border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        <div class="card-body" style="padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
            <div class="gantt-project-select-wrap" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <label style="font-size: 13px; font-weight: 700; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 6px;">
                    <span>📁</span> Proje:
                </label>
                <select onchange="changeProject(this.value)" class="form-control" style="width: 400px; max-width: 100%; height: 40px; font-weight: 600; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1;">
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ $project && $project->id == $p->id ? 'selected' : '' }}>
                            {{ $p->code }} - {{ $p->title }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            @if($project)
                <div class="gantt-controls-wrap" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    
                    <!-- 1. SÜTUN GÖRÜNÜRLÜK KONTROLLERİ -->
                    <div class="gantt-column-toggles" style="display: inline-flex; align-items: center; gap: 4px; background: #f8fafc; padding: 4px 8px; border-radius: 8px; border: 1.5px solid #e2e8f0;">
                        <span style="font-size: 11px; font-weight: 700; color: #475569; margin-right: 4px; display: inline-flex; align-items: center; gap: 4px;">
                            👁️ Sütunlar:
                        </span>
                        <button type="button" class="btn-col-toggle active" id="btnToggleAssignee" onclick="toggleGanttCol('assignee')" title="Sorumlu sütununu göster/gizle">
                            <span class="chk-icon">✓</span> Sorumlu
                        </button>
                        <button type="button" class="btn-col-toggle active" id="btnToggleProgress" onclick="toggleGanttCol('progress')" title="İlerleme sütununu göster/gizle">
                            <span class="chk-icon">✓</span> İlerleme
                        </button>
                        <button type="button" class="btn-col-toggle active" id="btnToggleStatus" onclick="toggleGanttCol('status')" title="Durum sütununu göster/gizle">
                            <span class="chk-icon">✓</span> Durum
                        </button>
                    </div>

                    <!-- 2. DIŞA AKTARMA BUTONLARI (Excel & PDF) -->
                    <div style="display: inline-flex; align-items: center; gap: 6px;">
                        <a href="{{ route('gantt.export_excel', $project->id) }}" class="btn btn-xs btn-outline-success" style="height: 32px; padding: 0 12px; font-size: 11.5px; border-radius: 6px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; border-color: #86efac; color: #15803d; background: #f0fdf4;" title="Gantt tablosunu Excel (CSV/XLS) olarak indir">
                            <span>📊</span> Excel'e Aktar
                        </a>
                        <button type="button" class="btn btn-xs btn-outline-danger" onclick="printGanttReport()" style="height: 32px; padding: 0 12px; font-size: 11.5px; border-radius: 6px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; border-color: #fca5a5; color: #b91c1c; background: #fef2f2;" title="Gantt takvimini PDF olarak kaydet veya yazdır">
                            <span>📄</span> PDF / Yazdır
                        </button>
                    </div>

                    <!-- 3. ZAMAN ÖLÇEĞİ BUTONLARI (HAFTALIK / AYLIK / GÜNLÜK) -->
                    <div style="display: flex; align-items: center; gap: 4px; background: #f8fafc; border: 1.5px solid #cbd5e1; padding: 3px 6px; border-radius: 8px;">
                        <span style="font-size: 11px; color: #475569; font-weight: 700; margin-right: 2px;">Ölçek:</span>
                        <button type="button" class="btn btn-xs {{ $scale === 'day' ? 'btn-primary' : 'btn-outline-secondary' }} scale-btn" id="scaleDayBtn" onclick="changeTimeScale('day')" style="padding: 4px 10px; font-size: 11px; border-radius: 5px; font-weight: 700;">Günlük</button>
                        <button type="button" class="btn btn-xs {{ $scale === 'week' ? 'btn-primary' : 'btn-outline-secondary' }} scale-btn" id="scaleWeekBtn" onclick="changeTimeScale('week')" style="padding: 4px 10px; font-size: 11px; border-radius: 5px; font-weight: 700;">Haftalık</button>
                        <button type="button" class="btn btn-xs {{ $scale === 'month' ? 'btn-primary' : 'btn-outline-secondary' }} scale-btn" id="scaleMonthBtn" onclick="changeTimeScale('month')" style="padding: 4px 10px; font-size: 11px; border-radius: 5px; font-weight: 700;">Aylık</button>
                    </div>

                    <!-- Kırmızı Bugüne Odaklan Butonu (Celoxis Modeli) -->
                    <button type="button" class="btn btn-xs" id="btnScrollToday" onclick="scrollToToday()" style="padding: 6px 12px; font-size: 11.5px; border-radius: 6px; font-weight: 700; border: 1.5px solid #fca5a5; color: #b91c1c; background: #fff5f5; display: inline-flex; align-items: center; gap: 4px; cursor: pointer;" title="Zaman çizelgesinde kırmızı Bugün çizgisine odaklan">
                        <span>📍</span> Bugüne Odaklan
                    </button>

                    <!-- Sadece Gecikenler Filtresi -->
                    <button type="button" class="btn btn-xs btn-outline-danger" id="filterDelayedBtn" onclick="toggleOnlyDelayed()" style="padding: 6px 12px; font-size: 11.5px; border-radius: 6px; font-weight: 600; border: 1px solid #fca5a5; color: #b91c1c; background: #fef2f2;">
                        ⚠️ Sadece Gecikenler
                    </button>

                    <!-- Temel Çizgi (Schedule Baseline) Karşılaştırma Modu -->
                    <button type="button" class="btn btn-xs" id="btnToggleBaseline" onclick="toggleBaselineComparison()" style="padding: 6px 12px; font-size: 11.5px; border-radius: 6px; font-weight: 700; border: 1.5px solid #6366f1; color: #4338ca; background: #eef2ff; display: inline-flex; align-items: center; gap: 4px; cursor: pointer;" title="Dondurulmuş Temel Çizgi (Baseline) ile mevcut takvimi çift çubukla karşılaştır">
                        <span>📐</span> Temel Çizgi @if($activeBaseline) ({{ $activeBaseline->version }}) @else (Yok) @endif
                    </button>
                </div>
            @endif
        </div>
    </div>

    @if($project)
        <!-- Lejant (Standart Açıklamaları) -->
        <div class="card mb-3 gantt-legend-card" style="border-radius: 10px; border: 1px solid #e2e8f0; background: #ffffff; padding: 10px 18px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; font-size: 12px;">
                <div class="gantt-legend-items" style="display: flex; align-items: center; gap: 18px; flex-wrap: wrap;">
                    <span style="font-weight: 700; color: #475569; text-transform: uppercase; font-size: 11px;">Lejant:</span>
                    
                    <span style="display: inline-flex; align-items: center; gap: 6px; color: #1e293b;">
                        <span style="width: 14px; height: 10px; border-radius: 3px; background: #cbd5e1; border: 1px solid #94a3b8; display: inline-block;"></span>
                        <strong>Planlanan Süre</strong>
                    </span>

                    <span style="display: inline-flex; align-items: center; gap: 6px; color: #1e293b;">
                        <span style="width: 14px; height: 10px; border-radius: 3px; background: #0284c7; display: inline-block;"></span>
                        <strong>Devam Ediyor (Fiili)</strong>
                    </span>

                    <span style="display: inline-flex; align-items: center; gap: 6px; color: #1e293b;">
                        <span style="width: 14px; height: 10px; border-radius: 3px; background: #16a34a; display: inline-block;"></span>
                        <strong>Tamamlandı (%100)</strong>
                    </span>

                    <span style="display: inline-flex; align-items: center; gap: 6px; color: #dc2626;">
                        <span style="width: 16px; height: 10px; border-radius: 3px; background: repeating-linear-gradient(45deg, #ef4444, #ef4444 4px, #fee2e2 4px, #fee2e2 8px); border: 1px solid #ef4444; display: inline-block;"></span>
                        <strong>Planı Aştı / Gecikmede</strong>
                    </span>

                    <span style="display: inline-flex; align-items: center; gap: 6px; color: #b45309;">
                        <span>💎</span>
                        <strong>Kilometre Taşı</strong>
                    </span>

                    <span class="baseline-legend-item" style="display: none; align-items: center; gap: 6px; color: #4338ca;">
                        <span style="width: 14px; height: 6px; border-radius: 2px; background: #6366f1; border-top: 2px dashed #312e81; display: inline-block;"></span>
                        <strong>Temel Çizgi (Baseline {{ $activeBaseline?->version }})</strong>
                    </span>
                </div>

                <div style="display: inline-flex; align-items: center; gap: 6px; background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 3px 10px; border-radius: 6px; font-weight: 700; font-size: 11.5px;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #dc2626; display: inline-block;"></span>
                    <span>📍 Kırmızı Çizgi = BUGÜN (Where We Are Right Now)</span>
                </div>
            </div>
        </div>

        @php
            // Zaman Çerçevesi Hesaplama
            $projStart = $project->start_date ?: now()->subDays(20);
            $projEnd = $project->target_end_date ?: now()->addDays(90);

            // Tüm görev ve fazların min ve max tarihlerini topla
            $allDates = collect([$projStart, $projEnd, now()]);
            foreach($project->phases as $ph) {
                if($ph->start_date) $allDates->push($ph->start_date);
                if($ph->end_date) $allDates->push($ph->end_date);
                foreach($ph->tasks as $t) {
                    if($t->start_date) $allDates->push($t->start_date);
                    if($t->due_date) $allDates->push($t->due_date);
                }
            }

            $minDate = $allDates->min()->copy()->startOfWeek();
            $maxDate = $allDates->max()->copy()->addDays(21)->endOfWeek();

            $totalDays = max(30, (int) round($minDate->diffInDays($maxDate)));
            $today = now();
            $todayOffsetDays = max(0, (int) round($minDate->diffInDays($today)));

            // Hafta blokları
            $weeks = [];
            $curWeek = $minDate->copy();
            while($curWeek <= $maxDate) {
                $weeks[] = [
                    'label' => $curWeek->format('d/m'),
                    'full_label' => 'Hafta ' . $curWeek->weekOfYear . ' (' . $curWeek->format('d.m') . ')',
                    'month' => $curWeek->translatedFormat('F Y'),
                    'start_day' => (int) round($minDate->diffInDays($curWeek)),
                ];
                $curWeek->addWeek();
            }

            // Piksel Katsayısı Ölçeğe Göre Dinamik Hesaplanır
            if ($scale === 'day') {
                $pxPerDay = 36;
            } elseif ($scale === 'month') {
                $pxPerDay = 4.5;
            } else {
                $pxPerDay = 14;
            }

            $timelineWidth = max(1100, $totalDays * $pxPerDay);
            $todayLeftPx = $todayOffsetDays * $pxPerDay;
        @endphp

        <!-- MOBİL GÖRÜNÜM SEKME BUTONLARI -->
        <div class="mobile-gantt-tabs">
            <button type="button" class="mobile-tab-btn active" id="tabBtnTimeline" onclick="setMobileGanttTab('timeline')">
                <span>📊 Zaman Çizelgesi (Gantt)</span>
            </button>
            <button type="button" class="mobile-tab-btn" id="tabBtnTable" onclick="setMobileGanttTab('table')">
                <span>📋 Görev Listesi</span>
            </button>
        </div>

        <!-- MODERN ŞEMATİK GANTT ÇİZELGESİ -->
        <div class="card mb-4" style="border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); background: #ffffff;">
            <div class="gantt-container mobile-show-timeline" style="display: flex; width: 100%; min-height: 520px; position: relative;">
                
                <!-- SOL PANEL: HİYERARŞİ, SORUMLU, İLERLEME, DURUM -->
                <div class="gantt-left-pane" style="width: 530px; min-width: 530px; max-width: 530px; border-right: 2px solid #cbd5e1; background: #ffffff; z-index: 20; box-shadow: 4px 0 10px rgba(0,0,0,0.03); transition: width 0.2s ease, min-width 0.2s ease, max-width 0.2s ease;">
                    
                    <!-- Sol Başlık Satırı -->
                    <div class="gantt-table-row gantt-header-row" style="height: 64px; background: #f8fafc; border-bottom: 2px solid #cbd5e1; font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase;">
                        <div class="gantt-col-task" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Görev / Faz Hiyerarşisi</div>
                        <div class="gantt-col-assignee" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Sorumlu</div>
                        <div class="gantt-col-progress" style="text-align: center;">İlerleme</div>
                        <div class="gantt-col-status" style="text-align: right;">Durum</div>
                    </div>

                    <!-- Sol Satırlar -->
                    <div class="gantt-left-rows">
                        <!-- 1. Ana Proje Satırı -->
                        <div class="gantt-table-row gantt-row-item project-row" style="height: 52px; background: #f1f5f9; font-weight: 700;">
                            <div class="gantt-col-task" style="font-size: 13px; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $project->title }}">
                                📁 {{ Str::limit($project->title, 24) }}
                            </div>
                            <div class="gantt-col-assignee" style="font-size: 11.5px; color: #475569; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $project->manager?->name ?? 'PMO' }}
                            </div>
                            <div class="gantt-col-progress" style="text-align: center;">
                                <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 11px; font-weight: 800; padding: 2px 7px; border-radius: 4px;">
                                    %{{ (int) round($project->progress_percentage) }}
                                </span>
                            </div>
                            <div class="gantt-col-status" style="text-align: right;">
                                <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 4px;">
                                    {{ ucfirst($project->status) }}
                                </span>
                            </div>
                        </div>

                        <!-- 2. Fazlar ve Alt Görevler -->
                        @foreach($project->phases as $ph)
                            <div class="gantt-table-row gantt-row-item phase-row" style="height: 52px; background: #f8fafc; font-weight: 700; border-top: 1px solid #e2e8f0;">
                                <div class="gantt-col-task" style="font-size: 12.5px; color: #0284c7; padding-left: 12px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $ph->name }}">
                                    🔷 {{ Str::limit($ph->name, 22) }}
                                </div>
                                <div class="gantt-col-assignee" style="font-size: 11px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $ph->tasks->count() }} Görev
                                </div>
                                <div class="gantt-col-progress" style="text-align: center;">
                                    <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 10.5px; font-weight: 800; padding: 2px 6px; border-radius: 4px;">
                                        %{{ (int) round($ph->progress_percentage) }}
                                    </span>
                                </div>
                                <div class="gantt-col-status" style="text-align: right;">
                                    <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px;">
                                        {{ ucfirst($ph->status) }}
                                    </span>
                                </div>
                            </div>

                            @foreach($ph->tasks as $t)
                                @php
                                    $tDue = $t->due_date ?: ($ph->end_date ?: null);
                                    $isDelayed = ($t->progress_percentage < 100 && $tDue && $tDue < $today);
                                    $overdueDays = $isDelayed ? (int) round($tDue->diffInDays($today)) : 0;
                                @endphp
                                <div class="gantt-table-row gantt-row-item task-row {{ $isDelayed ? 'delayed-task-row' : '' }}" style="height: 52px; background: #ffffff; padding-left: 20px;">
                                    <div class="gantt-col-task" style="font-size: 12px; color: #334155; padding-left: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $t->title }}">
                                        @if($t->is_milestone)
                                            <span title="Kilometre Taşı">💎</span>
                                        @else
                                            <span style="color: #94a3b8; margin-right: 4px;">↳</span>
                                        @endif
                                        <span style="font-weight: 600;">{{ Str::limit($t->title, 22) }}</span>
                                    </div>
                                    <div class="gantt-col-assignee" style="font-size: 11px; color: #475569; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $t->assignee?->name ?? 'Atanmamış' }}">
                                        {{ $t->assignee?->name ? Str::limit($t->assignee->name, 13) : '-' }}
                                    </div>
                                    <div class="gantt-col-progress" style="text-align: center;">
                                        <span class="badge" style="background: {{ round($t->progress_percentage) == 100 ? '#ecfdf5; color: #065f46;' : ($isDelayed ? '#fef2f2; color: #b91c1c;' : '#f0f9ff; color: #0369a1;') }}; font-size: 10.5px; font-weight: 800; padding: 2px 6px; border-radius: 4px;">
                                            %{{ (int) round($t->progress_percentage) }}
                                        </span>
                                    </div>
                                    <div class="gantt-col-status" style="text-align: right;">
                                        @if($isDelayed)
                                            <span class="status-badge badge-danger" title="{{ $overdueDays }} gün planı aştı!">
                                                <span class="badge-icon">⚠️</span>
                                                <span>Gecikmede (+{{ $overdueDays }}g)</span>
                                            </span>
                                        @else
                                            <x-status-badge :status="$t->status" />
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>

                <!-- SAĞ PANEL: ZAMAN ÇİZELGESİ (TIMELINE CANVAS & DUAL BARS) -->
                <div class="gantt-right-timeline" id="ganttTimelineScroll" style="flex: 1; overflow-x: auto; overflow-y: hidden; background: #ffffff; position: relative;">
                    
                    <div style="width: {{ $timelineWidth }}px; min-width: 100%; position: relative;">
                        
                        <!-- ZAMAN BAŞLIĞI (AYLAR & HAFTALAR / GÜNLER) -->
                        <div style="height: 64px; background: #f8fafc; border-bottom: 2px solid #cbd5e1; position: sticky; top: 0; z-index: 15;">
                            
                            @if($scale === 'day')
                                <!-- GÜNLÜK ÖLÇEK BAŞLIĞI -->
                                <!-- Üst Şerit: Aylar -->
                                <div style="height: 30px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; background: #f1f5f9;">
                                    @php $monthCursor = $minDate->copy()->startOfMonth(); @endphp
                                    @while($monthCursor <= $maxDate)
                                        @php
                                            $mStartDay = max(0, (int) round($minDate->diffInDays($monthCursor)));
                                            $mNext = $monthCursor->copy()->addMonth()->startOfMonth();
                                            $mEndDay = min($totalDays, (int) round($minDate->diffInDays($mNext)));
                                            $mWidth = ($mEndDay - $mStartDay) * $pxPerDay;
                                        @endphp
                                        @if($mWidth > 0)
                                            <div style="width: {{ $mWidth }}px; font-weight: 800; font-size: 11px; color: #1e293b; text-transform: uppercase; padding-left: 10px; border-right: 1px solid #cbd5e1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                📅 {{ $monthCursor->translatedFormat('F Y') }}
                                            </div>
                                        @endif
                                        @php $monthCursor->addMonth(); @endphp
                                    @endwhile
                                </div>

                                <!-- Alt Şerit: Her Bir Gün (36px genişlik) -->
                                <div style="height: 34px; display: flex; align-items: center;">
                                    @php $curDay = $minDate->copy(); @endphp
                                    @while($curDay <= $maxDate)
                                        @php
                                            $isWknd = $curDay->isWeekend();
                                            $isTdy = $curDay->isSameDay($today);
                                            $dayName = $curDay->translatedFormat('D');
                                        @endphp
                                        <div style="width: {{ $pxPerDay }}px; min-width: {{ $pxPerDay }}px; height: 34px; font-size: 10px; font-weight: 700; color: {{ $isTdy ? '#b91c1c' : ($isWknd ? '#94a3b8' : '#334155') }}; background: {{ $isTdy ? '#fef08a' : ($isWknd ? '#f8fafc' : 'transparent') }}; text-align: center; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; justify-content: center; line-height: 1.1;" title="{{ $curDay->format('d.m.Y') }} ({{ $dayName }})">
                                            <span>{{ $curDay->format('d') }}</span>
                                            <span style="font-size: 8px; font-weight: normal; text-transform: uppercase;">{{ mb_substr($dayName, 0, 3) }}</span>
                                        </div>
                                        @php $curDay->addDay(); @endphp
                                    @endwhile
                                </div>

                            @elseif($scale === 'month')
                                <!-- AYLIK ÖLÇEK BAŞLIĞI -->
                                <!-- Üst Şerit: Yıllar -->
                                <div style="height: 30px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; background: #f1f5f9;">
                                    @php $yearCursor = $minDate->copy()->startOfYear(); @endphp
                                    @while($yearCursor <= $maxDate)
                                        @php
                                            $yStart = max(0, (int) round($minDate->diffInDays($yearCursor)));
                                            $yNext = $yearCursor->copy()->addYear()->startOfYear();
                                            $yEnd = min($totalDays, (int) round($minDate->diffInDays($yNext)));
                                            $yWidth = ($yEnd - $yStart) * $pxPerDay;
                                        @endphp
                                        @if($yWidth > 0)
                                            <div style="width: {{ $yWidth }}px; font-weight: 800; font-size: 11px; color: #1e293b; padding-left: 10px; border-right: 1px solid #cbd5e1; white-space: nowrap;">
                                                📅 {{ $yearCursor->format('Y') }}
                                            </div>
                                        @endif
                                        @php $yearCursor->addYear(); @endphp
                                    @endwhile
                                </div>

                                <!-- Alt Şerit: Aylar -->
                                <div style="height: 34px; display: flex; align-items: center;">
                                    @php $monthCursor = $minDate->copy()->startOfMonth(); @endphp
                                    @while($monthCursor <= $maxDate)
                                        @php
                                            $mStartDay = max(0, (int) round($minDate->diffInDays($monthCursor)));
                                            $mNext = $monthCursor->copy()->addMonth()->startOfMonth();
                                            $mEndDay = min($totalDays, (int) round($minDate->diffInDays($mNext)));
                                            $mWidth = ($mEndDay - $mStartDay) * $pxPerDay;
                                        @endphp
                                        @if($mWidth > 0)
                                            <div style="width: {{ $mWidth }}px; font-size: 10.5px; font-weight: 700; color: #475569; text-align: center; border-right: 1px solid #cbd5e1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding: 0 4px;" title="{{ $monthCursor->translatedFormat('F Y') }}">
                                                {{ $monthCursor->translatedFormat('M y') }}
                                            </div>
                                        @endif
                                        @php $monthCursor->addMonth(); @endphp
                                    @endwhile
                                </div>

                            @else
                                <!-- HAFTALIK ÖLÇEK BAŞLIĞI (VARSAYILAN) -->
                                <!-- Üst: Aylar Şeridi -->
                                <div style="height: 32px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; background: #f1f5f9;">
                                    @php $monthCursor = $minDate->copy()->startOfMonth(); @endphp
                                    @while($monthCursor <= $maxDate)
                                        @php
                                            $mStartDay = max(0, (int) round($minDate->diffInDays($monthCursor)));
                                            $mNext = $monthCursor->copy()->addMonth()->startOfMonth();
                                            $mEndDay = min($totalDays, (int) round($minDate->diffInDays($mNext)));
                                            $mWidth = ($mEndDay - $mStartDay) * $pxPerDay;
                                        @endphp
                                        @if($mWidth > 0)
                                            <div style="width: {{ $mWidth }}px; font-weight: 800; font-size: 11px; color: #334155; text-transform: uppercase; padding-left: 10px; border-right: 1px solid #cbd5e1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                📅 {{ $monthCursor->translatedFormat('F Y') }}
                                            </div>
                                        @endif
                                        @php $monthCursor->addMonth(); @endphp
                                    @endwhile
                                </div>

                                <!-- Alt: Haftalar Şeridi -->
                                <div style="height: 32px; display: flex; align-items: center;">
                                    @foreach($weeks as $idx => $wk)
                                        <div style="width: {{ 7 * $pxPerDay }}px; font-size: 10.5px; font-weight: 700; color: #64748b; text-align: center; border-right: 1px solid #e2e8f0; white-space: nowrap;">
                                            W{{ $idx + 1 }} <span style="font-size: 9.5px; color: #94a3b8; font-weight: normal;">({{ $wk['label'] }})</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                        </div>

                        <!-- DİKEY ARKA PLAN IZGARALARI -->
                        <div style="position: absolute; top: 64px; bottom: 0; left: 0; right: 0; pointer-events: none; z-index: 1;">
                            @if($scale === 'day')
                                @php $curDay = $minDate->copy(); $dIdx = 0; @endphp
                                @while($curDay <= $maxDate)
                                    @if($curDay->isWeekend())
                                        <div style="position: absolute; left: {{ $dIdx * $pxPerDay }}px; top: 0; bottom: 0; width: {{ $pxPerDay }}px; background: rgba(241, 245, 249, 0.45); pointer-events: none;"></div>
                                    @endif
                                    <div style="position: absolute; left: {{ $dIdx * $pxPerDay }}px; top: 0; bottom: 0; width: 1px; background: #f1f5f9;"></div>
                                    @php $curDay->addDay(); $dIdx++; @endphp
                                @endwhile
                            @elseif($scale === 'month')
                                @php $monthCursor = $minDate->copy()->startOfMonth(); @endphp
                                @while($monthCursor <= $maxDate)
                                    @php $mStartDay = max(0, (int) round($minDate->diffInDays($monthCursor))); @endphp
                                    <div style="position: absolute; left: {{ $mStartDay * $pxPerDay }}px; top: 0; bottom: 0; width: 1px; background: #cbd5e1;"></div>
                                    @php $monthCursor->addMonth(); @endphp
                                @endwhile
                            @else
                                @foreach($weeks as $wk)
                                    <div style="position: absolute; left: {{ $wk['start_day'] * $pxPerDay }}px; top: 0; bottom: 0; width: 1px; background: #f1f5f9;"></div>
                                @endforeach
                            @endif
                        </div>

                        <!-- 📍 DİKEY BUGÜN ÇİZGİSİ (WHERE WE ARE RIGHT NOW) -->
                        <div class="today-marker-line" style="position: absolute; left: {{ $todayLeftPx }}px; top: 0; bottom: 0; width: 2px; background: #dc2626; z-index: 12; pointer-events: none;">
                            <div style="position: absolute; top: 2px; left: -60px; width: 122px; background: #fef08a; border: 1.5px solid #ca8a04; color: #854d0e; font-size: 9px; font-weight: 800; text-align: center; border-radius: 4px; padding: 2px 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); letter-spacing: 0.3px; pointer-events: auto;">
                                WHERE WE ARE NOW
                                <div style="font-size: 8.5px; color: #b45309; font-weight: 700;">{{ $today->format('d.m.Y') }}</div>
                            </div>
                        </div>

                        <!-- TIMELINE SATIRLARI & ÇİFT BARLAR (PLANLANAN VS MEVCUT DURUM) -->
                        <div class="gantt-right-rows" style="position: relative; z-index: 5;">
                            
                            <!-- 1. Proje Barı -->
                            @php
                                $pStartOffset = max(0, (int) round($minDate->diffInDays($projStart)));
                                $pDuration = max(1, (int) round($projStart->diffInDays($projEnd)));
                                $pBarLeft = $pStartOffset * $pxPerDay;
                                $pBarWidth = $pDuration * $pxPerDay;
                                $pFillWidth = ($pBarWidth * $project->progress_percentage) / 100;
                            @endphp
                            <div class="gantt-timeline-row project-t-row" style="height: 52px; display: flex; align-items: center; position: relative; border-bottom: 1px solid #e2e8f0;">
                                <!-- Planlanan Bar (Kapsam) -->
                                <div class="dual-gantt-bar-wrap" style="position: absolute; left: {{ $pBarLeft }}px; width: {{ $pBarWidth }}px; height: 28px; border-radius: 8px; background: #e2e8f0; border: 1.5px solid #94a3b8; box-sizing: border-box; display: flex; align-items: center; padding: 0 6px;" title="Ana Proje: {{ $projStart->format('d.m.Y') }} - {{ $projEnd->format('d.m.Y') }}">
                                    <!-- Fiili İlerleme Barı -->
                                    <div style="position: absolute; left: 0; top: 0; bottom: 0; width: {{ $pFillWidth }}px; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); border-radius: 6px; opacity: 0.95;"></div>
                                    <div style="position: relative; z-index: 2; font-size: 11px; font-weight: 800; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-shadow: 0 1px 2px rgba(0,0,0,0.4);">
                                        📁 {{ $project->title }} • %{{ (int) round($project->progress_percentage) }}
                                    </div>
                                </div>
                                @if($activeBaseline && $activeBaseline->planned_start_date && $activeBaseline->planned_end_date)
                                    @php
                                        $bpStart = \Carbon\Carbon::parse($activeBaseline->planned_start_date);
                                        $bpEnd = \Carbon\Carbon::parse($activeBaseline->planned_end_date);
                                        $bpOffset = max(0, (int) round($minDate->diffInDays($bpStart)));
                                        $bpDuration = max(1, (int) round($bpStart->diffInDays($bpEnd)));
                                        $bpLeft = $bpOffset * $pxPerDay;
                                        $bpWidth = $bpDuration * $pxPerDay;
                                    @endphp
                                    <div class="baseline-bar project-baseline-bar" style="display: none; position: absolute; left: {{ $bpLeft }}px; width: {{ $bpWidth }}px; bottom: 2px; height: 6px; border-radius: 3px; background: #6366f1; border-top: 2px dashed #312e81; z-index: 4;" title="Temel Çizgi ({{ $activeBaseline->version }}): {{ $bpStart->format('d.m.Y') }} - {{ $bpEnd->format('d.m.Y') }} (Bütçe: {{ number_format($activeBaseline->total_planned_budget, 2) }} {{ $project->currency ?? 'TRY' }})"></div>
                                @endif
                            </div>

                            <!-- 2. Faz ve Görev Barları -->
                            @foreach($project->phases as $ph)
                                @php
                                    $phStart = $ph->start_date ?: $projStart;
                                    $phEnd = $ph->end_date ?: ($phStart ? $phStart->copy()->addDays(20) : null);
                                    $phStartOffset = max(0, (int) round($minDate->diffInDays($phStart)));
                                    $phDuration = max(1, (int) round($phStart->diffInDays($phEnd)));
                                    $phBarLeft = $phStartOffset * $pxPerDay;
                                    $phBarWidth = $phDuration * $pxPerDay;
                                    $phFillWidth = ($phBarWidth * $ph->progress_percentage) / 100;
                                @endphp
                                <div class="gantt-timeline-row phase-t-row" style="height: 52px; display: flex; align-items: center; position: relative; border-bottom: 1px solid #e2e8f0; background: #fafafa;">
                                    <div class="dual-gantt-bar-wrap" style="position: absolute; left: {{ $phBarLeft }}px; width: {{ $phBarWidth }}px; height: 24px; border-radius: 6px; background: #cbd5e1; border: 1.5px solid #64748b; box-sizing: border-box; display: flex; align-items: center; padding: 0 6px;" title="Faz: {{ $ph->name }}">
                                        <div style="position: absolute; left: 0; top: 0; bottom: 0; width: {{ $phFillWidth }}px; background: linear-gradient(135deg, #0369a1 0%, #075985 100%); border-radius: 5px;"></div>
                                        <div style="position: relative; z-index: 2; font-size: 10.5px; font-weight: 700; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            🔷 {{ $ph->name }} (%{{ (int) round($ph->progress_percentage) }})
                                        </div>
                                    </div>
                                </div>

                                <!-- Görev Çift Barları -->
                                @foreach($ph->tasks as $t)
                                    @php
                                        $tStart = $t->start_date ?: ($ph->start_date ?: $projStart);
                                        $tDue = $t->due_date ?: ($ph->end_date ?: ($tStart ? $tStart->copy()->addDays(7) : null));
                                        $tStartOffset = max(0, (int) round($minDate->diffInDays($tStart)));
                                        $tDuration = max(1, (int) round($tStart->diffInDays($tDue)));
                                        
                                        $tBarLeft = $tStartOffset * $pxPerDay;
                                        $tPlannedWidth = $tDuration * $pxPerDay;
                                        $tActualFillWidth = ($tPlannedWidth * $t->progress_percentage) / 100;

                                        $isDelayed = ($t->progress_percentage < 100 && $tDue && $tDue < $today);
                                        $overdueDays = $isDelayed ? (int) round($tDue->diffInDays($today)) : 0;
                                        $overdueHatchWidth = $isDelayed ? ($overdueDays * $pxPerDay) : 0;
                                    @endphp
                                    <div class="gantt-timeline-row task-t-row {{ $isDelayed ? 'delayed-task-row' : '' }}" style="height: 52px; display: flex; align-items: center; position: relative; border-bottom: 1px solid #f1f5f9;">
                                        
                                        <!-- PLANLANAN SÜRE BANDI -->
                                        <div class="planned-scope-band" style="position: absolute; left: {{ $tBarLeft }}px; width: {{ $tPlannedWidth }}px; height: 26px; border-radius: 6px; background: #f1f5f9; border: 1.5px solid #cbd5e1; box-sizing: border-box; display: flex; align-items: center; padding: 0 4px;" title="Planlanan Süre: {{ $tStart->format('d.m.Y') }} - {{ $tDue ? $tDue->format('d.m.Y') : '-' }} ({{ (int) round($tDuration) }} gün)">
                                            
                                            <!-- FİİLİ MEVCUT DURUM / İLERLEME BARI -->
                                            <div class="actual-progress-fill" style="position: absolute; left: 0; top: 0; bottom: 0; width: {{ $tActualFillWidth }}px; background: {{ $t->progress_percentage == 100 ? '#16a34a' : ($isDelayed ? '#ea580c' : '#0284c7') }}; border-radius: 5px; opacity: 0.9; transition: width 0.3s;"></div>

                                            <!-- Görev İçi Metin & Yüzde -->
                                            <div style="position: relative; z-index: 2; font-size: 10.5px; font-weight: 700; color: {{ $tActualFillWidth > 50 ? '#ffffff' : '#0f172a' }}; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding-left: 4px; pointer-events: none;">
                                                @if($t->is_milestone) 💎 @endif
                                                {{ Str::limit($t->title, 20) }} • %{{ (int) round($t->progress_percentage) }}
                                            </div>
                                        </div>

                                        <!-- GECİKME / PLAN AŞIMI ÇİZGİLİ BLOĞU -->
                                        @if($isDelayed && $overdueHatchWidth > 0)
                                            <div class="delay-overdue-hatch" style="position: absolute; left: {{ $tBarLeft + $tPlannedWidth }}px; width: {{ $overdueHatchWidth }}px; height: 26px; border-radius: 0 6px 6px 0; background: repeating-linear-gradient(45deg, #ef4444, #ef4444 6px, #fee2e2 6px, #fee2e2 12px); border: 1.5px dashed #dc2626; border-left: none; box-sizing: border-box; display: flex; align-items: center; justify-content: center; z-index: 6;" title="⚠️ DİKKAT: Görev {{ $overdueDays }} gündür planlanan bitiş tarihini aştı!">
                                                <span style="font-size: 10px; font-weight: 900; color: #991b1b; background: #ffffff; padding: 1px 5px; border-radius: 4px; border: 1px solid #ef4444; white-space: nowrap; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                                    ⚠️ +{{ $overdueDays }}g AŞTI
                                                </span>
                                            </div>
                                        @endif

                                        <!-- KİLOMETRE TAŞI ELMAS SİMGESİ (CELOXIS MODELİ) -->
                                        @if($t->is_milestone)
                                            <div class="gantt-milestone-diamond" style="position: absolute; left: {{ $tBarLeft + ($tPlannedWidth / 2) - 9 }}px; top: 17px; width: 18px; height: 18px; transform: rotate(45deg); background: linear-gradient(135deg, #f59e0b, #d97706); border: 2px solid #ffffff; box-shadow: 0 2px 6px rgba(217, 119, 6, 0.4); z-index: 8;" title="Kilometre Taşı (Milestone): {{ $t->title }} ({{ $tDue ? $tDue->format('d.m.Y') : '' }})"></div>
                                        @endif

                                        <!-- ATANAN KİŞİ ETİKETİ (BARIN SAĞ UCUNDA CELOXIS ROZETİ) -->
                                        @if($t->assignee)
                                            <div class="floating-assignee-pill" style="position: absolute; left: {{ $tBarLeft + $tPlannedWidth + ($isDelayed ? $overdueHatchWidth : 0) + 8 }}px; display: inline-flex; align-items: center; gap: 5px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 12px; padding: 2px 8px 2px 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); font-size: 11px; font-weight: 700; color: #334155; pointer-events: none; white-space: nowrap; z-index: 7;">
                                                <span style="width: 17px; height: 17px; border-radius: 50%; background: #0284c7; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 9.5px; font-weight: 800;">
                                                    {{ mb_substr($t->assignee->name, 0, 1) }}
                                                </span>
                                                <span>{{ $t->assignee->name }}</span>
                                            </div>
                                        @endif

                                        <!-- TEMEL ÇİZGİ (BASELINE v1.0) KARŞILAŞTIRMA ÇUBUĞU VE SAPMA ROZETİ -->
                                        @if($activeBaseline && isset($baselineTasksMap))
                                            @php
                                                $bTask = $baselineTasksMap->get($t->id);
                                            @endphp
                                            @if($bTask && $bTask->baseline_start_date && $bTask->baseline_due_date)
                                                @php
                                                    $btStart = \Carbon\Carbon::parse($bTask->baseline_start_date);
                                                    $btDue = \Carbon\Carbon::parse($bTask->baseline_due_date);
                                                    $btOffset = max(0, (int) round($minDate->diffInDays($btStart)));
                                                    $btDuration = max(1, (int) round($btStart->diffInDays($btDue)));
                                                    $btLeft = $btOffset * $pxPerDay;
                                                    $btWidth = $btDuration * $pxPerDay;
                                                    $varDays = $bTask->getDaysVariance();
                                                @endphp
                                                <div class="baseline-bar task-baseline-bar" style="display: none; position: absolute; left: {{ $btLeft }}px; width: {{ $btWidth }}px; bottom: 3px; height: 6px; border-radius: 3px; background: #6366f1; border-top: 2px dashed #3730a3; z-index: 4;" title="Temel Çizgi ({{ $activeBaseline->version }}): {{ $btStart->format('d.m.Y') }} - {{ $btDue->format('d.m.Y') }} ({{ (int) round($btDuration) }} gün)"></div>
                                                @if($varDays !== null && $varDays != 0)
                                                    <div class="baseline-bar task-variance-badge" style="display: none; position: absolute; left: {{ max($tBarLeft + $tPlannedWidth, $btLeft + $btWidth) + ($t->assignee ? 115 : 10) }}px; bottom: 3px; z-index: 7;">
                                                        <span style="font-size: 9.5px; font-weight: 800; padding: 1px 6px; border-radius: 4px; white-space: nowrap; {{ $varDays > 0 ? 'background: #fee2e2; color: #b91c1c; border: 1px solid #f87171;' : 'background: #dcfce7; color: #15803d; border: 1px solid #86efac;' }}">
                                                            {{ $varDays > 0 ? "+{$varDays}g gecikme" : "{$varDays}g erken" }}
                                                        </span>
                                                    </div>
                                                @endif
                                            @endif
                                        @endif

                                    </div>
                                @endforeach
                            @endforeach

                        </div>

                    </div>
                </div>

            </div>
        </div>
    @endif

<script>
// =========================================================================
// 1. ZAMAN ÖLÇEĞİ MOTORU (GÜNLÜK / HAFTALIK / AYLIK)
// =========================================================================
function changeTimeScale(scale) {
    const url = new URL(window.location.href);
    url.searchParams.set('scale', scale);
    window.location.href = url.toString();
}

function changeProject(projId) {
    const url = new URL(window.location.href);
    url.searchParams.set('project_id', projId);
    window.location.href = url.toString();
}

// =========================================================================
// 2. MOBİL SEKME GEÇİŞ MOTORU (ZAMAN ÇİZELGESİ / GÖREV LİSTESİ)
// =========================================================================
function setMobileGanttTab(tab) {
    const container = document.querySelector('.gantt-container');
    const btnTimeline = document.getElementById('tabBtnTimeline');
    const btnTable = document.getElementById('tabBtnTable');

    if (!container) return;

    if (tab === 'table') {
        container.classList.remove('mobile-show-timeline');
        container.classList.add('mobile-show-table');
        if (btnTable) btnTable.classList.add('active');
        if (btnTimeline) btnTimeline.classList.remove('active');
    } else {
        container.classList.remove('mobile-show-table');
        container.classList.add('mobile-show-timeline');
        if (btnTimeline) btnTimeline.classList.add('active');
        if (btnTable) btnTable.classList.remove('active');
        setTimeout(scrollToToday, 150);
    }
}

// =========================================================================
// 3. BUGÜNE ODAKLAN (WHERE WE ARE RIGHT NOW)
// =========================================================================
function scrollToToday() {
    const timeline = document.getElementById('ganttTimelineScroll');
    const marker = document.querySelector('.today-marker-line');
    if (timeline && marker) {
        const targetLeft = Math.max(0, marker.offsetLeft - (timeline.clientWidth / 2));
        timeline.scrollTo({ left: targetLeft, behavior: 'smooth' });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(scrollToToday, 300);
});

// =========================================================================
// 4. SÜTUN GİZLEME / GÖSTERME MOTORU (Kullanıcı Talebi & Dinamik Genişlik)
// =========================================================================
function toggleGanttCol(colName) {
    const key = 'gantt_show_' + colName;
    const current = localStorage.getItem(key) !== 'false';
    localStorage.setItem(key, current ? 'false' : 'true');
    applyGanttColumnVisibility();
}

function updateToggleBtn(id, isActive) {
    const btn = document.getElementById(id);
    if (!btn) return;
    const chk = btn.querySelector('.chk-icon');
    if (isActive) {
        btn.classList.add('active');
        if (chk) chk.textContent = '✓';
    } else {
        btn.classList.remove('active');
        if (chk) chk.textContent = '✕';
    }
}

function applyGanttColumnVisibility() {
    const showAssignee = localStorage.getItem('gantt_show_assignee') !== 'false';
    const showProgress = localStorage.getItem('gantt_show_progress') !== 'false';
    const showStatus = localStorage.getItem('gantt_show_status') !== 'false';

    updateToggleBtn('btnToggleAssignee', showAssignee);
    updateToggleBtn('btnToggleProgress', showProgress);
    updateToggleBtn('btnToggleStatus', showStatus);

    document.querySelectorAll('.gantt-col-assignee').forEach(el => {
        el.style.display = showAssignee ? 'block' : 'none';
    });
    document.querySelectorAll('.gantt-col-progress').forEach(el => {
        el.style.display = showProgress ? 'block' : 'none';
    });
    document.querySelectorAll('.gantt-col-status').forEach(el => {
        el.style.display = showStatus ? 'block' : 'none';
    });

    let colTemplates = ['210px'];
    let totalWidth = 210;

    if (showAssignee) {
        colTemplates.push('105px');
        totalWidth += 105;
    }
    if (showProgress) {
        colTemplates.push('80px');
        totalWidth += 80;
    }
    if (showStatus) {
        colTemplates.push('110px');
        totalWidth += 110;
    }

    totalWidth += 24;

    const gridTemplateString = colTemplates.join(' ');

    document.querySelectorAll('.gantt-table-row').forEach(row => {
        row.style.gridTemplateColumns = gridTemplateString;
    });

    const leftPane = document.querySelector('.gantt-left-pane');
    if (leftPane) {
        if (window.innerWidth > 768) {
            leftPane.style.width = totalWidth + 'px';
            leftPane.style.minWidth = totalWidth + 'px';
            leftPane.style.maxWidth = totalWidth + 'px';
        } else {
            leftPane.style.width = '100%';
            leftPane.style.minWidth = '100%';
            leftPane.style.maxWidth = '100%';
        }
    }
}

window.addEventListener('resize', function() {
    applyGanttColumnVisibility();
});

// =========================================================================
// 5. PDF & YAZDIRMA RAPORU
// =========================================================================
function printGanttReport() {
    window.print();
}

// =========================================================================
// 6. SADECE GECİKENLER FİLTRESİ
// =========================================================================
let onlyDelayedActive = false;

function toggleOnlyDelayed() {
    onlyDelayedActive = !onlyDelayedActive;
    const btn = document.getElementById('filterDelayedBtn');
    const taskRows = document.querySelectorAll('.task-row');
    const taskTRows = document.querySelectorAll('.task-t-row');

    if (onlyDelayedActive) {
        btn.style.background = '#dc2626';
        btn.style.color = '#ffffff';
        btn.textContent = '✓ Tüm Görevleri Göster';

        taskRows.forEach(row => {
            if (!row.classList.contains('delayed-task-row')) {
                row.style.display = 'none';
            }
        });
        taskTRows.forEach(row => {
            if (!row.classList.contains('delayed-task-row')) {
                row.style.display = 'none';
            }
        });
    } else {
        btn.style.background = '#fef2f2';
        btn.style.color = '#b91c1c';
        btn.textContent = '⚠️ Sadece Gecikenler';

        applyGanttColumnVisibility();
        taskTRows.forEach(row => row.style.display = 'flex');
    }
}

// =========================================================================
// 7. TEMEL ÇİZGİ (BASELINE) ÇİFT ÇUBUK VE SAPMA MODU
// =========================================================================
let baselineComparisonActive = false;

function toggleBaselineComparison() {
    const bars = document.querySelectorAll('.baseline-bar');
    const legend = document.querySelector('.baseline-legend-item');
    const btn = document.getElementById('btnToggleBaseline');
    
    if (!bars || bars.length === 0) {
        alert('Bu proje için henüz dondurulmuş bir Temel Çizgi (Baseline) kaydı bulunmuyor. Proje Çalışma Alanı detay sayfasından "Temel Çizgi Dondur (Baseline)" işlemi yapabilirsiniz.');
        return;
    }
    
    baselineComparisonActive = !baselineComparisonActive;
    
    bars.forEach(el => {
        el.style.display = baselineComparisonActive ? 'block' : 'none';
    });
    
    if (legend) {
        legend.style.display = baselineComparisonActive ? 'inline-flex' : 'none';
    }
    
    if (btn) {
        if (baselineComparisonActive) {
            btn.style.background = '#4338ca';
            btn.style.color = '#ffffff';
            btn.style.borderColor = '#312e81';
        } else {
            btn.style.background = '#eef2ff';
            btn.style.color = '#4338ca';
            btn.style.borderColor = '#6366f1';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    applyGanttColumnVisibility();
});
</script>
@endsection