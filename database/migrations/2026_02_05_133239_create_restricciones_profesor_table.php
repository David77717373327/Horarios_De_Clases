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
        Schema::create('restricciones_profesor', function (Blueprint $table) {
            $table->id();
            
            // Relación con el profesor
            $table->foreignId('profesor_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('ID del profesor con restricción');
            
            // Definir cuándo NO puede dar clases
            $table->enum('dia_semana', ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'])
                ->nullable()
                ->comment('Día específico (NULL = todos los días)');
            
            $table->integer('hora_numero')
                ->unsigned()
                ->nullable()
                ->comment('Número de hora específica (ej: 3, 4). NULL = todas las horas del día');
            
            // Alternativa: rangos de tiempo
            $table->time('hora_inicio')
                ->nullable()
                ->comment('Hora de inicio del bloqueo (alternativo a hora_numero)');
            
            $table->time('hora_fin')
                ->nullable()
                ->comment('Hora de fin del bloqueo (alternativo a hora_numero)');
            
            $table->string('motivo', 100)
                ->nullable()
                ->comment('Razón del bloqueo: reunion, personal, almuerzo, etc.');
            
            $table->integer('year')
                ->unsigned()
                ->comment('Año académico al que aplica');
            
            $table->boolean('activa')
                ->default(true)
                ->comment('Si la restricción está activa');
            
            $table->timestamps();
            
            // Índices
            $table->index(['profesor_id', 'year']);
            $table->index(['profesor_id', 'dia_semana', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restricciones_profesor');
    }
};