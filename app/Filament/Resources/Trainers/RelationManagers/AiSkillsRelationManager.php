<?php

namespace App\Filament\Resources\Trainers\RelationManagers;

use App\Models\AiSkill;
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
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AiSkillsRelationManager extends RelationManager
{
    protected static string $relationship = 'aiSkills';

    protected static ?string $title = 'AI Integration & Proficiency';

    protected static string|BackedEnum|null $icon = 'heroicon-m-sparkles';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('ai_usage')
                    ->label('AI Application & Use Case')
                    ->placeholder('e.g., Utilizes LLMs for dynamic curriculum adaptation, automated code review generation, prompt engineering instruction, and synthetic scenario building.')
                    ->helperText('Describe how AI tools and methodologies are applied in training, content creation, or operational workflows.')
                    ->rows(3)
                    ->required()
                    ->columnSpanFull(),

                Textarea::make('ai_tools')
                    ->label('AI Tools & Platforms Tech Stack')
                    ->placeholder('e.g., ChatGPT, Claude 3.5, GitHub Copilot, Ollama, LangChain, Midjourney, Perplexity AI')
                    ->helperText('List specific AI models, frameworks, and software platforms utilized.')
                    ->rows(2)
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('AI Skill & Tool Details')
                    ->icon('heroicon-m-sparkles')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('ai_tools')
                            ->label('Tools & Tech Stack')
                            ->badge()
                            ->color('purple')
                            ->separator(',')
                            ->columnSpanFull(),

                        TextEntry::make('ai_usage')
                            ->label('Application & Methodology')
                            ->markdown()
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
            ->recordTitleAttribute('ai_usage')
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('ai_tools')
                    ->label('AI Tools & Stack')
                    ->searchable()
                    ->badge()
                    ->color('purple')
                    ->separator(',')
                    ->wrap()
                    ->weight('bold'),

                TextColumn::make('ai_usage')
                    ->label('Application & Methodology')
                    ->searchable()
                    ->lineClamp(2)
                    ->wrap(),

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
                CreateAction::make()
                    ->label('Record AI Skills')
                    ->icon('heroicon-m-plus')
                    ->modalHeading('Record AI Capabilities & Tools'),

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
            ->emptyStateIcon('heroicon-o-sparkles')
            ->emptyStateHeading('No AI Skills Recorded')
            ->emptyStateDescription('Document how this trainer leverages Artificial Intelligence tools (e.g., ChatGPT, Copilot, LangChain) and methodologies in their training.');
    }
}
