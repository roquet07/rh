<?php

use App\Models\Contrato;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\Puesto;
use App\Models\User;
use Database\Seeders\RolesPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesPermissionsSeeder::class);

    $this->rh = User::factory()->create();
    $this->rh->assignRole('RH');

    $this->puesto = Puesto::factory()->create(['departamento_id' => Departamento::factory()->create()->id]);
});

function datosValidosEmpleado(int $puestoId): array
{
    return [
        'nombre_completo' => 'Juan',
        'apellido_paterno' => 'Pérez',
        'fecha_nacimiento' => '1990-01-01',
        'rfc' => 'PEJU900101AB1',
        'curp' => 'PEJU900101HDFRRN01',
        'domicilio' => 'Av. Siempre Viva 123',
        'colonia' => 'Centro',
        'codigo_postal' => '06600',
        'estado_direccion' => 'Ciudad de México',
        'puesto_id' => $puestoId,
        'fecha_ingreso' => now()->toDateString(),
        'jornada' => 'diurna',
        'salario_diario' => '400',
        'tipo_contrato' => 'indeterminado',
    ];
}

test('un RFC con formato inválido es rechazado', function () {
    $componente = Livewire::actingAs($this->rh)->test('pages::empleados.create');

    foreach (datosValidosEmpleado($this->puesto->id) as $campo => $valor) {
        $componente->set($campo, $valor);
    }

    $componente->set('rfc', 'INVALIDO')
        ->call('save')
        ->assertHasErrors(['rfc']);
});

test('un CURP con formato inválido es rechazado', function () {
    $componente = Livewire::actingAs($this->rh)->test('pages::empleados.create');

    foreach (datosValidosEmpleado($this->puesto->id) as $campo => $valor) {
        $componente->set($campo, $valor);
    }

    $componente->set('curp', 'INVALIDO')
        ->call('save')
        ->assertHasErrors(['curp']);
});

test('un envío completo y válido crea el empleado y su contrato inicial', function () {
    $componente = Livewire::actingAs($this->rh)->test('pages::empleados.create');

    foreach (datosValidosEmpleado($this->puesto->id) as $campo => $valor) {
        $componente->set($campo, $valor);
    }

    $componente->call('save')->assertHasNoErrors();

    $empleado = Empleado::query()->where('rfc', 'PEJU900101AB1')->firstOrFail();

    expect($empleado->apellido_paterno)->toBe('Pérez')
        ->and($empleado->colonia)->toBe('Centro')
        ->and($empleado->estatus)->toBe('documentacion_pendiente');

    $contrato = Contrato::query()->where('empleado_id', $empleado->id)->firstOrFail();

    expect($contrato->tipo)->toBe('indeterminado')
        ->and($contrato->estatus)->toBe('borrador');
});
