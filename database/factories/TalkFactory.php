<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\Speaker;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Talk>
 */
class TalkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'speaker_id' => Speaker::factory(),
            'title' => fake()->sentence(5),
            'description' => fake()->paragraph(),
            'duration_minutes' => fake()->randomElement([20, 30, 45, 60]),
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
