<?php

use App\Models\Documento;
use App\Models\DocumentoTipo;
use App\Models\Empleado;
use App\Models\User;
use Database\Seeders\RolesPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesPermissionsSeeder::class);

    $this->rh = User::factory()->create();
    $this->rh->assignRole('RH');
});

test('RH puede crear un tipo de documento', function () {
    Livewire::actingAs($this->rh)
        ->test('pages::documento-tipos.index')
        ->set('nombre', 'INE')
        ->set('descripcion', 'Identificación oficial vigente')
        ->set('obligatorio', true)
        ->set('orden', '1')
        ->call('save')
        ->assertHasNoErrors();

    expect(DocumentoTipo::query()->where('nombre', 'INE')->exists())->toBeTrue();
});

test('RH puede editar un tipo de documento existente', function () {
    $tipo = DocumentoTipo::factory()->create(['nombre' => 'CURP']);

    Livewire::actingAs($this->rh)
        ->test('pages::documento-tipos.index')
        ->call('edit', $tipo->id)
        ->set('nombre', 'CURP actualizado')
        ->call('save')
        ->assertHasNoErrors();

    expect($tipo->refresh()->nombre)->toBe('CURP actualizado');
});

test('RH puede eliminar un tipo de documento sin documentos asociados', function () {
    $tipo = DocumentoTipo::factory()->create();

    Livewire::actingAs($this->rh)
        ->test('pages::documento-tipos.index')
        ->call('delete', $tipo->id);

    expect(DocumentoTipo::query()->find($tipo->id))->toBeNull();
});

test('no se puede eliminar un tipo de documento con documentos capturados', function () {
    $tipo = DocumentoTipo::factory()->create();
    Documento::factory()->create(['documento_tipo_id' => $tipo->id, 'empleado_id' => Empleado::factory()]);

    Livewire::actingAs($this->rh)
        ->test('pages::documento-tipos.index')
        ->call('delete', $tipo->id);

    expect(DocumentoTipo::query()->find($tipo->id))->not->toBeNull();
});

test('un usuario sin permiso documento-tipos.ver no puede acceder al catálogo', function () {
    $empleadoUser = User::factory()->create();
    $empleadoUser->assignRole('Empleado');

    $this->actingAs($empleadoUser)->get(route('documento-tipos.index'))->assertForbidden();
});
