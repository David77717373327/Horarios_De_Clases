// ========================================
// VARIABLES GLOBALES
// ========================================
let profesorActual = null;
let yearActual = null;

$(document).ready(function() {
    // ========================================
    // EVENT LISTENERS
    // ========================================
    $('#btnBuscar').on('click', buscarHorario);
    $('#btnPdf').on('click', descargarPDF);
    
    // Configuración AJAX global
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
});

// ========================================
// BUSCAR HORARIO
// ========================================
function buscarHorario() {
    const profesorId = $('#filterProfesor').val();
    const year = $('#filterYear').val();

    if (!profesorId) {
        Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: 'Por favor selecciona un profesor',
            confirmButtonColor: '#667eea'
        });
        return;
    }
    
    profesorActual = profesorId;
    yearActual = year;

    cargarHorario(profesorId, year);
}

// ========================================
// CARGAR HORARIO
// ========================================
function cargarHorario(profesorId, year) {
    $('#horarioContainer').html(`
        <div class="card-final-prof shadow-sm">
            <div class="card-body text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-3 text-muted fw-semibold">Cargando horario del profesor...</p>
            </div>
        </div>
    `);

    // Deshabilitar botón PDF mientras carga
    $('#btnPdf').prop('disabled', true);

    $.ajax({
        url: '/horarios-profesor/obtener',
        method: 'GET',
        data: { 
            profesor_id: profesorId, 
            year: year 
        },
        success: function(response) {
            console.log('✅ Respuesta recibida:', response);
            
            if (response.success) {
                if (response.message) {
                    mostrarSinHorarios(response.profesor, response.year);
                    $('#btnPdf').prop('disabled', true);
                } else {
                    renderizarHorario(response);
                    $('#btnPdf').prop('disabled', false);
                }
            }
        },
        error: function(xhr) {
            console.error('❌ Error completo:', xhr);
            
            const errorMessage = xhr.responseJSON?.message || 'No se pudo cargar el horario';
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: `<p>${errorMessage}</p>`,
                confirmButtonColor: '#667eea'
            });
            
            $('#horarioContainer').html(`
                <div class="card-final-prof shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 4rem;"></i>
                        <h4 class="mt-3 text-danger">Error al cargar el horario</h4>
                        <p class="text-muted">${errorMessage}</p>
                        <button class="btn-final-prof btn-azul-prof mt-3" onclick="buscarHorario()">
                            <i class="bi bi-arrow-clockwise me-2"></i>Reintentar
                        </button>
                    </div>
                </div>
            `);
            
            $('#btnPdf').prop('disabled', true);
        }
    });
}

// ========================================
// MOSTRAR SIN HORARIOS
// ========================================
function mostrarSinHorarios(profesor, year) {
    $('#horarioContainer').html(`
        <div class="card-final-prof shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-calendar-x text-warning" style="font-size: 5rem;"></i>
                <h4 class="mt-4">No hay horarios registrados</h4>
                <p class="text-muted">
                    No se encontraron horarios para <strong>${profesor}</strong> en el año <strong>${year}</strong>
                </p>
                <a href="/asignaciones" class="btn-final-prof btn-azul-prof mt-3">
                    <i class="bi bi-plus-circle me-2"></i>Gestionar Asignaciones
                </a>
            </div>
        </div>
    `);
}

// ========================================
// RENDERIZAR HORARIO
// ========================================
function renderizarHorario(data) {
    console.log('🎨 Renderizando horario con data:', data);
    
    const config = data.config;
    let dias = config.dias_semana;
    
    if (typeof dias === 'string') {
        try {
            dias = JSON.parse(dias);
        } catch (e) {
            console.error('Error al parsear dias_semana:', e);
            dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
        }
    }
    
    if (!Array.isArray(dias)) {
        dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
    }
    
    const horasPorDia = config.horas_por_dia;
    const recreoHora = config.recreo_despues_hora;
    const recreoDuracion = config.recreo_duracion;
    
    let html = `
        <div class="horario-card-prof shadow mb-4">
            <div class="card-header bg-gradient-primary text-white">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h4 class="mb-1">
                            <i class="bi bi-person-badge-fill me-2"></i>
                            ${data.profesor}
                        </h4>
                        <p class="mb-0 opacity-75">
                            <i class="bi bi-calendar-check me-1"></i>
                            Año Lectivo ${data.year}
                        </p>
                    </div>
                    <div>
                        <button class="btn btn-danger btn-sm" onclick="descargarPDF()">
                            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Descargar PDF
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body-prof">
                <div class="table-responsive">
                    <table class="table table-bordered horario-table-prof mb-0">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Hora</th>
    `;

    dias.forEach(function(dia) {
        html += `<th>${dia}</th>`;
    });

    html += `</tr></thead><tbody>`;

    for (let hora = 1; hora <= horasPorDia; hora++) {
        if (recreoHora && hora == recreoHora + 1) {
            html += `
                <tr class="recreo-row-prof">
                    <td class="text-center">
                        <i class="bi bi-cup-hot-fill"></i>
                    </td>
                    <td colspan="${dias.length}" class="text-center">
                        <strong><i class="bi bi-clock me-2"></i>RECREO - ${recreoDuracion} minutos</strong>
                    </td>
                </tr>
            `;
        }

        html += `<tr><td class="hora-cell-prof">${hora}°</td>`;

        dias.forEach(function(dia) {
            const clases = data.horarios[dia] && data.horarios[dia][hora];
            
            if (clases && clases.length > 0) {
                let contenidoClases = '';
                
                clases.forEach((clase, index) => {
                    if (index > 0) {
                        contenidoClases += '<hr class="my-2">';
                    }
                    
                    contenidoClases += `
                        <div class="clase-item-prof">
                            <div class="asignatura-nombre-prof">
                                <i class="bi bi-book-fill me-1"></i>
                                ${clase.asignatura}
                            </div>
                            <div class="grado-nombre-prof">
                                <i class="bi bi-mortarboard-fill me-1"></i>
                                ${clase.nivel} - ${clase.grado}
                            </div>
                        </div>
                    `;
                });
                
                html += `<td class="clase-cell-prof">${contenidoClases}</td>`;
            } else {
                html += `
                    <td class="empty-cell-prof">
                        <i class="bi bi-dash-circle"></i>
                        <small class="d-block">Libre</small>
                    </td>
                `;
            }
        });

        html += `</tr>`;
    }

    html += `
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;

    $('#horarioContainer').html(html);
    console.log('✅ Horario renderizado exitosamente');
}

// ========================================
// DESCARGAR PDF - SIN VISTA EN BLANCO
// ========================================
function descargarPDF() {
    const profesorId = $('#filterProfesor').val();
    const year = $('#filterYear').val();

    if (!profesorId || !year) {
        Swal.fire({
            icon: 'warning',
            title: 'Filtros incompletos',
            text: 'Seleccione un profesor y año',
            confirmButtonColor: '#667eea'
        });
        return;
    }

    // Mostrar animación de carga primero
    Swal.fire({
        title: 'Generando PDF...',
        text: 'Por favor espera un momento',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Esperar un momento para que la animación se muestre antes de abrir el PDF
    setTimeout(() => {
        const url = `/horarios-profesor/pdf?profesor_id=${profesorId}&year=${year}`;
        
        // Crear iframe oculto para generar el PDF sin abrir pestaña en blanco
        const iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.src = url;
        document.body.appendChild(iframe);
        
        // Remover el iframe después de cargar
        setTimeout(() => {
            document.body.removeChild(iframe);
        }, 1000);
        
        // Cerrar animación
        setTimeout(() => {
            Swal.close();
        }, 1500);
    }, 300);
}