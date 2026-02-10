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
        Schema::table('horarios', function (Blueprint $table) {
            // Referencia a la asignación académica que originó este horario
            $table->foreignId('asignacion_academica_id')
                ->nullable()
                ->after('profesor_id')
                ->constrained('asignaciones_academicas')
                ->onDelete('set null')
                ->comment('ID de la asignación académica origen');
            
            // Indicador de generación automática
            $table->boolean('generado_automaticamente')
                ->default(false)
                ->after('asignacion_academica_id')
                ->comment('Indica si fue generado automáticamente o manualmente');
            
            // Índice para búsquedas
            $table->index('asignacion_academica_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->dropForeign(['asignacion_academica_id']);
            $table->dropIndex(['asignacion_academica_id']);
            $table->dropColumn([
                'asignacion_academica_id',
                'generado_automaticamente'
            ]);
        });
    }
};