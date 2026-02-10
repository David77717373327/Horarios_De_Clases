<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RestriccionProfesor extends Model
{
    use HasFactory;

    protected $table = 'restricciones_profesor';
    
    protected $fillable = [
        'profesor_id',
        'dia_semana',
        'hora_numero',
        'hora_inicio',
        'hora_fin',
        'motivo',
        'year',
        'activa'
    ];

    protected $casts = [
        'hora_numero' => 'integer',
        'year' => 'integer',
        'activa' => 'boolean',
        'hora_inicio' => 'datetime',
        'hora_fin' => 'datetime',
    ];

    // ========================================
    // RELACIONES
    // ========================================

    /**
     * Profesor con la restricción
     */
    public function profesor()
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }

    // ========================================
    // MÉTODOS DE VALIDACIÓN
    // ========================================

    /**
     * Verificar si esta restricción bloquea una hora específica
     */
    public function bloqueaHora($dia, $horaNumero, $hora = null)
    {
        // Si la restricción está inactiva, no bloquea
        if (!$this->activa) {
            return false;
        }

        // Si tiene día específico, verificar que coincida
        // Si dia_semana es NULL, significa que aplica a TODOS los días
        if ($this->dia_semana && $this->dia_semana !== $dia) {
            return false;
        }

        // Método 1: Por número de hora
        if ($this->hora_numero) {
            return $this->hora_numero === $horaNumero;
        }

        // Método 2: Por rango de tiempo
        if ($this->hora_inicio && $this->hora_fin && $hora) {
            $horaConsulta = Carbon::parse($hora);
            $inicio = Carbon::parse($this->hora_inicio);
            $fin = Carbon::parse($this->hora_fin);

            return $horaConsulta->between($inicio, $fin);
        }

        // Método 3: Día completo
        // Si no tiene hora_numero ni rango horario, bloquea todo el día
        if (!$this->hora_numero && !$this->hora_inicio && !$this->hora_fin) {
            return true;
        }

        return false;
    }

    /**
     * Verificar si bloquea un día completo
     */
    public function bloqueaDiaCompleto()
    {
        return !$this->hora_numero && !$this->hora_inicio && !$this->hora_fin;
    }

    /**
     * Verificar si aplica a todos los días de la semana
     */
    public function aplicaTodosLosDias()
    {
        return $this->dia_semana === null || $this->dia_semana === '';
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Filtrar por año académico
     */
    public function scopeYear($query, $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Filtrar solo activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    /**
     * Filtrar por día (incluyendo restricciones que aplican a TODOS los días)
     */
    public function scopeDia($query, $dia)
    {
        return $query->where(function($q) use ($dia) {
            $q->where('dia_semana', $dia)
              ->orWhereNull('dia_semana');
        });
    }

    /**
     * Filtrar por profesor
     */
    public function scopeProfesor($query, $profesorId)
    {
        return $query->where('profesor_id', $profesorId);
    }

    // ========================================
    // MÉTODOS ESTÁTICOS
    // ========================================

    /**
     * Obtener todas las restricciones de un profesor para un año
     */
    public static function restriccionesProfesor($profesorId, $year)
    {
        return self::where('profesor_id', $profesorId)
            ->where('year', $year)
            ->where('activa', true)
            ->orderBy('dia_semana')
            ->orderBy('hora_numero')
            ->get();
    }

    /**
     * Verificar si un profesor tiene restricción en una hora específica
     * 
     * ESTE ES EL MÉTODO CLAVE PARA EL GENERADOR DE HORARIOS
     * 
     * @param int $profesorId ID del profesor
     * @param string $dia Día de la semana (Lunes, Martes, etc.)
     * @param int $horaNumero Número de hora (1-12)
     * @param int $year Año académico
     * @param string|null $hora Hora en formato H:i (opcional)
     * @return bool True si tiene restricción, False si está disponible
     */
    public static function profesorTieneRestriccion($profesorId, $dia, $horaNumero, $year, $hora = null)
    {
        $restricciones = self::where('profesor_id', $profesorId)
            ->where('year', $year)
            ->where('activa', true)
            ->get();

        foreach ($restricciones as $restriccion) {
            if ($restriccion->bloqueaHora($dia, $horaNumero, $hora)) {
                \Log::info("✋ Restricción encontrada", [
                    'profesor_id' => $profesorId,
                    'dia' => $dia,
                    'hora' => $horaNumero,
                    'restriccion' => $restriccion->descripcion
                ]);
                return true;
            }
        }

        return false;
    }

    /**
     * Obtener todas las horas bloqueadas de un profesor para un día específico
     * Útil para mostrar disponibilidad visual
     */
    public static function horasBloqueadasProfesor($profesorId, $dia, $year)
    {
        $restricciones = self::where('profesor_id', $profesorId)
            ->where('year', $year)
            ->where('activa', true)
            ->where(function($q) use ($dia) {
                $q->where('dia_semana', $dia)
                  ->orWhereNull('dia_semana'); // Incluir restricciones de TODOS los días
            })
            ->get();

        $horasBloqueadas = [];

        foreach ($restricciones as $restriccion) {
            if ($restriccion->hora_numero) {
                // Hora específica
                $horasBloqueadas[] = $restriccion->hora_numero;
            } elseif (!$restriccion->hora_inicio && !$restriccion->hora_fin) {
                // Día completo bloqueado - retornar todas las horas
                return range(1, 12);
            }
            // Los rangos horarios se manejan diferente, aquí solo retornamos horas específicas
        }

        return array_unique($horasBloqueadas);
    }

    // ========================================
    // ACCESSORS
    // ========================================

    /**
     * Obtener descripción legible de la restricción
     */
    public function getDescripcionAttribute()
    {
        $partes = [];

        // Día - IMPORTANTE: NULL significa TODOS los días
        if ($this->dia_semana) {
            $partes[] = $this->dia_semana;
        } else {
            $partes[] = "TODOS LOS DÍAS"; // NULL = Lunes a Sábado
        }

        // Hora
        if ($this->hora_numero) {
            $partes[] = "Hora {$this->hora_numero}";
        } elseif ($this->hora_inicio && $this->hora_fin) {
            $partes[] = Carbon::parse($this->hora_inicio)->format('H:i') . 
                       ' - ' . 
                       Carbon::parse($this->hora_fin)->format('H:i');
        } else {
            $partes[] = "Todo el día";
        }

        // Motivo
        if ($this->motivo) {
            $partes[] = "({$this->motivo})";
        }

        return implode(' - ', $partes);
    }

    /**
     * Obtener tipo de restricción
     */
    public function getTipoAttribute()
    {
        if ($this->bloqueaDiaCompleto()) {
            return 'dia_completo';
        } elseif ($this->hora_numero) {
            return 'hora_especifica';
        } else {
            return 'rango_horario';
        }
    }

    /**
     * Obtener el día formateado para mostrar
     */
    public function getDiaFormateadoAttribute()
    {
        return $this->dia_semana ?? 'TODOS LOS DÍAS';
    }
}