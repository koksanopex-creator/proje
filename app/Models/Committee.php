<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Committee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'voting_type',
        'quorum_percentage',
        'color',
        'icon',
        'authorities',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'quorum_percentage' => 'integer',
            'authorities' => 'array',
        ];
    }

    public function hasAuthority(string $key): bool
    {
        return !empty($this->authorities[$key]);
    }

    public function members(): HasMany
    {
        return $this->hasMany(CommitteeMember::class, 'committee_id');
    }

    public function activeMembers(): HasMany
    {
        return $this->hasMany(CommitteeMember::class, 'committee_id')->where('is_active', true);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'committee_members', 'committee_id', 'user_id')
            ->withPivot(['id', 'member_role', 'voting_weight', 'is_active'])
            ->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvalRequests(): HasMany
    {
        return $this->hasMany(ProjectApprovalRequest::class, 'target_committee_id');
    }

    public function getVotingTypeLabelAttribute(): string
    {
        return match ($this->voting_type) {
            'unanimous' => 'Oy Birliği (Tüm Üyeler)',
            'majority' => 'Oy Çokluğu (%50 + 1)',
            'single_any' => 'Herhangi Bir Üye Onayı Yeterli',
            'quorum_percentage' => "%{$this->quorum_percentage} Nisap Oranı",
            default => 'Oy Çokluğu',
        };
    }
}
