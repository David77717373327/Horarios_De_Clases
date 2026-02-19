<?php

namespace App\Interfaces\Services;

use App\Models\Nivel;
use Illuminate\Database\Eloquent\Collection;

interface NivelServiceInterface
{
    public function getAllNiveles(): Collection;
    public function createNivel(array $data): Nivel;
    public function updateNivel(int $id, array $data): Nivel;
    public function deleteNivel(int $id): void;
}