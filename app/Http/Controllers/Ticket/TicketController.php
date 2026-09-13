<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\StoreTicketRequest;
use App\Http\Requests\Ticket\UpdateStatusRequest;
use App\Http\Resources\Ticket\TicketIndexResource;
use App\Http\Resources\Ticket\TicketResource;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $tickets = [];
        if ($user->hasRole('customer')) {
            $tickets = $user->customerTickets()
                ->with('customer', 'agent')
                ->get();
        } else if ($user->hasRole('agent')) {
            $tickets = $user->agentTickets()
                ->with('customer', 'agent')
                ->get();
        } else if ($user->hasRole('admin')) {
            $tickets = Ticket::all();
        } else {
            abort(403);
        }

        return TicketIndexResource::collection($tickets);
    }

    public function show(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        if ($ticket->customer_id !== $user->id && $ticket->agent_id !== $user->id && ! $user->hasRole('admin')) {
            abort(403);
        }

        return new TicketResource($ticket);
    }

    public function store(StoreTicketRequest $request)
    {
        $user = $request->user();

        $data = $request->validated();

        $user->customerTickets()->create($data);

        return response()->json([
            'message' => 'The ticket has been created.'
        ], 201);
    }

    public function updateStatus(UpdateStatusRequest $request, Ticket $ticket)
    {
        Gate::authorize('updateStatus', $ticket);

        $newStatus = $request->validated('status');

        if ($newStatus !== $ticket->status) {
            $ticket->update([
                'status' => $newStatus
            ]);
        }

        return response()->json([
            'message' => 'The status has been update.'
        ], 200);
    }
}
