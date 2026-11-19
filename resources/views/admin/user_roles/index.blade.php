@extends('layouts.app')

@section('title', 'Kullanıcı Yetki & Çoklu Rol Yönetimi - Köksan Enterprise PPM')

@section('content')
<style>
/* Matrisler Arası Hızlı Geçiş Sekmeler Çubuğu (Responsive & Wrap) */
.matrix-sister-tabs {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 8px !important;
    border-bottom: 2px solid #e2e8f0 !important;
    margin-bottom: 24px !important;
    padding-bottom: 0 !important;
    align-items: center !important;
    box-sizing: border-box !important;
    width: 100% !important;
    height: auto !important;
    min-height: 44px !important;
    overflow: visible !important;
}

.matrix-tab-link {
    display: inline-flex !important;
    align-items: center !important;
    gap: 7px !important;
    padding: 0 14px !important;
    height: 40px !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    color: #64748b !important;
    text-decoration: none !important;
    border-radius: 8px 8px 0 0 !important;
    border-bottom: 3px solid transparent !important;
    transition: all 0.2s ease !important;
    white-space: nowrap !important;
    box-sizing: border-box !important;
    line-height: 1 !important;
}
.matrix-tab-link:hover {
    color: #0284c7 !important;
    background: #f8fafc !important;
}
.matrix-tab-link.active {
    color: #0284c7 !important;
    font-weight: 750 !important;
    background: #f0f9ff !important;
    border-bottom: 3px solid #0284c7 !important;
    box-shadow: 0 -2px 6px rgba(2, 132, 199, 0.05) !important;
}
.matrix-tab-link .tab-emoji {
    font-size: 14px !important;
    line-height: 1 !important;
}

/* Header Eylem Butonları */
.matrix-header-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    height: 40px;
    padding: 0 16px;
    font-size: 13px;
    font-weight: 650;
    color: #334155;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 9px;
    text-decoration: none;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    transition: all 0.2s ease;
    white-space: nowrap;
}
.matrix-header-btn-secondary:hover {
    background: #f8fafc;
    border-color: #94a3b8;
    color: #0f172a;
}
.matrix-header-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    height: 40px;
    padding: 0 18px;
    font-size: 13px;
    font-weight: 700;
    color: #ffffff;
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
    border: 1px solid #0284c7;
    border-radius: 9px;
    text-decoration: none;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);
    transition: all 0.2s ease;
    white-space: nowrap;
}
.matrix-header-btn-primary:hover {
    background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
    box-shadow: 0 4px 10px rgba(2, 132, 199, 0.35);
    color: #ffffff;
}

/* Sayfa Başlığı */
.matrix-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 24px;
    margin-bottom: 24px;
    padding-bottom: 4px;
    flex-wrap: wrap;
}
.matrix-page-header .header-left {
    flex: 1;
    min-width: 300px;
}
.matrix-page-header .page-title {
    font-size: 26px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.025em;
    margin: 0 0 8px 0;
    display: flex;
    align-items: center;
    gap: 12px;
    line-height: 1.25;
}
.matrix-page-header .page-subtitle {
    font-size: 13.5px;
    color: #64748b;
    margin: 0;
    line-height: 1.55;
    max-width: 860px;
    font-weight: 450;
}
.matrix-page-header .header-right {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
    padding-top: 4px;
    flex-wrap: wrap;
}

/* Filtre Çipleri */
.user-filter-chip {
    padding: 6px 13px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #475569;
    cursor: pointer;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    user-select: none;
    line-height: 1.2;
}
.user-filter-chip:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
    color: #0f172a;
}
.user-filter-chip.active {
    background: #0284c7;
    border-color: #0284c7;
    color: #ffffff;
    box-shadow: 0 2px 4px rgba(2, 132, 199, 0.25);
}
.user-filter-chip.active .chip-count {
    background: rgba(255, 255, 255, 0.25);
    color: #ffffff;
}
.chip-count {
    font-size: 10.5px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 10px;
    background: #f1f5f9;
    color: #64748b;
    transition: all 0.15s;
}

/* Kullanıcı Rol Rozetleri Grubu */
.user-roles-badge-wrap {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    align-items: center;
}
.user-single-role-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 9px;
    font-size: 11.5px;
    font-weight: 700;
    border-radius: 6px;
    white-space: nowrap;
    line-height: 1.2;
    transition: all 0.15s ease;
}
.btn-quick-add-role {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 3px;
    padding: 3px 8px;
    font-size: 11px;
    font-weight: 650;
    color: #0284c7;
    background: #f0f9ff;
    border: 1px dashed #bae6fd;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.15s ease;
}
.btn-quick-add-role:hover {
    background: #e0f2fe;
    border-color: #0284c7;
    color: #0369a1;
}

/* Kullanıcı Avatar Dairesi */
.user-avatar-initial {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 14px;
    flex-shrink: 0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.12);
}

/* Dönen İkon Animasyonu */
.spin-anim {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    100% { transform: rotate(360deg); }
}

/* Rol Seçim Kartı (Modal İçi) */
.role-choice-card {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 10px;
    border: 1.5px solid #e2e8f0;
    background: #ffffff;
    cursor: pointer;
    transition: all 0.15s ease;
    user-select: none;
}
.role-choice-card:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
}
.role-choice-card.selected {
    border-color: #0284c7;
    background: #f0f9ff;
    box-shadow: 0 2px 6px rgba(2, 132, 199, 0.12);
}
.role-choice-checkbox {
    width: 18px;
    height: 18px;
    margin-top: 2px;
    cursor: pointer;
    accent-color: #0284c7;
}
</style>

<!-- BAŞLIK & ÜST AKSİYONLAR -->
<div class="matrix-page-header">
    <div class="header-left">
        <h1 class="page-title">
            <span>👥</span>
            <span>Kullanıcı & Çoklu Rol Yetkilendirme</span>
        </h1>
        <p class="page-subtitle">
            Merkezi Yönetim Sistemi (Merkezi API & Veritabanı) ile tam senkron kullanıcı havuzu. İstediğiniz kullanıcıya birden fazla rol atayabilir, yetkilerini anında genişletebilir veya geri alabilirsiniz.
        </p>
    </div>
    <div class="header-right">
        <button type="button" class="matrix-header-btn-primary" onclick="openCentralUserModal()" title="Merkezi Yönetim Sisteminden (merkezi_auth_db) Personel Ara ve Projeye Çek" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
            <span>🏢</span>
            <span>Merkezden Kullanıcı Çek</span>
        </button>
        <button type="button" class="matrix-header-btn-secondary" onclick="openCreateUserModal()" title="Özel Manuel Kullanıcı Ekle">
            <span>➕</span>
            <span>Manuel Kullanıcı</span>
        </button>
        <button type="button" class="matrix-header-btn-secondary" id="btnSyncCentralUsers" onclick="triggerCentralSync()" title="Mevcut kayıtlı kullanıcıların unvan ve departman bilgilerini merkezden güncelle">
            <span id="syncIconSpan">🔄</span>
            <span id="syncTextSpan">Mevcutları Güncelle</span>
        </button>
        <a href="{{ route('admin.roles.index') }}" class="matrix-header-btn-secondary" title="Rol Tanımlarını ve Renklerini Yönet">
            <span>🎭</span>
            <span>Rol Tanımları</span>
        </a>
        <a href="{{ route('admin.permission_matrix') }}" class="matrix-header-btn-secondary" title="Dinamik Yetki ve Buton Matrisine Git">
            <span>🛡️</span>
            <span>Yetki Matrisi</span>
        </a>
    </div>
</div>

<!-- Matrisler & Roller Arası Hızlı Geçiş Sekmeleri -->
<div class="matrix-sister-tabs">
    <a href="{{ route('admin.roles.index') }}" class="matrix-tab-link">
        <span class="tab-emoji">🎭</span>
        <span>Roller & Gruplar</span>
    </a>
    <a href="{{ route('admin.user_roles.index') }}" class="matrix-tab-link active">
        <span class="tab-emoji">👥</span>
        <span>Kullanıcı Yetki & Rolleri</span>
    </a>
    <a href="{{ route('admin.permission_matrix') }}" class="matrix-tab-link">
        <span class="tab-emoji">🛡️</span>
        <span>Yetki & Buton Matrisi</span>
    </a>
    <a href="{{ route('admin.notification_matrix') }}" class="matrix-tab-link">
        <span class="tab-emoji">🔔</span>
        <span>Bildirim Matrisi</span>
    </a>
    <a href="{{ route('admin.doa_thresholds.index') }}" class="matrix-tab-link">
        <span class="tab-emoji">⚖️</span>
        <span>Bütçe Eşik Matrisi (DOA)</span>
    </a>
    <a href="{{ route('admin.committees.index') }}" class="matrix-tab-link">
        <span class="tab-emoji">🏛️</span>
        <span>Kurullar & Komiteler</span>
    </a>
    <a href="{{ route('admin.audit_logs') }}#tab-visibility" class="matrix-tab-link">
        <span class="tab-emoji">📋</span>
        <span>Log Görünürlük Matrisi</span>
    </a>
    <a href="{{ route('admin.system_health') }}" class="matrix-tab-link">
        <span class="tab-emoji">🩺</span>
        <span>Sistem Sağlığı</span>
    </a>
    <a href="{{ route('admin.notification_logs.index') }}" class="matrix-tab-link">
        <span class="tab-emoji">📧</span>
        <span>Bildirim & Mail Logları</span>
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4" style="padding: 12px 18px; border-radius: 10px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-size: 13.5px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
        <span>✓</span> <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger mb-4" style="padding: 12px 18px; border-radius: 10px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; font-size: 13.5px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
        <span>⚠️</span> <span>{{ session('error') }}</span>
    </div>
@endif

<!-- BİLGİLENDİRME & SENKRON BİLGİSİ BANTI -->
<div style="background: linear-gradient(135deg, #f0fdf4 0%, #e0f2fe 100%); border: 1px solid #bae6fd; border-radius: 12px; padding: 14px 18px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
    <div style="display: flex; align-items: center; gap: 10px;">
        <span style="font-size: 20px;">⚡</span>
        <div>
            <div style="font-size: 13px; font-weight: 700; color: #0369a1;">Merkezi API Otomatik Entegrasyonu Aktif</div>
            <div style="font-size: 12px; color: #475569;">
                Merkezi API sistemine eklenen kullanıcılar webhook ve periyodik senkronizasyon ile bu listeye otomatik dahil edilir. Bir kullanıcıya birden fazla rol verebilirsiniz.
            </div>
        </div>
    </div>
    @if($lastSyncLog)
        <div style="font-size: 11.5px; color: #64748b; background: rgba(255,255,255,0.7); padding: 4px 10px; border-radius: 6px; border: 1px solid #cbd5e1;">
            Son Senkron: <strong>{{ $lastSyncLog->created_at->format('d.m.Y H:i') }}</strong>
        </div>
    @endif
</div>

<!-- İSTATİSTİK SAYAÇLARI -->
<div class="stats-grid mb-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px;">
    <div class="card" style="padding: 16px 18px; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div class="text-xs text-muted font-weight-600" style="text-transform: uppercase; letter-spacing: 0.5px; font-size: 11px;">Toplam Kullanıcı</div>
        <div style="font-size: 26px; font-weight: 800; color: #0f172a; margin-top: 4px;" id="statTotalUsers">{{ $stats['total_users'] }}</div>
        <div class="text-xs text-muted mt-1" style="font-size: 11.5px; color: #64748b;">Merkez ve yerel kayıtlar</div>
    </div>
    <div class="card" style="padding: 16px 18px; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div class="text-xs text-muted font-weight-600" style="text-transform: uppercase; letter-spacing: 0.5px; font-size: 11px;">Aktif Personel</div>
        <div style="font-size: 26px; font-weight: 800; color: #10b981; margin-top: 4px;" id="statActiveUsers">{{ $stats['active_users'] }}</div>
        <div class="text-xs text-muted mt-1" style="font-size: 11.5px; color: #64748b;">Sisteme giriş izni olan</div>
    </div>
    <div class="card" style="padding: 16px 18px; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div class="text-xs text-muted font-weight-600" style="text-transform: uppercase; letter-spacing: 0.5px; font-size: 11px;">Yetkilendirilmiş (Rolü Var)</div>
        <div style="font-size: 26px; font-weight: 800; color: #0284c7; margin-top: 4px;" id="statAssignedUsers">{{ $stats['assigned_users'] }}</div>
        <div class="text-xs text-muted mt-1" style="font-size: 11.5px; color: #64748b;">En az 1 role sahip kullanıcılar</div>
    </div>
    <div class="card" style="padding: 16px 18px; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div class="text-xs text-muted font-weight-600" style="text-transform: uppercase; letter-spacing: 0.5px; font-size: 11px;">Rol Bekleyen (0 Rol)</div>
        <div style="font-size: 26px; font-weight: 800; color: {{ $stats['unassigned_users'] > 0 ? '#ef4444' : '#64748b' }}; margin-top: 4px;" id="statUnassignedUsers">{{ $stats['unassigned_users'] }}</div>
        <div class="text-xs text-muted mt-1" style="font-size: 11.5px; color: #64748b;">Henüz rol atanmamış hesaplar</div>
    </div>
    <div class="card" style="padding: 16px 18px; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div class="text-xs text-muted font-weight-600" style="text-transform: uppercase; letter-spacing: 0.5px; font-size: 11px;">Tanımlı Rol Havuzu</div>
        <div style="font-size: 26px; font-weight: 800; color: #8b5cf6; margin-top: 4px;">{{ $stats['total_roles'] }} Rol</div>
        <div class="text-xs text-muted mt-1" style="font-size: 11.5px; color: #64748b;">Atanabilir yetki rolleri</div>
    </div>
</div>

<!-- CANLI ARAMA & FİLTRELEME PANELİ -->
<div class="card mb-4" style="border: 1px solid #e2e8f0; border-radius: 14px; background: #ffffff; box-shadow: 0 2px 6px rgba(0,0,0,0.03); padding: 18px 22px;">
    <!-- Üst Satır: Arama ve Bölüm Dropdown -->
    <div style="display: flex; gap: 14px; align-items: center; justify-content: space-between; flex-wrap: wrap; margin-bottom: 14px;">
        <div style="position: relative; flex: 2; min-width: 280px;">
            <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 15px; color: #94a3b8; pointer-events: none;">🔍</span>
            <input type="text" id="userSearchInput" placeholder="Personel adı, e-posta, sicil no veya unvan ara..." 
                   style="width: 100%; height: 42px; padding: 0 38px 0 40px; border-radius: 10px; border: 1.5px solid #cbd5e1; font-size: 13.5px; background: #f8fafc; transition: all 0.2s; outline: none;"
                   oninput="filterUsersTable()" onfocus="this.style.background='#ffffff'; this.style.borderColor='#0284c7';" onblur="this.style.background='#f8fafc'; this.style.borderColor='#cbd5e1';">
            <button type="button" id="clearUserSearchBtn" onclick="clearUserSearch()" title="Aramayı Temizle"
                    style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: #e2e8f0; border: none; width: 22px; height: 22px; border-radius: 50%; font-size: 12px; color: #475569; cursor: pointer; display: none; align-items: center; justify-content: center; line-height: 1;">✕</button>
        </div>

        <div style="flex: 1; min-width: 220px;">
            <select id="userDeptSelect" onchange="filterUsersTable()" style="width: 100%; height: 42px; padding: 0 12px; border-radius: 10px; border: 1.5px solid #cbd5e1; font-size: 13px; background: #ffffff; outline: none; cursor: pointer;">
                <option value="all">🏢 Tüm Bölümler (Hepsi)</option>
                @foreach($bolumler as $bolum)
                    <option value="{{ $bolum->id }}">{{ $bolum->ad }}</option>
                @endforeach
            </select>
        </div>

        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <div id="filterResultCountBadge" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 8px; background: #eff6ff; border: 1px solid #bfdbfe; font-size: 12.5px; font-weight: 700; color: #1e40af;">
                <span>🎯 Gösterilen:</span>
                <span id="visibleUserCount">{{ count($users) }}</span> / <span>{{ count($users) }} Kişi</span>
            </div>
            <button type="button" onclick="resetAllUserFilters()" class="btn btn-sm btn-secondary" 
                    style="height: 38px; padding: 0 14px; font-size: 12px; font-weight: 600; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; border: 1px solid #cbd5e1; background: #ffffff; color: #475569;">
                <span>↺</span> Filtreleri Sıfırla
            </button>
        </div>
    </div>

    <!-- Alt Satır: Rol Çipleri ile Hızlı Filtreleme -->
    <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; border-top: 1px dashed #e2e8f0; padding-top: 14px;">
        <span style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; min-width: 90px; flex-shrink: 0;">Rol Filtresi:</span>
        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
            <button type="button" class="user-filter-chip active" data-role-filter="all" onclick="selectRoleFilter('all')">
                <span>Tüm Roller</span>
                <span class="chip-count">{{ count($users) }}</span>
            </button>
            <button type="button" class="user-filter-chip" data-role-filter="no_role" onclick="selectRoleFilter('no_role')">
                <span>⚪ Rolü Olmayanlar</span>
                <span class="chip-count">{{ $stats['unassigned_users'] }}</span>
            </button>
            @foreach($roles as $r)
                @php
                    $roleUsersCount = $users->filter(fn($u) => $u->roles->contains('id', $r->id))->count();
                @endphp
                @if($roleUsersCount > 0)
                    <button type="button" class="user-filter-chip" data-role-filter="{{ $r->id }}" onclick="selectRoleFilter('{{ $r->id }}')">
                        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: {{ $r->color }}; margin-right: 2px;"></span>
                        <span>{{ $r->name }}</span>
                        <span class="chip-count">{{ $roleUsersCount }}</span>
                    </button>
                @endif
            @endforeach
        </div>
    </div>
</div>

<!-- KULLANICI & ROL ATAMA LİSTESİ TABLOSU -->
<div class="card" style="border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; background: #ffffff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03);">
    <div class="card-header" style="padding: 16px 22px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <h3 class="card-title" style="margin: 0; font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
            <span>📋</span> Senkronize Kullanıcılar ve Atanmış Roller
        </h3>
        <div style="display: flex; align-items: center; gap: 10px;">
            <span class="badge" style="background: #e2e8f0; color: #334155; padding: 5px 12px; border-radius: 12px; font-size: 12px; font-weight: 700;">
                Toplam {{ count($users) }} Personel
            </span>
        </div>
    </div>

    <div class="table-responsive" style="overflow-x: auto;">
        <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px; margin: 0;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #cbd5e1; color: #475569; font-size: 11.5px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">
                    <th style="padding: 14px 18px; text-align: left; min-width: 240px; white-space: nowrap;">Kullanıcı & Bilgiler</th>
                    <th style="padding: 14px 14px; text-align: left; min-width: 180px; white-space: nowrap;">Bölüm & Ünvan</th>
                    <th style="padding: 14px 14px; text-align: left; min-width: 280px;">Atanmış Roller (Çoklu Yetki)</th>
                    <th style="padding: 14px 14px; text-align: center; min-width: 130px; white-space: nowrap;">Merkezi Senkron</th>
                    <th style="padding: 14px 14px; text-align: center; min-width: 100px; white-space: nowrap;">Durum</th>
                    <th style="padding: 14px 18px; text-align: right; min-width: 140px; white-space: nowrap;">Aksiyonlar</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
                @foreach($users as $user)
                    @php
                        $userRoleIds = $user->roles->pluck('id')->toArray();
                        $maskedName = $user->getMaskedNameFor(auth()->user());
                    @endphp
                    <tr class="user-table-row" id="user-row-{{ $user->id }}"
                        data-user-id="{{ $user->id }}"
                        data-name="{{ mb_strtolower($user->name, 'UTF-8') }}"
                        data-email="{{ mb_strtolower($user->email, 'UTF-8') }}"
                        data-sicil="{{ mb_strtolower($user->sicil_no ?? '', 'UTF-8') }}"
                        data-unvan="{{ mb_strtolower($user->unvan ?? '', 'UTF-8') }}"
                        data-bolum-id="{{ $user->bolum_id ?? 'none' }}"
                        data-roles="{{ implode(',', $userRoleIds) }}"
                        data-is-active="{{ $user->is_active ? '1' : '0' }}"
                        style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;"
                        onmouseover="this.style.background='#f8fafc'" 
                        onmouseout="this.style.background='#ffffff'">
                        
                        <!-- 1. Kullanıcı & Bilgiler -->
                        <td style="padding: 14px 18px; vertical-align: middle;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div class="user-avatar-initial" title="{{ $maskedName }}">
                                    {{ mb_substr($user->name, 0, 1) }}
                                </div>
                                <div style="display: flex; flex-direction: column;">
                                    <div style="font-size: 13.5px; font-weight: 750; color: #0f172a; line-height: 1.2;">
                                        {{ $maskedName }}
                                    </div>
                                    <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                                        {{ $user->email }}
                                    </div>
                                    @if($user->sicil_no)
                                        <div style="margin-top: 3px;">
                                            <span style="font-size: 10.5px; font-weight: 700; color: #475569; background: #f1f5f9; border: 1px solid #e2e8f0; padding: 1px 6px; border-radius: 4px; font-family: monospace;">
                                                Sicil: {{ $user->sicil_no }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- 2. Bölüm & Ünvan -->
                        <td style="padding: 14px 14px; vertical-align: middle;">
                            <div style="font-size: 13px; font-weight: 650; color: #334155;">
                                {{ $user->bolum?->ad ?? 'Bölüm Atanmamış' }}
                            </div>
                            <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                {{ $user->unvan ?? 'Uzman' }}
                            </div>
                            @if($user->phone)
                                <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">
                                    📞 {{ $user->phone }}
                                </div>
                            @endif
                        </td>

                        <!-- 3. Atanmış Roller (Çoklu Yetki Rozetleri) -->
                        <td style="padding: 14px 14px; vertical-align: middle;">
                            <div class="user-roles-badge-wrap" id="user-roles-container-{{ $user->id }}">
                                @forelse($user->roles as $role)
                                    <span class="user-single-role-badge" 
                                          style="background: {{ $role->badge_bg }}; color: {{ $role->badge_text }}; border: 1px solid {{ $role->badge_border }};"
                                          title="{{ $role->group_title }}: {{ $role->description }}">
                                        <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: {{ $role->color }};"></span>
                                        <span>{{ $role->name }}</span>
                                    </span>
                                @empty
                                    <span style="font-size: 11.5px; color: #94a3b8; font-style: italic;">
                                        Rol Atanmamış (0 Yetki)
                                    </span>
                                @endforelse
                                <button type="button" class="btn-quick-add-role" onclick="openAssignRolesModal({{ $user->id }})" title="Rol Ekle / Çıkar">
                                    <span>+</span> <span>Yetki Düzenle</span>
                                </button>
                            </div>
                        </td>

                        <!-- 4. Merkezi Senkron Rozeti -->
                        <td style="padding: 14px 14px; text-align: center; vertical-align: middle; white-space: nowrap;">
                            <span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;" title="Merkezi API ile kimlik ve sicil bilgisi eşleşti">
                                <span>✓</span> <span>Merkezi API</span>
                            </span>
                        </td>

                        <!-- 5. Durum -->
                        <td style="padding: 14px 14px; text-align: center; vertical-align: middle; white-space: nowrap;">
                            @if($user->is_active)
                                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #22c55e;"></span>
                                    Aktif
                                </span>
                            @else
                                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #ef4444;"></span>
                                    Pasif
                                </span>
                            @endif
                        </td>

                        <!-- 6. Aksiyonlar -->
                        <td style="padding: 14px 18px; text-align: right; vertical-align: middle; white-space: nowrap;">
                            <button type="button" class="btn btn-sm btn-primary" onclick="openAssignRolesModal({{ $user->id }})" 
                                    style="padding: 6px 14px; font-size: 12px; font-weight: 700; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff; border: 1px solid #0284c7; box-shadow: 0 1px 3px rgba(2, 132, 199, 0.2);">
                                <span>⚡</span> Rolleri Düzenle
                            </button>
                        </td>
                    </tr>
                @endforeach

                <!-- Eşleşmeyen Durum Satırı -->
                <tr id="noUsersFoundRow" style="display: none;">
                    <td colspan="6" style="padding: 40px 20px; text-align: center; color: #64748b;">
                        <div style="font-size: 32px; margin-bottom: 8px;">🔍</div>
                        <div style="font-size: 14.5px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">Aramanızla Eşleşen Kullanıcı Bulunamadı</div>
                        <div style="font-size: 12.5px;">Arama terimlerinizi değiştirerek veya filtreleri sıfırlayarak tekrar deneyebilirsiniz.</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================================================= -->
<!-- ÇOKLU ROL ATAMA & YETKİLENDİRME MODALI (AJAX DESTEKLİ, GRUPLANDIRILMIŞ) -->
<!-- ========================================================================= -->
<div id="assignRolesModalBackdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); z-index: 10050; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #ffffff; border-radius: 16px; width: 100%; max-width: 680px; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3); border: 1px solid #e2e8f0;">
        
        <!-- Modal Başlık Çubuğu (Sabit) -->
        <div style="padding: 16px 22px; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-shrink: 0;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div id="modalUserAvatar" class="user-avatar-initial" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.4);">
                    C
                </div>
                <div>
                    <h4 style="margin: 0; font-size: 16px; font-weight: 800; color: #ffffff; line-height: 1.2;" id="modalUserName">Kullanıcı Rolleri</h4>
                    <p style="margin: 3px 0 0 0; font-size: 12px; color: #bae6fd;" id="modalUserEmail">kullanici@koksan.com - Preform Bölümü</p>
                </div>
            </div>
            <button type="button" onclick="closeAssignRolesModal()" style="border: none; background: rgba(255, 255, 255, 0.2); width: 30px; height: 30px; border-radius: 50%; font-size: 16px; color: #ffffff; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">&times;</button>
        </div>

        <!-- Modal Rehber & Seçim İpuçları Çubuğu -->
        <div style="padding: 12px 22px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; flex-shrink: 0;">
            <div style="font-size: 12.5px; color: #475569; font-weight: 600;">
                <span>💡 Bir kullanıcıya birden fazla rol atayabilirsiniz. Seçimleri işaretleyin veya kaldırın.</span>
            </div>
            <div style="display: inline-flex; align-items: center; gap: 6px;">
                <button type="button" onclick="selectAllRolesInModal(true)" class="btn btn-xs" style="padding: 3px 10px; font-size: 11px; font-weight: 650; border-radius: 6px; background: #ffffff; border: 1px solid #cbd5e1; color: #334155; cursor: pointer;">Tümünü Seç</button>
                <button type="button" onclick="selectAllRolesInModal(false)" class="btn btn-xs" style="padding: 3px 10px; font-size: 11px; font-weight: 650; border-radius: 6px; background: #ffffff; border: 1px solid #cbd5e1; color: #dc2626; cursor: pointer;">Temizle</button>
            </div>
        </div>

        <!-- Kaydırılabilir Rol Seçim Listesi (Grup Bazlı) -->
        <div id="modalRolesListContainer" style="padding: 18px 22px; overflow-y: auto; max-height: 55vh; display: flex; flex-direction: column; gap: 18px; flex: 1;">
            @foreach($roleGroups as $rg)
                @php
                    $groupRoles = collect($roles)->where('role_group', $rg->slug);
                @endphp
                @if($groupRoles->count() > 0)
                    <div class="modal-role-group-section">
                        <div style="font-size: 12px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                            <span>{{ $rg->icon }}</span>
                            <span>{{ $rg->name }}</span>
                            <span style="font-size: 11px; color: #94a3b8; font-weight: 600;">({{ $groupRoles->count() }} Rol)</span>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 8px;">
                            @foreach($groupRoles as $r)
                                <label class="role-choice-card" id="role-card-{{ $r->id }}">
                                    <input type="checkbox" class="role-choice-checkbox modal-role-check" value="{{ $r->id }}" data-role-id="{{ $r->id }}" onchange="onRoleCheckChange(this)">
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px;">
                                            <span style="font-size: 12.5px; font-weight: 750; color: #0f172a;">
                                                {{ $r->name }}
                                            </span>
                                            <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: {{ $r->color }}; border: 1px solid rgba(0,0,0,0.15);" title="Rol Rengi"></span>
                                        </div>
                                        @if($r->description)
                                            <div style="font-size: 11px; color: #64748b; margin-top: 3px; line-height: 1.3;">
                                                {{ $r->description }}
                                            </div>
                                        @endif
                                        <div style="margin-top: 4px; display: flex; align-items: center; gap: 6px;">
                                            <span style="font-size: 10.5px; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 1px 6px; border-radius: 4px;">
                                                {{ $r->permissions->count() }} İzin
                                            </span>
                                            <code style="font-size: 10px; color: #64748b; font-family: monospace;">{{ $r->slug }}</code>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <!-- Modal Alt Çubuğu (Sabit) -->
        <div style="padding: 14px 22px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
            <div style="font-size: 12.5px; color: #64748b; font-weight: 600;">
                Seçili: <strong id="selectedRolesCountBadge" style="color: #0284c7;">0</strong> Rol
            </div>
            <div style="display: inline-flex; align-items: center; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeAssignRolesModal()" style="padding: 8px 16px; font-size: 13px; font-weight: 600; border-radius: 8px; cursor: pointer;">İptal</button>
                <button type="button" id="btnSaveUserRoles" class="btn btn-primary" onclick="submitUserRoles()" style="padding: 8px 22px; font-size: 13px; font-weight: 700; border-radius: 8px; cursor: pointer; background: #0284c7; border-color: #0284c7; display: inline-flex; align-items: center; gap: 6px;">
                    <span id="saveRolesBtnIcon">💾</span>
                    <span id="saveRolesBtnText">Yetkileri Kaydet</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- =========================================================================
     MERKEZİ SİSTEMDEN KULLANICI ÇEKME / ARAMA MODALI
     ========================================================================= -->
<div id="centralUserModalBackdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); z-index: 10050; align-items: center; justify-content: center; padding: 16px;" onclick="if(event.target===this) closeCentralUserModal()">
    <div style="background: #ffffff; border-radius: 16px; width: 100%; max-width: 780px; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); border: 1px solid #e2e8f0;" onclick="event.stopPropagation()">
        <!-- Header -->
        <div style="padding: 18px 24px; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    🏢
                </div>
                <div>
                    <h3 style="font-size: 16px; font-weight: 800; color: #ffffff; margin: 0;">Merkezi Yönetim Sisteminden Kullanıcı Çek</h3>
                    <p style="font-size: 12px; color: #e0f2fe; margin: 3px 0 0 0;">Merkezi havuzda kayıtlı 2.000+ personelden istediklerinizi arayıp Köksan PMS'e yetkili olarak ekleyin.</p>
                </div>
            </div>
            <button type="button" onclick="closeCentralUserModal()" style="background: rgba(255,255,255,0.15); border: none; font-size: 18px; color: #ffffff; cursor: pointer; border-radius: 8px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">✕</button>
        </div>

        <!-- Controls: Search & Role Selector -->
        <div style="padding: 16px 24px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; gap: 12px; align-items: center;">
            <div style="flex: 1; position: relative;">
                <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 15px; color: #94a3b8;">🔍</span>
                <input type="text" id="centralSearchInput" placeholder="Ad Soyad, E-posta, Sicil No veya Unvan yazın..." 
                       oninput="debouncedCentralSearch()" onkeydown="if(event.key==='Enter') searchCentralUsersNow()"
                       style="width: 100%; height: 42px; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 0 12px 0 38px; font-size: 13.5px; font-weight: 500; outline: none;">
            </div>
            <div style="width: 220px;">
                <label style="display: block; font-size: 11px; font-weight: 700; color: #64748b; margin-bottom: 4px;">Atanacak Rol:</label>
                <select id="centralDefaultRole" class="form-control" style="width: 100%; height: 38px; border-radius: 8px; border: 1.5px solid #cbd5e1; font-size: 12.5px; font-weight: 600;">
                    @foreach($roles as $r)
                        <option value="{{ $r->id }}" {{ $r->slug === 'kullanici' ? 'selected' : '' }}>
                            {{ $r->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Results Area -->
        <div id="centralSearchResultsList" style="padding: 16px 24px; min-height: 250px; max-height: calc(85vh - 200px); overflow-y: auto;">
            <div id="centralSearchPlaceholder" style="text-align: center; padding: 40px 20px; color: #64748b;">
                <div style="font-size: 36px; margin-bottom: 10px;">👥</div>
                <h4 style="font-size: 14px; font-weight: 700; color: #334155; margin: 0;">Personel Araması Yapın</h4>
                <p style="font-size: 12px; margin: 4px 0 0 0;">Merkezden getirmek istediğiniz kişinin adını veya sicil numarasını yukarıdaki alana yazınız.</p>
            </div>
        </div>

        <!-- Footer -->
        <div style="padding: 12px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
            <div style="font-size: 12px; color: #64748b;">
                <span id="centralResultCount">0</span> sonuç bulundu
            </div>
            <button type="button" class="btn btn-secondary" onclick="closeCentralUserModal()" style="padding: 7px 16px; font-size: 13px; font-weight: 600; border-radius: 8px;">Kapat</button>
        </div>
    </div>
</div>

<!-- =========================================================================
     YENİ KULLANICI OLUŞTURMA MODALI (TEST & OPERASYON)
     ========================================================================= -->
<div id="createUserModalBackdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); z-index: 10050; align-items: center; justify-content: center; padding: 16px;" onclick="if(event.target===this) closeCreateUserModal()">
    <div style="background: #ffffff; border-radius: 16px; width: 100%; max-width: 650px; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3); border: 1px solid #e2e8f0;" onclick="event.stopPropagation()">
        <!-- Header -->
        <div style="padding: 18px 24px; background: #ffffff; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    👤
                </div>
                <div>
                    <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0;">Yeni Test / Sistem Kullanıcısı Ekle</h3>
                    <p style="font-size: 12px; color: #64748b; margin: 2px 0 0 0;">Kullanıcıyı tanımlayın ve sistemdeki rollerini belirleyin.</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateUserModal()" style="background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer; padding: 4px;">✕</button>
        </div>

        <!-- Form Body -->
        <form action="{{ route('admin.user_roles.store') }}" method="POST" id="createUserForm">
            @csrf
            <div style="padding: 22px 24px; max-height: calc(85vh - 140px); overflow-y: auto;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 5px;">Ad Soyad <span style="color: #dc2626;">*</span></label>
                        <input type="text" name="name" required class="form-control" placeholder="Örn: Ahmet Yılmaz" style="width: 100%; height: 38px; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 0 12px; font-size: 13px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 5px;">E-Posta <span style="color: #dc2626;">*</span></label>
                        <input type="email" name="email" required class="form-control" placeholder="Örn: ahmet.yilmaz@koksan.com" style="width: 100%; height: 38px; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 0 12px; font-size: 13px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 5px;">Giriş Şifresi</label>
                        <input type="text" name="password" class="form-control" placeholder="Boş bırakılırsa: 12345678" style="width: 100%; height: 38px; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 0 12px; font-size: 13px;">
                        <span style="font-size: 11px; color: #64748b; margin-top: 3px; display: block;">💡 Test kolaylığı için varsayılan: <code>12345678</code></span>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 5px;">Kurumsal Unvan</label>
                        <input type="text" name="unvan" class="form-control" placeholder="Örn: Bölüm Lideri, Proje Mühendisi" style="width: 100%; height: 38px; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 0 12px; font-size: 13px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 18px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 5px;">Departman / Bölüm</label>
                        <select name="bolum_id" class="form-control" style="width: 100%; height: 38px; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 0 10px; font-size: 12.5px;">
                            <option value="">Bölüm Seçiniz...</option>
                            @foreach($bolumler as $b)
                                <option value="{{ $b->id }}">{{ $b->ad }} ({{ $b->kod }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 5px;">Sicil No</label>
                        <input type="text" name="sicil_no" class="form-control" placeholder="Örn: KOK-101" style="width: 100%; height: 38px; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 0 12px; font-size: 13px;">
                    </div>
                </div>

                <!-- Başlangıç Rolleri Seçimi -->
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 750; color: #0f172a; margin-bottom: 8px;">
                        🎭 Başlangıç Rolü Ata:
                    </label>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 8px; max-height: 180px; overflow-y: auto; padding: 8px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc;">
                        @foreach($roles as $r)
                            @if($r->slug !== 'superadmin')
                                <label style="display: flex; align-items: center; gap: 8px; padding: 6px 10px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 7px; cursor: pointer; font-size: 12px; font-weight: 650; color: #1e293b;">
                                    <input type="checkbox" name="roles[]" value="{{ $r->id }}" style="accent-color: #0284c7; width: 15px; height: 15px;">
                                    <span style="width: 8px; height: 8px; border-radius: 50%; background: {{ $r->color ?? '#0284c7' }}; flex-shrink: 0;"></span>
                                    <span>{{ $r->name }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div>
                    <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 12.5px; font-weight: 600; color: #334155; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" checked style="accent-color: #10b981; width: 16px; height: 16px;">
                        <span>Kullanıcı Hesabı Aktif Olsun</span>
                    </label>
                </div>
            </div>

            <!-- Footer -->
            <div style="padding: 14px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeCreateUserModal()" style="padding: 8px 16px; font-size: 13px; font-weight: 600; border-radius: 8px; cursor: pointer;">İptal</button>
                <button type="submit" class="btn btn-primary" style="padding: 8px 22px; font-size: 13px; font-weight: 700; border-radius: 8px; cursor: pointer; background: #0284c7; border-color: #0284c7; display: inline-flex; align-items: center; gap: 6px;">
                    <span>💾</span>
                    <span>Kullanıcıyı Kaydet</span>
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
// =========================================================================
// KULLANICI & ROL İSTEMCİ VERİLERİ (CLIENT DATA STORE)
// =========================================================================
const usersData = {
@foreach($users as $u)
    {{ $u->id }}: {
        id: {{ $u->id }},
        name: @json($u->name),
        masked_name: @json($u->getMaskedNameFor(auth()->user())),
        email: @json($u->email),
        sicil_no: @json($u->sicil_no),
        unvan: @json($u->unvan ?? 'Uzman'),
        bolum_id: @json($u->bolum_id),
        bolum_ad: @json($u->bolum?->ad ?? 'Bölüm Atanmamış'),
        phone: @json($u->phone),
        is_active: {{ $u->is_active ? 'true' : 'false' }},
        roles: [
            @foreach($u->roles as $r)
            {
                id: {{ $r->id }},
                name: @json($r->name),
                slug: @json($r->slug),
                color: @json($r->color),
                badge_bg: @json($r->badge_bg),
                badge_text: @json($r->badge_text),
                badge_border: @json($r->badge_border),
                group_title: @json($r->group_title ?? 'Diğer')
            },
            @endforeach
        ]
    },
@endforeach
};

const allRolesMap = {
@foreach($roles as $r)
    {{ $r->id }}: {
        id: {{ $r->id }},
        name: @json($r->name),
        slug: @json($r->slug),
        color: @json($r->color),
        badge_bg: @json($r->badge_bg),
        badge_text: @json($r->badge_text),
        badge_border: @json($r->badge_border),
        group_title: @json($r->group_title ?? 'Diğer')
    },
@endforeach
};

let currentEditingUserId = null;
let activeRoleFilter = 'all';

// =========================================================================
// CANLI FİLTRELEME & ARAMA MOTORU
// =========================================================================
function filterUsersTable() {
    const searchInput = document.getElementById('userSearchInput');
    const q = (searchInput ? searchInput.value : '').trim().toLowerCase();
    
    const clearBtn = document.getElementById('clearUserSearchBtn');
    if (clearBtn) clearBtn.style.display = q ? 'inline-flex' : 'none';

    const deptSelect = document.getElementById('userDeptSelect');
    const selectedDept = deptSelect ? deptSelect.value : 'all';

    const rows = document.querySelectorAll('.user-table-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const name = row.getAttribute('data-name') || '';
        const email = row.getAttribute('data-email') || '';
        const sicil = row.getAttribute('data-sicil') || '';
        const unvan = row.getAttribute('data-unvan') || '';
        const bolumId = row.getAttribute('data-bolum-id') || '';
        const rolesStr = row.getAttribute('data-roles') || '';
        const userRolesList = rolesStr ? rolesStr.split(',') : [];

        // 1. Arama Eşleşmesi
        let matchSearch = true;
        if (q) {
            matchSearch = name.includes(q) || email.includes(q) || sicil.includes(q) || unvan.includes(q);
        }

        // 2. Bölüm Eşleşmesi
        let matchDept = true;
        if (selectedDept !== 'all') {
            matchDept = (bolumId === selectedDept);
        }

        // 3. Rol Çipi Eşleşmesi
        let matchRole = true;
        if (activeRoleFilter === 'no_role') {
            matchRole = (userRolesList.length === 0 || rolesStr === '');
        } else if (activeRoleFilter !== 'all') {
            matchRole = userRolesList.includes(String(activeRoleFilter));
        }

        if (matchSearch && matchDept && matchRole) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const noRowsRow = document.getElementById('noUsersFoundRow');
    if (noRowsRow) {
        noRowsRow.style.display = (visibleCount === 0) ? '' : 'none';
    }

    const countSpan = document.getElementById('visibleUserCount');
    if (countSpan) countSpan.textContent = visibleCount;
}

function clearUserSearch() {
    const input = document.getElementById('userSearchInput');
    if (input) {
        input.value = '';
        input.focus();
    }
    filterUsersTable();
}

function selectRoleFilter(roleVal) {
    activeRoleFilter = roleVal;
    document.querySelectorAll('.user-filter-chip').forEach(btn => {
        if (btn.getAttribute('data-role-filter') === String(roleVal)) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    filterUsersTable();
}

function resetAllUserFilters() {
    const input = document.getElementById('userSearchInput');
    if (input) input.value = '';
    const clearBtn = document.getElementById('clearUserSearchBtn');
    if (clearBtn) clearBtn.style.display = 'none';

    const deptSelect = document.getElementById('userDeptSelect');
    if (deptSelect) deptSelect.value = 'all';

    selectRoleFilter('all');
}

// =========================================================================
// ÇOKLU ROL ATAMA MODALI KONTROLLERİ
// =========================================================================
function openAssignRolesModal(userId) {
    const user = usersData[userId];
    if (!user) return;

    currentEditingUserId = userId;

    document.getElementById('modalUserName').textContent = user.masked_name;
    document.getElementById('modalUserEmail').textContent = `${user.email} • ${user.bolum_ad} (${user.unvan})`;
    document.getElementById('modalUserAvatar').textContent = user.name.charAt(0).toUpperCase();

    // Mevcut rolleri işaretle
    const userRoleIds = user.roles.map(r => String(r.id));
    document.querySelectorAll('.modal-role-check').forEach(chk => {
        const roleId = chk.value;
        const isChecked = userRoleIds.includes(roleId);
        chk.checked = isChecked;
        const card = document.getElementById('role-card-' + roleId);
        if (card) {
            if (isChecked) card.classList.add('selected');
            else card.classList.remove('selected');
        }
    });

    updateSelectedRolesCount();

    const m = document.getElementById('assignRolesModalBackdrop');
    if (m) {
        m.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeAssignRolesModal() {
    const m = document.getElementById('assignRolesModalBackdrop');
    if (m) {
        m.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    currentEditingUserId = null;
}

function onRoleCheckChange(chk) {
    const card = document.getElementById('role-card-' + chk.value);
    if (card) {
        if (chk.checked) card.classList.add('selected');
        else card.classList.remove('selected');
    }
    updateSelectedRolesCount();
}

function selectAllRolesInModal(selectAll) {
    document.querySelectorAll('.modal-role-check').forEach(chk => {
        chk.checked = selectAll;
        const card = document.getElementById('role-card-' + chk.value);
        if (card) {
            if (selectAll) card.classList.add('selected');
            else card.classList.remove('selected');
        }
    });
    updateSelectedRolesCount();
}

function updateSelectedRolesCount() {
    const checkedCount = document.querySelectorAll('.modal-role-check:checked').length;
    const badge = document.getElementById('selectedRolesCountBadge');
    if (badge) badge.textContent = checkedCount;
}

// =========================================================================
// ROLLERİ KAYDET (AJAX POST)
// =========================================================================
function submitUserRoles() {
    if (!currentEditingUserId) return;

    const selectedRoleIds = [];
    document.querySelectorAll('.modal-role-check:checked').forEach(chk => {
        selectedRoleIds.push(parseInt(chk.value, 10));
    });

    const btn = document.getElementById('btnSaveUserRoles');
    const btnIcon = document.getElementById('saveRolesBtnIcon');
    const btnText = document.getElementById('saveRolesBtnText');

    btn.disabled = true;
    btnIcon.textContent = '⏳';
    btnText.textContent = 'Kaydediliyor...';

    const token = document.querySelector('input[name="_token"]') ? document.querySelector('input[name="_token"]').value : '{{ csrf_token() }}';

    fetch(`{{ url('admin/kullanici-rolleri') }}/${currentEditingUserId}/roller`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ roles: selectedRoleIds })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btnIcon.textContent = '💾';
        btnText.textContent = 'Yetkileri Kaydet';

        if (data.success) {
            // Kullanıcı veri yapısını güncelle
            const user = usersData[currentEditingUserId];
            user.roles = selectedRoleIds.map(rid => allRolesMap[rid]).filter(Boolean);

            // Tablo satırındaki rozetleri anında yeniden oluştur
            renderUserRolesRow(currentEditingUserId);

            // Satır veri niteliğini güncelle
            const row = document.getElementById('user-row-' + currentEditingUserId);
            if (row) {
                row.setAttribute('data-roles', selectedRoleIds.join(','));
            }

            closeAssignRolesModal();
            showFloatingToast(data.message || 'Yetkiler başarıyla güncellendi.', 'success');
        } else {
            alert(data.message || 'Yetkiler güncellenirken bir hata oluştu.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btnIcon.textContent = '💾';
        btnText.textContent = 'Yetkileri Kaydet';
        alert('Sunucu iletişim hatası: ' + err);
    });
}

function renderUserRolesRow(userId) {
    const user = usersData[userId];
    if (!user) return;

    const container = document.getElementById('user-roles-container-' + userId);
    if (!container) return;

    if (user.roles.length === 0) {
        container.innerHTML = `
            <span style="font-size: 11.5px; color: #94a3b8; font-style: italic;">
                Rol Atanmamış (0 Yetki)
            </span>
            <button type="button" class="btn-quick-add-role" onclick="openAssignRolesModal(${userId})" title="Rol Ekle / Çıkar">
                <span>+</span> <span>Yetki Düzenle</span>
            </button>
        `;
        return;
    }

    let html = '';
    user.roles.forEach(r => {
        html += `
            <span class="user-single-role-badge" 
                  style="background: ${r.badge_bg}; color: ${r.badge_text}; border: 1px solid ${r.badge_border};"
                  title="${r.group_title}">
                <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: ${r.color};"></span>
                <span>${r.name}</span>
            </span>
        `;
    });

    html += `
        <button type="button" class="btn-quick-add-role" onclick="openAssignRolesModal(${userId})" title="Rol Ekle / Çıkar">
            <span>+</span> <span>Yetki Düzenle</span>
        </button>
    `;

    container.innerHTML = html;
}

// =========================================================================
// MERKEZDEN SENKRONİZASYON TETİKLEME (AJAX)
// =========================================================================
function triggerCentralSync() {
    const btn = document.getElementById('btnSyncCentralUsers');
    const iconSpan = document.getElementById('syncIconSpan');
    const textSpan = document.getElementById('syncTextSpan');

    btn.disabled = true;
    iconSpan.classList.add('spin-anim');
    textSpan.textContent = 'Senkronize Ediliyor...';

    const token = document.querySelector('input[name="_token"]') ? document.querySelector('input[name="_token"]').value : '{{ csrf_token() }}';

    fetch('{{ route("admin.user_roles.sync") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        iconSpan.classList.remove('spin-anim');
        btn.disabled = false;
        textSpan.textContent = 'Merkezden Senkronize Et';

        if (data.success) {
            showFloatingToast(data.message, 'success');
            // Yeni kullanıcı veya güncellenen kayıtlar için sayfayı yenile
            setTimeout(() => {
                window.location.reload();
            }, 1200);
        } else {
            showFloatingToast(data.message || 'Senkronizasyon başarısız.', 'error');
        }
    })
    .catch(err => {
        iconSpan.classList.remove('spin-anim');
        btn.disabled = false;
        textSpan.textContent = 'Merkezden Senkronize Et';
        showFloatingToast('Bağlantı hatası: ' + err, 'error');
    });
}

// =========================================================================
// ANLIK BİLDİRİM TOAST'I (FLOATING TOAST)
// =========================================================================
function showFloatingToast(message, type = 'success') {
    const toast = document.createElement('div');
    const isSuccess = type === 'success';
    toast.style.cssText = `
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 99999;
        padding: 12px 20px;
        border-radius: 10px;
        background: ${isSuccess ? '#065f46' : '#991b1b'};
        color: #ffffff;
        font-size: 13px;
        font-weight: 700;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideInUp 0.2s ease forwards;
    `;
    toast.innerHTML = `<span>${isSuccess ? '✓' : '⚠️'}</span> <span>${message}</span>`;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

// Modal dışına tıklayınca kapatma
document.getElementById('assignRolesModalBackdrop').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAssignRolesModal();
    }
});

// Yeni Kullanıcı Ekle Modalı Fonksiyonları
function openCreateUserModal() {
    const modal = document.getElementById('createUserModalBackdrop');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeCreateUserModal() {
    const modal = document.getElementById('createUserModalBackdrop');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Merkezi Sistemden Kullanıcı Çekme Modalı Fonksiyonları
let centralSearchTimeout = null;

function openCentralUserModal() {
    const modal = document.getElementById('centralUserModalBackdrop');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        setTimeout(() => document.getElementById('centralSearchInput')?.focus(), 100);
    }
}

function closeCentralUserModal() {
    const modal = document.getElementById('centralUserModalBackdrop');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

function debouncedCentralSearch() {
    clearTimeout(centralSearchTimeout);
    centralSearchTimeout = setTimeout(searchCentralUsersNow, 350);
}

function searchCentralUsersNow() {
    const query = document.getElementById('centralSearchInput').value.trim();
    const list = document.getElementById('centralSearchResultsList');
    const countBadge = document.getElementById('centralResultCount');

    if (query.length < 2) {
        list.innerHTML = `
            <div style="text-align: center; padding: 40px 20px; color: #64748b;">
                <div style="font-size: 36px; margin-bottom: 10px;">👥</div>
                <h4 style="font-size: 14px; font-weight: 700; color: #334155; margin: 0;">Personel Araması Yapın</h4>
                <p style="font-size: 12px; margin: 4px 0 0 0;">En az 2 karakter giriniz.</p>
            </div>
        `;
        countBadge.textContent = '0';
        return;
    }

    list.innerHTML = `
        <div style="text-align: center; padding: 40px 20px; color: #0284c7;">
            <div style="font-size: 32px; margin-bottom: 10px; animation: spin 1s infinite linear;">🔄</div>
            <p style="font-size: 13px; font-weight: 600; margin: 0;">Merkezi sistemde aranıyor...</p>
        </div>
    `;

    fetch(`{{ route('admin.user_roles.search_central') }}?q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
            if (!data.success || !data.users || data.users.length === 0) {
                list.innerHTML = `
                    <div style="text-align: center; padding: 40px 20px; color: #94a3b8;">
                        <div style="font-size: 36px; margin-bottom: 10px;">🔍</div>
                        <h4 style="font-size: 14px; font-weight: 700; color: #475569; margin: 0;">Eşleşen Personel Bulunamadı</h4>
                        <p style="font-size: 12px; margin: 4px 0 0 0;">Farklı bir isim veya sicil no deneyebilirsiniz.</p>
                    </div>
                `;
                countBadge.textContent = '0';
                return;
            }

            countBadge.textContent = data.users.length;
            let html = '<div style="display: flex; flex-direction: column; gap: 8px;">';

            data.users.forEach(u => {
                const initial = (u.name || 'U').charAt(0).toUpperCase();
                const avatarHtml = u.avatar 
                    ? `<img src="${u.avatar}" style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover;">`
                    : `<div style="width: 38px; height: 38px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 15px;">${initial}</div>`;

                const actionHtml = u.is_imported 
                    ? `<span style="padding: 6px 12px; border-radius: 6px; font-size: 11.5px; font-weight: 700; background: #e2e8f0; color: #475569;">✓ Sistemde Ekli</span>`
                    : `<button type="button" class="btn btn-sm btn-primary" onclick="importCentralUser(${u.id}, this)" style="padding: 6px 14px; font-size: 12px; font-weight: 700; border-radius: 6px; background: #0284c7; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                         <span>📥</span><span>PMS'e Ekle</span>
                       </button>`;

                html += `
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; transition: all 0.2s ease;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            ${avatarHtml}
                            <div>
                                <div style="font-size: 13.5px; font-weight: 700; color: #0f172a; line-height: 1.2;">${u.name}</div>
                                <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                    <span style="font-weight: 600; color: #334155;">${u.department_name}</span> &bull; 
                                    <span>${u.unvan}</span>
                                    ${u.sicil_no ? ` &bull; <code style="font-size: 10.5px; background: #f1f5f9; padding: 1px 4px; border-radius: 3px;">${u.sicil_no}</code>` : ''}
                                </div>
                                <div style="font-size: 11px; color: #94a3b8; margin-top: 1px;">${u.email}</div>
                            </div>
                        </div>
                        <div id="centralAction_${u.id}">
                            ${actionHtml}
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            list.innerHTML = html;
        })
        .catch(err => {
            list.innerHTML = `<div style="text-align: center; color: #dc2626; padding: 20px;">Arama hatası: ${err.message}</div>`;
        });
}

function importCentralUser(centralUserId, btn) {
    const roleId = document.getElementById('centralDefaultRole').value;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span>⏳</span><span>Ekleniyor...</span>';

    fetch('{{ route("admin.user_roles.import_central") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            central_user_id: centralUserId,
            role_id: roleId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            btn.parentNode.innerHTML = '<span style="padding: 6px 12px; border-radius: 6px; font-size: 11.5px; font-weight: 700; background: #dcfce7; color: #15803d;">✓ Eklendi</span>';
            showToast(data.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 900);
        } else {
            btn.disabled = false;
            btn.innerHTML = originalText;
            showToast(data.message || 'Aktarma sırasında bir hata oluştu.', 'error');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        showToast('İstek hatası: ' + err.message, 'error');
    });
}
</script>
@endpush
