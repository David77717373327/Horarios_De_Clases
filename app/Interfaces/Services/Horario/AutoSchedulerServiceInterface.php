<?php

namespace App\Interfaces\Services\Horario;

use Illuminate\Database\Eloquent\Collection;

interface AutoSchedulerServiceInterface
{
    public function generarHorariosNivelCompleto(int $nivelId, int $year, array $configuracion, Collection $gradosDelNivel): array;
    public function limpiarHorariosNivel(int $nivelId, int $year): void;
}