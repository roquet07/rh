<?php

namespace Database\Seeders;

use App\Models\IsrBracket;
use Illuminate\Database\Seeder;

class IsrBracketsSeeder extends Seeder
{
    /**
     * Siembra una tarifa ISR de referencia con la estructura progresiva del Art. 96 LISR
     * (11 rangos, 1.92% a 35%). IMPORTANTE: estos valores son ilustrativos, NO los montos
     * oficiales vigentes publicados por el SAT. Antes de usar el sistema para nómina real,
     * un administrador debe reemplazarlos con la tarifa quincenal oficial del ejercicio fiscal
     * vigente desde la pantalla de configuración fiscal.
     */
    public function run(): void
    {
        $ejercicio = (int) now()->year;

        $rangos = [
            [0.01, 350.00, 0.00, 1.92],
            [350.01, 2966.00, 6.72, 6.40],
            [2966.01, 5214.00, 174.03, 10.88],
            [5214.01, 6058.00, 418.60, 16.00],
            [6058.01, 7252.00, 553.68, 17.92],
            [7252.01, 14624.00, 768.06, 21.36],
            [14624.01, 23046.00, 1343.29, 23.52],
            [23046.01, 44001.00, 2124.94, 30.00],
            [44001.01, 58668.00, 8410.34, 32.00],
            [58668.01, 176004.00, 13104.78, 34.00],
            [176004.01, null, 52981.30, 35.00],
        ];

        foreach ($rangos as $rango) {
            IsrBracket::query()->updateOrCreate(
                [
                    'ejercicio_fiscal' => $ejercicio,
                    'periodicidad' => 'quincenal',
                    'limite_inferior' => $rango[0],
                ],
                [
                    'limite_superior' => $rango[1],
                    'cuota_fija' => $rango[2],
                    'porcentaje_excedente' => $rango[3],
                ],
            );
        }
    }
}
