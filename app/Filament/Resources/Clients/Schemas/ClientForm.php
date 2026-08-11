<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Organization Details')
                    ->description('Specify the primary corporate client or organization profile.')
                    ->icon('heroicon-m-building-office')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Client / Organization Name')
                            ->placeholder('e.g., Maybank, Ministry of Human Resources, TechCorp')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->prefixIcon('heroicon-m-building-office')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
