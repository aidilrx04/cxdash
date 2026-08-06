<?php

namespace App\Filament\Resources\TrainingReports\RelationManagers;

use App\Jobs\GenerateSentiments;
use Filament\Actions\Action;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
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
                    $responseCount = $record->feedbackGenerals()->count() > 0;
                    return $responseCount ? ($record->feedbackGenerals()->where('feedback_question_id', $question->id)->first()->response ?? '') : 'No Response';
                });
            $schema[] = TextColumn::make('feedback_sentiment' . Str::uuid())
                ->label('Sentiment')
                ->getStateUsing(function ($record) use ($question) {
                    // dd($record->feedbackGenerals()->where('feedback_question_id', $question->id)->first());
                    $responseCount = $record->feedbackGenerals()->count() > 0;
                    return $responseCount ? ($record->feedbackGenerals()->where('feedback_question_id', $question->id)->first()->sentiment ?? '') : 'No Response';
                });
            $schema[] = TextColumn::make('feedback_theme' . Str::uuid())
                ->label('Theme')
                ->getStateUsing(function ($record) use ($question) {
                    // dd($record->feedbackGenerals()->where('feedback_question_id', $question->id)->first());
                    $responseCount = $record->feedbackGenerals()->count() > 0;
                    return $responseCount ? ($record->feedbackGenerals()->where('feedback_question_id', $question->id)->first()->theme ?? '') : 'No Response';
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
                Action::make('generate_sentiment')
                    ->label('Generate Sentiment Analysis')
                    ->icon(Heroicon::Sparkles)
                    ->action(function (RelationManager $livewire) {
                        $record = $this->getOwnerRecord();
                        GenerateSentiments::dispatch($record, Str::uuid());
                        Notification::make()->title('Sentiment analysis queued')
                            ->body('This will take a moment')
                            ->info()
                            ->send();
                    }),
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
