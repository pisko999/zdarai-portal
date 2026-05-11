<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Talk;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\TalkTranslation>
 */
class TalkTranslationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'talk_id' => Talk::factory(),
            'locale' => fake()->randomElement(['cs', 'en']),
            'title' => fake()->sentence(5),
            'description' => fake()->paragraph(),
        ];
    }
}
