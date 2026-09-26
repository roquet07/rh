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
});

test('la búsqueda filtra por nombre número o RFC', function () {
    $puesto = Puesto::factory()->create(['departamento_id' => Departamento::factory()->create()->id]);
    $juan = Empleado::factory()->create(['puesto_id' => $puesto->id, 'nombre_completo' => 'Juan', 'apellido_paterno' => 'Pérez']);
    $maria = Empleado::factory()->create(['puesto_id' => $puesto->id, 'nombre_completo' => 'María', 'apellido_paterno' => 'López']);

    Livewire::actingAs($this->rh)
        ->test('pages::empleados.index')
        ->set('buscar', 'Juan')
        ->assertSee($juan->numero_empleado)
        ->assertDontSee($maria->numero_empleado);
});

test('el filtro de estado baja solo muestra empleados dados de baja', function () {
    $puesto = Puesto::factory()->create(['departamento_id' => Departamento::factory()->create()->id]);
    $activo = Empleado::factory()->create(['puesto_id' => $puesto->id, 'estatus' => 'activo']);
    $baja = Empleado::factory()->create(['puesto_id' => $puesto->id, 'estatus' => 'baja']);

    Livewire::actingAs($this->rh)
        ->test('pages::empleados.index')
        ->set('estado', 'Baja')
        ->assertSee($baja->numero_empleado)
        ->assertDontSee($activo->numero_empleado);
});

test('el filtro de periodo de prueba usa el contrato vigente del empleado', function () {
    $puesto = Puesto::factory()->create(['departamento_id' => Departamento::factory()->create()->id]);
    $enPrueba = Empleado::factory()->create(['puesto_id' => $puesto->id, 'estatus' => 'activo']);
    $activo = Empleado::factory()->create(['puesto_id' => $puesto->id, 'estatus' => 'activo']);

    Contrato::query()->create([
        'empleado_id' => $enPrueba->id,
        'puesto_id' => $puesto->id,
        'tipo' => 'periodo_prueba',
        'fecha_inicio' => now()->toDateString(),
        'fecha_fin' => now()->addDays(30)->toDateString(),
        'salario_diario' => 400,
        'jornada' => 'diurna',
        'lugar_trabajo' => 'Oficina',
        'estatus' => 'borrador',
    ]);

    Livewire::actingAs($this->rh)
        ->test('pages::empleados.index')
        ->set('estado', 'Periodo de prueba')
        ->assertSee($enPrueba->numero_empleado)
        ->assertDontSee($activo->numero_empleado);
});

test('el filtro de documentación pendiente solo muestra empleados en esa etapa', function () {
    $puesto = Puesto::factory()->create(['departamento_id' => Departamento::factory()->create()->id]);
    $pendiente = Empleado::factory()->create(['puesto_id' => $puesto->id, 'estatus' => 'documentacion_pendiente']);
    $activo = Empleado::factory()->create(['puesto_id' => $puesto->id, 'estatus' => 'activo']);

    Livewire::actingAs($this->rh)
        ->test('pages::empleados.index')
        ->set('estado', 'Documentación pendiente')
        ->assertSee($pendiente->numero_empleado)
        ->assertDontSee($activo->numero_empleado);
});

test('el filtro de departamento limita los resultados', function () {
    $depA = Departamento::factory()->create();
    $depB = Departamento::factory()->create();
    $puestoA = Puesto::factory()->create(['departamento_id' => $depA->id]);
    $puestoB = Puesto::factory()->create(['departamento_id' => $depB->id]);
    $empleadoA = Empleado::factory()->create(['puesto_id' => $puestoA->id]);
    $empleadoB = Empleado::factory()->create(['puesto_id' => $puestoB->id]);

    Livewire::actingAs($this->rh)
        ->test('pages::empleados.index')
        ->set('departamento_id', (string) $depA->id)
        ->assertSee($empleadoA->numero_empleado)
        ->assertDontSee($empleadoB->numero_empleado);
});

test('exportar CSV respeta los filtros de búsqueda', function () {
    $puesto = Puesto::factory()->create(['departamento_id' => Departamento::factory()->create()->id]);
    $incluido = Empleado::factory()->create(['puesto_id' => $puesto->id, 'nombre_completo' => 'Roberto']);
    Empleado::factory()->create(['puesto_id' => $puesto->id, 'nombre_completo' => 'Distinto']);

    $response = $this->actingAs($this->rh)->get(route('empleados.exportar', ['buscar' => 'Roberto']));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    $contenido = $response->streamedContent();

    expect($contenido)->toContain($incluido->numero_empleado)
        ->and($contenido)->not->toContain('Distinto');
});
