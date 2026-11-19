<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Mail;
use App\Services\AuditLogService;

class NotificationLogController extends Controller
{
    public function index(Request $request)
    {
        $query = NotificationLog::with(['user', 'sentBy']);

        // Arama filtresi (alıcı, konu, şablon, gövde)
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Kanal filtresi (mail / bell)
        if ($request->filled('channel') && in_array($request->channel, ['mail', 'bell'])) {
            $query->channel($request->channel);
        }

        // Durum filtresi (sent / failed / pending)
        if ($request->filled('status') && in_array($request->status, ['sent', 'failed', 'pending'])) {
            $query->status($request->status);
        }

        // Olay filtresi
        if ($request->filled('event_key') && $request->event_key !== 'all') {
            $query->where('event_key', $request->event_key);
        }

        // Tarih filtresi
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // KPI İstatistikleri (Toplam havuzdan hızlı sayımlar)
        $totalCount = NotificationLog::count();
        $mailCount = NotificationLog::where('channel', 'mail')->count();
        $bellCount = NotificationLog::where('channel', 'bell')->count();
        $failedCount = NotificationLog::where('status', 'failed')->count();
        $todayCount = NotificationLog::whereDate('created_at', today())->count();

        // Şablon listesi (filtreleme için)
        $events = NotificationTemplate::select('event_key', 'event_title')
            ->orderBy('event_title')
            ->get();

        $logs = $query->latest('id')->paginate(20)->withQueryString();

        return view('admin.notification_logs.index', compact(
            'logs',
            'totalCount',
            'mailCount',
            'bellCount',
            'failedCount',
            'todayCount',
            'events'
        ));
    }

    public function show($id)
    {
        $log = NotificationLog::with(['user', 'sentBy'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'log' => [
                'id' => $log->id,
                'channel' => $log->channel,
                'channel_label' => $log->channel_label,
                'status' => $log->status,
                'status_badge' => $log->status_badge,
                'event_key' => $log->event_key,
                'event_title' => $log->event_title ?? $log->event_key,
                'recipient_name' => $log->recipient_name ?? ($log->user?->name ?? '—'),
                'recipient_email' => $log->recipient_email ?? ($log->user?->email ?? '—'),
                'subject' => $log->subject ?? '(Konu Belirtilmemiş)',
                'body' => $log->body ?? '',
                'action_url' => $log->action_url,
                'error_message' => $log->error_message,
                'sent_by' => $log->sentBy?->name ?? 'Sistem',
                'created_at' => $log->created_at ? $log->created_at->format('d.m.Y H:i:s') : '—',
            ]
        ]);
    }

    public function sendTestMail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $recipientEmail = trim($request->email);
        $currentUser = auth()->user();
        $senderName = $currentUser ? $currentUser->name : 'Sistem Yöneticisi';
        $timestamp = now()->format('d.m.Y H:i:s');
        $environment = app()->environment();

        $subject = "Köksan PMS - Microsoft Graph E-Posta Testi ({$timestamp})";

        $htmlBody = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{$subject}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0f172a; margin: 0; padding: 24px; color: #f8fafc;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #1e293b; border-radius: 12px; overflow: hidden; border: 1px solid #334155; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
        <div style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); padding: 28px 24px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 20px; font-weight: 700; letter-spacing: 0.5px;">KÖKSAN PROJE YÖNETİM SİSTEMİ</h1>
            <p style="color: #bae6fd; margin: 6px 0 0 0; font-size: 13px; font-weight: 500;">Microsoft Graph API E-Posta Servis Doğrulaması</p>
        </div>
        <div style="padding: 28px 24px; background-color: #0f172a;">
            <p style="font-size: 15px; color: #f1f5f9; margin-top: 0;">Sayın Yetkili,</p>
            <p style="font-size: 14px; color: #cbd5e1; line-height: 1.6;">
                Bu e-posta, <strong>Köksan Proje Yönetim Sistemi (PPM)</strong> üzerinden <strong>kys@koksan.com</strong> Microsoft Graph API altyapısı kullanılarak başarıyla gönderilmiştir.
            </p>
            
            <div style="background-color: #1e293b; border-left: 4px solid #38bdf8; border-radius: 8px; padding: 16px; margin: 24px 0;">
                <h4 style="margin: 0 0 10px 0; color: #38bdf8; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Entegrasyon Bilgileri</h4>
                <table style="width: 100%; border-collapse: collapse; font-size: 13px; color: #94a3b8;">
                    <tr>
                        <td style="padding: 4px 0; width: 140px; color: #64748b;">Gönderici Hesap:</td>
                        <td style="padding: 4px 0; color: #f8fafc; font-weight: 600;">kys@koksan.com</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; color: #64748b;">Hedef Alıcı:</td>
                        <td style="padding: 4px 0; color: #f8fafc; font-weight: 600;">{$recipientEmail}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; color: #64748b;">İşlemi Yapan:</td>
                        <td style="padding: 4px 0; color: #f8fafc;">{$senderName}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; color: #64748b;">Tarih / Saat:</td>
                        <td style="padding: 4px 0; color: #f8fafc;">{$timestamp}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; color: #64748b;">Sunucu Ortamı:</td>
                        <td style="padding: 4px 0; color: #34d399; font-weight: 600;">{$environment}</td>
                    </tr>
                </table>
            </div>

            <p style="font-size: 13px; color: #94a3b8; line-height: 1.5;">
                Bildirim matrisinde aktif olan tüm süreçler (toplantı davetleri, onay talepleri, kurul üyelik değişiklikleri vb.) bu Microsoft Graph hesabı üzerinden otomatik olarak ilgililere iletilmektedir.
            </p>
            
            <div style="margin-top: 32px; padding-top: 16px; border-top: 1px solid #1e293b; text-align: center;">
                <p style="font-size: 12px; color: #64748b; margin: 0;">
                    © Köksan Holding — Bilgi Teknolojileri ve Yönetim Sistemleri
                </p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;

        try {
            Mail::html($htmlBody, function ($msg) use ($recipientEmail, $subject) {
                $msg->to($recipientEmail)
                    ->subject($subject);
            });

            // Başarılı log kaydı oluştur
            $log = NotificationLog::create([
                'channel' => 'mail',
                'event_key' => 'test_mail',
                'event_title' => 'Sistem Test E-Postası (Microsoft Graph)',
                'user_id' => $currentUser?->id,
                'recipient_name' => $currentUser?->name ?? $recipientEmail,
                'recipient_email' => $recipientEmail,
                'subject' => $subject,
                'body' => $htmlBody,
                'action_url' => route('admin.notification_logs.index'),
                'status' => 'sent',
                'sent_by_user_id' => $currentUser?->id,
                'metadata' => [
                    'environment' => $environment,
                    'mailer' => config('mail.default'),
                ],
            ]);

            AuditLogService::log(
                'created',
                "Microsoft Graph canlı test maili {$recipientEmail} adresine başarıyla gönderildi (Log ID: {$log->id}).",
                'NotificationLog',
                $log->id
            );

            return redirect()->back()->with('success', "Test e-postası başarıyla {$recipientEmail} adresine iletildi ve loglandı.");
        } catch (\Throwable $e) {
            // Başarısız log kaydı oluştur
            NotificationLog::create([
                'channel' => 'mail',
                'event_key' => 'test_mail',
                'event_title' => 'Sistem Test E-Postası (Microsoft Graph)',
                'user_id' => $currentUser?->id,
                'recipient_name' => $currentUser?->name ?? $recipientEmail,
                'recipient_email' => $recipientEmail,
                'subject' => $subject,
                'body' => $htmlBody,
                'action_url' => route('admin.notification_logs.index'),
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'sent_by_user_id' => $currentUser?->id,
                'metadata' => [
                    'environment' => $environment,
                    'mailer' => config('mail.default'),
                    'trace' => $e->getTraceAsString(),
                ],
            ]);

            return redirect()->back()->with('error', "Test e-postası gönderilemedi! Hata: " . $e->getMessage());
        }
    }

    public function resend($id)
    {
        $log = NotificationLog::findOrFail($id);

        if ($log->channel !== 'mail' || empty($log->recipient_email)) {
            return redirect()->back()->with('error', 'Sadece geçerli bir alıcı e-postası olan mail logları yeniden gönderilebilir.');
        }

        try {
            Mail::html($log->body, function ($msg) use ($log) {
                $msg->to($log->recipient_email, $log->recipient_name)
                    ->subject($log->subject);
            });

            // Başarılı yeni log kaydı aç
            NotificationLog::create([
                'channel' => 'mail',
                'event_key' => $log->event_key,
                'event_title' => ($log->event_title ?? 'Bildirim') . ' (Yeniden Gönderim)',
                'user_id' => $log->user_id,
                'recipient_name' => $log->recipient_name,
                'recipient_email' => $log->recipient_email,
                'subject' => $log->subject,
                'body' => $log->body,
                'action_url' => $log->action_url,
                'status' => 'sent',
                'sent_by_user_id' => auth()->id(),
                'metadata' => array_merge($log->metadata ?? [], ['resend_of_log_id' => $log->id]),
            ]);

            return redirect()->back()->with('success', "E-posta {$log->recipient_email} adresine başarıyla yeniden iletildi.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', "Yeniden gönderim başarısız: " . $e->getMessage());
        }
    }
}
