<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class AssignDefaultPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionsByRole = [
            // 1. Superadmin (Developer) - Tüm izinler
            'superadmin' => [
                'proje.view', 'proje.create', 'proje.edit', 'proje.delete', 'proje.approve', 'proje.clone',
                'isg.view', 'isg.upload_permit', 'isg.approve', 'task.manage', 'timesheet.create',
                'timesheet.approve', 'butce.view', 'butce.manage', 'meeting.view', 'meeting.create', 'meeting.manage',
                'report.view', 'export.data', 'health.view', 'log.view', 'matrix.manage', 'settings.manage',
                'proje.view_all', 'proje.view_department', 'proje.manage_categories', 'proje.manage_team_roles',
                'kanban.view', 'kanban.change_status', 'task.create', 'task.assign'
            ],
            // 20. Admin (Sistem Yöneticisi)
            'admin' => [
                'proje.view', 'proje.create', 'proje.edit', 'proje.delete', 'proje.approve', 'proje.clone',
                'isg.view', 'isg.upload_permit', 'isg.approve', 'task.manage', 'timesheet.create',
                'timesheet.approve', 'butce.view', 'butce.manage', 'meeting.view', 'meeting.create', 'meeting.manage',
                'report.view', 'export.data', 'health.view', 'log.view', 'matrix.manage', 'settings.manage',
                'proje.view_all', 'proje.view_department',
                'kanban.view', 'kanban.change_status', 'task.create', 'task.assign'
            ],
            // 2. Bölüm Lideri
            'bolum_lideri' => [
                'proje.view', 'proje.create', 'proje.edit', 'proje.view_department',
                'task.manage', 'timesheet.create', 'timesheet.approve',
                'butce.view', 'meeting.view', 'meeting.create', 'meeting.manage',
                'report.view', 'export.data',
                'kanban.view', 'kanban.change_status', 'task.create', 'task.assign'
            ],
            // 8. Direktör (Kural 7)
            'direktor' => [
                'proje.view', 'proje.approve', 'proje.view_department',
                'task.manage', 'butce.view', 'meeting.view', 'meeting.create', 'meeting.manage',
                'report.view', 'export.data', 'isg.view', 'log.view',
                'kanban.view', 'kanban.change_status', 'task.create', 'task.assign'
            ],
            // 21. Proje Yöneticisi
            'proje_yoneticisi' => [
                'proje.view', 'proje.create', 'proje.edit', 'proje.clone', 'proje.approve',
                'task.manage', 'timesheet.create', 'timesheet.approve',
                'butce.view', 'butce.manage', 'meeting.view', 'meeting.create', 'meeting.manage',
                'report.view', 'export.data', 'log.view',
                'kanban.view', 'kanban.change_status', 'task.create', 'task.assign'
            ],
            // 3. Kullanıcı / Mühendis
            'kullanici' => [
                'proje.view', 'task.manage', 'timesheet.create', 'meeting.view',
                'kanban.view', 'kanban.change_status'
            ],
            // 28. Proje Sponsoru
            'sponsor' => [
                'proje.view', 'proje.approve', 'butce.view', 'meeting.view',
                'report.view', 'export.data', 'kanban.view'
            ],
            // 19. Yonetim (Salt Okunur İzleme, Kural 19)
            'yonetim' => [
                'proje.view', 'proje.view_all', 'butce.view', 'meeting.view',
                'report.view', 'export.data', 'health.view', 'log.view', 'isg.view',
                'kanban.view'
            ],
            // 22. İSG Denetleyicisi
            'isg_denetleyici' => [
                'proje.view', 'isg.view', 'isg.upload_permit', 'isg.approve',
                'report.view', 'log.view', 'kanban.view'
            ],
            // 23. İSG Sorumlusu
            'isg_sorumlusu' => [
                'proje.view', 'isg.view', 'isg.upload_permit', 'isg.approve', 'meeting.view', 'meeting.create',
                'kanban.view', 'kanban.change_status', 'task.create'
            ],
            // 24. Kalite Denetleyicisi
            'kalite_denetleyici' => [
                'proje.view', 'proje.create', 'task.manage', 'meeting.view', 'meeting.create', 'report.view',
                'kanban.view', 'kanban.change_status', 'task.create'
            ],
            // 25. Teknik Lider / Baş Mühendis
            'teknik_lider' => [
                'proje.view', 'proje.edit', 'task.manage', 'timesheet.create',
                'timesheet.approve', 'butce.view', 'meeting.view', 'meeting.create', 'meeting.manage',
                'kanban.view', 'kanban.change_status', 'task.create', 'task.assign'
            ],
            // 26. Saha / Şantiye Şefi
            'saha_sefi' => [
                'proje.view', 'task.manage', 'timesheet.create', 'isg.view',
                'isg.upload_permit', 'meeting.view',
                'kanban.view', 'kanban.change_status'
            ],
            // 27. Taşeron Temsilcisi
            'taseron' => [
                'proje.view', 'task.manage', 'timesheet.create',
                'kanban.view', 'kanban.change_status'
            ],
        ];

        $allPermissions = Permission::all()->keyBy('slug');

        foreach ($permissionsByRole as $roleSlug => $permSlugs) {
            $role = Role::where('slug', $roleSlug)->first();
            if (!$role) {
                continue;
            }

            $permissionIds = [];
            foreach ($permSlugs as $slug) {
                if (isset($allPermissions[$slug])) {
                    $permissionIds[] = $allPermissions[$slug]->id;
                }
            }

            $role->permissions()->sync($permissionIds);
        }
    }
}
