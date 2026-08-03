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
            'name' => fake()->company() . '.' . fake()->fileExtension(),
            'file_path' => fake()->filePath()
        ];
    }
}
