@extends('layouts.master')

@section('content')
    <div class="container-fluid py-4">
        <!-- ========================================
             HEADER
        ======================================== -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="header-final">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h1 class="titulo-final">
                                <i class="bi bi-calendar-week me-2"></i>
                                Gestión de Horarios Académicos
                            </h1>
                            <p class="subtitulo-final">Sistema de consulta y edición manual de horarios por nivel educativo</p>
                        </div>
                        <a href="{{ route('horarios.index') }}" class="btn-final btn-azul-final">
                            <i class="bi bi-plus-lg me-2"></i>Nuevo Horario
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================
             FILTROS DE BÚSQUEDA
        ======================================== -->
        <div class="card card-final shadow-sm mb-4">
            <div class="card-header-final">
                <h6 class="mb-0">
                    <i class="bi bi-funnel-fill me-2"></i>Filtros de Búsqueda
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <!-- Nivel Educativo -->
                    <div class="col-md-5">
                        <label class="label-final">
                            <i class="bi bi-layers-fill me-1"></i>Nivel Educativo
                        </label>
                        <select class="select-final" id="filterNivel">
                            <option value="">Seleccionar nivel</option>
                            @foreach ($niveles as $nivel)
                                <option value="{{ $nivel->id }}">{{ $nivel->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Año Lectivo -->
                    <div class="col-md-3">
                        <label class="label-final">
                            <i class="bi bi-calendar3 me-1"></i>Año Lectivo
                        </label>
                        <select class="select-final" id="filterYear">
                            @foreach ($years as $yr)
                                <option value="{{ $yr }}" {{ $yr == date('Y') ? 'selected' : '' }}>
                                    {{ $yr }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Botón Buscar -->
                    <div class="col-md-4">
                        <button type="button" class="btn-final btn-azul-final w-100" id="btnBuscar">
                            <i class="bi bi-search me-2"></i>Buscar Horarios
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================
             CONTENEDOR DE HORARIOS
        ======================================== -->
        <div id="horariosContainer">
            <div class="estado-vacio-final" id="emptyState">
                <i class="bi bi-calendar-week icono-vacio-final"></i>
                <h5 class="titulo-vacio-final">Selecciona un nivel y año para ver los horarios</h5>
                <p class="texto-vacio-final">Los horarios aparecerán organizados por grado</p>
                <p class="texto-vacio-final text-muted mt-2">
                    <i class="bi bi-info-circle me-1"></i>
                    Podrás editar, crear y eliminar clases haciendo clic en las celdas
                </p>
            </div>
        </div>
    </div>

    <!-- ========================================
         MODAL DE EDICIÓN/CREACIÓN DE CLASES
    ======================================== -->
    <div class="modal fade" id="modalEditarClase" tabindex="-1" aria-labelledby="modalTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl-final">
            <div class="modal-content modal-final">
                <!-- Header del Modal -->
                <div class="modal-header-final">
                    <div>
                        <h5 class="modal-titulo-final">
                            <span id="modalTitulo">Editar Asignación</span>
                        </h5>
                        <p class="modal-subtitulo-final" id="editInfoTexto"></p>
                    </div>
                    <button type="button" class="btn-close-final" data-bs-dismiss="modal" aria-label="Cerrar">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <!-- Body del Modal -->
                <div class="modal-body-final">
                    <!-- Campos ocultos -->
                    <input type="hidden" id="editGradoId">
                    <input type="hidden" id="editNivelId">
                    <input type="hidden" id="editYear">
                    <input type="hidden" id="editDia">
                    <input type="hidden" id="editHora">

                    <!-- Formulario -->
                    <div class="row g-4">
                        <!-- Asignatura -->
                        <div class="col-md-6">
                            <div class="form-group-final">
                                <label class="label-modal-final">
                                    <i class="bi bi-book-fill me-2"></i>Asignatura
                                </label>
                                <select class="select-modal-final" id="editAsignatura" required>
                                    <option value="">Seleccionar asignatura</option>
                                </select>
                                <small class="ayuda-texto">
                                    Al seleccionar una asignatura se cargarán los profesores disponibles
                                </small>
                            </div>
                        </div>

                        <!-- Profesor -->
                        <div class="col-md-6">
                            <div class="form-group-final">
                                <label class="label-modal-final">
                                    <i class="bi bi-person-fill me-2"></i>Profesor Asignado
                                </label>
                                <select class="select-modal-final" id="editProfesor" required disabled>
                                    <option value="">Primero seleccione una asignatura</option>
                                </select>
                                <small class="ayuda-texto">
                                    Elige el docente que impartirá esta clase
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Alerta de advertencia -->
                    <div class="alerta-final alerta-warning-final d-none" id="editWarning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <span id="editWarningText"></span>
                    </div>
                </div>

                <!-- Footer del Modal -->
                <div class="modal-footer-final">
                    <button type="button" class="btn-modal-final btn-modal-cancelar" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn-modal-final btn-modal-eliminar" id="btnEliminarClase">
                        <i class="bi bi-trash-fill me-2"></i>Eliminar
                    </button>
                    <button type="button" class="btn-modal-final btn-modal-guardar" id="btnGuardarEdicion">
                        <i class="bi bi-check-circle-fill me-2"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Estilos CSS -->
    <link href="{{ asset('css/horariolist.css') }}" rel="stylesheet">
    
    <!-- SweetAlert2 para alertas bonitas -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Script principal -->
    <script src="{{ asset('js/horario-listar.js') }}"></script>
    
    <!-- Script inline para debugging (opcional - remover en producción) -->
    <script>
        console.log('🎯 Vista de horarios cargada correctamente');
        console.log('📊 Niveles disponibles:', {{ $niveles->count() }});
        console.log('📅 Años disponibles:', {{ count($years) }});
    </script>
@endsection

{{-- 
========================================
NOTAS IMPORTANTES
========================================

1. ASSETS REQUERIDOS:
   - public/css/horariolist.css (ya existe)
   - public/js/horario-listar.js (reemplazar con horario-listar-completo.js)

2. VARIABLES DE BLADE:
   - $niveles: Collection de niveles educativos
   - $years: Array de años lectivos

3. FUNCIONALIDADES:
   - Edición manual de clases
   - Creación de nuevas clases
   - Eliminación de clases
   - Validación de conflictos
   - Descarga de PDFs sin pantalla en blanco

4. COMPATIBILIDAD:
   - Bootstrap 5.x
   - jQuery 3.x
   - SweetAlert2 11.x
   - Bootstrap Icons 1.x

5. SEGURIDAD:
   - CSRF token configurado automáticamente en JS
   - Validación en backend
   - Verificación de permisos (agregar middleware si es necesario)
--}}