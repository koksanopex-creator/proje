<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectStakeholder;
use App\Models\User;

class ProjectStakeholderService
{
    /**
     * Kullanıcının projeyi görüntüleme yetkisi var mı? (Scope Filtering & Department Isolation)
     */
    public function canAccessProject(Project $project, User $user): bool
    {
        return $user->canAccessProject($project);
    }

    /**
     * Kullanıcının projeyi yönetme yetkisi var mı?
     */
    public function canManageProject(Project $project, User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        if ($project->manager_id === $user->id || $project->created_by === $user->id || $project->sponsor_id === $user->id) {
            return true;
        }

        // Kurumsal vekalet kontrolü (Vekil olarak yönetme yetkisi)
        $activeDelegations = $user->getActiveReceivedDelegations(null, $project);
        foreach ($activeDelegations as $del) {
            if ($del->coversScope('proje.edit') || $del->coversScope('all')) {
                $delegator = $del->delegator;
                if ($delegator && ($delegator->isSuperAdmin() || $delegator->isAdmin() || $project->manager_id === $delegator->id || $project->created_by === $delegator->id || $project->sponsor_id === $delegator->id)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Projeye gözlemci ekler
     */
    public function addObserver(Project $project, int $userId, ?string $notes, User $currentUser): ProjectStakeholder
    {
        $targetUser = User::findOrFail($userId);

        $exists = $project->stakeholders()
            ->where('user_id', $userId)
            ->where('role_type', 'observer')
            ->exists();

        if ($exists) {
            throw new \Exception("Bu kullanıcı ({$targetUser->name}) zaten bu projede Gözlemci olarak kayıtlıdır.");
        }

        $stakeholder = $project->stakeholders()->create([
            'user_id' => $userId,
            'role_type' => 'observer',
            'raci_role' => 'I',
            'notes' => $notes ?: 'Gözlemci Paydaş',
        ]);

        AuditLogService::log(
            'observer_added',
            "**{$currentUser->name}**, **{$targetUser->name}** kullanıcısını **{$project->title}** projesine Gözlemci (Informed) olarak ekledi.",
            'ProjectStakeholder',
            $stakeholder->id,
            $project->id
        );

        app(NotificationMatrixService::class)->dispatch(
            'gozlemci_eklendi',
            [
                'proje_adi' => $project->title,
                'proje_kodu' => $project->code,
                'ekleyen' => $currentUser->name,
                'gozlemci_adi' => $targetUser->name,
                'notlar' => $notes ?: 'Gözlemci olarak yetkilendirildiniz.',
            ],
            route('projects.show', $project->id),
            $targetUser->id
        );

        return $stakeholder;
    }

    /**
     * Projeden gözlemciyi çıkarır
     */
    public function removeObserver(Project $project, int $stakeholderId, User $currentUser): void
    {
        $stakeholder = $project->stakeholders()->findOrFail($stakeholderId);
        $targetUser = $stakeholder->user;

        $stakeholder->delete();

        AuditLogService::log(
            'observer_removed',
            "**{$currentUser->name}**, **{$targetUser?->name}** kullanıcısını **{$project->title}** projesinin Gözlemci listesinden çıkardı.",
            'ProjectStakeholder',
            $stakeholderId,
            $project->id
        );
    }

    /**
     * Proje gizlilik durumunu değiştirir (Gizli / Herkese Açık)
     */
    public function toggleVisibility(Project $project, User $currentUser): Project
    {
        $newVal = !$project->is_confidential;
        $project->update(['is_confidential' => $newVal]);

        $statusStr = $newVal ? 'Gizli (Yalnızca Ekip & Gözlemciler)' : 'Herkese Açık (Şirket Geneli)';

        AuditLogService::log(
            'visibility_changed',
            "**{$currentUser->name}**, **{$project->title}** projesinin gizlilik ayarını değiştirdi: **{$statusStr}**",
            'Project',
            $project->id,
            $project->id
        );

        return $project;
    }

    /**
     * Proje içi ekip üyesi mikro yetkisini canlı açar / kapatır
     */
    public function toggleStakeholderPermission(Project $project, int $userId, string $key, bool $state, User $actor): \App\Models\ProjectStakeholderPermission
    {
        // Yetki kontrolü: Superadmin, Admin, Proje Lideri veya 'project.manage_team_matrix' izni
        $canManage = $actor->isSuperAdmin() || $actor->isAdmin() || $project->manager_id === $actor->id || $actor->hasPermission('project.manage_team_matrix');
        if (!$canManage) {
            throw new \Exception("Bu projede ekip içi yetki matrisini yönetme yetkiniz bulunmamaktadır.");
        }

        $targetUser = User::findOrFail($userId);
        $catalog = \App\Models\ProjectStakeholderPermission::getPermissionCatalog();
        if (!isset($catalog[$key])) {
            throw new \Exception("Geçersiz yetki anahtarı: {$key}");
        }

        $perm = \App\Models\ProjectStakeholderPermission::updateOrCreate(
            [
                'project_id' => $project->id,
                'user_id' => $userId,
                'permission_key' => $key,
            ],
            [
                'is_granted' => $state,
                'granted_by' => $actor->id,
            ]
        );

        $actionText = $state ? 'yetkisi verildi' : 'yetkisi kaldırıldı';
        $permTitle = $catalog[$key]['title'];

        AuditLogService::log(
            'matrix_delegated',
            "**{$actor->name}** tarafından **{$project->title}** projesinde **{$targetUser->name}** kullanıcısına '**{$permTitle}**' {$actionText}.",
            'ProjectStakeholderPermission',
            $perm->id,
            $project->id
        );

        if ($state) {
            try {
                app(NotificationMatrixService::class)->dispatch(
                    'proje_yetki_delege_edildi',
                    [
                        'proje_kodu' => $project->code,
                        'proje_adi' => $project->title,
                        'islem_yapan' => $actor->name,
                        'yetki_adi' => $permTitle,
                        'kullanici_adi' => $targetUser->name,
                    ],
                    route('projects.show', $project->id),
                    $targetUser->id
                );
            } catch (\Throwable $e) {}
        }

        return $perm;
    }

    /**
     * Ayrılan / Görevi değişen personelin görev ve rollerini başka bir üyeye devreder (Handover Wizard)
     */
    public function handoverMember(Project $project, int $fromUserId, int $toUserId, array $options, User $actor): array
    {
        $canHandover = $actor->isSuperAdmin() || $actor->isAdmin() || $project->manager_id === $actor->id || $actor->hasPermission('project.handover_member');
        if (!$canHandover) {
            throw new \Exception("Bu projede görev ve rol devri gerçekleştirme yetkiniz bulunmamaktadır.");
        }

        if ($fromUserId === $toUserId) {
            throw new \Exception("Devir yapılacak kişi mevcut kullanıcı ile aynı olamaz.");
        }

        $fromUser = User::findOrFail($fromUserId);
        $toUser = User::findOrFail($toUserId);

        // 1. Ayrılan kullanıcının proje paydaş kaydı
        $fromStakeholder = $project->stakeholders()->where('user_id', $fromUserId)->first();
        if (!$fromStakeholder) {
            throw new \Exception("**{$fromUser->name}** kullanıcısı bu projenin paydaşı değildir.");
        }

        $reason = $options['reason'] ?? 'Personel Ayrılışı / Görev Değişimi';

        // 2. Hedef kullanıcı projede yoksa otomatik paydaş olarak ekle
        $toStakeholder = $project->stakeholders()->where('user_id', $toUserId)->first();
        if (!$toStakeholder) {
            $toStakeholder = $project->stakeholders()->create([
                'user_id' => $toUserId,
                'role_type' => $fromStakeholder->role_type ?: 'team_member',
                'raci_role' => $fromStakeholder->raci_role ?: 'R',
                'notes' => "**{$fromUser->name}** kullanıcısından devralındı",
                'is_active' => true,
            ]);
        } else {
            $toStakeholder->update(['is_active' => true]);
        }

        // 3. Eski kullanıcıyı pasife çek
        $fromStakeholder->update([
            'is_active' => false,
            'left_at' => now(),
            'left_reason' => $reason,
            'handover_to_user_id' => $toUserId,
        ]);

        // 4. Açık görevlerin aktarılması
        $transferredTasksCount = 0;
        if (!empty($options['transfer_tasks'])) {
            $tasksToMove = \App\Models\ProjectTask::where('project_id', $project->id)
                ->where('assigned_to', $fromUserId)
                ->whereNotIn('status', ['done', 'completed'])
                ->get();

            foreach ($tasksToMove as $tsk) {
                $tsk->update(['assigned_to' => $toUserId]);
                $transferredTasksCount++;
            }
        }

        // 5. Delege yetkilerin aktarılması
        $transferredPermsCount = 0;
        if (!empty($options['transfer_permissions'])) {
            $oldPerms = \App\Models\ProjectStakeholderPermission::where('project_id', $project->id)
                ->where('user_id', $fromUserId)
                ->where('is_granted', true)
                ->get();

            foreach ($oldPerms as $op) {
                \App\Models\ProjectStakeholderPermission::updateOrCreate(
                    [
                        'project_id' => $project->id,
                        'user_id' => $toUserId,
                        'permission_key' => $op->permission_key,
                    ],
                    [
                        'is_granted' => true,
                        'granted_by' => $actor->id,
                    ]
                );
                // Eski yetkiyi kapat
                $op->update(['is_granted' => false]);
                $transferredPermsCount++;
            }
        }

        // Audit Trail
        AuditLogService::log(
            'member_handover',
            "**{$actor->name}** tarafından **{$project->title}** projesinde **{$fromUser->name}** kullanıcısının sorumlulukları **{$toUser->name}** personeline devredildi. (Aktarılan Görev: {$transferredTasksCount}, Aktarılan Yetki: {$transferredPermsCount}, Gerekçe: {$reason})",
            'ProjectStakeholder',
            $fromStakeholder->id,
            $project->id
        );

        // Bildirim Gönderimi
        try {
            app(NotificationMatrixService::class)->dispatch(
                'uye_gorev_devri_yapildi',
                [
                    'proje_kodu' => $project->code,
                    'proje_adi' => $project->title,
                    'islem_yapan' => $actor->name,
                    'ayrilan_kullanici' => $fromUser->name,
                    'kullanici_adi' => $toUser->name,
                    'gorev_sayisi' => (string)$transferredTasksCount,
                    'ayrilma_sebebi' => $reason,
                ],
                route('projects.show', $project->id),
                $toUserId
            );
        } catch (\Throwable $e) {}

        return [
            'success' => true,
            'transferred_tasks' => $transferredTasksCount,
            'transferred_permissions' => $transferredPermsCount,
        ];
    }

    /**
     * Proje devam ederken Proje Liderini resmi devirle değiştirir
     */
    public function changeProjectManager(Project $project, int $newManagerId, bool $keepOldAsMember, ?string $notes, User $actor): Project
    {
        $canChange = $actor->isSuperAdmin() || $actor->isAdmin() || $project->sponsor_id === $actor->id || $actor->hasPermission('project.change_manager');
        if (!$canChange) {
            throw new \Exception("Proje Liderini değiştirme yetkiniz bulunmamaktadır.");
        }

        if ((int)$project->manager_id === $newManagerId) {
            throw new \Exception("Seçilen kullanıcı zaten mevcut proje lideridir.");
        }

        $oldManager = $project->manager;
        $newManager = User::findOrFail($newManagerId);

        // Proje Liderini Güncelle
        $project->manager_id = $newManagerId;

        // Bekleyen onay kullanıcısı eski lider ise yeni lidere devret
        if ($project->current_waiting_user_id === $oldManager?->id) {
            $project->current_waiting_user_id = $newManagerId;
        }

        $project->save();

        // Yeni lideri stakeholders tablosuna ekle / güncelle
        $newLeaderStakeholder = $project->stakeholders()->where('user_id', $newManagerId)->first();
        if (!$newLeaderStakeholder) {
            $project->stakeholders()->create([
                'user_id' => $newManagerId,
                'role_type' => 'manager',
                'raci_role' => 'A',
                'notes' => 'Atanan Yeni Proje Lideri',
                'is_active' => true,
            ]);
        } else {
            $newLeaderStakeholder->update([
                'role_type' => 'manager',
                'raci_role' => 'A',
                'is_active' => true,
            ]);
        }

        // Eski liderin durumunu ayarla
        if ($oldManager) {
            $oldLeaderStakeholder = $project->stakeholders()->where('user_id', $oldManager->id)->first();
            if ($oldLeaderStakeholder) {
                if ($keepOldAsMember) {
                    $oldLeaderStakeholder->update([
                        'role_type' => 'team_member',
                        'raci_role' => 'R',
                        'notes' => 'Eski Proje Lideri (Ekip Üyesi olarak devam ediyor)',
                    ]);
                } else {
                    $oldLeaderStakeholder->update([
                        'is_active' => false,
                        'left_at' => now(),
                        'left_reason' => 'Proje Liderliği Devri Sonrası Ayrılış',
                        'handover_to_user_id' => $newManagerId,
                    ]);
                }
            }
        }

        AuditLogService::log(
            'manager_transferred',
            "**{$actor->name}** tarafından **{$project->title}** projesinin liderliği **{$oldManager?->name}** kullanıcısından **{$newManager->name}** kullanıcısına devredildi. (Ekipte kalma: " . ($keepOldAsMember ? 'Evet' : 'Hayır') . ", Not: " . ($notes ?: 'Yok') . ")",
            'Project',
            $project->id,
            $project->id
        );

        // Bildirim Tetikleme
        try {
            app(NotificationMatrixService::class)->dispatch(
                'proje_lideri_degistirildi',
                [
                    'proje_kodu' => $project->code,
                    'proje_adi' => $project->title,
                    'islem_yapan' => $actor->name,
                    'kullanici_adi' => $newManager->name,
                ],
                route('projects.show', $project->id),
                $newManagerId
            );
        } catch (\Throwable $e) {}

        return $project;
    }

    /**
     * Çıkar Çatışması Kalkanı (Separation of Duties):
     * Proje Lideri veya Proje Üyesi, şirket hiyerarşisinde Direktör dahi olsa kendi projesinin kurumsal onayını veremez!
     */
    public function enforceSeparationOfDuties(Project $project, User $user, string $actionType): void
    {
        // rules1.md Madde 15: Superadmin sistem genelinde tam temsil hakkına sahiptir
        if ($user->isSuperAdmin()) {
            return;
        }

        if ((int)$project->manager_id === (int)$user->id || (int)$project->created_by === (int)$user->id) {
            throw new \Exception("Çıkar Çatışması Kalkanı (Separation of Duties): Proje Lideri veya Projeyi Oluşturan personel, kendi projesinin kurumsal onayını ({$actionType}) veremez.");
        }
    }
}