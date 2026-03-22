<?php

namespace App\Filament\Resources\RestaurantRegistrationRequests\Pages;

use App\Filament\Resources\RestaurantRegistrationRequests\RestaurantRegistrationRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListRestaurantRegistrationRequests extends ListRecords
{
    protected static string $resource = RestaurantRegistrationRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
