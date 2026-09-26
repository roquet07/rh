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
        Schema::create('comisiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->decimal('monto_base', 12, 2);
            $table->decimal('porcentaje_aplicado', 5, 2);
            $table->decimal('monto_comision', 12, 2);
            $table->date('fecha_periodo_inicio');
            $table->date('fecha_periodo_fin');
            $table->enum('estatus', ['pendiente', 'pagada'])->default('pendiente');
            $table->foreignId('capturado_por_user_id')->constrained('users')->restrictOnDelete();
            $table->text('notas')->nullable();
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
        Schema::dropIfExists('comisiones');
    }
};
