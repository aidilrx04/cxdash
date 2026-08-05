<?php

namespace App\Filament\Resources\TrainingReports\Pages;

use App\Filament\Resources\TrainingReports\TrainingReportResource;
use App\Jobs\GenerateSentiments;
use App\Models\FeedbackGeneral;
use App\Models\FeedbackQuestion;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateTrainingReport extends CreateRecord
{
    protected static string $resource = TrainingReportResource::class;

    protected function afterCreate()
    {
        DB::beginTransaction();
        $parsed_file = Storage::disk('local')->path($this->form->getRawState()['parsed_path']);
        $report_information = json_decode(file_get_contents($parsed_file), true);

        // participants
        if (isset($report_information['participants'])) {
            $participants = $report_information['participants'];
            foreach ($participants as $participant) {
                $this->record->trainees()->create([
                    'name' => $participant
                ]);
            }
        }

        if (isset($report_information['general_feedbacks'])) {
            $general_feedbacks = $report_information['general_feedbacks'];
            foreach ($general_feedbacks as $question => $responses) {
                foreach ($responses as $response) {
                    $trainee = $this->record->trainees()->where(['name' => $response['name']])->first();
                    $fquestion = FeedbackQuestion::firstOrCreate(([
                        'question' => $question,
                        'type' => 'open'
                    ]));
                    FeedbackGeneral::create([
                        'trainee_id' => $trainee->id,
                        'feedback_question_id' => $fquestion->id,
                        'response' => $response['response']
                    ]);
                }
            }
        }

        DB::commit();

        GenerateSentiments::dispatch($this->record, Str::uuid());
        Notification::make()->title('Sentiments analysis is added to process queue')
            ->body('This process takes some time to finish. Feel free to do other things while waiting.')
            ->info()
            ->send();
    }
}
