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
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SentimentsRelationManager extends RelationManager
{
    protected static string $relationship = 'sentiments';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('trainee_id')
                    ->relationship('trainee', 'name')
                    ->required(),
                Select::make('feedback_question_id')
                    ->relationship('feedbackQuestion', 'id')
                    ->required(),
                TextInput::make('sentiment')
                    ->default(null),
                TextInput::make('theme')
                    ->default(null),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('trainee.name')
                    ->label('Trainee'),
                TextEntry::make('feedbackQuestion.question')
                    ->label('Feedback question'),
                TextEntry::make('sentiment')
                    ->placeholder('-'),
                TextEntry::make('theme')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sentiment')
            ->columns([
                TextColumn::make('trainee.name')
                    ->searchable(),
                TextColumn::make('feedbackQuestion.question')
                    ->searchable(),
                TextColumn::make('sentiment')
                    ->searchable(),
                TextColumn::make('theme')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                Action::make('generate_sentiment')
                    ->label('Generate')
                    ->icon(Heroicon::Sparkles)
                    ->requiresConfirmation()
                    ->modalHeading('Generate Sentiments')
                    ->modalDescription('Are you sure you want to generate sentiment analysis for all responses in this report?')
                    ->action(function (RelationManager $livewire) {
                        $trainingReport = $livewire->getOwnerRecord();
                        $jobUuid = (string) Str::uuid();

                        // 1. Dispatch the job
                        GenerateSentiments::dispatch($trainingReport, $jobUuid);

                        // 2. Notify the user that processing has started
                        Notification::make()
                            ->title('Sentiment generation started')
                            ->body('The sentiment analysis process has been queued and is running in the background.')
                            ->info()
                            ->send();
                    }),
                AssociateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
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
