<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectMeeting;
use App\Models\ProjectMeetingParticipant;
use App\Models\User;
use App\Services\AuditLogService;

class MeetingController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->hasPermission('meeting.view')) {
            abort(403, 'Toplantı modülünü görüntüleme yetkiniz bulunmamaktadır.');
        }

        $projects = Project::where('is_template', false)->forUser($user)->orderBy('code')->get();
        $selectedProjectId = $request->get('project_id');

        $query = ProjectMeeting::with(['project', 'organizer', 'participants.user', 'actions.assignee'])
            ->whereHas('project', function ($pq) use ($user) {
                $pq->forUser($user);
            });

        if ($selectedProjectId) {
            $query->where('project_id', $selectedProjectId);
        }

        $meetings = $query->orderBy('meeting_date', 'desc')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        $stats = [
            'total' => $meetings->count(),
            'scheduled' => $meetings->whereIn('status', ['scheduled', 'planned'])->count(),
            'completed' => $meetings->where('status', 'completed')->count(),
        ];

        return view('meetings.index', compact('meetings', 'projects', 'selectedProjectId', 'users', 'stats'));
    }

    public function show($id)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->hasPermission('meeting.view')) {
            abort(403, 'Toplantı modülünü görüntüleme yetkiniz bulunmamaktadır.');
        }

        $meeting = ProjectMeeting::with([
            'project.bolum',
            'project.manager',
            'project.sponsor',
            'project.stakeholders.user',
            'organizer',
            'participants.user',
            'actions.assignee',
            'actions.task',
        ])->findOrFail($id);

        if (!$user->canAccessProject($meeting->project)) {
            abort(403, 'Bu toplantının projesine erişim yetkiniz bulunmamaktadır.');
        }

        $users = User::where('is_active', true)->orderBy('name')->get();
        $projectTeam = $meeting->project->stakeholders->pluck('user')->filter()->unique('id');

        return view('meetings.show', compact('meeting', 'projectTeam', 'users'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->hasPermission('meeting.create') && !$user->hasPermission('meeting.manage')) {
            abort(403, 'Yeni toplantı planlama veya oluşturma yetkiniz bulunmamaktadır.');
        }

        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'meeting_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'agenda' => 'nullable|string',
            'participants' => 'nullable|array',
            'participants.*' => 'exists:users,id',
        ]);

        $project = Project::findOrFail($request->project_id);
        if (!$user->canAccessProject($project)) {
            abort(403, 'Bu projede toplantı oluşturma yetkiniz bulunmamaktadır.');
        }

        $data['organizer_id'] = auth()->id();
        $data['status'] = 'scheduled';
        $participants = $data['participants'] ?? [];
        unset($data['participants']);

        $meeting = ProjectMeeting::create($data);

        // Katılımcıları ekle ve her birine bildirim matrisinden Zil ve E-Posta daveti ilet
        $matrixService = app(\App\Services\NotificationMatrixService::class);
        foreach ($participants as $userId) {
            ProjectMeetingParticipant::create([
                'meeting_id' => $meeting->id,
                'user_id' => $userId,
                'status' => 'invited',
            ]);
            $matrixService->dispatchMeetingInvitation($meeting, (int)$userId);
        }

        AuditLogService::log(
            'created',
            "Proje toplantısı planlandı: '**{$meeting->title}**' (Tarih: {$meeting->meeting_date->format('d.m.Y H:i')})",
            'ProjectMeeting',
            $meeting->id,
            $meeting->project_id
        );

        return redirect()->route('meetings.show', $meeting->id)
            ->with('success', "Toplantı başarıyla oluşturuldu ve katılımcılara davet bildirimi (Zil/Mail) iletildi.");
    }

    public function updateStatus(Request $request, int $meetingId)
    {
        // Madde 19: Yonetim rolü (ID=19) salt okunurdur, durum değiştiremez
        if (auth()->user()->isYonetim()) {
            abort(403, 'Yönetim rolü salt okunur yetkiye sahiptir, toplantı durumunu değiştiremez.');
        }

        $meeting = ProjectMeeting::with(['project.stakeholders.user', 'participants.user'])->findOrFail($meetingId);
        if (!auth()->user()->canAccessProject($meeting->project)) {
            abort(403, 'Bu toplantının projesine erişim yetkiniz bulunmamaktadır.');
        }

        $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'minutes' => 'nullable|string',
            'custom_note' => 'nullable|string|max:1000',
            'notify_bell' => 'nullable|array',
            'notify_bell.*' => 'exists:users,id',
            'notify_mail' => 'nullable|array',
            'notify_mail.*' => 'exists:users,id',
        ]);

        $oldStatus = $meeting->status_label;

        $updateData = ['status' => $request->status];
        if ($request->has('minutes')) {
            $updateData['minutes'] = $request->minutes;
        }
        $meeting->update($updateData);

        // Bildirim Matrisi ve Özelleştirilmiş Alıcı Seçimleri (Zil & Mail)
        $matrixService = app(\App\Services\NotificationMatrixService::class);
        $matrixService->dispatchMeetingStatus(
            $meeting,
            $request->status,
            $request->custom_note,
            $request->has('notify_bell') ? (array)$request->notify_bell : null,
            $request->has('notify_mail') ? (array)$request->notify_mail : null
        );

        AuditLogService::log(
            'updated',
            "Toplantı durumu güncellendi: '**{$meeting->title}**' -> **{$meeting->status_label}**" . ($request->filled('custom_note') ? " (Not: " . e($request->custom_note) . ")" : ""),
            'ProjectMeeting',
            $meeting->id,
            $meeting->project_id
        );

        return back()->with('success', "Toplantı durumu '{$meeting->status_label}' olarak güncellendi ve paydaşlara bildirim iletildi.");
    }

    public function updateAgenda(Request $request, int $meetingId)
    {
        if (auth()->user()->isYonetim()) {
            abort(403, 'Yönetim rolü salt okunur yetkiye sahiptir, gündem maddelerini değiştiremez.');
        }

        $meeting = ProjectMeeting::with('project')->findOrFail($meetingId);
        if (!auth()->user()->canAccessProject($meeting->project)) {
            abort(403, 'Bu toplantının projesine erişim yetkiniz bulunmamaktadır.');
        }

        $request->validate([
            'agenda' => 'nullable|string',
        ]);

        $meeting->update([
            'agenda' => $request->agenda,
        ]);

        AuditLogService::log(
            'updated',
            "Toplantı gündem maddeleri güncellendi: '**{$meeting->title}**'",
            'ProjectMeeting',
            $meeting->id,
            $meeting->project_id
        );

        return back()->with('success', 'Toplantı gündem maddeleri başarıyla güncellendi.');
    }

    public function addParticipant(Request $request, int $meetingId)
    {
        $user = auth()->user();
        if ($user->isYonetim()) {
            abort(403, 'Yönetim rolü salt okunur yetkiye sahiptir, katılımcı ekleyemez.');
        }

        $meeting = ProjectMeeting::with('project')->findOrFail($meetingId);
        if (!$user->canAccessProject($meeting->project)) {
            abort(403, 'Bu toplantının projesine erişim yetkiniz bulunmamaktadır.');
        }

        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->hasPermission('meeting.create') && !$user->hasPermission('meeting.manage') && $meeting->organizer_id !== $user->id) {
            abort(403, 'Toplantıya katılımcı davet etme yetkiniz bulunmamaktadır.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        if (!$meeting->participants()->where('user_id', $request->user_id)->exists()) {
            $part = ProjectMeetingParticipant::create([
                'meeting_id' => $meeting->id,
                'user_id' => $request->user_id,
                'status' => 'invited',
            ]);

            // Katılımcıya Bildirim Matrisi ile Davet Gönder (Zil ve E-Posta)
            app(\App\Services\NotificationMatrixService::class)->dispatchMeetingInvitation($meeting, (int)$request->user_id);

            $invitedUser = User::find($request->user_id);
            $userName = $invitedUser ? $invitedUser->name : 'Katılımcı';

            AuditLogService::log(
                'participant_added',
                "Toplantıya yeni katılımcı davet edildi: **{$userName}**",
                'ProjectMeeting',
                $meeting->id,
                $meeting->project_id
            );

            return back()->with('success', "Katılımcı ({$userName}) toplantıya eklendi ve davet bildirimi (Zil/Mail) iletildi.");
        }

        return back()->with('error', 'Bu kullanıcı zaten toplantı katılımcı listesinde yer alıyor.');
    }

    public function removeParticipant(Request $request, int $meetingId, int $participantId)
    {
        $user = auth()->user();
        if ($user->isYonetim()) {
            abort(403, 'Yönetim rolü salt okunur yetkiye sahiptir, işlem yapamaz.');
        }

        $meeting = ProjectMeeting::with('project')->findOrFail($meetingId);
        if (!$user->canAccessProject($meeting->project)) {
            abort(403, 'Bu toplantının projesine erişim yetkiniz bulunmamaktadır.');
        }

        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$user->hasPermission('meeting.manage') && !$user->hasPermission('meeting.create') && $meeting->organizer_id !== $user->id) {
            abort(403, 'Katılımcı davetini geri alma / listeden çıkarma yetkiniz bulunmamaktadır.');
        }

        $participant = $meeting->participants()->findOrFail($participantId);
        $userName = $participant->user?->name ?? 'Katılımcı';
        $participant->delete();

        AuditLogService::log(
            'participant_removed',
            "Toplantı katılımcı daveti geri alındı / çıkarıldı: **{$userName}**",
            'ProjectMeeting',
            $meeting->id,
            $meeting->project_id
        );

        return back()->with('success', "'{$userName}' kullanıcısının toplantı daveti başarıyla geri alındı.");
    }

    public function convertActionToTask(Request $request, int $meetingId)
    {
        $meeting = ProjectMeeting::with('project.stakeholders.user')->findOrFail($meetingId);
        if (!auth()->user()->canAccessProject($meeting->project)) {
            abort(403, 'Bu toplantının projesine erişim yetkiniz bulunmamaktadır.');
        }
        
        $request->validate([
            'decision_text' => 'required|string|max:255',
            'assigned_target' => 'nullable|string', // 'team' veya user id
            'due_date' => 'required|date',
        ]);

        $assignedTarget = $request->input('assigned_target');
        $isTeamAssignment = ($assignedTarget === 'team');
        $assignedUserId = (!$isTeamAssignment && is_numeric($assignedTarget)) ? (int)$assignedTarget : null;

        $taskTitle = '[Toplantı Kararı] ' . $request->decision_text;
        $taskDesc = "Bu görev '{$meeting->title}' başlıklı toplantıda alınan karar doğrultusunda oluşturulmuştur.\nToplantı Tarihi: " . $meeting->meeting_date->format('d.m.Y H:i');

        if ($isTeamAssignment) {
            $taskDesc .= "\n\n👥 SAP Ekip Modeli: Bu görev tüm Proje Ekibinin ortak sorumluluğundadır.";
            $tag = '#Toplantı #OrtakEkip';
        } else {
            $tag = '#Toplantı';
        }

        // Proje Görevine dönüştür
        $task = $meeting->project->tasks()->create([
            'title' => $taskTitle,
            'description' => $taskDesc,
            'status' => 'todo',
            'priority' => 'high',
            'tag' => $tag,
            'assigned_to' => $assignedUserId,
            'due_date' => $request->due_date,
            'created_by' => auth()->id(),
        ]);

        $action = $meeting->actions()->create([
            'project_id' => $meeting->project_id,
            'task_id' => $task->id,
            'decision_text' => $request->decision_text,
            'assigned_to' => $assignedUserId,
            'due_date' => $request->due_date,
            'status' => 'converted_to_task',
        ]);

        AuditLogService::log(
            'created',
            "Toplantı kararı proje görevine dönüştürüldü: '**{$task->title}**' (Termin: {$task->due_date->format('d.m.Y')})",
            'ProjectTask',
            $task->id,
            $meeting->project_id
        );

        return back()->with('success', "Toplantı kararı başarıyla Proje Görevine dönüştürüldü ve Kanban/Gantt panolarına aktarıldı.");
    }
}