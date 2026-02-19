<?php

namespace App\Interfaces\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface AsignacionAcademicaServiceInterface
{
    public function getDatosIndex(): array;
    public function listarConFiltros(array $filtros): Collection;
    public function getGradosPorNivel(int $nivelId): array;
    public function getMatriz(int $gradoId, int $year): array;
    public function guardarMasiva(array $asignaciones): array;
    public function getResumenYear(int $year): array;
    public function copiarYear(int $yearOrigen, int $yearDestino, bool $sobreescribir): array;
    public function store(array $data): object;
    public function show(int $id): object;
    public function update(int $id, array $data): object;
    public function destroy(int $id): string;
    public function getEstadisticas(int $year): array;
    public function getResumenProfesor(int $profesorId, int $year): array;
    public function getResumenGrado(int $gradoId, int $year): array;
    public function validar(array $data): array;
    public function getAcademicYears(): array;
}