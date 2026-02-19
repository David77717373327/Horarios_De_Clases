<?php

namespace App\Http\Controllers;

use App\Http\Requests\HorarioList\GetEstadisticasRequest;
use App\Http\Requests\HorarioList\GetHorariosByNivelRequest;
use App\Interfaces\Services\HorarioListServiceInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HorarioListController extends Controller
{
    public function __construct(
        private HorarioListServiceInterface $service
    ) {}

    public function index()
    {
        $datos = $this->service->getDatosIndex();
        return view('horario.listar_horario', $datos);
    }

    public function getHorariosByNivel(GetHorariosByNivelRequest $request)
    {
        try {
            $resultado = $this->service->getHorariosByNivel($request->validated());

            return response()->json([
                'success'  => true,
                'nivel'    => $resultado['nivel'],
                'year'     => $resultado['year'],
                'horarios' => $resultado['horarios'],
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener horarios por nivel', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los horarios: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getEstadisticas(GetEstadisticasRequest $request)
    {
        try {
            $estadisticas = $this->service->getEstadisticas($request->validated());

            return response()->json([
                'success'      => true,
                'estadisticas' => $estadisticas,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar estadísticas',
            ], 500);
        }
    }

    public function pdf(Request $request)
    {
        try {
            Log::info('🎯 Iniciando generación de PDF', [
                'nivel_id' => $request->nivel_id,
                'year'     => $request->year,
            ]);

            $datos = $this->service->getPdfData(
                (int) $request->nivel_id,
                (int) $request->year
            );

            Log::info('✅ Generando PDF...');

            return Pdf::loadView('horario.pdf', $datos)
                ->setPaper('letter', 'landscape')
                ->download('horario-academico.pdf');
        } catch (\Exception $e) {
            Log::error('❌ Error generando PDF', [
                'error'   => $e->getMessage(),
                'linea'   => $e->getLine(),
                'archivo' => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al generar PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function pdfSoloMaterias(Request $request)
    {
        try {
            Log::info('🎯 Iniciando generación de PDF Solo Materias', [
                'nivel_id' => $request->nivel_id,
                'year'     => $request->year,
            ]);

            $datos = $this->service->getPdfData(
                (int) $request->nivel_id,
                (int) $request->year
            );

            Log::info('✅ Generando PDF Solo Materias...');

            return Pdf::loadView('horario.pdf-solo-materias', $datos)
                ->setPaper('letter', 'landscape')
                ->download('horario-solo-materias.pdf');
        } catch (\Exception $e) {
            Log::error('❌ Error generando PDF Solo Materias', [
                'error'   => $e->getMessage(),
                'linea'   => $e->getLine(),
                'archivo' => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al generar PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getProfesor($profesorId)
    {
        try {
            $profesor = $this->service->getProfesor((int) $profesorId);

            if (!$profesor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profesor no encontrado',
                ], 404);
            }

            return response()->json([
                'success'  => true,
                'profesor' => $profesor,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener profesor', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información del profesor',
            ], 500);
        }
    }
}