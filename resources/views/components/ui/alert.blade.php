@props([
    'type' => 'success',   {{-- success | error | warning --}}
    'id'   => 'alert-box',
    'title' => '',
    'autoHide' => true,
])

@php
$tipos = [
    'success' => ['class' => 'alert-success-custom', 'icon' => 'fa-check-circle'],
    'error'   => ['class' => 'alert-error-custom',   'icon' => 'fa-exclamation-circle'],
    'warning' => ['class' => 'alert-warning-custom', 'icon' => 'fa-exclamation-triangle'],
];
$cfg = $tipos[$type] ?? $tipos['success'];
@endphp

<div class="alert-custom {{ $cfg['class'] }}" id="{{ $id }}" @if($autoHide && $type === 'success') data-auto-hide="true" @endif>
    <i class="fas {{ $cfg['icon'] }} fa-lg mt-1"></i>
    <div>
        @if($title)
            <div class="alert-title">{{ $title }}</div>
        @endif
        <div class="alert-msg">{{ $slot }}</div>
    </div>
    <button class="alert-close" onclick="hideAlert('{{ $id }}')">
        <i class="fas fa-times"></i>
    </button>
</div>