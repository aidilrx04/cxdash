<?php

namespace Database\Factories;

use App\Models\Education;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Education>
 */
class EducationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->city(),
            'institution_name' => fake()->company(),
            'completion_year' => fake()->year(),
            'location' => fake()->address(),
            'grade' => fake()->numberBetween(1, 5),
            'document_paths' => json_encode([fake()->url(), fake()->url(), fake()->url()]),
        ];
    }
}
