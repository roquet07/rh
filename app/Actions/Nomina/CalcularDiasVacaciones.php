<?php

namespace App\Actions\Nomina;

use App\Models\TablaVacacionesAntiguedad;

class CalcularDiasVacaciones
{
    /**
     * Días de vacaciones que corresponden según años de antigüedad (LFT Art. 76, reforma 2023).
     * Si la antigüedad supera el máximo de la tabla, se toma el último valor disponible.
     */
    public function calcular(int $aniosAntiguedad): int
    {
        if ($aniosAntiguedad < 1) {
            return 0;
        }

        $fila = TablaVacacionesAntiguedad::query()
            ->where('anios_antiguedad', '<=', $aniosAntiguedad)
            ->orderByDesc('anios_antiguedad')
            ->first();

        if ($fila === null) {
            return 0;
        }

        return $fila->dias_vacaciones;
    }
}
