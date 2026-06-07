<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('role')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'owner' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('restaurant.name')
                    ->label('Restaurant')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        'customer' => 'Customer',
                        'owner' => 'Owner',
                        'super_admin' => 'Super Admin',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn ($record): bool => $record->role === 'super_admin'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
