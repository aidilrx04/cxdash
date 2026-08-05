<?php

namespace App\Filament\Resources\TrainingReports\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class FeedbackGeneralsRelationManager extends RelationManager
{
    protected static string $relationship = 'feedbackGenerals';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        $questions = $this->getOwnerRecord()->feedbackGenerals->first()->feedbackGenerals()->with('feedbackQuestion')
            ->get()
            ->map(fn($record) => $record->feedbackQuestion);

        $schema = [
            TextColumn::make('name')
        ];

        $feedbackGenerals = $this->getOwnerRecord()->feedbackGenerals->first()->feedbackGenerals;
        foreach ($questions as $question) {
            $schema[] = TextColumn::make('feedback_question' . Str::uuid())
                ->label($question->question)
                ->getStateUsing(function ($record) use ($question) {
                    // dd($record->feedbackGenerals()->where('feedback_question_id', $question->id)->first());
                    return $record->feedbackGenerals()->where('feedback_question_id', $question->id)->first()->response;
                });
            $schema[] = TextColumn::make('feedback_sentiment' . Str::uuid())
                ->label('Sentiment')
                ->getStateUsing(function ($record) use ($question) {
                    // dd($record->feedbackGenerals()->where('feedback_question_id', $question->id)->first());
                    return $record->feedbackGenerals()->where('feedback_question_id', $question->id)->first()->sentiment;
                });
            $schema[] = TextColumn::make('feedback_theme' . Str::uuid())
                ->label('Theme')
                ->getStateUsing(function ($record) use ($question) {
                    // dd($record->feedbackGenerals()->where('feedback_question_id', $question->id)->first());
                    return $record->feedbackGenerals()->where('feedback_question_id', $question->id)->first()->theme;
                });
        }
        // foreach()
        return $table
            ->recordTitleAttribute('name')
            ->columns(
                $schema
            )
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
