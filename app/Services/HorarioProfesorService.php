<?php

namespace App\Services;

use App\Interfaces\Repositories\HorarioProfesorRepositoryInterface;
use App\Interfaces\Services\HorarioProfesorServiceInterface;
use Illuminate\Support\Facades\Log;

class HorarioProfesorService implements HorarioProfesorServiceInterface
{
    public function __construct(
        private HorarioProfesorRepositoryInterface $repository
    ) {}

    public function getDatosIndex(): array
    {
        return [
            'years'     => $this->repository->getAcademicYears(),
            'profesores' => $this->repository->getProfesores(),
        ];
    }

    public function obtenerHorario(int $profesorId, int $year): array
    {
        $profesor = $this->repository->findProfesor($profesorId);
        $horarios = $this->repository->getHorariosProfesor($profesorId, $year);

        if ($horarios->isEmpty()) {
            return [
                'vacio'    => true,
                'profesor' => $profesor->name,
                'year'     => $year,
                'message'  => 'No hay horarios registrados para este profesor en el año ' . $year,
            ];
        }

        $config     = $horarios->first();
        $diasSemana = $this->parsearDiasSemana($config->dias_semana);
        $horaInicio = $this->limpiarHora($config->hora_inicio);

        $horariosOrganizados = [];
        foreach ($diasSemana as $dia) {
            $horariosOrganizados[$dia] = [];
        }

        foreach ($horarios as $horario) {
            if (!isset($horariosOrganizados[$horario->dia_semana])) {
                $horariosOrganizados[$horario->dia_semana] = [];
            }
            if (!isset($horariosOrganizados[$horario->dia_semana][$horario->hora_numero])) {
                $horariosOrganizados[$horario->dia_semana][$horario->hora_numero] = [];
            }
            $horariosOrganizados[$horario->dia_semana][$horario->hora_numero][] = [
                'asignatura'    => $horario->asignatura,
                'grado'         => $horario->grado,
                'nivel'         => $horario->nivel,
                'asignatura_id' => $horario->asignatura_id,
                'grado_id'      => $horario->grado_id,
            ];
        }

        return [
            'vacio'    => false,
            'profesor' => $profesor->name,
            'year'     => $year,
            'config'   => [
                'hora_inicio'        => $horaInicio,
                'hora_fin'           => $config->hora_fin,
                'duracion_clase'     => (int) $config->duracion_clase,
                'horas_por_dia'      => (int) $config->horas_por_dia,
                'dias_semana'        => $diasSemana,
                'recreo_despues_hora' => $config->recreo_despues_hora,
                'recreo_duracion'    => (int) $config->recreo_duracion,
            ],
            'horarios' => $horariosOrganizados,
        ];
    }

    public function getPdfData(int $profesorId, int $year): array
    {
        $profesor      = $this->repository->findProfesor($profesorId);
        $horariosQuery = $this->repository->getHorariosProfesorParaPdf($profesorId, $year);

        Log::info('📊 Horarios obtenidos', ['total' => $horariosQuery->count()]);

        if ($horariosQuery->isEmpty()) {
            return ['vacio' => true];
        }

        $config     = $horariosQuery->first();
        $diasSemana = $this->parsearDiasSemana($config->dias_semana);
        $horaInicio = $this->limpiarHora($config->hora_inicio);

        Log::info('⚙️ Configuración', [
            'hora_inicio'    => $horaInicio,
            'duracion_clase' => $config->duracion_clase,
            'horas_por_dia'  => $config->horas_por_dia,
            'dias_semana'    => $diasSemana,
        ]);

        $horarios = [];
        foreach ($diasSemana as $dia) {
            $horarios[$dia] = [];
        }

        foreach ($horariosQuery as $horario) {
            if (!isset($horarios[$horario->dia_semana][$horario->hora_numero])) {
                $horarios[$horario->dia_semana][$horario->hora_numero] = [];
            }
            $horarios[$horario->dia_semana][$horario->hora_numero][] = [
                'asignatura' => $horario->asignatura,
                'grado'      => $horario->grado,
                'nivel'      => $horario->nivel,
            ];
        }

        // ✅ Logo en base64
        $logoPath   = public_path('images/Logo.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
            Log::info('🖼️ Logo cargado en base64 correctamente');
        } else {
            Log::warning('⚠️ Logo no encontrado', ['ruta' => $logoPath]);
        }

        $nombreArchivo = 'horario_' . str_replace(' ', '_', strtolower($profesor->name)) . '_' . $year . '.pdf';

        return [
            'vacio'          => false,
            'profesorNombre' => $profesor->name,
            'year'           => $year,
            'horarios'       => $horarios,
            'config'         => [
                'hora_inicio'        => $horaInicio,
                'duracion_clase'     => (int) $config->duracion_clase,
                'horas_por_dia'      => (int) $config->horas_por_dia,
                'dias_semana'        => $diasSemana,
                'recreo_despues_hora' => $config->recreo_despues_hora,
                'recreo_duracion'    => (int) $config->recreo_duracion,
            ],
            'logoBase64'     => $logoBase64,
            'nombreArchivo'  => $nombreArchivo,
        ];
    }

    // ─── Métodos privados de apoyo ────────────────────────────────────────────

    private function parsearDiasSemana(mixed $diasSemana): array
    {
        if (is_string($diasSemana)) {
            $diasSemana = json_decode($diasSemana, true);
        }
        if (!is_array($diasSemana)) {
            $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
        }
        return $diasSemana;
    }

    private function limpiarHora(?string $hora): ?string
    {
        if ($hora && strlen($hora) > 5) {
            return date('H:i', strtotime($hora));
        }
        return $hora;
    }
}