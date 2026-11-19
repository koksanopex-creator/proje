@extends('layouts.app')

@section('title', 'Proje Toplantıları')
@section('page_title', 'Toplantı & Koordinasyon Yönetimi')
@section('page_subtitle', 'Gündemler, katılımcı yoklaması, tutanaklar ve SAP ekip modeliyle karardan göreve dönüşüm')

@section('content')
    <!-- ÜST İSTATİSTİK & FİLTRE ŞERİDİ -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px;">
        <div class="card" style="padding: 16px; display: flex; align-items: center; gap: 14px; margin-bottom: 0;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                📅
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Toplam Toplantı</div>
                <div style="font-size: 20px; font-weight: 800; color: var(--navy);">{{ $stats['total'] ?? $meetings->count() }}</div>
            </div>
        </div>

        <div class="card" style="padding: 16px; display: flex; align-items: center; gap: 14px; margin-bottom: 0;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                ⏱️
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Planlanan / Bekleyen</div>
                <div style="font-size: 20px; font-weight: 800; color: #b45309;">{{ $stats['scheduled'] ?? 0 }}</div>
            </div>
        </div>

        <div class="card" style="padding: 16px; display: flex; align-items: center; gap: 14px; margin-bottom: 0;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #dcfce7; color: #15803d; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                ✅
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Tamamlanan / Tutanaklı</div>
                <div style="font-size: 20px; font-weight: 800; color: #15803d;">{{ $stats['completed'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    <!-- YENİ TOPLANTI PLANLAMA KARTI -->
    @if(!auth()->user()->isYonetim() && (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->hasPermission('meeting.create') || auth()->user()->hasPermission('meeting.manage')))
    <div class="card" style="margin-bottom: 24px; border: 1px solid #e2e8f0; border-radius: 10px;">
        <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center;">
            <span class="card-title" style="font-size: 15px; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 8px;">
                <span>➕</span> Yeni Proje Toplantısı Planla
            </span>
            <span style="font-size: 12px; color: var(--text-muted);">Tüm proje paydaşlarına bildirim ve takvim daveti iletilir</span>
        </div>
        <div class="card-body" style="padding: 20px;">
            <form action="{{ route('meetings.store') }}" method="POST">
                @csrf
                <div style="display: grid; grid-template-columns: 1.2fr 2fr 1.2fr 1.2fr; gap: 16px; margin-bottom: 16px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 600; font-size: 12px;">📁 İlgili Proje *</label>
                        <select name="project_id" class="form-control" required style="font-size: 13px;">
                            <option value="">-- Proje Seçiniz --</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ (isset($selectedProjectId) && $selectedProjectId == $p->id) ? 'selected' : '' }}>
                                    {{ $p->code }} - {{ Str::limit($p->title, 40) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 600; font-size: 12px;">📌 Toplantı Başlığı *</label>
                        <input type="text" name="title" class="form-control" required placeholder="Örn: 2. OSB Saha Çelik Montaj & İSG Koordinasyonu" style="font-size: 13px;">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 600; font-size: 12px;">📅 Tarih & Saat *</label>
                        <input type="datetime-local" name="meeting_date" class="form-control" required style="font-size: 13px;">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 600; font-size: 12px;">📍 Toplantı Yeri / Ortamı</label>
                        <input type="text" name="location" class="form-control" placeholder="Örn: Şantiye Ofisi / Konferans Salonu" style="font-size: 13px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1.2fr; gap: 16px; margin-bottom: 16px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 600; font-size: 12px;">📝 Toplantı Gündemi & Görüşülecek Maddeler</label>
                        <textarea name="agenda" rows="3" class="form-control" placeholder="1. Temel beton kırım test sonuçlarının değerlendirilmesi&#10;2. Vinç kurulum sahası zemin sertleştirme kontrolü&#10;3. İSG Yüksekte Çalışma İzinlerinin imza süreci..." style="font-size: 13px; line-height: 1.5;"></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 600; font-size: 12px;">👥 Katılımcı Daveti (Zil & Mail Bildirimi İletilir)</label>
                        <select name="participants[]" class="form-control" multiple style="height: 74px; font-size: 12px;">
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->title ?? 'Personel' }})</option>
                            @endforeach
                        </select>
                        <small style="color: var(--text-muted); font-size: 11px;">Birden fazla kişi seçmek için Ctrl tuşunu kullanabilirsiniz.</small>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 14px;">
                    <span style="font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 6px;">
                        <span>👤</span> <strong>Planlayan / Düzenleyen:</strong> {{ auth()->user()->name }} ({{ auth()->user()->title ?? 'Proje Yetkilisi' }})
                    </span>
                    <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px; padding: 9px 20px; font-weight: 600;">
                        <span>📅</span> Toplantıyı Kaydet ve Bildirim Gönder
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- MEVCUT TOPLANTILAR LİSTESİ -->
    <div class="card" style="border: 1px solid #e2e8f0; border-radius: 10px;">
        <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="card-title" style="font-size: 16px; font-weight: 700; color: var(--navy);">
                    Planlanan & Tamamlanan Toplantılar
                </span>
                <span class="badge" style="background: #f1f5f9; color: #475569; font-weight: 700;">{{ $meetings->count() }} Kayıt</span>
            </div>

            <!-- PROJE FİLTRESİ -->
            <form action="{{ route('meetings.index') }}" method="GET" style="display: flex; gap: 10px; align-items: center;">
                <select name="project_id" class="form-control" style="font-size: 12px; height: 34px; min-width: 200px;" onchange="this.form.submit()">
                    <option value="">-- Tüm Projelerin Toplantıları --</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ (isset($selectedProjectId) && $selectedProjectId == $p->id) ? 'selected' : '' }}>
                            {{ $p->code }} - {{ Str::limit($p->title, 30) }}
                        </option>
                    @endforeach
                </select>
                @if(request()->filled('project_id'))
                    <a href="{{ route('meetings.index') }}" class="btn btn-sm btn-outline" style="height: 34px; display: flex; align-items: center; font-size: 12px; text-decoration: none;">
                        Temizle
                    </a>
                @endif
            </form>
        </div>

        <div class="card-body" style="padding: 20px;">
            @forelse($meetings as $m)
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); transition: border-color 0.2s;" onmouseover="this.style.borderColor='#93c5fd'" onmouseout="this.style.borderColor='#e2e8f0'">
                    
                    <!-- KART ÜST BAŞLIK, PROJE LİNKİ VE STATÜ -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; gap: 12px; flex-wrap: wrap;">
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
                            <span style="font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px; display: inline-flex; align-items: center; gap: 4px; {!! $m->status_badge_style !!}">
                                @if($m->status === 'completed')
                                    ✅ Tamamlandı
                                @elseif($m->status === 'in_progress')
                                    🔄 Devam Ediyor
                                @elseif($m->status === 'cancelled')
                                    ❌ İptal Edildi
                                @else
                                    ⏱️ Planlandı / Bekliyor
                                @endif
                            </span>
                        </div>

                        <!-- TARİH VE LOKASYON BİLGİSİ -->
                        <div style="display: flex; align-items: center; gap: 12px; font-size: 12px; color: #475569; background: #f8fafc; padding: 6px 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                            <span style="font-weight: 600; color: #1e293b; display: inline-flex; align-items: center; gap: 4px;">
                                📅 {{ $m->meeting_date->format('d.m.Y H:i') }}
                            </span>
                            <span style="color: #cbd5e1;">•</span>
                            <span style="display: inline-flex; align-items: center; gap: 4px;">
                                📍 {{ $m->location ?? '2. OSB Şantiye Ofisi / Konferans Salonu' }}
                            </span>
                        </div>
                    </div>

                    <!-- META BİLGİ ŞERİDİ: PLANLAYAN KİŞİ & KATILIMCILAR -->
                    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 14px; padding-bottom: 12px; border-bottom: 1px dashed #e2e8f0; font-size: 12px; flex-wrap: wrap;">
                        <!-- TOPLANTIYI PLANLAYAN KİŞİ -->
                        <div style="display: inline-flex; align-items: center; gap: 6px; background: #f1f5f9; padding: 4px 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                            <span style="color: #64748b; font-weight: 600;">👤 Düzenleyen / Moderatör:</span>
                            <strong style="color: #0f172a;">{{ $m->organizer ? $m->organizer->getMaskedNameFor(auth()->user()) : 'Proje Yöneticisi' }}</strong>
                            @if($m->organizer?->title)
                                <span style="color: #64748b; font-size: 11px;">({{ $m->organizer->title }})</span>
                            @endif
                        </div>

                        <!-- KATILIMCI SAYISI -->
                        <div style="display: inline-flex; align-items: center; gap: 6px; color: #64748b;">
                            <span>👥</span>
                            <span>Davetli Katılımcı: <strong>{{ $m->participants->count() > 0 ? $m->participants->count() . ' Kişi' : 'Tüm Proje Ekibi' }}</strong></span>
                        </div>

                        <!-- ALINAN KARAR SAYISI -->
                        @if($m->actions->count() > 0)
                            <div style="display: inline-flex; align-items: center; gap: 6px; background: #ecfdf5; color: #047857; padding: 4px 10px; border-radius: 6px; font-weight: 600;">
                                <span>⚡</span>
                                <span>{{ $m->actions->count() }} Karar Göreve Dönüştürüldü</span>
                            </div>
                        @endif

                        <!-- DETAY SAYFASINA GİT BUTONU -->
                        <div style="margin-left: auto;">
                            <a href="{{ route('meetings.show', $m->id) }}" 
                               style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: 700; color: #1e293b; text-decoration: none; transition: all 0.2s;"
                               onmouseover="this.style.background='#e2e8f0'; this.style.color='#0f172a';"
                               onmouseout="this.style.background='#f8fafc'; this.style.color='#1e293b';">
                                👁️ Toplantı Tutanak & Detay Sayfası →
                            </a>
                        </div>
                    </div>

                    <!-- GÜNDEM MADDELERİ -->
                    @if(!empty($m->formatted_agenda_items))
                        <div style="margin-bottom: 14px; background: #fafafa; padding: 12px 14px; border-radius: 6px; border-left: 3px solid #3b82f6;">
                            <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 6px;">Toplantı Gündem Maddeleri:</div>
                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                @foreach($m->formatted_agenda_items as $item)
                                    <div style="display: flex; align-items: baseline; gap: 8px; font-size: 12.5px; color: #1e293b;">
                                        <span style="font-weight: 800; color: #2563eb; min-width: 18px;">{{ $item['number'] }}.</span>
                                        <div style="flex: 1;">
                                            <span style="font-weight: 600;">{{ $item['title'] }}</span>
                                            @if($item['date'] || $item['responsible'])
                                                <span style="display: inline-flex; gap: 6px; margin-left: 6px; font-size: 11px;">
                                                    @if($item['date'])
                                                        <span style="background: #fef3c7; color: #92400e; padding: 1px 6px; border-radius: 3px;">📅 {{ $item['date'] }}</span>
                                                    @endif
                                                    @if($item['responsible'])
                                                        <span style="background: #e0f2fe; color: #0369a1; padding: 1px 6px; border-radius: 3px;">👤 {{ $item['responsible'] }}</span>
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @elseif($m->agenda)
                        <div style="font-size: 13px; color: #334155; margin-bottom: 14px; line-height: 1.6; background: #fafafa; padding: 12px 14px; border-radius: 6px; border-left: 3px solid #3b82f6;">
                            <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Toplantı Gündem Maddeleri:</div>
                            <div style="white-space: pre-line;">{{ $m->agenda }}</div>
                        </div>
                    @endif

                    <!-- TOPLANTI TUTANAĞI & NOTLARI (ZENGİN METİN İÇERİĞİ) -->
                    @if($m->minutes)
                        <div style="font-size: 13px; color: #334155; margin-bottom: 16px; line-height: 1.6; background: #f0fdf4; padding: 12px 14px; border-radius: 6px; border-left: 3px solid #16a34a;">
                            <div style="font-size: 11px; font-weight: 700; color: #166534; text-transform: uppercase; margin-bottom: 6px; display: flex; align-items: center; gap: 5px;">
                                <span>📝</span> Toplantı Görüşme Tutanağı & Saha Notları:
                            </div>
                            <div style="font-size: 12.5px; color: #1e293b; line-height: 1.6; max-height: 140px; overflow-y: auto;">
                                {!! $m->minutes !!}
                            </div>
                        </div>
                    @endif

                    <!-- KARARDAN GÖREVE DÖNÜŞTÜRME ALANI (SAP EKİP MANTIĞI VE ETİKETLİ TERMİN TARİHİ) -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <div style="font-size: 12px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                                <span style="color: #f59e0b;">⚡</span>
                                <span>Toplantı Kararını Doğrudan Proje Görevine Dönüştür (Kanban / Gantt'a Ekle):</span>
                            </div>
                            <span style="font-size: 11px; color: #64748b;">
                                💡 SAP Ekip Mantığı: Görev tüm proje ekibine veya belirli kişiye atanabilir
                            </span>
                        </div>

                        <form action="{{ route('meetings.convert_task', $m->id) }}" method="POST" style="display: grid; grid-template-columns: 2fr 1.3fr 1.1fr auto; gap: 10px; align-items: end;">
                            @csrf
                            <!-- KARAR METNİ -->
                            <div style="display: flex; flex-direction: column;">
                                <label style="font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 4px;">Karar / Aksiyon Maddesi *</label>
                                <input type="text" name="decision_text" class="form-control" placeholder="Örn: Zemin sertleştirme test raporu hazırlanacak..." required style="font-size: 13px;">
                            </div>

                            <!-- SORUMLU: SAP EKİP MANTIĞI VEYA TEKİL KİŞİ -->
                            <div style="display: flex; flex-direction: column;">
                                <label style="font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 4px;">Sorumlu (SAP Ekip / Kişi) *</label>
                                <select name="assigned_target" class="form-control" required style="font-size: 13px;">
                                    <option value="team" selected style="font-weight: 700; color: #1e3a8a; background: #eff6ff;">
                                        👥 Tüm Proje Ekibi (SAP Modeli / Ortak Görev)
                                    </option>
                                    <optgroup label="Tekil Kişi Sorumlu Ata">
                                        @foreach($users as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->title ?? 'Ekip Üyesi' }})</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>

                            <!-- TERMİN / BİTİŞ TARİHİ (KULLANICI NE OLDUĞUNU AÇIKÇA GÖRECEK) -->
                            <div style="display: flex; flex-direction: column;">
                                <label style="font-size: 11px; font-weight: 700; color: #b45309; margin-bottom: 4px;" title="Görevin tamamlanması gereken son tarih">
                                    📅 Termin / Bitiş Tarihi *
                                </label>
                                <input type="date" name="due_date" class="form-control" required style="font-size: 13px;" value="{{ now()->addDays(7)->format('Y-m-d') }}">
                            </div>

                            <!-- GÖREVE DÖNÜŞTÜR BUTONU -->
                            <div>
                                <button type="submit" class="btn btn-primary" style="white-space: nowrap; height: 38px; display: inline-flex; align-items: center; gap: 6px; font-weight: 600; font-size: 13px; padding: 0 16px;">
                                    <span>➕</span> Göreve Dönüştür
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- ÖNCEDEN ALINMIŞ KARARLAR LİSTESİ -->
                    @if($m->actions->count() > 0)
                        <div style="margin-top: 14px; border-top: 1px solid #f1f5f9; padding-top: 12px;">
                            <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">
                                Bu Toplantıda Alınan ve Göreve Aktarılan Kararlar:
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                @foreach($m->actions as $act)
                                    <div style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; padding: 6px 12px; border-radius: 6px; font-size: 12px; border: 1px solid #f1f5f9;">
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span style="color: #10b981; font-weight: 700;">✓</span>
                                            <span style="color: #1e293b; font-weight: 600;">{{ $act->decision_text }}</span>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <span style="color: #64748b; background: #ffffff; padding: 2px 8px; border-radius: 4px; border: 1px solid #e2e8f0; font-size: 11px;">
                                                👤 {{ $act->assignee?->name ?? '👥 Tüm Proje Ekibi' }}
                                            </span>
                                            <span style="color: #b45309; font-weight: 600; font-size: 11px;">
                                                📅 Termin: {{ $act->due_date ? \Carbon\Carbon::parse($act->due_date)->format('d.m.Y') : '-' }}
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
                    <div style="font-size: 15px; font-weight: 600; color: var(--navy); margin-bottom: 4px;">Kayıtlı toplantı bulunamadı</div>
                    <div style="font-size: 13px;">Yukarıdaki formdan yeni bir proje toplantısı planlayabilirsiniz.</div>
                </div>
            @endforelse
        </div>
    </div>
@endsection