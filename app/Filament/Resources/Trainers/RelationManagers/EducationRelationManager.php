<?php

namespace App\Filament\Resources\Trainers\RelationManagers;

use App\Models\Education;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EducationRelationManager extends RelationManager
{
    protected static string $relationship = 'education';

    protected static ?string $title = 'Education & Qualifications';

    protected static string|BackedEnum|null $icon = 'heroicon-m-academic-cap';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Degree / Qualification')
                            ->placeholder('e.g., Bachelor of Computer Science (Hons)')
                            ->prefixIcon('heroicon-m-academic-cap')
                            ->datalist([
                                'Diploma in Information Technology',
                                'Bachelor of Computer Science',
                                'Bachelor of Business Administration',
                                'Master of Science (MSc)',
                                'Master of Business Administration (MBA)',
                                'Doctor of Philosophy (PhD)',
                            ])
                            ->required()
                            ->columnSpan(2),

                        TextInput::make('institution_name')
                            ->label('Institution / University')
                            ->placeholder('e.g., Universiti Teknikal Malaysia Melaka (UTeM)')
                            ->prefixIcon('heroicon-m-building-library')
                            ->required()
                            ->columnSpan(2),

                        TextInput::make('completion_year')
                            ->label('Completion Year')
                            ->numeric()
                            ->minValue(1960)
                            ->maxValue((int) date('Y') + 5)
                            ->placeholder('e.g., ' . date('Y'))
                            ->prefixIcon('heroicon-m-calendar')
                            ->required(),

                        TextInput::make('location')
                            ->label('Location')
                            ->placeholder('e.g., Melaka, Malaysia')
                            ->prefixIcon('heroicon-m-map-pin')
                            ->required(),

                        TextInput::make('grade')
                            ->label('Grade / CGPA / Class')
                            ->placeholder('e.g., 3.85 / First Class Honours')
                            ->prefixIcon('heroicon-m-sparkles')
                            ->default(null)
                            ->columnSpan(2),

                        FileUpload::make('document_paths')
                            ->label('Certificates & Transcripts')
                            ->disk('public')
                            ->directory('trainers/education-docs')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->multiple()
                            ->reorderable()
                            ->openable()
                            ->downloadable()
                            ->maxSize(10240) // 10MB
                            ->helperText('Upload official degree certificates or transcripts (PDF or images).')
                            ->columnSpan(2),
                    ]),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Qualification Details')
                    ->icon('heroicon-m-academic-cap')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('name')
                            ->label('Degree / Qualification')
                            ->weight('bold')
                            ->columnSpan(2),

                        TextEntry::make('institution_name')
                            ->label('Institution')
                            ->icon('heroicon-m-building-library')
                            ->columnSpan(2),

                        TextEntry::make('completion_year')
                            ->label('Completion Year')
                            ->badge()
                            ->color('sky')
                            ->icon('heroicon-m-calendar'),

                        TextEntry::make('location')
                            ->label('Location')
                            ->icon('heroicon-m-map-pin'),

                        TextEntry::make('grade')
                            ->label('Grade / CGPA')
                            ->badge()
                            ->color('emerald')
                            ->placeholder('Not specified'),

                        TextEntry::make('created_at')
                            ->label('Date Added')
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('-'),

                        TextEntry::make('document_paths')
                            ->label('Attached Files')
                            ->placeholder('No certificates uploaded')
                            ->columnSpanFull()
                            ->listWithLineBreaks()
                            ->bulleted(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('completion_year', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('name')
                    ->label('Degree & Institution')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn(Education $record): string => $record->institution_name ?? '')
                    ->wrap(),

                TextColumn::make('completion_year')
                    ->label('Year')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('sky')
                    ->alignCenter(),

                TextColumn::make('location')
                    ->label('Location')
                    ->searchable()
                    ->icon('heroicon-m-map-pin')
                    ->toggleable(),

                TextColumn::make('grade')
                    ->label('Grade / CGPA')
                    ->searchable()
                    ->badge()
                    ->color('emerald')
                    ->placeholder('N/A')
                    ->alignCenter(),

                TextColumn::make('document_paths')
                    ->label('Certificates')
                    ->badge()
                    ->formatStateUsing(function ($state): string {
                        if (is_array($state)) {
                            return count($state) . ' File' . (count($state) > 1 ? 's' : '');
                        }

                        return $state ? '1 File' : 'None';
                    })
                    ->color(fn($state): string => ! empty($state) ? 'primary' : 'gray')
                    ->icon(fn($state): string => ! empty($state) ? 'heroicon-m-document-check' : 'heroicon-m-x-circle')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Date Added')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Qualification')
                    ->icon('heroicon-m-plus')
                    ->modalHeading('Add Academic Qualification'),
            ])
            ->recordActions([
                ViewAction::make()->iconButton(),
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-academic-cap')
            ->emptyStateHeading('No Qualifications Added')
            ->emptyStateDescription('Add degrees, diplomas, or academic certifications to build this trainer\'s profile.');
    }
}
