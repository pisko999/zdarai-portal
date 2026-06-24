<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\NewsletterSubscriber>
 */
class NewsletterSubscriberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'name' => fake()->optional(0.7)->name(),
            'user_id' => null,
            'token' => (string) Str::uuid(),
            'consent_at' => now(),
            'unsubscribed_at' => null,
            'source' => fake()->randomElement(['registration', 'modal']),
        ];
    }

    public function unsubscribed(): static
    {
        return $this->state(fn (): array => [
            'unsubscribed_at' => now(),
        ]);
    }
}
