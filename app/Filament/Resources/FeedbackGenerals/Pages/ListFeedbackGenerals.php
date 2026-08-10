<?php

namespace App\Filament\Resources\FeedbackGenerals\Pages;

use App\Filament\Resources\FeedbackGenerals\FeedbackGeneralResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeedbackGenerals extends ListRecords
{
    protected static string $resource = FeedbackGeneralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
