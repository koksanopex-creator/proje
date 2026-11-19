<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'title', 'description', 'category', 'categories', 'status', 'priority', 'location',
        'bolum_id', 'bolum_ids', 'manager_id', 'sponsor_id', 'planned_budget', 'actual_spent', 'currency',
        'start_date', 'target_end_date', 'actual_end_date', 'progress_percentage',
        'visibility', 'notification_settings', 'scope_tools', 'tags', 'current_waiting_role_id', 'current_waiting_user_id', 'waiting_since', 'waiting_step_name',
        'draft_created_at', 'draft_updated_at', 'draft_history', 'submitted_for_approval_at', 'approved_at', 'rejected_at',
        'is_template', 'created_by'
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'target_end_date' => 'date',
            'actual_end_date' => 'date',
            'waiting_since' => 'datetime',
            'draft_created_at' => 'datetime',
            'draft_updated_at' => 'datetime',
            'draft_history' => 'array',
            'submitted_for_approval_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'is_template' => 'boolean',
            'notification_settings' => 'array',
            'scope_tools' => 'array',
            'tags' => 'array',
            'planned_budget' => 'decimal:2',
            'actual_spent' => 'decimal:2',
            'categories' => 'array',
            'bolum_ids' => 'array',
        ];
    }

    /**
     * Proje etiketlerini dizi olarak döndürür
     */
    public function getTagsArrayAttribute(): array
    {
        $tags = $this->tags;
        if (is_string($tags)) {
            $decoded = json_decode($tags, true);
            return is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $tags)));
        }
        return is_array($tags) ? array_values(array_filter($tags)) : [];
    }

    /**
     * Projenin belirli bir etiketi var mı?
     */
    public function hasTag(string $tag): bool
    {
        $tag = ltrim(trim($tag), '#');
        foreach ($this->tags_array as $t) {
            if (strcasecmp(ltrim(trim($t), '#'), $tag) === 0) {
                return true;
            }
        }
        return false;
    }

    public function hasScopeTool(string $toolKey): bool
    {
        $tools = $this->scope_tools;
        if (!is_array($tools) || empty($tools[$toolKey])) {
            return false;
        }
        $tool = $tools[$toolKey];
        if (is_array($tool)) {
            return !empty($tool['enabled']) || !empty($tool['active']) || (isset($tool[0]) && count($tool) > 0) || count(array_filter($tool)) > 0;
        }
        return (bool)$tool;
    }

    public function getScopeTool(string $toolKey, $default = null)
    {
        return $this->scope_tools[$toolKey] ?? $default;
    }

    public function getActiveScopeToolsCount(): int
    {
        $tools = $this->scope_tools;
        if (!is_array($tools)) return 0;
        $count = 0;
        foreach (['budget_chart', 'kpis', 'scope_boundaries', 'roi_calculator', 'dependencies', 'custom_fields'] as $key) {
            if ($this->hasScopeTool($key)) $count++;
        }
        return $count;
    }

    public function getActiveScopeToolsSummary(): array
    {
        $tools = $this->scope_tools;
        if (!is_array($tools)) return [];
        
        $summary = [];
        if ($this->hasScopeTool('budget_chart')) {
            $count = count($tools['budget_chart']['items'] ?? []);
            $summary['budget_chart'] = $count > 0 ? "📊 {$count} Kalem Bütçe Grafiği" : "📊 Bütçe Dağılım Grafiği";
        }
        if ($this->hasScopeTool('kpis')) {
            $count = count($tools['kpis']['items'] ?? []);
            $summary['kpis'] = $count > 0 ? "🎯 {$count} SMART KPI" : "🎯 SMART Hedefler";
        }
        if ($this->hasScopeTool('scope_boundaries')) {
            $inCount = count($tools['scope_boundaries']['in_scope'] ?? []);
            $outCount = count($tools['scope_boundaries']['out_scope'] ?? []);
            $summary['scope_boundaries'] = "📌 Kapsam Sınırları ({$inCount} Dahil / {$outCount} Hariç)";
        }
        if ($this->hasScopeTool('roi_calculator')) {
            $roi = $tools['roi_calculator'];
            $pb = !empty($roi['payback_years']) ? $roi['payback_years'] . ' Yıl' : (!empty($roi['payback_months']) ? $roi['payback_months'] . ' Ay' : null);
            $summary['roi_calculator'] = $pb ? "⚖️ ROI ({$pb} Geri Dönüş)" : "⚖️ ROI & Geri Dönüş";
        }
        if ($this->hasScopeTool('dependencies')) {
            $count = count($tools['dependencies']['items'] ?? []);
            $summary['dependencies'] = $count > 0 ? "🔗 {$count} Dış Bağımlılık" : "🔗 Dış Bağımlılıklar";
        }
        if ($this->hasScopeTool('custom_fields')) {
            $count = count($tools['custom_fields']['items'] ?? []);
            $summary['custom_fields'] = $count > 0 ? "🧩 {$count} Özel Parametre" : "🧩 Özel Parametreler";
        }
        return $summary;
    }

    public function getNameAttribute(): string
    {
        return $this->title ?? '';
    }

        public function bolumler()
    {
        $ids = $this->bolum_ids ?? ($this->bolum_id ? [$this->bolum_id] : []);
        return Bolum::whereIn('id', (array)$ids)->get();
    }

    public function bolum() { return $this->belongsTo(Bolum::class, 'bolum_id'); }
    public function manager() { return $this->belongsTo(User::class, 'manager_id'); }
    public function sponsor() { return $this->belongsTo(User::class, 'sponsor_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function waitingRole() { return $this->belongsTo(Role::class, 'current_waiting_role_id'); }
    public function waitingUser() { return $this->belongsTo(User::class, 'current_waiting_user_id'); }

    public function phases() { return $this->hasMany(ProjectPhase::class, 'project_id')->orderBy('sort_order'); }
    public function stakeholders() { return $this->hasMany(ProjectStakeholder::class, 'project_id'); }
    public function activeStakeholders() { return $this->hasMany(ProjectStakeholder::class, 'project_id')->where('is_active', true); }
    public function stakeholderPermissions() { return $this->hasMany(ProjectStakeholderPermission::class, 'project_id'); }
    public function tasks() { return $this->hasMany(ProjectTask::class, 'project_id'); }
    public function risks() { return $this->hasMany(ProjectRisk::class, 'project_id'); }
    public function boms() { return $this->hasMany(ProjectBom::class, 'project_id'); }
    public function budgetItems() { return $this->hasMany(ProjectBudgetItem::class, 'project_id'); }
    public function budgets() { return $this->budgetItems(); }
    public function meetings() { return $this->hasMany(ProjectMeeting::class, 'project_id'); }
    public function files() { return $this->hasMany(ProjectFile::class, 'project_id'); }
    public function auditLogs() { return $this->hasMany(ProjectAuditLog::class, 'project_id')->latest(); }
    public function approvalRequests() { return $this->hasMany(ProjectApprovalRequest::class, 'project_id')->latest(); }
    public function latestApprovalRequest() { return $this->hasOne(ProjectApprovalRequest::class, 'project_id')->latestOfMany(); }

    // Canlı Bekleme Süresi Formatı
    public function getWaitingDurationAttribute(): ?string
    {
        if (!$this->waiting_since) return null;
        $diff = now()->diff($this->waiting_since);
        if ($diff->days > 0) return "{$diff->days} gündür";
        if ($diff->h > 0) return "{$diff->h} saattir";
        return "{$diff->i} dakikadır";
    }

    public function discussions() {
        return $this->hasMany(ProjectDiscussion::class)->latest();
    }
    // Sistem Standart Durum Etiketi
    public function getStatusLabelAttribute(): string
    {
        $map = [
            'draft' => 'Taslak',
            'in_progress' => 'Devam Ediyor',
            'waiting_approval' => 'Onay Bekliyor',
            'pending' => 'Onay Bekliyor',
            'waiting_ehs' => 'İSG Onayı Bekleniyor',
            'review' => 'İncelemede',
            'blocked' => 'Tıkandı',
            'critical' => 'Kritik',
            'completed' => 'Tamamlandı',
            'done' => 'Tamamlandı',
            'on_hold' => 'Askıda',
            'cancelled' => 'İptal',
        ];

        return $map[$this->status] ?? ucfirst(str_replace('_', ' ', (string)$this->status));
    }

    public function getPriorityLabelAttribute(): string
    {
        return match(strtolower($this->priority ?? 'medium')) {
            'critical' => 'Kritik',
            'high' => 'Yüksek',
            'low' => 'Düşük',
            default => 'Orta',
        };
    }

    /**
     * Takvim Sağlığı (Schedule Health - Takvim Durumu)
     */
    public function getScheduleHealthAttribute(): array
    {
        if ($this->status === 'completed' || $this->status === 'done' || (int)$this->progress_percentage === 100) {
            return [
                'status' => 'completed',
                'label' => 'Tamamlandı',
                'badge' => 'badge-success',
                'color' => '#10b981',
                'bg' => '#ecfdf5',
                'border' => '#a7f3d0'
            ];
        }

        $now = now();
        $target = $this->target_end_date ? \Carbon\Carbon::parse($this->target_end_date) : null;

        if (!$target) {
            return [
                'status' => 'on_track',
                'label' => 'Yolunda',
                'badge' => 'badge-success',
                'color' => '#10b981',
                'bg' => '#ecfdf5',
                'border' => '#a7f3d0'
            ];
        }

        if ($target->isPast()) {
            return [
                'status' => 'overdue',
                'label' => 'Gecikmede',
                'badge' => 'badge-danger',
                'color' => '#ef4444',
                'bg' => '#fef2f2',
                'border' => '#fecaca'
            ];
        }

        $daysLeft = $now->diffInDays($target, false);
        $progress = (int)$this->progress_percentage;

        if ($daysLeft <= 14 && $progress < 60) {
            return [
                'status' => 'at_risk',
                'label' => 'Risk Altında',
                'badge' => 'badge-warning',
                'color' => '#f59e0b',
                'bg' => '#fffbeb',
                'border' => '#fde68a'
            ];
        }

        return [
            'status' => 'on_track',
            'label' => 'Yolunda',
            'badge' => 'badge-success',
            'color' => '#10b981',
            'bg' => '#ecfdf5',
            'border' => '#a7f3d0'
        ];
    }

    /**
     * Bütçe Sağlığı (Budget Health - Bütçe Durumu)
     */
    public function getBudgetHealthAttribute(): array
    {
        $planned = (float)($this->planned_budget ?? 0);
        $spent = (float)($this->actual_spent ?? 0);

        if ($planned <= 0) {
            return [
                'status' => 'n_a',
                'label' => 'Plan Yok',
                'badge' => 'badge-secondary',
                'color' => '#64748b',
                'bg' => '#f1f5f9',
                'border' => '#cbd5e1'
            ];
        }

        $ratio = ($spent / $planned) * 100;

        if ($spent > $planned) {
            return [
                'status' => 'over_budget',
                'label' => 'Bütçe Aşımı',
                'badge' => 'badge-danger',
                'color' => '#ef4444',
                'bg' => '#fef2f2',
                'border' => '#fecaca'
            ];
        }

        if ($ratio >= 90) {
            return [
                'status' => 'near_limit',
                'label' => 'Bütçe Sınırında',
                'badge' => 'badge-warning',
                'color' => '#f59e0b',
                'bg' => '#fffbeb',
                'border' => '#fde68a'
            ];
        }

        if ($ratio < 50) {
            return [
                'status' => 'under_budget',
                'label' => 'Bütçe İçi',
                'badge' => 'badge-info',
                'color' => '#0284c7',
                'bg' => '#f0f9ff',
                'border' => '#bae6fd'
            ];
        }

        return [
            'status' => 'on_track',
            'label' => 'Bütçe İçi',
            'badge' => 'badge-success',
            'color' => '#10b981',
            'bg' => '#ecfdf5',
            'border' => '#a7f3d0'
        ];
    }
    public function draftSuggestions()
    {
        return $this->hasMany(ProjectDraftSuggestion::class, 'project_id')->orderBy('created_at', 'desc');
    }

    public function draftShares()
    {
        return $this->hasMany(ProjectDraftShare::class, 'project_id')->orderBy('created_at', 'desc');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function scopeAccessibleBy($query, ?User $user = null)
    {
        return $this->scopeForUser($query, $user);
    }

    /**
     * Projeleri kullanıcının rol, departman ve yetki sınırlarına göre filtreleyen evrensel sorgu kapsamı (Scope).
     */
    public function scopeForUser($query, ?User $user = null)
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        // Harici kullanıcılar sadece ve sadece dahil oldukları projeleri görebilir
        if ($user->isExternal()) {
            return $query->whereHas('stakeholders', function ($sq) use ($user) {
                $sq->where('user_id', $user->id);
            });
        }

        // Superadmin, Admin, Yonetim veya 'proje.view_all' yetkisine sahip olanlar tüm projeleri görebilir
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isYonetim() || $user->hasPermission('proje.view_all')) {
            return $query;
        }

        // Aktif vekillikleri topla
        $activeDelegations = $user->getActiveReceivedDelegations();
        $delegatorIds = [];
        $explicitProjectIds = [];
        $hasSuperDelegator = false;
        $combinedDeptIds = (array)($user->getResponsibleDepartmentIds() ?? []);

        foreach ($activeDelegations as $del) {
            $delegator = $del->delegator;
            if (!$delegator) continue;

            if ($del->project_scope_type === 'selected' && !empty($del->project_ids)) {
                $explicitProjectIds = array_merge($explicitProjectIds, array_map('intval', (array)$del->project_ids));
            } else {
                if ($delegator->isSuperAdmin() || $delegator->isAdmin() || $delegator->isYonetim() || $delegator->hasPermission('proje.view_all')) {
                    $hasSuperDelegator = true;
                }
                $delegatorIds[] = $delegator->id;
                if ($delegator->isDirector() || $delegator->isDepartmentLeader() || $delegator->hasPermission('proje.view_department')) {
                    $dDepts = $delegator->getResponsibleDepartmentIds();
                    if (!empty($dDepts)) {
                        $combinedDeptIds = array_merge($combinedDeptIds, $dDepts);
                    }
                }
            }
        }

        if ($hasSuperDelegator) {
            return $query;
        }

        $allUserIds = array_unique(array_merge([$user->id], $delegatorIds));
        $combinedDeptIds = array_unique($combinedDeptIds);
        $explicitProjectIds = array_unique($explicitProjectIds);

        return $query->where(function ($q) use ($allUserIds, $combinedDeptIds, $explicitProjectIds, $user) {
            // 1. Doğrudan ilişkili projeler (Yönetici, Sponsor, Oluşturan veya Paydaş - Asıl veya Vekaleten)
            $q->whereIn('manager_id', $allUserIds)
              ->orWhereIn('sponsor_id', $allUserIds)
              ->orWhereIn('created_by', $allUserIds)
              ->orWhereHas('stakeholders', function ($sq) use ($allUserIds) {
                  $sq->whereIn('user_id', $allUserIds);
              });

            // 2. Proje bazlı açıkça yetkilendirilmiş vekalet projeleri
            if (!empty($explicitProjectIds)) {
                $q->orWhereIn('id', $explicitProjectIds);
            }

            // 3. Departman bazlı erişim yetkisi varsa
            if (!empty($combinedDeptIds)) {
                $q->orWhereIn('bolum_id', $combinedDeptIds);

                // Çoklu bölüm (bolum_ids JSON) eşleşmesi
                foreach ($combinedDeptIds as $dId) {
                    $q->orWhereJsonContains('bolum_ids', (int)$dId)
                      ->orWhereJsonContains('bolum_ids', (string)$dId);
                }
            }
        });
    }

    /**
     * Kullanıcının bu projede belirli bir operasyonel yetkisi var mı?
     * (Superadmin/Admin tam yetkili, Proje Lideri tam yetkili, vekil tayin edilmişse vekil tam yetkili,
     * ya da project_stakeholder_permissions tablosunda yetki verilmişse geçerli).
     */
    public function hasStakeholderPermission(User $user, string $permissionKey): bool
    {
        // 1. Superadmin ve Admin sistem genelinde tam yetkilidir
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        // 2. Proje Lideri proje içindeki tüm operasyonları yönetebilir
        if ((int)$this->manager_id === (int)$user->id) {
            return true;
        }

        // 3. Proje Liderinin aktif bir vekili varsa ve bu kullanıcı vekil ise
        if ($this->manager_id && $user->isProxyFor($this->manager_id, 'tasks', $this)) {
            return true;
        }

        // 4. Proje içi delege edilmiş izin kontrolü (Doğrudan veya Vekaleten)
        $perm = $this->stakeholderPermissions()
            ->where('user_id', $user->id)
            ->where('permission_key', $permissionKey)
            ->first();

        if ($perm && $perm->is_granted) {
            return true;
        }

        $delegatingIds = $user->getActiveDelegatingUserIds($this);
        if (!empty($delegatingIds)) {
            $delegatorPerm = $this->stakeholderPermissions()
                ->whereIn('user_id', $delegatingIds)
                ->where('permission_key', $permissionKey)
                ->where('is_granted', true)
                ->exists();
            if ($delegatorPerm) {
                return true;
            }
        }

        return false;
    }

    /**
     * Proje paydaşlarının (Lider, Sponsor, Ekip Üyeleri) şu an aktif olan kurumsal vekalet sözleşmelerini döner
     */
    public function getActiveStakeholderDelegations()
    {
        $projectUserIds = array_filter(array_unique(array_merge(
            [$this->manager_id, $this->sponsor_id],
            $this->stakeholders->pluck('user_id')->toArray()
        )));

        $now = now();
        $delegations = \App\Models\UserDelegation::with(['delegator.bolum', 'delegatee.bolum'])
            ->where('is_active', true)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now)
            ->where(function ($q) use ($projectUserIds) {
                $q->whereIn('delegator_id', $projectUserIds)
                  ->orWhereJsonContains('project_ids', $this->id);
            })
            ->get();

        return $delegations->filter(fn($d) => $d->coversProject($this));
    }

    /**
     * Bu proje paydaşlarının geçmiş (iptal edilmiş veya süresi bitmiş) vekalet kayıtlarını döner
     */
    public function getPastStakeholderDelegations()
    {
        $projectUserIds = array_filter(array_unique(array_merge(
            [$this->manager_id, $this->sponsor_id],
            $this->stakeholders->pluck('user_id')->toArray()
        )));

        $now = now();
        $delegations = \App\Models\UserDelegation::with(['delegator.bolum', 'delegatee.bolum'])
            ->where(function ($q) use ($now) {
                $q->where('is_active', false)
                  ->orWhere('ends_at', '<', $now);
            })
            ->where(function ($q) use ($projectUserIds) {
                $q->whereIn('delegator_id', $projectUserIds)
                  ->orWhereJsonContains('project_ids', $this->id);
            })
            ->latest('ends_at')
            ->limit(10)
            ->get();

        return $delegations->filter(fn($d) => $d->coversProject($this));
    }
}