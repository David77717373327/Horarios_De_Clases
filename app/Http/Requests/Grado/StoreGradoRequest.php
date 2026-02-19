<?php

namespace App\Http\Requests\Grado;

use Illuminate\Foundation\Http\FormRequest;

class StoreGradoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'   => 'required|string|max:50',
            'nivel_id' => 'required|exists:niveles,id',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'   => 'El nombre del grado es obligatorio',
            'nombre.max'        => 'El nombre no puede exceder 50 caracteres',
            'nivel_id.required' => 'Debe seleccionar un nivel académico',
            'nivel_id.exists'   => 'El nivel seleccionado no existe',
        ];
    }
}