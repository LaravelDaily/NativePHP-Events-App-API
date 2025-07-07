<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Talk;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Talk>
 */
class TalkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = $this->faker->dateTimeBetween('now', '+2 months');
        $endTime = $this->faker->dateTimeBetween($startTime, '+3 months');

        return [
            'title' => $this->faker->sentence(4, 8),
            'description' => $this->faker->paragraphs(3, true),
            'speaker_name' => $this->faker->name(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'event_id' => Event::factory(),
        ];
    }

    /**
     * Indicate that the talk is happening today.
     */
    public function today(): static
    {
        return $this->state(fn(array $attributes) => [
            'start_time' => now()->startOfDay()->addHours(10),
            'end_time' => now()->startOfDay()->addHours(11),
        ]);
    }

    /**
     * Indicate that the talk is happening this week.
     */
    public function thisWeek(): static
    {
        return $this->state(fn(array $attributes) => [
            'start_time' => now()->addDays(rand(1, 7))->startOfDay()->addHours(10),
            'end_time' => now()->addDays(rand(1, 7))->startOfDay()->addHours(11),
        ]);
    }

    /**
     * Indicate that the talk is a short session (30 minutes).
     */
    public function short(): static
    {
        return $this->state(fn(array $attributes) => [
            'end_time' => $this->faker->dateTimeBetween($attributes['start_time'], '+30 minutes'),
        ]);
    }

    /**
     * Indicate that the talk is a long session (2 hours).
     */
    public function long(): static
    {
        return $this->state(fn(array $attributes) => [
            'end_time' => $this->faker->dateTimeBetween($attributes['start_time'], '+2 hours'),
        ]);
    }
}
