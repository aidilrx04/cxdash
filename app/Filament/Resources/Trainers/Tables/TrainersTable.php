<?php

namespace App\Filament\Resources\Trainers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\QueryBuilder\Constraints\NumberConstraint;
use Filament\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Table;

class TrainersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('profile_picture')
                    ->label('Picture')
                    ->grow(false),
                TextColumn::make('full_name'),
                TextColumn::make('email'),
                TextColumn::make('phone_number'),
                TextColumn::make('years_experience'),
                TextColumn::make('profile_picture'),
                TextColumn::make('notable_clients'),
                TextColumn::make('avg_evaluation_score'),
                TextColumn::make('fee_structure'),
                TextColumn::make('professional_summary'),
                TextColumn::make('additional_info'),
                TextColumn::make('cv_path'),
            ])
            ->filters([
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::Make('full_name'),
                        TextConstraint::Make('email'),
                        TextConstraint::Make('phone_number'),
                        NumberConstraint::Make('years_experience'),
                        TextConstraint::Make('notable_clients'),
                        NumberConstraint::Make('avg_evaluation_score'),
                        NumberConstraint::Make('fee_structure'),
                        TextConstraint::Make('professional_summary'),
                        TextConstraint::Make('additional_info'),
                        TextConstraint::Make('cv_path'),
                        TextConstraint::make('socialMedia.platform'),
                        TextConstraint::make('socialMedia.url'),
                    ]),
            ], FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->searchable()
        ;
    }
}
