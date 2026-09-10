<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\StoreTicketRequest;
use App\Http\Resources\Ticket\TicketResource;
use App\Models\Ticket;
use Illuminate\Http\Request;

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

        return TicketResource::collection($tickets);
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
}
