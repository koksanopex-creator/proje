# Görev Listesi: Modüler Proje Kapsam Araçları & Widget Sistemi

- [ ] 1. Veritabanı ve Model Altyapısı <!-- id: 1 -->
  - [ ] Migration: `projects` tablosuna `scope_tools` JSON kolonunun eklenmesi <!-- id: 1.1 -->
  - [ ] Model: `App\Models\Project` fillable ve cast tanımları, yardımcı metodlar <!-- id: 1.2 -->
- [ ] 2. Form Katmanı (Create & Edit) <!-- id: 2 -->
  - [ ] `create.blade.php`: Bölüm 5'e "+ Projeye Kapsam Aracı Ekle" menüsü ve 6 modüler araç bileşeninin eklenmesi <!-- id: 2.1 -->
  - [ ] `edit.blade.php`: Mevcut `scope_tools` verilerini yükleme, yeni araç ekleme, düzenleme ve silme desteği <!-- id: 2.2 -->
  - [ ] `ProjectController`: `store` ve `update` isteklerinde `scope_tools` verisinin doğrulanması ve işlenmesi <!-- id: 2.3 -->
- [ ] 3. Görünüm Katmanı (Show, Index, Draft Review) <!-- id: 3 -->
  - [ ] `projects/show.blade.php`: Kapsam ve bütçe sekmesinde aktif araçların modern kurumsal kartlar ve grafiklerle görselleştirilmesi <!-- id: 3.1 -->
  - [ ] `projects/external_draft_review.blade.php`: Taslak inceleme ekranında araçların görüntülenmesi <!-- id: 3.2 -->
  - [ ] `projects/index.blade.php`: Kart ve tablo satırlarında araç özet rozetlerinin gösterilmesi <!-- id: 3.3 -->
- [ ] 4. Denetim İzi (Audit Log) & Vekalet Entegrasyonu <!-- id: 4 -->
  - [ ] `ProjectService`: Araç değişimlerinin (ekleme, çıkarma, güncelleme) diff snapshot'ı ve log metni üretimi <!-- id: 4.1 -->
  - [ ] Vekil işlemleri: Vekalet durumunda Madde 16 kurallarına uygun loglama <!-- id: 4.2 -->
  - [ ] `ProjectAuditLog`: Diff karşılaştırma tablosunda `scope_tools` parametrelerinin Türkçe etiketlerle ayrıştırılması <!-- id: 4.3 -->
- [ ] 5. Test ve Doğrulama <!-- id: 5 -->
  - [ ] Proje oluşturma, düzenleme ve araç ekleme testleri <!-- id: 5.1 -->
  - [ ] Denetim logları ekranında eski/yeni diff doğrulaması <!-- id: 5.2 -->
