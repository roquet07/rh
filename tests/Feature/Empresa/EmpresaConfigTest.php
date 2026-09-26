<?php

use App\Models\EmpresaConfig;
use App\Models\User;
use Database\Seeders\RolesPermissionsSeeder;
use Livewire\Livewire;

test('guarda y recupera los datos legales y de privacidad de la empresa', function () {
    $this->seed(RolesPermissionsSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('Administrador');
    $datos = EmpresaConfig::factory()->make()->attributesToArray();

    Livewire::actingAs($user)->test('pages::empresa.config')->set($datos)->call('save')->assertHasNoErrors();
    $this->assertDatabaseHas('empresa_config', $datos);
    Livewire::test('pages::empresa.config')->assertSet('escritura_constitutiva', $datos['escritura_constitutiva'])->assertSet('correo_privacidad', $datos['correo_privacidad']);
});

test('impide modificar la configuración de empresa sin permiso', function () {
    $this->seed(RolesPermissionsSeeder::class);
    Livewire::actingAs(User::factory()->create())->test('pages::empresa.config')->assertForbidden();
    $this->assertDatabaseCount('empresa_config', 0);
});

test('rechaza correos y direcciones de privacidad inválidos', function () {
    $this->seed(RolesPermissionsSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('Administrador');
    Livewire::actingAs($user)->test('pages::empresa.config')->set(EmpresaConfig::factory()->make()->attributesToArray())
        ->set('correo_privacidad', 'no-es-correo')->set('url_aviso_privacidad', 'javascript:alert(1)')->call('save')->assertHasErrors(['correo_privacidad', 'url_aviso_privacidad']);
    $this->assertDatabaseCount('empresa_config', 0);
});
