<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Horario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class HorarioProfesorController extends Controller
{
    /**
     * Mostrar la vista principal de horarios por profesor
     */
    public function index()
    {
        $years = $this->getAcademicYears();
        
        $profesores = User::where('role', 'professor')
            ->where('is_approved', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('horario.horarios-profesor.listar_horario', compact('years', 'profesores'));
    }

    /**
     * Obtener horario de un profesor específico (AJAX)
     */
    public function obtenerHorario(Request $request)
    {
        try {
            $profesorId = $request->input('profesor_id');
            $year = $request->input('year', date('Y'));

            if (!$profesorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe proporcionar un profesor'
                ], 422);
            }

            // Obtener profesor
            $profesor = User::findOrFail($profesorId);

            // CONSULTA CORREGIDA
            $horarios = DB::table('horarios')
                ->join('asignaturas', 'horarios.asignatura_id', '=', 'asignaturas.id')
                ->join('grados', 'horarios.grado_id', '=', 'grados.id')
                ->join('niveles', 'grados.nivel_id', '=', 'niveles.id')
                ->where('horarios.profesor_id', $profesorId)
                ->where('horarios.year', $year)
                ->select(
                    'horarios.dia_semana',
                    'horarios.hora_numero',
                    'horarios.hora_inicio',
                    'horarios.hora_fin',
                    'horarios.duracion_clase',
                    'horarios.horas_por_dia',
                    'horarios.dias_semana',
                    'horarios.recreo_despues_hora',
                    'horarios.recreo_duracion',
                    'asignaturas.nombre as asignatura',
                    'asignaturas.id as asignatura_id',
                    'grados.nombre as grado',
                    'grados.id as grado_id',
                    'niveles.nombre as nivel'
                )
                ->orderBy('horarios.dia_semana')
                ->orderBy('horarios.hora_numero')
                ->get();

            if ($horarios->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'profesor' => $profesor->name,
                    'year' => $year,
                    'horarios' => [],
                    'message' => 'No hay horarios registrados para este profesor en el año ' . $year
                ]);
            }

            // Tomar configuración del primer horario
            $config = $horarios->first();
            
            // Decodificar JSON si es string
            $diasSemana = $config->dias_semana;
            
            if (is_string($diasSemana)) {
                $diasSemana = json_decode($diasSemana, true);
            }
            
            if (!is_array($diasSemana)) {
                $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
            }

            // Organizar horarios en matriz
            $horariosOrganizados = [];

            foreach ($diasSemana as $dia) {
                $horariosOrganizados[$dia] = [];
            }

            foreach ($horarios as $horario) {
                if (!isset($horariosOrganizados[$horario->dia_semana])) {
                    $horariosOrganizados[$horario->dia_semana] = [];
                }
                
                if (!isset($horariosOrganizados[$horario->dia_semana][$horario->hora_numero])) {
                    $horariosOrganizados[$horario->dia_semana][$horario->hora_numero] = [];
                }
                
                $horariosOrganizados[$horario->dia_semana][$horario->hora_numero][] = [
                    'asignatura' => $horario->asignatura,
                    'grado' => $horario->grado,
                    'nivel' => $horario->nivel,
                    'asignatura_id' => $horario->asignatura_id,
                    'grado_id' => $horario->grado_id
                ];
            }

            return response()->json([
                'success' => true,
                'profesor' => $profesor->name,
                'year' => $year,
                'config' => [
                    'hora_inicio' => $config->hora_inicio,
                    'hora_fin' => $config->hora_fin,
                    'duracion_clase' => $config->duracion_clase,
                    'horas_por_dia' => $config->horas_por_dia,
                    'dias_semana' => $diasSemana,
                    'recreo_despues_hora' => $config->recreo_despues_hora,
                    'recreo_duracion' => $config->recreo_duracion
                ],
                'horarios' => $horariosOrganizados
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener horario de profesor', [
                'profesor_id' => $profesorId ?? null,
                'year' => $year ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar horario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar PDF del horario de un profesor
     */
    public function descargarPdf(Request $request)
    {
        try {
            $profesorId = $request->profesor_id;
            $year = $request->year;

            if (!$profesorId || !$year) {
                return redirect()->back()->with('error', 'Debe seleccionar un profesor y un año');
            }

            // Obtener profesor
            $profesor = User::findOrFail($profesorId);

            // Obtener horarios
            $horariosQuery = DB::table('horarios')
                ->join('asignaturas', 'horarios.asignatura_id', '=', 'asignaturas.id')
                ->join('grados', 'horarios.grado_id', '=', 'grados.id')
                ->join('niveles', 'grados.nivel_id', '=', 'niveles.id')
                ->where('horarios.profesor_id', $profesorId)
                ->where('horarios.year', $year)
                ->select(
                    'horarios.dia_semana',
                    'horarios.hora_numero',
                    'horarios.horas_por_dia',
                    'horarios.dias_semana',
                    'horarios.recreo_despues_hora',
                    'horarios.recreo_duracion',
                    'asignaturas.nombre as asignatura',
                    'grados.nombre as grado',
                    'niveles.nombre as nivel'
                )
                ->orderBy('horarios.dia_semana')
                ->orderBy('horarios.hora_numero')
                ->get();

            if ($horariosQuery->isEmpty()) {
                return redirect()->back()->with('error', 'No hay horarios para este profesor');
            }

            // Configuración
            $config = $horariosQuery->first();
            $diasSemana = is_string($config->dias_semana) 
                ? json_decode($config->dias_semana, true) 
                : $config->dias_semana;

            if (!is_array($diasSemana)) {
                $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
            }

            // Organizar horarios
            $horarios = [];
            foreach ($diasSemana as $dia) {
                $horarios[$dia] = [];
            }

            foreach ($horariosQuery as $horario) {
                if (!isset($horarios[$horario->dia_semana][$horario->hora_numero])) {
                    $horarios[$horario->dia_semana][$horario->hora_numero] = [];
                }
                
                $horarios[$horario->dia_semana][$horario->hora_numero][] = [
                    'asignatura' => $horario->asignatura,
                    'grado' => $horario->grado,
                    'nivel' => $horario->nivel
                ];
            }

            $configArray = [
                'horas_por_dia' => $config->horas_por_dia,
                'dias_semana' => $diasSemana,
                'recreo_despues_hora' => $config->recreo_despues_hora,
                'recreo_duracion' => $config->recreo_duracion
            ];

            // Generar PDF
            $pdf = Pdf::loadView('horario.horarios-profesor.pdf', [
                'profesorNombre' => $profesor->name,
                'year' => $year,
                'horarios' => $horarios,
                'config' => $configArray
            ]);

            $nombreArchivo = 'horario_' . str_replace(' ', '_', strtolower($profesor->name)) . '_' . $year . '.pdf';

            return $pdf->setPaper('letter', 'portrait')->download($nombreArchivo);

        } catch (\Exception $e) {
            Log::error('Error al generar PDF de horario profesor', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Error al generar PDF: ' . $e->getMessage());
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