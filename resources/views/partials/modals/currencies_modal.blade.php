<!-- PARA BİRİMLERİ YÖNETİM MODALI (DİNAMİK PARA BİRİMİ CRUDS) -->
<div id="currencyModalBackdrop" class="custom-modal-backdrop" onclick="if(event.target === this) closeCurrencyModal()" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 10100; backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 20px;">
    <div class="custom-modal-dialog" style="max-width: 620px; width: 100%; max-height: 85vh; display: flex; flex-direction: column; background: #ffffff; border-radius: 14px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25); overflow: hidden;">
        <div class="custom-modal-header" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
            <h3 class="custom-modal-title" style="margin: 0; font-size: 15px; font-weight: 700; color: #ffffff; display: flex; align-items: center; gap: 8px;">
                <span>💱</span> Para Birimleri Yönetimi
            </h3>
            <button type="button" class="custom-modal-close" onclick="closeCurrencyModal()" style="background: none; border: none; color: #94a3b8; font-size: 20px; cursor: pointer;">✕</button>
        </div>

        <div class="custom-modal-body" style="padding: 20px; overflow-y: auto;">
            <!-- ÜST FORM: YENİ EKLE / DÜZENLE -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <span id="currencyFormHeaderTitle" style="font-size: 13px; font-weight: 700; color: #0f172a;">
                        ➕ Yeni Para Birimi Ekle
                    </span>
                    <button type="button" id="btnCancelEditCurrency" onclick="resetCurrencyForm()" style="display: none; background: none; border: none; font-size: 11px; color: #64748b; cursor: pointer; text-decoration: underline;">
                        ✕ Düzenlemeyi İptal Et (Yeni Ekle Moduna Dön)
                    </button>
                </div>

                <input type="hidden" id="editCurrencyId" value="">

                <div class="grid-3 mb-2" style="gap: 12px; display: grid; grid-template-columns: 1fr 2fr 1fr;">
                    <div class="form-group mb-0">
                        <label style="font-size: 11.5px; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Kod <span style="color: #ef4444;">*</span></label>
                        <input type="text" id="newCurrencyCode" class="form-control" placeholder="Örn: USD" maxlength="10" required style="height: 38px; font-size: 12.5px; text-transform: uppercase; font-weight: 700;">
                    </div>

                    <div class="form-group mb-0">
                        <label style="font-size: 11.5px; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Tanım / Ad <span style="color: #ef4444;">*</span></label>
                        <input type="text" id="newCurrencyName" class="form-control" placeholder="Örn: Amerikan Doları" required style="height: 38px; font-size: 12.5px;">
                    </div>

                    <div class="form-group mb-0">
                        <label style="font-size: 11.5px; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Sembol <span style="color: #ef4444;">*</span></label>
                        <input type="text" id="newCurrencySymbol" class="form-control" placeholder="Örn: $" maxlength="10" required style="height: 38px; font-size: 14px; text-align: center; font-weight: 800;">
                    </div>
                </div>

                <div class="grid-2 mb-2" style="gap: 12px; display: grid; grid-template-columns: 1fr 1fr; align-items: center; margin-top: 10px;">
                    <div class="form-group mb-0">
                        <label style="font-size: 11.5px; font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Sabit Referans Kuru (TRY bazında)</label>
                        <input type="number" step="0.0001" id="newCurrencyRate" class="form-control" placeholder="1.0000" value="1.0000" style="height: 36px; font-size: 12px;">
                    </div>

                    <div style="padding-top: 18px;">
                        <label style="font-size: 12px; font-weight: 600; color: #1e293b; display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" id="newCurrencyIsDefault" style="width: 16px; height: 16px; cursor: pointer;">
                            <span>Varsayılan Para Birimi Yap</span>
                        </label>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 12px;">
                    <button type="button" class="btn btn-primary btn-sm" id="btnSaveCurrency" onclick="saveCurrencyModalAction()" style="padding: 6px 18px; font-size: 12px; font-weight: 700; border-radius: 6px;">
                        ✓ Para Birimini Kaydet
                    </button>
                </div>
            </div>

            <!-- ALT BÖLÜM: MEVCUT LİSTE -->
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <h4 style="margin: 0; font-size: 13px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                        <span>📋</span> Sistemde Tanımlı Para Birimleri
                    </h4>
                    <span style="font-size: 11px; color: #64748b;">Proje bütçesinde ve kümülatif toplamda kullanılır.</span>
                </div>

                <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                    <table class="table" style="width: 100%; margin-bottom: 0; font-size: 12px;">
                        <thead style="background: #f8fafc; color: #475569; font-size: 11px; text-transform: uppercase;">
                            <tr>
                                <th style="padding: 8px 12px; width: 25%;">Kod & Sembol</th>
                                <th style="padding: 8px 12px; width: 45%;">Para Birimi Adı</th>
                                <th style="padding: 8px 12px; width: 30%; text-align: right;">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="currencyModalTableBody">
                            <!-- AJAX ile dinamik yüklenecek -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="custom-modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: flex-end;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeCurrencyModal()">Kapat</button>
        </div>
    </div>
</div>