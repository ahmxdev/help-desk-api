<?php

use App\Models\Ticket;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

test('customer can list their tickets', function () {
    $customer = User::factory()->customer()->create();
    Sanctum::actingAs($customer);

    $tickets = Ticket::factory()->count(2)->create([
        'customer_id' => $customer->id
    ]);

    $otherTicket = Ticket::factory()->create();

    $response = getJson('api/tickets');

    $response->assertOk();

    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id',
                'subject',

                'agent_name',

                'created_at',
            ],
        ],
    ]);

    $response->assertJsonPath('data.0.subject', $tickets[0]['subject']);
    $response->assertJsonPath('data.1.subject', $tickets[1]['subject']);
    $response->assertJsonMissing([
        'id' => $otherTicket->id
    ]);
});

test('agent can list their tickets', function () {
    $agent = User::factory()->agent()->create();
    Sanctum::actingAs($agent);

    $tickets = Ticket::factory()->count(2)->create([
        'agent_id' => $agent->id
    ]);

    $otherTicket = Ticket::factory()->create();

    $response = getJson('api/tickets');

    $response->assertOk();

    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id',
                'subject',
                'priority',
                'status',

                'agent' => [
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],

                'created_at',
            ],
        ],
    ]);

    $response->assertJsonPath('data.0.subject', $tickets[0]['subject']);
    $response->assertJsonPath('data.1.subject', $tickets[1]['subject']);
    $response->assertJsonMissing([
        'id' => $otherTicket->id
    ]);
});

test('admin can list all tickets', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $tickets = Ticket::factory()->count(3)->create();

    $response = getJson('api/tickets');

    $response->assertOk();

    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id',
                'subject',
                'priority',
                'status',

                'agent' => [
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],

                'created_at',
            ],
        ],
    ]);

    $response->assertJsonPath('data.0.subject', $tickets[0]['subject']);
    $response->assertJsonPath('data.1.subject', $tickets[1]['subject']);
    $response->assertJsonPath('data.2.subject', $tickets[2]['subject']);
});

test('guest cannot list any tickets', function () {
    $response = getJson('api/tickets');

    $response->assertUnauthorized();
});
