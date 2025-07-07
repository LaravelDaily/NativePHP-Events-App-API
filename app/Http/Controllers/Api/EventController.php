<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    /**
     * Display a listing of events with optional filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Event::query()->with(['owner', 'talks']);

        // Filter by event status
        if ($request->has('filter')) {
            $filter = $request->get('filter');

            switch ($filter) {
                case 'attending':
                    $query->whereHas('users', function ($q) use ($user) {
                        $q->where('user_id', $user->id)
                            ->where('is_attending', true);
                    });
                    $query->where('end_datetime', '>', now());
                    break;

                case 'future':
                    $query->where('start_datetime', '>', now());
                    break;

                case 'past':
                    $query->where('end_datetime', '<', now());
                    break;

                case 'upcoming':
                    $query->where('start_datetime', '>', now())
                        ->where('start_datetime', '<=', now()->addDays(30));
                    break;
            }
        }

        // Order by start datetime
        $query->orderBy('start_datetime', 'asc');

        // Paginate results
        $events = $query->paginate($request->get('per_page', 15));

        return EventResource::collection($events)->response();
    }

    /**
     * Attend an event.
     */
    public function attend(Request $request, Event $event): JsonResponse
    {
        $user = $request->user();

        // Check if user is already attending
        if ($user->isAttendingEvent($event)) {
            return response()->json([
                'message' => 'You are already attending this event.',
                'is_attending' => true
            ], 200);
        }

        // Attach user to event with attending status
        $user->events()->attach($event->id, ['is_attending' => true]);

        return response()->json([
            'message' => 'Successfully registered for the event.',
            'is_attending' => true
        ], 200);
    }

    /**
     * Unattend an event.
     */
    public function unattend(Request $request, Event $event): JsonResponse
    {
        $user = $request->user();

        // Check if user is attending
        if (!$user->isAttendingEvent($event)) {
            return response()->json([
                'message' => 'You are not attending this event.',
                'is_attending' => false
            ], 200);
        }

        // Remove user from event
        $user->events()->detach($event->id);

        return response()->json([
            'message' => 'Successfully unregistered from the event.',
            'is_attending' => false
        ], 200);
    }

    /**
     * Show the specified event.
     */
    public function show(Request $request, Event $event): JsonResponse
    {
        $event->load(['owner', 'talks']);

        return (new EventResource($event))->response();
    }
}
