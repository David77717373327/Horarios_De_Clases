<?php

namespace App\Interfaces\Repositories;

use App\Models\RestriccionProfesor;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface RestriccionProfesorRepositoryInterface
{
    public function getProfesores(): Collection;
    public function getConFiltros(array $filtros): Collection;
    public function findById(int $id): RestriccionProfesor;
    public function existeRestriccion(array $datos, ?int $excludeId = null): bool;
    public function create(array $data): RestriccionProfesor;
    public function update(RestriccionProfesor $restriccion, array $data): RestriccionProfesor;
    public function delete(RestriccionProfesor $restriccion): void;
    public function toggleActiva(RestriccionProfesor $restriccion): RestriccionProfesor;
    public function getActivasPorProfesorYYear(int $profesorId, int $year): Collection;
    public function getHorasBloqueadas(int $profesorId, string $dia, int $year): mixed;
    public function verificarRestriccion(int $profesorId, string $dia, ?int $horaNumero, int $year, ?string $hora): bool;
}