<?php

namespace App\Services;

use App\Models\ProjectAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * İşlem detayını loglar.
     */
    public static function log(
        string $action,
        string $description,
        string $entityType,
        ?int $entityId = null,
        ?int $projectId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?User $actingAsUser = null,
        ?string $actingAsRoleName = null
    ): ProjectAuditLog {
        $currentUser = auth()->user();
        $userId = $currentUser ? $currentUser->id : 1; // Fallback sistem ID

        // Eğer başkası adına işlem yapılıyorsa (Madde 16)
        $actingAsUserId = $actingAsUser ? $actingAsUser->id : null;
        if ($actingAsUser && !$actingAsRoleName) {
            $actingAsRoleName = $actingAsUser->name;
        }

        // Eğer doğrudan belirtilmemişse ancak aktif bir vekalet görevi varsa otomatik bağla
        if (!$actingAsUserId && $currentUser) {
            $delegation = $projectId 
                ? $currentUser->getActiveReceivedDelegations(null, $projectId)->first()
                : $currentUser->getActiveReceivedDelegations()->first();

            if ($delegation && $delegation->delegator) {
                $actingAsUserId = $delegation->delegator_id;
                if (!$actingAsRoleName) {
                    $actingAsRoleName = $delegation->delegator->name . ' (Vekaleten: ' . $currentUser->name . ')';
                }
            }
        }

        return ProjectAuditLog::create([
            'project_id' => $projectId,
            'user_id' => $userId,
            'acting_as_user_id' => $actingAsUserId,
            'acting_as_role_name' => $actingAsRoleName,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
