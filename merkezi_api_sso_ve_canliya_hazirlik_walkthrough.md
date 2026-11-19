# Köksan PMS — Merkezi API SSO & Canlıya Hazırlık Özeti (Walkthrough)

Bu dokümanda **Köksan PMS** projesinin **Merkezi API** ile bağlandığı SSO altyapısı, lokal ve canlı ortam ayrımı, yapılan değişiklikler ve sunucuya geçiş adımları özetlenmiştir.

---

## 1. Tamamlanan Altyapı Adımları

### 1.1. Merkezi API Tarafı
- **Yeni Dosya:** [2026_11_19_000001_register_koksan_pms_application.php](file:///C:/Users/celal.karaman/Desktop/Merkezi_API/database/migrations/2026_11_19_000001_register_koksan_pms_application.php)
  - `applications` tablosuna `koksan-pms` (Köksan PPM) uygulaması başarıyla kaydedildi.
  - Uygulama Kodu: `koksan-pms`
  - Yerel Port: `http://localhost:8088`
  - Canlı URL: `https://kys.koksan.com/proje` (sunucu kurulumuna göre dinamik)
  - API Anahtarı ve ikon tanımlandı.
  - Kullanıcı `CELAL KARAMAN` (ID: 172) uygulamaya onaylı olarak atandı.

### 1.2. Köksan PMS Tarafı
- **Veritabanı Migrasyonu:** [2026_11_19_000001_add_central_user_id_to_users_table.php](file:///C:/Users/celal.karaman/Desktop/Projelerim/koksan_pms/database/migrations/2026_11_19_000001_add_central_user_id_to_users_table.php)
  - `users` tablosuna `central_user_id` kolonu eklendi.
- **SSO Controller:** [SsoController.php](file:///C:/Users/celal.karaman/Desktop/Projelerim/koksan_pms/app/Http/Controllers/Auth/SsoController.php)
  - `redirectToCentral()`: Kullanıcıyı Merkezi API'ye yönlendirir.
  - `login()`: Gelen token'ı Merkezi API üzerinden doğrular (`/api/auth/verify-sso-token`), kullanıcıyı `UserSyncService` ile eşitler veya oluşturur, oturum açar.
  - `logout()`: Yerel oturumu kapatıp Merkezi API `/global-logout` kapısına yönlendirir.
- **Yapılandırma:**
  - [.env](file:///C:/Users/celal.karaman/Desktop/Projelerim/koksan_pms/.env) ve [services.php](file:///C:/Users/celal.karaman/Desktop/Projelerim/koksan_pms/config/services.php) dosyalarına `CENTRAL_SSO_URL`, `CENTRAL_SSO_APP_CODE` ve `CENTRAL_SSO_API_KEY` eklendi.
- **Ortam Güvenliği & İzolasyon:**
  - [app.blade.php](file:///C:/Users/celal.karaman/Desktop/Projelerim/koksan_pms/resources/views/layouts/app.blade.php): "🎭 Hızlı Rol Testi" widget'ı yalnızca `local`, `development` ve `testing` ortamlarında gösterilecek şekilde kapsüllendi. Canlıda (`production`) HTML çıktısında yer almaz.
  - [AuthController.php](file:///C:/Users/celal.karaman/Desktop/Projelerim/koksan_pms/app/Http/Controllers/AuthController.php): `switchUser` metodu canlı ortamda `403 Forbidden` dönecek şekilde kilitlendi. Canlıda `/login` sayfasına gelen kullanıcı doğrudan Merkezi API SSO kapısına yönlendirilir.
  - [login.blade.php](file:///C:/Users/celal.karaman/Desktop/Projelerim/koksan_pms/resources/views/auth/login.blade.php): "🏢 Köksan Merkezi Giriş (SSO)" butonu eklendi.

---

## 2. Doğrulama ve Test Sonuçları

1. **SSO Yönlendirme Testi:**
   - `http://127.0.0.1:8088/sso/redirect` -> `Location: http://127.0.0.1:8001/sso-entry?app_code=koksan-pms` (HTTP 302 Başarılı)
2. **SSO Token Doğrulama & Giriş Testi:**
   - Üretilen test token'ı ile `http://127.0.0.1:8088/sso/login?token=...` çağrıldı.
   - Merkezi API'den kullanıcı verisi çekildi, `central_user_id: 172`, sicil, telefon ve avatar güncellendi.
   - `Location: http://127.0.0.1:8088/dashboard` rotasına yönlendirilerek oturum başarıyla açıldı.
   - Denetim İzi (Audit Log) başarıyla kaydedildi.

---

## 3. GitHub & Sunucu Dağıtım Yol Haritası

### 3.1. GitHub Gönderimi (Local -> Remote)
Hedef Repo: `https://github.com/koksanopex-creator/proje`

```bash
# 1. Mevcut remote kontrol edilir ve güncellenir:
git remote set-url origin https://github.com/koksanopex-creator/proje.git
# veya eklenir:
git remote add origin https://github.com/koksanopex-creator/proje.git

# 2. Değişiklikler incelenip commit ve push yapılır (Kullanıcı talimat verdiğinde):
git add .
git commit -m "feat: Merkezi API SSO entegrasyonu, ortam izolasyonu ve kapsam modülü"
git push -u origin main
```

### 3.2. Sunucu Kurulum Adımları (Ubuntu 24.04 / Nginx)

1. **Dizin ve Kod Çekimi:**
   ```bash
   cd /var/www
   git clone https://github.com/koksanopex-creator/proje.git koksan_pms
   cd koksan_pms
   composer install --no-dev --optimize-autoloader
   ```
2. **Ortam Değişkenleri (`.env`):**
   ```env
   APP_NAME="Köksan PPM"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://kys.koksan.com/proje

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_DATABASE=koksan_pms
   DB_USERNAME=root
   DB_PASSWORD=...

   CENTRAL_SSO_URL=https://kys.koksan.com/merkezi_yonetim_sistemi
   CENTRAL_SSO_APP_CODE=koksan-pms
   ```
3. **Nginx Yapılandırması (Örnek Subpath `/proje`):**
   ```nginx
   location ^~ /proje {
       alias /var/www/koksan_pms/public;
       try_files $uri $uri/ @proje;

       location ~ \.php$ {
           include snippets/fastcgi-php.conf;
           fastcgi_param SCRIPT_FILENAME $request_filename;
           fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
       }
   }

   location @proje {
       rewrite /proje/(.*)$ /proje/index.php?/$1 last;
   }
   ```
4. **İzinler ve Storage Linki:**
   ```bash
   php artisan storage:link
   php artisan migrate --force
   chown -R www-data:www-data /var/www/koksan_pms/storage /var/www/koksan_pms/bootstrap/cache
   chmod -R 775 /var/www/koksan_pms/storage /var/www/koksan_pms/bootstrap/cache
   ```
