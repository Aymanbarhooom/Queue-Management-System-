<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Queue;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Traits\ApiResponse;

class QueueController extends Controller
{
    use ApiResponse;
    use AuthorizesRequests;

    public function index()
    {
        $user = auth()->user();

        if ($user && $user->isAdmin()) {
            $queues = Queue::all();
            return $this->apiResponse($queues, 'Queues fetched successfully', 200);
        }

        if ($user && $user->isManager()) {
            $queues = Queue::whereHas('service.business', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->get();

            return $this->apiResponse($queues, 'Queues fetched successfully', 200);
        }

        return $this->apiResponse(null, 'No queues available', 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'     => 'required|exists:users,id',
            'service_id'  => 'required|exists:services,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => ['required', Rule::in(['active', 'inactive'])],
            'type'        => ['required', Rule::in(['main', 'no_show'])],
        ]);


        $service = Service::findOrFail($request->service_id);
        $this->authorize('create', [Queue::class, $service]);

        $queue = Queue::create($validated);

        return $this->apiResponse($queue, 'Queue created successfully', 201);
    }

    public function show(Queue $queue)
    {
        $user = auth()->user();
        $this->authorize('view', $queue);


        $tickets = $queue->tickets()->whereIn('status', ['pending', 'no_show'])->get()->load('user');
        $totalWaitingTime = 0;
        foreach ($tickets as $ticket) {
            $totalWaitingTime += $ticket->final_session_duration;
        }

        $totalTickets = $queue->tickets()->count();

        $userData = array(
            'queue' => $queue,
        );

        $employeeData = array(
            'queue' => $queue,
            'service' => $queue->service,
            'totalTickets' => $totalTickets,
            'totalWaitingTime' => $totalWaitingTime,
            'tickets' => $tickets
        );
        if ($user && $user->isUser()) {
            return $this->apiResponse($userData, 'Queue fetched successfully', 200);
        } else {
            return $this->apiResponse($employeeData, 'Queue fetched successfully', 200);
        }
    }

    public function update(Request $request, Queue $queue)
    {
        $this->authorize('update', $queue);

        $validated = $request->validate([
            'user_id'     => 'sometimes|exists:users,id',
            'service_id'  => 'sometimes|exists:services,id',
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status'      => ['sometimes', Rule::in(['active', 'inactive'])],
            'type'        => ['sometimes', Rule::in(['main', 'no_show'])],
        ]);

        $queue->update($validated);

        return $this->apiResponse($queue, 'Queue updated successfully', 200);
    }

    public function destroy(Queue $queue)
    {
        $this->authorize('delete', $queue);

        $queue->delete();

        return $this->apiResponse(null, 'Queue deleted successfully', 204);
    }

    public function updateQueueCongestion(Request $request, Queue $queue)
    {
        $user = auth()->user();
        if (!$user->isManager() && !$user->isEmployee()) {
            return $this->apiError('not authorized', 403);
        }
        $validated = $request->validate([
            'congestion' => 'required|string|in:low,medium,high',
        ]);
        if ($validated['congestion'] === 'high') {
            $tickets = $queue->tickets()->whereIn('status', ['pending', 'no_show'])->take(ceil($queue->tickets()->count() / 3))->get();
            $users = $tickets->map(function ($ticket) {
                return $ticket->user;
            })->unique();
            $users->each(function ($user) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Queue congestion is high',
                    'body' => 'Queue congestion is high. It is recommended to change queue.'
                ]);
            });
        }
        $queue->update([
            'congestion' => $validated['congestion'],
        ]);
        return $this->apiResponse($queue, 'Queue updated successfully', 200);
    }

    public function myQueue()
    {
        $user = auth()->user();
        if (!$user || !$user->isEmployee()) {
            return $this->apiResponse(null, 'No queues available', 200);
        }

        $queue = Queue::where('user_id', $user->id)->first();

        if ($queue) {
            $queue->load([
                'service',
                'tickets' => function ($query) {
                    $query->whereIn('status', ['pending', 'handling', 'no_show'])
                        ->orderBy('number', 'asc')
                        ->with('user');
                },
            ]);
        }

        return $this->apiResponse($queue, 'Queue fetched successfully', 200);
    }
}
