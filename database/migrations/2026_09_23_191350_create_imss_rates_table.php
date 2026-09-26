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
        Schema::create('imss_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('ejercicio_fiscal');
            $table->decimal('uma_diaria', 8, 2);
            $table->decimal('porcentaje_obrero', 6, 4);
            $table->decimal('porcentaje_patronal', 6, 4);
            $table->unsignedSmallInteger('tope_sbc_umas')->default(25);
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index('ejercicio_fiscal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imss_rates');
    }
};
