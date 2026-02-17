// ========================================
// VARIABLES GLOBALES
// ========================================
let horarioActual = null;
let todasLasAsignaturas = [];
let nivelActual = null;
let yearActual = null;
let esNuevaClase = false;

$(document).ready(function() {
    cargarAsignaturas();
    
    $('#btnBuscar').on('click', buscarHorarios);
    $('#editAsignatura').on('change', cargarProfesoresPorAsignatura);
    $('#btnGuardarEdicion').on('click', guardarEdicion);
    $('#btnEliminarClase').on('click', eliminarClase);
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
});

// ========================================
// CARGAR ASIGNATURAS
// ========================================
function cargarAsignaturas() {
    $.ajax({
        url: '/horarios/ajax/asignaturas',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                todasLasAsignaturas = response.asignaturas;
                console.log('✅ Asignaturas cargadas:', todasLasAsignaturas.length);
            }
        },
        error: function(xhr) {
            console.error('❌ Error al cargar asignaturas:', xhr);
        }
    });
}

// ========================================
// BUSCAR HORARIOS
// ========================================
function buscarHorarios() {
    const nivelId = $('#filterNivel').val();
    const year = $('#filterYear').val();

    if (!nivelId) {
        Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: 'Por favor selecciona un nivel educativo',
            confirmButtonColor: '#1e40af'
        });
        return;
    }
    
    nivelActual = nivelId;
    yearActual = year;

    cargarHorarios(nivelId, year);
}

// ========================================
// CARGAR HORARIOS
// ========================================
function cargarHorarios(nivelId, year) {
    $('#horariosContainer').html(`
        <div class="card-final shadow-sm">
            <div class="card-body text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-3 text-muted fw-semibold">Cargando horarios...</p>
            </div>
        </div>
    `);

    $.ajax({
        url: '/horarios/listar/obtener',
        method: 'GET',
        data: { nivel_id: nivelId, year: year },
        success: function(response) {
            console.log('📊 Respuesta del servidor:', response);
            
            if (response.success) {
                if (response.horarios.length === 0) {
                    mostrarSinHorarios(response.nivel, response.year);
                } else {
                    renderizarHorarios(response);
                }
            }
        },
        error: function(xhr) {
            console.error('❌ Error al cargar horarios:', xhr);
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudieron cargar los horarios',
                confirmButtonColor: '#1e40af'
            });
            
            $('#horariosContainer').html(`
                <div class="card-final shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 4rem;"></i>
                        <h4 class="mt-3 text-danger">Error al cargar horarios</h4>
                        <p class="text-muted">Intenta nuevamente</p>
                    </div>
                </div>
            `);
        }
    });
}

// ========================================
// MOSTRAR SIN HORARIOS
// ========================================
function mostrarSinHorarios(nivel, year) {
    $('#horariosContainer').html(`
        <div class="card-final shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-calendar-x text-warning" style="font-size: 5rem;"></i>
                <h4 class="mt-4">No hay horarios registrados</h4>
                <p class="text-muted">
                    No se encontraron horarios para <strong>${nivel}</strong> en el año <strong>${year}</strong>
                </p>
                <a href="/horarios" class="btn-final btn-azul-final mt-3">
                    <i class="bi bi-plus-circle me-2"></i>Crear Nuevo Horario
                </a>
            </div>
        </div>
    `);
}

// ========================================
// RENDERIZAR HORARIOS
// ========================================
function renderizarHorarios(data) {
    console.log('🎨 Renderizando horarios:', data);
    
    let html = `
        <div class="mb-4">
            <h4 class="titulo-nivel-horario">
                <i class="bi bi-building me-2"></i>${data.nivel} - Año ${data.year}
            </h4>
        </div>
    `;

    data.horarios.forEach(function(gradoData, index) {
        html += generarTablaHorario(gradoData, index);
    });

    $('#horariosContainer').html(html);

    // ✅ EVENTOS PARA EDICIÓN MANUAL
    attachEventListeners();
}

// ========================================
// ADJUNTAR EVENT LISTENERS
// ========================================
function attachEventListeners() {
    console.log('🔗 Adjuntando event listeners para edición manual...');
    
    // Celdas con clases (editar)
    $('.clase-cell').off('click').on('click', function() {
        const gradoId = $(this).data('grado');
        const nivelId = $(this).data('nivel');
        const dia = $(this).data('dia');
        const hora = $(this).data('hora');
        const gradoNombre = $(this).data('grado-nombre');
        
        console.log('📝 Click en celda con clase:', { gradoId, nivelId, dia, hora, gradoNombre });
        
        abrirModalEdicion(gradoId, nivelId, dia, hora, gradoNombre, false);
    });

    // Celdas vacías (crear nueva clase)
    $('.empty-cell').off('click').on('click', function() {
        const gradoId = $(this).data('grado');
        const nivelId = $(this).data('nivel');
        const dia = $(this).data('dia');
        const hora = $(this).data('hora');
        const gradoNombre = $(this).data('grado-nombre');
        
        console.log('➕ Click en celda vacía:', { gradoId, nivelId, dia, hora, gradoNombre });
        
        abrirModalEdicion(gradoId, nivelId, dia, hora, gradoNombre, true);
    });

    // Botones PDF
    $('.btn-pdf-completo').off('click').on('click', function() {
        const gradoId = $(this).data('grado');
        descargarPDFCompleto(gradoId);
    });

    $('.btn-pdf-materias').off('click').on('click', function() {
        const gradoId = $(this).data('grado');
        descargarPDFMaterias(gradoId);
    });
    
    console.log('✅ Event listeners adjuntados correctamente');
}

// ========================================
// GENERAR TABLA DE HORARIO
// ========================================
function generarTablaHorario(gradoData, index) {
    const config = gradoData.config;
    const dias = config.dias_semana;
    const horasPorDia = config.horas_por_dia;
    
    let html = `
        <div class="card shadow-sm mb-4 horario-card" id="horario-${gradoData.grado_id}">
            <div class="card-header bg-gradient-primary text-white">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h5 class="mb-0">
                            <i class="bi bi-mortarboard-fill me-2"></i>
                            ${gradoData.grado}
                        </h5>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-sm btn-light-custom" onclick="toggleHorario('horario-${gradoData.grado_id}')">
                            <i class="bi bi-eye-fill me-1"></i>Ocultar/Mostrar
                        </button>
                        <button class="btn btn-sm btn-danger btn-pdf-completo" data-grado="${gradoData.grado_id}">
                            <i class="bi bi-file-earmark-pdf-fill me-1"></i>PDF Completo
                        </button>
                        <button class="btn btn-sm btn-success btn-pdf-materias" data-grado="${gradoData.grado_id}">
                            <i class="bi bi-file-earmark-text-fill me-1"></i>PDF Materias
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body horario-body">
                <div class="table-responsive">
                    <table class="table table-bordered horario-table">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Hora</th>
    `;

    dias.forEach(function(dia) {
        html += `<th>${dia}</th>`;
    });

    html += `</tr></thead><tbody>`;

    for (let hora = 1; hora <= horasPorDia; hora++) {
        if (config.recreo_despues_hora && hora == config.recreo_despues_hora + 1) {
            html += `
                <tr class="recreo-row">
                    <td class="text-center">
                        <i class="bi bi-cup-hot-fill"></i>
                    </td>
                    <td colspan="${dias.length}" class="text-center">
                        <strong><i class="bi bi-clock me-2"></i>RECREO - ${config.recreo_duracion} minutos</strong>
                    </td>
                </tr>
            `;
        }

        html += `<tr><td class="hora-cell">${hora}°</td>`;

        dias.forEach(function(dia) {
            const clase = gradoData.horarios[dia] && gradoData.horarios[dia][hora];
            
            if (clase) {
                html += `
                    <td class="clase-cell clase-editable" 
                        data-grado="${gradoData.grado_id}"
                        data-nivel="${nivelActual}"
                        data-dia="${dia}"
                        data-hora="${hora}"
                        data-grado-nombre="${gradoData.grado}"
                        data-asignatura-id="${clase.asignatura_id}"
                        data-profesor-id="${clase.profesor_id}"
                        title="Clic para editar">
                        <div class="clase-content">
                            <div class="asignatura-nombre text-truncate">
                                <i class="bi bi-book-fill me-1"></i>${clase.asignatura}
                            </div>
                            <div class="profesor-nombre text-truncate">
                                <i class="bi bi-person-fill me-1"></i>${clase.profesor}
                            </div>
                        </div>
                    </td>
                `;
            } else {
                html += `
                    <td class="empty-cell"
                        data-grado="${gradoData.grado_id}"
                        data-nivel="${nivelActual}"
                        data-dia="${dia}"
                        data-hora="${hora}"
                        data-grado-nombre="${gradoData.grado}"
                        title="Clic para asignar clase">
                        <div class="clase-content">
                            <span class="text-muted mb-1">${hora}ª hora</span>
                            <div class="btn-add-icon">+</div>
                        </div>
                    </td>
                `;
            }
        });

        html += `</tr>`;
    }

    html += `</tbody></table></div></div></div>`;
    return html;
}












function descargarPDFCompleto(gradoId) {
    const year = $('#filterYear').val();
    
    if (!nivelActual || !gradoId || !year) {
        Swal.fire({
            icon: 'warning',
            title: 'Error',
            text: 'Datos incompletos para generar PDF',
            confirmButtonColor: '#1e40af'
        });
        return;
    }

    Swal.fire({
        title: 'Generando PDF...',
        text: 'Por favor espera un momento',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const url = `/horarios/horarios/pdf?nivel_id=${nivelActual}&year=${year}&grado_id=${gradoId}`;
    
    setTimeout(() => {
        Swal.close();
    }, 3000);
    
    window.location.href = url;
}








function descargarPDFMaterias(gradoId) {
    const year = $('#filterYear').val();
    
    if (!nivelActual || !gradoId || !year) {
        Swal.fire({
            icon: 'warning',
            title: 'Error',
            text: 'Datos incompletos para generar PDF',
            confirmButtonColor: '#1e40af'
        });
        return;
    }

    Swal.fire({
        title: 'Generando PDF...',
        text: 'Por favor espera un momento',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const url = `/horarios/horarios/pdf-solo-materias?nivel_id=${nivelActual}&year=${year}&grado_id=${gradoId}`;
    
    setTimeout(() => {
        Swal.close();
    }, 3000);
    
    window.location.href = url;
}











// ========================================
// ABRIR MODAL DE EDICIÓN - MEJORADO
// ========================================
function abrirModalEdicion(gradoId, nivelId, dia, hora, gradoNombre, esNueva = false) {
    console.log('🔓 Abriendo modal de edición:', { gradoId, nivelId, dia, hora, gradoNombre, esNueva });
    
    esNuevaClase = esNueva;
    
    // Limpiar campos
    $('#editAsignatura').val('').trigger('change');
    $('#editProfesor').val('').prop('disabled', true).html('<option value="">Primero seleccione una asignatura</option>');
    $('#editWarning').addClass('d-none');
    
    // Setear valores en campos ocultos
    $('#editGradoId').val(gradoId);
    $('#editNivelId').val(nivelId);
    $('#editYear').val(yearActual);
    $('#editDia').val(dia);
    $('#editHora').val(hora);
    
    // Configurar título y botón eliminar
    if (esNueva) {
        $('#modalTitulo').html('<i class="bi bi-plus-circle me-2"></i>Asignar Nueva Clase');
        $('#btnEliminarClase').hide();
    } else {
        $('#modalTitulo').html('<i class="bi bi-pencil-square me-2"></i>Editar Clase');
        $('#btnEliminarClase').show();
    }
    
    $('#editInfoTexto').text(`${gradoNombre} - ${dia} - Hora ${hora}`);
    
    // Cargar asignaturas
    let options = '<option value="">Seleccione una asignatura</option>';
    todasLasAsignaturas.forEach(asig => {
        options += `<option value="${asig.id}">${asig.nombre}</option>`;
    });
    $('#editAsignatura').html(options);
    
    // Si es edición, cargar datos existentes
    if (!esNueva) {
        const celda = $(`.clase-cell[data-grado="${gradoId}"][data-dia="${dia}"][data-hora="${hora}"]`);
        
        if (celda.length) {
            const asignaturaId = celda.data('asignatura-id');
            const profesorId = celda.data('profesor-id');
            
            console.log('📖 Cargando datos existentes:', { asignaturaId, profesorId });
            
            // Setear asignatura y cargar profesores
            $('#editAsignatura').val(asignaturaId);
            cargarProfesoresPorAsignatura().then(() => {
                $('#editProfesor').val(profesorId);
            });
        }
    }
    
    // Mostrar modal
    $('#modalEditarClase').modal('show');
    console.log('✅ Modal abierto correctamente');
}

// ========================================
// CARGAR PROFESORES POR ASIGNATURA - MEJORADO
// ========================================
function cargarProfesoresPorAsignatura() {
    return new Promise((resolve, reject) => {
        const asignaturaId = $('#editAsignatura').val();
        
        console.log('👨‍🏫 Cargando profesores para asignatura:', asignaturaId);
        
        if (!asignaturaId) {
            $('#editProfesor').prop('disabled', true).html('<option value="">Primero seleccione una asignatura</option>');
            resolve();
            return;
        }
        
        $.ajax({
            url: `/horarios/ajax/profesores-por-asignatura/${asignaturaId}`,
            method: 'GET',
            success: function(response) {
                console.log('✅ Profesores cargados:', response);
                
                if (response.success && response.profesores) {
                    let options = '<option value="">Seleccione un profesor</option>';
                    response.profesores.forEach(prof => {
                        options += `<option value="${prof.id}">${prof.name}</option>`;
                    });
                    $('#editProfesor').prop('disabled', false).html(options);
                    resolve();
                } else {
                    console.warn('⚠️ No se encontraron profesores para esta asignatura');
                    $('#editProfesor').prop('disabled', true).html('<option value="">No hay profesores disponibles</option>');
                    resolve();
                }
            },
            error: function(xhr) {
                console.error('❌ Error al cargar profesores:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los profesores. Verifique que existan asignaciones académicas.',
                    confirmButtonColor: '#1e40af'
                });
                $('#editProfesor').prop('disabled', true).html('<option value="">Error al cargar profesores</option>');
                reject(xhr);
            }
        });
    });
}

// ========================================
// GUARDAR EDICIÓN - MEJORADO
// ========================================
function guardarEdicion() {
    const gradoId = $('#editGradoId').val();
    const nivelId = $('#editNivelId').val();
    const year = $('#editYear').val();
    const dia = $('#editDia').val();
    const hora = $('#editHora').val();
    const asignaturaId = $('#editAsignatura').val();
    const profesorId = $('#editProfesor').val();
    
    console.log('💾 Guardando edición:', { gradoId, nivelId, year, dia, hora, asignaturaId, profesorId });
    
    // Validar campos
    if (!asignaturaId || !profesorId) {
        Swal.fire({
            icon: 'warning',
            title: 'Error',
            text: 'Por favor complete todos los campos',
            confirmButtonColor: '#1e40af'
        });
        return;
    }
    
    // Ocultar warning previo
    $('#editWarning').addClass('d-none');
    
    // Validar conflictos
    $.ajax({
        url: '/horarios/validar-conflicto',
        method: 'POST',
        data: {
            nivel_id: nivelId,
            grado_id: gradoId,
            year: year,
            dia: dia,
            hora_numero: hora,
            profesor_id: profesorId
        },
        success: function(response) {
            console.log('📋 Validación de conflictos:', response);
            
            if (!response.success) {
                $('#editWarning').removeClass('d-none');
                $('#editWarningText').html(response.message);
                return;
            }
            
            // Si no hay conflictos, proceder a guardar
            guardarClaseEditada(gradoId, nivelId, year, dia, hora, asignaturaId, profesorId);
        },
        error: function(xhr) {
            console.error('❌ Error en validación:', xhr);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al validar el horario',
                confirmButtonColor: '#1e40af'
            });
        }
    });
}

// ========================================
// GUARDAR CLASE EDITADA - MEJORADO
// ========================================
function guardarClaseEditada(gradoId, nivelId, year, dia, hora, asignaturaId, profesorId) {
    console.log('💾 Guardando clase en base de datos...');
    
    // Obtener configuración actual del horario
    $.ajax({
        url: '/horarios/obtener',
        method: 'GET',
        data: {
            nivel_id: nivelId,
            grado_id: gradoId,
            year: year
        },
        success: function(response) {
            console.log('📊 Configuración obtenida:', response);
            
            if (!response.success) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo obtener la configuración del horario',
                    confirmButtonColor: '#1e40af'
                });
                return;
            }
            
            const config = response.config;
            let clases = [];
            
            // Mantener las clases existentes excepto la que estamos editando
            response.horarios.forEach(h => {
                if (!(h.dia_semana === dia && h.hora_numero == hora)) {
                    clases.push({
                        dia: h.dia_semana,
                        hora_numero: h.hora_numero,
                        asignatura_id: h.asignatura_id,
                        profesor_id: h.profesor_id
                    });
                }
            });
            
            // Agregar la nueva/editada clase
            clases.push({
                dia: dia,
                hora_numero: parseInt(hora),
                asignatura_id: parseInt(asignaturaId),
                profesor_id: parseInt(profesorId)
            });
            
            console.log('📝 Clases a guardar:', clases);
            
            // Guardar horario completo
            $.ajax({
                url: '/horarios/guardar',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    nivel_id: parseInt(nivelId),
                    grado_id: parseInt(gradoId),
                    year: parseInt(year),
                    hora_inicio: config.hora_inicio,
                    hora_fin: config.hora_fin,
                    duracion_clase: parseInt(config.duracion_clase),
                    horas_por_dia: parseInt(config.horas_por_dia),
                    dias_semana: config.dias_semana,
                    recreo_despues_hora: config.recreo_despues_hora,
                    recreo_duracion: config.recreo_duracion,
                    clases: clases
                }),
                success: function(response) {
                    console.log('✅ Horario guardado exitosamente:', response);
                    
                    if (response.success) {
                        $('#modalEditarClase').modal('hide');
                        
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: esNuevaClase ? 'Clase asignada correctamente' : 'Clase actualizada correctamente',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        
                        // Actualizar la celda en tiempo real
                        actualizarCeldaEnTiempoReal(gradoId, dia, hora, asignaturaId, profesorId);
                    }
                },
                error: function(xhr) {
                    console.error('❌ Error al guardar:', xhr);
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Error al guardar el horario',
                        confirmButtonColor: '#1e40af'
                    });
                }
            });
        },
        error: function(xhr) {
            console.error('❌ Error al obtener horario:', xhr);
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo obtener el horario actual',
                confirmButtonColor: '#1e40af'
            });
        }
    });
}

// ========================================
// ACTUALIZAR CELDA EN TIEMPO REAL - MEJORADO
// ========================================
function actualizarCeldaEnTiempoReal(gradoId, dia, hora, asignaturaId, profesorId) {
    console.log('🔄 Actualizando celda en tiempo real:', { gradoId, dia, hora, asignaturaId, profesorId });
    
    // Obtener nombre de la asignatura
    const asignatura = todasLasAsignaturas.find(a => a.id == asignaturaId);
    const asignaturaNombre = asignatura ? asignatura.nombre : 'Asignatura';
    
    // Obtener nombre del profesor
    $.ajax({
        url: `/horarios/ajax/profesor/${profesorId}`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const profesorNombre = response.profesor.name;
                
                // Buscar la celda a actualizar
                let celda = $(`td[data-grado="${gradoId}"][data-dia="${dia}"][data-hora="${hora}"]`);
                
                if (celda.length) {
                    const nuevoHTML = `
                        <div class="clase-content">
                            <div class="asignatura-nombre text-truncate">
                                <i class="bi bi-book-fill me-1"></i>${asignaturaNombre}
                            </div>
                            <div class="profesor-nombre text-truncate">
                                <i class="bi bi-person-fill me-1"></i>${profesorNombre}
                            </div>
                        </div>
                    `;
                    
                    // Actualizar clases CSS y atributos
                    celda.removeClass('empty-cell').addClass('clase-cell clase-editable');
                    celda.attr('data-asignatura-id', asignaturaId);
                    celda.attr('data-profesor-id', profesorId);
                    celda.attr('title', 'Clic para editar');
                    celda.addClass('celda-actualizando');
                    celda.html(nuevoHTML);
                    
                    setTimeout(() => {
                        celda.removeClass('celda-actualizando');
                    }, 600);
                    
                    // Re-adjuntar event listener
                    celda.off('click').on('click', function() {
                        const gId = $(this).data('grado');
                        const nId = $(this).data('nivel');
                        const d = $(this).data('dia');
                        const h = $(this).data('hora');
                        const gNombre = $(this).data('grado-nombre');
                        
                        abrirModalEdicion(gId, nId, d, h, gNombre, false);
                    });
                    
                    console.log('✅ Celda actualizada correctamente');
                }
            }
        },
        error: function(xhr) {
            console.error('❌ Error al obtener profesor:', xhr);
        }
    });
}

// ========================================
// ELIMINAR CLASE - MEJORADO
// ========================================
function eliminarClase() {
    const gradoId = $('#editGradoId').val();
    const nivelId = $('#editNivelId').val();
    const year = $('#editYear').val();
    const dia = $('#editDia').val();
    const hora = $('#editHora').val();
    
    console.log('🗑️ Solicitando eliminación:', { gradoId, nivelId, year, dia, hora });
    
    Swal.fire({
        title: '¿Eliminar esta clase?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            eliminarClaseConfirmado(gradoId, nivelId, year, dia, hora);
        }
    });
}

function eliminarClaseConfirmado(gradoId, nivelId, year, dia, hora) {
    console.log('💥 Eliminando clase...');
    
    $.ajax({
        url: '/horarios/obtener',
        method: 'GET',
        data: { nivel_id: nivelId, grado_id: gradoId, year: year },
        success: function(response) {
            if (response.success) {
                const config = response.config;
                
                // Filtrar todas las clases EXCEPTO la que queremos eliminar
                let clases = response.horarios
                    .filter(h => !(h.dia_semana === dia && h.hora_numero == hora))
                    .map(h => ({
                        dia: h.dia_semana,
                        hora_numero: h.hora_numero,
                        asignatura_id: h.asignatura_id,
                        profesor_id: h.profesor_id
                    }));
                
                console.log('📝 Clases después de eliminar:', clases);
                
                // Guardar horario sin la clase eliminada
                $.ajax({
                    url: '/horarios/guardar',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        nivel_id: parseInt(nivelId),
                        grado_id: parseInt(gradoId),
                        year: parseInt(year),
                        hora_inicio: config.hora_inicio,
                        hora_fin: config.hora_fin,
                        duracion_clase: parseInt(config.duracion_clase),
                        horas_por_dia: parseInt(config.horas_por_dia),
                        dias_semana: config.dias_semana,
                        recreo_despues_hora: config.recreo_despues_hora,
                        recreo_duracion: config.recreo_duracion,
                        clases: clases
                    }),
                    success: function(response) {
                        console.log('✅ Clase eliminada exitosamente');
                        
                        if (response.success) {
                            $('#modalEditarClase').modal('hide');
                            
                            Swal.fire({
                                icon: 'success',
                                title: '¡Eliminada!',
                                text: 'Clase eliminada correctamente',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            
                            // Actualizar celda a estado vacío
                            const celda = $(`td[data-grado="${gradoId}"][data-dia="${dia}"][data-hora="${hora}"]`);
                            
                            if (celda.length) {
                                celda.removeClass('clase-cell clase-editable').addClass('empty-cell');
                                celda.removeAttr('data-asignatura-id');
                                celda.removeAttr('data-profesor-id');
                                celda.attr('title', 'Clic para asignar clase');
                                celda.addClass('celda-actualizando');
                                celda.html(`
                                    <div class="clase-content">
                                        <span class="text-muted mb-1">${hora}ª hora</span>
                                        <div class="btn-add-icon">+</div>
                                    </div>
                                `);
                                
                                setTimeout(() => {
                                    celda.removeClass('celda-actualizando');
                                }, 600);
                                
                                // Re-adjuntar event listener para celda vacía
                                celda.off('click').on('click', function() {
                                    const gId = $(this).data('grado');
                                    const nId = $(this).data('nivel');
                                    const d = $(this).data('dia');
                                    const h = $(this).data('hora');
                                    const gNombre = $(this).data('grado-nombre');
                                    
                                    abrirModalEdicion(gId, nId, d, h, gNombre, true);
                                });
                                
                                console.log('✅ Celda convertida a vacía correctamente');
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error('❌ Error al eliminar:', xhr);
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Error al eliminar la clase',
                            confirmButtonColor: '#1e40af'
                        });
                    }
                });
            }
        },
        error: function(xhr) {
            console.error('❌ Error al obtener horario:', xhr);
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo obtener el horario actual',
                confirmButtonColor: '#1e40af'
            });
        }
    });
}

// ========================================
// TOGGLE HORARIO
// ========================================
window.toggleHorario = function(id) {
    $(`#${id} .horario-body`).slideToggle(300);
};