<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Models\Queue;
use App\Services\TicketBookingService;
use App\Services\QueueFlowService;

class TicketController extends Controller
{
    use ApiResponse;
    public function book(Request $request): JsonResponse
    {
        $bookingService = new TicketBookingService();

        $data = $request->validate([
            'queue_id' => 'required|exists:queues,id',
        ]);
        $user = auth()->user();
        $queue = Queue::findOrFail($data['queue_id']);
        $ticket = $bookingService->book($user, $queue);
        return $this->apiResponse($ticket, 'Ticket booked successfully', 201);
    }

    public function cancel(Ticket $ticket): JsonResponse
    {
        $queueFlowService = new QueueFlowService();
        $newTicket = $queueFlowService->cancel($ticket);
        return $this->apiResponse($newTicket, 'Ticket cancelled successfully', 200);
    }

    public function startHandling(Ticket $ticket): JsonResponse
    {
        $queueFlowService = new QueueFlowService();
        $newTicket = $queueFlowService->startHandling($ticket);
        return $this->apiResponse($newTicket, 'Ticket started handling successfully', 200);
    }

    public function complete(Ticket $ticket): JsonResponse
    {
        $queueFlowService = new QueueFlowService();
        $newTicket = $queueFlowService->complete($ticket);
        return $this->apiResponse($newTicket, "Ticket Completed!", 200);
    }

    public function noShow(Ticket $ticket): JsonResponse
    {
        $queueFlowService = new QueueFlowService();
        $newTicket = $queueFlowService->markNoShow($ticket);
        if ($newTicket->status === 'no_show') {
            return $this->apiResponse($newTicket, "Ticket Moved to No Show! Your turn has been moved to the end of the queue.", 200);
        } else {
            return $this->apiResponse($newTicket, "Ticket has been Canceled!", 200);
        }
    }

    public function myActiveTickets(Request $request): JsonResponse
    {

        $tickets = Ticket::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('status', [
                'pending',
                'handling',
                'no_show'
            ])
            ->with([
                'queue',
                'queue.service',
                'queue.service.business'
            ])
            ->latest()
            ->get();
        return $this->apiResponse($tickets, 'Tickets fetched successfully', 200);
    }

    public function myHistory(Request $request): JsonResponse
    {

        $tickets = Ticket::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('status', [
                'canceled',
                'completed',
                'expired',
            ])
            ->with([
                'queue',
                'queue.service',
                'queue.service.business'
            ])
            ->latest()
            ->get();
        return $this->apiResponse($tickets, 'Tickets fetched successfully', 200);
    }

    public function show(Ticket $ticket): JsonResponse
    {
        $ticketNumber = $ticket->number;
        $queue = $ticket->queue;
        $service = $queue->service;
        $ticketsAhead = $queue
            ->tickets()
            ->whereIn('status', [
                'pending',
                'no_show'
            ])
            ->where('number', '<', $ticketNumber);
        $data = array(
            'ticket' => $ticket,
            'service' => $service,
            'totalTickets' => $ticketsAhead,
        );
        return $this->apiResponse($data, 'Ticket fetched successfully', 200);
    }
    //tickets on business
    public function getBusinessTickets(Request $request, Business $business)
    {
        $this->authorize('view', $business);


        $tickets = Ticket::whereHas('queue.service.business', function ($query) use ($business) {
            $query->where('id', $business->id);
        })->get();

        return $this->apiResponse($tickets, 'Tickets fetched successfully', 200);
    }
}
