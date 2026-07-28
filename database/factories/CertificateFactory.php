<?php

namespace Database\Factories;

use App\Models\Certificate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Certificate>
 */
class CertificateFactory extends Factory
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
            'issuing_body' => fake()->company(),
            'year_obtained' => fake()->year(),
            'expires_at' => fake()->date(max: '+10 years'),
            'document_paths' => json_encode([fake()->url(), fake()->url(), fake()->url(),]),
        ];
    }
}
