<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectQuality extends Model
{
    use HasFactory;

    protected $table = 'project_qualities';

    protected $fillable = [
        'project_id',
        'phase_id',
        'task_id',
        'title',
        'check_type',
        'standard_code',
        'acceptance_criteria',
        'measured_value',
        'status',
        'checked_by',
        'approved_by',
        'checked_at',
        'notes',
        'certificate_file',
        'created_by',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
    ];

    // Sabit Türler ve Etiketler
    public static function checkTypes(): array
    {
        return [
            'test' => '🔬 Test & Ölçüm',
            'acceptance_gate' => '🚪 Kalite Kapısı (Stage-Gate)',
            'inspection' => '🔍 Saha / Görsel Muayene',
            'compliance' => '📋 Standart & Şartname Uygunluğu',
            'fat_sat' => '🏭 Fabrika / Saha Kabulü (FAT/SAT)',
        ];
    }

    public static function statuses(): array
    {
        return [
            'pending' => [
                'label' => 'Bekliyor / Planlandı',
                'color' => '#64748b',
                'bg' => '#f1f5f9',
                'icon' => '⏳'
            ],
            'passed' => [
                'label' => 'Başarılı / Kabul Edildi',
                'color' => '#15803d',
                'bg' => '#dcfce7',
                'icon' => '✓'
            ],
            'conditional' => [
                'label' => 'Şartlı Kabul (İstisnalı)',
                'color' => '#b45309',
                'bg' => '#fef3c7',
                'icon' => '⚠️'
            ],
            'failed' => [
                'label' => 'Başarısız / Reddedildi',
                'color' => '#b91c1c',
                'bg' => '#fee2e2',
                'icon' => '✕'
            ],
            'exempted' => [
                'label' => 'Muaf Tutuldu',
                'color' => '#475569',
                'bg' => '#e2e8f0',
                'icon' => '⊘'
            ],
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function phase()
    {
        return $this->belongsTo(ProjectPhase::class, 'phase_id');
    }

    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function nonConformances()
    {
        return $this->hasMany(ProjectNonConformance::class, 'quality_id');
    }

    public function getStatusBadgeAttribute(): array
    {
        $statuses = self::statuses();
        return $statuses[$this->status] ?? [
            'label' => ucfirst($this->status),
            'color' => '#64748b',
            'bg' => '#f1f5f9',
            'icon' => '•'
        ];
    }

    public function getCheckTypeLabelAttribute(): string
    {
        $types = self::checkTypes();
        return $types[$this->check_type] ?? $this->check_type;
    }
}
