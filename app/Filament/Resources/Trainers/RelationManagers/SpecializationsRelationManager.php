<?php

namespace App\Filament\Resources\Trainers\RelationManagers;

use App\Models\Specialization;
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

class SpecializationsRelationManager extends RelationManager
{
    protected static string $relationship = 'specializations';

    protected static ?string $title = 'Specializations & Subject Domains';

    protected static string|BackedEnum|null $icon = 'heroicon-m-tag';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Specialization / Skill Area')
                    ->placeholder('e.g., Cybersecurity & Network Defense, Full-Stack Web Development')
                    ->prefixIcon('heroicon-m-tag')
                    ->datalist([
                        'Web Development & Frameworks',
                        'Cybersecurity & Digital Forensics',
                        'Cloud Computing & DevOps',
                        'Data Science & Artificial Intelligence',
                        'Database Administration & Architecture',
                        'Project Management & Agile Methodologies',
                        'UX/UI Design & Product Management',
                        'Leadership & Corporate Soft Skills',
                    ])
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Specialization Details')
                    ->icon('heroicon-m-tag')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('name')
                            ->label('Domain / Expertise')
                            ->badge()
                            ->color('primary')
                            ->icon('heroicon-m-tag')
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
                    ->label('Specialization / Topic')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-m-tag')
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
                    ->modalHeading('Attach Specialization to Trainer'),

                CreateAction::make()
                    ->label('Create New')
                    ->icon('heroicon-m-plus')
                    ->modalHeading('Create & Attach New Specialization'),
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
            ->emptyStateIcon('heroicon-o-tag')
            ->emptyStateHeading('No Specializations Linked')
            ->emptyStateDescription('Attach or create skill domains (e.g., Network Security, Laravel, UX Design) to highlight trainer expertise.');
    }
}
