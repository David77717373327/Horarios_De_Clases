<?php

namespace App\Interfaces\Services;

use Illuminate\Http\Request;

interface HorarioListServiceInterface
{
    public function getDatosIndex(): array;
    public function getHorariosByNivel(array $data): array;
    public function getEstadisticas(array $data): array;
    public function getPdfData(int $nivelId, int $year): array;
    public function getProfesor(int $profesorId): ?object;
}