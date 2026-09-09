<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

use function Pest\Laravel\postJson;
use function Pest\Laravel\travel;

test('user can reset password with a valid token', function () {

    $user = User::factory()->create([
        'password' => 'old-password'
    ]);

    $token = Password::createToken($user);

    $data = [
        'token' => $token,
        'email' => $user->email,
        'password' => '12345678',
        'password_confirmation' => '12345678'
    ];

    $response = postJson('/api/reset-password', $data);

    $response->assertOk();
    $response->assertJson([
        'message' => 'Password reset successfully.'
    ]);

    $user->refresh();
    expect(Hash::check($data['password'], $user->password))->toBeTrue();
    expect(Hash::check('old-password', $user->password))->toBeFalse();
});

test('rejects an invalid password reset token', function () {

    $user = User::factory()->create([
        'password' => 'old-password'
    ]);

    $data = [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => '12345678',
        'password_confirmation' => '12345678'
    ];

    $response = postJson('/api/reset-password', $data);

    $response->assertBadRequest();
    $response->assertJson([
        'message' => 'Password reset failed.'
    ]);

    $user->refresh();
    expect(Hash::check($data['password'], $user->password))->toBeFalse();
    expect(Hash::check('old-password', $user->password))->toBeTrue();
});

test('rejects an expired password reset token', function () {

    $user = User::factory()->create([
        'password' => 'old-password'
    ]);

    $token = Password::createToken($user);
    travel(61)->minutes();

    $data = [
        'token' => $token,
        'email' => $user->email,
        'password' => '12345678',
        'password_confirmation' => '12345678'
    ];

    $response = postJson('/api/reset-password', $data);

    $response->assertBadRequest();
    $response->assertJson([
        'message' => 'Password reset failed.'
    ]);

    $user->refresh();
    expect(Hash::check($data['password'], $user->password))->toBeFalse();
    expect(Hash::check('old-password', $user->password))->toBeTrue();
});

test('rejects invalid password reset input', function ($data) {
    $response = postJson('/api/reset-password', $data);

    $response->assertUnprocessable();
})->with([
    [[
        'token' => '',
        'email' => 'user@example.com',
        'password' => '12345678',
        'password_confirmation' => '12345678',
    ]],
    [[
        'token' => 'token',
        'email' => '',
        'password' => '12345678',
        'password_confirmation' => '12345678',
    ]],
    [[
        'token' => 'token',
        'email' => 'invalid-email',
        'password' => '12345678',
        'password_confirmation' => '12345678',
    ]],
    [[
        'token' => 'token',
        'email' => 'user@example.com',
        'password' => '',
        'password_confirmation' => '',
    ]],
    [[
        'token' => 'token',
        'email' => 'user@example.com',
        'password' => '12345678',
        'password_confirmation' => 'different-password',
    ]],
]);
