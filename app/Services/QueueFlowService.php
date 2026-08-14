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
    /**
     * Recalculates expected_wait_min and expected_start_time for all unfinished tickets in the queue.
     */
    public function recalculateTimes(Queue $queue): void
    {
        $avgDuration = $queue->service->base_duration ?? 28;

        // Get all active and waiting tickets ordered by queue number
        $unfinishedTickets = $queue->tickets()
            ->whereIn('status', ['handling', 'pending', 'no_show'])
            ->orderBy('number')
            ->get();

        $accumulatedWait = 0;
        $now = now();

        // 1. Calculate remaining time of the currently active 'handling' session, if one exists
        $handlingTicket = $unfinishedTickets->where('status', 'handling')->first();
        if ($handlingTicket) {
            $activeSession = ServiceSession::where('ticket_id', $handlingTicket->id)
                ->whereNull('end_time')
                ->latest('id')
                ->first();

            if ($activeSession) {
                $elapsed = (int) Carbon::parse($activeSession->start_time)->diffInMinutes($now);
                // The next ticket will start after the remaining time of the current session
                $accumulatedWait = max(0, $avgDuration - $elapsed);
            } else {
                $accumulatedWait = $avgDuration;
            }
        }

        // 2. Map and update parameters sequentially for all tickets
        foreach ($unfinishedTickets as $ticket) {
            if ($ticket->status === 'handling') {
                $ticket->update([
                    'expected_wait_min' => 0,
                    'expected_start_time' => $now,
                ]);
                continue;
            }

            // For pending and no_show tickets, calculate their relative wait times
            $expectedStart = $now->copy()->addMinutes($accumulatedWait);

            $ticket->update([
                'expected_wait_min' => $accumulatedWait,
                'expected_start_time' => $expectedStart,
            ]);

            // Add this ticket's duration to the waiting time of subsequent tickets
            $accumulatedWait += $avgDuration;
        }
    }

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
                'user_statistics_id' => $userStatistic->id,
                'start_time' => now()
            ]);

            // Recalculate wait times now that this ticket has moved to 'handling'
            $this->recalculateTimes($queue);

            /*
            -------------------------------------------------
            Notify Next 3 Users
            -------------------------------------------------
            */
            $nextTickets = $queue
                ->tickets()
                ->with('user')
                ->whereIn('status', [
                    'pending',
                    'no_show'
                ])
                ->where('number', '>', $ticket->number)
                ->where('expected_wait_min', '<', 60)
                ->orderBy('number')
                ->take(5)
                ->get();
           

            $firebase = app(FirebaseNotificationService::class);

            foreach ($nextTickets as $nextTicket) {
                $title = 'اقترب موعدك';
                $body = 'Please prepare, your turn is approaching.';
                $data = [
                    'type' => 'queue_near',
                    'ticket_id' => $nextTicket->id,
                    'queue_id' => $nextTicket->queue_id,
                ];

                $firebase->sendToUser($nextTicket->user, $title, $body, $data);

                Notification::create([
                    'user_id' => $nextTicket->user_id,
                    'type' => 'queue_near',
                    'title' => $title,
                    'body' => $body,
                    'data' => $data,
                ]);
            }

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

            $ticket->update([
                'status' => 'completed', 
            ]);

            if ($userStatistic) {
                $userStatistic->update([
                    'session_avg_duration' => $userStatistic->get_avg_duration()
                ]);
            }

            // Recalculate wait times now that this ticket is out of the active queue pool
            $this->recalculateTimes($queue);

            $completionData = [
                'type' => 'session_completed',
                'ticket_id' => $ticket->id,
                'queue_id' => $ticket->queue_id,
            ];

            Notification::create([
                'user_id' => $ticket->user_id,
                'type' => 'session_completed',
                'title' => 'انتهت الخدمة',
                'body' => 'تم إكمال الخدمة بنجاح',
                'data' => $completionData,
            ]);

            app(FirebaseNotificationService::class)->sendToUser(
                $user,
                'انتهت الخدمة',
                'تم إكمال الخدمة بنجاح',
                $completionData
            );

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

            // Recalculate wait times since this ticket is canceled and its slot is cleared
            $this->recalculateTimes($queue);

            $cancelData = [
                'type' => 'booking_cancelled',
                'ticket_id' => $ticket->id,
                'queue_id' => $ticket->queue_id,
            ];

            Notification::create([
                'user_id' => $ticket->user_id,
                'type' => 'booking_cancelled',
                'title' => 'الحجز ملغى',
                'body' => 'تم إلغاء الحجز بنجاح',
                'data' => $cancelData,
            ]);

            app(FirebaseNotificationService::class)->sendToUser(
                $user,
                'الحجز ملغى',
                'تم إلغاء الحجز بنجاح',
                $cancelData
            );

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

                $lastNumber = $queue
                    ->tickets()
                    ->max('number');

                $ticket->update([
                    'status' => 'no_show',
                    'number' => ($lastNumber ?? 0) + 1
                ]);

                // Recalculate wait times because this ticket has moved to the end of the queue line
                $this->recalculateTimes($queue);

                $noShowData = [
                    'type' => 'moved_to_no_show',
                    'ticket_id' => $ticket->id,
                    'queue_id' => $ticket->queue_id,
                ];

                Notification::create([
                    'user_id' => $ticket->user_id,
                    'type' => 'moved_to_no_show',
                    'title' => 'فاتك الدور',
                    'body' => 'تم نقلك لنهاية الطابور لأنك لم تحضر على الوقت',
                    'data' => $noShowData,
                ]);

                app(FirebaseNotificationService::class)->sendToUser(
                    $user,
                    'فاتك الدور',
                    'تم نقلك لنهاية الطابور لأنك لم تحضر على الوقت',
                    $noShowData
                );

                return $ticket->fresh([
                    'queue',
                ]);
            }

            if ($ticket->status === 'no_show') {
                $userStatistic->increment(
                    'total_no_show_absent'
                );

                $ticket->update([
                    'status' => 'expired'
                ]);

                // Recalculate wait times because this ticket's turn is fully expired/canceled
                $this->recalculateTimes($queue);

                $expiredData = [
                    'type' => 'booking_expired',
                    'ticket_id' => $ticket->id,
                    'queue_id' => $ticket->queue_id,
                ];

                Notification::create([
                    'user_id' => $ticket->user_id,
                    'type' => 'booking_expired',
                    'title' => 'تم إلغاء دورك',
                    'body' => 'تم إلغاءالحجز لأنك لم تحضر مرتين',
                    'data' => $expiredData,
                ]);

                app(FirebaseNotificationService::class)->sendToUser(
                    $user,
                    'تم إلغاء دورك',
                    'تم إلغاءالحجز لأنك لم تحضر مرتين',
                    $expiredData
                );

                return $ticket->fresh([
                    'queue',
                ]);
            }

            throw new Exception(
                'Unhandled no-show state'
            );
        });
    }

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