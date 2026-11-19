<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectMeetingAction extends Model
{
    protected $fillable = ['meeting_id', 'project_id', 'task_id', 'decision_text', 'assigned_to', 'due_date', 'status'];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
        ];
    }

    public function meeting() { return $this->belongsTo(ProjectMeeting::class); }
    public function project() { return $this->belongsTo(Project::class); }
    public function task() { return $this->belongsTo(ProjectTask::class); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }
}
