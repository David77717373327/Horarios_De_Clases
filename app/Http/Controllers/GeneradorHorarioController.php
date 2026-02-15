<?php

namespace App\Http\Controllers;

use App\Models\AsignacionAcademica;
use App\Models\Horario;
use App\Models\Grado;
use App\Models\Nivel;
use App\Services\AutoSchedulerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 🚀 CONTROLADOR DE GENERACIÓN DE HORARIOS v10.0 - ULTRA INTELIGENTE
 * 
 * ✅ Backtracking inteligente
 * ✅ Reorganización dinámica
 * ✅ 10+ intentos con estrategias adaptativas
 * ✅ REQUISITO 1: Posición en jornada
 * ✅ REQUISITO 2: Distribución inteligente
 */
class GeneradorHorarioController extends Controller
{
    protected $schedulerService;

    public function __construct(AutoSchedulerService $schedulerService)
    {
        $this->schedulerService = $schedulerService;
    }

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
            
            Log::info('🚀 Iniciando generación ULTRA INTELIGENTE v10.0', [
                'nivel' => $nivel->nombre,
                'year' => $validated['year']
            ]);

            $configuracion = $this->prepararConfiguracion($validated);
            
            $gradosDelNivel = Grado::where('nivel_id', $nivelId)
                ->orderBy('nombre')
                ->get();
            
            Log::info('📚 Grados detectados', [
                'total_grados' => $gradosDelNivel->count(),
                'grados' => $gradosDelNivel->pluck('nombre_completo')->toArray()
            ]);
            
            // Cache hit check
            if ($this->existenHorariosCompletos($gradosDelNivel, $validated['year']) && !$request->limpiar_existentes) {
                Log::info('⚡ Cache hit - Horarios pre-existentes');
                
                $todosLosHorariosNivel = $this->obtenerHorariosNivel($gradosDelNivel, $validated['year']);
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => '⚡ Horarios recuperados instantáneamente (cache)',
                    'horarios_nivel' => $todosLosHorariosNivel,
                    'estadisticas_nivel' => $this->calcularEstadisticasNivel($gradosDelNivel, $validated['year']),
                    'grados_del_nivel' => $this->formatearGradosInfo($gradosDelNivel),
                    'cache_hit' => true,
                    'nivel_completo' => true,
                    'grados_generados' => $gradosDelNivel->count(),
                    'estrategia' => 'Horario pre-existente',
                    'reorganizaciones' => 0
                ]);
            }
            
            // Validaciones
            $validacionNivel = $this->validarAsignacionesNivel($gradosDelNivel, $validated['year']);
            
            if (!$validacionNivel['valido']) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => $validacionNivel['mensaje'],
                    'errores' => $validacionNivel['errores']
                ], 422);
            }
            
            $validacionCapacidad = $this->validarCapacidadHorario($gradosDelNivel, $validated);
            
            if (!$validacionCapacidad['valido']) {
                DB::rollBack();
                return $this->respuestaHorarioSaturado(
                    $validacionCapacidad['horas_requeridas'],
                    $validacionCapacidad['capacidad'],
                    count($validated['dias_semana']),
                    $validated
                );
            }
            
            if ($request->limpiar_existentes) {
                $this->schedulerService->limpiarHorariosNivel($nivelId, $validated['year']);
            }

            // 🔥 GENERAR CON SISTEMA ULTRA INTELIGENTE
            $resultado = $this->schedulerService->generarHorariosNivelCompleto(
                $nivelId,
                $validated['year'],
                $configuracion,
                $gradosDelNivel
            );

            if (!$resultado['exito']) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => "⚠️ No se pudo generar el horario completo",
                    'errores' => $resultado['errores'],
                    'estadisticas' => $resultado['estadisticas_globales'],
                    'diagnostico' => $resultado['diagnostico'],
                    'materias_faltantes' => $resultado['materias_faltantes'] ?? [],
                    'sugerencias' => $this->generarSugerenciasInteligentes($resultado),
                    'reorganizaciones_intentadas' => $resultado['reorganizaciones_realizadas'] ?? 0
                ], 422);
            }
            
            DB::commit();
            
            Log::info('✅ Generación ULTRA INTELIGENTE completada', [
                'nivel' => $nivel->nombre,
                'grados_generados' => count($resultado['grados_exitosos']),
                'reorganizaciones' => $resultado['reorganizaciones_realizadas'] ?? 0
            ]);
            
            $todosLosHorariosNivel = $this->obtenerHorariosNivel($gradosDelNivel, $validated['year']);
            
            return response()->json([
                'success' => true,
                'message' => '✅ Nivel generado con ÉXITO usando sistema ultra inteligente',
                'horarios_nivel' => $todosLosHorariosNivel,
                'estadisticas_nivel' => $resultado['estadisticas_globales'],
                'estrategia' => $resultado['estrategia_exitosa'] ?? 'Sistema Ultra Inteligente v10.0',
                'grados_generados' => count($resultado['grados_exitosos']),
                'grados_del_nivel' => $this->formatearGradosInfo($gradosDelNivel),
                'nivel_completo' => true,
                'reorganizaciones_realizadas' => $resultado['reorganizaciones_realizadas'] ?? 0,
                'modo_backtracking' => true
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en generación ultra inteligente', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    private function existenHorariosCompletos($gradosDelNivel, $year)
    {
        foreach ($gradosDelNivel as $grado) {
            if (!Horario::where('grado_id', $grado->id)->where('year', $year)->exists()) {
                return false;
            }
        }
        return true;
    }

    private function obtenerHorariosNivel($gradosDelNivel, $year)
    {
        $todosLosHorariosNivel = [];
        
        foreach ($gradosDelNivel as $grado) {
            $todosLosHorariosNivel[$grado->id] = [
                'grado' => [
                    'id' => $grado->id,
                    'nombre' => $grado->nombre_completo
                ],
                'horarios' => $this->obtenerHorariosGrado($grado->id, $year)
            ];
        }
        
        return $todosLosHorariosNivel;
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

    private function validarAsignacionesNivel($gradosDelNivel, $year)
    {
        $gradosSinAsignaciones = [];
        
        foreach ($gradosDelNivel as $grado) {
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
                'mensaje' => 'Hay grados sin asignaciones académicas',
                'errores' => [
                    'grados_afectados' => $gradosSinAsignaciones,
                    'recomendacion' => 'Configure las asignaciones para todos los grados'
                ]
            ];
        }
        
        return ['valido' => true];
    }

    private function validarCapacidadHorario($gradosDelNivel, $validated)
    {
        $capacidadTotal = count($validated['dias_semana']) * $validated['horas_por_dia'];
        $horasMaximasRequeridas = 0;
        
        foreach ($gradosDelNivel as $grado) {
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

    private function calcularEstadisticasNivel($gradosDelNivel, $year)
    {
        $totalGrados = $gradosDelNivel->count();
        $gradosCompletos = 0;
        $horasTotalesRequeridas = 0;
        $horasTotalesAsignadas = 0;
        
        foreach ($gradosDelNivel as $grado) {
            $stats = $this->calcularEstadisticasGrado($grado->id, $year);
            
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

    private function calcularEstadisticasGrado($gradoId, $year)
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

    private function formatearGradosInfo($gradosDelNivel)
    {
        return $gradosDelNivel->map(function($g) {
            return ['id' => $g->id, 'nombre' => $g->nombre_completo];
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

    private function generarSugerenciasInteligentes($resultado)
    {
        $sugerencias = [];
        
        if (!empty($resultado['grados_incompletos'])) {
            $sugerencias[] = "⚠️ Algunos grados no se completaron. El sistema intentó reorganizar pero encontró límites.";
        }
        
        $diagnostico = $resultado['diagnostico'] ?? [];
        $tipProblema = $diagnostico['tipo_problema'] ?? '';
        
        if ($tipProblema === 'restricciones_posicion_jornada') {
            $sugerencias[] = "🕐 Restricciones de 'posición en jornada' muy estrictas.";
            $sugerencias[] = "💡 Cambie algunas materias a 'sin_restriccion'.";
        }
        
        if ($tipProblema === 'restricciones_max_horas_dia' || $tipProblema === 'restricciones_max_dias_semana') {
            $sugerencias[] = "📊 Restricciones de distribución bloqueando slots.";
            $sugerencias[] = "💡 Aumente 'max_horas_por_dia' o 'max_dias_semana'.";
        }
        
        $reorganizaciones = $resultado['reorganizaciones_realizadas'] ?? 0;
        if ($reorganizaciones > 0) {
            $sugerencias[] = "🔄 El sistema intentó {$reorganizaciones} reorganizaciones para optimizar.";
        }
        
        $sugerencias[] = "💡 Considere aumentar horas por día o días de la semana.";
        $sugerencias[] = "💡 Revise restricciones de profesores compartidos entre grados.";
        
        return $sugerencias;
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
}