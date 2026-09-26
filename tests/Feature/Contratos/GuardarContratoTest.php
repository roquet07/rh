<?php

use App\Models\Contrato;
use App\Models\Empleado;
use App\Models\User;
use App\Support\ContratoRules;
use Database\Seeders\RolesPermissionsSeeder;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesPermissionsSeeder::class);
});

function formularioContrato(): Testable
{
    $user = User::factory()->create();
    $user->assignRole('Administrador');
    $empleado = Empleado::factory()->create();
    $datos = Contrato::factory()->make(['empleado_id' => $empleado->id, 'puesto_id' => $empleado->puesto_id])->attributesToArray();
    $datos['fecha_inicio'] = '2026-10-01';
    $datos['fecha_celebracion'] = '2026-09-26';
    $datos = array_intersect_key($datos, ContratoRules::rules());

    return Livewire::actingAs($user)->test('pages::contratos.create', ['empleado' => $empleado])->set($datos);
}

test('calcula la vigencia inclusiva en meses sin desbordar febrero', function (string $inicio, string $meses, string $fin) {
    formularioContrato()->set('tipo', 'determinado')->set('fecha_inicio', $inicio)->set('duracion_meses', $meses)->call('save')->assertHasNoErrors();

    $this->assertDatabaseHas('contratos', ['tipo' => 'determinado', 'fecha_inicio' => $inicio.' 00:00:00', 'fecha_fin' => $fin.' 00:00:00', 'duracion_meses' => (int) $meses]);
})->with([
    'tres meses' => ['2026-10-01', '3', '2026-12-31'],
    'fin de mes' => ['2026-01-31', '1', '2026-02-27'],
    'bisiesto' => ['2028-01-31', '1', '2028-02-28'],
]);

test('cambiar a indeterminado descarta la duración anterior', function () {
    formularioContrato()->set('tipo', 'determinado')->set('duracion_meses', '3')->set('tipo', 'indeterminado')->call('save')->assertHasNoErrors();

    $this->assertDatabaseHas('contratos', ['tipo' => 'indeterminado', 'fecha_fin' => null, 'duracion_meses' => null]);
});

test('rechaza duración inválida y modalidades no admitidas', function (string $campo, string $valor) {
    formularioContrato()->set('tipo', 'determinado')->set($campo, $valor)->call('save')->assertHasErrors([$campo]);
    $this->assertDatabaseCount('contratos', 0);
})->with([
    'sin meses' => ['duracion_meses', ''],
    'cero meses' => ['duracion_meses', '0'],
    'negativo' => ['duracion_meses', '-1'],
    'fracción' => ['duracion_meses', '1.5'],
    'fuera de rango' => ['duracion_meses', '121'],
    'tipo anterior' => ['tipo', 'periodo_prueba'],
    'fecha inválida' => ['fecha_inicio', '2026-02-30'],
]);

test('requiere que los beneficiarios sumen cien por ciento', function () {
    formularioContrato()->set('beneficiarios.0.porcentaje', 60)->call('save')
        ->assertHasErrors(['beneficiarios'])->assertSee('Los porcentajes de los beneficiarios deben sumar 100%.');
    $this->assertDatabaseCount('contratos', 0);
});

test('rechaza jornada incompatible y salario inconsistente', function (array $datos, string $campo) {
    formularioContrato()->set($datos)->call('save')->assertHasErrors([$campo]);
    $this->assertDatabaseCount('contratos', 0);
})->with([
    'nocturna' => [['jornada' => 'nocturna', 'horas_semanales' => 48], 'horas_semanales'],
    'mixta' => [['jornada' => 'mixta', 'horas_semanales' => 48], 'horas_semanales'],
    'salario cero' => [['salario_mensual' => 0], 'salario_mensual'],
]);

test('permite completar un contrato anterior e invalida el PDF anterior', function () {
    $user = User::factory()->create();
    $user->assignRole('Administrador');
    $contrato = Contrato::factory()->determinado()->create(['estatus' => 'generado', 'pdf_path' => 'contratos/anterior.pdf']);

    Livewire::actingAs($user)->test('pages::contratos.create', ['contrato' => $contrato])->set('tipo', 'indeterminado')->call('save')->assertHasNoErrors();

    expect($contrato->refresh()->fecha_fin)->toBeNull();
    expect($contrato->pdf_path)->toBeNull();
    expect($contrato->estatus)->toBe('borrador');
});

test('no permite editar un contrato firmado', function () {
    $user = User::factory()->create();
    $user->assignRole('Administrador');
    $contrato = Contrato::factory()->create(['estatus' => 'firmado']);
    $this->actingAs($user)->get(route('contratos.edit', $contrato))->assertForbidden();
});

test('protege las acciones de contratos contra usuarios que solo pueden consultar', function (string $accion) {
    $user = User::factory()->create();
    $user->givePermissionTo('contratos.ver');
    $contrato = Contrato::factory()->create();
    Livewire::actingAs($user)->test('pages::contratos.show', ['contrato' => $contrato])->call($accion)->assertForbidden();
    expect($contrato->refresh()->estatus)->toBe('borrador');
})->with(['generarPdf', 'marcarFirmado']);

test('protege la creación y la edición de contratos por permiso', function () {
    $user = User::factory()->create();
    $empleado = Empleado::factory()->create();
    $this->actingAs($user)->get(route('contratos.create', $empleado))->assertForbidden();
    $this->assertDatabaseCount('contratos', 0);
});

test('no registra firma sin un PDF generado', function () {
    $user = User::factory()->create();
    $user->assignRole('Administrador');
    $contrato = Contrato::factory()->create();
    Livewire::actingAs($user)->test('pages::contratos.show', ['contrato' => $contrato])->call('marcarFirmado')->assertHasErrors(['pdf']);
    expect($contrato->refresh()->estatus)->toBe('borrador');
});
