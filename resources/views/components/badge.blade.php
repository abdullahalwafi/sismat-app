@props(['value'])
@php
    $tone = match ($value) {
        'Baik', 'Aktif', 'Completed', 'Selesai', 'Disetujui' => 'green',
        'Rusak ringan', 'Pending', 'Diajukan', 'Menunggu persetujuan' => 'amber',
        'Dihapuskan', 'Rusak berat', 'Tidak aktif', 'Cancelled', 'Dibatalkan', 'Ditolak' => 'red',
        default => 'blue',
    };
@endphp
<span {{ $attributes->class(['status-badge', 'badge-' . $tone]) }}>
    <span class="badge-dot"></span>
    {{ $value }}</span>
