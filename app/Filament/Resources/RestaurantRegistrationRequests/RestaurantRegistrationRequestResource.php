<?php

namespace App\Filament\Resources\RestaurantRegistrationRequests;

use App\Filament\Resources\RestaurantRegistrationRequests\Pages\ListRestaurantRegistrationRequests;
use App\Filament\Resources\RestaurantRegistrationRequests\Schemas\RestaurantRegistrationRequestForm;
use App\Filament\Resources\RestaurantRegistrationRequests\Tables\RestaurantRegistrationRequestsTable;
use App\Models\RestaurantRegistrationRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RestaurantRegistrationRequestResource extends Resource
{
    protected static ?string $model = RestaurantRegistrationRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Restaurant Requests';

    protected static string | UnitEnum | null $navigationGroup = 'Administration';

    protected static ?string $recordTitleAttribute = 'restaurant_name';

    public static function form(Schema $schema): Schema
    {
        return RestaurantRegistrationRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RestaurantRegistrationRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRestaurantRegistrationRequests::route('/'),
        ];
    }
}
