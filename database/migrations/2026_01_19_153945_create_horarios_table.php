<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('horarios', function (Blueprint $table) {
        $table->id();

        // Agregar nivel_id
        $table->foreignId('nivel_id')
            ->constrained('niveles')
            ->onDelete('cascade');

        $table->foreignId('grado_id')
            ->constrained('grados')
            ->onDelete('cascade');

        $table->foreignId('asignatura_id')
            ->constrained('asignaturas')
            ->onDelete('cascade');

        $table->foreignId('profesor_id')
            ->constrained('users')
            ->onDelete('cascade');

        // Campos adicionales que tu controlador también espera
        $table->string('dia_semana'); // Cambiado de enum 'dia'
        $table->integer('hora_numero');
        $table->integer('year');
        $table->time('hora_inicio');
        $table->time('hora_fin');
        $table->integer('duracion_clase');
        $table->integer('horas_por_dia');
        $table->json('dias_semana');
        $table->integer('recreo_despues_hora')->nullable();
        $table->integer('recreo_duracion')->nullable();

        // Restricciones únicas actualizadas
        $table->unique(
            ['profesor_id', 'dia_semana', 'hora_numero', 'year'],
            'profesor_no_cruce'
        );

        $table->unique(
            ['grado_id', 'dia_semana', 'hora_numero', 'year'],
            'grado_no_cruce'
        );

        $table->timestamps();
    });
}

    public function down()
    {
        Schema::dropIfExists('horarios');
    }
};
