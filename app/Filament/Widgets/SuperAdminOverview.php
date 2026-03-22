<?php

namespace App\Filament\Widgets;

use App\Models\Restaurant;
use App\Models\RestaurantRegistrationRequest;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SuperAdminOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Restaurants', (string) Restaurant::count())
                ->description('Approved restaurants')
                ->color('success'),

            Stat::make('Users', (string) User::count())
                ->description('All registered users')
                ->color('info'),

            Stat::make(
                'Pending Requests',
                (string) RestaurantRegistrationRequest::where('status', 'pending')->count()
            )
                ->description('Waiting for review')
                ->color('warning'),
        ];
    }
}

