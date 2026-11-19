# Dinamik Bütçe Onay Kademeleri (DOA Matrix) & Kurul Yönetimi Görev Listesi

Bu liste, [dinamik_butce_doa_ve_kurul_matrisi_plani.md](file:///c:/Users/celal.karaman\Desktop\Projelerim\koksan_pms\dinamik_butce_doa_ve_kurul_matrisi_plani.md) planında tanımlanan maddelerin aşama aşama uygulanması için hazırlanmıştır.

---

- [x] **Faz 1: Veritabanı Şeması ve Modeller**
  - [x] `doa_threshold_rules` migration ve modeli (`DoaThresholdRule.php`)
  - [x] `committees` ve `committee_members` migration ve modelleri (`Committee.php`, `CommitteeMember.php`)
  - [x] `project_approval_requests` ve `committee_votes` migration ve modelleri (`ProjectApprovalRequest.php`, `CommitteeVote.php`)
  - [x] Model ilişkilerinin (`Project`, `User`, `Role`, `Bolum`) bağlanması

- [x] **Faz 2: DOA ve Oylama Servis Katmanı (İş Mantığı & No-Code Motoru)**
  - [x] `DoaThresholdService.php`:
    - Döviz kuru dönüştürme ve baz tutar hesaplama
    - Eşik kuralı eşleştirme motoru (`matchRule`)
    - Anlık kural snapshot'ı oluşturma ("O an olan olmuştur, geçmiş kural değişse de etkilenmez")
    - Doğrudan devreye alma (fast-track / oto onay) mantığı
  - [x] `CommitteeVotingService.php`:
    - Oy kaydetme ve karar nisabı hesaplama (oy birliği, oy çokluğu, tek üye onayı, nisap %)
    - Karar kesinleştiğinde proje durumunu otomatik ilerletme

- [x] **Faz 3: Matris & Bildirim & Log Entegrasyonları**
  - [x] **Bildirim Matrisi (`NotificationMatrix`)**:
    - `proje_kurul_oylamasina_dustu`, `proje_kurul_karar_verildi`, `proje_doa_esik_onayi`, `proje_hizli_onaylandi` şablonları
    - Dinamik alanların kalın (**...**) formatta oluşturulması
  - [x] **Yetki Matrisi (`permission_matrix`)**:
    - `manage_doa_rules`, `manage_committees`, `vote_in_committee`, `view_doa_rules` izin slug'ları
  - [x] **Log Görünürlük Matrisi (`AuditLogController`)**:
    - `doa`, `kurul` kategorilerinin matrise eklenmesi
    - Developer işlemlerini sadece Developer'ın görmesi; Admin işlemlerini Admin, Developer ve Yönetim'in görmesi kuralının uygulanması

- [x] **Faz 4: Admin Yönetim Arayüzleri**
  - [x] `/admin/doa-esikleri`: Bütçe eşik kademeleri CRUD, global aç/kapa switch'i, satır bazlı aktif/pasif, canlı simülatör
  - [x] `/admin/kurullar`: Kurul oluşturma, üye/başkan atama, oylama tipi belirleme paneli
  - [x] Sidebar ve Sister Tabs entegrasyonu

- [x] **Faz 5: Proje Detay Ekranı ve Kurul Oylama Paneli**
  - [x] `/projects/{id}` sayfasında kurul oylama widget'ı (Onayla / Reddet / Şerhli Onay / Çekimser)
  - [x] Canlı oy dağılımı, ilerleme çubuğu ve oylama tutanağı geçmişi
  - [x] Durum etiketlerinin Köksan standartlarında (`waiting_approval` + `waiting_step_name`) gösterimi
  - [x] DOA bütçe onay başlatma modalı

- [x] **Faz 6: Test ve Doğrulama**
  - [x] <$1.000 hızlı devreye alma testi (Doğrulandı: Talep `bypassed_auto`, Proje `in_progress`)
  - [x] $15.000 Bölüm Lideri eşik testi (Doğrulandı)
  - [x] >$1.000.000 Yatırım Kurulu oylaması testi (Doğrulandı: Kurul oylaması tetiklendi)
  - [x] Kurul oylaması nisap testi (Doğrulandı: 2 onay ile çoğunluk sağlandı ve proje `in_progress` oldu)
  - [x] Developer / Admin log görünürlük filtreleri (Doğrulandı)
