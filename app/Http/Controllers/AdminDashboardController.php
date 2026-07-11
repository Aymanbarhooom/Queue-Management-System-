<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Queue;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Traits\ApiResponse;

class AdminDashboardController extends Controller
{
    use ApiResponse;

    public function index()
    {
        if (! auth()->user()->isAdmin()) {
            return $this->apiError('not authorized', 403);
        }


        return $this->apiResponse([
            'all_businesses' => Business::all()->count(),
            'all_services'   => Service::all()->count(),
            'all_queues'     => Queue::all()->count(),
            'all_users'      => User::all()->count(),
            'all_managers'   => User::where('role', 'manager')->count(),
            'all_employees'  => User::where('role', 'employee')->count(),
            'all_clients'    => User::where('role', 'user')->count(),
            'all_tickets'    => Ticket::all()->count(),
            'all_pending_tickets' => Ticket::where('status', 'pending')->count(),
            'all_canceled_tickets' => Ticket::where('status', 'canceled')->count(),
            'all_handling_tickets' => Ticket::where('status', 'handling')->count(),
            'all_completed_tickets' => Ticket::where('status', 'completed')->count(),
        ], 'Admin dashboard data fetched successfully', 200);
    }
}
