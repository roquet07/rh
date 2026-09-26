<?php

namespace App\Actions\Nomina;

class CalcularPrimaVacacional
{
    /**
     * Prima vacacional: mínimo 25% del salario correspondiente a los días de vacaciones (Art. 80 LFT).
     */
    public function calcular(float $salarioDiario, int $diasVacacionesTomados, float $porcentaje = 0.25): float
    {
        return round($salarioDiario * $diasVacacionesTomados * $porcentaje, 2);
    }
}
