<?php

namespace App\Services\Horario;

use App\Interfaces\Repositories\HorarioRepositoryInterface;
use App\Interfaces\Services\Horario\AutoSchedulerServiceInterface;
use App\Interfaces\Services\Horario\GeneradorHorarioServiceInterface;
use App\Models\AsignacionAcademica;
use App\Models\Horario;
use Illuminate\Support\Facades\Log;

class GeneradorHorarioService implements GeneradorHorarioServiceInterface
{
    public function __construct(
        private HorarioRepositoryInterface   $horarioRepository,
        private AutoSchedulerServiceInterface $schedulerService
    ) {}

    public function generarAutomatico(int $nivelId, array $data, bool $limpiarExistentes): array
    {
        $nivel          = $this->horarioRepository->findNivel($nivelId);
        $gradosDelNivel = $this->horarioRepository->getGradosDelNivel($nivelId);
        $configuracion  = $this->prepararConfiguracion($data);

        Log::info('🚀 Iniciando generación ULTRA INTELIGENTE v10.0', [
            'nivel' => $nivel->nombre,
            'year'  => $data['year'],
        ]);

        Log::info('📚 Grados detectados', [
            'total_grados' => $gradosDelNivel->count(),
            'grados'       => $gradosDelNivel->pluck('nombre_completo')->toArray(),
        ]);

        // ⚡ Cache hit
        if ($this->existenHorariosCompletos($gradosDelNivel, $data['year']) && !$limpiarExistentes) {
            Log::info('⚡ Cache hit - Horarios pre-existentes');

            return [
                'cache_hit'          => true,
                'horarios_nivel'     => $this->obtenerHorariosNivel($gradosDelNivel, $data['year']),
                'estadisticas_nivel' => $this->calcularEstadisticasNivel($gradosDelNivel, $data['year']),
                'grados_del_nivel'   => $this->formatearGradosInfo($gradosDelNivel),
                'grados_generados'   => $gradosDelNivel->count(),
            ];
        }

        // Validar asignaciones
        $validacionNivel = $this->validarAsignacionesNivel($gradosDelNivel, $data['year']);
        if (!$validacionNivel['valido']) {
            return ['error_validacion' => true, 'validacion' => $validacionNivel];
        }

        // Validar capacidad
        $validacionCapacidad = $this->validarCapacidadHorario($gradosDelNivel, $data);
        if (!$validacionCapacidad['valido']) {
            return [
                'error_capacidad'  => true,
                'horas_requeridas' => $validacionCapacidad['horas_requeridas'],
                'capacidad'        => $validacionCapacidad['capacidad'],
                'total_dias'       => count($data['dias_semana']),
                'validated'        => $data,
            ];
        }

        // Limpiar si se solicita
        if ($limpiarExistentes) {
            $this->schedulerService->limpiarHorariosNivel($nivelId, $data['year']);
        }

        // 🔥 Generar
        $resultado = $this->schedulerService->generarHorariosNivelCompleto(
            $nivelId,
            $data['year'],
            $configuracion,
            $gradosDelNivel
        );

        if (!$resultado['exito']) {
            return [
                'error_generacion'           => true,
                'resultado'                  => $resultado,
                'sugerencias'                => $this->generarSugerenciasInteligentes($resultado),
            ];
        }

        Log::info('✅ Generación ULTRA INTELIGENTE completada', [
            'nivel'           => $nivel->nombre,
            'grados_generados' => count($resultado['grados_exitosos']),
            'reorganizaciones' => $resultado['reorganizaciones_realizadas'] ?? 0,
        ]);

        return [
            'exito'                    => true,
            'horarios_nivel'           => $this->obtenerHorariosNivel($gradosDelNivel, $data['year']),
            'estadisticas_nivel'       => $resultado['estadisticas_globales'],
            'estrategia'               => $resultado['estrategia_exitosa'] ?? 'Sistema Ultra Inteligente v10.0',
            'grados_generados'         => count($resultado['grados_exitosos']),
            'grados_del_nivel'         => $this->formatearGradosInfo($gradosDelNivel),
            'reorganizaciones_realizadas' => $resultado['reorganizaciones_realizadas'] ?? 0,
        ];
    }

    // ─── Métodos privados ────────────────────────────────────────────────────

    private function prepararConfiguracion(array $data): array
    {
        return [
            'hora_inicio'        => $data['hora_inicio'],
            'hora_fin'           => $data['hora_fin'],
            'duracion_clase'     => $data['duracion_clase'],
            'horas_por_dia'      => $data['horas_por_dia'],
            'dias_semana'        => $data['dias_semana'],
            'recreo_despues_hora' => $data['recreo_despues_hora'] ?? null,
            'recreo_duracion'    => $data['recreo_duracion'] ?? null,
        ];
    }

    private function existenHorariosCompletos($gradosDelNivel, int $year): bool
    {
        foreach ($gradosDelNivel as $grado) {
            if (!$this->horarioRepository->existenHorariosGrado($grado->id, $year)) {
                return false;
            }
        }
        return true;
    }

    private function obtenerHorariosNivel($gradosDelNivel, int $year): array
    {
        $resultado = [];
        foreach ($gradosDelNivel as $grado) {
            $resultado[$grado->id] = [
                'grado'    => ['id' => $grado->id, 'nombre' => $grado->nombre_completo],
                'horarios' => $this->obtenerHorariosGrado($grado->id, $year),
            ];
        }
        return $resultado;
    }

    private function obtenerHorariosGrado(int $gradoId, int $year)
    {
        return $this->horarioRepository->getHorariosGrado($gradoId, $year)
            ->map(fn($h) => [
                'id'                       => $h->id,
                'dia_semana'               => $h->dia_semana,
                'hora_numero'              => $h->hora_numero,
                'asignatura_id'            => $h->asignatura_id,
                'profesor_id'              => $h->profesor_id,
                'generado_automaticamente' => $h->generado_automaticamente,
                'asignatura'               => $h->asignatura ? ['id' => $h->asignatura->id, 'nombre' => $h->asignatura->nombre] : null,
                'profesor'                 => $h->profesor   ? ['id' => $h->profesor->id,   'name'   => $h->profesor->name]   : null,
            ]);
    }

    private function validarAsignacionesNivel($gradosDelNivel, int $year): array
    {
        $gradosSinAsignaciones = [];

        foreach ($gradosDelNivel as $grado) {
            if ($this->horarioRepository->getAsignacionesCount($grado->id, $year) === 0) {
                $gradosSinAsignaciones[] = $grado->nombre_completo;
            }
        }

        if (!empty($gradosSinAsignaciones)) {
            return [
                'valido'  => false,
                'mensaje' => 'Hay grados sin asignaciones académicas',
                'errores' => [
                    'grados_afectados' => $gradosSinAsignaciones,
                    'recomendacion'    => 'Configure las asignaciones para todos los grados',
                ],
            ];
        }

        return ['valido' => true];
    }

    private function validarCapacidadHorario($gradosDelNivel, array $data): array
    {
        $capacidadTotal         = count($data['dias_semana']) * $data['horas_por_dia'];
        $horasMaximasRequeridas = 0;

        foreach ($gradosDelNivel as $grado) {
            $horas = $this->horarioRepository->getAsignacionesSum($grado->id, $data['year']);
            $horasMaximasRequeridas = max($horasMaximasRequeridas, $horas);
        }

        if ($horasMaximasRequeridas > $capacidadTotal) {
            return [
                'valido'           => false,
                'horas_requeridas' => $horasMaximasRequeridas,
                'capacidad'        => $capacidadTotal,
            ];
        }

        return ['valido' => true];
    }

    private function calcularEstadisticasNivel($gradosDelNivel, int $year): array
    {
        $totalGrados            = $gradosDelNivel->count();
        $gradosCompletos        = 0;
        $horasTotalesRequeridas = 0;
        $horasTotalesAsignadas  = 0;

        foreach ($gradosDelNivel as $grado) {
            $requeridas = $this->horarioRepository->getAsignacionesSum($grado->id, $year);
            $asignadas  = $this->horarioRepository->getHorariosGrado($grado->id, $year)->count();
            $porcentaje = $requeridas > 0 ? round(($asignadas / $requeridas) * 100, 1) : 0;

            $horasTotalesRequeridas += $requeridas;
            $horasTotalesAsignadas  += $asignadas;

            if ($porcentaje >= 100) $gradosCompletos++;
        }

        return [
            'total_grados'      => $totalGrados,
            'grados_completos'  => $gradosCompletos,
            'grados_incompletos' => $totalGrados - $gradosCompletos,
            'horas_requeridas'  => $horasTotalesRequeridas,
            'horas_asignadas'   => $horasTotalesAsignadas,
            'porcentaje_global' => $horasTotalesRequeridas > 0
                ? round(($horasTotalesAsignadas / $horasTotalesRequeridas) * 100, 1)
                : 0,
        ];
    }

    private function formatearGradosInfo($gradosDelNivel)
    {
        return $gradosDelNivel->map(fn($g) => ['id' => $g->id, 'nombre' => $g->nombre_completo]);
    }

    private function generarSugerenciasInteligentes(array $resultado): array
    {
        $sugerencias = [];
        $diagnostico = $resultado['diagnostico'] ?? [];
        $tipProblema = $diagnostico['tipo_problema'] ?? '';

        if (!empty($resultado['grados_incompletos'])) {
            $sugerencias[] = "⚠️ Algunos grados no se completaron. El sistema intentó reorganizar pero encontró límites.";
        }
        if ($tipProblema === 'restricciones_posicion_jornada') {
            $sugerencias[] = "🕐 Restricciones de 'posición en jornada' muy estrictas.";
            $sugerencias[] = "💡 Cambie algunas materias a 'sin_restriccion'.";
        }
        if (in_array($tipProblema, ['restricciones_max_horas_dia', 'restricciones_max_dias_semana'])) {
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
}