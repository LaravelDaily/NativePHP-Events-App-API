<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TalkResource;
use App\Models\Talk;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TalkController extends Controller
{
    /**
     * Attend a talk.
     */
    public function attend(Request $request, Talk $talk): JsonResponse
    {
        $user = $request->user();

        // Check if user is already attending
        if ($user->isAttendingTalk($talk)) {
            return response()->json([
                'message' => 'You are already attending this talk.',
                'is_attending' => true
            ], 200);
        }

        // Attach user to talk with attending status
        $user->talks()->attach($talk->id, ['is_attending' => true]);

        return response()->json([
            'message' => 'Successfully registered for the talk.',
            'is_attending' => true
        ], 200);
    }

    /**
     * Unattend a talk.
     */
    public function unattend(Request $request, Talk $talk): JsonResponse
    {
        $user = $request->user();

        // Check if user is attending
        if (!$user->isAttendingTalk($talk)) {
            return response()->json([
                'message' => 'You are not attending this talk.',
                'is_attending' => false
            ], 200);
        }

        // Remove user from talk
        $user->talks()->detach($talk->id);

        return response()->json([
            'message' => 'Successfully unregistered from the talk.',
            'is_attending' => false
        ], 200);
    }

    /**
     * Show the specified talk.
     */
    public function show(Request $request, Talk $talk): JsonResponse
    {
        $talk->load(['event']);

        return (new TalkResource($talk))->response();
    }
}
