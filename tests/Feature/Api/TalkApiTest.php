<?php

use App\Models\Event;
use App\Models\Talk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('user can get single talk', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();
    $talk = Talk::factory()->for($event)->create();
    Sanctum::actingAs($user);

    $response = $this->getJson("/api/talks/{$talk->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'speaker_name',
                'start_time',
                'end_time',
                'event',
                'attendees_count',
                'is_attending',
                'created_at',
                'updated_at'
            ]
        ]);
});

test('user can attend talk', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();
    $talk = Talk::factory()->for($event)->create();
    Sanctum::actingAs($user);

    $response = $this->postJson("/api/talks/{$talk->id}/attend");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Successfully registered for the talk.',
            'is_attending' => true
        ]);

    expect($user->isAttendingTalk($talk))->toBeTrue();
});

test('user cannot attend talk twice', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();
    $talk = Talk::factory()->for($event)->create();
    Sanctum::actingAs($user);

    // First attendance
    $this->postJson("/api/talks/{$talk->id}/attend");

    // Second attendance attempt
    $response = $this->postJson("/api/talks/{$talk->id}/attend");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'You are already attending this talk.',
            'is_attending' => true
        ]);
});

test('user can unattend talk', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();
    $talk = Talk::factory()->for($event)->create();
    Sanctum::actingAs($user);

    // First attend
    $user->talks()->attach($talk->id, ['is_attending' => true]);

    $response = $this->deleteJson("/api/talks/{$talk->id}/attend");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Successfully unregistered from the talk.',
            'is_attending' => false
        ]);

    expect($user->isAttendingTalk($talk))->toBeFalse();
});

test('user cannot unattend talk not attending', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();
    $talk = Talk::factory()->for($event)->create();
    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/talks/{$talk->id}/attend");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'You are not attending this talk.',
            'is_attending' => false
        ]);
});

test('unauthenticated user cannot access talks', function () {
    $event = Event::factory()->create();
    $talk = Talk::factory()->for($event)->create();

    $response = $this->getJson("/api/talks/{$talk->id}");

    $response->assertStatus(401);
});

test('talk response includes event information', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();
    $talk = Talk::factory()->for($event)->create();
    Sanctum::actingAs($user);

    $response = $this->getJson("/api/talks/{$talk->id}");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'event' => [
                    'id' => $event->id,
                    'title' => $event->title
                ]
            ]
        ]);
});

test('talk attendance status is correct', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();
    $talk = Talk::factory()->for($event)->create();
    Sanctum::actingAs($user);

    // Initially not attending
    $response = $this->getJson("/api/talks/{$talk->id}");
    $response->assertJson(['data' => ['is_attending' => false]]);

    // After attending
    $this->postJson("/api/talks/{$talk->id}/attend");
    $response = $this->getJson("/api/talks/{$talk->id}");
    $response->assertJson(['data' => ['is_attending' => true]]);

    // After unattending
    $this->deleteJson("/api/talks/{$talk->id}/attend");
    $response = $this->getJson("/api/talks/{$talk->id}");
    $response->assertJson(['data' => ['is_attending' => false]]);
});
