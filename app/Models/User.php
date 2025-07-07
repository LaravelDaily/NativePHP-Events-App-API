<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Talk;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'company',
        'job_title',
        'country',
        'city',
        'socials',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'socials' => 'array',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn(string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    public function managedEvents(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class)
            ->withPivot('is_attending')
            ->withTimestamps();
    }

    public function attendingEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class)
            ->wherePivot('is_attending', true)
            ->withTimestamps();
    }

    public function talks(): BelongsToMany
    {
        return $this->belongsToMany(Talk::class)
            ->withPivot('is_attending')
            ->withTimestamps();
    }

    public function attendingTalks(): BelongsToMany
    {
        return $this->belongsToMany(Talk::class)
            ->wherePivot('is_attending', true)
            ->withTimestamps();
    }

    public function isAttendingEvent(Event $event): bool
    {
        return $this->events()
            ->where('event_id', $event->id)
            ->where('is_attending', true)
            ->exists();
    }

    public function isAttendingTalk(Talk $talk): bool
    {
        return $this->talks()
            ->where('talk_id', $talk->id)
            ->where('is_attending', true)
            ->exists();
    }

    public function getAttendingTalks()
    {
        return $this->attendingTalks()
            ->with('event')
            ->orderBy('start_time', 'asc')
            ->get();
    }
}
