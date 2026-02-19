<?php

namespace App\Interfaces\Services;

use App\Models\Grado;
use Illuminate\Database\Eloquent\Collection;

interface GradoServiceInterface
{
    public function getAllGrados(): Collection;
    public function getNiveles(): Collection; 
    public function createGrado(array $data): Grado;
    public function updateGrado(Grado $grado, array $data): Grado;
    public function deleteGrado(Grado $grado): void;
}