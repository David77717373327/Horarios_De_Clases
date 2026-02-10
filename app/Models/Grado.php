<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grado extends Model
{
    use HasFactory;

    protected $table = 'grados';

    protected $fillable = [
        'nombre',
        'nivel_id',
        'orden'
    ];

    /* ========================================
       RELACIONES EXISTENTES
    ======================================== */

    /**
     * Nivel al que pertenece el grado
     */
    public function nivel()
    {
        return $this->belongsTo(Nivel::class);
    }

    /**
     * Estudiantes del grado
     */
    public function estudiantes()
    {
        return $this->hasMany(User::class)->where('role', 'student');
    }

    /**
     * Horarios del grado
     */
    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    /* ========================================
       NUEVAS RELACIONES
    ======================================== */

    /**
     * Asignaciones académicas del grado
     * (Qué profesores dan qué materias en este grado)
     */
    public function asignaciones()
    {
        return $this->hasMany(AsignacionAcademica::class);
    }

    /* ========================================
       SCOPES
    ======================================== */

    /**
     * Filtrar por nivel
     */
    public function scopeNivel($query, $nivelId)
    {
        return $query->where('nivel_id', $nivelId);
    }

    /**
     * Ordenar por nombre
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('nombre');
    }

    /* ========================================
       MÉTODOS ÚTILES
    ======================================== */

    /**
     * Obtener nombre completo (Nivel + Grado)
     */
    public function getNombreCompletoAttribute()
    {
        return $this->nivel->nombre . ' - ' . $this->nombre;
    }

    /**
     * Obtener total de estudiantes
     */
    public function getTotalEstudiantesAttribute()
    {
        return $this->estudiantes()->count();
    }

    /**
     * Verificar si tiene horario asignado para un año
     */
    public function tieneHorario($year)
    {
        return $this->horarios()->where('year', $year)->exists();
    }

    /**
     * Obtener horario de un año específico
     */
    public function horarioDelYear($year)
    {
        return $this->horarios()
            ->where('year', $year)
            ->with(['asignatura', 'profesor'])
            ->orderBy('dia_semana')
            ->orderBy('hora_numero')
            ->get();
    }

    /**
     * Obtener asignaciones académicas de un año
     */
    public function asignacionesDelYear($year)
    {
        return $this->asignaciones()
            ->where('year', $year)
            ->with(['profesor', 'asignatura'])
            ->get();
    }

    /**
     * Obtener resumen de asignaciones
     */
    public function resumenAsignaciones($year)
    {
        $asignaciones = $this->asignacionesDelYear($year);
        
        return [
            'total_asignaciones' => $asignaciones->count(),
            'total_horas_requeridas' => $asignaciones->sum('horas_semanales'),
            'total_horas_programadas' => $this->horarios()->where('year', $year)->count(),
            'asignaciones_completas' => $asignaciones->filter(fn($a) => $a->estaCompleta())->count(),
            'asignaciones_pendientes' => $asignaciones->filter(fn($a) => !$a->estaCompleta())->count(),
        ];
    }

    /**
     * Obtener profesores que dan clases en este grado
     */
    public function profesores($year)
    {
        return User::whereHas('asignacionesAcademicas', function($query) use ($year) {
            $query->where('grado_id', $this->id)
                  ->where('year', $year);
        })->get();
    }

    /**
     * Obtener asignaturas que se imparten en este grado
     */
    public function asignaturasDelGrado($year)
    {
        return Asignatura::whereHas('asignaciones', function($query) use ($year) {
            $query->where('grado_id', $this->id)
                  ->where('year', $year);
        })->get();
    }
}