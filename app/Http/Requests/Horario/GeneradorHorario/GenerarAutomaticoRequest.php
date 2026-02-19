<?php

namespace App\Http\Requests\Horario\GeneradorHorario;

use Illuminate\Foundation\Http\FormRequest;

class GenerarAutomaticoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'year'                => 'required|integer|min:2020|max:2100',
            'limpiar_existentes'  => 'boolean',
            'hora_inicio'         => 'required|date_format:H:i',
            'hora_fin'            => 'required|date_format:H:i',
            'duracion_clase'      => 'required|integer|min:30|max:120',
            'horas_por_dia'       => 'required|integer|min:1|max:12',
            'dias_semana'         => 'required|array',
            'recreo_despues_hora' => 'nullable|integer',
            'recreo_duracion'     => 'nullable|integer',
        ];
    }
}