<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;


// Tests for 'email/verification-notification'
test('authenticated unverified user can request verification email', function () {
    Notification::fake();

    $user = User::create([
        'name' => fake()->name(),
        'email' => fake()->email(),
        'password' => fake()->password(8),
    ]);

    Sanctum::actingAs($user);

    $response = postJson('/api/email/verification-notification');

    $response->assertOk();
    $response->assertJson([
        'message' => 'Verification link sent successfully.'
    ]);
    Notification::assertSentTo($user, VerifyEmail::class);
});

test('authenticated verified user cannot request verification email', function () {
    Notification::fake();

    $user = User::factory()->create(); // Verified user

    Sanctum::actingAs($user);

    $response = postJson('/api/email/verification-notification');

    $response->assertConflict();
    $response->assertJson([
        'message' => 'Email already verified.'
    ]);
    Notification::assertNothingSent();
});

test('guest cannot request verification email', function () {
    $response = postJson('/api/email/verification-notification');

    $response->assertUnauthorized();
});

test('authenticated unverified user cannot request greater than 3 verification email', function () {
    $user = User::create([
        'name' => fake()->name(),
        'email' => fake()->email(),
        'password' => fake()->password(8),
    ]);

    Sanctum::actingAs($user);

    for ($i = 1; $i <= 3; $i++) {
        postJson('/api/email/verification-notification');
    }

    $response = postJson('/api/email/verification-notification');

    $response->assertTooManyRequests();
});


// Tests for 'email/verify/{id}/{hash}'
test('unverified user can verify their email using verification url', function () {
    $user = User::create([
        'name' => fake()->name(),
        'email' => fake()->email(),
        'password' => fake()->password(8),
    ]);

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]
    );

    $response = getJson($url);

    $response->assertOk();
    $response->assertJson([
        'message' => 'Email verified successfully.'
    ]);

    $user->refresh();
    expect($user->email_verified_at)->not->toBeNull();
});

test('non-existing user cannot verify their email', function () {
    $user = User::create([
        'name' => fake()->name(),
        'email' => fake()->email(),
        'password' => fake()->password(8),
    ]);

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        [
            'id' => 9999,
            'hash' => sha1($user->getEmailForVerification()),
        ]
    );

    $response = getJson($url);

    $response->assertNotFound();
    $response->assertJson([
        'message' => 'User not found.'
    ]);
});

test('unverified user cannot verify their email using wrong hash', function () {
    $user = User::create([
        'name' => fake()->name(),
        'email' => fake()->email(),
        'password' => fake()->password(8),
    ]);

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        [
            'id' => $user->id,
            'hash' => 'INVALID_HASH',
        ]
    );

    $response = getJson($url);

    $response->assertForbidden();
    $response->assertJson([
        'message' => 'Invalid verification link.'
    ]);

    $user->refresh();
    expect($user->email_verified_at)->toBeNull();
});

test('verified user cannot verify their email again', function () {
    $user = User::factory()->create(); // Verified user

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]
    );

    $response = getJson($url);

    $response->assertOk();
    $response->assertJson([
        'message' => 'Email already verified.'
    ]);

    expect($user->email_verified_at)->not->toBeNull();
});

test('expired verification url is rejected', function () {
    $user = User::create([
        'name' => fake()->name(),
        'email' => fake()->email(),
        'password' => fake()->password(8),
    ]);

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->subMinutes(1),
        [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]
    );

    $response = getJson($url);

    $response->assertForbidden();

    $user->refresh();
    expect($user->email_verified_at)->toBeNull();
});
