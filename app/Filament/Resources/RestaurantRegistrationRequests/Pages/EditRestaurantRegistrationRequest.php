<?php

namespace App\Filament\Resources\RestaurantRegistrationRequests\Pages;

use App\Filament\Resources\RestaurantRegistrationRequests\RestaurantRegistrationRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRestaurantRegistrationRequest extends EditRecord
{
    protected static string $resource = RestaurantRegistrationRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
