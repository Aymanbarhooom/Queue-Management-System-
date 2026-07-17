<?php

namespace App\Services;

use App\Models\Queue;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserStatistic;
use App\Models\Notification;
use App\Models\Wallet;
use App\Models\Process;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
 
class TicketBookingService
{
    public function book(User $user, Queue $queue): Ticket
    {
        return DB::transaction(function () use ($user, $queue) {

            $queue = Queue::query()
                ->with([
                    'service.business.workingTimes'
                ])
                ->lockForUpdate()
                ->findOrFail($queue->id);


            if (!$queue->isActive()) {
                throw new Exception('Queue is inactive');
            }



            $business = $queue->service->business;

            $today = strtolower(now()->format('l'));

            $workingTime = $business
                ->workingTimes
                ->first(fn ($schedule) => strtolower($schedule->day_of_week) === $today);

            if (!$workingTime || $workingTime->is_closed) {
                throw new Exception('Business is closed today');
            }

            $userStatistic = UserStatistic::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'service_id' => $queue->service_id
                ],
                [
                    'total_bookings' => 0,
                    'total_cancellations' => 0,
                    'total_on_time' => 0,
                    'total_moved_to_no_show' => 0,
                    'total_no_show_present' => 0,
                    'total_no_show_absent' => 0,
                    'session_avg_duration' => null
                ]
            );


            $predictedDuration =
                $userStatistic->session_avg_duration
                ??
                $queue->service->base_duration;

            $lastNumber = $queue
                ->tickets()
                ->max('number');

            $ticketNumber = ($lastNumber ?? 0) + 1;

            $remainingDuration = $queue
                ->tickets()
                ->whereIn('status', [
                    'pending',
                    'no_show'
                ])
                ->where('number', '<', $ticketNumber)
                ->sum('final_session_duration');

            $expectedStartTime = now()
                ->copy()
                ->addMinutes($remainingDuration);

            $expectedEndTime = $expectedStartTime
                ->copy()
                ->addMinutes($predictedDuration);

            $closingTime = Carbon::parse(
                now()->format('Y-m-d') . ' ' . $workingTime->close_hour
            );

            if ($expectedEndTime->greaterThan($closingTime)) {
                if (!$queue->isHighCongestion()) {
                    $queue->update([
                        'congestion' => 'high'
                    ]);
                }
            }

            $ticket = Ticket::create([
                'user_id' => $user->id,
                'queue_id' => $queue->id,

                'number' => $ticketNumber,

                'status' => 'pending',

                'expected_wait_min' =>
                    $remainingDuration,

                'expected_start_time' =>
                    $expectedStartTime,

                'final_session_duration' =>
                    $predictedDuration,
            ]);


          
            $userStatistic->increment(
                'total_bookings'
            );


            $servicePrice = $queue
                ->service
                ->price;

            $depositAmount =
                $servicePrice * 0.10;

            $managerAmount =
                $depositAmount / 2;

            $adminAmount =
                $depositAmount / 2;


           
            $userWallet = Wallet::where(
                'user_id',
                $user->id
            )->lockForUpdate()->first();

            if (
                !$userWallet ||
                $userWallet->balance < $depositAmount
            ) {
                throw new Exception(
                    'Insufficient wallet balance'
                );
            }


           
            $managerId = $business->user_id;

            $managerWallet = Wallet::firstOrCreate([
                'user_id' => $managerId
            ]);


            
            $adminWallet = Wallet::firstOrCreate([
                'user_id' => 1
            ]);


           
            $userWallet->decrement(
                'balance',
                $depositAmount
            );

            Process::create([
                'wallet_id' => $userWallet->id,
                'type' => 'withdrawal',
                'amount' => $depositAmount,
                'description' =>
                    'Service booking deposit'
            ]);


           
            $managerWallet->increment(
                'balance',
                $managerAmount
            );

            Process::create([
                'wallet_id' => $managerWallet->id,
                'type' => 'manager_payout',
                'amount' => $managerAmount,
                'description' =>
                    'Booking deposit share'
            ]);


            /*
            -------------------------------------------------
            Admin Deposit
            -------------------------------------------------
            */
            $adminWallet->increment(
                'balance',
                $adminAmount
            );

            Process::create([
                'wallet_id' => $adminWallet->id,
                'type' => 'platform_fee',
                'amount' => $adminAmount,
                'description' =>
                    'Platform booking fee'
            ]);


          
            Notification::create([
                'user_id' => $user->id,

                'type' => 'booking_confirmed',

                'title' => 'تم الحجر بنجاح',

                'body' =>
                    'لقد تم تأكيد حجرك بنجاح! سيصلك إشعار عند اقتراب دورك',
                    'data'=>[
                        'ticket_id'=>$ticket->id,
                        'queue_id'=>$ticket->queue_id
                    ]
            ]);
            $token = $user->fcm_token;
            $title = 'تم الحجر بنجاح';
            $body = 'لقد تم تأكيد حجرك بنجاح! سيصلك إشعار عند اقتراب دورك';
            $data = [
                'ticket_id' => $ticket->id,
                'queue_id' => $ticket->queue_id
            ];
            app(FirebaseNotificationService::class)->sendPushNotification(
                $token,
                $title,
                $body,
                $data
            );


           
            return $ticket->fresh([
                'queue',
                'queue.service',
                'user'
            ]);
        });
    }
    public function getUserStatistic(Ticket $ticket, User $user)
{
    $userStatistic = UserStatistic::firstOrCreate(
        [
            'user_id' => $user->id,
            'service_id' => $ticket->service_id
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