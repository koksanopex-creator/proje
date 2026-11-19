<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotificationTemplate;
use App\Models\NotificationMatrix;
use App\Models\Role;

class MeetingNotificationTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'event_key' => 'toplanti_baslatildi',
                'event_title' => 'Toplantı Başlatıldı',
                'category' => 'Toplantı',
                'bell_title_template' => 'Toplantı Başlatıldı: {toplanti_baslik}',
                'bell_body_template' => '**{islem_yapan}** tarafından **{proje_kodu}** projesine bağlı **{toplanti_baslik}** toplantısı başlatılmıştır.',
                'mail_subject_template' => '[Toplantı Başlatıldı] {proje_kodu} - {toplanti_baslik}',
                'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{islem_yapan}</strong> tarafından <strong>{proje_kodu} - {proje_adi}</strong> projesine ait <strong>{toplanti_baslik}</strong> toplantısı başlatılmıştır.</p><p>📅 Tarih & Saat: <strong>{tarih}</strong><br>📍 Lokasyon: <strong>{lokasyon}</strong></p>{ek_not}',
                'available_variables' => ['toplanti_baslik', 'proje_kodu', 'proje_adi', 'islem_yapan', 'tarih', 'lokasyon', 'ek_not', 'kullanici_adi', 'departman_adi'],
            ],
            [
                'event_key' => 'toplanti_tamamlandi',
                'event_title' => 'Toplantı Tamamlandı & Tutanak Bağlandı',
                'category' => 'Toplantı',
                'bell_title_template' => 'Toplantı Tamamlandı: {toplanti_baslik}',
                'bell_body_template' => '**{islem_yapan}** tarafından **{proje_kodu}** projesine bağlı **{toplanti_baslik}** toplantısı tamamlanmış ve resmi tutanak bağlanmıştır.',
                'mail_subject_template' => '[Toplantı Tamamlandı & Tutanak] {proje_kodu} - {toplanti_baslik}',
                'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{islem_yapan}</strong> tarafından <strong>{proje_kodu} - {proje_adi}</strong> projesine ait <strong>{toplanti_baslik}</strong> toplantısı tamamlanmış ve resmi tutanağı sisteme kaydedilmiştir.</p><p>📅 Tarih: <strong>{tarih}</strong><br>📍 Lokasyon: <strong>{lokasyon}</strong></p>{ek_not}',
                'available_variables' => ['toplanti_baslik', 'proje_kodu', 'proje_adi', 'islem_yapan', 'tarih', 'lokasyon', 'ek_not', 'kullanici_adi', 'departman_adi'],
            ],
            [
                'event_key' => 'toplanti_yeniden_planlandi',
                'event_title' => 'Toplantı Yeniden Planlandı',
                'category' => 'Toplantı',
                'bell_title_template' => 'Toplantı Yeniden Planlandı: {toplanti_baslik}',
                'bell_body_template' => '**{islem_yapan}** tarafından **{proje_kodu}** projesine bağlı **{toplanti_baslik}** toplantısı yeniden planlandı durumuna alınmıştır.',
                'mail_subject_template' => '[Toplantı Yeniden Planlandı] {proje_kodu} - {toplanti_baslik}',
                'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{islem_yapan}</strong> tarafından <strong>{proje_kodu} - {proje_adi}</strong> projesine ait <strong>{toplanti_baslik}</strong> toplantısı yeniden planlandı durumuna alınmıştır.</p><p>📅 Tarih & Saat: <strong>{tarih}</strong></p>{ek_not}',
                'available_variables' => ['toplanti_baslik', 'proje_kodu', 'proje_adi', 'islem_yapan', 'tarih', 'lokasyon', 'ek_not', 'kullanici_adi', 'departman_adi'],
            ],
            [
                'event_key' => 'toplanti_iptal_edildi',
                'event_title' => 'Toplantı İptal Edildi',
                'category' => 'Toplantı',
                'bell_title_template' => 'Toplantı İptal Edildi: {toplanti_baslik}',
                'bell_body_template' => '**{islem_yapan}** tarafından **{proje_kodu}** projesine bağlı **{toplanti_baslik}** toplantısı iptal edilmiştir.',
                'mail_subject_template' => '[Toplantı İptal Edildi] {proje_kodu} - {toplanti_baslik}',
                'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{islem_yapan}</strong> tarafından <strong>{proje_kodu} - {proje_adi}</strong> projesine ait <strong>{toplanti_baslik}</strong> toplantısı iptal edilmiştir.</p>{ek_not}',
                'available_variables' => ['toplanti_baslik', 'proje_kodu', 'proje_adi', 'islem_yapan', 'tarih', 'lokasyon', 'ek_not', 'kullanici_adi', 'departman_adi'],
            ],
        ];

        // Roller: Proje Yöneticisi (21), Proje Sponsoru (28), Bölüm Lideri (2), Direktör (8), Kullanıcı/Mühendis (3)
        $rolesToAttach = Role::whereIn('id', [1, 2, 3, 8, 21, 28])->get();

        foreach ($templates as $tplData) {
            $tpl = NotificationTemplate::updateOrCreate(
                ['event_key' => $tplData['event_key']],
                $tplData + ['is_active' => true]
            );

            foreach ($rolesToAttach as $role) {
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
}
