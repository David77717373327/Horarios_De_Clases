// ========================================
// GENERADOR AUTOMÁTICO DE HORARIOS v8.0
// JavaScript Completo - Refactorizado
// ========================================

// Estado Global de la Aplicación
const appState = {
    nivel: null,
    year: null,
    config: {
        horaInicio: '07:00',
        horaFin: '13:00',
        duracionClase: 45,
        horasPorDia: 6,
        diasSemana: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'],
        recreoDespuesHora: 3,
        recreoDuracion: 15
    },
    horariosNivel: {},
    estadisticasGeneracion: null,
    ultimoDiagnostico: null,
    nivelCompletoGenerado: false,
    gradosDelNivel: []
};

// ========================================
// INICIALIZACIÓN
// ========================================
$(document).ready(function() {
    initializeEventListeners();
    console.log('✅ Generador de Horarios v8.0 inicializado');
});

// ========================================
// EVENT LISTENERS
// ========================================
function initializeEventListeners() {
    $('#nivel').on('change', handleNivelChange);
    $('#btnContinue').on('click', handleContinue);
    $('#btnGenerarAutomatico').on('click', abrirModalGeneracionAutomatica);
    $('#btnConfirmarGeneracion').on('click', ejecutarGeneracionAutomatica);
    $('#btnBack1').on('click', () => toggleSteps('stepConfig'));
    $('#btnBack2').on('click', () => toggleSteps('stepSchedule'));
    $('#btnRegenerar').on('click', handleRegenerar);
    $('#btnVerDiagnostico').on('click', mostrarModalDiagnostico);
    $('#btnCerrarDiagnostico').on('click', () => $('#modalDiagnostico').modal('hide'));
}

// ========================================
// MANEJO DE EVENTOS PRINCIPALES
// ========================================

// Manejar cambio de nivel
function handleNivelChange() {
    const nivelId = $(this).val();
    appState.nivel = nivelId;
    
    appState.nivelCompletoGenerado = false;
    appState.gradosDelNivel = [];
    
    console.log('=== CAMBIO DE NIVEL ===');
    console.log('Nivel ID:', nivelId);
    
    if (!nivelId) {
        ocultarInfoAsignaciones();
        return;
    }
    
    // Mostrar mensaje informativo básico
    mostrarMensajeNivelSeleccionado();
}

// Continuar a Paso 2
function handleContinue() {
    const nivel = $('#nivel').val();
    const year = $('#year').val();
    
    if (!nivel || !year) {
        showNotification('Por favor complete todos los campos', 'warning');
        return;
    }
    
    appState.nivel = nivel;
    appState.year = year;
    
    actualizarSelectedInfo();
    toggleSteps('stepSchedule');
}

// Mostrar mensaje cuando se selecciona un nivel
function mostrarMensajeNivelSeleccionado() {
    const mensaje = `
        <strong>ℹ️ Nivel Seleccionado</strong>
        <br>El sistema generará automáticamente <strong>todos los grados de este nivel</strong> de forma coordinada.
        <br>Esto optimiza la distribución y evita conflictos de profesores entre grados.
    `;
    
    $('#infoAsignaciones')
        .removeClass('d-none alert-info alert-warning alert-danger')
        .addClass('alert-info');
    $('#textoAsignaciones').html(mensaje);
    $('#btnGenerarAutomatico').prop('disabled', false);
}

// Ocultar información de asignaciones
function ocultarInfoAsignaciones() {
    $('#infoAsignaciones').addClass('d-none');
    $('#btnGenerarAutomatico').prop('disabled', false);
}

// ========================================
// GENERACIÓN AUTOMÁTICA
// ========================================

// Abrir modal de generación automática
function abrirModalGeneracionAutomatica() {
    // Capturar configuración actual
    appState.config.horaInicio = $('#horaInicio').val();
    appState.config.horaFin = $('#horaFin').val();
    appState.config.duracionClase = parseInt($('#duracionClase').val());
    appState.config.horasPorDia = parseInt($('#horasPorDia').val());
    appState.config.recreoDespuesHora = $('#recreoDespuesHora').val() ? parseInt($('#recreoDespuesHora').val()) : null;
    appState.config.recreoDuracion = parseInt($('#recreoDuracion').val());
    
    appState.config.diasSemana = [];
    $('.dia-check:checked').each(function() {
        appState.config.diasSemana.push($(this).val());
    });
    
    if (appState.config.diasSemana.length === 0) {
        showNotification('Seleccione al menos un día de clase', 'warning');
        return;
    }
    
    $('#modoReemplazar').prop('checked', true);
    $('#modalGenerarAuto').modal('show');
}

// Ejecutar generación automática
function ejecutarGeneracionAutomatica() {
    const modoReemplazar = $('#modoReemplazar').is(':checked');
    
    $('#modalGenerarAuto').modal('hide');
    
    $('#modalProgreso').modal('show');
    actualizarProgresoGeneracion('🎯 Iniciando generación inteligente del nivel completo...', 0);
    
    const configuracion = {
        year: appState.year,
        limpiar_existentes: modoReemplazar,
        hora_inicio: appState.config.horaInicio,
        hora_fin: appState.config.horaFin,
        duracion_clase: appState.config.duracionClase,
        horas_por_dia: appState.config.horasPorDia,
        dias_semana: appState.config.diasSemana,
        recreo_despues_hora: appState.config.recreoDespuesHora,
        recreo_duracion: appState.config.recreoDuracion
    };
    
    console.log('📤 Configuración enviada al servidor:', configuracion);
    
    // Simulación de progreso
    let progreso = 0;
    const mensajesProgreso = [
        '📚 Analizando todos los grados del nivel...',
        '👥 Verificando disponibilidad de profesores...',
        '🔍 Detectando profesores compartidos entre grados...',
        '⚖️ Optimizando distribución global del nivel...',
        '🎯 Generando horarios de forma coordinada...',
        '✅ Validando compatibilidad entre grados...'
    ];
    let mensajeIndex = 0;
    
    const intervalProgreso = setInterval(() => {
        if (progreso < 90) {
            progreso += Math.random() * 12;
            if (progreso > 90) progreso = 90;
            
            if (progreso > 15 && mensajeIndex < 1) mensajeIndex = 1;
            if (progreso > 30 && mensajeIndex < 2) mensajeIndex = 2;
            if (progreso > 45 && mensajeIndex < 3) mensajeIndex = 3;
            if (progreso > 60 && mensajeIndex < 4) mensajeIndex = 4;
            if (progreso > 75 && mensajeIndex < 5) mensajeIndex = 5;
            
            actualizarProgresoGeneracion(mensajesProgreso[mensajeIndex], progreso);
        }
    }, 600);
    
    // 🔥 LLAMADA AL BACKEND - RUTA ACTUALIZADA
    $.ajax({
        url: `/generador/nivel/${appState.nivel}/generar`,
        method: 'POST',
        data: JSON.stringify(configuracion),
        contentType: 'application/json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            clearInterval(intervalProgreso);
            console.log('📥 Respuesta del servidor:', response);
            
            if (response.cache_hit) {
                actualizarProgresoGeneracion('⚡ Horarios recuperados instantáneamente (pre-generados)', 100);
            } else if (response.nivel_completo) {
                actualizarProgresoGeneracion(`✅ Nivel completo generado (${response.grados_generados} grados)`, 100);
            } else {
                actualizarProgresoGeneracion('✅ Horarios generados con éxito', 100);
            }
            
            setTimeout(() => {
                $('#modalProgreso').modal('hide');
                
                if (response.success) {
                    procesarHorarioExitoso(response);
                } else {
                    console.error('❌ Respuesta sin éxito del servidor:', response);
                    showNotification(response.message || 'Error al generar horario', 'error');
                }
            }, 1500);
        },
        error: function(xhr) {
            clearInterval(intervalProgreso);
            $('#modalProgreso').modal('hide');
            
            console.error('❌ Error AJAX completo:', xhr);
            procesarErrorGeneracion(xhr);
        }
    });
}

// ========================================
// PROCESAMIENTO DE RESPUESTAS
// ========================================

// Procesar horario exitoso
function procesarHorarioExitoso(response) {
    appState.horariosNivel = {};
    
    if (response.nivel_completo) {
        appState.nivelCompletoGenerado = true;
        appState.gradosDelNivel = response.grados_del_nivel || [];
    }
    
    // Cargar horarios de todos los grados
    if (response.horarios_nivel) {
        console.log(`✅ Horarios del nivel recibidos:`, response.horarios_nivel);
        
        Object.keys(response.horarios_nivel).forEach(gradoId => {
            const gradoData = response.horarios_nivel[gradoId];
            
            appState.horariosNivel[gradoId] = {
                grado: gradoData.grado,
                horarios: {}
            };
            
            if (gradoData.horarios && Array.isArray(gradoData.horarios)) {
                gradoData.horarios.forEach((h, index) => {
                    const key = `${h.dia_semana}_${h.hora_numero}`;
                    
                    if (!h.asignatura || !h.profesor) {
                        console.error(`❌ Horario ${index + 1} sin datos completos:`, h);
                        return;
                    }
                    
                    if (!h.asignatura.nombre || !h.profesor.name) {
                        console.error(`❌ Horario ${index + 1} con datos incompletos:`, h);
                        return;
                    }
                    
                    appState.horariosNivel[gradoId].horarios[key] = {
                        asignatura_id: h.asignatura_id,
                        profesor_id: h.profesor_id,
                        asignatura_nombre: h.asignatura.nombre,
                        profesor_nombre: h.profesor.name
                    };
                });
            }
        });
        
        console.log('📊 Estado completo de appState.horariosNivel:', appState.horariosNivel);
    }
    
    appState.estadisticasGeneracion = response.estadisticas_nivel;
    appState.ultimoDiagnostico = response.diagnostico || null;
    
    // Generar tablas de horarios para todos los grados
    generarTablasHorarioNivel();
    
    // Mostrar estadísticas
    if (response.nivel_completo) {
        mostrarEstadisticasGeneracionNivel(response.estadisticas_nivel, response.grados_generados);
    }
    
    actualizarSelectedInfo();
    toggleSteps('stepGrid');
    
    let mensajeExito = '✅ ¡Horarios generados automáticamente con éxito!';
    if (response.cache_hit) {
        mensajeExito = '⚡ ¡Horarios recuperados instantáneamente!';
    } else if (response.nivel_completo) {
        mensajeExito = `✅ ¡Nivel completo generado! (${response.grados_generados} grados optimizados)`;
    }
    
    showNotification(mensajeExito, 'success');
}

// Procesar error de generación
function procesarErrorGeneracion(xhr) {
    console.error('📊 Procesando error de generación...');
    console.error('Status:', xhr.status);
    console.error('Response Text:', xhr.responseText);
    
    let errorData = null;
    
    try {
        errorData = xhr.responseJSON;
    } catch (e) {
        console.error('No se pudo parsear JSON:', e);
    }
    
    if (errorData && errorData.diagnostico) {
        appState.ultimoDiagnostico = errorData.diagnostico;
    }
    
    if (errorData && (errorData.diagnostico || errorData.materias_faltantes || errorData.sugerencias)) {
        setTimeout(() => {
            mostrarModalDiagnosticoCompleto(errorData);
        }, 500);
    } else {
        const errorMsg = errorData?.message || 'Error al generar horario automáticamente';
        showNotification(errorMsg, 'error');
    }
}

// ========================================
// VISUALIZACIÓN DE ESTADÍSTICAS
// ========================================

// Mostrar estadísticas con información de nivel
function mostrarEstadisticasGeneracionNivel(statsNivel, gradosGenerados) {
    if (!statsNivel) {
        $('#statsAutomaticas').addClass('d-none');
        return;
    }
    
    const html = `
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-success mb-0">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>Nivel Completo Generado</strong>
                    <br>Se generaron exitosamente ${gradosGenerados} grado(s) de forma coordinada
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <small class="text-muted">Total Grados</small>
                <br><strong>${statsNivel.total_grados || 0}</strong>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Grados Completos</small>
                <br><strong class="text-success">${statsNivel.grados_completos || 0}</strong>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Horas Totales</small>
                <br><strong>${statsNivel.horas_asignadas || 0} / ${statsNivel.horas_requeridas || 0}</strong>
            </div>
            <div class="col-md-3">
                <small class="text-muted">Completitud Global</small>
                <br><strong class="text-primary">${statsNivel.porcentaje_global || 0}%</strong>
            </div>
        </div>
    `;
    
    $('#statsContent').html(html);
    $('#statsAutomaticas').removeClass('d-none');
}

// ========================================
// MODAL DE DIAGNÓSTICO
// ========================================

// Mostrar modal de diagnóstico completo
function mostrarModalDiagnosticoCompleto(errorData) {
    const modal = $('#modalDiagnostico');
    
    $('#diagnosticoTitulo').html(`
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        No se pudo generar el horario completo del nivel
    `);
    
    let contenido = '';
    
    if (errorData.message) {
        contenido += `
            <div class="alert alert-warning mb-4">
                <i class="bi bi-info-circle me-2"></i>
                <strong>${errorData.message}</strong>
            </div>
        `;
    }
    
    if (errorData.estadisticas) {
        const stats = errorData.estadisticas;
        contenido += `
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Estadísticas de Generación</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <small class="text-muted d-block">Horas Asignadas</small>
                            <h4 class="mb-0 text-primary">${stats.horas_asignadas || 0} / ${stats.horas_requeridas || 0}</h4>
                        </div>
                        <div class="col-6 mb-3">
                            <small class="text-muted d-block">Completitud</small>
                            <h4 class="mb-0 ${stats.porcentaje_global >= 80 ? 'text-success' : 'text-warning'}">
                                ${stats.porcentaje_global || 0}%
                            </h4>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Grados Completos</small>
                            <h5 class="mb-0 text-success">${stats.grados_completos || 0}</h5>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Grados Incompletos</small>
                            <h5 class="mb-0 text-danger">${stats.grados_incompletos || 0}</h5>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    if (errorData.materias_faltantes && errorData.materias_faltantes.length > 0) {
        contenido += `
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0"><i class="bi bi-exclamation-circle me-2"></i>Materias Sin Completar (${errorData.materias_faltantes.length})</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Asignatura</th>
                                    <th>Profesor</th>
                                    <th class="text-center">Requeridas</th>
                                    <th class="text-center">Asignadas</th>
                                    <th class="text-center">Faltantes</th>
                                </tr>
                            </thead>
                            <tbody>
        `;
        
        errorData.materias_faltantes.forEach(materia => {
            contenido += `
                <tr>
                    <td><strong>${materia.asignatura}</strong></td>
                    <td>${materia.profesor}</td>
                    <td class="text-center">${materia.horas_requeridas}</td>
                    <td class="text-center">${materia.horas_asignadas}</td>
                    <td class="text-center"><span class="badge bg-danger">${materia.horas_faltantes}</span></td>
                </tr>
            `;
        });
        
        contenido += `
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
    }
    
    if (errorData.diagnostico) {
        const diag = errorData.diagnostico;
        
        if (diag.problema_principal) {
            contenido += `
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bi bi-clipboard-data me-2"></i>Diagnóstico del Problema</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-3"><strong>${diag.problema_principal}</strong></p>
                        
                        ${diag.causas && diag.causas.length > 0 ? `
                            <h6 class="mt-3 mb-2">Causas identificadas:</h6>
                            <ul class="mb-0">
                                ${diag.causas.map(causa => `<li>${causa}</li>`).join('')}
                            </ul>
                        ` : ''}
                    </div>
                </div>
            `;
        }
    }
    
    if (errorData.sugerencias && errorData.sugerencias.length > 0) {
        contenido += `
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Sugerencias para Solucionar</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
        `;
        
        errorData.sugerencias.forEach(sugerencia => {
            contenido += `<li class="mb-2">${sugerencia}</li>`;
        });
        
        contenido += `
                    </ul>
                </div>
            </div>
        `;
    }
    
    $('#diagnosticoContenido').html(contenido);
    modal.modal('show');
}

// Mostrar modal de diagnóstico
function mostrarModalDiagnostico() {
    if (!appState.ultimoDiagnostico && !appState.estadisticasGeneracion) {
        showNotification('No hay diagnóstico disponible', 'info');
        return;
    }
    
    const errorData = {
        message: 'Análisis del último intento de generación',
        estadisticas: appState.estadisticasGeneracion,
        diagnostico: appState.ultimoDiagnostico
    };
    
    mostrarModalDiagnosticoCompleto(errorData);
}

// Actualizar progreso de generación
function actualizarProgresoGeneracion(mensaje, porcentaje) {
    const porcentajeRedondeado = Math.round(porcentaje);
    
    let icono = 'bi-hourglass-split';
    if (porcentaje > 30) icono = 'bi-cpu';
    if (porcentaje > 60) icono = 'bi-gear-wide-connected';
    if (porcentaje > 90) icono = 'bi-check-circle';
    
    $('#progresoTexto').html(`
        <i class="${icono} me-2"></i>
        ${mensaje}
    `);
    
    if ($('#progresoBar').length) {
        $('#progresoBar')
            .css('width', `${porcentajeRedondeado}%`)
            .attr('aria-valuenow', porcentajeRedondeado)
            .html(`<strong>${porcentajeRedondeado}%</strong>`);
    }
}

// ========================================
// GENERACIÓN DE TABLAS DE HORARIO
// ========================================

// Generar Tablas de Horario para todos los grados del nivel
function generarTablasHorarioNivel() {
    let htmlCompleto = '';
    
    // Iterar sobre cada grado del nivel
    Object.keys(appState.horariosNivel).forEach((gradoId, index) => {
        const gradoData = appState.horariosNivel[gradoId];
        const nombreGrado = gradoData.grado.nombre;
        const horariosGrado = gradoData.horarios;
        
        // Card para cada grado
        htmlCompleto += `
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar3 me-2"></i>
                        Horario: ${nombreGrado}
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        ${generarTablaHorarioGrado(horariosGrado)}
                    </div>
                </div>
            </div>
        `;
    });
    
    if (htmlCompleto === '') {
        htmlCompleto = `
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                No se pudieron cargar los horarios. Intente regenerar.
            </div>
        `;
    }
    
    $('#horarioTableContainer').html(htmlCompleto);
}

// Generar tabla de horario para un grado específico
function generarTablaHorarioGrado(horariosGrado) {
    let html = '<table class="horario-table"><thead><tr class="table-primary"><th class="text-center">Hora</th>';
    
    appState.config.diasSemana.forEach(dia => {
        html += `<th class="text-center">${dia}</th>`;
    });
    html += '</tr></thead><tbody>';
    
    const horasPorDia = appState.config.horasPorDia;
    const recreoDespues = appState.config.recreoDespuesHora;
    
    let horaActual = new Date(`2000-01-01T${appState.config.horaInicio}`);
    
    for (let i = 1; i <= horasPorDia; i++) {
        const horaInicio = formatTime(horaActual);
        horaActual = new Date(horaActual.getTime() + appState.config.duracionClase * 60000);
        const horaFin = formatTime(horaActual);
        
        html += `<tr><td class="text-center fw-bold bg-light">${i}ª<br><small class="text-muted">${horaInicio} - ${horaFin}</small></td>`;
        
        appState.config.diasSemana.forEach(dia => {
            const key = `${dia}_${i}`;
            const horario = horariosGrado[key];
            
            if (horario) {
                html += `<td class="schedule-cell filled">
                    <div class="schedule-content">
                        <div class="fw-bold text-primary">${horario.asignatura_nombre}</div>
                        <small class="text-muted">${horario.profesor_nombre}</small>
                    </div>
                </td>`;
            } else {
                html += `<td class="schedule-cell empty">
                    <div class="schedule-placeholder">
                        <small class="text-muted">Sin asignar</small>
                    </div>
                </td>`;
            }
        });
        html += '</tr>';
        
        // Insertar Recreo
        if (recreoDespues && i === recreoDespues) {
            const recreoInicio = formatTime(horaActual);
            horaActual = new Date(horaActual.getTime() + appState.config.recreoDuracion * 60000);
            const recreoFin = formatTime(horaActual);
            
            html += `<tr class="table-warning"><td class="text-center fw-bold">
                <i class="bi bi-cup-hot me-1"></i>RECREO<br>
                <small class="text-muted">${recreoInicio} - ${recreoFin}</small>
            </td>`;
            
            appState.config.diasSemana.forEach(() => {
                html += '<td class="text-center"><i class="bi bi-cup-hot-fill text-warning fs-3"></i></td>';
            });
            html += '</tr>';
        }
    }
    
    html += '</tbody></table>';
    return html;
}

// ========================================
// OTRAS FUNCIONES
// ========================================

// Regenerar Horario
function handleRegenerar() {
    if (!confirm('¿Está seguro de regenerar el horario? Se eliminará el horario actual del nivel completo y se generará uno nuevo.')) {
        return;
    }
    
    appState.horariosNivel = {};
    appState.estadisticasGeneracion = null;
    appState.ultimoDiagnostico = null;
    appState.nivelCompletoGenerado = false;
    appState.gradosDelNivel = [];
    
    toggleSteps('stepSchedule');
    showNotification('Puede configurar nuevamente y regenerar el horario del nivel', 'info');
}

// ========================================
// CONFIGURACIÓN AJAX
// ========================================

// Configuración global de AJAX para CSRF
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute('content')
    }
});

// ========================================
// UTILIDADES
// ========================================

function toggleSteps(showStep) {
    $('#stepConfig, #stepSchedule, #stepGrid').addClass('d-none');
    $(`#${showStep}`).removeClass('d-none');
    window.scrollTo(0, 0);
}

function actualizarSelectedInfo() {
    const nivelText = $('#nivel option:selected').text();
    $('#selectedInfo').text(`${nivelText} - ${appState.year}`);
}

function formatTime(date) {
    return date.toTimeString().substring(0, 5);
}

function showNotification(message, type) {
    const bgColor = {
        success: 'bg-success',
        error: 'bg-danger',
        warning: 'bg-warning',
        info: 'bg-info'
    }[type] || 'bg-primary';
    
    const toast = $(`
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
            <div class="toast show ${bgColor} text-white" role="alert">
                <div class="toast-body">${message}</div>
            </div>
        </div>
    `);
    
    $('body').append(toast);
    setTimeout(() => toast.remove(), 5000);
}

// ========================================
// LOG DE VERSIÓN
// ========================================
console.log(`
╔════════════════════════════════════════╗
║  GENERADOR DE HORARIOS v8.0           ║
║  Sistema Refactorizado                 ║
║  ✅ Servicio: AutoSchedulerService    ║
║  ✅ Ruta: /generador/nivel/{id}/gener ║
╚════════════════════════════════════════╝
`);