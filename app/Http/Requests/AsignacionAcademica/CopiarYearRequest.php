<?php

namespace App\Http\Requests\AsignacionAcademica;

use Illuminate\Foundation\Http\FormRequest;

class CopiarYearRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'year_origen'  => 'required|integer',
            'year_destino' => 'required|integer|different:year_origen',
            'sobreescribir' => 'boolean',
        ];
    }
}