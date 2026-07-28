<?php

namespace Database\Factories;

use App\Models\AiSkill;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiSkill>
 */
class AiSkillFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ai_usage' => fake()->paragraph(),
            'ai_tools' => fake()->paragraph()
        ];
    }
}
