<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectPhase;
use App\Models\User;
use Carbon\Carbon;

class ProjectPhaseService
{
    /**
     * Projeye yeni faz/aşama ekler
     */
    public function addPhase(Project $project, array $data, User $user): ProjectPhase
    {
        $maxSort = $project->phases()->max('sort_order') ?? 0;

        $phase = $project->phases()->create([
            'name' => $data['name'],
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'requires_ehs_approval' => !empty($data['requires_ehs_approval']),
            'sort_order' => $maxSort + 1,
            'status' => 'pending',
            'progress_percentage' => 0,
        ]);

        AuditLogService::log(
            'created',
            "Projeye yeni faz eklendi: **{$phase->name}**",
            'ProjectPhase',
            $phase->id,
            $project->id
        );

        return $phase;
    }

    /**
     * Fazı/aşamayı başarıyla tamamlandı olarak işaretler
     */
    public function completePhase(Project $project, ProjectPhase $phase, User $user): array
    {
        // İSG Kilidi Kontrolü
        if ($phase->requires_ehs_approval && !$phase->ehs_approved_at) {
            throw new \Exception("Bu aşama İSG Saha İzin Onayı gerektirmektedir. İSG onayı verilmeden tamamlanamaz!");
        }

        $phase->update([
            'status' => 'completed',
            'progress_percentage' => 100,
            'completed_by' => $user->id,
            'completed_at' => now(),
        ]);

        // Aşama altındaki tüm görevleri tamamlandı olarak güncelle
        $phase->tasks()->update([
            'status' => 'done',
            'progress_percentage' => 100,
        ]);

        $overallProgress = $this->recalculateOverallProgress($project);

        AuditLogService::log(
            'completed',
            "Proje adımı tamamlandı: **{$phase->name}**",
            'ProjectPhase',
            $phase->id,
            $project->id
        );

        // Bildirim Matrisi ile Paydaşlara Bilgi Ver
        app(NotificationMatrixService::class)->dispatch('proje_adimi_tamamlandi', [
            'proje_adi' => $project->title,
            'proje_kodu' => $project->code,
            'adim_adi' => $phase->name,
            'ilerleme' => $overallProgress,
            'tamamlayan' => $user->name,
        ], route('projects.show', $project->id));

        return [
            'phase' => $phase,
            'overall_progress' => $overallProgress,
            'message' => "Proje adımı ('{$phase->name}') ve aşama görevleri başarıyla tamamlandı.",
        ];
    }

    /**
     * Tamamlanan aşamayı geri alır (Rollback)
     */
    public function revertPhase(Project $project, ProjectPhase $phase, User $user): array
    {
        $phase->update([
            'status' => 'pending',
            'progress_percentage' => 0,
            'completed_by' => null,
            'completed_at' => null,
        ]);

        // Aşama altındaki görevleri yapılacak durumuna döndür
        $phase->tasks()->update([
            'status' => 'todo',
            'progress_percentage' => 0,
        ]);

        $overallProgress = $this->recalculateOverallProgress($project);

        AuditLogService::log(
            'reverted',
            "Proje adımı geri alındı: **{$phase->name}**",
            'ProjectPhase',
            $phase->id,
            $project->id
        );

        return [
            'phase' => $phase,
            'overall_progress' => $overallProgress,
            'message' => "Proje adımı ('{$phase->name}') başarıyla geri alındı.",
        ];
    }

    /**
     * İSG Kapı Onayı Verme
     */
    public function approveEhsGate(Project $project, ProjectPhase $phase, User $user): ProjectPhase
    {
        $phase->update([
            'ehs_approved_at' => now(),
            'ehs_approved_by' => $user->id,
        ]);

        AuditLogService::log(
            'gatekeeper_approval',
            "İSG Kapı Kilidi Onaylandı: **{$phase->name}**",
            'ProjectPhase',
            $phase->id,
            $project->id
        );

        return $phase;
    }

    /**
     * İSG Kapı Onayı Geri Alma
     */
    public function revertEhsGate(Project $project, ProjectPhase $phase, User $user): ProjectPhase
    {
        $phase->update([
            'ehs_approved_at' => null,
            'ehs_approved_by' => null,
        ]);

        AuditLogService::log(
            'gatekeeper_reverted',
            "İSG Kapı Kilidi Geri Alındı: **{$phase->name}**",
            'ProjectPhase',
            $phase->id,
            $project->id
        );

        return $phase;
    }

    /**
     * Proje genel ilerlemesini faz ve görevlere göre günceller
     */
    public function recalculateOverallProgress(Project $project): int
    {
        $totalPhases = $project->phases()->count();
        $completedPhases = $project->phases()->where('status', 'completed')->count();
        $overallProgress = $totalPhases > 0 ? (int) round(($completedPhases / $totalPhases) * 100) : 0;
        
        $project->update(['progress_percentage' => $overallProgress]);

        return $overallProgress;
    }

    /**
     * Kategoriye göre standart varsayılan fazları oluşturur
     */
    public function createDefaultPhasesForCategory(Project $project, string $categoryName, array $dates = []): void
    {
        $startDate = !empty($dates['start_date']) 
            ? Carbon::parse($dates['start_date']) 
            : ($project->start_date ? Carbon::parse($project->start_date) : now());

        $targetEndDate = !empty($dates['target_end_date']) 
            ? Carbon::parse($dates['target_end_date']) 
            : ($project->target_end_date ? Carbon::parse($project->target_end_date) : $startDate->copy()->addMonths(6));

        $totalDays = max(30, (int) round($startDate->diffInDays($targetEndDate)));

        $defaultPhases = [
            'ges_enerji' => [
                ['name' => '1. Aşama: Saha Hazırlığı & Statik Projeler', 'pct' => 0.20, 'ehs' => false],
                ['name' => '2. Aşama: İSG Yüksekte Çalışma & Çelik Konstrüksiyon', 'pct' => 0.35, 'ehs' => true],
                ['name' => '3. Aşama: Güneş Panelleri & İnverter Entegrasyonu', 'pct' => 0.30, 'ehs' => false],
                ['name' => '4. Aşama: TEDAŞ Kabulü, Test & Devreye Alma', 'pct' => 0.15, 'ehs' => true],
            ],
            'pet_ambalaj' => [
                ['name' => '1. Faz: Ürün Tasarımı & Kalıp Fizibilitesi', 'pct' => 0.25, 'ehs' => false],
                ['name' => '2. Faz: Preform Makine Siparişi & Gümrük Kabulü', 'pct' => 0.35, 'ehs' => false],
                ['name' => '3. Faz: Montaj, Chiller Soğutma & İSG İzinleri', 'pct' => 0.25, 'ehs' => true],
                ['name' => '4. Faz: Seri Üretim Doğrulama & Kalite Onayı', 'pct' => 0.15, 'ehs' => false],
            ],
            'default' => [
                ['name' => '1. Aşama: Proje Başlatma & Kapsam Netleştirme', 'pct' => 0.20, 'ehs' => false],
                ['name' => '2. Aşama: Mühendislik, Tedarik & İSG Değerlendirmesi', 'pct' => 0.40, 'ehs' => true],
                ['name' => '3. Aşama: Uygulama, Kurulum & Montaj', 'pct' => 0.30, 'ehs' => false],
                ['name' => '4. Aşama: Kabul Testleri & Nihai Kapanış', 'pct' => 0.10, 'ehs' => false],
            ]
        ];

        $phaseTemplates = $defaultPhases[$categoryName] ?? $defaultPhases['default'];
        $currentStart = $startDate->copy();

        foreach ($phaseTemplates as $idx => $tmpl) {
            $phaseDays = max(5, (int) round($totalDays * $tmpl['pct']));
            $phaseEnd = $currentStart->copy()->addDays($phaseDays);

            $project->phases()->create([
                'name' => $tmpl['name'],
                'sort_order' => $idx + 1,
                'status' => 'pending',
                'progress_percentage' => 0,
                'start_date' => $currentStart->toDateString(),
                'end_date' => $phaseEnd->toDateString(),
                'requires_ehs_approval' => $tmpl['ehs'],
            ]);

            $currentStart = $phaseEnd->copy()->addDay();
        }
    }

    /**
     * Kilometre taşlarını (Milestones) ve Fazları projeye kaydeder.
     * Hem bağımsız ProjectPhase hem de Gantt/Kanban için ProjectTask üretir.
     */
    public function saveMilestonesToProject(Project $project, array $milestones): void
    {
        $projStart = $project->start_date ? Carbon::parse($project->start_date) : now();
        $runningStart = $projStart->copy();

        foreach ($milestones as $idx => $m) {
            $name = !empty($m['name']) ? trim($m['name']) : (!empty($m['title']) ? trim($m['title']) : null);
            if (!$name) continue;

            $criteria = !empty($m['completion_criteria']) ? trim($m['completion_criteria']) : (!empty($m['criteria']) ? trim($m['criteria']) : null);

            // Başlangıç tarihi
            if (!empty($m['start_date'])) {
                $startDate = Carbon::parse($m['start_date']);
            } else {
                $startDate = $runningStart->copy();
            }

            // Hedef / Bitiş tarihi
            if (!empty($m['target_date'])) {
                $endDate = Carbon::parse($m['target_date']);
            } elseif (!empty($m['date'])) {
                $endDate = Carbon::parse($m['date']);
            } elseif (!empty($m['end_date'])) {
                $endDate = Carbon::parse($m['end_date']);
            } else {
                $endDate = $startDate->copy()->addDays(30);
            }

            // 1. Kurumsal Proje Fazı (ProjectPhase) Oluştur
            $phase = $project->phases()->create([
                'name' => $name,
                'description' => $criteria,
                'sort_order' => $idx + 1,
                'status' => 'pending',
                'progress_percentage' => 0,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'requires_ehs_approval' => false,
            ]);

            // 2. Fazın Kilometre Taşı Görevini (ProjectTask - is_milestone) Oluştur
            $phase->tasks()->create([
                'project_id' => $project->id,
                'title' => $name,
                'description' => $criteria,
                'is_milestone' => true,
                'start_date' => $startDate->toDateString(),
                'due_date' => $endDate->toDateString(),
                'status' => 'todo',
                'progress_percentage' => 0,
                'priority' => 'high',
                'estimated_hours' => 0,
                'sort_order' => 1,
            ]);

            // Sonraki faz için dinamik başlangıç referansı
            $runningStart = $endDate->copy()->addDay();
        }
    }
}