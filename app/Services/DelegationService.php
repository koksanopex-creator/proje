<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDelegation;
use App\Services\AuditLogService;
use App\Services\NotificationMatrixService;
use Carbon\Carbon;
use Exception;

class DelegationService
{
    /**
     * Yeni kurumsal vekalet tanımlar
     */
    public function createDelegation(User $delegator, int $delegateeId, array $data, User $actor): UserDelegation
    {
        $delegatee = User::findOrFail($delegateeId);

        // 1. Kendi kendine vekalet engeli
        if ($delegator->id === $delegatee->id) {
            throw new Exception("Kullanıcı kendi kendine vekalet veremez.");
        }

        $startsAt = Carbon::parse($data['starts_at']);
        $endsAt = Carbon::parse($data['ends_at']);

        // 2. Tarih mantık kontrolü
        if ($endsAt->isBefore($startsAt)) {
            throw new Exception("Vekalet bitiş tarihi başlangıç tarihinden önce olamaz.");
        }

        // 3. Döngüsel vekalet engeli (Örn: A kişisi B'ye vekalet verirken, B kişisinin de A'ya aktif vekaleti var mı?)
        $reverseDelegationExists = UserDelegation::where('delegator_id', $delegatee->id)
            ->where('delegatee_id', $delegator->id)
            ->where('is_active', true)
            ->where('ends_at', '>=', $startsAt)
            ->where('starts_at', '<=', $endsAt)
            ->exists();

        if ($reverseDelegationExists) {
            throw new Exception("Döngüsel vekalet tespit edildi! **{$delegatee->name}** kullanıcısının bu tarih aralığında **{$delegator->name}** kullanıcısına ait aktif vekaleti bulunmaktadır.");
        }

        // 4. Çakışan mükerrer vekalet kontrolü
        $overlapExists = UserDelegation::where('delegator_id', $delegator->id)
            ->where('delegatee_id', $delegatee->id)
            ->where('is_active', true)
            ->where('ends_at', '>=', $startsAt)
            ->where('starts_at', '<=', $endsAt)
            ->exists();

        if ($overlapExists) {
            throw new Exception("Bu tarih aralığında bu kullanıcıya zaten tanımlanmış aktif bir vekalet mevcuttur.");
        }

        $scope = isset($data['scope']) && is_array($data['scope']) && !empty($data['scope'])
            ? $data['scope']
            : ['all'];

        $notes = $data['notes'] ?? null;
        $reasonCategory = $data['reason_category'] ?? null;

        $projectScopeType = $data['project_scope_type'] ?? 'all';
        $projectIds = null;
        if ($projectScopeType === 'selected') {
            $projectIds = array_values(array_filter(array_map('intval', (array)($data['project_ids'] ?? []))));
            if (empty($projectIds)) {
                throw new Exception("Proje bazlı vekalet seçildiğinde en az bir proje seçilmelidir.");
            }
        } else {
            $projectScopeType = 'all';
        }

        $delegation = UserDelegation::create([
            'delegator_id' => $delegator->id,
            'delegatee_id' => $delegatee->id,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'scope' => $scope,
            'project_scope_type' => $projectScopeType,
            'project_ids' => $projectIds,
            'reason_category' => $reasonCategory,
            'notes' => $notes,
            'is_active' => true,
            'created_by' => $actor->id,
        ]);

        $scopeCatalog = UserDelegation::getScopeCatalog();
        if (in_array('all', $scope, true)) {
            $scopeText = 'Tam Yetki (Tüm Yetkiler)';
        } else {
            $translatedScopes = array_map(function ($slug) use ($scopeCatalog) {
                return $scopeCatalog[$slug] ?? $slug;
            }, $scope);
            $scopeText = implode(', ', $translatedScopes);
        }
        $projectText = $projectScopeType === 'all' ? 'Tüm Projeler' : count($projectIds) . ' Seçili Proje';

        // Audit Trail (Kurumsal Loglama)
        AuditLogService::log(
            'proxy_assigned',
            "**{$actor->name}** tarafından **{$delegator->name}** adına **{$delegatee->name}** vekil tayin edildi. (Tarih: {$startsAt->format('d.m.Y H:i')} - {$endsAt->format('d.m.Y H:i')}, Yetkiler: {$scopeText}, Projeler: {$projectText})",
            'UserDelegation',
            $delegation->id,
            null,
            null,
            [
                'delegator' => $delegator->name,
                'delegatee' => $delegatee->name,
                'starts_at' => $startsAt->format('d.m.Y H:i'),
                'ends_at' => $endsAt->format('d.m.Y H:i'),
                'scope' => $scopeText,
                'project_scope' => $projectText,
                'reason' => $reasonCategory ?: 'Belirtilmedi',
            ]
        );

        // Bildirim Tetikleme (Zil & Mail)
        try {
            app(NotificationMatrixService::class)->dispatch(
                'vekalet_verildi',
                [
                    'vekalet_veren' => $delegator->name,
                    'kullanici_adi' => $delegatee->name,
                    'baslangic_tarihi' => $startsAt->format('d.m.Y H:i'),
                    'bitis_tarihi' => $endsAt->format('d.m.Y H:i'),
                    'kapsam' => $scopeText,
                    'notlar' => $notes ?: 'Belirtilmedi',
                ],
                route('dashboard'),
                $delegatee->id
            );
        } catch (\Throwable $e) {
            // Bildirim hatası akışı kesmesin
        }

        return $delegation;
    }

    /**
     * Vekaleti iptal eder / erken sonlandırır
     */
    public function cancelDelegation(int $delegationId, User $actor, ?string $reason = null): UserDelegation
    {
        $delegation = UserDelegation::with(['delegator', 'delegatee'])->findOrFail($delegationId);

        // İptal yetkisi: Asıl yetkili, vekilin kendisi ya da Superadmin/Admin
        if ($actor->id !== $delegation->delegator_id && $actor->id !== $delegation->delegatee_id && !$actor->isSuperAdmin() && !$actor->isAdmin()) {
            throw new Exception("Bu vekalet sözleşmesini iptal etme yetkiniz bulunmamaktadır.");
        }

        $cancelReason = $reason ?: 'Kullanıcı talebiyle erken sonlandırıldı';

        $delegation->update([
            'is_active' => false,
            'cancelled_at' => now(),
            'cancel_reason' => $cancelReason,
        ]);

        AuditLogService::log(
            'proxy_revoked',
            "**{$actor->name}** tarafından **{$delegation->delegator->name}** -> **{$delegation->delegatee->name}** kurumsal vekaleti erken sonlandırıldı / iptal edildi. (Gerekçe: {$cancelReason})",
            'UserDelegation',
            $delegation->id
        );

        // Bildirim Tetikleme (İptal - Zil & Mail)
        try {
            $notifService = app(NotificationMatrixService::class);
            $vars = [
                'vekalet_veren' => $delegation->delegator?->name ?? 'Kullanıcı',
                'vekil_adi' => $delegation->delegatee?->name ?? 'Vekil',
                'islem_yapan' => $actor->name,
                'gerekce' => $cancelReason,
            ];

            // Vekile bildir (Yetkileriniz geri alındı)
            if ($delegation->delegatee_id) {
                $notifService->dispatch(
                    'vekalet_iptal_edildi',
                    array_merge($vars, ['kullanici_adi' => $delegation->delegatee->name]),
                    route('dashboard'),
                    $delegation->delegatee_id
                );
            }

            // Eğer iptal eden kişi vekalet verenin kendisi değilse, asıl kişiye de bildir
            if ($actor->id !== $delegation->delegator_id && $delegation->delegator_id) {
                $notifService->dispatch(
                    'vekalet_iptal_edildi',
                    array_merge($vars, ['kullanici_adi' => $delegation->delegator->name]),
                    route('dashboard'),
                    $delegation->delegator_id
                );
            }
        } catch (\Throwable $e) {
            // Bildirim hatası akışı kesmesin
        }

        return $delegation;
    }

    /**
     * Süresi dolan vekaletleri tespit eder, pasife alır ve her iki tarafa da zil ve mail bildirimi gönderir
     */
    public function checkAndNotifyExpiredDelegations(): int
    {
        $now = now();
        $expiredDelegations = UserDelegation::with(['delegator', 'delegatee'])
            ->where('is_active', true)
            ->where('ends_at', '<=', $now)
            ->get();

        $count = 0;
        $notifService = app(NotificationMatrixService::class);

        foreach ($expiredDelegations as $delegation) {
            $delegation->update(['is_active' => false]);

            AuditLogService::log(
                'proxy_expired',
                "**{$delegation->delegator->name}** -> **{$delegation->delegatee->name}** kurumsal vekalet sözleşmesinin süresi ({$delegation->ends_at->format('d.m.Y H:i')}) dolmuş ve vekillik otomatik sonlandırılmıştır.",
                'UserDelegation',
                $delegation->id
            );

            try {
                $vars = [
                    'vekalet_veren' => $delegation->delegator?->name ?? 'Kullanıcı',
                    'vekil_adi' => $delegation->delegatee?->name ?? 'Vekil',
                    'bitis_tarihi' => $delegation->ends_at->format('d.m.Y H:i'),
                ];

                // Vekile bildir
                if ($delegation->delegatee_id) {
                    $notifService->dispatch(
                        'vekalet_suresi_doldu',
                        array_merge($vars, ['kullanici_adi' => $delegation->delegatee->name]),
                        route('dashboard'),
                        $delegation->delegatee_id
                    );
                }

                // Vekalet verene bildir
                if ($delegation->delegator_id) {
                    $notifService->dispatch(
                        'vekalet_suresi_doldu',
                        array_merge($vars, ['kullanici_adi' => $delegation->delegator->name]),
                        route('dashboard'),
                        $delegation->delegator_id
                    );
                }
            } catch (\Throwable $e) {
                // Bildirim hatası akışı kesmesin
            }

            $count++;
        }

        return $count;
    }

    /**
     * Vekil üzerinden bir işlem gerçekleştirildiğinde zorunlu denetim izi üretir
     */
    public function recordProxyExecution(User $delegatee, User $delegator, string $actionName, string $entityType, int $entityId, ?int $projectId = null): void
    {
        // rules1.md Madde 16 formatı: "{islem_yapan} tarafından {adına_islem_yapilan} adına {islem_adi} gerçekleştirildi."
        $logMessage = "**{$delegator->name}** adına vekaleten **{$delegatee->name}** tarafından **{$actionName}** gerçekleştirildi.";

        AuditLogService::log(
            'proxy_executed',
            $logMessage,
            $entityType,
            $entityId,
            $projectId
        );

        // Asıl yetkiliye anlık bilgi bildirimi
        try {
            app(NotificationMatrixService::class)->dispatch(
                'vekaleten_islem_yapildi',
                [
                    'vekil_adi' => $delegatee->name,
                    'kullanici_adi' => $delegator->name,
                    'proje_kodu' => $projectId ? 'PRJ-' . $projectId : 'Genel',
                    'islem_adi' => $actionName,
                    'zaman' => now()->format('d.m.Y H:i'),
                ],
                $projectId ? route('projects.show', $projectId) : route('dashboard'),
                $delegator->id
            );
        } catch (\Throwable $e) {
            // sessiz
        }
    }
}
