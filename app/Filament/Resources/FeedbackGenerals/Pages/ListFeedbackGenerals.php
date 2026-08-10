<?php

namespace App\Filament\Resources\FeedbackGenerals\Pages;

use App\Filament\Resources\FeedbackGenerals\FeedbackGeneralResource;
use App\Filament\Resources\FeedbackGenerals\Widgets\FeedbackGeneralStatsOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

class ListFeedbackGenerals extends ListRecords
{
    protected static string $resource = FeedbackGeneralResource::class;

    #[Override]
    public function getHeaderWidgets(): array
    {
        return [
            FeedbackGeneralStatsOverview::class
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
