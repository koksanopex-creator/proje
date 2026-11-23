<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectPhase extends Model
{
    protected $fillable = [
        'project_id', 'name', 'description', 'sort_order', 'status',
        'start_date', 'end_date', 'progress_percentage',
        'requires_ehs_approval', 'ehs_approved_by', 'ehs_approved_at', 'ehs_permit_document', 'ehs_notes', 'completed_by', 'completed_at'
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'ehs_approved_at' => 'datetime',
            'completed_at' => 'datetime',
            'requires_ehs_approval' => 'boolean',
        ];
    }

    public function project() { return $this->belongsTo(Project::class); }
        public function completedBy() { return $this->belongsTo(User::class, 'completed_by'); }
    public function ehsApprovedBy() { return $this->belongsTo(User::class, 'ehs_approved_by'); }
    public function tasks() { return $this->hasMany(ProjectTask::class, 'phase_id'); }
    public function qualities() { return $this->hasMany(ProjectQuality::class, 'phase_id'); }
}
