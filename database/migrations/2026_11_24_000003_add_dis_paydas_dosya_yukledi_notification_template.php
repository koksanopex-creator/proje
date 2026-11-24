<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\NotificationTemplate;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $existing = DB::table('notification_templates')->where('event_key', 'dis_paydas_dosya_yukledi')->first();

        $data = [
            'event_key' => 'dis_paydas_dosya_yukledi',
            'event_title' => 'Dış Paydaş / Tedarikçi Tarafından Teknik Belge Yüklendi',
            'category' => 'Dış Paydaş & Tedarikçi',
            'bell_title_template' => '📥 Yeni Belge: {proje_adi}',
            'bell_body_template' => '**{firma_adi}** firması, **{proje_adi}** projesine **{dosya_adi}** belgesini yükledi (İnceleme Bekliyor).',
            'mail_subject_template' => '📥 Dış Paydaş Belge Yükledi: {firma_adi} - {proje_adi}',
            'mail_body_template' => '<p>Sayın Yetkili,</p><p><strong>{proje_adi}</strong> projesi kapsamında dış paydaş / yüklenici firma <strong>{firma_adi}</strong> tarafından sisteme yeni teknik belge yüklenmiştir.</p><div style="background: #f8fafc; border-left: 4px solid #0284c7; padding: 12px 16px; margin: 16px 0; border-radius: 4px;"><p style="margin: 0 0 6px 0;">• Belge Adı: <strong>{dosya_adi}</strong></p><p style="margin: 0 0 6px 0;">• Belge Kategorisi: <strong>{kategori}</strong></p><p style="margin: 0 0 6px 0;">• Dış Paydaş: <strong>{firma_adi}</strong></p><p style="margin: 0;">• Yükleme Zamanı: <strong>{tarih}</strong></p></div><p>Belge Köksan proje ekibinin <strong>inceleme ve onayına</strong> sunulmuştur. Proje detay sayfasındaki Dış Paydaşlar sekmesinden belgeyi inceleyip onaylayabilir veya reddedebilirsiniz.</p>',
            'available_variables' => json_encode([
                'proje_adi',
                'proje_kodu',
                'firma_adi',
                'dosya_adi',
                'kategori',
                'tarih',
            ]),
            'is_active' => 1,
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('notification_templates')->where('event_key', 'dis_paydas_dosya_yukledi')->update($data);
        } else {
            $data['created_at'] = now();
            DB::table('notification_templates')->insert($data);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('notification_templates')->where('event_key', 'dis_paydas_dosya_yukledi')->delete();
    }
};
