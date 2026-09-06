<?php

use App\Models\Role;
use App\Providers\RouteServiceProvider;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register with allowed role', function () {
    $role = Role::firstOrCreate(['role' => 'admin gudang']);

    $response = $this->post('/register', [
        'name' => 'New Staff Gudang',
        'username' => 'staffgudang1',
        'email' => 'staff@dishub.go.id',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role_id' => $role->id,
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(RouteServiceProvider::HOME);
});

test('users cannot register as superadmin', function () {
    $superadminRole = Role::firstOrCreate(['role' => 'superadmin']);

    $response = $this->post('/register', [
        'name' => 'Fake Super Admin',
        'username' => 'fakesuperadmin',
        'email' => 'fake@dishub.go.id',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role_id' => $superadminRole->id,
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('role_id');
});
