<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Services\AuditLogService;

class KanbanController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Dinamik Yetki Matrisi Kontrolü: kanban.view
        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->hasPermission('kanban.view') && !$user->hasPermission('task.manage')) {
            abort(403, 'Dinamik Yetki Matrisi kuralı: Kanban panosunu görüntüleme yetkiniz bulunmamaktadır.');
        }

        $projects = Project::where('is_template', false)->forUser($user)->orderBy('code')->get();
        $selectedProjectId = $request->get('project_id', $projects->first()?->id);

        $selectedProject = null;
        $tasks = collect();

        $allUsers = \App\Models\User::orderBy('name')->get();

        if ($selectedProjectId) {
            $candidate = Project::with(['tasks.assignee', 'tasks.assignees', 'tasks.phase', 'phases'])->find($selectedProjectId);
            if ($candidate && $user->canAccessProject($candidate)) {
                $selectedProject = $candidate;
                $tasks = $selectedProject->tasks;
            }
        }

        $columns = [
            'todo' => ['title' => 'Yapılacaklar', 'color' => '#64748b', 'badge' => 'badge-secondary'],
            'in_progress' => ['title' => 'Süreçte / Devam Ediyor', 'color' => '#d97706', 'badge' => 'badge-warning'],
            'review' => ['title' => 'Onay & İnceleme', 'color' => '#2563eb', 'badge' => 'badge-info'],
            'done' => ['title' => 'Tamamlandı', 'color' => '#16a34a', 'badge' => 'badge-success'],
            'blocked' => ['title' => 'Tıkandı / Beklemede', 'color' => '#dc2626', 'badge' => 'badge-danger'],
        ];

        // Dinamik İşlem Yetkileri (Matris & Kural 19 Uyumlu)
        $canChangeStatus = !$user->isYonetim() && ($user->isSuperAdmin() || $user->isAdmin() || $user->hasPermission('kanban.change_status') || $user->hasPermission('task.manage'));
        $canCreateTask = !$user->isYonetim() && ($user->isSuperAdmin() || $user->isAdmin() || $user->hasPermission('task.create') || $user->hasPermission('task.manage'));
        $canAssignTask = !$user->isYonetim() && ($user->isSuperAdmin() || $user->isAdmin() || $user->hasPermission('task.assign') || $user->hasPermission('task.manage'));

        return view('kanban.index', compact(
            'projects',
            'selectedProject',
            'tasks',
            'columns',
            'allUsers',
            'canChangeStatus',
            'canCreateTask',
            'canAssignTask'
        ));
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:project_tasks,id',
            'status' => 'required|in:todo,in_progress,review,done,blocked',
        ]);

        $user = auth()->user();

        // Kural 19: Yönetim rolü (Read-Only) kesinlikle veri mutasyonu yapamaz!
        if ($user->isYonetim()) {
            return response()->json([
                'success' => false,
                'message' => 'Yönetim (Read-Only) rolü görev veya kart durumu değiştiremez.'
            ], 403);
        }

        $task = ProjectTask::with(['project', 'assignees'])->findOrFail($request->task_id);

        if (!$user->canAccessProject($task->project)) {
            return response()->json([
                'success' => false,
                'message' => 'Bu görevin projesine erişim yetkiniz bulunmamaktadır.'
            ], 403);
        }

        // 1. Genel Yetki Kontrolü (Dinamik Yetki Matrisi)
        $hasGlobalPerm = $user->isSuperAdmin() || $user->isAdmin() || $user->hasPermission('kanban.change_status') || $user->hasPermission('task.manage');
        if (!$hasGlobalPerm) {
            return response()->json([
                'success' => false,
                'message' => 'Dinamik Yetki Matrisi: Rolünüzün Kanban kart durumu değiştirme yetkisi kapalıdır.'
            ], 403);
        }

        // 2. Proje & Görev Düzeyinde Temsil / Sorumluluk Kontrolü
        $isProjectManager = ($task->project->manager_id === $user->id || $task->project->created_by === $user->id || $task->project->sponsor_id === $user->id);
        $isAssignee = ($task->assigned_to === $user->id || $task->assignees->contains('id', $user->id));
        $isCreator = ($task->created_by === $user->id);

        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$isProjectManager && !$isAssignee && !$isCreator) {
            return response()->json([
                'success' => false,
                'message' => 'Bu görevin durumunu yalnızca proje yöneticisi, görevi oluşturan kişi veya göreve atanmış sorumlu personel değiştirebilir.'
            ], 403);
        }

        $oldStatus = $task->status;
        $task->update(['status' => $request->status]);

        if ($request->status === 'done') {
            $task->update(['progress_percentage' => 100]);
        }

        // Standart Kurumsal Durum Etiketleri
        $oldStatusLabel = ProjectTask::statusLabel($oldStatus);
        $newStatusLabel = ProjectTask::statusLabel($request->status);

        AuditLogService::log(
            'task_status_changed',
            "Kanban üzerinden '**{$task->title}**' görevi durumu '**{$oldStatusLabel}**' ➔ '**{$newStatusLabel}**' olarak güncellendi.",
            'ProjectTask',
            $task->id,
            $task->project_id,
            null,
            [
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'old_label' => $oldStatusLabel,
                'new_label' => $newStatusLabel,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Görev durumu '{$newStatusLabel}' olarak güncellendi."
        ]);
    }
}