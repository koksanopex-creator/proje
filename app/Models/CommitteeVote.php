<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommitteeVote extends Model
{
    protected $fillable = [
        'approval_request_id',
        'committee_id',
        'user_id',
        'vote',
        'weight',
        'notes',
        'voted_at',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'voted_at' => 'datetime',
        ];
    }

    public function approvalRequest(): BelongsTo
    {
        return $this->belongsTo(ProjectApprovalRequest::class, 'approval_request_id');
    }

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class, 'committee_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getVoteLabelAttribute(): string
    {
        return match ($this->vote) {
            'approve' => 'Onay',
            'reject' => 'Ret',
            'conditional_approve' => 'Şerhli Onay',
            'abstain' => 'Çekimser',
            default => ucfirst((string)$this->vote),
        };
    }
}
