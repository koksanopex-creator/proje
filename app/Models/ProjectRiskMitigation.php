<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectRiskMitigation extends Model
{
    protected $fillable = [
        'risk_id', 'action_plan', 'strategy', 'assigned_to', 'target_date', 'is_completed', 'completed_at'
    ];

    protected function casts(): array
    {
        return [
            'target_date' => 'date',
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function risk() { return $this->belongsTo(ProjectRisk::class); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }
}
