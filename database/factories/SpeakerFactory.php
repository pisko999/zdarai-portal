<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Speaker>
 */
class SpeakerFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->name();
        $slug = strtolower(str_replace(' ', '', $name));

        return [
            'name' => $name,
            'bio' => fake()->paragraph(),
            'avatar' => null,
            'github_url' => 'https://github.com/' . $slug,
            'linkedin_url' => fake()->optional(0.6)->url(),
            'twitter_url' => fake()->optional(0.4)->url(),
        ];
    }
}
