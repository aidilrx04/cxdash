<?php

namespace App\Filament\Widgets;

use App\Models\Trainer;
use App\Models\TrainingReport;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends StatsOverviewWidget
{
    protected function getHeading(): ?string
    {
        return 'Analytics';
    }

    protected function getDescription(): ?string
    {
        return 'An overview of customer experience analytics.';
    }

    protected function getStats(): array
    {
        return [
            $this->getTrainingReportStat(),
            $this->getActiveTrainersStat(),
            $this->getTotalParticipantsStat(),
            $this->getAveragePssStat(),
        ];
    }

    /**
     * Helper to compute percentage or value change between this month & last month
     */
    protected function getMonthDiff(float $thisMonth, float $lastMonth, bool $isScore = false): array
    {
        $diff = $thisMonth - $lastMonth;

        if ($isScore) {
            // For ratings/scores like PSS, point change (e.g. +0.25) is clearer than percentage
            $absDiff = number_format(abs($diff), 2);
            $text = $diff >= 0 ? "+{$absDiff} pts from last month" : "-{$absDiff} pts from last month";
        } else {
            if ($lastMonth == 0) {
                $percentage = $thisMonth > 0 ? 100 : 0;
            } else {
                $percentage = (($thisMonth - $lastMonth) / $lastMonth) * 100;
            }

            $absPercent = number_format(abs($percentage), 1);
            $text = $diff >= 0 ? "+{$absPercent}% from last month" : "-{$absPercent}% from last month";
        }

        return [
            'text' => $text,
            'icon' => $diff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down',
        ];
    }

    /**
     * 1. Total Training Reports Stat with 12-Month Trend & MoM Diff
     */
    protected function getTrainingReportStat(): Stat
    {
        $monthlyCounts = TrainingReport::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as aggregate")
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->pluck('aggregate', 'month');

        $chartData = collect(range(11, 0))->map(function ($offset) use ($monthlyCounts) {
            $monthKey = now()->subMonths($offset)->format('Y-m');
            return (int) $monthlyCounts->get($monthKey, 0);
        })->toArray();

        // Extract values from existing collection
        $thisMonth = (float) $monthlyCounts->get(now()->format('Y-m'), 0);
        $lastMonth = (float) $monthlyCounts->get(now()->subMonth()->format('Y-m'), 0);
        $diff = $this->getMonthDiff($thisMonth, $lastMonth);

        return Stat::make('Training Reports', number_format(TrainingReport::count()))
            ->description($diff['text'])
            ->descriptionIcon($diff['icon'])
            ->chart($chartData)
            ->color('success');
    }

    /**
     * 2. Active Trainers Stat with 12-Month Registration Trend & MoM Diff
     */
    protected function getActiveTrainersStat(): Stat
    {
        $monthlyTrainers = Trainer::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as aggregate")
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->pluck('aggregate', 'month');

        $chartData = collect(range(11, 0))->map(function ($offset) use ($monthlyTrainers) {
            $monthKey = now()->subMonths($offset)->format('Y-m');
            return (int) $monthlyTrainers->get($monthKey, 0);
        })->toArray();

        $thisMonth = (float) $monthlyTrainers->get(now()->format('Y-m'), 0);
        $lastMonth = (float) $monthlyTrainers->get(now()->subMonth()->format('Y-m'), 0);
        $diff = $this->getMonthDiff($thisMonth, $lastMonth);

        return Stat::make('Active Trainers', number_format(Trainer::count()))
            ->description($diff['text'])
            ->descriptionIcon($diff['icon'])
            ->chart($chartData)
            ->color('info');
    }

    /**
     * 3. Total Participants Stat with 12-Month Trend & MoM Diff
     */
    protected function getTotalParticipantsStat(): Stat
    {
        $monthlyParticipants = TrainingReport::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_participants) as aggregate")
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->pluck('aggregate', 'month');

        $chartData = collect(range(11, 0))->map(function ($offset) use ($monthlyParticipants) {
            $monthKey = now()->subMonths($offset)->format('Y-m');
            return (int) $monthlyParticipants->get($monthKey, 0);
        })->toArray();

        $thisMonth = (float) $monthlyParticipants->get(now()->format('Y-m'), 0);
        $lastMonth = (float) $monthlyParticipants->get(now()->subMonth()->format('Y-m'), 0);
        $diff = $this->getMonthDiff($thisMonth, $lastMonth);

        $totalParticipants = TrainingReport::sum('total_participants') ?? 0;

        return Stat::make('Total Participants', number_format($totalParticipants))
            ->description($diff['text'])
            ->descriptionIcon($diff['icon'])
            ->chart($chartData)
            ->color('warning');
    }

    /**
     * 4. Average PSS Score Stat with 12-Month Trend & MoM Diff
     */
    protected function getAveragePssStat(): Stat
    {
        $monthlyPss = TrainingReport::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, AVG(pss_score) as aggregate")
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->pluck('aggregate', 'month');

        $chartData = collect(range(11, 0))->map(function ($offset) use ($monthlyPss) {
            $monthKey = now()->subMonths($offset)->format('Y-m');
            return round((float) $monthlyPss->get($monthKey, 0), 2);
        })->toArray();

        $thisMonth = (float) $monthlyPss->get(now()->format('Y-m'), 0);
        $lastMonth = (float) $monthlyPss->get(now()->subMonth()->format('Y-m'), 0);
        // Using $isScore = true to show score point difference (e.g. "+0.35 pts")
        $diff = $this->getMonthDiff($thisMonth, $lastMonth, isScore: true);

        $avgPss = TrainingReport::avg('pss_score') ?? 0;

        return Stat::make('Avg PSS', number_format($avgPss, 2))
            ->description($diff['text'])
            ->descriptionIcon($diff['icon'])
            ->chart($chartData)
            ->color('primary');
    }
}
