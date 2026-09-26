<?php

namespace App\Actions\Nomina;

use App\Models\IsrBracket;

class CalcularISR
{
    /**
     * Retención de ISR conforme a la tarifa del Art. 96 LISR para el ejercicio y periodicidad dados.
     */
    public function calcular(float $baseGravable, int $ejercicioFiscal, string $periodicidad = 'quincenal'): float
    {
        $bracket = IsrBracket::query()
            ->where('ejercicio_fiscal', $ejercicioFiscal)
            ->where('periodicidad', $periodicidad)
            ->where('limite_inferior', '<=', $baseGravable)
            ->where(function ($query) use ($baseGravable) {
                $query->whereNull('limite_superior')->orWhere('limite_superior', '>=', $baseGravable);
            })
            ->orderByDesc('limite_inferior')
            ->first();

        if ($bracket === null) {
            return 0.0;
        }

        $excedente = $baseGravable - (float) $bracket->limite_inferior;

        return round((float) $bracket->cuota_fija + ($excedente * (float) $bracket->porcentaje_excedente / 100), 2);
    }
}
