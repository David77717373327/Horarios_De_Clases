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
use Illuminate\Support\Facades\Cache;

/**
 * 🚀 GENERADOR INTELIGENTE DE HORARIOS v8.0 - GENERACIÓN COMPLETA POR NIVEL
 * 
 * INNOVACIONES v8.0:
 * ✅ Generación automática de TODO EL NIVEL (sin seleccionar grado)
 * ✅ Retorna TODOS los horarios del nivel en una sola respuesta
 * ✅ Cache inteligente: recuperación instantánea si ya existe
 * ✅ Visión global: elimina conflictos de profesores entre grados
 * ✅ Rollback completo si falla cualquier grado del nivel
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
    
    private $gradosDelNivel = [];
    private $horariosGeneradosPorGrado = [];

    /**
     * 🎯 MÉTODO PRINCIPAL - GENERA HORARIO COMPLETO DEL NIVEL
     */
    public function generarAutomatico(Request $request, $nivelId)
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

            $nivel = Nivel::findOrFail($nivelId);
            $this->nivelActual = $nivelId;
            
            Log::info('🚀 Iniciando generación v8.0 - NIVEL COMPLETO', [
                'nivel' => $nivel->nombre,
                'year' => $validated['year']
            ]);

            // Preparar configuración
            $this->configuracion = $this->prepararConfiguracion($validated);
            
            // 🔥 OBTENER TODOS LOS GRADOS DEL NIVEL
            $this->gradosDelNivel = Grado::where('nivel_id', $nivelId)
                ->orderBy('nombre')
                ->get();
            
            Log::info('📚 Grados del nivel detectados', [
                'total_grados' => $this->gradosDelNivel->count(),
                'grados' => $this->gradosDelNivel->pluck('nombre_completo')->toArray()
            ]);
            
            // 🔥 ESTRATEGIA INTELIGENTE: Verificar si ya existe horario para TODOS los grados
            $horariosExistentes = true;
            foreach ($this->gradosDelNivel as $grado) {
                if (!Horario::where('grado_id', $grado->id)->where('year', $validated['year'])->exists()) {
                    $horariosExistentes = false;
                    break;
                }
            }
            
            if ($horariosExistentes && !$request->limpiar_existentes) {
                Log::info('⚡ Horarios ya existen para el nivel completo, retornando todos');
                
                // Obtener horarios de TODOS los grados del nivel
                $todosLosHorariosNivel = [];
                foreach ($this->gradosDelNivel as $grado) {
                    $todosLosHorariosNivel[$grado->id] = [
                        'grado' => [
                            'id' => $grado->id,
                            'nombre' => $grado->nombre_completo
                        ],
                        'horarios' => $this->obtenerHorariosGrado($grado->id, $validated['year'])
                    ];
                }
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => '⚡ Horarios del nivel recuperados instantáneamente',
                    'horarios_nivel' => $todosLosHorariosNivel,
                    'estadisticas_nivel' => $this->calcularEstadisticasNivel($validated['year']),
                    'grados_del_nivel' => $this->gradosDelNivel->map(function($g) {
                        return ['id' => $g->id, 'nombre' => $g->nombre_completo];
                    }),
                    'cache_hit' => true,
                    'nivel_completo' => true,
                    'grados_generados' => $this->gradosDelNivel->count(),
                    'estrategia' => 'Horario pre-existente'
                ]);
            }
            
            // 🔥 CARGAR CONTEXTO GLOBAL (todos los niveles)
            $this->cargarContextoGlobalCompleto($validated['year']);
            
            // 🔥 VALIDAR ASIGNACIONES DE TODO EL NIVEL
            $validacionNivel = $this->validarAsignacionesNivel($validated['year']);
            
            if (!$validacionNivel['valido']) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => $validacionNivel['mensaje'],
                    'errores' => $validacionNivel['errores']
                ], 422);
            }
            
            // 🔥 VALIDAR CAPACIDAD DEL HORARIO
            $validacionCapacidad = $this->validarCapacidadHorario($validated);
            
            if (!$validacionCapacidad['valido']) {
                DB::rollBack();
                return $this->respuestaHorarioSaturado(
                    $validacionCapacidad['horas_requeridas'],
                    $validacionCapacidad['capacidad'],
                    count($validated['dias_semana']),
                    $validated
                );
            }
            
            // 🔥 LIMPIAR HORARIOS DE TODO EL NIVEL SI SE SOLICITA
            if ($request->limpiar_existentes) {
                $this->limpiarHorariosNivel($this->nivelActual, $validated['year']);
            }

            // 🔥 GENERAR HORARIOS DE TODO EL NIVEL
            $resultado = $this->generarHorariosNivelCompleto($validated['year']);

            // 🔥 VALIDAR RESULTADO
            if (!$resultado['exito']) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => "⚠️ No se pudo generar el horario completo del nivel",
                    'errores' => $resultado['errores'],
                    'estadisticas' => $resultado['estadisticas_globales'],
                    'diagnostico' => $resultado['diagnostico'],
                    'materias_faltantes' => $resultado['materias_faltantes'] ?? [],
                    'sugerencias' => $this->generarSugerenciasInteligentes($resultado)
                ], 422);
            }
            
            // ✅ ÉXITO: Commit y retornar TODOS los horarios del nivel
            DB::commit();
            
            Log::info('✅ Generación de nivel completa exitosamente', [
                'nivel' => $nivel->nombre,
                'grados_generados' => count($resultado['grados_exitosos'])
            ]);
            
            // Obtener horarios de TODOS los grados del nivel
            $todosLosHorariosNivel = [];
            foreach ($this->gradosDelNivel as $grado) {
                $todosLosHorariosNivel[$grado->id] = [
                    'grado' => [
                        'id' => $grado->id,
                        'nombre' => $grado->nombre_completo
                    ],
                    'horarios' => $this->obtenerHorariosGrado($grado->id, $validated['year'])
                ];
            }
            
            return response()->json([
                'success' => true,
                'message' => '✅ Nivel generado exitosamente (todos los grados optimizados)',
                'horarios_nivel' => $todosLosHorariosNivel,
                'estadisticas_nivel' => $resultado['estadisticas_globales'],
                'estrategia' => $resultado['estrategia_exitosa'] ?? 'Generación por Nivel v8.0',
                'grados_generados' => count($resultado['grados_exitosos']),
                'grados_del_nivel' => $this->gradosDelNivel->map(function($g) {
                    return ['id' => $g->id, 'nombre' => $g->nombre_completo];
                }),
                'nivel_completo' => true
            ]);
            
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

    private function generarHorariosNivelCompleto($year)
    {
        Log::info('🎯 Iniciando generación de NIVEL COMPLETO', [
            'nivel_id' => $this->nivelActual,
            'total_grados' => $this->gradosDelNivel->count()
        ]);
        
        $this->bloqueosPorRazon = [
            'grado_ocupado' => 0,
            'profesor_ocupado' => 0,
            'restriccion_profesor' => 0,
            'duplicado_bd' => 0
        ];
        $this->intentosFallidosPorAsignatura = [];
        $this->horariosGeneradosPorGrado = [];
        
        $estrategias = [
            ['nombre' => 'grados_con_mas_horas_primero', 'desc' => 'Grados con más carga horaria primero', 'intentos' => 3],
            ['nombre' => 'profesores_compartidos_primero', 'desc' => 'Grados con profesores compartidos primero', 'intentos' => 3],
            ['nombre' => 'balance_paralelo', 'desc' => 'Generación balanceada en paralelo', 'intentos' => 5],
            ['nombre' => 'optimizacion_global', 'desc' => 'Optimización global con backtracking', 'intentos' => 5],
            ['nombre' => 'random_ponderado_nivel', 'desc' => 'Aleatorio ponderado por nivel', 'intentos' => 5]
        ];
        
        $mejorResultado = null;
        $mejorPorcentajeGlobal = 0;
        $estrategiaExitosa = '';
        
        foreach ($estrategias as $estrategia) {
            $nombreEstrategia = $estrategia['nombre'];
            $descripcion = $estrategia['desc'];
            
            Log::info("🔄 Probando estrategia de nivel: {$descripcion}");
            
            $this->guardarEstado();
            $this->estrategiaActual = $nombreEstrategia;
            
            $gradosOrdenados = $this->ordenarGradosPorEstrategiaNivel($nombreEstrategia, $year);
            
            $resultadoEstrategia = $this->intentarGeneracionNivel(
                $gradosOrdenados,
                $year,
                $nombreEstrategia,
                $estrategia['intentos']
            );
            
            $porcentajeGlobal = $resultadoEstrategia['porcentaje_global'] ?? 0;
            
            Log::info("📊 Resultado estrategia '{$descripcion}': {$porcentajeGlobal}%", [
                'grados_completos' => $resultadoEstrategia['grados_completos'] ?? 0,
                'grados_totales' => $this->gradosDelNivel->count()
            ]);
            
            if ($porcentajeGlobal > $mejorPorcentajeGlobal) {
                $mejorResultado = $resultadoEstrategia;
                $mejorPorcentajeGlobal = $porcentajeGlobal;
                $estrategiaExitosa = $descripcion;
                
                Log::info("🌟 NUEVO MEJOR RESULTADO: {$porcentajeGlobal}%");
            }
            
            if ($resultadoEstrategia['todos_completos'] ?? false) {
                Log::info("✅ ¡Nivel COMPLETO generado exitosamente!");
                break;
            }
            
            if ($porcentajeGlobal < 100) {
                $this->restaurarEstado();
                $this->limpiarHorariosNivel($this->nivelActual, $year);
            }
        }
        
        if ($mejorResultado) {
            $mejorResultado['estrategia_exitosa'] = $estrategiaExitosa;
            $mejorResultado['exito'] = ($mejorResultado['todos_completos'] ?? false);
        }
        
        return $mejorResultado ?? [
            'exito' => false,
            'estadisticas_globales' => ['porcentaje_global' => 0],
            'errores' => ['No se pudo generar el nivel con ninguna estrategia'],
            'grados_exitosos' => [],
            'todos_completos' => false
        ];
    }

    private function intentarGeneracionNivel($gradosOrdenados, $year, $nombreEstrategia, $maxIntentos)
    {
        $mejorResultado = null;
        $mejorPorcentaje = 0;
        
        for ($intento = 1; $intento <= $maxIntentos; $intento++) {
            Log::info("  📋 Intento #{$intento}/{$maxIntentos} de nivel completo");
            
            $gradosExitosos = [];
            $gradosIncompletos = [];
            $estadisticasPorGrado = [];
            $erroresTotales = [];
            $materiasFaltantes = [];
            
            $todosCompletos = true;
            $horasTotalesAsignadas = 0;
            $horasTotalesRequeridas = 0;
            
            foreach ($gradosOrdenados as $grado) {
                $gradoId = $grado->id;
                
                $asignaciones = AsignacionAcademica::with(['profesor.restricciones', 'asignatura.preferencia'])
                    ->where('grado_id', $gradoId)
                    ->where('year', $year)
                    ->get();
                
                if ($asignaciones->isEmpty()) {
                    continue;
                }
                
                $asignacionesOrdenadas = $this->ordenarPorEstrategiaUltra($asignaciones, $gradoId, $nombreEstrategia);
                
                $horasYaAsignadas = $this->contarHorasAsignadas($gradoId);
                
                $resultadoGrado = $this->intentarAsignacionConFlexibilidad(
                    $gradoId,
                    $asignacionesOrdenadas,
                    $year,
                    $horasYaAsignadas,
                    $intento
                );
                
                $porcentajeGrado = $resultadoGrado['estadisticas']['porcentaje_completado'] ?? 0;
                
                $horasTotalesAsignadas += $resultadoGrado['horas_asignadas'] ?? 0;
                $horasTotalesRequeridas += $asignaciones->sum('horas_semanales');
                
                $estadisticasPorGrado[$gradoId] = $resultadoGrado['estadisticas'];
                
                if ($porcentajeGrado >= 100) {
                    $gradosExitosos[] = $grado->nombre_completo;
                } else {
                    $todosCompletos = false;
                    $gradosIncompletos[] = [
                        'grado' => $grado->nombre_completo,
                        'porcentaje' => $porcentajeGrado
                    ];
                    
                    if (!empty($resultadoGrado['errores'])) {
                        $erroresTotales = array_merge($erroresTotales, $resultadoGrado['errores']);
                    }
                    
                    $faltantes = $this->diagnosticarAsignacionesFallidas($gradoId, $asignaciones, $year);
                    if (!empty($faltantes)) {
                        $materiasFaltantes = array_merge($materiasFaltantes, $faltantes);
                    }
                }
            }
            
            $porcentajeGlobal = $horasTotalesRequeridas > 0
                ? round(($horasTotalesAsignadas / $horasTotalesRequeridas) * 100, 1)
                : 0;
            
            $resultado = [
                'todos_completos' => $todosCompletos,
                'grados_exitosos' => $gradosExitosos,
                'grados_incompletos' => $gradosIncompletos,
                'porcentaje_global' => $porcentajeGlobal,
                'horas_totales_asignadas' => $horasTotalesAsignadas,
                'horas_totales_requeridas' => $horasTotalesRequeridas,
                'estadisticas_globales' => [
                    'total_grados' => $gradosOrdenados->count(),
                    'grados_completos' => count($gradosExitosos),
                    'grados_incompletos' => count($gradosIncompletos),
                    'porcentaje_global' => $porcentajeGlobal,
                    'horas_asignadas' => $horasTotalesAsignadas,
                    'horas_requeridas' => $horasTotalesRequeridas
                ],
                'estadisticas_por_grado' => $estadisticasPorGrado,
                'errores' => $erroresTotales,
                'materias_faltantes' => $materiasFaltantes,
                'diagnostico' => $this->generarDiagnosticoNivel($gradosOrdenados, $year)
            ];
            
            if ($todosCompletos) {
                return $resultado;
            }
            
            if ($porcentajeGlobal > $mejorPorcentaje) {
                $mejorResultado = $resultado;
                $mejorPorcentaje = $porcentajeGlobal;
            }
            
            if ($intento < $maxIntentos) {
                $this->limpiarHorariosNivel($this->nivelActual, $year);
                $gradosOrdenados = $gradosOrdenados->shuffle();
            }
        }
        
        return $mejorResultado ?? [
            'todos_completos' => false,
            'grados_exitosos' => [],
            'porcentaje_global' => 0,
            'estadisticas_globales' => []
        ];
    }

    private function ordenarGradosPorEstrategiaNivel($estrategia, $year)
    {
        return $this->gradosDelNivel->sortBy(function($grado) use ($estrategia, $year) {
            $puntuacion = 0;
            
            $asignaciones = AsignacionAcademica::where('grado_id', $grado->id)
                ->where('year', $year)
                ->get();
            
            $horasRequeridas = $asignaciones->sum('horas_semanales');
            $profesoresUnicos = $asignaciones->pluck('profesor_id')->unique()->count();
            
            switch ($estrategia) {
                case 'grados_con_mas_horas_primero':
                    $puntuacion += $horasRequeridas * 100;
                    break;
                
                case 'profesores_compartidos_primero':
                    $compartidos = 0;
                    foreach ($asignaciones as $asignacion) {
                        $otrosGrados = AsignacionAcademica::where('profesor_id', $asignacion->profesor_id)
                            ->where('year', $year)
                            ->where('grado_id', '!=', $grado->id)
                            ->count();
                        $compartidos += $otrosGrados;
                    }
                    $puntuacion += $compartidos * 200;
                    break;
                
                case 'balance_paralelo':
                    $puntuacion += $profesoresUnicos * 50;
                    $puntuacion += $horasRequeridas * 30;
                    break;
                
                case 'optimizacion_global':
                    $puntuacion += $horasRequeridas * 80;
                    $puntuacion += $profesoresUnicos * 40;
                    break;
                
                case 'random_ponderado_nivel':
                    $puntuacion += rand(0, 1000);
                    $puntuacion += $horasRequeridas * 20;
                    break;
            }
            
            return -$puntuacion;
        })->values();
    }

    private function validarAsignacionesNivel($year)
    {
        $errores = [];
        $gradosSinAsignaciones = [];
        
        foreach ($this->gradosDelNivel as $grado) {
            $asignaciones = AsignacionAcademica::where('grado_id', $grado->id)
                ->where('year', $year)
                ->count();
            
            if ($asignaciones === 0) {
                $gradosSinAsignaciones[] = $grado->nombre_completo;
            }
        }
        
        if (!empty($gradosSinAsignaciones)) {
            return [
                'valido' => false,
                'mensaje' => 'Hay grados sin asignaciones académicas configuradas',
                'errores' => [
                    'grados_afectados' => $gradosSinAsignaciones,
                    'recomendacion' => 'Configure las asignaciones académicas para todos los grados antes de generar horarios'
                ]
            ];
        }
        
        return ['valido' => true];
    }

    private function validarCapacidadHorario($validated)
    {
        $capacidadTotal = count($validated['dias_semana']) * $validated['horas_por_dia'];
        $horasMaximasRequeridas = 0;
        
        foreach ($this->gradosDelNivel as $grado) {
            $horasGrado = AsignacionAcademica::where('grado_id', $grado->id)
                ->where('year', $validated['year'])
                ->sum('horas_semanales');
            
            $horasMaximasRequeridas = max($horasMaximasRequeridas, $horasGrado);
        }
        
        if ($horasMaximasRequeridas > $capacidadTotal) {
            return [
                'valido' => false,
                'horas_requeridas' => $horasMaximasRequeridas,
                'capacidad' => $capacidadTotal
            ];
        }
        
        return ['valido' => true];
    }

    private function limpiarHorariosNivel($nivelId, $year)
    {
        $gradosIds = Grado::where('nivel_id', $nivelId)->pluck('id')->toArray();
        
        Horario::whereIn('grado_id', $gradosIds)
            ->where('year', $year)
            ->delete();
        
        foreach ($gradosIds as $gradoId) {
            $this->limpiarAsignacionesGrado($gradoId);
        }
        
        Log::info('🗑️ Horarios del nivel completo eliminados', [
            'nivel_id' => $nivelId,
            'grados' => count($gradosIds)
        ]);
    }

    private function generarDiagnosticoNivel($grados, $year)
    {
        $diagnostico = [
            'tipo_problema' => '',
            'problema_principal' => '',
            'causas' => [],
            'soluciones' => [],
            'detalles_tecnicos' => []
        ];
        
        $totalBloqueos = array_sum($this->bloqueosPorRazon);
        $profesorOcupado = $this->bloqueosPorRazon['profesor_ocupado'] ?? 0;
        $restricciones = $this->bloqueosPorRazon['restriccion_profesor'] ?? 0;
        
        if ($profesorOcupado > $totalBloqueos * 0.5) {
            $diagnostico['tipo_problema'] = 'profesores_sobrecargados_nivel';
            $diagnostico['problema_principal'] = 'Los profesores del nivel están sobrecargados y se generan conflictos entre grados';
            
            $diagnostico['causas'][] = "Hay {$profesorOcupado} conflictos de profesores ocupados entre los grados del nivel";
            $diagnostico['causas'][] = "Los profesores comparten múltiples grados y no hay suficientes slots disponibles";
            
            $diagnostico['soluciones'][] = "Considere aumentar las horas por día o agregar días a la semana";
            $diagnostico['soluciones'][] = "Redistribuya la carga de profesores entre más docentes";
            $diagnostico['soluciones'][] = "Revise si algunos profesores tienen demasiadas asignaciones";
            
        } elseif ($restricciones > $totalBloqueos * 0.3) {
            $diagnostico['tipo_problema'] = 'restricciones_excesivas_nivel';
            $diagnostico['problema_principal'] = 'Las restricciones de profesores están bloqueando la generación del nivel';
            
            $diagnostico['causas'][] = "Hay {$restricciones} slots bloqueados por restricciones de profesores";
            $diagnostico['soluciones'][] = "Revise las restricciones de profesores que atienden múltiples grados";
            
        } else {
            $diagnostico['tipo_problema'] = 'espacio_insuficiente_nivel';
            $diagnostico['problema_principal'] = 'No hay suficiente espacio para todos los grados del nivel';
            
            $diagnostico['soluciones'][] = "Aumente las horas por día o los días de la semana";
            $diagnostico['soluciones'][] = "Redistribuya las horas entre las asignaturas del nivel";
        }
        
        return $diagnostico;
    }

    private function calcularEstadisticas($gradoId, $year)
    {
        $asignaciones = AsignacionAcademica::where('grado_id', $gradoId)
            ->where('year', $year)
            ->get();
        
        $horarios = Horario::where('grado_id', $gradoId)
            ->where('year', $year)
            ->get();
        
        $totalHorasRequeridas = $asignaciones->sum('horas_semanales');
        $totalHorasProgramadas = $horarios->count();
        
        return [
            'total_asignaciones' => $asignaciones->count(),
            'total_horas_requeridas' => $totalHorasRequeridas,
            'horas_asignadas' => $totalHorasProgramadas,
            'porcentaje_completado' => $totalHorasRequeridas > 0
                ? round(($totalHorasProgramadas / $totalHorasRequeridas) * 100, 1)
                : 0
        ];
    }

    private function cargarContextoGlobalCompleto($year)
    {
        Log::info('🌍 Cargando contexto GLOBAL (todos los niveles y grados)...');

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
            
            $tieneHoraNumero = !empty($restriccion->hora_numero);
            $tieneRangoHoras = !empty($restriccion->hora_inicio) && !empty($restriccion->hora_fin);
            
            if (!$tieneHoraNumero && !$tieneRangoHoras) {
                $restriccionesInvalidas++;
                continue;
            }
            
            $diasAplicables = [];
            if (!empty($dia)) {
                $diasAplicables = [$dia];
            } else {
                $diasAplicables = $this->configuracion['dias_semana'];
            }
            
            $horasBloqueadas = [];
            
            if ($tieneHoraNumero) {
                $horasBloqueadas = [$restriccion->hora_numero];
            } else {
                $horasBloqueadas = $this->convertirRangoTiempoAHoras(
                    $restriccion->hora_inicio,
                    $restriccion->hora_fin
                );
            }
            
            foreach ($diasAplicables as $diaAplicable) {
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
        }

        $this->todosLosHorarios = Horario::where('year', $year)->get();

        foreach ($this->configuracion['dias_semana'] as $dia) {
            $this->matrizGlobal[$dia] = [];
            for ($hora = 1; $hora <= $this->configuracion['horas_por_dia']; $hora++) {
                $this->matrizGlobal[$dia][$hora] = [];
            }
        }

        foreach ($this->todosLosHorarios as $horario) {
            $dia = $horario->dia_semana;
            $hora = $horario->hora_numero;
            $gradoId = $horario->grado_id;
            $profesorId = $horario->profesor_id;

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

        $this->analizarDensidadSlots();
    }
    
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
                $horasBloqueadas = [1];
            }
            
        } catch (\Exception $e) {
            $horasBloqueadas = [1];
        }
        
        return $horasBloqueadas;
    }

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
            }
        }
        
        return $fallidas;
    }

    private function ordenarPorEstrategiaUltra($asignaciones, $gradoId, $estrategia)
    {
        return $asignaciones->sortBy(function($asignacion) use ($estrategia, $gradoId) {
            $puntuacion = 0;

            switch ($estrategia) {
                case 'profesor_menos_ocupado':
                case 'grados_con_mas_horas_primero':
                    $cargaTotal = $this->calcularCargaTotalProfesor($asignacion->profesor_id);
                    $puntuacion += (100 - $cargaTotal) * 100;
                    $puntuacion += ($asignacion->horas_semanales * 10);
                    break;

                case 'balance_dias_estricto':
                case 'balance_paralelo':
                    $puntuacion += ($asignacion->horas_semanales * 200);
                    $gradosQueAtiende = count($this->asignacionesPorProfesor[$asignacion->profesor_id] ?? []);
                    if ($gradosQueAtiende > 1) {
                        $puntuacion += ($gradosQueAtiende * 100);
                    }
                    break;

                case 'profesores_compartidos_primero':
                    $gradosQueAtiende = count($this->asignacionesPorProfesor[$asignacion->profesor_id] ?? []);
                    $puntuacion += ($gradosQueAtiende * 400);
                    break;

                default:
                    $puntuacion += ($asignacion->horas_semanales * 100);
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
            return false;
        }

        if (isset($this->restriccionesProfesores[$profesorId][$dia][$hora])) {
            $this->bloqueosPorRazon['restriccion_profesor']++;
            return false;
        }

        return true;
    }

    private function asignarSlot($gradoId, $asignacion, $slot, $year)
    {
        $dia = $slot['dia'];
        $hora = $slot['hora'];

        if (!$this->esSlotDisponible($gradoId, $asignacion->profesor_id, $dia, $hora)) {
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

            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    private function distribuirSegunEstrategiaConFlexibilidad($slots, $cantidad, $asignacion)
    {
        return $this->distribuirEquilibradamentePorDia($slots, $cantidad);
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

    private function generarSugerenciasInteligentes($resultado)
    {
        $sugerencias = [];
        
        if (!empty($resultado['grados_incompletos'])) {
            $sugerencias[] = "⚠️ Algunos grados del nivel no se completaron totalmente.";
        }
        
        if (!empty($resultado['materias_faltantes'])) {
            $sugerencias[] = "📋 Hay materias sin asignar en algunos grados.";
        }
        
        $sugerencias[] = "💡 Considere aumentar las horas por día o agregar más días a la semana.";
        $sugerencias[] = "💡 Revise las restricciones de profesores que atienden múltiples grados.";
        
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

    private function prepararConfiguracion($validated)
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

    private function calcularEstadisticasNivel($year)
    {
        $totalGrados = $this->gradosDelNivel->count();
        $gradosCompletos = 0;
        $horasTotalesRequeridas = 0;
        $horasTotalesAsignadas = 0;
        
        foreach ($this->gradosDelNivel as $grado) {
            $stats = $this->calcularEstadisticas($grado->id, $year);
            
            $horasTotalesRequeridas += $stats['total_horas_requeridas'];
            $horasTotalesAsignadas += $stats['horas_asignadas'];
            
            if ($stats['porcentaje_completado'] >= 100) {
                $gradosCompletos++;
            }
        }
        
        return [
            'total_grados' => $totalGrados,
            'grados_completos' => $gradosCompletos,
            'grados_incompletos' => $totalGrados - $gradosCompletos,
            'horas_requeridas' => $horasTotalesRequeridas,
            'horas_asignadas' => $horasTotalesAsignadas,
            'porcentaje_global' => $horasTotalesRequeridas > 0
                ? round(($horasTotalesAsignadas / $horasTotalesRequeridas) * 100, 1)
                : 0
        ];
    }
}