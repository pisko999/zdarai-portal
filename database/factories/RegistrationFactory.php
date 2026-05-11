<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Registration>
 */
class RegistrationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'user_id' => null,
            'token' => (string) Str::uuid(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'payment_status' => fake()->randomElement(['free', 'pending', 'paid', 'cancelled']),
            'reminder_sent_at' => null,
            'email_opt_out' => false,
            'dietary_notes' => fake()->optional(0.2)->word(),
        ];
    }
}
