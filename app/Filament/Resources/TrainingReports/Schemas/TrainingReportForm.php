<?php

namespace App\Filament\Resources\TrainingReports\Schemas;

use App\Models\FeedbackGeneral;
use App\Models\FeedbackQuestion;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class TrainingReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                FileUpload::make('file_path')
                    ->columnSpanFull()
                    ->hiddenOn('edit')

                    ->label('Training Report')

                    ->disk('public')
                    ->directory('uploads')
                    ->storeFileNamesIn('file_name')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(20480)
                    ->required(fn($operation) => $operation === 'create' ? true : false)
                    ->openable()
                    ->downloadable()
                    ->previewable()
                    ->helperText('Only PDF files up to 20MB are supported.')

                    ->panelAspectRatio('3:1')
                    ->panelLayout('integrated'),
                Group::make([
                    Section::make('Training Data')
                        ->columns(2)
                        ->schema([
                            TextInput::make('program_title')
                                ->label('Program')
                                ->columnSpanFull(),
                            TextInput::make('client_name')
                                ->label('Client'),
                            TextInput::make('trainer_name')
                                ->label('Trainer'),

                        ]),
                    Section::make('Executive Summary')
                        // ->columns(2)
                        // ->columnSpanFull()
                        ->hiddenOn('create')
                        ->schema([
                            RichEditor::make('executive_summary')
                                ->hiddenLabel()
                                ->toolbarButtons([
                                    ['bold', 'italic', 'underline', 'strike', 'alignStart', 'alignCenter', 'alignEnd', 'alignJustify']
                                ]),
                        ])
                        ->headerActions([
                            Action::make('auto_summarizer')
                                ->label('Generate')
                                ->icon(Heroicon::Sparkles)
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
                        ]),
                ])
                    ->columnSpan(2)
                    ->columns(1)
                    ->hiddenOn('create'),
                Section::make('Training Result')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('status')
                            ->columnSpanFull(),
                        TextInput::make('total_participants')
                            ->label('Participants')
                            ->columnSpanFull(),
                        TextInput::make('total_evaluation')
                            ->label('Evaluation')
                            ->columnSpanFull(),
                        TextInput::make('overall_satisfaction')
                            ->label('Customer Satisfaction')
                            ->columnSpanFull(),
                        TextInput::make('pss_score')
                            ->label('Customer Score')
                            ->columnSpanFull(),
                    ])
                    ->hiddenOn('create'),
            ]);
    }
}
