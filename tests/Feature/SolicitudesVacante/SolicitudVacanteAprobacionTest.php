<?php

use App\Models\Departamento;
use App\Models\SolicitudVacante;
use App\Models\User;
use Database\Seeders\RolesPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesPermissionsSeeder::class);
});

test('RH puede aprobar una solicitud pendiente', function () {
    $rh = User::factory()->create();
    $rh->assignRole('RH');

    $gerente = User::factory()->create();
    $gerente->assignRole('Gerente');

    $solicitud = SolicitudVacante::query()->create([
        'folio' => 'SOL-2026-0001',
        'solicitante_user_id' => $gerente->id,
        'departamento_id' => Departamento::factory()->create()->id,
        'puesto_propuesto' => 'Vendedor',
        'salario_propuesto' => 400,
        'numero_plazas' => 1,
        'justificacion' => 'Crecimiento del equipo de ventas.',
        'urgencia' => 'media',
        'estatus' => 'pendiente',
    ]);

    Livewire::actingAs($rh)
        ->test('pages::solicitudes.show', ['solicitud' => $solicitud])
        ->call('aprobar')
        ->assertHasNoErrors();

    expect($solicitud->refresh())
        ->estatus->toBe('aprobada')
        ->revisado_por_user_id->toBe($rh->id);
});

test('un gerente sin permiso de aprobación no puede aprobar solicitudes', function () {
    $gerente = User::factory()->create();
    $gerente->assignRole('Gerente');

    $solicitud = SolicitudVacante::query()->create([
        'folio' => 'SOL-2026-0002',
        'solicitante_user_id' => $gerente->id,
        'departamento_id' => Departamento::factory()->create()->id,
        'puesto_propuesto' => 'Vendedor',
        'salario_propuesto' => 400,
        'numero_plazas' => 1,
        'justificacion' => 'Crecimiento del equipo de ventas.',
        'urgencia' => 'media',
        'estatus' => 'pendiente',
    ]);

    Livewire::actingAs($gerente)
        ->test('pages::solicitudes.show', ['solicitud' => $solicitud])
        ->call('aprobar')
        ->assertForbidden();

    expect($solicitud->refresh()->estatus)->toBe('pendiente');
});
