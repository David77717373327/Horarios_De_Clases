{{--
    Componente: alert
    Uso:
        <x-alert type="success" id="alert-success" title="Éxito" :message="session('success')" />
        <x-alert type="error"   id="alert-error"   title="Error" :message="session('error')" />
--}}
@props([
    'type'    => 'success',  {{-- success | error --}}
    'id',
    'title',
    'message' => null,
    'list'    => null,
])

@php
    $isSuccess = $type === 'success';
    $iconPath  = $isSuccess
        ? '<path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/>'
        : '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>';
@endphp

<div class="alert-preline alert-preline-{{ $type }}" id="{{ $id }}" role="alert">
    <div class="alert-preline-icon-wrap alert-preline-icon-{{ $type }}">
        <svg class="alert-preline-svg" xmlns="http://www.w3.org/2000/svg"
            width="24" height="24" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            {!! $iconPath !!}
        </svg>
    </div>
    <div class="alert-preline-body">
        <h3 class="alert-preline-title">{{ $title }}</h3>
        @if($message)
            <p class="alert-preline-msg">{{ $message }}</p>
        @endif
        @if($list)
            <ul class="alert-preline-list">
                @foreach($list as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        @endif
    </div>
    <button class="alert-preline-close" onclick="hideAlert('{{ $id }}')" aria-label="Cerrar">
        <i class="fas fa-times"></i>
    </button>
</div>
