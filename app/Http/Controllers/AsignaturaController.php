<?php

namespace App\Http\Controllers;

use App\Models\Asignatura;
use Illuminate\Http\Request;

class AsignaturaController extends Controller
{
    // Mostrar listado de asignaturas
    public function index()
    {
        $asignaturas = Asignatura::orderBy('nombre')->get();
        return view('asignaturas.index', compact('asignaturas'));
    }

    // Guardar nueva asignatura
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:asignaturas,nombre',
        ], [
            'nombre.required' => 'El nombre de la asignatura es obligatorio.',
            'nombre.unique' => 'Ya existe una asignatura con este nombre.',
        ]);

        Asignatura::create([
            'nombre' => $request->nombre,
        ]);

        return redirect()->route('asignaturas.index')
            ->with('success', 'Asignatura creada exitosamente.');
    }

    // Actualizar asignatura
    public function update(Request $request, Asignatura $asignatura)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:asignaturas,nombre,' . $asignatura->id,
        ], [
            'nombre.required' => 'El nombre de la asignatura es obligatorio.',
            'nombre.unique' => 'Ya existe una asignatura con este nombre.',
        ]);

        $asignatura->update([
            'nombre' => $request->nombre,
        ]);

        return redirect()->route('asignaturas.index')
            ->with('success', 'Asignatura actualizada exitosamente.');
    }

    // Eliminar asignatura
    public function destroy(Asignatura $asignatura)
    {
        $asignatura->delete();

        return redirect()->route('asignaturas.index')
            ->with('success', 'Asignatura eliminada exitosamente.');
    }
}