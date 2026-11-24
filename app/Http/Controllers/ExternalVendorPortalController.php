<?php

namespace App\Http\Controllers;

use App\Models\ProjectExternalVendor;
use App\Models\ProjectVendorFile;
use App\Services\ProjectExternalVendorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExternalVendorPortalController extends Controller
{
    public function __construct(
        protected ProjectExternalVendorService $vendorService
    ) {
    }

    /**
     * Dış Paydaş Portalı Giriş & Ana Ekranı
     */
    public function show(Request $request, string $token)
    {
        $vendor = ProjectExternalVendor::with(['project'])
            ->where('access_token', $token)
            ->first();

        if (!$vendor) {
            return view('vendor_portal.error', [
                'title' => 'Geçersiz Bağlantı',
                'message' => 'Erişmeye çalıştığınız dış paydaş portal bağlantısı geçersizdir veya sistemden kaldırılmıştır.',
                'status_code' => 404,
            ]);
        }

        // Proje tamamlandı veya kapandıysa
        if ($vendor->isProjectClosed()) {
            return view('vendor_portal.error', [
                'title' => 'Proje Çalışması Tamamlandı',
                'message' => "Bu projenin teslim, devreye alma ve kapanış süreçleri tamamlandığından belge paylaşım portalı arşive alınmış ve erişime kapatılmıştır.",
                'status_code' => 403,
                'vendor' => $vendor,
            ]);
        }

        // Onay bekliyor veya iptal edildiyse
        if ($vendor->status === 'pending_approval') {
            return view('vendor_portal.error', [
                'title' => 'Erişim Onayı Bekleniyor',
                'message' => "'{$vendor->company_name}' dış paydaş erişiminiz henüz Proje Yöneticisi tarafından onaylanmamıştır. Onaylandığında e-posta ile bilgilendirileceksiniz.",
                'status_code' => 403,
                'vendor' => $vendor,
            ]);
        }

        if ($vendor->status === 'rejected' || $vendor->status === 'revoked') {
            return view('vendor_portal.error', [
                'title' => 'Erişim İptal Edildi',
                'message' => "Bu dış paydaş portalına erişim yetkisi sonlandırılmıştır. " . ($vendor->revocation_reason ? "Gerekçe: {$vendor->revocation_reason}" : ''),
                'status_code' => 403,
                'vendor' => $vendor,
            ]);
        }

        // Güvenli Şifre (Passkey) Kontrolü
        $sessionKey = 'vendor_auth_' . $vendor->id;
        if (!session($sessionKey)) {
            // Geliştirme (Local) ortamında güvenlik şifresi sormadan doğrudan otomatik yetkilendir
            if (app()->isLocal() && !$request->has('auth')) {
                session([$sessionKey => true]);
            } else {
                return view('vendor_portal.auth', compact('vendor'));
            }
        }

        // Portal Dosyalarını Çek (Sıfır Proje Bilgisi İle)
        $inboundFiles = ProjectVendorFile::where('vendor_id', $vendor->id)
            ->where('direction', 'inbound_from_vendor')
            ->latest()
            ->get();

        $outboundFiles = ProjectVendorFile::where('vendor_id', $vendor->id)
            ->where('direction', 'outbound_to_vendor')
            ->where('is_visible_to_vendor', true)
            ->latest()
            ->get();

        return view('vendor_portal.index', compact('vendor', 'inboundFiles', 'outboundFiles'));
    }

    /**
     * Güvenli Şifre (Passkey) & reCAPTCHA Doğrulama
     */
    public function verifyPasskey(Request $request, string $token)
    {
        $vendor = ProjectExternalVendor::where('access_token', $token)->firstOrFail();

        // Geliştirme (Local) ortamında şifre girilmemişse otomatik vendor şifresini al
        if (app()->isLocal() && empty($request->input('passkey'))) {
            $request->merge(['passkey' => $vendor->passkey]);
        }

        $request->validate([
            'passkey' => 'required|string',
        ]);

        // 1. reCAPTCHA Doğrulaması (Sadece canlı / non-local ortamlarda zorunludur)
        if (!app()->isLocal()) {
            $recaptchaResponse = $request->input('g-recaptcha-response');
            $secret = config('services.recaptcha.secret_key') ?: env('RECAPTCHA_SECRET_KEY', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe');

            if (empty($recaptchaResponse)) {
                return back()->withErrors(['recaptcha' => 'Lütfen robot olmadığınızı doğrulayınız (reCAPTCHA kutusunu işaretleyiniz).'])->withInput();
            }

            try {
                $verify = Http::asForm()->timeout(6)->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $secret,
                    'response' => $recaptchaResponse,
                    'remoteip' => $request->ip(),
                ]);

                if ($verify->successful() && !$verify->json('success')) {
                    return back()->withErrors(['recaptcha' => 'reCAPTCHA güvenlik doğrulaması başarısız oldu. Lütfen tekrar deneyiniz.'])->withInput();
                }
            } catch (\Throwable $e) {
                // İzole/yerel test ağında reCAPTCHA servisine ulaşılamazsa logla ama kullanıcıyı kilitleme
                Log::warning('reCAPTCHA doğrulama servisi yanıt vermedi: ' . $e->getMessage());
            }
        }

        // 2. Güvenli Şifre (Passkey) Kontrolü
        $enteredPasskey = strtoupper(trim($request->input('passkey')));
        $actualPasskey = strtoupper(trim($vendor->passkey));

        if ($enteredPasskey !== $actualPasskey) {
            return back()->withErrors(['passkey' => 'Girdiğiniz güvenlik şifresi hatalıdır. Lütfen e-postanıza iletilen şifreyi kontrol ediniz veya aşağıdan tekrar gönderiniz.'])->withInput();
        }

        // Oturumu doğrula
        $sessionKey = 'vendor_auth_' . $vendor->id;
        session([$sessionKey => true]);

        $vendor->increment('access_count');
        $vendor->update(['last_accessed_at' => now()]);

        return redirect()->route('vendor_portal.show', ['token' => $token])
            ->with('success', 'Kimliğiniz ve güvenlik kontrolünüz başarıyla doğrulandı. Belge portalına hoş geldiniz.');
    }

    /**
     * Dış firmanın şifresini unuttuğunda kayıtlı e-postasına tekrar talep etmesi
     */
    public function resendPasskey(Request $request, string $token)
    {
        $vendor = ProjectExternalVendor::with('project')->where('access_token', $token)->firstOrFail();

        if ($vendor->status !== 'approved') {
            return back()->withErrors(['passkey' => 'Firma erişiminiz henüz onaylanmadığından şifre iletimi yapılamaz. Proje yöneticiniz ile iletişime geçiniz.']);
        }

        if ($vendor->isProjectClosed()) {
            return back()->withErrors(['passkey' => 'Proje çalışması tamamlandığı için portal erişimi ve şifre iletimi kapatılmıştır.']);
        }

        $sender = $vendor->project?->manager ?? $vendor->addedBy ?? \App\Models\User::where('role', 'superadmin')->first() ?? \App\Models\User::first();
        $sent = $this->vendorService->sendCredentialsEmail($vendor, $sender);

        if ($sent) {
            return back()->with('success', "Güvenlik şifreniz ve portal erişim bağlantınız kayıtlı e-posta adresinize ({$vendor->email}) başarıyla iletildi. Lütfen gelen kutunuzu kontrol ediniz.");
        }

        return back()->withErrors(['passkey' => 'Şifre iletimi sırasında bir e-posta hatası oluştu. Lütfen proje yöneticiniz ile iletişime geçiniz.']);
    }

    /**
     * Dış firmanın çıkış yapması
     */
    public function logout(Request $request, string $token)
    {
        $vendor = ProjectExternalVendor::where('access_token', $token)->firstOrFail();
        session()->forget('vendor_auth_' . $vendor->id);

        return redirect()->route('vendor_portal.show', ['token' => $token, 'auth' => 1])
            ->with('info', 'Güvenli çıkış yapıldı.');
    }

    /**
     * Dış firma tarafından dosya yükleme (Inbound) - Güvenlik & Hız Sınırları Korumalı
     */
    public function upload(Request $request, string $token)
    {
        $vendor = ProjectExternalVendor::where('access_token', $token)->firstOrFail();
        if (!$vendor->canAccessPortal()) {
            abort(403, 'Portala dosya yükleme yetkiniz aktif değildir.');
        }

        $sessionKey = 'vendor_auth_' . $vendor->id;
        if (!session($sessionKey)) {
            abort(401, 'Lütfen önce güvenlik şifrenizi doğrulayınız.');
        }

        // 1. HIZ / ARD ARDA YÜKLEME KORUMASI (FLOOD PROTECTION)
        $rateKey = 'vendor_last_upload_' . $vendor->id;
        $lastUpload = session($rateKey);
        if ($lastUpload && (now()->timestamp - $lastUpload) < 10) {
            $waitSec = 10 - (now()->timestamp - $lastUpload);
            return back()->withErrors(['upload' => "Çok sık ard arda yükleme isteğinde bulundunuz. Lütfen {$waitSec} saniye bekleyip tekrar deneyiniz."]);
        }

        // 2. DOSYA SAYISI & TÜRÜ (MIMETYPE / WHITELIST) VALİDASYONU
        $allowedMimes = 'pdf,doc,docx,xls,xlsx,ppt,pptx,csv,txt,rtf,dwg,dxf,step,stp,iges,igs,sldprt,sldasm,jpg,jpeg,png,webp,tif,tiff,zip,rar,7z,tar,gz';
        $request->validate([
            'files' => 'required|array|min:1|max:10', // Tek seferde en fazla 10 dosya
            'files.*' => "required|file|max:51200|mimes:{$allowedMimes}", // Her dosya maks 50MB
            'category' => 'nullable|string|max:150',
            'notes' => 'nullable|string|max:2000',
        ], [
            'files.required' => 'Lütfen yüklenecek en az bir dosya seçiniz.',
            'files.min' => 'Lütfen en az 1 dosya seçiniz.',
            'files.max' => 'Tek seferde en fazla 10 adet belge yükleyebilirsiniz.',
            'files.*.max' => 'Her bir dosya boyutu en fazla 50 MB olabilir.',
            'files.*.mimes' => 'Seçilen dosya türü kabul edilmemektedir. Yalnızca teknik dökümanlar (PDF, CAD/DWG, STEP, Office, Resim, Arşiv) kabul edilmektedir.',
        ]);

        // 3. ZARARLI DOSYA & ÇALIŞTIRILABİLİR (.EXE, SCRIPT VB.) ÇİFT KATMANLI KONTROL
        $dangerousExtensions = [
            'exe',
            'bat',
            'cmd',
            'sh',
            'bash',
            'php',
            'phtml',
            'php3',
            'php4',
            'php5',
            'php7',
            'phar',
            'pl',
            'py',
            'cgi',
            'js',
            'jar',
            'vbs',
            'scr',
            'msi',
            'com',
            'dll',
            'bin',
            'app',
            'dmg',
            'iso',
            'reg',
            'asp',
            'aspx',
            'jsp'
        ];

        foreach ($request->file('files') as $file) {
            if (!$file || !$file->isValid()) {
                return back()->withErrors(['upload' => 'Yüklenen dosyalardan biri geçersiz veya bozuk.']);
            }

            $ext = strtolower($file->getClientOriginalExtension());
            $mime = strtolower($file->getMimeType() ?: '');

            if (
                in_array($ext, $dangerousExtensions)
                || str_contains($mime, 'x-msdownload')
                || str_contains($mime, 'x-dosexec')
                || str_contains($mime, 'x-executable')
                || str_contains($mime, 'x-php')
                || str_contains($mime, 'x-sh')
            ) {
                return back()->withErrors([
                    'upload' => "🚫 Güvenlik Engeli: '{$file->getClientOriginalName()}' dosyası çalıştırılabilir / zararlı bir uzantıya (.{$ext}) sahip olduğu için sistem tarafından engellendi. Yalnızca teknik dökümanlar kabul edilir."
                ]);
            }
        }

        // 4. PAYDAŞA ÖZEL KATEGORİ DOĞRULAMASI
        $selectedCategory = trim($request->input('category', ''));
        $allowedCategories = $vendor->categories_list;
        if (!in_array($selectedCategory, $allowedCategories)) {
            $selectedCategory = $allowedCategories[0] ?? 'Diğer Teknik Doküman';
        }

        // 5. DOSYALARI GÜVENLE YÜKLE
        $notes = $request->input('notes');
        $batchId = (string) \Illuminate\Support\Str::uuid();
        $uploadedCount = 0;
        $firstFileName = '';
        foreach ($request->file('files') as $file) {
            if (empty($firstFileName)) {
                $firstFileName = $file->getClientOriginalName();
            }
            // Her dosya için tekil bildirim gönderme (false), batch sonunda tek seferde toplu bildirim gönderilecek
            $this->vendorService->uploadInboundFile($vendor, $file, $selectedCategory, $notes, $batchId, false);
            $uploadedCount++;
        }

        // Proje RACI Paydaşlarına, Yöneticisine ve Sponsoruna Tek ve Özet Zil/Mail Bildirimi Gönder
        if ($uploadedCount > 0) {
            $this->vendorService->notifyStakeholdersBatchInbound($vendor, $uploadedCount, $firstFileName, $selectedCategory);
        }

        // Flood koruma zaman damgasını güncelle
        session([$rateKey => now()->timestamp]);

        return back()->with('success', "{$uploadedCount} adet teknik belge '{$selectedCategory}' kategorisinde başarıyla sisteme iletildi. Proje sorumlularına anlık bildirim gönderildi.");
    }

    /**
     * Dış firmanın hatalı yüklediği dosyayı "Geçersiz / Dikkate Alma" olarak işaretlemesi (Silme Yok!)
     */
    public function markVoid(Request $request, string $token, int $file)
    {
        $fileId = $file;
        $vendor = ProjectExternalVendor::where('access_token', $token)->firstOrFail();
        if (!$vendor->canAccessPortal()) {
            abort(403);
        }

        $sessionKey = 'vendor_auth_' . $vendor->id;
        if (!session($sessionKey)) {
            abort(401);
        }

        $file = ProjectVendorFile::where('vendor_id', $vendor->id)
            ->where('id', $fileId)
            ->firstOrFail();

        $validated = $request->validate([
            'void_reason' => 'required|string|max:500',
        ]);

        $this->vendorService->markFileAsVoid($file, $validated['void_reason'], 'vendor', $vendor->id);

        return back()->with('success', "'{$file->original_name}' belgesi 'Geçersiz / Dikkate Alma' olarak işaretlendi. Açıklamanız proje ekibine iletildi.");
    }

    /**
     * Dış firmanın dosya indirmesi (Güvenli)
     */
    public function download(Request $request, string $token, int $file)
    {
        $fileId = $file;
        $vendor = ProjectExternalVendor::where('access_token', $token)->firstOrFail();
        if (!$vendor->canAccessPortal()) {
            abort(403);
        }

        $sessionKey = 'vendor_auth_' . $vendor->id;
        if (!session($sessionKey)) {
            abort(401);
        }

        $file = ProjectVendorFile::where('vendor_id', $vendor->id)
            ->where('id', $fileId)
            ->firstOrFail();

        // Eğer firmaya gönderilen bir dosyaysa ve görünürlük kapalıysa izin verme!
        if ($file->direction === 'outbound_to_vendor' && !$file->is_visible_to_vendor) {
            abort(403, 'Bu dosyanın erişim izni kapatılmıştır.');
        }

        if (!Storage::disk('public')->exists($file->storage_path)) {
            abort(404, 'Dosya sunucuda bulunamadı.');
        }

        return Storage::disk('public')->download($file->storage_path, $file->original_name);
    }
}
