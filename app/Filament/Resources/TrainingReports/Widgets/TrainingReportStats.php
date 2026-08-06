<?php

namespace App\Filament\Resources\TrainingReports\Widgets;

use App\Models\TrainingReport;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TrainingReportStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $monthlyCounts = TrainingReport::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month_year, COUNT(*) as aggregate")
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month_year')
            ->pluck('aggregate', 'month_year');

        // 2. Map across all 12 months to fill missing months with 0
        $chartData = collect(range(11, 0))->map(function ($monthOffset) use ($monthlyCounts) {
            $monthKey = now()->subMonths($monthOffset)->format('Y-m');

            return $monthlyCounts->get($monthKey, 0);
        })->toArray();

        // 3. Extract Current and Previous Month Counts
        $currentMonthKey = now()->format('Y-m');
        $lastMonthKey = now()->subMonth()->format('Y-m');

        $currentMonthCount = $monthlyCounts->get($currentMonthKey, 0);
        $lastMonthCount = $monthlyCounts->get($lastMonthKey, 0);

        // 4. Calculate Difference and Percentage Change
        $diff = $currentMonthCount - $lastMonthCount;

        if ($lastMonthCount > 0) {
            $percentage = round(($diff / $lastMonthCount) * 100, 1);
        } else {
            $percentage = $currentMonthCount > 0 ? 100 : 0;
        }

        // 5. Determine Dynamic Styling & Labels
        if ($diff > 0) {
            $description = "+{$diff} (" . abs($percentage) . "%) increase from last month";
            $icon = 'heroicon-m-arrow-trending-up';
            $color = 'success'; // Green
        } elseif ($diff < 0) {
            $description = "{$diff} (" . abs($percentage) . "%) decrease from last month";
            $icon = 'heroicon-m-arrow-trending-down';
            $color = 'danger'; // Red
        } else {
            $description = 'No change from last month';
            $icon = 'heroicon-m-minus';
            $color = 'gray'; // Gray / Neutral
        }

        return [
            Stat::make('Monthly Reports', TrainingReport::count())
                ->description($description)
                ->descriptionIcon($icon)
                ->chart($chartData)
                ->color($color),
            $this->getPssStat()
        ];
    }

    private function getPssStat()
    {
        // 1. Fetch monthly average PSS scores for the past 12 months
        $monthlyPss = TrainingReport::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month_year, AVG(pss_score) as aggregate")
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month_year')
            ->pluck('aggregate', 'month_year');

        // 2. Map 12 months for the chart trendline (rounded to 2 decimal places)
        $pssChartData = collect(range(11, 0))->map(function ($monthOffset) use ($monthlyPss) {
            $monthKey = now()->subMonths($monthOffset)->format('Y-m');

            return round((float) $monthlyPss->get($monthKey, 0), 2);
        })->toArray();

        // 3. Extract Current and Previous Month Averages
        $currentMonthKey = now()->format('Y-m');
        $lastMonthKey = now()->subMonth()->format('Y-m');

        $currentPssAvg = (float) $monthlyPss->get($currentMonthKey, 0);
        $lastPssAvg = (float) $monthlyPss->get($lastMonthKey, 0);

        // 4. Calculate Difference and Percentage Change
        $pssDiff = round($currentPssAvg - $lastPssAvg, 2);

        if ($lastPssAvg > 0) {
            $pssPercentage = round(($pssDiff / $lastPssAvg) * 100, 1);
        } else {
            $pssPercentage = $currentPssAvg > 0 ? 100 : 0;
        }

        // 5. Determine Dynamic Styling & Labels
        if ($pssDiff > 0) {
            $pssDescription = "+{$pssDiff} pts (" . abs($pssPercentage) . "%) increase from last month";
            $pssIcon = 'heroicon-m-arrow-trending-up';
            $pssColor = 'success'; // Higher average is positive
        } elseif ($pssDiff < 0) {
            $pssDescription = "{$pssDiff} pts (" . abs($pssPercentage) . "%) decrease from last month";
            $pssIcon = 'heroicon-m-arrow-trending-down';
            $pssColor = 'danger';
        } else {
            $pssDescription = 'No change from last month';
            $pssIcon = 'heroicon-m-minus';
            $pssColor = 'gray';
        }

        // 6. Overall average score across all time
        $overallAvgPss = number_format(TrainingReport::avg('pss_score') ?? 0, 2);

        // Return Stat Widget
        return Stat::make('Avg PSS Score', $overallAvgPss)
            ->description($pssDescription)
            ->descriptionIcon($pssIcon)
            ->chart($pssChartData)
            ->color($pssColor);
    }
}
