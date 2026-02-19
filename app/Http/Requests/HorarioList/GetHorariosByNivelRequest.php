<?php

namespace App\Http\Requests\HorarioList;

use Illuminate\Foundation\Http\FormRequest;

class GetHorariosByNivelRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nivel_id' => 'required|exists:niveles,id',
            'year'     => 'required|integer|min:2020|max:2100',
        ];
    }
}