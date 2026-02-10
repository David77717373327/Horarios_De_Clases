<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('descansos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('nivel_id')
                ->constrained('niveles')
                ->onDelete('cascade');

            $table->time('hora_inicio');
            $table->time('hora_fin');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('descansos');
    }
};
