<?php

namespace App\Services;

use App\Models\ProjectApprovalRequest;
use App\Models\CommitteeVote;
use App\Models\Committee;
use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Exception;

class CommitteeVotingService
{
    /**
     * Kurul üyesinin oyunu kaydeder ve karar nisabını yeniden değerlendirir.
     */
    public function recordVote(
        ProjectApprovalRequest $request,
        User $user,
        string $vote,
        ?string $notes = null
    ): array {
        if ($request->status !== 'pending') {
            throw new Exception("Bu onay talebi zaten sonuçlandırılmıştır ({$request->status_label}).");
        }

        $committee = $request->targetCommittee;
        if (!$committee) {
            throw new Exception("Onay talebine atanmış geçerli bir kurul bulunamadı.");
        }

        // Kullanıcı kurul üyesi mi veya Superadmin mi? (Madde 15 Tam Yetki)
        $member = $committee->activeMembers()->where('user_id', $user->id)->first();
        if (!$member && !$user->isSuperAdmin()) {
            throw new Exception("Bu kurulda oy kullanma yetkiniz bulunmamaktadır.");
        }

        $votingWeight = $member ? (float)$member->voting_weight : 1.00;

        return DB::transaction(function () use ($request, $committee, $user, $vote, $votingWeight, $notes) {
            // Oyu kaydet veya güncelle
            $voteRecord = CommitteeVote::updateOrCreate(
                [
                    'approval_request_id' => $request->id,
                    'user_id' => $user->id,
                ],
                [
                    'committee_id' => $committee->id,
                    'vote' => $vote,
                    'weight' => $votingWeight,
                    'notes' => $notes,
                    'voted_at' => now(),
                ]
            );

            // Denetim logu yaz
            AuditLogService::log(
                'committee_voted',
                "Kullanıcı '{$user->name}', '{$committee->name}' kurulunda '{$voteRecord->vote_label}' oyu verdi.",
                'CommitteeVote',
                $voteRecord->id,
                $request->project_id,
                null,
                ['vote' => $vote, 'weight' => $votingWeight, 'notes' => $notes]
            );

            // Karar yeter sayısını hesapla
            $evaluation = $this->evaluateDecision($request);

            return [
                'success' => true,
                'vote' => $voteRecord,
                'evaluation' => $evaluation,
            ];
        });
    }

    /**
     * Kurul oylamasını değerlendirir, nisap sağlandıysa onay veya ret sürecini tamamlar.
     */
    public function evaluateDecision(ProjectApprovalRequest $request): array
    {
        $committee = $request->targetCommittee;
        if (!$committee) {
            return ['finalized' => false, 'reason' => 'Kurul bulunamadı'];
        }

        $activeMembers = $committee->activeMembers()->with('user')->get();
        $totalMembersCount = $activeMembers->count();
        $totalWeight = (float)$activeMembers->sum('voting_weight');

        if ($totalWeight <= 0) {
            $totalWeight = $totalMembersCount > 0 ? (float)$totalMembersCount : 1.0;
        }

        $votes = $request->votes()->with('user')->get();
        $votesCount = $votes->count();

        $approveWeight = (float)$votes->whereIn('vote', ['approve', 'conditional_approve'])->sum('weight');
        $rejectWeight = (float)$votes->where('vote', 'reject')->sum('weight');
        $abstainWeight = (float)$votes->where('vote', 'abstain')->sum('weight');

        $approveCount = $votes->whereIn('vote', ['approve', 'conditional_approve'])->count();
        $rejectCount = $votes->where('vote', 'reject')->count();
        $abstainCount = $votes->where('vote', 'abstain')->count();

        $votingType = $committee->voting_type ?? 'majority';
        $quorumPct = (int)($committee->quorum_percentage ?? 51);

        $isApproved = false;
        $isRejected = false;
        $decisionReason = null;

        switch ($votingType) {
            case 'single_any':
                // Herhangi bir üyeden onay geldiği anda karar çıkar
                if ($approveCount > 0) {
                    $isApproved = true;
                    $decisionReason = "Kurul karar modeli gereği tek onay yeterli görüldü.";
                } elseif ($rejectCount >= $totalMembersCount) {
                    $isRejected = true;
                    $decisionReason = "Tüm kurul üyeleri ret oyu kullandı.";
                }
                break;

            case 'unanimous':
                // Oy birliği: Tek bir ret reddettirir; herkes onaylamalıdır
                if ($rejectCount > 0) {
                    $isRejected = true;
                    $decisionReason = "Oy birliği modelinde ret oyu kullanıldığı için talep reddedildi.";
                } elseif ($approveCount >= $totalMembersCount) {
                    $isApproved = true;
                    $decisionReason = "Tüm üyelerin oy birliği ile kabul edildi.";
                }
                break;

            case 'quorum_percentage':
                // Belirli bir nisap yüzdesi
                $ratio = ($approveWeight / $totalWeight) * 100;
                if ($ratio >= $quorumPct) {
                    $isApproved = true;
                    $decisionReason = "Onay oranı (%{$ratio}), belirlenen nisap eşiğini (%{$quorumPct}) aştı.";
                } elseif (($rejectWeight / $totalWeight) * 100 > (100 - $quorumPct)) {
                    $isRejected = true;
                    $decisionReason = "Ret oranı, kalan oylarla kabul edilme imkanını ortadan kaldırdı.";
                }
                break;

            case 'majority':
            default:
                // Salt çoğunluk (%50 + 1 oy ağırlığı)
                if ($approveWeight > ($totalWeight / 2)) {
                    $isApproved = true;
                    $decisionReason = "Oy çokluğu (%50'den fazla onay ağırlığı) sağlandı.";
                } elseif ($rejectWeight >= ($totalWeight / 2)) {
                    $isRejected = true;
                    $decisionReason = "Üyelerin çoğunluğu ret oyu verdi.";
                }
                break;
        }

        // Eğer karar kesinleştiyse durumları güncelle
        if ($isApproved || $isRejected) {
            $finalStatus = $isApproved ? 'approved' : 'rejected';
            $finalStatusLabel = $isApproved ? 'Onaylandı' : 'Reddedildi';

            $request->update([
                'status' => $finalStatus,
                'resolved_at' => now(),
                'resolved_by' => auth()->id(),
                'decision_notes' => $decisionReason,
            ]);

            $project = $request->project;
            if ($project) {
                if ($isApproved) {
                    $project->update([
                        'status' => 'in_progress',
                        'current_waiting_role_id' => null,
                        'current_waiting_user_id' => null,
                        'waiting_since' => null,
                        'waiting_step_name' => null,
                    ]);
                } else {
                    $project->update([
                        'status' => 'draft',
                        'current_waiting_role_id' => null,
                        'current_waiting_user_id' => null,
                        'waiting_since' => null,
                        'waiting_step_name' => 'Kurul Tarafından Reddedildi (Revizyon Bekleniyor)',
                    ]);
                }

                AuditLogService::log(
                    'committee_decision_finalized',
                    "'{$committee->name}' oylaması sonuçlandı: Proje {$finalStatusLabel}. {$decisionReason}",
                    'Project',
                    $project->id,
                    $project->id,
                    null,
                    ['status' => $project->status, 'decision' => $finalStatus, 'reason' => $decisionReason]
                );

                // Bildirim Matrisi Tetikle
                $notifService = app(NotificationMatrixService::class);
                $actionUrl = route('projects.show', $project->id);

                $notifService->dispatch('proje_kurul_karar_verildi', [
                    'proje_adi' => $project->title,
                    'proje_kodu' => $project->code,
                    'kurul_adi' => $committee->name,
                    'karar' => $finalStatusLabel,
                    'gerekce' => $decisionReason,
                ], $actionUrl, $project->manager_id);
            }

            return [
                'finalized' => true,
                'status' => $finalStatus,
                'reason' => $decisionReason,
                'approve_weight' => $approveWeight,
                'reject_weight' => $rejectWeight,
                'total_weight' => $totalWeight,
            ];
        }

        return [
            'finalized' => false,
            'pending_reason' => 'Oylama devam ediyor (yeterli nisaba henüz ulaşılamadı).',
            'approve_weight' => $approveWeight,
            'reject_weight' => $rejectWeight,
            'total_weight' => $totalWeight,
            'votes_count' => $votesCount,
            'total_members' => $totalMembersCount,
        ];
    }
}
