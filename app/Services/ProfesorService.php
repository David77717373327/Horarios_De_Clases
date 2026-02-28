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
    Log::info('[ProfesorService] updateProfesor START', ['id' => $id, 'data' => $data]);

    Log::info('[ProfesorService] Buscando profesor...');
    $profesor = $this->profesorRepository->findById($id);
    Log::info('[ProfesorService] Profesor encontrado', ['id' => $profesor->id]);

    Log::info('[ProfesorService] Actualizando nombre...');
    $this->profesorRepository->update($profesor, ['name' => $data['name']]);
    Log::info('[ProfesorService] Nombre actualizado');

    $asignaturas = $data['asignaturas'] ?? [];
    Log::info('[ProfesorService] Sincronizando asignaturas...', ['asignaturas' => $asignaturas]);
    $this->profesorRepository->syncAsignaturas($profesor, $asignaturas);
    Log::info('[ProfesorService] Asignaturas sincronizadas');

    Log::info('[ProfesorService] Ejecutando fresh()...');
    $result = $profesor->fresh('asignaturas');
    Log::info('[ProfesorService] fresh() completado');

    return $result;
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