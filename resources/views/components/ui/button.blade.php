@props([
    'type'     => 'submit',
    'variant'  => 'save',   {{-- save | cancel --}}
    'icon'     => 'fa-save',
    'label'    => 'Guardar',
    'loadingLabel' => 'Guardando...',
])

@php
$class = $variant === 'cancel' ? 'btn-modal-cancel' : 'btn-save';
@endphp

<button
    type="{{ $type }}"
    class="{{ $class }}"
    data-loading-label="{{ $loadingLabel }}"
    {{ $attributes }}>
    <i class="fas {{ $icon }}"></i>
    {{ $label }}
</button>