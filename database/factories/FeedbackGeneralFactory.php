<?php

namespace Database\Factories;

use App\Models\FeedbackGeneral;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeedbackGeneral>
 */
class FeedbackGeneralFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'respond' => fake()->sentence(),
            'sentiment' => fake()->randomElement(['positive', 'neutral', 'negative']),
            'theme' => fake()->words(3, true),
        ];
    }
}
