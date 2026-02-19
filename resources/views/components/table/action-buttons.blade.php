@props([
    'editonclick'   => '',
    'deleteOnclick' => '',
    'showEdit'      => true,
    'showDelete'    => true,
])

<div class="actions-group">
    @if($showEdit)
        <button
            class="btn-action btn-edit"
            onclick="{{ $editonclick }}"
            title="Editar">
            <i class="fas fa-pen"></i>
        </button>
    @endif

    @if($showDelete)
        <button
            class="btn-action btn-delete"
            onclick="{{ $deleteOnclick }}"
            title="Eliminar">
            <i class="fas fa-trash"></i>
        </button>
    @endif
</div>