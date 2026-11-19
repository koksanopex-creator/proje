<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectTimesheet extends Model
{
    protected $fillable = [
        'project_id', 'task_id', 'user_id', 'work_date', 'hours',
        'description', 'is_timer_entry', 'is_approved', 'approved_by', 'approved_at'
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'hours' => 'decimal:2',
            'is_timer_entry' => 'boolean',
            'is_approved' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function project() { return $this->belongsTo(Project::class); }
    public function task() { return $this->belongsTo(ProjectTask::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
}
