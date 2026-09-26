<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresa_config', function (Blueprint $table) {
            $table->text('escritura_constitutiva')->nullable();
            $table->text('poder_representante')->nullable();
            $table->string('ciudad_firma')->nullable();
            $table->string('entidad_jurisdiccion')->nullable();
            $table->string('correo_privacidad')->nullable();
            $table->text('url_aviso_privacidad')->nullable();
            $table->string('horario_privacidad')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('empresa_config', function (Blueprint $table) {
            $table->dropColumn(['escritura_constitutiva', 'poder_representante', 'ciudad_firma', 'entidad_jurisdiccion', 'correo_privacidad', 'url_aviso_privacidad', 'horario_privacidad']);
        });
    }
};
