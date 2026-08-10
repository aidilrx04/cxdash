<?php

namespace App\Filament\Resources\Trainers\RelationManagers;

use App\Models\Language;
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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LanguagesRelationManager extends RelationManager
{
    protected static string $relationship = 'languages';

    protected static ?string $title = 'Languages Spoken & Proficiency';

    protected static string|BackedEnum|null $icon = 'heroicon-m-language';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('language')
                    ->label('Language')
                    ->placeholder('e.g., English, Bahasa Melayu, Mandarin, Tamil')
                    ->prefixIcon('heroicon-m-language')
                    ->datalist([
                        'English',
                        'Bahasa Melayu',
                        'Mandarin Chinese',
                        'Tamil',
                        'Cantonese',
                        'Japanese',
                        'Arabic',
                        'German',
                        'French',
                        'Spanish',
                        'Hindi',
                    ])
                    ->required(),

                Select::make('proficiency')
                    ->label('Proficiency Level')
                    ->options([
                        'Native' => 'Native / Mother Tongue',
                        'Fluent' => 'Fluent',
                        'Professional Working Proficiency' => 'Professional Working Proficiency',
                        'Conversational' => 'Conversational',
                        'Basic' => 'Basic',
                    ])
                    ->native(false)
                    ->required(),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Language Proficiency Details')
                    ->icon('heroicon-m-language')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('language')
                            ->label('Language')
                            ->weight('bold')
                            ->icon('heroicon-m-language'),

                        TextEntry::make('proficiency')
                            ->label('Proficiency Level')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'Native', 'Fluent' => 'success',
                                'Professional Working Proficiency' => 'info',
                                'Conversational' => 'warning',
                                default => 'gray',
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
            ->recordTitleAttribute('language')
            ->defaultSort('language', 'asc')
            ->striped()
            ->columns([
                TextColumn::make('language')
                    ->label('Language')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-m-language'),

                TextColumn::make('proficiency')
                    ->label('Proficiency Level')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Native', 'Fluent' => 'success',
                        'Professional Working Proficiency' => 'info',
                        'Conversational' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

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
                SelectFilter::make('proficiency')
                    ->label('Filter by Fluency')
                    ->options([
                        'Native' => 'Native',
                        'Fluent' => 'Fluent',
                        'Professional Working Proficiency' => 'Professional Working Proficiency',
                        'Conversational' => 'Conversational',
                        'Basic' => 'Basic',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Language')
                    ->icon('heroicon-m-plus')
                    ->modalHeading('Add Spoken Language'),

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
            ->emptyStateIcon('heroicon-o-language')
            ->emptyStateHeading('No Spoken Languages Added')
            ->emptyStateDescription('Record spoken languages and fluency levels (e.g., English - Native, Malay - Fluent) for this trainer.');
    }
}
