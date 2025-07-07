<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Talk;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create some sample events
        $events = Event::factory(5)
            ->has(Talk::factory(3)->thisWeek())
            ->create();

        // Attach some events and talks to the test user
        $events->take(3)->each(function ($event) use ($user) {
            $user->events()->attach($event->id, ['is_attending' => true]);
        });
    }
}
