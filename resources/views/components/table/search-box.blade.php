@props([
    'id'          => 'buscador',
    'placeholder' => 'Buscar...',
])

<div class="search-box">
    <span class="search-box-icon">
        <i class="fas fa-search"></i>
    </span>
    <input
        type="text"
        id="{{ $id }}"
        placeholder="{{ $placeholder }}">
</div>