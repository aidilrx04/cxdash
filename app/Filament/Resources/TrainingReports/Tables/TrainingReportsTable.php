<?php

namespace App\Filament\Resources\TrainingReports\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TrainingReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('file_name')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('program_title'),
                TextColumn::make('client_name'),
                TextColumn::make('trainer_name'),
                TextColumn::make('total_participants'),
                TextColumn::make('total_evaluation'),
                TextColumn::make('overall_satisfaction'),
                TextColumn::make('status'),
                TextColumn::make('pss_score'),
                TextColumn::make('file_path'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
