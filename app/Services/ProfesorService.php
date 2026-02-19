<?php

namespace App\Services;

use App\Interfaces\Repositories\ProfesorRepositoryInterface;
use App\Interfaces\Services\ProfesorServiceInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProfesorService implements ProfesorServiceInterface
{
    public function __construct(
        private ProfesorRepositoryInterface $profesorRepository
    ) {}

    public function getAllProfesores(): Collection
    {
        Log::info('Cargando listado de profesores');
        return $this->profesorRepository->getAll();
    }

    public function getAllAsignaturas(): Collection
    {
        return $this->profesorRepository->getAllAsignaturas();
    }

    public function getProfesorById(int $id): User
    {
        return $this->profesorRepository->findById($id);
    }

    public function createProfesor(array $data): User
    {
        Log::info('Intentando crear profesor', $data);

        $profesor = $this->profesorRepository->create([
            'name'        => $data['name'],
            'document'    => 'AUTO-' . Str::random(10),
            'email'       => 'prof_' . Str::random(10) . '@colegio.local',
            'password'    => bcrypt(Str::random(12)),
            'role'        => 'professor',
            'is_approved' => true,
        ]);

        if (!empty($data['asignaturas'])) {
            $this->profesorRepository->attachAsignaturas($profesor, $data['asignaturas']);
        }

        Log::info('Profesor creado correctamente', ['id' => $profesor->id]);

        return $profesor->load('asignaturas');
    }

    public function updateProfesor(int $id, array $data): User
    {
        Log::info('Intentando actualizar profesor', ['id' => $id]);

        $profesor = $this->profesorRepository->findById($id);
        $this->profesorRepository->update($profesor, ['name' => $data['name']]);

        $asignaturas = $data['asignaturas'] ?? [];
        $this->profesorRepository->syncAsignaturas($profesor, $asignaturas);

        Log::info('Profesor actualizado correctamente', [
            'id'                => $profesor->id,
            'asignaturas_count' => count($asignaturas),
        ]);

        return $profesor->fresh('asignaturas');
    }

    public function asignarAsignaturas(int $id, array $data): User
    {
        Log::info('Asignando asignaturas al profesor', ['id' => $id]);

        $profesor = $this->profesorRepository->findById($id);
        $asignaturas = $data['asignaturas'] ?? [];
        $this->profesorRepository->syncAsignaturas($profesor, $asignaturas);

        Log::info('Asignaturas actualizadas correctamente', [
            'profesor_id'       => $profesor->id,
            'total_asignaturas' => count($asignaturas),
        ]);

        return $profesor->fresh('asignaturas');
    }

    public function deleteProfesor(int $id): string
    {
        Log::info('Intentando eliminar profesor', ['id' => $id]);

        $profesor = $this->profesorRepository->findById($id);
        $nombre = $profesor->name;

        $this->profesorRepository->detachAsignaturas($profesor);
        $this->profesorRepository->delete($profesor);

        Log::info('Profesor eliminado correctamente', ['id' => $id, 'nombre' => $nombre]);

        return $nombre;
    }
}