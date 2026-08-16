<?php

namespace App\Filament\Resources\TrainingReports\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TrainingReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Primary File Upload Section (Visible during creation and editing)
                Section::make('Report Document')
                    ->description('Upload the processed PDF training report file.')
                    ->icon('heroicon-m-document-text')
                    ->collapsible(false)
                    ->schema([
                        FileUpload::make('file_path')
                            ->label('PDF Report File')
                            ->disk('local')
                            ->directory('uploads')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(20480) // 20 MB Limit
                            ->required()
                            ->openable()
                            ->downloadable()
                            ->previewable()
                            ->columnSpanFull()
                            ->helperText('Only PDF files up to 20MB are supported.')
                            ->hiddenOn('edit'),

                        TextInput::make('file_name')
                            ->label('Original File Name')
                            ->disabled()
                            ->dehydrated()
                            ->prefixIcon('heroicon-m-paper-clip')
                            ->placeholder('e.g., Training_Report_2026.pdf')
                            ->hiddenOn('create')
                            ->columnSpanFull(),
                    ]),

                // Detailed Metadata Section (Populated upon extraction / edit view)
                Section::make('Program & Trainer Overview')
                    ->description('General information extracted from the training report.')
                    ->icon('heroicon-m-academic-cap')
                    ->columns(2)
                    ->hiddenOn('create')
                    ->schema([
                        TextInput::make('program_title')
                            ->label('Program Title')
                            ->required()
                            ->prefixIcon('heroicon-m-book-open')
                            ->placeholder('Enter training program title')
                            ->columnSpan(2),

                        TextInput::make('client_name')
                            ->label('Client / Organization')
                            ->required()
                            ->prefixIcon('heroicon-m-building-office-2')
                            ->placeholder('Enter client or company name'),

                        TextInput::make('trainer_name')
                            ->label('Lead Trainer')
                            ->required()
                            ->prefixIcon('heroicon-m-user')
                            ->placeholder('Enter trainer name'),

                        Select::make('status')
                            ->label('Report Status')
                            ->options([
                                'Draft' => 'Draft',
                                'Pending Review' => 'Pending Review',
                                'Approved' => 'Approved',
                                'Completed' => 'Completed',
                                'Archived' => 'Archived',
                            ])
                            ->default('Pending Review')
                            ->required()
                            ->native(false)
                            ->prefixIcon('heroicon-m-flag')
                            ->columnSpan(2),
                    ]),

                // Quantitative Performance Metrics Section
                Section::make('Evaluation & Metrics')
                    ->description('Participant feedback, satisfaction, and evaluation summaries.')
                    ->icon('heroicon-m-chart-bar-square')
                    ->columns(2)
                    // ->columnSpanFull()
                    ->hiddenOn('create')
                    ->schema([
                        TextInput::make('total_participants')
                            ->label('Total Attendance')
                            ->numeric()
                            // ->minValue(0)
                            ->suffix('Attendees')
                            ->prefixIcon('heroicon-m-users')
                            ->placeholder('0'),

                        TextInput::make('total_evaluation')
                            ->label('Evaluations Received')
                            ->numeric()
                            // ->minValue(0)
                            ->suffix('Forms')
                            ->prefixIcon('heroicon-m-clipboard-document-check')
                            ->placeholder('0'),

                        TextInput::make('overall_satisfaction')
                            ->label('Overall Satisfaction')
                            ->suffix('%')
                            ->prefixIcon('heroicon-m-face-smile')
                            ->placeholder('e.g., 94.5%'),

                        TextInput::make('pss_score')
                            ->label('PSS Score')
                            ->numeric()
                            ->step(0.01)
                            ->prefixIcon('heroicon-m-star')
                            ->placeholder('e.g., 4.85'),
                    ]),
                Section::make('Executive Summary')
                    // ->columns(2)
                    // ->columnSpanFull()
                    ->hiddenOn('create')
                    ->schema([
                        RichEditor::make('executive_summary'),
                    ]),
            ]);
    }
}
