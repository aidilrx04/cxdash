<?php

namespace App\Filament\Resources\TrainingReports\Tables;

use App\Models\TrainingReport;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class TrainingReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('file_name')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('program_title')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('client_name')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('trainer_name')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('total_participants')
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('total_evaluation')
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('overall_satisfaction')
                    ->toggleable()
                    ->sortable()
                    ->color(fn($state) => (float)substr($state, 0, -1) >= 80 ? 'success' : ((float)substr($state, 0, -1) >= 60 ? 'warning' : 'danger')),
                TextColumn::make('status')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('pss_score')
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('file_path')
                    ->toggleable()
                    ->hidden(),
                TextColumn::make('created_at')
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->date(),
            ])
            ->filters([
                Filter::make('date')
                    ->schema([
                        DatePicker::make('date_start')
                            ->native(false),
                        DatePicker::make('date_end')
                            ->native(false)
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['date_start'], fn(Builder $q) => $q->where('created_at', '>=', $data['date_start']))
                            ->when($data['date_end'], fn(Builder $q) => $q->where('created_at', '<=', $data['date_end']));
                    })
                    ->indicateUsing(function (array $data) {
                        $date_start = $data['date_start'];
                        $date_end = $data['date_end'];
                        if ($date_start) {
                            $date_start = Carbon::create($date_start)->format('Y-m-d');
                        }
                        if ($date_end) {
                            $date_end = Carbon::create($date_end)->format('Y-m-d');
                        }
                        if ($date_start && $date_end) {
                            return "Date: {$date_start} to {$date_end}";
                        }
                        if ($date_start) {
                            return "Date Start: {$date_start}";
                        }
                        if ($date_end) {
                            return "Date End: {$date_end}";
                        }
                    }),
                SelectFilter::make('trainer_name')
                    ->options(fn() => TrainingReport::select('trainer_name')->distinct()->pluck('trainer_name'))
                    ->multiple()
                    ->preload()
                    ->searchable(),
                SelectFilter::make('client_name')
                    ->options(fn() => TrainingReport::select('client_name')->distinct()->pluck('client_name', 'client_name'))
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ], FiltersLayout::AboveContentCollapsible)
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
