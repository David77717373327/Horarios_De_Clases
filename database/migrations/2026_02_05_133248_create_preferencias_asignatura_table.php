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
        Schema::create('preferencias_asignatura', function (Blueprint $table) {
            $table->id();
            
            // Relación con asignatura
            $table->foreignId('asignatura_id')
                ->constrained('asignaturas')
                ->onDelete('cascade')
                ->comment('ID de la asignatura');
            
            // Preferencia de momento del día
            $table->enum('momento_preferido', [
                'primeras_horas',
                'horas_centrales',
                'ultimas_horas',
                'sin_preferencia'
            ])->default('sin_preferencia')
              ->comment('Momento preferido del día para esta asignatura');
            
            // Prioridad en asignación automática (mayor número = mayor prioridad)
            $table->integer('prioridad')
                ->default(0)
                ->comment('Prioridad de asignación (0-100, mayor = primero)');
            
            // Restricciones específicas
            $table->boolean('evitar_ultima_hora')
                ->default(false)
                ->comment('Evitar asignar en la última hora del día');
            
            $table->boolean('evitar_primera_hora')
                ->default(false)
                ->comment('Evitar asignar en la primera hora del día');
            
            $table->boolean('requiere_consecutivas')
                ->default(false)
                ->comment('Requiere horas consecutivas (ej: laboratorios)');
            
            $table->integer('horas_consecutivas_min')
                ->unsigned()
                ->nullable()
                ->comment('Mínimo de horas consecutivas requeridas');
            
            $table->boolean('evitar_despues_recreo')
                ->default(false)
                ->comment('Evitar asignar inmediatamente después del recreo');
            
            $table->timestamps();
            
            // Una asignatura solo tiene una configuración
            $table->unique('asignatura_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preferencias_asignatura');
    }
};