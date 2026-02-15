@extends('layouts.master')

@section('content')


@section('styles')
<link rel="stylesheet" href="{{ asset('css/asignaciones.css') }}">
@endsection

<div class="asignaciones-container">
    
    <!-- Header -->
    <header class="page-header">
        <div class="header-content">
            <div class="header-left">
                <h1 class="page-title">Asignaciones Académicas</h1>
            </div>
            <div class="header-right">
                <div class="year-selector">
                    <label for="yearGlobal">AÑO ACADÉMICO</label>
                    <select class="select-modern" id="yearGlobal">
                        @foreach($years as $year)
                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation Tabs -->
    <nav class="tab-navigation">
        <button class="tab-btn active" data-tab="profesor">
            <i class="fas fa-user-tie"></i>
            <span>Asignación por Profesor</span>
        </button>
        <button class="tab-btn" data-tab="consulta">
            <i class="fas fa-chart-bar"></i>
            <span>Consultar Asignaciones</span>
        </button>
    </nav>

    <!-- Tab Content -->
    <div class="tabs-container">
        
        <!-- ========================================== -->
        <!-- TAB 1: ASIGNACIÓN POR PROFESOR (SIN CAMBIOS) -->
        <!-- ========================================== -->
        <div class="tab-content active" id="tab-profesor">
            
            <div class="content-section">
                
                <!-- Selector de Profesor -->
                <div class="form-section">
                    <div class="form-row">
                        <div class="form-col">
                            <label class="form-label">SELECCIONE EL PROFESOR</label>
                            <select class="form-input select-lg" id="profesorRapido">
                                <option value="">Buscar profesor...</option>
                                @foreach($profesores as $profesor)
                                    <option value="{{ $profesor->id }}">{{ $profesor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-col-auto">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-primary btn-lg" id="btnCargarProfesor" disabled>
                                <i class="fas fa-sync"></i>
                                Cargar Asignaturas
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Información del Profesor -->
                <div id="infoProfesor" style="display: none;">
                    
                    <!-- Info Card Compacta -->
                    <div class="profesor-card-simple">
                        <div class="profesor-icon-box">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="profesor-info-box">
                            <h4 class="profesor-title" id="nombreProfesor"></h4>
                            <p class="profesor-detail">
                                <i class="fas fa-book"></i>
                                <strong id="asignaturasProfesor">0</strong> Asignaturas disponibles
                            </p>
                        </div>
                    </div>

                    <!-- Configuración de Asignaciones -->
                    <div class="assignments-config">
                        <h3 class="section-subtitle">CONFIGURACIÓN DE ASIGNACIONES</h3>
                        <div id="contenedorAsignacionesProfesor"></div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="actions-bar">
                        <button type="button" class="btn btn-success btn-lg" id="btnGuardarProfesor">
                            <i class="fas fa-save"></i>
                            Guardar Todas las Asignaciones
                        </button>
                    </div>
                </div>

                <!-- Loading -->
                <div id="loadingProfesor" class="loading-container" style="display: none;">
                    <div class="spinner"></div>
                    <p>Cargando información del profesor...</p>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: CONSULTAR ASIGNACIONES (NUEVO) -->
        <!-- ========================================== -->
        <div class="tab-content" id="tab-consulta">
            
            <div class="content-section">
                
                <!-- Filtros Globales -->
                <div class="filters-section">
                    <h3 class="section-subtitle">
                        <i class="fas fa-filter"></i>
                        FILTROS GLOBALES
                    </h3>
                    <div class="form-row">
                        <div class="form-col">
                            <label class="form-label">NIVEL</label>
                            <select class="form-input" id="filtroNivelGlobal">
                                <option value="">Todos los niveles</option>
                                @foreach($niveles as $nivel)
                                    <option value="{{ $nivel->id }}">{{ $nivel->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-col">
                            <label class="form-label">GRADO</label>
                            <select class="form-input" id="filtroGradoGlobal">
                                <option value="">Todos los grados</option>
                            </select>
                        </div>

                        <div class="form-col-auto">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-primary" id="btnAplicarFiltrosGlobales">
                                <i class="fas fa-sync"></i>
                                Actualizar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas Globales -->
                <div class="stats-container" id="estadisticasGlobales">
                    <div class="stat-box stat-primary">
                        <div class="stat-number" id="statTotalGlobal">0</div>
                        <div class="stat-text">Total Asignaciones</div>
                    </div>
                    <div class="stat-box stat-success">
                        <div class="stat-number" id="statCompletasGlobal">0</div>
                        <div class="stat-text">Completas</div>
                    </div>
                    <div class="stat-box stat-warning">
                        <div class="stat-number" id="statParcialesGlobal">0</div>
                        <div class="stat-text">Parciales</div>
                    </div>
                    <div class="stat-box stat-info">
                        <div class="stat-number" id="statHorasGlobal">0</div>
                        <div class="stat-text">Horas Totales</div>
                    </div>
                </div>

                <!-- Sub-Tabs Navigation -->
                <nav class="subtab-navigation">
                    <button class="subtab-btn active" data-subtab="por-profesor">
                        <i class="fas fa-users"></i>
                        <span>Por Profesor</span>
                    </button>
                    <button class="subtab-btn" data-subtab="por-materia">
                        <i class="fas fa-book-open"></i>
                        <span>Por Materia</span>
                    </button>
                    <button class="subtab-btn" data-subtab="por-grado">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Por Grado</span>
                    </button>
                </nav>

                <!-- Sub-Tab Content -->
                <div class="subtabs-container">
                    
                    <!-- ============================================ -->
                    <!-- SUB-TAB 1: POR PROFESOR -->
                    <!-- ============================================ -->
                    <div class="subtab-content active" id="subtab-por-profesor">
                        
                        <!-- Barra de búsqueda -->
                        <div class="search-bar">
                            <i class="fas fa-search"></i>
                            <input type="text" id="buscarProfesor" class="search-input" placeholder="Buscar profesor por nombre...">
                        </div>

                        <!-- Contenedor de Cards -->
                        <div id="contenedorProfesores" class="cards-grid">
                            <!-- Se llena dinámicamente -->
                        </div>

                        <!-- Loading -->
                        <div id="loadingProfesores" class="loading-container">
                            <div class="spinner"></div>
                            <p>Cargando profesores...</p>
                        </div>

                        <!-- Sin datos -->
                        <div id="sinDatosProfesores" class="empty-container" style="display: none;">
                            <i class="fas fa-user-slash"></i>
                            <h3>No hay profesores con asignaciones</h3>
                            <p>No se encontraron asignaciones para los filtros seleccionados</p>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- SUB-TAB 2: POR MATERIA -->
                    <!-- ============================================ -->
                    <div class="subtab-content" id="subtab-por-materia">
                        
                        <!-- Barra de búsqueda -->
                        <div class="search-bar">
                            <i class="fas fa-search"></i>
                            <input type="text" id="buscarMateria" class="search-input" placeholder="Buscar materia por nombre...">
                        </div>

                        <!-- Contenedor de Cards -->
                        <div id="contenedorMaterias" class="cards-grid">
                            <!-- Se llena dinámicamente -->
                        </div>

                        <!-- Loading -->
                        <div id="loadingMaterias" class="loading-container">
                            <div class="spinner"></div>
                            <p>Cargando materias...</p>
                        </div>

                        <!-- Sin datos -->
                        <div id="sinDatosMaterias" class="empty-container" style="display: none;">
                            <i class="fas fa-book"></i>
                            <h3>No hay materias con asignaciones</h3>
                            <p>No se encontraron asignaciones para los filtros seleccionados</p>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- SUB-TAB 3: POR GRADO -->
                    <!-- ============================================ -->
                    <div class="subtab-content" id="subtab-por-grado">
                        
                        <!-- Barra de búsqueda -->
                        <div class="search-bar">
                            <i class="fas fa-search"></i>
                            <input type="text" id="buscarGrado" class="search-input" placeholder="Buscar grado...">
                        </div>

                        <!-- Contenedor de Cards -->
                        <div id="contenedorGrados" class="cards-grid">
                            <!-- Se llena dinámicamente -->
                        </div>

                        <!-- Loading -->
                        <div id="loadingGrados" class="loading-container">
                            <div class="spinner"></div>
                            <p>Cargando grados...</p>
                        </div>

                        <!-- Sin datos -->
                        <div id="sinDatosGrados" class="empty-container" style="display: none;">
                            <i class="fas fa-school"></i>
                            <h3>No hay grados con asignaciones</h3>
                            <p>No se encontraron asignaciones para los filtros seleccionados</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>






<!-- ========================================== -->
<!-- MODAL ÚNICO CON 3 VISTAS PARA PROFESOR -->
<!-- ========================================== -->
<div class="modal fade" id="modalProfesor" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-user-tie"></i>
                    <span id="tituloModalProfesor"></span>
                </h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                
                <!-- VISTA 1: DETALLE -->
                <div id="vistaDetalleProfesor">
                    <div class="modal-stats-grid">
                        <div class="modal-stat-item">
                            <div class="modal-stat-label">Asignaciones</div>
                            <div class="modal-stat-value" id="modalProfesorAsignaciones">0</div>
                        </div>
                        <div class="modal-stat-item">
                            <div class="modal-stat-label">Horas Totales</div>
                            <div class="modal-stat-value" id="modalProfesorHoras">0</div>
                        </div>
                        <div class="modal-stat-item">
                            <div class="modal-stat-label">Grados</div>
                            <div class="modal-stat-value" id="modalProfesorGrados">0</div>
                        </div>
                        <div class="modal-stat-item">
                            <div class="modal-stat-label">Materias</div>
                            <div class="modal-stat-value" id="modalProfesorMaterias">0</div>
                        </div>
                    </div>

                    <div class="table-responsive mt-4">
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Materia</th>
                                    <th>Grado</th>
                                    <th class="text-center">Horas/Sem</th>
                                    <th class="text-center">Asignadas</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaDetalleProfesor"></tbody>
                        </table>
                    </div>
                </div>

                <!-- VISTA 2: EDICIÓN (SOLO HORAS) -->
                <div id="vistaEdicionProfesor" style="display: none;">
                    <form id="formEditarAsignacion">
                        <input type="hidden" id="editId">
                        
                        <div class="info-group">
                            <div class="info-item">
                                <label>PROFESOR</label>
                                <p id="editProfesor"></p>
                            </div>
                            <div class="info-item">
                                <label>ASIGNATURA</label>
                                <p id="editAsignatura"></p>
                            </div>
                            <div class="info-item full">
                                <label>GRADO</label>
                                <p id="editGrado"></p>
                            </div>
                        </div>

                        <div class="separator"></div>
                        
                        <div class="form-group">
                            <label for="editHoras" class="form-label">HORAS SEMANALES</label>
                            <input type="number" class="form-input" id="editHoras" min="1" max="40" required>
                            <small class="form-text text-muted">Número de horas semanales para esta asignación</small>
                        </div>
                    </form>
                </div>

                <!-- VISTA 3: PREFERENCIAS -->
                <div id="vistaPreferenciasProfesor" style="display: none;">
                    <form id="formPreferenciasProfesor">
                        <input type="hidden" id="prefId">
                        
                        <div class="alert alert-info" role="alert">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Asignación:</strong>
                            </div>
                            <div class="ms-4">
                                <p class="mb-1"><strong>Profesor:</strong> <span id="prefProfesor"></span></p>
                                <p class="mb-1"><strong>Materia:</strong> <span id="prefAsignatura"></span></p>
                                <p class="mb-0"><strong>Grado:</strong> <span id="prefGrado"></span></p>
                            </div>
                        </div>

                        <div class="separator"></div>

                        <h5 class="mb-3">
                            <i class="fas fa-clock text-primary me-2"></i>
                            Restricciones de Horario
                        </h5>

                        <div class="form-group">
                            <label for="prefPosicionJornada" class="form-label">
                                <i class="fas fa-map-marker-alt text-primary me-1"></i>
                                POSICIÓN EN LA JORNADA
                            </label>
                            <select class="form-input" id="prefPosicionJornada">
                                <option value="sin_restriccion">Sin restricción</option>
                                <option value="primeras_horas">Primeras horas del día</option>
                                <option value="ultimas_horas">Últimas horas del día</option>
                                <option value="antes_recreo">Antes del recreo</option>
                                <option value="despues_recreo">Después del recreo</option>
                            </select>
                            <small class="form-text text-muted">Define en qué momento de la jornada se prefiere ubicar esta asignación</small>
                        </div>

                        <div class="form-row mt-3">
                            <div class="form-col">
                                <label for="prefMaxHorasPorDia" class="form-label">
                                    <i class="fas fa-calendar-day text-primary me-1"></i>
                                    MAX. HORAS/DÍA
                                </label>
                                <input type="number" class="form-input" id="prefMaxHorasPorDia" 
                                       min="1" max="8" placeholder="Sin límite">
                                <small class="form-text text-muted">Máximo de horas por día (1-8)</small>
                            </div>
                            <div class="form-col">
                                <label for="prefMaxDiasSemana" class="form-label">
                                    <i class="fas fa-calendar-week text-primary me-1"></i>
                                    MAX. DÍAS/SEMANA
                                </label>
                                <input type="number" class="form-input" id="prefMaxDiasSemana" 
                                       min="1" max="5" placeholder="Sin límite">
                                <small class="form-text text-muted">Máximo de días por semana (1-5)</small>
                            </div>
                        </div>

                        <div class="alert alert-warning mt-4" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Nota:</strong> Estas preferencias serán consideradas durante la generación automática del horario.
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <!-- Botones VISTA DETALLE -->
                <div id="botonesDetalleProfesor">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
                <!-- Botones VISTA EDICIÓN -->
                <div id="botonesEdicionProfesor" style="display: none;">
                    <button type="button" class="btn btn-secondary" onclick="volverADetalle('profesor')">
                        <i class="fas fa-arrow-left"></i>
                        Volver
                    </button>
                    <button type="button" class="btn btn-primary" onclick="guardarEdicion('profesor')">
                        <i class="fas fa-save"></i>
                        Guardar Cambios
                    </button>
                </div>
                <!-- Botones VISTA PREFERENCIAS -->
                <div id="botonesPreferenciasProfesor" style="display: none;">
                    <button type="button" class="btn btn-secondary" onclick="volverADetalle('profesor')">
                        <i class="fas fa-arrow-left"></i>
                        Volver
                    </button>
                    <button type="button" class="btn btn-primary" onclick="guardarPreferencias('profesor')">
                        <i class="fas fa-save"></i>
                        Guardar Preferencias
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL ÚNICO CON 3 VISTAS PARA MATERIA -->
<div class="modal fade" id="modalMateria" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-book-open"></i>
                    <span id="tituloModalMateria"></span>
                </h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                
                <!-- VISTA 1: DETALLE -->
                <div id="vistaDetalleMateria">
                    <div class="modal-stats-grid">
                        <div class="modal-stat-item">
                            <div class="modal-stat-label">Asignaciones</div>
                            <div class="modal-stat-value" id="modalMateriaAsignaciones">0</div>
                        </div>
                        <div class="modal-stat-item">
                            <div class="modal-stat-label">Profesores</div>
                            <div class="modal-stat-value" id="modalMateriaProfesores">0</div>
                        </div>
                        <div class="modal-stat-item">
                            <div class="modal-stat-label">Grados</div>
                            <div class="modal-stat-value" id="modalMateriaGrados">0</div>
                        </div>
                        <div class="modal-stat-item">
                            <div class="modal-stat-label">Horas Totales</div>
                            <div class="modal-stat-value" id="modalMateriaHoras">0</div>
                        </div>
                    </div>

                    <div class="table-responsive mt-4">
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Profesor</th>
                                    <th>Grado</th>
                                    <th class="text-center">Horas/Sem</th>
                                    <th class="text-center">Asignadas</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaDetalleMateria"></tbody>
                        </table>
                    </div>
                </div>

                <!-- VISTA 2: EDICIÓN -->
                <div id="vistaEdicionMateria" style="display: none;">
                    <form id="formEditarAsignacionMateria">
                        <input type="hidden" id="editIdMateria">
                        
                        <div class="info-group">
                            <div class="info-item">
                                <label>PROFESOR</label>
                                <p id="editProfesorMateria"></p>
                            </div>
                            <div class="info-item">
                                <label>ASIGNATURA</label>
                                <p id="editAsignaturaMateria"></p>
                            </div>
                            <div class="info-item full">
                                <label>GRADO</label>
                                <p id="editGradoMateria"></p>
                            </div>
                        </div>

                        <div class="separator"></div>
                        
                        <div class="form-group">
                            <label for="editHorasMateria" class="form-label">HORAS SEMANALES</label>
                            <input type="number" class="form-input" id="editHorasMateria" min="1" max="40" required>
                            <small class="form-text text-muted">Número de horas semanales para esta asignación</small>
                        </div>
                    </form>
                </div>

                <!-- VISTA 3: PREFERENCIAS -->
                <div id="vistaPreferenciasMateria" style="display: none;">
                    <form id="formPreferenciasMateria">
                        <input type="hidden" id="prefIdMateria">
                        
                        <div class="alert alert-info" role="alert">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Asignación:</strong>
                            </div>
                            <div class="ms-4">
                                <p class="mb-1"><strong>Profesor:</strong> <span id="prefProfesorMateria"></span></p>
                                <p class="mb-1"><strong>Materia:</strong> <span id="prefAsignaturaMateria"></span></p>
                                <p class="mb-0"><strong>Grado:</strong> <span id="prefGradoMateria"></span></p>
                            </div>
                        </div>

                        <div class="separator"></div>

                        <h5 class="mb-3">
                            <i class="fas fa-clock text-primary me-2"></i>
                            Restricciones de Horario
                        </h5>

                        <div class="form-group">
                            <label for="prefPosicionJornadaMateria" class="form-label">
                                <i class="fas fa-map-marker-alt text-primary me-1"></i>
                                POSICIÓN EN LA JORNADA
                            </label>
                            <select class="form-input" id="prefPosicionJornadaMateria">
                                <option value="sin_restriccion">Sin restricción</option>
                                <option value="primeras_horas">Primeras horas del día</option>
                                <option value="ultimas_horas">Últimas horas del día</option>
                                <option value="antes_recreo">Antes del recreo</option>
                                <option value="despues_recreo">Después del recreo</option>
                            </select>
                            <small class="form-text text-muted">Define en qué momento de la jornada se prefiere ubicar esta asignación</small>
                        </div>

                        <div class="form-row mt-3">
                            <div class="form-col">
                                <label for="prefMaxHorasPorDiaMateria" class="form-label">
                                    <i class="fas fa-calendar-day text-primary me-1"></i>
                                    MAX. HORAS/DÍA
                                </label>
                                <input type="number" class="form-input" id="prefMaxHorasPorDiaMateria" 
                                       min="1" max="8" placeholder="Sin límite">
                                <small class="form-text text-muted">Máximo de horas por día (1-8)</small>
                            </div>
                            <div class="form-col">
                                <label for="prefMaxDiasSemanaMateria" class="form-label">
                                    <i class="fas fa-calendar-week text-primary me-1"></i>
                                    MAX. DÍAS/SEMANA
                                </label>
                                <input type="number" class="form-input" id="prefMaxDiasSemanaMateria" 
                                       min="1" max="5" placeholder="Sin límite">
                                <small class="form-text text-muted">Máximo de días por semana (1-5)</small>
                            </div>
                        </div>

                        <div class="alert alert-warning mt-4" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Nota:</strong> Estas preferencias serán consideradas durante la generación automática del horario.
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <div id="botonesDetalleMateria">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
                <div id="botonesEdicionMateria" style="display: none;">
                    <button type="button" class="btn btn-secondary" onclick="volverADetalle('materia')">
                        <i class="fas fa-arrow-left"></i>
                        Volver
                    </button>
                    <button type="button" class="btn btn-primary" onclick="guardarEdicion('materia')">
                        <i class="fas fa-save"></i>
                        Guardar Cambios
                    </button>
                </div>
                <div id="botonesPreferenciasMateria" style="display: none;">
                    <button type="button" class="btn btn-secondary" onclick="volverADetalle('materia')">
                        <i class="fas fa-arrow-left"></i>
                        Volver
                    </button>
                    <button type="button" class="btn btn-primary" onclick="guardarPreferencias('materia')">
                        <i class="fas fa-save"></i>
                        Guardar Preferencias
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>




<!-- MODAL ÚNICO CON 3 VISTAS PARA GRADO -->
<div class="modal fade" id="modalGrado" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-graduation-cap"></i>
                    <span id="tituloModalGrado"></span>
                </h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                
                <!-- VISTA 1: DETALLE -->
                <div id="vistaDetalleGrado">
                    <div class="modal-stats-grid">
                        <div class="modal-stat-item">
                            <div class="modal-stat-label">Asignaciones</div>
                            <div class="modal-stat-value" id="modalGradoAsignaciones">0</div>
                        </div>
                        <div class="modal-stat-item">
                            <div class="modal-stat-label">Materias</div>
                            <div class="modal-stat-value" id="modalGradoMaterias">0</div>
                        </div>
                        <div class="modal-stat-item">
                            <div class="modal-stat-label">Profesores</div>
                            <div class="modal-stat-value" id="modalGradoProfesores">0</div>
                        </div>
                        <div class="modal-stat-item">
                            <div class="modal-stat-label">Horas Totales</div>
                            <div class="modal-stat-value" id="modalGradoHoras">0</div>
                        </div>
                    </div>

                    <div class="table-responsive mt-4">
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Materia</th>
                                    <th>Profesor</th>
                                    <th class="text-center">Horas/Sem</th>
                                    <th class="text-center">Asignadas</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaDetalleGrado"></tbody>
                        </table>
                    </div>
                </div>

                <!-- VISTA 2: EDICIÓN -->
                <div id="vistaEdicionGrado" style="display: none;">
                    <form id="formEditarAsignacionGrado">
                        <input type="hidden" id="editIdGrado">
                        
                        <div class="info-group">
                            <div class="info-item">
                                <label>PROFESOR</label>
                                <p id="editProfesorGrado"></p>
                            </div>
                            <div class="info-item">
                                <label>ASIGNATURA</label>
                                <p id="editAsignaturaGrado"></p>
                            </div>
                            <div class="info-item full">
                                <label>GRADO</label>
                                <p id="editGradoGrado"></p>
                            </div>
                        </div>

                        <div class="separator"></div>
                        
                        <div class="form-group">
                            <label for="editHorasGrado" class="form-label">HORAS SEMANALES</label>
                            <input type="number" class="form-input" id="editHorasGrado" min="1" max="40" required>
                            <small class="form-text text-muted">Número de horas semanales para esta asignación</small>
                        </div>
                    </form>
                </div>

                <!-- VISTA 3: PREFERENCIAS -->
                <div id="vistaPreferenciasGrado" style="display: none;">
                    <form id="formPreferenciasGrado">
                        <input type="hidden" id="prefIdGrado">
                        
                        <div class="alert alert-info" role="alert">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Asignación:</strong>
                            </div>
                            <div class="ms-4">
                                <p class="mb-1"><strong>Profesor:</strong> <span id="prefProfesorGrado"></span></p>
                                <p class="mb-1"><strong>Materia:</strong> <span id="prefAsignaturaGrado"></span></p>
                                <p class="mb-0"><strong>Grado:</strong> <span id="prefGradoGrado"></span></p>
                            </div>
                        </div>

                        <div class="separator"></div>

                        <h5 class="mb-3">
                            <i class="fas fa-clock text-primary me-2"></i>
                            Restricciones de Horario
                        </h5>

                        <div class="form-group">
                            <label for="prefPosicionJornadaGrado" class="form-label">
                                <i class="fas fa-map-marker-alt text-primary me-1"></i>
                                POSICIÓN EN LA JORNADA
                            </label>
                            <select class="form-input" id="prefPosicionJornadaGrado">
                                <option value="sin_restriccion">Sin restricción</option>
                                <option value="primeras_horas">Primeras horas del día</option>
                                <option value="ultimas_horas">Últimas horas del día</option>
                                <option value="antes_recreo">Antes del recreo</option>
                                <option value="despues_recreo">Después del recreo</option>
                            </select>
                            <small class="form-text text-muted">Define en qué momento de la jornada se prefiere ubicar esta asignación</small>
                        </div>

                        <div class="form-row mt-3">
                            <div class="form-col">
                                <label for="prefMaxHorasPorDiaGrado" class="form-label">
                                    <i class="fas fa-calendar-day text-primary me-1"></i>
                                    MAX. HORAS/DÍA
                                </label>
                                <input type="number" class="form-input" id="prefMaxHorasPorDiaGrado" 
                                       min="1" max="8" placeholder="Sin límite">
                                <small class="form-text text-muted">Máximo de horas por día (1-8)</small>
                            </div>
                            <div class="form-col">
                                <label for="prefMaxDiasSemanaGrado" class="form-label">
                                    <i class="fas fa-calendar-week text-primary me-1"></i>
                                    MAX. DÍAS/SEMANA
                                </label>
                                <input type="number" class="form-input" id="prefMaxDiasSemanaGrado" 
                                       min="1" max="5" placeholder="Sin límite">
                                <small class="form-text text-muted">Máximo de días por semana (1-5)</small>
                            </div>
                        </div>

                        <div class="alert alert-warning mt-4" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Nota:</strong> Estas preferencias serán consideradas durante la generación automática del horario.
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <div id="botonesDetalleGrado">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
                <div id="botonesEdicionGrado" style="display: none;">
                    <button type="button" class="btn btn-secondary" onclick="volverADetalle('grado')">
                        <i class="fas fa-arrow-left"></i>
                        Volver
                    </button>
                    <button type="button" class="btn btn-primary" onclick="guardarEdicion('grado')">
                        <i class="fas fa-save"></i>
                        Guardar Cambios
                    </button>
                </div>
                <div id="botonesPreferenciasGrado" style="display: none;">
                    <button type="button" class="btn btn-secondary" onclick="volverADetalle('grado')">
                        <i class="fas fa-arrow-left"></i>
                        Volver
                    </button>
                    <button type="button" class="btn btn-primary" onclick="guardarPreferencias('grado')">
                        <i class="fas fa-save"></i>
                        Guardar Preferencias
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    let yearActual = $('#yearGlobal').val();
    let asignacionesGlobales = [];
    let profesorActual = null;
    let filtrosActuales = {
        nivel_id: '',
        grado_id: ''
    };
    
    let modalActivo = null;

    // ========================================
    // INICIALIZACIÓN
    // ========================================
    
    cargarDatosConsulta();

    // ========================================
    // TAB NAVIGATION (Principal)
    // ========================================
    
    $('.tab-btn').on('click', function() {
        const tab = $(this).data('tab');
        
        $('.tab-btn').removeClass('active');
        $(this).addClass('active');
        
        $('.tab-content').removeClass('active');
        $('#tab-' + tab).addClass('active');
        
        if (tab === 'consulta') {
            cargarDatosConsulta();
        }
    });

    // ========================================
    // SUB-TAB NAVIGATION (Secundario)
    // ========================================
    
    $('.subtab-btn').on('click', function() {
        const subtab = $(this).data('subtab');
        
        $('.subtab-btn').removeClass('active');
        $(this).addClass('active');
        
        $('.subtab-content').removeClass('active');
        $('#subtab-' + subtab).addClass('active');
    });

    // ========================================
    // YEAR SELECTOR
    // ========================================
    
    $('#yearGlobal').on('change', function() {
        yearActual = $(this).val();
        
        if ($('#tab-consulta').hasClass('active')) {
            cargarDatosConsulta();
        }
    });

    // ========================================
    // TAB 1: ASIGNACIÓN POR PROFESOR
    // ========================================
    
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
                <div class="empty-container">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Sin Asignaturas Asignadas</h3>
                    <p>Este profesor no tiene asignaturas. Debe asignarle asignaturas en el módulo de profesores.</p>
                </div>
            `;
            $('#contenedorAsignacionesProfesor').html(html);
            $('#btnGuardarProfesor').hide();
            return;
        }

        $('#btnGuardarProfesor').show();

        profesor.asignaturas.forEach((asignatura) => {
            html += `
                <div class="subject-item">
                    <h3 class="subject-header">
                        <i class="fas fa-book"></i>
                        ${asignatura.nombre}
                    </h3>
                    <div id="grados-asignatura-${asignatura.id}"></div>
                    <button type="button" class="btn btn-outline btn-sm btn-add-grade" 
                            onclick="agregarGradoAsignatura(${asignatura.id})">
                        <i class="fas fa-plus"></i>
                        Agregar Grado
                    </button>
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
            <div class="grade-assignment" data-asignatura="${asignaturaId}" data-index="${index}">
                <select class="form-input select-nivel" data-index="${index}">
                    <option value="">Seleccione nivel...</option>
                    @foreach($niveles as $nivel)
                        <option value="{{ $nivel->id }}">{{ $nivel->nombre }}</option>
                    @endforeach
                </select>
                <select class="form-input select-grado" data-index="${index}" disabled>
                    <option value="">Seleccione nivel primero...</option>
                </select>
                <input type="number" class="form-input input-horas" 
                       data-index="${index}"
                       placeholder="Horas" min="1" max="40">
                <button type="button" class="btn btn-danger btn-sm" 
                        onclick="eliminarFilaGrado(${asignaturaId}, ${index})"
                        style="width: 50px; height: 50px;">
                    <i class="fas fa-trash"></i>
                </button>
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
                selectGrado.html('<option value="">Seleccione nivel primero...</option>').prop('disabled', true);
            }
        });
    };

    window.eliminarFilaGrado = function(asignaturaId, index) {
        $(`.grade-assignment[data-asignatura="${asignaturaId}"][data-index="${index}"]`).remove();
    };

    function guardarAsignacionesProfesor() {
        const datos = [];
        const profesorId = $('#profesorRapido').val();

        $('.grade-assignment').each(function() {
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
            mostrarAdvertencia('Complete al menos una asignación antes de guardar');
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
                    $('#profesorRapido').val('');
                    $('#infoProfesor').hide();
                    $('#btnCargarProfesor').prop('disabled', true);
                    
                    if ($('#tab-consulta').hasClass('active')) {
                        cargarDatosConsulta();
                    }
                } else {
                    mostrarError(response.message);
                }
            },
            error: function(xhr) {
                mostrarError('Error al guardar las asignaciones');
            },
            complete: function() {
                $('#btnGuardarProfesor').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Todas las Asignaciones');
            }
        });
    }

    // ========================================
    // TAB 2: CONSULTAR ASIGNACIONES
    // ========================================
    
    $('#filtroNivelGlobal').on('change', function() {
        const nivelId = $(this).val();
        cargarGrados(nivelId, '#filtroGradoGlobal');
        filtrosActuales.nivel_id = nivelId;
        filtrosActuales.grado_id = '';
        $('#filtroGradoGlobal').val('');
    });

    $('#btnAplicarFiltrosGlobales').on('click', function() {
        filtrosActuales.nivel_id = $('#filtroNivelGlobal').val();
        filtrosActuales.grado_id = $('#filtroGradoGlobal').val();
        cargarDatosConsulta();
    });

    $('#buscarProfesor').on('input', function() {
        const termino = $(this).val().toLowerCase();
        filtrarCards('.entity-card[data-tipo="profesor"]', termino);
    });

    $('#buscarMateria').on('input', function() {
        const termino = $(this).val().toLowerCase();
        filtrarCards('.entity-card[data-tipo="materia"]', termino);
    });

    $('#buscarGrado').on('input', function() {
        const termino = $(this).val().toLowerCase();
        filtrarCards('.entity-card[data-tipo="grado"]', termino);
    });

    function filtrarCards(selector, termino) {
        $(selector).each(function() {
            const nombre = $(this).data('nombre').toLowerCase();
            if (nombre.includes(termino)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    function cargarDatosConsulta() {
        mostrarLoadings();
        
        const filtros = {
            year: yearActual,
            nivel_id: filtrosActuales.nivel_id,
            grado_id: filtrosActuales.grado_id
        };

        $.ajax({
            url: '{{ route("asignaciones.listar") }}',
            method: 'GET',
            data: filtros,
            success: function(response) {
                if (response.success) {
                    asignacionesGlobales = response.asignaciones;
                    actualizarEstadisticasGlobales();
                    renderizarPorProfesor();
                    renderizarPorMateria();
                    renderizarPorGrado();
                }
            },
            error: function(xhr) {
                mostrarError('Error al cargar datos');
            },
            complete: function() {
                ocultarLoadings();
            }
        });
    }

    function mostrarLoadings() {
        $('#loadingProfesores, #loadingMaterias, #loadingGrados').show();
        $('#contenedorProfesores, #contenedorMaterias, #contenedorGrados').hide();
        $('#sinDatosProfesores, #sinDatosMaterias, #sinDatosGrados').hide();
    }

    function ocultarLoadings() {
        $('#loadingProfesores, #loadingMaterias, #loadingGrados').hide();
    }

    function actualizarEstadisticasGlobales() {
        const total = asignacionesGlobales.length;
        const completas = asignacionesGlobales.filter(a => a.estado === 'completo').length;
        const parciales = asignacionesGlobales.filter(a => a.estado === 'parcial').length;
        const horasTotales = asignacionesGlobales.reduce((sum, a) => sum + (a.horas_semanales || 0), 0);

        $('#statTotalGlobal').text(total);
        $('#statCompletasGlobal').text(completas);
        $('#statParcialesGlobal').text(parciales);
        $('#statHorasGlobal').text(horasTotales);
    }

    // ========================================
    // RENDERIZADO: POR PROFESOR
    // ========================================
    
    function renderizarPorProfesor() {
        const contenedor = $('#contenedorProfesores');
        contenedor.empty().show();

        const porProfesor = {};
        asignacionesGlobales.forEach(asig => {
            const profId = asig.profesor_id;
            if (!porProfesor[profId]) {
                porProfesor[profId] = {
                    id: profId,
                    nombre: asig.profesor.name,
                    asignaciones: [],
                    totalHoras: 0,
                    grados: new Set(),
                    materias: new Set()
                };
            }
            porProfesor[profId].asignaciones.push(asig);
            porProfesor[profId].totalHoras += asig.horas_semanales;
            porProfesor[profId].grados.add(asig.grado_id);
            porProfesor[profId].materias.add(asig.asignatura_id);
        });

        const profesores = Object.values(porProfesor);

        if (profesores.length === 0) {
            contenedor.hide();
            $('#sinDatosProfesores').show();
            return;
        }

        $('#sinDatosProfesores').hide();

        profesores.forEach(prof => {
            const completas = prof.asignaciones.filter(a => a.estado === 'completo').length;
            const porcentaje = prof.asignaciones.length > 0 
                ? Math.round((completas / prof.asignaciones.length) * 100) 
                : 0;

            let badgeClass = 'badge-pending';
            let badgeText = 'Incompleto';
            if (porcentaje === 100) {
                badgeClass = 'badge-complete';
                badgeText = 'Completo';
            } else if (porcentaje > 0) {
                badgeClass = 'badge-partial';
                badgeText = 'Parcial';
            }

            const card = `
                <div class="entity-card" data-tipo="profesor" data-nombre="${prof.nombre}" data-id="${prof.id}">
                    <div class="entity-card-header">
                        <div class="entity-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="entity-title">
                            <h4 class="entity-name">${prof.nombre}</h4>
                            <p class="entity-subtitle">${prof.asignaciones.length} asignaciones</p>
                        </div>
                    </div>
                    <div class="entity-stats">
                        <div class="entity-stat">
                            <div class="entity-stat-value">${prof.totalHoras}</div>
                            <div class="entity-stat-label">Horas/Semana</div>
                        </div>
                        <div class="entity-stat">
                            <div class="entity-stat-value">${prof.grados.size}</div>
                            <div class="entity-stat-label">Grados</div>
                        </div>
                    </div>
                    <div class="entity-footer">
                        <span class="entity-badge ${badgeClass}">
                            <i class="fas fa-circle"></i>
                            ${badgeText}
                        </span>
                        <span class="entity-action">
                            Ver detalle
                            <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </div>
            `;
            contenedor.append(card);
        });

        $('.entity-card[data-tipo="profesor"]').on('click', function() {
            const profesorId = $(this).data('id');
            mostrarDetalleProfesor(profesorId);
        });
    }

    // ========================================
    // RENDERIZADO: POR MATERIA
    // ========================================
    
    function renderizarPorMateria() {
        const contenedor = $('#contenedorMaterias');
        contenedor.empty().show();

        const porMateria = {};
        asignacionesGlobales.forEach(asig => {
            const matId = asig.asignatura_id;
            if (!porMateria[matId]) {
                porMateria[matId] = {
                    id: matId,
                    nombre: asig.asignatura.nombre,
                    asignaciones: [],
                    totalHoras: 0,
                    profesores: new Set(),
                    grados: new Set()
                };
            }
            porMateria[matId].asignaciones.push(asig);
            porMateria[matId].totalHoras += asig.horas_semanales;
            porMateria[matId].profesores.add(asig.profesor_id);
            porMateria[matId].grados.add(asig.grado_id);
        });

        const materias = Object.values(porMateria);

        if (materias.length === 0) {
            contenedor.hide();
            $('#sinDatosMaterias').show();
            return;
        }

        $('#sinDatosMaterias').hide();

        materias.forEach(mat => {
            const completas = mat.asignaciones.filter(a => a.estado === 'completo').length;
            const porcentaje = mat.asignaciones.length > 0 
                ? Math.round((completas / mat.asignaciones.length) * 100) 
                : 0;

            let badgeClass = 'badge-pending';
            let badgeText = 'Incompleto';
            if (porcentaje === 100) {
                badgeClass = 'badge-complete';
                badgeText = 'Completo';
            } else if (porcentaje > 0) {
                badgeClass = 'badge-partial';
                badgeText = 'Parcial';
            }

            const card = `
                <div class="entity-card" data-tipo="materia" data-nombre="${mat.nombre}" data-id="${mat.id}">
                    <div class="entity-card-header">
                        <div class="entity-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="entity-title">
                            <h4 class="entity-name">${mat.nombre}</h4>
                            <p class="entity-subtitle">${mat.asignaciones.length} asignaciones</p>
                        </div>
                    </div>
                    <div class="entity-stats">
                        <div class="entity-stat">
                            <div class="entity-stat-value">${mat.profesores.size}</div>
                            <div class="entity-stat-label">Profesores</div>
                        </div>
                        <div class="entity-stat">
                            <div class="entity-stat-value">${mat.grados.size}</div>
                            <div class="entity-stat-label">Grados</div>
                        </div>
                    </div>
                    <div class="entity-footer">
                        <span class="entity-badge ${badgeClass}">
                            <i class="fas fa-circle"></i>
                            ${badgeText}
                        </span>
                        <span class="entity-action">
                            Ver detalle
                            <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </div>
            `;
            contenedor.append(card);
        });

        $('.entity-card[data-tipo="materia"]').on('click', function() {
            const materiaId = $(this).data('id');
            mostrarDetalleMateria(materiaId);
        });
    }

    // ========================================
    // RENDERIZADO: POR GRADO
    // ========================================
    
    function renderizarPorGrado() {
        const contenedor = $('#contenedorGrados');
        contenedor.empty().show();

        const porGrado = {};
        asignacionesGlobales.forEach(asig => {
            const gradoId = asig.grado_id;
            if (!porGrado[gradoId]) {
                porGrado[gradoId] = {
                    id: gradoId,
                    nombre: asig.grado.nombre,
                    nivel: asig.grado.nivel.nombre,
                    asignaciones: [],
                    totalHoras: 0,
                    profesores: new Set(),
                    materias: new Set()
                };
            }
            porGrado[gradoId].asignaciones.push(asig);
            porGrado[gradoId].totalHoras += asig.horas_semanales;
            porGrado[gradoId].profesores.add(asig.profesor_id);
            porGrado[gradoId].materias.add(asig.asignatura_id);
        });

        const grados = Object.values(porGrado);

        if (grados.length === 0) {
            contenedor.hide();
            $('#sinDatosGrados').show();
            return;
        }

        $('#sinDatosGrados').hide();

        grados.forEach(grado => {
            const completas = grado.asignaciones.filter(a => a.estado === 'completo').length;
            const porcentaje = grado.asignaciones.length > 0 
                ? Math.round((completas / grado.asignaciones.length) * 100) 
                : 0;

            let badgeClass = 'badge-pending';
            let badgeText = 'Incompleto';
            if (porcentaje === 100) {
                badgeClass = 'badge-complete';
                badgeText = 'Completo';
            } else if (porcentaje > 0) {
                badgeClass = 'badge-partial';
                badgeText = 'Parcial';
            }

            const card = `
                <div class="entity-card" data-tipo="grado" data-nombre="${grado.nombre}" data-id="${grado.id}">
                    <div class="entity-card-header">
                        <div class="entity-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="entity-title">
                            <h4 class="entity-name">${grado.nombre}</h4>
                            <p class="entity-subtitle">${grado.nivel}</p>
                        </div>
                    </div>
                    <div class="entity-stats">
                        <div class="entity-stat">
                            <div class="entity-stat-value">${grado.materias.size}</div>
                            <div class="entity-stat-label">Materias</div>
                        </div>
                        <div class="entity-stat">
                            <div class="entity-stat-value">${grado.totalHoras}</div>
                            <div class="entity-stat-label">Horas/Semana</div>
                        </div>
                    </div>
                    <div class="entity-footer">
                        <span class="entity-badge ${badgeClass}">
                            <i class="fas fa-circle"></i>
                            ${badgeText}
                        </span>
                        <span class="entity-action">
                            Ver detalle
                            <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </div>
            `;
            contenedor.append(card);
        });

        $('.entity-card[data-tipo="grado"]').on('click', function() {
            const gradoId = $(this).data('id');
            mostrarDetalleGrado(gradoId);
        });
    }

    // ========================================
    // MODALES DE DETALLE
    // ========================================
    
    function mostrarDetalleProfesor(profesorId) {
        modalActivo = 'profesor';
        const asignaciones = asignacionesGlobales.filter(a => a.profesor_id == profesorId);
        if (asignaciones.length === 0) return;

        const profesor = asignaciones[0].profesor.name;
        const totalHoras = asignaciones.reduce((sum, a) => sum + a.horas_semanales, 0);
        const grados = new Set(asignaciones.map(a => a.grado_id)).size;
        const materias = new Set(asignaciones.map(a => a.asignatura_id)).size;

        $('#tituloModalProfesor').text(profesor);
        $('#modalProfesorAsignaciones').text(asignaciones.length);
        $('#modalProfesorHoras').text(totalHoras);
        $('#modalProfesorGrados').text(grados);
        $('#modalProfesorMaterias').text(materias);

        const tbody = $('#tablaDetalleProfesor');
        tbody.empty();

        asignaciones.forEach(asig => {
            let estadoBadge = '<span class="badge bg-secondary">Pendiente</span>';
            if (asig.estado === 'completo') {
                estadoBadge = '<span class="badge bg-success">Completo</span>';
            } else if (asig.estado === 'parcial') {
                estadoBadge = '<span class="badge bg-warning text-dark">Parcial</span>';
            }

            const row = `
                <tr>
                    <td>${asig.asignatura.nombre}</td>
                    <td>${asig.grado.nombre}</td>
                    <td class="text-center"><strong>${asig.horas_semanales}</strong></td>
                    <td class="text-center"><strong>${asig.horas_asignadas_count || 0}</strong></td>
                    <td class="text-center">${estadoBadge}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-info" onclick="abrirPreferencias(${asig.id}, 'profesor')" title="Preferencias">
                            <i class="fas fa-sliders-h"></i>
                        </button>
                        <button class="btn btn-sm btn-warning" onclick="editarAsignacion(${asig.id}, 'profesor')" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarAsignacion(${asig.id})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });

        $('#vistaDetalleProfesor').show();
        $('#vistaEdicionProfesor').hide();
        $('#vistaPreferenciasProfesor').hide();
        $('#botonesDetalleProfesor').show();
        $('#botonesEdicionProfesor').hide();
        $('#botonesPreferenciasProfesor').hide();

        $('#modalProfesor').modal('show');
    }

    function mostrarDetalleMateria(materiaId) {
        modalActivo = 'materia';
        const asignaciones = asignacionesGlobales.filter(a => a.asignatura_id == materiaId);
        if (asignaciones.length === 0) return;

        const materia = asignaciones[0].asignatura.nombre;
        const profesores = new Set(asignaciones.map(a => a.profesor_id)).size;
        const grados = new Set(asignaciones.map(a => a.grado_id)).size;
        const totalHoras = asignaciones.reduce((sum, a) => sum + a.horas_semanales, 0);

        $('#tituloModalMateria').text(materia);
        $('#modalMateriaAsignaciones').text(asignaciones.length);
        $('#modalMateriaProfesores').text(profesores);
        $('#modalMateriaGrados').text(grados);
        $('#modalMateriaHoras').text(totalHoras);

        const tbody = $('#tablaDetalleMateria');
        tbody.empty();

        asignaciones.forEach(asig => {
            let estadoBadge = '<span class="badge bg-secondary">Pendiente</span>';
            if (asig.estado === 'completo') {
                estadoBadge = '<span class="badge bg-success">Completo</span>';
            } else if (asig.estado === 'parcial') {
                estadoBadge = '<span class="badge bg-warning text-dark">Parcial</span>';
            }

            const row = `
                <tr>
                    <td>${asig.profesor.name}</td>
                    <td>${asig.grado.nombre}</td>
                    <td class="text-center"><strong>${asig.horas_semanales}</strong></td>
                    <td class="text-center"><strong>${asig.horas_asignadas_count || 0}</strong></td>
                    <td class="text-center">${estadoBadge}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-info" onclick="abrirPreferencias(${asig.id}, 'materia')" title="Preferencias">
                            <i class="fas fa-sliders-h"></i>
                        </button>
                        <button class="btn btn-sm btn-warning" onclick="editarAsignacion(${asig.id}, 'materia')" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarAsignacion(${asig.id})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });

        $('#vistaDetalleMateria').show();
        $('#vistaEdicionMateria').hide();
        $('#vistaPreferenciasMateria').hide();
        $('#botonesDetalleMateria').show();
        $('#botonesEdicionMateria').hide();
        $('#botonesPreferenciasMateria').hide();

        $('#modalMateria').modal('show');
    }

    function mostrarDetalleGrado(gradoId) {
        modalActivo = 'grado';
        const asignaciones = asignacionesGlobales.filter(a => a.grado_id == gradoId);
        if (asignaciones.length === 0) return;

        const grado = asignaciones[0].grado.nombre + ' - ' + asignaciones[0].grado.nivel.nombre;
        const materias = new Set(asignaciones.map(a => a.asignatura_id)).size;
        const profesores = new Set(asignaciones.map(a => a.profesor_id)).size;
        const totalHoras = asignaciones.reduce((sum, a) => sum + a.horas_semanales, 0);

        $('#tituloModalGrado').text(grado);
        $('#modalGradoAsignaciones').text(asignaciones.length);
        $('#modalGradoMaterias').text(materias);
        $('#modalGradoProfesores').text(profesores);
        $('#modalGradoHoras').text(totalHoras);

        const tbody = $('#tablaDetalleGrado');
        tbody.empty();

        asignaciones.forEach(asig => {
            let estadoBadge = '<span class="badge bg-secondary">Pendiente</span>';
            if (asig.estado === 'completo') {
                estadoBadge = '<span class="badge bg-success">Completo</span>';
            } else if (asig.estado === 'parcial') {
                estadoBadge = '<span class="badge bg-warning text-dark">Parcial</span>';
            }

            const row = `
                <tr>
                    <td>${asig.asignatura.nombre}</td>
                    <td>${asig.profesor.name}</td>
                    <td class="text-center"><strong>${asig.horas_semanales}</strong></td>
                    <td class="text-center"><strong>${asig.horas_asignadas_count || 0}</strong></td>
                    <td class="text-center">${estadoBadge}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-info" onclick="abrirPreferencias(${asig.id}, 'grado')" title="Preferencias">
                            <i class="fas fa-sliders-h"></i>
                        </button>
                        <button class="btn btn-sm btn-warning" onclick="editarAsignacion(${asig.id}, 'grado')" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarAsignacion(${asig.id})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });

        $('#vistaDetalleGrado').show();
        $('#vistaEdicionGrado').hide();
        $('#vistaPreferenciasGrado').hide();
        $('#botonesDetalleGrado').show();
        $('#botonesEdicionGrado').hide();
        $('#botonesPreferenciasGrado').hide();

        $('#modalGrado').modal('show');
    }

    // ========================================
    // CAMBIAR A VISTA PREFERENCIAS
    // ========================================
    
    window.abrirPreferencias = function(id, tipo) {
        const asignacion = asignacionesGlobales.find(a => a.id === id);
        if (!asignacion) return;

        if (tipo === 'profesor') {
            $('#prefId').val(asignacion.id);
            $('#prefProfesor').text(asignacion.profesor.name);
            $('#prefAsignatura').text(asignacion.asignatura.nombre);
            $('#prefGrado').text(`${asignacion.grado.nivel.nombre} - ${asignacion.grado.nombre}`);
            $('#prefPosicionJornada').val(asignacion.posicion_jornada || 'sin_restriccion');
            $('#prefMaxHorasPorDia').val(asignacion.max_horas_por_dia || '');
            $('#prefMaxDiasSemana').val(asignacion.max_dias_semana || '');

            $('#vistaDetalleProfesor').hide();
            $('#vistaEdicionProfesor').hide();
            $('#vistaPreferenciasProfesor').show();
            $('#botonesDetalleProfesor').hide();
            $('#botonesEdicionProfesor').hide();
            $('#botonesPreferenciasProfesor').show();
        } else if (tipo === 'materia') {
            $('#prefIdMateria').val(asignacion.id);
            $('#prefProfesorMateria').text(asignacion.profesor.name);
            $('#prefAsignaturaMateria').text(asignacion.asignatura.nombre);
            $('#prefGradoMateria').text(`${asignacion.grado.nivel.nombre} - ${asignacion.grado.nombre}`);
            $('#prefPosicionJornadaMateria').val(asignacion.posicion_jornada || 'sin_restriccion');
            $('#prefMaxHorasPorDiaMateria').val(asignacion.max_horas_por_dia || '');
            $('#prefMaxDiasSemanaMateria').val(asignacion.max_dias_semana || '');

            $('#vistaDetalleMateria').hide();
            $('#vistaEdicionMateria').hide();
            $('#vistaPreferenciasMateria').show();
            $('#botonesDetalleMateria').hide();
            $('#botonesEdicionMateria').hide();
            $('#botonesPreferenciasMateria').show();
        } else if (tipo === 'grado') {
            $('#prefIdGrado').val(asignacion.id);
            $('#prefProfesorGrado').text(asignacion.profesor.name);
            $('#prefAsignaturaGrado').text(asignacion.asignatura.nombre);
            $('#prefGradoGrado').text(`${asignacion.grado.nivel.nombre} - ${asignacion.grado.nombre}`);
            $('#prefPosicionJornadaGrado').val(asignacion.posicion_jornada || 'sin_restriccion');
            $('#prefMaxHorasPorDiaGrado').val(asignacion.max_horas_por_dia || '');
            $('#prefMaxDiasSemanaGrado').val(asignacion.max_dias_semana || '');

            $('#vistaDetalleGrado').hide();
            $('#vistaEdicionGrado').hide();
            $('#vistaPreferenciasGrado').show();
            $('#botonesDetalleGrado').hide();
            $('#botonesEdicionGrado').hide();
            $('#botonesPreferenciasGrado').show();
        }
    };

    // ========================================
    // CAMBIAR A VISTA EDICIÓN
    // ========================================
    
    window.editarAsignacion = function(id, tipo) {
        const asignacion = asignacionesGlobales.find(a => a.id === id);
        if (!asignacion) return;

        if (tipo === 'profesor') {
            $('#editId').val(asignacion.id);
            $('#editProfesor').text(asignacion.profesor.name);
            $('#editAsignatura').text(asignacion.asignatura.nombre);
            $('#editGrado').text(`${asignacion.grado.nivel.nombre} - ${asignacion.grado.nombre}`);
            $('#editHoras').val(asignacion.horas_semanales);

            $('#vistaDetalleProfesor').hide();
            $('#vistaEdicionProfesor').show();
            $('#vistaPreferenciasProfesor').hide();
            $('#botonesDetalleProfesor').hide();
            $('#botonesEdicionProfesor').show();
            $('#botonesPreferenciasProfesor').hide();
        } else if (tipo === 'materia') {
            $('#editIdMateria').val(asignacion.id);
            $('#editProfesorMateria').text(asignacion.profesor.name);
            $('#editAsignaturaMateria').text(asignacion.asignatura.nombre);
            $('#editGradoMateria').text(`${asignacion.grado.nivel.nombre} - ${asignacion.grado.nombre}`);
            $('#editHorasMateria').val(asignacion.horas_semanales);

            $('#vistaDetalleMateria').hide();
            $('#vistaEdicionMateria').show();
            $('#vistaPreferenciasMateria').hide();
            $('#botonesDetalleMateria').hide();
            $('#botonesEdicionMateria').show();
            $('#botonesPreferenciasMateria').hide();
        } else if (tipo === 'grado') {
            $('#editIdGrado').val(asignacion.id);
            $('#editProfesorGrado').text(asignacion.profesor.name);
            $('#editAsignaturaGrado').text(asignacion.asignatura.nombre);
            $('#editGradoGrado').text(`${asignacion.grado.nivel.nombre} - ${asignacion.grado.nombre}`);
            $('#editHorasGrado').val(asignacion.horas_semanales);

            $('#vistaDetalleGrado').hide();
            $('#vistaEdicionGrado').show();
            $('#vistaPreferenciasGrado').hide();
            $('#botonesDetalleGrado').hide();
            $('#botonesEdicionGrado').show();
            $('#botonesPreferenciasGrado').hide();
        }
    };

    // ========================================
    // VOLVER A VISTA DETALLE
    // ========================================
    
    window.volverADetalle = function(tipo) {
        if (tipo === 'profesor') {
            $('#vistaEdicionProfesor').hide();
            $('#vistaPreferenciasProfesor').hide();
            $('#vistaDetalleProfesor').show();
            $('#botonesEdicionProfesor').hide();
            $('#botonesPreferenciasProfesor').hide();
            $('#botonesDetalleProfesor').show();
        } else if (tipo === 'materia') {
            $('#vistaEdicionMateria').hide();
            $('#vistaPreferenciasMateria').hide();
            $('#vistaDetalleMateria').show();
            $('#botonesEdicionMateria').hide();
            $('#botonesPreferenciasMateria').hide();
            $('#botonesDetalleMateria').show();
        } else if (tipo === 'grado') {
            $('#vistaEdicionGrado').hide();
            $('#vistaPreferenciasGrado').hide();
            $('#vistaDetalleGrado').show();
            $('#botonesEdicionGrado').hide();
            $('#botonesPreferenciasGrado').hide();
            $('#botonesDetalleGrado').show();
        }
    };

    // ========================================
    // GUARDAR EDICIÓN (SOLO HORAS)
    // ========================================
    
    window.guardarEdicion = function(tipo) {
        let id, horas;

        if (tipo === 'profesor') {
            id = $('#editId').val();
            horas = $('#editHoras').val();
        } else if (tipo === 'materia') {
            id = $('#editIdMateria').val();
            horas = $('#editHorasMateria').val();
        } else if (tipo === 'grado') {
            id = $('#editIdGrado').val();
            horas = $('#editHorasGrado').val();
        }

        const asignacion = asignacionesGlobales.find(a => a.id == id);

        $.ajax({
            url: `/asignaciones/${id}`,
            method: 'PUT',
            data: {
                horas_semanales: horas,
                posicion_jornada: asignacion.posicion_jornada,
                max_horas_por_dia: asignacion.max_horas_por_dia,
                max_dias_semana: asignacion.max_dias_semana,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $(`#modal${tipo.charAt(0).toUpperCase() + tipo.slice(1)}`).modal('hide');
                    mostrarExito('Asignación actualizada correctamente');
                    cargarDatosConsulta();
                } else {
                    mostrarError(response.message);
                }
            },
            error: function(xhr) {
                mostrarError('Error al actualizar la asignación');
            }
        });
    };

    // ========================================
    // GUARDAR PREFERENCIAS
    // ========================================
    
    window.guardarPreferencias = function(tipo) {
        let id, posicionJornada, maxHorasPorDia, maxDiasSemana;

        if (tipo === 'profesor') {
            id = $('#prefId').val();
            posicionJornada = $('#prefPosicionJornada').val();
            maxHorasPorDia = $('#prefMaxHorasPorDia').val();
            maxDiasSemana = $('#prefMaxDiasSemana').val();
        } else if (tipo === 'materia') {
            id = $('#prefIdMateria').val();
            posicionJornada = $('#prefPosicionJornadaMateria').val();
            maxHorasPorDia = $('#prefMaxHorasPorDiaMateria').val();
            maxDiasSemana = $('#prefMaxDiasSemanaMateria').val();
        } else if (tipo === 'grado') {
            id = $('#prefIdGrado').val();
            posicionJornada = $('#prefPosicionJornadaGrado').val();
            maxHorasPorDia = $('#prefMaxHorasPorDiaGrado').val();
            maxDiasSemana = $('#prefMaxDiasSemanaGrado').val();
        }

        const asignacion = asignacionesGlobales.find(a => a.id == id);

        $.ajax({
            url: `/asignaciones/${id}`,
            method: 'PUT',
            data: {
                horas_semanales: asignacion.horas_semanales,
                posicion_jornada: posicionJornada,
                max_horas_por_dia: maxHorasPorDia || null,
                max_dias_semana: maxDiasSemana || null,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    volverADetalle(tipo);
                    mostrarExito('Preferencias actualizadas correctamente');
                    cargarDatosConsulta();
                } else {
                    mostrarError(response.message);
                }
            },
            error: function(xhr) {
                mostrarError('Error al actualizar las preferencias');
            }
        });
    };

    // ========================================
    // ELIMINAR ASIGNACIÓN (SweetAlert2)
    // ========================================
    
    window.eliminarAsignacion = function(id) {
        const asignacion = asignacionesGlobales.find(a => a.id === id);
        if (!asignacion) return;

        Swal.fire({
            title: '¿Confirmar eliminación?',
            html: `
                <div style="text-align: left; padding: 1rem; background: #f8f9fa; border-radius: 6px; margin-top: 1rem;">
                    <p style="margin: 0 0 0.75rem 0;"><strong>Profesor:</strong> ${asignacion.profesor.name}</p>
                    <p style="margin: 0 0 0.75rem 0;"><strong>Asignatura:</strong> ${asignacion.asignatura.nombre}</p>
                    <p style="margin: 0;"><strong>Grado:</strong> ${asignacion.grado.nombre}</p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/asignaciones/${id}`,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            $('#modalProfesor, #modalMateria, #modalGrado').modal('hide');
                            mostrarExito('Asignación eliminada correctamente');
                            cargarDatosConsulta();
                        } else {
                            mostrarError(response.message);
                        }
                    },
                    error: function(xhr) {
                        mostrarError('Error al eliminar la asignación');
                    }
                });
            }
        });
    };

    // ========================================
    // UTILIDADES
    // ========================================
    
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
                    $select.html('<option value="">Seleccione un grado</option>');
                    response.grados.forEach(grado => {
                        $select.append(`<option value="${grado.id}">${grado.nombre}</option>`);
                    });
                }
            },
            error: function(xhr) {
                $select.html('<option value="">Error al cargar</option>');
            }
        });
    }

    function mostrarExito(mensaje) {
        Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: mensaje,
            confirmButtonColor: '#28a745'
        });
    }

    function mostrarError(mensaje) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: mensaje,
            confirmButtonColor: '#dc3545'
        });
    }

    function mostrarAdvertencia(mensaje) {
        Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: mensaje,
            confirmButtonColor: '#ffc107'
        });
    }
});
</script>
@endsection
