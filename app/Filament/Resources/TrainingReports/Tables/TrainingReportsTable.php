<?php

namespace App\Filament\Resources\TrainingReports\Tables;

use App\Models\TrainingReport;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class TrainingReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('program_title')
                    ->label('Program / Client')
                    ->sortable()
                    ->searchable()
                    ->weight('bold')
                    ->description(fn(TrainingReport $record): string => $record->client_name ?? 'N/A')
                    ->wrap(),

                TextColumn::make('trainer_name')
                    ->label('Trainer')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-m-user-circle'),

                TextColumn::make('total_participants')
                    ->label('Participants')
                    ->sortable()
                    ->numeric()
                    ->alignCenter()
                    ->description(fn(TrainingReport $record): string => "{$record->total_evaluation} evaluated"),

                TextColumn::make('overall_satisfaction')
                    ->label('Satisfaction')
                    ->sortable()
                    ->badge()
                    ->color(function ($state): string {
                        $val = (float) str_replace('%', '', (string) $state);

                        return match (true) {
                            $val >= 80 => 'success',
                            $val >= 60 => 'warning',
                            $val > 0 => 'danger',
                            default => 'gray',
                        };
                    })
                    ->alignCenter(),

                TextColumn::make('pss_score')
                    ->label('PSS Score')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->placeholder('N/A')
                    ->alignCenter()
                    ->toggleable()
                    ->numeric(2),

                TextColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color(fn(?string $state): string => match (strtolower((string) $state)) {
                        'completed', 'approved', 'final' => 'success',
                        'pending', 'in progress', 'draft' => 'warning',
                        'rejected', 'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->alignCenter(),

                TextColumn::make('file_path')
                    ->label('Report File')
                    ->formatStateUsing(fn($state) => $state ? 'Download' : 'No File')
                    ->icon(fn($state) => $state ? 'heroicon-m-document-arrow-down' : 'heroicon-m-x-circle')
                    ->color(fn($state) => $state ? 'primary' : 'gray')
                    ->url(fn(TrainingReport $record) => $record->file_path ? Storage::disk('public')->path($record->file_path) : null, shouldOpenInNewTab: true)
                    ->badge()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created Date')
                    ->sortable()
                    ->date('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('date')
                    ->schema([
                        DatePicker::make('date_start')
                            ->label('From Date')
                            ->native(false),
                        DatePicker::make('date_end')
                            ->label('To Date')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['date_start'], fn(Builder $q) => $q->whereDate('created_at', '>=', $data['date_start']))
                            ->when($data['date_end'], fn(Builder $q) => $q->whereDate('created_at', '<=', $data['date_end']));
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if ($data['date_start'] && $data['date_end']) {
                            return 'Date: ' . Carbon::parse($data['date_start'])->format('d M Y') . ' to ' . Carbon::parse($data['date_end'])->format('d M Y');
                        }
                        if ($data['date_start']) {
                            return 'Date From: ' . Carbon::parse($data['date_start'])->format('d M Y');
                        }
                        if ($data['date_end']) {
                            return 'Date To: ' . Carbon::parse($data['date_end'])->format('d M Y');
                        }

                        return null;
                    }),

                SelectFilter::make('trainer_name')
                    ->label('Filter by Trainer')
                    ->options(fn() => TrainingReport::query()->whereNotNull('trainer_name')->pluck('trainer_name', 'trainer_name'))
                    ->multiple()
                    ->preload()
                    ->searchable(),

                SelectFilter::make('client_name')
                    ->label('Filter by Client')
                    ->options(fn() => TrainingReport::query()->whereNotNull('client_name')->pluck('client_name', 'client_name'))
                    ->multiple()
                    ->preload()
                    ->searchable(),
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
            ->emptyStateIcon('heroicon-o-document-chart-bar')
            ->emptyStateHeading('No Training Reports Found')
            ->emptyStateDescription('Upload or process a training report to start tracking performance metrics.');
    }
}
