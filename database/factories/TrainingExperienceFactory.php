<?php

namespace Database\Factories;

use App\Models\TrainingExperience;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingExperience>
 */
class TrainingExperienceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'program_name' => fake()->colorName(),
            'client' => fake()->company(),
            'date_start' => fake()->date(),
            'date_end' => fake()->date(),
            'participant_count' => fake()->numberBetween(10, 100),
        ];
    }
}
