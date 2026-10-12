<?php

namespace App\Filament\Resources\TrainingReports\Pages;

use App\Filament\Resources\TrainingReports\TrainingReportResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class ViewTrainingReport extends ViewRecord
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
            EditAction::make(),
        ];
    }

    public function getTitle(): string | Htmlable
    {
        return $this->getRecordTitle();
    }
}
