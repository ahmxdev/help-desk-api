<?php

use App\Models\Ticket;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\patchJson;

test('admin can assign an agent for any ticket', function () {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $ticket = Ticket::factory()->create([
        'agent_id' => null
    ]);

    $agent = User::factory()->agent()->create();

    $response = patchJson("api/tickets/{$ticket->id}/agent", [
        'agent_id' => $agent->id
    ]);

    $response->assertOk();
    assertDatabaseHas('tickets', [
        'agent_id' => $agent->id
    ]);
});

test('agent cannot assign an agent for their ticket', function () {
    $agent = User::factory()->agent()->create();
    Sanctum::actingAs($agent);

    $ticket = Ticket::factory()->create([
        'agent_id' => $agent->id
    ]);

    $anotherAgent = User::factory()->agent()->create();

    $response = patchJson("api/tickets/{$ticket->id}/agent", [
        'agent_id' => $anotherAgent->id
    ]);

    $response->assertForbidden();
    assertDatabaseMissing('tickets', [
        'agent_id' => $anotherAgent->id
    ]);
});

test('customer cannot assign an agent for their ticket', function () {
    $customer = User::factory()->customer()->create();
    Sanctum::actingAs($customer);

    $ticket = Ticket::factory()->create([
        'agent_id' => null,
        'customer_id' => $customer->id
    ]);

    $agent = User::factory()->agent()->create();

    $response = patchJson("api/tickets/{$ticket->id}/agent", [
        'agent_id' => $agent->id
    ]);

    $response->assertForbidden();
    assertDatabaseMissing('tickets', [
        'agent_id' => $agent->id
    ]);
});

test('guest cannot assign an agent for any ticket', function () {
    $ticket = Ticket::factory()->create();

    $response = patchJson("api/tickets/{$ticket->id}/priority", [
        'agent_id' => '9999'
    ]);

    $response->assertUnauthorized();
});
