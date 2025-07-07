<?php

use App\Models\Event;
use App\Models\Talk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('user can get events list', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Event::factory()->count(3)->create();

    $response = $this->getJson('/api/events');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'start_datetime',
                    'end_datetime',
                    'location',
                    'owner',
                    'attendees_count',
                    'talks_count',
                    'is_attending',
                    'created_at',
                    'updated_at'
                ]
            ],
            'links',
            'meta'
        ]);
});

test('user can filter attending events', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $attendingEvent = Event::factory()->create();
    $nonAttendingEvent = Event::factory()->create();

    // User attends one event
    $user->events()->attach($attendingEvent->id, ['is_attending' => true]);

    $response = $this->getJson('/api/events?filter=attending');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.id'))->toBe($attendingEvent->id);
});

test('user can filter future events', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $futureEvent = Event::factory()->create([
        'start_datetime' => now()->addDays(5),
        'end_datetime' => now()->addDays(5)->addHours(2)
    ]);

    $pastEvent = Event::factory()->create([
        'start_datetime' => now()->subDays(5),
        'end_datetime' => now()->subDays(5)->addHours(2)
    ]);

    $response = $this->getJson('/api/events?filter=future');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.id'))->toBe($futureEvent->id);
});

test('user can filter past events', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $futureEvent = Event::factory()->create([
        'start_datetime' => now()->addDays(5),
        'end_datetime' => now()->addDays(5)->addHours(2)
    ]);

    $pastEvent = Event::factory()->create([
        'start_datetime' => now()->subDays(5),
        'end_datetime' => now()->subDays(5)->addHours(2)
    ]);

    $response = $this->getJson('/api/events?filter=past');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.id'))->toBe($pastEvent->id);
});

test('user can attend event', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/events/{$event->id}/attend");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Successfully registered for the event.',
            'is_attending' => true
        ]);

    expect($user->isAttendingEvent($event))->toBeTrue();
});

test('user cannot attend event twice', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();
    Sanctum::actingAs($user);

    // First attendance
    $this->postJson("/api/events/{$event->id}/attend");

    // Second attendance attempt
    $response = $this->postJson("/api/events/{$event->id}/attend");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'You are already attending this event.',
            'is_attending' => true
        ]);
});

test('user can unattend event', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();
    Sanctum::actingAs($user);

    // First attend
    $user->events()->attach($event->id, ['is_attending' => true]);

    $response = $this->deleteJson("/api/events/{$event->id}/attend");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Successfully unregistered from the event.',
            'is_attending' => false
        ]);

    expect($user->isAttendingEvent($event))->toBeFalse();
});

test('user cannot unattend event not attending', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/events/{$event->id}/attend");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'You are not attending this event.',
            'is_attending' => false
        ]);
});

test('user can get single event', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson("/api/events/{$event->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'start_datetime',
                'end_datetime',
                'location',
                'owner',
                'attendees_count',
                'talks_count',
                'is_attending',
                'created_at',
                'updated_at'
            ]
        ]);
});

test('unauthenticated user cannot access events', function () {
    $response = $this->getJson('/api/events');

    $response->assertStatus(401);
});

test('pagination works correctly', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Event::factory()->count(25)->create();

    $response = $this->getJson('/api/events?per_page=10');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(10);
    expect($response->json('meta.per_page'))->toBe(10);
    expect($response->json('meta.last_page'))->toBe(3);
});
