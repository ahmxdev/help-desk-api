<?php

use App\Models\Message;
use App\Models\Ticket;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

test('customer can show their ticket', function () {
    $customer = User::factory()->customer()->create();
    Sanctum::actingAs($customer);

    $ticket = Ticket::factory()->create([
        'customer_id' => $customer->id
    ]);
    Message::factory()->count(3)->create([
        'ticket_id' => $ticket->id,
        'sender_id' => $customer->id,
    ]);

    $otherTicket = Ticket::factory()->create();

    $response = getJson("api/tickets/{$ticket->id}");

    $response->assertOk();

    $response->assertJsonStructure([
        'data' => [
            'id',
            'subject',
            'description',
            'priority',
            'status',

            'customer' => [
                'name',
                'email',
                'created_at',
                'updated_at',
            ],

            'agent' => [
                'name',
                'email',
                'created_at',
                'updated_at',
            ],

            'messages' => [
                '*' => [
                    'id',
                    'text',
                    'sender_id',
                    'created_at'
                ]
            ],

            'created_at',
            'updated_at',
        ],
    ]);


    $response->assertJsonPath('data.subject', $ticket->subject);
    $response->assertJsonMissing([
        'subject' => $otherTicket->subject
    ]);
});

test('agent can show their ticket', function () {
    $agent = User::factory()->agent()->create();
    Sanctum::actingAs($agent);

    $ticket = Ticket::factory()->create([
        'customer_id' => $agent->id
    ]);

    Message::factory()->count(3)->create([
        'ticket_id' => $ticket->id,
        'sender_id' => $agent->id,
    ]);

    $otherTicket = Ticket::factory()->create();

    $response = getJson("api/tickets/{$ticket->id}");

    $response->assertOk();

    $response->assertJsonStructure([
        'data' => [
            'id',
            'subject',
            'description',
            'priority',
            'status',

            'customer' => [
                'name',
                'email',
                'created_at',
                'updated_at',
            ],

            'agent' => [
                'name',
                'email',
                'created_at',
                'updated_at',
            ],

            'messages' => [
                '*' => [
                    'id',
                    'text',
                    'sender_id',
                    'created_at'
                ]
            ],

            'created_at',
            'updated_at',
        ],
    ]);


    $response->assertJsonPath('data.subject', $ticket->subject);
    $response->assertJsonMissing([
        'subject' => $otherTicket->subject
    ]);
});

test('customer cannot show a ticket they do not own', function () {
    $customer = User::factory()->customer()->create();
    Sanctum::actingAs($customer);

    $ticket = Ticket::factory()->create();

    $response = getJson("api/tickets/{$ticket->id}");

    $response->assertForbidden();
});

test('agent cannot show a ticket they do not own', function () {
    $agent = User::factory()->agent()->create();
    Sanctum::actingAs($agent);

    $ticket = Ticket::factory()->create();

    $response = getJson("api/tickets/{$ticket->id}");

    $response->assertForbidden();
});

test('admin can show any ticket', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $ticket = Ticket::factory()->create();

    $response = getJson("api/tickets/{$ticket->id}");

    $response->assertOk();

    $response->assertJsonStructure([
        'data' => [
            'id',
            'subject',
            'description',
            'priority',
            'status',

            'customer' => [
                'name',
                'email',
                'created_at',
                'updated_at',
            ],

            'agent' => [
                'name',
                'email',
                'created_at',
                'updated_at',
            ],

            'messages' => [
                '*' => [
                    'id',
                    'text',
                    'sender_id',
                    'created_at'
                ]
            ],

            'created_at',
            'updated_at',
        ],
    ]);


    $response->assertJsonPath('data.subject', $ticket->subject);
});

test('guest cannot show any tickets', function () {
    $response = getJson('api/tickets/99999');

    $response->assertUnauthorized();
});
