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
        Schema::create('solicitudes_vacante', function (Blueprint $table) {
            $table->id();
            $table->string('folio')->unique();
            $table->foreignId('solicitante_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('departamento_id')->constrained('departamentos')->restrictOnDelete();
            $table->foreignId('puesto_id')->nullable()->constrained('puestos')->nullOnDelete();
            $table->string('puesto_propuesto')->nullable();
            $table->decimal('salario_propuesto', 10, 2);
            $table->unsignedSmallInteger('numero_plazas')->default(1);
            $table->text('justificacion');
            $table->enum('urgencia', ['baja', 'media', 'alta'])->default('media');
            $table->enum('estatus', ['pendiente', 'aprobada', 'rechazada'])->default('pendiente');
            $table->foreignId('revisado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('comentario_revision')->nullable();
            $table->timestamp('fecha_revision')->nullable();
            $table->timestamps();

            $table->index('estatus');
            $table->index('departamento_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes_vacante');
    }
};
