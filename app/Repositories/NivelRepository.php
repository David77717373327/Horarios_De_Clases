<?php

namespace App\Repositories;

use App\Interfaces\Repositories\NivelRepositoryInterface;
use App\Models\Nivel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class NivelRepository implements NivelRepositoryInterface
{
    public function getAll(): Collection
    {
        return Nivel::withCount(['grados', 'descansos'])
            ->orderBy('id')
            ->get();
    }

    public function findById(int $id): Nivel
    {
        return Nivel::findOrFail($id);
    }

    public function create(array $data): Nivel
    {
        return Nivel::create($data);
    }

    public function update(Nivel $nivel, array $data): Nivel
    {
        $nivel->update($data);
        return $nivel;
    }

    public function delete(Nivel $nivel): void
    {
        $nivel->delete();
    }

    public function getGradosIds(int $nivelId): array
    {
        return DB::table('grados')
            ->where('nivel_id', $nivelId)
            ->pluck('id')
            ->toArray();
    }
}