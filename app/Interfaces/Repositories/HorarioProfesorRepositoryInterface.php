<?php

namespace App\Interfaces\Repositories;

use Illuminate\Support\Collection;

interface HorarioProfesorRepositoryInterface
{
    public function getProfesores(): Collection;
    public function findProfesor(int $profesorId): object;
    public function getHorariosProfesor(int $profesorId, int $year): Collection;
    public function getHorariosProfesorParaPdf(int $profesorId, int $year): Collection;
    public function getAcademicYears(): array;
}