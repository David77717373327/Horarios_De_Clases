<?php

namespace App\Services;

use App\Interfaces\Repositories\AsignaturaRepositoryInterface;
use App\Interfaces\Services\AsignaturaServiceInterface;
use App\Models\Asignatura;
use Illuminate\Database\Eloquent\Collection;

class AsignaturaService implements AsignaturaServiceInterface
{
    public function __construct(
        private AsignaturaRepositoryInterface $asignaturaRepository
    ) {}

    public function getAllAsignaturas(): Collection
    {
        return $this->asignaturaRepository->getAll();
    }

    public function createAsignatura(array $data): Asignatura
    {
        return $this->asignaturaRepository->create($data);
    }

    public function updateAsignatura(Asignatura $asignatura, array $data): Asignatura
    {
        return $this->asignaturaRepository->update($asignatura, $data);
    }

    public function deleteAsignatura(Asignatura $asignatura): void
    {
        $this->asignaturaRepository->delete($asignatura);
    }
}