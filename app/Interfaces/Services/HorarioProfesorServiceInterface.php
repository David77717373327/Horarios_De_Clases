<?php

namespace App\Interfaces\Services;

interface HorarioProfesorServiceInterface
{
    public function getDatosIndex(): array;
    public function obtenerHorario(int $profesorId, int $year): array;
    public function getPdfData(int $profesorId, int $year): array;
}