<?php

namespace Database\Seeders;

use App\Models\ImssRate;
use Illuminate\Database\Seeder;

class ImssRatesSeeder extends Seeder
{
    /**
     * Siembra una tasa IMSS de referencia (UMA y porcentaje obrero agregado simplificado).
     * IMPORTANTE: valores ilustrativos. Un administrador debe actualizar la UMA vigente y las
     * tasas desde la pantalla de configuración fiscal antes de usar el sistema para nómina real.
     * El cálculo resultante siempre es un estimado, no sustituye el cálculo oficial del IMSS.
     */
    public function run(): void
    {
        ImssRate::query()->updateOrCreate(
            ['ejercicio_fiscal' => (int) now()->year],
            [
                'uma_diaria' => 113.14,
                'porcentaje_obrero' => 2.3750,
                'porcentaje_patronal' => 20.4000,
                'tope_sbc_umas' => 25,
                'notas' => 'Estimado, no sustituye el cálculo oficial del IMSS. Actualizar UMA y tasas cada año.',
            ],
        );
    }
}
