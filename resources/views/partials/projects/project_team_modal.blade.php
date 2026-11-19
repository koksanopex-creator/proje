@php
    $stakeholders = $p->activeStakeholders()->with('user.bolum')->get();
    $teamTotal = $p->stakeholders_count ?? $stakeholders->count();
    if ($teamTotal === 0 && ($p->manager_id || $p->sponsor_id)) {
        $teamTotal = ($p->manager_id ? 1 : 0) + ($p->sponsor_id && $p->sponsor_id !== $p->manager_id ? 1 : 0);
    }

    $raciMap = [
        'R' => ['label' => 'Sorumlu (R)', 'badge' => '#0284c7', 'bg' => '#e0f2fe', 'desc' => 'İşi doğrudan yürüten'],
        'A' => ['label' => 'Hesap Veren (A)', 'badge' => '#10b981', 'bg' => '#dcfce7', 'desc' => 'Nihai onaylayıcı'],
        'C' => ['label' => 'Danışılan (C)', 'badge' => '#d97706', 'bg' => '#fef3c7', 'desc' => 'Uzmanlığına başvurulan'],
        'I' => ['label' => 'Bilgilendirilen (I)', 'badge' => '#475569', 'bg' => '#f1f5f9', 'desc' => 'Gelişmeler aktarılan'],
    ];

    $roleLabels = [
        'manager' => 'Proje Yöneticisi / Lider',
        'sponsor' => 'Proje Sponsoru',
        'member' => 'Ekip Üyesi',
        'engineer' => 'Teknik / Mühendislik Sorumlusu',
        'procurement' => 'Satınalma & Tedarik',
        'quality' => 'Kalite / Kalite Güvence',
        'observer' => 'Gözlemci / Paydaş'
    ];
@endphp

<!-- PROJE EKİP VE PAYDAŞ KADROSU MODALI (PROJE ID: {{ $p->id }}) -->
<div id="projectTeamModal_{{ $p->id }}" class="custom-modal-backdrop" onclick="if(event.target === this) window.closeCustomModal('projectTeamModal_{{ $p->id }}')" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 100000; backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 20px;">
    <div class="custom-modal-dialog" style="max-width: 780px; width: 100%; max-height: 88vh; display: flex; flex-direction: column; background: #ffffff; border-radius: 14px; box-shadow: 0 25px 50px -12px rgba(15,23,42,0.35); overflow: hidden; border: none;">
            
            <!-- MODAL BAŞLIĞI -->
            <div class="modal-header" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff; padding: 16px 22px; border-bottom: none;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center; font-size: 20px; backdrop-filter: blur(4px);">
                        👥
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="badge" style="background: rgba(255,255,255,0.25); color: #ffffff; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 6px;">
                                {{ $p->code }}
                            </span>
                            <h5 class="modal-title mb-0" id="projectTeamModalLabel_{{ $p->id }}" style="font-size: 15px; font-weight: 800; color: #ffffff;">
                                Proje Ekibi & Paydaş Kadrosu
                            </h5>
                        </div>
                        <p style="margin: 3px 0 0; font-size: 12px; color: rgba(255,255,255,0.85); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 550px;">
                            {{ $p->title }}
                        </p>
                    </div>
                </div>
                <button type="button" class="custom-modal-close" onclick="window.closeCustomModal('scopeToolsModal_{{ $p->id }}')" style="background: rgba(255,255,255,0.18); border: none; color: #ffffff; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 700; cursor: pointer; transition: all 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.35)'" onmouseout="this.style.background='rgba(255,255,255,0.18)'">✕</button>
            </div>

            <!-- MODAL GÖVDESİ -->
            <div class="custom-modal-body" style="padding: 20px 22px; background: #f8fafc; display: flex; flex-direction: column; gap: 18px; overflow-y: auto; max-height: calc(88vh - 130px);">
                
                <!-- 1. TEMEL LİDERLİK & YÖNETİM ROLLERİ (BAŞLATAN, LİDER, SPONSOR) -->
                <div>
                    <span style="font-size: 11.5px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 8px;">
                        📌 Temel Sorumlular & Liderlik
                    </span>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px;">
                        
                        <!-- Projeyi Başlatan -->
                        <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-top: 3.5px solid #6366f1; border-radius: 9px; padding: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <span style="font-size: 11px; font-weight: 700; color: #4f46e5;">✍️ Projeyi Başlatan</span>
                                <span class="badge" style="background: #e0e7ff; color: #4338ca; font-size: 10px;">Giriş Yapan</span>
                            </div>
                            <strong style="font-size: 13px; color: #0f172a; display: block;">
                                {{ $p->creator?->name ?? 'Sistem / Belirtilmedi' }}
                            </strong>
                            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                {{ $p->creator?->unvan ?? 'Yetkili' }} • {{ $p->creator?->bolum?->ad ?? 'Genel' }}
                            </div>
                            @if($p->creator?->email)
                                <div style="font-size: 10.5px; color: #94a3b8; margin-top: 3px;">
                                    ✉️ {{ $p->creator->email }}
                                </div>
                            @endif
                        </div>

                        <!-- Proje Yöneticisi (Lider) -->
                        <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-top: 3.5px solid #0284c7; border-radius: 9px; padding: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <span style="font-size: 11px; font-weight: 700; color: #0369a1;">👤 Proje Yöneticisi</span>
                                <span class="badge" style="background: #e0f2fe; color: #0284c7; font-size: 10px;">Yürütücü Lider</span>
                            </div>
                            <strong style="font-size: 13px; color: #0f172a; display: block;">
                                {{ $p->manager?->name ?? 'Atanmadı' }}
                            </strong>
                            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                {{ $p->manager?->unvan ?? 'Yönetici' }} • {{ $p->manager?->bolum?->ad ?? 'Genel' }}
                            </div>
                            @if($p->manager?->email)
                                <div style="font-size: 10.5px; color: #94a3b8; margin-top: 3px;">
                                    ✉️ {{ $p->manager->email }}
                                </div>
                            @endif
                        </div>

                        <!-- Proje Sponsoru -->
                        <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-top: 3.5px solid #f59e0b; border-radius: 9px; padding: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <span style="font-size: 11px; font-weight: 700; color: #b45309;">⭐ Proje Sponsoru</span>
                                <span class="badge" style="background: #fef3c7; color: #b45309; font-size: 10px;">Hamî / Sponsor</span>
                            </div>
                            <strong style="font-size: 13px; color: #0f172a; display: block;">
                                {{ $p->sponsor?->name ?? 'Atanmadı' }}
                            </strong>
                            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                {{ $p->sponsor?->unvan ?? 'Yönetim' }} • {{ $p->sponsor?->bolum?->ad ?? 'Genel' }}
                            </div>
                            @if($p->sponsor?->email)
                                <div style="font-size: 10.5px; color: #94a3b8; margin-top: 3px;">
                                    ✉️ {{ $p->sponsor->email }}
                                </div>
                            @endif
                        </div>

                    </div>
                </div>

                <!-- 2. PROJE EKİP MATRİSİ & PAYDAŞLAR (RACI TABLOSU) -->
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 11.5px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">
                            👥 Proje Ekip Üyeleri & Paydaşlar
                        </span>
                        <span class="badge" style="background: #e0f2fe; color: #0284c7; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 6px;">
                            {{ $stakeholders->count() }} Tanımlı Paydaş
                        </span>
                    </div>

                    @if($stakeholders->count() > 0)
                        <div class="table-responsive" style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 9px; overflow: hidden;">
                            <table class="table table-sm table-hover align-middle mb-0" style="font-size: 12.5px;">
                                <thead style="background: #f8fafc; color: #475569; font-size: 11.5px; font-weight: 700; border-bottom: 1.5px solid #e2e8f0;">
                                    <tr>
                                        <th style="padding: 10px 14px;">Personel / Paydaş</th>
                                        <th style="padding: 10px 14px;">Bölüm / Departman</th>
                                        <th style="padding: 10px 14px;">Proje Rolü</th>
                                        <th style="padding: 10px 14px; text-align: center;">RACI</th>
                                        <th style="padding: 10px 14px;">Notlar / Görev</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stakeholders as $sh)
                                        @php
                                            $u = $sh->user;
                                            $raci = $sh->raci_role ?? 'R';
                                            $rConf = $raciMap[$raci] ?? $raciMap['R'];
                                            $roleText = $roleLabels[$sh->role_type] ?? ucfirst($sh->role_type ?? 'Ekip Üyesi');
                                        @endphp
                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                            <td style="padding: 10px 14px;">
                                                <strong style="color: #0f172a; font-size: 13px; display: block;">{{ $u?->name ?? 'Bilinmeyen Kullanıcı' }}</strong>
                                                <span style="font-size: 11px; color: #64748b;">{{ $u?->unvan ?? 'Personel' }}</span>
                                                @if($u?->email)
                                                    <span style="font-size: 10.5px; color: #94a3b8; display: block;">{{ $u->email }}</span>
                                                @endif
                                            </td>
                                            <td style="padding: 10px 14px; color: #334155; font-weight: 600;">
                                                {{ $u?->bolum?->ad ?? 'Genel' }}
                                            </td>
                                            <td style="padding: 10px 14px;">
                                                <span style="font-weight: 600; color: #0284c7; background: #f0f9ff; border: 1px solid #bae6fd; font-size: 11px; padding: 2px 7px; border-radius: 5px;">
                                                    {{ $roleText }}
                                                </span>
                                            </td>
                                            <td style="padding: 10px 14px; text-align: center;">
                                                <span class="badge" style="background: {{ $rConf['bg'] }}; color: {{ $rConf['badge'] }}; border: 1px solid {{ $rConf['badge'] }}40; font-size: 11px; padding: 3px 8px;" title="{{ $rConf['desc'] }}">
                                                    {{ $rConf['label'] }}
                                                </span>
                                            </td>
                                            <td style="padding: 10px 14px; color: #64748b; font-size: 11.5px;">
                                                {{ $sh->notes ?: '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div style="background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 9px; padding: 22px; text-align: center; color: #64748b;">
                            <span style="font-size: 26px; display: block; margin-bottom: 4px;">👥</span>
                            <strong style="font-size: 13px; color: #334155; display: block;">Henüz Ekip Matrisi Tanımlanmadı</strong>
                            <p style="margin: 4px 0 0 0; font-size: 12px; color: #94a3b8;">
                                Bu proje için Proje Yöneticisi ve Sponsor doğrudan yürütücü kadrodur. Proje detay sayfasındaki "Ekip & Paydaşlar" sekmesinden yeni üyeler eklenebilir.
                            </p>
                        </div>
                    @endif
                </div>

            </div>

            <!-- MODAL ALTI -->
            <div class="custom-modal-footer" style="background: #ffffff; border-top: 1px solid #e2e8f0; padding: 12px 22px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 11.5px; color: #64748b; font-weight: 600;">
                    Toplam Kadro: {{ $teamTotal }} Kişi
                </span>
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.closeCustomModal('projectTeamModal_{{ $p->id }}')" style="font-size: 12px; font-weight: 700; padding: 5px 14px; border-radius: 7px;">
                        Kapat
                    </button>
                    <a href="{{ route('projects.show', $p->id) }}#tab-team" class="btn btn-sm btn-primary" style="font-size: 12px; font-weight: 750; padding: 5px 16px; border-radius: 7px; display: inline-flex; align-items: center; gap: 6px; background: #0284c7; border-color: #0284c7;">
                        <span>👥 Ekip Matrisine Git</span>
                        <span>→</span>
                    </a>
                </div>
            </div>

            </div>
</div>
