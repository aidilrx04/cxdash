<?php

namespace App\Filament\Resources\Trainers\RelationManagers;

use App\Models\SocialMedia;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SocialMediaRelationManager extends RelationManager
{
    protected static string $relationship = 'socialMedia';

    protected static ?string $title = 'Social Profiles & Links';

    protected static string|BackedEnum|null $icon = 'heroicon-m-share';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('platform')
                    ->label('Platform')
                    ->options([
                        'LinkedIn' => 'LinkedIn',
                        'GitHub' => 'GitHub',
                        'Twitter' => 'Twitter / X',
                        'YouTube' => 'YouTube',
                        'Instagram' => 'Instagram',
                        'Facebook' => 'Facebook',
                        'Website' => 'Personal Website / Portfolio',
                        'Other' => 'Other',
                    ])
                    // ->tags()
                    ->searchable()
                    ->required()
                    ->placeholder('Select or type platform name')
                    ->columnSpanFull(),

                TextInput::make('url')
                    ->label('Profile URL')
                    ->url()
                    ->prefix('https://')
                    ->placeholder('linkedin.com/in/username')
                    ->suffixIcon('heroicon-m-link')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Social Profile Info')
                    ->icon('heroicon-m-globe-alt')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('platform')
                            ->label('Platform')
                            ->badge()
                            ->color(fn(string $state): string => match (strtolower($state)) {
                                'linkedin' => 'info',
                                'github' => 'gray',
                                'twitter', 'twitter / x' => 'sky',
                                'youtube' => 'danger',
                                'instagram', 'facebook' => 'pink',
                                'website' => 'success',
                                default => 'primary',
                            }),

                        TextEntry::make('url')
                            ->label('Direct Link')
                            ->icon('heroicon-m-arrow-top-right-on-square')
                            ->url(fn(string $state): string => $state, shouldOpenInNewTab: true)
                            ->formatStateUsing(fn(): string => 'Visit Link ↗')
                            ->color('primary')
                            ->copyable(),

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
            ->recordTitleAttribute('platform')
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('platform')
                    ->label('Platform')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->icon(fn(string $state): string => match (strtolower($state)) {
                        'linkedin' => 'heroicon-m-briefcase',
                        'github' => 'heroicon-m-code-bracket',
                        'twitter', 'twitter / x' => 'heroicon-m-chat-bubble-bottom-center-text',
                        'youtube' => 'heroicon-m-video-camera',
                        'instagram', 'facebook' => 'heroicon-m-camera',
                        'website' => 'heroicon-m-globe-alt',
                        default => 'heroicon-m-link',
                    })
                    ->color(fn(string $state): string => match (strtolower($state)) {
                        'linkedin' => 'info',
                        'github' => 'gray',
                        'twitter', 'twitter / x' => 'sky',
                        'youtube' => 'danger',
                        'instagram', 'facebook' => 'pink',
                        'website' => 'success',
                        default => 'primary',
                    }),

                TextColumn::make('url')
                    ->label('Profile Link')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('URL copied to clipboard')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn(string $state): string => $state, shouldOpenInNewTab: true)
                    ->color('primary')
                    ->weight('medium'),

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
                    ->label('Add Social Link')
                    ->icon('heroicon-m-plus')
                    ->modalHeading('Add New Social Profile'),
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
            ->emptyStateIcon('heroicon-o-share')
            ->emptyStateHeading('No Social Links Added')
            ->emptyStateDescription('Link LinkedIn, GitHub, or portfolio sites to display on trainer profiles.');
    }
}
