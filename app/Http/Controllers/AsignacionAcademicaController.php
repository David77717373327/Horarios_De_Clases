<?php

namespace App\Http\Controllers;

use App\Models\AsignacionAcademica;
use App\Models\User;
use App\Models\Asignatura;
use App\Models\Grado;
use App\Models\Nivel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AsignacionAcademicaController extends Controller
{
    /**
     * Mostrar la vista principal de asignaciones
     */
    public function index()
    {
        $years = $this->getAcademicYears();

        $profesores = User::where('role', 'professor')
            ->where('is_approved', true)
            ->with('asignaturas:id,nombre')
            ->orderBy('name')
            ->get(['id', 'name']);

        $niveles = Nivel::orderBy('orden')->get(['id', 'nombre']);

        return view('asignaciones.index', compact('years', 'profesores', 'niveles'));
    }

    /**
     * Listar asignaciones con filtros
     */
    public function listar(Request $request)
    {
        try {
            $query = AsignacionAcademica::with([
                'profesor:id,name',
                'asignatura:id,nombre',
                'grado:id,nombre,nivel_id',
                'grado.nivel:id,nombre'
            ])
                ->withCount('horarios as horas_asignadas_count');

            // Aplicar filtros
            if ($request->filled('year')) {
                $query->where('year', $request->year);
            }

            if ($request->filled('profesor_id')) {
                $query->where('profesor_id', $request->profesor_id);
            }

            if ($request->filled('nivel_id')) {
                $query->whereHas('grado', function ($q) use ($request) {
                    $q->where('nivel_id', $request->nivel_id);
                });
            }

            if ($request->filled('grado_id')) {
                $query->where('grado_id', $request->grado_id);
            }

            $asignaciones = $query->orderBy('year', 'desc')
                ->orderBy('profesor_id')
                ->get();

            // Calcular datos adicionales
            $asignaciones->each(function ($asignacion) {
                $asignacion->horas_pendientes_count = max(0, $asignacion->horas_semanales - $asignacion->horas_asignadas_count);
                $asignacion->porcentaje = $asignacion->horas_semanales > 0
                    ? round(($asignacion->horas_asignadas_count / $asignacion->horas_semanales) * 100, 1)
                    : 0;
                $asignacion->estado = $asignacion->estaCompleta() ? 'completo' : ($asignacion->horas_asignadas_count > 0 ? 'parcial' : 'pendiente');
            });

            return response()->json([
                'success' => true,
                'asignaciones' => $asignaciones
            ]);
        } catch (\Exception $e) {
            Log::error('Error al listar asignaciones', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar asignaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener grados por nivel
     */
    public function gradosPorNivel($nivelId)
    {
        try {
            Log::info('Solicitando grados para nivel', ['nivel_id' => $nivelId]);

            // Verificar que el nivel existe
            $nivel = Nivel::find($nivelId);
            if (!$nivel) {
                Log::warning('Nivel no encontrado', ['nivel_id' => $nivelId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Nivel no encontrado',
                    'grados' => []
                ], 404);
            }

            // Obtener grados
            $grados = Grado::where('nivel_id', $nivelId)
                ->orderBy('nombre')
                ->get(['id', 'nombre']);

            Log::info('Grados encontrados', [
                'nivel_id' => $nivelId,
                'total_grados' => $grados->count(),
                'grados' => $grados->pluck('nombre')->toArray()
            ]);

            return response()->json([
                'success' => true,
                'grados' => $grados,
                'total' => $grados->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener grados', [
                'nivel_id' => $nivelId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar grados: ' . $e->getMessage(),
                'grados' => []
            ], 500);
        }
    }

    /**
     * Alias para compatibilidad
     */
    public function getGradosByNivel($nivelId)
    {
        return $this->gradosPorNivel($nivelId);
    }

    /**
     * Obtener datos para matriz de asignación masiva
     */
    public function obtenerMatriz($gradoId, Request $request)
    {
        try {
            $year = $request->input('year', date('Y'));

            // Obtener el grado con su nivel
            $grado = Grado::with('nivel:id,nombre')->findOrFail($gradoId);

            // Obtener todos los profesores activos con sus asignaturas
            $profesores = User::where('role', 'professor')
                ->where('is_approved', true)
                ->with('asignaturas:id,nombre')
                ->orderBy('name')
                ->get(['id', 'name']);

            // Obtener todas las asignaturas activas
            $asignaturas = Asignatura::orderBy('nombre')->get(['id', 'nombre']);

            // Obtener asignaciones existentes para este grado y año
            $asignaciones_existentes = AsignacionAcademica::where('grado_id', $gradoId)
                ->where('year', $year)
                ->get(['id', 'profesor_id', 'asignatura_id', 'horas_semanales']);

            return response()->json([
                'success' => true,
                'data' => [
                    'grado' => $grado,
                    'profesores' => $profesores,
                    'asignaturas' => $asignaturas,
                    'asignaciones_existentes' => $asignaciones_existentes
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al cargar matriz', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar la matriz: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar asignaciones masivas
     */
    public function guardarMasiva(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'asignaciones' => 'required|array|min:1',
            'asignaciones.*.profesor_id' => 'required|exists:users,id',
            'asignaciones.*.asignatura_id' => 'required|exists:asignaturas,id',
            'asignaciones.*.grado_id' => 'required|exists:grados,id',
            'asignaciones.*.horas_semanales' => 'required|integer|min:1|max:40',
            'asignaciones.*.year' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $guardadas = 0;
            $actualizadas = 0;
            $errores = [];

            foreach ($request->asignaciones as $index => $datos) {
                try {
                    // Verificar que el profesor puede dar esta asignatura
                    $profesor = User::with('asignaturas')->find($datos['profesor_id']);
                    if (!$profesor->asignaturas->contains($datos['asignatura_id'])) {
                        $errores[] = "Fila {$index}: El profesor no puede impartir esta asignatura";
                        continue;
                    }

                    // Buscar si ya existe
                    $asignacion = AsignacionAcademica::where('profesor_id', $datos['profesor_id'])
                        ->where('asignatura_id', $datos['asignatura_id'])
                        ->where('grado_id', $datos['grado_id'])
                        ->where('year', $datos['year'])
                        ->first();

                    if ($asignacion) {
                        // Actualizar
                        $asignacion->update([
                            'horas_semanales' => $datos['horas_semanales'],
                            'periodo_id' => $datos['periodo_id'] ?? null
                        ]);
                        $actualizadas++;
                    } else {
                        // Crear nueva
                        AsignacionAcademica::create($datos);
                        $guardadas++;
                    }
                } catch (\Exception $e) {
                    Log::error("Error al procesar asignación {$index}", ['error' => $e->getMessage()]);
                    $errores[] = "Fila {$index}: " . $e->getMessage();
                }
            }

            DB::commit();

            $mensaje = "Proceso completado";
            if ($guardadas > 0) $mensaje .= ": {$guardadas} creadas";
            if ($actualizadas > 0) $mensaje .= ", {$actualizadas} actualizadas";
            if (count($errores) > 0) $mensaje .= ", " . count($errores) . " errores";

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'guardadas' => $guardadas,
                'actualizadas' => $actualizadas,
                'total' => $guardadas + $actualizadas,
                'errores' => $errores
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar asignaciones masivas', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar asignaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener resumen de un año académico
     */
    public function resumenYear(Request $request)
    {
        try {
            $year = $request->input('year');

            if (!$year) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe proporcionar un año'
                ], 422);
            }

            $asignaciones = AsignacionAcademica::where('year', $year)->get();

            $profesores = $asignaciones->pluck('profesor_id')->unique()->count();
            $grados = $asignaciones->pluck('grado_id')->unique()->count();

            return response()->json([
                'success' => true,
                'total' => $asignaciones->count(),
                'profesores' => $profesores,
                'grados' => $grados
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener resumen', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener resumen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Copiar asignaciones de un año a otro
     */
    public function copiarYear(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year_origen' => 'required|integer',
            'year_destino' => 'required|integer|different:year_origen',
            'sobreescribir' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $yearOrigen = $request->year_origen;
            $yearDestino = $request->year_destino;
            $sobreescribir = $request->input('sobreescribir', false);

            $eliminadas = 0;

            // Si se debe sobreescribir, eliminar asignaciones del año destino
            if ($sobreescribir) {
                $eliminadas = AsignacionAcademica::where('year', $yearDestino)->delete();
            }

            // Obtener asignaciones del año origen
            $asignacionesOrigen = AsignacionAcademica::where('year', $yearOrigen)->get();

            if ($asignacionesOrigen->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "No hay asignaciones en el año {$yearOrigen} para copiar"
                ], 404);
            }

            $copiadas = 0;

            // Copiar cada asignación
            foreach ($asignacionesOrigen as $asignacion) {
                // Verificar si ya existe (solo si no se sobreescribió)
                if (!$sobreescribir) {
                    $existe = AsignacionAcademica::where('profesor_id', $asignacion->profesor_id)
                        ->where('asignatura_id', $asignacion->asignatura_id)
                        ->where('grado_id', $asignacion->grado_id)
                        ->where('year', $yearDestino)
                        ->exists();

                    if ($existe) {
                        continue;
                    }
                }

                // Crear la nueva asignación
                AsignacionAcademica::create([
                    'profesor_id' => $asignacion->profesor_id,
                    'asignatura_id' => $asignacion->asignatura_id,
                    'grado_id' => $asignacion->grado_id,
                    'horas_semanales' => $asignacion->horas_semanales,
                    'year' => $yearDestino,
                    'periodo_id' => $asignacion->periodo_id,
                    // ✅ COPIAR NUEVOS CAMPOS
                    'posicion_jornada' => $asignacion->posicion_jornada,
                    'max_horas_por_dia' => $asignacion->max_horas_por_dia,
                    'max_dias_semana' => $asignacion->max_dias_semana
                ]);

                $copiadas++;
            }

            DB::commit();

            Log::info('Asignaciones copiadas', [
                'year_origen' => $yearOrigen,
                'year_destino' => $yearDestino,
                'copiadas' => $copiadas,
                'eliminadas' => $eliminadas
            ]);

            return response()->json([
                'success' => true,
                'message' => "Asignaciones copiadas exitosamente",
                'copiadas' => $copiadas,
                'eliminadas' => $eliminadas
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al copiar asignaciones', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al copiar asignaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear una asignación individual
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profesor_id' => 'required|exists:users,id',
            'asignatura_id' => 'required|exists:asignaturas,id',
            'grado_id' => 'required|exists:grados,id',
            'horas_semanales' => 'required|integer|min:1|max:40',
            'year' => 'required|integer',
            'periodo_id' => 'nullable|integer|min:1|max:4',
            // ✅ VALIDACIÓN DE NUEVOS CAMPOS
            'posicion_jornada' => 'nullable|in:primeras_horas,ultimas_horas,antes_recreo,despues_recreo,sin_restriccion',
            'max_horas_por_dia' => 'nullable|integer|min:1|max:8',
            'max_dias_semana' => 'nullable|integer|min:1|max:5'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Verificar que el profesor puede dar esta asignatura
            $profesor = User::with('asignaturas')->find($request->profesor_id);
            if (!$profesor->asignaturas->contains($request->asignatura_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El profesor no está habilitado para impartir esta asignatura'
                ], 422);
            }

            // Verificar duplicado
            $existe = AsignacionAcademica::where('profesor_id', $request->profesor_id)
                ->where('asignatura_id', $request->asignatura_id)
                ->where('grado_id', $request->grado_id)
                ->where('year', $request->year)
                ->exists();

            if ($existe) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe una asignación con estos datos'
                ], 422);
            }

            $asignacion = AsignacionAcademica::create($request->all());

            DB::commit();

            Log::info('Asignación creada', ['id' => $asignacion->id]);

            return response()->json([
                'success' => true,
                'message' => 'Asignación creada exitosamente',
                'asignacion' => $asignacion->load(['profesor', 'asignatura', 'grado.nivel'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear asignación', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear asignación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar una asignación específica
     */
    public function show($id)
    {
        try {
            $asignacion = AsignacionAcademica::with([
                'profesor:id,name',
                'asignatura:id,nombre',
                'grado:id,nombre,nivel_id',
                'grado.nivel:id,nombre'
            ])
                ->withCount('horarios as horas_asignadas_count')
                ->findOrFail($id);

            $asignacion->horas_pendientes_count = max(0, $asignacion->horas_semanales - $asignacion->horas_asignadas_count);
            $asignacion->porcentaje = $asignacion->horas_semanales > 0
                ? round(($asignacion->horas_asignadas_count / $asignacion->horas_semanales) * 100, 1)
                : 0;

            return response()->json([
                'success' => true,
                'asignacion' => $asignacion
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Asignación no encontrada'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener asignación', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener asignación'
            ], 500);
        }
    }

    /**
     * Actualizar una asignación
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'horas_semanales' => 'required|integer|min:1|max:40',
            'periodo_id' => 'nullable|integer|min:1|max:4',
            // ✅ VALIDACIÓN DE NUEVOS CAMPOS
            'posicion_jornada' => 'nullable|in:primeras_horas,ultimas_horas,antes_recreo,despues_recreo,sin_restriccion',
            'max_horas_por_dia' => 'nullable|integer|min:1|max:8',
            'max_dias_semana' => 'nullable|integer|min:1|max:5'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $asignacion = AsignacionAcademica::findOrFail($id);
            
            // ✅ ACTUALIZAR CON NUEVOS CAMPOS
            $asignacion->update($request->only([
                'horas_semanales', 
                'periodo_id',
                'posicion_jornada',
                'max_horas_por_dia',
                'max_dias_semana'
            ]));

            DB::commit();

            Log::info('Asignación actualizada', ['id' => $asignacion->id]);

            return response()->json([
                'success' => true,
                'message' => 'Asignación actualizada exitosamente',
                'asignacion' => $asignacion->fresh()
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Asignación no encontrada'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar asignación', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una asignación
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $asignacion = AsignacionAcademica::findOrFail($id);

            // Verificar si tiene horarios asignados
            $tieneHorarios = $asignacion->horarios()->exists();

            if ($tieneHorarios) {
                $totalHorarios = $asignacion->horarios()->count();

                return response()->json([
                    'success' => false,
                    'message' => "No se puede eliminar esta asignación porque tiene {$totalHorarios} clase(s) programada(s) en el horario. Elimine primero los horarios asociados.",
                    'tiene_horarios' => true,
                    'total_horarios' => $totalHorarios
                ], 422);
            }

            $profesor = $asignacion->profesor->name;
            $asignatura = $asignacion->asignatura->nombre;
            $grado = $asignacion->grado->nombre;

            $asignacion->delete();

            DB::commit();

            Log::info('Asignación eliminada', ['id' => $id]);

            return response()->json([
                'success' => true,
                'message' => "Asignación eliminada: {$profesor} - {$asignatura} - {$grado}"
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Asignación no encontrada'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar asignación', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas generales
     */
    public function estadisticas(Request $request)
    {
        try {
            $year = $request->input('year', date('Y'));

            $asignaciones = AsignacionAcademica::where('year', $year)
                ->withCount('horarios')
                ->get();

            $total = $asignaciones->count();
            $completas = $asignaciones->filter(function ($a) {
                return $a->horarios_count >= $a->horas_semanales;
            })->count();

            $parciales = $asignaciones->filter(function ($a) {
                return $a->horarios_count > 0 && $a->horarios_count < $a->horas_semanales;
            })->count();

            $pendientes = $asignaciones->filter(function ($a) {
                return $a->horarios_count == 0;
            })->count();

            $horasTotales = $asignaciones->sum('horas_semanales');
            $horasAsignadas = $asignaciones->sum('horarios_count');

            return response()->json([
                'success' => true,
                'estadisticas' => [
                    'total' => $total,
                    'completas' => $completas,
                    'parciales' => $parciales,
                    'pendientes' => $pendientes,
                    'horas_totales' => $horasTotales,
                    'horas_asignadas' => $horasAsignadas,
                    'porcentaje_global' => $horasTotales > 0 ? round(($horasAsignadas / $horasTotales) * 100, 1) : 0
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener resumen de asignaciones por profesor
     */
    public function resumenProfesor($profesorId)
    {
        try {
            $year = request()->input('year', date('Y'));

            $profesor = User::findOrFail($profesorId);

            if (method_exists($profesor, 'resumenCargaAcademica')) {
                $resumen = $profesor->resumenCargaAcademica($year);
            } else {
                $asignaciones = AsignacionAcademica::where('profesor_id', $profesorId)
                    ->where('year', $year)
                    ->with(['asignatura', 'grado'])
                    ->withCount('horarios as horas_asignadas_count')
                    ->get();

                $resumen = [
                    'total_asignaciones' => $asignaciones->count(),
                    'total_horas_requeridas' => $asignaciones->sum('horas_semanales'),
                    'total_horas_asignadas' => $asignaciones->sum('horas_asignadas_count'),
                    'asignaciones' => $asignaciones
                ];
            }

            return response()->json([
                'success' => true,
                'resumen' => $resumen
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener resumen de profesor', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener resumen del profesor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener resumen de asignaciones por grado
     */
    public function resumenGrado($gradoId)
    {
        try {
            $year = request()->input('year', date('Y'));

            $asignaciones = AsignacionAcademica::where('grado_id', $gradoId)
                ->where('year', $year)
                ->with(['profesor', 'asignatura'])
                ->withCount('horarios as horas_asignadas_count')
                ->get();

            $total_horas = $asignaciones->sum('horas_semanales');
            $horas_asignadas = $asignaciones->sum('horas_asignadas_count');

            return response()->json([
                'success' => true,
                'resumen' => [
                    'total_asignaciones' => $asignaciones->count(),
                    'total_horas_requeridas' => $total_horas,
                    'total_horas_asignadas' => $horas_asignadas,
                    'horas_pendientes' => max(0, $total_horas - $horas_asignadas),
                    'porcentaje_completado' => $total_horas > 0 ? round(($horas_asignadas / $total_horas) * 100, 1) : 0,
                    'asignaciones' => $asignaciones
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener resumen de grado', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener resumen del grado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validar una asignación antes de crearla
     */
    public function validar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'profesor_id' => 'required|exists:users,id',
                'asignatura_id' => 'required|exists:asignaturas,id',
                'grado_id' => 'required|exists:grados,id',
                'year' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $errores = [];

            // Verificar que el profesor puede dar esta asignatura
            $profesor = User::with('asignaturas')->find($request->profesor_id);
            if (!$profesor->asignaturas->contains($request->asignatura_id)) {
                $errores[] = 'El profesor no está habilitado para impartir esta asignatura';
            }

            // Verificar duplicados
            $existe = AsignacionAcademica::where('profesor_id', $request->profesor_id)
                ->where('asignatura_id', $request->asignatura_id)
                ->where('grado_id', $request->grado_id)
                ->where('year', $request->year)
                ->when($request->has('id'), function ($query) use ($request) {
                    return $query->where('id', '!=', $request->id);
                })
                ->exists();

            if ($existe) {
                $errores[] = 'Ya existe una asignación con estos mismos datos';
            }

            return response()->json([
                'success' => empty($errores),
                'valido' => empty($errores),
                'errores' => $errores
            ]);
        } catch (\Exception $e) {
            Log::error('Error al validar', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al validar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar años académicos
     */
    private function getAcademicYears()
    {
        $currentYear = date('Y');
        $years = [];

        for ($i = -2; $i <= 5; $i++) {
            $years[] = $currentYear + $i;
        }

        return $years;
    }
}