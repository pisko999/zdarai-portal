<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\NewsletterSubscriber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\NewsletterEventSend>
 */
class NewsletterEventSendFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'newsletter_subscriber_id' => NewsletterSubscriber::factory(),
            'email' => fake()->safeEmail(),
            'sent_at' => now(),
        ];
    }
}
