<?php

namespace App\Filament\Resources\Trainers\RelationManagers;

use App\Models\WorkExperience;
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
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class WorkExperiencesRelationManager extends RelationManager
{
    protected static string $relationship = 'workExperiences';

    protected static ?string $title = 'Work & Professional Experience';

    protected static string|BackedEnum|null $icon = 'heroicon-m-briefcase';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Job Title / Position')
                    ->placeholder('e.g., Senior Systems Engineer, Lead IT Instructor')
                    ->prefixIcon('heroicon-m-briefcase')
                    ->required(),

                TextInput::make('company_name')
                    ->label('Company / Organization')
                    ->placeholder('e.g., PETRONAS, Telekom Malaysia, Universiti Teknikal Malaysia')
                    ->prefixIcon('heroicon-m-building-office-2')
                    ->required(),

                DatePicker::make('start_date')
                    ->label('Start Date')
                    ->native(false)
                    ->displayFormat('M Y')
                    ->maxDate(now())
                    ->required(),

                DatePicker::make('end_date')
                    ->label('End Date')
                    ->native(false)
                    ->displayFormat('M Y')
                    ->hidden(fn(Get $get): bool => (bool) $get('is_current'))
                    ->required(fn(Get $get): bool => ! $get('is_current')),

                Toggle::make('is_current')
                    ->label('Current Position')
                    ->helperText('Check if the trainer actively holds this role.')
                    ->live()
                    ->afterStateUpdated(function (bool $state, callable $set) {
                        if ($state) {
                            $set('end_date', null);
                        }
                    })
                    ->columnSpanFull(),

                Textarea::make('responsibilities')
                    ->label('Key Responsibilities & Scope')
                    ->placeholder('Detail primary duties, team management, course deliveries, or technical architecture managed...')
                    ->rows(3)
                    ->required()
                    ->columnSpanFull(),

                Textarea::make('achievements')
                    ->label('Key Accomplishments & Impact')
                    ->placeholder('e.g., Orchestrated network infrastructure migration reducing downtime by 40%, trained 200+ staff on cloud security.')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Work Experience Details')
                    ->icon('heroicon-m-briefcase')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('title')
                            ->label('Job Title')
                            ->weight('bold')
                            ->icon('heroicon-m-briefcase'),

                        TextEntry::make('company_name')
                            ->label('Company / Organization')
                            ->icon('heroicon-m-building-office-2'),

                        TextEntry::make('start_date')
                            ->label('Duration')
                            ->icon('heroicon-m-calendar')
                            ->formatStateUsing(function ($record): string {
                                $start = $record->start_date ? Carbon::parse($record->start_date)->format('M Y') : 'N/A';
                                $end = $record->is_current
                                    ? 'Present'
                                    : ($record->end_date ? Carbon::parse($record->end_date)->format('M Y') : 'N/A');

                                return "{$start} — {$end}";
                            }),

                        TextEntry::make('is_current')
                            ->label('Employment Status')
                            ->badge()
                            ->formatStateUsing(fn(bool $state): string => $state ? 'Current Position' : 'Past Position')
                            ->color(fn(bool $state): string => $state ? 'success' : 'gray'),

                        TextEntry::make('responsibilities')
                            ->label('Responsibilities & Scope')
                            ->markdown()
                            ->columnSpanFull(),

                        TextEntry::make('achievements')
                            ->label('Key Accomplishments')
                            ->markdown()
                            ->placeholder('No specific accomplishments recorded.')
                            ->columnSpanFull(),

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
            ->recordTitleAttribute('title')
            ->defaultSort('start_date', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('title')
                    ->label('Position & Company')
                    ->description(fn($record): string => $record->company_name)
                    ->searchable(['title', 'company_name'])
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-m-briefcase'),

                TextColumn::make('start_date')
                    ->label('Duration')
                    ->formatStateUsing(function ($record): string {
                        $start = $record->start_date ? Carbon::parse($record->start_date)->format('M Y') : '-';
                        $end = $record->is_current
                            ? 'Present'
                            : ($record->end_date ? Carbon::parse($record->end_date)->format('M Y') : '-');

                        return "{$start} – {$end}";
                    })
                    ->sortable(),

                IconColumn::make('is_current')
                    ->label('Current')
                    ->boolean()
                    ->trueIcon('heroicon-m-check-circle')
                    ->falseIcon('heroicon-m-minus-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->alignCenter(),

                TextColumn::make('responsibilities')
                    ->label('Responsibilities')
                    ->lineClamp(1)
                    ->toggleable(isToggledHiddenByDefault: true),

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
                TernaryFilter::make('is_current')
                    ->label('Employment Status')
                    ->placeholder('All Roles')
                    ->trueLabel('Current Positions Only')
                    ->falseLabel('Past Positions Only'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Experience')
                    ->icon('heroicon-m-plus')
                    ->modalHeading('Record Work Experience'),

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
            ->emptyStateIcon('heroicon-o-briefcase')
            ->emptyStateHeading('No Work Experience Recorded')
            ->emptyStateDescription('Document professional roles, responsibilities, and major accomplishments for this trainer.');
    }
}
