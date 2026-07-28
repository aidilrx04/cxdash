<?php

namespace App\Filament\Resources\Trainers\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;

class TrainerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Section::make('Personal Information')->schema([
                TextInput::make('full_name')
                    ->helperText('Please use your legal name as it would appear on a contract.')
                    ->required(),
                // TextInput::make('commercial_name')
                //     ->label('The name you wish to be used commercially')
                //     ->helperText('e.g., on our website, promotional materials, or class schedules')
                //     ->required(),
                // Textarea::make('home_address')
                //     ->required(),
                // Group::make([
                //     TextInput::make('street_address_1')
                //         ->aboveLabel(Text::make('Address')->color('neutral')->size(TextSize::Medium))
                //         ->required(),
                //     TextInput::make('street_address_2'),
                //     Grid::make(2)
                //         ->schema([
                //             TextInput::make('city'),
                //             TextInput::make('state'),
                //             TextInput::make('zip_code'),
                //             TextInput::make('country')
                //         ])
                // ]),
                TextInput::make('email')
                    ->email()
                    ->label("Email Address")
                    ->required()
                    ->helperText('Please enter a valid email address (e.g., name@company.com).'),
                TextInput::make('phone_number')
                    ->required(),
                TextInput::make('years_experience')
                    ->numeric()
                    ->required(),
                Textarea::make('professional_summary')
                    ->belowLabel(Text::make(
                        "A brief overview of your training approach and philosophy (2-3 sentences). This will be used in trainer bios for proposals.Example: 'I believe in experiential, learner-centered training that drives measurable behavior change. With 12 years of experience in financial services, I focus on practical application and real-world case studies.'"
                    ))
                    ->required(),
                FileUpload::make('cv_path')
                    ->label("CV/Resume")
                    ->disk('public')
                    ->openable()
                    ->required()
                    ->belowLabel(Text::make('A professional headshot or photo (JPEG or PNG). This will be used in client proposals.')),
                FileUpload::make('profile_picture')
                    ->disk('public')
                    ->openable()
                    ->required()
                // ])
            ]);
    }
}
