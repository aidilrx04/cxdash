<?php

namespace App\Filament\Resources\Trainers\RelationManagers;

use App\Models\TrainingMethod;
use BackedEnum;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TrainingMethodsRelationManager extends RelationManager
{
    protected static string $relationship = 'trainingMethods';

    protected static ?string $title = 'Training Delivery Methods & Formats';

    protected static string|BackedEnum|null $icon = 'heroicon-m-presentation-chart-bar';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Methodology / Delivery Mode')
                    ->placeholder('e.g., Hands-on Lab, Virtual Instructor-Led (VILT), Interactive Workshop')
                    ->prefixIcon('heroicon-m-academic-cap')
                    ->datalist([
                        'Instructor-Led Training (ILT) / Classroom',
                        'Virtual Instructor-Led Training (VILT)',
                        'Hands-on Lab & Practical Exercises',
                        'Hybrid & Blended Learning',
                        'Interactive Workshops & Case Studies',
                        'Bootcamp & Intensive Immersion',
                        'Self-Paced & E-Learning Modules',
                        'One-on-One Mentorship & Coaching',
                        'Gamified & Simulation-Based Training',
                        'On-the-Job Training (OJT)',
                    ])
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Training Method Details')
                    ->icon('heroicon-m-presentation-chart-bar')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('name')
                            ->label('Methodology / Mode')
                            ->badge()
                            ->color('success')
                            ->icon('heroicon-m-academic-cap')
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
            ->recordTitleAttribute('name')
            ->defaultSort('name', 'asc')
            ->striped()
            ->columns([
                TextColumn::make('name')
                    ->label('Methodology / Mode')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-m-academic-cap')
                    ->weight('bold'),

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
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Attach Existing')
                    ->icon('heroicon-m-link')
                    ->preloadRecordSelect()
                    ->modalHeading('Attach Delivery Method to Trainer'),

                CreateAction::make()
                    ->label('Create New')
                    ->icon('heroicon-m-plus')
                    ->modalHeading('Create & Attach New Training Method'),
            ])
            ->recordActions([
                ViewAction::make()->iconButton(),
                EditAction::make()->iconButton(),
                DetachAction::make()
                    ->iconButton()
                    ->tooltip('Detach from trainer'),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-presentation-chart-bar')
            ->emptyStateHeading('No Training Methods Linked')
            ->emptyStateDescription('Attach or create training delivery formats (e.g., VILT, Hands-on Labs, Hybrid Workshops) utilized by this trainer.');
    }
}
