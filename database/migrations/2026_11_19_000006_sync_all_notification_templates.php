<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\NotificationTemplate;
use App\Models\NotificationMatrix;
use App\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $templates = array (
  0 => 
  array (
    'event_key' => 'proje_onaya_gonderildi',
    'event_title' => 'Proje Onay Aşamasına Geldi',
    'category' => 'Proje',
    'bell_title_template' => 'Onay: {proje_adi} - {departman_adi}',
    'bell_body_template' => 'Sayın {username} ({departman_adi}), {proje_kodu} projesi {islem_yapan} tarafından {tarih} tarihinde onayınıza sunuldu.',
    'mail_subject_template' => 'Onay Bildirimi: {proje_adi}',
    'mail_body_template' => '<p>Sayın Yetkili,</p><p><strong>{proje_adi}</strong> başlıklı proje <strong>{rol}</strong> onayınıza sunulmuştur. Lütfen sistemi ziyaret ederek onaylayınız.</p>',
    'available_variables' => 
    array (
      0 => 'proje_adi',
      1 => 'rol',
      2 => 'sure',
      3 => 'kullanici_adi',
      4 => 'username',
      5 => 'departman_adi',
      6 => 'department_name',
      7 => 'unvan',
      8 => 'islem_yapan',
      9 => 'action_by',
      10 => 'tarih',
      11 => 'proje_kodu',
    ),
    'is_active' => true,
  ),
  1 => 
  array (
    'event_key' => 'isg_onayi_bekleniyor',
    'event_title' => 'Kritik Faz İçin İSG Onayı Gerekiyor',
    'category' => 'İSG',
    'bell_title_template' => 'İSG Kapı Kilidi Uyarısı: {proje_adi}',
    'bell_body_template' => '{proje_adi} projesinde {faz_adi} fazına geçiş için İSG Saha İzin Onayı bekleniyor.',
    'mail_subject_template' => 'Kritik İSG Denetçi Onayı: {proje_adi}',
    'mail_body_template' => '<p>Sayın İSG Denetçisi,</p><p><strong>{proje_adi}</strong> projesinde <strong>{faz_adi}</strong> fazı için çalışma izni belgesi yüklenmiş olup onayınız beklenmektedir.</p>',
    'available_variables' => 
    array (
      0 => 'proje_adi',
      1 => 'faz_adi',
      2 => 'kullanici',
      3 => 'kullanici_adi',
      4 => 'username',
      5 => 'departman_adi',
      6 => 'department_name',
      7 => 'unvan',
      8 => 'islem_yapan',
      9 => 'action_by',
      10 => 'tarih',
      11 => 'proje_kodu',
    ),
    'is_active' => true,
  ),
  2 => 
  array (
    'event_key' => 'yeni_proje_baslatildi',
    'event_title' => 'Yeni Proje Başlatıldı',
    'category' => 'Proje',
    'bell_title_template' => 'Yeni Proje: {proje_adi}',
    'bell_body_template' => '{olusturan} tarafından {proje_adi} projesi başlatıldı. Proje Kodu: {proje_kodu}',
    'mail_subject_template' => 'Yeni Proje Başlatıldı: {proje_adi}',
    'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{proje_adi}</strong> ({proje_kodu}) başlıklı yeni proje oluşturulmuştur.</p><p>Kategori: <strong>{kategori}</strong><br>Planlanan Bütçe: <strong>{butce}</strong><br>Oluşturan: <strong>{olusturan}</strong></p>',
    'available_variables' => 
    array (
      0 => 'proje_adi',
      1 => 'proje_kodu',
      2 => 'kategori',
      3 => 'butce',
      4 => 'olusturan',
      5 => 'kullanici_adi',
      6 => 'username',
      7 => 'departman_adi',
      8 => 'department_name',
      9 => 'unvan',
      10 => 'islem_yapan',
      11 => 'action_by',
      12 => 'tarih',
    ),
    'is_active' => true,
  ),
  3 => 
  array (
    'event_key' => 'projeye_dahil_edildi',
    'event_title' => 'Projeye Ekip Üyesi Olarak Dahil Edildi',
    'category' => 'Ekip',
    'bell_title_template' => 'Projeye Dahil Edildiniz: {proje_adi}',
    'bell_body_template' => '{proje_adi} projesine "{sorumluluk}" göreviyle ekip üyesi olarak atandınız.',
    'mail_subject_template' => 'Projeye Dahil Edildiniz: {proje_adi}',
    'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{proje_adi}</strong> projesine <strong>{sorumluluk}</strong> rolüyle dahil edildiniz. Projeye ve üzerinize atanan görevlere sistemden erişebilirsiniz.</p>',
    'available_variables' => 
    array (
      0 => 'proje_adi',
      1 => 'proje_kodu',
      2 => 'sorumluluk',
      3 => 'kullanici_adi',
      4 => 'olusturan',
      5 => 'username',
      6 => 'departman_adi',
      7 => 'department_name',
      8 => 'unvan',
      9 => 'islem_yapan',
      10 => 'action_by',
      11 => 'tarih',
    ),
    'is_active' => true,
  ),
  4 => 
  array (
    'event_key' => 'proje_sponsoru_atandi',
    'event_title' => 'Proje Sponsorluğu Görevi',
    'category' => 'Yönetim',
    'bell_title_template' => 'Proje Sponsorluğu: {proje_adi}',
    'bell_body_template' => '{proje_adi} projesine Proje Sponsoru (Üst Yönetim) olarak atandınız.',
    'mail_subject_template' => 'Proje Sponsoru Olarak Atandınız: {proje_adi}',
    'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{proje_adi}</strong> projesine <strong>Proje Sponsoru</strong> olarak atandınız. Planlanan Bütçe: <strong>{butce}</strong>.</p>',
    'available_variables' => 
    array (
      0 => 'proje_adi',
      1 => 'proje_kodu',
      2 => 'butce',
      3 => 'yonetici',
      4 => 'kullanici_adi',
      5 => 'username',
      6 => 'departman_adi',
      7 => 'department_name',
      8 => 'unvan',
      9 => 'islem_yapan',
      10 => 'action_by',
      11 => 'tarih',
    ),
    'is_active' => true,
  ),
  5 => 
  array (
    'event_key' => 'proje_yoneticisi_atandi',
    'event_title' => 'Proje Yöneticisi / Lideri Görevi',
    'category' => 'Proje',
    'bell_title_template' => 'Proje Yöneticisi Ataması: {proje_adi}',
    'bell_body_template' => '{proje_adi} projesine Proje Yöneticisi / Lideri olarak atandınız.',
    'mail_subject_template' => 'Proje Yöneticisi Olarak Atandınız: {proje_adi}',
    'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{proje_adi}</strong> projesine <strong>Proje Yöneticisi</strong> olarak atanmış bulunmaktasınız.</p>',
    'available_variables' => 
    array (
      0 => 'proje_adi',
      1 => 'proje_kodu',
      2 => 'kullanici_adi',
      3 => 'sponsor',
      4 => 'butce',
      5 => 'username',
      6 => 'departman_adi',
      7 => 'department_name',
      8 => 'unvan',
      9 => 'islem_yapan',
      10 => 'action_by',
      11 => 'tarih',
    ),
    'is_active' => true,
  ),
  6 => 
  array (
    'event_key' => 'isg_onayi_tamamlandi',
    'event_title' => 'İSG Saha Çalışma İzni Onaylandı',
    'category' => 'İSG',
    'bell_title_template' => 'İSG Onayı Verildi: {proje_adi}',
    'bell_body_template' => '{proje_adi} projesinde {faz_adi} fazı için İSG Saha İzin Onayı başarıyla verildi. Faz aktif edildi.',
    'mail_subject_template' => 'İSG Saha İzin Onayı Verildi: {proje_adi}',
    'mail_body_template' => '<p>Sayın Yetkili,</p><p><strong>{proje_adi}</strong> projesinde <strong>{faz_adi}</strong> fazı için İSG çalışma izni onaylanmıştır.</p>',
    'available_variables' => 
    array (
      0 => 'proje_adi',
      1 => 'faz_adi',
      2 => 'kullanici_adi',
      3 => 'username',
      4 => 'departman_adi',
      5 => 'department_name',
      6 => 'unvan',
      7 => 'islem_yapan',
      8 => 'action_by',
      9 => 'tarih',
      10 => 'proje_kodu',
    ),
    'is_active' => true,
  ),
  7 => 
  array (
    'event_key' => 'faz_tamamlandi',
    'event_title' => 'Proje Fazı Tamamlandı',
    'category' => 'Proje',
    'bell_title_template' => 'Faz Tamamlandı: {proje_adi}',
    'bell_body_template' => '{proje_adi} projesinde "{faz_adi}" fazı tamamlandı.',
    'mail_subject_template' => 'Faz Tamamlandı: {proje_adi}',
    'mail_body_template' => '<p>Sayın Yetkili,</p><p><strong>{proje_adi}</strong> projesinde <strong>{faz_adi}</strong> fazı tamamlanmış ve sonraki faza geçilmiştir.</p>',
    'available_variables' => 
    array (
      0 => 'proje_adi',
      1 => 'faz_adi',
      2 => 'ilerleme',
      3 => 'kullanici_adi',
      4 => 'username',
      5 => 'departman_adi',
      6 => 'department_name',
      7 => 'unvan',
      8 => 'islem_yapan',
      9 => 'action_by',
      10 => 'tarih',
      11 => 'proje_kodu',
    ),
    'is_active' => true,
  ),
  8 => 
  array (
    'event_key' => 'yeni_proje_gozlemcisi',
    'event_title' => 'Projeye Gözlemci (Read-Only) Olarak Eklendi',
    'category' => 'Proje',
    'bell_title_template' => 'Gözlemci Olarak Eklendiniz: {proje_adi}',
    'bell_body_template' => '{proje_adi} ({proje_kodu}) projesine izleyici / gözlemci olarak eklendiniz. Proje ilerlemesini salt okunur takip edebilirsiniz.',
    'mail_subject_template' => 'Proje Gözlemcisi Bildirimi: {proje_adi}',
    'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{proje_adi}</strong> ({proje_kodu}) projesine <strong>Gözlemci (Read-Only)</strong> olarak dahil edildiniz.</p><p>Projenin Gantt, Kanban, bütçe ve aşama durumlarını sistemden güncel olarak takip edebilirsiniz.</p><p>Ekleyen Yetkili: <strong>{ekleyen}</strong></p>',
    'available_variables' => 
    array (
      0 => 'proje_adi',
      1 => 'proje_kodu',
      2 => 'kullanici_adi',
      3 => 'ekleyen',
      4 => 'username',
      5 => 'departman_adi',
      6 => 'department_name',
      7 => 'unvan',
      8 => 'islem_yapan',
      9 => 'action_by',
      10 => 'tarih',
    ),
    'is_active' => true,
  ),
  9 => 
  array (
    'event_key' => 'proje_dosyasi_yuklendi',
    'event_title' => 'Projeye Yeni Dosya / Envanter Yüklendi',
    'category' => 'Belge & Envanter',
    'bell_title_template' => 'Yeni Dosya / Belge: {proje_adi}',
    'bell_body_template' => '{proje_adi} projesine "{dosya_adi}" isimli yeni bir {kategori} belgesi yüklendi. Yükleyen: {yukleyen}',
    'mail_subject_template' => 'Yeni Proje Belgesi / Envanter Yüklendi: {proje_adi}',
    'mail_body_template' => '<p>Sayın Yetkili,</p><p><strong>{proje_adi}</strong> ({proje_kodu}) projesine yeni bir belge yüklenmiştir.</p><p>Dosya Adı: <strong>{dosya_adi}</strong><br>Belge Kategorisi: <strong>{kategori}</strong><br>Yükleyen: <strong>{yukleyen}</strong></p>',
    'available_variables' => 
    array (
      0 => 'proje_adi',
      1 => 'proje_kodu',
      2 => 'dosya_adi',
      3 => 'kategori',
      4 => 'yukleyen',
      5 => 'kullanici_adi',
      6 => 'username',
      7 => 'departman_adi',
      8 => 'department_name',
      9 => 'unvan',
      10 => 'islem_yapan',
      11 => 'action_by',
      12 => 'tarih',
    ),
    'is_active' => true,
  ),
  10 => 
  array (
    'event_key' => 'proje_adimi_tamamlandi',
    'event_title' => 'Proje Adımı / Fazı Tamamlandı',
    'category' => 'Aşama & İlerleme',
    'bell_title_template' => 'Adım Tamamlandı: {adim_adi}',
    'bell_body_template' => '{proje_adi} projesinde "{adim_adi}" adımı tamamlandı olarak işaretlendi. İlerleme: %{ilerleme}',
    'mail_subject_template' => 'Proje Adımı Başarıyla Tamamlandı: {proje_adi}',
    'mail_body_template' => '<p>Sayın Yetkili,</p><p><strong>{proje_adi}</strong> projesinde <strong>{adim_adi}</strong> aşaması başarıyla tamamlanmıştır.</p><p>Güncel Genel İlerleme: <strong>%{ilerleme}</strong><br>Onaylayan / Tamamlayan: <strong>{tamamlayan}</strong></p>',
    'available_variables' => 
    array (
      0 => 'proje_adi',
      1 => 'proje_kodu',
      2 => 'adim_adi',
      3 => 'ilerleme',
      4 => 'tamamlayan',
      5 => 'kullanici_adi',
      6 => 'username',
      7 => 'departman_adi',
      8 => 'department_name',
      9 => 'unvan',
      10 => 'islem_yapan',
      11 => 'action_by',
      12 => 'tarih',
    ),
    'is_active' => true,
  ),
  11 => 
  array (
    'event_key' => 'islem_geri_alindi',
    'event_title' => 'Gerçekleştirilen İşlem Geri Alındı (Rollback)',
    'category' => 'Süreç & Güvenlik',
    'bell_title_template' => 'İşleminiz Geri Alındı: {islem_adi}',
    'bell_body_template' => '{proje_adi} projesinde "{islem_adi}" işlemi {geri_alan} tarafından adınıza geri alınmış ve durum önceki haline döndürülmüştür.',
    'mail_subject_template' => 'İşlem Geri Alma Bildirimi: {proje_adi}',
    'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{proje_adi}</strong> ({proje_kodu}) projesinde daha önce gerçekleştirilmiş olan <strong>{islem_adi}</strong> işlemi, <strong>{geri_alan}</strong> tarafından adınıza geri alınmıştır.</p><p>Proje ve aşama durumu eksiksiz olarak işlem öncesindeki orijinal haline döndürülmüştür.</p>',
    'available_variables' => 
    array (
      0 => 'proje_adi',
      1 => 'proje_kodu',
      2 => 'islem_adi',
      3 => 'geri_alan',
      4 => 'kullanici_adi',
      5 => 'username',
      6 => 'departman_adi',
      7 => 'department_name',
      8 => 'unvan',
      9 => 'islem_yapan',
      10 => 'action_by',
      11 => 'tarih',
    ),
    'is_active' => true,
  ),
  12 => 
  array (
    'event_key' => 'proje_tartisma_mesaji',
    'event_title' => 'Projeye Yeni Tartışma / Yorum Mesajı Eklendi',
    'category' => 'İletişim & Tartışma',
    'bell_title_template' => 'Yeni Proje Mesajı: {proje_kodu}',
    'bell_body_template' => '{islem_yapan}, {proje_adi} projesine yeni bir yorum bıraktı: "{mesaj}"',
    'mail_subject_template' => 'Yeni Tartışma Mesajı: {proje_adi}',
    'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{proje_adi}</strong> ({proje_kodu}) projesinde <strong>{islem_yapan}</strong> tarafından yeni bir tartışma mesajı paylaşıldı:</p><blockquote style="border-left: 3px solid #3b82f6; padding-left: 10px; color: #475569;">{mesaj}</blockquote><p>Detayları incelemek ve yanıtlamak için projeyi ziyaret edebilirsiniz.</p>',
    'available_variables' => 
    array (
      0 => 'proje_adi',
      1 => 'proje_kodu',
      2 => 'islem_yapan',
      3 => 'action_by',
      4 => 'mesaj',
      5 => 'kullanici_adi',
      6 => 'username',
      7 => 'departman_adi',
      8 => 'department_name',
      9 => 'tarih',
    ),
    'is_active' => true,
  ),
  13 => 
  array (
    'event_key' => 'gorev_sorumlu_atandi',
    'event_title' => 'Görev Sorumlusu Olarak Atandı',
    'category' => 'Görev',
    'bell_title_template' => 'Yeni Görev: {gorev_adi}',
    'bell_body_template' => '<strong>{proje_adi}</strong> ({proje_kodu}) projesinde "<strong>{faz_adi}</strong>" aşamasında "<strong>{gorev_adi}</strong>" görevine sorumlu olarak atandınız. Öncelik: <strong>{oncelik}</strong>, Termin: <strong>{termin}</strong>',
    'mail_subject_template' => 'Yeni Görev Ataması: {gorev_adi} ({proje_kodu})',
    'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{proje_adi} ({proje_kodu})</strong> projesinde <strong>"{faz_adi}"</strong> fazı altındaki <strong>"{gorev_adi}"</strong> görevine sorumlu personel olarak atandınız.</p><ul><li><strong>Görev Başlığı:</strong> {gorev_adi}</li><li><strong>Proje:</strong> {proje_adi} ({proje_kodu})</li><li><strong>Faz / Aşama:</strong> {faz_adi}</li><li><strong>Öncelik:</strong> {oncelik}</li><li><strong>Hedef Bitiş Tarihi:</strong> {termin}</li><li><strong>Tahmini Efor:</strong> {tahmini_efor} saat</li><li><strong>Görevi Veren:</strong> {olusturan}</li></ul><p>Görevin detaylarını incelemek ve durumunu güncellemek için aşağıdaki bağlantıyı kullanabilirsiniz.</p>',
    'available_variables' => 
    array (
      0 => 'proje_adi',
      1 => 'proje_kodu',
      2 => 'faz_adi',
      3 => 'gorev_adi',
      4 => 'oncelik',
      5 => 'termin',
      6 => 'tahmini_efor',
      7 => 'olusturan',
      8 => 'kullanici_adi',
      9 => 'gorev_aciklamasi',
    ),
    'is_active' => true,
  ),
  14 => 
  array (
    'event_key' => 'projeden_cikarildi',
    'event_title' => 'Projeden Ekip Üyeliği Kaldırıldı',
    'category' => 'Ekip',
    'bell_title_template' => 'Ekip Güncellemesi: {proje_adi}',
    'bell_body_template' => 'Sayın {username}, {proje_kodu} projesindeki ekip üyeliğiniz {islem_yapan} tarafından {tarih} tarihinde sonlandırıldı.',
    'mail_subject_template' => 'Proje Ekip Bilgilendirmesi: {proje_adi}',
    'mail_body_template' => '<p>Sayın {username},</p><p><strong>{proje_kodu} - {proje_adi}</strong> projesindeki ekip üyeliğiniz güncellenmiş ve projeden ayrılışınız tamamlanmıştır.</p>',
    'available_variables' => 
    array (
      0 => 'proje_adi',
      1 => 'proje_kodu',
      2 => 'username',
      3 => 'islem_yapan',
      4 => 'tarih',
      5 => 'departman_adi',
    ),
    'is_active' => true,
  ),
  15 => 
  array (
    'event_key' => 'proje_gozlemcisi_cikarildi',
    'event_title' => 'Proje Gözlemciliğinden Çıkarıldı',
    'category' => 'Proje',
    'bell_title_template' => 'Gözlemci Güncellemesi: {proje_adi}',
    'bell_body_template' => 'Sayın {username}, {proje_kodu} projesi gözlemci takip listenizden çıkarıldı.',
    'mail_subject_template' => 'Proje Takip Güncellemesi: {proje_adi}',
    'mail_body_template' => '<p>Sayın {username},</p><p><strong>{proje_kodu} - {proje_adi}</strong> projesindeki gözlemci yetkiniz sonlandırılmıştır.</p>',
    'available_variables' => 
    array (
      0 => 'proje_adi',
      1 => 'proje_kodu',
      2 => 'username',
      3 => 'islem_yapan',
      4 => 'tarih',
      5 => 'departman_adi',
    ),
    'is_active' => true,
  ),
  16 => 
  array (
    'event_key' => 'proje_gizlilik_guncellendi',
    'event_title' => 'Proje Gizlilik & Güvenlik Seviyesi Değişti',
    'category' => 'Proje',
    'bell_title_template' => 'Güvenlik/Gizlilik Uyarısı: {proje_adi}',
    'bell_body_template' => '{proje_kodu} projesinin gizlilik seviyesi {yeni_gizlilik} olarak güncellendi. İşlem Yapan: {islem_yapan}',
    'mail_subject_template' => 'Güvenlik Bildirimi: {proje_adi} Gizlilik Seviyesi Güncellendi',
    'mail_body_template' => '<p>Sayın Yetkili,</p><p><strong>{proje_kodu} - {proje_adi}</strong> projesinin gizlilik seviyesi <strong>{yeni_gizlilik}</strong> olarak değiştirilmiştir.</p>',
    'available_variables' => 
    array (
      0 => 'proje_adi',
      1 => 'proje_kodu',
      2 => 'yeni_gizlilik',
      3 => 'islem_yapan',
      4 => 'tarih',
    ),
    'is_active' => true,
  ),
  17 => 
  array (
    'event_key' => 'proje_butce_takvim_guncellendi',
    'event_title' => 'Proje Bütçe, Takvim veya Kapsam Güncellendi',
    'category' => 'Proje',
    'bell_title_template' => 'Bütçe & Takvim Revizyonu: {proje_adi}',
    'bell_body_template' => '{proje_kodu} projesinin bütçe/takvim parametreleri güncellendi. Yeni Bütçe: {butce}, Hedef Bitiş: {hedef_tarih}.',
    'mail_subject_template' => 'Revizyon Bildirimi: {proje_adi} Bütçe ve Takvim Güncellendi',
    'mail_body_template' => '<p>Sayın Paydaş,</p><p><strong>{proje_kodu} - {proje_adi}</strong> projesinde bütçe veya takvim revizyonu yapılmıştır.<br>Yeni Bütçe: <strong>{butce}</strong><br>Hedef Bitiş: <strong>{hedef_tarih}</strong><br>İşlemi Yapan: <strong>{islem_yapan}</strong></p>',
    'available_variables' => 
    array (
      0 => 'proje_adi',
      1 => 'proje_kodu',
      2 => 'butce',
      3 => 'hedef_tarih',
      4 => 'islem_yapan',
      5 => 'tarih',
    ),
    'is_active' => true,
  ),
  18 => 
  array (
    'event_key' => 'proje_bilgileri_guncellendi',
    'event_title' => 'Proje Bilgileri Güncellendi',
    'category' => 'Proje',
    'bell_title_template' => 'Proje Güncellendi: {proje_adi}',
    'bell_body_template' => '{proje_kodu} projesinin temel bilgileri {islem_yapan} tarafından güncellendi.',
    'mail_subject_template' => 'Proje Güncelleme Bildirimi: {proje_adi}',
    'mail_body_template' => '<p>Sayın Paydaş,</p><p><strong>{proje_kodu} - {proje_adi}</strong> projesinin bilgileri güncellenmiştir.</p>',
    'available_variables' => 
    array (
      0 => 'proje_adi',
      1 => 'proje_kodu',
      2 => 'islem_yapan',
      3 => 'tarih',
    ),
    'is_active' => true,
  ),
  19 => 
  array (
    'event_key' => 'project_draft_shared',
    'event_title' => 'Proje Taslağı Paylaşıldı',
    'category' => 'Proje',
    'bell_title_template' => 'Proje Taslağı Paylaşıldı',
    'bell_body_template' => '**{sender_name}** kullanıcısı, **{project_title}** taslak projesini incelemeniz ve öneri sunmanız için sizinle paylaştı.',
    'mail_subject_template' => 'Proje Taslağı İnceleme Talebi: {project_title}',
    'mail_body_template' => 'Sayın **{recipient_name}**,<br><br>**{sender_name}** tarafından hazırlanan **{project_title}** başlıklı proje taslağı görüşlerinize ve incelemenize sunulmuştur.<br><br><strong>İnceleme Notu:</strong> {note}<br><br>Taslağı salt okunur olarak inceleyebilir ve ilgili alanlara önerilerinizi ekleyebilirsiniz.',
    'available_variables' => 
    array (
      0 => 'sender_name',
      1 => 'project_title',
      2 => 'recipient_name',
      3 => 'note',
    ),
    'is_active' => true,
  ),
  20 => 
  array (
    'event_key' => 'project_draft_suggestion_added',
    'event_title' => 'Taslak Projeye Yeni Öneri Eklendi',
    'category' => 'Proje',
    'bell_title_template' => 'Taslak Projeye Öneri Geldi',
    'bell_body_template' => '**{user_name}**, **{project_title}** taslağındaki **{field_label}** alanına yeni bir öneri ekledi.',
    'mail_subject_template' => 'Taslak Projenize Yeni Öneri: {project_title}',
    'mail_body_template' => 'Sayın **{recipient_name}**,<br><br>**{user_name}**, hazırladığınız **{project_title}** taslağının **{field_label}** alanı için şu öneride bulundu:<br><br><em>"{suggestion_text}"</em>',
    'available_variables' => 
    array (
      0 => 'user_name',
      1 => 'project_title',
      2 => 'field_label',
      3 => 'recipient_name',
      4 => 'suggestion_text',
    ),
    'is_active' => true,
  ),
  21 => 
  array (
    'event_key' => 'proje_taslak_harici_paylasim',
    'event_title' => 'Taslak Proje Harici Paydaş İnceleme Daveti',
    'category' => 'Proje & Taslak',
    'bell_title_template' => 'Harici Paylaşım: {proje_adi}',
    'bell_body_template' => '{proje_adi} taslağı {alici_eposta} harici adresiyle paylaşıldı.',
    'mail_subject_template' => 'Köksan PPM: {proje_adi} Taslak Proje İnceleme Daveti (Geçiş Kodu: {gecis_anahtari})',
    'mail_body_template' => '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff;"><div style="border-bottom: 2px solid #0284c7; padding-bottom: 12px; margin-bottom: 16px;"><h2 style="color: #0f172a; margin: 0; font-size: 18px;">Köksan Enterprise PPM</h2><span style="color: #64748b; font-size: 12px;">Taslak Proje Dış Paydaş İnceleme Portalı</span></div><p>Sayın Yetkili,</p><p><strong>{paylasan_kisi}</strong> tarafından <strong>{proje_kodu} - {proje_adi}</strong> taslak projesi incelemenize ve değerlendirmenize sunulmuştur.</p><div style="background: #f8fafc; border-left: 4px solid #0284c7; padding: 12px; margin: 16px 0; border-radius: 0 8px 8px 0;"><strong style="color: #334155;">İnceleme Talebi / Not:</strong><p style="margin: 4px 0 0 0; color: #475569;">{paylasim_notu}</p></div><div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px; margin: 16px 0;"><div style="font-size: 13px; font-weight: bold; color: #1e40af; margin-bottom: 6px;">🔑 Güvenli Geçiş Anahtarınız:</div><div style="font-size: 18px; font-weight: 800; color: #0284c7; letter-spacing: 2px; font-family: monospace;">{gecis_anahtari}</div><p style="margin: 6px 0 0 0; font-size: 11.5px; color: #64748b;">* Bu anahtar yalnızca bu taslak projeye ({proje_kodu}) özeldir.</p></div><div style="text-align: center; margin: 24px 0;"><a href="{erisim_linki}" style="display: inline-block; background: #0284c7; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: bold; font-size: 14px;">🚀 Taslak Projeyi İncele →</a></div><p style="font-size: 11.5px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 12px; margin-top: 24px;">Bu e-posta Köksan Enterprise Portföy & Proje Yönetim Sistemi (PPM) tarafından otomatik oluşturulmuştur.</p></div>',
    'available_variables' => 
    array (
      0 => 'proje_adi',
      1 => 'proje_kodu',
      2 => 'paylasan_kisi',
      3 => 'alici_eposta',
      4 => 'erisim_linki',
      5 => 'gecis_anahtari',
      6 => 'paylasim_notu',
    ),
    'is_active' => true,
  ),
  22 => 
  array (
    'event_key' => 'toplanti_baslatildi',
    'event_title' => 'Toplantı Başlatıldı',
    'category' => 'Toplantı',
    'bell_title_template' => '▶️ Toplantı Başlatıldı: {toplanti_baslik}',
    'bell_body_template' => '**{islem_yapan}** tarafından **{proje_kodu}** projesine bağlı **{toplanti_baslik}** toplantısı başlatılmıştır.',
    'mail_subject_template' => '[Toplantı Başlatıldı] {proje_kodu} - {toplanti_baslik}',
    'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{islem_yapan}</strong> tarafından <strong>{proje_kodu} - {proje_adi}</strong> projesine ait <strong>{toplanti_baslik}</strong> toplantısı başlatılmıştır.</p><p>📅 Tarih & Saat: <strong>{tarih}</strong><br>📍 Lokasyon: <strong>{lokasyon}</strong></p>{ek_not}',
    'available_variables' => 
    array (
      0 => 'toplanti_baslik',
      1 => 'proje_kodu',
      2 => 'proje_adi',
      3 => 'islem_yapan',
      4 => 'tarih',
      5 => 'lokasyon',
      6 => 'ek_not',
      7 => 'kullanici_adi',
      8 => 'departman_adi',
    ),
    'is_active' => true,
  ),
  23 => 
  array (
    'event_key' => 'toplanti_tamamlandi',
    'event_title' => 'Toplantı Tamamlandı & Tutanak Bağlandı',
    'category' => 'Toplantı',
    'bell_title_template' => '✅ Toplantı Tamamlandı: {toplanti_baslik}',
    'bell_body_template' => '**{islem_yapan}** tarafından **{proje_kodu}** projesine bağlı **{toplanti_baslik}** toplantısı tamamlanmış ve resmi tutanak bağlanmıştır.',
    'mail_subject_template' => '[Toplantı Tamamlandı & Tutanak] {proje_kodu} - {toplanti_baslik}',
    'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{islem_yapan}</strong> tarafından <strong>{proje_kodu} - {proje_adi}</strong> projesine ait <strong>{toplanti_baslik}</strong> toplantısı tamamlanmış ve resmi tutanağı sisteme kaydedilmiştir.</p><p>📅 Tarih: <strong>{tarih}</strong><br>📍 Lokasyon: <strong>{lokasyon}</strong></p>{ek_not}',
    'available_variables' => 
    array (
      0 => 'toplanti_baslik',
      1 => 'proje_kodu',
      2 => 'proje_adi',
      3 => 'islem_yapan',
      4 => 'tarih',
      5 => 'lokasyon',
      6 => 'ek_not',
      7 => 'kullanici_adi',
      8 => 'departman_adi',
    ),
    'is_active' => true,
  ),
  24 => 
  array (
    'event_key' => 'toplanti_yeniden_planlandi',
    'event_title' => 'Toplantı Yeniden Planlandı',
    'category' => 'Toplantı',
    'bell_title_template' => '⏱️ Toplantı Yeniden Planlandı: {toplanti_baslik}',
    'bell_body_template' => '**{islem_yapan}** tarafından **{proje_kodu}** projesine bağlı **{toplanti_baslik}** toplantısı yeniden planlandı durumuna alınmıştır.',
    'mail_subject_template' => '[Toplantı Yeniden Planlandı] {proje_kodu} - {toplanti_baslik}',
    'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{islem_yapan}</strong> tarafından <strong>{proje_kodu} - {proje_adi}</strong> projesine ait <strong>{toplanti_baslik}</strong> toplantısı yeniden planlandı durumuna alınmıştır.</p><p>📅 Tarih & Saat: <strong>{tarih}</strong></p>{ek_not}',
    'available_variables' => 
    array (
      0 => 'toplanti_baslik',
      1 => 'proje_kodu',
      2 => 'proje_adi',
      3 => 'islem_yapan',
      4 => 'tarih',
      5 => 'lokasyon',
      6 => 'ek_not',
      7 => 'kullanici_adi',
      8 => 'departman_adi',
    ),
    'is_active' => true,
  ),
  25 => 
  array (
    'event_key' => 'toplanti_iptal_edildi',
    'event_title' => 'Toplantı İptal Edildi',
    'category' => 'Toplantı',
    'bell_title_template' => '❌ Toplantı İptal Edildi: {toplanti_baslik}',
    'bell_body_template' => '**{islem_yapan}** tarafından **{proje_kodu}** projesine bağlı **{toplanti_baslik}** toplantısı iptal edilmiştir.',
    'mail_subject_template' => '[Toplantı İptal Edildi] {proje_kodu} - {toplanti_baslik}',
    'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{islem_yapan}</strong> tarafından <strong>{proje_kodu} - {proje_adi}</strong> projesine ait <strong>{toplanti_baslik}</strong> toplantısı iptal edilmiştir.</p>{ek_not}',
    'available_variables' => 
    array (
      0 => 'toplanti_baslik',
      1 => 'proje_kodu',
      2 => 'proje_adi',
      3 => 'islem_yapan',
      4 => 'tarih',
      5 => 'lokasyon',
      6 => 'ek_not',
      7 => 'kullanici_adi',
      8 => 'departman_adi',
    ),
    'is_active' => true,
  ),
  26 => 
  array (
    'event_key' => 'toplanti_daveti',
    'event_title' => 'Toplantı Katılımcı Daveti',
    'category' => 'Toplantı',
    'bell_title_template' => 'Toplantı Daveti: {toplanti_baslik}',
    'bell_body_template' => '**{islem_yapan}** sizi **{proje_kodu} - {proje_adi}** projesine bağlı **{toplanti_baslik}** toplantısına davet etti. 📅 Tarih: **{tarih}**',
    'mail_subject_template' => '[Toplantı Daveti] {proje_kodu} - {toplanti_baslik}',
    'mail_body_template' => '<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #1e293b; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;"><div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 20px 24px;"><h2 style="margin: 0; font-size: 18px; font-weight: 700;">📅 Toplantı Katılım Daveti</h2><p style="margin: 4px 0 0; font-size: 13px; color: #94a3b8;">Köksan Enterprise PPM Toplantı Yönetimi</p></div><div style="padding: 24px; background: #ffffff;"><p style="margin-top: 0;">Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{islem_yapan}</strong> sizi <strong>{proje_kodu} - {proje_adi}</strong> projesine ait aşağıdaki toplantıya katılımcı olarak davet etmiştir:</p><div style="background: #f8fafc; border-left: 4px solid #0284c7; padding: 14px 18px; margin: 18px 0; border-radius: 0 8px 8px 0;"><h3 style="margin: 0 0 8px 0; font-size: 15px; color: #0f172a;">{toplanti_baslik}</h3><div style="font-size: 13px; color: #475569; margin-bottom: 4px;">📅 <strong>Tarih & Saat:</strong> {tarih}</div><div style="font-size: 13px; color: #475569; margin-bottom: 4px;">📍 <strong>Lokasyon / Salon:</strong> {lokasyon}</div><div style="font-size: 13px; color: #475569;">🏢 <strong>Departman:</strong> {departman_adi}</div></div>{gundem_alani}{ek_not}<div style="margin-top: 24px; text-align: center;"><a href="{toplanti_url}" style="display: inline-block; background: #0284c7; color: #ffffff; text-decoration: none; padding: 10px 24px; border-radius: 8px; font-weight: 700; font-size: 14px;">Toplantı Detaylarını & Gündemi İncele ↗</a></div></div><div style="background: #f1f5f9; padding: 14px 24px; font-size: 12px; color: #64748b; text-align: center; border-top: 1px solid #e2e8f0;">Bu e-posta Köksan Enterprise PPM sistemi tarafından otomatik olarak oluşturulmuştur.</div></div>',
    'available_variables' => 
    array (
      0 => 'toplanti_baslik',
      1 => 'proje_kodu',
      2 => 'proje_adi',
      3 => 'islem_yapan',
      4 => 'tarih',
      5 => 'lokasyon',
      6 => 'gundem_alani',
      7 => 'ek_not',
      8 => 'kullanici_adi',
      9 => 'departman_adi',
      10 => 'toplanti_url',
    ),
    'is_active' => true,
  ),
  27 => 
  array (
    'event_key' => 'proje_kurul_oylamasina_dustu',
    'event_title' => 'Proje Kurul Oylamasına Sevk Edildi',
    'category' => 'Bütçe & Onay',
    'bell_title_template' => 'Kurul Oylaması: {proje_kodu} - {proje_adi}',
    'bell_body_template' => 'Üyesi olduğunuz **{kurul_adi}** kuruluna **{proje_kodu}** kodlu ve **{tutar}** bütçeli proje oylama için sevk edilmiştir.',
    'mail_subject_template' => '[Kurul Oylaması] {proje_kodu} - {kurul_adi} Oylama Çağrısı',
    'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p>Üyesi olduğunuz <strong>{kurul_adi}</strong> kuruluna onaylanmak üzere yeni bir proje iletilmiştir.</p><p>📁 Proje: <strong>{proje_kodu} - {proje_adi}</strong><br>💰 Planlanan Bütçe: <strong>{tutar}</strong><br>⚖️ Karar Modeli: <strong>{oylama_modeli}</strong><br>📅 Tarih: <strong>{tarih}</strong></p><p>Lütfen sisteme giriş yaparak oyunuzu kullanınız.</p>',
    'available_variables' => 
    array (
      0 => 'proje_adi',
      1 => 'proje_kodu',
      2 => 'kurul_adi',
      3 => 'tutar',
      4 => 'oylama_modeli',
      5 => 'kullanici_adi',
      6 => 'tarih',
    ),
    'is_active' => true,
  ),
  28 => 
  array (
    'event_key' => 'proje_kurul_karar_verildi',
    'event_title' => 'Kurul Kararı Kesinleşti',
    'category' => 'Bütçe & Onay',
    'bell_title_template' => 'Kurul Kararı: {proje_kodu} - {karar}',
    'bell_body_template' => '**{kurul_adi}** kurulu **{proje_kodu}** projesine ilişkin değerlendirmesini tamamlamış ve proje **{karar}** durumuna geçmiştir.',
    'mail_subject_template' => '[Kurul Kararı] {proje_kodu} - {karar}',
    'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{proje_kodu} - {proje_adi}</strong> projesi için <strong>{kurul_adi}</strong> tarafından yapılan oylama sonuçlanmıştır.</p><p>🏁 Nihai Karar: <strong>{karar}</strong><br>📝 Karar Açıklaması: <strong>{gerekce}</strong><br>📅 Tarih: <strong>{tarih}</strong></p>',
    'available_variables' => 
    array (
      0 => 'proje_adi',
      1 => 'proje_kodu',
      2 => 'kurul_adi',
      3 => 'karar',
      4 => 'gerekce',
      5 => 'kullanici_adi',
      6 => 'tarih',
    ),
    'is_active' => true,
  ),
  29 => 
  array (
    'event_key' => 'proje_doa_esik_onayi',
    'event_title' => 'Bütçe Eşik Onayı Bekleniyor',
    'category' => 'Bütçe & Onay',
    'bell_title_template' => 'Bütçe Onayı: {proje_kodu}',
    'bell_body_template' => '**{proje_kodu}** kodlu proje **{tutar}** bütçesi ile **{kural_adi}** kapsamında onayınıza sunulmuştur.',
    'mail_subject_template' => '[Bütçe Onay Talebi] {proje_kodu} - {tutar}',
    'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{proje_kodu} - {proje_adi}</strong> projesi <strong>{tutar}</strong> planlanan bütçesi ile yetki eşiğinize girmiştir.</p><p>🔍 Kural: <strong>{kural_adi}</strong><br>📅 Tarih: <strong>{tarih}</strong></p><p>Lütfen detayları inceleyerek onay/red işlemini gerçekleştiriniz.</p>',
    'available_variables' => 
    array (
      0 => 'proje_adi',
      1 => 'proje_kodu',
      2 => 'tutar',
      3 => 'kural_adi',
      4 => 'kullanici_adi',
      5 => 'tarih',
    ),
    'is_active' => true,
  ),
  30 => 
  array (
    'event_key' => 'proje_hizli_onaylandi',
    'event_title' => 'Proje Hızlı Onaylandı (Doğrudan Devreye Alma)',
    'category' => 'Bütçe & Onay',
    'bell_title_template' => 'Hızlı Onay: {proje_kodu} Devreye Alındı',
    'bell_body_template' => '**{proje_kodu}** projesi bütçesi (**{tutar}**) hızlı onay eşiğinde olduğu için doğrudan devreye alınmıştır.',
    'mail_subject_template' => '[Doğrudan Devreye Alma] {proje_kodu} Projesi Başlatıldı',
    'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{proje_kodu} - {proje_adi}</strong> projesi bütçesi (<strong>{tutar}</strong>) sistem hızlı devreye alma sınırları içerisinde yer aldığından ek onay gerekmeksizin doğrudan <strong>Devam Ediyor</strong> aşamasına geçirilmiştir.</p>',
    'available_variables' => 
    array (
      0 => 'proje_adi',
      1 => 'proje_kodu',
      2 => 'tutar',
      3 => 'kural_adi',
      4 => 'kullanici_adi',
      5 => 'tarih',
    ),
    'is_active' => true,
  ),
  31 => 
  array (
    'event_key' => 'kurula_uye_atandi',
    'event_title' => 'Kurul Üyeliğine Atandı',
    'category' => 'Kurul & Komite',
    'bell_title_template' => '🏛️ Kurul Ataması: {committee_name}',
    'bell_body_template' => '**{committee_name}** bünyesinde **{member_role}** rolüyle görevlendirildiniz. Oy Ağırlığı: **{voting_weight}**',
    'mail_subject_template' => '[Kurul Görevlendirmesi] {committee_name}',
    'mail_body_template' => '<p>Sayın <strong>{user_name}</strong>,</p><p>Köksan Enterprise PPM sistemi üzerinde <strong>{committee_name}</strong> bünyesinde <strong>{member_role}</strong> göreviyle görevlendirildiniz.</p><div style="background: #f8fafc; border-left: 4px solid #0284c7; padding: 12px 16px; margin: 16px 0; border-radius: 0 8px 8px 0;"><p style="margin: 0 0 6px 0; font-size: 13px;">🏛️ <strong>Kurul / Komite:</strong> {committee_name}</p><p style="margin: 0 0 6px 0; font-size: 13px;">👤 <strong>Görev / Rol:</strong> {member_role}</p><p style="margin: 0 0 6px 0; font-size: 13px;">⚖️ <strong>Oy Ağırlığı Katsayısı:</strong> {voting_weight}</p><p style="margin: 0; font-size: 13px;">✍️ <strong>İşlemi Yapan:</strong> {assigned_by}</p></div><p>Kurul gündemindeki oylamaları ve kararları sistem üzerinden takip edebilirsiniz.</p>',
    'available_variables' => 
    array (
      0 => 'user_name',
      1 => 'kullanici_adi',
      2 => 'committee_name',
      3 => 'kurul_adi',
      4 => 'member_role',
      5 => 'voting_weight',
      6 => 'assigned_by',
      7 => 'islem_yapan',
      8 => 'tarih',
    ),
    'is_active' => true,
  ),
  32 => 
  array (
    'event_key' => 'kuruldan_uye_cikarildi',
    'event_title' => 'Kurul Üyeliğinden Çıkarıldı',
    'category' => 'Kurul & Komite',
    'bell_title_template' => '🏛️ Kurul Üyeliği: {committee_name}',
    'bell_body_template' => '**{committee_name}** bünyesindeki kurul üyeliğiniz **{removed_by}** tarafından sonlandırılmıştır.',
    'mail_subject_template' => '[Kurul Bilgilendirmesi] {committee_name} Üyeliği',
    'mail_body_template' => '<p>Sayın <strong>{user_name}</strong>,</p><p><strong>{committee_name}</strong> bünyesindeki kurul üyeliğiniz sonlandırılmıştır.</p><div style="background: #fef2f2; border-left: 4px solid #ef4444; padding: 12px 16px; margin: 16px 0; border-radius: 0 8px 8px 0;"><p style="margin: 0 0 6px 0; font-size: 13px; color: #991b1b;">🏛️ <strong>Kurul / Komite:</strong> {committee_name}</p><p style="margin: 0; font-size: 13px; color: #991b1b;">✍️ <strong>İşlemi Yapan Yetkili:</strong> {removed_by}</p></div><p>Bilgilerinize sunarız.</p>',
    'available_variables' => 
    array (
      0 => 'user_name',
      1 => 'kullanici_adi',
      2 => 'committee_name',
      3 => 'kurul_adi',
      4 => 'removed_by',
      5 => 'islem_yapan',
      6 => 'tarih',
    ),
    'is_active' => true,
  ),
  33 => 
  array (
    'event_key' => 'proje_yetki_delege_edildi',
    'event_title' => 'Projede Yeni Yetki Tanımlandı',
    'category' => 'Proje',
    'bell_title_template' => 'Yetki Tanımlandı: {yetki_adi}',
    'bell_body_template' => '**{islem_yapan}** tarafından **{proje_kodu}** projesinde size **{yetki_adi}** yetkisi tanımlandı.',
    'mail_subject_template' => '[Yetki Tanımlandı] {proje_kodu} - {yetki_adi}',
    'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{islem_yapan}</strong> tarafından <strong>{proje_kodu} - {proje_adi}</strong> projesinde adınıza <strong>{yetki_adi}</strong> operasyonel yetkisi verilmiştir.</p>',
    'available_variables' => 
    array (
      0 => 'proje_kodu',
      1 => 'proje_adi',
      2 => 'islem_yapan',
      3 => 'yetki_adi',
      4 => 'kullanici_adi',
    ),
    'is_active' => true,
  ),
  34 => 
  array (
    'event_key' => 'uye_gorev_devri_yapildi',
    'event_title' => 'Proje Görevleri ve Rolü Size Devredildi',
    'category' => 'Proje',
    'bell_title_template' => 'Görev Devri: {proje_kodu}',
    'bell_body_template' => '**{ayrilan_kullanici}** kullanıcısının **{proje_kodu}** projesindeki görev ve yetkileri **{islem_yapan}** tarafından size devredildi.',
    'mail_subject_template' => '[Görev Devri] {proje_kodu} - Görevler Size Aktarıldı',
    'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{islem_yapan}</strong> tarafından <strong>{proje_kodu} - {proje_adi}</strong> projesinde <strong>{ayrilan_kullanici}</strong> üzerindeki <strong>{gorev_sayisi}</strong> adet görev ve sorumluluklar size aktarılmıştır.</p><p>Ayrılma Gerekçesi: <em>{ayrilma_sebebi}</em></p>',
    'available_variables' => 
    array (
      0 => 'proje_kodu',
      1 => 'proje_adi',
      2 => 'islem_yapan',
      3 => 'ayrilan_kullanici',
      4 => 'gorev_sayisi',
      5 => 'ayrilma_sebebi',
      6 => 'kullanici_adi',
    ),
    'is_active' => true,
  ),
  35 => 
  array (
    'event_key' => 'vekalet_verildi',
    'event_title' => 'Adınıza Kurumsal Vekalet Bırakıldı',
    'category' => 'Sistem',
    'bell_title_template' => 'Vekalet Bırakıldı: {vekalet_veren}',
    'bell_body_template' => '**{vekalet_veren}**, **{baslangic_tarihi}** - **{bitis_tarihi}** tarihleri arasında adınıza vekalet tanımladı.',
    'mail_subject_template' => '[Kurumsal Vekalet] {vekalet_veren} Size Vekalet Bıraktı',
    'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{vekalet_veren}</strong> kullanıcısı <strong>{baslangic_tarihi}</strong> ile <strong>{bitis_tarihi}</strong> tarihleri arasında sistemsel işlemler için sizi vekil tayin etmiştir.</p><p>Kapsam: <strong>{kapsam}</strong><br>Not: <em>{notlar}</em></p>',
    'available_variables' => 
    array (
      0 => 'vekalet_veren',
      1 => 'baslangic_tarihi',
      2 => 'bitis_tarihi',
      3 => 'kapsam',
      4 => 'notlar',
      5 => 'kullanici_adi',
    ),
    'is_active' => true,
  ),
  36 => 
  array (
    'event_key' => 'vekaleten_islem_yapildi',
    'event_title' => 'Vekaletiniz Kullanılarak İşlem Yapıldı',
    'category' => 'Sistem',
    'bell_title_template' => 'Vekaleten İşlem: {islem_adi}',
    'bell_body_template' => 'Vekiliniz **{vekil_adi}**, adınıza **{proje_kodu}** projesinde **{islem_adi}** işlemini gerçekleştirdi.',
    'mail_subject_template' => '[Vekalet Bildirimi] Vekiliniz Adınıza İşlem Yaptı',
    'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p>Vekiliniz <strong>{vekil_adi}</strong> tarafından adınıza <strong>{proje_kodu}</strong> projesinde <strong>{islem_adi}</strong> işlemi gerçekleştirilmiştir.</p><p>Zaman: <strong>{zaman}</strong></p>',
    'available_variables' => 
    array (
      0 => 'vekil_adi',
      1 => 'proje_kodu',
      2 => 'islem_adi',
      3 => 'zaman',
      4 => 'kullanici_adi',
    ),
    'is_active' => true,
  ),
  37 => 
  array (
    'event_key' => 'proje_lideri_degistirildi',
    'event_title' => 'Proje Liderliği Size Devredildi',
    'category' => 'Proje',
    'bell_title_template' => 'Proje Liderliği Devredildi: {proje_kodu}',
    'bell_body_template' => '**{proje_kodu}** projesinin liderliği **{islem_yapan}** tarafından resmi olarak size devredildi.',
    'mail_subject_template' => '[Proje Liderliği] {proje_kodu} Projesine Lider Olarak Atandınız',
    'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{proje_kodu} - {proje_adi}</strong> projesinin liderliği <strong>{islem_yapan}</strong> tarafından resmi olarak size devredilmiştir.</p>',
    'available_variables' => 
    array (
      0 => 'proje_kodu',
      1 => 'proje_adi',
      2 => 'islem_yapan',
      3 => 'kullanici_adi',
    ),
    'is_active' => true,
  ),
  38 => 
  array (
    'event_key' => 'vekalet_iptal_edildi',
    'event_title' => 'Kurumsal Vekalet İptal Edildi / Geri Alındı',
    'category' => 'Sistem',
    'bell_title_template' => 'Vekalet İptal Edildi: {vekalet_veren}',
    'bell_body_template' => '**{vekalet_veren}** adına tanımlanan vekalet sözleşmesi **{islem_yapan}** tarafından sonlandırıldı. (Gerekçe: {gerekce})',
    'mail_subject_template' => '[Vekalet İptali] {vekalet_veren} Vekaleti Sonlandırıldı',
    'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{vekalet_veren}</strong> adına tanımlı olan kurumsal vekalet sözleşmesi <strong>{islem_yapan}</strong> tarafından erken sonlandırılmış ve vekillik yetkileri geri alınmıştır.</p><p>Gerekçe / Açıklama: <em>{gerekce}</em></p>',
    'available_variables' => 
    array (
      0 => 'vekalet_veren',
      1 => 'vekil_adi',
      2 => 'islem_yapan',
      3 => 'gerekce',
      4 => 'kullanici_adi',
    ),
    'is_active' => true,
  ),
  39 => 
  array (
    'event_key' => 'vekalet_suresi_doldu',
    'event_title' => 'Kurumsal Vekalet Sözleşmesi Süresi Doldu',
    'category' => 'Sistem',
    'bell_title_template' => 'Vekalet Süresi Doldu: {vekalet_veren}',
    'bell_body_template' => '**{vekalet_veren}** ile aranızdaki kurumsal vekalet süresi ({bitis_tarihi}) doldu ve vekillik yetkileri otomatik olarak sona erdi.',
    'mail_subject_template' => '[Süre Dolumu] {vekalet_veren} Kurumsal Vekalet Süresi Doldu',
    'mail_body_template' => '<p>Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{vekalet_veren}</strong> ile <strong>{vekil_adi}</strong> arasındaki kurumsal vekalet sözleşmesinin süresi (<strong>{bitis_tarihi}</strong>) dolmuş ve vekillik yetkileri sistem tarafından otomatik olarak sonlandırılmıştır.</p>',
    'available_variables' => 
    array (
      0 => 'vekalet_veren',
      1 => 'vekil_adi',
      2 => 'bitis_tarihi',
      3 => 'kullanici_adi',
    ),
    'is_active' => true,
  ),
  40 => 
  array (
    'event_key' => 'toplanti_ertelendi',
    'event_title' => 'Toplantı Ertelendi',
    'category' => 'Toplantı',
    'bell_title_template' => '⏱️ Toplantı Ertelendi: {toplanti_baslik}',
    'bell_body_template' => '**{islem_yapan}** tarafından **{proje_kodu} - {proje_adi}** projesine ait **{toplanti_baslik}** toplantısı ertelendi.{erteleme_notu_alani} 📅 Yeni Tarih: **{yeni_tarih}**',
    'mail_subject_template' => '[Toplantı Ertelendi] {proje_kodu} - {toplanti_baslik}',
    'mail_body_template' => '<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #1e293b; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;"><div style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%); color: #ffffff; padding: 20px 24px;"><h2 style="margin: 0; font-size: 18px; font-weight: 700;">⏱️ Toplantı Ertelendi</h2><p style="margin: 4px 0 0; font-size: 13px; color: #fef3c7;">Köksan Enterprise PPM Toplantı Koordinasyonu</p></div><div style="padding: 24px; background: #ffffff;"><p style="margin-top: 0;">Sayın <strong>{kullanici_adi}</strong>,</p><p><strong>{proje_kodu} - {proje_adi}</strong> projesine ait planlanmış olan aşağıdaki toplantı <strong>{islem_yapan}</strong> tarafından ertelenmiştir:</p><div style="background: #fffbeb; border-left: 4px solid #f59e0b; padding: 14px 18px; margin: 18px 0; border-radius: 0 8px 8px 0;"><h3 style="margin: 0 0 10px 0; font-size: 15px; color: #78350f;">{toplanti_baslik}</h3><div style="font-size: 13px; color: #92400e; margin-bottom: 4px;">📅 <strong>Eski Tarih:</strong> <del>{eski_tarih}</del></div><div style="font-size: 13px; color: #15803d; font-weight: 600; margin-bottom: 4px;">📅 <strong>Yeni Tarih & Saat:</strong> {yeni_tarih}</div><div style="font-size: 13px; color: #475569; margin-bottom: 4px;">📍 <strong>Lokasyon / Salon:</strong> {lokasyon}</div><div style="font-size: 13px; color: #475569;">🏢 <strong>Departman:</strong> {departman_adi}</div></div>{erteleme_notu_alani}{gundem_alani}<div style="margin-top: 24px; text-align: center;"><a href="{toplanti_url}" style="display: inline-block; background: #d97706; color: #ffffff; text-decoration: none; padding: 10px 24px; border-radius: 8px; font-weight: 700; font-size: 14px;">Güncel Toplantı Detaylarını İncele ↗</a></div></div><div style="background: #f1f5f9; padding: 14px 24px; font-size: 12px; color: #64748b; text-align: center; border-top: 1px solid #e2e8f0;">Bu e-posta Köksan Enterprise PPM sistemi tarafından otomatik olarak oluşturulmuştur.</div></div>',
    'available_variables' => 
    array (
      0 => 'toplanti_baslik',
      1 => 'proje_kodu',
      2 => 'proje_adi',
      3 => 'islem_yapan',
      4 => 'eski_tarih',
      5 => 'yeni_tarih',
      6 => 'erteleme_notu_alani',
      7 => 'lokasyon',
      8 => 'kullanici_adi',
      9 => 'departman_adi',
      10 => 'toplanti_url',
      11 => 'gundem_alani',
    ),
    'is_active' => true,
  ),
);

        $roles = Role::all();

        foreach ($templates as $item) {
            $tpl = NotificationTemplate::updateOrCreate(
                ['event_key' => $item['event_key']],
                [
                    'event_title' => $item['event_title'],
                    'category' => $item['category'] ?? 'Genel',
                    'bell_title_template' => $item['bell_title_template'],
                    'bell_body_template' => $item['bell_body_template'],
                    'mail_subject_template' => $item['mail_subject_template'],
                    'mail_body_template' => $item['mail_body_template'],
                    'available_variables' => $item['available_variables'],
                    'is_active' => $item['is_active'] ?? true,
                ]
            );

            // Matris kayıtlarını tüm roller için garantiye al
            foreach ($roles as $role) {
                NotificationMatrix::firstOrCreate(
                    [
                        'event_key' => $item['event_key'],
                        'role_id' => $role->id,
                    ],
                    [
                        'template_id' => $tpl->id,
                        'is_bell_active' => true,
                        'is_mail_active' => in_array($role->name, ['Admin (Sistem Yöneticisi)', 'Proje Yöneticisi', 'Proje Sponsoru', 'Yönetim (Read-Only)']),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        // Geri alma durumunda şablonları silmeye gerek yoktur
    }
};