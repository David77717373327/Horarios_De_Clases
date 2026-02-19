<?php

namespace App\Services;

use App\Interfaces\Repositories\NivelRepositoryInterface;
use App\Interfaces\Services\NivelServiceInterface;
use App\Models\Nivel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NivelService implements NivelServiceInterface
{
    public function __construct(
        private NivelRepositoryInterface $nivelRepository
    ) {}

    public function getAllNiveles(): Collection
    {
        return $this->nivelRepository->getAll();
    }

    public function createNivel(array $data): Nivel
    {
        return $this->nivelRepository->create($data);
    }

    public function updateNivel(int $id, array $data): Nivel
    {
        $nivel = $this->nivelRepository->findById($id);
        return $this->nivelRepository->update($nivel, $data);
    }

    public function deleteNivel(int $id): void
    {
        Log::info("=== INICIO ELIMINACIÓN DE NIVEL ===");
        Log::info("ID recibido: {$id}");

        $nivel = $this->nivelRepository->findById($id);
        Log::info("Nivel encontrado - ID: {$nivel->id}, Nombre: {$nivel->nombre}");

        DB::beginTransaction();

        try {
            $gradosIds = $this->nivelRepository->getGradosIds($id);
            Log::info("Grados encontrados: " . count($gradosIds));

            if (!empty($gradosIds)) {

                $horariosEliminados = DB::table('horarios')
                    ->whereIn('grado_id', $gradosIds)->delete();
                Log::info("Horarios eliminados: {$horariosEliminados}");

                if (DB::getSchemaBuilder()->hasTable('asignaciones_academicas')) {
                    $asignaciones = DB::table('asignaciones_academicas')
                        ->whereIn('grado_id', $gradosIds)->delete();
                    Log::info("Asignaciones eliminadas: {$asignaciones}");
                }

                if (DB::getSchemaBuilder()->hasColumn('users', 'grado_id')) {
                    $estudiantes = DB::table('users')
                        ->whereIn('grado_id', $gradosIds)
                        ->update(['grado_id' => null]);
                    Log::info("Estudiantes desvinculados: {$estudiantes}");
                }

                if (DB::getSchemaBuilder()->hasTable('notas')) {
                    $notas = DB::table('notas')
                        ->whereIn('grado_id', $gradosIds)->delete();
                    Log::info("Notas eliminadas: {$notas}");
                }

                if (DB::getSchemaBuilder()->hasTable('asistencias')) {
                    $asistencias = DB::table('asistencias')
                        ->whereIn('grado_id', $gradosIds)->delete();
                    Log::info("Asistencias eliminadas: {$asistencias}");
                }

                $gradosEliminados = DB::table('grados')
                    ->where('nivel_id', $id)->delete();
                Log::info("Grados eliminados: {$gradosEliminados}");
            }

            $descansos = DB::table('descansos')
                ->where('nivel_id', $id)->delete();
            Log::info("Descansos eliminados: {$descansos}");

            $this->nivelRepository->delete($nivel);
            Log::info("Nivel eliminado correctamente");

            DB::commit();
            Log::info("=== ELIMINACIÓN COMPLETA EXITOSA ===");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("=== ERROR EN ELIMINACIÓN ===");
            Log::error("Mensaje: " . $e->getMessage());
            Log::error("Línea: " . $e->getLine());
            throw $e; // 🔑 Re-lanzamos para que el Controller lo maneje
        }
    }
}