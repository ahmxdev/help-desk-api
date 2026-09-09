<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

test('user can get password reset link', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = postJson('/api/forgot-password', [
        'email' => $user->email
    ]);

    $response->assertOk();
    $response->assertJson([
        'message' => 'Reset password link sent successfully.'
    ]);
    Notification::assertSentTo($user, ResetPassword::class);
    assertDatabaseHas('password_reset_tokens', [
        'email' => $user->email
    ]);
});

test('does not send reset link for unknown email', function () {
    Notification::fake();

    $response = postJson('/api/forgot-password', [
        'email' => 'email@e.com'
    ]);

    $response->assertOk();
    $response->assertJson([
        'message' => 'Reset password link sent successfully.'
    ]);
    Notification::assertNothingSent();
});

test('forgot password validation fails with invalid data', function ($email) {
    $response = postJson('/api/forgot-password', [
        'email' => $email
    ]);

    $response->assertUnprocessable();
})->with([
    '',
    'abc',
    str_repeat('a', 250) . '@test.com'
]);

test('user cannot request greater than 3 forgot password links per minute', function () {
    $user = User::factory()->create();

    $data = [
        'email' => $user->email
    ];

    for ($i = 1; $i <= 3; $i++) {
        postJson('/api/forgot-password', $data);
    }

    $response = postJson('/api/forgot-password', $data);

    $response->assertTooManyRequests();
});

test('reset password notification contains the correct reset url', function () {
    Notification::fake();

    $user = User::factory()->create();

    postJson('/api/forgot-password', [
        'email' => $user->email,
    ]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $url = $notification->toMail($user)->actionUrl;

        return str_contains($url, env('FRONTEND_URL') . '/reset-password')
            && str_contains($url, 'token=')
            && str_contains($url, 'email=' . urlencode($user->email));
    });
});
