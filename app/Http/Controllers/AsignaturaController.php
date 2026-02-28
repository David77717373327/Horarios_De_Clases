<?php

namespace App\Http\Controllers;

use App\Http\Requests\Asignatura\StoreAsignaturaRequest;
use App\Http\Requests\Asignatura\UpdateAsignaturaRequest;
use App\Interfaces\Services\AsignaturaServiceInterface;
use App\Models\Asignatura;

class AsignaturaController extends Controller
{
    public function __construct(
        private AsignaturaServiceInterface $asignaturaService
    ) {}

    public function index()
    {
        $asignaturas = $this->asignaturaService->getAllAsignaturas();
        return view('asignaturas.index', compact('asignaturas'));
    }

    public function store(StoreAsignaturaRequest $request)
    {
        try {
            $creados = $this->asignaturaService->createAsignaturas(
                $request->validated()['nombres']
            );

            $mensaje = $creados === 1
                ? 'Asignatura creada exitosamente.'
                : "{$creados} asignaturas creadas exitosamente.";

            return redirect()->route('asignaturas.index')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            return redirect()->route('asignaturas.index')
                ->with('error', 'Error al crear las asignaturas: ' . $e->getMessage());
        }
    }

    public function update(UpdateAsignaturaRequest $request, Asignatura $asignatura)
    {
        try {
            $this->asignaturaService->updateAsignatura($asignatura, $request->validated());
            return redirect()->route('asignaturas.index')
                ->with('success', 'Asignatura actualizada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('asignaturas.index')
                ->with('error', 'Error al actualizar la asignatura: ' . $e->getMessage());
        }
    }

    public function destroy(Asignatura $asignatura)
    {
        try {
            $this->asignaturaService->deleteAsignatura($asignatura);
            return redirect()->route('asignaturas.index')
                ->with('success', 'Asignatura eliminada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('asignaturas.index')
                ->with('error', 'Error al eliminar la asignatura: ' . $e->getMessage());
        }
    }
}