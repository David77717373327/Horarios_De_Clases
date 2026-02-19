@extends('layouts.master')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
/* ===================================
   GENERAL
=================================== */
.page-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
    letter-spacing: -0.3px;
}

.page-subtitle {
    font-size: 0.875rem;
    color: #4b5563;
    margin: 3px 0 0 0;
    font-weight: 400;
}

/* ===================================
   CARD FORMULARIO
=================================== */
.card-custom {
    border: 1px solid #9ca3af;
    border-radius: 0;
    background: #ffffff;
}

.card-custom .card-header-custom {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #9ca3af;
    background-color: #f9fafb;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-header-icon {
    width: 32px;
    height: 32px;
    background-color: #dbeafe;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.card-header-icon i {
    color: #2563eb;
    font-size: 0.875rem;
}

.card-header-text h5 {
    font-size: 0.95rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
    line-height: 1;
}

.card-header-text p {
    font-size: 0.78rem;
    color: #4b5563;
    margin: 2px 0 0 0;
    line-height: 1;
}

.card-custom .card-body-custom {
    padding: 1.5rem;
}

/* ===================================
   FORMULARIO
=================================== */
.form-label-custom {
    font-size: 0.82rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 5px;
    display: block;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.input-wrapper {
    position: relative;
}

.input-wrapper .input-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 0.875rem;
    pointer-events: none;
    transition: color 0.2s ease;
}

.form-control-custom {
    border: 1px solid #9ca3af;
    border-radius: 0;
    padding: 0.65rem 0.875rem 0.65rem 2.25rem;
    font-size: 0.9rem;
    width: 100%;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    outline: none;
    background-color: #ffffff;
    color: #111827;
    font-weight: 500;
}

.form-control-custom::placeholder {
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: 400;
}

.form-control-custom:hover {
    border-color: #2563eb;
}

.form-control-custom:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.input-wrapper:focus-within .input-icon {
    color: #2563eb;
}

.btn-save {
    background-color: #2563eb;
    color: #ffffff;
    border: none;
    border-radius: 0;
    padding: 0.65rem 1.25rem;
    font-size: 0.875rem;
    font-weight: 600;
    width: 100%;
    cursor: pointer;
    transition: background-color 0.2s ease, box-shadow 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    letter-spacing: 0.02em;
}

.btn-save:hover {
    background-color: #1d4ed8;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
}

/* ===================================
   SECCIÓN TABLA SIN CARD
=================================== */
.section-gap {
    margin-top: 2rem;
}

.table-section-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    margin-bottom: 1rem;
    padding-bottom: 0.875rem;
    border-bottom: 2px solid #e5e7eb;
}

.table-section-left h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
    letter-spacing: -0.2px;
}

.table-section-left p {
    font-size: 0.82rem;
    color: #4b5563;
    margin: 3px 0 0 0;
    font-weight: 400;
}

/* Buscador */
.search-box {
    display: flex;
    align-items: center;
    border: 1px solid #9ca3af;
    background-color: #ffffff;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    min-width: 280px;
}

.search-box:focus-within {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.search-box-icon {
    padding: 0 10px 0 12px;
    color: #9ca3af;
    font-size: 0.82rem;
    display: flex;
    align-items: center;
    flex-shrink: 0;
    transition: color 0.2s ease;
}

.search-box:focus-within .search-box-icon {
    color: #2563eb;
}

.search-box input {
    border: none;
    outline: none;
    padding: 0.6rem 0.75rem 0.6rem 0;
    font-size: 0.875rem;
    width: 100%;
    color: #111827;
    background: transparent;
    font-weight: 500;
}

.search-box input::placeholder {
    color: #6b7280;
    font-weight: 400;
}

/* ===================================
   TABLA
=================================== */
.table-custom {
    width: 100% !important;
    font-size: 0.875rem;
    border-collapse: collapse;
    border: 1px solid #9ca3af;
}

.table-custom thead tr {
    background-color: #f1f5f9;
    border-bottom: 2px solid #9ca3af;
}

.table-custom thead th {
    font-size: 0.72rem;
    font-weight: 700;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    padding: 1rem 1.25rem;
    border-right: 1px solid #e2e8f0;
    white-space: nowrap;
}

.table-custom thead th:last-child {
    border-right: none;
}

.table-custom tbody tr {
    border-bottom: 1px solid #e5e7eb;
    transition: background-color 0.15s ease;
}

.table-custom tbody tr:last-child {
    border-bottom: none;
}

.table-custom tbody tr:hover {
    background-color: #eff6ff;
}

.table-custom tbody td {
    padding: 1rem 1.25rem;
    color: #111827;
    font-weight: 500;
    border-right: 1px solid #f1f5f9;
    vertical-align: middle;
}

.table-custom tbody td:last-child {
    border-right: none;
}

.nombre-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}

.nombre-icon {
    width: 32px;
    height: 32px;
    background-color: #dbeafe;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.8rem;
    color: #2563eb;
}

.nombre-text {
    display: flex;
    flex-direction: column;
    gap: 1px;
}

.nombre-text span {
    font-size: 0.9rem;
    font-weight: 600;
    color: #111827;
    line-height: 1;
}

.nombre-text small {
    font-size: 0.72rem;
    color: #6b7280;
    font-weight: 400;
}

/* ===================================
   BOTONES ACCIÓN
=================================== */
.btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background-color: transparent;
    border-radius: 0;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.8rem;
}

.btn-edit {
    color: #2563eb;
    border: 1px solid #2563eb;
}

.btn-edit:hover {
    background-color: #2563eb;
    color: #ffffff;
}

.btn-delete {
    color: #dc2626;
    border: 1px solid #dc2626;
}

.btn-delete:hover {
    background-color: #dc2626;
    color: #ffffff;
}

.actions-group {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

/* ===================================
   FOOTER TABLA
=================================== */
.dt-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 1rem;
    padding-top: 0.875rem;
    border-top: 1px solid #e5e7eb;
}

.dataTables_wrapper .dataTables_info {
    font-size: 0.8rem;
    color: #4b5563;
    padding-top: 0;
}

.dataTables_wrapper .dataTables_paginate {
    padding-top: 0;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
    border-radius: 0 !important;
    border: 1px solid #9ca3af !important;
    font-size: 0.8rem;
    padding: 0.3rem 0.7rem !important;
    margin: 0 2px;
    color: #374151 !important;
    transition: all 0.2s ease;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: #2563eb !important;
    border-color: #2563eb !important;
    color: #ffffff !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: #eff6ff !important;
    border-color: #2563eb !important;
    color: #2563eb !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
    background: #1d4ed8 !important;
    border-color: #1d4ed8 !important;
    color: #ffffff !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
    opacity: 0.4;
}

.dataTables_wrapper .dataTables_filter,
.dataTables_wrapper .dataTables_length {
    display: none;
}

/* ===================================
   ALERTAS
=================================== */
.alert-custom {
    border-radius: 0;
    border: none;
    border-left: 4px solid;
    padding: 1rem 1.25rem;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 1.5rem;
    animation: slideDown 0.3s ease;
    /* Altura fija para que el collapse sea suave */
    overflow: hidden;
    transition: max-height 0.4s ease, opacity 0.4s ease,
                padding 0.4s ease, margin 0.4s ease;
    max-height: 200px;
}

/* Clase que se aplica para colapsar */
.alert-custom.hiding {
    max-height: 0;
    opacity: 0;
    padding-top: 0;
    padding-bottom: 0;
    margin-bottom: 0;
}

.alert-success-custom {
    background-color: #f0fdf4;
    border-color: #22c55e;
}

.alert-success-custom i { color: #22c55e; }
.alert-success-custom .alert-title { color: #15803d; font-weight: 700; font-size: 0.875rem; }
.alert-success-custom .alert-msg  { color: #166534; font-size: 0.82rem; }

.alert-error-custom { background-color: #fef2f2; border-color: #ef4444; }
.alert-error-custom i { color: #ef4444; }
.alert-error-custom .alert-title { color: #991b1b; font-weight: 700; font-size: 0.875rem; }
.alert-error-custom .alert-msg  { color: #dc2626; font-size: 0.82rem; }

.alert-warning-custom { background-color: #fffbeb; border-color: #f59e0b; }
.alert-warning-custom i { color: #f59e0b; }
.alert-warning-custom .alert-title { color: #92400e; font-weight: 700; font-size: 0.875rem; }
.alert-warning-custom .alert-msg  { color: #b45309; font-size: 0.82rem; }

.alert-close {
    margin-left: auto;
    background: none;
    border: none;
    font-size: 0.9rem;
    cursor: pointer;
    opacity: 0.4;
    padding: 0;
    flex-shrink: 0;
}

.alert-close:hover { opacity: 1; }

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ===================================
   MODAL
=================================== */
.modal-custom .modal-content {
    border: 1px solid #9ca3af;
    border-radius: 0;
    box-shadow: 0 10px 30px rgba(0,0,0,0.12);
}

.modal-custom .modal-header {
    background-color: #f9fafb;
    border-bottom: 1px solid #9ca3af;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal-custom .modal-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #111827;
}

.modal-custom .modal-body {
    padding: 1.5rem;
}

.modal-custom .modal-footer {
    background-color: #f9fafb;
    border-top: 1px solid #9ca3af;
    padding: 1rem 1.5rem;
}

.btn-modal-cancel {
    background-color: transparent;
    border: 1px solid #9ca3af;
    border-radius: 0;
    color: #374151;
    padding: 0.55rem 1.25rem;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-modal-cancel:hover {
    background-color: #f3f4f6;
    border-color: #6b7280;
}

.btn-modal-save {
    background-color: #2563eb;
    border: none;
    border-radius: 0;
    color: #ffffff;
    padding: 0.55rem 1.25rem;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-modal-save:hover {
    background-color: #1d4ed8;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
}
</style>
@endsection

@section('content')
<div class="container-fluid py-4 px-4">

    {{-- Header página --}}
    <div class="mb-4">
        <h2 class="page-title">Niveles Académicos</h2>
        <p class="page-subtitle">Administra y gestiona los niveles educativos del sistema</p>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
        <div class="alert-custom alert-success-custom" id="alert-success">
            <i class="fas fa-check-circle fa-lg mt-1"></i>
            <div>
                <div class="alert-title">Operación exitosa</div>
                <div class="alert-msg">{{ session('success') }}</div>
            </div>
            <button class="alert-close" onclick="hideAlert('alert-success')">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert-custom alert-error-custom" id="alert-error">
            <i class="fas fa-exclamation-circle fa-lg mt-1"></i>
            <div>
                <div class="alert-title">Se produjo un error</div>
                <div class="alert-msg">{{ session('error') }}</div>
            </div>
            <button class="alert-close" onclick="hideAlert('alert-error')">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert-custom alert-warning-custom" id="alert-warning">
            <i class="fas fa-exclamation-triangle fa-lg mt-1"></i>
            <div>
                <div class="alert-title">Errores de validación</div>
                <ul class="alert-msg mb-0 mt-1" style="padding-left:1.2rem;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button class="alert-close" onclick="hideAlert('alert-warning')">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    {{-- Formulario CREAR --}}
    <div class="card-custom">
        <div class="card-header-custom">
            <div class="card-header-icon">
                <i class="fas fa-plus"></i>
            </div>
            <div class="card-header-text">
                <h5>Crear Nuevo Nivel</h5>
                <p>Ingresa el nombre del nivel académico a registrar</p>
            </div>
        </div>
        <div class="card-body-custom">
            <form method="POST" action="{{ route('niveles.store') }}" id="create-form">
                @csrf
                <div class="row align-items-end g-3">
                    <div class="col-md-8">
                        <label class="form-label-custom">Nombre del Nivel</label>
                        <div class="input-wrapper">
                            <i class="fas fa-layer-group input-icon"></i>
                            <input
                                type="text"
                                name="nombre"
                                class="form-control-custom"
                                placeholder="Ej: Primaria, Secundaria, Bachillerato..."
                                value="{{ old('nombre') }}"
                                required>
                        </div>
                        @error('nombre')
                            <small style="color:#dc2626; font-size:0.78rem; display:block; margin-top:5px;">
                                <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                            </small>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn-save">
                            <i class="fas fa-save"></i> Guardar Nivel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Sección Tabla --}}
    <div class="section-gap">

        <div class="table-section-header">
            <div class="table-section-left">
                <h3>Niveles Registrados</h3>
                <p>Listado completo de niveles académicos del sistema</p>
            </div>
            <div class="search-box">
                <span class="search-box-icon">
                    <i class="fas fa-search"></i>
                </span>
                <input
                    type="text"
                    id="buscador-niveles"
                    placeholder="Buscar nivel...">
            </div>
        </div>

        <table class="table-custom" id="tabla-niveles">
            <thead>
                <tr>
                    <th>Nombre del Nivel</th>
                    <th class="text-center" style="width: 120px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($niveles as $nivel)
                    <tr>
                        <td>
                            <div class="nombre-cell">
                                <div class="nombre-icon">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                                <div class="nombre-text">
                                    <span>{{ $nivel->nombre }}</span>
                                    <small>Nivel académico</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="actions-group">
                                <button
                                    class="btn-action btn-edit"
                                    onclick="openEditModal({{ $nivel->id }}, '{{ $nivel->nombre }}')"
                                    title="Editar">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button
                                    class="btn-action btn-delete"
                                    onclick="confirmDelete({{ $nivel->id }}, '{{ $nivel->nombre }}', {{ $nivel->grados_count }}, {{ $nivel->descansos_count }})"
                                    title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="dt-footer">
            <div id="dt-info"></div>
            <div id="dt-paginate"></div>
        </div>

    </div>

</div>

{{-- MODAL EDITAR --}}
<div class="modal fade modal-custom" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="card-header-icon me-1">
                    <i class="fas fa-pen"></i>
                </div>
                <h5 class="modal-title">Editar Nivel Académico</h5>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="edit-form">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <label class="form-label-custom">Nombre del Nivel</label>
                    <div class="input-wrapper">
                        <i class="fas fa-layer-group input-icon"></i>
                        <input
                            type="text"
                            name="nombre"
                            id="edit-nombre"
                            class="form-control-custom"
                            placeholder="Ej: Primaria, Secundaria"
                            required>
                    </div>
                    <small style="color:#6b7280; font-size:0.78rem; margin-top:5px; display:block;">
                        Modifica el nombre del nivel educativo y guarda los cambios
                    </small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn-modal-save">
                        <i class="fas fa-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="delete-form" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Función para ocultar alertas con colapso suave sin dejar espacio
function hideAlert(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.add('hiding');
    setTimeout(() => el.remove(), 420);
}

$(document).ready(function () {
    const dt = $('#tabla-niveles').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
            emptyTable: '<div style="text-align:center; padding:2.5rem 1rem;"><i class="fas fa-layer-group" style="font-size:2rem; opacity:0.25; display:block; margin-bottom:0.75rem; color:#6b7280;"></i><p style="font-size:0.9rem; font-weight:600; color:#374151; margin:0 0 4px 0;">No hay niveles registrados</p><small style="font-size:0.8rem; color:#6b7280;">Crea tu primer nivel usando el formulario de arriba</small></div>'
        },
        pageLength: 10,
        order: [[0, 'asc']],
        columnDefs: [{ orderable: false, targets: 1 }],
        dom: 'rtip',
        initComplete: function() {
            $('#dt-info').append($('.dataTables_info'));
            $('#dt-paginate').append($('.dataTables_paginate'));
        }
    });

    $('#buscador-niveles').on('keyup', function() {
        dt.search(this.value).draw();
    });

    // Auto ocultar alerta de éxito a los 2.5s con colapso suave
    setTimeout(() => hideAlert('alert-success'), 2500);
});

function openEditModal(id, nombre) {
    document.getElementById('edit-nombre').value = nombre;
    document.getElementById('edit-form').action = "{{ url('niveles') }}/" + id;
    const modal = new bootstrap.Modal(document.getElementById('editModal'));
    modal.show();
    setTimeout(() => {
        document.getElementById('edit-nombre').focus();
        document.getElementById('edit-nombre').select();
    }, 300);
}

function confirmDelete(id, nombre, gradosCount, descansosCount) {
    Swal.fire({
        title: '¿Eliminar nivel?',
        html: `
            <div style="text-align:left; margin-top:1rem;">
                <p style="font-size:0.9rem; margin-bottom:1rem;">
                    Nivel: <strong>${nombre}</strong>
                </p>
                ${gradosCount > 0 || descansosCount > 0 ? `
                <div style="background:#fef2f2; padding:1rem; border-left:4px solid #dc2626;">
                    <p style="margin:0 0 0.5rem 0; color:#991b1b; font-weight:600; font-size:0.875rem;">
                        <i class="fas fa-exclamation-triangle me-2"></i>Se eliminarán permanentemente:
                    </p>
                    <ul style="margin:0; padding-left:1.5rem; color:#7f1d1d; font-size:0.85rem;">
                        ${gradosCount > 0 ? `<li><strong>${gradosCount}</strong> grado(s) con todos sus horarios</li>` : ''}
                        ${descansosCount > 0 ? `<li><strong>${descansosCount}</strong> descanso(s)</li>` : ''}
                        <li>Todas las asignaciones académicas relacionadas</li>
                    </ul>
                </div>` : `
                <div style="background:#fffbeb; padding:1rem; border-left:4px solid #f59e0b;">
                    <p style="margin:0; color:#92400e; font-size:0.85rem;">
                        <i class="fas fa-info-circle me-2"></i>Este nivel no tiene relaciones y se puede eliminar de forma segura.
                    </p>
                </div>`}
            </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-trash me-2"></i>Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        customClass: { popup: 'rounded-0' }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Eliminando...',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => Swal.showLoading()
            });
            const form = document.getElementById('delete-form');
            form.action = "{{ url('niveles') }}/" + id;
            form.submit();
        }
    });
}

document.getElementById('create-form').addEventListener('submit', function() {
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
});

document.getElementById('edit-form').addEventListener('submit', function() {
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
});
</script>
@endsection