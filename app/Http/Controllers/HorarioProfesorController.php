<?php

namespace App\Http\Controllers;

use App\Http\Requests\HorarioProfesor\ObtenerHorarioRequest;
use App\Interfaces\Services\HorarioProfesorServiceInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HorarioProfesorController extends Controller
{
    public function __construct(
        private HorarioProfesorServiceInterface $service
    ) {}

    public function index()
    {
        $datos = $this->service->getDatosIndex();
        return view('horario.horarios-profesor.listar_horario', $datos);
    }

    public function obtenerHorario(ObtenerHorarioRequest $request)
    {
        try {
            $profesorId = (int) $request->input('profesor_id');
            $year       = (int) $request->input('year', date('Y'));

            $resultado = $this->service->obtenerHorario($profesorId, $year);

            if ($resultado['vacio']) {
                return response()->json([
                    'success'  => true,
                    'profesor' => $resultado['profesor'],
                    'year'     => $resultado['year'],
                    'horarios' => [],
                    'message'  => $resultado['message'],
                ]);
            }

            return response()->json([
                'success'  => true,
                'profesor' => $resultado['profesor'],
                'year'     => $resultado['year'],
                'config'   => $resultado['config'],
                'horarios' => $resultado['horarios'],
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener horario de profesor', [
                'profesor_id' => $request->input('profesor_id'),
                'year'        => $request->input('year'),
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar horario: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function descargarPdf(Request $request)
    {
        try {
            $profesorId = (int) $request->profesor_id;
            $year       = (int) $request->year;

            Log::info('🎯 Iniciando PDF profesor', [
                'profesor_id' => $profesorId,
                'year'        => $year,
            ]);

            if (!$profesorId || !$year) {
                return redirect()->back()->with('error', 'Debe seleccionar un profesor y un año');
            }

            $datos = $this->service->getPdfData($profesorId, $year);

            if ($datos['vacio']) {
                return redirect()->back()->with('error', 'No hay horarios para este profesor');
            }

            Log::info('✅ Generando PDF...');

            $pdf = Pdf::loadView('horario.horarios-profesor.pdf', [
                'profesorNombre' => $datos['profesorNombre'],
                'year'           => $datos['year'],
                'horarios'       => $datos['horarios'],
                'config'         => $datos['config'],
                'logoBase64'     => $datos['logoBase64'],
            ]);

            Log::info('📄 Descargando archivo', ['nombre' => $datos['nombreArchivo']]);

            return $pdf->setPaper('letter', 'portrait')->download($datos['nombreArchivo']);

        } catch (\Exception $e) {
            Log::error('❌ Error al generar PDF profesor', [
                'error'   => $e->getMessage(),
                'linea'   => $e->getLine(),
                'archivo' => $e->getFile(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Error al generar PDF: ' . $e->getMessage());
        }
    }
}