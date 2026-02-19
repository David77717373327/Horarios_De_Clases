<?php

namespace App\Http\Controllers;

use App\Http\Requests\Horario\DestroyHorarioRequest;
use App\Http\Requests\Horario\GetHorarioRequest;
use App\Interfaces\Services\Horario\HorarioServiceInterface;

class HorarioController extends Controller
{
    public function __construct(
        private HorarioServiceInterface $horarioService
    ) {}

    public function index()
    {
        $datos = $this->horarioService->getDatosIndex();
        return view('horario.index', $datos);
    }

    public function create()
    {
        $datos = $this->horarioService->getDatosCreate();
        return view('horario.create', $datos);
    }

    public function getGradosByNivel($nivelId)
    {
        try {
            $grados = $this->horarioService->getGradosByNivel($nivelId);
            return response()->json(['success' => true, 'grados' => $grados]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cargar los grados'], 500);
        }
    }

    public function getHorario(GetHorarioRequest $request)
    {
        try {
            $resultado = $this->horarioService->getHorario($request->validated());
            return response()->json(['success' => true, 'horarios' => $resultado['horarios'], 'config' => $resultado['config']]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cargar el horario'], 500);
        }
    }

    public function destroy(DestroyHorarioRequest $request)
    {
        try {
            $resultado = $this->horarioService->destroy($request->validated());
            return response()->json(['success' => true, 'message' => 'Horario eliminado exitosamente', 'deleted_count' => $resultado['deleted_count']]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar el horario'], 500);
        }
    }
}