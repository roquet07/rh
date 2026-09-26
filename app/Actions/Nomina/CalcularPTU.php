<?php

namespace App\Actions\Nomina;

class CalcularPTU
{
    /**
     * Reparto de PTU: 50% en proporción a los días trabajados, 50% en proporción a los salarios
     * devengados por cada empleado (Art. 123 fracción IX LFT).
     *
     * @param  array<int, array{dias_trabajados: int, salario_devengado: float}>  $empleadosData  Llave = empleado_id.
     * @return array<int, float> Llave = empleado_id, valor = monto de PTU correspondiente.
     */
    public function calcular(float $utilidadRepartible, array $empleadosData): array
    {
        if ($empleadosData === []) {
            return [];
        }

        $mitad = $utilidadRepartible / 2;

        $totalDias = array_sum(array_column($empleadosData, 'dias_trabajados'));
        $totalSalarios = array_sum(array_column($empleadosData, 'salario_devengado'));

        $resultado = [];

        foreach ($empleadosData as $empleadoId => $datos) {
            $porDias = $totalDias > 0 ? $mitad * ($datos['dias_trabajados'] / $totalDias) : 0.0;
            $porSalario = $totalSalarios > 0 ? $mitad * ($datos['salario_devengado'] / $totalSalarios) : 0.0;

            $resultado[$empleadoId] = round($porDias + $porSalario, 2);
        }

        return $resultado;
    }
}
