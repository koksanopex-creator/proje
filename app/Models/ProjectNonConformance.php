<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectNonConformance extends Model
{
    use HasFactory;

    protected $table = 'project_non_conformances';

    protected $fillable = [
        'project_id',
        'quality_id',
        'ncr_code',
        'title',
        'severity',
        'status',
        'assigned_to',
        'due_date',
        'description',
        'corrective_action',
        'resolution_notes',
        'evidence_file',
        'resolved_at',
        'closed_by',
        'closed_at',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public static function severities(): array
    {
        return [
            'critical' => [
                'label' => 'Kritik / Durdurucu',
                'color' => '#dc2626',
                'bg' => '#fee2e2',
                'badge' => '🚨 Kritik',
            ],
            'major' => [
                'label' => 'Majör / Önemli',
                'color' => '#ea580c',
                'bg' => '#ffedd5',
                'badge' => '⚠️ Majör',
            ],
            'minor' => [
                'label' => 'Minör / Kusur',
                'color' => '#0284c7',
                'bg' => '#e0f2fe',
                'badge' => 'ℹ️ Minör',
            ],
        ];
    }

    public static function statuses(): array
    {
        return [
            'open' => [
                'label' => 'Açık / Tespit Edildi',
                'color' => '#dc2626',
                'bg' => '#fee2e2',
            ],
            'in_progress' => [
                'label' => 'Aksiyon Alınıyor',
                'color' => '#d97706',
                'bg' => '#fef3c7',
            ],
            'resolved' => [
                'label' => 'Düzeltildi / Doğrulama Bekliyor',
                'color' => '#0284c7',
                'bg' => '#e0f2fe',
            ],
            'closed' => [
                'label' => 'Kapatıldı / Kabul Onaylı',
                'color' => '#16a34a',
                'bg' => '#dcfce7',
            ],
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function quality()
    {
        return $this->belongsTo(ProjectQuality::class, 'quality_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getSeverityBadgeAttribute(): array
    {
        $severities = self::severities();
        return $severities[$this->severity] ?? [
            'label' => ucfirst($this->severity),
            'color' => '#64748b',
            'bg' => '#f1f5f9',
            'badge' => '•'
        ];
    }

    public function getStatusBadgeAttribute(): array
    {
        $statuses = self::statuses();
        return $statuses[$this->status] ?? [
            'label' => ucfirst($this->status),
            'color' => '#64748b',
            'bg' => '#f1f5f9',
        ];
    }
}
