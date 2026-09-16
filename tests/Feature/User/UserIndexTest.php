<?php

use App\Models\Role;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

it('allows admin to list users with their roles', function () {
    $admin = User::factory()->create();
    $adminRole = Role::where('name', 'admin')->first();

    $admin->roles()->attach($adminRole);

    $customer = User::factory()->create([
        'name' => 'Ahmed',
        'email' => 'ahmed@example.com',
    ]);

    $customerRole = Role::where('name', 'customer')->first();
    $customer->roles()->attach($customerRole);

    Sanctum::actingAs($admin);

    getJson('/api/users')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'name',
                    'email',
                    'roles',
                ],
            ],
        ])
        ->assertJsonFragment([
            'name' => 'Ahmed',
            'email' => 'ahmed@example.com',
        ]);
});

it('forbids non-admin users from listing users', function () {
    $customer = User::factory()->create();
    $customerRole = Role::where('name', 'customer')->first();

    $customer->roles()->attach($customerRole);

    Sanctum::actingAs($customer);

    getJson('/api/users')
        ->assertForbidden();
});

it('does not allow guests to list users', function () {
    getJson('/api/users')
        ->assertUnauthorized();
});
