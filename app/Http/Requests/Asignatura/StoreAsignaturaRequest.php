<?php

namespace App\Http\Requests\Asignatura;

use Illuminate\Foundation\Http\FormRequest;

class StoreAsignaturaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombres'   => 'required|array|min:1',
            'nombres.*' => 'required|string|max:255|distinct|unique:asignaturas,nombre',
        ];
    }

    public function messages(): array
    {
        return [
            'nombres.required'   => 'Debes ingresar al menos una asignatura.',
            'nombres.array'      => 'El formato de los datos no es válido.',
            'nombres.min'        => 'Debes ingresar al menos una asignatura.',
            'nombres.*.required' => 'El nombre de la asignatura no puede estar vacío.',
            'nombres.*.string'   => 'El nombre debe ser texto.',
            'nombres.*.max'      => 'El nombre no puede superar los 255 caracteres.',
            'nombres.*.distinct' => 'Hay nombres duplicados en la lista.',
            'nombres.*.unique'   => 'Ya existe una asignatura con ese nombre.',  // ← ESTO
        ];
    }

    /**
     * Limpia los nombres antes de validar:
     * elimina vacíos y hace trim a cada uno.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('nombres')) {
            $limpios = array_values(
                array_filter(
                    array_map('trim', $this->nombres ?? []),
                    fn($n) => $n !== ''
                )
            );
            $this->merge(['nombres' => $limpios]);
        }
    }
}
