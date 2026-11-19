@extends('layouts.app')

@section('title', 'Kişisel Çalışma Alanı & Yönetici Paneli')
@section('page_title', 'Çalışma Alanı & Gösterge Paneli')
@section('page_subtitle', 'Kişisel ajandanız, üzerinizdeki görevler ve fabrika geneli canlı analitik')

@section('content')
    @if(isset($activeReceivedDelegations) && $activeReceivedDelegations->isNotEmpty())
        <!-- =========================================================================
             DASHBOARD AKTİF VEKALET BİLGİLENDİRME & GÖREV KARTI (AÇILIP KAPANABİLİR)
             ========================================================================= -->
        <div id="dashboardDelegationCardWrap" style="margin-bottom: 20px;">
            <div id="dashboardDelegationCard" style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #eff6ff 100%); border: 1.5px solid #86efac; border-left: 5px solid #10b981; border-radius: 12px; padding: 16px 20px; box-shadow: 0 4px 12px rgba(16,185,129,0.08); transition: all 0.25s ease;">
                <!-- Başlık & Kontrol Barı -->
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; background: #dcfce7; border: 1.5px solid #86efac; border-radius: 9px; font-size: 17px;">
                            🛡️
                        </span>
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <h3 style="margin: 0; font-size: 15px; font-weight: 800; color: #065f46; letter-spacing: -0.2px;">
                                    Aktif Vekalet Göreviniz Bulunmaktadır
                                </h3>
                                <span style="display: inline-flex; align-items: center; gap: 4px; background: #10b981; color: #ffffff; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 10px;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #ffffff; display: inline-block;"></span>
                                    Vekilsiniz
                                </span>
                            </div>
                            <p style="margin: 2px 0 0; font-size: 12px; color: #475569;">
                                Aşağıda belirtilen yetkili(ler) adına sisteme tanımlanmış canlı vekaletiniz devrededir. İlgili projelerde vekaleten işlem yapmaya salahiyetlisiniz.
                            </p>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px;">
                        <button type="button" onclick="toggleDashboardDelegationPanel()" id="btnToggleDashboardDelegation" class="btn btn-sm" style="background: #ffffff; border: 1px solid #cbd5e1; color: #334155; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 7px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer;">
                            <span id="lblDashboardDelegationToggle">Detayları Gizle ▲</span>
                        </button>
                    </div>
                </div>

                <!-- Genişletilebilir Detay Paneli -->
                <div id="dashboardDelegationContent" style="margin-top: 14px; display: flex; flex-direction: column; gap: 12px;">
                    @foreach($activeReceivedDelegations as $del)
                        @php
                            $delegator = $del->delegator;
                            $badge = $del->getScopeSummaryBadge();
                            $reason = $del->getFormattedReason();
                            $durationText = $del->getDurationText();
                            $actions = $del->getAuditLogs();
                            $projectNames = [];
                            if ($del->project_scope_type === 'selected' && !empty($del->project_ids)) {
                                $projectNames = \App\Models\Project::whereIn('id', (array)$del->project_ids)->pluck('title', 'id')->toArray();
                            }
                        @endphp
                        <div style="background: #ffffff; border: 1px solid #bbf7d0; border-radius: 10px; padding: 14px 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap;">
                                <div style="flex: 1; min-width: 260px;">
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                        <strong style="font-size: 14px; color: #0f172a;">{{ $delegator?->name ?? 'Bilinmeyen Yetkili' }}</strong>
                                        <span style="font-size: 11.5px; color: #64748b; font-weight: 600;">({{ $delegator?->unvan ?? 'Yetkili' }} • {{ $delegator?->bolum?->ad ?? 'Bölüm' }})</span>
                                        <span style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-size: 11px; font-weight: 700; padding: 1px 7px; border-radius: 5px;">
                                            ⏳ {{ $durationText }}
                                        </span>
                                    </div>

                                    <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: #475569; margin-top: 5px; flex-wrap: wrap;">
                                        <span>📅 <strong>{{ $del->starts_at->format('d.m.Y H:i') }}</strong> — <strong>{{ $del->ends_at->format('d.m.Y H:i') }}</strong></span>
                                        @if($del->project_scope_type === 'selected')
                                            <span style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; padding: 1px 7px; border-radius: 5px; font-size: 11px; font-weight: 700;" title="Yalnızca belirtilen projelerde geçerlidir">
                                                📁 {{ count($del->project_ids ?? []) }} Seçili Projede Yetkili
                                            </span>
                                        @else
                                            <span style="background: #fefce8; color: #854d0e; border: 1px solid #fef08a; padding: 1px 7px; border-radius: 5px; font-size: 11px; font-weight: 700;">
                                                🌟 Tüm Projelerde Yetkili (Genel Vekalet)
                                            </span>
                                        @endif
                                        @if(!empty($reason['category']) && $reason['category'] !== 'Belirtilmedi')
                                            <span style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 1px 6px; border-radius: 4px; font-size: 11px; font-weight: 700;">
                                                🏷️ {{ $reason['category'] }}
                                            </span>
                                        @endif
                                    </div>

                                    @if($reason['has_note'])
                                        <div style="font-size: 11.5px; color: #334155; font-style: italic; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px; padding: 4px 8px; margin-top: 6px;">
                                            💬 "{{ $reason['note'] }}"
                                        </div>
                                    @endif

                                    @if($del->project_scope_type === 'selected' && !empty($projectNames))
                                        <div style="font-size: 11px; color: #1e3a8a; margin-top: 6px;">
                                            <strong>Kapsamdaki Projeler:</strong> {{ implode(', ', $projectNames) }}
                                        </div>
                                    @endif
                                </div>

                                <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0;">
                                    <button type="button" onclick="openDelegationScopeModal({{ $del->id }})" class="btn btn-xs" style="background: {{ $badge['bg'] }}; color: {{ $badge['color'] }}; border: 1px solid {{ $badge['border'] }}; font-weight: 700; font-size: 11px; padding: 3px 9px; border-radius: 6px; cursor: pointer;">
                                        🎯 Yetki Kapsamı ({{ $badge['label'] }})
                                    </button>
                                    <a href="{{ route('projects.index') }}" class="btn btn-xs btn-outline-primary" style="font-weight: 700; font-size: 11px; padding: 3px 9px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 3px;">
                                        📁 Projelere Git ↗
                                    </a>
                                </div>
                            </div>

                            <!-- VARSAYILAN OLARAK KAPALI AKORDEON: BU VEKALET ÜZERİNDEYKEN YAPTIĞIM İŞLEMLER -->
                            <div style="margin-top: 10px; border-top: 1px dashed #e2e8f0; padding-top: 8px;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <button type="button" onclick="toggleDashboardDelegationActions({{ $del->id }})" style="background: transparent; border: none; padding: 0; font-size: 11.5px; font-weight: 700; color: #0284c7; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                        <span id="iconActionToggle_{{ $del->id }}">▶</span>
                                        <span>Bu Vekalet Kapsamında Yaptığınız İşlemler ({{ $actions->count() }} Kayıt)</span>
                                    </button>
                                    <span style="font-size: 10.5px; color: #64748b;">(Varsayılan Kapalı)</span>
                                </div>

                                <div id="panelActions_{{ $del->id }}" style="display: none; margin-top: 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px;">
                                    @if($actions->isEmpty())
                                        <div style="font-size: 11.5px; color: #64748b; text-align: center; padding: 8px;">
                                            Bu vekalet süresince vekil olarak henüz bir işlem (görev, onay, revizyon vb.) kaydınız bulunmamaktadır.
                                        </div>
                                    @else
                                        <div style="max-height: 220px; overflow-y: auto;">
                                            <table style="width: 100%; font-size: 11.5px; border-collapse: collapse;">
                                                <thead>
                                                    <tr style="border-bottom: 1px solid #cbd5e1; color: #475569; text-align: left; font-size: 10.5px;">
                                                        <th style="padding: 4px 6px;">Tarih</th>
                                                        <th style="padding: 4px 6px;">Proje</th>
                                                        <th style="padding: 4px 6px;">İşlem</th>
                                                        <th style="padding: 4px 6px;">Açıklama</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($actions as $act)
                                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                                            <td style="padding: 4px 6px; color: #64748b; white-space: nowrap;">{{ $act->created_at->format('d.m.Y H:i') }}</td>
                                                            <td style="padding: 4px 6px; font-weight: 600; color: #0f172a; white-space: nowrap;">{{ $act->project?->title ?? 'Genel Sistem' }}</td>
                                                            <td style="padding: 4px 6px;"><span class="badge" style="background: #e2e8f0; color: #334155; font-size: 10px;">{{ $act->action }}</span></td>
                                                            <td style="padding: 4px 6px; color: #334155;">{!! strip_tags($act->description, '<strong><b>') !!}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <script>
            function toggleDashboardDelegationPanel() {
                const content = document.getElementById('dashboardDelegationContent');
                const lbl = document.getElementById('lblDashboardDelegationToggle');
                if (!content || !lbl) return;

                const isHidden = content.style.display === 'none';
                if (isHidden) {
                    content.style.display = 'flex';
                    lbl.textContent = 'Detayları Gizle ▲';
                    try { localStorage.setItem('kpm_hide_dashboard_delegation', '0'); } catch(e){}
                } else {
                    content.style.display = 'none';
                    lbl.textContent = 'Detayları Göster ▼';
                    try { localStorage.setItem('kpm_hide_dashboard_delegation', '1'); } catch(e){}
                }
            }

            function toggleDashboardDelegationActions(delId) {
                const panel = document.getElementById('panelActions_' + delId);
                const icon = document.getElementById('iconActionToggle_' + delId);
                if (!panel) return;

                if (panel.style.display === 'none' || !panel.style.display) {
                    panel.style.display = 'block';
                    if (icon) icon.textContent = '▼';
                } else {
                    panel.style.display = 'none';
                    if (icon) icon.textContent = '▶';
                }
            }

            (function() {
                try {
                    if (localStorage.getItem('kpm_hide_dashboard_delegation') === '1') {
                        const content = document.getElementById('dashboardDelegationContent');
                        const lbl = document.getElementById('lblDashboardDelegationToggle');
                        if (content && lbl) {
                            content.style.display = 'none';
                            lbl.textContent = 'Detayları Göster ▼';
                        }
                    }
                } catch(e){}
            })();
        </script>
    @endif

    <!-- AKTİF TARİH & DÖNEM FİLTRESİ (EN ÜSTTE) -->
    <div class="card" style="margin-bottom: 20px; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 20px; background: #ffffff; box-shadow: 0 2px 6px rgba(15,23,42,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <span style="font-size: 16px;">📅</span>
                <span style="font-size: 14px; font-weight: 800; color: #0f172a; letter-spacing: -0.2px;">Dönem & Tarih Analitiği</span>
                <span style="font-size: 12px; color: #64748b;">(Tüm göstergeler, bütçeler ve görevler seçilen aralığa göre canlı güncellenir)</span>
            </div>
            <div>
                @if(!empty($activeDateFilterLabel))
                    <div style="display: inline-flex; align-items: center; gap: 6px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; box-shadow: 0 1px 2px rgba(16,185,129,0.06);">
                        <span style="width: 7px; height: 7px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                        <span>Aktif Filtre: {{ $activeDateFilterLabel }}</span>
                        <span style="color: #059669; font-weight: 500;">• {{ $dateType === 'start_date' ? 'Proje Başlangıç Tarihine Göre' : ($dateType === 'target_end_date' ? 'Hedef Bitiş Tarihine Göre' : 'Kayıt / Oluşturma Tarihine Göre') }}</span>
                        <a href="{{ route('dashboard') }}" style="color: #047857; text-decoration: none; margin-left: 4px; font-weight: 800;" title="Filtreyi Kaldır">✕</a>
                    </div>
                @else
                    <span style="display: inline-flex; align-items: center; gap: 6px; background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                        <span>🌐 Tüm Zamanlar (Filtresiz Görünüm)</span>
                    </span>
                @endif
            </div>
        </div>

        <form method="GET" action="{{ route('dashboard') }}" id="dashboardDateFilterForm" style="display: flex; align-items: flex-end; flex-wrap: wrap; gap: 12px;">
            <!-- Dönem Hazır Şablonu (Preset) -->
            <div style="display: flex; flex-direction: column; gap: 4px;">
                <label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.3px;">Dönem Seçimi</label>
                <select name="time_filter" id="timeFilterSelect" class="form-select form-select-sm" style="height: 38px; border-radius: 8px; font-size: 12.5px; font-weight: 600; border-color: #cbd5e1; min-width: 170px;" onchange="handleTimeFilterChange(this.value)">
                    <option value="all" {{ ($timeFilter ?? 'all') === 'all' ? 'selected' : '' }}>🌐 Tüm Zamanlar</option>
                    <option value="this_month" {{ ($timeFilter ?? '') === 'this_month' ? 'selected' : '' }}>📅 Bu Ay ({{ now()->locale('tr')->isoFormat('MMMM YYYY') }})</option>
                    <option value="last_month" {{ ($timeFilter ?? '') === 'last_month' ? 'selected' : '' }}>⏪ Geçen Ay</option>
                    <option value="this_quarter" {{ ($timeFilter ?? '') === 'this_quarter' ? 'selected' : '' }}>📊 Bu Çeyrek</option>
                    <option value="this_year" {{ ($timeFilter ?? '') === 'this_year' ? 'selected' : '' }}>🗓️ Bu Yıl ({{ now()->year }})</option>
                    <option value="last_year" {{ ($timeFilter ?? '') === 'last_year' ? 'selected' : '' }}>⏳ Geçen Yıl ({{ now()->year - 1 }})</option>
                    <option value="custom" {{ ($timeFilter ?? '') === 'custom' ? 'selected' : '' }}>🎯 Özel Tarih Aralığı</option>
                </select>
            </div>

            <!-- Tarih Kriteri -->
            <div style="display: flex; flex-direction: column; gap: 4px;">
                <label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.3px;">Tarih Kriteri</label>
                <select name="date_type" class="form-select form-select-sm" style="height: 38px; border-radius: 8px; font-size: 12.5px; font-weight: 600; border-color: #cbd5e1; min-width: 195px;">
                    <option value="created_at" {{ ($dateType ?? 'created_at') === 'created_at' ? 'selected' : '' }}>📝 Kayıt / Oluşturma Tarihi</option>
                    <option value="start_date" {{ ($dateType ?? '') === 'start_date' ? 'selected' : '' }}>🚀 Proje Başlangıç Tarihi</option>
                    <option value="target_end_date" {{ ($dateType ?? '') === 'target_end_date' ? 'selected' : '' }}>🎯 Hedef Bitiş Tarihi</option>
                </select>
            </div>

            <!-- Başlangıç Tarihi -->
            <div style="display: flex; flex-direction: column; gap: 4px;">
                <label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.3px;">Başlangıç</label>
                <input type="date" name="date_from" id="dashboardDateFrom" value="{{ $dateFrom ?? '' }}" class="form-control form-control-sm" style="height: 38px; border-radius: 8px; font-size: 12.5px; border-color: #cbd5e1; width: 145px;" onchange="document.getElementById('timeFilterSelect').value = 'custom';">
            </div>

            <!-- Bitiş Tarihi -->
            <div style="display: flex; flex-direction: column; gap: 4px;">
                <label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.3px;">Bitiş</label>
                <input type="date" name="date_to" id="dashboardDateTo" value="{{ $dateTo ?? '' }}" class="form-control form-control-sm" style="height: 38px; border-radius: 8px; font-size: 12.5px; border-color: #cbd5e1; width: 145px;" onchange="document.getElementById('timeFilterSelect').value = 'custom';">
            </div>

            <!-- Butonlar -->
            <div style="display: flex; align-items: center; gap: 8px;">
                <button type="submit" class="btn btn-primary btn-sm" style="height: 38px; padding: 0 16px; border-radius: 8px; font-weight: 700; font-size: 12.5px; display: inline-flex; align-items: center; gap: 6px;">
                    <span>🔍</span> Filtrele & Güncelle
                </button>
                @if(($timeFilter ?? 'all') !== 'all' || !empty($dateFrom) || !empty($dateTo))
                    <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm" style="height: 38px; padding: 0 12px; border-radius: 8px; font-weight: 600; font-size: 12.5px; display: inline-flex; align-items: center; gap: 4px; border: 1px solid #cbd5e1; color: #475569; text-decoration: none;" title="Filtreyi Temizle">
                        <span>✕</span> Temizle
                    </a>
                @endif
            </div>
        </form>
    </div>

    <script>
        function handleTimeFilterChange(val) {
            const today = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            const formatDate = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

            let from = '', to = '';
            if (val === 'this_month') {
                const start = new Date(today.getFullYear(), today.getMonth(), 1);
                const end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                from = formatDate(start);
                to = formatDate(end);
            } else if (val === 'last_month') {
                const start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                const end = new Date(today.getFullYear(), today.getMonth(), 0);
                from = formatDate(start);
                to = formatDate(end);
            } else if (val === 'this_quarter') {
                const q = Math.floor(today.getMonth() / 3);
                const start = new Date(today.getFullYear(), q * 3, 1);
                const end = new Date(today.getFullYear(), (q + 1) * 3, 0);
                from = formatDate(start);
                to = formatDate(end);
            } else if (val === 'this_year') {
                from = `${today.getFullYear()}-01-01`;
                to = `${today.getFullYear()}-12-31`;
            } else if (val === 'last_year') {
                from = `${today.getFullYear() - 1}-01-01`;
                to = `${today.getFullYear() - 1}-12-31`;
            } else if (val === 'all') {
                from = '';
                to = '';
            }

            if (val !== 'custom') {
                document.getElementById('dashboardDateFrom').value = from;
                document.getElementById('dashboardDateTo').value = to;
                document.getElementById('dashboardDateFilterForm').submit();
            }
        }
    </script>

    <!-- 1. KİŞİSELLEŞTİRİLMİŞ KARŞILAMA VE HIZLI ERİŞİM BANNER'I (ProofHub / Asana Modeli) -->
    <div class="card" style="margin-bottom: 24px; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); box-shadow: 0 2px 6px rgba(15,23,42,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="font-size: 13px; font-weight: 700; color: #2563eb; text-transform: uppercase; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                    <span>⚡</span> <span>Kişisel Çalışma Alanı • {{ auth()->user()->bolum?->name ?? 'Merkez Departman' }}</span>
                </div>
                <h2 style="font-size: 22px; font-weight: 800; color: #0f172a; margin: 0 0 6px 0;">
                    İyi günler, {{ auth()->user()->name }}! 👋
                </h2>
                <div style="font-size: 13px; color: #64748b;">
                    Bugün takip etmeniz gereken <strong style="color: #b45309;">{{ $myOpenTasksCount }} açık görev</strong> ve <strong style="color: #2563eb;">{{ $myMeetings->count() }} yaklaşan toplantınız</strong> bulunuyor.
                </div>
            </div>

            <!-- HIZLI ARAMA / KISAYOL ÇUBUĞU (Ctrl + K Stili) -->
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="position: relative; width: 280px;">
                    <input type="text" id="quickJumpSearch" class="form-control" placeholder="Proje veya görev ara... (Ctrl + K)" style="font-size: 13px; padding-left: 34px; height: 38px; border-radius: 8px; background: #ffffff;">
                    <span style="position: absolute; left: 10px; top: 10px; font-size: 14px; color: #94a3b8;">🔍</span>
                </div>
                @if(auth()->user()->hasPermission('proje.create'))
                    <a href="{{ route('projects.create') }}" class="btn btn-primary" style="height: 38px; display: inline-flex; align-items: center; gap: 6px; font-weight: 600; font-size: 13px; padding: 0 16px; border-radius: 8px; text-decoration: none;">
                        <span>+</span> Yeni Proje
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- 2. PROOFHUB / ASANA 4'LÜ HİBRİT ÇALIŞMA ALANI IZGARASI -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-bottom: 28px;">
        
        <!-- KART 1: 📌 ÜZERİMDEKİ GÖREVLER (MY TASKS) -->
        <div class="card" style="margin-bottom: 0; border: 1px solid #e2e8f0; border-radius: 12px; display: flex; flex-direction: column;">
            <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center;">
                <span class="card-title" style="font-size: 14.5px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <span>📌</span> Üzerimdeki Görevler & Terminler
                </span>
                <span class="badge" style="background: #eff6ff; color: #1e40af; font-weight: 700;">{{ $myOpenTasksCount }} Açık</span>
            </div>
            <div class="card-body" style="padding: 14px; flex: 1;">
                @forelse($myTasks as $task)
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center; transition: all 0.15s;" onmouseover="this.style.background='#ffffff'; this.style.borderColor='#93c5fd'" onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'">
                        <div style="overflow: hidden; padding-right: 10px;">
                            <a href="{{ $task->project_id ? route('projects.show', $task->project_id) : '#' }}" style="font-size: 13px; font-weight: 700; color: #1e293b; text-decoration: none; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $task->title }}
                            </a>
                            <div style="font-size: 11px; color: #64748b; margin-top: 2px; display: flex; align-items: center; gap: 8px;">
                                <span style="font-weight: 600; color: #2563eb;">{{ $task->project?->code ?? '-' }}</span>
                                <span>•</span>
                                <span style="color: {{ $task->due_date && $task->due_date->isPast() ? '#dc2626' : '#b45309' }}; font-weight: 600;">
                                    📅 {{ $task->due_date ? $task->due_date->format('d.m.Y') : 'Terminsiz' }}
                                </span>
                            </div>
                        </div>
                        <x-priority-badge :priority="$task->priority" />
                    </div>
                @empty
                    <div style="text-align: center; padding: 30px; color: #64748b; font-size: 12.5px;">
                        <div>🎉 Harika! Üzerinizde geciken veya açık görev bulunmuyor.</div>
                    </div>
                @endforelse
            </div>
            <div style="border-top: 1px solid #f1f5f9; padding: 10px 16px; background: #ffffff; text-align: right; border-radius: 0 0 12px 12px;">
                <a href="{{ route('kanban.index') }}" style="font-size: 12px; font-weight: 700; color: #2563eb; text-decoration: none;">
                    Kanban Panosunu Aç →
                </a>
            </div>
        </div>

        <!-- KART 2: 📁 DAHİL OLDUĞUM PROJELER (MY PROJECTS) -->
        <div class="card" style="margin-bottom: 0; border: 1px solid #e2e8f0; border-radius: 12px; display: flex; flex-direction: column;">
            <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center;">
                <span class="card-title" style="font-size: 14.5px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <span>📁</span> Dahil Olduğum Projelerim
                </span>
                <span class="badge" style="background: #f1f5f9; color: #475569; font-weight: 700;">{{ $myProjectsCount }} Proje</span>
            </div>
            <div class="card-body" style="padding: 14px; flex: 1;">
                @forelse($myProjects as $prj)
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px; margin-bottom: 8px; transition: all 0.15s;" onmouseover="this.style.background='#ffffff'; this.style.borderColor='#93c5fd'" onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <a href="{{ route('projects.show', $prj->id) }}" style="font-size: 13px; font-weight: 700; color: #0f172a; text-decoration: none;">
                                📁 {{ $prj->code }} - {{ Str::limit($prj->title, 26) }}
                            </a>
                            <span class="badge" style="font-size: 10px; background: #e0f2fe; color: #0369a1;">
                                %{{ $prj->progress_percentage }}
                            </span>
                        </div>
                        <div style="background: #e2e8f0; height: 5px; border-radius: 3px; overflow: hidden; margin-bottom: 4px;">
                            <div style="background: #2563eb; width: {{ $prj->progress_percentage }}%; height: 100%;"></div>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 11px; color: #64748b;">
                            <span>{{ $prj->bolum?->name ?? 'Merkez' }}</span>
                            <span>{{ $prj->phases->where('status', 'completed')->count() }}/{{ $prj->phases->count() }} Aşama Tamam</span>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 30px; color: #64748b; font-size: 12.5px;">
                        Kayıtlı aktif projeniz bulunmuyor.
                    </div>
                @endforelse
            </div>
            <div style="border-top: 1px solid #f1f5f9; padding: 10px 16px; background: #ffffff; text-align: right; border-radius: 0 0 12px 12px;">
                <a href="{{ route('projects.index') }}" style="font-size: 12px; font-weight: 700; color: #2563eb; text-decoration: none;">
                    Tüm Projeler Listesi →
                </a>
            </div>
        </div>

        <!-- KART 3: 📅 GÜNLÜK AJANDA & TOPLANTILARIM (MY AGENDA & MEETINGS) -->
        <div class="card" style="margin-bottom: 0; border: 1px solid #e2e8f0; border-radius: 12px; display: flex; flex-direction: column;">
            <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center;">
                <span class="card-title" style="font-size: 14.5px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <span>📅</span> Ajanda & Toplantılarım
                </span>
                <span class="badge" style="background: #fef3c7; color: #b45309; font-weight: 700;">{{ $myMeetings->count() }} Toplantı</span>
            </div>
            <div class="card-body" style="padding: 14px; flex: 1;">
                @forelse($myMeetings as $meeting)
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px; margin-bottom: 8px;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <a href="{{ route('meetings.show', $meeting->id) }}" style="font-size: 13px; font-weight: 700; color: #0f172a; text-decoration: none;">
                                {{ Str::limit($meeting->title, 28) }}
                            </a>
                            <span style="font-size: 11px; font-weight: 700; color: #2563eb; white-space: nowrap;">
                                {{ $meeting->meeting_date->format('H:i') }}
                            </span>
                        </div>
                        <div style="font-size: 11px; color: #64748b; margin-top: 4px; display: flex; align-items: center; gap: 8px;">
                            <span>📁 {{ $meeting->project?->code ?? '-' }}</span>
                            <span>•</span>
                            <span>📅 {{ $meeting->meeting_date->format('d.m.Y') }}</span>
                            <span>•</span>
                            <span>📍 {{ Str::limit($meeting->location ?? 'Şantiye Ofisi', 15) }}</span>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 30px; color: #64748b; font-size: 12.5px;">
                        Bugün için planlanmış toplantınız bulunmuyor.
                    </div>
                @endforelse
            </div>
            <div style="border-top: 1px solid #f1f5f9; padding: 10px 16px; background: #ffffff; text-align: right; border-radius: 0 0 12px 12px;">
                <a href="{{ route('meetings.index') }}" style="font-size: 12px; font-weight: 700; color: #2563eb; text-decoration: none;">
                    Toplantı Takvimini Aç →
                </a>
            </div>
        </div>

        <!-- KART 4: 📢 ŞİRKET & SAHA DUYURULARI (ANNOUNCEMENTS & SAFETY) -->
        <div class="card" style="margin-bottom: 0; border: 1px solid #e2e8f0; border-radius: 12px; display: flex; flex-direction: column;">
            <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center;">
                <span class="card-title" style="font-size: 14.5px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <span>📢</span> Şirket & Saha Bildirim Akışı
                </span>
                <span class="badge" style="background: #ecfdf5; color: #047857; font-weight: 700;">Canlı İletişim</span>
            </div>
            <div class="card-body" style="padding: 14px; flex: 1;">
                @forelse($announcements as $ann)
                    <div style="border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 8px; font-size: 12px;">
                        <div style="color: #1e293b; font-weight: 600;">
                            {!! Str::limit(strip_tags($ann->description), 65) !!}
                        </div>
                        <div style="font-size: 10.5px; color: #94a3b8; margin-top: 2px; display: flex; justify-content: space-between;">
                            <span>{{ $ann->user?->name ?? 'Sistem' }}</span>
                            <span>{{ $ann->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 30px; color: #64748b; font-size: 12.5px;">
                        Henüz duyuru bulunmuyor.
                    </div>
                @endforelse
            </div>
            <div style="border-top: 1px solid #f1f5f9; padding: 10px 16px; background: #ffffff; text-align: right; border-radius: 0 0 12px 12px;">
                <span style="font-size: 11px; color: #64748b;">📡 Otomatik Denetim & İSG Akışı</span>
            </div>
        </div>

    </div>

        <!-- 3. KİŞİSEL ÇALIŞMA TAKVİMİ & AJANDAM (INTERACTIVE CALENDAR) -->
    <div class="card calendar-container-card" id="dashboardCalendarCard" style="margin-bottom: 28px; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 4px 15px rgba(15,23,42,0.04); background: #ffffff; overflow: hidden;">
        <!-- TAKVİM ÜST BAR / KONTROLLER -->
        <div class="cal-header-bar" style="padding: 18px 24px; border-bottom: 1px solid #e2e8f0; background: #ffffff; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #eff6ff; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(2, 132, 199, 0.15);">
                    📅
                </div>
                <div>
                    <h3 style="font-size: 17px; font-weight: 800; color: #0f172a; margin: 0 0 3px 0; display: flex; align-items: center; gap: 8px;">
                        <span>Kişisel Çalışma Takvimi & Ajandam</span>
                    </h3>
                    <p style="font-size: 12.5px; color: #64748b; margin: 0;">
                        Termini yaklaşan görevleriniz, proje toplantılarınız ve kişiselleştirilebilir hatırlatma notlarınız.
                    </p>
                </div>
            </div>

            <!-- AY GEZİNTİSİ & BUGÜN BUTONU -->
            <div class="cal-nav-controls" style="display: flex; align-items: center; gap: 10px;">
                <div style="display: flex; align-items: center; background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 2px;">
                    <button type="button" class="cal-nav-btn" onclick="navigateCalendarMonth(-1)" title="Önceki Ay" style="border: none; background: transparent; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 13px; color: #475569; transition: all 0.15s;">
                        ◀
                    </button>
                    <span id="calCurrentMonthLabel" style="font-size: 13.5px; font-weight: 800; color: #0f172a; padding: 0 14px; min-width: 140px; text-align: center;">
                        Ekim 2026
                    </span>
                    <button type="button" class="cal-nav-btn" onclick="navigateCalendarMonth(1)" title="Sonraki Ay" style="border: none; background: transparent; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 13px; color: #475569; transition: all 0.15s;">
                        ▶
                    </button>
                </div>
                <button type="button" class="btn btn-sm" onclick="jumpCalendarToday()" style="height: 38px; padding: 0 14px; border-radius: 10px; font-weight: 700; font-size: 12.5px; border: 1.5px solid #cbd5e1; background: #ffffff; color: #334155; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                    Bugün
                </button>
            </div>

            <!-- FİLTRE ROZETLERİ VE YENİ NOT EKLE BUTONU -->
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <div class="cal-filter-group" style="display: flex; gap: 4px; background: #f1f5f9; padding: 3px; border-radius: 10px;">
                    <button type="button" class="cal-filter-btn active" data-type="all" onclick="filterCalendarEvents('all', this)" style="border: none; padding: 6px 12px; border-radius: 7px; font-size: 12px; font-weight: 700; cursor: pointer; background: #0284c7; color: #ffffff; transition: all 0.15s;">
                        Tümü (<span id="cntAllEvents">0</span>)
                    </button>
                    <button type="button" class="cal-filter-btn" data-type="task" onclick="filterCalendarEvents('task', this)" style="border: none; padding: 6px 10px; border-radius: 7px; font-size: 12px; font-weight: 600; cursor: pointer; background: transparent; color: #0369a1; transition: all 0.15s;">
                        📋 Görevler (<span id="cntTaskEvents">0</span>)
                    </button>
                    <button type="button" class="cal-filter-btn" data-type="meeting" onclick="filterCalendarEvents('meeting', this)" style="border: none; padding: 6px 10px; border-radius: 7px; font-size: 12px; font-weight: 600; cursor: pointer; background: transparent; color: #047857; transition: all 0.15s;">
                        📅 Toplantılar (<span id="cntMeetingEvents">0</span>)
                    </button>
                    <button type="button" class="cal-filter-btn" data-type="note" onclick="filterCalendarEvents('note', this)" style="border: none; padding: 6px 10px; border-radius: 7px; font-size: 12px; font-weight: 600; cursor: pointer; background: transparent; color: #b45309; transition: all 0.15s;">
                        📌 Notlarım (<span id="cntNoteEvents">0</span>)
                    </button>
                </div>

                <button type="button" class="btn btn-primary" onclick="openAddNoteModal()" style="height: 38px; display: inline-flex; align-items: center; gap: 6px; font-weight: 700; font-size: 13px; padding: 0 16px; border-radius: 10px; box-shadow: 0 2px 6px rgba(2, 132, 199, 0.28); white-space: nowrap;">
                    <span>+</span> Not / Hatırlatıcı Ekle
                </button>
            </div>
        </div>

        <!-- TAKVİM GÜNLERİ TABLOSU -->
        <div class="cal-grid-wrapper" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <div style="min-width: 760px;">
                <!-- HAFTANIN GÜNLERİ BAŞLIĞI -->
                <div class="cal-weekdays-row" style="display: grid; grid-template-columns: repeat(7, 1fr); background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; text-align: center;">
                    <div style="padding: 10px 4px; font-size: 12px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;">Pzt</div>
                    <div style="padding: 10px 4px; font-size: 12px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;">Sal</div>
                    <div style="padding: 10px 4px; font-size: 12px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;">Çar</div>
                    <div style="padding: 10px 4px; font-size: 12px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;">Per</div>
                    <div style="padding: 10px 4px; font-size: 12px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;">Cum</div>
                    <div style="padding: 10px 4px; font-size: 12px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; background: #f1f5f9;">Cmt</div>
                    <div style="padding: 10px 4px; font-size: 12px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; background: #f1f5f9;">Paz</div>
                </div>

                <!-- GÜN HÜCRELERİ IZGARASI (GRID) -->
                <div id="calDaysGrid" class="cal-days-grid" style="display: grid; grid-template-columns: repeat(7, 1fr); background: #e2e8f0; gap: 1px;">
                    <!-- JavaScript tarafından dinamik oluşturulur -->
                </div>
            </div>
        </div>

        <!-- SEÇİLİ GÜN AJANDASI / DETAY ÇEKMECESİ -->
        <div id="calDayAgendaBox" style="display: none; padding: 18px 24px; background: #f8fafc; border-top: 1.5px solid #e2e8f0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h4 style="font-size: 14.5px; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span>📌</span> <span id="agendaDayTitle">Seçili Gün Etkinlikleri</span>
                </h4>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button type="button" class="btn btn-sm btn-primary" id="agendaAddNoteBtn" onclick="openAddNoteModal(currentSelectedDateStr)" style="font-size: 11.5px; padding: 5px 12px; border-radius: 6px;">
                        + Bu Güne Not Ekle
                    </button>
                    <button type="button" class="btn btn-sm btn-light" onclick="closeAgendaBox()" style="font-size: 11.5px; padding: 5px 10px; border-radius: 6px; border: 1px solid #cbd5e1;">
                        ✕ Kapat
                    </button>
                </div>
            </div>
            <div id="agendaItemsList" style="display: flex; flex-direction: column; gap: 8px;">
                <!-- Günün etkinlikleri dinamik listelenir -->
            </div>
        </div>
    </div>

    <!-- 3. FABRİKA GENELİ YÖNETİM KPI ANALİTİĞİ (CELOXIS / PPM ÇİFT METRİKLİ MODEL) -->
    <div style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div>
            <h3 style="font-size: 16.5px; font-weight: 800; color: #0f172a; margin: 0 0 4px 0; display: flex; align-items: center; gap: 8px;">
                <span>📊</span> Fabrika Geneli Portföy KPI & Sağlık Göstergeleri
            </h3>
            <p style="font-size: 12.5px; color: #64748b; margin: 0;">
                Bütçe gerçekleşmesi, adam/saat eforu, görev çıktıları ve takvim/bütçe portföy sağlık analitiği.
            </p>
        </div>
        <div style="display: flex; gap: 8px;">
            <span class="badge" style="background: #eff6ff; color: #0284c7; font-weight: 700; font-size: 11.5px; padding: 6px 12px; border-radius: 8px; border: 1px solid #bfdbfe;">
                ⚡ Canlı Portföy Özeti
            </span>
        </div>
    </div>

    <!-- ÇİFT METRİKLİ EXECUTIVE KPI KARTLARI (DUAL-METRIC RIBBON) -->
    <div class="grid-4" style="margin-bottom: 24px; gap: 16px;">
        <!-- KPI 1: BÜTÇE GERÇEKLEŞMESİ (DUAL METRIC) -->
        <div class="kpi-card" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; background: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.03); border-top: 3.5px solid #0284c7;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                <span class="kpi-title" style="font-size: 12px; font-weight: 750; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">Bütçe Gerçekleşmesi</span>
                <span style="font-size: 18px;">💰</span>
            </div>
            <div style="display: flex; align-items: baseline; gap: 6px; margin-bottom: 8px;">
                <span style="font-size: 20px; font-weight: 800; color: #0f172a;">₺{{ number_format($totalActualSpent / 1000000, 2) }}M</span>
                <span style="font-size: 12px; color: #94a3b8; font-weight: 600;">/ ₺{{ number_format($totalPlannedBudget / 1000000, 2) }}M</span>
            </div>
            <!-- Bütçe Progress Bar -->
            <div style="background: #f1f5f9; height: 7px; border-radius: 4px; overflow: hidden; margin-bottom: 8px;">
                <div style="background: {{ $budgetUsageRate > 100 ? '#ef4444' : ($budgetUsageRate > 85 ? '#f59e0b' : '#0284c7') }}; width: {{ min(100, $budgetUsageRate) }}%; height: 100%; border-radius: 4px; transition: width 0.4s ease;"></div>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 11.5px; color: #475569;">
                <span>Kullanım: <strong style="color: {{ $budgetUsageRate > 100 ? '#ef4444' : '#0284c7' }};">%{{ $budgetUsageRate }}</strong></span>
                <span>Kalan: <strong style="color: #059669;">₺{{ number_format($remainingBudget / 1000000, 2) }}M</strong></span>
            </div>
        </div>

        <!-- KPI 2: ADAM/SAAT & EFOR GERÇEKLEŞMESİ (DUAL METRIC) -->
        <div class="kpi-card" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; background: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.03); border-top: 3.5px solid #10b981;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                <span class="kpi-title" style="font-size: 12px; font-weight: 750; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">İş Gücü / Efor</span>
                <span style="font-size: 18px;">⏱️</span>
            </div>
            <div style="display: flex; align-items: baseline; gap: 6px; margin-bottom: 8px;">
                <span style="font-size: 20px; font-weight: 800; color: #0f172a;">{{ number_format($totalSpentHours) }}s</span>
                <span style="font-size: 12px; color: #94a3b8; font-weight: 600;">/ {{ number_format($totalEstimatedHours) }}s Plan</span>
            </div>
            <!-- Efor Progress Bar -->
            <div style="background: #f1f5f9; height: 7px; border-radius: 4px; overflow: hidden; margin-bottom: 8px;">
                <div style="background: #10b981; width: {{ min(100, $effortCompletionRate) }}%; height: 100%; border-radius: 4px; transition: width 0.4s ease;"></div>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 11.5px; color: #475569;">
                <span>Efor Oranı: <strong style="color: #10b981;">%{{ $effortCompletionRate }}</strong></span>
                <span>Kalan: <strong style="color: #64748b;">{{ number_format(max(0, $totalEstimatedHours - $totalSpentHours)) }}s</strong></span>
            </div>
        </div>

        <!-- KPI 3: GÖREV BAŞARISI & ÇIKTILAR (DUAL METRIC) -->
        <div class="kpi-card" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; background: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.03); border-top: 3.5px solid #8b5cf6;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                <span class="kpi-title" style="font-size: 12px; font-weight: 750; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">Görev Tamamlanma</span>
                <span style="font-size: 18px;">📋</span>
            </div>
            <div style="display: flex; align-items: baseline; gap: 6px; margin-bottom: 8px;">
                <span style="font-size: 20px; font-weight: 800; color: #0f172a;">{{ $doneTasksCount }} Görev</span>
                <span style="font-size: 12px; color: #94a3b8; font-weight: 600;">/ {{ $totalTasksCount }} Toplam</span>
            </div>
            <!-- Görev Progress Bar -->
            <div style="background: #f1f5f9; height: 7px; border-radius: 4px; overflow: hidden; margin-bottom: 8px;">
                <div style="background: #8b5cf6; width: {{ min(100, $taskCompletionRate) }}%; height: 100%; border-radius: 4px; transition: width 0.4s ease;"></div>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 11.5px; color: #475569;">
                <span>Başarı: <strong style="color: #8b5cf6;">%{{ $taskCompletionRate }}</strong></span>
                <span>{{ $inProgressTasksCount }} Süreçte • {{ $todoTasksCount }} Bekleyen</span>
            </div>
        </div>

        <!-- KPI 4: PORTFÖY SAĞLIK İNDEKSİ (S.HEALTH & B.HEALTH) -->
        <div class="kpi-card" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; background: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.03); border-top: 3.5px solid #f59e0b;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                <span class="kpi-title" style="font-size: 12px; font-weight: 750; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">Portföy Sağlık İndeksi</span>
                <span style="font-size: 18px;">🛡️</span>
            </div>
            <div style="display: flex; align-items: baseline; gap: 6px; margin-bottom: 8px;">
                <span style="font-size: 20px; font-weight: 800; color: #0f172a;">{{ $totalProjects }} Proje</span>
                <span style="font-size: 12px; color: #94a3b8; font-weight: 600;">Aktif İzlemede</span>
            </div>
            <!-- Rozet Dağılımı -->
            <div style="display: flex; gap: 6px; margin-bottom: 8px;">
                <span style="font-size: 11px; font-weight: 700; color: #047857; background: #ecfdf5; padding: 3px 8px; border-radius: 6px; border: 1px solid #a7f3d0;">
                    🟢 {{ $onTrackProjectsCount }} Yolunda
                </span>
                <span style="font-size: 11px; font-weight: 700; color: #b45309; background: #fffbeb; padding: 3px 8px; border-radius: 6px; border: 1px solid #fde68a;">
                    🟡 {{ $atRiskProjectsCount }} Risk Altında
                </span>
                <span style="font-size: 11px; font-weight: 700; color: #b91c1c; background: #fef2f2; padding: 3px 8px; border-radius: 6px; border: 1px solid #fecaca;">
                    🔴 {{ $overdueProjectsCount }} Gecikmede
                </span>
            </div>
            <div style="font-size: 11px; color: #64748b;">
                {{ $waitingApprovalProjects }} Onay Darboğazı • {{ $criticalRisks->count() }} Kritik Risk
            </div>
        </div>
    </div>

    <!-- İKİ SÜTUNLU ANALİTİK PANEL: GÖREV DONUT GRAFİĞİ + PORTFÖY S.HEALTH & B.HEALTH TABLOSU -->
    <div style="display: grid; grid-template-columns: 340px 1fr; gap: 20px; margin-bottom: 24px;" class="dash-exec-grid">
        <!-- SOL SÜTUN: GÖREV DURUMU DONUT GRAFİĞİ (CELOXIS MODELİ) -->
        <div class="card" style="margin-bottom: 0; border: 1px solid #e2e8f0; border-radius: 14px; background: #ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.03); display: flex; flex-direction: column;">
            <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 14.5px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <span>🍩</span> Görev Durumu Dağılımı
                </span>
                <span class="badge" style="background: #f1f5f9; color: #475569; font-weight: 700;">{{ $totalTasksCount }} Görev</span>
            </div>
            <div class="card-body" style="padding: 20px; flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                @php
                    $pTodo = $totalTasksCount > 0 ? ($todoTasksCount / $totalTasksCount) : 0;
                    $pProg = $totalTasksCount > 0 ? ($inProgressTasksCount / $totalTasksCount) : 0;
                    $pRev  = $totalTasksCount > 0 ? ($reviewTasksCount / $totalTasksCount) : 0;
                    $pDone = $totalTasksCount > 0 ? ($doneTasksCount / $totalTasksCount) : 0;

                    $c = 2 * M_PI * 40; // 251.32
                    $dashDone = $pDone * $c;
                    $dashProg = $pProg * $c;
                    $dashRev  = $pRev  * $c;
                    $dashTodo = $pTodo * $c;

                    $offsetDone = 0;
                    $offsetProg = -$dashDone;
                    $offsetRev  = -($dashDone + $dashProg);
                    $offsetTodo = -($dashDone + $dashProg + $dashRev);
                @endphp

                <!-- MODERN SVG DONUT CHART -->
                <div style="position: relative; width: 160px; height: 160px; margin-bottom: 18px;">
                    <svg width="160" height="160" viewBox="0 0 100 100" style="transform: rotate(-90deg); border-radius: 50%;">
                        <!-- Arka Plan Halka -->
                        <circle cx="50" cy="50" r="40" fill="transparent" stroke="#f1f5f9" stroke-width="14" />
                        
                        @if($totalTasksCount > 0)
                            <!-- Tamamlandı (Yeşil) -->
                            <circle cx="50" cy="50" r="40" fill="transparent" stroke="#10b981" stroke-width="14"
                                stroke-dasharray="{{ $dashDone }} {{ $c }}" stroke-dashoffset="{{ $offsetDone }}" />

                            <!-- Süreçte (Mavi) -->
                            <circle cx="50" cy="50" r="40" fill="transparent" stroke="#0284c7" stroke-width="14"
                                stroke-dasharray="{{ $dashProg }} {{ $c }}" stroke-dashoffset="{{ $offsetProg }}" />

                            <!-- İncelemede (Mor) -->
                            <circle cx="50" cy="50" r="40" fill="transparent" stroke="#8b5cf6" stroke-width="14"
                                stroke-dasharray="{{ $dashRev }} {{ $c }}" stroke-dashoffset="{{ $offsetRev }}" />

                            <!-- Yapılacak (Gri/Sarı) -->
                            <circle cx="50" cy="50" r="40" fill="transparent" stroke="#cbd5e1" stroke-width="14"
                                stroke-dasharray="{{ $dashTodo }} {{ $c }}" stroke-dashoffset="{{ $offsetTodo }}" />
                        @endif
                    </svg>

                    <!-- Merkez İstatistik Etiketi -->
                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; pointer-events: none;">
                        <span style="font-size: 24px; font-weight: 850; color: #0f172a; line-height: 1;">{{ $totalTasksCount }}</span>
                        <span style="font-size: 10.5px; font-weight: 700; color: #64748b; margin-top: 3px; text-transform: uppercase;">Görev</span>
                    </div>
                </div>

                <!-- DÖKÜM LEJANTI VE SAYAÇLAR -->
                <div style="width: 100%; display: flex; flex-direction: column; gap: 7px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; padding: 4px 8px; border-radius: 6px; background: #f8fafc;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="width: 10px; height: 10px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                            <span style="color: #334155; font-weight: 600;">Tamamlandı</span>
                        </div>
                        <span style="font-weight: 800; color: #0f172a;">{{ $doneTasksCount }} <span style="font-weight: 500; color: #64748b; font-size: 11px;">(%{{ $totalTasksCount > 0 ? round(($doneTasksCount / $totalTasksCount) * 100) : 0 }})</span></span>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; padding: 4px 8px; border-radius: 6px; background: #f8fafc;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="width: 10px; height: 10px; border-radius: 50%; background: #0284c7; display: inline-block;"></span>
                            <span style="color: #334155; font-weight: 600;">Süreçte / Devam Eden</span>
                        </div>
                        <span style="font-weight: 800; color: #0f172a;">{{ $inProgressTasksCount }} <span style="font-weight: 500; color: #64748b; font-size: 11px;">(%{{ $totalTasksCount > 0 ? round(($inProgressTasksCount / $totalTasksCount) * 100) : 0 }})</span></span>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; padding: 4px 8px; border-radius: 6px; background: #f8fafc;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="width: 10px; height: 10px; border-radius: 50%; background: #8b5cf6; display: inline-block;"></span>
                            <span style="color: #334155; font-weight: 600;">İncelemede / Test</span>
                        </div>
                        <span style="font-weight: 800; color: #0f172a;">{{ $reviewTasksCount }} <span style="font-weight: 500; color: #64748b; font-size: 11px;">(%{{ $totalTasksCount > 0 ? round(($reviewTasksCount / $totalTasksCount) * 100) : 0 }})</span></span>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; padding: 4px 8px; border-radius: 6px; background: #f8fafc;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="width: 10px; height: 10px; border-radius: 50%; background: #cbd5e1; display: inline-block;"></span>
                            <span style="color: #334155; font-weight: 600;">Yapılacak (Backlog)</span>
                        </div>
                        <span style="font-weight: 800; color: #0f172a;">{{ $todoTasksCount }} <span style="font-weight: 500; color: #64748b; font-size: 11px;">(%{{ $totalTasksCount > 0 ? round(($todoTasksCount / $totalTasksCount) * 100) : 0 }})</span></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- SAĞ SÜTUN: PORTFÖY PROJE SAĞLIĞI & DURUMLARI (S.HEALTH & B.HEALTH TABLOSU) -->
        <div class="card" style="margin-bottom: 0; border: 1px solid #e2e8f0; border-radius: 14px; background: #ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.03); display: flex; flex-direction: column;">
            <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="font-size: 14.5px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <span>📁</span> Portföy Sağlık Durumları (Takvim & Bütçe)
                    </span>
                </div>
                <a href="{{ route('projects.index') }}" class="btn btn-sm btn-secondary" style="font-size: 11.5px; padding: 5px 12px; border-radius: 6px;">
                    Tüm Projeler Portföyü →
                </a>
            </div>
            <div class="card-body" style="padding: 0; flex: 1; overflow-x: auto;">
                <table class="app-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 10px 16px; font-size: 11.5px; font-weight: 750; color: #475569; text-align: left;">Proje Kodu & Başlık</th>
                            <th style="padding: 10px 12px; font-size: 11.5px; font-weight: 750; color: #475569; text-align: left;">Bölüm</th>
                            <th style="padding: 10px 12px; font-size: 11.5px; font-weight: 750; color: #475569; text-align: left; min-width: 110px;">İlerleme</th>
                            <th style="padding: 10px 12px; font-size: 11.5px; font-weight: 750; color: #475569; text-align: center;">Takvim Durumu</th>
                            <th style="padding: 10px 12px; font-size: 11.5px; font-weight: 750; color: #475569; text-align: center;">Bütçe Durumu</th>
                            <th style="padding: 10px 16px; font-size: 11.5px; font-weight: 750; color: #475569; text-align: right;">Yönetici</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($portfolioProjects as $prj)
                            @php
                                $sHealth = $prj->schedule_health;
                                $bHealth = $prj->budget_health;
                            @endphp
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#ffffff'">
                                <td style="padding: 12px 16px;">
                                    <a href="{{ route('projects.show', $prj->id) }}" style="font-weight: 750; font-size: 13px; color: #0284c7; text-decoration: none;">
                                        {{ $prj->code }}
                                    </a>
                                    <div style="font-size: 12px; color: #0f172a; font-weight: 600; margin-top: 2px;">
                                        {{ Str::limit($prj->title, 28) }}
                                    </div>
                                </td>
                                <td style="padding: 12px 12px; font-size: 12px; color: #64748b;">
                                    {{ $prj->bolum?->name ?? 'Fabrika Geneli' }}
                                </td>
                                <td style="padding: 12px 12px;">
                                    <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                                        <span>%{{ $prj->progress_percentage }}</span>
                                    </div>
                                    <div style="background: #e2e8f0; height: 6px; border-radius: 3px; overflow: hidden;">
                                        <div style="background: #0284c7; width: {{ $prj->progress_percentage }}%; height: 100%;"></div>
                                    </div>
                                </td>
                                <!-- S.Health Rozeti -->
                                <td style="padding: 12px 12px; text-align: center;">
                                    <span style="display: inline-block; padding: 4px 9px; font-size: 11px; font-weight: 750; border-radius: 6px; background: {{ $sHealth['bg'] }}; color: {{ $sHealth['color'] }}; border: 1px solid {{ $sHealth['border'] }}; white-space: nowrap;">
                                        {{ $sHealth['label'] }}
                                    </span>
                                </td>
                                <!-- B.Health Rozeti -->
                                <td style="padding: 12px 12px; text-align: center;">
                                    <span style="display: inline-block; padding: 4px 9px; font-size: 11px; font-weight: 750; border-radius: 6px; background: {{ $bHealth['bg'] }}; color: {{ $bHealth['color'] }}; border: 1px solid {{ $bHealth['border'] }}; white-space: nowrap;">
                                        {{ $bHealth['label'] }}
                                    </span>
                                </td>
                                <td style="padding: 12px 16px; text-align: right;">
                                    <div style="font-size: 12px; font-weight: 600; color: #334155;">
                                        {{ $prj->manager?->name ?? 'Belirtilmedi' }}
                                    </div>
                                    <div style="font-size: 11px; color: #94a3b8;">
                                        {{ $prj->target_end_date ? \Carbon\Carbon::parse($prj->target_end_date)->format('d.m.Y') : '-' }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 30px; color: #64748b; font-size: 12.5px;">
                                    Portföyde kayıtlı proje bulunmuyor.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 4. İKİ SÜTUNLU RADAR ALANI ("KİMDE BEKLİYOR?" DARBOĞAZ + RİSK RADARI) -->
    <div class="grid-2">
        <!-- SOL: "KİMDE BEKLİYOR?" DARBOĞAZ (BOTTLENECK) RADARI -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 14px 18px;">
                <span class="card-title" style="font-size: 14.5px; font-weight: 700; color: var(--danger); display: flex; align-items: center; gap: 8px;">
                    <span>🚨</span> "Kimde Bekliyor?" Darboğaz Radarı
                </span>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Proje</th>
                            <th>Bekleyen Aşama</th>
                            <th>Onay Mercii</th>
                            <th>Bekleme</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($waitingProjects as $wp)
                            <tr>
                                <td>
                                    <a href="{{ route('projects.show', $wp->id) }}" style="font-weight: 700; color: var(--navy); text-decoration: none;">
                                        {{ $wp->code }}
                                    </a>
                                    <div style="font-size: 11px; color: var(--text-muted);">{{ Str::limit($wp->title, 20) }}</div>
                                </td>
                                <td>
                                    <span class="badge" style="background: #fef2f2; color: #b91c1c; font-size: 11px;">
                                        {{ $wp->waiting_step_name ?? 'Onay Bekliyor' }}
                                    </span>
                                </td>
                                <td>
                                    <strong style="font-size: 12px; color: #1e293b;">{{ $wp->waitingRole?->name ?? 'Yetkili' }}</strong>
                                </td>
                                <td>
                                    <span style="font-size: 11px; font-weight: 700; color: #dc2626;">
                                        {{ $wp->waiting_since ? $wp->waiting_since->diffForHumans(null, true) : '-' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 12px;">
                                    Şu an onay darboğazında bekleyen proje bulunmuyor.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SAĞ: RİSK & GECİKEN GÖREV RADARI -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 14px 18px;">
                <span class="card-title" style="font-size: 14.5px; font-weight: 700; color: #b45309; display: flex; align-items: center; gap: 8px;">
                    <span>⚠️</span> Kritik Riskler & SLA Gecikmeleri
                </span>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Proje / Konu</th>
                            <th>Risk / Görev</th>
                            <th>Sorumlu</th>
                            <th>Durum</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($criticalRisks as $risk)
                            <tr>
                                <td>
                                    <strong style="color: var(--navy); font-size: 12px;">{{ $risk->project?->code ?? '-' }}</strong>
                                </td>
                                <td>
                                    <div style="font-size: 12px; font-weight: 600; color: #dc2626;">{{ Str::limit($risk->title, 25) }}</div>
                                </td>
                                <td>
                                    <span style="font-size: 11px; color: #475569;">{{ $risk->owner?->name ?? 'Atanmadı' }}</span>
                                </td>
                                <td>
                                    <span class="badge" style="background: #fee2e2; color: #991b1b; font-size: 10px;">KRİTİK RİSK</span>
                                </td>
                            </tr>
                        @empty
                            @forelse($overdueTasks as $task)
                                <tr>
                                    <td>
                                        <strong style="color: var(--navy); font-size: 12px;">{{ $task->project?->code ?? '-' }}</strong>
                                    </td>
                                    <td>
                                        <div style="font-size: 12px; font-weight: 600; color: #b45309;">{{ Str::limit($task->title, 25) }}</div>
                                    </td>
                                    <td>
                                        <span style="font-size: 11px; color: #475569;">{{ $task->assignee?->name ?? 'Ekip' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 10px;">GECİKTİ</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 12px;">
                                        Kritik risk veya geciken SLA görevi bulunmuyor.
                                    </td>
                                </tr>
                            @endforelse
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<!-- KİŞİSEL TAKVİM NOTU EKLEME VE HATIRLATICI MODALI -->
<div id="addNoteModalBackdrop" class="cal-modal-backdrop" onclick="closeAddNoteModal(event)" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div class="cal-modal-dialog" onclick="event.stopPropagation()" style="background: #ffffff; border-radius: 16px; width: 100%; max-width: 580px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 40px rgba(0,0,0,0.2); animation: modalSlide 0.2s ease;">
        <div style="padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 20px;" id="noteModalEmoji">📌</span>
                <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0;" id="noteModalTitle">
                    Kişisel Takvim Notu & Hatırlatıcı Ekle
                </h3>
            </div>
            <button type="button" onclick="closeAddNoteModal()" style="border: none; background: transparent; font-size: 20px; color: #94a3b8; cursor: pointer; padding: 4px;">
                ✕
            </button>
        </div>

        <form id="addCalendarNoteForm" onsubmit="submitCalendarNote(event)" style="padding: 22px 24px;">
            <input type="hidden" id="editNoteId" value="">
            <div style="margin-bottom: 16px;">
                <label style="font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">
                    Not / Hatırlatıcı Başlığı <span style="color: #ef4444;">*</span>
                </label>
                <input type="text" id="noteTitleInput" class="form-control" placeholder="Örn: İcra Kurulu Sunumu Hazırlığı" required style="width: 100%; height: 40px; border-radius: 8px; font-size: 13px;" oninput="updateDefaultEmailPreview()">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div>
                    <label style="font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">
                        Tarih <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="date" id="noteDateInput" class="form-control" required style="width: 100%; height: 40px; border-radius: 8px; font-size: 13px;" onchange="updateDefaultEmailPreview()">
                </div>
                <div>
                    <label style="font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">
                        Saat (İsteğe Bağlı)
                    </label>
                    <input type="time" id="noteTimeInput" class="form-control" value="09:00" style="width: 100%; height: 40px; border-radius: 8px; font-size: 13px;" onchange="updateDefaultEmailPreview()">
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 6px; display: block;">
                    Açıklama / Detay Notu
                </label>
                <textarea id="noteDescInput" class="form-control" rows="3" placeholder="Örn: Toplantı öncesi analiz tablolarının kontrolü ve slaytların birleştirilmesi..." style="width: 100%; border-radius: 8px; font-size: 13px;" oninput="updateDefaultEmailPreview()"></textarea>
            </div>

            <!-- RENK SEÇİMİ -->
            <div style="margin-bottom: 20px;">
                <label style="font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 8px; display: block;">
                    Etiket Vurgu Rengi
                </label>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <label style="cursor: pointer; display: flex; align-items: center; gap: 4px;">
                        <input type="radio" name="noteColor" value="#f59e0b" checked>
                        <span style="width: 22px; height: 22px; border-radius: 50%; background: #f59e0b; display: inline-block;"></span>
                    </label>
                    <label style="cursor: pointer; display: flex; align-items: center; gap: 4px;">
                        <input type="radio" name="noteColor" value="#0284c7">
                        <span style="width: 22px; height: 22px; border-radius: 50%; background: #0284c7; display: inline-block;"></span>
                    </label>
                    <label style="cursor: pointer; display: flex; align-items: center; gap: 4px;">
                        <input type="radio" name="noteColor" value="#10b981">
                        <span style="width: 22px; height: 22px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                    </label>
                    <label style="cursor: pointer; display: flex; align-items: center; gap: 4px;">
                        <input type="radio" name="noteColor" value="#8b5cf6">
                        <span style="width: 22px; height: 22px; border-radius: 50%; background: #8b5cf6; display: inline-block;"></span>
                    </label>
                    <label style="cursor: pointer; display: flex; align-items: center; gap: 4px;">
                        <input type="radio" name="noteColor" value="#ef4444">
                        <span style="width: 22px; height: 22px; border-radius: 50%; background: #ef4444; display: inline-block;"></span>
                    </label>
                </div>
            </div>

            <!-- HATIRLATMA KANALLARI (ZİL & E-POSTA) -->
            <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                <div style="font-size: 13px; font-weight: 750; color: #0f172a; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                    <span>🔔</span> <span>Hatırlatma Kanalları</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 13px; color: #334155; font-weight: 600;">
                        <input type="checkbox" id="chkSendBell" checked style="width: 17px; height: 17px; cursor: pointer;">
                        <span>🔔 Zil Bildirimi Gönder (Sistem içi bildirim merkezine ilet)</span>
                    </label>

                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 13px; color: #334155; font-weight: 600;">
                        <input type="checkbox" id="chkSendEmail" onchange="toggleEmailCustomBox()" style="width: 17px; height: 17px; cursor: pointer;">
                        <span>📧 Kendime E-Posta Hatırlatması İlet</span>
                    </label>
                </div>

                <!-- E-POSTA ÖZELLEŞTİRME KUTUSU (STANDART ŞABLON + KİŞİSELLEŞTİRME) -->
                <div id="emailCustomBox" style="display: none; margin-top: 14px; padding-top: 14px; border-top: 1px dashed #cbd5e1;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 12px; font-weight: 700; color: #0284c7; display: flex; align-items: center; gap: 5px;">
                            <span>✨</span> E-Posta Şablonunu Kişiselleştir
                        </span>
                        <button type="button" onclick="resetDefaultEmailTemplate()" style="border: none; background: transparent; color: #64748b; font-size: 11px; text-decoration: underline; cursor: pointer;">
                            Standart Şablona Dön
                        </button>
                    </div>

                    <div style="margin-bottom: 10px;">
                        <label style="font-size: 11.5px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">E-Posta Konusu:</label>
                        <input type="text" id="emailSubjectInput" class="form-control" style="width: 100%; height: 36px; border-radius: 6px; font-size: 12.5px;">
                    </div>

                    <div>
                        <label style="font-size: 11.5px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">E-Posta Gövde Metni:</label>
                        <textarea id="emailBodyInput" class="form-control" rows="5" style="width: 100%; border-radius: 6px; font-size: 12px; font-family: inherit; line-height: 1.5;"></textarea>
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeAddNoteModal()" style="height: 40px; padding: 0 18px; border-radius: 8px;">
                    Vazgeç
                </button>
                <button type="submit" class="btn btn-primary" id="btnSaveNote" style="height: 40px; padding: 0 22px; border-radius: 8px; font-weight: 700;">
                    <span>✓</span> Notu Kaydet & Hatırlatıcı Kur
                </button>
            </div>
        </form>
    </div>
</div>

<!-- KİŞİSEL NOT GÖRÜNTÜLEME VE SİLME MODALI -->
<div id="viewNoteModalBackdrop" class="cal-modal-backdrop" onclick="closeViewNoteModal(event)" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div class="cal-modal-dialog" onclick="event.stopPropagation()" style="background: #ffffff; border-radius: 16px; width: 100%; max-width: 500px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); animation: modalSlide 0.2s ease;">
        <div style="padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 20px;" id="viewNoteEmoji">📌</span>
                <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0;" id="viewNoteModalTitle">
                    Not Detayı
                </h3>
            </div>
            <button type="button" onclick="closeViewNoteModal()" style="border: none; background: transparent; font-size: 20px; color: #94a3b8; cursor: pointer; padding: 4px;">
                ✕
            </button>
        </div>

        <div style="padding: 22px 24px;">
            <div style="margin-bottom: 14px;">
                <span style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; display: block; margin-bottom: 2px;">Tarih & Saat:</span>
                <div style="font-size: 14px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <span id="viewNoteDate"></span> • <span id="viewNoteTime" style="color: #0284c7;"></span>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <span style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; display: block; margin-bottom: 4px;">Açıklama:</span>
                <div id="viewNoteDesc" style="font-size: 13px; color: #334155; line-height: 1.5; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0; white-space: pre-wrap;"></div>
            </div>

            <div style="display: flex; gap: 8px; margin-bottom: 20px;">
                <span id="viewNoteBellBadge" class="badge" style="font-size: 11px; padding: 4px 10px; border-radius: 6px; background: #f1f5f9; color: #475569;">
                    🔔 Zil Bildirimi: Açık
                </span>
                <span id="viewNoteEmailBadge" class="badge" style="font-size: 11px; padding: 4px 10px; border-radius: 6px; background: #f1f5f9; color: #475569;">
                    📧 E-Posta: İletildi
                </span>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 16px; gap: 10px;">
                <button type="button" class="btn btn-sm btn-danger" onclick="deleteCurrentCalendarNote()" style="display: inline-flex; align-items: center; gap: 5px; font-weight: 700; font-size: 12px; padding: 6px 14px; border-radius: 7px;">
                    <span>🗑️</span> Notu Sil
                </button>
                <div style="display: flex; gap: 8px;">
                    <button type="button" class="btn btn-secondary" onclick="closeViewNoteModal()" style="font-size: 12.5px; padding: 6px 18px; border-radius: 7px;">
                        Kapat
                    </button>
                    <button type="button" class="btn btn-primary" onclick="openEditCurrentNoteModal()" style="font-size: 12.5px; padding: 6px 18px; border-radius: 7px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                        <span>✏️</span> Notu Düzenle
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('quickJumpSearch');
        if (!searchInput) return;

        // 1. Ctrl + K / Cmd + K Kısayol Dinleyicisi
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            } else if (e.key === 'Escape' && document.activeElement === searchInput) {
                searchInput.value = '';
                searchInput.blur();
                filterDashboardItems('');
            }
        });

        // 2. Canlı Filtreleme (Live Quick Jump)
        searchInput.addEventListener('input', function() {
            filterDashboardItems(this.value.trim().toLowerCase());
        });

        function filterDashboardItems(query) {
            // Görev kartlarını filtrele
            document.querySelectorAll('#tab-tasks-container > div, .card-body > div').forEach(function(el) {
                if (el.querySelector('a') || el.querySelector('h4') || el.querySelector('strong')) {
                    const text = el.textContent.toLowerCase();
                    if (!query || text.includes(query)) {
                        el.style.display = '';
                    } else {
                        el.style.display = 'none';
                    }
                }
            });

            // Tablo satırlarını filtrele
            document.querySelectorAll('tbody tr').forEach(function(row) {
                const rowText = row.textContent.toLowerCase();
                if (!query || rowText.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    });
</script>

<style>
/* ==========================================================================
   KİŞİSEL ÇALIŞMA TAKVİMİ TASARIM STİLLERİ
   ========================================================================== */
.cal-day-cell {
    background: #ffffff;
    min-height: 115px;
    padding: 8px;
    display: flex;
    flex-direction: column;
    position: relative;
    transition: background 0.15s ease;
}
.cal-day-cell:hover {
    background: #f8fafc;
}
.cal-day-cell.other-month {
    background: #fbfcfe;
    opacity: 0.55;
}
.cal-day-cell.today {
    background: #f0f9ff;
    box-shadow: inset 0 0 0 2px #0284c7;
}

.cal-day-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}
.cal-day-num {
    font-size: 12.5px;
    font-weight: 750;
    color: #1e293b;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}
.cal-day-cell.today .cal-day-num {
    background: #0284c7;
    color: #ffffff;
    box-shadow: 0 2px 5px rgba(2, 132, 199, 0.4);
}
.cal-quick-add-btn {
    opacity: 0;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: none;
    background: #e0f2fe;
    color: #0369a1;
    font-size: 13px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.15s ease;
}
.cal-day-cell:hover .cal-quick-add-btn {
    opacity: 1;
}
.cal-quick-add-btn:hover {
    background: #0284c7;
    color: #ffffff;
    transform: scale(1.1);
}

.cal-events-wrap {
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1;
    overflow: hidden;
}

.cal-event-pill {
    font-size: 11px;
    font-weight: 650;
    padding: 3px 6px;
    border-radius: 6px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
    transition: transform 0.1s ease, box-shadow 0.1s ease;
    border: 1px solid rgba(0,0,0,0.06);
}
.cal-event-pill:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.cal-more-pill {
    font-size: 10px;
    font-weight: 750;
    color: #64748b;
    padding: 2px 5px;
    background: #f1f5f9;
    border-radius: 4px;
    cursor: pointer;
    text-align: center;
    margin-top: 2px;
}
.cal-more-pill:hover {
    background: #e2e8f0;
    color: #0f172a;
}

@keyframes modalSlide {
    from { opacity: 0; transform: translateY(12px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

@media (max-width: 768px) {
    .cal-day-cell {
        min-height: 70px;
        padding: 4px;
    }
    .cal-event-pill {
        font-size: 10px;
        padding: 2px 4px;
    }
}

@media (max-width: 992px) {
    .dash-exec-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<script>
// ==========================================================================
// KİŞİSEL ÇALIŞMA TAKVİMİ MOTORU (JAVASCRIPT)
// ==========================================================================
let calCurrentDate = new Date();
let calendarAllEvents = [];
let currentCalendarFilter = 'all';
let currentSelectedDateStr = '';
let currentActiveViewNote = null;

const trMonthNames = [
    'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran',
    'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'
];

document.addEventListener('DOMContentLoaded', function() {
    loadCalendarEventsForMonth();
});

// Ay Etkinliklerini Yükle
function loadCalendarEventsForMonth() {
    const year = calCurrentDate.getFullYear();
    const month = calCurrentDate.getMonth();

    // Ay başı ve sonu
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);

    // 7 gün öncesi ve sonrasını da kapsa
    const startDate = new Date(firstDay);
    startDate.setDate(startDate.getDate() - 7);
    const endDate = new Date(lastDay);
    endDate.setDate(endDate.getDate() + 7);

    const startStr = formatDateISO(startDate);
    const endStr = formatDateISO(endDate);

    // Başlık etiketini güncelle
    document.getElementById('calCurrentMonthLabel').innerText = `${trMonthNames[month]} ${year}`;

    fetch(`{{ route('dashboard.calendar_events') }}?start=${startStr}&end=${endStr}`)
        .then(res => res.json())
        .then(data => {
            calendarAllEvents = data || [];
            updateCalendarEventCounters();
            renderCalendarGrid();
        })
        .catch(err => {
            console.error('Takvim etkinlikleri yüklenemedi:', err);
            renderCalendarGrid();
        });
}

function updateCalendarEventCounters() {
    const all = calendarAllEvents.length;
    const tasks = calendarAllEvents.filter(e => e.type === 'task').length;
    const meetings = calendarAllEvents.filter(e => e.type === 'meeting').length;
    const notes = calendarAllEvents.filter(e => e.type === 'note').length;

    document.getElementById('cntAllEvents').innerText = all;
    document.getElementById('cntTaskEvents').innerText = tasks;
    document.getElementById('cntMeetingEvents').innerText = meetings;
    document.getElementById('cntNoteEvents').innerText = notes;
}

// Takvim Hücre Izgarasını Çizme
function renderCalendarGrid() {
    const grid = document.getElementById('calDaysGrid');
    if (!grid) return;
    grid.innerHTML = '';

    const year = calCurrentDate.getFullYear();
    const month = calCurrentDate.getMonth();

    const todayStr = formatDateISO(new Date());

    const firstDayOfMonth = new Date(year, month, 1);
    const lastDayOfMonth = new Date(year, month + 1, 0);

    // Pazartesi ilk gün (Pzt=0, Sal=1 ... Paz=6)
    let startDayOfWeek = firstDayOfMonth.getDay() - 1;
    if (startDayOfWeek === -1) startDayOfWeek = 6;

    const daysInMonth = lastDayOfMonth.getDate();

    // Önceki ayın son günleri
    const prevMonthLastDay = new Date(year, month, 0).getDate();
    for (let i = startDayOfWeek - 1; i >= 0; i--) {
        const d = prevMonthLastDay - i;
        const cellDate = new Date(year, month - 1, d);
        grid.appendChild(createDayCellElement(cellDate, true, todayStr));
    }

    // Bu ayın günleri
    for (let day = 1; day <= daysInMonth; day++) {
        const cellDate = new Date(year, month, day);
        grid.appendChild(createDayCellElement(cellDate, false, todayStr));
    }

    // Sonraki ayın tamamlayıcı günleri (42 hücreye tamamla)
    const totalCellsSoFar = startDayOfWeek + daysInMonth;
    const remainingCells = (totalCellsSoFar % 7 === 0) ? 0 : (7 - (totalCellsSoFar % 7));
    for (let day = 1; day <= remainingCells; day++) {
        const cellDate = new Date(year, month + 1, day);
        grid.appendChild(createDayCellElement(cellDate, true, todayStr));
    }
}

// Tek Bir Gün Hücresi Üretici
function createDayCellElement(dateObj, isOtherMonth, todayStr) {
    const dateStr = formatDateISO(dateObj);
    const isToday = (dateStr === todayStr);

    const cell = document.createElement('div');
    cell.className = `cal-day-cell ${isOtherMonth ? 'other-month' : ''} ${isToday ? 'today' : ''}`;
    cell.setAttribute('data-date', dateStr);

    // Gün Başlığı
    const header = document.createElement('div');
    header.className = 'cal-day-header';

    const numSpan = document.createElement('span');
    numSpan.className = 'cal-day-num';
    numSpan.innerText = dateObj.getDate();

    const quickAdd = document.createElement('button');
    quickAdd.type = 'button';
    quickAdd.className = 'cal-quick-add-btn';
    quickAdd.title = `${dateObj.getDate()} ${trMonthNames[dateObj.getMonth()]} tarihine not ekle`;
    quickAdd.innerText = '+';
    quickAdd.onclick = function(e) {
        e.stopPropagation();
        openAddNoteModal(dateStr);
    };

    header.appendChild(numSpan);
    header.appendChild(quickAdd);
    cell.appendChild(header);

    // O güne ait filtrelenmiş etkinlikler
    const eventsWrap = document.createElement('div');
    eventsWrap.className = 'cal-events-wrap';

    const dayEvents = calendarAllEvents.filter(e => {
        const matchesDate = (e.date === dateStr);
        const matchesType = (currentCalendarFilter === 'all' || e.type === currentCalendarFilter);
        return matchesDate && matchesType;
    });

    const maxDisplay = 3;
    const displayEvents = dayEvents.slice(0, maxDisplay);

    displayEvents.forEach(evt => {
        const pill = document.createElement('div');
        pill.className = 'cal-event-pill';
        pill.style.background = evt.badge_bg;
        pill.style.color = evt.badge_color;
        pill.title = `${evt.time ? '[' + evt.time + '] ' : ''}${evt.title} (${evt.description || ''})`;

        const icon = (evt.type === 'task' ? '📋' : (evt.type === 'meeting' ? '📅' : '📌'));
        pill.innerHTML = `<span>${icon}</span> <span style="overflow:hidden;text-overflow:ellipsis;">${evt.title}</span>`;

        pill.onclick = function(e) {
            e.stopPropagation();
            if (evt.type === 'note') {
                openViewNoteModal(evt);
            } else if (evt.url && evt.url !== '#') {
                window.location.href = evt.url;
            } else {
                showDayAgenda(dateStr, dayEvents);
            }
        };

        eventsWrap.appendChild(pill);
    });

    if (dayEvents.length > maxDisplay) {
        const morePill = document.createElement('div');
        morePill.className = 'cal-more-pill';
        morePill.innerText = `+${dayEvents.length - maxDisplay} daha...`;
        morePill.onclick = function(e) {
            e.stopPropagation();
            showDayAgenda(dateStr, dayEvents);
        };
        eventsWrap.appendChild(morePill);
    }

    cell.appendChild(eventsWrap);

    // Güne tıklanınca ajanda çekmecesini aç
    cell.onclick = function() {
        showDayAgenda(dateStr, dayEvents);
    };

    return cell;
}

// Gün Ajandası Çekmecesini Göster
function showDayAgenda(dateStr, dayEvents) {
    currentSelectedDateStr = dateStr;
    const box = document.getElementById('calDayAgendaBox');
    const title = document.getElementById('agendaDayTitle');
    const list = document.getElementById('agendaItemsList');

    const d = new Date(dateStr);
    title.innerText = `${d.getDate()} ${trMonthNames[d.getMonth()]} ${d.getFullYear()} Ajanda & Etkinlikleri (${dayEvents.length} Kayıt)`;

    list.innerHTML = '';
    if (dayEvents.length === 0) {
        list.innerHTML = `<div style="text-align: center; padding: 20px; color: #64748b; font-size: 13px;">Bu tarihte planlanmış bir görev, toplantı veya not bulunmuyor.</div>`;
    } else {
        dayEvents.forEach(evt => {
            const item = document.createElement('div');
            item.style.background = '#ffffff';
            item.style.border = '1px solid #e2e8f0';
            item.style.borderRadius = '8px';
            item.style.padding = '10px 14px';
            item.style.display = 'flex';
            item.style.justifyContent = 'space-between';
            item.style.alignItems = 'center';

            const icon = (evt.type === 'task' ? '📋' : (evt.type === 'meeting' ? '📅' : '📌'));
            const typeLabel = (evt.type === 'task' ? 'Görev' : (evt.type === 'meeting' ? 'Toplantı' : 'Kişisel Not'));

            item.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 18px;">${icon}</span>
                    <div>
                        <div style="font-size: 13.5px; font-weight: 700; color: #0f172a;">${evt.title}</div>
                        <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                            <span>${typeLabel}</span> • <span>⏰ ${evt.time || 'Tüm Gün'}</span> ${evt.project_code ? '• <strong>' + evt.project_code + '</strong>' : ''}
                        </div>
                    </div>
                </div>
                <div>
                    ${evt.type === 'note' ? `<button type="button" class="btn btn-sm btn-light" style="font-size: 11.5px; border: 1px solid #cbd5e1;" onclick="openViewNoteModal(${JSON.stringify(evt).replace(/"/g, '&quot;')})">İncele / Sil</button>` : `<a href="${evt.url}" class="btn btn-sm btn-primary" style="font-size: 11.5px; text-decoration: none;">Detaya Git →</a>`}
                </div>
            `;
            list.appendChild(item);
        });
    }

    box.style.display = 'block';
    box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function closeAgendaBox() {
    document.getElementById('calDayAgendaBox').style.display = 'none';
}

// Navigasyon Fonksiyonları
function navigateCalendarMonth(offset) {
    calCurrentDate.setMonth(calCurrentDate.getMonth() + offset);
    closeAgendaBox();
    loadCalendarEventsForMonth();
}

function jumpCalendarToday() {
    calCurrentDate = new Date();
    closeAgendaBox();
    loadCalendarEventsForMonth();
}

function filterCalendarEvents(type, btnElement) {
    currentCalendarFilter = type;
    document.querySelectorAll('.cal-filter-btn').forEach(btn => {
        btn.style.background = 'transparent';
        btn.classList.remove('active');
        if (btn.getAttribute('data-type') === 'all') btn.style.color = '#0284c7';
    });

    btnElement.classList.add('active');
    btnElement.style.background = '#0284c7';
    btnElement.style.color = '#ffffff';

    renderCalendarGrid();
}

// ==========================================================================
// NOT EKLEME & E-POSTA ŞABLONU FONKSİYONLARI
// ==========================================================================
function openAddNoteModal(preselectedDate = null) {
    const backdrop = document.getElementById('addNoteModalBackdrop');
    const dateInput = document.getElementById('noteDateInput');

    document.getElementById('editNoteId').value = '';
    document.getElementById('noteModalTitle').innerText = 'Kişisel Takvim Notu & Hatırlatıcı Ekle';
    document.getElementById('noteModalEmoji').innerText = '📌';
    document.getElementById('btnSaveNote').innerHTML = '<span>✓</span> Notu Kaydet & Hatırlatıcı Kur';

    if (preselectedDate) {
        dateInput.value = preselectedDate;
    } else {
        dateInput.value = formatDateISO(new Date());
    }

    document.getElementById('noteTitleInput').value = '';
    document.getElementById('noteDescInput').value = '';
    document.getElementById('noteTimeInput').value = '10:00';

    const defaultColorRadio = document.querySelector('input[name="noteColor"][value="#f59e0b"]');
    if (defaultColorRadio) defaultColorRadio.checked = true;

    document.getElementById('chkSendBell').checked = true;
    document.getElementById('chkSendEmail').checked = false;
    document.getElementById('emailCustomBox').style.display = 'none';

    updateDefaultEmailPreview();
    backdrop.style.display = 'flex';
}

function openEditCurrentNoteModal() {
    if (!currentActiveViewNote) return;
    const note = currentActiveViewNote;
    closeViewNoteModal();
    openEditNoteModal(note);
}

function openEditNoteModal(noteEvt) {
    if (!noteEvt) return;
    const backdrop = document.getElementById('addNoteModalBackdrop');

    document.getElementById('editNoteId').value = noteEvt.raw_id;
    document.getElementById('noteModalTitle').innerText = 'Kişisel Hatırlatıcı Notunu Düzenle';
    document.getElementById('noteModalEmoji').innerText = '✏️';
    document.getElementById('btnSaveNote').innerHTML = '<span>💾</span> Değişiklikleri Kaydet';

    document.getElementById('noteTitleInput').value = noteEvt.title || '';
    document.getElementById('noteDateInput').value = noteEvt.date || '';
    document.getElementById('noteTimeInput').value = (noteEvt.time && noteEvt.time !== 'Tüm Gün') ? noteEvt.time : '';
    document.getElementById('noteDescInput').value = noteEvt.description || '';

    if (noteEvt.color) {
        const colorRadio = document.querySelector(`input[name="noteColor"][value="${noteEvt.color}"]`);
        if (colorRadio) colorRadio.checked = true;
    }

    document.getElementById('chkSendBell').checked = !!noteEvt.send_bell;
    document.getElementById('chkSendEmail').checked = !!noteEvt.send_email;

    if (noteEvt.send_email) {
        document.getElementById('emailCustomBox').style.display = 'block';
        document.getElementById('emailSubjectInput').value = noteEvt.email_subject || '';
        document.getElementById('emailBodyInput').value = noteEvt.email_body || '';
    } else {
        document.getElementById('emailCustomBox').style.display = 'none';
        updateDefaultEmailPreview();
    }

    backdrop.style.display = 'flex';
}

function closeAddNoteModal(e) {
    if (!e || e.target.id === 'addNoteModalBackdrop') {
        document.getElementById('addNoteModalBackdrop').style.display = 'none';
    }
}

function toggleEmailCustomBox() {
    const chk = document.getElementById('chkSendEmail');
    const box = document.getElementById('emailCustomBox');
    box.style.display = chk.checked ? 'block' : 'none';
    if (chk.checked) {
        updateDefaultEmailPreview();
    }
}

function updateDefaultEmailPreview() {
    const title = document.getElementById('noteTitleInput').value.trim() || 'Yeni Hatırlatıcı Notu';
    const dateVal = document.getElementById('noteDateInput').value;
    const timeVal = document.getElementById('noteTimeInput').value || 'Tüm Gün';
    const descVal = document.getElementById('noteDescInput').value.trim() || 'Açıklama belirtilmedi.';

    let formattedDate = dateVal;
    if (dateVal) {
        const parts = dateVal.split('-');
        if (parts.length === 3) formattedDate = `${parts[2]}.${parts[1]}.${parts[0]}`;
    }

    const defaultSubj = `[Köksan PMS Hatırlatıcı] ${title}`;
    const defaultBody = `Sayın {{ auth()->user()->name }},\n\nKöksan Proje Yönetim Sistemi (PPM) kişisel takviminizde planladığınız "${title}" konulu hatırlatmanız aşağıdadır:\n\n📅 Tarih: ${formattedDate}\n⏰ Saat: ${timeVal}\n📝 Detay: ${descVal}\n\nİyi çalışmalar dileriz.\nKöksan Enterprise PPM`;

    const subjInput = document.getElementById('emailSubjectInput');
    const bodyInput = document.getElementById('emailBodyInput');

    if (!subjInput.getAttribute('data-customized')) subjInput.value = defaultSubj;
    if (!bodyInput.getAttribute('data-customized')) bodyInput.value = defaultBody;
}

function resetDefaultEmailTemplate() {
    const subjInput = document.getElementById('emailSubjectInput');
    const bodyInput = document.getElementById('emailBodyInput');
    subjInput.removeAttribute('data-customized');
    bodyInput.removeAttribute('data-customized');
    updateDefaultEmailPreview();
}

// Form Gönderimi (AJAX POST)
function submitCalendarNote(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSaveNote');
    const editId = document.getElementById('editNoteId').value;
    const isEdit = !!editId;

    btn.disabled = true;
    btn.innerHTML = isEdit ? 'Değişiklikler Kaydediliyor...' : 'Kaydediliyor...';

    const title = document.getElementById('noteTitleInput').value.trim();
    const note_date = document.getElementById('noteDateInput').value;
    const note_time = document.getElementById('noteTimeInput').value;
    const description = document.getElementById('noteDescInput').value.trim();
    const color = document.querySelector('input[name="noteColor"]:checked')?.value || '#f59e0b';
    const send_bell = document.getElementById('chkSendBell').checked ? 1 : 0;
    const send_email = document.getElementById('chkSendEmail').checked ? 1 : 0;
    const email_subject = document.getElementById('emailSubjectInput').value.trim();
    const email_body = document.getElementById('emailBodyInput').value.trim();

    const targetUrl = isEdit ? `{{ url('dashboard/calendar-notes') }}/${editId}` : "{{ route('dashboard.calendar_notes.store') }}";
    const method = isEdit ? "PUT" : "POST";

    fetch(targetUrl, {
        method: method,
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        },
        body: JSON.stringify({
            title, note_date, note_time, description, color,
            send_bell, send_email, email_subject, email_body
        })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = isEdit ? '<span>💾</span> Değişiklikleri Kaydet' : '<span>✓</span> Notu Kaydet & Hatırlatıcı Kur';
        if (data.success) {
            closeAddNoteModal();
            loadCalendarEventsForMonth();
            if (typeof currentSelectedDateStr !== 'undefined' && currentSelectedDateStr) {
                renderAgendaEvents(currentSelectedDateStr);
            }
            alert('✅ ' + data.message);
        } else {
            alert('Hata: ' + (data.message || 'İşlem başarısız.'));
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = isEdit ? '<span>💾</span> Değişiklikleri Kaydet' : '<span>✓</span> Notu Kaydet & Hatırlatıcı Kur';
        alert('Bağlantı hatası: ' + err.message);
    });
}

// ==========================================================================
// NOT DETAY GÖRÜNTÜLEME & SİLME
// ==========================================================================
function openViewNoteModal(noteEvt) {
    currentActiveViewNote = noteEvt;
    const backdrop = document.getElementById('viewNoteModalBackdrop');

    document.getElementById('viewNoteModalTitle').innerText = noteEvt.title;
    document.getElementById('viewNoteDate').innerText = `📅 ${noteEvt.date}`;
    document.getElementById('viewNoteTime').innerText = `⏰ ${noteEvt.time}`;
    document.getElementById('viewNoteDesc').innerText = noteEvt.description || 'Detay açıklaması girilmedi.';

    const bellBadge = document.getElementById('viewNoteBellBadge');
    bellBadge.innerText = noteEvt.send_bell ? '🔔 Zil Bildirimi: Açık' : '🔔 Zil: Kapalı';
    bellBadge.style.color = noteEvt.send_bell ? '#047857' : '#64748b';
    bellBadge.style.background = noteEvt.send_bell ? '#ecfdf5' : '#f1f5f9';

    const emailBadge = document.getElementById('viewNoteEmailBadge');
    emailBadge.innerText = noteEvt.send_email ? '📧 E-Posta: İletildi' : '📧 E-Posta: Kapalı';
    emailBadge.style.color = noteEvt.send_email ? '#0284c7' : '#64748b';
    emailBadge.style.background = noteEvt.send_email ? '#eff6ff' : '#f1f5f9';

    backdrop.style.display = 'flex';
}

function closeViewNoteModal(e) {
    if (!e || e.target.id === 'viewNoteModalBackdrop') {
        document.getElementById('viewNoteModalBackdrop').style.display = 'none';
    }
}

function deleteCurrentCalendarNote() {
    if (!currentActiveViewNote || !currentActiveViewNote.raw_id) return;
    if (!confirm(`'${currentActiveViewNote.title}' notunu silmek istediğinize emin misiniz?`)) return;

    fetch(`{{ url('dashboard/calendar-notes') }}/${currentActiveViewNote.raw_id}`, {
        method: "DELETE",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeViewNoteModal();
            closeAgendaBox();
            loadCalendarEventsForMonth();
            alert('✓ Not silindi.');
        } else {
            alert('Hata: ' + data.message);
        }
    })
    .catch(err => alert('Silme hatası: ' + err.message));
}

// Yardımcı: Tarih Formatla (YYYY-MM-DD)
function formatDateISO(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}
</script>

@endpush