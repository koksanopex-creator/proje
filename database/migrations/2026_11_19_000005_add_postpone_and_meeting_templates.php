<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\NotificationTemplate;
use App\Models\NotificationMatrix;
use App\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // 1. project_meetings tablosuna postpone_reason alanı ekle
        if (Schema::hasTable('project_meetings') && !Schema::hasColumn('project_meetings', 'postpone_reason')) {
            Schema::table('project_meetings', function (Blueprint $table) {
                $table->text('postpone_reason')->nullable()->after('minutes');
            });
        }

        // 2. toplanti_ertelendi ve Eksik Toplantı Şablonlarını Oluştur
        $mailTemplate = '<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #1e293b; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">'
            . '<div style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%); color: #ffffff; padding: 20px 24px;">'
            . '<h2 style="margin: 0; font-size: 18px; font-weight: 700;">⏱️ Toplantı Ertelendi</h2>'
            . '<p style="margin: 4px 0 0; font-size: 13px; color: #fef3c7;">Köksan Enterprise PPM Toplantı Koordinasyonu</p>'
            . '</div>'
            . '<div style="padding: 24px; background: #ffffff;">'
            . '<p style="margin-top: 0;">Sayın <strong>{kullanici_adi}</strong>,</p>'
            . '<p><strong>{proje_kodu} - {proje_adi}</strong> projesine ait planlanmış olan aşağıdaki toplantı <strong>{islem_yapan}</strong> tarafından ertelenmiştir:</p>'
            . '<div style="background: #fffbeb; border-left: 4px solid #f59e0b; padding: 14px 18px; margin: 18px 0; border-radius: 0 8px 8px 0;">'
            . '<h3 style="margin: 0 0 10px 0; font-size: 15px; color: #78350f;">{toplanti_baslik}</h3>'
            . '<div style="font-size: 13px; color: #92400e; margin-bottom: 4px;">📅 <strong>Eski Tarih:</strong> <del>{eski_tarih}</del></div>'
            . '<div style="font-size: 13px; color: #15803d; font-weight: 600; margin-bottom: 4px;">📅 <strong>Yeni Tarih & Saat:</strong> {yeni_tarih}</div>'
            . '<div style="font-size: 13px; color: #475569; margin-bottom: 4px;">📍 <strong>Lokasyon / Salon:</strong> {lokasyon}</div>'
            . '<div style="font-size: 13px; color: #475569;">🏢 <strong>Departman:</strong> {departman_adi}</div>'
            . '</div>'
            . '{erteleme_notu_alani}'
            . '{gundem_alani}'
            . '<div style="margin-top: 24px; text-align: center;">'
            . '<a href="{toplanti_url}" style="display: inline-block; background: #d97706; color: #ffffff; text-decoration: none; padding: 10px 24px; border-radius: 8px; font-weight: 700; font-size: 14px;">Güncel Toplantı Detaylarını İncele ↗</a>'
            . '</div>'
            . '</div>'
            . '<div style="background: #f1f5f9; padding: 14px 24px; font-size: 12px; color: #64748b; text-align: center; border-top: 1px solid #e2e8f0;">'
            . 'Bu e-posta Köksan Enterprise PPM sistemi tarafından otomatik olarak oluşturulmuştur.'
            . '</div>'
            . '</div>';

        $allMeetingTemplates = [
            [
                'event_key' => 'toplanti_ertelendi',
                'event_title' => 'Toplantı Ertelendi',
                'category' => 'Toplantı',
                'bell_title_template' => '⏱️ Toplantı Ertelendi: {toplanti_baslik}',
                'bell_body_template' => '**{islem_yapan}** tarafından **{proje_kodu} - {proje_adi}** projesine ait **{toplanti_baslik}** toplantısı ertelendi.{erteleme_notu_alani} 📅 Yeni Tarih: **{yeni_tarih}**',
                'mail_subject_template' => '[Toplantı Ertelendi] {proje_kodu} - {toplanti_baslik}',
                'mail_body_template' => $mailTemplate,
                'available_variables' => [
                    'toplanti_baslik', 'proje_kodu', 'proje_adi', 'islem_yapan',
                    'eski_tarih', 'yeni_tarih', 'erteleme_notu_alani', 'lokasyon',
                    'kullanici_adi', 'departman_adi', 'toplanti_url', 'gundem_alani'
                ],
            ],
            [
                'event_key' => 'toplanti_baslatildi',
                'event_title' => 'Toplantı Başlatıldı',
                'category' => 'Toplantı',
                'bell_title_template' => '▶️ Toplantı Başlatıldı: {toplanti_baslik}',
                'bell_body_template' => '**{islem_yapan}** tarafından **{proje_kodu}** projesine bağlı **{toplanti_baslik}** toplantısı başlatılmıştır.',
                'mail_subject_template' => '[Toplantı Başlatıldı] {proje_kodu} - {toplanti_baslik}',
                'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{islem_yapan}</strong> tarafından <strong>{proje_kodu} - {proje_adi}</strong> projesine ait <strong>{toplanti_baslik}</strong> toplantısı başlatılmıştır.</p><p>📅 Tarih & Saat: <strong>{tarih}</strong><br>📍 Lokasyon: <strong>{lokasyon}</strong></p>{ek_not}',
                'available_variables' => ['toplanti_baslik', 'proje_kodu', 'proje_adi', 'islem_yapan', 'tarih', 'lokasyon', 'ek_not', 'kullanici_adi', 'departman_adi'],
            ],
            [
                'event_key' => 'toplanti_tamamlandi',
                'event_title' => 'Toplantı Tamamlandı & Tutanak Bağlandı',
                'category' => 'Toplantı',
                'bell_title_template' => '✅ Toplantı Tamamlandı: {toplanti_baslik}',
                'bell_body_template' => '**{islem_yapan}** tarafından **{proje_kodu}** projesine bağlı **{toplanti_baslik}** toplantısı tamamlanmış ve resmi tutanak bağlanmıştır.',
                'mail_subject_template' => '[Toplantı Tamamlandı & Tutanak] {proje_kodu} - {toplanti_baslik}',
                'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{islem_yapan}</strong> tarafından <strong>{proje_kodu} - {proje_adi}</strong> projesine ait <strong>{toplanti_baslik}</strong> toplantısı tamamlanmış ve resmi tutanağı sisteme kaydedilmiştir.</p><p>📅 Tarih: <strong>{tarih}</strong><br>📍 Lokasyon: <strong>{lokasyon}</strong></p>{ek_not}',
                'available_variables' => ['toplanti_baslik', 'proje_kodu', 'proje_adi', 'islem_yapan', 'tarih', 'lokasyon', 'ek_not', 'kullanici_adi', 'departman_adi'],
            ],
            [
                'event_key' => 'toplanti_yeniden_planlandi',
                'event_title' => 'Toplantı Yeniden Planlandı',
                'category' => 'Toplantı',
                'bell_title_template' => '⏱️ Toplantı Yeniden Planlandı: {toplanti_baslik}',
                'bell_body_template' => '**{islem_yapan}** tarafından **{proje_kodu}** projesine bağlı **{toplanti_baslik}** toplantısı yeniden planlandı durumuna alınmıştır.',
                'mail_subject_template' => '[Toplantı Yeniden Planlandı] {proje_kodu} - {toplanti_baslik}',
                'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{islem_yapan}</strong> tarafından <strong>{proje_kodu} - {proje_adi}</strong> projesine ait <strong>{toplanti_baslik}</strong> toplantısı yeniden planlandı durumuna alınmıştır.</p><p>📅 Tarih & Saat: <strong>{tarih}</strong></p>{ek_not}',
                'available_variables' => ['toplanti_baslik', 'proje_kodu', 'proje_adi', 'islem_yapan', 'tarih', 'lokasyon', 'ek_not', 'kullanici_adi', 'departman_adi'],
            ],
            [
                'event_key' => 'toplanti_iptal_edildi',
                'event_title' => 'Toplantı İptal Edildi',
                'category' => 'Toplantı',
                'bell_title_template' => '❌ Toplantı İptal Edildi: {toplanti_baslik}',
                'bell_body_template' => '**{islem_yapan}** tarafından **{proje_kodu}** projesine bağlı **{toplanti_baslik}** toplantısı iptal edilmiştir.',
                'mail_subject_template' => '[Toplantı İptal Edildi] {proje_kodu} - {toplanti_baslik}',
                'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{islem_yapan}</strong> tarafından <strong>{proje_kodu} - {proje_adi}</strong> projesine ait <strong>{toplanti_baslik}</strong> toplantısı iptal edilmiştir.</p>{ek_not}',
                'available_variables' => ['toplanti_baslik', 'proje_kodu', 'proje_adi', 'islem_yapan', 'tarih', 'lokasyon', 'ek_not', 'kullanici_adi', 'departman_adi'],
            ],
        ];

        $roles = Role::all();

        foreach ($allMeetingTemplates as $tplData) {
            $tpl = NotificationTemplate::updateOrCreate(
                ['event_key' => $tplData['event_key']],
                $tplData + ['is_active' => true]
            );

            // Bildirim Matrisi Tablosuna Rol Kurallarını Ekle
            foreach ($roles as $role) {
                NotificationMatrix::firstOrCreate(
                    [
                        'event_key' => $tpl->event_key,
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
    }

    public function down(): void
    {
        if (Schema::hasTable('project_meetings') && Schema::hasColumn('project_meetings', 'postpone_reason')) {
            Schema::table('project_meetings', function (Blueprint $table) {
                $table->dropColumn('postpone_reason');
            });
        }

        NotificationMatrix::where('event_key', 'toplanti_ertelendi')->delete();
        NotificationTemplate::where('event_key', 'toplanti_ertelendi')->delete();
    }
};
