<?php

namespace App\Http\Controllers;

use App\Http\Requests\Nivel\StoreNivelRequest;
use App\Http\Requests\Nivel\UpdateNivelRequest;
use App\Interfaces\Services\NivelServiceInterface;

class NivelController extends Controller
{
    public function __construct(
        private NivelServiceInterface $nivelService
    ) {}

    public function index()
    {
        $niveles = $this->nivelService->getAllNiveles();
        return view('nivel.index', compact('niveles'));
    }

    public function store(StoreNivelRequest $request)
    {
        try {
            $this->nivelService->createNivel($request->validated());
            return redirect()->route('niveles.index')
                ->with('success', 'Nivel académico creado exitosamente');
        } catch (\Exception $e) {
            return redirect()->route('niveles.index')
                ->with('error', 'Error al crear el nivel: ' . $e->getMessage());
        }
    }

    public function update(UpdateNivelRequest $request, $id)
    {
        try {
            $this->nivelService->updateNivel($id, $request->validated());
            return redirect()->route('niveles.index')
                ->with('success', 'Nivel actualizado exitosamente');
        } catch (\Exception $e) {
            return redirect()->route('niveles.index')
                ->with('error', 'Error al actualizar el nivel: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->nivelService->deleteNivel($id);
            return redirect()->route('niveles.index')
                ->with('success', 'Nivel y todas sus relaciones eliminadas exitosamente');
        } catch (\Exception $e) {
            return redirect()->route('niveles.index')
                ->with('error', 'Error al eliminar el nivel. Contacta al administrador.');
        }
    }
}