<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\EventTranslation>
 */
class EventTranslationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'locale' => fake()->randomElement(['cs', 'en']),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
        ];
    }
}
