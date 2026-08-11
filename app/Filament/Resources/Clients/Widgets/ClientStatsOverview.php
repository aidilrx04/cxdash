<?php

namespace App\Filament\Resources\Clients\Widgets;

use App\Models\Client;
use App\Models\TrainingReport;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ClientStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $now = now();
        $startOfThisMonth = $now->copy()->startOfMonth();
        $endOfThisMonth = $now->copy()->endOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // 1. Total Clients & 12-Month Active Clients Trend
        $totalClients = Client::count();

        // Single query to fetch last 12 months of training reports for sparkline & MoM stats
        $reportsLast12Months = TrainingReport::where('created_at', '>=', $now->copy()->subMonths(11)->startOfMonth())
            ->get(['client_id', 'created_at']);

        $months = collect(range(11, 0))->map(fn($i) => $now->copy()->subMonths($i));
        $monthlyActiveClientsTrend = [];

        foreach ($months as $month) {
            $monthKey = $month->format('Y-m');
            $activeCount = $reportsLast12Months
                ->filter(fn($r) => $r->created_at?->format('Y-m') === $monthKey)
                ->pluck('client_id')
                ->unique()
                ->count();

            $monthlyActiveClientsTrend[] = $activeCount;
        }

        // Active client count comparison (This Month vs Last Month)
        $thisMonthActiveClients = $reportsLast12Months
            ->filter(fn($r) => $r->created_at?->between($startOfThisMonth, $endOfThisMonth))
            ->pluck('client_id')
            ->unique()
            ->count();

        $lastMonthActiveClients = $reportsLast12Months
            ->filter(fn($r) => $r->created_at?->between($startOfLastMonth, $endOfLastMonth))
            ->pluck('client_id')
            ->unique()
            ->count();

        $activeDiff = $thisMonthActiveClients - $lastMonthActiveClients;
        $activeDiffLabel = ($activeDiff >= 0 ? "+{$activeDiff}" : "{$activeDiff}") . " active clients vs last month ({$thisMonthActiveClients} vs {$lastMonthActiveClients})";

        // 2. Repeat Client Rate (clients with > 1 training report)
        $repeatClientsCount = Client::has('trainingReports', '>', 1)->count();
        $repeatRate = $totalClients > 0
            ? round(($repeatClientsCount / $totalClients) * 100, 1)
            : 0;

        // 3. Average PSS Score across all training reports
        $avgPssScore = TrainingReport::avg('pss_score');
        $formattedAvgPss = $avgPssScore !== null ? round((float) $avgPssScore, 2) : 0;

        return [
            Stat::make('Total Clients', number_format($totalClients))
                ->description($activeDiffLabel)
                ->descriptionIcon($activeDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($activeDiff >= 0 ? 'success' : 'danger')
                ->chart($monthlyActiveClientsTrend),

            Stat::make('Repeat Client Rate', "{$repeatRate}%")
                ->description("{$repeatClientsCount} of {$totalClients} clients with >1 training report")
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color($repeatRate >= 40 ? 'success' : ($repeatRate >= 20 ? 'warning' : 'gray')),

            Stat::make('Average PSS Score', $formattedAvgPss > 0 ? "{$formattedAvgPss}" : 'N/A')
                ->description('Mean satisfaction score across training reports')
                ->descriptionIcon('heroicon-m-star')
                ->color($formattedAvgPss >= 4.0 ? 'success' : ($formattedAvgPss >= 3.0 ? 'warning' : 'danger')),
        ];
    }
}
