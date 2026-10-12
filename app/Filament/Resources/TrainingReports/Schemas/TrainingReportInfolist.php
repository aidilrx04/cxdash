<?php

namespace App\Filament\Resources\TrainingReports\Schemas;

use App\Models\FeedbackGeneral;
use App\Models\FeedbackQuestion;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class TrainingReportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Grid::make(1)
                    ->columnSpan(2)
                    ->schema([
                        Section::make('Training')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('program_title')
                                    ->label('Program')
                                    ->columnSpanFull(),
                                TextEntry::make('client_name')
                                    ->label('Client'),
                                TextEntry::make('trainer_name')
                                    ->label('Trainer'),
                                TextEntry::make('status')
                                    ->columnSpanFull(),
                                Section::make('')->columnSpanFull(),
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('total_participants')
                                            ->label('Participants')
                                            ->badge()
                                            ->color(Color::Blue),
                                        TextEntry::make('total_evaluation')
                                            ->label('Evaluation')
                                            ->badge()
                                            ->color(Color::Amber),
                                        TextEntry::make('overall_satisfaction')
                                            ->label('Satisfaction')
                                            ->badge()
                                            ->color(Color::Emerald),
                                        TextEntry::make('pss_score')
                                            ->label('Customer Score')
                                            ->badge()
                                            ->color(Color::Emerald),
                                    ])
                                    ->columnSpanFull(),
                            ]),
                        Section::make('Executive Summary')
                            ->schema([
                                TextEntry::make('executive_summary')
                                    ->hiddenLabel()
                                    ->html()
                                    ->alignJustify()
                            ])
                            ->headerActions(
                                [
                                    Action::make('Generate Report')
                                        ->label('Generate Report')
                                        ->extraAttributes(function ($record) {
                                            return [
                                                'data-generate-report' => true,
                                                'data-executive-summary' => e($record->executive_summary),
                                                'data-id' => $record->id,
                                                'id' => 'cx-generate-report'
                                            ];
                                        }),
                                    Action::make('auto_summarizer')
                                        ->extraAttributes(function ($record) {
                                            // Safe check for file path
                                            $filePath = $record->file_path ? Storage::url($record->file_path) : null;

                                            // Fetch question IDs in a single query to minimize database hits
                                            $questions = FeedbackQuestion::whereIn('question', [
                                                'What did you like most about the course?',
                                                'The courses you would probably like to attend.',
                                            ])->pluck('id', 'question');

                                            // Pre-fetch trainee IDs once
                                            $traineeIds = $record->trainees()->pluck('id');

                                            // Fetch responses for question 1
                                            $feedbacksLikeMost = [];
                                            if (isset($questions['What did you like most about the course?'])) {
                                                $feedbacksLikeMost = FeedbackGeneral::where('feedback_question_id', $questions['What did you like most about the course?'])
                                                    ->whereIn('trainee_id', $traineeIds)
                                                    ->pluck('response')
                                                    ->toArray();
                                            }

                                            // Fetch responses for question 2
                                            $feedbacksFutureCourses = [];
                                            if (isset($questions['The courses you would probably like to attend.'])) {
                                                $feedbacksFutureCourses = FeedbackGeneral::where('feedback_question_id', $questions['The courses you would probably like to attend.'])
                                                    ->whereIn('trainee_id', $traineeIds)
                                                    ->pluck('response')
                                                    ->toArray();
                                            }

                                            return [
                                                'data-summarizer' => 'true',
                                                'data-file-path' => $filePath,
                                                'data-question-like-the-most' => e(json_encode($feedbacksLikeMost)),
                                                'data-question-future-courses' => e(json_encode($feedbacksFutureCourses)),
                                            ];
                                        })
                                ]
                            )
                    ]),
                Section::make('Information')
                    ->columns(1)
                    ->schema([
                        TextEntry::make('file_name')
                            ->suffixAction(
                                Action::make('downloadPdf')
                                    ->label('Download PDF')
                                    ->icon('heroicon-m-arrow-down-tray')
                                    ->color('primary')
                                    ->visible(fn($record) => ! empty($record->file_path))
                                    ->action(fn($record) => Storage::disk('public')->download($record->file_path, $record->file_name ?? null))
                            ),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->date('d/m/Y H:i:s'),
                                TextEntry::make('updated_at')
                                    ->date('d/m/Y H:i:s'),
                            ]),
                    ]),
            ]);
    }
}
