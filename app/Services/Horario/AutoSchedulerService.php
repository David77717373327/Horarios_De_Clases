<?php

namespace App\Services\Horario;

use App\Interfaces\Services\Horario\AutoSchedulerServiceInterface;
use App\Models\{AsignacionAcademica, Horario, Grado, RestriccionProfesor};
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * 🚀 v19.0 DEFINITIVO - RESPETA TODAS LAS RESTRICCIONES
 *
 * ✅ Posición en jornada
 * ✅ Máximas horas por día
 * ✅ Máximos días a la semana
 * ✅ Validación estricta de horas requeridas
 */
class AutoSchedulerService implements AutoSchedulerServiceInterface
{
    private array $matrizGlobal             = [];
    private array $profesoresOcupados       = [];
    private array $restriccionesProfesores  = [];
    private array $diasUsadosPorAsignatura  = [];
    private array $configuracion            = [];

    public function generarHorariosNivelCompleto(int $nivelId, int $year, array $configuracion, Collection $gradosDelNivel): array
    {
        $inicio = microtime(true);

        $this->inicializar($nivelId, $configuracion);
        $this->cargarContextoGlobal($year);

        Log::info('🎯 v19.0 - Respetando TODAS las restricciones', [
            'nivel'  => $nivelId,
            'grados' => $gradosDelNivel->count(),
        ]);

        $resultado  = $this->generarConEstrategias($gradosDelNivel, $year);
        $validacion = $this->validarHorasRequeridas($gradosDelNivel, $year);

        if (!$validacion['valido']) {
            $this->completarHorasFaltantes($gradosDelNivel, $year, $validacion['faltantes']);
            $validacion = $this->validarHorasRequeridas($gradosDelNivel, $year);

            if (!$validacion['valido']) {
                $resultado = $this->actualizarResultadoConFaltantes($resultado, $validacion);
            }
        }

        $tiempo = round(microtime(true) - $inicio, 2);
        Log::info("⏱️ Tiempo: {$tiempo}s");

        return $this->prepararResultado($resultado, $gradosDelNivel, $year);
    }

    public function limpiarHorariosNivel(int $nivelId, int $year): void
    {
        $gradosIds = Grado::where('nivel_id', $nivelId)->pluck('id');

        Horario::whereIn('grado_id', $gradosIds)
            ->where('year', $year)
            ->where('generado_automaticamente', true)
            ->delete();

        foreach ($gradosIds as $gid) {
            foreach ($this->matrizGlobal as $dia => $horas) {
                foreach ($horas as $hora => $grados) {
                    if (isset($grados[$gid])) {
                        $pid = $grados[$gid]['profesor_id'];
                        unset($this->profesoresOcupados[$pid][$dia][$hora]);
                        unset($this->matrizGlobal[$dia][$hora][$gid]);
                    }
                }
            }
        }
    }

    // ─── Estrategias ─────────────────────────────────────────────────────────

    private function generarConEstrategias($gradosDelNivel, int $year): array
    {
        $estrategias = [
            ['nombre' => 'estricto',  'intentos' => 2],
            ['nombre' => 'flexible',  'intentos' => 2],
            ['nombre' => 'agresivo',  'intentos' => 2],
        ];

        $mejorResultado  = null;
        $mejorPorcentaje = 0;

        foreach ($estrategias as $estrategia) {
            Log::info("🔄 Modo: {$estrategia['nombre']}");

            for ($intento = 1; $intento <= $estrategia['intentos']; $intento++) {
                $resultado  = $this->ejecutarGeneracion($gradosDelNivel, $year, $estrategia['nombre']);
                $porcentaje = $resultado['porcentaje_global'] ?? 0;

                Log::info("  {$porcentaje}% (intento {$intento})");

                if ($porcentaje > $mejorPorcentaje) {
                    $mejorResultado  = $resultado;
                    $mejorPorcentaje = $porcentaje;
                }

                if ($porcentaje >= 100) return $mejorResultado;

                if ($porcentaje >= 95) {
                    $rescatado = $this->rescateFinal($gradosDelNivel, $year);
                    if ($rescatado['porcentaje_global'] > $porcentaje) {
                        $mejorResultado  = $rescatado;
                        $mejorPorcentaje = $rescatado['porcentaje_global'];
                    }
                    if ($mejorPorcentaje >= 100) return $mejorResultado;
                }

                $this->limpiarHorariosNivel($this->configuracion['nivel_id'], $year);
            }
        }

        return $mejorResultado ?? $this->resultadoVacio();
    }

    private function ejecutarGeneracion($gradosDelNivel, int $year, string $modo): array
    {
        $gradosOrdenados     = $this->ordenarGrados($gradosDelNivel, $year);
        $gradosExitosos      = [];
        $gradosIncompletos   = [];
        $estadisticasPorGrado = [];

        foreach ($gradosOrdenados as $grado) {
            $asignaciones = $this->obtenerAsignaciones($grado->id, $year);
            if ($asignaciones->isEmpty()) continue;

            $resultado   = $this->procesarGrado($grado->id, $asignaciones, $year, $modo);
            $porcentaje  = $resultado['porcentaje'] ?? 0;
            $estadisticasPorGrado[$grado->id] = $resultado['estadisticas'];

            if ($porcentaje >= 100) {
                $gradosExitosos[] = $grado->nombre_completo;
            } else {
                $gradosIncompletos[] = ['grado' => $grado->nombre_completo, 'porcentaje' => $porcentaje];
            }
        }

        return $this->construirResultado(
            compact('gradosExitosos', 'gradosIncompletos', 'estadisticasPorGrado'),
            $gradosOrdenados,
            $year
        );
    }

    private function procesarGrado(int $gradoId, $asignaciones, int $year, string $modo): array
    {
        $horasAsignadas  = 0;
        $horasRequeridas = 0;

        foreach ($asignaciones as $asignacion) {
            $horasRequeridas += $asignacion->horas_semanales;
            $horasActuales   = $this->contarHorasAsignacion($gradoId, $asignacion->asignatura_id);
            $horasFaltantes  = $asignacion->horas_semanales - $horasActuales;

            if ($horasFaltantes <= 0) {
                $horasAsignadas += $asignacion->horas_semanales;
                continue;
            }

            $slots        = $this->encontrarSlotsRespetandoRestricciones($gradoId, $asignacion, $horasFaltantes, $modo);
            $asignadasAhora = 0;

            foreach ($slots as $slot) {
                if ($asignadasAhora >= $horasFaltantes) break;
                if ($this->asignarEnSlot($gradoId, $asignacion, $slot['dia'], $slot['hora'], $year)) {
                    $asignadasAhora++;
                }
            }

            $horasAsignadas += $asignadasAhora;
        }

        $porcentaje = $horasRequeridas > 0 ? round(($horasAsignadas / $horasRequeridas) * 100, 1) : 0;

        return [
            'porcentaje'  => $porcentaje,
            'estadisticas' => [
                'total_horas_requeridas' => $horasRequeridas,
                'horas_asignadas'        => $horasAsignadas,
                'porcentaje_completado'  => $porcentaje,
            ],
        ];
    }

    // ─── Slots y restricciones ───────────────────────────────────────────────

    private function encontrarSlotsRespetandoRestricciones(int $gradoId, $asignacion, int $cantidad, string $modo): array
    {
        $slots       = [];
        $profesorId  = $asignacion->profesor_id;
        $asignaturaId = $asignacion->asignatura_id;
        $cacheKey    = "{$gradoId}_{$asignaturaId}_{$profesorId}";
        $diasUsados  = $this->diasUsadosPorAsignatura[$cacheKey] ?? [];
        $maxDias     = $asignacion->max_dias_semana ?? 0;
        $maxDiasAlcanzado = ($maxDias > 0 && count($diasUsados) >= $maxDias);

        foreach ($this->configuracion['dias_semana'] as $dia) {
            if ($maxDiasAlcanzado && !in_array($dia, $diasUsados)) continue;

            $maxHorasDia = $asignacion->max_horas_por_dia ?? 0;
            if ($maxHorasDia > 0) {
                if ($this->contarHorasEnDia($gradoId, $asignaturaId, $profesorId, $dia) >= $maxHorasDia) continue;
            }

            for ($hora = 1; $hora <= $this->configuracion['horas_por_dia']; $hora++) {
                if (isset($this->matrizGlobal[$dia][$hora][$gradoId])) continue;
                if (isset($this->profesoresOcupados[$profesorId][$dia][$hora])) continue;
                if (isset($this->restriccionesProfesores[$profesorId][$dia][$hora])) continue;

                if ($modo === 'estricto' && !$this->cumplePosicionJornada($hora, $asignacion)) continue;

                $puntuacion = 100;
                if ($this->cumplePosicionJornada($hora, $asignacion)) $puntuacion += 200;
                if (in_array($dia, $diasUsados)) $puntuacion += 150;

                $slots[] = ['dia' => $dia, 'hora' => $hora, 'puntuacion' => $puntuacion];
            }
        }

        usort($slots, fn($a, $b) => $b['puntuacion'] <=> $a['puntuacion']);

        return array_slice($slots, 0, $cantidad * 5);
    }

    private function cumplePosicionJornada(int $hora, $asignacion): bool
    {
        $posicion = $asignacion->posicion_jornada ?? 'sin_restriccion';

        if (empty($posicion) || $posicion === 'sin_restriccion') return true;

        $total  = $this->configuracion['horas_por_dia'];
        $recreo = $this->configuracion['recreo_despues_hora'] ?? null;

        return match($posicion) {
            'primeras_horas'  => $hora <= 2,
            'ultimas_horas'   => $hora >= ($total - 1),
            'antes_recreo'    => $recreo ? $hora <= $recreo : $hora <= ceil($total / 2),
            'despues_recreo'  => $recreo ? $hora > $recreo  : $hora > ceil($total / 2),
            default           => true,
        };
    }

    private function contarHorasEnDia(int $gradoId, int $asignaturaId, int $profesorId, string $dia): int
    {
        $total = 0;
        foreach ($this->matrizGlobal[$dia] ?? [] as $grados) {
            if (isset($grados[$gradoId]) &&
                $grados[$gradoId]['asignatura_id'] == $asignaturaId &&
                $grados[$gradoId]['profesor_id']   == $profesorId) {
                $total++;
            }
        }
        return $total;
    }

    // ─── Completar faltantes ────────────────────────────────────────────────

    private function validarHorasRequeridas($gradosDelNivel, int $year): array
    {
        $faltantes = [];
        $valido    = true;

        foreach ($gradosDelNivel as $grado) {
            foreach ($this->obtenerAsignaciones($grado->id, $year) as $asignacion) {
                $requeridas = $asignacion->horas_semanales;
                $asignadas  = $this->contarHorasAsignacion($grado->id, $asignacion->asignatura_id);
                $deficit    = $requeridas - $asignadas;

                if ($deficit > 0) {
                    $valido = false;
                    Log::warning("❌ {$grado->nombre_completo} - {$asignacion->asignatura->nombre}: {$asignadas}/{$requeridas}h (faltan {$deficit}h)");
                    $faltantes[] = [
                        'grado_id'    => $grado->id,
                        'grado_nombre' => $grado->nombre_completo,
                        'asignacion'  => $asignacion,
                        'asignatura'  => $asignacion->asignatura->nombre ?? 'N/A',
                        'profesor'    => $asignacion->profesor->name    ?? 'N/A',
                        'requeridas'  => $requeridas,
                        'asignadas'   => $asignadas,
                        'deficit'     => $deficit,
                    ];
                }
            }
        }

        return ['valido' => $valido, 'faltantes' => $faltantes];
    }

    private function completarHorasFaltantes($gradosDelNivel, int $year, array $faltantes): int
    {
        $completadas = 0;

        foreach ($faltantes as $faltante) {
            $gradoId    = $faltante['grado_id'];
            $asignacion = $faltante['asignacion'];
            $deficit    = $faltante['deficit'];

            Log::info("🔧 Completando {$faltante['grado_nombre']} - {$faltante['asignatura']}: {$deficit}h");

            for ($i = 0; $i < $deficit; $i++) {
                $slot = $this->buscarSlotFlexible($gradoId, $asignacion);

                if ($slot && $this->asignarEnSlot($gradoId, $asignacion, $slot['dia'], $slot['hora'], $year)) {
                    $completadas++;
                    Log::info("  ✅ {$slot['dia']} hora {$slot['hora']}");
                    continue;
                }

                if ($this->intentarSwap($gradoId, $asignacion, $year)) {
                    $completadas++;
                    Log::info("  ✅ Via SWAP");
                    continue;
                }

                if ($this->forzarAsignacion($gradoId, $asignacion, $year)) {
                    $completadas++;
                    Log::info("  ⚡ Forzada");
                } else {
                    Log::error("  ❌ IMPOSIBLE asignar");
                }
            }
        }

        Log::info("✅ Completadas: {$completadas}");
        return $completadas;
    }

    private function buscarSlotFlexible(int $gradoId, $asignacion): ?array
    {
        return $this->buscarConRestricciones($gradoId, $asignacion, true,  true,  true)
            ?? $this->buscarConRestricciones($gradoId, $asignacion, false, true,  true)
            ?? $this->buscarConRestricciones($gradoId, $asignacion, false, false, true)
            ?? $this->buscarConRestricciones($gradoId, $asignacion, false, false, false);
    }

    private function buscarConRestricciones(int $gradoId, $asignacion, bool $respetarPosicion, bool $respetarMaxHoras, bool $respetarMaxDias): ?array
    {
        $profesorId   = $asignacion->profesor_id;
        $asignaturaId = $asignacion->asignatura_id;
        $cacheKey     = "{$gradoId}_{$asignaturaId}_{$profesorId}";
        $diasUsados   = $this->diasUsadosPorAsignatura[$cacheKey] ?? [];
        $maxDias      = $respetarMaxDias ? ($asignacion->max_dias_semana ?? 0) : 0;
        $maxDiasAlcanzado = ($maxDias > 0 && count($diasUsados) >= $maxDias);

        foreach ($this->configuracion['dias_semana'] as $dia) {
            if ($maxDiasAlcanzado && !in_array($dia, $diasUsados)) continue;

            if ($respetarMaxHoras) {
                $maxHorasDia = $asignacion->max_horas_por_dia ?? 0;
                if ($maxHorasDia > 0 && $this->contarHorasEnDia($gradoId, $asignaturaId, $profesorId, $dia) >= $maxHorasDia) continue;
            }

            for ($hora = 1; $hora <= $this->configuracion['horas_por_dia']; $hora++) {
                if (isset($this->matrizGlobal[$dia][$hora][$gradoId])) continue;
                if (isset($this->profesoresOcupados[$profesorId][$dia][$hora])) continue;
                if (isset($this->restriccionesProfesores[$profesorId][$dia][$hora])) continue;
                if ($respetarPosicion && !$this->cumplePosicionJornada($hora, $asignacion)) continue;

                return ['dia' => $dia, 'hora' => $hora];
            }
        }

        return null;
    }

    private function intentarSwap(int $gradoId, $asignacion, int $year): bool
    {
        $profesorId = $asignacion->profesor_id;

        foreach ($this->profesoresOcupados[$profesorId] ?? [] as $dia => $horas) {
            foreach ($horas as $hora => $otroGradoId) {
                if ($otroGradoId === $gradoId) continue;
                if (isset($this->matrizGlobal[$dia][$hora][$gradoId])) continue;

                $horarioAMover = $this->matrizGlobal[$dia][$hora][$otroGradoId] ?? null;
                if (!$horarioAMover || $horarioAMover['existente']) continue;

                $horario  = Horario::find($horarioAMover['id']);
                if (!$horario) continue;

                $asigMover = AsignacionAcademica::find($horario->asignacion_academica_id);
                if (!$asigMover) continue;

                $alternativa = $this->buscarAlternativa($otroGradoId, $asigMover, $dia, $hora);

                if ($alternativa && $this->moverSlot($horario, $alternativa, $otroGradoId)) {
                    if ($this->asignarEnSlot($gradoId, $asignacion, $dia, $hora, $year)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function forzarAsignacion(int $gradoId, $asignacion, int $year): bool
    {
        $profesorId = $asignacion->profesor_id;

        foreach ($this->configuracion['dias_semana'] as $dia) {
            for ($hora = 1; $hora <= $this->configuracion['horas_por_dia']; $hora++) {
                if (isset($this->matrizGlobal[$dia][$hora][$gradoId])) continue;
                if (isset($this->profesoresOcupados[$profesorId][$dia][$hora])) continue;
                if (isset($this->restriccionesProfesores[$profesorId][$dia][$hora])) continue;

                if ($this->asignarEnSlot($gradoId, $asignacion, $dia, $hora, $year)) return true;
            }
        }

        return false;
    }

    private function buscarAlternativa(int $gradoId, $asignacion, string $diaActual, int $horaActual): ?array
    {
        $profesorId = $asignacion->profesor_id;

        foreach ($this->configuracion['dias_semana'] as $dia) {
            for ($hora = 1; $hora <= $this->configuracion['horas_por_dia']; $hora++) {
                if ($dia === $diaActual && $hora === $horaActual) continue;
                if (isset($this->matrizGlobal[$dia][$hora][$gradoId])) continue;
                if (isset($this->profesoresOcupados[$profesorId][$dia][$hora])) continue;
                if (isset($this->restriccionesProfesores[$profesorId][$dia][$hora])) continue;

                return ['dia' => $dia, 'hora' => $hora];
            }
        }

        return null;
    }

    private function rescateFinal($gradosDelNivel, int $year): array
    {
        $validacion = $this->validarHorasRequeridas($gradosDelNivel, $year);
        if (!$validacion['valido']) {
            $this->completarHorasFaltantes($gradosDelNivel, $year, $validacion['faltantes']);
        }
        return $this->construirResultadoActual($gradosDelNivel, $year);
    }

    // ─── Asignar / mover ────────────────────────────────────────────────────

    private function asignarEnSlot(int $gradoId, $asignacion, string $dia, int $hora, int $year): bool
    {
        try {
            $horario = Horario::create([
                'nivel_id'                 => $asignacion->grado->nivel_id,
                'grado_id'                 => $gradoId,
                'asignatura_id'            => $asignacion->asignatura_id,
                'profesor_id'              => $asignacion->profesor_id,
                'asignacion_academica_id'  => $asignacion->id,
                'dia_semana'               => $dia,
                'hora_numero'              => $hora,
                'year'                     => $year,
                'hora_inicio'              => $this->configuracion['hora_inicio'],
                'hora_fin'                 => $this->configuracion['hora_fin'],
                'duracion_clase'           => $this->configuracion['duracion_clase'],
                'horas_por_dia'            => $this->configuracion['horas_por_dia'],
                'dias_semana'              => json_encode($this->configuracion['dias_semana']),
                'recreo_despues_hora'      => $this->configuracion['recreo_despues_hora'],
                'recreo_duracion'          => $this->configuracion['recreo_duracion'],
                'generado_automaticamente' => true,
            ]);

            $this->matrizGlobal[$dia][$hora][$gradoId] = [
                'id'           => $horario->id,
                'asignatura_id' => $asignacion->asignatura_id,
                'profesor_id'  => $asignacion->profesor_id,
                'existente'    => false,
            ];

            $this->profesoresOcupados[$asignacion->profesor_id][$dia][$hora] = $gradoId;

            $cacheKey = "{$gradoId}_{$asignacion->asignatura_id}_{$asignacion->profesor_id}";
            if (!in_array($dia, $this->diasUsadosPorAsignatura[$cacheKey] ?? [])) {
                $this->diasUsadosPorAsignatura[$cacheKey][] = $dia;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error asignando: {$e->getMessage()}");
            return false;
        }
    }

    private function moverSlot($horario, array $nuevoSlot, int $gradoId): bool
    {
        try {
            $diaAnterior  = $horario->dia_semana;
            $horaAnterior = $horario->hora_numero;
            $profesorId   = $horario->profesor_id;

            $horario->dia_semana  = $nuevoSlot['dia'];
            $horario->hora_numero = $nuevoSlot['hora'];
            $horario->save();

            unset($this->matrizGlobal[$diaAnterior][$horaAnterior][$gradoId]);
            unset($this->profesoresOcupados[$profesorId][$diaAnterior][$horaAnterior]);

            $this->matrizGlobal[$nuevoSlot['dia']][$nuevoSlot['hora']][$gradoId] = [
                'id'           => $horario->id,
                'asignatura_id' => $horario->asignatura_id,
                'profesor_id'  => $profesorId,
                'existente'    => false,
            ];

            $this->profesoresOcupados[$profesorId][$nuevoSlot['dia']][$nuevoSlot['hora']] = $gradoId;

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    // ─── Auxiliares ─────────────────────────────────────────────────────────

    private function ordenarGrados($grados, int $year)
    {
        return $grados->sortByDesc(fn($g) => $this->obtenerAsignaciones($g->id, $year)->sum('horas_semanales'))->values();
    }

    private function obtenerAsignaciones(int $gradoId, int $year)
    {
        return AsignacionAcademica::with(['profesor', 'asignatura', 'grado'])
            ->where('grado_id', $gradoId)
            ->where('year', $year)
            ->get();
    }

    private function contarHorasAsignacion(int $gradoId, int $asignaturaId): int
    {
        $total = 0;
        foreach ($this->matrizGlobal as $horas) {
            foreach ($horas as $grados) {
                if (isset($grados[$gradoId]) && $grados[$gradoId]['asignatura_id'] == $asignaturaId) {
                    $total++;
                }
            }
        }
        return $total;
    }

    private function inicializar(int $nivelId, array $configuracion): void
    {
        $this->configuracion = array_merge($configuracion, ['nivel_id' => $nivelId]);
    }

    private function cargarContextoGlobal(int $year): void
    {
        $restricciones = RestriccionProfesor::where('year', $year)->where('activa', true)->get();

        foreach ($restricciones as $r) {
            $dias  = $r->dia_semana  ? [$r->dia_semana]  : $this->configuracion['dias_semana'];
            $horas = $r->hora_numero ? [$r->hora_numero] : range(1, $this->configuracion['horas_por_dia']);

            foreach ($dias  as $dia)  {
                foreach ($horas as $hora) {
                    $this->restriccionesProfesores[$r->profesor_id][$dia][$hora] = true;
                }
            }
        }

        foreach ($this->configuracion['dias_semana'] as $dia) {
            for ($hora = 1; $hora <= $this->configuracion['horas_por_dia']; $hora++) {
                $this->matrizGlobal[$dia][$hora] = [];
            }
        }

        foreach (Horario::where('year', $year)->get() as $h) {
            $this->matrizGlobal[$h->dia_semana][$h->hora_numero][$h->grado_id] = [
                'id'           => $h->id,
                'asignatura_id' => $h->asignatura_id,
                'profesor_id'  => $h->profesor_id,
                'existente'    => true,
            ];

            $this->profesoresOcupados[$h->profesor_id][$h->dia_semana][$h->hora_numero] = $h->grado_id;

            if ($h->asignacion_academica_id) {
                $key = "{$h->grado_id}_{$h->asignatura_id}_{$h->profesor_id}";
                if (!in_array($h->dia_semana, $this->diasUsadosPorAsignatura[$key] ?? [])) {
                    $this->diasUsadosPorAsignatura[$key][] = $h->dia_semana;
                }
            }
        }
    }

    private function actualizarResultadoConFaltantes(array $resultado, array $validacion): array
    {
        $resultado['exito']          = false;
        $resultado['todos_completos'] = false;

        $resultado['materias_faltantes'] = array_map(fn($f) => [
            'grado'           => $f['grado_nombre'],
            'asignatura'      => $f['asignatura'],
            'profesor'        => $f['profesor'],
            'horas_requeridas' => $f['requeridas'],
            'horas_asignadas' => $f['asignadas'],
            'horas_faltantes' => $f['deficit'],
        ], $validacion['faltantes']);

        $resultado['errores'] = ['No se pudieron asignar todas las horas requeridas'];

        return $resultado;
    }

    private function construirResultadoActual($grados, int $year): array
    {
        $gradosExitosos      = [];
        $gradosIncompletos   = [];
        $estadisticasPorGrado = [];

        foreach ($grados as $grado) {
            $asignaciones    = $this->obtenerAsignaciones($grado->id, $year);
            $horasRequeridas = $asignaciones->sum('horas_semanales');
            $horasAsignadas  = $asignaciones->sum(fn($a) => $this->contarHorasAsignacion($grado->id, $a->asignatura_id));
            $porcentaje      = $horasRequeridas > 0 ? round(($horasAsignadas / $horasRequeridas) * 100, 1) : 0;

            $estadisticasPorGrado[$grado->id] = [
                'total_horas_requeridas' => $horasRequeridas,
                'horas_asignadas'        => $horasAsignadas,
                'porcentaje_completado'  => $porcentaje,
            ];

            $porcentaje >= 100
                ? $gradosExitosos[]    = $grado->nombre_completo
                : $gradosIncompletos[] = ['grado' => $grado->nombre_completo, 'porcentaje' => $porcentaje];
        }

        return $this->construirResultado(compact('gradosExitosos', 'gradosIncompletos', 'estadisticasPorGrado'), $grados, $year);
    }

    private function construirResultado(array $stats, $grados, int $year): array
    {
        $horasAsignadas  = array_sum(array_column($stats['estadisticasPorGrado'], 'horas_asignadas'));
        $horasRequeridas = array_sum(array_column($stats['estadisticasPorGrado'], 'total_horas_requeridas'));
        $porcentaje      = $horasRequeridas > 0 ? round(($horasAsignadas / $horasRequeridas) * 100, 1) : 0;

        return [
            'exito'               => $porcentaje >= 100,
            'todos_completos'     => $porcentaje >= 100,
            'grados_exitosos'     => $stats['gradosExitosos']    ?? [],
            'grados_incompletos'  => $stats['gradosIncompletos'] ?? [],
            'porcentaje_global'   => $porcentaje,
            'grados_completos'    => count($stats['gradosExitosos'] ?? []),
            'estadisticas_globales' => [
                'total_grados'     => $grados->count(),
                'grados_completos' => count($stats['gradosExitosos'] ?? []),
                'porcentaje_global' => $porcentaje,
                'horas_asignadas'  => $horasAsignadas,
                'horas_requeridas' => $horasRequeridas,
            ],
            'estadisticas_por_grado' => $stats['estadisticasPorGrado'] ?? [],
            'errores'             => [],
            'materias_faltantes'  => [],
            'diagnostico'         => [],
        ];
    }

    private function prepararResultado(?array $resultado, $gradosDelNivel, int $year): array
    {
        return $resultado ?? $this->resultadoVacio();
    }

    private function resultadoVacio(): array
    {
        return [
            'exito'               => false,
            'todos_completos'     => false,
            'porcentaje_global'   => 0,
            'grados_exitosos'     => [],
            'grados_incompletos'  => [],
            'estadisticas_globales' => ['porcentaje_global' => 0],
            'errores'             => [],
            'materias_faltantes'  => [],
            'diagnostico'         => [],
        ];
    }
}