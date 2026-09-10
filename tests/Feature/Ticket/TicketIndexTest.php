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

    $response = getJson('api/tickets');

    $response->assertOk();

    $response->assertJsonStructure([
        'data' => [
            '*' => [
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

                'created_at',
                'updated_at',
            ],
        ],
    ]);

    $response->assertJsonPath('data.0.subject', $tickets[0]['subject']);
    $response->assertJsonPath('data.1.subject', $tickets[1]['subject']);
});

test('guest cannot list any tickets', function () {
    $response = getJson('api/tickets');

    $response->assertUnauthorized();
});
