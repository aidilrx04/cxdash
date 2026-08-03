<?php

namespace App\Filament\Resources\TrainingReports\Pages;

use App\Filament\Resources\TrainingReports\TrainingReportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrainingReports extends ListRecords
{
    protected static string $resource = TrainingReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
