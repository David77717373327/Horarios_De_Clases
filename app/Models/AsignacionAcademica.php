<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionAcademica extends Model
{
    use HasFactory;

    protected $table = 'asignaciones_academicas';
    
    protected $fillable = [
        'profesor_id',
        'asignatura_id',
        'grado_id',
        'horas_semanales',
        'year',
        'periodo_id',
        'posicion_jornada',
        'max_horas_por_dia',
        'max_dias_semana'
    ];

    protected $casts = [
        'horas_semanales' => 'integer',
        'year' => 'integer',
        'periodo_id' => 'integer',
        'max_horas_por_dia' => 'integer',
        'max_dias_semana' => 'integer',
    ];

    // ========================================
    // RELACIONES
    // ========================================

    public function profesor()
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }

    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class);
    }

    public function grado()
    {
        return $this->belongsTo(Grado::class);
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class, 'asignacion_academica_id');
    }

    // ========================================
    // MÉTODOS DE CÁLCULO
    // ========================================

    public function horasAsignadas()
    {
        $porRelacion = $this->horarios()->count();
        
        if ($porRelacion > 0) {
            return $porRelacion;
        }
        
        return \App\Models\Horario::where('grado_id', $this->grado_id)
            ->where('asignatura_id', $this->asignatura_id)
            ->where('profesor_id', $this->profesor_id)
            ->where('year', $this->year)
            ->count();
    }

    public function horasPendientes()
    {
        return max(0, $this->horas_semanales - $this->horasAsignadas());
    }

    public function estaCompleta()
    {
        return $this->horasAsignadas() >= $this->horas_semanales;
    }

    public function porcentajeCompletado()
    {
        if ($this->horas_semanales == 0) {
            return 0;
        }
        
        return round(($this->horasAsignadas() / $this->horas_semanales) * 100, 1);
    }

    public function getEstadoAttribute()
    {
        $horasAsignadas = $this->horasAsignadas();
        
        if ($horasAsignadas == 0) {
            return 'pendiente';
        } elseif ($horasAsignadas < $this->horas_semanales) {
            return 'parcial';
        } else {
            return 'completo';
        }
    }

    public function getColorEstadoAttribute()
    {
        switch ($this->estado) {
            case 'completo':
                return 'success';
            case 'parcial':
                return 'warning';
            case 'pendiente':
                return 'secondary';
            default:
                return 'secondary';
        }
    }

    // ========================================
    // NUEVOS MÉTODOS PARA RESTRICCIONES
    // ========================================

    public function tieneRestriccionPosicion()
    {
        return $this->posicion_jornada && $this->posicion_jornada !== 'sin_restriccion';
    }

    public function tieneRestriccionDistribucion()
    {
        return $this->max_horas_por_dia !== null || $this->max_dias_semana !== null;
    }

    // ========================================
    // SCOPES
    // ========================================

    public function scopeYear($query, $year)
    {
        return $query->where('year', $year);
    }

    public function scopeProfesor($query, $profesorId)
    {
        return $query->where('profesor_id', $profesorId);
    }

    public function scopeGrado($query, $gradoId)
    {
        return $query->where('grado_id', $gradoId);
    }

    public function scopeIncompletas($query)
    {
        return $query->whereRaw('(SELECT COUNT(*) FROM horarios WHERE horarios.asignacion_academica_id = asignaciones_academicas.id) < asignaciones_academicas.horas_semanales');
    }

    public function scopeCompletas($query)
    {
        return $query->whereRaw('(SELECT COUNT(*) FROM horarios WHERE horarios.asignacion_academica_id = asignaciones_academicas.id) >= asignaciones_academicas.horas_semanales');
    }

    // ========================================
    // MÉTODOS ESTÁTICOS ÚTILES
    // ========================================

    public static function resumenProfesor($profesorId, $year)
    {
        $asignaciones = self::where('profesor_id', $profesorId)
            ->where('year', $year)
            ->with(['asignatura', 'grado'])
            ->get();

        return [
            'total_asignaciones' => $asignaciones->count(),
            'total_horas_requeridas' => $asignaciones->sum('horas_semanales'),
            'total_horas_asignadas' => $asignaciones->sum(fn($a) => $a->horasAsignadas()),
            'asignaciones' => $asignaciones
        ];
    }

    public static function resumenGrado($gradoId, $year)
    {
        $asignaciones = self::where('grado_id', $gradoId)
            ->where('year', $year)
            ->with(['profesor', 'asignatura'])
            ->get();

        return [
            'total_asignaciones' => $asignaciones->count(),
            'total_horas_requeridas' => $asignaciones->sum('horas_semanales'),
            'total_horas_asignadas' => $asignaciones->sum(fn($a) => $a->horasAsignadas()),
            'asignaciones' => $asignaciones
        ];
    }

    // ========================================
    // VALIDACIONES
    // ========================================

    public function validarCreacion()
    {
        $errores = [];

        $profesor = User::find($this->profesor_id);
        if ($profesor && !$profesor->asignaturas->contains($this->asignatura_id)) {
            $errores[] = 'El profesor no está habilitado para impartir esta asignatura';
        }

        $existe = self::where('profesor_id', $this->profesor_id)
            ->where('asignatura_id', $this->asignatura_id)
            ->where('grado_id', $this->grado_id)
            ->where('year', $this->year)
            ->where('id', '!=', $this->id ?? 0)
            ->exists();

        if ($existe) {
            $errores[] = 'Ya existe una asignación con estos mismos datos';
        }

        return [
            'valido' => empty($errores),
            'errores' => $errores
        ];
    }
}