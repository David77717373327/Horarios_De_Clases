<?php

namespace App\Http\Controllers;

use App\Models\Grado;
use App\Models\Nivel;
use Illuminate\Http\Request;

class GradoController extends Controller
{
    public function index()
    {
        $grados = Grado::with('nivel')->orderBy('id')->get();
        $niveles = Nivel::orderBy('nombre')->get();

        return view('grado.index', compact('grados', 'niveles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'   => 'required|string|max:50',
            'nivel_id' => 'required|exists:niveles,id',
        ]);

        Grado::create($request->only('nombre', 'nivel_id'));

        return redirect()->route('grados.index')
            ->with('success', 'Grado creado correctamente');
    }

    public function update(Request $request, Grado $grado)
    {
        $request->validate([
            'nombre'   => 'required|string|max:50',
            'nivel_id' => 'required|exists:niveles,id',
        ]);

        $grado->update($request->only('nombre', 'nivel_id'));

        return redirect()->route('grados.index')
            ->with('success', 'Grado actualizado correctamente');
    }

    public function destroy(Grado $grado)
    {
        $grado->delete();

        return redirect()->route('grados.index')
            ->with('success', 'Grado eliminado correctamente');
    }
}
