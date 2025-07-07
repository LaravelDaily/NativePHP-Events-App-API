<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Models\Talk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::query();

        // Handle search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $events = $query->orderBy('start_datetime', 'asc')->get();

        return view('events.index', [
            'events' => $events,
            'search' => $request->input('search', ''),
        ]);
    }

    public function create()
    {
        return view('events.create');
    }

    public function store(StoreEventRequest $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            $event = Event::create([
                'user_id' => Auth::id(),
                'title' => $validated['title'],
                'description' => $validated['description'],
                'start_datetime' => $validated['start_datetime'],
                'end_datetime' => $validated['end_datetime'],
                'location' => $validated['location'],
            ]);

            // Create talks if provided
            if (isset($validated['talks']) && is_array($validated['talks'])) {
                foreach ($validated['talks'] as $talkData) {
                    if (!empty($talkData['title'])) {
                        $event->talks()->create([
                            'title' => $talkData['title'],
                            'description' => $talkData['description'],
                            'speaker_name' => $talkData['speaker_name'],
                            'start_time' => $talkData['start_time'],
                            'end_time' => $talkData['end_time'],
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('events.show', $event)
                ->with('success', 'Event created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create event. Please try again.']);
        }
    }

    public function edit(Event $event)
    {
        // Check if user owns the event
        if ($event->user_id !== Auth::id()) {
            abort(403, 'You are not authorized to edit this event.');
        }

        return view('events.edit', compact('event'));
    }

    public function update(UpdateEventRequest $request, Event $event)
    {
        // Check if user owns the event
        if ($event->user_id !== Auth::id()) {
            abort(403, 'You are not authorized to edit this event.');
        }

        try {
            DB::beginTransaction();

            $validated = $request->validated();

            $event->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'start_datetime' => $validated['start_datetime'],
                'end_datetime' => $validated['end_datetime'],
                'location' => $validated['location'],
            ]);

            // Handle talks - update existing, add new, remove deleted
            $existingTalkIds = $event->talks()->pluck('id')->toArray();
            $updatedTalkIds = [];

            if (isset($validated['talks']) && is_array($validated['talks'])) {
                foreach ($validated['talks'] as $talkData) {
                    if (!empty($talkData['title'])) {
                        if (isset($talkData['id']) && in_array($talkData['id'], $existingTalkIds)) {
                            // Update existing talk
                            $talk = $event->talks()->find($talkData['id']);
                            $talk->update([
                                'title' => $talkData['title'],
                                'description' => $talkData['description'],
                                'speaker_name' => $talkData['speaker_name'],
                                'start_time' => $talkData['start_time'],
                                'end_time' => $talkData['end_time'],
                            ]);
                            $updatedTalkIds[] = $talkData['id'];
                        } else {
                            // Create new talk
                            $newTalk = $event->talks()->create([
                                'title' => $talkData['title'],
                                'description' => $talkData['description'],
                                'speaker_name' => $talkData['speaker_name'],
                                'start_time' => $talkData['start_time'],
                                'end_time' => $talkData['end_time'],
                            ]);
                            $updatedTalkIds[] = $newTalk->id;
                        }
                    }
                }
            }

            // Remove talks that are no longer present
            $talksToDelete = array_diff($existingTalkIds, $updatedTalkIds);
            if (!empty($talksToDelete)) {
                $event->talks()->whereIn('id', $talksToDelete)->delete();
            }

            DB::commit();

            return redirect()->route('events.show', $event)
                ->with('success', 'Event updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update event. Please try again.']);
        }
    }

    public function show(Event $event)
    {
        $talks = $event->talks()
            ->orderBy('start_time', 'asc')
            ->get()
            ->groupBy(function ($talk) {
                return $talk->start_time->format('Y-m-d');
            });

        $isAttending = Auth::user()->isAttendingEvent($event);

        // Add attendance status to each talk
        $talks->transform(function ($dayTalks) {
            return $dayTalks->map(function ($talk) {
                $talk->isAttending = Auth::user()->isAttendingTalk($talk);
                return $talk;
            });
        });

        return view('events.show', [
            'event' => $event,
            'talks' => $talks,
            'isAttending' => $isAttending,
        ]);
    }

    public function toggleAttendance(Event $event)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $existingAttendance = $event->users()
            ->where('user_id', $user->id)
            ->first();

        if ($existingAttendance) {
            $event->users()->updateExistingPivot($user->id, [
                'is_attending' => !$existingAttendance->pivot->is_attending
            ]);

            $message = $existingAttendance->pivot->is_attending
                ? 'You are no longer attending this event.'
                : 'You are now attending this event!';
        } else {
            // Add new attendance
            $event->users()->attach($user->id, ['is_attending' => true]);
            $message = 'You are now attending this event!';
        }

        return redirect()->back()->with('success', $message);
    }

    public function toggleTalkAttendance(Talk $talk)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $existingAttendance = $talk->users()
            ->where('user_id', $user->id)
            ->first();

        if ($existingAttendance) {
            $talk->users()->updateExistingPivot($user->id, [
                'is_attending' => !$existingAttendance->pivot->is_attending
            ]);

            $message = $existingAttendance->pivot->is_attending
                ? 'You are no longer attending this talk.'
                : 'You are now attending this talk!';
        } else {
            // Add new attendance
            $talk->users()->attach($user->id, ['is_attending' => true]);
            $message = 'You are now attending this talk!';
        }

        return redirect()->back()->with('success', $message);
    }
}
