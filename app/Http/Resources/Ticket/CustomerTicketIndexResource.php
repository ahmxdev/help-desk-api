<?php

namespace App\Http\Resources\Ticket;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerTicketIndexResource extends JsonResource
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

            'agent_name' => $this->agent ? $this->agent->name : null,

            'created_at' => $this->created_at,
        ];
    }
}
