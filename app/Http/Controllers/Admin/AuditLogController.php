<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProjectAuditLog;
use App\Models\Role;
use App\Models\User;
use App\Models\Project;
use App\Models\LogVisibilitySetting;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ProjectAuditLog::with(['project', 'user', 'actingAsUser']);

        $viewer = auth()->user();

        // 1. DEVELOPER GİZLİLİĞİ KURALI:
        // Developer (Superadmin, ID=1) tarafından yapılan işlemleri SADECE Developer görebilir.
        if ($viewer && !$viewer->isDeveloper()) {
            $developerUserIds = User::whereHas('roles', function ($q) {
                $q->where('roles.id', 1)->orWhere('slug', 'superadmin')->orWhere('slug', 'developer');
            })->pluck('id')->toArray();

            if (!empty($developerUserIds)) {
                $query->whereNotIn('project_audit_logs.user_id', $developerUserIds);
            }
        }

        // 2. ADMIN GÖRÜNÜRLÜĞÜ KURALI:
        // Admin (ID=20) işlem loglarını sadece Admin, Developer ve Yönetim görebilir.
        $canSeeAdminLogs = $viewer && ($viewer->isDeveloper() || $viewer->hasRole('admin') || $viewer->isYonetim());
        if (!$canSeeAdminLogs) {
            $adminUserIds = User::whereHas('roles', function ($q) {
                $q->where('roles.id', 20)->orWhere('slug', 'admin');
            })->whereDoesntHave('roles', function ($q) {
                $q->where('roles.id', 1)->orWhere('slug', 'superadmin');
            })->pluck('id')->toArray();

            if (!empty($adminUserIds)) {
                $query->whereNotIn('project_audit_logs.user_id', $adminUserIds);
            }
        }

        // 3. Rol Bazlı Log Görünürlük Matrisi Filtrelemesi (Developer ve Admin hariç - matris kuralları uygulanır)
        if ($viewer && !$viewer->isSuperAdmin() && !$viewer->isAdmin()) {
            $userRoleIds = $viewer->roles ? $viewer->roles->pluck('id')->toArray() : [];
            if ($viewer->role_id && !in_array($viewer->role_id, $userRoleIds)) {
                $userRoleIds[] = $viewer->role_id;
            }

            $visibleCategories = LogVisibilitySetting::whereIn('role_id', $userRoleIds)
                ->where('can_view_logs', true)
                ->pluck('log_category')
                ->unique()
                ->toArray();

            $query->where(function ($q) use ($visibleCategories) {
                $hasCondition = false;

                if (in_array('proje', $visibleCategories) || in_array('project', $visibleCategories)) {
                    $q->orWhere(function ($sub) {
                        $sub->whereIn('entity_type', ['Project', 'ProjectDraftShare', 'ProjectDraftSuggestion'])
                            ->where('action', '!=', 'deleted');
                    });
                    $hasCondition = true;
                }

                if (in_array('proje_silme', $visibleCategories)) {
                    $q->orWhere(function ($sub) {
                        $sub->whereIn('entity_type', ['Project', 'ProjectDraftShare'])
                            ->where('action', 'deleted');
                    });
                    $hasCondition = true;
                }

                if (in_array('gorev', $visibleCategories) || in_array('task', $visibleCategories)) {
                    $q->orWhereIn('entity_type', ['ProjectTask', 'Task', 'ProjectPhase', 'ProjectTimesheet'])
                      ->orWhere('action', 'like', 'task_%');
                    $hasCondition = true;
                }

                if (in_array('risk', $visibleCategories)) {
                    $q->orWhere('entity_type', 'ProjectRisk');
                    $hasCondition = true;
                }

                if (in_array('isg', $visibleCategories) || in_array('ehs', $visibleCategories)) {
                    $q->orWhere('entity_type', 'ProjectGatekeeper')
                      ->orWhere('action', 'gatekeeper_approval');
                    $hasCondition = true;
                }

                if (in_array('butce', $visibleCategories) || in_array('financial', $visibleCategories) || in_array('budget', $visibleCategories)) {
                    $q->orWhereIn('entity_type', ['ProjectBudgetItem', 'ProjectBudgetCategory', 'Currency', 'ProjectBudget', 'Budget']);
                    $hasCondition = true;
                }

                if (in_array('toplanti', $visibleCategories) || in_array('meeting', $visibleCategories)) {
                    $q->orWhere('entity_type', 'ProjectMeeting');
                    $hasCondition = true;
                }

                if (in_array('doa', $visibleCategories)) {
                    $q->orWhereIn('entity_type', ['DoaThresholdRule', 'ProjectApprovalRequest']);
                    $hasCondition = true;
                }

                if (in_array('kurul', $visibleCategories)) {
                    $q->orWhereIn('entity_type', ['Committee', 'CommitteeMember', 'CommitteeVote']);
                    $hasCondition = true;
                }

                if (in_array('sistem', $visibleCategories) || in_array('system', $visibleCategories)) {
                    $q->orWhereIn('entity_type', ['SystemSetting', 'LogVisibilitySetting', 'Role', 'Permission', 'User', 'Bolum', 'ProjectCategory']);
                    $hasCondition = true;
                }

                if (in_array('vekalet', $visibleCategories)) {
                    $q->orWhereIn('entity_type', ['UserDelegation', 'ProjectStakeholderPermission', 'ProjectStakeholder'])
                      ->orWhereIn('action', ['matrix_delegated', 'member_handover', 'proxy_assigned', 'proxy_revoked', 'proxy_executed', 'proxy_expired', 'proxy_scope_updated', 'manager_transferred']);
                    $hasCondition = true;
                }

                if (!$hasCondition) {
                    $q->whereRaw('1 = 0');
                }
            });
        }

        // 1. Aksiyonlar Multi-Select Filtresi (actions[] ve action destekli)
        $selectedActions = (array) $request->input('actions', []);
        if (empty($selectedActions) && $request->filled('action') && $request->action !== 'all') {
            $selectedActions = [$request->action];
        }
        $selectedActions = array_values(array_filter($selectedActions, fn($v) => !empty($v) && $v !== 'all'));
        if (!empty($selectedActions)) {
            $query->whereIn('action', $selectedActions);
        }

        // 2. Kullanıcılar Multi-Select Filtresi (user_ids[] ve user_id destekli)
        $selectedUserIds = (array) $request->input('user_ids', []);
        if (empty($selectedUserIds) && $request->filled('user_id') && $request->user_id !== 'all') {
            $selectedUserIds = [$request->user_id];
        }
        $selectedUserIds = array_values(array_filter($selectedUserIds, fn($v) => !empty($v) && $v !== 'all'));
        if (!empty($selectedUserIds)) {
            $query->whereIn('user_id', $selectedUserIds);
        }

        // 3. Projeler Multi-Select Filtresi (project_ids[] ve project_id destekli)
        $selectedProjectIds = (array) $request->input('project_ids', []);
        if (empty($selectedProjectIds) && $request->filled('project_id') && $request->project_id !== 'all') {
            $selectedProjectIds = [$request->project_id];
        }
        $selectedProjectIds = array_values(array_filter($selectedProjectIds, fn($v) => $v !== null && $v !== '' && $v !== 'all'));
        if (!empty($selectedProjectIds)) {
            $query->where(function($q) use ($selectedProjectIds) {
                $hasGeneral = in_array('0', $selectedProjectIds) || in_array('general', $selectedProjectIds);
                $ids = array_filter($selectedProjectIds, fn($id) => is_numeric($id) && (int)$id > 0);
                if (!empty($ids)) {
                    $q->whereIn('project_id', $ids);
                }
                if ($hasGeneral) {
                    if (!empty($ids)) {
                        $q->orWhereNull('project_id');
                    } else {
                        $q->whereNull('project_id');
                    }
                }
            });
        }

        // 4. Kategori / Varlık Türü Multi-Select Filtresi (categories[] ve category destekli)
        $selectedCategories = (array) $request->input('categories', []);
        if (empty($selectedCategories) && $request->filled('category') && $request->category !== 'all') {
            $selectedCategories = [$request->category];
        }
        $selectedCategories = array_values(array_filter($selectedCategories, fn($v) => !empty($v) && $v !== 'all'));
        if (!empty($selectedCategories)) {
            $query->whereIn('entity_type', $selectedCategories);
        }

        // 5. Genel Arama (search)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('entity_type', 'like', "%{$search}%")
                  ->orWhereHas('project', function ($pq) use ($search) {
                      $pq->where('title', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(30)->withQueryString();

        $roles = Role::orderBy('id')->get();
        $visibilitySettings = LogVisibilitySetting::with('role')->get();

        // -------------------------------------------------------------
        // Excel Tarzı Filtre Menüleri İçin Katalog ve İstatistikler
        // -------------------------------------------------------------

        // 1. Aksiyon İstatistikleri (Yalnızca loglarda fiilen kaydı olanlar)
        $actionCounts = ProjectAuditLog::select('action', DB::raw('count(*) as count'))
            ->groupBy('action')
            ->pluck('count', 'action')
            ->toArray();

        // 1. Aksiyon İstatistikleri & Tek Merkezden Alınan Sistem Aksiyon Kataloğu
        $actionCounts = ProjectAuditLog::select('action', DB::raw('count(*) as count'))
            ->groupBy('action')
            ->pluck('count', 'action')
            ->toArray();

        // SADECE log kaydı bulunan veya aktif filtrelenmiş aksiyonları listele (Tek Merkezden)
        $activeActionsList = array_unique(array_merge(array_keys($actionCounts), $selectedActions));
        $actionCatalog = [];
        foreach ($activeActionsList as $act) {
            if (empty($act)) continue;
            $actionCatalog[$act] = ProjectAuditLog::getActionMeta($act);
        }

        // 2. Kategori / Varlık Türü Kataloğu & İstatistikleri (Tek Merkezden)
        $categoryCounts = ProjectAuditLog::select('entity_type', DB::raw('count(*) as count'))
            ->whereNotNull('entity_type')
            ->where('entity_type', '!=', '')
            ->groupBy('entity_type')
            ->pluck('count', 'entity_type')
            ->toArray();

        // SADECE log kaydı bulunan veya aktif filtrelenmiş kategorileri listele (Tek Merkezden)
        $activeCategoriesList = array_unique(array_merge(array_keys($categoryCounts), $selectedCategories));
        $categoryCatalog = [];
        foreach ($activeCategoriesList as $cat) {
            if (empty($cat)) continue;
            $categoryCatalog[$cat] = ProjectAuditLog::getCategoryMeta($cat);
        }

        // 3. Kullanıcılar & Log Sayıları (Yalnızca loglarda fiilen işlemi bulunan kullanıcılar)
        $userCounts = ProjectAuditLog::select('user_id', DB::raw('count(*) as count'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->pluck('count', 'user_id')
            ->toArray();

        $activeUserIds = array_unique(array_merge(
            array_keys($userCounts),
            array_filter(array_map('intval', $selectedUserIds))
        ));

        $allUsers = !empty($activeUserIds)
            ? User::whereIn('id', $activeUserIds)->orderBy('name')->get()
            : collect();

        // 4. Projeler & Log Sayıları (Yalnızca loglarda fiilen kaydı olan projeler)
        $projectCounts = ProjectAuditLog::select('project_id', DB::raw('count(*) as count'))
            ->whereNotNull('project_id')
            ->groupBy('project_id')
            ->pluck('count', 'project_id')
            ->toArray();
        $generalSystemCount = ProjectAuditLog::whereNull('project_id')->count();

        $activeProjectIds = array_unique(array_merge(
            array_keys($projectCounts),
            array_filter(array_map('intval', $selectedProjectIds))
        ));

        $allProjects = !empty($activeProjectIds)
            ? Project::whereIn('id', $activeProjectIds)->orderBy('code')->get()
            : collect();

        // Toplam filtrelenmemiş log sayısı
        $totalLogsCount = ProjectAuditLog::count();
        return view('admin.audit_logs', compact(
            'logs',
            'roles',
            'visibilitySettings',
            'allUsers',
            'allProjects',
            'actionCatalog',
            'actionCounts',
            'categoryCatalog',
            'categoryCounts',
            'userCounts',
            'projectCounts',
            'generalSystemCount',
            'totalLogsCount',
            'selectedActions',
            'selectedUserIds',
            'selectedProjectIds',
            'selectedCategories'
        ));
    }

    public function toggleVisibility(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'log_category' => 'required|string',
            'can_view' => 'required|boolean',
        ]);

        LogVisibilitySetting::updateOrCreate(
            ['role_id' => $request->role_id, 'log_category' => $request->log_category],
            ['can_view_logs' => $request->can_view]
        );

        AuditLogService::log(
            'updated',
            "Log Görünürlük Matrisi güncellendi: Rol ID #{$request->role_id}, Kategori '{$request->log_category}', Durum: " . ($request->can_view ? 'Açık' : 'Kapalı'),
            'LogVisibilitySetting',
            $request->role_id
        );

        return response()->json([
            'success' => true,
            'message' => 'Görünürlük ayarı güncellendi.'
        ]);
    }
}