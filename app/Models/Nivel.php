<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nivel extends Model
{
    use HasFactory;

    // 🔴 ESTO ES LO QUE FALTABA
    protected $table = 'niveles';

    protected $fillable = [
        'nombre',
    ];

    // Un nivel tiene muchos grados
    public function grados()
    {
        return $this->hasMany(Grado::class);
    }

    // Un nivel tiene descansos
    public function descansos()
    {
        return $this->hasMany(Descanso::class);
    }
}
