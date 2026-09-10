<?php

namespace App\Http\Controllers\Message;

use App\Http\Controllers\Controller;
use App\Http\Requests\Message\SendMessageRequest;
use App\Models\Ticket;

class MessageController extends Controller
{
    public function send(SendMessageRequest $request)
    {
        $sender = $request->user();
        $ticket = Ticket::findOrFail($request->ticket_id);
        $messageText = $request->text;

        if ($ticket->customer_id !== $sender->id && $ticket->agent_id !== $sender->id) {
            abort(403);
        }

        $ticket->messages()->create([
            'sender_id' => $sender->id,
            'text' => $messageText
        ]);

        return response()->json([
            'message' => 'The message has been sent.'
        ], 201);
    }
}
