<?php

namespace App\Http\Requests\HorarioProfesor;

use Illuminate\Foundation\Http\FormRequest;

class ObtenerHorarioRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'profesor_id' => 'required|exists:users,id',
            'year'        => 'nullable|integer|min:2020|max:2100',
        ];
    }
}