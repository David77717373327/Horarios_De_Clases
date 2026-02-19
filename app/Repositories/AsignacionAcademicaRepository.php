<?php

namespace App\Repositories;

use App\Interfaces\Repositories\AsignacionAcademicaRepositoryInterface;
use App\Models\AsignacionAcademica;
use App\Models\Asignatura;
use App\Models\Grado;
use App\Models\Nivel;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AsignacionAcademicaRepository implements AsignacionAcademicaRepositoryInterface
{
    public function getProfesores(): Collection
    {
        return User::where('role', 'professor')
            ->where('is_approved', true)
            ->with('asignaturas:id,nombre')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getNiveles(): Collection
    {
        return Nivel::orderBy('orden')->get(['id', 'nombre']);
    }

    public function getAsignaturas(): Collection
    {
        return Asignatura::orderBy('nombre')->get(['id', 'nombre']);
    }

    public function getGradosByNivel(int $nivelId): Collection
    {
        return Grado::where('nivel_id', $nivelId)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    public function findNivel(int $nivelId): ?Nivel
    {
        return Nivel::find($nivelId);
    }

    public function findGradoConNivel(int $gradoId): Grado
    {
        return Grado::with('nivel:id,nombre')->findOrFail($gradoId);
    }

    public function getProfesoresConAsignaturas(): Collection
    {
        return User::where('role', 'professor')
            ->where('is_approved', true)
            ->with('asignaturas:id,nombre')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getAsignacionesConFiltros(array $filtros): Collection
    {
        $query = AsignacionAcademica::with([
            'profesor:id,name',
            'asignatura:id,nombre',
            'grado:id,nombre,nivel_id',
            'grado.nivel:id,nombre',
        ])->withCount('horarios as horas_asignadas_count');

        if (!empty($filtros['year'])) {
            $query->where('year', $filtros['year']);
        }
        if (!empty($filtros['profesor_id'])) {
            $query->where('profesor_id', $filtros['profesor_id']);
        }
        if (!empty($filtros['nivel_id'])) {
            $query->whereHas('grado', fn($q) => $q->where('nivel_id', $filtros['nivel_id']));
        }
        if (!empty($filtros['grado_id'])) {
            $query->where('grado_id', $filtros['grado_id']);
        }

        return $query->orderBy('year', 'desc')->orderBy('profesor_id')->get();
    }

    public function getAsignacionesPorGradoYYear(int $gradoId, int $year): Collection
    {
        return AsignacionAcademica::where('grado_id', $gradoId)
            ->where('year', $year)
            ->get(['id', 'profesor_id', 'asignatura_id', 'horas_semanales']);
    }

    public function getAsignacionesPorYear(int $year): Collection
    {
        return AsignacionAcademica::where('year', $year)->get();
    }

    public function getAsignacionesPorYearConHorarios(int $year): Collection
    {
        return AsignacionAcademica::where('year', $year)
            ->withCount('horarios')
            ->get();
    }

    public function getAsignacionesPorProfesorYYear(int $profesorId, int $year): Collection
    {
        return AsignacionAcademica::where('profesor_id', $profesorId)
            ->where('year', $year)
            ->with(['asignatura', 'grado'])
            ->withCount('horarios as horas_asignadas_count')
            ->get();
    }

    public function getAsignacionesPorGradoYYearConRelaciones(int $gradoId, int $year): Collection
    {
        return AsignacionAcademica::where('grado_id', $gradoId)
            ->where('year', $year)
            ->with(['profesor', 'asignatura'])
            ->withCount('horarios as horas_asignadas_count')
            ->get();
    }

    public function findById(int $id): AsignacionAcademica
    {
        return AsignacionAcademica::with([
            'profesor:id,name',
            'asignatura:id,nombre',
            'grado:id,nombre,nivel_id',
            'grado.nivel:id,nombre',
        ])->withCount('horarios as horas_asignadas_count')->findOrFail($id);
    }

    public function findByUniqueKey(int $profesorId, int $asignaturaId, int $gradoId, int $year): ?AsignacionAcademica
    {
        return AsignacionAcademica::where('profesor_id', $profesorId)
            ->where('asignatura_id', $asignaturaId)
            ->where('grado_id', $gradoId)
            ->where('year', $year)
            ->first();
    }

    public function existeAsignacion(int $profesorId, int $asignaturaId, int $gradoId, int $year, ?int $excludeId = null): bool
    {
        return AsignacionAcademica::where('profesor_id', $profesorId)
            ->where('asignatura_id', $asignaturaId)
            ->where('grado_id', $gradoId)
            ->where('year', $year)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }

    public function create(array $data): AsignacionAcademica
    {
        return AsignacionAcademica::create($data);
    }

    public function update(AsignacionAcademica $asignacion, array $data): AsignacionAcademica
    {
        $asignacion->update($data);
        return $asignacion;
    }

    public function delete(AsignacionAcademica $asignacion): void
    {
        $asignacion->delete();
    }

    public function eliminarPorYear(int $year): int
    {
        return AsignacionAcademica::where('year', $year)->delete();
    }

    public function findProfesorConAsignaturas(int $profesorId): User
    {
        return User::with('asignaturas')->findOrFail($profesorId);
    }

    public function findProfesor(int $profesorId): User
    {
        return User::findOrFail($profesorId);
    }
}