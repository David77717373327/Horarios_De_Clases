@props([
    'name',
    'label'       => '',
    'icon'        => '',
    'placeholder' => '',
    'value'       => '',
    'type'        => 'text',
    'required'    => false,
    'id'          => null,
])

@php $inputId = $id ?? $name; @endphp

@if($label)
    <label class="form-label-custom" for="{{ $inputId }}">{{ $label }}</label>
@endif

<div class="input-wrapper">
    @if($icon)
        <i class="fas {{ $icon }} input-icon"></i>
    @endif
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $inputId }}"
        class="form-control-custom {{ !$icon ? 'no-icon' : '' }} @error($name) border-red @enderror"
        placeholder="{{ $placeholder }}"
        value="{{ old($name, $value) }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes }}>
</div>

@error($name)
    <small class="field-error">
        <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
    </small>
@enderror