@props(['priority'])

@php
    $priorityMap = [
        'critical' => ['label' => 'Kritik', 'bg' => '#fee2e2', 'color' => '#991b1b', 'border' => '#fecaca', 'icon' => '🔥'],
        'high'     => ['label' => 'Yüksek', 'bg' => '#fee2e2', 'color' => '#b91c1c', 'border' => '#fecaca', 'icon' => '▲'],
        'medium'   => ['label' => 'Orta',   'bg' => '#fef3c7', 'color' => '#92400e', 'border' => '#fde68a', 'icon' => '■'],
        'normal'   => ['label' => 'Normal', 'bg' => '#fef3c7', 'color' => '#92400e', 'border' => '#fde68a', 'icon' => '■'],
        'low'      => ['label' => 'Düşük',  'bg' => '#dcfce7', 'color' => '#166534', 'border' => '#bbf7d0', 'icon' => '▼'],
    ];
    $key = strtolower(trim((string) ($priority ?? 'medium')));
    $cfg = $priorityMap[$key] ?? ['label' => ucfirst($priority ?? 'Orta'), 'bg' => '#f1f5f9', 'color' => '#475569', 'border' => '#cbd5e1', 'icon' => '•'];
@endphp

<span class="priority-badge" style="display: inline-flex; align-items: center; gap: 4px; font-size: 10.5px; font-weight: 700; padding: 2.5px 7.5px; border-radius: 4px; background: {{ $cfg['bg'] }}; color: {{ $cfg['color'] }}; border: 1px solid {{ $cfg['border'] }}; white-space: nowrap; vertical-align: middle;">
    <span style="font-size: 9.5px; line-height: 1;">{{ $cfg['icon'] }}</span>
    <span>{{ $cfg['label'] }}</span>
</span>
