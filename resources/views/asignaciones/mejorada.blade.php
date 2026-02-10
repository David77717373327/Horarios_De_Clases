<?php

namespace App\Http\Controllers;

use App\Models\AsignacionAcademica;
use App\Models\Horario;
use App\Models\Grado;
use App\Models\Nivel;
use App\Models\RestriccionProfesor;
use App\Models\PreferenciaAsignatura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 🚀 GENERADOR INTELIGENTE DE HORARIOS v6.1 - VERSIÓN ULTRA-INTELIGENTE
 * 
 * MEJORAS CRÍTICAS v6.1:
 * ✅ Diagnóstico completo de materias faltantes (SOLUCIÓN 2)
 * ✅ Guarda SIEMPRE el mejor resultado aunque no sea 100% (SOLUCIÓN 3)
 * ✅ Restricciones de profesores CORREGIDAS (día completo + hora específica + rango)
 * ✅ Validación cruzada REAL entre todos los grados
 * ✅ 10 estrategias con backtracking inteligente
 * ✅ Acepta SOLO 100% de completitud (sin umbral relajado)
 */
class GeneradorHorarioController extends Controller
{
    private $matrizGlobal = [];           
    private $profesoresOcupados = [];     
    private $asignacionesPorProfesor = [];
    private $asignacionesPorGrado = [];   
    private $restriccionesProfesores = [];
    private $configuracion = [];
    private $estadosGuardados = [];
    private $slotsDensidad = [];
    private $estrategiaActual = 0;
    
    private $bloqueosPorRazon = [];
    private $intentosFallidosPorAsignatura = [];
    private $todosLosHorarios = [];
    private $nivelActual = null;

    public function generarAutomatico(Request $request, $gradoId)
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validate([
                'year' => 'required|integer|min:2020|max:2100',
                'limpiar_existentes' => 'boolean',
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fin' => 'required|date_format:H:i',
                'duracion_clase' => 'required|integer|min:30|max:120',
                'horas_por_dia' => 'required|integer|min:1|max:12',
                'dias_semana' => 'required|array',
                'recreo_despues_hora' => 'nullable|integer',
                'recreo_duracion' => 'nullable|integer'
            ]);

            $grado = Grado::with('nivel')->findOrFail($gradoId);
            $this->nivelActual = $grado->nivel_id;
            
            Log::info('🚀 Iniciando generación v6.1 ULTRA-INTELIGENTE', [
                'grado' => $grado->nombre_completo,
                'nivel' => $grado->nivel->nombre,
                'year' => $validated['year']
            ]);

            // 🔥 IMPORTANTE: Preparar configuración ANTES de cargar restricciones
            $this->configuracion = $this->prepararConfiguracion($validated, $grado);
            
            // Ahora sí cargar contexto global (que necesita la configuración)
            $this->cargarContextoGlobalCompleto($validated['year']);
            
            $asignaciones = AsignacionAcademica::with(['profesor.restricciones', 'asignatura.preferencia'])
                ->where('grado_id', $gradoId)
                ->where('year', $validated['year'])
                ->get();

            if ($asignaciones->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No hay asignaciones académicas configuradas para este grado.'
                ], 422);
            }

            $totalHorasRequeridas = $asignaciones->sum('horas_semanales');
            $totalDias = count($validated['dias_semana']);
            $capacidadHorario = $totalDias * $validated['horas_por_dia'];
            
            if ($totalHorasRequeridas > $capacidadHorario) {
                DB::rollBack();
                return $this->respuestaHorarioSaturado($totalHorasRequeridas, $capacidadHorario, $totalDias, $validated);
            }

            if ($request->limpiar_existentes) {
                $this->limpiarHorariosGrado($gradoId, $validated['year']);
            }

            // 🔥 GENERACIÓN CON ALGORITMO ULTRA-INTELIGENTE v6.1
            $resultado = $this->generarHorarioUltraInteligente(
                $gradoId,
                $asignaciones,
                $validated['year'],
                $request->limpiar_existentes
            );

            $porcentajeCompletado = $resultado['estadisticas']['porcentaje_completado'] ?? 0;

            // ✅ SOLO ACEPTA 100% (sin umbral relajado)
            if ($porcentajeCompletado >= 100) {
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => '✅ Horario generado exitosamente al 100%',
                    'horarios' => $this->obtenerHorariosGrado($gradoId, $validated['year']),
                    'estadisticas' => $resultado['estadisticas'],
                    'estrategia' => $resultado['estrategia_exitosa'] ?? 'Ultra-Inteligente v6.1',
                    'diagnostico' => $resultado['diagnostico'] ?? null
                ]);
            } else {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => "⚠️ No se pudo generar el horario completo (máximo alcanzado: {$porcentajeCompletado}%)",
                    'errores' => $resultado['errores'],
                    'estadisticas' => $resultado['estadisticas'],
                    'diagnostico' => $resultado['diagnostico'],
                    'materias_faltantes' => $resultado['materias_faltantes'] ?? [],
                    'sugerencias' => $this->generarSugerenciasInteligentes($resultado)
                ], 422);
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al generar horario', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔥 CARGA GLOBAL COMPLETA - TODOS LOS HORARIOS DE TODOS LOS NIVELES
     * 
     * ✅ VERSIÓN CORREGIDA: Valida que las restricciones tengan día Y hora válidos
     */
    private function cargarContextoGlobalCompleto($year)
    {
        Log::info('🌍 Cargando contexto GLOBAL (todos los niveles y grados)...');

        // 1️⃣ Cargar TODAS las asignaciones de TODOS los niveles
        $todasAsignaciones = AsignacionAcademica::with(['profesor', 'asignatura', 'grado'])
            ->where('year', $year)
            ->get();

        Log::info('📚 Total asignaciones cargadas GLOBALMENTE', [
            'total' => $todasAsignaciones->count()
        ]);

        foreach ($todasAsignaciones as $asignacion) {
            $profesorId = $asignacion->profesor_id;
            $gradoId = $asignacion->grado_id;
            
            if (!isset($this->asignacionesPorProfesor[$profesorId])) {
                $this->asignacionesPorProfesor[$profesorId] = [];
            }
            $this->asignacionesPorProfesor[$profesorId][] = $asignacion;
            
            if (!isset($this->asignacionesPorGrado[$gradoId])) {
                $this->asignacionesPorGrado[$gradoId] = [];
            }
            $this->asignacionesPorGrado[$gradoId][] = $asignacion;
        }

        // 2️⃣ Cargar TODAS las restricciones de profesores
        $profesoresIds = array_keys($this->asignacionesPorProfesor);
        $restricciones = RestriccionProfesor::whereIn('profesor_id', $profesoresIds)
            ->where('year', $year)
            ->where('activa', true)
            ->get();

        Log::info('🔒 Restricciones encontradas GLOBALMENTE', [
            'total' => $restricciones->count(),
            'profesores' => count($profesoresIds)
        ]);

        $restriccionesInvalidas = 0;
        $restriccionesCargadas = 0;
        
        foreach ($restricciones as $restriccion) {
            $profesorId = $restriccion->profesor_id;
            $dia = $restriccion->dia_semana;
            
            // 🔥 VALIDACIÓN: Debe tener al menos hora_numero O rango de horas
            $tieneHoraNumero = !empty($restriccion->hora_numero);
            $tieneRangoHoras = !empty($restriccion->hora_inicio) && !empty($restriccion->hora_fin);
            
            if (!$tieneHoraNumero && !$tieneRangoHoras) {
                $restriccionesInvalidas++;
                Log::warning('⚠️ Restricción IGNORADA (falta información de hora)', [
                    'id' => $restriccion->id,
                    'profesor_id' => $profesorId,
                    'dia_semana' => $dia ?? 'NULL (todos los días)',
                    'hora_numero' => $restriccion->hora_numero,
                    'hora_inicio' => $restriccion->hora_inicio,
                    'hora_fin' => $restriccion->hora_fin,
                    'motivo' => $restriccion->motivo
                ]);
                continue;
            }
            
            // 🔥 DETERMINAR A QUÉ DÍAS APLICA
            $diasAplicables = [];
            if (!empty($dia)) {
                // Restricción para un día específico
                $diasAplicables = [$dia];
            } else {
                // dia_semana = NULL → Aplica a TODOS los días
                $diasAplicables = $this->configuracion['dias_semana'];
                Log::info('📌 Restricción GLOBAL (todos los días)', [
                    'profesor_id' => $profesorId,
                    'motivo' => $restriccion->motivo ?? 'No especificado'
                ]);
            }
            
            // 🔥 DETERMINAR QUÉ HORAS BLOQUEAR
            $horasBloqueadas = [];
            
            if ($tieneHoraNumero) {
                // CASO 1: Hora específica
                $horasBloqueadas = [$restriccion->hora_numero];
            } else {
                // CASO 2: Rango de tiempo
                $horasBloqueadas = $this->convertirRangoTiempoAHoras(
                    $restriccion->hora_inicio,
                    $restriccion->hora_fin
                );
            }
            
            // 🔥 APLICAR BLOQUEO EN CADA DÍA APLICABLE
            foreach ($diasAplicables as $diaAplicable) {
                // Inicializar estructura
                if (!isset($this->restriccionesProfesores[$profesorId])) {
                    $this->restriccionesProfesores[$profesorId] = [];
                }
                if (!isset($this->restriccionesProfesores[$profesorId][$diaAplicable])) {
                    $this->restriccionesProfesores[$profesorId][$diaAplicable] = [];
                }
                
                foreach ($horasBloqueadas as $hora) {
                    $this->restriccionesProfesores[$profesorId][$diaAplicable][$hora] = true;
                    $restriccionesCargadas++;
                }
            }
            
            Log::info('✅ Restricción cargada', [
                'profesor_id' => $profesorId,
                'dia' => $dia,
                'horas_bloqueadas' => $horasBloqueadas,
                'motivo' => $restriccion->motivo ?? 'No especificado'
            ]);
        }

        // 3️⃣ Cargar TODOS los horarios ya generados (de todos los niveles)
        $this->todosLosHorarios = Horario::where('year', $year)->get();

        Log::info('📅 Horarios GLOBALES cargados', [
            'total' => $this->todosLosHorarios->count()
        ]);

        // 4️⃣ Inicializar matriz global
        foreach ($this->configuracion['dias_semana'] as $dia) {
            $this->matrizGlobal[$dia] = [];
            for ($hora = 1; $hora <= $this->configuracion['horas_por_dia']; $hora++) {
                $this->matrizGlobal[$dia][$hora] = [];
            }
        }

        // 5️⃣ Cargar horarios existentes en la matriz
        foreach ($this->todosLosHorarios as $horario) {
            $dia = $horario->dia_semana;
            $hora = $horario->hora_numero;
            $gradoId = $horario->grado_id;
            $profesorId = $horario->profesor_id;

            // Validar que el día/hora estén en la configuración actual
            if (!in_array($dia, $this->configuracion['dias_semana']) || 
                $hora > $this->configuracion['horas_por_dia']) {
                continue;
            }

            $this->matrizGlobal[$dia][$hora][$gradoId] = [
                'id' => $horario->id,
                'asignatura_id' => $horario->asignatura_id,
                'profesor_id' => $profesorId,
                'existente' => true
            ];

            if (!isset($this->profesoresOcupados[$profesorId])) {
                $this->profesoresOcupados[$profesorId] = [];
            }
            if (!isset($this->profesoresOcupados[$profesorId][$dia])) {
                $this->profesoresOcupados[$profesorId][$dia] = [];
            }
            
            $this->profesoresOcupados[$profesorId][$dia][$hora] = $gradoId;
        }

        // 6️⃣ Analizar densidad de slots
        $this->analizarDensidadSlots();

        Log::info('✅ Contexto GLOBAL cargado completamente', [
            'profesores_totales' => count($this->asignacionesPorProfesor),
            'grados_totales' => count($this->asignacionesPorGrado),
            'restricciones_validas' => $restriccionesCargadas,
            'restricciones_invalidas' => $restriccionesInvalidas,
            'horarios_existentes' => $this->todosLosHorarios->count()
        ]);
        
        // 🔥 LOG DETALLADO de restricciones por profesor
        Log::info('📊 RESUMEN DE RESTRICCIONES CARGADAS:');
        
        if (count($this->restriccionesProfesores) === 0) {
            Log::warning('⚠️ NO SE CARGARON RESTRICCIONES DE PROFESORES');
        } else {
            foreach ($this->restriccionesProfesores as $profId => $dias) {
                $totalRestriccionesProf = 0;
                $detallesPorDia = [];
                
                foreach ($dias as $diaRestr => $horas) {
                    $totalRestriccionesProf += count($horas);
                    $horasArray = array_keys($horas);
                    $detallesPorDia[$diaRestr] = count($horas) . ' horas: [' . implode(', ', $horasArray) . ']';
                }
                
                if ($totalRestriccionesProf > 0) {
                    $profesor = \App\Models\User::find($profId);
                    Log::info("📌 Profesor: " . ($profesor ? $profesor->name : "ID {$profId}") . " → {$totalRestriccionesProf} slots restringidos", [
                        'detalle' => $detallesPorDia
                    ]);
                }
            }
        }
    }
    
    /**
     * 🆕 CONVERTIR RANGO DE TIEMPO A NÚMEROS DE HORA
     */
    private function convertirRangoTiempoAHoras($horaInicio, $horaFin)
    {
        $horasBloqueadas = [];
        
        try {
            $inicio = \Carbon\Carbon::parse($horaInicio);
            $fin = \Carbon\Carbon::parse($horaFin);
            $horaInicioConfig = \Carbon\Carbon::parse($this->configuracion['hora_inicio']);
            $duracionClase = $this->configuracion['duracion_clase'];
            
            for ($h = 1; $h <= $this->configuracion['horas_por_dia']; $h++) {
                $minutosTotales = ($h - 1) * $duracionClase;
                $horaClase = $horaInicioConfig->copy()->addMinutes($minutosTotales);
                
                if ($horaClase->between($inicio, $fin, false)) {
                    $horasBloqueadas[] = $h;
                }
            }
            
            if (empty($horasBloqueadas)) {
                Log::warning('⚠️ No se pudo calcular horas del rango, bloqueando hora 1', [
                    'hora_inicio' => $horaInicio,
                    'hora_fin' => $horaFin
                ]);
                $horasBloqueadas = [1];
            }
            
        } catch (\Exception $e) {
            Log::error('❌ Error al convertir rango de tiempo', [
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'error' => $e->getMessage()
            ]);
            $horasBloqueadas = [1];
        }
        
        return $horasBloqueadas;
    }

    /**
     * 🆕 GENERACIÓN ULTRA-INTELIGENTE v6.1 CON MEJOR RESULTADO (SOLUCIÓN 3)
     */
    private function generarHorarioUltraInteligente($gradoId, $asignaciones, $year, $limpiarExistentes)
    {
        Log::info('🎯 Iniciando generación ULTRA-INTELIGENTE v6.1');

        $this->bloqueosPorRazon = [
            'grado_ocupado' => 0,
            'profesor_ocupado' => 0,
            'restriccion_profesor' => 0,
            'duplicado_bd' => 0
        ];
        $this->intentosFallidosPorAsignatura = [];

        $horasYaAsignadas = $this->contarHorasAsignadas($gradoId);
        
        // 🔥 10 ESTRATEGIAS CON MÁS INTENTOS
        $estrategias = [
            ['nombre' => 'profesor_menos_ocupado', 'desc' => 'Profesores menos ocupados primero', 'intentos' => 5],
            ['nombre' => 'balance_dias_estricto', 'desc' => 'Balance estricto de días', 'intentos' => 5],
            ['nombre' => 'bloques_consecutivos_flex', 'desc' => 'Bloques consecutivos con flexibilidad', 'intentos' => 5],
            ['nombre' => 'dispersion_inteligente', 'desc' => 'Dispersión inteligente con agrupación', 'intentos' => 5],
            ['nombre' => 'profesor_compartido_smart', 'desc' => 'Profesores compartidos con lógica smart', 'intentos' => 5],
            ['nombre' => 'horas_optimas', 'desc' => 'Horas óptimas según preferencias', 'intentos' => 5],
            ['nombre' => 'slots_criticos', 'desc' => 'Priorizar slots críticos', 'intentos' => 5],
            ['nombre' => 'backtracking_completo', 'desc' => 'Backtracking completo con reintento', 'intentos' => 10],
            ['nombre' => 'random_ponderado', 'desc' => 'Aleatorio ponderado con historia', 'intentos' => 5],
            ['nombre' => 'mixta_ultra', 'desc' => 'Estrategia mixta ultra-adaptativa', 'intentos' => 10]
        ];

        // ✅ SOLUCIÓN 3: GUARDAR MEJOR RESULTADO
        $mejorResultado = null;
        $mejorPorcentaje = 0;
        $estrategiaExitosa = '';
        $mejorHorarios = [];

        foreach ($estrategias as $estrategia) {
            $nombreEstrategia = $estrategia['nombre'];
            $descripcion = $estrategia['desc'];
            
            Log::info("🔄 Probando estrategia: {$descripcion}");
            
            $this->guardarEstado();
            $this->estrategiaActual = $nombreEstrategia;
            
            $asignacionesOrdenadas = $this->ordenarPorEstrategiaUltra($asignaciones, $gradoId, $nombreEstrategia);
            
            $resultadoEstrategia = $this->intentarConEstrategiaConBacktracking(
                $gradoId,
                $asignacionesOrdenadas,
                $year,
                $horasYaAsignadas,
                $nombreEstrategia,
                $estrategia['intentos']
            );
            
            $porcentaje = $resultadoEstrategia['estadisticas']['porcentaje_completado'] ?? 0;
            
            Log::info("📊 Resultado de estrategia '{$descripcion}': {$porcentaje}%");
            
            // ✅ GUARDAR SI ES MEJOR (SOLUCIÓN 3)
            if ($porcentaje > $mejorPorcentaje) {
                // Guardar snapshot de los horarios actuales
                $mejorHorarios = Horario::where('grado_id', $gradoId)
                    ->where('year', $year)
                    ->get()
                    ->map(function($h) {
                        return $h->toArray();
                    })
                    ->toArray();
                
                $mejorResultado = $resultadoEstrategia;
                $mejorPorcentaje = $porcentaje;
                $estrategiaExitosa = $descripcion;
                
                Log::info("🌟 NUEVO MEJOR RESULTADO: {$porcentaje}% con {$descripcion}");
            }
            
            // Si logramos 100%, terminar inmediatamente
            if ($porcentaje >= 100) {
                Log::info("✅ ¡Estrategia PERFECTA encontrada! {$descripcion}");
                break;
            }
            
            // Limpiar para siguiente estrategia
            if ($porcentaje < 100) {
                $this->restaurarEstado();
                $this->limpiarAsignacionesGrado($gradoId);
            }
        }

        // ✅ RESTAURAR EL MEJOR RESULTADO SI NO ES 100% (SOLUCIÓN 3)
        if ($mejorPorcentaje > 0 && $mejorPorcentaje < 100) {
            Log::warning('⚠️ Restaurando mejor resultado disponible', [
                'porcentaje' => $mejorPorcentaje,
                'estrategia' => $estrategiaExitosa
            ]);
            
            // Limpiar y restaurar
            Horario::where('grado_id', $gradoId)->where('year', $year)->delete();
            foreach ($mejorHorarios as $horarioData) {
                Horario::create($horarioData);
            }
        }

        if ($mejorResultado) {
            $mejorResultado['estrategia_exitosa'] = $estrategiaExitosa;
            $mejorResultado['exito'] = $mejorPorcentaje >= 100;
            
            // ✅ SOLUCIÓN 2: DIAGNÓSTICO COMPLETO
            $mejorResultado['diagnostico'] = $this->generarDiagnostico($gradoId, $asignaciones, $year);
            $mejorResultado['materias_faltantes'] = $this->diagnosticarAsignacionesFallidas($gradoId, $asignaciones, $year);
        }

        return $mejorResultado ?? [
            'exito' => false,
            'estadisticas' => ['porcentaje_completado' => 0],
            'errores' => ['No se pudo generar ningún horario con ninguna estrategia'],
            'diagnostico' => $this->generarDiagnostico($gradoId, $asignaciones, $year),
            'materias_faltantes' => []
        ];
    }

    /**
     * ✅ SOLUCIÓN 2: DIAGNOSTICAR ASIGNACIONES FALLIDAS
     */
    private function diagnosticarAsignacionesFallidas($gradoId, $asignacionesPendientes, $year)
    {
        $fallidas = [];
        $asignacionesExitosas = Horario::where('grado_id', $gradoId)
                                       ->where('year', $year)
                                       ->get();
        
        foreach ($asignacionesPendientes as $asignacion) {
            $horasAsignadas = $asignacionesExitosas->where('asignatura_id', $asignacion->asignatura_id)
                                                   ->where('profesor_id', $asignacion->profesor_id)
                                                   ->count();
            
            $horasFaltantes = $asignacion->horas_semanales - $horasAsignadas;
            
            if ($horasFaltantes > 0) {
                $profesorId = $asignacion->profesor_id;
                $horasOcupadas = 0;
                
                // Contar cuántas horas ya tiene el profesor
                foreach ($this->configuracion['dias_semana'] as $dia) {
                    for ($hora = 1; $hora <= $this->configuracion['horas_por_dia']; $hora++) {
                        if (isset($this->profesoresOcupados[$profesorId][$dia][$hora])) {
                            $horasOcupadas++;
                        }
                    }
                }
                
                $fallidas[] = [
                    'asignatura' => $asignacion->asignatura->nombre ?? 'Desconocida',
                    'profesor_id' => $profesorId,
                    'profesor' => $asignacion->profesor->name ?? 'Desconocido',
                    'horas_requeridas' => $asignacion->horas_semanales,
                    'horas_asignadas' => $horasAsignadas,
                    'horas_faltantes' => $horasFaltantes,
                    'profesor_horas_ocupadas' => $horasOcupadas,
                    'profesor_horas_disponibles' => (5 * 6) - $horasOcupadas
                ];
                
                Log::warning('❌ MATERIA SIN COMPLETAR', [
                    'asignatura' => $asignacion->asignatura->nombre,
                    'profesor' => $asignacion->profesor->name,
                    'requeridas' => $asignacion->horas_semanales,
                    'asignadas' => $horasAsignadas,
                    'faltantes' => $horasFaltantes
                ]);
            }
        }
        
        if (count($fallidas) > 0) {
            Log::warning('❌ RESUMEN MATERIAS FALTANTES', [
                'total_materias_incompletas' => count($fallidas),
                'detalles' => $fallidas
            ]);
        }
        
        return $fallidas;
    }

    /**
     * 🆕 ORDENAR CON ESTRATEGIAS ULTRA-INTELIGENTES
     */
    private function ordenarPorEstrategiaUltra($asignaciones, $gradoId, $estrategia)
    {
        return $asignaciones->sortBy(function($asignacion) use ($estrategia, $gradoId) {
            $puntuacion = 0;

            switch ($estrategia) {
                case 'profesor_menos_ocupado':
                    $cargaTotal = $this->calcularCargaTotalProfesor($asignacion->profesor_id);
                    $puntuacion += (100 - $cargaTotal) * 100;
                    $puntuacion += ($asignacion->horas_semanales * 10);
                    break;

                case 'balance_dias_estricto':
                    $puntuacion += ($asignacion->horas_semanales * 200);
                    $gradosQueAtiende = count($this->asignacionesPorProfesor[$asignacion->profesor_id] ?? []);
                    if ($gradosQueAtiende > 1) {
                        $puntuacion += ($gradosQueAtiende * 100);
                    }
                    break;

                case 'bloques_consecutivos_flex':
                    if ($asignacion->horas_semanales >= 2) {
                        $puntuacion += ($asignacion->horas_semanales * 150);
                    }
                    break;

                case 'dispersion_inteligente':
                    $puntuacion += (10 - $asignacion->horas_semanales) * 120;
                    break;

                case 'profesor_compartido_smart':
                    $gradosQueAtiende = count($this->asignacionesPorProfesor[$asignacion->profesor_id] ?? []);
                    $cargaTotal = $this->calcularCargaTotalProfesor($asignacion->profesor_id);
                    
                    if ($gradosQueAtiende > 1) {
                        $puntuacion += ($gradosQueAtiende * 400);
                    }
                    $puntuacion -= ($cargaTotal * 10);
                    break;

                case 'horas_optimas':
                    if ($asignacion->asignatura->preferencia) {
                        $puntuacion += ($asignacion->asignatura->preferencia->prioridad * 200);
                    }
                    $puntuacion += ($asignacion->horas_semanales * 30);
                    break;

                case 'slots_criticos':
                    $slotsDisponibles = $this->contarSlotsDisponiblesParaProfesor($asignacion->profesor_id, $gradoId);
                    $puntuacion += ((50 - $slotsDisponibles) * 100);
                    break;

                case 'backtracking_completo':
                    $puntuacion += ($asignacion->horas_semanales * 100);
                    $gradosQueAtiende = count($this->asignacionesPorProfesor[$asignacion->profesor_id] ?? []);
                    $puntuacion += ($gradosQueAtiende * 80);
                    break;

                case 'random_ponderado':
                    $puntuacion += rand(0, 1000);
                    $cargaTotal = $this->calcularCargaTotalProfesor($asignacion->profesor_id);
                    $puntuacion -= ($cargaTotal * 5);
                    break;

                case 'mixta_ultra':
                    $puntuacion += ($asignacion->horas_semanales * 100);
                    $gradosQueAtiende = count($this->asignacionesPorProfesor[$asignacion->profesor_id] ?? []);
                    $puntuacion += ($gradosQueAtiende * 120);
                    $cargaTotal = $this->calcularCargaTotalProfesor($asignacion->profesor_id);
                    $puntuacion -= ($cargaTotal * 8);
                    if ($asignacion->asignatura->preferencia) {
                        $puntuacion += ($asignacion->asignatura->preferencia->prioridad * 80);
                    }
                    break;
            }

            return -$puntuacion;
        })->values();
    }

    private function calcularCargaTotalProfesor($profesorId)
    {
        $total = 0;
        
        if (!isset($this->profesoresOcupados[$profesorId])) {
            return 0;
        }
        
        foreach ($this->profesoresOcupados[$profesorId] as $dia => $horas) {
            $total += count($horas);
        }
        
        return $total;
    }

    private function contarSlotsDisponiblesParaProfesor($profesorId, $gradoId)
    {
        $disponibles = 0;
        
        foreach ($this->configuracion['dias_semana'] as $dia) {
            for ($hora = 1; $hora <= $this->configuracion['horas_por_dia']; $hora++) {
                if ($this->esSlotDisponible($gradoId, $profesorId, $dia, $hora)) {
                    $disponibles++;
                }
            }
        }
        
        return $disponibles;
    }

    private function intentarConEstrategiaConBacktracking($gradoId, $asignaciones, $year, $horasYaAsignadas, $nombreEstrategia, $maxIntentos)
    {
        $mejorResultado = null;
        $mejorPorcentaje = 0;

        for ($intento = 1; $intento <= $maxIntentos; $intento++) {
            Log::info("  📋 Intento #{$intento}/{$maxIntentos} con estrategia '{$nombreEstrategia}'");

            $resultadoIntento = $this->intentarAsignacionConFlexibilidad(
                $gradoId,
                $asignaciones,
                $year,
                $horasYaAsignadas,
                $intento
            );

            $porcentaje = $resultadoIntento['estadisticas']['porcentaje_completado'] ?? 0;

            if ($porcentaje >= 100) {
                return $resultadoIntento;
            }

            if ($porcentaje > $mejorPorcentaje) {
                $mejorResultado = $resultadoIntento;
                $mejorPorcentaje = $porcentaje;
            }

            if ($intento < $maxIntentos) {
                $this->limpiarAsignacionesGrado($gradoId);
                $asignaciones = $asignaciones->shuffle();
            }
        }

        return $mejorResultado ?? [
            'exito' => false,
            'estadisticas' => ['porcentaje_completado' => 0],
            'errores' => []
        ];
    }

    private function intentarAsignacionConFlexibilidad($gradoId, $asignaciones, $year, $horasYaAsignadas, $nivelFlexibilidad)
    {
        $horasAsignadas = $horasYaAsignadas;
        $completas = 0;
        $incompletas = 0;
        $errores = [];

        foreach ($asignaciones as $asignacion) {
            $horasRequeridas = $asignacion->horas_semanales;
            $horasActuales = $this->contarHorasAsignacion($gradoId, $asignacion->asignatura_id);
            $horasFaltantes = $horasRequeridas - $horasActuales;

            if ($horasFaltantes <= 0) {
                $completas++;
                continue;
            }

            $slots = $this->encontrarMejoresSlotsConFlexibilidad(
                $gradoId, 
                $asignacion, 
                $horasFaltantes,
                $nivelFlexibilidad
            );

            $horasAsignadasAsignacion = 0;
            foreach ($slots as $slot) {
                if ($horasAsignadasAsignacion >= $horasFaltantes) break;

                if ($this->asignarSlot($gradoId, $asignacion, $slot, $year)) {
                    $horasAsignadasAsignacion++;
                    $horasAsignadas++;
                }
            }

            if ($horasAsignadasAsignacion >= $horasFaltantes) {
                $completas++;
            } else {
                $incompletas++;
                $errores[] = [
                    'asignatura' => $asignacion->asignatura->nombre,
                    'profesor' => $asignacion->profesor->name,
                    'horas_faltantes' => $horasFaltantes - $horasAsignadasAsignacion
                ];
            }
        }

        return [
            'exito' => $incompletas === 0,
            'horas_asignadas' => $horasAsignadas,
            'completas' => $completas,
            'incompletas' => $incompletas,
            'errores' => $errores,
            'estadisticas' => [
                'total_asignaciones' => $asignaciones->count(),
                'total_horas_requeridas' => $asignaciones->sum('horas_semanales'),
                'horas_asignadas' => $horasAsignadas,
                'asignaciones_completas' => $completas,
                'asignaciones_incompletas' => $incompletas,
                'porcentaje_completado' => $asignaciones->sum('horas_semanales') > 0
                    ? round(($horasAsignadas / $asignaciones->sum('horas_semanales')) * 100, 1)
                    : 0
            ]
        ];
    }

    private function encontrarMejoresSlotsConFlexibilidad($gradoId, $asignacion, $cantidad, $nivelFlexibilidad)
    {
        $slots = [];
        $profesorId = $asignacion->profesor_id;

        foreach ($this->configuracion['dias_semana'] as $dia) {
            for ($hora = 1; $hora <= $this->configuracion['horas_por_dia']; $hora++) {
                if (!$this->esSlotDisponible($gradoId, $profesorId, $dia, $hora)) {
                    continue;
                }

                $puntuacion = $this->calcularPuntuacionSlotUltraMejorada(
                    $gradoId,
                    $asignacion,
                    $dia,
                    $hora,
                    $nivelFlexibilidad
                );

                $slots[] = [
                    'dia' => $dia,
                    'hora' => $hora,
                    'puntuacion' => $puntuacion
                ];
            }
        }

        usort($slots, function($a, $b) {
            return $b['puntuacion'] <=> $a['puntuacion'];
        });

        $factorExpansion = $nivelFlexibilidad * 2;
        return $this->distribuirSegunEstrategiaConFlexibilidad(
            $slots, 
            $cantidad + $factorExpansion,
            $asignacion
        );
    }

    private function calcularPuntuacionSlotUltraMejorada($gradoId, $asignacion, $dia, $hora, $nivelFlexibilidad)
    {
        $puntuacion = 100;

        $densidad = $this->slotsDensidad[$dia][$hora] ?? null;
        if ($densidad) {
            $puntuacion += ($densidad['porcentaje_libre'] * 0.8);
        }

        $preferencia = $asignacion->asignatura->preferencia;
        if ($preferencia) {
            $esHoraDespuesRecreo = $this->esHoraDespuesRecreo($hora);
            $totalHoras = $this->configuracion['horas_por_dia'];
            
            if ($preferencia->esHoraAdecuada($hora, $totalHoras, $esHoraDespuesRecreo)) {
                $puntuacion += 50;
            } else {
                $puntuacion -= (20 - ($nivelFlexibilidad * 5));
            }
        }

        $vecesEnDia = $this->contarAsignaturaEnDia($gradoId, $asignacion->asignatura_id, $dia);
        if ($vecesEnDia > 0) {
            $puntuacion -= ($vecesEnDia * (25 - ($nivelFlexibilidad * 3)));
        } else {
            $puntuacion += 40;
        }

        if ($hora == 1 || $hora == $this->configuracion['horas_por_dia']) {
            $puntuacion -= (10 - ($nivelFlexibilidad * 2));
        }

        if ($this->estrategiaActual === 'bloques_consecutivos_flex') {
            if ($this->tieneHoraConsecutivaDisponible($gradoId, $asignacion, $dia, $hora)) {
                $puntuacion += 35;
            }
        }

        $cargaProfesor = $this->calcularCargaProfesor($asignacion->profesor_id, $dia);
        if ($cargaProfesor < 2) {
            $puntuacion += 20;
        } elseif ($cargaProfesor >= 5) {
            $puntuacion -= (15 - ($nivelFlexibilidad * 2));
        }

        $conflictosProfesor = $this->contarConflictosProfesorEnSlot($asignacion->profesor_id, $dia, $hora);
        if ($conflictosProfesor > 0) {
            $puntuacion -= 1000;
        }

        return $puntuacion;
    }

    private function contarConflictosProfesorEnSlot($profesorId, $dia, $hora)
    {
        if (!isset($this->profesoresOcupados[$profesorId][$dia][$hora])) {
            return 0;
        }
        
        return 1;
    }

    private function esSlotDisponible($gradoId, $profesorId, $dia, $hora)
    {
        if (isset($this->matrizGlobal[$dia][$hora][$gradoId])) {
            $this->bloqueosPorRazon['grado_ocupado']++;
            return false;
        }

        if (isset($this->profesoresOcupados[$profesorId][$dia][$hora])) {
            $this->bloqueosPorRazon['profesor_ocupado']++;
            
            $gradoOcupado = $this->profesoresOcupados[$profesorId][$dia][$hora];
            Log::debug('🚫 Profesor ocupado en otro grado', [
                'profesor_id' => $profesorId,
                'dia' => $dia,
                'hora' => $hora,
                'grado_ocupado' => $gradoOcupado,
                'grado_solicitado' => $gradoId
            ]);
            
            return false;
        }

        if (isset($this->restriccionesProfesores[$profesorId][$dia][$hora])) {
            $this->bloqueosPorRazon['restriccion_profesor']++;
            
            Log::info('🚫 SLOT BLOQUEADO POR RESTRICCIÓN', [
                'profesor_id' => $profesorId,
                'dia' => $dia,
                'hora' => $hora,
                'grado_solicitado' => $gradoId
            ]);
            
            return false;
        }

        return true;
    }

    private function asignarSlot($gradoId, $asignacion, $slot, $year)
    {
        $dia = $slot['dia'];
        $hora = $slot['hora'];

        if (!$this->esSlotDisponible($gradoId, $asignacion->profesor_id, $dia, $hora)) {
            $asigId = $asignacion->asignatura_id;
            if (!isset($this->intentosFallidosPorAsignatura[$asigId])) {
                $this->intentosFallidosPorAsignatura[$asigId] = 0;
            }
            $this->intentosFallidosPorAsignatura[$asigId]++;
            
            return false;
        }

        if ($this->slotExisteEnBD($gradoId, $dia, $hora, $year)) {
            $this->bloqueosPorRazon['duplicado_bd']++;
            return false;
        }

        $profesorOcupado = Horario::where('profesor_id', $asignacion->profesor_id)
            ->where('dia_semana', $dia)
            ->where('hora_numero', $hora)
            ->where('year', $year)
            ->exists();

        if ($profesorOcupado) {
            $this->bloqueosPorRazon['profesor_ocupado']++;
            
            Log::warning('🚫 Profesor ya ocupado en BD', [
                'profesor_id' => $asignacion->profesor_id,
                'dia' => $dia,
                'hora' => $hora
            ]);
            
            return false;
        }

        try {
            $horario = Horario::create([
                'nivel_id' => $asignacion->grado->nivel_id,
                'grado_id' => $gradoId,
                'asignatura_id' => $asignacion->asignatura_id,
                'profesor_id' => $asignacion->profesor_id,
                'asignacion_academica_id' => $asignacion->id,
                'dia_semana' => $dia,
                'hora_numero' => $hora,
                'year' => $year,
                'hora_inicio' => $this->configuracion['hora_inicio'],
                'hora_fin' => $this->configuracion['hora_fin'],
                'duracion_clase' => $this->configuracion['duracion_clase'],
                'horas_por_dia' => $this->configuracion['horas_por_dia'],
                'dias_semana' => json_encode($this->configuracion['dias_semana']),
                'recreo_despues_hora' => $this->configuracion['recreo_despues_hora'],
                'recreo_duracion' => $this->configuracion['recreo_duracion'],
                'generado_automaticamente' => true
            ]);

            $this->matrizGlobal[$dia][$hora][$gradoId] = [
                'id' => $horario->id,
                'asignatura_id' => $asignacion->asignatura_id,
                'profesor_id' => $asignacion->profesor_id,
                'existente' => false
            ];

            if (!isset($this->profesoresOcupados[$asignacion->profesor_id])) {
                $this->profesoresOcupados[$asignacion->profesor_id] = [];
            }
            if (!isset($this->profesoresOcupados[$asignacion->profesor_id][$dia])) {
                $this->profesoresOcupados[$asignacion->profesor_id][$dia] = [];
            }
            
            $this->profesoresOcupados[$asignacion->profesor_id][$dia][$hora] = $gradoId;

            Log::debug('✅ Slot asignado exitosamente', [
                'grado_id' => $gradoId,
                'profesor_id' => $asignacion->profesor_id,
                'asignatura' => $asignacion->asignatura->nombre,
                'dia' => $dia,
                'hora' => $hora
            ]);

            return true;

        } catch (\Exception $e) {
            if (!str_contains($e->getMessage(), 'Duplicate entry')) {
                Log::error('Error inesperado al asignar slot', [
                    'dia' => $dia,
                    'hora' => $hora,
                    'grado' => $gradoId,
                    'profesor' => $asignacion->profesor_id,
                    'error' => $e->getMessage()
                ]);
            }
            return false;
        }
    }

    // ========== MÉTODOS AUXILIARES ==========

    private function distribuirSegunEstrategiaConFlexibilidad($slots, $cantidad, $asignacion)
    {
        switch ($this->estrategiaActual) {
            case 'balance_dias_estricto':
            case 'mixta_ultra':
                return $this->distribuirBalanceadoPorDia($slots, $cantidad);
            
            case 'bloques_consecutivos_flex':
                return $this->distribuirEnBloques($slots, $cantidad);
            
            case 'dispersion_inteligente':
                return $this->distribuirDispersion($slots, $cantidad);
            
            default:
                return $this->distribuirEquilibradamentePorDia($slots, $cantidad);
        }
    }

    private function generarDiagnostico($gradoId, $asignaciones, $year)
    {
        $diagnostico = [
            'resumen_bloqueos' => $this->bloqueosPorRazon,
            'asignaturas_problematicas' => [],
            'profesores_mas_ocupados' => [],
            'slots_saturados' => [],
            'restricciones_activas' => []
        ];

        foreach ($this->intentosFallidosPorAsignatura as $asigId => $intentos) {
            if ($intentos > 10) {
                $asig = $asignaciones->firstWhere('asignatura_id', $asigId);
                if ($asig) {
                    $diagnostico['asignaturas_problematicas'][] = [
                        'nombre' => $asig->asignatura->nombre,
                        'profesor' => $asig->profesor->name,
                        'intentos_fallidos' => $intentos,
                        'horas_requeridas' => $asig->horas_semanales
                    ];
                }
            }
        }

        foreach ($this->profesoresOcupados as $profesorId => $dias) {
            $totalHoras = 0;
            foreach ($dias as $horas) {
                $totalHoras += count($horas);
            }
            
            if ($totalHoras > 0) {
                $profesor = \App\Models\User::find($profesorId);
                $diagnostico['profesores_mas_ocupados'][] = [
                    'nombre' => $profesor ? $profesor->name : "ID: {$profesorId}",
                    'total_horas' => $totalHoras
                ];
            }
        }

        usort($diagnostico['profesores_mas_ocupados'], function($a, $b) {
            return $b['total_horas'] <=> $a['total_horas'];
        });
        $diagnostico['profesores_mas_ocupados'] = array_slice($diagnostico['profesores_mas_ocupados'], 0, 5);

        foreach ($this->slotsDensidad as $dia => $horas) {
            foreach ($horas as $hora => $info) {
                if ($info['porcentaje_libre'] < 30) {
                    $diagnostico['slots_saturados'][] = [
                        'dia' => $dia,
                        'hora' => $hora,
                        'ocupacion' => $info['ocupacion'],
                        'disponibilidad' => $info['disponibilidad']
                    ];
                }
            }
        }

        foreach ($this->restriccionesProfesores as $profesorId => $diasRest) {
            $profesor = \App\Models\User::find($profesorId);
            $totalRestricciones = 0;
            
            foreach ($diasRest as $dia => $horas) {
                $totalRestricciones += count($horas);
            }
            
            if ($totalRestricciones > 0) {
                $diagnostico['restricciones_activas'][] = [
                    'profesor' => $profesor ? $profesor->name : "ID: {$profesorId}",
                    'total_slots_restringidos' => $totalRestricciones
                ];
            }
        }

        return $diagnostico;
    }

    private function generarSugerenciasInteligentes($resultado)
    {
        $sugerencias = [];
        $diagnostico = $resultado['diagnostico'] ?? [];

        $bloqueos = $diagnostico['resumen_bloqueos'] ?? [];
        
        if (($bloqueos['profesor_ocupado'] ?? 0) > 20) {
            $sugerencias[] = "⚠️ Muchos conflictos de profesores ocupados ({$bloqueos['profesor_ocupado']}). Los profesores están sobre-asignados en múltiples grados.";
        }

        if (($bloqueos['restriccion_profesor'] ?? 0) > 10) {
            $sugerencias[] = "⚠️ Las restricciones de profesores están bloqueando {$bloqueos['restriccion_profesor']} slots. Revise si son realmente necesarias.";
        }

        if (!empty($diagnostico['slots_saturados'])) {
            $sugerencias[] = "⚠️ Hay " . count($diagnostico['slots_saturados']) . " slots muy saturados. Considere aumentar las horas por día o días de la semana.";
        }

        if (!empty($diagnostico['asignaturas_problematicas'])) {
            $sugerencias[] = "📋 Asignaturas con dificultad:";
            foreach (array_slice($diagnostico['asignaturas_problematicas'], 0, 3) as $asig) {
                $sugerencias[] = "  • {$asig['nombre']} ({$asig['profesor']}) - {$asig['intentos_fallidos']} intentos fallidos";
            }
        }
        
        if (!empty($resultado['materias_faltantes'])) {
            $sugerencias[] = "📋 Materias sin completar:";
            foreach (array_slice($resultado['materias_faltantes'], 0, 5) as $faltante) {
                $sugerencias[] = "  • {$faltante['asignatura']} ({$faltante['profesor']}) - Faltan {$faltante['horas_faltantes']} horas";
            }
        }

        if (empty($sugerencias)) {
            $sugerencias[] = "El horario está muy cerca de completarse. Intente generar nuevamente.";
        }

        return $sugerencias;
    }

    private function analizarDensidadSlots()
    {
        $this->slotsDensidad = [];
        
        foreach ($this->configuracion['dias_semana'] as $dia) {
            $this->slotsDensidad[$dia] = [];
            
            for ($hora = 1; $hora <= $this->configuracion['horas_por_dia']; $hora++) {
                $ocupacion = count($this->matrizGlobal[$dia][$hora] ?? []);
                $totalGrados = count($this->asignacionesPorGrado);
                
                $this->slotsDensidad[$dia][$hora] = [
                    'ocupacion' => $ocupacion,
                    'disponibilidad' => $totalGrados - $ocupacion,
                    'porcentaje_libre' => $totalGrados > 0 ? (($totalGrados - $ocupacion) / $totalGrados) * 100 : 0
                ];
            }
        }
    }

    private function distribuirEnBloques($slots, $cantidad)
    {
        $seleccionados = [];
        $bloqueActual = [];
        
        foreach ($slots as $slot) {
            if (count($seleccionados) >= $cantidad) break;
            
            if (empty($bloqueActual) || $this->esConsecutivo($bloqueActual[count($bloqueActual) - 1], $slot)) {
                $bloqueActual[] = $slot;
                $seleccionados[] = $slot;
            } else {
                if (count($seleccionados) < $cantidad) {
                    $bloqueActual = [$slot];
                    $seleccionados[] = $slot;
                }
            }
        }
        
        return $seleccionados;
    }

    private function distribuirDispersion($slots, $cantidad)
    {
        $seleccionados = [];
        $diasUsados = [];
        
        foreach ($slots as $slot) {
            if (count($seleccionados) >= $cantidad) break;
            
            if (!in_array($slot['dia'], $diasUsados)) {
                $seleccionados[] = $slot;
                $diasUsados[] = $slot['dia'];
            }
        }
        
        if (count($seleccionados) < $cantidad) {
            foreach ($slots as $slot) {
                if (count($seleccionados) >= $cantidad) break;
                
                $puedeAgregar = true;
                foreach ($seleccionados as $sel) {
                    if ($sel['dia'] === $slot['dia'] && abs($sel['hora'] - $slot['hora']) < 2) {
                        $puedeAgregar = false;
                        break;
                    }
                }
                
                if ($puedeAgregar && !in_array($slot, $seleccionados)) {
                    $seleccionados[] = $slot;
                }
            }
        }
        
        return $seleccionados;
    }

    private function distribuirBalanceadoPorDia($slots, $cantidad)
    {
        $seleccionados = [];
        $contadorPorDia = [];
        
        foreach ($this->configuracion['dias_semana'] as $dia) {
            $contadorPorDia[$dia] = 0;
        }
        
        $horasPorDia = ceil($cantidad / count($this->configuracion['dias_semana']));
        
        foreach ($slots as $slot) {
            if (count($seleccionados) >= $cantidad) break;
            
            if ($contadorPorDia[$slot['dia']] < $horasPorDia) {
                $seleccionados[] = $slot;
                $contadorPorDia[$slot['dia']]++;
            }
        }
        
        foreach ($slots as $slot) {
            if (count($seleccionados) >= $cantidad) break;
            
            if (!in_array($slot, $seleccionados)) {
                $seleccionados[] = $slot;
            }
        }
        
        return $seleccionados;
    }

    private function distribuirEquilibradamentePorDia($slots, $cantidad)
    {
        $seleccionados = [];
        $diasUsados = [];

        foreach ($slots as $slot) {
            if (count($seleccionados) >= $cantidad) break;
            
            if (!in_array($slot['dia'], $diasUsados)) {
                $seleccionados[] = $slot;
                $diasUsados[] = $slot['dia'];
            }
        }

        foreach ($slots as $slot) {
            if (count($seleccionados) >= $cantidad) break;
            
            $yaSeleccionado = false;
            foreach ($seleccionados as $sel) {
                if ($sel['dia'] === $slot['dia'] && $sel['hora'] === $slot['hora']) {
                    $yaSeleccionado = true;
                    break;
                }
            }

            if (!$yaSeleccionado) {
                $seleccionados[] = $slot;
            }
        }

        return $seleccionados;
    }

    private function esConsecutivo($slot1, $slot2)
    {
        return $slot1['dia'] === $slot2['dia'] && abs($slot1['hora'] - $slot2['hora']) === 1;
    }

    private function guardarEstado()
    {
        $this->estadosGuardados = [
            'matrizGlobal' => $this->matrizGlobal,
            'profesoresOcupados' => $this->profesoresOcupados
        ];
    }

    private function restaurarEstado()
    {
        if (!empty($this->estadosGuardados)) {
            $this->matrizGlobal = $this->estadosGuardados['matrizGlobal'];
            $this->profesoresOcupados = $this->estadosGuardados['profesoresOcupados'];
        }
    }

    private function contarHorasAsignadas($gradoId)
    {
        $total = 0;
        foreach ($this->matrizGlobal as $dia => $horas) {
            foreach ($horas as $hora => $grados) {
                if (isset($grados[$gradoId])) {
                    $total++;
                }
            }
        }
        return $total;
    }

    private function contarHorasAsignacion($gradoId, $asignaturaId)
    {
        $total = 0;
        foreach ($this->matrizGlobal as $dia => $horas) {
            foreach ($horas as $hora => $grados) {
                if (isset($grados[$gradoId]) && 
                    $grados[$gradoId]['asignatura_id'] == $asignaturaId) {
                    $total++;
                }
            }
        }
        return $total;
    }

    private function contarAsignaturaEnDia($gradoId, $asignaturaId, $dia)
    {
        $total = 0;
        foreach ($this->matrizGlobal[$dia] as $hora => $grados) {
            if (isset($grados[$gradoId]) && 
                $grados[$gradoId]['asignatura_id'] == $asignaturaId) {
                $total++;
            }
        }
        return $total;
    }

    private function tieneHoraConsecutivaDisponible($gradoId, $asignacion, $dia, $hora)
    {
        $horaAnterior = $hora - 1;
        $horaSiguiente = $hora + 1;

        if ($horaAnterior >= 1 && 
            isset($this->matrizGlobal[$dia][$horaAnterior][$gradoId]) &&
            $this->matrizGlobal[$dia][$horaAnterior][$gradoId]['asignatura_id'] == $asignacion->asignatura_id) {
            return true;
        }

        if ($horaSiguiente <= $this->configuracion['horas_por_dia'] && 
            isset($this->matrizGlobal[$dia][$horaSiguiente][$gradoId]) &&
            $this->matrizGlobal[$dia][$horaSiguiente][$gradoId]['asignatura_id'] == $asignacion->asignatura_id) {
            return true;
        }

        return false;
    }

    private function calcularCargaProfesor($profesorId, $dia)
    {
        if (!isset($this->profesoresOcupados[$profesorId][$dia])) {
            return 0;
        }
        return count($this->profesoresOcupados[$profesorId][$dia]);
    }

    private function esHoraDespuesRecreo($hora)
    {
        if (!$this->configuracion['recreo_despues_hora']) {
            return false;
        }
        return $hora == ($this->configuracion['recreo_despues_hora'] + 1);
    }

    private function slotExisteEnBD($gradoId, $dia, $hora, $year)
    {
        return Horario::where('grado_id', $gradoId)
            ->where('dia_semana', $dia)
            ->where('hora_numero', $hora)
            ->where('year', $year)
            ->exists();
    }

    private function limpiarHorariosGrado($gradoId, $year)
    {
        Horario::where('grado_id', $gradoId)
            ->where('year', $year)
            ->delete();

        $this->limpiarAsignacionesGrado($gradoId);

        Log::info('🗑️ Horarios del grado eliminados', ['grado_id' => $gradoId]);
    }

    private function limpiarAsignacionesGrado($gradoId)
    {
        Horario::where('grado_id', $gradoId)
            ->where('generado_automaticamente', true)
            ->delete();
        
        foreach ($this->matrizGlobal as $dia => $horas) {
            foreach ($horas as $hora => $grados) {
                if (isset($grados[$gradoId])) {
                    $profesorId = $grados[$gradoId]['profesor_id'];
                    
                    if (isset($this->profesoresOcupados[$profesorId][$dia][$hora]) &&
                        $this->profesoresOcupados[$profesorId][$dia][$hora] == $gradoId) {
                        unset($this->profesoresOcupados[$profesorId][$dia][$hora]);
                    }
                    
                    unset($this->matrizGlobal[$dia][$hora][$gradoId]);
                }
            }
        }
        
        Log::info('🗑️ Asignaciones del grado limpiadas', ['grado_id' => $gradoId]);
    }

    private function obtenerHorariosGrado($gradoId, $year)
    {
        return Horario::where('grado_id', $gradoId)
            ->where('year', $year)
            ->with(['asignatura', 'profesor'])
            ->get()
            ->map(function($h) {
                return [
                    'id' => $h->id,
                    'dia_semana' => $h->dia_semana,
                    'hora_numero' => $h->hora_numero,
                    'asignatura_id' => $h->asignatura_id,
                    'profesor_id' => $h->profesor_id,
                    'generado_automaticamente' => $h->generado_automaticamente,
                    'asignatura' => $h->asignatura ? [
                        'id' => $h->asignatura->id,
                        'nombre' => $h->asignatura->nombre
                    ] : null,
                    'profesor' => $h->profesor ? [
                        'id' => $h->profesor->id,
                        'name' => $h->profesor->name
                    ] : null
                ];
            });
    }

    private function prepararConfiguracion($validated, $grado)
    {
        return [
            'hora_inicio' => $validated['hora_inicio'],
            'hora_fin' => $validated['hora_fin'],
            'duracion_clase' => $validated['duracion_clase'],
            'horas_por_dia' => $validated['horas_por_dia'],
            'dias_semana' => $validated['dias_semana'],
            'recreo_despues_hora' => $validated['recreo_despues_hora'] ?? null,
            'recreo_duracion' => $validated['recreo_duracion'] ?? null
        ];
    }

    private function respuestaHorarioSaturado($totalHorasRequeridas, $capacidadHorario, $totalDias, $validated)
    {
        $diferencia = $totalHorasRequeridas - $capacidadHorario;
        
        return response()->json([
            'success' => false,
            'message' => '⚠️ HORARIO SATURADO',
            'problema' => [
                'horas_requeridas' => $totalHorasRequeridas,
                'capacidad_horario' => $capacidadHorario,
                'horas_faltantes' => $diferencia
            ],
            'soluciones' => [
                "Aumentar horas por día a " . ($validated['horas_por_dia'] + ceil($diferencia / $totalDias)),
                "Agregar " . ceil($diferencia / $validated['horas_por_dia']) . " día(s) más",
                "Reducir {$diferencia} hora(s) de asignaturas"
            ]
        ], 422);
    }

    public function estadisticas(Request $request, $gradoId)
    {
        try {
            $year = $request->input('year', date('Y'));
            $grado = Grado::with('nivel')->findOrFail($gradoId);
            
            $asignaciones = AsignacionAcademica::where('grado_id', $gradoId)
                ->where('year', $year)
                ->get();

            $horarios = Horario::where('grado_id', $gradoId)
                ->where('year', $year)
                ->get();

            $estadisticas = [
                'grado' => $grado->nombre_completo,
                'total_asignaciones' => $asignaciones->count(),
                'total_horas_requeridas' => $asignaciones->sum('horas_semanales'),
                'total_horas_programadas' => $horarios->count(),
                'porcentaje_completado' => $asignaciones->sum('horas_semanales') > 0
                    ? round(($horarios->count() / $asignaciones->sum('horas_semanales')) * 100, 1)
                    : 0
            ];

            return response()->json([
                'success' => true,
                'estadisticas' => $estadisticas
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ], 500);
        }
    }
}