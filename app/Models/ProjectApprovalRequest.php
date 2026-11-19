<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectApprovalRequest extends Model
{
    protected $fillable = [
        'project_id',
        'doa_rule_id',
        'rule_snapshot',
        'target_approver_type',
        'target_role_id',
        'target_user_id',
        'target_committee_id',
        'status',
        'requested_by',
        'resolved_by',
        'resolved_at',
        'decision_notes',
    ];

    protected function casts(): array
    {
        return [
            'rule_snapshot' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function doaRule(): BelongsTo
    {
        return $this->belongsTo(DoaThresholdRule::class, 'doa_rule_id');
    }

    public function targetRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'target_role_id');
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function targetCommittee(): BelongsTo
    {
        return $this->belongsTo(Committee::class, 'target_committee_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(CommitteeVote::class, 'approval_request_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Onay Bekliyor',
            'approved' => 'Onaylandı',
            'rejected' => 'Reddedildi',
            'bypassed_auto' => 'Doğrudan Devreye Alındı (Hızlı Onay)',
            default => ucfirst((string)$this->status),
        };
    }
}
