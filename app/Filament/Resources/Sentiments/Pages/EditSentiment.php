<?php

namespace App\Filament\Resources\Sentiments\Pages;

use App\Filament\Resources\Sentiments\SentimentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSentiment extends EditRecord
{
    protected static string $resource = SentimentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
