<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use App\Models\Nivel;
use App\Models\Grado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HorarioController extends Controller
{
    /**
     * Mostrar vista principal de gestión de horarios
     */
    public function index()
    {
        $niveles = Nivel::orderBy('nombre')->get();
        $years = $this->getAcademicYears();
        
        return view('horario.index', compact('niveles', 'years'));
    }

    /**
     * Mostrar vista de creación de horario (solo generación automática)
     */
    public function create()
    {
        $niveles = Nivel::orderBy('nombre')->get();
        $years = $this->getAcademicYears();
        
        return view('horario.create', compact('niveles', 'years'));
    }

    /**
     * Obtener grados por nivel (AJAX)
     * Necesario para el Paso 1: Configuración Inicial
     */
    public function getGradosByNivel($nivelId)
    {
        try {
            $grados = Grado::where('nivel_id', $nivelId)
                ->orderBy('nombre')
                ->get();

            return response()->json([
                'success' => true,
                'grados' => $grados
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener grados', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los grados'
            ], 500);
        }
    }

    /**
     * Cargar horario existente
     * Necesario para el Paso 2: Cargar configuración previa si existe
     * ✅ Formato de hora correcto (HH:mm)
     */
    public function getHorario(Request $request)
    {
        try {
            $validated = $request->validate([
                'nivel_id' => 'required|exists:niveles,id',
                'grado_id' => 'required|exists:grados,id',
                'year' => 'required|integer|min:2020|max:2100'
            ]);

            $horarios = Horario::where('nivel_id', $validated['nivel_id'])
                ->where('grado_id', $validated['grado_id'])
                ->where('year', $validated['year'])
                ->with(['asignatura', 'profesor'])
                ->get();

            // Obtener configuración si existe
            $config = null;
            if ($horarios->isNotEmpty()) {
                $firstHorario = $horarios->first();
                
                // ✅ Convertir a formato HH:mm
                $horaInicio = $firstHorario->hora_inicio;
                $horaFin = $firstHorario->hora_fin;
                
                // Si es un objeto Carbon o DateTime, convertir
                if ($horaInicio instanceof \DateTime || $horaInicio instanceof Carbon) {
                    $horaInicio = $horaInicio->format('H:i');
                } else {
                    // Si es string, asegurarse de que esté en formato correcto
                    $horaInicio = Carbon::parse($horaInicio)->format('H:i');
                }
                
                if ($horaFin instanceof \DateTime || $horaFin instanceof Carbon) {
                    $horaFin = $horaFin->format('H:i');
                } else {
                    $horaFin = Carbon::parse($horaFin)->format('H:i');
                }
                
                $config = [
                    'hora_inicio' => $horaInicio,
                    'hora_fin' => $horaFin,
                    'duracion_clase' => $firstHorario->duracion_clase,
                    'horas_por_dia' => $firstHorario->horas_por_dia,
                    'dias_semana' => json_decode($firstHorario->dias_semana),
                    'recreo_despues_hora' => $firstHorario->recreo_despues_hora,
                    'recreo_duracion' => $firstHorario->recreo_duracion
                ];
            }

            return response()->json([
                'success' => true,
                'horarios' => $horarios,
                'config' => $config
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cargar horario', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar el horario'
            ], 500);
        }
    }

    /**
     * Eliminar horario completo
     * Necesario para regenerar horarios
     */
    public function destroy(Request $request)
    {
        try {
            $validated = $request->validate([
                'nivel_id' => 'required|exists:niveles,id',
                'grado_id' => 'required|exists:grados,id',
                'year' => 'required|integer'
            ]);

            $deleted = Horario::where('nivel_id', $validated['nivel_id'])
                ->where('grado_id', $validated['grado_id'])
                ->where('year', $validated['year'])
                ->delete();

            Log::info('Horario eliminado', [
                'nivel_id' => $validated['nivel_id'],
                'grado_id' => $validated['grado_id'],
                'year' => $validated['year'],
                'deleted_count' => $deleted
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Horario eliminado exitosamente',
                'deleted_count' => $deleted
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar horario', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el horario'
            ], 500);
        }
    }

    /**
     * Generar años académicos (actual ± 5 años)
     * Método auxiliar necesario para las vistas
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