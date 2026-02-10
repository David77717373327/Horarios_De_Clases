<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Asignatura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProfesorController extends Controller
{
    /**
     * Mostrar listado de profesores
     */
    public function index()
    {
        Log::info('Cargando listado de profesores');

        $profesores = User::where('role', 'professor')
            ->with('asignaturas')
            ->orderBy('name')
            ->get();

        $asignaturas = Asignatura::orderBy('nombre')->get();

        return view('profesores.index', compact('profesores', 'asignaturas'));
    }

    /**
     * Crear nuevo profesor
     */
    public function store(Request $request)
    {
        try {
            Log::info('Intentando crear profesor', $request->all());

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'asignaturas' => 'nullable|array',
                'asignaturas.*' => 'exists:asignaturas,id'
            ]);

            // Campos automáticos para cumplir con la base de datos
            $profesor = User::create([
                'name' => $validated['name'],
                'document' => 'AUTO-' . Str::random(10),
                'email' => 'prof_' . Str::random(10) . '@colegio.local',
                'password' => bcrypt(Str::random(12)),
                'role' => 'professor',
                'is_approved' => true
            ]);

            // Asignar asignaturas si se proporcionaron
            if (!empty($validated['asignaturas'])) {
                $profesor->asignaturas()->attach($validated['asignaturas']);
            }

            Log::info('Profesor creado correctamente', ['id' => $profesor->id]);

            return response()->json([
                'success' => true,
                'message' => 'Profesor creado correctamente',
                'profesor' => $profesor->load('asignaturas')
            ], 201);
        } catch (ValidationException $e) {
            Log::warning('Error de validación al crear profesor', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear profesor', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el profesor: ' . $e->getMessage()
            ], 500);
        }
    }




    /**
     * Obtener datos de un profesor específico
     */
    public function show($id)
    {
        try {
            $profesor = User::with(['asignaturas', 'grado', 'horarios'])
                ->where('role', 'professor')
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'profesor' => $profesor,
                'asignaturas' => $profesor->asignaturas, // ✅ IMPORTANTE: Agregar esta línea
                'total_asignaturas' => $profesor->asignaturas->count(),
                'total_horarios' => $profesor->horarios->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información del profesor'
            ], 500);
        }
    }




    /**
     * Actualizar profesor (nombre y asignaturas)
     */
    public function update(Request $request, $id)
    {
        try {
            Log::info('Intentando actualizar profesor', ['id' => $id, 'data' => $request->all()]);

            $profesor = User::where('role', 'professor')
                ->where('id', $id)
                ->firstOrFail();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'asignaturas' => 'nullable|array',
                'asignaturas.*' => 'exists:asignaturas,id'
            ]);

            // Actualizar nombre
            $profesor->update([
                'name' => $validated['name']
            ]);

            // Sincronizar asignaturas (esto reemplaza todas las asignaturas)
            $asignaturas = $validated['asignaturas'] ?? [];
            $profesor->asignaturas()->sync($asignaturas);

            Log::info('Profesor actualizado correctamente', [
                'id' => $profesor->id,
                'asignaturas_count' => count($asignaturas)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profesor actualizado correctamente',
                'profesor' => $profesor->fresh('asignaturas')
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Profesor no encontrado para actualizar', ['id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Profesor no encontrado'
            ], 404);
        } catch (ValidationException $e) {
            Log::warning('Error de validación al actualizar profesor', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al actualizar profesor', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el profesor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Asignar asignaturas a un profesor
     * (Método alternativo si quieres usar una ruta específica)
     */
    public function asignarAsignaturas(Request $request, $id)
    {
        try {
            Log::info('Asignando asignaturas al profesor', ['id' => $id, 'asignaturas' => $request->asignaturas]);

            $profesor = User::where('role', 'professor')
                ->where('id', $id)
                ->firstOrFail();

            $validated = $request->validate([
                'asignaturas' => 'nullable|array',
                'asignaturas.*' => 'exists:asignaturas,id'
            ]);

            $asignaturas = $validated['asignaturas'] ?? [];
            $profesor->asignaturas()->sync($asignaturas);

            Log::info('Asignaturas actualizadas correctamente', [
                'profesor_id' => $profesor->id,
                'total_asignaturas' => count($asignaturas)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Asignaturas actualizadas correctamente',
                'profesor' => $profesor->fresh('asignaturas')
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Profesor no encontrado para asignar asignaturas', ['id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Profesor no encontrado'
            ], 404);
        } catch (ValidationException $e) {
            Log::warning('Error de validación al asignar asignaturas', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al asignar asignaturas', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar asignaturas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar profesor
     */
    public function destroy($id)
    {
        try {
            Log::info('Intentando eliminar profesor', ['id' => $id]);

            $profesor = User::where('role', 'professor')
                ->where('id', $id)
                ->firstOrFail();

            $nombreProfesor = $profesor->name;

            // Desasociar asignaturas antes de eliminar
            $profesor->asignaturas()->detach();

            // Eliminar profesor
            $profesor->delete();

            Log::info('Profesor eliminado correctamente', ['id' => $id, 'nombre' => $nombreProfesor]);

            return response()->json([
                'success' => true,
                'message' => "Profesor {$nombreProfesor} eliminado correctamente"
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Profesor no encontrado para eliminar', ['id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Profesor no encontrado'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al eliminar profesor', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el profesor: ' . $e->getMessage()
            ], 500);
        }
    }
}
