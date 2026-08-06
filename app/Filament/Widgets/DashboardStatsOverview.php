<?php

namespace App\Filament\Widgets;

use App\Models\Trainer;
use App\Models\TrainingReport;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Training', TrainingReport::count()),
            Stat::make('Active Trainers', Trainer::count()),
            Stat::make('Active Trainers', TrainingReport::sum('total_participants')),
            Stat::make('Avg PSS', TrainingReport::avg('pss_score')),
        ];
    }
}
