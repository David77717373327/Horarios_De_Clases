@extends('layouts.master')

@section('title', 'Niveles Académicos')

@section('content')
    <div class="container-fluid py-4 px-4">

        {{-- Header --}}
        <div class="mb-3">
            <div class="d-flex align-items-center justify-content-between mb-19">
                <div>
                    <h2 class="page-title">Niveles Académicos</h2>
                    <p class="page-subtitle">Administra y gestiona los niveles educativos del sistema</p>
                </div>
                <button class="btn-primary-custom" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCrear">

                    <i class="fas fa-graduation-cap"></i> Nuevo Nivel

                </button>
            </div>
            <div class="search-box w-100">
                <span class="search-box-icon"><i class="fas fa-search"></i></span>
                <input type="text" id="buscador-niveles" placeholder="Buscar nivel...">
            </div>
        </div>

        {{-- Alertas --}}
        @if (session('success'))
            <div class="alert-preline alert-preline-success" id="alert-success" role="alert">
                <div class="alert-preline-icon-wrap alert-preline-icon-success">
                    <svg class="alert-preline-svg" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" />
                        <path d="m9 12 2 2 4-4" />
                    </svg>
                </div>
                <div class="alert-preline-body">
                    <h3 class="alert-preline-title">Operación exitosa</h3>
                    <p class="alert-preline-msg">{{ session('success') }}</p>
                </div>
                <button class="alert-preline-close" onclick="hideAlert('alert-success')" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert-preline alert-preline-error" id="alert-error" role="alert">
                <div class="alert-preline-icon-wrap alert-preline-icon-error">
                    <svg class="alert-preline-svg" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M18 6 6 18" />
                        <path d="m6 6 12 12" />
                    </svg>
                </div>
                <div class="alert-preline-body">
                    <h3 class="alert-preline-title">Se produjo un error</h3>
                    <p class="alert-preline-msg">{{ session('error') }}</p>
                </div>
                <button class="alert-preline-close" onclick="hideAlert('alert-error')" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-preline alert-preline-error" id="alert-warning" role="alert">
                <div class="alert-preline-icon-wrap alert-preline-icon-error">
                    <svg class="alert-preline-svg" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M18 6 6 18" />
                        <path d="m6 6 12 12" />
                    </svg>
                </div>
                <div class="alert-preline-body">
                    <h3 class="alert-preline-title">Errores de validación</h3>
                    <ul class="alert-preline-list">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button class="alert-preline-close" onclick="hideAlert('alert-warning')" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        {{-- Tabla --}}
        <div class="card-panel">
            <div class="table-wrapper">
                <table class="table-modern" id="tabla-niveles">
                    <thead>
                        <tr>
                            <th>Nombre del Nivel</th>
                            <th class="text-center" style="width:120px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($niveles as $nivel)
                            <tr>
                                <td>
                                    <div class="cell-with-icon">
                                        <div class="cell-icon"><i class="fas fa-layer-group"></i></div>
                                        <div>
                                            <span class="cell-title">{{ $nivel->nombre }}</span>
                                            <span class="cell-subtitle">Nivel académico</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="actions-group">
                                        <button class="btn-icon btn-icon-blue"
                                            onclick="openEditCanvas({{ $nivel->id }}, '{{ addslashes($nivel->nombre) }}')"
                                            title="Editar"><i class="fas fa-pen"></i></button>
                                        <button class="btn-icon btn-icon-red"
                                            onclick="openDeleteCanvas({{ $nivel->id }}, '{{ addslashes($nivel->nombre) }}', {{ $nivel->grados_count }}, {{ $nivel->descansos_count }})"
                                            title="Eliminar"><i class="fas fa-trash"></i></button>
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
                    <h5 class="mb-0 card-panel-title">Nuevo Nivel</h5>
                    <p class="card-panel-subtitle mb-0">Registra un nuevo nivel académico</p>
                </div>
            </div>
            <button type="button" class="offcanvas-close-btn" data-bs-dismiss="offcanvas">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('niveles.store') }}" id="create-form" class="oc-form-wrap">
            @csrf
            <div class="oc-scroll-body">
                <div class="mb-4">
                    <label class="form-label-custom">Nombre del Nivel</label>
                    <div class="input-wrapper">
                        <i class="fas fa-layer-group input-icon"></i>
                        <input type="text" name="nombre" id="nombre-input" class="form-control-custom"
                            placeholder="Ej: Primaria, Secundaria, Bachillerato..." value="{{ old('nombre') }}" required>
                    </div>
                    @error('nombre')
                        <small class="text-danger d-block mt-1">
                            <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                        </small>
                    @enderror
                    <small class="d-block mt-2" style="font-size:0.78rem;color:#6b7280;">
                        Ingresa el nombre completo del nivel educativo a registrar.
                    </small>
                </div>
            </div>
            <div class="oc-fixed-footer">
                <div class="oc-divider mb-3"></div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn-outline-custom flex-fill" data-bs-dismiss="offcanvas">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn-primary-custom flex-fill">
                        <i class="fas fa-save me-1"></i> Guardar
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
                    <h5 class="mb-0 card-panel-title">Editar Nivel</h5>
                    <p class="card-panel-subtitle mb-0" id="edit-subtitle">Modificando nivel académico</p>
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
                    <label class="form-label-custom">Nombre del Nivel</label>
                    <div class="input-wrapper">
                        <i class="fas fa-layer-group input-icon"></i>
                        <input type="text" name="nombre" id="edit-nombre" class="form-control-custom"
                            placeholder="Ej: Primaria, Secundaria" required>
                    </div>
                    <small class="d-block mt-2" style="font-size:0.78rem;color:#6b7280;">
                        Modifica el nombre del nivel y guarda los cambios.
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
                    <h5 class="mb-0 card-panel-title">Eliminar Nivel</h5>
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
                    <div class="del-target-bg-icon"><i class="fas fa-layer-group"></i></div>
                    <div class="del-target-content">
                        <span class="del-target-eyebrow">Nivel seleccionado</span>
                        <span class="del-target-name" id="delete-nombre-display">—</span>
                    </div>
                </div>
                <div id="delete-warning-block"></div>
                <div id="delete-confirm-input-wrap" style="display:none;">
                    <div class="del-confirm-section">
                        <div class="del-confirm-label">
                            <i class="fas fa-lock"></i>
                            <span>Escribe el nombre del nivel para confirmar</span>
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

    <form id="delete-form" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>

@endsection

@section('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        function hideAlert(id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.classList.add('hiding');
            setTimeout(() => el.remove(), 420);
        }

        $(document).ready(function() {
            const dt = $('#tabla-niveles').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                    emptyTable: '<div style="text-align:center;padding:2.5rem 1rem;"><i class="fas fa-layer-group" style="font-size:2rem;opacity:0.2;display:block;margin-bottom:.75rem;color:#6b7280;"></i><p style="font-size:.9rem;font-weight:600;color:#374151;margin:0 0 4px;">No hay niveles registrados</p><small style="font-size:.8rem;color:#6b7280;">Usa el botón "Nuevo Nivel" para agregar el primero</small></div>'
                },
                pageLength: 10,
                order: [
                    [0, 'asc']
                ],
                columnDefs: [{
                    orderable: false,
                    targets: 1
                }],
                dom: 'rt',
                initComplete: function() {
                    $('#dt-info').append($('.dataTables_info'));
                    $('#dt-paginate').append($('.dataTables_paginate'));
                }
            });

            $('#buscador-niveles').on('keyup', function() {
                dt.search(this.value).draw();
            });
            setTimeout(() => hideAlert('alert-success'), 2500);

            @if ($errors->any())
                new bootstrap.Offcanvas(document.getElementById('offcanvasCrear')).show();
            @endif

            document.getElementById('offcanvasCrear').addEventListener('shown.bs.offcanvas', function() {
                document.getElementById('nombre-input').focus();
            });
            document.getElementById('offcanvasEditar').addEventListener('shown.bs.offcanvas', function() {
                const inp = document.getElementById('edit-nombre');
                inp.focus();
                inp.select();
            });
            document.getElementById('offcanvasEliminar').addEventListener('shown.bs.offcanvas', function() {
                const wrap = document.getElementById('delete-confirm-input-wrap');
                if (wrap.style.display !== 'none') {
                    document.getElementById('delete-confirm-input').focus();
                }
            });
        });

        function openEditCanvas(id, nombre) {
            document.getElementById('edit-nombre').value = nombre;
            document.getElementById('edit-subtitle').textContent = 'Modificando: ' + nombre;
            document.getElementById('edit-form').action = "{{ url('niveles') }}/" + id;
            new bootstrap.Offcanvas(document.getElementById('offcanvasEditar')).show();
        }

        let _deleteId = null,
            _deleteName = null;

        function openDeleteCanvas(id, nombre, gradosCount, descansosCount) {
            _deleteId = id;
            _deleteName = nombre;
            document.getElementById('delete-nombre-display').textContent = nombre;

            const warnBlock = document.getElementById('delete-warning-block');
            const confirmWrap = document.getElementById('delete-confirm-input-wrap');
            const btn = document.getElementById('btn-confirm-delete');

            if (gradosCount > 0 || descansosCount > 0) {
                let chips = '';
                if (gradosCount > 0) chips +=
                    `<div class="del-consequence-chip"><i class="fas fa-chalkboard"></i><span><strong>${gradosCount}</strong> grado(s) y sus horarios</span></div>`;
                if (descansosCount > 0) chips +=
                    `<div class="del-consequence-chip"><i class="fas fa-coffee"></i><span><strong>${descansosCount}</strong> descanso(s)</span></div>`;
                chips +=
                    `<div class="del-consequence-chip"><i class="fas fa-link"></i><span>Todas las asignaciones académicas</span></div>`;
                warnBlock.innerHTML =
                    `<div class="del-consequences-block"><div class="del-consequences-header"><i class="fas fa-exclamation-triangle"></i><span>Impacto de la eliminación</span></div><div class="del-consequences-chips">${chips}</div></div>`;

                confirmWrap.style.display = 'block';
                const inp = document.getElementById('delete-confirm-input');
                inp.value = '';
                inp.placeholder = `Escribe "${nombre}"`;
                inp.oninput = validateDeleteInput;
                document.getElementById('del-hint-dot').style.color = '#9ca3af';
                document.getElementById('del-hint-msg').textContent = 'Escribe el nombre exacto para continuar';
                btn.disabled = true;
                btn.classList.add('btn-danger-disabled');
            } else {
                warnBlock.innerHTML =
                    `<div class="del-safe-block"><div class="del-safe-icon"><i class="fas fa-check-circle"></i></div><div><div class="del-safe-title">Sin dependencias</div><div class="del-safe-desc">Este nivel no tiene grados ni descansos asociados. Puedes eliminarlo de forma segura.</div></div></div>`;
                confirmWrap.style.display = 'none';
                btn.disabled = false;
                btn.classList.remove('btn-danger-disabled');
            }

            new bootstrap.Offcanvas(document.getElementById('offcanvasEliminar')).show();
        }

        function validateDeleteInput() {
            const val = document.getElementById('delete-confirm-input').value.trim();
            const btn = document.getElementById('btn-confirm-delete');
            const dot = document.getElementById('del-hint-dot');
            const msg = document.getElementById('del-hint-msg');
            const match = val === _deleteName;
            if (val === '') {
                dot.style.color = '#9ca3af';
                msg.textContent = 'Escribe el nombre exacto para continuar';
            } else if (match) {
                dot.style.color = '#22c55e';
                msg.textContent = '¡Nombre confirmado! Puedes continuar.';
            } else {
                dot.style.color = '#dc2626';
                msg.textContent = 'El nombre no coincide, verifica e intenta de nuevo.';
            }
            btn.disabled = !match;
            btn.classList.toggle('btn-danger-disabled', !match);
        }

        function submitDelete() {
            const btn = document.getElementById('btn-confirm-delete');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Eliminando...';
            const form = document.getElementById('delete-form');
            form.action = "{{ url('niveles') }}/" + _deleteId;
            form.submit();
        }

        document.getElementById('create-form').addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';
        });
        document.getElementById('edit-form').addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Guardando...';
        });
    </script>
@endsection
