<?php

namespace App\Interfaces\Services\Horario;

interface GeneradorHorarioServiceInterface
{
    public function generarAutomatico(int $nivelId, array $data, bool $limpiarExistentes): array;
}