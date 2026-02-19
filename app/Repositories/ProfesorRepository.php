<?php

namespace App\Repositories;

use App\Interfaces\Repositories\ProfesorRepositoryInterface;
use App\Models\Asignatura;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ProfesorRepository implements ProfesorRepositoryInterface
{
    public function getAll(): Collection
    {
        return User::where('role', 'professor')
            ->with('asignaturas')
            ->orderBy('name')
            ->get();
    }

    public function getAllAsignaturas(): Collection
    {
        return Asignatura::orderBy('nombre')->get();
    }

    public function findById(int $id): User
    {
        return User::with(['asignaturas', 'grado', 'horarios'])
            ->where('role', 'professor')
            ->findOrFail($id);
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $profesor, array $data): User
    {
        $profesor->update($data);
        return $profesor;
    }

    public function syncAsignaturas(User $profesor, array $asignaturas): void
    {
        $profesor->asignaturas()->sync($asignaturas);
    }

    public function attachAsignaturas(User $profesor, array $asignaturas): void
    {
        $profesor->asignaturas()->attach($asignaturas);
    }

    public function detachAsignaturas(User $profesor): void
    {
        $profesor->asignaturas()->detach();
    }

    public function delete(User $profesor): void
    {
        $profesor->delete();
    }
}