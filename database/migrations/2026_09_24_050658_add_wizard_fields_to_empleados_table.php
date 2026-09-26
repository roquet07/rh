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
        Schema::table('empleados', function (Blueprint $table) {
            $table->string('apellido_paterno')->nullable()->after('nombre_completo');
            $table->string('apellido_materno')->nullable()->after('apellido_paterno');
            $table->string('correo_personal')->nullable()->after('telefono');
            $table->string('colonia')->nullable()->after('domicilio');
            $table->string('codigo_postal', 5)->nullable()->after('colonia');
            $table->string('municipio')->nullable()->after('codigo_postal');
            $table->string('estado_direccion')->nullable()->after('municipio');
            $table->string('regimen_fiscal')->nullable()->after('nss');
            $table->foreignId('jefe_directo_id')->nullable()->after('puesto_id')
                ->constrained('empleados')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropConstrainedForeignId('jefe_directo_id');
            $table->dropColumn([
                'apellido_paterno',
                'apellido_materno',
                'correo_personal',
                'colonia',
                'codigo_postal',
                'municipio',
                'estado_direccion',
                'regimen_fiscal',
            ]);
        });
    }
};
