<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;

class NotificationController extends Controller
{
    use ApiResponse;
    public function index()
    {
        $user = auth()->user();
        $notifications = $user->notifications;
        return $this->apiResponse($notifications, 'Notifications fetched successfully', 200);
    }

}
