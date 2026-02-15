<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asignaciones_academicas', function (Blueprint $table) {
            $table->enum('posicion_jornada', [
                'primeras_horas',
                'ultimas_horas', 
                'antes_recreo',
                'despues_recreo',
                'sin_restriccion'
            ])
            ->nullable()
            ->default('sin_restriccion')
            ->after('periodo_id')
            ->comment('Restricción de posición en la jornada escolar');
            
            $table->tinyInteger('max_horas_por_dia')
                ->unsigned()
                ->nullable()
                ->after('posicion_jornada')
                ->comment('Máximo de horas de esta materia por día (NULL = sin límite)');
            
            $table->tinyInteger('max_dias_semana')
                ->unsigned()
                ->nullable()
                ->after('max_horas_por_dia')
                ->comment('Máximo de días a la semana que puede aparecer (NULL = sin límite)');
        });
    }

    public function down(): void
    {
        Schema::table('asignaciones_academicas', function (Blueprint $table) {
            $table->dropColumn(['posicion_jornada', 'max_horas_por_dia', 'max_dias_semana']);
        });
    }
};