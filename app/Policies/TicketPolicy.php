<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function updateTicket(User $user, Ticket $ticket): bool
    {
        return $user->hasRole('admin')
            || (
                $user->hasRole('agent')
                && $ticket->agent_id === $user->id
            );
    }
}
