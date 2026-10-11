<?php

namespace App\Http\Controllers;

use App\Models\TrainingReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function submitGeneratedReport(Request $request)
    {
        logger()->info('', $request->all());
        $request->validate([
            'report' => 'required|file|mimes:pdf|max:10240', // Validate PDF max 10MB,
            'id' => 'required|numeric'
        ]);

        // 1. Save file to storage/app/training-reports with a random filename
        $uploadedFile = $request->file('report');
        $fileName = Str::random(40) . '.pdf';
        $relativePath = $uploadedFile->storeAs('training-reports', $fileName);

        // Get absolute path required for shell execution
        $absolutePath = Storage::path($relativePath);

        /**
         * @var TrainingReport
         */
        $report = TrainingReport::findOrFail($request->input('id'));

        $complete_report = Storage::disk('public')->path($report->file_path);

        $out_filename = "uploads/" . Str::random(32) . 'pdf';
        $output_path = Storage::disk('public')->path($out_filename);

        // 2. Run the process synchronously and wait for it to complete
        $result = Process::run(sprintf(
            '%s %s %s %s %s',
            escapeshellarg(base_path('lib/pdfparser/venv/Scripts/python.exe')),
            escapeshellarg(base_path('lib/pdfparser/create_report.py')),
            escapeshellarg($absolutePath),
            escapeshellarg($complete_report),
            escapeshellarg($output_path)
        ));

        logger()->info($result->command());

        logger()->info('', [$result->output(), $result->errorOutput()]);

        // Check if the process failed
        if ($result->failed()) {
            return response()->json([
                'error' => 'Report processing failed.',
                'details' => $result->errorOutput()
            ], 500);
        }

        // 3. Return output of the process
        $downloadPath = trim($result->output());

        return response()->json([
            'downloadPath' => asset('storage/' . $out_filename)
        ]);
    }
}
