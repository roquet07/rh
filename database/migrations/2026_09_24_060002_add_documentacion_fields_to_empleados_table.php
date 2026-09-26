<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        // Postgres emula enum con un CHECK constraint que ->change() no reemplaza solo.
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('alter table empleados drop constraint if exists empleados_estatus_check');
        }

        Schema::table('empleados', function (Blueprint $table) {
            $table->string('estatus')->default('documentacion_pendiente')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->string('estatus')->default('activo')->change();
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
