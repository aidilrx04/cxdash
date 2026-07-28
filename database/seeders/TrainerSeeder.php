<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\Education;
use App\Models\Industry;
use App\Models\Language;
use App\Models\SocialMedia;
use App\Models\Specialization;
use App\Models\Tool;
use App\Models\Trainer;
use App\Models\TrainingMethod;
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
            ->has(Education::factory(3))
            ->has(Certificate::factory(3))
            ->hasAttached(Specialization::factory(3))
            ->hasAttached(Industry::factory(3))
            ->hasAttached(Tool::factory(3))
            ->hasAttached(TrainingMethod::factory(3))
            ->has(Language::factory(3))
            ->create();
    }
}
