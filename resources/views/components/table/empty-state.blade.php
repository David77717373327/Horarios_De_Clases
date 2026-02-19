@props([
    'icon'    => 'fa-inbox',
    'titulo'  => 'No hay registros',
    'mensaje' => 'Crea el primer registro usando el formulario.',
    'colspan' => 4,
])

<tr>
    <td colspan="{{ $colspan }}" style="padding: 3rem; text-align: center;">
        <i class="fas {{ $icon }}" style="font-size: 2rem; opacity: 0.2; display: block; margin-bottom: 0.75rem; color: var(--gray-500);"></i>
        <p style="font-size: 0.9rem; font-weight: 600; color: var(--gray-700); margin: 0 0 4px 0;">
            {{ $titulo }}
        </p>
        <small style="font-size: 0.8rem; color: var(--gray-500);">
            {{ $mensaje }}
        </small>
    </td>
</tr>