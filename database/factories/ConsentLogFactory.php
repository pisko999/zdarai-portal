<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ConsentLog>
 */
class ConsentLogFactory extends Factory
{
    public function definition(): array
    {
        $analytics = fake()->boolean();

        return [
            'user_id' => null,
            'ip_hash' => hash('sha256', fake()->ipv4() . 'salt'),
            'categories' => [
                'necessary' => true,
                'analytics' => $analytics,
            ],
            'consent_version' => '1',
            'user_agent' => fake()->userAgent(),
            'created_at' => now(),
        ];
    }

    public function analyticsAccepted(): static
    {
        return $this->state(fn (): array => [
            'categories' => ['necessary' => true, 'analytics' => true],
        ]);
    }
}
