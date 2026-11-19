# KURULA ÜYE ATA SEÇENEĞİ ARAMA KUTUSU VE SAYFALAMA PLANI

## 1. AMAÇ VE GEREKSİNİMLER
Kullanıcının ilettiği talep:
> "kurula üye ata seçenği arama kutulu olsun devasa liste burada görünrüse sorun olur. alfabetik sırada ilk 20-30 filan görünür sayfa sayfa görünür. üstteki anlık arama kutusu ile de üyeler ekrana gelir"

Sistemde binlerce personel olduğunda modal içerisindeki devasa `<select>` açılır kutusu hem arayüzü dondurmakta hem de mobilde/masaüstünde kullanılmaz hale gelmektedir.
Yapılacak geliştirme ile:
1. Modal içerisindeki standart `<select>` yerine dinamik, hızlı, arama kutulu ve sayfalamalı (sayfa başına 25 kullanıcı) bir personel seçim bileşeni inşa edilecek.
2. İlk açılışta veya arama kutusu boşken kullanıcılar alfabetik sırada (`name ASC`) ilk 25 kişi olarak listelenecek.
3. Listenin altında `« Önceki`, `Sonraki »` ve `Sayfa X / Y (Toplam Z Personel)` kontrolleri yer alacak.
4. Üstteki anlık arama kutusu (`oninput` / 250ms debounce) ile isim, e-posta, unvan veya sicil no yazıldığında eşleşen kullanıcılar anında sayfalanmış olarak listelenecek.
5. Kullanıcı bir personele tıkladığında kart aktifleşecek (`border-color: #0284c7`, `background: #f0f9ff`), üstte seçili personel özeti belirecek ve gizli `<input type="hidden" name="user_id" required>` alanına `user_id` yazılacak.
6. Eğer personel zaten o kurulda kayıtlıysa kart üzerinde `(Zaten Kurul Üyesi)` uyarısı gösterilecek.
7. `CommitteeController::index` fonksiyonundan devasa `User::all()` sorgusu kaldırılarak sunucu ve sayfa açılış performansı maksimize edilecek; kullanıcılar `admin/kurullar/search-users` AJAX uç noktası üzerinden sayfa sayfa çekilecek.

---

## 2. YAPILACAK DEĞİŞİKLİKLER VE TEKNİK DETAYLAR

### A. Rota Tanımı (`routes/web.php`)
- `Route::get('/kurullar/search-users', [CommitteeController::class, 'searchUsers'])->name('committees.search_users');`

### B. Controller Katmanı (`app/Http/Controllers/Admin/CommitteeController.php`)
- `index()`: `$allUsers = User::where('is_active', true)->orderBy('name')->get();` çağrısı kaldırılarak sayfa yükü hafifletilecek.
- `searchUsers(Request $request)` metodu eklenecek:
  - Parametreler: `q` (arama terimi), `page` (sayfa no), `per_page` (varsayılan 25), `committee_id` (ilgili kurul).
  - Arama kriterleri: `name`, `email`, `unvan`, `sicil_no`.
  - Sıralama: `name ASC`.
  - Kurul üyeliği kontrolü: `CommitteeMember` tablosundan kurulun mevcut üyeleri taranıp `already_member: true/false` bayrağı eklenecek.
  - JSON çıktı: `users`, `current_page`, `last_page`, `total`, `has_more`.

### C. Görünüm & Arayüz Katmanı (`resources/views/admin/committees/index.blade.php`)
- `addMemberModal` form gövdesi yenilenecek:
  - Gizli `input`: `<input type="hidden" name="user_id" id="selectedAddMemberUserId" required>`
  - Seçili Personel Bilgi Kartı (Seçim yapıldığında beliren yeşil/mavi onay kutusu)
  - Arama Kutusu: Büyüteç ikonu, anlık filtreleme, temizleme butonu (`✕`)
  - Liste Alanı: `max-height: 260px; overflow-y: auto;` içinde avatarlı, unvanlı, tıklanabilir kullanıcı satırları
  - Sayfalama Çubuğu: `Önceki`, `Sonraki`, sayfa numarası ve toplam kayıt sayısı
- JavaScript Mantığı:
  - `openAddMemberModal(committeeId, committeeName, defaultRole)`: Seçili kurulu belirler, arama kutusunu sıfırlar, `loadCommitteeUsers(1)` ile ilk 25 kullanıcıyı getirir.
  - `loadCommitteeUsers(page)`: Fetch ile AJAX isteği atar, yükleniyor göstergesi (spinner), kartları render eder.
  - `debouncedMemberUserSearch()`: 250ms gecikmeli anlık arama yapar.
  - `selectMemberUser(id, name, email)`: Kullanıcıyı seçer ve gizli alana yazar.
- Responsive Tasarım: `rules1.md` Madde 2 ve Madde 3 uyarınca mobilde ve masaüstünde ekrana tam oturur.

---

## 3. DOĞRULAMA ADIMLARI
1. Yerel ortamda `http://localhost:8088/admin/kurullar` sayfasına girilerek "+ Üye" ve "+ Başkan Ata" butonlarına basılacak.
2. Arama kutusuna harfler yazılarak anlık arama ve sayfalama test edilecek.
3. Kullanıcı seçilip form submit edilecek ve üyenin kurula başarıyla atandığı doğrulanacak.
4. Kod standartları ve `rules1.md` kontrol edilecek.
