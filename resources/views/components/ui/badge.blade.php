@props([
    'icon'  => 'fa-tag',
    'color' => 'blue',   {{-- blue | green | red | yellow --}}
])

@php
$colores = [
    'blue'   => 'badge-nivel',
    'green'  => 'badge-green',
    'red'    => 'badge-red',
    'yellow' => 'badge-yellow',
];
@endphp

<span class="{{ $colores[$color] ?? 'badge-nivel' }}">
    <i class="fas {{ $icon }}"></i>
    {{ $slot }}
</span>