@extends('layouts.master')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Principal -->
    <div class="header-final mb-4">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h2 class="titulo-final">Crear Horario Académico por Nivel</h2>
                <p class="subtitulo-final">Configure y genere los horarios de todos los grados del nivel automáticamente</p>
            </div>
        </div>
    </div>

    <!-- Paso 1: Configuración Inicial -->
    <div class="card-final mb-4" id="stepConfig">
        <div class="card-header-final">
            <h6>
                <i class="bi bi-gear-fill me-2" style="color: #1e40af;"></i>
                Paso 1: Selección de Nivel
            </h6>
        </div>
        <div class="card-body-crear p-4">
            <div class="row g-4">
                <!-- Selección de Nivel -->
                <div class="col-md-6">
                    <label for="nivel" class="label-final">
                        <i class="bi bi-building me-2" style="color: #1e40af;"></i>
                        Nivel Educativo
                    </label>
                    <select class="select-final" id="nivel" required>
                        <option value="">Seleccione un nivel</option>
                        @foreach($niveles as $nivel)
                            <option value="{{ $nivel->id }}">{{ $nivel->nombre }}</option>
                        @endforeach
                    </select>
                    <small class="ayuda-texto">Se generarán automáticamente todos los grados de este nivel</small>
                </div>

                <!-- Año Lectivo -->
                <div class="col-md-6">
                    <label for="year" class="label-final">
                        <i class="bi bi-calendar-check me-2" style="color: #1e40af;"></i>
                        Año Lectivo
                    </label>
                    <select class="select-final" id="year" required>
                        @foreach($years as $yr)
                            <option value="{{ $yr }}" {{ $yr == date('Y') ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="button" class="btn-azul-final" id="btnContinue">
                    Continuar
                    <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Paso 2: Configuración de Horario -->
    <div class="card-final mb-4 d-none" id="stepSchedule">
        <div class="card-header-final">
            <h6>
                <i class="bi bi-clock-fill me-2" style="color: #059669;"></i>
                Paso 2: Configuración de Horario
            </h6>
        </div>
        <div class="card-body-crear p-4">
            <div class="row g-4">
                <!-- Hora de Inicio -->
                <div class="col-md-3">
                    <label for="horaInicio" class="label-final">
                        <i class="bi bi-sunrise me-2" style="color: #059669;"></i>
                        Hora de Inicio
                    </label>
                    <input type="time" class="select-final" id="horaInicio" value="07:00" required>
                </div>

                <!-- Hora de Fin -->
                <div class="col-md-3">
                    <label for="horaFin" class="label-final">
                        <i class="bi bi-sunset me-2" style="color: #059669;"></i>
                        Hora de Fin
                    </label>
                    <input type="time" class="select-final" id="horaFin" value="13:00" required>
                </div>

                <!-- Duración de Clase -->
                <div class="col-md-3">
                    <label for="duracionClase" class="label-final">
                        <i class="bi bi-hourglass-split me-2" style="color: #059669;"></i>
                        Duración por Clase
                    </label>
                    <select class="select-final" id="duracionClase">
                        <option value="30">30 minutos</option>
                        <option value="40">40 minutos</option>
                        <option value="45" selected>45 minutos</option>
                        <option value="50">50 minutos</option>
                        <option value="60">60 minutos</option>
                    </select>
                </div>

                <!-- Horas por Día -->
                <div class="col-md-3">
                    <label for="horasPorDia" class="label-final">
                        <i class="bi bi-list-ol me-2" style="color: #059669;"></i>
                        Horas por Día
                    </label>
                    <input type="number" class="select-final" id="horasPorDia" min="1" max="12" value="6" required>
                </div>

                <!-- Días de la Semana -->
                <div class="col-12">
                    <label class="label-final mb-3">
                        <i class="bi bi-calendar3 me-2" style="color: #059669;"></i>
                        Días de Clase
                    </label>
                    <div class="dias-container">
                        <div class="form-check-custom">
                            <input class="form-check-input dia-check" type="checkbox" value="Lunes" id="diaLunes" checked>
                            <label class="form-check-label-custom" for="diaLunes">Lunes</label>
                        </div>
                        <div class="form-check-custom">
                            <input class="form-check-input dia-check" type="checkbox" value="Martes" id="diaMartes" checked>
                            <label class="form-check-label-custom" for="diaMartes">Martes</label>
                        </div>
                        <div class="form-check-custom">
                            <input class="form-check-input dia-check" type="checkbox" value="Miércoles" id="diaMiercoles" checked>
                            <label class="form-check-label-custom" for="diaMiercoles">Miércoles</label>
                        </div>
                        <div class="form-check-custom">
                            <input class="form-check-input dia-check" type="checkbox" value="Jueves" id="diaJueves" checked>
                            <label class="form-check-label-custom" for="diaJueves">Jueves</label>
                        </div>
                        <div class="form-check-custom">
                            <input class="form-check-input dia-check" type="checkbox" value="Viernes" id="diaViernes" checked>
                            <label class="form-check-label-custom" for="diaViernes">Viernes</label>
                        </div>
                        <div class="form-check-custom">
                            <input class="form-check-input dia-check" type="checkbox" value="Sábado" id="diaSabado">
                            <label class="form-check-label-custom" for="diaSabado">Sábado</label>
                        </div>
                    </div>
                </div>

                <!-- Configuración de Recreo -->
                <div class="col-md-6">
                    <label for="recreoDespuesHora" class="label-final">
                        <i class="bi bi-cup-hot me-2" style="color: #f59e0b;"></i>
                        Recreo después de la hora
                    </label>
                    <select class="select-final" id="recreoDespuesHora">
                        <option value="">Sin recreo</option>
                        <option value="2">2</option>
                        <option value="3" selected>3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                    <small class="ayuda-texto">El recreo se insertará automáticamente en el horario</small>
                </div>

                <div class="col-md-6">
                    <label for="recreoDuracion" class="label-final">
                        <i class="bi bi-stopwatch me-2" style="color: #f59e0b;"></i>
                        Duración del Recreo
                    </label>
                    <select class="select-final" id="recreoDuracion">
                        <option value="10">10 minutos</option>
                        <option value="15" selected>15 minutos</option>
                        <option value="20">20 minutos</option>
                        <option value="30">30 minutos</option>
                    </select>
                </div>
            </div>

            <!-- Información de Asignaciones Académicas -->
            <div class="alert-info-crear mt-4 d-none" id="infoAsignaciones">
                <i class="bi bi-info-circle me-2"></i>
                <div id="textoAsignaciones">
                    Cargando información de asignaciones académicas...
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-between flex-wrap gap-2">
                <button type="button" class="btn-secundario-final" id="btnBack1">
                    <i class="bi bi-arrow-left me-2"></i>
                    Volver
                </button>
                
                <button type="button" class="btn-success-final" id="btnGenerarAutomatico">
                    <i class="bi bi-magic me-2"></i>
                    Generar Horarios del Nivel
                </button>
            </div>
        </div>
    </div>

    <!-- Paso 3: Visualización de Horarios Generados -->
    <div class="card-final mb-4 d-none" id="stepGrid">
        <div class="card-header-final">
            <div class="d-flex justify-content-between align-items-center w-100">
                <h6 class="mb-0">
                    <i class="bi bi-table me-2" style="color: #1e40af;"></i>
                    Paso 3: Horarios Generados del Nivel
                </h6>
                <span class="badge-info-final" id="selectedInfo"></span>
            </div>
        </div>
        <div class="card-body-crear p-4">
            <!-- Badge de modo -->
            <div class="mb-3">
                <span class="badge bg-success" id="badgeModo">Modo: Generación Automática por Nivel</span>
            </div>

            <div class="alert-success-crear mb-4">
                <i class="bi bi-check-circle me-2"></i>
                <div>
                    <strong>Horarios generados exitosamente:</strong> El sistema ha distribuido automáticamente todas las asignaturas de todos los grados del nivel, respetando las restricciones de profesores y preferencias configuradas.
                </div>
            </div>

            <!-- Estadísticas de generación automática -->
            <div class="alert alert-success mb-4 d-none" id="statsAutomaticas">
                <h6><i class="bi bi-graph-up me-2"></i>Estadísticas de Generación del Nivel</h6>
                <div class="row" id="statsContent">
                    <!-- Se llena dinámicamente -->
                </div>
            </div>

            <!-- Contenedor de Tablas de Horario (una por cada grado) -->
            <div id="horarioTableContainer">
                <!-- Se genera dinámicamente con JavaScript -->
            </div>

            <div class="mt-4 d-flex justify-content-between flex-wrap gap-2">
                <button type="button" class="btn-secundario-final" id="btnBack2">
                    <i class="bi bi-arrow-left me-2"></i>
                    Volver
                </button>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn-info" id="btnVerDiagnostico">
                        <i class="bi bi-clipboard-data me-2"></i>
                        Ver Diagnóstico
                    </button>
                    <button type="button" class="btn-warning-final" id="btnRegenerar">
                        <i class="bi bi-arrow-clockwise me-2"></i>
                        Regenerar Horarios
                    </button>
                    <a href="/horarios" class="btn-azul-final">
                        <i class="bi bi-check-lg me-2"></i>
                        Finalizar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Generación Automática -->
<div class="modal fade" id="modalGenerarAuto" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-final">
            <div class="modal-header-final">
                <div>
                    <h5 class="modal-titulo-final">
                        <i class="bi bi-magic me-2"></i>
                        Generación Automática del Nivel
                    </h5>
                </div>
                <button type="button" class="btn-close-final" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body-final">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Información importante:</strong>
                    <p class="mb-0 mt-2">
                        El sistema generará automáticamente los horarios de <strong>todos los grados del nivel</strong> de manera óptima y coordinada.
                    </p>
                </div>

                <div class="form-group-final">
                    <label class="label-modal-final">Opciones de Generación</label>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="modoGeneracion" id="modoReemplazar" value="reemplazar" checked>
                        <label class="form-check-label" for="modoReemplazar">
                            Reemplazar horarios existentes (si existen)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="modoGeneracion" id="modoCombinar" value="combinar">
                        <label class="form-check-label" for="modoCombinar">
                            Combinar con horarios actuales
                        </label>
                    </div>
                </div>

                <div class="alert-info-crear mt-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <small>La generación automática respetará las restricciones de profesores y preferencias de asignaturas configuradas para todos los grados.</small>
                </div>
            </div>
            <div class="modal-footer-final">
                <button type="button" class="btn-modal-cancelar" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn-modal-guardar" id="btnConfirmarGeneracion">
                    <i class="bi bi-magic me-2"></i>
                    Generar Horarios
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Progreso -->
<div class="modal fade" id="modalProgreso" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-final">
            <div class="modal-body-final text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Generando...</span>
                </div>
                <h5 class="mb-3">Generando Horarios del Nivel</h5>
                <p class="text-muted mb-3">
                    Por favor espere mientras el sistema analiza las asignaciones académicas de todos los grados,
                    restricciones y preferencias para crear los mejores horarios posibles...
                </p>
                
                <!-- Barra de progreso -->
                <div class="progress mb-3" style="height: 25px;">
                    <div id="progresoBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        0%
                    </div>
                </div>
                
                <div class="mt-3">
                    <small class="text-muted" id="progresoTexto">Iniciando generación...</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Diagnóstico -->
<div class="modal fade" id="modalDiagnostico" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content modal-diagnostico">
            <!-- Header del Modal -->
            <div class="modal-header-diagnostico">
                <div class="header-content-diagnostico">
                    <div class="icono-diagnostico">
                        <i class="bi bi-clipboard-data"></i>
                    </div>
                    <div>
                        <h5 class="modal-titulo-diagnostico" id="diagnosticoTitulo">
                            Diagnóstico de Generación de Horarios
                        </h5>
                        <p class="modal-subtitulo-diagnostico">
                            Análisis detallado del proceso de generación automática
                        </p>
                    </div>
                </div>
                <button type="button" class="btn-close-diagnostico" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <!-- Body del Modal -->
            <div class="modal-body-diagnostico" id="diagnosticoContenido">
                <!-- El contenido se genera dinámicamente con JavaScript -->
            </div>

            <!-- Footer del Modal -->
            <div class="modal-footer-diagnostico">
                <button type="button" class="btn-secundario-final" data-bs-dismiss="modal" id="btnCerrarDiagnostico">
                    <i class="bi bi-x-lg me-2"></i>
                    Cerrar
                </button>
                <button type="button" class="btn-azul-final" data-bs-dismiss="modal" onclick="$('#btnGenerarAutomatico').click()">
                    <i class="bi bi-arrow-clockwise me-2"></i>
                    Intentar Nuevamente
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<link href="{{ asset('css/horario-create.css') }}" rel="stylesheet">
<script src="{{ asset('js/horario.js') }}"></script>

<style>
/* Estilos adicionales para generación por nivel */
.btn-success-final {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);
}

.btn-success-final:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(16, 185, 129, 0.4);
}

.badge-info-final {
    background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
    color: white;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 600;
}

.alert-success-crear {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    border: 1px solid #10b981;
    border-radius: 8px;
    padding: 16px;
    color: #065f46;
    display: flex;
    align-items: flex-start;
}

.alert-success-crear i {
    color: #10b981;
    font-size: 1.2rem;
    margin-top: 2px;
}

#statsContent {
    font-size: 0.95rem;
}

#statsContent strong {
    display: block;
    font-size: 1.3rem;
    color: #059669;
}

.horario-table td.schedule-cell {
    cursor: default !important;
}

.horario-table td.schedule-cell:hover {
    background-color: inherit !important;
}

.progress-bar {
    transition: width 0.3s ease;
    font-weight: 600;
    font-size: 14px;
}

/* Estilos para cards de grados */
#horarioTableContainer .card {
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

#horarioTableContainer .card-header {
    border-bottom: 2px solid #1e40af;
}
</style>
@endsection