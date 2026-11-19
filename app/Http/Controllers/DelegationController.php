<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserDelegation;
use App\Services\DelegationService;
use Exception;

class DelegationController extends Controller
{
    protected DelegationService $delegationService;

    public function __construct(DelegationService $delegationService)
    {
        $this->delegationService = $delegationService;
    }

    /**
     * Kullanıcının vekaletlerini JSON olarak döner (Modal ve üst bar için)
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Kendi verdiği vekaletler
        $given = UserDelegation::with(['delegatee', 'delegator'])
            ->where('delegator_id', $user->id)
            ->latest()
            ->get();

        // Kendi adına aldığı vekillikler
        $received = UserDelegation::with(['delegator', 'delegatee'])
            ->where('delegatee_id', $user->id)
            ->latest()
            ->get();

        // Tüm aktif personeller (vekalet bırakılabilecek adaylar)
        $users = User::where('id', '!=', $user->id)
            ->where('is_active', true)
            ->where('is_external', false)
            ->orderBy('name')
            ->select('id', 'name', 'unvan', 'bolum_id')
            ->with('bolum:id,ad')
            ->get();

        $formatDelegation = function ($d) {
            $actions = $d->getAuditLogs()->map(fn($l) => [
                'id' => $l->id,
                'project_id' => $l->project_id,
                'project_title' => $l->project?->title ?? 'Genel Sistem / Proje Dışı',
                'action' => $l->action,
                'description' => $l->description,
                'created_at' => $l->created_at->format('d.m.Y H:i'),
                'user_name' => $l->user?->name ?? 'Vekil',
                'acting_as' => $l->acting_as_role_name ?? null,
            ]);

            $projectNames = [];
            if ($d->project_scope_type === 'selected' && !empty($d->project_ids)) {
                $projectNames = \App\Models\Project::whereIn('id', (array)$d->project_ids)->pluck('title', 'id')->toArray();
            }

            return [
                'id' => $d->id,
                'delegator_id' => $d->delegator_id,
                'delegator' => $d->delegator ? ['id' => $d->delegator->id, 'name' => $d->delegator->name, 'unvan' => $d->delegator->unvan] : null,
                'delegatee_id' => $d->delegatee_id,
                'delegatee' => $d->delegatee ? ['id' => $d->delegatee->id, 'name' => $d->delegatee->name, 'unvan' => $d->delegatee->unvan] : null,
                'starts_at' => $d->starts_at->toISOString(),
                'ends_at' => $d->ends_at->toISOString(),
                'starts_at_formatted' => $d->starts_at->format('d.m.Y H:i'),
                'ends_at_formatted' => $d->ends_at->format('d.m.Y H:i'),
                'is_active' => (bool)$d->is_active,
                'is_currently_valid' => $d->isCurrentlyValid(),
                'duration_text' => $d->getDurationText(),
                'cancelled_at' => $d->cancelled_at ? $d->cancelled_at->format('d.m.Y H:i') : null,
                'cancel_reason' => $d->cancel_reason,
                'scope' => $d->scope,
                'scope_badge' => $d->getScopeSummaryBadge(),
                'project_scope_type' => $d->project_scope_type ?: 'all',
                'project_ids' => $d->project_ids ?: [],
                'project_names' => $projectNames,
                'reason_category' => $d->reason_category,
                'notes' => $d->notes,
                'actions_count' => $actions->count(),
                'actions' => $actions,
            ];
        };

        $myProjects = \App\Models\Project::accessibleBy($user)
            ->select('id', 'title', 'code')
            ->orderBy('title')
            ->get();

        return response()->json([
            'success' => true,
            'given' => $given->map($formatDelegation),
            'received' => $received->map($formatDelegation),
            'users' => $users,
            'my_projects' => $myProjects,
            'my_permissions' => $user->getDelegatablePermissions(),
            'scopes' => UserDelegation::getScopeCatalog(),
            'reason_categories' => UserDelegation::getReasonCategories(),
        ]);
    }

    /**
     * Yeni bir vekalet kaydı oluşturur
     */
    public function store(Request $request)
    {
        $request->validate([
            'delegatee_id' => 'required|exists:users,id',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'scope' => 'nullable|array',
            'project_scope_type' => 'nullable|string|in:all,selected',
            'project_ids' => 'nullable|array',
            'reason_category' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:255',
            'delegator_id' => 'nullable|exists:users,id',
        ]);

        $actor = auth()->user();

        // Başkası adına vekalet oluşturuluyorsa yetki kontrolü
        $delegator = $actor;
        if ($request->filled('delegator_id') && (int)$request->delegator_id !== $actor->id) {
            if (!$actor->isSuperAdmin() && !$actor->isAdmin() && !$actor->hasPermission('delegation.manage_all')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Başkası adına vekalet tanımlama yetkiniz bulunmamaktadır.'
                ], 403);
            }
            $delegator = User::findOrFail($request->delegator_id);
        } else {
            // Kendi adına vekalet yetkisi kontrolü
            if (!$actor->isSuperAdmin() && !$actor->hasPermission('delegation.create')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kurumsal vekalet bırakma yetkiniz bulunmamaktadır.'
                ], 403);
            }
        }

        try {
            $delegation = $this->delegationService->createDelegation(
                $delegator,
                (int)$request->delegatee_id,
                $request->all(),
                $actor
            );

            return response()->json([
                'success' => true,
                'message' => 'Kurumsal vekalet sözleşmesi başarıyla oluşturuldu ve vekile bildirim iletildi.',
                'delegation' => $delegation->load(['delegator', 'delegatee']),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Vekaleti iptal eder / erken sonlandırır
     */
    public function destroy(Request $request, $id)
    {
        $actor = auth()->user();
        $reason = $request->input('reason', 'Kullanıcı talebiyle erken sonlandırıldı');

        try {
            $delegation = $this->delegationService->cancelDelegation((int)$id, $actor, $reason);

            return response()->json([
                'success' => true,
                'message' => 'Vekalet sözleşmesi başarıyla sonlandırıldı ve taraflara bildirim iletildi.',
                'delegation' => $delegation,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        }
    }

    /**
     * Belirli bir vekaletin detay ve yetki kapsam matrisini döner
     */
    public function show($id)
    {
        $delegation = UserDelegation::with(['delegator', 'delegatee', 'creator'])->findOrFail($id);

        $matrix = $delegation->getDetailedScopeMatrix();
        $badge = $delegation->getScopeSummaryBadge();
        $reason = $delegation->getFormattedReason();

        $actions = $delegation->getAuditLogs()->map(fn($l) => [
            'id' => $l->id,
            'project_id' => $l->project_id,
            'project_title' => $l->project?->title ?? 'Genel Sistem / Proje Dışı',
            'action' => $l->action,
            'description' => $l->description,
            'created_at' => $l->created_at->format('d.m.Y H:i'),
            'user_name' => $l->user?->name ?? 'Vekil',
            'acting_as' => $l->acting_as_role_name ?? null,
        ]);

        $projectNames = [];
        if ($delegation->project_scope_type === 'selected' && !empty($delegation->project_ids)) {
            $projectNames = \App\Models\Project::whereIn('id', (array)$delegation->project_ids)->pluck('title', 'id')->toArray();
        }

        return response()->json([
            'success' => true,
            'delegation' => [
                'id' => $delegation->id,
                'delegator_id' => $delegation->delegator_id,
                'delegator_name' => $delegation->delegator?->name ?? 'Bilinmeyen',
                'delegator_department' => $delegation->delegator?->department ?? 'Yönetim',
                'delegator_role' => $delegation->delegator?->roles?->first()?->name ?? 'Kullanıcı',
                'delegatee_id' => $delegation->delegatee_id,
                'delegatee_name' => $delegation->delegatee?->name ?? 'Bilinmeyen Vekil',
                'delegatee_department' => $delegation->delegatee?->department ?? '',
                'delegatee_role' => $delegation->delegatee?->roles?->first()?->name ?? 'Kullanıcı',
                'starts_at' => $delegation->starts_at->format('d.m.Y H:i'),
                'ends_at' => $delegation->ends_at->format('d.m.Y H:i'),
                'is_active' => $delegation->is_active,
                'is_currently_valid' => $delegation->isCurrentlyValid(),
                'duration_text' => $delegation->getDurationText(),
                'cancelled_at' => $delegation->cancelled_at ? $delegation->cancelled_at->format('d.m.Y H:i') : null,
                'cancel_reason' => $delegation->cancel_reason,
                'project_scope_type' => $delegation->project_scope_type ?: 'all',
                'project_ids' => $delegation->project_ids ?: [],
                'project_names' => $projectNames,
                'reason_category' => $reason['category'],
                'notes' => $reason['note'],
                'has_notes' => $reason['has_note'],
                'scope_badge' => $badge,
                'matrix' => $matrix,
                'actions_count' => $actions->count(),
                'actions' => $actions,
            ]
        ]);
    }
}
