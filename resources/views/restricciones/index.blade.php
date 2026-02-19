@extends('layouts.master')

@section('title', 'Restricciones de Profesores')

@section('content')
<div class="container-fluid py-4" x-data="restriccionesApp" x-init="init()">
    
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                
                                Restricciones de Profesores
                            </h2>
                            <p class="text-muted mb-0">
                                Configure las horas donde los profesores NO pueden dar clases
                            </p>
                        </div>
                        <button class="btn btn-primary" @click="abrirModalCrear()">
                            <i class="fas fa-plus"></i> Nueva Restricción
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="fas fa-calendar"></i> Año Académico
                            </label>
                            <select class="form-select" x-model="filtros.year" @change="cargarRestricciones()">
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="fas fa-user-tie"></i> Profesor
                            </label>
                            <select class="form-select" x-model="filtros.profesor_id" @change="cargarRestricciones()">
                                <option value="">Todos los profesores</option>
                                @foreach($profesores as $profesor)
                                    <option value="{{ $profesor->id }}">{{ $profesor->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="fas fa-filter"></i> Estado
                            </label>
                            <select class="form-select" x-model="filtros.activa" @change="cargarRestricciones()">
                                <option value="">Todas</option>
                                <option value="true">Solo Activas</option>
                                <option value="false">Solo Inactivas</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Restricciones -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    
                    <!-- Loading -->
                    <div x-show="loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando restricciones...</p>
                    </div>

                    <!-- Sin Restricciones -->
                    <div x-show="!loading && restricciones.length === 0" class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No hay restricciones registradas</p>
                        <button class="btn btn-primary" @click="abrirModalCrear()">
                            <i class="fas fa-plus"></i> Crear Primera Restricción
                        </button>
                    </div>

                    <!-- Tabla -->
                    <div x-show="!loading && restricciones.length > 0" class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Profesor</th>
                                    <th>Tipo</th>
                                    <th>Día(s)</th>
                                    <th>Hora/Rango</th>
                                    <th>Motivo</th>
                                    <th>Año</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="restriccion in restricciones" :key="restriccion.id">
                                    <tr :class="{'table-secondary': !restriccion.activa}">
                                        <td>
                                            <strong x-text="restriccion.profesor.name"></strong>
                                        </td>
                                        <td>
                                            <span class="badge" 
                                                  :class="getBadgeTipo(restriccion)"
                                                  x-text="getTipoTexto(restriccion)">
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info" x-text="getDiaTexto(restriccion)"></span>
                                        </td>
                                        <td>
                                            <span x-html="getHoraDisplay(restriccion)"></span>
                                        </td>
                                        <td>
                                            <span x-text="restriccion.motivo || '-'" class="text-muted"></span>
                                        </td>
                                        <td>
                                            <span x-text="restriccion.year"></span>
                                        </td>
                                        <td>
                                            <span class="badge" 
                                                  :class="restriccion.activa ? 'bg-success' : 'bg-secondary'"
                                                  x-text="restriccion.activa ? 'Activa' : 'Inactiva'">
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-warning" 
                                                        @click="toggleActiva(restriccion)"
                                                        :title="restriccion.activa ? 'Desactivar' : 'Activar'">
                                                    <i class="fas" 
                                                       :class="restriccion.activa ? 'fa-toggle-on' : 'fa-toggle-off'">
                                                    </i>
                                                </button>
                                                <button class="btn btn-outline-primary" 
                                                        @click="editarRestriccion(restriccion)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" 
                                                        @click="eliminarRestriccion(restriccion)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Resumen -->
                    <div x-show="!loading && restricciones.length > 0" class="mt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">
                                Total: <strong x-text="restricciones.length"></strong> restricciones
                            </span>
                            <span class="text-muted">
                                Activas: <strong x-text="restriccionesActivas"></strong> | 
                                Inactivas: <strong x-text="restriccionesInactivas"></strong>
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal Crear/Editar -->
    <div class="modal fade" id="modalRestriccion" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-ban"></i>
                        <span x-text="modoEdicion ? 'Editar Restricción' : 'Nueva Restricción'"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <form @submit.prevent="guardarRestriccion()">
                    <div class="modal-body">
                        
                        <!-- Información del Profesor y Año -->
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label class="form-label required">
                                    <i class="fas fa-user-tie"></i> Profesor
                                </label>
                                <select class="form-select" x-model="form.profesor_id" required>
                                    <option value="">Seleccione un profesor</option>
                                    @foreach($profesores as $profesor)
                                        <option value="{{ $profesor->id }}">{{ $profesor->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label required">
                                    <i class="fas fa-calendar"></i> Año
                                </label>
                                <select class="form-select" x-model="form.year" required>
                                    @foreach($years as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Tipo de Restricción -->
                        <div class="mb-4">
                            <label class="form-label required">
                                <i class="fas fa-list"></i> Tipo de Restricción
                            </label>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <div class="form-check form-check-inline w-100">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="tipoRestriccion" 
                                               id="tipoHoraEspecifica"
                                               value="hora_especifica" 
                                               x-model="form.tipo_restriccion"
                                               @change="limpiarCamposSegunTipo()">
                                        <label class="form-check-label w-100" for="tipoHoraEspecifica">
                                            <div class="card text-center p-2" 
                                                 :class="form.tipo_restriccion === 'hora_especifica' ? 'border-primary' : ''">
                                                <i class="fas fa-clock fa-2x text-primary mb-2"></i>
                                                <strong>Hora Específica</strong>
                                                <small class="text-muted">Bloquear una hora (ej: hora 3)</small>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-inline w-100">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="tipoRestriccion" 
                                               id="tipoRangoHorario"
                                               value="rango_horario" 
                                               x-model="form.tipo_restriccion"
                                               @change="limpiarCamposSegunTipo()">
                                        <label class="form-check-label w-100" for="tipoRangoHorario">
                                            <div class="card text-center p-2"
                                                 :class="form.tipo_restriccion === 'rango_horario' ? 'border-warning' : ''">
                                                <i class="fas fa-hourglass-half fa-2x text-warning mb-2"></i>
                                                <strong>Rango Horario</strong>
                                                <small class="text-muted">Bloquear rango (ej: 10:00-11:30)</small>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-inline w-100">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="tipoRestriccion" 
                                               id="tipoDiaCompleto"
                                               value="dia_completo" 
                                               x-model="form.tipo_restriccion"
                                               @change="limpiarCamposSegunTipo()">
                                        <label class="form-check-label w-100" for="tipoDiaCompleto">
                                            <div class="card text-center p-2"
                                                 :class="form.tipo_restriccion === 'dia_completo' ? 'border-danger' : ''">
                                                <i class="fas fa-calendar-times fa-2x text-danger mb-2"></i>
                                                <strong>Día Completo</strong>
                                                <small class="text-muted">Bloquear todo el día</small>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Día de la semana -->
                        <div class="mb-3">
                            <label class="form-label" 
                                   :class="form.tipo_restriccion === 'dia_completo' ? 'required' : ''">
                                <i class="fas fa-calendar-day"></i> Día(s) de la Semana
                            </label>
                            <select class="form-select" 
                                    x-model="form.dia_semana"
                                    :required="form.tipo_restriccion === 'dia_completo'">
                                <option value="">Todos los días de la semana</option>
                                <option value="Lunes">Lunes</option>
                                <option value="Martes">Martes</option>
                                <option value="Miércoles">Miércoles</option>
                                <option value="Jueves">Jueves</option>
                                <option value="Viernes">Viernes</option>
                                <option value="Sábado">Sábado</option>
                            </select>
                            <small class="text-muted">
                                <template x-if="form.tipo_restriccion === 'dia_completo'">
                                    <span class="text-danger">⚠️ Requerido para bloqueo de día completo</span>
                                </template>
                                <template x-if="form.tipo_restriccion !== 'dia_completo'">
                                    <span>💡 Dejar en blanco para aplicar a <strong>TODOS</strong> los días (Lunes a Sábado)</span>
                                </template>
                            </small>
                        </div>

                        <!-- Campos específicos según tipo -->
                        
                        <!-- Hora Específica -->
                        <div x-show="form.tipo_restriccion === 'hora_especifica'" x-transition>
                            <div class="mb-3">
                                <label class="form-label required">
                                    <i class="fas fa-clock"></i> Número de Hora
                                </label>
                                <select class="form-select" 
                                        x-model="form.hora_numero"
                                        :required="form.tipo_restriccion === 'hora_especifica'">
                                    <option value="">Seleccione la hora</option>
                                    <option value="1">Hora 1</option>
                                    <option value="2">Hora 2</option>
                                    <option value="3">Hora 3</option>
                                    <option value="4">Hora 4</option>
                                    <option value="5">Hora 5</option>
                                    <option value="6">Hora 6</option>
                                    <option value="7">Hora 7</option>
                                    <option value="8">Hora 8</option>
                                    <option value="9">Hora 9</option>
                                    <option value="10">Hora 10</option>
                                    <option value="11">Hora 11</option>
                                    <option value="12">Hora 12</option>
                                </select>
                                <small class="text-muted">Seleccione el número de hora a bloquear</small>
                            </div>
                        </div>

                        <!-- Rango Horario -->
                        <div x-show="form.tipo_restriccion === 'rango_horario'" x-transition>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">
                                        <i class="fas fa-clock"></i> Hora Inicio
                                    </label>
                                    <input type="time" 
                                           class="form-control" 
                                           x-model="form.hora_inicio"
                                           :required="form.tipo_restriccion === 'rango_horario'"
                                           step="300">
                                    <small class="text-muted">Hora de inicio del bloqueo</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">
                                        <i class="fas fa-clock"></i> Hora Fin
                                    </label>
                                    <input type="time" 
                                           class="form-control" 
                                           x-model="form.hora_fin"
                                           :required="form.tipo_restriccion === 'rango_horario'"
                                           step="300">
                                    <small class="text-muted">Hora de fin del bloqueo</small>
                                </div>
                            </div>
                            <div x-show="validarHorasRango()" class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                La hora de fin debe ser posterior a la hora de inicio
                            </div>
                        </div>

                        <!-- Día Completo -->
                        <div x-show="form.tipo_restriccion === 'dia_completo'" x-transition>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                Se bloqueará <strong>todo el día</strong>
                                <span x-show="form.dia_semana">
                                    <strong x-text="form.dia_semana"></strong>
                                </span>
                                <span x-show="!form.dia_semana">
                                    <strong>TODOS LOS DÍAS</strong> (Lunes a Sábado)
                                </span>
                                para este profesor.
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Motivo -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-comment"></i> Motivo (Opcional)
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   x-model="form.motivo"
                                   maxlength="100"
                                   placeholder="Ej: Reunión administrativa, Almuerzo, Gestión personal, etc.">
                            <small class="text-muted">Razón del bloqueo (máximo 100 caracteres)</small>
                        </div>

                        <!-- Estado -->
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       x-model="form.activa"
                                       id="checkActiva">
                                <label class="form-check-label" for="checkActiva">
                                    <strong>Restricción activa</strong>
                                    <small class="d-block text-muted">
                                        Las restricciones inactivas no se aplicarán en la generación de horarios
                                    </small>
                                </label>
                            </div>
                        </div>

                        <!-- Vista Previa -->
                        <div class="alert" 
                             :class="form.activa ? 'alert-success' : 'alert-warning'">
                            <h6 class="alert-heading">
                                <i class="fas fa-eye"></i> Vista Previa de la Restricción
                            </h6>
                            <hr>
                            <div x-html="vistaPrevia()"></div>
                        </div>

                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" 
                                class="btn btn-primary" 
                                :disabled="guardando || !formularioValido()">
                            <span x-show="!guardando">
                                <i class="fas fa-save"></i>
                                <span x-text="modoEdicion ? 'Actualizar Restricción' : 'Guardar Restricción'"></span>
                            </span>
                            <span x-show="guardando">
                                <span class="spinner-border spinner-border-sm me-1"></span>
                                Guardando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<style>
.required::after {
    content: " *";
    color: red;
}

.form-check-input[type="radio"] {
    display: none;
}

.form-check-label .card {
    cursor: pointer;
    transition: all 0.3s;
}

.form-check-label .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>

@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('restriccionesApp', () => ({
        // Estado
        restricciones: [],
        loading: false,
        guardando: false,
        modoEdicion: false,
        
        // Filtros
        filtros: {
            year: {{ date('Y') }},
            profesor_id: '',
            activa: ''
        },
        
        // Formulario
        form: {
            id: null,
            profesor_id: '',
            dia_semana: '', // '' = TODOS los días
            tipo_restriccion: 'hora_especifica',
            hora_numero: '',
            hora_inicio: '',
            hora_fin: '',
            motivo: '',
            year: {{ date('Y') }},
            activa: true
        },
        
        modalInstance: null,

        init() {
            console.log('🚀 Sistema de Restricciones iniciado');
            this.cargarRestricciones();
            
            this.$nextTick(() => {
                const modalElement = document.getElementById('modalRestriccion');
                if (modalElement) {
                    this.modalInstance = new bootstrap.Modal(modalElement);
                    console.log('✅ Modal inicializado correctamente');
                }
            });
        },

        // Cargar restricciones
        async cargarRestricciones() {
            this.loading = true;
            console.log('📥 Cargando restricciones...');
            
            try {
                const params = new URLSearchParams();
                if (this.filtros.year) params.append('year', this.filtros.year);
                if (this.filtros.profesor_id) params.append('profesor_id', this.filtros.profesor_id);
                if (this.filtros.activa !== '') params.append('activa', this.filtros.activa);

                const response = await fetch(`/restricciones/listar?${params}`);
                const data = await response.json();
                
                if (data.success) {
                    this.restricciones = data.restricciones;
                    console.log(`✅ ${this.restricciones.length} restricciones cargadas`);
                } else {
                    this.mostrarError('Error al cargar restricciones');
                }
            } catch (error) {
                console.error('❌ Error:', error);
                this.mostrarError('Error de conexión: ' + error.message);
            } finally {
                this.loading = false;
            }
        },

        // Abrir modal crear
        abrirModalCrear() {
            console.log('➕ Abriendo modal para crear restricción');
            this.modoEdicion = false;
            this.resetForm();
            
            if (this.modalInstance) {
                this.modalInstance.show();
            }
        },

        // Editar restricción
        editarRestriccion(restriccion) {
            console.log('✏️ Editando restricción:', restriccion.id);
            this.modoEdicion = true;
            
            // Determinar tipo de restricción
            let tipo = 'dia_completo';
            if (restriccion.hora_numero) {
                tipo = 'hora_especifica';
            } else if (restriccion.hora_inicio && restriccion.hora_fin) {
                tipo = 'rango_horario';
            }

            this.form = {
                id: restriccion.id,
                profesor_id: restriccion.profesor_id,
                dia_semana: restriccion.dia_semana || '', // NULL se convierte en ''
                tipo_restriccion: tipo,
                hora_numero: restriccion.hora_numero || '',
                hora_inicio: restriccion.hora_inicio ? restriccion.hora_inicio.substring(0, 5) : '',
                hora_fin: restriccion.hora_fin ? restriccion.hora_fin.substring(0, 5) : '',
                motivo: restriccion.motivo || '',
                year: restriccion.year,
                activa: restriccion.activa
            };

            if (this.modalInstance) {
                this.modalInstance.show();
            }
        },

        // Guardar restricción
        async guardarRestriccion() {
            console.log('💾 Guardando restricción...', this.form);
            
            if (!this.formularioValido()) {
                this.mostrarError('Complete todos los campos requeridos correctamente');
                return;
            }

            this.guardando = true;

            try {
                const url = this.modoEdicion 
                    ? `/restricciones/${this.form.id}` 
                    : '/restricciones';
                
                const method = this.modoEdicion ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (data.success) {
                    this.mostrarExito(data.message);
                    if (this.modalInstance) {
                        this.modalInstance.hide();
                    }
                    this.cargarRestricciones();
                } else {
                    this.mostrarError(data.message || 'Error al guardar');
                }
            } catch (error) {
                console.error('❌ Error:', error);
                this.mostrarError('Error de conexión: ' + error.message);
            } finally {
                this.guardando = false;
            }
        },

        // Toggle activa/inactiva
        async toggleActiva(restriccion) {
            const accion = restriccion.activa ? 'desactivar' : 'activar';
            if (!confirm(`¿Está seguro de ${accion} esta restricción?`)) {
                return;
            }

            try {
                const response = await fetch(`/restricciones/${restriccion.id}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.mostrarExito(data.message);
                    this.cargarRestricciones();
                } else {
                    this.mostrarError(data.message);
                }
            } catch (error) {
                console.error('❌ Error:', error);
                this.mostrarError('Error de conexión');
            }
        },

        // Eliminar restricción
        async eliminarRestriccion(restriccion) {
            if (!confirm(`¿Eliminar restricción de ${restriccion.profesor.name}?\n\n${this.getDescripcionRestriccion(restriccion)}`)) {
                return;
            }

            try {
                const response = await fetch(`/restricciones/${restriccion.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.mostrarExito(data.message);
                    this.cargarRestricciones();
                } else {
                    this.mostrarError(data.message);
                }
            } catch (error) {
                console.error('❌ Error:', error);
                this.mostrarError('Error de conexión');
            }
        },

        // Limpiar campos según tipo
        limpiarCamposSegunTipo() {
            if (this.form.tipo_restriccion === 'hora_especifica') {
                this.form.hora_inicio = '';
                this.form.hora_fin = '';
            } else if (this.form.tipo_restriccion === 'rango_horario') {
                this.form.hora_numero = '';
            } else if (this.form.tipo_restriccion === 'dia_completo') {
                this.form.hora_numero = '';
                this.form.hora_inicio = '';
                this.form.hora_fin = '';
            }
        },

        // Validar que las horas del rango sean correctas
        validarHorasRango() {
            if (this.form.tipo_restriccion !== 'rango_horario') return false;
            if (!this.form.hora_inicio || !this.form.hora_fin) return false;
            return this.form.hora_inicio >= this.form.hora_fin;
        },

        // Validar formulario completo
        formularioValido() {
            // Campos base requeridos
            if (!this.form.profesor_id || !this.form.year || !this.form.tipo_restriccion) {
                return false;
            }

            // Validar según tipo
            if (this.form.tipo_restriccion === 'hora_especifica') {
                return this.form.hora_numero !== '';
            } else if (this.form.tipo_restriccion === 'rango_horario') {
                if (!this.form.hora_inicio || !this.form.hora_fin) return false;
                if (this.form.hora_inicio >= this.form.hora_fin) return false;
                return true;
            } else if (this.form.tipo_restriccion === 'dia_completo') {
                return this.form.dia_semana !== '';
            }

            return false;
        },

        // Vista previa
        vistaPrevia() {
            if (!this.form.profesor_id) {
                return '<p class="mb-0 text-muted">Seleccione un profesor para ver la vista previa</p>';
            }

            const profesores = @json($profesores);
            const profesor = profesores.find(p => p.id == this.form.profesor_id);
            
            let preview = `<p class="mb-2"><strong>Profesor:</strong> ${profesor ? profesor.name : 'No seleccionado'}</p>`;
            preview += `<p class="mb-2"><strong>Año:</strong> ${this.form.year}</p>`;
            
            // Día (importante la diferencia entre '' y valor específico)
            if (this.form.dia_semana === '') {
                preview += `<p class="mb-2"><strong>Día(s):</strong> <span class="badge bg-warning text-dark">TODOS LOS DÍAS</span> (Lunes a Sábado)</p>`;
            } else {
                preview += `<p class="mb-2"><strong>Día:</strong> ${this.form.dia_semana}</p>`;
            }
            
            if (this.form.tipo_restriccion === 'hora_especifica') {
                preview += `<p class="mb-2"><strong>Restricción:</strong> Hora específica ${this.form.hora_numero || '(no seleccionada)'}</p>`;
            } else if (this.form.tipo_restriccion === 'rango_horario') {
                preview += `<p class="mb-2"><strong>Restricción:</strong> Rango de ${this.form.hora_inicio || '??:??'} a ${this.form.hora_fin || '??:??'}</p>`;
            } else if (this.form.tipo_restriccion === 'dia_completo') {
                preview += `<p class="mb-2"><strong>Restricción:</strong> TODO EL DÍA completo</p>`;
            }
            
            if (this.form.motivo) {
                preview += `<p class="mb-2"><strong>Motivo:</strong> ${this.form.motivo}</p>`;
            }
            
            preview += `<p class="mb-0"><strong>Estado:</strong> <span class="badge ${this.form.activa ? 'bg-success' : 'bg-secondary'}">${this.form.activa ? 'Activa' : 'Inactiva'}</span></p>`;
            
            return preview;
        },

        // Reset formulario
        resetForm() {
            this.form = {
                id: null,
                profesor_id: '',
                dia_semana: '', // '' = TODOS los días
                tipo_restriccion: 'hora_especifica',
                hora_numero: '',
                hora_inicio: '',
                hora_fin: '',
                motivo: '',
                year: this.filtros.year,
                activa: true
            };
        },

        // Obtener texto del día para la tabla
        getDiaTexto(restriccion) {
            if (!restriccion.dia_semana || restriccion.dia_semana === '') {
                return 'TODOS LOS DÍAS';
            }
            return restriccion.dia_semana;
        },

        // Obtener tipo de restricción para mostrar
        getTipoTexto(restriccion) {
            if (restriccion.hora_numero) return 'Hora Específica';
            if (restriccion.hora_inicio && restriccion.hora_fin) return 'Rango Horario';
            return 'Día Completo';
        },

        // Obtener clase de badge según tipo
        getBadgeTipo(restriccion) {
            if (restriccion.hora_numero) return 'bg-primary';
            if (restriccion.hora_inicio && restriccion.hora_fin) return 'bg-warning text-dark';
            return 'bg-danger';
        },

        // Obtener display de hora
        getHoraDisplay(restriccion) {
            if (restriccion.hora_numero) {
                return `<span class="badge bg-primary">Hora ${restriccion.hora_numero}</span>`;
            }
            if (restriccion.hora_inicio && restriccion.hora_fin) {
                return `<span class="badge bg-warning text-dark">${restriccion.hora_inicio.substring(0,5)} - ${restriccion.hora_fin.substring(0,5)}</span>`;
            }
            return '<span class="badge bg-secondary">Todo el día</span>';
        },

        // Obtener descripción de restricción
        getDescripcionRestriccion(restriccion) {
            let desc = restriccion.dia_semana || 'TODOS LOS DÍAS';
            desc += ' - ';
            
            if (restriccion.hora_numero) {
                desc += `Hora ${restriccion.hora_numero}`;
            } else if (restriccion.hora_inicio && restriccion.hora_fin) {
                desc += `${restriccion.hora_inicio.substring(0,5)} - ${restriccion.hora_fin.substring(0,5)}`;
            } else {
                desc += 'Todo el día';
            }
            
            if (restriccion.motivo) {
                desc += ` (${restriccion.motivo})`;
            }
            
            return desc;
        },

        // Computed
        get restriccionesActivas() {
            return this.restricciones.filter(r => r.activa).length;
        },

        get restriccionesInactivas() {
            return this.restricciones.filter(r => !r.activa).length;
        },

        // Notificaciones mejoradas
        mostrarExito(mensaje) {
            alert('✅ ' + mensaje);
        },

        mostrarError(mensaje) {
            alert('❌ ' + mensaje);
        }
    }));
});
</script>
@endpush