<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectStakeholder extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'role_type',
        'raci_role',
        'notes',
        'is_active',
        'left_at',
        'left_reason',
        'handover_to_user_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'left_at' => 'datetime',
        ];
    }

    public function project() { return $this->belongsTo(Project::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function handoverToUser() { return $this->belongsTo(User::class, 'handover_to_user_id'); }
}
