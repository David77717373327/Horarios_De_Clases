<?php

namespace App\Http\Controllers;

use App\Models\RestriccionProfesor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RestriccionProfesorController extends Controller
{
    /**
     * Mostrar vista principal de restricciones
     */
    public function index()
    {
        $profesores = User::where('role', 'professor')
            ->where('is_approved', true)
            ->orderBy('name')
            ->get();
        $years = $this->getAcademicYears();
        
        return view('restricciones.index', compact('profesores', 'years'));
    }

    /**
     * Listar restricciones con filtros
     */
    public function listar(Request $request)
    {
        try {
            $query = RestriccionProfesor::with('profesor');

            if ($request->filled('year')) {
                $query->where('year', $request->year);
            }

            if ($request->filled('profesor_id')) {
                $query->where('profesor_id', $request->profesor_id);
            }

            if ($request->filled('activa')) {
                $query->where('activa', $request->activa === 'true');
            }

            $restricciones = $query->orderBy('profesor_id')
                ->orderBy('dia_semana')
                ->orderBy('hora_numero')
                ->get();

            return response()->json([
                'success' => true,
                'restricciones' => $restricciones
            ]);

        } catch (\Exception $e) {
            Log::error('Error al listar restricciones', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar las restricciones'
            ], 500);
        }
    }

    /**
     * Crear nueva restricción
     */
    public function store(Request $request)
    {
        try {
            Log::info('Creando restricción de profesor', $request->all());

            // Validación base
            $validated = $request->validate([
                'profesor_id' => 'required|exists:users,id',
                'dia_semana' => 'nullable|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado',
                'tipo_restriccion' => 'required|in:hora_especifica,rango_horario,dia_completo',
                'hora_numero' => 'nullable|integer|min:1|max:12',
                'hora_inicio' => 'nullable|date_format:H:i',
                'hora_fin' => 'nullable|date_format:H:i',
                'motivo' => 'nullable|string|max:100',
                'year' => 'required|integer|min:2020|max:2100',
                'activa' => 'boolean'
            ]);

            DB::beginTransaction();

            // Validación específica según tipo de restricción
            $tipo = $validated['tipo_restriccion'];
            
            if ($tipo === 'hora_especifica') {
                // Debe tener hora_numero
                if (empty($validated['hora_numero'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Para restricción de hora específica debe indicar el número de hora (1-12)'
                    ], 422);
                }
                // Limpiar campos no usados
                $validated['hora_inicio'] = null;
                $validated['hora_fin'] = null;
                
            } elseif ($tipo === 'rango_horario') {
                // Debe tener hora_inicio y hora_fin
                if (empty($validated['hora_inicio']) || empty($validated['hora_fin'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Para restricción de rango horario debe especificar hora de inicio y fin'
                    ], 422);
                }
                // Validar que hora_fin sea mayor que hora_inicio
                if ($validated['hora_inicio'] >= $validated['hora_fin']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La hora de fin debe ser posterior a la hora de inicio'
                    ], 422);
                }
                // Limpiar campos no usados
                $validated['hora_numero'] = null;
                
            } elseif ($tipo === 'dia_completo') {
                // Para día completo debe tener al menos un día especificado
                if (empty($validated['dia_semana'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Para restricción de día completo debe especificar el día de la semana'
                    ], 422);
                }
                // Limpiar campos no usados
                $validated['hora_numero'] = null;
                $validated['hora_inicio'] = null;
                $validated['hora_fin'] = null;
            }

            // Verificar duplicados
            $existe = RestriccionProfesor::where('profesor_id', $validated['profesor_id'])
                ->where('year', $validated['year'])
                ->where('dia_semana', $validated['dia_semana'])
                ->where('hora_numero', $validated['hora_numero'])
                ->where('hora_inicio', $validated['hora_inicio'])
                ->where('hora_fin', $validated['hora_fin'])
                ->where('activa', true)
                ->exists();

            if ($existe) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe una restricción activa idéntica para este profesor'
                ], 422);
            }

            // Remover tipo_restriccion antes de guardar (no existe en la tabla)
            unset($validated['tipo_restriccion']);

            // Crear restricción
            $restriccion = RestriccionProfesor::create($validated);

            DB::commit();

            Log::info('Restricción creada exitosamente', ['id' => $restriccion->id]);

            return response()->json([
                'success' => true,
                'message' => 'Restricción creada exitosamente',
                'restriccion' => $restriccion->load('profesor')
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear restricción', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la restricción: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener restricción específica
     */
    public function show($id)
    {
        try {
            $restriccion = RestriccionProfesor::with('profesor')->findOrFail($id);

            return response()->json([
                'success' => true,
                'restriccion' => $restriccion
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Restricción no encontrada'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error al obtener restricción', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la restricción'
            ], 500);
        }
    }

    /**
     * Actualizar restricción
     */
    public function update(Request $request, $id)
    {
        try {
            Log::info('Actualizando restricción', ['id' => $id]);

            $restriccion = RestriccionProfesor::findOrFail($id);

            // Validación base
            $validated = $request->validate([
                'profesor_id' => 'required|exists:users,id',
                'dia_semana' => 'nullable|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado',
                'tipo_restriccion' => 'required|in:hora_especifica,rango_horario,dia_completo',
                'hora_numero' => 'nullable|integer|min:1|max:12',
                'hora_inicio' => 'nullable|date_format:H:i',
                'hora_fin' => 'nullable|date_format:H:i',
                'motivo' => 'nullable|string|max:100',
                'year' => 'required|integer|min:2020|max:2100',
                'activa' => 'boolean'
            ]);

            DB::beginTransaction();

            // Validación específica según tipo
            $tipo = $validated['tipo_restriccion'];
            
            if ($tipo === 'hora_especifica') {
                if (empty($validated['hora_numero'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Debe indicar el número de hora'
                    ], 422);
                }
                $validated['hora_inicio'] = null;
                $validated['hora_fin'] = null;
                
            } elseif ($tipo === 'rango_horario') {
                if (empty($validated['hora_inicio']) || empty($validated['hora_fin'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Debe especificar hora de inicio y fin'
                    ], 422);
                }
                if ($validated['hora_inicio'] >= $validated['hora_fin']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La hora de fin debe ser posterior a la hora de inicio'
                    ], 422);
                }
                $validated['hora_numero'] = null;
                
            } elseif ($tipo === 'dia_completo') {
                if (empty($validated['dia_semana'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Debe especificar el día de la semana'
                    ], 422);
                }
                $validated['hora_numero'] = null;
                $validated['hora_inicio'] = null;
                $validated['hora_fin'] = null;
            }

            // Verificar duplicados (excluyendo el registro actual)
            $existe = RestriccionProfesor::where('id', '!=', $id)
                ->where('profesor_id', $validated['profesor_id'])
                ->where('year', $validated['year'])
                ->where('dia_semana', $validated['dia_semana'])
                ->where('hora_numero', $validated['hora_numero'])
                ->where('hora_inicio', $validated['hora_inicio'])
                ->where('hora_fin', $validated['hora_fin'])
                ->where('activa', true)
                ->exists();

            if ($existe) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe una restricción activa idéntica'
                ], 422);
            }

            unset($validated['tipo_restriccion']);

            $restriccion->update($validated);

            DB::commit();

            Log::info('Restricción actualizada', ['id' => $restriccion->id]);

            return response()->json([
                'success' => true,
                'message' => 'Restricción actualizada exitosamente',
                'restriccion' => $restriccion->fresh('profesor')
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar restricción', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la restricción: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar restricción
     */
    public function destroy($id)
    {
        try {
            Log::info('Eliminando restricción', ['id' => $id]);

            $restriccion = RestriccionProfesor::findOrFail($id);
            
            $profesor = $restriccion->profesor->name;
            $descripcion = $restriccion->descripcion;

            $restriccion->delete();

            Log::info('Restricción eliminada', ['id' => $id]);

            return response()->json([
                'success' => true,
                'message' => "Restricción eliminada: {$profesor} - {$descripcion}"
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Restricción no encontrada'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error al eliminar restricción', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la restricción'
            ], 500);
        }
    }

    /**
     * Activar/Desactivar restricción
     */
    public function toggleActiva($id)
    {
        try {
            $restriccion = RestriccionProfesor::findOrFail($id);
            $restriccion->activa = !$restriccion->activa;
            $restriccion->save();

            $estado = $restriccion->activa ? 'activada' : 'desactivada';

            return response()->json([
                'success' => true,
                'message' => "Restricción {$estado} exitosamente",
                'activa' => $restriccion->activa
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cambiar estado de restricción', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado'
            ], 500);
        }
    }

    /**
     * Obtener restricciones de un profesor para un año específico
     */
    public function restriccionesProfesor($profesorId, Request $request)
    {
        try {
            $year = $request->input('year', date('Y'));
            
            $restricciones = RestriccionProfesor::where('profesor_id', $profesorId)
                ->where('year', $year)
                ->where('activa', true)
                ->get();

            return response()->json([
                'success' => true,
                'restricciones' => $restricciones
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener restricciones de profesor', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener restricciones'
            ], 500);
        }
    }

    /**
     * Verificar si un profesor tiene restricción en una hora específica
     * Método usado por el sistema de generación de horarios
     */
    public function verificarRestriccion(Request $request)
    {
        try {
            $validated = $request->validate([
                'profesor_id' => 'required|exists:users,id',
                'dia' => 'required|string',
                'hora_numero' => 'nullable|integer',
                'year' => 'required|integer',
                'hora' => 'nullable|date_format:H:i'
            ]);

            $tieneRestriccion = RestriccionProfesor::profesorTieneRestriccion(
                $validated['profesor_id'],
                $validated['dia'],
                $validated['hora_numero'] ?? null,
                $validated['year'],
                $validated['hora'] ?? null
            );

            if ($tieneRestriccion) {
                return response()->json([
                    'success' => false,
                    'tiene_restriccion' => true,
                    'message' => 'El profesor tiene una restricción configurada para esta hora'
                ]);
            }

            return response()->json([
                'success' => true,
                'tiene_restriccion' => false,
                'message' => 'El profesor está disponible'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al verificar restricción', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar restricción'
            ], 500);
        }
    }

    /**
     * Obtener horas bloqueadas de un profesor para un día
     * Usado para mostrar disponibilidad visual en el frontend
     */
    public function horasBloqueadas($profesorId, Request $request)
    {
        try {
            $dia = $request->input('dia');
            $year = $request->input('year', date('Y'));
            
            if (!$dia) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe especificar el día'
                ], 422);
            }
            
            $horas = RestriccionProfesor::horasBloqueadasProfesor($profesorId, $dia, $year);
            
            return response()->json([
                'success' => true,
                'horas_bloqueadas' => $horas,
                'profesor_id' => $profesorId,
                'dia' => $dia,
                'year' => $year
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al obtener horas bloqueadas', [
                'error' => $e->getMessage(),
                'profesor_id' => $profesorId
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener horas bloqueadas'
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