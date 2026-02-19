<?php

namespace App\Repositories;

use App\Interfaces\Repositories\RestriccionProfesorRepositoryInterface;
use App\Models\RestriccionProfesor;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class RestriccionProfesorRepository implements RestriccionProfesorRepositoryInterface
{
    public function getProfesores(): Collection
    {
        return User::where('role', 'professor')
            ->where('is_approved', true)
            ->orderBy('name')
            ->get();
    }

    public function getConFiltros(array $filtros): Collection
    {
        $query = RestriccionProfesor::with('profesor');

        if (!empty($filtros['year'])) {
            $query->where('year', $filtros['year']);
        }

        if (!empty($filtros['profesor_id'])) {
            $query->where('profesor_id', $filtros['profesor_id']);
        }

        if (isset($filtros['activa'])) {
            $query->where('activa', $filtros['activa'] === 'true');
        }

        return $query->orderBy('profesor_id')
            ->orderBy('dia_semana')
            ->orderBy('hora_numero')
            ->get();
    }

    public function findById(int $id): RestriccionProfesor
    {
        return RestriccionProfesor::with('profesor')->findOrFail($id);
    }

    public function existeRestriccion(array $datos, ?int $excludeId = null): bool
    {
        return RestriccionProfesor::when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->where('profesor_id', $datos['profesor_id'])
            ->where('year', $datos['year'])
            ->where('dia_semana', $datos['dia_semana'])
            ->where('hora_numero', $datos['hora_numero'])
            ->where('hora_inicio', $datos['hora_inicio'])
            ->where('hora_fin', $datos['hora_fin'])
            ->where('activa', true)
            ->exists();
    }

    public function create(array $data): RestriccionProfesor
    {
        return RestriccionProfesor::create($data);
    }

    public function update(RestriccionProfesor $restriccion, array $data): RestriccionProfesor
    {
        $restriccion->update($data);
        return $restriccion;
    }

    public function delete(RestriccionProfesor $restriccion): void
    {
        $restriccion->delete();
    }

    public function toggleActiva(RestriccionProfesor $restriccion): RestriccionProfesor
    {
        $restriccion->activa = !$restriccion->activa;
        $restriccion->save();
        return $restriccion;
    }

    public function getActivasPorProfesorYYear(int $profesorId, int $year): Collection
    {
        return RestriccionProfesor::where('profesor_id', $profesorId)
            ->where('year', $year)
            ->where('activa', true)
            ->get();
    }

    public function getHorasBloqueadas(int $profesorId, string $dia, int $year): mixed
    {
        return RestriccionProfesor::horasBloqueadasProfesor($profesorId, $dia, $year);
    }

    public function verificarRestriccion(int $profesorId, string $dia, ?int $horaNumero, int $year, ?string $hora): bool
    {
        return RestriccionProfesor::profesorTieneRestriccion($profesorId, $dia, $horaNumero, $year, $hora);
    }
}