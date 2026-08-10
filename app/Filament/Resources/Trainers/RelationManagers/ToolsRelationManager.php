<?php

namespace App\Filament\Resources\Trainers\RelationManagers;

use App\Models\Tool;
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

class ToolsRelationManager extends RelationManager
{
    protected static string $relationship = 'tools';

    protected static ?string $title = 'Software, Hardware & Tech Tools';

    protected static string|BackedEnum|null $icon = 'heroicon-m-wrench-screwdriver';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Tool / Software / Platform Name')
                    ->placeholder('e.g., Cisco Packet Tracer, Wireshark, Laravel, Figma, Docker')
                    ->prefixIcon('heroicon-m-wrench')
                    ->datalist([
                        'Cisco Packet Tracer / GNS3',
                        'Wireshark / Network Analyzers',
                        'Laravel / Filament PHP',
                        'Svelte / SvelteKit',
                        'Docker / Kubernetes',
                        'Git / GitHub / GitLab',
                        'Postman / Insomnia API Client',
                        'Figma / Penpot / UI Design Tools',
                        'VS Code / JetBrains IDEs',
                        'Linux / Bash / Shell Scripting',
                        'Jira / Confluence / Trello',
                        'AWS / Google Cloud Platform / Azure',
                    ])
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tool Details')
                    ->icon('heroicon-m-wrench-screwdriver')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('name')
                            ->label('Tool / Technology')
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-m-wrench')
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
                    ->label('Tool / Technology')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-m-wrench')
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
                    ->modalHeading('Attach Tool / Technology to Trainer'),

                CreateAction::make()
                    ->label('Create New')
                    ->icon('heroicon-m-plus')
                    ->modalHeading('Create & Attach New Tool'),
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
            ->emptyStateIcon('heroicon-o-wrench-screwdriver')
            ->emptyStateHeading('No Tools Linked')
            ->emptyStateDescription('Attach or create software, hardware, or framework tools (e.g., Cisco Packet Tracer, Laravel, Figma) that this trainer uses.');
    }
}
