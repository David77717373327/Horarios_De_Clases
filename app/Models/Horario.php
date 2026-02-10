<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;

    protected $fillable = [
        'nivel_id',
        'grado_id',
        'asignatura_id',
        'profesor_id',
        'dia_semana',
        'hora_numero',
        'year',
        'hora_inicio',
        'hora_fin',
        'duracion_clase',
        'horas_por_dia',
        'dias_semana',
        'recreo_despues_hora',
        'recreo_duracion',
        'asignacion_academica_id',      // ✨ NUEVO
        'generado_automaticamente',     // ✨ NUEVO
    ];

    protected $casts = [
        'dias_semana' => 'array',
        'hora_inicio' => 'datetime:H:i',
        'hora_fin' => 'datetime:H:i',
        'generado_automaticamente' => 'boolean',  // ✨ NUEVO
    ];

    /* ========================================
       RELACIONES EXISTENTES
    ======================================== */

    /**
     * Nivel al que pertenece el horario
     */
    public function nivel()
    {
        return $this->belongsTo(Nivel::class);
    }

    /**
     * Grado al que pertenece el horario
     */
    public function grado()
    {
        return $this->belongsTo(Grado::class);
    }

    /**
     * Asignatura del horario
     */
    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class);
    }

    /**
     * Profesor asignado
     */
    public function profesor()
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }

    /* ========================================
       NUEVAS RELACIONES
    ======================================== */

    /**
     * Asignación académica que originó este horario
     * (Solo si fue generado automáticamente)
     */
    public function asignacionAcademica()
    {
        return $this->belongsTo(AsignacionAcademica::class);
    }

    /* ========================================
       SCOPES
    ======================================== */

    /**
     * Filtrar por año académico
     */
    public function scopeYear($query, $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Filtrar por nivel
     */
    public function scopeNivel($query, $nivelId)
    {
        return $query->where('nivel_id', $nivelId);
    }

    /**
     * Filtrar por grado
     */
    public function scopeGrado($query, $gradoId)
    {
        return $query->where('grado_id', $gradoId);
    }

    /**
     * Filtrar por día de la semana
     */
    public function scopeDia($query, $dia)
    {
        return $query->where('dia_semana', $dia);
    }

    /**
     * Filtrar por profesor
     */
    public function scopeProfesor($query, $profesorId)
    {
        return $query->where('profesor_id', $profesorId);
    }

    /**
     * Solo horarios generados automáticamente
     */
    public function scopeAutomaticos($query)
    {
        return $query->where('generado_automaticamente', true);
    }

    /**
     * Solo horarios manuales
     */
    public function scopeManuales($query)
    {
        return $query->where('generado_automaticamente', false);
    }

    /* ========================================
       MÉTODOS ÚTILES
    ======================================== */

    /**
     * Verificar si es un recreo
     */
    public function esRecreo()
    {
        return $this->asignatura_id === null && $this->profesor_id === null;
    }

    /**
     * Obtener hora formateada
     */
    public function getHoraFormateadaAttribute()
    {
        return $this->hora_inicio->format('H:i') . ' - ' . $this->hora_fin->format('H:i');
    }

    /**
     * Obtener descripción completa
     */
    public function getDescripcionAttribute()
    {
        if ($this->esRecreo()) {
            return 'Recreo';
        }

        return "{$this->asignatura->nombre} - {$this->profesor->name}";
    }
}