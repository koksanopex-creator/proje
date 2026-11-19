<?php

namespace App\Services;

use App\Models\NotificationMatrix;
use App\Models\NotificationTemplate;
use App\Models\InAppNotification;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class NotificationMatrixService
{
    /**
     * Bildirim matrisindeki kurallara göre Zil ve E-Posta bildirimi dağıtır.
     */
    public function dispatch(
        string $eventKey,
        array $variables = [],
        ?string $actionUrl = null,
        ?int $specificUserId = null
    ): void {
        $template = NotificationTemplate::where('event_key', $eventKey)->where('is_active', true)->first();
        if (!$template) {
            Log::info("NotificationMatrix: '{$eventKey}' için aktif şablon bulunamadı.");
            return;
        }

        // Action URL'i her zaman göreli path yap (/projects/3 gibi)
        if ($actionUrl) {
            $parsed = parse_url($actionUrl);
            $actionUrl = ($parsed['path'] ?? $actionUrl) . (isset($parsed['query']) ? '?' . $parsed['query'] : '');
        }

        $recipients = collect();

        if ($specificUserId) {
            $targetUser = User::with(['roles', 'bolum'])->find($specificUserId);
            if ($targetUser && $targetUser->is_active) {
                $roleIds = $targetUser->roles->pluck('id')->toArray();
                
                $matrixEntries = NotificationMatrix::where('event_key', $eventKey)
                    ->whereIn('role_id', $roleIds)
                    ->get();

                // Eğer kullanıcının rollerinden birinde zil veya mail aktifse
                $bellActive = $matrixEntries->contains('is_bell_active', true);
                $mailActive = $matrixEntries->contains('is_mail_active', true);

                // Özel kullanıcı hedefliyse ve matriste kural henüz girilmemişse varsayılan aktif say
                if ($matrixEntries->isEmpty()) {
                    $bellActive = true;
                    $mailActive = true;
                }

                if ($bellActive || $mailActive) {
                    $recipients->push([
                        'user' => $targetUser,
                        'bell' => $bellActive,
                        'mail' => $mailActive,
                    ]);
                }
            }
        } else {
            $matrixEntries = NotificationMatrix::where('event_key', $eventKey)
                ->where(function ($q) {
                    $q->where('is_bell_active', true)->orWhere('is_mail_active', true);
                })
                ->get();

            foreach ($matrixEntries as $entry) {
                $usersInRole = User::with('bolum')->whereHas('roles', function ($q) use ($entry) {
                    $q->where('roles.id', $entry->role_id);
                })->where('is_active', true)->get();

                foreach ($usersInRole as $u) {
                    $recipients->push([
                        'user' => $u,
                        'bell' => (bool)$entry->is_bell_active,
                        'mail' => (bool)$entry->is_mail_active,
                    ]);
                }
            }
        }

        $uniqueRecipients = $recipients->unique(fn($item) => $item['user']->id);
        $currentUser = auth()->user();

        foreach ($uniqueRecipients as $r) {
            /** @var User $targetUser */
            $targetUser = $r['user'];

            // ZENGİN DİNAMİK DEĞİŞKEN HAVUZU (Username, Departman Name vb.)
            $deptName = $variables['departman_adi'] ?? ($targetUser->bolum?->name ?? 'Merkez Departman');
            $actorName = $variables['islem_yapan'] ?? ($variables['olusturan'] ?? ($currentUser?->name ?? 'Sistem'));

            $richVars = [
                // Kullanıcı Değişkenleri
                'kullanici_adi' => $targetUser->name,
                'username' => $targetUser->name,
                'kullanici_eposta' => $targetUser->email,
                'user_email' => $targetUser->email,
                'unvan' => $targetUser->unvan ?? 'Personel',
                'title' => $targetUser->unvan ?? 'Personel',
                'sicil_no' => $targetUser->sicil_no ?? '',

                // Departman & Bölüm Değişkenleri
                'departman_adi' => $deptName,
                'department_name' => $deptName,
                'bolum_adi' => $deptName,

                // İşlemi Yapan / Moderatör Değişkenleri
                'islem_yapan' => $actorName,
                'action_by' => $actorName,
                'olusturan' => $actorName,
                'creator' => $actorName,

                // Tarih & Zaman Değişkenleri
                'tarih' => now()->format('d.m.Y H:i'),
                'date' => now()->format('d.m.Y H:i'),
                'saat' => now()->format('H:i'),
            ];

            // Proje Takma İsimleri (Alias)
            if (!empty($variables['proje_adi'])) {
                $richVars['project_name'] = $variables['proje_adi'];
            }
            if (!empty($variables['proje_kodu'])) {
                $richVars['project_code'] = $variables['proje_kodu'];
            }

            // Olay özel değişkenleriyle birleştir
            $finalVars = array_merge($richVars, $variables);

            $bellTitle = $this->renderText($template->bell_title_template, $finalVars);
            $bellBody = $this->renderText($template->bell_body_template, $finalVars);
            $mailSubject = $this->renderText($template->mail_subject_template, $finalVars);
            $mailBody = $this->renderHtml($template->mail_body_template, $finalVars);

            if ($r['bell']) {
                InAppNotification::create([
                    'id' => (string) Str::uuid(),
                    'user_id' => $targetUser->id,
                    'event_key' => $eventKey,
                    'title' => $bellTitle,
                    'message' => $bellBody,
                    'action_url' => $actionUrl,
                ]);
            }

            if ($r['mail'] && !empty($targetUser->email)) {
                try {
                    \Illuminate\Support\Facades\Mail::html($mailBody, function ($msg) use ($targetUser, $mailSubject) {
                        $msg->to($targetUser->email, $targetUser->name)
                            ->subject($mailSubject);
                    });
                    Log::info("E-Posta Gönderildi: {$targetUser->email} | Başlık: {$mailSubject}");
                } catch (\Throwable $e) {
                    Log::error("E-Posta Gönderim Hatası ({$targetUser->email}): " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Toplantı statü değişikliği için Bildirim Matrisi veya özelleştirilmiş alıcı listesine göre bildirim dağıtır.
     */
    public function dispatchMeetingStatus(
        \App\Models\ProjectMeeting $meeting,
        string $newStatus,
        ?string $customNote = null,
        ?array $notifyBellUserIds = null,
        ?array $notifyMailUserIds = null
    ): void {
        $eventMap = [
            'in_progress' => 'toplanti_baslatildi',
            'completed' => 'toplanti_tamamlandi',
            'scheduled' => 'toplanti_yeniden_planlandi',
            'cancelled' => 'toplanti_iptal_edildi',
        ];

        $eventKey = $eventMap[$newStatus] ?? null;
        if (!$eventKey) {
            return;
        }

        $template = NotificationTemplate::where('event_key', $eventKey)->where('is_active', true)->first();
        if (!$template) {
            Log::info("NotificationMatrix: '{$eventKey}' için aktif şablon bulunamadı.");
            return;
        }

        $project = $meeting->project;
        $currentUser = auth()->user();
        $actorName = $currentUser ? $currentUser->getMaskedNameFor() : 'Sistem';

        $actionUrl = route('meetings.show', $meeting->id);

        $variables = [
            'toplanti_baslik' => $meeting->title,
            'meeting_title' => $meeting->title,
            'proje_kodu' => $project->code,
            'project_code' => $project->code,
            'proje_adi' => $project->title,
            'project_name' => $project->title,
            'departman_adi' => $project->bolum?->name ?? 'Merkez Departman',
            'tarih' => $meeting->meeting_date ? $meeting->meeting_date->format('d.m.Y H:i') : now()->format('d.m.Y H:i'),
            'lokasyon' => $meeting->location ?: 'Şantiye Ofisi / Konferans Salonu',
            'islem_yapan' => $actorName,
            'ek_not' => $customNote ? '<p><strong>Ek Bilgi / Not:</strong> ' . nl2br(e($customNote)) . '</p>' : '',
        ];

        // Eğer kullanıcı arayüzdeki modal üzerinden özel alıcı seçimi yapmışsa
        $isCustomSelection = ($notifyBellUserIds !== null || $notifyMailUserIds !== null);

        $recipients = collect();

        if ($isCustomSelection) {
            $allTargetIds = array_unique(array_merge((array)$notifyBellUserIds, (array)$notifyMailUserIds));
            if (!empty($allTargetIds)) {
                $targetUsers = User::with('bolum')->whereIn('id', $allTargetIds)->where('is_active', true)->get();
                foreach ($targetUsers as $u) {
                    $wantsBell = in_array($u->id, (array)$notifyBellUserIds);
                    $wantsMail = in_array($u->id, (array)$notifyMailUserIds);
                    if ($wantsBell || $wantsMail) {
                        $recipients->push([
                            'user' => $u,
                            'bell' => $wantsBell,
                            'mail' => $wantsMail,
                        ]);
                    }
                }
            }
        } else {
            // Sistem standardı: Matris kurallarına göre alıcıları çek
            $meetingParticipantUserIds = $meeting->participants->pluck('user_id')->filter()->toArray();
            $projectStakeholderUserIds = $project->stakeholders->pluck('user_id')->filter()->toArray();
            $relevantUserIds = array_unique(array_merge(
                $meetingParticipantUserIds,
                $projectStakeholderUserIds,
                array_filter([$project->manager_id, $project->sponsor_id])
            ));

            $matrixEntries = NotificationMatrix::where('event_key', $eventKey)
                ->where(function ($q) {
                    $q->where('is_bell_active', true)->orWhere('is_mail_active', true);
                })
                ->get();

            $users = User::with(['roles', 'bolum'])->whereIn('id', $relevantUserIds)->where('is_active', true)->get();
            foreach ($users as $u) {
                $userRoleIds = $u->roles->pluck('id')->toArray();
                $matchedEntries = $matrixEntries->whereIn('role_id', $userRoleIds);
                $bell = $matchedEntries->contains('is_bell_active', true) || $matrixEntries->isEmpty();
                $mail = $matchedEntries->contains('is_mail_active', true) || $matrixEntries->isEmpty();
                if ($bell || $mail) {
                    $recipients->push([
                        'user' => $u,
                        'bell' => $bell,
                        'mail' => $mail,
                    ]);
                }
            }
        }

        $uniqueRecipients = $recipients->unique(fn($item) => $item['user']->id);

        foreach ($uniqueRecipients as $r) {
            /** @var User $targetUser */
            $targetUser = $r['user'];

            $richVars = array_merge($variables, [
                'kullanici_adi' => $targetUser->name,
                'username' => $targetUser->name,
                'kullanici_eposta' => $targetUser->email,
            ]);

            $bellTitle = $this->renderText($template->bell_title_template, $richVars);
            $bellBody = $this->renderText($template->bell_body_template, $richVars);
            $mailSubject = $this->renderText($template->mail_subject_template, $richVars);
            $mailBody = $this->renderHtml($template->mail_body_template, $richVars);

            if ($r['bell']) {
                InAppNotification::create([
                    'id' => (string) Str::uuid(),
                    'user_id' => $targetUser->id,
                    'event_key' => $eventKey,
                    'title' => $bellTitle,
                    'message' => $bellBody,
                    'action_url' => $actionUrl,
                ]);
            }

            if ($r['mail'] && !empty($targetUser->email)) {
                try {
                    \Illuminate\Support\Facades\Mail::html($mailBody, function ($msg) use ($targetUser, $mailSubject) {
                        $msg->to($targetUser->email, $targetUser->name)
                            ->subject($mailSubject);
                    });
                    Log::info("Toplantı Durum E-Postası Gönderildi: {$targetUser->email} | Başlık: {$mailSubject}");
                } catch (\Throwable $e) {
                    Log::error("Toplantı Durum E-Posta Gönderim Hatası ({$targetUser->email}): " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Toplantı katılımcısına davet bildirimi gönderir (Zil ve E-Posta).
     */
    public function dispatchMeetingInvitation(
        \App\Models\ProjectMeeting $meeting,
        int $targetUserId,
        ?string $customNote = null
    ): void {
        $eventKey = 'toplanti_daveti';
        $template = NotificationTemplate::where('event_key', $eventKey)->where('is_active', true)->first();
        if (!$template) {
            Log::info("NotificationMatrix: '{$eventKey}' için aktif şablon bulunamadı.");
            return;
        }

        $targetUser = User::with(['roles', 'bolum'])->find($targetUserId);
        if (!$targetUser || !$targetUser->is_active) {
            return;
        }

        $project = $meeting->project;
        $currentUser = auth()->user();
        $actorName = $currentUser ? $currentUser->getMaskedNameFor() : 'Sistem';
        $actionUrl = route('meetings.show', $meeting->id);
        $parsed = parse_url($actionUrl);
        $cleanActionUrl = ($parsed['path'] ?? $actionUrl) . (isset($parsed['query']) ? '?' . $parsed['query'] : '');

        // Rol matrisi kontrolü
        $roleIds = $targetUser->roles->pluck('id')->toArray();
        $matrixEntries = NotificationMatrix::where('event_key', $eventKey)
            ->whereIn('role_id', $roleIds)
            ->get();

        $wantsBell = true;
        $wantsMail = true;
        if ($matrixEntries->isNotEmpty()) {
            $wantsBell = $matrixEntries->contains('is_bell_active', true);
            $wantsMail = $matrixEntries->contains('is_mail_active', true);
        }

        if (!$wantsBell && !$wantsMail) {
            return;
        }

        $deptName = $targetUser->bolum?->name ?? ($project->bolum?->name ?? 'Merkez Departman');
        $gundemText = $meeting->agenda ? nl2br(e($meeting->agenda)) : 'Gündem maddeleri toplantı öncesinde paylaşılacaktır.';

        $variables = [
            'toplanti_baslik' => $meeting->title,
            'meeting_title' => $meeting->title,
            'proje_kodu' => $project->code,
            'project_code' => $project->code,
            'proje_adi' => $project->title,
            'project_name' => $project->title,
            'departman_adi' => $deptName,
            'department_name' => $deptName,
            'tarih' => $meeting->meeting_date ? $meeting->meeting_date->format('d.m.Y H:i') : now()->format('d.m.Y H:i'),
            'lokasyon' => $meeting->location ?: 'Şantiye Ofisi / Konferans Salonu',
            'islem_yapan' => $actorName,
            'kullanici_adi' => $targetUser->name,
            'username' => $targetUser->name,
            'toplanti_url' => $actionUrl,
            'gundem_alani' => '<p><strong>Toplantı Gündemi:</strong><br>' . $gundemText . '</p>',
            'ek_not' => $customNote ? '<p><strong>Ek Bilgi / Not:</strong> ' . nl2br(e($customNote)) . '</p>' : '',
        ];

        // 1. ZİL BİLDİRİMİ (InAppNotification)
        if ($wantsBell) {
            $bellTitle = $this->renderText($template->bell_title_template, $variables);
            $bellBody = $this->renderHtml($template->bell_body_template, $variables);

            InAppNotification::create([
                'id' => (string) Str::uuid(),
                'user_id' => $targetUser->id,
                'event_key' => $eventKey,
                'title' => $bellTitle,
                'message' => $bellBody,
                'action_url' => $cleanActionUrl,
            ]);
            Log::info("Toplantı Davet Zili Gönderildi: {$targetUser->id} ({$targetUser->name})");
        }

        // 2. E-POSTA BİLDİRİMİ
        if ($wantsMail && !empty($targetUser->email)) {
            $mailSubject = $this->renderText($template->mail_subject_template, $variables);
            $mailBody = $this->renderRawHtml($template->mail_body_template, $variables);

            try {
                \Illuminate\Support\Facades\Mail::html($mailBody, function ($msg) use ($targetUser, $mailSubject) {
                    $msg->to($targetUser->email, $targetUser->name)
                        ->subject($mailSubject);
                });
                Log::info("Toplantı Davet E-Postası Gönderildi: {$targetUser->email} | Başlık: {$mailSubject}");
            } catch (\Throwable $e) {
                Log::error("Toplantı Davet E-Posta Gönderim Hatası ({$targetUser->email}): " . $e->getMessage());
            }
        }
    }

    /**
     * Dış paydaşa taslak proje inceleme daveti e-postası gönderir (Zil bildirimi hariç tutulur).
     */
    public function dispatchExternalDraftShare(\App\Models\Project $project, \App\Models\ProjectDraftShare $share, \App\Models\User $sender): void
    {
        $template = NotificationTemplate::where('event_key', 'proje_taslak_harici_paylasim')->where('is_active', true)->first();
        if (!$template) {
            Log::info("NotificationMatrix: 'proje_taslak_harici_paylasim' için aktif şablon bulunamadı.");
            return;
        }

        $accessUrl = route('projects.external_draft_review', [
            'project' => $project->id,
            'token' => $share->token,
            'passkey' => $share->passkey,
        ]);

        $variables = [
            'proje_adi' => $project->title,
            'proje_kodu' => $project->code,
            'paylasan_kisi' => $sender->name,
            'alici_eposta' => $share->email,
            'erisim_linki' => $accessUrl,
            'gecis_anahtari' => $share->passkey,
            'paylasim_notu' => $share->note ?: 'Lütfen proje hedefleri ve bütçe planını inceleyerek görüş ve önerilerinizi iletiniz.',
        ];

        $mailSubject = $this->renderText($template->mail_subject_template, $variables);
        $mailBody = $this->renderRawHtml($template->mail_body_template, $variables);

        try {
            \Illuminate\Support\Facades\Mail::html($mailBody, function ($msg) use ($share, $mailSubject) {
                $msg->to($share->email)
                    ->subject($mailSubject);
            });
            Log::info("Harici E-Posta Gönderildi: {$share->email} | Başlık: {$mailSubject}");
        } catch (\Throwable $e) {
            Log::error("Harici E-Posta Gönderim Hatası ({$share->email}): " . $e->getMessage());
        }
    }

    protected function renderText(string $template, array $variables): string
    {
        foreach ($variables as $key => $val) {
            if (is_scalar($val)) {
                $template = str_replace("{{$key}}", (string)$val, $template);
                $template = str_replace("{" . $key . "}", (string)$val, $template);
            }
        }
        return $template;
    }

    protected function renderHtml(string $template, array $variables): string
    {
        foreach ($variables as $key => $val) {
            if (is_scalar($val)) {
                $template = str_replace("{{$key}}", "<strong>" . e($val) . "</strong>", $template);
                $template = str_replace("{" . $key . "}", "<strong>" . e($val) . "</strong>", $template);
            }
        }
        return $template;
    }

    protected function renderRawHtml(string $template, array $variables): string
    {
        foreach ($variables as $key => $val) {
            if (is_scalar($val)) {
                $template = str_replace("{{$key}}", (string)$val, $template);
                $template = str_replace("{" . $key . "}", (string)$val, $template);
            }
        }
        return $template;
    }
}