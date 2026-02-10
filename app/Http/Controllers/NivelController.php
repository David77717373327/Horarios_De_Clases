<?php

namespace App\Http\Controllers;

use App\Models\Nivel;
use Illuminate\Http\Request;

class NivelController extends Controller
{
    public function index()
    {
        $niveles = Nivel::orderBy('id')->get();
        return view('nivel.index', compact('niveles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:niveles,nombre',
        ]);

        Nivel::create([
            'nombre' => $request->nombre,
        ]);

        return redirect()->route('niveles.index')
            ->with('success', 'Nivel creado correctamente');
    }

    public function update(Request $request, Nivel $nivel)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:niveles,nombre,' . $nivel->id,
        ]);

        $nivel->update([
            'nombre' => $request->nombre,
        ]);

        return redirect()->route('niveles.index')
            ->with('success', 'Nivel actualizado correctamente');
    }

    public function destroy(Nivel $nivel)
    {
        $nivel->delete();

        return redirect()->route('niveles.index')
            ->with('success', 'Nivel eliminado correctamente');
    }
}
