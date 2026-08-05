<?php

namespace Database\Seeders;

use App\Models\FeedbackGeneral;
use App\Models\FeedbackQuestion;
use App\Models\Sentiment;
use App\Models\Trainee;
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
        $questions = FeedbackQuestion::factory(4)->create();
        $reports = TrainingReport::factory(10)
            ->has(Trainee::factory(10))
            ->create();

        foreach ($reports as $report) {
            foreach ($report->trainees as $trainee) {
                foreach ($questions as $question) {
                    Sentiment::factory()->for($report)->for($trainee)->for($question)->create();
                    FeedbackGeneral::factory()->for($trainee)->for($question)->create();
                }
            }
        }
    }
}
