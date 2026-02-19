<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asignaciones_academicas', function (Blueprint $table) {
            $table->id();
            
            // Relaciones con otras tablas
            $table->foreignId('profesor_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('ID del profesor (user con role=professor)');
            
            $table->foreignId('asignatura_id')
                ->constrained('asignaturas')
                ->onDelete('cascade')
                ->comment('ID de la asignatura a impartir');
            
            $table->foreignId('grado_id')
                ->constrained('grados')
                ->onDelete('cascade')
                ->comment('ID del grado donde se imparte');
            
            // Configuración de carga horaria
            $table->integer('horas_semanales')
                ->unsigned()
                ->comment('Número de horas que debe dar el profesor de esta materia por semana');
            
            $table->integer('year')
                ->unsigned()
                ->comment('Año académico (ej: 2024, 2025)');
            
            $table->integer('periodo_id')
                ->unsigned()
                ->nullable()
                ->comment('ID del periodo/trimestre (opcional)');
            
            $table->timestamps();
            
            // Índice único: evitar duplicados
            $table->unique(
                ['profesor_id', 'asignatura_id', 'grado_id', 'year', 'periodo_id'],
                'asignacion_unica'
            );
            
            // Índices para búsquedas rápidas
            $table->index(['profesor_id', 'year']);
            $table->index(['grado_id', 'year']);
            $table->index('year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignaciones_academicas');
    }
};



<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('restricciones_profesor', function (Blueprint $table) {
            $table->id();
            
            // Relación con el profesor
            $table->foreignId('profesor_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('ID del profesor con restricción');
            
            // Definir cuándo NO puede dar clases
            $table->enum('dia_semana', ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'])
                ->nullable()
                ->comment('Día específico (NULL = todos los días)');
            
            $table->integer('hora_numero')
                ->unsigned()
                ->nullable()
                ->comment('Número de hora específica (ej: 3, 4). NULL = todas las horas del día');
            
            // Alternativa: rangos de tiempo
            $table->time('hora_inicio')
                ->nullable()
                ->comment('Hora de inicio del bloqueo (alternativo a hora_numero)');
            
            $table->time('hora_fin')
                ->nullable()
                ->comment('Hora de fin del bloqueo (alternativo a hora_numero)');
            
            $table->string('motivo', 100)
                ->nullable()
                ->comment('Razón del bloqueo: reunion, personal, almuerzo, etc.');
            
            $table->integer('year')
                ->unsigned()
                ->comment('Año académico al que aplica');
            
            $table->boolean('activa')
                ->default(true)
                ->comment('Si la restricción está activa');
            
            $table->timestamps();
            
            // Índices
            $table->index(['profesor_id', 'year']);
            $table->index(['profesor_id', 'dia_semana', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restricciones_profesor');
    }
};




<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('preferencias_asignatura', function (Blueprint $table) {
            $table->id();
            
            // Relación con asignatura
            $table->foreignId('asignatura_id')
                ->constrained('asignaturas')
                ->onDelete('cascade')
                ->comment('ID de la asignatura');
            
            // Preferencia de momento del día
            $table->enum('momento_preferido', [
                'primeras_horas',
                'horas_centrales',
                'ultimas_horas',
                'sin_preferencia'
            ])->default('sin_preferencia')
              ->comment('Momento preferido del día para esta asignatura');
            
            // Prioridad en asignación automática (mayor número = mayor prioridad)
            $table->integer('prioridad')
                ->default(0)
                ->comment('Prioridad de asignación (0-100, mayor = primero)');
            
            // Restricciones específicas
            $table->boolean('evitar_ultima_hora')
                ->default(false)
                ->comment('Evitar asignar en la última hora del día');
            
            $table->boolean('evitar_primera_hora')
                ->default(false)
                ->comment('Evitar asignar en la primera hora del día');
            
            $table->boolean('requiere_consecutivas')
                ->default(false)
                ->comment('Requiere horas consecutivas (ej: laboratorios)');
            
            $table->integer('horas_consecutivas_min')
                ->unsigned()
                ->nullable()
                ->comment('Mínimo de horas consecutivas requeridas');
            
            $table->boolean('evitar_despues_recreo')
                ->default(false)
                ->comment('Evitar asignar inmediatamente después del recreo');
            
            $table->timestamps();
            
            // Una asignatura solo tiene una configuración
            $table->unique('asignatura_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preferencias_asignatura');
    }
};






<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            // Referencia a la asignación académica que originó este horario
            $table->foreignId('asignacion_academica_id')
                ->nullable()
                ->after('profesor_id')
                ->constrained('asignaciones_academicas')
                ->onDelete('set null')
                ->comment('ID de la asignación académica origen');
            
            // Indicador de generación automática
            $table->boolean('generado_automaticamente')
                ->default(false)
                ->after('asignacion_academica_id')
                ->comment('Indica si fue generado automáticamente o manualmente');
            
            // Índice para búsquedas
            $table->index('asignacion_academica_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->dropForeign(['asignacion_academica_id']);
            $table->dropIndex(['asignacion_academica_id']);
            $table->dropColumn([
                'asignacion_academica_id',
                'generado_automaticamente'
            ]);
        });
    }
};






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
     */
    public function horasAsignadas()
    {
        return $this->horarios()->count();
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

        // Si no tiene hora específica ni rango, bloquea todo el día
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
     * Filtrar por día
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
     */
    public static function profesorTieneRestriccion($profesorId, $dia, $horaNumero, $year, $hora = null)
    {
        $restricciones = self::where('profesor_id', $profesorId)
            ->where('year', $year)
            ->where('activa', true)
            ->get();

        foreach ($restricciones as $restriccion) {
            if ($restriccion->bloqueaHora($dia, $horaNumero, $hora)) {
                return true;
            }
        }

        return false;
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

        // Día
        if ($this->dia_semana) {
            $partes[] = $this->dia_semana;
        } else {
            $partes[] = "Todos los días";
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
}





php artisan optimize