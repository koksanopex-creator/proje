<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $project->code }} - Taslak Proje İnceleme Portalı | Köksan Enterprise PPM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/koksan_pms.css') }}">
    <style>
        /* SCROLL VE CONTAINER GENEL DÜZELTMESİ (rules1.md Kural 3 kısıtını harici bağımsız sayfada override etme) */
        html, body {
            height: auto !important;
            min-height: 100vh !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            background: #f8fafc !important;
            color: #0f172a !important;
            font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .review-page-wrapper {
            max-width: 1320px;
            margin: 0 auto;
            padding: 24px 20px 80px;
        }

        /* ÜST PRESTİJLİ KÖKSAN HEADER */
        .portal-header-card {
            background: linear-gradient(135deg, #091325 0%, #0f172a 50%, #1e293b 100%);
            color: #ffffff;
            border-radius: 16px;
            padding: 24px 30px;
            margin-bottom: 24px;
            box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.08);
            position: relative;
            overflow: hidden;
        }
        .portal-header-card::after {
            content: '';
            position: absolute;
            right: -80px;
            top: -80px;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(2, 132, 199, 0.25) 0%, rgba(2, 132, 199, 0) 70%);
            pointer-events: none;
        }

        .portal-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 700;
        }

        /* 4'LÜ KPI ÖZET KUTULARI */
        .kpi-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        .kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
            display: flex;
            align-items: center;
            gap: 14px;
            transition: all 0.2s;
        }
        .kpi-card:hover {
            box-shadow: 0 6px 16px rgba(0,0,0,0.06);
            transform: translateY(-2px);
        }
        .kpi-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        /* 2 SÜTUNLU ANA DÜZEN */
        .content-layout-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 24px;
            align-items: start;
        }

        .main-column {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .sidebar-column {
            display: flex;
            flex-direction: column;
            gap: 20px;
            position: sticky;
            top: 60px;
        }

        .section-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            overflow: hidden;
        }
        .section-header {
            padding: 16px 22px;
            border-bottom: 1px solid #f1f5f9;
            background: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .section-title {
            margin: 0;
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
            letter-spacing: -0.2px;
        }
        .section-body {
            padding: 22px;
        }

        /* TABLOLAR */
        .table-custom {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .table-custom th {
            background: #f8fafc;
            color: #475569;
            font-weight: 700;
            padding: 10px 14px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11.5px;
            text-transform: uppercase;
        }
        .table-custom td {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .table-custom tr:last-child td {
            border-bottom: none;
        }

        @media (max-width: 1100px) {
            .kpi-summary-grid { grid-template-columns: repeat(2, 1fr); }
            .content-layout-grid { grid-template-columns: 1fr; }
            .sidebar-column { position: static; }
        }
        @media (max-width: 640px) {
            .kpi-summary-grid { grid-template-columns: 1fr; }
            .review-page-wrapper { padding: 14px 12px 60px; }
            .portal-header-card { padding: 18px 20px; }
        }
    </style>
</head>
<body>

@auth
    <!-- DAHİLİ KULLANICI / YÖNETİCİ ÖNİZLEME BİLGİ BANTLARI -->
    <div style="background: #091325; color: #f8fafc; padding: 10px 24px; font-size: 13px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #1e293b; position: sticky; top: 0; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.25);">
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <span style="background: #0284c7; color: #fff; padding: 3px 9px; border-radius: 5px; font-size: 11px; font-weight: 800; letter-spacing: 0.5px;">ÖNİZLEME MODU</span>
            <span style="color: #cbd5e1;">Bu sayfayı şu anda <strong>{{ $share->email }}</strong> dış paydaşının göreceği arayüz haliyle inceliyorsunuz.</span>
        </div>
        <a href="{{ route('projects.show', $project->id) }}" style="color: #38bdf8; text-decoration: none; font-weight: 700; font-size: 12.5px; display: inline-flex; align-items: center; gap: 6px; padding: 5px 14px; background: rgba(56, 189, 248, 0.12); border: 1.5px solid rgba(56, 189, 248, 0.35); border-radius: 6px; transition: all 0.2s;" onmouseover="this.style.background='rgba(56,189,248,0.2)'" onmouseout="this.style.background='rgba(56,189,248,0.12)'">
            <span>← Proje Detayına Dön</span>
        </a>
    </div>
@endauth

<div class="review-page-wrapper">

    <!-- 1. ÜST HEADER KART (KURUMSAL BAŞLIK & İNCELEME KİMLİĞİ) -->
    <div class="portal-header-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 300px;">
                <!-- KÖKSAN LOGO VE SİSTEM BAŞLIĞI -->
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                    <div style="width: 38px; height: 38px; border-radius: 9px; background: linear-gradient(135deg, #0284c7, #2563eb); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 19px; box-shadow: 0 4px 10px rgba(2, 132, 199, 0.4);">
                        K
                    </div>
                    <div>
                        <div style="font-size: 14px; font-weight: 800; color: #ffffff; letter-spacing: 0.5px;">KÖKSAN HOLDİNG</div>
                        <div style="font-size: 11px; color: #94a3b8; font-weight: 600;">Enterprise PPM • Güvenli Taslak İnceleme Portalı</div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 10px;">
                    <span class="portal-badge" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3);">
                        🛡️ Güvenli İnceleme Oturumu
                    </span>
                    <span class="portal-badge" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3);">
                        📝 Fizibilite / Taslak Aşaması
                    </span>
                    @if($project->bolum)
                        <span class="portal-badge" style="background: rgba(255, 255, 255, 0.08); color: #cbd5e1; border: 1px solid rgba(255, 255, 255, 0.15);">
                            🏢 {{ $project->bolum->name }}
                        </span>
                    @endif
                </div>

                <h1 style="font-size: 22px; font-weight: 800; margin: 0 0 8px 0; color: #ffffff; letter-spacing: -0.4px; line-height: 1.3;">
                    <span style="color: #38bdf8;">{{ $project->code }}</span> • {{ $project->title }}
                </h1>
                <p style="margin: 0; font-size: 12.5px; color: #94a3b8;">
                    Bu portal üzerinden projenin bütçesini, takvimini, iş paketlerini inceleyebilir ve resmi görüşlerinizi iletebilirsiniz.
                </p>
            </div>

            <!-- GEÇİŞ ANAHTARI & OTURUM BİLGİSİ -->
            <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: 12px; padding: 14px 18px; min-width: 240px; text-align: right;">
                <div style="font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 700; margin-bottom: 3px;">
                    Doğrulanmış Geçiş Anahtarı
                </div>
                <div style="font-size: 18px; font-weight: 900; color: #38bdf8; font-family: monospace; letter-spacing: 1.5px;">
                    {{ $share->passkey }}
                </div>
                <div style="font-size: 11.5px; color: #cbd5e1; margin-top: 6px; display: flex; align-items: center; justify-content: flex-end; gap: 5px;">
                    <span>👤</span> <span>{{ $share->email }}</span>
                </div>
                @if($share->expires_at)
                    <div style="font-size: 10.5px; color: #94a3b8; margin-top: 2px;">
                        ⏳ Son Geçerlilik: {{ $share->expires_at->format('d.m.Y H:i') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div style="background: #ecfdf5; border: 1.5px solid #86efac; color: #166534; border-radius: 12px; padding: 14px 20px; margin-bottom: 24px; font-size: 13.5px; font-weight: 600; display: flex; align-items: center; gap: 10px; box-shadow: 0 2px 8px rgba(22, 101, 52, 0.08);">
            <span style="font-size: 20px;">✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- 2. 4'LÜ KPI ÖZET KARTLARI -->
    <div class="kpi-summary-grid">
        <!-- Bütçe -->
        <div class="kpi-card">
            <div class="kpi-icon-box" style="background: #eff6ff; color: #0284c7;">
                💰
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 2px;">Planlanan Bütçe</div>
                <div style="font-size: 17px; font-weight: 900; color: #0f172a;">
                    {{ $project->planned_budget > 0 ? ($project->currency_symbol ?? '₺') . number_format($project->planned_budget, 2, ',', '.') . ' ' . $project->currency : 'Belirlenmedi' }}
                </div>
            </div>
        </div>

        <!-- Takvim & Termin -->
        <div class="kpi-card">
            <div class="kpi-icon-box" style="background: #fef3c7; color: #d97706;">
                📅
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 2px;">Hedeflenen Takvim</div>
                <div style="font-size: 13.5px; font-weight: 800; color: #0f172a;">
                    {{ $project->start_date ? $project->start_date->format('d.m.Y') : 'Başlangıç Yok' }} ➔ 
                    {{ $project->target_end_date ? $project->target_end_date->format('d.m.Y') : 'Bitiş Yok' }}
                </div>
            </div>
        </div>

        <!-- Faz & Görev Sayısı -->
        <div class="kpi-card">
            <div class="kpi-icon-box" style="background: #f0fdf4; color: #16a34a;">
                🚩
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 2px;">Planlanan Kapsam</div>
                <div style="font-size: 16px; font-weight: 800; color: #0f172a;">
                    {{ $project->phases->count() }} Faz • {{ $project->phases->sum(fn($p) => $p->tasks->count()) }} Görev
                </div>
            </div>
        </div>

        <!-- Sorumlu & Liderlik -->
        <div class="kpi-card">
            <div class="kpi-icon-box" style="background: #f5f3ff; color: #7c3aed;">
                👥
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 2px;">Proje Yöneticisi</div>
                <div style="font-size: 13.5px; font-weight: 800; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 170px;">
                    {{ $project->manager?->name ?? 'Atanmadı' }}
                </div>
            </div>
        </div>
    </div>

    <!-- 3. ANA İÇERİK DÜZENİ (SOL: DETAYLAR, SAĞ: ETKİLEŞİM & NOT FORMU) -->
    <div class="content-layout-grid">

        <!-- SOL ANA SÜTUN (KAPSAM, TAKVİM, BÜTÇE, DOSYALAR, RİSKLER) -->
        <div class="main-column">

            <!-- 1. KAPSAM & HEDEFLER -->
            <div class="section-card">
                <div class="section-header">
                    <h3 class="section-title">
                        <span>📑</span> 1. Proje Amacı, Gerekçesi ve Kapsam Tanımı
                    </h3>
                    <span class="portal-badge" style="background: #f1f5f9; color: #475569;">
                        Hazırlayan: {{ $project->creator?->name ?? 'Proje Ekibi' }}
                    </span>
                </div>
                <div class="section-body">
                    @if($project->description)
                        <div style="font-size: 13.5px; line-height: 1.7; color: #334155; background: #f8fafc; padding: 18px 22px; border-radius: 10px; border-left: 4px solid #0284c7; white-space: pre-line;">
                            {!! nl2br(e($project->description)) !!}
                        </div>
                    @else
                        <div style="color: #94a3b8; font-size: 13px; font-style: italic; padding: 10px 0;">
                            Bu proje için henüz detaylı kapsam açıklaması girilmemiştir.
                        </div>
                    @endif
                </div>
            </div>

            <!-- KAPSAM, ANALİZ & PROJE MODÜLER ARAÇLARI -->
            @include('projects.partials.scope_tools_view', ['project' => $project])

            <!-- 2. FAZLAR & KİLOMETRE TAŞLARI (GÖREV PLANI) -->
            <div class="section-card">
                <div class="section-header">
                    <h3 class="section-title">
                        <span>🗓️</span> 2. Fazlar, Kilometre Taşları & İş Paketleri
                    </h3>
                    <span class="portal-badge" style="background: #e0f2fe; color: #0369a1;">
                        {{ $project->phases->count() }} Ana Faz
                    </span>
                </div>
                <div class="section-body" style="padding: 0;">
                    @if($project->phases->isNotEmpty())
                        <div style="display: flex; flex-direction: column;">
                            @foreach($project->phases as $idx => $phase)
                                <div style="padding: 18px 22px; border-bottom: 1px solid #f1f5f9; {{ $loop->last ? 'border-bottom: none;' : '' }}">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 8px;">
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span style="width: 24px; height: 24px; border-radius: 50%; background: #0284c7; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800;">
                                                {{ $idx + 1 }}
                                            </span>
                                            <strong style="font-size: 14px; color: #0f172a;">{{ $phase->name }}</strong>
                                            @if($phase->requires_ehs_approval)
                                                <span style="font-size: 10.5px; font-weight: 700; background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; padding: 1px 7px; border-radius: 4px;" title="İSG İzin Belgesi Gerektirir">
                                                    🛡️ İSG Kapı Kilidi
                                                </span>
                                            @endif
                                        </div>
                                        <div style="font-size: 12px; font-weight: 600; color: #64748b;">
                                            📅 {{ $phase->start_date ? $phase->start_date->format('d.m.Y') : 'Planlanıyor' }} ➔ {{ $phase->end_date ? $phase->end_date->format('d.m.Y') : 'Planlanıyor' }}
                                        </div>
                                    </div>

                                    @if($phase->tasks->isNotEmpty())
                                        <div style="margin-top: 10px; margin-left: 14px; padding-left: 14px; border-left: 2px solid #e2e8f0; display: flex; flex-direction: column; gap: 8px;">
                                            @foreach($phase->tasks as $task)
                                                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12.5px; padding: 2px 0;">
                                                    <div style="display: flex; align-items: center; gap: 8px;">
                                                        <span style="color: #94a3b8;">•</span>
                                                        <span style="color: #1e293b; font-weight: 600;">{{ $task->title }}</span>
                                                        @if($task->is_milestone)
                                                            <span style="font-size: 10px; font-weight: 800; background: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 1px 6px; border-radius: 4px;">
                                                                🚩 KİLOMETRE TAŞI
                                                            </span>
                                                        @endif
                                                        @if($task->tag)
                                                            <span style="font-size: 10.5px; color: #0284c7; background: #f0f9ff; padding: 1px 6px; border-radius: 4px;">
                                                                {{ $task->tag }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div style="font-size: 11.5px; color: #64748b; font-weight: 600;">
                                                        {{ $task->due_date ? $task->due_date->format('d.m.Y') : '-' }}
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div style="padding: 24px; text-align: center; color: #94a3b8; font-size: 13px;">
                            Bu taslak için henüz aşama ve faz planı eklenmemiştir.
                        </div>
                    @endif
                </div>
            </div>

            <!-- 3. KALEM KALEM YATIRIM MALİYETİ KIRILIMI -->
            <div class="section-card">
                <div class="section-header">
                    <h3 class="section-title">
                        <span>📊</span> 3. Yatırım Bütçesi & Maliyet Kırılımı
                    </h3>
                    <span class="portal-badge" style="background: #f0fdf4; color: #166534;">
                        Toplam: {{ $project->planned_budget > 0 ? ($project->currency_symbol ?? '₺') . number_format($project->planned_budget, 2, ',', '.') . ' ' . $project->currency : 'Belirlenmedi' }}
                    </span>
                </div>
                <div class="section-body" style="padding: 0;">
                    @if($project->budgetItems->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th style="width: 40px; text-align: center;">#</th>
                                        <th>Maliyet / Yatırım Kalemi</th>
                                        <th style="width: 180px;">Kategori</th>
                                        <th style="width: 180px; text-align: right;">Planlanan Tutar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($project->budgetItems as $bIdx => $bItem)
                                        <tr>
                                            <td style="text-align: center; color: #94a3b8; font-weight: 700;">{{ $bIdx + 1 }}</td>
                                            <td>
                                                <strong style="color: #0f172a;">{{ $bItem->title }}</strong>
                                            </td>
                                            <td>
                                                <div style="display: flex; flex-direction: column; gap: 3px; align-items: flex-start;">
                                                    <span style="background: #eff6ff; color: #1e40af; font-size: 11.5px; padding: 3px 8px; border-radius: 4px; font-weight: 600;">
                                                        {{ $bItem->category ?? 'Genel Yatırım' }}
                                                    </span>
                                                    @if(!empty($bItem->sub_category))
                                                        <span style="background: #f1f5f9; color: #475569; font-size: 10.5px; padding: 2px 6px; border-radius: 4px; font-weight: 500;">
                                                            ↳ {{ $bItem->sub_category }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td style="text-align: right; font-weight: 800; color: #0f172a;">
                                                {{ ($project->currency_symbol ?? '₺') . number_format($bItem->planned_amount, 2, ',', '.') }} {{ $bItem->currency ?? $project->currency }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr style="background: #f8fafc; font-weight: 800;">
                                        <td colspan="3" style="text-align: right; font-size: 12.5px; color: #475569;">
                                            TOPLAM PLANLANAN BÜTÇE:
                                        </td>
                                        <td style="text-align: right; font-size: 14.5px; color: #0284c7;">
                                            {{ ($project->currency_symbol ?? '₺') . number_format($project->budgetItems->sum('planned_amount'), 2, ',', '.') }} {{ $project->currency }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div style="padding: 24px; text-align: center; color: #64748b; font-size: 13px;">
                            Bütçe tek kalem olarak belirlenmiştir: 
                            <strong style="color: #0f172a; font-size: 15px;">
                                {{ $project->planned_budget > 0 ? ($project->currency_symbol ?? '₺') . number_format($project->planned_budget, 2, ',', '.') . ' ' . $project->currency : 'Belirlenmedi' }}
                            </strong>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 4. KAPSAM & ŞARTNAME DOKÜMANLARI (DOSYALAR) -->
            <div class="section-card">
                <div class="section-header">
                    <h3 class="section-title">
                        <span>📎</span> 4. Proje Dokümanları & Teknik Şartnameler
                    </h3>
                    <span class="portal-badge" style="background: #f1f5f9; color: #475569;">
                        {{ $project->files->count() }} Dosya Ekli
                    </span>
                </div>
                <div class="section-body">
                    @if($project->files->isNotEmpty())
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px;">
                            @foreach($project->files as $file)
                                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 14px; display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                        <div style="width: 34px; height: 34px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;">
                                            📄
                                        </div>
                                        <div style="min-width: 0;">
                                            <div style="font-weight: 700; font-size: 12.5px; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $file->original_name }}">
                                                {{ $file->original_name }}
                                            </div>
                                            <div style="font-size: 11px; color: #64748b;">
                                                {{ $file->file_size ? number_format($file->file_size / 1024, 1) . ' KB' : 'Doküman' }} • {{ $file->created_at->format('d.m.Y') }}
                                            </div>
                                        </div>
                                    </div>
                                    <a href="{{ route('projects.external_download_file', [$project->id, $file->id, 'token' => $share->token, 'passkey' => $share->passkey]) }}" style="background: #ffffff; border: 1px solid #cbd5e1; color: #0284c7; padding: 5px 10px; border-radius: 6px; font-size: 11.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; flex-shrink: 0; transition: all 0.15s;" onmouseover="this.style.background='#eff6ff'; this.style.borderColor='#93c5fd';" onmouseout="this.style.background='#ffffff'; this.style.borderColor='#cbd5e1';">
                                        <span>⬇️</span> İndir
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div style="text-align: center; color: #94a3b8; font-size: 13px; padding: 10px 0;">
                            Bu taslak için henüz harici ek doküman yüklenmemiştir.
                        </div>
                    @endif
                </div>
            </div>

            <!-- 5. ÖNGÖRÜLEN RİSKLER & İSG TEDBİRLERİ (VARSA) -->
            @if($project->risks->isNotEmpty())
                <div class="section-card">
                    <div class="section-header">
                        <h3 class="section-title">
                            <span>⚠️</span> 5. Tespit Edilen Riskler ve Önlem Planı
                        </h3>
                        <span class="portal-badge" style="background: #fef2f2; color: #991b1b;">
                            {{ $project->risks->count() }} Risk Değerlendirmesi
                        </span>
                    </div>
                    <div class="section-body" style="padding: 0;">
                        <div class="table-responsive">
                            <table class="table-custom">
                                <thead>
                                    <tr>
                                        <th>Risk Tanımı & Açıklama</th>
                                        <th style="width: 130px; text-align: center;">Risk Seviyesi</th>
                                        <th style="width: 250px;">Önlem / Mitigasyon Stratejisi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($project->risks as $r)
                                        <tr>
                                            <td>
                                                <strong style="color: #0f172a;">{{ $r->title }}</strong>
                                                @if($r->description)
                                                    <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">{{ $r->description }}</div>
                                                @endif
                                            </td>
                                            <td style="text-align: center;">
                                                <span style="font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 4px; background: {{ $r->score >= 15 ? '#fee2e2; color: #b91c1c;' : ($r->score >= 8 ? '#fef3c7; color: #92400e;' : '#ecfdf5; color: #166534;') }}">
                                                    Skor: {{ $r->score ?? '-' }}
                                                </span>
                                            </td>
                                            <td style="font-size: 12px; color: #334155;">
                                                {{ $r->mitigation_plan ?: 'Önlem planı bekleniyor.' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        <!-- SAĞ SÜTUN (YÖNETİM BİLGİSİ & İNTERAKTİF GERİ BİLDİRİM FORMU) -->
        <div class="sidebar-column">

            <!-- 1. PAYLAŞIM & İNCELEMECİ BİLGİ KARTI -->
            <div class="section-card" style="border: 1.5px solid #bae6fd; background: #ffffff;">
                <div class="section-header" style="background: #f0f9ff; border-bottom: 1px solid #bae6fd;">
                    <h4 style="margin: 0; font-size: 13.5px; font-weight: 800; color: #0284c7; display: flex; align-items: center; gap: 6px;">
                        <span>👤</span> Paydaş İnceleme Bilgisi
                    </h4>
                </div>
                <div class="section-body" style="font-size: 12.5px; color: #334155; display: flex; flex-direction: column; gap: 10px;">
                    <div>
                        <div style="font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase;">İncelemeci E-Posta:</div>
                        <div style="font-weight: 700; color: #0f172a;">{{ $share->email }}</div>
                    </div>

                    @if($share->note)
                        <div style="background: #f8fafc; border-left: 3px solid #0284c7; padding: 8px 12px; border-radius: 0 6px 6px 0;">
                            <div style="font-size: 10.5px; color: #64748b; font-weight: 700;">PROJE YÖNETİCİSİ NOTU:</div>
                            <div style="font-size: 12px; color: #1e293b; margin-top: 2px;">{{ $share->note }}</div>
                        </div>
                    @endif

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; padding-top: 6px; border-top: 1px solid #f1f5f9; font-size: 11.5px;">
                        <div>
                            <span style="color: #64748b;">Erişim Sayısı:</span>
                            <strong style="color: #0f172a; display: block;">{{ $share->access_count }} kez</strong>
                        </div>
                        <div>
                            <span style="color: #64748b;">Son Ziyaret:</span>
                            <strong style="color: #0f172a; display: block;">{{ $share->last_accessed_at ? $share->last_accessed_at->format('d.m.Y H:i') : 'İlk Giriş' }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. İNTERAKTİF GÖRÜŞ VE ÖNERİ BİLDİRME FORMU -->
            <div class="section-card" style="border: 2px solid #0284c7; box-shadow: 0 4px 20px rgba(2, 132, 199, 0.12);">
                <div class="section-header" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff;">
                    <h3 style="margin: 0; font-size: 14.5px; font-weight: 800; color: #ffffff; display: flex; align-items: center; gap: 8px;">
                        <span>💬</span> Görüş / Revizyon Talebi Bildir
                    </h3>
                </div>
                <div class="section-body">
                    <p style="font-size: 12.5px; color: #475569; margin: 0 0 14px 0; line-height: 1.5;">
                        Projenin bütçesi, terminleri veya teknik kapsamı hakkındaki önerilerinizi doğrudan proje yöneticisine iletebilirsiniz.
                    </p>

                    <form id="externalFeedbackForm" action="{{ route('projects.store_external_draft_suggestion', $project->id) }}" method="POST" onsubmit="return handleExternalFeedbackSubmit(event, this);">
                        @csrf
                        <input type="hidden" name="token" value="{{ $share->token }}">
                        <input type="hidden" name="passkey" value="{{ $share->passkey }}">

                        <div class="form-group mb-3">
                            <label class="form-label" style="font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 5px; display: block;">
                                Değerlendirme Konusu <span class="text-danger">*</span>
                            </label>
                            <select name="field_label" class="form-control" style="font-size: 12.5px; height: 38px;" required>
                                <option value="Genel Taslak Değerlendirmesi">Genel Taslak Değerlendirmesi</option>
                                <option value="Bütçe ve Maliyet Planı">Bütçe ve Maliyet Planı</option>
                                <option value="Takvim ve Termin Hedefleri">Takvim ve Termin Hedefleri</option>
                                <option value="Faz ve İş Paketi Kapsamı">Faz ve İş Paketi Kapsamı</option>
                                <option value="Teknik Şartname & Ekipman Notu">Teknik Şartname & Ekipman Notu</option>
                                <option value="Risk ve İSG Tedbirleri">Risk ve İSG Tedbirleri</option>
                            </select>
                            <input type="hidden" name="field_key" value="external_feedback">
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label" style="font-weight: 700; font-size: 12px; color: #334155; margin-bottom: 5px; display: block;">
                                Görüş & Değerlendirme Notunuz <span class="text-danger">*</span>
                            </label>
                            <textarea name="suggestion_text" class="form-control" rows="5" style="font-size: 12.5px; line-height: 1.5;" placeholder="Proje ile ilgili fizibilite değerlendirmelerinizi, revizyon taleplerinizi veya onay notlarınızı detaylıca buraya yazınız..." required></textarea>
                        </div>

                        <button type="submit" id="externalFeedbackBtn" class="btn btn-primary" style="width: 100%; padding: 11px; font-weight: 800; font-size: 13.5px; border-radius: 8px; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3); transition: all 0.2s;">
                            <span id="externalFeedbackIcon">🚀</span> <span id="externalFeedbackText">Görüşü Proje Ekibine İlet</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- 3. DAHA ÖNCE İLETİLEN GÖRÜŞLER (TARİHÇE) -->
            @if($project->draftSuggestions->isNotEmpty())
                <div class="section-card">
                    <div class="section-header">
                        <h4 style="margin: 0; font-size: 13.5px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                            <span>📝</span> İletilen Görüşler ({{ $project->draftSuggestions->count() }})
                        </h4>
                    </div>
                    <div class="section-body" style="padding: 14px 16px;">
                        <div style="display: flex; flex-direction: column; gap: 10px; max-height: 360px; overflow-y: auto;">
                            @foreach($project->draftSuggestions as $sug)
                                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                        <span style="font-size: 11.5px; font-weight: 800; color: #0284c7;">
                                            📌 {{ $sug->field_label }}
                                        </span>
                                        <span style="font-size: 10.5px; color: #94a3b8;">
                                            {{ $sug->created_at->format('d.m.Y H:i') }}
                                        </span>
                                    </div>
                                    <div style="font-size: 12px; color: #334155; line-height: 1.5;">
                                        {{ $sug->suggestion_text }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

        </div>

    </div>

    <!-- ALT KURUMSAL FOOTER -->
    <div style="text-align: center; font-size: 12px; color: #94a3b8; margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
        © {{ date('Y') }} Köksan Pet ve Plastik Ambalaj San. Tic. A.Ş. • Kurumsal Proje Yönetim Sistemi (PPM)
    </div>

</div>

    <script>
    let isExternalFeedbackSubmitting = false;
    function handleExternalFeedbackSubmit(e, form) {
        if (isExternalFeedbackSubmitting) {
            e.preventDefault();
            return false;
        }
        isExternalFeedbackSubmitting = true;
        const btn = document.getElementById('externalFeedbackBtn');
        const icon = document.getElementById('externalFeedbackIcon');
        const txt = document.getElementById('externalFeedbackText');
        if (btn) {
            btn.style.pointerEvents = 'none';
            btn.style.opacity = '0.75';
            if (icon) icon.innerHTML = '⏳';
            if (txt) txt.textContent = 'Görüş İletiliyor... Lütfen Bekleyiniz';
        }
        return true;
    }
    </script>
</body>
</html>
