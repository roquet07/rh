<?php

use App\Actions\Empleados\SubirDocumentoEmpleado;
use App\Models\Departamento;
use App\Models\Documento;
use App\Models\DocumentoTipo;
use App\Models\Empleado;
use App\Models\Puesto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');

    $puesto = Puesto::factory()->create(['departamento_id' => Departamento::factory()->create()->id]);
    $this->empleado = Empleado::factory()->create(['puesto_id' => $puesto->id]);
    $this->documentoTipo = DocumentoTipo::factory()->create();
    $this->usuario = User::factory()->create();
});

test('comprime una imagen jpg reduciendo su tamaño', function () {
    $archivo = UploadedFile::fake()->image('foto.jpg', 3000, 2000);
    $tamanoOriginal = $archivo->getSize();

    $documento = (new SubirDocumentoEmpleado)($this->empleado, $this->documentoTipo, $archivo, $this->usuario);

    Storage::disk('local')->assertExists($documento->path);
    expect($documento->mime_type)->toBe('image/jpeg')
        ->and($documento->tamano_bytes)->toBeLessThan($tamanoOriginal)
        ->and(Storage::disk('local')->size($documento->path))->toBe($documento->tamano_bytes);
});

test('guarda un pdf sin modificarlo', function () {
    $archivo = UploadedFile::fake()->create('documento.pdf', 200, 'application/pdf');
    $tamanoOriginal = $archivo->getSize();

    $documento = (new SubirDocumentoEmpleado)($this->empleado, $this->documentoTipo, $archivo, $this->usuario);

    Storage::disk('local')->assertExists($documento->path);
    expect($documento->mime_type)->toBe('application/pdf')
        ->and($documento->tamano_bytes)->toBe($tamanoOriginal);
});

test('subir un documento del mismo tipo reemplaza el anterior', function () {
    $accion = new SubirDocumentoEmpleado;

    $primero = $accion($this->empleado, $this->documentoTipo, UploadedFile::fake()->create('a.pdf', 50, 'application/pdf'), $this->usuario);
    $pathAnterior = $primero->path;

    $segundo = $accion($this->empleado, $this->documentoTipo, UploadedFile::fake()->create('b.pdf', 50, 'application/pdf'), $this->usuario);

    expect($segundo->id)->toBe($primero->id)
        ->and(Documento::query()->where('empleado_id', $this->empleado->id)->where('documento_tipo_id', $this->documentoTipo->id)->count())->toBe(1);

    Storage::disk('local')->assertMissing($pathAnterior);
    Storage::disk('local')->assertExists($segundo->path);
});
