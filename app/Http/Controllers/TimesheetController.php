<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectTimesheet;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Services\AuditLogService;

class TimesheetController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $myTimesheets = ProjectTimesheet::with(['project', 'task'])
            ->where('user_id', $userId)
            ->orderBy('work_date', 'desc')
            ->take(30)
            ->get();

        $activeTasks = ProjectTask::with('project')
            ->whereHas('project')
            ->where('assigned_to', $userId)
            ->whereIn('status', ['todo', 'in_progress'])
            ->get();

        $pendingApprovals = [];
        if (auth()->user()->isDepartmentLeader() || auth()->user()->hasRole('proje_yoneticisi') || auth()->user()->isSuperAdmin()) {
            $pendingApprovals = ProjectTimesheet::with(['user', 'project', 'task'])
                ->where('is_approved', false)
                ->orderBy('work_date', 'desc')
                ->get();
        }

        return view('timesheets.index', compact('myTimesheets', 'activeTasks', 'pendingApprovals'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_id' => 'nullable|exists:project_tasks,id',
            'work_date' => 'required|date',
            'hours' => 'required|numeric|min:0.25|max:24',
            'description' => 'required|string',
        ]);

        $data['user_id'] = auth()->id();
        $timesheet = ProjectTimesheet::create($data);

        // Görevin spent_hours değerini güncelle
        if (!empty($data['task_id'])) {
            $task = ProjectTask::find($data['task_id']);
            if ($task) {
                $task->increment('spent_hours', $data['hours']);
            }
        }

        AuditLogService::log('created', "**{$data['hours']} saat** çalışma kaydı girildi.", 'ProjectTimesheet', $timesheet->id, $data['project_id']);

        return back()->with('success', "Zaman kaydı başarıyla eklendi.");
    }

    public function approve(int $id)
    {
        $timesheet = ProjectTimesheet::findOrFail($id);
        $timesheet->update([
            'is_approved' => true,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', "Zaman kaydı onaylandı.");
    }
}