<!-- HARCAMA KATEGORİLERİ YÖNETİM MODALI (BÜTÇE KIRILIMI DİNAMİK YÖNETİMİ) -->
<div id="budgetCategoryModalBackdrop" class="custom-modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 10000; backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 20px;">
    <div class="custom-modal-dialog" style="max-width: 640px; width: 100%; max-height: 85vh; display: flex; flex-direction: column; background: #ffffff; border-radius: 14px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25); overflow: hidden;">
        <div class="custom-modal-header" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h3 class="custom-modal-title" style="margin: 0; font-size: 15px; font-weight: 700; color: #ffffff; display: flex; align-items: center; gap: 8px;">
                <span>📊</span> Harcama Kategorileri Yönetimi (Bütçe Kırılımı)
            </h3>
            <button type="button" class="custom-modal-close" onclick="closeBudgetCategoryModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">✕</button>
        </div>

        <div class="custom-modal-body" style="padding: 20px; overflow-y: auto;">
            <!-- ÜST FORM: YENİ EKLE / DÜZENLE -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <span id="budgetCatFormHeaderTitle" style="font-size: 13px; font-weight: 700; color: #0f172a;">
                        ➕ Yeni Harcama Kategorisi Ekle
                    </span>
                    <button type="button" id="btnCancelEditBudgetCat" onclick="resetBudgetCategoryForm()" style="display: none; background: none; border: none; font-size: 11px; color: #64748b; cursor: pointer; text-decoration: underline;">
                        ✕ Düzenlemeyi İptal Et (Yeni Ekle Moduna Dön)
                    </button>
                </div>

                <input type="hidden" id="editBudgetCatId" value="">

                <div class="grid-2 mb-2" style="gap: 12px; display: grid; grid-template-columns: 2fr 1fr;">
                    <div class="form-group mb-0">
                        <label style="font-size: 11.5px; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Kategori Adı <span class="text-danger" style="color: #ef4444;">*</span></label>
                        <input type="text" id="newBudgetCatName" class="form-control" placeholder="Örn: Nakliye & Lojistik Maliyetleri" required style="height: 38px; font-size: 12.5px;">
                    </div>

                    <div class="form-group mb-0">
                        <label style="font-size: 11.5px; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Renk Kodu</label>
                        <div style="display: flex; gap: 8px;">
                            <input type="color" id="newBudgetCatColor" value="#0284c7" style="height: 38px; width: 44px; padding: 2px; border: 1px solid #cbd5e1; border-radius: 6px; cursor: pointer;">
                            <input type="text" id="newBudgetCatColorHex" class="form-control" value="#0284c7" maxlength="7" oninput="document.getElementById('newBudgetCatColor').value = this.value" style="height: 38px; font-size: 12px; font-family: monospace;">
                        </div>
                    </div>
                </div>

                <div class="form-group mb-2">
                    <label style="font-size: 11.5px; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Açıklama <span style="font-size: 11px; color: #64748b;">(Opsiyonel)</span></label>
                    <input type="text" id="newBudgetCatDesc" class="form-control" placeholder="Bu harcama kaleminin kullanım alanı..." style="height: 36px; font-size: 12px;">
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 10px;">
                    <button type="button" class="btn btn-primary btn-sm" id="btnSaveBudgetCat" onclick="saveBudgetCategoryModalAction()" style="padding: 6px 18px; font-size: 12px; font-weight: 700; border-radius: 6px;">
                        ✓ Harcama Kategorisini Kaydet
                    </button>
                </div>
            </div>

            <!-- ALT BÖLÜM: MEVCUT LİSTE -->
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <h4 style="margin: 0; font-size: 13px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                        <span>📋</span> Mevcut Harcama Kategorileri
                    </h4>
                    <span style="font-size: 11px; color: #64748b;">Bütçe kırılım tablosunda anlık listelenir.</span>
                </div>

                <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                    <table class="table" style="width: 100%; margin-bottom: 0; font-size: 12px;">
                        <thead style="background: #f8fafc; color: #475569; font-size: 11px; text-transform: uppercase;">
                            <tr>
                                <th style="padding: 8px 12px; width: 65%;">Kategori Adı</th>
                                <th style="padding: 8px 12px; width: 35%; text-align: right;">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="budgetCategoryModalTableBody">
                            <!-- AJAX ile dinamik yüklenecek -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="custom-modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: flex-end;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeBudgetCategoryModal()">Kapat</button>
        </div>
    </div>
</div>