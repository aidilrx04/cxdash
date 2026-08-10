<?php

namespace App\Filament\Resources\TrainingReports;

use App\Filament\Resources\TrainingReports\Pages\CreateTrainingReport;
use App\Filament\Resources\TrainingReports\Pages\EditTrainingReport;
use App\Filament\Resources\TrainingReports\Pages\ListTrainingReports;
use App\Filament\Resources\TrainingReports\Pages\ViewTrainingReport;
use App\Filament\Resources\TrainingReports\RelationManagers\FeedbackGeneralsRelationManager;
use App\Filament\Resources\TrainingReports\RelationManagers\SentimentsRelationManager;
use App\Filament\Resources\TrainingReports\RelationManagers\TraineesRelationManager;
use App\Filament\Resources\TrainingReports\Schemas\TrainingReportForm;
use App\Filament\Resources\TrainingReports\Schemas\TrainingReportInfolist;
use App\Filament\Resources\TrainingReports\Tables\TrainingReportsTable;
use App\Models\TrainingReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TrainingReportResource extends Resource
{
    protected static ?string $model = TrainingReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TrainingReportForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TrainingReportInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrainingReportsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            TraineesRelationManager::class,
            // SentimentsRelationManager::class,
            FeedbackGeneralsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrainingReports::route('/'),
            'create' => CreateTrainingReport::route('/create'),
            'view' => ViewTrainingReport::route('/{record}'),
            'edit' => EditTrainingReport::route('/{record}/edit'),
        ];
    }
}
