<?php

namespace App\Filament\Resources\Trainers\RelationManagers;

use App\Models\TrainingExperience;
use BackedEnum;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class TrainingExperiencesRelationManager extends RelationManager
{
    protected static string $relationship = 'trainingExperiences';

    protected static ?string $title = 'Training Delivery Track Record';

    protected static string|BackedEnum|null $icon = 'heroicon-m-academic-cap';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('program_name')
                    ->label('Program / Course Name')
                    ->placeholder('e.g., Enterprise Cybersecurity Operations & Incident Response')
                    ->prefixIcon('heroicon-m-academic-cap')
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('client')
                    ->label('Client / Organization')
                    ->placeholder('e.g., Ministry of Human Resources, Maybank, TechCorp')
                    ->prefixIcon('heroicon-m-building-office')
                    ->required(),

                TextInput::make('participant_count')
                    ->label('Number of Participants')
                    ->placeholder('e.g., 30')
                    ->prefixIcon('heroicon-m-user-group')
                    ->numeric()
                    ->minValue(1)
                    ->required(),

                DatePicker::make('date_start')
                    ->label('Start Date')
                    ->native(false)
                    ->displayFormat('d M Y')
                    ->maxDate(now())
                    ->required()
                    ->live(),

                DatePicker::make('date_end')
                    ->label('End Date')
                    ->native(false)
                    ->displayFormat('d M Y')
                    ->minDate(fn(Get $get) => $get('date_start'))
                    ->required(),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Training Event Details')
                    ->icon('heroicon-m-academic-cap')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('program_name')
                            ->label('Program Name')
                            ->weight('bold')
                            ->icon('heroicon-m-academic-cap')
                            ->columnSpanFull(),

                        TextEntry::make('client')
                            ->label('Client / Client Organization')
                            ->icon('heroicon-m-building-office'),

                        TextEntry::make('participant_count')
                            ->label('Attendance')
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-m-user-group')
                            ->formatStateUsing(fn($state) => "{$state} Participants"),

                        TextEntry::make('date_start')
                            ->label('Program Duration')
                            ->icon('heroicon-m-calendar')
                            ->formatStateUsing(function ($record): string {
                                $start = $record->date_start ? Carbon::parse($record->date_start)->format('d M Y') : 'N/A';
                                $end = $record->date_end ? Carbon::parse($record->date_end)->format('d M Y') : 'N/A';

                                return "{$start} — {$end}";
                            }),

                        TextEntry::make('created_at')
                            ->label('Added On')
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('-'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('-'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('program_name')
            ->defaultSort('date_start', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('program_name')
                    ->label('Program & Client')
                    ->description(fn($record): string => "Client: {$record->client}")
                    ->searchable(['program_name', 'client'])
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-m-academic-cap')
                    ->wrap(),

                TextColumn::make('date_start')
                    ->label('Duration')
                    ->formatStateUsing(function ($record): string {
                        $start = $record->date_start ? Carbon::parse($record->date_start)->format('d M Y') : '-';
                        $end = $record->date_end ? Carbon::parse($record->date_end)->format('d M Y') : '-';

                        if ($start === $end) {
                            return $start;
                        }

                        return "{$start} – {$end}";
                    })
                    ->sortable(),

                TextColumn::make('participant_count')
                    ->label('Attendees')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-m-user-group')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Date Added')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Last Modified')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('recent_trainings')
                    ->label('Conducted Last 12 Months')
                    ->query(fn(Builder $query): Builder => $query->where('date_start', '>=', now()->subYear())),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Log Training')
                    ->icon('heroicon-m-plus')
                    ->modalHeading('Log Delivered Training Program'),

                AssociateAction::make()
                    ->label('Associate Existing')
                    ->icon('heroicon-m-link')
                    ->preloadRecordSelect(),
            ])
            ->recordActions([
                ViewAction::make()->iconButton(),
                EditAction::make()->iconButton(),
                DissociateAction::make()
                    ->iconButton()
                    ->tooltip('Dissociate from trainer'),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-academic-cap')
            ->emptyStateHeading('No Training History Recorded')
            ->emptyStateDescription('Track and log corporate training programs, clients served, date ranges, and attendance figures for this trainer.');
    }
}
