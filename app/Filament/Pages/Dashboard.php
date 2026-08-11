<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DashboardStatsOverview;
use App\Filament\Widgets\FeedbackGeneralPieChart;
use App\Filament\Widgets\TopClientPssScore;
use Filament\Pages\Dashboard as BaseDashboard;
use Override;

class Dashboard extends BaseDashboard
{
	#[Override]
	public function getWidgets(): array
	{
		// return parent::getWidgets();
		return [
			DashboardStatsOverview::class,
			TopClientPssScore::class,
			FeedbackGeneralPieChart::class
		];
	}
}
