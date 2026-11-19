<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectDraftShare;
use App\Models\ProjectDraftSuggestion;
use App\Models\User;

class ProjectDraftService
{
    /**
     * Taslak projeyi seçilen dahili paydaşlar ve/veya harici e-posta ile incelemeye açar
     */
    public function shareDraft(Project $project, array $reviewerIds = [], ?string $externalEmails = null, ?string $note = null, User $user = null): array
    {
        $notifService = app(NotificationMatrixService::class);
        $addedCount = 0;
        $reviewers = collect();

        // 1. Dahili Kullanıcı Paylaşımı (Zil + E-Posta)
        if (!empty($reviewerIds)) {
            $reviewers = User::whereIn('id', $reviewerIds)->get();

            foreach ($reviewers as $rev) {
                $exists = $project->stakeholders()
                    ->where('user_id', $rev->id)
                    ->where('role_type', 'reviewer')
                    ->exists();

                if (!$exists) {
                    $project->stakeholders()->create([
                        'user_id' => $rev->id,
                        'role_type' => 'reviewer',
                        'raci_role' => 'C',
                        'notes' => $note ?: 'Taslak inceleme paydaşı',
                    ]);
                    $addedCount++;
                }

                // Bildirim gönder
                $notifService->dispatch(
                    'project_draft_shared',
                    [
                        'project_title' => $project->title,
                        'project_code' => $project->code,
                        'shared_by' => $user->name,
                        'recipient_name' => $rev->name,
                        'custom_note' => $note ?: 'Proje taslağı incelemenize sunuldu.',
                    ],
                    route('projects.show', $project->id),
                    $rev->id
                );
            }

            if ($reviewers->isNotEmpty()) {
                AuditLogService::log(
                    'draft_shared',
                    "**{$user->name}**, **{$project->title}** taslağını inceleme için " . $reviewers->pluck('name')->map(fn($n) => "**{$n}**")->implode(', ') . " ile paylaştı. Not: \"" . ($note ?: 'Lütfen taslak detaylarını inceleyiniz.') . "\"",
                    'Project',
                    $project->id,
                    $project->id
                );
            }
        }

        // 2. Harici E-Posta Paylaşımı (Güvenli Geçiş Anahtarı & Token ile Sadece Bu Projeye Özel)
        $externalShares = [];
        if (!empty($externalEmails)) {
            $emails = array_filter(array_map('trim', explode(',', str_replace([';', "\n", "\r"], ',', $externalEmails))));

            foreach ($emails as $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    continue;
                }

                $token = ProjectDraftShare::generateSecureToken();
                $passkey = ProjectDraftShare::generatePasskey();

                $share = $project->draftShares()->create([
                    'email' => strtolower($email),
                    'token' => $token,
                    'passkey' => $passkey,
                    'shared_by_user_id' => $user->id,
                    'shared_by_role_name' => $user->roles->first()?->name ?? 'Kullanıcı',
                    'note' => $note,
                    'status' => 'active',
                    'expires_at' => now()->addDays(30),
                ]);

                // Bildirim matrisi üzerinden dış paydaşa özel e-posta gönderimi (Zil bildirimi hariç)
                $notifService->dispatchExternalDraftShare($project, $share, $user);

                // Denetim Günlüğü (Audit Log) Kaydı
                AuditLogService::log(
                    'draft_external_shared',
                    "**{$user->name}**, **{$project->title}** taslağını harici bir e-posta (**{$share->email}**) ile açık olarak paylaştı. Güvenli geçiş anahtarı: **{$share->passkey}**",
                    'ProjectDraftShare',
                    $share->id,
                    $project->id
                );

                $externalShares[] = $share;
            }
        }

        return [
            'reviewers' => $reviewers,
            'added_count' => $addedCount,
            'external_shares' => $externalShares,
            'message' => 'Taslak proje paydaşlarla başarıyla paylaşıldı ve bilgilendirmeler iletildi.',
        ];
    }

    /**
     * Harici paydaş erişim yetkisini iptal eder (Erişimi Kes)
     */
    public function revokeDraftExternalShare(ProjectDraftShare $share, User $user): void
    {
        // Yetki denetimi: Paylaşan kişi, proje oluşturanı, süperadmin veya developer
        $canRevoke = ($share->shared_by_user_id === $user->id) ||
                     ($share->project->created_by === $user->id) ||
                     $user->isAdmin();

        if (!$canRevoke) {
            abort(403, 'Bu harici paylaşım erişimini iptal etme yetkiniz bulunmamaktadır.');
        }

        $share->revoke($user);

        // Denetim günlüğü kaydı
        AuditLogService::log(
            'draft_external_revoked',
            "**{$user->name}**, **{$share->project->title}** taslağının **{$share->email}** harici adresine tanımlı erişim yetkisini kesti.",
            'ProjectDraftShare',
            $share->id,
            $share->project_id
        );
    }

    /**
     * Taslak projenin belirli bir alanına öneri ekler
     */
    public function storeDraftSuggestion(Project $project, array $data, ?User $user = null, ?string $externalEmail = null): ProjectDraftSuggestion
    {
        $suggestion = $project->draftSuggestions()->create([
            'user_id' => $user ? $user->id : null,
            'external_email' => $externalEmail,
            'field_key' => $data['field_key'],
            'field_label' => $data['field_label'],
            'suggestion_text' => $data['suggestion_text'] . ($externalEmail ? " (Harici Paydaş: {$externalEmail})" : ''),
            'status' => 'pending',
        ]);

        $authorName = $user ? $user->name : ($externalEmail ?: 'Harici Paydaş');

        AuditLogService::log(
            'draft_suggestion_added',
            "**{$authorName}**, **{$project->title}** taslağındaki **{$suggestion->field_label}** alanına öneri ekledi: \"{$suggestion->suggestion_text}\"",
            'ProjectDraftSuggestion',
            $suggestion->id,
            $project->id
        );

        // Taslak sahibine bildirim
        $recipientId = $project->created_by ?: $project->manager_id;
        if ($recipientId && (!$user || $recipientId !== $user->id)) {
            $recipient = User::find($recipientId);
            if ($recipient) {
                app(NotificationMatrixService::class)->dispatch(
                    'project_draft_suggestion_added',
                    [
                        'user_name' => $authorName,
                        'project_title' => $project->title,
                        'field_label' => $suggestion->field_label,
                        'recipient_name' => $recipient->name,
                        'suggestion_text' => $suggestion->suggestion_text,
                    ],
                    route('projects.show', $project->id),
                    $recipient->id
                );
            }
        }

        return $suggestion;
    }

    /**
     * Taslak projeyi yayınlar ve onay sürecini başlatır
     */
    public function publishDraft(Project $project, User $user): Project
    {
        $project->update(['status' => 'pending']);

        AuditLogService::log(
            'draft_published',
            "**{$user->name}**, **{$project->title}** taslağını yayına aldı ve proje onay sürecini başlattı.",
            'Project',
            $project->id,
            $project->id
        );

        return $project;
    }
}