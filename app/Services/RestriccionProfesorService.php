<?php

namespace App\Services;

use App\Interfaces\Repositories\RestriccionProfesorRepositoryInterface;
use App\Interfaces\Services\RestriccionProfesorServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RestriccionProfesorService implements RestriccionProfesorServiceInterface
{
    public function __construct(
        private RestriccionProfesorRepositoryInterface $repository
    ) {}

    public function getDatosIndex(): array
    {
        return [
            'profesores' => $this->repository->getProfesores(),
            'years'      => $this->getAcademicYears(),
        ];
    }

    public function listarConFiltros(array $filtros): Collection
    {
        return $this->repository->getConFiltros($filtros);
    }

    public function store(array $data): object
    {
        Log::info('Creando restricción de profesor', $data);

        $data = $this->procesarTipoRestriccion($data);

        $this->validarDuplicado($data);

        DB::beginTransaction();
        try {
            $restriccion = $this->repository->create($data);
            DB::commit();

            Log::info('Restricción creada exitosamente', ['id' => $restriccion->id]);

            return $restriccion->load('profesor');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show(int $id): object
    {
        return $this->repository->findById($id);
    }

    public function update(int $id, array $data): object
    {
        Log::info('Actualizando restricción', ['id' => $id]);

        $restriccion = $this->repository->findById($id);

        $data = $this->procesarTipoRestriccion($data);

        $this->validarDuplicado($data, $id);

        DB::beginTransaction();
        try {
            $this->repository->update($restriccion, $data);
            DB::commit();

            Log::info('Restricción actualizada', ['id' => $restriccion->id]);

            return $restriccion->fresh('profesor');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy(int $id): string
    {
        Log::info('Eliminando restricción', ['id' => $id]);

        $restriccion = $this->repository->findById($id);
        $profesor    = $restriccion->profesor->name;
        $descripcion = $restriccion->descripcion;

        $this->repository->delete($restriccion);

        Log::info('Restricción eliminada', ['id' => $id]);

        return "{$profesor} - {$descripcion}";
    }

    public function toggleActiva(int $id): array
    {
        $restriccion = $this->repository->findById($id);
        $restriccion = $this->repository->toggleActiva($restriccion);
        $estado      = $restriccion->activa ? 'activada' : 'desactivada';

        return ['estado' => $estado, 'activa' => $restriccion->activa];
    }

    public function getRestriccionesProfesor(int $profesorId, int $year): Collection
    {
        return $this->repository->getActivasPorProfesorYYear($profesorId, $year);
    }

    public function verificarRestriccion(array $data): array
    {
        $tieneRestriccion = $this->repository->verificarRestriccion(
            $data['profesor_id'],
            $data['dia'],
            $data['hora_numero'] ?? null,
            $data['year'],
            $data['hora'] ?? null
        );

        return [
            'tiene_restriccion' => $tieneRestriccion,
            'message'           => $tieneRestriccion
                ? 'El profesor tiene una restricción configurada para esta hora'
                : 'El profesor está disponible',
        ];
    }

    public function getHorasBloqueadas(int $profesorId, string $dia, int $year): mixed
    {
        return $this->repository->getHorasBloqueadas($profesorId, $dia, $year);
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

    // ─── Métodos privados de apoyo ────────────────────────────────────────────

    private function procesarTipoRestriccion(array $data): array
    {
        $tipo = $data['tipo_restriccion'];

        if ($tipo === 'hora_especifica') {
            if (empty($data['hora_numero'])) {
                throw new \Exception('Para restricción de hora específica debe indicar el número de hora (1-12)', 422);
            }
            $data['hora_inicio'] = null;
            $data['hora_fin']    = null;

        } elseif ($tipo === 'rango_horario') {
            if (empty($data['hora_inicio']) || empty($data['hora_fin'])) {
                throw new \Exception('Para restricción de rango horario debe especificar hora de inicio y fin', 422);
            }
            if ($data['hora_inicio'] >= $data['hora_fin']) {
                throw new \Exception('La hora de fin debe ser posterior a la hora de inicio', 422);
            }
            $data['hora_numero'] = null;

        } elseif ($tipo === 'dia_completo') {
            if (empty($data['dia_semana'])) {
                throw new \Exception('Para restricción de día completo debe especificar el día de la semana', 422);
            }
            $data['hora_numero'] = null;
            $data['hora_inicio'] = null;
            $data['hora_fin']    = null;
        }

        unset($data['tipo_restriccion']);

        return $data;
    }

    private function validarDuplicado(array $data, ?int $excludeId = null): void
    {
        $existe = $this->repository->existeRestriccion($data, $excludeId);

        if ($existe) {
            throw new \Exception('Ya existe una restricción activa idéntica para este profesor', 422);
        }
    }
}