<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\postJson;

test('authenticated user can change their password', function () {

    $user = User::factory()->create([
        'password' => 'old-password'
    ]);
    Sanctum::actingAs($user);

    $data = [
        'current_password' => 'old-password',
        'password' => '12345678',
        'password_confirmation' => '12345678'
    ];

    $response = postJson('/api/change-password', $data);

    $response->assertOk();
    $response->assertJson([
        'message' => 'Password changed successfully.'
    ]);

    $user->refresh();
    expect(Hash::check($data['password'], $user->password))->toBeTrue();
    expect(Hash::check('old-password', $user->password))->toBeFalse();
});

test('rejects an incorrect current password', function () {

    $user = User::factory()->create([
        'password' => 'old-password'
    ]);
    Sanctum::actingAs($user);

    $data = [
        'current_password' => 'wrong-password',
        'password' => '12345678',
        'password_confirmation' => '12345678'
    ];

    $response = postJson('/api/change-password', $data);

    $response->assertUnprocessable();
    $response->assertJson([
        'message' => 'Current password is incorrect.'
    ]);

    $user->refresh();
    expect(Hash::check('old-password', $user->password))->toBeTrue();
});

test('guest cannot change password', function () {

    $user = User::factory()->create([
        'password' => 'old-password'
    ]);

    $data = [
        'current_password' => 'old-password',
        'password' => '12345678',
        'password_confirmation' => '12345678'
    ];

    $response = postJson('/api/change-password', $data);

    $response->assertUnauthorized();

    $user->refresh();
    expect(Hash::check('old-password', $user->password))->toBeTrue();
});

test('rejects invalid password change input', function ($data) {
    $user = User::factory()->create([
        'password' => 'old-password',
    ]);

    Sanctum::actingAs($user);

    $response = postJson('/api/change-password', $data);

    $response->assertUnprocessable();
})->with([
    [[
        'current_password' => '',
        'password' => '12345678',
        'password_confirmation' => '12345678',
    ]],
    [[
        'current_password' => 'old-password',
        'password' => '',
        'password_confirmation' => '',
    ]],
    [[
        'current_password' => 'old-password',
        'password' => '12345678',
        'password_confirmation' => 'different-password',
    ]],
]);
