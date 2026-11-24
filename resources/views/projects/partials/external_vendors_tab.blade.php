@php
    $user = auth()->user();
    $canManageVendors = $user && ($user->isSuperAdmin()
        || $user->isAdmin()
        || $project->manager_id === $user->id
        || $project->sponsor_id === $user->id
        || $project->stakeholders->where('user_id', $user->id)->isNotEmpty());

    $canApproveVendors = $user && ($user->isSuperAdmin()
        || $user->isAdmin()
        || $project->manager_id === $user->id
        || $project->sponsor_id === $user->id);

    $isSuperAdmin = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();

    $vendors = $project->externalVendors;
    $totalVendors = $vendors->count();
    $activeVendors = $vendors->where('status', 'approved')->count();
    $pendingInboundTotal = 0;
    $outboundTotal = 0;
    foreach ($vendors as $v) {
        $pendingInboundTotal += $v->inboundFiles->where('status', 'pending_review')->where('is_void', false)->count();
        $outboundTotal += $v->outboundFiles->count();
    }
@endphp

<!-- CSS: GARANTİLİ MERKEZİ MODAL & ERGONOMİK KURUMSAL DÜZEN -->
<style>
    /* İnceleme Bekleyen Belgeler İçin Yanıp Yanıp Sönen (Blinking Flashing Warning) Uyarı Animasyonu */
    @keyframes pulseWarningBlink {

        0%,
        100% {
            opacity: 1;
            transform: scale(1);
            background-color: #dc2626 !important;
            box-shadow: 0 0 12px rgba(220, 38, 38, 0.85);
            color: #ffffff !important;
        }

        50% {
            opacity: 0.25;
            transform: scale(1.05);
            background-color: #ef4444 !important;
            box-shadow: 0 0 0 rgba(220, 38, 38, 0);
            color: #ffffff !important;
        }
    }

    .pulse-alert-badge {
        animation: pulseWarningBlink 1.1s infinite ease-in-out !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        cursor: pointer !important;
        transition: transform 0.2s ease !important;
    }

    .pulse-alert-badge:hover {
        transform: scale(1.08) !important;
    }

    .vendor-portal-modal {
        position: fixed !important;
        inset: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: rgba(15, 23, 42, 0.65) !important;
        backdrop-filter: blur(4px) !important;
        -webkit-backdrop-filter: blur(4px) !important;
        z-index: 105000 !important;
        display: none;
        align-items: center !important;
        justify-content: center !important;
        padding: 16px !important;
        box-sizing: border-box !important;
        overflow-y: auto !important;
    }

    .vendor-portal-modal.active {
        display: flex !important;
    }

    .vendor-portal-modal .modal-dialog {
        background: #ffffff !important;
        color: #0f172a !important;
        border-radius: 12px !important;
        border: none !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4) !important;
        overflow: hidden !important;
        width: 100% !important;
        margin: auto !important;
        max-height: calc(100vh - 32px) !important;
        display: flex !important;
        flex-direction: column !important;
    }

    .vendor-portal-modal .modal-content {
        background: #ffffff !important;
        color: #0f172a !important;
        border: none !important;
        border-radius: 12px !important;
        display: flex !important;
        flex-direction: column !important;
        height: auto !important;
        max-height: calc(100vh - 32px) !important;
        min-height: 0 !important;
        overflow: hidden !important;
    }

    .vendor-portal-modal form {
        display: flex !important;
        flex-direction: column !important;
        flex: 1 1 auto !important;
        min-height: 0 !important;
        max-height: 100% !important;
        overflow: hidden !important;
        margin: 0 !important;
    }

    .vendor-portal-modal .modal-header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
        flex-shrink: 0 !important;
        padding: 12px 18px !important;
    }

    .vendor-portal-modal .modal-body {
        background: #ffffff !important;
        color: #1e293b !important;
        overflow-y: auto !important;
        flex: 1 1 auto !important;
        min-height: 0 !important;
        padding: 16px 20px !important;
    }

    .vendor-portal-modal .modal-body::-webkit-scrollbar {
        width: 6px;
    }

    .vendor-portal-modal .modal-body::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }

    .vendor-portal-modal .modal-body::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    .vendor-portal-modal .modal-body::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .vendor-portal-modal .modal-footer {
        background: #f8fafc !important;
        border-top: 1px solid #e2e8f0 !important;
        flex-shrink: 0 !important;
        padding: 10px 18px !important;
        display: flex !important;
        justify-content: flex-end !important;
        gap: 8px !important;
    }

    /* Modal Form Alanları ve Başlık Aralıkları (Ekrana Sığan Ergonomik Düzen) */
    .vendor-modal-field-group {
        display: block !important;
        margin-bottom: 14px !important;
    }

    .vendor-portal-modal label,
    .vendor-portal-modal .form-label {
        color: #1e293b !important;
        font-weight: 700 !important;
        font-size: 11.5px !important;
        margin-top: 0 !important;
        margin-bottom: 5px !important;
        display: block !important;
        text-transform: uppercase !important;
        letter-spacing: 0.3px !important;
    }

    .vendor-portal-modal input.form-control,
    .vendor-portal-modal select.form-control,
    .vendor-portal-modal textarea.form-control {
        background: #ffffff !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 7px !important;
        font-size: 12.5px !important;
        padding: 7px 11px !important;
        width: 100% !important;
        box-sizing: border-box !important;
        margin-bottom: 0 !important;
    }

    .vendor-portal-modal input.form-control:focus,
    .vendor-portal-modal select.form-control:focus,
    .vendor-portal-modal textarea.form-control:focus {
        border-color: #0284c7 !important;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15) !important;
        outline: none !important;
    }

    .custom-modal-close-btn {
        background: transparent !important;
        border: none !important;
        color: #ffffff !important;
        font-size: 20px !important;
        line-height: 1 !important;
        padding: 4px 8px !important;
        border-radius: 6px !important;
        cursor: pointer !important;
        opacity: 0.85;
        transition: all 0.15s ease;
    }

    .custom-modal-close-btn:hover {
        opacity: 1;
        background: rgba(255, 255, 255, 0.15) !important;
    }

    .vendor-kpi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 14px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    .vendor-table-row:hover {
        background-color: #f8fafc;
    }

    /* Modal Outbound Dosya Yükleme Dropzone ve Önizleme Kartları */
    .modal-file-dropzone {
        border: 2px dashed #94a3b8;
        border-radius: 10px;
        background: #f8fafc;
        text-align: center;
        padding: 18px 14px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .modal-file-dropzone:hover {
        border-color: #0284c7;
        background: #f0f9ff;
    }

    .modal-selected-files-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
        gap: 8px;
        margin-top: 8px;
        max-height: 180px;
        overflow-y: auto;
        padding-right: 4px;
    }

    .modal-selected-file-card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 7px 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: transform 0.15s ease, border-color 0.15s ease;
    }

    .modal-selected-file-card:hover {
        border-color: #0284c7;
    }

    .modal-file-remove-btn {
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fecaca;
        border-radius: 6px;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        cursor: pointer;
        flex-shrink: 0;
        transition: all 0.15s ease;
        padding: 0;
        line-height: 1;
    }

    .modal-file-remove-btn:hover {
        background: #dc2626;
        color: #ffffff;
        border-color: #dc2626;
        transform: scale(1.1);
    }
</style>

<div class="card mb-4"
    style="border-radius: 14px; border: 1px solid #e2e8f0; box-shadow: 0 4px 14px rgba(0,0,0,0.04); overflow: hidden;">
    <!-- ÜST BAŞLIK VE HIZLI AKSİYON -->
    <div class="card-header"
        style="background: linear-gradient(135deg, #091325 0%, #0f172a 100%); color: #ffffff; padding: 20px 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span style="font-size: 22px;">🌐</span>
                <h4 style="margin: 0; font-size: 16px; font-weight: 800; color: #ffffff;">Dış Paydaş, Taşeron &
                    Tedarikçi Portalları</h4>
                <span class="badge"
                    style="background: rgba(56, 189, 248, 0.2); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4); font-size: 11px;">
                    {{ $totalVendors }} Kayıtlı Firma
                </span>
            </div>
            <div style="font-size: 12.5px; color: #94a3b8; max-width: 820px; line-height: 1.5;">
                Dış firmalarla sıfır bilgi izolasyonlu (iç bütçe ve hassas verileri görmeyen) güvenli teknik belge
                paylaşımı. Muayene raporları, kalite testleri ve şartnameler karşılıklı onay ve loglama ile yönetilir.
            </div>
        </div>

        <div>
            @if($canManageVendors)
                <button type="button" class="btn btn-primary" onclick="openVendorModal('addVendorModal')"
                    style="font-weight: 700; font-size: 13px; padding: 10px 20px; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px; background: #0284c7; border: none; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35); cursor: pointer;">
                    <span>🏢</span> + Yeni Dış Paydaş (Firma) Ekle
                </button>
            @endif
        </div>
    </div>

    <!-- KPI ÖZET KARTLARI -->
    <div
        style="background: #f8fafc; padding: 16px 24px; border-bottom: 1px solid #e2e8f0; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
        <div class="vendor-kpi-card">
            <div
                style="width: 44px; height: 44px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                🏢
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Aktif Dış
                    Paydaş</div>
                <div style="font-size: 18px; font-weight: 800; color: #0f172a;">{{ $activeVendors }} <span
                        style="font-size: 12px; font-weight: 500; color: #64748b;">/ {{ $totalVendors }} Firma</span>
                </div>
            </div>
        </div>

        <div class="vendor-kpi-card"
            style="{{ $pendingInboundTotal > 0 ? 'border-color: #fca5a5; background: #fff5f5;' : '' }}">
            <div
                style="width: 44px; height: 44px; border-radius: 10px; background: {{ $pendingInboundTotal > 0 ? '#fee2e2' : '#f1f5f9' }}; color: {{ $pendingInboundTotal > 0 ? '#dc2626' : '#64748b' }}; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                📥
            </div>
            <div>
                <div
                    style="font-size: 11px; font-weight: 700; color: {{ $pendingInboundTotal > 0 ? '#b91c1c' : '#64748b' }}; text-transform: uppercase;">
                    İnceleme Bekleyen Belge</div>
                <div
                    style="font-size: 18px; font-weight: 800; color: {{ $pendingInboundTotal > 0 ? '#dc2626' : '#0f172a' }}; display: flex; align-items: center; gap: 8px; flex-wrap: nowrap;">
                    <span>{{ $pendingInboundTotal }} Adet</span>
                    @if($pendingInboundTotal > 0)
                        <span class="badge pulse-alert-badge"
                            style="background: #dc2626; color: #ffffff; font-size: 10.5px; font-weight: 800; white-space: nowrap; padding: 4px 9px; border-radius: 6px;">
                            🚨 İnceleme Gerekli
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="vendor-kpi-card">
            <div
                style="width: 44px; height: 44px; border-radius: 10px; background: #f0fdf4; color: #166534; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                📤
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Paylaşılan
                    Teknik Çizim/Şartname</div>
                <div style="font-size: 18px; font-weight: 800; color: #0f172a;">{{ $outboundTotal }} Adet</div>
            </div>
        </div>
    </div>

    <!-- FİRMA YÖNETİM MERKEZİ (TABLO / GRID GÖRÜNÜMÜ) -->
    <div class="card-body" style="padding: 24px; background: #ffffff;">
        @if($vendors->isEmpty())
            <div
                style="text-align: center; padding: 48px 20px; background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px;">
                <div style="font-size: 44px; margin-bottom: 12px;">🏢</div>
                <h5 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">Henüz Dış Paydaş
                    Eklenmedi</h5>
                <p style="font-size: 13px; color: #64748b; max-width: 540px; margin: 0 auto 20px;">
                    Bu projede dış yüklenici (otomasyon, talaşlı imalat, kaynak kontrolü vb.) firmalarla karşılıklı dosya
                    transferi yapmak için yukarıdaki butondan firma yetkilisini ekleyebilirsiniz.
                </p>
                @if($canManageVendors)
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="openVendorModal('addVendorModal')"
                        style="font-weight: 600; padding: 8px 18px; border-radius: 6px;">
                        + İlk Dış Paydaş Firmayı Ekle
                    </button>
                @endif
            </div>
        @else
            <div class="table-responsive" style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
                <table class="table mb-0" style="vertical-align: middle;">
                    <thead
                        style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; font-size: 11.5px; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">
                        <tr>
                            <th style="padding: 12px 16px;">Firma & Çalışma Kapsamı</th>
                            <th style="padding: 12px 16px;">Yetkili & İletişim</th>
                            <th style="padding: 12px 16px;">Belge Kategorileri</th>
                            <th style="padding: 12px 16px; text-align: center;">Durum</th>
                            <th style="padding: 12px 16px; text-align: center;">Dosya & İnceleme</th>
                            <th style="padding: 12px 16px; text-align: center;">Giriş & E-Posta</th>
                            <th style="padding: 12px 16px; text-align: right;">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 13px;">
                        @foreach($vendors as $vendor)
                            @php
                                $inboundFiles = $vendor->inboundFiles;
                                $outboundFiles = $vendor->outboundFiles;
                                $pendingInboundCount = $inboundFiles->where('status', 'pending_review')->where('is_void', false)->count();
                                $totalFilesCount = $inboundFiles->count() + $outboundFiles->count();
                            @endphp
                            <tr class="vendor-table-row" style="border-bottom: 1px solid #f1f5f9;">
                                <!-- FİRMA & KAPSAM -->
                                <td style="padding: 16px;">
                                    <div
                                        style="font-weight: 800; color: #0f172a; font-size: 14.5px; display: flex; align-items: center; gap: 8px;">
                                        <span style="font-size: 18px;">🏢</span>
                                        {{ $vendor->company_name }}
                                    </div>
                                    <div
                                        style="font-size: 12px; color: #64748b; margin-top: 4px; line-height: 1.4; max-width: 260px;">
                                        {{ Str::limit($vendor->service_scope, 80) }}
                                    </div>
                                    <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">
                                        Ekleyen: <strong>{{ $vendor->addedBy?->name ?? 'Kullanıcı' }}</strong>
                                        ({{ $vendor->created_at->format('d.m.Y H:i') }})
                                    </div>
                                </td>

                                <!-- YETKİLİ & İLETİŞİM -->
                                <td style="padding: 16px;">
                                    <div style="font-weight: 700; color: #1e293b;">👤 {{ $vendor->contact_person }}</div>
                                    <div style="font-size: 12px; color: #0284c7; margin-top: 2px;">
                                        <a href="mailto:{{ $vendor->email }}" style="color: #0284c7; text-decoration: none;">✉️
                                            {{ $vendor->email }}</a>
                                    </div>
                                    @if($vendor->phone)
                                        <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">📞 {{ $vendor->phone }}
                                        </div>
                                    @endif
                                </td>

                                <!-- KATEGORİLER -->
                                <td style="padding: 16px; text-align: center; white-space: nowrap;">
                                    <div style="display: inline-flex; align-items: center; gap: 6px;">
                                        <span
                                            title="Firma İzinli Kategorileri: {{ implode(', ', $vendor->categories_list) }}"
                                            style="font-weight: 800; font-size: 12px; color: #0369a1; background: #e0f2fe; border: 1.5px solid #bae6fd; border-radius: 8px; padding: 5px 12px; display: inline-flex; align-items: center; gap: 6px; cursor: help; box-shadow: 0 1px 2px rgba(2, 132, 199, 0.06);">
                                            <span>📁</span> {{ count($vendor->categories_list) }} Kategori
                                        </span>
                                        @if($canManageVendors)
                                            <button type="button" class="btn btn-xs btn-outline-secondary"
                                                onclick="openEditCategoriesModal({{ $vendor->id }}, '{{ addslashes($vendor->company_name) }}', '{{ addslashes(implode(', ', $vendor->categories_list)) }}')"
                                                title="Kategorileri Düzenle"
                                                style="padding: 4px 8px; font-size: 11px; border-radius: 6px; font-weight: 700; color: #475569;">
                                                ✏️
                                            </button>
                                        @endif
                                    </div>
                                </td>

                                <!-- DURUM -->
                                <td style="padding: 16px; text-align: center; white-space: nowrap;">
                                    @if($vendor->status === 'approved')
                                        <span
                                            style="display: inline-flex; align-items: center; gap: 7px; background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); color: #065f46; border: 1.5px solid #a7f3d0; border-radius: 9999px; font-weight: 800; font-size: 11.5px; padding: 5px 14px; white-space: nowrap; box-shadow: 0 1px 3px rgba(16, 185, 129, 0.12); letter-spacing: 0.2px;">
                                            <span
                                                style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block; box-shadow: 0 0 6px #10b981;"></span>
                                            Onaylı & Aktif
                                        </span>
                                    @elseif($vendor->status === 'pending_approval')
                                        <span
                                            style="display: inline-flex; align-items: center; gap: 6px; background: #fffbeb; color: #b45309; border: 1px solid #fde68a; border-radius: 20px; font-weight: 800; font-size: 12px; padding: 5px 14px; white-space: nowrap;">
                                            <span
                                                style="width: 8px; height: 8px; border-radius: 50%; background: #f59e0b; display: inline-block;"></span>
                                            Onay Bekliyor
                                        </span>
                                        @if($canApproveVendors)
                                            <div style="margin-top: 8px; display: flex; gap: 6px; justify-content: center;">
                                                <form
                                                    action="{{ route('projects.vendors.approve', ['project' => $project->id, 'vendor' => $vendor->id]) }}"
                                                    method="POST" style="margin:0;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-xs btn-success"
                                                        style="font-weight: 700; font-size: 11px; padding: 3px 8px; border-radius: 6px;">Onayla</button>
                                                </form>
                                                <button type="button" class="btn btn-xs btn-outline-danger"
                                                    onclick="openRejectVendorModal({{ $vendor->id }}, '{{ addslashes($vendor->company_name) }}')"
                                                    style="font-weight: 700; font-size: 11px; padding: 3px 8px; border-radius: 6px;">Reddet</button>
                                            </div>
                                        @endif
                                    @elseif($vendor->status === 'rejected')
                                        <span
                                            style="display: inline-flex; align-items: center; gap: 6px; background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; border-radius: 20px; font-weight: 800; font-size: 12px; padding: 5px 14px; white-space: nowrap;">
                                            <span
                                                style="width: 8px; height: 8px; border-radius: 50%; background: #ef4444; display: inline-block;"></span>
                                            Reddedildi
                                        </span>
                                    @elseif($vendor->status === 'revoked')
                                        <span
                                            style="display: inline-flex; align-items: center; gap: 6px; background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; border-radius: 20px; font-weight: 800; font-size: 12px; padding: 5px 14px; white-space: nowrap;">
                                            <span
                                                style="width: 8px; height: 8px; border-radius: 50%; background: #94a3b8; display: inline-block;"></span>
                                            İptal Edildi
                                        </span>
                                    @endif
                                </td>

                                <!-- DOSYA & İNCELEME -->
                                <td style="padding: 16px; text-align: center;">
                                    <div style="display: flex; flex-direction: column; align-items: center; gap: 6px;">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick="openVendorFilesModal({{ $vendor->id }})"
                                            style="font-weight: 700; font-size: 12px; padding: 5px 12px; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                                            <span>📂</span> Dosyaları Yönet ({{ $totalFilesCount }})
                                        </button>
                                        @if($pendingInboundCount > 0)
                                            <!-- YANIP SÖNEN UYARI ROZETİ (BLINKING PULSE) -->
                                            <span class="badge pulse-alert-badge" onclick="openVendorFilesModal({{ $vendor->id }})"
                                                style="background: #dc2626; color: #ffffff; font-size: 11px; font-weight: 800; padding: 5px 10px; border-radius: 6px; letter-spacing: 0.3px;"
                                                title="İnceleme bekleyen dosyaları görüntülemek için tıklayınız">
                                                🚨 {{ $pendingInboundCount }} İnceleme Bekliyor!
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- GİRİŞ & E-POSTA İLETİMİ -->
                                <td style="padding: 16px; text-align: center;">
                                    @if($vendor->status === 'approved')
                                        <!-- OTOMATİK E-POSTA İLETİM BUTONU -->
                                        <form
                                            action="{{ route('projects.vendors.send_credentials', ['project' => $project->id, 'vendor' => $vendor->id]) }}"
                                            method="POST" style="margin: 0;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-info"
                                                style="font-size: 11.5px; font-weight: 700; padding: 4px 10px; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                                                <span>✉️</span> Firmaya E-Posta Gönder
                                            </button>
                                        </form>

                                        <!-- GÖNDERİM DURUMU & MASKE ŞİFRE -->
                                        <div style="font-size: 11px; margin-top: 4px; color: #64748b;">
                                            @if($vendor->credentials_sent_at)
                                                <span style="color: #047857; font-weight: 600;">✓ İletildi:
                                                    {{ $vendor->credentials_sent_at->format('d.m H:i') }}</span>
                                            @else
                                                <span style="color: #b45309; font-weight: 600;">⚠️ E-Posta Gönderilmedi</span>
                                            @endif
                                        </div>

                                        <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">
                                            Şifre: <code style="color: #475569; font-weight: 700;"
                                                id="passkeyMask_{{ $vendor->id }}">{{ $vendor->masked_passkey }}</code>
                                            @if($isSuperAdmin)
                                                <button type="button" class="btn btn-xs btn-link text-muted p-0"
                                                    onclick="togglePasskey({{ $vendor->id }}, '{{ $vendor->passkey }}')"
                                                    title="Şifreyi Aç/Kapat (Superadmin Yetkisi)"
                                                    style="font-size: 11px; text-decoration: none; margin-left: 4px; cursor: pointer;">👁️</button>
                                            @endif
                                        </div>
                                    @else
                                        <span style="font-size: 11px; color: #94a3b8;">Onay Bekleniyor</span>
                                    @endif
                                </td>

                                <!-- AKSİYONLAR -->
                                <td style="padding: 16px; text-align: right;">
                                    <div style="display: flex; justify-content: flex-end; gap: 6px; flex-wrap: wrap;">
                                        @if($vendor->status === 'approved' && $canManageVendors)
                                            <button type="button" class="btn btn-sm btn-primary"
                                                onclick="openOutboundUploadModal({{ $vendor->id }}, '{{ addslashes($vendor->company_name) }}')"
                                                style="font-size: 11.5px; font-weight: 600; padding: 4px 10px; border-radius: 6px; background: #0284c7;"
                                                title="Firmaya Belge Yükle">
                                                <span>📤</span> Dosya Yükle
                                            </button>
                                        @endif

                                        @if($vendor->status === 'approved')
                                            <a href="{{ $vendor->portal_url }}" target="_blank"
                                                class="btn btn-sm btn-outline-secondary"
                                                style="font-size: 11.5px; padding: 4px 8px; border-radius: 6px;" title="Portalı Aç">
                                                🔗 Portal
                                            </a>
                                        @endif

                                        @if($canManageVendors && $vendor->status !== 'revoked')
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="openRevokeVendorModal({{ $vendor->id }}, '{{ addslashes($vendor->company_name) }}')"
                                                style="font-size: 11.5px; padding: 4px 8px; border-radius: 6px;"
                                                title="Firmayı Çıkar">
                                                🚫 Çıkar
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>


<!-- ================================================================= -->
<!-- GARANTİLİ MERKEZİ MODALLAR (KURUMSAL TEMİZ & FERAH TASARIM)        -->
<!-- ================================================================= -->

<!-- MODAL 1: YENİ DIŞ PAYDAŞ EKLE -->
<div class="vendor-portal-modal" id="addVendorModal" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 580px;">
        <div class="modal-content">
            <div class="modal-header"
                style="background: linear-gradient(135deg, #091325 0%, #0f172a 100%); color: #ffffff; padding: 12px 18px;">
                <h5 class="modal-title"
                    style="font-size: 14.5px; font-weight: 700; color: #ffffff; display: flex; align-items: center; gap: 8px; margin: 0;">
                    <span>🏢</span> Projeye Yeni Dış Paydaş (Firma) Ekle
                </h5>
                <button type="button" class="custom-modal-close-btn"
                    onclick="closeVendorModal('addVendorModal')">✕</button>
            </div>
            <form id="addVendorForm" action="{{ route('projects.vendors.store', ['project' => $project->id]) }}"
                method="POST">
                @csrf
                <div class="modal-body" style="padding: 16px 20px;">
                    <div class="vendor-modal-field-group">
                        <label class="form-label">FİRMA TİCARİ UNVANI <span class="text-danger">*</span></label>
                        <input type="text" name="company_name" required placeholder="Örn: Casan Otomasyon Ltd. Şti."
                            class="form-control" style="height: 38px;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                        <div>
                            <label class="form-label">FİRMA YETKİLİSİ (AD SOYAD) <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="contact_person" required placeholder="Örn: Ahmet YILMAZ"
                                class="form-control" style="height: 38px;">
                        </div>
                        <div>
                            <label class="form-label">YETKİLİ E-POSTA (ŞİFRE İLETİLİR) <span
                                    class="text-danger">*</span></label>
                            <input type="email" name="email" required placeholder="ahmet@casan.com" class="form-control"
                                style="height: 38px;">
                        </div>
                    </div>

                    <div class="vendor-modal-field-group">
                        <label class="form-label">TELEFON (OPSİYONEL)</label>
                        <input type="text" name="phone" placeholder="0532 ..." class="form-control"
                            style="height: 38px;">
                    </div>

                    <div class="vendor-modal-field-group">
                        <label class="form-label">ÇALIŞMA KONUSU / VERİLEN PARÇA & HİZMET <span
                                class="text-danger">*</span></label>
                        <textarea name="service_scope" required rows="2" class="form-control"
                            placeholder="Örn: Preform robotik taşıma hücresi mekanik montajı ve kaynak muayene testleri."></textarea>
                    </div>

                    <div class="vendor-modal-field-group">
                        <label class="form-label">BELGE KATEGORİLERİ (İSTEĞE BAĞLI, PAYDAŞA ÖZEL)</label>
                        <input type="text" name="allowed_categories" class="form-control"
                            placeholder="Örn: Çizim Parçaları, Jant Parçaları, Yedek Parçalar, Muayene Raporları"
                            style="height: 38px;">
                        <small style="font-size: 11px; color: #64748b; display: block; margin-top: 4px;">
                            Virgülle ayırarak firmanın yükleyebileceği özel kategorileri belirleyebilirsiniz. Boş
                            bırakırsanız standart teknik kategoriler atanır.
                        </small>
                    </div>

                    <div class="vendor-modal-field-group">
                        <label class="form-label">DAHİLİ NOTLAR (SADECE BİZ GÖRÜRÜZ)</label>
                        <input type="text" name="notes" placeholder="Örn: Satın alma onay no: SAT-2026-44"
                            class="form-control" style="height: 38px;">
                    </div>

                    @if($canApproveVendors)
                        <div
                            style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 10px 14px; margin-top: 10px;">
                            <label
                                style="display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 700; color: #166534; cursor: pointer; margin: 0; text-transform: none;">
                                <input type="checkbox" name="auto_approve" value="1" checked style="width: auto;">
                                <span>Yetkiniz bulunuyor: Doğrudan Onayla & Giriş Bilgilerini Firmaya İlet</span>
                            </label>
                        </div>
                    @else
                        <div
                            style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 10px 14px; font-size: 11px; color: #92400e; margin-top: 10px;">
                            ℹ️ Eklediğiniz firma, Proje Yöneticisi onayına sunulacak; onay verilene kadar dosya portalı
                            kilitli tutulacaktır.
                        </div>
                    @endif
                </div>
                <div class="modal-footer"
                    style="padding: 10px 18px; display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" class="btn btn-secondary btn-sm"
                        onclick="closeVendorModal('addVendorModal')">Vazgeç</button>
                    <button type="submit" class="btn btn-primary btn-sm"
                        style="font-weight: 700; padding: 7px 16px; background: #0284c7;">
                        <span>🏢</span> Firmayı Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- MODAL 2: FİRMA DOSYALARI YÖNETİM MERKEZİ (DEDICATED FILE HUB) -->
@foreach($vendors as $vendor)
    @php
        $vInbound = $vendor->inboundFiles;
        $vOutbound = $vendor->outboundFiles;
    @endphp
    <div class="vendor-portal-modal" id="vendorFilesModal_{{ $vendor->id }}" aria-hidden="true">
        <div class="modal-dialog" style="max-width: 920px;">
            <div class="modal-content">
                <div class="modal-header"
                    style="background: linear-gradient(135deg, #091325 0%, #0f172a 100%); color: #ffffff; padding: 12px 18px;">
                    <div>
                        <h5 class="modal-title"
                            style="font-size: 14.5px; font-weight: 800; color: #ffffff; display: flex; align-items: center; gap: 8px; margin: 0;">
                            <span>📂</span> {{ $vendor->company_name }} &bull; Belge ve Rapor Transfer Merkezi
                        </h5>
                        <div style="font-size: 11.5px; color: #94a3b8; margin-top: 2px;">
                            Yetkili: {{ $vendor->contact_person }} ({{ $vendor->email }}) &bull; Kapsam:
                            {{ $vendor->service_scope }}
                        </div>
                    </div>
                    <button type="button" class="custom-modal-close-btn"
                        onclick="closeVendorModal('vendorFilesModal_{{ $vendor->id }}')">✕</button>
                </div>

                <div class="modal-body" style="padding: 14px 18px; background: #f8fafc;">
                    <!-- İKİ KOLONLU BELGE MERKEZİ -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <!-- SOL: FİRMADAN GELENLER (INBOUND) -->
                        <div
                            style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); display: flex; flex-direction: column;">
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9;">
                                <div
                                    style="font-size: 13px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                                    <span>📥</span> Firmadan Gelen Muayene & Test Raporları
                                </div>
                                <span class="badge"
                                    style="background: #e0f2fe; color: #0369a1; font-weight: 700;">{{ $vInbound->count() }}
                                    Belge</span>
                            </div>

                            @if($vInbound->isEmpty())
                                <div style="text-align: center; padding: 30px 10px; color: #94a3b8; font-size: 12px;">
                                    Firma tarafından henüz sisteme bir belge yüklenmedi.
                                </div>
                            @else
                                <div
                                    style="display: flex; flex-direction: column; gap: 10px; max-height: 400px; overflow-y: auto; padding-right: 4px;">
                                    @foreach($vInbound as $file)
                                        <div
                                            style="padding: 12px 14px; border: 1.5px solid {{ $file->is_void ? '#fed7aa' : ($file->status === 'pending_review' ? '#f87171' : '#e2e8f0') }}; border-left: {{ !$file->is_void && $file->status === 'pending_review' ? '4px solid #dc2626' : '1.5px solid ' . ($file->is_void ? '#fed7aa' : ($file->status === 'pending_review' ? '#f87171' : '#e2e8f0')) }}; border-radius: 8px; background: {{ $file->is_void ? '#fffbeb' : ($file->status === 'pending_review' ? '#fff5f5' : '#ffffff') }}; font-size: 12px;">
                                            <div
                                                style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
                                                <div>
                                                    <div
                                                        style="font-weight: 800; color: {{ $file->is_void ? '#9a3412' : ($file->status === 'pending_review' ? '#991b1b' : '#0f172a') }}; {{ $file->is_void ? 'text-decoration: line-through;' : '' }}">
                                                        {{ $file->file_icon }} {{ $file->original_name }}
                                                    </div>
                                                    <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                                        📁 {{ $file->category }} &bull; {{ $file->formatted_size }} &bull;
                                                        {{ $file->created_at->format('d.m.Y H:i') }}
                                                    </div>
                                                </div>

                                                <!-- DURUM ROZETİ (İNCELEME BEKLİYORSA YANIP SÖNER) -->
                                                <div>
                                                    @if($file->is_void)
                                                        <span class="badge"
                                                            style="background: #fef3c7; color: #92400e; font-size: 10px;">GEÇERSİZ</span>
                                                    @elseif($file->status === 'approved')
                                                        <span class="badge"
                                                            style="background: #ecfdf5; color: #047857; font-size: 10px; font-weight: 800;">✅
                                                            ONAYLANDI</span>
                                                    @elseif($file->status === 'rejected')
                                                        <span class="badge"
                                                            style="background: #fee2e2; color: #991b1b; font-size: 10px; font-weight: 800;">❌
                                                            REDDEDİLDİ</span>
                                                    @else
                                                        <span class="badge pulse-alert-badge"
                                                            style="background: #dc2626; color: #ffffff; font-size: 10.5px; font-weight: 800; padding: 4px 9px; border-radius: 6px; letter-spacing: 0.3px;">
                                                            🚨 İNCELEME BEKLİYOR
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            @if($file->notes)
                                                <div
                                                    style="margin-top: 8px; font-size: 11.5px; color: #0369a1; background: #e0f2fe; border: 1px solid #bae6fd; padding: 6px 10px; border-radius: 6px; display: flex; align-items: flex-start; gap: 6px;">
                                                    <span style="font-size: 13px;">💬</span>
                                                    <div>
                                                        <strong>Firma Açıklama / Notu:</strong> {{ $file->notes }}
                                                    </div>
                                                </div>
                                            @endif

                                            @if($file->is_void && $file->void_reason)
                                                <div
                                                    style="margin-top: 8px; font-size: 11px; color: #b45309; background: rgba(254, 243, 199, 0.5); padding: 5px 8px; border-radius: 4px;">
                                                    ⚠️ <strong>Dikkate Alma Nedeni:</strong> {{ $file->void_reason }}
                                                </div>
                                            @endif

                                            @if($file->review_note)
                                                <div
                                                    style="margin-top: 8px; font-size: 11px; color: #475569; background: #f8fafc; padding: 5px 8px; border-radius: 4px;">
                                                    📝 <strong>İnceleme Notu:</strong> {{ $file->review_note }}
                                                </div>
                                            @endif

                                            <!-- İŞLEM BUTONLARI -->
                                            <div
                                                style="margin-top: 10px; display: flex; justify-content: flex-end; gap: 6px; align-items: center;">
                                                <a href="{{ route('projects.vendor_files.download', ['project' => $project->id, 'file' => $file->id]) }}"
                                                    class="btn btn-xs btn-outline-secondary"
                                                    style="font-size: 11px; font-weight: 700;">
                                                    ⬇️ İndir
                                                </a>

                                                @if(!$file->is_void && $canManageVendors)
                                                    @if($file->status === 'pending_review')
                                                        <button type="button" class="btn btn-xs btn-success"
                                                            onclick="openReviewFileModal({{ $file->id }}, 'approved', '{{ addslashes($file->original_name) }}')"
                                                            style="font-size: 11px; font-weight: 700;">
                                                            ✅ Onayla
                                                        </button>
                                                        <button type="button" class="btn btn-xs btn-outline-danger"
                                                            onclick="openReviewFileModal({{ $file->id }}, 'rejected', '{{ addslashes($file->original_name) }}')"
                                                            style="font-size: 11px; font-weight: 700;">
                                                            ❌ Reddet
                                                        </button>
                                                    @endif
                                                    <button type="button" class="btn btn-xs btn-outline-warning"
                                                        onclick="openVoidInternalFileModal({{ $file->id }}, '{{ addslashes($file->original_name) }}')"
                                                        style="font-size: 11px;" title="Geçersiz olarak işaretle">
                                                        ⚠️ Dikkate Alma
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- SAĞ: KÖKSAN'DAN GÖNDERİLENLER (OUTBOUND) -->
                        <div
                            style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); display: flex; flex-direction: column;">
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9;">
                                <div
                                    style="font-size: 13px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                                    <span>📤</span> Firmaya Gönderdiğimiz Belgeler
                                </div>
                                <button type="button" class="btn btn-xs btn-primary"
                                    onclick="closeVendorModal('vendorFilesModal_{{ $vendor->id }}'); openOutboundUploadModal({{ $vendor->id }}, '{{ addslashes($vendor->company_name) }}')"
                                    style="font-size: 11px; font-weight: 700; background: #0284c7;">
                                    + Belge Gönder
                                </button>
                            </div>

                            @if($vOutbound->isEmpty())
                                <div style="text-align: center; padding: 30px 10px; color: #94a3b8; font-size: 12px;">
                                    Firmaya henüz gönderilmiş bir teknik çizim veya şartname bulunmuyor.
                                </div>
                            @else
                                <div
                                    style="display: flex; flex-direction: column; gap: 10px; max-height: 400px; overflow-y: auto; padding-right: 4px;">
                                    @foreach($vOutbound as $file)
                                        <div
                                            style="padding: 12px 14px; border: 1px solid {{ $file->is_void ? '#fed7aa' : '#e2e8f0' }}; border-radius: 8px; background: {{ $file->is_void ? '#fffbeb' : '#ffffff' }}; font-size: 12px;">
                                            <div
                                                style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
                                                <div>
                                                    <div
                                                        style="font-weight: 700; color: {{ $file->is_void ? '#9a3412' : '#0f172a' }}; {{ $file->is_void ? 'text-decoration: line-through;' : '' }}">
                                                        {{ $file->file_icon }} {{ $file->original_name }}
                                                    </div>
                                                    <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                                        📁 {{ $file->category }} &bull; {{ $file->formatted_size }} &bull;
                                                        {{ $file->created_at->format('d.m.Y H:i') }}
                                                    </div>
                                                </div>
                                                <div>
                                                    @if($file->is_void)
                                                        <span class="badge"
                                                            style="background: #fef3c7; color: #92400e; font-size: 10px;">GEÇERSİZ</span>
                                                    @elseif($file->is_visible_to_vendor)
                                                        <span class="badge"
                                                            style="background: #ecfdf5; color: #047857; font-size: 10px; font-weight: 700;">FİRMA
                                                            GÖRÜYOR</span>
                                                    @else
                                                        <span class="badge"
                                                            style="background: #f1f5f9; color: #64748b; font-size: 10px; font-weight: 700;">FİRMAYA
                                                            GİZLİ</span>
                                                    @endif
                                                </div>
                                            </div>

                                            @if($file->notes)
                                                <div
                                                    style="margin-top: 8px; font-size: 11.5px; color: #334155; background: #f1f5f9; border: 1px solid #e2e8f0; padding: 6px 10px; border-radius: 6px; display: flex; align-items: flex-start; gap: 6px;">
                                                    <span style="font-size: 13px;">📝</span>
                                                    <div>
                                                        <strong>Köksan Gönderim Notu:</strong> {{ $file->notes }}
                                                    </div>
                                                </div>
                                            @endif

                                            @if($file->is_void && $file->void_reason)
                                                <div
                                                    style="margin-top: 8px; font-size: 11px; color: #b45309; background: rgba(254, 243, 199, 0.5); padding: 5px 8px; border-radius: 4px;">
                                                    ⚠️ <strong>Dikkate Alma Nedeni:</strong> {{ $file->void_reason }}
                                                </div>
                                            @endif

                                            <!-- İŞLEM BUTONLARI -->
                                            <div
                                                style="margin-top: 10px; display: flex; justify-content: flex-end; gap: 6px; align-items: center;">
                                                <a href="{{ route('projects.vendor_files.download', ['project' => $project->id, 'file' => $file->id]) }}"
                                                    class="btn btn-xs btn-outline-secondary"
                                                    style="font-size: 11px; font-weight: 700;">
                                                    ⬇️ İndir
                                                </a>

                                                @if(!$file->is_void && $canManageVendors)
                                                    <form
                                                        action="{{ route('projects.vendor_files.toggle_visibility', ['project' => $project->id, 'file' => $file->id]) }}"
                                                        method="POST" style="margin: 0; display: inline;">
                                                        @csrf
                                                        <input type="hidden" name="is_visible"
                                                            value="{{ $file->is_visible_to_vendor ? 0 : 1 }}">
                                                        <button type="submit"
                                                            class="btn btn-xs {{ $file->is_visible_to_vendor ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                            style="font-size: 11px; font-weight: 700;">
                                                            {{ $file->is_visible_to_vendor ? '🔒 Firmaya Gizle' : '👁️ Firmaya Aç' }}
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-xs btn-outline-warning"
                                                        onclick="openVoidInternalFileModal({{ $file->id }}, '{{ addslashes($file->original_name) }}')"
                                                        style="font-size: 11px;">
                                                        ⚠️ Dikkate Alma
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="padding: 10px 18px;">
                    <button type="button" class="btn btn-secondary btn-sm"
                        onclick="closeVendorModal('vendorFilesModal_{{ $vendor->id }}')">Kapat</button>
                </div>
            </div>
        </div>
    </div>
@endforeach


<!-- MODAL 3: FİRMAYA BELGE GÖNDER (OUTBOUND UPLOAD - GELİŞMİŞ ÖNİZLEME & ÇARPI İLE ÇIKARMA) -->
<div class="vendor-portal-modal" id="uploadOutboundModal" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 580px;">
        <div class="modal-content">
            <div class="modal-header"
                style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff; padding: 14px 20px;">
                <h5 class="modal-title"
                    style="font-size: 15px; font-weight: 700; color: #ffffff; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span>📤</span> <span id="outboundVendorName"></span> Firmasına Belge Gönder
                </h5>
                <button type="button" class="custom-modal-close-btn"
                    onclick="closeVendorModal('uploadOutboundModal')">✕</button>
            </div>
            <form id="outboundUploadForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body" style="padding: 20px 22px;">
                    <!-- 1. DOKÜMAN KATEGORİSİ ALANI -->
                    <div class="vendor-modal-field-group" style="margin-bottom: 18px !important;">
                        <label class="form-label"
                            style="display: block; font-size: 11.5px; font-weight: 700; color: #1e293b; margin-bottom: 6px !important;">
                            DOKÜMAN KATEGORİSİ
                        </label>
                        <select name="category" class="form-control" style="height: 40px; font-size: 13px;">
                            <option value="teknik_sartname">📐 Teknik Şartname & Toleranslar</option>
                            <option value="imalat_cizimi">📐 İmalat & CAD/DWG Çizimi</option>
                            <option value="montaj_talimati">📝 Montaj & Devreye Alma Talimatı</option>
                            <option value="sozlesme_ek">📜 Sözleşme Eki & Protokol</option>
                            <option value="diger">📎 Diğer Teknik Belge</option>
                        </select>
                    </div>

                    <!-- 2. SÜRÜKLE-BIRAK & ÇOKLU DOSYA SEÇİM ALANI (FERAH & GELİŞMİŞ) -->
                    <div class="vendor-modal-field-group" style="margin-bottom: 18px !important;">
                        <label class="form-label"
                            style="display: block; font-size: 11.5px; font-weight: 700; color: #1e293b; margin-bottom: 6px !important;">
                            DOSYALARI SEÇİNİZ (TOPLU SEÇİM DESTEKLENİR, MAKS. 10 DOSYA) <span
                                class="text-danger">*</span>
                        </label>
                        
                        <div class="modal-file-dropzone" id="outboundDropzone"
                            onclick="document.getElementById('outboundFileInput').click()">
                            <div style="font-size: 26px; margin-bottom: 4px;">☁️</div>
                            <div style="font-size: 13px; font-weight: 700; color: #0284c7; margin-bottom: 2px;">
                                Dosyaları seçmek için tıklayın veya bu alana sürükleyip bırakın
                            </div>
                            <div style="font-size: 11px; color: #64748b; line-height: 1.4;">
                                PDF, DWG, DXF, STEP, CAD, XLSX, DOCX, ZIP vb. &bull; <span style="color: #0369a1; font-weight: 600;">(Tek seferde maks. 10 dosya, azami 50 MB/dosya)</span>
                            </div>
                            <input type="file" name="files[]" id="outboundFileInput" multiple style="display: none;"
                                onchange="handleOutboundFilesSelected(this.files)">
                        </div>

                        <!-- SEÇİLEN DOSYALAR ÖNİZLEME VE ÇARPI (✕) İLE ÇIKARMA ALANI -->
                        <div id="outboundSelectedPreviewBox" style="display: none; margin-top: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; padding-bottom: 4px; border-bottom: 1px solid #e2e8f0;">
                                <div style="font-size: 11.5px; font-weight: 700; color: #334155; display: flex; align-items: center; gap: 5px;">
                                    <span>📑</span> Gönderilmek Üzere Seçilen Dosyalar:
                                </div>
                                <span class="badge" id="outboundSelectedCountBadge"
                                    style="background: #e0f2fe; color: #0369a1; font-weight: 800; font-size: 10.5px;">
                                    0 / 10 Belge
                                </span>
                            </div>
                            <div class="modal-selected-files-grid" id="outboundCardsGrid"></div>
                        </div>
                    </div>

                    <!-- 3. AÇIKLAMA / GÖNDERİM NOTU ALANI -->
                    <div class="vendor-modal-field-group" style="margin-bottom: 18px !important;">
                        <label class="form-label"
                            style="display: block; font-size: 11.5px; font-weight: 700; color: #1e293b; margin-bottom: 6px !important;">
                            AÇIKLAMA / BİLGİLENDİRME NOTU <span
                                style="font-size: 11px; font-weight: 500; color: #64748b;">(Opsiyonel)</span>
                        </label>
                        <textarea name="notes" rows="2" class="form-control"
                            placeholder="Firmaya iletmek istediğiniz teknik revizyon, şartname veya inceleme notunu yazabilirsiniz..."
                            style="font-size: 12.5px; resize: vertical;"></textarea>
                    </div>

                    <!-- 4. FİRMA GÖRÜNÜRLÜK KUTUSU -->
                    <div
                        style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px 14px; margin-top: 10px; margin-bottom: 2px;">
                        <label
                            style="display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 700; color: #166534; cursor: pointer; margin: 0; text-transform: none;">
                            <input type="checkbox" name="is_visible_to_vendor" value="1" checked style="width: auto;">
                            <span>👁️ Yüklenen belgeler doğrudan dış firma tarafından görünsün</span>
                        </label>
                        <div style="font-size: 10.5px; color: #15803d; margin-top: 3px; margin-left: 22px;">
                            İşaretlenmezse belgeler yüklenir ancak siz "Firmaya Aç" diyene kadar gizli kalır.
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 12px 20px;">
                    <button type="button" class="btn btn-secondary btn-sm"
                        onclick="closeVendorModal('uploadOutboundModal')">Vazgeç</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="btnSubmitOutboundUpload"
                        style="font-weight: 700; background: #0284c7; padding: 8px 18px;">
                        <span>📤</span> Belgeleri Gönder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- MODAL 4: GELEN BELGEYİ ONAYLA / REDDET -->
<div class="vendor-portal-modal" id="reviewFileModal" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 480px;">
        <div class="modal-content">
            <div class="modal-header" id="reviewModalHeader"
                style="background: #047857; color: #ffffff; padding: 12px 18px;">
                <h5 class="modal-title" id="reviewModalTitle"
                    style="font-size: 14.5px; font-weight: 700; color: #ffffff; margin: 0;">Belge İnceleme</h5>
                <button type="button" class="custom-modal-close-btn"
                    onclick="closeVendorModal('reviewFileModal')">✕</button>
            </div>
            <form id="reviewFileForm" method="POST">
                @csrf
                <input type="hidden" name="status" id="reviewFileStatus">
                <div class="modal-body" style="padding: 16px 20px;">
                    <div style="font-size: 13px; color: #334155; margin-bottom: 14px; line-height: 1.4;">
                        <strong id="reviewFileName" style="color: #0f172a;"></strong> belgesini <span
                            id="reviewActionText" style="font-weight: 700;"></span> üzeresiniz.
                    </div>
                    <div class="vendor-modal-field-group" style="margin-bottom: 14px !important;">
                        <label class="form-label"
                            style="display: block; font-size: 11.5px; font-weight: 700; color: #1e293b; margin-bottom: 5px !important;">
                            İNCELEME NOTU / GEREKÇE
                        </label>
                        <textarea name="review_note" rows="2" class="form-control"
                            style="padding: 7px 11px; font-size: 12.5px;"
                            placeholder="Onay veya red gerekçenizi yazınız..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 10px 18px;">
                    <button type="button" class="btn btn-secondary btn-sm"
                        onclick="closeVendorModal('reviewFileModal')">Vazgeç</button>
                    <button type="submit" class="btn btn-sm" id="reviewSubmitBtn"
                        style="font-weight: 700; color: #ffffff; padding: 7px 16px;">İşlemi Tamamla</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- MODAL 5: BELGE KATEGORİLERİNİ DÜZENLE -->
<div class="vendor-portal-modal" id="editCategoriesModal" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 560px;">
        <div class="modal-content">
            <div class="modal-header"
                style="background: linear-gradient(135deg, #091325 0%, #0f172a 100%); color: #ffffff; padding: 12px 18px;">
                <h5 class="modal-title"
                    style="font-size: 14.5px; font-weight: 700; color: #ffffff; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span>🏷️</span> <span id="categoriesVendorName"></span> Belge Kategorilerini Yönet
                </h5>
                <button type="button" class="custom-modal-close-btn"
                    onclick="closeVendorModal('editCategoriesModal')">✕</button>
            </div>
            <form id="editCategoriesForm" method="POST">
                @csrf
                <div class="modal-body" style="padding: 16px 20px;">
                    <p style="font-size: 12.5px; color: #475569; line-height: 1.4; margin-bottom: 12px;">
                        Bu dış firmanın portalına giriş yaptığında dosya yüklerken seçebileceği kategorileri
                        belirleyiniz. Kategorileri <strong>virgül (,)</strong> ile ayırarak yazınız.
                    </p>
                    <div class="vendor-modal-field-group" style="margin-bottom: 14px !important;">
                        <label class="form-label"
                            style="display: block; font-size: 11.5px; font-weight: 700; color: #1e293b; margin-bottom: 5px !important;">
                            KATEGORİ LİSTESİ <span class="text-danger">*</span>
                        </label>
                        <textarea name="categories" id="inputCategoriesText" rows="2" required class="form-control"
                            style="font-size: 12.5px; padding: 7px 11px;"
                            placeholder="Örn: Çizim Parçaları, Jant Parçaları, Yedek Parça Listeleri, Kaynak Muayene Raporu"></textarea>
                    </div>
                    <div
                        style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; margin-top: 8px;">
                        <div style="font-size: 11px; font-weight: 700; color: #334155; margin-bottom: 6px;">💡 Hızlı
                            Şablonlar (Tıklayarak Ekleyin):</div>
                        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                            <button type="button" class="btn btn-xs btn-outline-secondary"
                                onclick="applyCategoryTemplate('manufacturing')"
                                style="font-size: 11px; padding: 3px 8px;">Talaşlı İmalat & Parça</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary"
                                onclick="applyCategoryTemplate('ndt_welding')"
                                style="font-size: 11px; padding: 3px 8px;">Kaynak & Muayene</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary"
                                onclick="applyCategoryTemplate('automation')"
                                style="font-size: 11px; padding: 3px 8px;">Otomasyon & PLC</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary"
                                onclick="applyCategoryTemplate('default')"
                                style="font-size: 11px; padding: 3px 8px;">Standart Mühendislik</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 10px 18px;">
                    <button type="button" class="btn btn-secondary btn-sm"
                        onclick="closeVendorModal('editCategoriesModal')">Vazgeç</button>
                    <button type="submit" class="btn btn-primary btn-sm"
                        style="font-weight: 700; background: #0284c7; padding: 7px 16px;">
                        Kaydet & Güncelle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- MODAL 6: FİRMAYI ÇIKAR (REVOKE) -->
<div class="vendor-portal-modal" id="revokeVendorModal" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 480px;">
        <div class="modal-content">
            <div class="modal-header" style="background: #b91c1c; color: #ffffff; padding: 12px 18px;">
                <h5 class="modal-title"
                    style="font-size: 14.5px; font-weight: 700; color: #ffffff; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span>🚫</span> Dış Paydaşı Projeden Çıkar
                </h5>
                <button type="button" class="custom-modal-close-btn"
                    onclick="closeVendorModal('revokeVendorModal')">✕</button>
            </div>
            <form id="revokeVendorForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body" style="padding: 16px 20px;">
                    <p style="font-size: 13px; color: #475569; line-height: 1.4; margin-bottom: 14px;">
                        <strong id="revokeVendorName" style="color: #0f172a;"></strong> firmasıyla çalışmayı sonlandırıp
                        projeden çıkarmak üzeresiniz. Firmanın dosya portalına erişim bağlantısı <strong>derhal iptal
                            edilecek</strong> ve geçersiz kılınacaktır.
                    </p>
                    <div class="vendor-modal-field-group" style="margin-bottom: 14px !important;">
                        <label class="form-label"
                            style="display: block; font-size: 11.5px; font-weight: 700; color: #1e293b; margin-bottom: 5px !important;">
                            ÇIKARMA GEREKÇESİ <span class="text-danger">*</span>
                        </label>
                        <textarea name="revocation_reason" required rows="2" class="form-control"
                            style="padding: 7px 11px; font-size: 12.5px;"
                            placeholder="Örn: Sözleşme tamamlandı veya tedarikçi değişikliğine gidildi."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 10px 18px;">
                    <button type="button" class="btn btn-secondary btn-sm"
                        onclick="closeVendorModal('revokeVendorModal')">Vazgeç</button>
                    <button type="submit" class="btn btn-danger btn-sm" style="font-weight: 700; padding: 7px 16px;">🚫
                        Evet, Firmayı Çıkar</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- MODAL 7: GİDEN DOSYAYI DİKKATE ALMA İŞARETÇİSİ -->
<div class="vendor-portal-modal" id="voidInternalFileModal" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 480px;">
        <div class="modal-content">
            <div class="modal-header" style="background: #b45309; color: #ffffff; padding: 12px 18px;">
                <h5 class="modal-title"
                    style="font-size: 14.5px; font-weight: 700; color: #ffffff; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span>⚠️</span> Dosyayı 'Geçersiz / Dikkate Alma' Olarak İşaretle
                </h5>
                <button type="button" class="custom-modal-close-btn"
                    onclick="closeVendorModal('voidInternalFileModal')">✕</button>
            </div>
            <form id="voidInternalFileForm" method="POST">
                @csrf
                <div class="modal-body" style="padding: 16px 20px;">
                    <p style="font-size: 13px; color: #475569; line-height: 1.4; margin-bottom: 14px;">
                        <strong id="voidInternalFileName" style="color: #0f172a;"></strong> belgesini hatalı
                        gönderdiyseniz veya dikkate alınmaması gerekiyorsa işaretleyebilirsiniz.
                    </p>
                    <div class="vendor-modal-field-group" style="margin-bottom: 14px !important;">
                        <label class="form-label"
                            style="display: block; font-size: 11.5px; font-weight: 700; color: #1e293b; margin-bottom: 5px !important;">
                            AÇIKLAMA / GEREKÇE <span class="text-danger">*</span>
                        </label>
                        <textarea name="void_reason" required rows="2" class="form-control"
                            style="padding: 7px 11px; font-size: 12.5px;"
                            placeholder="Örn: Rev 1 çizim hatalı tolerans içeriyor, dikkate almayınız."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 10px 18px;">
                    <button type="button" class="btn btn-secondary btn-sm"
                        onclick="closeVendorModal('voidInternalFileModal')">Vazgeç</button>
                    <button type="submit" class="btn btn-warning btn-sm"
                        style="font-weight: 700; color: #78350f; padding: 7px 16px;">⚠️ İşaretle</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- MODAL 8: REJECT VENDOR MODAL -->
<div class="vendor-portal-modal" id="rejectVendorModal" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 480px;">
        <div class="modal-content">
            <div class="modal-header" style="background: #b91c1c; color: #ffffff; padding: 12px 18px;">
                <h5 class="modal-title"
                    style="font-size: 14.5px; font-weight: 700; color: #ffffff; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span>❌</span> Dış Paydaşı Reddet
                </h5>
                <button type="button" class="custom-modal-close-btn"
                    onclick="closeVendorModal('rejectVendorModal')">✕</button>
            </div>
            <form id="rejectVendorForm" method="POST">
                @csrf
                <div class="modal-body" style="padding: 16px 20px;">
                    <p style="font-size: 13px; color: #334155; margin-bottom: 14px; line-height: 1.4;">
                        <strong id="rejectVendorName" style="color: #0f172a;"></strong> dış paydaş başvurusunu reddetmek
                        üzeresiniz.
                    </p>
                    <div class="vendor-modal-field-group" style="margin-bottom: 14px !important;">
                        <label class="form-label"
                            style="display: block; font-size: 11.5px; font-weight: 700; color: #1e293b; margin-bottom: 5px !important;">
                            RED GEREKÇESİ <span class="text-danger">*</span>
                        </label>
                        <textarea name="rejection_reason" required rows="2" class="form-control"
                            style="padding: 7px 11px; font-size: 12.5px;"
                            placeholder="Örn: İlgili hizmet için firma yeterlilik kriterlerini karşılamamaktadır."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 10px 18px;">
                    <button type="button" class="btn btn-secondary btn-sm"
                        onclick="closeVendorModal('rejectVendorModal')">Vazgeç</button>
                    <button type="submit" class="btn btn-danger btn-sm" style="font-weight: 700; padding: 7px 16px;">❌
                        Reddet</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- JAVASCRIPT: GARANTİLİ MERKEZİ MODAL AÇMA / KAPAMA VE KONTROLLER -->
<script>
    function openVendorModal(modalId) {
        const el = document.getElementById(modalId);
        if (el) {
            el.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeVendorModal(modalId) {
        const el = document.getElementById(modalId);
        if (el) {
            el.classList.remove('active');
            // Başka açık modal kalmadıysa scroll'u geri aç
            if (document.querySelectorAll('.vendor-portal-modal.active').length === 0) {
                document.body.style.overflow = '';
            }
        }
    }

    // Dışarı (Backdrop) tıklandığında kapatma
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.vendor-portal-modal').forEach(modal => {
            modal.addEventListener('click', function (e) {
                if (e.target === this) {
                    closeVendorModal(this.id);
                }
            });
        });

        // ESC tuşuyla kapatma
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.vendor-portal-modal.active').forEach(modal => {
                    closeVendorModal(modal.id);
                });
            }
        });

        // Çift Tıklama Engeli
        const formsToProtect = ['addVendorForm', 'outboundUploadForm', 'reviewFileForm', 'revokeVendorForm', 'editCategoriesForm', 'voidInternalFileForm', 'rejectVendorForm'];
        formsToProtect.forEach(formId => {
            const formEl = document.getElementById(formId);
            if (formEl) {
                formEl.addEventListener('submit', function (e) {
                    const submitBtn = formEl.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        if (submitBtn.disabled) {
                            e.preventDefault();
                            return false;
                        }
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span>⏳ İşleniyor...</span>';
                        submitBtn.style.opacity = '0.7';
                        submitBtn.style.cursor = 'not-allowed';
                    }
                });
            }
        });
    });

    function openVendorFilesModal(vendorId) {
        openVendorModal(`vendorFilesModal_${vendorId}`);
    }

    let outboundSelectedFilesList = [];

    function openOutboundUploadModal(vendorId, vendorName) {
        document.getElementById('outboundVendorName').textContent = vendorName;
        document.getElementById('outboundUploadForm').action = `{{ url('/projects/' . $project->id . '/vendors') }}/${vendorId}/outbound-files`;
        outboundSelectedFilesList = [];
        syncOutboundInputAndRenderPreview();
        openVendorModal('uploadOutboundModal');
    }

    function handleOutboundFilesSelected(files) {
        if (!files || files.length === 0) return;

        const maxFiles = 10;
        const maxBytesPerFile = 50 * 1024 * 1024; // 50 MB

        for (let i = 0; i < files.length; i++) {
            const file = files[i];

            if (outboundSelectedFilesList.length >= maxFiles) {
                alert(`En fazla ${maxFiles} adet dosya seçebilirsiniz. Sınırı aşan dosyalar eklenmedi.`);
                break;
            }

            if (file.size > maxBytesPerFile) {
                alert(`'${file.name}' dosyası 50 MB sınırını aşıyor (${(file.size / (1024 * 1024)).toFixed(1)} MB). Lütfen daha küçük bir dosya seçiniz.`);
                continue;
            }

            // Mükerrer dosya kontrolü
            const isDuplicate = outboundSelectedFilesList.some(f => f.name === file.name && f.size === file.size);
            if (!isDuplicate) {
                outboundSelectedFilesList.push(file);
            }
        }

        syncOutboundInputAndRenderPreview();
    }

    function removeOutboundFile(index) {
        if (index >= 0 && index < outboundSelectedFilesList.length) {
            outboundSelectedFilesList.splice(index, 1);
            syncOutboundInputAndRenderPreview();
        }
    }

    function getModalFileIcon(fileName) {
        const ext = fileName.split('.').pop().toLowerCase();
        if (['pdf'].includes(ext)) return '📕';
        if (['dwg', 'dxf', 'step', 'stp', 'iges', 'igs', 'sldprt', 'sldasm'].includes(ext)) return '📐';
        if (['xls', 'xlsx', 'csv'].includes(ext)) return '📊';
        if (['doc', 'docx', 'txt', 'rtf'].includes(ext)) return '📄';
        if (['jpg', 'jpeg', 'png', 'webp', 'tif', 'tiff'].includes(ext)) return '🖼️';
        if (['zip', 'rar', '7z', 'tar', 'gz'].includes(ext)) return '📦';
        return '📎';
    }

    function syncOutboundInputAndRenderPreview() {
        const fileInput = document.getElementById('outboundFileInput');
        const previewBox = document.getElementById('outboundSelectedPreviewBox');
        const cardsGrid = document.getElementById('outboundCardsGrid');
        const badge = document.getElementById('outboundSelectedCountBadge');

        // DataTransfer API ile input dosya listesini senkronize et
        const dt = new DataTransfer();
        outboundSelectedFilesList.forEach(file => dt.items.add(file));
        if (fileInput) {
            fileInput.files = dt.files;
        }

        if (cardsGrid) {
            cardsGrid.innerHTML = '';
        }

        if (outboundSelectedFilesList.length === 0) {
            if (previewBox) previewBox.style.display = 'none';
            if (badge) badge.textContent = '0 / 10 Belge';
            return;
        }

        if (previewBox) previewBox.style.display = 'block';
        if (badge) badge.textContent = `${outboundSelectedFilesList.length} / 10 Belge`;

        outboundSelectedFilesList.forEach((file, index) => {
            const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
            const icon = getModalFileIcon(file.name);

            const card = document.createElement('div');
            card.className = 'modal-selected-file-card';
            card.innerHTML = `
                <div style="display: flex; align-items: center; gap: 8px; min-width: 0; flex: 1;">
                    <span style="font-size: 16px; flex-shrink: 0;">${icon}</span>
                    <div style="min-width: 0; flex: 1;">
                        <div style="font-size: 12px; font-weight: 700; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${file.name}">
                            ${file.name}
                        </div>
                        <div style="font-size: 10.5px; color: #64748b;">
                            ${sizeMb} MB
                        </div>
                    </div>
                </div>
                <button type="button" class="modal-file-remove-btn" onclick="removeOutboundFile(${index})" title="Bu dosyayı listeden çıkar">
                    ✕
                </button>
            `;
            if (cardsGrid) {
                cardsGrid.appendChild(card);
            }
        });
    }

    // Outbound Dropzone Sürükle & Bırak Dinleyicileri
    document.addEventListener('DOMContentLoaded', function () {
        const dropzone = document.getElementById('outboundDropzone');
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
                    dropzone.style.borderColor = '#94a3b8';
                    dropzone.style.background = '#f8fafc';
                }, false);
            });

            dropzone.addEventListener('drop', function (e) {
                const dt = e.dataTransfer;
                if (dt && dt.files) {
                    handleOutboundFilesSelected(dt.files);
                }
            }, false);
        }

        // Outbound form submit kontrolü
        const outboundForm = document.getElementById('outboundUploadForm');
        if (outboundForm) {
            outboundForm.addEventListener('submit', function (e) {
                if (outboundSelectedFilesList.length === 0) {
                    e.preventDefault();
                    alert('Lütfen gönderilecek en az bir teknik belge seçiniz.');
                    return false;
                }
            });
        }
    });

    function openReviewFileModal(fileId, status, fileName) {
        document.getElementById('reviewFileName').textContent = fileName;
        document.getElementById('reviewFileStatus').value = status;
        const form = document.getElementById('reviewFileForm');
        form.action = `{{ url('/projects/' . $project->id . '/vendor-files') }}/${fileId}/review`;

        const titleEl = document.getElementById('reviewModalTitle');
        const headerEl = document.getElementById('reviewModalHeader');
        const btnEl = document.getElementById('reviewSubmitBtn');
        const textEl = document.getElementById('reviewActionText');

        if (status === 'approved') {
            titleEl.textContent = '✅ Belgeyi Onayla & Kabul Et';
            headerEl.style.background = '#047857';
            btnEl.style.background = '#047857';
            textEl.textContent = 'onaylayıp kabul etmek';
        } else {
            titleEl.textContent = '❌ Belgeyi Reddet';
            headerEl.style.background = '#b91c1c';
            btnEl.style.background = '#b91c1c';
            textEl.textContent = 'reddetmek';
        }

        openVendorModal('reviewFileModal');
    }

    function openRevokeVendorModal(vendorId, vendorName) {
        document.getElementById('revokeVendorName').textContent = vendorName;
        document.getElementById('revokeVendorForm').action = `{{ url('/projects/' . $project->id . '/vendors') }}/${vendorId}/revoke`;
        openVendorModal('revokeVendorModal');
    }

    function openRejectVendorModal(vendorId, vendorName) {
        document.getElementById('rejectVendorName').textContent = vendorName;
        document.getElementById('rejectVendorForm').action = `{{ url('/projects/' . $project->id . '/vendors') }}/${vendorId}/reject`;
        openVendorModal('rejectVendorModal');
    }

    function openVoidInternalFileModal(fileId, fileName) {
        document.getElementById('voidInternalFileName').textContent = fileName;
        document.getElementById('voidInternalFileForm').action = `{{ url('/projects/' . $project->id . '/vendor-files') }}/${fileId}/void`;
        openVendorModal('voidInternalFileModal');
    }

    function openEditCategoriesModal(vendorId, vendorName, currentCategories) {
        document.getElementById('categoriesVendorName').textContent = vendorName;
        document.getElementById('inputCategoriesText').value = currentCategories;
        document.getElementById('editCategoriesForm').action = `{{ url('/projects/' . $project->id . '/vendors') }}/${vendorId}/categories`;
        openVendorModal('editCategoriesModal');
    }

    function applyCategoryTemplate(type) {
        const templates = {
            'manufacturing': 'Çizim Parçaları, Jant Parçaları, Talaşlı İmalat Parçaları, Isıl İşlem Raporu, Ölçüm ve Tolerans Raporu, Malzeme Sertifikası',
            'ndt_welding': 'Kaynak Muayene Raporu, Penetrant Test Raporu, Radyografik Film Raporu, Ultrasonik Muayene, WPS / PQR Kaynak Şartnamesi',
            'automation': 'PLC & Scada Proje Çizimleri, Pano İçi Yerleşim Şeması, I/O Listesi, Sürücü Parametreleri, Test & Devreye Alma Raporu',
            'default': 'Kaynak / İmalat Muayene Raporu, Test & Devreye Alma Raporu, Malzeme / Kalite Sertifikası, İmalat / Montaj Çizimi (As-Built), İrsaliye & Mal Kabul Tutanağı, Diğer Teknik Doküman'
        };
        if (templates[type]) {
            document.getElementById('inputCategoriesText').value = templates[type];
        }
    }

    function togglePasskey(vendorId, rawPasskey) {
        const el = document.getElementById(`passkeyMask_${vendorId}`);
        if (el) {
            if (el.textContent.includes('•')) {
                el.textContent = rawPasskey;
            } else {
                el.textContent = rawPasskey.substring(0, 4) + '••••-•••';
            }
        }
    }
</script>