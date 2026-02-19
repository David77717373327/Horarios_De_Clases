<?php

namespace App\Http\Requests\Nivel;

use Illuminate\Foundation\Http\FormRequest;

class StoreNivelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:100|unique:niveles,nombre',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del nivel es obligatorio',
            'nombre.unique'   => 'Este nivel ya existe en el sistema',
            'nombre.max'      => 'El nombre no puede exceder 100 caracteres',
        ];
    }
}