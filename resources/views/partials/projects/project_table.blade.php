<div class="card" style="border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
    <div class="table-responsive">
        <table class="table" style="margin-bottom: 0; width: 100%; border-collapse: collapse; font-size: 12.5px;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase;">
                    <th style="padding: 12px 14px; white-space: nowrap; width: 1%;">KOD</th>
                    <th style="padding: 12px 16px;">PROJE ADI & TARİH DETAYLARI</th>
                    <th style="padding: 12px 14px; white-space: nowrap; width: 1%; text-align: center;">GİZLİLİK</th>
                    <th style="padding: 12px 14px; white-space: nowrap; width: 1%;">DURUM</th>
                    <th style="padding: 12px 14px; white-space: nowrap; width: 1%;">ÖNCELİK</th>
                    <th style="padding: 12px 14px; width: 120px;">İLERLEME</th>
                    <th style="padding: 12px 14px; white-space: nowrap; width: 1%;">BÜTÇE</th>
                    <th style="padding: 12px 14px; white-space: nowrap;">YÖNETİCİ & EKİP</th>
                    <th style="padding: 12px 14px; white-space: nowrap;">SPONSOR</th>
                    <th style="padding: 12px 16px; text-align: right; white-space: nowrap; width: 1%;">İŞLEMLER</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $p)
                    @php
                        $canManage = auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->hasPermission('proje.manage') || auth()->user()->hasPermission('proje.edit') || $p->manager_id === auth()->id() || $p->sponsor_id === auth()->id() || $p->created_by === auth()->id();
                        $canDelete = auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isDeveloper() || auth()->user()->hasPermission('proje.delete');
                        $isPrivate = ($p->visibility ?? 'private') === 'private';

                        // Duruma göre kenarlık ve arka plan renklendirme haritası
                        $statusBorderMap = [
                            'draft' => ['border' => '#94a3b8', 'bg_hover' => 'rgba(241, 245, 249, 0.7)'],
                            'waiting_approval' => ['border' => '#0284c7', 'bg_hover' => 'rgba(224, 242, 254, 0.4)'],
                            'pending' => ['border' => '#0284c7', 'bg_hover' => 'rgba(224, 242, 254, 0.4)'],
                            'review' => ['border' => '#0284c7', 'bg_hover' => 'rgba(224, 242, 254, 0.4)'],
                            'in_progress' => ['border' => '#f59e0b', 'bg_hover' => 'rgba(254, 243, 199, 0.4)'],
                            'completed' => ['border' => '#10b981', 'bg_hover' => 'rgba(220, 252, 231, 0.4)'],
                            'done' => ['border' => '#10b981', 'bg_hover' => 'rgba(220, 252, 231, 0.4)'],
                            'cancelled' => ['border' => '#64748b', 'bg_hover' => 'rgba(248, 250, 252, 0.8)'],
                            'on_hold' => ['border' => '#64748b', 'bg_hover' => 'rgba(248, 250, 252, 0.8)'],
                            'blocked' => ['border' => '#ef4444', 'bg_hover' => 'rgba(254, 226, 226, 0.4)'],
                            'critical' => ['border' => '#ef4444', 'bg_hover' => 'rgba(254, 226, 226, 0.4)'],
                            'waiting_ehs' => ['border' => '#ef4444', 'bg_hover' => 'rgba(254, 226, 226, 0.4)'],
                        ];
                        $stConf = $statusBorderMap[$p->status] ?? $statusBorderMap['draft'];

                        // Ekip kişi sayısı
                        $teamCount = $p->stakeholders_count ?? $p->stakeholders()->count();
                        if ($teamCount === 0 && ($p->manager_id || $p->sponsor_id)) {
                            $teamCount = ($p->manager_id ? 1 : 0) + ($p->sponsor_id && $p->sponsor_id !== $p->manager_id ? 1 : 0);
                        }

                        // Tarihler ve Geri Sayım Hesaplaması
                        $startDate = $p->start_date ? \Carbon\Carbon::parse($p->start_date)->startOfDay() : null;
                        $targetEndDate = $p->target_end_date ? \Carbon\Carbon::parse($p->target_end_date)->startOfDay() : null;
                        $today = \Carbon\Carbon::today();
                        $countdownText = null;
                        $countdownBg = '#f1f5f9';
                        $countdownColor = '#475569';
                        $countdownBorder = '#cbd5e1';

                        if ($startDate) {
                            $diff = (int) $today->diffInDays($startDate, false);
                            if ($diff > 0) {
                                $countdownText = "Başlangıca {$diff} gün kaldı";
                                $countdownBg = $diff <= 7 ? '#fef3c7' : '#e0f2fe';
                                $countdownColor = $diff <= 7 ? '#b45309' : '#0369a1';
                                $countdownBorder = $diff <= 7 ? '#fde68a' : '#bae6fd';
                            } elseif ($diff === 0) {
                                $countdownText = "Bugün Başlıyor";
                                $countdownBg = '#dcfce7';
                                $countdownColor = '#15803d';
                                $countdownBorder = '#bbf7d0';
                            } else {
                                $pastDays = abs($diff);
                                if (in_array($p->status, ['completed', 'done'])) {
                                    $countdownText = "Tamamlandı";
                                    $countdownBg = '#dcfce7';
                                    $countdownColor = '#15803d';
                                    $countdownBorder = '#bbf7d0';
                                } elseif ($p->status === 'in_progress') {
                                    $countdownText = "{$pastDays} gündür sürüyor";
                                    $countdownBg = '#e0f2fe';
                                    $countdownColor = '#0369a1';
                                    $countdownBorder = '#bae6fd';
                                } else {
                                    $countdownText = "Başlangıç {$pastDays} gün geçti";
                                    $countdownBg = '#fee2e2';
                                    $countdownColor = '#b91c1c';
                                    $countdownBorder = '#fecaca';
                                }
                            }
                        }
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9; border-left: 5px solid {{ $stConf['border'] }}; transition: background 0.15s;" onmouseover="this.style.background='{{ $stConf['bg_hover'] }}'" onmouseout="this.style.background='#ffffff'">
                        <!-- KOD (Asla alt satıra geçmez) -->
                        <td style="padding: 12px 14px; font-weight: 800; white-space: nowrap; width: 1%;">
                            <a href="{{ route('projects.show', $p->id) }}" style="color: #0284c7; text-decoration: none; font-size: 11.5px; font-weight: 800; background: #e0f2fe; padding: 3px 8px; border-radius: 5px; white-space: nowrap; display: inline-block; letter-spacing: -0.2px;">
                                {{ $p->code }}
                            </a>
                        </td>

                            <!-- PROJE ADI & TARİH DETAYLARI -->
                        <td style="padding: 12px 16px; min-width: 270px; max-width: 420px;">
                            <a href="{{ route('projects.show', $p->id) }}" style="text-decoration: none; font-weight: 800; color: #0f172a; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 13.5px;" title="{{ $p->title }}">
                                {{ $p->title }}
                            </a>
                            @if(!empty($p->tags_array))
                                <span style="display: inline-flex; gap: 4px; margin-left: 6px; vertical-align: middle;">
                                    @foreach(array_slice($p->tags_array, 0, 2) as $t)
                                        <span style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; font-size: 9.5px; font-weight: 750; padding: 1px 5px; border-radius: 4px;">#{{ $t }}</span>
                                    @endforeach
                                </span>
                            @endif
                            
                            <!-- 1. SATIR: BÖLÜM, BAŞLATAN & PROJE YÖNETİCİSİ -->
                            <div style="font-size: 11px; color: #475569; margin-top: 4px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <span style="font-weight: 700; color: #334155;">🏢 {{ $p->bolum?->ad ?? 'Genel' }}</span>
                                <span style="color: #cbd5e1;">•</span>
                                <span>✍️ Başlatan: <strong style="color: #1e293b;">{{ $p->creator?->name ?? 'Sistem' }}</strong></span>
                                <span style="color: #cbd5e1;">•</span>
                                <span>👤 Lider: <strong style="color: #1e293b;">{{ $p->manager?->name ?? '-' }}</strong></span>
                            </div>

                            <!-- 2. SATIR: TAHMİNİ TAKVİM & GERİ SAYIM SAYACI -->
                            @if($startDate || $targetEndDate)
                                <div style="font-size: 11px; color: #334155; margin-top: 3px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <span>📅 <strong>Tahmini Takvim:</strong> {{ $startDate?->format('d.m.Y') ?? '-' }} ➔ {{ $targetEndDate?->format('d.m.Y') ?? '-' }}</span>
                                    @if($countdownText)
                                        <span style="background: {{ $countdownBg }}; color: {{ $countdownColor }}; border: 1px solid {{ $countdownBorder }}; font-size: 10px; font-weight: 750; padding: 1.5px 7px; border-radius: 10px;">
                                            ⏱️ {{ $countdownText }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <!-- 3. SATIR: AÇIKLAMALI YAŞAM DÖNGÜSÜ & REVİZYON TARİHLERİ -->
                            <div style="font-size: 10.5px; color: #64748b; margin-top: 3px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                <span>📝 <strong>Taslak Kayıt:</strong> {{ ($p->draft_created_at ?? $p->created_at)?->format('d.m.Y H:i') }}</span>
                                
                                @if($p->draft_updated_at && $p->draft_updated_at->ne($p->draft_created_at ?? $p->created_at))
                                    <span style="color: #d97706; font-weight: 600;" title="Son Taslak Revizyonu">
                                        ✏️ <strong>Son Revizyon:</strong> {{ $p->draft_updated_at->format('d.m.Y H:i') }}
                                    </span>
                                @endif

                                @if($p->submitted_for_approval_at)
                                    <span style="color: #2563eb; font-weight: 600;" title="Onaya Sunulma Tarihi">
                                        📤 <strong>Onaya Sunulma:</strong> {{ $p->submitted_for_approval_at->format('d.m.Y H:i') }}
                                    </span>
                                @endif

                                @if($p->approved_at)
                                    <span style="color: #16a34a; font-weight: 600;" title="Onaylanma / Devreye Alınma Tarihi">
                                        ✅ <strong>Onay / Başlama:</strong> {{ $p->approved_at->format('d.m.Y H:i') }}
                                    </span>
                                @endif
                            </div>

                            <!-- 4. SATIR: KAPSAM ARAÇLARI & EKİP KADROSU BUTONLARI -->
                            @php
                                $activeScopeToolsCount = $p->getActiveScopeToolsCount();
                            @endphp
                            <div style="display: flex; align-items: center; gap: 6px; margin-top: 6px; flex-wrap: wrap;">
                                @if($activeScopeToolsCount > 0)
                                    <button type="button" 
                                            class="btn btn-xs" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#scopeToolsModal_{{ $p->id }}" onclick="window.openCustomModal('scopeToolsModal_{{ $p->id }}')"
                                            style="background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; font-size: 10px; font-weight: 750; padding: 2px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; transition: all 0.15s; cursor: pointer;"
                                            onmouseover="this.style.background='#e0f2fe'; this.style.borderColor='#7dd3fc';"
                                            onmouseout="this.style.background='#f0f9ff'; this.style.borderColor='#bae6fd';"
                                            title="Kullanılan modüler analitik araçları görüntüle">
                                        <span>🛠️</span>
                                        <span>{{ $activeScopeToolsCount }} Kapsam Aracı</span>
                                        <span style="font-size: 9px; opacity: 0.8;">▾</span>
                                    </button>
                                @endif

                                <button type="button" 
                                        class="btn btn-xs" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#projectTeamModal_{{ $p->id }}" onclick="window.openCustomModal('projectTeamModal_{{ $p->id }}')"
                                        style="background: #f8fafc; color: #475569; border: 1px solid #cbd5e1; font-size: 10px; font-weight: 750; padding: 2px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; transition: all 0.15s; cursor: pointer;"
                                        onmouseover="this.style.background='#f1f5f9'; this.style.borderColor='#94a3b8'; this.style.color='#0f172a';"
                                        onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#cbd5e1'; this.style.color='#475569';"
                                        title="Proje ekibi ve paydaş listesini görüntüle">
                                    <span>👥</span>
                                    <span>Ekip ({{ $teamCount }} Kişi)</span>
                                    <span style="font-size: 9px; opacity: 0.8;">▾</span>
                                </button>
                            </div>
                        </td>

                        <!-- GİZLİLİK (Simge ve metin yan yana, tek satırda) -->
                        <td style="padding: 12px 14px; white-space: nowrap; width: 1%; text-align: center;">
                            <span style="display: inline-flex; align-items: center; justify-content: center; gap: 4px; white-space: nowrap; font-size: 10.5px; font-weight: 700; padding: 3px 8px; border-radius: 4px; line-height: 1; {{ $isPrivate ? 'background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;' : 'background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;' }}">
                                <span style="font-size: 11px;">{{ $isPrivate ? '🔒' : '🌐' }}</span>
                                <span>{{ $isPrivate ? 'Özel' : 'Genel' }}</span>
                            </span>
                        </td>

                        <!-- DURUM -->
                        <td style="padding: 12px 14px; white-space: nowrap; width: 1%;">
                            <x-status-badge :status="$p->status" />
                        </td>

                        <!-- ÖNCELİK -->
                        <td style="padding: 12px 14px; white-space: nowrap; width: 1%;">
                            <x-priority-badge :priority="$p->priority" />
                        </td>

                        <!-- İLERLEME -->
                        <td style="padding: 12px 14px;">
                            <div style="display: flex; justify-content: space-between; font-size: 10.5px; font-weight: 700; margin-bottom: 3px;">
                                <span>%{{ (int) round($p->progress_percentage) }}</span>
                            </div>
                            <div style="background: #e2e8f0; height: 5px; border-radius: 3px; overflow: hidden;">
                                <div style="background: {{ $p->progress_percentage == 100 ? '#16a34a' : '#0284c7' }}; width: {{ $p->progress_percentage }}%; height: 100%;"></div>
                            </div>
                        </td>

                        <!-- BÜTÇE -->
                        <td style="padding: 12px 14px; font-weight: 700; color: #1e293b; white-space: nowrap; width: 1%;">
                            ₺{{ number_format($p->planned_budget, 0, ',', '.') }}
                        </td>

                        <!-- YÖNETİCİ & EKİP BİLGİSİ -->
                        <td style="padding: 12px 14px; white-space: nowrap;">
                            <div style="font-weight: 650; color: #1e293b; font-size: 12px;">{{ $p->manager?->name ?? '-' }}</div>
                            <div style="font-size: 10.5px; color: #64748b; margin-top: 2px; display: inline-flex; align-items: center; gap: 3px;">
                                <span>👥</span>
                                <span style="font-weight: 600; color: #475569;">{{ $teamCount > 0 ? $teamCount . ' Kişilik Ekip' : 'Ekip Yok' }}</span>
                            </div>
                        </td>

                        <!-- SPONSOR -->
                        <td style="padding: 12px 14px; color: #334155; white-space: nowrap;">
                            {{ $p->sponsor?->name ?? '-' }}
                        </td>

                        <!-- İŞLEMLER -->
                        <td style="padding: 12px 16px; text-align: right; white-space: nowrap; width: 1%;">
                            <div style="display: inline-flex; gap: 6px; align-items: center;">
                                <a href="{{ route('projects.show', $p->id) }}" class="btn btn-xs btn-primary" style="font-size: 11px; padding: 3px 9px; border-radius: 5px;">
                                    İncele
                                </a>
                                @if($canManage)
                                    <a href="{{ route('projects.edit', $p->id) }}" class="btn btn-xs btn-outline-secondary" style="font-size: 11px; padding: 3px 8px; border-radius: 5px;" title="Projeyi Düzenle">
                                        ✏️
                                    </a>
                                @endif
                                @if($canDelete)
                                    <form action="{{ route('projects.destroy', $p->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-xs btn-outline-danger" style="font-size: 11px; padding: 3px 8px; border-radius: 5px;" title="Projeyi Sil" onclick="confirmProjectDelete(this, '{{ addslashes($p->title) }}', '{{ $p->code }}')">
                                            🗑️
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 40px; color: #64748b;">
                            Kriterlere uygun proje bulunamadı.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
