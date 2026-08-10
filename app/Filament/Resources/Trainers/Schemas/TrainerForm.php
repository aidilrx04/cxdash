<?php

namespace App\Filament\Resources\Trainers\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TrainerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        // Left Column: Primary Trainer Info (2 Columns wide)
                        Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Section::make('Personal Details')
                                    ->description('Basic contact and legal identification information.')
                                    ->icon('heroicon-m-user-circle')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('full_name')
                                            ->label('Legal Full Name')
                                            ->placeholder('e.g., Jane Doe')
                                            ->prefixIcon('heroicon-m-user')
                                            ->helperText('Please use your legal name as it would appear on a contract.')
                                            ->required()
                                            ->columnSpan(2),

                                        TextInput::make('email')
                                            ->label('Email Address')
                                            ->email()
                                            ->placeholder('name@company.com')
                                            ->prefixIcon('heroicon-m-envelope')
                                            ->helperText('Used for official contract notifications.')
                                            ->required(),

                                        TextInput::make('phone_number')
                                            ->label('Phone Number')
                                            ->tel()
                                            ->placeholder('+60 12-345 6789')
                                            ->prefixIcon('heroicon-m-phone')
                                            ->required(),
                                    ]),

                                Section::make('Professional Background')
                                    ->description('Highlight trainer expertise and proposal overview.')
                                    ->icon('heroicon-m-briefcase')
                                    ->schema([
                                        TextInput::make('years_experience')
                                            ->label('Years of Experience')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(50)
                                            ->suffix('Years')
                                            ->prefixIcon('heroicon-m-clock')
                                            ->placeholder('e.g., 8')
                                            ->required(),

                                        Textarea::make('professional_summary')
                                            ->label('Professional Summary & Philosophy')
                                            ->rows(4)
                                            ->placeholder("Example: 'I believe in experiential, learner-centered training that drives measurable behavior change. With 12 years of experience in financial services, I focus on practical application and real-world case studies.'")
                                            ->helperText('A brief overview of your training approach (2-3 sentences). Used in client proposals.')
                                            ->required(),
                                    ]),
                            ]),

                        // Right Column: Media & Documents Sidebar (1 Column wide)
                        Group::make()
                            ->columnSpan(1)
                            ->schema([
                                Section::make('Profile Picture')
                                    ->description('Professional headshot for client proposals.')
                                    ->icon('heroicon-m-photo')
                                    ->schema([
                                        FileUpload::make('profile_picture')
                                            ->label('')
                                            ->disk('public')
                                            ->directory('trainers/avatars')
                                            ->image()
                                            ->avatar()
                                            ->imageEditor()
                                            ->acceptedFileTypes(['image/png', 'image/jpeg'])
                                            ->maxSize(5120) // 5MB
                                            ->required()
                                            ->alignCenter()
                                            ->helperText('Upload a high-resolution JPEG or PNG headshot.'),
                                    ]),

                                Section::make('Curriculum Vitae (CV)')
                                    ->description('Official resume or biography document.')
                                    ->icon('heroicon-m-document-text')
                                    ->schema([
                                        FileUpload::make('cv_path')
                                            ->label('Upload CV / Resume')
                                            ->disk('public')
                                            ->directory('trainers/cvs')
                                            ->acceptedFileTypes(['application/pdf'])
                                            ->maxSize(10240) // 10MB
                                            ->openable()
                                            ->downloadable()
                                            ->required()
                                            ->helperText('PDF format up to 10MB.'),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
