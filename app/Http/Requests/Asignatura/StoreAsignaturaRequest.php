<?php

namespace App\Http\Requests\Asignatura;

use Illuminate\Foundation\Http\FormRequest;

class StoreAsignaturaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255|unique:asignaturas,nombre',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la asignatura es obligatorio.',
            'nombre.unique'   => 'Ya existe una asignatura con este nombre.',
        ];
    }
}