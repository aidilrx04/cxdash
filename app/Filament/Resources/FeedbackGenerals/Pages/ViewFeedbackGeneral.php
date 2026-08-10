<?php

namespace App\Filament\Resources\FeedbackGenerals\Pages;

use App\Filament\Resources\FeedbackGenerals\FeedbackGeneralResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFeedbackGeneral extends ViewRecord
{
    protected static string $resource = FeedbackGeneralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
