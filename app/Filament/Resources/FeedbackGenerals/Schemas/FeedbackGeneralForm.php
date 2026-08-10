<?php

namespace App\Filament\Resources\FeedbackGenerals\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FeedbackGeneralForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Feedback Details')
                    ->description('Record trainee response, sentiment rating, and categorized feedback theme.')
                    ->icon('heroicon-m-chat-bubble-bottom-center-text')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('trainee_id')
                            ->relationship('trainee', 'name')
                            ->label('Trainee')
                            ->placeholder('Select Trainee (leave blank for anonymous)')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->prefixIcon('heroicon-m-user'),

                        Select::make('feedback_question_id')
                            ->relationship('feedbackQuestion', 'question')
                            ->label('Feedback Question')
                            ->placeholder('Select question')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->prefixIcon('heroicon-m-question-mark-circle'),

                        Textarea::make('response')
                            ->label('Trainee Response')
                            ->placeholder('Type the detailed response or transcript...')
                            ->rows(4)
                            ->required()
                            ->columnSpanFull(),

                        Select::make('sentiment')
                            ->label('Sentiment Analysis')
                            ->options([
                                'positive' => 'Positive',
                                'neutral' => 'Neutral',
                                'negative' => 'Negative',
                            ])
                            ->native(false)
                            ->prefixIcon('heroicon-m-face-smile'),

                        TextInput::make('theme')
                            ->label('Extracted Theme / Topic')
                            ->placeholder('e.g., Course Material, Facilities, Instructor Quality')
                            ->prefixIcon('heroicon-m-tag'),
                    ]),
            ]);
    }
}
