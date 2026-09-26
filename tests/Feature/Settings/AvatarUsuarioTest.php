<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('local');
    $this->user = User::factory()->create();
});

test('el usuario puede subir y comprimir su propia foto de perfil', function () {
    Livewire::actingAs($this->user)
        ->test('pages::settings.profile')
        ->set('avatar', UploadedFile::fake()->image('foto.jpg', 2000, 2000))
        ->call('subirAvatar')
        ->assertHasNoErrors();

    $this->user->refresh();

    expect($this->user->avatar_path)->not->toBeNull();
    Storage::disk('local')->assertExists($this->user->avatar_path);
});

test('el usuario puede quitar su foto de perfil', function () {
    Livewire::actingAs($this->user)
        ->test('pages::settings.profile')
        ->set('avatar', UploadedFile::fake()->image('foto.jpg'))
        ->call('subirAvatar');

    $path = $this->user->refresh()->avatar_path;

    Livewire::actingAs($this->user)
        ->test('pages::settings.profile')
        ->call('eliminarAvatar');

    expect($this->user->refresh()->avatar_path)->toBeNull();
    Storage::disk('local')->assertMissing($path);
});

test('la ruta del avatar exige autenticación', function () {
    $this->get(route('usuarios.foto', $this->user))->assertRedirect(route('login'));
});

test('la ruta del avatar responde 404 si el usuario no tiene foto', function () {
    $this->actingAs($this->user)->get(route('usuarios.foto', $this->user))->assertNotFound();
});

test('cualquier usuario autenticado puede ver el avatar de otro usuario', function () {
    Livewire::actingAs($this->user)
        ->test('pages::settings.profile')
        ->set('avatar', UploadedFile::fake()->image('foto.jpg'))
        ->call('subirAvatar');

    $otro = User::factory()->create();

    $this->actingAs($otro)->get(route('usuarios.foto', $this->user->refresh()))->assertOk();
});
