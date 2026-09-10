<?php

use App\Models\Ticket;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;

test('customer can send a message', function () {
    $customer = User::factory()->customer()->create();
    Sanctum::actingAs($customer);

    $ticket = Ticket::factory()->create([
        'customer_id' => $customer->id
    ]);

    $data = [
        'text' => fake()->paragraph(),
        'ticket_id' => $ticket->id
    ];

    $response = postJson('api/messages', $data);

    $response->assertCreated();

    $response->assertJsonFragment([
        'message' => 'The message has been sent.'
    ]);

    assertDatabaseHas('messages', [
        'text' => $data['text']
    ]);
});

test('agent can send a message', function () {
    $agent = User::factory()->agent()->create();
    Sanctum::actingAs($agent);

    $ticket = Ticket::factory()->create([
        'agent_id' => $agent->id
    ]);

    $data = [
        'text' => fake()->paragraph(),
        'ticket_id' => $ticket->id
    ];

    $response = postJson('api/messages', $data);

    $response->assertCreated();

    $response->assertJsonFragment([
        'message' => 'The message has been sent.'
    ]);

    assertDatabaseHas('messages', [
        'text' => $data['text']
    ]);
});

test('guest cannot send a message', function () {
    $ticket = Ticket::factory()->create();

    $data = [
        'text' => fake()->paragraph(),
        'ticket_id' => $ticket->id
    ];

    $response = postJson('api/messages', $data);

    $response->assertUnauthorized();

    assertDatabaseMissing('messages', [
        'text' => $data['text']
    ]);
});
