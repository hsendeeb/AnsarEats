<?php

namespace App\Filament\Resources\CategoryTags\Pages;

use App\Filament\Resources\CategoryTags\CategoryTagResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategoryTag extends CreateRecord
{
    protected static string $resource = CategoryTagResource::class;
}
