<?php

namespace App\Repositories;

use App\Interfaces\Repositories\HorarioListRepositoryInterface;
use App\Models\Grado;
use App\Models\Horario;
use App\Models\Nivel;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class HorarioListRepository implements HorarioListRepositoryInterface
{
    public function getNiveles(): Collection
    {
        return Nivel::orderBy('nombre')->get();
    }

    public function findNivel(int $nivelId): ?Nivel
    {
        return Nivel::find($nivelId);
    }

    public function findNivelOrFail(int $nivelId): Nivel
    {
        return Nivel::findOrFail($nivelId);
    }

    public function getGradosByNivel(int $nivelId): Collection
    {
        return Grado::where('nivel_id', $nivelId)
            ->orderBy('nombre')
            ->get();
    }

    public function getHorariosPorNivelYGrado(int $nivelId, int $gradoId, int $year): Collection
    {
        return Horario::where('nivel_id', $nivelId)
            ->where('grado_id', $gradoId)
            ->where('year', $year)
            ->with(['asignatura', 'profesor'])
            ->get();
    }

    public function getHorariosAgrupadosPorGrado(int $nivelId, int $year): SupportCollection
    {
        return Horario::with(['grado', 'asignatura', 'profesor'])
            ->where('nivel_id', $nivelId)
            ->where('year', $year)
            ->orderBy('grado_id')
            ->orderBy('hora_numero')
            ->orderBy('dia_semana')
            ->get()
            ->groupBy('grado_id');
    }

    public function getTotalGrados(int $nivelId): int
    {
        return Grado::where('nivel_id', $nivelId)->count();
    }

    public function getGradosConHorario(int $nivelId, int $year): int
    {
        return Horario::where('nivel_id', $nivelId)
            ->where('year', $year)
            ->distinct('grado_id')
            ->count('grado_id');
    }

    public function getTotalClases(int $nivelId, int $year): int
    {
        return Horario::where('nivel_id', $nivelId)
            ->where('year', $year)
            ->count();
    }

    public function getProfesoresUnicos(int $nivelId, int $year): int
    {
        return Horario::where('nivel_id', $nivelId)
            ->where('year', $year)
            ->distinct('profesor_id')
            ->count('profesor_id');
    }

    public function getAsignaturasUnicas(int $nivelId, int $year): int
    {
        return Horario::where('nivel_id', $nivelId)
            ->where('year', $year)
            ->distinct('asignatura_id')
            ->count('asignatura_id');
    }

    public function findProfesor(int $profesorId): ?object
    {
        return User::where('id', $profesorId)
            ->where('role', 'professor')
            ->first(['id', 'name']);
    }

    public function getAcademicYears(): array
    {
        $currentYear = date('Y');
        $years = [];
        for ($i = -2; $i <= 5; $i++) {
            $years[] = $currentYear + $i;
        }
        return $years;
    }
}