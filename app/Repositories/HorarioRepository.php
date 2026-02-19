<?php

namespace App\Repositories;

use App\Interfaces\Repositories\HorarioRepositoryInterface;
use App\Models\AsignacionAcademica;
use App\Models\Grado;
use App\Models\Horario;
use App\Models\Nivel;
use Illuminate\Database\Eloquent\Collection;

class HorarioRepository implements HorarioRepositoryInterface
{
    public function getNiveles(): Collection
    {
        return Nivel::orderBy('nombre')->get();
    }

    public function getGradosByNivel(int $nivelId): Collection
    {
        return Grado::where('nivel_id', $nivelId)
            ->orderBy('nombre')
            ->get();
    }

    public function getHorarioConRelaciones(int $nivelId, int $gradoId, int $year): Collection
    {
        return Horario::where('nivel_id', $nivelId)
            ->where('grado_id', $gradoId)
            ->where('year', $year)
            ->with(['asignatura', 'profesor'])
            ->get();
    }

    public function existenHorariosGrado(int $gradoId, int $year): bool
    {
        return Horario::where('grado_id', $gradoId)
            ->where('year', $year)
            ->exists();
    }

    public function getGradosDelNivel(int $nivelId): Collection
    {
        return Grado::where('nivel_id', $nivelId)
            ->orderBy('nombre')
            ->get();
    }

    public function findNivel(int $nivelId): Nivel
    {
        return Nivel::findOrFail($nivelId);
    }

    public function getAsignacionesCount(int $gradoId, int $year): int
    {
        return AsignacionAcademica::where('grado_id', $gradoId)
            ->where('year', $year)
            ->count();
    }

    public function getAsignacionesSum(int $gradoId, int $year): int
    {
        return AsignacionAcademica::where('grado_id', $gradoId)
            ->where('year', $year)
            ->sum('horas_semanales');
    }

    public function getHorariosGrado(int $gradoId, int $year): Collection
    {
        return Horario::where('grado_id', $gradoId)
            ->where('year', $year)
            ->with(['asignatura', 'profesor'])
            ->get();
    }

    public function eliminarHorario(int $nivelId, int $gradoId, int $year): int
    {
        return Horario::where('nivel_id', $nivelId)
            ->where('grado_id', $gradoId)
            ->where('year', $year)
            ->delete();
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