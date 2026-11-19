<?php

namespace App\Services;

use App\Models\Bolum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepartmentSyncService
{
    /**
     * Merkezi IAA veritabanından (iaa_db.bolumler) bölümleri çeker ve koksan_pms.bolumler ile eşitler.
     */
    public static function syncFromCentral(): array
    {
        try {
            $centralDepts = DB::table('iaa_db.bolumler')
                ->where('is_active', 1)
                ->whereNull('deleted_at')
                ->get();

            $syncedCount = 0;
            foreach ($centralDepts as $dept) {
                Bolum::updateOrCreate(
                    ['ad' => $dept->ad],
                    [
                        'kod' => 'BOL-' . $dept->id,
                        'is_active' => true,
                    ]
                );
                $syncedCount++;
            }

            $allActive = Bolum::where('is_active', true)->orderBy('ad')->get(['id', 'ad', 'kod']);

            AuditLogService::log(
                'sync',
                "Merkezi Bölüm Senkronizasyonu yapıldı: {$syncedCount} aktif bölüm senkronize edildi.",
                'Bolum',
                null
            );

            return [
                'success' => true,
                'count' => $syncedCount,
                'bolumler' => $allActive,
                'message' => "Merkezden {$syncedCount} adet aktif bölüm başarıyla senkronize edildi.",
            ];
        } catch (\Throwable $e) {
            Log::error('DepartmentSyncService Hatası: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Merkezi veritabanına bağlanırken hata oluştu: ' . $e->getMessage(),
            ];
        }
    }
}