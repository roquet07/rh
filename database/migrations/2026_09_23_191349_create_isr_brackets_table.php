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
        Schema::create('isr_brackets', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('ejercicio_fiscal');
            $table->enum('periodicidad', ['semanal', 'decenal', 'catorcenal', 'quincenal', 'mensual'])->default('quincenal');
            $table->decimal('limite_inferior', 12, 2);
            $table->decimal('limite_superior', 12, 2)->nullable();
            $table->decimal('cuota_fija', 12, 2);
            $table->decimal('porcentaje_excedente', 5, 2);
            $table->timestamps();

            $table->index(['ejercicio_fiscal', 'periodicidad']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('isr_brackets');
    }
};
