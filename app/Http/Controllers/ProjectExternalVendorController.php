<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectExternalVendor;
use App\Models\ProjectVendorFile;
use App\Models\User;
use App\Services\ProjectExternalVendorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectExternalVendorController extends Controller
{
    public function __construct(
        protected ProjectExternalVendorService $vendorService
    ) {}

    /**
     * Dış firma talep et / ekle
     */
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'service_scope' => 'required|string|max:1000',
            'allowed_categories' => 'nullable',
            'notes' => 'nullable|string|max:1000',
        ]);

        $vendor = $this->vendorService->requestVendor($project, $validated, auth()->user());

        // Eğer kullanıcı SuperAdmin, Admin veya Proje Yöneticisi ise doğrudan onaylama opsiyonu:
        $user = auth()->user();
        if ($request->boolean('auto_approve') && ($user->isSuperAdmin() || $user->isAdmin() || $user->id == $project->manager_id || $user->id == $project->sponsor_id)) {
            $this->vendorService->approveVendor($vendor, $user);
            return back()->with('success', "'{$vendor->company_name}' dış paydaşı başarıyla eklendi ve portala erişimi onaylandı.");
        }

        return back()->with('success', "'{$vendor->company_name}' dış paydaş ekleme talebi oluşturuldu. Proje Yöneticisi onayına sunuldu.");
    }

    /**
     * Dış firmanın belge kategorilerini güncelle
     */
    public function updateCategories(Request $request, Project $project, ProjectExternalVendor $vendor)
    {
        $user = auth()->user();
        if (!$this->canApproveVendor($project, $user) && $user->id !== $vendor->added_by_user_id) {
            abort(403, 'Kategorileri düzenleme yetkiniz bulunmamaktadır.');
        }

        $raw = $request->input('categories');
        $categories = [];
        if (is_string($raw)) {
            $categories = array_values(array_filter(array_map('trim', explode(',', $raw))));
        } elseif (is_array($raw)) {
            $categories = array_values(array_filter(array_map('trim', $raw)));
        }

        $this->vendorService->updateCategories($vendor, $categories, $user);

        return back()->with('success', "'{$vendor->company_name}' dış paydaşı için belge kategorileri başarıyla güncellendi.");
    }

    /**
     * Dış firmayı onayla
     */
    public function approve(Request $request, Project $project, ProjectExternalVendor $vendor)
    {
        $user = auth()->user();
        if (!$this->canApproveVendor($project, $user)) {
            abort(403, 'Dış paydaşı onaylama yetkiniz bulunmamaktadır.');
        }

        $this->vendorService->approveVendor($vendor, $user);

        return back()->with('success', "'{$vendor->company_name}' dış paydaşı onaylandı. Güvenli dosya portalı bağlantısı aktif edildi.");
    }

    /**
     * Dış firmaya giriş bilgilerini ve şifresini e-posta ile gönder
     */
    public function sendCredentials(Request $request, Project $project, ProjectExternalVendor $vendor)
    {
        $user = auth()->user();
        if (!$this->canApproveVendor($project, $user) && $user->id !== $vendor->added_by_user_id) {
            abort(403, 'Giriş bilgilerini iletme yetkiniz bulunmamaktadır.');
        }

        $this->vendorService->sendCredentialsEmail($vendor, $user);

        return back()->with('success', "'{$vendor->company_name}' yetkilisi {$vendor->contact_person} ({$vendor->email}) adresine portal giriş bağlantısı ve güvenlik şifresi e-posta ile başarıyla iletildi.");
    }

    /**
     * Dış firmayı reddet
     */
    public function reject(Request $request, Project $project, ProjectExternalVendor $vendor)
    {
        $user = auth()->user();
        if (!$this->canApproveVendor($project, $user)) {
            abort(403, 'Dış paydaşı reddetme yetkiniz bulunmamaktadır.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $this->vendorService->rejectVendor($vendor, $validated['rejection_reason'], $user);

        return back()->with('success', "'{$vendor->company_name}' dış paydaş başvurusu reddedildi.");
    }

    /**
     * Dış firmayı projeden çıkar / iptal et (Revoke)
     */
    public function revoke(Request $request, Project $project, ProjectExternalVendor $vendor)
    {
        $user = auth()->user();
        // Ekleyen kişi, PM, Sponsor veya SuperAdmin çıkarabilir
        if (!$this->canRevokeVendor($project, $vendor, $user)) {
            abort(403, 'Dış paydaşı projeden çıkarma yetkiniz bulunmamaktadır.');
        }

        $validated = $request->validate([
            'revocation_reason' => 'required|string|max:500',
        ]);

        $this->vendorService->revokeVendor($vendor, $validated['revocation_reason'], $user);

        return back()->with('success', "'{$vendor->company_name}' dış paydaşı projeden çıkarıldı ve portal erişimi iptal edildi.");
    }

    /**
     * Köksan ekibi tarafından firmaya dosya yükleme (Outbound)
     */
    public function uploadOutboundFile(Request $request, Project $project, ProjectExternalVendor $vendor)
    {
        $request->validate([
            'files' => 'required|array|min:1',
            'files.*' => 'required|file|max:51200', // 50MB per file
            'is_visible_to_vendor' => 'nullable|boolean',
            'category' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
        ]);

        $user = auth()->user();
        $isVisible = $request->boolean('is_visible_to_vendor', false);
        $category = $request->input('category', 'teknik_sartname');
        $notes = $request->input('notes');
        $batchId = (string) \Illuminate\Support\Str::uuid();

        $uploadedCount = 0;
        foreach ($request->file('files') as $file) {
            if ($file && $file->isValid()) {
                $this->vendorService->uploadOutboundFile($project, $vendor, $file, $user, $isVisible, $category, $notes, $batchId);
                $uploadedCount++;
            }
        }

        return back()->with('success', "{$uploadedCount} adet dosya '{$vendor->company_name}' için yüklendi. (Görünürlük: " . ($isVisible ? 'Firma Görebilir' : 'Firma Göremez / Kapalı') . ")");
    }

    /**
     * Firmadan gelen dosyayı incele & onayla / reddet
     */
    public function reviewFile(Request $request, Project $project, ProjectVendorFile $file)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'review_note' => 'nullable|string|max:500',
        ]);

        $this->vendorService->reviewVendorFile($file, $validated['status'], $validated['review_note'] ?? null, auth()->user());

        $statusTr = ($validated['status'] === 'approved') ? 'onaylandı ve kabul edildi' : 'reddedildi';
        return back()->with('success', "'{$file->original_name}' belgesi başarıyla {$statusTr}.");
    }

    /**
     * Dosya görünürlüğünü aç / kapat (Toggle)
     */
    public function toggleVisibility(Request $request, Project $project, ProjectVendorFile $file)
    {
        $validated = $request->validate([
            'is_visible' => 'required|boolean',
        ]);

        $this->vendorService->toggleFileVisibility($file, (bool)$validated['is_visible'], auth()->user());

        $msg = $validated['is_visible'] ? 'Dosya dış firmanın görünürlüğüne açıldı.' : 'Dosya dış firmaya kapatıldı.';
        return back()->with('success', $msg);
    }

    /**
     * Şirket içi personel tarafından hatalı yüklenen dosyayı "Geçersiz / Dikkate Alma" olarak işaretle
     */
    public function markVoid(Request $request, Project $project, ProjectVendorFile $file)
    {
        $validated = $request->validate([
            'void_reason' => 'required|string|max:500',
        ]);

        $this->vendorService->markFileAsVoid($file, $validated['void_reason'], 'internal_user', auth()->id());

        return back()->with('success', "'{$file->original_name}' belgesi 'Geçersiz / Dikkate Alma' olarak işaretlendi.");
    }

    /**
     * Güvenli dosya indirme
     */
    public function downloadFile(Project $project, ProjectVendorFile $file)
    {
        if ($file->project_id != $project->id) {
            abort(404);
        }

        if (!Storage::disk('public')->exists($file->storage_path)) {
            abort(404, 'Dosya sunucuda bulunamadı.');
        }

        return Storage::disk('public')->download($file->storage_path, $file->original_name);
    }

    protected function canApproveVendor(Project $project, User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->isAdmin() 
            || $user->id == $project->manager_id 
            || $user->id == $project->sponsor_id;
    }

    protected function canRevokeVendor(Project $project, ProjectExternalVendor $vendor, User $user): bool
    {
        return $user->isSuperAdmin() 
            || $user->isAdmin() 
            || $user->id == $project->manager_id 
            || $user->id == $project->sponsor_id 
            || $user->id == $vendor->added_by_user_id;
    }
}
