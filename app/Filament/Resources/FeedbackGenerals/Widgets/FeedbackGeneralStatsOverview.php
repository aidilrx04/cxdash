<?php

namespace App\Filament\Resources\FeedbackGenerals\Widgets;

use App\Models\FeedbackGeneral;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class FeedbackGeneralStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $now = now();
        $startOfThisMonth = $now->copy()->startOfMonth();
        $endOfThisMonth = $now->copy()->endOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // 12-month rolling date range
        $months = collect(range(11, 0))->map(fn($i) => $now->copy()->subMonths($i));

        // Single query for last 12 months to optimize performance
        $records = FeedbackGeneral::where('created_at', '>=', $now->copy()->subMonths(11)->startOfMonth())
            ->get(['created_at', 'sentiment', 'theme']);

        // Build 12-month sparkline data arrays
        $totalTrend = [];
        $positiveTrend = [];
        $negativeTrend = [];
        $themeTrend = [];

        foreach ($months as $month) {
            $monthKey = $month->format('Y-m');
            $monthRecords = $records->filter(fn($r) => $r->created_at?->format('Y-m') === $monthKey);

            $mTotal = $monthRecords->count();
            $mPos = $monthRecords->whereIn('sentiment', ['positive', 'good', 'satisfied'])->count();
            $mNeg = $monthRecords->whereIn('sentiment', ['negative', 'bad', 'dissatisfied'])->count();
            $mThemes = $monthRecords->pluck('theme')->filter()->unique()->count();

            $totalTrend[] = $mTotal;
            $positiveTrend[] = $mTotal > 0 ? round(($mPos / $mTotal) * 100) : 0;
            $negativeTrend[] = $mNeg;
            $themeTrend[] = $mThemes;
        }

        // Current Month vs Last Month Dataset Filtering
        $thisMonthRecords = $records->filter(fn($r) => $r->created_at?->between($startOfThisMonth, $endOfThisMonth));
        $lastMonthRecords = $records->filter(fn($r) => $r->created_at?->between($startOfLastMonth, $endOfLastMonth));

        // 1. Total Responses Comparison
        $thisMonthTotal = $thisMonthRecords->count();
        $lastMonthTotal = $lastMonthRecords->count();
        $totalDiff = $thisMonthTotal - $lastMonthTotal;
        $totalDiffLabel = ($totalDiff >= 0 ? "+{$totalDiff}" : "{$totalDiff}") . ' from last month';

        // 2. Positive Satisfaction Rate Comparison
        $thisMonthPos = $thisMonthRecords->whereIn('sentiment', ['positive', 'good', 'satisfied'])->count();
        $lastMonthPos = $lastMonthRecords->whereIn('sentiment', ['positive', 'good', 'satisfied'])->count();

        $thisMonthPosRate = $thisMonthTotal > 0 ? round(($thisMonthPos / $thisMonthTotal) * 100, 1) : 0;
        $lastMonthPosRate = $lastMonthTotal > 0 ? round(($lastMonthPos / $lastMonthTotal) * 100, 1) : 0;
        $posRateDiff = round($thisMonthPosRate - $lastMonthPosRate, 1);
        $posRateDiffLabel = ($posRateDiff >= 0 ? "+{$posRateDiff}%" : "{$posRateDiff}%") . ' from last month';

        // 3. Negative Feedback Comparison
        $thisMonthNeg = $thisMonthRecords->whereIn('sentiment', ['negative', 'bad', 'dissatisfied'])->count();
        $lastMonthNeg = $lastMonthRecords->whereIn('sentiment', ['negative', 'bad', 'dissatisfied'])->count();
        $negDiff = $thisMonthNeg - $lastMonthNeg;
        $negDiffLabel = ($negDiff >= 0 ? "+{$negDiff}" : "{$negDiff}") . ' from last month';

        // 4. Identified Themes Comparison
        $thisMonthThemes = $thisMonthRecords->pluck('theme')->filter()->unique()->count();
        $lastMonthThemes = $lastMonthRecords->pluck('theme')->filter()->unique()->count();
        $themeDiff = $thisMonthThemes - $lastMonthThemes;
        $themeDiffLabel = ($themeDiff >= 0 ? "+{$themeDiff}" : "{$themeDiff}") . ' from last month';

        return [
            Stat::make('Total Responses', number_format($thisMonthTotal))
                ->description($totalDiffLabel)
                ->descriptionIcon($totalDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($totalDiff >= 0 ? 'success' : 'danger')
                ->chart($totalTrend),

            Stat::make('Positive Satisfaction Rate', "{$thisMonthPosRate}%")
                ->description($posRateDiffLabel)
                ->descriptionIcon($posRateDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($posRateDiff >= 0 ? 'success' : 'danger')
                ->chart($positiveTrend),

            Stat::make('Negative Feedback', number_format($thisMonthNeg))
                ->description($negDiffLabel)
                ->descriptionIcon($negDiff <= 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-arrow-trending-up')
                ->color($negDiff <= 0 ? 'success' : 'danger') // Drop in negative feedback is marked green (success)
                ->chart($negativeTrend),

            Stat::make('Identified Themes', number_format($thisMonthThemes))
                ->description($themeDiffLabel)
                ->descriptionIcon($themeDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('info')
                ->chart($themeTrend),
        ];
    }
}
