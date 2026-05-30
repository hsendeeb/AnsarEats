<?php

namespace App\Filament\Resources\CategoryTags\Pages;

use App\Filament\Resources\CategoryTags\CategoryTagResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCategoryTag extends EditRecord
{
    protected static string $resource = CategoryTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
