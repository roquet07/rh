<?php

use App\Models\User;

test('la raíz redirige a login si no hay sesión iniciada', function () {
    $response = $this->get(route('home'));

    $response->assertRedirect(route('login'));
});

test('la raíz redirige al dashboard si ya hay sesión iniciada', function () {
    $response = $this->actingAs(User::factory()->create())->get(route('home'));

    $response->assertRedirect(route('dashboard'));
});
