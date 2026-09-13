<?php

use App\Models\Ticket;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\patchJson;

test('admin can update any ticket status', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $ticket = Ticket::factory()->create();

    $response = patchJson("api/tickets/{$ticket->id}/status", [
        'status' => 'closed'
    ]);

    $response->assertOk();
    assertDatabaseHas('tickets', [
        'status' => 'closed'
    ]);
});

test('agent can update their ticket status', function () {
    $agent = User::factory()->agent()->create();
    Sanctum::actingAs($agent);

    $ticket = Ticket::factory()->create([
        'agent_id' => $agent->id
    ]);


    $response = patchJson("api/tickets/{$ticket->id}/status", [
        'status' => 'closed'
    ]);

    $response->assertOk();
    assertDatabaseHas('tickets', [
        'status' => 'closed'
    ]);
});

test('agent cannot update ticket status they do not own', function () {
    $agent = User::factory()->agent()->create();
    Sanctum::actingAs($agent);

    $ticket = Ticket::factory()->create();

    $response = patchJson("api/tickets/{$ticket->id}/status", [
        'status' => 'closed'
    ]);

    $response->assertForbidden();
    assertDatabaseMissing('tickets', [
        'status' => 'closed'
    ]);
});

test('customer cannot update ticket status they own', function () {
    $customer = User::factory()->customer()->create();
    Sanctum::actingAs($customer);

    $ticket = Ticket::factory()->create([
        'customer_id' => $customer->id
    ]);

    $response = patchJson("api/tickets/{$ticket->id}/status", [
        'status' => 'closed'
    ]);

    $response->assertForbidden();
});

test('guest cannot update any ticket status', function () {
    $ticket = Ticket::factory()->create();

    $response = patchJson("api/tickets/{$ticket->id}/status", [
        'status' => 'closed'
    ]);

    $response->assertUnauthorized();
});
