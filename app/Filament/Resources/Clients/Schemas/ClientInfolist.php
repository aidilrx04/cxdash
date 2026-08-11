<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Client Overview')
                    ->description('Summary of corporate client details and training engagement metrics.')
                    ->icon('heroicon-m-building-office')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('name')
                            ->label('Client Name')
                            ->weight('bold')
                            ->icon('heroicon-m-building-office')
                            ->columnSpanFull(),

                        TextEntry::make('training_reports_count')
                            ->label('Total Training Reports')
                            ->state(fn($record) => $record->trainingReports()->count())
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-m-document-text')
                            ->formatStateUsing(fn($state) => "{$state} Reports"),

                        TextEntry::make('max_pax')
                            ->label('Peak Program Capacity')
                            ->state(fn($record) => $record->maxPax())
                            ->badge()
                            ->color('success')
                            ->icon('heroicon-m-user-group')
                            ->formatStateUsing(fn($state) => "{$state} Pax"),

                        TextEntry::make('created_at')
                            ->label('Date Added')
                            ->dateTime('d M Y, h:i A')
                            ->icon('heroicon-m-calendar'),

                        TextEntry::make('updated_at')
                            ->label('Last Modified')
                            ->dateTime('d M Y, h:i A')
                            ->icon('heroicon-m-clock'),
                    ]),
            ]);
    }
}
