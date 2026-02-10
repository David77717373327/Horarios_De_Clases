<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreferenciaAsignatura extends Model
{
    use HasFactory;

    protected $table = 'preferencias_asignatura';
    
    protected $fillable = [
        'asignatura_id',
        'momento_preferido',
        'prioridad',
        'evitar_ultima_hora',
        'evitar_primera_hora',
        'requiere_consecutivas',
        'horas_consecutivas_min',
        'evitar_despues_recreo'
    ];

    protected $casts = [
        'prioridad' => 'integer',
        'evitar_ultima_hora' => 'boolean',
        'evitar_primera_hora' => 'boolean',
        'requiere_consecutivas' => 'boolean',
        'horas_consecutivas_min' => 'integer',
        'evitar_despues_recreo' => 'boolean',
    ];

    // ========================================
    // RELACIONES
    // ========================================

    /**
     * Asignatura asociada
     */
    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class);
    }

    // ========================================
    // MÉTODOS DE VALIDACIÓN
    // ========================================

    /**
     * Verificar si una hora específica es adecuada según las preferencias
     */
    public function esHoraAdecuada($numeroHora, $totalHoras, $esHoraDespuesRecreo = false)
    {
        // Verificar primera hora
        if ($numeroHora === 1 && $this->evitar_primera_hora) {
            return false;
        }

        // Verificar última hora
        if ($numeroHora === $totalHoras && $this->evitar_ultima_hora) {
            return false;
        }

        // Verificar después de recreo
        if ($esHoraDespuesRecreo && $this->evitar_despues_recreo) {
            return false;
        }

        // Verificar momento preferido
        return $this->esHoraDentroDeMomentoPreferido($numeroHora, $totalHoras);
    }

    /**
     * Verificar si la hora está dentro del momento preferido
     */
    public function esHoraDentroDeMomentoPreferido($numeroHora, $totalHoras)
    {
        if ($this->momento_preferido === 'sin_preferencia') {
            return true;
        }

        $tercio = ceil($totalHoras / 3);

        switch ($this->momento_preferido) {
            case 'primeras_horas':
                return $numeroHora <= $tercio;
            
            case 'horas_centrales':
                return $numeroHora > $tercio && $numeroHora <= ($tercio * 2);
            
            case 'ultimas_horas':
                return $numeroHora > ($tercio * 2);
            
            default:
                return true;
        }
    }

    /**
     * Calcular puntuación de idoneidad para una hora
     * Mayor puntuación = más adecuada
     */
    public function calcularPuntuacion($numeroHora, $totalHoras, $esHoraDespuesRecreo = false)
    {
        // Si no es adecuada, puntuación 0
        if (!$this->esHoraAdecuada($numeroHora, $totalHoras, $esHoraDespuesRecreo)) {
            return 0;
        }

        $puntuacion = 100; // Puntuación base

        // Bonus por prioridad de la asignatura
        $puntuacion += ($this->prioridad * 10);

        // Bonus adicional si está en el momento preferido exacto
        if ($this->momento_preferido !== 'sin_preferencia' && 
            $this->esHoraDentroDeMomentoPreferido($numeroHora, $totalHoras)) {
            $puntuacion += 50;
        }

        // Penalización leve si está cerca de horas evitadas
        if ($numeroHora === 2 && $this->evitar_primera_hora) {
            $puntuacion -= 10;
        }
        if ($numeroHora === ($totalHoras - 1) && $this->evitar_ultima_hora) {
            $puntuacion -= 10;
        }

        return $puntuacion;
    }

    // ========================================
    // ACCESSORS
    // ========================================

    /**
     * Obtener descripción legible del momento preferido
     */
    public function getMomentoPreferidoTextoAttribute()
    {
        $textos = [
            'primeras_horas' => 'Primeras horas del día',
            'horas_centrales' => 'Horas centrales',
            'ultimas_horas' => 'Últimas horas',
            'sin_preferencia' => 'Sin preferencia'
        ];

        return $textos[$this->momento_preferido] ?? 'Sin preferencia';
    }

    /**
     * Obtener lista de restricciones activas
     */
    public function getRestriccionesActivasAttribute()
    {
        $restricciones = [];

        if ($this->evitar_primera_hora) {
            $restricciones[] = 'No en primera hora';
        }
        if ($this->evitar_ultima_hora) {
            $restricciones[] = 'No en última hora';
        }
        if ($this->evitar_despues_recreo) {
            $restricciones[] = 'No después de recreo';
        }
        if ($this->requiere_consecutivas) {
            $min = $this->horas_consecutivas_min ?? 2;
            $restricciones[] = "Requiere {$min}+ horas consecutivas";
        }

        return $restricciones;
    }

    /**
     * Obtener nivel de prioridad textual
     */
    public function getNivelPrioridadAttribute()
    {
        if ($this->prioridad >= 8) return 'Muy alta';
        if ($this->prioridad >= 5) return 'Alta';
        if ($this->prioridad >= 3) return 'Media';
        if ($this->prioridad > 0) return 'Baja';
        return 'Sin prioridad';
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Ordenar por prioridad (mayor primero)
     */
    public function scopeOrdenadaPorPrioridad($query)
    {
        return $query->orderByDesc('prioridad');
    }

    /**
     * Filtrar solo con preferencias activas
     */
    public function scopeConPreferencias($query)
    {
        return $query->where(function($q) {
            $q->where('momento_preferido', '!=', 'sin_preferencia')
              ->orWhere('prioridad', '>', 0)
              ->orWhere('evitar_primera_hora', true)
              ->orWhere('evitar_ultima_hora', true)
              ->orWhere('requiere_consecutivas', true)
              ->orWhere('evitar_despues_recreo', true);
        });
    }

    // ========================================
    // MÉTODOS ESTÁTICOS
    // ========================================

    /**
     * Obtener o crear preferencia para una asignatura
     */
    public static function obtenerOCrear($asignaturaId)
    {
        return self::firstOrCreate(
            ['asignatura_id' => $asignaturaId],
            [
                'momento_preferido' => 'sin_preferencia',
                'prioridad' => 0,
                'evitar_ultima_hora' => false,
                'evitar_primera_hora' => false,
                'requiere_consecutivas' => false,
                'evitar_despues_recreo' => false
            ]
        );
    }

    /**
     * Configurar preferencias predeterminadas por tipo de asignatura
     */
    public static function configurarPredeterminadas($asignaturaId, $tipo = null)
    {
        $configs = [
            'educacion_fisica' => [
                'momento_preferido' => 'primeras_horas',
                'prioridad' => 8,
                'evitar_ultima_hora' => true
            ],
            'matematicas' => [
                'momento_preferido' => 'primeras_horas',
                'prioridad' => 7
            ],
            'laboratorio' => [
                'requiere_consecutivas' => true,
                'horas_consecutivas_min' => 2,
                'prioridad' => 6
            ],
            'arte' => [
                'momento_preferido' => 'horas_centrales',
                'prioridad' => 3
            ]
        ];

        $config = $configs[$tipo] ?? [];
        
        return self::updateOrCreate(
            ['asignatura_id' => $asignaturaId],
            $config
        );
    }
}