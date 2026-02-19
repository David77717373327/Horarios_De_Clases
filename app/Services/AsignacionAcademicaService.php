<?php

namespace App\Services;

use App\Interfaces\Repositories\AsignacionAcademicaRepositoryInterface;
use App\Interfaces\Services\AsignacionAcademicaServiceInterface;
use App\Models\AsignacionAcademica;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AsignacionAcademicaService implements AsignacionAcademicaServiceInterface
{
    public function __construct(
        private AsignacionAcademicaRepositoryInterface $repository
    ) {}

    public function getDatosIndex(): array
    {
        return [
            'years'     => $this->getAcademicYears(),
            'profesores' => $this->repository->getProfesores(),
            'niveles'   => $this->repository->getNiveles(),
        ];
    }

    public function listarConFiltros(array $filtros): Collection
    {
        $asignaciones = $this->repository->getAsignacionesConFiltros($filtros);

        $asignaciones->each(function ($asignacion) {
            $asignacion->horas_pendientes_count = max(0, $asignacion->horas_semanales - $asignacion->horas_asignadas_count);
            $asignacion->porcentaje = $asignacion->horas_semanales > 0
                ? round(($asignacion->horas_asignadas_count / $asignacion->horas_semanales) * 100, 1)
                : 0;
            $asignacion->estado = $asignacion->estaCompleta()
                ? 'completo'
                : ($asignacion->horas_asignadas_count > 0 ? 'parcial' : 'pendiente');
        });

        return $asignaciones;
    }

    public function getGradosPorNivel(int $nivelId): array
    {
        Log::info('Solicitando grados para nivel', ['nivel_id' => $nivelId]);

        $nivel = $this->repository->findNivel($nivelId);

        if (!$nivel) {
            Log::warning('Nivel no encontrado', ['nivel_id' => $nivelId]);
            return ['encontrado' => false, 'grados' => collect()];
        }

        $grados = $this->repository->getGradosByNivel($nivelId);

        Log::info('Grados encontrados', [
            'nivel_id'     => $nivelId,
            'total_grados' => $grados->count(),
        ]);

        return ['encontrado' => true, 'grados' => $grados];
    }

    public function getMatriz(int $gradoId, int $year): array
    {
        $grado       = $this->repository->findGradoConNivel($gradoId);
        $profesores  = $this->repository->getProfesoresConAsignaturas();
        $asignaturas = $this->repository->getAsignaturas();
        $asignaciones_existentes = $this->repository->getAsignacionesPorGradoYYear($gradoId, $year);

        return compact('grado', 'profesores', 'asignaturas', 'asignaciones_existentes');
    }

    public function guardarMasiva(array $asignaciones): array
    {
        $guardadas  = 0;
        $actualizadas = 0;
        $errores    = [];

        DB::beginTransaction();
        try {
            foreach ($asignaciones as $index => $datos) {
                try {
                    $profesor = $this->repository->findProfesorConAsignaturas($datos['profesor_id']);

                    if (!$profesor->asignaturas->contains($datos['asignatura_id'])) {
                        $errores[] = "Fila {$index}: El profesor no puede impartir esta asignatura";
                        continue;
                    }

                    $asignacion = $this->repository->findByUniqueKey(
                        $datos['profesor_id'],
                        $datos['asignatura_id'],
                        $datos['grado_id'],
                        $datos['year']
                    );

                    if ($asignacion) {
                        $this->repository->update($asignacion, [
                            'horas_semanales' => $datos['horas_semanales'],
                            'periodo_id'      => $datos['periodo_id'] ?? null,
                        ]);
                        $actualizadas++;
                    } else {
                        $this->repository->create($datos);
                        $guardadas++;
                    }
                } catch (\Exception $e) {
                    Log::error("Error al procesar asignación {$index}", ['error' => $e->getMessage()]);
                    $errores[] = "Fila {$index}: " . $e->getMessage();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        $mensaje = "Proceso completado";
        if ($guardadas > 0)        $mensaje .= ": {$guardadas} creadas";
        if ($actualizadas > 0)     $mensaje .= ", {$actualizadas} actualizadas";
        if (count($errores) > 0)   $mensaje .= ", " . count($errores) . " errores";

        return compact('mensaje', 'guardadas', 'actualizadas', 'errores');
    }

    public function getResumenYear(int $year): array
    {
        $asignaciones = $this->repository->getAsignacionesPorYear($year);

        return [
            'total'     => $asignaciones->count(),
            'profesores' => $asignaciones->pluck('profesor_id')->unique()->count(),
            'grados'    => $asignaciones->pluck('grado_id')->unique()->count(),
        ];
    }

    public function copiarYear(int $yearOrigen, int $yearDestino, bool $sobreescribir): array
    {
        $eliminadas = 0;

        DB::beginTransaction();
        try {
            if ($sobreescribir) {
                $eliminadas = $this->repository->eliminarPorYear($yearDestino);
            }

            $asignacionesOrigen = $this->repository->getAsignacionesPorYear($yearOrigen);

            if ($asignacionesOrigen->isEmpty()) {
                DB::rollBack();
                return ['vacio' => true, 'yearOrigen' => $yearOrigen];
            }

            $copiadas = 0;

            foreach ($asignacionesOrigen as $asignacion) {
                if (!$sobreescribir) {
                    $existe = $this->repository->existeAsignacion(
                        $asignacion->profesor_id,
                        $asignacion->asignatura_id,
                        $asignacion->grado_id,
                        $yearDestino
                    );
                    if ($existe) continue;
                }

                $this->repository->create([
                    'profesor_id'      => $asignacion->profesor_id,
                    'asignatura_id'    => $asignacion->asignatura_id,
                    'grado_id'         => $asignacion->grado_id,
                    'horas_semanales'  => $asignacion->horas_semanales,
                    'year'             => $yearDestino,
                    'periodo_id'       => $asignacion->periodo_id,
                    'posicion_jornada' => $asignacion->posicion_jornada,
                    'max_horas_por_dia' => $asignacion->max_horas_por_dia,
                    'max_dias_semana'  => $asignacion->max_dias_semana,
                ]);

                $copiadas++;
            }

            DB::commit();

            Log::info('Asignaciones copiadas', [
                'year_origen'  => $yearOrigen,
                'year_destino' => $yearDestino,
                'copiadas'     => $copiadas,
                'eliminadas'   => $eliminadas,
            ]);

            return ['vacio' => false, 'copiadas' => $copiadas, 'eliminadas' => $eliminadas];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function store(array $data): object
    {
        $profesor = $this->repository->findProfesorConAsignaturas($data['profesor_id']);

        if (!$profesor->asignaturas->contains($data['asignatura_id'])) {
            throw new \Exception('El profesor no está habilitado para impartir esta asignatura', 422);
        }

        $existe = $this->repository->existeAsignacion(
            $data['profesor_id'],
            $data['asignatura_id'],
            $data['grado_id'],
            $data['year']
        );

        if ($existe) {
            throw new \Exception('Ya existe una asignación con estos datos', 422);
        }

        DB::beginTransaction();
        try {
            $asignacion = $this->repository->create($data);
            DB::commit();

            Log::info('Asignación creada', ['id' => $asignacion->id]);

            return $asignacion->load(['profesor', 'asignatura', 'grado.nivel']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show(int $id): object
    {
        $asignacion = $this->repository->findById($id);

        $asignacion->horas_pendientes_count = max(0, $asignacion->horas_semanales - $asignacion->horas_asignadas_count);
        $asignacion->porcentaje = $asignacion->horas_semanales > 0
            ? round(($asignacion->horas_asignadas_count / $asignacion->horas_semanales) * 100, 1)
            : 0;

        return $asignacion;
    }

    public function update(int $id, array $data): object
    {
        DB::beginTransaction();
        try {
            $asignacion = $this->repository->findById($id);
            $this->repository->update($asignacion, $data);
            DB::commit();

            Log::info('Asignación actualizada', ['id' => $asignacion->id]);

            return $asignacion->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy(int $id): string
    {
        DB::beginTransaction();
        try {
            $asignacion = $this->repository->findById($id);

            if ($asignacion->horarios()->exists()) {
                $total = $asignacion->horarios()->count();
                throw new \Exception("tiene_horarios:{$total}", 422);
            }

            $mensaje = "{$asignacion->profesor->name} - {$asignacion->asignatura->nombre} - {$asignacion->grado->nombre}";

            $this->repository->delete($asignacion);
            DB::commit();

            Log::info('Asignación eliminada', ['id' => $id]);

            return $mensaje;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getEstadisticas(int $year): array
    {
        $asignaciones = $this->repository->getAsignacionesPorYearConHorarios($year);

        $total         = $asignaciones->count();
        $completas     = $asignaciones->filter(fn($a) => $a->horarios_count >= $a->horas_semanales)->count();
        $parciales     = $asignaciones->filter(fn($a) => $a->horarios_count > 0 && $a->horarios_count < $a->horas_semanales)->count();
        $pendientes    = $asignaciones->filter(fn($a) => $a->horarios_count == 0)->count();
        $horasTotales  = $asignaciones->sum('horas_semanales');
        $horasAsignadas = $asignaciones->sum('horarios_count');

        return [
            'total'            => $total,
            'completas'        => $completas,
            'parciales'        => $parciales,
            'pendientes'       => $pendientes,
            'horas_totales'    => $horasTotales,
            'horas_asignadas'  => $horasAsignadas,
            'porcentaje_global' => $horasTotales > 0 ? round(($horasAsignadas / $horasTotales) * 100, 1) : 0,
        ];
    }

    public function getResumenProfesor(int $profesorId, int $year): array
    {
        $profesor = $this->repository->findProfesor($profesorId);

        if (method_exists($profesor, 'resumenCargaAcademica')) {
            return $profesor->resumenCargaAcademica($year);
        }

        $asignaciones = $this->repository->getAsignacionesPorProfesorYYear($profesorId, $year);

        return [
            'total_asignaciones'     => $asignaciones->count(),
            'total_horas_requeridas' => $asignaciones->sum('horas_semanales'),
            'total_horas_asignadas'  => $asignaciones->sum('horas_asignadas_count'),
            'asignaciones'           => $asignaciones,
        ];
    }

    public function getResumenGrado(int $gradoId, int $year): array
    {
        $asignaciones   = $this->repository->getAsignacionesPorGradoYYearConRelaciones($gradoId, $year);
        $total_horas    = $asignaciones->sum('horas_semanales');
        $horas_asignadas = $asignaciones->sum('horas_asignadas_count');

        return [
            'total_asignaciones'     => $asignaciones->count(),
            'total_horas_requeridas' => $total_horas,
            'total_horas_asignadas'  => $horas_asignadas,
            'horas_pendientes'       => max(0, $total_horas - $horas_asignadas),
            'porcentaje_completado'  => $total_horas > 0 ? round(($horas_asignadas / $total_horas) * 100, 1) : 0,
            'asignaciones'           => $asignaciones,
        ];
    }

    public function validar(array $data): array
    {
        $errores = [];

        $profesor = $this->repository->findProfesorConAsignaturas($data['profesor_id']);
        if (!$profesor->asignaturas->contains($data['asignatura_id'])) {
            $errores[] = 'El profesor no está habilitado para impartir esta asignatura';
        }

        $existe = $this->repository->existeAsignacion(
            $data['profesor_id'],
            $data['asignatura_id'],
            $data['grado_id'],
            $data['year'],
            $data['id'] ?? null
        );

        if ($existe) {
            $errores[] = 'Ya existe una asignación con estos mismos datos';
        }

        return $errores;
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