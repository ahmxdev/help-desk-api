<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;

test('customer can create a ticket', function () {
    $customer = User::factory()->customer()->create();
    Sanctum::actingAs($customer);

    $data = [
        'subject' => fake()->sentence(),
        'description' => fake()->paragraph()
    ];

    $response = postJson('api/tickets', $data);

    $response->assertCreated();

    $response->assertJsonFragment([
        'message' => 'The ticket has been created.'
    ]);

    assertDatabaseHas('tickets', [
        'subject' => $data['subject']
    ]);
});

test('other roles cannot create a ticket', function () {
    $agent = User::factory()->agent()->create();
    Sanctum::actingAs($agent);

    $data = [
        'subject' => fake()->sentence(),
        'description' => fake()->paragraph()
    ];

    $response = postJson('api/tickets', $data);

    $response->assertForbidden();

    assertDatabaseMissing('tickets', [
        'subject' => $data['subject']
    ]);
});

test('guest cannot create a ticket', function () {
    $response = postJson('api/tickets', []);

    $response->assertUnauthorized();
});
