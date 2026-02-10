@extends('layouts.master')

@section('title', 'Gestión de Asignaturas')

@section('content')
<div class="container py-5">
    {{-- Header minimalista sin espacio arriba --}}
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h2 class="fw-bold mb-2" style="color: #000000; font-size: 1.75rem; letter-spacing: -0.5px;">Gestión de Asignaturas</h2>
                <p class="text-muted mb-0" style="font-size: 0.9rem; color: #6b7280;">{{ $asignaturas->count() }} {{ $asignaturas->count() === 1 ? 'asignatura registrada' : 'asignaturas registradas' }}</p>
            </div>
            <button onclick="openCreateModal()" class="btn-primary-custom fw-semibold d-inline-flex align-items-center gap-2" 
                    style="background-color: #2563eb; color: #ffffff; padding: 0.75rem 1.5rem; border: none; font-size: 0.9rem; border-radius: 6px; transition: all 0.2s ease;">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                </svg>
                Nueva Asignatura
            </button>
        </div>
    </div>

    {{-- Alerta dinámica --}}
    <div id="alertContainer"></div>

    {{-- Alerta de éxito desde el servidor --}}
    @if(session('success'))
        <div class="alert alert-success mb-4" style="padding: 1rem 1.25rem; border-radius: 6px; display: flex; align-items: center; gap: 0.75rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); font-weight: 500; font-size: 0.9rem; background-color: #10b981; color: #ffffff; border: 1px solid #059669;">
            ✓ {{ session('success') }}
        </div>
    @endif

    {{-- Tabla de asignaturas --}}
    <div class="card-custom">
        <div class="card-header-custom">
            <div>
                <h5 class="mb-1 fw-semibold" style="color: #000000; font-size: 1.05rem;">Asignaturas Registradas</h5>
                <small style="font-size: 0.85rem; color: #6b7280;">Listado completo de asignaturas del sistema</small>
            </div>
        </div>
        <div class="card-body-custom">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th>Nombre de la Asignatura</th>
                            <th style="width: 140px; text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaAsignaturas">
                        @forelse($asignaturas as $asignatura)
                            <tr data-id="{{ $asignatura->id }}">
                                <td style="color: #6b7280; font-weight: 500;">{{ $asignatura->id }}</td>
                                <td style="color: #000000; font-weight: 600;">{{ $asignatura->nombre }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <button 
                                            onclick='openEditModal({{ $asignatura->id }}, "{{ addslashes($asignatura->nombre) }}")'
                                            class="btn-icon btn-edit"
                                            title="Editar asignatura">
                                            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" stroke="currentColor" stroke-width="0.3">
                                                <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
                                            </svg>
                                        </button>
                                        <button 
                                            onclick='openDeleteModal({{ $asignatura->id }}, "{{ addslashes($asignatura->nombre) }}")'
                                            class="btn-icon btn-delete"
                                            title="Eliminar asignatura">
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
                                <td colspan="3" class="empty-state">
                                    <svg width="48" height="48" fill="currentColor" class="mb-3" viewBox="0 0 16 16" style="opacity: 0.3;">
                                        <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z"/>
                                    </svg>
                                    <p class="mb-1 fw-semibold" style="font-size: 1rem; color: #374151;">No hay asignaturas registradas</p>
                                    <small style="color: #6b7280;">Comienza agregando una nueva asignatura</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CREAR ASIGNATURA -->
<div id="createModal" class="modal">
    <div class="modal-content-custom">
        <div class="modal-header-custom" style="background-color: #ffffff; color: #000000; border-bottom: 2px solid #e5e7eb;">
            <h5 class="mb-0 fw-semibold d-flex align-items-center gap-2">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                </svg>
                Nueva Asignatura
            </h5>
            <button class="btn-close-modal" onclick="closeCreateModal()">&times;</button>
        </div>
        <div class="modal-body-custom">
            <form id="formCrear" action="{{ route('asignaturas.store') }}" method="POST">
                @csrf
                <div class="form-group-custom">
                    <label class="form-label-custom">Nombre de la Asignatura <span style="color: #dc2626;">*</span></label>
                    <input 
                        type="text" 
                        id="nombre_create" 
                        name="nombre" 
                        value="{{ old('nombre') }}"
                        class="form-control-custom @error('nombre') is-invalid @enderror" 
                        required 
                        placeholder="Ej: Matemáticas, Español, Ciencias...">
                    @error('nombre')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
            </form>
        </div>
        <div class="modal-footer-custom">
            <button type="button" class="btn-secondary-modal" onclick="closeCreateModal()">Cancelar</button>
            <button type="submit" form="formCrear" class="btn-primary-modal">Guardar Asignatura</button>
        </div>
    </div>
</div>

<!-- MODAL EDITAR ASIGNATURA -->
<div id="editModal" class="modal">
    <div class="modal-content-custom">
        <div class="modal-header-custom" style="background-color: #ffffff; color: #000000; border-bottom: 2px solid #e5e7eb;">
            <h5 class="mb-0 fw-semibold d-flex align-items-center gap-2">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
                </svg>
                Editar Asignatura
            </h5>
            <button class="btn-close-modal" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="modal-body-custom">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group-custom">
                    <label class="form-label-custom">Nombre de la Asignatura <span style="color: #dc2626;">*</span></label>
                    <input 
                        type="text" 
                        id="nombre_edit" 
                        name="nombre" 
                        class="form-control-custom" 
                        required
                        placeholder="Ej: Matemáticas, Español, Ciencias...">
                    <div class="error-message" id="error_edit_nombre"></div>
                </div>
            </form>
        </div>
        <div class="modal-footer-custom">
            <button type="button" class="btn-secondary-modal" onclick="closeEditModal()">Cancelar</button>
            <button type="submit" form="editForm" class="btn-primary-modal">Actualizar</button>
        </div>
    </div>
</div>

<!-- MODAL ELIMINAR ASIGNATURA -->
<div id="deleteModal" class="modal">
    <div class="modal-content-custom" style="max-width: 480px;">
        <div class="modal-header-custom" style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: #ffffff; border: none;">
            <h5 class="mb-0 fw-semibold d-flex align-items-center gap-2">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                </svg>
                Confirmar Eliminación
            </h5>
            <button class="btn-close-modal btn-close-modal-white" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body-custom" style="text-align: center; padding: 2.5rem 2rem;">
            <p style="font-size: 1rem; margin: 1rem 0 0.5rem 0; color: #374151;">
                ¿Estás seguro de eliminar la asignatura
            </p>
            <p style="font-size: 1.1rem; font-weight: 600; color: #000000; margin: 0 0 1rem 0;">
                <strong id="delete_nombre"></strong>?
            </p>
            <p style="font-size: 0.875rem; color: #6b7280; margin: 0;">Esta acción no se puede deshacer.</p>
        </div>
        <div class="modal-footer-custom">
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <button type="button" class="btn-secondary-modal" onclick="closeDeleteModal()">Cancelar</button>
                <button type="submit" class="btn-danger-modal">Eliminar Definitivamente</button>
            </form>
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

/* Botones de acción */
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
.modal-content-custom::-webkit-scrollbar {
    width: 6px;
}

.modal-content-custom::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}

.modal-content-custom::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.modal-content-custom::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Responsive */
@media (max-width: 768px) {
    .modal-content-custom {
        width: 95%;
        max-height: 95vh;
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
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function openCreateModal() {
        document.getElementById('createModal').classList.add('active');
        document.getElementById('nombre_create').focus();
    }

    function closeCreateModal() {
        document.getElementById('createModal').classList.remove('active');
        document.getElementById('nombre_create').value = '';
        document.getElementById('nombre_create').classList.remove('is-invalid');
        const errorDiv = document.querySelector('#createModal .error-message');
        if (errorDiv) errorDiv.textContent = '';
    }

    function openEditModal(id, nombre) {
        document.getElementById('editModal').classList.add('active');
        document.getElementById('nombre_edit').value = nombre;
        document.getElementById('editForm').action = '/asignaturas/' + id;
        document.getElementById('nombre_edit').focus();
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
        document.getElementById('nombre_edit').value = '';
        document.getElementById('nombre_edit').classList.remove('is-invalid');
        document.getElementById('error_edit_nombre').textContent = '';
    }

    function openDeleteModal(id, nombre) {
        document.getElementById('deleteModal').classList.add('active');
        document.getElementById('delete_nombre').textContent = nombre;
        document.getElementById('deleteForm').action = '/asignaturas/' + id;
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('active');
    }

    function mostrarAlerta(mensaje, tipo = 'success') {
        const alertContainer = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${tipo}`;
        alert.innerHTML = `${tipo === 'success' ? '✓' : '⚠'} ${mensaje}`;
        alertContainer.appendChild(alert);
        
        setTimeout(() => alert.remove(), 4000);
    }

    // Cerrar modal al hacer click fuera
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.classList.remove('active');
        }
    }

    // Cerrar modal con tecla ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
            closeDeleteModal();
        }
    });

    // Si hay errores de validación, abrir el modal correspondiente
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function() {
            openCreateModal();
        });
    @endif

    // Auto-cerrar alertas de sesión después de 5 segundos
    document.addEventListener('DOMContentLoaded', function() {
        const sessionAlert = document.querySelector('.alert-success');
        if (sessionAlert && !sessionAlert.parentElement.id) {
            setTimeout(function() {
                sessionAlert.style.transition = 'opacity 0.5s ease';
                sessionAlert.style.opacity = '0';
                setTimeout(function() {
                    sessionAlert.remove();
                }, 500);
            }, 5000);
        }
    });
</script>
@endsection