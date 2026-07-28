<?php

namespace Database\Factories;

use App\Models\Trainer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trainer>
 */
class TrainerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'email' => fake()->email(),
            'phone_number' => fake()->phoneNumber(),
            'years_experience' => fake()->numberBetween(1, 50),
            'profile_picture' => fake()->imageUrl(),
            'notable_clients' => fake()->words(4, true),
            'avg_evaluation_score' => fake()->numberBetween(1, 10),
            'professional_summary' => fake()->paragraph(),
        ];
    }
}
