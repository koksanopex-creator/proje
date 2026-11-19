<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserDelegation extends Model
{
    use HasFactory;

    protected $fillable = [
        'delegator_id',
        'delegatee_id',
        'starts_at',
        'ends_at',
        'scope',
        'project_scope_type',
        'project_ids',
        'reason_category',
        'notes',
        'is_active',
        'cancelled_at',
        'cancel_reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'is_active' => 'boolean',
            'scope' => 'array',
            'project_ids' => 'array',
        ];
    }

    public function delegator()
    {
        return $this->belongsTo(User::class, 'delegator_id');
    }

    public function delegatee()
    {
        return $this->belongsTo(User::class, 'delegatee_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Şuan geçerli ve aktif olan vekaletler sorgusu
     */
    public function scopeActive($query)
    {
        $now = now();
        return $query->where('is_active', true)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now);
    }

    /**
     * Vekaletin süresi dolmuş mu?
     */
    public function isExpired(): bool
    {
        return now()->isAfter($this->ends_at);
    }

    /**
     * Vekalet şu anda canlı geçerli mi?
     */
    public function isCurrentlyValid(): bool
    {
        return $this->is_active && now()->between($this->starts_at, $this->ends_at);
    }

    /**
     * Belirli bir kapsamı içeriyor mu? (Örn: 'approvals', 'tasks', 'meetings', 'all')
     */
    public function coversScope(string $requestedScope): bool
    {
        if (empty($this->scope)) {
            return true;
        }

        if (in_array('all', $this->scope, true)) {
            return true;
        }

        // Birebir slug eşleşmesi (Örn: 'proje.approve')
        if (in_array($requestedScope, $this->scope, true)) {
            return true;
        }

        // Genel şemsiye filtreler (Geriye dönük uyumluluk)
        if ($requestedScope === 'tasks') {
            return in_array('task.manage', $this->scope, true) || in_array('task.create', $this->scope, true) || in_array('kanban.change_status', $this->scope, true) || in_array('tasks', $this->scope, true);
        }
        if ($requestedScope === 'approvals') {
            return in_array('proje.approve', $this->scope, true) || in_array('vote_in_committee', $this->scope, true) || in_array('isg.approve', $this->scope, true) || in_array('approvals', $this->scope, true);
        }
        if ($requestedScope === 'meetings') {
            return in_array('meeting.create', $this->scope, true) || in_array('meeting.manage', $this->scope, true) || in_array('meetings', $this->scope, true);
        }

        return false;
    }

    /**
     * Belirli bir projeyi kapsıyor mu?
     */
    public function coversProject(int|Project $project): bool
    {
        if ($this->project_scope_type === 'all' || empty($this->project_ids)) {
            return true;
        }

        $projectId = is_object($project) ? $project->id : (int)$project;
        $projectIds = array_map('intval', (array)$this->project_ids);

        return in_array($projectId, $projectIds, true);
    }

    /**
     * Vekaletin fiili süresini hesaplar ve metin olarak döner
     * (Örn: "5 gün vekalet edildi", "1 gün 4 saat vekalet edildi", "3 saat vekalet edildi")
     */
    public function getDurationText(): string
    {
        $start = $this->starts_at;

        if (!$this->is_active) {
            $end = $this->cancelled_at ?: ($this->updated_at ?: $this->ends_at);
        } elseif (now()->isBefore($this->ends_at)) {
            $end = now();
        } else {
            $end = $this->ends_at;
        }

        if (!$end || $end->isBefore($start)) {
            $end = $start;
        }

        $totalMinutes = $start->diffInMinutes($end);
        $days = intdiv($totalMinutes, 1440);
        $hours = intdiv($totalMinutes % 1440, 60);
        $minutes = $totalMinutes % 60;

        $parts = [];
        if ($days > 0) {
            $parts[] = "{$days} gün";
        }
        if ($hours > 0 && $days < 7) {
            $parts[] = "{$hours} saat";
        }
        if (empty($parts)) {
            $parts[] = ($minutes > 0 ? "{$minutes} dakika" : "1 dakikadan az");
        }

        $prefix = $this->is_active ? 'Şu ana kadar ' : '';
        return $prefix . implode(' ', $parts) . ' vekalet edildi';
    }

    /**
     * Bu vekalet sözleşmesi kapsamında vekilin gerçekleştirdiği denetim/işlem loglarını döner
     */
    public function getAuditLogs()
    {
        $start = $this->starts_at;
        $end = !$this->is_active ? ($this->cancelled_at ?: ($this->updated_at ?: $this->ends_at)) : now();

        $query = ProjectAuditLog::with(['project:id,title', 'user:id,name', 'actingAsUser:id,name'])
            ->where(function ($q) use ($start, $end) {
                $q->where(function ($sub) {
                    $sub->where('acting_as_user_id', $this->delegator_id)
                        ->where('user_id', $this->delegatee_id);
                })->orWhere(function ($sub) use ($start, $end) {
                    $sub->where('user_id', $this->delegatee_id)
                        ->whereBetween('created_at', [$start, $end]);
                });
            });

        if ($this->project_scope_type === 'selected' && !empty($this->project_ids)) {
            $projectIds = array_map('intval', (array)$this->project_ids);
            $query->whereIn('project_id', $projectIds);
        }

        return $query->latest()->limit(50)->get();
    }

    /**
     * Kapsam etiketleri kataloğu & slug etiket çözümleyici (Matris Başlıkları ile Birebir Uyumlu)
     */
    public static function getScopeCatalog(): array
    {
        $catalog = [
            // Genel Şemsiye Yetkiler
            'all' => 'Tam Yetki (Tüm Yetkiler)',
            'approvals' => 'Yalnızca Onay Süreçleri',
            'tasks' => 'Görev Atama & Güncelleme',
            'meetings' => 'Toplantı & Karar Katılımı',

            // Proje Süreçleri
            'proje.approve' => 'Proje Onaylama / İlerletme',
            'proje.edit' => 'Proje Düzenleme',
            'proje.create' => 'Yeni Proje Oluşturma',
            'proje.clone' => 'Şablondan Klonlama',
            'proje.delete' => 'Proje Silme',
            'proje.view' => 'Projeleri Görüntüleme',
            'proje.view_all' => 'Tüm Şirket Projelerini Görüntüleme',
            'proje.view_department' => 'Bölüm Projelerini Görüntüleme',
            'proje.manage_categories' => 'Proje Kategorilerini Yönetme',
            'proje.manage_team_roles' => 'Ekip Sorumluluk Rolleri',

            // Kanban & Görevler
            'task.manage' => 'Görevleri Düzenleme & Yönetme',
            'task.create' => 'Yeni Görev Kartı Ekleme',
            'task.assign' => 'Göreve Personel Atama',
            'kanban.view' => 'Kanban Panosunu Görüntüleme',
            'kanban.change_status' => 'Kanban Kartı Taşıma',
            'timesheet.create' => 'Zaman Girişi (Timesheet)',
            'timesheet.approve' => 'Timesheet Onaylama',

            // Bütçe, DOA & Kurul
            'butce.view' => 'Bütçe & BOM Görüntüleme',
            'butce.manage' => 'Bütçe & BOM Yönetimi',
            'manage_doa_rules' => 'Bütçe Eşik Kademelerini Yönetme',
            'view_doa_rules' => 'Bütçe Kademelerini Görüntüleme',
            'manage_committees' => 'Kurul ve Komiteleri Yönetme',
            'view_committees' => 'Kurul ve Komiteleri Görüntüleme',
            'vote_in_committee' => 'Kurul Oylamalarına Katılabilme',

            // Toplantılar
            'meeting.view' => 'Toplantı Modülü Görüntüleme',
            'meeting.create' => 'Yeni Toplantı Planlama',
            'meeting.manage' => 'Toplantı Oluşturma & Karar Alma',

            // Ekip & Yetki Devri
            'project.manage_team_matrix' => 'Proje İçi Ekip Yetki Matrisini Yönetme',
            'project.handover_member' => 'Personel Görev/Rol Devri',
            'project.change_manager' => 'Proje Liderini Değiştirme',
            'delegation.create' => 'Kurumsal Vekalet Verme',
            'delegation.manage_all' => 'Tüm Vekaletleri Yönetme',

            // İSG & Denetim
            'isg.view' => 'İSG Modülünü Görüntüleme',
            'isg.approve' => 'İSG Kapı Kilidi Onayı Verme',
            'isg.upload_permit' => 'İSG İzin Belgesi Yükleme',

            // Rapor & Sistem
            'report.view' => 'Raporları Görüntüleme',
            'export.data' => 'Veri Dışa Aktarma (Excel/PDF)',
            'health.view' => 'Sistem Sağlığı Sayfası',
            'log.view' => 'Denetim Loglarını Görüntüleme',
            'matrix.manage' => 'Yetki ve Bildirim Matrisi Yönetimi',
            'settings.manage' => 'Sistem Ayarları & Logo Yönetimi',
        ];

        return $catalog;
    }

    /**
     * Standart vekalet nedeni kategorileri
     */
    public static function getReasonCategories(): array
    {
        return [
            'Yıllık İzin',
            'Yurt Dışı Saha Görevi',
            'Saha / Şantiye Ziyareti',
            'Sağlık / İstirahat',
            'Şirket İçi Eğitim / Seminer',
            'Mazeret İzni',
            'Diğer / Özel Görev',
        ];
    }

    /**
     * Kategori ve kişisel mesajı harmanlayarak döner
     */
    public function getFormattedReason(): array
    {
        $cat = trim($this->reason_category ?? '');
        $note = trim($this->notes ?? '');

        // Geriye dönük uyumluluk: Eğer category boşsa ama notes varsa
        if (empty($cat) && !empty($note)) {
            // Eğer note bilinen kategorilerden biriyse onu kategori yap
            if (in_array($note, self::getReasonCategories(), true)) {
                $cat = $note;
                $note = '';
            } else {
                $cat = 'Genel Görev / İzin';
            }
        }

        return [
            'category' => $cat ?: 'Belirtilmedi',
            'note' => $note,
            'has_note' => !empty($note),
            'display_badge' => $cat ? "(Sebep: {$cat})" : '',
            'cancelled_at' => $this->cancelled_at ? $this->cancelled_at->format('d.m.Y H:i') : null,
            'cancel_reason' => $this->cancel_reason,
            'duration_text' => $this->getDurationText(),
            'project_scope_type' => $this->project_scope_type ?: 'all',
            'project_ids' => $this->project_ids ?: [],
        ];
    }

    /**
     * Vekalet kapsamını devreden kullanıcının sahip olduğu tüm yetkilerle
     * karşılaştırarak detaylı granted/denied matrisi halinde döner
     */
    public function getDetailedScopeMatrix(): array
    {
        $delegator = $this->delegator;
        if (!$delegator) {
            return [
                'is_all' => false,
                'total_granted' => 0,
                'total_available' => 0,
                'groups' => [],
            ];
        }

        $allGroups = $delegator->getDelegatablePermissions();
        $isAll = empty($this->scope) || in_array('all', $this->scope, true);

        $result = [];
        $totalGranted = 0;
        $totalAvailable = 0;

        foreach ($allGroups as $group) {
            $groupName = $group['group'];
            $items = [];

            foreach ($group['permissions'] as $p) {
                $totalAvailable++;
                $granted = $isAll || $this->coversScope($p['slug']);
                if ($granted) {
                    $totalGranted++;
                }

                $items[] = [
                    'slug' => $p['slug'],
                    'name' => $p['name'],
                    'desc' => $p['desc'],
                    'category' => $p['category'] ?? 'general',
                    'is_granted' => $granted,
                ];
            }

            $result[] = [
                'group' => $groupName,
                'items' => $items,
            ];
        }

        return [
            'is_all' => $isAll,
            'total_granted' => $totalGranted,
            'total_available' => $totalAvailable,
            'groups' => $result,
        ];
    }

    /**
     * Proje üst bandında ve listelerde gösterilecek özet yetki rozeti verisi
     */
    public function getScopeSummaryBadge(): array
    {
        $matrix = $this->getDetailedScopeMatrix();
        if ($matrix['is_all']) {
            return [
                'type' => 'all',
                'label' => '🌟 Tüm Yetkiler Devredildi',
                'count_text' => "Tam Yetki ({$matrix['total_granted']}/{$matrix['total_available']})",
                'bg' => '#dcfce7',
                'color' => '#15803d',
                'border' => '#86efac',
            ];
        }

        return [
            'type' => 'partial',
            'label' => "🎯 {$matrix['total_granted']} Yetki Devredildi",
            'count_text' => "{$matrix['total_granted']}/{$matrix['total_available']} Yetki Kapsamında",
            'bg' => '#eff6ff',
            'color' => '#1d4ed8',
            'border' => '#bfdbfe',
        ];
    }
}
