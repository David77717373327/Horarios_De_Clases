<?php

namespace App\Interfaces\Services;

use Illuminate\Database\Eloquent\Collection;

interface RestriccionProfesorServiceInterface
{
    public function getDatosIndex(): array;
    public function listarConFiltros(array $filtros): Collection;
    public function store(array $data): object;
    public function show(int $id): object;
    public function update(int $id, array $data): object;
    public function destroy(int $id): string;
    public function toggleActiva(int $id): array;
    public function getRestriccionesProfesor(int $profesorId, int $year): Collection;
    public function verificarRestriccion(array $data): array;
    public function getHorasBloqueadas(int $profesorId, string $dia, int $year): mixed;
    public function getAcademicYears(): array;
}