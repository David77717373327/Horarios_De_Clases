@extends('layouts.master')

@section('content')
<div class="container-fluid py-4">
    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h2 class="mb-1">
                                <i class="fas fa-clipboard-list text-primary"></i>
                                Asignaciones Académicas
                            </h2>
                            <p class="text-muted mb-0">
                                Asigna materias a profesores por grado de forma rápida
                            </p>
                        </div>
                        <div>
                            <select class="form-select form-select-lg" id="yearGlobal" style="width: 150px;">
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Pestañas -->
                    <ul class="nav nav-tabs" id="tabsAsignaciones" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-profesor" data-bs-toggle="tab" 
                                    data-bs-target="#contenido-profesor" type="button">
                                <i class="fas fa-user-tie"></i> Asignación por Profesor
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-listado" data-bs-toggle="tab" 
                                    data-bs-target="#contenido-listado" type="button">
                                <i class="fas fa-list"></i> Ver Listado
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido de las Pestañas -->
    <div class="tab-content" id="contenidoTabs">
        
        <!-- ============================================ -->
        <!-- TAB 1: ASIGNACIÓN POR PROFESOR -->
        <!-- ============================================ -->
        <div class="tab-pane fade show active" id="contenido-profesor" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-tie"></i> Asignación Rápida por Profesor
                    </h5>
                    <small>Asigna todas las materias de un profesor a la vez</small>
                </div>
                <div class="card-body">
                    <!-- Selección de Profesor -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                <i class="fas fa-chalkboard-teacher"></i> Selecciona un Profesor
                            </label>
                            <select class="form-select form-select-lg" id="profesorRapido">
                                <option value="">Seleccione un profesor...</option>
                                @foreach($profesores as $profesor)
                                    <option value="{{ $profesor->id }}">{{ $profesor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">&nbsp;</label>
                            <button type="button" class="btn btn-success btn-lg w-100" id="btnCargarProfesor" disabled>
                                <i class="fas fa-sync"></i> Cargar Asignaturas del Profesor
                            </button>
                        </div>
                    </div>

                    <!-- Información del Profesor -->
                    <div id="infoProfesor" style="display: none;">
                        <div class="alert alert-info">
                            <h5><i class="fas fa-user"></i> <span id="nombreProfesor"></span></h5>
                            <p class="mb-0">
                                <strong>Asignaturas que puede impartir:</strong> 
                                <span id="asignaturasProfesor" class="badge bg-primary ms-2">0</span>
                            </p>
                        </div>

                        <!-- Formulario de Asignación Rápida -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Asigna las materias a los grados</h6>
                            </div>
                            <div class="card-body">
                                <div id="contenedorAsignacionesProfesor">
                                    <!-- Se genera dinámicamente -->
                                </div>

                                <div class="mt-4 text-end">
                                    <button type="button" class="btn btn-success btn-lg" id="btnGuardarProfesor">
                                        <i class="fas fa-save"></i> Guardar Todas las Asignaciones
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loading Spinner -->
                    <div id="loadingProfesor" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-3 text-muted">Cargando información del profesor...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- TAB 2: LISTADO COMPLETO -->
        <!-- ============================================ -->
        <div class="tab-pane fade" id="contenido-listado" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> Listado Completo de Asignaciones
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-layer-group"></i> Nivel
                            </label>
                            <select class="form-select" id="filtroNivel">
                                <option value="">Todos los niveles</option>
                                @foreach($niveles as $nivel)
                                    <option value="{{ $nivel->id }}">{{ $nivel->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-school"></i> Grado
                            </label>
                            <select class="form-select" id="filtroGrado">
                                <option value="">Todos los grados</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-chalkboard-teacher"></i> Profesor
                            </label>
                            <select class="form-select" id="filtroProfesor">
                                <option value="">Todos los profesores</option>
                                @foreach($profesores as $profesor)
                                    <option value="{{ $profesor->id }}">{{ $profesor->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">&nbsp;</label>
                            <button type="button" class="btn btn-primary w-100" id="btnAplicarFiltros">
                                <i class="fas fa-filter"></i> Aplicar Filtros
                            </button>
                        </div>
                    </div>

                    <!-- Estadísticas -->
                    <div class="row mb-4" id="estadisticasResumen">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3 class="mb-0" id="statTotal">0</h3>
                                    <small>Total Asignaciones</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3 class="mb-0" id="statCompletas">0</h3>
                                    <small>Completas</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h3 class="mb-0" id="statParciales">0</h3>
                                    <small>Parciales</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h3 class="mb-0" id="statHoras">0</h3>
                                    <small>Horas Totales</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="tablaListado">
                            <thead class="table-light">
                                <tr>
                                    <th>Profesor</th>
                                    <th>Asignatura</th>
                                    <th>Nivel / Grado</th>
                                    <th class="text-center">Horas Req.</th>
                                    <th class="text-center">Horas Asig.</th>
                                    <th class="text-center">Progreso</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="cuerpoListado">
                                <!-- Se llena dinámicamente -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Loading Spinner -->
                    <div id="loadingListado" class="text-center py-5">
                        <div class="spinner-border text-info" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando listado...</p>
                    </div>

                    <!-- Mensaje Sin Datos -->
                    <div id="mensajeSinDatos" class="text-center py-5" style="display: none;">
                        <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No hay asignaciones</h5>
                        <p class="text-muted">Usa la pestaña "Asignación por Profesor" para crear asignaciones</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Edición Rápida -->
<div class="modal fade" id="modalEditarRapido" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Editar Asignación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditarRapido">
                <div class="modal-body">
                    <input type="hidden" id="editId">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Profesor:</label>
                        <p class="form-control-static" id="editProfesor"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Asignatura:</label>
                        <p class="form-control-static" id="editAsignatura"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Grado:</label>
                        <p class="form-control-static" id="editGrado"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editHoras" class="form-label fw-bold">Horas Semanales:</label>
                        <input type="number" class="form-control" id="editHoras" min="1" max="40" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // ============================================
    // VARIABLES GLOBALES
    // ============================================
    let yearActual = $('#yearGlobal').val();
    let asignaciones = [];
    let profesorActual = null;

    // ============================================
    // INICIALIZACIÓN
    // ============================================
    cargarListado();

    // ============================================
    // EVENT LISTENERS
    // ============================================
    
    $('#yearGlobal').on('change', function() {
        yearActual = $(this).val();
        cargarListado();
    });

    // TAB: ASIGNACIÓN POR PROFESOR
    $('#profesorRapido').on('change', function() {
        const profesorId = $(this).val();
        $('#btnCargarProfesor').prop('disabled', !profesorId);
        $('#infoProfesor').hide();
    });

    $('#btnCargarProfesor').on('click', function() {
        const profesorId = $('#profesorRapido').val();
        if (profesorId) {
            cargarAsignacionesProfesor(profesorId);
        }
    });

    $('#btnGuardarProfesor').on('click', function() {
        guardarAsignacionesProfesor();
    });

    // TAB: LISTADO
    $('#filtroNivel').on('change', function() {
        const nivelId = $(this).val();
        cargarGrados(nivelId, '#filtroGrado');
    });

    $('#btnAplicarFiltros').on('click', function() {
        cargarListado();
    });

    $('#formEditarRapido').on('submit', function(e) {
        e.preventDefault();
        guardarEdicion();
    });

    // ============================================
    // FUNCIONES: ASIGNACIÓN POR PROFESOR
    // ============================================

    function cargarAsignacionesProfesor(profesorId) {
        $('#loadingProfesor').show();
        $('#infoProfesor').hide();

        $.ajax({
            url: `/profesores/${profesorId}`,
            method: 'GET',
            success: function(response) {
                profesorActual = response;
                generarFormularioProfesor(response);
                $('#nombreProfesor').text(response.name);
                $('#asignaturasProfesor').text(response.asignaturas.length);
                $('#infoProfesor').show();
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                mostrarError('Error al cargar información del profesor');
            },
            complete: function() {
                $('#loadingProfesor').hide();
            }
        });
    }

    function generarFormularioProfesor(profesor) {
        let html = '';

        if (profesor.asignaturas.length === 0) {
            html = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Este profesor no tiene asignaturas asignadas. 
                    Debe asignarle asignaturas primero en el módulo de profesores.
                </div>
            `;
            $('#contenedorAsignacionesProfesor').html(html);
            $('#btnGuardarProfesor').hide();
            return;
        }

        $('#btnGuardarProfesor').show();

        profesor.asignaturas.forEach((asignatura) => {
            html += `
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-book text-success"></i> ${asignatura.nombre}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3" id="grados-asignatura-${asignatura.id}">
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" 
                                onclick="agregarGradoAsignatura(${asignatura.id})">
                            <i class="fas fa-plus"></i> Agregar Grado
                        </button>
                    </div>
                </div>
            `;
        });

        $('#contenedorAsignacionesProfesor').html(html);

        profesor.asignaturas.forEach(asignatura => {
            agregarGradoAsignatura(asignatura.id);
        });
    }

    window.agregarGradoAsignatura = function(asignaturaId) {
        const index = Date.now() + Math.random();
        const html = `
            <div class="col-md-12 mb-2 fila-grado" data-asignatura="${asignaturaId}" data-index="${index}">
                <div class="row g-2">
                    <div class="col-md-4">
                        <select class="form-select select-nivel" data-index="${index}">
                            <option value="">Seleccione nivel...</option>
                            @foreach($niveles as $nivel)
                                <option value="{{ $nivel->id }}">{{ $nivel->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select select-grado" data-index="${index}" disabled>
                            <option value="">Primero seleccione nivel...</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="number" class="form-control input-horas" 
                               data-index="${index}"
                               placeholder="Horas" min="1" max="40">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm w-100" 
                                onclick="eliminarFilaGrado(${asignaturaId}, ${index})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        $(`#grados-asignatura-${asignaturaId}`).append(html);

        $(`.select-nivel[data-index="${index}"]`).on('change', function() {
            const nivelId = $(this).val();
            const selectGrado = $(`.select-grado[data-index="${index}"]`);
            
            if (nivelId) {
                cargarGrados(nivelId, selectGrado);
                selectGrado.prop('disabled', false);
            } else {
                selectGrado.html('<option value="">Primero seleccione nivel...</option>').prop('disabled', true);
            }
        });
    };

    window.eliminarFilaGrado = function(asignaturaId, index) {
        $(`.fila-grado[data-asignatura="${asignaturaId}"][data-index="${index}"]`).remove();
    };

    function guardarAsignacionesProfesor() {
        const datos = [];
        const profesorId = $('#profesorRapido').val();

        $('.fila-grado').each(function() {
            const $fila = $(this);
            const asignaturaId = $fila.data('asignatura');
            const index = $fila.data('index');
            
            const gradoId = $(`.select-grado[data-index="${index}"]`).val();
            const horas = $(`.input-horas[data-index="${index}"]`).val();

            if (gradoId && horas && parseInt(horas) > 0) {
                datos.push({
                    profesor_id: profesorId,
                    asignatura_id: asignaturaId,
                    grado_id: gradoId,
                    horas_semanales: parseInt(horas),
                    year: yearActual
                });
            }
        });

        if (datos.length === 0) {
            mostrarAdvertencia('No hay datos para guardar. Complete al menos un grado con sus horas.');
            return;
        }

        $('#btnGuardarProfesor').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: '{{ route("asignaciones.masiva") }}',
            method: 'POST',
            data: {
                asignaciones: datos,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    mostrarExito(`${response.total} asignaciones guardadas correctamente`);
                    cargarListado();
                    $('#profesorRapido').val('');
                    $('#infoProfesor').hide();
                    $('#btnCargarProfesor').prop('disabled', true);
                } else {
                    mostrarError(response.message);
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                mostrarError('Error al guardar las asignaciones');
            },
            complete: function() {
                $('#btnGuardarProfesor').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Todas las Asignaciones');
            }
        });
    }

    // ============================================
    // FUNCIONES: LISTADO
    // ============================================

    function cargarListado() {
        $('#loadingListado').show();
        $('#tablaListado').hide();
        $('#mensajeSinDatos').hide();

        const filtros = {
            year: yearActual,
            nivel_id: $('#filtroNivel').val(),
            grado_id: $('#filtroGrado').val(),
            profesor_id: $('#filtroProfesor').val()
        };

        $.ajax({
            url: '{{ route("asignaciones.listar") }}',
            method: 'GET',
            data: filtros,
            success: function(response) {
                if (response.success) {
                    asignaciones = response.asignaciones;
                    renderizarListado();
                    actualizarEstadisticas();
                } else {
                    mostrarError(response.message);
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                mostrarError('Error al cargar el listado');
            },
            complete: function() {
                $('#loadingListado').hide();
            }
        });
    }

    function renderizarListado() {
        const tbody = $('#cuerpoListado');
        tbody.empty();

        if (asignaciones.length === 0) {
            $('#tablaListado').hide();
            $('#mensajeSinDatos').show();
            return;
        }

        $('#mensajeSinDatos').hide();
        $('#tablaListado').show();

        asignaciones.forEach(asignacion => {
            const porcentaje = asignacion.porcentaje || 0;
            const estado = asignacion.estado || 'pendiente';
            
            let badgeEstado = '';
            if (estado === 'completo') {
                badgeEstado = '<span class="badge bg-success">Completo</span>';
            } else if (estado === 'parcial') {
                badgeEstado = '<span class="badge bg-warning">Parcial</span>';
            } else {
                badgeEstado = '<span class="badge bg-secondary">Pendiente</span>';
            }

            let colorProgreso = 'bg-danger';
            if (porcentaje >= 100) colorProgreso = 'bg-success';
            else if (porcentaje >= 50) colorProgreso = 'bg-warning';

            const row = `
                <tr>
                    <td><i class="fas fa-user text-primary"></i> ${asignacion.profesor.name}</td>
                    <td><i class="fas fa-book text-success"></i> ${asignacion.asignatura.nombre}</td>
                    <td>
                        <span class="badge bg-primary">${asignacion.grado.nivel.nombre}</span>
                        ${asignacion.grado.nombre}
                    </td>
                    <td class="text-center"><strong>${asignacion.horas_semanales}</strong></td>
                    <td class="text-center"><strong>${asignacion.horas_asignadas_count || 0}</strong></td>
                    <td>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar ${colorProgreso}" style="width: ${porcentaje}%">
                                ${porcentaje}%
                            </div>
                        </div>
                    </td>
                    <td class="text-center">${badgeEstado}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-warning" onclick="editarAsignacion(${asignacion.id})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarAsignacion(${asignacion.id})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    function actualizarEstadisticas() {
        const completas = asignaciones.filter(a => a.estado === 'completo').length;
        const parciales = asignaciones.filter(a => a.estado === 'parcial').length;
        const horasTotales = asignaciones.reduce((sum, a) => sum + (a.horas_semanales || 0), 0);

        $('#statTotal').text(asignaciones.length);
        $('#statCompletas').text(completas);
        $('#statParciales').text(parciales);
        $('#statHoras').text(horasTotales);
    }

    window.editarAsignacion = function(id) {
        const asignacion = asignaciones.find(a => a.id === id);
        if (!asignacion) return;

        $('#editId').val(asignacion.id);
        $('#editProfesor').text(asignacion.profesor.name);
        $('#editAsignatura').text(asignacion.asignatura.nombre);
        $('#editGrado').text(`${asignacion.grado.nivel.nombre} - ${asignacion.grado.nombre}`);
        $('#editHoras').val(asignacion.horas_semanales);

        $('#modalEditarRapido').modal('show');
    };

    window.eliminarAsignacion = function(id) {
        const asignacion = asignaciones.find(a => a.id === id);
        if (!asignacion) return;

        Swal.fire({
            title: '¿Eliminar asignación?',
            html: `
                <p><strong>Profesor:</strong> ${asignacion.profesor.name}</p>
                <p><strong>Asignatura:</strong> ${asignacion.asignatura.nombre}</p>
                <p><strong>Grado:</strong> ${asignacion.grado.nombre}</p>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/asignaciones/${id}`,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            mostrarExito(response.message);
                            cargarListado();
                        } else {
                            mostrarError(response.message);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                        mostrarError('Error al eliminar');
                    }
                });
            }
        });
    };

    function guardarEdicion() {
        const id = $('#editId').val();
        const horas = $('#editHoras').val();

        $.ajax({
            url: `/asignaciones/${id}`,
            method: 'PUT',
            data: {
                horas_semanales: horas,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $('#modalEditarRapido').modal('hide');
                    mostrarExito(response.message);
                    cargarListado();
                } else {
                    mostrarError(response.message);
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                mostrarError('Error al actualizar');
            }
        });
    }

    // ============================================
    // FUNCIONES AUXILIARES
    // ============================================

    function cargarGrados(nivelId, targetSelect) {
        const $select = $(targetSelect);
        
        if (!nivelId) {
            $select.html('<option value="">Todos los grados</option>');
            return;
        }

        $select.html('<option value="">Cargando...</option>');

        $.ajax({
            url: `/asignaciones/nivel/${nivelId}/grados`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $select.html('<option value="">Seleccione un grado...</option>');
                    response.grados.forEach(grado => {
                        $select.append(`<option value="${grado.id}">${grado.nombre}</option>`);
                    });
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                $select.html('<option value="">Error al cargar</option>');
            }
        });
    }

    function mostrarExito(mensaje) {
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: mensaje,
            timer: 2000,
            showConfirmButton: false
        });
    }

    function mostrarError(mensaje) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: mensaje
        });
    }

    function mostrarAdvertencia(mensaje) {
        Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: mensaje
        });
    }
});
</script>
@endsection