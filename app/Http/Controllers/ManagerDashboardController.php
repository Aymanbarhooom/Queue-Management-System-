<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Queue;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Traits\ApiResponse;

class ManagerDashboardController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $user = auth()->user();

        if (! $user || ! $user->isManager()) {
            return $this->apiError('not authorized', 403);
        }

        $businesses = Business::where('user_id', $user->id)
            ->withCount(['services', 'employees'])
            ->get();

        $responseData = [
            'all_businesses' => $businesses->count(),
            'all_data' => [
                'businesses' => [],
                'totals' => [
                    'services' => 0,
                    'queues' => 0,
                    'employees' => 0,
                    'tickets' => 0,
                    'pending_tickets' => 0,
                    'canceled_tickets' => 0,
                    'handling_tickets' => 0,
                    'completed_tickets' => 0,
                ],
            ],
        ];

        foreach ($businesses as $business) {
            $queuesCount = $business->services()->withCount('queues')->get()->sum('queues_count');
            $ticketsCount = Queue::whereIn('service_id', $business->services()->pluck('id'))->count();
            $pendingTickets = Ticket::whereIn('queue_id', Queue::whereIn('service_id', $business->services()->pluck('id'))->pluck('id'))->where('status', 'pending')->count();
            $canceledTickets = Ticket::whereIn('queue_id', Queue::whereIn('service_id', $business->services()->pluck('id'))->pluck('id'))->where('status', 'canceled')->count();
            $handlingTickets = Ticket::whereIn('queue_id', Queue::whereIn('service_id', $business->services()->pluck('id'))->pluck('id'))->where('status', 'handling')->count();
            $completedTickets = Ticket::whereIn('queue_id', Queue::whereIn('service_id', $business->services()->pluck('id'))->pluck('id'))->where('status', 'completed')->count();

            $responseData['all_data']['businesses'][] = [
                'business_id' => $business->id,
                'business_name' => $business->name,
                'services' => $business->services_count,
                'queues' => $queuesCount,
                'employees' => $business->employees_count,
                'tickets' => $ticketsCount,
                'pending_tickets' => $pendingTickets,
                'canceled_tickets' => $canceledTickets,
                'handling_tickets' => $handlingTickets,
                'completed_tickets' => $completedTickets,
            ];

            $responseData['all_data']['totals']['services'] += $business->services_count;
            $responseData['all_data']['totals']['queues'] += $queuesCount;
            $responseData['all_data']['totals']['employees'] += $business->employees_count;
            $responseData['all_data']['totals']['tickets'] += $ticketsCount;
            $responseData['all_data']['totals']['pending_tickets'] += $pendingTickets;
            $responseData['all_data']['totals']['canceled_tickets'] += $canceledTickets;
            $responseData['all_data']['totals']['handling_tickets'] += $handlingTickets;
            $responseData['all_data']['totals']['completed_tickets'] += $completedTickets;
        }

        return $this->apiResponse($responseData, 'Manager dashboard data fetched successfully', 200);
    }
}
