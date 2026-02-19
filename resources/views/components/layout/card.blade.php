@props([
    'titulo',
    'subtitulo' => '',
    'icon'      => 'fa-plus',
])

<div class="card-custom">
    <div class="card-header-custom">
        <div class="card-header-icon">
            <i class="fas {{ $icon }}"></i>
        </div>
        <div class="card-header-text">
            <h5>{{ $titulo }}</h5>
            @if($subtitulo)
                <p>{{ $subtitulo }}</p>
            @endif
        </div>
    </div>
    <div class="card-body-custom">
        {{ $slot }}
    </div>
</div>