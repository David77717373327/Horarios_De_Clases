<?php

namespace App\Interfaces\Repositories;

use App\Models\Nivel;
use Illuminate\Database\Eloquent\Collection;

interface NivelRepositoryInterface
{
    public function getAll(): Collection;
    public function findById(int $id): Nivel;
    public function create(array $data): Nivel;
    public function update(Nivel $nivel, array $data): Nivel;
    public function delete(Nivel $nivel): void;
    public function getGradosIds(int $nivelId): array;
}