<?php

use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\Puesto;
use App\Models\SolicitudVacante;
use App\Models\User;
use Database\Seeders\RolesPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesPermissionsSeeder::class);
});

test('crear empleado desde una solicitud aprobada conserva la trazabilidad', function () {
    $rh = User::factory()->create();
    $rh->assignRole('RH');

    $puesto = Puesto::factory()->create(['departamento_id' => Departamento::factory()->create()->id]);

    $solicitud = SolicitudVacante::query()->create([
        'folio' => 'SOL-2026-0003',
        'solicitante_user_id' => $rh->id,
        'departamento_id' => $puesto->departamento_id,
        'puesto_id' => $puesto->id,
        'salario_propuesto' => 450,
        'numero_plazas' => 1,
        'justificacion' => 'Reemplazo de baja.',
        'urgencia' => 'alta',
        'estatus' => 'aprobada',
        'revisado_por_user_id' => $rh->id,
        'fecha_revision' => now(),
    ]);

    Livewire::actingAs($rh)
        ->test('pages::empleados.create', ['solicitud' => $solicitud->id])
        ->assertSet('puesto_id', $puesto->id)
        ->assertSet('salario_diario', (string) $solicitud->salario_propuesto)
        ->set('nombre_completo', 'Juan')
        ->set('apellido_paterno', 'Pérez')
        ->set('rfc', 'PEJU800101ABC')
        ->set('curp', 'PEJU800101HDFRRN01')
        ->set('fecha_nacimiento', '1980-01-01')
        ->set('domicilio', 'Av. Siempre Viva 123')
        ->set('colonia', 'Centro')
        ->set('codigo_postal', '06600')
        ->set('estado_direccion', 'Ciudad de México')
        ->set('fecha_ingreso', now()->toDateString())
        ->set('jornada', 'diurna')
        ->set('tipo_contrato', 'indeterminado')
        ->call('save')
        ->assertHasNoErrors();

    $empleado = Empleado::query()->where('rfc', 'PEJU800101ABC')->firstOrFail();

    expect($empleado->solicitud_vacante_id)->toBe($solicitud->id)
        ->and($empleado->estatus)->toBe('documentacion_pendiente');
});
