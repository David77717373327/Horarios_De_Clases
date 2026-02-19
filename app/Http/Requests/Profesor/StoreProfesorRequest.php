<?php

namespace App\Http\Requests\Profesor;

use Illuminate\Foundation\Http\FormRequest;

class StoreProfesorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => 'required|string|max:255',
            'asignaturas'    => 'nullable|array',
            'asignaturas.*'  => 'exists:asignaturas,id',
        ];
    }
}