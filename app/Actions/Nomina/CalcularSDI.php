<?php

namespace App\Actions\Nomina;

class CalcularSDI
{
    /**
     * Salario Diario Integrado: incluye la parte proporcional de aguinaldo y prima vacacional.
     */
    public function calcular(
        float $salarioDiario,
        int $diasVacaciones,
        float $primaVacacionalPct = 0.25,
        int $diasAguinaldo = 15,
    ): float {
        $factorIntegracion = (365 + $diasAguinaldo + ($diasVacaciones * $primaVacacionalPct)) / 365;

        return round($salarioDiario * $factorIntegracion, 2);
    }
}
