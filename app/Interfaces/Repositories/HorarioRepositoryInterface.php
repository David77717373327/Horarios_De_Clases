<?php

namespace App\Interfaces\Repositories;

use App\Models\Grado;
use App\Models\Horario;
use App\Models\Nivel;
use Illuminate\Database\Eloquent\Collection;

interface HorarioRepositoryInterface
{
    public function getNiveles(): Collection;
    public function getGradosByNivel(int $nivelId): Collection;
    public function getHorarioConRelaciones(int $nivelId, int $gradoId, int $year): Collection;
    public function existenHorariosGrado(int $gradoId, int $year): bool;
    public function getGradosDelNivel(int $nivelId): Collection;
    public function findNivel(int $nivelId): Nivel;
    public function getAsignacionesCount(int $gradoId, int $year): int;
    public function getAsignacionesSum(int $gradoId, int $year): int;
    public function getHorariosGrado(int $gradoId, int $year): Collection;
    public function eliminarHorario(int $nivelId, int $gradoId, int $year): int;
    public function getAcademicYears(): array;
}