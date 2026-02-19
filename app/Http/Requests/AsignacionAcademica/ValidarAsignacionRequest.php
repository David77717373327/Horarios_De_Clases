<?php

namespace App\Http\Requests\AsignacionAcademica;

use Illuminate\Foundation\Http\FormRequest;

class ValidarAsignacionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'profesor_id'   => 'required|exists:users,id',
            'asignatura_id' => 'required|exists:asignaturas,id',
            'grado_id'      => 'required|exists:grados,id',
            'year'          => 'required|integer',
            'id'            => 'nullable|integer',
        ];
    }
}