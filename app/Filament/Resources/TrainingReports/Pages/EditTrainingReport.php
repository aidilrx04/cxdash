<?php

namespace App\Filament\Resources\TrainingReports\Pages;

use App\Filament\Resources\TrainingReports\TrainingReportResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTrainingReport extends EditRecord
{
    protected static string $resource = TrainingReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
