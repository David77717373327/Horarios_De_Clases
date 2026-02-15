@extends('layouts.master')

@section('content')
<div class="container py-4">
    {{-- Header --}}
    <div class="mb-4">
        <h2 class="fw-bold mb-1" style="color: #000000;">Niveles Académicos</h2>
        <p class="text-muted mb-0" style="font-size: 0.95rem;">Administra los niveles educativos del sistema</p>
    </div>

    {{-- Mensajes de éxito/error SIN modal --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-left: 4px solid #10b981; background-color: #f0fdf4; border-color: #86efac;">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-3" style="color: #10b981; font-size: 1.5rem;"></i>
                <div>
                    <strong style="color: #065f46;">¡Éxito!</strong>
                    <p class="mb-0" style="color: #047857;">{{ session('success') }}</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-left: 4px solid #ef4444; background-color: #fef2f2; border-color: #fca5a5;">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle me-3" style="color: #ef4444; font-size: 1.5rem;"></i>
                <div>
                    <strong style="color: #991b1b;">Error</strong>
                    <p class="mb-0" style="color: #dc2626;">{{ session('error') }}</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-warning alert-dismissible fade show" role="alert" style="border-left: 4px solid #f59e0b; background-color: #fffbeb; border-color: #fcd34d;">
            <div class="d-flex align-items-start">
                <i class="fas fa-exclamation-triangle me-3 mt-1" style="color: #f59e0b; font-size: 1.5rem;"></i>
                <div>
                    <strong style="color: #92400e;">Errores de validación</strong>
                    <ul class="mb-0 mt-2" style="color: #b45309; padding-left: 1.2rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Grid de dos columnas --}}
    <div class="row g-4">
        {{-- Formulario CREAR --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="border: 1px solid #e5e7eb;">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3" style="color: #000000; font-size: 1.1rem;">
                        <i class="fas fa-plus-circle me-2" style="color: #1e40af;"></i>Crear Nivel
                    </h5>
                    <form method="POST" action="{{ route('niveles.store') }}" id="create-form">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-medium" style="color: #374151; font-size: 0.9rem;">
                                Nombre del Nivel
                            </label>
                            <input 
                                type="text" 
                                name="nombre" 
                                class="form-control @error('nombre') is-invalid @enderror" 
                                placeholder="Ej: Primaria, Secundaria"
                                value="{{ old('nombre') }}"
                                style="border: 1px solid #d1d5db; padding: 0.625rem 0.875rem; font-size: 0.95rem;"
                                required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-2" style="font-size: 0.85rem;">
                                Ingresa un nombre descriptivo y único
                            </small>
                        </div>
                        
                        <button 
                            type="submit" 
                            class="btn w-100 fw-semibold"
                            style="background-color: #1e40af; color: #ffffff; padding: 0.675rem; border: none; font-size: 0.95rem;">
                            <i class="fas fa-save me-2"></i>Guardar Nivel
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Tabla SIN columna de relaciones --}}
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
                                    <th class="fw-semibold" style="color: #374151; padding: 1rem 1.5rem; width: 100px;">ID</th>
                                    <th class="fw-semibold" style="color: #374151; padding: 1rem 1.5rem;">Nombre del Nivel</th>
                                    <th class="fw-semibold text-center" style="color: #374151; padding: 1rem 1.5rem; width: 150px;">Acciones</th>
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
                                        <td class="align-middle text-center" style="padding: 1rem 1.5rem;">
                                            <div class="btn-group" role="group">
                                                {{-- Botón EDITAR --}}
                                                <button 
                                                    type="button"
                                                    class="btn btn-sm btn-editar"
                                                    onclick="openEditModal({{ $nivel->id }}, '{{ $nivel->nombre }}')"
                                                    title="Editar nivel">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                
                                                {{-- Botón ELIMINAR --}}
                                                <button 
                                                    type="button"
                                                    class="btn btn-sm btn-eliminar"
                                                    onclick="confirmDelete({{ $nivel->id }}, '{{ $nivel->nombre }}', {{ $nivel->grados_count }}, {{ $nivel->descansos_count }})"
                                                    title="Eliminar nivel">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center" style="padding: 3rem; color: #9ca3af;">
                                            <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i>
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

{{-- MODAL EDITAR --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: none; border-radius: 0.75rem; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            <div class="modal-header" style="background-color: #f9fafb; border-bottom: 2px solid #e5e7eb; padding: 1.5rem;">
                <h5 class="modal-title fw-bold" style="color: #1e40af;">
                    <i class="fas fa-edit me-2"></i>Editar Nivel Académico
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="edit-form">
                @csrf
                @method('PUT')
                <div class="modal-body" style="padding: 2rem;">
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="color: #374151; font-size: 0.95rem;">
                            <i class="fas fa-tag me-2" style="color: #6b7280;"></i>Nombre del Nivel
                        </label>
                        <input 
                            type="text" 
                            name="nombre" 
                            id="edit-nombre"
                            class="form-control form-control-lg"
                            placeholder="Ej: Primaria, Secundaria"
                            style="border: 2px solid #d1d5db; padding: 0.75rem 1rem; font-size: 1rem; border-radius: 0.5rem;"
                            required>
                        <small class="text-muted d-block mt-2" style="font-size: 0.85rem;">
                            <i class="fas fa-info-circle me-1"></i>Modifica el nombre del nivel educativo
                        </small>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e5e7eb; padding: 1.25rem 1.5rem; background-color: #f9fafb;">
                    <button 
                        type="button" 
                        class="btn btn-secondary fw-semibold"
                        data-bs-dismiss="modal"
                        style="padding: 0.625rem 1.5rem;">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button 
                        type="submit" 
                        class="btn fw-semibold"
                        style="background-color: #2563eb; color: #ffffff; padding: 0.625rem 1.5rem; border: none;">
                        <i class="fas fa-save me-2"></i>Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Formulario oculto para eliminación --}}
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<style>
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

.shadow-sm {
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06) !important;
}

/* Botones con SOLO ÍCONOS */
.btn-editar {
    background-color: #2563eb;
    color: #ffffff;
    border: none;
    padding: 0.5rem 0.75rem;
    font-size: 0.95rem;
    border-radius: 0.375rem;
}

.btn-editar:hover {
    background-color: #1d4ed8 !important;
    color: #ffffff;
}

.btn-eliminar {
    background-color: #dc2626;
    color: #ffffff;
    border: none;
    padding: 0.5rem 0.75rem;
    font-size: 0.95rem;
    border-radius: 0.375rem;
}

.btn-eliminar:hover {
    background-color: #b91c1c !important;
    color: #ffffff;
}

.btn-group {
    gap: 0.5rem;
    display: flex;
}

/* Alertas personalizadas */
.alert {
    border-radius: 0.5rem;
    animation: slideInDown 0.3s ease-out;
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Auto-ocultar alertas después de 5 segundos */
.alert-success {
    animation: slideInDown 0.3s ease-out, fadeOut 0.5s ease-out 4.5s forwards;
}

@keyframes fadeOut {
    to {
        opacity: 0;
        transform: translateY(-10px);
    }
}

/* Modal profesional */
.modal-content {
    animation: modalFadeIn 0.2s ease-out;
}

@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
// Abrir modal de edición
function openEditModal(id, nombre) {
    document.getElementById('edit-nombre').value = nombre;
    document.getElementById('edit-form').action = "{{ url('niveles') }}/" + id;
    
    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
    editModal.show();
    
    setTimeout(() => {
        document.getElementById('edit-nombre').focus();
        document.getElementById('edit-nombre').select();
    }, 300);
}

// Confirmar eliminación con SweetAlert
function confirmDelete(id, nombre, gradosCount, descansosCount) {
    Swal.fire({
        title: '¿Eliminar nivel?',
        html: `
            <div style="text-align: left; margin-top: 1rem;">
                <p style="font-size: 1.05rem; margin-bottom: 1rem;">
                    Nivel: <strong style="color: #1e40af;">${nombre}</strong>
                </p>
                ${gradosCount > 0 || descansosCount > 0 ? `
                <div style="background: #fef2f2; padding: 1rem; border-radius: 0.5rem; border-left: 4px solid #dc2626;">
                    <p style="margin: 0 0 0.5rem 0; color: #991b1b; font-weight: 600;">
                        <i class="fas fa-exclamation-triangle me-2"></i>Se eliminarán permanentemente:
                    </p>
                    <ul style="margin: 0; padding-left: 1.5rem; color: #7f1d1d;">
                        ${gradosCount > 0 ? `<li><strong>${gradosCount}</strong> grado(s) con todos sus horarios</li>` : ''}
                        ${descansosCount > 0 ? `<li><strong>${descansosCount}</strong> descanso(s)</li>` : ''}
                        <li>Todas las asignaciones académicas relacionadas</li>
                    </ul>
                </div>
                ` : `
                <div style="background: #fffbeb; padding: 1rem; border-radius: 0.5rem; border-left: 4px solid #f59e0b;">
                    <p style="margin: 0; color: #92400e;">
                        <i class="fas fa-info-circle me-2"></i>Este nivel no tiene relaciones y se puede eliminar de forma segura.
                    </p>
                </div>
                `}
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-trash me-2"></i>Sí, eliminar',
        cancelButtonText: '<i class="fas fa-times me-2"></i>Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Eliminando nivel...',
                html: '<p style="margin: 1rem 0; color: #6b7280;">Por favor espere mientras se eliminan todos los registros relacionados</p>',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Enviar formulario
            const form = document.getElementById('delete-form');
            form.action = "{{ url('niveles') }}/" + id;
            form.submit();
        }
    });
}

// Prevenir doble submit en formulario de creación
document.getElementById('create-form').addEventListener('submit', function(e) {
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';
});

// Prevenir doble submit en formulario de edición
document.getElementById('edit-form').addEventListener('submit', function(e) {
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';
});

// Auto-cerrar alertas de éxito después de 5 segundos
setTimeout(() => {
    const successAlert = document.querySelector('.alert-success');
    if (successAlert) {
        const bsAlert = new bootstrap.Alert(successAlert);
        bsAlert.close();
    }
}, 5000);
</script>

@endsection