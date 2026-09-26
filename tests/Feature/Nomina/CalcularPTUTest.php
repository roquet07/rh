<?php

use App\Actions\Nomina\CalcularPTU;

test('el reparto de PTU suma de vuelta la utilidad repartible', function () {
    $resultado = (new CalcularPTU)->calcular(10000.00, [
        1 => ['dias_trabajados' => 180, 'salario_devengado' => 20000],
        2 => ['dias_trabajados' => 90, 'salario_devengado' => 10000],
    ]);

    expect(array_sum($resultado))->toEqualWithDelta(10000.00, 0.02)
        ->and($resultado[1])->toBeGreaterThan($resultado[2]);
});

test('PTU con un solo empleado le asigna toda la utilidad repartible', function () {
    $resultado = (new CalcularPTU)->calcular(5000.00, [
        1 => ['dias_trabajados' => 365, 'salario_devengado' => 50000],
    ]);

    expect($resultado[1])->toBe(5000.00);
});

test('PTU sin empleados retorna arreglo vacío', function () {
    expect((new CalcularPTU)->calcular(1000.00, []))->toBe([]);
});
