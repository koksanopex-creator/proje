<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;

class GanttController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $projects = Project::where('is_template', false)->forUser($user)->orderBy('code')->get();
        $selectedProjectId = $request->get('project_id', $projects->first()?->id);
        $project = null;

        if ($selectedProjectId) {
            $candidate = Project::with(['phases.tasks.assignee', 'tasks.assignee'])->find($selectedProjectId);
            if ($candidate && $user->canAccessProject($candidate)) {
                $project = $candidate;
            }
        }

        return view('gantt.index', compact('projects', 'project'));
    }

    public function data(int $id)
    {
        $project = Project::with(['phases.tasks', 'tasks.dependencies'])->findOrFail($id);
        $user = auth()->user();

        if (!$user->canAccessProject($project)) {
            abort(403, 'Bu projenin Gantt verilerine erişim yetkiniz bulunmamaktadır.');
        }

        $ganttTasks = [];

        // Ana Proje Barı
        $ganttTasks[] = [
            'id' => 'p_' . $project->id,
            'name' => $project->title,
            'start' => $project->start_date ? $project->start_date->format('Y-m-d') : now()->format('Y-m-d'),
            'end' => $project->target_end_date ? $project->target_end_date->format('Y-m-d') : now()->addMonths(3)->format('Y-m-d'),
            'progress' => $project->progress_percentage,
            'custom_class' => 'gantt-project-bar',
        ];

        // Fazlar
        foreach ($project->phases as $phase) {
            $ganttTasks[] = [
                'id' => 'ph_' . $phase->id,
                'name' => $phase->name,
                'start' => $phase->start_date ? $phase->start_date->format('Y-m-d') : now()->format('Y-m-d'),
                'end' => $phase->end_date ? $phase->end_date->format('Y-m-d') : now()->addDays(20)->format('Y-m-d'),
                'progress' => $phase->progress_percentage,
                'custom_class' => 'gantt-phase-bar',
            ];

            // Görevler
            foreach ($phase->tasks as $task) {
                $ganttTasks[] = [
                    'id' => 't_' . $task->id,
                    'name' => $task->title,
                    'start' => $task->start_date ? $task->start_date->format('Y-m-d') : now()->format('Y-m-d'),
                    'end' => $task->due_date ? $task->due_date->format('Y-m-d') : now()->addDays(5)->format('Y-m-d'),
                    'progress' => $task->progress_percentage,
                    'dependencies' => 'ph_' . $phase->id,
                    'custom_class' => $task->is_milestone ? 'gantt-milestone' : 'gantt-task-bar',
                ];
            }
        }

        return response()->json($ganttTasks);
    }

    public function exportExcel(int $id)
    {
        $project = Project::with(['phases.tasks.assignee', 'manager'])->findOrFail($id);
        $user = auth()->user();

        if (!$user->canAccessProject($project)) {
            abort(403, 'Bu projenin Gantt verilerini dışa aktarma yetkiniz bulunmamaktadır.');
        }

        $safeCode = preg_replace('/[^A-Za-z0-9_\-]/', '_', $project->code);
        $filename = 'Gantt_Takvimi_' . $safeCode . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($project) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM so Excel opens with Turkish characters without encoding issues
            fputs($file, "\xEF\xBB\xBF");

            // Header
            fputcsv($file, [
                'Seviye',
                'Hiyerarşi / Başlık',
                'Sorumlu',
                'İlerleme (%)',
                'Durum',
                'Başlangıç Tarihi',
                'Bitiş / Hedef Tarihi',
                'Gecikme (Gün)',
                'Açıklama'
            ], ';');

            // 1. Proje Satırı
            $projStatusLabel = match($project->status) {
                'completed', 'done' => 'Tamamlandı',
                'in_progress' => 'Devam Ediyor',
                'pending' => 'Beklemede',
                'draft' => 'Taslak',
                'cancelled' => 'İptal',
                'on_hold' => 'Askıda',
                default => ucfirst(str_replace('_', ' ', $project->status))
            };

            fputcsv($file, [
                'PROJE',
                $project->code . ' - ' . $project->title,
                $project->manager?->name ?? 'PMO',
                round($project->progress_percentage) . '%',
                $projStatusLabel,
                $project->start_date ? $project->start_date->format('d.m.Y') : '-',
                $project->target_end_date ? $project->target_end_date->format('d.m.Y') : '-',
                '0',
                $project->description ?? ''
            ], ';');

            // 2. Fazlar ve Görevler
            foreach ($project->phases as $idx => $ph) {
                $phStatusLabel = match($ph->status ?? 'in_progress') {
                    'completed', 'done' => 'Tamamlandı',
                    'in_progress' => 'Devam Ediyor',
                    'pending' => 'Beklemede',
                    default => ucfirst(str_replace('_', ' ', $ph->status ?? ''))
                };

                fputcsv($file, [
                    'FAZ',
                    '  ' . ($idx + 1) . '. ' . $ph->name,
                    $ph->tasks->count() . ' Görev',
                    round($ph->progress_percentage) . '%',
                    $phStatusLabel,
                    $ph->start_date ? $ph->start_date->format('d.m.Y') : '-',
                    $ph->end_date ? $ph->end_date->format('d.m.Y') : '-',
                    '0',
                    ''
                ], ';');

                foreach ($ph->tasks as $t) {
                    $today = now();
                    $tStart = $t->start_date ?: ($ph->start_date ?: $project->start_date);
                    $tDue = $t->due_date ?: ($ph->end_date ?: ($tStart ? $tStart->copy()->addDays(7) : null));
                    $isDelayed = ($t->progress_percentage < 100 && $tDue && $tDue < $today);
                    $overdueDays = $isDelayed ? (int) round($tDue->diffInDays($today)) : 0;

                    $tStatusLabel = $isDelayed 
                        ? "Gecikmede (+{$overdueDays} gün)" 
                        : match($t->status) {
                            'completed', 'done' => 'Tamamlandı',
                            'in_progress' => 'Devam Ediyor',
                            'blocked' => 'Tıkandı',
                            'todo' => 'Yapılacak',
                            'pending' => 'Beklemede',
                            default => ucfirst(str_replace('_', ' ', $t->status))
                        };

                    fputcsv($file, [
                        $t->is_milestone ? 'KİLOMETRE TAŞI' : 'GÖREV',
                        '    ' . ($t->is_milestone ? '◆ ' : '• ') . $t->title,
                        $t->assignee?->name ?? 'Atanmamış',
                        round($t->progress_percentage) . '%',
                        $tStatusLabel,
                        $tStart ? $tStart->format('d.m.Y') : '-',
                        $tDue ? $tDue->format('d.m.Y') : '-',
                        $overdueDays > 0 ? "+{$overdueDays}" : '0',
                        $t->description ?? ''
                    ], ';');
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
