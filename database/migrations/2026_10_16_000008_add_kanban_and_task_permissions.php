<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Mevcut task.manage yetkisini 'Kanban & Görevler' grubuna taşı
        Permission::where('slug', 'task.manage')->update([
            'name' => 'Görevleri Düzenleme & Yönetme',
            'group_name' => 'Kanban & Görevler',
            'description' => 'Görev detaylarını, termin ve önceliklerini yönetme yetkisi.',
        ]);

        // 2. Yeni Kanban ve Görev İzinleri Tanımla
        $permissions = [
            [
                'name' => 'Kanban Panosunu Görüntüleme',
                'slug' => 'kanban.view',
                'group_name' => 'Kanban & Görevler',
                'type' => 'page',
                'description' => 'İnteraktif Kanban Panosu ekranına erişim ve kartları aşama bazında görüntüleme yetkisi.',
            ],
            [
                'name' => 'Kanban Kartı Taşıma (Sürükle-Bırak / Durum Değiştirme)',
                'slug' => 'kanban.change_status',
                'group_name' => 'Kanban & Görevler',
                'type' => 'button',
                'description' => 'Kanban panosunda görev kartlarını sütunlar arasında sürükleyip durumunu güncelleme yetkisi.',
            ],
            [
                'name' => 'Yeni Görev Kartı Ekleme',
                'slug' => 'task.create',
                'group_name' => 'Kanban & Görevler',
                'type' => 'button',
                'description' => 'Kanban panosu veya proje üzerinden yeni görev kartı oluşturma yetkisi.',
            ],
            [
                'name' => 'Göreve Personel Atama / Sorumlu Değiştirme',
                'slug' => 'task.assign',
                'group_name' => 'Kanban & Görevler',
                'type' => 'button',
                'description' => 'Görev kartlarına tekli veya çoklu sorumlu personel atama ve değiştirme yetkisi.',
            ],
        ];

        $createdPerms = [];
        foreach ($permissions as $p) {
            $createdPerms[$p['slug']] = Permission::firstOrCreate(
                ['slug' => $p['slug']],
                $p
            );
        }

        // 3. Varsayılan Rol Eşleştirmeleri
        $roleAssignments = [
            'superadmin' => ['kanban.view', 'kanban.change_status', 'task.create', 'task.assign', 'task.manage'],
            'admin' => ['kanban.view', 'kanban.change_status', 'task.create', 'task.assign', 'task.manage'],
            'proje_yoneticisi' => ['kanban.view', 'kanban.change_status', 'task.create', 'task.assign', 'task.manage'],
            'teknik_lider' => ['kanban.view', 'kanban.change_status', 'task.create', 'task.assign', 'task.manage'],
            'bolum_lideri' => ['kanban.view', 'kanban.change_status', 'task.create', 'task.assign', 'task.manage'],
            'direktor' => ['kanban.view', 'kanban.change_status', 'task.create', 'task.assign', 'task.manage'],
            'kalite_denetleyici' => ['kanban.view', 'kanban.change_status', 'task.create'],
            'isg_sorumlusu' => ['kanban.view', 'kanban.change_status', 'task.create'],
            'kullanici' => ['kanban.view', 'kanban.change_status'],
            'saha_sefi' => ['kanban.view', 'kanban.change_status'],
            'taseron' => ['kanban.view', 'kanban.change_status'],
            'yonetim' => ['kanban.view'], // Madde 19: Sadece salt okunur izleme!
            'sponsor' => ['kanban.view'],
        ];

        $allPerms = Permission::all()->keyBy('slug');

        foreach ($roleAssignments as $roleSlug => $slugs) {
            $role = Role::where('slug', $roleSlug)->first();
            if (!$role) continue;

            $permIds = [];
            foreach ($slugs as $s) {
                if (isset($allPerms[$s])) {
                    $permIds[] = $allPerms[$s]->id;
                }
            }

            // Sync without detaching existing permissions
            $role->permissions()->syncWithoutDetaching($permIds);
        }
    }

    public function down(): void
    {
        Permission::whereIn('slug', [
            'kanban.view',
            'kanban.change_status',
            'task.create',
            'task.assign',
        ])->delete();
    }
};
