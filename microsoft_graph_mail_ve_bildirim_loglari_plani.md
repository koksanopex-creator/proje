# MİCROSOFT GRAPH E-POSTA ENTEGRASYONU VE BİLDİRİM/MAİL LOGLARI PLANI

## 1. DURUM TESPİTİ VE GEREKSİNİM ANALİZİ
Kullanıcının ilettiği sorular ve talepler:
1. *"Şu an sistem mail gönderiyor mu ? log da mı ayarlı. microsoft graph kullanıyorduk kys@koksan.com dan mail gönderiyorduk hatırlıyormusun? onun gibi çalışması lazım."*
   - **Mevcut Durum**: Projede `MAIL_MAILER=log` ayarlıdır. Microsoft Graph mail sürücüsü henüz projeye entegre edilmemiştir. Bu sebeple giden tüm postalar gerçek e-posta olarak iletilmeyip sadece `laravel.log` dosyasına yazılmaktadır.
   - **Gereksinim**: Sunucuda IAA ve Merkezi API projelerinde çalışan Azure AD App (`f4aaaaea-18a2-4b88-8f36-8b2e81344c7a`) ve `kys@koksan.com` Microsoft Graph API mail gönderim transportunun `koksan_pms` projesine eklenmesi, hem lokal hem de sunucu `.env` ayarlarının yapılandırılması gerekmektedir.
2. *"Bize birde mail bildirim log sayfası lazım. zil ve maillerin loglarını tutmam alzım kime hangi olaydan ne mail gitmiş içeriği nedir onları göreceğim bri mail log sayfası hazılaman lazım lokalede sunucuyada"*
   - **Gereksinim**: Hem Zil (panel içi) hem de E-Posta kanallarından kime, hangi olaydan, hangi başlık ve gövdeyle, ne zaman bildirim gittiğini eksiksiz kaydeden bir `notification_logs` veritabanı tablosu, otomatik loglama servisi ve `admin/bildirim-loglari` yönetim ekranı hazırlanmalıdır.
   - Ekranda arama, kanal/durum/olay filtreleri, sayfalama, gönderilen e-postanın tam HTML gövdesini açan önizleme modalı ve Microsoft Graph bağlantısını canlı test etmek için "Test E-Postası Gönder" butonu yer almalıdır.

---

## 2. ADIM ADIM UYGULAMA PLANI

### A. Microsoft Graph Mail Sürücüsü Entegrasyonu
1. `app/Mail/Transports/MicrosoftGraphTransport.php` sınıfının oluşturulması (Azure AD OAuth2 token alma ve Graph API `/users/kys@koksan.com/sendMail` çağrısı).
2. `config/services.php` içerisine `microsoft` servis anahtarlarının (`tenant_id`, `client_id`, `client_secret`, `from_address`) eklenmesi.
3. `config/mail.php` içine `microsoft-graph` mailer tanımının eklenmesi.
4. `app/Providers/AppServiceProvider.php` içinde `Mail::extend('microsoft-graph', ...)` ile sürücünün tescil edilmesi.
5. `.env` dosyalarının güncellenmesi (Lokal ve Sunucu):
   - `MAIL_MAILER=microsoft-graph`
   - `MICROSOFT_TENANT_ID=f4aaaaea-18a2-4b88-8f36-8b2e81344c7a`
   - `MICROSOFT_CLIENT_ID=899261b3-cb55-4e7d-9e0d-2bbeecc2b924`
   - `MICROSOFT_CLIENT_SECRET=***_AZURE_SECRET_KEY_***`
   - `MICROSOFT_FROM_ADDRESS=kys@koksan.com`
   - `MAIL_FROM_ADDRESS=kys@koksan.com`
   - `MAIL_FROM_NAME="Köksan Proje Yönetim Sistemi"`

### B. Bildirim & Mail Loglama Veritabanı ve Modeli
1. Migration: `2026_11_19_000004_create_notification_logs_table.php`
   - Alanlar: `channel` (mail/bell), `event_key`, `event_title`, `user_id`, `recipient_name`, `recipient_email`, `subject`, `body` (longText), `action_url`, `status` (sent/failed), `error_message`, `sent_by_user_id`, timestamps.
2. Model: `app/Models/NotificationLog.php`
   - İlişkiler: `user()`, `sentBy()`.
   - Yardımcı metodlar ve scope'lar.

### C. `NotificationMatrixService` Loglama Entegrasyonu
1. `dispatch()`:
   - Zil bildirimlerinde `NotificationLog::create(['channel' => 'bell', ...])`.
   - E-posta bildirimlerinde başarılı gönderimde `'sent'`, hata durumunda `'failed'` ve `$e->getMessage()` ile kayıt.
2. `dispatchMeetingStatus()`, `dispatchMeetingInvite()`, `dispatchExternalDraftShare()` fonksiyonlarına otomatik log kaydı eklenmesi.

### D. Yönetim Ekranı ve Canlı Test Paneli
1. Controller: `app/Http/Controllers/Admin/NotificationLogController.php`
   - `index()`: Filtreleme (kanal, durum, olay, arama, tarih), KPI istatistikleri ve sayfalama.
   - `show($id)`: Modal için log içeriği ve tam HTML/metin detayı.
   - `sendTestMail()`: Anlık Microsoft Graph test maili gönderimi.
   - `resend($id)`: Hatalı maili yeniden gönderme.
2. Rota:
   - `GET /admin/bildirim-loglari` -> `admin.notification_logs.index`
   - `GET /admin/bildirim-loglari/{id}` -> `admin.notification_logs.show`
   - `POST /admin/bildirim-loglari/test-mail` -> `admin.notification_logs.test_mail`
   - `POST /admin/bildirim-loglari/{id}/resend` -> `admin.notification_logs.resend`
3. View: `resources/views/admin/notification_logs/index.blade.php`
   - KPI Kartları (Toplam, Mail, Zil, Hatalı, Bugün)
   - Test Maili Gönder Kutusu (Canlı Microsoft Graph testi)
   - Filtreleme ve Arama Çubuğu
   - Log Tablosu (Kanal ikonu, Alıcı avatar/adı, Olay, Konu, Durum, İncele butonu)
   - E-Posta Önizleme Modalı (Gönderilen gerçek HTML tasarımı)
4. Menü ve Sekme Entegrasyonu:
   - `layouts/app.blade.php` sidebar menüsüne "Bildirim & Mail Logları" bağlantısı.
   - `notification_matrix`, `audit_logs` ve diğer admin sayfalarındaki kardeş sekmeler çubuğuna sekme eklenmesi.

### E. Sunucu Dağıtımı ve Test
1. Yerel ortamda migration çalıştırma, test maili gönderimi ve log kontrolü.
2. GitHub'a push ve sunucuda (`172.20.5.10`) `git pull`, migration çalıştırma ve `php artisan optimize:clear`.
3. Sunucuda `celal.karaman@koksan.com` adresine canlı test maili atılarak Graph API çıktısının doğrulanması.
