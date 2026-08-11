<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use App\Filament\Resources\TrainingReports\TrainingReportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class TrainingReportsRelationManager extends RelationManager
{
    protected static string $relationship = 'trainingReports';

    protected static ?string $relatedResource = TrainingReportResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
