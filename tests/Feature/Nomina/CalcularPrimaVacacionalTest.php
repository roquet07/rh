<?php

use App\Actions\Nomina\CalcularPrimaVacacional;

test('prima vacacional es el 25% del salario de los días tomados por defecto', function () {
    $prima = (new CalcularPrimaVacacional)->calcular(salarioDiario: 400, diasVacacionesTomados: 12);

    expect($prima)->toBe(round(400 * 12 * 0.25, 2));
});

test('prima vacacional acepta un porcentaje mayor al mínimo legal', function () {
    $prima = (new CalcularPrimaVacacional)->calcular(salarioDiario: 400, diasVacacionesTomados: 12, porcentaje: 0.30);

    expect($prima)->toBe(round(400 * 12 * 0.30, 2));
});
