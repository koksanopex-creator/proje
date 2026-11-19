@extends('layouts.app')

@section('title', 'BOM & Bütçe Yönetimi')
@section('page_title', 'BOM (Malzeme Ağacı) & Bütçe Yönetimi')
@section('page_subtitle', 'Endüstriyel malzeme listesi, CAPEX/OPEX ve hakediş analizi')

@section('content')
    <!-- Proje Seçim ve Üst Kontrol Kartı -->
    <div class="card mb-4" style="border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.04); border: 1px solid #e2e8f0;">
        <div class="card-body" style="padding: 16px 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                <label style="font-size: 13.5px; font-weight: 700; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 6px;">
                    <span>📁</span> Proje Seçiniz:
                </label>
                <select onchange="window.location.href='?project_id=' + this.value" class="form-control" style="width: 440px; max-width: 100%; height: 42px; font-weight: 600; font-size: 13px; border-radius: 8px; border: 1.5px solid #cbd5e1; background: #ffffff;">
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ $project && $project->id == $p->id ? 'selected' : '' }}>
                            {{ $p->code }} - {{ $p->title }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if($project)
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="font-size: 12px; font-weight: 700; color: #64748b;">Durum:</span>
                        <x-status-badge :status="$project->status" />
                    </div>
                    <a href="{{ route('projects.show', $project->id) }}" class="badge" style="background: #f8fafc; color: #0284c7; border: 1.5px solid #cbd5e1; padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s ease; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.03);" onmouseover="this.style.background='#e0f2fe'; this.style.borderColor='#0284c7'; this.style.color='#0369a1';" onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#cbd5e1'; this.style.color='#0284c7';" title="Projeyi Görüntüle ({{ $project->title }})">
                        <span>📁</span> Kod: {{ $project->code }} <span style="font-size: 10px; color: #64748b;">↗</span>
                    </a>
                </div>
            @endif
        </div>
    </div>

    @if($project)
        @php
            $totalBomCost = $project->boms->sum('total_price');
            $totalPlannedBudget = $project->planned_budget ?: $project->budgetItems->sum('planned_amount');
            $totalActualSpent = $project->budgetItems->sum('actual_spent');
            $budgetVariance = $totalPlannedBudget - $totalActualSpent;
            $burnPercent = $totalPlannedBudget > 0 ? min(100, round(($totalActualSpent / $totalPlannedBudget) * 100, 1)) : 0;
        @endphp

        <!-- Finansal ve Maliyet KPI Gösterge Kartları -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
            <!-- Kart 1: Planlanan Toplam Bütçe -->
            <div class="card" style="border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between;">
                    <span>🎯 Toplam Planlanan Bütçe</span>
                    <span style="font-size: 16px;">💼</span>
                </div>
                <div style="font-size: 22px; font-weight: 800; color: #0f172a; margin-bottom: 4px;">
                    ₺{{ number_format($totalPlannedBudget, 2, ',', '.') }}
                </div>
                <div style="font-size: 11.5px; color: #64748b;">
                    Proje onaylı ana yatırım bütçesi
                </div>
            </div>

            <!-- Kart 2: BOM Malzeme Tutarı -->
            <div class="card" style="border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between;">
                    <span>🏗️ BOM Malzeme Maliyeti</span>
                    <span style="font-size: 16px;">📦</span>
                </div>
                <div style="font-size: 22px; font-weight: 800; color: #0284c7; margin-bottom: 4px;">
                    ₺{{ number_format($totalBomCost, 2, ',', '.') }}
                </div>
                <div style="font-size: 11.5px; color: #64748b;">
                    Toplam {{ $project->boms->count() }} endüstriyel malzeme kalemi
                </div>
            </div>

            <!-- Kart 3: Fiili Harcanan Bütçe -->
            <div class="card" style="border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between;">
                    <span>💸 Fiili Harcanan Tutar</span>
                    <span style="font-size: 16px;">🧾</span>
                </div>
                <div style="font-size: 22px; font-weight: 800; color: {{ $totalActualSpent > $totalPlannedBudget ? '#dc2626' : '#0f172a' }}; margin-bottom: 4px;">
                    ₺{{ number_format($totalActualSpent, 2, ',', '.') }}
                </div>
                <div style="font-size: 11.5px; color: #64748b;">
                    Hakediş & gerçekleşen harcama: <strong>%{{ $burnPercent }}</strong>
                </div>
            </div>

            <!-- Kart 4: Kalan Bütçe / Sapma -->
            <div class="card" style="border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between;">
                    <span>⚖️ Net Kalan Bütçe</span>
                    <span style="font-size: 16px;">{{ $budgetVariance >= 0 ? '✅' : '⚠️' }}</span>
                </div>
                <div style="font-size: 22px; font-weight: 800; color: {{ $budgetVariance >= 0 ? '#16a34a' : '#dc2626' }}; margin-bottom: 4px;">
                    ₺{{ number_format($budgetVariance, 2, ',', '.') }}
                </div>
                <div style="font-size: 11.5px; color: {{ $budgetVariance >= 0 ? '#166534' : '#991b1b' }};">
                    {{ $budgetVariance >= 0 ? '✓ Bütçe limitleri dahilinde' : '⚠️ Bütçe aşımı gerçekleşti!' }}
                </div>
            </div>
        </div>

        <!-- 1. GENİŞ TABLO: BOM MALZEME LİSTESİ (ALT ALTA FERAH YERLEŞİM) -->
        <div class="card mb-4" style="border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.04); overflow: hidden;">
            <div class="card-header" style="background: #ffffff; padding: 16px 24px; border-bottom: 1.5px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                        📦
                    </div>
                    <div>
                        <h3 class="card-title" style="margin: 0; font-size: 15px; font-weight: 700; color: #0f172a;">
                            BOM (Malzeme Ağacı) Listesi
                        </h3>
                        <p style="margin: 0; font-size: 12px; color: #64748b;">Mekanik, inşaat ve otomasyon kalemleri detay tablosu</p>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 12px; font-weight: 700; padding: 6px 12px; border-radius: 8px;">
                        {{ $project->boms->count() }} Malzeme Kalemi
                    </span>
                    <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 12px; font-weight: 700; padding: 6px 12px; border-radius: 8px;">
                        Toplam: ₺{{ number_format($totalBomCost, 2, ',', '.') }}
                    </span>
                </div>
            </div>
            <div class="card-body" style="padding: 0; overflow-x: auto;">
                <table class="app-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 12px 20px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase;">Malzeme Adı & Kod</th>
                            <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase;">Kategori</th>
                            <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; text-align: right;">Miktar</th>
                            <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; text-align: right;">Birim Fiyat</th>
                            <th style="padding: 12px 20px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; text-align: right;">Toplam Tutar</th>
                            <th style="padding: 12px 20px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; text-align: center;">Tedarik Durumu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($project->boms as $b)
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 14px 20px;">
                                    <div style="font-weight: 700; color: #0f172a; font-size: 13.5px;">{{ $b->item_name }}</div>
                                    <div style="font-size: 11.5px; color: #64748b; margin-top: 2px; display: flex; align-items: center; gap: 8px;">
                                        <code style="background: #f1f5f9; color: #334155; padding: 1px 6px; border-radius: 4px; font-size: 11px;">{{ $b->item_code ?? 'MLZ-' . $b->id }}</code>
                                        @if($b->supplier)
                                            <span>• Tedarikçi: <strong>{{ $b->supplier }}</strong></span>
                                        @endif
                                    </div>
                                </td>
                                <td style="padding: 14px 16px;">
                                    @php $catMeta = $b->getCategoryMeta(); @endphp
                                    <span class="badge" style="background: {{ $catMeta['bg'] }}; color: {{ $catMeta['color'] }}; border: 1px solid {{ $catMeta['border'] }}; font-size: 11.5px; font-weight: 700; padding: 4px 10px; border-radius: 6px; display: inline-flex; align-items: center; gap: 5px; letter-spacing: 0.2px;">
                                        <span>{{ $catMeta['icon'] }}</span>
                                        <span>{{ $catMeta['name'] }}</span>
                                    </span>
                                </td>
                                <td style="padding: 14px 16px; text-align: right; font-weight: 600; color: #1e293b; font-size: 13px;">
                                    {{ number_format($b->quantity, 0, ',', '.') }} <span style="font-size: 11.5px; color: #64748b;">{{ $b->unit }}</span>
                                </td>
                                <td style="padding: 14px 16px; text-align: right; color: #475569; font-size: 13px;">
                                    ₺{{ number_format($b->unit_price, 2, ',', '.') }}
                                </td>
                                <td style="padding: 14px 20px; text-align: right; font-weight: 800; color: #0f172a; font-size: 14px;">
                                    ₺{{ number_format($b->total_price, 2, ',', '.') }}
                                </td>
                                <td style="padding: 14px 20px; text-align: center;">
                                    <x-status-badge :status="$b->status" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 36px 20px; color: #94a3b8; font-size: 13.5px;">
                                    📦 Bu projeye henüz tanımlanmış BOM malzeme kaydı bulunmamaktadır.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. GENİŞ TABLO: CAPEX / OPEX HARCAMA KALEMLERİ (ALT ALTA FERAH YERLEŞİM) -->
        <div class="card mb-4" style="border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.04); overflow: hidden;">
            <div class="card-header" style="background: #ffffff; padding: 16px 24px; border-bottom: 1.5px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                        📊
                    </div>
                    <div>
                        <h3 class="card-title" style="margin: 0; font-size: 15px; font-weight: 700; color: #0f172a;">
                            CAPEX / OPEX Harcama Kalemleri & Hakediş Analizi
                        </h3>
                        <p style="margin: 0; font-size: 12px; color: #64748b;">Planlanan bütçe, gerçekleşen harcamalar ve sapma takibi</p>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 12px; font-weight: 700; padding: 6px 12px; border-radius: 8px;">
                        {{ $project->budgetItems->count() }} Harcama Kalemi
                    </span>
                    <span class="badge" style="background: #f0fdf4; color: #166534; font-size: 12px; font-weight: 700; padding: 6px 12px; border-radius: 8px;">
                        Fiili Toplam: ₺{{ number_format($totalActualSpent, 2, ',', '.') }}
                    </span>
                </div>
            </div>
            <div class="card-body" style="padding: 0; overflow-x: auto;">
                <table class="app-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 12px 20px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase;">Kalem Başlığı</th>
                            <th style="padding: 12px 16px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; text-align: center;">Yatırım Türü</th>
                            <th style="padding: 12px 20px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; text-align: right;">Planlanan Bütçe</th>
                            <th style="padding: 12px 20px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; text-align: right;">Fiili Harcanan</th>
                            <th style="padding: 12px 20px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; text-align: right;">Bütçe Sapması (Kalan)</th>
                            <th style="padding: 12px 20px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; text-align: center; width: 140px;">Gerçekleşme</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($project->budgetItems as $bi)
                            @php
                                $itemVariance = $bi->planned_amount - $bi->actual_spent;
                                $itemPct = $bi->planned_amount > 0 ? min(100, round(($bi->actual_spent / $bi->planned_amount) * 100)) : 0;
                            @endphp
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 14px 20px;">
                                    <div style="font-weight: 700; color: #0f172a; font-size: 13.5px;">{{ $bi->title }}</div>
                                    @if($bi->invoice_no || $bi->invoice_date)
                                        <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                            <span>Fatura: {{ $bi->invoice_no ?? '-' }}</span> • <span>{{ $bi->invoice_date ? $bi->invoice_date->format('d.m.Y') : '-' }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td style="padding: 14px 16px; text-align: center;">
                                    <span style="font-size: 11px; font-weight: 800; padding: 3px 8px; border-radius: 6px; letter-spacing: 0.5px; background: {{ strtoupper($bi->budget_type) === 'CAPEX' ? '#e0f2fe; color: #0369a1; border: 1px solid #bae6fd;' : '#fef3c7; color: #92400e; border: 1px solid #fde68a;' }}">
                                        {{ strtoupper($bi->budget_type) }}
                                    </span>
                                </td>
                                <td style="padding: 14px 20px; text-align: right; font-weight: 600; color: #334155; font-size: 13px;">
                                    ₺{{ number_format($bi->planned_amount, 2, ',', '.') }}
                                </td>
                                <td style="padding: 14px 20px; text-align: right; font-weight: 700; color: #0f172a; font-size: 13.5px;">
                                    ₺{{ number_format($bi->actual_spent, 2, ',', '.') }}
                                </td>
                                <td style="padding: 14px 20px; text-align: right; font-weight: 800; font-size: 13.5px; color: {{ $itemVariance >= 0 ? '#16a34a' : '#dc2626' }};">
                                    {{ $itemVariance >= 0 ? '+' : '' }}₺{{ number_format($itemVariance, 2, ',', '.') }}
                                </td>
                                <td style="padding: 14px 20px; text-align: center;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="flex: 1; height: 7px; background: #e2e8f0; border-radius: 10px; overflow: hidden;">
                                            <div style="width: {{ $itemPct }}%; height: 100%; background: {{ $itemPct > 100 ? '#dc2626' : ($itemPct > 80 ? '#f59e0b' : '#0284c7') }}; border-radius: 10px;"></div>
                                        </div>
                                        <span style="font-size: 11.5px; font-weight: 700; color: #475569; width: 34px; text-align: right;">%{{ $itemPct }}</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 36px 20px; color: #94a3b8; font-size: 13.5px;">
                                    📊 Bu projeye ait kayıtlı CAPEX/OPEX harcama kalemi bulunmamaktadır.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection