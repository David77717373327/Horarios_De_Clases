<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profesor\AsignarAsignaturasRequest;
use App\Http\Requests\Profesor\StoreProfesorRequest;
use App\Http\Requests\Profesor\UpdateProfesorRequest;
use App\Interfaces\Services\ProfesorServiceInterface;

class ProfesorController extends Controller
{
    public function __construct(
        private ProfesorServiceInterface $profesorService
    ) {}

    public function index()
    {
        $profesores  = $this->profesorService->getAllProfesores();
        $asignaturas = $this->profesorService->getAllAsignaturas();
        return view('profesores.index', compact('profesores', 'asignaturas'));
    }

    public function store(StoreProfesorRequest $request)
    {
        try {
            $profesor = $this->profesorService->createProfesor($request->validated());
            return response()->json([
                'success'  => true,
                'message'  => 'Profesor creado correctamente',
                'profesor' => $profesor,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el profesor: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $profesor = $this->profesorService->getProfesorById($id);
            return response()->json([
                'success'           => true,
                'profesor'          => $profesor,
                'asignaturas'       => $profesor->asignaturas,
                'total_asignaturas' => $profesor->asignaturas->count(),
                'total_horarios'    => $profesor->horarios->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información del profesor',
            ], 500);
        }
    }

    public function update(UpdateProfesorRequest $request, $id)
    {
        try {
            $profesor = $this->profesorService->updateProfesor($id, $request->validated());
            return response()->json([
                'success'  => true,
                'message'  => 'Profesor actualizado correctamente',
                'profesor' => $profesor,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el profesor: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function asignarAsignaturas(AsignarAsignaturasRequest $request, $id)
    {
        try {
            $profesor = $this->profesorService->asignarAsignaturas($id, $request->validated());
            return response()->json([
                'success'  => true,
                'message'  => 'Asignaturas actualizadas correctamente',
                'profesor' => $profesor,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar asignaturas: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $nombre = $this->profesorService->deleteProfesor($id);
            return response()->json([
                'success' => true,
                'message' => "Profesor {$nombre} eliminado correctamente",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el profesor: ' . $e->getMessage(),
            ], 500);
        }
    }
}