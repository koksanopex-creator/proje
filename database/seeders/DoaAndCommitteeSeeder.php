<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotificationTemplate;
use App\Models\NotificationMatrix;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Committee;
use App\Models\CommitteeMember;
use App\Models\DoaThresholdRule;
use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;

class DoaAndCommitteeSeeder extends Seeder
{
    public function run(): void
    {
        // 1. GLOBAL SİSTEM AYARLARI
        SystemSetting::updateOrCreate(
            ['key' => 'doa_budget_approval_engine_active'],
            [
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Dinamik Bütçe Onay Kademeleri ve Yetki Devri (DOA) Motoru Aktif mi?',
            ]
        );

        SystemSetting::updateOrCreate(
            ['key' => 'doa_base_currency'],
            [
                'value' => 'USD',
                'type' => 'string',
                'description' => 'Bütçe eşik kademelerinde baz alınan para birimi (USD, EUR, TRY)',
            ]
        );

        // 2. YENİ İZİNLERİ (PERMISSIONS) OLUŞTUR
        $permissions = [
            [
                'name' => 'Bütçe Eşik Kademelerini Yönetme',
                'slug' => 'manage_doa_rules',
                'group_name' => 'Bütçe & DOA',
            ],
            [
                'name' => 'Kurul ve Komiteleri Yönetme',
                'slug' => 'manage_committees',
                'group_name' => 'Kurul Yönetimi',
            ],
            [
                'name' => 'Kurul Oylamalarına Katılabilme',
                'slug' => 'vote_in_committee',
                'group_name' => 'Kurul Yönetimi',
            ],
            [
                'name' => 'Bütçe Kademelerini Görüntüleme',
                'slug' => 'view_doa_rules',
                'group_name' => 'Bütçe & DOA',
            ],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['slug' => $p['slug']], $p);
        }

        // İzinleri rollere bağla
        $superadmin = Role::where('slug', 'superadmin')->first();
        $admin = Role::where('slug', 'admin')->first();
        $direktor = Role::where('slug', 'direktor')->first();
        $projeYoneticisi = Role::where('slug', 'proje_yoneticisi')->first();

        $allDoaSlugs = ['manage_doa_rules', 'manage_committees', 'vote_in_committee', 'view_doa_rules'];
        $permIds = Permission::whereIn('slug', $allDoaSlugs)->pluck('id');

        if ($superadmin) {
            $superadmin->permissions()->syncWithoutDetaching($permIds);
        }
        if ($admin) {
            $admin->permissions()->syncWithoutDetaching($permIds);
        }
        if ($direktor) {
            $direktorPerms = Permission::whereIn('slug', ['vote_in_committee', 'view_doa_rules'])->pluck('id');
            $direktor->permissions()->syncWithoutDetaching($direktorPerms);
        }

        // 3. ÖRNEK KURULLAR OLUŞTUR
        $yatirimKurulu = Committee::firstOrCreate(
            ['slug' => 'yatirim_degerlendirme_kurulu'],
            [
                'name' => 'Yatırım Değerlendirme ve Bütçe Kurulu',
                'description' => '1.000.000 USD ve üzeri stratejik CAPEX yatırımlarını oylayan üst kurul.',
                'voting_type' => 'majority',
                'quorum_percentage' => 51,
                'color' => '#6366f1',
                'icon' => 'ti ti-building-bank',
                'is_active' => true,
            ]
        );

        $isgKurulu = Committee::firstOrCreate(
            ['slug' => 'isg_ve_cevre_ust_kurulu'],
            [
                'name' => 'İSG ve Çevre Üst Kurulu',
                'description' => 'Yüksek riskli endüstriyel faz ve ekipman yatırımlarını denetleyen kurul.',
                'voting_type' => 'unanimous',
                'quorum_percentage' => 100,
                'color' => '#ef4444',
                'icon' => 'ti ti-shield-alert',
                'is_active' => true,
            ]
        );

        // Kurullara ilk üyeleri ata (Superadmin, Direktörler ve Yöneticilerden)
        $initialUsers = User::whereIn('id', [1, 2, 3])->get();
        if ($initialUsers->isEmpty()) {
            $initialUsers = User::limit(3)->get();
        }

        foreach ($initialUsers as $index => $u) {
            CommitteeMember::firstOrCreate(
                [
                    'committee_id' => $yatirimKurulu->id,
                    'user_id' => $u->id,
                ],
                [
                    'member_role' => $index === 0 ? 'baskan' : 'uye',
                    'voting_weight' => 1.00,
                    'is_active' => true,
                ]
            );

            CommitteeMember::firstOrCreate(
                [
                    'committee_id' => $isgKurulu->id,
                    'user_id' => $u->id,
                ],
                [
                    'member_role' => $index === 0 ? 'baskan' : 'uye',
                    'voting_weight' => 1.00,
                    'is_active' => true,
                ]
            );
        }

        // 4. BÜTÇE EŞİK KADEMELERİNİ (DOA KURALLARI) OLUŞTUR
        $rules = [
            [
                'slug' => 'capex_tier_1_fast_track',
                'name' => 'Hızlı Onay Kademesi (Doğrudan Devreye Alma)',
                'rule_type' => 'project_approval',
                'currency_code' => 'USD',
                'min_amount' => 0.00,
                'max_amount' => 1000.00,
                'requires_approval' => false,
                'approver_type' => 'role',
                'priority_order' => 1,
                'is_active' => true,
                'description' => '1.000 USD altındaki düşük bütçeli projeler yöneticiyi rahatsız etmeden doğrudan devreye girer.',
            ],
            [
                'slug' => 'capex_tier_2_bolum_lideri',
                'name' => 'Bölüm Lideri Onay Kademesi',
                'rule_type' => 'project_approval',
                'currency_code' => 'USD',
                'min_amount' => 1000.01,
                'max_amount' => 50000.00,
                'requires_approval' => true,
                'approver_type' => 'role',
                'role_id' => Role::where('slug', 'bolum_lideri')->value('id') ?? 2,
                'priority_order' => 2,
                'is_active' => true,
                'description' => '1.000 - 50.000 USD arasındaki operasyonel projeler Bölüm Lideri onayına düşer.',
            ],
            [
                'slug' => 'capex_tier_3_direktor',
                'name' => 'Direktörlük / Genel Müdürlük Eşiği',
                'rule_type' => 'project_approval',
                'currency_code' => 'USD',
                'min_amount' => 50000.01,
                'max_amount' => 250000.00,
                'requires_approval' => true,
                'approver_type' => 'role',
                'role_id' => Role::where('slug', 'direktor')->value('id') ?? 8,
                'priority_order' => 3,
                'is_active' => true,
                'description' => '50.000 - 250.000 USD arasındaki yatırımlar Direktör onayına tabidir.',
            ],
            [
                'slug' => 'capex_tier_4_admin_ceo',
                'name' => 'Üst Yönetim / CEO Onay Kademesi',
                'rule_type' => 'project_approval',
                'currency_code' => 'USD',
                'min_amount' => 250000.01,
                'max_amount' => 1000000.00,
                'requires_approval' => true,
                'approver_type' => 'role',
                'role_id' => Role::where('slug', 'admin')->value('id') ?? 20,
                'priority_order' => 4,
                'is_active' => true,
                'description' => '250.000 - 1.000.000 USD arası büyük ölçekli projeler Üst Yönetim / CEO onayına düşer.',
            ],
            [
                'slug' => 'capex_tier_5_yatirim_kurulu',
                'name' => 'Yatırım Kurulu Oylama Kademesi',
                'rule_type' => 'project_approval',
                'currency_code' => 'USD',
                'min_amount' => 1000000.01,
                'max_amount' => null, // Üst limitsiz
                'requires_approval' => true,
                'approver_type' => 'committee',
                'committee_id' => $yatirimKurulu->id,
                'priority_order' => 5,
                'is_active' => true,
                'description' => '1.000.000 USD üzerindeki stratejik yatırımlar Yatırım Kurulu oylamasına sevk edilir.',
            ],
        ];

        foreach ($rules as $r) {
            DoaThresholdRule::updateOrCreate(['slug' => $r['slug']], $r);
        }

        // 5. BİLDİRİM VE MAİL ŞABLONLARINI (NOTIFICATION TEMPLATES) OLUŞTUR
        $templates = [
            [
                'event_key' => 'proje_kurul_oylamasina_dustu',
                'event_title' => 'Proje Kurul Oylamasına Sevk Edildi',
                'category' => 'Bütçe & Onay',
                'bell_title_template' => 'Kurul Oylaması: {proje_kodu} - {proje_adi}',
                'bell_body_template' => 'Üyesi olduğunuz **{kurul_adi}** kuruluna **{proje_kodu}** kodlu ve **{tutar}** bütçeli proje oylama için sevk edilmiştir.',
                'mail_subject_template' => '[Kurul Oylaması] {proje_kodu} - {kurul_adi} Oylama Çağrısı',
                'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p>Üyesi olduğunuz <strong>{kurul_adi}</strong> kuruluna onaylanmak üzere yeni bir proje iletilmiştir.</p><p>📁 Proje: <strong>{proje_kodu} - {proje_adi}</strong><br>💰 Planlanan Bütçe: <strong>{tutar}</strong><br>⚖️ Karar Modeli: <strong>{oylama_modeli}</strong><br>📅 Tarih: <strong>{tarih}</strong></p><p>Lütfen sisteme giriş yaparak oyunuzu kullanınız.</p>',
                'available_variables' => ['proje_adi', 'proje_kodu', 'kurul_adi', 'tutar', 'oylama_modeli', 'kullanici_adi', 'tarih'],
            ],
            [
                'event_key' => 'proje_kurul_karar_verildi',
                'event_title' => 'Kurul Kararı Kesinleşti',
                'category' => 'Bütçe & Onay',
                'bell_title_template' => 'Kurul Kararı: {proje_kodu} - {karar}',
                'bell_body_template' => '**{kurul_adi}** kurulu **{proje_kodu}** projesine ilişkin değerlendirmesini tamamlamış ve proje **{karar}** durumuna geçmiştir.',
                'mail_subject_template' => '[Kurul Kararı] {proje_kodu} - {karar}',
                'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{proje_kodu} - {proje_adi}</strong> projesi için <strong>{kurul_adi}</strong> tarafından yapılan oylama sonuçlanmıştır.</p><p>🏁 Nihai Karar: <strong>{karar}</strong><br>📝 Karar Açıklaması: <strong>{gerekce}</strong><br>📅 Tarih: <strong>{tarih}</strong></p>',
                'available_variables' => ['proje_adi', 'proje_kodu', 'kurul_adi', 'karar', 'gerekce', 'kullanici_adi', 'tarih'],
            ],
            [
                'event_key' => 'proje_doa_esik_onayi',
                'event_title' => 'Bütçe Eşik Onayı Bekleniyor',
                'category' => 'Bütçe & Onay',
                'bell_title_template' => 'Bütçe Onayı: {proje_kodu}',
                'bell_body_template' => '**{proje_kodu}** kodlu proje **{tutar}** bütçesi ile **{kural_adi}** kapsamında onayınıza sunulmuştur.',
                'mail_subject_template' => '[Bütçe Onay Talebi] {proje_kodu} - {tutar}',
                'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{proje_kodu} - {proje_adi}</strong> projesi <strong>{tutar}</strong> planlanan bütçesi ile yetki eşiğinize girmiştir.</p><p>🔍 Kural: <strong>{kural_adi}</strong><br>📅 Tarih: <strong>{tarih}</strong></p><p>Lütfen detayları inceleyerek onay/red işlemini gerçekleştiriniz.</p>',
                'available_variables' => ['proje_adi', 'proje_kodu', 'tutar', 'kural_adi', 'kullanici_adi', 'tarih'],
            ],
            [
                'event_key' => 'proje_hizli_onaylandi',
                'event_title' => 'Proje Hızlı Onaylandı (Doğrudan Devreye Alma)',
                'category' => 'Bütçe & Onay',
                'bell_title_template' => 'Hızlı Onay: {proje_kodu} Devreye Alındı',
                'bell_body_template' => '**{proje_kodu}** projesi bütçesi (**{tutar}**) hızlı onay eşiğinde olduğu için doğrudan devreye alınmıştır.',
                'mail_subject_template' => '[Doğrudan Devreye Alma] {proje_kodu} Projesi Başlatıldı',
                'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{proje_kodu} - {proje_adi}</strong> projesi bütçesi (<strong>{tutar}</strong>) sistem hızlı devreye alma sınırları içerisinde yer aldığından ek onay gerekmeksizin doğrudan <strong>Devam Ediyor</strong> aşamasına geçirilmiştir.</p>',
                'available_variables' => ['proje_adi', 'proje_kodu', 'tutar', 'kural_adi', 'kullanici_adi', 'tarih'],
            ],
        ];

        $rolesForNotif = Role::whereIn('id', [1, 2, 8, 20, 21, 28])->get();

        foreach ($templates as $t) {
            $createdTemplate = NotificationTemplate::updateOrCreate(
                ['event_key' => $t['event_key']],
                $t
            );

            // Bildirim Matrisi kayıtlarını varsayılan olarak aç
            foreach ($rolesForNotif as $role) {
                NotificationMatrix::firstOrCreate(
                    [
                        'event_key' => $t['event_key'],
                        'role_id' => $role->id,
                    ],
                    [
                        'template_id' => $createdTemplate->id,
                        'is_bell_active' => true,
                        'is_mail_active' => true,
                    ]
                );
            }
        }
    }
}
