<?php

namespace App\Filament\Resources\TrainingReports\Schemas;

use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Storage;

class TrainingReportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Training')
                    ->columns(2)
                    ->columnSpan(2)
                    ->schema([
                        TextEntry::make('program_title')
                            ->label('Program')
                            ->columnSpanFull(),
                        TextEntry::make('client_name')
                            ->label('Client'),
                        TextEntry::make('trainer_name')
                            ->label('Trainer'),
                        TextEntry::make('status')
                            ->columnSpanFull(),
                        Section::make('')->columnSpanFull(),
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('total_participants')
                                    ->label('Participants')
                                    ->badge()
                                    ->color(Color::Blue),
                                TextEntry::make('total_evaluation')
                                    ->label('Evaluation')
                                    ->badge()
                                    ->color(Color::Amber),
                                TextEntry::make('overall_satisfaction')
                                    ->label('Satisfaction')
                                    ->badge()
                                    ->color(Color::Emerald),
                                TextEntry::make('pss_score')
                                    ->label('Customer Score')
                                    ->badge()
                                    ->color(Color::Emerald),
                            ])
                            ->columnSpanFull(),
                    ]),
                Section::make('Information')
                    ->columns(1)
                    ->schema([
                        TextEntry::make('file_name')
                            ->suffixAction(
                                Action::make('downloadPdf')
                                    ->label('Download PDF')
                                    ->icon('heroicon-m-arrow-down-tray')
                                    ->color('primary')
                                    ->visible(fn($record) => ! empty($record->file_path))
                                    ->action(fn($record) => Storage::disk('public')->download($record->file_path, $record->file_name ?? null))
                            ),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->date('d/m/Y H:i:s'),
                                TextEntry::make('updated_at')
                                    ->date('d/m/Y H:i:s'),
                            ]),
                    ]),
            ]);
    }
}
