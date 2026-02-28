@extends('layouts.master')

@section('title', 'Gestión de Asignaturas')

@section('content')
<div class="container-fluid py-4 px-4">

    {{-- Header con buscador --}}
    <div class="mb-3">
        <div class="d-flex align-items-center justify-content-between mb-19">
            <div>
                <h2 class="page-title">Gestión de Asignaturas</h2>
                <p class="page-subtitle">Administra las asignaturas del sistema educativo</p>
            </div>
            <button class="btn-primary-custom" type="button"
                data-bs-toggle="offcanvas" data-bs-target="#offcanvasCrear">
                <i class="fas fa-plus"></i> Nueva Asignatura
            </button>
        </div>
        <div class="search-box w-100">
            <span class="search-box-icon"><i class="fas fa-search"></i></span>
            <input type="text" id="buscador-asignaturas" placeholder="Buscar asignatura...">
        </div>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
        <x-alert type="success" id="alert-success" title="Operación exitosa" :message="session('success')" />
    @endif
    @if(session('error'))
        <x-alert type="error" id="alert-error" title="Se produjo un error" :message="session('error')" />
    @endif
    @if($errors->any())
        <x-alert type="error" id="alert-warning" title="Errores de validación" :list="$errors->all()" />
    @endif

    {{-- Tabla --}}
    <div class="card-panel">
        <div class="table-wrapper">
            <table class="table-modern" id="tabla-asignaturas">
                <thead>
                    <tr>
                        <th>Nombre de la Asignatura</th>
                        <th class="text-center" style="width:120px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($asignaturas as $asignatura)
                        <tr>
                            <td>
                                <div class="cell-with-icon">
                                    <div class="cell-icon">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <div>
                                        <span class="cell-title">{{ $asignatura->nombre }}</span>
                                        <span class="cell-subtitle">Asignatura académica</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="actions-group">
                                    <button class="btn-icon btn-icon-blue"
                                        onclick='openEditCanvas({{ $asignatura->id }}, "{{ addslashes($asignatura->nombre) }}")'
                                        title="Editar">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button class="btn-icon btn-icon-red"
                                        onclick='openDeleteCanvas({{ $asignatura->id }}, "{{ addslashes($asignatura->nombre) }}", {{ $asignatura->horarios_count ?? 0 }})'
                                        title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="table-footer">
            <div id="dt-info"></div>
            <div id="dt-paginate"></div>
        </div>
    </div>

</div>

{{-- OFFCANVAS: CREAR (múltiple premium) --}}
<div class="offcanvas offcanvas-end offcanvas-modern" tabindex="-1" id="offcanvasCrear">
    <div class="offcanvas-modern-header">
        <div class="d-flex align-items-center gap-2">
            <div class="card-panel-icon oc-icon-blue" style="width:32px;height:32px;font-size:0.8rem;">
                <i class="fas fa-plus"></i>
            </div>
            <div>
                <h5 class="mb-0 card-panel-title">Nueva Asignatura</h5>
                <p class="card-panel-subtitle mb-0" id="oc-create-subtitle">Agrega una o varias asignaturas</p>
            </div>
        </div>
        <button type="button" class="offcanvas-close-btn" data-bs-dismiss="offcanvas">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <form method="POST" action="{{ route('asignaturas.store') }}" id="create-form" class="oc-form-wrap">
        @csrf
        <div class="oc-scroll-body">

            {{-- Cabecera con contador --}}
            <div class="multi-field-header">
                <span class="multi-field-count" id="field-count-label">1 asignatura</span>
                <span class="multi-field-hint">Enter para agregar otra</span>
            </div>

            {{-- Cards dinámicas --}}
            <div id="fields-container"></div>

            {{-- Botón agregar --}}
            <button type="button" class="btn-add-field" onclick="addField(true)">
                <i class="fas fa-plus-circle"></i>
                <span>Agregar otra asignatura</span>
            </button>

        </div>
        <div class="oc-fixed-footer">
            <div class="oc-divider mb-3"></div>

            {{-- Resumen antes del botón --}}
            <div class="mf-summary" id="mf-summary">
                <i class="fas fa-info-circle"></i>
                <span id="mf-summary-text">Completa al menos un campo para guardar</span>
            </div>

            <div class="d-flex gap-2">
                <button type="button" class="btn-outline-custom flex-fill" data-bs-dismiss="offcanvas">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="submit" class="btn-primary-custom flex-fill" id="btn-submit-create">
                    <i class="fas fa-save me-1"></i>
                    <span id="btn-submit-label">Guardar asignatura</span>
                </button>
            </div>
        </div>
    </form>
</div>

{{-- OFFCANVAS: EDITAR --}}
<div class="offcanvas offcanvas-end offcanvas-modern" tabindex="-1" id="offcanvasEditar">
    <div class="offcanvas-modern-header">
        <div class="d-flex align-items-center gap-2">
            <div class="card-panel-icon oc-icon-amber" style="width:32px;height:32px;font-size:0.8rem;">
                <i class="fas fa-pen"></i>
            </div>
            <div>
                <h5 class="mb-0 card-panel-title">Editar Asignatura</h5>
                <p class="card-panel-subtitle mb-0" id="edit-subtitle">Modificando asignatura</p>
            </div>
        </div>
        <button type="button" class="offcanvas-close-btn" data-bs-dismiss="offcanvas">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <form method="POST" id="edit-form" class="oc-form-wrap">
        @csrf
        @method('PUT')
        <div class="oc-scroll-body">
            <div class="mb-4">
                <label class="form-label-custom">Nombre de la Asignatura</label>
                <div class="input-wrapper">
                    <i class="fas fa-book input-icon"></i>
                    <input type="text" name="nombre" id="edit-nombre"
                        class="form-control-custom"
                        placeholder="Ej: Matemáticas, Español..." required>
                </div>
                <small class="d-block mt-2" style="font-size:0.78rem;color:#6b7280;">
                    Modifica el nombre y guarda los cambios.
                </small>
            </div>
        </div>
        <div class="oc-fixed-footer">
            <div class="oc-divider mb-3"></div>
            <div class="d-flex gap-2">
                <button type="button" class="btn-outline-custom flex-fill" data-bs-dismiss="offcanvas">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="submit" class="btn-warning-custom flex-fill">
                    <i class="fas fa-save me-1"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </form>
</div>

{{-- OFFCANVAS: ELIMINAR --}}
<div class="offcanvas offcanvas-end offcanvas-modern offcanvas-delete" tabindex="-1" id="offcanvasEliminar">
    <div class="offcanvas-modern-header">
        <div class="d-flex align-items-center gap-2">
            <div class="card-panel-icon oc-icon-red" style="width:32px;height:32px;font-size:0.8rem;">
                <i class="fas fa-trash-alt"></i>
            </div>
            <div>
                <h5 class="mb-0 card-panel-title">Eliminar Asignatura</h5>
                <p class="card-panel-subtitle mb-0">Esta acción no se puede deshacer</p>
            </div>
        </div>
        <button type="button" class="offcanvas-close-btn" data-bs-dismiss="offcanvas">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="oc-form-wrap">
        <div class="oc-scroll-body">
            <div class="del-target-card">
                <div class="del-target-bg-icon"><i class="fas fa-book"></i></div>
                <div class="del-target-content">
                    <span class="del-target-eyebrow">Asignatura seleccionada</span>
                    <span class="del-target-name" id="delete-nombre-display">—</span>
                </div>
            </div>
            <div id="delete-warning-block"></div>
            <div id="delete-confirm-input-wrap" style="display:none;">
                <div class="del-confirm-section">
                    <div class="del-confirm-label">
                        <i class="fas fa-lock"></i>
                        <span>Escribe el nombre para confirmar</span>
                    </div>
                    <div class="input-wrapper mt-2">
                        <i class="fas fa-keyboard input-icon"></i>
                        <input type="text" id="delete-confirm-input"
                            class="form-control-custom del-confirm-input" placeholder="">
                    </div>
                    <div class="del-confirm-hint">
                        <i class="fas fa-circle del-hint-dot" id="del-hint-dot"></i>
                        <span id="del-hint-msg">Escribe el nombre exacto para continuar</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="oc-fixed-footer">
            <div class="oc-divider mb-3"></div>
            <div class="d-flex gap-2">
                <button type="button" class="btn-outline-custom flex-fill" data-bs-dismiss="offcanvas">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </button>
                <button type="button" class="btn-danger-custom flex-fill"
                    id="btn-confirm-delete" onclick="submitDelete()">
                    <i class="fas fa-trash-alt me-1"></i> Confirmar eliminación
                </button>
            </div>
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

<script>const baseUrl = "{{ url('') }}";</script>
<script src="{{ asset('js/components/alerts.js') }}"></script>
<script src="{{ asset('js/components/datatables.js') }}"></script>
<script src="{{ asset('js/pages/asignaturas.js') }}"></script>

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Offcanvas(document.getElementById('offcanvasCrear')).show();
    });
</script>
@endif
@endsection