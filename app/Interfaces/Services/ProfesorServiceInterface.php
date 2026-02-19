<?php

namespace App\Interfaces\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface ProfesorServiceInterface
{
    public function getAllProfesores(): Collection;
    public function getAllAsignaturas(): Collection;
    public function getProfesorById(int $id): User;
    public function createProfesor(array $data): User;
    public function updateProfesor(int $id, array $data): User;
    public function asignarAsignaturas(int $id, array $data): User;
    public function deleteProfesor(int $id): string;
}