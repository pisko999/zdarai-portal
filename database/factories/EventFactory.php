<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(1, 9999),
            'description' => fake()->paragraph(),
            'date' => fake()->dateTimeBetween('+1 week', '+6 months'),
            'location' => 'MtgForFun, Žďár nad Sázavou',
            'capacity' => fake()->numberBetween(15, 40),
            'price' => fake()->optional(0.3)->numberBetween(0, 300),
            'status' => fake()->randomElement(['draft', 'published']),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'date' => fake()->dateTimeBetween('-6 months', '-1 week'),
        ]);
    }
}
