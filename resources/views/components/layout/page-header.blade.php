@props([
    'titulo',
    'subtitulo' => '',
])

<div class="mb-4">
    <h2 class="page-title">{{ $titulo }}</h2>
    @if($subtitulo)
        <p class="page-subtitle">{{ $subtitulo }}</p>
    @endif
</div>