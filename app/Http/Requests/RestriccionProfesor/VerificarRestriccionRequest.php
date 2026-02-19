<?php

namespace App\Http\Requests\RestriccionProfesor;

use Illuminate\Foundation\Http\FormRequest;

class VerificarRestriccionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'profesor_id' => 'required|exists:users,id',
            'dia'         => 'required|string',
            'hora_numero' => 'nullable|integer',
            'year'        => 'required|integer',
            'hora'        => 'nullable|date_format:H:i',
        ];
    }
}