<?php

use App\Models\User;
use Database\Seeders\RolesPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesPermissionsSeeder::class);
});

test('un usuario sin permiso empleados.ver no puede listar empleados', function () {
    $empleadoUser = User::factory()->create();
    $empleadoUser->assignRole('Empleado');

    $this->actingAs($empleadoUser)->get(route('empleados.index'))->assertForbidden();
});

test('RH puede listar empleados', function () {
    $rh = User::factory()->create();
    $rh->assignRole('RH');

    $this->actingAs($rh)->get(route('empleados.index'))->assertOk();
});

test('un gerente con solo empleados.ver no puede crear empleados', function () {
    $gerente = User::factory()->create();
    $gerente->assignRole('Gerente');

    $this->actingAs($gerente)->get(route('empleados.create'))->assertForbidden();
});

test('solo un administrador puede llegar a la configuración fiscal', function () {
    $rh = User::factory()->create();
    $rh->assignRole('RH');

    $admin = User::factory()->create();
    $admin->assignRole('Administrador');

    $this->actingAs($rh)->get(route('config-fiscal.isr'))->assertForbidden();
    $this->actingAs($admin)->get(route('config-fiscal.isr'))->assertOk();
});

test('mi expediente devuelve 403 si el usuario no tiene un empleado vinculado', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('mi-expediente'))->assertForbidden();
});
