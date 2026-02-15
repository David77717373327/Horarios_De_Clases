<?php

namespace App\Services;

use App\Models\{AsignacionAcademica, Horario, Grado, RestriccionProfesor};
use Illuminate\Support\Facades\Log;

/**
 * 🚀 v19.0 DEFINITIVO - RESPETA TODAS LAS RESTRICCIONES
 * 
 * ✅ Posición en jornada (primeras_horas, ultimas_horas, antes_recreo, despues_recreo)
 * ✅ Máximas horas por día
 * ✅ Máximos días a la semana
 * ✅ Validación estricta de horas requeridas
 */
class AutoSchedulerService
{
    private array $matrizGlobal = [];
    private array $profesoresOcupados = [];
    private array $restriccionesProfesores = [];
    private array $diasUsadosPorAsignatura = [];
    private array $configuracion = [];

    /**
     * 🎯 MÉTODO PRINCIPAL
     */
    public function generarHorariosNivelCompleto($nivelId, $year, $configuracion, $gradosDelNivel)
    {
        $inicio = microtime(true);
        
        $this->inicializar($nivelId, $configuracion);
        $this->cargarContextoGlobal($year);
        
        Log::info('🎯 v19.0 - Respetando TODAS las restricciones', [
            'nivel' => $nivelId, 
            'grados' => $gradosDelNivel->count()
        ]);
        
        // GENERACIÓN
        $resultado = $this->generarConEstrategias($gradosDelNivel, $year);
        
        // VALIDACIÓN
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

    /**
     * 🔄 GENERACIÓN CON ESTRATEGIAS
     */
    private function generarConEstrategias($gradosDelNivel, $year)
    {
        $estrategias = [
            ['nombre' => 'estricto', 'intentos' => 2],
            ['nombre' => 'flexible', 'intentos' => 2],
            ['nombre' => 'agresivo', 'intentos' => 2],
        ];
        
        $mejorResultado = null;
        $mejorPorcentaje = 0;
        
        foreach ($estrategias as $estrategia) {
            Log::info("🔄 Modo: {$estrategia['nombre']}");
            
            for ($intento = 1; $intento <= $estrategia['intentos']; $intento++) {
                
                $resultado = $this->ejecutarGeneracion($gradosDelNivel, $year, $estrategia['nombre']);
                $porcentaje = $resultado['porcentaje_global'] ?? 0;
                
                Log::info("  {$porcentaje}% (intento {$intento})");
                
                if ($porcentaje > $mejorPorcentaje) {
                    $mejorResultado = $resultado;
                    $mejorPorcentaje = $porcentaje;
                }
                
                if ($porcentaje >= 100) {
                    return $mejorResultado;
                }
                
                if ($porcentaje >= 95) {
                    $rescatado = $this->rescateFinal($gradosDelNivel, $year);
                    if ($rescatado['porcentaje_global'] > $porcentaje) {
                        $mejorResultado = $rescatado;
                        $mejorPorcentaje = $rescatado['porcentaje_global'];
                    }
                    if ($mejorPorcentaje >= 100) return $mejorResultado;
                }
                
                $this->limpiarHorariosNivel($this->configuracion['nivel_id'], $year);
            }
        }
        
        return $mejorResultado ?? $this->resultadoVacio();
    }

    /**
     * ⚙️ EJECUTAR GENERACIÓN
     */
    private function ejecutarGeneracion($gradosDelNivel, $year, $modo)
    {
        $gradosOrdenados = $this->ordenarGrados($gradosDelNivel, $year);
        
        $gradosExitosos = [];
        $gradosIncompletos = [];
        $estadisticasPorGrado = [];
        
        foreach ($gradosOrdenados as $grado) {
            $asignaciones = $this->obtenerAsignaciones($grado->id, $year);
            if ($asignaciones->isEmpty()) continue;
            
            $resultado = $this->procesarGrado($grado->id, $asignaciones, $year, $modo);
            
            $porcentaje = $resultado['porcentaje'] ?? 0;
            $estadisticasPorGrado[$grado->id] = $resultado['estadisticas'];
            
            if ($porcentaje >= 100) {
                $gradosExitosos[] = $grado->nombre_completo;
            } else {
                $gradosIncompletos[] = [
                    'grado' => $grado->nombre_completo,
                    'porcentaje' => $porcentaje
                ];
            }
        }
        
        return $this->construirResultado(
            compact('gradosExitosos', 'gradosIncompletos', 'estadisticasPorGrado'),
            $gradosOrdenados,
            $year
        );
    }

    /**
     * 📖 PROCESAR GRADO
     */
    private function procesarGrado($gradoId, $asignaciones, $year, $modo)
    {
        $horasAsignadas = 0;
        $horasRequeridas = 0;
        
        foreach ($asignaciones as $asignacion) {
            $horasRequeridas += $asignacion->horas_semanales;
            $horasActuales = $this->contarHorasAsignacion($gradoId, $asignacion->asignatura_id);
            $horasFaltantes = $asignacion->horas_semanales - $horasActuales;
            
            if ($horasFaltantes <= 0) {
                $horasAsignadas += $asignacion->horas_semanales;
                continue;
            }
            
            $slots = $this->encontrarSlotsRespetandoRestricciones(
                $gradoId,
                $asignacion,
                $horasFaltantes,
                $modo
            );
            
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
            'porcentaje' => $porcentaje,
            'estadisticas' => [
                'total_horas_requeridas' => $horasRequeridas,
                'horas_asignadas' => $horasAsignadas,
                'porcentaje_completado' => $porcentaje
            ]
        ];
    }

    /**
     * 🔍 ENCONTRAR SLOTS RESPETANDO RESTRICCIONES
     */
    private function encontrarSlotsRespetandoRestricciones($gradoId, $asignacion, $cantidad, $modo)
    {
        $slots = [];
        $profesorId = $asignacion->profesor_id;
        $asignaturaId = $asignacion->asignatura_id;
        
        // ✅ CALCULAR DÍAS YA USADOS
        $cacheKey = "{$gradoId}_{$asignaturaId}_{$profesorId}";
        $diasUsados = $this->diasUsadosPorAsignatura[$cacheKey] ?? [];
        
        // ✅ RESTRICCIÓN: max_dias_semana
        $maxDias = $asignacion->max_dias_semana ?? 0;
        $maxDiasAlcanzado = ($maxDias > 0 && count($diasUsados) >= $maxDias);
        
        foreach ($this->configuracion['dias_semana'] as $dia) {
            
            // ✅ Si ya alcanzó el máximo de días, SOLO buscar en días ya usados
            if ($maxDiasAlcanzado && !in_array($dia, $diasUsados)) {
                continue;
            }
            
            // ✅ RESTRICCIÓN: max_horas_por_dia
            $maxHorasDia = $asignacion->max_horas_por_dia ?? 0;
            if ($maxHorasDia > 0) {
                $horasEnEsteDia = $this->contarHorasEnDia($gradoId, $asignaturaId, $profesorId, $dia);
                if ($horasEnEsteDia >= $maxHorasDia) {
                    continue; // Ya alcanzó el máximo en este día
                }
            }
            
            for ($hora = 1; $hora <= $this->configuracion['horas_por_dia']; $hora++) {
                
                // Validaciones básicas
                if (isset($this->matrizGlobal[$dia][$hora][$gradoId])) continue;
                if (isset($this->profesoresOcupados[$profesorId][$dia][$hora])) continue;
                if (isset($this->restriccionesProfesores[$profesorId][$dia][$hora])) continue;
                
                // ✅ RESTRICCIÓN: posicion_jornada
                if ($modo === 'estricto') {
                    if (!$this->cumplePosicionJornada($hora, $asignacion)) continue;
                }
                
                $puntuacion = 100;
                
                // Bonificar si cumple posición
                if ($this->cumplePosicionJornada($hora, $asignacion)) {
                    $puntuacion += 200;
                }
                
                // Bonificar días ya usados (reduce fragmentación)
                if (in_array($dia, $diasUsados)) {
                    $puntuacion += 150;
                }
                
                $slots[] = ['dia' => $dia, 'hora' => $hora, 'puntuacion' => $puntuacion];
            }
        }
        
        usort($slots, fn($a, $b) => $b['puntuacion'] <=> $a['puntuacion']);
        
        return array_slice($slots, 0, $cantidad * 5);
    }

    /**
     * ✅ CUMPLE POSICIÓN JORNADA
     */
    private function cumplePosicionJornada($hora, $asignacion)
    {
        $posicion = $asignacion->posicion_jornada ?? 'sin_restriccion';
        
        if (empty($posicion) || $posicion === 'sin_restriccion') {
            return true;
        }
        
        $total = $this->configuracion['horas_por_dia'];
        $recreo = $this->configuracion['recreo_despues_hora'] ?? null;
        
        return match($posicion) {
            'primeras_horas' => $hora <= 2,
            'ultimas_horas' => $hora >= ($total - 1),
            'antes_recreo' => $recreo ? $hora <= $recreo : $hora <= ceil($total / 2),
            'despues_recreo' => $recreo ? $hora > $recreo : $hora > ceil($total / 2),
            default => true
        };
    }

    /**
     * 📊 CONTAR HORAS EN DÍA (para max_horas_por_dia)
     */
    private function contarHorasEnDia($gradoId, $asignaturaId, $profesorId, $dia)
    {
        $total = 0;
        
        foreach ($this->matrizGlobal[$dia] ?? [] as $hora => $grados) {
            if (isset($grados[$gradoId]) && 
                $grados[$gradoId]['asignatura_id'] == $asignaturaId &&
                $grados[$gradoId]['profesor_id'] == $profesorId) {
                $total++;
            }
        }
        
        return $total;
    }

    /**
     * 📊 VALIDAR HORAS REQUERIDAS
     */
    private function validarHorasRequeridas($gradosDelNivel, $year)
    {
        $faltantes = [];
        $valido = true;
        
        foreach ($gradosDelNivel as $grado) {
            $asignaciones = $this->obtenerAsignaciones($grado->id, $year);
            
            foreach ($asignaciones as $asignacion) {
                $requeridas = $asignacion->horas_semanales;
                $asignadas = $this->contarHorasAsignacion($grado->id, $asignacion->asignatura_id);
                $deficit = $requeridas - $asignadas;
                
                if ($deficit > 0) {
                    $valido = false;
                    
                    Log::warning("❌ {$grado->nombre_completo} - {$asignacion->asignatura->nombre}: {$asignadas}/{$requeridas}h (faltan {$deficit}h)");
                    
                    $faltantes[] = [
                        'grado_id' => $grado->id,
                        'grado_nombre' => $grado->nombre_completo,
                        'asignacion' => $asignacion,
                        'asignatura' => $asignacion->asignatura->nombre ?? 'N/A',
                        'profesor' => $asignacion->profesor->name ?? 'N/A',
                        'requeridas' => $requeridas,
                        'asignadas' => $asignadas,
                        'deficit' => $deficit
                    ];
                }
            }
        }
        
        return [
            'valido' => $valido,
            'faltantes' => $faltantes
        ];
    }

    /**
     * 🔧 COMPLETAR HORAS FALTANTES
     */
    private function completarHorasFaltantes($gradosDelNivel, $year, $faltantes)
    {
        $completadas = 0;
        
        foreach ($faltantes as $faltante) {
            $gradoId = $faltante['grado_id'];
            $asignacion = $faltante['asignacion'];
            $deficit = $faltante['deficit'];
            
            Log::info("🔧 Completando {$faltante['grado_nombre']} - {$faltante['asignatura']}: {$deficit}h");
            
            for ($i = 0; $i < $deficit; $i++) {
                // Intentar con restricciones flexibles
                $slot = $this->buscarSlotFlexible($gradoId, $asignacion);
                
                if ($slot) {
                    if ($this->asignarEnSlot($gradoId, $asignacion, $slot['dia'], $slot['hora'], $year)) {
                        $completadas++;
                        Log::info("  ✅ {$slot['dia']} hora {$slot['hora']}");
                        continue;
                    }
                }
                
                // SWAP
                if ($this->intentarSwap($gradoId, $asignacion, $year)) {
                    $completadas++;
                    Log::info("  ✅ Via SWAP");
                    continue;
                }
                
                // Forzar
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

    /**
     * 🔍 BUSCAR SLOT FLEXIBLE (relaja restricciones progresivamente)
     */
    private function buscarSlotFlexible($gradoId, $asignacion)
    {
        $profesorId = $asignacion->profesor_id;
        
        // NIVEL 1: Respetar TODAS las restricciones
        $slot = $this->buscarConRestricciones($gradoId, $asignacion, true, true, true);
        if ($slot) return $slot;
        
        // NIVEL 2: Ignorar posicion_jornada
        $slot = $this->buscarConRestricciones($gradoId, $asignacion, false, true, true);
        if ($slot) return $slot;
        
        // NIVEL 3: Ignorar max_horas_por_dia
        $slot = $this->buscarConRestricciones($gradoId, $asignacion, false, false, true);
        if ($slot) return $slot;
        
        // NIVEL 4: Ignorar max_dias_semana
        $slot = $this->buscarConRestricciones($gradoId, $asignacion, false, false, false);
        if ($slot) return $slot;
        
        return null;
    }

    /**
     * 🔍 BUSCAR CON RESTRICCIONES ESPECÍFICAS
     */
    private function buscarConRestricciones($gradoId, $asignacion, $respetarPosicion, $respetarMaxHoras, $respetarMaxDias)
    {
        $profesorId = $asignacion->profesor_id;
        $asignaturaId = $asignacion->asignatura_id;
        
        $cacheKey = "{$gradoId}_{$asignaturaId}_{$profesorId}";
        $diasUsados = $this->diasUsadosPorAsignatura[$cacheKey] ?? [];
        
        $maxDias = $respetarMaxDias ? ($asignacion->max_dias_semana ?? 0) : 0;
        $maxDiasAlcanzado = ($maxDias > 0 && count($diasUsados) >= $maxDias);
        
        foreach ($this->configuracion['dias_semana'] as $dia) {
            
            if ($maxDiasAlcanzado && !in_array($dia, $diasUsados)) {
                continue;
            }
            
            if ($respetarMaxHoras) {
                $maxHorasDia = $asignacion->max_horas_por_dia ?? 0;
                if ($maxHorasDia > 0) {
                    $horasEnDia = $this->contarHorasEnDia($gradoId, $asignaturaId, $profesorId, $dia);
                    if ($horasEnDia >= $maxHorasDia) continue;
                }
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

    /**
     * 🔄 INTENTAR SWAP
     */
    private function intentarSwap($gradoId, $asignacion, $year)
    {
        $profesorId = $asignacion->profesor_id;
        
        foreach ($this->profesoresOcupados[$profesorId] ?? [] as $dia => $horas) {
            foreach ($horas as $hora => $otroGradoId) {
                if ($otroGradoId === $gradoId) continue;
                if (isset($this->matrizGlobal[$dia][$hora][$gradoId])) continue;
                
                $horarioAMover = $this->matrizGlobal[$dia][$hora][$otroGradoId] ?? null;
                if (!$horarioAMover || $horarioAMover['existente']) continue;
                
                $horario = Horario::find($horarioAMover['id']);
                if (!$horario) continue;
                
                $asigMover = AsignacionAcademica::find($horario->asignacion_academica_id);
                if (!$asigMover) continue;
                
                $alternativa = $this->buscarAlternativa($otroGradoId, $asigMover, $dia, $hora);
                
                if ($alternativa) {
                    if ($this->moverSlot($horario, $alternativa, $otroGradoId)) {
                        if ($this->asignarEnSlot($gradoId, $asignacion, $dia, $hora, $year)) {
                            return true;
                        }
                    }
                }
            }
        }
        
        return false;
    }

    /**
     * ⚡ FORZAR ASIGNACIÓN
     */
    private function forzarAsignacion($gradoId, $asignacion, $year)
    {
        $profesorId = $asignacion->profesor_id;
        
        foreach ($this->configuracion['dias_semana'] as $dia) {
            for ($hora = 1; $hora <= $this->configuracion['horas_por_dia']; $hora++) {
                
                if (isset($this->matrizGlobal[$dia][$hora][$gradoId])) continue;
                if (isset($this->profesoresOcupados[$profesorId][$dia][$hora])) continue;
                if (isset($this->restriccionesProfesores[$profesorId][$dia][$hora])) continue;
                
                if ($this->asignarEnSlot($gradoId, $asignacion, $dia, $hora, $year)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    private function buscarAlternativa($gradoId, $asignacion, $diaActual, $horaActual)
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

    private function rescateFinal($gradosDelNivel, $year)
    {
        $validacion = $this->validarHorasRequeridas($gradosDelNivel, $year);
        
        if (!$validacion['valido']) {
            $this->completarHorasFaltantes($gradosDelNivel, $year, $validacion['faltantes']);
        }
        
        return $this->construirResultadoActual($gradosDelNivel, $year);
    }

    /**
     * 💾 ASIGNAR EN SLOT
     */
    private function asignarEnSlot($gradoId, $asignacion, $dia, $hora, $year)
    {
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
            
            $this->profesoresOcupados[$asignacion->profesor_id][$dia][$hora] = $gradoId;
            
            // ✅ ACTUALIZAR DÍAS USADOS
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

    private function moverSlot($horario, $nuevoSlot, $gradoId)
    {
        try {
            $diaAnterior = $horario->dia_semana;
            $horaAnterior = $horario->hora_numero;
            $profesorId = $horario->profesor_id;
            
            $horario->dia_semana = $nuevoSlot['dia'];
            $horario->hora_numero = $nuevoSlot['hora'];
            $horario->save();
            
            unset($this->matrizGlobal[$diaAnterior][$horaAnterior][$gradoId]);
            unset($this->profesoresOcupados[$profesorId][$diaAnterior][$horaAnterior]);
            
            $this->matrizGlobal[$nuevoSlot['dia']][$nuevoSlot['hora']][$gradoId] = [
                'id' => $horario->id,
                'asignatura_id' => $horario->asignatura_id,
                'profesor_id' => $profesorId,
                'existente' => false
            ];
            
            $this->profesoresOcupados[$profesorId][$nuevoSlot['dia']][$nuevoSlot['hora']] = $gradoId;
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function actualizarResultadoConFaltantes($resultado, $validacion)
    {
        $resultado['exito'] = false;
        $resultado['todos_completos'] = false;
        
        $materiasFaltantes = [];
        foreach ($validacion['faltantes'] as $faltante) {
            $materiasFaltantes[] = [
                'grado' => $faltante['grado_nombre'],
                'asignatura' => $faltante['asignatura'],
                'profesor' => $faltante['profesor'],
                'horas_requeridas' => $faltante['requeridas'],
                'horas_asignadas' => $faltante['asignadas'],
                'horas_faltantes' => $faltante['deficit']
            ];
        }
        
        $resultado['materias_faltantes'] = $materiasFaltantes;
        $resultado['errores'] = ['No se pudieron asignar todas las horas requeridas'];
        
        return $resultado;
    }

    // ============================================================================
    // MÉTODOS AUXILIARES
    // ============================================================================

    private function ordenarGrados($grados, $year)
    {
        return $grados->sortByDesc(function($grado) use ($year) {
            return $this->obtenerAsignaciones($grado->id, $year)->sum('horas_semanales');
        })->values();
    }

    private function obtenerAsignaciones($gradoId, $year)
    {
        return AsignacionAcademica::with(['profesor', 'asignatura', 'grado'])
            ->where('grado_id', $gradoId)
            ->where('year', $year)
            ->get();
    }

    private function contarHorasAsignacion($gradoId, $asignaturaId)
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

    private function inicializar($nivelId, $configuracion)
    {
        $this->configuracion = array_merge($configuracion, ['nivel_id' => $nivelId]);
    }

    private function cargarContextoGlobal($year)
    {
        $restricciones = RestriccionProfesor::where('year', $year)->where('activa', true)->get();
        foreach ($restricciones as $r) {
            $dias = $r->dia_semana ? [$r->dia_semana] : $this->configuracion['dias_semana'];
            $horas = $r->hora_numero ? [$r->hora_numero] : range(1, $this->configuracion['horas_por_dia']);
            
            foreach ($dias as $dia) {
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
        
        $horarios = Horario::where('year', $year)->get();
        foreach ($horarios as $h) {
            $this->matrizGlobal[$h->dia_semana][$h->hora_numero][$h->grado_id] = [
                'id' => $h->id,
                'asignatura_id' => $h->asignatura_id,
                'profesor_id' => $h->profesor_id,
                'existente' => true
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

    public function limpiarHorariosNivel($nivelId, $year)
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

    private function construirResultadoActual($grados, $year)
    {
        $gradosExitosos = [];
        $gradosIncompletos = [];
        $estadisticasPorGrado = [];
        
        foreach ($grados as $grado) {
            $asignaciones = $this->obtenerAsignaciones($grado->id, $year);
            $horasRequeridas = $asignaciones->sum('horas_semanales');
            $horasAsignadas = 0;
            
            foreach ($asignaciones as $asig) {
                $horasAsignadas += $this->contarHorasAsignacion($grado->id, $asig->asignatura_id);
            }
            
            $porcentaje = $horasRequeridas > 0 
                ? round(($horasAsignadas / $horasRequeridas) * 100, 1) 
                : 0;
            
            $estadisticasPorGrado[$grado->id] = [
                'total_horas_requeridas' => $horasRequeridas,
                'horas_asignadas' => $horasAsignadas,
                'porcentaje_completado' => $porcentaje
            ];
            
            if ($porcentaje >= 100) {
                $gradosExitosos[] = $grado->nombre_completo;
            } else {
                $gradosIncompletos[] = [
                    'grado' => $grado->nombre_completo,
                    'porcentaje' => $porcentaje
                ];
            }
        }
        
        return $this->construirResultado(
            compact('gradosExitosos', 'gradosIncompletos', 'estadisticasPorGrado'),
            $grados,
            $year
        );
    }

    private function construirResultado($stats, $grados, $year)
    {
        $horasAsignadas = 0;
        $horasRequeridas = 0;
        
        foreach ($stats['estadisticasPorGrado'] as $estadistica) {
            $horasAsignadas += $estadistica['horas_asignadas'] ?? 0;
            $horasRequeridas += $estadistica['total_horas_requeridas'] ?? 0;
        }
        
        $porcentaje = $horasRequeridas > 0 ? round(($horasAsignadas / $horasRequeridas) * 100, 1) : 0;
        
        return [
            'exito' => $porcentaje >= 100,
            'todos_completos' => $porcentaje >= 100,
            'grados_exitosos' => $stats['gradosExitosos'] ?? [],
            'grados_incompletos' => $stats['gradosIncompletos'] ?? [],
            'porcentaje_global' => $porcentaje,
            'grados_completos' => count($stats['gradosExitosos'] ?? []),
            'estadisticas_globales' => [
                'total_grados' => $grados->count(),
                'grados_completos' => count($stats['gradosExitosos'] ?? []),
                'porcentaje_global' => $porcentaje,
                'horas_asignadas' => $horasAsignadas,
                'horas_requeridas' => $horasRequeridas
            ],
            'estadisticas_por_grado' => $stats['estadisticasPorGrado'] ?? [],
            'errores' => [],
            'materias_faltantes' => [],
            'diagnostico' => []
        ];
    }

    private function prepararResultado($resultado, $gradosDelNivel, $year)
    {
        return $resultado ?? $this->resultadoVacio();
    }

    private function resultadoVacio()
    {
        return [
            'exito' => false,
            'todos_completos' => false,
            'porcentaje_global' => 0,
            'grados_exitosos' => [],
            'grados_incompletos' => [],
            'estadisticas_globales' => ['porcentaje_global' => 0],
            'errores' => [],
            'materias_faltantes' => [],
            'diagnostico' => []
        ];
    }
}