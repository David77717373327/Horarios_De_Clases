<?php

namespace App\Http\Controllers;

use App\Http\Requests\Grado\StoreGradoRequest;
use App\Http\Requests\Grado\UpdateGradoRequest;
use App\Interfaces\Services\GradoServiceInterface;
use App\Models\Grado;

class GradoController extends Controller
{
    public function __construct(
        private GradoServiceInterface $gradoService
    ) {}

    public function index()
    {
        $grados  = $this->gradoService->getAllGrados();
        $niveles = $this->gradoService->getNiveles();

        return view('grado.index', compact('grados', 'niveles'));
    }

    public function store(StoreGradoRequest $request)
    {
        try {
            $this->gradoService->createGrado($request->validated());
            return redirect()->route('grados.index')
                ->with('success', 'Grado creado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('grados.index')
                ->with('error', 'Error al crear el grado: ' . $e->getMessage());
        }
    }

    public function update(UpdateGradoRequest $request, Grado $grado)
    {
        try {
            $this->gradoService->updateGrado($grado, $request->validated());
            return redirect()->route('grados.index')
                ->with('success', 'Grado actualizado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('grados.index')
                ->with('error', 'Error al actualizar el grado: ' . $e->getMessage());
        }
    }

    public function destroy(Grado $grado)
    {
        try {
            $this->gradoService->deleteGrado($grado);
            return redirect()->route('grados.index')
                ->with('success', "El grado '{$grado->nombre}' ha sido eliminado correctamente");
        } catch (\Exception $e) {
            return redirect()->route('grados.index')
                ->with('error', 'Error al eliminar el grado: ' . $e->getMessage());
        }
    }
}