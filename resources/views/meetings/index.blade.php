@extends('layouts.app')

@section('title', 'Proje Toplantıları')
@section('page_title', 'Toplantı & Koordinasyon Yönetimi')
@section('page_subtitle', 'Gündemler, katılımcı yoklaması, tutanaklar ve SAP ekip modeliyle karardan göreve dönüşüm')

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
            min-height: 120px;
            background: #ffffff;
        }

        .ql-editor {
            line-height: 1.6;
            color: #1e293b;
            min-height: 120px;
        }

        .meeting-rich-content {
            font-size: 13px;
            line-height: 1.65;
            color: #334155;
        }

        .meeting-rich-content p {
            margin-bottom: 6px;
        }

        .meeting-rich-content ul,
        .meeting-rich-content ol {
            padding-left: 20px;
            margin-bottom: 6px;
        }

        /* Katılımcı Arama & Rozet Stilleri */
        .participant-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            border-radius: 20px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 600;
        }

        .participant-chip .remove-chip {
            background: none;
            border: none;
            color: #0284c7;
            cursor: pointer;
            font-weight: 800;
            padding: 0;
            line-height: 1;
            font-size: 14px;
        }

        .participant-chip .remove-chip:hover {
            color: #dc2626;
        }

        .user-search-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
            z-index: 1050;
            max-height: 240px;
            overflow-y: auto;
            display: none;
        }

        .user-search-item {
            padding: 8px 12px;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.15s ease;
        }

        .user-search-item:hover {
            background: #f0f9ff;
        }

        /* İlgili Proje Arama Dropdown Stilleri */
        .project-select-trigger {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 13px;
            cursor: pointer;
            min-height: 40px;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
        }

        .project-select-trigger:hover,
        .project-select-trigger.active {
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }

        .project-search-dropdown {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            width: 100%;
            min-width: 320px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.14);
            z-index: 1060;
            display: none;
            overflow: hidden;
        }

        .project-search-item {
            padding: 9px 12px;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.15s ease;
        }

        .project-search-item:hover {
            background: #f0f9ff;
        }

        .project-search-item.selected {
            background: #e0f2fe;
            border-left: 3px solid #0284c7;
        }

        /* Erteleme Modalı */
        .postpone-modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            box-sizing: border-box;
        }

        .postpone-modal-card {
            background: #ffffff;
            width: 100%;
            max-width: 560px;
            border-radius: 14px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            animation: modalFadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.96);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>
@endpush

@section('content')
    <!-- ÜST İSTATİSTİK ŞERİDİ -->
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 16px; margin-bottom: 20px;">
        <div class="card" style="padding: 16px; display: flex; align-items: center; gap: 14px; margin-bottom: 0;">
            <div
                style="width: 44px; height: 44px; border-radius: 10px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                📅
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Toplam
                    Toplantı</div>
                <div style="font-size: 20px; font-weight: 800; color: var(--navy);">
                    {{ number_format($stats['total'] ?? 0) }}</div>
            </div>
        </div>

        <div class="card" style="padding: 16px; display: flex; align-items: center; gap: 14px; margin-bottom: 0;">
            <div
                style="width: 44px; height: 44px; border-radius: 10px; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                ⏱️
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">
                    Planlanan / Bekleyen</div>
                <div style="font-size: 20px; font-weight: 800; color: #b45309;">
                    {{ number_format($stats['scheduled'] ?? 0) }}</div>
            </div>
        </div>

        <div class="card" style="padding: 16px; display: flex; align-items: center; gap: 14px; margin-bottom: 0;">
            <div
                style="width: 44px; height: 44px; border-radius: 10px; background: #fffbeb; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 20px; border: 1px solid #fde68a;">
                ⏳
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #b45309; text-transform: uppercase;">Ertelenen
                    Toplantılar</div>
                <div style="font-size: 20px; font-weight: 800; color: #d97706;">
                    {{ number_format($stats['postponed'] ?? 0) }}</div>
            </div>
        </div>

        <div class="card" style="padding: 16px; display: flex; align-items: center; gap: 14px; margin-bottom: 0;">
            <div
                style="width: 44px; height: 44px; border-radius: 10px; background: #dcfce7; color: #15803d; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                ✅
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">
                    Tamamlanan / Tutanaklı</div>
                <div style="font-size: 20px; font-weight: 800; color: #15803d;">
                    {{ number_format($stats['completed'] ?? 0) }}</div>
            </div>
        </div>
    </div>

    <!-- YENİ TOPLANTI PLANLAMA KARTI -->
    @if(!auth()->user()->isYonetim() && (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->hasPermission('meeting.create') || auth()->user()->hasPermission('meeting.manage')))
        <div class="card" style="margin-bottom: 24px; border: 1px solid #e2e8f0; border-radius: 10px;">
            <div class="card-header"
                style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <span class="card-title"
                    style="font-size: 15px; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 8px;">
                    <span>➕</span> Yeni Proje Toplantısı Planla
                </span>
                <span style="font-size: 12px; color: var(--text-muted);">Tüm proje paydaşlarına bildirim ve takvim daveti (Zil &
                    Mail) iletilir</span>
            </div>
            <div class="card-body" style="padding: 20px;">
                <form action="{{ route('meetings.store') }}" method="POST" id="createMeetingForm">
                    @csrf
                    <div
                        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 16px;">
                        <div class="form-group" style="margin-bottom: 0; position: relative;" id="projectSelectContainer">
                            <label class="form-label" style="font-weight: 600; font-size: 12px;">📁 İlgili Proje *</label>

                            <!-- Gizli Form Değeri -->
                            <input type="hidden" name="project_id" id="meetingProjectSelect"
                                value="{{ $selectedProjectId ?? '' }}" required>

                            <!-- Arama Açılır Kutu Tetikleyicisi -->
                            <div class="project-select-trigger" id="projectSelectTrigger" onclick="toggleProjectDropdown()">
                                <div id="projectSelectText"
                                    style="display: flex; align-items: center; gap: 6px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    @php
                                        $currentSelectedProject = null;
                                        if (isset($selectedProjectId)) {
                                            $currentSelectedProject = $projects->firstWhere('id', $selectedProjectId);
                                        }
                                    @endphp
                                    @if($currentSelectedProject)
                                        <span
                                            style="font-weight: 700; font-size: 11.5px; color: #1d4ed8; background: #eff6ff; padding: 1px 6px; border-radius: 4px; border: 1px solid #bfdbfe;">{{ $currentSelectedProject->code }}</span>
                                        <span
                                            style="color: #0f172a; font-weight: 600;">{{ Str::limit($currentSelectedProject->title, 36) }}</span>
                                    @else
                                        <span style="color: #94a3b8;">-- Proje Seçiniz (Arama yapabilirsiniz) --</span>
                                    @endif
                                </div>
                                <span style="font-size: 11px; color: #64748b; margin-left: 8px;">▼</span>
                            </div>

                            <!-- Arama Kutulu Menü -->
                            <div class="project-search-dropdown" id="projectSearchDropdown">
                                <div
                                    style="padding: 8px 10px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; z-index: 2;">
                                    <div style="position: relative;">
                                        <span
                                            style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 12px; color: #94a3b8; pointer-events: none;">🔍</span>
                                        <input type="text" id="projectSearchInput" class="form-control"
                                            placeholder="Proje kodu, başlık veya etiket ile ara..."
                                            style="font-size: 12px; height: 32px; padding-left: 30px;" autocomplete="off"
                                            oninput="filterProjectDropdown(this.value)">
                                    </div>
                                </div>
                                <div id="projectSearchResultsList" style="max-height: 220px; overflow-y: auto; padding: 4px 0;">
                                    @foreach($projects as $p)
                                        @php
                                            $pSearchMeta = strtolower($p->code . ' ' . $p->title . ' ' . ($p->category?->name ?? '') . ' ' . (is_array($p->tags) ? implode(' ', $p->tags) : ($p->tags ?? '')));
                                            $isSel = (isset($selectedProjectId) && $selectedProjectId == $p->id);
                                        @endphp
                                        <div class="project-search-item {{ $isSel ? 'selected' : '' }}"
                                            data-project-id="{{ $p->id }}" data-project-code="{{ $p->code }}"
                                            data-project-title="{{ $p->title }}" data-search="{{ $pSearchMeta }}"
                                            onclick="selectProjectItem({{ $p->id }}, '{{ addslashes($p->code) }}', '{{ addslashes($p->title) }}')">
                                            <div style="flex: 1; min-width: 0;">
                                                <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                                    <span
                                                        style="font-weight: 700; font-size: 11px; color: #1d4ed8; background: #eff6ff; padding: 1px 5px; border-radius: 4px; border: 1px solid #bfdbfe;">{{ $p->code }}</span>
                                                    <span
                                                        style="font-weight: 600; font-size: 12px; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $p->title }}</span>
                                                </div>
                                                @if($p->category || !empty($p->tags))
                                                    <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                                        {{ $p->category?->name ?? '' }}
                                                        @if(!empty($p->tags))
                                                            • {{ is_array($p->tags) ? implode(', ', $p->tags) : $p->tags }}
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                            <span class="project-item-check"
                                                style="color: #0284c7; font-weight: bold; font-size: 13px; {{ $isSel ? '' : 'display: none;' }}">✓</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-weight: 600; font-size: 12px;">📌 Toplantı Başlığı *</label>
                            <input type="text" name="title" class="form-control" required
                                placeholder="Örn: 2. OSB Saha Çelik Montaj & İSG Koordinasyonu" style="font-size: 13px;">
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-weight: 600; font-size: 12px;">📅 Tarih & Saat *</label>
                            <input type="datetime-local" name="meeting_date" class="form-control" required
                                style="font-size: 13px;">
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-weight: 600; font-size: 12px;">📍 Toplantı Yeri /
                                Ortamı</label>
                            <input type="text" name="location" class="form-control"
                                placeholder="Örn: Şantiye Ofisi / Konferans Salonu" style="font-size: 13px;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <!-- ZENGİN METİN GÜNDEM ALANI (QUILL.JS) -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label"
                                style="font-weight: 600; font-size: 12px; display: flex; justify-content: space-between;">
                                <span>📝 Toplantı Gündemi & Görüşülecek Maddeler (Zengin Metin)</span>
                                <span style="font-size: 11px; color: #64748b;">Madde işaretleri, numaralandırma ve biçimlendirme
                                    desteklenir</span>
                            </label>
                            <div id="quillAgendaEditor"></div>
                            <input type="hidden" name="agenda" id="agendaHiddenInput">
                        </div>

                        <!-- KATILIMCI ARAMA VE ÇOKTAN SEÇİM ALANI -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-weight: 600; font-size: 12px;">
                                👥 Katılımcı Daveti (Zil & Mail Bildirimi İletilir)
                            </label>

                            <!-- SEÇİLİ KATILIMCI ROZETLERİ -->
                            <div id="selectedParticipantsContainer"
                                style="display: flex; flex-wrap: wrap; gap: 6px; min-height: 34px; padding: 6px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; margin-bottom: 8px;">
                                <span id="noParticipantPlaceholder"
                                    style="font-size: 12px; color: #94a3b8; align-self: center; padding-left: 4px;">
                                    Henüz katılımcı seçilmedi. Aşağıdan arayarak ekleyebilirsiniz.
                                </span>
                            </div>
                            <div id="hiddenParticipantsInputs"></div>

                            <!-- ARAMA KUTUSU VE ANLIK DROPDOWN -->
                            <div style="position: relative;">
                                <input type="text" id="participantSearchInput" class="form-control"
                                    placeholder="İsim, unvan veya departman ile ara..." style="font-size: 13px;"
                                    autocomplete="off" onfocus="showSearchDropdown()"
                                    oninput="handleParticipantSearch(this.value)">

                                <div id="participantSearchDropdown" class="user-search-dropdown">
                                    <div id="participantSearchResults">
                                        @foreach($initialUsers as $u)
                                            <div class="user-search-item" data-user-id="{{ $u->id }}"
                                                onclick="addParticipant({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->unvan ?? ($u->title ?? 'Personel')) }}')">
                                                <div>
                                                    <div style="font-weight: 600; font-size: 12.5px; color: #0f172a;">{{ $u->name }}
                                                    </div>
                                                    <div style="font-size: 11px; color: #64748b;">
                                                        {{ $u->unvan ?? ($u->title ?? 'Personel') }} •
                                                        {{ $u->bolum?->ad ?? ($u->bolum?->name ?? 'Merkez') }}</div>
                                                </div>
                                                <span
                                                    style="font-size: 11px; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 2px 8px; border-radius: 4px;">+
                                                    Ekle</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <small style="color: var(--text-muted); font-size: 11px; display: block; margin-top: 4px;">
                                💡 Katılımcı eklemek için listeden seçiniz. Seçilen her kullanıcıya davet bildirimi gidecektir.
                            </small>
                        </div>
                    </div>

                    <div
                        style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 14px; flex-wrap: wrap; gap: 10px;">
                        <span style="font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 6px;">
                            <span>👤</span> <strong>Planlayan / Düzenleyen:</strong> {{ auth()->user()->name }}
                            ({{ auth()->user()->title ?? 'Proje Yetkilisi' }})
                        </span>
                        <button type="submit" class="btn btn-primary"
                            style="display: inline-flex; align-items: center; gap: 6px; padding: 9px 20px; font-weight: 600;">
                            <span>📅</span> Toplantıyı Kaydet ve Bildirim Gönder
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MEVCUT TOPLANTILAR LİSTESİ & GELİŞMİŞ FİLTRELEME -->
    <div class="card" style="border: 1px solid #e2e8f0; border-radius: 10px;">
        <div class="card-header"
            style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 18px 22px; display: flex !important; flex-direction: column !important; align-items: stretch !important; gap: 14px !important;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <span class="card-title"
                        style="font-size: 16px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <span>🗓️</span> Planlanan & Tamamlanan Toplantılar
                    </span>
                    <span
                        style="background: #e0f2fe; color: #0284c7; border: 1.5px solid #bae6fd; font-size: 12px; font-weight: 750; padding: 4px 14px; border-radius: 9999px; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 1px 3px rgba(2, 132, 199, 0.08);">
                        <span style="width: 7px; height: 7px; border-radius: 50%; background: #0284c7;"></span>
                        {{ $meetings->total() }} Toplantı Kaydı
                    </span>
                </div>
                @if(request()->hasAny(['search', 'project_id', 'status']))
                    <div
                        style="font-size: 12px; color: #0284c7; background: #f0f9ff; padding: 4px 12px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; border: 1px solid #e0f2fe;">
                        <span>🔍</span> Filtrelenmiş Liste
                    </div>
                @endif
            </div>

            <!-- ANLIK ARAMA & ÇOKLU FİLTRE ÇUBUĞU -->
            <form action="{{ route('meetings.index') }}" method="GET"
                style="display: grid; grid-template-columns: 2fr 1.5fr 1.2fr auto; gap: 10px; align-items: center; width: 100%;">
                <div>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Toplantı başlığı, gündem, lokasyon veya proje ara..." class="form-control"
                        style="font-size: 12.5px; height: 36px;">
                </div>
                <div>
                    <select name="project_id" class="form-control" style="font-size: 12.5px; height: 36px;">
                        <option value="">-- Tüm Projelerin Toplantıları --</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ (request('project_id') == $p->id || (isset($selectedProjectId) && $selectedProjectId == $p->id)) ? 'selected' : '' }}>
                                {{ $p->code }} - {{ Str::limit($p->title, 35) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="status" class="form-control" style="font-size: 12.5px; height: 36px;">
                        <option value="all">Tüm Durumlar</option>
                        <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>⏱️ Planlanan /
                            Bekleyen</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>🔄 Devam
                            Ediyor</option>
                        <option value="postponed" {{ request('status') === 'postponed' ? 'selected' : '' }}>⏳ Ertelenenler
                        </option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>✅ Tamamlananlar
                        </option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>❌ İptal Edilenler
                        </option>
                    </select>
                </div>
                <div style="display: flex; gap: 6px;">
                    <button type="submit" class="btn btn-primary"
                        style="height: 36px; padding: 0 14px; font-size: 12.5px; display: inline-flex; align-items: center; gap: 4px;">
                        🔍 Filtrele
                    </button>
                    @if(request()->hasAny(['search', 'project_id', 'status']))
                        <a href="{{ route('meetings.index') }}" class="btn btn-outline"
                            style="height: 36px; padding: 0 12px; font-size: 12.5px; display: inline-flex; align-items: center; text-decoration: none; background: #ffffff; border: 1px solid #cbd5e1; color: #64748b;">
                            ✕ Sıfırla
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="card-body" style="padding: 20px;">
            @forelse($meetings as $m)
                @php
                    $user = auth()->user();
                    $canPostpone = !$user->isYonetim() && ($user->isSuperAdmin() || $user->isAdmin() || $user->hasPermission('meeting.manage') || $m->organizer_id === $user->id);
                @endphp
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); transition: border-color 0.2s;"
                    onmouseover="this.style.borderColor='#93c5fd'" onmouseout="this.style.borderColor='#e2e8f0'">

                    <!-- KART ÜST BAŞLIK, PROJE LİNKİ VE STATÜ -->
                    <div
                        style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; gap: 12px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <!-- TIKLANABİLİR PROJE KODU LİNKİ -->
                            @if($m->project)
                                <a href="{{ route('projects.show', $m->project->id) }}" target="_blank" rel="noopener noreferrer"
                                    style="display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 800; color: #1d4ed8; background: #eff6ff; border: 1px solid #bfdbfe; padding: 4px 10px; border-radius: 6px; text-decoration: none; transition: all 0.2s;"
                                    onmouseover="this.style.background='#dbeafe'; this.style.borderColor='#93c5fd';"
                                    onmouseout="this.style.background='#eff6ff'; this.style.borderColor='#bfdbfe';"
                                    title="Projeyi Yeni Sekmede Aç: {{ $m->project->title }}">
                                    📁 {{ $m->project->code }} ↗
                                </a>
                            @endif

                            <h3 style="font-size: 17px; font-weight: 700; color: #0f172a; margin: 0; display: inline-block;">
                                {{ $m->title }}
                            </h3>

                            <!-- STATÜ ROZETİ -->
                            <span
                                style="font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px; display: inline-flex; align-items: center; gap: 4px; {!! $m->status_badge_style !!}">
                                @if($m->status === 'completed')
                                    ✅ Tamamlandı
                                @elseif($m->status === 'in_progress')
                                    🔄 Devam Ediyor
                                @elseif($m->status === 'postponed')
                                    ⏳ Ertelendi
                                @elseif($m->status === 'cancelled')
                                    ❌ İptal Edildi
                                @else
                                    ⏱️ Planlandı / Bekliyor
                                @endif
                            </span>
                        </div>

                        <!-- TARİH VE LOKASYON BİLGİSİ -->
                        <div
                            style="display: flex; align-items: center; gap: 12px; font-size: 12px; color: #475569; background: #f8fafc; padding: 6px 12px; border-radius: 6px; border: 1px solid #e2e8f0; flex-wrap: wrap;">
                            <span
                                style="font-weight: 600; color: #1e293b; display: inline-flex; align-items: center; gap: 4px;">
                                📅 {{ $m->meeting_date->format('d.m.Y H:i') }}
                            </span>
                            <span style="color: #cbd5e1;">•</span>
                            <span style="display: inline-flex; align-items: center; gap: 4px;">
                                📍 {{ $m->location ?? 'Şantiye Ofisi / Konferans Salonu' }}
                            </span>
                        </div>
                    </div>

                    <!-- ERTELEME GEREKÇESİ BİLGİ KUTUSU (Eğer Toplantı Ertelenmişse) -->
                    @if($m->status === 'postponed' && $m->postpone_reason)
                        <div
                            style="background: #fffbeb; border: 1px solid #fde68a; border-left: 4px solid #f59e0b; border-radius: 6px; padding: 10px 14px; margin-bottom: 14px; font-size: 12.5px; color: #92400e;">
                            <strong>⏱️ Toplantı Erteleme Gerekçesi / Notu:</strong>
                            <div style="margin-top: 3px;">{{ $m->postpone_reason }}</div>
                        </div>
                    @endif

                    <!-- META BİLGİ ŞERİDİ: PLANLAYAN KİŞİ, KATILIMCILAR VE AKSİYON BUTONLARI -->
                    <div
                        style="display: flex; align-items: center; gap: 16px; margin-bottom: 14px; padding-bottom: 12px; border-bottom: 1px dashed #e2e8f0; font-size: 12px; flex-wrap: wrap;">
                        <!-- TOPLANTIYI PLANLAYAN KİŞİ -->
                        <div
                            style="display: inline-flex; align-items: center; gap: 6px; background: #f1f5f9; padding: 4px 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                            <span style="color: #64748b; font-weight: 600;">👤 Düzenleyen:</span>
                            <strong
                                style="color: #0f172a;">{{ $m->organizer ? $m->organizer->getMaskedNameFor(auth()->user()) : 'Proje Yöneticisi' }}</strong>
                            @if($m->organizer?->title)
                                <span style="color: #64748b; font-size: 11px;">({{ $m->organizer->title }})</span>
                            @endif
                        </div>

                        <!-- KATILIMCI SAYISI -->
                        <div style="display: inline-flex; align-items: center; gap: 6px; color: #64748b;">
                            <span>👥</span>
                            <span>Davetli Katılımcı:
                                <strong>{{ $m->participants->count() > 0 ? $m->participants->count() . ' Kişi' : 'Tüm Proje Ekibi' }}</strong></span>
                        </div>

                        <!-- ALINAN KARAR SAYISI -->
                        @if($m->actions->count() > 0)
                            <div
                                style="display: inline-flex; align-items: center; gap: 6px; background: #ecfdf5; color: #047857; padding: 4px 10px; border-radius: 6px; font-weight: 600;">
                                <span>⚡</span>
                                <span>{{ $m->actions->count() }} Karar Göreve Dönüştürüldü</span>
                            </div>
                        @endif

                        <!-- AKSİYON BUTONLARI (ERTELEME & DETAY) -->
                        <div style="margin-left: auto; display: flex; gap: 8px; align-items: center;">
                            @if($canPostpone && $m->status !== 'completed' && $m->status !== 'cancelled')
                                <button type="button"
                                    onclick="openPostponeModal({{ $m->id }}, '{{ addslashes($m->title) }}', '{{ $m->meeting_date->format('Y-m-d\TH:i') }}', '{{ addslashes($m->location ?? '') }}')"
                                    style="display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; font-size: 12px; font-weight: 700; color: #b45309; cursor: pointer;">
                                    ⏱️ Toplantıyı Ertele
                                </button>
                            @endif

                            <a href="{{ route('meetings.show', $m->id) }}"
                                style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: 700; color: #1e293b; text-decoration: none; transition: all 0.2s;"
                                onmouseover="this.style.background='#e2e8f0'; this.style.color='#0f172a';"
                                onmouseout="this.style.background='#f8fafc'; this.style.color='#1e293b';">
                                👁️ Toplantı Tutanak & Detay Sayfası →
                            </a>
                        </div>
                    </div>

                    <!-- GÜNDEM MADDELERİ (ZENGİN METİN DESTEKLİ) -->
                    @if($m->agenda)
                        <div
                            style="margin-bottom: 14px; background: #fafafa; padding: 14px; border-radius: 6px; border-left: 3px solid #3b82f6;">
                            <div
                                style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 6px;">
                                📝 Toplantı Gündem Maddeleri:
                            </div>
                            <div class="meeting-rich-content">
                                {!! $m->agenda !!}
                            </div>
                        </div>
                    @endif

                    <!-- TOPLANTI TUTANAĞI & NOTLARI -->
                    @if($m->minutes)
                        <div
                            style="font-size: 13px; color: #334155; margin-bottom: 16px; line-height: 1.6; background: #f0fdf4; padding: 12px 14px; border-radius: 6px; border-left: 3px solid #16a34a;">
                            <div
                                style="font-size: 11px; font-weight: 700; color: #166534; text-transform: uppercase; margin-bottom: 6px; display: flex; align-items: center; gap: 5px;">
                                <span>📝</span> Toplantı Görüşme Tutanağı & Saha Notları:
                            </div>
                            <div style="font-size: 12.5px; color: #1e293b; line-height: 1.6; max-height: 140px; overflow-y: auto;">
                                {!! $m->minutes !!}
                            </div>
                        </div>
                    @endif

                    <!-- KARARDAN GÖREVE DÖNÜŞTÜRME ALANI (SAP EKİP MANTIĞI) -->
                    @if(!auth()->user()->isYonetim())
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px;">
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 6px;">
                                <div
                                    style="font-size: 12px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                                    <span style="color: #f59e0b;">⚡</span>
                                    <span>Toplantı Kararını Doğrudan Proje Görevine Dönüştür (Kanban / Gantt'a Ekle):</span>
                                </div>
                                <span style="font-size: 11px; color: #64748b;">
                                    💡 SAP Ekip Mantığı: Görev tüm proje ekibine veya belirli kişiye atanabilir
                                </span>
                            </div>

                            <form action="{{ route('meetings.convert_task', $m->id) }}" method="POST"
                                style="display: grid; grid-template-columns: 2fr 1.3fr 1.1fr auto; gap: 10px; align-items: end;">
                                @csrf
                                <div style="display: flex; flex-direction: column;">
                                    <label style="font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 4px;">Karar /
                                        Aksiyon Maddesi *</label>
                                    <input type="text" name="decision_text" class="form-control"
                                        placeholder="Örn: Zemin sertleştirme test raporu hazırlanacak..." required
                                        style="font-size: 13px;">
                                </div>

                                <div style="display: flex; flex-direction: column;">
                                    <label style="font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 4px;">Sorumlu
                                        (SAP Ekip / Kişi) *</label>
                                    <select name="assigned_target" class="form-control" required style="font-size: 13px;">
                                        <option value="team" selected
                                            style="font-weight: 700; color: #1e3a8a; background: #eff6ff;">
                                            👥 Tüm Proje Ekibi (SAP Modeli / Ortak Görev)
                                        </option>
                                        <optgroup label="Tekil Kişi Sorumlu Ata">
                                            @foreach($initialUsers as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}
                                                    ({{ $u->unvan ?? ($u->title ?? 'Ekip Üyesi') }})</option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                </div>

                                <div style="display: flex; flex-direction: column;">
                                    <label style="font-size: 11px; font-weight: 700; color: #b45309; margin-bottom: 4px;">
                                        📅 Termin / Bitiş Tarihi *
                                    </label>
                                    <input type="date" name="due_date" class="form-control" required style="font-size: 13px;"
                                        value="{{ now()->addDays(7)->format('Y-m-d') }}">
                                </div>

                                <div>
                                    <button type="submit" class="btn btn-primary"
                                        style="white-space: nowrap; height: 38px; display: inline-flex; align-items: center; gap: 6px; font-weight: 600; font-size: 13px; padding: 0 16px;">
                                        <span>➕</span> Göreve Dönüştür
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                    <!-- ÖNCEDEN ALINMIŞ KARARLAR LİSTESİ -->
                    @if($m->actions->count() > 0)
                        <div style="margin-top: 14px; border-top: 1px solid #f1f5f9; padding-top: 12px;">
                            <div
                                style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">
                                Bu Toplantıda Alınan ve Göreve Aktarılan Kararlar:
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                @foreach($m->actions as $act)
                                    <div
                                        style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; padding: 6px 12px; border-radius: 6px; font-size: 12px; border: 1px solid #f1f5f9;">
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span style="color: #10b981; font-weight: 700;">✓</span>
                                            <span style="color: #1e293b; font-weight: 600;">{{ $act->decision_text }}</span>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <span
                                                style="color: #64748b; background: #ffffff; padding: 2px 8px; border-radius: 4px; border: 1px solid #e2e8f0; font-size: 11px;">
                                                👤 {{ $act->assignee?->name ?? '👥 Tüm Proje Ekibi' }}
                                            </span>
                                            <span style="color: #b45309; font-weight: 600; font-size: 11px;">
                                                📅 Termin:
                                                {{ $act->due_date ? \Carbon\Carbon::parse($act->due_date)->format('d.m.Y') : '-' }}
                                            </span>
                                            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 10px;">
                                                Kanban Görevi Açıldı
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
            @empty
                <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
                    <div style="font-size: 36px; margin-bottom: 10px;">📅</div>
                    <div style="font-size: 15px; font-weight: 600; color: var(--navy); margin-bottom: 4px;">Kayıtlı toplantı
                        bulunamadı</div>
                    <div style="font-size: 13px;">Yukarıdaki formdan yeni bir proje toplantısı planlayabilirsiniz.</div>
                </div>
            @endforelse
        </div>

        <!-- EN ÇOK SON 10 TOPLANTI SAYFALAMA ALANI -->
        @if($meetings->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
                {{ $meetings->links() }}
            </div>
        @endif
    </div>

    <!-- TOPLANTI ERTELEME MODALI -->
    <div class="postpone-modal-backdrop" id="postponeModalBackdrop">
        <div class="postpone-modal-card">
            <div
                style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%); color: #ffffff; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 20px;">⏱️</span>
                    <span style="font-weight: 700; font-size: 16px;">Toplantıyı Ertele</span>
                </div>
                <button type="button" onclick="closePostponeModal()"
                    style="background: none; border: none; color: #fef3c7; font-size: 20px; cursor: pointer;">✕</button>
            </div>
            <form id="postponeForm" method="POST" style="padding: 24px;">
                @csrf
                <div
                    style="margin-bottom: 16px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 12px 14px;">
                    <div style="font-size: 12px; color: #92400e; font-weight: 600;">Ertelenen Toplantı:</div>
                    <div id="postponeMeetingTitle"
                        style="font-weight: 700; font-size: 14px; color: #78350f; margin-top: 2px;"></div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label
                            style="font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">📅
                            Yeni Tarih & Saat *</label>
                        <input type="datetime-local" name="new_meeting_date" id="postponeNewDate" required
                            class="form-control" style="font-size: 13px;">
                    </div>
                    <div>
                        <label
                            style="font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">📍
                            Lokasyon / Salon</label>
                        <input type="text" name="location" id="postponeLocation" class="form-control"
                            placeholder="Toplantı Yeri" style="font-size: 13px;">
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">📝
                        Erteleme Gerekçesi / Notu *</label>
                    <textarea name="postpone_reason" required rows="3" class="form-control"
                        placeholder="Örn: Saha beton test raporlarının gecikmesi sebebiyle toplantı yarına ertelenmiştir..."
                        style="font-size: 13px; line-height: 1.5;"></textarea>
                </div>

                <!-- BİLDİRİM MATRİSİ KANALLARI -->
                <div
                    style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; margin-bottom: 20px;">
                    <div style="font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">
                        📢 Katılımcı Bilgilendirmesi (Bildirim Matrisi):
                    </div>
                    <div style="display: flex; gap: 20px;">
                        <label
                            style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; cursor: pointer; color: #334155;">
                            <input type="checkbox" name="send_bell" value="1" checked> 🔔 Zil Bildirimi Gönder
                        </label>
                        <label
                            style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; cursor: pointer; color: #334155;">
                            <input type="checkbox" name="send_mail" value="1" checked> ✉️ E-Posta Bildirimi Gönder
                        </label>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closePostponeModal()" class="btn btn-outline"
                        style="padding: 8px 16px; font-size: 13px; border: 1px solid #cbd5e1; background: #ffffff;">Vazgeç</button>
                    <button type="submit" class="btn btn-primary"
                        style="padding: 8px 18px; font-size: 13px; background: #d97706; border-color: #b45309; font-weight: 700;">
                        <span>⏱️</span> Toplantıyı Ertele ve Bildir
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="{{ asset('vendor/quill/quill.js') }}"></script>
    <script>
        // 1. Gündem Alanı Quill Editörü
        let quillAgenda = null;
        document.addEventListener('DOMContentLoaded', function () {
            const agendaContainer = document.getElementById('quillAgendaEditor');
            if (agendaContainer && typeof Quill !== 'undefined') {
                quillAgenda = new Quill(agendaContainer, {
                    theme: 'snow',
                    placeholder: '1. Temel beton kırım test sonuçlarının değerlendirilmesi\n2. Vinç kurulum sahası zemin sertleştirme kontrolü\n3. İSG Yüksekte Çalışma İzinlerinin imza süreci...',
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline'],
                            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                            ['link', 'clean']
                        ]
                    }
                });
            }

            const createForm = document.getElementById('createMeetingForm');
            if (createForm) {
                createForm.addEventListener('submit', function (e) {
                    if (quillAgenda) {
                        const html = quillAgenda.root.innerHTML;
                        // Boşsa boş string yap
                        document.getElementById('agendaHiddenInput').value = (quillAgenda.getText().trim() === '') ? '' : html;
                    }
                });
            }
        });

        // 2. İlgili Proje Arama Özellikli Dropdown Menüsü
        function toggleProjectDropdown() {
            const dropdown = document.getElementById('projectSearchDropdown');
            const trigger = document.getElementById('projectSelectTrigger');
            if (!dropdown) return;
            const isVisible = dropdown.style.display === 'block';

            if (isVisible) {
                dropdown.style.display = 'none';
                trigger.classList.remove('active');
            } else {
                dropdown.style.display = 'block';
                trigger.classList.add('active');
                const searchInput = document.getElementById('projectSearchInput');
                if (searchInput) {
                    searchInput.value = '';
                    filterProjectDropdown('');
                    setTimeout(() => searchInput.focus(), 50);
                }
            }
        }

        function selectProjectItem(id, code, title) {
            document.getElementById('meetingProjectSelect').value = id;

            // Tetikleyici kutusundaki metni güncelle
            const textContainer = document.getElementById('projectSelectText');
            if (textContainer) {
                textContainer.innerHTML = `
                        <span style="font-weight: 700; font-size: 11.5px; color: #1d4ed8; background: #eff6ff; padding: 1px 6px; border-radius: 4px; border: 1px solid #bfdbfe;">${escapeHtml(code)}</span>
                        <span style="color: #0f172a; font-weight: 600;">${escapeHtml(title)}</span>
                    `;
            }

            // Menü listesinde seçili satırı işaretle
            document.querySelectorAll('#projectSearchResultsList .project-search-item').forEach(item => {
                const isCurrent = parseInt(item.getAttribute('data-project-id')) === parseInt(id);
                item.classList.toggle('selected', isCurrent);
                const check = item.querySelector('.project-item-check');
                if (check) check.style.display = isCurrent ? 'inline' : 'none';
            });

            // Dropdown'ı kapat
            const dropdown = document.getElementById('projectSearchDropdown');
            if (dropdown) dropdown.style.display = 'none';
            const trigger = document.getElementById('projectSelectTrigger');
            if (trigger) trigger.classList.remove('active');

            // Katılımcı havuzunu bu projeye göre güncelle
            handleProjectChange(id);
        }

        function filterProjectDropdown(query) {
            const q = (query || '').toLowerCase().trim();
            const items = document.querySelectorAll('#projectSearchResultsList .project-search-item');
            let found = 0;

            items.forEach(item => {
                const meta = item.getAttribute('data-search') || '';
                if (q === '' || meta.includes(q)) {
                    item.style.display = 'flex';
                    found++;
                } else {
                    item.style.display = 'none';
                }
            });

            let noResultEl = document.getElementById('noProjectResultMsg');
            if (found === 0) {
                if (!noResultEl) {
                    noResultEl = document.createElement('div');
                    noResultEl.id = 'noProjectResultMsg';
                    noResultEl.style.cssText = 'padding: 16px; text-align: center; color: #64748b; font-size: 12px;';
                    noResultEl.innerText = 'Eşleşen proje bulunamadı.';
                    document.getElementById('projectSearchResultsList').appendChild(noResultEl);
                }
                noResultEl.style.display = 'block';
            } else if (noResultEl) {
                noResultEl.style.display = 'none';
            }
        }

        // 3. Katılımcı Arama & Çoklu Seçim Mekanizması (Eklenenler Listeden Silinir / Çıkarılınca Geri Döner)
        const selectedParticipants = new Map(); // id -> {name, title}
        let searchDebounceTimer = null;

        function addParticipant(id, name, title) {
            id = parseInt(id);
            if (selectedParticipants.has(id)) {
                return;
            }
            selectedParticipants.set(id, { name, title });
            renderSelectedParticipants();

            // Seçilen kişiyi aşağıdaki listeden anında kaldır / gizle
            hideParticipantFromList(id);

            // Arama kutusunu temizle
            const searchInput = document.getElementById('participantSearchInput');
            if (searchInput) {
                searchInput.value = '';
            }

            // Arama kutusunun dropdown'ını açık tut ki kullanıcı ardışık seçim yapabilsin
            showSearchDropdown();
        }

        function removeParticipant(id) {
            id = parseInt(id);
            selectedParticipants.delete(id);
            renderSelectedParticipants();

            // Silinen kişiyi aşağıdaki listeye geri kazandır / görünür yap
            showParticipantInList(id);
        }

        function hideParticipantFromList(id) {
            const items = document.querySelectorAll(`.user-search-item[data-user-id="${id}"]`);
            items.forEach(el => {
                el.style.display = 'none';
            });
        }

        function showParticipantInList(id) {
            const items = document.querySelectorAll(`.user-search-item[data-user-id="${id}"]`);
            items.forEach(el => {
                el.style.display = 'flex';
            });
        }

        function renderSelectedParticipants() {
            const container = document.getElementById('selectedParticipantsContainer');
            const hiddenInputs = document.getElementById('hiddenParticipantsInputs');

            container.innerHTML = '';
            hiddenInputs.innerHTML = '';

            if (selectedParticipants.size === 0) {
                container.innerHTML = '<span id="noParticipantPlaceholder" style="font-size: 12px; color: #94a3b8; align-self: center; padding-left: 4px;">Henüz katılımcı seçilmedi. Aşağıdan arayarak ekleyebilirsiniz.</span>';
                return;
            }

            selectedParticipants.forEach((val, id) => {
                const chip = document.createElement('div');
                chip.className = 'participant-chip';
                chip.innerHTML = `
                        <span>👤 ${escapeHtml(val.name)}</span>
                        <button type="button" class="remove-chip" onclick="removeParticipant(${id})" title="Kaldır">✕</button>
                    `;
                container.appendChild(chip);

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'participants[]';
                input.value = id;
                hiddenInputs.appendChild(input);
            });
        }

        function handleParticipantSearch(query) {
            clearTimeout(searchDebounceTimer);
            showSearchDropdown();

            searchDebounceTimer = setTimeout(() => {
                const projectId = document.getElementById('meetingProjectSelect') ? document.getElementById('meetingProjectSelect').value : '';
                const url = "{{ route('meetings.search_users') }}?q=" + encodeURIComponent(query) + "&project_id=" + encodeURIComponent(projectId);

                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        const resultsContainer = document.getElementById('participantSearchResults');
                        resultsContainer.innerHTML = '';

                        if (!data.users || data.users.length === 0) {
                            resultsContainer.innerHTML = '<div style="padding: 12px; font-size: 12px; color: #64748b; text-align: center;">Kullanıcı bulunamadı.</div>';
                            return;
                        }

                        let renderedCount = 0;
                        data.users.forEach(u => {
                            const uid = parseInt(u.id);
                            // KULLANICI TALEBİ: ÜST KUTUYA EKLENEN KİŞİLER LİSTEDE GÖRÜNMEZ!
                            if (selectedParticipants.has(uid)) {
                                return;
                            }

                            renderedCount++;
                            const item = document.createElement('div');
                            item.className = 'user-search-item';
                            item.setAttribute('data-user-id', uid);
                            item.onclick = () => addParticipant(uid, u.name, u.title);
                            item.innerHTML = `
                                    <div>
                                        <div style="font-weight: 600; font-size: 12.5px; color: #0f172a;">
                                            ${escapeHtml(u.name)}
                                            ${u.is_project_member ? '<span style="font-size: 10px; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 1px 5px; border-radius: 4px; margin-left: 4px;">Proje Ekibi</span>' : ''}
                                        </div>
                                        <div style="font-size: 11px; color: #64748b;">${escapeHtml(u.title || 'Personel')} • ${escapeHtml(u.department || 'Merkez')}</div>
                                    </div>
                                    <span style="font-size: 11px; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 2px 8px; border-radius: 4px;">+ Ekle</span>
                                `;
                            resultsContainer.appendChild(item);
                        });

                        if (renderedCount === 0) {
                            resultsContainer.innerHTML = '<div style="padding: 12px; font-size: 12px; color: #64748b; text-align: center;">Tüm eşleşen kullanıcılar zaten üst kutuya eklendi.</div>';
                        }
                    })
                    .catch(err => {
                        console.error('Kullanıcı arama hatası:', err);
                    });
            }, 200);
        }

        function showSearchDropdown() {
            const dropdown = document.getElementById('participantSearchDropdown');
            if (!dropdown) return;
            dropdown.style.display = 'block';

            // Mevcut listede seçili olanları gizle, seçili olmayanları göster
            document.querySelectorAll('#participantSearchResults .user-search-item').forEach(el => {
                const uid = parseInt(el.getAttribute('data-user-id'));
                if (selectedParticipants.has(uid)) {
                    el.style.display = 'none';
                } else {
                    el.style.display = 'flex';
                }
            });
        }

        function closeSearchDropdown() {
            const dropdown = document.getElementById('participantSearchDropdown');
            if (dropdown) dropdown.style.display = 'none';
        }

        function handleProjectChange(projectId) {
            if (projectId) {
                // Proje değiştiğinde arama listesini tetikle
                handleParticipantSearch('');
            }
        }

        // Tıklama Olayları (Dışarı tıklandığında açılır kutuları kapatma)
        document.addEventListener('click', function (e) {
            // Proje Dropdown'ı dışına tıklandıysa kapat
            const projectContainer = document.getElementById('projectSelectContainer');
            if (projectContainer && !projectContainer.contains(e.target)) {
                const pDropdown = document.getElementById('projectSearchDropdown');
                if (pDropdown) pDropdown.style.display = 'none';
                const pTrigger = document.getElementById('projectSelectTrigger');
                if (pTrigger) pTrigger.classList.remove('active');
            }

            // Katılımcı Dropdown'ı dışına tıklandıysa kapat
            const participantGroup = document.getElementById('participantSearchInput')?.closest('.form-group');
            if (participantGroup && !participantGroup.contains(e.target)) {
                closeSearchDropdown();
            }
        });

        // 4. Toplantı Erteleme Modalı
        function openPostponeModal(meetingId, title, currentDate, location) {
            document.getElementById('postponeMeetingTitle').innerText = title;
            document.getElementById('postponeNewDate').value = currentDate;
            document.getElementById('postponeLocation').value = location || '';
            document.getElementById('postponeForm').action = "{{ url('/toplantilar') }}/" + meetingId + "/postpone";
            document.getElementById('postponeModalBackdrop').style.display = 'flex';
        }

        function closePostponeModal() {
            document.getElementById('postponeModalBackdrop').style.display = 'none';
        }

        document.getElementById('postponeModalBackdrop').addEventListener('click', function (e) {
            if (e.target === this) {
                closePostponeModal();
            }
        });

        function escapeHtml(text) {
            if (!text) return '';
            return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }
    </script>
@endsection