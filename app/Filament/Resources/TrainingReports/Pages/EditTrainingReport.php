<?php

namespace App\Filament\Resources\TrainingReports\Pages;

use App\Filament\Resources\TrainingReports\TrainingReportResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;

class EditTrainingReport extends EditRecord
{
    protected static string $resource = TrainingReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Download Report')
                ->color(Color::Blue)
                ->icon(Heroicon::CloudArrowDown)
                ->extraAttributes(function ($record) {
                    return [
                        'data-generate-report' => true,
                        'data-executive-summary' => e($record->executive_summary),
                        'data-id' => $record->id,
                        'id' => 'cx-generate-report'
                    ];
                }),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
