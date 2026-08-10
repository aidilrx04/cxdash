<?php

namespace App\Filament\Resources\FeedbackGenerals\Pages;

use App\Filament\Resources\FeedbackGenerals\FeedbackGeneralResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditFeedbackGeneral extends EditRecord
{
    protected static string $resource = FeedbackGeneralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
