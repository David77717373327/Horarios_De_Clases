<?php

namespace App\Http\Controllers;

use App\Models\Grado;
use App\Models\Nivel;
use Illuminate\Http\Request;

class GradoController extends Controller
{
    public function index()
    {
        // Cargar grados con el conteo de horarios para el mensaje de eliminación
        $grados = Grado::with('nivel')
            ->withCount('horarios')
            ->orderBy('id')
            ->get();
            
        $niveles = Nivel::orderBy('nombre')->get();

        return view('grado.index', compact('grados', 'niveles'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre'   => 'required|string|max:50',
                'nivel_id' => 'required|exists:niveles,id',
            ], [
                'nombre.required' => 'El nombre del grado es obligatorio',
                'nombre.max' => 'El nombre no puede exceder 50 caracteres',
                'nivel_id.required' => 'Debe seleccionar un nivel académico',
                'nivel_id.exists' => 'El nivel seleccionado no existe',
            ]);

            Grado::create($request->only('nombre', 'nivel_id'));

            return redirect()->route('grados.index')
                ->with('success', 'Grado creado correctamente');
                
        } catch (\Exception $e) {
            return redirect()->route('grados.index')
                ->with('error', 'Error al crear el grado: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Grado $grado)
    {
        try {
            $request->validate([
                'nombre'   => 'required|string|max:50',
                'nivel_id' => 'required|exists:niveles,id',
            ], [
                'nombre.required' => 'El nombre del grado es obligatorio',
                'nombre.max' => 'El nombre no puede exceder 50 caracteres',
                'nivel_id.required' => 'Debe seleccionar un nivel académico',
                'nivel_id.exists' => 'El nivel seleccionado no existe',
            ]);

            $grado->update($request->only('nombre', 'nivel_id'));

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
            $nombreGrado = $grado->nombre;
            $grado->delete();

            return redirect()->route('grados.index')
                ->with('success', "El grado '{$nombreGrado}' ha sido eliminado correctamente");
                
        } catch (\Exception $e) {
            return redirect()->route('grados.index')
                ->with('error', 'Error al eliminar el grado: ' . $e->getMessage());
        }
    }
}