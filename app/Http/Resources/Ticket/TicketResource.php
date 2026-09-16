<?php

namespace App\Http\Resources\Ticket;

use App\Http\Resources\Message\MessageResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
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
            'priority' => $this->priority,
            'status' => $this->status,

            'customer' => new UserResource($this->customer),
            'agent' => $this->agent ? new UserResource($this->agent) : null,

            'messages' => MessageResource::collection($this->messages),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
