@php
    $existingTools = old('scope_tools');
    if (!$existingTools && isset($project) && $project && is_array($project->scope_tools)) {
        $existingTools = $project->scope_tools;
    }
    $existingToolsJson = json_encode($existingTools ?: new stdClass(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
@endphp

<!-- MODÜLER KAPSAM & ANALİZ ARAÇLARI (WIDGET HAVUZU) -->
<div class="card mb-4" id="scopeToolsMainCard" style="border: 1px solid #cbd5e1; border-radius: 12px; box-shadow: 0 2px 8px rgba(15,23,42,0.04); overflow: visible; position: relative; margin-top: 20px; background: #ffffff;">
    <div style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); padding: 16px 20px; border-bottom: 1.5px solid #e2e8f0; border-top-left-radius: 11px; border-top-right-radius: 11px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; position: relative;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #0284c7, #2563eb); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 19px; box-shadow: 0 2px 6px rgba(2,132,199,0.25);">
                🛠️
            </div>
            <div>
                <h4 style="margin: 0; font-size: 14.5px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <span>Kapsam, Analiz & Proje Araçları</span>
                    <span class="badge" id="activeToolsCounterBadge" style="background: #e0f2fe; color: #0284c7; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 6px; border: 1px solid #bae6fd;">
                        0 Araç Aktif
                    </span>
                </h4>
                <p style="margin: 3px 0 0; font-size: 11.5px; color: #64748b;">
                    Düz metin yerine projeyi zenginleştirecek analitik grafikler, KPI'lar, ROI hesabı ve kapsam sınırları ekleyebilirsiniz.
                </p>
            </div>
        </div>

        <!-- ARAÇ EKLEME AÇILIR MENÜSÜ -->
        <div style="position: relative; display: inline-block;">
            <button type="button" class="btn btn-primary btn-sm" id="btnToggleToolDropdown" onclick="toggleToolSelectorDropdown()" style="font-weight: 700; border-radius: 8px; display: inline-flex; align-items: center; gap: 7px; padding: 7px 15px; font-size: 12.5px; box-shadow: 0 2px 5px rgba(2,132,199,0.2);">
                <span>➕ Projeye Araç Ekle</span>
                <span style="font-size: 10px;">▼</span>
            </button>
            <div id="toolSelectorDropdown" style="display: none; position: absolute; right: 0; top: calc(100% + 6px); width: 310px; max-width: 92vw; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; box-shadow: 0 16px 36px rgba(15,23,42,0.22); z-index: 99999; padding: 6px 0;">
                <div style="padding: 7px 14px; font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #f1f5f9;">
                    Kullanılabilir Modüler Araçlar
                </div>
                <a href="javascript:void(0)" class="tool-dropdown-item" id="toolOpt_budget_chart" onclick="activateScopeTool('budget_chart')" style="display: flex; align-items: center; justify-content: space-between; padding: 9px 14px; text-decoration: none; color: #1e293b; font-size: 12.5px; transition: background 0.15s;">
                    <div style="display: flex; align-items: center; gap: 9px;">
                        <span style="font-size: 17px;">📊</span>
                        <div>
                            <strong style="display: block; font-size: 12px; color: #0f172a;">Bütçe Dağılım Grafiği (Donut)</strong>
                            <span style="font-size: 10.5px; color: #64748b;">Halka grafik ve kategori kırılımı</span>
                        </div>
                    </div>
                    <span class="tool-status-icon" style="font-size: 13px; color: #10b981; display: none;">✓</span>
                </a>
                <a href="javascript:void(0)" class="tool-dropdown-item" id="toolOpt_kpis" onclick="activateScopeTool('kpis')" style="display: flex; align-items: center; justify-content: space-between; padding: 9px 14px; text-decoration: none; color: #1e293b; font-size: 12.5px; transition: background 0.15s;">
                    <div style="display: flex; align-items: center; gap: 9px;">
                        <span style="font-size: 17px;">🎯</span>
                        <div>
                            <strong style="display: block; font-size: 12px; color: #0f172a;">SMART Hedefler & KPI Radarı</strong>
                            <span style="font-size: 10.5px; color: #64748b;">Canlı başarı grafiği & metrikler</span>
                        </div>
                    </div>
                    <span class="tool-status-icon" style="font-size: 13px; color: #10b981; display: none;">✓</span>
                </a>
                <a href="javascript:void(0)" class="tool-dropdown-item" id="toolOpt_scope_boundaries" onclick="activateScopeTool('scope_boundaries')" style="display: flex; align-items: center; justify-content: space-between; padding: 9px 14px; text-decoration: none; color: #1e293b; font-size: 12.5px; transition: background 0.15s;">
                    <div style="display: flex; align-items: center; gap: 9px;">
                        <span style="font-size: 17px;">📌</span>
                        <div>
                            <strong style="display: block; font-size: 12px; color: #0f172a;">Kapsam Sınırları (Dahil / Hariç)</strong>
                            <span style="font-size: 10.5px; color: #64748b;">Yapılacaklar vs. Faz 2 listesi</span>
                        </div>
                    </div>
                    <span class="tool-status-icon" style="font-size: 13px; color: #10b981; display: none;">✓</span>
                </a>
                <a href="javascript:void(0)" class="tool-dropdown-item" id="toolOpt_roi_calculator" onclick="activateScopeTool('roi_calculator')" style="display: flex; align-items: center; justify-content: space-between; padding: 9px 14px; text-decoration: none; color: #1e293b; font-size: 12.5px; transition: background 0.15s;">
                    <div style="display: flex; align-items: center; gap: 9px;">
                        <span style="font-size: 17px;">⚖️</span>
                        <div>
                            <strong style="display: block; font-size: 12px; color: #0f172a;">ROI & Geri Dönüş Hesabı</strong>
                            <span style="font-size: 10.5px; color: #64748b;">Yatırım amortisman ve tasarruf</span>
                        </div>
                    </div>
                    <span class="tool-status-icon" style="font-size: 13px; color: #10b981; display: none;">✓</span>
                </a>
                <a href="javascript:void(0)" class="tool-dropdown-item" id="toolOpt_dependencies" onclick="activateScopeTool('dependencies')" style="display: flex; align-items: center; justify-content: space-between; padding: 9px 14px; text-decoration: none; color: #1e293b; font-size: 12.5px; transition: background 0.15s;">
                    <div style="display: flex; align-items: center; gap: 9px;">
                        <span style="font-size: 17px;">🔗</span>
                        <div>
                            <strong style="display: block; font-size: 12px; color: #0f172a;">Kritik Dış Bağımlılıklar</strong>
                            <span style="font-size: 10.5px; color: #64748b;">Tedarikçi ve dış izin kilometre taşları</span>
                        </div>
                    </div>
                    <span class="tool-status-icon" style="font-size: 13px; color: #10b981; display: none;">✓</span>
                </a>
                <a href="javascript:void(0)" class="tool-dropdown-item" id="toolOpt_custom_fields" onclick="activateScopeTool('custom_fields')" style="display: flex; align-items: center; justify-content: space-between; padding: 9px 14px; text-decoration: none; color: #1e293b; font-size: 12.5px; transition: background 0.15s;">
                    <div style="display: flex; align-items: center; gap: 9px;">
                        <span style="font-size: 17px;">🧩</span>
                        <div>
                            <strong style="display: block; font-size: 12px; color: #0f172a;">Özel Parametreler / Alanlar</strong>
                            <span style="font-size: 10.5px; color: #64748b;">Özel etiket & değer eşleşmeleri</span>
                        </div>
                    </div>
                    <span class="tool-status-icon" style="font-size: 13px; color: #10b981; display: none;">✓</span>
                </a>
            </div>
        </div>
    </div>

    <!-- HİÇ ARAÇ EKLENMEMİŞSE GÖSTERİLEN BİLGİ KUTUSU -->
    <div id="noToolsPlaceholder" style="padding: 24px; text-align: center; color: #64748b; background: #fafafa; border-bottom-left-radius: 11px; border-bottom-right-radius: 11px;">
        <span style="font-size: 28px; display: block; margin-bottom: 6px;">💡</span>
        <strong style="font-size: 13px; color: #334155; display: block;">Henüz bir kapsam / analiz aracı eklenmedi</strong>
        <p style="margin: 4px 0 10px 0; font-size: 12px; color: #94a3b8;">
            İhtiyacınıza göre yukarıdaki <strong>"+ Projeye Araç Ekle"</strong> butonunu kullanarak bütçe dağılım grafiği, SMART hedefler veya ROI hesaplayıcı ekleyebilirsiniz.
        </p>
    </div>

    <!-- AKTİF ARAÇLAR KAPSAYICISI (GRID DÜZENİNDE KARTLAR) -->
    <div id="activeToolsContainer" style="padding: 18px 20px; display: flex; flex-direction: column; gap: 18px;">

        <!-- 1. ARAÇ: BÜTÇE DAĞILIM GRAFİĞİ (DONUT VE KALEMLER) -->
        <div class="scope-tool-card" id="toolCard_budget_chart" style="display: none; background: #ffffff; border: 1.5px solid #bae6fd; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 6px rgba(2,132,199,0.04);">
            <div style="background: #f0f9ff; padding: 10px 16px; border-bottom: 1px solid #bae6fd; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 16px;">📊</span>
                    <strong style="font-size: 13px; color: #0369a1;">Bütçe Dağılım Grafiği (Donut & Kategori Kırılımı)</strong>
                    <span class="badge" style="background: #e0f2fe; color: #0284c7; font-size: 10.5px;">Görsel Grafik</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button type="button" class="btn btn-xs btn-outline-primary" onclick="syncBudgetChartFromDetailedItems()" style="font-size: 11px; padding: 3px 9px; border-radius: 5px;">
                        ⚡ Bütçe Kalemlerinden Otomatik Çek
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-danger" onclick="deactivateScopeTool('budget_chart')" style="font-size: 11px; padding: 3px 8px; border-radius: 5px;" title="Bu aracı projeden kaldır">
                        🗑️ Aracı Kaldır
                    </button>
                </div>
            </div>
            <div style="padding: 16px;">
                <div style="display: grid; grid-template-columns: 1.3fr 1.1fr; gap: 18px; align-items: start;">
                    <!-- Sol: Kalem Girişleri -->
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 12px; font-weight: 700; color: #334155;">Kategori Kalemleri & Tutarlar</span>
                            <button type="button" class="btn btn-xs btn-outline-success" onclick="addBudgetChartRow()" style="font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                + Satır Ekle
                            </button>
                        </div>
                        <div id="budgetChartRowsContainer" style="display: flex; flex-direction: column; gap: 8px;">
                            <!-- Dinamik Satırlar -->
                        </div>
                    </div>

                    <!-- Sağ: Canlı Donut Grafik ve Önizleme -->
                    <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <span style="font-size: 12px; font-weight: 800; color: #334155;">
                                🍩 Bütçe Halka (Donut) Grafiği
                            </span>
                            <span id="budgetChartTotalBadge" style="font-size: 11px; font-weight: 800; color: #0284c7; background: #e0f2fe; padding: 2px 8px; border-radius: 6px;">
                                Toplam: ₺0
                            </span>
                        </div>
                        
                        <!-- Donut Chart & Legend Yan Yana -->
                        <div style="display: grid; grid-template-columns: 130px 1fr; gap: 14px; align-items: center; margin-bottom: 12px;">
                            <!-- SVG Donut Chart Kapsayıcısı -->
                            <div id="budgetChartSvgContainer" style="display: flex; justify-content: center; align-items: center; min-height: 130px;">
                                <!-- Dinamik SVG Donut Buraya Çizilecek -->
                            </div>
                            <!-- Lejant Listesi -->
                            <div id="budgetChartLegendList" style="display: flex; flex-direction: column; gap: 6px; font-size: 11.5px; max-height: 160px; overflow-y: auto;"></div>
                        </div>

                        <!-- Segmentli Dağılım Barı -->
                        <div style="font-size: 10.5px; font-weight: 600; color: #64748b; margin-bottom: 4px;">Kümülatif Pay Dağılımı:</div>
                        <div id="budgetChartProgressBar" style="height: 12px; border-radius: 5px; background: #e2e8f0; display: flex; overflow: hidden;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. ARAÇ: SMART HEDEFLER & KPI METRİKLERİ (CANLI GRAFİKLERLE) -->
        <div class="scope-tool-card" id="toolCard_kpis" style="display: none; background: #ffffff; border: 1.5px solid #bbf7d0; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 6px rgba(16,185,129,0.04);">
            <div style="background: #f0fdf4; padding: 10px 16px; border-bottom: 1px solid #bbf7d0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 16px;">🎯</span>
                    <strong style="font-size: 13px; color: #166534;">SMART Hedefler & KPI Başarı Radarı</strong>
                    <span class="badge" style="background: #dcfce7; color: #15803d; font-size: 10.5px;">Canlı Grafik Çizimi</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button type="button" class="btn btn-xs btn-outline-success" onclick="addKpiRow()" style="font-size: 11px; padding: 3px 9px; border-radius: 5px;">
                        + Yeni Hedef / KPI Ekle
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-danger" onclick="deactivateScopeTool('kpis')" style="font-size: 11px; padding: 3px 8px; border-radius: 5px;" title="Bu aracı projeden kaldır">
                        🗑️ Aracı Kaldır
                    </button>
                </div>
            </div>
            
            <div style="padding: 16px;">
                <!-- CANLI KPI BAŞARI RADARI & ÖZETİ -->
                <div id="kpiRadarSummaryCard" style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border: 1.5px solid #a7f3d0; border-radius: 9px; padding: 12px 16px; margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <!-- SVG Mini Circular Gauge -->
                        <div id="kpiRadarGaugeContainer" style="width: 44px; height: 44px; position: relative; flex-shrink: 0;">
                            <svg width="44" height="44" viewBox="0 0 44 44" style="transform: rotate(-90deg);">
                                <circle cx="22" cy="22" r="18" fill="transparent" stroke="#d1fae5" stroke-width="5"></circle>
                                <circle id="kpiRadarGaugeCircle" cx="22" cy="22" r="18" fill="transparent" stroke="#10b981" stroke-width="5" stroke-dasharray="0 113.1" stroke-linecap="round" style="transition: stroke-dasharray 0.4s ease;"></circle>
                            </svg>
                            <span id="kpiRadarGaugeText" style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 10.5px; font-weight: 850; color: #065f46;">
                                %0
                            </span>
                        </div>
                        <div>
                            <strong style="color: #065f46; font-size: 13px; display: block;">Genel KPI Başarı & Hedef Ortalaması</strong>
                            <span id="kpiRadarSubtitle" style="font-size: 11.5px; color: #047857;">Tanımlı KPI verilerinden anlık başarı oranı hesaplanmaktadır.</span>
                        </div>
                    </div>

                    <div style="display: flex; gap: 8px; flex-wrap: wrap;" id="kpiRadarBadges">
                        <span class="badge" id="kpiCountBadge" style="background: #ffffff; color: #166534; border: 1px solid #bbf7d0; font-size: 11px; padding: 4px 9px;">0 KPI</span>
                        <span class="badge" id="kpiAchievedBadge" style="background: #10b981; color: #ffffff; font-size: 11px; padding: 4px 9px;">0 Hedefte</span>
                        <span class="badge" id="kpiProgressBadge" style="background: #0284c7; color: #ffffff; font-size: 11px; padding: 4px 9px;">0 İlerlemede</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 12.5px;">
                        <thead style="background: #f8fafc; color: #475569; font-weight: 700; font-size: 11.5px;">
                            <tr>
                                <th style="min-width: 170px;">Hedef / Metrik Adı <span class="text-danger">*</span></th>
                                <th style="width: 105px;">Başlangıç</th>
                                <th style="width: 105px;">Hedef Değer <span class="text-danger">*</span></th>
                                <th style="width: 105px;">Gerçekleşen</th>
                                <th style="width: 75px;">Birim</th>
                                <th style="width: 185px;">📊 Canlı Başarı Grafiği</th>
                                <th style="width: 45px; text-align: center;">Sil</th>
                            </tr>
                        </thead>
                        <tbody id="kpiRowsTableBody">
                            <!-- Dinamik KPI Satırları -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 3. ARAÇ: KAPSAM SINIRLARI (DAHİL / HARİÇ) -->
        <div class="scope-tool-card" id="toolCard_scope_boundaries" style="display: none; background: #ffffff; border: 1.5px solid #fed7aa; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 6px rgba(234,88,12,0.04);">
            <div style="background: #fff7ed; padding: 10px 16px; border-bottom: 1px solid #fed7aa; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 16px;">📌</span>
                    <strong style="font-size: 13px; color: #9a3412;">Kapsam Sınırları Listesi (In-Scope vs. Out-of-Scope)</strong>
                    <span class="badge" style="background: #ffedd5; color: #c2410c; font-size: 10.5px;">Kapsam Kaymasını Önler</span>
                </div>
                <button type="button" class="btn btn-xs btn-outline-danger" onclick="deactivateScopeTool('scope_boundaries')" style="font-size: 11px; padding: 3px 8px; border-radius: 5px;" title="Bu aracı projeden kaldır">
                    🗑️ Aracı Kaldır
                </button>
            </div>
            <div style="padding: 16px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <!-- Sol: Kapsam İçi -->
                    <div style="background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 8px; padding: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <strong style="color: #166534; font-size: 12.5px; display: flex; align-items: center; gap: 6px;">
                                <span>✅</span> Kapsam İçi (Taahhüt Edilenler)
                            </strong>
                            <span class="badge" id="inScopeCounter" style="background: #dcfce7; color: #166534; font-size: 10.5px;">0 Madde</span>
                        </div>
                        <div style="display: flex; gap: 6px; margin-bottom: 10px;">
                            <input type="text" id="inScopeInput" class="form-control form-control-sm" placeholder="Kapsam içi madde yazıp Enter'a basın..." onkeydown="if(event.key==='Enter'){event.preventDefault(); addBoundaryItem('in');}">
                            <button type="button" class="btn btn-sm btn-success" onclick="addBoundaryItem('in')" style="padding: 2px 10px; font-weight: 700;">+</button>
                        </div>
                        <div id="inScopeListContainer" style="display: flex; flex-direction: column; gap: 6px; max-height: 200px; overflow-y: auto;"></div>
                    </div>

                    <!-- Sağ: Kapsam Dışı -->
                    <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 8px; padding: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <strong style="color: #991b1b; font-size: 12.5px; display: flex; align-items: center; gap: 6px;">
                                <span>🚫</span> Kapsam Dışı (Dahil Edilmeyenler / Faz 2)
                            </strong>
                            <span class="badge" id="outScopeCounter" style="background: #fee2e2; color: #991b1b; font-size: 10.5px;">0 Madde</span>
                        </div>
                        <div style="display: flex; gap: 6px; margin-bottom: 10px;">
                            <input type="text" id="outScopeInput" class="form-control form-control-sm" placeholder="Kapsam dışı madde yazıp Enter'a basın..." onkeydown="if(event.key==='Enter'){event.preventDefault(); addBoundaryItem('out');}">
                            <button type="button" class="btn btn-sm btn-danger" onclick="addBoundaryItem('out')" style="padding: 2px 10px; font-weight: 700;">+</button>
                        </div>
                        <div id="outScopeListContainer" style="display: flex; flex-direction: column; gap: 6px; max-height: 200px; overflow-y: auto;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. ARAÇ: ROI, TASARRUF & GERİ DÖNÜŞ HESAPLAYICI -->
        <div class="scope-tool-card" id="toolCard_roi_calculator" style="display: none; background: #ffffff; border: 1.5px solid #ddd6fe; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 6px rgba(109,40,217,0.04);">
            <div style="background: #f5f3ff; padding: 10px 16px; border-bottom: 1px solid #ddd6fe; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 16px;">⚖️</span>
                    <strong style="font-size: 13px; color: #6d28d9;">ROI, Tasarruf & Geri Dönüş Hesaplayıcı</strong>
                    <span class="badge" style="background: #ede9fe; color: #6d28d9; font-size: 10.5px;">Fizibilite Analizi</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button type="button" class="btn btn-xs btn-outline-primary" onclick="pullInvestmentFromPlannedBudget()" style="font-size: 11px; padding: 3px 9px; border-radius: 5px;">
                        💰 Planlanan Bütçeden Çek
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-danger" onclick="deactivateScopeTool('roi_calculator')" style="font-size: 11px; padding: 3px 8px; border-radius: 5px;" title="Bu aracı projeden kaldır">
                        🗑️ Aracı Kaldır
                    </button>
                </div>
            </div>
            <div style="padding: 16px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-bottom: 14px;">
                    <div class="form-group mb-0">
                        <label class="form-label font-weight-600" style="font-size: 12px; color: #475569; margin-bottom: 4px;">
                            Yatırım Tutarı (Maliyet)
                        </label>
                        <input type="number" step="0.01" id="roiInvestmentCost" class="form-control form-control-sm" placeholder="0.00" oninput="calculateRoiMetrics()">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label font-weight-600" style="font-size: 12px; color: #475569; margin-bottom: 4px;">
                            Yıllık Tahmini Net Tasarruf / Kazanç
                        </label>
                        <input type="number" step="0.01" id="roiAnnualSavings" class="form-control form-control-sm" placeholder="0.00" oninput="calculateRoiMetrics()">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label font-weight-600" style="font-size: 12px; color: #475569; margin-bottom: 4px;">
                            Hesaplama / Amortisman Ufku
                        </label>
                        <select id="roiHorizonYears" class="form-control form-control-sm" onchange="calculateRoiMetrics()">
                            <option value="1">1 Yıl</option>
                            <option value="2">2 Yıl</option>
                            <option value="3" selected>3 Yıl (Standart)</option>
                            <option value="5">5 Yıl (Uzun Vade)</option>
                        </select>
                    </div>
                </div>

                <!-- Dinamik Sonuç Göstergeleri Kartı -->
                <div style="background: linear-gradient(135deg, #f8fafc 0%, #ede9fe 100%); border: 1.5px solid #c4b5fd; border-radius: 10px; padding: 14px 18px; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px;">
                    <div>
                        <span style="font-size: 11px; font-weight: 700; color: #6d28d9; text-transform: uppercase;">⏱️ Geri Dönüş Süresi</span>
                        <div id="roiPaybackDisplay" style="font-size: 18px; font-weight: 900; color: #5b21b6; margin-top: 2px;">-</div>
                        <span style="font-size: 10.5px; color: #7c3aed;">Yatırımın kendini amorti ettiği süre</span>
                    </div>
                    <div>
                        <span style="font-size: 11px; font-weight: 700; color: #047857; text-transform: uppercase;">📈 Yıllık ROI Oranı</span>
                        <div id="roiPercentDisplay" style="font-size: 18px; font-weight: 900; color: #065f46; margin-top: 2px;">-%</div>
                        <span style="font-size: 10.5px; color: #059669;">Yıllık net getiri oranı</span>
                    </div>
                    <div>
                        <span style="font-size: 11px; font-weight: 700; color: #1e40af; text-transform: uppercase;">💵 Kümülatif Net Katkı</span>
                        <div id="roiNetBenefitDisplay" style="font-size: 18px; font-weight: 900; color: #1e3a8a; margin-top: 2px;">-</div>
                        <span style="font-size: 10.5px; color: #2563eb;" id="roiHorizonText">3 yıllık net finansal getiri</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. ARAÇ: KRİTİK DIŞ BAĞIMLILIKLAR & VARSAYIMLAR -->
        <div class="scope-tool-card" id="toolCard_dependencies" style="display: none; background: #ffffff; border: 1.5px solid #fecdd3; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 6px rgba(225,29,72,0.04);">
            <div style="background: #fff1f2; padding: 10px 16px; border-bottom: 1px solid #fecdd3; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 16px;">🔗</span>
                    <strong style="font-size: 13px; color: #be123c;">Kritik Dış Bağımlılıklar & Varsayımlar</strong>
                    <span class="badge" style="background: #ffe4e6; color: #e11d48; font-size: 10.5px;">Dış Kilometre Taşları</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button type="button" class="btn btn-xs btn-outline-danger" onclick="addDependencyRow()" style="font-size: 11px; padding: 3px 9px; border-radius: 5px;">
                        + Yeni Bağımlılık Ekle
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-danger" onclick="deactivateScopeTool('dependencies')" style="font-size: 11px; padding: 3px 8px; border-radius: 5px;" title="Bu aracı projeden kaldır">
                        🗑️ Aracı Kaldır
                    </button>
                </div>
            </div>
            <div style="padding: 16px;">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 12.5px;">
                        <thead style="background: #f8fafc; color: #475569; font-weight: 700; font-size: 11.5px;">
                            <tr>
                                <th style="min-width: 220px;">Bağımlılık / Varsayım Tanımı <span class="text-danger">*</span></th>
                                <th style="width: 170px;">İlgili Dış Taraf / Tedarikçi</th>
                                <th style="width: 130px;">Kritiklik Seviyesi</th>
                                <th style="width: 135px;">Hedef Tarih</th>
                                <th style="width: 45px; text-align: center;">Sil</th>
                            </tr>
                        </thead>
                        <tbody id="dependencyRowsTableBody">
                            <!-- Dinamik Bağımlılık Satırları -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 6. ARAÇ: ÖZEL PARAMETRELER / ALANLAR -->
        <div class="scope-tool-card" id="toolCard_custom_fields" style="display: none; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 6px rgba(15,23,42,0.04);">
            <div style="background: #f8fafc; padding: 10px 16px; border-bottom: 1px solid #cbd5e1; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 16px;">🧩</span>
                    <strong style="font-size: 13px; color: #334155;">Özel Parametreler / Tanımlı Alanlar (Key - Value)</strong>
                    <span class="badge" style="background: #e2e8f0; color: #475569; font-size: 10.5px;">Esnek Parametreler</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button type="button" class="btn btn-xs btn-outline-secondary" onclick="addCustomFieldRow()" style="font-size: 11px; padding: 3px 9px; border-radius: 5px;">
                        + Yeni Parametre Ekle
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-danger" onclick="deactivateScopeTool('custom_fields')" style="font-size: 11px; padding: 3px 8px; border-radius: 5px;" title="Bu aracı projeden kaldır">
                        🗑️ Aracı Kaldır
                    </button>
                </div>
            </div>
            <div style="padding: 16px;">
                <div id="customFieldsContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 10px;">
                    <!-- Dinamik Özel Alanlar -->
                </div>
            </div>
        </div>

    </div>

    <!-- FORM SUBMIT İÇİN GİZLİ JSON INPUTU -->
    <input type="hidden" name="scope_tools_json" id="scopeToolsJsonInput" value="{{ $existingToolsJson }}">
</div>

<script>
    // State Store
    let currentScopeTools = {!! $existingToolsJson !!} || {};
    if (Array.isArray(currentScopeTools)) {
        currentScopeTools = {};
    }

    const availableColors = ['#0284c7', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#64748b'];

    document.addEventListener('DOMContentLoaded', function () {
        initScopeToolsState();
    });

    function toggleToolSelectorDropdown() {
        const dd = document.getElementById('toolSelectorDropdown');
        if (!dd) return;
        const isHidden = dd.style.display === 'none' || dd.style.display === '';
        if (isHidden) {
            dd.style.display = 'block';
            // Menünün taşmasını ve ekrandan kesilmesini engelleyen dinamik hizalama
            const rect = dd.getBoundingClientRect();
            if (rect.left < 15) {
                dd.style.right = 'auto';
                dd.style.left = '0';
            } else {
                dd.style.right = '0';
                dd.style.left = 'auto';
            }
        } else {
            dd.style.display = 'none';
        }
    }

    // Dışarı tıklandığında dropdown'ı kapat
    document.addEventListener('click', function (e) {
        const btn = document.getElementById('btnToggleToolDropdown');
        const dd = document.getElementById('toolSelectorDropdown');
        if (btn && dd && !btn.contains(e.target) && !dd.contains(e.target)) {
            dd.style.display = 'none';
        }
    });

    function initScopeToolsState() {
        const toolKeys = ['budget_chart', 'kpis', 'scope_boundaries', 'roi_calculator', 'dependencies', 'custom_fields'];
        let activeCount = 0;

        toolKeys.forEach(key => {
            const toolData = currentScopeTools[key];
            const isToolActive = toolData && (
                toolData.enabled || 
                (key === 'budget_chart' && toolData.items && toolData.items.length > 0) ||
                (key === 'kpis' && toolData.items && toolData.items.length > 0) ||
                (key === 'scope_boundaries' && ((toolData.in_scope && toolData.in_scope.length > 0) || (toolData.out_scope && toolData.out_scope.length > 0))) ||
                (key === 'roi_calculator' && (toolData.annual_savings || toolData.investment_cost || toolData.payback_months)) ||
                (key === 'dependencies' && toolData.items && toolData.items.length > 0) ||
                (key === 'custom_fields' && toolData.items && toolData.items.length > 0)
            );

            if (isToolActive) {
                activateScopeTool(key, false);
                activeCount++;
            }
        });

        updateToolsSummaryBadge();
    }

    function activateScopeTool(toolKey, saveToState = true) {
        const card = document.getElementById('toolCard_' + toolKey);
        const opt = document.getElementById('toolOpt_' + toolKey);
        const placeholder = document.getElementById('noToolsPlaceholder');

        if (card) {
            card.style.display = 'block';
        }
        if (opt) {
            const checkIcon = opt.querySelector('.tool-status-icon');
            if (checkIcon) checkIcon.style.display = 'inline';
            opt.style.background = '#f8fafc';
        }
        if (placeholder) {
            placeholder.style.display = 'none';
        }

        if (saveToState) {
            if (!currentScopeTools[toolKey]) {
                currentScopeTools[toolKey] = { enabled: true };
            } else {
                currentScopeTools[toolKey].enabled = true;
            }

            // İlk açılışta boş şablon satırları ekle
            if (toolKey === 'budget_chart' && (!currentScopeTools.budget_chart.items || currentScopeTools.budget_chart.items.length === 0)) {
                addBudgetChartRow('Ekipman & Altyapı', 0);
                addBudgetChartRow('İşçilik & Mühendislik', 0);
            } else if (toolKey === 'kpis' && (!currentScopeTools.kpis.items || currentScopeTools.kpis.items.length === 0)) {
                addKpiRow('', '', '', '', '%');
            } else if (toolKey === 'roi_calculator') {
                pullInvestmentFromPlannedBudget();
            } else if (toolKey === 'dependencies' && (!currentScopeTools.dependencies.items || currentScopeTools.dependencies.items.length === 0)) {
                addDependencyRow('', '', 'medium', '');
            } else if (toolKey === 'custom_fields' && (!currentScopeTools.custom_fields.items || currentScopeTools.custom_fields.items.length === 0)) {
                addCustomFieldRow('Tesis / Hat No', '');
            }

            syncScopeToolsJson();
        } else {
            // Var olan verileri forma bas
            populateToolForm(toolKey);
        }

        updateToolsSummaryBadge();
        const dd = document.getElementById('toolSelectorDropdown');
        if (dd) dd.style.display = 'none';
    }

    function deactivateScopeTool(toolKey) {
        if (!confirm('Bu aracı ve içeriğini kaldırmak istediğinizden emin misiniz?')) {
            return;
        }

        const card = document.getElementById('toolCard_' + toolKey);
        const opt = document.getElementById('toolOpt_' + toolKey);

        if (card) {
            card.style.display = 'none';
        }
        if (opt) {
            const checkIcon = opt.querySelector('.tool-status-icon');
            if (checkIcon) checkIcon.style.display = 'none';
            opt.style.background = 'transparent';
        }

        delete currentScopeTools[toolKey];
        syncScopeToolsJson();
        updateToolsSummaryBadge();
    }

    function updateToolsSummaryBadge() {
        const keys = ['budget_chart', 'kpis', 'scope_boundaries', 'roi_calculator', 'dependencies', 'custom_fields'];
        let count = 0;
        keys.forEach(k => {
            const c = document.getElementById('toolCard_' + k);
            if (c && c.style.display !== 'none') count++;
        });

        const badge = document.getElementById('activeToolsCounterBadge');
        if (badge) {
            badge.innerText = count + ' Araç Aktif';
            badge.style.background = count > 0 ? '#e0f2fe' : '#f1f5f9';
            badge.style.color = count > 0 ? '#0284c7' : '#64748b';
        }

        const placeholder = document.getElementById('noToolsPlaceholder');
        if (placeholder) {
            placeholder.style.display = count === 0 ? 'block' : 'none';
        }
    }

    function syncScopeToolsJson() {
        const input = document.getElementById('scopeToolsJsonInput');
        if (input) {
            input.value = JSON.stringify(currentScopeTools);
        }
    }

    /* -------------------------------------------------------------
       1. BÜTÇE DAĞILIM GRAFİĞİ METOTLARI (DONUT SVG GRAFİK)
       ------------------------------------------------------------- */
    function addBudgetChartRow(label = '', amount = 0) {
        const container = document.getElementById('budgetChartRowsContainer');
        if (!container) return;

        const rowId = 'bcr_' + Date.now() + '_' + Math.random().toString(36).substr(2, 4);
        const div = document.createElement('div');
        div.id = rowId;
        div.style.cssText = 'display: flex; gap: 8px; align-items: center;';
        div.innerHTML = `
            <input type="text" class="form-control form-control-sm bcr-label" value="${escapeHtml(label)}" placeholder="Kategori adı (örn: Makine & Donanım)" oninput="updateBudgetChartPreview()" style="flex: 1.4;">
            <input type="number" step="0.01" class="form-control form-control-sm bcr-amount" value="${amount > 0 ? amount : ''}" placeholder="0.00" oninput="updateBudgetChartPreview()" style="flex: 1;">
            <button type="button" class="btn btn-xs btn-outline-danger" onclick="document.getElementById('${rowId}').remove(); updateBudgetChartPreview();" style="padding: 3px 7px;">✕</button>
        `;
        container.appendChild(div);
        updateBudgetChartPreview();
    }

    function syncBudgetChartFromDetailedItems() {
        const rows = document.querySelectorAll('#budgetItemsTbody tr');
        if (!rows || rows.length === 0) {
            alert('Ayrıntılı bütçe tablosunda kayıtlı kalem bulunamadı.');
            return;
        }

        const container = document.getElementById('budgetChartRowsContainer');
        if (container) container.innerHTML = '';

        let pulledCount = 0;
        rows.forEach(r => {
            const catSelect = r.querySelector('select[name*="category"]');
            const descInput = r.querySelector('input[name*="title"]');
            const amtInput = r.querySelector('input[name*="planned_amount"]');

            const label = (catSelect ? catSelect.value : '') || (descInput ? descInput.value : '');
            const rawAmt = amtInput ? amtInput.value.replace(/\./g, '').replace(',', '.') : 0;
            const amt = parseFloat(rawAmt) || 0;

            if (label || amt > 0) {
                addBudgetChartRow(label || 'Genel', amt);
                pulledCount++;
            }
        });

        if (pulledCount > 0) {
            updateBudgetChartPreview();
        } else {
            alert('Kalemlerden geçerli tutar tespit edilemedi.');
        }
    }

    function updateBudgetChartPreview() {
        const rows = document.querySelectorAll('#budgetChartRowsContainer > div');
        const items = [];
        let total = 0;

        rows.forEach(r => {
            const lbl = r.querySelector('.bcr-label')?.value.trim() || '';
            const amt = parseFloat(r.querySelector('.bcr-amount')?.value) || 0;
            if (lbl && amt > 0) {
                items.push({ label: lbl, amount: amt });
                total += amt;
            }
        });

        const svgContainer = document.getElementById('budgetChartSvgContainer');
        const pBar = document.getElementById('budgetChartProgressBar');
        const legList = document.getElementById('budgetChartLegendList');
        const totalBadge = document.getElementById('budgetChartTotalBadge');

        if (totalBadge) totalBadge.innerText = 'Toplam: ' + numberFormat(total);
        if (pBar) pBar.innerHTML = '';
        if (legList) legList.innerHTML = '';
        if (svgContainer) svgContainer.innerHTML = '';

        if (items.length === 0 || total === 0) {
            if (svgContainer) {
                svgContainer.innerHTML = `
                    <div style="text-align: center; color: #94a3b8; font-size: 11px;">
                        <div style="font-size: 26px; margin-bottom: 2px;">🍩</div>
                        Kalem girildiğinde grafik çizilir
                    </div>
                `;
            }
            if (pBar) pBar.innerHTML = '<div style="width: 100%; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #94a3b8;">Veri yok</div>';
            currentScopeTools.budget_chart = { enabled: true, items: [], total: 0 };
            syncScopeToolsJson();
            return;
        }

        // SVG Donut Chart Oluştur
        const radius = 48;
        const circ = 2 * Math.PI * radius; // ~301.59
        let accumulatedOffset = 0;
        let circlesHtml = '';

        items.forEach((item, idx) => {
            const pct = (item.amount / total) * 100;
            const dash = (pct / 100) * circ;
            const color = availableColors[idx % availableColors.length];

            circlesHtml += `
                <circle cx="70" cy="70" r="${radius}" fill="transparent"
                        stroke="${color}" stroke-width="18"
                        stroke-dasharray="${dash} ${circ}"
                        stroke-dashoffset="-${accumulatedOffset}"
                        style="transition: all 0.3s ease-out; cursor: pointer;">
                    <title>${escapeHtml(item.label)}: %${pct.toFixed(1)} (${numberFormat(item.amount)})</title>
                </circle>
            `;
            accumulatedOffset += dash;

            // Progress bar segment
            if (pBar) {
                const seg = document.createElement('div');
                seg.style.cssText = `width: ${pct.toFixed(1)}%; background: ${color}; height: 100%; transition: width 0.3s;`;
                seg.title = `${item.label}: %${pct.toFixed(1)}`;
                pBar.appendChild(seg);
            }

            // Legend item
            if (legList) {
                const leg = document.createElement('div');
                leg.style.cssText = 'display: flex; justify-content: space-between; align-items: center; padding: 2px 0;';
                leg.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 6px; overflow: hidden;">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: ${color}; flex-shrink: 0;"></span>
                        <span style="font-weight: 600; color: #334155; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${escapeHtml(item.label)}">${escapeHtml(item.label)}</span>
                    </div>
                    <div style="font-weight: 750; color: #0f172a; white-space: nowrap; margin-left: 6px;">
                        %${pct.toFixed(1)} <span style="font-size: 10px; color: #64748b; font-weight: normal;">(${numberFormat(item.amount)})</span>
                    </div>
                `;
                legList.appendChild(leg);
            }
        });

        if (svgContainer) {
            svgContainer.innerHTML = `
                <svg width="130" height="130" viewBox="0 0 140 140" style="transform: rotate(-90deg);">
                    <circle cx="70" cy="70" r="${radius}" fill="transparent" stroke="#f1f5f9" stroke-width="18" />
                    ${circlesHtml}
                </svg>
            `;
        }

        currentScopeTools.budget_chart = { enabled: true, items: items, total: total };
        syncScopeToolsJson();
    }

    /* -------------------------------------------------------------
       2. SMART HEDEFLER & KPI METOTLARI (CANLI ÇİZİM & RADAR)
       ------------------------------------------------------------- */
    function addKpiRow(title = '', baseline = '', target = '', current = '', unit = '%') {
        const tbody = document.getElementById('kpiRowsTableBody');
        if (!tbody) return;

        const rowId = 'kpi_' + Date.now() + '_' + Math.random().toString(36).substr(2, 4);
        const tr = document.createElement('tr');
        tr.id = rowId;
        tr.innerHTML = `
            <td>
                <input type="text" class="form-control form-control-sm kpi-title" value="${escapeHtml(title)}" placeholder="örn: Hat Verimliliği (OEE)" oninput="syncKpisFromForm()">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm kpi-baseline" value="${escapeHtml(baseline)}" placeholder="örn: 65" oninput="syncKpisFromForm()">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm kpi-target" value="${escapeHtml(target)}" placeholder="örn: 85" oninput="syncKpisFromForm()">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm kpi-current" value="${escapeHtml(current)}" placeholder="örn: 75" oninput="syncKpisFromForm()">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm kpi-unit" value="${escapeHtml(unit)}" placeholder="%" oninput="syncKpisFromForm()">
            </td>
            <td class="kpi-live-gauge-cell" style="vertical-align: middle;">
                <!-- Canlı SVG Gauge Buraya Çizilecek -->
                <span style="color: #94a3b8; font-size: 11px; font-style: italic;">Hesaplanıyor...</span>
            </td>
            <td style="text-align: center; vertical-align: middle;">
                <button type="button" class="btn btn-xs btn-outline-danger" onclick="document.getElementById('${rowId}').remove(); syncKpisFromForm();" style="padding: 2px 6px;">✕</button>
            </td>
        `;
        tbody.appendChild(tr);
        syncKpisFromForm();
    }

    function syncKpisFromForm() {
        const rows = document.querySelectorAll('#kpiRowsTableBody tr');
        const items = [];
        let totalPct = 0;
        let validPctCount = 0;
        let achievedCount = 0;
        let inProgressCount = 0;

        rows.forEach(r => {
            const title = r.querySelector('.kpi-title')?.value.trim() || '';
            const baselineStr = r.querySelector('.kpi-baseline')?.value.trim() || '';
            const targetStr = r.querySelector('.kpi-target')?.value.trim() || '';
            const currentStr = r.querySelector('.kpi-current')?.value.trim() || '';
            const unit = r.querySelector('.kpi-unit')?.value.trim() || '%';
            const gaugeCell = r.querySelector('.kpi-live-gauge-cell');

            const bNum = parseFloat(baselineStr);
            const tNum = parseFloat(targetStr);
            const cNum = parseFloat(currentStr);

            let pct = null;
            if (!isNaN(tNum) && !isNaN(cNum)) {
                if (!isNaN(bNum) && tNum !== bNum) {
                    pct = Math.round(((cNum - bNum) / (tNum - bNum)) * 100);
                } else if (tNum > 0) {
                    pct = Math.round((cNum / tNum) * 100);
                }
            }

            // Canlı hücre grafiğini render et
            if (gaugeCell) {
                if (pct !== null) {
                    const clamped = Math.min(Math.max(pct, 0), 100);
                    const color = pct >= 80 ? '#10b981' : (pct >= 50 ? '#0284c7' : (pct >= 0 ? '#f59e0b' : '#ef4444'));
                    const statusText = pct >= 100 ? '✓ Hedefe Ulaşıldı' : (pct >= 50 ? 'İlerleme Var' : 'Kritik / Başlangıç');
                    const circ = 2 * Math.PI * 14; // ~87.96
                    const dash = (clamped / 100) * circ;

                    gaugeCell.innerHTML = `
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="position: relative; width: 34px; height: 34px; flex-shrink: 0;">
                                <svg width="34" height="34" viewBox="0 0 34 34" style="transform: rotate(-90deg);">
                                    <circle cx="17" cy="17" r="14" fill="transparent" stroke="#f1f5f9" stroke-width="4"></circle>
                                    <circle cx="17" cy="17" r="14" fill="transparent" stroke="${color}" stroke-width="4" stroke-dasharray="${dash} ${circ}" stroke-linecap="round" style="transition: stroke-dasharray 0.3s;"></circle>
                                </svg>
                                <span style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 850; color: ${color};">
                                    %${pct}
                                </span>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                <span style="font-size: 10px; font-weight: 800; color: ${color};">${statusText}</span>
                                <div style="background: #e2e8f0; height: 5px; width: 85px; border-radius: 3px; overflow: hidden;">
                                    <div style="width: ${clamped}%; height: 100%; background: ${color}; transition: width 0.3s;"></div>
                                </div>
                            </div>
                        </div>
                    `;

                    totalPct += pct;
                    validPctCount++;
                    if (pct >= 80) achievedCount++;
                    else inProgressCount++;
                } else if (!isNaN(tNum)) {
                    gaugeCell.innerHTML = `
                        <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 10px; font-weight: 600;">
                            Hedef: ${tNum} ${unit} (Veri bekleniyor)
                        </span>
                    `;
                } else {
                    gaugeCell.innerHTML = `
                        <span style="color: #94a3b8; font-size: 10.5px; font-style: italic;">
                            Hedef giriniz
                        </span>
                    `;
                }
            }

            if (title || targetStr) {
                items.push({
                    title: title || 'Hedef Metrik',
                    baseline: baselineStr,
                    target: targetStr,
                    current: currentStr,
                    unit: unit
                });
            }
        });

        // Üst KPI Radar Özetini Güncelle
        const avgPct = validPctCount > 0 ? Math.round(totalPct / validPctCount) : 0;
        const clampedAvg = Math.min(Math.max(avgPct, 0), 100);
        const radarColor = avgPct >= 80 ? '#10b981' : (avgPct >= 50 ? '#0284c7' : '#f59e0b');

        const gaugeCircle = document.getElementById('kpiRadarGaugeCircle');
        const gaugeText = document.getElementById('kpiRadarGaugeText');
        const countBadge = document.getElementById('kpiCountBadge');
        const achBadge = document.getElementById('kpiAchievedBadge');
        const progBadge = document.getElementById('kpiProgressBadge');
        const subText = document.getElementById('kpiRadarSubtitle');

        if (gaugeCircle) {
            const circ18 = 2 * Math.PI * 18; // ~113.1
            const dash18 = (clampedAvg / 100) * circ18;
            gaugeCircle.setAttribute('stroke-dasharray', `${dash18} ${circ18}`);
            gaugeCircle.setAttribute('stroke', radarColor);
        }
        if (gaugeText) {
            gaugeText.innerText = `%${avgPct}`;
            gaugeText.style.color = radarColor;
        }
        if (countBadge) countBadge.innerText = `${items.length} KPI`;
        if (achBadge) achBadge.innerText = `${achievedCount} Hedefte`;
        if (progBadge) progBadge.innerText = `${inProgressCount} İlerlemede`;
        if (subText) {
            subText.innerText = validPctCount > 0 
                ? `${validPctCount} metrik üzerinden ortalama %${avgPct} gerçekleşme sağlandı.`
                : 'Metrik değerleri girildikçe başarı radarı otomatik hesaplanır.';
        }

        currentScopeTools.kpis = { enabled: true, items: items, average_pct: avgPct };
        syncScopeToolsJson();
    }

    /* -------------------------------------------------------------
       3. KAPSAM SINIRLARI METOTLARI
       ------------------------------------------------------------- */
    function addBoundaryItem(type) {
        const input = document.getElementById(type === 'in' ? 'inScopeInput' : 'outScopeInput');
        if (!input) return;

        const val = input.value.trim();
        if (!val) return;

        if (!currentScopeTools.scope_boundaries) {
            currentScopeTools.scope_boundaries = { enabled: true, in_scope: [], out_scope: [] };
        }
        if (!currentScopeTools.scope_boundaries.in_scope) currentScopeTools.scope_boundaries.in_scope = [];
        if (!currentScopeTools.scope_boundaries.out_scope) currentScopeTools.scope_boundaries.out_scope = [];

        if (type === 'in') {
            currentScopeTools.scope_boundaries.in_scope.push(val);
        } else {
            currentScopeTools.scope_boundaries.out_scope.push(val);
        }

        input.value = '';
        renderBoundaryItems();
        syncScopeToolsJson();
    }

    function removeBoundaryItem(type, index) {
        if (!currentScopeTools.scope_boundaries) return;
        const arr = type === 'in' ? currentScopeTools.scope_boundaries.in_scope : currentScopeTools.scope_boundaries.out_scope;
        if (arr && arr[index] !== undefined) {
            arr.splice(index, 1);
            renderBoundaryItems();
            syncScopeToolsJson();
        }
    }

    function renderBoundaryItems() {
        const inContainer = document.getElementById('inScopeListContainer');
        const outContainer = document.getElementById('outScopeListContainer');
        const inArr = currentScopeTools.scope_boundaries?.in_scope || [];
        const outArr = currentScopeTools.scope_boundaries?.out_scope || [];

        if (inContainer) {
            inContainer.innerHTML = inArr.map((item, idx) => `
                <div style="background: #ffffff; border: 1px solid #bbf7d0; border-radius: 6px; padding: 5px 10px; display: flex; justify-content: space-between; align-items: center; font-size: 12px;">
                    <span style="color: #166534; font-weight: 600;">✓ ${escapeHtml(item)}</span>
                    <a href="javascript:void(0)" onclick="removeBoundaryItem('in', ${idx})" style="color: #94a3b8; text-decoration: none; font-weight: 800; font-size: 11px;">✕</a>
                </div>
            `).join('');
        }

        if (outContainer) {
            outContainer.innerHTML = outArr.map((item, idx) => `
                <div style="background: #ffffff; border: 1px solid #fecaca; border-radius: 6px; padding: 5px 10px; display: flex; justify-content: space-between; align-items: center; font-size: 12px;">
                    <span style="color: #991b1b; font-weight: 600;">✕ ${escapeHtml(item)}</span>
                    <a href="javascript:void(0)" onclick="removeBoundaryItem('out', ${idx})" style="color: #94a3b8; text-decoration: none; font-weight: 800; font-size: 11px;">✕</a>
                </div>
            `).join('');
        }

        const inBadge = document.getElementById('inScopeCounter');
        const outBadge = document.getElementById('outScopeCounter');
        if (inBadge) inBadge.innerText = inArr.length + ' Madde';
        if (outBadge) outBadge.innerText = outArr.length + ' Madde';
    }

    /* -------------------------------------------------------------
       4. ROI, TASARRUF & GERİ DÖNÜŞ METOTLARI
       ------------------------------------------------------------- */
    function pullInvestmentFromPlannedBudget() {
        const pBudgetInput = document.getElementById('plannedBudgetInput');
        let val = 0;
        if (pBudgetInput && pBudgetInput.value) {
            val = parseFloat(pBudgetInput.value.replace(/\./g, '').replace(',', '.')) || 0;
        }
        const invInput = document.getElementById('roiInvestmentCost');
        if (invInput && val > 0) {
            invInput.value = val;
            calculateRoiMetrics();
        }
    }

    function calculateRoiMetrics() {
        const cost = parseFloat(document.getElementById('roiInvestmentCost')?.value) || 0;
        const savings = parseFloat(document.getElementById('roiAnnualSavings')?.value) || 0;
        const horizon = parseInt(document.getElementById('roiHorizonYears')?.value) || 3;

        let paybackMonths = 0;
        let paybackYears = 0;
        let roiPercent = 0;
        let netBenefit = 0;

        if (cost > 0 && savings > 0) {
            paybackYears = (cost / savings);
            paybackMonths = Math.round(paybackYears * 12);
            roiPercent = ((savings / cost) * 100).toFixed(1);
            netBenefit = (savings * horizon) - cost;
        }

        const pbDisplay = document.getElementById('roiPaybackDisplay');
        const roiDisplay = document.getElementById('roiPercentDisplay');
        const nbDisplay = document.getElementById('roiNetBenefitDisplay');
        const hText = document.getElementById('roiHorizonText');

        if (pbDisplay) {
            if (paybackMonths > 0) {
                pbDisplay.innerText = `${paybackYears.toFixed(1)} Yıl (${paybackMonths} Ay)`;
            } else {
                pbDisplay.innerText = '-';
            }
        }
        if (roiDisplay) {
            roiDisplay.innerText = roiPercent > 0 ? `%${roiPercent}` : '-%';
        }
        if (nbDisplay) {
            if (cost > 0 && savings > 0) {
                nbDisplay.innerText = (netBenefit >= 0 ? '+' : '') + numberFormat(netBenefit);
                nbDisplay.style.color = netBenefit >= 0 ? '#1e3a8a' : '#dc2626';
            } else {
                nbDisplay.innerText = '-';
            }
        }
        if (hText) {
            hText.innerText = `${horizon} yıllık net finansal getiri`;
        }

        currentScopeTools.roi_calculator = {
            enabled: true,
            investment_cost: cost,
            annual_savings: savings,
            horizon_years: horizon,
            payback_years: paybackYears.toFixed(1),
            payback_months: paybackMonths,
            roi_percentage: roiPercent,
            net_benefit: netBenefit
        };
        syncScopeToolsJson();
    }

    /* -------------------------------------------------------------
       5. KRİTİK DIŞ BAĞIMLILIKLAR METOTLARI
       ------------------------------------------------------------- */
    function addDependencyRow(title = '', owner = '', criticalLevel = 'medium', targetDate = '') {
        const tbody = document.getElementById('dependencyRowsTableBody');
        if (!tbody) return;

        const rowId = 'dep_' + Date.now() + '_' + Math.random().toString(36).substr(2, 4);
        const tr = document.createElement('tr');
        tr.id = rowId;
        tr.innerHTML = `
            <td>
                <input type="text" class="form-control form-control-sm dep-title" value="${escapeHtml(title)}" placeholder="örn: Makine Teslimi / İzin Onayı" oninput="syncDependenciesFromForm()">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm dep-owner" value="${escapeHtml(owner)}" placeholder="örn: Krones AG / Bakanlık" oninput="syncDependenciesFromForm()">
            </td>
            <td>
                <select class="form-control form-control-sm dep-level" onchange="syncDependenciesFromForm()">
                    <option value="low" ${criticalLevel === 'low' ? 'selected' : ''}>Düşük</option>
                    <option value="medium" ${criticalLevel === 'medium' ? 'selected' : ''}>Orta</option>
                    <option value="high" ${criticalLevel === 'high' ? 'selected' : ''}>Yüksek</option>
                    <option value="critical" ${criticalLevel === 'critical' ? 'selected' : ''}>Kritik / Bloke</option>
                </select>
            </td>
            <td>
                <input type="date" class="form-control form-control-sm dep-date" value="${targetDate || ''}" onchange="syncDependenciesFromForm()">
            </td>
            <td style="text-align: center;">
                <button type="button" class="btn btn-xs btn-outline-danger" onclick="document.getElementById('${rowId}').remove(); syncDependenciesFromForm();" style="padding: 2px 6px;">✕</button>
            </td>
        `;
        tbody.appendChild(tr);
        syncDependenciesFromForm();
    }

    function syncDependenciesFromForm() {
        const rows = document.querySelectorAll('#dependencyRowsTableBody tr');
        const items = [];
        rows.forEach(r => {
            const title = r.querySelector('.dep-title')?.value.trim();
            if (title) {
                items.push({
                    title: title,
                    owner: r.querySelector('.dep-owner')?.value.trim() || '',
                    critical_level: r.querySelector('.dep-level')?.value || 'medium',
                    target_date: r.querySelector('.dep-date')?.value || ''
                });
            }
        });

        currentScopeTools.dependencies = { enabled: true, items: items };
        syncScopeToolsJson();
    }

    /* -------------------------------------------------------------
       6. ÖZEL PARAMETRELER METOTLARI
       ------------------------------------------------------------- */
    function addCustomFieldRow(label = '', value = '') {
        const container = document.getElementById('customFieldsContainer');
        if (!container) return;

        const rowId = 'cf_' + Date.now() + '_' + Math.random().toString(36).substr(2, 4);
        const div = document.createElement('div');
        div.id = rowId;
        div.style.cssText = 'background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px; display: flex; gap: 8px; align-items: center;';
        div.innerHTML = `
            <input type="text" class="form-control form-control-sm cf-label" value="${escapeHtml(label)}" placeholder="Parametre Adı" oninput="syncCustomFieldsFromForm()" style="flex: 1; font-weight: 600;">
            <input type="text" class="form-control form-control-sm cf-value" value="${escapeHtml(value)}" placeholder="Değer" oninput="syncCustomFieldsFromForm()" style="flex: 1.2;">
            <button type="button" class="btn btn-xs btn-outline-danger" onclick="document.getElementById('${rowId}').remove(); syncCustomFieldsFromForm();" style="padding: 3px 7px;">✕</button>
        `;
        container.appendChild(div);
        syncCustomFieldsFromForm();
    }

    function syncCustomFieldsFromForm() {
        const rows = document.querySelectorAll('#customFieldsContainer > div');
        const items = [];
        rows.forEach(r => {
            const lbl = r.querySelector('.cf-label')?.value.trim();
            const val = r.querySelector('.cf-value')?.value.trim();
            if (lbl || val) {
                items.push({ label: lbl || 'Parametre', value: val || '' });
            }
        });

        currentScopeTools.custom_fields = { enabled: true, items: items };
        syncScopeToolsJson();
    }

    /* -------------------------------------------------------------
       VAR OLAN VERİLERİ FORMA AKTARMA
       ------------------------------------------------------------- */
    function populateToolForm(toolKey) {
        const tData = currentScopeTools[toolKey];
        if (!tData) return;

        if (toolKey === 'budget_chart' && tData.items) {
            const container = document.getElementById('budgetChartRowsContainer');
            if (container) container.innerHTML = '';
            tData.items.forEach(it => addBudgetChartRow(it.label, it.amount));
        } else if (toolKey === 'kpis' && tData.items) {
            const tbody = document.getElementById('kpiRowsTableBody');
            if (tbody) tbody.innerHTML = '';
            tData.items.forEach(it => addKpiRow(it.title, it.baseline, it.target, it.current, it.unit));
        } else if (toolKey === 'scope_boundaries') {
            renderBoundaryItems();
        } else if (toolKey === 'roi_calculator') {
            if (document.getElementById('roiInvestmentCost')) document.getElementById('roiInvestmentCost').value = tData.investment_cost || '';
            if (document.getElementById('roiAnnualSavings')) document.getElementById('roiAnnualSavings').value = tData.annual_savings || '';
            if (document.getElementById('roiHorizonYears')) document.getElementById('roiHorizonYears').value = tData.horizon_years || '3';
            calculateRoiMetrics();
        } else if (toolKey === 'dependencies' && tData.items) {
            const tbody = document.getElementById('dependencyRowsTableBody');
            if (tbody) tbody.innerHTML = '';
            tData.items.forEach(it => addDependencyRow(it.title, it.owner, it.critical_level, it.target_date));
        } else if (toolKey === 'custom_fields' && tData.items) {
            const container = document.getElementById('customFieldsContainer');
            if (container) container.innerHTML = '';
            tData.items.forEach(it => addCustomFieldRow(it.label, it.value));
        }
    }

    // Helper functions
    function escapeHtml(text) {
        if (!text) return '';
        return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function numberFormat(num) {
        return new Intl.NumberFormat('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
    }
</script>
