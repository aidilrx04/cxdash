<?php

namespace App\Filament\Resources\TrainingReports\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Symfony\Component\Process\Process;

class TrainingReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Wizard::make([
                // Step::make('Training Report File')
                //     ->afterValidation(function (Get $get, Set $set) {
                //         // dd($get('file_path'));

                //         if (! $get('auto_parse')) {
                //             return;
                //         }

                //         $files = $get('file_path');

                //         if (empty($files)) {
                //             return;
                //         }

                //         // Extract the TemporaryUploadedFile instance from the array
                //         $file = is_array($files) ? array_values($files)[0] : $files;

                //         // Get absolute temporary file path or disk path
                //         if ($file instanceof TemporaryUploadedFile) {
                //             $absoluteFilePath = $file->getRealPath();
                //             $originalName = $file->getClientOriginalName();
                //         } else {
                //             // If editing an existing record where the file path is already a saved string
                //             $absoluteFilePath = storage_path('app/' . $file);
                //             $originalName = basename($file);
                //         }

                //         Storage::disk('local')->makeDirectory('parsed');

                //         // Path to Python executable in venv & script
                //         $venvPython = base_path('lib/pdfparser/venv/Scripts/python.exe'); // Use 'venv/Scripts/python.exe' on Windows
                //         $scriptPath = base_path('lib/pdfparser/report_information.py');

                //         // Run Python Process
                //         $process = new Process([$venvPython, $scriptPath, $absoluteFilePath, Storage::disk('local')->path("parsed/" . basename($absoluteFilePath) . ".json")]);
                //         $process->run();

                //         if ($process->isSuccessful()) {
                //             $extractedData = json_decode(file_get_contents(Storage::disk('local')->path("parsed/" . basename($absoluteFilePath) . ".json")), true);

                //             if (is_array($extractedData)) {
                //                 // Populate step 2 fields
                //                 $set('file_name', $originalName);
                //                 $set('program_title', $extractedData['program_title'] ?? null);
                //                 $set('client_name', $extractedData['client_name'] ?? null);
                //                 $set('trainer_name', $extractedData['trainer_name'] ?? null);
                //                 $set('total_participants', $extractedData['total_participant'] ?? null);
                //                 $set('total_evaluation', $extractedData['total_evaluation'] ?? null);
                //                 $set('overall_satisfaction', $extractedData['overall_satisfaction'] ?? null);
                //                 $set('status', $extractedData['status'] ?? null);
                //                 $set('pss_score', $extractedData['pss_score'] ?? null);
                //                 $set('parsed_path', "parsed/" . basename($absoluteFilePath) . ".json");
                //             }
                //         } else {
                //             logger()->error('PDF Parsing failed: ' . $process->getErrorOutput());
                //         }
                //     })
                //     ->schema([
                FileUpload::make('file_path')
                    ->label('Training Report File')
                    ->disk('local')
                    ->directory('uploads')
                    ->acceptedFileTypes(['application/pdf'])
                    ->required()
                    ->columnSpanFull()
                    ->hiddenOn('edit'),
                // Checkbox::make('auto_parse')
                //     ->label('Parse file content automatically')
                //     ->default(true),
                //     ]),
                // Step::make('Training Information')
                //     ->schema([
                TextInput::make('file_name')
                    ->hiddenOn('create'),
                TextInput::make('program_title')
                    ->hiddenOn('create'),
                TextInput::make('client_name')
                    ->hiddenOn('create'),
                TextInput::make('trainer_name')
                    ->hiddenOn('create'),
                TextInput::make('total_participants')
                    ->hiddenOn('create'),
                TextInput::make('total_evaluation')
                    ->hiddenOn('create'),
                TextInput::make('overall_satisfaction')
                    ->hiddenOn('create'),
                TextInput::make('status')
                    ->hiddenOn('create'),
                TextInput::make('pss_score')
                    ->hiddenOn('create')
                // ]),
                //                 ])->submitAction(new HtmlString(Blade::render(<<<BLADE
                //     <x-filament::button
                //         type="submit"
                //         size="sm"
                //     >
                //         Submit
                //     </x-filament::button>
                // BLADE))),

                // TextInput::make('name'),
                // FileUpload::make('file_path')
                //     ->disk('local')
                //     ->directory('uploads')
                //     ->acceptedFileTypes(['application/pdf'])
            ]);
    }
}
