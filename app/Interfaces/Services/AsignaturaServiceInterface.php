<?php

namespace App\Interfaces\Services;

use App\Models\Asignatura;
use Illuminate\Database\Eloquent\Collection;

interface AsignaturaServiceInterface
{
    public function getAllAsignaturas(): Collection;
    public function createAsignatura(array $data): Asignatura;
    public function updateAsignatura(Asignatura $asignatura, array $data): Asignatura;
    public function deleteAsignatura(Asignatura $asignatura): void;
}