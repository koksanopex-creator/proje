# KÖKSAN ENTERPRISE PPM
# PROJE İÇİ YETKİ MATRİSİ, GÖREV DEVRİ VE VEKALET SİSTEMİ GÖREV LİSTESİ

- [x] **1. Veritabanı Şeması & Migrasyonlar**
  - [x] `2026_10_19_000001_create_project_stakeholder_permissions_table.php` (Proje içi delege izinler tablosu)
  - [x] `2026_10_19_000002_create_user_delegations_table.php` (Kullanıcı kurumsal vekalet tablosu)
  - [x] `2026_10_19_000003_add_handover_fields_to_project_stakeholders.php` (`is_active`, `left_at`, `left_reason`, `handover_to_user_id`)
  - [x] `php artisan migrate` komutunun çalıştırılması ve doğrulanması

- [x] **2. Modeller ve İlişki Metotları**
  - [x] `ProjectStakeholderPermission.php` modelinin oluşturulması (`project`, `user`, `granter` ilişkileri)
  - [x] `UserDelegation.php` modelinin oluşturulması (`delegator`, `delegatee`, `creator` ilişkileri, aktiflik ve tarih sorgu scope'ları)
  - [x] `ProjectStakeholder.php` modeline devir alanlarının ve `handoverToUser` ilişkisinin eklenmesi
  - [x] `Project.php` modeline `hasStakeholderPermission($user, $permissionKey)` metodunun eklenmesi
  - [x] `User.php` modeline `getActiveDelegation()`, `getEffectiveUsersFor()` ve `isProxyFor()` metotlarının eklenmesi

- [x] **3. Dinamik Matris Kayıtları & Seeder**
  - [x] `ProjectDelegationMatrixSeeder.php` oluşturulması
  - [x] Dinamik Yetki Matrisine 5 yeni slug'ın eklenmesi (`project.manage_team_matrix`, `project.handover_member`, `delegation.create`, `delegation.manage_all`, `project.change_manager`)
  - [x] Dinamik Bildirim Matrisine 5 yeni şablonun eklenmesi (`proje_yetki_delege_edildi`, `uye_gorev_devri_yapildi`, `vekalet_verildi`, `vekaleten_islem_yapildi`, `proje_lideri_degistirildi`)
  - [x] Log Görünürlük Matrisine `vekalet` kategorisinin kaydedilmesi
  - [x] Seeder'ın çalıştırılması

- [x] **4. Servis Katmanı ve İş Mantığı Geliştirmesi**
  - [x] `DelegationService.php` servisinin yazılması (Vekalet oluşturma, iptal etme, aktif vekil çözümleme, döngü engelleme)
  - [x] `ProjectStakeholderService.php` servisinin genişletilmesi:
    - [x] `toggleStakeholderPermission(...)` (Mikro yetki açma/kapatma)
    - [x] `handoverMember(...)` (Ayrılan personeli pasife alma, görevleri ve yetkileri aktarma)
    - [x] `changeProjectManager(...)` (Proje liderini ortasında devretme)
    - [x] "Separation of Duties (Çıkar Çatışması Kalkanı)" kontrolleri
  - [x] `SystemHealthService.php` içine delege matrisi ve vekalet sağlığı kontrollerinin eklenmesi

- [x] **5. Kontrolörler ve Rotalar**
  - [x] `DelegationController.php` (Vekalet listesi, oluşturma, iptal etme endpoint'leri)
  - [x] `ProjectController` içine delege izin toggle, üye devir (handover) ve lider değiştirme endpoint'lerinin eklenmesi
  - [x] `routes/web.php` rotalarının tanımlanması

- [x] **6. Kullanıcı Arayüzü (UI / UX)**
  - [x] `resources/views/projects/show.blade.php`:
    - [x] "👥 10. Ekip & Yetki Matrisi" sekmesi ve canlı AJAX toggle anahtarları
    - [x] "🔄 Görev & Rol Devret" Modal Sihirbazı (`#modalHandoverMember`)
    - [x] "👑 Proje Liderini Değiştir" Modalı (`#modalChangeManager`)
  - [x] `resources/views/layouts/app.blade.php`:
    - [x] Topbar "🛡️ Vekaletlerim" butonu ve modalı (`#modalGlobalDelegation`)
    - [x] Aktif vekillik durumunda üst bant rozeti ve bilgilendirme
  - [x] `resources/views/admin/audit_logs.blade.php`:
    - [x] `vekalet` sekmesi ve filtreleme entegrasyonu
  - [x] `resources/views/admin/system_health.blade.php`:
    - [x] Delege Matrisi ve Vekalet Sağlık Kartları (Canlı retest ile senkronize)

- [x] **7. Canlı Doğrulama ve Testler**
  - [x] Tarayıcı subagent ile delege yetki açma/kapama testi
  - [x] Personel devir (handover) sihirbazı ve modal kontrolleri doğrulaması
  - [x] Vekalet bırakma ve vekaleten onay verme mekanizması
  - [x] Sistem sağlığı sayfasında teşhislerin yeşil (HEALTHY) yandığını doğrulama

- [x] **8. Ek Kullanıcı Talepleri (Dinamik Yetki Kapsamı & İptal / Süre Dolumu Bildirimleri)**
  - [x] **Dinamik Kapsam Yetki Kataloğu**: Kullanıcının bizzat sahip olduğu yetkiler dinamik gruplanarak listelenir (`User::getDelegatablePermissions()`).
  - [x] **Granüler / Kısmi Yetki Seçimi**: Tek tek yetki verip almama imkanı (Örn: Proje onayı verip ekip üyesi davetini hariç tutma).
  - [x] **Hızlı Ön Ayar Butonları**: `🌟 Tüm Yetkilerimi Seç`, `📑 Yalnızca Onay Yetkileri`, `📋 Yalnızca Görev & Planlama`, `✕ Temizle` ve grup bazında toplu seçim.
  - [x] **Proje Sayfası Aktif Vekalet Bilgi Bandı**: Projeye giren tüm kullanıcılara vekil, vekalet veren, tarihler ve gerekçeyi gösteren kurumsal duyuru bandı.
  - [x] **Vekalet İptal Bildirimi (`vekalet_iptal_edildi`)**: Erken iptal durumunda kullanıcıdan gerekçe alınması, audit log yazılması ve taraflara hem zil hem e-posta bildiriminin matris üzerinden gitmesi.
  - [x] **Süre Dolumu Otomatik Sonlandırma & Bildirimi (`vekalet_suresi_doldu`)**: `delegations:check-expired` artisan komutu ve her 15 dakikada bir çalışan zamanlayıcı (cron) ile süresi dolanların pasife alınıp her iki tarafa zil ve mail gitmesi.

- [x] **9. Vekalet Sebebi & Kişisel Not Ayrımı ve Şeffaf Yetki Kapsamı Modalı**
  - [x] **Veritabanı Yapısı**: `reason_category` (Vekalet Sebebi/Kategorisi) ve `notes` (Kişisel Mesaj / Ek Açıklama) ayrımı sağlandı.
  - [x] **Kurumsal Kategori Seçici**: 7 standart vekalet kategorisi (Yıllık İzin, Yurt Dışı Görevi, Saha Ziyareti vb.) + serbest metin kişisel açıklama kutusu.
  - [x] **Canlı Duyuru Bandı Zenginleştirmesi**: Tarih, Sebep etiketi, Kişisel mesaj (`💬 "..."`) ve tıklanabilir yetki rozeti (`🎯 5 Yetki Devredildi (Kapsamı Gör ↗)`).
  - [x] **Yetki Kapsamı & Sınırları Modalı (`#modalDelegationScopeDetail`)**:
    - [x] Vekalet Veren ➔ Vekil Temsil Kartı (İsimler, departmanlar, unvanlar).
    - [x] Geçerlilik tarihleri, vekalet nedeni ve kişisel mesaj şeridi.
    - [x] 6 fonksiyonel grup altında devredilen (`✓ Yetki Devredildi`) ve kapsam dışı bırakılan (`✕ Kapsam Dışı / Yetkisiz`) yetkilerin açık listesi.
    - [x] Proje ekibi bilgilendirme notu: *"Vekil kullanıcı yalnızca yukarıda 'Yetki Devredildi' olarak belirtilen alanlarda işlem yapabilir..."* vurgusu.
  - [x] **Evrensel Erişim**: Proje bandı, Topbar vekalet rozeti ve Vekaletlerim sekmesinden doğrudan açılabilirlik.

