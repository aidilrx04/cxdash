<?php

namespace App\Filament\Resources\TrainingReports\Pages;

use App\Filament\Resources\TrainingReports\TrainingReportResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTrainingReport extends ViewRecord
{
    protected static string $resource = TrainingReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
