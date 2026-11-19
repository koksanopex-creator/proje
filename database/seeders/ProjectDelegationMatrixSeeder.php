<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use App\Models\NotificationTemplate;
use App\Models\NotificationMatrix;
use App\Models\LogVisibilitySetting;

class ProjectDelegationMatrixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // =========================================================================
        // 1. DİNAMİK YETKİ MATRİSİ İZİNLERİ (PERMISSIONS)
        // =========================================================================
        $newPermissions = [
            [
                'name' => 'Proje İçi Ekip Yetki Matrisini Yönetme',
                'slug' => 'project.manage_team_matrix',
                'group_name' => 'Proje Yönetimi',
            ],
            [
                'name' => 'Ayrılan Ekip Üyesinin Görev/Rollerini Devretme',
                'slug' => 'project.handover_member',
                'group_name' => 'Proje Yönetimi',
            ],
            [
                'name' => 'Kendi Adına Kurumsal Vekalet Verme',
                'slug' => 'delegation.create',
                'group_name' => 'Kullanıcı & Vekalet',
            ],
            [
                'name' => 'Tüm Vekaletleri Yönetme (Sistem Geneli)',
                'slug' => 'delegation.manage_all',
                'group_name' => 'Kullanıcı & Vekalet',
            ],
            [
                'name' => 'Proje Liderini Proje Ortasında Değiştirme',
                'slug' => 'project.change_manager',
                'group_name' => 'Proje Yönetimi',
            ],
        ];

        foreach ($newPermissions as $pData) {
            Permission::firstOrCreate(['slug' => $pData['slug']], $pData);
        }

        // Rol İzin Eşleştirmeleri
        $superadmin = Role::where('slug', 'superadmin')->first();
        $admin = Role::where('slug', 'admin')->first();
        $sponsor = Role::where('slug', 'sponsor')->first();
        $projeYoneticisi = Role::where('slug', 'proje_yoneticisi')->first();

        // Superadmin & Admin her şeye yetkili
        $allNewSlugs = ['project.manage_team_matrix', 'project.handover_member', 'delegation.create', 'delegation.manage_all', 'project.change_manager'];
        $allNewPermIds = Permission::whereIn('slug', $allNewSlugs)->pluck('id');

        if ($superadmin) {
            $superadmin->permissions()->syncWithoutDetaching($allNewPermIds);
        }
        if ($admin) {
            $admin->permissions()->syncWithoutDetaching($allNewPermIds);
        }

        // Proje Sponsoru: Lider Değiştirme ve Kendi Adına Vekalet
        if ($sponsor) {
            $sponsorPermIds = Permission::whereIn('slug', ['project.change_manager', 'delegation.create'])->pluck('id');
            $sponsor->permissions()->syncWithoutDetaching($sponsorPermIds);
        }

        // Proje Yöneticisi: Ekip Matrisi Yönetimi, Üye Devir ve Kendi Adına Vekalet
        if ($projeYoneticisi) {
            $pmPermIds = Permission::whereIn('slug', ['project.manage_team_matrix', 'project.handover_member', 'delegation.create'])->pluck('id');
            $projeYoneticisi->permissions()->syncWithoutDetaching($pmPermIds);
        }

        // Tüm rollere varsayılan olarak "Kendi Adına Vekalet Verme" (delegation.create) yetkisi tanımla
        $delegationCreateId = Permission::where('slug', 'delegation.create')->value('id');
        if ($delegationCreateId) {
            $allRoles = Role::all();
            foreach ($allRoles as $r) {
                // Yonetim rolü read-only kuralı (rules1.md Kural 19) hariç
                if ($r->slug === 'yonetim' || $r->id === 19) {
                    continue;
                }
                $r->permissions()->syncWithoutDetaching([$delegationCreateId]);
            }
        }

        // =========================================================================
        // 2. DİNAMİK BİLDİRİM ŞABLONLARI & BİLDİRİM MATRİSİ
        // =========================================================================
        $templates = [
            [
                'event_key' => 'proje_yetki_delege_edildi',
                'event_title' => 'Projede Yeni Yetki Tanımlandı',
                'category' => 'Proje',
                'bell_title_template' => 'Yetki Tanımlandı: {yetki_adi}',
                'bell_body_template' => '**{islem_yapan}** tarafından **{proje_kodu}** projesinde size **{yetki_adi}** yetkisi tanımlandı.',
                'mail_subject_template' => '[Yetki Tanımlandı] {proje_kodu} - {yetki_adi}',
                'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{islem_yapan}</strong> tarafından <strong>{proje_kodu} - {proje_adi}</strong> projesinde adınıza <strong>{yetki_adi}</strong> operasyonel yetkisi verilmiştir.</p>',
                'available_variables' => ['proje_kodu', 'proje_adi', 'islem_yapan', 'yetki_adi', 'kullanici_adi'],
            ],
            [
                'event_key' => 'uye_gorev_devri_yapildi',
                'event_title' => 'Proje Görevleri ve Rolü Size Devredildi',
                'category' => 'Proje',
                'bell_title_template' => 'Görev Devri: {proje_kodu}',
                'bell_body_template' => '**{ayrilan_kullanici}** kullanıcısının **{proje_kodu}** projesindeki görev ve yetkileri **{islem_yapan}** tarafından size devredildi.',
                'mail_subject_template' => '[Görev Devri] {proje_kodu} - Görevler Size Aktarıldı',
                'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{islem_yapan}</strong> tarafından <strong>{proje_kodu} - {proje_adi}</strong> projesinde <strong>{ayrilan_kullanici}</strong> üzerindeki <strong>{gorev_sayisi}</strong> adet görev ve sorumluluklar size aktarılmıştır.</p><p>Ayrılma Gerekçesi: <em>{ayrilma_sebebi}</em></p>',
                'available_variables' => ['proje_kodu', 'proje_adi', 'islem_yapan', 'ayrilan_kullanici', 'gorev_sayisi', 'ayrilma_sebebi', 'kullanici_adi'],
            ],
            [
                'event_key' => 'vekalet_verildi',
                'event_title' => 'Adınıza Kurumsal Vekalet Bırakıldı',
                'category' => 'Sistem',
                'bell_title_template' => 'Vekalet Bırakıldı: {vekalet_veren}',
                'bell_body_template' => '**{vekalet_veren}**, **{baslangic_tarihi}** - **{bitis_tarihi}** tarihleri arasında adınıza vekalet tanımladı.',
                'mail_subject_template' => '[Kurumsal Vekalet] {vekalet_veren} Size Vekalet Bıraktı',
                'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{vekalet_veren}</strong> kullanıcısı <strong>{baslangic_tarihi}</strong> ile <strong>{bitis_tarihi}</strong> tarihleri arasında sistemsel işlemler için sizi vekil tayin etmiştir.</p><p>Kapsam: <strong>{kapsam}</strong><br>Not: <em>{notlar}</em></p>',
                'available_variables' => ['vekalet_veren', 'baslangic_tarihi', 'bitis_tarihi', 'kapsam', 'notlar', 'kullanici_adi'],
            ],
            [
                'event_key' => 'vekaleten_islem_yapildi',
                'event_title' => 'Vekaletiniz Kullanılarak İşlem Yapıldı',
                'category' => 'Sistem',
                'bell_title_template' => 'Vekaleten İşlem: {islem_adi}',
                'bell_body_template' => 'Vekiliniz **{vekil_adi}**, adınıza **{proje_kodu}** projesinde **{islem_adi}** işlemini gerçekleştirdi.',
                'mail_subject_template' => '[Vekalet Bildirimi] Vekiliniz Adınıza İşlem Yaptı',
                'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p>Vekiliniz <strong>{vekil_adi}</strong> tarafından adınıza <strong>{proje_kodu}</strong> projesinde <strong>{islem_adi}</strong> işlemi gerçekleştirilmiştir.</p><p>Zaman: <strong>{zaman}</strong></p>',
                'available_variables' => ['vekil_adi', 'proje_kodu', 'islem_adi', 'zaman', 'kullanici_adi'],
            ],
            [
                'event_key' => 'proje_lideri_degistirildi',
                'event_title' => 'Proje Liderliği Size Devredildi',
                'category' => 'Proje',
                'bell_title_template' => 'Proje Liderliği Devredildi: {proje_kodu}',
                'bell_body_template' => '**{proje_kodu}** projesinin liderliği **{islem_yapan}** tarafından resmi olarak size devredildi.',
                'mail_subject_template' => '[Proje Liderliği] {proje_kodu} Projesine Lider Olarak Atandınız',
                'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{proje_kodu} - {proje_adi}</strong> projesinin liderliği <strong>{islem_yapan}</strong> tarafından resmi olarak size devredilmiştir.</p>',
                'available_variables' => ['proje_kodu', 'proje_adi', 'islem_yapan', 'kullanici_adi'],
            ],
            [
                'event_key' => 'vekalet_iptal_edildi',
                'event_title' => 'Kurumsal Vekalet İptal Edildi / Geri Alındı',
                'category' => 'Sistem',
                'bell_title_template' => 'Vekalet İptal Edildi: {vekalet_veren}',
                'bell_body_template' => '**{vekalet_veren}** adına tanımlanan vekalet sözleşmesi **{islem_yapan}** tarafından sonlandırıldı. (Gerekçe: {gerekce})',
                'mail_subject_template' => '[Vekalet İptali] {vekalet_veren} Vekaleti Sonlandırıldı',
                'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{vekalet_veren}</strong> adına tanımlı olan kurumsal vekalet sözleşmesi <strong>{islem_yapan}</strong> tarafından erken sonlandırılmış ve vekillik yetkileri geri alınmıştır.</p><p>Gerekçe / Açıklama: <em>{gerekce}</em></p>',
                'available_variables' => ['vekalet_veren', 'vekil_adi', 'islem_yapan', 'gerekce', 'kullanici_adi'],
            ],
            [
                'event_key' => 'vekalet_suresi_doldu',
                'event_title' => 'Kurumsal Vekalet Sözleşmesi Süresi Doldu',
                'category' => 'Sistem',
                'bell_title_template' => 'Vekalet Süresi Doldu: {vekalet_veren}',
                'bell_body_template' => '**{vekalet_veren}** ile aranızdaki kurumsal vekalet süresi ({bitis_tarihi}) doldu ve vekillik yetkileri otomatik olarak sona erdi.',
                'mail_subject_template' => '[Süre Dolumu] {vekalet_veren} Kurumsal Vekalet Süresi Doldu',
                'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{vekalet_veren}</strong> ile <strong>{vekil_adi}</strong> arasındaki kurumsal vekalet sözleşmesinin süresi (<strong>{bitis_tarihi}</strong>) dolmuş ve vekillik yetkileri sistem tarafından otomatik olarak sonlandırılmıştır.</p>',
                'available_variables' => ['vekalet_veren', 'vekil_adi', 'bitis_tarihi', 'kullanici_adi'],
            ],
        ];

        $rolesForNotification = Role::all();

        foreach ($templates as $tplData) {
            $tpl = NotificationTemplate::updateOrCreate(
                ['event_key' => $tplData['event_key']],
                $tplData + ['is_active' => true]
            );

            foreach ($rolesForNotification as $role) {
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

        // =========================================================================
        // 3. LOG GÖRÜNÜRLÜK MATRİSİ ('vekalet' KATEGORİSİ)
        // =========================================================================
        $keyRolesForLogs = Role::whereIn('slug', ['superadmin', 'admin', 'direktor', 'bolum_lideri', 'proje_yoneticisi', 'sponsor', 'yonetim'])->get();
        foreach ($keyRolesForLogs as $r) {
            LogVisibilitySetting::updateOrCreate(
                ['role_id' => $r->id, 'log_category' => 'vekalet'],
                ['can_view_logs' => true]
            );
        }
    }
}
