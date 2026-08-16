<?php

namespace App\Filament\Resources\TrainingReports\Schemas;

use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class TrainingReportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Primary Document & Status Section
                Section::make('Report Document')
                    ->description('Access and view the uploaded PDF report document.')
                    ->icon('heroicon-m-document-text')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('file_name')
                            ->label('Original File Name')
                            ->icon('heroicon-m-paper-clip')
                            ->placeholder('No document attached')
                            ->weight('medium'),

                        TextEntry::make('status')
                            ->label('Report Status')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'Draft' => 'gray',
                                'Pending Review' => 'warning',
                                'Approved' => 'info',
                                'Completed' => 'success',
                                'Archived' => 'danger',
                                default => 'gray',
                            })
                            ->icon('heroicon-m-flag'),

                        TextEntry::make('file_path')
                            ->label('PDF File Action')
                            ->icon('heroicon-m-arrow-down-tray')
                            ->columnSpanFull()
                            ->formatStateUsing(fn($state) => $state ? basename($state) : 'No file available')
                            ->suffixAction(
                                Action::make('downloadPdf')
                                    ->label('Download PDF')
                                    ->icon('heroicon-m-arrow-down-tray')
                                    ->color('primary')
                                    ->visible(fn($record) => ! empty($record->file_path))
                                    ->action(fn($record) => Storage::download(Storage::disk('local')->path($record->file_path), $record->file_name ?? null))
                            ),
                    ]),

                // Program & Trainer Overview
                Section::make('Program & Trainer Overview')
                    ->description('General metadata extracted from the training report.')
                    ->icon('heroicon-m-academic-cap')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('program_title')
                            ->label('Program Title')
                            ->weight('bold')
                            ->icon('heroicon-m-book-open')
                            ->columnSpanFull(),

                        TextEntry::make('client_name')
                            ->label('Client / Organization')
                            ->icon('heroicon-m-building-office-2')
                            ->placeholder('N/A'),

                        TextEntry::make('trainer_name')
                            ->label('Lead Trainer')
                            ->icon('heroicon-m-user')
                            ->placeholder('N/A'),
                    ]),

                // Quantitative Performance Metrics
                Section::make('Evaluation & Metrics')
                    ->description('Participant feedback, satisfaction ratings, and evaluation counts.')
                    ->icon('heroicon-m-chart-bar-square')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('total_participants')
                            ->label('Total Attendance')
                            ->icon('heroicon-m-users')
                            ->numeric()
                            ->badge()
                            ->color('info')
                            ->formatStateUsing(fn($state) => $state !== null ? "{$state} Attendees" : '0 Attendees'),

                        TextEntry::make('total_evaluation')
                            ->label('Evaluations Received')
                            ->icon('heroicon-m-clipboard-document-check')
                            ->numeric()
                            ->badge()
                            ->color('gray')
                            ->formatStateUsing(fn($state) => $state !== null ? "{$state} Forms" : '0 Forms'),

                        TextEntry::make('overall_satisfaction')
                            ->label('Overall Satisfaction')
                            ->icon('heroicon-m-face-smile')
                            ->badge()
                            ->color('success')
                            ->placeholder('N/A')
                            ->formatStateUsing(fn($state) => str_contains((string) $state, '%') ? $state : "{$state}%"),

                        TextEntry::make('pss_score')
                            ->label('PSS Score')
                            ->icon('heroicon-m-star')
                            ->numeric(decimalPlaces: 2)
                            ->badge()
                            ->color(fn($state): string => match (true) {
                                (float) $state >= 4.5 => 'success',
                                (float) $state >= 3.5 => 'warning',
                                (float) $state > 0 => 'danger',
                                default => 'gray',
                            })
                            ->placeholder('N/A'),
                    ]),

                // Audit Metadata
                Section::make('System Information')
                    ->icon('heroicon-m-information-circle')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Uploaded At')
                            ->dateTime('d M Y, h:i A')
                            ->icon('heroicon-m-calendar'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('d M Y, h:i A')
                            ->icon('heroicon-m-clock'),
                    ]),
            ]);
    }
}
