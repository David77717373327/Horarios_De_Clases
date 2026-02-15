<?php

namespace App\Http\Controllers;

use App\Models\Nivel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class NivelController extends Controller
{
    public function index()
    {
        $niveles = Nivel::withCount(['grados', 'descansos'])
            ->orderBy('id')
            ->get();
            
        return view('nivel.index', compact('niveles'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:100|unique:niveles,nombre',
            ], [
                'nombre.required' => 'El nombre del nivel es obligatorio',
                'nombre.unique' => 'Este nivel ya existe en el sistema',
                'nombre.max' => 'El nombre no puede exceder 100 caracteres',
            ]);

            Nivel::create([
                'nombre' => $request->nombre,
            ]);

            return redirect()->route('niveles.index')
                ->with('success', 'Nivel académico creado exitosamente');
                
        } catch (Exception $e) {
            Log::error("Error al crear nivel: " . $e->getMessage());
            
            return redirect()->route('niveles.index')
                ->with('error', 'Error al crear el nivel: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Buscar el nivel por ID
            $nivel = Nivel::findOrFail($id);

            $request->validate([
                'nombre' => 'required|string|max:100|unique:niveles,nombre,' . $id,
            ], [
                'nombre.required' => 'El nombre del nivel es obligatorio',
                'nombre.unique' => 'Este nivel ya existe en el sistema',
                'nombre.max' => 'El nombre no puede exceder 100 caracteres',
            ]);

            $nivel->update([
                'nombre' => $request->nombre,
            ]);

            return redirect()->route('niveles.index')
                ->with('success', 'Nivel actualizado exitosamente');
                
        } catch (Exception $e) {
            Log::error("Error al actualizar nivel: " . $e->getMessage());
            
            return redirect()->route('niveles.index')
                ->with('error', 'Error al actualizar el nivel: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            Log::info("=== INICIO ELIMINACIÓN DE NIVEL ===");
            Log::info("ID recibido: {$id}");

            // 🔑 Buscar el nivel primero
            $nivel = Nivel::findOrFail($id);
            
            Log::info("Nivel encontrado - ID: {$nivel->id}, Nombre: {$nivel->nombre}");

            // Iniciar transacción
            DB::beginTransaction();

            // PASO 1: Obtener todos los IDs de grados asociados al nivel
            $gradosIds = DB::table('grados')
                ->where('nivel_id', $id)
                ->pluck('id')
                ->toArray();

            Log::info("Grados encontrados: " . count($gradosIds));

            if (!empty($gradosIds)) {
                
                // PASO 2: Eliminar HORARIOS asociados a los grados
                $horariosEliminados = DB::table('horarios')
                    ->whereIn('grado_id', $gradosIds)
                    ->delete();
                Log::info("Horarios eliminados: {$horariosEliminados}");

                // PASO 3: Eliminar ASIGNACIONES ACADÉMICAS (si la tabla existe)
                if (DB::getSchemaBuilder()->hasTable('asignaciones_academicas')) {
                    $asignacionesEliminadas = DB::table('asignaciones_academicas')
                        ->whereIn('grado_id', $gradosIds)
                        ->delete();
                    Log::info("Asignaciones académicas eliminadas: {$asignacionesEliminadas}");
                }

                // PASO 4: Desvincular ESTUDIANTES (si la columna grado_id existe en users)
                if (DB::getSchemaBuilder()->hasColumn('users', 'grado_id')) {
                    $estudiantesActualizados = DB::table('users')
                        ->whereIn('grado_id', $gradosIds)
                        ->update(['grado_id' => null]);
                    Log::info("Estudiantes desvinculados: {$estudiantesActualizados}");
                }

                // PASO 5: Eliminar cualquier otra relación que pueda existir
                // (Agrega aquí otras tablas relacionadas si existen)
                
                // Ejemplo: Si tienes tabla de notas
                if (DB::getSchemaBuilder()->hasTable('notas')) {
                    $notasEliminadas = DB::table('notas')
                        ->whereIn('grado_id', $gradosIds)
                        ->delete();
                    Log::info("Notas eliminadas: {$notasEliminadas}");
                }

                // Ejemplo: Si tienes tabla de asistencias
                if (DB::getSchemaBuilder()->hasTable('asistencias')) {
                    $asistenciasEliminadas = DB::table('asistencias')
                        ->whereIn('grado_id', $gradosIds)
                        ->delete();
                    Log::info("Asistencias eliminadas: {$asistenciasEliminadas}");
                }

                // PASO 6: Ahora sí eliminar GRADOS
                $gradosEliminados = DB::table('grados')
                    ->where('nivel_id', $id)
                    ->delete();
                Log::info("Grados eliminados: {$gradosEliminados}");
            }

            // PASO 7: Eliminar DESCANSOS asociados al nivel
            $descansosEliminados = DB::table('descansos')
                ->where('nivel_id', $id)
                ->delete();
            Log::info("Descansos eliminados: {$descansosEliminados}");

            // PASO 8: Finalmente eliminar el NIVEL
            $nivel->delete();
            Log::info("Nivel eliminado correctamente");

            // Confirmar transacción - TODO SE ELIMINÓ EXITOSAMENTE
            DB::commit();

            Log::info("=== ELIMINACIÓN COMPLETA EXITOSA ===");

            return redirect()->route('niveles.index')
                ->with('success', "Nivel '{$nivel->nombre}' y TODAS sus relaciones han sido eliminadas exitosamente");
                
        } catch (Exception $e) {
            // Si algo sale mal, REVERTIR TODO
            DB::rollBack();
            
            Log::error("=== ERROR EN ELIMINACIÓN ===");
            Log::error("Mensaje: " . $e->getMessage());
            Log::error("Línea: " . $e->getLine());
            Log::error("Archivo: " . $e->getFile());
            Log::error("Stack Trace: " . $e->getTraceAsString());
            
            return redirect()->route('niveles.index')
                ->with('error', 'Error al eliminar el nivel. Por favor contacta al administrador del sistema.');
        }
    }
}