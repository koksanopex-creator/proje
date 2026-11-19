<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProjectDraftShare extends Model
{
    protected $fillable = [
        'project_id',
        'email',
        'token',
        'passkey',
        'shared_by_user_id',
        'shared_by_role_name',
        'note',
        'status',
        'expires_at',
        'last_accessed_at',
        'access_count',
        'revoked_at',
        'revoked_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_accessed_at' => 'datetime',
            'revoked_at' => 'datetime',
            'access_count' => 'integer',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function sharedBy()
    {
        return $this->belongsTo(User::class, 'shared_by_user_id');
    }

    public function revokedBy()
    {
        return $this->belongsTo(User::class, 'revoked_by_user_id');
    }

    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function revoke(User $user): void
    {
        $this->update([
            'status' => 'revoked',
            'revoked_at' => now(),
            'revoked_by_user_id' => $user->id,
        ]);
    }

    public static function generateSecureToken(): string
    {
        return Str::random(48);
    }

    public static function generatePasskey(): string
    {
        return 'KKS-' . strtoupper(Str::random(6));
    }

    public function getPublicAccessUrlAttribute(): string
    {
        return route('projects.external_draft_review', [
            'project' => $this->project_id,
            'token' => $this->token,
            'passkey' => $this->passkey,
        ]);
    }
}
