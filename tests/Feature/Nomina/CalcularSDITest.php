<?php

use App\Actions\Nomina\CalcularSDI;

test('SDI integra la parte proporcional de aguinaldo y prima vacacional', function () {
    $sdi = (new CalcularSDI)->calcular(salarioDiario: 300, diasVacaciones: 12);

    $factorEsperado = (365 + 15 + (12 * 0.25)) / 365;

    expect($sdi)->toBe(round(300 * $factorEsperado, 2));
});

test('SDI con cero días de vacaciones solo integra el aguinaldo', function () {
    $sdi = (new CalcularSDI)->calcular(salarioDiario: 200, diasVacaciones: 0);

    $factorEsperado = (365 + 15) / 365;

    expect($sdi)->toBe(round(200 * $factorEsperado, 2));
});
