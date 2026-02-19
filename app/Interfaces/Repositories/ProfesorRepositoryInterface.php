<?php

namespace App\Interfaces\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface ProfesorRepositoryInterface
{
    public function getAll(): Collection;
    public function getAllAsignaturas(): Collection;
    public function findById(int $id): User;
    public function create(array $data): User;
    public function update(User $profesor, array $data): User;
    public function syncAsignaturas(User $profesor, array $asignaturas): void;
    public function attachAsignaturas(User $profesor, array $asignaturas): void;
    public function detachAsignaturas(User $profesor): void;
    public function delete(User $profesor): void;
}