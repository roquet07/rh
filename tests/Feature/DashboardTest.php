<?php

use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\Puesto;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard shows real active headcount', function () {
    $puesto = Puesto::factory()->create(['departamento_id' => Departamento::factory()->create()->id]);
    Empleado::factory()->count(3)->create(['puesto_id' => $puesto->id, 'estatus' => 'activo']);
    Empleado::factory()->create(['puesto_id' => $puesto->id, 'estatus' => 'baja']);

    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Hola, '.$user->name)
        ->assertSeeText('3');
});
