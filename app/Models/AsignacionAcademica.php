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
        'periodo_id'
    ];

    protected $casts = [
        'horas_semanales' => 'integer',
        'year' => 'integer',
        'periodo_id' => 'integer',
    ];

    // ========================================
    // RELACIONES
    // ========================================

    /**
     * Profesor asignado (relación con User)
     */
    public function profesor()
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }

    /**
     * Asignatura a impartir
     */
    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class);
    }

    /**
     * Grado donde se imparte
     */
    public function grado()
    {
        return $this->belongsTo(Grado::class);
    }

    /**
     * Horarios generados a partir de esta asignación
     */
    public function horarios()
    {
        return $this->hasMany(Horario::class, 'asignacion_academica_id');
    }

    // ========================================
    // MÉTODOS DE CÁLCULO
    // ========================================

    /**
 * Obtener total de horas ya asignadas en horarios
 * ✅ CORREGIDO: Busca por coincidencia de datos, no solo por relación
 */
public function horasAsignadas()
{
    // Primero intentar por relación directa (más eficiente)
    $porRelacion = $this->horarios()->count();
    
    if ($porRelacion > 0) {
        return $porRelacion;
    }
    
    // Si no hay relación, buscar por coincidencia de datos
    return \App\Models\Horario::where('grado_id', $this->grado_id)
        ->where('asignatura_id', $this->asignatura_id)
        ->where('profesor_id', $this->profesor_id)
        ->where('year', $this->year)
        ->count();
}

    /**
     * Obtener horas pendientes por asignar
     */
    public function horasPendientes()
    {
        return max(0, $this->horas_semanales - $this->horasAsignadas());
    }

    /**
     * Verificar si la asignación está completa
     */
    public function estaCompleta()
    {
        return $this->horasAsignadas() >= $this->horas_semanales;
    }

    /**
     * Calcular porcentaje de completado
     */
    public function porcentajeCompletado()
    {
        if ($this->horas_semanales == 0) {
            return 0;
        }
        
        return round(($this->horasAsignadas() / $this->horas_semanales) * 100, 1);
    }

    /**
     * Obtener estado de la asignación
     */
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

    /**
     * Obtener color del estado para UI
     */
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
    // SCOPES (Consultas reutilizables)
    // ========================================

    /**
     * Filtrar por año académico
     */
    public function scopeYear($query, $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Filtrar por profesor
     */
    public function scopeProfesor($query, $profesorId)
    {
        return $query->where('profesor_id', $profesorId);
    }

    /**
     * Filtrar por grado
     */
    public function scopeGrado($query, $gradoId)
    {
        return $query->where('grado_id', $gradoId);
    }

    /**
     * Obtener solo asignaciones incompletas
     */
    public function scopeIncompletas($query)
    {
        return $query->whereRaw('(SELECT COUNT(*) FROM horarios WHERE horarios.asignacion_academica_id = asignaciones_academicas.id) < asignaciones_academicas.horas_semanales');
    }

    /**
     * Obtener solo asignaciones completas
     */
    public function scopeCompletas($query)
    {
        return $query->whereRaw('(SELECT COUNT(*) FROM horarios WHERE horarios.asignacion_academica_id = asignaciones_academicas.id) >= asignaciones_academicas.horas_semanales');
    }

    // ========================================
    // MÉTODOS ESTÁTICOS ÚTILES
    // ========================================

    /**
     * Obtener resumen de asignaciones por profesor
     */
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

    /**
     * Obtener resumen de asignaciones por grado
     */
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

    /**
     * Validar si se puede crear esta asignación
     */
    public function validarCreacion()
    {
        $errores = [];

        // Verificar que el profesor puede dar esta asignatura
        $profesor = User::find($this->profesor_id);
        if ($profesor && !$profesor->asignaturas->contains($this->asignatura_id)) {
            $errores[] = 'El profesor no está habilitado para impartir esta asignatura';
        }

        // Verificar que no exista duplicado
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