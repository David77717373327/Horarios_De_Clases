<?php

namespace App\Interfaces\Repositories;

use App\Models\Asignatura;
use Illuminate\Database\Eloquent\Collection;

interface AsignaturaRepositoryInterface
{
    public function getAll(): Collection;
    public function create(array $data): Asignatura;
    public function update(Asignatura $asignatura, array $data): Asignatura;
    public function delete(Asignatura $asignatura): void;
}