<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->unsignedSmallInteger('duracion_meses')->nullable();
            $table->date('fecha_celebracion')->nullable();
            $table->string('nacionalidad')->nullable();
            $table->string('sexo')->nullable();
            $table->text('funciones')->nullable();
            $table->string('horario_trabajo')->nullable();
            $table->unsignedSmallInteger('horas_semanales')->nullable();
            $table->unsignedSmallInteger('descanso_minutos')->nullable();
            $table->string('dia_descanso')->nullable();
            $table->decimal('salario_mensual', 12, 2)->nullable();
            $table->string('periodicidad_pago')->nullable();
            $table->string('dias_pago')->nullable();
            $table->string('forma_pago')->nullable();
            $table->json('beneficiarios')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->dropColumn(['duracion_meses', 'fecha_celebracion', 'nacionalidad', 'sexo', 'funciones', 'horario_trabajo', 'horas_semanales', 'descanso_minutos', 'dia_descanso', 'salario_mensual', 'periodicidad_pago', 'dias_pago', 'forma_pago', 'beneficiarios']);
        });
    }
};
