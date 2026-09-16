<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\patchJson;

test('admin can set agent role for a customer', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $customer = User::factory()->customer()->create();

    $response = patchJson("api/users/$customer->id/role", [
        'role' => 'agent'
    ]);

    $response->assertOk();
    expect($customer->hasRole('agent'))->ToBeTrue();
    expect($customer->hasRole('customer'))->toBeFalse();
});

test('admin can set customer role for an agent', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $agent = User::factory()->agent()->create();

    $response = patchJson("api/users/$agent->id/role", [
        'role' => 'customer'
    ]);

    $response->assertOk();
    expect($agent->hasRole('customer'))->ToBeTrue();
    expect($agent->hasRole('agent'))->toBeFalse();
});

test('agent cannot set role for a customer', function () {
    $agent = User::factory()->agent()->create();
    Sanctum::actingAs($agent);

    $customer = User::factory()->customer()->create();

    $response = patchJson("api/users/$customer->id/role", [
        'role' => 'agent'
    ]);

    $response->assertForbidden();
    expect($customer->hasRole('agent'))->ToBeFalse();
    expect($customer->hasRole('customer'))->toBeTrue();
});

test('customer cannot set role for an agent', function () {
    $customer = User::factory()->customer()->create();
    Sanctum::actingAs($customer);

    $agent = User::factory()->agent()->create();

    $response = patchJson("api/users/$customer->id/role", [
        'role' => 'customer'
    ]);

    $response->assertForbidden();
    expect($agent->hasRole('customer'))->ToBeFalse();
    expect($agent->hasRole('agent'))->toBeTrue();
});
