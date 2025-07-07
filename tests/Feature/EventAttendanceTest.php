<?php

use App\Models\Event;
use App\Models\Talk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('unauthenticated users see login button instead of attend button', function () {
    $event = Event::factory()->create();

    $response = $this->get(route('events.show', $event));

    $response->assertRedirect('/login');
});

test('authenticated users can see attend button', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $response = $this->actingAs($user)->get(route('events.show', $event));

    $response->assertStatus(200);
    $response->assertSee('Attend Event');
    $response->assertDontSee('Login to Attend');
});

test('users can attend an event', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('events.attend', $event));

    $response->assertRedirect();
    $response->assertSessionHas('success', 'You are now attending this event!');

    expect($user->isAttendingEvent($event))->toBeTrue();
});

test('users can cancel attendance', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $event = Event::factory()->create();

    // First attend the event
    $user->events()->attach($event->id, ['is_attending' => true]);

    $response = $this->actingAs($user)
        ->post(route('events.attend', $event));

    $response->assertRedirect();
    $response->assertSessionHas('success', 'You are no longer attending this event.');

    expect($user->isAttendingEvent($event))->toBeFalse();
});

test('attending status is displayed correctly', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $event = Event::factory()->create();

    // User is not attending initially
    $response = $this->actingAs($user)->get(route('events.show', $event));
    $response->assertSee('Attend Event');
    $response->assertDontSee('You&#039;re attending!');

    // User attends the event
    $user->events()->attach($event->id, ['is_attending' => true]);

    $response = $this->actingAs($user)->get(route('events.show', $event));
    $response->assertSee('Cancel Attendance');
    $response->assertSee('attending');
});

test('unauthenticated users are redirected to login when trying to attend', function () {
    $event = Event::factory()->create();

    $response = $this->post(route('events.attend', $event));

    $response->assertRedirect(route('login'));
});

test('users can attend a talk', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $event = Event::factory()->create();
    $talk = Talk::factory()->create(['event_id' => $event->id]);

    $response = $this->actingAs($user)
        ->post(route('talks.attend', $talk));

    $response->assertRedirect();
    $response->assertSessionHas('success', 'You are now attending this talk!');

    expect($user->isAttendingTalk($talk))->toBeTrue();
});

test('users can cancel talk attendance', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $event = Event::factory()->create();
    $talk = Talk::factory()->create(['event_id' => $event->id]);

    // First attend the talk
    $user->talks()->attach($talk->id, ['is_attending' => true]);

    $response = $this->actingAs($user)
        ->post(route('talks.attend', $talk));

    $response->assertRedirect();
    $response->assertSessionHas('success', 'You are no longer attending this talk.');

    expect($user->isAttendingTalk($talk))->toBeFalse();
});

test('talk attendance status is displayed correctly', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $event = Event::factory()->create();
    $talk = Talk::factory()->create(['event_id' => $event->id]);

    // User is not attending initially
    $response = $this->actingAs($user)->get(route('events.show', $event));
    $response->assertSee('Attend');
    $response->assertDontSee('Attending');

    // User attends the talk
    $user->talks()->attach($talk->id, ['is_attending' => true]);

    $response = $this->actingAs($user)->get(route('events.show', $event));
    $response->assertSee('Cancel');
    $response->assertSee('Attending');
});

test('unauthenticated users are redirected to login when trying to attend talk', function () {
    $event = Event::factory()->create();
    $talk = Talk::factory()->create(['event_id' => $event->id]);

    $response = $this->post(route('talks.attend', $talk));

    $response->assertRedirect(route('login'));
});
