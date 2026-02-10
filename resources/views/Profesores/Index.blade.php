@extends('layouts.master')

@section('content')
<div class="container py-5">
    {{-- Header minimalista sin espacio arriba --}}
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h2 class="fw-bold mb-2" style="color: #000000; font-size: 1.75rem; letter-spacing: -0.5px;">Gestión de Profesores</h2>
                <p class="text-muted mb-0" style="font-size: 0.9rem; color: #6b7280;">Administra los profesores y sus asignaturas asignadas</p>
            </div>
            <button onclick="abrirModalCrear()" class="btn-primary-custom fw-semibold d-inline-flex align-items-center gap-2" 
                    style="background-color: #2563eb; color: #ffffff; padding: 0.75rem 1.5rem; border: none; font-size: 0.9rem; border-radius: 6px; transition: all 0.2s ease;">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                </svg>
                Nuevo Profesor
            </button>
        </div>
    </div>

    {{-- Alerta dinámica --}}
    <div id="alertContainer"></div>

    {{-- Tabla de profesores --}}
    <div class="card-custom">
        <div class="card-header-custom">
            <div>
                <h5 class="mb-1 fw-semibold" style="color: #000000; font-size: 1.05rem;">Profesores Registrados</h5>
                <small style="font-size: 0.85rem; color: #6b7280;">{{ $profesores->count() }} {{ $profesores->count() === 1 ? 'profesor' : 'profesores' }} en el sistema</small>
            </div>
        </div>
        <div class="card-body-custom">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th>Nombre del Profesor</th>
                            <th>Asignaturas</th>
                            <th style="width: 140px; text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaProfesores">
                        @forelse($profesores as $profesor)
                            <tr data-id="{{ $profesor->id }}">
                                <td style="color: #6b7280; font-weight: 500;">{{ $profesor->id }}</td>
                                <td style="color: #000000; font-weight: 600;">{{ $profesor->name }}</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        @forelse($profesor->asignaturas as $asignatura)
                                            <span class="badge-custom">{{ $asignatura->nombre }}</span>
                                        @empty
                                            <span class="badge-empty">Sin asignaturas</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button 
                                            onclick="abrirModalAsignaturas({{ $profesor->id }})"
                                            class="btn-icon btn-assign"
                                            title="Asignar asignaturas">
                                            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" stroke="currentColor" stroke-width="0.3">
                                                <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z"/>
                                            </svg>
                                        </button>
                                        <button 
                                            onclick="abrirModalEditar({{ $profesor->id }})"
                                            class="btn-icon btn-edit"
                                            title="Editar profesor">
                                            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" stroke="currentColor" stroke-width="0.3">
                                                <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
                                            </svg>
                                        </button>
                                        <button 
                                            onclick="abrirModalEliminar({{ $profesor->id }}, '{{ addslashes($profesor->name) }}')"
                                            class="btn-icon btn-delete"
                                            title="Eliminar profesor">
                                            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" stroke="currentColor" stroke-width="0.3">
                                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                                <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr id="filaVacia">
                                <td colspan="4" class="empty-state">
                                    <svg width="48" height="48" fill="currentColor" class="mb-3" viewBox="0 0 16 16" style="opacity: 0.3;">
                                        <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816zM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275zM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                                    </svg>
                                    <p class="mb-1 fw-semibold" style="font-size: 1rem; color: #374151;">No hay profesores registrados</p>
                                    <small style="color: #6b7280;">Comienza agregando un nuevo profesor</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CREAR PROFESOR -->
<div id="modalCrear" class="modal">
    <div class="modal-content-custom">
        <div class="modal-header-custom" style="background-color: #ffffff; color: #000000; border-bottom: 2px solid #e5e7eb;">
            <h5 class="mb-0 fw-semibold d-flex align-items-center gap-2">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                </svg>
                Crear Nuevo Profesor
            </h5>
            <button class="btn-close-modal" onclick="cerrarModal('modalCrear')">&times;</button>
        </div>
        <div class="modal-body-custom">
            <form id="formCrear">
                @csrf
                <div class="form-group-custom">
                    <label class="form-label-custom">Nombre Completo <span style="color: #dc2626;">*</span></label>
                    <input type="text" id="crear_name" name="name" class="form-control-custom" required placeholder="Ej: Juan Pérez García">
                    <div class="error-message" id="error_crear_name"></div>
                </div>

                <div class="form-group-custom">
                    <label class="form-label-custom">Asignaturas</label>
                    <div class="asignaturas-container">
                        @foreach($asignaturas as $asignatura)
                            <label class="checkbox-label-custom">
                                <input 
                                    type="checkbox" 
                                    id="crear_asig_{{ $asignatura->id }}" 
                                    name="asignaturas[]" 
                                    value="{{ $asignatura->id }}"
                                    class="checkbox-custom">
                                <span>{{ $asignatura->nombre }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer-custom">
            <button type="button" class="btn-secondary-modal" onclick="cerrarModal('modalCrear')">Cancelar</button>
            <button type="button" class="btn-primary-modal" onclick="crearProfesor()">Guardar Profesor</button>
        </div>
    </div>
</div>

<!-- MODAL EDITAR PROFESOR -->
<div id="modalEditar" class="modal">
    <div class="modal-content-custom">
        <div class="modal-header-custom" style="background-color: #ffffff; color: #000000; border-bottom: 2px solid #e5e7eb;">
            <h5 class="mb-0 fw-semibold d-flex align-items-center gap-2">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
                </svg>
                Editar Profesor
            </h5>
            <button class="btn-close-modal" onclick="cerrarModal('modalEditar')">&times;</button>
        </div>
        <div class="modal-body-custom">
            <form id="formEditar">
                @csrf
                <input type="hidden" id="editar_id">
                <div class="form-group-custom">
                    <label class="form-label-custom">Nombre Completo <span style="color: #dc2626;">*</span></label>
                    <input type="text" id="editar_name" name="name" class="form-control-custom" required>
                    <div class="error-message" id="error_editar_name"></div>
                </div>
                <div class="form-group-custom">
                    <label class="form-label-custom">Asignaturas</label>
                    <div id="editar_asignaturas_list" class="asignaturas-container"></div>
                </div>
            </form>
        </div>
        <div class="modal-footer-custom">
            <button type="button" class="btn-secondary-modal" onclick="cerrarModal('modalEditar')">Cancelar</button>
            <button type="button" class="btn-primary-modal" onclick="actualizarProfesor()">Actualizar</button>
        </div>
    </div>
</div>

<!-- MODAL ASIGNAR ASIGNATURAS -->
<div id="modalAsignaturas" class="modal">
    <div class="modal-content-custom">
        <div class="modal-header-custom" style="background-color: #ffffff; color: #000000; border-bottom: 2px solid #e5e7eb;">
            <h5 class="mb-0 fw-semibold d-flex align-items-center gap-2">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z"/>
                </svg>
                Asignar Asignaturas
            </h5>
            <button class="btn-close-modal" onclick="cerrarModal('modalAsignaturas')">&times;</button>
        </div>
        <div class="modal-body-custom">
            <form id="formAsignarAsignaturas">
                @csrf
                <input type="hidden" id="asignar_profesor_id">
                <p style="margin-bottom: 1.25rem; color: #6b7280; font-size: 0.9rem;">Selecciona las asignaturas para este profesor:</p>
                <div id="listaAsignaturas" class="asignaturas-container"></div>
            </form>
        </div>
        <div class="modal-footer-custom">
            <button type="button" class="btn-secondary-modal" onclick="cerrarModal('modalAsignaturas')">Cancelar</button>
            <button type="button" class="btn-primary-modal" onclick="guardarAsignaturas()">Guardar Cambios</button>
        </div>
    </div>
</div>

<!-- MODAL ELIMINAR PROFESOR -->
<div id="modalEliminar" class="modal">
    <div class="modal-content-custom" style="max-width: 480px;">
        <div class="modal-header-custom" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: #ffffff; border: none;">
            <h5 class="mb-0 fw-semibold d-flex align-items-center gap-2">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                </svg>
                Confirmar Eliminación
            </h5>
            <button class="btn-close-modal btn-close-modal-white" onclick="cerrarModal('modalEliminar')">&times;</button>
        </div>
        <div class="modal-body-custom" style="text-align: center; padding: 2.5rem 2rem;">
            <input type="hidden" id="eliminar_id">
            <p style="font-size: 1rem; margin: 1rem 0 0.5rem 0; color: #374151;">
                ¿Estás seguro de eliminar al profesor
            </p>
            <p style="font-size: 1.1rem; font-weight: 600; color: #000000; margin: 0 0 1rem 0;">
                <strong id="eliminar_nombre"></strong>?
            </p>
            <p style="font-size: 0.875rem; color: #6b7280; margin: 0;">Esta acción no se puede deshacer.</p>
        </div>
        <div class="modal-footer-custom">
            <button type="button" class="btn-secondary-modal" onclick="cerrarModal('modalEliminar')">Cancelar</button>
            <button type="button" class="btn-danger-modal" onclick="eliminarProfesor()">Eliminar Definitivamente</button>
        </div>
    </div>
</div>

<style>
/* Variables y reset */
* {
    box-sizing: border-box;
}

/* Card personalizada */
.card-custom {
    background-color: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
}

.card-header-custom {
    background-color: #ffffff;
    border-bottom: 2px solid #e5e7eb;
    padding: 1.5rem 1.75rem;
}

.card-body-custom {
    padding: 0;
}

/* Tabla personalizada */
.table-custom {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.table-custom thead tr {
    background-color: #f9fafb;
    border-bottom: 2px solid #e5e7eb;
}

.table-custom thead th {
    color: #374151;
    font-weight: 600;
    padding: 1rem 1.75rem;
    text-align: left;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table-custom tbody tr {
    border-bottom: 1px solid #f3f4f6;
    transition: background-color 0.15s ease;
}

.table-custom tbody tr:hover {
    background-color: #fafbfc;
}

.table-custom tbody td {
    padding: 1.25rem 1.75rem;
    vertical-align: middle;
}

/* Badges */
.badge-custom {
    display: inline-block;
    background-color: #f3f4f6;
    color: #374151;
    padding: 0.375rem 0.75rem;
    font-weight: 500;
    font-size: 0.8rem;
    border-radius: 4px;
    border: 1px solid #e5e7eb;
}

.badge-empty {
    display: inline-block;
    background-color: #fef3c7;
    color: #92400e;
    padding: 0.375rem 0.75rem;
    font-weight: 500;
    font-size: 0.8rem;
    border-radius: 4px;
    border: 1px solid #fde68a;
    font-style: italic;
}

/* Botones de acción - ICONOS OSCUROS CON FONDO */
.action-buttons {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
}

.btn-icon {
    width: 38px;
    height: 38px;
    border: none;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    padding: 0;
}

.btn-icon svg {
    filter: drop-shadow(0 1px 1px rgba(0,0,0,0.1));
}

.btn-icon:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px -1px rgba(0, 0, 0, 0.2);
}




.btn-assign {
    background-color: #16a34a;
    color: #ffffff;
}

.btn-assign:hover {
    background-color: #15803d;
}



.btn-edit {
    background-color: #1e40af;
    color: #ffffff;
}

.btn-edit:hover {
    background-color: #1e3a8a;
}

.btn-delete {
    background-color: #dc2626;
    color: #ffffff;
}

.btn-delete:hover {
    background-color: #b91c1c;
}

/* Estado vacío */
.empty-state {
    text-align: center;
    padding: 4rem 2rem !important;
    color: #9ca3af;
}

/* Modales */
.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.15);
    overflow-y: auto;
    padding: 1rem;
}

.modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content-custom {
    background-color: #ffffff;
    border-radius: 10px;
    width: 90%;
    max-width: 650px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    border: 1px solid #e5e7eb;
}

.modal-header-custom {
    padding: 1.5rem 2rem;
    border-radius: 10px 10px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header-custom h5 {
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0;
}

.btn-close-modal {
    background-color: #e5e7eb;
    border: none;
    color: #374151;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 1.5rem;
    transition: all 0.2s ease;
    line-height: 1;
    padding: 0;
}

.btn-close-modal:hover {
    background-color: #d1d5db;
}

.btn-close-modal-white {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
}

.btn-close-modal-white:hover {
    background: rgba(255, 255, 255, 0.3);
}

.modal-body-custom {
    padding: 2rem;
}

.modal-footer-custom {
    padding: 1.25rem 2rem;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    background-color: #f9fafb;
    border-radius: 0 0 10px 10px;
}

/* Formularios */
.form-group-custom {
    margin-bottom: 1.5rem;
}

.form-label-custom {
    display: block;
    color: #374151;
    font-weight: 500;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.form-control-custom {
    width: 100%;
    border: 1px solid #d1d5db;
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    border-radius: 6px;
    transition: all 0.2s ease;
    color: #000000;
    background-color: #ffffff;
}

.form-control-custom:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    outline: none;
}

.form-control-custom.is-invalid {
    border-color: #dc2626;
}

.error-message {
    color: #dc2626;
    font-size: 0.8rem;
    margin-top: 0.375rem;
    display: block;
}

/* Contenedor de asignaturas */
.asignaturas-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 0.75rem;
    max-height: 320px;
    overflow-y: auto;
    padding: 1rem;
    background-color: #f9fafb;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
}

.checkbox-label-custom {
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

.checkbox-label-custom:hover {
    border-color: #2563eb;
    background-color: #eff6ff;
}

.checkbox-custom {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #2563eb;
    margin: 0;
}

.checkbox-label-custom span {
    font-size: 0.85rem;
    color: #374151;
    font-weight: 500;
}

/* Botones de modales */
.btn-primary-modal,
.btn-secondary-modal,
.btn-danger-modal {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-primary-modal {
    background-color: #2563eb;
    color: #ffffff;
}

.btn-primary-modal:hover {
    background-color: #1d4ed8;
    transform: translateY(-1px);
}

.btn-secondary-modal {
    background-color: #e5e7eb;
    color: #374151;
}

.btn-secondary-modal:hover {
    background-color: #d1d5db;
}

.btn-danger-modal {
    background-color: #dc2626;
    color: #ffffff;
}

.btn-danger-modal:hover {
    background-color: #b91c1c;
    transform: translateY(-1px);
}

/* Botón principal */
.btn-primary-custom:hover {
    background-color: #1d4ed8 !important;
    transform: translateY(-1px);
}

/* Alertas flotantes */
#alertContainer {
    position: fixed;
    top: 2rem;
    right: 2rem;
    z-index: 10000;
    max-width: 380px;
}

.alert {
    padding: 1rem 1.25rem;
    border-radius: 6px;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    animation: slideInRight 0.3s ease;
    font-weight: 500;
    font-size: 0.9rem;
    border: 1px solid;
}

@keyframes slideInRight {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.alert-success {
    background-color: #10b981;
    color: #ffffff;
    border-color: #059669;
}

.alert-danger {
    background-color: #ef4444;
    color: #ffffff;
    border-color: #dc2626;
}

/* Scrollbar personalizado */
.asignaturas-container::-webkit-scrollbar,
.modal-content-custom::-webkit-scrollbar {
    width: 6px;
}

.asignaturas-container::-webkit-scrollbar-track,
.modal-content-custom::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}

.asignaturas-container::-webkit-scrollbar-thumb,
.modal-content-custom::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.asignaturas-container::-webkit-scrollbar-thumb:hover,
.modal-content-custom::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Responsive */
@media (max-width: 768px) {
    .modal-content-custom {
        width: 95%;
        max-height: 95vh;
    }
    
    .asignaturas-container {
        grid-template-columns: 1fr !important;
    }
    
    #alertContainer {
        right: 1rem;
        left: 1rem;
        max-width: none;
    }

    .action-buttons {
        gap: 0.375rem;
    }

    .btn-icon {
        width: 34px;
        height: 34px;
    }

    .table-custom thead th,
    .table-custom tbody td {
        padding: 1rem;
    }
}

@media (max-width: 576px) {
    .card-header-custom {
        padding: 1.25rem 1rem;
    }

    .table-custom thead th,
    .table-custom tbody td {
        padding: 0.875rem;
        font-size: 0.85rem;
    }
}
</style>

@endsection

@section('scripts')
<script>
    const todasAsignaturas = @json($asignaturas);
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function abrirModal(id) {
        document.getElementById(id).classList.add('active');
    }

    function cerrarModal(id) {
        document.getElementById(id).classList.remove('active');
        limpiarErrores(id);
    }

    function limpiarErrores(modalId) {
        const modal = document.getElementById(modalId);
        modal.querySelectorAll('.error-message').forEach(el => el.textContent = '');
        modal.querySelectorAll('.form-control-custom').forEach(el => el.classList.remove('is-invalid'));
    }

    function mostrarErrores(errores, prefijo) {
        Object.keys(errores).forEach(campo => {
            const errorEl = document.getElementById(`error_${prefijo}_${campo}`);
            const inputEl = document.getElementById(`${prefijo}_${campo}`);
            if (errorEl) {
                errorEl.textContent = errores[campo][0];
                if (inputEl) inputEl.classList.add('is-invalid');
            }
        });
    }

    function mostrarAlerta(mensaje, tipo = 'success') {
        const alertContainer = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${tipo}`;
        alert.innerHTML = `${tipo === 'success' ? '✓' : '⚠'} ${mensaje}`;
        alertContainer.appendChild(alert);
        
        setTimeout(() => alert.remove(), 4000);
    }

    function abrirModalCrear() {
        document.getElementById('formCrear').reset();
        limpiarErrores('modalCrear');
        abrirModal('modalCrear');
    }

    async function crearProfesor() {
        const form = document.getElementById('formCrear');
        const formData = new FormData(form);
        
        const asignaturas = [];
        form.querySelectorAll('input[name="asignaturas[]"]:checked').forEach(cb => {
            asignaturas.push(cb.value);
        });

        const data = {
            name: formData.get('name'),
            asignaturas: asignaturas
        };

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

            if (response.ok) {
                cerrarModal('modalCrear');
                mostrarAlerta(result.message);
                agregarProfesorATabla(result.profesor);
            } else {
                if (result.errors) {
                    mostrarErrores(result.errors, 'crear');
                } else {
                    mostrarAlerta(result.message || 'Error al crear profesor', 'danger');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarAlerta('Error al crear profesor', 'danger');
        }
    }

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
                
                todasAsignaturas.forEach(asignatura => {
                    const isChecked = asignaturasIds.includes(asignatura.id);
                    const label = document.createElement('label');
                    label.className = 'checkbox-label-custom';
                    label.innerHTML = `
                        <input 
                            type="checkbox" 
                            id="editar_asig_${asignatura.id}" 
                            name="asignaturas[]"
                            value="${asignatura.id}"
                            ${isChecked ? 'checked' : ''}
                            class="checkbox-custom"
                        >
                        <span>${asignatura.nombre}</span>
                    `;
                    container.appendChild(label);
                });

                limpiarErrores('modalEditar');
                abrirModal('modalEditar');
            } else {
                mostrarAlerta('Error al cargar datos del profesor', 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarAlerta('Error al cargar datos del profesor', 'danger');
        }
    }

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
                cerrarModal('modalEditar');
                mostrarAlerta(result.message);
                actualizarProfesorEnTabla(result.profesor);
            } else {
                if (result.errors) {
                    mostrarErrores(result.errors, 'editar');
                } else {
                    mostrarAlerta(result.message || 'Error al actualizar profesor', 'danger');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarAlerta('Error al actualizar profesor', 'danger');
        }
    }

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
                
                todasAsignaturas.forEach(asignatura => {
                    const isChecked = asignaturasIds.includes(asignatura.id);
                    const label = document.createElement('label');
                    label.className = 'checkbox-label-custom';
                    label.innerHTML = `
                        <input 
                            type="checkbox" 
                            id="asignar_asig_${asignatura.id}" 
                            name="asignaturas[]"
                            value="${asignatura.id}"
                            ${isChecked ? 'checked' : ''}
                            class="checkbox-custom"
                        >
                        <span>${asignatura.nombre}</span>
                    `;
                    container.appendChild(label);
                });

                abrirModal('modalAsignaturas');
            } else {
                mostrarAlerta('Error al cargar asignaturas', 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarAlerta('Error al cargar asignaturas', 'danger');
        }
    }

    async function guardarAsignaturas() {
        const id = document.getElementById('asignar_profesor_id').value;
        
        const asignaturas = [];
        document.querySelectorAll('#listaAsignaturas input[name="asignaturas[]"]:checked').forEach(cb => {
            asignaturas.push(parseInt(cb.value));
        });

        const data = {
            name: document.querySelector(`tr[data-id="${id}"] td:nth-child(2)`).textContent.trim(),
            asignaturas: asignaturas
        };

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
                cerrarModal('modalAsignaturas');
                mostrarAlerta('Asignaturas actualizadas correctamente');
                actualizarProfesorEnTabla(result.profesor);
            } else {
                mostrarAlerta(result.message || 'Error al asignar asignaturas', 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarAlerta('Error al asignar asignaturas', 'danger');
        }
    }

    function abrirModalEliminar(id, nombre) {
        document.getElementById('eliminar_id').value = id;
        document.getElementById('eliminar_nombre').textContent = nombre;
        abrirModal('modalEliminar');
    }

    async function eliminarProfesor() {
        const id = document.getElementById('eliminar_id').value;

        try {
            const response = await fetch(`/profesores/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const result = await response.json();

            if (result.success) {
                cerrarModal('modalEliminar');
                mostrarAlerta(result.message);
                eliminarProfesorDeTabla(id);
            } else {
                mostrarAlerta(result.message || 'Error al eliminar profesor', 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarAlerta('Error al eliminar profesor', 'danger');
        }
    }

    function agregarProfesorATabla(profesor) {
        const tbody = document.getElementById('tablaProfesores');
        const filaVacia = document.getElementById('filaVacia');
        if (filaVacia) filaVacia.remove();

        const asignaturasHTML = profesor.asignaturas.length > 0
            ? profesor.asignaturas.map(a => `<span class="badge-custom">${a.nombre}</span>`).join(' ')
            : '<span class="badge-empty">Sin asignaturas</span>';

        const nombreEscapado = profesor.name.replace(/'/g, "\\'");

        const tr = document.createElement('tr');
        tr.setAttribute('data-id', profesor.id);
        tr.innerHTML = `
            <td style="color: #6b7280; font-weight: 500;">${profesor.id}</td>
            <td style="color: #000000; font-weight: 600;">${profesor.name}</td>
            <td><div class="d-flex flex-wrap gap-2">${asignaturasHTML}</div></td>
            <td>
                <div class="action-buttons">
                    <button onclick="abrirModalAsignaturas(${profesor.id})" class="btn-icon btn-assign" title="Asignar asignaturas">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" stroke="currentColor" stroke-width="0.3"><path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z"/></svg>
                    </button>
                    <button onclick="abrirModalEditar(${profesor.id})" class="btn-icon btn-edit" title="Editar profesor">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" stroke="currentColor" stroke-width="0.3"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/></svg>
                    </button>
                    <button onclick="abrirModalEliminar(${profesor.id}, '${nombreEscapado}')" class="btn-icon btn-delete" title="Eliminar profesor">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" stroke="currentColor" stroke-width="0.3"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/><path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/></svg>
                    </button>
                </div>
            </td>
        `;
        tbody.insertBefore(tr, tbody.firstChild);
    }

    function actualizarProfesorEnTabla(profesor) {
        const tr = document.querySelector(`tr[data-id="${profesor.id}"]`);
        if (!tr) return;

        const asignaturasHTML = profesor.asignaturas.length > 0
            ? profesor.asignaturas.map(a => `<span class="badge-custom">${a.nombre}</span>`).join(' ')
            : '<span class="badge-empty">Sin asignaturas</span>';

        const nombreEscapado = profesor.name.replace(/'/g, "\\'");

        tr.innerHTML = `
            <td style="color: #6b7280; font-weight: 500;">${profesor.id}</td>
            <td style="color: #000000; font-weight: 600;">${profesor.name}</td>
            <td><div class="d-flex flex-wrap gap-2">${asignaturasHTML}</div></td>
            <td>
                <div class="action-buttons">
                    <button onclick="abrirModalAsignaturas(${profesor.id})" class="btn-icon btn-assign" title="Asignar asignaturas">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" stroke="currentColor" stroke-width="0.3"><path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z"/></svg>
                    </button>
                    <button onclick="abrirModalEditar(${profesor.id})" class="btn-icon btn-edit" title="Editar profesor">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" stroke="currentColor" stroke-width="0.3"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/></svg>
                    </button>
                    <button onclick="abrirModalEliminar(${profesor.id}, '${nombreEscapado}')" class="btn-icon btn-delete" title="Eliminar profesor">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" stroke="currentColor" stroke-width="0.3"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/><path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/></svg>
                    </button>
                </div>
            </td>
        `;
    }

    function eliminarProfesorDeTabla(id) {
        const tr = document.querySelector(`tr[data-id="${id}"]`);
        if (tr) tr.remove();

        const tbody = document.getElementById('tablaProfesores');
        if (tbody.children.length === 0) {
            const tr = document.createElement('tr');
            tr.id = 'filaVacia';
            tr.innerHTML = `
                <td colspan="4" class="empty-state">
                    <svg width="48" height="48" fill="currentColor" class="mb-3" viewBox="0 0 16 16" style="opacity: 0.3;">
                        <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816zM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275zM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                    </svg>
                    <p class="mb-1 fw-semibold" style="font-size: 1rem; color: #374151;">No hay profesores registrados</p>
                    <small style="color: #6b7280;">Comienza agregando un nuevo profesor</small>
                </td>
            `;
            tbody.appendChild(tr);
        }
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.classList.remove('active');
        }
    }
</script>
@endsection