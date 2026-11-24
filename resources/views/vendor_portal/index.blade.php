@extends('vendor_portal.layout')

@section('title', $vendor->company_name . ' - Güvenli Belge Portalı')

@section('styles')
<style>
    /* Özel Kaydırma Çubuğu (Scrollbar) - Sayfanın Düzenini Korur ve Kolayca Kaydırır */
    .portal-files-scroll-container {
        max-height: 560px;
        overflow-y: auto;
        padding-right: 6px;
    }
    .portal-files-scroll-container::-webkit-scrollbar {
        width: 6px;
    }
    .portal-files-scroll-container::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 8px;
    }
    .portal-files-scroll-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 8px;
    }
    .portal-files-scroll-container::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Tarih & Detay Rozeti */
    .portal-date-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        color: #0f172a;
        font-weight: 700;
        font-size: 11.5px;
        padding: 3px 8px;
        border-radius: 6px;
        letter-spacing: 0.2px;
    }

    /* Seçilen Dosya Önizleme Kartları */
    .selected-file-chip {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 8px 12px;
        transition: all 0.15s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .selected-file-chip:hover {
        border-color: #94a3b8;
        background: #f8fafc;
    }
    .selected-file-remove-btn {
        background: #fee2e2;
        color: #ef4444;
        border: 1px solid #fca5a5;
        border-radius: 6px;
        width: 26px;
        height: 26px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.15s ease;
        flex-shrink: 0;
    }
    .selected-file-remove-btn:hover {
        background: #ef4444;
        color: #ffffff;
    }

    /* Dosya Kartı Hover */
    .portal-file-card {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 14px;
        background: #ffffff;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .portal-file-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        border-color: #cbd5e1;
    }
</style>
@endsection

@section('content')
@php
    $inboundGroups = $inboundFiles->groupBy(function($file) {
        return $file->upload_batch_id ?: ('single_' . $file->id);
    });
    $outboundGroups = $outboundFiles->groupBy(function($file) {
        return $file->upload_batch_id ?: ('single_' . $file->id);
    });
@endphp
<!-- ========================================================================= -->
<!-- 1. PORTAL ÜST BAŞLIK VE PROJE BİLGİ ALANI                                   -->
<!-- ========================================================================= -->
<div class="portal-header">
    <div>
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px; flex-wrap: wrap;">
            <span class="portal-badge">🌐 Dış Paydaş Güvenli Portalı</span>
            <span style="font-size: 12px; color: #94a3b8;">Ref: {{ substr($vendor->access_token, 0, 8) }}</span>
            @if($vendor->project)
                <span class="badge" style="background: rgba(255,255,255,0.15); color: #ffffff; font-size: 11px; padding: 4px 10px; border-radius: 6px;">
                    📋 Proje Ref: {{ $vendor->project->code ?: ('PRJ-' . str_pad($vendor->project->id, 4, '0', STR_PAD_LEFT)) }}
                </span>
            @endif
        </div>
        <h1 style="font-size: 22px; font-weight: 800; margin: 0; color: #ffffff; display: flex; align-items: center; gap: 10px;">
            🏢 {{ $vendor->company_name }}
        </h1>
        <div style="font-size: 13px; color: #cbd5e1; margin-top: 6px; display: flex; gap: 18px; flex-wrap: wrap;">
            <span>👤 Yetkili: <strong>{{ $vendor->contact_person }}</strong></span>
            @if($vendor->service_scope)
                <span>🛠️ İş / Kapsam: <strong>{{ $vendor->service_scope }}</strong></span>
            @endif
        </div>
    </div>
    <div>
        <form action="{{ route('vendor_portal.logout', ['token' => $vendor->access_token]) }}" method="POST">
            @csrf
            <button type="submit" class="btn-portal-secondary" style="font-size: 12.5px; padding: 8px 16px; background: rgba(255,255,255,0.12); color: #ffffff; border-color: rgba(255,255,255,0.25); font-weight: 600;">
                Güvenli Çıkış ✕
            </button>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 2. EN ÜSTTE DOSYA YÜKLEME ALANI (HERO UPLOAD BAR - PROJE YÖNETİMİ DÜZENİ) -->
<!-- ========================================================================= -->
<div class="portal-card" style="margin-bottom: 16px; border: 1.5px solid #bfdbfe; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.06);">
    <div class="portal-card-header" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff; padding: 10px 18px;">
        <div style="font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
            <span>📤</span> Yeni Belge / Rapor Yükle
        </div>
        <span style="font-size: 11px; background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 6px; color: #ffffff; font-weight: 600;">
            Maks. 50 MB / Dosya &bull; Tek Seferde Maks. 10 Belge
        </span>
    </div>

    <div class="portal-card-body" style="padding: 14px 18px;">
        <form action="{{ route('vendor_portal.upload', ['token' => $vendor->access_token]) }}" method="POST" enctype="multipart/form-data" id="vendorUploadForm">
            @csrf

            <!-- 1. KATEGORİ VE BİLGİ ÇUBUĞU -->
            <div style="display: grid; grid-template-columns: 1fr auto; gap: 12px; align-items: center; margin-bottom: 12px;">
                <div>
                    <label style="display: block; font-size: 11px; font-weight: 700; color: #1e293b; margin-bottom: 4px;">
                        DOKÜMAN / BELGE KATEGORİSİ <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="category" id="vendorCategorySelect" class="form-control" style="width: 100%; height: 38px; border: 1.5px solid #cbd5e1; border-radius: 7px; font-size: 13px; font-weight: 600; padding: 0 10px; color: #0f172a; background-color: #ffffff;" required>
                        @foreach($vendor->categories_list as $cat)
                            <option value="{{ $cat }}">📁 {{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="text-align: right; align-self: flex-end;">
                    <span style="font-size: 11.5px; color: #0284c7; background: #e0f2fe; border: 1px solid #bae6fd; padding: 6px 12px; border-radius: 7px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                        <span>🏷️</span> Paydaşa Özel ({{ count($vendor->categories_list) }} Kategori)
                    </span>
                </div>
            </div>

            <!-- 2. SÜRÜKLE & BIRAK ALANI (DROPZONE - KOMPAKT & ŞIK) -->
            <div class="file-dropzone" id="portalDropzone" onclick="document.getElementById('vendorFileInput').click()" style="margin-bottom: 12px; padding: 16px 14px;">
                <div style="font-size: 26px; margin-bottom: 2px;">☁️</div>
                <div style="font-size: 13.5px; font-weight: 700; color: #0284c7; margin-bottom: 2px;">
                    Dosyaları seçmek için tıklayın veya bu alana sürükleyip bırakın
                </div>
                <div style="font-size: 11.5px; color: #64748b; line-height: 1.4;">
                    Desteklenen Formatlar: <strong>PDF, DWG, DXF, STEP, CAD, XLSX, DOCX, ZIP, PNG, JPG</strong> 
                    <span style="color: #0369a1; font-weight: 600;">(Tek seferde en fazla 10 dosya &bull; azami 50 MB/dosya)</span>
                </div>
                <input type="file" name="files[]" id="vendorFileInput" multiple style="display: none;" 
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.csv,.txt,.rtf,.dwg,.dxf,.step,.stp,.iges,.igs,.sldprt,.sldasm,.jpg,.jpeg,.png,.webp,.tif,.tiff,.zip,.rar,.7z,.tar,.gz"
                       onchange="handleFilesSelected(this.files)">
            </div>

            <!-- 3. ÖNİZLEME VE ÇARPI İLE ÇIKARMA ALANI -->
            <div id="selectedFilesPreviewBox" style="display: none; margin-bottom: 14px; background: #f8fafc; border: 1.5px dashed #93c5fd; border-radius: 8px; padding: 12px; max-height: 180px; overflow-y: auto;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px;">
                    <span style="font-size: 12px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                        <span>📋</span> Gönderilmek Üzere Seçilen Dosyalar
                    </span>
                    <span id="selectedCountBadge" style="font-weight: 800; font-size: 11px; color: #0284c7; background: #e0f2fe; padding: 2px 8px; border-radius: 5px;"></span>
                </div>

                <!-- Kartlar bu grid içerisine JS ile doldurulur -->
                <div id="selectedFilesCardsGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 8px;"></div>
            </div>

            <!-- 4. AÇIKLAMA / BİLGİLENDİRME MESAJI (OPSİYONEL) -->
            <div style="margin-bottom: 14px;">
                <label for="vendorUploadNotes" style="display: flex; align-items: center; justify-content: space-between; font-size: 11.5px; font-weight: 700; color: #1e293b; margin-bottom: 5px; flex-wrap: wrap; gap: 6px;">
                    <span style="display: flex; align-items: center; gap: 6px;">
                        <span>💬</span> AÇIKLAMA / BİLGİLENDİRME NOTU <span style="font-size: 11px; font-weight: 500; color: #64748b;">(Opsiyonel)</span>
                    </span>
                    <span style="font-size: 11px; font-weight: 500; color: #0284c7;">
                        ℹ️ Bu not yüklediğiniz dosyalara eklenir ve Köksan ekibi tarafından incelenirken görüntülenir.
                    </span>
                </label>
                <textarea name="notes" id="vendorUploadNotes" rows="2" class="form-control" 
                          placeholder="Yüklediğiniz dosya/dosyalar hakkında Köksan proje ekibine iletmek istediğiniz açıklama, revizyon detayları veya teknik notlar..." 
                          style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; font-size: 12.5px; font-family: inherit; color: #0f172a; resize: vertical; box-sizing: border-box; line-height: 1.4; transition: border-color 0.15s ease;"></textarea>
            </div>

            <!-- 5. GÜVENLİK NOTU VE GÖNDERİM BUTONU -->
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 260px; background: #eff6ff; border-left: 3px solid #0284c7; padding: 8px 12px; border-radius: 0 6px 6px 0; font-size: 11.5px; color: #1e40af;">
                    ℹ️ <strong>Denetim Kaydı:</strong> Yüklenen belgeler anında Köksan proje yöneticilerine iletilir ve kayıt altına alınır.
                </div>
                <div>
                    <button type="submit" id="btnUploadSubmit" class="btn-portal-primary" style="height: 40px; padding: 0 22px; font-size: 13.5px; font-weight: 800; border-radius: 7px; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);">
                        <span>🚀</span> Belgeleri Köksan'a Gönder
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>


<!-- ========================================================================= -->
<!-- 3. ORTA ARAMA & FİLTRELEME ÇUBUĞU (REALTIME CLIENT-SIDE SEARCH)            -->
<!-- ========================================================================= -->
<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 16px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
    <!-- ARAMA KUTUSU -->
    <div style="flex: 1; min-width: 280px; max-width: 480px; position: relative;">
        <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 15px; color: #64748b;">🔍</span>
        <input type="text" id="fileSearchInput" placeholder="Belge adı, kategori veya açıklama notuna göre ara..." 
               style="width: 100%; height: 40px; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 0 38px 0 38px; font-size: 13px; font-weight: 600; color: #0f172a; box-sizing: border-box;"
               oninput="filterPortalFiles(this.value)">
        <button type="button" id="clearSearchBtn" onclick="clearFileSearch()" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 16px; color: #94a3b8; cursor: pointer; display: none;">✕</button>
    </div>

    <!-- İSTATİSTİK & GÖRÜNÜM MODU SEÇİCİ & HIZLI GEÇİŞ ROZETLERİ -->
    <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
        <!-- GÖRÜNÜM MODU SEÇİCİ: PAKET / GRUP vs AYRI AYRI KARTLAR -->
        <div style="display: inline-flex; align-items: center; gap: 6px; background: #f1f5f9; padding: 3px 6px; border-radius: 8px; border: 1.5px solid #cbd5e1;">
            <span style="font-size: 11px; font-weight: 800; color: #475569; margin-left: 4px;">Görünüm:</span>
            <button type="button" id="btnModeGrouped" onclick="switchPortalViewMode('grouped')" 
                    style="border: none; background: #0284c7; color: #ffffff; font-weight: 800; font-size: 11.5px; padding: 4px 11px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: all 0.15s ease; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <span>📦</span> Paket / Grup Görünümü
            </button>
            <button type="button" id="btnModeSingle" onclick="switchPortalViewMode('single')" 
                    style="border: none; background: transparent; color: #64748b; font-weight: 700; font-size: 11.5px; padding: 4px 11px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: all 0.15s ease;">
                <span>📄</span> Ayrı Ayrı Kartlar
            </button>
        </div>

        <a href="#inboundSection" style="text-decoration: none; font-size: 12px; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 6px 14px; border-radius: 8px; border: 1px solid #bae6fd; display: inline-flex; align-items: center; gap: 6px; transition: all 0.15s ease;">
            <span>📤</span> Firmanız: <strong id="statInboundCount">{{ $inboundFiles->count() }}</strong> Belge ⬇️
        </a>
        <a href="#outboundSection" style="text-decoration: none; font-size: 12px; font-weight: 700; color: #0f172a; background: #f1f5f9; padding: 6px 14px; border-radius: 8px; border: 1px solid #e2e8f0; display: inline-flex; align-items: center; gap: 6px; transition: all 0.15s ease;">
            <span>📥</span> Köksan: <strong id="statOutboundCount">{{ $outboundFiles->count() }}</strong> Belge ⬇️
        </a>
    </div>
</div>


<!-- ========================================================================= -->
<!-- 4. ALT BÖLÜM: 2 EŞİT KOLONLU BELGE YÖNETİM MERKEZİ (SOL: FİRMA / SAĞ: KÖKSAN) -->
<!-- ========================================================================= -->
<div class="grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start;">

    <!-- ===================================================================== -->
    <!-- SOL KOLON: FİRMANIN YÜKLEDİĞİ BELGELER (INBOUND)                       -->
    <!-- ===================================================================== -->
    <div class="portal-card" id="inboundSection" style="margin-bottom: 0;">
        <div class="portal-card-header" style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; padding: 14px 18px;">
            <div style="font-size: 14px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <span>📤</span> Yüklediğiniz Belgeler ve Onay Durumu
            </div>
            <span class="portal-badge" style="background: #e0f2fe; color: #0369a1; font-weight: 800;">
                {{ $inboundFiles->count() }} Belge
            </span>
        </div>

        <div class="portal-card-body" style="padding: 16px;">
            @if($inboundFiles->isEmpty())
                <div style="text-align: center; padding: 48px 20px; color: #94a3b8; font-size: 13px;">
                    <div style="font-size: 40px; margin-bottom: 10px;">📭</div>
                    Henüz sisteme bir rapor veya belge yüklemediniz.<br>Yukarıdaki yükleme alanını kullanabilirsiniz.
                </div>
            @else
                <!-- İÇ KAYDIRMA ÇUBUĞU (MAX-HEIGHT: SAYFA ASLA UZAMAZ) -->
                <div class="portal-files-scroll-container" id="inboundFilesContainer">
                    
                    <!-- 1. GÖRÜNÜM: GRUPLU / PAKET GÖRÜNÜMÜ (VARSAYILAN - ÇOKLU YÜKLEMELER TEK KARTTA) -->
                    <div id="inboundGroupedContainer" style="display: flex; flex-direction: column; gap: 14px;">
                        @foreach($inboundGroups as $batchId => $groupFiles)
                            @if($groupFiles->count() > 1)
                                <!-- ÇOKLU DOSYA GRUP / PAKET KARTI -->
                                <div class="portal-file-card portal-file-item inbound-file-item grouped-package-card"
                                     data-name="{{ mb_strtolower($groupFiles->pluck('original_name')->implode(' ')) }}"
                                     data-category="{{ mb_strtolower($groupFiles->first()->category) }}"
                                     data-notes="{{ mb_strtolower($groupFiles->first()->notes ?? '') }} {{ mb_strtolower($groupFiles->first()->review_note ?? '') }}"
                                     style="background: #ffffff; border: 1.5px solid #cbd5e1; border-top: 4px solid #0284c7; border-radius: 10px; padding: 14px; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                                    
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1.5px solid #f1f5f9;">
                                        <div>
                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <span style="font-size: 13.5px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                                                    <span>📦</span> Toplu Yükleme Paketi
                                                </span>
                                                <span style="font-size: 11px; font-weight: 800; color: #0284c7; background: #e0f2fe; border: 1px solid #bae6fd; padding: 2px 8px; border-radius: 6px;">
                                                    {{ $groupFiles->count() }} Belge
                                                </span>
                                                <span style="font-size: 11px; font-weight: 700; color: #0f172a; background: #f1f5f9; border: 1px solid #e2e8f0; padding: 2px 8px; border-radius: 6px;">
                                                    📁 {{ $groupFiles->first()->category }}
                                                </span>
                                            </div>

                                            <!-- BELİRGİN TARİH VE SAAT ROZETİ -->
                                            <div style="display: flex; align-items: center; gap: 8px; margin-top: 6px; flex-wrap: wrap;">
                                                <div style="display: inline-flex; align-items: center; gap: 8px; background: #f8fafc; border: 1.5px solid #cbd5e1; padding: 3px 10px; border-radius: 7px;">
                                                    <span style="font-size: 12px; font-weight: 800; color: #0369a1; display: inline-flex; align-items: center; gap: 4px;">
                                                        <span>📅</span> {{ $groupFiles->first()->created_at->format('d.m.Y') }}
                                                    </span>
                                                    <span style="color: #cbd5e1; font-weight: 300;">|</span>
                                                    <span style="font-size: 12px; font-weight: 800; color: #0f172a; display: inline-flex; align-items: center; gap: 4px;">
                                                        <span>⏰</span> {{ $groupFiles->first()->created_at->format('H:i') }}
                                                    </span>
                                                </div>
                                                <span style="font-size: 11.5px; font-weight: 700; color: #64748b;">
                                                    💾 Toplam: {{ number_format($groupFiles->sum('file_size') / (1024*1024), 2, ',', '.') }} MB
                                                </span>
                                            </div>
                                        </div>

                                        <div style="text-align: right; flex-shrink: 0;">
                                            @php
                                                $pendingCount = $groupFiles->where('status', 'pending_review')->where('is_void', false)->count();
                                                $approvedCount = $groupFiles->where('status', 'approved')->where('is_void', false)->count();
                                            @endphp
                                            @if($pendingCount > 0)
                                                <span style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 11px; font-weight: 800; padding: 4px 9px; border-radius: 6px; display: inline-block;">
                                                    ⏳ {{ $pendingCount }} İnceleme Bekliyor
                                                </span>
                                            @elseif($approvedCount === $groupFiles->count())
                                                <span style="background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; font-size: 11px; font-weight: 800; padding: 4px 9px; border-radius: 6px; display: inline-block;">
                                                    ✅ Tümü Kabul Edildi
                                                </span>
                                            @else
                                                <span style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-size: 11px; font-weight: 800; padding: 4px 9px; border-radius: 6px; display: inline-block;">
                                                    {{ $groupFiles->count() }} Belge
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- FİRMA BİLGİLENDİRME NOTU (PAKET İÇİN TEK YERDE) -->
                                    @if($groupFiles->first()->notes)
                                        <div style="margin-bottom: 10px; background: #f0fdf4; border: 1px solid #bbf7d0; border-left: 3.5px solid #16a34a; border-radius: 6px; padding: 7px 11px; font-size: 12px; color: #166534;">
                                            <div style="font-weight: 800; display: flex; align-items: center; gap: 5px; margin-bottom: 2px;">
                                                <span>💬</span> <span>Firma Bilgilendirme Notu / Açıklaması:</span>
                                            </div>
                                            <div style="color: #0f172a; font-size: 12px; line-height: 1.45;">
                                                {{ $groupFiles->first()->notes }}
                                            </div>
                                        </div>
                                    @endif

                                    <!-- KÖKSAN BİLGİLENDİRME & DEĞERLENDİRME NOTU -->
                                    @if($groupFiles->first()->review_note)
                                        <div style="margin-bottom: 10px; background: #eff6ff; border: 1px solid #bfdbfe; border-left: 3.5px solid #0284c7; border-radius: 6px; padding: 7px 11px; font-size: 12px; color: #1e40af;">
                                            <div style="font-weight: 800; display: flex; align-items: center; gap: 5px; margin-bottom: 2px;">
                                                <span>🏢</span> <span>Köksan Bilgilendirme & Değerlendirme Notu:</span>
                                            </div>
                                            <div style="color: #0f172a; font-size: 12px; line-height: 1.45;">
                                                {{ $groupFiles->first()->review_note }}
                                            </div>
                                        </div>
                                    @endif

                                    <!-- PAKET İÇİ DOSYALARIN DÜZENLİ LİSTESİ -->
                                    <div style="display: flex; flex-direction: column; gap: 6px;">
                                        @foreach($groupFiles as $file)
                                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 7px; padding: 8px 12px; font-size: 12px; transition: background 0.15s ease;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                                                <div style="display: flex; align-items: center; gap: 8px; overflow: hidden;">
                                                    <span style="font-size: 18px; line-height: 1;">{{ $file->file_icon }}</span>
                                                    <div style="overflow: hidden;">
                                                        <span style="font-weight: 700; color: #0f172a; word-break: break-all; {{ $file->is_void ? 'text-decoration: line-through; color: #94a3b8;' : '' }}">
                                                            {{ $file->original_name }}
                                                        </span>
                                                        <span style="font-size: 11px; color: #64748b; font-weight: 600; margin-left: 4px;">
                                                            ({{ $file->formatted_size }})
                                                        </span>
                                                    </div>
                                                </div>
                                                <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                                                    @if($file->is_void)
                                                        <span style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-size: 10.5px; font-weight: 800; padding: 2px 7px; border-radius: 5px;">
                                                            ⚠️ Geçersiz
                                                        </span>
                                                    @elseif($file->status === 'approved')
                                                        <span style="background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; font-size: 10.5px; font-weight: 800; padding: 2px 7px; border-radius: 5px;">
                                                            ✅ Kabul
                                                        </span>
                                                    @elseif($file->status === 'rejected')
                                                        <span style="background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; font-size: 10.5px; font-weight: 800; padding: 2px 7px; border-radius: 5px;">
                                                            ❌ Red
                                                        </span>
                                                    @else
                                                        <span style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 10.5px; font-weight: 800; padding: 2px 7px; border-radius: 5px;">
                                                            ⏳ Bekliyor
                                                        </span>
                                                    @endif
                                                    <a href="{{ route('vendor_portal.download', ['token' => $vendor->access_token, 'file' => $file->id]) }}" class="btn-portal-secondary" style="padding: 3px 8px; font-size: 11.5px; text-decoration: none; font-weight: 700;">
                                                        ⬇️ İndir
                                                    </a>
                                                    @if(!$file->is_void)
                                                        <button type="button" class="btn-portal-danger" onclick="openVoidModal({{ $file->id }}, '{{ addslashes($file->original_name) }}')" style="padding: 3px 8px; font-size: 11px; font-weight: 600;" title="Dikkate Alma olarak işaretle">
                                                            ⚠️
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                @php $file = $groupFiles->first(); @endphp
                                <!-- TEKİL DOSYA KARTI (GRUPLU MODDA TEK YÜKLENEN DOSYALAR) -->
                                <div class="portal-file-card portal-file-item inbound-file-item" 
                                     data-name="{{ mb_strtolower($file->original_name) }}" 
                                     data-category="{{ mb_strtolower($file->category) }}"
                                     data-notes="{{ mb_strtolower($file->notes ?? '') }} {{ mb_strtolower($file->review_note ?? '') }}"
                                     style="{{ $file->is_void ? 'background: #fffbeb; border-color: #fde68a;' : 'background: #ffffff;' }}">
                                    
                                    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                                        <div style="display: flex; align-items: flex-start; gap: 12px; overflow: hidden;">
                                            <span style="font-size: 26px; line-height: 1;">{{ $file->file_icon }}</span>
                                            <div style="overflow: hidden;">
                                                <div style="font-size: 13.5px; font-weight: 800; color: #0f172a; word-break: break-all; {{ $file->is_void ? 'text-decoration: line-through; color: #94a3b8;' : '' }}">
                                                    {{ $file->original_name }}
                                                </div>

                                                <div style="margin-top: 4px;">
                                                    <span style="font-size: 11px; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 2px 7px; border-radius: 4px;">
                                                        📁 {{ $file->category }}
                                                    </span>
                                                </div>

                                                <!-- BELİRGİN TARİH & SAAT ROZETİ -->
                                                <div style="display: flex; align-items: center; gap: 8px; margin-top: 6px; flex-wrap: wrap;">
                                                    <div style="display: inline-flex; align-items: center; gap: 8px; background: #f8fafc; border: 1.5px solid #cbd5e1; padding: 3px 10px; border-radius: 7px;">
                                                        <span style="font-size: 12px; font-weight: 800; color: #0369a1; display: inline-flex; align-items: center; gap: 4px;">
                                                            <span>📅</span> {{ $file->created_at->format('d.m.Y') }}
                                                        </span>
                                                        <span style="color: #cbd5e1; font-weight: 300;">|</span>
                                                        <span style="font-size: 12px; font-weight: 800; color: #0f172a; display: inline-flex; align-items: center; gap: 4px;">
                                                            <span>⏰</span> {{ $file->created_at->format('H:i') }}
                                                        </span>
                                                    </div>
                                                    <span style="font-size: 11.5px; color: #475569; font-weight: 700;">
                                                        💾 {{ $file->formatted_size }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div style="text-align: right; flex-shrink: 0;">
                                            @if($file->is_void)
                                                <span style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-size: 11px; font-weight: 800; padding: 4px 9px; border-radius: 6px; display: inline-block;">
                                                    ⚠️ GEÇERSİZ
                                                </span>
                                            @elseif($file->status === 'approved')
                                                <span style="background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; font-size: 11px; font-weight: 800; padding: 4px 9px; border-radius: 6px; display: inline-block;">
                                                    ✅ KABUL EDİLDİ
                                                </span>
                                            @elseif($file->status === 'rejected')
                                                <span style="background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; font-size: 11px; font-weight: 800; padding: 4px 9px; border-radius: 6px; display: inline-block;">
                                                    ❌ REDDEDİLDİ
                                                </span>
                                            @else
                                                <span style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 11px; font-weight: 800; padding: 4px 9px; border-radius: 6px; display: inline-block;">
                                                    ⏳ İNCELEME BEKLİYOR
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- FİRMA BİLGİLENDİRME NOTU -->
                                    @if($file->notes)
                                        <div style="margin-top: 10px; background: #f0fdf4; border: 1px solid #bbf7d0; border-left: 3.5px solid #16a34a; border-radius: 6px; padding: 7px 11px; font-size: 12px; color: #166534;">
                                            <div style="font-weight: 800; display: flex; align-items: center; gap: 5px; margin-bottom: 2px;">
                                                <span>💬</span> <span>Firma Bilgilendirme Notu / Açıklaması:</span>
                                            </div>
                                            <div style="color: #0f172a; font-size: 12px; line-height: 1.45;">
                                                {{ $file->notes }}
                                            </div>
                                        </div>
                                    @endif

                                    <!-- KÖKSAN BİLGİLENDİRME & İNCELEME NOTU -->
                                    @if($file->review_note)
                                        <div style="margin-top: 10px; background: #eff6ff; border: 1px solid #bfdbfe; border-left: 3.5px solid #0284c7; border-radius: 6px; padding: 7px 11px; font-size: 12px; color: #1e40af;">
                                            <div style="font-weight: 800; display: flex; align-items: center; gap: 5px; margin-bottom: 2px;">
                                                <span>🏢</span> <span>Köksan Bilgilendirme & Değerlendirme Notu:</span>
                                            </div>
                                            <div style="color: #0f172a; font-size: 12px; line-height: 1.45;">
                                                {{ $file->review_note }}
                                            </div>
                                        </div>
                                    @endif

                                    @if($file->is_void)
                                        <div style="margin-top: 10px; background: #fffdf5; border: 1px dashed #fde68a; border-radius: 6px; padding: 8px 12px; font-size: 12px; color: #92400e;">
                                            <strong>⚠️ Dikkate Alma Nedeni:</strong> {{ $file->void_reason }} ({{ $file->voided_at?->format('d.m.Y H:i') }})
                                        </div>
                                    @endif

                                    <div style="display: flex; justify-content: flex-end; align-items: center; gap: 8px; margin-top: 12px; border-top: 1px solid #f1f5f9; padding-top: 10px;">
                                        <a href="{{ route('vendor_portal.download', ['token' => $vendor->access_token, 'file' => $file->id]) }}" class="btn-portal-secondary" style="padding: 5px 12px; font-size: 12px; text-decoration: none; font-weight: 700;">
                                            ⬇️ İndir
                                        </a>
                                        @if(!$file->is_void)
                                            <button type="button" class="btn-portal-danger" onclick="openVoidModal({{ $file->id }}, '{{ addslashes($file->original_name) }}')" style="font-weight: 600;">
                                                ⚠️ Bu Dosyayı Dikkate Alma Olarak İşaretle
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- 2. GÖRÜNÜM: AYRI AYRI KARTLAR (TEKİL LİSTE) -->
                    <div id="inboundSingleContainer" style="display: none; flex-direction: column; gap: 12px;">
                        @foreach($inboundFiles as $file)
                            <div class="portal-file-card portal-file-item inbound-file-item" 
                                 data-name="{{ mb_strtolower($file->original_name) }}" 
                                 data-category="{{ mb_strtolower($file->category) }}"
                                 data-notes="{{ mb_strtolower($file->notes ?? '') }} {{ mb_strtolower($file->review_note ?? '') }}"
                                 style="{{ $file->is_void ? 'background: #fffbeb; border-color: #fde68a;' : 'background: #ffffff;' }}">
                                
                                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                                    <div style="display: flex; align-items: flex-start; gap: 12px; overflow: hidden;">
                                        <span style="font-size: 26px; line-height: 1;">{{ $file->file_icon }}</span>
                                        <div style="overflow: hidden;">
                                            <div style="font-size: 13.5px; font-weight: 800; color: #0f172a; word-break: break-all; {{ $file->is_void ? 'text-decoration: line-through; color: #94a3b8;' : '' }}">
                                                {{ $file->original_name }}
                                            </div>

                                            <div style="margin-top: 4px;">
                                                <span style="font-size: 11px; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 2px 7px; border-radius: 4px;">
                                                    📁 {{ $file->category }}
                                                </span>
                                            </div>

                                            <!-- BELİRGİN TARİH VE SAAT ROZETİ -->
                                            <div style="display: flex; align-items: center; gap: 8px; margin-top: 6px; flex-wrap: wrap;">
                                                <div style="display: inline-flex; align-items: center; gap: 8px; background: #f8fafc; border: 1.5px solid #cbd5e1; padding: 3px 10px; border-radius: 7px;">
                                                    <span style="font-size: 12px; font-weight: 800; color: #0369a1; display: inline-flex; align-items: center; gap: 4px;">
                                                        <span>📅</span> {{ $file->created_at->format('d.m.Y') }}
                                                    </span>
                                                    <span style="color: #cbd5e1; font-weight: 300;">|</span>
                                                    <span style="font-size: 12px; font-weight: 800; color: #0f172a; display: inline-flex; align-items: center; gap: 4px;">
                                                        <span>⏰</span> {{ $file->created_at->format('H:i') }}
                                                    </span>
                                                </div>
                                                <span style="font-size: 11.5px; color: #475569; font-weight: 700;">
                                                    💾 {{ $file->formatted_size }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div style="text-align: right; flex-shrink: 0;">
                                        @if($file->is_void)
                                            <span style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-size: 11px; font-weight: 800; padding: 4px 9px; border-radius: 6px; display: inline-block;">
                                                ⚠️ GEÇERSİZ
                                            </span>
                                        @elseif($file->status === 'approved')
                                            <span style="background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; font-size: 11px; font-weight: 800; padding: 4px 9px; border-radius: 6px; display: inline-block;">
                                                ✅ KABUL EDİLDİ
                                            </span>
                                        @elseif($file->status === 'rejected')
                                            <span style="background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; font-size: 11px; font-weight: 800; padding: 4px 9px; border-radius: 6px; display: inline-block;">
                                                ❌ REDDEDİLDİ
                                            </span>
                                        @else
                                            <span style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 11px; font-weight: 800; padding: 4px 9px; border-radius: 6px; display: inline-block;">
                                                ⏳ İNCELEME BEKLİYOR
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- FİRMA BİLGİLENDİRME NOTU -->
                                @if($file->notes)
                                    <div style="margin-top: 10px; background: #f0fdf4; border: 1px solid #bbf7d0; border-left: 3.5px solid #16a34a; border-radius: 6px; padding: 7px 11px; font-size: 12px; color: #166534;">
                                        <div style="font-weight: 800; display: flex; align-items: center; gap: 5px; margin-bottom: 2px;">
                                            <span>💬</span> <span>Firma Bilgilendirme Notu / Açıklaması:</span>
                                        </div>
                                        <div style="color: #0f172a; font-size: 12px; line-height: 1.45;">
                                            {{ $file->notes }}
                                        </div>
                                    </div>
                                @endif

                                <!-- KÖKSAN BİLGİLENDİRME & İNCELEME NOTU -->
                                @if($file->review_note)
                                    <div style="margin-top: 10px; background: #eff6ff; border: 1px solid #bfdbfe; border-left: 3.5px solid #0284c7; border-radius: 6px; padding: 7px 11px; font-size: 12px; color: #1e40af;">
                                        <div style="font-weight: 800; display: flex; align-items: center; gap: 5px; margin-bottom: 2px;">
                                            <span>🏢</span> <span>Köksan Bilgilendirme & Değerlendirme Notu:</span>
                                        </div>
                                        <div style="color: #0f172a; font-size: 12px; line-height: 1.45;">
                                            {{ $file->review_note }}
                                        </div>
                                    </div>
                                @endif

                                @if($file->is_void)
                                    <div style="margin-top: 10px; background: #fffdf5; border: 1px dashed #fde68a; border-radius: 6px; padding: 8px 12px; font-size: 12px; color: #92400e;">
                                        <strong>⚠️ Dikkate Alma Nedeni:</strong> {{ $file->void_reason }} ({{ $file->voided_at?->format('d.m.Y H:i') }})
                                    </div>
                                @endif

                                <div style="display: flex; justify-content: flex-end; align-items: center; gap: 8px; margin-top: 12px; border-top: 1px solid #f1f5f9; padding-top: 10px;">
                                    <a href="{{ route('vendor_portal.download', ['token' => $vendor->access_token, 'file' => $file->id]) }}" class="btn-portal-secondary" style="padding: 5px 12px; font-size: 12px; text-decoration: none; font-weight: 700;">
                                        ⬇️ İndir
                                    </a>
                                    @if(!$file->is_void)
                                        <button type="button" class="btn-portal-danger" onclick="openVoidModal({{ $file->id }}, '{{ addslashes($file->original_name) }}')" style="font-weight: 600;">
                                            ⚠️ Bu Dosyayı Dikkate Alma Olarak İşaretle
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
                <div id="inboundNoResults" style="display: none; text-align: center; padding: 32px 10px; color: #64748b; font-size: 13px;">
                    🔍 Arama kriterine uygun yüklenmiş belge bulunamadı.
                </div>
            @endif
        </div>
    </div>


    <!-- ===================================================================== -->
    <!-- SAĞ KOLON: KÖKSAN TARAFINDAN GÖNDERİLEN BELGELER (OUTBOUND)            -->
    <!-- ===================================================================== -->
    <div class="portal-card" id="outboundSection" style="margin-bottom: 0;">
        <div class="portal-card-header" style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; padding: 14px 18px;">
            <div style="font-size: 14px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <span>📥</span> Köksan Tarafından Paylaşılan Belgeler
            </div>
            <span class="portal-badge" style="background: #e0f2fe; color: #0284c7; font-weight: 800;">
                {{ $outboundFiles->count() }} Belge
            </span>
        </div>

        <div class="portal-card-body" style="padding: 16px;">
            @if($outboundFiles->isEmpty())
                <div style="text-align: center; padding: 48px 20px; color: #94a3b8; font-size: 13px;">
                    <div style="font-size: 40px; margin-bottom: 10px;">📐</div>
                    Henüz tarafınızla paylaşılan bir şartname veya teknik çizim bulunmamaktadır.
                </div>
            @else
                <!-- İÇ KAYDIRMA ÇUBUĞU (MAX-HEIGHT: SAYFA ASLA UZAMAZ) -->
                <div class="portal-files-scroll-container" id="outboundFilesContainer">
                    
                    <!-- 1. GÖRÜNÜM: GRUPLU / PAKET GÖRÜNÜMÜ -->
                    <div id="outboundGroupedContainer" style="display: flex; flex-direction: column; gap: 14px;">
                        @foreach($outboundGroups as $batchId => $groupFiles)
                            @if($groupFiles->count() > 1)
                                <!-- ÇOKLU DOSYA GRUP / PAKET KARTI -->
                                <div class="portal-file-card portal-file-item outbound-file-item grouped-package-card"
                                     data-name="{{ mb_strtolower($groupFiles->pluck('original_name')->implode(' ')) }}"
                                     data-category="{{ mb_strtolower($groupFiles->first()->category) }}"
                                     data-notes="{{ mb_strtolower($groupFiles->first()->notes ?? '') }}"
                                     style="background: #ffffff; border: 1.5px solid #cbd5e1; border-top: 4px solid #0284c7; border-radius: 10px; padding: 14px; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                                    
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1.5px solid #f1f5f9;">
                                        <div>
                                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                                <span style="font-size: 13.5px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                                                    <span>📦</span> Köksan Paylaşım Paketi
                                                </span>
                                                <span style="font-size: 11px; font-weight: 800; color: #0284c7; background: #e0f2fe; border: 1px solid #bae6fd; padding: 2px 8px; border-radius: 6px;">
                                                    {{ $groupFiles->count() }} Belge
                                                </span>
                                                <span style="font-size: 11px; font-weight: 700; color: #166534; background: #dcfce7; border: 1px solid #bbf7d0; padding: 2px 8px; border-radius: 6px;">
                                                    📁 {{ $groupFiles->first()->category }}
                                                </span>
                                            </div>

                                            <!-- BELİRGİN TARİH VE SAAT ROZETİ -->
                                            <div style="display: flex; align-items: center; gap: 8px; margin-top: 6px; flex-wrap: wrap;">
                                                <div style="display: inline-flex; align-items: center; gap: 8px; background: #f8fafc; border: 1.5px solid #cbd5e1; padding: 3px 10px; border-radius: 7px;">
                                                    <span style="font-size: 12px; font-weight: 800; color: #0369a1; display: inline-flex; align-items: center; gap: 4px;">
                                                        <span>📅</span> {{ $groupFiles->first()->created_at->format('d.m.Y') }}
                                                    </span>
                                                    <span style="color: #cbd5e1; font-weight: 300;">|</span>
                                                    <span style="font-size: 12px; font-weight: 800; color: #0f172a; display: inline-flex; align-items: center; gap: 4px;">
                                                        <span>⏰</span> {{ $groupFiles->first()->created_at->format('H:i') }}
                                                    </span>
                                                </div>
                                                <span style="font-size: 11.5px; font-weight: 700; color: #64748b;">
                                                    💾 Toplam: {{ number_format($groupFiles->sum('file_size') / (1024*1024), 2, ',', '.') }} MB
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- KÖKSAN BİLGİLENDİRME NOTU -->
                                    @if($groupFiles->first()->notes)
                                        <div style="margin-bottom: 10px; background: #eff6ff; border: 1px solid #bfdbfe; border-left: 3.5px solid #0284c7; border-radius: 6px; padding: 7px 11px; font-size: 12px; color: #1e40af;">
                                            <div style="font-weight: 800; display: flex; align-items: center; gap: 5px; margin-bottom: 2px;">
                                                <span>🏢</span> <span>Köksan Bilgilendirme Notu / Açıklaması:</span>
                                            </div>
                                            <div style="color: #0f172a; font-size: 12px; line-height: 1.45;">
                                                {{ $groupFiles->first()->notes }}
                                            </div>
                                        </div>
                                    @endif

                                    <!-- PAKET İÇİ DOSYALARIN DÜZENLİ LİSTESİ -->
                                    <div style="display: flex; flex-direction: column; gap: 6px;">
                                        @foreach($groupFiles as $file)
                                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 7px; padding: 8px 12px; font-size: 12px; transition: background 0.15s ease;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                                                <div style="display: flex; align-items: center; gap: 8px; overflow: hidden;">
                                                    <span style="font-size: 18px; line-height: 1;">{{ $file->file_icon }}</span>
                                                    <div style="overflow: hidden;">
                                                        <span style="font-weight: 700; color: #0f172a; word-break: break-all; {{ $file->is_void ? 'text-decoration: line-through; color: #b45309;' : '' }}">
                                                            {{ $file->original_name }}
                                                        </span>
                                                        <span style="font-size: 11px; color: #64748b; font-weight: 600; margin-left: 4px;">
                                                            ({{ $file->formatted_size }})
                                                        </span>
                                                    </div>
                                                </div>
                                                <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                                                    <a href="{{ route('vendor_portal.download', ['token' => $vendor->access_token, 'file' => $file->id]) }}" class="btn-portal-secondary" style="padding: 4px 10px; font-size: 11.5px; text-decoration: none; font-weight: 700;">
                                                        ⬇️ İndir
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                @php $file = $groupFiles->first(); @endphp
                                <!-- TEKİL KÖKSAN DOSYA KARTI -->
                                <div class="portal-file-card portal-file-item outbound-file-item" 
                                     data-name="{{ mb_strtolower($file->original_name) }}" 
                                     data-category="{{ mb_strtolower($file->category) }}"
                                     data-notes="{{ mb_strtolower($file->notes ?? '') }}"
                                     style="{{ $file->is_void ? 'background: #fffbeb; border-color: #fde68a;' : 'background: #ffffff;' }}">
                                    
                                    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                                        <div style="display: flex; align-items: flex-start; gap: 12px; overflow: hidden;">
                                            <span style="font-size: 26px; line-height: 1;">{{ $file->file_icon }}</span>
                                            <div style="overflow: hidden;">
                                                <div style="font-size: 13.5px; font-weight: 800; color: #0f172a; word-break: break-all; {{ $file->is_void ? 'text-decoration: line-through; color: #b45309;' : '' }}">
                                                    {{ $file->original_name }}
                                                </div>

                                                <div style="margin-top: 4px;">
                                                    <span style="font-size: 11px; font-weight: 700; color: #166534; background: #dcfce7; padding: 2px 7px; border-radius: 4px;">
                                                        📁 {{ $file->category }}
                                                    </span>
                                                </div>

                                                <!-- BELİRGİN TARİH VE SAAT ROZETİ -->
                                                <div style="display: flex; align-items: center; gap: 8px; margin-top: 6px; flex-wrap: wrap;">
                                                    <div style="display: inline-flex; align-items: center; gap: 8px; background: #f8fafc; border: 1.5px solid #cbd5e1; padding: 3px 10px; border-radius: 7px;">
                                                        <span style="font-size: 12px; font-weight: 800; color: #0369a1; display: inline-flex; align-items: center; gap: 4px;">
                                                            <span>📅</span> {{ $file->created_at->format('d.m.Y') }}
                                                        </span>
                                                        <span style="color: #cbd5e1; font-weight: 300;">|</span>
                                                        <span style="font-size: 12px; font-weight: 800; color: #0f172a; display: inline-flex; align-items: center; gap: 4px;">
                                                            <span>⏰</span> {{ $file->created_at->format('H:i') }}
                                                        </span>
                                                    </div>
                                                    <span style="font-size: 11.5px; color: #475569; font-weight: 700;">
                                                        💾 {{ $file->formatted_size }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <a href="{{ route('vendor_portal.download', ['token' => $vendor->access_token, 'file' => $file->id]) }}" class="btn-portal-secondary" style="padding: 6px 14px; font-size: 12px; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                                ⬇️ İndir
                                            </a>
                                        </div>
                                    </div>

                                    <!-- KÖKSAN BİLGİLENDİRME NOTU (GÖRÜNÜR & ŞIK) -->
                                    @if($file->notes)
                                        <div style="margin-top: 10px; background: #eff6ff; border: 1px solid #bfdbfe; border-left: 3.5px solid #0284c7; border-radius: 6px; padding: 7px 11px; font-size: 12px; color: #1e40af;">
                                            <div style="font-weight: 800; display: flex; align-items: center; gap: 5px; margin-bottom: 2px;">
                                                <span>🏢</span> <span>Köksan Bilgilendirme Notu / Açıklaması:</span>
                                            </div>
                                            <div style="color: #0f172a; font-size: 12px; line-height: 1.45;">
                                                {{ $file->notes }}
                                            </div>
                                        </div>
                                    @endif

                                    @if($file->is_void)
                                        <div style="font-size: 11.5px; color: #b45309; font-weight: 600; margin-top: 6px; background: rgba(254, 243, 199, 0.6); padding: 4px 8px; border-radius: 4px;">
                                            ⚠️ Dikkate Alınmayacak Belge: {{ $file->void_reason }}
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- 2. GÖRÜNÜM: AYRI AYRI KARTLAR (TEKİL LİSTE) -->
                    <div id="outboundSingleContainer" style="display: none; flex-direction: column; gap: 12px;">
                        @foreach($outboundFiles as $file)
                            <div class="portal-file-card portal-file-item outbound-file-item" 
                                 data-name="{{ mb_strtolower($file->original_name) }}" 
                                 data-category="{{ mb_strtolower($file->category) }}"
                                 data-notes="{{ mb_strtolower($file->notes ?? '') }}"
                                 style="{{ $file->is_void ? 'background: #fffbeb; border-color: #fde68a;' : 'background: #ffffff;' }}">
                                
                                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                                    <div style="display: flex; align-items: flex-start; gap: 12px; overflow: hidden;">
                                        <span style="font-size: 26px; line-height: 1;">{{ $file->file_icon }}</span>
                                        <div style="overflow: hidden;">
                                            <div style="font-size: 13.5px; font-weight: 800; color: #0f172a; word-break: break-all; {{ $file->is_void ? 'text-decoration: line-through; color: #b45309;' : '' }}">
                                                {{ $file->original_name }}
                                            </div>

                                            <div style="margin-top: 4px;">
                                                <span style="font-size: 11px; font-weight: 700; color: #166534; background: #dcfce7; padding: 2px 7px; border-radius: 4px;">
                                                    📁 {{ $file->category }}
                                                </span>
                                            </div>

                                            <!-- BELİRGİN TARİH VE SAAT ROZETİ -->
                                            <div style="display: flex; align-items: center; gap: 8px; margin-top: 6px; flex-wrap: wrap;">
                                                <div style="display: inline-flex; align-items: center; gap: 8px; background: #f8fafc; border: 1.5px solid #cbd5e1; padding: 3px 10px; border-radius: 7px;">
                                                    <span style="font-size: 12px; font-weight: 800; color: #0369a1; display: inline-flex; align-items: center; gap: 4px;">
                                                        <span>📅</span> {{ $file->created_at->format('d.m.Y') }}
                                                    </span>
                                                    <span style="color: #cbd5e1; font-weight: 300;">|</span>
                                                    <span style="font-size: 12px; font-weight: 800; color: #0f172a; display: inline-flex; align-items: center; gap: 4px;">
                                                        <span>⏰</span> {{ $file->created_at->format('H:i') }}
                                                    </span>
                                                </div>
                                                <span style="font-size: 11.5px; color: #475569; font-weight: 700;">
                                                    💾 {{ $file->formatted_size }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <a href="{{ route('vendor_portal.download', ['token' => $vendor->access_token, 'file' => $file->id]) }}" class="btn-portal-secondary" style="padding: 6px 14px; font-size: 12px; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                            ⬇️ İndir
                                        </a>
                                    </div>
                                </div>

                                <!-- KÖKSAN BİLGİLENDİRME NOTU -->
                                @if($file->notes)
                                    <div style="margin-top: 10px; background: #eff6ff; border: 1px solid #bfdbfe; border-left: 3.5px solid #0284c7; border-radius: 6px; padding: 7px 11px; font-size: 12px; color: #1e40af;">
                                        <div style="font-weight: 800; display: flex; align-items: center; gap: 5px; margin-bottom: 2px;">
                                            <span>🏢</span> <span>Köksan Bilgilendirme Notu / Açıklaması:</span>
                                        </div>
                                        <div style="color: #0f172a; font-size: 12px; line-height: 1.45;">
                                            {{ $file->notes }}
                                        </div>
                                    </div>
                                @endif

                                @if($file->is_void)
                                    <div style="font-size: 11.5px; color: #b45309; font-weight: 600; margin-top: 6px; background: rgba(254, 243, 199, 0.6); padding: 4px 8px; border-radius: 4px;">
                                        ⚠️ Dikkate Alınmayacak Belge: {{ $file->void_reason }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                </div>
                <div id="outboundNoResults" style="display: none; text-align: center; padding: 32px 10px; color: #64748b; font-size: 13px;">
                    🔍 Arama kriterine uygun paylaşılan belge bulunamadı.
                </div>
            @endif
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 5. GEÇERSİZ / DİKKATE ALMA İŞARETÇİ MODALI                                 -->
<!-- ========================================================================= -->
<div id="voidMarkerModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: #ffffff; border-radius: 14px; max-width: 480px; width: 90%; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25);">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
            <span style="font-size: 24px;">⚠️</span>
            <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0;">Belgeyi 'Geçersiz / Dikkate Alma' Olarak İşaretle</h3>
        </div>
        <p style="font-size: 13px; color: #64748b; line-height: 1.5; margin-bottom: 16px;">
            <strong id="voidFileName" style="color: #0f172a;"></strong> belgesini hatalı yüklediyseniz veya revize edildiği için dikkate alınmaması gerekiyorsa lütfen gerekçesini belirtiniz. Belge silinmeyecek, denetim izinde üstü çizili ve açıklamanızla saklanacaktır.
        </p>

        <form id="voidMarkerForm" method="POST">
            @csrf
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 6px;">İşaretleme Açıklaması / Gerekçe <span class="text-danger">*</span></label>
                <textarea name="void_reason" required rows="3" class="form-control" placeholder="Örn: Revizyon 1 hatalı kaynak parametreleri içeriyor. Yeni hazırlanan Revizyon 2 belgesi geçerlidir." style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 12px; font-size: 13px; box-sizing: border-box;"></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-portal-secondary" onclick="closeVoidModal()">Vazgeç</button>
                <button type="submit" id="btnVoidSubmit" class="btn-portal-danger" style="padding: 8px 18px; font-weight: 700;">⚠️ Evet, Geçersiz Olarak İşaretle</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
// ZARARLI UZANTI KARALİSTESİ
const BLOCKED_EXTENSIONS = [
    'exe', 'bat', 'cmd', 'sh', 'bash', 'php', 'phtml', 'php3', 'php4', 'php5', 
    'phar', 'pl', 'py', 'cgi', 'js', 'jar', 'vbs', 'scr', 'msi', 'com', 'dll', 
    'bin', 'app', 'dmg', 'iso', 'reg', 'asp', 'aspx', 'jsp'
];

// SEÇİLEN DOSYALAR DİZİSİ (ETKİLEŞİMLİ LİSTE)
let selectedFilesList = [];

/**
 * Dosya Seçimi veya Sürükle-Bırak Olayı
 */
function handleFilesSelected(fileList) {
    if (!fileList || fileList.length === 0) return;

    for (let i = 0; i < fileList.length; i++) {
        const file = fileList[i];
        const ext = file.name.split('.').pop().toLowerCase();

        // 1. Güvenlik Engeli
        if (BLOCKED_EXTENSIONS.includes(ext)) {
            alert('🚫 Güvenlik Engeli: "' + file.name + '" çalıştırılabilir / zararlı olabilecek bir uzantıya (.' + ext + ') sahip olduğu için eklenemez.');
            continue;
        }

        // 2. Boyut Sınırı (50 MB)
        if (file.size > 50 * 1024 * 1024) {
            alert('⚠️ Boyut Sınırı: "' + file.name + '" dosyası 50 MB sınırını aşmaktadır.');
            continue;
        }

        // 3. Maksimum 10 Dosya Kontrolü
        if (selectedFilesList.length >= 10) {
            alert('⚠️ Yükleme Sınırı: Tek seferde en fazla 10 adet belge seçebilirsiniz.');
            break;
        }

        // Mükerrer eklemeyi engelle
        const alreadyExists = selectedFilesList.some(f => f.name === file.name && f.size === file.size);
        if (!alreadyExists) {
            selectedFilesList.push(file);
        }
    }

    syncInputAndRenderPreview();
}

/**
 * Seçilen Dosyayı Çarpıya (✕) Basarak Listeden Çıkarma
 */
function removeSelectedFile(index) {
    if (index >= 0 && index < selectedFilesList.length) {
        selectedFilesList.splice(index, 1);
        syncInputAndRenderPreview();
    }
}

/**
 * HTML <input type="file"> ile JS Dizisini Senkronize Et & Önizlemeyi Çiz
 */
function syncInputAndRenderPreview() {
    const fileInput = document.getElementById('vendorFileInput');
    const previewBox = document.getElementById('selectedFilesPreviewBox');
    const cardsGrid = document.getElementById('selectedFilesCardsGrid');
    const badge = document.getElementById('selectedCountBadge');

    // DataTransfer API ile input dosya listesini güncelle
    const dt = new DataTransfer();
    selectedFilesList.forEach(file => dt.items.add(file));
    fileInput.files = dt.files;

    cardsGrid.innerHTML = '';

    if (selectedFilesList.length === 0) {
        previewBox.style.display = 'none';
        return;
    }

    previewBox.style.display = 'block';
    badge.textContent = `${selectedFilesList.length} / 10 Belge`;

    selectedFilesList.forEach((file, idx) => {
        const ext = file.name.split('.').pop().toLowerCase();
        const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
        const isImage = ['jpg', 'jpeg', 'png', 'webp'].includes(ext);

        let iconOrThumb = '📄';
        if (['pdf'].includes(ext)) iconOrThumb = '📕';
        else if (['dwg', 'dxf', 'step', 'stp'].includes(ext)) iconOrThumb = '📐';
        else if (['xls', 'xlsx', 'csv'].includes(ext)) iconOrThumb = '📊';
        else if (['doc', 'docx'].includes(ext)) iconOrThumb = '📝';
        else if (['zip', 'rar', '7z'].includes(ext)) iconOrThumb = '📦';
        else if (isImage) iconOrThumb = '🖼️';

        const chip = document.createElement('div');
        chip.className = 'selected-file-chip';

        let thumbHtml = `<span style="font-size: 20px;">${iconOrThumb}</span>`;
        if (isImage) {
            const objectUrl = URL.createObjectURL(file);
            thumbHtml = `<img src="${objectUrl}" alt="Önizleme" style="width: 32px; height: 32px; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0;">`;
        }

        chip.innerHTML = `
            <div style="display: flex; align-items: center; gap: 8px; overflow: hidden;">
                ${thumbHtml}
                <div style="overflow: hidden;">
                    <div style="font-size: 12.5px; font-weight: 700; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 220px;" title="${escapeHtml(file.name)}">
                        ${escapeHtml(file.name)}
                    </div>
                    <div style="font-size: 11px; color: #64748b;">
                        .${ext.toUpperCase()} &bull; ${sizeMb} MB
                    </div>
                </div>
            </div>
            <button type="button" class="selected-file-remove-btn" onclick="removeSelectedFile(${idx})" title="Bu dosyayı kaldır">✕</button>
        `;

        cardsGrid.appendChild(chip);
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Anlık Arama ve Filtreleme Motoru (Canlı Arama)
 */
function filterPortalFiles(query) {
    const term = query.trim().toLowerCase();
    const clearBtn = document.getElementById('clearSearchBtn');
    if (clearBtn) {
        clearBtn.style.display = term ? 'block' : 'none';
    }

    let inboundVisible = 0;
    document.querySelectorAll('.inbound-file-item').forEach(item => {
        const name = item.getAttribute('data-name') || '';
        const cat = item.getAttribute('data-category') || '';
        const notes = item.getAttribute('data-notes') || '';
        if (!term || name.includes(term) || cat.includes(term) || notes.includes(term)) {
            item.style.display = 'block';
            inboundVisible++;
        } else {
            item.style.display = 'none';
        }
    });

    const inboundNoResults = document.getElementById('inboundNoResults');
    if (inboundNoResults) {
        inboundNoResults.style.display = (inboundVisible === 0 && document.querySelectorAll('.inbound-file-item').length > 0) ? 'block' : 'none';
    }

    let outboundVisible = 0;
    document.querySelectorAll('.outbound-file-item').forEach(item => {
        const name = item.getAttribute('data-name') || '';
        const cat = item.getAttribute('data-category') || '';
        const notes = item.getAttribute('data-notes') || '';
        if (!term || name.includes(term) || cat.includes(term) || notes.includes(term)) {
            item.style.display = 'block';
            outboundVisible++;
        } else {
            item.style.display = 'none';
        }
    });

    const outboundNoResults = document.getElementById('outboundNoResults');
    if (outboundNoResults) {
        outboundNoResults.style.display = (outboundVisible === 0 && document.querySelectorAll('.outbound-file-item').length > 0) ? 'block' : 'none';
    }
}

function clearFileSearch() {
    const input = document.getElementById('fileSearchInput');
    if (input) {
        input.value = '';
        filterPortalFiles('');
        input.focus();
    }
}

/**
 * Görünüm Modu Değiştirici: Paket / Grup Görünümü vs Ayrı Ayrı Kartlar
 */
function switchPortalViewMode(mode) {
    const isGrouped = mode === 'grouped';
    
    const inGrouped = document.getElementById('inboundGroupedContainer');
    const inSingle = document.getElementById('inboundSingleContainer');
    const outGrouped = document.getElementById('outboundGroupedContainer');
    const outSingle = document.getElementById('outboundSingleContainer');
    
    if (inGrouped) inGrouped.style.display = isGrouped ? 'flex' : 'none';
    if (inSingle) inSingle.style.display = isGrouped ? 'none' : 'flex';
    if (outGrouped) outGrouped.style.display = isGrouped ? 'flex' : 'none';
    if (outSingle) outSingle.style.display = isGrouped ? 'none' : 'flex';
    
    const btnGrouped = document.getElementById('btnModeGrouped');
    const btnSingle = document.getElementById('btnModeSingle');
    if (btnGrouped && btnSingle) {
        if (isGrouped) {
            btnGrouped.style.background = '#0284c7';
            btnGrouped.style.color = '#ffffff';
            btnGrouped.style.fontWeight = '800';
            btnGrouped.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';
            btnSingle.style.background = 'transparent';
            btnSingle.style.color = '#64748b';
            btnSingle.style.fontWeight = '700';
            btnSingle.style.boxShadow = 'none';
        } else {
            btnSingle.style.background = '#0284c7';
            btnSingle.style.color = '#ffffff';
            btnSingle.style.fontWeight = '800';
            btnSingle.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';
            btnGrouped.style.background = 'transparent';
            btnGrouped.style.color = '#64748b';
            btnGrouped.style.fontWeight = '700';
            btnGrouped.style.boxShadow = 'none';
        }
    }
    
    try {
        localStorage.setItem('vendor_portal_view_mode', mode);
    } catch(e) {}
}

/**
 * Dikkate Alma Modalı
 */
function openVoidModal(fileId, fileName) {
    document.getElementById('voidFileName').textContent = fileName;
    const form = document.getElementById('voidMarkerForm');
    form.action = `{{ url('/vendor-portal/' . $vendor->access_token . '/files') }}/${fileId}/void`;
    document.getElementById('voidMarkerModal').style.display = 'flex';
}

function closeVoidModal() {
    document.getElementById('voidMarkerModal').style.display = 'none';
}

/**
 * Sürükle-Bırak (Drag & Drop) ve Çift Tıklama Koruması
 */
document.addEventListener('DOMContentLoaded', function () {
    // Kayıtlı görünüm modunu yükle (Varsayılan: Paket / Grup Görünümü)
    try {
        const savedViewMode = localStorage.getItem('vendor_portal_view_mode') || 'grouped';
        switchPortalViewMode(savedViewMode);
    } catch (e) {
        switchPortalViewMode('grouped');
    }

    const dropzone = document.getElementById('portalDropzone');
    if (dropzone) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.style.borderColor = '#0284c7';
                dropzone.style.background = '#f0f9ff';
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.style.borderColor = '#cbd5e1';
                dropzone.style.background = '#f8fafc';
            }, false);
        });

        dropzone.addEventListener('drop', function (e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFilesSelected(files);
        }, false);
    }

    // Modal dışına tıklayınca kapatma
    const voidModal = document.getElementById('voidMarkerModal');
    if (voidModal) {
        voidModal.addEventListener('click', function (e) {
            if (e.target === this) closeVoidModal();
        });
    }

    // Form Gönderim & Çift Tıklama Koruması
    const uploadForm = document.getElementById('vendorUploadForm');
    const uploadBtn = document.getElementById('btnUploadSubmit');
    if (uploadForm && uploadBtn) {
        uploadForm.addEventListener('submit', function (e) {
            if (uploadBtn.disabled) {
                e.preventDefault();
                return false;
            }

            if (selectedFilesList.length === 0) {
                e.preventDefault();
                alert('Lütfen Köksan\'a göndermek için en az bir adet geçerli belge seçiniz.');
                return false;
            }

            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<span>⏳ Belgeler Gönderiliyor...</span>';
            uploadBtn.style.opacity = '0.75';
            uploadBtn.style.cursor = 'not-allowed';
        });
    }

    const voidForm = document.getElementById('voidMarkerForm');
    const voidBtn = document.getElementById('btnVoidSubmit');
    if (voidForm && voidBtn) {
        voidForm.addEventListener('submit', function (e) {
            if (voidBtn.disabled) {
                e.preventDefault();
                return false;
            }
            voidBtn.disabled = true;
            voidBtn.innerHTML = '<span>⏳ İşleniyor...</span>';
            voidBtn.style.opacity = '0.75';
            voidBtn.style.cursor = 'not-allowed';
        });
    }
});
</script>
@endsection