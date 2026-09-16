<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

test('guest can create an account', function () {
    Notification::fake();

    $data = [
        'name' => fake()->name(),
        'email' => fake()->email(),
        'password' => fake()->password(8),
        'password_confirmation' => ''
    ];
    $data['password_confirmation'] = $data['password'];


    $response = postJson('/api/register', $data);


    $response->assertCreated();

    assertDatabaseHas('users', [
        'name' => $data['name'],
        'email' => $data['email'],
    ]);

    $response->assertJsonStructure([
        'message',
        'user' => [
            'name',
            'email',
            'roles',
            'created_at',
            'updated_at',
        ]
    ]);
    $response->assertJson([
        'user' => [
            'name' => $data['name'],
            'email' => $data['email'],
        ]
    ]);

    $user = User::where('email', $data['email'])->first();
    expect(Hash::check($data['password'], $user->password))->toBeTrue();
    Notification::assertSentTo($user, VerifyEmail::class);
});

test('registration validation fails with invalid data', function ($data) {

    User::create([
        'name' => fake()->name(),
        'email' => 'used@used.com',
        'password' => fake()->password(8),
    ]);

    $response = postJson('/api/register', $data);

    $response->assertUnprocessable();
})->with([
    [[
        'name' => '',
        'email' => 'valid@valid.com',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]],

    [[
        'name' => 'Ahmad',
        'email' => 'abc',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]],

    [[
        'name' => 'Ahmad',
        'email' => 'used@used.com',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]],

    [[
        'name' => 'Ahmad',
        'email' => 'valid@valid.com',
        'password' => 'password123',
        'password_confirmation' => 'different_password'
    ]],

    [[
        'name' => 'Ahmad',
        'email' => 'valid@valid.com',
        'password' => 'short',
        'password_confirmation' => 'short'
    ]],
]);
