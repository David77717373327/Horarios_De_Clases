@extends('layouts.master')

@section('title', 'Gestión de Profesores')

@section('content')
<div class="container py-4">
    {{-- Header --}}
    <div class="mb-4">
        <h2 class="fw-bold mb-1" style="color: #000000;">Gestión de Profesores</h2>
        <p class="text-muted mb-0" style="font-size: 0.95rem;">Administra los profesores y sus asignaturas asignadas</p>
    </div>

    {{-- Contenedor de alertas dinámicas --}}
    <div id="alert-container"></div>

    {{-- Header con botón --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0 fw-semibold" style="color: #000000; font-size: 1.1rem;">Lista de Profesores</h5>
            <small class="text-muted" style="font-size: 0.85rem;">{{ $profesores->count() }} {{ $profesores->count() === 1 ? 'profesor registrado' : 'profesores registrados' }}</small>
        </div>
        <button onclick="abrirModalCrear()" class="btn fw-semibold d-inline-flex align-items-center gap-2" 
                style="background-color: #1e40af; color: #ffffff; padding: 0.675rem 1.5rem; border: none; font-size: 0.95rem; border-radius: 0.375rem;">
            <i class="fas fa-plus-circle"></i>
            Nuevo Profesor
        </button>
    </div>

    {{-- Tabla de profesores --}}
    <div class="card border-0 shadow-sm" style="border: 1px solid #e5e7eb;">
        <div class="card-header bg-white border-bottom" style="border-color: #e5e7eb !important; padding: 1.25rem 1.5rem;">
            <h5 class="mb-0 fw-semibold" style="color: #000000; font-size: 1.05rem;">Profesores Registrados</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size: 0.95rem;">
                    <thead style="background-color: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                        <tr>
                            <th class="fw-semibold" style="color: #374151; padding: 1rem 1.5rem; width: 100px;">ID</th>
                            <th class="fw-semibold" style="color: #374151; padding: 1rem 1.5rem;">Nombre del Profesor</th>
                            <th class="fw-semibold" style="color: #374151; padding: 1rem 1.5rem;">Asignaturas</th>
                            <th class="fw-semibold text-center" style="color: #374151; padding: 1rem 1.5rem; width: 180px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaProfesores">
                        @forelse($profesores as $profesor)
                            <tr style="border-bottom: 1px solid #f3f4f6;" data-id="{{ $profesor->id }}">
                                <td class="align-middle" style="color: #6b7280; padding: 1rem 1.5rem; font-weight: 500;">
                                    {{ $profesor->id }}
                                </td>
                                <td class="align-middle" style="color: #000000; padding: 1rem 1.5rem; font-weight: 600;">
                                    {{ $profesor->name }}
                                </td>
                                <td class="align-middle" style="padding: 1rem 1.5rem;">
                                    <div class="d-flex flex-wrap gap-2">
                                        @forelse($profesor->asignaturas as $asignatura)
                                            <span class="badge" style="background-color: #dbeafe; color: #1e40af; padding: 0.375rem 0.75rem; font-weight: 500; font-size: 0.8rem; border-radius: 4px;">
                                                {{ $asignatura->nombre }}
                                            </span>
                                        @empty
                                            <span class="badge" style="background-color: #fef3c7; color: #92400e; padding: 0.375rem 0.75rem; font-weight: 500; font-size: 0.8rem; border-radius: 4px; font-style: italic;">
                                                Sin asignaturas
                                            </span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="align-middle text-center" style="padding: 1rem 1.5rem;">
                                    <div class="btn-group" role="group">
                                        {{-- Botón ASIGNAR color verde --}}
                                        <button 
                                            type="button"
                                            class="btn btn-sm btn-asignar"
                                            onclick="abrirModalAsignaturas({{ $profesor->id }})"
                                            title="Asignar asignaturas">
                                            <i class="fas fa-book"></i>
                                        </button>
                                        
                                        {{-- Botón EDITAR --}}
                                        <button 
                                            type="button"
                                            class="btn btn-sm btn-editar"
                                            onclick="abrirModalEditar({{ $profesor->id }})"
                                            title="Editar profesor">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        {{-- Botón ELIMINAR --}}
                                        <button 
                                            type="button"
                                            class="btn btn-sm btn-eliminar"
                                            onclick='confirmarEliminar({{ $profesor->id }}, "{{ addslashes($profesor->name) }}")'
                                            title="Eliminar profesor">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="filaVacia">
                                <td colspan="4" class="text-center" style="padding: 3rem; color: #9ca3af;">
                                    <i class="fas fa-users fa-3x mb-3 opacity-50"></i>
                                    <p class="mb-0 fw-medium" style="font-size: 1rem;">No hay profesores registrados</p>
                                    <small class="text-muted">Comienza agregando tu primer profesor</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL CREAR PROFESOR (MODAL-LG CON GRID) --}}
<div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border: none; border-radius: 0.75rem; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            <div class="modal-header" style="background-color: #f9fafb; border-bottom: 2px solid #e5e7eb; padding: 1.5rem;">
                <h5 class="modal-title fw-bold" style="color: #1e40af;">
                    <i class="fas fa-plus-circle me-2"></i>Nuevo Profesor
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formCrear">
                @csrf
                <div class="modal-body" style="padding: 2rem;">
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="color: #374151; font-size: 0.95rem;">
                            <i class="fas fa-user me-2" style="color: #6b7280;"></i>Nombre Completo
                        </label>
                        <input 
                            type="text" 
                            name="name" 
                            id="crear_name"
                            class="form-control form-control-lg"
                            placeholder="Ej: Juan Pérez García"
                            style="border: 2px solid #d1d5db; padding: 0.75rem 1rem; font-size: 1rem; border-radius: 0.5rem;"
                            required>
                        <div class="invalid-feedback" id="error_crear_name"></div>
                        <small class="text-muted d-block mt-2" style="font-size: 0.85rem;">
                            <i class="fas fa-info-circle me-1"></i>Ingresa el nombre completo del profesor
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: #374151; font-size: 0.95rem;">
                            <i class="fas fa-book me-2" style="color: #6b7280;"></i>Asignaturas
                        </label>
                        <div id="crear_asignaturas_list" style="max-height: 300px; overflow-y: auto; padding: 1rem; background-color: #f9fafb; border-radius: 0.5rem; border: 1px solid #e5e7eb;">
                            <div class="row">
                                @foreach($asignaturas as $asignatura)
                                    <div class="col-md-6 mb-2">
                                        <label class="checkbox-label-grid">
                                            <input 
                                                type="checkbox" 
                                                name="asignaturas[]" 
                                                value="{{ $asignatura->id }}"
                                                id="crear_asig_{{ $asignatura->id }}">
                                            <span>{{ $asignatura->nombre }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2" style="font-size: 0.85rem;">
                            <i class="fas fa-info-circle me-1"></i>Selecciona las asignaturas que impartirá
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
                        type="button" 
                        onclick="crearProfesor()"
                        class="btn fw-semibold"
                        id="btn-crear"
                        style="background-color: #2563eb; color: #ffffff; padding: 0.625rem 1.5rem; border: none;">
                        <i class="fas fa-save me-2"></i>Guardar Profesor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL EDITAR PROFESOR (ANCHO GRANDE CON GRID - AZUL) --}}
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border: none; border-radius: 0.75rem; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            <div class="modal-header" style="background-color: #f9fafb; border-bottom: 2px solid #e5e7eb; padding: 1.5rem;">
                <h5 class="modal-title fw-bold" style="color: #1e40af;">
                    <i class="fas fa-edit me-2"></i>Editar Profesor
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditar">
                @csrf
                <input type="hidden" id="editar_id">
                <div class="modal-body" style="padding: 2rem;">
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="color: #374151; font-size: 0.95rem;">
                            <i class="fas fa-user me-2" style="color: #6b7280;"></i>Nombre Completo
                        </label>
                        <input 
                            type="text" 
                            name="name" 
                            id="editar_name"
                            class="form-control form-control-lg"
                            style="border: 2px solid #d1d5db; padding: 0.75rem 1rem; font-size: 1rem; border-radius: 0.5rem;"
                            required>
                        <div class="invalid-feedback" id="error_editar_name"></div>
                        <small class="text-muted d-block mt-2" style="font-size: 0.85rem;">
                            <i class="fas fa-info-circle me-1"></i>Modifica el nombre del profesor
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: #374151; font-size: 0.95rem;">
                            <i class="fas fa-book me-2" style="color: #6b7280;"></i>Asignaturas
                        </label>
                        <div id="editar_asignaturas_list" style="max-height: 300px; overflow-y: auto; padding: 1rem; background-color: #f9fafb; border-radius: 0.5rem; border: 1px solid #e5e7eb;">
                            <!-- Se llenará dinámicamente con grid -->
                        </div>
                        <small class="text-muted d-block mt-2" style="font-size: 0.85rem;">
                            <i class="fas fa-info-circle me-1"></i>Modifica las asignaturas del profesor
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
                        type="button"
                        onclick="actualizarProfesor()" 
                        class="btn fw-semibold"
                        id="btn-editar"
                        style="background-color: #2563eb; color: #ffffff; padding: 0.625rem 1.5rem; border: none;">
                        <i class="fas fa-save me-2"></i>Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL ASIGNAR ASIGNATURAS (ANCHO GRANDE CON GRID - AZUL) --}}
<div class="modal fade" id="modalAsignaturas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border: none; border-radius: 0.75rem; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            <div class="modal-header" style="background-color: #f9fafb; border-bottom: 2px solid #e5e7eb; padding: 1.5rem;">
                <h5 class="modal-title fw-bold" style="color: #1e40af;">
                    <i class="fas fa-book me-2"></i>Asignar Asignaturas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAsignarAsignaturas">
                @csrf
                <input type="hidden" id="asignar_profesor_id">
                <div class="modal-body" style="padding: 2rem;">
                    <p style="margin-bottom: 1.25rem; color: #6b7280; font-size: 0.95rem;">
                        <i class="fas fa-info-circle me-2"></i>Selecciona las asignaturas para este profesor:
                    </p>
                    <div id="listaAsignaturas" style="max-height: 350px; overflow-y: auto; padding: 1rem; background-color: #f9fafb; border-radius: 0.5rem; border: 1px solid #e5e7eb;">
                        <!-- Se llenará dinámicamente con grid -->
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
                        type="button"
                        onclick="guardarAsignaturas()" 
                        class="btn fw-semibold"
                        id="btn-asignar"
                        style="background-color: #2563eb; color: #ffffff; padding: 0.625rem 1.5rem; border: none;">
                        <i class="fas fa-save me-2"></i>Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL CONFIRMAR ELIMINACIÓN --}}
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: none; border-radius: 0.75rem; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            <div class="modal-header" style="background-color: #fef2f2; border-bottom: 2px solid #fca5a5; padding: 1.5rem;">
                <h5 class="modal-title fw-bold" style="color: #dc2626;">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 2rem; text-align: center;">
                <input type="hidden" id="eliminar_id">
                <i class="fas fa-user-times" style="font-size: 3rem; color: #dc2626; opacity: 0.5; margin-bottom: 1rem;"></i>
                <p style="font-size: 1rem; margin-bottom: 0.5rem; color: #374151;">
                    ¿Estás seguro de eliminar al profesor
                </p>
                <p style="font-size: 1.1rem; font-weight: 600; color: #dc2626; margin-bottom: 1rem;">
                    <strong id="eliminar_nombre"></strong>?
                </p>
                <div style="background-color: #fef2f2; padding: 1rem; border-radius: 0.5rem; border-left: 4px solid #dc2626;">
                    <p style="margin: 0; color: #991b1b; font-size: 0.9rem;">
                        <i class="fas fa-info-circle me-2"></i>Esta acción no se puede deshacer
                    </p>
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
                    type="button"
                    onclick="eliminarProfesor()" 
                    class="btn fw-semibold"
                    id="btn-eliminar"
                    style="background-color: #dc2626; color: #ffffff; padding: 0.625rem 1.5rem; border: none;">
                    <i class="fas fa-trash me-2"></i>Eliminar Definitivamente
                </button>
            </div>
        </div>
    </div>
</div>

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

/* Boton de colro verde */
.btn-asignar {
    background-color: #16a34a;
    color: #ffffff;
    border: none;
    padding: 0.5rem 0.75rem;
    font-size: 0.95rem;
    border-radius: 0.375rem;
}

.btn-asignar:hover {
    background-color: #15803d !important;
    color: #ffffff;
}

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

/* Checkboxes hover */
.form-check:hover {
    background-color: #eff6ff;
}

/* Estilo para grid de checkboxes (crear, editar y asignar) */
.checkbox-label-grid {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.75rem;
    background-color: #ffffff;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
    cursor: pointer;
    transition: all 0.2s ease;
    margin: 0;
}

.checkbox-label-grid:hover {
    border-color: #2563eb;
    background-color: #eff6ff;
}

.checkbox-label-grid input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #2563eb;
    margin: 0;
}

.checkbox-label-grid span {
    font-size: 0.85rem;
    color: #374151;
    font-weight: 500;
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

/* Scrollbar personalizado */
div[style*="overflow-y"]::-webkit-scrollbar {
    width: 6px;
}

div[style*="overflow-y"]::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}

div[style*="overflow-y"]::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

div[style*="overflow-y"]::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Alertas - EXACTAMENTE IGUAL QUE ASIGNATURAS */
.alert {
    border-radius: 0.5rem;
    animation: slideInDown 0.3s ease-out;
    margin-bottom: 1rem;
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

.alert-success {
    animation: slideInDown 0.3s ease-out, fadeOut 0.5s ease-out 4.5s forwards;
}

@keyframes fadeOut {
    to {
        opacity: 0;
        transform: translateY(-10px);
    }
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
    const todasAsignaturas = @json($asignaturas);
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Función para mostrar alertas (IGUAL QUE ASIGNATURAS)
    function mostrarAlerta(tipo, mensaje) {
        const alertContainer = document.getElementById('alert-container');
        
        let icono = '';
        let titulo = '';
        let bgColor = '';
        let borderColor = '';
        let iconColor = '';
        let titleColor = '';
        let textColor = '';
        
        if (tipo === 'success') {
            icono = 'fa-check-circle';
            titulo = '¡Éxito!';
            bgColor = '#f0fdf4';
            borderColor = '#86efac';
            iconColor = '#10b981';
            titleColor = '#065f46';
            textColor = '#047857';
        } else if (tipo === 'error') {
            icono = 'fa-exclamation-circle';
            titulo = 'Error';
            bgColor = '#fef2f2';
            borderColor = '#fca5a5';
            iconColor = '#ef4444';
            titleColor = '#991b1b';
            textColor = '#dc2626';
        }
        
        const alert = document.createElement('div');
        alert.className = `alert alert-${tipo === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        alert.setAttribute('role', 'alert');
        alert.style.cssText = `border-left: 4px solid ${iconColor}; background-color: ${bgColor}; border-color: ${borderColor};`;
        
        alert.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas ${icono} me-3" style="color: ${iconColor}; font-size: 1.5rem;"></i>
                <div>
                    <strong style="color: ${titleColor};">${titulo}</strong>
                    <p class="mb-0" style="color: ${textColor};">${mensaje}</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        alertContainer.innerHTML = '';
        alertContainer.appendChild(alert);
        
        // Auto-cerrar después de 5 segundos
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    }

    // Función para limpiar errores
    function limpiarErrores(prefijo) {
        const errorEl = document.getElementById(`error_${prefijo}_name`);
        const inputEl = document.getElementById(`${prefijo}_name`);
        if (errorEl) errorEl.textContent = '';
        if (inputEl) inputEl.classList.remove('is-invalid');
    }

    // Función para mostrar errores
    function mostrarErrores(errores, prefijo) {
        Object.keys(errores).forEach(campo => {
            const errorEl = document.getElementById(`error_${prefijo}_${campo}`);
            const inputEl = document.getElementById(`${prefijo}_${campo}`);
            if (errorEl) {
                errorEl.textContent = errores[campo][0];
                errorEl.style.display = 'block';
                if (inputEl) inputEl.classList.add('is-invalid');
            }
        });
    }

    // Abrir modal de creación
    function abrirModalCrear() {
        document.getElementById('formCrear').reset();
        limpiarErrores('crear');
        const modal = new bootstrap.Modal(document.getElementById('modalCrear'));
        modal.show();
        
        setTimeout(() => {
            document.getElementById('crear_name').focus();
        }, 300);
    }

    // Crear profesor
    async function crearProfesor() {
        const form = document.getElementById('formCrear');
        const formData = new FormData(form);
        
        const asignaturas = [];
        form.querySelectorAll('input[name="asignaturas[]"]:checked').forEach(cb => {
            asignaturas.push(parseInt(cb.value));
        });

        const data = {
            name: formData.get('name'),
            asignaturas: asignaturas
        };

        const btnGuardar = document.getElementById('btn-crear');
        const textoOriginal = btnGuardar.innerHTML;
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';

        try {
            const response = await fetch('/profesores', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok && result.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalCrear'));
                modal.hide();
                mostrarAlerta('success', result.message || 'Profesor creado correctamente');
                agregarProfesorATabla(result.profesor);
            } else {
                if (result.errors) {
                    mostrarErrores(result.errors, 'crear');
                } else {
                    mostrarAlerta('error', result.message || 'Error al crear el profesor');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarAlerta('error', 'Error al crear el profesor');
        } finally {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = textoOriginal;
        }
    }

    // Abrir modal de edición (CON GRID)
    async function abrirModalEditar(id) {
        try {
            const response = await fetch(`/profesores/${id}`);
            const result = await response.json();

            if (result.success) {
                const profesor = result.profesor;
                
                document.getElementById('editar_id').value = profesor.id;
                document.getElementById('editar_name').value = profesor.name;

                const container = document.getElementById('editar_asignaturas_list');
                container.innerHTML = '';
                
                const asignaturasIds = profesor.asignaturas.map(a => a.id);
                
                // Crear grid con row y columnas
                const row = document.createElement('div');
                row.className = 'row';
                
                todasAsignaturas.forEach(asignatura => {
                    const isChecked = asignaturasIds.includes(asignatura.id);
                    const col = document.createElement('div');
                    col.className = 'col-md-6 mb-2';
                    
                    const label = document.createElement('label');
                    label.className = 'checkbox-label-grid';
                    label.innerHTML = `
                        <input 
                            type="checkbox" 
                            name="asignaturas[]"
                            value="${asignatura.id}"
                            ${isChecked ? 'checked' : ''}
                            id="editar_asig_${asignatura.id}">
                        <span>${asignatura.nombre}</span>
                    `;
                    col.appendChild(label);
                    row.appendChild(col);
                });
                
                container.appendChild(row);

                limpiarErrores('editar');
                const modal = new bootstrap.Modal(document.getElementById('modalEditar'));
                modal.show();
                
                setTimeout(() => {
                    document.getElementById('editar_name').focus();
                    document.getElementById('editar_name').select();
                }, 300);
            } else {
                mostrarAlerta('error', 'Error al cargar datos del profesor');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarAlerta('error', 'Error al cargar datos del profesor');
        }
    }

    // Actualizar profesor
    async function actualizarProfesor() {
        const id = document.getElementById('editar_id').value;
        const name = document.getElementById('editar_name').value;
        
        const asignaturas = [];
        document.querySelectorAll('#editar_asignaturas_list input[name="asignaturas[]"]:checked').forEach(cb => {
            asignaturas.push(parseInt(cb.value));
        });

        const data = {
            name: name,
            asignaturas: asignaturas
        };

        const btnGuardar = document.getElementById('btn-editar');
        const textoOriginal = btnGuardar.innerHTML;
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';

        try {
            const response = await fetch(`/profesores/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok && result.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditar'));
                modal.hide();
                mostrarAlerta('success', result.message || 'Profesor actualizado correctamente');
                actualizarProfesorEnTabla(result.profesor);
            } else {
                if (result.errors) {
                    mostrarErrores(result.errors, 'editar');
                } else {
                    mostrarAlerta('error', result.message || 'Error al actualizar el profesor');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarAlerta('error', 'Error al actualizar el profesor');
        } finally {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = textoOriginal;
        }
    }

    // Abrir modal de asignar asignaturas (CON GRID)
    async function abrirModalAsignaturas(id) {
        try {
            const response = await fetch(`/profesores/${id}`);
            const result = await response.json();

            if (result.success) {
                const profesor = result.profesor;
                document.getElementById('asignar_profesor_id').value = profesor.id;

                const container = document.getElementById('listaAsignaturas');
                container.innerHTML = '';
                
                const asignaturasIds = profesor.asignaturas.map(a => a.id);
                
                // Crear grid con row y columnas
                const row = document.createElement('div');
                row.className = 'row';
                
                todasAsignaturas.forEach(asignatura => {
                    const isChecked = asignaturasIds.includes(asignatura.id);
                    const col = document.createElement('div');
                    col.className = 'col-md-6 mb-2';
                    
                    const label = document.createElement('label');
                    label.className = 'checkbox-label-grid';
                    label.innerHTML = `
                        <input 
                            type="checkbox" 
                            name="asignaturas[]"
                            value="${asignatura.id}"
                            ${isChecked ? 'checked' : ''}
                            id="asignar_asig_${asignatura.id}">
                        <span>${asignatura.nombre}</span>
                    `;
                    col.appendChild(label);
                    row.appendChild(col);
                });
                
                container.appendChild(row);

                const modal = new bootstrap.Modal(document.getElementById('modalAsignaturas'));
                modal.show();
            } else {
                mostrarAlerta('error', 'Error al cargar asignaturas');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarAlerta('error', 'Error al cargar asignaturas');
        }
    }

    // Guardar asignaturas
    async function guardarAsignaturas() {
        const id = document.getElementById('asignar_profesor_id').value;
        
        const asignaturas = [];
        document.querySelectorAll('#listaAsignaturas input[name="asignaturas[]"]:checked').forEach(cb => {
            asignaturas.push(parseInt(cb.value));
        });

        const nombre = document.querySelector(`tr[data-id="${id}"] td:nth-child(2)`).textContent.trim();

        const data = {
            name: nombre,
            asignaturas: asignaturas
        };

        const btnGuardar = document.getElementById('btn-asignar');
        const textoOriginal = btnGuardar.innerHTML;
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';

        try {
            const response = await fetch(`/profesores/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok && result.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalAsignaturas'));
                modal.hide();
                mostrarAlerta('success', 'Asignaturas actualizadas correctamente');
                actualizarProfesorEnTabla(result.profesor);
            } else {
                mostrarAlerta('error', result.message || 'Error al asignar asignaturas');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarAlerta('error', 'Error al asignar asignaturas');
        } finally {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = textoOriginal;
        }
    }

    // Confirmar eliminación
    function confirmarEliminar(id, nombre) {
        document.getElementById('eliminar_id').value = id;
        document.getElementById('eliminar_nombre').textContent = nombre;
        const modal = new bootstrap.Modal(document.getElementById('modalEliminar'));
        modal.show();
    }

    // Eliminar profesor
    async function eliminarProfesor() {
        const id = document.getElementById('eliminar_id').value;

        const btnEliminar = document.getElementById('btn-eliminar');
        const textoOriginal = btnEliminar.innerHTML;
        btnEliminar.disabled = true;
        btnEliminar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Eliminando...';

        try {
            const response = await fetch(`/profesores/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const result = await response.json();

            if (result.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalEliminar'));
                modal.hide();
                mostrarAlerta('success', result.message || 'Profesor eliminado correctamente');
                eliminarProfesorDeTabla(id);
            } else {
                mostrarAlerta('error', result.message || 'Error al eliminar el profesor');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarAlerta('error', 'Error al eliminar el profesor');
        } finally {
            btnEliminar.disabled = false;
            btnEliminar.innerHTML = textoOriginal;
        }
    }

    // Agregar profesor a la tabla
    function agregarProfesorATabla(profesor) {
        const tbody = document.getElementById('tablaProfesores');
        const filaVacia = document.getElementById('filaVacia');
        if (filaVacia) filaVacia.remove();

        const asignaturasHTML = profesor.asignaturas.length > 0
            ? profesor.asignaturas.map(a => `<span class="badge" style="background-color: #dbeafe; color: #1e40af; padding: 0.375rem 0.75rem; font-weight: 500; font-size: 0.8rem; border-radius: 4px;">${a.nombre}</span>`).join(' ')
            : '<span class="badge" style="background-color: #fef3c7; color: #92400e; padding: 0.375rem 0.75rem; font-weight: 500; font-size: 0.8rem; border-radius: 4px; font-style: italic;">Sin asignaturas</span>';

        const nombreEscapado = profesor.name.replace(/'/g, "\\'");

        const tr = document.createElement('tr');
        tr.setAttribute('data-id', profesor.id);
        tr.style.borderBottom = '1px solid #f3f4f6';
        tr.innerHTML = `
            <td class="align-middle" style="color: #6b7280; padding: 1rem 1.5rem; font-weight: 500;">${profesor.id}</td>
            <td class="align-middle" style="color: #000000; padding: 1rem 1.5rem; font-weight: 600;">${profesor.name}</td>
            <td class="align-middle" style="padding: 1rem 1.5rem;">
                <div class="d-flex flex-wrap gap-2">${asignaturasHTML}</div>
            </td>
            <td class="align-middle text-center" style="padding: 1rem 1.5rem;">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-asignar" onclick="abrirModalAsignaturas(${profesor.id})" title="Asignar asignaturas">
                        <i class="fas fa-book"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-editar" onclick="abrirModalEditar(${profesor.id})" title="Editar profesor">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-eliminar" onclick='confirmarEliminar(${profesor.id}, "${nombreEscapado}")' title="Eliminar profesor">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.insertBefore(tr, tbody.firstChild);
    }

    // Actualizar profesor en la tabla
    function actualizarProfesorEnTabla(profesor) {
        const tr = document.querySelector(`tr[data-id="${profesor.id}"]`);
        if (!tr) return;

        const asignaturasHTML = profesor.asignaturas.length > 0
            ? profesor.asignaturas.map(a => `<span class="badge" style="background-color: #dbeafe; color: #1e40af; padding: 0.375rem 0.75rem; font-weight: 500; font-size: 0.8rem; border-radius: 4px;">${a.nombre}</span>`).join(' ')
            : '<span class="badge" style="background-color: #fef3c7; color: #92400e; padding: 0.375rem 0.75rem; font-weight: 500; font-size: 0.8rem; border-radius: 4px; font-style: italic;">Sin asignaturas</span>';

        const nombreEscapado = profesor.name.replace(/'/g, "\\'");

        tr.innerHTML = `
            <td class="align-middle" style="color: #6b7280; padding: 1rem 1.5rem; font-weight: 500;">${profesor.id}</td>
            <td class="align-middle" style="color: #000000; padding: 1rem 1.5rem; font-weight: 600;">${profesor.name}</td>
            <td class="align-middle" style="padding: 1rem 1.5rem;">
                <div class="d-flex flex-wrap gap-2">${asignaturasHTML}</div>
            </td>
            <td class="align-middle text-center" style="padding: 1rem 1.5rem;">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-asignar" onclick="abrirModalAsignaturas(${profesor.id})" title="Asignar asignaturas">
                        <i class="fas fa-book"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-editar" onclick="abrirModalEditar(${profesor.id})" title="Editar profesor">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-eliminar" onclick='confirmarEliminar(${profesor.id}, "${nombreEscapado}")' title="Eliminar profesor">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </td>
        `;
    }

    // Eliminar profesor de la tabla
    function eliminarProfesorDeTabla(id) {
        const tr = document.querySelector(`tr[data-id="${id}"]`);
        if (tr) tr.remove();

        const tbody = document.getElementById('tablaProfesores');
        if (tbody.children.length === 0) {
            const tr = document.createElement('tr');
            tr.id = 'filaVacia';
            tr.innerHTML = `
                <td colspan="4" class="text-center" style="padding: 3rem; color: #9ca3af;">
                    <i class="fas fa-users fa-3x mb-3 opacity-50"></i>
                    <p class="mb-0 fw-medium" style="font-size: 1rem;">No hay profesores registrados</p>
                    <small class="text-muted">Comienza agregando tu primer profesor</small>
                </td>
            `;
            tbody.appendChild(tr);
        }
    }
</script>

@endsection