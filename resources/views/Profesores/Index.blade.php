@extends('layouts.master')

@section('title', 'Gestión de Profesores')

@section('content')
<div class="container-fluid py-4 px-4">

    {{-- Header --}}
    <x-page-header
        title="Gestión de Profesores"
        subtitle="Administra los profesores y sus asignaturas asignadas"
        button-label="Nuevo Profesor"
        button-target="#offcanvasCrear"
        button-icon="fa-user-tie"
    >
        <x:slot name="search">
            <input type="text" id="buscador-profesores" placeholder="Buscar profesor...">
        </x:slot>
    </x-page-header>

    {{-- Contenedor para alertas dinámicas JS (justo debajo del header) --}}
    <div id="alert-dynamic-container"></div>

    {{-- Alertas de sesión --}}
    @if (session('success'))
        <x-alert type="success" id="alert-success" title="Operación exitosa" :message="session('success')" />
    @endif
    @if (session('error'))
        <x-alert type="error" id="alert-error" title="Se produjo un error" :message="session('error')" />
    @endif

    {{-- Tabla --}}
    <div class="card-panel">
        <div class="table-wrapper">
            <table class="table-modern" id="tabla-profesores">
                <thead>
                    <tr>
                        <th>Nombre del Profesor</th>
                        <th>Asignaturas</th>
                        <th class="text-center" style="width:150px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($profesores as $profesor)
                        <tr data-id="{{ $profesor->id }}">
                            <td>
                                <div class="cell-with-icon">
                                    <div class="cell-icon">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div>
                                        <span class="cell-title">{{ $profesor->name }}</span>
                                        <span class="cell-subtitle">Profesor</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @forelse ($profesor->asignaturas as $asignatura)
                                        <span class="badge-nivel">
                                            <i class="fas fa-book me-1"></i>{{ $asignatura->nombre }}
                                        </span>
                                    @empty
                                        <span class="badge-nivel" style="opacity:0.6; font-style:italic;">
                                            <i class="fas fa-exclamation-circle me-1"></i>Sin asignaturas
                                        </span>
                                    @endforelse
                                </div>
                            </td>
                            <td>
                                <div class="actions-group">
                                    <button class="btn-icon btn-icon-green"
                                        onclick="openAsignarCanvas({{ $profesor->id }}, '{{ addslashes($profesor->name) }}')"
                                        title="Asignar asignaturas">
                                        <i class="fas fa-book"></i>
                                    </button>
                                    <button class="btn-icon btn-icon-blue"
                                        onclick="openEditCanvas({{ $profesor->id }}, '{{ addslashes($profesor->name) }}')"
                                        title="Editar">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button class="btn-icon btn-icon-red"
                                        onclick="openDeleteCanvas({{ $profesor->id }}, '{{ addslashes($profesor->name) }}', {{ $profesor->asignaturas->count() }})"
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

{{-- OFFCANVAS: CREAR --}}
<div class="offcanvas offcanvas-end offcanvas-modern" tabindex="-1" id="offcanvasCrear">
    <div class="offcanvas-modern-header">
        <div class="d-flex align-items-center gap-2">
            <div class="card-panel-icon oc-icon-blue" style="width:32px;height:32px;font-size:0.8rem;">
                <i class="fas fa-plus"></i>
            </div>
            <div>
                <h5 class="mb-0 card-panel-title">Nuevo Profesor</h5>
                <p class="card-panel-subtitle mb-0">Registra un nuevo profesor</p>
            </div>
        </div>
        <button type="button" class="offcanvas-close-btn" data-bs-dismiss="offcanvas">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="oc-form-wrap">
        <div class="oc-scroll-body">

            <div class="mb-4">
                <label class="form-label-custom">Nombre Completo</label>
                <div class="input-wrapper">
                    <i class="fas fa-user-tie input-icon"></i>
                    <input type="text" id="crear-nombre" class="form-control-custom"
                        placeholder="Ej: Juan Pérez García">
                </div>
                <div id="error-crear-nombre" class="text-danger" style="font-size:0.8rem; margin-top:4px; display:none;">
                    <i class="fas fa-exclamation-circle me-1"></i><span></span>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label-custom">Asignaturas</label>
                <div class="checkbox-grid" id="crear-asignaturas-list">
                    @foreach ($asignaturas as $asignatura)
                        <label class="checkbox-label-grid">
                            <input type="checkbox" name="asignaturas[]"
                                value="{{ $asignatura->id }}"
                                id="crear_asig_{{ $asignatura->id }}">
                            <span>{{ $asignatura->nombre }}</span>
                        </label>
                    @endforeach
                </div>
                <small class="d-block mt-2" style="font-size:0.78rem;color:#6b7280;">
                    Selecciona las asignaturas que impartirá este profesor.
                </small>
            </div>

        </div>
        <div class="oc-fixed-footer">
            <div class="oc-divider mb-3"></div>
            <div class="d-flex gap-2">
                <button type="button" class="btn-outline-custom flex-fill" data-bs-dismiss="offcanvas">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" class="btn-primary-custom flex-fill" id="btn-crear" onclick="crearProfesor()">
                    <i class="fas fa-save me-1"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- OFFCANVAS: EDITAR --}}
<div class="offcanvas offcanvas-end offcanvas-modern" tabindex="-1" id="offcanvasEditar">
    <div class="offcanvas-modern-header">
        <div class="d-flex align-items-center gap-2">
            <div class="card-panel-icon oc-icon-amber" style="width:32px;height:32px;font-size:0.8rem;">
                <i class="fas fa-pen"></i>
            </div>
            <div>
                <h5 class="mb-0 card-panel-title">Editar Profesor</h5>
                <p class="card-panel-subtitle mb-0" id="edit-subtitle">Modificando profesor</p>
            </div>
        </div>
        <button type="button" class="offcanvas-close-btn" data-bs-dismiss="offcanvas">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="oc-form-wrap">
        <div class="oc-scroll-body">

            <div class="mb-4">
                <label class="form-label-custom">Nombre Completo</label>
                <div class="input-wrapper">
                    <i class="fas fa-user-tie input-icon"></i>
                    <input type="text" id="edit-nombre" class="form-control-custom"
                        placeholder="Nombre del profesor">
                </div>
                <div id="error-edit-nombre" class="text-danger" style="font-size:0.8rem; margin-top:4px; display:none;">
                    <i class="fas fa-exclamation-circle me-1"></i><span></span>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label-custom">Asignaturas</label>
                <div class="checkbox-grid" id="edit-asignaturas-list">
                    {{-- Se llena dinámicamente desde profesores.js --}}
                </div>
                <small class="d-block mt-2" style="font-size:0.78rem;color:#6b7280;">
                    Modifica las asignaturas del profesor.
                </small>
            </div>

        </div>
        <div class="oc-fixed-footer">
            <div class="oc-divider mb-3"></div>
            <div class="d-flex gap-2">
                <button type="button" class="btn-outline-custom flex-fill" data-bs-dismiss="offcanvas">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" class="btn-warning-custom flex-fill" id="btn-editar" onclick="actualizarProfesor()">
                    <i class="fas fa-save me-1"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

{{-- OFFCANVAS: ASIGNAR ASIGNATURAS --}}
<div class="offcanvas offcanvas-end offcanvas-modern" tabindex="-1" id="offcanvasAsignar">
    <div class="offcanvas-modern-header">
        <div class="d-flex align-items-center gap-2">
            <div class="card-panel-icon oc-icon-green" style="width:32px;height:32px;font-size:0.8rem;">
                <i class="fas fa-book"></i>
            </div>
            <div>
                <h5 class="mb-0 card-panel-title">Asignar Asignaturas</h5>
                <p class="card-panel-subtitle mb-0" id="asignar-subtitle">Selecciona las asignaturas</p>
            </div>
        </div>
        <button type="button" class="offcanvas-close-btn" data-bs-dismiss="offcanvas">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="oc-form-wrap">
        <div class="oc-scroll-body">
            <div class="checkbox-grid" id="asignar-asignaturas-list">
                {{-- Se llena dinámicamente desde profesores.js --}}
            </div>
        </div>
        <div class="oc-fixed-footer">
            <div class="oc-divider mb-3"></div>
            <div class="d-flex gap-2">
                <button type="button" class="btn-outline-custom flex-fill" data-bs-dismiss="offcanvas">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" class="btn-primary-custom flex-fill" id="btn-asignar" onclick="guardarAsignaturas()">
                    <i class="fas fa-save me-1"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

{{-- OFFCANVAS: ELIMINAR --}}
<div class="offcanvas offcanvas-end offcanvas-modern offcanvas-delete" tabindex="-1" id="offcanvasEliminar">
    <div class="offcanvas-modern-header">
        <div class="d-flex align-items-center gap-2">
            <div class="card-panel-icon oc-icon-red" style="width:32px;height:32px;font-size:0.8rem;">
                <i class="fas fa-trash-alt"></i>
            </div>
            <div>
                <h5 class="mb-0 card-panel-title">Eliminar Profesor</h5>
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
                <div class="del-target-bg-icon"><i class="fas fa-user-tie"></i></div>
                <div class="del-target-content">
                    <span class="del-target-eyebrow">Profesor seleccionado</span>
                    <span class="del-target-name" id="delete-nombre-display">—</span>
                </div>
            </div>
            <div id="delete-warning-block"></div>
            <div id="delete-confirm-input-wrap" style="display:none;">
                <div class="del-confirm-section">
                    <div class="del-confirm-label">
                        <i class="fas fa-lock"></i>
                        <span>Escribe el nombre del profesor para confirmar</span>
                    </div>
                    <div class="input-wrapper mt-2">
                        <i class="fas fa-keyboard input-icon"></i>
                        <input type="text" id="delete-confirm-input" class="form-control-custom del-confirm-input"
                            placeholder="">
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
                <button type="button" class="btn-danger-custom flex-fill" id="btn-confirm-delete"
                    onclick="submitDelete()">
                    <i class="fas fa-trash-alt me-1"></i> Confirmar eliminación
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        const baseUrl          = "{{ url('') }}";
        const todasAsignaturas = @json($asignaturas);
        const csrfToken        = document.querySelector('meta[name="csrf-token"]').content;
    </script>
    <script src="{{ asset('js/components/alerts.js') }}"></script>
    <script src="{{ asset('js/components/datatables.js') }}"></script>
    <script src="{{ asset('js/pages/profesores.js') }}"></script>
@endsection