<?php

namespace App\Filament\Resources\Clients\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->withMax('trainingReports', 'total_participants'))
            ->defaultSort('name', 'asc')
            ->striped()
            ->columns([
                TextColumn::make('name')
                    ->label('Client Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-m-building-office'),

                TextColumn::make('training_reports_count')
                    ->label('Total Reports')
                    ->counts('trainingReports')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('training_reports_max_total_participants')
                    ->label('Max Pax / Program')
                    ->numeric()
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->alignCenter()
                    ->placeholder('0 Pax')
                    ->formatStateUsing(fn($state) => $state ? "{$state} Pax" : '0 Pax'),

                TextColumn::make('created_at')
                    ->label('Added Date')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()->iconButton(),
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-building-office')
            ->emptyStateHeading('No Clients Recorded')
            ->emptyStateDescription('Create a new client entry to start tracking training reports and participant metrics.');
    }
}
