<?php

namespace App\Filament\Resources\Trainers\RelationManagers;

use App\Models\Certificate;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class CertificatesRelationManager extends RelationManager
{
    protected static string $relationship = 'certificates';

    protected static ?string $title = 'Certifications & Accreditations';

    protected static string|BackedEnum|null $icon = 'heroicon-m-document-check';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Certification Title')
                            ->placeholder('e.g., HRD Corp Certified Trainer / PMP / AWS Solutions Architect')
                            ->prefixIcon('heroicon-m-document-check')
                            ->required()
                            ->columnSpan(2),

                        TextInput::make('issuing_body')
                            ->label('Issuing Body / Organization')
                            ->placeholder('e.g., HRD Corp, Cisco, PMI, Microsoft')
                            ->prefixIcon('heroicon-m-building-office')
                            ->datalist([
                                'HRD Corp (HRDF)',
                                'Project Management Institute (PMI)',
                                'Cisco Networking Academy',
                                'Amazon Web Services (AWS)',
                                'Microsoft',
                                'CompTIA',
                                'Scrum Alliance',
                            ])
                            ->required()
                            ->columnSpan(2),

                        TextInput::make('year_obtained')
                            ->label('Year Obtained')
                            ->numeric()
                            ->minValue(1970)
                            ->maxValue((int) date('Y'))
                            ->placeholder('e.g., ' . date('Y'))
                            ->prefixIcon('heroicon-m-calendar')
                            ->required(),

                        DatePicker::make('expires_at')
                            ->label('Expiration Date')
                            ->placeholder('Select date (if applicable)')
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->prefixIcon('heroicon-m-clock')
                            ->helperText('Leave empty if the certification has lifetime validity.'),

                        FileUpload::make('document_paths')
                            ->label('Certificate Copies & Supporting Documents')
                            ->disk('public')
                            ->directory('trainers/certificates')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->multiple()
                            ->reorderable()
                            ->openable()
                            ->downloadable()
                            ->maxSize(10240) // 10MB
                            ->helperText('Upload official certificate copies (PDF or images up to 10MB).')
                            ->required()
                            ->columnSpan(2),
                    ]),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Certification Details')
                    ->columnSpanFull()
                    ->icon('heroicon-m-document-check')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Certification Name')
                            ->weight('bold')
                            ->columnSpan(2),

                        TextEntry::make('issuing_body')
                            ->label('Issuing Organization')
                            ->icon('heroicon-m-building-office')
                            ->columnSpan(2),

                        TextEntry::make('year_obtained')
                            ->label('Year Issued')
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-m-calendar'),

                        TextEntry::make('expires_at')
                            ->label('Expiration Date')
                            ->date('d M Y')
                            ->placeholder('Lifetime / No Expiration')
                            ->badge()
                            ->color(function ($record): string {
                                if (! $record->expires_at) {
                                    return 'gray';
                                }

                                return Carbon::parse($record->expires_at)->isPast() ? 'danger' : 'success';
                            }),

                        TextEntry::make('created_at')
                            ->label('Record Added')
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('-'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('-'),

                        TextEntry::make('document_paths')
                            ->label('Attached Certificate Files')
                            ->placeholder('No files attached')
                            ->columnSpanFull()
                            ->listWithLineBreaks()
                            ->bulleted(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('year_obtained', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('name')
                    ->label('Certification & Issuer')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn($record): string => $record->issuing_body ?? '')
                    ->wrap(),

                TextColumn::make('year_obtained')
                    ->label('Year')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                TextColumn::make('validity')
                    ->label('Status')
                    ->badge()
                    ->state(function ($record): string {
                        if (! $record->expires_at) {
                            return 'Lifetime';
                        }

                        $expires = Carbon::parse($record->expires_at);

                        if ($expires->isPast()) {
                            return 'Expired';
                        }

                        if ($expires->diffInDays(now()) <= 90) {
                            return 'Expiring Soon';
                        }

                        return 'Active';
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'Active' => 'success',
                        'Lifetime' => 'gray',
                        'Expiring Soon' => 'warning',
                        'Expired' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'Active' => 'heroicon-m-check-circle',
                        'Lifetime' => 'heroicon-m-shield-check',
                        'Expiring Soon' => 'heroicon-m-exclamation-triangle',
                        'Expired' => 'heroicon-m-x-circle',
                        default => 'heroicon-m-minus-circle',
                    })
                    ->alignCenter(),

                TextColumn::make('expires_at')
                    ->label('Expires On')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('Lifetime')
                    ->toggleable(),

                TextColumn::make('document_paths')
                    ->label('Files')
                    ->badge()
                    ->formatStateUsing(function ($state): string {
                        if (is_array($state)) {
                            return count($state) . ' File' . (count($state) > 1 ? 's' : '');
                        }

                        return $state ? '1 File' : '0 Files';
                    })
                    ->color(fn($state): string => ! empty($state) ? 'primary' : 'gray')
                    ->icon(fn($state): string => ! empty($state) ? 'heroicon-m-document-text' : 'heroicon-m-x-circle')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Date Added')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('active_valid')
                    ->label('Active / Lifetime Only')
                    ->query(fn(Builder $query): Builder => $query->where(function ($q) {
                        $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    })),

                Filter::make('expired')
                    ->label('Expired Only')
                    ->query(fn(Builder $query): Builder => $query->where('expires_at', '<=', now())),

                Filter::make('expiring_soon')
                    ->label('Expiring in 90 Days')
                    ->query(fn(Builder $query): Builder => $query->whereBetween('expires_at', [now(), now()->addDays(90)])),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Certificate')
                    ->icon('heroicon-m-plus')
                    ->modalHeading('Add Professional Certification'),
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
            ->emptyStateIcon('heroicon-o-document-check')
            ->emptyStateHeading('No Certifications Recorded')
            ->emptyStateDescription('Add professional credentials, HRD Corp accreditations, or technical licenses.');
    }
}
