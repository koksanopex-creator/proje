<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectBaselineTask extends Model
{
    protected $fillable = [
        'baseline_id',
        'task_id',
        'phase_id',
        'title',
        'baseline_start_date',
        'baseline_due_date',
        'baseline_hours',
        'is_milestone',
    ];

    protected function casts(): array
    {
        return [
            'baseline_start_date' => 'date',
            'baseline_due_date' => 'date',
            'baseline_hours' => 'decimal:2',
            'is_milestone' => 'boolean',
        ];
    }

    public function baseline()
    {
        return $this->belongsTo(ProjectBaseline::class, 'baseline_id');
    }

    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }

    public function phase()
    {
        return $this->belongsTo(ProjectPhase::class, 'phase_id');
    }

    /**
     * Güncel görev ile temel çizgi arasındaki gün farkını hesaplar (+ gecikme, - erken)
     */
    public function getDaysVariance(): ?int
    {
        if (!$this->baseline_due_date || !$this->task || !$this->task->due_date) {
            return null;
        }

        $baselineDue = \Carbon\Carbon::parse($this->baseline_due_date)->startOfDay();
        $currentDue = \Carbon\Carbon::parse($this->task->due_date)->startOfDay();

        return (int) $baselineDue->diffInDays($currentDue, false);
    }
}
