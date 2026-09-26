<?php

namespace App\Actions\Nomina;

use Carbon\CarbonImmutable;
use DateTimeInterface;

class CalcularAguinaldo
{
    /**
     * Aguinaldo proporcional a los días laborados en el año (Art. 87 LFT), mínimo 15 días de salario.
     */
    public function calcular(
        float $salarioDiario,
        DateTimeInterface $fechaIngreso,
        DateTimeInterface $fechaCorte,
        int $diasAguinaldo = 15,
    ): float {
        $ingreso = CarbonImmutable::instance($fechaIngreso);
        $corte = CarbonImmutable::instance($fechaCorte);

        $inicioConteo = $corte->startOfYear()->max($ingreso);
        $diasLaboradosEnAnio = min(365, max(0, $inicioConteo->diffInDays($corte) + 1));

        return round(($diasLaboradosEnAnio / 365) * $diasAguinaldo * $salarioDiario, 2);
    }
}
