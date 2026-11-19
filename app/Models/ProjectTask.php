<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTask extends Model
{
    use SoftDeletes;

    protected $attributes = [
        'status' => 'todo',
        'priority' => 'medium',
        'estimated_hours' => 0.00,
        'spent_hours' => 0.00,
        'progress_percentage' => 0,
        'is_milestone' => false,
        'sort_order' => 1,
    ];


    protected $fillable = [
        'project_id', 'phase_id', 'parent_id', 'title', 'description',
        'status', 'priority', 'tag', 'assigned_to', 'created_by',
        'start_date', 'due_date', 'estimated_hours', 'spent_hours',
        'progress_percentage', 'is_milestone', 'sort_order'
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'estimated_hours' => 'decimal:2',
            'spent_hours' => 'decimal:2',
            'is_milestone' => 'boolean',
        ];
    }

    public function project() { return $this->belongsTo(Project::class); }
    public function phase() { return $this->belongsTo(ProjectPhase::class, 'phase_id'); }
    public function parent() { return $this->belongsTo(ProjectTask::class, 'parent_id'); }
    public function subtasks() { return $this->hasMany(ProjectTask::class, 'parent_id'); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function assignees() { return $this->belongsToMany(User::class, 'project_task_assignees', 'task_id', 'user_id')->withTimestamps(); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function dependencies() { return $this->hasMany(ProjectTaskDependency::class, 'task_id'); }
    public function timesheets() { return $this->hasMany(ProjectTimesheet::class, 'task_id'); }

    public static function statusLabel(?string $status): string
    {
        return match(strtolower((string)$status)) {
            'todo' => 'Yapılacak (todo)',
            'in_progress' => 'Süreçte / Devam Ediyor (in_progress)',
            'review' => 'Onay & İnceleme (review)',
            'done' => 'Tamamlandı (done)',
            'blocked' => 'Tıkandı / Beklemede (blocked)',
            'cancelled' => 'İptal Edildi (cancelled)',
            default => ucfirst(str_replace('_', ' ', (string)$status)),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabel($this->status);
    }

    public function getPriorityLabelAttribute(): string
    {
        return match(strtolower($this->priority ?? 'medium')) {
            'critical' => 'Kritik',
            'high' => 'Yüksek',
            'low' => 'Düşük',
            default => 'Orta',
        };
    }
}
