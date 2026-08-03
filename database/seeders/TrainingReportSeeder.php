<?php

namespace Database\Seeders;

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
        $reports = TrainingReport::factory(10)
            ->has(Trainee::factory(10))
            ->has(FeedbackQuestion::factory(4))
            ->create();

        foreach ($reports as $report) {
            foreach ($report->trainees as $trainee) {
                foreach ($report->feedbackQuestions as $question) {
                    Sentiment::factory()->for($report)->for($trainee)->for($question)->create();
                }
            }
        }
    }
}
