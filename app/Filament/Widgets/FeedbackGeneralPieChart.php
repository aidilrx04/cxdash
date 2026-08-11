<?php

namespace App\Filament\Widgets;

use App\Models\FeedbackGeneral;
use Filament\Widgets\ChartWidget;

class FeedbackGeneralPieChart extends ChartWidget
{
    protected  ?string $heading = 'Feedback Sentiment Breakdown';

    protected  ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $positive = FeedbackGeneral::whereIn('sentiment', ['positive', 'good', 'satisfied'])->count();
        $neutral = FeedbackGeneral::whereIn('sentiment', ['neutral', 'average'])->count();
        $negative = FeedbackGeneral::whereIn('sentiment', ['negative', 'bad', 'dissatisfied'])->count();

        $uncategorized = FeedbackGeneral::where(function ($query) {
            $query->whereNotIn('sentiment', ['positive', 'good', 'satisfied', 'neutral', 'average', 'negative', 'bad', 'dissatisfied'])
                ->orWhereNull('sentiment');
        })->count();

        return [
            'datasets' => [
                [
                    'label' => 'Feedback Count',
                    'data' => [$positive, $neutral, $negative, $uncategorized],
                    'backgroundColor' => [
                        '#10b981', // Success Green - Positive
                        '#f59e0b', // Warning Amber - Neutral
                        '#ef4444', // Danger Red - Negative
                        '#9ca3af', // Gray - Uncategorized
                    ],
                    'hoverOffset' => 6,
                ],
            ],
            'labels' => ['Positive', 'Neutral', 'Negative', 'Uncategorized'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
