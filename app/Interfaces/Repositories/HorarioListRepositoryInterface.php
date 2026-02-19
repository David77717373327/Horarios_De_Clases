<?php

namespace App\Interfaces\Repositories;

use App\Models\Nivel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

interface HorarioListRepositoryInterface
{
    public function getNiveles(): Collection;
    public function findNivel(int $nivelId): ?Nivel;
    public function findNivelOrFail(int $nivelId): Nivel;
    public function getGradosByNivel(int $nivelId): Collection;
    public function getHorariosPorNivelYGrado(int $nivelId, int $gradoId, int $year): Collection;
    public function getHorariosAgrupadosPorGrado(int $nivelId, int $year): SupportCollection;
    public function getTotalGrados(int $nivelId): int;
    public function getGradosConHorario(int $nivelId, int $year): int;
    public function getTotalClases(int $nivelId, int $year): int;
    public function getProfesoresUnicos(int $nivelId, int $year): int;
    public function getAsignaturasUnicas(int $nivelId, int $year): int;
    public function findProfesor(int $profesorId): ?object;
    public function getAcademicYears(): array;
}