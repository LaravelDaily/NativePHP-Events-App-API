<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDateTime = $this->faker->dateTimeBetween('now', '+2 months');
        $endDateTime = $this->faker->dateTimeBetween($startDateTime, '+3 months');

        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3, 6),
            'description' => $this->faker->paragraphs(2, true),
            'start_datetime' => $startDateTime,
            'end_datetime' => $endDateTime,
            'location' => $this->faker->address(),
        ];
    }

    /**
     * Indicate that the event is happening today.
     */
    public function today(): static
    {
        return $this->state(fn(array $attributes) => [
            'start_datetime' => now()->startOfDay()->addHours(9),
            'end_datetime' => now()->startOfDay()->addHours(17),
        ]);
    }

    /**
     * Indicate that the event is happening this week.
     */
    public function thisWeek(): static
    {
        return $this->state(fn(array $attributes) => [
            'start_datetime' => now()->addDays(rand(1, 7))->startOfDay()->addHours(9),
            'end_datetime' => now()->addDays(rand(1, 7))->startOfDay()->addHours(17),
        ]);
    }
}
