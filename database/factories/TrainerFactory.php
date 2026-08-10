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
        $name = fake()->name();
        return [
            'full_name' => $name,
            'email' => fake()->email(),
            'phone_number' => fake()->phoneNumber(),
            'years_experience' => fake()->numberBetween(1, 50),
            // 'profile_picture' => fake()->imageUrl(),
            'profile_picture' => 'https://ui-avatars.com/api/?background=' . substr(fake()->hexColor(), 1) . '&name=' . str_replace(' ', '+', $name),
            'notable_clients' => fake()->words(4, true),
            'avg_evaluation_score' => fake()->numberBetween(1, 10),
            'professional_summary' => fake()->paragraph(),
        ];
    }
}
