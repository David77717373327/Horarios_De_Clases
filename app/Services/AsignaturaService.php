<?php

namespace App\Services;

use App\Interfaces\Repositories\AsignaturaRepositoryInterface;
use App\Interfaces\Services\AsignaturaServiceInterface;
use App\Models\Asignatura;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AsignaturaService implements AsignaturaServiceInterface
{
    public function __construct(
        private AsignaturaRepositoryInterface $asignaturaRepository
    ) {}

    public function getAllAsignaturas(): Collection
    {
        return $this->asignaturaRepository->getAll();
    }

    /**
     * Crea múltiples asignaturas en una transacción.
     * Si una falla, ninguna se guarda.
     */
    public function createAsignaturas(array $nombres): int
    {
        return DB::transaction(function () use ($nombres) {
            $creados = 0;
            foreach ($nombres as $nombre) {
                $this->asignaturaRepository->create(['nombre' => $nombre]);
                $creados++;
            }
            return $creados;
        });
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