@extends('layouts.app')

@section('title', $meeting->title . ' - Toplantı Detayı & Tutanağı')
@section('page_title', 'Toplantı Detayı & Resmi Tutanağı')
@section('page_subtitle', $meeting->project->code . ' - ' . $meeting->project->title)

@push('styles')
    <!-- Quill.js Zengin Metin Editörü Stilleri -->
    <link rel="stylesheet" href="{{ asset('vendor/quill/quill.snow.css') }}">
    <style>
        .ql-toolbar.ql-snow {
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            border-color: #cbd5e1;
            background: #f8fafc;
        }
        .ql-container.ql-snow {
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
            border-color: #cbd5e1;
            font-family: inherit;
            font-size: 13.5px;
            min-height: 160px;
            background: #ffffff;
        }
        .ql-editor {
            line-height: 1.6;
            color: #1e293b;
            min-height: 160px;
        }
        .meeting-rich-content {
            font-size: 13.5px;
            line-height: 1.7;
            color: #1e293b;
        }
        .meeting-rich-content p {
            margin-bottom: 8px;
        }
        .meeting-rich-content ul, .meeting-rich-content ol {
            padding-left: 24px;
            margin-bottom: 8px;
        }
        .agenda-card {
            transition: all 0.2s ease;
        }
        .agenda-card:hover {
            border-color: #93c5fd !important;
            box-shadow: 0 2px 6px rgba(59, 130, 246, 0.08);
        }
    </style>
@endpush

@section('content')
    @php
        // Bildirim Matrisi Alıcı Havuzu (Yönetici, Sponsor, Paydaşlar ve Katılımcılar)
        $recipientCollection = collect();
        if ($meeting->project->manager) $recipientCollection->push($meeting->project->manager);
        if ($meeting->project->sponsor) $recipientCollection->push($meeting->project->sponsor);
        foreach ($projectTeam as $pt) $recipientCollection->push($pt);
        foreach ($meeting->participants as $part) {
            if ($part->user) $recipientCollection->push($part->user);
        }
        $allNotificationRecipients = $recipientCollection->unique('id')->values();
    @endphp

    <!-- ÜST NAVİGASYON VE DÖNÜŞ ŞERİDİ -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('meetings.index') }}" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; text-decoration: none; padding: 7px 14px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; color: #1e293b;">
                ← Tüm Toplantılara Dön
            </a>
            <!-- YENİ SEKMEDE PROJE BAĞLANTISI (Kullanıcı toplantı sayfasından kopmaz) -->
            <a href="{{ route('projects.show', $meeting->project_id) }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; text-decoration: none; padding: 7px 14px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; color: #1d4ed8; font-weight: 600;" title="Projeyi yeni bir sekmede aç">
                📁 {{ $meeting->project->code }} Projesine Git ↗
            </a>
            <button type="button" onclick="openProjectPreviewModal()" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; padding: 7px 14px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; color: #475569; font-weight: 600; cursor: pointer;">
                🔍 Hızlı Proje Özeti
            </button>
        </div>

        <!-- HIZLI STATÜ DEĞİŞTİRME AKSİYONLARI (BİLDİRİM MATRİSİ & PAYDAŞ SEÇİM MODALI ENTEGRELİ) -->
        <div style="display: flex; align-items: center; gap: 8px;">
            @if(auth()->user()?->isYonetim())
                <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 11px; padding: 6px 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                    👁️ Yönetim İzleme Modu (Salt Okunur)
                </span>
            @else
                @if($meeting->status !== 'completed' && $meeting->status !== 'cancelled')
                    <button type="button" onclick="openPostponeModalShow()" class="btn btn-outline" style="font-size: 12px; padding: 7px 12px; border-radius: 6px; border: 1px solid #fde68a; background: #fffbeb; color: #b45309; font-weight: 700; cursor: pointer;">
                        ⏱️ Toplantıyı Ertele
                    </button>
                @endif

                @if($meeting->status !== 'in_progress')
                    <button type="button" onclick="openStatusActionModal('in_progress', 'Toplantıyı Başlat', 'Toplantıyı resmi olarak devam ediyor statüsüne almak ve paydaşlara bildirim göndermek üzeresiniz.', '#0284c7', '▶️ Toplantıyı Başlat')" class="btn btn-outline" style="font-size: 12px; padding: 7px 12px; border-radius: 6px; border: 1px solid #bae6fd; background: #f0f9ff; color: #0284c7; font-weight: 600; cursor: pointer;">
                        ▶️ Toplantıyı Başlat
                    </button>
                @endif

                @if($meeting->status !== 'completed')
                    <button type="button" onclick="openStatusActionModal('completed', 'Tutanakları Kapat & Tamamla', 'Toplantıyı tamamlandı statüsüne almak, resmi tutanakları kilitlemek ve paydaşlara bildirim göndermek üzeresiniz.', '#16a34a', '✅ Tutanakları Kapat & Tamamla')" class="btn btn-primary" style="font-size: 12px; padding: 7px 14px; border-radius: 6px; font-weight: 600; background: #16a34a; border-color: #15803d; cursor: pointer;">
                        ✅ Tutanakları Kapat & Tamamla
                    </button>
                @else
                    <button type="button" onclick="openStatusActionModal('scheduled', 'Yeniden Planlandıya Çek', 'Toplantıyı yeniden planlandı/bekliyor statüsüne almak ve paydaşlara bildirim göndermek üzeresiniz.', '#b45309', '⏱️ Yeniden Planlandıya Çek')" class="btn btn-outline" style="font-size: 12px; padding: 7px 12px; border-radius: 6px; border: 1px solid #cbd5e1; background: #ffffff; color: #475569; cursor: pointer;">
                        ⏱️ Yeniden Planlandıya Çek
                    </button>
                @endif

                @if($meeting->status !== 'cancelled')
                    <button type="button" onclick="openStatusActionModal('cancelled', 'Toplantıyı İptal Et', 'Toplantıyı iptal etmek ve paydaşlara iptal bildirimi iletmek üzeresiniz.', '#dc2626', '❌ İptal Et')" class="btn btn-outline" style="font-size: 12px; padding: 7px 12px; border-radius: 6px; border: 1px solid #fecaca; background: #fff5f5; color: #dc2626; cursor: pointer;">
                        ❌ İptal Et
                    </button>
                @endif
            @endif
        </div>
    </div>

    <!-- ANA HERO BANNER: TOPLANTI BAŞLIĞI, PROJE & STATÜ -->
    <div class="card" style="margin-bottom: 20px; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; background: linear-gradient(to right, #ffffff, #f8fafc);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; flex-wrap: wrap;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                    <a href="{{ route('projects.show', $meeting->project->id) }}" target="_blank" rel="noopener noreferrer"
                       style="font-size: 13px; font-weight: 800; color: #1d4ed8; background: #eff6ff; border: 1px solid #bfdbfe; padding: 3px 10px; border-radius: 6px; text-decoration: none;"
                       title="Projeyi yeni sekmede aç">
                        📁 {{ $meeting->project->code }} ↗
                    </a>
                    <span style="font-size: 13px; color: #64748b; font-weight: 600;">
                        {{ $meeting->project->title }}
                    </span>
                    <span style="font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 20px; {!! $meeting->status_badge_style !!}">
                        @if($meeting->status === 'completed')
                            ✅ Tamamlandı & Tutanak Bağlandı
                        @elseif($meeting->status === 'in_progress')
                            🔄 Toplantı Devam Ediyor
                        @elseif($meeting->status === 'postponed')
                            ⏳ Ertelendi
                        @elseif($meeting->status === 'cancelled')
                            ❌ İptal Edildi
                        @else
                            ⏱️ Planlandı / Bekliyor
                        @endif
                    </span>
                </div>

                <h1 style="font-size: 22px; font-weight: 800; color: #0f172a; margin: 0 0 10px 0;">
                    {{ $meeting->title }}
                </h1>

                <!-- TOPLANTI AYRINTILARI ŞERİDİ -->
                <div style="display: flex; align-items: center; gap: 18px; font-size: 13px; color: #475569; flex-wrap: wrap;">
                    <div style="display: inline-flex; align-items: center; gap: 6px;">
                        <span>📅</span>
                        <strong>Tarih & Saat:</strong>
                        <span style="color: #0f172a;">{{ $meeting->meeting_date->format('d.m.Y H:i') }}</span>
                    </div>

                    <div style="display: inline-flex; align-items: center; gap: 6px;">
                        <span>📍</span>
                        <strong>Lokasyon:</strong>
                        <span style="color: #0f172a;">{{ $meeting->location ?? '2. OSB Şantiye Ofisi / Konferans Salonu' }}</span>
                    </div>

                    <div style="display: inline-flex; align-items: center; gap: 6px;">
                        <span>👤</span>
                        <strong>Moderatör / Planlayan:</strong>
                        <span style="color: #0f172a; font-weight: 700;">
                            {{ $meeting->organizer ? $meeting->organizer->getMaskedNameFor(auth()->user()) : 'Sistem / Belirtilmemiş' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- HIZLI METRİKLER -->
            <div style="display: flex; gap: 12px;">
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 18px; text-align: center; min-width: 90px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                    <div style="font-size: 20px; font-weight: 800; color: #1e3a8a;">{{ $meeting->participants->count() }}</div>
                    <div style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase;">Katılımcı</div>
                </div>
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 18px; text-align: center; min-width: 90px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                    <div style="font-size: 20px; font-weight: 800; color: #047857;">{{ $meeting->actions->count() }}</div>
                    <div style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase;">Alınan Karar</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ERTELEME BİLGİLENDİRME BANNERI (TOPLANTI ERTELENMİŞSE) -->
    @if($meeting->status === 'postponed')
        <div style="background: #fffbeb; border: 1px solid #fde68a; border-left: 4px solid #f59e0b; border-radius: 10px; padding: 16px 20px; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
            <span style="font-size: 26px; line-height: 1;">⏱️</span>
            <div style="flex: 1;">
                <div style="font-size: 14.5px; font-weight: 750; color: #92400e; margin-bottom: 4px;">Bu Toplantı Ertelenmiştir</div>
                @if($meeting->postpone_reason)
                    <div style="font-size: 13px; color: #78350f; margin-bottom: 6px; line-height: 1.5; background: #ffffff; padding: 10px 14px; border-radius: 6px; border: 1px solid #fed7aa;">
                        <strong>Erteleme Gerekçesi / Notu:</strong> {{ $meeting->postpone_reason }}
                    </div>
                @endif
                <div style="font-size: 12.5px; color: #b45309;">
                    📅 Yeni Planlanan Tarih & Saat: <strong style="color: #15803d; font-size: 13px;">{{ $meeting->meeting_date->format('d.m.Y H:i') }}</strong>
                </div>
            </div>
        </div>
    @endif

    <!-- 2 KOLONLU DÜZEN: İÇERİK & TUTANAK / PROJE VE KATILIMCILAR -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; align-items: start;">
        
        <!-- SOL KOLON: GÜNDEM, TUTANAK NOTLARI, ALINAN KARARLAR & GÖREVE DÖNÜŞTÜRME -->
        <div style="display: flex; flex-direction: column; gap: 20px;">

            <!-- 1. GÜNDEM MADDELERİ PANELİ (NUMARALI & DETAYLI) -->
            <div class="card" style="border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 0;">
                <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center;">
                    <span class="card-title" style="font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <span>📋</span> Toplantı Öncesi Belirlenen Gündem Maddeleri
                    </span>
                    @if(!auth()->user()?->isYonetim())
                        <button type="button" onclick="openEditAgendaModal()" class="btn btn-sm btn-outline" style="font-size: 11.5px; padding: 4px 10px; border-radius: 6px; border: 1px solid #cbd5e1; background: #ffffff; color: #334155; font-weight: 600; cursor: pointer;">
                            ✏️ Gündemi Düzenle
                        </button>
                    @endif
                </div>
                <div class="card-body" style="padding: 18px;">
                    @if($meeting->agenda)
                        <div class="meeting-rich-content" style="background: #ffffff; padding: 14px 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
                            {!! $meeting->agenda !!}
                        </div>
                    @else
                        <div style="color: #64748b; font-size: 13px; font-style: italic; background: #f8fafc; padding: 14px; border-radius: 6px; border: 1px dashed #cbd5e1; text-align: center;">
                            Toplantı öncesi belirlenmiş özel bir gündem maddesi girilmemiştir.
                        </div>
                    @endif
                </div>
            </div>

            <!-- 2. RESMİ TOPLANTI TUTANAĞI & NOTLAR (QUILL WYSIWYG ZENGİN METİN EDİTÖRÜ) -->
            <div class="card" style="border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 0;">
                <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center;">
                    <span class="card-title" style="font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <span>📝</span> Toplantı Görüşme Tutanağı & Saha Notları
                    </span>
                    <span style="font-size: 11px; color: #64748b;">
                        Zengin metin formatında resmi tutanak ve teknik detaylar
                    </span>
                </div>
                <div class="card-body" style="padding: 18px;">
                    @if(auth()->user()?->isYonetim())
                        <!-- YÖNETİM ROLÜ SALT OKUNUR GÖRÜNÜM -->
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; min-height: 120px;" class="meeting-rich-content">
                            {!! $meeting->minutes ?: '<div style="color: #64748b; font-style: italic;">Henüz tutanak metni kaydedilmemiş.</div>' !!}
                        </div>
                    @else
                        <!-- ZENGİN METİN EDİTÖRÜ FORMU (QUILL.JS) -->
                        <form action="{{ route('meetings.update_status', $meeting->id) }}" method="POST" id="minutesForm" onsubmit="syncQuillBeforeSubmit()">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="{{ $meeting->status }}">
                            <input type="hidden" name="minutes" id="minutesHiddenInput">

                            <div class="form-group" style="margin-bottom: 14px;">
                                <div id="quillMinutesEditorContainer"></div>
                            </div>

                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 11.5px; color: #64748b;">
                                    💡 Kalın, italik, liste ve başlık gibi tüm zengin metin öğeleri desteklenir.
                                </span>
                                <button type="submit" class="btn btn-primary" style="font-size: 12px; font-weight: 600; padding: 7px 18px; display: inline-flex; align-items: center; gap: 6px;">
                                    <span>💾</span> Tutanak Notlarını Kaydet
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            <!-- 3. TOPLANTI KARARLARI & SAP EKİP MODELİYLE GÖREVE DÖNÜŞTÜRME -->
            <div class="card" style="border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 0;">
                <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <span class="card-title" style="font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                            <span>⚡</span> Alınan Kararlar & Kanban / Gantt Görev Entegrasyonu
                        </span>
                        <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                            Toplantı kararları anında doğrudan projenin operasyonel panolarına aktarılır.
                        </div>
                    </div>
                    <span class="badge" style="background: #eff6ff; color: #1e40af; font-weight: 700;">
                        {{ $meeting->actions->count() }} Karar
                    </span>
                </div>

                <div class="card-body" style="padding: 18px;">
                    <!-- YENİ KARAR / GÖREVE DÖNÜŞTÜRME FORMU -->
                    @if(!auth()->user()?->isYonetim())
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                            <div style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                                <span>➕</span> Yeni Toplantı Kararı Ekle ve Göreve Dönüştür
                            </div>

                            <form action="{{ route('meetings.convert_task', $meeting->id) }}" method="POST">
                                @csrf
                                <div style="display: grid; grid-template-columns: 2fr 1.3fr 1.1fr auto; gap: 12px; align-items: end;">
                                    
                                    <!-- KARAR METNİ -->
                                    <div>
                                        <label class="form-label" style="font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 4px;">
                                            Karar / Aksiyon Maddesi *
                                        </label>
                                        <input type="text" name="decision_text" class="form-control" required placeholder="Örn: 2. Etap Çelik kiriş statik kontrolü tamamlanacak" style="font-size: 13px;">
                                    </div>

                                    <!-- SORUMLU ATAMASI (SAP EKİP MANTIĞI) -->
                                    <div>
                                        <label class="form-label" style="font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 4px;">
                                            Sorumlu (SAP Ekip Mantığı) *
                                        </label>
                                        <select name="assigned_target" class="form-control" required style="font-size: 13px;">
                                            <option value="team" selected style="font-weight: 700; color: #1e3a8a; background: #eff6ff;">
                                                👥 Tüm Proje Ekibi (SAP Ortak Görev)
                                            </option>
                                            <optgroup label="Proje Ekibi Paydaşları">
                                                @foreach($projectTeam as $member)
                                                    <option value="{{ $member->id }}">{{ $member->name }} ({{ $member->title ?? 'Ekip Üyesi' }})</option>
                                                @endforeach
                                            </optgroup>
                                            <optgroup label="Diğer Sistem Kullanıcıları">
                                                @foreach($users as $u)
                                                    @if(!$projectTeam->contains('id', $u->id))
                                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                                    @endif
                                                @endforeach
                                            </optgroup>
                                        </select>
                                    </div>

                                    <!-- TERMİN / BİTİŞ TARİHİ (NET VE AÇIK) -->
                                    <div>
                                        <label class="form-label" style="font-size: 11px; font-weight: 700; color: #b45309; margin-bottom: 4px;" title="Görevin tamamlanması gereken son tarih">
                                            📅 Görev Termin Tarihi *
                                        </label>
                                        <input type="date" name="due_date" class="form-control" required style="font-size: 13px;" value="{{ now()->addDays(7)->format('Y-m-d') }}">
                                    </div>

                                    <!-- DÖNÜŞTÜR BUTONU -->
                                    <div>
                                        <button type="submit" class="btn btn-primary" style="height: 38px; display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; white-space: nowrap; padding: 0 16px;">
                                            <span>⚡</span> Göreve Dönüştür
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif

                    <!-- ALINMIŞ KARARLAR TABLOSU -->
                    @if($meeting->actions->count() > 0)
                        <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                                <thead>
                                    <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">
                                        <th style="padding: 10px 14px; width: 40px;">#</th>
                                        <th style="padding: 10px 14px;">Karar / Aksiyon Maddesi</th>
                                        <th style="padding: 10px 14px;">Sorumlu (SAP Modeli)</th>
                                        <th style="padding: 10px 14px;">Termin Tarihi</th>
                                        <th style="padding: 10px 14px;">Bağlı Görev Durumu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($meeting->actions as $idx => $act)
                                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                                            <td style="padding: 12px 14px; font-weight: 700; color: #94a3b8;">
                                                {{ $idx + 1 }}
                                            </td>
                                            <td style="padding: 12px 14px;">
                                                <div style="font-weight: 600; color: #0f172a;">{{ $act->decision_text }}</div>
                                            </td>
                                            <td style="padding: 12px 14px;">
                                                @if($act->assigned_to)
                                                    <span style="display: inline-flex; align-items: center; gap: 4px; background: #f1f5f9; padding: 2px 8px; border-radius: 4px; font-size: 12px; color: #334155;">
                                                        👤 {{ $act->assignee?->name ?? 'Kullanıcı' }}
                                                    </span>
                                                @else
                                                    <span style="display: inline-flex; align-items: center; gap: 4px; background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">
                                                        👥 Tüm Proje Ekibi (Ortak)
                                                    </span>
                                                @endif
                                            </td>
                                            <td style="padding: 12px 14px; color: #b45309; font-weight: 600;">
                                                📅 {{ $act->due_date ? \Carbon\Carbon::parse($act->due_date)->format('d.m.Y') : '-' }}
                                            </td>
                                            <td style="padding: 12px 14px;">
                                                @if($act->task)
                                                    <a href="{{ route('projects.show', $meeting->project_id) }}#tasks" target="_blank" rel="noopener noreferrer" style="display: inline-flex; align-items: center; gap: 4px; background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; padding: 3px 8px; border-radius: 4px; text-decoration: none; font-size: 11px; font-weight: 700;">
                                                        <span>✓</span> Kanban: {{ Str::limit($act->task->title, 25) }} ↗
                                                    </a>
                                                @else
                                                    <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 11px;">
                                                        Göreve Aktarıldı
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div style="text-align: center; padding: 30px; color: #64748b; font-size: 13px; background: #f8fafc; border-radius: 8px;">
                            Bu toplantıda henüz kayıtlı bir karar bulunmamaktadır. Yukarıdaki formdan karar ekleyerek doğrudan Kanban panosuna aktarabilirsiniz.
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- SAĞ KOLON: PROJE KÜNYESİ, KATILIMCILAR & YOKLAMA -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            
            <!-- 1. İLGİLİ PROJE KÜNYESİ KARTI (SİSTEM STANDARDI STATUS ROZETİ VE MODAL/YENİ SEKME BUTONU) -->
            <div class="card" style="border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 0;">
                <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 14px 18px;">
                    <span class="card-title" style="font-size: 14px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                        <span>📁</span> Bağlı Proje Bilgileri
                    </span>
                </div>
                <div class="card-body" style="padding: 16px;">
                    <div style="margin-bottom: 12px;">
                        <a href="{{ route('projects.show', $meeting->project->id) }}" target="_blank" rel="noopener noreferrer"
                           style="display: inline-block; font-size: 13px; font-weight: 800; color: #1d4ed8; text-decoration: none; margin-bottom: 4px;"
                           title="Projeyi yeni bir sekmede aç">
                            📁 {{ $meeting->project->code }} - {{ $meeting->project->title }} ↗
                        </a>
                        <div style="font-size: 12px; color: #64748b;">
                            {{ $meeting->project->bolum?->name ?? 'Merkez Departman' }}
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 8px; font-size: 12px; border-top: 1px solid #f1f5f9; padding-top: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #64748b;">Proje Yöneticisi:</span>
                            <strong style="color: #0f172a;">{{ $meeting->project->manager?->name ?? 'Belirtilmedi' }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #64748b;">Proje Sponsoru:</span>
                            <strong style="color: #0f172a;">{{ $meeting->project->sponsor?->name ?? 'Belirtilmedi' }}</strong>
                        </div>
                        <!-- SİSTEM STANDARDI DURUM ROZETİ (PENDING -> ONAY BEKLİYOR DÜZELTİLDİ) -->
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #64748b;">Proje Durumu:</span>
                            <x-status-badge :status="$meeting->project->status" />
                        </div>
                    </div>

                    <!-- PROJE DETAYLARINI AÇMA BUTONLARI (MODAL VE YENİ SEKME - TOPLANTI SAYFASINDAN KOPMAZ) -->
                    <div style="margin-top: 14px; display: flex; flex-direction: column; gap: 8px;">
                        <button type="button" onclick="openProjectPreviewModal()" class="btn btn-outline" style="width: 100%; text-align: center; font-size: 12.5px; padding: 8px 12px; border-radius: 6px; border: 1px solid #bfdbfe; background: #eff6ff; color: #1d4ed8; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
                            <span>🔍</span> Proje Detaylarını Aç (Hızlı Modal)
                        </button>
                        <a href="{{ route('projects.show', $meeting->project->id) }}" target="_blank" rel="noopener noreferrer" style="display: block; text-align: center; font-size: 11.5px; color: #64748b; text-decoration: none; padding: 4px;" onmouseover="this.style.color='#1d4ed8'" onmouseout="this.style.color='#64748b'">
                            Yeni sekmede tam sayfayı aç ↗
                        </a>
                    </div>
                </div>
            </div>

            <!-- 2. KATILIMCI YOKLAMA LİSTESİ & YENİ KATILIMCI EKLEME -->
            <div class="card" style="border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 0;">
                <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center;">
                    <span class="card-title" style="font-size: 14px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                        <span>👥</span> Katılımcı Yoklaması
                    </span>
                    <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 11px;">
                        {{ $meeting->participants->count() }} Davetli
                    </span>
                </div>
                <div class="card-body" style="padding: 16px;">
                    
                    <!-- YENİ KATILIMCI EKLE FORMU -->
                    @if(!auth()->user()?->isYonetim() && (auth()->user()?->isSuperAdmin() || auth()->user()?->isAdmin() || auth()->user()?->hasPermission('meeting.create') || auth()->user()?->hasPermission('meeting.manage') || $meeting->organizer_id === auth()->id()))
                        <form action="{{ route('meetings.add_participant', $meeting->id) }}" method="POST" style="margin-bottom: 14px; display: flex; gap: 6px;">
                            @csrf
                            <select name="user_id" class="form-control" required style="font-size: 12px; height: 34px;">
                                <option value="">-- Katılımcı Ekle --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->title ?? 'Personel' }})</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary" style="font-size: 12px; white-space: nowrap; height: 34px; padding: 0 10px;">
                                + Ekle
                            </button>
                        </form>
                    @endif

                    <!-- KATILIMCILAR LİSTESİ -->
                    @if($meeting->participants->count() > 0)
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @foreach($meeting->participants as $part)
                                <div style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; padding: 8px 10px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 12px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="width: 26px; height: 26px; border-radius: 50%; background: #3b82f6; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700;">
                                            {{ mb_substr($part->user?->name ?? 'U', 0, 1) }}
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; color: #0f172a;">{{ $part->user?->name ?? 'Belirtilmedi' }}</div>
                                            <div style="font-size: 10px; color: #64748b;">{{ $part->user?->title ?? 'Ekip Üyesi' }}</div>
                                        </div>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span style="font-size: 10px; font-weight: 700; color: #059669; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 2px 6px; border-radius: 4px;">
                                            {{ $part->status === 'attended' ? 'Katıldı' : 'Davetli' }}
                                        </span>
                                        @if(!auth()->user()?->isYonetim() && (auth()->user()?->isSuperAdmin() || auth()->user()?->isAdmin() || auth()->user()?->hasPermission('meeting.manage') || auth()->user()?->hasPermission('meeting.create') || $meeting->organizer_id === auth()->id()))
                                            <form action="{{ route('meetings.remove_participant', [$meeting->id, $part->id]) }}" method="POST" style="margin: 0; display: inline;" onsubmit="return confirm('{{ $part->user?->name ?? 'Katılımcı' }} kullanıcısının toplantı davetini iptal etmek / listeden çıkarmak istediğinize emin misiniz?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs" style="background: #fee2e2; border: 1px solid #fecaca; color: #b91c1c; padding: 2px 7px; border-radius: 4px; font-size: 10.5px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 3px; transition: all 0.15s;" onmouseover="this.style.background='#fecaca'" onmouseout="this.style.background='#fee2e2'" title="Daveti Geri Al / Listeden Çıkar">
                                                    <span>✕</span> <span>Geri Al</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <!-- Davetli yoksa tüm proje ekibi davetlidir -->
                        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 12px; font-size: 12px; color: #1e40af; text-align: center;">
                            👥 Bu toplantıya özel bir davetli listesi sınırlandırılmamıştır. <strong>Tüm Proje Ekibi</strong> doğal katılımcıdır.
                        </div>
                    @endif
                </div>
            </div>

            <!-- 3. SAP EKİP MODELİ HAKKINDA BİLGİLENDİRME -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px;">
                <div style="font-size: 12px; font-weight: 700; color: #1e3a8a; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                    <span>💡</span> SAP Modeli: Ekip Sorumluluğu
                </div>
                <div style="font-size: 11px; color: #475569; line-height: 1.5;">
                    Toplantıda tek bir sorumlu atanması yerine <strong>"Tüm Proje Ekibi"</strong> seçildiğinde, görev ortak bir operasyonel hedef olarak Kanban & Gantt panosuna işlenir ve tüm proje paydaşlarının görev listesine yansıtılır.
                </div>
            </div>

        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 1: HIZLI PROJE ÖZETİ VE DETAY MODALI (TOPLANTIDAN AYRILMADAN İNCELEME) -->
    <!-- ========================================================================= -->
    <div id="projectPreviewModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 20px;">
        <div style="background: #ffffff; border-radius: 12px; max-width: 720px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); border: 1px solid #e2e8f0;">
            <!-- Modal Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 18px;">📁</span>
                    <div>
                        <div style="font-size: 16px; font-weight: 800; color: #0f172a;">
                            {{ $meeting->project->code }} - {{ $meeting->project->title }}
                        </div>
                        <div style="font-size: 12px; color: #64748b;">
                            {{ $meeting->project->bolum?->name ?? 'Merkez Departman' }}
                        </div>
                    </div>
                </div>
                <button type="button" onclick="closeProjectPreviewModal()" style="background: none; border: none; font-size: 22px; cursor: pointer; color: #64748b; line-height: 1;">&times;</button>
            </div>

            <!-- Modal Body -->
            <div style="padding: 24px; display: flex; flex-direction: column; gap: 18px;">
                <!-- Durum ve İlerleme -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <div>
                        <div style="font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 4px;">PROJE DURUMU</div>
                        <x-status-badge :status="$meeting->project->status" />
                    </div>
                    <div>
                        <div style="font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 4px;">GENEL İLERLEME: %{{ (int) round($meeting->project->progress_percentage) }}</div>
                        <div style="background: #e2e8f0; border-radius: 10px; height: 10px; overflow: hidden; margin-top: 6px;">
                            <div style="width: {{ (int) round($meeting->project->progress_percentage) }}%; background: #2563eb; height: 100%;"></div>
                        </div>
                    </div>
                </div>

                <!-- Bütçe & Takvim Sağlığı -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px;">
                        <div style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 8px;">💰 Bütçe Durumu</div>
                        <div style="font-size: 12px; display: flex; flex-direction: column; gap: 4px;">
                            <div>Planlanan: <strong>{{ number_format((float)($meeting->project->planned_budget ?? 0), 2) }} {{ $meeting->project->currency ?? 'EUR' }}</strong></div>
                            <div>Harcanan: <strong>{{ number_format((float)($meeting->project->actual_spent ?? 0), 2) }} {{ $meeting->project->currency ?? 'EUR' }}</strong></div>
                        </div>
                    </div>
                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px;">
                        <div style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 8px;">📅 Tarih & Termin</div>
                        <div style="font-size: 12px; display: flex; flex-direction: column; gap: 4px;">
                            <div>Başlangıç: <strong>{{ $meeting->project->start_date ? $meeting->project->start_date->format('d.m.Y') : '-' }}</strong></div>
                            <div>Hedef Bitiş: <strong>{{ $meeting->project->target_end_date ? $meeting->project->target_end_date->format('d.m.Y') : '-' }}</strong></div>
                        </div>
                    </div>
                </div>

                <!-- Liderlik & Paydaşlar -->
                <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px;">
                    <div style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 8px;">👥 Proje Ekibi & Paydaşlar</div>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <span style="font-size: 12px; background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; padding: 3px 10px; border-radius: 4px;">
                            👑 Yöneticisi: <strong>{{ $meeting->project->manager?->name ?? 'Atanmadı' }}</strong>
                        </span>
                        <span style="font-size: 12px; background: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 3px 10px; border-radius: 4px;">
                            💼 Sponsoru: <strong>{{ $meeting->project->sponsor?->name ?? 'Atanmadı' }}</strong>
                        </span>
                        @foreach($projectTeam as $m)
                            <span style="font-size: 12px; background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; padding: 3px 8px; border-radius: 4px;">
                                👤 {{ $m->name }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <!-- Proje Açıklaması -->
                @if($meeting->project->description)
                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px;">
                        <div style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 6px;">📝 Proje Açıklaması</div>
                        <div style="font-size: 12.5px; color: #334155; line-height: 1.6;">
                            {!! $meeting->project->description !!}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Modal Footer -->
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
                <button type="button" onclick="closeProjectPreviewModal()" class="btn btn-outline" style="font-size: 12px; padding: 8px 16px; border-radius: 6px; border: 1px solid #cbd5e1; background: #ffffff; color: #475569; font-weight: 600;">
                    Kapat
                </button>
                <a href="{{ route('projects.show', $meeting->project->id) }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary" style="font-size: 12px; padding: 8px 18px; border-radius: 6px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    <span>📁</span> Yeni Sekmede Projenin Tam Detaylarını Aç ↗
                </a>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 2: BİLDİRİM MATRİSİ & PAYDAŞ SEÇİMİ İLE TOPLANTI DURUMUNU GÜNCELLEME MODALI -->
    <!-- (3. GÖRSELDEKİ HER BUTONA BASINCA AÇILIR, ZİL VE MAİL KANALLARI ÖZELLEŞTİRİLEBİLİR) -->
    <!-- ========================================================================= -->
    <div id="statusActionModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 20px;">
        <div style="background: #ffffff; border-radius: 12px; max-width: 680px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); border: 1px solid #e2e8f0;">
            <form action="{{ route('meetings.update_status', $meeting->id) }}" method="POST" id="statusActionForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" id="modalTargetStatus" value="">

                <!-- Modal Header -->
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span id="modalActionIcon" style="font-size: 20px;">⚡</span>
                        <div>
                            <div id="modalActionTitle" style="font-size: 16px; font-weight: 800; color: #0f172a;">
                                Toplantı Durumunu Güncelle
                            </div>
                            <div style="font-size: 12px; color: #64748b;">
                                <strong>{{ $meeting->project->code }}</strong> - {{ $meeting->title }}
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="closeStatusActionModal()" style="background: none; border: none; font-size: 22px; cursor: pointer; color: #64748b; line-height: 1;">&times;</button>
                </div>

                <!-- Modal Body -->
                <div style="padding: 22px; display: flex; flex-direction: column; gap: 16px;">
                    <div id="modalActionDesc" style="font-size: 13px; color: #334155; line-height: 1.5; background: #f0f9ff; border-left: 4px solid #0284c7; padding: 12px 14px; border-radius: 6px;">
                        Toplantı durumunu değiştirmek ve paydaşlara bildirim göndermek üzeresiniz.
                    </div>

                    <!-- İSTEĞE BAĞLI BİLDİRİM VE TUTANAK NOTU -->
                    <div>
                        <label class="form-label" style="font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">
                            📝 Bildirim Notu / Açıklama (İsteğe Bağlı)
                        </label>
                        <textarea name="custom_note" rows="2" class="form-control" placeholder="Toplantı statü değişikliği ile ilgili paydaşlara iletilecek ek açıklama veya sebep..." style="font-size: 13px;"></textarea>
                    </div>

                    <!-- BİLDİRİM MATRİSİ & PAYDAŞ SEÇİM LİSTESİ (ZİL VE MAİL ÖZELLEŞTİRMESİ) -->
                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; background: #ffffff;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 8px;">
                            <div>
                                <div style="font-size: 12.5px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                                    <span>🔔</span> Bildirim Dağıtımı & Paydaş Seçimi
                                </div>
                                <div style="font-size: 11px; color: #64748b;">
                                    Sistem standardında ayarlanan bildirim kanallarını kişi bazında özelleştirebilirsiniz.
                                </div>
                            </div>
                            <!-- TOPLU SEÇİM BUTONLARI -->
                            <div style="display: flex; gap: 8px;">
                                <button type="button" onclick="toggleAllCheckboxes('bell')" class="btn btn-sm btn-outline" style="font-size: 11px; padding: 3px 8px; border-radius: 4px; border: 1px solid #cbd5e1;">
                                    Zil Tümünü Aç/Kapa
                                </button>
                                <button type="button" onclick="toggleAllCheckboxes('mail')" class="btn btn-sm btn-outline" style="font-size: 11px; padding: 3px 8px; border-radius: 4px; border: 1px solid #cbd5e1;">
                                    Mail Tümünü Aç/Kapa
                                </button>
                            </div>
                        </div>

                        <!-- PAYDAŞ TABLOSU -->
                        <div style="max-height: 220px; overflow-y: auto; border: 1px solid #f1f5f9; border-radius: 6px;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                                <thead>
                                    <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-size: 10.5px; font-weight: 700; color: #475569; text-transform: uppercase;">
                                        <th style="padding: 8px 12px;">Paydaş / Katılımcı</th>
                                        <th style="padding: 8px 12px; text-align: center; width: 100px;">🔔 Zil Bildirimi</th>
                                        <th style="padding: 8px 12px; text-align: center; width: 100px;">📧 E-Posta</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allNotificationRecipients as $recipient)
                                        <tr style="border-bottom: 1px solid #f8fafc;">
                                            <td style="padding: 8px 12px;">
                                                <div style="font-weight: 600; color: #0f172a;">{{ $recipient->name }}</div>
                                                <div style="font-size: 10.5px; color: #64748b;">
                                                    {{ $recipient->title ?? ($recipient->bolum?->name ?? 'Proje Paydaşı') }}
                                                    @if($meeting->project->manager_id === $recipient->id)
                                                        <span style="color: #2563eb; font-weight: 700;">(Yönetici)</span>
                                                    @elseif($meeting->project->sponsor_id === $recipient->id)
                                                        <span style="color: #b45309; font-weight: 700;">(Sponsor)</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td style="padding: 8px 12px; text-align: center;">
                                                <input type="checkbox" name="notify_bell[]" value="{{ $recipient->id }}" class="chk-bell" checked style="width: 16px; height: 16px; cursor: pointer;">
                                            </td>
                                            <td style="padding: 8px 12px; text-align: center;">
                                                <input type="checkbox" name="notify_mail[]" value="{{ $recipient->id }}" class="chk-mail" checked style="width: 16px; height: 16px; cursor: pointer;">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div style="display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
                    <button type="button" onclick="closeStatusActionModal()" class="btn btn-outline" style="font-size: 12px; padding: 8px 16px; border-radius: 6px; border: 1px solid #cbd5e1; background: #ffffff; color: #475569; font-weight: 600;">
                        Vazgeç
                    </button>
                    <button type="submit" id="modalSubmitBtn" class="btn btn-primary" style="font-size: 12px; padding: 8px 20px; border-radius: 6px; font-weight: 700;">
                        Onayla ve Bildir
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 3: GÜNDEM MADDELERİNİ DÜZENLEME MODALI (QUILL.JS ZENGİN METİN) -->
    <!-- ========================================================================= -->
    <div id="editAgendaModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 20px;">
        <div style="background: #ffffff; border-radius: 12px; max-width: 650px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); border: 1px solid #e2e8f0; overflow: hidden;">
            <form action="{{ route('meetings.update_agenda', $meeting->id) }}" method="POST" id="editAgendaForm" onsubmit="syncAgendaQuillBeforeSubmit()">
                @csrf
                @method('PUT')
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                    <span style="font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <span>📋</span> Gündem Maddelerini Düzenle (Zengin Metin)
                    </span>
                    <button type="button" onclick="closeEditAgendaModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #64748b;">&times;</button>
                </div>
                <div style="padding: 20px;">
                    <label class="form-label" style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px; display: block;">
                        Gündem maddelerini liste, kalın, italik ve bağlantılarla biçimlendirebilirsiniz:
                    </label>
                    <div id="quillEditAgendaContainer" style="min-height: 160px; background: #ffffff;"></div>
                    <input type="hidden" name="agenda" id="editAgendaHiddenInput">
                    <div style="font-size: 11px; color: #64748b; margin-top: 8px; line-height: 1.4;">
                        💡 Madde işaretleri, numaralandırma ve vurgular aynen toplantı detayında ve bildirimlerde görüntülenecektir.
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; padding: 14px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
                    <button type="button" onclick="closeEditAgendaModal()" class="btn btn-outline" style="font-size: 12px; padding: 7px 14px; border-radius: 6px; border: 1px solid #cbd5e1; background: #ffffff; color: #475569;">
                        İptal
                    </button>
                    <button type="submit" class="btn btn-primary" style="font-size: 12px; padding: 7px 18px; border-radius: 6px; font-weight: 600;">
                        💾 Gündemi Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 4: TOPLANTI ERTELEME MODALI (BİLDİRİM MATRİSİ VE KANALLAR) -->
    <!-- ========================================================================= -->
    <div id="postponeModalShowBackdrop" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 20px;">
        <div style="background: #ffffff; border-radius: 12px; max-width: 540px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); border: 1px solid #e2e8f0; overflow: hidden;">
            <form action="{{ route('meetings.postpone', $meeting->id) }}" method="POST" id="postponeFormShow">
                @csrf
                <div style="padding: 18px 24px; border-bottom: 1px solid #e2e8f0; background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 22px;">⏱️</span>
                        <div>
                            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #92400e;">Toplantıyı Ertele</h3>
                            <div style="font-size: 12px; color: #b45309;">Yeni tarih belirleyip katılımcılara bilgilendirme iletin</div>
                        </div>
                    </div>
                    <button type="button" onclick="closePostponeModalShow()" style="background: none; border: none; font-size: 22px; cursor: pointer; color: #92400e; line-height: 1;">&times;</button>
                </div>

                <div style="padding: 22px 24px; display: flex; flex-direction: column; gap: 16px;">
                    <div>
                        <div style="font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 2px;">{{ $meeting->title }}</div>
                        <div style="font-size: 11.5px; color: #64748b;">Mevcut Tarih: {{ $meeting->meeting_date->format('d.m.Y H:i') }}</div>
                    </div>

                    <div>
                        <label class="form-label" style="font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 6px; display: block;">
                            📅 Yeni Toplantı Tarihi & Saati <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="datetime-local" name="new_meeting_date" value="{{ $meeting->meeting_date->format('Y-m-d\TH:i') }}" class="form-control" required style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 9px 12px; font-size: 13px; width: 100%; box-sizing: border-box;">
                    </div>

                    <div>
                        <label class="form-label" style="font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 6px; display: block;">
                            📍 Toplantı Yeri / Ortamı (İsteğe Bağlı)
                        </label>
                        <input type="text" name="location" value="{{ $meeting->location }}" class="form-control" placeholder="Örn: 2. OSB Şantiye Ofisi veya Teams Linki" style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 9px 12px; font-size: 13px; width: 100%; box-sizing: border-box;">
                    </div>

                    <div>
                        <label class="form-label" style="font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 6px; display: block;">
                            📝 Erteleme Nedeni / Açıklama Notu
                        </label>
                        <textarea name="postpone_reason" rows="3" class="form-control" placeholder="Örn: Saha şefinin acil beton döküm denetimi nedeniyle toplantı ertelenmiştir." style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 9px 12px; font-size: 13px; width: 100%; box-sizing: border-box; line-height: 1.5;"></textarea>
                    </div>

                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px;">
                        <div style="font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 8px;">📢 Katılımcı Bilgilendirme Kanalları (Bildirim Matrisi):</div>
                        <div style="display: flex; gap: 20px; font-size: 12.5px;">
                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                <input type="checkbox" name="notify_bell" value="1" checked style="accent-color: #d97706;">
                                🔔 Zil Bildirimi Gönder
                            </label>
                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                <input type="checkbox" name="notify_mail" value="1" checked style="accent-color: #d97706;">
                                📧 E-Posta Gönder
                            </label>
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
                    <button type="button" onclick="closePostponeModalShow()" class="btn btn-outline" style="font-size: 12px; padding: 8px 16px; border-radius: 6px; border: 1px solid #cbd5e1; background: #ffffff; color: #475569; font-weight: 600;">
                        Vazgeç
                    </button>
                    <button type="submit" class="btn btn-primary" style="font-size: 12px; padding: 8px 20px; border-radius: 6px; font-weight: 700; background: #d97706; border-color: #b45309; color: #ffffff;">
                        ⏱️ Toplantıyı Ertele ve Bildir
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Quill.js Zengin Metin Editörü Scripti -->
    <script src="{{ asset('vendor/quill/quill.js') }}"></script>
    <script>
        let quillMinutes = null;
        let quillEditAgenda = null;

        document.addEventListener('DOMContentLoaded', function () {
            // 1. Tutanak Quill Editörü Başlatma
            const minutesContainer = document.getElementById('quillMinutesEditorContainer');
            if (minutesContainer && typeof Quill !== 'undefined') {
                quillMinutes = new Quill(minutesContainer, {
                    theme: 'snow',
                    placeholder: 'Toplantı süresince görüşülen teknik konular, sahadaki gecikmeler, malzeme tedarik kararları ve genel notlar buraya kaydedilir...',
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ 'header': [1, 2, 3, false] }],
                            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                            ['blockquote', 'code-block'],
                            [{ 'color': [] }, { 'background': [] }],
                            ['link', 'clean']
                        ]
                    }
                });

                // Mevcut içeriği yükle
                const initialContent = {!! json_encode($meeting->minutes ?? '') !!};
                if (initialContent) {
                    quillMinutes.root.innerHTML = initialContent;
                }

                // Değişim anında gizli inputu güncelle
                quillMinutes.on('text-change', function () {
                    const hiddenInput = document.getElementById('minutesHiddenInput');
                    if (hiddenInput) {
                        hiddenInput.value = quillMinutes.root.innerHTML;
                    }
                });
            }

            // 2. Gündem Düzenleme Quill Editörü Başlatma
            const editAgendaContainer = document.getElementById('quillEditAgendaContainer');
            if (editAgendaContainer && typeof Quill !== 'undefined') {
                quillEditAgenda = new Quill(editAgendaContainer, {
                    theme: 'snow',
                    placeholder: '1. Temel beton kırım test sonuçlarının değerlendirilmesi\n2. Vinç kurulum sahası zemin sertleştirme kontrolü...',
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline'],
                            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                            ['link', 'clean']
                        ]
                    }
                });

                const initialAgenda = {!! json_encode($meeting->agenda ?? '') !!};
                if (initialAgenda) {
                    quillEditAgenda.root.innerHTML = initialAgenda;
                }
            }
        });

        function syncQuillBeforeSubmit() {
            if (quillMinutes) {
                const hiddenInput = document.getElementById('minutesHiddenInput');
                if (hiddenInput) {
                    hiddenInput.value = quillMinutes.root.innerHTML;
                }
            }
            return true;
        }

        function syncAgendaQuillBeforeSubmit() {
            if (quillEditAgenda) {
                const hiddenInput = document.getElementById('editAgendaHiddenInput');
                if (hiddenInput) {
                    hiddenInput.value = (quillEditAgenda.getText().trim() === '') ? '' : quillEditAgenda.root.innerHTML;
                }
            }
            return true;
        }

        // ==========================================
        // PROJE ÖNİZLEME MODALI FONKSİYONLARI
        // ==========================================
        function openProjectPreviewModal() {
            const modal = document.getElementById('projectPreviewModal');
            if (modal) {
                modal.style.display = 'flex';
            }
        }

        function closeProjectPreviewModal() {
            const modal = document.getElementById('projectPreviewModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        // ==========================================
        // TOPLANTI DURUM & BİLDİRİM MATRİSİ MODALI
        // ==========================================
        function openStatusActionModal(targetStatus, title, description, color, buttonText) {
            const modal = document.getElementById('statusActionModal');
            if (!modal) return;

            document.getElementById('modalTargetStatus').value = targetStatus;
            document.getElementById('modalActionTitle').innerText = title;
            document.getElementById('modalActionDesc').innerHTML = description;
            
            const submitBtn = document.getElementById('modalSubmitBtn');
            submitBtn.innerText = buttonText || 'Onayla ve Bildir';
            if (color) {
                submitBtn.style.backgroundColor = color;
                submitBtn.style.borderColor = color;
            }

            modal.style.display = 'flex';
        }

        function closeStatusActionModal() {
            const modal = document.getElementById('statusActionModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        // ==========================================
        // TOPLANTI ERTELEME MODALI (SHOW VIEW)
        // ==========================================
        function openPostponeModalShow() {
            const modal = document.getElementById('postponeModalShowBackdrop');
            if (modal) {
                modal.style.display = 'flex';
            }
        }

        function closePostponeModalShow() {
            const modal = document.getElementById('postponeModalShowBackdrop');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        // Toplu Checkbox Seç/Kaldır
        let bellAllState = true;
        let mailAllState = true;

        function toggleAllCheckboxes(type) {
            if (type === 'bell') {
                bellAllState = !bellAllState;
                document.querySelectorAll('.chk-bell').forEach(cb => cb.checked = bellAllState);
            } else if (type === 'mail') {
                mailAllState = !mailAllState;
                document.querySelectorAll('.chk-mail').forEach(cb => cb.checked = mailAllState);
            }
        }

        // ==========================================
        // GÜNDEM DÜZENLEME MODALI
        // ==========================================
        function openEditAgendaModal() {
            const modal = document.getElementById('editAgendaModal');
            if (modal) {
                modal.style.display = 'flex';
            }
        }

        function closeEditAgendaModal() {
            const modal = document.getElementById('editAgendaModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        // Modal dışına tıklayınca kapatma
        window.addEventListener('click', function(event) {
            const previewModal = document.getElementById('projectPreviewModal');
            const statusModal = document.getElementById('statusActionModal');
            const agendaModal = document.getElementById('editAgendaModal');
            const postponeModal = document.getElementById('postponeModalShowBackdrop');

            if (event.target === previewModal) closeProjectPreviewModal();
            if (event.target === statusModal) closeStatusActionModal();
            if (event.target === agendaModal) closeEditAgendaModal();
            if (event.target === postponeModal) closePostponeModalShow();
        });
    </script>
@endpush