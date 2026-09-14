<?php

namespace App\Http\Resources\Ticket;

use App\Http\Resources\Message\MessageResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerTicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'description' => $this->description,

            'customer' => new UserResource($this->customer),
            'agent_name' => $this->agent ? $this->agent->name : null,

            'messages' => MessageResource::collection($this->messages),

            'created_at' => $this->created_at,
        ];
    }
}
