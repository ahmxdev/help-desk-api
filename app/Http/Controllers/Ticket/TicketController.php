<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\StoreTicketRequest;
use App\Http\Resources\Ticket\TicketResource;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $tickets = $user->customerTickets()
            ->with('customer', 'agent')
            ->get();

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
