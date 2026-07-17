<?php

namespace App\Http\Controllers;

use App\Models\Notification;
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

    public function markAsRead(Notification $notification)
    {
        $notification->is_read = true;
        $notification->save();
        return $this->apiResponse($notification, 'Notification marked as read', 200);
    }

}
