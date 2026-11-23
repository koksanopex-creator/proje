<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectBaseline;
use App\Models\ProjectBaselineTask;
use App\Services\AuditLogService;

class BaselineController extends Controller
{
    /**
     * Projeyi o anki haliyle dondurarak yeni bir Baseline (Temel Çizgi) oluşturur
     */
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'version' => 'nullable|string|max:20',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'set_active' => 'nullable|boolean',
        ]);

        // Mevcut baseline sayısını kontrol et
        $existingCount = $project->baselines()->count();
        $nextVersion = !empty($validated['version']) ? $validated['version'] : ('v' . ($existingCount + 1) . '.0');
        $baselineName = !empty($validated['name']) ? $validated['name'] : ($existingCount === 0 ? 'Orijinal Temel Çizgi (Original Baseline)' : "Revizyon Temel Çizgisi ({$nextVersion})");

        // Görevler ve bütçe verilerini topla
        $tasks = $project->tasks()->with(['phase'])->get();
        $phases = $project->phases()->get();
        $budgetItems = $project->budgetItems()->get();

        $totalPlannedHours = (float) $tasks->sum('estimated_hours');
        $totalPlannedBudget = (float) ($project->planned_budget ?? 0);

        // Snapshot JSON verisini oluştur (WBS ağacı, bütçe ve kilometre taşları)
        $snapshot = [
            'created_at' => now()->toIso8601String(),
            'project' => [
                'id' => $project->id,
                'code' => $project->code,
                'title' => $project->title,
                'start_date' => $project->start_date ? $project->start_date->toDateString() : null,
                'target_end_date' => $project->target_end_date ? $project->target_end_date->toDateString() : null,
                'planned_budget' => $totalPlannedBudget,
                'currency' => $project->currency ?? 'TRY',
            ],
            'phases' => $phases->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'start_date' => $p->start_date ? $p->start_date->toDateString() : null,
                'end_date' => $p->end_date ? $p->end_date->toDateString() : null,
                'sort_order' => $p->sort_order,
            ])->toArray(),
            'tasks' => $tasks->map(fn($t) => [
                'id' => $t->id,
                'phase_id' => $t->phase_id,
                'title' => $t->title,
                'start_date' => $t->start_date ? $t->start_date->toDateString() : null,
                'due_date' => $t->due_date ? $t->due_date->toDateString() : null,
                'estimated_hours' => (float)$t->estimated_hours,
                'is_milestone' => (bool)$t->is_milestone,
            ])->toArray(),
            'budget_items' => $budgetItems->map(fn($b) => [
                'id' => $b->id,
                'title' => $b->title,
                'amount' => (float)$b->amount,
            ])->toArray(),
        ];

        // Eğer ilk baseline ise veya kullanıcı aktif seçtiyse, diğerlerini pasif yap
        $shouldBeActive = $request->boolean('set_active', true) || $existingCount === 0;
        if ($shouldBeActive) {
            ProjectBaseline::where('project_id', $project->id)->update(['is_active' => false]);
        }

        $baseline = ProjectBaseline::create([
            'project_id' => $project->id,
            'version' => $nextVersion,
            'name' => $baselineName,
            'description' => $validated['description'] ?? null,
            'is_active' => $shouldBeActive,
            'total_planned_budget' => $totalPlannedBudget,
            'total_planned_hours' => $totalPlannedHours,
            'planned_start_date' => $project->start_date,
            'planned_end_date' => $project->target_end_date,
            'snapshot_data' => $snapshot,
            'created_by' => auth()->id() ?? 1,
            'approved_by' => auth()->id() ?? 1,
            'approved_at' => now(),
        ]);

        // Görev bazında Temel Çizgi satırlarını oluştur (Gantt render hızı için)
        foreach ($tasks as $task) {
            ProjectBaselineTask::create([
                'baseline_id' => $baseline->id,
                'task_id' => $task->id,
                'phase_id' => $task->phase_id,
                'title' => $task->title,
                'baseline_start_date' => $task->start_date,
                'baseline_due_date' => $task->due_date,
                'baseline_hours' => $task->estimated_hours ?? 0,
                'is_milestone' => (bool) $task->is_milestone,
            ]);
        }

        // Audit Log
        AuditLogService::log(
            'baseline_created',
            "Proje temel çizgisi donduruldu ve kaydedildi: {$baseline->version} - {$baseline->name} (Toplam Bütçe: " . number_format($totalPlannedBudget, 2) . " " . ($project->currency ?? 'TRY') . ")",
            'ProjectBaseline',
            $baseline->id,
            $project->id
        );

        return redirect()->back()->with('success', "Proje Temel Çizgisi ({$baseline->version}) başarıyla donduruldu ve aktif referans olarak kaydedildi.");
    }

    /**
     * Seçilen Temel Çizgiyi aktif referans yapar
     */
    public function setActive(Request $request, Project $project, ProjectBaseline $baseline)
    {
        ProjectBaseline::where('project_id', $project->id)->update(['is_active' => false]);
        $baseline->update(['is_active' => true]);

        AuditLogService::log(
            'baseline_activated',
            "Aktif proje temel çizgisi değiştirildi: {$baseline->version} - {$baseline->name}",
            'ProjectBaseline',
            $baseline->id,
            $project->id
        );

        return redirect()->back()->with('success', "Aktif Temel Çizgi referansı {$baseline->version} olarak güncellendi.");
    }

    /**
     * Temel Çizgiyi siler
     */
    public function destroy(Request $request, Project $project, ProjectBaseline $baseline)
    {
        $version = $baseline->version;
        $isActive = $baseline->is_active;
        $baseline->delete();

        // Eğer aktif baseline silindiyse son kalanı aktif yap
        if ($isActive) {
            $lastBaseline = $project->baselines()->first();
            if ($lastBaseline) {
                $lastBaseline->update(['is_active' => true]);
            }
        }

        AuditLogService::log(
            'baseline_deleted',
            "Temel çizgi kaydı silindi: {$version}",
            'ProjectBaseline',
            $baseline->id,
            $project->id
        );

        return redirect()->back()->with('success', "Temel Çizgi ({$version}) silindi.");
    }

    /**
     * EVM (Earned Value Management) Metriklerini JSON olarak döner
     */
    public function evmMetrics(Request $request, Project $project)
    {
        $activeBaseline = $project->activeBaseline;
        if (!$activeBaseline) {
            return response()->json([
                'has_baseline' => false,
                'message' => 'Bu proje için dondurulmuş bir aktif temel çizgi (baseline) bulunmuyor.',
            ]);
        }

        $metrics = $activeBaseline->calculateEvm();
        return response()->json([
            'has_baseline' => true,
            'baseline' => [
                'id' => $activeBaseline->id,
                'version' => $activeBaseline->version,
                'name' => $activeBaseline->name,
                'approved_at' => $activeBaseline->approved_at ? $activeBaseline->approved_at->format('d.m.Y H:i') : null,
            ],
            'metrics' => $metrics,
        ]);
    }
}
