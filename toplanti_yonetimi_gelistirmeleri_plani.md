# TOPLANTI YÖNETİMİ VE KOORDİNASYON GELİŞTİRMELERİ PLANI

## 1. GEREKSİNİM ANALİZİ VE HEDEFLER
Kullanıcımızın ilettiği talepler ve çözüm yaklaşımları:

1. **Toplantı Gündemi & Görüşülecek Maddeler Zengin Metin Alanı**:
   - Yeni toplantı oluşturma formunda (`meetings.index`) gündem maddeleri için `textarea` yerine Quill.js tabanlı modern zengin metin editörü (bold, italic, listeler, linkler) entegre edilecek.
   - Toplantı listesinde (`meetings.index`) ve detay sayfasında (`meetings.show`) kaydedilen zengin metin HTML içeriği formatı bozulmadan, şık bir kart içinde gösterilecek.

2. **Katılımcı Daveti (Zil & Mail) Dinamik Arama Kutusu ve Çoktan Seçim**:
   - Ham `<select multiple>` kaldırılacak; yerine anlık arama yapılabilen, seçilen kişileri chip/rozet olarak üstte toplayan, tek tıkla eklenebilen/kaldırılabilen AJAX/JS destekli çoktan seçmeli arama kutusu yerleştirilecek.
   - Sayfa ilk açıldığında tüm kullanıcılar devasa liste olarak DOM'a basılmayacak, arama kutusu ve aktif katılımcı seçim paneli ile hafifletilecek.

3. **Planlanan & Tamamlanan Toplantılar Tablosu / Listesi İyileştirmeleri**:
   - **Yetki Bazlı Filtreleme**: Kullanıcı sadece yetkili olduğu toplantıları görecek (Organizatör olduğu, katılımcı davet edildiği, proje yöneticisi/sponsoru olduğu, proje paydaşı olduğu veya `meeting.view_all` yetkisine sahip olduğu toplantılar).
   - **Sayfalama**: Sayfa başına en çok 10 toplantı gösterilecek (`paginate(10)`), alt kısma modern sayfalama çubuğu eklenecek.
   - **Anlık Arama & Filtreleme Çubuğu**: Tablonun üstüne toplantı başlığı, gündem içeriği, proje kodu/adı ve durum (Tümü, Planlandı, Devam Ediyor, Tamamlandı, Ertelendi, İptal) filtreleri yerleştirilecek.

4. **Toplantı Erteleme Altyapısı (Planlanan ve Toplantı Anında Erteleme)**:
   - Toplantıyı planlayan kişi (veya yetkili adminler), toplantıyı `scheduled` (planlandı) veya `in_progress` (toplantı anında) durumundayken erteleyebilecek.
   - Erteleme modalı açılacak: Yeni Tarih & Saat, yeni lokasyon (opsiyonel), erteleme gerekçesi/notu girilecek; katılımcılara zil ve mail bildirimi gönderme seçeneği sunulacak.
   - Veritabanına `postpone_reason` alanı eklenecek, `status` `'postponed'` (Ertelendi) olarak güncellenecek.
   - **Bildirim Matrisi Entegrasyonu**: `toplanti_ertelendi` olay şablonu oluşturulacak, Bildirim Matrisine Zil ve Mail kanalları varsayılan aktif olarak eklenecek; erteleme anında tüm davetlilere e-posta ve zil bildirimi otomatik gidecek ve `notification_logs` tablosuna kaydedilecek.

---

## 2. ADIM ADIM UYGULAMA PLANI

### A. Veritabanı ve Bildirim Matrisi Altyapısı
1. Migration oluşturma (`2026_11_19_000005_add_postpone_and_meeting_templates.php`):
   - `project_meetings` tablosuna `postpone_reason` (text, nullable) sütununu ekleme.
   - `notification_templates` tablosuna `toplanti_ertelendi` olay şablonunu ekleme.
   - `notification_matrix` tablosuna tüm roller için `toplanti_ertelendi` zil ve mail kurallarını tanımlama.
2. `ProjectMeeting.php` modelini güncelleme:
   - `postpone_reason` alanını `fillable` listesine ekleme.
   - `getStatusLabelAttribute` ve `getStatusBadgeStyleAttribute` metodlarına `'postponed'` durumunu ekleme (turuncu/kehribar erteleme rozeti).
   - `scopeForUser($query, $user)` yetki filtresi ekleme.

### B. NotificationMatrixService & Mail Bildirimleri
1. `NotificationMatrixService` içine `toplanti_ertelendi` eşleşmesini ve erteleme değişkenlerini (`eski_tarih`, `yeni_tarih`, `erteleme_notu`) ekleme.
2. Toplantı ertelendiğinde otomatik log kaydı oluşturulmasını sağlama (`notification_logs`).

### C. MeetingController Geliştirmeleri
1. `index()` metodunu güncelleme:
   - `ProjectMeeting::forUser($user)` ile yetki filtresi uygulama.
   - Metin araması (`search`), durum filtresi (`status`) ve proje filtresi (`project_id`).
   - `paginate(10)` ile sayfalama uygulama.
   - Hızlı kullanıcı arama endpoint'i veya arama verisi hazırlama.
2. `store()` metodunu güncelleme:
   - HTML zengin metin gündem kaydetme desteği.
3. `postpone()` metodunu oluşturma:
   - Yetki kontrolü (düzenleyen veya admin/manage yetkisi).
   - Erteleme tarihi, gerekçesi ve bildirim dağıtımı.
4. `searchUsers(Request $request)` metodunu ekleme (anlık katılımcı arama).

### D. Blade Arayüz Geliştirmeleri (`meetings/index.blade.php` ve `meetings/show.blade.php`)
1. **Gündem Zengin Metin Editörü**:
   - Quill.js editörünün oluşturma formuna eklenmesi.
   - Form submit anında gizli inputa senkronizasyon.
   - Kartlarda HTML gündemin formatlı gösterimi.
2. **Katılımcı Seçim Alanı**:
   - Modern arama kutusu, anlık filtreleme, seçilen katılımcı rozetleri.
3. **Toplantı Listesi & Filtre**:
   - Tablo üstü anlık arama ve durum filtresi barı.
   - 10 kayıtlık sayfalama (`{{ $meetings->links() }}`).
   - Liste kartlarına "⏱️ Ertele" butonu ve modalı.
4. **Detay Sayfası (`meetings/show.blade.php`)**:
   - Toplantı anında veya planlanan aşamada "⏱️ Toplantıyı Ertele" modalı ve aksiyonu.

### E. Test ve Canlı Dağıtım
1. Yerel ortamda migration ve testlerin yapılması (Quill editörü, arama, sayfalama, erteleme ve mail/zil bildirimi).
2. GitHub'a commit ve push.
3. Canlı sunucuda (`172.20.5.10`) `git pull`, migration çalıştırma, cache temizleme ve doğrulama.
