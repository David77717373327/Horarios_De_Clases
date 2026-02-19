<?php

namespace App\Http\Requests\HorarioList;

use Illuminate\Foundation\Http\FormRequest;

class GetEstadisticasRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nivel_id' => 'required|exists:niveles,id',
            'year'     => 'required|integer',
        ];
    }
}