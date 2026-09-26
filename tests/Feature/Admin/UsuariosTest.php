<?php

use App\Models\User;
use Database\Seeders\RolesPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('Administrador');
});

test('un administrador puede crear un usuario con rol', function () {
    Livewire::actingAs($this->admin)
        ->test('pages::admin.usuarios')
        ->set('name', 'Nuevo Usuario')
        ->set('email', 'nuevo@rh.test')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('rol', 'RH')
        ->call('save')
        ->assertHasNoErrors();

    $usuario = User::query()->where('email', 'nuevo@rh.test')->firstOrFail();

    expect($usuario->name)->toBe('Nuevo Usuario')
        ->and($usuario->hasRole('RH'))->toBeTrue();
});

test('crear un usuario exige contraseña', function () {
    Livewire::actingAs($this->admin)
        ->test('pages::admin.usuarios')
        ->set('name', 'Sin Password')
        ->set('email', 'sinpass@rh.test')
        ->call('save')
        ->assertHasErrors(['password']);
});

test('un administrador puede editar un usuario sin cambiar la contraseña', function () {
    $usuario = User::factory()->create(['name' => 'Original', 'email' => 'original@rh.test']);
    $usuario->assignRole('Gerente');
    $hashOriginal = $usuario->password;

    Livewire::actingAs($this->admin)
        ->test('pages::admin.usuarios')
        ->call('edit', $usuario->id)
        ->set('name', 'Editado')
        ->set('rol', 'RH')
        ->call('save')
        ->assertHasNoErrors();

    $usuario->refresh();

    expect($usuario->name)->toBe('Editado')
        ->and($usuario->password)->toBe($hashOriginal)
        ->and($usuario->hasRole('RH'))->toBeTrue()
        ->and($usuario->hasRole('Gerente'))->toBeFalse();
});

test('un administrador puede cambiar la contraseña de un usuario al editar', function () {
    $usuario = User::factory()->create();
    $hashOriginal = $usuario->password;

    Livewire::actingAs($this->admin)
        ->test('pages::admin.usuarios')
        ->call('edit', $usuario->id)
        ->set('password', 'nueva-password-123')
        ->set('password_confirmation', 'nueva-password-123')
        ->call('save')
        ->assertHasNoErrors();

    expect($usuario->refresh()->password)->not->toBe($hashOriginal);
});

test('el correo debe ser único al crear o editar', function () {
    $existente = User::factory()->create(['email' => 'ocupado@rh.test']);

    Livewire::actingAs($this->admin)
        ->test('pages::admin.usuarios')
        ->set('name', 'Duplicado')
        ->set('email', 'ocupado@rh.test')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->call('save')
        ->assertHasErrors(['email']);
});

test('un administrador puede eliminar a otro usuario', function () {
    $usuario = User::factory()->create();

    Livewire::actingAs($this->admin)
        ->test('pages::admin.usuarios')
        ->call('delete', $usuario->id);

    expect(User::query()->find($usuario->id))->toBeNull();
});

test('un administrador no puede eliminar su propia cuenta', function () {
    Livewire::actingAs($this->admin)
        ->test('pages::admin.usuarios')
        ->call('delete', $this->admin->id);

    expect(User::query()->find($this->admin->id))->not->toBeNull();
});

test('un usuario sin permiso usuarios.gestionar no accede al CRUD de usuarios', function () {
    $rh = User::factory()->create();
    $rh->assignRole('RH');

    $this->actingAs($rh)->get(route('admin.usuarios'))->assertForbidden();
});
