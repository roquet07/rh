<?php

namespace Database\Seeders;

use App\Models\TablaVacacionesAntiguedad;
use Illuminate\Database\Seeder;

class TablaVacacionesAntiguedadSeeder extends Seeder
{
    /**
     * Seed the vacation-days-by-seniority lookup table per LFT Art. 76 (reforma 2023).
     */
    public function run(): void
    {
        for ($anios = 1; $anios <= 50; $anios++) {
            $dias = $anios <= 5
                ? 10 + (2 * $anios)
                : 20 + (2 * (int) ceil(($anios - 5) / 5));

            TablaVacacionesAntiguedad::query()->updateOrCreate(
                ['anios_antiguedad' => $anios],
                ['dias_vacaciones' => $dias, 'vigente_desde' => '2023-01-01'],
            );
        }
    }
}
