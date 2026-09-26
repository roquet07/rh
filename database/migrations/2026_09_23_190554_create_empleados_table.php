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
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->foreignId('puesto_id')->constrained('puestos')->restrictOnDelete();
            $table->string('numero_empleado')->unique();
            $table->string('nombre_completo');
            $table->string('rfc', 13)->unique();
            $table->string('curp', 18)->unique();
            $table->string('nss', 11)->nullable();
            $table->date('fecha_nacimiento');
            $table->string('estado_civil')->nullable();
            $table->text('domicilio')->nullable();
            $table->string('telefono')->nullable();
            $table->string('contacto_emergencia_nombre')->nullable();
            $table->string('contacto_emergencia_telefono')->nullable();
            $table->date('fecha_ingreso');
            $table->date('fecha_baja')->nullable();
            $table->text('motivo_baja')->nullable();
            $table->decimal('salario_diario', 10, 2);
            $table->enum('jornada', ['diurna', 'nocturna', 'mixta'])->default('diurna');
            $table->string('sucursal')->nullable();
            $table->string('banco')->nullable();
            $table->string('clabe', 18)->nullable();
            $table->enum('estatus', ['activo', 'baja'])->default('activo');
            $table->timestamps();

            $table->index('estatus');
            $table->index('puesto_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
