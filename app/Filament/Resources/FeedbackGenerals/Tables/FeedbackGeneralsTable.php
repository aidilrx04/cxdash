<?php

namespace App\Filament\Resources\FeedbackGenerals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FeedbackGeneralsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('trainee.name')
                    ->label('Trainee')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user')
                    ->placeholder('Anonymous / Unassigned'),

                TextColumn::make('feedbackQuestion.question')
                    ->label('Question')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(60)
                    ->tooltip(fn($record) => $record->feedbackQuestion?->question),

                TextColumn::make('response')
                    ->label('Response Text')
                    ->searchable()
                    ->wrap()
                    ->limit(75),

                TextColumn::make('sentiment')
                    ->label('Sentiment')
                    ->badge()
                    ->color(fn(?string $state): string => match (strtolower($state ?? '')) {
                        'positive', 'good', 'satisfied' => 'success',
                        'neutral', 'average' => 'warning',
                        'negative', 'bad', 'dissatisfied' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn(?string $state): ?string => match (strtolower($state ?? '')) {
                        'positive', 'good', 'satisfied' => 'heroicon-m-face-smile',
                        'neutral', 'average' => 'heroicon-m-minus-circle',
                        'negative', 'bad', 'dissatisfied' => 'heroicon-m-face-frown',
                        default => null,
                    })
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('theme')
                    ->label('Theme / Topic')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Uncategorized'),

                TextColumn::make('created_at')
                    ->label('Submitted At')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('sentiment')
                    ->label('Filter by Sentiment')
                    ->options([
                        'positive' => 'Positive',
                        'neutral' => 'Neutral',
                        'negative' => 'Negative',
                    ]),

                SelectFilter::make('theme')
                    ->label('Filter by Theme')
                    ->attribute('theme')
                    ->searchable()
                    ->preload(),
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
            ->emptyStateIcon('heroicon-o-chat-bubble-bottom-center-text')
            ->emptyStateHeading('No Feedback Recorded')
            ->emptyStateDescription('Trainee responses, sentiment scores, and extracted themes will appear here once submitted.');
    }
}
