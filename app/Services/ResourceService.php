<?php

namespace App\Services;

use App\Models\ProjectTask;
use App\Models\User;
use Carbon\Carbon;

class ResourceService
{
    /**
     * Personel ve ekipmanların haftalık/aylık kapasite ve iş yükü ısı haritası analizi (Workload Heatmap).
     */
    public function getCapacityAnalysis(?User $viewer = null): array
    {
        $viewer = $viewer ?? auth()->user();
        $usersQuery = User::with(['assignedTasks.project'])
            ->where('is_active', true)
            ->orderBy('name');

        if ($viewer) {
            $deptIds = $viewer->getResponsibleDepartmentIds();
            if ($deptIds !== null && !empty($deptIds)) {
                $usersQuery->whereIn('bolum_id', $deptIds);
            }
        }

        $users = $usersQuery->get();

        // Önümüzdeki 5 Hafta Blokları
        $weeks = [];
        $startOfWeek = now()->startOfWeek();
        for ($i = 0; $i < 5; $i++) {
            $wStart = $startOfWeek->copy()->addWeeks($i);
            $wEnd = $wStart->copy()->endOfWeek();
            $weeks[$i] = [
                'index' => $i,
                'week_number' => $wStart->weekOfYear,
                'label' => 'H' . $wStart->weekOfYear,
                'full_label' => 'Hafta ' . $wStart->weekOfYear . ' (' . $wStart->format('d/m') . ' - ' . $wEnd->format('d/m') . ')',
                'start_date' => $wStart->toDateString(),
                'end_date' => $wEnd->toDateString(),
            ];
        }

        $userRows = [];
        $weeklyTotals = array_fill(0, 5, 0.0);
        $weeklyCapacities = array_fill(0, 5, 0.0);

        foreach ($users as $user) {
            $tasks = $user->assignedTasks->whereIn('status', ['todo', 'in_progress']);
            $totalHours = (float) $tasks->sum('estimated_hours');
            $activeTasksCount = $tasks->count();
            $weeklyCapacity = 45.0; // Haftalık standart tam zamanlı kapasite

            // Haftalık saat dağılımı (Dinamik Task Termin Eşlemesi)
            $weeksLoad = [];
            $userHasConflict = false;

            for ($i = 0; $i < 5; $i++) {
                $wStart = Carbon::parse($weeks[$i]['start_date']);
                $wEnd = Carbon::parse($weeks[$i]['end_date']);

                // Bu haftaya düşen görevlerin saatleri
                $weekHours = 0.0;
                foreach ($tasks as $t) {
                    $tStart = $t->start_date ? Carbon::parse($t->start_date) : $t->created_at;
                    $tDue = $t->due_date ? Carbon::parse($t->due_date) : $tStart->copy()->addDays(7);

                    // Görevin bu haftayla kesişimi var mı?
                    if ($tStart->lte($wEnd) && $tDue->gte($wStart)) {
                        $taskDays = max(1, (int) round($tStart->diffInDays($tDue)));
                        // Bu haftaya düşen gün oranı
                        $overlapStart = $tStart->max($wStart);
                        $overlapEnd = $tDue->min($wEnd);
                        $overlapDays = max(1, (int) round($overlapStart->diffInDays($overlapEnd)));
                        $proportionalHours = ($t->estimated_hours / $taskDays) * $overlapDays;
                        $weekHours += round($proportionalHours, 1);
                    }
                }

                // İlk hafta aktif görev saatlerini kapsayacak şekilde fallback
                if ($i === 0 && $weekHours === 0.0 && $totalHours > 0) {
                    $weekHours = min(50.0, $totalHours);
                }

                $loadPct = $weeklyCapacity > 0 ? (int) round(($weekHours / $weeklyCapacity) * 100) : 0;
                $isOverloaded = ($weekHours > $weeklyCapacity);
                if ($isOverloaded) {
                    $userHasConflict = true;
                }

                if ($isOverloaded) {
                    $status = 'overloaded';
                    $class = 'cell-overloaded';
                } elseif ($weekHours >= 35.0) {
                    $status = 'optimal';
                    $class = 'cell-optimal';
                } elseif ($weekHours > 0.0) {
                    $status = 'underloaded';
                    $class = 'cell-underloaded';
                } else {
                    $status = 'idle';
                    $class = 'cell-idle';
                }

                $weeksLoad[$i] = [
                    'hours' => $weekHours,
                    'percentage' => $loadPct,
                    'status' => $status,
                    'class' => $class,
                ];

                $weeklyTotals[$i] += $weekHours;
                $weeklyCapacities[$i] += $weeklyCapacity;
            }

            // Görev dökümü (Personel > Proje > Görev Açılır Ağacı)
            $tasksBreakdown = [];
            foreach ($tasks as $t) {
                $tasksBreakdown[] = [
                    'id' => $t->id,
                    'title' => $t->title,
                    'project_id' => $t->project_id,
                    'project_code' => $t->project?->code ?? '-',
                    'project_title' => $t->project?->title ?? '-',
                    'hours' => (float) $t->estimated_hours,
                    'status' => $t->status,
                    'due_date' => $t->due_date ? Carbon::parse($t->due_date)->format('d.m.Y') : '-',
                ];
            }

            $overallLoadPct = $weeklyCapacity > 0 ? (int) round(($totalHours / $weeklyCapacity) * 100) : 0;

            $userRows[] = [
                'user_id' => $user->id,
                'name' => $user->name,
                'unvan' => $user->unvan ?? 'Uzman',
                'department' => $user->department ?? 'Operasyon',
                'task_count' => $activeTasksCount,
                'total_hours' => $totalHours,
                'weekly_capacity' => $weeklyCapacity,
                'capacity_percentage' => $overallLoadPct,
                'has_conflict' => $userHasConflict,
                'conflict_warning' => $userHasConflict ? "**{$user->name}** haftalık 45 saatin üzerinde planlanarak aşırı yüklendi! Kaynak çakışması mevcut." : null,
                'weeks_load' => $weeksLoad,
                'tasks_breakdown' => $tasksBreakdown,
            ];
        }

        // Özet İstatistikler
        $overloadedCount = collect($userRows)->where('has_conflict', true)->count();
        $activeWithTasks = collect($userRows)->where('task_count', '>', 0);
        $optimalCount = $activeWithTasks->where('has_conflict', false)->filter(fn($u) => $u['total_hours'] >= 30)->count();
        $underloadedCount = $activeWithTasks->filter(fn($u) => $u['total_hours'] < 30)->count();

        return [
            'weeks' => $weeks,
            'user_rows' => $userRows,
            'weekly_totals' => $weeklyTotals,
            'weekly_capacities' => $weeklyCapacities,
            'summary' => [
                'total_users' => count($userRows),
                'active_assigned_users' => $activeWithTasks->count(),
                'overloaded_count' => $overloadedCount,
                'optimal_count' => $optimalCount,
                'underloaded_count' => $underloadedCount,
                'total_company_hours' => array_sum($weeklyTotals),
            ],
            // Geriye dönük uyumluluk (Legacy flat rows)
            'legacy_rows' => $userRows,
        ];
    }
}