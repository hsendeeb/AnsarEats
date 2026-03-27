<?php

namespace App\Filament\Resources\Restaurants\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RestaurantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->rows(4),
                TextInput::make('address')
                    ->maxLength(255),
                TextInput::make('phone')
                    ->maxLength(50),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                FileUpload::make('logo')
                    ->disk('public')
                    ->directory('restaurants'),
                FileUpload::make('cover_image')
                    ->disk('public')
                    ->directory('restaurants'),
                Toggle::make('is_open')
                    ->default(true),
            ]);
    }
}
