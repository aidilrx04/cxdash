<?php

namespace App\Filament\Resources\Sentiments;

use App\Filament\Resources\Sentiments\Pages\CreateSentiment;
use App\Filament\Resources\Sentiments\Pages\EditSentiment;
use App\Filament\Resources\Sentiments\Pages\ListSentiments;
use App\Filament\Resources\Sentiments\Schemas\SentimentForm;
use App\Filament\Resources\Sentiments\Tables\SentimentsTable;
use App\Models\Sentiment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SentimentResource extends Resource
{
    protected static ?string $model = Sentiment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'sentiment';

    public static function form(Schema $schema): Schema
    {
        return SentimentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SentimentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSentiments::route('/'),
            'create' => CreateSentiment::route('/create'),
            'edit' => EditSentiment::route('/{record}/edit'),
        ];
    }
}
