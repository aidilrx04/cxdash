<?php

namespace App\Filament\Resources\TrainingReports\Pages;

use App\Filament\Resources\TrainingReports\TrainingReportResource;
use App\Filament\Resources\TrainingReports\Widgets\TrainingReportStats;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

class ListTrainingReports extends ListRecords
{
    protected static string $resource = TrainingReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    #[Override]
    public function getHeaderWidgets(): array
    {
        return [
            TrainingReportStats::class
        ];
    }
}
