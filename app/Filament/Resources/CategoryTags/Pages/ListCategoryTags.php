<?php

namespace App\Filament\Resources\CategoryTags\Pages;

use App\Filament\Resources\CategoryTags\CategoryTagResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCategoryTags extends ListRecords
{
    protected static string $resource = CategoryTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
