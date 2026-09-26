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
        Schema::create('periodos_nomina', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['semanal', 'quincenal', 'mensual'])->default('quincenal');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->date('fecha_pago');
            $table->enum('estatus', ['abierto', 'calculado', 'pagado', 'cerrado'])->default('abierto');
            $table->timestamps();

            $table->unique(['fecha_inicio', 'fecha_fin']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodos_nomina');
    }
};
