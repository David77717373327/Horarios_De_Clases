<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

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
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $appends = [
        'profile_photo_url',
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

    public function grado()
    {
        return $this->belongsTo(Grado::class);
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class, 'profesor_id');
    }

    public function asignaturas()
    {
        return $this->belongsToMany(Asignatura::class, 'asignatura_profesor');
    }

    /* ========================================
       NUEVAS RELACIONES (ASIGNACIÓN ACADÉMICA)
    ======================================== */

    public function asignacionesAcademicas()
    {
        return $this->hasMany(AsignacionAcademica::class, 'profesor_id');
    }

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

    public function estaDisponible($dia, $horaNumero, $year, $gradoExcluido = null, $hora = null)
    {
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

        $tieneClase = Horario::where('profesor_id', $this->id)
            ->where('dia_semana', $dia)
            ->where('hora_numero', $horaNumero)
            ->where('year', $year)
            ->when($gradoExcluido, function ($query) use ($gradoExcluido) {
                return $query->where('grado_id', '!=', $gradoExcluido);
            })
            ->exists();

        return !$tieneClase;
    }

    public function totalHorasAsignadas($year)
    {
        return $this->asignacionesAcademicas()
            ->where('year', $year)
            ->sum('horas_semanales');
    }

    public function horasProgramadas($year)
    {
        return Horario::where('profesor_id', $this->id)
            ->where('year', $year)
            ->count();
    }

    public function horasPendientes($year)
    {
        return max(0, $this->totalHorasAsignadas($year) - $this->horasProgramadas($year));
    }

    public function tieneAsignacionesCompletas($year)
    {
        return $this->horasPendientes($year) === 0;
    }

    /* ========================================
       MÉTODOS DE INFORMACIÓN
    ======================================== */

    public function resumenCargaAcademica($year)
    {
        $asignaciones = $this->asignacionesAcademicas()
            ->where('year', $year)
            ->with(['asignatura', 'grado'])
            ->get();

        return [
            'total_asignaciones'      => $asignaciones->count(),
            'total_horas_requeridas'  => $asignaciones->sum('horas_semanales'),
            'total_horas_programadas' => $this->horasProgramadas($year),
            'horas_pendientes'        => $this->horasPendientes($year),
            'porcentaje_completado'   => $this->totalHorasAsignadas($year) > 0
                ? round(($this->horasProgramadas($year) / $this->totalHorasAsignadas($year)) * 100, 1)
                : 0,
            'asignaciones'            => $asignaciones,
        ];
    }

    public function gradosQueImparte($year)
    {
        return Grado::whereHas('asignaciones', function ($query) use ($year) {
            $query->where('profesor_id', $this->id)
                  ->where('year', $year);
        })->with('nivel')->get();
    }

    public function puedeImpartirAsignatura($asignaturaId)
    {
        return $this->asignaturas->contains($asignaturaId);
    }
}