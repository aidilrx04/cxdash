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
use Symfony\Component\Process\Process;

class CreateTrainingReport extends CreateRecord
{
    protected static string $resource = TrainingReportResource::class;

    protected function afterCreate()
    {
        $record = $this->record;
        $reportFilePath = Storage::disk('local')->path($record->file_path);

        Storage::disk('local')->makeDirectory('parsed');

        // Path to Python executable in venv & script
        $venvPython = base_path('lib/pdfparser/venv/Scripts/python.exe'); // Use 'venv/Scripts/python.exe' on Windows
        $scriptPath = base_path('lib/pdfparser/report_information.py');

        // Run Python Process
        $process = new Process([$venvPython, $scriptPath, $reportFilePath, Storage::disk('local')->path("parsed/" . basename($record->file_path) . ".json")]);
        $process->run();

        if ($process->isSuccessful()) {
            $extractedData = json_decode(file_get_contents(Storage::disk('local')->path("parsed/" . basename($record->file_path) . ".json")), true);

            if (is_array($extractedData)) {
                // Populate step 2 fields
                $record->update([
                    'file_name' => basename($record->file_path),
                    'program_title' => $extractedData['program_title'] ?? null,
                    'client_name' => $extractedData['client_name'] ?? null,
                    'trainer_name' => $extractedData['trainer_name'] ?? null,
                    'total_participants' => $extractedData['total_participant'] ?? null,
                    'total_evaluation' => $extractedData['total_evaluation'] ?? null,
                    'overall_satisfaction' => $extractedData['overall_satisfaction'] ?? null,
                    'status' => $extractedData['status'] ?? null,
                    'pss_score' => $extractedData['pss_score'] ?? null,
                ]);
            }
        } else {
            logger()->error('PDF Parsing failed: ' . $process->getErrorOutput());
        }

        DB::beginTransaction();
        $parsed_file = Storage::disk('local')->path("parsed/" . basename($record->file_path) . ".json");
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
