<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'central_user_id',
        'name',
        'email',
        'sicil_no',
        'bolum_id',
        'unvan',
        'phone',
        'avatar',
        'is_active',
        'is_external',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_external' => 'boolean',
        ];
    }

    // İlişkiler
    public function bolum()
    {
        return $this->belongsTo(Bolum::class, 'bolum_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'model_has_roles', 'user_id', 'role_id');
    }

    public function projectsAsManager()
    {
        return $this->hasMany(Project::class, 'manager_id');
    }

    public function projectsAsSponsor()
    {
        return $this->hasMany(Project::class, 'sponsor_id');
    }

    public function assignedTasks()
    {
        return $this->hasMany(ProjectTask::class, 'assigned_to');
    }

    public function calendarNotes()
    {
        return $this->hasMany(\App\Models\UserCalendarNote::class);
    }

    public function inAppNotifications()
    {
        return $this->hasMany(InAppNotification::class, 'user_id')->latest();
    }

        public function isExternal(): bool
    {
        return (bool) $this->is_external;
    }

    public function stakeholderProjects()
    {
        return $this->belongsToMany(Project::class, 'project_stakeholders', 'user_id', 'project_id');
    }

    // Rol Kontrol Metodları (rules1.md Kural 7, 14, 15, 19)
    public function hasRole(string $roleSlug): bool
    {
        return $this->roles->contains('slug', $roleSlug);
    }

    public function isSuperAdmin(): bool
    {
        // Rol ID=1 veya slug 'superadmin'
        return $this->roles->contains(function ($role) {
            return $role->id === 1 || $role->slug === 'superadmin';
        });
    }

    public function isYonetim(): bool
    {
        // Rol ID=19 veya slug 'yonetim' (Tamamen Read-Only, Madde 19)
        return $this->roles->contains(function ($role) {
            return $role->id === 19 || $role->slug === 'yonetim';
        });
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->isSuperAdmin();
    }

    public function isDeveloper(): bool
    {
        return $this->isSuperAdmin() || $this->hasRole('developer') || $this->hasRole('superadmin');
    }

    public function isDirector(): bool
    {
        // Direktörlük kontrolü: Rol ID=8 VEYA bir bölümün director_id'si bu kullanıcı
        if ($this->hasRole('direktor')) {
            return true;
        }
        return Bolum::where('director_id', $this->id)->exists();
    }

    public function isDepartmentLeader(): bool
    {
        if ($this->hasRole('bolum_lideri')) {
            return true;
        }
        return Bolum::where('lider_id', $this->id)->exists();
    }

    // Yetki (Permission) Kontrolü (Dinamik Yetki Matrisi)
    public function hasPermission(string $permissionSlug): bool
    {
        // Superadmin her şeye yetkilidir (Madde 15)
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Yönetim rolü veri mutasyonu izinlerine sahip olamaz (Madde 19)
        if ($this->isYonetim()) {
            if (str_contains($permissionSlug, '.create') || 
                str_contains($permissionSlug, '.edit') || 
                str_contains($permissionSlug, '.delete') || 
                str_contains($permissionSlug, '.approve') ||
                str_contains($permissionSlug, '.change') ||
                str_contains($permissionSlug, '.manage') ||
                str_contains($permissionSlug, '.assign')) {
                return false;
            }
            // Okuma ve export yetkileri tam açıktır
            if (str_contains($permissionSlug, '.view') || str_contains($permissionSlug, '.export')) {
                return true;
            }
        }

        // Rollerin izinlerini kontrol et
        foreach ($this->roles as $role) {
            if ($role->permissions->contains('slug', $permissionSlug)) {
                return true;
            }
        }

        // Kurumsal vekalet ile devredilmiş yetki kontrolü
        $activeDelegations = $this->getActiveReceivedDelegations($permissionSlug);
        foreach ($activeDelegations as $del) {
            $delegator = $del->delegator;
            if ($delegator && $delegator->id !== $this->id) {
                // Asıl yetkilinin kendi rollerinde bu izin varsa devralınır
                foreach ($delegator->roles as $role) {
                    if ($role->permissions->contains('slug', $permissionSlug)) {
                        return true;
                    }
                }
                if ($delegator->isSuperAdmin()) {
                    return true;
                }
            }
        }

        return false;
    }

    // Madde 14 & 15: Superadmin Maskeleme Kuralı
    // Diğer kullanıcılara "Yönetim", sadece Superadmin'lere "Yönetim (Ad Soyad)" gösterilir
    public function getMaskedNameFor(?User $viewer = null): string
    {
        if (!$this->isSuperAdmin()) {
            return $this->name;
        }

        if ($viewer && $viewer->isSuperAdmin()) {
            return "Yönetim ({$this->name})";
        }

        return "Yönetim";
    }

    /**
     * Kullanıcının sorumlu olduğu veya bağlı bulunduğu departman ID'lerini döndürür.
     * Superadmin, Admin, Yonetim veya 'proje.view_all' yetkisine sahip olanlar için null döner (Kısıtlama Yok).
     * Direktörler için bolumler.director_id = user.id olan tüm bölümler.
     * Bölüm Liderleri için bolumler.lider_id = user.id olan bölümler ve kendi bolum_id'si.
     * Standart kullanıcılar için [bolum_id] (eğer varsa).
     *
     * @return array<int>|null
     */
    public function getResponsibleDepartmentIds(): ?array
    {
        if ($this->isSuperAdmin() || $this->isAdmin() || $this->isYonetim() || $this->hasPermission('proje.view_all')) {
            return null;
        }

        $deptIds = [];

        // 1. Direktörlük kontrolü (rules1.md Madde 7: bolumler.director_id)
        if ($this->isDirector()) {
            $directorDeptIds = Bolum::where('director_id', $this->id)->pluck('id')->toArray();
            $deptIds = array_merge($deptIds, $directorDeptIds);
        }

        // 2. Bölüm Lideri kontrolü (rules1.md Madde 9 & Rol ID=2: bolumler.lider_id)
        if ($this->isDepartmentLeader()) {
            $leaderDeptIds = Bolum::where('lider_id', $this->id)->pluck('id')->toArray();
            $deptIds = array_merge($deptIds, $leaderDeptIds);
        }

        // 3. Kullanıcının kendi bağlı olduğu bölüm
        if ($this->bolum_id) {
            $deptIds[] = (int) $this->bolum_id;
        }

        $deptIds = array_values(array_unique(array_filter($deptIds)));

        return $deptIds;
    }

    // Proje Erişim ve Gözlemci Kontrolleri
    public function canAccessProject(Project $project): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Harici kullanıcılar yalnızca ve yalnızca o projenin paydaşı/gözlemcisi ise erişebilir
        // URL ID manipülasyonu ile başka projelere erişim KESİNLİKLE engellenir
        if ($this->isExternal()) {
            return $project->stakeholders()->where('user_id', $this->id)->exists();
        }

        if ($this->isSuperAdmin() || $this->isAdmin() || $this->isYonetim() || $this->hasPermission('proje.view_all')) {
            return true;
        }

        if ($project->manager_id === $this->id || $project->sponsor_id === $this->id || $project->created_by === $this->id) {
            return true;
        }

        if ($project->stakeholders()->where('user_id', $this->id)->exists()) {
            return true;
        }

        // Bölüm bazlı erişim kontrolü (Bölüm Lideri, Direktör veya 'proje.view_department' yetkisi olanlar)
        if ($this->isDirector() || $this->isDepartmentLeader() || $this->hasPermission('proje.view_department')) {
            $responsibleDepts = $this->getResponsibleDepartmentIds();
            if ($responsibleDepts !== null && !empty($responsibleDepts)) {
                if ($project->bolum_id && in_array((int)$project->bolum_id, $responsibleDepts, true)) {
                    return true;
                }
                $pBolumIds = (array)($project->bolum_ids ?? []);
                if (!empty(array_intersect($responsibleDepts, array_map('intval', $pBolumIds)))) {
                    return true;
                }
            }
        }

        // Kurumsal Vekalet Kontrolü: Bu kullanıcının vekili olduğu asıl yetkililerin projelerine erişim
        $activeDelegations = $this->getActiveReceivedDelegations(null, $project);
        foreach ($activeDelegations as $del) {
            $delegator = $del->delegator;
            if ($delegator && $delegator->id !== $this->id) {
                if ($delegator->isSuperAdmin() || $delegator->isAdmin() || $delegator->isYonetim() || $delegator->hasPermission('proje.view_all')) {
                    return true;
                }
                if ($project->manager_id === $delegator->id || $project->sponsor_id === $delegator->id || $project->created_by === $delegator->id) {
                    return true;
                }
                if ($project->stakeholders()->where('user_id', $delegator->id)->exists()) {
                    return true;
                }
                if ($delegator->isDirector() || $delegator->isDepartmentLeader() || $delegator->hasPermission('proje.view_department')) {
                    $dDepts = $delegator->getResponsibleDepartmentIds();
                    if ($dDepts !== null && !empty($dDepts)) {
                        if ($project->bolum_id && in_array((int)$project->bolum_id, $dDepts, true)) {
                            return true;
                        }
                        $pBolumIds = (array)($project->bolum_ids ?? []);
                        if (!empty(array_intersect($dDepts, array_map('intval', $pBolumIds)))) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    public function isProjectObserver(Project $project): bool
    {
        return $project->stakeholders()
            ->where('user_id', $this->id)
            ->where('role_type', 'observer')
            ->exists();
    }

    /**
     * Kullanıcının projeyi yönetme (düzenleme, görev atama, toplantı açma vb.) yetkisi var mı?
     */
    public function canManageProject(Project $project): bool
    {
        return app(\App\Services\ProjectStakeholderService::class)->canManageProject($project, $this);
    }

    public function committees()
    {
        return $this->belongsToMany(Committee::class, 'committee_members', 'user_id', 'committee_id')
            ->withPivot(['id', 'member_role', 'voting_weight', 'is_active'])
            ->withTimestamps();
    }

    public function committeeVotes()
    {
        return $this->hasMany(CommitteeVote::class, 'user_id');
    }

    // =========================================================================
    // KURUMSAL VEKALET (USER DEPUTYSHIP / PROXY) İLİŞKİLERİ VE METOTLARI
    // =========================================================================

    public function givenDelegations()
    {
        return $this->hasMany(UserDelegation::class, 'delegator_id');
    }

    public function receivedDelegations()
    {
        return $this->hasMany(UserDelegation::class, 'delegatee_id');
    }

    public function stakeholderPermissions()
    {
        return $this->hasMany(ProjectStakeholderPermission::class, 'user_id');
    }

    /**
     * Kullanıcının şu an canlıda geçerli olan vekalet sözleşmesini döner (Varsa)
     */
    public function getActiveGivenDelegation(?string $scope = null): ?UserDelegation
    {
        $now = now();
        $query = $this->givenDelegations()
            ->where('is_active', true)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now);

        $delegation = $query->first();
        if ($delegation && $scope && !$delegation->coversScope($scope)) {
            return null;
        }

        return $delegation;
    }

    /**
     * Bu kullanıcının şu an vekili olduğu aktif delegasyonları döner
     */
    public function getActiveReceivedDelegations(?string $scope = null, $project = null)
    {
        $now = now();
        $delegations = $this->receivedDelegations()
            ->with(['delegator', 'delegator.bolum'])
            ->where('is_active', true)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now)
            ->get();

        if ($scope) {
            $delegations = $delegations->filter(fn($d) => $d->coversScope($scope));
        }

        if ($project) {
            $delegations = $delegations->filter(fn($d) => $d->coversProject($project));
        }

        return $delegations;
    }

    /**
     * Bu kullanıcı, belirtilen delegator'ın şu an geçerli bir vekili mi?
     */
    public function isProxyFor(int|User $delegator, string $scope = 'all', $project = null): bool
    {
        $delegatorId = is_object($delegator) ? $delegator->id : (int)$delegator;
        if ($this->id === $delegatorId) {
            return false;
        }

        $now = now();
        $delegations = UserDelegation::where('delegator_id', $delegatorId)
            ->where('delegatee_id', $this->id)
            ->where('is_active', true)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now)
            ->get();

        foreach ($delegations as $d) {
            if ($d->coversScope($scope) && (!$project || $d->coversProject($project))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Bu kullanıcıya şu an aktif olarak vekalet vermiş delegator kullanıcı ID'lerini döner
     */
    public function getActiveDelegatingUserIds($project = null): array
    {
        return $this->getActiveReceivedDelegations(null, $project)
            ->pluck('delegator_id')
            ->unique()
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Kullanıcının vekiline devredebileceği, bizzat sahip olduğu yetkiler listesi
     */
    public function getDelegatablePermissions(): array
    {
        // 1. Kullanıcının sahip olduğu tüm izinler
        if ($this->isSuperAdmin()) {
            $userPerms = Permission::orderBy('id')->get();
        } else {
            $userPerms = $this->roles()
                ->with('permissions')
                ->get()
                ->pluck('permissions')
                ->flatten()
                ->unique('id')
                ->values();
        }

        // 2. Modüllere göre gruplanmış izin kataloğu
        $catalog = [
            'Proje & Onay Süreçleri' => [
                'proje.approve' => ['name' => 'Proje Onaylama / Faz İlerletme', 'desc' => 'Faz kapı onayları, tamamlama ve geçiş kararları', 'category' => 'approvals'],
                'proje.edit' => ['name' => 'Proje Bilgilerini Düzenleme', 'desc' => 'Tarih, kapsam ve proje detaylarını güncelleme', 'category' => 'projects'],
                'proje.create' => ['name' => 'Yeni Proje Başlatma', 'desc' => 'Taslak ve yeni proje oluşturabilme yetkisi', 'category' => 'projects'],
                'proje.clone' => ['name' => 'Şablondan Klonlama', 'desc' => 'Mevcut şablonlardan proje türetme', 'category' => 'projects'],
            ],
            'Görev, Planlama & Kanban' => [
                'task.manage' => ['name' => 'Görevleri Düzenleme & Yönetme', 'desc' => 'Görev durumları ve detaylarını yönetebilme', 'category' => 'tasks'],
                'task.create' => ['name' => 'Yeni Görev Kartı Ekleme', 'desc' => 'WBS / Faz altına yeni görev kartı açma', 'category' => 'tasks'],
                'task.assign' => ['name' => 'Göreve Personel Atama', 'desc' => 'Görev sorumlusu ve ekip üyesi tayini', 'category' => 'tasks'],
                'kanban.change_status' => ['name' => 'Kanban Kartı Taşıma', 'desc' => 'Panoda görev kartı sürükleme / durum geçişi', 'category' => 'tasks'],
                'timesheet.create' => ['name' => 'Zaman Girişi (Timesheet)', 'desc' => 'Görevlere fiili çalışma eforu kaydetme', 'category' => 'tasks'],
                'timesheet.approve' => ['name' => 'Timesheet Onaylama', 'desc' => 'Ekip efor girişlerini onaylama', 'category' => 'approvals'],
            ],
            'Bütçe, Satınalma & Kurul' => [
                'butce.manage' => ['name' => 'Bütçe & BOM Yönetimi', 'desc' => 'Malzeme, kalem ve harcama revizyonları', 'category' => 'finance'],
                'vote_in_committee' => ['name' => 'Kurul Oylamalarına Katılabilme', 'desc' => 'Yatırım/Karar kurullarında vekaleten oy kullanma', 'category' => 'approvals'],
                'manage_doa_rules' => ['name' => 'Bütçe Eşiklerini (DOA) Yönetme', 'desc' => 'Yetki devri kademelerini düzenleme', 'category' => 'finance'],
            ],
            'Toplantı & Temsil' => [
                'meeting.create' => ['name' => 'Yeni Toplantı Planlama', 'desc' => 'Tarih belirleme ve katılımcı davet etme', 'category' => 'meetings'],
                'meeting.manage' => ['name' => 'Toplantı Kararları & Tutanak', 'desc' => 'Gündem, karar ve aksiyon kaydetme', 'category' => 'meetings'],
            ],
            'Ekip & Yetki Devri' => [
                'project.manage_team_matrix' => ['name' => 'Ekip Mikro Yetki Matrisini Yönetme', 'desc' => 'Proje içi üye operasyonel yetkilerini açıp kapama', 'category' => 'team'],
                'project.handover_member' => ['name' => 'Personel Görev/Rol Devri', 'desc' => 'Ayrılan personelin görev ve rollerini aktarma', 'category' => 'team'],
            ],
            'İSG & Saha' => [
                'isg.approve' => ['name' => 'İSG Kapı Kilidi Onayı Verme', 'desc' => 'İSG onaylı faz geçişlerine onay verme', 'category' => 'approvals'],
                'isg.upload_permit' => ['name' => 'İSG İzin Belgesi Yükleme', 'desc' => 'Saha çalışma izin belgelerini sisteme ekleme', 'category' => 'safety'],
            ],
        ];

        $userPermSlugs = $userPerms->pluck('slug')->toArray();
        $result = [];

        foreach ($catalog as $groupTitle => $items) {
            $groupItems = [];
            foreach ($items as $slug => $meta) {
                // Kullanıcı bu yetkiye sahipse listeye ekle
                if ($this->isSuperAdmin() || in_array($slug, $userPermSlugs, true)) {
                    $groupItems[] = [
                        'slug' => $slug,
                        'name' => $meta['name'],
                        'desc' => $meta['desc'],
                        'category' => $meta['category'],
                    ];
                }
            }
            if (!empty($groupItems)) {
                $result[] = [
                    'group' => $groupTitle,
                    'permissions' => $groupItems,
                ];
            }
        }

        return $result;
    }
}
