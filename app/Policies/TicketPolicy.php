<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
 
    public function viewAny(User $user): bool
    {
        return true; 
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return
            $ticket->user_id === $user->id

            || (
                $user->isEmployee()
                && $ticket->queue->user_id === $user->id
            )

            || (
                $user->isManager()
                && $ticket->queue->service->business->user_id === $user->id
            )

            || $user->isAdmin();
    }



    public function create(User $user): bool
    {
        return $user->isUser();
    }

    public function cancel(User $user, Ticket $ticket): bool
    {
        return
            $user->isUser()
            && $ticket->user_id === $user->id
            && $ticket->isPending();
    }


  
    public function startHandling(User $user, Ticket $ticket): bool
    {
        return $this->canManageQueue($user, $ticket)
            && $ticket->isPending();
    }

    public function complete(User $user, Ticket $ticket): bool
    {
        return $this->canManageQueue($user, $ticket)
            && $ticket->isHandling();
    }

    public function markNoShow(User $user, Ticket $ticket): bool
    {
        return $this->canManageQueue($user, $ticket)
            && $ticket->isPending();
    }

    public function moveToNoShowQueue(User $user, Ticket $ticket): bool
    {
        return $this->canManageQueue($user, $ticket)
            && $ticket->isPending();
    }

    public function skip(User $user, Ticket $ticket): bool
    {
        return $this->canManageQueue($user, $ticket)
            && $ticket->isPending();
    }


   

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin();
    }

    private function canManageQueue(User $user, Ticket $ticket): bool
    {
        return

            (
                $user->isEmployee()
                && $ticket->queue->user_id === $user->id
            )

            ||

            (
                $user->isManager()
                && $ticket->queue->service->business->user_id === $user->id
            )

            ||

            $user->isAdmin();
    }
}