<?php

namespace App\Filament\Resources\Trainers\RelationManagers;

use App\Models\Industry;
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

class IndustriesRelationManager extends RelationManager
{
    protected static string $relationship = 'industries';

    protected static ?string $title = 'Target Industries & Sectors';

    protected static string|BackedEnum|null $icon = 'heroicon-m-building-office-2';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Industry / Sector Name')
                    ->placeholder('e.g., Banking & Financial Services, Healthcare, Manufacturing')
                    ->prefixIcon('heroicon-m-building-office')
                    ->datalist([
                        'Information Technology & Telecommunications',
                        'Banking, Finance & Insurance',
                        'Healthcare & Pharmaceuticals',
                        'Manufacturing & Industrial Automation',
                        'Oil & Gas, Energy & Utilities',
                        'Education, Research & Academic',
                        'Retail, E-Commerce & FMCG',
                        'Government, Defense & Public Sector',
                        'Logistics, Supply Chain & Transportation',
                        'Construction & Real Estate',
                    ])
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Industry Details')
                    ->icon('heroicon-m-building-office-2')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('name')
                            ->label('Industry / Sector')
                            ->badge()
                            ->color('warning')
                            ->icon('heroicon-m-building-office')
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
                    ->label('Industry / Sector')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-m-building-office')
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
                    ->modalHeading('Attach Industry Sector to Trainer'),

                CreateAction::make()
                    ->label('Create New')
                    ->icon('heroicon-m-plus')
                    ->modalHeading('Create & Attach New Industry'),
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
            ->emptyStateIcon('heroicon-o-building-office-2')
            ->emptyStateHeading('No Industries Linked')
            ->emptyStateDescription('Attach or create industry sectors (e.g., Banking, Healthcare, IT) that this trainer target-trains for.');
    }
}
