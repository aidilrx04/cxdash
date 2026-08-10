<?php

namespace App\Filament\Resources\FeedbackGenerals;

use App\Filament\Resources\FeedbackGenerals\Pages\CreateFeedbackGeneral;
use App\Filament\Resources\FeedbackGenerals\Pages\EditFeedbackGeneral;
use App\Filament\Resources\FeedbackGenerals\Pages\ListFeedbackGenerals;
use App\Filament\Resources\FeedbackGenerals\Pages\ViewFeedbackGeneral;
use App\Filament\Resources\FeedbackGenerals\Schemas\FeedbackGeneralForm;
use App\Filament\Resources\FeedbackGenerals\Schemas\FeedbackGeneralInfolist;
use App\Filament\Resources\FeedbackGenerals\Tables\FeedbackGeneralsTable;
use App\Models\FeedbackGeneral;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FeedbackGeneralResource extends Resource
{
    protected static ?string $model = FeedbackGeneral::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    protected static ?string $recordTitleAttribute = 'response';

    public static function form(Schema $schema): Schema
    {
        return FeedbackGeneralForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FeedbackGeneralInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeedbackGeneralsTable::configure($table);
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
            'index' => ListFeedbackGenerals::route('/'),
            'create' => CreateFeedbackGeneral::route('/create'),
            'view' => ViewFeedbackGeneral::route('/{record}'),
            'edit' => EditFeedbackGeneral::route('/{record}/edit'),
        ];
    }
}
