<?php

namespace App\Repositories;

use App\Interfaces\Repositories\GradoRepositoryInterface;
use App\Models\Grado;
use Illuminate\Database\Eloquent\Collection;

class GradoRepository implements GradoRepositoryInterface
{
    public function getAll(): Collection
    {
        return Grado::with('nivel')
            ->withCount('horarios')
            ->orderBy('id')
            ->get();
    }

    public function create(array $data): Grado
    {
        return Grado::create($data);
    }

    public function update(Grado $grado, array $data): Grado
    {
        $grado->update($data);
        return $grado;
    }

    public function delete(Grado $grado): void
    {
        $grado->delete();
    }
}