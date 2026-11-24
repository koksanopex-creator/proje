<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\Project;
use App\Models\ProjectAuditLog;
use App\Models\ProjectExternalVendor;
use App\Models\ProjectVendorFile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectExternalVendorService
{
    public function __construct(
        protected NotificationMatrixService $notifService
    ) {}

    /**
     * Mühendis / Proje Üyesi tarafından dış firma ekleme talebi oluşturulması
     */
    public function requestVendor(Project $project, array $data, User $actor): ProjectExternalVendor
    {
        $token = ProjectExternalVendor::generateSecureToken();
        $passkey = ProjectExternalVendor::generatePasskey();

        $categories = null;
        if (!empty($data['allowed_categories'])) {
            if (is_string($data['allowed_categories'])) {
                $categories = array_values(array_filter(array_map('trim', explode(',', $data['allowed_categories']))));
            } elseif (is_array($data['allowed_categories'])) {
                $categories = array_values(array_filter(array_map('trim', $data['allowed_categories'])));
            }
        }

        $vendor = ProjectExternalVendor::create([
            'project_id' => $project->id,
            'company_name' => trim($data['company_name']),
            'contact_person' => trim($data['contact_person']),
            'email' => strtolower(trim($data['email'])),
            'phone' => trim($data['phone'] ?? ''),
            'service_scope' => trim($data['service_scope'] ?? ''),
            'allowed_categories' => !empty($categories) ? $categories : null,
            'access_token' => $token,
            'passkey' => $passkey,
            'status' => 'pending_approval',
            'added_by_user_id' => $actor->id,
            'notes' => trim($data['notes'] ?? ''),
        ]);

        // Audit Log
        ProjectAuditLog::create([
            'project_id' => $project->id,
            'user_id' => $actor->id,
            'entity_type' => 'project_external_vendor',
            'entity_id' => $vendor->id,
            'action' => 'vendor_added',
            'description' => "{$actor->name}, '{$vendor->company_name}' firmasını dış paydaş olarak ekleme talebinde bulundu (Onay Bekliyor).",
            'new_values' => [
                'company_name' => $vendor->company_name,
                'contact_person' => $vendor->contact_person,
                'email' => $vendor->email,
                'service_scope' => $vendor->service_scope,
            ],
        ]);

        // Proje Yöneticisi ve Sponsora bildirim
        $approverIds = array_filter([$project->manager_id, $project->sponsor_id]);
        $actionUrl = route('projects.show', ['project' => $project->id]) . '#external-vendors';
        foreach ($approverIds as $apprId) {
            $this->notifService->dispatch('dis_paydas_onay_talebi', [
                'proje_adi' => $project->title,
                'firma_adi' => $vendor->company_name,
                'talep_eden' => $actor->name,
            ], $actionUrl, $apprId);
        }

        return $vendor;
    }

    /**
     * Dış firmanın onaylanması (PM / Sponsor / Admin)
     */
    public function approveVendor(ProjectExternalVendor $vendor, User $approver): bool
    {
        $vendor->update([
            'status' => 'approved',
            'approved_by_user_id' => $approver->id,
            'approved_at' => now(),
            'rejection_reason' => null,
            'rejected_by_user_id' => null,
            'rejected_at' => null,
        ]);

        $project = $vendor->project;

        ProjectAuditLog::create([
            'project_id' => $vendor->project_id,
            'user_id' => $approver->id,
            'entity_type' => 'project_external_vendor',
            'entity_id' => $vendor->id,
            'action' => 'vendor_approved',
            'description' => "{$approver->name}, '{$vendor->company_name}' dış paydaşını onayladı. Güvenli dosya portalı erişimi aktifleştirildi.",
            'new_values' => ['status' => 'approved', 'approved_at' => now()->toIso8601String()],
        ]);

        // Ekleyen kullanıcıya onay bildirimi
        if ($vendor->added_by_user_id && $vendor->added_by_user_id != $approver->id) {
            $actionUrl = route('projects.show', ['project' => $vendor->project_id]) . '#external-vendors';
            $this->notifService->dispatch('dis_paydas_onaylandi', [
                'proje_adi' => $project?->title ?? '',
                'firma_adi' => $vendor->company_name,
                'onaylayan' => $approver->name,
            ], $actionUrl, $vendor->added_by_user_id);
        }

        // Onaylandığı an firmaya otomatik giriş bilgilerini e-posta ile gönder
        $this->sendCredentialsEmail($vendor, $approver);

        return true;
    }

    /**
     * Dış firmanın reddedilmesi
     */
    public function rejectVendor(ProjectExternalVendor $vendor, string $reason, User $rejector): bool
    {
        $vendor->update([
            'status' => 'rejected',
            'rejected_by_user_id' => $rejector->id,
            'rejected_at' => now(),
            'rejection_reason' => trim($reason),
        ]);

        $project = $vendor->project;

        ProjectAuditLog::create([
            'project_id' => $vendor->project_id,
            'user_id' => $rejector->id,
            'entity_type' => 'project_external_vendor',
            'entity_id' => $vendor->id,
            'action' => 'vendor_rejected',
            'description' => "{$rejector->name}, '{$vendor->company_name}' dış paydaş başvurusunu reddetti. Gerekçe: " . trim($reason),
            'new_values' => ['status' => 'rejected', 'rejection_reason' => trim($reason)],
        ]);

        // Ekleyen kullanıcıya red bildirimi
        if ($vendor->added_by_user_id && $vendor->added_by_user_id != $rejector->id) {
            $actionUrl = route('projects.show', ['project' => $vendor->project_id]) . '#external-vendors';
            $this->notifService->dispatch('dis_paydas_reddedildi', [
                'proje_adi' => $project?->title ?? '',
                'firma_adi' => $vendor->company_name,
                'reddeden' => $rejector->name,
                'gerekce' => trim($reason),
            ], $actionUrl, $vendor->added_by_user_id);
        }

        return true;
    }

    /**
     * Dış firmanın projeden çıkarılması / yetkisinin iptal edilmesi
     */
    public function revokeVendor(ProjectExternalVendor $vendor, string $reason, User $actor): bool
    {
        $vendor->update([
            'status' => 'revoked',
            'revoked_by_user_id' => $actor->id,
            'revoked_at' => now(),
            'revocation_reason' => trim($reason),
        ]);

        ProjectAuditLog::create([
            'project_id' => $vendor->project_id,
            'user_id' => $actor->id,
            'entity_type' => 'project_external_vendor',
            'entity_id' => $vendor->id,
            'action' => 'vendor_revoked',
            'description' => "{$actor->name}, '{$vendor->company_name}' dış paydaşını projeden çıkardı. Portal erişim bağlantıları iptal edildi. Gerekçe: " . trim($reason),
            'new_values' => ['status' => 'revoked', 'revocation_reason' => trim($reason)],
        ]);

        // Soft delete ile güvenle sakla
        $vendor->delete();

        return true;
    }

    /**
     * Dış firmaya özel belge kategorilerini güncelle
     */
    public function updateCategories(ProjectExternalVendor $vendor, array $categories, User $actor): bool
    {
        $cleanCategories = array_values(array_filter(array_map('trim', $categories)));
        $vendor->update([
            'allowed_categories' => !empty($cleanCategories) ? $cleanCategories : null,
        ]);

        ProjectAuditLog::create([
            'project_id' => $vendor->project_id,
            'user_id' => $actor->id,
            'entity_type' => 'project_external_vendor',
            'entity_id' => $vendor->id,
            'action' => 'vendor_updated',
            'description' => "{$actor->name}, '{$vendor->company_name}' dış paydaşının belge yükleme kategorilerini güncelledi: " . implode(', ', $cleanCategories ?: ProjectExternalVendor::defaultCategories()),
            'new_values' => ['allowed_categories' => $cleanCategories],
        ]);

        return true;
    }

    /**
     * Dış firma tarafından sisteme dosya yüklenmesi (Inbound)
     */
    public function uploadInboundFile(
        ProjectExternalVendor $vendor,
        UploadedFile $file,
        ?string $category = 'muayene_raporu',
        ?string $notes = null,
        ?string $uploadBatchId = null,
        bool $sendNotification = true
    ): ProjectVendorFile {
        $project = $vendor->project;
        $originalName = $file->getClientOriginalName();
        $companySlug = Str::slug($vendor->company_name, '_') ?: 'firma';
        $projectSlug = Str::slug($project?->code ?? ('proje_' . $vendor->project_id), '_') ?: 'proje';
        $dateTime = now()->format('d.m.Y_H.i');
        $random = Str::lower(Str::random(3));
        $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $fileName = "{$companySlug}_{$projectSlug}_{$dateTime}_{$random}.{$ext}";
        $folder = "proje_dis_paydas_dosyalari/{$companySlug}/{$projectSlug}/{$vendor->id}";
        $path = $file->storeAs($folder, $fileName, 'public');

        $cleanNotes = $notes ? trim($notes) : null;

        $vendorFile = ProjectVendorFile::create([
            'project_id' => $vendor->project_id,
            'vendor_id' => $vendor->id,
            'original_name' => $originalName,
            'storage_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getClientMimeType(),
            'direction' => 'inbound_from_vendor',
            'status' => 'pending_review',
            'is_visible_to_vendor' => true,
            'uploaded_by_vendor_id' => $vendor->id,
            'category' => $category ?: 'muayene_raporu',
            'notes' => $cleanNotes,
            'upload_batch_id' => $uploadBatchId,
        ]);

        // Audit Log
        $auditDesc = "'{$vendor->company_name}' dış paydaşı '{$originalName}' belgesini sisteme yükledi (İnceleme Bekliyor).";
        if ($cleanNotes) {
            $auditDesc .= " Açıklama Notu: {$cleanNotes}";
        }

        ProjectAuditLog::create([
            'project_id' => $vendor->project_id,
            'user_id' => null,
            'acting_as_role_name' => "Dış Paydaş ({$vendor->company_name})",
            'entity_type' => 'project_vendor_file',
            'entity_id' => $vendorFile->id,
            'action' => 'vendor_file_uploaded',
            'description' => $auditDesc,
            'new_values' => [
                'file_name' => $originalName,
                'file_size' => $vendorFile->formatted_size,
                'vendor_id' => $vendor->id,
                'notes' => $cleanNotes,
            ],
        ]);

        // Proje Paydaşlarına Zil & Mail Bildirimi (Toplu yüklemelerde batch sonunda tek seferde tetiklenebilir)
        if ($sendNotification) {
            $stakeholderUserIds = $project->stakeholders()
                ->where('is_active', true)
                ->pluck('user_id')
                ->filter()
                ->unique()
                ->toArray();

            if ($project->manager_id) $stakeholderUserIds[] = $project->manager_id;
            if ($project->sponsor_id) $stakeholderUserIds[] = $project->sponsor_id;
            if ($vendor->added_by_user_id) $stakeholderUserIds[] = $vendor->added_by_user_id;
            $stakeholderUserIds = array_values(array_unique($stakeholderUserIds));

            $actionUrl = route('projects.show', ['project' => $vendor->project_id]) . '#external-vendors';
            foreach ($stakeholderUserIds as $uId) {
                $this->notifService->dispatch('dis_paydas_dosya_yukledi', [
                    'proje_adi' => $project->title,
                    'proje_kodu' => $project->code ?? ('#' . $project->id),
                    'firma_adi' => $vendor->company_name,
                    'dosya_adi' => $originalName,
                    'kategori' => $category ?: 'Teknik Doküman',
                    'tarih' => now()->format('d.m.Y H:i'),
                ], $actionUrl, $uId);
            }
        }

        return $vendorFile;
    }

    /**
     * Dış firmanın toplu dosya yüklemesi sonrasında tüm aktif RACI paydaşlarına tek ve özet bildirim iletir.
     */
    public function notifyStakeholdersBatchInbound(
        ProjectExternalVendor $vendor,
        int $fileCount,
        string $firstFileName,
        string $category
    ): void {
        $project = $vendor->project;
        if (!$project) return;

        $stakeholderUserIds = $project->stakeholders()
            ->where('is_active', true)
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->toArray();

        if ($project->manager_id) $stakeholderUserIds[] = $project->manager_id;
        if ($project->sponsor_id) $stakeholderUserIds[] = $project->sponsor_id;
        if ($vendor->added_by_user_id) $stakeholderUserIds[] = $vendor->added_by_user_id;
        $stakeholderUserIds = array_values(array_unique($stakeholderUserIds));

        $displayDocName = $fileCount > 1
            ? "{$fileCount} Adet Teknik Belge ({$firstFileName} vb.)"
            : $firstFileName;

        $actionUrl = route('projects.show', ['project' => $vendor->project_id]) . '#external-vendors';
        foreach ($stakeholderUserIds as $uId) {
            $this->notifService->dispatch('dis_paydas_dosya_yukledi', [
                'proje_adi' => $project->title,
                'proje_kodu' => $project->code ?? ('#' . $project->id),
                'firma_adi' => $vendor->company_name,
                'dosya_adi' => $displayDocName,
                'kategori' => $category,
                'tarih' => now()->format('d.m.Y H:i'),
            ], $actionUrl, $uId);
        }
    }

    /**
     * Köksan ekibi tarafından firmaya dosya yüklenmesi (Outbound)
     */
    public function uploadOutboundFile(
        Project $project,
        ProjectExternalVendor $vendor,
        UploadedFile $file,
        User $actor,
        bool $isVisible = false,
        ?string $category = 'teknik_sartname',
        ?string $notes = null,
        ?string $uploadBatchId = null
    ): ProjectVendorFile {
        $originalName = $file->getClientOriginalName();
        $companySlug = Str::slug($vendor->company_name, '_') ?: 'firma';
        $projectSlug = Str::slug($project->code ?? ('proje_' . $project->id), '_') ?: 'proje';
        $dateTime = now()->format('d.m.Y_H.i');
        $random = Str::lower(Str::random(3));
        $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $fileName = "{$companySlug}_{$projectSlug}_{$dateTime}_{$random}.{$ext}";
        $folder = "proje_dis_paydas_dosyalari/{$companySlug}/{$projectSlug}/{$vendor->id}";
        $path = $file->storeAs($folder, $fileName, 'public');

        $cleanNotes = $notes ? trim($notes) : null;

        $vendorFile = ProjectVendorFile::create([
            'project_id' => $project->id,
            'vendor_id' => $vendor->id,
            'original_name' => $originalName,
            'storage_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getClientMimeType(),
            'direction' => 'outbound_to_vendor',
            'status' => 'approved', // Şirket içi personel yüklediği için varsayılan onaylı
            'is_visible_to_vendor' => $isVisible,
            'uploaded_by_user_id' => $actor->id,
            'category' => $category ?: 'teknik_sartname',
            'notes' => $cleanNotes,
            'upload_batch_id' => $uploadBatchId,
        ]);

        ProjectAuditLog::create([
            'project_id' => $project->id,
            'user_id' => $actor->id,
            'entity_type' => 'project_vendor_file',
            'entity_id' => $vendorFile->id,
            'action' => 'vendor_file_uploaded',
            'description' => "{$actor->name}, '{$vendor->company_name}' dış paydaşı için '{$originalName}' belgesini yükledi (Görünürlük: " . ($isVisible ? 'Açık' : 'Kapalı') . ").",
            'new_values' => [
                'file_name' => $originalName,
                'file_size' => $vendorFile->formatted_size,
                'is_visible' => $isVisible,
            ],
        ]);

        return $vendorFile;
    }

    /**
     * Gelen dosyanın incelenip onaylanması veya reddedilmesi
     */
    public function reviewVendorFile(ProjectVendorFile $file, string $status, ?string $note, User $reviewer): bool
    {
        $file->update([
            'status' => $status, // approved veya rejected
            'reviewed_by_user_id' => $reviewer->id,
            'reviewed_at' => now(),
            'review_note' => trim($note ?? ''),
        ]);

        $action = ($status === 'approved') ? 'vendor_file_approved' : 'vendor_file_rejected';
        $statusTr = ($status === 'approved') ? 'Onaylandı' : 'Reddedildi';

        ProjectAuditLog::create([
            'project_id' => $file->project_id,
            'user_id' => $reviewer->id,
            'entity_type' => 'project_vendor_file',
            'entity_id' => $file->id,
            'action' => $action,
            'description' => "{$reviewer->name}, '{$file->vendor?->company_name}' firmasının yüklediği '{$file->original_name}' belgesini {$statusTr}." . ($note ? " Not: {$note}" : ''),
            'new_values' => ['status' => $status, 'review_note' => $note],
        ]);

        return true;
    }

    /**
     * Gönderilen dosyanın dış firma tarafından görünürlüğünü açıp kapama (Toggle)
     */
    public function toggleFileVisibility(ProjectVendorFile $file, bool $isVisible, User $actor): bool
    {
        $file->update(['is_visible_to_vendor' => $isVisible]);

        $durumTr = $isVisible ? 'Açıldı (Firma görebilir)' : 'Kapatıldı (Firma göremez)';

        ProjectAuditLog::create([
            'project_id' => $file->project_id,
            'user_id' => $actor->id,
            'entity_type' => 'project_vendor_file',
            'entity_id' => $file->id,
            'action' => 'vendor_file_visibility',
            'description' => "{$actor->name}, '{$file->original_name}' belgesinin dış firma görünürlüğünü {$durumTr} olarak güncelledi.",
            'new_values' => ['is_visible_to_vendor' => $isVisible],
        ]);

        return true;
    }

    /**
     * Hatalı yüklenen dosyanın "Geçersiz / Dikkate Alma" (Void Flag) olarak işaretlenmesi
     */
    public function markFileAsVoid(ProjectVendorFile $file, string $reason, string $actorType = 'internal_user', $actorId = null): bool
    {
        $file->markAsVoid($reason, $actorType, $actorId);

        $actorDesc = ($actorType === 'vendor') ? "'{$file->vendor?->company_name}' yetkilisi" : "Kullanıcı #{$actorId}";
        if ($actorType === 'internal_user' && $actorId) {
            $user = User::find($actorId);
            if ($user) $actorDesc = $user->name;
        }

        ProjectAuditLog::create([
            'project_id' => $file->project_id,
            'user_id' => ($actorType === 'internal_user') ? $actorId : null,
            'acting_as_role_name' => ($actorType === 'vendor') ? "Dış Paydaş ({$file->vendor?->company_name})" : null,
            'entity_type' => 'project_vendor_file',
            'entity_id' => $file->id,
            'action' => 'vendor_file_voided',
            'description' => "{$actorDesc}, '{$file->original_name}' belgesini 'Geçersiz / Dikkate Alma' olarak işaretledi. Açıklama: " . trim($reason),
            'new_values' => ['is_void' => true, 'void_reason' => trim($reason), 'voided_by_type' => $actorType],
        ]);

        return true;
    }

    /**
     * Dış paydaşa güvenli portal giriş bağlantısı ve şifresini e-posta ile ilet
     */
    public function sendCredentialsEmail(ProjectExternalVendor $vendor, User $sender): bool
    {
        $mailSubject = "Köksan Teknik Belge Portalı - Giriş Bilgileriniz ({$vendor->company_name})";
        
        $portalUrl = $vendor->portal_url;
        $passkey = $vendor->passkey;
        $contactPerson = htmlspecialchars($vendor->contact_person);
        $companyName = htmlspecialchars($vendor->company_name);
        $serviceScope = htmlspecialchars($vendor->service_scope ?: 'Teknik Doküman ve Belge Paylaşımı');

        $mailBody = "
        <div style=\"font-family: 'Segoe UI', Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);\">
            <div style=\"background: linear-gradient(135deg, #091325 0%, #0f172a 100%); color: #ffffff; padding: 24px 28px; text-align: center;\">
                <div style=\"font-size: 26px; font-weight: 900; letter-spacing: 2px; color: #38bdf8;\">KÖKSAN</div>
                <div style=\"font-size: 13px; color: #94a3b8; margin-top: 4px;\">Güvenli Dış Paydaş & Belge Paylaşım Portalı</div>
            </div>
            
            <div style=\"padding: 28px 24px; color: #334155; line-height: 1.6;\">
                <p style=\"font-size: 15px; margin-top: 0;\">Sayın <strong>{$contactPerson}</strong> ({$companyName}),</p>
                <p style=\"font-size: 13.5px; color: #475569;\">
                    Köksan ile yürütülen teknik çalışma ve onay süreçleri kapsamında resmi muayene raporları, kalite belgeleri ve teknik çizim transferlerini gerçekleştirebileceğiniz <strong>Güvenli Dış Paydaş Belge Portalı</strong> erişiminiz aktifleştirilmiştir.
                </p>

                <div style=\"background: #f8fafc; border-left: 4px solid #0284c7; padding: 12px 16px; margin: 20px 0; border-radius: 0 8px 8px 0;\">
                    <div style=\"font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;\">Çalışma / Hizmet Kapsamı</div>
                    <div style=\"font-size: 14px; font-weight: 600; color: #0f172a; margin-top: 2px;\">{$serviceScope}</div>
                </div>

                <div style=\"text-align: center; margin: 28px 0;\">
                    <a href=\"{$portalUrl}\" style=\"display: inline-block; background: #0284c7; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 14.5px; padding: 12px 28px; border-radius: 8px; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);\">
                        🌐 Belge Portalına Güvenli Giriş Yap ➔
                    </a>
                </div>

                <div style=\"background: #f0f9ff; border: 1.5px dashed #0284c7; border-radius: 10px; padding: 16px; text-align: center; margin: 24px 0;\">
                    <div style=\"font-size: 12px; font-weight: 700; color: #0369a1; text-transform: uppercase; letter-spacing: 1px;\">
                        Giriş Güvenlik Şifreniz (Passkey)
                    </div>
                    <div style=\"font-size: 24px; font-weight: 800; color: #0284c7; letter-spacing: 2px; margin-top: 6px;\">
                        {$passkey}
                    </div>
                    <div style=\"font-size: 11.5px; color: #64748b; margin-top: 6px;\">
                        Portala girişte bu şifreyi ve robot doğrulamasını (reCAPTCHA) tamamlamanız istenecektir.
                    </div>
                </div>

                <div style=\"font-size: 12px; color: #64748b; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 10px 14px; margin-top: 20px;\">
                    ⚠️ <strong>Güvenlik Hatırlatması:</strong> Bu şifre ve bağlantı yalnızca tarafınıza özel üretilmiştir. Lütfen şifrenizi üçüncü şahıslarla paylaşmayınız. Sistemde yüklenen tüm teknik belgeler denetim kaydına alınmaktadır.
                </div>
            </div>

            <div style=\"background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px; text-align: center; font-size: 11.5px; color: #94a3b8;\">
                Köksan Pet ve Plastik Ambalaj San. Tic. A.Ş. &bull; Proje & Mühendislik Yönetimi
            </div>
        </div>
        ";

        try {
            if (app()->environment('local') || env('APP_ENV') === 'local' || config('mail.default') === 'log') {
                Log::info("[Lokal Geliştirme - Dış Paydaş Şifre Maili Loglandı] Kime: {$vendor->email} | Başlık: {$mailSubject}");
            } else {
                Mail::html($mailBody, function ($msg) use ($vendor, $mailSubject) {
                    $msg->to($vendor->email, $vendor->contact_person)
                        ->subject($mailSubject);
                });
            }

            NotificationLog::create([
                'channel' => 'mail',
                'event_key' => 'dis_paydas_portal_giris_bilgileri',
                'event_title' => 'Dış Paydaş Portal Giriş Bilgileri İletimi',
                'user_id' => null,
                'recipient_name' => "{$vendor->contact_person} ({$vendor->company_name})",
                'recipient_email' => $vendor->email,
                'subject' => $mailSubject,
                'body' => $mailBody,
                'action_url' => $portalUrl,
                'status' => 'sent',
                'sent_by_user_id' => $sender->id,
                'metadata' => [
                    'project_id' => $vendor->project_id,
                    'vendor_id' => $vendor->id,
                ],
            ]);

            Log::info("Dış Paydaş Giriş Bilgileri Gönderildi: {$vendor->email} ({$vendor->company_name})");
        } catch (\Throwable $e) {
            Log::warning("Dış paydaş e-posta gönderiminde hata: " . $e->getMessage());
        }

        // Güncelle
        $vendor->update([
            'credentials_sent_at' => now(),
            'credentials_sent_by_user_id' => $sender->id,
        ]);

        ProjectAuditLog::create([
            'project_id' => $vendor->project_id,
            'user_id' => $sender->id,
            'entity_type' => 'project_external_vendor',
            'entity_id' => $vendor->id,
            'action' => 'vendor_credentials_sent',
            'description' => "{$sender->name}, '{$vendor->company_name}' yetkilisi {$vendor->contact_person}'e ({$vendor->email}) portal giriş bilgilerini ve şifresini e-posta ile gönderdi.",
            'new_values' => ['credentials_sent_at' => now()->toIso8601String()],
        ]);

        return true;
    }
}
