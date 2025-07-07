<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TalkResource extends JsonResource
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
            'speaker_name' => $this->speaker_name,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'event' => $this->event ? [
                'id' => $this->event->id,
                'title' => $this->event->title,
            ] : null,
            'attendees_count' => $this->attendees()->count(),
            'is_attending' => $request->user() ? $request->user()->isAttendingTalk($this->resource) : false,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
