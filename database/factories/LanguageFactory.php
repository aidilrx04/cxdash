<?php

namespace Database\Factories;

use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Language>
 */
class LanguageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'language' => fake()->languageCode(),
            'proficiency' => fake()->randomElement(['Native', 'Fluent', 'Professional Working Proficiency', 'Conversational', 'Basic'])
        ];
    }
}
