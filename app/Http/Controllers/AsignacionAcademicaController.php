<?php

namespace App\Http\Controllers;

use App\Http\Requests\AsignacionAcademica\CopiarYearRequest;
use App\Http\Requests\AsignacionAcademica\GuardarMasivaRequest;
use App\Http\Requests\AsignacionAcademica\StoreAsignacionRequest;
use App\Http\Requests\AsignacionAcademica\UpdateAsignacionRequest;
use App\Http\Requests\AsignacionAcademica\ValidarAsignacionRequest;
use App\Interfaces\Services\AsignacionAcademicaServiceInterface;
use Illuminate\Http\Request;

class AsignacionAcademicaController extends Controller
{
    public function __construct(
        private AsignacionAcademicaServiceInterface $service
    ) {}

    public function index()
    {
        $datos = $this->service->getDatosIndex();
        return view('asignaciones.index', $datos);
    }

    public function listar(Request $request)
    {
        try {
            $asignaciones = $this->service->listarConFiltros($request->only([
                'year', 'profesor_id', 'nivel_id', 'grado_id'
            ]));
            return response()->json(['success' => true, 'asignaciones' => $asignaciones]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cargar asignaciones: ' . $e->getMessage()], 500);
        }
    }

    public function gradosPorNivel($nivelId)
    {
        try {
            $resultado = $this->service->getGradosPorNivel($nivelId);
            if (!$resultado['encontrado']) {
                return response()->json(['success' => false, 'message' => 'Nivel no encontrado', 'grados' => []], 404);
            }
            return response()->json(['success' => true, 'grados' => $resultado['grados'], 'total' => $resultado['grados']->count()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cargar grados: ' . $e->getMessage(), 'grados' => []], 500);
        }
    }

    public function getGradosByNivel($nivelId)
    {
        return $this->gradosPorNivel($nivelId);
    }

    public function obtenerMatriz($gradoId, Request $request)
    {
        try {
            $year = $request->input('year', date('Y'));
            $data = $this->service->getMatriz($gradoId, $year);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cargar la matriz: ' . $e->getMessage()], 500);
        }
    }

    public function guardarMasiva(GuardarMasivaRequest $request)
    {
        try {
            $resultado = $this->service->guardarMasiva($request->validated()['asignaciones']);
            return response()->json([
                'success'     => true,
                'message'     => $resultado['mensaje'],
                'guardadas'   => $resultado['guardadas'],
                'actualizadas' => $resultado['actualizadas'],
                'total'       => $resultado['guardadas'] + $resultado['actualizadas'],
                'errores'     => $resultado['errores'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al guardar asignaciones: ' . $e->getMessage()], 500);
        }
    }

    public function resumenYear(Request $request)
    {
        try {
            $year = $request->input('year');
            if (!$year) {
                return response()->json(['success' => false, 'message' => 'Debe proporcionar un año'], 422);
            }
            $resumen = $this->service->getResumenYear($year);
            return response()->json(['success' => true, ...$resumen]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener resumen: ' . $e->getMessage()], 500);
        }
    }

    public function copiarYear(CopiarYearRequest $request)
    {
        try {
            $data = $request->validated();
            $resultado = $this->service->copiarYear(
                $data['year_origen'],
                $data['year_destino'],
                $data['sobreescribir'] ?? false
            );

            if ($resultado['vacio']) {
                return response()->json(['success' => false, 'message' => "No hay asignaciones en el año {$resultado['yearOrigen']} para copiar"], 404);
            }

            return response()->json([
                'success'   => true,
                'message'   => 'Asignaciones copiadas exitosamente',
                'copiadas'  => $resultado['copiadas'],
                'eliminadas' => $resultado['eliminadas'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al copiar asignaciones: ' . $e->getMessage()], 500);
        }
    }

    public function store(StoreAsignacionRequest $request)
    {
        try {
            $asignacion = $this->service->store($request->validated());
            return response()->json(['success' => true, 'message' => 'Asignación creada exitosamente', 'asignacion' => $asignacion], 201);
        } catch (\Exception $e) {
            $code = (int) $e->getCode() === 422 ? 422 : 500;
            return response()->json(['success' => false, 'message' => $e->getMessage()], $code);
        }
    }

    public function show($id)
    {
        try {
            $asignacion = $this->service->show($id);
            return response()->json(['success' => true, 'asignacion' => $asignacion]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Asignación no encontrada'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener asignación'], 500);
        }
    }

    public function update(UpdateAsignacionRequest $request, $id)
    {
        try {
            $asignacion = $this->service->update($id, $request->validated());
            return response()->json(['success' => true, 'message' => 'Asignación actualizada exitosamente', 'asignacion' => $asignacion]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Asignación no encontrada'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $mensaje = $this->service->destroy($id);
            return response()->json(['success' => true, 'message' => "Asignación eliminada: {$mensaje}"]);
        } catch (\Exception $e) {
            // Detectar si el error es por horarios existentes
            if (str_starts_with($e->getMessage(), 'tiene_horarios:')) {
                $total = explode(':', $e->getMessage())[1];
                return response()->json([
                    'success'        => false,
                    'message'        => "No se puede eliminar porque tiene {$total} clase(s) programada(s). Elimine primero los horarios.",
                    'tiene_horarios' => true,
                    'total_horarios' => (int) $total,
                ], 422);
            }
            return response()->json(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }

    public function estadisticas(Request $request)
    {
        try {
            $year = $request->input('year', date('Y'));
            $estadisticas = $this->service->getEstadisticas($year);
            return response()->json(['success' => true, 'estadisticas' => $estadisticas]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener estadísticas: ' . $e->getMessage()], 500);
        }
    }

    public function resumenProfesor($profesorId)
    {
        try {
            $year   = request()->input('year', date('Y'));
            $resumen = $this->service->getResumenProfesor($profesorId, $year);
            return response()->json(['success' => true, 'resumen' => $resumen]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener resumen del profesor: ' . $e->getMessage()], 500);
        }
    }

    public function resumenGrado($gradoId)
    {
        try {
            $year   = request()->input('year', date('Y'));
            $resumen = $this->service->getResumenGrado($gradoId, $year);
            return response()->json(['success' => true, 'resumen' => $resumen]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener resumen del grado: ' . $e->getMessage()], 500);
        }
    }

    public function validar(ValidarAsignacionRequest $request)
    {
        try {
            $errores = $this->service->validar($request->validated());
            return response()->json(['success' => empty($errores), 'valido' => empty($errores), 'errores' => $errores]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al validar: ' . $e->getMessage()], 500);
        }
    }
}