<?php

namespace App\Http\Requests\Profesor;

use Illuminate\Foundation\Http\FormRequest;

class AsignarAsignaturasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asignaturas'   => 'nullable|array',
            'asignaturas.*' => 'exists:asignaturas,id',
        ];
    }
}