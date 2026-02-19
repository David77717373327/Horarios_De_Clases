<?php

namespace App\Services\Horario;

use App\Interfaces\Repositories\HorarioRepositoryInterface;
use App\Interfaces\Services\Horario\HorarioServiceInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class HorarioService implements HorarioServiceInterface
{
    public function __construct(
        private HorarioRepositoryInterface $horarioRepository
    ) {}

    public function getDatosIndex(): array
    {
        return [
            'niveles' => $this->horarioRepository->getNiveles(),
            'years'   => $this->horarioRepository->getAcademicYears(),
        ];
    }

    public function getDatosCreate(): array
    {
        return [
            'niveles' => $this->horarioRepository->getNiveles(),
            'years'   => $this->horarioRepository->getAcademicYears(),
        ];
    }

    public function getGradosByNivel(int $nivelId): Collection
    {
        return $this->horarioRepository->getGradosByNivel($nivelId);
    }

    public function getHorario(array $data): array
    {
        $horarios = $this->horarioRepository->getHorarioConRelaciones(
            $data['nivel_id'],
            $data['grado_id'],
            $data['year']
        );

        $config = null;

        if ($horarios->isNotEmpty()) {
            $firstHorario = $horarios->first();

            $horaInicio = $firstHorario->hora_inicio;
            $horaFin    = $firstHorario->hora_fin;

            if ($horaInicio instanceof \DateTime || $horaInicio instanceof Carbon) {
                $horaInicio = $horaInicio->format('H:i');
            } else {
                $horaInicio = Carbon::parse($horaInicio)->format('H:i');
            }

            if ($horaFin instanceof \DateTime || $horaFin instanceof Carbon) {
                $horaFin = $horaFin->format('H:i');
            } else {
                $horaFin = Carbon::parse($horaFin)->format('H:i');
            }

            $config = [
                'hora_inicio'        => $horaInicio,
                'hora_fin'           => $horaFin,
                'duracion_clase'     => $firstHorario->duracion_clase,
                'horas_por_dia'      => $firstHorario->horas_por_dia,
                'dias_semana'        => json_decode($firstHorario->dias_semana),
                'recreo_despues_hora' => $firstHorario->recreo_despues_hora,
                'recreo_duracion'    => $firstHorario->recreo_duracion,
            ];
        }

        return ['horarios' => $horarios, 'config' => $config];
    }

    public function destroy(array $data): array
    {
        $deleted = $this->horarioRepository->eliminarHorario(
            $data['nivel_id'],
            $data['grado_id'],
            $data['year']
        );

        Log::info('Horario eliminado', [
            'nivel_id'      => $data['nivel_id'],
            'grado_id'      => $data['grado_id'],
            'year'          => $data['year'],
            'deleted_count' => $deleted,
        ]);

        return ['deleted_count' => $deleted];
    }
}