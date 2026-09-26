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
        Schema::create('nomina_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periodo_nomina_id')->constrained('periodos_nomina')->cascadeOnDelete();
            $table->foreignId('empleado_id')->constrained('empleados')->restrictOnDelete();
            $table->decimal('dias_trabajados', 4, 1);
            $table->decimal('salario_diario', 10, 2);
            $table->decimal('sdi', 10, 2);
            $table->decimal('percepcion_salario', 12, 2);
            $table->decimal('percepcion_comision', 12, 2)->default(0);
            $table->decimal('percepcion_aguinaldo', 12, 2)->default(0);
            $table->decimal('percepcion_ptu', 12, 2)->default(0);
            $table->decimal('percepcion_otras', 12, 2)->default(0);
            $table->decimal('deduccion_isr', 12, 2)->default(0);
            $table->decimal('deduccion_imss', 12, 2)->default(0);
            $table->decimal('deduccion_otras', 12, 2)->default(0);
            $table->decimal('total_percepciones', 12, 2);
            $table->decimal('total_deducciones', 12, 2);
            $table->decimal('neto_pagar', 12, 2);
            $table->string('recibo_pdf_path')->nullable();
            $table->timestamps();

            $table->unique(['periodo_nomina_id', 'empleado_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nomina_detalles');
    }
};
