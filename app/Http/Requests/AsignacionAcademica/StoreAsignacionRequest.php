<?php

namespace App\Http\Requests\AsignacionAcademica;

use Illuminate\Foundation\Http\FormRequest;

class StoreAsignacionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'profesor_id'      => 'required|exists:users,id',
            'asignatura_id'    => 'required|exists:asignaturas,id',
            'grado_id'         => 'required|exists:grados,id',
            'horas_semanales'  => 'required|integer|min:1|max:40',
            'year'             => 'required|integer',
            'periodo_id'       => 'nullable|integer|min:1|max:4',
            'posicion_jornada' => 'nullable|in:primeras_horas,ultimas_horas,antes_recreo,despues_recreo,sin_restriccion',
            'max_horas_por_dia' => 'nullable|integer|min:1|max:8',
            'max_dias_semana'  => 'nullable|integer|min:1|max:5',
        ];
    }
}