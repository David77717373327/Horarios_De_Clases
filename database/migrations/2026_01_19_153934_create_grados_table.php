<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('grados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // 601, 602, etc.

            $table->foreignId('nivel_id')
                ->constrained('niveles')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('grados');
    }
};
