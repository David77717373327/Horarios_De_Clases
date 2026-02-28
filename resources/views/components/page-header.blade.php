{{--
    Componente: page-header
    Uso: <x-page-header title="Título" subtitle="Subtítulo" button-label="Nuevo" button-target="#offcanvasCrear" />
--}}
@props([
    'title',
    'subtitle'    => '',
    'buttonLabel' => null,
    'buttonTarget' => null,
    'buttonIcon'  => 'fa-plus',
])

<div class="mb-3">
    <div class="d-flex align-items-center justify-content-between mb-19">
        <div>
            <h2 class="page-title">{{ $title }}</h2>
            @if($subtitle)
                <p class="page-subtitle">{{ $subtitle }}</p>
            @endif
        </div>
        @if($buttonLabel && $buttonTarget)
            <button class="btn-primary-custom" type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="{{ $buttonTarget }}">
                <i class="fas {{ $buttonIcon }}"></i> {{ $buttonLabel }}
            </button>
        @endif
    </div>
    @if(isset($search))
        <div class="search-box w-100">
            <span class="search-box-icon"><i class="fas fa-search"></i></span>
            {{ $search }}
        </div>
    @endif
</div>
