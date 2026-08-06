<?php

namespace App\Filament\Resources\TrainingReports\RelationManagers;

use App\Jobs\GenerateSentiments;
use App\Models\FeedbackQuestion;
use App\Models\TrainingReport;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Spatie\SimpleExcel\SimpleExcelWriter;

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
        $questions = FeedbackQuestion::query()
            ->whereHas('feedbackGenerals', function ($query) {
                $query->whereHas('trainee', function ($query) {
                    $query->where('training_report_id', $this->getOwnerRecord()->id);
                });
            })
            ->get();

        $schema = [
            TextColumn::make('name')
                ->searchable()
                ->sortable(),
        ];

        foreach ($questions as $index => $question) {
            $qNum = $index + 1;
            $qId = $question->id;

            // Response Column
            $schema[] = TextColumn::make("question_{$qId}")
                ->label("Q{$qNum}: {$question->question}")
                ->extraAttributes(['class' => 'max-w-[300px]'])
                ->wrap()
                ->getStateUsing(function ($record) use ($qId) {
                    $feedback = $record->feedbackGenerals->firstWhere('feedback_question_id', $qId);
                    return $feedback?->response ?? 'No Response';
                });

            // Sentiment Column
            $schema[] = TextColumn::make("sentiment_{$qId}")
                ->label("Sentiment {$qNum}")
                ->badge()
                ->color(fn(?string $state): string => match ($state) {
                    'positive' => 'success',
                    'neutral' => 'warning',
                    'negative' => 'danger',
                    default => 'gray',
                })
                ->getStateUsing(function ($record) use ($qId) {
                    $feedback = $record->feedbackGenerals->firstWhere('feedback_question_id', $qId);
                    return $feedback?->sentiment ?? 'N/A';
                });

            // Theme Column
            $schema[] = TextColumn::make("theme_{$qId}")
                ->label("Theme {$qNum}")
                ->getStateUsing(function ($record) use ($qId) {
                    $feedback = $record->feedbackGenerals->firstWhere('feedback_question_id', $qId);
                    return $feedback?->theme ?? 'N/A';
                });
        }

        return $table
            ->modifyQueryUsing(fn($query) => $query->with('feedbackGenerals'))
            ->recordTitleAttribute('name')
            ->columns(
                $schema
            )
            ->filters([
                // SelectFilter::make('sentiment')
                //     ->options([
                //         'positive' => 'positive',
                //         'neutral' => 'neutral',
                //         'negative' => 'negative',
                //     ])
                //     ->native(false)
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
                Action::make('export_excel')
                    ->label('Export to Excel')
                    ->icon(Heroicon::DocumentArrowDown)
                    ->action(function (RelationManager $livewire) {
                        /** @var \App\Models\TrainingReport $record */
                        $record = $livewire->getOwnerRecord();

                        $trainees = $record->trainees()
                            ->with(['feedbackGenerals.feedbackQuestion'])
                            ->get();

                        $rows = [];

                        foreach ($trainees as $index => $trainee) {
                            $row = [
                                'No' => $index + 1,
                                'Trainee Name' => $trainee->name ?? 'N/A',
                            ];

                            foreach ($trainee->feedbackGenerals as $qIndex => $feedback) {
                                $num = $qIndex + 1;
                                $questionText = $feedback->feedbackQuestion->question ?? "Question {$feedback->feedback_question_id}";

                                $row["Q{$num}: {$questionText}"] = $feedback->response ?? '';
                                $row["Sentiment{$num}"] = $feedback->sentiment ?? '-';
                                $row["Theme{$num}"] = $feedback->theme ?? '-';
                            }

                            $rows[] = $row;
                        }

                        $filename = "Feedback_Report_{$record->id}_" . now()->format('Y-m-d') . ".xlsx";
                        $tempPath = tempnam(sys_get_temp_dir(), 'excel_') . '.xlsx';

                        $writer = SimpleExcelWriter::create($tempPath);
// 1. Retrieve the underlying OpenSpout Writer
                        /** @var \OpenSpout\Writer\XLSX\Writer $openSpoutWriter */
                        $openSpoutWriter = $writer->getWriter();

                        // 2. Configure column options on OpenSpout
                        $options = $openSpoutWriter->getOptions();;

                        // Set a generous global default column width (default is 10)
                        $options->DEFAULT_COLUMN_WIDTH = 25;

                        // Optionally set precise widths for specific columns (1-indexed)
                        $options->setColumnWidth(8, 1);                  // Col 1: 'No' (narrow)
                        $options->setColumnWidth(25, 2);                 // Col 2: 'Trainee Name'
                        $options->setColumnWidthForRange(35, 3, 50);     // Cols 3 to 50: Questions, Sentiments, Themes (wide)
                        // ----------------------------------------------------

                        $writer->addRows($rows);
                        $writer->close();

                        return response()->download($tempPath, $filename, [
                            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])->deleteFileAfterSend(true);
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
