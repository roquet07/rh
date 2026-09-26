<?php

use App\Actions\Nomina\CalcularDiasVacaciones;
use Database\Seeders\TablaVacacionesAntiguedadSeeder;

beforeEach(function () {
    $this->seed(TablaVacacionesAntiguedadSeeder::class);
});

test('días de vacaciones siguen la tabla de antigüedad de la reforma 2023', function (int $anios, int $diasEsperados) {
    $dias = (new CalcularDiasVacaciones)->calcular($anios);

    expect($dias)->toBe($diasEsperados);
})->with([
    [1, 12],
    [2, 14],
    [3, 16],
    [4, 18],
    [5, 20],
    [6, 22],
    [10, 22],
    [11, 24],
    [15, 24],
    [16, 26],
]);

test('antigüedad menor a un año no otorga vacaciones', function () {
    expect((new CalcularDiasVacaciones)->calcular(0))->toBe(0);
});
