<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class ProjectBaseline extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'version',
        'name',
        'description',
        'is_active',
        'total_planned_budget',
        'total_planned_hours',
        'planned_start_date',
        'planned_end_date',
        'snapshot_data',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'total_planned_budget' => 'decimal:2',
            'total_planned_hours' => 'decimal:2',
            'planned_start_date' => 'date',
            'planned_end_date' => 'date',
            'snapshot_data' => 'array',
            'approved_at' => 'datetime',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks()
    {
        return $this->hasMany(ProjectBaselineTask::class, 'baseline_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * PMI / Earned Value Management (EVM) Metriklerini Hesaplar
     */
    public function calculateEvm(): array
    {
        $project = $this->project;
        $bac = (float) ($this->total_planned_budget > 0 ? $this->total_planned_budget : ($project->planned_budget ?? 0));
        $actualCost = (float) ($project->actual_spent ?? 0);
        $progressPct = (float) ($project->progress_percentage ?? 0);

        // Earned Value (EV) = Tamamlanma Yüzdesi * BAC
        $ev = round(($progressPct / 100) * $bac, 2);

        // Planned Value (PV) = Planlanan zaman akışına göre bugün harcanmış olması beklenen tutar
        $startDate = $this->planned_start_date ? Carbon::parse($this->planned_start_date) : ($project->start_date ? Carbon::parse($project->start_date) : now());
        $endDate = $this->planned_end_date ? Carbon::parse($this->planned_end_date) : ($project->target_end_date ? Carbon::parse($project->target_end_date) : now()->addMonths(1));
        
        $totalDays = max(1, $startDate->diffInDays($endDate));
        $now = now();
        
        if ($now->lessThan($startDate)) {
            $timeRatio = 0.0;
        } elseif ($now->greaterThanOrEqualTo($endDate)) {
            $timeRatio = 1.0;
        } else {
            $elapsedDays = $startDate->diffInDays($now);
            $timeRatio = min(1.0, max(0.0, $elapsedDays / $totalDays));
        }

        $pv = round($timeRatio * $bac, 2);

        // Schedule Variance (SV) = EV - PV
        $sv = round($ev - $pv, 2);

        // Cost Variance (CV) = EV - AC
        $cv = round($ev - $actualCost, 2);

        // Schedule Performance Index (SPI) = EV / PV
        $spi = $pv > 0 ? round($ev / $pv, 2) : 1.0;

        // Cost Performance Index (CPI) = EV / AC
        $cpi = $actualCost > 0 ? round($ev / $actualCost, 2) : 1.0;

        // Durum Değerlendirmeleri
        $scheduleStatus = 'on_track';
        if ($sv < -($bac * 0.05)) {
            $scheduleStatus = 'behind'; // %5'ten fazla geride
        } elseif ($sv > ($bac * 0.05)) {
            $scheduleStatus = 'ahead'; // %5'ten fazla ileride
        }

        $costStatus = 'on_budget';
        if ($cv < -($bac * 0.05)) {
            $costStatus = 'over_budget'; // Bütçe aşımı riski
        } elseif ($cv > ($bac * 0.05)) {
            $costStatus = 'under_budget'; // Bütçe tasarrufu
        }

        return [
            'bac' => $bac,
            'pv' => $pv,
            'ev' => $ev,
            'ac' => $actualCost,
            'sv' => $sv,
            'cv' => $cv,
            'spi' => $spi,
            'cpi' => $cpi,
            'schedule_status' => $scheduleStatus,
            'cost_status' => $costStatus,
            'time_ratio_pct' => round($timeRatio * 100, 1),
            'progress_pct' => $progressPct,
        ];
    }
}
