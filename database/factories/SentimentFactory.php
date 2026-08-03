<?php

namespace Database\Factories;

use App\Models\Sentiment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sentiment>
 */
class SentimentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sentiment' => fake()->randomElement(['positive', 'neutral', 'negative']),
            'theme' => fake()->words(3, true),
        ];
    }
}
