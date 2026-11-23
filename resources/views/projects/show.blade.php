@extends('layouts.app')

@section('title', $project->title)
@section('page_title', $project->title)
@section('page_subtitle', $project->code . ' • ' . ucfirst($project->category) . ' Projesi')

@section('content')
    @if(session('new_external_observers'))
        <div class="pastel-blue-helper-box mb-4" style="background: #eff6ff; border-color: #93c5fd; padding: 16px 20px;">
            <span class="helper-box-icon" style="font-size: 24px;">🔑</span>
            <div class="helper-box-content">
                <strong style="font-size: 14px; color: #1e40af; display: block; margin-bottom: 6px;">
                    Harici Gözlemciler İçin Geçici Giriş Bilgileri Oluşturuldu
                </strong>
                <div style="font-size: 12.5px; color: #1e3a8a; margin-bottom: 10px;">
                    Aşağıdaki harici kullanıcılara özel tek kullanımlık geçici şifreler üretildi. Bu kişiler yalnızca bu projeye
                    erişebilir:
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                    @foreach(session('new_external_observers') as $cred)
                        <div
                            style="background: #ffffff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 8px 14px; font-size: 12px;">
                            <div><strong>{{ $cred['name'] }}</strong> ({{ $cred['email'] }})</div>
                            <div
                                style="margin-top: 3px; font-family: monospace; font-size: 12.5px; color: #0284c7; font-weight: 700;">
                                Şifre: <code>{{ $cred['password'] }}</code>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if(session('new_observer_password'))
        @php $newObs = session('new_observer_password'); @endphp
        <div class="pastel-blue-helper-box mb-4" style="background: #eff6ff; border-color: #93c5fd; padding: 16px 20px;">
            <span class="helper-box-icon" style="font-size: 24px;">🔑</span>
            <div class="helper-box-content">
                <strong style="font-size: 14px; color: #1e40af; display: block; margin-bottom: 4px;">
                    Yeni Harici Gözlemci Giriş Bilgisi
                </strong>
                <div style="font-size: 12.5px; color: #1e3a8a;">
                    <strong>{{ $newObs['name'] }}</strong> ({{ $newObs['email'] }}) için sistem geçici şifresi: <code
                        style="background: #dbeafe; padding: 2px 6px; border-radius: 4px; font-size: 13px; font-weight: 700; color: #1d4ed8;">{{ $newObs['password'] }}</code>
                </div>
            </div>
        </div>
    @endif
    @if(isset($isObserver) && $isObserver)
        <div
            style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px 18px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 22px;">👁️</span>
                <div>
                    <strong style="color: #1e40af; font-size: 13.5px;">Gözlemci (Salt Okunur) Modundasınız</strong>
                    <div style="color: #3b82f6; font-size: 12px;">Bu projeyi izleyici olarak takip etmektesiniz. Aşamaları,
                        Gantt şemasını, bütçe kalemlerini ve dokümanları salt okunur olarak inceleyebilirsiniz.</div>
                </div>
            </div>
            <span class="badge"
                style="background: #dbeafe; color: #1d4ed8; font-weight: 700; padding: 5px 12px; border-radius: 6px;">
                🔒 Read-Only İzleyici
            </span>
        </div>
    @endif

    @php
        $activeProjectDelegations = $activeProjectDelegations ?? $project->getActiveStakeholderDelegations();
    @endphp

    <!-- =========================================================================
             KURUMSAL VEKALET & TEMSİL BİLGİLENDİRME BANTLARI (AKTİF & GEÇMİŞ)
             ========================================================================= -->
    <style>
        @keyframes delegationPulseSlow {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.18);
                border-color: #86efac;
            }

            50% {
                box-shadow: 0 0 14px 3px rgba(16, 185, 129, 0.40);
                border-color: #10b981;
            }
        }

        @keyframes delegationDotPulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.28;
                transform: scale(0.75);
            }
        }

        .active-delegation-pulse {
            animation: delegationPulseSlow 2.8s infinite ease-in-out;
        }

        .delegation-dot-blinking {
            animation: delegationDotPulse 1.6s infinite ease-in-out;
        }
    </style>

    @if(isset($activeProjectDelegations) && $activeProjectDelegations->isNotEmpty())
        <!-- 1. CANLI & AKTİF VEKALET BANDI -->
        <div id="projectDelegationBannerWrap" class="mb-3">
            <div id="projectDelegationBanner" class="active-delegation-pulse"
                style="background: linear-gradient(90deg, #f0fdf4 0%, #ecfdf5 100%); border: 1.5px solid #86efac; border-left: 5px solid #10b981; border-radius: 9px; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between; gap: 10px; font-size: 12.5px; color: #0f172a; transition: all 0.2s ease;">
                <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0; flex-wrap: wrap;">
                    <span
                        style="display: inline-flex; align-items: center; gap: 5px; background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 12px; flex-shrink: 0;">
                        <span class="delegation-dot-blinking"
                            style="width: 7px; height: 7px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                        🛡️ Canlı Vekalet
                    </span>

                    <div style="display: flex; flex-direction: column; gap: 5px; flex: 1; min-width: 260px;">
                        @foreach($activeProjectDelegations as $del)
                            @php
                                $startDate = $del->starts_at->format('d.m.Y H:i');
                                $endDate = $del->ends_at->format('d.m.Y H:i');
                                $delegatorName = $del->delegator?->name ?? 'Bilinmeyen';
                                $delegateeName = $del->delegatee?->name ?? 'Bilinmeyen Vekil';
                                $reasonData = $del->getFormattedReason();
                                $badge = $del->getScopeSummaryBadge();
                                $durationText = $del->getDurationText();
                                $delActions = $del->getAuditLogs()->where('project_id', $project->id);

                                $projectRole = 'Ekip Üyesi';
                                if ((int) $project->manager_id === (int) $del->delegator_id) {
                                    $projectRole = 'Proje Lideri';
                                } elseif ((int) $project->sponsor_id === (int) $del->delegator_id) {
                                    $projectRole = 'Proje Sponsoru';
                                }
                            @endphp
                            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap; line-height: 1.35;">
                                <span style="color: #475569; font-size: 11.5px;">📅 <strong>{{ $startDate }}</strong> -
                                    <strong>{{ $endDate }}</strong></span>
                                <span
                                    style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; padding: 1px 7px; border-radius: 5px; font-size: 11px; font-weight: 700;">
                                    ⏳ {{ $durationText }}
                                </span>
                                @if(!empty($reasonData['category']) && $reasonData['category'] !== 'Belirtilmedi')
                                    <span
                                        style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 1px 7px; border-radius: 4px; font-size: 11px; font-weight: 700;">
                                        (Sebep: {{ $reasonData['category'] }})
                                    </span>
                                @endif
                                <span>
                                    <strong>{{ $delegatorName }}</strong> <span
                                        style="color: #64748b; font-size: 11px;">({{ $projectRole }})</span> adına
                                    <strong>{{ $delegateeName }}</strong> vekalet etmektedir.
                                </span>
                                <button type="button" onclick="openDelegationScopeModal({{ $del->id }})" class="btn btn-xs"
                                    style="background: {{ $badge['bg'] }}; color: {{ $badge['color'] }}; border: 1px solid {{ $badge['border'] }}; font-weight: 700; font-size: 11px; padding: 1.5px 8px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: all 0.15s; margin-left: 3px;"
                                    title="Vekaletin hangi yetkileri kapsadığını incelemek için tıklayın">
                                    <span>{{ $badge['label'] }}</span>
                                    <span style="font-size: 10px; opacity: 0.85;">(Kapsamı Gör ↗)</span>
                                </button>
                                <!-- Akordeon Butonu: Yapılan İşlemler -->
                                <button type="button" onclick="toggleProjectDelegationActions({{ $del->id }})"
                                    class="btn btn-xs btn-outline-secondary"
                                    style="font-weight: 700; font-size: 11px; padding: 1.5px 7px; border-radius: 6px; cursor: pointer; margin-left: 2px;">
                                    <span id="projActionToggleIcon_{{ $del->id }}">▶</span> Yapılan İşlemler
                                    ({{ $delActions->count() }})
                                </button>
                            </div>

                            <!-- VARSAYILAN KAPALI AKORDEON: BU PROJEDE VEKİLİN YAPTIĞI İŞLEMLER -->
                            <div id="projActionPanel_{{ $del->id }}"
                                style="display: none; width: 100%; margin-top: 6px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 12px;">
                                <div style="font-size: 11px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                                    🔍 <strong>{{ $delegateeName }}</strong> tarafından bu projede vekaleten yapılan işlemler:
                                </div>
                                @if($delActions->isEmpty())
                                    <div style="font-size: 11px; color: #64748b; font-style: italic;">Bu proje üzerinde henüz vekaleten
                                        kayıtlı bir işlem bulunmamaktadır.</div>
                                @else
                                    <div style="max-height: 160px; overflow-y: auto;">
                                        <table style="width: 100%; font-size: 11px; border-collapse: collapse;">
                                            <thead>
                                                <tr style="border-bottom: 1px solid #e2e8f0; color: #64748b; text-align: left;">
                                                    <th style="padding: 3px 5px;">Tarih</th>
                                                    <th style="padding: 3px 5px;">İşlem</th>
                                                    <th style="padding: 3px 5px;">Açıklama</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($delActions as $act)
                                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                                        <td style="padding: 3px 5px; color: #64748b; white-space: nowrap;">
                                                            {{ $act->created_at->format('d.m.Y H:i') }}</td>
                                                        <td style="padding: 3px 5px; font-weight: 600;"><span class="badge"
                                                                style="background: #e2e8f0; color: #334155; font-size: 9.5px;">{{ $act->action }}</span>
                                                        </td>
                                                        <td style="padding: 3px 5px; color: #334155;">
                                                            {!! strip_tags($act->description, '<strong><b>') !!}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                    <span style="font-size: 11px; color: #059669; font-weight: 600; cursor: help;"
                        title="Bu proje kapsamında vekil tarafından alınan onaylar Rule 16 gereğince çift yönlü olarak loglanmaktadır.">
                        ℹ️ Rule 16
                    </span>
                    <button type="button" onclick="toggleDelegationBanner(false)" class="btn btn-xs"
                        style="background: transparent; border: none; color: #64748b; font-size: 15px; line-height: 1; padding: 3px 6px; cursor: pointer; border-radius: 4px; transition: all 0.15s;"
                        title="Bu uyarıyı gizle">
                        ✕
                    </button>
                </div>
            </div>

            <!-- Gizlendiğinde Görünen Minik Çip -->
            <div id="projectDelegationMiniChip"
                style="display: none; align-items: center; justify-content: flex-end; margin-bottom: 6px;">
                <button type="button" onclick="toggleDelegationBanner(true)" class="btn btn-xs"
                    style="background: #f0fdf4; border: 1px solid #86efac; color: #166534; font-size: 11px; font-weight: 700; padding: 2px 10px; border-radius: 12px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                    <span class="delegation-dot-blinking"
                        style="width: 6px; height: 6px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                    <span>📢 Aktif Vekalet Bilgisini Göster</span>
                    <span style="color: #059669; font-size: 10px;">▼</span>
                </button>
            </div>
        </div>
    @endif

    @if(isset($pastProjectDelegations) && $pastProjectDelegations->isNotEmpty())
        <!-- 2. GEÇMİŞ VEKALETLER & İŞLEM GEÇMİŞİ PANELİ (VARSAYILAN KAPALI / AKORDEON) -->
        <div id="projectPastDelegationsWrap" class="mb-3">
            <div
                style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 10px 14px; box-shadow: 0 1px 4px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 15px;">📜</span>
                        <strong style="font-size: 12.5px; color: #334155;">Bu Projedeki Geçmiş Vekalet Kayıtları & Denetim
                            İzi</strong>
                        <span
                            style="font-size: 11px; font-weight: 700; color: #64748b; background: #f1f5f9; padding: 1px 7px; border-radius: 10px;">
                            {{ $pastProjectDelegations->count() }} Kayıt
                        </span>
                    </div>
                    <button type="button" onclick="togglePastDelegationsPanel()" id="btnTogglePastDelegations"
                        class="btn btn-xs btn-outline-secondary"
                        style="font-weight: 700; font-size: 11px; padding: 2px 8px; border-radius: 6px; cursor: pointer;">
                        <span id="lblPastDelegationsToggle">Geçmişi İncele ▼</span>
                    </button>
                </div>

                <!-- Açılır Geçmiş Listesi (Varsayılan Kapalı) -->
                <div id="panelPastDelegationsList"
                    style="display: none; margin-top: 10px; border-top: 1px solid #f1f5f9; padding-top: 10px;">
                    @foreach($pastProjectDelegations as $pdel)
                        @php
                            $delegatorName = $pdel->delegator?->name ?? 'Bilinmeyen';
                            $delegateeName = $pdel->delegatee?->name ?? 'Bilinmeyen Vekil';
                            $durationText = $pdel->getDurationText();
                            $startDate = $pdel->starts_at->format('d.m.Y H:i');
                            $endDate = $pdel->cancelled_at ? $pdel->cancelled_at->format('d.m.Y H:i') : $pdel->ends_at->format('d.m.Y H:i');
                            $reason = $pdel->getFormattedReason();
                            $pActions = $pdel->getAuditLogs()->where('project_id', $project->id);
                        @endphp
                        <div
                            style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px; margin-bottom: 8px;">
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                                <div>
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                        <strong style="font-size: 12.5px; color: #0f172a;">{{ $delegatorName }}</strong>
                                        <span style="color: #64748b; font-size: 11px;">adına</span>
                                        <strong style="font-size: 12.5px; color: #0f172a;">{{ $delegateeName }}</strong>
                                        <span class="badge"
                                            style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; font-size: 10.5px;">
                                            ⚪ Sona Erdi / İptal
                                        </span>
                                        <span
                                            style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-size: 11px; font-weight: 700; padding: 1px 7px; border-radius: 4px;">
                                            ⏳ {{ $durationText }}
                                        </span>
                                    </div>
                                    <div style="font-size: 11.5px; color: #475569; margin-top: 3px;">
                                        📅 <strong>{{ $startDate }}</strong> — <strong>{{ $endDate }}</strong>
                                        @if($pdel->cancel_reason)
                                            <span style="color: #dc2626; font-style: italic; margin-left: 6px;">(İptal Gerekçesi:
                                                {{ $pdel->cancel_reason }})</span>
                                        @endif
                                        @if($reason['has_note'])
                                            <span style="color: #64748b; font-style: italic; margin-left: 6px;">💬
                                                "{{ $reason['note'] }}"</span>
                                        @endif
                                    </div>
                                </div>

                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <button type="button" onclick="openDelegationScopeModal({{ $pdel->id }})"
                                        class="btn btn-xs btn-outline-primary"
                                        style="font-weight: 700; font-size: 11px; padding: 2px 7px; border-radius: 6px; cursor: pointer;">
                                        🎯 Yetki Kapsamı
                                    </button>
                                    <button type="button" onclick="togglePastDelegationActions({{ $pdel->id }})"
                                        class="btn btn-xs btn-outline-secondary"
                                        style="font-weight: 700; font-size: 11px; padding: 2px 7px; border-radius: 6px; cursor: pointer;">
                                        <span id="pastActionToggleIcon_{{ $pdel->id }}">▶</span> Yapılan İşlemler
                                        ({{ $pActions->count() }})
                                    </button>
                                </div>
                            </div>

                            <!-- VARSAYILAN KAPALI AKORDEON: BU GEÇMİŞ VEKALETTE YAPILAN İŞLEMLER -->
                            <div id="pastActionPanel_{{ $pdel->id }}"
                                style="display: none; margin-top: 8px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 10px;">
                                <div style="font-size: 11px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                                    🔍 Bu vekalet süresince vekil tarafından yapılan işlemler ({{ $pActions->count() }} Kayıt):
                                </div>
                                @if($pActions->isEmpty())
                                    <div style="font-size: 11px; color: #64748b; font-style: italic;">Bu vekalet süresince vekil
                                        tarafından bu projede kayıtlı bir işlem gerçekleştirilmemiştir.</div>
                                @else
                                    <div style="max-height: 150px; overflow-y: auto;">
                                        <table style="width: 100%; font-size: 11px; border-collapse: collapse;">
                                            <thead>
                                                <tr style="border-bottom: 1px solid #e2e8f0; color: #64748b; text-align: left;">
                                                    <th style="padding: 2px 4px;">Tarih</th>
                                                    <th style="padding: 2px 4px;">İşlem</th>
                                                    <th style="padding: 2px 4px;">Açıklama</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($pActions as $act)
                                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                                        <td style="padding: 2px 4px; color: #64748b; white-space: nowrap;">
                                                            {{ $act->created_at->format('d.m.Y H:i') }}</td>
                                                        <td style="padding: 2px 4px; font-weight: 600;"><span class="badge"
                                                                style="background: #e2e8f0; color: #334155; font-size: 9.5px;">{{ $act->action }}</span>
                                                        </td>
                                                        <td style="padding: 2px 4px; color: #334155;">
                                                            {!! strip_tags($act->description, '<strong><b>') !!}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <script>
        function toggleDelegationBanner(show) {
            const banner = document.getElementById('projectDelegationBanner');
            const miniChip = document.getElementById('projectDelegationMiniChip');
            const projectId = '{{ $project->id }}';
            if (!banner || !miniChip) return;

            if (show) {
                banner.style.display = 'flex';
                miniChip.style.display = 'none';
                try { localStorage.removeItem('kpm_hide_delegation_banner_' + projectId); } catch (e) { }
            } else {
                banner.style.display = 'none';
                miniChip.style.display = 'flex';
                try { localStorage.setItem('kpm_hide_delegation_banner_' + projectId, '1'); } catch (e) { }
            }
        }

        function toggleProjectDelegationActions(delId) {
            const panel = document.getElementById('projActionPanel_' + delId);
            const icon = document.getElementById('projActionToggleIcon_' + delId);
            if (!panel) return;

            if (panel.style.display === 'none' || !panel.style.display) {
                panel.style.display = 'block';
                if (icon) icon.textContent = '▼';
            } else {
                panel.style.display = 'none';
                if (icon) icon.textContent = '▶';
            }
        }

        function togglePastDelegationsPanel() {
            const panel = document.getElementById('panelPastDelegationsList');
            const lbl = document.getElementById('lblPastDelegationsToggle');
            if (!panel || !lbl) return;

            if (panel.style.display === 'none' || !panel.style.display) {
                panel.style.display = 'block';
                lbl.textContent = 'Gizle ▲';
            } else {
                panel.style.display = 'none';
                lbl.textContent = 'Geçmişi İncele ▼';
            }
        }

        function togglePastDelegationActions(delId) {
            const panel = document.getElementById('pastActionPanel_' + delId);
            const icon = document.getElementById('pastActionToggleIcon_' + delId);
            if (!panel) return;

            if (panel.style.display === 'none' || !panel.style.display) {
                panel.style.display = 'block';
                if (icon) icon.textContent = '▼';
            } else {
                panel.style.display = 'none';
                if (icon) icon.textContent = '▶';
            }
        }

        (function () {
            try {
                const projectId = '{{ $project->id }}';
                if (localStorage.getItem('kpm_hide_delegation_banner_' + projectId) === '1') {
                    const banner = document.getElementById('projectDelegationBanner');
                    const miniChip = document.getElementById('projectDelegationMiniChip');
                    if (banner && miniChip) {
                        banner.style.display = 'none';
                        miniChip.style.display = 'flex';
                    }
                }
            } catch (e) { }
        })();
    </script>

    @if($project->isDraft())
        <!-- =========================================================================
                     TASLAK PROJE İNCELEME, ÖNERİ & PAYLAŞIM BANNERI
                     ========================================================================= -->
        <div class="card mb-4"
            style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1.5px solid #fde68a; border-radius: 12px; box-shadow: 0 4px 16px rgba(245, 158, 11, 0.12); overflow: hidden; margin-bottom: 20px;">
            <div class="card-body" style="padding: 18px 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div
                            style="width: 44px; height: 44px; border-radius: 10px; background: #fef08a; color: #b45309; display: flex; align-items: center; justify-content: center; font-size: 22px; box-shadow: 0 2px 8px rgba(180, 83, 9, 0.15);">
                            📝
                        </div>
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: #92400e;">BU PROJE TASLAK
                                    MODUNDADIR</h3>
                                <span class="badge"
                                    style="background: #fef08a; color: #854d0e; font-weight: 700; font-size: 11px;">Eksik Bilgi
                                    / Taslak</span>
                            </div>
                            <p style="margin: 3px 0 0; font-size: 12.5px; color: #78350f;">
                                Proje henüz resmi onay sürecine gönderilmemiştir. Taslağı paydaşlarla paylaşabilir, satır satır
                                gelen önerileri inceleyebilir ve eksikleri tamamlayıp yayınlayabilirsiniz.
                            </p>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        @if($canManage)
                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="openShareDraftModal()"
                                style="background: #ffffff; color: #b45309; border-color: #f59e0b; font-weight: 700; padding: 7px 14px; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                                <span>👥</span> Taslağı Paylaş
                            </button>
                        @endif

                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openViewSuggestionsModal()"
                            style="background: #ffffff; color: #475569; border-color: #cbd5e1; font-weight: 700; padding: 7px 14px; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                            <span>💡</span> Önerileri Gör
                            <span class="badge"
                                style="background: #0284c7; color: #ffffff; border-radius: 10px; padding: 2px 7px; font-size: 11px;">
                                {{ $project->draftSuggestions->count() }}
                            </span>
                        </button>

                        @if($project->draftShares->isNotEmpty())
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="openExternalSharesModal()"
                                style="background: #ffffff; color: #0284c7; border-color: #0284c7; font-weight: 700; padding: 7px 14px; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                                <span>🌐</span> Harici Paylaşımlar
                                <span class="badge"
                                    style="background: #0284c7; color: #ffffff; border-radius: 10px; padding: 2px 7px; font-size: 11px;">
                                    {{ $project->draftShares->where('status', 'active')->count() }} Aktif
                                </span>
                            </button>
                        @endif

                        @if($canManage)
                            <form action="{{ route('projects.publish_draft', $project->id) }}" method="POST" style="margin: 0;"
                                onsubmit="return confirm('Bu taslak projeyi yayınlamak ve resmi onay sürecini başlatmak istediğinize emin misiniz?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success"
                                    style="font-weight: 700; padding: 7px 16px; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);">
                                    <span>🚀</span> Projeyi Yayınla
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @php
        $activeApproval = $project->latestApprovalRequest;
    @endphp

    @if($activeApproval && $activeApproval->status === 'pending')
        @if($activeApproval->target_approver_type === 'committee' && $activeApproval->targetCommittee)
            @php
                $committee = $activeApproval->targetCommittee;
                $activeMembers = $committee->activeMembers()->with('user')->get();
                $totalWeight = (float) $activeMembers->sum('voting_weight') ?: 1.0;
                $votes = $activeApproval->votes()->with('user')->get();
                $approveWeight = (float) $votes->whereIn('vote', ['approve', 'conditional_approve'])->sum('weight');
                $rejectWeight = (float) $votes->where('vote', 'reject')->sum('weight');
                $myVote = $votes->firstWhere('user_id', auth()->id());
                $isCommitteeMember = $activeMembers->contains('user_id', auth()->id()) || auth()->user()->isSuperAdmin();
                $approvePct = round(($approveWeight / $totalWeight) * 100, 1);
                $rejectPct = round(($rejectWeight / $totalWeight) * 100, 1);
            @endphp
            <div class="card mb-4"
                style="background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%); border: 2px solid #93c5fd; border-radius: 14px; box-shadow: 0 4px 16px rgba(59, 130, 246, 0.12); overflow: hidden; margin-bottom: 24px;">
                <div class="card-body" style="padding: 20px 24px;">
                    <div
                        style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 16px;">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <div
                                style="width: 48px; height: 48px; border-radius: 12px; background: #dbeafe; color: #1e40af; display: flex; align-items: center; justify-content: center; font-size: 24px; box-shadow: 0 2px 8px rgba(30, 64, 175, 0.15);">
                                🏛️
                            </div>
                            <div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: #1e3a8a;">{{ $committee->name }}
                                        OYLAMASI DEVAM EDİYOR</h3>
                                    <span class="badge"
                                        style="background: #e0e7ff; color: #3730a3; font-weight: 700; font-size: 11px;">{{ $committee->voting_type_label }}</span>
                                </div>
                                <p style="margin: 4px 0 0; font-size: 13px; color: #3b82f6;">
                                    Bu proje planlanan <strong>{{ number_format($project->planned_budget, 2, ',', '.') }}
                                        {{ $project->currency ?? 'TRY' }}</strong> bütçesi ile kurul onayına sunulmuştur. Kurul
                                    üyeleri oylarını kullanarak karar yeter sayısını oluşturmaktadır.
                                </p>
                            </div>
                        </div>

                        <!-- Oylama Özeti Rozetleri -->
                        <div style="display: flex; gap: 12px; align-items: center;">
                            <div style="text-align: right;">
                                <div style="font-size: 11.5px; color: #64748b; font-weight: 600;">Kullanılan Oylar</div>
                                <div style="font-size: 14px; font-weight: 800; color: #1e293b;">{{ $votes->count() }} /
                                    {{ $activeMembers->count() }} Üye</div>
                            </div>
                            <div style="text-align: right; border-left: 1px solid #cbd5e1; padding-left: 12px;">
                                <div style="font-size: 11.5px; color: #16a34a; font-weight: 700;">Onay Ağırlığı: %{{ $approvePct }}
                                </div>
                                <div style="font-size: 11.5px; color: #dc2626; font-weight: 700;">Ret Ağırlığı: %{{ $rejectPct }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Canlı Oylama İlerleme Çubuğu -->
                    <div style="margin-bottom: 18px;">
                        <div style="height: 10px; background: #e2e8f0; border-radius: 6px; overflow: hidden; display: flex;">
                            <div style="width: {{ $approvePct }}%; background: #22c55e; transition: width 0.3s;"
                                title="Onay: %{{ $approvePct }}"></div>
                            <div style="width: {{ $rejectPct }}%; background: #ef4444; transition: width 0.3s;"
                                title="Ret: %{{ $rejectPct }}"></div>
                        </div>
                    </div>

                    <!-- Oy Kullanma Paneli (Üyeler için) -->
                    @if($isCommitteeMember)
                        <div
                            style="background: #ffffff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 16px; margin-bottom: 14px;">
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                                <span style="font-size: 13px; font-weight: 700; color: #1e40af;">
                                    🗳️ Kurul Üyesi Olarak Oyunuzu Kullanın:
                                </span>
                                @if($myVote)
                                    <span class="badge"
                                        style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-size: 12px; padding: 5px 10px;">
                                        Mevcut Oyunuz: <strong>{{ $myVote->vote_label }}</strong>
                                        ({{ $myVote->voted_at ? $myVote->voted_at->format('d.m.Y H:i') : '' }})
                                    </span>
                                @endif
                            </div>

                            <form action="{{ route('projects.committee_vote', $project->id) }}" method="POST">
                                @csrf
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-7">
                                        <input type="text" name="notes" class="form-control form-control-sm"
                                            placeholder="Oyunuzla ilgili gerekçe veya şerh notunuzu yazabilirsiniz (opsiyonel)..."
                                            value="{{ $myVote?->notes }}">
                                    </div>
                                    <div class="col-md-5 d-flex gap-2 justify-content-end">
                                        <button type="submit" name="vote" value="approve"
                                            class="btn btn-sm btn-success fw-bold px-3 d-inline-flex align-items-center gap-1">
                                            <span>✓</span> Onayla
                                        </button>
                                        <button type="submit" name="vote" value="conditional_approve"
                                            class="btn btn-sm btn-warning fw-bold px-2.5 d-inline-flex align-items-center gap-1"
                                            style="background: #f59e0b; color: #fff; border: none;">
                                            <span>⚠️</span> Şerhli Onay
                                        </button>
                                        <button type="submit" name="vote" value="reject"
                                            class="btn btn-sm btn-danger fw-bold px-3 d-inline-flex align-items-center gap-1">
                                            <span>✕</span> Reddet
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif

                    <!-- Kullanılan Oylar Listesi -->
                    @if($votes->isNotEmpty())
                        <div
                            style="background: rgba(255,255,255,0.7); border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px;">
                            <div
                                style="font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 6px;">
                                Kullanılan Oylar & Şerhler:</div>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                @foreach($votes as $v)
                                    <span
                                        style="display: inline-flex; align-items: center; gap: 6px; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 4px 10px; font-size: 12px;">
                                        <strong>{{ $v->user?->name }}:</strong>
                                        @if($v->vote === 'approve')
                                            <span style="color: #16a34a; font-weight: 700;">✓ Onay</span>
                                        @elseif($v->vote === 'conditional_approve')
                                            <span style="color: #d97706; font-weight: 700;">⚠️ Şerhli Onay</span>
                                        @elseif($v->vote === 'reject')
                                            <span style="color: #dc2626; font-weight: 700;">✕ Ret</span>
                                        @else
                                            <span style="color: #64748b;">Çekimser</span>
                                        @endif
                                        @if($v->notes)
                                            <span
                                                style="color: #64748b; font-style: italic; font-size: 11px;">("{{ Str::limit($v->notes, 30) }}")</span>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @elseif($activeApproval->target_approver_type !== 'committee')
            <!-- TEKİL ROL VEYA KİŞİ EŞİK ONAYI BANNERI -->
            <div class="card mb-4"
                style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 2px solid #fde68a; border-radius: 14px; box-shadow: 0 4px 16px rgba(245, 158, 11, 0.12); overflow: hidden; margin-bottom: 24px;">
                <div class="card-body" style="padding: 18px 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <div
                                style="width: 46px; height: 46px; border-radius: 12px; background: #fef08a; color: #b45309; display: flex; align-items: center; justify-content: center; font-size: 22px; box-shadow: 0 2px 8px rgba(180, 83, 9, 0.15);">
                                ⏳
                            </div>
                            <div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: #92400e;">
                                        {{ $project->waiting_step_name ?: 'BÜTÇE ONAYI BEKLENİYOR' }}
                                    </h3>
                                    <span class="badge"
                                        style="background: #fef08a; color: #854d0e; font-weight: 700; font-size: 11px;">Eşik Onay
                                        Süreci</span>
                                </div>
                                <p style="margin: 3px 0 0; font-size: 12.5px; color: #78350f;">
                                    Bu proje planlanan <strong>{{ number_format($project->planned_budget, 2, ',', '.') }}
                                        {{ $project->currency ?? 'TRY' }}</strong> bütçesi ile
                                    <strong>{{ $activeApproval->targetRole?->name ?? $activeApproval->targetUser?->name ?? 'Yetkili Yönetici' }}</strong>
                                    onayına sunulmuştur. (Bekleme Süresi: {{ $project->waiting_duration ?: 'Yeni' }})
                                </p>
                            </div>
                        </div>

                        @php
                            $userCanApprove = auth()->user()->isSuperAdmin()
                                || ($activeApproval->target_role_id && auth()->user()->hasRole($activeApproval->target_role_id))
                                || ($activeApproval->target_user_id && auth()->id() === $activeApproval->target_user_id);
                        @endphp

                        @if($userCanApprove)
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <form action="{{ route('projects.approve', $project->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success fw-bold px-3 py-2"
                                        style="box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);">
                                        ✓ Bütçeyi Onayla ve Devreye Al
                                    </button>
                                </form>
                                <form action="{{ route('projects.revert_approval', $project->id) }}" method="POST" style="margin: 0;"
                                    onsubmit="return confirm('Projeyi reddederek revizyona göndermek istediğinize emin misiniz?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger fw-bold px-3 py-2"
                                        style="background: #ffffff;">
                                        ↩ Reddet / Revizyon İste
                                    </button>
                                </form>
                            </div>
                        @else
                            <span class="badge"
                                style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 6px 12px; font-size: 12px; font-weight: 700;">
                                🛡️ Onay Yetkisi:
                                {{ $activeApproval->targetRole?->name ?? $activeApproval->targetUser?->name ?? 'İlgili Yönetici' }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @endif

    <!-- PROJE ÖZET BAŞLIK KARTI -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body" style="padding: 20px 24px;">
            <div
                style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                        <span
                            style="font-size: 13px; font-weight: 800; color: var(--primary); background: var(--primary-light); padding: 4px 10px; border-radius: 6px;">
                            {{ $project->code }}
                        </span>
                        <x-status-badge :status="$project->status" />
                        <x-priority-badge :priority="$project->priority" />
                        @php
                            $isPrivate = ($project->visibility ?? 'private') === 'private';
                            $canTogglePrivacy = auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->hasPermission('proje.manage') || $project->manager_id === auth()->id() || $project->sponsor_id === auth()->id() || $project->created_by === auth()->id();
                        @endphp
                        <div id="projectPrivacyWrapper" style="display: inline-flex; align-items: center; gap: 6px;"
                            data-project-id="{{ $project->id }}"
                            data-current-visibility="{{ $project->visibility ?? 'private' }}">
                            <span id="projectPrivacyBadge" class="badge"
                                style="font-size: 11.5px; font-weight: 700; padding: 4px 10px; border-radius: 6px; display: inline-flex; align-items: center; gap: 5px; {{ $isPrivate ? 'background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;' : 'background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;' }}"
                                title="{{ $isPrivate ? 'Yalnızca atanan ekip üyeleri, liderler ve gözlemciler görebilir' : 'Tüm şirket portföyde ve aramalarda görebilir' }}">
                                <span id="projectPrivacyIcon">{{ $isPrivate ? '🔒' : '🌐' }}</span>
                                <span id="projectPrivacyText">{{ $isPrivate ? 'Özel / Gizli Proje' : 'Genel Proje' }}</span>
                            </span>
                            @if($canTogglePrivacy)
                                <button type="button" class="btn btn-xs btn-outline-secondary"
                                    onclick="openChangePrivacyModal({{ $project->id }})"
                                    style="padding: 2px 8px; font-size: 11px; font-weight: 600; border-radius: 5px; border-color: #cbd5e1; color: #475569; background: #ffffff;"
                                    title="Proje gizlilik durumunu Genel ile Özel arasında anlık değiştir">
                                    🔄 Gizliliği Değiştir
                                </button>
                            @endif
                        </div>
                    </div>
                    <h2 style="font-size: 20px; font-weight: 800; color: var(--navy); margin-bottom: 8px;">
                        {{ $project->title }}</h2>
                    @if(!empty($project->tags_array))
                        <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px;">
                            @foreach($project->tags_array as $t)
                                <span
                                    style="background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); color: #0369a1; border: 1px solid #7dd3fc; font-size: 11.5px; font-weight: 750; padding: 2px 9px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 1px 2px rgba(2,132,199,0.06);">
                                    <span>🏷️</span> #{{ $t }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <!-- PROJE KAPSAMI, VİZYONU & YAZAR KÜNYESİ -->
                    <div
                        style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 18px; max-width: 880px; margin-top: 6px;">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 8px; border-bottom: 1px solid #edf2f7; padding-bottom: 6px;">
                            <span
                                style="font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;">
                                <span>📝</span> Proje Açıklaması & Kapsam Vizyonu
                                @if($project->isDraft())
                                    <button type="button" class="btn btn-xs btn-outline-info"
                                        onclick="openAddSuggestionModal('description', 'Proje Açıklaması ve Kapsamı')"
                                        style="font-size: 11px; padding: 1px 7px; border-radius: 4px; font-weight: 700; margin-left: 8px; border-color: #38bdf8; color: #0284c7;">
                                        💬 Öneri Ekle
                                    </button>
                                @endif
                            </span>
                            <div style="display: flex; align-items: center; gap: 8px; font-size: 11.5px;">
                                <span
                                    style="color: #0369a1; background: #e0f2fe; padding: 2px 8px; border-radius: 4px; font-weight: 600;">
                                    ✍️ Hazırlayan:
                                    {{ $project->creator->name ?? $project->manager->name ?? 'Proje Yöneticisi' }}
                                </span>
                                <span
                                    style="color: #64748b; background: #ffffff; border: 1px solid #e2e8f0; padding: 2px 8px; border-radius: 4px;">
                                    📅
                                    {{ $project->created_at ? $project->created_at->translatedFormat('d F Y, H:i') : '-' }}
                                </span>
                            </div>
                        </div>

                        <div class="project-rich-description" style="font-size: 13.5px; color: #1e293b; line-height: 1.65;">
                            @if($project->description)
                                {!! $project->description !!}
                            @else
                                <span style="color: #94a3b8; font-style: italic;">Proje açıklaması ve vizyonu
                                    belirtilmemiş.</span>
                            @endif
                        </div>

                        @if($project->files->count() > 0)
                            <div style="margin-top: 12px; padding-top: 10px; border-top: 1px dashed #cbd5e1;">
                                <div
                                    style="font-size: 11.5px; font-weight: 700; color: #475569; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                                    <span>📎</span> Proje Kapsam Ekleri & Dokümanlar ({{ $project->files->count() }}):
                                </div>
                                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                    @foreach($project->files as $pFile)
                                        @php
                                            $ext = strtolower(pathinfo($pFile->original_name, PATHINFO_EXTENSION));
                                            $fileIcon = in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif']) ? '🖼️' : ($ext === 'pdf' ? '📕' : (in_array($ext, ['xls', 'xlsx', 'xlm']) ? '📊' : (in_array($ext, ['doc', 'docx']) ? '📘' : ($ext === 'xml' ? '📙' : '📄'))));
                                        @endphp
                                        <a href="{{ route('projects.download_file', [$project->id, $pFile->id]) }}"
                                            style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 5px 12px; font-size: 12px; font-weight: 600; color: #1e293b; transition: all 0.15s; box-shadow: 0 1px 2px rgba(0,0,0,0.03);"
                                            onmouseover="this.style.borderColor='#0284c7'; this.style.background='#f0f9ff';"
                                            onmouseout="this.style.borderColor='#cbd5e1'; this.style.background='#ffffff';">
                                            <span>{{ $fileIcon }}</span>
                                            <span>{{ Str::limit($pFile->original_name, 30) }}</span>
                                            <span
                                                style="font-size: 10.5px; color: #64748b;">({{ number_format($pFile->file_size / 1024, 1) }}
                                                KB)</span>
                                            @if(!empty($pFile->tags_array))
                                                <span style="display: inline-flex; gap: 3px; margin: 0 4px;">
                                                    @foreach($pFile->tags_array as $ft)
                                                        <span
                                                            style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-size: 9.5px; font-weight: 700; padding: 1px 5px; border-radius: 4px;">#{{ $ft }}</span>
                                                    @endforeach
                                                </span>
                                            @endif
                                            <span style="font-size: 11px; color: #0284c7; font-weight: 700;">⬇ İndir</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- AKSİYON BUTONLARI -->
                <div style="display: flex; gap: 10px; align-items: center;">
                    @can('proje.approve')
                        @if($project->current_waiting_role_id)
                            <form action="{{ route('projects.approve', $project->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    ✓ Onayla ve Süreci İlerlet
                                </button>
                            </form>
                        @else
                            <button type="button" class="btn btn-warning"
                                onclick="document.getElementById('modalApproval').style.display='flex'">
                                ⏱️ Onaya Gönder...
                            </button>
                        @endif
                    @endcan

                    <a href="{{ route('kanban.index', ['project_id' => $project->id]) }}" class="btn btn-secondary"
                        style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600; text-decoration: none;">
                        📌 Kanban Pano
                    </a>
                    <a href="{{ route('gantt.index', ['project_id' => $project->id]) }}" class="btn btn-secondary"
                        style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600; text-decoration: none;">
                        📈 Gantt Çizelge
                    </a>

                    @if($canManage)
                        <a href="{{ route('projects.edit', $project->id) }}" class="btn"
                            style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; text-decoration: none !important; background: #ffffff; border: 1.5px solid #cbd5e1; color: #334155; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.2s;"
                            onmouseover="this.style.background='#f8fafc'; this.style.borderColor='#94a3b8'; this.style.color='#0f172a'; this.style.transform='translateY(-1px)';"
                            onmouseout="this.style.background='#ffffff'; this.style.borderColor='#cbd5e1'; this.style.color='#334155'; this.style.transform='none';"
                            title="Proje bilgilerini, ekibi ve bütçeyi güncelle">
                            <span>✏️</span> Projeyi Düzenle
                        </a>
                    @endif

                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isDeveloper() || auth()->user()->hasPermission('proje.delete'))
                        <form action="{{ route('projects.destroy', $project->id) }}" method="POST"
                            style="display: inline; margin: 0;">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn"
                                style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; text-decoration: none !important; background: #fff5f5; border: 1.5px solid #fecaca; color: #b91c1c; box-shadow: 0 1px 2px rgba(185,28,28,0.06); cursor: pointer; transition: all 0.2s;"
                                onmouseover="this.style.background='#fee2e2'; this.style.borderColor='#f87171'; this.style.color='#991b1b'; this.style.transform='translateY(-1px)';"
                                onmouseout="this.style.background='#fff5f5'; this.style.borderColor='#fecaca'; this.style.color='#b91c1c'; this.style.transform='none';"
                                title="Projeyi Sil (Yalnızca Admin/Developer)"
                                onclick="confirmProjectDelete(this, '{{ addslashes($project->title) }}', '{{ $project->code }}')">
                                <span>🗑️</span> Projeyi Sil
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- "KİMDE BEKLİYOR?" UYARI ALANI -->
            @if($project->current_waiting_role_id)
                <div
                    style="background: #fef2f2; border: 1px solid #f87171; padding: 12px 18px; border-radius: 8px; margin-top: 18px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="font-size: 13px; color: #991b1b; font-weight: 600;">
                        ⏱️ Bu proje şu anda <strong>{{ $project->waiting_duration }}</strong>
                        <strong>{{ $project->waitingRole?->name }}</strong> onayında beklemektedir. (Aşama:
                        <strong>{{ $project->waiting_step_name }}</strong>)
                    </div>
                    <span
                        style="font-size: 12px; background: #dc2626; color: #fff; padding: 3px 8px; border-radius: 4px; font-weight: 700;">SLA
                        DARBOĞAZI</span>
                </div>
            @endif

            <!-- METRİK ÖZET ÇİZELGESİ -->
            <div
                style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-top: 20px; padding-top: 18px; border-top: 1px solid var(--border-color);">
                <div>
                    <span style="font-size: 11px; color: var(--text-muted); font-weight: 600;">İlerleme Oranı</span>
                    <div style="font-size: 18px; font-weight: 800; color: var(--navy); margin-top: 2px;">
                        %{{ $project->progress_percentage }}</div>
                    <div style="background: #e2e8f0; height: 6px; border-radius: 3px; overflow: hidden; margin-top: 4px;">
                        <div style="background: var(--primary); width: {{ $project->progress_percentage }}%; height: 100%;">
                        </div>
                    </div>
                </div>

                <div>
                    <span style="font-size: 11px; color: var(--text-muted); font-weight: 600;">Planlanan Bütçe</span>
                    <div style="font-size: 18px; font-weight: 800; color: var(--navy); margin-top: 2px;">
                        {{ ($project->currency === 'USD' ? '$' : ($project->currency === 'EUR' ? '€' : '₺')) }}{{ number_format($project->planned_budget, 2, ',', '.') }}
                    </div>
                    <span style="font-size: 11px; color: var(--text-muted);">Fiili Harcama:
                        ₺{{ number_format($project->actual_spent, 0, ',', '.') }}</span>
                </div>

                <div>
                    <span style="font-size: 11px; color: var(--text-muted); font-weight: 600;">Proje Yöneticisi</span>
                    <div style="font-size: 14px; font-weight: 700; color: var(--navy); margin-top: 4px;">
                        {{ $project->manager?->name ?? 'Atanmamış' }}
                    </div>
                    <span style="font-size: 11px; color: var(--text-muted);">Sponsor:
                        {{ $project->sponsor?->name ?? '-' }}</span>
                </div>

                <div>
                    <span style="font-size: 11px; color: var(--text-muted); font-weight: 600;">Zaman Planı</span>
                    <div style="font-size: 13px; font-weight: 700; color: var(--navy); margin-top: 4px;">
                        {{ $project->start_date ? $project->start_date->format('d.m.Y') : '-' }} ➔
                        {{ $project->target_end_date ? $project->target_end_date->format('d.m.Y') : '-' }}
                    </div>
                    <span style="font-size: 11px; color: var(--text-muted);">Lokasyon:
                        {{ $project->location ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- =========================================================================
             KURUMSAL KİLOMETRE TAŞLARI & FAZ YOL HARİTASI (MILESTONE ROAD-MAP / STEPPER)
             ========================================================================= -->
    <div class="card mb-4"
        style="border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 2px 10px rgba(15,23,42,0.04); overflow: hidden; margin-bottom: 20px;">
        <div
            style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; color: #ffffff;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div
                    style="width: 34px; height: 34px; border-radius: 8px; background: rgba(56, 189, 248, 0.2); border: 1px solid rgba(56, 189, 248, 0.4); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    🚩
                </div>
                <div>
                    <h3
                        style="margin: 0; font-size: 14.5px; font-weight: 800; color: #ffffff; display: flex; align-items: center; gap: 8px;">
                        Kurumsal Kilometre Taşları & Faz Yol Haritası
                        <span class="badge"
                            style="background: rgba(255,255,255,0.15); color: #93c5fd; font-size: 11px; font-weight: 700;">
                            {{ $project->phases->count() }} Kilometre Taşı
                        </span>
                    </h3>
                    <p style="margin: 2px 0 0; font-size: 11.5px; color: #94a3b8;">
                        Projenin ana aşamaları, hedeflenen termin tarihleri ve tamamlanma kriterleri
                    </p>
                </div>
            </div>
            @if($project->isDraft())
                <button type="button" class="btn btn-sm btn-outline-info"
                    onclick="openAddSuggestionModal('milestones', 'Kilometre Taşları ve Faz Planı')"
                    style="border-radius: 6px; font-size: 12px; font-weight: 700; color: #38bdf8; border-color: rgba(56,189,248,0.5); background: rgba(15,23,42,0.5); display: inline-flex; align-items: center; gap: 6px;">
                    <span>💬</span> Kilometre Taşlarına Öneri Ekle
                </button>
            @endif
        </div>
        <div class="card-body" style="padding: 20px; background: #f8fafc; overflow-x: auto;">
            @if($project->phases->count() > 0)
                <div class="milestones-stepper-grid"
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 14px;">
                    @foreach($project->phases as $ph)
                        @php
                            $isDone = ($ph->status === 'completed' || $ph->status === 'done');
                            $isInProgress = ($ph->status === 'in_progress');
                            $hasEhs = (bool) $ph->requires_ehs_approval;
                            $cardBorder = $isDone ? '#86efac' : ($isInProgress ? '#93c5fd' : '#e2e8f0');
                            $headerBg = $isDone ? '#f0fdf4' : ($isInProgress ? '#eff6ff' : '#ffffff');
                            $numBg = $isDone ? '#22c55e' : ($isInProgress ? '#3b82f6' : '#64748b');
                        @endphp
                        <div class="milestone-card"
                            style="background: #ffffff; border: 1.5px solid {{ $cardBorder }}; border-radius: 10px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 1px 4px rgba(0,0,0,0.03); transition: transform 0.15s, box-shadow 0.15s;">
                            <!-- Header -->
                            <div
                                style="background: {{ $headerBg }}; padding: 10px 14px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div
                                        style="width: 24px; height: 24px; border-radius: 50%; background: {{ $numBg }}; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800;">
                                        {{ $isDone ? '✓' : $loop->iteration }}
                                    </div>
                                    <span style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">
                                        Faz {{ $loop->iteration }}
                                    </span>
                                </div>
                                <span class="badge"
                                    style="font-size: 10px; font-weight: 700; {{ $isDone ? 'background: #dcfce7; color: #166534;' : ($isInProgress ? 'background: #dbeafe; color: #1e40af;' : 'background: #f1f5f9; color: #475569;') }}">
                                    {{ $isDone ? 'Tamamlandı' : ($isInProgress ? 'Devam Ediyor' : 'Beklemede') }}
                                </span>
                            </div>
                            <!-- Body -->
                            <div
                                style="padding: 12px 14px; flex: 1; display: flex; flex-direction: column; justify-content: space-between; gap: 8px;">
                                <div>
                                    <div
                                        style="font-size: 13px; font-weight: 800; color: #0f172a; margin-bottom: 6px; line-height: 1.35;">
                                        {{ $ph->name }}
                                    </div>

                                    <!-- Hedef Tarih -->
                                    <div
                                        style="display: flex; align-items: center; gap: 6px; font-size: 11.5px; color: #475569; margin-bottom: 6px;">
                                        <span style="color: #0284c7; font-weight: 700;">📅 Hedef:</span>
                                        <span style="font-weight: 600; background: #f1f5f9; padding: 1px 6px; border-radius: 4px;">
                                            {{ $ph->end_date ? $ph->end_date->format('d.m.Y') : 'Belirtilmedi' }}
                                        </span>
                                    </div>

                                    <!-- Tamamlanma Kriteri -->
                                    @if($ph->description)
                                        <div
                                            style="font-size: 11.5px; color: #334155; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 8px; line-height: 1.4;">
                                            <strong
                                                style="color: #64748b; font-size: 10.5px; display: block; text-transform: uppercase;">🎯
                                                Tamamlanma Kriteri:</strong>
                                            {{ $ph->description }}
                                        </div>
                                    @endif
                                </div>

                                @if($hasEhs)
                                    <div
                                        style="font-size: 10.5px; background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; padding: 4px 6px; border-radius: 4px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; margin-top: 4px;">
                                        <span>🦺</span> İSG / Performans Gate Onayı Gerekir
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="text-align: center; padding: 24px; color: #64748b;">
                    <span>ℹ️</span> Henüz tanımlanmış kilometre taşı bulunmamaktadır.
                </div>
            @endif
        </div>
    </div>
    <!-- PROJE KAPSAM, ANALİZ VE MODÜLER ARAÇLAR VİTRİNİ -->
    @include('projects.partials.scope_tools_view', ['project' => $project])

    <!-- PROJE DETAY SEKME MENÜSÜ (TABS - Monday.com & Hub Modeli) -->
    <div
        style="display: flex; gap: 6px; border-bottom: 2px solid var(--border-color); margin-bottom: 20px; flex-wrap: wrap;">
        <button class="tab-btn active" onclick="switchTab('phases')">📊 1. Ana Tablo & Fazlar
            ({{ $project->phases->count() }})</button>
        <button class="tab-btn" onclick="switchTab('tasks')">📋 2. Kanban & Görevler
            ({{ $project->tasks->count() }})</button>
        <button class="tab-btn" onclick="switchTab('discussions')">💬 3. Tartışmalar & Notlar
            ({{ $project->discussions->count() }})</button>
        <button class="tab-btn" onclick="switchTab('raci')">👥 4. RACI Paydaşlar
            ({{ $project->stakeholders->count() }})</button>
        <button class="tab-btn" onclick="switchTab('risks')">⚠️ 5. 5x5 Risk Matrisi
            ({{ $project->risks->count() }})</button>
        <button class="tab-btn" onclick="switchTab('bom')">💰 6. Yatırım Bütçesi & Kalem Kırılımı
            ({{ $project->budgetItems->count() > 0 ? $project->budgetItems->count() : $project->boms->count() }})</button>
        <button class="tab-btn" onclick="switchTab('meetings')">📅 7. Toplantılar
            ({{ $project->meetings->count() }})</button>
        <button class="tab-btn" onclick="switchTab('files')">📁 8. Dosya & Envanter Kasası
            ({{ $project->files->count() }})</button>
        <button class="tab-btn" onclick="switchTab('logs')">📜 9. Denetim İzi (Loglar)</button>
        <button class="tab-btn" onclick="switchTab('team_matrix')"
            style="background: #f0fdf4; color: #166534; border-radius: 6px 6px 0 0; font-weight: 700;">👥 10. Ekip & Yetki
            Matrisi ({{ $project->stakeholders->count() }})</button>
        <button class="tab-btn" onclick="switchTab('quality')"
            style="background: #f0f9ff; color: #0284c7; border-radius: 6px 6px 0 0; font-weight: 700;">🔬 11. Kalite & Kabul
            Kriterleri ({{ $project->qualities->count() }})</button>
        <button class="tab-btn" onclick="switchTab('baselines')"
            style="background: #eef2ff; color: #4338ca; border-radius: 6px 6px 0 0; font-weight: 700;">📐 12. Temel Çizgi
            (Baseline & EVM) ({{ $project->baselines->count() }})</button>
    </div>

    <!-- TAB 1: FAZLAR & MONDAY.COM TARZI KATLANABİLİR WBS TABLOSU (Görsel 4) -->
    <div id="tab-phases" class="tab-content" style="display: block;">

        <!-- =========================================================================
                 DİNAMİK PROJE İŞ AKIŞI TUVÂLİ (INTERACTIVE WORKFLOW CANVAS & PULSING NODES)
                 ========================================================================= -->
        <div class="workflow-canvas-card" id="projectWorkflowCanvasCard">
            <div class="workflow-canvas-header">
                <div class="workflow-header-title">
                    <span style="font-size: 18px;">🌐</span>
                    <span>Canlı Süreç & Görev İş Akışı (Live Workflow Pipeline)</span>
                    <span class="badge"
                        style="background: rgba(34, 197, 94, 0.2); color: #86efac; border: 1px solid rgba(134, 239, 172, 0.4); font-size: 11px;">⚡
                        Otomatik Çizilen Akış</span>
                </div>
                <div class="workflow-header-actions">
                    @if($canManage)
                        <button type="button" class="btn btn-sm btn-success" onclick="openCreateTaskModal()"
                            style="font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 6px; box-shadow: 0 2px 4px rgba(22, 163, 74, 0.3);">
                            <span>➕</span> Yeni Görev Ekle
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="toggleWorkflowCanvas()"
                            id="btnToggleWorkflow" style="font-size: 11.5px; padding: 5px 12px; border-radius: 6px;">
                            🔽 Daralt / Genişlet
                        </button>
                    @endif
                </div>
            </div>

            <!-- LEJANT ŞERİDİ -->
            <div class="workflow-legend-bar">
                <div class="legend-item"><span class="legend-dot done"></span> <strong>Tamamlandı (Yeşil Düz Çizgi)</strong>
                </div>
                <div class="legend-item"><span class="legend-dot active"></span> <strong>Süreçte / Bekliyor (Pulsing Glow &
                        Hareketli Çizgi)</strong></div>
                <div class="legend-item"><span class="legend-dot ehs"></span> <strong>İSG İzin Kapısı (Denetim
                        Kalkanı)</strong></div>
                <div class="legend-item"><span class="legend-dot pending"></span> <strong>Sıradaki Adım (Planlanan)</strong>
                </div>
            </div>

            <!-- TUVÂL VIEWPORT & SVG BAĞLANTI KATMANI -->
            <div class="workflow-canvas-viewport" id="workflowCanvasViewport">
                <svg class="workflow-svg-layer" id="workflowSvgLayer"></svg>
                <div class="workflow-nodes-container" id="workflowNodesContainer">
                    <!-- Dinamik JavaScript Akış Motoru -->
                </div>
            </div>
        </div>

        <!-- 🔄 MODÜL 4: FAZ & İSG ONAY AKIŞI (STAGE-GATE WORKFLOW & PIPELINE) -->
        <div class="card mb-4" id="stageGateWorkflowCard"
            style="border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff; box-shadow: 0 4px 16px rgba(15,23,42,0.04); overflow: hidden;">
            <div
                style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; color: #ffffff;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div
                        style="width: 32px; height: 32px; border-radius: 8px; background: rgba(56,189,248,0.15); border: 1px solid rgba(56,189,248,0.3); display: flex; align-items: center; justify-content: center; font-size: 16px;">
                        🔄
                    </div>
                    <div>
                        <h3 style="margin: 0; font-size: 15px; font-weight: 800; color: #ffffff; letter-spacing: -0.2px;">
                            Faz & İSG Onay Akışı (Stage-Gate Workflow)
                        </h3>
                        <p style="margin: 2px 0 0; font-size: 11.5px; color: #94a3b8;">
                            Aşama kapı kilitleri, İSG saha izinleri ve sıralı onay zinciri
                        </p>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    @php
                        $totalPh = $project->phases->count();
                        $compPh = $project->phases->where('status', 'completed')->count();
                        $ehsLockedPh = $project->phases->where('requires_ehs_approval', true)->whereNull('ehs_approved_at')->count();
                        $ehsApprovedPh = $project->phases->where('requires_ehs_approval', true)->whereNotNull('ehs_approved_at')->count();
                    @endphp
                    <span
                        style="background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3); color: #34d399; font-size: 11.5px; font-weight: 700; padding: 4px 10px; border-radius: 20px;">
                        ✓ {{ $compPh }}/{{ $totalPh }} Faz Tamamlandı
                    </span>
                    @if($ehsLockedPh > 0)
                        <span
                            style="background: rgba(239,68,68,0.2); border: 1px solid rgba(239,68,68,0.4); color: #f87171; font-size: 11.5px; font-weight: 700; padding: 4px 10px; border-radius: 20px; animation: pulse 2s infinite;">
                            🔒 {{ $ehsLockedPh }} İSG Kilidi Bekliyor
                        </span>
                    @else
                        <span
                            style="background: rgba(59,130,246,0.15); border: 1px solid rgba(59,130,246,0.3); color: #60a5fa; font-size: 11.5px; font-weight: 700; padding: 4px 10px; border-radius: 20px;">
                            🛡️ Tüm İSG İzinleri Açık
                        </span>
                    @endif
                </div>
            </div>

            <!-- STAGE-GATE STEPPER AKIŞI -->
            <div style="padding: 20px; overflow-x: auto; background: #fafafa;">
                <div
                    style="display: flex; align-items: stretch; gap: 14px; min-width: {{ max(700, $project->phases->count() * 260) }}px;">
                    @forelse($project->phases as $idx => $ph)
                        @php
                            $isComp = ($ph->status === 'completed');
                            $isEhsLocked = ($ph->requires_ehs_approval && !$ph->ehs_approved_at);
                            $isCurrent = (!$isComp && ($idx === 0 || $project->phases[$idx - 1]->status === 'completed'));
                            $tasksCount = $ph->tasks->count();
                            $doneTasksCount = $ph->tasks->where('status', 'done')->count();
                            $progressPct = (int) round($ph->progress_percentage);
                        @endphp

                        <div class="stage-gate-node"
                            style="flex: 1; min-width: 240px; background: #ffffff; border-radius: 10px; border: 2px solid {{ $isComp ? '#10b981' : ($isEhsLocked ? '#f87171' : ($isCurrent ? '#0284c7' : '#e2e8f0')) }}; box-shadow: {{ $isCurrent ? '0 4px 14px rgba(2,132,199,0.12)' : '0 2px 6px rgba(0,0,0,0.03)' }}; display: flex; flex-direction: column; justify-content: space-between; overflow: hidden; position: relative;">

                            <!-- Üst Başlık & İlerleme Çubuğu -->
                            <div
                                style="padding: 12px 14px; background: {{ $isComp ? '#f0fdf4' : ($isEhsLocked ? '#fef2f2' : ($isCurrent ? '#f0f9ff' : '#f8fafc')) }}; border-bottom: 1px solid #e2e8f0;">
                                <div
                                    style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span
                                            style="width: 22px; height: 22px; border-radius: 50%; background: {{ $isComp ? '#10b981' : ($isEhsLocked ? '#ef4444' : ($isCurrent ? '#0284c7' : '#94a3b8')) }}; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800;">
                                            {{ $isComp ? '✓' : ($idx + 1) }}
                                        </span>
                                        <span
                                            style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">
                                            Kapı {{ $idx + 1 }}
                                        </span>
                                    </div>
                                    <span
                                        style="font-size: 11px; font-weight: 800; color: {{ $isComp ? '#059669' : ($isEhsLocked ? '#dc2626' : ($isCurrent ? '#0284c7' : '#64748b')) }};">
                                        %{{ $progressPct }}
                                    </span>
                                </div>
                                <h4 style="margin: 0; font-size: 13px; font-weight: 800; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                    title="{{ $ph->name }}">
                                    {{ $ph->name }}
                                </h4>
                            </div>

                            <!-- Gövde: Bilgiler & İSG Durumu -->
                            <div style="padding: 12px 14px; font-size: 11.5px; color: #475569; flex: 1;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                                    <span>Görevler:</span>
                                    <strong>{{ $doneTasksCount }} / {{ $tasksCount }} Bitti</strong>
                                </div>

                                @if($ph->start_date || $ph->end_date)
                                    <div
                                        style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 11px; color: #64748b;">
                                        <span>Takvim:</span>
                                        <span>{{ $ph->start_date ? \Carbon\Carbon::parse($ph->start_date)->format('d.m') : '-' }} ➔
                                            {{ $ph->end_date ? \Carbon\Carbon::parse($ph->end_date)->format('d.m.y') : '-' }}</span>
                                    </div>
                                @endif

                                <!-- İSG Kilidi Kutusu -->
                                @if($ph->requires_ehs_approval)
                                    <div
                                        style="margin-top: 8px; padding: 6px 10px; border-radius: 6px; background: {{ $ph->ehs_approved_at ? '#ecfdf5' : '#fef2f2' }}; border: 1px solid {{ $ph->ehs_approved_at ? '#a7f3d0' : '#fecaca' }};">
                                        @if($ph->ehs_approved_at)
                                            <div
                                                style="font-weight: 700; color: #065f46; font-size: 11px; display: flex; align-items: center; justify-content: space-between;">
                                                <span>✓ İSG Saha İzni Açık</span>
                                                @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->hasPermission('isg.approve_gates'))
                                                    <form action="{{ route('projects.revert_ehs', [$project->id, $ph->id]) }}" method="POST"
                                                        style="margin: 0; display: inline;">
                                                        @csrf
                                                        <button type="submit"
                                                            style="background: none; border: none; color: #dc2626; font-size: 10px; font-weight: 700; cursor: pointer; padding: 0;"
                                                            title="İSG İznini Geri Al"
                                                            onclick="return confirm('İSG Saha İzin Onayını geri almak istediğinize emin misiniz?');">
                                                            [Kilitle]
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                            <div style="font-size: 10px; color: #047857; margin-top: 2px;">
                                                Onaylayan: {{ $ph->ehsApprovedBy?->name ?? 'İSG Yetkilisi' }}
                                            </div>
                                        @else
                                            <div
                                                style="font-weight: 700; color: #b91c1c; font-size: 11px; display: flex; align-items: center; justify-content: space-between;">
                                                <span>🔒 İSG Kilidi Bekliyor</span>
                                                @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->hasPermission('isg.approve_gates'))
                                                    <form action="{{ route('projects.approve_ehs', [$project->id, $ph->id]) }}"
                                                        method="POST" style="margin: 0; display: inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-xs btn-danger"
                                                            style="font-size: 9.5px; padding: 2px 6px; border-radius: 4px; font-weight: 700;"
                                                            onclick="return confirm('Bu aşama için İSG Saha İzin Onayı verilecek. Devam edilsin mi?');">
                                                            🛡️ Onayla
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                            <div style="font-size: 10px; color: #991b1b; margin-top: 2px;">
                                                Saha İzni verilmeden tamamlanamaz.
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div style="margin-top: 8px; font-size: 10.5px; color: #94a3b8;">
                                        Standart Proje Aşaması
                                    </div>
                                @endif
                            </div>

                            <!-- Alt Eylemler Barı -->
                            <div
                                style="padding: 10px 14px; background: #ffffff; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                                @if(!$isComp)
                                    @if($project->isDraft())
                                        <span class="badge"
                                            style="background: #fef3c7; color: #92400e; font-size: 10.5px; width: 100%; text-align: center; padding: 5px; border: 1px solid #fde68a;"
                                            title="Proje taslak modundadır. Fazları tamamlamak için önce projeyi yayınlayın.">
                                            📝 Taslak Modunda
                                        </span>
                                    @elseif($project->status === 'waiting_approval')
                                        <span class="badge"
                                            style="background: #e0f2fe; color: #0369a1; font-size: 10.5px; width: 100%; text-align: center; padding: 5px; border: 1px solid #bae6fd;"
                                            title="Proje onay sürecindedir.">
                                            ⏳ Onay Bekliyor
                                        </span>
                                    @elseif($canManage && !auth()->user()->isYonetim())
                                        <form action="{{ route('projects.complete_phase', [$project->id, $ph->id]) }}" method="POST"
                                            style="margin: 0; width: 100%;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success"
                                                style="width: 100%; font-size: 11px; font-weight: 700; padding: 5px 8px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; gap: 4px;"
                                                {{ $isEhsLocked ? 'disabled' : '' }}
                                                onclick="return confirm('Bu adımı tamamlandı olarak onaylamak istediğinize emin misiniz?');">
                                                <span>✓</span> {{ $isEhsLocked ? 'İSG Kilidi Var' : 'Adımı Tamamla' }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="badge"
                                            style="background: #f1f5f9; color: #64748b; font-size: 10.5px; width: 100%; text-align: center; padding: 5px;">
                                            İnceleniyor
                                        </span>
                                    @endif
                                @else
                                    @php
                                        $canRev = auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || ($ph->completed_by && $ph->completed_by === auth()->id()) || ($project->manager_id === auth()->id());
                                    @endphp
                                    <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                                        <span
                                            style="font-size: 11px; font-weight: 700; color: #059669; display: inline-flex; align-items: center; gap: 3px;">
                                            <span>✓</span> Tamamlandı
                                        </span>
                                        @if($canRev)
                                            <form action="{{ route('projects.revert_phase', [$project->id, $ph->id]) }}" method="POST"
                                                style="margin: 0;">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-outline-danger"
                                                    style="font-size: 10px; padding: 2px 7px; border-radius: 4px; font-weight: 700;"
                                                    onclick="return confirm('Bu adımı geri almak istediğinize emin misiniz?');">
                                                    ↩️ Geri Al
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endif
                            </div>

                        </div>

                        @if(!$loop->last)
                            <div
                                style="display: flex; align-items: center; justify-content: center; color: #cbd5e1; font-size: 20px; font-weight: 800; user-select: none;">
                                ➔
                            </div>
                        @endif
                    @empty
                        <div style="padding: 20px; color: #64748b; font-size: 13px;">Tanımlı aşama bulunmuyor.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- ÜST İSTATİSTİK & HIZLI AŞAMA EKLEME ŞERİDİ -->
        <div
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 15px; font-weight: 800; color: #0f172a;">📊 İnteraktif Proje Tablosu & Faz
                    Grupları</span>
                <span class="badge"
                    style="background: #eff6ff; color: #1e40af; font-weight: 700;">{{ $project->phases->count() }} Faz
                    Grubu</span>
                <span class="badge"
                    style="background: #f0fdf4; color: #15803d; font-weight: 700;">{{ $project->tasks->count() }} Toplam
                    Görev</span>
            </div>

            <!-- YENİ AŞAMA / ADIM EKLEME AÇILIR PANELİ -->
            <div>
                <button type="button" class="btn btn-sm btn-primary"
                    onclick="document.getElementById('addPhaseModalBlock').style.display = document.getElementById('addPhaseModalBlock').style.display === 'none' ? 'block' : 'none';"
                    style="font-weight: 600; font-size: 12px; display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 6px;">
                    <span>➕</span> Yeni Aşama / Faz Grubu Ekle
                </button>
            </div>
        </div>

        <!-- YENİ FAZ EKLEME FORMU KUTUSU (GİZLENEBİLİR) -->
        <div id="addPhaseModalBlock"
            style="display: none; background: #ffffff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 18px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(37,99,235,0.06);">
            <div
                style="font-size: 13.5px; font-weight: 700; color: #1e40af; margin-bottom: 12px; display: flex; justify-content: space-between;">
                <span>➕ Yeni Aşama (Faz) Grubu Tanımla</span>
                <button type="button" onclick="document.getElementById('addPhaseModalBlock').style.display = 'none';"
                    style="border: none; background: none; cursor: pointer; color: #64748b; font-size: 16px;">✕</button>
            </div>
            <form action="{{ route('projects.add_phase', $project->id) }}" method="POST">
                @csrf
                <div class="grid-3" style="gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label class="form-label" style="font-size: 11px; font-weight: 600;">Aşama / Faz Başlığı *</label>
                        <input type="text" name="name" class="form-control" required
                            placeholder="Örn: 3. Çelik Montaj & Mekanik Kurulum" style="font-size: 12.5px;">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 11px; font-weight: 600;">Başlangıç Tarihi</label>
                        <input type="date" name="start_date" class="form-control" style="font-size: 12.5px;">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 11px; font-weight: 600;">Hedef Bitiş Tarihi</label>
                        <input type="date" name="end_date" class="form-control" style="font-size: 12.5px;">
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <label
                        style="font-size: 12px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; color: #b45309; font-weight: 600;">
                        <input type="checkbox" name="requires_ehs_approval" value="1">
                        <span>🔒 Bu aşama İSG Güvenlik Kapı Kilidi (Saha İzin Onayı) gerektirsin</span>
                    </label>
                    <button type="submit" class="btn btn-primary btn-sm"
                        style="font-size: 12px; font-weight: 600; padding: 6px 18px;">
                        Aşamayı Kaydet ve Tabloya Ekle
                    </button>
                </div>
            </form>
        </div>

        <!-- MONDAY.COM KATLANABİLİR FAZ GRUPLARI LİSTESİ -->
        @php
            $phaseColors = [
                0 => ['border' => '#3b82f6', 'bg' => '#eff6ff', 'text' => '#1e40af'],
                1 => ['border' => '#10b981', 'bg' => '#ecfdf5', 'text' => '#065f46'],
                2 => ['border' => '#f59e0b', 'bg' => '#fffbeb', 'text' => '#92400e'],
                3 => ['border' => '#8b5cf6', 'bg' => '#f5f3ff', 'text' => '#5b21b6'],
                4 => ['border' => '#ec4899', 'bg' => '#fdf2f8', 'text' => '#9d174d'],
            ];
        @endphp

        @forelse($project->phases as $pIndex => $ph)
            @php
                $color = $phaseColors[$pIndex % 5];
                $phaseTasks = $ph->tasks;
                $completedTasksCount = $phaseTasks->where('status', 'done')->count();
                $totalTasksCount = $phaseTasks->count();
                $calculatedProgress = $totalTasksCount > 0 ? round(($completedTasksCount / $totalTasksCount) * 100) : $ph->progress_percentage;
            @endphp

            <div class="monday-phase-group"
                style="margin-bottom: 24px; border: 1px solid #e2e8f0; border-radius: 10px; background: #ffffff; box-shadow: 0 1px 4px rgba(15,23,42,0.04); overflow: hidden;">

                <!-- FAZ GRUBU BAŞLIĞI (AKORDEON & HIZLI KONTROL) -->
                <div style="background: #ffffff; border-left: 6px solid {{ $color['border'] }}; border-bottom: 1px solid #e2e8f0; padding: 12px 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; cursor: pointer;"
                    onclick="togglePhaseCollapse({{ $ph->id }})">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span id="arrow-phase-{{ $ph->id }}"
                            style="font-size: 14px; font-weight: 800; color: #64748b; transition: transform 0.2s;">▼</span>
                        <h4 style="margin: 0; font-size: 15px; font-weight: 800; color: #0f172a;">
                            {{ $ph->name }}
                        </h4>
                        <span class="badge"
                            style="background: {{ $color['bg'] }}; color: {{ $color['text'] }}; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 4px;">
                            {{ $totalTasksCount }} Görev
                        </span>
                        @if($ph->status === 'completed')
                            <span class="badge" style="background: #dcfce7; color: #166534; font-size: 11px; font-weight: 700;">
                                ✅ Tamamlandı
                            </span>
                        @endif
                    </div>

                    <!-- SAĞ TARAF: İLERLEME, İSG KİLİDİ VE AKSİYONLAR -->
                    <div style="display: flex; align-items: center; gap: 14px;" onclick="event.stopPropagation();">
                        <!-- İlerleme Çubuğu -->
                        <div style="display: flex; align-items: center; gap: 8px; width: 140px;">
                            <div style="background: #e2e8f0; height: 6px; border-radius: 3px; flex: 1; overflow: hidden;">
                                <div
                                    style="background: {{ $color['border'] }}; width: {{ $calculatedProgress }}%; height: 100%;">
                                </div>
                            </div>
                            <span style="font-size: 11px; font-weight: 700; color: #334155;">%{{ $calculatedProgress }}</span>
                        </div>

                        <!-- İSG Kapı Kilidi Durumu -->
                        @if($ph->requires_ehs_approval)
                            @if($ph->ehs_approved_at)
                                <span
                                    style="font-size: 11px; font-weight: 700; color: #059669; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 3px 8px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
                                    ✓ İSG Açık
                                </span>
                            @else
                                <span
                                    style="font-size: 11px; font-weight: 700; color: #dc2626; background: #fef2f2; border: 1px solid #fecaca; padding: 3px 8px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
                                    🔒 İSG Kilitli
                                </span>
                            @endif
                        @endif

                        <!-- Adımı Tamamla / Geri Al Butonları -->
                        @if($ph->status !== 'completed')
                            @if($project->isDraft())
                                <span class="badge"
                                    style="background: #fef3c7; color: #92400e; font-size: 11px; padding: 4px 10px; border-radius: 4px; border: 1px solid #fde68a;"
                                    title="Proje taslak modundadır. Fazları tamamlamak için önce projeyi yayınlayın.">
                                    📝 Taslak (Yayınlanmadı)
                                </span>
                            @elseif($project->status === 'waiting_approval')
                                <span class="badge"
                                    style="background: #e0f2fe; color: #0369a1; font-size: 11px; padding: 4px 10px; border-radius: 4px; border: 1px solid #bae6fd;">
                                    ⏳ Onay Bekliyor
                                </span>
                            @elseif($canManage && !auth()->user()->isYonetim())
                                <form action="{{ route('projects.complete_phase', [$project->id, $ph->id]) }}" method="POST"
                                    style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline"
                                        style="font-size: 11px; padding: 4px 10px; border-radius: 4px; border: 1px solid #86efac; color: #15803d; background: #f0fdf4; font-weight: 700; cursor: pointer;"
                                        onclick="return confirm('Bu adımı/fazı başarıyla tamamlandı olarak işaretlemek istediğinize emin misiniz?');">
                                        ✓ Adımı Tamamla
                                    </button>
                                </form>
                            @endif
                        @else
                            @php
                                $canRevertPhase = auth()->user()->isSuperAdmin()
                                    || auth()->user()->isAdmin()
                                    || ($ph->completed_by && $ph->completed_by === auth()->id())
                                    || ($project->manager_id === auth()->id());
                                $completedByName = $ph->completedBy?->name ?? 'İlgili Personel';
                                $isActingOnBehalf = ($ph->completed_by && $ph->completed_by !== auth()->id());
                            @endphp
                            @if($canRevertPhase)
                                <form action="{{ route('projects.revert_phase', [$project->id, $ph->id]) }}" method="POST"
                                    style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline"
                                        style="font-size: 11px; padding: 3px 8px; border-radius: 4px; border: 1px solid #fecaca; background: #fff5f5; color: #dc2626; font-weight: 700; cursor: pointer;"
                                        onclick="return confirm('{{ $isActingOnBehalf ? '⚠️ DİKKAT: Bu işlem ' . $completedByName . ' tarafından tamamlanmıştır.\n\n[' . $completedByName . '] ADINA BU ADIMI GERİ ALIYORSUNUZ!\n\nProje durumu eksiksiz olarak bir önceki haline döndürülecek ve kullanıcıya bildirim gönderilecektir.\n\nOnaylıyor musunuz?' : 'Bu adımı geri almak istediğinize emin misiniz? Proje durumu bir önceki haline döndürülecektir.' }}');"
                                        title="{{ $isActingOnBehalf ? $completedByName . ' adına adımı geri al' : 'Adımı geri al' }}">
                                        ↩️ Geri Al
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- FAZIN İÇİNDEKİ MONDAY.COM GÖREVLER TABLOSU -->
                <div id="content-phase-{{ $ph->id }}" style="display: block;">
                    @if($phaseTasks->count() > 0)
                        <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                            <thead>
                                <tr
                                    style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">
                                    <th style="padding: 10px 16px; width: 35%;">Görev / İş Paketi</th>
                                    <th style="padding: 10px 14px; width: 22%;">Sorumlu (SAP Modeli)</th>
                                    <th style="padding: 10px 14px; width: 14%; text-align: center;">Durum</th>
                                    <th style="padding: 10px 14px; width: 12%; text-align: center;">Öncelik</th>
                                    <th style="padding: 10px 14px; width: 17%;">Termin & Zaman Çizelgesi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($phaseTasks as $task)
                                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;"
                                        onmouseover="this.style.background='#fbfcfe'" onmouseout="this.style.background='#ffffff'">
                                        <!-- GÖREV ADI -->
                                        <td style="padding: 12px 16px;">
                                            <div
                                                style="font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                                                <span>{{ $task->title }}</span>
                                                @if($task->tag)
                                                    <span
                                                        style="font-size: 10px; font-weight: 600; color: #2563eb; background: #eff6ff; padding: 1px 6px; border-radius: 4px;">
                                                        {{ $task->tag }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($task->description)
                                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                                    {{ Str::limit($task->description, 55) }}
                                                </div>
                                            @endif
                                        </td>

                                        <!-- SORUMLU KİŞİ (SAP EKİP MODELİ AVATARI) -->
                                        <td style="padding: 12px 14px;">
                                            @if($task->assigned_to && $task->assignee)
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <div
                                                        style="width: 26px; height: 26px; border-radius: 50%; background: #3b82f6; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700;">
                                                        {{ mb_substr($task->assignee->name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <div style="font-weight: 600; color: #1e293b; font-size: 12px;">
                                                            {{ $task->assignee->name }}</div>
                                                        <div style="font-size: 10px; color: #64748b;">
                                                            {{ $task->assignee->unvan ?? 'Uzman' }}</div>
                                                    </div>
                                                </div>
                                            @else
                                                <div
                                                    style="display: inline-flex; align-items: center; gap: 5px; background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700;">
                                                    <span>👥</span> Tüm Proje Ekibi (Ortak)
                                                </div>
                                            @endif
                                        </td>

                                        <!-- DURUM (İnteraktif Durum Seçici & Monday.com Modeli) -->
                                        <td style="padding: 10px 14px; text-align: center;">
                                            @if($canManage)
                                                        <form action="{{ route('projects.update_task_status', [$project->id, $task->id]) }}"
                                                            method="POST" style="margin: 0; display: inline-block;">
                                                            @csrf
                                                            <select name="status" onchange="this.form.submit()" title="Görev durumunu değiştir"
                                                                style="font-size: 11.5px; font-weight: 750; padding: 4px 10px; border-radius: 6px; cursor: pointer; border: 1.5px solid; transition: all 0.15s; outline: none;
                                                                                    {{ $task->status === 'done' ? 'background: #dcfce7; color: #15803d; border-color: #86efac;' :
                                                ($task->status === 'in_progress' ? 'background: #e0f2fe; color: #0369a1; border-color: #7dd3fc;' :
                                                    ($task->status === 'review' ? 'background: #fef3c7; color: #b45309; border-color: #fde68a;' :
                                                        'background: #f1f5f9; color: #475569; border-color: #cbd5e1;')) }}">
                                                                <option value="todo" {{ $task->status === 'todo' ? 'selected' : '' }}>⏳ Yapılacak
                                                                </option>
                                                                <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>⚡
                                                                    Devam Ediyor</option>
                                                                <option value="review" {{ $task->status === 'review' ? 'selected' : '' }}>👀
                                                                    İncelemede</option>
                                                                <option value="done" {{ $task->status === 'done' ? 'selected' : '' }}>✅ Tamamlandı
                                                                </option>
                                                            </select>
                                                        </form>
                                            @else
                                                <x-status-badge :status="$task->status" />
                                            @endif
                                        </td>

                                        <!-- ÖNCELİK (Standart Sistem Rozeti) -->
                                        <td style="padding: 12px 14px; text-align: center;">
                                            <x-priority-badge :priority="$task->priority" />
                                        </td>

                                        <!-- TERMİN & ZAMAN ÇİZELGESİ -->
                                        <td style="padding: 12px 14px;">
                                            <div
                                                style="font-size: 12px; font-weight: 600; color: {{ $task->due_date && $task->due_date->isPast() && $task->status !== 'done' ? '#dc2626' : '#334155' }};">
                                                📅 {{ $task->due_date ? $task->due_date->format('d M Y') : 'Tarih Yok' }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <!-- Görev Yoksa Bilgi ve Hızlı Ekleme -->
                        <div style="padding: 20px; text-align: center; color: #64748b; font-size: 12.5px; background: #fafafa;">
                            Bu aşama için henüz görev oluşturulmamış. Üstteki <strong>"Kanban & Görevler"</strong> sekmesinden bu
                            aşamaya görev bağlayabilirsiniz.
                        </div>
                    @endif

                    <!-- FAZ DİP TOPLAM ÇUBUĞU (MONDAY.COM SUMMARY ROW) -->
                    <div
                        style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 10px 18px; display: flex; justify-content: space-between; align-items: center; font-size: 11.5px; color: #475569;">
                        <div>
                            <span>Tamamlanma Oranı: <strong>%{{ $calculatedProgress }}</strong>
                                ({{ $completedTasksCount }}/{{ $totalTasksCount }} Görev Tamamlandı)</span>
                        </div>
                        <div>
                            <span style="color: #64748b;">Tarih Aralığı:
                                <strong>{{ $ph->start_date ? $ph->start_date->format('d.m.Y') : '-' }} ➔
                                    {{ $ph->end_date ? $ph->end_date->format('d.m.Y') : '-' }}</strong></span>
                        </div>
                    </div>
                </div>

            </div>
        @empty
            <div
                style="text-align: center; padding: 60px; color: #64748b; background: #ffffff; border-radius: 12px; border: 1px dashed #cbd5e1;">
                <div style="font-size: 36px; margin-bottom: 10px;">📋</div>
                <div style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">Projeye Ait Aşama (Faz)
                    Bulunamadı</div>
                <div style="font-size: 13px; margin-bottom: 14px;">Yukarıdaki "Yeni Aşama Ekle" butonuna tıklayarak projenize
                    adımlar tanımlayabilirsiniz.</div>
            </div>
        @endforelse

    </div>

    <!-- TAB 2: GÖREVLER & WBS -->
    <div id="tab-tasks" class="tab-content" style="display: none;">
        <div class="card">
            <div class="card-header">
                <span class="card-title">Proje Görevleri (WBS Hiyerarşisi)</span>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Görev Başlığı</th>
                            <th>Faz</th>
                            <th>Etiket</th>
                            <th>Sorumlu</th>
                            <th>Efor (Tahmini / Harcanan)</th>
                            <th>Termin</th>
                            <th>Durum</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($project->tasks as $t)
                            <tr>
                                <td>
                                    <strong style="color: var(--navy);">{{ $t->title }}</strong>
                                    @if($t->is_milestone)
                                        <span
                                            style="font-size: 10px; background: #fef08a; color: #854d0e; padding: 2px 6px; border-radius: 4px; font-weight: 700; margin-left: 6px;">💎
                                            Kilometre Taşı</span>
                                    @endif
                                </td>
                                <td style="font-size: 12px; color: var(--text-muted);">{{ $t->phase?->name ?? '-' }}</td>
                                <td>
                                    @if($t->tag)
                                        <span
                                            style="font-size: 11px; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-weight: 600;">{{ $t->tag }}</span>
                                    @endif
                                </td>
                                <td>{{ $t->assignee?->name ?? 'Atanmamış' }}</td>
                                <td style="font-size: 12px;">
                                    <strong>{{ $t->spent_hours }}s</strong> / {{ $t->estimated_hours }}s
                                </td>
                                <td style="font-size: 12px; font-weight: 600;">
                                    {{ $t->due_date ? $t->due_date->format('d.m.Y') : '-' }}</td>
                                <td><x-status-badge :status="$t->status" /></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 24px;">Henüz görev açılmadı.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 3: PROJE İÇİ CANLI TARTIŞMALAR & NOTLAR (DISCUSSIONS) -->
    <div id="tab-discussions" class="tab-content" style="display: none;">
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header"
                style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
                <span class="card-title"
                    style="font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <span>💬</span> Proje Ekip Tartışmaları, Saha Notları & Koordinasyon
                </span>
                <span class="badge" style="background: #eff6ff; color: #1e40af; font-weight: 700;">
                    {{ $project->discussions->count() }} Mesaj & Not
                </span>
            </div>

            <div class="card-body" style="padding: 20px;">
                <!-- YENİ MESAJ / YORUM PAYLAŞMA FORMU -->
                <div
                    style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; margin-bottom: 24px;">
                    <h4
                        style="margin: 0 0 10px 0; font-size: 13.5px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                        <span>✍️</span> Projeye Yeni Tartışma Notu / Görüş Bırak
                    </h4>
                    <form action="{{ route('projects.discussions.store', $project->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="form-group" style="margin-bottom: 12px;">
                            <textarea name="message" rows="3" class="form-control" required
                                placeholder="Proje ile ilgili saha tespiti, teknik soru, tedarik notu veya koordinasyon mesajınızı yazınız..."
                                style="font-size: 13px; line-height: 1.5; background: #ffffff;"></textarea>
                        </div>
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <label
                                    style="font-size: 12px; color: #64748b; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                    <span>📎 Belge/Fotoğraf İliştir (Opsiyonel):</span>
                                    <input type="file" name="attachment" style="font-size: 11px;">
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary"
                                style="font-size: 13px; font-weight: 600; padding: 8px 20px; display: inline-flex; align-items: center; gap: 6px;">
                                <span>🚀</span> Paylaş ve Paydaşlara Bildir
                            </button>
                        </div>
                    </form>
                </div>

                <!-- TARTIŞMALAR & YORUMLAR AKIŞI -->
                @forelse($project->discussions as $disc)
                    @php
                        $canModifyDisc = (auth()->id() === $disc->user_id)
                            || auth()->user()->isSuperAdmin()
                            || auth()->user()->isAdmin()
                            || ($project->manager_id === auth()->id());
                        $isEdited = $disc->updated_at && $disc->updated_at->diffInSeconds($disc->created_at) > 10;
                    @endphp
                    <div style="border-bottom: 1px solid #f1f5f9; padding-bottom: 16px; margin-bottom: 16px; display: flex; gap: 14px;"
                        id="discussion-row-{{ $disc->id }}">
                        <div
                            style="width: 38px; height: 38px; border-radius: 50%; background: #3b82f6; color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0;">
                            {{ mb_substr($disc->user->name ?? 'U', 0, 1) }}
                        </div>
                        <div style="flex: 1;">
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; flex-wrap: wrap; gap: 6px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <strong
                                        style="color: #0f172a; font-size: 13.5px;">{{ $disc->user->name ?? 'Kullanıcı' }}</strong>
                                    <span
                                        style="font-size: 11px; color: #64748b; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">
                                        {{ $disc->user->bolum?->name ?? 'Merkez' }} • {{ $disc->user->unvan ?? 'Ekip Üyesi' }}
                                    </span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    @if($isEdited)
                                        <span
                                            style="font-size: 10.5px; color: #0284c7; background: #e0f2fe; padding: 2px 6px; border-radius: 4px; font-weight: 600;"
                                            title="Düzenlenme Tarihi: {{ $disc->updated_at->format('d.m.Y H:i') }}">
                                            ✎ Düzenlendi
                                        </span>
                                    @endif
                                    <span style="font-size: 11px; color: #94a3b8;">
                                        {{ $disc->created_at->diffForHumans() }} ({{ $disc->created_at->format('d.m.Y H:i') }})
                                    </span>

                                    @if($canModifyDisc)
                                        <div style="display: inline-flex; align-items: center; gap: 4px; margin-left: 6px;">
                                            <button type="button" onclick="toggleEditDiscussion({{ $disc->id }})"
                                                class="btn btn-xs btn-outline-secondary"
                                                style="padding: 2px 8px; font-size: 11px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;"
                                                title="Mesajı Düzenle">
                                                <span>✏️</span> Düzenle
                                            </button>
                                            <form action="{{ route('projects.discussions.delete', [$project->id, $disc->id]) }}"
                                                method="POST" style="display: inline;"
                                                onsubmit="return confirm('Bu tartışma mesajını silmek istediğinize emin misiniz? Bu işlem tüm içeriğiyle Denetim Kütüğüne (Audit Log) kaydedilecektir.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-outline-danger"
                                                    style="padding: 2px 8px; font-size: 11px; border-radius: 4px; display: inline-flex; align-items: center; gap: 3px;"
                                                    title="Mesajı Sil">
                                                    <span>🗑️</span> Sil
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- NORMAL MESAJ GÖRÜNÜMÜ -->
                            <div id="disc-content-{{ $disc->id }}"
                                style="font-size: 13px; color: #1e293b; line-height: 1.6; white-space: pre-line; background: #f8fafc; padding: 12px 14px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                {{ $disc->message }}
                            </div>

                            <!-- INLINE DÜZENLEME FORMU -->
                            @if($canModifyDisc)
                                <div id="disc-edit-box-{{ $disc->id }}"
                                    style="display: none; background: #f0fdf4; padding: 14px; border-radius: 8px; border: 1px solid #86efac; margin-top: 6px;">
                                    <form action="{{ route('projects.discussions.update', [$project->id, $disc->id]) }}"
                                        method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div
                                            style="font-size: 12px; font-weight: 700; color: #166534; margin-bottom: 6px; display: flex; align-items: center; gap: 5px;">
                                            <span>✏️</span> Mesajı Düzenle (Yapılan değişiklik denetim kütüğüne kaydedilecektir)
                                        </div>
                                        <textarea name="message" rows="3" class="form-control mb-2" required
                                            style="font-size: 13px; line-height: 1.5; background: #ffffff;">{{ $disc->message }}</textarea>
                                        <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                            <button type="button" onclick="toggleEditDiscussion({{ $disc->id }})"
                                                class="btn btn-secondary btn-sm"
                                                style="font-size: 11.5px; padding: 4px 12px;">Vazgeç</button>
                                            <button type="submit" class="btn btn-success btn-sm"
                                                style="font-size: 11.5px; padding: 4px 14px; font-weight: 600;">✓ Değişiklikleri
                                                Kaydet</button>
                                        </div>
                                    </form>
                                </div>
                            @endif

                            @if($disc->attachment_path)
                                <div style="margin-top: 6px;">
                                    <a href="{{ asset('storage/' . $disc->attachment_path) }}" target="_blank"
                                        style="font-size: 11.5px; font-weight: 600; color: #2563eb; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                        <span>📎</span> İliştirilmiş Belgeyi İncele ↗
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 40px; color: #64748b; font-size: 13px;">
                        <div style="font-size: 32px; margin-bottom: 8px;">💬</div>
                        <div style="font-weight: 700; color: #0f172a; margin-bottom: 4px;">Henüz Tartışma Mesajı Eklenmedi</div>
                        <div>Proje paydaşlarıyla fikir alışverişi yapmak ve saha notu bırakmak için yukarıdaki formu
                            kullanabilirsiniz.</div>
                    </div>
                @endforelse

            </div>
        </div>
    </div>

    <!-- TAB 3: RACI PAYDAŞLAR -->
    <div id="tab-raci" class="tab-content" style="display: none;">
        @php
            $canManageObservers = auth()->user()->isSuperAdmin()
                || auth()->user()->isAdmin()
                || $project->manager_id === auth()->id()
                || $project->sponsor_id === auth()->id()
                || $project->created_by === auth()->id();
        @endphp

        <!-- PROJE EKİBİ & RACI TABLOSU -->
        <div class="card mb-4" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
            <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 20px;">
                <span class="card-title" style="margin: 0; font-size: 15px; font-weight: 700; color: #0f172a;">Proje
                    Paydaşları ve RACI Sorumluluk Modeli</span>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Paydaş Adı</th>
                            <th>Proje Rolü</th>
                            <th>RACI Seviyesi</th>
                            <th>Sorumluluk Açıklaması</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($project->stakeholders as $stk)
                            <tr>
                                <td>
                                    <strong>{{ $stk->user->name }}</strong>
                                    <div style="font-size: 11px; color: var(--text-muted);">{{ $stk->user->unvan ?? 'Uzman' }}
                                    </div>
                                </td>
                                <td>
                                    <span
                                        style="font-weight: 700; color: var(--primary);">{{ strtoupper($stk->role_type) }}</span>
                                </td>
                                <td>
                                    @php
                                        $raciDesc = [
                                            'R' => ['text' => 'R (Responsible - İcracı)', 'color' => '#16a34a'],
                                            'A' => ['text' => 'A (Accountable - Karar Verici)', 'color' => '#2563eb'],
                                            'C' => ['text' => 'C (Consulted - Denetleyici)', 'color' => '#dc2626'],
                                            'I' => ['text' => 'I (Informed - Bilgilendirilen)', 'color' => '#64748b'],
                                        ];
                                    @endphp
                                    <span style="font-weight: 700; color: {{ $raciDesc[$stk->raci_role]['color'] ?? '#333' }};">
                                        {{ $raciDesc[$stk->raci_role]['text'] ?? $stk->raci_role }}
                                    </span>
                                </td>
                                <td style="font-size: 12px;">{{ $stk->notes ?? 'Standart rol sorumluluğu.' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 24px;">Henüz paydaş atanmadı.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PROJE GÖZLEMCİLERİ & DIŞ PAYDAŞLAR YÖNETİMİ -->
        <div class="card" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; margin-top: 24px;">
            <div class="card-header"
                style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; padding: 16px 20px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 18px;">👁️</span>
                    <div>
                        <span class="card-title" style="margin: 0; font-size: 15px; font-weight: 700; color: #0f172a;">
                            Proje Gözlemcileri (İzleyiciler & Dış Paydaşlar)
                        </span>
                        <div class="text-xs text-muted">Salt okunur takipçiler, dış denetçiler ve paydaşların anlık erişim
                            kontrolü.</div>
                    </div>
                </div>
                @if($canManageObservers)
                    <button type="button" class="btn btn-primary btn-sm" onclick="toggleAddObserverModal()"
                        style="font-weight: 600; padding: 6px 14px; border-radius: 6px;">
                        + Yeni Gözlemci Ekle
                    </button>
                @endif
            </div>

            <div class="card-body" style="padding: 20px;">
                <div class="pastel-blue-helper-box mb-3">
                    <span class="helper-box-icon">ℹ️</span>
                    <div class="helper-box-content">
                        <strong>Erişim ve İzolasyon Kuralı:</strong> Harici gözlemciler yalnızca bu projeye erişebilir.
                        Proje yöneticisi, sponsor veya başlatan kişi gözlemciyi listeden çıkardığı anda kullanıcının sistem
                        oturumu sonlandırılır, şifre sıfırlama ve oturum açma yetkisi derhal engellenir.
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="app-table">
                        <thead>
                            <tr>
                                <th>Gözlemci Adı & E-Posta</th>
                                <th>Tür</th>
                                <th>Bağlı Kurum / Not</th>
                                <th>Erişim Durumu</th>
                                @if($canManageObservers)
                                    <th style="text-align: right;">İşlem</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $observerStakeholders = $project->stakeholders->where('role_type', 'observer');
                            @endphp
                            @forelse($observerStakeholders as $stk)
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: #1e293b;">{{ $stk->user->name }}</div>
                                        <div style="font-size: 11.5px; color: #64748b;">{{ $stk->user->email }}</div>
                                    </td>
                                    <td>
                                        @if($stk->user->isExternal())
                                            <span class="badge-observer-external">🌐 Harici Gözlemci</span>
                                        @else
                                            <span class="badge-observer-internal">🏢 Kurum İçi Personel</span>
                                        @endif
                                    </td>
                                    <td style="font-size: 12px; color: #475569;">
                                        {{ $stk->notes ?? ($stk->user->unvan ?? 'Gözlemci') }}
                                    </td>
                                    <td>
                                        @if($stk->user->is_active)
                                            <span class="badge"
                                                style="background: #dcfce7; color: #166534; font-size: 11px; padding: 3px 8px; border-radius: 4px;">
                                                ✓ Aktif İzleme
                                            </span>
                                        @else
                                            <span class="badge"
                                                style="background: #fee2e2; color: #991b1b; font-size: 11px; padding: 3px 8px; border-radius: 4px;">
                                                ⛔ Erişimi Askıda
                                            </span>
                                        @endif
                                    </td>
                                    @if($canManageObservers)
                                        <td style="text-align: right;">
                                            <form action="{{ route('projects.remove_observer', [$project->id, $stk->id]) }}"
                                                method="POST"
                                                onsubmit="return confirm('Bu kullanıcıyı gözlemcilikten çıkarmak istediğinize emin misiniz?\nHarici kullanıcı ise sistem erişimi ve giriş yetkisi tamamen engellenecektir.')"
                                                style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-xs"
                                                    style="padding: 4px 10px; font-size: 11.5px; font-weight: 600; border-radius: 6px;">
                                                    🚫 Erişimi Kaldır
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $canManageObservers ? 5 : 4 }}"
                                        style="text-align: center; padding: 20px; color: #64748b; font-size: 13px;">
                                        Henüz bu projeye eklenmiş bir gözlemci bulunmamaktadır.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- YENİ GÖZLEMCİ EKLEME FORMU (COLLAPSIBLE) -->
                @if($canManageObservers)
                    <div id="addObserverCollapse"
                        style="display: none; margin-top: 20px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; padding: 18px;">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
                            <strong style="color: #0f172a; font-size: 13.5px;">➕ Projeye Yeni Gözlemci Ekle</strong>
                            <button type="button" class="btn btn-link btn-xs text-muted" onclick="toggleAddObserverModal()">✕
                                Kapat</button>
                        </div>

                        <form action="{{ route('projects.add_observer', $project->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label font-weight-600 mb-1">Gözlemci Kaynak Tipi</label>
                                <div style="display: flex; gap: 20px;">
                                    <label
                                        style="font-size: 13px; color: #1e293b; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                        <input type="radio" name="observer_type" value="internal" checked
                                            onchange="toggleObsType('internal')">
                                        <span>🏢 Kurum İçi Personel</span>
                                    </label>
                                    <label
                                        style="font-size: 13px; color: #1e293b; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                        <input type="radio" name="observer_type" value="external"
                                            onchange="toggleObsType('external')">
                                        <span>🌐 Harici E-Posta ile (Dış Paydaş)</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Kurum İçi Seçim Alanı -->
                            <div id="internalObsFields">
                                <div class="form-group mb-3">
                                    <label class="form-label font-weight-600">Personel Seçiniz</label>
                                    <select name="user_id" class="form-control" id="internalObsUserSelect">
                                        <option value="">-- Personel Seçiniz --</option>
                                        @foreach($users as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->bolum?->name ?? 'Merkez' }} -
                                                {{ $u->unvan ?? 'Personel' }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Harici E-posta Seçim Alanı -->
                            <div id="externalObsFields" style="display: none;">
                                <div class="grid-3 mb-3">
                                    <div class="form-group mb-0">
                                        <label class="form-label font-weight-600">E-Posta Adresi <span
                                                class="text-danger">*</span></label>
                                        <input type="email" name="email" id="externalObsEmail" class="form-control"
                                            placeholder="ornek@dispaydas.com">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="form-label font-weight-600">Ad Soyad (Opsiyonel)</label>
                                        <input type="text" name="name" class="form-control" placeholder="Örn: Mehmet Can">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="form-label font-weight-600">Kurum / Unvan / Not</label>
                                        <input type="text" name="company" class="form-control"
                                            placeholder="Örn: Denetim Firması">
                                    </div>
                                </div>
                                <div class="pastel-blue-helper-box subtle mb-3" style="font-size: 12px;">
                                    <span class="helper-box-icon">💡</span>
                                    <div class="helper-box-content">
                                        Bu kişiye sistem otomatik güvenli şifre atayacak ve eklendiğinde ekranda şifresi size
                                        gösterilecektir.
                                    </div>
                                </div>
                            </div>

                            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                                <button type="button" class="btn btn-secondary btn-sm"
                                    onclick="toggleAddObserverModal()">İptal</button>
                                <button type="submit" class="btn btn-primary btn-sm" style="font-weight: 600;">Kaydet ve
                                    Gözlemci Ekle</button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <!-- TAB 4: 5x5 RİSK MATRİSİ -->
    <div id="tab-risks" class="tab-content" style="display: none;">
        <div class="card">
            <div class="card-header">
                <span class="card-title">5x5 Risk Değerlendirme ve Azaltma Stratejisi</span>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Risk Tanımı</th>
                            <th>Kategori</th>
                            <th>Olasılık (1-5)</th>
                            <th>Etki (1-5)</th>
                            <th>Risk Skoru</th>
                            <th>Ciddiyet</th>
                            <th>Sorumlu</th>
                            <th>Durum</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($project->risks as $r)
                            <tr>
                                <td><strong>{{ $r->title }}</strong></td>
                                <td style="font-size: 12px; color: var(--text-muted);">{{ ucfirst($r->category) }}</td>
                                <td style="text-align: center; font-weight: 700;">{{ $r->probability }}</td>
                                <td style="text-align: center; font-weight: 700;">{{ $r->impact }}</td>
                                <td
                                    style="text-align: center; font-weight: 800; font-size: 15px; color: {{ $r->risk_score >= 15 ? 'var(--danger)' : ($r->risk_score >= 8 ? 'var(--warning)' : 'var(--success)') }};">
                                    {{ $r->risk_score }}
                                </td>
                                <td>
                                    <span
                                        class="status-badge {{ $r->severity === 'critical' || $r->severity === 'high' ? 'badge-danger' : ($r->severity === 'medium' ? 'badge-warning' : 'badge-success') }}">
                                        {{ strtoupper($r->severity) }}
                                    </span>
                                </td>
                                <td>{{ $r->owner?->name ?? 'Belirlenmedi' }}</td>
                                <td><x-status-badge :status="$r->status" /></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 24px;">Tanımlı risk bulunmuyor.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 5: YATIRIM BÜTÇESİ & KALEM KALEM MALİYET KIRILIMI -->
    <div id="tab-bom" class="tab-content" style="display: none;">
        @php
            $items = $project->budgetItems;
            $itemsSum = $items->sum('planned_amount');
            $effectiveBudget = $itemsSum > 0 ? $itemsSum : $project->planned_budget;
            $totalActual = $items->sum('actual_spent');
            $remainingBudget = $effectiveBudget - $totalActual;
            $spendRatio = $effectiveBudget > 0 ? round(($totalActual / $effectiveBudget) * 100, 1) : 0;
            $currSymbol = ($project->currency === 'USD') ? '$' : (($project->currency === 'EUR') ? '€' : '₺');
            $byCategory = $items->groupBy('category');
        @endphp

        <!-- 1. BÜTÇE & MALİYET ÖZET KARTLARI -->
        <div class="grid-4 mb-4" style="gap: 14px;">
            <div class="stat-card"
                style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; border-left: 4px solid #0284c7;">
                <div class="stat-card-title"
                    style="font-size: 11.5px; color: #64748b; font-weight: 600; text-transform: uppercase;">Toplam Planlanan
                    Bütçe</div>
                <div class="stat-card-value" style="font-size: 20px; font-weight: 800; color: #0284c7; margin-top: 4px;">
                    {{ $currSymbol }} {{ number_format($effectiveBudget, 2, ',', '.') }}
                </div>
                <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">
                    @if($items->count() > 0)
                        {{ $items->count() }} alt yatırım kaleminden oluşuyor
                    @else
                        Tek kalem genel bütçe girişi
                    @endif
                </div>
            </div>

            <div class="stat-card"
                style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; border-left: 4px solid #f59e0b;">
                <div class="stat-card-title"
                    style="font-size: 11.5px; color: #64748b; font-weight: 600; text-transform: uppercase;">Fiili Harcanan
                    Tutar</div>
                <div class="stat-card-value" style="font-size: 20px; font-weight: 800; color: #d97706; margin-top: 4px;">
                    {{ $currSymbol }} {{ number_format($totalActual, 2, ',', '.') }}
                </div>
                <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">
                    Gerçekleşen harcama toplamı
                </div>
            </div>

            <div class="stat-card"
                style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; border-left: 4px solid {{ $remainingBudget >= 0 ? '#10b981' : '#ef4444' }};">
                <div class="stat-card-title"
                    style="font-size: 11.5px; color: #64748b; font-weight: 600; text-transform: uppercase;">Kalan
                    Kullanılabilir Bütçe</div>
                <div class="stat-card-value"
                    style="font-size: 20px; font-weight: 800; color: {{ $remainingBudget >= 0 ? '#15803d' : '#b91c1c' }}; margin-top: 4px;">
                    {{ $currSymbol }} {{ number_format($remainingBudget, 2, ',', '.') }}
                </div>
                <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">
                    {{ $remainingBudget >= 0 ? 'Bütçe limitleri dahilinde' : 'Bütçe aşımı riski!' }}
                </div>
            </div>

            <div class="stat-card"
                style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; border-left: 4px solid #6366f1;">
                <div class="stat-card-title"
                    style="font-size: 11.5px; color: #64748b; font-weight: 600; text-transform: uppercase;">Harcama &
                    Gerçekleşme Oranı</div>
                <div class="stat-card-value" style="font-size: 20px; font-weight: 800; color: #4f46e5; margin-top: 4px;">
                    %{{ $spendRatio }}
                </div>
                <div style="height: 6px; background: #e2e8f0; border-radius: 3px; margin-top: 8px; overflow: hidden;">
                    <div
                        style="width: {{ min(100, $spendRatio) }}%; height: 100%; background: {{ $spendRatio > 100 ? '#ef4444' : '#6366f1' }}; border-radius: 3px;">
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. KATEGORİ BAZLI HARCAMA ÖZETLERİ (KATEGORİ KATEGORİ KIRILIM) -->
        @if($byCategory->count() > 0)
            <div class="card mb-4"
                style="border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header"
                    style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 12px 18px; display: flex; justify-content: space-between; align-items: center;">
                    <span class="card-title"
                        style="font-size: 13.5px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                        <span>📊</span> Yatırım Harcama Kategorileri Kırılımı (KPI Radar Beslemesi)
                    </span>
                    <span class="badge"
                        style="background: #e0f2fe; color: #0369a1; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px;">
                        {{ $byCategory->count() }} Harcama Kategorisi
                    </span>
                </div>
                <div class="card-body" style="padding: 16px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px;">
                        @foreach($byCategory as $catName => $catItems)
                            @php
                                $catPlanned = $catItems->sum('planned_amount');
                                $catActual = $catItems->sum('actual_spent');
                                $catRatio = $catPlanned > 0 ? round(($catActual / $catPlanned) * 100, 1) : 0;
                            @endphp
                            <div
                                style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                                <div
                                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                    <strong style="font-size: 12.5px; color: #0f172a;">{{ $catName }}</strong>
                                    <span class="badge"
                                        style="background: #f1f5f9; color: #475569; font-size: 10.5px;">{{ $catItems->count() }}
                                        Kalem</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px;">
                                    <span style="color: #64748b;">Planlanan:</span>
                                    <strong style="color: #0284c7;">{{ $currSymbol }}
                                        {{ number_format($catPlanned, 2, ',', '.') }}</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px;">
                                    <span style="color: #64748b;">Harcanan:</span>
                                    <strong style="color: {{ $catActual > $catPlanned ? '#b91c1c' : '#15803d' }};">{{ $currSymbol }}
                                        {{ number_format($catActual, 2, ',', '.') }} (%{{ $catRatio }})</strong>
                                </div>
                                <div style="height: 5px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                                    <div
                                        style="width: {{ min(100, $catRatio) }}%; height: 100%; background: #0284c7; border-radius: 3px;">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- 3. KALEM KALEM YATIRIM BÜTÇESİ LİSTESİ TABLOSU -->
        <div class="card mb-4"
            style="border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header"
                style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 12px 18px; display: flex; justify-content: space-between; align-items: center;">
                <span class="card-title"
                    style="font-size: 13.5px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                    <span>📑</span> Kalem Kalem Bütçe Dağılım Listesi
                </span>
                <span style="font-size: 11.5px; color: #64748b;">Raporlanabilir Maliyet Kalemleri</span>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th style="width: 22%;">Harcama Kategorisi</th>
                            <th style="width: 32%;">Bütçe Kalemi & Açıklaması</th>
                            <th style="width: 16%; text-align: right;">Planlanan Tutar</th>
                            <th style="width: 16%; text-align: right;">Fiili Harcama</th>
                            <th style="width: 14%; text-align: right;">Kalan Bakiye</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($project->budgetItems as $item)
                            @php
                                $itemDiff = $item->planned_amount - $item->actual_spent;
                            @endphp
                            <tr>
                                <td>
                                    <span class="badge"
                                        style="background: #eff6ff; color: #1e40af; font-size: 11px; padding: 3px 8px; border-radius: 6px; font-weight: 600;">
                                        {{ $item->category }}
                                    </span>
                                </td>
                                <td>
                                    <strong style="color: #0f172a; font-size: 12.5px;">{{ $item->title }}</strong>
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #0284c7;">
                                    {{ $currSymbol }} {{ number_format($item->planned_amount, 2, ',', '.') }}
                                </td>
                                <td style="text-align: right; font-weight: 600; color: #d97706;">
                                    {{ $currSymbol }} {{ number_format($item->actual_spent, 2, ',', '.') }}
                                </td>
                                <td
                                    style="text-align: right; font-weight: 700; color: {{ $itemDiff >= 0 ? '#15803d' : '#b91c1c' }};">
                                    {{ $currSymbol }} {{ number_format($itemDiff, 2, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 24px; color: #64748b;">
                                    Bu projede kalem kalem bütçe kırılımı girilmemiştir. Genel planlanan yatırım bütçesi:
                                    <strong>{{ $currSymbol }}
                                        {{ number_format($project->planned_budget, 2, ',', '.') }}</strong>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($project->budgetItems->count() > 0)
                        <tfoot>
                            <tr style="background: #f8fafc; font-weight: 800;">
                                <td colspan="2" style="padding: 12px 16px; font-size: 13px; color: #0f172a;">KÜMÜLATİF TOPLAM:
                                </td>
                                <td style="text-align: right; padding: 12px 16px; font-size: 13.5px; color: #0284c7;">
                                    {{ $currSymbol }} {{ number_format($itemsSum, 2, ',', '.') }}
                                </td>
                                <td style="text-align: right; padding: 12px 16px; font-size: 13.5px; color: #d97706;">
                                    {{ $currSymbol }} {{ number_format($totalActual, 2, ',', '.') }}
                                </td>
                                <td
                                    style="text-align: right; padding: 12px 16px; font-size: 13.5px; color: {{ $remainingBudget >= 0 ? '#15803d' : '#b91c1c' }};">
                                    {{ $currSymbol }} {{ number_format($remainingBudget, 2, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <!-- 4. BOM (ENDÜSTRİYEL MALZEME LİSTESİ) KARTI -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">BOM (Endüstriyel Malzeme Listesi) ve Harcama Kalemleri</span>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Kalem Adı</th>
                            <th>Kod</th>
                            <th>Kategori</th>
                            <th>Miktar</th>
                            <th>Birim Fiyat</th>
                            <th>Toplam Fiyat</th>
                            <th>Tedarikçi</th>
                            <th>Durum</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($project->boms as $b)
                            <tr>
                                <td><strong>{{ $b->item_name }}</strong></td>
                                <td style="font-size: 11px; color: var(--text-muted);">{{ $b->item_code ?? '-' }}</td>
                                <td>
                                    @php $catMeta = $b->getCategoryMeta(); @endphp
                                    <span class="badge"
                                        style="background: {{ $catMeta['bg'] }}; color: {{ $catMeta['color'] }}; border: 1px solid {{ $catMeta['border'] }}; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 5px; display: inline-flex; align-items: center; gap: 4px;">
                                        <span>{{ $catMeta['icon'] }}</span>
                                        <span>{{ $catMeta['name'] }}</span>
                                    </span>
                                </td>
                                <td>{{ number_format($b->quantity, 0) }} {{ $b->unit }}</td>
                                <td>₺{{ number_format($b->unit_price, 2) }}</td>
                                <td style="font-weight: 700; color: var(--navy);">₺{{ number_format($b->total_price, 2) }}</td>
                                <td>{{ $b->supplier ?? '-' }}</td>
                                <td><x-status-badge :status="$b->status" /></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 24px;">BOM kalemi eklenmedi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 6: TOPLANTILAR -->
    <div id="tab-meetings" class="tab-content" style="display: none;">
        <div class="card">
            <div class="card-header"
                style="display: flex; justify-content: space-between; align-items: center; background: #ffffff; padding: 14px 20px; border-bottom: 1px solid #e2e8f0;">
                <span class="card-title" style="margin: 0; font-size: 15px; font-weight: 700; color: #0f172a;">Projeye Bağlı
                    Toplantılar ve Alınan Kararlar</span>
                @if(!auth()->user()->isYonetim() && (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->hasPermission('meeting.create') || auth()->user()->hasPermission('meeting.manage') || ($canManage ?? false) || auth()->user()->canManageProject($project)))
                    <a href="{{ route('meetings.index', ['project_id' => $project->id]) }}" class="btn btn-sm btn-primary"
                        style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700; text-decoration: none; padding: 6px 14px; font-size: 12.5px;">
                        <span>📅</span> + Bu Projede Yeni Toplantı Planla
                    </a>
                @endif
            </div>
            <div class="card-body">
                @forelse($project->meetings as $m)
                    <div
                        style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 16px; margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <h4 style="font-size: 15px; font-weight: 700; color: var(--navy);">{{ $m->title }}</h4>
                            <span style="font-size: 12px; font-weight: 600; color: var(--primary);">
                                📅 {{ $m->meeting_date->format('d.m.Y H:i') }} • {{ $m->location ?? 'Online' }}
                            </span>
                        </div>
                        @if(!empty($m->formatted_agenda_items))
                            <div style="margin-bottom: 10px; font-size: 12.5px;">
                                <div
                                    style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">
                                    Toplantı Gündemi:</div>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    @foreach($m->formatted_agenda_items as $item)
                                        <div style="display: flex; gap: 6px; align-items: baseline;">
                                            <span style="font-weight: 700; color: #2563eb;">{{ $item['number'] }}.</span>
                                            <span>{{ $item['title'] }}</span>
                                            @if($item['date'])
                                                <span
                                                    style="font-size: 10.5px; background: #fef3c7; color: #92400e; padding: 1px 5px; border-radius: 3px;">📅
                                                    {{ $item['date'] }}</span>
                                            @endif
                                            @if($item['responsible'])
                                                <span
                                                    style="font-size: 10.5px; background: #e0f2fe; color: #0369a1; padding: 1px 5px; border-radius: 3px;">👤
                                                    {{ $item['responsible'] }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @elseif($m->agenda)
                            <p style="font-size: 13px; color: var(--text-main); margin-bottom: 12px; white-space: pre-line;">
                                {{ $m->agenda }}</p>
                        @endif

                        @if($m->minutes)
                            <div
                                style="background: #ffffff; border: 1px solid #bbf7d0; border-radius: 6px; padding: 12px 14px; margin-bottom: 12px;">
                                <div
                                    style="font-size: 11px; font-weight: 700; color: #166534; text-transform: uppercase; margin-bottom: 4px; display: flex; justify-content: space-between; align-items: center;">
                                    <span>📝 Resmi Toplantı Tutanağı & Saha Notları:</span>
                                    <a href="{{ route('meetings.show', $m->id) }}" target="_blank" rel="noopener noreferrer"
                                        style="font-size: 11px; color: #1d4ed8; text-decoration: none; font-weight: 600;">
                                        Toplantı Sayfasını Aç ↗
                                    </a>
                                </div>
                                <div style="font-size: 12.5px; color: #1e293b; line-height: 1.6;">
                                    {!! $m->minutes !!}
                                </div>
                            </div>
                        @endif

                        <!-- KARARDAN GÖREVE DÖNÜŞTÜRME ALANI -->
                        <div
                            style="background: #fff; border: 1px solid var(--border-color); border-radius: 6px; padding: 12px;">
                            <div style="font-size: 12px; font-weight: 700; color: var(--navy); margin-bottom: 6px;">⚡ Alınan
                                Kararı Proje Görevine Dönüştür:</div>
                            <form action="{{ route('meetings.convert_task', $m->id) }}" method="POST"
                                style="display: flex; gap: 10px;">
                                @csrf
                                <input type="text" name="decision_text" class="form-control"
                                    placeholder="Karar metnini yazınız..." required style="flex: 2;">
                                <select name="assigned_to" class="form-control" style="flex: 1;">
                                    <option value="">-- Sorumlu Ata --</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                                    + Göreve Dönüştür
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 30px; color: var(--text-muted);">
                        Bu projeye bağlı henüz toplantı kaydedilmemiş.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- TAB 8: DOSYA & ENVANTER KASASI (FILES) -->
    <div id="tab-files" class="tab-content" style="display: none;">
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header"
                style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
                <span class="card-title"
                    style="font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <span>📁</span> Proje Dosya, Belge & Envanter Kasası
                </span>
                <span class="badge" style="background: #eff6ff; color: #1e40af; font-weight: 700;">
                    {{ $project->files->count() }} Belge / Dosya
                </span>
            </div>

            <div class="card-body" style="padding: 20px;">
                <!-- YENİ DOSYA YÜKLEME FORMU -->
                <div
                    style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; margin-bottom: 24px;">
                    <h4
                        style="margin: 0 0 12px 0; font-size: 13.5px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                        <span>📤</span> Yeni Belge / Envanter Yükle ve Paydaşlara Bildir
                    </h4>
                    <form action="{{ route('projects.upload_file', $project->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="grid-3" style="gap: 14px; margin-bottom: 12px;">
                            <div>
                                <label class="form-label" style="font-size: 11.5px; font-weight: 600;">Dosya Seç (Maks. 25
                                    MB) *</label>
                                <input type="file" name="file" class="form-control" required
                                    style="font-size: 12px; background: #fff;">
                            </div>
                            <div>
                                <label class="form-label" style="font-size: 11.5px; font-weight: 600;">Belge Kategorisi
                                    *</label>
                                <select name="category" class="form-control" required
                                    style="font-size: 12px; background: #fff;">
                                    <option value="Teknik Şartname">Teknik Şartname</option>
                                    <option value="İSG Saha İzin Belgesi">İSG Saha İzin Belgesi</option>
                                    <option value="BOM / Malzeme Listesi">BOM / Malzeme Listesi</option>
                                    <option value="Test & Devreye Alma Raporu">Test & Devreye Alma Raporu</option>
                                    <option value="Sözleşme & Hukuki">Sözleşme & Hukuki Belge</option>
                                    <option value="Saha Fotoğrafı & Tutanak">Saha Fotoğrafı & Tutanak</option>
                                    <option value="Diğer">Diğer</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label" style="font-size: 11.5px; font-weight: 600;">İlgili Görev / Adım
                                    (Opsiyonel)</label>
                                <select name="task_id" class="form-control" style="font-size: 12px; background: #fff;">
                                    <option value="">-- Proje Geneli (Bağlantısız) --</option>
                                    @foreach($project->tasks as $ptask)
                                        <option value="{{ $ptask->id }}">{{ $ptask->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- ETİKETLER GİRİŞ ALANI -->
                        <div style="margin-bottom: 14px;">
                            <label class="form-label"
                                style="font-size: 11.5px; font-weight: 600; display: flex; align-items: center; justify-content: space-between;">
                                <span>🏷️ Dosya Etiketleri (Aramayı ve Gruplamayı Kolaylaştırmak İçin)</span>
                                <span style="font-size: 11px; color: #64748b; font-weight: 400;">Örn: #Statik, #İSG,
                                    #Sözleşme, #Rapor (virgülle ayırın)</span>
                            </label>
                            <input type="text" name="tags" class="form-control"
                                placeholder="#Statik_Hesap, #İSG_Onayı, #Sözleşme"
                                style="font-size: 12px; background: #fff;">
                        </div>

                        <div
                            style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                            <div style="display: flex; align-items: center; gap: 14px;">
                                <label
                                    style="font-size: 12px; color: #475569; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                                    <input type="radio" name="notify_scope" value="all" checked>
                                    <span>Tüm Proje Paydaşlarına & Gözlemcilere Bildir</span>
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary"
                                style="font-size: 12.5px; font-weight: 600; padding: 7px 18px; display: inline-flex; align-items: center; gap: 6px;">
                                <span>🚀</span> Belgeyi Yükle & Kaydet
                            </button>
                        </div>
                    </form>
                </div>

                <!-- YÜKLENEN DOSYALAR & CANLI ETİKET FİLTRESİ -->
                @php
                    $projectFileTags = [];
                    foreach ($project->files as $f) {
                        foreach ($f->tags_array as $t) {
                            $t = trim($t);
                            if ($t !== '') {
                                $projectFileTags[$t] = ($projectFileTags[$t] ?? 0) + 1;
                            }
                        }
                    }
                @endphp

                @if($project->files->count() > 0)
                    <!-- Canlı Arama ve Etiket Hapları Barı -->
                    <div
                        style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; padding: 12px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <div style="position: relative; min-width: 250px; flex: 1;">
                            <span
                                style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 13px; color: #64748b;">🔍</span>
                            <input type="text" id="projectFileSearchInput" oninput="filterProjectFilesTable()"
                                placeholder="Dosya adı veya #etiket ara..." class="form-control"
                                style="padding-left: 32px; font-size: 12px; height: 34px; background: #fff;">
                        </div>

                        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;" id="fileTagPillsWrap">
                            <button type="button" class="btn btn-xs" onclick="filterByTagPill('')" id="pill-all"
                                style="font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; border: 1px solid #0284c7; background: #0284c7; color: #fff; cursor: pointer;">
                                Tümü ({{ $project->files->count() }})
                            </button>
                            @foreach($projectFileTags as $tagName => $tagCount)
                                <button type="button" class="btn btn-xs project-tag-pill"
                                    onclick="filterByTagPill('{{ addslashes($tagName) }}')" data-tag="{{ $tagName }}"
                                    style="font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 20px; border: 1px solid #cbd5e1; background: #ffffff; color: #334155; cursor: pointer; transition: all 0.15s ease;">
                                    #{{ $tagName }} <span style="font-size: 9.5px; opacity: 0.75;">({{ $tagCount }})</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <table class="app-table" id="projectFilesTable"
                        style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr
                                style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">
                                <th style="padding: 10px 14px;">Dosya & Etiketler</th>
                                <th style="padding: 10px 14px;">Kategori</th>
                                <th style="padding: 10px 14px;">Boyut</th>
                                <th style="padding: 10px 14px;">Revizyon</th>
                                <th style="padding: 10px 14px;">İlişkili Görev</th>
                                <th style="padding: 10px 14px;">Yükleyen</th>
                                <th style="padding: 10px 14px;">Tarih</th>
                                <th style="padding: 10px 14px; text-align: center;">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($project->files as $file)
                                <tr class="project-file-row" data-filename="{{ strtolower($file->original_name) }}"
                                    data-tags="{{ strtolower(implode(' ', $file->tags_array)) }}"
                                    style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 12px 14px;">
                                        <div
                                            style="font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                                            <span>📄</span>
                                            <span>{{ $file->original_name }}</span>
                                        </div>
                                        <div
                                            style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap; margin-top: 6px;">
                                            @forelse($file->tags_array as $ft)
                                                <span class="badge" onclick="filterByTagPill('{{ addslashes($ft) }}')"
                                                    style="background: #e0f2fe; color: #0369a1; font-size: 10.5px; font-weight: 600; padding: 2px 6px; border-radius: 4px; cursor: pointer;"
                                                    title="Bu etikete göre filtrele">#{{ $ft }}</span>
                                            @empty
                                                <span style="font-size: 10.5px; color: #94a3b8; font-style: italic;">Etiket yok</span>
                                            @endforelse
                                            <button type="button"
                                                onclick="editFileTagsModal({{ $project->id }}, {{ $file->id }}, '{{ addslashes(implode(', ', $file->tags_array)) }}')"
                                                style="border: 1px dashed #cbd5e1; background: #fff; border-radius: 4px; padding: 1px 6px; font-size: 10px; color: #64748b; cursor: pointer;"
                                                title="Etiketleri Düzenle">✏️ Etiket Düzenle</button>
                                        </div>
                                    </td>
                                    <td style="padding: 12px 14px;">
                                        <span class="badge"
                                            style="background: #eff6ff; color: #1e40af; font-size: 11px; font-weight: 700;">
                                            {{ $file->category }}
                                        </span>
                                    </td>
                                    <td style="padding: 12px 14px; font-size: 12px; color: #64748b;">
                                        {{ number_format($file->file_size / 1024, 1) }} KB
                                    </td>
                                    <td style="padding: 12px 14px; font-size: 11.5px; font-weight: 600; color: #475569;">
                                        Rev {{ $file->revision }}
                                    </td>
                                    <td style="padding: 12px 14px; font-size: 12px; color: #64748b;">
                                        {{ $file->task?->title ?? '-' }}
                                    </td>
                                    <td style="padding: 12px 14px;">
                                        <span style="font-weight: 600; font-size: 12px; color: #334155;">
                                            {{ $file->uploader?->name ?? 'Sistem' }}
                                        </span>
                                    </td>
                                    <td style="padding: 12px 14px; font-size: 11.5px; color: #64748b;">
                                        {{ $file->created_at->format('d.m.Y H:i') }}
                                    </td>
                                    <td style="padding: 12px 14px; text-align: center;">
                                        <div style="display: inline-flex; align-items: center; gap: 6px;">
                                            <a href="{{ route('projects.download_file', [$project->id, $file->id]) }}"
                                                class="btn btn-sm btn-outline"
                                                style="font-size: 11px; padding: 4px 8px; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                                <span>⬇️</span> İndir
                                            </a>
                                            @php
                                                $canDeleteFile = auth()->user()->isSuperAdmin()
                                                    || auth()->user()->isAdmin()
                                                    || ($file->uploaded_by === auth()->id())
                                                    || ($project->manager_id === auth()->id());
                                            @endphp
                                            @if($canDeleteFile)
                                                <form action="{{ route('projects.delete_file', [$project->id, $file->id]) }}"
                                                    method="POST" style="margin: 0;"
                                                    onsubmit="return confirm('Bu belgeyi silmek istediğinize emin misiniz?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline"
                                                        style="font-size: 11px; padding: 4px 8px; border-radius: 4px; border-color: #fecaca; color: #dc2626; background: #fff5f5; cursor: pointer;">
                                                        <span>🗑️</span>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div id="noFilesMatchNotice"
                        style="display: none; text-align: center; padding: 30px; color: #64748b; font-size: 13px;">
                        <span style="font-size: 24px; display: block; margin-bottom: 6px;">🔍</span>
                        Seçilen etiket veya arama terimiyle eşleşen dosya bulunamadı.
                    </div>
                @else
                    <div style="text-align: center; padding: 40px; color: #64748b; font-size: 13px;">
                        <div style="font-size: 32px; margin-bottom: 8px;">📁</div>
                        <div style="font-weight: 700; color: #0f172a; margin-bottom: 4px;">Henüz Belge / Envanter Yüklenmedi
                        </div>
                        <div>Proje paydaşları için teknik çizim, İSG çalışma izni veya malzeme listesi yüklemek için yukarıdaki
                            formu kullanabilirsiniz.</div>
                    </div>
                @endif

                <script>
                    let currentActiveTagFilter = '';

                    function filterByTagPill(tag) {
                        currentActiveTagFilter = tag.toLowerCase().trim();
                        const pillAll = document.getElementById('pill-all');
                        const pills = document.querySelectorAll('.project-tag-pill');

                        if (pillAll) {
                            if (!tag) {
                                pillAll.style.background = '#0284c7';
                                pillAll.style.color = '#fff';
                                pillAll.style.borderColor = '#0284c7';
                            } else {
                                pillAll.style.background = '#fff';
                                pillAll.style.color = '#334155';
                                pillAll.style.borderColor = '#cbd5e1';
                            }
                        }

                        pills.forEach(p => {
                            const pTag = (p.getAttribute('data-tag') || '').toLowerCase().trim();
                            if (pTag === currentActiveTagFilter && tag !== '') {
                                p.style.background = '#0284c7';
                                p.style.color = '#fff';
                                p.style.borderColor = '#0284c7';
                            } else {
                                p.style.background = '#fff';
                                p.style.color = '#334155';
                                p.style.borderColor = '#cbd5e1';
                            }
                        });

                        filterProjectFilesTable();
                    }

                    function filterProjectFilesTable() {
                        const searchVal = (document.getElementById('projectFileSearchInput')?.value || '').toLowerCase().trim();
                        const rows = document.querySelectorAll('.project-file-row');
                        let visibleCount = 0;

                        rows.forEach(row => {
                            const filename = row.getAttribute('data-filename') || '';
                            const tags = row.getAttribute('data-tags') || '';

                            const matchesTag = !currentActiveTagFilter || tags.includes(currentActiveTagFilter);
                            const matchesSearch = !searchVal || filename.includes(searchVal) || tags.includes(searchVal.replace('#', ''));

                            if (matchesTag && matchesSearch) {
                                row.style.display = '';
                                visibleCount++;
                            } else {
                                row.style.display = 'none';
                            }
                        });

                        const notice = document.getElementById('noFilesMatchNotice');
                        if (notice) {
                            notice.style.display = visibleCount === 0 ? 'block' : 'none';
                        }
                    }

                    function editFileTagsModal(projectId, fileId, currentTags) {
                        const newTags = prompt('Dosya etiketlerini virgülle ayırarak giriniz (örn: #Statik, #İSG, #Sözleşme):', currentTags);
                        if (newTags === null) return;

                        fetch(`/projects/${projectId}/files/${fileId}/tags`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ tags: newTags }                          )
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                window.location.reload();
                            } else {
                                alert('Hata: ' + (data.message || 'Güncellenemedi.'));
                            }
                        })
                        .catch(() => alert('Bağlantı hatası.'));
                    }
                    </script>
                </div>
            </div>
        </div>

        <!-- TAB 9: DENETİM İZİ (AUDIT LOGS) -->
        <div id="tab-logs" class="tab-content" style="display: none;">
            <div class="card" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 18px 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 14px;">
                        <div>
                            <h4 style="margin: 0; font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                                <span>📜</span> Detaylı Değişiklik ve İşlem Tarihçesi (Audit Trail)
                            </h4>
                            <span style="font-size: 12px; color: #64748b; margin-top: 2px; display: block;">
                                Tüm kullanıcı işlemleri, durum geçişleri ve yetki eylemleri denetim amacıyla şeffaf olarak listelenir.
                            </span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span class="badge" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 12px; padding: 6px 12px; font-weight: 700;" id="auditStatsBadge">
                                Gösterilen: <span id="auditVisibleCount">{{ $project->auditLogs->count() }}</span> / {{ $project->auditLogs->count() }} Kayıt
                            </span>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetAuditFilters()" style="padding: 5px 12px; font-size: 12px; font-weight: 600; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                <span>🔄</span> Filtreleri Sıfırla
                            </button>
                        </div>
                    </div>

                    <!-- ÜST HIZLI ARAMA & FİLTRE ÇUBUĞU -->
                    <div style="display: grid; grid-template-columns: 2.2fr 1.2fr 1.2fr; gap: 12px; background: #f8fafc; padding: 14px; border-radius: 10px; border: 1px solid #e2e8f0;">
                        <div>
                            <label style="font-size: 11.5px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">🔍 Genel Arama (Tüm Sütunlarda):</label>
                            <input type="text" id="auditGlobalSearch" onkeyup="filterAuditLogs()" placeholder="Tarih, kişi, eylem veya açıklama içinde ara..." class="form-control" style="font-size: 12.5px; height: 38px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px;">
                        </div>
                        <div>
                            <label style="font-size: 11.5px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">📌 Eyleme Göre Filtrele:</label>
                            <select id="auditActionFilter" onchange="filterAuditLogs()" class="form-control" style="font-size: 12.5px; height: 38px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px;">
                                <option value="">-- Tüm Eylemler --</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 11.5px; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">👤 İşlemi Yapana Göre:</label>
                            <select id="auditUserFilter" onchange="filterAuditLogs()" class="form-control" style="font-size: 12.5px; height: 38px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px;">
                                <option value="">-- Tüm Kullanıcılar --</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card-body" style="padding: 0; overflow-x: auto;">
                    <table class="app-table" id="auditLogTable" style="margin: 0; width: 100%;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <th style="width: 170px; padding: 12px 14px; font-size: 12.5px; font-weight: 700; color: #1e293b;">Tarih & Saat</th>
                                <th style="width: 220px; padding: 12px 14px; font-size: 12.5px; font-weight: 700; color: #1e293b;">İşlemi Yapan</th>
                                <th style="width: 200px; padding: 12px 14px; font-size: 12.5px; font-weight: 700; color: #1e293b;">Eylem (İşlem Türü)</th>
                                <th style="padding: 12px 14px; font-size: 12.5px; font-weight: 700; color: #1e293b;">Açıklama & Detay</th>
                            </tr>
                            <!-- HER SÜTUNA ÖZEL ANLIK FİLTRELEME SATIRI -->
                            <tr style="background: #f1f5f9; border-bottom: 2px solid #e2e8f0;">
                                <th style="padding: 6px 10px;">
                                    <input type="text" id="filterColDate" onkeyup="filterAuditLogs()" placeholder="Tarih ara..." class="form-control" style="font-size: 11.5px; height: 30px; padding: 4px 8px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 4px;">
                                </th>
                                <th style="padding: 6px 10px;">
                                    <input type="text" id="filterColUser" onkeyup="filterAuditLogs()" placeholder="Kullanıcı ara..." class="form-control" style="font-size: 11.5px; height: 30px; padding: 4px 8px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 4px;">
                                </th>
                                <th style="padding: 6px 10px;">
                                    <input type="text" id="filterColAction" onkeyup="filterAuditLogs()" placeholder="Eylem ara..." class="form-control" style="font-size: 11.5px; height: 30px; padding: 4px 8px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 4px;">
                                </th>
                                <th style="padding: 6px 10px;">
                                    <input type="text" id="filterColDesc" onkeyup="filterAuditLogs()" placeholder="Açıklamada ara..." class="form-control" style="font-size: 11.5px; height: 30px; padding: 4px 8px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 4px;">
                                </th>
                            </tr>
                        </thead>
                        <tbody id="auditLogTbody">
                            @forelse($project->auditLogs as $log)
                                @php
                                    $meta = $log->action_meta;
                                @endphp
                                <tr class="audit-row"
                                    data-date="{{ $log->created_at->format('d.m.Y H:i:s') }}"
                                    data-actor="{{ $log->actor_name }}"
                                    data-action-slug="{{ strtolower($log->action) }}"
                                    data-action-label="{{ $meta['label'] }}"
                                    data-desc="{{ strip_tags($log->description) }}"
                                    style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="font-size: 12px; color: #64748b; font-weight: 500; width: 170px; padding: 12px 14px;">
                                        {{ $log->created_at->format('d.m.Y H:i:s') }}
                                    </td>
                                    <td style="font-weight: 600; font-size: 13px; color: #1e293b; width: 220px; padding: 12px 14px;">
                                        {{ $log->actor_name }}
                                    </td>
                                    <td style="width: 200px; padding: 12px 14px;">
                                        <span class="audit-action-badge" style="background: {{ $meta['bg'] }}; color: {{ $meta['color'] }}; border: 1px solid {{ $meta['border'] }}; padding: 3px 10px; border-radius: 6px; font-size: 11.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; white-space: nowrap;">
                                            <span>{{ $meta['icon'] }}</span>
                                            <span>{{ $meta['label'] }}</span>
                                        </span>
                                    </td>
                                    <td style="font-size: 12.5px; color: #334155; line-height: 1.5; padding: 12px 14px;">
                                        {!! $log->description !!}
                                    </td>
                                </tr>
                            @empty
                                <tr id="auditEmptyInitialRow">
                                    <td colspan="4" style="text-align: center; padding: 32px; color: #64748b; font-size: 13px;">
                                        <div style="font-size: 28px; margin-bottom: 6px;">📜</div>
                                        <strong>Henüz kayıtlı bir denetim izi bulunmuyor.</strong>
                                    </td>
                                </tr>
                            @endforelse
                            <!-- FİLTRELEME SONUCU BOŞSA GÖSTERİLECEK SATIR -->
                            <tr id="auditNoResultsRow" style="display: none;">
                                <td colspan="4" style="text-align: center; padding: 36px 20px; color: #64748b; background: #fafafa;">
                                    <div style="font-size: 32px; margin-bottom: 8px;">🔍</div>
                                    <strong style="color: #0f172a; font-size: 14px; display: block; margin-bottom: 4px;">Arama kriterlerine uygun log kaydı bulunamadı.</strong>
                                    <span style="font-size: 12px;">Lütfen arama terimini değiştirin veya filtreleri sıfırlayın.</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 10: PROJE İÇİ EKİP YETKİ MATRİSİ & GÖREV/ROL DEVRİ -->
        <div id="tab-team_matrix" class="tab-content" style="display: none;">
            <div class="card mb-4" style="border-radius: 14px; border: 1px solid #e2e8f0; box-shadow: 0 4px 14px rgba(0,0,0,0.04); overflow: hidden;">
                <div class="card-header" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 20px 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                            <span style="font-size: 22px;">👥</span>
                            <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: #ffffff;">
                                Proje İçi Ekip Yetki Matrisi & Görev Devir Merkezi
                            </h3>
                            <span class="badge" style="background: rgba(34, 197, 94, 0.2); color: #86efac; border: 1px solid rgba(134, 239, 172, 0.4); font-size: 11px;">
                                ⚡ Mikro Yetkilendirme
                            </span>
                        </div>
                        <p style="margin: 0; font-size: 12.5px; color: #94a3b8; max-width: 800px; line-height: 1.5;">
                            Proje Lideri veya Sistem Yöneticisi, proje içindeki ekip üyelerine görev atama, toplantı planlama, BOM girişi ve faz tamamlama gibi operasyonel yetkileri verebilir. Ayrılan personellerin görevleri devir sihirbazı ile aktarılabilir.
                        </p>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        @php
                            $canChangeManager = auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || (int) $project->sponsor_id === (int) auth()->id() || auth()->user()->hasPermission('project.change_manager');
                        @endphp
                        @if($canChangeManager)
                            <button type="button" class="btn btn-sm btn-warning" onclick="openChangeManagerModal()" style="font-weight: 700; font-size: 12.5px; padding: 8px 16px; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(245, 158, 11, 0.3);">
                                <span>👑</span> Proje Liderini Değiştir
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Bilgilendirme Kılavuzu -->
                <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 14px 24px; display: flex; gap: 20px; flex-wrap: wrap; align-items: center; font-size: 12px; color: #475569;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #22c55e;"></span>
                        <strong>Canlı Yetkilendirme:</strong> Anahtarı çevirdiğiniz an yetki veri tabanına işlenir.
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span>🛡️</span>
                        <strong>Çıkar Çatışması Kalkanı:</strong> Proje Lideri veya üyesi kendi projesinin kurumsal onayını (DOA) veremez.
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span>🔄</span>
                        <strong>Görev & Rol Devri:</strong> Ayrılan personelin üzerindeki açık işler tek tıkla aktarılır.
                    </div>
                </div>

                <div class="card-body p-0" style="overflow-x: auto;">
                    <table class="app-table" style="min-width: 1100px; width: 100%; margin: 0; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 2px solid #cbd5e1; text-align: left;">
                                <th style="padding: 14px 18px; font-size: 12px; font-weight: 800; color: #475569; text-transform: uppercase; min-width: 260px; white-space: nowrap;">Ekip Üyesi & Unvan</th>
                                <th style="padding: 14px 12px; font-size: 12px; font-weight: 800; color: #475569; text-transform: uppercase; text-align: center; min-width: 130px; white-space: nowrap;">Durum</th>
                                <th style="padding: 14px 10px; font-size: 12px; font-weight: 800; color: #475569; text-transform: uppercase; text-align: center; min-width: 135px; white-space: nowrap;" title="Projeye yeni görev ekleme, atama ve durum güncelleme">📋 Görev Yönetimi</th>
                                <th style="padding: 14px 10px; font-size: 12px; font-weight: 800; color: #475569; text-transform: uppercase; text-align: center; min-width: 140px; white-space: nowrap;" title="Proje toplantısı planlama, tutanak ve karar ekleme">📅 Toplantı Planlama</th>
                                <th style="padding: 14px 10px; font-size: 12px; font-weight: 800; color: #475569; text-transform: uppercase; text-align: center; min-width: 135px; white-space: nowrap;" title="Malzeme ağacı (BOM) ve harcama kalemi ekleme">💰 BOM & Bütçe</th>
                                <th style="padding: 14px 10px; font-size: 12px; font-weight: 800; color: #475569; text-transform: uppercase; text-align: center; min-width: 130px; white-space: nowrap;" title="Faz tamamlama ve kilometre taşı ilerleme raporlama">🚩 Faz Raporu</th>
                                <th style="padding: 14px 10px; font-size: 12px; font-weight: 800; color: #475569; text-transform: uppercase; text-align: center; min-width: 130px; white-space: nowrap;" title="Proje dosyaları ve sözleşme yönetimi">📁 Dokümanlar</th>
                                <th style="padding: 14px 18px; font-size: 12px; font-weight: 800; color: #475569; text-transform: uppercase; text-align: center; min-width: 170px; white-space: nowrap;">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $projectPermissions = $project->stakeholderPermissions->groupBy('user_id');
                                $canManageMatrix = auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || (int) $project->manager_id === (int) auth()->id() || auth()->user()->hasPermission('project.manage_team_matrix');
                                $canHandover = auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || (int) $project->manager_id === (int) auth()->id() || auth()->user()->hasPermission('project.handover_member');
                            @endphp
                            @forelse($project->stakeholders as $stk)
                                @php
                                    $u = $stk->user;
                                    if (!$u)
                                        continue;
                                    $isManager = (int) $project->manager_id === (int) $u->id;
                                    $isSponsor = (int) $project->sponsor_id === (int) $u->id;
                                    $userPerms = $projectPermissions->get($u->id, collect())->keyBy('permission_key');
                                    $isActive = (bool) ($stk->is_active ?? true);
                                @endphp
                                <tr style="border-bottom: 1px solid #f1f5f9; background: {{ $isActive ? '#ffffff' : '#fef2f2' }}; transition: background 0.15s;">
                                    <td style="padding: 14px 18px; vertical-align: middle; white-space: nowrap;">
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <div style="width: 38px; height: 38px; border-radius: 50%; background: {{ $isManager ? '#fef3c7' : '#e0f2fe' }}; color: {{ $isManager ? '#b45309' : '#0369a1' }}; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 800; border: 1.5px solid {{ $isManager ? '#fde68a' : '#bae6fd' }}; flex-shrink: 0;">
                                                {{ $isManager ? '👑' : mb_substr($u->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div style="font-size: 13.5px; font-weight: 750; color: #0f172a; display: flex; align-items: center; gap: 6px; white-space: nowrap;">
                                                    <span>{{ $u->name }}</span>
                                                    @if($isManager)
                                                        <span class="badge" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-size: 10px; font-weight: 800; padding: 2px 7px; border-radius: 4px; white-space: nowrap; display: inline-flex; align-items: center; gap: 3px;">👑 LİDER</span>
                                                    @elseif($isSponsor)
                                                        <span class="badge" style="background: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe; font-size: 10px; font-weight: 800; padding: 2px 7px; border-radius: 4px; white-space: nowrap; display: inline-flex; align-items: center; gap: 3px;">SPONSOR</span>
                                                    @endif
                                                </div>
                                                <div style="font-size: 11.5px; color: #64748b; margin-top: 2px; white-space: nowrap;">
                                                    {{ $u->bolum?->name ?? 'Merkez' }} • {{ $u->unvan ?? 'Ekip Üyesi' }}
                                                </div>
                                                @if(!$isActive && $stk->handoverToUser)
                                                    <div style="font-size: 10.5px; color: #b91c1c; font-weight: 600; margin-top: 3px; white-space: nowrap;">
                                                        ↳ Görevler Devredildi: <strong>{{ $stk->handoverToUser->name }}</strong>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding: 14px 12px; vertical-align: middle; text-align: center; white-space: nowrap;">
                                        @if($isActive)
                                            <span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px; white-space: nowrap; display: inline-flex; align-items: center; gap: 4px;">
                                                🟢 Aktif Üye
                                            </span>
                                        @else
                                            <span class="badge" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px; white-space: nowrap; display: inline-flex; align-items: center; gap: 4px;" title="{{ $stk->left_reason ?: 'Ayrıldı' }}">
                                                🔴 Pasif / Ayrıldı
                                            </span>
                                        @endif
                                    </td>

                                    @foreach(['task_manage', 'meeting_plan', 'bom_manage', 'phase_report', 'file_manage'] as $pKey)
                                        @php
                                            $granted = $isManager ? true : (bool) ($userPerms->get($pKey)?->is_granted ?? false);
                                        @endphp
                                        <td style="padding: 14px 10px; vertical-align: middle; text-align: center; white-space: nowrap;">
                                            @if($isManager)
                                                <span style="font-size: 11px; font-weight: 700; color: #166534; background: #dcfce7; border: 1px solid #bbf7d0; padding: 4px 10px; border-radius: 6px; white-space: nowrap; display: inline-flex; align-items: center; gap: 4px;">
                                                    ✓ Tam Yetkili
                                                </span>
                                            @elseif(!$isActive)
                                                <span style="font-size: 11px; color: #94a3b8; font-style: italic; white-space: nowrap;">Kapalı</span>
                                            @else
                                                <label class="custom-toggle" style="margin: 0; display: inline-block;">
                                                    <input type="checkbox"
                                                           {{ $granted ? 'checked' : '' }}
                                                           {{ !$canManageMatrix ? 'disabled' : '' }}
                                                           onchange="toggleProjectPermission(this, {{ $project->id }}, {{ $u->id }}, '{{ $pKey }}')">
                                                    <span class="toggle-slider"></span>
                                                </label>
                                            @endif
                                        </td>
                                    @endforeach

                                    <td style="padding: 14px 18px; vertical-align: middle; text-align: center; white-space: nowrap;">
                                        @if($isActive && $canHandover)
                                            <button type="button" class="btn btn-xs btn-outline-danger" onclick="openHandoverModal({{ $u->id }}, '{{ addslashes($u->name) }}')" style="font-weight: 700; font-size: 11.5px; padding: 6px 12px; border-radius: 7px; display: inline-flex; align-items: center; gap: 5px; border-color: #fca5a5; color: #dc2626; background: #ffffff; white-space: nowrap; box-shadow: 0 1px 2px rgba(220,38,38,0.06); cursor: pointer;" title="Personelin görev ve yetkilerini başka bir üyeye devret">
                                                <span>🔄</span> Görev & Rol Devret
                                            </button>
                                        @elseif(!$isActive)
                                            <span style="font-size: 11px; color: #64748b; font-weight: 500; white-space: nowrap;">{{ $stk->left_at ? $stk->left_at->format('d.m.Y') : 'Ayrıldı' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 36px; color: #64748b;">
                                        Henüz eklenmiş ekip üyesi bulunmuyor.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 11: KALİTE & KABUL KRİTERLERİ (QUALITY GATES & TESTS) -->
        <div id="tab-quality" class="tab-content" style="display: none;">
            @php
                $projQualities = $project->qualities ?? collect();
                $projNcrs = $project->nonConformances ?? collect();
                $qPassed = $projQualities->where('status', 'passed')->count();
                $qFailed = $projQualities->where('status', 'failed')->count();
                $qPending = $projQualities->where('status', 'pending')->count();
                $ncrOpen = $projNcrs->whereIn('status', ['open', 'investigating', 'capa_in_progress'])->count();
            @endphp

            <!-- ÜST KART & HIZLI AKSİYONLAR -->
            <div class="card mb-4" style="border-radius: 12px; border: 1px solid #bae6fd; background: linear-gradient(135deg, #f0f9ff 0%, #ffffff 100%); padding: 18px 22px;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                    <div>
                        <h3 style="font-size: 17px; font-weight: 800; color: #0369a1; margin: 0; display: flex; align-items: center; gap: 8px;">
                            <span>🔬</span> Proje Kalite Standartları, Kabul Kriterleri & Kalite Kapıları
                        </h3>
                        <p style="font-size: 12.5px; color: #0284c7; margin: 4px 0 0;">
                            Faz geçiş kriterleri, fabrika kabul testleri (FAT/SAT), ölçüm toleransları ve açık uygunsuzluk (NCR) takibi.
                        </p>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        @if(!auth()->user()->isYonetim())
                            <button type="button" class="btn btn-sm btn-primary" onclick="openProjQualityModal()" style="font-weight: 700; border-radius: 8px;">
                                + Kalite Kriteri Ekle
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="openProjNcrModal()" style="font-weight: 700; border-radius: 8px;">
                                🚨 Uygunsuzluk (NCR) Aç
                            </button>
                        @endif
                        <a href="{{ route('quality.index', ['project_id' => $project->id]) }}" class="btn btn-sm btn-outline-info" style="font-weight: 700; border-radius: 8px;">
                            Tam Kalite Panosu ↗
                        </a>
                    </div>
                </div>

                <!-- ÖZET KPI ŞERİDİ -->
                <div style="display: flex; gap: 16px; margin-top: 16px; flex-wrap: wrap;">
                    <div style="background: #ffffff; padding: 10px 18px; border-radius: 8px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 18px;">📋</span>
                        <div>
                            <div style="font-size: 11px; color: #64748b; font-weight: 700;">TOPLAM KRİTER</div>
                            <div style="font-size: 16px; font-weight: 800; color: #0f172a;">{{ $projQualities->count() }}</div>
                        </div>
                    </div>
                    <div style="background: #ffffff; padding: 10px 18px; border-radius: 8px; border: 1px solid #bbf7d0; display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 18px;">✅</span>
                        <div>
                            <div style="font-size: 11px; color: #15803d; font-weight: 700;">GEÇTİ (PASSED)</div>
                            <div style="font-size: 16px; font-weight: 800; color: #166534;">{{ $qPassed }}</div>
                        </div>
                    </div>
                    <div style="background: #ffffff; padding: 10px 18px; border-radius: 8px; border: 1px solid #fecaca; display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 18px;">❌</span>
                        <div>
                            <div style="font-size: 11px; color: #b91c1c; font-weight: 700;">REDDEDİLDİ (FAILED)</div>
                            <div style="font-size: 16px; font-weight: 800; color: #991b1b;">{{ $qFailed }}</div>
                        </div>
                    </div>
                    <div style="background: #ffffff; padding: 10px 18px; border-radius: 8px; border: 1px solid #fed7aa; display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 18px;">⏳</span>
                        <div>
                            <div style="font-size: 11px; color: #c2410c; font-weight: 700;">TEST BEKLİYOR</div>
                            <div style="font-size: 16px; font-weight: 800; color: #ea580c;">{{ $qPending }}</div>
                        </div>
                    </div>
                    <div style="background: #ffffff; padding: 10px 18px; border-radius: 8px; border: 1px solid #fecdd3; display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 18px;">🚨</span>
                        <div>
                            <div style="font-size: 11px; color: #e11d48; font-weight: 700;">AÇIK NCR</div>
                            <div style="font-size: 16px; font-weight: 800; color: #be123c;">{{ $ncrOpen }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 1. BÖLÜM: KALİTE TESTLERİ & KABUL KRİTERLERİ TABLOSU -->
            <div class="card mb-4" style="border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff;">
                <div class="card-header bg-white" style="padding: 14px 20px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
                    <div style="font-weight: 800; color: #1e293b; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                        <span>🔬</span> Testler, Ölçümler ve Faz Kalite Kapıları
                    </div>
                    <span style="font-size: 11.5px; color: #64748b;">{{ $projQualities->count() }} Kriter Tanımlı</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 12.5px; vertical-align: middle;">
                        <thead style="background: #f8fafc; color: #475569; font-weight: 700; border-bottom: 1.5px solid #e2e8f0;">
                            <tr>
                                <th style="padding: 10px 16px;">Tip</th>
                                <th>Kriter & Standart</th>
                                <th>İlgili Faz / Görev</th>
                                <th>Hedef Değer & Tolerans</th>
                                <th>Ölçülen Fiili Değer</th>
                                <th>Durum</th>
                                <th>Kontrolör / Onay</th>
                                <th style="text-align: right; padding-right: 16px;">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projQualities as $q)
                                <tr>
                                    <td style="padding: 10px 16px;">
                                        <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 5px;">
                                            {{ $q->check_type_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: #0f172a;">{{ $q->title }}</div>
                                        @if($q->standards_reference)
                                            <div style="font-size: 11px; color: #0284c7;">Ref: {{ $q->standards_reference }}</div>
                                        @endif
                                        @if($q->description)
                                            <div style="font-size: 11px; color: #64748b;">{{ Str::limit($q->description, 50) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($q->phase)
                                            <div style="font-weight: 600;">🔷 {{ $q->phase->name }}</div>
                                        @endif
                                        @if($q->task)
                                            <div style="font-size: 11px; color: #64748b;">📋 {{ Str::limit($q->task->title, 30) }}</div>
                                        @endif
                                        @if(!$q->phase && !$q->task)
                                            <span style="color: #94a3b8;">Genel Proje</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($q->target_value)
                                            <div style="font-weight: 700; color: #0f172a;">{{ $q->target_value }} {{ $q->unit }}</div>
                                            @if($q->tolerance)
                                                <div style="font-size: 10.5px; color: #64748b;">Tol: {{ $q->tolerance }}</div>
                                            @endif
                                        @else
                                            <span style="color: #94a3b8;">Niteliksel / Görsel</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($q->actual_value)
                                            <span style="font-weight: 800; color: #0f172a;">{{ $q->actual_value }} {{ $q->unit }}</span>
                                        @else
                                            <span style="color: #94a3b8;">Ölçülmedi</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php $qBadge = $q->status_badge; @endphp
                                        <span class="badge" style="background: {{ $qBadge['bg'] }}; color: {{ $qBadge['color'] }}; font-size: 11px; font-weight: 700; padding: 4px 8px; border-radius: 6px;">
                                            {{ $qBadge['icon'] ?? '' }} {{ $qBadge['label'] }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($q->checker)
                                            <div>{{ $q->checker->name }}</div>
                                            <div style="font-size: 10.5px; color: #94a3b8;">{{ $q->checked_at ? $q->checked_at->format('d.m.Y H:i') : '' }}</div>
                                        @else
                                            <span style="color: #94a3b8;">Atanmadı</span>
                                        @endif
                                    </td>
                                    <td style="text-align: right; padding-right: 16px;">
                                        @if(!auth()->user()->isYonetim())
                                            <div style="display: inline-flex; align-items: center; gap: 6px;">
                                                <button type="button" class="btn btn-xs btn-outline-primary" onclick="evaluateProjQuality({{ json_encode($q) }})" style="font-size: 11px; font-weight: 700; border-radius: 6px;">
                                                    Değerlendir
                                                </button>
                                                <form method="POST" action="{{ route('quality.destroy', $q->id) }}" onsubmit="return confirm('Bu kalite kriterini silmek istediğinize emin misiniz?');" style="margin: 0;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-outline-danger" style="font-size: 11px; border-radius: 6px;">
                                                        Sil
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span style="color: #94a3b8; font-size: 11px;">👁️ Salt Okunur</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4" style="color: #94a3b8;">
                                        Bu projeye henüz kalite kontrol kriteri veya test planı eklenmemiş.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 2. BÖLÜM: KALİTE UYGUNSUZLUKLARI (NCR / PUNCH LIST) TABLOSU -->
            <div class="card mb-4" style="border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff;">
                <div class="card-header bg-white" style="padding: 14px 20px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
                    <div style="font-weight: 800; color: #1e293b; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                        <span>🚨</span> Açık Kalite Uygunsuzlukları & DÖF Takibi
                    </div>
                    <span style="font-size: 11.5px; color: #64748b;">{{ $projNcrs->count() }} NCR Kaydı</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 12.5px; vertical-align: middle;">
                        <thead style="background: #f8fafc; color: #475569; font-weight: 700; border-bottom: 1.5px solid #e2e8f0;">
                            <tr>
                                <th style="padding: 10px 16px;">NCR No</th>
                                <th>Önem</th>
                                <th>Uygunsuzluk Başlığı</th>
                                <th>Kök Neden & Aksiyon</th>
                                <th>Sorumlu</th>
                                <th>Termin</th>
                                <th>Durum</th>
                                <th style="text-align: right; padding-right: 16px;">Detay</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projNcrs as $ncr)
                                <tr>
                                    <td style="padding: 10px 16px; font-weight: 800; color: #b91c1c;">
                                        {{ $ncr->ncr_number }}
                                    </td>
                                    <td>
                                        @php $sevBadge = $ncr->severity_badge; @endphp
                                        <span class="badge" style="background: {{ $sevBadge['bg'] }}; color: {{ $sevBadge['color'] }}; font-size: 10.5px; font-weight: 800; padding: 3px 7px; border-radius: 6px;">
                                            {{ $sevBadge['label'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: #0f172a;">{{ $ncr->title }}</div>
                                        @if($ncr->description)
                                            <div style="font-size: 11px; color: #64748b;">{{ Str::limit($ncr->description, 50) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($ncr->root_cause)
                                            <div style="font-size: 11px; color: #475569;"><strong>Kök Neden:</strong> {{ Str::limit($ncr->root_cause, 40) }}</div>
                                        @endif
                                        @if($ncr->corrective_action)
                                            <div style="font-size: 11px; color: #0284c7;"><strong>Aksiyon:</strong> {{ Str::limit($ncr->corrective_action, 40) }}</div>
                                        @endif
                                        @if(!$ncr->root_cause && !$ncr->corrective_action)
                                            <span style="color: #94a3b8;">Analiz bekleniyor</span>
                                        @endif
                                    </td>
                                    <td>{{ $ncr->assignee?->name ?? 'Atanmadı' }}</td>
                                    <td>
                                        @if($ncr->due_date)
                                            <span style="{{ $ncr->is_overdue ? 'color: #dc2626; font-weight: 800;' : '' }}">
                                                {{ $ncr->due_date->format('d.m.Y') }}
                                                @if($ncr->is_overdue) ⚠️ @endif
                                            </span>
                                        @else
                                            <span style="color: #94a3b8;">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php $stBadge = $ncr->status_badge; @endphp
                                        <span class="badge" style="background: {{ $stBadge['bg'] }}; color: {{ $stBadge['color'] }}; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px;">
                                            {{ $stBadge['label'] }}
                                        </span>
                                    </td>
                                    <td style="text-align: right; padding-right: 16px;">
                                        <a href="{{ route('quality.index', ['project_id' => $project->id, 'tab' => 'ncrs']) }}" class="btn btn-xs btn-outline-secondary" style="font-size: 11px;">
                                            Detay ↗
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4" style="color: #94a3b8;">
                                        Bu projede açık veya kayıtlı bir uygunsuzluk (NCR) bulunmuyor.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 12: TEMEL ÇİZGİLER & EVM (SCHEDULE & COST BASELINE, S-CURVE) -->
        <div id="tab-baselines" class="tab-content" style="display: none;">
            @php
                $activeBaseline = $project->activeBaseline;
                $allBaselines = $project->baselines;
                $evm = $evmMetrics ?? ($activeBaseline ? $activeBaseline->calculateEvm() : null);
            @endphp

            <!-- ÜST AKSİYON & BİLGİ KARTI -->
            <div class="card mb-4" style="border-radius: 12px; border: 1px solid #c7d2fe; background: linear-gradient(135deg, #eef2ff 0%, #ffffff 100%); padding: 18px 22px;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                    <div>
                        <h3 style="font-size: 17px; font-weight: 800; color: #312e81; margin: 0; display: flex; align-items: center; gap: 8px;">
                            <span>📐</span> Schedule & Cost Baseline (Temel Çizgi) ve EVM Analizi
                        </h3>
                        <p style="font-size: 12.5px; color: #4338ca; margin: 4px 0 0;">
                            Onaylanmış orijinal proje taahhütleri (Zaman ve Maliyet Temel Çizgisi), S-Eğrisi referansı ve PMI Kazanılmış Değer (Earned Value) metrikleri.
                        </p>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        @if(!auth()->user()->isYonetim())
                            <button type="button" class="btn btn-sm" onclick="openFreezeBaselineModal()" style="background: #4f46e5; color: #ffffff; font-weight: 700; border-radius: 8px; padding: 7px 16px; border: none; box-shadow: 0 2px 4px rgba(79,70,229,0.25);">
                                <span>🔒</span> + Yeni Temel Çizgi Dondur (Snapshot)
                            </button>
                        @endif
                        <a href="{{ route('gantt.index', ['project_id' => $project->id]) }}" class="btn btn-sm btn-outline-primary" style="font-weight: 700; border-radius: 8px; border-color: #6366f1; color: #4f46e5; background: #ffffff;">
                            <span>📊</span> Gantt Çift Çubuk Karşılaştır ↗
                        </a>
                    </div>
                </div>
            </div>

            <!-- EVM KPI METRİKLERİ PANOSU -->
            @if($evm)
                <div class="row g-3 mb-4">
                    <!-- BAC (Budget at Completion) -->
                    <div class="col-md-3 col-sm-6">
                        <div class="card p-3" style="border-radius: 10px; border: 1.5px solid #e0e7ff; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                            <div style="font-size: 11px; font-weight: 800; color: #4338ca; text-transform: uppercase;">BAC (Temel Çizgi Bütçesi)</div>
                            <div style="font-size: 20px; font-weight: 900; color: #1e1b4b; margin-top: 4px;">
                                {{ number_format($evm['bac'], 2) }} <span style="font-size: 13px; font-weight: 700; color: #6366f1;">{{ $project->currency ?? 'TRY' }}</span>
                            </div>
                            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Onaylanan Tavan Bütçe</div>
                        </div>
                    </div>

                    <!-- PV (Planned Value) -->
                    <div class="col-md-3 col-sm-6">
                        <div class="card p-3" style="border-radius: 10px; border: 1.5px solid #e2e8f0; background: #ffffff;">
                            <div style="font-size: 11px; font-weight: 800; color: #0284c7; text-transform: uppercase;">PV (Planlanan Değer)</div>
                            <div style="font-size: 20px; font-weight: 900; color: #0f172a; margin-top: 4px;">
                                {{ number_format($evm['pv'], 2) }} <span style="font-size: 13px; font-weight: 700; color: #0284c7;">{{ $project->currency ?? 'TRY' }}</span>
                            </div>
                            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Takvime göre bugünkü hedef (%{{ $evm['time_ratio_pct'] }})</div>
                        </div>
                    </div>

                    <!-- EV (Earned Value) -->
                    <div class="col-md-3 col-sm-6">
                        <div class="card p-3" style="border-radius: 10px; border: 1.5px solid #bbf7d0; background: #f0fdf4;">
                            <div style="font-size: 11px; font-weight: 800; color: #15803d; text-transform: uppercase;">EV (Kazanılmış Değer)</div>
                            <div style="font-size: 20px; font-weight: 900; color: #166534; margin-top: 4px;">
                                {{ number_format($evm['ev'], 2) }} <span style="font-size: 13px; font-weight: 700; color: #15803d;">{{ $project->currency ?? 'TRY' }}</span>
                            </div>
                            <div style="font-size: 11px; color: #15803d; margin-top: 2px;">Gerçekleşen İlerleme Karşılığı (%{{ $evm['progress_pct'] }})</div>
                        </div>
                    </div>

                    <!-- AC (Actual Cost) -->
                    <div class="col-md-3 col-sm-6">
                        <div class="card p-3" style="border-radius: 10px; border: 1.5px solid #fecaca; background: #fff5f5;">
                            <div style="font-size: 11px; font-weight: 800; color: #b91c1c; text-transform: uppercase;">AC (Fiili Harcanan)</div>
                            <div style="font-size: 20px; font-weight: 900; color: #991b1b; margin-top: 4px;">
                                {{ number_format($evm['ac'], 2) }} <span style="font-size: 13px; font-weight: 700; color: #b91c1c;">{{ $project->currency ?? 'TRY' }}</span>
                            </div>
                            <div style="font-size: 11px; color: #991b1b; margin-top: 2px;">Kümülatif fiili harcama</div>
                        </div>
                    </div>

                    <!-- CV (Cost Variance) & CPI -->
                    <div class="col-md-6">
                        <div class="card p-3" style="border-radius: 10px; border: 1.5px solid {{ $evm['cv'] >= 0 ? '#86efac' : '#fca5a5' }}; background: #ffffff;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <span style="font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase;">Maliyet Sapması (CV) & Endeksi (CPI)</span>
                                    <div style="font-size: 18px; font-weight: 900; color: {{ $evm['cv'] >= 0 ? '#15803d' : '#b91c1c' }}; margin-top: 4px;">
                                        CV: {{ $evm['cv'] >= 0 ? '+' : '' }}{{ number_format($evm['cv'], 2) }} {{ $project->currency ?? 'TRY' }}
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <span class="badge" style="font-size: 13px; font-weight: 800; padding: 6px 12px; border-radius: 8px; {{ $evm['cpi'] >= 1.0 ? 'background: #dcfce7; color: #166534;' : 'background: #fee2e2; color: #991b1b;' }}">
                                        CPI: {{ number_format($evm['cpi'], 2) }}
                                    </span>
                                    <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                        {{ $evm['cpi'] >= 1.0 ? '✅ Bütçe altında / Tasarruflu' : '⚠️ Bütçe aşımı riski' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SV (Schedule Variance) & SPI -->
                    <div class="col-md-6">
                        <div class="card p-3" style="border-radius: 10px; border: 1.5px solid {{ $evm['sv'] >= 0 ? '#86efac' : '#fca5a5' }}; background: #ffffff;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <span style="font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase;">Zaman Sapması (SV) & Endeksi (SPI)</span>
                                    <div style="font-size: 18px; font-weight: 900; color: {{ $evm['sv'] >= 0 ? '#15803d' : '#b91c1c' }}; margin-top: 4px;">
                                        SV: {{ $evm['sv'] >= 0 ? '+' : '' }}{{ number_format($evm['sv'], 2) }} {{ $project->currency ?? 'TRY' }}
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <span class="badge" style="font-size: 13px; font-weight: 800; padding: 6px 12px; border-radius: 8px; {{ $evm['spi'] >= 1.0 ? 'background: #dcfce7; color: #166534;' : 'background: #fee2e2; color: #991b1b;' }}">
                                        SPI: {{ number_format($evm['spi'], 2) }}
                                    </span>
                                    <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                        {{ $evm['spi'] >= 1.0 ? '✅ Takvimin önünde / Zamanında' : '⚠️ Takvim gerisinde gecikme' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-info d-flex align-items-center mb-4" style="border-radius: 10px; border: 1.5px solid #bfdbfe; background: #eff6ff; color: #1e40af;">
                    <span style="font-size: 20px; margin-right: 12px;">ℹ️</span>
                    <div>
                        <strong>Henüz aktif bir Temel Çizgi (Baseline) dondurulmamış.</strong>
                        <div style="font-size: 12px; margin-top: 2px;">
                            EVM maliyet ve takvim sapma analizlerinin hesaplanabilmesi için yukarıdaki <strong>"+ Yeni Temel Çizgi Dondur"</strong> butonuna tıklayarak ilk onaylı planı (v1.0) kaydedebilirsiniz.
                        </div>
                    </div>
                </div>
            @endif

            <!-- DONDURULMUŞ TEMEL ÇİZGİ SÜRÜMLERİ TABLOSU -->
            <div class="card mb-4" style="border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff;">
                <div class="card-header bg-white" style="padding: 14px 20px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
                    <div style="font-weight: 800; color: #1e293b; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                        <span>📜</span> Dondurulmuş Temel Çizgi Sürüm Geçmişi (Baseline History)
                    </div>
                    <span style="font-size: 11.5px; color: #64748b;">{{ $allBaselines->count() }} Sürüm Kayıtlı</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 12.5px; vertical-align: middle;">
                        <thead style="background: #f8fafc; color: #475569; font-weight: 700; border-bottom: 1.5px solid #e2e8f0;">
                            <tr>
                                <th style="padding: 10px 16px;">Sürüm</th>
                                <th>Temel Çizgi Adı & Açıklama</th>
                                <th>Onaylı Bütçe (Cost Baseline)</th>
                                <th>Toplam Efor</th>
                                <th>Hedef Başlangıç / Bitiş</th>
                                <th>Donduran / Tarih</th>
                                <th>Durum</th>
                                <th style="text-align: right; padding-right: 16px;">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allBaselines as $b)
                                <tr style="{{ $b->is_active ? 'background: #f8fafc; font-weight: 600;' : '' }}">
                                    <td style="padding: 10px 16px;">
                                        <span class="badge" style="font-size: 11px; font-weight: 800; {{ $b->is_active ? 'background: #4f46e5; color: #ffffff;' : 'background: #e2e8f0; color: #475569;' }}">
                                            {{ $b->version }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="color: #0f172a; font-weight: 700;">{{ $b->name }}</div>
                                        @if($b->description)
                                            <div style="font-size: 11px; color: #64748b;">{{ Str::limit($b->description, 60) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <strong style="color: #1e293b;">{{ number_format($b->total_planned_budget, 2) }}</strong> {{ $project->currency ?? 'TRY' }}
                                    </td>
                                    <td>{{ number_format($b->total_planned_hours, 1) }} saat</td>
                                    <td>
                                        <div style="font-size: 11.5px;">{{ $b->planned_start_date ? $b->planned_start_date->format('d.m.Y') : '-' }}</div>
                                        <div style="font-size: 11px; color: #64748b;">{{ $b->planned_end_date ? $b->planned_end_date->format('d.m.Y') : '-' }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $b->creator?->name ?? 'Sistem' }}</div>
                                        <div style="font-size: 10.5px; color: #94a3b8;">{{ $b->approved_at ? $b->approved_at->format('d.m.Y H:i') : $b->created_at->format('d.m.Y H:i') }}</div>
                                    </td>
                                    <td>
                                        @if($b->is_active)
                                            <span class="badge" style="background: #dcfce7; color: #166534; font-size: 11px; border: 1px solid #86efac;">
                                                ✓ Aktif Referans
                                            </span>
                                        @else
                                            <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 11px;">
                                                Arşiv
                                            </span>
                                        @endif
                                    </td>
                                    <td style="text-align: right; padding-right: 16px;">
                                        @if(!auth()->user()->isYonetim())
                                            <div style="display: inline-flex; align-items: center; gap: 6px;">
                                                @if(!$b->is_active)
                                                    <form method="POST" action="{{ route('projects.baselines.set_active', [$project->id, $b->id]) }}" style="margin: 0;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-xs btn-outline-primary" style="font-size: 11px; font-weight: 700; border-radius: 6px;" title="Bu sürümü aktif temel çizgi referansı yap">
                                                            Aktif Yap
                                                        </button>
                                                    </form>
                                                @endif
                                                <form method="POST" action="{{ route('projects.baselines.destroy', [$project->id, $b->id]) }}" onsubmit="return confirm('Bu temel çizgi sürümünü silmek istediğinize emin misiniz?');" style="margin: 0;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-outline-danger" style="font-size: 11px; border-radius: 6px;">
                                                        Sil
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span style="color: #94a3b8; font-size: 11px;">👁️ Salt Okunur</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4" style="color: #94a3b8;">
                                        Bu proje için henüz dondurulmuş bir Temel Çizgi (Baseline) kaydı bulunmuyor.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- AKTİF TEMEL ÇİZGİ GÖREV SAPMA DETAYLARI (SCHEDULE VARIANCE TABLE) -->
            @if($activeBaseline && $activeBaseline->tasks->count() > 0)
                <div class="card mb-4" style="border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff;">
                    <div class="card-header bg-white" style="padding: 14px 20px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
                        <div style="font-weight: 800; color: #1e293b; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                            <span>📋</span> Aktif Temel Çizgi ({{ $activeBaseline->version }}) Görev Zaman Sapma Analizi
                        </div>
                        <span style="font-size: 11.5px; color: #64748b;">{{ $activeBaseline->tasks->count() }} Görev Kıyaslandı</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size: 12.5px; vertical-align: middle;">
                            <thead style="background: #f8fafc; color: #475569; font-weight: 700; border-bottom: 1.5px solid #e2e8f0;">
                                <tr>
                                    <th style="padding: 10px 16px;">Görev Adı</th>
                                    <th>Faz</th>
                                    <th>Temel Çizgi Bitiş</th>
                                    <th>Güncel Bitiş</th>
                                    <th>Takvim Sapması (Schedule Variance)</th>
                                    <th>Mevcut İlerleme</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activeBaseline->tasks as $bt)
                                    @php
                                        $currentTask = $bt->task;
                                        $var = $bt->getDaysVariance();
                                    @endphp
                                    <tr>
                                        <td style="padding: 10px 16px;">
                                            <strong>{{ $bt->title }}</strong>
                                            @if($bt->is_milestone)
                                                <span style="font-size: 10px; background: #fef3c7; color: #92400e; padding: 1px 5px; border-radius: 4px; margin-left: 4px;">Kilometre Taşı</span>
                                            @endif
                                        </td>
                                        <td>{{ $bt->phase?->name ?? '-' }}</td>
                                        <td>{{ $bt->baseline_due_date ? $bt->baseline_due_date->format('d.m.Y') : '-' }}</td>
                                        <td>{{ $currentTask?->due_date ? $currentTask->due_date->format('d.m.Y') : '-' }}</td>
                                        <td>
                                            @if($var === null)
                                                <span style="color: #94a3b8;">-</span>
                                            @elseif($var > 0)
                                                <span class="badge" style="background: #fee2e2; color: #b91c1c; font-size: 11px; border: 1px solid #f87171;">
                                                    ⚠️ +{{ $var }} Gün Gecikme
                                                </span>
                                            @elseif($var < 0)
                                                <span class="badge" style="background: #dcfce7; color: #166534; font-size: 11px; border: 1px solid #86efac;">
                                                    ✓ {{ abs($var) }} Gün Erken
                                                </span>
                                            @else
                                                <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 11px;">
                                                    Tam Zamanında (0g)
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <div class="progress" style="width: 80px; height: 6px; border-radius: 3px; background: #e2e8f0;">
                                                    <div class="progress-bar" style="width: {{ $currentTask?->progress_percentage ?? 0 }}%; background: #0284c7;"></div>
                                                </div>
                                                <span style="font-size: 11px; font-weight: 700; color: #475569;">%{{ (int) round($currentTask?->progress_percentage ?? 0) }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- MODALLER: KALİTE & TEMEL ÇİZGİ (NATIVE OVERLAY ARŞİTEKTÜRÜ) -->
        <!-- 1. PROJE İÇİ KALİTE KRİTERİ EKLE MODALI -->
        <div id="modalProjNewQuality" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.65); align-items: center; justify-content: center; z-index: 10000; backdrop-filter: blur(4px);" onclick="if(event.target === this) closeProjQualityModal()">
            <div style="background: #ffffff; width: 680px; max-width: 94vw; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35); overflow: hidden; display: flex; flex-direction: column; max-height: 90vh;">
                <div style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff; padding: 16px 22px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                    <h5 style="font-size: 15px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <span>🔬</span> {{ $project->code }} Projesine Kalite Kriteri Ekle
                    </h5>
                    <button type="button" onclick="closeProjQualityModal()" style="background: none; border: none; color: #ffffff; font-size: 22px; cursor: pointer; line-height: 1; padding: 0;">&times;</button>
                </div>
                <form method="POST" action="{{ route('quality.store') }}" enctype="multipart/form-data" style="margin: 0; display: flex; flex-direction: column; overflow: hidden;">
                    @csrf
                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                    <div style="padding: 22px; overflow-y: auto; max-height: calc(90vh - 130px); font-size: 13px;">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" style="font-weight: 700; color: #0f172a;">Kontrol Tipi *</label>
                                <select name="check_type" class="form-select" required style="border-radius: 8px; border: 1.5px solid #cbd5e1; height: 38px;">
                                    <option value="acceptance_criteria">Kabul Kriteri (Acceptance)</option>
                                    <option value="quality_gate">Faz Kalite Kapısı (Gate)</option>
                                    <option value="test_case">Test Vakası / Test Case</option>
                                    <option value="inspection">Saha Muayenesi (Inspection)</option>
                                    <option value="ehs_audit">İSG & Çevre Denetimi</option>
                                    <option value="fat">Fabrika Kabul Testi (FAT)</option>
                                    <option value="sat">Saha Kabul Testi (SAT)</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label" style="font-weight: 700; color: #0f172a;">Kalite Kontrolü & Test Başlığı *</label>
                                <input type="text" name="title" class="form-control" required placeholder="Örn: Güneş Panelleri Taşıyıcı Ray Tork Kalibrasyonu" style="border-radius: 8px; border: 1.5px solid #cbd5e1; height: 38px;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 700; color: #0f172a;">İlgili Faz / Aşama</label>
                                <select name="phase_id" class="form-select" style="border-radius: 8px; border: 1.5px solid #cbd5e1; height: 38px;">
                                    <option value="">-- Tüm Proje / Faz Bağımsız --</option>
                                    @foreach($project->phases as $ph)
                                        <option value="{{ $ph->id }}">{{ $ph->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 700; color: #0f172a;">İlgili Görev (Opsiyonel)</label>
                                <select name="task_id" class="form-select" style="border-radius: 8px; border: 1.5px solid #cbd5e1; height: 38px;">
                                    <option value="">-- Görev Bağımsız --</option>
                                    @foreach($project->tasks as $t)
                                        <option value="{{ $t->id }}">{{ $t->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" style="font-weight: 700; color: #0f172a;">Hedef Ölçüm / Standart Değer</label>
                                <input type="text" name="target_value" class="form-control" placeholder="Örn: 25.4" style="border-radius: 8px; border: 1.5px solid #cbd5e1; height: 38px;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" style="font-weight: 700; color: #0f172a;">Tolerans Aralığı</label>
                                <input type="text" name="tolerance" class="form-control" placeholder="Örn: ±0.2 mm veya min. 95%" style="border-radius: 8px; border: 1.5px solid #cbd5e1; height: 38px;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" style="font-weight: 700; color: #0f172a;">Birim</label>
                                <input type="text" name="unit" class="form-control" placeholder="Örn: bar, mm, °C, %" style="border-radius: 8px; border: 1.5px solid #cbd5e1; height: 38px;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 700; color: #0f172a;">Standart / Şartname Referansı</label>
                                <input type="text" name="standards_reference" class="form-control" placeholder="Örn: ISO 9001 Madde 8.6, TS EN 1090" style="border-radius: 8px; border: 1.5px solid #cbd5e1; height: 38px;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 700; color: #0f172a;">Testi Yapacak Personel</label>
                                <select name="checked_by" class="form-select" style="border-radius: 8px; border: 1.5px solid #cbd5e1; height: 38px;">
                                    <option value="">-- Atama Yapılmadı --</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label" style="font-weight: 700; color: #0f172a;">Test Yöntemi, Açıklaması ve Kabul Şartları</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="Test adımları, cihaz kalibrasyonu, numune alma koşulları..." style="border-radius: 8px; border: 1.5px solid #cbd5e1;"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label" style="font-weight: 700; color: #0f172a;">Kanıt / Sertifika / Şartname Dosyası</label>
                                <input type="file" name="evidence_file" class="form-control" style="border-radius: 8px; border: 1.5px solid #cbd5e1;">
                            </div>
                        </div>
                    </div>
                    <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 22px; display: flex; justify-content: flex-end; gap: 10px; flex-shrink: 0;">
                        <button type="button" onclick="closeProjQualityModal()" class="btn btn-secondary btn-sm" style="border-radius: 8px; font-weight: 600; padding: 7px 16px;">Vazgeç</button>
                        <button type="submit" class="btn btn-primary btn-sm" style="background: #0284c7; border-color: #0284c7; font-weight: 700; border-radius: 8px; padding: 7px 18px;">
                            ✓ Kriteri Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. PROJE İÇİ KALİTE UYGUNSUZLUĞU (NCR) AÇ MODALI -->
        <div id="modalProjNewNcr" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.65); align-items: center; justify-content: center; z-index: 10000; backdrop-filter: blur(4px);" onclick="if(event.target === this) closeProjNcrModal()">
            <div style="background: #ffffff; width: 680px; max-width: 94vw; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35); overflow: hidden; display: flex; flex-direction: column; max-height: 90vh;">
                <div style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: #ffffff; padding: 16px 22px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                    <h5 style="font-size: 15px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <span>🚨</span> {{ $project->code }} İçin Kalite Uygunsuzluğu (NCR) Aç
                    </h5>
                    <button type="button" onclick="closeProjNcrModal()" style="background: none; border: none; color: #ffffff; font-size: 22px; cursor: pointer; line-height: 1; padding: 0;">&times;</button>
                </div>
                <form method="POST" action="{{ route('quality.ncr.store') }}" enctype="multipart/form-data" style="margin: 0; display: flex; flex-direction: column; overflow: hidden;">
                    @csrf
                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                    <div style="padding: 22px; overflow-y: auto; max-height: calc(90vh - 130px); font-size: 13px;">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label" style="font-weight: 700; color: #0f172a;">Uygunsuzluk Başlığı *</label>
                                <input type="text" name="title" class="form-control" required placeholder="Örn: Kaynak Dikişinde Radyografik Çatlak Tespiti" style="border-radius: 8px; border: 1.5px solid #cbd5e1; height: 38px;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" style="font-weight: 700; color: #0f172a;">Kritiklik Seviyesi *</label>
                                <select name="severity" class="form-select" required style="border-radius: 8px; border: 1.5px solid #cbd5e1; height: 38px;">
                                    <option value="minor">Düşük (Minor)</option>
                                    <option value="major" selected>Orta / Önemli (Major)</option>
                                    <option value="critical">Kritik / Durdurucu (Critical)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 700; color: #0f172a;">Düzeltici Faaliyet Sorumlusu (CAPA Owner)</label>
                                <select name="assigned_to" class="form-select" style="border-radius: 8px; border: 1.5px solid #cbd5e1; height: 38px;">
                                    <option value="">-- Sorumlu Seçiniz --</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 700; color: #0f172a;">Giderilme Hedef Tarihi (Termin)</label>
                                <input type="date" name="due_date" class="form-control" style="border-radius: 8px; border: 1.5px solid #cbd5e1; height: 38px;">
                            </div>
                            <div class="col-12">
                                <label class="form-label" style="font-weight: 700; color: #0f172a;">Uygunsuzluk Detayı & Sapma Tanımı *</label>
                                <textarea name="description" class="form-control" rows="2" required placeholder="Hatanın oluş şekli, nerede ve hangi parçada tespit edildiği..." style="border-radius: 8px; border: 1.5px solid #cbd5e1;"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label" style="font-weight: 700; color: #0f172a;">Kök Neden Analizi (Root Cause)</label>
                                <textarea name="root_cause" class="form-control" rows="2" placeholder="Neden oldu? Operatör hatası, malzeme kusuru, parametre sapması..." style="border-radius: 8px; border: 1.5px solid #cbd5e1;"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label" style="font-weight: 700; color: #0f172a;">Planlanan Düzeltici / Önleyici Faaliyet (CAPA)</label>
                                <textarea name="corrective_action" class="form-control" rows="2" placeholder="Tekrarını önlemek için ne yapılacak? Parça değişimi, revizyon, eğitim..." style="border-radius: 8px; border: 1.5px solid #cbd5e1;"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label" style="font-weight: 700; color: #0f172a;">Hata Fotoğrafı / Ölçüm Raporu Kanıtı</label>
                                <input type="file" name="evidence_file" class="form-control" style="border-radius: 8px; border: 1.5px solid #cbd5e1;">
                            </div>
                        </div>
                    </div>
                    <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 22px; display: flex; justify-content: flex-end; gap: 10px; flex-shrink: 0;">
                        <button type="button" onclick="closeProjNcrModal()" class="btn btn-secondary btn-sm" style="border-radius: 8px; font-weight: 600; padding: 7px 16px;">Vazgeç</button>
                        <button type="submit" class="btn btn-danger btn-sm" style="background: #dc2626; border-color: #dc2626; font-weight: 700; border-radius: 8px; padding: 7px 18px;">
                            🚨 NCR Kaydını Aç
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 3. KALİTE TESTİNİ DEĞERLENDİR MODALI -->
        <div id="modalProjEvaluateQuality" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.65); align-items: center; justify-content: center; z-index: 10000; backdrop-filter: blur(4px);" onclick="if(event.target === this) closeProjEvaluateQuality()">
            <div style="background: #ffffff; width: 560px; max-width: 94vw; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35); overflow: hidden; display: flex; flex-direction: column; max-height: 90vh;">
                <div style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: #ffffff; padding: 16px 22px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                    <h5 style="font-size: 15px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <span>📋</span> Kalite Testini / Kabul Kriterini Değerlendir
                    </h5>
                    <button type="button" onclick="closeProjEvaluateQuality()" style="background: none; border: none; color: #ffffff; font-size: 22px; cursor: pointer; line-height: 1; padding: 0;">&times;</button>
                </div>
                <form method="POST" id="formProjEvaluateQuality" enctype="multipart/form-data" style="margin: 0; display: flex; flex-direction: column; overflow: hidden;">
                    @csrf
                    <div style="padding: 22px; overflow-y: auto; max-height: calc(90vh - 130px); font-size: 13px;">
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 14px; margin-bottom: 16px;">
                            <div style="font-size: 11.5px; font-weight: 700; color: #64748b;">Kriter Başlığı:</div>
                            <div id="evalProjQualityTitle" style="font-size: 13.5px; font-weight: 800; color: #0284c7; margin-top: 2px;"></div>
                            <div style="font-size: 11.5px; font-weight: 700; color: #64748b; margin-top: 6px;">Hedef / Standart:</div>
                            <div id="evalProjQualityTarget" style="font-size: 12.5px; color: #334155;"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; color: #0f172a;">Ölçülen Fiili Değer</label>
                            <input type="text" name="actual_value" id="evalProjActualValue" class="form-control" placeholder="Örn: 25.3" style="border-radius: 8px; border: 1.5px solid #cbd5e1; height: 38px; font-family: 'JetBrains Mono', monospace;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; color: #0f172a;">Değerlendirme Sonucu *</label>
                            <select name="status" id="evalProjStatus" class="form-select" required style="border-radius: 8px; border: 1.5px solid #cbd5e1; font-weight: 700; height: 38px;">
                                <option value="passed">✅ Geçti (Passed / Kabul)</option>
                                <option value="failed">❌ Kaldı / Reddedildi (Failed)</option>
                                <option value="conditionally_passed">⚠️ Şartlı Kabul Edildi (Conditionally Passed)</option>
                                <option value="in_progress">⏳ Test Süreci Devam Ediyor</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; color: #0f172a;">Test Notları & Değerlendirme Raporu</label>
                            <textarea name="evaluation_notes" id="evalProjNotes" class="form-control" rows="2" placeholder="Ölçüm ortamı, kullanılan cihaz seri no, gözlemler..." style="border-radius: 8px; border: 1.5px solid #cbd5e1;"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; color: #0f172a;">Test Kanıtı / Ölçüm Raporu Dosyası</label>
                            <input type="file" name="evidence_file" class="form-control" style="border-radius: 8px; border: 1.5px solid #cbd5e1;">
                        </div>
                    </div>
                    <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 22px; display: flex; justify-content: flex-end; gap: 10px; flex-shrink: 0;">
                        <button type="button" onclick="closeProjEvaluateQuality()" class="btn btn-secondary btn-sm" style="border-radius: 8px; font-weight: 600; padding: 7px 16px;">İptal</button>
                        <button type="submit" class="btn btn-success btn-sm" style="background: #16a34a; border-color: #16a34a; font-weight: 700; border-radius: 8px; padding: 7px 18px;">
                            ✓ Değerlendirmeyi Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 4. YENİ TEMEL ÇİZGİ DONDUR (FREEZE BASELINE) MODALI -->
        <div id="modalFreezeBaseline" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.65); align-items: center; justify-content: center; z-index: 10000; backdrop-filter: blur(4px);" onclick="if(event.target === this) closeFreezeBaselineModal()">
            <div style="background: #ffffff; width: 560px; max-width: 94vw; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35); overflow: hidden; display: flex; flex-direction: column; max-height: 90vh;">
                <div style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); color: #ffffff; padding: 16px 22px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                    <h5 style="font-size: 15px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <span>🔒</span> Proje Temel Çizgisini Dondur (Baseline Snapshot)
                    </h5>
                    <button type="button" onclick="closeFreezeBaselineModal()" style="background: none; border: none; color: #ffffff; font-size: 22px; cursor: pointer; line-height: 1; padding: 0;">&times;</button>
                </div>
                <form method="POST" action="{{ route('projects.baselines.store', $project->id) }}" style="margin: 0; display: flex; flex-direction: column; overflow: hidden;">
                    @csrf
                    <div style="padding: 22px; overflow-y: auto; max-height: calc(90vh - 130px); font-size: 13px;">
                        <div class="alert alert-warning mb-3" style="font-size: 12px; border-radius: 10px; background: #fffbeb; border: 1px solid #fef3c7; color: #92400e;">
                            <span>ℹ️</span> <strong>Temel Çizgi (Baseline):</strong> Projenin şu anki planlanan bütçesi ({{ number_format($project->planned_budget ?? 0, 2) }} {{ $project->currency ?? 'TRY' }}), faz ve görev tarihleri kilitlenerek saklanacaktır. Gelecekteki takvim ve maliyet sapmaları bu referansa göre kıyaslanacaktır.
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; color: #0f172a;">Temel Çizgi Sürüm Kodu *</label>
                            <input type="text" name="version" class="form-control" value="v{{ $project->baselines->count() + 1 }}.0" required style="border-radius: 8px; border: 1.5px solid #cbd5e1; height: 38px; font-weight: 700;">
                            <small class="text-muted" style="font-size: 11px;">Örn: v1.0 (Orijinal Onay), v2.0 (Bütçe Revizyonu)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; color: #0f172a;">Temel Çizgi Adı / Etiketi</label>
                            <input type="text" name="name" class="form-control" value="{{ $project->baselines->count() === 0 ? 'Orijinal Onaylı Proje Planı' : 'Proje Revizyon Temel Çizgisi' }}" placeholder="Örn: 2026 Q1 Onaylı Temel Çizgi" style="border-radius: 8px; border: 1.5px solid #cbd5e1; height: 38px;">
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; color: #0f172a;">Dondurma Sebebi & Açıklama</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Bu temel çizginin alınma gerekçesi veya yönetim kararı..." style="border-radius: 8px; border: 1.5px solid #cbd5e1;"></textarea>
                        </div>

                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="set_active" value="1" id="switchSetActive" checked>
                            <label class="form-check-label" for="switchSetActive" style="font-weight: 700; font-size: 12.5px; color: #0f172a;">
                                Aktif Kıyaslama Referansı Olarak Ayarla (Gantt & EVM)
                            </label>
                        </div>
                    </div>
                    <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 22px; display: flex; justify-content: flex-end; gap: 10px; flex-shrink: 0;">
                        <button type="button" onclick="closeFreezeBaselineModal()" class="btn btn-secondary btn-sm" style="border-radius: 8px; font-weight: 600; padding: 7px 16px;">Vazgeç</button>
                        <button type="submit" class="btn btn-primary btn-sm" style="background: #4f46e5; border-color: #4f46e5; font-weight: 700; border-radius: 8px; padding: 7px 18px;">
                            <span>🔒</span> Temel Çizgiyi Dondur ve Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>

            <!-- TASLAĞI PAYLAŞ MODALI -->
        <div id="modalShareDraft" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.65); align-items: center; justify-content: center; z-index: 10000; backdrop-filter: blur(4px);">
            <div style="background: #ffffff; width: 560px; max-width: 94vw; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35); overflow: hidden; display: flex; flex-direction: column; max-height: 90vh;">
                <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 18px;">👥</span>
                        <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #ffffff;">Taslak Projeyi İncelemeye Paylaş</h3>
                    </div>
                    <button type="button" onclick="closeShareDraftModal()" style="background: transparent; border: none; color: #ffffff; font-size: 18px; cursor: pointer;">✕</button>
                </div>

                <form action="{{ route('projects.share_draft', $project->id) }}" method="POST" style="padding: 20px; overflow-y: auto; flex: 1;">
                    @csrf

                    <!-- 1. Dahili Kullanıcı Seçimi (Modern Dropdown & Multi-Select) -->
                    <div class="form-group mb-3">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <label class="form-label" style="font-weight: 700; font-size: 12.5px; color: #334155; margin: 0;">
                                👥 Dahili Paydaşlar (Kullanıcı Seçimi)
                            </label>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span id="selectedReviewerCountBadge" class="badge" style="background: #eff6ff; color: #0284c7; font-size: 11px; padding: 2px 7px; border-radius: 6px;">
                                    0 Kişi Seçili
                                </span>
                                <button type="button" onclick="clearSelectedReviewers()" style="background: none; border: none; color: #64748b; font-size: 11px; cursor: pointer; text-decoration: underline; padding: 0;">Temizle</button>
                            </div>
                        </div>

                        <!-- Modern Search & Checklist Container -->
                        <div style="border: 1.5px solid #cbd5e1; border-radius: 10px; background: #ffffff; overflow: hidden;">
                            <div style="padding: 8px 10px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 8px;">
                                <span style="color: #94a3b8; font-size: 13px;">🔍</span>
                                <input type="text" id="reviewerSearchInput" oninput="filterReviewerList(this.value)" placeholder="Paydaş adı, departman veya unvan ara..." style="width: 100%; border: none; background: transparent; font-size: 12.5px; outline: none; color: #1e293b;">
                            </div>
                            <div id="reviewerChecklist" style="max-height: 140px; overflow-y: auto; padding: 6px;">
                                @foreach($users as $u)
                                    @if($u->id !== auth()->id())
                                        <label class="reviewer-option-row" data-search="{{ strtolower($u->name . ' ' . ($u->unvan ?? '') . ' ' . ($u->bolum?->name ?? '')) }}" style="display: flex; align-items: center; justify-content: space-between; padding: 6px 8px; border-radius: 6px; cursor: pointer; transition: background 0.12s; margin-bottom: 2px;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                            <div style="display: flex; align-items: center; gap: 10px; overflow: hidden;">
                                                <input type="checkbox" name="reviewer_ids[]" value="{{ $u->id }}" onchange="updateReviewerCount()" style="accent-color: #0284c7; width: 16px; height: 16px; cursor: pointer;">
                                                <div style="overflow: hidden;">
                                                    <div style="font-size: 12.5px; font-weight: 650; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                        {{ $u->name }}
                                                    </div>
                                                    <div style="font-size: 11px; color: #64748b;">
                                                        {{ $u->bolum?->name ?? 'Merkez' }} • {{ $u->unvan ?? 'Personel' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                            💡 Seçilen kullanıcılara hem <strong>Zil Bildirimi</strong> hem de <strong>E-Posta</strong> gönderilir.
                        </div>
                    </div>

                    <!-- 2. Harici E-Posta Paylaşımı (Geçiş Anahtarlı Dış Paydaş) -->
                    <div class="form-group mb-3" style="background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 10px; padding: 12px 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <label class="form-label" style="font-weight: 750; font-size: 12.5px; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 6px;">
                                <span>🌐</span> Harici / Dış Paydaş E-Posta Adresi
                            </label>
                            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 10.5px; font-weight: 700; padding: 2px 6px; border-radius: 4px;">
                                🔑 Güvenli Token & Passkey
                            </span>
                        </div>
                        <input type="text" name="external_emails" class="form-control" placeholder="ornek@dispaydas.com (Birden fazlaysa virgülle ayırınız)" style="font-size: 12.5px; height: 38px; border-radius: 7px;">
                        <div style="font-size: 11px; color: #475569; margin-top: 6px; line-height: 1.4;">
                            🛡️ Dış paydaşa, <strong>Bildirim Matrisi</strong>'ndeki şablon kullanılarak yalnızca bu projeye (<strong>{{ $project->code }}</strong>) özel geçiş anahtarı ve link iletilir. Başka projelere erişemez ve erişimi dilediğiniz an tek tıkla kesebilirsiniz.
                        </div>
                    </div>

                    <!-- 3. İnceleme Notu / Talebiniz -->
                    <div class="form-group mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 12.5px; color: #334155;">İnceleme Notu / Talebiniz</label>
                        <textarea name="note" class="form-control" rows="2" style="font-size: 12.5px; border-radius: 7px;" placeholder="Lütfen proje hedefleri ve bütçe planını inceleyerek görüş ve önerilerinizi iletiniz...">Lütfen taslak proje detaylarını inceleyerek görüş ve önerilerinizi iletiniz.</textarea>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 14px;">
                        <button type="button" class="btn btn-secondary" onclick="closeShareDraftModal()" style="font-size: 12.5px;">Vazgeç</button>
                        <button type="submit" class="btn btn-primary" style="font-weight: 700; font-size: 12.5px; display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 8px;">
                            <span>🚀</span> Taslağı Paylaş ve Bildirim Gönder
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- HARİCİ PAYLAŞIMLAR YÖNETİMİ MODALI -->
        <div id="modalExternalShares" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.65); align-items: center; justify-content: center; z-index: 10000; backdrop-filter: blur(4px);">
            <div style="background: #ffffff; width: 640px; max-width: 94vw; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35); overflow: hidden; display: flex; flex-direction: column; max-height: 85vh;">
                <div style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 18px;">🌐</span>
                        <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #ffffff;">Harici Dış Paydaş Paylaşımları</h3>
                    </div>
                    <button type="button" onclick="closeExternalSharesModal()" style="background: transparent; border: none; color: #ffffff; font-size: 18px; cursor: pointer;">✕</button>
                </div>
                <div style="padding: 16px 20px; overflow-y: auto; flex: 1;">
                    <div style="font-size: 12.5px; color: #475569; margin-bottom: 14px; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 10px 14px; line-height: 1.5;">
                        💡 <strong>Nasıl Çalışır?</strong> Bu bağlantı, sisteme kayıtlı olmayan dış paydaşların (danışman, tedarikçi, müşteri) taslak projeyi güvenle inceleyip revizyon/görüş bildirmesi içindir. <strong>"İnceleme Sayfasını Aç ↗"</strong> butonu, dış paydaşın göreceği sade arayüzün önizlemesini yeni sekmede açar.
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        @forelse($project->draftShares as $share)
                            <div style="border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 14px; background: {{ $share->isActive() ? '#ffffff' : '#f8fafc' }}; display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
                                <div>
                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 3px;">
                                        <strong style="font-size: 13px; color: #0f172a;">{{ $share->email }}</strong>
                                        @if($share->isActive())
                                            <span class="badge" style="background: #dcfce7; color: #15803d; font-size: 10.5px; padding: 2px 6px; border-radius: 4px;">🟢 Aktif</span>
                                        @else
                                            <span class="badge" style="background: #fee2e2; color: #b91c1c; font-size: 10.5px; padding: 2px 6px; border-radius: 4px;">🔴 Erişim Kesildi</span>
                                        @endif
                                    </div>
                                    <div style="font-size: 11px; color: #64748b;">
                                        Geçiş Anahtarı: <code style="background: #f1f5f9; padding: 1px 5px; border-radius: 3px; font-weight: 700; color: #0284c7;">{{ $share->passkey }}</code>
                                        • Paylaşan: {{ $share->sharedBy?->name ?? 'Sistem' }}
                                        • {{ $share->created_at->format('d.m.Y H:i') }}
                                        @if($share->access_count > 0)
                                            • <span style="color: #0369a1; font-weight: 600;">{{ $share->access_count }} Kez İncelendi</span>
                                        @endif
                                    </div>
                                    @if($share->isActive())
                                        <div style="margin-top: 6px; display: flex; align-items: center; gap: 8px;">
                                            <a href="{{ $share->public_access_url }}" target="_blank" style="font-size: 11px; color: #0284c7; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                                🔗 İnceleme Sayfasını Aç ↗
                                            </a>
                                            <button type="button" class="btn btn-xs btn-outline-secondary" onclick="navigator.clipboard.writeText('{{ $share->public_access_url }}'); alert('Harici erişim bağlantısı panoya kopyalandı:\n\n{{ $share->public_access_url }}');" style="font-size: 10.5px; padding: 2px 7px; border-radius: 4px;">
                                                📋 URL Kopyala
                                            </button>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    @php
                                        $canRevokeShare = $canManage || auth()->user()->isAdmin() || auth()->user()->isDeveloper() || (int) $share->shared_by_user_id === (int) auth()->id();
                                    @endphp
                                    @if($share->isActive() && $canRevokeShare)
                                        <form action="{{ route('projects.draft_shares.revoke', [$project->id, $share->id]) }}" method="POST" onsubmit="return confirm('Bu e-posta adresinin taslak projeye harici erişimini derhal kesmek istediğinize emin misiniz?');" style="margin: 0;">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-outline-danger" style="font-weight: 700; font-size: 11px; padding: 4px 10px; border-radius: 6px; border: 1px solid #ef4444; color: #dc2626; background: #ffffff;">
                                                🚫 Erişimi Kes
                                            </button>
                                        </form>
                                    @else
                                        <span style="font-size: 11px; color: #94a3b8; font-style: italic;">
                                            {{ $share->revoked_at ? $share->revoked_at->format('d.m H:i') . ' İptal Edildi' : 'İşlem Kapalı' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div style="text-align: center; padding: 24px; color: #94a3b8; font-size: 12.5px;">
                                Henüz harici paylaşılan dış e-posta adresi bulunmuyor.
                            </div>
                        @endforelse
                    </div>
                </div>
                <div style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: right;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="closeExternalSharesModal()">Kapat</button>
                </div>
            </div>
        </div>

        <!-- ALAN BAZLI ÖNERİ EKLEME MODALI -->
        <div id="modalAddSuggestion" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.65); align-items: center; justify-content: center; z-index: 10000; backdrop-filter: blur(4px);">
            <div style="background: #ffffff; width: 480px; max-width: 92vw; border-radius: 14px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35); overflow: hidden;">
                <div style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 18px;">💬</span>
                        <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #ffffff;">Taslak İçin Öneri / Görüş Ekle</h3>
                    </div>
                    <button type="button" onclick="closeAddSuggestionModal()" style="background: transparent; border: none; color: #ffffff; font-size: 18px; cursor: pointer;">✕</button>
                </div>
                <form action="{{ route('projects.store_draft_suggestion', $project->id) }}" method="POST" style="padding: 20px;">
                    @csrf
                    <input type="hidden" name="field_key" id="suggestionFieldKey" value="general">
                    <input type="hidden" name="field_label" id="suggestionFieldLabel" value="Genel Görüş">

                    <div style="background: #e0f2fe; border: 1px solid #bae6fd; border-radius: 8px; padding: 8px 12px; margin-bottom: 14px; font-size: 12.5px; color: #0369a1; font-weight: 600;">
                        İlgili Alan: <span id="suggestionFieldDisplay" style="font-weight: 800; color: #0284c7;">-</span>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 13px; color: #334155;">Öneriniz / Değerlendirmeniz <span class="text-danger">*</span></label>
                        <textarea name="suggestion_text" id="suggestionTextInput" class="form-control" rows="4" required style="font-size: 13px;" placeholder="Bu alan hakkında düşüncenizi, önerilen revizyonu veya ilave edilmesini istediğiniz detayları belirtiniz..."></textarea>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" class="btn btn-secondary" onclick="closeAddSuggestionModal()" style="font-size: 13px;">Vazgeç</button>
                        <button type="submit" class="btn btn-primary" style="font-weight: 700; font-size: 13px;">
                            ✓ Öneriyi İlet
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- GELEN ÖNERİLERİ GÖR LİSTESİ MODALI -->
        <div id="modalViewSuggestions" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.65); align-items: center; justify-content: center; z-index: 10000; backdrop-filter: blur(4px);">
            <div style="background: #ffffff; width: 680px; max-width: 94vw; max-height: 85vh; border-radius: 14px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35); overflow: hidden; display: flex; flex-direction: column;">
                <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 18px;">💡</span>
                        <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #ffffff;">Paydaş Önerileri & Görüş Listesi ({{ $project->draftSuggestions->count() }})</h3>
                    </div>
                    <button type="button" onclick="closeViewSuggestionsModal()" style="background: transparent; border: none; color: #ffffff; font-size: 18px; cursor: pointer;">✕</button>
                </div>
                <div style="padding: 20px; overflow-y: auto; flex: 1;">
                    @forelse($project->draftSuggestions->sortByDesc('created_at') as $sug)
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; margin-bottom: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 6px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 28px; height: 28px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700;">
                                        {{ mb_substr($sug->user->name ?? ($sug->external_email ?? 'H'), 0, 1) }}
                                    </div>
                                    <strong style="font-size: 13px; color: #0f172a;">{{ $sug->user->name ?? ($sug->external_email ?? 'Harici Paydaş') }}</strong>
                                    <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 11px; font-weight: 700;">
                                        📍 {{ $sug->field_label }}
                                    </span>
                                </div>
                                <span style="font-size: 11px; color: #64748b;">
                                    📅 {{ $sug->created_at ? $sug->created_at->translatedFormat('d F Y, H:i') : '-' }}
                                </span>
                            </div>
                            <div style="font-size: 13px; color: #334155; line-height: 1.5; background: #ffffff; border: 1px solid #edf2f7; border-radius: 6px; padding: 10px 12px;">
                                {{ $sug->suggestion_text }}
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; padding: 36px 20px; color: #64748b;">
                            <span style="font-size: 32px; display: block; margin-bottom: 8px;">💭</span>
                            <strong style="display: block; font-size: 14px; color: #0f172a; margin-bottom: 4px;">Henüz paydaş önerisi bulunmuyor</strong>
                            <span style="font-size: 12px;">"Taslağı Paylaş" butonu ile paydaşlarınızı incelemeye davet edebilirsiniz.</span>
                        </div>
                    @endforelse
                </div>
                <div style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeViewSuggestionsModal()" style="font-size: 13px;">Kapat</button>
                </div>
            </div>
        </div>
        <!-- ONAYA GÖNDERME MODALI (DOA & BÜTÇE EŞİK MOTORU) -->
        <div id="modalApproval" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 9999;">
            <div style="background: #fff; width: 480px; border-radius: var(--radius-md); padding: 24px; box-shadow: var(--shadow-lg);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                    <div style="font-size: 24px;">⚖️</div>
                    <div>
                        <h3 style="font-size: 16px; font-weight: 700; color: var(--navy); margin: 0;">Projeyi Bütçe Onayına Gönder</h3>
                        <div style="font-size: 12px; color: #64748b;">Yetki Devri (DOA) ve Bütçe Eşik Kademeleri</div>
                    </div>
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 12.5px; color: #475569; font-weight: 600;">Planlanan Proje Bütçesi:</span>
                        <span style="font-size: 14px; color: #0284c7; font-weight: 800;">
                            {{ number_format($project->planned_budget, 2, ',', '.') }} {{ $project->currency ?? 'TRY' }}
                        </span>
                    </div>
                    <div style="font-size: 11.5px; color: #64748b; margin-top: 6px; line-height: 1.4;">
                        Sistem, planlanan bütçeyi baz kur üzerinden otomatik hesaplayacak ve tanımlı <strong>Bütçe Eşik Kademesine (DOA)</strong> göre ilgili yöneticiye, kurula veya doğrudan devreye alma sürecine sevk edecektir.
                    </div>
                </div>

                <form action="{{ route('projects.submit_approval', $project->id) }}" method="POST">
                    @csrf
                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 18px;">
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalApproval').style.display='none'">Vazgeç</button>
                        <button type="submit" class="btn btn-primary fw-bold px-3">🚀 Onay Sürecini Başlat</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- GÖREV VE ROL DEVİR SİHİRBAZI MODALI (HANDOVER WIZARD) -->
        <div id="modalHandoverMember" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.65); align-items: center; justify-content: center; z-index: 10000; backdrop-filter: blur(4px);" onclick="if(event.target === this) closeHandoverModal()">
            <div style="background: #ffffff; width: 540px; max-width: 94vw; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35); overflow: hidden;">
                <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 18px 22px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 20px;">🔄</span>
                        <div>
                            <h3 style="margin: 0; font-size: 15px; font-weight: 800; color: #ffffff;">Ekip Üyesi Görev & Rol Devir Sihirbazı</h3>
                            <div style="font-size: 11.5px; color: #94a3b8;">Personel ayrılışı, istifa veya yurt dışı görevi devir protokolü</div>
                        </div>
                    </div>
                    <button type="button" onclick="closeHandoverModal()" style="background: transparent; border: none; color: #ffffff; font-size: 18px; cursor: pointer;">✕</button>
                </div>
                <form action="{{ route('projects.stakeholders.handover', $project->id) }}" method="POST" style="padding: 22px;">
                    @csrf
                    <input type="hidden" name="from_user_id" id="handoverFromUserId">

                    <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 10px; padding: 12px 14px; margin-bottom: 16px;">
                        <div style="font-size: 12px; color: #991b1b; font-weight: 700; text-transform: uppercase; margin-bottom: 3px;">
                            Ayrılan / Görevi Devreden Personel:
                        </div>
                        <div style="font-size: 14px; font-weight: 800; color: #b91c1c;" id="handoverFromUserNameDisplay">
                            -
                        </div>
                        <div style="font-size: 11.5px; color: #7f1d1d; margin-top: 4px;">
                            ⚠️ Bu personel projede <strong>Pasif (Ayrıldı)</strong> durumuna çekilecek ve seçtiğiniz unsurlar yeni üyeye aktarılacaktır.
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 12.5px; color: #334155;">
                            Sorumlulukları Devralacak Yeni Ekip Üyesi <span class="text-danger">*</span>
                        </label>
                        <select name="to_user_id" class="form-control" required style="height: 42px; font-size: 13px; border-radius: 8px;">
                            <option value="">-- Personel Seçiniz (Mevcut Üye veya Şirket Geneli) --</option>
                            <optgroup label="Proje Ekibindeki Mevcut Üyeler">
                                @foreach($project->stakeholders as $stk)
                                    @if($stk->user && (bool) ($stk->is_active ?? true))
                                        <option value="{{ $stk->user_id }}">{{ $stk->user->name }} ({{ $stk->user->unvan ?? 'Ekip Üyesi' }})</option>
                                    @endif
                                @endforeach
                            </optgroup>
                            <optgroup label="Diğer Şirket Personelleri (Ekibe Dahil Edilecekler)">
                                @php
                                    $existingStakeholderIds = $project->stakeholders->pluck('user_id')->toArray();
                                @endphp
                                @foreach($users as $u)
                                    @if(!in_array($u->id, $existingStakeholderIds, true))
                                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->bolum?->name ?? 'Merkez' }} - {{ $u->unvan ?? 'Personel' }})</option>
                                    @endif
                                @endforeach
                            </optgroup>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 12.5px; color: #334155;">
                            Ayrılma / Devir Gerekçesi <span class="text-danger">*</span>
                        </label>
                        <select name="reason" class="form-control mb-2" onchange="if(this.value==='other') { document.getElementById('customReasonWrapper').style.display='block'; } else { document.getElementById('customReasonWrapper').style.display='none'; document.getElementById('customReasonInput').value=this.value; }" style="height: 38px; font-size: 12.5px;">
                            <option value="İşten Ayrılış / İstifa">İşten Ayrılış / İstifa</option>
                            <option value="Yurt Dışı Görevlendirmesi">Yurt Dışı Görevlendirmesi</option>
                            <option value="Bölüm / Departman Değişikliği">Bölüm / Departman Değişikliği</option>
                            <option value="Askerlik / Uzun Süreli Rapor">Askerlik / Uzun Süreli Rapor</option>
                            <option value="other">Diğer (Özel Açıklama Giriniz)</option>
                        </select>
                        <div id="customReasonWrapper" style="display: none;">
                            <input type="text" id="customReasonInput" name="custom_reason" class="form-control" placeholder="Ayrılma gerekçesini yazınız..." style="font-size: 12.5px;">
                        </div>
                    </div>

                    <!-- Devredilecek Unsurlar (Checklist) -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; margin-bottom: 18px;">
                        <span style="font-size: 12px; font-weight: 750; color: #334155; display: block; margin-bottom: 8px;">
                            📦 Devredilecek Unsurlar:
                        </span>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: #1e293b; margin-bottom: 6px; cursor: pointer;">
                            <input type="checkbox" name="transfer_tasks" value="1" checked style="accent-color: #0284c7; width: 16px; height: 16px;">
                            <span>📋 <strong>Açık Görevler:</strong> Tamamlanmamış tüm görevler yeni üyeye atanır.</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: #1e293b; margin: 0; cursor: pointer;">
                            <input type="checkbox" name="transfer_permissions" value="1" checked style="accent-color: #0284c7; width: 16px; height: 16px;">
                            <span>⚡ <strong>Proje İçi Mikro Yetkiler:</strong> Tanımlı delege izinler yeni üyeye aktarılır.</span>
                        </label>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" class="btn btn-secondary" onclick="closeHandoverModal()" style="font-size: 13px;">Vazgeç</button>
                        <button type="submit" class="btn btn-danger fw-bold" style="font-size: 13px; display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px;">
                            <span>⚡</span> Devir İşlemini Onayla
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- PROJE LİDERİNİ DEĞİŞTİRME MODALI (MANAGER SUCCESSION) -->
        <div id="modalChangeManager" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.65); align-items: center; justify-content: center; z-index: 10000; backdrop-filter: blur(4px);" onclick="if(event.target === this) closeChangeManagerModal()">
            <div style="background: #ffffff; width: 520px; max-width: 94vw; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35); overflow: hidden;">
                <div style="background: linear-gradient(135deg, #b45309 0%, #78350f 100%); color: #ffffff; padding: 18px 22px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 22px;">👑</span>
                        <div>
                            <h3 style="margin: 0; font-size: 15px; font-weight: 800; color: #ffffff;">Proje Liderini Değiştirme</h3>
                            <div style="font-size: 11.5px; color: #fde68a;">Resmi proje yönetim devir protokolü</div>
                        </div>
                    </div>
                    <button type="button" onclick="closeChangeManagerModal()" style="background: transparent; border: none; color: #ffffff; font-size: 18px; cursor: pointer;">✕</button>
                </div>
                <form action="{{ route('projects.change_manager', $project->id) }}" method="POST" style="padding: 22px;">
                    @csrf
                    <div style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 10px; padding: 12px 14px; margin-bottom: 16px;">
                        <div style="font-size: 12px; color: #92400e; font-weight: 700; text-transform: uppercase;">Mevcut Proje Lideri:</div>
                        <div style="font-size: 14.5px; font-weight: 800; color: #78350f; margin-top: 2px;">
                            {{ $project->manager?->name ?? 'Atanmamış' }}
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 12.5px; color: #334155;">
                            Atanacak Yeni Proje Lideri <span class="text-danger">*</span>
                        </label>
                        <select name="new_manager_id" class="form-control" required style="height: 42px; font-size: 13px; border-radius: 8px;">
                            <option value="">-- Yeni Proje Liderini Seçiniz --</option>
                            @foreach($users as $u)
                                @if((int) $u->id !== (int) $project->manager_id)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->bolum?->name ?? 'Merkez' }} - {{ $u->unvan ?? 'Yönetici' }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #1e293b; cursor: pointer;">
                            <input type="checkbox" name="keep_old_as_member" value="1" checked style="accent-color: #b45309; width: 16px; height: 16px;">
                            <span>Eski lideri projede <strong>Ekip Üyesi</strong> olarak tutmaya devam et.</span>
                        </label>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" style="font-weight: 700; font-size: 12.5px; color: #334155;">Devir Notu / Gerekçe</label>
                        <textarea name="notes" rows="2" class="form-control" placeholder="Proje liderliği devir gerekçesini belirtiniz..." style="font-size: 12.5px; border-radius: 8px;"></textarea>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                        <button type="button" class="btn btn-secondary" onclick="closeChangeManagerModal()" style="font-size: 13px;">Vazgeç</button>
                        <button type="submit" class="btn btn-warning fw-bold" style="font-size: 13px; display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; color: #000;">
                            <span>👑</span> Liderliği Resmi Olarak Devret
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @push('styles')
            <style>
                .tab-btn {
                    background: none;
                    border: none;
                    padding: 10px 16px;
                    font-size: 13px;
                    font-weight: 600;
                    color: var(--text-muted);
                    cursor: pointer;
                    border-bottom: 3px solid transparent;
                    margin-bottom: -2px;
                    transition: all 0.15s ease;
                }
                .tab-btn:hover { color: var(--navy); }
                .tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); font-weight: 700; }
            </style>
        @endpush

    <!-- YENİ GÖREV EKLEME MODALI -->
    <div id="createTaskModal" class="custom-modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; backdrop-filter: blur(4px); align-items: center; justify-content: center;">
        <div style="background: #ffffff; width: 100%; max-width: 580px; border-radius: 14px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25); overflow: hidden; margin: 20px; animation: modalFadeIn 0.2s ease;">
            <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 700;">
                    <span>➕</span> Yeni Proje Görevi Tanımla & Akışa Bağla
                </div>
                <button type="button" onclick="closeCreateTaskModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">&times;</button>
            </div>
            <form action="{{ route('projects.add_task', $project->id) }}" method="POST" id="createTaskForm" style="padding: 20px;">
                @csrf
                <div class="form-group mb-3">
                    <label style="font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 5px; display: block;">
                        Görev Başlığı <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="title" id="taskTitleInput" class="form-control" required placeholder="Örn: Mekanik Sac Kaynağı & Montajı" style="height: 40px; font-size: 13.5px;">
                </div>

                <div class="grid-2 mb-3">
                    <div class="form-group mb-0">
                        <label style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; display: block;">
                            Bağlı Olduğu Aşama / Faz <span class="text-danger">*</span>
                        </label>
                        <select name="phase_id" id="taskPhaseSelect" class="form-control" required style="height: 40px; font-size: 13px;">
                            @foreach($project->phases as $phase)
                                <option value="{{ $phase->id }}">{{ $phase->sort_order ?? $loop->iteration }}. {{ $phase->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; display: block;">
                            Öncül Görev (Akış Bağıntısı) <span class="text-muted" style="font-weight: 400;">(Opsiyonel)</span>
                        </label>
                        <select name="parent_id" id="taskParentSelect" class="form-control" style="height: 40px; font-size: 13px;">
                            <option value="">-- Doğrudan Faz Başlangıcına Bağla --</option>
                            @foreach($project->tasks as $pt)
                                <option value="{{ $pt->id }}">{{ $pt->title }} ({{ $pt->phase?->name ?? 'Faz' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid-2 mb-3">
                    <div class="form-group mb-0">
                        <label style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; display: block;">
                            Atanan Sorumlu Personel
                        </label>
                        <select name="assigned_to" id="taskAssigneeSelect" class="form-control" style="height: 40px; font-size: 13px;">
                            <option value="">-- Atanmamış (Boş) --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->unvan ?? ($u->roles->first()?->name ?? 'Personel') }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; display: block;">
                            Öncelik Seviyesi <span class="text-danger">*</span>
                        </label>
                        <select name="priority" class="form-control" required style="height: 40px; font-size: 13px;">
                            <option value="low">Düşük</option>
                            <option value="medium" selected>Orta</option>
                            <option value="high">Yüksek</option>
                            <option value="critical">Kritik / Acil</option>
                        </select>
                    </div>
                </div>

                <div class="grid-3 mb-3">
                    <div class="form-group mb-0">
                        <label style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; display: block;">
                            Etiket / Kategori
                        </label>
                        <input type="text" name="tag" class="form-control" placeholder="#İSG, #Mekanik vb." value="#Mekanik" style="height: 38px; font-size: 12.5px;">
                    </div>

                    <div class="form-group mb-0">
                        <label style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; display: block;">
                            Tahmini Efor (Saat)
                        </label>
                        <input type="number" step="0.5" name="estimated_hours" class="form-control" placeholder="16.0" value="16.0" style="height: 38px; font-size: 12.5px;">
                    </div>

                    <div class="form-group mb-0">
                        <label style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; display: block;">
                            Hedef Bitiş Tarihi
                        </label>
                        <input type="date" name="due_date" class="form-control" style="height: 38px; font-size: 12.5px;">
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; display: block;">
                        Açıklama ve Talimatlar <span class="text-muted" style="font-weight: 400;">(Opsiyonel)</span>
                    </label>
                    <textarea name="description" rows="2" class="form-control" placeholder="Görev detayları, teknik şartname veya güvenlik notları..."></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 10px; border-top: 1px solid #f1f5f9;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="closeCreateTaskModal()" style="padding: 8px 16px;">İptal</button>
                    <button type="submit" class="btn btn-success btn-sm" style="padding: 8px 20px; font-weight: 700;">⚡ Görevi Oluştur & Akışa Ekle</button>
                </div>
            </form>
        </div>
    </div>

    <!-- GÖREV HIZLI DURUM GÜNCELLEME MODALI -->
    <div id="quickTaskStatusModal" class="custom-modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; backdrop-filter: blur(4px); align-items: center; justify-content: center;">
        <div style="background: #ffffff; width: 100%; max-width: 440px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25); overflow: hidden; margin: 20px;">
            <div style="background: #0f172a; color: #fff; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 14px; font-weight: 700;" id="quickTaskModalTitle">Görev Durumu Güncelle</div>
                <button type="button" onclick="closeQuickTaskModal()" style="background: none; border: none; color: #94a3b8; font-size: 18px; cursor: pointer;">&times;</button>
            </div>
            <div style="padding: 18px;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 16px;">
                    <div style="font-weight: 700; color: #1e293b; font-size: 13.5px;" id="quickTaskTitleText">--</div>
                    <div style="font-size: 12px; color: #64748b; margin-top: 4px;" id="quickTaskAssigneeText">Sorumlu: --</div>
                </div>
                <div style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 8px;">Durumu Seçiniz:</div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="submitQuickTaskStatus('in_progress')" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 14px; font-weight: 600;">
                        <span>⏳ Süreçte / Çalışılıyor</span>
                        <span class="badge" style="background: #fef3c7; color: #d97706;">Pulsing Yanıp Söner</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="submitQuickTaskStatus('done')" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 14px; font-weight: 600;">
                        <span>✅ Tamamlandı & Onaylandı</span>
                        <span class="badge" style="background: #dcfce7; color: #15803d;">Yeşile Döner</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="submitQuickTaskStatus('todo')" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 14px; font-weight: 500;">
                        <span>📋 Yapılacak (Beklemede)</span>
                        <span class="badge" style="background: #f1f5f9; color: #475569;">Planlanan</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="submitQuickTaskStatus('blocked')" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 14px; font-weight: 500;">
                        <span>⛔ Tıkandı / Engel Var</span>
                        <span class="badge" style="background: #fee2e2; color: #b91c1c;">Kırmızı Pulsing</span>
                    </button>
                </div>
            </div>
            <div style="background: #f8fafc; padding: 10px 18px; display: flex; justify-content: flex-end; border-top: 1px solid #e2e8f0;">
                <button type="button" class="btn btn-sm btn-secondary" onclick="closeQuickTaskModal()">Kapat</button>
            </div>
        </div>
    </div>

        @push('scripts')
                <script>
            window.CAN_MANAGE_PROJECT = {{ $canManage ? "true" : "false" }};

            window.PROJECT_WORKFLOW_DATA = {
                projectId: {{ $project->id }},
                projectStatus: "{{ $project->status }}",
                phases: @json($workflowPhasesData ?? [])
            };


            // =========================================================================
            // İNTERAKTİF SÜREÇ & GÖREV İŞ AKIŞI MOTORU (DYNAMIC WORKFLOW ENGINE)
            // =========================================================================
            let currentActiveTaskId = null;

            function escapeHtml(str) {
                if (!str) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function openCreateTaskModal(phaseId = null) {
                const modal = document.getElementById('createTaskModal');
                if (!modal) return;
                if (phaseId) {
                    const select = document.getElementById('taskPhaseSelect');
                    if (select) select.value = phaseId;
                }
                modal.style.display = 'flex';
            }

            function closeCreateTaskModal() {
                const modal = document.getElementById('createTaskModal');
                if (modal) modal.style.display = 'none';
            }

            function openQuickTaskModal(taskId, title, assigneeName) {
                currentActiveTaskId = taskId;
                document.getElementById('quickTaskTitleText').textContent = title;
                document.getElementById('quickTaskAssigneeText').textContent = 'Sorumlu: ' + (assigneeName || 'Atanmamış');
                document.getElementById('quickTaskStatusModal').style.display = 'flex';
            }

            function closeQuickTaskModal() {
                document.getElementById('quickTaskStatusModal').style.display = 'none';
                currentActiveTaskId = null;
            }

            function toggleWorkflowCanvas() {
                const card = document.getElementById('projectWorkflowCanvasCard');
                const btn = document.getElementById('btnToggleWorkflow');
                if (!card) return;
                card.classList.toggle('collapsed');
                if (btn) {
                    btn.textContent = card.classList.contains('collapsed') ? '▶️ Genişlet' : '🔽 Daralt';
                }
                if (!card.classList.contains('collapsed')) {
                    setTimeout(drawWorkflowConnectors, 250);
                }
            }

            function updateLocalTaskStatus(taskId, newStatus) {
                if (!window.PROJECT_WORKFLOW_DATA || !window.PROJECT_WORKFLOW_DATA.phases) return;
                window.PROJECT_WORKFLOW_DATA.phases.forEach(phase => {
                    if (phase.tasks) {
                        phase.tasks.forEach(t => {
                            if (String(t.id) === String(taskId)) {
                                t.status = newStatus;
                            }
                        });
                    }
                });
                renderWorkflowCanvas();
            }

            function submitQuickTaskStatus(newStatus) {
                if (!currentActiveTaskId) return;
                const projectId = window.PROJECT_WORKFLOW_DATA ? window.PROJECT_WORKFLOW_DATA.projectId : null;
                if (!projectId) return;

                const url = `/projects/${projectId}/tasks/${currentActiveTaskId}/status`;

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: newStatus })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        closeQuickTaskModal();
                        updateLocalTaskStatus(currentActiveTaskId, newStatus);
                        if (typeof showToast === 'function') {
                            showToast(`✓ Görev durumu güncellendi: ${newStatus}`);
                        }
                    } else {
                        alert('Durum güncellenemedi: ' + (data.message || 'Hata'));
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Sunucu iletişim hatası oluştu!');
                });
            }

            function renderWorkflowCanvas() {
                const container = document.getElementById('workflowNodesContainer');
                if (!container || !window.PROJECT_WORKFLOW_DATA) return;

                const phases = window.PROJECT_WORKFLOW_DATA.phases || [];
                let html = '';

                // 1. START NODE
                html += `
                    <div class="wf-node wf-node-start" id="wf-node-start">
                        <span style="font-size: 16px;">🚀</span>
                        <div>
                            <div>Proje Başlangıcı</div>
                            <span class="text-xs" style="color: #166534; font-weight: 500;">Onaylandı</span>
                        </div>
                    </div>
                `;

                // 2. PHASE CLUSTERS & TASKS
                let foundFirstActive = false;

                phases.forEach((phase, pIdx) => {
                    const pNum = pIdx + 1;
                    const tasks = phase.tasks || [];
                    const hasEhs = phase.requires_ehs_approval;
                    const isEhsDone = !!phase.ehs_approved_at;

                    // Check if this cluster contains the active task
                    const isClusterActive = tasks.some(t => t.status === 'in_progress');

                    html += `
                        <div class="wf-phase-cluster ${isClusterActive ? 'has-active' : ''}" id="wf-cluster-phase-${phase.id}">
                            <div class="wf-phase-header">
                                <span>${pNum}. ${escapeHtml(phase.name)}</span>
                                ${window.CAN_MANAGE_PROJECT ? `<button type="button" class="btn btn-xs btn-outline-primary" onclick="openCreateTaskModal(${phase.id})" title="Bu faza yeni görev ekle" style="padding: 1px 6px; font-size: 10px; border-radius: 4px;">+ Görev</button>` : ''}
                            </div>
                            <div class="wf-phase-tasks-stack">
                    `;

                    if (tasks.length === 0) {
                        html += `
                            <div class="wf-task-node" id="wf-node-empty-${phase.id}" style="border-style: dashed; text-align: center; padding: 12px 10px;">
                                <div style="font-size: 11.5px; color: #64748b; margin-bottom: 6px;">Henüz görev yok</div>
                                ${window.CAN_MANAGE_PROJECT ? `<button type="button" class="btn btn-xs btn-outline-success" onclick="openCreateTaskModal(${phase.id})" style="font-size: 10.5px; font-weight: 600;">+ Görev Tanımla</button>` : ''}
                            </div>
                        `;
                    } else {
                        tasks.forEach(task => {
                            let statusClass = 'pending';
                            let statusBadge = '<span class="badge" style="background: #f1f5f9; color: #475569;">Yapılacak</span>';

                            if (task.status === 'done') {
                                statusClass = 'done';
                                statusBadge = '<span class="badge" style="background: #dcfce7; color: #15803d;">✓ Tamamlandı</span>';
                            } else if (task.status === 'in_progress') {
                                statusClass = 'active'; // PULSING GLOW
                                statusBadge = '<span class="badge" style="background: #ffedd5; color: #ea580c; font-weight: 700;">⏳ Süreçte</span>';
                                foundFirstActive = true;
                            } else if (task.status === 'blocked') {
                                statusClass = 'blocked'; // RED PULSING
                                statusBadge = '<span class="badge" style="background: #fee2e2; color: #b91c1c;">⛔ Tıkandı</span>';
                                foundFirstActive = true;
                            } else if (!foundFirstActive) {
                                // İlk sıradaki yapılacak görev aktif gibi parlasın
                                statusClass = 'active';
                                statusBadge = '<span class="badge" style="background: #ffedd5; color: #ea580c; font-weight: 700;">● Sıradaki Görev</span>';
                                foundFirstActive = true;
                            }

                            const assigneeName = task.assignee ? task.assignee.name : 'Atanmamış';
                            const assigneeInitial = task.assignee ? task.assignee.name.charAt(0).toUpperCase() : '?';

                            html += `
                                <div class="wf-node wf-task-node ${statusClass}" id="wf-node-task-${task.id}" onclick="openQuickTaskModal(${task.id}, '${escapeHtml(task.title)}', '${escapeHtml(assigneeName)}')">
                                    <div class="wf-task-title">
                                        <span>📌</span>
                                        <span>${escapeHtml(task.title)}</span>
                                    </div>
                                    <div class="wf-task-assignee-box">
                                        <span class="wf-task-assignee-avatar">${assigneeInitial}</span>
                                        <strong>${escapeHtml(assigneeName)}</strong>
                                        ${task.tag ? `<span class="badge badge-light" style="font-size: 9px; padding: 1px 4px; margin-left: auto;">${task.tag}</span>` : ''}
                                    </div>
                                    <div class="wf-task-footer">
                                        <span style="color: #64748b;">${task.estimated_hours ? task.estimated_hours + ' saat' : ''}</span>
                                        ${statusBadge}
                                    </div>
                                </div>
                            `;
                        });
                    }

                    // If EHS gate exists in this phase
                    if (hasEhs) {
                        const ehsClass = isEhsDone ? 'done' : 'active';
                        html += `
                            <div class="wf-node wf-ehs-node ${ehsClass}" id="wf-node-ehs-${phase.id}">
                                <div style="font-size: 11.5px; font-weight: 700; color: ${isEhsDone ? '#166534' : '#c2410c'}; display: flex; align-items: center; justify-content: center; gap: 5px;">
                                    <span>🛡️</span>
                                    <span>İSG İzin & Denetim Kapısı</span>
                                </div>
                                <div class="text-xs mt-1" style="color: #64748b;">
                                    ${isEhsDone ? '✓ İSG İzni Onaylandı' : '⏳ İSG Uzmanı Onayı Bekleniyor'}
                                </div>
                            </div>
                        `;
                    }

                    html += `
                            </div>
                        </div>
                    `;
                });

                // 3. END NODE
                html += `
                    <div class="wf-node wf-node-end" id="wf-node-end">
                        <span style="font-size: 16px;">🏁</span>
                        <div>
                            <div>Proje Bitişi</div>
                            <span class="text-xs" style="color: #64748b;">Devreye Alma & Kabul</span>
                        </div>
                    </div>
                `;

                container.innerHTML = html;

                // Draw SVG connectors after DOM layout settles
                requestAnimationFrame(() => {
                    setTimeout(drawWorkflowConnectors, 50);
                });
            }

            function drawWorkflowConnectors() {
                const svg = document.getElementById('workflowSvgLayer');
                const viewport = document.getElementById('workflowCanvasViewport');
                const container = document.getElementById('workflowNodesContainer');
                if (!svg || !viewport || !container) return;

                const viewportRect = viewport.getBoundingClientRect();
                const scrollLeft = viewport.scrollLeft;

                svg.setAttribute('width', Math.max(container.scrollWidth, viewport.clientWidth));
                svg.setAttribute('height', Math.max(container.scrollHeight, viewport.clientHeight));

                // Collect all primary sequential anchor nodes in order
                const nodes = [];

                // Start node
                const startNode = document.getElementById('wf-node-start');
                if (startNode) nodes.push({ el: startNode, isDone: true, isActive: false });

                // Task & EHS nodes inside clusters
                const allTaskAndEhsNodes = container.querySelectorAll('.wf-task-node, .wf-ehs-node');
                allTaskAndEhsNodes.forEach(node => {
                    nodes.push({
                        el: node,
                        isDone: node.classList.contains('done'),
                        isActive: node.classList.contains('active') || node.classList.contains('blocked')
                    });
                });

                // End node
                const endNode = document.getElementById('wf-node-end');
                if (endNode) {
                    const allDone = nodes.length > 1 && nodes.slice(1).every(n => n.isDone);
                    if (allDone) endNode.classList.add('completed');
                    nodes.push({ el: endNode, isDone: allDone, isActive: false });
                }

                // Generate SVG path connectors between adjacent anchors
                let svgPaths = '';

                for (let i = 0; i < nodes.length - 1; i++) {
                    const from = nodes[i];
                    const to = nodes[i + 1];

                    const r1 = from.el.getBoundingClientRect();
                    const r2 = to.el.getBoundingClientRect();

                    // Relative coordinates inside viewport accounting for scroll
                    const x1 = r1.right - viewportRect.left + scrollLeft;
                    const y1 = r1.top + r1.height / 2 - viewportRect.top;

                    const x2 = r2.left - viewportRect.left + scrollLeft;
                    const y2 = r2.top + r2.height / 2 - viewportRect.top;

                    if (x2 <= x1) continue; // Skip same-column elements

                    // Smooth Bezier Curve
                    const dx = (x2 - x1) * 0.45;
                    const pathData = `M ${x1} ${y1} C ${x1 + dx} ${y1}, ${x2 - dx} ${y2}, ${x2} ${y2}`;

                    let lineClass = 'flow-line-pending';
                    if (from.isDone) {
                        lineClass = 'flow-line-completed';
                    } else if (from.isActive) {
                        lineClass = 'flow-line-active'; // PULSING / DASHED MOVING
                    }

                    svgPaths += `<path d="${pathData}" class="${lineClass}" />`;
                }

                svg.innerHTML = svgPaths;
            }

            // Window resize & scroll listeners
            window.addEventListener('resize', () => {
                requestAnimationFrame(drawWorkflowConnectors);
            });

            document.addEventListener('DOMContentLoaded', () => {
                if (document.getElementById('workflowNodesContainer')) {
                    renderWorkflowCanvas();
                }
            });


                            function toggleEditDiscussion(discId) {
                const viewEl = document.getElementById('disc-content-' + discId);
                const editEl = document.getElementById('disc-edit-box-' + discId);
                if (!viewEl || !editEl) return;
                const isEditing = editEl.style.display !== 'none';
                editEl.style.display = isEditing ? 'none' : 'block';
                viewEl.style.display = isEditing ? 'block' : 'none';
            }
            function togglePhaseCollapse(phaseId) {
                        const content = document.getElementById('content-phase-' + phaseId);
                        const arrow = document.getElementById('arrow-phase-' + phaseId);
                        if (content) {
                            const isHidden = content.style.display === 'none';
                            content.style.display = isHidden ? 'block' : 'none';
                            if (arrow) {
                                arrow.textContent = isHidden ? '▼' : '►';
                            }
                        }
                    }

                    function switchTab(tabName) {
                        document.querySelectorAll('.tab-content').forEach(function(el) {
                            el.style.display = 'none';
                        });
                        document.querySelectorAll('.tab-btn').forEach(function(btn) {
                            btn.classList.remove('active');
                        });
                        document.getElementById('tab-' + tabName).style.display = 'block';
                        event.target.classList.add('active');
                    }
                function toggleAddObserverModal() {
                const el = document.getElementById('addObserverCollapse');
                if (el) {
                    el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
                }
            }

            function toggleObsType(type) {
                const internalFields = document.getElementById('internalObsFields');
                const externalFields = document.getElementById('externalObsFields');
                const userSelect = document.getElementById('internalObsUserSelect');
                const emailInput = document.getElementById('externalObsEmail');

                if (type === 'external') {
                    internalFields.style.display = 'none';
                    externalFields.style.display = 'block';
                    if (userSelect) userSelect.required = false;
                    if (emailInput) emailInput.required = true;
                } else {
                    internalFields.style.display = 'block';
                    externalFields.style.display = 'none';
                    if (userSelect) userSelect.required = true;
                    if (emailInput) emailInput.required = false;
                }
            }

            // =========================================================================
            // DENETİM İZİ (AUDIT LOGS) ANLIK VE SÜTUN BAZLI FİLTRELEME MOTORU
            // =========================================================================
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

            function initAuditFilters() {
                const actionSelect = document.getElementById('auditActionFilter');
                const userSelect = document.getElementById('auditUserFilter');
                if (!actionSelect || !userSelect) return;

                const rows = document.querySelectorAll('.audit-row');
                const actionsMap = new Map();
                const usersSet = new Set();

                rows.forEach(row => {
                    const actionSlug = row.dataset.actionSlug;
                    const actionLabel = row.dataset.actionLabel;
                    const actor = row.dataset.actor;

                    if (actionSlug && actionLabel && !actionsMap.has(actionSlug)) {
                        actionsMap.set(actionSlug, actionLabel);
                    }
                    if (actor) {
                        usersSet.add(actor);
                    }
                });

                // Populate Actions
                actionsMap.forEach((label, slug) => {
                    const opt = document.createElement('option');
                    opt.value = slug;
                    opt.textContent = label;
                    actionSelect.appendChild(opt);
                });

                // Populate Users
                usersSet.forEach(user => {
                    const opt = document.createElement('option');
                    opt.value = user;
                    opt.textContent = user;
                    userSelect.appendChild(opt);
                });
            }

            function filterAuditLogs() {
                const globalQ = normalizeTurkish(document.getElementById('auditGlobalSearch')?.value || '');
                const actionFilter = normalizeTurkish(document.getElementById('auditActionFilter')?.value || '');
                const userFilter = normalizeTurkish(document.getElementById('auditUserFilter')?.value || '');

                const colDateQ = normalizeTurkish(document.getElementById('filterColDate')?.value || '');
                const colUserQ = normalizeTurkish(document.getElementById('filterColUser')?.value || '');
                const colActionQ = normalizeTurkish(document.getElementById('filterColAction')?.value || '');
                const colDescQ = normalizeTurkish(document.getElementById('filterColDesc')?.value || '');

                const rows = document.querySelectorAll('.audit-row');
                let visibleCount = 0;

                rows.forEach(row => {
                    const date = normalizeTurkish(row.dataset.date);
                    const actor = normalizeTurkish(row.dataset.actor);
                    const actionSlug = normalizeTurkish(row.dataset.actionSlug);
                    const actionLabel = normalizeTurkish(row.dataset.actionLabel);
                    const desc = normalizeTurkish(row.dataset.desc);

                    // Global search match (any column)
                    const matchGlobal = !globalQ || (
                        date.includes(globalQ) ||
                        actor.includes(globalQ) ||
                        actionSlug.includes(globalQ) ||
                        actionLabel.includes(globalQ) ||
                        desc.includes(globalQ)
                    );

                    // Dropdown filters
                    const matchAction = !actionFilter || actionSlug === actionFilter;
                    const matchUser = !userFilter || actor.includes(userFilter);

                    // Column inputs
                    const matchColDate = !colDateQ || date.includes(colDateQ);
                    const matchColUser = !colUserQ || actor.includes(colUserQ);
                    const matchColAction = !colActionQ || actionLabel.includes(colActionQ) || actionSlug.includes(colActionQ);
                    const matchColDesc = !colDescQ || desc.includes(colDescQ);

                    const isVisible = matchGlobal && matchAction && matchUser && matchColDate && matchColUser && matchColAction && matchColDesc;

                    if (isVisible) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Update counter
                const counterEl = document.getElementById('auditVisibleCount');
                if (counterEl) counterEl.textContent = visibleCount;

                // Show/hide no results row
                const noResultsRow = document.getElementById('auditNoResultsRow');
                if (noResultsRow) {
                    noResultsRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
                }
            }

            function resetAuditFilters() {
                if (document.getElementById('auditGlobalSearch')) document.getElementById('auditGlobalSearch').value = '';
                if (document.getElementById('auditActionFilter')) document.getElementById('auditActionFilter').value = '';
                if (document.getElementById('auditUserFilter')) document.getElementById('auditUserFilter').value = '';
                if (document.getElementById('filterColDate')) document.getElementById('filterColDate').value = '';
                if (document.getElementById('filterColUser')) document.getElementById('filterColUser').value = '';
                if (document.getElementById('filterColAction')) document.getElementById('filterColAction').value = '';
                if (document.getElementById('filterColDesc')) document.getElementById('filterColDesc').value = '';
                filterAuditLogs();
            }

            document.addEventListener('DOMContentLoaded', () => {
                initAuditFilters();
            });

            </script>

            <!-- YENİ GÖREV EKLEME MODALI -->
            <div id="createTaskModal" class="custom-modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; backdrop-filter: blur(4px); align-items: center; justify-content: center;">
                <div style="background: #ffffff; width: 100%; max-width: 580px; border-radius: 14px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25); overflow: hidden; margin: 20px; animation: modalFadeIn 0.2s ease;">
                    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 700;">
                            <span>➕</span> Yeni Proje Görevi Tanımla & Akışa Bağla
                        </div>
                        <button type="button" onclick="closeCreateTaskModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">&times;</button>
                    </div>
                    <form action="{{ route('projects.add_task', $project->id) }}" method="POST" id="createTaskForm" style="padding: 20px;">
                        @csrf
                        <div class="form-group mb-3">
                            <label style="font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 5px; display: block;">
                                Görev Başlığı <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="title" id="taskTitleInput" class="form-control" required placeholder="Örn: Mekanik Sac Kaynağı & Montajı" style="height: 40px; font-size: 13.5px;">
                        </div>

                        <div class="grid-2 mb-3">
                            <div class="form-group mb-0">
                                <label style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; display: block;">
                                    Bağlı Olduğu Aşama / Faz <span class="text-danger">*</span>
                                </label>
                                <select name="phase_id" id="taskPhaseSelect" class="form-control" required style="height: 40px; font-size: 13px;">
                                    @foreach($project->phases as $phase)
                                        <option value="{{ $phase->id }}">{{ $phase->sort_order ?? $loop->iteration }}. {{ $phase->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-0">
                                <label style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; display: block;">
                                    Öncül Görev (Akış Bağıntısı) <span class="text-muted" style="font-weight: 400;">(Opsiyonel)</span>
                                </label>
                                <select name="parent_id" id="taskParentSelect" class="form-control" style="height: 40px; font-size: 13px;">
                                    <option value="">-- Doğrudan Faz Başlangıcına Bağla --</option>
                                    @foreach($project->tasks as $pt)
                                        <option value="{{ $pt->id }}">{{ $pt->title }} ({{ $pt->phase?->name ?? 'Faz' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid-2 mb-3">
                            <div class="form-group mb-0">
                                <label style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; display: block;">
                                    Atanan Sorumlu Personel
                                </label>
                                <select name="assigned_to" id="taskAssigneeSelect" class="form-control" style="height: 40px; font-size: 13px;">
                                    <option value="">-- Atanmamış (Boş) --</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->unvan ?? ($u->roles->first()?->name ?? 'Personel') }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-0">
                                <label style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; display: block;">
                                    Öncelik Seviyesi <span class="text-danger">*</span>
                                </label>
                                <select name="priority" class="form-control" required style="height: 40px; font-size: 13px;">
                                    <option value="low">Düşük</option>
                                    <option value="medium" selected>Orta</option>
                                    <option value="high">Yüksek</option>
                                    <option value="critical">Kritik / Acil</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid-3 mb-3">
                            <div class="form-group mb-0">
                                <label style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; display: block;">
                                    Etiket / Kategori
                                </label>
                                <input type="text" name="tag" class="form-control" placeholder="#İSG, #Mekanik vb." value="#Mekanik" style="height: 38px; font-size: 12.5px;">
                            </div>

                            <div class="form-group mb-0">
                                <label style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; display: block;">
                                    Tahmini Efor (Saat)
                                </label>
                                <input type="number" step="0.5" name="estimated_hours" class="form-control" placeholder="16.0" value="16.0" style="height: 38px; font-size: 12.5px;">
                            </div>

                            <div class="form-group mb-0">
                                <label style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; display: block;">
                                    Hedef Bitiş Tarihi
                                </label>
                                <input type="date" name="due_date" class="form-control" style="height: 38px; font-size: 12.5px;">
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; display: block;">
                                Açıklama ve Talimatlar <span class="text-muted" style="font-weight: 400;">(Opsiyonel)</span>
                            </label>
                            <textarea name="description" rows="2" class="form-control" placeholder="Görev detayları, teknik şartname veya güvenlik notları..."></textarea>
                        </div>

                        <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 10px; border-top: 1px solid #f1f5f9;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="closeCreateTaskModal()" style="padding: 8px 16px;">İptal</button>
                            <button type="submit" class="btn btn-success btn-sm" style="padding: 8px 20px; font-weight: 700;">⚡ Görevi Oluştur & Akışa Ekle</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- GÖREV HIZLI DURUM GÜNCELLEME MODALI -->
            <div id="quickTaskStatusModal" class="custom-modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 9999; backdrop-filter: blur(4px); align-items: center; justify-content: center;">
                <div style="background: #ffffff; width: 100%; max-width: 440px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25); overflow: hidden; margin: 20px;">
                    <div style="background: #0f172a; color: #fff; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 14px; font-weight: 700;" id="quickTaskModalTitle">Görev Durumu Güncelle</div>
                        <button type="button" onclick="closeQuickTaskModal()" style="background: none; border: none; color: #94a3b8; font-size: 18px; cursor: pointer;">&times;</button>
                    </div>
                    <div style="padding: 18px;">
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 16px;">
                            <div style="font-weight: 700; color: #1e293b; font-size: 13.5px;" id="quickTaskTitleText">--</div>
                            <div style="font-size: 12px; color: #64748b; margin-top: 4px;" id="quickTaskAssigneeText">Sorumlu: --</div>
                        </div>
                        <div style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 8px;">Durumu Seçiniz:</div>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="submitQuickTaskStatus('in_progress')" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 14px; font-weight: 600;">
                                <span>⏳ Süreçte / Çalışılıyor</span>
                                <span class="badge" style="background: #fef3c7; color: #d97706;">Pulsing Yanıp Söner</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="submitQuickTaskStatus('done')" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 14px; font-weight: 600;">
                                <span>✅ Tamamlandı & Onaylandı</span>
                                <span class="badge" style="background: #dcfce7; color: #15803d;">Yeşile Döner</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="submitQuickTaskStatus('todo')" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 14px; font-weight: 500;">
                                <span>📋 Yapılacak (Beklemede)</span>
                                <span class="badge" style="background: #f1f5f9; color: #475569;">Planlanan</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="submitQuickTaskStatus('blocked')" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 14px; font-weight: 500;">
                                <span>⛔ Tıkandı / Engel Var</span>
                                <span class="badge" style="background: #fee2e2; color: #b91c1c;">Kırmızı Pulsing</span>
                            </button>
                        </div>
                    </div>
                    <div style="background: #f8fafc; padding: 10px 18px; display: flex; justify-content: flex-end; border-top: 1px solid #e2e8f0;">
                        <button type="button" class="btn btn-sm btn-secondary" onclick="closeQuickTaskModal()">Kapat</button>
                    </div>
                </div>
            </div>


            <!-- PROJE GİZLİLİK SEVİYESİ DEĞİŞTİRME BİLGİ VE ONAY MODALI -->
            <div id="modalChangePrivacy" class="custom-modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); z-index: 10000; backdrop-filter: blur(5px); align-items: center; justify-content: center; padding: 20px;">
                <div style="background: #ffffff; width: 100%; max-width: 580px; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); overflow: hidden; animation: modalFadeIn 0.2s ease;">
                    <!-- Header -->
                    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 18px 22px; display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.3); display: flex; align-items: center; justify-content: center; font-size: 18px;" id="privacyModalHeaderIcon">
                                🔒
                            </div>
                            <div>
                                <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #ffffff;">Proje Gizlilik & Görünürlük Seviyesi</h3>
                                <p style="margin: 2px 0 0; font-size: 11.5px; color: #94a3b8;">Erişim kapsamını ve şirket içi görünürlük kurallarını belirleyin</p>
                            </div>
                        </div>
                        <button type="button" onclick="closeChangePrivacyModal()" style="background: rgba(255,255,255,0.08); border: none; color: #cbd5e1; width: 30px; height: 30px; border-radius: 8px; font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.08)'">&times;</button>
                    </div>

                    <!-- Body -->
                    <div style="padding: 22px;">
                        <!-- Durum Geçiş Kartı (Mevcut -> Hedef) -->
                        <div style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 14px 18px; margin-bottom: 18px;">
                            <div style="text-align: center; flex: 1;">
                                <span style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">Mevcut Durum</span>
                                <span id="privacyModalCurrentBadge" class="badge" style="font-size: 12px; font-weight: 700; padding: 5px 12px; border-radius: 6px; display: inline-flex; align-items: center; gap: 5px; background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;">
                                    <span>🔒</span> Özel / Gizli Proje
                                </span>
                            </div>
                            <div style="font-size: 20px; color: #94a3b8; padding: 0 10px;">
                                ➡️
                            </div>
                            <div style="text-align: center; flex: 1;">
                                <span style="font-size: 11px; font-weight: 600; color: #0284c7; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">Hedef Durum</span>
                                <span id="privacyModalTargetBadge" class="badge" style="font-size: 12px; font-weight: 700; padding: 5px 12px; border-radius: 6px; display: inline-flex; align-items: center; gap: 5px; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">
                                    <span>🌐</span> Genel Proje
                                </span>
                            </div>
                        </div>

                        <!-- Bilgilendirici Açıklama Kutusu -->
                        <div id="privacyModalDynamicInfo" style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 10px; padding: 14px; margin-bottom: 18px;">
                            <!-- Dinamik JS İçeriği -->
                        </div>

                        <!-- Maddeler -->
                        <div style="display: flex; flex-direction: column; gap: 10px; font-size: 12.5px; color: #334155;">
                            <div style="display: flex; align-items: flex-start; gap: 10px;">
                                <span style="font-size: 15px; margin-top: -1px;">👥</span>
                                <div>
                                    <strong style="color: #0f172a;">Ekip Görevleri Korunur:</strong> Proje lideri, görev atamaları ve mevcut sorumluluklar gizlilik değişiminden etkilenmez.
                                </div>
                            </div>
                            <div style="display: flex; align-items: flex-start; gap: 10px;">
                                <span style="font-size: 15px; margin-top: -1px;">🛡️</span>
                                <div>
                                    <strong style="color: #0f172a;">Denetim Kaydı (Audit Log):</strong> Değişiklik, zaman damgası ve kullanıcı adı ile güvenlik loglarına kaydedilir.
                                </div>
                            </div>
                            <div style="display: flex; align-items: flex-start; gap: 10px;">
                                <span style="font-size: 15px; margin-top: -1px;">🔄</span>
                                <div>
                                    <strong style="color: #0f172a;">Geri Alınabilirlik:</strong> İhtiyaç duyulduğunda yetkili yöneticiler gizlilik seviyesini tekrar değiştirebilir.
                                </div>
                            </div>
                        </div>

                        <div id="privacyModalAlert" style="display: none; margin-top: 14px; padding: 10px 14px; border-radius: 8px; font-size: 12.5px; font-weight: 600;"></div>
                    </div>

                    <!-- Footer -->
                    <div style="background: #f8fafc; padding: 14px 22px; display: flex; justify-content: flex-end; align-items: center; gap: 10px; border-top: 1px solid #e2e8f0;">
                        <button type="button" class="btn btn-secondary" onclick="closeChangePrivacyModal()" style="padding: 8px 18px; font-size: 13px; font-weight: 600; border-radius: 8px;">
                            Vazgeç
                        </button>
                        <button type="button" id="btnConfirmChangePrivacy" onclick="executeTogglePrivacy()" style="padding: 8px 20px; font-size: 13px; font-weight: 700; border-radius: 8px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;">
                            <span id="privacyConfirmBtnIcon">🔄</span>
                            <span id="privacyConfirmBtnText">Gizlilik Seviyesini Değiştir</span>
                        </button>
                    </div>
                </div>
            </div>

            <script>
            let currentPrivacyTargetId = {{ $project->id }};
            let currentPrivacyState = '{{ $project->visibility ?? "private" }}';

            function openChangePrivacyModal(projectId) {
                if (projectId) currentPrivacyTargetId = projectId;
                const modal = document.getElementById('modalChangePrivacy');
                if (!modal) return;

                const wrapper = document.getElementById('projectPrivacyWrapper');
                if (wrapper && wrapper.getAttribute('data-current-visibility')) {
                    currentPrivacyState = wrapper.getAttribute('data-current-visibility');
                }

                const currentBadge = document.getElementById('privacyModalCurrentBadge');
                const targetBadge = document.getElementById('privacyModalTargetBadge');
                const dynamicInfo = document.getElementById('privacyModalDynamicInfo');
                const confirmBtn = document.getElementById('btnConfirmChangePrivacy');
                const confirmBtnText = document.getElementById('privacyConfirmBtnText');
                const confirmBtnIcon = document.getElementById('privacyConfirmBtnIcon');
                const alertBox = document.getElementById('privacyModalAlert');
                if (alertBox) alertBox.style.display = 'none';

                if (currentPrivacyState === 'private') {
                    currentBadge.className = 'badge';
                    currentBadge.style.background = '#fef2f2';
                    currentBadge.style.color = '#991b1b';
                    currentBadge.style.border = '1px solid #fecaca';
                    currentBadge.innerHTML = '<span>🔒</span> Özel / Gizli Proje';

                    targetBadge.className = 'badge';
                    targetBadge.style.background = '#e0f2fe';
                    targetBadge.style.color = '#0369a1';
                    targetBadge.style.border = '1px solid #bae6fd';
                    targetBadge.innerHTML = '<span>🌐</span> Genel Proje';

                    dynamicInfo.style.background = '#f0f9ff';
                    dynamicInfo.style.borderColor = '#bae6fd';
                    dynamicInfo.innerHTML = `
                        <div style="font-size: 12.5px; color: #0369a1; line-height: 1.45;">
                            <strong>🌐 Genel Proje Kapsamına Geçiş:</strong><br>
                            Bu işlem onaylandığında proje <strong>tüm şirket personeli</strong> tarafından ana portföy listesinde, global arama kutusunda ve özet raporlarda görüntülenebilir hale gelecektir. Şirket içi şeffaflık ve departmanlar arası iş birliği desteklenir.
                        </div>
                    `;

                    confirmBtn.style.background = '#0284c7';
                    confirmBtn.style.color = '#ffffff';
                    confirmBtn.style.boxShadow = '0 2px 6px rgba(2, 132, 199, 0.25)';
                    if (confirmBtnIcon) confirmBtnIcon.textContent = '🌐';
                    if (confirmBtnText) confirmBtnText.textContent = 'Projeyi Genel Yap';
                } else {
                    currentBadge.className = 'badge';
                    currentBadge.style.background = '#e0f2fe';
                    currentBadge.style.color = '#0369a1';
                    currentBadge.style.border = '1px solid #bae6fd';
                    currentBadge.innerHTML = '<span>🌐</span> Genel Proje';

                    targetBadge.className = 'badge';
                    targetBadge.style.background = '#fef2f2';
                    targetBadge.style.color = '#991b1b';
                    targetBadge.style.border = '1px solid #fecaca';
                    targetBadge.innerHTML = '<span>🔒</span> Özel / Gizli Proje';

                    dynamicInfo.style.background = '#fff5f5';
                    dynamicInfo.style.borderColor = '#fecaca';
                    dynamicInfo.innerHTML = `
                        <div style="font-size: 12.5px; color: #991b1b; line-height: 1.45;">
                            <strong>🔒 Özel / Gizli Proje Kapsamına Geçiş:</strong><br>
                            Bu işlem onaylandığında proje genel arama ve portföy listesinden gizlenir. Yalnızca <strong>atanan proje ekibi, liderler, sponsor, direktörler ve yöneticiler</strong> projeyi görebilir ve erişebilir.
                        </div>
                    `;

                    confirmBtn.style.background = '#dc2626';
                    confirmBtn.style.color = '#ffffff';
                    confirmBtn.style.boxShadow = '0 2px 6px rgba(220, 38, 38, 0.25)';
                    if (confirmBtnIcon) confirmBtnIcon.textContent = '🔒';
                    if (confirmBtnText) confirmBtnText.textContent = 'Projeyi Özel / Gizli Yap';
                }

                modal.style.display = 'flex';
            }

            function closeChangePrivacyModal() {
                const modal = document.getElementById('modalChangePrivacy');
                if (modal) modal.style.display = 'none';
            }

            function executeTogglePrivacy() {
                const confirmBtn = document.getElementById('btnConfirmChangePrivacy');
                const confirmBtnText = document.getElementById('privacyConfirmBtnText');
                const alertBox = document.getElementById('privacyModalAlert');

                if (confirmBtn) {
                    confirmBtn.disabled = true;
                    confirmBtn.style.opacity = '0.7';
                }
                if (confirmBtnText) confirmBtnText.textContent = 'İşlem Yapılıyor...';

                fetch(`/projects/${currentPrivacyTargetId}/toggle-visibility`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(res => {
                    if (confirmBtn) {
                        confirmBtn.disabled = false;
                        confirmBtn.style.opacity = '1';
                    }
                    if (res.success) {
                        currentPrivacyState = res.visibility;
                        const wrapper = document.getElementById('projectPrivacyWrapper');
                        if (wrapper) wrapper.setAttribute('data-current-visibility', res.visibility);

                        const badge = document.getElementById('projectPrivacyBadge');
                        const icon = document.getElementById('projectPrivacyIcon');
                        const text = document.getElementById('projectPrivacyText');
                        if (res.visibility === 'private') {
                            if (badge) {
                                badge.style.background = '#fef2f2';
                                badge.style.color = '#991b1b';
                                badge.style.borderColor = '#fecaca';
                            }
                            if (icon) icon.textContent = '🔒';
                            if (text) text.textContent = 'Özel / Gizli Proje';
                        } else {
                            if (badge) {
                                badge.style.background = '#e0f2fe';
                                badge.style.color = '#0369a1';
                                badge.style.borderColor = '#bae6fd';
                            }
                            if (icon) icon.textContent = '🌐';
                            if (text) text.textContent = 'Genel Proje';
                        }

                        if (alertBox) {
                            alertBox.style.display = 'block';
                            alertBox.style.background = '#f0fdf4';
                            alertBox.style.color = '#166534';
                            alertBox.style.border = '1px solid #bbf7d0';
                            alertBox.innerHTML = '✓ ' + (res.message || 'Gizlilik durumu başarıyla güncellendi.');
                        }

                        setTimeout(() => {
                            closeChangePrivacyModal();
                        }, 1000);
                    } else {
                        if (alertBox) {
                            alertBox.style.display = 'block';
                            alertBox.style.background = '#fef2f2';
                            alertBox.style.color = '#991b1b';
                            alertBox.style.border = '1px solid #fecaca';
                            alertBox.innerHTML = '⚠️ ' + (res.message || 'Gizlilik değiştirilemedi.');
                        }
                    }
                })
                .catch(err => {
                    if (confirmBtn) {
                        confirmBtn.disabled = false;
                        confirmBtn.style.opacity = '1';
                    }
                    if (alertBox) {
                        alertBox.style.display = 'block';
                        alertBox.style.background = '#fef2f2';
                        alertBox.style.color = '#991b1b';
                        alertBox.style.border = '1px solid #fecaca';
                        alertBox.innerHTML = '⚠️ Sunucu bağlantı hatası: ' + err.message;
                    }
                });
            }

            function toggleProjectPrivacy(projectId) {
                openChangePrivacyModal(projectId);
            }
            function openShareDraftModal() {
                const m = document.getElementById('modalShareDraft');
                if (m) m.style.display = 'flex';
            }

            function closeShareDraftModal() {
                const m = document.getElementById('modalShareDraft');
                if (m) m.style.display = 'none';
            }

            function openExternalSharesModal() {
                const m = document.getElementById('modalExternalShares');
                if (m) m.style.display = 'flex';
            }

            function closeExternalSharesModal() {
                const m = document.getElementById('modalExternalShares');
                if (m) m.style.display = 'none';
            }

            function filterReviewerList(query) {
                const q = (query || '').toLowerCase().trim();
                const rows = document.querySelectorAll('.reviewer-option-row');
                rows.forEach(r => {
                    const text = r.getAttribute('data-search') || '';
                    if (!q || text.includes(q)) {
                        r.style.display = 'flex';
                    } else {
                        r.style.display = 'none';
                    }
                });
            }

            function updateReviewerCount() {
                const checked = document.querySelectorAll('#reviewerChecklist input[type="checkbox"]:checked');
                const badge = document.getElementById('selectedReviewerCountBadge');
                if (badge) {
                    badge.textContent = `${checked.length} Kişi Seçili`;
                }
            }

            function clearSelectedReviewers() {
                document.querySelectorAll('#reviewerChecklist input[type="checkbox"]').forEach(c => c.checked = false);
                updateReviewerCount();
            }

            function openViewSuggestionsModal() {
                const m = document.getElementById('modalViewSuggestions');
                if (m) m.style.display = 'flex';
            }

            function closeViewSuggestionsModal() {
                const m = document.getElementById('modalViewSuggestions');
                if (m) m.style.display = 'none';
            }

            function openAddSuggestionModal(fieldKey, fieldLabel) {
                const m = document.getElementById('modalAddSuggestion');
                if (m) {
                    document.getElementById('suggestionFieldKey').value = fieldKey;
                    document.getElementById('suggestionFieldLabel').value = fieldLabel;
                    document.getElementById('suggestionFieldDisplay').textContent = fieldLabel;
                    document.getElementById('suggestionTextInput').value = '';
                    m.style.display = 'flex';
                    setTimeout(() => document.getElementById('suggestionTextInput').focus(), 50);
                }
            }

            function closeAddSuggestionModal() {
                const m = document.getElementById('modalAddSuggestion');
                if (m) m.style.display = 'none';
            }

            // =========================================================================
            // PROJE İÇİ EKİP YETKİ MATRİSİ & DEVİR (HANDOVER) FONKSİYONLARI
            // =========================================================================
            function toggleProjectPermission(checkbox, projectId, userId, permKey) {
                const isGranted = checkbox.checked;
                checkbox.disabled = true;

                fetch(`/projects/${projectId}/stakeholders/permission`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        user_id: userId,
                        permission_key: permKey,
                        state: isGranted ? 1 : 0
                    })
                })
                .then(res => res.json())
                .then(data => {
                    checkbox.disabled = false;
                    if (!data.success) {
                        checkbox.checked = !isGranted;
                        alert('Yetki güncellenemedi: ' + (data.message || 'Bilinmeyen hata'));
                    }
                })
                .catch(err => {
                    checkbox.disabled = false;
                    checkbox.checked = !isGranted;
                    console.error(err);
                    alert('Sunucu iletişim hatası oluştu!');
                });
            }

            function openHandoverModal(userId, userName) {
                const m = document.getElementById('modalHandoverMember');
                if (m) {
                    document.getElementById('handoverFromUserId').value = userId;
                    document.getElementById('handoverFromUserNameDisplay').textContent = userName;
                    m.style.display = 'flex';
                }
            }

            function closeHandoverModal() {
                const m = document.getElementById('modalHandoverMember');
                if (m) m.style.display = 'none';
            }

            function openChangeManagerModal() {
                const m = document.getElementById('modalChangeManager');
                if (m) m.style.display = 'flex';
            }

            function closeChangeManagerModal() {
                const m = document.getElementById('modalChangeManager');
                if (m) m.style.display = 'none';
            }

            window.toggleProjectPermission = toggleProjectPermission;
            window.openHandoverModal = openHandoverModal;
            window.closeHandoverModal = closeHandoverModal;
            window.openChangeManagerModal = openChangeManagerModal;
            window.closeChangeManagerModal = closeChangeManagerModal;

            // Kalite & Temel Çizgi (Baseline) Modal Yöneticileri (Native Overlay Arşitektürü)
            function openProjQualityModal() {
                const el = document.getElementById('modalProjNewQuality');
                if (el) el.style.display = 'flex';
            }
            function closeProjQualityModal() {
                const el = document.getElementById('modalProjNewQuality');
                if (el) el.style.display = 'none';
            }

            function openProjNcrModal() {
                const el = document.getElementById('modalProjNewNcr');
                if (el) el.style.display = 'flex';
            }
            function closeProjNcrModal() {
                const el = document.getElementById('modalProjNewNcr');
                if (el) el.style.display = 'none';
            }

            function evaluateProjQuality(q) {
                document.getElementById('evalProjQualityTitle').textContent = q.title;
                document.getElementById('evalProjQualityTarget').textContent = (q.target_value ? q.target_value + ' ' + (q.unit || '') : 'Niteliksel / Görsel Standart') + (q.tolerance ? ' (Tolerans: ' + q.tolerance + ')' : '');
                document.getElementById('evalProjActualValue').value = q.actual_value || '';
                document.getElementById('evalProjStatus').value = q.status || 'passed';
                document.getElementById('evalProjNotes').value = q.evaluation_notes || '';
                document.getElementById('formProjEvaluateQuality').action = '{{ url("/kalite") }}/' + q.id + '/evaluate';
                const el = document.getElementById('modalProjEvaluateQuality');
                if (el) el.style.display = 'flex';
            }
            function closeProjEvaluateQuality() {
                const el = document.getElementById('modalProjEvaluateQuality');
                if (el) el.style.display = 'none';
            }

            function openFreezeBaselineModal() {
                const el = document.getElementById('modalFreezeBaseline');
                if (el) el.style.display = 'flex';
            }
            function closeFreezeBaselineModal() {
                const el = document.getElementById('modalFreezeBaseline');
                if (el) el.style.display = 'none';
            }

            // ESC tuşu ile kalite ve baseline modallarını kapatma
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeProjQualityModal();
                    closeProjNcrModal();
                    closeProjEvaluateQuality();
                    closeFreezeBaselineModal();
                }
            });

            window.openProjQualityModal = openProjQualityModal;
            window.closeProjQualityModal = closeProjQualityModal;
            window.openProjNcrModal = openProjNcrModal;
            window.closeProjNcrModal = closeProjNcrModal;
            window.evaluateProjQuality = evaluateProjQuality;
            window.closeProjEvaluateQuality = closeProjEvaluateQuality;
            window.openFreezeBaselineModal = openFreezeBaselineModal;
            window.closeFreezeBaselineModal = closeFreezeBaselineModal;
            </script>
        @endpush
@endsection