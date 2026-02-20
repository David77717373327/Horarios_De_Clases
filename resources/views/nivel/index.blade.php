@extends('layouts.master')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="{{ asset('css/niveles.css') }}">
@section('title', 'Niveles Académicos')

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