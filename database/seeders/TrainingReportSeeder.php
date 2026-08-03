<?php

namespace Database\Seeders;

use App\Models\TrainingReport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TrainingReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TrainingReport::factory(10)->create();
    }
}
