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
        Schema::create('puestos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departamento_id')->constrained('departamentos')->restrictOnDelete();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->decimal('salario_min', 10, 2);
            $table->decimal('salario_max', 10, 2);
            $table->decimal('porcentaje_comision', 5, 2)->default(0);
            $table->enum('jornada', ['diurna', 'nocturna', 'mixta'])->default('diurna');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('departamento_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puestos');
    }
};
