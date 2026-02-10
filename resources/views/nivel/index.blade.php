@extends('layouts.master')

@section('content')
<div class="container py-4">
    {{-- Header con título y descripción --}}
    <div class="mb-4">
        <h2 class="fw-bold mb-1" style="color: #000000;">Niveles Académicos</h2>
        <p class="text-muted mb-0" style="font-size: 0.95rem;">Administra los niveles educativos del sistema</p>
    </div>

    {{-- Mensajes de éxito --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4" style="border-left: 4px solid #10b981 !important;">
            <div class="d-flex align-items-center">
                <svg width="20" height="20" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    {{-- Mensajes de error --}}
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-left: 4px solid #dc2626 !important;">
            <div class="d-flex align-items-center">
                <svg width="20" height="20" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                    <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                </svg>
                {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- Grid de dos columnas: Formulario + Tabla --}}
    <div class="row g-4">
        {{-- Columna: Formulario crear/editar --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="border: 1px solid #e5e7eb;">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3" style="color: #000000; font-size: 1.1rem;" id="form-title">
                        Crear Nivel
                    </h5>
                    <form method="POST" action="{{ route('niveles.store') }}" id="nivel-form">
                        @csrf
                        <input type="hidden" name="_method" value="POST" id="form-method">
                        <input type="hidden" name="nivel_id" id="nivel-id">
                        
                        <div class="mb-3">
                            <label class="form-label fw-medium" style="color: #374151; font-size: 0.9rem;">
                                Nombre del Nivel
                            </label>
                            <input 
                                type="text" 
                                name="nombre" 
                                id="nombre-input"
                                class="form-control @error('nombre') is-invalid @enderror" 
                                placeholder="Ej: Primaria, Secundaria"
                                value="{{ old('nombre') }}"
                                style="border: 1px solid #d1d5db; padding: 0.625rem 0.875rem; font-size: 0.95rem;"
                                required
                                autofocus>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-2" style="font-size: 0.85rem;">
                                Ingresa un nombre descriptivo y único
                            </small>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button 
                                type="submit" 
                                class="btn btn-primary-custom fw-semibold" 
                                id="submit-btn"
                                style="background-color: #1e40af; color: #ffffff; padding: 0.675rem; border: none; font-size: 0.95rem;">
                                Guardar Nivel
                            </button>
                            <button 
                                type="button" 
                                class="btn btn-secondary fw-semibold d-none" 
                                id="cancel-btn"
                                onclick="cancelEdit()"
                                style="background-color: #6b7280; color: #ffffff; padding: 0.675rem; border: none; font-size: 0.95rem;">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Columna: Tabla de niveles --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border: 1px solid #e5e7eb;">
                <div class="card-header bg-white border-bottom" style="border-color: #e5e7eb !important; padding: 1.25rem 1.5rem;">
                    <h5 class="mb-0 fw-semibold" style="color: #000000; font-size: 1.1rem;">Niveles Registrados</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size: 0.95rem;">
                            <thead style="background-color: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                                <tr>
                                    <th class="fw-semibold" style="color: #374151; padding: 1rem 1.5rem; width: 80px;">ID</th>
                                    <th class="fw-semibold" style="color: #374151; padding: 1rem 1.5rem;">Nombre del Nivel</th>
                                    <th class="fw-semibold" style="color: #374151; padding: 1rem 1.5rem; width: 120px;">Relaciones</th>
                                    <th class="fw-semibold text-center" style="color: #374151; padding: 1rem 1.5rem; width: 200px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($niveles as $nivel)
                                    <tr style="border-bottom: 1px solid #f3f4f6;">
                                        <td class="align-middle" style="color: #6b7280; padding: 1rem 1.5rem; font-weight: 500;">
                                            {{ $nivel->id }}
                                        </td>
                                        <td class="align-middle" style="color: #000000; padding: 1rem 1.5rem; font-weight: 500;">
                                            {{ $nivel->nombre }}
                                        </td>
                                        <td class="align-middle" style="padding: 1rem 1.5rem;">
                                            <small class="text-muted">
                                                {{ $nivel->grados_count }} grados<br>
                                                {{ $nivel->descansos_count }} descansos
                                            </small>
                                        </td>
                                        <td class="align-middle text-center" style="padding: 1rem 1.5rem;">
                                            <div class="btn-group" role="group">
                                                {{-- Botón Editar --}}
                                                <button 
                                                    type="button"
                                                    class="btn btn-sm fw-medium"
                                                    style="background-color: #f59e0b; color: #ffffff; border: none; padding: 0.425rem 1rem; font-size: 0.875rem;"
                                                    onclick="editNivel({{ $nivel->id }}, '{{ $nivel->nombre }}')">
                                                    Editar
                                                </button>
                                                
                                                {{-- Botón Eliminar --}}
                                                <form action="{{ route('niveles.destroy', $nivel) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button 
                                                        type="submit"
                                                        class="btn btn-sm fw-medium"
                                                        style="background-color: #dc2626; color: #ffffff; border: none; padding: 0.425rem 1rem; font-size: 0.875rem;"
                                                        onclick="return confirm('¿Está seguro de eliminar este nivel académico?\n\nNota: Tiene {{ $nivel->grados_count }} grados y {{ $nivel->descansos_count }} descansos asociados que también se eliminarán.')">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center" style="padding: 3rem; color: #9ca3af;">
                                            <svg width="48" height="48" fill="currentColor" class="mb-3 mx-auto d-block opacity-50" viewBox="0 0 16 16">
                                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                                <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                            </svg>
                                            <p class="mb-0 fw-medium" style="font-size: 1rem;">No hay niveles académicos registrados</p>
                                            <small class="text-muted">Comienza creando tu primer nivel en el formulario</small>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos adicionales para mejorar la experiencia */
.form-control:focus {
    border-color: #1e40af !important;
    box-shadow: 0 0 0 0.2rem rgba(30, 64, 175, 0.15) !important;
}

.btn:hover {
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.btn:active {
    transform: translateY(0);
}

.table-hover tbody tr:hover {
    background-color: #f9fafb;
}

.card {
    transition: all 0.2s ease;
}

.shadow-sm {
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06) !important;
}

/* Botón de eliminar hover */
button[style*="background-color: #dc2626"]:hover {
    background-color: #b91c1c !important;
}

/* Botón azul de guardar hover */
.btn-primary-custom:hover {
    background-color: #1e3a8a !important;
}

/* Botón editar hover */
button[style*="background-color: #f59e0b"]:hover {
    background-color: #d97706 !important;
}

.btn-group {
    gap: 0.5rem;
    display: flex;
}
</style>

<script>
function editNivel(id, nombre) {
    // Cambiar título del formulario
    document.getElementById('form-title').textContent = 'Editar Nivel';
    
    // Cambiar acción del formulario
    const form = document.getElementById('nivel-form');
    form.action = "{{ url('niveles') }}/" + id;
    
    // Cambiar método a PUT
    document.getElementById('form-method').value = 'PUT';
    
    // Rellenar el campo nombre
    document.getElementById('nombre-input').value = nombre;
    
    // Cambiar texto del botón
    document.getElementById('submit-btn').textContent = 'Actualizar Nivel';
    document.getElementById('submit-btn').style.backgroundColor = '#f59e0b';
    
    // Mostrar botón cancelar
    document.getElementById('cancel-btn').classList.remove('d-none');
    
    // Hacer scroll al formulario
    document.getElementById('nivel-form').scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    // Focus en el input
    document.getElementById('nombre-input').focus();
}

function cancelEdit() {
    // Restaurar título
    document.getElementById('form-title').textContent = 'Crear Nivel';
    
    // Restaurar acción del formulario
    const form = document.getElementById('nivel-form');
    form.action = "{{ route('niveles.store') }}";
    
    // Restaurar método a POST
    document.getElementById('form-method').value = 'POST';
    
    // Limpiar campo
    document.getElementById('nombre-input').value = '';
    
    // Restaurar botón
    document.getElementById('submit-btn').textContent = 'Guardar Nivel';
    document.getElementById('submit-btn').style.backgroundColor = '#1e40af';
    
    // Ocultar botón cancelar
    document.getElementById('cancel-btn').classList.add('d-none');
}
</script>
@endsection