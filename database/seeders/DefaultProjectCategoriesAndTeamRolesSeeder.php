<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultProjectCategoriesAndTeamRolesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Kategoriler
        $categories = [
            ['id' => 1, 'name' => 'İnşaat & Tesis Altyapı', 'slug' => 'insaat', 'color' => '#ea580c', 'description' => 'Fabrika, hol, drenaj ve zemin altyapı projeleri', 'sort_order' => 1],
            ['id' => 2, 'name' => 'GES Enerji Santrali', 'slug' => 'ges_enerji', 'color' => '#16a34a', 'description' => 'Çatı ve arazi güneş enerji santrali kurulumları', 'sort_order' => 2],
            ['id' => 4, 'name' => 'Makine İmalat & Montaj', 'slug' => 'makine', 'color' => '#2563eb', 'description' => 'Üretim hatları, mekanik kaynak ve montaj projeleri', 'sort_order' => 4],
            ['id' => 5, 'name' => 'Ar-Ge & Proses İyileştirme', 'slug' => 'arge', 'color' => '#7c3aed', 'description' => 'Verimlilik, kalıp ve kimyasal proses geliştirme', 'sort_order' => 5],
            ['id' => 7, 'name' => 'Preform & Kalıp Devreye Alma', 'slug' => 'preform_kalip', 'color' => '#0d9488', 'description' => 'PET preform enjeksiyon ve şişirme kalıpları', 'sort_order' => 7],
            ['id' => 8, 'name' => 'İSG & Çevre Yatırımları', 'slug' => 'isg_cevre', 'color' => '#dc2626', 'description' => 'İş sağlığı, yangın güvenliği ve çevre arıtma projeleri', 'sort_order' => 0],
            ['id' => 9, 'name' => 'SAP & BT Dijital Dönüşüm', 'slug' => 'sap_bt', 'color' => '#0284c7', 'description' => 'SAP S/4HANA, entegrasyonlar ve altyapı projeleri', 'sort_order' => 6],
            ['id' => 10, 'name' => 'LPG Boru Hattı', 'slug' => 'lpg_hatti', 'color' => '#d97706', 'description' => 'Endüstriyel LPG borulama ve tank sahası projeleri', 'sort_order' => 3],
            ['id' => 20, 'name' => 'Üretim ve Kapasite Artırımı', 'slug' => 'uretim_ve_kapasite_artirimi', 'color' => '#98b497', 'description' => 'Üretimin kapasitesini arttırmak için gerekli projeler', 'sort_order' => 8],
        ];

        foreach ($categories as $cat) {
            DB::table('project_categories')->updateOrInsert(
                ['slug' => $cat['slug']],
                array_merge($cat, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // 2. Ekip Fonksiyon Rolleri
        $teamRoles = [
            ['id' => 1, 'name' => 'Mekanik İmalat & Montaj Sorumlusu', 'description' => 'Proje ekibi Mekanik İmalat & Montaj Sorumlusu kurumsal fonksiyonu.', 'sort_order' => 1],
            ['id' => 2, 'name' => 'Elektrik & Otomasyon Mühendisi', 'description' => 'Proje ekibi Elektrik & Otomasyon Mühendisi kurumsal fonksiyonu.', 'sort_order' => 2],
            ['id' => 3, 'name' => 'Yazılım & BT Sistem Entegratörü', 'description' => 'Proje ekibi Yazılım & BT Sistem Entegratörü kurumsal fonksiyonu.', 'sort_order' => 3],
            ['id' => 4, 'name' => 'Saha & Şantiye Şefi', 'description' => 'Proje ekibi Saha & Şantiye Şefi kurumsal fonksiyonu.', 'sort_order' => 4],
            ['id' => 5, 'name' => 'İSG & Saha Güvenlik Temsilcisi', 'description' => 'Proje ekibi İSG & Saha Güvenlik Temsilcisi kurumsal fonksiyonu.', 'sort_order' => 5],
            ['id' => 6, 'name' => 'Kalite Kontrol & Kabul Test Mühendisi', 'description' => 'Proje ekibi Kalite Kontrol & Kabul Test Mühendisi kurumsal fonksiyonu.', 'sort_order' => 6],
            ['id' => 7, 'name' => 'Satın Alma & Lojistik Sorumlusu', 'description' => 'Proje ekibi Satın Alma & Lojistik Sorumlusu kurumsal fonksiyonu.', 'sort_order' => 7],
            ['id' => 8, 'name' => 'Maliyet & Bütçe Denetçisi', 'description' => 'Proje ekibi Maliyet & Bütçe Denetçisi kurumsal fonksiyonu.', 'sort_order' => 8],
            ['id' => 9, 'name' => 'Proje Saha Koordinatörü', 'description' => 'Proje ekibi Proje Saha Koordinatörü kurumsal fonksiyonu.', 'sort_order' => 9],
            ['id' => 10, 'name' => 'Proses & Verimlilik Mühendisi', 'description' => 'Proje ekibi Proses & Verimlilik Mühendisi kurumsal fonksiyonu.', 'sort_order' => 10],
            ['id' => 11, 'name' => 'Diğer / Özel Fonksiyon', 'description' => 'Proje ekibi Diğer / Özel Fonksiyon kurumsal fonksiyonu.', 'sort_order' => 11],
        ];

        foreach ($teamRoles as $tr) {
            DB::table('project_team_roles')->updateOrInsert(
                ['name' => $tr['name']],
                array_merge($tr, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
