<?php

namespace App\Http\Requests\RestriccionProfesor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRestriccionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'profesor_id'      => 'required|exists:users,id',
            'dia_semana'       => 'nullable|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado',
            'tipo_restriccion' => 'required|in:hora_especifica,rango_horario,dia_completo',
            'hora_numero'      => 'nullable|integer|min:1|max:12',
            'hora_inicio'      => 'nullable|date_format:H:i',
            'hora_fin'         => 'nullable|date_format:H:i',
            'motivo'           => 'nullable|string|max:100',
            'year'             => 'required|integer|min:2020|max:2100',
            'activa'           => 'boolean',
        ];
    }
}