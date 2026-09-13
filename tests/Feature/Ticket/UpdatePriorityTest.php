<?php

use App\Models\Ticket;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\patchJson;

test('admin can update any ticket priority', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $ticket = Ticket::factory()->create();

    $response = patchJson("api/tickets/{$ticket->id}/priority", [
        'priority' => 'high'
    ]);

    $response->assertOk();
    assertDatabaseHas('tickets', [
        'priority' => 'high'
    ]);
});

test('agent can update their ticket priority', function () {
    $agent = User::factory()->agent()->create();
    Sanctum::actingAs($agent);

    $ticket = Ticket::factory()->create([
        'agent_id' => $agent->id
    ]);


    $response = patchJson("api/tickets/{$ticket->id}/priority", [
        'priority' => 'high'
    ]);

    $response->assertOk();
    assertDatabaseHas('tickets', [
        'priority' => 'high'
    ]);
});

test('agent cannot update ticket priority they do not own', function () {
    $agent = User::factory()->agent()->create();
    Sanctum::actingAs($agent);

    $ticket = Ticket::factory()->create();

    $response = patchJson("api/tickets/{$ticket->id}/priority", [
        'priority' => 'high'
    ]);

    $response->assertForbidden();
    assertDatabaseMissing('tickets', [
        'priority' => 'high'
    ]);
});

test('customer cannot update ticket priority they own', function () {
    $customer = User::factory()->customer()->create();
    Sanctum::actingAs($customer);

    $ticket = Ticket::factory()->create([
        'customer_id' => $customer->id
    ]);

    $response = patchJson("api/tickets/{$ticket->id}/priority", [
        'priority' => 'high'
    ]);

    $response->assertForbidden();
});

test('guest cannot update any ticket priority', function () {
    $ticket = Ticket::factory()->create();

    $response = patchJson("api/tickets/{$ticket->id}/priority", [
        'priority' => 'high'
    ]);

    $response->assertUnauthorized();
});
