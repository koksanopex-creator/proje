# MATRİS TABLOLARINI EKRANA OTURTMA VE YATAY TAŞMA (OVERFLOW) DÜZELTME PLANI

## 1. AMAÇ VE GEREKSİNİM ANALİZİ
Kullanıcının ekran görüntüsünde belirttiği ve ilettiği üzere:
- `admin/bildirim-matrisi` (Dinamik Bildirim Matrisi)
- `admin/yetki-matrisi` (Dinamik Yetki & Buton Matrisi)
- `admin/denetim-loglari#tab-visibility` (Rol Bazlı Log Görünürlük Matrisi)

sayfalarında tablolar 14+ rol sütunu ve sabit tanım kolonları nedeniyle ~1800px - 2100px genişliğe ulaşmaktadır.
Ancak masaüstü görünümde `.matrix-scroll-wrapper`, `.matrix-group-card` ve ilgili `.table-responsive` sınıflarında `overflow: visible !important;` tanımlı olduğundan tablo kart dışına taşmakta, sağ kenardaki sütunlar ekranın dışına taşarak `.app-main` (`overflow-x: hidden`) tarafından kırpılmakta ve kullanıcı tarafından erişilemez hale gelmektedir.

`rules1.md` Madde 3 ("Ana container'larda scroll/overflow kullanmak YASAK, tasarım her zaman ekrana sığmalı") ve Madde 2 (%100 responsive) uyarınca:
1. Kartlar ve sarmalayıcılar sayfanın/ekranın tam genişliğine (%100) oturacak, dış çerçeve dışına ASLA taşma yapmayacaktır.
2. Tablo sarmalayıcıları (`.matrix-scroll-wrapper` ve `.table-responsive`), kendi içinde pürüzsüz yatay kaydırma (`overflow-x: auto !important;`) ile sınırlandırılacaktır.
3. Sol taraftaki olay/izin/log kategorisi sütunları yatay kaydırma esnasında sabit (sticky) kalarak kullanıcının hangi satıra baktığını kaybetmemesi sağlanacaktır.
4. Tablo başlıkları (`thead th`) dikey kaydırmada sticky özelliğini koruyacaktır.
5. Değişiklikler hem yerel ortamda hem de canlı sunucuda (`172.20.5.10`) senkronize olarak uygulanıp doğrulanacaktır.

---

## 2. ETKİLENEN DOSYALAR VE YAPILACAK DEĞİŞİKLİKLER

### A. `public/css/koksan_pms.css`
- `.matrix-group-card`: `overflow: visible !important;` kaldırılarak `overflow: hidden !important;` ve `border-radius: var(--radius-md);` ile kart sınırları güvenceye alınacak.
- `.matrix-scroll-wrapper`:
  - `overflow: visible !important;` kaldırılacak.
  - Yerine:
    ```css
    overflow-x: auto !important;
    overflow-y: visible !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
    -webkit-overflow-scrolling: touch !important;
    ```
- `#tab-visibility .card` ve `#tab-visibility .table-responsive`:
  - `overflow: visible !important;` kaldırılacak.
  - Kart `overflow: hidden !important;`, tablo sarmalayıcısı `overflow-x: auto !important;` yapılacak.
  - Log kategorisi sütunu (`tbody td:first-child`, `thead th:first-child`) için `position: sticky; left: 0;` ve sağ kenar ayrım gölgesi eklenecek.

### B. `resources/views/admin/notification_matrix.blade.php`
- `@media (max-width: 991.98px)` altındaki kısıtlayıcı kural kaldırılarak `.matrix-scroll-wrapper` masaüstü dahil tüm ekran boyutlarında `overflow-x: auto !important;` olarak çalışacak.
- Sütun 1 (`.sticky-col-event`): `width: 220px; left: 0; position: sticky; z-index: 22; background: #ffffff;`
- Sütun 2 (`.sticky-col-template`): `width: 240px; left: 220px; position: sticky; z-index: 21; background: #ffffff; box-shadow: 4px 0 8px -2px rgba(15,23,42,0.08);`
- `body.hide-template-col` durumunda Sütun 1 sağ gölge ve border kazanacak.
- İnce ve şık kaydırma çubuğu (scrollbar) eklenecek.

### C. `resources/views/admin/permission_matrix.blade.php`
- `.matrix-scroll-wrapper` için masaüstünde de geçerli `overflow-x: auto !important;` ve `%100` genişlik kuralı eklenecek.
- Sütun 1 (`.sticky-col-perm`): `width: 290px; left: 0; position: sticky; z-index: 22; background: #ffffff;`
- Sütun 2 (`.sticky-col-slug`): `width: 220px; left: 290px; position: sticky; z-index: 21; background: #ffffff; box-shadow: 4px 0 8px -2px rgba(15,23,42,0.08);`
- `body.hide-slug-col` durumunda Sütun 1 sağ gölge ve border kazanacak.

### D. `resources/views/admin/audit_logs.blade.php`
- `#tab-visibility` içerisindeki inline `overflow: visible !important;` tanımları temizlenecek.
- Kart gövdesi ve tablo kapsayıcısı `overflow-x: auto !important;` ile sarmalanacak.
- Log kategorisi sütununa `position: sticky; left: 0;` ve sağ gölge eklenecek.

---

## 3. DOĞRULAMA VE TEST ADIMLARI
1. Local ortamda (`http://localhost:8088/admin/bildirim-matrisi`, `/admin/yetki-matrisi`, `/admin/denetim-loglari#tab-visibility`) sayfa genişliği, yatay scroll ve sticky kolon davranışı kontrol edilecek.
2. Git commit ve push yapılarak sunucuya (`172.20.5.10`) `git pull` ile aktarılacak.
3. Sunucuda `php artisan optimize:clear` çalıştırılacak.
4. Canlı URL'lerde (`https://kys.koksan.com/proje/admin/bildirim-matrisi`, vb.) taşmanın giderildiği ve tam sayfaya oturduğu doğrulanacak.
