<?php

namespace App\Repositories;

use App\Interfaces\Repositories\ProfesorRepositoryInterface;
use App\Models\Asignatura;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log; // ← agregar

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
        Log::info('[ProfesorRepository] findById START', ['id' => $id]);

        $profesor = User::with(['asignaturas', 'grado', 'horarios'])
            ->where('role', 'professor')
            ->findOrFail($id);

        Log::info('[ProfesorRepository] findById END', [
            'id'          => $profesor->id,
            'asignaturas' => $profesor->asignaturas->count(),
            'horarios'    => $profesor->horarios->count(),
        ]);

        return $profesor;
    }

    public function create(array $data): User
    {
        return User::create($data);
    }


    public function update(User $profesor, array $data): User
    {
        Log::info('[ProfesorRepository] update START', ['id' => $profesor->id, 'data' => $data]);

        $profesor->update($data);

        Log::info('[ProfesorRepository] update END', ['id' => $profesor->id]);

        return $profesor;
    }

    public function syncAsignaturas(User $profesor, array $asignaturas): void
    {
        Log::info('[ProfesorRepository] syncAsignaturas START', [
            'profesor_id' => $profesor->id,
            'asignaturas' => $asignaturas,
        ]);

        $profesor->asignaturas()->sync($asignaturas);

        Log::info('[ProfesorRepository] syncAsignaturas END', ['profesor_id' => $profesor->id]);
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
