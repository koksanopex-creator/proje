<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectPhase;
use App\Models\ProjectTask;
use App\Models\ProjectFile;
use App\Models\ProjectDiscussion;
use App\Models\ProjectStakeholder;
use App\Models\Bolum;
use App\Models\User;
use App\Models\Role;
use App\Models\ProjectCategory;
use App\Models\ProjectTeamRole;
use App\Models\ProjectBudgetCategory;
use App\Models\ProjectDraftShare;
use App\Models\Currency;
use App\Services\AuditLogService;
use App\Services\ProjectService;
use App\Services\ProjectPhaseService;
use App\Services\ProjectDraftService;
use App\Services\ProjectStakeholderService;
use App\Services\ProjectFileService;
use App\Services\DoaThresholdService;
use App\Services\CommitteeVotingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    protected ProjectService $projectService;
    protected ProjectPhaseService $phaseService;
    protected ProjectDraftService $draftService;
    protected ProjectStakeholderService $stakeholderService;
    protected ProjectFileService $fileService;

    public function __construct(
        ProjectService $projectService,
        ProjectPhaseService $phaseService,
        ProjectDraftService $draftService,
        ProjectStakeholderService $stakeholderService,
        ProjectFileService $fileService
    ) {
        $this->projectService = $projectService;
        $this->phaseService = $phaseService;
        $this->draftService = $draftService;
        $this->stakeholderService = $stakeholderService;
        $this->fileService = $fileService;
    }

    /**
     * Projeler listesi (Yetkilendirme, KPI istatistikleri, çoklu filtreleme ve dinamik tarih dayanaklı filtreleme destekli)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $baseQuery = Project::where('is_template', false)->forUser($user);

        // Dinamik Tarih Dayanağı ve Zaman Filtresi Mantığı
        $dateType = $request->get('date_type', 'created_at');
        $validDateColumns = ['created_at', 'start_date', 'target_end_date'];
        $dateColumn = in_array($dateType, $validDateColumns) ? $dateType : 'created_at';

        $timeFilter = $request->get('time_filter', 'all');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        if ($timeFilter === 'custom' && ($dateFrom || $dateTo)) {
            if ($dateFrom && $dateTo) {
                $baseQuery->whereBetween($dateColumn, [
                    \Carbon\Carbon::parse($dateFrom)->startOfDay(),
                    \Carbon\Carbon::parse($dateTo)->endOfDay()
                ]);
            } elseif ($dateFrom) {
                $baseQuery->where($dateColumn, '>=', \Carbon\Carbon::parse($dateFrom)->startOfDay());
            } elseif ($dateTo) {
                $baseQuery->where($dateColumn, '<=', \Carbon\Carbon::parse($dateTo)->endOfDay());
            }
        } elseif ($timeFilter && $timeFilter !== 'all') {
            switch ($timeFilter) {
                case 'this_month':
                    $baseQuery->whereBetween($dateColumn, [now()->startOfMonth(), now()->endOfMonth()]);
                    break;
                case 'this_quarter':
                    $baseQuery->whereBetween($dateColumn, [now()->startOfQuarter(), now()->endOfQuarter()]);
                    break;
                case 'this_year':
                    $baseQuery->whereYear($dateColumn, now()->year);
                    break;
                case 'last_year':
                    $baseQuery->whereYear($dateColumn, now()->subYear()->year);
                    break;
            }
        }

        // Tek bakışta Portföy KPI İstatistikleri (Kompakt ve bölüm izolasyonlu)
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'waiting_approval' => (clone $baseQuery)->whereIn('status', ['waiting_approval', 'pending', 'waiting_ehs', 'review'])->count(),
            'draft' => (clone $baseQuery)->where('status', 'draft')->count(),
            'completed' => (clone $baseQuery)->whereIn('status', ['completed', 'done'])->count(),
            'cancelled_on_hold' => (clone $baseQuery)->whereIn('status', ['cancelled', 'on_hold', 'blocked'])->count(),
        ];

        $query = (clone $baseQuery)->with(['bolum', 'manager', 'sponsor', 'waitingRole'])->withCount('stakeholders');

        // Çoklu Kategori Filtresi (Tekil veya Çoklu seçim destekli)
        $selectedCategories = $request->input('categories');
        if (empty($selectedCategories) && $request->filled('category')) {
            $selectedCategories = [$request->category];
        }
        if (!empty($selectedCategories) && is_array($selectedCategories)) {
            $selectedCategories = array_values(array_filter($selectedCategories));
            if (!empty($selectedCategories)) {
                $query->where(function ($q) use ($selectedCategories) {
                    $q->whereIn('category', $selectedCategories);
                    foreach ($selectedCategories as $catSlug) {
                        $q->orWhereJsonContains('categories', $catSlug);
                    }
                });
            }
        } else {
            $selectedCategories = [];
        }

        // Çoklu Durum Filtresi (Tekil veya Çoklu seçim destekli)
        $selectedStatuses = $request->input('statuses');
        if (empty($selectedStatuses) && $request->filled('status')) {
            $selectedStatuses = [$request->status];
        }
        if (!empty($selectedStatuses) && is_array($selectedStatuses)) {
            $selectedStatuses = array_values(array_filter($selectedStatuses));
            if (!empty($selectedStatuses)) {
                $expandedStatuses = [];
                foreach ($selectedStatuses as $st) {
                    if ($st === 'waiting_approval') {
                        $expandedStatuses = array_merge($expandedStatuses, ['waiting_approval', 'pending', 'waiting_ehs', 'review']);
                    } elseif ($st === 'completed') {
                        $expandedStatuses = array_merge($expandedStatuses, ['completed', 'done']);
                    } elseif ($st === 'cancelled_on_hold') {
                        $expandedStatuses = array_merge($expandedStatuses, ['cancelled', 'on_hold', 'blocked']);
                    } else {
                        $expandedStatuses[] = $st;
                    }
                }
                $query->whereIn('status', array_unique($expandedStatuses));
            }
        } else {
            $selectedStatuses = [];
        }

        if ($request->filled('bolum_id')) {
            $query->where('bolum_id', $request->bolum_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $projects = $query->latest('updated_at')->paginate(12)->withQueryString();
        $bolumler = Bolum::where('is_active', true)->get();
        $templates = Project::where('is_template', true)->get();
        $categories = ProjectCategory::where('is_active', true)->orderBy('name')->get();

        return view('projects.index', compact(
            'projects', 'bolumler', 'templates', 'categories', 'stats',
            'timeFilter', 'dateType', 'dateFrom', 'dateTo',
            'selectedCategories', 'selectedStatuses'
        ));
    }

    /**
     * Proje detay sayfası
     */
    public function show($project)
    {
        $id = $project instanceof Project ? $project->id : (int)$project;
        $project = Project::with([
            'bolum', 'manager', 'sponsor', 'waitingRole', 'waitingUser',
            'phases.tasks.assignee', 'stakeholders.user', 'stakeholders.handoverToUser', 'stakeholderPermissions',
            'risks.owner', 'risks.mitigations', 'boms', 'budgetItems', 'meetings.organizer', 'files.uploader',
            'qualities.checker', 'qualities.approver', 'nonConformances.assignee',
            'baselines.creator', 'activeBaseline.tasks',
            'discussions.user.bolum', 'auditLogs.user', 'auditLogs.actingAsUser', 'draftSuggestions.user',
            'draftShares.sharedBy', 'draftShares.revokedBy',
            'externalVendors.files.uploadedByUser', 'externalVendors.addedBy', 'externalVendors.approvedBy'
        ])->findOrFail($id);

        $user = auth()->user();

        if (!$this->stakeholderService->canAccessProject($project, $user)) {
            abort(403, 'Bu projeyi görüntüleme yetkiniz bulunmamaktadır.');
        }

        $isObserver = $user->isProjectObserver($project);
        $roles = Role::all();
        $users = User::where('is_active', true)->orderBy('name')->get();
        $observers = $project->stakeholders()->where('role_type', 'observer')->with('user')->get();

        $workflowPhasesData = $project->phases->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'sort_order' => $p->sort_order,
                'status' => $p->status,
                'requires_ehs_approval' => (bool)$p->requires_ehs_approval,
                'ehs_approved_at' => $p->ehs_approved_at ? $p->ehs_approved_at->format('d.m.Y H:i') : null,
                'tasks' => $p->tasks->map(function ($t) {
                    return [
                        'id' => $t->id,
                        'title' => $t->title,
                        'status' => $t->status,
                        'priority' => $t->priority,
                        'tag' => $t->tag,
                        'estimated_hours' => $t->estimated_hours,
                        'due_date' => $t->due_date ? $t->due_date->format('d.m.Y') : null,
                        'parent_id' => $t->parent_id,
                        'assignee' => $t->assignee ? [
                            'id' => $t->assignee->id,
                            'name' => $t->assignee->name,
                            'title' => $t->assignee->unvan ?? 'Personel'
                        ] : null,
                    ];
                })->values()
            ];
        })->values();

        $canManage = $this->canManageProject($project, $user);
        $activeProjectDelegations = $project->getActiveStakeholderDelegations();
        $pastProjectDelegations = $project->getPastStakeholderDelegations();
        $evmMetrics = $project->activeBaseline ? $project->activeBaseline->calculateEvm() : null;

        return view('projects.show', compact('project', 'roles', 'users', 'isObserver', 'observers', 'workflowPhasesData', 'canManage', 'activeProjectDelegations', 'pastProjectDelegations', 'evmMetrics'));
    }

    /**
     * Yeni proje başlatma ekranı
     */
    public function create()
    {
        if (!auth()->user()->hasPermission('proje.create')) {
            abort(403, 'Yeni proje başlatma yetkiniz bulunmamaktadır.');
        }

        $bolumler = Bolum::where('is_active', true)->get();
        $users = User::where('is_active', true)->orderBy('name')->get();
        $templates = Project::where('is_template', true)->with(['phases.tasks', 'risks', 'boms'])->get();
        $categories = ProjectCategory::where('is_active', true)->orderBy('name')->get();
        $teamRoles = ProjectTeamRole::where('is_active', true)->orderBy('sort_order')->get();
        $currencies = Currency::where('is_active', true)->orderBy('sort_order')->get();
        $budgetCategories = ProjectBudgetCategory::with(['parent', 'children' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('name')])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('projects.create', compact('bolumler', 'users', 'templates', 'categories', 'teamRoles', 'currencies', 'budgetCategories'));
    }

    /**
     * Yeni proje kaydı (Servis katmanı delegasyonu)
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('proje.create')) {
            abort(403, 'Yeni proje başlatma yetkiniz bulunmamaktadir.');
        }

        $isDraft = $request->boolean('is_draft') || $request->input('action_type') === 'draft';

        // Sayısal bütçe normalizasyonu
        $this->normalizeBudgetInputs($request);

        // Kapsam Araçları Normalizasyonu (JSON string veya Array)
        if ($request->filled('scope_tools_json') && is_string($request->input('scope_tools_json'))) {
            $decoded = json_decode($request->input('scope_tools_json'), true);
            if (is_array($decoded)) {
                $request->merge(['scope_tools' => $decoded]);
            }
        } elseif ($request->has('scope_tools') && is_string($request->input('scope_tools'))) {
            $decoded = json_decode($request->input('scope_tools'), true);
            if (is_array($decoded)) {
                $request->merge(['scope_tools' => $decoded]);
            }
        }

        // Proje Etiketleri Normalizasyonu (tags_json string -> tags array)
        if ($request->filled('tags_json') && is_string($request->input('tags_json'))) {
            $decodedTags = json_decode($request->input('tags_json'), true);
            if (is_array($decodedTags)) {
                $request->merge(['tags' => array_values(array_filter(array_map('trim', $decodedTags)))]);
            }
        }

        if ($isDraft) {
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'category' => 'nullable|string',
                'categories' => 'nullable|array',
                'priority' => 'nullable|string',
                'location' => 'nullable|string',
                'bolum_id' => 'nullable',
                'bolum_ids' => 'nullable|array',
                'manager_id' => 'nullable|exists:users,id',
                'sponsor_id' => 'nullable|exists:users,id',
                'planned_budget' => 'nullable|numeric',
                'currency' => 'nullable|string|max:5',
                'visibility' => 'nullable|string|in:public,private',
                'notification_settings' => 'nullable|array',
                'scope_tools' => 'nullable|array',
                'tags' => 'nullable|array',
                'start_date' => 'nullable|date',
                'target_end_date' => 'nullable|date',
                'description' => 'nullable|string',
                'template_id' => 'nullable|exists:projects,id',
                'team_members' => 'nullable|array',
                'observers' => 'nullable|array',
                'external_observers' => 'nullable|array',
                'budget_items' => 'nullable|array',
                'milestones' => 'nullable|array',
            ]);
            $data['priority'] = $data['priority'] ?? 'medium';
            $data['currency'] = $data['currency'] ?? 'TRY';
            $data['status'] = 'draft';
        } else {
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'category' => 'nullable|string',
                'categories' => 'nullable|array',
                'priority' => 'required|string',
                'location' => 'nullable|string',
                'bolum_id' => 'nullable',
                'bolum_ids' => 'nullable|array',
                'manager_id' => 'nullable|exists:users,id',
                'sponsor_id' => 'nullable|exists:users,id',
                'planned_budget' => 'nullable|numeric',
                'currency' => 'required|string|max:5',
                'visibility' => 'nullable|string|in:public,private',
                'notification_settings' => 'nullable|array',
                'scope_tools' => 'nullable|array',
                'tags' => 'nullable|array',
                'start_date' => 'nullable|date',
                'target_end_date' => 'nullable|date',
                'description' => 'nullable|string',
                'template_id' => 'nullable|exists:projects,id',
                'team_members' => 'nullable|array',
                'observers' => 'nullable|array',
                'external_observers' => 'nullable|array',
                'budget_items' => 'nullable|array',
                'milestones' => 'nullable|array',
            ]);
            $data['status'] = 'pending';
        }

        $user = auth()->user();
        $data['created_by'] = $user->id;
        if (empty($data['bolum_id'])) {
            if (!empty($data['bolum_ids']) && is_array($data['bolum_ids'])) {
                $data['bolum_id'] = (int)$data['bolum_ids'][0];
            } else {
                $data['bolum_id'] = $user->bolum_id;
            }
        }

        // Bütçe Güvencesi (SQL 1048 Column 'planned_budget' cannot be null hatası önleme)
        $data['planned_budget'] = !empty($data['planned_budget']) ? (float)$data['planned_budget'] : 0.00;
        $data['currency'] = !empty($data['currency']) ? $data['currency'] : 'TRY';

        // Proje Kodu Üretimi
        $prefix = 'PRJ-' . date('Y') . '-';
        $lastProject = Project::where('code', 'like', "{$prefix}%")->orderByDesc('id')->first();
        $nextNum = 1;
        if ($lastProject && preg_match('/-(\d+)$/', $lastProject->code, $m)) {
            $nextNum = ((int)$m[1]) + 1;
        }
        $data['code'] = $prefix . str_pad((string)$nextNum, 4, '0', STR_PAD_LEFT);

        // Kategori Güvencesi (SQL 1364 default value hatası önleme)
        if (empty($data['category'])) {
            if (!empty($data['categories']) && is_array($data['categories'])) {
                $data['category'] = $data['categories'][0] ?? 'genel';
            } else {
                $data['category'] = 'genel';
            }
        }

        // Yaşam Döngüsü Zaman Damgaları & Taslak Geçmişi
        $now = now();
        if ($isDraft) {
            $data['draft_created_at'] = $now;
            $data['draft_updated_at'] = $now;
            $data['draft_history'] = [[
                'action' => 'draft_created',
                'user_id' => $user->id,
                'user_name' => $user->name,
                'timestamp' => $now->toIso8601String(),
                'note' => 'Taslak ilk kez oluşturuldu'
            ]];
        } else {
            $data['submitted_for_approval_at'] = $now;
        }

        // Şablondan kopyalama veya normal oluşturma
        if (!empty($data['template_id'])) {
            $project = $this->projectService->createFromTemplate((int)$data['template_id'], $data);
        } else {
            $project = Project::create($data);
        }

        // Bütçe Kalemleri
        if ($request->filled('budget_items') && is_array($request->input('budget_items'))) {
            $totalBudget = 0;
            foreach ($request->input('budget_items') as $bItem) {
                if (!empty($bItem['title']) && isset($bItem['planned_amount']) && (float)$bItem['planned_amount'] > 0) {
                    $itemAmount = (float)$bItem['planned_amount'];
                    $totalBudget += $itemAmount;
                    $catName = trim($bItem['category'] ?? 'Genel');
                    $subCatName = !empty($bItem['sub_category']) ? trim($bItem['sub_category']) : null;
                    $catModel = ProjectBudgetCategory::where('name', $catName)->first();
                    $subCatModel = $subCatName ? ProjectBudgetCategory::where('name', $subCatName)->first() : null;

                    $project->budgetItems()->create([
                        'title' => trim($bItem['title']),
                        'planned_amount' => $itemAmount,
                        'actual_spent' => 0,
                        'committed_amount' => 0,
                        'currency' => $data['currency'] ?? 'TRY',
                        'category' => $catName,
                        'sub_category' => $subCatName,
                        'category_id' => $catModel?->id,
                        'sub_category_id' => $subCatModel?->id,
                    ]);
                }
            }
            if ($totalBudget > 0) {
                $project->update(['planned_budget' => $totalBudget]);
            }
        }

        // Fazlar & Kilometre Taşları (ProjectPhaseService delegasyonu)
        if ($request->has('milestones') && is_array($request->input('milestones')) && count($request->input('milestones')) > 0) {
            $this->phaseService->saveMilestonesToProject($project, $request->input('milestones'));
        } elseif (empty($data['template_id'])) {
            $this->phaseService->createDefaultPhasesForCategory($project, $project->category ?? '');
        }

        // Ekip Üyeleri & Paydaşlar
        if (!empty($data['team_members'])) {
            foreach ($data['team_members'] as $tm) {
                if (!empty($tm['user_id'])) {
                    $project->stakeholders()->create([
                        'user_id' => $tm['user_id'],
                        'role_type' => 'team',
                        'notes' => $tm['responsibility'] ?? 'Ekip Üyesi',
                        'responsibility' => $tm['responsibility'] ?? 'Ekip Üyesi',
                    ]);
                }
            }
        }

        // Gözlemciler
        if (!empty($data['observers'])) {
            foreach ($data['observers'] as $obsUserId) {
                $this->stakeholderService->addObserver($project, (int)$obsUserId, 'Proje oluşturulurken eklendi', $user);
            }
        }

        // Ek Dosyalar Yükleme (ProjectFileService delegasyonu & Etiket Desteği)
        if ($request->hasFile('attachments')) {
            $attachmentTags = $request->input('attachment_tags', []);
            if ($request->filled('attachment_tags_json')) {
                $attachmentTags = json_decode($request->input('attachment_tags_json'), true) ?: $attachmentTags;
            }
            foreach ($request->file('attachments') as $idx => $file) {
                if ($file && $file->isValid()) {
                    $rawTags = $attachmentTags[$idx] ?? [];
                    if (is_string($rawTags)) {
                        $rawTags = json_decode($rawTags, true) ?: array_filter(array_map('trim', explode(',', $rawTags)));
                    }
                    $this->fileService->uploadFile($project, $file, [
                        'category' => 'kapsam_belgesi',
                        'tags' => (array)$rawTags
                    ], $user);
                }
            }
        }

        AuditLogService::log(
            'created',
            "Yeni proje başlatıldı: **{$project->title}** ({$project->code})",
            'Project',
            $project->id,
            $project->id
        );

        return redirect()->route('projects.show', $project->id)
            ->with('success', "'{$project->title}' projesi başarıyla kaydedildi.");
    }

    /**
     * Proje düzenleme ekranı
     */
    public function edit(Project $project)
    {
        if (!$this->canManageProject($project)) {
            abort(403, 'Bu projeyi düzenleme yetkiniz bulunmamaktadır.');
        }

        $project->load(['stakeholders.user', 'budgetItems', 'manager', 'sponsor', 'bolum']);

        $bolumler = Bolum::where('is_active', true)->get();
        $users = User::where('is_active', true)->orderBy('name')->get();
        $categories = ProjectCategory::where('is_active', true)->orderBy('name')->get();
        $teamRoles = ProjectTeamRole::where('is_active', true)->orderBy('sort_order')->get();
        $currencies = Currency::where('is_active', true)->orderBy('sort_order')->get();
        $budgetCategories = ProjectBudgetCategory::with(['parent', 'children' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('name')])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        $templates = Project::where('is_template', true)->with('phases.tasks', 'risks')->get();

        return view('projects.edit', compact(
            'project', 'bolumler', 'users', 'categories', 'teamRoles', 'currencies', 'budgetCategories', 'templates'
        ));
    }

    /**
     * Proje güncelleme
     */
    public function update(Request $request, Project $project)
    {
        if (!$this->canManageProject($project)) {
            abort(403, 'Bu projeyi düzenleme yetkiniz bulunmamaktadır.');
        }

        $this->normalizeBudgetInputs($request);

        // Kapsam Araçları Normalizasyonu (JSON string veya Array)
        if ($request->filled('scope_tools_json') && is_string($request->input('scope_tools_json'))) {
            $decoded = json_decode($request->input('scope_tools_json'), true);
            if (is_array($decoded)) {
                $request->merge(['scope_tools' => $decoded]);
            }
        } elseif ($request->has('scope_tools') && is_string($request->input('scope_tools'))) {
            $decoded = json_decode($request->input('scope_tools'), true);
            if (is_array($decoded)) {
                $request->merge(['scope_tools' => $decoded]);
            }
        }

        // Proje Etiketleri Normalizasyonu (tags_json string -> tags array)
        if ($request->filled('tags_json') && is_string($request->input('tags_json'))) {
            $decodedTags = json_decode($request->input('tags_json'), true);
            if (is_array($decodedTags)) {
                $request->merge(['tags' => array_values(array_filter(array_map('trim', $decodedTags)))]);
            }
        }

        $request->merge([
            'code' => $request->input('code', $project->code),
            'status' => $request->input('action_type') === 'draft' ? 'draft' : $request->input('status', $project->status),
        ]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:projects,code,' . $project->id,
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'categories' => 'nullable|array',
            'bolum_id' => 'nullable',
            'bolum_ids' => 'nullable|array',
            'manager_id' => 'nullable|exists:users,id',
            'sponsor_id' => 'nullable|exists:users,id',
            'status' => 'required|string',
            'priority' => 'required|string',
            'planned_budget' => 'nullable|numeric',
            'currency' => 'required|string|max:5',
            'scope_tools' => 'nullable|array',
            'start_date' => 'nullable|date',
            'target_end_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date',
            'team_members' => 'nullable|array',
            'budget_items' => 'nullable|array',
            'milestones' => 'nullable|array',
            'tags' => 'nullable|array',
            'observers' => 'nullable|array',
            'external_observers' => 'nullable|array',
            'deleted_attachment_ids' => 'nullable|array',
        ]);

        $validated['planned_budget'] = !empty($validated['planned_budget']) ? (float)$validated['planned_budget'] : 0.00;
        if (empty($validated['bolum_id'])) {
            if (!empty($validated['bolum_ids']) && is_array($validated['bolum_ids'])) {
                $validated['bolum_id'] = (int)$validated['bolum_ids'][0];
            }
        }

        // Silinen dosyalar
        if ($request->filled('deleted_attachment_ids')) {
            foreach ($request->input('deleted_attachment_ids') as $delId) {
                $f = ProjectFile::where('project_id', $project->id)->find($delId);
                if ($f) {
                    $this->fileService->deleteFile($project, $f, auth()->user());
                }
            }
        }

        // Mevcut dosyaların etiket güncellemeleri
        if ($request->filled('existing_file_tags') && is_array($request->input('existing_file_tags'))) {
            foreach ($request->input('existing_file_tags') as $fId => $fTags) {
                $f = ProjectFile::where('project_id', $project->id)->find($fId);
                if ($f) {
                    $this->fileService->updateTags($project, $f, $fTags, auth()->user());
                }
            }
        }

        // Yeni dosyalar (Etiket Desteği ile)
        if ($request->hasFile('attachments')) {
            $attachmentTags = $request->input('attachment_tags', []);
            if ($request->filled('attachment_tags_json')) {
                $attachmentTags = json_decode($request->input('attachment_tags_json'), true) ?: $attachmentTags;
            }
            foreach ($request->file('attachments') as $idx => $file) {
                if ($file && $file->isValid()) {
                    $rawTags = $attachmentTags[$idx] ?? [];
                    if (is_string($rawTags)) {
                        $rawTags = json_decode($rawTags, true) ?: array_filter(array_map('trim', explode(',', $rawTags)));
                    }
                    $this->fileService->uploadFile($project, $file, [
                        'category' => 'kapsam_belgesi',
                        'tags' => (array)$rawTags
                    ], auth()->user());
                }
            }
        }

        $this->projectService->updateProject($project, $validated, auth()->user());

        if ($request->has('milestones') && is_array($request->input('milestones')) && count($request->input('milestones')) > 0) {
            $project->phases()->delete();
            $this->phaseService->saveMilestonesToProject($project, $request->input('milestones'));
        }

        return redirect()->route('projects.show', $project->id)
            ->with('success', "'{$project->title}' projesi başarıyla güncellendi.");
    }

    /**
     * Proje silme (SoftDeletes)
     */
    public function destroy(Project $project)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->isDeveloper() && !$user->hasPermission('proje.delete')) {
            abort(403, 'Projeyi silme yetkiniz bulunmamaktadır.');
        }

        $this->projectService->deleteProject($project, $user);

        return redirect()->route('projects.index')
            ->with('success', "'{$project->title}' projesi silindi.");
    }

    /**
     * Şablon JSON çıktısı
     */
    public function getTemplateJson($id)
    {
        $tpl = Project::where('is_template', true)->with(['phases.tasks', 'risks', 'boms'])->findOrFail($id);
        return response()->json($tpl);
    }

    /**
     * Proje gizlilik/görünürlük geçişi
     */
    public function toggleVisibility(Request $request, Project $project)
    {
        $project = $this->stakeholderService->toggleVisibility($project, auth()->user());
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_confidential' => (bool)$project->is_confidential,
                'message' => $project->is_confidential ? 'Proje gizli (confidential) yapıldı.' : 'Proje herkese açık hale getirildi.'
            ]);
        }
        return back()->with('success', 'Proje görünürlük ayarı güncellendi.');
    }

    /**
     * Gözlemci ekleme
     */
    public function addObserver(Request $request, $project)
    {
        $project = Project::findOrFail($project);
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:500',
        ]);
        $stakeholder = $this->stakeholderService->addObserver($project, (int)$data['user_id'], $data['notes'] ?? null, auth()->user());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Gözlemci başarıyla eklendi.',
                'stakeholder' => $stakeholder->load('user')
            ]);
        }
        return back()->with('success', 'Gözlemci başarıyla eklendi.');
    }

    /**
     * Gözlemci çıkarma
     */
    public function removeObserver(Request $request, $project, $stakeholder)
    {
        $project = Project::findOrFail($project);
        $this->stakeholderService->removeObserver($project, (int)$stakeholder, auth()->user());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Gözlemci projeden çıkarıldı.']);
        }
        return back()->with('success', 'Gözlemci projeden çıkarıldı.');
    }

    /**
     * Proje yönetici yetkisi kontrolü
     */
    public function canManageProject(Project $project, $user = null): bool
    {
        return $this->stakeholderService->canManageProject($project, $user ?: auth()->user());
    }

    // ==========================================
    // FAZ / ADIM & İSG ONAY KAPISI METODLARI
    // ==========================================

    public function addPhase(Request $request, $project)
    {
        $project = Project::findOrFail($project);
        if (!$this->stakeholderService->canAccessProject($project, auth()->user())) {
            abort(403);
        }
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'requires_ehs_approval' => 'nullable|boolean',
        ]);
        $phase = $this->phaseService->addPhase($project, $data, auth()->user());
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'phase' => $phase]);
        }
        return back()->with('success', 'Yeni faz eklendi.');
    }

    public function completePhase(Request $request, $project, $phase)
    {
        $project = Project::findOrFail($project);
        $phase = ProjectPhase::findOrFail($phase);
        try {
            $res = $this->phaseService->completePhase($project, $phase, auth()->user());
            $msg = $res['message'] ?? "'{$phase->name}' adımı başarıyla tamamlandı.";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(array_merge(['success' => true, 'message' => $msg], $res));
            }
            return back()->with('success', $msg);
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function revertPhase(Request $request, $project, $phase)
    {
        $project = Project::findOrFail($project);
        $phase = ProjectPhase::findOrFail($phase);
        try {
            $res = $this->phaseService->revertPhase($project, $phase, auth()->user());
            $msg = $res['message'] ?? "'{$phase->name}' adımı geri alındı.";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(array_merge(['success' => true, 'message' => $msg], $res));
            }
            return back()->with('success', $msg);
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function approveEhsGate(Request $request, $project, $phase)
    {
        $project = Project::findOrFail($project);
        $phase = ProjectPhase::findOrFail($phase);
        $this->phaseService->approveEhsGate($project, $phase, auth()->user());
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'İSG Onay Kapısı onaylandı.']);
        }
        return back()->with('success', 'İSG Onay Kapısı onaylandı.');
    }

    public function revertEhsGate(Request $request, $project, $phase)
    {
        $project = Project::findOrFail($project);
        $phase = ProjectPhase::findOrFail($phase);
        $this->phaseService->revertEhsGate($project, $phase, auth()->user());
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'İSG Onay Kapısı onayı geri alındı.']);
        }
        return back()->with('success', 'İSG Onay Kapısı onayı geri alındı.');
    }

    // ==========================================
    // GÖREVLER (TASKS) METODLARI
    // ==========================================

    public function addTask(Request $request, Project $project)
    {
        $user = auth()->user();

        // Kural 19: Yönetim rolü kesinlikle görev ekleyemez
        if ($user->isYonetim()) {
            abort(403, 'Yönetim (Read-Only) rolü görev ekleyemez.');
        }

        // Dinamik Yetki Kontrolü: task.create veya task.manage
        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->hasPermission('task.create') && !$user->hasPermission('task.manage')) {
            abort(403, 'Dinamik Yetki Matrisi kuralı: Görev oluşturma yetkiniz bulunmamaktadır.');
        }

        if (!$this->canManageProject($project)) {
            abort(403, 'Bu projeye görev ekleme yetkiniz bulunmamaktadır.');
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'phase_id' => 'required|exists:project_phases,id',
            'parent_id' => 'nullable|exists:project_tasks,id',
            'assigned_to' => 'nullable',
            'assigned_user_ids' => 'nullable|array',
            'priority' => 'required|in:low,medium,high,critical',
            'tag' => 'nullable|string|max:50',
            'estimated_hours' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
            'is_milestone' => 'nullable|boolean',
        ]);

        $rawAssignees = $request->input('assigned_to', []);
        if (!is_array($rawAssignees)) {
            $rawAssignees = $rawAssignees ? [$rawAssignees] : [];
        }
        $rawAdditional = $request->input('assigned_user_ids', []);
        if (!is_array($rawAdditional)) {
            $rawAdditional = [];
        }

        $assigneeIds = collect($rawAssignees)
            ->merge($rawAdditional)
            ->filter()
            ->map(fn($id) => (int)$id)
            ->unique()
            ->values()
            ->toArray();

        // Personel Atama Yetkisi Kontrolü (task.assign veya task.manage)
        if (!empty($assigneeIds)) {
            if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->hasPermission('task.assign') && !$user->hasPermission('task.manage')) {
                abort(403, 'Dinamik Yetki Matrisi kuralı: Göreve personel atama yetkiniz bulunmamaktadır.');
            }
        }

        $data['project_id'] = $project->id;
        $data['status'] = $request->input('status', 'todo');
        $data['created_by'] = auth()->id();
        $data['is_milestone'] = $request->has('is_milestone');
        $data['assigned_to'] = $assigneeIds[0] ?? null;
        $data['estimated_hours'] = isset($data['estimated_hours']) ? (float)$data['estimated_hours'] : 0.00;
        $data['spent_hours'] = 0.00;
        $data['progress_percentage'] = 0;

        $task = ProjectTask::create($data);

        if (!empty($assigneeIds)) {
            $task->assignees()->sync($assigneeIds);
        }

        if (!empty($data['parent_id'])) {
            \App\Models\ProjectTaskDependency::firstOrCreate([
                'task_id' => $task->id,
                'depends_on_task_id' => $data['parent_id'],
                'dependency_type' => 'finish_to_start',
            ]);
        }

        $assigneeNames = $task->assignees()->pluck('name')->implode(', ');
        $assigneeText = $assigneeNames ? " (Atanan Personeller: **{$assigneeNames}**)" : "";

        AuditLogService::log(
            'task_created',
            "Proje aşamasına yeni görev eklendi: '**{$task->title}**'{$assigneeText}",
            'ProjectTask',
            $task->id,
            $project->id,
            null,
            ['status' => $task->status, 'assignees' => $assigneeIds]
        );

        return back()->with('success', "Yeni görev '**{$task->title}**' başarıyla eklendi.");
    }

    public function deleteTask(Project $project, ProjectTask $task)
    {
        if (!$this->canManageProject($project) && $task->created_by !== auth()->id()) {
            abort(403, 'Bu görevi silme yetkiniz bulunmamaktadır.');
        }

        $title = $task->title;
        $task->delete();

        AuditLogService::log(
            'task_deleted',
            "'**{$title}**' görevi projeden silindi.",
            'ProjectTask',
            $task->id,
            $project->id
        );

        return back()->with('success', "'{$title}' görevi silindi.");
    }

    public function updateTaskStatus(Request $request, Project $project, ProjectTask $task)
    {
        if ($project->isDraft() || $project->status === 'waiting_approval') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Taslak veya onay sürecindeki bir projede görev durumu değiştirilemez. Lütfen önce projeyi yayınlayın.'], 422);
            }
            return back()->with('error', 'Taslak veya onay sürecindeki bir projede görev durumu değiştirilemez. Lütfen önce projeyi yayınlayın.');
        }

        $data = $request->validate([
            'status' => 'required|in:todo,in_progress,review,done',
        ]);
        $task->update([
            'status' => $data['status'],
            'progress_percentage' => $data['status'] === 'done' ? 100 : $task->progress_percentage,
        ]);
        $this->phaseService->recalculateOverallProgress($project);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'task' => $task]);
        }
        return back()->with('success', 'Görev durumu güncellendi.');
    }

    // ==========================================
    // DOSYA YÖNETİMİ & ETİKETLEME METODLARI
    // ==========================================

    public function uploadFile(Request $request, Project $project)
    {
        $request->validate([
            'file' => 'required|file|max:25600',
            'category' => 'nullable|string|max:100',
            'tags' => 'nullable',
        ]);

        $projectFile = $this->fileService->uploadFile(
            $project,
            $request->file('file'),
            $request->only(['category', 'tags']),
            auth()->user()
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Dosya başarıyla yüklendi.',
                'file' => $projectFile,
                'tags' => $projectFile->tags_array,
                'download_url' => route('projects.download_file', [$project->id, $projectFile->id])
            ]);
        }

        return back()->with('success', "'{$projectFile->original_name}' başarıyla yüklendi.");
    }

    public function updateFileTags(Request $request, Project $project, ProjectFile $file)
    {
        $request->validate(['tags' => 'nullable']);
        $updated = $this->fileService->updateTags($project, $file, $request->input('tags', []), auth()->user());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Dosya etiketleri güncellendi.',
                'tags' => $updated->tags_array,
            ]);
        }

        return back()->with('success', 'Dosya etiketleri güncellendi.');
    }

    public function downloadFile(Project $project, ProjectFile $file)
    {
        return $this->fileService->downloadFile($project, $file, auth()->user());
    }

    public function deleteFile(Project $project, ProjectFile $file)
    {
        $fileName = $file->original_name;
        $this->fileService->deleteFile($project, $file, auth()->user());

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => "'{$fileName}' silindi."]);
        }

        return back()->with('success', "'{$fileName}' silindi.");
    }

    // ==========================================
    // TASLAK PAYLAŞIMI & ÖNERİ METODLARI
    // ==========================================

    public function shareDraft(Request $request, Project $project)
    {
        $request->validate([
            'reviewer_ids' => 'nullable|array',
            'reviewer_ids.*' => 'exists:users,id',
            'external_emails' => 'nullable|string|max:500',
            'note' => 'nullable|string|max:1000',
        ]);

        $reviewerIds = $request->input('reviewer_ids', []);
        $externalEmails = trim((string)$request->input('external_emails'));

        if (empty($reviewerIds) && empty($externalEmails)) {
            return back()->with('error', 'Lütfen en az bir dahili paydaş veya harici e-posta adresi belirtiniz.');
        }

        $res = $this->draftService->shareDraft(
            $project,
            $reviewerIds,
            $externalEmails,
            $request->input('note'),
            auth()->user()
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($res);
        }

        return back()->with('success', $res['message']);
    }

    public function revokeDraftShare(Request $request, Project $project, ProjectDraftShare $share)
    {
        $user = auth()->user();
        $isAuthorized = $user->isSuperAdmin() || $user->isDeveloper() || $user->isAdmin() ||
                        $this->canManageProject($project) ||
                        (int)$share->shared_by_user_id === (int)$user->id;

        if (!$isAuthorized) {
            abort(403, 'Bu harici erişim yetkisini iptal etme izniniz bulunmamaktadır.');
        }

        if ((int)$share->project_id !== (int)$project->id) {
            abort(403, 'Erişim Engellendi: Bu paylaşım kaydı ilgili projeye ait değildir.');
        }

        $this->draftService->revokeDraftExternalShare($share, $user);

        return back()->with('success', "'{$share->email}' için harici erişim yetkisi başarıyla iptal edildi.");
    }

    public function externalDraftReview(Request $request, Project $project)
    {
        $token = $request->query('token');
        $passkey = $request->query('passkey');

        if (!$token && !$passkey) {
            abort(404);
        }

        $share = null;
        if ($token) {
            $share = ProjectDraftShare::where('token', $token)->first();
        }
        if (!$share && $passkey) {
            $share = ProjectDraftShare::where('passkey', strtoupper(trim($passkey)))->first();
        }

        if (!$share || !$share->isActive()) {
            abort(404);
        }

        // KATI GÜVENLİK KURALI: Başka projeye ID değiştirerek erişim denemesi doğrudan 404 döner!
        if ((int)$share->project_id !== (int)$project->id) {
            abort(404);
        }

        // Erişim sayacı ve son erişim zamanı
        $share->increment('access_count');
        $share->update(['last_accessed_at' => now()]);

        $project->load([
            'bolum', 'manager', 'creator', 'sponsor',
            'phases.tasks', 'budgetItems', 'files',
            'draftSuggestions.user', 'risks.mitigations', 'stakeholders.user'
        ]);

        return view('projects.external_draft_review', compact('project', 'share'));
    }

    public function downloadExternalFile(Request $request, Project $project, ProjectFile $file)
    {
        $token = $request->query('token');
        $passkey = $request->query('passkey');

        if (!$token && !$passkey) {
            abort(404);
        }

        $share = null;
        if ($token) {
            $share = ProjectDraftShare::where('token', $token)->first();
        }
        if (!$share && $passkey) {
            $share = ProjectDraftShare::where('passkey', strtoupper(trim($passkey)))->first();
        }

        if (!$share || !$share->isActive() || (int)$share->project_id !== (int)$project->id) {
            abort(404);
        }

        if ((int)$file->project_id !== (int)$project->id) {
            abort(404);
        }

        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($file->storage_path)) {
            abort(404, 'Dosya sunucu diskinde bulunamadı.');
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download($file->storage_path, $file->original_name);
    }

    public function storeExternalDraftSuggestion(Request $request, Project $project)
    {
        $token = $request->input('token');
        $passkey = $request->input('passkey');

        if (!$token && !$passkey) {
            abort(404);
        }

        $share = null;
        if ($token) {
            $share = ProjectDraftShare::where('token', $token)->first();
        }
        if (!$share && $passkey) {
            $share = ProjectDraftShare::where('passkey', strtoupper(trim($passkey)))->first();
        }

        if (!$share || !$share->isActive() || (int)$share->project_id !== (int)$project->id) {
            abort(404);
        }

        $data = $request->validate([
            'field_key' => 'required|string|max:50',
            'field_label' => 'required|string|max:150',
            'suggestion_text' => 'required|string|max:1500',
        ]);

        $this->draftService->storeDraftSuggestion(
            $project,
            $data,
            null,
            $share->email
        );

        return back()->with('success', 'Görüş ve öneriniz proje ekibine başarıyla iletildi. Katkınız için teşekkür ederiz.');
    }

    public function storeDraftSuggestion(Request $request, Project $project)
    {
        $request->validate([
            'field_key' => 'required|string|max:50',
            'field_label' => 'required|string|max:150',
            'suggestion_text' => 'required|string|max:1500',
        ]);

        $suggestion = $this->draftService->storeDraftSuggestion(
            $project,
            $request->only(['field_key', 'field_label', 'suggestion_text']),
            auth()->user()
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Öneriniz başarıyla kaydedildi.',
                'suggestion' => $suggestion->load('user'),
            ]);
        }

        return back()->with('success', 'Öneriniz başarıyla kaydedildi.');
    }

    public function publishDraft(Request $request, Project $project)
    {
        $this->draftService->publishDraft($project, auth()->user());

        return redirect()->route('projects.show', $project->id)
            ->with('success', "'{$project->title}' projesi başarıyla yayına alındı ve onay sürecine girdi.");
    }

    // ==========================================
    // TARTIŞMA & ONAY AKIŞI METODLARI
    // ==========================================

    public function storeDiscussion(Request $request, Project $project)
    {
        $data = $request->validate([
            'message' => 'required|string|max:2000',
        ]);
        $discussion = $project->discussions()->create([
            'user_id' => auth()->id(),
            'message' => $data['message'],
        ]);
        AuditLogService::log('discussion_added', "Projeye yeni tartışma yorumu eklendi.", 'ProjectDiscussion', $discussion->id, $project->id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'discussion' => $discussion->load('user.bolum')]);
        }
        return back()->with('success', 'Yorumunuz eklendi.');
    }

    public function updateDiscussion(Request $request, Project $project, ProjectDiscussion $discussion)
    {
        if ($discussion->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }
        $data = $request->validate(['message' => 'required|string|max:2000']);
        $discussion->update(['message' => $data['message']]);

        return response()->json(['success' => true, 'discussion' => $discussion]);
    }

    public function deleteDiscussion(Project $project, ProjectDiscussion $discussion)
    {
        if ($discussion->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }
        $discussion->delete();
        return response()->json(['success' => true]);
    }

    public function submitForApproval(Request $request, Project $project)
    {
        /** @var DoaThresholdService $doaService */
        $doaService = app(DoaThresholdService::class);
        $approvalRequest = $doaService->submitProjectForApproval($project, auth()->user());

        if ($approvalRequest->status === 'bypassed_auto') {
            return back()->with('success', 'Proje bütçesi hızlı onay eşiğinde olduğu için doğrudan devreye alındı (Devam Ediyor).');
        }

        if ($approvalRequest->target_approver_type === 'committee' && $approvalRequest->targetCommittee) {
            return back()->with('info', "Proje '{$approvalRequest->targetCommittee->name}' kurulunun oylamasına sunuldu. Oylama süreci başlatıldı.");
        }

        return back()->with('success', 'Proje başarıyla bütçe onay sürecine iletildi.');
    }

    public function approve(Request $request, Project $project)
    {
        $latestReq = $project->latestApprovalRequest;
        if ($latestReq && $latestReq->status === 'pending') {
            $latestReq->update([
                'status' => 'approved',
                'resolved_at' => now(),
                'resolved_by' => auth()->id(),
                'decision_notes' => $request->input('notes', 'Yetkili tarafından bütçe onayı verildi.'),
            ]);
        }

        $project->update([
            'status' => 'in_progress',
            'approved_at' => now(),
            'current_waiting_role_id' => null,
            'current_waiting_user_id' => null,
            'waiting_since' => null,
            'waiting_step_name' => null,
        ]);

        AuditLogService::log('approved', "Proje onaylandı ve devreye alındı.", 'Project', $project->id, $project->id);
        return back()->with('success', 'Proje onaylandı ve devreye alındı.');
    }

    public function revertApproval(Request $request, Project $project)
    {
        $latestReq = $project->latestApprovalRequest;
        if ($latestReq && $latestReq->status === 'pending') {
            $latestReq->update([
                'status' => 'rejected',
                'resolved_at' => now(),
                'resolved_by' => auth()->id(),
                'decision_notes' => 'Onay geri çekildi / taslağa döndürüldü.',
            ]);
        }

        $project->update([
            'status' => 'draft',
            'rejected_at' => now(),
            'current_waiting_role_id' => null,
            'current_waiting_user_id' => null,
            'waiting_since' => null,
            'waiting_step_name' => null,
        ]);

        AuditLogService::log('approval_reverted', "Proje onayı geri alındı ve taslak duruma getirildi.", 'Project', $project->id, $project->id);
        return back()->with('success', 'Proje onayı geri alındı.');
    }

    public function castCommitteeVote(Request $request, Project $project)
    {
        $request->validate([
            'vote' => 'required|in:approve,reject,conditional_approve,abstain',
            'notes' => 'nullable|string|max:1000',
        ]);

        $latestReq = $project->latestApprovalRequest;
        if (!$latestReq || $latestReq->status !== 'pending' || $latestReq->target_approver_type !== 'committee') {
            return back()->with('error', 'Bu proje için aktif bir kurul oylaması bulunmamaktadır.');
        }

        try {
            /** @var CommitteeVotingService $votingService */
            $votingService = app(CommitteeVotingService::class);
            $result = $votingService->recordVote($latestReq, auth()->user(), $request->vote, $request->notes);

            $eval = $result['evaluation'];
            if ($eval['finalized'] ?? false) {
                $statusLabel = $eval['status'] === 'approved' ? 'onaylandı' : 'reddedildi';
                return back()->with('success', "Oyunuz kaydedildi. Kurul nisabı sağlandı ve proje {$statusLabel}!");
            }

            return back()->with('success', 'Kurul oyunuz başarıyla kaydedildi. Diğer üyelerin oyları bekleniyor.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Binlik ayracı ve virgül temizliği
     */
    protected function normalizeBudgetInputs(Request $request): void
    {
        if ($request->has('planned_budget')) {
            $rawBudget = $request->input('planned_budget');
            if ($rawBudget !== null && $rawBudget !== '') {
                $rawBudget = (string) $rawBudget;
                if (strpos($rawBudget, ',') !== false) {
                    $rawBudget = str_replace('.', '', $rawBudget);
                    $rawBudget = str_replace(',', '.', $rawBudget);
                } else {
                    if (substr_count($rawBudget, '.') > 1 || preg_match('/\.\d{3}$/', $rawBudget)) {
                        $rawBudget = str_replace('.', '', $rawBudget);
                    }
                }
                $request->merge(['planned_budget' => is_numeric($rawBudget) ? (float)$rawBudget : 0.00]);
            } else {
                $request->merge(['planned_budget' => 0.00]);
            }
        }

        if ($request->has('budget_items') && is_array($request->input('budget_items'))) {
            $items = $request->input('budget_items');
            foreach ($items as $k => $item) {
                if (isset($item['planned_amount']) && $item['planned_amount'] !== null) {
                    $rawAmount = (string) $item['planned_amount'];
                    if (strpos($rawAmount, ',') !== false) {
                        $rawAmount = str_replace('.', '', $rawAmount);
                        $rawAmount = str_replace(',', '.', $rawAmount);
                    } else {
                        if (substr_count($rawAmount, '.') > 1 || preg_match('/\.\d{3}$/', $rawAmount)) {
                            $rawAmount = str_replace('.', '', $rawAmount);
                        }
                    }
                    $items[$k]['planned_amount'] = is_numeric($rawAmount) ? (float)$rawAmount : 0;
                }
            }
            $request->merge(['budget_items' => $items]);
        }
    }

    /**
     * Proje içi ekip üyesi mikro yetkisini AJAX ile değiştirir
     */
    public function toggleStakeholderPermission(Request $request, Project $project)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission_key' => 'required|string',
            'state' => 'required|boolean',
        ]);

        try {
            $perm = app(\App\Services\ProjectStakeholderService::class)->toggleStakeholderPermission(
                $project,
                (int)$request->user_id,
                $request->permission_key,
                (bool)$request->state,
                auth()->user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Proje içi yetki güncellendi.',
                'permission' => $perm,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Personel ayrılışında görev ve rolleri devreder (Handover Wizard)
     */
    public function handoverMember(Request $request, Project $project)
    {
        $request->validate([
            'from_user_id' => 'required|exists:users,id',
            'to_user_id' => 'required|exists:users,id|different:from_user_id',
            'reason' => 'nullable|string|max:255',
            'transfer_tasks' => 'nullable|boolean',
            'transfer_permissions' => 'nullable|boolean',
        ]);

        try {
            $result = app(\App\Services\ProjectStakeholderService::class)->handoverMember(
                $project,
                (int)$request->from_user_id,
                (int)$request->to_user_id,
                [
                    'reason' => $request->reason,
                    'transfer_tasks' => $request->boolean('transfer_tasks', true),
                    'transfer_permissions' => $request->boolean('transfer_permissions', true),
                ],
                auth()->user()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Görev ve yetki devri başarıyla tamamlandı.',
                    'details' => $result,
                ]);
            }

            return back()->with('success', 'Ayrılan personelin görev ve sorumlulukları başarıyla yeni üyeye devredildi.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Proje ortasında Proje Liderini resmi devirle değiştirir
     */
    public function changeManager(Request $request, Project $project)
    {
        $request->validate([
            'new_manager_id' => 'required|exists:users,id',
            'keep_old_as_member' => 'nullable|boolean',
            'notes' => 'nullable|string|max:255',
        ]);

        try {
            $updatedProject = app(\App\Services\ProjectStakeholderService::class)->changeProjectManager(
                $project,
                (int)$request->new_manager_id,
                $request->boolean('keep_old_as_member', true),
                $request->notes,
                auth()->user()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Proje Lideri başarıyla değiştirildi ve resmi devir tamamlandı.',
                    'project' => $updatedProject,
                ]);
            }

            return back()->with('success', 'Proje Lideri başarıyla değiştirildi.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }
}
