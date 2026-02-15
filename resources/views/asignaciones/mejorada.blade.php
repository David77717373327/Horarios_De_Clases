<?php

namespace App\Services;

use App\Models\{AsignacionAcademica, Horario, Grado, RestriccionProfesor};
use Illuminate\Support\Facades\Log;

/**
 * 🚀 SERVICIO DE GENERACIÓN AUTOMÁTICA DE HORARIOS v12.0 - ULTRA RÁPIDO
 * 
 * ✅ OPTIMIZADO para velocidad y efectividad
 * ✅ Elimina reorganizaciones redundantes
 * ✅ Estrategias enfocadas y eficientes
 * ✅ Backtracking inteligente solo cuando es necesario
 */
class AutoSchedulerService
{
    private array $matrizGlobal = [];
    private array $profesoresOcupados = [];
    private array $restriccionesProfesores = [];
    private array $diasUsadosPorAsignatura = [];
    private array $configuracion = [];
    private array $estadoGuardado = [];
    
    // Control de optimización
    private int $maxIntentosRapido = 3; // Reducido de 15 a 3
    private bool $modoRapido = true;
    
    private array $pesosHeuristicas = [
        'posicion_jornada' => 150,
        'dias_usados' => 100,
        'max_horas_dia' => 80,
        'profesor_libre' => 60
    ];

    /**
     * 🎯 MÉTODO PRINCIPAL - Optimizado para rapidez
     */
    public function generarHorariosNivelCompleto($nivelId, $year, $configuracion, $gradosDelNivel)
    {
        $inicio = microtime(true);
        
        $this->inicializar($nivelId, $configuracion);
        $this->cargarContextoGlobal($year);
        
        Log::info('🎯 Generación OPTIMIZADA v12.0', [
            'nivel' => $nivelId, 
            'grados' => $gradosDelNivel->count()
        ]);
        
        // Estrategias optimizadas - solo las más efectivas
        $estrategias = [
            ['nombre' => 'inteligente_rapido', 'desc' => '⚡ Inteligente rápido', 'intentos' => 3],
            ['nombre' => 'backtracking_eficiente', 'desc' => '🧠 Backtracking eficiente', 'intentos' => 5],
            ['nombre' => 'hibrido_final', 'desc' => '🎯 Híbrido final', 'intentos' => 4]
        ];
        
        $mejorResultado = null;
        $mejorPorcentaje = 0;
        
        foreach ($estrategias as $idx => $estrategia) {
            Log::info("🔄 Estrategia: {$estrategia['desc']}");
            
            $this->guardarEstado();
            $gradosOrdenados = $this->ordenarGradosOptimizado($gradosDelNivel, $year);
            
            $resultado = $this->ejecutarEstrategiaRapida(
                $gradosOrdenados, 
                $year, 
                $estrategia
            );
            
            $porcentaje = $resultado['porcentaje_global'] ?? 0;
            
            Log::info("📊 {$estrategia['desc']}: {$porcentaje}%", [
                'completos' => $resultado['grados_completos'] ?? 0,
                'tiempo' => round(microtime(true) - $inicio, 2) . 's'
            ]);
            
            if ($porcentaje > $mejorPorcentaje) {
                $mejorResultado = $resultado;
                $mejorPorcentaje = $porcentaje;
                $mejorResultado['estrategia_exitosa'] = $estrategia['desc'];
            }
            
            // Si logró 100% o muy alto porcentaje, terminar
            if ($porcentaje >= 100 || ($porcentaje >= 95 && $idx > 0)) {
                Log::info("✅ Objetivo alcanzado: {$porcentaje}%");
                break;
            }
            
            if ($porcentaje < 100) {
                $this->restaurarEstado();
                $this->limpiarHorariosNivel($nivelId, $year);
            }
        }
        
        $tiempoTotal = round(microtime(true) - $inicio, 2);
        Log::info("⏱️ Tiempo total: {$tiempoTotal}s");
        
        return $this->prepararResultadoFinal($mejorResultado);
    }

    /**
     * ⚡ EJECUCIÓN RÁPIDA DE ESTRATEGIA
     */
    private function ejecutarEstrategiaRapida($gradosOrdenados, $year, $estrategia)
    {
        $mejorPorcentaje = 0;
        $mejorResultado = null;
        
        for ($intento = 1; $intento <= $estrategia['intentos']; $intento++) {
            
            $estadisticas = $this->procesarGradosRapido(
                $gradosOrdenados,
                $year,
                $estrategia['nombre'],
                $intento
            );
            
            $resultado = $this->construirResultado($estadisticas, $gradosOrdenados, $year);
            $porcentaje = $resultado['porcentaje_global'];
            
            // Si es perfecto, retornar inmediatamente
            if ($resultado['todos_completos']) {
                return $resultado;
            }
            
            if ($porcentaje > $mejorPorcentaje) {
                $mejorPorcentaje = $porcentaje;
                $mejorResultado = $resultado;
            }
            
            // Si alcanzó 95%+ en los primeros intentos, no insistir más
            if ($porcentaje >= 95 && $intento >= 2) {
                break;
            }
            
            // Limpiar para siguiente intento
            if ($intento < $estrategia['intentos']) {
                $this->limpiarHorariosNivel($this->configuracion['nivel_id'], $year);
            }
        }
        
        return $mejorResultado ?? $this->resultadoVacio();
    }

    /**
     * 📚 PROCESAR GRADOS DE FORMA RÁPIDA
     */
    private function procesarGradosRapido($grados, $year, $estrategia, $flexibilidad)
    {
        $gradosExitosos = [];
        $gradosIncompletos = [];
        $estadisticasPorGrado = [];
        
        foreach ($grados as $grado) {
            $asignaciones = $this->obtenerAsignaciones($grado->id, $year);
            if ($asignaciones->isEmpty()) continue;
            
            $asignacionesOrdenadas = $this->ordenarAsignacionesOptimizado($asignaciones);
            
            $resultado = $this->procesarGradoRapido(
                $grado->id,
                $asignacionesOrdenadas,
                $year,
                $flexibilidad
            );
            
            $porcentaje = $resultado['porcentaje'] ?? 0;
            $estadisticasPorGrado[$grado->id] = $resultado['estadisticas'];
            
            if ($porcentaje >= 100) {
                $gradosExitosos[] = $grado->nombre_completo;
            } else {
                $gradosIncompletos[] = [
                    'grado' => $grado->nombre_completo,
                    'porcentaje' => $porcentaje
                ];
                
                // Solo reorganizar una vez si está muy cerca del objetivo
                if ($porcentaje >= 85 && $porcentaje < 100) {
                    $this->reorganizarUnaVez($grado->id, $asignaciones, $year);
                    
                    // Reintentar solo las faltantes
                    $resultado = $this->procesarGradoRapido(
                        $grado->id,
                        $asignacionesOrdenadas,
                        $year,
                        $flexibilidad + 1
                    );
                    
                    if (($resultado['porcentaje'] ?? 0) >= 100) {
                        $gradosExitosos[] = $grado->nombre_completo;
                        $gradosIncompletos = array_filter(
                            $gradosIncompletos, 
                            fn($g) => $g['grado'] !== $grado->nombre_completo
                        );
                    }
                }
            }
        }
        
        return compact('gradosExitosos', 'gradosIncompletos', 'estadisticasPorGrado');
    }

    /**
     * 📖 PROCESAR UN GRADO RÁPIDAMENTE
     */
    private function procesarGradoRapido($gradoId, $asignaciones, $year, $flexibilidad)
    {
        $horasAsignadas = 0;
        $horasRequeridas = 0;
        $completas = 0;
        $incompletas = 0;
        
        foreach ($asignaciones as $asignacion) {
            $horasRequeridas += $asignacion->horas_semanales;
            $horasActuales = $this->contarHorasAsignacion($gradoId, $asignacion->asignatura_id);
            $horasFaltantes = $asignacion->horas_semanales - $horasActuales;
            
            if ($horasFaltantes <= 0) {
                $completas++;
                $horasAsignadas += $asignacion->horas_semanales;
                continue;
            }
            
            // Buscar slots óptimos de una vez
            $slots = $this->encontrarMejoresSlotsRapido(
                $gradoId,
                $asignacion,
                $horasFaltantes,
                $flexibilidad
            );
            
            $asignadasAhora = 0;
            foreach ($slots as $slot) {
                if ($asignadasAhora >= $horasFaltantes) break;
                
                if ($this->asignarSlot($gradoId, $asignacion, $slot, $year)) {
                    $asignadasAhora++;
                }
            }
            
            if ($asignadasAhora >= $horasFaltantes) {
                $completas++;
                $horasAsignadas += $asignadasAhora;
            } else {
                $incompletas++;
                $horasAsignadas += $asignadasAhora;
            }
        }
        
        $porcentaje = $horasRequeridas > 0 
            ? round(($horasAsignadas / $horasRequeridas) * 100, 1) 
            : 0;
        
        return [
            'porcentaje' => $porcentaje,
            'estadisticas' => [
                'total_horas_requeridas' => $horasRequeridas,
                'horas_asignadas' => $horasAsignadas,
                'asignaciones_completas' => $completas,
                'asignaciones_incompletas' => $incompletas,
                'porcentaje_completado' => $porcentaje
            ]
        ];
    }

    /**
     * 🔍 ENCONTRAR MEJORES SLOTS - VERSIÓN RÁPIDA
     */
    private function encontrarMejoresSlotsRapido($gradoId, $asignacion, $cantidad, $flexibilidad)
    {
        $slots = [];
        $profesorId = $asignacion->profesor_id;
        
        // Calcular restricciones una sola vez
        $restricciones = $this->calcularRestricciones($gradoId, $asignacion);
        
        foreach ($this->configuracion['dias_semana'] as $dia) {
            // Si ya alcanzó el máximo de días, solo buscar en días ya usados
            if ($restricciones['max_dias_alcanzado'] && !in_array($dia, $restricciones['dias_usados'])) {
                continue;
            }
            
            for ($hora = 1; $hora <= $this->configuracion['horas_por_dia']; $hora++) {
                // Validación rápida de disponibilidad
                if (!$this->validacionRapida($gradoId, $profesorId, $dia, $hora, $asignacion)) {
                    continue;
                }
                
                // Verificar max horas por día
                if ($restricciones['max_horas_dia'] > 0) {
                    $horasEnDia = $this->contarHorasEnDia($gradoId, $asignacion->asignatura_id, $profesorId, $dia);
                    if ($horasEnDia >= $restricciones['max_horas_dia']) {
                        continue;
                    }
                }
                
                $puntuacion = $this->calcularPuntuacionRapida(
                    $gradoId,
                    $asignacion,
                    $dia,
                    $hora,
                    $restricciones,
                    $flexibilidad
                );
                
                $slots[] = [
                    'dia' => $dia,
                    'hora' => $hora,
                    'puntuacion' => $puntuacion
                ];
            }
        }
        
        // Ordenar solo una vez
        usort($slots, fn($a, $b) => $b['puntuacion'] <=> $a['puntuacion']);
        
        // Distribuir de forma equilibrada
        return $this->distribuirEquilibrado($slots, $cantidad, $restricciones['dias_usados']);
    }

    /**
     * ⚡ VALIDACIÓN RÁPIDA (sin calcular puntuación)
     */
    private function validacionRapida($gradoId, $profesorId, $dia, $hora, $asignacion)
    {
        // Verificaciones en orden de más rápido a más lento
        if (isset($this->matrizGlobal[$dia][$hora][$gradoId])) return false;
        if (isset($this->profesoresOcupados[$profesorId][$dia][$hora])) return false;
        if (isset($this->restriccionesProfesores[$profesorId][$dia][$hora])) return false;
        
        // Posición en jornada (solo si tiene restricción)
        if (!empty($asignacion->posicion_jornada) && $asignacion->posicion_jornada !== 'sin_restriccion') {
            return $this->cumplePosicionJornada($hora, $asignacion->posicion_jornada);
        }
        
        return true;
    }

    /**
     * 📊 CALCULAR PUNTUACIÓN RÁPIDA
     */
    private function calcularPuntuacionRapida($gradoId, $asignacion, $dia, $hora, $restricciones, $flexibilidad)
    {
        $puntuacion = 100;
        
        // Bonificar días ya usados (reduce fragmentación)
        if (in_array($dia, $restricciones['dias_usados'])) {
            $puntuacion += $this->pesosHeuristicas['dias_usados'];
        }
        
        // Bonificar posición correcta en jornada
        if (!empty($asignacion->posicion_jornada) && $asignacion->posicion_jornada !== 'sin_restriccion') {
            if ($this->cumplePosicionJornada($hora, $asignacion->posicion_jornada)) {
                $puntuacion += $this->pesosHeuristicas['posicion_jornada'];
            }
        }
        
        // Bonificar si el profesor tiene poca carga
        if (!isset($this->profesoresOcupados[$asignacion->profesor_id][$dia])) {
            $puntuacion += $this->pesosHeuristicas['profesor_libre'];
        }
        
        // Penalizar horas tardías para asignaturas difíciles
        if ($asignacion->asignatura->preferencia ?? false) {
            if ($hora > 4) $puntuacion -= 30;
        }
        
        return $puntuacion + ($flexibilidad * 10);
    }

    /**
     * 🔧 REORGANIZAR UNA SOLA VEZ (no en bucle)
     */
    private function reorganizarUnaVez($gradoId, $asignaciones, $year)
    {
        Log::info("🔧 Reorganización única para grado {$gradoId}");
        
        // Identificar solo 5 slots movibles del mismo grado
        $movibles = [];
        foreach ($this->matrizGlobal as $dia => $horas) {
            foreach ($horas as $hora => $grados) {
                if (count($movibles) >= 5) break 2;
                
                if (isset($grados[$gradoId])) {
                    $horario = Horario::find($grados[$gradoId]['id']);
                    if ($horario && $this->esMovible($horario)) {
                        $movibles[] = [
                            'horario' => $horario,
                            'dia_actual' => $dia,
                            'hora_actual' => $hora
                        ];
                    }
                }
            }
        }
        
        // Intentar mover cada uno a mejor posición
        $movidos = 0;
        foreach ($movibles as $info) {
            $asignacion = AsignacionAcademica::find($info['horario']->asignacion_academica_id);
            if (!$asignacion) continue;
            
            // Buscar solo 2 alternativas
            $alternativas = $this->encontrarDosAlternativas($gradoId, $asignacion, $info['dia_actual'], $info['hora_actual'], $year);
            
            foreach ($alternativas as $alt) {
                if ($this->moverSlotSimple($info['horario'], $alt, $gradoId)) {
                    $movidos++;
                    break;
                }
            }
        }
        
        Log::info("✅ Movidos: {$movidos}/5 slots");
        return $movidos > 0;
    }

    /**
     * 🔍 ENCONTRAR SOLO 2 ALTERNATIVAS (no 5)
     */
    private function encontrarDosAlternativas($gradoId, $asignacion, $diaActual, $horaActual, $year)
    {
        $alternativas = [];
        
        foreach ($this->configuracion['dias_semana'] as $dia) {
            if (count($alternativas) >= 2) break;
            
            for ($hora = 1; $hora <= $this->configuracion['horas_por_dia']; $hora++) {
                if ($dia === $diaActual && $hora === $horaActual) continue;
                
                if ($this->validacionRapida($gradoId, $asignacion->profesor_id, $dia, $hora, $asignacion)) {
                    $alternativas[] = ['dia' => $dia, 'hora' => $hora];
                    if (count($alternativas) >= 2) break;
                }
            }
        }
        
        return $alternativas;
    }

    /**
     * 🔄 MOVER SLOT DE FORMA SIMPLE
     */
    private function moverSlotSimple($horario, $nuevoSlot, $gradoId)
    {
        try {
            $diaAnterior = $horario->dia_semana;
            $horaAnterior = $horario->hora_numero;
            $profesorId = $horario->profesor_id;
            
            $horario->dia_semana = $nuevoSlot['dia'];
            $horario->hora_numero = $nuevoSlot['hora'];
            $horario->save();
            
            // Actualizar estructuras
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

    /**
     * 📐 CALCULAR RESTRICCIONES UNA SOLA VEZ
     */
    private function calcularRestricciones($gradoId, $asignacion)
    {
        $cacheKey = "{$gradoId}_{$asignacion->asignatura_id}_{$asignacion->profesor_id}";
        $diasUsados = $this->diasUsadosPorAsignatura[$cacheKey] ?? [];
        
        return [
            'dias_usados' => $diasUsados,
            'max_dias_semana' => $asignacion->max_dias_semana ?? 0,
            'max_horas_dia' => $asignacion->max_horas_por_dia ?? 0,
            'max_dias_alcanzado' => !empty($asignacion->max_dias_semana) && 
                                   count($diasUsados) >= $asignacion->max_dias_semana
        ];
    }

    /**
     * 🎯 DISTRIBUIR EQUILIBRADAMENTE
     */
    private function distribuirEquilibrado($slots, $cantidad, $diasUsados)
    {
        $seleccionados = [];
        
        // Primero: días ya usados
        foreach ($slots as $slot) {
            if (count($seleccionados) >= $cantidad) break;
            if (in_array($slot['dia'], $diasUsados)) {
                $seleccionados[] = $slot;
            }
        }
        
        // Luego: completar con los mejores disponibles
        foreach ($slots as $slot) {
            if (count($seleccionados) >= $cantidad) break;
            if (!in_array($slot, $seleccionados)) {
                $seleccionados[] = $slot;
            }
        }
        
        return $seleccionados;
    }

    /**
     * 💾 ASIGNAR SLOT
     */
    private function asignarSlot($gradoId, $asignacion, $slot, $year)
    {
        try {
            $horario = Horario::create([
                'nivel_id' => $asignacion->grado->nivel_id,
                'grado_id' => $gradoId,
                'asignatura_id' => $asignacion->asignatura_id,
                'profesor_id' => $asignacion->profesor_id,
                'asignacion_academica_id' => $asignacion->id,
                'dia_semana' => $slot['dia'],
                'hora_numero' => $slot['hora'],
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
            
            // Actualizar estructuras
            $this->matrizGlobal[$slot['dia']][$slot['hora']][$gradoId] = [
                'id' => $horario->id,
                'asignatura_id' => $asignacion->asignatura_id,
                'profesor_id' => $asignacion->profesor_id,
                'existente' => false
            ];
            
            $this->profesoresOcupados[$asignacion->profesor_id][$slot['dia']][$slot['hora']] = $gradoId;
            
            $cacheKey = "{$gradoId}_{$asignacion->asignatura_id}_{$asignacion->profesor_id}";
            if (!in_array($slot['dia'], $this->diasUsadosPorAsignatura[$cacheKey] ?? [])) {
                $this->diasUsadosPorAsignatura[$cacheKey][] = $slot['dia'];
            }
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Error asignando: {$e->getMessage()}");
            return false;
        }
    }

    // ============================================================================
    // MÉTODOS AUXILIARES
    // ============================================================================

    private function inicializar($nivelId, $configuracion)
    {
        $this->configuracion = array_merge($configuracion, ['nivel_id' => $nivelId]);
    }

    private function cargarContextoGlobal($year)
    {
        // Cargar restricciones
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
        
        // Inicializar matriz
        foreach ($this->configuracion['dias_semana'] as $dia) {
            for ($hora = 1; $hora <= $this->configuracion['horas_por_dia']; $hora++) {
                $this->matrizGlobal[$dia][$hora] = [];
            }
        }
        
        // Cargar horarios existentes
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

    private function ordenarGradosOptimizado($grados, $year)
    {
        // Ordenar por complejidad (de más complejo a menos complejo)
        return $grados->sortByDesc(function($grado) use ($year) {
            $asignaciones = $this->obtenerAsignaciones($grado->id, $year);
            $complejidad = 0;
            
            foreach ($asignaciones as $a) {
                $complejidad += $a->horas_semanales;
                if (!empty($a->posicion_jornada) && $a->posicion_jornada !== 'sin_restriccion') {
                    $complejidad += 10;
                }
            }
            
            return $complejidad;
        })->values();
    }

    private function ordenarAsignacionesOptimizado($asignaciones)
    {
        // Ordenar por complejidad de restricciones
        return $asignaciones->sortByDesc(function($a) {
            $complejidad = $a->horas_semanales * 10;
            if (!empty($a->posicion_jornada) && $a->posicion_jornada !== 'sin_restriccion') $complejidad += 50;
            if (!empty($a->max_horas_por_dia)) $complejidad += 20;
            if (!empty($a->max_dias_semana)) $complejidad += 20;
            return $complejidad;
        })->values();
    }

    private function cumplePosicionJornada($hora, $posicion)
    {
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

    private function esMovible($horario)
    {
        $asignacion = AsignacionAcademica::find($horario->asignacion_academica_id);
        return $asignacion && !in_array($asignacion->posicion_jornada ?? '', ['primeras_horas', 'ultimas_horas']);
    }

    private function obtenerAsignaciones($gradoId, $year)
    {
        return AsignacionAcademica::with(['profesor', 'asignatura'])
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

    private function contarHorasEnDia($gradoId, $asignaturaId, $profesorId, $dia)
    {
        $total = 0;
        foreach ($this->matrizGlobal[$dia] ?? [] as $grados) {
            if (isset($grados[$gradoId]) && 
                $grados[$gradoId]['asignatura_id'] == $asignaturaId &&
                $grados[$gradoId]['profesor_id'] == $profesorId) {
                $total++;
            }
        }
        return $total;
    }

    private function guardarEstado()
    {
        $this->estadoGuardado = [
            'matriz' => json_decode(json_encode($this->matrizGlobal), true),
            'profesores' => json_decode(json_encode($this->profesoresOcupados), true),
            'dias' => json_decode(json_encode($this->diasUsadosPorAsignatura), true)
        ];
    }

    private function restaurarEstado()
    {
        if (isset($this->estadoGuardado)) {
            $this->matrizGlobal = $this->estadoGuardado['matriz'];
            $this->profesoresOcupados = $this->estadoGuardado['profesores'];
            $this->diasUsadosPorAsignatura = $this->estadoGuardado['dias'];
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

    private function construirResultado($stats, $grados, $year)
    {
        $horasAsignadas = 0;
        $horasRequeridas = 0;
        
        foreach ($stats['estadisticasPorGrado'] as $estadistica) {
            $horasAsignadas += $estadistica['horas_asignadas'] ?? 0;
            $horasRequeridas += $estadistica['total_horas_requeridas'] ?? 0;
        }
        
        $porcentaje = $horasRequeridas > 0 ? round(($horasAsignadas / $horasRequeridas) * 100, 1) : 0;
        $todosCompletos = $porcentaje >= 100;
        
        // Construir lista de materias faltantes
        $materiasFaltantes = [];
        foreach ($stats['estadisticasPorGrado'] as $gradoId => $estadistica) {
            if (($estadistica['asignaciones_incompletas'] ?? 0) > 0) {
                $grado = $grados->firstWhere('id', $gradoId);
                if ($grado) {
                    $materiasFaltantes[] = [
                        'grado' => $grado->nombre_completo,
                        'faltantes' => $estadistica['asignaciones_incompletas']
                    ];
                }
            }
        }
        
        return [
            'todos_completos' => $todosCompletos,
            'grados_exitosos' => $stats['gradosExitosos'] ?? [],
            'grados_incompletos' => $stats['gradosIncompletos'] ?? [],
            'porcentaje_global' => $porcentaje,
            'grados_completos' => count($stats['gradosExitosos'] ?? []),
            'estadisticas_globales' => [
                'total_grados' => $grados->count(),
                'grados_completos' => count($stats['gradosExitosos'] ?? []),
                'grados_incompletos' => count($stats['gradosIncompletos'] ?? []),
                'porcentaje_global' => $porcentaje,
                'horas_asignadas' => $horasAsignadas,
                'horas_requeridas' => $horasRequeridas
            ],
            'estadisticas_por_grado' => $stats['estadisticasPorGrado'] ?? [],
            'errores' => [],
            'materias_faltantes' => $materiasFaltantes
        ];
    }

    private function prepararResultadoFinal($resultado)
    {
        if (!$resultado) {
            return [
                'exito' => false,
                'estadisticas_globales' => ['porcentaje_global' => 0],
                'todos_completos' => false,
                'grados_exitosos' => [],
                'grados_incompletos' => [],
                'errores' => [],
                'materias_faltantes' => [],
                'porcentaje_global' => 0
            ];
        }
        
        // Asegurar que todas las claves necesarias existan
        $resultado['exito'] = $resultado['todos_completos'] ?? false;
        $resultado['errores'] = $resultado['errores'] ?? [];
        $resultado['materias_faltantes'] = $resultado['materias_faltantes'] ?? [];
        
        return $resultado;
    }

    private function resultadoVacio()
    {
        return [
            'todos_completos' => false,
            'grados_exitosos' => [],
            'grados_incompletos' => [],
            'porcentaje_global' => 0,
            'grados_completos' => 0,
            'estadisticas_globales' => [
                'total_grados' => 0,
                'grados_completos' => 0,
                'grados_incompletos' => 0,
                'porcentaje_global' => 0,
                'horas_asignadas' => 0,
                'horas_requeridas' => 0
            ],
            'estadisticas_por_grado' => [],
            'errores' => [],
            'materias_faltantes' => []
        ];
    }
}