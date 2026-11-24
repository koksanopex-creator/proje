<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dış Paydaş Güvenli Belge Portalı') | KÖKSAN</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/koksan_pms.css') }}">
    <style>
        /* rules1.md Kural 3 koksan_pms.css'deki body { overflow: hidden; height: 100vh; } kuralını portal için ezerek sayfanın aşağı kaymasını sağlar */
        html {
            height: auto !important;
            min-height: 100% !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
        }
        body {
            height: auto !important;
            min-height: 100vh !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
            background: #f1f5f9 !important;
            color: #0f172a !important;
            font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .portal-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 16px 20px 80px;
            min-height: 100vh;
            box-sizing: border-box;
        }

        .portal-header {
            background: linear-gradient(135deg, #091325 0%, #0f172a 60%, #1e293b 100%);
            color: #ffffff;
            border-radius: 12px;
            padding: 16px 24px;
            margin-bottom: 16px;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.25);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }

        .portal-badge {
            background: rgba(2, 132, 199, 0.15);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.3);
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .portal-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02), 0 8px 16px rgba(0,0,0,0.04);
            margin-bottom: 24px;
            overflow: hidden;
        }

        .portal-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
            background: #fafafa;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .portal-card-body {
            padding: 20px;
        }

        .file-dropzone {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 32px 20px;
            text-align: center;
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.2s;
        }
        .file-dropzone:hover, .file-dropzone.dragover {
            border-color: #0284c7;
            background: #f0f9ff;
        }

        .portal-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 6px;
        }
        .portal-table th {
            padding: 10px 14px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        .portal-table td {
            padding: 12px 14px;
            font-size: 13px;
            background: #ffffff;
            border-top: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
        }
        .portal-table tr:hover td {
            background: #f8fafc;
        }

        .file-voided {
            text-decoration: line-through;
            color: #94a3b8;
        }

        .btn-portal-primary {
            background: #0284c7;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.15s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-portal-primary:hover {
            background: #0369a1;
        }

        .btn-portal-secondary {
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.15s;
        }
        .btn-portal-secondary:hover {
            background: #f8fafc;
        }

        .btn-portal-danger {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-portal-danger:hover {
            background: #fee2e2;
        }
    </style>
    @yield('styles')
</head>
<body>

    <div class="portal-container">
        <!-- ========================================================================= -->
        <!-- KÖKSAN KURUMSAL LOGO & DIŞ PAYDAŞ PLATFORM BAŞLIĞI                          -->
        <!-- ========================================================================= -->
        @php
            $siteLogo = \App\Models\SystemSetting::get('site_logo');
        @endphp
        <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 12px 22px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
            <div style="display: flex; align-items: center; gap: 18px;">
                @if($siteLogo && file_exists(public_path($siteLogo)))
                    <div style="background: #ffffff; padding: 4px 8px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <img src="{{ asset($siteLogo) }}" alt="Köksan Logo" style="height: 40px; width: auto; max-width: 170px; object-fit: contain; display: block;">
                    </div>
                @else
                    <div style="font-size: 22px; font-weight: 900; color: #0284c7; letter-spacing: -0.5px; display: flex; align-items: center; gap: 6px;">
                        <span>🏢</span> KÖKSAN
                    </div>
                @endif
                <div style="height: 36px; width: 1.5px; background: #e2e8f0;"></div>
                <div>
                    <h1 style="margin: 0; font-size: 17px; font-weight: 900; color: #0f172a; line-height: 1.25; letter-spacing: -0.2px;">
                        Dış Paydaş Dosya & Belge Yükleme Platformu
                    </h1>
                    <div style="font-size: 11.5px; color: #64748b; font-weight: 600; margin-top: 2px;">
                        Köksan Holding &bull; Güvenli Teknik Doküman, Çizim ve Rapor Transfer Sistemi
                    </div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; padding: 5px 12px; border-radius: 9999px; font-size: 11.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                    <span style="width: 8px; height: 8px; background: #22c55e; border-radius: 50%; display: inline-block;"></span>
                    256-Bit SSL Güvenli Bağlantı
                </span>
            </div>
        </div>

        @if(session('success'))
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-weight: 600; font-size: 13px;">
                <span>✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('info'))
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-weight: 600; font-size: 13px;">
                <span>ℹ️</span>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-size: 13px;">
                <div style="font-weight: 700; margin-bottom: 6px;">⚠️ Lütfen aşağıdaki uyarıları kontrol ediniz:</div>
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')

        <div style="text-align: center; margin-top: 40px; color: #64748b; font-size: 12px;">
            <div>Köksan Holding &copy; {{ date('Y') }} &bull; Güvenli Dış Paydaş Belge Yönetim Portalı</div>
            <div style="margin-top: 4px; font-size: 11px; color: #94a3b8;">Verileriniz ISO 27001 Bilgi Güvenliği Standartlarına uygun olarak 256-bit SSL ile şifrelenmektedir.</div>
        </div>
    </div>

    @yield('scripts')
</body>
</html>
