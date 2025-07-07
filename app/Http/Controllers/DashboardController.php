<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $attendingEvents = $user->attendingEvents()
            ->orderBy('start_datetime', 'asc')
            ->get();
        $attendingTalks = $user->getAttendingTalks();
        $otherEvents = Event::query()
            ->whereNotIn('id', $attendingEvents->pluck('id'))
            ->orderBy('start_datetime', 'asc')
            ->limit(6)
            ->get();

        return view('dashboard', [
            'attendingEvents' => $attendingEvents,
            'attendingTalks' => $attendingTalks,
            'otherEvents' => $otherEvents,
        ]);
    }
}
