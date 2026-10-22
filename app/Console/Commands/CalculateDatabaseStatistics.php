<?php

namespace App\Console\Commands;

use App\Models\Statistic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CalculateDatabaseStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics:calculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate database statistics and feedback sentiment percentages';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // 1. Calculate General Database Table Counts
        $tableCounts = [
            'users'            => DB::table('users')->count(),
            'trainers'         => DB::table('trainers')->count(),
            'trainees'         => DB::table('trainees')->count(),
            'training_reports' => DB::table('training_reports')->count(),
            'clients'          => DB::table('clients')->count(),
            'feedback_generals' => DB::table('feedback_generals')->count(),
        ];

        // 2. Calculate Sentiment Totals and Percentages from feedback_generals
        $sentimentRaw = DB::table('feedback_generals')
            ->selectRaw("
                SUM(CASE WHEN LOWER(sentiment) = 'positive' THEN 1 ELSE 0 END) as positive_count,
                SUM(CASE WHEN LOWER(sentiment) = 'negative' THEN 1 ELSE 0 END) as negative_count,
                SUM(CASE WHEN LOWER(sentiment) = 'neutral' THEN 1 ELSE 0 END) as neutral_count,
                COUNT(*) as total_count
            ")
            ->first();

        $totalFeedback = (int) ($sentimentRaw->total_count ?? 0);
        $positiveCount = (int) ($sentimentRaw->positive_count ?? 0);
        $negativeCount = (int) ($sentimentRaw->negative_count ?? 0);
        $neutralCount  = (int) ($sentimentRaw->neutral_count ?? 0);

        $sentimentPercentage = [
            'positive' => $totalFeedback > 0 ? round(($positiveCount / $totalFeedback) * 100, 2) : 0.00,
            'negative' => $totalFeedback > 0 ? round(($negativeCount / $totalFeedback) * 100, 2) : 0.00,
            'neutral'  => $totalFeedback > 0 ? round(($neutralCount / $totalFeedback) * 100, 2) : 0.00,
            'counts'   => [
                'positive' => $positiveCount,
                'negative' => $negativeCount,
                'neutral'  => $neutralCount,
                'total'    => $totalFeedback,
            ],
        ];

        // 3. Assemble JSON Payload
        $payload = [
            'counts'               => $tableCounts,
            'sentiment_percentage' => $sentimentPercentage,
            'calculated_at'        => now()->toIso8601String(),
        ];

        // 4. Update existing single row or create if it does not exist
        $statistic = Statistic::firstOrNew(['id' => 1]);
        $statistic->data = $payload;
        $statistic->is_running = false;
        $statistic->save();

        $this->info('Database statistics and sentiment analysis successfully updated.');

        return Command::SUCCESS;
    }
}
