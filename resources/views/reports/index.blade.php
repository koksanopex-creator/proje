@extends('layouts.app')

@section('title', '5 Farklı Rapor Tipi')
@section('page_title', 'Raporlama & Karar Destek Analitiği')
@section('page_subtitle', '5 farklı kurumsal rapor tipi ile veri odaklı portföy yönetimi ve Excel stili anlık çoklu filtreleme')

@section('content')
<style>
/* Link & Vurgu Stilleri */
.project-code-link {
    color: #0284c7;
    font-weight: 700;
    text-decoration: none;
    transition: color 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 3px;
}
.project-code-link:hover {
    color: #0369a1;
    text-decoration: underline;
}
.project-title-text {
    font-weight: 600;
    color: #1e293b;
    display: inline-block;
}

/* Aktif Filtreler ve Sayaç Çubuğu (Üst Gösterge) */
.active-filters-container {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 16px;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
}
.active-filters-left {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    flex: 1;
}
.active-filters-title {
    font-size: 12px;
    font-weight: 700;
    color: #475569;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.active-filter-chips-list {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.active-filter-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #eff6ff;
    color: #1e40af;
    border: 1px solid #bfdbfe;
    padding: 3px 9px;
    border-radius: 16px;
    font-size: 11.5px;
    font-weight: 500;
    line-height: 1.3;
}
.active-filter-chip strong {
    color: #1e3a8a;
    font-weight: 700;
}
.chip-remove-btn {
    border: none;
    background: none;
    color: #3b82f6;
    font-size: 14px;
    cursor: pointer;
    padding: 0 2px;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.15s ease;
}
.chip-remove-btn:hover {
    color: #ef4444;
    background: #fee2e2;
}
.no-filter-text {
    font-size: 12px;
    color: #94a3b8;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.active-filters-right {
    display: flex;
    align-items: center;
    gap: 10px;
}
.report-counter-badge {
    background: #f8fafc;
    color: #1e293b;
    border: 1px solid #cbd5e1;
    font-size: 12px;
    padding: 5px 12px;
    font-weight: 600;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.report-counter-badge strong {
    color: #0284c7;
    font-weight: 800;
}

/* Excel Stili Sütun Başlığı Altı Filtre Satırı & Widget */
.excel-filter-row th {
    background: #f8fafc !important;
    padding: 6px 8px !important;
    border-bottom: 2px solid #e2e8f0 !important;
    vertical-align: middle;
}
.col-filter-wrap {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
}
.excel-col-filter {
    width: 100%;
    height: 30px;
    font-size: 11.5px;
    padding: 4px 28px 4px 8px; /* Sağ taraftaki dropdown butonu için boşluk */
    border: 1px solid #cbd5e1;
    border-radius: 5px;
    background: #ffffff;
    color: #1e293b;
    outline: none;
    box-sizing: border-box;
    transition: all 0.15s ease;
}
.excel-col-filter:focus {
    border-color: #0284c7;
    box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.12);
}
.excel-col-filter.has-value {
    border-color: #0284c7;
    background-color: #f0f9ff;
    font-weight: 600;
}
.col-filter-btn {
    position: absolute;
    right: 3px;
    top: 50%;
    transform: translateY(-50%);
    width: 24px;
    height: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    color: #64748b;
    padding: 0;
    transition: all 0.15s ease;
}
.col-filter-btn:hover {
    background: #e2e8f0;
    color: #0f172a;
}
.col-filter-btn.is-active {
    background: #0284c7;
    color: #ffffff;
}
.filter-count-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    background: #ef4444;
    color: #ffffff;
    font-size: 9px;
    font-weight: 700;
    border-radius: 10px;
    padding: 1px 4px;
    line-height: 1;
    min-width: 12px;
    text-align: center;
    box-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

/* Floating Popover Excel Dropdown */
.excel-dropdown-popover {
    position: fixed;
    z-index: 10000;
    width: 270px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: fadeInDown 0.12s ease-out;
}
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-6px); }
    to { opacity: 1; transform: translateY(0); }
}
.dropdown-header-bar {
    padding: 8px 12px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.dropdown-header-title {
    font-size: 12px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 6px;
}
.dropdown-close-btn {
    border: none;
    background: none;
    font-size: 16px;
    color: #94a3b8;
    cursor: pointer;
    line-height: 1;
    padding: 2px 4px;
    border-radius: 4px;
}
.dropdown-close-btn:hover {
    color: #0f172a;
    background: #e2e8f0;
}
.dropdown-search-box {
    padding: 8px 10px;
    border-bottom: 1px solid #f1f5f9;
}
.dropdown-search-input {
    width: 100%;
    height: 28px;
    font-size: 11.5px;
    padding: 3px 8px;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    outline: none;
    box-sizing: border-box;
}
.dropdown-search-input:focus {
    border-color: #0284c7;
}
.dropdown-quick-actions {
    padding: 6px 12px;
    background: #fafafa;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 11px;
}
.dropdown-action-link {
    color: #0284c7;
    cursor: pointer;
    background: none;
    border: none;
    padding: 0;
    font-weight: 600;
}
.dropdown-action-link:hover {
    text-decoration: underline;
}
.dropdown-items-list {
    max-height: 190px;
    overflow-y: auto;
    padding: 4px 6px;
}
.dropdown-item-row {
    display: flex;
    align-items: center;
    padding: 5px 8px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 11.5px;
    color: #334155;
    user-select: none;
    gap: 8px;
    transition: background 0.1s;
}
.dropdown-item-row:hover {
    background: #f1f5f9;
}
.dropdown-item-row input[type="checkbox"] {
    cursor: pointer;
    width: 14px;
    height: 14px;
    accent-color: #0284c7;
    margin: 0;
}
.dropdown-item-label {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.dropdown-item-count {
    color: #94a3b8;
    font-size: 10.5px;
}
.dropdown-footer-bar {
    padding: 8px 10px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    display: flex;
    gap: 6px;
    justify-content: flex-end;
}

/* Yazdırma Modunda Filtreleri Gizle */
@media print {
    .active-filters-container,
    .excel-filter-row,
    .report-nav-buttons,
    .excel-dropdown-popover {
        display: none !important;
    }
}
</style>

    <!-- RAPOR TİPİ SEÇİM BUTONLARI -->
    <div class="report-nav-buttons" style="display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap;">
        <a href="?type=portfolio" class="btn {{ $type === 'portfolio' ? 'btn-primary' : 'btn-secondary' }}">
            📊 1. Portföy Durum Raporu
        </a>
        <a href="?type=capacity" class="btn {{ $type === 'capacity' ? 'btn-primary' : 'btn-secondary' }}">
            👥 2. Kaynak Kapasite Raporu
        </a>
        <a href="?type=budget" class="btn {{ $type === 'budget' ? 'btn-primary' : 'btn-secondary' }}">
            💰 3. Bütçe ve Maliyet Sapma Raporu
        </a>
        <a href="?type=timesheet" class="btn {{ $type === 'timesheet' ? 'btn-primary' : 'btn-secondary' }}">
            ⏱️ 4. Zaman ve Efor Raporu
        </a>
        <a href="?type=risk" class="btn {{ $type === 'risk' ? 'btn-primary' : 'btn-secondary' }}">
            🛡️ 5. 5x5 Risk & Tehdit Raporu
        </a>
    </div>

    @php
        $totalItemsCount = match($type) {
            'portfolio' => $data['projects']->count(),
            'capacity'  => count($data['capacity']['user_rows'] ?? (is_array($data['capacity']) ? $data['capacity'] : [])),
            'budget'    => $data['projects']->count(),
            'timesheet' => $data['timesheets']->count(),
            'risk'      => $data['risks']->count(),
            default     => 0
        };
    @endphp

    <!-- AKTİF FİLTRELER GÖSTERGE & SAYAÇ ÇUBUĞU (Kullanıcının yaptığı tüm anlık/dropdown seçimleri burada listelenir) -->
    <div class="active-filters-container">
        <div class="active-filters-left">
            <span class="active-filters-title">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                </svg>
                Aktif Filtreler:
            </span>
            <div class="active-filter-chips-list" id="activeFilterChipsList">
                <span class="no-filter-text">ℹ️ Aktif filtre yok (Tüm kayıtlar listeleniyor)</span>
            </div>
            <button type="button" id="resetAllFiltersBtn" class="btn btn-secondary btn-sm" onclick="resetAllFilters()" style="display: none; padding: 3px 10px; font-size: 11.5px; font-weight: 600; color: #dc2626;">
                ✕ Tümünü Temizle
            </button>
        </div>

        <div class="active-filters-right">
            <span class="report-counter-badge">
                Gösterilen: <strong id="reportRowCount">{{ $totalItemsCount }}</strong> / {{ $totalItemsCount }} Kayıt
            </span>
        </div>
    </div>

    <!-- RAPOR TABLOSU KARTI -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">
                @if($type === 'portfolio') 1. Genel Portföy Durum ve Sağlık Raporu
                @elseif($type === 'capacity') 2. Kaynak Kapasite ve İş Yükü Raporu
                @elseif($type === 'budget') 3. Bütçe, CAPEX/OPEX ve Maliyet Sapma Raporu
                @elseif($type === 'timesheet') 4. Zaman ve Efor Harcama Raporu
                @elseif($type === 'risk') 5. Risk Değerlendirme ve Tehdit Raporu
                @endif
            </span>
            @can('export.data')
                <button class="btn btn-secondary" style="font-size: 11px; padding: 4px 10px;" onclick="window.print()">
                    📥 Dışa Aktar / Yazdır (PDF)
                </button>
            @endcan
        </div>
        <div class="card-body" style="padding: 0; overflow-x: auto;">

            <!-- 1. PORTFOLIO TABLOSU -->
            @if($type === 'portfolio')
                <table class="app-table" id="reportTable">
                    <thead>
                        <tr>
                            <th style="width: 140px;"># Proje Kodu</th>
                            <th>📋 Başlık</th>
                            <th style="width: 180px;">🏢 Bölüm</th>
                            <th style="width: 180px;">👤 Yönetici</th>
                            <th style="width: 110px;">📈 İlerleme</th>
                            <th style="width: 150px;">💰 Bütçe</th>
                            <th style="width: 140px;">📌 Durum</th>
                        </tr>
                        <!-- Excel Stili Sütun İçi Arama & Çoklu Dropdown Seçim Satırı -->
                        <tr class="excel-filter-row">
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="0" placeholder="Koda göre..." oninput="onColFilterInput(0)">
                                    <button type="button" class="col-filter-btn" data-col="0" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(0, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_0" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="1" placeholder="Başlığa göre..." oninput="onColFilterInput(1)">
                                    <button type="button" class="col-filter-btn" data-col="1" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(1, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_1" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="2" placeholder="Bölüme göre..." oninput="onColFilterInput(2)">
                                    <button type="button" class="col-filter-btn" data-col="2" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(2, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_2" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="3" placeholder="Yöneticiye göre..." oninput="onColFilterInput(3)">
                                    <button type="button" class="col-filter-btn" data-col="3" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(3, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_3" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="4" placeholder="%..." oninput="onColFilterInput(4)">
                                    <button type="button" class="col-filter-btn" data-col="4" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(4, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_4" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="5" placeholder="Bütçe tutarı..." oninput="onColFilterInput(5)">
                                    <button type="button" class="col-filter-btn" data-col="5" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(5, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_5" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="6" placeholder="Duruma göre..." oninput="onColFilterInput(6)">
                                    <button type="button" class="col-filter-btn" data-col="6" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(6, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_6" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="reportTableBody">
                        @forelse($data['projects'] as $p)
                            @php
                                $mgrName = $p->manager ? $p->manager->getMaskedNameFor(auth()->user()) : '-';
                                $statusLabel = $p->status_label;
                                $deptName = $p->bolum?->ad ?? '-';
                            @endphp
                            <tr class="report-data-row">
                                <td>
                                    <!-- Sadece proje koduna link verildi -->
                                    <a href="{{ route('projects.show', $p->id) }}" class="project-code-link" title="Projeyi Görüntüle">
                                        <strong>{{ $p->code }}</strong> ↗
                                    </a>
                                </td>
                                <td>
                                    <!-- Proje başlığındaki mükerrer link kaldırıldı -->
                                    <span class="project-title-text">{{ $p->title }}</span>
                                </td>
                                <td>{{ $deptName }}</td>
                                <td>{{ $mgrName }}</td>
                                <td>%{{ $p->progress_percentage }}</td>
                                <td>₺{{ number_format($p->planned_budget, 0, ',', '.') }}</td>
                                <td><x-status-badge :status="$p->status" /></td>
                            </tr>
                        @empty
                            <tr id="emptyInitialRow">
                                <td colspan="7" style="text-align: center; padding: 36px; color: var(--text-muted);">
                                    Kayıtlı proje bulunamadı.
                                </td>
                            </tr>
                        @endforelse
                        <tr id="reportNoResultsRow" style="display: none;">
                            <td colspan="7" style="text-align: center; padding: 36px; color: #64748b; background: #f8fafc;">
                                🔍 Filtre kriterlerinize uygun proje kaydı bulunamadı.
                            </td>
                        </tr>
                    </tbody>
                </table>

            <!-- 2. CAPACITY TABLOSU -->
            @elseif($type === 'capacity')
                <table class="app-table" id="reportTable">
                    <thead>
                        <tr>
                            <th>👤 Personel</th>
                            <th>💼 Unvan</th>
                            <th style="width: 120px;">📝 Aktif Görevler</th>
                            <th style="width: 130px;">⏱️ Toplam Efor</th>
                            <th style="width: 130px;">📊 Haftalık Doluluk</th>
                            <th style="width: 150px;">⚡ Çakışma Durumu</th>
                        </tr>
                        <!-- Excel Stili Sütun İçi Arama & Çoklu Dropdown Seçim Satırı -->
                        <tr class="excel-filter-row">
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="0" placeholder="Personele göre..." oninput="onColFilterInput(0)">
                                    <button type="button" class="col-filter-btn" data-col="0" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(0, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_0" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="1" placeholder="Unvana göre..." oninput="onColFilterInput(1)">
                                    <button type="button" class="col-filter-btn" data-col="1" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(1, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_1" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="2" placeholder="Göreve göre..." oninput="onColFilterInput(2)">
                                    <button type="button" class="col-filter-btn" data-col="2" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(2, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_2" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="3" placeholder="Saat eforuna göre..." oninput="onColFilterInput(3)">
                                    <button type="button" class="col-filter-btn" data-col="3" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(3, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_3" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="4" placeholder="Doluluk %..." oninput="onColFilterInput(4)">
                                    <button type="button" class="col-filter-btn" data-col="4" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(4, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_4" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="5" placeholder="Duruma göre..." oninput="onColFilterInput(5)">
                                    <button type="button" class="col-filter-btn" data-col="5" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(5, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_5" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="reportTableBody">
                        @php
                            $capacityRows = isset($data['capacity']['user_rows']) 
                                ? $data['capacity']['user_rows'] 
                                : (is_array($data['capacity']) ? $data['capacity'] : []);
                        @endphp
                        @forelse($capacityRows as $row)
                            <tr class="report-data-row">
                                <td><strong>{{ $row['name'] ?? '-' }}</strong></td>
                                <td>{{ $row['unvan'] ?? '-' }}</td>
                                <td>{{ $row['active_tasks_count'] ?? 0 }}</td>
                                <td>{{ $row['total_estimated_hours'] ?? 0 }}s</td>
                                <td>
                                    @php $rate = $row['allocation_rate'] ?? 0; @endphp
                                    <span style="font-weight: 700; color: {{ $rate > 100 ? 'var(--danger, #dc2626)' : 'var(--success, #16a34a)' }};">
                                        %{{ $rate }}
                                    </span>
                                </td>
                                <td>
                                    @if(($row['has_conflict'] ?? false) || ($row['allocation_rate'] ?? 0) > 100)
                                        <span class="badge badge-danger">⚠️ ÇAKIŞMA VAR</span>
                                    @else
                                        <span class="badge badge-success">✓ Dengeli</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyInitialRow">
                                <td colspan="6" style="text-align: center; padding: 36px; color: var(--text-muted);">
                                    Personel kapasite verisi bulunamadı.
                                </td>
                            </tr>
                        @endforelse
                        <tr id="reportNoResultsRow" style="display: none;">
                            <td colspan="6" style="text-align: center; padding: 36px; color: #64748b; background: #f8fafc;">
                                🔍 Filtre kriterlerinize uygun personel kapasite kaydı bulunamadı.
                            </td>
                        </tr>
                    </tbody>
                </table>

            <!-- 3. BUDGET TABLOSU -->
            @elseif($type === 'budget')
                <table class="app-table" id="reportTable">
                    <thead>
                        <tr>
                            <th>📁 Proje</th>
                            <th style="width: 180px;">🎯 Planlanan Bütçe</th>
                            <th style="width: 180px;">💸 Fiili Harcama</th>
                            <th style="width: 180px;">💰 Kalan Bütçe</th>
                            <th style="width: 130px;">📊 Harcama Oranı</th>
                        </tr>
                        <!-- Excel Stili Sütun İçi Arama & Çoklu Dropdown Seçim Satırı -->
                        <tr class="excel-filter-row">
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="0" placeholder="Proje kodu veya başlık..." oninput="onColFilterInput(0)">
                                    <button type="button" class="col-filter-btn" data-col="0" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(0, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_0" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="1" placeholder="Planlanan..." oninput="onColFilterInput(1)">
                                    <button type="button" class="col-filter-btn" data-col="1" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(1, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_1" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="2" placeholder="Fiili..." oninput="onColFilterInput(2)">
                                    <button type="button" class="col-filter-btn" data-col="2" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(2, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_2" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="3" placeholder="Kalan..." oninput="onColFilterInput(3)">
                                    <button type="button" class="col-filter-btn" data-col="3" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(3, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_3" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="4" placeholder="%..." oninput="onColFilterInput(4)">
                                    <button type="button" class="col-filter-btn" data-col="4" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(4, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_4" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="reportTableBody">
                        @forelse($data['projects'] as $p)
                            @php
                                $diff = $p->planned_budget - $p->actual_spent;
                            @endphp
                            <tr class="report-data-row">
                                <td>
                                    <!-- Sadece proje koduna link verildi -->
                                    <a href="{{ route('projects.show', $p->id) }}" class="project-code-link" title="Projeyi Görüntüle">
                                        <strong>{{ $p->code }}</strong> ↗
                                    </a>
                                    <span style="color: #94a3b8; margin: 0 4px;">-</span>
                                    <!-- Proje başlığındaki mükerrer link kaldırıldı -->
                                    <span class="project-title-text">{{ $p->title }}</span>
                                </td>
                                <td>₺{{ number_format($p->planned_budget, 2) }}</td>
                                <td>₺{{ number_format($p->actual_spent, 2) }}</td>
                                <td style="font-weight: 700; color: {{ $diff >= 0 ? 'var(--success, #16a34a)' : 'var(--danger, #dc2626)' }};">
                                    ₺{{ number_format($diff, 2) }}
                                </td>
                                <td>%{{ $p->planned_budget > 0 ? round(($p->actual_spent / $p->planned_budget) * 100) : 0 }}</td>
                            </tr>
                        @empty
                            <tr id="emptyInitialRow">
                                <td colspan="5" style="text-align: center; padding: 36px; color: var(--text-muted);">
                                    Bütçe kaydı bulunamadı.
                                </td>
                            </tr>
                        @endforelse
                        <tr id="reportNoResultsRow" style="display: none;">
                            <td colspan="5" style="text-align: center; padding: 36px; color: #64748b; background: #f8fafc;">
                                🔍 Filtre kriterlerinize uygun bütçe kaydı bulunamadı.
                            </td>
                        </tr>
                    </tbody>
                </table>

            <!-- 4. TIMESHEET TABLOSU -->
            @elseif($type === 'timesheet')
                <table class="app-table" id="reportTable">
                    <thead>
                        <tr>
                            <th style="width: 120px;">📅 Tarih</th>
                            <th style="width: 180px;">👤 Personel</th>
                            <th style="width: 140px;">📁 Proje</th>
                            <th>📝 Görev</th>
                            <th style="width: 100px;">⏱️ Süre</th>
                            <th>💬 Açıklama</th>
                        </tr>
                        <!-- Excel Stili Sütun İçi Arama & Çoklu Dropdown Seçim Satırı -->
                        <tr class="excel-filter-row">
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="0" placeholder="GG.AA.YYYY" oninput="onColFilterInput(0)">
                                    <button type="button" class="col-filter-btn" data-col="0" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(0, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_0" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="1" placeholder="Personele göre..." oninput="onColFilterInput(1)">
                                    <button type="button" class="col-filter-btn" data-col="1" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(1, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_1" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="2" placeholder="Projeye göre..." oninput="onColFilterInput(2)">
                                    <button type="button" class="col-filter-btn" data-col="2" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(2, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_2" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="3" placeholder="Göreve göre..." oninput="onColFilterInput(3)">
                                    <button type="button" class="col-filter-btn" data-col="3" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(3, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_3" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="4" placeholder="Saat..." oninput="onColFilterInput(4)">
                                    <button type="button" class="col-filter-btn" data-col="4" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(4, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_4" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="5" placeholder="Açıklamaya göre..." oninput="onColFilterInput(5)">
                                    <button type="button" class="col-filter-btn" data-col="5" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(5, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_5" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="reportTableBody">
                        @forelse($data['timesheets'] as $ts)
                            @php
                                $tsUserName = $ts->user ? $ts->user->getMaskedNameFor(auth()->user()) : '-';
                                $tsProjCode = $ts->project?->code ?? '-';
                            @endphp
                            <tr class="report-data-row">
                                <td>{{ $ts->work_date ? $ts->work_date->format('d.m.Y') : '-' }}</td>
                                <td><strong>{{ $tsUserName }}</strong></td>
                                <td>
                                    @if($ts->project)
                                        <a href="{{ route('projects.show', $ts->project->id) }}" class="project-code-link" title="{{ $ts->project->title }}">
                                            <strong>{{ $ts->project->code }}</strong> ↗
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $ts->task?->title ?? '-' }}</td>
                                <td><strong>{{ $ts->hours }}s</strong></td>
                                <td>{{ $ts->description }}</td>
                            </tr>
                        @empty
                            <tr id="emptyInitialRow">
                                <td colspan="6" style="text-align: center; padding: 36px; color: var(--text-muted);">
                                    Zaman kaydı bulunamadı.
                                </td>
                            </tr>
                        @endforelse
                        <tr id="reportNoResultsRow" style="display: none;">
                            <td colspan="6" style="text-align: center; padding: 36px; color: #64748b; background: #f8fafc;">
                                🔍 Filtre kriterlerinize uygun zaman takibi kaydı bulunamadı.
                            </td>
                        </tr>
                    </tbody>
                </table>

            <!-- 5. RISK TABLOSU -->
            @elseif($type === 'risk')
                <table class="app-table" id="reportTable">
                    <thead>
                        <tr>
                            <th>🛡️ Risk Başlığı</th>
                            <th style="width: 140px;">📁 Proje</th>
                            <th style="width: 180px;">🏷️ Kategori</th>
                            <th style="width: 80px;">🎯 Skor</th>
                            <th style="width: 130px;">🔥 Ciddiyet</th>
                            <th style="width: 170px;">👤 Sorumlu</th>
                            <th style="width: 140px;">📌 Durum</th>
                        </tr>
                        <!-- Excel Stili Sütun İçi Arama & Çoklu Dropdown Seçim Satırı -->
                        <tr class="excel-filter-row">
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="0" placeholder="Başlığa göre..." oninput="onColFilterInput(0)">
                                    <button type="button" class="col-filter-btn" data-col="0" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(0, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_0" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="1" placeholder="Projeye göre..." oninput="onColFilterInput(1)">
                                    <button type="button" class="col-filter-btn" data-col="1" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(1, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_1" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="2" placeholder="Kategoriye göre..." oninput="onColFilterInput(2)">
                                    <button type="button" class="col-filter-btn" data-col="2" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(2, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_2" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="3" placeholder="Skor..." oninput="onColFilterInput(3)">
                                    <button type="button" class="col-filter-btn" data-col="3" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(3, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_3" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="4" placeholder="Ciddiyete göre..." oninput="onColFilterInput(4)">
                                    <button type="button" class="col-filter-btn" data-col="4" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(4, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_4" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="5" placeholder="Sorumluya göre..." oninput="onColFilterInput(5)">
                                    <button type="button" class="col-filter-btn" data-col="5" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(5, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_5" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                            <th>
                                <div class="col-filter-wrap">
                                    <input type="text" class="excel-col-filter" data-col="6" placeholder="Duruma göre..." oninput="onColFilterInput(6)">
                                    <button type="button" class="col-filter-btn" data-col="6" title="Çoklu Seçim Filtresi" onclick="openColFilterPopover(6, this, event)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                        <span class="filter-count-badge" id="colBadge_6" style="display:none;">0</span>
                                    </button>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="reportTableBody">
                        @forelse($data['risks'] as $rk)
                            @php
                                $rProjCode = $rk->project?->code ?? '-';
                                $rOwner = $rk->owner ? $rk->owner->getMaskedNameFor(auth()->user()) : '-';
                            @endphp
                            <tr class="report-data-row">
                                <td><strong>{{ $rk->title }}</strong></td>
                                <td>
                                    @if($rk->project)
                                        <a href="{{ route('projects.show', $rk->project->id) }}" class="project-code-link" title="{{ $rk->project->title }}">
                                            <strong>{{ $rk->project->code }}</strong> ↗
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <!-- Kategori Türkçe Kurumsal Etiketi (Insan_kaynaklari slug sorunu çözüldü) -->
                                <td>
                                    <span class="badge" style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; font-weight: 600; font-size: 11.5px; padding: 3px 8px;">
                                        {{ $rk->category_label }}
                                    </span>
                                </td>
                                <td style="font-weight: 800; font-size: 14px;">{{ $rk->risk_score }}</td>
                                <td><x-priority-badge :priority="$rk->severity" /></td>
                                <td>{{ $rOwner }}</td>
                                <td><x-status-badge :status="$rk->status" /></td>
                            </tr>
                        @empty
                            <tr id="emptyInitialRow">
                                <td colspan="7" style="text-align: center; padding: 36px; color: var(--text-muted);">
                                    Risk kaydı bulunamadı.
                                </td>
                            </tr>
                        @endforelse
                        <tr id="reportNoResultsRow" style="display: none;">
                            <td colspan="7" style="text-align: center; padding: 36px; color: #64748b; background: #f8fafc;">
                                🔍 Filtre kriterlerinize uygun risk kaydı bulunamadı.
                            </td>
                        </tr>
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <!-- EXCEL STİLİ TEKİL POPOVER DROPDOWN MENU KAPSAYICISI (Body içerisine sabitlenir, taşma kesilmesini engeller) -->
    <div id="excelColumnFilterPopover" class="excel-dropdown-popover" style="display: none;">
        <div class="dropdown-header-bar">
            <span class="dropdown-header-title">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                </svg>
                <span id="popoverColTitle">Sütun Filtresi</span>
            </span>
            <button type="button" class="dropdown-close-btn" onclick="closeColFilterPopover()">&times;</button>
        </div>
        <div class="dropdown-search-box">
            <input type="text" id="popoverSearchInput" class="dropdown-search-input" placeholder="Listede ara..." oninput="filterPopoverOptionsList(this.value)">
        </div>
        <div class="dropdown-quick-actions">
            <button type="button" class="dropdown-action-link" onclick="toggleSelectAllCurrentCol(true)">✓ Tümünü Seç</button>
            <span style="color: #cbd5e1;">|</span>
            <button type="button" class="dropdown-action-link" onclick="toggleSelectAllCurrentCol(false)">✕ Seçimi Kaldır</button>
        </div>
        <div class="dropdown-items-list" id="popoverOptionsList">
            <!-- JavaScript tarafından tablodaki benzersiz verilerle dinamik doldurulur -->
        </div>
        <div class="dropdown-footer-bar">
            <button type="button" class="btn btn-secondary btn-xs" onclick="resetCurrentColFilter()" style="font-size: 11px; padding: 3px 8px;">Sıfırla</button>
            <button type="button" class="btn btn-primary btn-xs" onclick="closeColFilterPopover()" style="font-size: 11px; padding: 3px 12px;">Tamam</button>
        </div>
    </div>

<script>
// Türkçe Karakter Normalizasyonu (Büyük/Küçük İ/I vs. hassas eşleşme)
function normalizeTurkish(str) {
    if (!str) return '';
    return str.toString()
        .replace(/İ/g, 'i')
        .replace(/I/g, 'ı')
        .replace(/Ğ/g, 'ğ')
        .replace(/Ü/g, 'ü')
        .replace(/Ş/g, 'ş')
        .replace(/Ö/g, 'ö')
        .replace(/Ç/g, 'ç')
        .toLowerCase()
        .trim();
}

function escapeHtml(text) {
    if (!text) return '';
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Hücre metnini temizle (ikonları ve gereksiz boşlukları ayıkla)
function getCleanCellText(cell) {
    if (!cell) return '';
    let text = cell.innerText || cell.textContent || '';
    text = text.replace(/↗/g, '').replace(/↘/g, '').trim();
    text = text.replace(/\s+/g, ' ');
    return text;
}

// Filtre Durumu Yönetimi
// columnFilters[colIdx] = { text: '', selectedValues: Set() }
let columnFilters = {};
let columnUniqueValues = {};
let totalColumnsCount = 0;
let activePopoverColIdx = null;

// Tablo Başlığındaki Sütun İsmini Al
function getColHeaderTitle(colIdx) {
    const table = document.getElementById('reportTable');
    if (!table) return 'Sütun';
    const th = table.querySelectorAll('thead tr:first-child th')[colIdx];
    if (!th) return 'Sütun ' + (colIdx + 1);
    let title = th.innerText || th.textContent || '';
    return title.trim();
}

// Sayfa Yüklendiğinde Tablo Verilerini Analiz Et ve Filtreleri Başlat
function initTableExcelFilters() {
    const table = document.getElementById('reportTable');
    if (!table) return;

    const rows = table.querySelectorAll('#reportTableBody tr.report-data-row');
    const headerCols = table.querySelectorAll('thead tr:first-child th');
    totalColumnsCount = headerCols.length;

    columnUniqueValues = {};
    for (let c = 0; c < totalColumnsCount; c++) {
        columnUniqueValues[c] = [];
        const valueMap = new Map();

        rows.forEach(row => {
            const cell = row.children[c];
            if (cell) {
                const val = getCleanCellText(cell);
                if (val && val !== '-') {
                    valueMap.set(val, (valueMap.get(val) || 0) + 1);
                }
            }
        });

        // Değerleri Türkçe harf sıralamasına göre alfabetik diz
        const sortedItems = Array.from(valueMap.entries()).map(([val, count]) => ({ val, count }));
        sortedItems.sort((a, b) => a.val.localeCompare(b.val, 'tr'));
        columnUniqueValues[c] = sortedItems;
    }
}

// Sütun İçi Çoklu Seçim Popover'ını Aç
function openColFilterPopover(colIdx, btnEl, event) {
    if (event) event.stopPropagation();

    const popover = document.getElementById('excelColumnFilterPopover');
    if (!popover) return;

    // Aynı buton tıklandıysa kapat/aç toggle yap
    if (activePopoverColIdx === colIdx && popover.style.display !== 'none') {
        closeColFilterPopover();
        return;
    }

    activePopoverColIdx = colIdx;

    // Başlığı güncelle
    const colTitle = getColHeaderTitle(colIdx);
    document.getElementById('popoverColTitle').innerText = colTitle;
    document.getElementById('popoverSearchInput').value = '';

    // Seçenekleri render et
    renderPopoverOptions(colIdx);

    // Popover'ı butonun hemen altına hizala
    popover.style.display = 'flex';
    const rect = btnEl.getBoundingClientRect();
    const popoverWidth = 270;

    let left = rect.left;
    if (left + popoverWidth > window.innerWidth - 12) {
        left = window.innerWidth - popoverWidth - 12;
    }
    if (left < 10) left = 10;

    const top = rect.bottom + 5;
    popover.style.top = top + 'px';
    popover.style.left = left + 'px';

    // Arama kutusuna odaklan
    setTimeout(() => {
        document.getElementById('popoverSearchInput')?.focus();
    }, 50);
}

// Popover'ı Kapat
function closeColFilterPopover() {
    const popover = document.getElementById('excelColumnFilterPopover');
    if (popover) popover.style.display = 'none';
    activePopoverColIdx = null;
}

// Popover İçerisindeki Checkbox Listesini Render Et
function renderPopoverOptions(colIdx, filterQuery = '') {
    const listContainer = document.getElementById('popoverOptionsList');
    if (!listContainer) return;

    const items = columnUniqueValues[colIdx] || [];
    const currentSelected = columnFilters[colIdx]?.selectedValues || new Set();
    const normQ = normalizeTurkish(filterQuery);

    let html = '';
    let visibleItemCount = 0;

    items.forEach((item, idx) => {
        if (normQ && !normalizeTurkish(item.val).includes(normQ)) {
            return;
        }

        visibleItemCount++;
        const isChecked = currentSelected.has(item.val);
        html += `
            <label class="dropdown-item-row">
                <input type="checkbox" value="${escapeHtml(item.val)}" ${isChecked ? 'checked' : ''} onchange="onOptionCheckboxChange(${colIdx}, this)">
                <span class="dropdown-item-label" title="${escapeHtml(item.val)}">${escapeHtml(item.val)}</span>
                <span class="dropdown-item-count">(${item.count})</span>
            </label>
        `;
    });

    if (visibleItemCount === 0) {
        html = `<div style="text-align: center; color: #94a3b8; padding: 18px 10px; font-size: 11.5px;">Eşleşen değer bulunamadı.</div>`;
    }

    listContainer.innerHTML = html;
}

// Popover İçinde Hızlı Arama
function filterPopoverOptionsList(query) {
    if (activePopoverColIdx === null) return;
    renderPopoverOptions(activePopoverColIdx, query);
}

// Checkbox Değiştiğinde Anlık Filtrele
function onOptionCheckboxChange(colIdx, checkboxEl) {
    if (!columnFilters[colIdx]) {
        columnFilters[colIdx] = { text: '', selectedValues: new Set() };
    }

    if (checkboxEl.checked) {
        columnFilters[colIdx].selectedValues.add(checkboxEl.value);
    } else {
        columnFilters[colIdx].selectedValues.delete(checkboxEl.value);
    }

    applyAllFilters();
}

// Mevcut Sütundaki Tümünü Seç / Seçimi Kaldır
function toggleSelectAllCurrentCol(select) {
    if (activePopoverColIdx === null) return;
    const colIdx = activePopoverColIdx;
    const items = columnUniqueValues[colIdx] || [];

    if (!columnFilters[colIdx]) {
        columnFilters[colIdx] = { text: '', selectedValues: new Set() };
    }

    if (select) {
        items.forEach(it => columnFilters[colIdx].selectedValues.add(it.val));
    } else {
        columnFilters[colIdx].selectedValues.clear();
    }

    renderPopoverOptions(colIdx, document.getElementById('popoverSearchInput')?.value || '');
    applyAllFilters();
}

// Mevcut Sütunun Filtresini Sıfırla
function resetCurrentColFilter() {
    if (activePopoverColIdx === null) return;
    const colIdx = activePopoverColIdx;

    if (columnFilters[colIdx]) {
        columnFilters[colIdx].selectedValues.clear();
    }

    renderPopoverOptions(colIdx);
    applyAllFilters();
}

// Sütun Text Arama Girişi
function onColFilterInput(colIdx) {
    const input = document.querySelector(`.excel-col-filter[data-col="${colIdx}"]`);
    const val = input ? input.value : '';

    if (!columnFilters[colIdx]) {
        columnFilters[colIdx] = { text: '', selectedValues: new Set() };
    }
    columnFilters[colIdx].text = val;

    if (val.trim()) {
        input.classList.add('has-value');
    } else {
        input.classList.remove('has-value');
    }

    applyAllFilters();
}

// Belirli bir sütunun seçili çoklu değerlerini temizle (Çip üzerinden tıklandığında)
function clearColSelectedValues(colIdx) {
    if (columnFilters[colIdx]) {
        columnFilters[colIdx].selectedValues.clear();
    }
    if (activePopoverColIdx === colIdx) {
        renderPopoverOptions(colIdx);
    }
    applyAllFilters();
}

// Belirli bir sütunun arama metnini temizle (Çip üzerinden tıklandığında)
function clearColTextInput(colIdx) {
    if (columnFilters[colIdx]) {
        columnFilters[colIdx].text = '';
    }
    const input = document.querySelector(`.excel-col-filter[data-col="${colIdx}"]`);
    if (input) {
        input.value = '';
        input.classList.remove('has-value');
    }
    applyAllFilters();
}

// Tüm Filtreleri Sıfırla
function resetAllFilters() {
    columnFilters = {};
    document.querySelectorAll('.excel-col-filter').forEach(input => {
        input.value = '';
        input.classList.remove('has-value');
    });

    closeColFilterPopover();
    applyAllFilters();
}

// GENEL FİLTRELEME MOTORU (Tüm sütun koşullarını AND / sütun içi çoklu seçimleri OR olarak uygular)
function applyAllFilters() {
    const rows = document.querySelectorAll('#reportTableBody tr.report-data-row');
    let visibleCount = 0;

    rows.forEach(row => {
        let isMatch = true;
        const cells = row.children;

        for (let c = 0; c < totalColumnsCount; c++) {
            const cf = columnFilters[c];
            if (!cf) continue;

            const cell = cells[c];
            if (!cell) continue;

            const rawCellText = getCleanCellText(cell);
            const normCellText = normalizeTurkish(rawCellText);

            // 1. Text input araması kontrolü
            if (cf.text && cf.text.trim()) {
                const normQ = normalizeTurkish(cf.text);
                if (!normCellText.includes(normQ)) {
                    isMatch = false;
                    break;
                }
            }

            // 2. Çoklu Seçim (Dropdown Checkbox) kontrolü (OR mantığı)
            if (cf.selectedValues && cf.selectedValues.size > 0) {
                let matchAnyOption = false;
                for (let selectedVal of cf.selectedValues) {
                    const normSelectedVal = normalizeTurkish(selectedVal);
                    if (rawCellText === selectedVal || normCellText === normSelectedVal || normCellText.includes(normSelectedVal)) {
                        matchAnyOption = true;
                        break;
                    }
                }
                if (!matchAnyOption) {
                    isMatch = false;
                    break;
                }
            }
        }

        if (isMatch) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Gösterilen kayıt sayacını güncelle
    const countEl = document.getElementById('reportRowCount');
    if (countEl) countEl.innerText = visibleCount;

    // Sonuç bulunamadı satırını göster/gizle
    const noResultsRow = document.getElementById('reportNoResultsRow');
    if (noResultsRow) {
        noResultsRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
    }

    // Butonlardaki sayaç rozetlerini ve aktiflik sınıflarını güncelle
    updateColumnFilterBadges();

    // Üstteki Aktif Filtreler çubuğundaki etiketleri (çipleri) render et
    renderActiveFilterChips();
}

// Sütun İçi Buton Rozetlerini Güncelle
function updateColumnFilterBadges() {
    for (let c = 0; c < totalColumnsCount; c++) {
        const btn = document.querySelector(`.col-filter-btn[data-col="${c}"]`);
        const badge = document.getElementById(`colBadge_${c}`);
        const cf = columnFilters[c];

        const selectedCount = cf && cf.selectedValues ? cf.selectedValues.size : 0;
        const hasText = cf && cf.text && cf.text.trim();

        if (btn) {
            if (selectedCount > 0 || hasText) {
                btn.classList.add('is-active');
            } else {
                btn.classList.remove('is-active');
            }
        }

        if (badge) {
            if (selectedCount > 0) {
                badge.innerText = selectedCount;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }
    }
}

// Üstteki Aktif Filtre Çiplerini Render Et
function renderActiveFilterChips() {
    const listContainer = document.getElementById('activeFilterChipsList');
    const resetAllBtn = document.getElementById('resetAllFiltersBtn');
    if (!listContainer) return;

    let chipsHtml = '';
    let totalActiveFilters = 0;

    for (let c = 0; c < totalColumnsCount; c++) {
        const cf = columnFilters[c];
        if (!cf) continue;

        const colTitle = getColHeaderTitle(c);

        // Seçili çoklu değerler
        if (cf.selectedValues && cf.selectedValues.size > 0) {
            totalActiveFilters++;
            const vals = Array.from(cf.selectedValues);
            let labelText = '';
            if (vals.length <= 2) {
                labelText = vals.join(', ');
            } else {
                labelText = `${vals[0]}, ${vals[1]} (+${vals.length - 2} seçim)`;
            }

            chipsHtml += `
                <span class="active-filter-chip">
                    <strong>${escapeHtml(colTitle)}:</strong> ${escapeHtml(labelText)}
                    <button type="button" class="chip-remove-btn" onclick="clearColSelectedValues(${c})" title="Bu filtreyi kaldır">&times;</button>
                </span>
            `;
        }

        // Metin araması
        if (cf.text && cf.text.trim()) {
            totalActiveFilters++;
            chipsHtml += `
                <span class="active-filter-chip">
                    <strong>${escapeHtml(colTitle)}:</strong> "${escapeHtml(cf.text.trim())}"
                    <button type="button" class="chip-remove-btn" onclick="clearColTextInput(${c})" title="Bu aramayı kaldır">&times;</button>
                </span>
            `;
        }
    }

    if (totalActiveFilters === 0) {
        listContainer.innerHTML = `<span class="no-filter-text">ℹ️ Aktif filtre yok (Tüm kayıtlar listeleniyor)</span>`;
        if (resetAllBtn) resetAllBtn.style.display = 'none';
    } else {
        listContainer.innerHTML = chipsHtml;
        if (resetAllBtn) resetAllBtn.style.display = 'inline-flex';
    }
}

// Dışarı tıklandığında popover'ı kapat
document.addEventListener('click', function(e) {
    const popover = document.getElementById('excelColumnFilterPopover');
    if (popover && popover.style.display !== 'none') {
        if (!popover.contains(e.target) && !e.target.closest('.col-filter-btn')) {
            closeColFilterPopover();
        }
    }
});

// ESC tuşuna basıldığında popover'ı kapat
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeColFilterPopover();
    }
});

// Sayfa hazır olduğunda başlat
document.addEventListener('DOMContentLoaded', function() {
    initTableExcelFilters();
});
</script>
@endsection