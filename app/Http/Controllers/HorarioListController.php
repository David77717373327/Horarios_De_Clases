<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use App\Models\Nivel;
use App\Models\Grado;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HorarioListController extends Controller
{

    public function pdf(Request $request)
    {
        $nivelId = $request->nivel_id;
        $year = $request->year;

        // Obtener el nombre del nivel desde la base de datos
        $nivel = Nivel::find($nivelId);
        $nivelNombre = $nivel ? $nivel->nombre : 'Sin Nivel';

        $horarios = Horario::with(['grado', 'asignatura', 'profesor'])
            ->where('nivel_id', $nivelId)
            ->where('year', $year)
            ->orderBy('grado_id')
            ->orderBy('hora_numero')
            ->orderBy('dia_semana')
            ->get()
            ->groupBy('grado_id');

        return Pdf::loadView('horario.pdf', compact('horarios', 'year', 'nivelNombre'))
            ->setPaper('letter', 'landscape')
            ->download('horario-academico.pdf');
    }



public function pdfSoloMaterias(Request $request)
{
    $nivelId = $request->nivel_id;
    $year = $request->year;

    $nivel = Nivel::find($nivelId);
    $nivelNombre = $nivel ? $nivel->nombre : 'Sin Nivel';

    $horarios = Horario::with(['grado', 'asignatura', 'profesor'])
        ->where('nivel_id', $nivelId)
        ->where('year', $year)
        ->orderBy('grado_id')
        ->orderBy('hora_numero')
        ->orderBy('dia_semana')
        ->get()
        ->groupBy('grado_id');

    return Pdf::loadView('horario.pdf-solo-materias', compact('horarios', 'year', 'nivelNombre'))
        ->setPaper('letter', 'landscape')
        ->download('horario-solo-materias.pdf');
}




















    /**
     * Mostrar vista principal de listado de horarios
     */
    public function index()
    {
        $niveles = Nivel::orderBy('nombre')->get();
        $years = $this->getAcademicYears();
        
        return view('horario.listar_horario', compact('niveles', 'years'));
    }

    /**
     * Obtener todos los horarios por nivel y año
     */
    public function getHorariosByNivel(Request $request)
    {
        try {
            $validated = $request->validate([
                'nivel_id' => 'required|exists:niveles,id',
                'year' => 'required|integer|min:2020|max:2100'
            ]);

            $nivel = Nivel::findOrFail($validated['nivel_id']);
            
            // Obtener todos los grados del nivel
            $grados = Grado::where('nivel_id', $validated['nivel_id'])
                ->orderBy('nombre')
                ->get();

            $horariosData = [];

            foreach ($grados as $grado) {
                $horarios = Horario::where('nivel_id', $validated['nivel_id'])
                    ->where('grado_id', $grado->id)
                    ->where('year', $validated['year'])
                    ->with(['asignatura', 'profesor'])
                    ->get();

                if ($horarios->isNotEmpty()) {
                    $firstHorario = $horarios->first();
                    
                    // Obtener configuración
                    $config = [
                        'hora_inicio' => $firstHorario->hora_inicio,
                        'hora_fin' => $firstHorario->hora_fin,
                        'duracion_clase' => $firstHorario->duracion_clase,
                        'horas_por_dia' => $firstHorario->horas_por_dia,
                        'dias_semana' => json_decode($firstHorario->dias_semana),
                        'recreo_despues_hora' => $firstHorario->recreo_despues_hora,
                        'recreo_duracion' => $firstHorario->recreo_duracion
                    ];

                    // Organizar horarios por día y hora
                    $horarioOrganizado = [];
                    foreach ($horarios as $horario) {
                        $horarioOrganizado[$horario->dia_semana][$horario->hora_numero] = [
                            'asignatura' => $horario->asignatura->nombre,
                            'profesor' => $horario->profesor->name,
                            'asignatura_id' => $horario->asignatura_id,
                            'profesor_id' => $horario->profesor_id
                        ];
                    }

                    $horariosData[] = [
                        'grado' => $grado->nombre,
                        'grado_id' => $grado->id,
                        'config' => $config,
                        'horarios' => $horarioOrganizado
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'nivel' => $nivel->nombre,
                'year' => $validated['year'],
                'horarios' => $horariosData
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener horarios por nivel', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los horarios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de horarios
     */
    public function getEstadisticas(Request $request)
    {
        try {
            $validated = $request->validate([
                'nivel_id' => 'required|exists:niveles,id',
                'year' => 'required|integer'
            ]);

            $nivel = Nivel::findOrFail($validated['nivel_id']);
            
            // Total de grados en el nivel
            $totalGrados = Grado::where('nivel_id', $validated['nivel_id'])->count();
            
            // Grados con horarios asignados
            $gradosConHorario = Horario::where('nivel_id', $validated['nivel_id'])
                ->where('year', $validated['year'])
                ->distinct('grado_id')
                ->count('grado_id');
            
            // Total de clases programadas
            $totalClases = Horario::where('nivel_id', $validated['nivel_id'])
                ->where('year', $validated['year'])
                ->count();
            
            // Profesores únicos asignados
            $profesoresUnicos = Horario::where('nivel_id', $validated['nivel_id'])
                ->where('year', $validated['year'])
                ->distinct('profesor_id')
                ->count('profesor_id');
            
            // Asignaturas únicas
            $asignaturasUnicas = Horario::where('nivel_id', $validated['nivel_id'])
                ->where('year', $validated['year'])
                ->distinct('asignatura_id')
                ->count('asignatura_id');

            return response()->json([
                'success' => true,
                'estadisticas' => [
                    'nivel' => $nivel->nombre,
                    'total_grados' => $totalGrados,
                    'grados_con_horario' => $gradosConHorario,
                    'total_clases' => $totalClases,
                    'profesores_unicos' => $profesoresUnicos,
                    'asignaturas_unicas' => $asignaturasUnicas,
                    'porcentaje_completado' => $totalGrados > 0 
                        ? round(($gradosConHorario / $totalGrados) * 100, 1) 
                        : 0
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar estadísticas'
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

    public function getProfesor($profesorId)
{
    try {
        $profesor = User::where('id', $profesorId)
            ->where('role', 'professor')
            ->first(['id', 'name']);

        if (!$profesor) {
            return response()->json([
                'success' => false,
                'message' => 'Profesor no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'profesor' => $profesor
        ]);

    } catch (\Exception $e) {
        Log::error('Error al obtener profesor', ['error' => $e->getMessage()]);
        return response()->json([
            'success' => false,
            'message' => 'Error al obtener información del profesor'
        ], 500);
    }
}

}