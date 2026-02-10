<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asignatura extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
    ];

    /* ========================================
       RELACIONES EXISTENTES
    ======================================== */

    /**
     * Horarios donde se usa esta asignatura
     */
    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    /**
     * Profesores que pueden impartir esta asignatura
     */
    public function profesores()
    {
        return $this->belongsToMany(User::class, 'asignatura_profesor');
    }

    /* ========================================
       NUEVAS RELACIONES
    ======================================== */

    /**
     * Preferencia horaria de la asignatura
     * (Para generación automática: primeras horas, etc.)
     */
    public function preferencia()
    {
        return $this->hasOne(PreferenciaAsignatura::class);
    }

    /**
     * Asignaciones académicas de esta asignatura
     */
    public function asignaciones()
    {
        return $this->hasMany(AsignacionAcademica::class);
    }

    /* ========================================
       SCOPES
    ======================================== */

    /**
     * Ordenar alfabéticamente
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('nombre');
    }

    /**
     * Buscar por nombre
     */
    public function scopeBuscar($query, $termino)
    {
        return $query->where('nombre', 'like', "%{$termino}%");
    }

    /* ========================================
       MÉTODOS ÚTILES
    ======================================== */

    /**
     * Obtener o crear preferencia
     */
    public function obtenerPreferencia()
    {
        if (!$this->preferencia) {
            return PreferenciaAsignatura::create([
                'asignatura_id' => $this->id,
                'momento_preferido' => 'sin_preferencia',
                'prioridad' => 0
            ]);
        }
        
        return $this->preferencia;
    }

    /**
     * Verificar si tiene preferencias configuradas
     */
    public function tienePreferencias()
    {
        return $this->preferencia && (
            $this->preferencia->momento_preferido !== 'sin_preferencia' ||
            $this->preferencia->prioridad > 0 ||
            $this->preferencia->evitar_primera_hora ||
            $this->preferencia->evitar_ultima_hora
        );
    }

    /**
     * Obtener total de profesores habilitados
     */
    public function getTotalProfesoresAttribute()
    {
        return $this->profesores()->count();
    }

    /**
     * Obtener grados donde se imparte (en un año específico)
     */
    public function gradosQueImparte($year)
    {
        return Grado::whereHas('asignaciones', function($query) use ($year) {
            $query->where('asignatura_id', $this->id)
                  ->where('year', $year);
        })->with('nivel')->get();
    }

    /**
     * Obtener total de horas asignadas en un año
     */
    public function totalHorasAsignadas($year)
    {
        return $this->asignaciones()
            ->where('year', $year)
            ->sum('horas_semanales');
    }

    /**
     * Obtener total de horas programadas en horarios
     */
    public function totalHorasProgramadas($year)
    {
        return $this->horarios()
            ->where('year', $year)
            ->count();
    }

    /**
     * Verificar si un profesor puede dar esta asignatura
     */
    public function profesorPuedeImpartir($profesorId)
    {
        return $this->profesores->contains($profesorId);
    }
}