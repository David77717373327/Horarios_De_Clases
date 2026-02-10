<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asignaciones_academicas', function (Blueprint $table) {
            $table->id();
            
            // Relaciones con otras tablas
            $table->foreignId('profesor_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('ID del profesor (user con role=professor)');
            
            $table->foreignId('asignatura_id')
                ->constrained('asignaturas')
                ->onDelete('cascade')
                ->comment('ID de la asignatura a impartir');
            
            $table->foreignId('grado_id')
                ->constrained('grados')
                ->onDelete('cascade')
                ->comment('ID del grado donde se imparte');
            
            // Configuración de carga horaria
            $table->integer('horas_semanales')
                ->unsigned()
                ->comment('Número de horas que debe dar el profesor de esta materia por semana');
            
            $table->integer('year')
                ->unsigned()
                ->comment('Año académico (ej: 2024, 2025)');
            
            $table->integer('periodo_id')
                ->unsigned()
                ->nullable()
                ->comment('ID del periodo/trimestre (opcional)');
            
            $table->timestamps();
            
            // Índice único: evitar duplicados
            $table->unique(
                ['profesor_id', 'asignatura_id', 'grado_id', 'year', 'periodo_id'],
                'asignacion_unica'
            );
            
            // Índices para búsquedas rápidas
            $table->index(['profesor_id', 'year']);
            $table->index(['grado_id', 'year']);
            $table->index('year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignaciones_academicas');
    }
};