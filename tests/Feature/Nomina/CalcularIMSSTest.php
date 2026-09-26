<?php

use App\Actions\Nomina\CalcularIMSS;
use App\Models\ImssRate;

beforeEach(function () {
    ImssRate::query()->create([
        'ejercicio_fiscal' => 2026,
        'uma_diaria' => 100.00,
        'porcentaje_obrero' => 2.5,
        'porcentaje_patronal' => 20.0,
        'tope_sbc_umas' => 25,
        'notas' => 'Estimado, no sustituye el cálculo oficial del IMSS.',
    ]);
});

test('IMSS calcula la cuota sobre el salario diario cuando no rebasa el tope', function () {
    $resultado = (new CalcularIMSS)->calcular(salarioDiario: 500, dias: 15, ejercicioFiscal: 2026);

    expect($resultado['cuota'])->toBe(round(500 * 15 * 0.025, 2))
        ->and($resultado['estimado'])->toBeTrue();
});

test('IMSS topa el salario base de cotización a 25 UMA', function () {
    $topeSbc = 100.00 * 25;

    $resultado = (new CalcularIMSS)->calcular(salarioDiario: 5000, dias: 15, ejercicioFiscal: 2026);

    expect($resultado['cuota'])->toBe(round($topeSbc * 15 * 0.025, 2))
        ->and($resultado['estimado'])->toBeTrue();
});

test('IMSS retorna cero y estimado true cuando no hay tasa configurada', function () {
    $resultado = (new CalcularIMSS)->calcular(salarioDiario: 500, dias: 15, ejercicioFiscal: 1999);

    expect($resultado['cuota'])->toBe(0.0)
        ->and($resultado['estimado'])->toBeTrue();
});
