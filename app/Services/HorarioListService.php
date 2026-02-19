<?php

namespace App\Services;

use App\Interfaces\Repositories\HorarioListRepositoryInterface;
use App\Interfaces\Services\HorarioListServiceInterface;
use Illuminate\Support\Facades\Log;

class HorarioListService implements HorarioListServiceInterface
{
    public function __construct(
        private HorarioListRepositoryInterface $repository
    ) {}

    public function getDatosIndex(): array
    {
        return [
            'niveles' => $this->repository->getNiveles(),
            'years'   => $this->repository->getAcademicYears(),
        ];
    }

    public function getHorariosByNivel(array $data): array
    {
        $nivel  = $this->repository->findNivelOrFail($data['nivel_id']);
        $grados = $this->repository->getGradosByNivel($data['nivel_id']);

        $horariosData = [];

        foreach ($grados as $grado) {
            $horarios = $this->repository->getHorariosPorNivelYGrado(
                $data['nivel_id'],
                $grado->id,
                $data['year']
            );

            if ($horarios->isEmpty()) continue;

            $firstHorario = $horarios->first();

            $config = [
                'hora_inicio'        => $firstHorario->hora_inicio,
                'hora_fin'           => $firstHorario->hora_fin,
                'duracion_clase'     => $firstHorario->duracion_clase,
                'horas_por_dia'      => $firstHorario->horas_por_dia,
                'dias_semana'        => json_decode($firstHorario->dias_semana),
                'recreo_despues_hora' => $firstHorario->recreo_despues_hora,
                'recreo_duracion'    => $firstHorario->recreo_duracion,
            ];

            $horarioOrganizado = [];
            foreach ($horarios as $horario) {
                $horarioOrganizado[$horario->dia_semana][$horario->hora_numero] = [
                    'asignatura'    => $horario->asignatura->nombre,
                    'profesor'      => $horario->profesor->name,
                    'asignatura_id' => $horario->asignatura_id,
                    'profesor_id'   => $horario->profesor_id,
                ];
            }

            $horariosData[] = [
                'grado'    => $grado->nombre,
                'grado_id' => $grado->id,
                'config'   => $config,
                'horarios' => $horarioOrganizado,
            ];
        }

        return [
            'nivel'    => $nivel->nombre,
            'year'     => $data['year'],
            'horarios' => $horariosData,
        ];
    }

    public function getEstadisticas(array $data): array
    {
        $nivel           = $this->repository->findNivelOrFail($data['nivel_id']);
        $totalGrados     = $this->repository->getTotalGrados($data['nivel_id']);
        $gradosConHorario = $this->repository->getGradosConHorario($data['nivel_id'], $data['year']);

        return [
            'nivel'               => $nivel->nombre,
            'total_grados'        => $totalGrados,
            'grados_con_horario'  => $gradosConHorario,
            'total_clases'        => $this->repository->getTotalClases($data['nivel_id'], $data['year']),
            'profesores_unicos'   => $this->repository->getProfesoresUnicos($data['nivel_id'], $data['year']),
            'asignaturas_unicas'  => $this->repository->getAsignaturasUnicas($data['nivel_id'], $data['year']),
            'porcentaje_completado' => $totalGrados > 0
                ? round(($gradosConHorario / $totalGrados) * 100, 1)
                : 0,
        ];
    }

    public function getPdfData(int $nivelId, int $year): array
    {
        $nivel       = $this->repository->findNivel($nivelId);
        $nivelNombre = $nivel ? $nivel->nombre : 'Sin Nivel';
        $horarios    = $this->repository->getHorariosAgrupadosPorGrado($nivelId, $year);

        Log::info('📊 Horarios obtenidos', [
            'total_grados'    => $horarios->count(),
            'total_registros' => $horarios->flatten()->count(),
        ]);

        $configuracion = null;

        foreach ($horarios as $horariosGrado) {
            if ($horariosGrado->isNotEmpty()) {
                $primer     = $horariosGrado->first();
                $horaInicio = $primer->hora_inicio;

                // ✅ Extraer solo HH:MM del timestamp si es necesario
                if (strlen($horaInicio) > 5) {
                    $horaInicio = date('H:i', strtotime($horaInicio));
                }

                $configuracion = [
                    'hora_inicio'        => $horaInicio,
                    'duracion_clase'     => (int) $primer->duracion_clase,
                    'recreo_despues_hora' => $primer->recreo_despues_hora,
                    'recreo_duracion'    => (int) $primer->recreo_duracion,
                ];

                Log::info('⚙️ Configuración extraída y limpiada', $configuracion);
                break;
            }
        }

        if (!$configuracion) {
            Log::warning('⚠️ No se encontró configuración, usando valores por defecto');
            $configuracion = [
                'hora_inicio'        => '07:00',
                'duracion_clase'     => 45,
                'recreo_despues_hora' => 2,
                'recreo_duracion'    => 15,
            ];
        }

        return [
            'horarios'      => $horarios,
            'year'          => $year,
            'nivelNombre'   => $nivelNombre,
            'configuracion' => $configuracion,
        ];
    }

    public function getProfesor(int $profesorId): ?object
    {
        return $this->repository->findProfesor($profesorId);
    }
}