<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'document',
        'email',
        'password',
        'role',
        'grado_id',
        'is_approved',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_approved' => 'boolean',
        ];
    }

    /* ========================================
       RELACIONES EXISTENTES
    ======================================== */

    /**
     * Grado del estudiante (SOLO para role=student)
     */
    public function grado()
    {
        return $this->belongsTo(Grado::class);
    }

    /**
     * Horarios donde el usuario es profesor
     */
    public function horarios()
    {
        return $this->hasMany(Horario::class, 'profesor_id');
    }

    /**
     * Asignaturas que el profesor puede impartir
     */
    public function asignaturas()
    {
        return $this->belongsToMany(Asignatura::class, 'asignatura_profesor');
    }

    /* ========================================
       NUEVAS RELACIONES (ASIGNACIÓN ACADÉMICA)
    ======================================== */

    /**
     * Asignaciones académicas del profesor
     * (Define QUÉ debe enseñar, a QUÉ grado, CUÁNTAS horas)
     */
    public function asignacionesAcademicas()
    {
        return $this->hasMany(AsignacionAcademica::class, 'profesor_id');
    }

    /**
     * Restricciones horarias del profesor
     * (Días/horas donde NO puede dar clases)
     */
    public function restricciones()
    {
        return $this->hasMany(RestriccionProfesor::class, 'profesor_id');
    }

    /* ========================================
       SCOPES ÚTILES
    ======================================== */

    public function scopeProfesores($query)
    {
        return $query->where('role', 'professor');
    }

    public function scopeEstudiantes($query)
    {
        return $query->where('role', 'student');
    }

    public function scopeAprobados($query)
    {
        return $query->where('is_approved', true);
    }

    /* ========================================
       MÉTODOS DE DISPONIBILIDAD
    ======================================== */

    /**
     * Verificar si el profesor está disponible en un día y hora específicos
     * 
     * @param string $dia Día de la semana (ej: 'Lunes')
     * @param int $horaNumero Número de la hora (1, 2, 3...)
     * @param int $year Año académico
     * @param int|null $gradoExcluido ID del grado a excluir (para edición)
     * @param string|null $hora Hora en formato HH:mm (opcional)
     * @return bool
     */
    public function estaDisponible($dia, $horaNumero, $year, $gradoExcluido = null, $hora = null)
    {
        // 1. Verificar restricciones configuradas
        $tieneRestriccion = RestriccionProfesor::profesorTieneRestriccion(
            $this->id, 
            $dia, 
            $horaNumero, 
            $year, 
            $hora
        );

        if ($tieneRestriccion) {
            return false;
        }

        // 2. Verificar si ya tiene clase en otro grado a esa misma hora
        $tieneClase = Horario::where('profesor_id', $this->id)
            ->where('dia_semana', $dia)
            ->where('hora_numero', $horaNumero)
            ->where('year', $year)
            ->when($gradoExcluido, function($query) use ($gradoExcluido) {
                return $query->where('grado_id', '!=', $gradoExcluido);
            })
            ->exists();

        return !$tieneClase;
    }

    /**
     * Obtener total de horas asignadas según asignaciones académicas
     */
    public function totalHorasAsignadas($year)
    {
        return $this->asignacionesAcademicas()
            ->where('year', $year)
            ->sum('horas_semanales');
    }

    /**
     * Obtener total de horas ya programadas en horarios
     */
    public function horasProgramadas($year)
    {
        return Horario::where('profesor_id', $this->id)
            ->where('year', $year)
            ->count();
    }

    /**
     * Obtener horas pendientes por programar
     */
    public function horasPendientes($year)
    {
        return max(0, $this->totalHorasAsignadas($year) - $this->horasProgramadas($year));
    }

    /**
     * Verificar si el profesor tiene asignaciones completas
     */
    public function tieneAsignacionesCompletas($year)
    {
        return $this->horasPendientes($year) === 0;
    }

    /* ========================================
       MÉTODOS DE INFORMACIÓN
    ======================================== */

    /**
     * Obtener resumen de carga académica del profesor
     */
    public function resumenCargaAcademica($year)
    {
        $asignaciones = $this->asignacionesAcademicas()
            ->where('year', $year)
            ->with(['asignatura', 'grado'])
            ->get();

        return [
            'total_asignaciones' => $asignaciones->count(),
            'total_horas_requeridas' => $asignaciones->sum('horas_semanales'),
            'total_horas_programadas' => $this->horasProgramadas($year),
            'horas_pendientes' => $this->horasPendientes($year),
            'porcentaje_completado' => $this->totalHorasAsignadas($year) > 0 
                ? round(($this->horasProgramadas($year) / $this->totalHorasAsignadas($year)) * 100, 1)
                : 0,
            'asignaciones' => $asignaciones
        ];
    }

    /**
     * Obtener grados donde el profesor da clases
     */
    public function gradosQueImparte($year)
    {
        return Grado::whereHas('asignaciones', function($query) use ($year) {
            $query->where('profesor_id', $this->id)
                  ->where('year', $year);
        })->with('nivel')->get();
    }

    /**
     * Verificar si el profesor puede dar una asignatura específica
     */
    public function puedeImpartirAsignatura($asignaturaId)
    {
        return $this->asignaturas->contains($asignaturaId);
    }
}