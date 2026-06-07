<?php

namespace App\Filament\Resources\CategoryTags;

use App\Filament\Resources\CategoryTags\Pages\CreateCategoryTag;
use App\Filament\Resources\CategoryTags\Pages\EditCategoryTag;
use App\Filament\Resources\CategoryTags\Pages\ListCategoryTags;
use App\Filament\Resources\CategoryTags\Schemas\CategoryTagForm;
use App\Filament\Resources\CategoryTags\Tables\CategoryTagsTable;
use App\Models\CategoryTag;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CategoryTagResource extends Resource
{
    protected static ?string $model = CategoryTag::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'Category Tags';

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CategoryTagForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoryTagsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategoryTags::route('/'),
            'create' => CreateCategoryTag::route('/create'),
            'edit' => EditCategoryTag::route('/{record}/edit'),
        ];
    }
}
