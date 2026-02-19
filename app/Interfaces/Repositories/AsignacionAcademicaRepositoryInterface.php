<?php

namespace App\Interfaces\Repositories;

use App\Models\AsignacionAcademica;
use App\Models\Grado;
use App\Models\Nivel;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface AsignacionAcademicaRepositoryInterface
{
    public function getProfesores(): Collection;
    public function getNiveles(): Collection;
    public function getAsignaturas(): Collection;
    public function getGradosByNivel(int $nivelId): Collection;
    public function findNivel(int $nivelId): ?Nivel;
    public function findGradoConNivel(int $gradoId): Grado;
    public function getProfesoresConAsignaturas(): Collection;
    public function getAsignacionesConFiltros(array $filtros): Collection;
    public function getAsignacionesPorGradoYYear(int $gradoId, int $year): Collection;
    public function getAsignacionesPorYear(int $year): Collection;
    public function getAsignacionesPorYearConHorarios(int $year): Collection;
    public function getAsignacionesPorProfesorYYear(int $profesorId, int $year): Collection;
    public function getAsignacionesPorGradoYYearConRelaciones(int $gradoId, int $year): Collection;
    public function findById(int $id): AsignacionAcademica;
    public function findByUniqueKey(int $profesorId, int $asignaturaId, int $gradoId, int $year): ?AsignacionAcademica;
    public function existeAsignacion(int $profesorId, int $asignaturaId, int $gradoId, int $year, ?int $excludeId = null): bool;
    public function create(array $data): AsignacionAcademica;
    public function update(AsignacionAcademica $asignacion, array $data): AsignacionAcademica;
    public function delete(AsignacionAcademica $asignacion): void;
    public function eliminarPorYear(int $year): int;
    public function findProfesorConAsignaturas(int $profesorId): User;
    public function findProfesor(int $profesorId): User;
}