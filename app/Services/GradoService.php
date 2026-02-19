<?php

namespace App\Services;

use App\Interfaces\Repositories\GradoRepositoryInterface;
use App\Interfaces\Services\GradoServiceInterface;
use App\Interfaces\Repositories\NivelRepositoryInterface;
use App\Models\Grado;
use Illuminate\Database\Eloquent\Collection;

class GradoService implements GradoServiceInterface
{
    public function __construct(
        private GradoRepositoryInterface $gradoRepository,
        private NivelRepositoryInterface $nivelRepository  // 🔑 para traer niveles en index
    ) {}

    public function getAllGrados(): Collection
    {
        return $this->gradoRepository->getAll();
    }

    public function getNiveles(): Collection
    {
        return $this->nivelRepository->getAll(); // reutilizas lo que ya existe
    }

    public function createGrado(array $data): Grado
    {
        return $this->gradoRepository->create($data);
    }

    public function updateGrado(Grado $grado, array $data): Grado
    {
        return $this->gradoRepository->update($grado, $data);
    }

    public function deleteGrado(Grado $grado): void
    {
        $this->gradoRepository->delete($grado);
    }
}