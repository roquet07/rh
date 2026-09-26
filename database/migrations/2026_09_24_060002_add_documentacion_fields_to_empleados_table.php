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
            $table->foreignId('documentacion_revisado_por_user_id')->nullable()
                ->after('estatus')->constrained('users')->nullOnDelete();
            $table->timestamp('documentacion_fecha_revision')->nullable()->after('documentacion_revisado_por_user_id');
            $table->text('documentacion_comentario_revision')->nullable()->after('documentacion_fecha_revision');
            $table->enum('documentacion_estatus', ['pendiente', 'aprobada', 'rechazada'])
                ->default('pendiente')->after('documentacion_comentario_revision');
        });

        Schema::table('empleados', function (Blueprint $table) {
            $table->enum('estatus', ['documentacion_pendiente', 'activo', 'baja'])
                ->default('documentacion_pendiente')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->enum('estatus', ['activo', 'baja'])->default('activo')->change();
        });

        Schema::table('empleados', function (Blueprint $table) {
            $table->dropConstrainedForeignId('documentacion_revisado_por_user_id');
            $table->dropColumn([
                'documentacion_fecha_revision',
                'documentacion_comentario_revision',
                'documentacion_estatus',
            ]);
        });
    }
};
