<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Descanso extends Model
{
    use HasFactory;

    protected $fillable = [
        'nivel_id',
        'hora_inicio',
        'hora_fin',
    ];

    public function nivel()
    {
        return $this->belongsTo(Nivel::class);
    }
}
