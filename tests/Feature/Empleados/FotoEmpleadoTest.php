<?php

use App\Models\Departamento;
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

    $puesto = Puesto::factory()->create(['departamento_id' => Departamento::factory()->create()->id]);
    $this->empleado = Empleado::factory()->create(['puesto_id' => $puesto->id]);
});

test('RH puede subir y comprimir la foto de un empleado', function () {
    $archivo = UploadedFile::fake()->image('foto.jpg', 2000, 2000);

    Livewire::actingAs($this->rh)
        ->test('pages::empleados.show', ['empleado' => $this->empleado])
        ->set('foto', $archivo)
        ->call('subirFoto')
        ->assertHasNoErrors();

    $this->empleado->refresh();

    expect($this->empleado->foto_path)->not->toBeNull();
    Storage::disk('local')->assertExists($this->empleado->foto_path);
});

test('subir una nueva foto reemplaza el contenido de la anterior', function () {
    Livewire::actingAs($this->rh)
        ->test('pages::empleados.show', ['empleado' => $this->empleado])
        ->set('foto', UploadedFile::fake()->image('a.jpg', 100, 100))
        ->call('subirFoto');

    $pathAnterior = $this->empleado->refresh()->foto_path;
    $contenidoAnterior = Storage::disk('local')->get($pathAnterior);

    Livewire::actingAs($this->rh)
        ->test('pages::empleados.show', ['empleado' => $this->empleado])
        ->set('foto', UploadedFile::fake()->image('b.jpg', 900, 900))
        ->call('subirFoto');

    $this->empleado->refresh();

    expect($this->empleado->foto_path)->toBe($pathAnterior)
        ->and(Storage::disk('local')->get($pathAnterior))->not->toBe($contenidoAnterior);
});

test('RH puede quitar la foto de un empleado', function () {
    Livewire::actingAs($this->rh)
        ->test('pages::empleados.show', ['empleado' => $this->empleado])
        ->set('foto', UploadedFile::fake()->image('foto.jpg'))
        ->call('subirFoto');

    $path = $this->empleado->refresh()->foto_path;

    Livewire::actingAs($this->rh)
        ->test('pages::empleados.show', ['empleado' => $this->empleado])
        ->call('eliminarFoto');

    expect($this->empleado->refresh()->foto_path)->toBeNull();
    Storage::disk('local')->assertMissing($path);
});

test('la ruta de la foto exige autenticación', function () {
    $this->get(route('empleados.foto', $this->empleado))->assertRedirect(route('login'));
});

test('la ruta de la foto exige el permiso empleados.ver', function () {
    $sinPermiso = User::factory()->create();
    $sinPermiso->assignRole('Empleado');

    $this->actingAs($sinPermiso)->get(route('empleados.foto', $this->empleado))->assertForbidden();
});

test('la ruta de la foto responde 404 si el empleado no tiene foto', function () {
    $this->actingAs($this->rh)->get(route('empleados.foto', $this->empleado))->assertNotFound();
});

test('la ruta de la foto sirve el archivo a un usuario autorizado', function () {
    Livewire::actingAs($this->rh)
        ->test('pages::empleados.show', ['empleado' => $this->empleado])
        ->set('foto', UploadedFile::fake()->image('foto.jpg'))
        ->call('subirFoto');

    $this->actingAs($this->rh)->get(route('empleados.foto', $this->empleado->refresh()))->assertOk();
});
