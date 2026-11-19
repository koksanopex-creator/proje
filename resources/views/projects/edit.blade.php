@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/quill/quill.snow.css') }}">
<style>
.ql-toolbar.ql-snow {
    border-color: #cbd5e1 !important;
    background: #f8fafc !important;
    border-radius: 8px 8px 0 0 !important;
}
.ql-container.ql-snow {
    border-color: #cbd5e1 !important;
    border-radius: 0 0 8px 8px !important;
    background: #ffffff !important;
    min-height: 200px !important;
    font-family: 'Inter', sans-serif !important;
    font-size: 14px !important;
}
.scope-file-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 9px 12px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    transition: all 0.15s;
}
.scope-file-card:hover {
    border-color: #94a3b8;
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
}
</style>
@endpush
@extends('layouts.app')

@section('title', 'Projeyi Düzenle: ' . $project->title)
@section('page_title', 'Projeyi Düzenle: ' . $project->title)
@section('page_subtitle', '[' . $project->code . '] Projesinin temel verilerini, liderlik/sponsor atamalarını, ekip ve gözlemcilerini, bütçe kırılımlarını ve gizlilik ayarlarını güncelleyin.')

@section('content')
@php
    $categories = $categories ?? \App\Models\ProjectCategory::where('is_active', true)->orderBy('name')->get();
@endphp

<div style="max-width: 1040px; margin: 0 auto; padding-bottom: 50px;">
    <div class="card" style="border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 4px 14px rgba(15,23,42,0.06); overflow: hidden; background: #ffffff;">
        <div class="card-header" style="padding: 18px 24px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 class="card-title" style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">
                    📋 Proje Düzenleme Formu: {{ $project->title }}
                </h3>
                <span class="text-xs text-muted">Projenin temel verilerini, yetkilerini, bütçesini ve faz planını güncelleyiniz.</span>
            </div>
            <a href="{{ route('projects.index') }}" class="btn btn-secondary btn-sm">
                Geri Dön
            </a>
        </div>

        <div class="card-body" style="padding: 24px;">
            <form action="{{ route('projects.update', $project->id) }}" method="POST" id="editProjectForm" enctype="multipart/form-data">
                @method('PUT')
                @csrf
                
                                <!-- HAZIR ŞABLONDAN KLONLAMA (Opsiyonel & Anlık Uygulanabilir) -->
                <div class="project-form-section-card mb-4" style="background: #f0fdf4; border-color: #bbf7d0;">
                    <div class="project-section-title" style="border-bottom-color: #dcfce7; margin-bottom: 12px;">
                        <span class="title-text" style="color: #166534;">
                            <span>📑</span> Hazır Proje Şablonundan Klonla (Önerilen)
                        </span>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span id="templateStatusBadge" class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-size: 11px;">
                                ⚡ Hızlı Kurulum
                            </span>
                        </div>
                    </div>

                    <div class="template-selector-toolbar mb-2">
                        <select name="template_id" id="templateSelect" class="template-select-input" onchange="handleTemplateSelectChange(this.value)">
                            <option value="">-- Sıfırdan Boş Proje Başlat --</option>
                            @foreach($templates as $tpl)
                                <option value="{{ $tpl->id }}" data-code="{{ $tpl->code }}">{{ $tpl->title }} ({{ $tpl->phases->count() }} Faz, {{ $tpl->risks->count() }} Risk Hazır Tanımlı)</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn-template-apply" id="applyTemplateBtn" onclick="applyCurrentTemplate()" title="Seçilen şablonun parametrelerini forma uygular ve önizlemeyi açar">
                            <span>⚡</span> Şablonu Getir & Forma Uygula
                        </button>
                        <button type="button" class="btn-template-toggle-preview" id="togglePreviewBtn" onclick="toggleTemplatePreview()" style="display: none;" title="Şablonun getireceği fazları, görevleri ve riskleri açıp kapatır">
                            <span>👁️</span> Faz & Görevleri Göster (<span id="previewPhasesCount">0</span>)
                        </button>
                        <button type="button" class="btn-template-reset" id="resetTemplateBtn" onclick="resetTemplateSelection(true)" style="display: none;" title="Şablonu temizler ve boş proje moduna döner">
                            <span>🔄</span> Şablonu Kaldır
                        </button>
                    </div>

                    <div id="templateInfoBox" class="pastel-blue-helper-box subtle" style="background: #ffffff; border-color: #bbf7d0; color: #166534; margin-bottom: 0;">
                        <span class="helper-box-icon">💡</span>
                        <div class="helper-box-content" id="templateInfoText">
                            Şablon seçip <strong>"⚡ Şablonu Getir & Forma Uygula"</strong> butonuna basarak veya şablonu doğrudan seçerek fazları, görevleri, İSG kapılarını ve bütçeyi anlık olarak sayfaya yükleyebilirsiniz.
                        </div>
                    </div>

                    <!-- CANLI ŞABLON DETAY & FAZ / GÖREV ÖNİZLEME KARTI -->
                    <div id="templateLivePreviewCard" class="template-live-preview-box" style="display: none;">
                        <div class="template-auto-fill-notice">
                            <span>✓</span>
                            <div>
                                <strong>Şablon Başarıyla Yüklendi:</strong> Başlık, kategori, öncelik, bütçe ve açıklama alanları şablona göre otomatik dolduruldu. İhtiyacınıza göre aşağıdan düzenleyebilirsiniz.
                            </div>
                        </div>

                        <div class="template-live-preview-header">
                            <div>
                                <div class="template-live-preview-title">
                                    <span id="tplPreviewTitle">--</span>
                                </div>
                                <div class="text-xs text-muted mt-1" id="tplPreviewDesc">--</div>
                            </div>
                            <span class="badge" id="tplPreviewCodeBadge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-weight: 600; font-size: 11px;">--</span>
                        </div>

                        <!-- KPI Rozetleri -->
                        <div class="template-live-preview-kpis" id="tplPreviewKpis">
                            <!-- JS ile dinamik oluşturulur -->
                        </div>

                        <!-- Faz & Görev Akışı -->
                        <div style="font-size: 12.5px; font-weight: 700; color: #334155; margin-top: 14px; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                            <span>📋</span> Projeye Otomatik Eklenecek Fazlar & Tanımlı Görevler
                        </div>
                        <div class="template-phases-timeline" id="tplPreviewPhasesTimeline">
                            <!-- JS ile dinamik oluşturulur -->
                        </div>

                        <!-- Varsa Riskler -->
                        <div id="tplPreviewRisksContainer" style="display: none; margin-top: 14px;">
                            <div style="font-size: 12.5px; font-weight: 700; color: #991b1b; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                                <span>⚠️</span> Öngörülen Riskler
                            </div>
                            <div id="tplPreviewRisksList" style="display: flex; flex-direction: column; gap: 6px;">
                                <!-- JS ile dinamik oluşturulur -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BÖLÜM 1: TEMEL BİLGİLER & KATEGORİ -->
                <div class="project-form-section-card">
                    <div class="project-section-title">
                        <span class="title-text" style="display: flex; align-items: center; gap: 10px;">
                        <span style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #0284c7 0%, #1d4ed8 100%); color: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 850; box-shadow: 0 3px 8px rgba(2,132,199,0.3); flex-shrink: 0;">1</span>
                        <span style="font-size: 15px; font-weight: 800; color: #0f172a;">Temel Proje Bilgileri & Kategori
                        </span>
                    </span>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            @if(auth()->user()->hasPermission('proje.manage_categories') || auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="openCategoryModal()" style="font-size: 11.5px; font-weight: 700; padding: 4px 12px; border-radius: 6px; box-shadow: 0 1px 2px rgba(2,132,199,0.15);">
                                    🏷️ Kategori Yönetimi & Ekle
                                </button>
                            @endif
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="syncBolumBtn" onclick="syncCentralDepartments()" style="font-size: 11.5px; font-weight: 600; padding: 4px 10px; border-radius: 6px;">
                                🔄 Bölümleri Senkronize Et
                            </button>
                        </div>
                    </div>

                                        <!-- PROJE ETİKETLERİ (TAGS) - EN BAŞTA -->
                    <div class="form-group mb-3" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 14px 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 6px;">
                            <label class="form-label mb-0" style="font-size: 13px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                                <span>🏷️</span> Proje Etiketleri (Hashtags / Tags)
                                <span class="badge" id="projectTagsCountBadge" style="background: #e0f2fe; color: #0284c7; font-size: 11px; font-weight: 700; border: 1px solid #bae6fd;">
                                    0 Etiket
                                </span>
                            </label>
                            <span style="font-size: 11px; color: #64748b;">
                                Arama, filtreleme ve raporlamalarda hızlı erişim için etiket ekleyin (Enter veya virgül ile).
                            </span>
                        </div>

                        <!-- Etiket Giriş ve Rozet Kutusu -->
                        <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 7px 10px; display: flex; flex-wrap: wrap; align-items: center; gap: 6px; min-height: 42px; cursor: text;" id="projectTagsInputBox" onclick="document.getElementById('projectTagTextInput').focus()">
                            <div id="projectActiveTagsList" style="display: inline-flex; flex-wrap: wrap; gap: 6px;"></div>
                            <input type="text" id="projectTagTextInput" placeholder="+ Etiket yazıp Enter veya virgüle basın..." style="border: none; outline: none; font-size: 12.5px; color: #1e293b; min-width: 220px; flex: 1; padding: 2px 4px;" onkeydown="handleProjectTagInputKey(event)">
                        </div>

                        <!-- Hızlı Öneri Çipleri -->
                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 8px; flex-wrap: wrap;">
                            <span style="font-size: 11px; font-weight: 700; color: #64748b;">💡 Hızlı Seçim:</span>
                            @php
                                $suggestedProjectTags = ['Yatırım', 'Ar-Ge', 'GES', 'Otomasyon', 'Maliyet Azaltma', 'Kapasite Artırımı', 'İSG', 'Sürdürülebilirlik', 'Kalite'];
                            @endphp
                            @foreach($suggestedProjectTags as $sTag)
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="addProjectTag('{{ $sTag }}')" style="font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 6px; background: #ffffff;">
                                    + #{{ $sTag }}
                                </button>
                            @endforeach
                        </div>

                        <input type="hidden" name="tags_json" id="projectTagsJsonInput" value="{{ json_encode(old('tags', isset($project) ? ($project->tags ?? []) : [])) }}">
                    </div>

                    <!-- Satır 1: Başlık & Çoklu Kategori (Eşit Baseline) -->
                    <div class="grid-2 mb-3" style="align-items: start;">
                        <div class="form-group mb-0">
                            <label class="form-label-aligned">
                                <span>Proje Başlığı <span class="text-danger">*</span></span>
                            </label>
                            <input type="text" name="title" class="form-control" required placeholder="Örn: Preform Bölümü HCV Projesi" value="{{ old('title', $project->title) }}" style="height: 42px;">
                        </div>

                        <div class="form-group mb-0">
                            <label class="form-label-aligned">
                                <span>Proje Kategorileri (Çoklu Seçilebilir) <span class="text-danger">*</span></span>
                                <span id="selectedCategoriesCount" class="text-xs text-muted" style="font-weight: 500;">0 kategori seçildi</span>
                            </label>
                            <!-- ARAMALI MULTI-SELECT KATEGORİ -->
                            <div class="searchable-multiselect-container" id="categoriesMultiSelect">
                                <div class="sms-input-wrap" id="categoriesInputWrap" onclick="focusCategoriesSearch()">
                                    <div id="categoriesTagContainer" style="display: inline-flex; flex-wrap: wrap; gap: 6px;"></div>
                                    <input type="text" id="categoriesSearchInput" class="sms-search-input" placeholder="🔍 Kategori ara veya seç..." oninput="handleCategoriesSearch(this.value)" onfocus="openCategoriesDropdown()">
                                </div>

                                <div class="sms-dropdown-menu" id="categoriesDropdownMenu">
                                    @foreach($categories as $cat)
                                        <div class="sms-dropdown-item category-item" data-id="{{ $cat->slug }}" data-text="{{ $cat->name }}" onclick="toggleCategoryItem('{{ $cat->slug }}', '{{ addslashes($cat->name) }}', '{{ $cat->color ?? '#0284c7' }}')">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background-color: {{ $cat->color ?? '#0284c7' }};"></span>
                                                <strong style="color: #0f172a;">{{ $cat->name }}</strong>
                                            </div>
                                            <span class="sms-check-indicator" style="display: none; color: #0284c7; font-weight: 700;">✓</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <!-- Gizli select (Form submit) -->
                            <select name="categories[]" id="hiddenCategoriesSelect" multiple style="display: none;"></select>
                        </div>
                    </div>

                    <!-- Satır 2: Öncelik, Saha/Lokasyon, Çoklu Bölüm (Kusursuz Yatay Hiza) -->
                    <div class="grid-3 mb-2" style="align-items: start;">
                        <div class="form-group mb-0">
                            <label class="form-label-aligned" style="height: 22px; margin-bottom: 6px; display: flex; align-items: center;">
                                <span>Öncelik Seviyesi <span class="text-danger">*</span></span>
                            </label>
                            <select name="priority" class="form-control" required style="height: 42px; box-sizing: border-box;">
                                <option value="low">Düşük</option>
                                <option value="medium" selected>Orta</option>
                                <option value="high">Yüksek</option>
                                <option value="urgent">Acil / Kritik</option>
                            </select>
                        </div>

                        <div class="form-group mb-0">
                            <label class="form-label-aligned" style="height: 22px; margin-bottom: 6px; display: flex; align-items: center;">
                                <span>Saha / Lokasyon</span>
                            </label>
                            <input type="text" name="location" class="form-control" placeholder="Gaziantep Merkez, 2. OSB vb." value="{{ old('location', $project->location) }}" style="height: 42px; box-sizing: border-box;">
                        </div>

                        <div class="form-group mb-0">
                            <label class="form-label-aligned" style="height: 22px; margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between;">
                                <span style="white-space: nowrap; font-size: 13px;">İlgili Bölüm(ler)</span>
                                <span id="selectedBolumlerCount" class="text-xs text-muted" style="font-weight: 500;">0 bölüm seçildi</span>
                            </label>
                            <!-- ARAMALI MULTI-SELECT BÖLÜM -->
                            <div class="searchable-multiselect-container" id="bolumlerMultiSelect">
                                <div class="sms-input-wrap" id="bolumlerInputWrap" onclick="focusBolumlerSearch()" style="min-height: 42px; box-sizing: border-box; display: flex; flex-wrap: wrap; align-items: center; gap: 6px; padding: 4px 8px; cursor: pointer; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px;">
                                    <div id="bolumlerTagContainer" style="display: inline-flex; flex-wrap: wrap; gap: 6px;"></div>
                                    <input type="text" id="bolumlerSearchInput" class="sms-search-input" placeholder="🔍 Bölüm ara veya seç..." oninput="handleBolumlerSearch(this.value)" onfocus="openBolumlerDropdown()" style="border: none; outline: none; font-size: 12px; flex: 1; min-width: 130px; height: 30px; background: transparent;">
                                </div>

                                <div class="sms-dropdown-menu" id="bolumlerDropdownMenu">
                                    @foreach($bolumler as $b)
                                        <div class="sms-dropdown-item bolum-item" data-id="{{ $b->id }}" data-text="{{ $b->ad ?? $b->name }} {{ $b->kod ?? '' }}" onclick="toggleBolumItem({{ $b->id }}, '{{ addslashes($b->ad ?? $b->name) }}', '{{ $b->kod ?? '' }}')">
                                            <div>
                                                <strong style="color: #0f172a;">{{ $b->ad ?? $b->name }}</strong>
                                                @if($b->kod)
                                                    <span style="font-size: 11px; color: #64748b; margin-left: 6px; background: #f1f5f9; padding: 1px 6px; border-radius: 4px;">{{ $b->kod }}</span>
                                                @endif
                                            </div>
                                            <span class="sms-check-indicator" style="display: none; color: #0284c7; font-weight: 700;">✓</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <!-- Gizli select (Form submit) -->
                            <select name="bolum_ids[]" id="hiddenBolumlerSelect" multiple style="display: none;"></select>
                        </div>
                    </div>

                                        <div class="pastel-blue-helper-box mb-0 mt-3">
                        <span class="helper-box-icon">ℹ️</span>
                        <div class="helper-box-content" id="syncStatusMsg">
                            Merkezden senkronize edilmiş <strong>{{ count($bolumler) }}</strong> aktif bölüm listeleniyor. Seçilen tüm bölümlerin lider ve direktörlerine süreç bildirimleri otomatik iletilir.
                        </div>
                    </div>
                </div>

                <!-- BÖLÜM 2: LİDERLİK & SPONSORLUK -->
                <div class="project-form-section-card">
                    <div class="project-section-title">
                        <span class="title-text" style="display: flex; align-items: center; gap: 10px;">
                        <span style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #0284c7 0%, #1d4ed8 100%); color: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 850; box-shadow: 0 3px 8px rgba(2,132,199,0.3); flex-shrink: 0;">2</span>
                        <span style="font-size: 15px; font-weight: 800; color: #0f172a;">Proje Liderliği & Sponsorluk (Aramalı Seçim)
                        </span>
                    </span>
                        <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 11px;">RACI Matrisi</span>
                    </div>

                    <div class="grid-2 mb-2" style="align-items: start;">
                        <!-- Proje Yöneticisi (Görsel 3 Tarzı Modern Arama & Seçim) -->
                        <div class="form-group mb-0">
                            <label class="form-label-aligned">
                                <span>Proje Yöneticisi / Lideri</span>
                            </label>
                            <div class="searchable-multiselect-container" id="managerSearchContainer" style="position: relative;">
                                <div class="sms-input-wrap" id="managerInputWrap" onclick="openManagerDropdown()" style="min-height: 42px; display: flex; align-items: center; justify-content: space-between; padding: 4px 10px; cursor: pointer; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px;">
                                    <div id="managerSelectedBox" style="display: flex; align-items: center; flex: 1; min-width: 0;">
                                        <input type="text" id="managerSearchInput" class="sms-search-input" placeholder="🔍 Personel ara (isim, unvan)..." oninput="filterManagerDropdown(this.value)" onfocus="openManagerDropdown()" style="width: 100%; border: none; outline: none; font-size: 13px; background: transparent;">
                                    </div>
                                    <div id="managerClearBtn" style="display: none; cursor: pointer; color: #94a3b8; font-weight: 700; margin-left: 6px; padding: 2px 6px;" onclick="clearManagerSelection(event)" title="Temizle">✕</div>
                                </div>
                                <div class="sms-dropdown-menu" id="managerDropdownMenu" style="display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0; max-height: 240px; overflow-y: auto; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); z-index: 1050;">
                                    @foreach($users as $u)
                                        <div class="sms-dropdown-item manager-item" data-id="{{ $u->id }}" data-text="{{ strtolower($u->name . ' ' . ($u->unvan ?? '') . ' ' . ($u->bolum?->ad ?? '')) }}" onclick="selectManager({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->unvan ?? $u->roles->first()?->name ?? 'Uzman') }}')" style="padding: 9px 12px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9;">
                                            <div>
                                                <strong style="color: #0f172a; font-size: 13px;">{{ $u->name }}</strong>
                                                <span style="font-size: 11.5px; color: #64748b; margin-left: 6px;">{{ $u->bolum?->ad ?? $u->bolum?->name ?? 'Merkez' }} • {{ $u->unvan ?? $u->roles->first()?->name ?? 'Uzman' }}</span>
                                            </div>
                                            <span class="manager-check-icon" id="mgrCheck_{{ $u->id }}" style="display: none; color: #0284c7; font-weight: 700;">✓</span>
                                        </div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="manager_id" id="hiddenManagerId" value="{{ old('manager_id', '') }}">
                            </div>
                        </div>

                        <!-- Proje Sponsoru (Görsel 3 Tarzı Modern Arama & Seçim) -->
                        <div class="form-group mb-0">
                            <label class="form-label-aligned">
                                <span>Proje Sponsoru (Direktör / Üst Yönetim)</span>
                            </label>
                            <div class="searchable-multiselect-container" id="sponsorSearchContainer" style="position: relative;">
                                <div class="sms-input-wrap" id="sponsorInputWrap" onclick="openSponsorDropdown()" style="min-height: 42px; display: flex; align-items: center; justify-content: space-between; padding: 4px 10px; cursor: pointer; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px;">
                                    <div id="sponsorSelectedBox" style="display: flex; align-items: center; flex: 1; min-width: 0;">
                                        <input type="text" id="sponsorSearchInput" class="sms-search-input" placeholder="🔍 Sponsor ara (isim, unvan, direktör)..." oninput="filterSponsorDropdown(this.value)" onfocus="openSponsorDropdown()" style="width: 100%; border: none; outline: none; font-size: 13px; background: transparent;">
                                    </div>
                                    <div id="sponsorClearBtn" style="display: none; cursor: pointer; color: #94a3b8; font-weight: 700; margin-left: 6px; padding: 2px 6px;" onclick="clearSponsorSelection(event)" title="Temizle">✕</div>
                                </div>
                                <div class="sms-dropdown-menu" id="sponsorDropdownMenu" style="display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0; max-height: 240px; overflow-y: auto; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); z-index: 1050;">
                                    @foreach($users as $u)
                                        <div class="sms-dropdown-item sponsor-item" data-id="{{ $u->id }}" data-text="{{ strtolower($u->name . ' ' . ($u->unvan ?? '') . ' ' . ($u->bolum?->ad ?? '')) }}" onclick="selectSponsor({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->unvan ?? $u->roles->first()?->name ?? 'Yönetici') }}')" style="padding: 9px 12px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9;">
                                            <div>
                                                <strong style="color: #0f172a; font-size: 13px;">{{ $u->name }}</strong>
                                                <span style="font-size: 11.5px; color: #64748b; margin-left: 6px;">{{ $u->bolum?->ad ?? $u->bolum?->name ?? 'Merkez' }} • {{ $u->unvan ?? $u->roles->first()?->name ?? 'Yönetici' }}</span>
                                            </div>
                                            <span class="sponsor-check-icon" id="spnCheck_{{ $u->id }}" style="display: none; color: #0284c7; font-weight: 700;">✓</span>
                                        </div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="sponsor_id" id="hiddenSponsorId" value="{{ old('sponsor_id', '') }}">
                            </div>
                        </div>
                    </div>

                                        <div class="pastel-blue-helper-box mb-0 mt-3">
                        <span class="helper-box-icon">💡</span>
                        <div class="helper-box-content">
                            Proje Yöneticisi ve Sponsoru aşağıdaki Proje Ekibi listesinde de ek görev alabilir. Ekibe dahil edildiklerinde sistem yönetici/sponsor unvanlarını koruyarak ek sorumluluklarını kaydeder.
                        </div>
                    </div>
                </div>

                <!-- BÖLÜM 3: PROJE EKİBİ & GÖREV DAĞILIMI -->
                <div class="project-form-section-card">
                    <div class="project-section-title">
                        <span class="title-text" style="display: flex; align-items: center; gap: 10px;">
                        <span style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #0284c7 0%, #1d4ed8 100%); color: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 850; box-shadow: 0 3px 8px rgba(2,132,199,0.3); flex-shrink: 0;">3</span>
                        <span style="font-size: 15px; font-weight: 800; color: #0f172a;">Proje Ekibi & Görev Dağılımı
                        </span>
                    </span>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            @if(auth()->user()->hasPermission('proje.manage_team_roles') || auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openTeamRolesModal()" style="font-weight: 600; padding: 5px 12px; border-radius: 8px; font-size: 12px; border: 1px solid #cbd5e1; color: #475569; background: #f8fafc;" title="Ekip rollerini ve unvanlarını ekle/düzenle/sil">
                                    ⚙️ Sorumluluk / Rolleri Yönet
                                </button>
                            @endif
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addTeamMemberRow()" style="font-weight: 600; padding: 5px 14px; border-radius: 8px; border: 1px solid #0284c7; color: #0284c7; background: #f0f9ff;">
                                + Ekip Üyesi Ekle
                            </button>
                        </div>
                    </div>

                    <!-- Ekip Listesi Kapsayıcısı -->
                    <div id="teamMembersContainer" class="mb-2">
                        <!-- Dinamik Satırlar Buraya Eklenecek -->
                    </div>

                    <div id="noTeamMembersNotice" class="pastel-blue-helper-box subtle mb-0" style="text-align: center; justify-content: center;">
                        <span class="helper-box-icon">👥</span>
                        <div class="helper-box-content">
                            Henüz proje ekibi üyesi eklenmedi. Projeye çoklu personel eklemek için yukarıdaki <strong>"+ Ekip Üyesi Ekle"</strong> butonuna tıklayabilirsiniz.
                        </div>
                    </div>
                </div>

                <!-- BÖLÜM 4: PROJE GÖZLEMCİLERİ (READ-ONLY / SALT OKUNUR TAKİPÇİLER & DIŞ PAYDAŞLAR) -->
                <div class="project-form-section-card">
                    <div class="project-section-title">
                        <span class="title-text" style="display: flex; align-items: center; gap: 10px;">
                        <span style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #0284c7 0%, #1d4ed8 100%); color: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 850; box-shadow: 0 3px 8px rgba(2,132,199,0.3); flex-shrink: 0;">4</span>
                        <span style="font-size: 15px; font-weight: 800; color: #0f172a;">Proje Gözlemcileri (Salt Okunur Takipçiler / Bilgi Verilecekler)
                        </span>
                    </span>
                        <span class="badge" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 11px; padding: 4px 10px;">
                            🔒 Read-Only Erişim
                        </span>
                    </div>

                    <div class="pastel-blue-helper-box mb-3">
                        <span class="helper-box-icon">ℹ️</span>
                        <div class="helper-box-content">
                            <strong>Gözlemci İzolasyonu & Güvenlik:</strong> Gözlemciler projeyi izler ancak görev, onay veya maliyet verilerini değiştiremezler. Eklenen <strong>harici kullanıcılar yalnızca ve yalnızca bu projeye</strong> erişebilir; URL'de ID değiştirerek diğer projelere ASLA erişemezler.
                        </div>
                    </div>

                    <!-- 4A: Kurum İçi Personel Gözlemcileri -->
                    <div class="mb-4" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px;">
                        <label class="form-label font-weight-700" style="font-size: 13px; color: #1e293b; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center;">
                            <span>🏢 Kurum İçi Personel Gözlemcileri (Aramalı ve Çoklu Seçim)</span>
                            <span id="selectedObserversCount" class="text-xs text-muted" style="font-weight: 500;">0 kişi seçildi</span>
                        </label>

                        <!-- ARAMALI MULTI-SELECT GÖZLEMCİ -->
                        <div class="searchable-multiselect-container" id="observersMultiSelect">
                            <div class="sms-input-wrap" id="smsInputWrap" onclick="focusSmsSearch()">
                                <div id="smsTagContainer" style="display: inline-flex; flex-wrap: wrap; gap: 6px;"></div>
                                <input type="text" id="smsSearchInput" class="sms-search-input" placeholder="🔍 Personel ara (isim, unvan, bölüm)..." oninput="handleSmsSearch(this.value)" onfocus="openSmsDropdown()">
                            </div>

                            <div class="sms-dropdown-menu" id="smsDropdownMenu">
                                @foreach($users as $u)
                                    <div class="sms-dropdown-item observer-item" data-id="{{ $u->id }}" data-text="{{ $u->name }} ({{ $u->bolum?->ad ?? $u->bolum?->name ?? 'Merkez' }} - {{ $u->unvan ?? 'Personel' }})" onclick="toggleSmsItem({{ $u->id }}, '{{ addslashes($u->name) }} ({{ addslashes($u->bolum?->ad ?? $u->bolum?->name ?? 'Merkez') }})')">
                                        <div>
                                            <strong>{{ $u->name }}</strong>
                                            <span style="font-size: 11.5px; color: #64748b; margin-left: 6px;">{{ $u->bolum?->ad ?? $u->bolum?->name ?? 'Merkez' }} • {{ $u->unvan ?? 'Personel' }}</span>
                                        </div>
                                        <span class="sms-check-indicator" style="display: none; color: #0284c7; font-weight: 700;">✓</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Gizli select kutusu -->
                        <select name="observers[]" id="hiddenObserversSelect" multiple style="display: none;"></select>

                        <div class="pastel-blue-helper-box subtle mt-2 mb-0" style="font-size: 11.5px; padding: 8px 12px;">
                            <span class="helper-box-icon">💡</span>
                            <div class="helper-box-content">
                                Arama kutusuna isim veya bölüm yazıp çıkan listeden dilediğiniz kadar personel seçebilirsiniz. Seçilen personeller yukarıda etiket olarak gösterilir.
                            </div>
                        </div>
                    </div>

                    <!-- 4B: Harici E-Posta ile Gözlemci Ekleme -->
                    <div style="background: #fdfdfe; border: 1px solid #e0e7ff; border-radius: 10px; padding: 18px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <div>
                                <label class="form-label font-weight-700" style="font-size: 13px; color: #1e1b4b; margin-bottom: 2px;">
                                    🌐 Harici E-Posta ile Gözlemci Ekle (Dış Paydaş / Müşteri / Denetçi)
                                </label>
                                <span class="text-xs text-muted" style="display: block;">
                                    Kurum dışı e-posta adresine sahip kişilere otomatik güvenli şifre atanır ve sadece bu projeye erişim verilir.
                                </span>
                            </div>
                            <button type="button" class="btn btn-sm" onclick="addExternalObserverRow()" style="background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; font-weight: 600; padding: 5px 12px; border-radius: 6px;">
                                + Harici Gözlemci Ekle
                            </button>
                        </div>

                        <div id="externalObserversContainer">
                            <!-- Dinamik Harici Gözlemci Satırları -->
                        </div>

                        <div id="noExternalObserversNotice" class="pastel-blue-helper-box subtle mb-0" style="text-align: center; justify-content: center; font-size: 12px;">
                            <span class="helper-box-icon">✉️</span>
                            <div class="helper-box-content">
                                Harici gözlemci eklemek için yukarıdaki <strong>"+ Harici Gözlemci Ekle"</strong> butonuna basarak e-posta ve isim girebilirsiniz.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BÖLÜM 5: YATIRIM BÜTÇESİ, TAKVİM & KAPSAM (TEK KALEM VEYA KALEM KALEM DETAYLI KIRILIM) -->
                <div class="project-form-section-card">
                    <div class="project-section-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                        <span class="title-text" style="display: flex; align-items: center; gap: 10px;">
                        <span style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #0284c7 0%, #1d4ed8 100%); color: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 850; box-shadow: 0 3px 8px rgba(2,132,199,0.3); flex-shrink: 0;">5</span>
                        <span style="font-size: 15px; font-weight: 800; color: #0f172a;">Yatırım Bütçesi, Takvim & Kapsam
                        </span>
                    </span>
                        
                        <!-- Bütçe Planlama Modu Seçici (Kullanıcı Talebi: Tek Kalem veya Kalem Kalem Ayrılabilir) -->
                        <div style="display: inline-flex; background: #e2e8f0; border-radius: 8px; padding: 3px; gap: 4px;">
                            <button type="button" id="btnBudgetModeSingle" onclick="setBudgetMode('single')" style="padding: 5px 12px; font-size: 11.5px; font-weight: 700; border-radius: 6px; border: none; background: #ffffff; color: #0284c7; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.08); transition: all 0.2s;">
                                ⚡ Tek Kalem Bütçe
                            </button>
                            <button type="button" id="btnBudgetModeDetailed" onclick="setBudgetMode('detailed')" style="padding: 5px 12px; font-size: 11.5px; font-weight: 700; border-radius: 6px; border: none; background: transparent; color: #64748b; cursor: pointer; transition: all 0.2s;">
                                📊 Kalem Kalem Detaylı Kırılım
                            </button>
                        </div>
                    </div>

                    <input type="hidden" name="budget_mode" id="budgetModeInput" value="single">

                    <!-- ORTAK ALANLAR: BÜTÇE, PARA BİRİMİ VE TARİHLER (DAİMA YAN YANA EŞLEŞTİRİLMİŞ 2x2 IZGARA) -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 16px; align-items: start;" class="mb-3">
                        <!-- Sol Blok: Planlanan Bütçe ve Para Birimi (Yan yana 2 sütun) -->
                        <div style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 12px; align-items: start;">
                            <!-- Tek Kalem Bütçe Girişi (Varsayılan Mod) -->
                            <div class="form-group mb-0" id="singleBudgetFieldGroup">
                                <label class="form-label-aligned" style="height: 22px; margin-bottom: 6px; display: flex; align-items: center;">
                                    <span>Planlanan Yatırım Bütçesi</span>
                                </label>
                                @php
                                    $oldBudget = old('planned_budget', $project->planned_budget);
                                    $oldBudgetFormatted = '';
                                    if ($oldBudget !== null && $oldBudget !== '') {
                                        $ob = (float) $oldBudget;
                                        $oldBudgetFormatted = ($ob == (int)$ob) ? number_format($ob, 0, ',', '.') : number_format($ob, 2, ',', '.');
                                    }
                                @endphp
                                <input type="text" inputmode="decimal" name="planned_budget" id="plannedBudgetInput" value="{{ $oldBudgetFormatted }}" class="form-control" placeholder="0" style="height: 42px; font-weight: 700; color: #0f172a;" oninput="handleBudgetInput(this)">
                            </div>

                            <!-- Detaylı Mod Aktifken Gösterilen Kümülatif Özet Kutusu -->
                            <div class="form-group mb-0" id="detailedBudgetSummaryGroup" style="display: none;">
                                <label class="form-label-aligned" style="height: 22px; margin-bottom: 6px; display: flex; align-items: center;">
                                    <span style="color: #0284c7; font-weight: 700;">∑ Kümülatif Toplam Bütçe</span>
                                </label>
                                <div style="height: 42px; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 6px; padding: 0 14px; display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-size: 11px; font-weight: 600; color: #166534;">Kalemler Toplamı:</span>
                                    <strong id="cumulativeDisplayTop" style="font-size: 15px; font-weight: 800; color: #15803d;">₺ 0,00</strong>
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <label class="form-label-aligned" style="height: 22px; margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between;">
                                    <span>Para Birimi</span>
                                    @if(auth()->user()->isDeveloper() || auth()->user()->isSuperAdmin())
                                    <button type="button" onclick="openCurrencyModal()" style="background: #f0f9ff; border: 1px solid #bae6fd; font-size: 11px; color: #0284c7; cursor: pointer; font-weight: 700; padding: 2px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; transition: all 0.15s; box-shadow: 0 1px 2px rgba(0,0,0,0.03);" onmouseover="this.style.background='#e0f2fe'; this.style.borderColor='#7dd3fc';" onmouseout="this.style.background='#f0f9ff'; this.style.borderColor='#bae6fd';" title="Para birimlerini ekle, düzenle veya sil (Developer)">
                                        <span style="font-size: 11px;">⚙️</span> <span>Yönet</span>
                                    </button>
                                    @endif
                                </label>
                                <select name="currency" id="projectCurrencySelect" class="form-control" style="height: 42px;" onchange="updateCurrencySymbols(this.value)">
                                    @if(isset($currencies) && $currencies->count() > 0)
                                        @foreach($currencies as $c)
                                            <option value="{{ $c->code }}" {{ $c->is_default ? "selected" : "" }}>
                                                {{ $c->code }} ({{ $c->symbol }}) {{ $c->name }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="TRY" selected>TRY (₺) Türk Lirası</option>
                                        <option value="USD">USD ($) Amerikan Doları</option>
                                        <option value="EUR">EUR (€) Euro</option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        <!-- Sağ Blok: Planlanan Başlangıç Tarihi ve Hedeflenen Bitiş Tarihi (DAİMA YAN YANA 2 SÜTUN) -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; align-items: start;">
                            <div class="form-group mb-0">
                                <label class="form-label-aligned" style="height: 22px; margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between;">
                                    <span>Planlanan Başlangıç Tarihi</span>
                                    <span style="font-size: 10.5px; color: #0284c7; font-weight: 600;">Gantt Referansı</span>
                                </label>
                                <input type="date" name="start_date" id="projectStartDateInput" class="form-control" value="{{ old('start_date', $project->start_date ? $project->start_date->format('Y-m-d') : date('Y-m-d')) }}" style="height: 42px; font-weight: 600;" onchange="calculateProjectDuration()">
                            </div>

                            <div class="form-group mb-0">
                                <label class="form-label-aligned" style="height: 22px; margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between;">
                                    <span>Hedeflenen Bitiş Tarihi</span>
                                    <span style="font-size: 10.5px; color: #16a34a; font-weight: 600;">Teslim Hedefi</span>
                                </label>
                                <input type="date" name="target_end_date" id="projectTargetEndDateInput" class="form-control" value="{{ old('target_end_date', $project->target_end_date ? $project->target_end_date->format('Y-m-d') : '') }}" style="height: 42px; font-weight: 600;" onchange="calculateProjectDuration()">
                            </div>
                        </div>
                    </div>

                    <!-- DİNAMİK PROJE SÜRESİ VE TAKVİM BİLGİ ROZETİ -->
                    <div id="projectDurationNotice" style="display: none; margin-bottom: 16px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 8px 14px; font-size: 12px; color: #166534; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 15px;">⏱️</span>
                            <span><strong>Planlanan Proje Süresi:</strong> <span id="projectDurationDaysText" style="font-weight: 800; color: #15803d;">0 Gün</span> (<span id="projectDurationMonthsText" style="font-weight: 700;">~0 Ay</span>)</span>
                        </div>
                        <div style="font-size: 11.5px; color: #15803d;">
                            <span>📅 Faz takvimleri ve Gantt başlangıcı otomatik olarak bu referans aralığına göre optimize edilir.</span>
                        </div>
                    </div>

                    <!-- DETAYLI KALEM KALEM BÜTÇE DAĞILIMI TABLOSU (DETAILED MODE) -->
                    <div id="detailedBudgetSection" style="display: none; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 16px; margin-bottom: 18px; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                            <div>
                                <h4 style="margin: 0; font-size: 13.5px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                                    <span>📑</span> Kalem Kalem Yatırım Maliyeti Kırılımı
                                </h4>
                                <span style="font-size: 11.5px; color: #64748b;">Maliyet takip paneline ve KPI radara kategori kategori aktarılır.</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                @if(auth()->user()->isDeveloper() || auth()->user()->isSuperAdmin())
                                <button type="button" class="btn btn-xs" onclick="openBudgetCategoryModal()" style="padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 6px; border: 1px solid #bae6fd; background: #f0f9ff; color: #0284c7; display: inline-flex; align-items: center; gap: 4px; transition: all 0.15s;" onmouseover="this.style.background='#e0f2fe'; this.style.borderColor='#7dd3fc';" onmouseout="this.style.background='#f0f9ff'; this.style.borderColor='#bae6fd';" title="Harcama kategorilerini ekle, düzenle veya sil (Developer)">
                                    <span style="font-size: 11px;">⚙️</span> <span>Kategorileri Yönet</span>
                                </button>
                            @endif
                                <button type="button" class="btn btn-xs btn-outline-primary" onclick="addBudgetItemRow()" style="padding: 5px 14px; font-size: 11.5px; font-weight: 700; border-radius: 6px;">
                                    + Yeni Bütçe Kalemi Ekle
                                </button>
                            </div>
                        </div>

                        <div style="overflow-x: auto;">
                            <table class="table" style="width: 100%; margin-bottom: 10px; border-collapse: separate; border-spacing: 0 6px;">
                                <thead>
                                    <tr style="background: #f8fafc; font-size: 11.5px; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">
                                        <th style="padding: 8px 10px; width: 30%; border-radius: 6px 0 0 6px;">Harcama Kategorisi</th>
                                        <th style="padding: 8px 10px; width: 42%;">Kalem Tanımı & Açıklaması</th>
                                        <th style="padding: 8px 10px; width: 22%;">Planlanan Tutar</th>
                                        <th style="padding: 8px 10px; width: 6%; text-align: center; border-radius: 0 6px 6px 0;">Sil</th>
                                    </tr>
                                </thead>
                                <tbody id="budgetItemsTbody">
                                    <!-- Dinamik Bütçe Kalemleri -->
                                </tbody>
                            </table>
                        </div>

                        <!-- KÜMÜLATİF OTOMATİK TOPLAM ÇUBUĞU -->
                        <div style="background: #f8fafc; border-top: 2px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                            <span style="font-size: 12.5px; font-weight: 600; color: #334155;">
                                📌 Kalemler Kümülatif Toplamı:
                            </span>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-size: 11px; color: #64748b;">(Tüm kalemlerin anlık toplamı)</span>
                                <span class="badge" style="background: #15803d; color: #ffffff; font-size: 14px; font-weight: 800; padding: 6px 14px; border-radius: 6px;" id="cumulativeBadge">
                                    ₺ 0,00
                                </span>
                            </div>
                        </div>
                    </div>

                                        <!-- ZENGİN METİN EDİTÖRÜ (PROJE AÇIKLAMASI & VİZYON KAPSAMI) -->
                    <div class="form-group mb-3 mt-3" id="projectDescriptionWrapper">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 6px;">
                            <label class="form-label font-weight-700" style="margin-bottom: 0; font-size: 13.5px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 16px;">📝</span> Proje Açıklaması ve Kapsamı
                                <span style="font-size: 11px; background: #e0f2fe; color: #0284c7; padding: 2px 8px; border-radius: 6px; font-weight: 700;">Zengin Metin (WYSIWYG)</span>
                            </label>
                            <div style="font-size: 11.5px; color: #64748b;">
                                💡 Kalın, liste, renk ve görsel ekleyerek projenizi pazarlayabilirsiniz.
                            </div>
                        </div>

                        <!-- Gizli textarea (Form submit için gerçek HTML'i tutar) -->
                        <textarea name="description" id="projectDescriptionInput" style="display: none;">{{ old('description', $project->description) }}</textarea>

                        <!-- Quill Editor Yuvası -->
                        <div id="quillEditorContainer" style="background: #ffffff;"></div>
                    </div>

                    <!-- ÖNİZLEMELİ VE MIME-TYPE KONTROLLÜ ÇOKLU DOSYA YÜKLEME ALANI -->
                    <div class="form-group mb-3" style="background: #f8fafc; border: 1.5px dashed #94a3b8; border-radius: 12px; padding: 18px; transition: all 0.2s;" id="scopeAttachmentsDropzone">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 36px; height: 36px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                    📎
                                </div>
                                <div>
                                    <strong style="font-size: 13.5px; color: #0f172a; display: block;">Proje Kapsam Dokümanları & Ek Dosyalar</strong>
                                    <span style="font-size: 11.5px; color: #64748b;">İzin Verilen Formatlar: PDF, Word (DOC/DOCX), Excel (XLS/XLSX), XML, Görseller (PNG/JPG/WEBP)</span>
                                </div>
                            </div>
                            <div>
                                <label for="scopeFileInput" class="btn btn-sm btn-outline-primary" style="margin: 0; cursor: pointer; padding: 6px 14px; font-weight: 700; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                                    <span>📁 Yeni Dosya Seç</span>
                                </label>
                                <input type="file" id="scopeFileInput" multiple style="display: none;" accept=".pdf,.doc,.docx,.xls,.xlsx,.xlm,.xml,.png,.jpg,.jpeg,.webp,.txt,.csv" onchange="handleScopeFilesSelected(this.files)">
                            </div>
                        </div>

                        <!-- Gizli form inputu (Form submit sırasında dosyaları gönderir) -->
                        <input type="file" name="attachments[]" id="hiddenFormAttachmentsInput" multiple style="display: none;">
                        <input type="hidden" name="attachment_tags_json" id="attachmentTagsJsonInput" value="[]">

                        <!-- Silinecek dosyalar için dinamik container -->
                        <div id="deletedAttachmentsInputs"></div>

                        <!-- Sürükle Bırak Bırakma Alanı -->
                        <div id="dropzonePrompt" style="text-align: center; padding: 16px; border: 1px dashed #cbd5e1; border-radius: 8px; background: #ffffff; cursor: pointer;" onclick="document.getElementById('scopeFileInput').click()">
                            <span style="font-size: 24px; display: block; margin-bottom: 4px;">☁️</span>
                            <span style="font-size: 12.5px; font-weight: 600; color: #334155;">Dosyaları buraya sürükleyip bırakın</span>
                            <span style="font-size: 11px; color: #94a3b8; display: block; margin-top: 2px;">veya yeni dosya seçmek için tıklayın (Maks. 25 MB/dosya)</span>
                        </div>

                        <!-- Mevcut Kayıtlı Dosyalar Listesi (Varsa) -->
                        @if($project->files->count() > 0)
                            <div style="margin-top: 14px; padding-top: 10px; border-top: 1px solid #e2e8f0;">
                                <div style="font-size: 11.5px; font-weight: 700; color: #475569; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    📌 Mevcut Kayıtlı Dokümanlar ({{ $project->files->count() }})
                                </div>
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 10px;" id="existingFilesContainer">
                                    @foreach($project->files as $pFile)
                                        @php
                                            $ext = strtolower(pathinfo($pFile->original_name, PATHINFO_EXTENSION));
                                            $fileIcon = in_array($ext, ['png','jpg','jpeg','webp','gif']) ? '🖼️' : ($ext === 'pdf' ? '📕' : (in_array($ext, ['xls','xlsx','xlm']) ? '📊' : (in_array($ext, ['doc','docx']) ? '📘' : ($ext === 'xml' ? '📙' : '📄'))));
                                        @endphp
                                        <div class="scope-file-card" id="existingFileCard_{{ $pFile->id }}">
                                            <div style="display: flex; align-items: center; gap: 8px; overflow: hidden;">
                                                <span style="font-size: 20px;">{{ $fileIcon }}</span>
                                                <div style="overflow: hidden;">
                                                    <div style="font-size: 12px; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px;" title="{{ $pFile->original_name }}">
                                                        {{ $pFile->original_name }}
                                                    </div>
                                                    <div style="font-size: 10.5px; color: #64748b;">
                                                        {{ number_format($pFile->file_size / 1024, 1) }} KB
                                                    </div>
                                                </div>
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <a href="{{ route('projects.download_file', [$project->id, $pFile->id]) }}" class="btn btn-xs btn-outline-secondary" style="padding: 2px 7px; font-size: 11px;" title="İndir">
                                                    ⬇
                                                </a>
                                                <button type="button" onclick="markExistingFileForDeletion({{ $pFile->id }}, '{{ addslashes($pFile->original_name) }}')" title="Dosyayı Kaldır" style="background: #fee2e2; border: none; color: #dc2626; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 11px; font-weight: 700; transition: all 0.15s;" onmouseover="this.style.background='#fecaca'" onmouseout="this.style.background='#fee2e2'">
                                                    ✕
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Yeni Eklenen Dosyaların Önizleme Kartları Grid'i -->
                        <div id="scopeFilesPreviewContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 10px; margin-top: 12px;"></div>
                    </div>

                    

                                        <!-- KAPSAM, ANALİZ & PROJE ARAÇLARI (WIDGET HAVUZU) -->
                    @include('projects.partials.scope_tools_form', ['project' => $project])

                    <!-- KURUMSAL KİLOMETRE TAŞLARI & FAZ GATELERİ (ÖNERİLEN FAZ PLANI) -->
                    <div class="card mb-4" style="border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 2px 8px rgba(15,23,42,0.03); overflow: hidden; margin-top: 18px;">
                        <div style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); padding: 14px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 34px; height: 34px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 17px;">
                                    🚩
                                </div>
                                <div>
                                    <h4 style="margin: 0; font-size: 14px; font-weight: 700; color: #0f172a;">Proje İçin Önerilen Kilometre Taşları & Faz Planı</h4>
                                    <p style="margin: 2px 0 0; font-size: 11.5px; color: #64748b;">Projenin ana aşamalarını, hedef tarihlerini ve faz tamamlanma kriterlerini belirleyin.</p>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addNewMilestoneRow()" style="font-weight: 700; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px; font-size: 12px; padding: 6px 14px;">
                                <span>➕</span> Yeni Faz / Kilometre Taşı Ekle
                            </button>
                        </div>
                        <div class="p-3" style="background: #ffffff;">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle mb-0" id="milestonesTable" style="font-size: 13px;">
                                    <thead style="background: #f8fafc; color: #475569; font-weight: 700; font-size: 12px;">
                                        <tr>
                                            <th style="width: 45px; text-align: center;">#</th>
                                            <th style="min-width: 220px;">Faz / Kilometre Taşı Adı <span class="text-danger">*</span></th>
                                            <th style="width: 150px;">Başlangıç Tarihi <span class="text-danger">*</span></th>
                                            <th style="width: 150px;">Hedef Bitiş Tarihi <span class="text-danger">*</span></th>
                                            <th style="min-width: 280px;">Tamamlanma Kriteri</th>
                                            <th style="width: 55px; text-align: center;">İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody id="milestonesTableBody">
                                        <!-- Mevcut veya Kurumsal Faz Satırları -->
                                    </tbody>
                                </table>
                            </div>
                            <div style="margin-top: 10px; font-size: 11.5px; color: #64748b; display: flex; align-items: center; gap: 6px;">
                                <span>💡</span> <em>Fazlar güncellendiğinde otomatik olarak Gantt şemasına ve canlı süreç akışına (Workflow Pipeline) aktarılır.</em>
                            </div>
                        </div>
                    </div>

                    <div class="pastel-blue-helper-box mb-0 mt-3">
                        <span class="helper-box-icon">ℹ️</span>
                        <div class="helper-box-content">
                            Girdiğiniz yatırım bütçesi ve hedef bitiş tarihi, proje onaylandığında otomatik olarak maliyet takip paneline ve KPI radar sistemine aktarılır.
                        </div>
                    </div>
                </div>

                <!-- BÖLÜM 6: PROJE GÜVENLİK, GİZLİLİK VE BİLDİRİM ZİNCİRİ YÖNETİMİ -->
                <div class="project-form-section-card mb-4" style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 20px 22px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                    <div class="project-section-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; border-bottom: 1.5px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 16px;">
                        <span class="title-text" style="display: flex; align-items: center; gap: 10px;">
                        <span style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #0284c7 0%, #1d4ed8 100%); color: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 850; box-shadow: 0 3px 8px rgba(2,132,199,0.3); flex-shrink: 0;">6</span>
                        <span style="font-size: 15px; font-weight: 800; color: #0f172a;">Proje Güvenlik, Gizlilik Seviyesi & Bildirim Zinciri
                        </span>
                    </span>
                        <span style="font-size: 12px; color: #64748b; font-weight: 500;">
                            Tüm proje genelinde geçerli güvenlik ve otomatik alarm mekanizmaları
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1.25fr 1fr; gap: 18px; align-items: stretch;">
                        <!-- Gizlilik Düzeyi (Varsayılan: Özel Proje) -->
                        <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 16px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                <label class="form-label font-weight-600 mb-0" style="font-size: 13px; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                                    <span>🔒</span> Proje Gizlilik Seviyesi & Erişim Kapsamı
                                </label>
                                <span class="badge" id="privacyCurrentBadge" style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px;">
                                    🔒 Özel Proje (Varsayılan)
                                </span>
                            </div>
                            <p style="font-size: 11.5px; color: #64748b; margin-bottom: 12px; line-height: 1.4;">
                                Proje ilerlerken detay sayfasından da bu ayar yetkili yöneticiler tarafından istendiğinde açılıp kapatılabilir.
                            </p>
                            <div style="display: flex; gap: 12px;">
                                <label id="labelVisPrivate" style="flex: 1; display: flex; align-items: flex-start; gap: 10px; background: #fff5f5; border: 1.5px solid #dc2626; border-radius: 8px; padding: 10px 12px; cursor: pointer; transition: all 0.2s;">
                                    <input type="radio" name="visibility" value="private" checked onchange="handleVisibilityChange('private')" style="margin-top: 3px;">
                                    <div>
                                        <strong style="display: block; font-size: 12.5px; color: #991b1b;">🔒 Özel / Gizli Proje</strong>
                                        <span style="font-size: 11px; color: #64748b; line-height: 1.35; display: block;">(Varsayılan) Yalnızca atanan ekip, liderler, direktörler ve superadmin görebilir.</span>
                                    </div>
                                </label>

                                <label id="labelVisPublic" style="flex: 1; display: flex; align-items: flex-start; gap: 10px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px 12px; cursor: pointer; transition: all 0.2s;">
                                    <input type="radio" name="visibility" value="public" onchange="handleVisibilityChange('public')" style="margin-top: 3px;">
                                    <div>
                                        <strong style="display: block; font-size: 12.5px; color: #334155;">🌐 Genel Proje</strong>
                                        <span style="font-size: 11px; color: #64748b; line-height: 1.35; display: block;">Tüm şirket personeli portföyde ve aramalarda özet bilgileri görebilir.</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Bildirim Kuralları Modalı Tetikleyici -->
                        <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                    <label class="form-label font-weight-600 mb-0" style="font-size: 13px; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                                        <span>🔔</span> Proje Otomatik Bildirim Zinciri
                                    </label>
                                    <span class="badge" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px;">
                                        ✓ Devrede
                                    </span>
                                </div>
                                <p style="font-size: 11.5px; color: #64748b; margin-bottom: 12px; line-height: 1.4;">
                                    Faz tamamlama, İSG kapısı onayları, takvim gecikmesi ve bütçe sapmalarında ilgili paydaşlara otomatik alarm gönderilir.
                                </p>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px 14px;">
                                <div>
                                    <span id="notifSummaryText" style="font-size: 12.5px; font-weight: 700; color: #0f172a; display: block;">✓ 5 Kural Varsayılan Aktif</span>
                                    <span style="font-size: 11px; color: #64748b;">Faz, İSG, gecikme & bütçe uyarıları</span>
                                </div>
                                <button type="button" class="btn btn-outline-primary" onclick="openNotificationRulesModal()" style="padding: 7px 14px; font-size: 12px; font-weight: 700; white-space: nowrap; border-radius: 7px; display: inline-flex; align-items: center; gap: 5px;">
                                    <span>⚙️</span> Kuralları Yapılandır
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- GİZLİ BİLDİRİM KURALLARI FORM İNPUTLARI -->
                    <input type="hidden" name="notification_settings[phase_complete_bell]" id="hidden_rule_phase_complete_bell" value="1">
                    <input type="hidden" name="notification_settings[phase_complete_mail]" id="hidden_rule_phase_complete_mail" value="1">
                    <input type="hidden" name="notification_settings[ehs_gate_bell]" id="hidden_rule_ehs_gate_bell" value="1">
                    <input type="hidden" name="notification_settings[ehs_gate_mail]" id="hidden_rule_ehs_gate_mail" value="1">
                    <input type="hidden" name="notification_settings[delay_alert_bell]" id="hidden_rule_delay_alert_bell" value="1">
                    <input type="hidden" name="notification_settings[delay_alert_mail]" id="hidden_rule_delay_alert_mail" value="1">
                    <input type="hidden" name="notification_settings[budget_alert_bell]" id="hidden_rule_budget_alert_bell" value="1">
                    <input type="hidden" name="notification_settings[budget_alert_mail]" id="hidden_rule_budget_alert_mail" value="1">
                    <input type="hidden" name="notification_settings[file_upload_bell]" id="hidden_rule_file_upload_bell" value="1">
                    <input type="hidden" name="notification_settings[file_upload_mail]" id="hidden_rule_file_upload_mail" value="0">
                </div>



                <!-- Form Butonları -->
                <div style="display: flex; justify-content: flex-end; align-items: center; gap: 14px; margin-top: 10px; padding-top: 18px; border-top: 1px solid #e2e8f0;">
                    <a href="{{ route('projects.show', $project->id) }}" class="btn btn-secondary" style="padding: 10px 20px;">
                        İptal
                    </a>
                    <input type="hidden" name="action_type" id="actionTypeInput" value="{{ $project->isDraft() ? 'draft' : 'update' }}">
                    <input type="hidden" name="code" value="{{ $project->code }}">
                    <input type="hidden" name="status" id="statusInput" value="{{ $project->status }}">

                    @if($project->isDraft())
                    <button type="button" id="btnSaveDraftEdit" onclick="submitEditFormAs('draft')" class="btn btn-outline-secondary" style="padding: 11px 22px; font-weight: 700; font-size: 14px; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px; background: #ffffff; color: #334155; border: 1.5px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <span>💾</span> Taslağı Güncelle
                    </button>
                    <button type="button" id="btnTriggerSummaryModal" onclick="submitEditFormAs('publish')" class="btn btn-primary" style="padding: 11px 26px; font-weight: 700; font-size: 14px; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25); border-radius: 8px; display: inline-flex; align-items: center; gap: 8px;">
                        <span>🚀</span> Projeyi Yayınla ve Başlat
                    </button>
                    @else
                    <button type="button" onclick="submitEditFormAs('draft')" class="btn btn-outline-warning" style="padding: 11px 20px; font-weight: 700; font-size: 14px; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; background: #fffbeb; border-color: #fde68a; color: #92400e;" title="Onay sürecini durdurur ve projeyi taslağa geri çeker">
                        <span>↩️</span> Taslağa Geri Çek
                    </button>
                    <button type="button" id="btnTriggerSummaryModal" onclick="openProjectSummaryConfirmModal()" class="btn btn-primary" style="padding: 11px 26px; font-weight: 700; font-size: 14px; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25); border-radius: 8px; display: inline-flex; align-items: center; gap: 8px;">
                        <span>👁️</span> Önizle & Değişiklikleri Kaydet
                    </button>
                    @endif
                </div>
            </form>

<!-- PROJE BAŞLATMA & ONAY ÖZETİ MODALI -->
<div id="projectSummaryModalBackdrop" class="custom-modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); z-index: 10000; backdrop-filter: blur(5px); align-items: center; justify-content: center; padding: 20px;">
    <div class="custom-modal-dialog custom-modal-dialog-wide" style="max-width: 1360px; width: 94vw; max-height: 94vh; display: flex; flex-direction: column; background: #ffffff; border-radius: 18px; box-shadow: 0 30px 70px -15px rgba(0, 0, 0, 0.45); overflow: hidden; animation: modalFadeIn 0.25s ease;">
        <!-- Modal Header -->
        <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1);">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(2, 132, 199, 0.2); border: 1px solid rgba(56, 189, 248, 0.4); display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    🚀
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #ffffff; letter-spacing: -0.3px;">
                        {{ $project->isDraft() ? 'Proje Başlatma & Onay Özeti' : 'Proje Güncelleme & Değişiklik Özeti' }}
                    </h3>
                    <p style="margin: 2px 0 0; font-size: 12px; color: #94a3b8;">
                        {{ $project->isDraft() ? 'Lütfen projeyi sisteme kaydetmeden önce tanımlanan temel parametreleri doğrulayınız.' : 'Lütfen değişiklikleri kaydetmeden önce güncellenen parametreleri doğrulayınız.' }}
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeProjectSummaryConfirmModal()" style="background: rgba(255,255,255,0.08); border: none; color: #94a3b8; width: 32px; height: 32px; border-radius: 8px; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                ✕
            </button>
        </div>

        <!-- Modal Body (Kaydırılabilir Özet İçeriği) -->
        <div id="projectSummaryModalBody" style="padding: 24px 28px; overflow-y: auto; overflow-x: hidden; max-height: calc(94vh - 145px); background: #f8fafc;">
            <!-- Dinamik Olarak JS ile Doldurulacak Kartlar -->
        </div>

        <!-- Modal Footer (Genişletilmiş Yönetici Paneli) -->
        <div style="background: #ffffff; border-top: 1px solid #e2e8f0; padding: 18px 28px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <button type="button" class="btn btn-secondary" onclick="closeProjectSummaryConfirmModal()" style="padding: 10px 22px; font-weight: 600; border-radius: 8px;">
                ✕ Düzenlemeye Geri Dön
            </button>
            <div style="display: flex; align-items: center; gap: 10px;">
                @if($project->isDraft())
                <button type="button" onclick="closeProjectSummaryConfirmModal(); submitEditFormAs('draft');" class="btn btn-outline-secondary" style="padding: 10px 20px; font-weight: 700; font-size: 13.5px; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155;">
                    <span>💾</span> Taslak Olarak Kaydet
                </button>
                
                <button type="button" id="btnConfirmAndSubmitProject" class="btn btn-primary" onclick="confirmAndSubmitProjectForm()" style="padding: 11px 32px; font-weight: 700; font-size: 14.5px; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); border-radius: 8px; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35); display: inline-flex; align-items: center; gap: 8px;">
                    <span>🚀 Onayla ve Projeyi Başlat</span>
                </button>
                @else
                <button type="button" onclick="closeProjectSummaryConfirmModal(); submitEditFormAs('draft');" class="btn btn-outline-warning" style="padding: 10px 20px; font-weight: 700; font-size: 13.5px; border-radius: 8px; background: #fffbeb; border-color: #fde68a; color: #92400e;" title="Onay sürecini durdurur ve projeyi taslağa geri çeker">
                    <span>↩️</span> Taslağa Geri Çek & Kaydet
                </button>
                
                <button type="button" id="btnConfirmAndSubmitProject" class="btn btn-primary" onclick="confirmAndSubmitProjectForm()" style="padding: 11px 32px; font-weight: 700; font-size: 14.5px; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); border-radius: 8px; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35); display: inline-flex; align-items: center; gap: 8px;">
                    <span>✓ Değişiklikleri Kaydet</span>
                </button>
                @endif
            </div>
        </div>
    </div>
</div>

        </div>
    </div>
</div>

<!-- KATEGORİ YÖNETİMİ & EKLEME/DÜZENLEME/SİLME MODALI -->
<div id="categoryModalBackdrop" class="custom-modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; backdrop-filter: blur(4px); align-items: center; justify-content: center;">
    <div class="custom-modal-dialog custom-modal-dialog-wide" style="max-width: 680px; width: 100%; max-height: 85vh; display: flex; flex-direction: column; background: #ffffff; border-radius: 14px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25); overflow: hidden;">
        <div class="custom-modal-header" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h3 class="custom-modal-title" style="margin: 0; font-size: 15px; font-weight: 700; color: #ffffff; display: flex; align-items: center; gap: 8px;">
                <span>🏷️</span> Proje Kategori Yönetimi & Tanımlama
            </h3>
            <button type="button" class="custom-modal-close" onclick="closeCategoryModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">✕</button>
        </div>

        <div class="custom-modal-body" style="padding: 20px; overflow-y: auto;">
            <!-- ÜST FORM: YENİ EKLE VEYA DÜZENLE -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <span id="catFormHeaderTitle" style="font-size: 13px; font-weight: 700; color: #0f172a;">
                        ➕ Yeni Proje Kategorisi Ekle
                    </span>
                    <button type="button" id="btnCancelEditCat" onclick="resetCategoryForm()" style="display: none; background: none; border: none; font-size: 11px; color: #64748b; cursor: pointer; text-decoration: underline;">
                        ✕ Düzenlemeyi İptal Et (Yeni Ekle Moduna Dön)
                    </button>
                </div>

                <input type="hidden" id="editCatId" value="">

                <div class="grid-2 mb-2" style="gap: 12px;">
                    <div class="form-group mb-0">
                        <label class="modal-label" style="font-size: 11.5px; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Kategori Adı <span class="text-danger">*</span></label>
                        <input type="text" id="newCatName" class="form-control" placeholder="Örn: Otomasyon & SCADA Entegrasyonu" required style="height: 38px; font-size: 12.5px;">
                    </div>

                    <div class="form-group mb-0">
                        <label class="modal-label" style="font-size: 11.5px; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Görsel Renk Kodu</label>
                        <div class="color-picker-input-wrap" style="display: flex; gap: 8px;">
                            <input type="color" id="newCatColor" value="#0284c7" style="height: 38px; width: 44px; padding: 2px; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer;">
                            <input type="text" id="newCatColorHex" class="form-control" value="#0284c7" maxlength="7" oninput="document.getElementById('newCatColor').value = this.value" style="height: 38px; font-size: 12.5px; font-family: monospace;">
                        </div>
                    </div>
                </div>

                <div class="form-group mb-2">
                    <label class="modal-label" style="font-size: 11.5px; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Açıklama <span class="text-muted" style="font-size: 11px;">(Opsiyonel)</span></label>
                    <input type="text" id="newCatDesc" class="form-control" placeholder="Kategori kapsamı hakkında kısa bilgi..." style="height: 36px; font-size: 12px;">
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 10px;">
                    <button type="button" class="btn btn-primary btn-sm" id="btnSaveCategory" onclick="saveCategoryModalAction()" style="padding: 6px 18px; font-size: 12px; font-weight: 700; border-radius: 6px;">
                        ✓ Kategoriyi Kaydet
                    </button>
                </div>
            </div>

            <!-- ALT BÖLÜM: MEVCUT KATEGORİLER LİSTESİ (DÜZENLE & SİL) -->
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <h4 style="margin: 0; font-size: 13px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                        <span>📋</span> Mevcut Proje Kategorileri
                    </h4>
                    <span style="font-size: 11px; color: #64748b;">Bağlı proje varsa soft delete, yoksa kalıcı silinir.</span>
                </div>

                <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                    <table class="table" style="width: 100%; margin-bottom: 0; font-size: 12px;">
                        <thead style="background: #f8fafc; color: #475569; font-size: 11px; text-transform: uppercase;">
                            <tr>
                                <th style="padding: 8px 12px; width: 42%;">Kategori</th>
                                <th style="padding: 8px 12px; width: 25%;">Bağlı Proje</th>
                                <th style="padding: 8px 12px; width: 30%; text-align: right;">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="categoryModalTableBody">
                            <!-- AJAX ile dinamik yüklenecek -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="custom-modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: flex-end;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeCategoryModal()">Kapat</button>
        </div>
    </div>
</div>

<!-- EKİP SORUMLULUK & ROLLERİ YÖNETİM MODALI -->
<div id="teamRolesModalBackdrop" class="custom-modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; backdrop-filter: blur(4px); align-items: center; justify-content: center;">
    <div class="custom-modal-dialog custom-modal-dialog-wide" style="max-width: 620px; width: 100%; max-height: 85vh; display: flex; flex-direction: column; background: #ffffff; border-radius: 14px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25); overflow: hidden;">
        <div class="custom-modal-header" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h3 class="custom-modal-title" style="margin: 0; font-size: 15px; font-weight: 700; color: #ffffff; display: flex; align-items: center; gap: 8px;">
                <span>⚙️</span> Proje Ekip Sorumluluk & Rolleri Yönetimi
            </h3>
            <button type="button" class="custom-modal-close" onclick="closeTeamRolesModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">✕</button>
        </div>

        <div class="custom-modal-body" style="padding: 20px; overflow-y: auto;">
            <!-- ÜST FORM: YENİ ROL EKLE -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <span id="roleFormHeaderTitle" style="font-size: 13px; font-weight: 700; color: #0f172a;">
                        ➕ Yeni Ekip Sorumluluğu / Fonksiyonu Ekle
                    </span>
                    <button type="button" id="btnCancelEditRole" onclick="resetRoleForm()" style="display: none; background: none; border: none; font-size: 11px; color: #64748b; cursor: pointer; text-decoration: underline;">
                        ✕ Düzenlemeyi İptal Et
                    </button>
                </div>

                <input type="hidden" id="editRoleId" value="">

                <div class="grid-2 mb-2" style="gap: 12px;">
                    <div class="form-group mb-0">
                        <label class="modal-label" style="font-size: 11.5px; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Sorumluluk / Rol Adı <span class="text-danger">*</span></label>
                        <input type="text" id="newRoleName" class="form-control" placeholder="Örn: SCADA & PLC Sistem Mühendisi" required style="height: 38px; font-size: 12.5px;">
                    </div>
                    <div class="form-group mb-0">
                        <label class="modal-label" style="font-size: 11.5px; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Açıklama (Opsiyonel)</label>
                        <input type="text" id="newRoleDesc" class="form-control" placeholder="Görev tanımı ve fonksiyonu..." style="height: 38px; font-size: 12.5px;">
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 10px;">
                    <button type="button" class="btn btn-primary btn-sm" id="btnSaveRole" onclick="saveTeamRoleAction()" style="padding: 6px 18px; font-size: 12px; font-weight: 700; border-radius: 6px;">
                        ✓ Rolü Kaydet
                    </button>
                </div>
            </div>

            <!-- ALT BÖLÜM: MEVCUT ROLLER LİSTESİ -->
            <div>
                <h4 style="margin: 0 0 10px 0; font-size: 13px; font-weight: 700; color: #1e293b;">
                    📋 Sistemde Tanımlı Ekip Rolleri
                </h4>
                <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                    <table class="table" style="width: 100%; margin-bottom: 0; font-size: 12px;">
                        <thead style="background: #f8fafc; color: #475569; font-size: 11px; text-transform: uppercase;">
                            <tr>
                                <th style="padding: 8px 12px; width: 60%;">Rol / Fonksiyon Adı</th>
                                <th style="padding: 8px 12px; width: 40%; text-align: right;">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="teamRolesModalTableBody">
                            <!-- AJAX ile dinamik yüklenecek -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="custom-modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: flex-end;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeTeamRolesModal()">Kapat</button>
        </div>
    </div>
</div>

<!-- BİLDİRİM KURALLARI YAPILANDIRMA MODALI -->
<div id="notificationRulesModal" class="custom-modal-backdrop" onclick="if(event.target === this) closeNotificationRulesModal()" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; backdrop-filter: blur(4px); align-items: center; justify-content: center;">
    <div style="background: #ffffff; width: 100%; max-width: 620px; border-radius: 14px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25); overflow: hidden; margin: 20px; animation: modalFadeIn 0.2s ease;">
        <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 8px; font-size: 14.5px; font-weight: 700;">
                <span>🔔</span> Proje Bildirim & Alarm Kuralları Yapılandırma
            </div>
            <button type="button" onclick="closeNotificationRulesModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">&times;</button>
        </div>

        <div style="padding: 20px; max-height: 75vh; overflow-y: auto;">
            <div class="pastel-blue-helper-box mb-3" style="font-size: 12px; margin-top: 0;">
                <span class="helper-box-icon">ℹ️</span>
                <div class="helper-box-content">
                    Burada belirlediğiniz kurallar, proje onaylandığında otomatik bildirim motoruna kaydedilir. İlgili durumlar gerçekleştiğinde seçili kanallardan anlık iletilir.
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                <!-- Kural 1: Faz Tamamlandı -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="font-size: 13px; color: #0f172a; display: block;">🏁 Faz / Aşama Tamamlandığında</strong>
                        <span style="font-size: 11px; color: #64748b;">Proje Sponsoru, Proje Yöneticisi ve Gözlemcilere ilerleme raporu ilet.</span>
                    </div>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <label style="font-size: 12px; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                            <input type="checkbox" id="modal_rule_phase_complete_bell" checked> 🔔 Zil
                        </label>
                        <label style="font-size: 12px; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                            <input type="checkbox" id="modal_rule_phase_complete_mail" checked> ✉️ E-Posta
                        </label>
                    </div>
                </div>

                <!-- Kural 2: İSG Kapı Kilidi Onayı -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="font-size: 13px; color: #0f172a; display: block;">🔒 Kritik İSG Kapı Onayı Beklendiğinde</strong>
                        <span style="font-size: 11px; color: #64748b;">İSG Denetçisi ve PMO'ya anlık onay talebi ve SLA alarmı ilet.</span>
                    </div>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <label style="font-size: 12px; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                            <input type="checkbox" id="modal_rule_ehs_gate_bell" checked> 🔔 Zil
                        </label>
                        <label style="font-size: 12px; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                            <input type="checkbox" id="modal_rule_ehs_gate_mail" checked> ✉️ E-Posta
                        </label>
                    </div>
                </div>

                <!-- Kural 3: Görev Gecikmesi & Darboğaz -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="font-size: 13px; color: #0f172a; display: block;">⏳ Görev Gecikmesi & Darboğaz Durumunda</strong>
                        <span style="font-size: 11px; color: #64748b;">Görev sorumlusuna ve Proje Yöneticisine darboğaz alarmı gönder.</span>
                    </div>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <label style="font-size: 12px; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                            <input type="checkbox" id="modal_rule_delay_alert_bell" checked> 🔔 Zil
                        </label>
                        <label style="font-size: 12px; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                            <input type="checkbox" id="modal_rule_delay_alert_mail" checked> ✉️ E-Posta
                        </label>
                    </div>
                </div>

                <!-- Kural 4: Bütçe Limiti & Sapma -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="font-size: 13px; color: #0f172a; display: block;">💰 Bütçe Limiti %80 veya Sapma Gerçekleştiğinde</strong>
                        <span style="font-size: 11px; color: #64748b;">Bütçe aşım riskinde Finans Departmanı ve Proje Sponsorunu uyar.</span>
                    </div>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <label style="font-size: 12px; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                            <input type="checkbox" id="modal_rule_budget_alert_bell" checked> 🔔 Zil
                        </label>
                        <label style="font-size: 12px; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                            <input type="checkbox" id="modal_rule_budget_alert_mail" checked> ✉️ E-Posta
                        </label>
                    </div>
                </div>

                <!-- Kural 5: Yeni Doküman & Dosya Paylaşımı -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong style="font-size: 13px; color: #0f172a; display: block;">📁 Yeni Doküman / Envanter Yüklendiğinde</strong>
                        <span style="font-size: 11px; color: #64748b;">Proje ekibine yeni revizyon veya teknik doküman bildirimi ilet.</span>
                    </div>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <label style="font-size: 12px; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                            <input type="checkbox" id="modal_rule_file_upload_bell" checked> 🔔 Zil
                        </label>
                        <label style="font-size: 12px; display: flex; align-items: center; gap: 4px; cursor: pointer;">
                            <input type="checkbox" id="modal_rule_file_upload_mail"> ✉️ E-Posta
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center;">
            <button type="button" class="btn btn-secondary" onclick="closeNotificationRulesModal()">Vazgeç</button>
            <button type="button" class="btn btn-primary" onclick="applyNotificationRules()" style="padding: 8px 20px; font-weight: 600;">
                ✓ Kuralları Uygula & Kaydet
            </button>
        </div>
    </div>
</div>
@include('partials.modals.confirm_delete_modal')
@include('partials.modals.budget_categories_modal')
@include('partials.modals.currencies_modal')

<script>
document.addEventListener('DOMContentLoaded', function() {
        renderProjectTags();

    // 1. Kategorileri Doldur
    const initialCategories = @json($project->categories ?? ($project->category ? [$project->category] : []));
    if (Array.isArray(initialCategories)) {
        initialCategories.forEach(slug => {
            const item = document.querySelector(`.category-item[data-slug="${slug}"]`);
            if (item) {
                const name = item.querySelector('strong')?.textContent?.trim() || slug;
                const color = item.querySelector('span[style*="background-color"]')?.style?.backgroundColor || '#0284c7';
                selectedCategories.set(slug, { name, color });
            }
        });
        if (typeof renderCategoryTags === 'function') renderCategoryTags();
    }

    // 3. Proje Yöneticisi & Sponsor Doldur
    @if($project->manager)
        if (typeof selectManager === 'function') {
            selectManager({{ $project->manager->id }}, @json($project->manager->name), @json($project->manager->unvan ?? 'Proje Yöneticisi'));
        }
    @endif
    @if($project->sponsor)
        if (typeof selectSponsor === 'function') {
            selectSponsor({{ $project->sponsor->id }}, @json($project->sponsor->name), @json($project->sponsor->unvan ?? 'Proje Sponsoru'));
        }
    @endif


    // 2. Bölümleri Doldur
    const initialBolumler = @json($project->bolum_ids ?? ($project->bolum_id ? [$project->bolum_id] : []));
    if (Array.isArray(initialBolumler)) {
        initialBolumler.forEach(id => {
            const item = document.querySelector(`.bolum-item[data-id="${id}"]`);
            if (item) {
                const name = item.querySelector('strong')?.textContent?.trim() || `Bölüm #${id}`;
                selectedBolumler.set(String(id), { name });
            }
        });
        if (typeof renderBolumTags === 'function') renderBolumTags();
    }

    // 3. Proje Yöneticisi Doldur
    @if($project->manager)
        selectManager(
            {{ $project->manager->id }},
            @json($project->manager->name),
            @json($project->manager->unvan ?? $project->manager->roles->first()?->name ?? 'Uzman')
        );
    @endif

    // 4. Proje Sponsoru Doldur
    @if($project->sponsor)
        selectSponsor(
            {{ $project->sponsor->id }},
            @json($project->sponsor->name),
            @json($project->sponsor->unvan ?? $project->sponsor->roles->first()?->name ?? 'Yönetici')
        );
    @endif

    // 5. Ekip Üyelerini Doldur
    const initialTeamMembers = [
        @foreach($project->stakeholders->where('role_type', 'team') as $stk)
            @if($stk->user)
            {
                user_id: {{ $stk->user_id }},
                user_name: @json($stk->user->name . ' (' . ($stk->user->unvan ?? 'Uzman') . ')'),
                responsibility: @json($stk->notes ?? 'Ekip Üyesi')
            },
            @endif
        @endforeach
    ];

    if (initialTeamMembers.length > 0) {
        initialTeamMembers.forEach(m => {
            addTeamMemberRow(m);
            // Son eklenen satırın sorumluluk ve kullanıcısını seç
            const lastIdx = teamMemberRowIndex - 1;
            if (typeof selectTeamUser === 'function') {
                selectTeamUser(lastIdx, m.user_id, m.user_name);
            }
            const selEl = document.querySelector(`select[name="team_members[${lastIdx}][responsibility]"]`);
            if (selEl) {
                selEl.value = m.responsibility;
                if (!selEl.value) {
                    selEl.value = 'Diğer';
                    const customWrap = document.getElementById(`customRespWrap_${lastIdx}`);
                    const customInp = document.getElementById(`customRespInput_${lastIdx}`);
                    if (customWrap) customWrap.style.display = 'block';
                    if (customInp) customInp.value = m.responsibility;
                }
            }
        });
    }

    // 6. Gözlemcileri (Dahili) Doldur
    const initialObserverIds = @json($project->stakeholders->where('role_type', 'observer')->pluck('user_id')->toArray());
    if (Array.isArray(initialObserverIds)) {
        initialObserverIds.forEach(id => {
            const item = document.querySelector(`.observer-item[data-id="${id}"]`);
            if (item) {
                const name = item.querySelector('strong')?.textContent?.trim() || `Personel #${id}`;
                selectedObservers.set(String(id), { name });
            }
        });
        if (typeof renderObserverTags === 'function') renderObserverTags();
    }

    // 7. Bütçe Kalemlerini Doldur
    const initialBudgetItems = [
        @foreach($project->budgetItems as $bItem)
        {
            category: @json($bItem->category ?? 'Genel Yatırım'),
            title: @json($bItem->title),
            amount: {{ (float)$bItem->planned_amount }}
        },
        @endforeach
    ];

    if (initialBudgetItems.length > 0) {
        if (typeof setBudgetMode === 'function') {
            setBudgetMode('detailed');
        }
        const tbody = document.getElementById('budgetItemsTbody');
        if (tbody) tbody.innerHTML = '';
        initialBudgetItems.forEach(b => {
            addBudgetItemRow(b.category, b.title, b.amount);
        });
    }
});
</script>

<script src="{{ asset('vendor/quill/quill.js') }}"></script>
<script>
let quill = null;
let pendingScopeFiles = [];
const ALLOWED_SCOPE_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'xlm', 'xml', 'png', 'jpg', 'jpeg', 'webp', 'txt', 'csv'];
const MAX_SCOPE_FILE_SIZE = 25 * 1024 * 1024; // 25 MB

document.addEventListener('DOMContentLoaded', function() {
        renderProjectTags();

    // 1. Quill Editor Başlatma
    const editorContainer = document.getElementById('quillEditorContainer');
    const hiddenDescInput = document.getElementById('projectDescriptionInput');
    if (editorContainer && typeof Quill !== 'undefined') {
        const toolbarOptions = [
            [{ 'header': [1, 2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            ['blockquote', 'code-block'],
            ['link', 'image'],
            ['clean']
        ];

        quill = new Quill(editorContainer, {
            theme: 'snow',
            modules: { toolbar: toolbarOptions },
            placeholder: 'Projenin hedefleri, teknik isterleri, vizyonu ve beklenen kurumsal kazanımları hakkında detaylı açıklama yazınız (Kalın metin, görsel ve liste ekleyebilirsiniz)...'
        });

        // Mevcut açıklama varsa editöre aktar
        if (hiddenDescInput && hiddenDescInput.value) {
            quill.root.innerHTML = hiddenDescInput.value;
        }

        quill.on('text-change', function() {
            if (hiddenDescInput) {
                hiddenDescInput.value = quill.root.innerHTML;
            }
        });
    }

    // 2. Drag & Drop Listener'ları
    const dropzone = document.getElementById('scopeAttachmentsDropzone');
    if (dropzone) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.style.borderColor = '#0284c7';
                dropzone.style.background = '#f0f9ff';
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.style.borderColor = '#94a3b8';
                dropzone.style.background = '#f8fafc';
            }, false);
        });

        dropzone.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleScopeFilesSelected(files);
        }, false);
    }
});

function markExistingFileForDeletion(fileId, fileName) {
    if (!confirm(`"${fileName}" dosyasını projeden kaldırmak istediğinize emin misiniz?`)) return;

    const container = document.getElementById('deletedAttachmentsInputs');
    if (container) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'deleted_attachment_ids[]';
        input.value = fileId;
        container.appendChild(input);
    }

    const card = document.getElementById(`existingFileCard_${fileId}`);
    if (card) {
        card.style.opacity = '0.3';
        card.style.pointerEvents = 'none';
        card.style.textDecoration = 'line-through';
        setTimeout(() => card.remove(), 400);
    }
}

function handleScopeFilesSelected(fileList) {
    if (!fileList || fileList.length === 0) return;

    for (let i = 0; i < fileList.length; i++) {
        const file = fileList[i];
        const ext = file.name.split('.').pop().toLowerCase();

        // Uzantı ve MIME kontrolü
        if (!ALLOWED_SCOPE_EXTENSIONS.includes(ext)) {
            alert(`"${file.name}" desteklenmeyen dosya türüdür!\nİzin verilenler: PDF, DOC/DOCX, XLS/XLSX, XML, PNG/JPG/WEBP.`);
            continue;
        }

        // Boyut kontrolü
        if (file.size > MAX_SCOPE_FILE_SIZE) {
            alert(`"${file.name}" boyutu 25 MB sınırını aşıyor! Lütfen daha küçük dosya yükleyiniz.`);
            continue;
        }

        // Çift eklemeyi engelle
        if (!pendingScopeFiles.some(f => f.name === file.name && f.size === file.size)) {
            pendingScopeFiles.push(file);
        }
    }

    syncPendingFilesToHiddenInput();
    renderScopeFilesPreview();
}

function removePendingScopeFile(index) {
    pendingScopeFiles.splice(index, 1);
    syncPendingFilesToHiddenInput();
    renderScopeFilesPreview();
}

function syncPendingFilesToHiddenInput() {
    const dt = new DataTransfer();
    const tagsArr = [];
    pendingScopeFiles.forEach(f => {
        dt.items.add(f);
        tagsArr.push(f._tags || []);
    });
    const hiddenInput = document.getElementById('hiddenFormAttachmentsInput');
    if (hiddenInput) {
        hiddenInput.files = dt.files;
    }
    const tagsHidden = document.getElementById('attachmentTagsJsonInput');
    if (tagsHidden) {
        tagsHidden.value = JSON.stringify(tagsArr);
    }
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    else if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    else return (bytes / 1048576).toFixed(1) + ' MB';
}

function renderScopeFilesPreview() {
    const container = document.getElementById('scopeFilesPreviewContainer');
    if (!container) return;
    container.innerHTML = '';

    pendingScopeFiles.forEach((file, idx) => {
        const ext = file.name.split('.').pop().toLowerCase();
        let iconHtml = '';

        if (['png', 'jpg', 'jpeg', 'webp', 'gif'].includes(ext)) {
            const blobUrl = URL.createObjectURL(file);
            iconHtml = `<img src="${blobUrl}" style="width: 36px; height: 36px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e1;">`;
        } else if (ext === 'pdf') {
            iconHtml = `<div style="width: 36px; height: 36px; border-radius: 6px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 11px;">PDF</div>`;
        } else if (['doc', 'docx'].includes(ext)) {
            iconHtml = `<div style="width: 36px; height: 36px; border-radius: 6px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 11px;">DOC</div>`;
        } else if (['xls', 'xlsx', 'xlm'].includes(ext)) {
            iconHtml = `<div style="width: 36px; height: 36px; border-radius: 6px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 11px;">XLS</div>`;
        } else if (ext === 'xml') {
            iconHtml = `<div style="width: 36px; height: 36px; border-radius: 6px; background: #ffedd5; color: #ea580c; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 11px;">XML</div>`;
        } else {
            iconHtml = `<div style="width: 36px; height: 36px; border-radius: 6px; background: #f1f5f9; color: #475569; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 11px;">FILE</div>`;
        }

        const tags = file._tags || [];
        const card = document.createElement('div');
        card.className = 'scope-file-card';
        card.style.flexDirection = 'column';
        card.style.alignItems = 'stretch';
        card.style.gap = '8px';
        card.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 10px; overflow: hidden;">
                    ${iconHtml}
                    <div style="overflow: hidden;">
                        <div style="font-size: 12px; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;" title="${escapeHtml(file.name)}">
                            ${escapeHtml(file.name)}
                        </div>
                        <div style="font-size: 10.5px; color: #64748b;">
                            ${formatFileSize(file.size)}
                        </div>
                    </div>
                </div>
                <button type="button" onclick="removePendingScopeFile(${idx})" title="Dosyayı Kaldır" style="background: #fee2e2; border: none; color: #dc2626; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 11px; font-weight: 700; transition: all 0.15s; margin-left: 6px;" onmouseover="this.style.background='#fecaca'" onmouseout="this.style.background='#fee2e2'">
                    ✕
                </button>
            </div>

            <!-- DOSYA ETİKETLERİ BÖLÜMÜ -->
            <div style="border-top: 1px dashed #e2e8f0; padding-top: 6px;">
                <div style="display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 5px;">
                    ${tags.map((t, tIdx) => `
                        <span style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
                            <span>#${escapeHtml(t)}</span>
                            <a href="javascript:void(0)" onclick="removeScopeFileTag(${idx}, ${tIdx})" style="color: #0284c7; text-decoration: none; font-weight: 800;">✕</a>
                        </span>
                    `).join('')}
                    ${tags.length === 0 ? '<span style="font-size: 10px; color: #94a3b8; font-style: italic;">Etiket eklenmedi</span>' : ''}
                </div>
                <div style="display: flex; align-items: center; gap: 4px;">
                    <select onchange="if(this.value){ addScopeFileTag(${idx}, this.value); this.value=''; }" class="form-control form-control-xs" style="font-size: 10.5px; height: 25px; padding: 1px 6px; border-radius: 4px; border-color: #cbd5e1; background: #ffffff; flex: 1;">
                        <option value="">🏷️ + Etiket Seç...</option>
                        <option value="Sözleşme">📄 #Sözleşme</option>
                        <option value="Teknik Şartname">📐 #Teknik Şartname</option>
                        <option value="Fizibilite">📊 #Fizibilite</option>
                        <option value="CAD / Çizim">📐 #CAD / Çizim</option>
                        <option value="Fatura / Teklif">💵 #Fatura / Teklif</option>
                        <option value="İzin & Ruhsat">🏛️ #İzin & Ruhsat</option>
                        <option value="İSG / Rapor">🛡️ #İSG / Rapor</option>
                        <option value="Diğer">🔖 #Diğer</option>
                    </select>
                    <input type="text" placeholder="+ yaz..." style="font-size: 10px; height: 25px; width: 68px; padding: 1px 5px; border-radius: 4px; border: 1px solid #cbd5e1;" onkeydown="if(event.key==='Enter'||event.key===','){ event.preventDefault(); if(this.value.trim()){ addScopeFileTag(${idx}, this.value.trim()); this.value=''; } }">
                </div>
            </div>
        `;
        container.appendChild(card);
    });
}
</script>

@endsection

@push('scripts')
<script>
// =========================================================================
// HAZIR ŞABLON ANLIK UYGULAMA & CANLI ÖNİZLEME (INSTANT TEMPLATE ENGINE)
// =========================================================================
window.PROJECT_TEMPLATES = @json($templates);

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function getTemplateById(id) {
    if (!window.PROJECT_TEMPLATES || !Array.isArray(window.PROJECT_TEMPLATES)) return null;
    return window.PROJECT_TEMPLATES.find(t => String(t.id) === String(id)) || null;
}

function handleTemplateSelectChange(val) {
    if (!val) {
        resetTemplateSelection(false);
        return;
    }
    applyCurrentTemplate();
}

function applyCurrentTemplate() {
    const selectEl = document.getElementById('templateSelect');
    const templateId = selectEl ? selectEl.value : null;
    if (!templateId) {
        alert('Lütfen önce bir hazır şablon seçiniz.');
        return;
    }

    const tpl = getTemplateById(templateId);
    if (!tpl) {
        // Fallback AJAX
        fetch(`/projects/templates/${templateId}/json`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.template) {
                    fillFormFromTemplate(data.template);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Şablon verisi yüklenirken bir hata oluştu.');
            });
        return;
    }

    fillFormFromTemplate(tpl);
}

function fillFormFromTemplate(tpl) {
    // 1. Proje Başlığı (input[name="title"])
    const titleInput = document.querySelector('input[name="title"]');
    if (titleInput) {
        let cleanTitle = tpl.title.replace(' Şablonu', ' Projesi').replace('Şablonu', 'Projesi');
        titleInput.value = cleanTitle;
        titleInput.style.transition = 'background-color 0.4s';
        titleInput.style.backgroundColor = '#dcfce7';
        setTimeout(() => { titleInput.style.backgroundColor = ''; }, 1200);
    }

    // 2. Kategori Çipleri
    if (tpl.category) {
        const catItem = document.querySelector(`.category-item[data-id="${tpl.category}"]`);
        if (catItem) {
            const catName = catItem.getAttribute('data-text') || tpl.category;
            selectedCategories.clear();
            const dotEl = catItem.querySelector('span[style*="background-color"]');
            const color = dotEl ? dotEl.style.backgroundColor : '#0284c7';
            selectedCategories.set(tpl.category, { name: catName, color: color });
            renderCategoryTags();
        }
    }

    // 3. Öncelik
    const prioritySelect = document.querySelector('select[name="priority"]');
    if (prioritySelect && tpl.priority) {
        prioritySelect.value = tpl.priority;
    }

    // 4. Saha / Lokasyon
    const locationInput = document.querySelector('input[name="location"]');
    if (locationInput && tpl.location) {
        locationInput.value = tpl.location;
    }

    // 5. Bütçe & Para Birimi
    const budgetInput = document.querySelector('input[name="planned_budget"]');
    if (budgetInput && tpl.planned_budget) {
        budgetInput.value = parseFloat(tpl.planned_budget).toFixed(2);
        budgetInput.style.transition = 'background-color 0.4s';
        budgetInput.style.backgroundColor = '#dcfce7';
        setTimeout(() => { budgetInput.style.backgroundColor = ''; }, 1200);
    }
    const currencySelect = document.querySelector('select[name="currency"]');
    if (currencySelect && tpl.currency) {
        currencySelect.value = tpl.currency;
    }

    // 6. Açıklama
    const descTextarea = document.querySelector('textarea[name="description"]');
    if (descTextarea && tpl.description) {
        descTextarea.value = tpl.description;
    }

    // 7. Buton ve Rozet Durumu
    const togglePreviewBtn = document.getElementById('togglePreviewBtn');
    const resetBtn = document.getElementById('resetTemplateBtn');
    const statusBadge = document.getElementById('templateStatusBadge');
    const countSpan = document.getElementById('previewPhasesCount');

    if (togglePreviewBtn) togglePreviewBtn.style.display = 'inline-flex';
    if (resetBtn) resetBtn.style.display = 'inline-flex';
    if (countSpan) countSpan.textContent = tpl.phases ? tpl.phases.length : 0;
    if (statusBadge) {
        statusBadge.innerHTML = '✓ ' + (tpl.code || 'Şablon') + ' Uygulandı';
        statusBadge.style.background = '#22c55e';
        statusBadge.style.color = '#ffffff';
        statusBadge.style.borderColor = '#16a34a';
    }

    // 8. Canlı Önizleme Kartını Render Et ve Aç
    renderTemplatePreview(tpl);
    const previewCard = document.getElementById('templateLivePreviewCard');
    if (previewCard) previewCard.style.display = 'block';

    if (typeof showToast === 'function') {
        showToast(`⚡ ${tpl.title} şablonu başarıyla uygulandı!`);
    }
}

function renderTemplatePreview(tpl) {
    const titleEl = document.getElementById('tplPreviewTitle');
    const descEl = document.getElementById('tplPreviewDesc');
    const codeEl = document.getElementById('tplPreviewCodeBadge');
    const kpisEl = document.getElementById('tplPreviewKpis');
    const timelineEl = document.getElementById('tplPreviewPhasesTimeline');
    const risksContainer = document.getElementById('tplPreviewRisksContainer');
    const risksList = document.getElementById('tplPreviewRisksList');

    if (titleEl) titleEl.textContent = tpl.title;
    if (descEl) descEl.textContent = tpl.description || 'Hazır kurumsal faz ve denetim şablonu.';
    if (codeEl) codeEl.textContent = tpl.code || 'TPL';

    // KPI Hesaplamaları
    const phasesCount = tpl.phases ? tpl.phases.length : 0;
    let totalTasks = 0;
    let totalEhs = 0;
    if (tpl.phases) {
        tpl.phases.forEach(p => {
            if (p.tasks) totalTasks += p.tasks.length;
            if (p.requires_ehs_approval) totalEhs++;
        });
    }
    const risksCount = tpl.risks ? tpl.risks.length : 0;
    const formattedBudget = tpl.planned_budget ? Number(tpl.planned_budget).toLocaleString('tr-TR') + ' ' + (tpl.currency || 'TRY') : '0 ₺';

    if (kpisEl) {
        kpisEl.innerHTML = `
            <span class="template-kpi-pill">📦 ${phasesCount} Hazır Faz</span>
            <span class="template-kpi-pill">🛠️ ${totalTasks} Tanımlı Görev</span>
            ${totalEhs > 0 ? `<span class="template-kpi-pill ehs">🛡️ ${totalEhs} İSG Onay Kapısı</span>` : ''}
            ${risksCount > 0 ? `<span class="template-kpi-pill risk">⚠️ ${risksCount} Öngörülen Risk</span>` : ''}
            <span class="template-kpi-pill budget">💰 ${formattedBudget} Planlanan Bütçe</span>
        `;
    }

    // Fazlar ve Görevler
    if (timelineEl) {
        if (!tpl.phases || tpl.phases.length === 0) {
            timelineEl.innerHTML = '<div class="text-xs text-muted p-2">Bu şablonda henüz faz tanımlanmamış.</div>';
        } else {
            timelineEl.innerHTML = tpl.phases.map((p, idx) => {
                const pNum = idx + 1;
                const tasksHtml = p.tasks && p.tasks.length > 0 ? 
                    p.tasks.map(t => `
                        <span class="template-task-chip">
                            <span>📌</span>
                            <strong>${escapeHtml(t.title)}</strong>
                            ${t.tag ? `<span class="badge badge-light" style="font-size: 9.5px; padding: 1px 4px; border: 1px solid #cbd5e1;">${t.tag}</span>` : ''}
                            ${t.estimated_hours ? `<span style="color: #64748b; font-size: 10px;">(${t.estimated_hours}s)</span>` : ''}
                        </span>
                    `).join('') : '<span class="text-xs text-muted" style="font-style: italic;">Standart faz (görevler süreç esnasında eklenecek)</span>';

                return `
                    <div class="template-phase-item">
                        <div class="template-phase-top">
                            <div class="template-phase-left">
                                <span class="template-phase-num">${pNum}</span>
                                <span class="template-phase-name">${escapeHtml(p.name)}</span>
                            </div>
                            <div>
                                ${p.requires_ehs_approval ? 
                                    '<span class="badge" style="background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; font-size: 10.5px;">🛡️ İSG İzni Zorunlu</span>' : 
                                    '<span class="badge" style="background: #f1f5f9; color: #475569; font-size: 10.5px;">Standart Faz</span>'}
                            </div>
                        </div>
                        <div class="template-phase-tasks-wrap">
                            ${tasksHtml}
                        </div>
                    </div>
                `;
            }).join('');
        }
    }

    // Riskler
    if (risksContainer && risksList) {
        if (tpl.risks && tpl.risks.length > 0) {
            risksContainer.style.display = 'block';
            risksList.innerHTML = tpl.risks.map(r => `
                <div style="background: #fff; border: 1px solid #fecaca; border-radius: 6px; padding: 6px 10px; display: flex; justify-content: space-between; align-items: center; font-size: 11.5px;">
                    <div>
                        <strong style="color: #991b1b;">${escapeHtml(r.title)}</strong>
                        <span style="color: #64748b; margin-left: 6px;">(${r.category || 'Teknik'})</span>
                    </div>
                    <span class="badge" style="background: #fee2e2; color: #991b1b; font-size: 10.5px;">
                        Skor: ${r.risk_score || (r.probability * r.impact) || '-'} • ${r.severity ? r.severity.toUpperCase() : 'ORTA'}
                    </span>
                </div>
            `).join('');
        } else {
            risksContainer.style.display = 'none';
        }
    }
}

function toggleTemplatePreview() {
    const card = document.getElementById('templateLivePreviewCard');
    const btn = document.getElementById('togglePreviewBtn');
    if (!card) return;
    if (card.style.display === 'none') {
        card.style.display = 'block';
        if (btn) btn.innerHTML = '<span>👁️</span> Fazları Gizle';
    } else {
        card.style.display = 'none';
        const selectEl = document.getElementById('templateSelect');
        const tpl = selectEl ? getTemplateById(selectEl.value) : null;
        const count = tpl && tpl.phases ? tpl.phases.length : 0;
        if (btn) btn.innerHTML = `<span>👁️</span> Faz & Görevleri Göster (${count})`;
    }
}

function resetTemplateSelection(clearInputs = true) {
    const selectEl = document.getElementById('templateSelect');
    if (selectEl) selectEl.value = '';
    
    const card = document.getElementById('templateLivePreviewCard');
    if (card) card.style.display = 'none';

    const togglePreviewBtn = document.getElementById('togglePreviewBtn');
    const resetBtn = document.getElementById('resetTemplateBtn');
    const statusBadge = document.getElementById('templateStatusBadge');

    if (togglePreviewBtn) togglePreviewBtn.style.display = 'none';
    if (resetBtn) resetBtn.style.display = 'none';
    if (statusBadge) {
        statusBadge.innerHTML = '⚡ Hızlı Kurulum';
        statusBadge.style.background = '#dcfce7';
        statusBadge.style.color = '#15803d';
        statusBadge.style.borderColor = '#86efac';
    }

    if (clearInputs) {
        const titleInput = document.querySelector('input[name="title"]');
        if (titleInput) titleInput.value = '';
        const budgetInput = document.querySelector('input[name="planned_budget"]');
        if (budgetInput) budgetInput.value = '0.00';
        const descTextarea = document.querySelector('textarea[name="description"]');
        if (descTextarea) descTextarea.value = '';
        selectedCategories.clear();
        renderCategoryTags();
        if (typeof showToast === 'function') {
            showToast('Şablon seçimi kaldırıldı, form temizlendi.');
        }
    }
}

// Tüm kullanıcıların veritabanı verisi
const allUsersData = @json($users->map(fn($u) => [
    'id' => $u->id, 
    'name' => $u->name, 
    'title' => $u->unvan ?? ($u->roles->first()?->name ?? 'Personel')
]));

// Dinamik Ekip Rol / Sorumluluk Seçenekleri
let responsibilityOptions = @json($teamRoles ? $teamRoles->pluck("name") : []);
if (!responsibilityOptions.length) {
    responsibilityOptions = [
        'Mekanik İmalat & Montaj Sorumlusu',
        'Elektrik & Otomasyon Mühendisi',
        'Yazılım & BT Sistem Entegratörü',
        'Saha & Şantiye Şefi',
        'İSG & Saha Güvenlik Temsilcisi',
        'Kalite Kontrol & Kabul Test Mühendisi',
        'Satın Alma & Lojistik Sorumlusu',
        'Maliyet & Bütçe Denetçisi',
        'Proje Saha Koordinatörü',
        'Proses & Verimlilik Mühendisi',
        'Diğer'
    ];
}

let teamMemberRowIndex = 0;

// =========================================================================
// 1. KATEGORİ ÇOKLU SEÇİM BİLEŞENİ (CATEGORIES MULTI-SELECT)
// =========================================================================
const selectedCategories = new Map(); // slug => {name, color}

function focusCategoriesSearch() {
    document.getElementById('categoriesSearchInput').focus();
    openCategoriesDropdown();
}

function openCategoriesDropdown() {
    document.getElementById('categoriesDropdownMenu').classList.add('show');
}

function closeCategoriesDropdown() {
    document.getElementById('categoriesDropdownMenu').classList.remove('show');
}

function handleCategoriesSearch(val) {
    openCategoriesDropdown();
    const query = val.toLowerCase().trim();
    document.querySelectorAll('.category-item').forEach(item => {
        const text = item.getAttribute('data-text').toLowerCase();
        item.style.display = text.includes(query) ? 'flex' : 'none';
    });
}

function toggleCategoryItem(slug, name, color) {
    if (selectedCategories.has(slug)) {
        selectedCategories.delete(slug);
    } else {
        selectedCategories.set(slug, { name, color: color || '#0284c7' });
    }
    renderCategoryTags();
    const searchInput = document.getElementById('categoriesSearchInput');
    if (searchInput) searchInput.value = '';
    document.querySelectorAll('.category-item').forEach(item => {
        item.style.display = 'flex';
    });
}

function removeCategoryTag(slug, event) {
    if (event) event.stopPropagation();
    selectedCategories.delete(slug);
    renderCategoryTags();
}

function renderCategoryTags() {
    window.renderCategoriesTags = renderCategoryTags;
    const container = document.getElementById('categoriesTagContainer');
    const hiddenSelect = document.getElementById('hiddenCategoriesSelect');
    const countEl = document.getElementById('selectedCategoriesCount');

    container.innerHTML = '';
    hiddenSelect.innerHTML = '';

    selectedCategories.forEach((info, slug) => {
        const chip = document.createElement('span');
        chip.className = 'category-tag-chip';
        chip.style.borderColor = info.color;
        chip.innerHTML = `
            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: ${info.color};"></span>
            <span>${info.name}</span>
            <button type="button" class="remove-tag-btn" onclick="removeCategoryTag('${slug}', event)">✕</button>
        `;
        container.appendChild(chip);

        const opt = document.createElement('option');
        opt.value = slug;
        opt.selected = true;
        hiddenSelect.appendChild(opt);
    });

    document.querySelectorAll('.category-item').forEach(item => {
        const itemId = item.getAttribute('data-id');
        const check = item.querySelector('.sms-check-indicator');
        if (selectedCategories.has(itemId)) {
            item.classList.add('selected');
            if (check) check.style.display = 'inline';
        } else {
            item.classList.remove('selected');
            if (check) check.style.display = 'none';
        }
    });

    countEl.textContent = `${selectedCategories.size} kategori seçildi`;
}

// Varsayılan olarak kapalı başlar ve kategori seçilmeden temiz gelir (Kullanıcı kendi seçer)

// =========================================================================
// 2. İLGİLİ BÖLÜMLER ÇOKLU SEÇİM BİLEŞENİ (BOLUMLER MULTI-SELECT)
// =========================================================================
const selectedBolumler = new Map(); // id => {name, code}

function focusBolumlerSearch() {
    document.getElementById('bolumlerSearchInput').focus();
    openBolumlerDropdown();
}

function openBolumlerDropdown() {
    document.getElementById('bolumlerDropdownMenu').classList.add('show');
}

function closeBolumlerDropdown() {
    document.getElementById('bolumlerDropdownMenu').classList.remove('show');
}

function handleBolumlerSearch(val) {
    openBolumlerDropdown();
    const query = val.toLowerCase().trim();
    document.querySelectorAll('.bolum-item').forEach(item => {
        const text = item.getAttribute('data-text').toLowerCase();
        item.style.display = text.includes(query) ? 'flex' : 'none';
    });
}

function toggleBolumItem(id, name, code) {
    if (selectedBolumler.has(id)) {
        selectedBolumler.delete(id);
    } else {
        selectedBolumler.set(id, { name, code });
    }
    renderBolumTags();
    document.getElementById('bolumlerSearchInput').value = '';
    handleBolumlerSearch('');
}

function removeBolumTag(id, event) {
    if (event) event.stopPropagation();
    selectedBolumler.delete(id);
    renderBolumTags();
}

function renderBolumTags() {
    const container = document.getElementById('bolumlerTagContainer');
    const hiddenSelect = document.getElementById('hiddenBolumlerSelect');
    const countEl = document.getElementById('selectedBolumlerCount');

    container.innerHTML = '';
    hiddenSelect.innerHTML = '';

    selectedBolumler.forEach((info, id) => {
        const chip = document.createElement('span');
        chip.className = 'dept-tag-chip';
        chip.innerHTML = `
            <span>🏢 ${info.name}</span>
            <button type="button" class="remove-tag-btn" onclick="removeBolumTag(${id}, event)">✕</button>
        `;
        container.appendChild(chip);

        const opt = document.createElement('option');
        opt.value = id;
        opt.selected = true;
        hiddenSelect.appendChild(opt);
    });

    document.querySelectorAll('.bolum-item').forEach(item => {
        const itemId = parseInt(item.getAttribute('data-id'));
        const check = item.querySelector('.sms-check-indicator');
        if (selectedBolumler.has(itemId)) {
            item.classList.add('selected');
            if (check) check.style.display = 'inline';
        } else {
            item.classList.remove('selected');
            if (check) check.style.display = 'none';
        }
    });

    countEl.textContent = `${selectedBolumler.size} bölüm seçildi`;
}

// =========================================================================
// 3. PROJE EKİBİ & GÖREV DAĞILIMI (LİDER/SPONSOR UYARI ROZETLİ)
// =========================================================================

function getSelectedManagerId() {
    const el = document.getElementById('hiddenManagerId') || document.getElementById('managerSelect');
    return el && el.value ? (parseInt(el.value) || 0) : 0;
}

function getSelectedSponsorId() {
    const el = document.getElementById('hiddenSponsorId') || document.getElementById('sponsorSelect');
    return el && el.value ? (parseInt(el.value) || 0) : 0;
}

function addTeamMemberRow(prefill = null) {
    const container = document.getElementById('teamMembersContainer');
    const notice = document.getElementById('noTeamMembersNotice');
    if (notice) notice.style.display = 'none';

    const index = teamMemberRowIndex++;
    const selectedManagerId = getSelectedManagerId();
    const selectedSponsorId = getSelectedSponsorId();

    let userItemsHtml = '';
    allUsersData.forEach(u => {
        let tag = '';
        if (u.id === selectedManagerId) tag = ' <span class="badge" style="background: #fef3c7; color: #b45309; font-size: 9.5px; padding: 1px 5px; border-radius: 4px;">👑 Yönetici</span>';
        else if (u.id === selectedSponsorId) tag = ' <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 9.5px; padding: 1px 5px; border-radius: 4px;">⭐ Sponsor</span>';
        
        userItemsHtml += `
            <div class="team-user-list-item" data-id="${u.id}" data-text="${escapeHtml(u.name)} ${escapeHtml(u.title)}" onclick="selectTeamUser(${index}, ${u.id}, '${escapeHtml(u.name)} (${escapeHtml(u.title)})')" style="padding: 7px 10px; border-radius: 6px; cursor: pointer; font-size: 12px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f8fafc; transition: background 0.15s;">
                <div>
                    <strong style="color: #0f172a;">${escapeHtml(u.name)}</strong>
                    <span style="color: #64748b; font-size: 11px; margin-left: 6px;">${escapeHtml(u.title)}</span>
                </div>
                <div>${tag}</div>
            </div>
        `;
    });

    let respOptionsHtml = '<option value="">-- Sorumluluk / Rol Seçiniz --</option>';
    responsibilityOptions.forEach(r => {
        respOptionsHtml += `<option value="${r}">${r}</option>`;
    });

    const row = document.createElement('div');
    row.id = `teamRow_${index}`;
    row.className = 'team-member-row mb-2';
    row.style = 'background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; display: grid; grid-template-columns: 1.2fr 1.2fr auto; gap: 12px; align-items: start;';

    row.innerHTML = `
        <div class="form-group mb-0" style="position: relative;">
            <label class="text-xs font-weight-600 mb-1" style="height: 18px; display: flex; align-items: center; color: #475569;">Ekip Personeli <span class="text-danger">*</span></label>
            <input type="hidden" name="team_members[${index}][user_id]" id="teamUserHidden_${index}" required>
            
            <div class="team-user-trigger" id="teamUserTrigger_${index}" onclick="toggleTeamUserDropdown(${index}, event)" style="height: 42px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 12px; box-sizing: border-box;">
                <span id="teamUserSelectedText_${index}" style="color: #64748b; font-size: 12.5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">-- Personel Seçiniz --</span>
                <span style="font-size: 10px; color: #94a3b8; margin-left: 8px;">▼</span>
            </div>

            <div class="team-user-menu" id="teamUserDropdown_${index}" style="display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.12); z-index: 1050; padding: 8px;" onclick="event.stopPropagation()">
                <div style="margin-bottom: 6px;">
                    <input type="text" class="form-control text-xs" placeholder="🔍 Personel ara (isim, unvan)..." id="teamUserSearch_${index}" oninput="filterTeamUserList(${index}, this.value)" style="height: 32px; font-size: 12px; background: #f8fafc;" onclick="event.stopPropagation()">
                </div>
                <div id="teamUserList_${index}" style="max-height: 180px; overflow-y: auto;">
                    ${userItemsHtml}
                </div>
            </div>
            <div id="teamLeadershipNotice_${index}"></div>
        </div>

        <div class="form-group mb-0">
            <label class="text-xs font-weight-600 mb-1" style="height: 18px; display: flex; align-items: center; color: #475569;">Projedeki Sorumluluğu / Fonksiyonu <span class="text-danger">*</span></label>
            <select name="team_members[${index}][responsibility]" id="respSelect_${index}" class="form-control text-xs" required style="height: 42px; font-size: 12.5px;" onchange="handleResponsibilityChange(${index})">
                ${respOptionsHtml}
            </select>
            <input type="text" name="team_members[${index}][custom_responsibility]" id="customRespInput_${index}" class="form-control text-xs mt-1" placeholder="Özel sorumluluk tanımını yazınız..." style="display: none; height: 32px;">
        </div>

        <div style="padding-top: 22px;">
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeTeamMemberRow(${index})" title="Kaldır" style="height: 42px; width: 42px; display: flex; align-items: center; justify-content: center; border-radius: 6px;">
                ✕
            </button>
        </div>
    `;

    container.appendChild(row);
}

function toggleTeamUserDropdown(index, event) {
    if (event) event.stopPropagation();
    // Close other team dropdowns first
    document.querySelectorAll('.team-user-menu').forEach(menu => {
        if (menu.id !== `teamUserDropdown_${index}`) menu.style.display = 'none';
    });
    const menu = document.getElementById(`teamUserDropdown_${index}`);
    if (menu) {
        const isHidden = menu.style.display === 'none';
        menu.style.display = isHidden ? 'block' : 'none';
        if (isHidden) {
            const input = document.getElementById(`teamUserSearch_${index}`);
            if (input) {
                input.value = '';
                filterTeamUserList(index, '');
                setTimeout(() => input.focus(), 50);
            }
        }
    }
}

function filterTeamUserList(index, query) {
    const q = query.toLowerCase().trim();
    const list = document.getElementById(`teamUserList_${index}`);
    if (!list) return;
    list.querySelectorAll('.team-user-list-item').forEach(item => {
        const text = (item.getAttribute('data-text') || '').toLowerCase();
        item.style.display = text.includes(q) ? 'flex' : 'none';
    });
}

function selectTeamUser(index, userId, userDisplay) {
    const hiddenInput = document.getElementById(`teamUserHidden_${index}`);
    const selectedText = document.getElementById(`teamUserSelectedText_${index}`);
    const menu = document.getElementById(`teamUserDropdown_${index}`);
    
    if (hiddenInput) hiddenInput.value = userId;
    if (selectedText) {
        selectedText.textContent = userDisplay;
        selectedText.style.color = '#0f172a';
        selectedText.style.fontWeight = '600';
    }
    if (menu) menu.style.display = 'none';

    // Check manager / sponsor leadership notice
    const selectedManagerId = getSelectedManagerId();
    const selectedSponsorId = getSelectedSponsorId();
    const noticeEl = document.getElementById(`teamLeadershipNotice_${index}`);
    if (noticeEl) {
        if (userId === selectedManagerId) {
            noticeEl.innerHTML = '<span class="badge" style="background: #fef3c7; color: #b45309; font-size: 10px; margin-top: 4px; display: inline-block;">👑 Proje Yöneticisi seçili personele ek görev verildi</span>';
        } else if (userId === selectedSponsorId) {
            noticeEl.innerHTML = '<span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 10px; margin-top: 4px; display: inline-block;">⭐ Proje Sponsoru seçili personele ek görev verildi</span>';
        } else {
            noticeEl.innerHTML = '';
        }
    }
}

function removeTeamMemberRow(index) {
    const row = document.getElementById(`teamRow_${index}`);
    if (row) row.remove();

    const container = document.getElementById('teamMembersContainer');
    const notice = document.getElementById('noTeamMembersNotice');
    if (container.children.length === 0 && notice) {
        notice.style.display = 'flex';
    }
}

function handleResponsibilityChange(index) {
    const select = document.getElementById(`respSelect_${index}`);
    const customInput = document.getElementById(`customRespInput_${index}`);
    if (select.value === 'Diğer') {
        customInput.style.display = 'block';
        customInput.required = true;
        customInput.focus();
    } else {
        customInput.style.display = 'none';
        customInput.required = false;
        customInput.value = '';
    }
}

function handleTeamUserSelectChange(index) {
    const select = document.getElementById(`teamUserSelect_${index}`);
    const noticeContainer = document.getElementById(`teamLeadershipNotice_${index}`);
    const selectedUserId = parseInt(select.value) || 0;

    const selectedManagerId = getSelectedManagerId();
    const selectedSponsorId = getSelectedSponsorId();

    noticeContainer.innerHTML = '';

    if (selectedUserId === selectedManagerId && selectedUserId !== 0) {
        noticeContainer.innerHTML = `
            <div class="team-leadership-notice manager">
                <span>👑</span>
                <span><strong>Proje Yöneticisi:</strong> Bu personel aynı zamanda projenin yöneticisidir ve bu ek sorumluluğu da üstlenecektir.</span>
            </div>
        `;
    } else if (selectedUserId === selectedSponsorId && selectedUserId !== 0) {
        noticeContainer.innerHTML = `
            <div class="team-leadership-notice sponsor">
                <span>⭐</span>
                <span><strong>Proje Sponsoru:</strong> Bu personel projenin sponsorudur ve projede bu ek sorumluluğu da üstlenecektir.</span>
            </div>
        `;
    }
}

// Liderlik değiştiğinde ekip seçimlerini silmeden sadece etiket ve uyarıları güncelle
function handleLeadershipChange() {
    const selectedManagerId = getSelectedManagerId();
    const selectedSponsorId = getSelectedSponsorId();

    document.querySelectorAll('.team-user-select').forEach(sel => {
        const rowId = sel.id.replace('teamUserSelect_', '');
        const currentVal = parseInt(sel.value) || 0;
        
        // Option etiketlerini güncelle
        Array.from(sel.options).forEach(opt => {
            const optVal = parseInt(opt.value);
            if (!optVal) return;
            const u = allUsersData.find(x => x.id === optVal);
            if (u) {
                let tag = '';
                if (u.id === selectedManagerId) tag = ' 👑 [Proje Yöneticisi]';
                else if (u.id === selectedSponsorId) tag = ' ⭐ [Proje Sponsoru]';
                opt.textContent = `${u.name} (${u.title})${tag}`;
            }
        });

        // Mevcut seçimin uyarısını güncelle
        if (rowId !== '') {
            handleTeamUserSelectChange(rowId);
        }
    });
}

// Dropdown filtreleme
function filterDropdownOptions(inputId, selectId) {
    const searchVal = document.getElementById(inputId).value.toLowerCase().trim();
    const select = document.getElementById(selectId);

    Array.from(select.options).forEach(opt => {
        if (!opt.value) return;
        const text = opt.textContent.toLowerCase();
        opt.style.display = text.includes(searchVal) ? '' : 'none';
    });
}

function filterDropdownOptionsDirect(input, selectId) {
    const searchVal = input.value.toLowerCase().trim();
    const select = document.getElementById(selectId);

    Array.from(select.options).forEach(opt => {
        if (!opt.value) return;
        const text = opt.textContent.toLowerCase();
        opt.style.display = text.includes(searchVal) ? '' : 'none';
    });
}

// =========================================================================
// 4. GÖZLEMCİLER ÇOKLU SEÇİMİ (OBSERVERS MULTI-SELECT)
// =========================================================================
const selectedObservers = new Map();

function focusSmsSearch() {
    document.getElementById('smsSearchInput').focus();
    openSmsDropdown();
}

function openSmsDropdown() {
    document.getElementById('smsDropdownMenu').classList.add('show');
}

function closeSmsDropdown() {
    document.getElementById('smsDropdownMenu').classList.remove('show');
}

function handleSmsSearch(val) {
    openSmsDropdown();
    const query = val.toLowerCase().trim();
    document.querySelectorAll('.observer-item').forEach(item => {
        const text = item.getAttribute('data-text').toLowerCase();
        item.style.display = text.includes(query) ? 'flex' : 'none';
    });
}

function toggleSmsItem(id, name) {
    if (selectedObservers.has(id)) {
        selectedObservers.delete(id);
    } else {
        selectedObservers.set(id, name);
    }
    renderSmsTags();
    document.getElementById('smsSearchInput').value = '';
    handleSmsSearch('');
}

function removeSmsTag(id, event) {
    if (event) event.stopPropagation();
    selectedObservers.delete(id);
    renderSmsTags();
}

function renderSmsTags() {
    const container = document.getElementById('smsTagContainer');
    const hiddenSelect = document.getElementById('hiddenObserversSelect');
    const countEl = document.getElementById('selectedObserversCount');
    
    container.innerHTML = '';
    hiddenSelect.innerHTML = '';

    selectedObservers.forEach((name, id) => {
        const chip = document.createElement('span');
        chip.className = 'sms-tag-chip';
        chip.innerHTML = `
            <span>${name}</span>
            <button type="button" class="remove-tag-btn" onclick="removeSmsTag(${id}, event)">✕</button>
        `;
        container.appendChild(chip);

        const opt = document.createElement('option');
        opt.value = id;
        opt.selected = true;
        hiddenSelect.appendChild(opt);
    });

    document.querySelectorAll('.observer-item').forEach(item => {
        const itemId = parseInt(item.getAttribute('data-id'));
        const check = item.querySelector('.sms-check-indicator');
        if (selectedObservers.has(itemId)) {
            item.classList.add('selected');
            if (check) check.style.display = 'inline';
        } else {
            item.classList.remove('selected');
            if (check) check.style.display = 'none';
        }
    });

    countEl.textContent = `${selectedObservers.size} kişi seçildi`;
}

// =========================================================================
// 5. HARİCİ GÖZLEMCİ DİNAMİK SATIRLARI (EXTERNAL OBSERVERS)
// =========================================================================
let externalObserverIndex = 0;

function addExternalObserverRow() {
    const container = document.getElementById('externalObserversContainer');
    const notice = document.getElementById('noExternalObserversNotice');
    if (notice) notice.style.display = 'none';

    const index = externalObserverIndex++;
    const row = document.createElement('div');
    row.id = `extRow_${index}`;
    row.className = 'external-observer-row';

    row.innerHTML = `
        <div>
            <label class="text-xs font-weight-600 mb-1" style="display: block; color: #475569;">E-Posta Adresi <span class="text-danger">*</span></label>
            <input type="email" name="external_observers[${index}][email]" class="form-control text-xs" required placeholder="ornek@dispaydas.com">
        </div>
        <div>
            <label class="text-xs font-weight-600 mb-1" style="display: block; color: #475569;">Ad Soyad (Opsiyonel)</label>
            <input type="text" name="external_observers[${index}][name]" class="form-control text-xs" placeholder="Örn: Ayşe DİNÇ">
        </div>
        <div>
            <label class="text-xs font-weight-600 mb-1" style="display: block; color: #475569;">Kurum / Temsil / Unvan</label>
            <input type="text" name="external_observers[${index}][company]" class="form-control text-xs" placeholder="Örn: ISO Denetçisi / Siemens">
        </div>
        <div style="padding-top: 18px;">
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeExternalObserverRow(${index})" title="Kaldır" style="border-radius: 6px; padding: 6px 10px;">
                ✕
            </button>
        </div>
    `;

    container.appendChild(row);
}

function removeExternalObserverRow(index) {
    const row = document.getElementById(`extRow_${index}`);
    if (row) row.remove();

    const container = document.getElementById('externalObserversContainer');
    const notice = document.getElementById('noExternalObserversNotice');
    if (container.children.length === 0 && notice) {
        notice.style.display = 'flex';
    }
}

// =========================================================================
// 6. Dışarı tıklandığında tüm dropdown'ları kapat
// =========================================================================
document.addEventListener('click', function(e) {
    const catContainer = document.getElementById('categoriesMultiSelect');
    if (catContainer && !catContainer.contains(e.target)) {
        closeCategoriesDropdown();
    }

    const bolumContainer = document.getElementById('bolumlerMultiSelect');
    if (bolumContainer && !bolumContainer.contains(e.target)) {
        closeBolumlerDropdown();
    }

    const obsContainer = document.getElementById('observersMultiSelect');
    if (obsContainer && !obsContainer.contains(e.target)) {
        closeSmsDropdown();
    }
});

// =========================================================================
// 7. Merkezi IAA Bölüm Senkronizasyonu (AJAX)
// =========================================================================
function syncCentralDepartments() {
    const btn = document.getElementById('syncBolumBtn');
    const msg = document.getElementById('syncStatusMsg');
    const dropdown = document.getElementById('bolumlerDropdownMenu');

    btn.disabled = true;
    btn.textContent = '⏳ Senkronize ediliyor...';

    fetch("{{ route('admin.sync_bolumler') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            "Accept": "application/json"
        }
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.textContent = '🔄 Bölümleri Senkronize Et';

        if (data.success) {
            dropdown.innerHTML = '';
            data.bolumler.forEach(b => {
                const item = document.createElement('div');
                item.className = 'sms-dropdown-item bolum-item';
                item.setAttribute('data-id', b.id);
                item.setAttribute('data-text', `${b.ad} ${b.kod || ''}`);
                item.onclick = function() {
                    toggleBolumItem(b.id, b.ad, b.kod || '');
                };
                item.innerHTML = `
                    <div>
                        <strong style="color: #0f172a;">${b.ad}</strong>
                        ${b.kod ? `<span style="font-size: 11px; color: #64748b; margin-left: 6px; background: #f1f5f9; padding: 1px 6px; border-radius: 4px;">${b.kod}</span>` : ''}
                    </div>
                    <span class="sms-check-indicator" style="display: ${selectedBolumler.has(b.id) ? 'inline' : 'none'}; color: #0284c7; font-weight: 700;">✓</span>
                `;
                dropdown.appendChild(item);
            });

            msg.innerHTML = `✓ Merkezden <strong>${data.count}</strong> aktif bölüm başarıyla eşitlendi (${new Date().toLocaleTimeString()}).`;
            msg.style.color = '#16a34a';
            showToast(data.message);
        } else {
            alert('Bölüm senkronizasyonu hatası: ' + data.message);
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.textContent = '🔄 Bölümleri Senkronize Et';
        console.error(err);
        alert('Sunucu iletişim hatası oluştu!');
    });
}

function showToast(msg, type = 'info') {
    let toast = document.getElementById('toastNotification');
    let text = document.getElementById('toastText');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toastNotification';
        toast.style.cssText = 'position: fixed; bottom: 24px; right: 24px; background: #0f172a; color: #fff; padding: 12px 20px; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); z-index: 99999; display: flex; align-items: center; gap: 10px; font-size: 13.5px; font-weight: 600; transition: all 0.3s;';
        toast.innerHTML = `<span style="font-size: 18px;">🔔</span><span id="toastText">${msg}</span>`;
        document.body.appendChild(toast);
        setTimeout(() => { toast.remove(); }, 3000);
        return;
    }
    if (text) text.textContent = msg;
    toast.style.display = 'flex';
    toast.style.opacity = '1';
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => { toast.style.display = 'none'; }, 300);
    }, 3000);
}

// =========================================================================
// 4. BÜTÇE YÖNETİMİ & KALEM KALEM DETAYLI KIRILIM MOTORU
// =========================================================================
let budgetItemRowIndex = 0;
window.BUDGET_CATEGORIES_CACHE = new Map();
let budgetCategories = @json(isset($budgetCategories) ? $budgetCategories->pluck("name") : []);
if (!budgetCategories || budgetCategories.length === 0) {
    budgetCategories = [
        "Kaynak ve İşçilik Maliyetleri",
        "İletişim ve Teknoloji Yatırımları",
        "İnşaat & Tesis Altyapı",
        "Makine & Donanım Teçhizatı",
        "Hammadde & Satın Alma",
        "Mühendislik & Danışmanlık",
        "İSG & Çevre Yatırımları",
        "Genel Yönetim & Diğer"
    ];
}

// Para Birimleri Motoru Veri Önbelleği
window.CURRENCIES_CACHE = new Map();
@if(isset($currencies))
@foreach($currencies as $c)
window.CURRENCIES_CACHE.set("{{ $c->code }}", {
    id: {{ $c->id }},
    code: "{{ $c->code }}",
    name: "{{ $c->name }}",
    symbol: "{{ $c->symbol }}",
    exchange_rate: {{ $c->exchange_rate }},
    is_default: {{ $c->is_default ? "true" : "false" }}
});
@endforeach
@endif

function getCurrencySymbol(code) {
    if (window.CURRENCIES_CACHE && window.CURRENCIES_CACHE.has(code)) {
        return window.CURRENCIES_CACHE.get(code).symbol || code;
    }
    if (code === "USD") return "$";
    if (code === "EUR") return "€";
    if (code === "GBP") return "£";
    return "₺";
}

function setBudgetMode(mode) {
    document.getElementById('budgetModeInput').value = mode;
    const btnSingle = document.getElementById('btnBudgetModeSingle');
    const btnDetailed = document.getElementById('btnBudgetModeDetailed');
    const singleGroup = document.getElementById('singleBudgetFieldGroup');
    const summaryGroup = document.getElementById('detailedBudgetSummaryGroup');
    const detailedSection = document.getElementById('detailedBudgetSection');

    if (mode === 'detailed') {
        btnDetailed.style.background = '#0284c7';
        btnDetailed.style.color = '#ffffff';
        btnSingle.style.background = 'transparent';
        btnSingle.style.color = '#64748b';

        singleGroup.style.display = 'none';
        summaryGroup.style.display = 'block';
        detailedSection.style.display = 'block';

        // İlk açılışta satır yoksa örnek satırları ekle
        if (document.querySelectorAll('#budgetItemsTbody tr').length === 0) {
            addBudgetItemRow('Kaynak ve İşçilik Maliyetleri', 'Mekanik Sac Kaynağı & Montaj İşçilikleri', 15000000);
            addBudgetItemRow('İletişim ve Teknoloji Yatırımları', 'SCADA, Sensör & BT Ağ Altyapısı', 8500000);
            addBudgetItemRow('İnşaat & Tesis Altyapı', 'Zemin Drenaj & Temel Kazı İmalatları', 28500000);
        }
        recalcBudgetTotal();
    } else {
        btnSingle.style.background = '#ffffff';
        btnSingle.style.color = '#0284c7';
        btnDetailed.style.background = 'transparent';
        btnDetailed.style.color = '#64748b';

        singleGroup.style.display = 'block';
        summaryGroup.style.display = 'none';
        detailedSection.style.display = 'none';
    }
}

function addBudgetItemRow(defaultCat = '', defaultTitle = '', defaultAmount = 0) {
    const tbody = document.getElementById('budgetItemsTbody');
    const idx = budgetItemRowIndex++;

    let catOptions = '';
    budgetCategories.forEach(cat => {
        const isSel = (cat === defaultCat) ? 'selected' : '';
        catOptions += `<option value="${cat}" ${isSel}>${cat}</option>`;
    });

    const formattedAmount = formatThousandsNumber(defaultAmount);

    const tr = document.createElement('tr');
    tr.id = `budgetRow_${idx}`;
    tr.style.background = '#ffffff';

    tr.innerHTML = `
        <td style="padding: 6px 10px;">
            <select name="budget_items[${idx}][category]" class="form-control text-xs" style="height: 36px; font-weight: 600;" required>
                ${catOptions}
            </select>
        </td>
        <td style="padding: 6px 10px;">
            <input type="text" name="budget_items[${idx}][title]" value="${defaultTitle}" class="form-control text-xs" placeholder="Örn: Mekanik Kaynak Malzeme Bütçesi..." required style="height: 36px;">
        </td>
        <td style="padding: 6px 10px;">
            <div style="position: relative;">
                <input type="text" inputmode="decimal" name="budget_items[${idx}][planned_amount]" value="${formattedAmount}" class="form-control text-xs budget-amount-input" placeholder="0" required style="height: 36px; font-weight: 700; text-align: right; padding-right: 12px;" oninput="handleBudgetItemInput(this)">
            </div>
        </td>
        <td style="padding: 6px 10px; text-align: center;">
            <button type="button" class="btn btn-outline-danger btn-xs" onclick="removeBudgetItemRow(${idx})" style="height: 32px; width: 32px; border-radius: 4px; padding: 0;">✕</button>
        </td>
    `;

    tbody.appendChild(tr);
    recalcBudgetTotal();
}

function removeBudgetItemRow(idx) {
    const row = document.getElementById(`budgetRow_${idx}`);
    if (row) row.remove();
    recalcBudgetTotal();
}



// =========================================================================
// OTOMATİK BİNLİK AYRACI (.) VE PARA BİRİMİ BİÇİMLENDİRME MOTORU
// =========================================================================
function formatThousandsInput(input) {
    if (!input) return;
    const oldVal = input.value;
    const oldCursor = input.selectionEnd || oldVal.length;
    const digitsBeforeCursor = (oldVal.slice(0, oldCursor).match(/\d/g) || []).length;

    let parts = oldVal.split(',');
    let intStr = parts[0].replace(/\D/g, '');
    let decStr = parts.length > 1 ? parts[1].replace(/\D/g, '').slice(0, 2) : null;

    if (intStr.length > 1 && intStr.startsWith('0')) {
        intStr = intStr.replace(/^0+/, '') || '0';
    }

    let formattedInt = intStr ? intStr.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
    let newVal = formattedInt;
    if (decStr !== null) {
        newVal += ',' + decStr;
    }

    input.value = newVal;

    if (input.setSelectionRange) {
        let newCursor = 0;
        let digitsCount = 0;
        for (let i = 0; i < newVal.length; i++) {
            if (/\d/.test(newVal[i])) {
                digitsCount++;
            }
            if (digitsCount === digitsBeforeCursor) {
                newCursor = i + 1;
                break;
            }
        }
        if (digitsBeforeCursor === 0) newCursor = 0;
        if (digitsBeforeCursor > (newVal.match(/\d/g) || []).length) newCursor = newVal.length;
        if (newVal[newCursor] === '.') newCursor++;
        input.setSelectionRange(newCursor, newCursor);
    }
}

function parseLocaleNumber(val) {
    if (val === null || val === undefined || val === '') return 0;
    let str = val.toString().trim();
    if (str.indexOf(',') !== -1) {
        str = str.replace(/\./g, '').replace(',', '.');
    } else {
        if ((str.match(/\./g) || []).length > 1 || /\.\d{3}$/.test(str)) {
            str = str.replace(/\./g, '');
        }
    }
    return parseFloat(str) || 0;
}

function formatThousandsNumber(val) {
    if (val === null || val === undefined || val === '') return '';
    let num = typeof val === 'number' ? val : parseLocaleNumber(val);
    if (isNaN(num)) return '';
    let parts = num.toString().split('.');
    let intFormatted = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    if (parts.length > 1 && parseInt(parts[1], 10) > 0) {
        let dec = parts[1].slice(0, 2);
        return intFormatted + ',' + dec;
    }
    return intFormatted;
}

function handleBudgetInput(input) {
    formatThousandsInput(input);
    syncSingleBudget(input.value);
}

function handleBudgetItemInput(input) {
    formatThousandsInput(input);
    recalcBudgetTotal();
}

function recalcBudgetTotal() {
    let sum = 0;
    document.querySelectorAll('.budget-amount-input').forEach(input => {
        const val = parseLocaleNumber(input.value);
        sum += val;
    });

    const currency = document.getElementById('projectCurrencySelect')?.value || 'TRY';
    const symbol = getCurrencySymbol(currency);
    const dec = 2;
    const formatted = symbol + ' ' + sum.toLocaleString('tr-TR', { minimumFractionDigits: dec, maximumFractionDigits: dec });

    const badge = document.getElementById('cumulativeBadge');
    const displayTop = document.getElementById('cumulativeDisplayTop');
    const mainInput = document.getElementById('plannedBudgetInput');

    if (badge) badge.textContent = formatted;
    if (displayTop) displayTop.textContent = formatted;
    if (mainInput && document.getElementById('budgetModeInput').value === 'detailed') {
        mainInput.value = formatThousandsNumber(sum);
    }
}

function syncSingleBudget(val) {
    const sum = parseLocaleNumber(val);
    const currency = document.getElementById('projectCurrencySelect')?.value || 'TRY';
    const symbol = getCurrencySymbol(currency);
    const dec = 2;
    const formatted = symbol + ' ' + sum.toLocaleString('tr-TR', { minimumFractionDigits: dec, maximumFractionDigits: dec });
    const badge = document.getElementById('cumulativeBadge');
    const displayTop = document.getElementById('cumulativeDisplayTop');
    if (badge) badge.textContent = formatted;
    if (displayTop) displayTop.textContent = formatted;
}

function updateCurrencySymbols(curr) {
    recalcBudgetTotal();
    const sumInput = document.getElementById('plannedBudgetInput');
    if (sumInput) syncSingleBudget(sumInput.value);
}

// Global click listener to close team dropdowns
document.addEventListener('click', () => {
    document.querySelectorAll('.team-user-menu').forEach(menu => {
        menu.style.display = 'none';
    });
});

// =========================================================================
// 5. GİZLİLİK DÜZEYİ & BİLDİRİM KURALLARI MOTORU
// =========================================================================
function handleVisibilityChange(mode) {
    const pub = document.getElementById('labelVisPublic');
    const priv = document.getElementById('labelVisPrivate');
    const badge = document.getElementById('privacyCurrentBadge');

    if (mode === 'public') {
        if (pub) {
            pub.style.borderColor = '#0284c7';
            pub.style.borderWidth = '1.5px';
            pub.style.background = '#f0f9ff';
            const s = pub.querySelector('strong');
            if (s) s.style.color = '#0284c7';
        }
        if (priv) {
            priv.style.borderColor = '#cbd5e1';
            priv.style.borderWidth = '1px';
            priv.style.background = '#ffffff';
            const s = priv.querySelector('strong');
            if (s) s.style.color = '#334155';
        }
        if (badge) {
            badge.style.background = '#f0f9ff';
            badge.style.color = '#0369a1';
            badge.style.borderColor = '#bae6fd';
            badge.textContent = '🌐 Genel Proje';
        }
    } else {
        if (priv) {
            priv.style.borderColor = '#dc2626';
            priv.style.borderWidth = '1.5px';
            priv.style.background = '#fff5f5';
            const s = priv.querySelector('strong');
            if (s) s.style.color = '#991b1b';
        }
        if (pub) {
            pub.style.borderColor = '#cbd5e1';
            pub.style.borderWidth = '1px';
            pub.style.background = '#ffffff';
            const s = pub.querySelector('strong');
            if (s) s.style.color = '#334155';
        }
        if (badge) {
            badge.style.background = '#fef2f2';
            badge.style.color = '#991b1b';
            badge.style.borderColor = '#fecaca';
            badge.textContent = '🔒 Özel Proje (Varsayılan)';
        }
    }
}

// =========================================================================
// 6. PROJE BAŞLATMA ÖZETİ MODALI & DOĞRULAMA
// =========================================================================
function openProjectSummaryConfirmModal() {
    // 1. Zorunlu Alan Doğrulaması: Başlık
    const titleInput = document.querySelector('input[name="title"]');
    const title = titleInput ? titleInput.value.trim() : '';
    if (!title) {
        if (typeof showToast === 'function') {
            showToast('Lütfen önce Proje Başlığı alanını doldurunuz.', 'warning');
        } else {
            alert('Lütfen önce Proje Başlığı alanını doldurunuz.');
        }
        if (titleInput) {
            titleInput.focus();
            titleInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
    }

    // 2. Proje Yöneticisi
    const hiddenMgr = document.getElementById('hiddenManagerId');
    let managerName = 'Belirtilmedi';
    const mgrBox = document.getElementById('managerSelectedBox');
    if (mgrBox && hiddenMgr && hiddenMgr.value) {
        const rawText = mgrBox.innerText || mgrBox.textContent || '';
        const cleaned = rawText.replace(/👤/g, '').replace(/Temizle|✕/g, '').trim();
        if (cleaned) managerName = cleaned;
    }

    // 3. Sponsor
    let sponsorName = 'Belirtilmedi';
    const hiddenSpn = document.getElementById('hiddenSponsorId');
    const spnBox = document.getElementById('sponsorSelectedBox');
    if (spnBox && hiddenSpn && hiddenSpn.value) {
        const rawText = spnBox.innerText || spnBox.textContent || '';
        const cleaned = rawText.replace(/⭐/g, '').replace(/Temizle|✕/g, '').trim();
        if (cleaned) sponsorName = cleaned;
    }

    // 4. Öncelik
    const prioSelect = document.querySelector('select[name="priority"]');
    const prioVal = prioSelect ? prioSelect.value : 'medium';
    const prioMap = {
        'low': { label: 'Düşük', color: '#16a34a', bg: '#f0fdf4', border: '#bbf7d0' },
        'medium': { label: 'Orta', color: '#d97706', bg: '#fffbeb', border: '#fde68a' },
        'high': { label: 'Yüksek', color: '#ea580c', bg: '#fff7ed', border: '#fed7aa' },
        'critical': { label: 'Kritik', color: '#dc2626', bg: '#fef2f2', border: '#fecaca' }
    };
    const prioInfo = prioMap[prioVal] || { label: prioVal, color: '#0284c7', bg: '#f0f9ff', border: '#bae6fd' };

    // 5. Lokasyon
    const locInput = document.querySelector('input[name="location"]');
    const locationVal = locInput && locInput.value.trim() ? locInput.value.trim() : 'Belirtilmedi';

    // 6. Kategoriler
    let categoriesHtml = '';
    if (typeof selectedCategories !== 'undefined' && selectedCategories.size > 0) {
        selectedCategories.forEach((info) => {
            categoriesHtml += `<span style="display: inline-flex; align-items: center; gap: 6px; background: #ffffff; border: 1.5px solid ${info.color || '#cbd5e1'}; border-radius: 8px; padding: 4px 10px; font-size: 12px; font-weight: 700; color: #1e293b; box-shadow: 0 1px 2px rgba(0,0,0,0.03);"><span style="width: 8px; height: 8px; border-radius: 50%; background: ${info.color || '#0284c7'};"></span>${escapeHtml(info.name)}</span>`;
        });
    } else {
        categoriesHtml = '<span style="font-size: 12px; color: #94a3b8; font-style: italic;">Seçilmedi</span>';
    }

    // 7. İlgili Bölümler
    let bolumlerHtml = '';
    if (typeof selectedBolumler !== 'undefined' && selectedBolumler.size > 0) {
        selectedBolumler.forEach((info) => {
            bolumlerHtml += `<span style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; border-radius: 8px; padding: 4px 10px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;">🏢 ${escapeHtml(info.name)}</span>`;
        });
    } else {
        bolumlerHtml = '<span style="font-size: 12px; color: #94a3b8; font-style: italic;">Seçilmedi</span>';
    }

    // 8. Ekip Üyeleri
    const teamRows = document.querySelectorAll('#teamMembersContainer .team-member-row');
    let teamNamesList = [];
    teamRows.forEach(row => {
        const userSpan = row.querySelector('[id^="teamUserSelectedText_"]');
        const roleSelect = row.querySelector('select');
        if (userSpan && userSpan.textContent && !userSpan.textContent.includes('-- Personel')) {
            const role = roleSelect && roleSelect.value ? ` (${roleSelect.value})` : '';
            teamNamesList.push(`${userSpan.textContent.trim()}${role}`);
        }
    });

    // 9. Harici Gözlemciler
    const extRows = document.querySelectorAll('#externalObserversContainer .external-observer-row');
    let extNamesList = [];
    extRows.forEach(row => {
        const emailInput = row.querySelector('input[type="email"]');
        const nameInput = row.querySelector('input[type="text"]');
        if (emailInput && emailInput.value.trim()) {
            extNamesList.push(`${nameInput && nameInput.value.trim() ? nameInput.value.trim() + ' - ' : ''}${emailInput.value.trim()}`);
        }
    });

    // 10. Bütçe & Para Birimi
    const currencySelect = document.querySelector('select[name="currency"]');
    const currency = currencySelect ? currencySelect.value : 'TRY';
    const currencySymbol = currency === 'USD' ? '$' : (currency === 'EUR' ? '€' : '₺');
    const budgetModeInput = document.getElementById('budgetModeInput');
    const isDetailed = budgetModeInput && budgetModeInput.value === 'detailed';

    let budgetDisplay = '0,00 ' + currencySymbol;
    if (isDetailed) {
        const cumEl = document.getElementById('cumulativeBadge');
        budgetDisplay = cumEl ? cumEl.textContent.trim() : '0,00 ' + currencySymbol;
    } else {
        const plannedInput = document.getElementById('plannedBudgetInput');
        const val = plannedInput ? parseLocaleNumber(plannedInput.value) : 0;
        budgetDisplay = currencySymbol + ' ' + val.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // 10B. Planlanan Başlangıç Tarihi
    const startDateInput = document.querySelector('input[name="start_date"]');
    let startDateFormatted = 'Bugün';
    let sDateObj = null;
    let durationText = '';
    if (startDateInput && startDateInput.value) {
        const sd = new Date(startDateInput.value);
        if (!isNaN(sd.getTime())) {
            sDateObj = sd;
            startDateFormatted = sd.toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
        } else {
            startDateFormatted = startDateInput.value;
        }
    }

    // 11. Hedef Bitiş Tarihi
    const targetEndDateInput = document.querySelector('input[name="target_end_date"]');
    let targetEndDateFormatted = 'Belirtilmedi';
    if (targetEndDateInput && targetEndDateInput.value) {
        const d = new Date(targetEndDateInput.value);
        if (!isNaN(d.getTime())) {
            targetEndDateFormatted = d.toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
            if (sDateObj) {
                const diffMs = d - sDateObj;
                if (diffMs >= 0) {
                    const diffDays = Math.round(diffMs / (1000 * 60 * 60 * 24)) + 1;
                    const approxMonths = (diffDays / 30.4).toFixed(1);
                    durationText = `${diffDays} Gün (~${approxMonths} Ay)`;
                }
            }
        } else {
            targetEndDateFormatted = targetEndDateInput.value;
        }
    }

    // 12. Gizlilik
    const visibilitySelect = document.querySelector('select[name="visibility"]');
    const isPrivate = visibilitySelect ? visibilitySelect.value === 'private' : true;

    // 13. Bildirimler Özeti
    let notifText = 'Tüm Kritik Uyarılar Açık';
    const notifSummary = document.getElementById('notifSummaryText');
    if (notifSummary && notifSummary.textContent) {
        notifText = notifSummary.textContent.trim();
    }

    // 14. Quill Editör Açıklaması
    const quill = window.quillProjectDescription;
    let descriptionHtml = '<span style="color: #94a3b8; font-style: italic;">Açıklama belirtilmedi.</span>';
    if (quill && quill.root.innerHTML.trim() && quill.root.innerHTML !== '<p><br></p>') {
        descriptionHtml = quill.root.innerHTML;
    }

    // 15. Ekli Dosyalar
    const pendingScopeFiles = window.selectedScopeFiles || [];

    // 16. Kilometre Taşları & Fazlar
    const milestoneRows = document.querySelectorAll('#milestonesTableBody tr');
    let milestonesList = [];
    milestoneRows.forEach(row => {
        const nameInput = row.querySelector('input[name*="[name]"]');
        const startInput = row.querySelector('input[name*="[start_date]"]');
        const dateInput = row.querySelector('input[name*="[target_date]"]');
        const critInput = row.querySelector('input[name*="[completion_criteria]"]');
        if (nameInput && nameInput.value.trim()) {
            let sDate = startInput && startInput.value ? startInput.value : '';
            if (sDate && sDate.includes('-')) {
                const sp = sDate.split('-');
                if (sp.length === 3) sDate = `${sp[2]}.${sp[1]}.${sp[0]}`;
            }
            let fDate = dateInput && dateInput.value ? dateInput.value : '';
            if (fDate && fDate.includes('-')) {
                const p = fDate.split('-');
                if (p.length === 3) fDate = `${p[2]}.${p[1]}.${p[0]}`;
            }
            milestonesList.push({
                name: nameInput.value.trim(),
                start_date: sDate || 'Belirtilmedi',
                target_date: fDate || 'Belirtilmedi',
                criteria: critInput && critInput.value.trim() ? critInput.value.trim() : 'Tamamlanma kriteri tanımlanmadı'
            });
        }
    });

    const bodyEl = document.getElementById('projectSummaryModalBody');
    if (bodyEl) {
        bodyEl.innerHTML = `
            <!-- ÜST KPI METRİK ŞERİDİ (4 KART) -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 14px; margin-bottom: 20px;">
                <div style="background: #ffffff; border: 1.5px solid #86efac; border-radius: 12px; padding: 14px 18px; box-shadow: 0 2px 6px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 14px;">
                    <div style="width: 44px; height: 44px; border-radius: 10px; background: #dcfce7; color: #15803d; display: flex; align-items: center; justify-content: center; font-size: 22px;">💰</div>
                    <div>
                        <span style="font-size: 11px; font-weight: 700; color: #166534; text-transform: uppercase; letter-spacing: 0.5px; display: block;">Toplam Yatırım Bütçesi</span>
                        <strong style="font-size: 19px; font-weight: 850; color: #15803d; display: block; line-height: 1.2;">${escapeHtml(budgetDisplay)}</strong>
                        <span style="font-size: 11px; color: #64748b;">${isDetailed ? 'Kalem Kırılımlı Plan' : 'Tek Kalem Bütçe'} • ${escapeHtml(currency)}</span>
                    </div>
                </div>

                <div style="background: #ffffff; border: 1.5px solid #bae6fd; border-radius: 12px; padding: 14px 18px; box-shadow: 0 2px 6px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 14px;">
                    <div style="width: 44px; height: 44px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 22px;">📅</div>
                    <div>
                        <span style="font-size: 11px; font-weight: 700; color: #0369a1; text-transform: uppercase; letter-spacing: 0.5px; display: block;">Planlanan Takvim & Süre</span>
                        <strong style="font-size: 14.5px; font-weight: 800; color: #0f172a; display: block; line-height: 1.25;">${escapeHtml(startDateFormatted)} ➔ ${escapeHtml(targetEndDateFormatted)}</strong>
                        <span style="font-size: 11px; color: #0284c7; font-weight: 600;">${escapeHtml(durationText ? 'Süre: ' + durationText : 'Gantt & Faz Takvimine Aktarılır')}</span>
                    </div>
                </div>

                <div style="background: #ffffff; border: 1.5px solid #fde68a; border-radius: 12px; padding: 14px 18px; box-shadow: 0 2px 6px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 14px;">
                    <div style="width: 44px; height: 44px; border-radius: 10px; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; font-size: 22px;">👑</div>
                    <div>
                        <span style="font-size: 11px; font-weight: 700; color: #92400e; text-transform: uppercase; letter-spacing: 0.5px; display: block;">Proje Yöneticisi</span>
                        <strong style="font-size: 15px; font-weight: 800; color: #0f172a; display: block; line-height: 1.2;">${escapeHtml(managerName)}</strong>
                        <span style="font-size: 11px; color: #64748b;">Sponsor: ${escapeHtml(sponsorName)}</span>
                    </div>
                </div>

                <div style="background: #ffffff; border: 1.5px solid #fed7aa; border-radius: 12px; padding: 14px 18px; box-shadow: 0 2px 6px rgba(0,0,0,0.03); display: flex; align-items: center; gap: 14px;">
                    <div style="width: 44px; height: 44px; border-radius: 10px; background: #ffedd5; color: #c2410c; display: flex; align-items: center; justify-content: center; font-size: 22px;">🚩</div>
                    <div>
                        <span style="font-size: 11px; font-weight: 700; color: #9a3412; text-transform: uppercase; letter-spacing: 0.5px; display: block;">Kilometre Taşları & Fazlar</span>
                        <strong style="font-size: 18px; font-weight: 800; color: #0f172a; display: block; line-height: 1.2;">${milestonesList.length} Planlı Faz</strong>
                        <span style="font-size: 11px; color: #64748b;">Kurumsal Phase-Gate Onay Akışı</span>
                    </div>
                </div>
            </div>

            <!-- 2 GENİŞ ANA SÜTUN (55% / 45%) -->
            <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 20px; margin-bottom: 16px;">
                <!-- SOL SÜTUN -->
                <div style="display: flex; flex-direction: column; gap: 18px;">
                    <!-- TEMEL PROJE KÜNYESİ -->
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; gap: 10px;">
                            <div>
                                <span style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px;">PROJE BAŞLIĞI</span>
                                <h3 style="font-size: 19px; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.35;">${escapeHtml(title)}</h3>
                            </div>
                            <span class="badge" style="background: ${prioInfo.bg}; color: ${prioInfo.color}; border: 1.5px solid ${prioInfo.border}; font-size: 12px; font-weight: 800; padding: 5px 12px; border-radius: 8px; white-space: nowrap;">
                                ${prioInfo.label} Öncelik
                            </span>
                        </div>

                        <div style="margin-bottom: 14px;">
                            <label style="font-size: 11.5px; color: #475569; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">Kategoriler</label>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px;">${categoriesHtml}</div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; padding-top: 14px; border-top: 1px solid #f1f5f9;">
                            <div>
                                <label style="font-size: 11.5px; color: #64748b; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 4px;">📍 Saha / Lokasyon</label>
                                <span style="font-size: 13px; color: #1e293b; font-weight: 700;">${escapeHtml(locationVal)}</span>
                            </div>
                            <div>
                                <label style="font-size: 11.5px; color: #64748b; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 6px;">🏢 İlgili Bölümler & Departmanlar</label>
                                <div style="display: flex; flex-wrap: wrap; gap: 6px;">${bolumlerHtml}</div>
                            </div>
                        </div>
                    </div>

                    <!-- PROJE AÇIKLAMASI & KAPSAM VİZYONU -->
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; flex-wrap: wrap; gap: 8px;">
                            <h4 style="margin: 0; font-size: 14px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                                <span>📝</span> Proje Açıklaması & Kapsam Vizyonu
                            </h4>
                            <div style="display: flex; align-items: center; gap: 8px; font-size: 11.5px;">
                                <span style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; padding: 2px 8px; border-radius: 6px; font-weight: 700;">
                                    ✍️ {{ auth()->user()->name }}
                                </span>
                                <span style="background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; padding: 2px 8px; border-radius: 6px;">
                                    📅 {{ now()->translatedFormat('d F Y, H:i') }}
                                </span>
                            </div>
                        </div>

                        <div style="font-size: 13.5px; color: #334155; line-height: 1.65; max-height: 220px; overflow-y: auto; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px;">
                            ${descriptionHtml}
                        </div>

                        <!-- EKLENEN KAPSAM DOKÜMANLARI -->
                        <div style="margin-top: 14px; padding-top: 12px; border-top: 1px dashed #cbd5e1;">
                            <div style="font-size: 12px; color: #475569; font-weight: 700; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                                <span>📎</span> Proje Kapsam Belgeleri & Ek Dosyalar (${pendingScopeFiles.length}):
                            </div>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                ${pendingScopeFiles.length > 0 ? pendingScopeFiles.map(f => {
                                    const ext = f.name.split('.').pop().toLowerCase();
                                    const icon = ['png','jpg','jpeg','webp'].includes(ext) ? '🖼️' : (ext === 'pdf' ? '📕' : (['xls','xlsx','xlm'].includes(ext) ? '📊' : (['doc','docx'].includes(ext) ? '📘' : '📄')));
                                    return `<span style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 4px 10px; font-size: 12px; font-weight: 600; color: #1e293b; display: inline-flex; align-items: center; gap: 6px;"><span>${icon}</span> ${escapeHtml(f.name)} <span style="font-size: 10.5px; color: #64748b;">(${formatFileSize(f.size)})</span></span>`;
                                }).join('') : '<span style="font-size: 11.5px; color: #94a3b8; font-style: italic;">Ek doküman seçilmedi.</span>'}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SAĞ SÜTUN -->
                <div style="display: flex; flex-direction: column; gap: 18px;">
                    <!-- KURUMSAL KİLOMETRE TAŞLARI & FAZ PLANI -->
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">
                            <h4 style="margin: 0; font-size: 14px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                                <span>🚩</span> Önerilen Kilometre Taşları & Faz Planı
                            </h4>
                            <span class="badge" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px;">
                                ${milestonesList.length} Faz
                            </span>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 8px; max-height: 260px; overflow-y: auto; padding-right: 4px;">
                            ${milestonesList.length > 0 ? milestonesList.map((m, idx) => `
                                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px; display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 24px; height: 24px; border-radius: 50%; background: #0284c7; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; flex-shrink: 0;">
                                            ${idx + 1}
                                        </div>
                                        <div>
                                            <strong style="font-size: 12.5px; color: #0f172a; display: block; line-height: 1.3;">${escapeHtml(m.name)}</strong>
                                            <span style="font-size: 11px; color: #64748b; display: block; margin-top: 2px;">🎯 ${escapeHtml(m.criteria)}</span>
                                        </div>
                                    </div>
                                    <div style="flex-shrink: 0; text-align: right;">
                                        <span style="font-size: 11.5px; font-weight: 700; background: #ffffff; border: 1px solid #cbd5e1; padding: 2px 7px; border-radius: 5px; color: #0284c7; white-space: nowrap;">
                                            📅 ${escapeHtml(m.start_date)} ➔ ${escapeHtml(m.target_date)}
                                        </span>
                                    </div>
                                </div>
                            `).join('') : '<span style="font-size: 12px; color: #94a3b8; font-style: italic;">Tanımlı kilometre taşı bulunmuyor.</span>'}
                        </div>
                    </div>

                    <!-- LİDERLİK, EKİP & PAYDAŞLAR -->
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">
                            <h4 style="margin: 0; font-size: 14px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                                <span>👥</span> Liderlik & Ekip Kadrosu
                            </h4>
                            <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px;">
                                ${teamNamesList.length} Personel
                            </span>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px;">
                                <label style="font-size: 10.5px; color: #64748b; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 2px;">👑 Proje Yöneticisi</label>
                                <strong style="font-size: 13px; color: #0f172a; display: block;">${escapeHtml(managerName)}</strong>
                            </div>
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px;">
                                <label style="font-size: 10.5px; color: #64748b; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 2px;">⭐ Proje Sponsoru</label>
                                <strong style="font-size: 13px; color: #0f172a; display: block;">${escapeHtml(sponsorName)}</strong>
                            </div>
                        </div>

                        <div>
                            <label style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">Atanan Ekip Kadrosu</label>
                            <div style="display: flex; flex-wrap: wrap; gap: 6px; max-height: 120px; overflow-y: auto;">
                                ${teamNamesList.length > 0 ? teamNamesList.map(name => `
                                    <span style="font-size: 11.5px; color: #334155; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 4px 10px; display: inline-flex; align-items: center; gap: 5px;">
                                        <span>👤</span> ${escapeHtml(name)}
                                    </span>
                                `).join('') : '<span style="font-size: 11.5px; color: #94a3b8; font-style: italic;">Ekip personeli atanmadı.</span>'}
                            </div>
                        </div>

                        ${extNamesList.length > 0 ? `
                            <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #f1f5f9;">
                                <label style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 4px;">Harici Gözlemciler (${extNamesList.length})</label>
                                <div style="font-size: 11.5px; color: #475569;">${extNamesList.map(e => escapeHtml(e)).join(', ')}</div>
                            </div>
                        ` : ''}
                    </div>

                    <!-- GÜVENLİK & BİLDİRİM KURALLARI -->
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 16px 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 38px; height: 38px; border-radius: 10px; background: ${isPrivate ? '#fef2f2' : '#e0f2fe'}; color: ${isPrivate ? '#dc2626' : '#0284c7'}; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                ${isPrivate ? '🔒' : '🌐'}
                            </div>
                            <div>
                                <strong style="font-size: 13px; color: #0f172a; display: block;">${isPrivate ? 'Özel / Gizli Proje' : 'Genel Proje'}</strong>
                                <span style="font-size: 11px; color: #64748b;">${isPrivate ? 'Yalnızca yetkili ekip ve yöneticiler görebilir' : 'Tüm organizasyon görebilir'}</span>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-size: 11.5px; font-weight: 700; color: #047857; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 3px 10px; border-radius: 6px;">
                                🔔 ${escapeHtml(notifText)}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ALT BİLGİLENDİRME BANNERI -->
            <div style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border: 1px solid #86efac; border-radius: 10px; padding: 12px 18px; display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 20px;">🛡️</span>
                <span style="font-size: 12px; color: #166534; line-height: 1.45;">
                    <strong>'Onayla ve Projeyi Başlat'</strong> butonuna tıkladığınızda proje resmi onay iş akışına girecek, proje yöneticisi ve paydaşlara bildirimler iletilecek ve süreç takibi aktifleşecektir. Eksik bilginiz varsa <strong>'Taslak Olarak Kaydet'</strong> butonu ile projeyi taslak durumunda tutabilirsiniz.
                </span>
            </div>
        `;
    }

    const modal = document.getElementById('projectSummaryModalBackdrop');
    if (modal) modal.style.display = 'flex';
}

function closeProjectSummaryConfirmModal() {
    const modal = document.getElementById('projectSummaryModalBackdrop');
    if (modal) modal.style.display = 'none';
}

function confirmAndSubmitProjectForm() {
    const btn = document.getElementById('btnConfirmAndSubmitProject');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 🚀 Proje Güncelleniyor...';
    }
    const form = document.getElementById('editProjectForm') || document.getElementById('createProjectForm');
    if (form) {
        form.submit();
    }
}

function applyNotificationRules() {
    const keys = [
        'phase_complete_bell', 'phase_complete_mail',
        'ehs_gate_bell', 'ehs_gate_mail',
        'delay_alert_bell', 'delay_alert_mail',
        'budget_alert_bell', 'budget_alert_mail',
        'file_upload_bell', 'file_upload_mail'
    ];

    let activeCount = 0;
    keys.forEach(k => {
        const chk = document.getElementById('modal_rule_' + k);
        const hid = document.getElementById('hidden_rule_' + k);
        if (chk && hid) {
            hid.value = chk.checked ? '1' : '0';
            if (chk.checked) activeCount++;
        }
    });

    const summary = document.getElementById('notifSummaryText');
    if (summary) {
        summary.textContent = `✓ ${activeCount} Bildirim Kuralı Aktif`;
        summary.style.color = '#047857';
    }

    closeNotificationRulesModal();
}

// =========================================================================
// 6. PROJE LİDERLİĞİ & SPONSORLUK (GÖRSEL 3 TARZI ARAMALI SEÇİM MOTORU)
// =========================================================================
// =========================================================================
// 6. PROJE LİDERLİĞİ & SPONSORLUK (GÖRSEL 3 TARZI ARAMALI SEÇİM MOTORU)
// =========================================================================
function openManagerDropdown(event) {
    if (event) event.stopPropagation();
    const menu = document.getElementById('managerDropdownMenu');
    document.querySelectorAll('#sponsorDropdownMenu, .team-user-menu').forEach(m => m.style.display = 'none');
    menu.style.display = 'block';
    const input = document.getElementById('managerSearchInput');
    if (input) {
        input.focus();
    }
}

function filterManagerDropdown(q) {
    const query = (q || '').toLowerCase().trim();
    document.querySelectorAll('.manager-item').forEach(item => {
        const text = (item.getAttribute('data-text') || '').toLowerCase();
        item.style.display = text.includes(query) ? 'flex' : 'none';
    });
}

function selectManager(id, name, unvan) {
    document.getElementById('hiddenManagerId').value = id;
    const box = document.getElementById('managerSelectedBox');
    box.innerHTML = `
        <div style="display: inline-flex; align-items: center; gap: 6px; background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 13px;">
            <span>👑 ${name}</span>
            <span style="font-size: 11.5px; color: #0284c7; font-weight: normal;">(${unvan || 'Uzman'})</span>
        </div>
    `;
    document.getElementById('managerClearBtn').style.display = 'block';
    document.getElementById('managerDropdownMenu').style.display = 'none';
    document.querySelectorAll('.manager-check-icon').forEach(el => el.style.display = 'none');
    const chk = document.getElementById('mgrCheck_' + id);
    if (chk) chk.style.display = 'inline';
    handleLeadershipChange();
}

function clearManagerSelection(event) {
    if (event) event.stopPropagation();
    document.getElementById('hiddenManagerId').value = '';
    const box = document.getElementById('managerSelectedBox');
    box.innerHTML = `<input type="text" id="managerSearchInput" class="sms-search-input" placeholder="🔍 Personel ara (isim, unvan)..." oninput="filterManagerDropdown(this.value)" onfocus="openManagerDropdown(event)" style="width: 100%; border: none; outline: none; font-size: 13px; background: transparent;">`;
    document.getElementById('managerClearBtn').style.display = 'none';
    document.querySelectorAll('.manager-check-icon').forEach(el => el.style.display = 'none');
    handleLeadershipChange();
}

function openSponsorDropdown(event) {
    if (event) event.stopPropagation();
    const menu = document.getElementById('sponsorDropdownMenu');
    document.querySelectorAll('#managerDropdownMenu, .team-user-menu').forEach(m => m.style.display = 'none');
    menu.style.display = 'block';
    const input = document.getElementById('sponsorSearchInput');
    if (input) {
        input.focus();
    }
}

function filterSponsorDropdown(q) {
    const query = (q || '').toLowerCase().trim();
    document.querySelectorAll('.sponsor-item').forEach(item => {
        const text = (item.getAttribute('data-text') || '').toLowerCase();
        item.style.display = text.includes(query) ? 'flex' : 'none';
    });
}

function selectSponsor(id, name, unvan) {
    document.getElementById('hiddenSponsorId').value = id;
    const box = document.getElementById('sponsorSelectedBox');
    box.innerHTML = `
        <div style="display: inline-flex; align-items: center; gap: 6px; background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 13px;">
            <span>⭐ ${name}</span>
            <span style="font-size: 11.5px; color: #b45309; font-weight: normal;">(${unvan || 'Yönetici'})</span>
        </div>
    `;
    document.getElementById('sponsorClearBtn').style.display = 'block';
    document.getElementById('sponsorDropdownMenu').style.display = 'none';
    document.querySelectorAll('.sponsor-check-icon').forEach(el => el.style.display = 'none');
    const chk = document.getElementById('spnCheck_' + id);
    if (chk) chk.style.display = 'inline';
    handleLeadershipChange();
}

function clearSponsorSelection(event) {
    if (event) event.stopPropagation();
    document.getElementById('hiddenSponsorId').value = '';
    const box = document.getElementById('sponsorSelectedBox');
    box.innerHTML = `<input type="text" id="sponsorSearchInput" class="sms-search-input" placeholder="🔍 Sponsor ara (isim, unvan, direktör)..." oninput="filterSponsorDropdown(this.value)" onfocus="openSponsorDropdown(event)" style="width: 100%; border: none; outline: none; font-size: 13px; background: transparent;">`;
    document.getElementById('sponsorClearBtn').style.display = 'none';
    document.querySelectorAll('.sponsor-check-icon').forEach(el => el.style.display = 'none');
    handleLeadershipChange();
}

// Global click to close manager & sponsor dropdowns
document.addEventListener('click', function(e) {
    const mCont = document.getElementById('managerSearchContainer');
    const sCont = document.getElementById('sponsorSearchContainer');
    if (mCont && !mCont.contains(e.target)) {
        const m = document.getElementById('managerDropdownMenu');
        if (m) m.style.display = 'none';
    }
    if (sCont && !sCont.contains(e.target)) {
        const s = document.getElementById('sponsorDropdownMenu');
        if (s) s.style.display = 'none';
    }
});

// =========================================================================
// 7. KATEGORİ YÖNETİMİ & CRUD MOTORU (DÜZENLE, SİL, SOFT-DELETE)
// =========================================================================
// 7. KATEGOR? Y?NET?M? & CRUD MOTORU (D?ZENLE, S?L, SOFT-DELETE)
// =========================================================================
// =========================================================================
// 7. KATEGORİ YÖNETİMİ & CRUD MOTORU (DÜZENLE, SİL, SOFT-DELETE)
// =========================================================================

// =========================================================================
// PROJE BİLDİRİM & ALARM KURALLARI MODALI MOTORU
// =========================================================================
function openNotificationRulesModal() {
    const modal = document.getElementById('notificationRulesModal');
    if (modal) {
        syncNotificationInputsToModal();
        modal.style.display = 'flex';
    }
}

function closeNotificationRulesModal() {
    const modal = document.getElementById('notificationRulesModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function syncNotificationInputsToModal() {
    const rules = ['phase_complete', 'ehs_gate', 'delay_alert', 'budget_alert', 'file_upload'];
    rules.forEach(rule => {
        ['bell', 'mail'].forEach(ch => {
            const hidden = document.getElementById('hidden_rule_' + rule + '_' + ch);
            const chk = document.getElementById('modal_rule_' + rule + '_' + ch);
            if (hidden && chk) {
                chk.checked = (hidden.value === '1');
            }
        });
    });
}

function applyNotificationRules() {
    const rules = ['phase_complete', 'ehs_gate', 'delay_alert', 'budget_alert', 'file_upload'];
    let activeCount = 0;

    rules.forEach(rule => {
        let isAnyActive = false;
        ['bell', 'mail'].forEach(ch => {
            const hidden = document.getElementById('hidden_rule_' + rule + '_' + ch);
            const chk = document.getElementById('modal_rule_' + rule + '_' + ch);
            if (hidden && chk) {
                hidden.value = chk.checked ? '1' : '0';
                if (chk.checked) isAnyActive = true;
            }
        });
        if (isAnyActive) activeCount++;
    });

    const summaryText = document.getElementById('notifSummaryText');
    if (summaryText) {
        if (activeCount > 0) {
            summaryText.textContent = '✓ ' + activeCount + ' Kural Yapılandırıldı & Aktif';
            summaryText.style.color = '#0284c7';
        } else {
            summaryText.textContent = '⚠️ Bildirim Kuralları Kapalı';
            summaryText.style.color = '#64748b';
        }
    }

    closeNotificationRulesModal();
}

window.CATEGORIES_CACHE = new Map();

function openCategoryModal() {
    const modal = document.getElementById('categoryModalBackdrop');
    if (modal) modal.style.display = 'flex';
    resetCategoryForm();
    loadCategoriesList();
}

function closeCategoryModal() {
    const modal = document.getElementById('categoryModalBackdrop');
    if (modal) modal.style.display = 'none';
}

function resetCategoryForm() {
    document.getElementById('editCatId').value = '';
    document.getElementById('newCatName').value = '';
    document.getElementById('newCatColor').value = '#0284c7';
    document.getElementById('newCatColorHex').value = '#0284c7';
    document.getElementById('newCatDesc').value = '';
    document.getElementById('catFormHeaderTitle').textContent = '➕ Yeni Proje Kategorisi Ekle';
    document.getElementById('btnSaveCategory').textContent = '✓ Kategoriyi Kaydet';
    document.getElementById('btnCancelEditCat').style.display = 'none';
}

function loadCategoriesList() {
    const tbody = document.getElementById('categoryModalTableBody');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; padding: 16px; color: #64748b;">Kategoriler yükleniyor...</td></tr>';

    fetch('{{ route("admin.categories.index") }}', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;
        window.CATEGORIES_CACHE.clear();
        tbody.innerHTML = '';
        if (!data.categories || data.categories.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; padding: 16px; color: #94a3b8;">Henüz kayıtlı kategori bulunmuyor.</td></tr>';
            return;
        }

        data.categories.forEach(cat => {
            window.CATEGORIES_CACHE.set(Number(cat.id), cat);
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid #f1f5f9';
            tr.id = 'cat-row-' + cat.id;
            const projCount = parseInt(cat.projects_count) || 0;
            const projBadge = projCount > 0 
                ? `<span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 700;">${projCount} Proje Bağlı</span>`
                : `<span class="badge" style="background: #f1f5f9; color: #64748b;">0 Proje</span>`;

            tr.innerHTML = `
                <td style="padding: 10px 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="width: 10px; height: 10px; border-radius: 50%; background: ${cat.color || '#0284c7'}; flex-shrink: 0;"></span>
                        <strong style="color: #0f172a; font-size: 12.5px;">${escapeHtml(cat.name)}</strong>
                    </div>
                    <span style="font-size: 11px; color: #94a3b8; margin-left: 18px;">${cat.slug}</span>
                </td>
                <td style="padding: 10px 12px;">${projBadge}</td>
                <td style="padding: 10px 12px; text-align: right; white-space: nowrap;">
                    <div style="display: inline-flex; align-items: center; justify-content: flex-end; gap: 6px;">
                        <button type="button" class="btn btn-xs btn-outline-primary" onclick="editCategoryItem(${cat.id})" style="padding: 4px 9px; font-weight: 600; border-radius: 5px; cursor: pointer;">
                            ✏️ Düzenle
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-danger" id="btn-del-cat-${cat.id}" onclick="deleteCategoryItem(${cat.id})" style="padding: 4px 9px; font-weight: 600; border-radius: 5px; cursor: pointer; color: #dc2626; border-color: #fca5a5;">
                            🗑️ Sil
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    })
    .catch(err => {
        console.error('Kategoriler yüklenirken hata:', err);
        tbody.innerHTML = '<tr><td colspan="3" style="text-align:center; padding: 16px; color: #dc2626;">Kategoriler yüklenemedi.</td></tr>';
    });
}

function editCategoryItem(id) {
    const cat = window.CATEGORIES_CACHE.get(Number(id));
    if (!cat) return;
    document.getElementById('editCatId').value = cat.id;
    document.getElementById('newCatName').value = cat.name;
    document.getElementById('newCatColor').value = cat.color;
    document.getElementById('newCatColorHex').value = cat.color;
    document.getElementById('newCatDesc').value = cat.description || '';
    document.getElementById('catFormHeaderTitle').textContent = `✏️ '${cat.name}' Kategorisini Düzenle`;
    document.getElementById('btnSaveCategory').textContent = '✓ Güncellemeyi Kaydet';
    document.getElementById('btnCancelEditCat').style.display = 'inline';
    document.getElementById('newCatName').focus();
}

function saveCategoryModalAction() {
    const editId = document.getElementById('editCatId').value;
    const name = document.getElementById('newCatName').value.trim();
    const color = document.getElementById('newCatColor').value.trim();
    const desc = document.getElementById('newCatDesc').value.trim();

    if (!name) {
        alert('Lütfen kategori adını giriniz.');
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    const url = editId ? `/admin/kategoriler/${editId}` : '{{ route("admin.categories.store") }}';
    const method = editId ? 'PUT' : 'POST';

    const saveBtn = document.getElementById('btnSaveCategory');
    const originalText = saveBtn.textContent;
    saveBtn.textContent = 'Kaydediliyor...';
    saveBtn.disabled = true;

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ name, color, description: desc, _token: csrfToken })
    })
    .then(r => r.json())
    .then(res => {
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
        if (res.success) {
            resetCategoryForm();
            loadCategoriesList();
            refreshMainPageCategories();
        } else {
            alert(res.message || 'Hata oluştu.');
        }
    })
    .catch(e => {
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
        alert('İşlem sırasında sunucu hatası oluştu.');
    });
}

function deleteCategoryItem(id) {
    const cat = window.CATEGORIES_CACHE.get(Number(id));
    const catName = cat ? cat.name : "Seçili kategori";
    const count = cat ? (parseInt(cat.projects_count) || 0) : 0;

    let details = "";
    if (count > 0) {
        details = `⚠️ DİKKAT: '${catName}' kategorisine bağlı ${count} adet proje bulunmaktadır! Bu kategori silindiğinde veritabanından kalıcı olarak silinmeyecek, GÜVENLİ ŞEKİLDE ARŞİVE ALINACAKTIR (Soft Delete). Eski projelerdeki geçmişi korunur fakat yeni proje oluştururken listelenmez.`;
    } else {
        details = `'${catName}' kategorisine bağlı hiçbir proje bulunmamaktadır. Kategori veritabanından kalıcı olarak silinecektir.`;
    }

    showAppConfirmModal({
        title: "Kategoriyi Sil",
        message: `'${catName}' kategorisini silmek istediğinize emin misiniz?`,
        details: details,
        confirmButtonText: "Evet, Güvenle Sil",
        onConfirm: function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            fetch('{{ url("admin/kategoriler") }}/' + id, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ _token: csrfToken, _method: 'DELETE' })
            })
            .then(async (r) => {
                if (!r.ok) {
                    const fallbackRes = await fetch('{{ url("admin/kategoriler") }}/' + id + '/delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ _token: csrfToken })
                    });
                    return fallbackRes.json();
                }
                return r.json();
            })
            .then(res => {
                closeAppConfirmDeleteModal();
                if (res.success) {
                    loadCategoriesList();
                    refreshMainPageCategories();
                } else {
                    alert(res.message || "Silme işlemi başarısız.");
                }
            })
            .catch(err => {
                closeAppConfirmDeleteModal();
                alert("Silme işlemi sırasında sunucu hatası oluştu: " + (err.message || err));
            });
        }
    });
}

function refreshMainPageCategories() {
    fetch('{{ route("admin.categories.index") }}')
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            const dropdownMenu = document.getElementById('categoriesDropdownMenu');
            if (!dropdownMenu) return;
            dropdownMenu.innerHTML = '';
            data.categories.forEach(cat => {
                const isSelected = selectedCategories.has(cat.slug);
                const item = document.createElement('div');
                item.className = 'sms-dropdown-item category-item';
                item.setAttribute('data-slug', cat.slug);
                item.setAttribute('data-text', cat.name.toLowerCase());
                item.onclick = () => toggleCategoryItem(cat.slug, cat.name, cat.color);
                item.style.background = isSelected ? '#f0f9ff' : 'transparent';
                item.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background-color: ${cat.color};"></span>
                        <strong style="color: #0f172a;">${escapeHtml(cat.name)}</strong>
                    </div>
                    <span class="sms-check-indicator" style="display: ${isSelected ? 'inline' : 'none'}; color: #0284c7; font-weight: 700;">?</span>
                `;
                dropdownMenu.appendChild(item);
            });
        })
        .catch(err => console.error('Kategori listesi yenilenirken hata:', err));
}

// =========================================================================
// 8. EK?P SORUMLULUK & ROLLER? Y?NET?M? (DEVELOPER / ADM?N MOTORU)
// =========================================================================
window.TEAM_ROLES_CACHE = new Map();

function openTeamRolesModal() {
    const modal = document.getElementById('teamRolesModalBackdrop');
    if (modal) modal.style.display = 'flex';
    resetRoleForm();
    loadTeamRolesList();
}

function closeTeamRolesModal() {
    const modal = document.getElementById('teamRolesModalBackdrop');
    if (modal) modal.style.display = 'none';
}

function resetRoleForm() {
    document.getElementById('editRoleId').value = '';
    document.getElementById('newRoleName').value = '';
    document.getElementById('newRoleDesc').value = '';
    document.getElementById('roleFormHeaderTitle').textContent = '+ Yeni Ekip Sorumluluğu / Rolü Ekle';
    document.getElementById('btnSaveRole').textContent = '✓ Rolü Kaydet';
    document.getElementById('btnCancelEditRole').style.display = 'none';
}

function loadTeamRolesList() {
    fetch('{{ route("admin.team_roles.index") }}')
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            window.TEAM_ROLES_CACHE.clear();
            responsibilityOptions = data.roles.map(r => r.name);
            const tbody = document.getElementById('teamRolesModalTableBody');
            tbody.innerHTML = '';
            data.roles.forEach(r => {
                window.TEAM_ROLES_CACHE.set(Number(r.id), r);
                const tr = document.createElement('tr');
                tr.style.borderBottom = '1px solid #f1f5f9';
                tr.innerHTML = `
                    <td style="padding: 10px 12px;">
                        <strong style="color: #0f172a;">${escapeHtml(r.name)}</strong>
                        ${r.description ? `<div style="font-size: 11px; color: #64748b;">${escapeHtml(r.description)}</div>` : ''}
                    </td>
                    <td style="padding: 10px 12px; text-align: right; white-space: nowrap;">
                        <div style="display: inline-flex; align-items: center; justify-content: flex-end; gap: 6px;">
                            <button type="button" class="btn btn-xs btn-outline-primary" onclick="editRoleItem(${r.id})" style="padding: 4px 9px; font-weight: 600; border-radius: 5px; cursor: pointer;">✏️ Düzenle</button>
                            <button type="button" class="btn btn-xs btn-outline-danger" onclick="deleteRoleItem(${r.id})" style="padding: 4px 9px; font-weight: 600; border-radius: 5px; cursor: pointer; color: #dc2626; border-color: #fca5a5;">🗑️ Sil</button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
            refreshTeamRoleSelects();
        })
        .catch(err => console.error('Roller y?klenirken hata:', err));
}

function editRoleItem(id) {
    const r = window.TEAM_ROLES_CACHE.get(Number(id));
    if (!r) return;
    document.getElementById('editRoleId').value = r.id;
    document.getElementById('newRoleName').value = r.name;
    document.getElementById('newRoleDesc').value = r.description || '';
    document.getElementById('roleFormHeaderTitle').textContent = `?? '${r.name}' Rol?n? Düzenle`;
    document.getElementById('btnSaveRole').textContent = '✓ Güncellemeyi Kaydet';
    document.getElementById('btnCancelEditRole').style.display = 'inline';
    document.getElementById('newRoleName').focus();
}

function saveTeamRoleAction() {
    const editId = document.getElementById('editRoleId').value;
    const name = document.getElementById('newRoleName').value.trim();
    const desc = document.getElementById('newRoleDesc').value.trim();

    if (!name) {
        alert('L?tfen rol ad?n? giriniz.');
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    const url = editId ? `/admin/team-roles/${editId}` : '{{ route("admin.team_roles.store") }}';
    const method = editId ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ name, description: desc, _token: csrfToken })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            resetRoleForm();
            loadTeamRolesList();
        } else {
            alert(res.message || 'Hata oluştu.');
        }
    })
    .catch(e => alert('İşlem sırasında sunucu hatası oluştu.'));
}

function deleteRoleItem(id) {
    const r = window.TEAM_ROLES_CACHE.get(Number(id));
    const roleName = r ? r.name : "Seçili rol";

    showAppConfirmModal({
        title: "Ekip Rolünü Sil",
        message: `'${roleName}' sorumluluk rolünü silmek istediğinize emin misiniz?`,
        details: "Bu ekip fonksiyonu silindiğinde mevcut sorumluluk seçeneklerinden kaldırılır. Eski projelerdeki geçmiş unvanlar korunur.",
        confirmButtonText: "Evet, Rolü Sil",
        onConfirm: function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            fetch('{{ url("admin/team-roles") }}/' + id, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ _token: csrfToken, _method: 'DELETE' })
            })
            .then(async (res) => {
                if (!res.ok) {
                    const fallback = await fetch('{{ url("admin/team-roles") }}/' + id + '/delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ _token: csrfToken })
                    });
                    return fallback.json();
                }
                return res.json();
            })
            .then(data => {
                closeAppConfirmDeleteModal();
                if (data.success) {
                    loadTeamRolesList();
                } else {
                    alert(data.message || "Silme işlemi başarısız.");
                }
            })
            .catch(err => {
                closeAppConfirmDeleteModal();
                alert("Silme sırasında hata oluştu: " + (err.message || err));
            });
        }
    });
}

function refreshTeamRoleSelects() {
    document.querySelectorAll('.team-member-row select[id^="respSelect_"]').forEach(sel => {
        const curVal = sel.value;
        let html = '<option value="">-- Sorumluluk / Rol Se?iniz --</option>';
        responsibilityOptions.forEach(r => {
            const isSel = (r === curVal) ? 'selected' : '';
            html += `<option value="${escapeHtml(r)}" ${isSel}>${escapeHtml(r)}</option>`;
        });
        sel.innerHTML = html;
    });
}

// =========================================================================
// 9. HARCAMA KATEGORİLERİ YÖNETİMİ & CRUD MOTORU (BÜTÇE KIRILIMI DİNAMİK)
// =========================================================================
function openBudgetCategoryModal() {
    const modal = document.getElementById('budgetCategoryModalBackdrop');
    if (modal) modal.style.display = 'flex';
    resetBudgetCategoryForm();
    loadBudgetCategoriesList();
}

function closeBudgetCategoryModal() {
    const modal = document.getElementById('budgetCategoryModalBackdrop');
    if (modal) modal.style.display = 'none';
}

function resetBudgetCategoryForm() {
    document.getElementById('editBudgetCatId').value = '';
    document.getElementById('newBudgetCatName').value = '';
    document.getElementById('newBudgetCatColor').value = '#0284c7';
    document.getElementById('newBudgetCatColorHex').value = '#0284c7';
    document.getElementById('newBudgetCatDesc').value = '';
    document.getElementById('budgetCatFormHeaderTitle').textContent = '➕ Yeni Harcama Kategorisi Ekle';
    document.getElementById('btnSaveBudgetCat').textContent = '✓ Harcama Kategorisini Kaydet';
    document.getElementById('btnCancelEditBudgetCat').style.display = 'none';
}

function loadBudgetCategoriesList() {
    fetch('{{ route("admin.budget_categories.index") }}', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;
        window.BUDGET_CATEGORIES_CACHE.clear();
        budgetCategories = data.categories.map(c => c.name);
        const tbody = document.getElementById('budgetCategoryModalTableBody');
        if (!tbody) return;
        tbody.innerHTML = '';
        data.categories.forEach(cat => {
            window.BUDGET_CATEGORIES_CACHE.set(Number(cat.id), cat);
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid #f1f5f9';
            tr.innerHTML = `
                <td style="padding: 10px 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="width: 10px; height: 10px; border-radius: 50%; background: ${cat.color || '#0284c7'}; flex-shrink: 0;"></span>
                        <strong style="color: #0f172a; font-size: 12.5px;">${escapeHtml(cat.name)}</strong>
                    </div>
                    ${cat.description ? `<div style="font-size: 11px; color: #64748b; margin-left: 18px;">${escapeHtml(cat.description)}</div>` : ''}
                </td>
                <td style="padding: 10px 12px; text-align: right; white-space: nowrap;">
                    <div style="display: inline-flex; align-items: center; justify-content: flex-end; gap: 6px;">
                        <button type="button" class="btn btn-xs btn-outline-primary" onclick="editBudgetCatItem(${cat.id})" style="padding: 4px 9px; font-weight: 600; border-radius: 5px; cursor: pointer;">✏️ Düzenle</button>
                        <button type="button" class="btn btn-xs btn-outline-danger" onclick="deleteBudgetCategoryItem(${cat.id})" style="padding: 4px 9px; font-weight: 600; border-radius: 5px; cursor: pointer; color: #dc2626; border-color: #fca5a5;">🗑️ Sil</button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
        refreshBudgetCategoryDropdowns();
    })
    .catch(err => console.error('Bütçe kategorileri yüklenirken hata:', err));
}

function editBudgetCatItem(id) {
    const cat = window.BUDGET_CATEGORIES_CACHE.get(Number(id));
    if (!cat) return;
    document.getElementById('editBudgetCatId').value = cat.id;
    document.getElementById('newBudgetCatName').value = cat.name;
    document.getElementById('newBudgetCatColor').value = cat.color || '#0284c7';
    document.getElementById('newBudgetCatColorHex').value = cat.color || '#0284c7';
    document.getElementById('newBudgetCatDesc').value = cat.description || '';
    document.getElementById('budgetCatFormHeaderTitle').textContent = `✏️ '${cat.name}' Kategorisini Düzenle`;
    document.getElementById('btnSaveBudgetCat').textContent = '✓ Güncellemeyi Kaydet';
    document.getElementById('btnCancelEditBudgetCat').style.display = 'inline';
    document.getElementById('newBudgetCatName').focus();
}

function saveBudgetCategoryModalAction() {
    const editId = document.getElementById('editBudgetCatId').value;
    const name = document.getElementById('newBudgetCatName').value.trim();
    const color = document.getElementById('newBudgetCatColor').value;
    const desc = document.getElementById('newBudgetCatDesc').value.trim();

    if (!name) {
        alert('Lütfen kategori adını giriniz.');
        document.getElementById('newBudgetCatName').focus();
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    const isEdit = Boolean(editId);
    const url = isEdit ? `{{ url('admin/budget-categories') }}/${editId}` : `{{ route('admin.budget_categories.store') }}`;
    const method = isEdit ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ name, color, description: desc, _token: csrfToken })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            resetBudgetCategoryForm();
            loadBudgetCategoriesList();
        } else {
            alert(res.message || 'Hata oluştu.');
        }
    })
    .catch(e => alert('İşlem sırasında sunucu hatası oluştu.'));
}

function deleteBudgetCategoryItem(id) {
    const cat = window.BUDGET_CATEGORIES_CACHE.get(Number(id));
    const catName = cat ? cat.name : 'Seçili kategori';

    showAppConfirmModal({
        title: 'Harcama Kategorisini Sil',
        message: `'${catName}' kategorisini silmek istediğinize emin misiniz?`,
        details: 'Bu kategori bütçe kırılım listesinden kaldırılacaktır. Mevcut projelerdeki bütçe kayıtları güvenle korunur.',
        confirmButtonText: 'Evet, Kategoriyi Sil',
        onConfirm: function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            fetch('{{ url("admin/budget-categories") }}/' + id, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ _token: csrfToken, _method: 'DELETE' })
            })
            .then(async (r) => {
                if (!r.ok) {
                    const fallback = await fetch('{{ url("admin/budget-categories") }}/' + id + '/delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ _token: csrfToken })
                    });
                    return fallback.json();
                }
                return r.json();
            })
            .then(data => {
                closeAppConfirmDeleteModal();
                if (data.success) {
                    loadBudgetCategoriesList();
                } else {
                    alert(data.message || 'Silme işlemi başarısız.');
                }
            })
            .catch(err => {
                closeAppConfirmDeleteModal();
                alert('Silme sırasında hata oluştu: ' + (err.message || err));
            });
        }
    });
}

function refreshBudgetCategoryDropdowns() {
    document.querySelectorAll('#budgetItemsTbody select[name$="[category]"]').forEach(select => {
        const currentVal = select.value;
        let catOptions = '';
        budgetCategories.forEach(cat => {
            const isSel = (cat === currentVal) ? 'selected' : '';
            catOptions += `<option value="${escapeHtml(cat)}" ${isSel}>${escapeHtml(cat)}</option>`;
        });
        select.innerHTML = catOptions;
    });
}

// =========================================================================
// 10. PARA BİRİMLERİ YÖNETİMİ & CRUD MOTORU (DİNAMİK PARA BİRİMİ YÖNETİMİ)
// =========================================================================
function openCurrencyModal() {
    const modal = document.getElementById('currencyModalBackdrop');
    if (modal) modal.style.display = 'flex';
    resetCurrencyForm();
    loadCurrenciesList();
}

function closeCurrencyModal() {
    const modal = document.getElementById('currencyModalBackdrop');
    if (modal) modal.style.display = 'none';
}

function resetCurrencyForm() {
    document.getElementById('editCurrencyId').value = '';
    document.getElementById('newCurrencyCode').value = '';
    document.getElementById('newCurrencyName').value = '';
    document.getElementById('newCurrencySymbol').value = '';
    document.getElementById('newCurrencyRate').value = '1.0000';
    document.getElementById('newCurrencyIsDefault').checked = false;
    document.getElementById('currencyFormHeaderTitle').textContent = '➕ Yeni Para Birimi Ekle';
    document.getElementById('btnSaveCurrency').textContent = '✓ Para Birimini Kaydet';
    document.getElementById('btnCancelEditCurrency').style.display = 'none';
}

function loadCurrenciesList() {
    fetch('{{ route("admin.currencies.index") }}', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;
        window.CURRENCIES_CACHE.clear();
        const tbody = document.getElementById('currencyModalTableBody');
        if (!tbody) return;
        tbody.innerHTML = '';
        data.currencies.forEach(cur => {
            window.CURRENCIES_CACHE.set(cur.code, cur);
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid #f1f5f9';
            const defaultBadge = cur.is_default 
                ? '<span class="badge" style="background: #dcfce7; color: #166534; font-weight: 700; font-size: 10px; margin-left: 6px;">Varsayılan</span>' 
                : '';
            tr.innerHTML = `
                <td style="padding: 10px 12px;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="font-weight: 800; font-size: 13px; color: #0f172a; font-family: monospace;">${escapeHtml(cur.code)}</span>
                        <span style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-weight: 700; color: #475569;">${escapeHtml(cur.symbol)}</span>
                        ${defaultBadge}
                    </div>
                </td>
                <td style="padding: 10px 12px;">
                    <strong style="color: #334155; font-size: 12.5px;">${escapeHtml(cur.name)}</strong>
                    <div style="font-size: 10.5px; color: #94a3b8;">Kur: ${parseFloat(cur.exchange_rate).toFixed(4)}</div>
                </td>
                <td style="padding: 10px 12px; text-align: right; white-space: nowrap;">
                    <div style="display: inline-flex; align-items: center; justify-content: flex-end; gap: 6px;">
                        <button type="button" class="btn btn-xs btn-outline-primary" onclick="editCurrencyItem(${cur.id})" style="padding: 4px 9px; font-weight: 600; border-radius: 5px; cursor: pointer;">✏️ Düzenle</button>
                        ${cur.is_default ? '' : `<button type="button" class="btn btn-xs btn-outline-danger" onclick="deleteCurrencyItem(${cur.id})" style="padding: 4px 9px; font-weight: 600; border-radius: 5px; cursor: pointer; color: #dc2626; border-color: #fca5a5;">🗑️ Sil</button>`}
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
        refreshCurrencySelectOptions(data.currencies);
    })
    .catch(err => console.error('Para birimleri yüklenirken hata:', err));
}

function refreshCurrencySelectOptions(currenciesList) {
    const sel = document.getElementById('projectCurrencySelect');
    if (!sel) return;
    const currentVal = sel.value;
    sel.innerHTML = '';
    currenciesList.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.code;
        opt.textContent = `${c.code} (${c.symbol}) ${c.name}`;
        if (c.code === currentVal || (!currentVal && c.is_default)) {
            opt.selected = true;
        }
        sel.appendChild(opt);
    });
    updateCurrencySymbols(sel.value);
}

function editCurrencyItem(id) {
    let cur = null;
    for (let item of window.CURRENCIES_CACHE.values()) {
        if (Number(item.id) === Number(id)) { cur = item; break; }
    }
    if (!cur) return;

    document.getElementById('editCurrencyId').value = cur.id;
    document.getElementById('newCurrencyCode').value = cur.code;
    document.getElementById('newCurrencyName').value = cur.name;
    document.getElementById('newCurrencySymbol').value = cur.symbol;
    document.getElementById('newCurrencyRate').value = parseFloat(cur.exchange_rate).toFixed(4);
    document.getElementById('newCurrencyIsDefault').checked = Boolean(cur.is_default);
    document.getElementById('currencyFormHeaderTitle').textContent = `✏️ '${cur.code}' Para Birimini Düzenle`;
    document.getElementById('btnSaveCurrency').textContent = '✓ Güncellemeyi Kaydet';
    document.getElementById('btnCancelEditCurrency').style.display = 'inline';
    document.getElementById('newCurrencyCode').focus();
}

function saveCurrencyModalAction() {
    const editId = document.getElementById('editCurrencyId').value;
    const code = document.getElementById('newCurrencyCode').value.trim().toUpperCase();
    const name = document.getElementById('newCurrencyName').value.trim();
    const symbol = document.getElementById('newCurrencySymbol').value.trim();
    const exchange_rate = document.getElementById('newCurrencyRate').value;
    const is_default = document.getElementById('newCurrencyIsDefault').checked ? 1 : 0;

    if (!code || !name || !symbol) {
        alert('Lütfen Kod, Tanım ve Sembol alanlarını eksiksiz doldurunuz.');
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    const isEdit = Boolean(editId);
    const url = isEdit ? `{{ url('admin/currencies') }}/${editId}` : `{{ route('admin.currencies.store') }}`;
    const method = isEdit ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ code, name, symbol, exchange_rate, is_default, _token: csrfToken })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            resetCurrencyForm();
            loadCurrenciesList();
        } else {
            alert(res.message || 'Hata oluştu.');
        }
    })
    .catch(e => alert('İşlem sırasında sunucu hatası oluştu.'));
}

function deleteCurrencyItem(id) {
    let cur = null;
    for (let item of window.CURRENCIES_CACHE.values()) {
        if (Number(item.id) === Number(id)) { cur = item; break; }
    }
    const curCode = cur ? cur.code : 'Seçili para birimi';

    showAppConfirmModal({
        title: 'Para Birimini Sil',
        message: `'${curCode}' para birimini silmek istediğinize emin misiniz?`,
        details: 'Bu para birimi proje seçeneklerinden kaldırılır. Projelerde veya bütçe kalemlerinde kullanılıyorsa güvenli şekilde arşive alınır.',
        confirmButtonText: 'Evet, Para Birimini Sil',
        onConfirm: function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            fetch('{{ url("admin/currencies") }}/' + id, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ _token: csrfToken, _method: 'DELETE' })
            })
            .then(async (r) => {
                if (!r.ok) {
                    const fallback = await fetch('{{ url("admin/currencies") }}/' + id + '/delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ _token: csrfToken })
                    });
                    return fallback.json();
                }
                return r.json();
            })
            .then(data => {
                closeAppConfirmDeleteModal();
                if (data.success) {
                    loadCurrenciesList();
                } else {
                    alert(data.message || 'Silme işlemi başarısız.');
                }
            })
            .catch(err => {
                closeAppConfirmDeleteModal();
                alert('Silme sırasında hata oluştu: ' + (err.message || err));
            });
        }
    });
}


// ==========================================
// KURUMSAL KİLOMETRE TAŞLARI (FAZLAR) YÖNETİMİ (EDIT)
// ==========================================
function calculateProjectDuration() {
    const startInput = document.getElementById('projectStartDateInput');
    const endInput = document.getElementById('projectTargetEndDateInput');
    const noticeEl = document.getElementById('projectDurationNotice');
    const daysEl = document.getElementById('projectDurationDaysText');
    const monthsEl = document.getElementById('projectDurationMonthsText');

    if (!startInput || !endInput || !noticeEl) return;

    if (startInput.value && endInput.value) {
        const s = new Date(startInput.value);
        const e = new Date(endInput.value);
        const diffMs = e - s;
        if (diffMs >= 0) {
            const diffDays = Math.round(diffMs / (1000 * 60 * 60 * 24)) + 1;
            const approxMonths = (diffDays / 30.4).toFixed(1);
            if (daysEl) daysEl.textContent = diffDays + ' Gün';
            if (monthsEl) monthsEl.textContent = '~' + approxMonths + ' Ay';
            noticeEl.style.display = 'flex';
        } else {
            noticeEl.style.display = 'none';
        }
    } else {
        noticeEl.style.display = 'none';
    }
}
const existingProjectPhases = [
    @if(isset($project) && $project->phases->count() > 0)
        @foreach($project->phases as $ph)
        {
            name: @json($ph->name),
            start_date: @json($ph->start_date ? $ph->start_date->format('Y-m-d') : ''),
            target_date: @json($ph->end_date ? $ph->end_date->format('Y-m-d') : ''),
            completion_criteria: @json($ph->description ?? '')
        },
        @endforeach
    @else
        { name: '1. Analiz ve teknik şartname', target_date: '2026-10-31', completion_criteria: 'Mevcut durum analizi ve teknik şartname onayı' },
        { name: '2. Satın alma ve sipariş', target_date: '2026-11-30', completion_criteria: 'Tedarikçi ve yatırım siparişlerinin onaylanması' },
        { name: '3. Altyapı hazırlığı', target_date: '2027-01-31', completion_criteria: 'Elektrik, otomasyon ve yardımcı tesislerin hazır olması' },
        { name: '4. Makine kurulumu', target_date: '2027-03-31', completion_criteria: 'Mekanik ve elektriksel kurulumların tamamlanması' },
        { name: '5. Test ve devreye alma', target_date: '2027-05-31', completion_criteria: 'İSG, kalite ve performans testlerinin tamamlanması' },
        { name: '6. Nihai kabul', target_date: '2027-06-30', completion_criteria: 'Hedeflerin doğrulanması ve seri üretime geçiş' }
    @endif
];

let milestoneRowIndex = 0;

function renderInitialMilestones() {
    const tbody = document.getElementById('milestonesTableBody');
    if (!tbody) return;
    tbody.innerHTML = '';
    existingProjectPhases.forEach(m => {
        addMilestoneRow(m.name, m.start_date || '', m.target_date, m.completion_criteria);
    });
    calculateProjectDuration();
}

function addMilestoneRow(name = '', startDate = '', targetDate = '', criteria = '') {
    const tbody = document.getElementById('milestonesTableBody');
    if (!tbody) return;
    
    const idx = milestoneRowIndex++;
    const tr = document.createElement('tr');
    tr.id = `milestone_row_${idx}`;
    tr.innerHTML = `
        <td style="text-align: center; font-weight: 700; color: #64748b;" class="milestone-num-col">
            ${tbody.children.length + 1}
        </td>
        <td>
            <input type="text" name="milestones[${idx}][name]" value="${escapeHtml(name)}" class="form-control form-control-sm" placeholder="Örn: 1. Analiz ve teknik şartname" required style="font-weight: 600;">
        </td>
        <td>
            <input type="date" name="milestones[${idx}][start_date]" value="${startDate}" class="form-control form-control-sm" required style="font-weight: 500;">
        </td>
        <td>
            <input type="date" name="milestones[${idx}][target_date]" value="${targetDate}" class="form-control form-control-sm" required>
        </td>
        <td>
            <input type="text" name="milestones[${idx}][completion_criteria]" value="${escapeHtml(criteria)}" class="form-control form-control-sm" placeholder="Örn: Mevcut durum analizi ve teknik şartname onayı">
        </td>
        <td style="text-align: center;">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeMilestoneRow(${idx})" title="Bu fazı sil" style="padding: 2px 7px; font-size: 11px;">
                🗑️
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    reindexMilestoneNumbers();
}

function addNewMilestoneRow() {
    let defaultStart = '';
    let defaultEnd = '';
    const lastRow = document.querySelector('#milestonesTableBody tr:last-child');
    if (lastRow) {
        const lastTargetInput = lastRow.querySelector('input[name*="[target_date]"]');
        if (lastTargetInput && lastTargetInput.value) {
            const lastD = new Date(lastTargetInput.value);
            lastD.setDate(lastD.getDate() + 1);
            defaultStart = lastD.toISOString().split('T')[0];
            const endD = new Date(lastD);
            endD.setDate(endD.getDate() + 30);
            defaultEnd = endD.toISOString().split('T')[0];
        }
    }
    if (!defaultStart) {
        const pStartInput = document.getElementById('projectStartDateInput');
        defaultStart = (pStartInput && pStartInput.value) ? pStartInput.value : new Date().toISOString().split('T')[0];
        const endD = new Date(defaultStart);
        endD.setDate(endD.getDate() + 30);
        defaultEnd = endD.toISOString().split('T')[0];
    }
    addMilestoneRow('', defaultStart, defaultEnd, '');
}

function removeMilestoneRow(idx) {
    const row = document.getElementById(`milestone_row_${idx}`);
    if (row) {
        row.remove();
        reindexMilestoneNumbers();
    }
}

function reindexMilestoneNumbers() {
    const tbody = document.getElementById('milestonesTableBody');
    if (!tbody) return;
    Array.from(tbody.children).forEach((tr, i) => {
        const numCol = tr.querySelector('.milestone-num-col');
        if (numCol) numCol.textContent = i + 1;
    });
}

function submitEditFormAs(actionType) {
    const actionInput = document.getElementById('actionTypeInput');
    if (actionInput) actionInput.value = actionType;

    const statusInput = document.getElementById('statusInput');
    if (statusInput && actionType === 'draft') {
        statusInput.value = 'draft';
    }

    // Quill editörü senkronize et
    if (window.quillProjectDescription) {
        document.getElementById('projectDescriptionInput').value = window.quillProjectDescription.root.innerHTML;
    }

    if (actionType === 'publish') {
        openProjectSummaryConfirmModal();
    } else {
        // Taslak güncelleme
        const form = document.querySelector('form[action*="projects"]');
        if (form) {
            form.noValidate = true;
            const btn = document.getElementById('btnSaveDraftEdit');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span>⏳</span> Taslak Güncelleniyor...';
            }
            form.submit();
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
        renderProjectTags();

    renderInitialMilestones();
});


// =========================================================================
// PROJE ETİKETLERİ (PROJECT TAGS) YÖNETİMİ
// =========================================================================
let currentProjectTags = [];
try {
    const rawTags = document.getElementById('projectTagsJsonInput') ? document.getElementById('projectTagsJsonInput').value : '[]';
    currentProjectTags = JSON.parse(rawTags || '[]');
    if (!Array.isArray(currentProjectTags)) currentProjectTags = [];
} catch(e) {
    currentProjectTags = [];
}

function renderProjectTags() {
    const list = document.getElementById('projectActiveTagsList');
    const badge = document.getElementById('projectTagsCountBadge');
    const hidden = document.getElementById('projectTagsJsonInput');
    if (!list) return;

    list.innerHTML = currentProjectTags.map((tag, idx) => `
        <span style="background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); color: #0369a1; border: 1px solid #7dd3fc; font-size: 11.5px; font-weight: 750; padding: 3px 9px; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 1px 2px rgba(2,132,199,0.08);">
            <span>#${escapeHtml(tag)}</span>
            <a href="javascript:void(0)" onclick="removeProjectTag(${idx}); event.stopPropagation();" style="color: #0284c7; text-decoration: none; font-weight: 850; font-size: 11px;" title="Etiketi Kaldır">✕</a>
        </span>
    `).join('');

    if (badge) badge.innerText = currentProjectTags.length + ' Etiket';
    if (hidden) hidden.value = JSON.stringify(currentProjectTags);
}

function addProjectTag(tag) {
    tag = tag.trim().replace(/^#+/, '');
    if (!tag) return;
    if (!currentProjectTags.includes(tag)) {
        currentProjectTags.push(tag);
        renderProjectTags();
    }
}

function removeProjectTag(idx) {
    currentProjectTags.splice(idx, 1);
    renderProjectTags();
}

function handleProjectTagInputKey(e) {
    if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault();
        const val = e.target.value.trim().replace(/,/g, '');
        if (val) {
            addProjectTag(val);
            e.target.value = '';
        }
    } else if (e.key === 'Backspace' && !e.target.value && currentProjectTags.length > 0) {
        removeProjectTag(currentProjectTags.length - 1);
    }
}

// =========================================================================
// DOSYA ETİKETLERİ (ATTACHMENT TAGS) YÖNETİMİ
// =========================================================================
function addScopeFileTag(fileIdx, tag) {
    tag = tag.trim().replace(/^#+/, '');
    if (!tag || !pendingScopeFiles[fileIdx]) return;
    if (!pendingScopeFiles[fileIdx]._tags) pendingScopeFiles[fileIdx]._tags = [];
    if (!pendingScopeFiles[fileIdx]._tags.includes(tag)) {
        pendingScopeFiles[fileIdx]._tags.push(tag);
        syncPendingFilesToHiddenInput();
        renderScopeFilesPreview();
    }
}

function removeScopeFileTag(fileIdx, tagIdx) {
    if (!pendingScopeFiles[fileIdx] || !pendingScopeFiles[fileIdx]._tags) return;
    pendingScopeFiles[fileIdx]._tags.splice(tagIdx, 1);
    syncPendingFilesToHiddenInput();
    renderScopeFilesPreview();
}

</script>
@endpush
