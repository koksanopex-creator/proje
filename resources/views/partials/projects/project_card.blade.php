@php
    $canManage = auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->hasPermission('proje.manage') || auth()->user()->hasPermission('proje.edit') || $p->manager_id === auth()->id() || $p->sponsor_id === auth()->id() || $p->created_by === auth()->id();
    $canDelete = auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isDeveloper() || auth()->user()->hasPermission('proje.delete');
    $isPrivate = ($p->visibility ?? 'private') === 'private';

    // Duruma göre kenarlık ve tema renklendirme haritası
    $statusColorMap = [
        'draft' => [
            'border' => '#94a3b8',
            'border_full' => '#cbd5e1',
            'bg_subtle' => 'rgba(241, 245, 249, 0.5)',
            'accent' => '#64748b',
        ],
        'waiting_approval' => [
            'border' => '#0284c7',
            'border_full' => '#7dd3fc',
            'bg_subtle' => 'rgba(224, 242, 254, 0.25)',
            'accent' => '#0284c7',
        ],
        'pending' => [
            'border' => '#0284c7',
            'border_full' => '#7dd3fc',
            'bg_subtle' => 'rgba(224, 242, 254, 0.25)',
            'accent' => '#0284c7',
        ],
        'review' => [
            'border' => '#0284c7',
            'border_full' => '#7dd3fc',
            'bg_subtle' => 'rgba(224, 242, 254, 0.25)',
            'accent' => '#0284c7',
        ],
        'in_progress' => [
            'border' => '#f59e0b',
            'border_full' => '#fcd34d',
            'bg_subtle' => 'rgba(254, 243, 199, 0.25)',
            'accent' => '#d97706',
        ],
        'completed' => [
            'border' => '#10b981',
            'border_full' => '#86efac',
            'bg_subtle' => 'rgba(220, 252, 231, 0.25)',
            'accent' => '#16a34a',
        ],
        'done' => [
            'border' => '#10b981',
            'border_full' => '#86efac',
            'bg_subtle' => 'rgba(220, 252, 231, 0.25)',
            'accent' => '#16a34a',
        ],
        'cancelled' => [
            'border' => '#64748b',
            'border_full' => '#cbd5e1',
            'bg_subtle' => 'rgba(248, 250, 252, 0.5)',
            'accent' => '#475569',
        ],
        'on_hold' => [
            'border' => '#64748b',
            'border_full' => '#cbd5e1',
            'bg_subtle' => 'rgba(248, 250, 252, 0.5)',
            'accent' => '#475569',
        ],
        'blocked' => [
            'border' => '#ef4444',
            'border_full' => '#fca5a5',
            'bg_subtle' => 'rgba(254, 226, 226, 0.25)',
            'accent' => '#dc2626',
        ],
        'critical' => [
            'border' => '#ef4444',
            'border_full' => '#fca5a5',
            'bg_subtle' => 'rgba(254, 226, 226, 0.25)',
            'accent' => '#dc2626',
        ],
        'waiting_ehs' => [
            'border' => '#ef4444',
            'border_full' => '#fca5a5',
            'bg_subtle' => 'rgba(254, 226, 226, 0.25)',
            'accent' => '#dc2626',
        ],
    ];
    $stConf = $statusColorMap[$p->status] ?? $statusColorMap['draft'];

    // Ekip Kişi Sayısı
    $teamCount = $p->stakeholders_count ?? $p->stakeholders()->count();
    if ($teamCount === 0 && ($p->manager_id || $p->sponsor_id)) {
        $teamCount = ($p->manager_id ? 1 : 0) + ($p->sponsor_id && $p->sponsor_id !== $p->manager_id ? 1 : 0);
    }

    // Tarihler ve Başlangıç Geri Sayımı
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
            $countdownText = "Başlangıca {$diff} gün var";
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
                $countdownText = "Proje Tamamlandı";
                $countdownBg = '#dcfce7';
                $countdownColor = '#15803d';
                $countdownBorder = '#bbf7d0';
            } elseif ($p->status === 'in_progress') {
                $countdownText = "{$pastDays} gündür süreçte";
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

<div class="card project-card-item" 
     style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between; border-radius: 12px; border: 1.5px solid {{ $stConf['border_full'] }}; border-left: 5px solid {{ $stConf['border'] }}; box-shadow: 0 2px 6px rgba(0,0,0,0.03); transition: all 0.2s;" 
     onmouseover="this.style.boxShadow='0 8px 18px rgba(0,0,0,0.08)'; this.style.borderColor='{{ $stConf['border'] }}'" 
     onmouseout="this.style.boxShadow='0 2px 6px rgba(0,0,0,0.03)'; this.style.borderColor='{{ $stConf['border_full'] }}'; this.style.borderLeftColor='{{ $stConf['border'] }}'">
    
    <div class="card-body" style="padding: 16px 18px;">
        <!-- ÜST ŞERİT: KOD, GİZLİLİK, ÖNCELİK, DURUM (TEK SATIR HİZALAMA) -->
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 12px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: nowrap;">
                <span style="font-size: 11.5px; font-weight: 800; color: #0284c7; background: #e0f2fe; padding: 3px 8px; border-radius: 5px; white-space: nowrap; letter-spacing: -0.2px;">
                    {{ $p->code }}
                </span>
                <!-- GİZLİLİK ROZETİ (Simge ve yazı asla kırılmaz) -->
                <span style="display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; font-size: 10px; font-weight: 700; padding: 3px 7px; border-radius: 4px; line-height: 1; {{ $isPrivate ? 'background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;' : 'background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;' }}">
                    <span style="font-size: 11px;">{{ $isPrivate ? '🔒' : '🌐' }}</span>
                    <span>{{ $isPrivate ? 'Özel' : 'Genel' }}</span>
                </span>
            </div>
            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: nowrap;">
                <x-priority-badge :priority="$p->priority" />
                <x-status-badge :status="$p->status" />
            </div>
        </div>

        <!-- BAŞLIK & AÇIKLAMA (SABİT YÜKSEKLİK İLE MÜKEMMEL KART HİZALAMASI) -->
        <a href="{{ route('projects.show', $p->id) }}" style="text-decoration: none; color: #0f172a;">
            <h3 style="font-size: 14.5px; font-weight: 800; margin-bottom: 6px; line-height: 1.35; color: #0f172a; height: 38px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; transition: color 0.15s;" title="{{ $p->title }}" onmouseover="this.style.color='#0284c7'" onmouseout="this.style.color='#0f172a'">
                {{ $p->title }}
            </h3>
        </a>

        <p style="font-size: 11.5px; color: #64748b; line-height: 1.45; margin-bottom: 12px; height: 34px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis;">
            {{ strip_tags($p->description ?? 'Proje açıklaması belirtilmemiş.') }}
        </p>

        <!-- PROJE ETİKETLERİ -->
        @if(!empty($p->tags_array))
            <div style="display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 8px; min-height: 20px;">
                @foreach(array_slice($p->tags_array, 0, 3) as $t)
                    <span style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; font-size: 10px; font-weight: 750; padding: 1px 6px; border-radius: 4px;">#{{ $t }}</span>
                @endforeach
                @if(count($p->tags_array) > 3)
                    <span style="font-size: 9.5px; color: #64748b; font-weight: 600;">+{{ count($p->tags_array) - 3 }}</span>
                @endif
            </div>
        @else
            <div style="min-height: 20px; margin-bottom: 8px;"></div>
        @endif

        <!-- İLERLEME ÇUBUĞU (SABİT 22PX) -->
        <div style="margin-bottom: 12px; height: 22px;">
            <div style="display: flex; justify-content: space-between; font-size: 10.5px; font-weight: 700; margin-bottom: 4px; color: #334155;">
                <span>İlerleme Seviyesi</span>
                <span style="color: {{ $p->progress_percentage == 100 ? '#16a34a' : '#0284c7' }};">%{{ (int) round($p->progress_percentage) }}</span>
            </div>
            <div style="background: #e2e8f0; height: 6px; border-radius: 3px; overflow: hidden;">
                <div style="background: {{ $p->progress_percentage == 100 ? '#16a34a' : '#0284c7' }}; width: {{ $p->progress_percentage }}%; height: 100%; transition: width 0.3s;"></div>
            </div>
        </div>

        <!-- BÜTÇE & DARBOĞAZ DETAYI (SABİT 52PX MİNİMUM YÜKSEKLİK İLE HİZALI) -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 7px 12px; font-size: 11px; margin-bottom: 11px; min-height: 52px; display: flex; flex-direction: column; justify-content: center;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="color: #64748b; font-weight: 600;">Planlanan Bütçe:</span>
                <span style="font-weight: 800; color: #0f172a; font-size: 12.5px;">₺{{ number_format($p->planned_budget, 0, ',', '.') }}</span>
            </div>
            @if($p->current_waiting_role_id)
                <div style="margin-top: 4px; padding-top: 4px; border-top: 1px dashed #cbd5e1; color: #dc2626; font-weight: 700; font-size: 10px; display: flex; align-items: center; gap: 5px;">
                    <span>⏱️</span>
                    <span>{{ $p->waiting_duration }} {{ $p->waitingRole?->name }} bekliyor</span>
                </div>
            @else
                <div style="margin-top: 4px; padding-top: 4px; border-top: 1px dashed transparent; font-size: 10px; color: #94a3b8; display: flex; align-items: center; gap: 5px;">
                    <span>⚡</span>
                    <span>Süreç normal akışında</span>
                </div>
            @endif
        </div>

        <!-- KAPSAM VE MODÜLER ANALİZ ARAÇLARI YUVASI (SABİT 32PX YÜKSEKLİK İLE HİZALI) -->
        @php
            $activeScopeToolsCount = $p->getActiveScopeToolsCount();
        @endphp
        <div style="height: 32px; margin-bottom: 11px;">
            @if($activeScopeToolsCount > 0)
                <button type="button" 
                        class="btn btn-sm w-100" 
                        data-bs-toggle="modal" 
                        data-bs-target="#scopeToolsModal_{{ $p->id }}" onclick="window.openCustomModal('scopeToolsModal_{{ $p->id }}')"
                        style="background: #f0f9ff; color: #0284c7; border: 1.5px solid #bae6fd; font-size: 11px; font-weight: 750; padding: 4px 12px; border-radius: 8px; height: 32px; display: flex; align-items: center; justify-content: space-between; transition: all 0.2s; box-shadow: 0 1px 3px rgba(2,132,199,0.06); cursor: pointer;"
                        onmouseover="this.style.background='#e0f2fe'; this.style.borderColor='#7dd3fc'; this.style.transform='translateY(-1px)';"
                        onmouseout="this.style.background='#f0f9ff'; this.style.borderColor='#bae6fd'; this.style.transform='none';">
                    <span style="display: flex; align-items: center; gap: 6px;">
                        <span>🛠️</span>
                        <span>Kapsam & Analiz Araçları</span>
                    </span>
                    <span style="background: #0284c7; color: #ffffff; font-size: 10px; font-weight: 800; padding: 2px 7px; border-radius: 10px;">
                        {{ $activeScopeToolsCount }} Araç ▾
                    </span>
                </button>
            @else
                <div style="height: 32px; border: 1px dashed #e2e8f0; border-radius: 8px; background: #fafafa; display: flex; align-items: center; justify-content: center; font-size: 10.5px; color: #94a3b8;">
                    <span>🛠️ Kapsam Aracı Tanımlanmadı</span>
                </div>
            @endif
        </div>

        <!-- SORUMLULUKLAR, BAŞLATAN, YÖNETİCİ, SPONSOR, BÖLÜM & EKİP BİLGİSİ (TAM HİZALI 5 SATIR) -->
        <div style="display: flex; flex-direction: column; gap: 5px; font-size: 11px; color: #475569; border-top: 1px solid #f1f5f9; padding-top: 10px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="color: #64748b;">✍️ Başlatan:</span>
                <strong style="color: #1e293b;" title="Projeyi İlk Oluşturan / Başlatan">{{ $p->creator?->name ?? 'Sistem' }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="color: #64748b;">👤 Proje Yöneticisi:</span>
                <strong style="color: #1e293b;" title="Atanan Lider">{{ $p->manager?->name ?? '-' }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="color: #64748b;">⭐ Sponsor:</span>
                <strong style="color: #1e293b;">{{ $p->sponsor?->name ?? '-' }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="color: #64748b;">🏢 Departman / Bölüm:</span>
                <strong style="color: #334155;">{{ $p->bolum?->ad ?? 'Genel' }}</strong>
            </div>
            <!-- PROJE EKİBİ (TIKLANINCA EKİP MODALINI AÇAN BUTON) -->
            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 4px; border-top: 1px dashed #f1f5f9;">
                <span style="color: #64748b;">👥 Proje Ekibi:</span>
                <button type="button" 
                        class="btn btn-xs" 
                        data-bs-toggle="modal" 
                        data-bs-target="#projectTeamModal_{{ $p->id }}" onclick="window.openCustomModal('projectTeamModal_{{ $p->id }}')"
                        style="font-weight: 750; color: #0284c7; background: #e0f2fe; border: 1px solid #bae6fd; padding: 2px 9px; border-radius: 12px; font-size: 10.5px; display: inline-flex; align-items: center; gap: 4px; cursor: pointer; transition: all 0.15s;"
                        onmouseover="this.style.background='#bae6fd'; this.style.borderColor='#7dd3fc';"
                        onmouseout="this.style.background='#e0f2fe'; this.style.borderColor='#bae6fd';"
                        title="Proje ekibi ve paydaş kadrosunu görüntüle">
                    <span>{{ $teamCount > 0 ? $teamCount . ' Kişi' : 'Ekibi Gör' }}</span>
                    <span style="font-size: 9px;">▾</span>
                </button>
            </div>
        </div>

        <!-- TARİH DETAYLARI: AÇIKLAMALI VE SABİT 4 SATIRLI BLOK -->
        <div style="margin-top: 10px; padding: 9px 11px; background: #fafafa; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 11px; display: flex; flex-direction: column; gap: 4px; min-height: 104px; justify-content: center;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="color: #64748b; font-weight: 600;">🚀 Başlangıç Tarihi:</span>
                <strong style="color: #1e293b;">{{ $startDate?->format('d.m.Y') ?? 'Belirtilmedi' }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="color: #64748b; font-weight: 600;">🏁 Hedef Bitiş Tarihi:</span>
                <strong style="color: #1e293b;">{{ $targetEndDate?->format('d.m.Y') ?? 'Belirtilmedi' }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 3px; border-top: 1px dashed #e2e8f0;">
                <span style="color: #64748b; font-weight: 600;">⏱️ Süreç Sayacı:</span>
                <span style="background: {{ $countdownBg }}; color: {{ $countdownColor }}; border: 1px solid {{ $countdownBorder }}; font-weight: 750; font-size: 10px; padding: 1.5px 7px; border-radius: 10px;">
                    {{ $countdownText ?? 'Tarih bekleniyor' }}
                </span>
            </div>
            <!-- YAŞAM DÖNGÜSÜ TARİHİ BİLGİSİ (AÇIKLAMALI) -->
            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 3px; border-top: 1px dashed #e2e8f0; font-size: 10.5px;">
                @if($p->status === 'draft')
                    <span style="color: #64748b;">📝 Taslak Kaydı:</span>
                    <span style="color: #334155; font-weight: 600;">{{ ($p->draft_created_at ?? $p->created_at)?->format('d.m.Y H:i') }}</span>
                @elseif($p->status === 'waiting_approval')
                    <span style="color: #2563eb;">📤 Onaya Sunulma:</span>
                    <span style="color: #1d4ed8; font-weight: 600;">{{ ($p->submitted_for_approval_at ?? $p->waiting_since ?? $p->updated_at)?->format('d.m.Y H:i') }}</span>
                @else
                    <span style="color: #16a34a;">✅ Onay / Başlama:</span>
                    <span style="color: #15803d; font-weight: 600;">{{ ($p->approved_at ?? $p->updated_at)?->format('d.m.Y H:i') }}</span>
                @endif
            </div>
        </div>
    </div>

    <!-- KART ALTI AKSİYON BUTONLARI -->
    <div class="card-footer" style="padding: 10px 16px; background: #fafafa; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; border-radius: 0 0 12px 12px;">
        <div style="display: flex; gap: 6px;">
            @if($canManage)
                <a href="{{ route('projects.edit', $p->id) }}" class="btn btn-xs btn-outline-secondary" style="font-size: 11px; padding: 4px 8px; border-radius: 5px; color: #475569;" title="Projeyi Düzenle">
                    ✏️ Düzenle
                </a>
            @endif
            @if($canDelete)
                <form action="{{ route('projects.destroy', $p->id) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-xs btn-outline-danger" style="font-size: 11px; padding: 4px 8px; border-radius: 5px;" title="Projeyi Sil (Admin/Developer)" onclick="confirmProjectDelete(this, '{{ addslashes($p->title) }}', '{{ $p->code }}')">
                        🗑️ Sil
                    </button>
                </form>
            @endif
        </div>
        <a href="{{ route('projects.show', $p->id) }}" class="btn btn-xs btn-primary" style="font-size: 11.5px; padding: 5px 12px; font-weight: 700; border-radius: 6px;">
            İncele →
        </a>
    </div>
</div>
