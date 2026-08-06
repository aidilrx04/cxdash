<?php

namespace App\Filament\Widgets;

use App\Models\TrainingReport;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TopClientPssScore extends TableWidget
{
    public string $sortOrder = 'highest';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Top Client Score this month')
            ->query(
                fn(): Builder => TrainingReport::query()
                    ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->orderByRaw("CAST(NULLIF(pss_score, '') AS DECIMAL) " . ($this->sortOrder === 'lowest' ? 'ASC' : 'DESC'))
                    ->limit(10)
            )
            ->defaultSort('pss_score', 'desc')
            ->paginated(false)
            ->columns([
                TextColumn::make('client_name'),
                TextColumn::make('pss_score')
                    ->color(fn($state) => (float)$state >= 80 ? 'success' : ((float)$state >= 50 ? 'warning' : 'danger'))
                // ->sortable(),
            ])
            ->filters([
                // Filter::make('score_score')
                //     // ->label('')
                //     ->label('PSS Score')
                //     ->indicateUsing(function (array $data) {
                //         return 'PSS Score: ' . $data['pss_score'];
                //     })
                //     ->schema([
                //         Select::make('pss_score')
                //             ->options([
                //                 'highest_month' => 'Highest This Month',
                //                 'lowest_month' => 'Lowest This Month',
                //                 'highest_alltime' => 'Highest All Time',
                //                 'lowest_alltime' => ':owest All Time',
                //             ])
                //             ->default('highest_month')
                //             ->native(false)
                //     ])
            ])
            ->headerActions([
                Action::make('highest')
                    ->label('Highest')
                    ->icon('heroicon-m-arrow-trending-up')
                    ->button()
                    ->color(fn(): string => $this->sortOrder === 'highest' ? 'primary' : 'gray')
                    ->action(fn() => $this->sortOrder = 'highest'),

                Action::make('lowest')
                    ->label('Lowest')
                    ->icon('heroicon-m-arrow-trending-down')
                    ->button()
                    ->color(fn(): string => $this->sortOrder === 'lowest' ? 'primary' : 'gray')
                    ->action(fn() => $this->sortOrder = 'lowest'),
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
