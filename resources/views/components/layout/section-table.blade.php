@props([
    'titulo',
    'subtitulo'   => '',
    'searchId'    => 'buscador',
    'placeholder' => 'Buscar...',
])

<div class="section-gap">
    <div class="table-section-header">
        <div class="table-section-left">
            <h3>{{ $titulo }}</h3>
            @if($subtitulo)
                <p>{{ $subtitulo }}</p>
            @endif
        </div>
        <div class="search-box">
            <span class="search-box-icon">
                <i class="fas fa-search"></i>
            </span>
            <input
                type="text"
                id="{{ $searchId }}"
                placeholder="{{ $placeholder }}">
        </div>
    </div>

    {{ $slot }}

    <div class="dt-footer">
        <div id="dt-info"></div>
        <div id="dt-paginate"></div>
    </div>
</div>