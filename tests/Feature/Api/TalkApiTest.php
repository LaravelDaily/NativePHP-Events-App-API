<?php

use App\Models\Event;
use App\Models\Talk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

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
