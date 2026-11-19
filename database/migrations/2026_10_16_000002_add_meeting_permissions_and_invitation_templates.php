<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permission;
use App\Models\NotificationTemplate;
use App\Models\NotificationMatrix;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. meeting.create Yetkisini Ekle
        $meetingCreate = Permission::firstOrCreate(
            ['slug' => 'meeting.create'],
            [
                'name' => 'Yeni Toplantı Planlama & Katılımcı Daveti',
                'group_name' => 'Toplantılar',
                'description' => 'Projelerde yeni toplantı organize etme ve paydaşları davet etme yetkisi.',
            ]
        );

        // İlgili Rollere Yetkiyi Ata
        $createRoles = Role::whereIn('slug', [
            'superadmin',
            'admin',
            'bolum_lideri',
            'direktor',
            'proje_yoneticisi',
            'kalite_denetleyici',
            'teknik_lider',
            'isg_sorumlusu',
        ])->get();

        foreach ($createRoles as $r) {
            DB::table('role_has_permissions')->updateOrInsert([
                'role_id' => $r->id,
                'permission_id' => $meetingCreate->id,
            ]);
        }

        // 2. toplanti_daveti Bildirim Şablonunu Ekle
        $templateData = [
            'event_key' => 'toplanti_daveti',
            'event_title' => 'Toplantı Katılımcı Daveti',
            'category' => 'Toplantı',
            'bell_title_template' => 'Toplantı Daveti: {toplanti_baslik}',
            'bell_body_template' => '**{islem_yapan}** sizi **{proje_kodu} - {proje_adi}** projesine bağlı **{toplanti_baslik}** toplantısına davet etti. 📅 Tarih: **{tarih}**',
            'mail_subject_template' => '[Toplantı Daveti] {proje_kodu} - {toplanti_baslik}',
            'mail_body_template' => '<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #1e293b; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;"><div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 20px 24px;"><h2 style="margin: 0; font-size: 18px; font-weight: 700;">📅 Toplantı Katılım Daveti</h2><p style="margin: 4px 0 0; font-size: 13px; color: #94a3b8;">Köksan Enterprise PPM Toplantı Yönetimi</p></div><div style="padding: 24px; background: #ffffff;"><p style="margin-top: 0;">Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{islem_yapan}</strong> sizi <strong>{proje_kodu} - {proje_adi}</strong> projesine ait aşağıdaki toplantıya katılımcı olarak davet etmiştir:</p><div style="background: #f8fafc; border-left: 4px solid #0284c7; padding: 14px 18px; margin: 18px 0; border-radius: 0 8px 8px 0;"><h3 style="margin: 0 0 8px 0; font-size: 15px; color: #0f172a;">{toplanti_baslik}</h3><div style="font-size: 13px; color: #475569; margin-bottom: 4px;">📅 <strong>Tarih & Saat:</strong> {tarih}</div><div style="font-size: 13px; color: #475569; margin-bottom: 4px;">📍 <strong>Lokasyon / Salon:</strong> {lokasyon}</div><div style="font-size: 13px; color: #475569;">🏢 <strong>Departman:</strong> {departman_adi}</div></div>{gundem_alani}{ek_not}<div style="margin-top: 24px; text-align: center;"><a href="{toplanti_url}" style="display: inline-block; background: #0284c7; color: #ffffff; text-decoration: none; padding: 10px 24px; border-radius: 8px; font-weight: 700; font-size: 14px;">Toplantı Detaylarını & Gündemi İncele ↗</a></div></div><div style="background: #f1f5f9; padding: 14px 24px; font-size: 12px; color: #64748b; text-align: center; border-top: 1px solid #e2e8f0;">Bu e-posta Köksan Enterprise PPM sistemi tarafından otomatik olarak oluşturulmuştur.</div></div>',
            'available_variables' => ['toplanti_baslik', 'proje_kodu', 'proje_adi', 'islem_yapan', 'tarih', 'lokasyon', 'gundem_alani', 'ek_not', 'kullanici_adi', 'departman_adi', 'toplanti_url'],
            'is_active' => true,
        ];

        $tpl = NotificationTemplate::updateOrCreate(
            ['event_key' => 'toplanti_daveti'],
            $templateData
        );

        // Bildirim matrisine varsayılan rolleri bağla
        $allRoles = Role::all();
        foreach ($allRoles as $role) {
            NotificationMatrix::firstOrCreate(
                [
                    'event_key' => 'toplanti_daveti',
                    'role_id' => $role->id,
                ],
                [
                    'is_bell_active' => true,
                    'is_mail_active' => true,
                    'template_id' => $tpl->id,
                ]
            );
        }
    }

    public function down(): void
    {
        Permission::where('slug', 'meeting.create')->delete();
        NotificationMatrix::where('event_key', 'toplanti_daveti')->delete();
        NotificationTemplate::where('event_key', 'toplanti_daveti')->delete();
    }
};
