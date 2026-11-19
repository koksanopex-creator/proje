<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DoaThresholdRule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'slug',
        'name',
        'rule_type',
        'currency_code',
        'min_amount',
        'max_amount',
        'requires_approval',
        'approver_type',
        'role_id',
        'user_id',
        'committee_id',
        'role_group_slug',
        'project_category_slug',
        'bolum_id',
        'priority_order',
        'is_active',
        'description',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'min_amount' => 'decimal:2',
            'max_amount' => 'decimal:2',
            'requires_approval' => 'boolean',
            'is_active' => 'boolean',
            'priority_order' => 'integer',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class, 'committee_id');
    }

    public function bolum(): BelongsTo
    {
        return $this->belongsTo(Bolum::class, 'bolum_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvalRequests(): HasMany
    {
        return $this->hasMany(ProjectApprovalRequest::class, 'doa_rule_id');
    }

    public function getApproverNameAttribute(): string
    {
        if (!$this->requires_approval) {
            return 'Doğrudan Devreye Alma (Hızlı Onay / Onaysız)';
        }

        return match ($this->approver_type) {
            'role' => $this->role?->name ?? 'Belirtilmemiş Rol',
            'user' => $this->user?->name ?? 'Belirtilmemiş Kişi',
            'committee' => $this->committee?->name ?? 'Belirtilmemiş Kurul',
            'team' => 'Takım: ' . ($this->role_group_slug ?? 'Genel'),
            default => 'Varsayılan Hiyerarşi',
        };
    }

    public function getAmountRangeFormattedAttribute(): string
    {
        $curr = $this->currency_code ?? 'USD';
        $min = number_format((float)$this->min_amount, 0, ',', '.');
        
        if ($this->max_amount === null) {
            return "{$min} {$curr} ve Üzeri";
        }

        $max = number_format((float)$this->max_amount, 0, ',', '.');
        return "{$min} - {$max} {$curr}";
    }
}
