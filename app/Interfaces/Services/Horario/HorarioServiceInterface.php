<?php

namespace App\Interfaces\Services\Horario;

use Illuminate\Database\Eloquent\Collection;

interface HorarioServiceInterface
{
    public function getDatosIndex(): array;
    public function getDatosCreate(): array;
    public function getGradosByNivel(int $nivelId): Collection;
    public function getHorario(array $data): array;
    public function destroy(array $data): array;
}