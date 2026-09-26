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
        Schema::create('contratos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->foreignId('solicitud_vacante_id')->nullable()->constrained('solicitudes_vacante')->nullOnDelete();
            $table->foreignId('puesto_id')->constrained('puestos')->restrictOnDelete();
            $table->enum('tipo', ['indeterminado', 'determinado', 'obra_determinada', 'periodo_prueba', 'capacitacion_inicial']);
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->decimal('salario_diario', 10, 2);
            $table->enum('jornada', ['diurna', 'nocturna', 'mixta']);
            $table->string('lugar_trabajo');
            $table->text('clausulas_adicionales')->nullable();
            $table->enum('estatus', ['borrador', 'generado', 'firmado'])->default('borrador');
            $table->date('fecha_firma')->nullable();
            $table->string('documento_firmado_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();

            $table->index('empleado_id');
            $table->index('estatus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
