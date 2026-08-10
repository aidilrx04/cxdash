<?php

namespace App\Filament\Resources\Trainers\Tables;

use App\Models\Trainer;
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
use Illuminate\Support\Facades\Storage;

class TrainersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('profile_picture')
                    ->label('')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl('https://ui-avatars.com/api/?background=random')
                    ->imageSize(45),

                TextColumn::make('full_name')
                    ->label('Trainer')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn(Trainer $record): string => $record->professional_summary
                        ? str($record->professional_summary)->limit(40)
                        : 'No summary provided'),

                TextColumn::make('email')
                    ->label('Contact Info')
                    ->icon('heroicon-m-envelope')
                    ->copyable()
                    ->copyMessage('Email copied to clipboard')
                    ->searchable()
                    ->description(fn(Trainer $record): ?string => $record->phone_number),

                TextColumn::make('years_experience')
                    ->label('Experience')
                    ->sortable()
                    ->numeric()
                    ->suffix(' yrs')
                    ->badge()
                    ->color('gray')
                    ->alignCenter(),

                TextColumn::make('avg_evaluation_score')
                    ->label('Rating')
                    ->sortable()
                    ->numeric(decimalPlaces: 1)
                    ->badge()
                    ->icon('heroicon-m-star')
                    ->color(fn(?float $state): string => match (true) {
                        $state >= 4.5 => 'success',
                        $state >= 3.5 => 'warning',
                        $state > 0 => 'danger',
                        default => 'gray',
                    })
                    ->placeholder('N/A')
                    ->alignCenter(),

                TextColumn::make('fee_structure')
                    ->label('Fee Rate')
                    ->sortable()
                    ->money('MYR') // Adjust currency code as needed (e.g., 'USD', 'MYR')
                    ->placeholder('N/A')
                    ->toggleable(),

                TextColumn::make('notable_clients')
                    ->label('Clients')
                    ->badge()
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('cv_path')
                    ->label('CV / Resume')
                    ->formatStateUsing(fn($state) => $state ? 'Download' : 'Missing')
                    ->icon(fn($state) => $state ? 'heroicon-m-document-arrow-down' : 'heroicon-m-x-circle')
                    ->color(fn($state) => $state ? 'primary' : 'gray')
                    ->url(fn(Trainer $record) => $record->cv_path ? Storage::disk('public')->path($record->cv_path) : null, shouldOpenInNewTab: true)
                    ->badge()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->filters([
                QueryBuilder::make()
                    ->constraints([
                        TextConstraint::make('full_name'),
                        TextConstraint::make('email'),
                        TextConstraint::make('phone_number'),
                        NumberConstraint::make('years_experience'),
                        TextConstraint::make('notable_clients'),
                        NumberConstraint::make('avg_evaluation_score'),
                        NumberConstraint::make('fee_structure'),
                        TextConstraint::make('professional_summary'),
                        TextConstraint::make('additional_info'),
                        TextConstraint::make('socialMedia.platform'),
                        TextConstraint::make('socialMedia.url'),
                    ]),
            ], FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                ViewAction::make()->iconButton(),
                EditAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-academic-cap')
            ->emptyStateHeading('No Trainers Registered')
            ->emptyStateDescription('Start adding trainers to track their evaluations, CVs, and fee structures.');
    }
}
