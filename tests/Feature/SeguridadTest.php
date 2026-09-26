<?php

use Illuminate\Support\Facades\Route;

test('la ruta pública de Laravel para servir el disco local no está registrada', function () {
    expect(Route::has('storage.local'))->toBeFalse();

    $this->get('/storage/empleados/documentos/1/algo.pdf')->assertNotFound();
});

test('las páginas incluyen la meta etiqueta para no indexarse en buscadores', function () {
    $this->get(route('login'))->assertOk()->assertSee('name="robots" content="noindex, nofollow, noarchive"', false);
});
