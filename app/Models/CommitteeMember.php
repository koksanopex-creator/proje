<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommitteeMember extends Model
{
    protected $fillable = [
        'committee_id',
        'user_id',
        'member_role',
        'voting_weight',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'voting_weight' => 'decimal:2',
        ];
    }

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class, 'committee_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->member_role) {
            'baskan' => 'Kurul Başkanı',
            'uye' => 'Kurul Üyesi',
            'raportor' => 'Raportör',
            'yedek' => 'Yedek Üye',
            default => ucfirst((string)$this->member_role),
        };
    }
}
