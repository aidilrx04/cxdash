<?php

namespace App\Jobs;

use App\Filament\Resources\TrainingReports\TrainingReportResource;
use App\Models\FeedbackGeneral;
use App\Models\FeedbackQuestion;
use App\Models\Trainee;
use App\Models\TrainingReport;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelReader;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GenerateSentiments implements ShouldQueue
{
    use Queueable;

    public TrainingReport $trainingReport;
    public string $outputPath;
    public User $user;

    private $isTest = false;

    /**
     * Set a generous timeout (e.g., 10 minutes) for heavy NLP Python execution.
     */
    public int $timeout = 0;

    /**
     * Create a new job instance.
     */
    public function __construct(TrainingReport $trainingReport, string $id)
    {
        $this->trainingReport = $trainingReport;

        // Ensure destination folder exists inside storage/app/private/sentiments
        Storage::disk('local')->makeDirectory('sentiments');

        $this->outputPath = Storage::disk('local')->path("sentiments/{$id}.xlsx");

        $this->user = filament()->auth()->user();

        if ($this->isTest) {
            $this->outputPath = Storage::disk('local')->path(Storage::disk('local')->files('sentiments')[0]);
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->isTest === false) {

            // 1. Resolve absolute path of input report file
            $inputPath = realpath(Storage::disk('local')->path($this->trainingReport->file_path));

            // 2. Define process command array
            // Adjust the python binary path (e.g., venv python) if necessary
            $pythonBinary = 'C:/users/aidilrx04/Desktop/projects/sentimentjer/venv/Scripts/python.exe'; // or 'C:/dev/sentimentjer/venv/Scripts/python.exe'
            $scriptPath = 'C:/users/aidilrx04/Desktop/projects/sentimentjer/src/main.py';

            $process = new Process([
                $pythonBinary,
                $scriptPath,
                $inputPath,
                $this->outputPath,
            ]);
            Log::debug($process->getCommandLine());

            // Set process execution timeout matching job timeout
            $process->setTimeout(null);

            // 3. Synchronously run script and wait until complete
            $process->run();

            // 4. Check outcome
            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $output = $process->getOutput();
            Log::debug($output);
        }


        // Output Excel file now exists at $this->outputPath
        $rows = SimpleExcelReader::create($this->outputPath)->noHeaderRow()->getRows();
        Log::debug($rows);

        $firstRow = $rows->first();
        $questions = collect([
            2,
            6,
            10,
            14,
        ])
            ->map(function ($col) use ($firstRow) {
                return [
                    'question' => FeedbackQuestion::firstWhere('question', $firstRow[$col]),
                    'sentiment' => $col + 1,
                    'theme' => $col + 2,
                ];
            });

        DB::beginTransaction();
        $rows->each(function ($row, $key) use ($questions) {
            if ($key == 0) return;
            $trainee = Trainee::where('training_report_id', $this->trainingReport->id)->where('name', $row[1])->first();
            if (!$trainee) {
                Log::error("Can't Find trainee", compact('row'));
                return;
            }

            foreach ($questions as $question) {
                FeedbackGeneral::where('trainee_id', $trainee->id)->where('feedback_question_id', $question['question']->id)->update([
                    'sentiment' => $row[$question['sentiment']],
                    'theme' => $row[$question['theme']],
                ]);
            }
        });
        DB::commit();

        Notification::make()
            ->title('Sentiment Analysis completed')
            ->actions([
                Action::make('view')
                    ->url(TrainingReportResource::getUrl('view', ['record' => $this->trainingReport, 'relation' => 2]))
            ])
            ->success()
            ->sendToDatabase($this->user);
    }
}
