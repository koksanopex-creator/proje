<?php

namespace App\Services;

use App\Models\Project;
use App\Models\DoaThresholdRule;
use App\Models\ProjectApprovalRequest;
use App\Models\SystemSetting;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DoaThresholdService
{
    /**
     * Bütçe Yetki Devri (DOA) motoru genel olarak aktif mi?
     */
    public function isEngineActive(): bool
    {
        $setting = SystemSetting::where('key', 'doa_budget_approval_engine_active')->value('value');
        if ($setting === null) {
            return true; // Varsayılan aktif
        }
        return (bool)$setting;
    }

    /**
     * Eşik kıyaslamasında kullanılacak taban para birimini döner.
     */
    public function getBaseCurrency(): string
    {
        return SystemSetting::where('key', 'doa_base_currency')->value('value') ?? 'USD';
    }

    /**
     * Verilen tutarı sistem döviz kurlarını kullanarak baz para birimine çevirir.
     */
    public function convertToBase(float $amount, string $fromCurrency, ?string $baseCurrency = null): float
    {
        $baseCurrency = $baseCurrency ?? $this->getBaseCurrency();
        $fromCurrency = strtoupper(trim($fromCurrency));
        $baseCurrency = strtoupper(trim($baseCurrency));

        if ($fromCurrency === $baseCurrency || $amount <= 0) {
            return $amount;
        }

        // Aktif döviz kurlarını oku
        $currencies = Currency::whereIn('code', [$fromCurrency, $baseCurrency])->get()->keyBy('code');
        
        $fromRate = isset($currencies[$fromCurrency]) ? (float)$currencies[$fromCurrency]->exchange_rate : 1.0;
        $baseRate = isset($currencies[$baseCurrency]) ? (float)$currencies[$baseCurrency]->exchange_rate : 1.0;

        // TRY bazlı kurlar varsayımıyla: Tutar * fromRate / baseRate
        if ($baseRate <= 0) $baseRate = 1.0;
        if ($fromRate <= 0) $fromRate = 1.0;

        return round(($amount * $fromRate) / $baseRate, 2);
    }

    /**
     * Projeye ve bütçesine uyan en uygun DOA eşik kuralını bulur.
     */
    public function matchRule(Project $project, string $ruleType = 'project_approval'): ?DoaThresholdRule
    {
        if (!$this->isEngineActive()) {
            return null;
        }

        $baseCurrency = $this->getBaseCurrency();
        $projectBudget = (float)($project->planned_budget ?? 0);
        $projectCurrency = $project->currency ?? 'TRY';
        $baseAmount = $this->convertToBase($projectBudget, $projectCurrency, $baseCurrency);

        // Kural arama sorgusu
        $query = DoaThresholdRule::with(['role', 'user', 'committee.members', 'bolum'])
            ->where('is_active', true)
            ->where('rule_type', $ruleType)
            ->where('min_amount', '<=', $baseAmount)
            ->where(function ($q) use ($baseAmount) {
                $q->whereNull('max_amount')
                  ->orWhere('max_amount', '>=', $baseAmount);
            });

        // Kategori filtresi eşleşmesi (opsiyonel)
        $projectCat = $project->category;
        $query->where(function ($q) use ($projectCat) {
            $q->whereNull('project_category_slug');
            if ($projectCat) {
                $q->orWhere('project_category_slug', $projectCat);
            }
        });

        // Departman filtresi eşleşmesi (opsiyonel)
        $projectDeptId = $project->bolum_id;
        $query->where(function ($q) use ($projectDeptId) {
            $q->whereNull('bolum_id');
            if ($projectDeptId) {
                $q->orWhere('bolum_id', $projectDeptId);
            }
        });

        return $query->orderBy('priority_order', 'asc')
            ->orderBy('min_amount', 'desc')
            ->first();
    }

    /**
     * Projeyi onay sürecine gönderir, snapshot oluşturur ve ilgili onaycıya/kurula yönlendirir.
     */
    public function submitProjectForApproval(Project $project, ?User $requester = null): ProjectApprovalRequest
    {
        return DB::transaction(function () use ($project, $requester) {
            $requester = $requester ?? auth()->user();
            $baseCurrency = $this->getBaseCurrency();
            $projectBudget = (float)($project->planned_budget ?? 0);
            $projectCurrency = $project->currency ?? 'TRY';
            $baseAmount = $this->convertToBase($projectBudget, $projectCurrency, $baseCurrency);

            $rule = $this->matchRule($project, 'project_approval');

            // 1. Snapshot Verisi Hazırla ("O an olan olmuştur, geçmiş kural değişse de etkilenmez")
            $snapshot = [
                'engine_active' => $this->isEngineActive(),
                'matched_rule_id' => $rule?->id,
                'matched_rule_slug' => $rule?->slug,
                'matched_rule_name' => $rule?->name ?? 'Standart Onay Rotası',
                'rule_type' => $rule?->rule_type ?? 'project_approval',
                'project_budget' => $projectBudget,
                'project_currency' => $projectCurrency,
                'base_currency' => $baseCurrency,
                'base_amount' => $baseAmount,
                'min_amount' => $rule ? (float)$rule->min_amount : null,
                'max_amount' => $rule && $rule->max_amount !== null ? (float)$rule->max_amount : null,
                'requires_approval' => $rule ? (bool)$rule->requires_approval : true,
                'approver_type' => $rule?->approver_type ?? 'role',
                'target_role_id' => $rule?->role_id,
                'target_role_name' => $rule?->role?->name,
                'target_user_id' => $rule?->user_id,
                'target_user_name' => $rule?->user?->name,
                'target_committee_id' => $rule?->committee_id,
                'target_committee_name' => $rule?->committee?->name,
                'committee_voting_type' => $rule?->committee?->voting_type,
                'committee_quorum' => $rule?->committee?->quorum_percentage,
                'timestamp' => now()->toIso8601String(),
            ];

            // 2. Onay Talebi Kaydını Oluştur
            $approvalRequest = ProjectApprovalRequest::create([
                'project_id' => $project->id,
                'doa_rule_id' => $rule?->id,
                'rule_snapshot' => $snapshot,
                'target_approver_type' => $rule ? $rule->approver_type : 'role',
                'target_role_id' => $rule?->role_id,
                'target_user_id' => $rule?->user_id,
                'target_committee_id' => $rule?->committee_id,
                'status' => 'pending',
                'requested_by' => $requester?->id,
            ]);

            $notifService = app(NotificationMatrixService::class);
            $actionUrl = route('projects.show', $project->id);

            // DURUM A: Doğrudan Devreye Alma (Fast-track / Oto Onay)
            if ($rule && !$rule->requires_approval) {
                $approvalRequest->update([
                    'status' => 'bypassed_auto',
                    'resolved_at' => now(),
                    'decision_notes' => "Proje bütçesi ({$projectBudget} {$projectCurrency} = {$baseAmount} {$baseCurrency}) hızlı onay eşiğinde ({$rule->name}) olduğu için sistem tarafından otomatik devreye alındı.",
                ]);

                $project->update([
                    'status' => 'in_progress',
                    'submitted_for_approval_at' => now(),
                    'approved_at' => now(),
                    'current_waiting_role_id' => null,
                    'current_waiting_user_id' => null,
                    'waiting_since' => null,
                    'waiting_step_name' => null,
                ]);

                AuditLogService::log(
                    'auto_approved',
                    "Proje '{$rule->name}' eşik kuralı kapsamında doğrudan devreye alındı.",
                    'Project',
                    $project->id,
                    $project->id,
                    null,
                    ['status' => 'in_progress', 'doa_rule' => $rule->slug]
                );

                // Bildirim tetikle
                $notifService->dispatch('proje_hizli_onaylandi', [
                    'proje_adi' => $project->title,
                    'proje_kodu' => $project->code,
                    'tutar' => number_format($projectBudget, 2, ',', '.') . " {$projectCurrency}",
                    'kural_adi' => $rule->name,
                ], $actionUrl, $project->manager_id);

                return $approvalRequest;
            }

            // DURUM B: Kurul Oylamasına Yönlendirme
            if ($rule && $rule->approver_type === 'committee' && $rule->committee) {
                $committee = $rule->committee;

                $project->update([
                    'status' => 'waiting_approval',
                    'submitted_for_approval_at' => now(),
                    'current_waiting_role_id' => null,
                    'current_waiting_user_id' => null,
                    'waiting_since' => now(),
                    'waiting_step_name' => "{$committee->name} Oylaması Bekleniyor",
                ]);

                AuditLogService::log(
                    'committee_approval_pending',
                    "Proje '{$committee->name}' onay kuruluna sevk edildi. Karar modeli: {$committee->voting_type_label}",
                    'Project',
                    $project->id,
                    $project->id,
                    null,
                    ['committee_id' => $committee->id, 'waiting_step' => $project->waiting_step_name]
                );

                // Kurul üyelerine bildirim gönder
                foreach ($committee->activeMembers as $member) {
                    $notifService->dispatch('proje_kurul_oylamasina_dustu', [
                        'proje_adi' => $project->title,
                        'proje_kodu' => $project->code,
                        'kurul_adi' => $committee->name,
                        'tutar' => number_format($projectBudget, 2, ',', '.') . " {$projectCurrency}",
                        'oylama_modeli' => $committee->voting_type_label,
                    ], $actionUrl, $member->user_id);
                }

                return $approvalRequest;
            }

            // DURUM C: Tekil Rol veya Tekil Kişi Onayına Yönlendirme
            $waitingRoleId = $rule?->role_id ?? ($project->current_waiting_role_id ?? 8); // Fallback direktör
            $waitingUserId = $rule?->user_id ?? null;
            $stepName = $rule ? "{$rule->name} Onayı Bekleniyor" : 'Yönetim Bütçe Onayı Bekleniyor';

            $project->update([
                'status' => 'waiting_approval',
                'submitted_for_approval_at' => now(),
                'current_waiting_role_id' => $waitingRoleId,
                'current_waiting_user_id' => $waitingUserId,
                'waiting_since' => now(),
                'waiting_step_name' => $stepName,
            ]);

            AuditLogService::log(
                'status_changed',
                "Proje '{$stepName}' aşamasına gönderildi. Bütçe Eşik Kuralı: " . ($rule ? $rule->name : 'Standart'),
                'Project',
                $project->id,
                $project->id,
                null,
                ['waiting_role_id' => $waitingRoleId, 'waiting_user_id' => $waitingUserId]
            );

            // Bildirim tetikle
            $notifService->dispatch('proje_doa_esik_onayi', [
                'proje_adi' => $project->title,
                'proje_kodu' => $project->code,
                'tutar' => number_format($projectBudget, 2, ',', '.') . " {$projectCurrency}",
                'kural_adi' => $rule?->name ?? 'Bütçe Eşiği',
            ], $actionUrl, $waitingUserId);

            return $approvalRequest;
        });
    }
}
