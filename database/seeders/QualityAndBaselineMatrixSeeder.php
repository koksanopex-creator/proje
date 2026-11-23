<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use App\Models\NotificationTemplate;
use App\Models\NotificationMatrix;

class QualityAndBaselineMatrixSeeder extends Seeder
{
    public function run(): void
    {
        // =========================================================================
        // 1. YETKİ MATRİSİ (PERMISSIONS) TANIMLARI
        // =========================================================================
        $permissions = [
            [
                'name' => 'Kalite & Test Panosunu Görüntüleme',
                'slug' => 'quality.view',
                'group_name' => 'Kalite & Temel Çizgi',
                'description' => 'Kalite güvence panosunu, test kriterlerini ve NCR kayıtlarını görüntüleme yetkisi'
            ],
            [
                'name' => 'Kalite Kriteri Tanımlama & Test Sonuçlandırma',
                'slug' => 'quality.manage',
                'group_name' => 'Kalite & Temel Çizgi',
                'description' => 'Yeni kalite kriteri ekleme ve test sonuçlarını (Geçti/Kaldı/Tolerans) değerlendirme yetkisi'
            ],
            [
                'name' => 'Uygunsuzluk (NCR) Açma & DÖF Kapatma',
                'slug' => 'quality.ncr_manage',
                'group_name' => 'Kalite & Temel Çizgi',
                'description' => 'Kalite kusur raporu (NCR) açma, DÖF atama ve çözüm notuyla kapatma yetkisi'
            ],
            [
                'name' => 'Proje Temel Çizgisini (Baseline) Dondurma',
                'slug' => 'baseline.manage',
                'group_name' => 'Kalite & Temel Çizgi',
                'description' => 'Proje bütçe ve takvim temel çizgisini (Baseline) dondurma ve aktif referansı belirleme yetkisi'
            ],
        ];

        $createdPerms = [];
        foreach ($permissions as $pData) {
            $createdPerms[$pData['slug']] = Permission::updateOrCreate(
                ['slug' => $pData['slug']],
                $pData
            );
        }

        // Rol Yetkilendirmeleri:
        // Superadmin (1), Admin (20) -> Tüm yetkiler
        foreach ([1, 20] as $adminRoleId) {
            $role = Role::find($adminRoleId);
            if ($role) {
                $role->permissions()->syncWithoutDetaching(collect($createdPerms)->pluck('id'));
            }
        }

        // Kalite Denetleyicisi (24) -> quality.view, quality.manage, quality.ncr_manage
        $kaliteDenetleyiciRole = Role::find(24);
        if ($kaliteDenetleyiciRole) {
            $kaliteDenetleyiciRole->permissions()->syncWithoutDetaching([
                $createdPerms['quality.view']->id,
                $createdPerms['quality.manage']->id,
                $createdPerms['quality.ncr_manage']->id,
            ]);
        }

        // Proje Yöneticisi (21) -> quality.view, quality.manage, quality.ncr_manage, baseline.manage
        $pmRole = Role::find(21);
        if ($pmRole) {
            $pmRole->permissions()->syncWithoutDetaching(collect($createdPerms)->pluck('id'));
        }

        // Teknik Lider / Baş Mühendis (25) -> quality.view, quality.manage, quality.ncr_manage
        $techLeaderRole = Role::find(25);
        if ($techLeaderRole) {
            $techLeaderRole->permissions()->syncWithoutDetaching([
                $createdPerms['quality.view']->id,
                $createdPerms['quality.manage']->id,
                $createdPerms['quality.ncr_manage']->id,
            ]);
        }

        // Direktör (8) ve Bölüm Lideri (2) -> quality.view, baseline.manage
        foreach ([8, 2] as $dirId) {
            $dirRole = Role::find($dirId);
            if ($dirRole) {
                $dirRole->permissions()->syncWithoutDetaching([
                    $createdPerms['quality.view']->id,
                    $createdPerms['baseline.manage']->id,
                ]);
            }
        }

        // Yonetim (19) -> SADECE quality.view (Salt Okunur - Madde 19!)
        $yonetimRole = Role::find(19);
        if ($yonetimRole) {
            $yonetimRole->permissions()->syncWithoutDetaching([
                $createdPerms['quality.view']->id,
            ]);
        }

        // =========================================================================
        // 2. BİLDİRİM MATRİSİ (NOTIFICATION TEMPLATES) TANIMLARI
        // =========================================================================
        $templates = [
            [
                'event_key' => 'kalite_testi_sonuclandi',
                'event_title' => 'Kalite Testi Sonuçlandırıldı',
                'category' => 'Kalite',
                'bell_title_template' => 'Kalite Testi Değerlendirildi: {test_baslik}',
                'bell_body_template' => '**{islem_yapan}**, **{proje_kodu}** projesindeki **{test_baslik}** testini **{sonuc}** olarak sonuçlandırdı. (Ölçülen: **{olculen_deger}**)',
                'mail_subject_template' => '[Kalite Testi] {proje_kodu} - {test_baslik} ({sonuc})',
                'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{proje_kodu} - {proje_adi}</strong> projesi kapsamındaki <strong>{test_baslik}</strong> kalite kontrol testi <strong>{islem_yapan}</strong> tarafından <strong>{sonuc}</strong> olarak değerlendirilmiştir.</p><p>📊 Ölçülen Değer: <strong>{olculen_deger}</strong><br>📝 Değerlendirme Notu: <strong>{notlar}</strong><br>📅 Tarih: <strong>{tarih}</strong></p><p><a href="{proje_url}" style="display:inline-block;padding:8px 16px;background:#0284c7;color:#fff;text-decoration:none;border-radius:6px;font-weight:700;">Kalite Testini İncele &rarr;</a></p>',
                'available_variables' => ['proje_kodu', 'proje_adi', 'test_baslik', 'sonuc', 'olculen_deger', 'notlar', 'islem_yapan', 'tarih', 'kullanici_adi', 'proje_url'],
            ],
            [
                'event_key' => 'yeni_ncr_acildi',
                'event_title' => 'Yeni Kalite Uygunsuzluğu (NCR) Açıldı',
                'category' => 'Kalite',
                'bell_title_template' => '🚨 Yeni NCR Açıldı: {ncr_kodu} ({siddet})',
                'bell_body_template' => '**{islem_yapan}**, **{proje_kodu}** projesinde **{ncr_kodu}** kodlu **{siddet}** seviyesinde yeni uygunsuzluk bildirdi: **{ncr_baslik}**',
                'mail_subject_template' => '🚨 [NCR Uygunsuzluk] {proje_kodu} - {ncr_kodu}: {ncr_baslik}',
                'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{proje_kodu} - {proje_adi}</strong> projesinde <strong>{islem_yapan}</strong> tarafından yeni bir kalite uygunsuzluğu (NCR / DÖF) kaydı açılmıştır.</p><p>🚨 <strong>NCR Kodu:</strong> {ncr_kodu}<br>⚠️ <strong>Şiddet Derecesi:</strong> {siddet}<br>📌 <strong>Başlık:</strong> {ncr_baslik}<br>📄 <strong>Kusur / Sapma:</strong> {aciklama}<br>🔧 <strong>DÖF Planı:</strong> {dof_aksiyon}<br>👤 <strong>Sorumlu:</strong> {sorumlu}<br>📅 <strong>Termin:</strong> {termin}</p><p><a href="{proje_url}" style="display:inline-block;padding:8px 16px;background:#dc2626;color:#fff;text-decoration:none;border-radius:6px;font-weight:700;">NCR Detayına Git ve Aksiyon Al &rarr;</a></p>',
                'available_variables' => ['proje_kodu', 'proje_adi', 'ncr_kodu', 'siddet', 'ncr_baslik', 'aciklama', 'dof_aksiyon', 'sorumlu', 'termin', 'islem_yapan', 'tarih', 'kullanici_adi', 'proje_url'],
            ],
            [
                'event_key' => 'ncr_durumu_guncellendi',
                'event_title' => 'NCR Uygunsuzluğu Güncellendi / Kapatıldı',
                'category' => 'Kalite',
                'bell_title_template' => 'NCR Güncellendi: {ncr_kodu} -> {yeni_durum}',
                'bell_body_template' => '**{islem_yapan}**, **{proje_kodu}** projesindeki **{ncr_kodu}** kodlu uygunsuzluğu **{yeni_durum}** durumuna getirdi.',
                'mail_subject_template' => '[NCR Durum Güncellemesi] {proje_kodu} - {ncr_kodu}: {yeni_durum}',
                'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{proje_kodu} - {proje_adi}</strong> projesinde yer alan <strong>{ncr_kodu}</strong> kodlu kalite uygunsuzluğu <strong>{islem_yapan}</strong> tarafından <strong>{yeni_durum}</strong> durumuna güncellenmiştir.</p><p>📝 <strong>Çözüm / Aksiyon Notu:</strong> {cozum_notu}<br>📅 <strong>Tarih:</strong> {tarih}</p><p><a href="{proje_url}" style="display:inline-block;padding:8px 16px;background:#16a34a;color:#fff;text-decoration:none;border-radius:6px;font-weight:700;">NCR Kaydını İncele &rarr;</a></p>',
                'available_variables' => ['proje_kodu', 'proje_adi', 'ncr_kodu', 'yeni_durum', 'cozum_notu', 'islem_yapan', 'tarih', 'kullanici_adi', 'proje_url'],
            ],
            [
                'event_key' => 'temel_cizgi_donduruldu',
                'event_title' => 'Proje Temel Çizgisi (Baseline) Kilitlendi',
                'category' => 'Planlama',
                'bell_title_template' => '🔒 Temel Çizgi Kilitlendi: {versiyon}',
                'bell_body_template' => '**{islem_yapan}**, **{proje_kodu}** projesinin **{versiyon}** sürüm temel çizgisini kilitledi. Planlanan bütçe: **{planlanan_butce}**',
                'mail_subject_template' => '🔒 [Temel Çizgi Snapshot] {proje_kodu} - {versiyon} Donduruldu',
                'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{proje_kodu} - {proje_adi}</strong> projesinin <strong>{versiyon} ({surum_adi})</strong> resmi temel çizgisi <strong>{islem_yapan}</strong> tarafından dondurulmuştur.</p><p>💰 <strong>Planlanan Bütçe:</strong> {planlanan_butce}<br>ℹ️ <strong>Dondurma Gerekçesi:</strong> {aciklama}<br>📅 <strong>Tarih:</strong> {tarih}</p><p><a href="{proje_url}" style="display:inline-block;padding:8px 16px;background:#4f46e5;color:#fff;text-decoration:none;border-radius:6px;font-weight:700;">Temel Çizgi & EVM Kıyaslamasını Gör &rarr;</a></p>',
                'available_variables' => ['proje_kodu', 'proje_adi', 'versiyon', 'surum_adi', 'planlanan_butce', 'aciklama', 'islem_yapan', 'tarih', 'kullanici_adi', 'proje_url'],
            ],
        ];

        // Roller: Superadmin (1), Bölüm Lideri (2), Direktör (8), Proje Yöneticisi (21), Kalite Denetleyicisi (24), Proje Sponsoru (28)
        $rolesForNotification = Role::whereIn('id', [1, 2, 8, 21, 24, 28])->get();

        foreach ($templates as $tplData) {
            $tpl = NotificationTemplate::updateOrCreate(
                ['event_key' => $tplData['event_key']],
                $tplData
            );

            foreach ($rolesForNotification as $r) {
                NotificationMatrix::updateOrCreate(
                    [
                        'event_key' => $tpl->event_key,
                        'role_id' => $r->id,
                    ],
                    [
                        'is_bell_active' => true,
                        'is_mail_active' => true,
                    ]
                );
            }
        }
    }
}
