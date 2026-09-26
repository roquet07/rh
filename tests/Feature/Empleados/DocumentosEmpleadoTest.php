<?php

use App\Models\Departamento;
use App\Models\Documento;
use App\Models\DocumentoTipo;
use App\Models\Empleado;
use App\Models\Puesto;
use App\Models\User;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('local');
    $this->seed(RolesPermissionsSeeder::class);

    $this->rh = User::factory()->create();
    $this->rh->assignRole('RH');

    $this->admin = User::factory()->create();
    $this->admin->assignRole('Administrador');

    $puesto = Puesto::factory()->create(['departamento_id' => Departamento::factory()->create()->id]);
    $this->empleado = Empleado::factory()->create(['puesto_id' => $puesto->id, 'estatus' => 'documentacion_pendiente']);
    $this->tipo = DocumentoTipo::factory()->create(['obligatorio' => true]);
});

test('RH puede subir un documento y el indicador avanza', function () {
    Livewire::actingAs($this->rh)
        ->test('pages::empleados.show', ['empleado' => $this->empleado])
        ->set("archivos.{$this->tipo->id}", UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'))
        ->call('subirDocumento', $this->tipo->id)
        ->assertHasNoErrors();

    expect(Documento::query()->where('empleado_id', $this->empleado->id)->where('documento_tipo_id', $this->tipo->id)->exists())->toBeTrue();

    expect($this->empleado->progresoDocumentacion())->toBe(['completados' => 1, 'total' => 1]);
});

test('RH puede eliminar un documento cargado', function () {
    $documento = Documento::factory()->create(['empleado_id' => $this->empleado->id, 'documento_tipo_id' => $this->tipo->id]);
    Storage::disk('local')->put($documento->path, 'contenido');

    Livewire::actingAs($this->rh)
        ->test('pages::empleados.show', ['empleado' => $this->empleado])
        ->call('eliminarDocumento', $documento->id);

    expect(Documento::query()->find($documento->id))->toBeNull();
    Storage::disk('local')->assertMissing($documento->path);
});

test('un usuario sin permiso de captura no ve los controles de subir documentos', function () {
    $gerente = User::factory()->create();
    $gerente->assignRole('Gerente');

    Livewire::actingAs($gerente)
        ->test('pages::empleados.show', ['empleado' => $this->empleado])
        ->assertDontSee(__('Subir'));
});

test('no se puede aprobar la documentación si faltan documentos obligatorios', function () {
    Livewire::actingAs($this->admin)
        ->test('pages::empleados.show', ['empleado' => $this->empleado])
        ->call('aprobarDocumentacion');

    expect($this->empleado->refresh()->estatus)->toBe('documentacion_pendiente');
});

test('aprobar la documentación completa activa al empleado', function () {
    Documento::factory()->create(['empleado_id' => $this->empleado->id, 'documento_tipo_id' => $this->tipo->id]);

    Livewire::actingAs($this->admin)
        ->test('pages::empleados.show', ['empleado' => $this->empleado])
        ->call('aprobarDocumentacion');

    $this->empleado->refresh();

    expect($this->empleado->estatus)->toBe('activo')
        ->and($this->empleado->documentacion_estatus)->toBe('aprobada')
        ->and($this->empleado->documentacion_revisado_por_user_id)->toBe($this->admin->id);
});

test('rechazar la documentación conserva al empleado en estatus pendiente con el motivo', function () {
    Livewire::actingAs($this->admin)
        ->test('pages::empleados.show', ['empleado' => $this->empleado])
        ->set('comentarioRechazo', 'Falta comprobante de domicilio.')
        ->call('rechazarDocumentacion')
        ->assertHasNoErrors();

    $this->empleado->refresh();

    expect($this->empleado->estatus)->toBe('documentacion_pendiente')
        ->and($this->empleado->documentacion_estatus)->toBe('rechazada')
        ->and($this->empleado->documentacion_comentario_revision)->toBe('Falta comprobante de domicilio.');
});

test('RH no ve los controles de aprobar o rechazar documentación', function () {
    Livewire::actingAs($this->rh)
        ->test('pages::empleados.show', ['empleado' => $this->empleado])
        ->assertDontSee(__('Aprobar documentación'));
});
