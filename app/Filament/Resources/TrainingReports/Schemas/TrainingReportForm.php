<?php

namespace App\Filament\Resources\TrainingReports\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class TrainingReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Training Report File')
                        ->afterValidation(function () {
                            sleep(3);
                        })
                        ->schema([
                            FileUpload::make('file_path')
                                ->disk('local')
                                ->directory('uploads')
                                ->acceptedFileTypes(['application/pdf'])
                                ->required(),
                            Checkbox::make('auto_parse')
                                ->label('Parse file content automatically')
                                ->default(true)
                        ]),
                    Step::make('Training Information')
                        ->schema([
                            TextInput::make('file_name'),
                            TextInput::make('program_title'),
                            TextInput::make('client_name'),
                            TextInput::make('trainer_name'),
                            TextInput::make('total_participants'),
                            TextInput::make('total_evaluation'),
                            TextInput::make('overall_satisfaction'),
                            TextInput::make('status'),
                            TextInput::make('pss_score'),
                        ]),
                ])->submitAction(new HtmlString(Blade::render(<<<BLADE
    <x-filament::button
        type="submit"
        size="sm"
    >
        Submit
    </x-filament::button>
BLADE))),

                // TextInput::make('name'),
                // FileUpload::make('file_path')
                //     ->disk('local')
                //     ->directory('uploads')
                //     ->acceptedFileTypes(['application/pdf'])
            ]);
    }
}
