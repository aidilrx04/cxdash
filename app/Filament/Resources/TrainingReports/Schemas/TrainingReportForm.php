<?php

namespace App\Filament\Resources\TrainingReports\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TrainingReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name'),
                FileUpload::make('file_path')
                    ->disk('local')
                    ->directory('uploads')
                    ->acceptedFileTypes(['application/pdf'])
            ]);
    }
}
