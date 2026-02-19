<?php

namespace App\Http\Requests\Horario;

use Illuminate\Foundation\Http\FormRequest;

class GetHorarioRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nivel_id' => 'required|exists:niveles,id',
            'grado_id' => 'required|exists:grados,id',
            'year'     => 'required|integer|min:2020|max:2100',
        ];
    }
}