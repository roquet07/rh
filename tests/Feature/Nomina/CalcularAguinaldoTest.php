<?php

use App\Actions\Nomina\CalcularAguinaldo;
use Illuminate\Support\Carbon;

test('empleado con año completo recibe el aguinaldo íntegro', function () {
    $aguinaldo = (new CalcularAguinaldo)->calcular(
        salarioDiario: 300,
        fechaIngreso: Carbon::parse('2020-01-01'),
        fechaCorte: Carbon::parse('2024-12-31'),
    );

    expect($aguinaldo)->toBe(round(300 * 15, 2));
});

test('empleado contratado a mitad de año recibe aguinaldo proporcional', function () {
    $fechaIngreso = Carbon::parse('2024-07-01');
    $fechaCorte = Carbon::parse('2024-12-31');

    $aguinaldo = (new CalcularAguinaldo)->calcular(
        salarioDiario: 300,
        fechaIngreso: $fechaIngreso,
        fechaCorte: $fechaCorte,
    );

    $diasLaborados = $fechaIngreso->diffInDays($fechaCorte) + 1;
    $esperado = round(($diasLaborados / 365) * 15 * 300, 2);

    expect($aguinaldo)->toBe($esperado);
});
