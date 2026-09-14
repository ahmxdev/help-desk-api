<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\AssignAgentRequest;
use App\Http\Requests\Ticket\StoreTicketRequest;
use App\Http\Requests\Ticket\UpdatePriorityRequest;
use App\Http\Requests\Ticket\UpdateStatusRequest;
use App\Http\Resources\Ticket\CustomerTicketIndexResource;
use App\Http\Resources\Ticket\CustomerTicketResource;
use App\Http\Resources\Ticket\TicketIndexResource;
use App\Http\Resources\Ticket\TicketResource;
use App\Models\Ticket;
use App\Models\User;
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

            return CustomerTicketIndexResource::collection($tickets);
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

        if ($ticket->customer_id == $user->id) {
            return new CustomerTicketResource($ticket);
        }
        if ($ticket->agent_id == $user->id || $user->hasRole('admin')) {
            return new TicketResource($ticket);
        }

        abort(403);
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
        Gate::authorize('updateTicket', $ticket);

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

    public function updatePriority(UpdatePriorityRequest $request, Ticket $ticket)
    {
        Gate::authorize('updateTicket', $ticket);

        $newPriority = $request->validated('priority');

        if ($newPriority !== $ticket->priority) {
            $ticket->update([
                'priority' => $newPriority
            ]);
        }

        return response()->json([
            'message' => 'The priority has been update.'
        ], 200);
    }

    public function assignAgent(AssignAgentRequest $request, Ticket $ticket)
    {
        $agent = User::find($request->validated('agent_id'));

        if (! $agent->hasRole('agent')) {
            abort(403);
        }

        $ticket->update([
            'agent_id' => $agent->id
        ]);

        return response()->json([
            'message' => 'Agent has been assigned.'
        ], 200);
    }
}
