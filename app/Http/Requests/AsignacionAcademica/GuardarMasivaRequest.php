<?php

namespace App\Http\Requests\AsignacionAcademica;

use Illuminate\Foundation\Http\FormRequest;

class GuardarMasivaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'asignaciones'                   => 'required|array|min:1',
            'asignaciones.*.profesor_id'     => 'required|exists:users,id',
            'asignaciones.*.asignatura_id'   => 'required|exists:asignaturas,id',
            'asignaciones.*.grado_id'        => 'required|exists:grados,id',
            'asignaciones.*.horas_semanales' => 'required|integer|min:1|max:40',
            'asignaciones.*.year'            => 'required|integer',
        ];
    }
}