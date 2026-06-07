<?php

namespace App\Filament\Resources\CategoryTags\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoryTagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Tag name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->helperText('Used in saved menu items and URLs. Leave blank on create to generate from the name.')
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->disabledOn('edit'),
                TextInput::make('emoji')
                    ->maxLength(20),
                FileUpload::make('image')
                    ->label('Photo')
                    ->image()
                    ->disk('public')
                    ->directory('category-tags')
                    ->visibility('public'),
                TextInput::make('sort_order')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }
}
