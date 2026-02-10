// ========================================
// VARIABLES GLOBALES
// ========================================
let horarioActual = null;
let todasLasAsignaturas = [];
let nivelActual = null;
let yearActual = null;
let esNuevaClase = false; // ✨ NUEVO: Determinar si es creación o edición

$(document).ready(function() {
    // Cargar asignaturas al inicio
    cargarAsignaturas();
    
    // ========================================
    // EVENT LISTENERS
    // ========================================
    $('#btnBuscar').on('click', buscarHorarios);
    
    // ⭐ Modal de edición
    $('#editAsignatura').on('change', cargarProfesoresPorAsignatura);
    $('#btnGuardarEdicion').on('click', guardarEdicion);
    $('#btnEliminarClase').on('click', eliminarClase);
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
            }
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
            text: 'Por favor selecciona un nivel educativo'
        });
        return;
    }
    
    nivelActual = nivelId;
    yearActual = year;

    cargarHorarios(nivelId, year);
    cargarEstadisticas(nivelId, year);
}

// ========================================
// CARGAR HORARIOS
// ========================================
function cargarHorarios(nivelId, year) {
    $('#horariosContainer').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3 text-muted">Cargando horarios...</p>
        </div>
    `);

    $.ajax({
        url: '/horarios/listar/obtener',
        method: 'GET',
        data: { nivel_id: nivelId, year: year },
        success: function(response) {
            if (response.success) {
                if (response.horarios.length === 0) {
                    mostrarSinHorarios(response.nivel, response.year);
                } else {
                    renderizarHorarios(response);
                }
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudieron cargar los horarios'
            });
            $('#horariosContainer').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    Error al cargar los horarios
                </div>
            `);
        }
    });
}

// ========================================
// CARGAR ESTADÍSTICAS
// ========================================
function cargarEstadisticas(nivelId, year) {
    $.ajax({
        url: '/horarios/listar/estadisticas',
        method: 'GET',
        data: { nivel_id: nivelId, year: year },
        success: function(response) {
            if (response.success) {
                const stats = response.estadisticas;
                $('#statTotalGrados').text(stats.total_grados);
                $('#statGradosConHorario').text(stats.grados_con_horario);
                $('#statTotalClases').text(stats.total_clases);
                $('#statProfesores').text(stats.profesores_unicos);
                $('#estadisticasContainer').removeClass('d-none');
            }
        }
    });
}

// ========================================
// MOSTRAR SIN HORARIOS
// ========================================
function mostrarSinHorarios(nivel, year) {
    $('#horariosContainer').html(`
        <div class="text-center py-5">
            <i class="bi bi-calendar-x display-1 text-warning mb-3"></i>
            <h4>No hay horarios registrados</h4>
            <p class="text-muted">No se encontraron horarios para ${nivel} en el año ${year}</p>
            <a href="/horarios" class="btn btn-primary mt-3">
                <i class="bi bi-plus-circle"></i> Crear Nuevo Horario
            </a>
        </div>
    `);
}

// ========================================
// RENDERIZAR HORARIOS
// ========================================
function renderizarHorarios(data) {
    let html = `
        <div class="mb-3">
            <h4 class="text-primary">
                <i class="bi bi-building"></i> ${data.nivel} - Año ${data.year}
            </h4>
        </div>
    `;

    data.horarios.forEach(function(gradoData, index) {
        html += generarTablaHorario(gradoData, index);
    });

    $('#horariosContainer').html(html);

    // ⭐ Event listeners para edición (clases existentes)
    $('.clase-cell').on('click', function() {
        const gradoId = $(this).data('grado');
        const nivelId = $(this).data('nivel');
        const dia = $(this).data('dia');
        const hora = $(this).data('hora');
        const gradoNombre = $(this).data('grado-nombre');
        
        abrirModalEdicion(gradoId, nivelId, dia, hora, gradoNombre, false);
    });

    // ✨ NUEVO: Event listeners para celdas vacías
    $('.empty-cell').on('click', function() {
        const gradoId = $(this).data('grado');
        const nivelId = $(this).data('nivel');
        const dia = $(this).data('dia');
        const hora = $(this).data('hora');
        const gradoNombre = $(this).data('grado-nombre');
        
        abrirModalEdicion(gradoId, nivelId, dia, hora, gradoNombre, true);
    });

    // Botón de imprimir
    $('.btn-print').on('click', function() {
        const gradoId = $(this).data('grado');
        imprimirHorario(gradoId);
    });
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
            <div class="card-header bg-gradient-info text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-mortarboard-fill me-2"></i>
                        ${gradoData.grado}
                    </h5>
                    <div>
                        <button class="btn btn-sm btn-light me-2" onclick="toggleHorario('horario-${gradoData.grado_id}')">
                            <i class="bi bi-eye"></i> Ocultar/Mostrar
                        </button>
                        <button class="btn btn-sm btn-light btn-print" data-grado="${gradoData.grado_id}">
                            <i class="bi bi-printer"></i> Imprimir
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body horario-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Haz clic en cualquier celda</strong> para asignar o editar clases
                </div>
                
                <div class="table-responsive">
                    <table class="horario-table">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 80px;">Hora</th>
    `;

    // Encabezados de días
    dias.forEach(function(dia) {
        html += `<th class="text-center">${dia}</th>`;
    });

    html += `</tr></thead><tbody>`;

    // Generar filas
    for (let hora = 1; hora <= horasPorDia; hora++) {
        // Recreo
        if (config.recreo_despues_hora && hora == config.recreo_despues_hora + 1) {
            html += `
                <tr class="recreo-row">
                    <td class="text-center fw-bold bg-warning bg-opacity-25">
                        <i class="bi bi-cup-hot"></i> RECREO
                    </td>
                    <td colspan="${dias.length}" class="text-center fw-bold bg-warning bg-opacity-25">
                        ${config.recreo_duracion} minutos
                    </td>
                </tr>
            `;
        }

        html += `<tr><td class="text-center fw-bold hora-cell">${hora}</td>`;

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
    <div class="clase-info">
        <div class="asignatura-nombre text-truncate">
            <i class="bi bi-book"></i> ${clase.asignatura}
        </div>
        <div class="profesor-nombre text-truncate small">
            <i class="bi bi-person"></i> ${clase.profesor}
        </div>
    </div>

    <button class="btn-delete-clase"
        data-grado="${gradoData.grado_id}"
        data-dia="${dia}"
        data-hora="${hora}"
        title="Eliminar clase">
        <i class="bi bi-trash3"></i>
    </button>
</div>



                    </td>
                `;
            } else {
                // ✨ NUEVO: Celdas vacías ahora son clicables
               html += `
<td class="empty-cell"
    data-grado="${gradoData.grado_id}"
    data-nivel="${nivelActual}"
    data-dia="${dia}"
    data-hora="${hora}"
    data-grado-nombre="${gradoData.grado}"
    title="Clic para asignar clase">

    <div class="clase-content">
        <span class="text-muted mb-1">${hora} aula</span>
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





function eliminarDesdeCelda(gradoId, dia, hora) {
    Swal.fire({
        title: '¿Eliminar esta clase?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (result.isConfirmed) {
            $('#editGradoId').val(gradoId);
            $('#editDia').val(dia);
            $('#editHora').val(hora);
            eliminarClase();
        }
    });
}



// ========================================
// ⭐ ABRIR MODAL DE EDICIÓN
// ========================================
function abrirModalEdicion(gradoId, nivelId, dia, hora, gradoNombre, esNueva = false) {
    esNuevaClase = esNueva; // Guardar el estado
    
    // Guardar datos actuales
    $('#editGradoId').val(gradoId);
    $('#editNivelId').val(nivelId);
    $('#editYear').val(yearActual);
    $('#editDia').val(dia);
    $('#editHora').val(hora);
    
    // ✨ Cambiar título según acción
    if (esNueva) {
        $('#modalTitulo').html('<i class="bi bi-plus-circle me-2"></i>Asignar Nueva Clase');
        $('#editAccion').text('Asignando');
        $('#btnEliminarClase').hide(); // No mostrar botón eliminar para nuevas clases
    } else {
        $('#modalTitulo').html('<i class="bi bi-pencil-square me-2"></i>Editar Clase');
        $('#editAccion').text('Editando');
        $('#btnEliminarClase').show();
    }
    
    // Mostrar información
    $('#editInfoTexto').text(`${gradoNombre} - ${dia} - Hora ${hora}`);
    
    // Cargar asignaturas
    let options = '<option value="">Seleccione una asignatura</option>';
    todasLasAsignaturas.forEach(asig => {
        options += `<option value="${asig.id}">${asig.nombre}</option>`;
    });
    $('#editAsignatura').html(options);
    
    // Limpiar campos
    $('#editProfesor').prop('disabled', true).html('<option value="">Primero seleccione una asignatura</option>');
    $('#editWarning').addClass('d-none');
    
    // ✨ Cargar datos actuales solo si es edición
    if (!esNueva) {
        const celda = $(`.clase-cell[data-grado="${gradoId}"][data-dia="${dia}"][data-hora="${hora}"]`);
        if (celda.length) {
            const asignaturaId = celda.data('asignatura-id');
            const profesorId = celda.data('profesor-id');
            
            $('#editAsignatura').val(asignaturaId).trigger('change');
            setTimeout(() => {
                $('#editProfesor').val(profesorId);
            }, 500);
        }
    }
    
    $('#modalEditarClase').modal('show');
}

// ========================================
// CARGAR PROFESORES POR ASIGNATURA
// ========================================
function cargarProfesoresPorAsignatura() {
    const asignaturaId = $('#editAsignatura').val();
    
    if (!asignaturaId) {
        $('#editProfesor').prop('disabled', true).html('<option value="">Primero seleccione una asignatura</option>');
        return;
    }
    
    $.ajax({
        url: `/horarios/ajax/profesores-por-asignatura/${asignaturaId}`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                let options = '<option value="">Seleccione un profesor</option>';
                response.profesores.forEach(prof => {
                    options += `<option value="${prof.id}">${prof.name}</option>`;
                });
                $('#editProfesor').prop('disabled', false).html(options);
            }
        }
    });
}

// ========================================
// ⭐ GUARDAR EDICIÓN
// ========================================
function guardarEdicion() {
    const gradoId = $('#editGradoId').val();
    const nivelId = $('#editNivelId').val();
    const year = $('#editYear').val();
    const dia = $('#editDia').val();
    const hora = $('#editHora').val();
    const asignaturaId = $('#editAsignatura').val();
    const profesorId = $('#editProfesor').val();
    
    if (!asignaturaId || !profesorId) {
        Swal.fire('Error', 'Complete todos los campos', 'warning');
        return;
    }
    
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
            if (!response.success) {
                $('#editWarning').removeClass('d-none');
                $('#editWarningText').html(response.message);
                return;
            }
            
            // No hay conflictos, guardar
            guardarClaseEditada(gradoId, nivelId, year, dia, hora, asignaturaId, profesorId);
        }
    });
}

// ========================================
// GUARDAR CLASE EDITADA
// ========================================
function guardarClaseEditada(gradoId, nivelId, year, dia, hora, asignaturaId, profesorId) {
    // Recargar todo el horario del grado para obtener todas las clases
    $.ajax({
        url: '/horarios/obtener',
        method: 'GET',
        data: {
            nivel_id: nivelId,
            grado_id: gradoId,
            year: year
        },
        success: function(response) {
            if (response.success) {
                const config = response.config;
                let clases = [];
                
                // Obtener todas las clases existentes
                response.horarios.forEach(h => {
                    clases.push({
                        dia: h.dia_semana,
                        hora_numero: h.hora_numero,
                        asignatura_id: h.asignatura_id,
                        profesor_id: h.profesor_id
                    });
                });
                
                // Actualizar o agregar la clase editada
                const index = clases.findIndex(c => c.dia === dia && c.hora_numero == hora);
                const nuevaClase = {
                    dia: dia,
                    hora_numero: parseInt(hora),
                    asignatura_id: asignaturaId,
                    profesor_id: profesorId
                };
                
                if (index >= 0) {
                    clases[index] = nuevaClase;
                } else {
                    clases.push(nuevaClase);
                }
                
                // Guardar todo el horario actualizado
                $.ajax({
                    url: '/horarios/guardar',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        nivel_id: nivelId,
                        grado_id: gradoId,
                        year: year,
                        hora_inicio: config.hora_inicio,
                        hora_fin: config.hora_fin,
                        duracion_clase: config.duracion_clase,
                        horas_por_dia: config.horas_por_dia,
                        dias_semana: config.dias_semana,
                        recreo_despues_hora: config.recreo_despues_hora,
                        recreo_duracion: config.recreo_duracion,
                        clases: clases
                    }),
                    success: function(response) {
                        if (response.success) {
                            $('#modalEditarClase').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: esNuevaClase ? 'Clase asignada correctamente' : 'Clase actualizada correctamente',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            
                            // ✨ ACTUALIZACIÓN EN TIEMPO REAL
                            actualizarCeldaEnTiempoReal(gradoId, dia, hora, asignaturaId, profesorId);
                            
                            // Actualizar estadísticas
                            cargarEstadisticas(nivelActual, yearActual);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Error al guardar', 'error');
                    }
                });
            }
        }
    });
}

// ========================================
// ✨ NUEVO: ACTUALIZAR CELDA EN TIEMPO REAL
// ========================================
function actualizarCeldaEnTiempoReal(gradoId, dia, hora, asignaturaId, profesorId) {
    // Buscar la asignatura
    const asignatura = todasLasAsignaturas.find(a => a.id == asignaturaId);
    const asignaturaNombre = asignatura ? asignatura.nombre : 'Asignatura';
    
    // Obtener el nombre del profesor
    $.ajax({
        url: `/horarios/ajax/profesor/${profesorId}`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const profesorNombre = response.profesor.name;
                
                // ✅ BUSCAR LA CELDA de forma más específica
                // Buscar primero dentro del grado específico
                const horarioGrado = $(`#horario-${gradoId}`);
                
                // Buscar la celda por sus atributos data (más confiable)
                let celda = horarioGrado.find(`td[data-dia="${dia}"][data-hora="${hora}"]`);
                
                // Si no encuentra, buscar de forma global como fallback
                if (!celda.length) {
                    celda = $(`td[data-grado="${gradoId}"][data-dia="${dia}"][data-hora="${hora}"]`);
                }
                
                console.log('🔍 Buscando celda:', {
                    gradoId, dia, hora,
                    encontrada: celda.length > 0,
                    clases: celda.attr('class')
                });
                
                if (celda.length) {
                    // Crear nuevo contenido
                    const nuevoHTML = `
                        <div class="clase-content">
                            <div class="asignatura-nombre text-truncate">
                                <i class="bi bi-book"></i> ${asignaturaNombre}
                            </div>
                            <div class="profesor-nombre text-truncate small text-muted">
                                <i class="bi bi-person"></i> ${profesorNombre}
                            </div>
                        </div>
                    `;
                    
                    // Actualizar clases CSS
                    celda.removeClass('empty-cell text-center text-muted')
                         .addClass('clase-cell clase-editable');
                    
                    // Actualizar atributos
                    celda.attr('data-asignatura-id', asignaturaId);
                    celda.attr('data-profesor-id', profesorId);
                    celda.attr('title', 'Clic para editar');
                    
                    // Actualizar contenido con animación
                    celda.addClass('celda-actualizando');
                    celda.html(nuevoHTML);
                    
                    setTimeout(() => {
                        celda.removeClass('celda-actualizando');
                    }, 600);
                    
                    // Re-asignar event listeners
                    celda.off('click').on('click', function() {
                        const gId = $(this).data('grado');
                        const nId = $(this).data('nivel');
                        const d = $(this).data('dia');
                        const h = $(this).data('hora');
                        const gNombre = $(this).data('grado-nombre');
                        
                        abrirModalEdicion(gId, nId, d, h, gNombre, false);
                    });
                    
                    console.log('✅ Celda actualizada correctamente');
                } else {
                    console.error('❌ No se encontró la celda para actualizar');
                }
            } else {
                console.error('❌ Error al obtener profesor:', response);
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ Error en AJAX profesor:', error);
            console.error('Respuesta completa:', xhr.responseText);
        }
    });
}

// ========================================
// ⭐ ELIMINAR CLASE
// ========================================
function eliminarClase() {
    const gradoId = $('#editGradoId').val();
    const nivelId = $('#editNivelId').val();
    const year = $('#editYear').val();
    const dia = $('#editDia').val();
    const hora = $('#editHora').val();
    
    Swal.fire({
        title: '¿Eliminar esta clase?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            eliminarClaseConfirmado(gradoId, nivelId, year, dia, hora);
        }
    });
}

function eliminarClaseConfirmado(gradoId, nivelId, year, dia, hora) {
    // Recargar horario completo
    $.ajax({
        url: '/horarios/obtener',
        method: 'GET',
        data: { nivel_id: nivelId, grado_id: gradoId, year: year },
        success: function(response) {
            if (response.success) {
                const config = response.config;
                
                // Filtrar la clase a eliminar
                let clases = response.horarios
                    .filter(h => !(h.dia_semana === dia && h.hora_numero == hora))
                    .map(h => ({
                        dia: h.dia_semana,
                        hora_numero: h.hora_numero,
                        asignatura_id: h.asignatura_id,
                        profesor_id: h.profesor_id
                    }));
                
                // Guardar horario sin la clase eliminada
                $.ajax({
                    url: '/horarios/guardar',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        nivel_id: nivelId,
                        grado_id: gradoId,
                        year: year,
                        hora_inicio: config.hora_inicio,
                        hora_fin: config.hora_fin,
                        duracion_clase: config.duracion_clase,
                        horas_por_dia: config.horas_por_dia,
                        dias_semana: config.dias_semana,
                        recreo_despues_hora: config.recreo_despues_hora,
                        recreo_duracion: config.recreo_duracion,
                        clases: clases
                    }),
                    success: function(response) {
                        if (response.success) {
                            $('#modalEditarClase').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: '¡Eliminada!',
                                text: 'Clase eliminada correctamente',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            
                            // ✨ ACTUALIZACIÓN EN TIEMPO REAL: Convertir a celda vacía
                            const selector = `td[data-grado="${gradoId}"][data-dia="${dia}"][data-hora="${hora}"]`;
                            const celda = $(selector);
                            
                            if (celda.length) {
                                const gradoNombre = celda.data('grado-nombre');
                                
                                // Actualizar clases CSS
                                celda.removeClass('clase-cell clase-editable')
                                     .addClass('empty-cell');
                                
                                // Eliminar atributos de clase
                                celda.removeAttr('data-asignatura-id');
                                celda.removeAttr('data-profesor-id');
                                celda.attr('title', 'Clic para asignar clase');
                                
                                // Actualizar contenido con animación
                                celda.addClass('celda-actualizando');
                                celda.html('<small><i class="bi bi-plus-circle"></i> Asignar</small>');
                                
                                setTimeout(() => {
                                    celda.removeClass('celda-actualizando');
                                }, 600);
                                
                                // Re-asignar event listeners
                                celda.off('click').on('click', function() {
                                    const gId = $(this).data('grado');
                                    const nId = $(this).data('nivel');
                                    const d = $(this).data('dia');
                                    const h = $(this).data('hora');
                                    const gNombre = $(this).data('grado-nombre');
                                    
                                    abrirModalEdicion(gId, nId, d, h, gNombre, true);
                                });
                            }
                            
                            // Actualizar estadísticas
                            cargarEstadisticas(nivelActual, yearActual);
                        }
                    }
                });
            }
        }
    });
}

// ========================================
// TOGGLE HORARIO
// ========================================
window.toggleHorario = function(id) {
    $(`#${id} .horario-body`).slideToggle(300);
};

// ========================================
// IMPRIMIR HORARIO
// ========================================
function imprimirHorario(gradoId) {
    const contenido = document.getElementById(`horario-${gradoId}`).innerHTML;
    const ventana = window.open('', '', 'height=800,width=1000');
    
    ventana.document.write(`
        <html>
        <head>
            <title>Horario de Clases</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { padding: 20px; }
                .horario-table { width: 100%; }
                .clase-cell { padding: 10px; }
                @media print {
                    .btn { display: none; }
                }
            </style>
        </head>
        <body>
            ${contenido}
        </body>
        </html>
    `);
    
    ventana.document.close();
    setTimeout(() => {
        ventana.print();
    }, 250);
}

// Configuración AJAX global
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});