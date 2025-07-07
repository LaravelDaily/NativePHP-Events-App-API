<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'start_datetime' => $this->start_datetime,
            'end_datetime' => $this->end_datetime,
            'location' => $this->location,
            'owner' => $this->owner ? [
                'id' => $this->owner->id,
                'name' => $this->owner->name,
                'email' => $this->owner->email,
            ] : null,
            'attendees_count' => $this->attendees()->count(),
            'talks_count' => $this->talks()->count(),
            'is_attending' => $request->user() ? $request->user()->isAttendingEvent($this->resource) : false,
            'talks' => $this->talks->groupBy(function ($talk) {
                return $talk->start_time->format('Y-m-d');
            })->map(function ($dayTalks) {
                return TalkResource::collection($dayTalks);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
