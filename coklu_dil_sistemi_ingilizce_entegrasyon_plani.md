# Köksan PMS — Çoklu Dil (İngilizce / Türkçe) Entegrasyon & Mimari Uygulama Planı

Bu belge, Köksan Proje Yönetim Sistemi (PMS) bünyesinde **Türkçe ve İngilizce** çift dil desteğinin eksiksiz, performanslı ve oturumlar arası kalıcı olarak entegre edilmesi için hazırlanan teknik uygulama planıdır.

> [!NOTE]
> Bu belge **yalnızca bir uygulama planıdır**. Kullanıcı onayı alınana kadar projede herhangi bir kod değişikliği veya migration çalıştırılmayacaktır.

---

## 1. Mimari Genel Bakış ve Kalıcılık Motoru (Persistence Engine)

Kullanıcının seçtiği dilin **tüm cihaz ve oturumlarda kalıcı kalabilmesi**, çıkış yapıp tekrar girdiğinde veya farklı bir tarayıcıdan bağlandığında dahi sıfırlanmaması için 3 katmanlı hibrit bir kalıcılık mimarisi kullanılacaktır:

```mermaid
flowchart TD
    Req[Gelen HTTP İsteği] --> Middleware[SetLocale Middleware]
    Middleware --> AuthCheck{Kullanıcı Giriş Yapmış mı?}
    AuthCheck -- Evet --> CheckDB[1. users.locale Tercihi]
    AuthCheck -- Hayır --> CheckSession[2. Session: session('locale')]
    CheckSession --> CheckCookie[3. Kalıcı Çerez: cookie('pms_locale')]
    CheckCookie --> CheckConfig[4. Varsayılan Fallback: config('app.locale', 'tr')]
    CheckDB --> ApplyLocale[App::setLocale / Carbon::setLocale]
    CheckSession --> ApplyLocale
    CheckCookie --> ApplyLocale
    CheckConfig --> ApplyLocale
    ApplyLocale --> Render[Blade / JSON Yanıtı Üret]
```

### 1.1. Katmanlı Çözümleme Sırası
1. **Veritabanı Katmanı (`users.locale`):** 
   - `users` tablosuna `VARCHAR(5) DEFAULT 'tr'` sütunu eklenir (`tr` veya `en`).
   - Kullanıcı oturum açtığında, profilindeki kayıtlı dil doğrudan devreye girer. Başka bilgisayardan veya mobilden bağlansa dahi dil tercihi korunur.
2. **Oturum Katmanı (`session('locale')`):** 
   - Kullanıcı dili değiştirdiği milisaniyede veritabanı yazılırken anlık oturum belleği de güncellenir.
3. **Kalıcı Çerez Katmanı (`cookie('pms_locale', 1 yıl)`):** 
   - Kullanıcı oturumu kapansa (logout) veya oturum süresi dolsa dahi, giriş ekranı (login) ve misafir sayfalarında kullanıcının daha önce seçtiği dil hatırlanır.
4. **Sistem Varsayılanı (Fallback):** 
   - Hiçbir tercih yoksa `.env` dosyasındaki `APP_LOCALE=tr` geçerli olur.

---

## 2. Üst Bar (Topbar) Dil Seçici Arayüz Tasarımı (UI/UX)

Sistemin üst barında (sağ üst bölüm; profil menüsü ve vekalet/bildirim alanının hemen yanında) modern, kurumsal ve akıcı bir dil seçici bileşeni konumlandırılacaktır:

### 2.1. Tasarım Seçenekleri
* **Seçenek A (Açılır Menü / Dropdown - Önerilen):**
  * Görünüm: `[ 🇹🇷 TR ▾ ]`
  * Açıldığında şık bir kart içinde:
    * `🇹🇷 Türkçe (TR)` — Aktifse yeşil tik ikonu
    * `🇬🇧 English (EN)`
* **Seçenek B (İkili Hap Buton / Segmented Toggle):**
  * Görünüm: `[ TR | EN ]` tek tıkla doğrudan geçiş.

### 2.2. Etkileşim Akışı
1. Kullanıcı üst bardan dili seçer.
2. Sayfayı terk etmeden `POST /locale/switch` rotasına CSRF ile istek atılır (veya AJAX ile anlık geçiş).
3. Controller:
   - Giriş yapılmışsa `User::where('id', auth()->id())->update(['locale' => $lang]);`
   - `session(['locale' => $lang]);`
   - `Cookie::queue(cookie()->forever('pms_locale', $lang));`
4. Sayfa anında seçilen dille yenilenir.

---

## 3. Çeviri Sözlüğü Stratejisi (Translation Strategy)

Laravel 12 altyapısına en uygun ve bakım maliyeti en düşük yöntem **Hibrit JSON + PHP Sözlük** yapısıdır:

### 3.1. JSON Sözlüğü (`lang/en.json` & `lang/tr.json`)
Blade arayüzlerindeki metinler için anahtar olarak cümlenin kendisi kullanılır:
* **Blade Kullanımı:**
  ```blade
  {{ __('Proje Liderini Değiştir') }}
  {{ __('Görev & Rol Devret') }}
  {{ __('Proje İçi Ekip Yetki Matrisi & Görev Devir Merkezi') }}
  ```
* **`lang/en.json` İçeriği:**
  ```json
  {
    "Proje Liderini Değiştir": "Change Project Manager",
    "Görev & Rol Devret": "Delegate Task & Role",
    "Proje İçi Ekip Yetki Matrisi & Görev Devir Merkezi": "Project Team Permission Matrix & Task Handover Center",
    "Aktif Üye": "Active Member",
    "Tam Yetkili": "Fully Authorized",
    "Görev Yönetimi": "Task Management",
    "Toplantı Planlama": "Meeting Planning",
    "BOM & Bütçe": "BOM & Budget",
    "Faz Raporu": "Phase Report",
    "Dokümanlar": "Documents",
    "İşlemler": "Actions"
  }
  ```
* **Avantajı:** Blade şablonlarında kodun okunabilirliği bozulmaz. İngilizce karşılığı henüz eklenmemiş bir metin olursa sistem patlamaz; otomatik olarak Türkçe metni gösterir (Graceful Fallback).

### 3.2. Modüler PHP Sözlükleri (`lang/tr/` ve `lang/en/`)
Sistem durumları, sabit enums ve hata mesajları için yapılandırılmış diziler kullanılır:
* `lang/en/status.php` & `lang/tr/status.php`:
  * `draft` => `Draft` / `Taslak`
  * `pending_approval` => `Pending Approval` / `Onay Bekliyor`
  * `active` => `In Progress` / `Devam Ediyor`
  * `completed` => `Completed` / `Tamamlandı`
  * `on_hold` => `On Hold` / `Donduruldu`
  * `cancelled` => `Cancelled` / `İptal Edildi`
* `lang/en/validation.php`: Form hata mesajlarının İngilizce karşılıkları.

---

## 4. Frontend & JavaScript Çeviri Köprüsü (JS i18n Bridge)

Sayfalardaki JavaScript fonksiyonlarında yer alan modal uyarıları, SweetAlert bildirimleri ve hata mesajları (`alert()`, `confirm()`) için dinamik köprü kurulacaktır:

```html
<!-- layouts/app.blade.php Head Bloğu -->
<script>
    window.PMS_LOCALE = "{{ app()->getLocale() }}";
    window.PMS_TRANSLATIONS = @json(__('js')); // veya dinamik json
    window.__ = function(text, replacements = {}) {
        let translated = (window.PMS_TRANSLATIONS && window.PMS_TRANSLATIONS[text]) ? window.PMS_TRANSLATIONS[text] : text;
        for (const [key, val] of Object.entries(replacements)) {
            translated = translated.replace(':' + key, val);
        }
        return translated;
    };
</script>
```

Örnek Kullanım:
```javascript
// Eski: alert('Sunucu iletişim hatası oluştu!');
// Yeni:
alert(__('Sunucu iletişim hatası oluştu!'));
```

---

## 5. Tarih, Saat ve Para Birimi Formatlama

* **Carbon Tarihleri:**
  * `app/Http/Middleware/SetLocale.php` içerisinde:
    ```php
    \Carbon\Carbon::setLocale($locale);
    setlocale(LC_TIME, $locale === 'en' ? 'en_US.UTF-8' : 'tr_TR.UTF-8');
    ```
  * Blade tarafında: `{{ $project->created_at->translatedFormat('d M Y') }}` (TR: `19 Eki 2026`, EN: `19 Oct 2026`).
* **Sayı ve Para Birimi:**
  * TR: `1.250.000,00 TRY`
  * EN: `1,250,000.00 TRY` (Formatlama fonksiyonu dile göre ondalık/binlik ayracı belirler).

---

## 6. Aşamalı Uygulama Yol Haritası (Adım Adım)

Bu geçiş sistemi bozmadan, güvenli ve 6 aşamada gerçekleştirilecektir:

| Faz | Kapsam | Detaylar | Risk Düzeyi |
| :--- | :--- | :--- | :--- |
| **Faz 1: Altyapı & Kalıcılık Motoru** | Migration & Middleware | • `users` tablosuna `locale` sütunu migration'ı.<br>• `SetLocale` Middleware oluşturma & `bootstrap/app.php` kaydı.<br>• `LocaleController@switch` rotası & kalıcı cookie entegrasyonu. | Düşük (Sıfır kesinti) |
| **Faz 2: Üst Bar Dil Seçici UI** | Topbar Entegrasyonu | • `resources/views/layouts/app.blade.php` sağ üst menüye şık TR/EN seçici eklenmesi.<br>• Mobil ve masaüstü uyumluluğu. | Düşük |
| **Faz 3: Ana Şablonlar & Navigasyon** | Layout & Ortak Bileşenler | • Sol Yan Menü (Sidebar) menü başlıkları.<br>• Üst bar arama çubuğu ve profil menüsü.<br>• Bildirimler paneli ve vekalet modalları.<br>• `lang/en.json` & `lang/tr.json` oluşturulması. | Düşük |
| **Faz 4: Projeler & Yetki Matrisi Modülü** | Proje Görünümleri | • `projects/index.blade.php` ve `projects/show.blade.php`.<br>• Yetki Matrisi, Görev Devir Sihirbazı, Lider Değiştirme Modalı.<br>• Proje oluşturma & düzenleme sihirbazları. | Orta |
| **Faz 5: Yan Modüller & Çizelgeler** | BOM, Kanban, Gantt, Toplantılar | • Görev ve İş Akışı modülü, Kanban kartları, Gantt görünümü.<br>• Malzeme Ağacı (BOM), Toplantılar, Riskler ve Raporlar modülü. | Orta |
| **Faz 6: Yönetim & QA Doğrulama** | Admin & Son Kontroller | • Denetim Logları, Sistem Sağlığı, Kullanıcı & Rol yönetimi.<br>• Tüm sistem genelinde eksik çeviri taraması (otomasyon scripti). | Düşük |

---

## 7. Örnek Karşılaştırma Tablosu (Sistem Terminolojisi)

| Türkçe Arayüz | İngilizce Karşılığı (EN) | Kullanım Yeri |
| :--- | :--- | :--- |
| **Proje Liderini Değiştir** | Change Project Manager | Proje Detay Yetki Matrisi |
| **Görev & Rol Devret** | Delegate Task & Role | Yetki Matrisi & Devir Sihirbazı |
| **Vekaletlerim** | My Delegations | Üst Bar Hızlı İşlemler |
| **Çıkar Çatışması Kalkanı** | Conflict of Interest Shield | DOA & Onay Bilgilendirmeleri |
| **Canlı Yetkilendirme** | Live Authorization | Matris Kılavuzu |
| **Faz Raporu** | Phase Report | Proje Süreç Çizelgesi |
| **BOM & Bütçe** | BOM & Budget | Maliyet & Malzeme Ağacı |
| **Denetim Logları** | Audit Logs | Admin Paneli |
| **Sistem Sağlığı** | System Health | Admin Paneli |

---

## 8. Onay ve Başlama Kriteri

> [!IMPORTANT]
> Kullanıcı talimatına istinaden **bu plan şu anda yalnızca bir taslak/mimari plan olarak kaydedilmiştir ve sisteme hiçbir kod müdahalesi yapılmamıştır**.
> Uygulamaya geçmek istediğinizde, belirteceğiniz fazdan (örneğin önce *Faz 1 Altyapı ve Üst Bar Seçici*, ardından *Modül Modül Çeviri*) kontrollü şekilde başlanabilir.
