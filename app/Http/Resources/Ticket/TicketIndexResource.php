<?php

namespace App\Http\Resources\Ticket;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketIndexResource extends JsonResource
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
            'priority' => $this->priority,
            'status' => $this->status,

            'agent' => $this->agent ? new UserResource($this->agent) : null,

            'created_at' => $this->created_at,
        ];
    }
}
