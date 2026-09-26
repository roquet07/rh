<?php

use App\Actions\Nomina\CalcularISR;
use App\Models\IsrBracket;

beforeEach(function () {
    IsrBracket::query()->create([
        'ejercicio_fiscal' => 2026,
        'periodicidad' => 'quincenal',
        'limite_inferior' => 0.01,
        'limite_superior' => 1000.00,
        'cuota_fija' => 0.00,
        'porcentaje_excedente' => 10.00,
    ]);

    IsrBracket::query()->create([
        'ejercicio_fiscal' => 2026,
        'periodicidad' => 'quincenal',
        'limite_inferior' => 1000.01,
        'limite_superior' => 5000.00,
        'cuota_fija' => 100.00,
        'porcentaje_excedente' => 20.00,
    ]);

    IsrBracket::query()->create([
        'ejercicio_fiscal' => 2026,
        'periodicidad' => 'quincenal',
        'limite_inferior' => 5000.01,
        'limite_superior' => null,
        'cuota_fija' => 900.00,
        'porcentaje_excedente' => 30.00,
    ]);
});

test('ISR en el borde inferior del primer rango', function () {
    $isr = (new CalcularISR)->calcular(baseGravable: 0.01, ejercicioFiscal: 2026, periodicidad: 'quincenal');

    expect($isr)->toBe(0.0);
});

test('ISR a la mitad de un rango intermedio', function () {
    $isr = (new CalcularISR)->calcular(baseGravable: 3000.00, ejercicioFiscal: 2026, periodicidad: 'quincenal');

    expect($isr)->toBe(round(100.00 + (3000.00 - 1000.01) * 0.20, 2));
});

test('ISR en el último rango sin límite superior', function () {
    $isr = (new CalcularISR)->calcular(baseGravable: 50000.00, ejercicioFiscal: 2026, periodicidad: 'quincenal');

    expect($isr)->toBe(round(900.00 + (50000.00 - 5000.01) * 0.30, 2));
});

test('ISR retorna cero cuando no hay tabla para el ejercicio', function () {
    $isr = (new CalcularISR)->calcular(baseGravable: 1000.00, ejercicioFiscal: 1999, periodicidad: 'quincenal');

    expect($isr)->toBe(0.0);
});
