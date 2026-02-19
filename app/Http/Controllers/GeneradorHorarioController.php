<?php

namespace App\Http\Controllers;

use App\Http\Requests\Horario\GeneradorHorario\GenerarAutomaticoRequest;
use App\Interfaces\Services\Horario\GeneradorHorarioServiceInterface;

class GeneradorHorarioController extends Controller
{
    public function __construct(
        private GeneradorHorarioServiceInterface $generadorService
    ) {}

    public function generarAutomatico(GenerarAutomaticoRequest $request, $nivelId)
    {
        try {
            $data      = $request->validated();
            $limpiar   = $request->boolean('limpiar_existentes');
            $resultado = $this->generadorService->generarAutomatico($nivelId, $data, $limpiar);

            // Cache hit
            if (!empty($resultado['cache_hit'])) {
                return response()->json([
                    'success'            => true,
                    'message'            => '⚡ Horarios recuperados instantáneamente (cache)',
                    'horarios_nivel'     => $resultado['horarios_nivel'],
                    'estadisticas_nivel' => $resultado['estadisticas_nivel'],
                    'grados_del_nivel'   => $resultado['grados_del_nivel'],
                    'cache_hit'          => true,
                    'nivel_completo'     => true,
                    'grados_generados'   => $resultado['grados_generados'],
                    'estrategia'         => 'Horario pre-existente',
                    'reorganizaciones'   => 0,
                ]);
            }

            // Error de validación de asignaciones
            if (!empty($resultado['error_validacion'])) {
                return response()->json([
                    'success' => false,
                    'message' => $resultado['validacion']['mensaje'],
                    'errores' => $resultado['validacion']['errores'],
                ], 422);
            }

            // Error de capacidad
            if (!empty($resultado['error_capacidad'])) {
                return $this->respuestaHorarioSaturado(
                    $resultado['horas_requeridas'],
                    $resultado['capacidad'],
                    $resultado['total_dias'],
                    $resultado['validated']
                );
            }

            // Error de generación
            if (!empty($resultado['error_generacion'])) {
                $r = $resultado['resultado'];
                return response()->json([
                    'success'                      => false,
                    'message'                      => '⚠️ No se pudo generar el horario completo',
                    'errores'                      => $r['errores'],
                    'estadisticas'                 => $r['estadisticas_globales'],
                    'diagnostico'                  => $r['diagnostico'],
                    'materias_faltantes'           => $r['materias_faltantes'] ?? [],
                    'sugerencias'                  => $resultado['sugerencias'],
                    'reorganizaciones_intentadas'  => $r['reorganizaciones_realizadas'] ?? 0,
                ], 422);
            }

            // Éxito
            return response()->json([
                'success'                  => true,
                'message'                  => '✅ Nivel generado con ÉXITO usando sistema ultra inteligente',
                'horarios_nivel'           => $resultado['horarios_nivel'],
                'estadisticas_nivel'       => $resultado['estadisticas_nivel'],
                'estrategia'               => $resultado['estrategia'],
                'grados_generados'         => $resultado['grados_generados'],
                'grados_del_nivel'         => $resultado['grados_del_nivel'],
                'nivel_completo'           => true,
                'reorganizaciones_realizadas' => $resultado['reorganizaciones_realizadas'],
                'modo_backtracking'        => true,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function respuestaHorarioSaturado(int $totalHorasRequeridas, int $capacidadHorario, int $totalDias, array $validated)
    {
        $diferencia = $totalHorasRequeridas - $capacidadHorario;

        return response()->json([
            'success'  => false,
            'message'  => '⚠️ HORARIO SATURADO',
            'problema' => [
                'horas_requeridas'  => $totalHorasRequeridas,
                'capacidad_horario' => $capacidadHorario,
                'horas_faltantes'   => $diferencia,
            ],
            'soluciones' => [
                "Aumentar horas por día a " . ($validated['horas_por_dia'] + ceil($diferencia / $totalDias)),
                "Agregar " . ceil($diferencia / $validated['horas_por_dia']) . " día(s) más",
                "Reducir {$diferencia} hora(s) de asignaturas",
            ],
        ], 422);
    }
}