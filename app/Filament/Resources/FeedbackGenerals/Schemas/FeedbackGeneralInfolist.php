<?php

namespace App\Filament\Resources\FeedbackGenerals\Schemas;

use App\Filament\Resources\TrainingReports\TrainingReportResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FeedbackGeneralInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Feedback Details')
                    ->description('Detailed overview of trainee response, sentiment analysis, and topic tagging.')
                    ->icon('heroicon-m-chat-bubble-bottom-center-text')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('trainee.name')
                            ->label('Trainee')
                            ->placeholder('Anonymous / Unassigned')
                            ->icon('heroicon-m-user'),

                        TextEntry::make('trainee.trainingReport.program_title')
                            ->label('Training Report')
                            ->placeholder('No Report Linked')
                            ->icon('heroicon-m-document-text')
                            ->weight('bold')
                            ->color('primary')
                            ->url(function ($record): ?string {
                                $reportId = $record->trainee?->training_report_id;

                                return $reportId
                                    ? TrainingReportResource::getUrl('view', ['record' => $reportId])
                                    : null;
                            })
                            ->openUrlInNewTab(),

                        TextEntry::make('feedbackQuestion.question')
                            ->label('Feedback Question')
                            ->icon('heroicon-m-question-mark-circle'),

                        TextEntry::make('response')
                            ->label('Trainee Response')
                            ->columnSpanFull()
                            ->prose()
                            ->markdown(),

                        TextEntry::make('sentiment')
                            ->label('Sentiment Analysis')
                            ->badge()
                            ->color(fn(?string $state): string => match (strtolower($state ?? '')) {
                                'positive', 'good', 'satisfied' => 'success',
                                'neutral', 'average' => 'warning',
                                'negative', 'bad', 'dissatisfied' => 'danger',
                                default => 'gray',
                            })
                            ->icon(fn(?string $state): ?string => match (strtolower($state ?? '')) {
                                'positive', 'good', 'satisfied' => 'heroicon-m-face-smile',
                                'neutral', 'average' => 'heroicon-m-minus-circle',
                                'negative', 'bad', 'dissatisfied' => 'heroicon-m-face-frown',
                                default => null,
                            }),

                        TextEntry::make('theme')
                            ->label('Extracted Theme / Topic')
                            ->badge()
                            ->color('info')
                            ->placeholder('Uncategorized')
                            ->icon('heroicon-m-tag'),

                        TextEntry::make('created_at')
                            ->label('Submitted On')
                            ->dateTime('d M Y, h:i A')
                            ->icon('heroicon-m-calendar'),

                        TextEntry::make('updated_at')
                            ->label('Last Modified')
                            ->dateTime('d M Y, h:i A')
                            ->icon('heroicon-m-clock'),
                    ]),
            ]);
    }
}
