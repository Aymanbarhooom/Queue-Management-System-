<?php

namespace App\Services;

use App\Models\Queue;
use App\Models\Ticket;
use App\Models\ServiceSession;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserStatistic;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class QueueFlowService
{
    public function startHandling(Ticket $ticket): Ticket
    {
        return DB::transaction(function () use ($ticket) {
            $ticket = Ticket::query()
                ->with([
                    'queue',
                    'queue.tickets',

                ])
                ->lockForUpdate()
                ->findOrFail($ticket->id);

            $user = $ticket->user;



            $queue = Queue::query()
                ->lockForUpdate()
                ->findOrFail($ticket->queue_id);
            $userStatistic = $this->getUserStatistic($queue, $user);

            /*
            -------------------------------------------------
            Validate Ticket Status
            -------------------------------------------------
            */
            if (
                !in_array($ticket->status, [
                    'pending',
                    'no_show'
                ])
            ) {
                throw new Exception(
                    'Ticket cannot start handling'
                );
            }



            $nextRunnableTicket = $queue
                ->tickets()
                ->whereIn('status', [
                    'pending',
                    'no_show'
                ])
                ->orderBy('number')
                ->first();


            if (
                !$nextRunnableTicket ||
                $nextRunnableTicket->id !== $ticket->id
            ) {
                throw new Exception(
                    'This is not the next ticket in queue'
                );
            }



            if ($ticket->status === 'pending') {

                $userStatistic->increment(
                    'total_on_time'
                );
            }

            if ($ticket->status === 'no_show') {

                $userStatistic->increment(
                    'total_no_show_present'
                );
            }



            $ticket->update([
                'status' => 'handling'
            ]);



            ServiceSession::create([
                'ticket_id' => $ticket->id,

                'user_statistics_id' =>
                $userStatistic->id,

                'start_time' => now()
            ]);


            /*
            -------------------------------------------------
            Notify Next 3 Users
            -------------------------------------------------
            */
            $nextTickets = $queue
                ->tickets()
                ->whereIn('status', [
                    'pending',
                    'no_show'
                ])
                ->where('number', '>', $ticket->number)
                ->orderBy('number')
                ->take(3)
                ->get();


            foreach ($nextTickets as $nextTicket) {

                Notification::create([
                    'user_id' => $nextTicket->user_id,

                    'type' => 'queue_near',

                    'title' => 'Your turn is near',

                    'body' =>
                    'Please prepare, your turn is approaching.'
                ]);
            }


            /*
            -------------------------------------------------
            Return Fresh Ticket
            -------------------------------------------------
            */
            return $ticket->fresh([
                'queue',

            ]);
        });
    }

    public function complete(Ticket $ticket): Ticket
    {
        return DB::transaction(function () use ($ticket) {
            $ticket = Ticket::query()
                ->with([
                    'queue',
                    'user',
                ])
                ->lockForUpdate()
                ->findOrFail($ticket->id);

            $queue = Queue::query()
                ->lockForUpdate()
                ->findOrFail($ticket->queue_id);

            $user = $ticket->user;

            $userStatistic = $this->getUserStatistic($queue, $user);

            if ($ticket->status !== 'handling') {
                throw new Exception(
                    'Only handling tickets can be completed'
                );
            }


            $serviceSession = ServiceSession::query()
                ->where('ticket_id', $ticket->id)
                ->whereNull('end_time')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (!$serviceSession) {
                throw new Exception(
                    'No active service session found for this ticket'
                );
            }

            $endTime = now();
            $startTime = Carbon::parse($serviceSession->start_time); 

            $duration = max(
                1,
                (int) $startTime->diffInMinutes($endTime)
            );

            $serviceSession->update([
                'end_time' => $endTime,
                'duration' => $duration,
            ]);

            /*
            -------------------------------------------------
            Update Ticket Status
            -------------------------------------------------
            */
            $ticket->update([
                'status' => 'completed', 
            ]);

            if ($userStatistic) {
                $avgDuration = (float) (
                    $userStatistic
                    ->serviceSessions()
                    ->whereNotNull('duration')
                    ->avg('duration') ?? $duration
                );

                $userStatistic->update([
                    'session_avg_duration' => $userStatistic->get_avg_duration()
                ]);
            }


            Notification::create([
                'user_id' => $ticket->user_id,
                'type' => 'session_completed',
                'title' => 'Session completed',
                'body' => 'Your service session has been completed successfully.'
            ]);

            return $ticket->fresh([
                'queue',

            ]);
        });
    }

    public function cancel(Ticket $ticket): Ticket
    {
        return DB::transaction(function () use ($ticket) {
            $ticket = Ticket::query()
                ->with([
                    'queue',
                    'user',
                ])
                ->lockForUpdate()
                ->findOrFail($ticket->id);

            $queue = Queue::query()
                ->lockForUpdate()
                ->findOrFail($ticket->queue_id);

            $user = $ticket->user;

            $userStatistic = $this->getUserStatistic($queue, $user);


            if (!in_array($ticket->status, ['pending', 'no_show'])) {
                throw new Exception(
                    'Only pending/no-show tickets can be cancelled'
                );
            }


            $ticket->update([
                'status' => 'canceled',
            ]);

            if ($userStatistic) {
                $userStatistic->increment('total_cancellations');
            }


            Notification::create([
                'user_id' => $ticket->user_id,
                'type' => 'booking_cancelled',
                'title' => 'Booking cancelled',
                'body' => 'Your booking has been cancelled successfully.'
            ]);

            return $ticket->fresh([
                'queue',

            ]);
        });
    }

    public function markNoShow(Ticket $ticket): Ticket
    {
        return DB::transaction(function () use ($ticket) {

            $ticket = Ticket::query()
                ->with([
                    'queue',

                    'user'
                ])
                ->lockForUpdate()
                ->findOrFail($ticket->id);


            $queue = Queue::query()
                ->lockForUpdate()
                ->findOrFail($ticket->queue_id);

            $user = $ticket->user;

            $userStatistic = $this->getUserStatistic($queue, $user);


            if (
                !in_array($ticket->status, [
                    'pending',
                    'no_show'
                ])
            ) {
                throw new Exception(
                    'Ticket cannot be marked as no-show'
                );
            }



            $nextRunnableTicket = $queue
                ->tickets()
                ->whereIn('status', [
                    'pending',
                    'no_show'
                ])
                ->orderBy('number')
                ->first();


            if (
                !$nextRunnableTicket ||
                $nextRunnableTicket->id !== $ticket->id
            ) {
                throw new Exception(
                    'This is not the next ticket in queue'
                );
            }



            if ($ticket->status === 'pending') {


                $userStatistic->increment(
                    'total_moved_to_no_show'
                );
                $message = "Moved to No Show! Your turn has been moved to the end of the queue.";

                $lastNumber = $queue
                    ->tickets()
                    ->max('number');

                $ticket->update([
                    'status' => 'no_show',
                    'number' => ($lastNumber ?? 0) + 1
                ]);



                Notification::create([
                    'user_id' => $ticket->user_id,

                    'type' => 'moved_to_no_show',

                    'title' => 'You missed your turn',

                    'body' =>
                    'Your booking has been moved to the end of the queue.'
                ]);


                return $ticket->fresh([
                    'queue',

                ]);
            }



            if ($ticket->status === 'no_show') {


                $userStatistic->increment(
                    'total_no_show_absent'
                );
                $message = "Ticket has been Canceled!";


                $ticket->update([
                    'status' => 'expired'
                ]);



                Notification::create([
                    'user_id' => $ticket->user_id,

                    'type' => 'booking_expired',

                    'title' => 'Booking expired',

                    'body' =>
                    'Your booking expired because you missed your turn twice.'
                ]);


                return $ticket->fresh([
                    'queue',

                ]);
            }


            /*
        -------------------------------------------------
        Fallback
        -------------------------------------------------
        */
            throw new Exception(
                'Unhandled no-show state'
            );
        });
    }
    //function to get userStatistic by $ticket and $user
    public function getUserStatistic(Queue $queue, User $user): UserStatistic
    {
        $userStatistic = UserStatistic::firstOrCreate(
            [
                'user_id' => $user->id,
                'service_id' => $queue->service_id
            ],
            [
                'total_bookings' => 0,
                'total_on_time' => 0,
                'total_cancellations' => 0,
                'total_moved_to_no_show' => 0,
                'total_no_show_present' => 0,
                'total_no_show_absent' => 0,
                'session_avg_duration' => null
            ]
        );

        return $userStatistic;
    }
}
