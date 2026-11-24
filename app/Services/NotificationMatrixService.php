<?php

namespace App\Services;

use App\Models\NotificationMatrix;
use App\Models\NotificationTemplate;
use App\Models\InAppNotification;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class NotificationMatrixService
{
    /**
     * Lokal geliştirme ortamında gerçek mail atılmasını engelleyip sadece log tutulmasını sağlar.
     */
    public static function isLocalMailSimulation(): bool
    {
        return app()->environment('local') || env('APP_ENV') === 'local' || config('mail.default') === 'log';
    }
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

                // Proje bağlamı varsa kullanıcının projedeki rolünü (Yönetici, Sponsor) tespit et
                $primaryProjectRoleId = null;
                if (!empty($variables['project_id'])) {
                    $proj = \App\Models\Project::find($variables['project_id']);
                    if ($proj) {
                        if ($proj->sponsor_id === $targetUser->id) {
                            $primaryProjectRoleId = \App\Models\Role::where('name', 'like', '%Proje Sponsoru%')->value('id') ?? 28;
                        } elseif ($proj->manager_id === $targetUser->id) {
                            $primaryProjectRoleId = \App\Models\Role::where('name', 'like', '%Proje Yöneticisi%')->value('id') ?? 21;
                        }
                    }
                }

                if ($primaryProjectRoleId) {
                    $roleIds[] = $primaryProjectRoleId;
                }
                $roleIds = array_values(array_unique($roleIds));

                $matrixEntries = NotificationMatrix::where('event_key', $eventKey)
                    ->whereIn('role_id', $roleIds)
                    ->get();

                // Eğer kullanıcının öncelikli bir proje rolü varsa (örn: Proje Sponsoru veya Proje Yöneticisi)
                // matristeki o role özel ayar öncelikli olarak uygulanır.
                $projectRoleEntry = $primaryProjectRoleId ? $matrixEntries->firstWhere('role_id', $primaryProjectRoleId) : null;

                if ($projectRoleEntry) {
                    $bellActive = (bool) $projectRoleEntry->is_bell_active;
                    $mailActive = (bool) $projectRoleEntry->is_mail_active;
                } elseif ($matrixEntries->isEmpty()) {
                    // Henüz matrise kural girilmemişse varsayılan açık say
                    $bellActive = true;
                    $mailActive = true;
                } else {
                    $bellActive = $matrixEntries->contains('is_bell_active', true);
                    $mailActive = $matrixEntries->contains('is_mail_active', true);
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
                        'bell' => (bool) $entry->is_bell_active,
                        'mail' => (bool) $entry->is_mail_active,
                    ]);
                }
            }
        }

        $uniqueRecipients = $recipients->unique(fn($item) => $item['user']->id);
        $currentUser = auth()->user();

        foreach ($uniqueRecipients as $r) {
            /** @var User $targetUser */
            $targetUser = $r['user'];

            // ZENGİN DİNAMİK DEĞİŞKEN HAVUZU
            $deptName = $variables['departman_adi'] ?? ($variables['department_name'] ?? ($targetUser->bolum?->ad ?? ($targetUser->bolum?->name ?? 'Merkez Departman')));
            $actorName = $variables['islem_yapan'] 
                ?? ($variables['olusturan'] 
                ?? ($variables['removed_by'] 
                ?? ($variables['assigned_by'] 
                ?? ($variables['action_by'] 
                ?? ($currentUser?->name ?? 'Sistem Yöneticisi')))));

            $userName = $targetUser->name;
            $appUrl = url('/');
            if (!str_ends_with($appUrl, '/proje') && str_contains($appUrl, 'kys.koksan.com')) {
                $appUrl = rtrim($appUrl, '/') . '/proje';
            }

            $richVars = [
                // Kullanıcı Değişkenleri (Tüm Takma Adlar)
                'kullanici_adi' => $userName,
                'username' => $userName,
                'user_name' => $userName,
                'kullanici' => $userName,
                'ad_soyad' => $userName,
                'name' => $userName,

                'kullanici_eposta' => $targetUser->email,
                'user_email' => $targetUser->email,
                'email' => $targetUser->email,
                'unvan' => $targetUser->unvan ?? 'Personel',
                'title' => $targetUser->unvan ?? 'Personel',
                'sicil_no' => $targetUser->sicil_no ?? '',

                // Departman & Bölüm Değişkenleri
                'departman_adi' => $deptName,
                'department_name' => $deptName,
                'bolum_adi' => $deptName,

                // İşlemi Yapan / Moderatör Değişkenleri (Tüm Takma Adlar)
                'islem_yapan' => $actorName,
                'action_by' => $actorName,
                'olusturan' => $actorName,
                'creator' => $actorName,
                'removed_by' => $actorName,
                'assigned_by' => $actorName,
                'ekleyen' => $actorName,
                'tamamlayan' => $actorName,

                // Tarih & Zaman Değişkenleri
                'tarih' => now()->format('d.m.Y H:i'),
                'date' => now()->format('d.m.Y H:i'),
                'saat' => now()->format('H:i'),

                // Uygulama & Sistem URL
                'uygulama_url' => $appUrl,
                'app_url' => $appUrl,
                'proje_url' => $appUrl,
            ];

            // Kurul / Komite Takma İsimleri (Alias)
            $committeeName = $variables['kurul_adi'] ?? ($variables['committee_name'] ?? ($variables['komite_adi'] ?? ''));
            if (!empty($committeeName)) {
                $richVars['kurul_adi'] = $committeeName;
                $richVars['committee_name'] = $committeeName;
                $richVars['komite_adi'] = $committeeName;
                $richVars['kurul'] = $committeeName;
            }

            // Kurul Üyelik Rolü & Görev Takma İsimleri
            $memberRole = $variables['member_role'] ?? ($variables['rol'] ?? ($variables['role_label'] ?? ($variables['gorev'] ?? '')));
            if (!empty($memberRole)) {
                $richVars['member_role'] = $memberRole;
                $richVars['rol'] = $memberRole;
                $richVars['role_label'] = $memberRole;
                $richVars['gorev'] = $memberRole;
            }

            // Oy Ağırlığı
            $votingWeight = $variables['voting_weight'] ?? ($variables['oy_agirligi'] ?? '');
            if (!empty($votingWeight)) {
                $richVars['voting_weight'] = $votingWeight;
                $richVars['oy_agirligi'] = $votingWeight;
            }

            // Proje Takma İsimleri (Alias)
            if (!empty($variables['proje_adi'])) {
                $richVars['project_name'] = $variables['proje_adi'];
                $richVars['project_title'] = $variables['proje_adi'];
            }
            if (!empty($variables['project_name'])) {
                $richVars['proje_adi'] = $variables['project_name'];
                $richVars['project_title'] = $variables['project_name'];
            }
            if (!empty($variables['proje_kodu'])) {
                $richVars['project_code'] = $variables['proje_kodu'];
            }
            if (!empty($variables['project_code'])) {
                $richVars['proje_kodu'] = $variables['project_code'];
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

                // Bildirim Loguna Ekle (Zil)
                NotificationLog::create([
                    'channel' => 'bell',
                    'event_key' => $eventKey,
                    'event_title' => $template->event_title,
                    'user_id' => $targetUser->id,
                    'recipient_name' => $targetUser->name,
                    'recipient_email' => $targetUser->email,
                    'subject' => $bellTitle,
                    'body' => $bellBody,
                    'action_url' => $actionUrl,
                    'status' => 'sent',
                    'sent_by_user_id' => $currentUser?->id,
                    'metadata' => ['variables' => $variables],
                ]);
            }

            if ($r['mail'] && !empty($targetUser->email)) {
                try {
                    if (self::isLocalMailSimulation()) {
                        \Illuminate\Support\Facades\Log::info("[Lokal Geliştirme - Mail Loglandı] Kime: {$targetUser->email} | Başlık: {$mailSubject}");
                    } else {
                        \Illuminate\Support\Facades\Mail::html($mailBody, function ($msg) use ($targetUser, $mailSubject) {
                            $msg->to($targetUser->email, $targetUser->name)
                                ->subject($mailSubject);
                        });
                    }

                    // Bildirim Loguna Ekle (E-Posta Başarılı)
                    NotificationLog::create([
                        'channel' => 'mail',
                        'event_key' => $eventKey,
                        'event_title' => $template->event_title,
                        'user_id' => $targetUser->id,
                        'recipient_name' => $targetUser->name,
                        'recipient_email' => $targetUser->email,
                        'subject' => $mailSubject,
                        'body' => $mailBody,
                        'action_url' => $actionUrl,
                        'status' => 'sent',
                        'sent_by_user_id' => $currentUser?->id,
                        'metadata' => ['variables' => $variables],
                    ]);

                    Log::info("E-Posta Gönderildi: {$targetUser->email} | Başlık: {$mailSubject}");
                } catch (\Throwable $e) {
                    // Bildirim Loguna Ekle (E-Posta Başarısız)
                    NotificationLog::create([
                        'channel' => 'mail',
                        'event_key' => $eventKey,
                        'event_title' => $template->event_title,
                        'user_id' => $targetUser->id,
                        'recipient_name' => $targetUser->name,
                        'recipient_email' => $targetUser->email,
                        'subject' => $mailSubject,
                        'body' => $mailBody,
                        'action_url' => $actionUrl,
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                        'sent_by_user_id' => $currentUser?->id,
                        'metadata' => ['variables' => $variables],
                    ]);

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

        $deptName = $project->bolum?->ad ?? ($project->bolum?->name ?? 'Merkez Departman');

        $variables = [
            'toplanti_baslik' => $meeting->title,
            'meeting_title' => $meeting->title,
            'proje_kodu' => $project->code,
            'project_code' => $project->code,
            'proje_adi' => $project->title,
            'project_name' => $project->title,
            'departman_adi' => $deptName,
            'tarih' => $meeting->meeting_date ? $meeting->meeting_date->format('d.m.Y H:i') : now()->format('d.m.Y H:i'),
            'lokasyon' => $meeting->location ?: 'Şantiye Ofisi / Konferans Salonu',
            'islem_yapan' => $actorName,
            'ek_not' => $customNote ? '<p><strong>Ek Bilgi / Not:</strong> ' . nl2br(e($customNote)) . '</p>' : '',
        ];

        // Eğer kullanıcı arayüzdeki modal üzerinden özel alıcı seçimi yapmışsa
        $isCustomSelection = ($notifyBellUserIds !== null || $notifyMailUserIds !== null);

        $recipients = collect();

        if ($isCustomSelection) {
            $allTargetIds = array_unique(array_merge((array) $notifyBellUserIds, (array) $notifyMailUserIds));
            if (!empty($allTargetIds)) {
                $targetUsers = User::with('bolum')->whereIn('id', $allTargetIds)->where('is_active', true)->get();
                foreach ($targetUsers as $u) {
                    $wantsBell = in_array($u->id, (array) $notifyBellUserIds);
                    $wantsMail = in_array($u->id, (array) $notifyMailUserIds);
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

                // Bildirim Logu (Zil)
                NotificationLog::create([
                    'channel' => 'bell',
                    'event_key' => $eventKey,
                    'event_title' => $template->event_title,
                    'user_id' => $targetUser->id,
                    'recipient_name' => $targetUser->name,
                    'recipient_email' => $targetUser->email,
                    'subject' => $bellTitle,
                    'body' => $bellBody,
                    'action_url' => $actionUrl,
                    'status' => 'sent',
                    'sent_by_user_id' => $currentUser?->id,
                    'metadata' => ['meeting_id' => $meeting->id, 'variables' => $variables],
                ]);
            }

            if ($r['mail'] && !empty($targetUser->email)) {
                try {
                    if (self::isLocalMailSimulation()) {
                        \Illuminate\Support\Facades\Log::info("[Lokal Geliştirme - Mail Loglandı] Kime: {$targetUser->email} | Başlık: {$mailSubject}");
                    } else {
                        \Illuminate\Support\Facades\Mail::html($mailBody, function ($msg) use ($targetUser, $mailSubject) {
                            $msg->to($targetUser->email, $targetUser->name)
                                ->subject($mailSubject);
                        });
                    }

                    // Bildirim Logu (Mail Başarılı)
                    NotificationLog::create([
                        'channel' => 'mail',
                        'event_key' => $eventKey,
                        'event_title' => $template->event_title,
                        'user_id' => $targetUser->id,
                        'recipient_name' => $targetUser->name,
                        'recipient_email' => $targetUser->email,
                        'subject' => $mailSubject,
                        'body' => $mailBody,
                        'action_url' => $actionUrl,
                        'status' => 'sent',
                        'sent_by_user_id' => $currentUser?->id,
                        'metadata' => ['meeting_id' => $meeting->id, 'variables' => $variables],
                    ]);

                    Log::info("Toplantı Durum E-Postası Gönderildi: {$targetUser->email} | Başlık: {$mailSubject}");
                } catch (\Throwable $e) {
                    // Bildirim Logu (Mail Hata)
                    NotificationLog::create([
                        'channel' => 'mail',
                        'event_key' => $eventKey,
                        'event_title' => $template->event_title,
                        'user_id' => $targetUser->id,
                        'recipient_name' => $targetUser->name,
                        'recipient_email' => $targetUser->email,
                        'subject' => $mailSubject,
                        'body' => $mailBody,
                        'action_url' => $actionUrl,
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                        'sent_by_user_id' => $currentUser?->id,
                        'metadata' => ['meeting_id' => $meeting->id, 'variables' => $variables],
                    ]);

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

        $deptName = $targetUser->bolum?->ad ?? ($targetUser->bolum?->name ?? ($project->bolum?->ad ?? ($project->bolum?->name ?? 'Merkez Departman')));
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

            // Bildirim Logu (Zil)
            NotificationLog::create([
                'channel' => 'bell',
                'event_key' => $eventKey,
                'event_title' => $template->event_title,
                'user_id' => $targetUser->id,
                'recipient_name' => $targetUser->name,
                'recipient_email' => $targetUser->email,
                'subject' => $bellTitle,
                'body' => $bellBody,
                'action_url' => $cleanActionUrl,
                'status' => 'sent',
                'sent_by_user_id' => $currentUser?->id,
                'metadata' => ['meeting_id' => $meeting->id, 'variables' => $variables],
            ]);

            Log::info("Toplantı Davet Zili Gönderildi: {$targetUser->id} ({$targetUser->name})");
        }

        // 2. E-POSTA BİLDİRİMİ
        if ($wantsMail && !empty($targetUser->email)) {
            $mailSubject = $this->renderText($template->mail_subject_template, $variables);
            $mailBody = $this->renderRawHtml($template->mail_body_template, $variables);

            try {
                if (self::isLocalMailSimulation()) {
                    \Illuminate\Support\Facades\Log::info("[Lokal Geliştirme - Mail Loglandı] Kime: {$targetUser->email} | Başlık: {$mailSubject}");
                } else {
                    \Illuminate\Support\Facades\Mail::html($mailBody, function ($msg) use ($targetUser, $mailSubject) {
                        $msg->to($targetUser->email, $targetUser->name)
                            ->subject($mailSubject);
                    });
                }

                // Bildirim Logu (Mail Başarılı)
                NotificationLog::create([
                    'channel' => 'mail',
                    'event_key' => $eventKey,
                    'event_title' => $template->event_title,
                    'user_id' => $targetUser->id,
                    'recipient_name' => $targetUser->name,
                    'recipient_email' => $targetUser->email,
                    'subject' => $mailSubject,
                    'body' => $mailBody,
                    'action_url' => $cleanActionUrl,
                    'status' => 'sent',
                    'sent_by_user_id' => $currentUser?->id,
                    'metadata' => ['meeting_id' => $meeting->id, 'variables' => $variables],
                ]);

                Log::info("Toplantı Davet E-Postası Gönderildi: {$targetUser->email} | Başlık: {$mailSubject}");
            } catch (\Throwable $e) {
                // Bildirim Logu (Mail Hata)
                NotificationLog::create([
                    'channel' => 'mail',
                    'event_key' => $eventKey,
                    'event_title' => $template->event_title,
                    'user_id' => $targetUser->id,
                    'recipient_name' => $targetUser->name,
                    'recipient_email' => $targetUser->email,
                    'subject' => $mailSubject,
                    'body' => $mailBody,
                    'action_url' => $cleanActionUrl,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'sent_by_user_id' => $currentUser?->id,
                    'metadata' => ['meeting_id' => $meeting->id, 'variables' => $variables],
                ]);

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
            if (self::isLocalMailSimulation()) {
                \Illuminate\Support\Facades\Log::info("[Lokal Geliştirme - Harici Mail Loglandı] Kime: {$share->email} | Başlık: {$mailSubject}");
            } else {
                \Illuminate\Support\Facades\Mail::html($mailBody, function ($msg) use ($share, $mailSubject) {
                    $msg->to($share->email)
                        ->subject($mailSubject);
                });
            }

            // Bildirim Logu (Harici Mail Başarılı)
            NotificationLog::create([
                'channel' => 'mail',
                'event_key' => 'proje_taslak_harici_paylasim',
                'event_title' => $template->event_title,
                'user_id' => null,
                'recipient_name' => $share->email,
                'recipient_email' => $share->email,
                'subject' => $mailSubject,
                'body' => $mailBody,
                'action_url' => $accessUrl,
                'status' => 'sent',
                'sent_by_user_id' => $sender->id,
                'metadata' => ['project_id' => $project->id, 'variables' => $variables],
            ]);

            Log::info("Harici E-Posta Gönderildi: {$share->email} | Başlık: {$mailSubject}");
        } catch (\Throwable $e) {
            // Bildirim Logu (Harici Mail Hata)
            NotificationLog::create([
                'channel' => 'mail',
                'event_key' => 'proje_taslak_harici_paylasim',
                'event_title' => $template->event_title,
                'user_id' => null,
                'recipient_name' => $share->email,
                'recipient_email' => $share->email,
                'subject' => $mailSubject,
                'body' => $mailBody,
                'action_url' => $accessUrl,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'sent_by_user_id' => $sender->id,
                'metadata' => ['project_id' => $project->id, 'variables' => $variables],
            ]);

            Log::error("Harici E-Posta Gönderim Hatası ({$share->email}): " . $e->getMessage());
        }
    }

    protected function renderText(string $template, array $variables): string
    {
        foreach ($variables as $key => $val) {
            if (is_scalar($val)) {
                $valStr = (string) $val;
                // Markdown bold brackets: **{key}**
                $template = str_replace("**{{$key}}**", $valStr, $template);
                $template = str_replace("**{" . $key . "}**", $valStr, $template);
                // Normal brackets: {key}
                $template = str_replace("{{$key}}", $valStr, $template);
                $template = str_replace("{" . $key . "}", $valStr, $template);
            }
        }
        // Kalan olası çift yıldızları temizle
        $template = preg_replace('/\*\*(.*?)\*\*/', '$1', $template);
        return trim($template);
    }

    protected function renderHtml(string $template, array $variables): string
    {
        foreach ($variables as $key => $val) {
            if (is_scalar($val)) {
                $valStr = (string) $val;
                $boldVal = "<strong>" . e($valStr) . "</strong>";

                // Markdown bold wrappers: **{key}** -> <strong>val</strong>
                $template = str_replace("**{{$key}}**", $boldVal, $template);
                $template = str_replace("**{" . $key . "}**", $boldVal, $template);

                // Normal brackets: {key} -> <strong>val</strong>
                $template = str_replace("{{$key}}", $boldVal, $template);
                $template = str_replace("{" . $key . "}", $boldVal, $template);
            }
        }

        // <strong> etiketlerinin etrafında kalabilecek çift yıldızları temizle
        $template = str_replace('**<strong>', '<strong>', $template);
        $template = str_replace('</strong>**', '</strong>', $template);
        // Kalan genel markdown bold **metin** sözdizimini <strong>metin</strong> yap
        $template = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $template);

        // Eğer HTML etiketleri (p, div, br) yoksa satır sonlarını br ile biçimlendir
        if (!str_contains($template, '<p>') && !str_contains($template, '<div>') && !str_contains($template, '<br>')) {
            $template = nl2br($template);
        }

        return $this->wrapEmailWithCorporateLayout($template);
    }

    protected function renderRawHtml(string $template, array $variables): string
    {
        foreach ($variables as $key => $val) {
            if (is_scalar($val)) {
                $template = str_replace("{{$key}}", (string) $val, $template);
                $template = str_replace("{" . $key . "}", (string) $val, $template);
            }
        }
        return $template;
    }

    /**
     * Tüm bildirim e-postalarını kurumsal Köksan PPM tasarım şablonu ve
     * doğrudan sisteme yönlendiren 'Köksan Proje Yönetim Sistemi' buton linki ile sarmalar.
     */
    protected function wrapEmailWithCorporateLayout(string $bodyHtml): string
    {
        // Halihazırda tam HTML şablonuna sahipse veya kurumsal buton içeriyorsa mükerrer sarmalama yapma
        if (str_contains($bodyHtml, '<!DOCTYPE') || str_contains($bodyHtml, 'Köksan Proje Yönetim Sistemi ↗') || str_contains($bodyHtml, 'Köksan Enterprise PPM Toplantı')) {
            return $bodyHtml;
        }

        $appUrl = url('/');
        if (!str_ends_with($appUrl, '/proje') && str_contains($appUrl, 'kys.koksan.com')) {
            $appUrl = rtrim($appUrl, '/') . '/proje';
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 24px 12px; color: #1e293b;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <!-- Kurumsal PPM Üst Başlık -->
        <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 20px 24px; text-align: left; border-bottom: 3px solid #0284c7;">
            <div style="font-size: 11px; font-weight: 700; color: #38bdf8; text-transform: uppercase; letter-spacing: 1px;">KÖKSAN HOLDİNG</div>
            <div style="font-size: 17px; font-weight: 800; color: #ffffff; margin-top: 3px; letter-spacing: -0.02em;">Kurumsal Proje Yönetim Sistemi (PPM)</div>
        </div>
        
        <!-- İçerik Alanı -->
        <div style="padding: 24px 28px; line-height: 1.65; font-size: 14px; color: #334155;">
            {$bodyHtml}
            
            <!-- Sabit Kurumsal Buton Bağlantısı (Kullanıcı Talebi) -->
            <div style="margin-top: 28px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center;">
                <a href="{$appUrl}" style="display: inline-block; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff; text-decoration: none; padding: 11px 26px; border-radius: 8px; font-weight: 700; font-size: 13.5px; box-shadow: 0 3px 8px rgba(2, 132, 199, 0.25);">
                    Köksan Proje Yönetim Sistemi ↗
                </a>
            </div>
        </div>
        
        <!-- Kurumsal Altlık Bilgisi -->
        <div style="background-color: #f8fafc; padding: 14px 24px; border-top: 1px solid #e2e8f0; text-align: center;">
            <p style="margin: 0; font-size: 11.5px; color: #64748b;">
                Bu e-posta Köksan Enterprise Proje Yönetim Sistemi tarafından otomatik olarak gönderilmiştir.
            </p>
            <p style="margin: 4px 0 0 0; font-size: 11px; color: #94a3b8;">
                © 2026 Köksan Holding A.Ş. — Bilgi Teknolojileri ve Yönetim Sistemleri
            </p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
