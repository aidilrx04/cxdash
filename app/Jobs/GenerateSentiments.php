<?php

namespace App\Jobs;

use App\Models\TrainingReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GenerateSentiments implements ShouldQueue
{
    use Queueable;

    public TrainingReport $trainingReport;
    public string $outputPath;

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
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
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

        // Output Excel file now exists at $this->outputPath
    }
}
