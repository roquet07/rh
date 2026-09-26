<?php

namespace App\Actions\Nomina;

use App\Models\ImssRate;

class CalcularIMSS
{
    /**
     * Cálculo simplificado y estimado de la cuota obrera del IMSS. No sustituye el cálculo oficial
     * del IMSS, que involucra múltiples ramos (enfermedad y maternidad, invalidez y vida, cesantía en
     * edad avanzada y vejez, guarderías, riesgo de trabajo) e INFONAVIT.
     *
     * @return array{cuota: float, estimado: bool}
     */
    public function calcular(float $salarioDiario, int $dias, int $ejercicioFiscal): array
    {
        $tasa = ImssRate::query()->where('ejercicio_fiscal', $ejercicioFiscal)->first();

        if ($tasa === null) {
            return ['cuota' => 0.0, 'estimado' => true];
        }

        $topeSbc = (float) $tasa->uma_diaria * $tasa->tope_sbc_umas;
        $sbc = min($salarioDiario, $topeSbc);

        $cuota = round($sbc * $dias * ((float) $tasa->porcentaje_obrero / 100), 2);

        return ['cuota' => $cuota, 'estimado' => true];
    }
}
