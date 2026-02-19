<?php

namespace App\Interfaces\Repositories;

use App\Models\Grado;
use Illuminate\Database\Eloquent\Collection;

interface GradoRepositoryInterface
{
    public function getAll(): Collection;
    public function create(array $data): Grado;
    public function update(Grado $grado, array $data): Grado;
    public function delete(Grado $grado): void;
}