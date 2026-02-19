<?php

namespace App\Repositories;

use App\Interfaces\Repositories\HorarioProfesorRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class HorarioProfesorRepository implements HorarioProfesorRepositoryInterface
{
    public function getProfesores(): Collection
    {
        return User::where('role', 'professor')
            ->where('is_approved', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function findProfesor(int $profesorId): object
    {
        return User::findOrFail($profesorId);
    }

    public function getHorariosProfesor(int $profesorId, int $year): Collection
    {
        return DB::table('horarios')
            ->join('asignaturas', 'horarios.asignatura_id', '=', 'asignaturas.id')
            ->join('grados', 'horarios.grado_id', '=', 'grados.id')
            ->join('niveles', 'grados.nivel_id', '=', 'niveles.id')
            ->where('horarios.profesor_id', $profesorId)
            ->where('horarios.year', $year)
            ->select(
                'horarios.dia_semana',
                'horarios.hora_numero',
                'horarios.hora_inicio',
                'horarios.hora_fin',
                'horarios.duracion_clase',
                'horarios.horas_por_dia',
                'horarios.dias_semana',
                'horarios.recreo_despues_hora',
                'horarios.recreo_duracion',
                'asignaturas.nombre as asignatura',
                'asignaturas.id as asignatura_id',
                'grados.nombre as grado',
                'grados.id as grado_id',
                'niveles.nombre as nivel'
            )
            ->orderBy('horarios.dia_semana')
            ->orderBy('horarios.hora_numero')
            ->get();
    }

    public function getHorariosProfesorParaPdf(int $profesorId, int $year): Collection
    {
        return DB::table('horarios')
            ->join('asignaturas', 'horarios.asignatura_id', '=', 'asignaturas.id')
            ->join('grados', 'horarios.grado_id', '=', 'grados.id')
            ->join('niveles', 'grados.nivel_id', '=', 'niveles.id')
            ->where('horarios.profesor_id', $profesorId)
            ->where('horarios.year', $year)
            ->select(
                'horarios.dia_semana',
                'horarios.hora_numero',
                'horarios.hora_inicio',
                'horarios.duracion_clase',
                'horarios.horas_por_dia',
                'horarios.dias_semana',
                'horarios.recreo_despues_hora',
                'horarios.recreo_duracion',
                'asignaturas.nombre as asignatura',
                'grados.nombre as grado',
                'niveles.nombre as nivel'
            )
            ->orderBy('horarios.dia_semana')
            ->orderBy('horarios.hora_numero')
            ->get();
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