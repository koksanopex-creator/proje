<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectStakeholderPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'permission_key',
        'is_granted',
        'granted_by',
    ];

    protected function casts(): array
    {
        return [
            'is_granted' => 'boolean',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function granter()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    /**
     * İzin anahtarlarının insan tarafından okunabilir etiketleri
     */
    public static function getPermissionCatalog(): array
    {
        return [
            'task_manage' => [
                'title' => 'Görev Yönetimi',
                'description' => 'Projeye görev oluşturma, atama ve durum güncelleme yetkisi',
                'badge' => '📋 Görev',
                'color' => '#0284c7',
            ],
            'meeting_plan' => [
                'title' => 'Toplantı Planlama',
                'description' => 'Proje toplantısı planlama, tutanak ve karar yazma yetkisi',
                'badge' => '📅 Toplantı',
                'color' => '#8b5cf6',
            ],
            'bom_manage' => [
                'title' => 'BOM & Harcama Girişi',
                'description' => 'Malzeme ağacı (BOM) ve harcama kalemi ekleme yetkisi',
                'badge' => '💰 BOM & Harcama',
                'color' => '#10b981',
            ],
            'phase_report' => [
                'title' => 'Faz / Kilometre Taşı',
                'description' => 'Proje fazlarını tamamlama ve ilerleme raporlama yetkisi',
                'badge' => '🚩 Faz Raporu',
                'color' => '#f59e0b',
            ],
            'file_manage' => [
                'title' => 'Doküman Yönetimi',
                'description' => 'Sözleşme, teknik şartname ve dosya yükleme/yönetme yetkisi',
                'badge' => '📁 Doküman',
                'color' => '#06b6d4',
            ],
        ];
    }
}
