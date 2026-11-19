@props(['status'])

@php
    $statusMap = [
        'completed' => ['label' => 'Tamamlandı', 'class' => 'badge-success', 'icon' => '✓'],
        'done' => ['label' => 'Tamamlandı', 'class' => 'badge-success', 'icon' => '✓'],
        'in_progress' => ['label' => 'Devam Ediyor', 'class' => 'badge-warning', 'icon' => '⏳'],
        'waiting_approval' => ['label' => 'Onay Bekliyor', 'class' => 'badge-info', 'icon' => '⏱'],
        'waiting_ehs' => ['label' => 'İSG Onayı Bekleniyor', 'class' => 'badge-ehs', 'icon' => '🦺'],
        'review' => ['label' => 'İncelemede', 'class' => 'badge-info', 'icon' => '🔍'],
        'blocked' => ['label' => 'Tıkandı', 'class' => 'badge-danger', 'icon' => '⚠'],
        'critical' => ['label' => 'Kritik', 'class' => 'badge-danger', 'icon' => '🔥'],
        'todo' => ['label' => 'Yapılacak', 'class' => 'badge-secondary', 'icon' => '○'],
        'pending' => ['label' => 'Onay Bekliyor', 'class' => 'badge-info', 'icon' => '⏱'],
        'draft' => ['label' => 'Taslak', 'class' => 'badge-secondary', 'icon' => '✎'],
        'cancelled' => ['label' => 'İptal', 'class' => 'badge-dark', 'icon' => '✕'],
        'on_hold' => ['label' => 'Askıda', 'class' => 'badge-dark', 'icon' => '⏸'],
        // Tedarik / BOM Kalem Durumları
        'delivered' => ['label' => 'Teslim Alındı', 'class' => 'badge-success', 'icon' => '📦'],
        'ordered' => ['label' => 'Sipariş Verildi', 'class' => 'badge-info', 'icon' => '🛒'],
        'procuring' => ['label' => 'Tedarik Aşamasında', 'class' => 'badge-warning', 'icon' => '🚚'],
        'in_procurement' => ['label' => 'Tedarik Aşamasında', 'class' => 'badge-warning', 'icon' => '🚚'],
        // Risk & İş Akışı Ek Durumları
        'open' => ['label' => 'Açık', 'class' => 'badge-info', 'icon' => '🔓'],
        'identified' => ['label' => 'Tespit Edildi', 'class' => 'badge-info', 'icon' => '🔍'],
        'mitigated' => ['label' => 'Hafifletildi', 'class' => 'badge-success', 'icon' => '🛡️'],
        'closed' => ['label' => 'Kapatıldı', 'class' => 'badge-success', 'icon' => '✓'],
        'accepted' => ['label' => 'Kabul Edildi', 'class' => 'badge-secondary', 'icon' => '🤝'],
        'active' => ['label' => 'Aktif', 'class' => 'badge-success', 'icon' => '✓'],
        'inactive' => ['label' => 'Pasif', 'class' => 'badge-dark', 'icon' => '⏸'],
        // Ciddiyet / Öncelik Fallback
        'high' => ['label' => 'Yüksek', 'class' => 'badge-danger', 'icon' => '▲'],
        'medium' => ['label' => 'Orta', 'class' => 'badge-warning', 'icon' => '■'],
        'low' => ['label' => 'Düşük', 'class' => 'badge-success', 'icon' => '▼'],
    ];

    $key = strtolower(trim((string) $status));
    $cfg = $statusMap[$key] ?? ['label' => ucfirst(str_replace('_', ' ', (string)$status)), 'class' => 'badge-secondary', 'icon' => '•'];
@endphp

<span class="status-badge {{ $cfg['class'] }}">
    <span class="badge-icon">{{ $cfg['icon'] }}</span>
    <span>{{ $cfg['label'] }}</span>
</span>
