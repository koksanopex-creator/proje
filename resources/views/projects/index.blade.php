@extends('layouts.app')

@section('title', 'Proje Portföyü')
@section('page_title', 'Proje Portföy Yönetimi')
@section('page_subtitle', 'Tüm mühendislik, inşaat, GES, makine ve Ar-Ge projeleri')

<style>
    .kpi-mini-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08) !important;
        border-color: #cbd5e1 !important;
    }
    .kpi-mini-card.active-kpi {
        transform: translateY(-1px);
    }
    .active-filter-badge {
        transition: all 0.15s ease;
    }
    .active-filter-badge:hover {
        filter: brightness(0.95);
    }

    /* KÖKSAN ÇOKLU SEÇİM (MULTI-SELECT) BİLEŞENİ */
    .koksan-multiselect {
        position: relative;
        user-select: none;
    }
    .koksan-multiselect-trigger {
        height: 40px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 0 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        cursor: pointer;
        font-size: 13px;
        color: #1e293b;
        transition: all 0.15s ease;
        width: 100%;
        text-align: left;
    }
    .koksan-multiselect-trigger:hover {
        border-color: #0284c7;
    }
    .koksan-multiselect-trigger.active {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
    }
    .koksan-multiselect-panel {
        position: absolute;
        top: calc(100% + 5px);
        left: 0;
        right: 0;
        min-width: 250px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        z-index: 1050;
        padding: 8px;
        display: none;
    }
    .koksan-multiselect-panel.show {
        display: block;
        animation: koksanFadeIn 0.15s ease;
    }
    @keyframes koksanFadeIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .koksan-multiselect-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 4px 6px 8px 6px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 11px;
        color: #64748b;
    }
    .koksan-multiselect-items {
        max-height: 220px;
        overflow-y: auto;
        padding: 4px 0;
    }
    .koksan-multiselect-item {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 7px 8px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 12.5px;
        color: #334155;
        transition: background 0.1s;
        margin: 0;
    }
    .koksan-multiselect-item:hover {
        background: #f1f5f9;
    }
    .koksan-multiselect-item input[type="checkbox"] {
        width: 15px;
        height: 15px;
        cursor: pointer;
        border-radius: 4px;
        accent-color: #0284c7;
        margin: 0;
    }
</style>

@section('content')
    <!-- 1. ÜST EYLEM & GÖRÜNÜM BARI (BAŞLIK & BUTONLARIN FERAH YERLEŞİMİ) -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 14px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 800; color: #0f172a; margin: 0 0 3px 0;">Proje Portföyü</h2>
            <p style="margin: 0; font-size: 13px; color: #64748b;">
                Tüm mühendislik, inşaat, GES, makine ve Ar-Ge projeleri 
                <span style="font-weight: 600; color: #0284c7;">({{ $stats['total'] }} Proje)</span>
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <!-- Görünüm Değiştirici (Kartlar / Liste) -->
            <div class="btn-group" style="background: #f1f5f9; padding: 3px; border-radius: 9px; border: 1px solid #cbd5e1; display: inline-flex;">
                <button type="button" id="btnViewGrid" onclick="setProjectsView('grid')" class="btn btn-xs" style="padding: 6px 14px; font-size: 12.5px; font-weight: 750; border-radius: 7px; border: none; cursor: pointer; transition: all 0.15s;" title="Kart Görünümü">
                    ⊞ Kartlar
                </button>
                <button type="button" id="btnViewTable" onclick="setProjectsView('table')" class="btn btn-xs" style="padding: 6px 14px; font-size: 12.5px; font-weight: 750; border-radius: 7px; border: none; cursor: pointer; transition: all 0.15s;" title="Liste / Tablo Görünümü">
                    ☰ Liste
                </button>
            </div>

            @if(auth()->user()->hasPermission('proje.create'))
                <a href="{{ route('projects.create') }}" 
                   id="btnNewProject"
                   onclick="return handleNewProjectClick(this);"
                   class="btn btn-primary" 
                   style="height: 40px; font-size: 13.5px; font-weight: 750; padding: 0 18px; display: inline-flex; align-items: center; gap: 6px; border-radius: 8px; box-shadow: 0 2px 4px rgba(2,132,199,0.25); text-decoration: none;">
                    <span id="btnNewProjectIcon" style="font-size: 16px; font-weight: 800;">+</span>
                    <span id="btnNewProjectText">Yeni Proje Başlat</span>
                </a>
            @endif
        </div>
    </div>

    @php
        $currentStatusFilter = request('status') ?: (count((array)$selectedStatuses) === 1 ? $selectedStatuses[0] : null);
    @endphp

    <!-- 2. TEK BAKIŞTA KOMPAKT İSTATİSTİK KARTLARI (KPI BAR) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 20px;">
        <!-- Toplam Portföy -->
        <a href="{{ route('projects.index', array_merge(request()->except(['status', 'statuses', 'page']), ['time_filter' => request('time_filter', 'all')])) }}" 
           style="text-decoration: none; display: block;">
            <div class="kpi-mini-card {{ empty($selectedStatuses) && !request('status') ? 'active-kpi' : '' }}" 
                 style="background: #ffffff; border: 1.5px solid {{ empty($selectedStatuses) && !request('status') ? '#0284c7' : '#e2e8f0' }}; border-radius: 12px; padding: 12px 14px; transition: all 0.2s; box-shadow: {{ empty($selectedStatuses) && !request('status') ? '0 4px 10px rgba(2,132,199,0.12)' : '0 1px 3px rgba(0,0,0,0.03)' }};">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                    <span style="font-size: 11px; font-weight: 750; color: #64748b; text-transform: uppercase;">Toplam Portföy</span>
                    <span style="font-size: 14px;">📁</span>
                </div>
                <div style="display: flex; align-items: baseline; justify-content: space-between;">
                    <span style="font-size: 22px; font-weight: 850; color: #0f172a;">{{ $stats['total'] }}</span>
                    <span style="font-size: 11px; font-weight: 600; color: #0284c7;">Tümü</span>
                </div>
            </div>
        </a>

        <!-- Devam Edenler -->
        <a href="{{ route('projects.index', array_merge(request()->except(['status', 'statuses', 'page']), ['status' => 'in_progress'])) }}" 
           style="text-decoration: none; display: block;">
            <div class="kpi-mini-card {{ $currentStatusFilter === 'in_progress' ? 'active-kpi' : '' }}" 
                 style="background: #ffffff; border: 1.5px solid {{ $currentStatusFilter === 'in_progress' ? '#f59e0b' : '#e2e8f0' }}; border-radius: 12px; padding: 12px 14px; transition: all 0.2s; box-shadow: {{ $currentStatusFilter === 'in_progress' ? '0 4px 10px rgba(245,158,11,0.15)' : '0 1px 3px rgba(0,0,0,0.03)' }};">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                    <span style="font-size: 11px; font-weight: 750; color: #b45309; text-transform: uppercase;">Devam Eden</span>
                    <span style="font-size: 14px;">⚡</span>
                </div>
                <div style="display: flex; align-items: baseline; justify-content: space-between;">
                    <span style="font-size: 22px; font-weight: 850; color: #d97706;">{{ $stats['in_progress'] }}</span>
                    <span style="font-size: 11px; font-weight: 600; color: #d97706;">Süreçte</span>
                </div>
            </div>
        </a>

        <!-- Onay Bekleyenler -->
        <a href="{{ route('projects.index', array_merge(request()->except(['status', 'statuses', 'page']), ['status' => 'waiting_approval'])) }}" 
           style="text-decoration: none; display: block;">
            <div class="kpi-mini-card {{ $currentStatusFilter === 'waiting_approval' ? 'active-kpi' : '' }}" 
                 style="background: #ffffff; border: 1.5px solid {{ $currentStatusFilter === 'waiting_approval' ? '#0284c7' : '#e2e8f0' }}; border-radius: 12px; padding: 12px 14px; transition: all 0.2s; box-shadow: {{ $currentStatusFilter === 'waiting_approval' ? '0 4px 10px rgba(2,132,199,0.15)' : '0 1px 3px rgba(0,0,0,0.03)' }};">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                    <span style="font-size: 11px; font-weight: 750; color: #0369a1; text-transform: uppercase;">Onay Bekleyen</span>
                    <span style="font-size: 14px;">⏳</span>
                </div>
                <div style="display: flex; align-items: baseline; justify-content: space-between;">
                    <span style="font-size: 22px; font-weight: 850; color: #0284c7;">{{ $stats['waiting_approval'] }}</span>
                    <span style="font-size: 11px; font-weight: 600; color: #0284c7;">İncelemede</span>
                </div>
            </div>
        </a>

        <!-- Taslak Projeler -->
        <a href="{{ route('projects.index', array_merge(request()->except(['status', 'statuses', 'page']), ['status' => 'draft'])) }}" 
           style="text-decoration: none; display: block;">
            <div class="kpi-mini-card {{ $currentStatusFilter === 'draft' ? 'active-kpi' : '' }}" 
                 style="background: #ffffff; border: 1.5px solid {{ $currentStatusFilter === 'draft' ? '#64748b' : '#e2e8f0' }}; border-radius: 12px; padding: 12px 14px; transition: all 0.2s; box-shadow: {{ $currentStatusFilter === 'draft' ? '0 4px 10px rgba(100,116,139,0.15)' : '0 1px 3px rgba(0,0,0,0.03)' }};">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                    <span style="font-size: 11px; font-weight: 750; color: #475569; text-transform: uppercase;">Taslak Projeler</span>
                    <span style="font-size: 14px;">📝</span>
                </div>
                <div style="display: flex; align-items: baseline; justify-content: space-between;">
                    <span style="font-size: 22px; font-weight: 850; color: #475569;">{{ $stats['draft'] }}</span>
                    <span style="font-size: 11px; font-weight: 600; color: #64748b;">Hazırlık</span>
                </div>
            </div>
        </a>

        <!-- Tamamlananlar -->
        <a href="{{ route('projects.index', array_merge(request()->except(['status', 'statuses', 'page']), ['status' => 'completed'])) }}" 
           style="text-decoration: none; display: block;">
            <div class="kpi-mini-card {{ $currentStatusFilter === 'completed' ? 'active-kpi' : '' }}" 
                 style="background: #ffffff; border: 1.5px solid {{ $currentStatusFilter === 'completed' ? '#10b981' : '#e2e8f0' }}; border-radius: 12px; padding: 12px 14px; transition: all 0.2s; box-shadow: {{ $currentStatusFilter === 'completed' ? '0 4px 10px rgba(16,185,129,0.15)' : '0 1px 3px rgba(0,0,0,0.03)' }};">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                    <span style="font-size: 11px; font-weight: 750; color: #047857; text-transform: uppercase;">Tamamlanan</span>
                    <span style="font-size: 14px;">✅</span>
                </div>
                <div style="display: flex; align-items: baseline; justify-content: space-between;">
                    <span style="font-size: 22px; font-weight: 850; color: #16a34a;">{{ $stats['completed'] }}</span>
                    <span style="font-size: 11px; font-weight: 600; color: #16a34a;">Başarılı</span>
                </div>
            </div>
        </a>

        <!-- İptal & Askıda -->
        <a href="{{ route('projects.index', array_merge(request()->except(['status', 'statuses', 'page']), ['status' => 'cancelled_on_hold'])) }}" 
           style="text-decoration: none; display: block;">
            <div class="kpi-mini-card {{ $currentStatusFilter === 'cancelled_on_hold' ? 'active-kpi' : '' }}" 
                 style="background: #ffffff; border: 1.5px solid {{ $currentStatusFilter === 'cancelled_on_hold' ? '#ef4444' : '#e2e8f0' }}; border-radius: 12px; padding: 12px 14px; transition: all 0.2s; box-shadow: {{ $currentStatusFilter === 'cancelled_on_hold' ? '0 4px 10px rgba(239,68,68,0.15)' : '0 1px 3px rgba(0,0,0,0.03)' }};">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                    <span style="font-size: 11px; font-weight: 750; color: #b91c1c; text-transform: uppercase;">İptal / Askıda</span>
                    <span style="font-size: 14px;">⛔</span>
                </div>
                <div style="display: flex; align-items: baseline; justify-content: space-between;">
                    <span style="font-size: 22px; font-weight: 850; color: #dc2626;">{{ $stats['cancelled_on_hold'] }}</span>
                    <span style="font-size: 11px; font-weight: 600; color: #dc2626;">Durduruldu</span>
                </div>
            </div>
        </a>
    </div>

    <!-- 3. FERAH VE KAPSAMLI FİLTRELEME ALANI (ÇOKTAN SEÇMELİ VE TARİH DAYANAKLI) -->
    <div class="card" style="margin-bottom: 20px; border-radius: 14px; border: 1px solid #e2e8f0; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        <div class="card-body" style="padding: 18px 20px;">
            <form action="{{ route('projects.index') }}" method="GET" id="projectsFilterForm">
                
                <!-- 1. SATIR: METİN ARAMA, ÇOKLU KATEGORİ, ÇOKLU DURUM, BÖLÜM & AKSİYON BUTONLARI -->
                <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    
                    <!-- Arama Inputu -->
                    <div style="flex: 1.4; min-width: 220px;">
                        <input type="text" name="search" class="form-control" style="height: 40px; font-size: 13px; border-radius: 8px;" placeholder="🔍 Kod veya başlık ile ara..." value="{{ request('search') }}">
                    </div>

                    <!-- ÇOKTAN SEÇMELİ KATEGORİ FİLTRESİ -->
                    @php
                        $catCount = count((array)$selectedCategories);
                    @endphp
                    <div class="koksan-multiselect" id="msCategory" style="flex: 1.1; min-width: 180px;">
                        <button type="button" class="koksan-multiselect-trigger" onclick="toggleMultiSelect('msCategory')">
                            <span class="trigger-label" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 550;">
                                @if($catCount === 0)
                                    🏷️ Tüm Kategoriler
                                @elseif($catCount === 1)
                                    🏷️ {{ $categories->firstWhere('slug', $selectedCategories[0])?->name ?? $selectedCategories[0] }}
                                @else
                                    🏷️ {{ $catCount }} Kategori Seçildi
                                @endif
                            </span>
                            <span style="font-size: 10px; color: #64748b;">▼</span>
                        </button>
                        <div class="koksan-multiselect-panel">
                            <div class="koksan-multiselect-header">
                                <span class="selection-counter" style="font-weight: 700;">{{ $catCount }} seçildi</span>
                                <button type="button" onclick="clearMultiSelect('msCategory', '🏷️ Tüm Kategoriler')" style="background: none; border: none; color: #ef4444; font-size: 11px; font-weight: 600; cursor: pointer;">Temizle</button>
                            </div>
                            <div class="koksan-multiselect-items">
                                @foreach($categories as $cat)
                                    <label class="koksan-multiselect-item">
                                        <input type="checkbox" name="categories[]" value="{{ $cat->slug }}" {{ in_array($cat->slug, (array)$selectedCategories) ? 'checked' : '' }} onchange="onMultiSelectChange('msCategory', '🏷️ Tüm Kategoriler', 'Kategori')">
                                        <span>{{ $cat->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- ÇOKTAN SEÇMELİ DURUM FİLTRESİ -->
                    @php
                        $stCount = count((array)$selectedStatuses);
                        $statusLabelsMap = [
                            'draft' => 'Taslak Projeler',
                            'in_progress' => 'Süreçte / Devam Eden',
                            'waiting_approval' => 'Onay Bekleyenler',
                            'completed' => 'Tamamlanan Projeler',
                            'cancelled_on_hold' => 'İptal & Askıda Olanlar',
                        ];
                    @endphp
                    <div class="koksan-multiselect" id="msStatus" style="flex: 1.1; min-width: 180px;">
                        <button type="button" class="koksan-multiselect-trigger" onclick="toggleMultiSelect('msStatus')">
                            <span class="trigger-label" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 550;">
                                @if($stCount === 0)
                                    ⚡ Tüm Durumlar
                                @elseif($stCount === 1)
                                    ⚡ {{ $statusLabelsMap[$selectedStatuses[0]] ?? $selectedStatuses[0] }}
                                @else
                                    ⚡ {{ $stCount }} Durum Seçildi
                                @endif
                            </span>
                            <span style="font-size: 10px; color: #64748b;">▼</span>
                        </button>
                        <div class="koksan-multiselect-panel">
                            <div class="koksan-multiselect-header">
                                <span class="selection-counter" style="font-weight: 700;">{{ $stCount }} seçildi</span>
                                <button type="button" onclick="clearMultiSelect('msStatus', '⚡ Tüm Durumlar')" style="background: none; border: none; color: #ef4444; font-size: 11px; font-weight: 600; cursor: pointer;">Temizle</button>
                            </div>
                            <div class="koksan-multiselect-items">
                                <label class="koksan-multiselect-item">
                                    <input type="checkbox" name="statuses[]" value="draft" {{ in_array('draft', (array)$selectedStatuses) ? 'checked' : '' }} onchange="onMultiSelectChange('msStatus', '⚡ Tüm Durumlar', 'Durum')">
                                    <span>📝 Taslak Projeler</span>
                                </label>
                                <label class="koksan-multiselect-item">
                                    <input type="checkbox" name="statuses[]" value="in_progress" {{ in_array('in_progress', (array)$selectedStatuses) ? 'checked' : '' }} onchange="onMultiSelectChange('msStatus', '⚡ Tüm Durumlar', 'Durum')">
                                    <span>⏳ Süreçte / Devam Eden</span>
                                </label>
                                <label class="koksan-multiselect-item">
                                    <input type="checkbox" name="statuses[]" value="waiting_approval" {{ in_array('waiting_approval', (array)$selectedStatuses) ? 'checked' : '' }} onchange="onMultiSelectChange('msStatus', '⚡ Tüm Durumlar', 'Durum')">
                                    <span>⏱️ Onay Bekleyenler</span>
                                </label>
                                <label class="koksan-multiselect-item">
                                    <input type="checkbox" name="statuses[]" value="completed" {{ in_array('completed', (array)$selectedStatuses) ? 'checked' : '' }} onchange="onMultiSelectChange('msStatus', '⚡ Tüm Durumlar', 'Durum')">
                                    <span>✅ Tamamlanan Projeler</span>
                                </label>
                                <label class="koksan-multiselect-item">
                                    <input type="checkbox" name="statuses[]" value="cancelled_on_hold" {{ in_array('cancelled_on_hold', (array)$selectedStatuses) ? 'checked' : '' }} onchange="onMultiSelectChange('msStatus', '⚡ Tüm Durumlar', 'Durum')">
                                    <span>⛔ İptal & Askıda Olanlar</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isDirector() || auth()->user()->isYonetim())
                        <!-- Bölüm Filtresi -->
                        <div style="flex: 1; min-width: 160px;">
                            <select name="bolum_id" class="form-control" style="height: 40px; font-size: 13px; border-radius: 8px;">
                                <option value="">🏢 Tüm Bölümler</option>
                                @foreach($bolumler as $bolum)
                                    <option value="{{ $bolum->id }}" {{ request('bolum_id') == $bolum->id ? 'selected' : '' }}>
                                        {{ $bolum->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Filtre Butonları -->
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <button type="submit" class="btn btn-primary" style="height: 40px; font-size: 13px; font-weight: 700; padding: 0 18px; border-radius: 8px;">
                            Filtrele
                        </button>
                        <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary" style="height: 40px; font-size: 13px; padding: 0 14px; border-radius: 8px; display: inline-flex; align-items: center;" title="Tüm filtreleri sıfırla">
                            Sıfırla
                        </a>
                    </div>
                </div>

                <!-- 2. SATIR: GELİŞMİŞ TARİH DAYANAĞI & ÖZEL TARİH ARALIĞI FİLTRESİ -->
                <div style="margin-top: 14px; padding-top: 12px; border-top: 1px dashed #e2e8f0; display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 6px; color: #475569; font-size: 12px; font-weight: 750; text-transform: uppercase;">
                        <span>📅</span>
                        <span>Tarih Filtresi:</span>
                    </div>

                    <!-- Tarih Türü (Dayanak) Seçimi -->
                    <div style="min-width: 210px;">
                        <select name="date_type" class="form-control" style="height: 38px; font-size: 12.5px; border-radius: 8px; font-weight: 600; color: #1e293b; background: #f8fafc;" title="Filtrelemede esas alınacak tarih türü">
                            <option value="created_at" {{ ($dateType ?? 'created_at') === 'created_at' ? 'selected' : '' }}>📝 Taslak / Kayıt Tarihi</option>
                            <option value="start_date" {{ ($dateType ?? '') === 'start_date' ? 'selected' : '' }}>🚀 Tahmini Başlangıç Tarihi</option>
                            <option value="target_end_date" {{ ($dateType ?? '') === 'target_end_date' ? 'selected' : '' }}>🏁 Tahmini Bitiş Tarihi</option>
                        </select>
                    </div>

                    <!-- Zaman Aralığı / Presets -->
                    <div style="min-width: 180px;">
                        <select name="time_filter" id="timeFilterSelect" onchange="toggleCustomDateInputs(this.value)" class="form-control" style="height: 38px; font-size: 12.5px; border-radius: 8px; font-weight: 600; color: #1e293b; background: #f8fafc;">
                            <option value="all" {{ ($timeFilter ?? 'all') === 'all' ? 'selected' : '' }}>📅 Tüm Zamanlar</option>
                            <option value="this_month" {{ ($timeFilter ?? '') === 'this_month' ? 'selected' : '' }}>📅 Bu Ay ({{ now()->locale('tr')->isoFormat('MMMM') }})</option>
                            <option value="this_quarter" {{ ($timeFilter ?? '') === 'this_quarter' ? 'selected' : '' }}>📅 Bu Çeyrek (Q{{ now()->quarter }})</option>
                            <option value="this_year" {{ ($timeFilter ?? '') === 'this_year' ? 'selected' : '' }}>📅 Bu Yıl ({{ now()->year }})</option>
                            <option value="last_year" {{ ($timeFilter ?? '') === 'last_year' ? 'selected' : '' }}>📅 Geçen Yıl ({{ now()->subYear()->year }})</option>
                            <option value="custom" {{ ($timeFilter ?? '') === 'custom' || (!empty($dateFrom) || !empty($dateTo)) ? 'selected' : '' }}>🗓️ Özel Tarih Aralığı...</option>
                        </select>
                    </div>

                    <!-- Özel Tarih Aralığı Inputları -->
                    <div id="customDateRangeInputs" style="display: {{ ($timeFilter ?? '') === 'custom' || (!empty($dateFrom) || !empty($dateTo)) ? 'flex' : 'none' }}; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <span style="font-size: 11px; font-weight: 650; color: #64748b;">Başlangıç:</span>
                            <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="form-control" style="height: 38px; font-size: 12px; border-radius: 8px; width: 140px;">
                        </div>
                        <div style="display: flex; align-items: center; gap: 5px;">
                            <span style="font-size: 11px; font-weight: 650; color: #64748b;">Bitiş:</span>
                            <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="form-control" style="height: 38px; font-size: 12px; border-radius: 8px; width: 140px;">
                        </div>
                    </div>
                </div>
            </form>

            @php
                $hasActiveFilters = request()->filled('search') || 
                                    !empty($selectedCategories) || 
                                    !empty($selectedStatuses) || 
                                    request()->filled('bolum_id') || 
                                    (($timeFilter ?? 'all') !== 'all') ||
                                    !empty($dateFrom) || !empty($dateTo);

                $activeBolum = request('bolum_id') ? $bolumler->firstWhere('id', request('bolum_id')) : null;

                $dateTypeNames = [
                    'created_at' => 'Taslak/Kayıt',
                    'start_date' => 'Tahmini Başlangıç',
                    'target_end_date' => 'Tahmini Bitiş',
                ];
                $activeDateTypeName = $dateTypeNames[$dateType ?? 'created_at'] ?? 'Tarih';

                $timeFilterNames = [
                    'this_month' => 'Bu Ay (' . now()->locale('tr')->isoFormat('MMMM') . ')',
                    'this_quarter' => 'Bu Çeyrek (Q' . now()->quarter . ')',
                    'this_year' => 'Bu Yıl (' . now()->year . ')',
                    'last_year' => 'Geçen Yıl (' . now()->subYear()->year . ')',
                ];
            @endphp

            <!-- 4. AKTİF FİLTRE ROZETLERİ (TEK TEK KALDIRILABİLİR ETİKETLER) -->
            @if($hasActiveFilters)
                <div style="margin-top: 14px; padding-top: 12px; border-top: 1px dashed #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <span style="font-size: 12px; font-weight: 750; color: #475569; text-transform: uppercase;">Aktif Filtreler:</span>

                        @if(request()->filled('search'))
                            <span class="active-filter-badge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-size: 11.5px; font-weight: 600; padding: 3px 10px; border-radius: 20px; display: inline-flex; align-items: center; gap: 6px;">
                                <span>Arama: "{{ request('search') }}"</span>
                                <a href="{{ route('projects.index', request()->except('search')) }}" style="color: #0369a1; text-decoration: none; font-weight: 800;" title="Arama filtresini kaldır">✕</a>
                            </span>
                        @endif

                        <!-- Seçili Kategoriler Rozetleri -->
                        @foreach((array)$selectedCategories as $slug)
                            @php
                                $catItem = $categories->firstWhere('slug', $slug);
                                $remainingCats = array_values(array_diff((array)$selectedCategories, [$slug]));
                                $removeCatUrl = route('projects.index', array_merge(request()->except(['categories', 'category', 'page']), !empty($remainingCats) ? ['categories' => $remainingCats] : []));
                            @endphp
                            <span class="active-filter-badge" style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; font-size: 11.5px; font-weight: 600; padding: 3px 10px; border-radius: 20px; display: inline-flex; align-items: center; gap: 6px;">
                                <span>🏷️ {{ $catItem?->name ?? $slug }}</span>
                                <a href="{{ $removeCatUrl }}" style="color: #475569; text-decoration: none; font-weight: 800;" title="Kategori filtresini kaldır">✕</a>
                            </span>
                        @endforeach

                        <!-- Seçili Durumlar Rozetleri -->
                        @foreach((array)$selectedStatuses as $st)
                            @php
                                $remainingStats = array_values(array_diff((array)$selectedStatuses, [$st]));
                                $removeStatUrl = route('projects.index', array_merge(request()->except(['statuses', 'status', 'page']), !empty($remainingStats) ? ['statuses' => $remainingStats] : []));
                            @endphp
                            <span class="active-filter-badge" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-size: 11.5px; font-weight: 600; padding: 3px 10px; border-radius: 20px; display: inline-flex; align-items: center; gap: 6px;">
                                <span>⚡ {{ $statusLabelsMap[$st] ?? $st }}</span>
                                <a href="{{ $removeStatUrl }}" style="color: #92400e; text-decoration: none; font-weight: 800;" title="Durum filtresini kaldır">✕</a>
                            </span>
                        @endforeach

                        <!-- Tarih ve Zaman Rozeti -->
                        @if(($timeFilter ?? 'all') !== 'all' || !empty($dateFrom) || !empty($dateTo))
                            @php
                                $dateDisplay = '';
                                if (($timeFilter ?? '') === 'custom' || (!empty($dateFrom) || !empty($dateTo))) {
                                    $dateDisplay = ($dateFrom ? date('d.m.Y', strtotime($dateFrom)) : '...') . ' - ' . ($dateTo ? date('d.m.Y', strtotime($dateTo)) : '...');
                                } else {
                                    $dateDisplay = $timeFilterNames[$timeFilter] ?? $timeFilter;
                                }
                                $removeDateUrl = route('projects.index', request()->except(['time_filter', 'date_type', 'date_from', 'date_to', 'page']));
                            @endphp
                            <span class="active-filter-badge" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-size: 11.5px; font-weight: 600; padding: 3px 10px; border-radius: 20px; display: inline-flex; align-items: center; gap: 6px;">
                                <span>📅 [{{ $activeDateTypeName }}]: {{ $dateDisplay }}</span>
                                <a href="{{ $removeDateUrl }}" style="color: #065f46; text-decoration: none; font-weight: 800;" title="Tarih filtresini kaldır">✕</a>
                            </span>
                        @endif

                        @if($activeBolum)
                            <span class="active-filter-badge" style="background: #f3e8ff; color: #6b21a8; border: 1px solid #e9d5ff; font-size: 11.5px; font-weight: 600; padding: 3px 10px; border-radius: 20px; display: inline-flex; align-items: center; gap: 6px;">
                                <span>🏢 Bölüm: {{ $activeBolum->name }}</span>
                                <a href="{{ route('projects.index', request()->except('bolum_id')) }}" style="color: #6b21a8; text-decoration: none; font-weight: 800;" title="Bölüm filtresini kaldır">✕</a>
                            </span>
                        @endif

                        <a href="{{ route('projects.index') }}" style="font-size: 11.5px; font-weight: 700; color: #ef4444; text-decoration: none; margin-left: 6px;" title="Tüm filtreleri kaldır">
                            Tümünü Temizle
                        </a>
                    </div>

                    <div style="font-size: 12px; font-weight: 700; color: #0284c7;">
                        ✓ Filtrelenen Sonuç: {{ $projects->total() }} Proje
                    </div>
                </div>
            @endif
        </div>
    </div>

    @php
        $initialView = request('view', 'grid');
    @endphp

    <!-- 1. KART GÖRÜNÜMÜ (GRID 3) -->
    <div id="projectsGridView" class="grid-3" style="{{ $initialView === 'table' ? 'display: none;' : 'display: grid;' }} grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 20px;">
        @forelse($projects as $p)
            @include('partials.projects.project_card', ['p' => $p])
        @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px; color: #64748b; background: #ffffff; border-radius: 12px; border: 1px dashed #cbd5e1;">
                Kriterlere uygun proje bulunamadı.
            </div>
        @endforelse
    </div>

    <!-- 2. LİSTE GÖRÜNÜMÜ (TABLO) -->
    <div id="projectsTableView" style="{{ $initialView === 'table' ? 'display: block;' : 'display: none;' }}">
        @include('partials.projects.project_table', ['projects' => $projects])
    </div>

    <div style="margin-top: 24px;">
        {{ $projects->links() }}
    </div>

    <!-- PROJELER KAPSAM & ANALİZ ARAÇLARI VE EKİP KADROSU MODALLARI -->
    @foreach($projects as $p)
        @if($p->getActiveScopeToolsCount() > 0)
            @include('partials.projects.scope_tools_modal', ['p' => $p])
        @endif
        @include('partials.projects.project_team_modal', ['p' => $p])
    @endforeach

    <!-- MULTI-SELECT, TARİH ARALIĞI VE GÖRÜNÜM TERCİHİ JAVASCRIPT -->
    <script>
        // Multi-select Aç / Kapat
        function toggleMultiSelect(id) {
            const el = document.getElementById(id);
            if (!el) return;
            const panel = el.querySelector('.koksan-multiselect-panel');
            const trigger = el.querySelector('.koksan-multiselect-trigger');
            const isOpen = panel.classList.contains('show');

            // Diğer açık multi-selectleri kapat
            document.querySelectorAll('.koksan-multiselect-panel.show').forEach(p => {
                if (p !== panel) {
                    p.classList.remove('show');
                    p.closest('.koksan-multiselect')?.querySelector('.koksan-multiselect-trigger')?.classList.remove('active');
                }
            });

            if (isOpen) {
                panel.classList.remove('show');
                trigger.classList.remove('active');
            } else {
                panel.classList.add('show');
                trigger.classList.add('active');
            }
        }

        // Multi-select Checkbox Değişimi
        function onMultiSelectChange(id, defaultLabel, singularNoun) {
            const el = document.getElementById(id);
            if (!el) return;
            const checkedBoxes = el.querySelectorAll('input[type="checkbox"]:checked');
            const count = checkedBoxes.length;
            const labelSpan = el.querySelector('.trigger-label');
            const counterSpan = el.querySelector('.selection-counter');

            if (counterSpan) {
                counterSpan.textContent = count + ' seçildi';
            }

            if (count === 0) {
                labelSpan.textContent = defaultLabel;
            } else if (count === 1) {
                labelSpan.textContent = checkedBoxes[0].closest('.koksan-multiselect-item').querySelector('span').textContent.trim();
            } else {
                labelSpan.textContent = count + ' ' + singularNoun + ' Seçildi';
            }
        }

        // Multi-select Tümünü Temizle
        function clearMultiSelect(id, defaultLabel) {
            const el = document.getElementById(id);
            if (!el) return;
            el.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
            const labelSpan = el.querySelector('.trigger-label');
            const counterSpan = el.querySelector('.selection-counter');
            if (labelSpan) labelSpan.textContent = defaultLabel;
            if (counterSpan) counterSpan.textContent = '0 seçildi';
        }

        // Özel Tarih Aralığı Input Görünürlüğü
        function toggleCustomDateInputs(value) {
            const container = document.getElementById('customDateRangeInputs');
            if (!container) return;
            if (value === 'custom') {
                container.style.display = 'flex';
            } else {
                container.style.display = 'none';
            }
        }

        // Dışarı tıklandığında multi-select panellerini kapat
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.koksan-multiselect')) {
                document.querySelectorAll('.koksan-multiselect-panel.show').forEach(p => {
                    p.classList.remove('show');
                    p.closest('.koksan-multiselect')?.querySelector('.koksan-multiselect-trigger')?.classList.remove('active');
                });
            }
        });

        // Yeni Proje Başlat Butonunda Çift Tıklamayı Engelleme
        function handleNewProjectClick(button) {
            if (button.dataset.clicked === 'true') {
                return false;
            }
            button.dataset.clicked = 'true';
            button.style.pointerEvents = 'none';
            button.style.opacity = '0.7';

            const iconSpan = document.getElementById('btnNewProjectIcon');
            const textSpan = document.getElementById('btnNewProjectText');
            if (iconSpan) {
                iconSpan.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="width: 13px; height: 13px; border-width: 2px; vertical-align: middle;"></span>';
            }
            if (textSpan) {
                textSpan.textContent = 'Açılıyor...';
            }
            return true;
        }

        function setProjectsView(mode) {
            const gridView = document.getElementById('projectsGridView');
            const tableView = document.getElementById('projectsTableView');
            const btnGrid = document.getElementById('btnViewGrid');
            const btnTable = document.getElementById('btnViewTable');

            if (mode === 'table') {
                gridView.style.display = 'none';
                tableView.style.display = 'block';

                btnTable.style.background = '#ffffff';
                btnTable.style.color = '#0284c7';
                btnTable.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';

                btnGrid.style.background = 'transparent';
                btnGrid.style.color = '#64748b';
                btnGrid.style.boxShadow = 'none';
            } else {
                gridView.style.display = 'grid';
                tableView.style.display = 'none';

                btnGrid.style.background = '#ffffff';
                btnGrid.style.color = '#0284c7';
                btnGrid.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';

                btnTable.style.background = 'transparent';
                btnTable.style.color = '#64748b';
                btnTable.style.boxShadow = 'none';
            }

            localStorage.setItem('koksan_projects_view_mode', mode);
        }

        // Sayfa yüklendiğinde tercihi hatırla (URL parametresi veya localStorage)
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const urlView = urlParams.get('view');
            const savedMode = urlView || localStorage.getItem('koksan_projects_view_mode') || 'grid';
            setProjectsView(savedMode);
        });
    </script>
@endsection
