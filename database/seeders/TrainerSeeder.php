<?php

namespace Database\Seeders;

use App\Models\SocialMedia;
use App\Models\Trainer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TrainerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Trainer::factory(10)
            ->has(SocialMedia::factory(3))
            ->create();
    }
}
