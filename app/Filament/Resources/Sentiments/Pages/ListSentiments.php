<?php

namespace App\Filament\Resources\Sentiments\Pages;

use App\Filament\Resources\Sentiments\SentimentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSentiments extends ListRecords
{
    protected static string $resource = SentimentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
