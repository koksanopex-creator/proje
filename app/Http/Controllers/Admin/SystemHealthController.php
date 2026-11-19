<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemHealthService;
use Illuminate\Http\Request;

class SystemHealthController extends Controller
{
    public function index()
    {
        $health = app(SystemHealthService::class)->getSystemHealth();
        return view('admin.system_health', compact('health'));
    }

    public function retest(Request $request)
    {
        $health = app(SystemHealthService::class)->getSystemHealth();

        $statusText = match($health['overall_status']) {
            'HEALTHY' => 'Tüm sistem bileşenleri ve rotalar başarıyla doğrulandı (0 Hata).',
            'WARNING' => 'Temel altyapı operasyonel; ancak ' . $health['broken_routes'] . ' rotada geliştirici incelemesi gerekmektedir.',
            'CRITICAL' => 'Kritik altyapı hatası tespit edildi: Veritabanı veya Storage erişimi başarısız!',
            default => 'Sistem tanı denetimi başarıyla tamamlandı.'
        };

        return response()->json([
            'success' => $health['overall_status'] !== 'CRITICAL',
            'status' => $health['overall_status'],
            'message' => $statusText,
            'timestamp' => $health['server_time'],
            'data' => $health,
        ]);
    }
}
