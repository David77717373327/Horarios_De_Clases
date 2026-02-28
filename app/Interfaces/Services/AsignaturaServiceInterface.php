<?php

namespace App\Interfaces\Services;

use App\Models\Asignatura;
use Illuminate\Database\Eloquent\Collection;

interface AsignaturaServiceInterface
{
    public function getAllAsignaturas(): Collection;

    /**
     * Crea una o varias asignaturas.
     * Retorna la cantidad de registros creados.
     */
    public function createAsignaturas(array $nombres): int;

    public function updateAsignatura(Asignatura $asignatura, array $data): Asignatura;
    public function deleteAsignatura(Asignatura $asignatura): void;
}