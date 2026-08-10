<?php

namespace App\Filament\Resources\FeedbackGenerals\Schemas;

use App\Models\FeedbackQuestion;
use App\Models\Trainee;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FeedbackGeneralForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('trainee_id')
                    ->native(false)
                    ->options(fn() => Trainee::pluck('name', 'id',))
                    ->preload(),
                Select::make('feedback_question_id')
                    ->native(false)
                    ->options(fn() => FeedbackQuestion::pluck('question', 'id',))
                    ->preload(),
                Textarea::make('response'),
                Select::make('sentiment')
                    ->options([
                        'positive' => 'Positive',
                        'neutral' => 'Neutral',
                        'negative' => 'Negative',
                    ]),
                TextInput::make('theme'),
            ]);
    }
}
