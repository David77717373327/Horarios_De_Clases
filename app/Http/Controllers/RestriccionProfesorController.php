<?php

namespace App\Http\Controllers;

use App\Http\Requests\RestriccionProfesor\StoreRestriccionRequest;
use App\Http\Requests\RestriccionProfesor\UpdateRestriccionRequest;
use App\Http\Requests\RestriccionProfesor\VerificarRestriccionRequest;
use App\Interfaces\Services\RestriccionProfesorServiceInterface;
use Illuminate\Http\Request;

class RestriccionProfesorController extends Controller
{
    public function __construct(
        private RestriccionProfesorServiceInterface $service
    ) {}

    public function index()
    {
        $datos = $this->service->getDatosIndex();
        return view('restricciones.index', $datos);
    }

    public function listar(Request $request)
    {
        try {
            $restricciones = $this->service->listarConFiltros(
                $request->only(['year', 'profesor_id', 'activa'])
            );
            return response()->json(['success' => true, 'restricciones' => $restricciones]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cargar las restricciones'], 500);
        }
    }

    public function store(StoreRestriccionRequest $request)
    {
        try {
            $restriccion = $this->service->store($request->validated());
            return response()->json(['success' => true, 'message' => 'Restricción creada exitosamente', 'restriccion' => $restriccion], 201);
        } catch (\Exception $e) {
            $code = (int) $e->getCode() === 422 ? 422 : 500;
            return response()->json(['success' => false, 'message' => $e->getMessage()], $code);
        }
    }

    public function show($id)
    {
        try {
            $restriccion = $this->service->show($id);
            return response()->json(['success' => true, 'restriccion' => $restriccion]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Restricción no encontrada'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener la restricción'], 500);
        }
    }

    public function update(UpdateRestriccionRequest $request, $id)
    {
        try {
            $restriccion = $this->service->update($id, $request->validated());
            return response()->json(['success' => true, 'message' => 'Restricción actualizada exitosamente', 'restriccion' => $restriccion]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Restricción no encontrada'], 404);
        } catch (\Exception $e) {
            $code = (int) $e->getCode() === 422 ? 422 : 500;
            return response()->json(['success' => false, 'message' => $e->getMessage()], $code);
        }
    }

    public function destroy($id)
    {
        try {
            $mensaje = $this->service->destroy($id);
            return response()->json(['success' => true, 'message' => "Restricción eliminada: {$mensaje}"]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Restricción no encontrada'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar la restricción'], 500);
        }
    }

    public function toggleActiva($id)
    {
        try {
            $resultado = $this->service->toggleActiva($id);
            return response()->json(['success' => true, 'message' => "Restricción {$resultado['estado']} exitosamente", 'activa' => $resultado['activa']]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cambiar el estado'], 500);
        }
    }

    public function restriccionesProfesor($profesorId, Request $request)
    {
        try {
            $year = $request->input('year', date('Y'));
            $restricciones = $this->service->getRestriccionesProfesor($profesorId, $year);
            return response()->json(['success' => true, 'restricciones' => $restricciones]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener restricciones'], 500);
        }
    }

    public function verificarRestriccion(VerificarRestriccionRequest $request)
    {
        try {
            $resultado = $this->service->verificarRestriccion($request->validated());
            return response()->json([
                'success'           => !$resultado['tiene_restriccion'],
                'tiene_restriccion' => $resultado['tiene_restriccion'],
                'message'           => $resultado['message'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al verificar restricción'], 500);
        }
    }

    public function horasBloqueadas($profesorId, Request $request)
    {
        try {
            $dia  = $request->input('dia');
            $year = $request->input('year', date('Y'));

            if (!$dia) {
                return response()->json(['success' => false, 'message' => 'Debe especificar el día'], 422);
            }

            $horas = $this->service->getHorasBloqueadas($profesorId, $dia, $year);

            return response()->json([
                'success'         => true,
                'horas_bloqueadas' => $horas,
                'profesor_id'     => $profesorId,
                'dia'             => $dia,
                'year'            => $year,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener horas bloqueadas'], 500);
        }
    }
}