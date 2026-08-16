<?php

namespace Database\Factories;

use App\Models\TrainingReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingReport>
 */
class TrainingReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'file_name' => fake()->company() . '.' . fake()->fileExtension(),
            'program_title' => fake()->name(),
            'client_name' => fake()->company(),
            'trainer_name' => fake()->name(),
            'total_participants' => fake()->numberBetween(10, 100),
            'total_evaluation' => fake()->numberBetween(10, 100),
            'overall_satisfaction' => fake()->numberBetween(10, 100) . '%',
            'status' => fake()->colorName(),
            'pss_score' => fake()->numberBetween(10, 100),
            'file_path' => fake()->filePath(),
            'executive_summary' => fake()->paragraphs(6, true),
        ];
    }
}
