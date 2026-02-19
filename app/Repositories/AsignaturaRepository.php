<?php

namespace App\Repositories;

use App\Interfaces\Repositories\AsignaturaRepositoryInterface;
use App\Models\Asignatura;
use Illuminate\Database\Eloquent\Collection;

class AsignaturaRepository implements AsignaturaRepositoryInterface
{
    public function getAll(): Collection
    {
        return Asignatura::orderBy('nombre')->get();
    }

    public function create(array $data): Asignatura
    {
        return Asignatura::create($data);
    }

    public function update(Asignatura $asignatura, array $data): Asignatura
    {
        $asignatura->update($data);
        return $asignatura;
    }

    public function delete(Asignatura $asignatura): void
    {
        $asignatura->delete();
    }
}