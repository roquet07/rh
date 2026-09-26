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
        Schema::create('tabla_vacaciones_antiguedad', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('anios_antiguedad')->unique();
            $table->unsignedTinyInteger('dias_vacaciones');
            $table->date('vigente_desde')->default('2023-01-01');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tabla_vacaciones_antiguedad');
    }
};
