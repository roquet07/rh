<?php

use App\Models\User;
use Database\Seeders\RolesPermissionsSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolesPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('Administrador');
});

test('un administrador puede crear un rol con permisos', function () {
    Livewire::actingAs($this->admin)
        ->test('pages::admin.roles')
        ->set('name', 'Auditor')
        ->set('permisosSeleccionados', ['empleados.ver', 'contratos.ver'])
        ->call('save')
        ->assertHasNoErrors();

    $rol = Role::query()->where('name', 'Auditor')->firstOrFail();

    expect($rol->permissions->pluck('name')->sort()->values()->all())->toBe(['contratos.ver', 'empleados.ver']);
});

test('un administrador puede editar los permisos de un rol existente', function () {
    $rol = Role::findOrCreate('Auditor');
    $rol->syncPermissions(['empleados.ver']);

    Livewire::actingAs($this->admin)
        ->test('pages::admin.roles')
        ->call('edit', $rol->id)
        ->set('permisosSeleccionados', ['contratos.ver', 'comisiones.ver'])
        ->call('save')
        ->assertHasNoErrors();

    expect($rol->refresh()->permissions->pluck('name')->sort()->values()->all())->toBe(['comisiones.ver', 'contratos.ver']);
});

test('el nombre del rol debe ser único', function () {
    Role::findOrCreate('Auditor');

    Livewire::actingAs($this->admin)
        ->test('pages::admin.roles')
        ->set('name', 'Auditor')
        ->call('save')
        ->assertHasErrors(['name']);
});

test('un administrador puede eliminar un rol y los usuarios se quedan sin rol', function () {
    $rol = Role::findOrCreate('Auditor');
    $usuario = User::factory()->create();
    $usuario->assignRole('Auditor');

    Livewire::actingAs($this->admin)
        ->test('pages::admin.roles')
        ->call('delete', $rol->id);

    expect(Role::query()->find($rol->id))->toBeNull()
        ->and($usuario->refresh()->roles)->toHaveCount(0);
});

test('el rol Administrador no se puede eliminar', function () {
    $rol = Role::query()->where('name', 'Administrador')->firstOrFail();

    Livewire::actingAs($this->admin)
        ->test('pages::admin.roles')
        ->call('delete', $rol->id);

    expect(Role::query()->find($rol->id))->not->toBeNull();
});

test('el rol Administrador no se puede renombrar', function () {
    $rol = Role::query()->where('name', 'Administrador')->firstOrFail();

    Livewire::actingAs($this->admin)
        ->test('pages::admin.roles')
        ->call('edit', $rol->id)
        ->set('name', 'SuperAdmin')
        ->call('save');

    expect($rol->refresh()->name)->toBe('Administrador');
});

test('editar el rol Administrador siempre conserva todos los permisos aunque se manden vacíos', function () {
    $rol = Role::query()->where('name', 'Administrador')->firstOrFail();
    $totalPermisos = Permission::query()->count();

    Livewire::actingAs($this->admin)
        ->test('pages::admin.roles')
        ->call('edit', $rol->id)
        ->set('permisosSeleccionados', [])
        ->call('save');

    expect($rol->refresh()->permissions()->count())->toBe($totalPermisos);
});

test('un usuario sin permiso roles.gestionar no accede al CRUD de roles', function () {
    $rh = User::factory()->create();
    $rh->assignRole('RH');

    $this->actingAs($rh)->get(route('admin.roles'))->assertForbidden();
});
