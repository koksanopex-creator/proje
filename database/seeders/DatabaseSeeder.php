<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Bolum;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Project;
use App\Models\ProjectPhase;
use App\Models\ProjectTask;
use App\Models\ProjectStakeholder;
use App\Models\ProjectRisk;
use App\Models\ProjectBom;
use App\Models\ProjectBudgetItem;
use App\Models\ProjectMeeting;
use App\Models\NotificationTemplate;
use App\Models\NotificationMatrix;
use App\Models\SystemSetting;
use App\Models\LogVisibilitySetting;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Sistem Ayarları
        SystemSetting::set('site_title', 'Köksan Enterprise PPM', 'string', 'general');
        SystemSetting::set('site_logo', 'assets/images/koksan_logo.png', 'image', 'branding');
        SystemSetting::set('currency_default', 'TRY', 'string', 'general');
        SystemSetting::set('company_name', 'Köksan Pet ve Plastik Ambalaj San. Tic. A.Ş.', 'string', 'general');

        // 2. Temel Roller
        $roleSuperadmin = Role::create(['id' => 1, 'name' => 'Superadmin (Developer)', 'slug' => 'superadmin', 'color' => '#dc2626', 'is_system' => true]);
        $roleBolumLideri = Role::create(['id' => 2, 'name' => 'Bölüm Lideri', 'slug' => 'bolum_lideri', 'color' => '#2563eb', 'is_system' => true]);
        $roleKullanici = Role::create(['id' => 3, 'name' => 'Kullanıcı / Mühendis', 'slug' => 'kullanici', 'color' => '#64748b', 'is_system' => true]);
        $roleDirektor = Role::create(['id' => 8, 'name' => 'Direktör', 'slug' => 'direktor', 'color' => '#7c3aed', 'is_system' => true]);
        $roleYonetim = Role::create(['id' => 19, 'name' => 'Yonetim (Read-Only)', 'slug' => 'yonetim', 'color' => '#059669', 'is_system' => true]);
        $roleAdmin = Role::create(['id' => 20, 'name' => 'Admin (Sistem Yöneticisi)', 'slug' => 'admin', 'color' => '#ea580c', 'is_system' => true]);
        
        $roleProjeYonetici = Role::create(['name' => 'Proje Yöneticisi', 'slug' => 'proje_yoneticisi', 'color' => '#0284c7']);
        $roleEhsAuditor = Role::create(['name' => 'İSG Denetleyicisi', 'slug' => 'isg_denetleyici', 'color' => '#b91c1c']);
        $roleEhsOfficer = Role::create(['name' => 'İSG Sorumlusu', 'slug' => 'isg_sorumlusu', 'color' => '#d97706']);
        $roleQaQc = Role::create(['name' => 'Kalite Denetleyicisi', 'slug' => 'kalite_denetleyici', 'color' => '#0d9488']);
        $roleLeadEngineer = Role::create(['name' => 'Teknik Lider / Baş Mühendis', 'slug' => 'teknik_lider', 'color' => '#4338ca']);
        $roleSiteSupervisor = Role::create(['name' => 'Saha / Şantiye Şefi', 'slug' => 'saha_sefi', 'color' => '#c2410c']);
        $roleSubcontractor = Role::create(['name' => 'Taşeron Temsilcisi', 'slug' => 'taseron', 'color' => '#475569']);
        $roleSponsor = Role::create(['name' => 'Proje Sponsoru', 'slug' => 'sponsor', 'color' => '#4f46e5']);

        // 3. İzinler (Permissions - Dinamik Yetki Matrisi)
        $perms = [
            // Proje İzinleri
            ['name' => 'Projeleri Görüntüleme', 'slug' => 'proje.view', 'group_name' => 'Projeler', 'type' => 'page'],
            ['name' => 'Yeni Proje Oluşturma', 'slug' => 'proje.create', 'group_name' => 'Projeler', 'type' => 'button'],
            ['name' => 'Proje Düzenleme', 'slug' => 'proje.edit', 'group_name' => 'Projeler', 'type' => 'button'],
            ['name' => 'Proje Silme', 'slug' => 'proje.delete', 'group_name' => 'Projeler', 'type' => 'button'],
            ['name' => 'Proje Onaylama / İlerletme', 'slug' => 'proje.approve', 'group_name' => 'Projeler', 'type' => 'button'],
            ['name' => 'Şablondan Klonlama', 'slug' => 'proje.clone', 'group_name' => 'Projeler', 'type' => 'button'],
            
            // İSG & Denetim İzinleri
            ['name' => 'İSG Modülünü Görüntüleme', 'slug' => 'isg.view', 'group_name' => 'İSG & Denetim', 'type' => 'page'],
            ['name' => 'İSG İzin Belgesi Yükleme', 'slug' => 'isg.upload_permit', 'group_name' => 'İSG & Denetim', 'type' => 'button'],
            ['name' => 'İSG Kapı Kilidi Onayı Verme', 'slug' => 'isg.approve', 'group_name' => 'İSG & Denetim', 'type' => 'button'],

            // Görev & Zaman İzinleri
            ['name' => 'Görev Yönetimi', 'slug' => 'task.manage', 'group_name' => 'Görevler & Zaman', 'type' => 'button'],
            ['name' => 'Zaman Girişi (Timesheet)', 'slug' => 'timesheet.create', 'group_name' => 'Görevler & Zaman', 'type' => 'button'],
            ['name' => 'Timesheet Onaylama', 'slug' => 'timesheet.approve', 'group_name' => 'Görevler & Zaman', 'type' => 'button'],

            // Bütçe, BOM & Maliyet İzinleri
            ['name' => 'Bütçe & BOM Görüntüleme', 'slug' => 'butce.view', 'group_name' => 'Bütçe & BOM', 'type' => 'page'],
            ['name' => 'Bütçe & BOM Yönetimi', 'slug' => 'butce.manage', 'group_name' => 'Bütçe & BOM', 'type' => 'button'],

            // Toplantı İzinleri
            ['name' => 'Toplantı Modülü Görüntüleme', 'slug' => 'meeting.view', 'group_name' => 'Toplantılar', 'type' => 'page'],
            ['name' => 'Toplantı Oluşturma & Karar Alma', 'slug' => 'meeting.manage', 'group_name' => 'Toplantılar', 'type' => 'button'],

            // Rapor & Dışa Aktarma
            ['name' => 'Raporları Görüntüleme (5 Rapor)', 'slug' => 'report.view', 'group_name' => 'Raporlar', 'type' => 'page'],
            ['name' => 'Veri Dışa Aktarma (Export Excel/PDF)', 'slug' => 'export.data', 'group_name' => 'Raporlar', 'type' => 'button'],

            // Sistem, Log ve Sağlık
            ['name' => 'Sistem Sağlığı Sayfası', 'slug' => 'health.view', 'group_name' => 'Sistem & Güvenlik', 'type' => 'page'],
            ['name' => 'Denetim Loglarını Görüntüleme', 'slug' => 'log.view', 'group_name' => 'Sistem & Güvenlik', 'type' => 'page'],
            ['name' => 'Yetki ve Bildirim Matrisi Yönetimi', 'slug' => 'matrix.manage', 'group_name' => 'Sistem & Güvenlik', 'type' => 'page'],
            ['name' => 'Sistem Ayarları & Logo Yönetimi', 'slug' => 'settings.manage', 'group_name' => 'Sistem & Güvenlik', 'type' => 'button'],
        ];

        $createdPerms = [];
        foreach ($perms as $p) {
            $createdPerms[$p['slug']] = Permission::create($p);
        }

        // Rol İzin Eşleştirmeleri
        // Superadmin: Hepsi
        $roleSuperadmin->permissions()->sync(collect($createdPerms)->pluck('id'));

        // Admin: Operasyonel hepsi
        $roleAdmin->permissions()->sync(collect($createdPerms)->pluck('id'));

        // Yonetim (Madde 19): SADECE view ve export
        $yonetimPerms = collect($createdPerms)->filter(function ($p, $slug) {
            return str_contains($slug, '.view') || $slug === 'export.data';
        })->pluck('id');
        $roleYonetim->permissions()->sync($yonetimPerms);

        // Proje Yöneticisi
        $pmPerms = collect($createdPerms)->filter(function ($p, $slug) {
            return in_array($slug, [
                'proje.view', 'proje.create', 'proje.edit', 'proje.approve', 'proje.clone',
                'task.manage', 'timesheet.create', 'timesheet.approve',
                'butce.view', 'butce.manage', 'meeting.view', 'meeting.manage',
                'report.view', 'export.data', 'log.view'
            ]);
        })->pluck('id');
        $roleProjeYonetici->permissions()->sync($pmPerms);

        // İSG Denetleyicisi
        $ehsPerms = collect($createdPerms)->filter(function ($p, $slug) {
            return in_array($slug, ['proje.view', 'isg.view', 'isg.upload_permit', 'isg.approve', 'report.view', 'log.view']);
        })->pluck('id');
        $roleEhsAuditor->permissions()->sync($ehsPerms);

        // 4. Bölümler
        $bolumGenel = Bolum::create(['ad' => 'Üst Yönetim & Holding', 'kod' => 'HOLDING']);
        $bolumInsaat = Bolum::create(['ad' => 'İnşaat ve Tesis Yatırımları', 'kod' => 'INSAAT']);
        $bolumEnerji = Bolum::create(['ad' => 'Enerji ve Yardımcı Tesisler', 'kod' => 'ENERJI']);
        $bolumMakine = Bolum::create(['ad' => 'Makine İmalat ve Otomasyon', 'kod' => 'MAKINE']);
        $bolumArge = Bolum::create(['ad' => 'Ar-Ge ve Proses İyileştirme', 'kod' => 'ARGE']);
        $bolumSap = Bolum::create(['ad' => 'Bilgi Teknolojileri & SAP', 'kod' => 'BT_SAP']);
        $bolumIsg = Bolum::create(['ad' => 'İş Sağlığı ve Güvenliği & Çevre', 'kod' => 'ISG']);
        $bolumKalite = Bolum::create(['ad' => 'Kalite Güvence ve Laboratuvar', 'kod' => 'KALITE']);
        $bolumPreform = Bolum::create(['ad' => 'Preform ve Kalıp Üretim', 'kod' => 'PREFORM']);

        // 5. Kullanıcılar (Test Hesapları)
        $userSuper = User::create([
            'id' => 1,
            'name' => 'Celal KARAMAN',
            'email' => 'celal.karaman@koksan.com',
            'sicil_no' => 'KOK-001',
            'bolum_id' => $bolumSap->id,
            'unvan' => 'Lead Software Architect',
            'phone' => '0555 111 2233',
            'password' => Hash::make('password123'),
        ]);
        $userSuper->roles()->attach([$roleSuperadmin->id]);

        $userAdmin = User::create([
            'id' => 20,
            'name' => 'Sistem Yöneticisi',
            'email' => 'admin@koksan.com',
            'sicil_no' => 'KOK-020',
            'bolum_id' => $bolumGenel->id,
            'unvan' => 'Sistem Yöneticisi',
            'password' => Hash::make('password123'),
        ]);
        $userAdmin->roles()->attach([$roleAdmin->id]);

        $userYonetim = User::create([
            'id' => 19,
            'name' => 'Yönetim Kurulu Denetçi',
            'email' => 'yonetim@koksan.com',
            'sicil_no' => 'KOK-019',
            'bolum_id' => $bolumGenel->id,
            'unvan' => 'Yönetim Kurulu Üyesi',
            'password' => Hash::make('password123'),
        ]);
        $userYonetim->roles()->attach([$roleYonetim->id]);

        $userDirektor = User::create([
            'id' => 8,
            'name' => 'Şenol Kanat',
            'email' => 'senol.kanat1@koksan.com',
            'sicil_no' => 'KOK-008',
            'bolum_id' => $bolumInsaat->id,
            'unvan' => 'Teknik & Yatırımlar Direktörü',
            'password' => Hash::make('senol.kanat1@koksan.com'),
        ]);
        $userDirektor->roles()->attach([$roleDirektor->id]);
        $bolumInsaat->update(['director_id' => $userDirektor->id]);
        $bolumEnerji->update(['director_id' => $userDirektor->id]);

        $userKalite = User::create([
            'id' => 7,
            'name' => 'Serkan Tölek',
            'email' => 'serkan.tolek1@koksan.com',
            'sicil_no' => 'KOK-007',
            'bolum_id' => $bolumKalite->id,
            'unvan' => 'Bölüm Kalite Yöneticisi',
            'password' => Hash::make('serkan.tolek1@koksan.com'),
        ]);
        $userKalite->roles()->attach([$roleQaQc->id]);

        $userPreformLider = User::create([
            'id' => 2,
            'name' => 'Emrah Al',
            'email' => 'emrah.al1@koksan.com',
            'sicil_no' => 'KOK-002',
            'bolum_id' => $bolumPreform->id,
            'unvan' => 'Preform Bölüm Lideri',
            'password' => Hash::make('emrah.al1@koksan.com'),
        ]);
        $userPreformLider->roles()->attach([$roleBolumLideri->id]);
        $bolumPreform->update(['lider_id' => $userPreformLider->id]);

        $userEhs = User::create([
            'name' => 'Ali Kaya',
            'email' => 'ali.kaya@koksan.com',
            'sicil_no' => 'KOK-014',
            'bolum_id' => $bolumIsg->id,
            'unvan' => 'Baş İSG Uzmanı & Denetçi',
            'password' => Hash::make('password123'),
        ]);
        $userEhs->roles()->attach([$roleEhsAuditor->id]);

        $userInsaatPm = User::create([
            'name' => 'Mehmet Demir',
            'email' => 'mehmet.demir@koksan.com',
            'sicil_no' => 'KOK-015',
            'bolum_id' => $bolumInsaat->id,
            'unvan' => 'Kıdemli İnşaat Proje Müdürü',
            'password' => Hash::make('password123'),
        ]);
        $userInsaatPm->roles()->attach([$roleProjeYonetici->id]);

        $userEnerjiPm = User::create([
            'name' => 'Ayşe Yılmaz',
            'email' => 'ayse.yilmaz@koksan.com',
            'sicil_no' => 'KOK-016',
            'bolum_id' => $bolumEnerji->id,
            'unvan' => 'Enerji Projeleri Lideri',
            'password' => Hash::make('password123'),
        ]);
        $userEnerjiPm->roles()->attach([$roleProjeYonetici->id]);

        // 6. Bildirim Şablonları & Bildirim Matrisi
        $template1 = NotificationTemplate::create([
            'event_key' => 'proje_onaya_gonderildi',
            'event_title' => 'Proje Onay Aşamasına Geldi',
            'category' => 'Proje',
            'bell_title_template' => 'Onay Bekleyen Proje: {proje_adi}',
            'bell_body_template' => '{proje_adi} projesi {rol} onayına iletildi. Bekleme süresi: {sure}',
            'mail_subject_template' => 'Onay Bildirimi: {proje_adi}',
            'mail_body_template' => '<p>Sayın Yetkili,</p><p><strong>{proje_adi}</strong> başlıklı proje <strong>{rol}</strong> onayınıza sunulmuştur. Lütfen sistemi ziyaret ederek onaylayınız.</p>',
            'available_variables' => ['proje_adi', 'rol', 'sure'],
        ]);

        $template2 = NotificationTemplate::create([
            'event_key' => 'isg_onayi_bekleniyor',
            'event_title' => 'Kritik Faz İçin İSG Onayı Gerekiyor',
            'category' => 'İSG',
            'bell_title_template' => 'İSG Kapı Kilidi Uyarısı: {proje_adi}',
            'bell_body_template' => '{proje_adi} projesinde {faz_adi} fazına geçiş için İSG Saha İzin Onayı bekleniyor.',
            'mail_subject_template' => 'Kritik İSG Denetçi Onayı: {proje_adi}',
            'mail_body_template' => '<p>Sayın İSG Denetçisi,</p><p><strong>{proje_adi}</strong> projesinde <strong>{faz_adi}</strong> fazı için çalışma izni belgesi yüklenmiş olup onayınız beklenmektedir.</p>',
            'available_variables' => ['proje_adi', 'faz_adi', 'kullanici'],
        ]);

        NotificationMatrix::create([
            'event_key' => 'proje_onaya_gonderildi',
            'role_id' => $roleDirektor->id,
            'is_bell_active' => true,
            'is_mail_active' => true,
            'template_id' => $template1->id,
        ]);

        NotificationMatrix::create([
            'event_key' => 'isg_onayi_bekleniyor',
            'role_id' => $roleEhsAuditor->id,
            'is_bell_active' => true,
            'is_mail_active' => true,
            'template_id' => $template2->id,
        ]);

        // 7. Log Görünürlük Ayarları
        foreach (Role::all() as $r) {
            LogVisibilitySetting::create([
                'role_id' => $r->id,
                'log_category' => 'project',
                'can_view_logs' => in_array($r->slug, ['superadmin', 'admin', 'direktor', 'proje_yoneticisi', 'yonetim']),
                'show_full_details' => in_array($r->slug, ['superadmin', 'admin']),
            ]);
        }

        // 8. Proje Şablonları (Templates)
        $tplInsaat = Project::create([
            'code' => 'TPL-INSAAT-01',
            'title' => 'Endüstriyel Tesis & Fabrika Binası Kurulum Şablonu',
            'description' => 'Zemin etüdü, temel kazısı, çelik hol, saha betonu ve İSG izin kapılarını içeren ana şablon.',
            'category' => 'insaat',
            'status' => 'draft',
            'priority' => 'high',
            'is_template' => true,
            'planned_budget' => 45000000.00,
        ]);

        $phase1 = $tplInsaat->phases()->create(['name' => '1. Ruhsat, Zemin Etüdü ve Projelendirme', 'sort_order' => 1, 'requires_ehs_approval' => false]);
        $phase2 = $tplInsaat->phases()->create(['name' => '2. Hafriyat ve Temel Kazısı', 'sort_order' => 2, 'requires_ehs_approval' => true]);
        $phase3 = $tplInsaat->phases()->create(['name' => '3. Çelik Konstrüksiyon Montajı', 'sort_order' => 3, 'requires_ehs_approval' => true]);
        $phase4 = $tplInsaat->phases()->create(['name' => '4. Yer Altı Drenaj ve Altyapı Hatları', 'sort_order' => 4, 'requires_ehs_approval' => true]);
        $phase5 = $tplInsaat->phases()->create(['name' => '5. Saha Betonu ve Geçici Kabul', 'sort_order' => 5, 'requires_ehs_approval' => false]);

        $tplInsaat->tasks()->create(['phase_id' => $phase2->id, 'title' => 'Kazı Güvenlik Bariyeri ve İkaz Hatları Kurulumu', 'priority' => 'high', 'tag' => '#İSG', 'estimated_hours' => 24]);
        $tplInsaat->tasks()->create(['phase_id' => $phase3->id, 'title' => 'Vinç Ağır Yük Kaldırma Planı ve Yüksekte Çalışma İzni', 'priority' => 'critical', 'tag' => '#İSG', 'estimated_hours' => 16]);

        $tplInsaat->risks()->create([
            'title' => 'Temel Kazısında Yeraltı Su Seviyesi Yükselmesi',
            'category' => 'teknik',
            'probability' => 3,
            'impact' => 4,
            'risk_score' => 12,
            'severity' => 'medium',
        ]);

        // Şablon 2: GES Projesi
        $tplGes = Project::create([
            'code' => 'TPL-GES-01',
            'title' => 'Çatı & Saha Güneş Enerjisi Santrali (GES) Şablonu',
            'description' => 'Statik taşıyıcı analizi, panel montajı, inverterler ve Yüksek Gerilim İSG onay kapısı.',
            'category' => 'ges_enerji',
            'status' => 'draft',
            'priority' => 'high',
            'is_template' => true,
            'planned_budget' => 28000000.00,
        ]);
        $tplGes->phases()->create(['name' => '1. Çatı Statik Uygunluk Analizi & Çağrı Mektubu', 'sort_order' => 1]);
        $tplGes->phases()->create(['name' => '2. Konstrüksiyon ve Panel Montajı', 'sort_order' => 2, 'requires_ehs_approval' => true]);
        $tplGes->phases()->create(['name' => '3. Trafo Bağlantısı ve TEDAŞ Kabul Testleri', 'sort_order' => 3, 'requires_ehs_approval' => true]);

        // 9. Aktif Canlı Proje Örneği 1: Gaziantep 2. OSB Yeni Fabrika Binası
        $prj1 = Project::create([
            'code' => 'PRJ-2026-001',
            'title' => 'Gaziantep 2. OSB Yeni Fabrika Binası & Zemin Drenaj Projesi',
            'description' => '12.000 m2 kapalı alan çelik konstrüksiyon üretim holü ve ağır tonaj zemin drenaj altyapısı.',
            'category' => 'insaat',
            'status' => 'in_progress',
            'priority' => 'critical',
            'location' => 'Gaziantep 2. OSB',
            'bolum_id' => $bolumInsaat->id,
            'manager_id' => $userInsaatPm->id,
            'sponsor_id' => $userDirektor->id,
            'planned_budget' => 52000000.00,
            'actual_spent' => 18450000.00,
            'currency' => 'TRY',
            'start_date' => now()->subDays(45)->toDateString(),
            'target_end_date' => now()->addDays(120)->toDateString(),
            'progress_percentage' => 38,
            'current_waiting_role_id' => $roleEhsAuditor->id,
            'current_waiting_user_id' => $userEhs->id,
            'waiting_since' => now()->subHours(14),
            'waiting_step_name' => 'Çelik Montaj Öncesi İSG Kapı Onayı',
            'is_template' => false,
            'created_by' => $userSuper->id,
        ]);

        $prj1Phase1 = $prj1->phases()->create([
            'name' => '1. Ruhsat & Zemin Etüdü',
            'sort_order' => 1,
            'status' => 'completed',
            'progress_percentage' => 100,
            'start_date' => now()->subDays(45),
            'end_date' => now()->subDays(20),
        ]);

        $prj1Phase2 = $prj1->phases()->create([
            'name' => '2. Temel Kazısı & Drenaj Borulama',
            'sort_order' => 2,
            'status' => 'in_progress',
            'progress_percentage' => 75,
            'start_date' => now()->subDays(20),
            'end_date' => now()->addDays(15),
            'requires_ehs_approval' => true,
            'ehs_approved_by' => $userEhs->id,
            'ehs_approved_at' => now()->subDays(18),
            'ehs_permit_document' => 'storage/isg_izinler/kazi_izni_onayli.pdf',
            'ehs_notes' => 'Tüm şev güvenlik açıları ve baret/ikaz standartları denetlendi, onaylandı.',
        ]);

        $prj1Phase3 = $prj1->phases()->create([
            'name' => '3. Çelik Konstrüksiyon Kolon Ankrajı',
            'sort_order' => 3,
            'status' => 'waiting_ehs',
            'progress_percentage' => 0,
            'start_date' => now()->addDays(16),
            'end_date' => now()->addDays(60),
            'requires_ehs_approval' => true,
        ]);

        // Paydaşlar (RACI)
        $prj1->stakeholders()->create(['user_id' => $userDirektor->id, 'role_type' => 'sponsor', 'raci_role' => 'A']);
        $prj1->stakeholders()->create(['user_id' => $userInsaatPm->id, 'role_type' => 'manager', 'raci_role' => 'R']);
        $prj1->stakeholders()->create(['user_id' => $userEhs->id, 'role_type' => 'ehs_auditor', 'raci_role' => 'C', 'notes' => 'Faz geçişlerinde İSG onay kapısı zorunlu.']);
        $prj1->stakeholders()->create(['user_id' => $userKalite->id, 'role_type' => 'qa_qc', 'raci_role' => 'C', 'notes' => 'C35 beton numune kırım raporları']);

        // Görevler
        $task1 = $prj1->tasks()->create([
            'phase_id' => $prj1Phase2->id,
            'title' => 'Q500 Koruge Drenaj Borularının Döşenmesi ve Eğim Kontrolü',
            'description' => 'Lazer nivo ile %1.5 drenaj eğimi verilerek boru serimi tamamlanacak.',
            'status' => 'in_progress',
            'priority' => 'high',
            'tag' => '#İnşaat',
            'assigned_to' => $userInsaatPm->id,
            'start_date' => now()->subDays(5),
            'due_date' => now()->addDays(5),
            'estimated_hours' => 40.0,
            'spent_hours' => 26.5,
            'progress_percentage' => 65,
        ]);

        $task2 = $prj1->tasks()->create([
            'phase_id' => $prj1Phase3->id,
            'title' => 'Ağır Tonaj Vinç Kurulumu & Yüksekte Çalışma İskele Kontrolü',
            'description' => '150 tonluk vinç bom açılımı ve şantiye güvenlik çemberi oluşturulacak.',
            'status' => 'todo',
            'priority' => 'critical',
            'tag' => '#İSG',
            'assigned_to' => $userEhs->id,
            'start_date' => now()->addDays(16),
            'due_date' => now()->addDays(20),
            'estimated_hours' => 16.0,
            'spent_hours' => 0.0,
        ]);

        // BOM Malzeme Ağacı
        $prj1->boms()->create([
            'item_code' => 'MLZ-KRG-500',
            'item_name' => 'SN8 Koruge Drenaj Borusu 500mm',
            'category' => 'insaat_malzeme',
            'quantity' => 1200,
            'unit' => 'Metre',
            'unit_price' => 850.00,
            'total_price' => 1020000.00,
            'status' => 'delivered',
            'supplier' => 'Dizayn Boru A.Ş.',
        ]);

        $prj1->boms()->create([
            'item_code' => 'MLZ-CLK-ST52',
            'item_name' => 'ST52 Ağır Çelik Kolon & Makas Konstrüksiyon',
            'category' => 'mekanik',
            'quantity' => 340,
            'unit' => 'Ton',
            'unit_price' => 48500.00,
            'total_price' => 16490000.00,
            'status' => 'ordered',
            'supplier' => 'Kardemir Çelik',
        ]);

        // Riskler (5x5 Matris)
        $prj1->risks()->create([
            'title' => 'Çelik Kolon Montajında Şiddetli Rüzgar ve Vinç Devrilme Riski',
            'category' => 'isg',
            'probability' => 3,
            'impact' => 5,
            'risk_score' => 15,
            'severity' => 'high',
            'status' => 'open',
            'owner_id' => $userEhs->id,
        ]);

        // Projeye Bağlı Toplantı
        $meeting1 = $prj1->meetings()->create([
            'title' => 'Şantiye Çelik Montaj & İSG Koordinasyon Toplantısı',
            'meeting_date' => now()->addDays(2)->setHour(10)->setMinute(0),
            'location' => '2. OSB Şantiye Ofisi / Konferans Salonu',
            'agenda' => "1. Temel beton kırım test sonuçlarının değerlendirilmesi\n2. Vinç kurulum sahası zemin sertleştirme kontrolü\n3. İSG Yüksekte Çalışma İzinlerinin imza süreci",
            'status' => 'scheduled',
            'organizer_id' => $userInsaatPm->id,
        ]);

        $meeting1->participants()->create(['user_id' => $userInsaatPm->id, 'status' => 'attending']);
        $meeting1->participants()->create(['user_id' => $userEhs->id, 'status' => 'attending']);
        $meeting1->participants()->create(['user_id' => $userDirektor->id, 'status' => 'attending']);
        $meeting1->participants()->create(['external_attendee_name' => 'Ahmet Kaya (Çelik Taşeron Şefi)', 'external_attendee_email' => 'ahmet@kayacelik.com', 'status' => 'attending']);

        // 10. İkinci Aktif Canlı Proje: Fabrika Çatı GES Projesi (3.2 MW)
        $prj2 = Project::create([
            'code' => 'PRJ-2026-002',
            'title' => 'Köksan Ana Fabrika Çatı GES Kurulumu (3.2 MWp)',
            'description' => 'Fabrika çatılarına 3.2 MW Güneş Enerjisi Santrali kurulumu, inverter odası ve trafo bağlantısı.',
            'category' => 'ges_enerji',
            'status' => 'in_progress',
            'priority' => 'high',
            'location' => 'Gaziantep Merkez Tesis',
            'bolum_id' => $bolumEnerji->id,
            'manager_id' => $userEnerjiPm->id,
            'sponsor_id' => $userDirektor->id,
            'planned_budget' => 31000000.00,
            'actual_spent' => 24800000.00,
            'currency' => 'TRY',
            'start_date' => now()->subDays(60)->toDateString(),
            'target_end_date' => now()->addDays(30)->toDateString(),
            'progress_percentage' => 82,
            'is_template' => false,
            'created_by' => $userSuper->id,
        ]);

        $prj2->stakeholders()->create(['user_id' => $userDirektor->id, 'role_type' => 'sponsor', 'raci_role' => 'A']);
        $prj2->stakeholders()->create(['user_id' => $userEnerjiPm->id, 'role_type' => 'manager', 'raci_role' => 'R']);
        $prj2->stakeholders()->create(['user_id' => $userEhs->id, 'role_type' => 'ehs_auditor', 'raci_role' => 'C']);
    }
}
